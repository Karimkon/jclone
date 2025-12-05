<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ImportRequest;
use App\Models\ImportCost;
use App\Models\Listing;
use App\Models\VendorProfile;
use App\Services\ImportCalculator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ImportController extends Controller
{
    /**
     * Display import requests dashboard
     */
    public function index()
    {
        $vendor = Auth::user()->vendorProfile;
        if (!$vendor) {
            return redirect()->route('vendor.onboard.create');
        }

        $imports = ImportRequest::with(['listing', 'costs'])
            ->where('vendor_profile_id', $vendor->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'pending' => ImportRequest::where('vendor_profile_id', $vendor->id)
                ->where('status', 'pending')->count(),
            'calculating' => ImportRequest::where('vendor_profile_id', $vendor->id)
                ->where('status', 'calculating')->count(),
            'processing' => ImportRequest::where('vendor_profile_id', $vendor->id)
                ->where('status', 'processing')->count(),
            'importing' => ImportRequest::where('vendor_profile_id', $vendor->id)
                ->where('status', 'importing')->count(),
            'completed' => ImportRequest::where('vendor_profile_id', $vendor->id)
                ->where('status', 'completed')->count(),
            'cancelled' => ImportRequest::where('vendor_profile_id', $vendor->id)
                ->where('status', 'cancelled')->count(),
        ];

        return view('vendor.imports.index', compact('imports', 'stats'));
    }

    /**
     * Show import calculator form
     */
    public function create()
    {
        $vendor = Auth::user()->vendorProfile;
        if (!$vendor) {
            return redirect()->route('vendor.onboard.create');
        }

        // Get vendor's listings for reference
        $listings = $vendor->listings()->get();
        
        // Default tariff rates
        $defaultRates = [
            'duty_rate' => config('imports.default_duty_rate', 0.10),
            'vat_rate' => config('imports.default_vat_rate', 0.18),
            'excise_rate' => config('imports.default_excise_rate', 0.00),
            'withholding_tax_rate' => config('imports.default_withholding_tax_rate', 0.05),
            'marketplace_commission' => config('imports.marketplace_comm_percent', 0.05),
            'weight_rate' => config('imports.default_rate_per_kg', 2.0),
        ];

        return view('vendor.imports.create', compact('listings', 'defaultRates'));
    }

    /**
     * Quick calculate API (public)
     */
    public function calculateApi(Request $request)
    {
        $validated = $request->validate([
            'supplier_price' => 'required|numeric|min:0',
            'freight' => 'required|numeric|min:0',
            'insurance' => 'nullable|numeric|min:0',
            'calc_method' => 'required|in:ad_valorem,weight',
            'weight_kg' => 'nullable|numeric|min:0',
            
            // Rates
            'duty_rate' => 'nullable|numeric|min:0|max:100',
            'vat_rate' => 'nullable|numeric|min:0|max:100',
            'excise_rate' => 'nullable|numeric|min:0|max:100',
            'withholding_tax_rate' => 'nullable|numeric|min:0|max:100',
            'marketplace_commission' => 'nullable|numeric|min:0|max:100',
            'rate_per_kg' => 'nullable|numeric|min:0',
        ]);

        try {
            $calculator = new ImportCalculator();
            
            if ($validated['calc_method'] === 'ad_valorem') {
                $result = $calculator->calculateAdValorem(
                    floatval($validated['supplier_price']),
                    floatval($validated['freight']),
                    floatval($validated['insurance'] ?? 0),
                    floatval($validated['duty_rate'] ?? config('imports.default_duty_rate', 0.10)),
                    floatval($validated['vat_rate'] ?? config('imports.default_vat_rate', 0.18)),
                    floatval($validated['excise_rate'] ?? config('imports.default_excise_rate', 0.00)),
                    floatval($validated['withholding_tax_rate'] ?? config('imports.default_withholding_tax_rate', 0.05)),
                    floatval($validated['marketplace_commission'] ?? config('imports.marketplace_comm_percent', 0.05))
                );
            } else {
                $result = $calculator->calculateWeightBased(
                    floatval($validated['rate_per_kg'] ?? config('imports.default_rate_per_kg', 2.0)),
                    floatval($validated['weight_kg'] ?? 0),
                    floatval($validated['supplier_price']),
                    floatval($validated['freight']),
                    floatval($validated['insurance'] ?? 0),
                    floatval($validated['vat_rate'] ?? config('imports.default_vat_rate', 0.18)),
                    floatval($validated['excise_rate'] ?? config('imports.default_excise_rate', 0.00)),
                    floatval($validated['withholding_tax_rate'] ?? config('imports.default_withholding_tax_rate', 0.05)),
                    floatval($validated['marketplace_commission'] ?? config('imports.marketplace_comm_percent', 0.05))
                );
            }

            return response()->json([
                'success' => true,
                'result' => $result,
                'breakdown' => $this->formatBreakdown($result)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Calculation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store import request
     */
    public function store(Request $request)
    {
        $vendor = Auth::user()->vendorProfile;
        if (!$vendor) {
            return response()->json(['error' => 'Vendor profile required'], 403);
        }

        $validated = $request->validate([
            'listing_id' => 'nullable|exists:listings,id',
            'title' => 'required_if:listing_id,null|string|max:255',
            'description' => 'nullable|string|max:1000',
            'supplier_price' => 'required|numeric|min:0',
            'freight' => 'required|numeric|min:0',
            'insurance' => 'nullable|numeric|min:0',
            'calc_method' => 'required|in:ad_valorem,weight',
            'weight_kg' => 'nullable|numeric|min:0',
            
            // Additional info for new listings
            'category_id' => 'nullable|exists:categories,id',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpg,jpeg,png|max:2048',
        ]);

        DB::beginTransaction();
        try {
            // Create listing if not provided
            if (!$request->listing_id && $request->title) {
                $listing = Listing::create([
                    'vendor_profile_id' => $vendor->id,
                    'title' => $validated['title'],
                    'description' => $validated['description'] ?? 'Import product - details to be updated',
                    'category_id' => $validated['category_id'] ?? 1,
                    'price' => 0, // Will be set after cost calculation
                    'weight_kg' => $validated['weight_kg'] ?? 0,
                    'origin' => 'imported',
                    'condition' => 'new',
                    'stock' => 0, // Will be set after import
                    'is_active' => false, // Activate after import completion
                ]);

                $listingId = $listing->id;
            } else {
                $listingId = $validated['listing_id'];
            }

            $import = ImportRequest::create([
                'listing_id' => $listingId,
                'vendor_profile_id' => $vendor->id,
                'supplier_price' => $validated['supplier_price'],
                'freight' => $validated['freight'],
                'insurance' => $validated['insurance'] ?? 0,
                'calc_method' => $validated['calc_method'],
                'weight_kg' => $validated['weight_kg'] ?? 0,
                'status' => 'pending',
            ]);

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'import' => $import,
                    'message' => 'Import request created successfully'
                ], 201);
            }

            return redirect()->route('vendor.imports.show', $import)
                ->with('success', 'Import request created. Please calculate costs to proceed.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to create import request: ' . $e->getMessage()
                ], 500);
            }

            return back()->withInput()->with('error', 'Failed to create import request: ' . $e->getMessage());
        }
    }

    /**
     * Calculate import costs
     */
    public function calculate(Request $request, ImportRequest $import)
    {
        // Verify ownership
        if ($import->vendor_profile_id !== Auth::user()->vendorProfile->id) {
            abort(403);
        }

        $validated = $request->validate([
            'duty_rate' => 'nullable|numeric|min:0|max:100',
            'vat_rate' => 'nullable|numeric|min:0|max:100',
            'excise_rate' => 'nullable|numeric|min:0|max:100',
            'withholding_tax_rate' => 'nullable|numeric|min:0|max:100',
            'marketplace_commission' => 'nullable|numeric|min:0|max:100',
            'rate_per_kg' => 'nullable|numeric|min:0',
            'import_commission' => 'nullable|numeric|min:0|max:100',
        ]);

        DB::beginTransaction();
        try {
            $import->update(['status' => 'calculating']);
            
            $calculator = new ImportCalculator();
            
            if ($import->calc_method === 'ad_valorem') {
                $result = $calculator->calculateAdValorem(
                    floatval($import->supplier_price),
                    floatval($import->freight),
                    floatval($import->insurance),
                    floatval($validated['duty_rate'] ?? config('imports.default_duty_rate', 0.10)),
                    floatval($validated['vat_rate'] ?? config('imports.default_vat_rate', 0.18)),
                    floatval($validated['excise_rate'] ?? config('imports.default_excise_rate', 0.00)),
                    floatval($validated['withholding_tax_rate'] ?? config('imports.default_withholding_tax_rate', 0.05)),
                    floatval($validated['marketplace_commission'] ?? config('imports.marketplace_comm_percent', 0.05)),
                    floatval($validated['import_commission'] ?? config('imports.import_commission', 0.03))
                );
            } else {
                $result = $calculator->calculateWeightBased(
                    floatval($validated['rate_per_kg'] ?? config('imports.default_rate_per_kg', 2.0)),
                    floatval($import->weight_kg ?: 0),
                    floatval($import->supplier_price),
                    floatval($import->freight),
                    floatval($import->insurance),
                    floatval($validated['vat_rate'] ?? config('imports.default_vat_rate', 0.18)),
                    floatval($validated['excise_rate'] ?? config('imports.default_excise_rate', 0.00)),
                    floatval($validated['withholding_tax_rate'] ?? config('imports.default_withholding_tax_rate', 0.05)),
                    floatval($validated['marketplace_commission'] ?? config('imports.marketplace_comm_percent', 0.05)),
                    floatval($validated['import_commission'] ?? config('imports.import_commission', 0.03))
                );
            }

            // Save import costs
            $importCost = ImportCost::updateOrCreate(
                ['import_request_id' => $import->id],
                [
                    'item_cost' => $import->supplier_price,
                    'freight' => $import->freight,
                    'insurance' => $import->insurance,
                    'cif' => $result['cif'],
                    
                    'duty' => $result['duty'],
                    'vat' => $result['vat'],
                    'other_taxes' => $result['other_taxes'] ?? 0,
                    'total_tax' => $result['total_tax'],
                    
                    'import_commission' => $result['import_commission'] ?? 0,
                    'platform_commission' => $result['platform_commission'],
                    
                    'final_import_cost' => $result['total_cost'],
                    'breakdown' => $result
                ]
            );

            // Update import status
            $import->update([
                'status' => 'calculated',
                'tariff_meta' => [
                    'duty_rate' => $validated['duty_rate'] ?? config('imports.default_duty_rate', 0.10),
                    'vat_rate' => $validated['vat_rate'] ?? config('imports.default_vat_rate', 0.18),
                    'excise_rate' => $validated['excise_rate'] ?? config('imports.default_excise_rate', 0.00),
                    'withholding_tax_rate' => $validated['withholding_tax_rate'] ?? config('imports.default_withholding_tax_rate', 0.05),
                    'marketplace_commission' => $validated['marketplace_commission'] ?? config('imports.marketplace_comm_percent', 0.05),
                ]
            ]);

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'import_cost' => $importCost,
                    'calculation' => $result,
                    'formatted_breakdown' => $this->formatBreakdown($result)
                ]);
            }

            return back()->with('success', 'Import costs calculated successfully.')
                ->with('breakdown', $result);

        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Calculation failed: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Failed to calculate costs: ' . $e->getMessage());
        }
    }

    /**
     * Start import process
     */
    public function startImport(Request $request, ImportRequest $import)
    {
        // Verify ownership
        if ($import->vendor_profile_id !== Auth::user()->vendorProfile->id) {
            abort(403);
        }

        // Check if costs are calculated
        if (!$import->costs) {
            return back()->with('error', 'Please calculate import costs first.');
        }

        DB::beginTransaction();
        try {
            $import->update(['status' => 'importing']);
            
            // Update listing with final price (cost + margin)
            $listing = $import->listing;
            $finalCost = $import->costs->final_import_cost;
            $sellingPrice = $finalCost * 1.3; // 30% margin
            
            $listing->update([
                'price' => $sellingPrice,
                'weight_kg' => $import->weight_kg ?? $listing->weight_kg,
                'is_active' => true,
            ]);

            // Create shipment record
            $shipment = \App\Models\Shipment::create([
                'tracking_number' => 'IMP-' . strtoupper(uniqid()),
                'order_id' => null,
                'status' => 'pending',
                'meta' => [
                    'type' => 'import',
                    'import_request_id' => $import->id,
                    'vendor_id' => $import->vendor_profile_id,
                    'estimated_arrival' => now()->addDays(14)->toDateString(),
                    'total_cost' => $finalCost,
                ]
            ]);

            // Create warehouse stock reservation
            \App\Models\WarehouseStock::create([
                'warehouse_id' => 1, // Default warehouse
                'listing_id' => $listing->id,
                'quantity' => 0, // Will be updated on arrival
                'status' => 'in_transit',
                'meta' => [
                    'import_request_id' => $import->id,
                    'shipment_id' => $shipment->id,
                    'expected_quantity' => $request->input('quantity', 1),
                ]
            ]);

            // Notify logistics team
            \App\Models\NotificationQueue::create([
                'type' => 'logistics_notification',
                'title' => 'New Import Request',
                'message' => "New import request for {$listing->title}. Total cost: {$finalCost}",
                'meta' => [
                    'import_id' => $import->id,
                    'shipment_id' => $shipment->id,
                    'listing_id' => $listing->id,
                    'vendor_name' => $import->vendorProfile->business_name,
                    'action_url' => route('logistics.shipments.show', $shipment),
                ],
                'status' => 'pending',
            ]);

            // Notify vendor
            \App\Models\NotificationQueue::create([
                'user_id' => Auth::id(),
                'type' => 'import_started',
                'title' => 'Import Process Started',
                'message' => "Your import for {$listing->title} has been initiated. Tracking: {$shipment->tracking_number}",
                'meta' => [
                    'import_id' => $import->id,
                    'shipment_id' => $shipment->id,
                    'tracking_number' => $shipment->tracking_number,
                    'estimated_arrival' => now()->addDays(14)->toDateString(),
                ],
                'status' => 'pending',
            ]);

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Import started successfully',
                    'import' => $import,
                    'shipment' => $shipment
                ]);
            }

            return redirect()->route('vendor.imports.show', $import)
                ->with('success', 'Import process started! Logistics team has been notified.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to start import: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Failed to start import: ' . $e->getMessage());
        }
    }

    /**
     * Show import details
     */
    public function show(ImportRequest $import)
    {
        // Verify ownership
        if ($import->vendor_profile_id !== Auth::user()->vendorProfile->id) {
            abort(403);
        }

        $import->load(['listing.images', 'costs', 'vendorProfile']);
        
        // Get related shipment if exists
        $shipment = \App\Models\Shipment::where('meta->import_request_id', $import->id)->first();
        
        // Get warehouse stock status
        $warehouseStock = \App\Models\WarehouseStock::where('meta->import_request_id', $import->id)->first();
        
        // Format breakdown for display
        $breakdown = $import->costs ? $this->formatBreakdown($import->costs->breakdown) : null;

        return view('vendor.imports.show', compact('import', 'shipment', 'warehouseStock', 'breakdown'));
    }

    /**
     * Check import status
     */
    public function status(ImportRequest $import)
    {
        // Verify ownership
        if ($import->vendor_profile_id !== Auth::user()->vendorProfile->id) {
            abort(403);
        }

        $import->load(['costs', 'listing']);
        
        $shipment = \App\Models\Shipment::where('meta->import_request_id', $import->id)->first();
        $warehouseStock = \App\Models\WarehouseStock::where('meta->import_request_id', $import->id)->first();
        
        $statusInfo = [
            'import' => $import,
            'shipment' => $shipment,
            'warehouse_stock' => $warehouseStock,
            'timeline' => $this->getImportTimeline($import, $shipment),
            'next_steps' => $this->getNextSteps($import, $shipment),
        ];

        if (request()->wantsJson()) {
            return response()->json($statusInfo);
        }

        return view('vendor.imports.status', $statusInfo);
    }

    /**
     * Cancel import request
     */
    public function cancel(Request $request, ImportRequest $import)
    {
        // Verify ownership
        if ($import->vendor_profile_id !== Auth::user()->vendorProfile->id) {
            abort(403);
        }

        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        // Only allow cancellation if not already importing/completed
        if (in_array($import->status, ['importing', 'completed', 'shipped'])) {
            return back()->with('error', 'Cannot cancel import in current status.');
        }

        $import->update([
            'status' => 'cancelled',
            'meta' => array_merge($import->meta ?? [], [
                'cancellation' => [
                    'reason' => $request->reason,
                    'cancelled_by' => Auth::id(),
                    'cancelled_at' => now()->toDateTimeString(),
                ]
            ])
        ]);

        // Update listing status
        if ($import->listing) {
            $import->listing->update(['is_active' => false]);
        }

        // Notify logistics if shipment exists
        if ($shipment = \App\Models\Shipment::where('meta->import_request_id', $import->id)->first()) {
            $shipment->update(['status' => 'cancelled']);
        }

        return back()->with('success', 'Import request cancelled successfully.');
    }

    /**
     * Format breakdown for display
     */
    private function formatBreakdown(array $result): array
    {
        return [
            'CIF Calculation' => [
                'Supplier Price' => number_format($result['item_cost'] ?? 0, 2),
                'Freight Cost' => number_format($result['freight'] ?? 0, 2),
                'Insurance' => number_format($result['insurance'] ?? 0, 2),
                'Total CIF' => number_format($result['cif'] ?? 0, 2),
            ],
            'Taxes & Duties' => [
                'Duty' => number_format($result['duty'] ?? 0, 2) . " ({$result['duty_rate']}%)",
                'VAT' => number_format($result['vat'] ?? 0, 2) . " ({$result['vat_rate']}%)",
                'Other Taxes' => number_format($result['other_taxes'] ?? 0, 2),
                'Total Taxes' => number_format($result['total_tax'] ?? 0, 2),
            ],
            'Commissions' => [
                'Import Commission' => number_format($result['import_commission'] ?? 0, 2),
                'Platform Commission' => number_format($result['platform_commission'] ?? 0, 2),
            ],
            'Final Cost' => [
                'Total Cost' => number_format($result['total_cost'] ?? 0, 2),
                'Recommended Selling Price (30% margin)' => number_format(($result['total_cost'] ?? 0) * 1.3, 2),
            ]
        ];
    }

    /**
     * Get import timeline
     */
    private function getImportTimeline($import, $shipment): array
    {
        $timeline = [];
        
        $timeline[] = [
            'date' => $import->created_at,
            'title' => 'Request Created',
            'description' => 'Import request submitted',
            'status' => 'completed',
        ];
        
        if ($import->costs) {
            $timeline[] = [
                'date' => $import->costs->created_at,
                'title' => 'Costs Calculated',
                'description' => 'Import costs calculated and approved',
                'status' => 'completed',
            ];
        }
        
        if ($import->status === 'importing' && $shipment) {
            $timeline[] = [
                'date' => $shipment->created_at,
                'title' => 'Shipment Created',
                'description' => 'Tracking: ' . $shipment->tracking_number,
                'status' => 'completed',
            ];
            
            $timeline[] = [
                'date' => null,
                'title' => 'In Transit',
                'description' => 'Estimated arrival: ' . ($shipment->meta['estimated_arrival'] ?? 'N/A'),
                'status' => 'current',
            ];
            
            $timeline[] = [
                'date' => null,
                'title' => 'Customs Clearance',
                'description' => 'Waiting for customs processing',
                'status' => 'pending',
            ];
            
            $timeline[] = [
                'date' => null,
                'title' => 'Warehouse Receiving',
                'description' => 'Stock will be available for sale',
                'status' => 'pending',
            ];
        }
        
        return $timeline;
    }

    /**
     * Get next steps for import
     */
    private function getNextSteps($import, $shipment): array
    {
        $steps = [];
        
        switch ($import->status) {
            case 'pending':
                $steps[] = 'Calculate import costs';
                $steps[] = 'Review cost breakdown';
                $steps[] = 'Start import process';
                break;
                
            case 'calculated':
                $steps[] = 'Review and confirm costs';
                $steps[] = 'Start import process';
                $steps[] = 'Make payment (if required)';
                break;
                
            case 'importing':
                $steps[] = 'Track shipment progress';
                $steps[] = 'Await customs clearance';
                $steps[] = 'Confirm warehouse receipt';
                break;
                
            case 'completed':
                $steps[] = 'Update product listing';
                $steps[] = 'Set selling price';
                $steps[] = 'Start selling!';
                break;
        }
        
        return $steps;
    }

    /**
     * Get import statistics
     */
    public function statistics()
    {
        $vendor = Auth::user()->vendorProfile;
        if (!$vendor) {
            return response()->json(['error' => 'No vendor profile'], 403);
        }

        $stats = [
            'total_imports' => ImportRequest::where('vendor_profile_id', $vendor->id)->count(),
            'total_cost' => ImportCost::whereHas('importRequest', function($q) use ($vendor) {
                $q->where('vendor_profile_id', $vendor->id);
            })->sum('final_import_cost'),
            'avg_import_cost' => ImportCost::whereHas('importRequest', function($q) use ($vendor) {
                $q->where('vendor_profile_id', $vendor->id);
            })->avg('final_import_cost') ?? 0,
            'successful_imports' => ImportRequest::where('vendor_profile_id', $vendor->id)
                ->where('status', 'completed')->count(),
        ];

        // Monthly import trend
        $monthlyTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthDate = now()->subMonths($i);
            $month = $monthDate->format('M');
            
            $imports = ImportRequest::where('vendor_profile_id', $vendor->id)
                ->whereYear('created_at', $monthDate->year)
                ->whereMonth('created_at', $monthDate->month)
                ->count();
            
            $cost = ImportCost::whereHas('importRequest', function($q) use ($vendor, $monthDate) {
                $q->where('vendor_profile_id', $vendor->id)
                  ->whereYear('created_at', $monthDate->year)
                  ->whereMonth('created_at', $monthDate->month);
            })->sum('final_import_cost');
            
            $monthlyTrend[] = [
                'month' => $month,
                'imports' => $imports,
                'cost' => $cost,
            ];
        }

        return response()->json([
            'stats' => $stats,
            'monthly_trend' => $monthlyTrend,
        ]);
    }
}