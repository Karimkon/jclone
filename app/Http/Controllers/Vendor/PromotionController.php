<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Promotion;
use App\Models\Listing;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PromotionController extends Controller
{
    /**
     * Display vendor's promotions
     */
    public function index(Request $request)
    {
        $vendor = Auth::user()->vendorProfile;
        
        if (!$vendor) {
            return redirect()->route('vendor.onboard.create');
        }

        $query = Promotion::with(['listing.images'])
            ->where('vendor_profile_id', $vendor->id)
            ->orderBy('created_at', 'desc');

        // Status filter
        if ($request->has('status') && $request->status) {
            switch ($request->status) {
                case 'active':
                    $query->where('is_active', true)
                          ->where('starts_at', '<=', now())
                          ->where('ends_at', '>=', now());
                    break;
                case 'pending':
                    $query->where('is_active', true)
                          ->where('starts_at', '>', now());
                    break;
                case 'expired':
                    $query->where('ends_at', '<', now());
                    break;
                case 'cancelled':
                    $query->where('is_active', false);
                    break;
            }
        }

        // Type filter
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        $promotions = $query->paginate(20);

        // Promotion types for filter
        $promotionTypes = [
            Promotion::TYPE_FEATURED,
            Promotion::TYPE_BANNER,
            Promotion::TYPE_SPOTLIGHT,
            Promotion::TYPE_DISCOUNT,
            Promotion::TYPE_FLASH_SALE
        ];

        // Stats
        $stats = [
            'total' => Promotion::where('vendor_profile_id', $vendor->id)->count(),
            'active' => Promotion::where('vendor_profile_id', $vendor->id)
                ->where('is_active', true)
                ->where('starts_at', '<=', now())
                ->where('ends_at', '>=', now())
                ->count(),
            'pending' => Promotion::where('vendor_profile_id', $vendor->id)
                ->where('is_active', true)
                ->where('starts_at', '>', now())
                ->count(),
            'expired' => Promotion::where('vendor_profile_id', $vendor->id)
                ->where('ends_at', '<', now())
                ->count(),
        ];

        return view('vendor.promotions.index', compact('promotions', 'stats', 'promotionTypes'));
    }

    /**
     * Show create promotion form
     */
    public function create()
    {
        $vendor = Auth::user()->vendorProfile;
        
        if (!$vendor) {
            return redirect()->route('vendor.onboard.create');
        }

        // Get vendor's active listings for promotion
        $listings = $vendor->listings()
            ->where('is_active', true)
            ->with('images')
            ->orderBy('title')
            ->get();

        // Promotion types
        $promotionTypes = [
            Promotion::TYPE_FEATURED => [
                'name' => 'Featured Listing',
                'description' => 'Show your product in featured section',
                'price' => 10.00, // per week
                'duration' => 7 // days
            ],
            Promotion::TYPE_SPOTLIGHT => [
                'name' => 'Product Spotlight',
                'description' => 'Highlight your product in spotlight section',
                'price' => 25.00, // per week
                'duration' => 7 // days
            ],
            Promotion::TYPE_DISCOUNT => [
                'name' => 'Special Discount',
                'description' => 'Run a discount promotion',
                'price' => 5.00, // flat fee
                'duration' => 7 // days
            ],
            Promotion::TYPE_FLASH_SALE => [
                'name' => 'Flash Sale',
                'description' => 'Run a flash sale promotion',
                'price' => 15.00, // flat fee
                'duration' => 3 // days
            ]
        ];

        return view('vendor.promotions.create', compact('listings', 'promotionTypes'));
    }

    /**
     * Store new promotion
     */
    public function store(Request $request)
    {
        $vendor = Auth::user()->vendorProfile;
        
        if (!$vendor) {
            return redirect()->route('vendor.onboard.create');
        }

        $validated = $request->validate([
            'type' => 'required|in:featured,spotlight,discount,flash_sale',
            'listing_id' => 'required|exists:listings,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'duration' => 'required|integer|min:1|max:30', // days
            'starts_at' => 'required|date|after_or_equal:today',
            'ends_at' => 'required|date|after:starts_at',
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_type' => 'nullable|in:percentage,fixed',
        ]);

        // Calculate promotion fee based on type
        $fee = $this->calculatePromotionFee(
            $validated['type'],
            $validated['duration']
        );

        // Calculate ends_at if not provided
        if (empty($validated['ends_at'])) {
            $validated['ends_at'] = Carbon::parse($validated['starts_at'])
                ->addDays($validated['duration']);
        }

        // Store discount info in meta
        $meta = [];
        if ($validated['type'] == Promotion::TYPE_DISCOUNT || $validated['type'] == Promotion::TYPE_FLASH_SALE) {
            $meta['discount'] = [
                'amount' => $validated['discount_amount'] ?? null,
                'type' => $validated['discount_type'] ?? null,
            ];
        }

        DB::beginTransaction();
        try {
            $promotion = Promotion::create([
                'vendor_profile_id' => $vendor->id,
                'listing_id' => $validated['listing_id'],
                'type' => $validated['type'],
                'title' => $validated['title'],
                'description' => $validated['description'],
                'fee' => $fee,
                'starts_at' => $validated['starts_at'],
                'ends_at' => $validated['ends_at'],
                'is_active' => true,
                'meta' => $meta
            ]);

            DB::commit();

            return redirect()->route('vendor.promotions.index')
                ->with('success', 'Promotion created successfully! It will be reviewed by admin.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create promotion: ' . $e->getMessage());
        }
    }

    /**
     * Show promotion details
     */
    public function show(Promotion $promotion)
    {
        // Check ownership
        if ($promotion->vendor_profile_id !== Auth::user()->vendorProfile->id) {
            abort(403);
        }

        $promotion->load(['listing.images', 'vendor.user']);

        return view('vendor.promotions.show', compact('promotion'));
    }

    /**
     * Cancel promotion
     */
    public function cancel(Promotion $promotion)
    {
        // Check ownership
        if ($promotion->vendor_profile_id !== Auth::user()->vendorProfile->id) {
            abort(403);
        }

        DB::beginTransaction();
        try {
            $promotion->update([
                'is_active' => false,
                'meta' => array_merge($promotion->meta ?? [], [
                    'cancelled_at' => now()->toDateTimeString(),
                    'cancelled_by' => Auth::id()
                ])
            ]);

            DB::commit();

            return back()->with('success', 'Promotion cancelled successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to cancel promotion.');
        }
    }

    /**
     * Extend promotion
     */
    public function extend(Request $request, Promotion $promotion)
    {
        // Check ownership
        if ($promotion->vendor_profile_id !== Auth::user()->vendorProfile->id) {
            abort(403);
        }

        $request->validate([
            'extension_days' => 'required|integer|min:1|max:30',
        ]);

        // Check if promotion is active or can be extended
        if ($promotion->ends_at < now()) {
            return back()->with('error', 'Cannot extend expired promotion.');
        }

        $newEndDate = Carbon::parse($promotion->ends_at)
            ->addDays($request->extension_days);

        $extensionFee = $this->calculateExtensionFee(
            $promotion->type,
            $request->extension_days
        );

        DB::beginTransaction();
        try {
            $promotion->update([
                'ends_at' => $newEndDate,
                'fee' => $promotion->fee + $extensionFee,
                'meta' => array_merge($promotion->meta ?? [], [
                    'extensions' => array_merge($promotion->meta['extensions'] ?? [], [
                        'extended_at' => now()->toDateTimeString(),
                        'extension_days' => $request->extension_days,
                        'extension_fee' => $extensionFee,
                    ])
                ])
            ]);

            DB::commit();

            return back()->with('success', "Promotion extended by {$request->extension_days} days for \${$extensionFee}.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to extend promotion.');
        }
    }

    /**
     * Calculate promotion fee
     */
    private function calculatePromotionFee($type, $duration)
    {
        $basePrices = [
            Promotion::TYPE_FEATURED => 10.00, // per week
            Promotion::TYPE_SPOTLIGHT => 25.00, // per week
            Promotion::TYPE_DISCOUNT => 5.00, // flat fee
            Promotion::TYPE_FLASH_SALE => 15.00, // flat fee
        ];

        $basePrice = $basePrices[$type] ?? 5.00;

        // For weekly promotions, calculate based on duration
        if (in_array($type, [Promotion::TYPE_FEATURED, Promotion::TYPE_SPOTLIGHT])) {
            $weeks = ceil($duration / 7);
            return round($basePrice * $weeks, 2);
        }

        // For flat fee promotions
        return $basePrice;
    }

    /**
     * Calculate extension fee
     */
    private function calculateExtensionFee($type, $days)
    {
        $weeklyRate = [
            Promotion::TYPE_FEATURED => 10.00,
            Promotion::TYPE_SPOTLIGHT => 25.00,
            Promotion::TYPE_DISCOUNT => 5.00,
            Promotion::TYPE_FLASH_SALE => 15.00,
        ];

        $rate = $weeklyRate[$type] ?? 5.00;

        if (in_array($type, [Promotion::TYPE_FEATURED, Promotion::TYPE_SPOTLIGHT])) {
            $weeks = ceil($days / 7);
            return round($rate * $weeks, 2);
        }

        return $rate;
    }

    /**
     * Get promotion statistics
     */
    public function statistics()
    {
        $vendor = Auth::user()->vendorProfile;
        
        if (!$vendor) {
            return response()->json(['error' => 'No vendor profile'], 403);
        }

        $promotions = Promotion::where('vendor_profile_id', $vendor->id)
            ->where('is_active', true)
            ->where('ends_at', '>=', now())
            ->with('listing')
            ->get();

        $stats = [
            'total_active' => $promotions->where('status', 'active')->count(),
            'total_pending' => $promotions->where('status', 'pending')->count(),
            'total_spent' => Promotion::where('vendor_profile_id', $vendor->id)
                ->where('is_active', true)
                ->sum('fee'),
            'monthly_spent' => Promotion::where('vendor_profile_id', $vendor->id)
                ->where('is_active', true)
                ->whereMonth('created_at', now()->month)
                ->sum('fee'),
        ];

        // Performance data (simulated - in real app, track views/clicks)
        $performance = [];
        foreach ($promotions as $promotion) {
            $performance[] = [
                'title' => $promotion->title,
                'type' => $promotion->type_label,
                'views' => rand(100, 1000), // simulated
                'clicks' => rand(10, 100), // simulated
                'conversion' => rand(1, 10), // simulated
            ];
        }

        return response()->json([
            'stats' => $stats,
            'performance' => $performance,
            'active_promotions' => $promotions->count(),
        ]);
    }
}