<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Category;
use App\Models\VendorProfile;
use App\Models\ListingImage;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class AdminListingController extends Controller
{
    /**
     * Display all listings (admin view)
     */
    public function index(Request $request)
    {
        $query = Listing::with(['vendor.user', 'category', 'images']);
        
        // Search filter
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhereHas('vendor.user', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('vendor', function($q) use ($search) {
                      $q->where('business_name', 'like', "%{$search}%");
                  });
            });
        }
        
        // Status filter
        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }
        
        // Featured filter
        if ($request->has('featured')) {
            $query->where('is_featured', $request->featured === 'true');
        }
        
        // Category filter
        if ($request->has('category') && $request->category) {
            $query->where('category_id', $request->category);
        }
        
        // Vendor filter
        if ($request->has('vendor') && $request->vendor) {
            $query->where('vendor_profile_id', $request->vendor);
        }
        
        // Origin filter
        if ($request->has('origin') && $request->origin) {
            $query->where('origin', $request->origin);
        }
        
        // Sort options
        $sort = $request->get('sort', 'newest');
        switch ($sort) {
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'stock_low':
                $query->orderBy('stock', 'asc');
                break;
            case 'stock_high':
                $query->orderBy('stock', 'desc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }
        
        $listings = $query->paginate(20);
        
        // Statistics
        $stats = [
            'total' => Listing::count(),
            'active' => Listing::where('is_active', true)->count(),
            'inactive' => Listing::where('is_active', false)->count(),
            'featured' => Listing::where('is_active', true)->count(),
            'out_of_stock' => Listing::where('stock', 0)->where('is_active', true)->count(),
            'imported' => Listing::where('origin', 'imported')->count(),
            'local' => Listing::where('origin', 'local')->count(),
        ];
        
        // Filter options
        $categories = Category::where('is_active', true)->get();
        $vendors = VendorProfile::where('vetting_status', 'approved')->with('user')->get();
        
        return view('admin.listings.index', compact('listings', 'stats', 'categories', 'vendors'));
    }

    /**
     * Show create listing form for admin
     */
    public function create()
    {
        $categories = Category::where('is_active', true)
            ->with(['children' => function($query) {
                $query->where('is_active', true)
                    ->with(['children' => function($q) {
                        $q->where('is_active', true);
                    }]);
            }])
            ->whereNull('parent_id')
            ->get();
            
        $vendors = VendorProfile::where('vetting_status', 'approved')
            ->with('user')
            ->get();
            
        return view('admin.listings.create', compact('categories', 'vendors'));
    }

    /**
     * Store new listing as admin
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'category_id' => 'required|exists:categories,id',
            'vendor_profile_id' => 'required|exists:vendor_profiles,id',
            'price' => 'required|numeric|min:0',
            'compare_at_price' => 'nullable|numeric|min:0|gt:price',
            'weight_kg' => 'required|numeric|min:0',
            'weight_lbs' => 'nullable|numeric|min:0',
            'origin' => 'required|in:local,imported',
            'condition' => 'required|in:new,used,refurbished',
            'stock' => 'required|integer|min:0',
            'sku' => 'nullable|string|max:100|unique:listings,sku',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            
            // Attributes
            'attributes.brand' => 'nullable|string|max:100',
            'attributes.model' => 'nullable|string|max:100',
            'attributes.color' => 'nullable|string|max:50',
            'attributes.size' => 'nullable|string|max:50',
            'attributes.material' => 'nullable|string|max:100',
            'attributes.warranty' => 'nullable|string|max:100',
            'attributes.dimensions' => 'nullable|string|max:100',
            
            // Images
            'images' => 'required|array|min:1|max:8',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        DB::beginTransaction();
        try {
            // Generate SKU if not provided
            if (empty($validated['sku'])) {
                $validated['sku'] = 'ADMIN-' . strtoupper(Str::random(8));
            }

            // Prepare attributes
            $attributes = [];
            if ($request->has('attributes')) {
                $attributes = array_filter($request->input('attributes'));
            }

            // Create listing
            $listing = Listing::create([
                'vendor_profile_id' => $validated['vendor_profile_id'],
                'title' => $validated['title'],
                'description' => $validated['description'],
                'category_id' => $validated['category_id'],
                'price' => $validated['price'],
                'compare_at_price' => $validated['compare_at_price'] ?? null,
                'weight_kg' => $validated['weight_kg'],
                'weight_lbs' => $validated['weight_lbs'] ?? null,
                'origin' => $validated['origin'],
                'condition' => $validated['condition'],
                'stock' => $validated['stock'],
                'sku' => $validated['sku'],
                'attributes' => $attributes,
                'is_active' => $validated['is_active'] ?? true,
                'is_featured' => $validated['is_featured'] ?? false,
                'status' => 'approved', // Admin listings are auto-approved
                'created_by_admin' => true,
                'admin_notes' => $request->get('admin_notes'),
            ]);

            // Upload images
            foreach ($request->file('images') as $index => $image) {
                $path = $image->store('listings/' . $listing->id, 'public');
                
                ListingImage::create([
                    'listing_id' => $listing->id,
                    'path' => $path,
                    'order' => $index,
                    'uploaded_by_admin' => true,
                ]);
            }

            // Log the action
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'admin_listing_created',
                'model' => 'Listing',
                'model_id' => $listing->id,
                'new_values' => $listing->toArray(),
                'ip' => $request->ip(),
            ]);

            // Notify vendor if admin created listing for them
            if ($listing->vendor && $listing->vendor->user_id !== auth()->id()) {
                \App\Models\NotificationQueue::create([
                    'user_id' => $listing->vendor->user_id,
                    'type' => 'listing_created_by_admin',
                    'title' => 'New Product Listing Added',
                    'message' => "An admin has added a new product listing '{$listing->title}' to your store.",
                    'meta' => ['listing_id' => $listing->id],
                    'status' => 'pending',
                ]);
            }

            DB::commit();

            return redirect()->route('admin.listings.index')
                ->with('success', 'Product listing created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Admin listing creation failed: ' . $e->getMessage());
            
            return back()->withInput()->with('error', 'Failed to create listing: ' . $e->getMessage());
        }
    }

    /**
     * Show listing details (admin view)
     */
    public function show(Listing $listing)
    {
        $listing->load(['vendor.user', 'category', 'images', 'reviews']);
        
        // Sales statistics (you can expand this)
        $salesData = [
            'total_orders' => $listing->orderItems()->count(),
            'total_quantity' => $listing->orderItems()->sum('quantity'),
            'total_revenue' => $listing->orderItems()->sum('line_total'),
        ];
        
        return view('admin.listings.show', compact('listing', 'salesData'));
    }

    /**
     * Show edit listing form for admin
     */
    public function edit(Listing $listing)
    {
        $listing->load(['images', 'vendor.user', 'category']);
        
        $categories = Category::where('is_active', true)
            ->with(['children' => function($query) {
                $query->where('is_active', true)
                    ->with(['children' => function($q) {
                        $q->where('is_active', true);
                    }]);
            }])
            ->whereNull('parent_id')
            ->get();
            
        $vendors = VendorProfile::where('vetting_status', 'approved')
            ->with('user')
            ->get();
            
        return view('admin.listings.edit', compact('listing', 'categories', 'vendors'));
    }

    /**
     * Update listing as admin
     */
    public function update(Request $request, Listing $listing)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'category_id' => 'required|exists:categories,id',
            'vendor_profile_id' => 'required|exists:vendor_profiles,id',
            'price' => 'required|numeric|min:0',
            'compare_at_price' => 'nullable|numeric|min:0|gt:price',
            'weight_kg' => 'required|numeric|min:0',
            'weight_lbs' => 'nullable|numeric|min:0',
            'origin' => 'required|in:local,imported',
            'condition' => 'required|in:new,used,refurbished',
            'stock' => 'required|integer|min:0',
            'sku' => 'nullable|string|max:100|unique:listings,sku,' . $listing->id,
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            
            // Attributes
            'attributes.brand' => 'nullable|string|max:100',
            'attributes.model' => 'nullable|string|max:100',
            'attributes.color' => 'nullable|string|max:50',
            'attributes.size' => 'nullable|string|max:50',
            'attributes.material' => 'nullable|string|max:100',
            'attributes.warranty' => 'nullable|string|max:100',
            'attributes.dimensions' => 'nullable|string|max:100',
            
            // New images
            'new_images' => 'nullable|array|max:8',
            'new_images.*' => 'image|mimes:jpg,jpeg,png,webp|max:5120',
            
            // Image order
            'image_order' => 'nullable|array',
            
            // Admin notes
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        // Store old values for audit log
        $oldValues = $listing->toArray();

        DB::beginTransaction();
        try {
            // Prepare attributes
            $attributes = [];
            if ($request->has('attributes')) {
                $attributes = array_filter($request->input('attributes'));
            }

            // Update listing
            $listing->update([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'category_id' => $validated['category_id'],
                'vendor_profile_id' => $validated['vendor_profile_id'],
                'price' => $validated['price'],
                'compare_at_price' => $validated['compare_at_price'] ?? null,
                'weight_kg' => $validated['weight_kg'],
                'weight_lbs' => $validated['weight_lbs'] ?? null,
                'origin' => $validated['origin'],
                'condition' => $validated['condition'],
                'stock' => $validated['stock'],
                'sku' => $validated['sku'] ?? $listing->sku,
                'attributes' => $attributes,
                'is_active' => $validated['is_active'] ?? $listing->is_active,
                'is_featured' => $validated['is_featured'] ?? $listing->is_featured,
                'admin_notes' => $request->get('admin_notes', $listing->admin_notes),
                'last_updated_by_admin' => now(),
            ]);

            // Handle image order
            if ($request->has('image_order')) {
                foreach ($request->image_order as $imageId => $order) {
                    ListingImage::where('id', $imageId)
                        ->where('listing_id', $listing->id)
                        ->update(['order' => $order]);
                }
            }

            // Add new images
            if ($request->hasFile('new_images')) {
                $currentMaxOrder = $listing->images()->max('order') ?? -1;
                
                foreach ($request->file('new_images') as $image) {
                    $currentMaxOrder++;
                    $path = $image->store('listings/' . $listing->id, 'public');
                    
                    ListingImage::create([
                        'listing_id' => $listing->id,
                        'path' => $path,
                        'order' => $currentMaxOrder,
                        'uploaded_by_admin' => true,
                    ]);
                }
            }

            // Delete images if requested
            if ($request->has('delete_images')) {
                foreach ($request->delete_images as $imageId) {
                    $image = ListingImage::find($imageId);
                    if ($image && $image->listing_id === $listing->id) {
                        Storage::delete('public/' . $image->path);
                        $image->delete();
                    }
                }
            }

            // Log the action
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'admin_listing_updated',
                'model' => 'Listing',
                'model_id' => $listing->id,
                'old_values' => $oldValues,
                'new_values' => $listing->toArray(),
                'ip' => $request->ip(),
            ]);

            // Notify vendor if listing ownership changed or important updates
            if ($oldValues['vendor_profile_id'] != $listing->vendor_profile_id) {
                \App\Models\NotificationQueue::create([
                    'user_id' => $listing->vendor->user_id,
                    'type' => 'listing_transferred',
                    'title' => 'Product Transferred to Your Store',
                    'message' => "The product '{$listing->title}' has been transferred to your store by admin.",
                    'meta' => ['listing_id' => $listing->id],
                    'status' => 'pending',
                ]);
            }

            DB::commit();

            return redirect()->route('admin.listings.index')
                ->with('success', 'Product listing updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Admin listing update failed: ' . $e->getMessage());
            
            return back()->withInput()->with('error', 'Failed to update listing: ' . $e->getMessage());
        }
    }

    /**
     * Delete listing as admin
     */
    public function destroy(Listing $listing)
    {
        DB::beginTransaction();
        try {
            // Store for logging
            $listingData = $listing->toArray();
            
            // Delete images
            foreach ($listing->images as $image) {
                Storage::delete('public/' . $image->path);
                $image->delete();
            }
            
            // Delete the listing
            $listing->delete();
            
            // Log the action
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'admin_listing_deleted',
                'model' => 'Listing',
                'model_id' => $listing->id,
                'old_values' => $listingData,
                'ip' => request()->ip(),
            ]);

            DB::commit();

            return redirect()->route('admin.listings.index')
                ->with('success', 'Product listing deleted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Admin listing deletion failed: ' . $e->getMessage());
            
            return back()->with('error', 'Failed to delete listing: ' . $e->getMessage());
        }
    }

    /**
     * Toggle listing active status
     */
    public function toggleStatus(Listing $listing)
    {
        $oldStatus = $listing->is_active;
        $newStatus = !$oldStatus;
        
        $listing->update(['is_active' => $newStatus]);
        
        // Log the action
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'admin_listing_status_toggled',
            'model' => 'Listing',
            'model_id' => $listing->id,
            'old_values' => ['is_active' => $oldStatus],
            'new_values' => ['is_active' => $newStatus],
            'ip' => request()->ip(),
        ]);
        
        $statusText = $newStatus ? 'activated' : 'deactivated';
        return back()->with('success', "Product listing {$statusText} successfully.");
    }

    /**
     * Toggle featured status
     */
    public function toggleFeatured(Listing $listing)
    {
        $oldStatus = $listing->is_featured;
        $newStatus = !$oldStatus;
        
        $listing->update(['is_featured' => $newStatus]);
        
        // Log the action
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'admin_listing_featured_toggled',
            'model' => 'Listing',
            'model_id' => $listing->id,
            'old_values' => ['is_featured' => $oldStatus],
            'new_values' => ['is_featured' => $newStatus],
            'ip' => request()->ip(),
        ]);
        
        $statusText = $newStatus ? 'featured' : 'unfeatured';
        return back()->with('success', "Product listing {$statusText} successfully.");
    }

    /**
     * Bulk actions (activate, deactivate, delete, feature, unfeature)
     */
    public function bulkActions(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete,feature,unfeature',
            'listings' => 'required|array',
            'listings.*' => 'exists:listings,id',
        ]);

        $listings = Listing::whereIn('id', $request->listings)->get();
        $action = $request->action;
        
        DB::beginTransaction();
        try {
            switch ($action) {
                case 'activate':
                    $listings->each->update(['is_active' => true]);
                    $message = count($listings) . ' products activated successfully.';
                    break;
                    
                case 'deactivate':
                    $listings->each->update(['is_active' => false]);
                    $message = count($listings) . ' products deactivated successfully.';
                    break;
                    
                case 'delete':
                    foreach ($listings as $listing) {
                        // Delete images
                        foreach ($listing->images as $image) {
                            Storage::delete('public/' . $image->path);
                            $image->delete();
                        }
                        $listing->delete();
                    }
                    $message = count($listings) . ' products deleted successfully.';
                    break;
                    
                case 'feature':
                    $listings->each->update(['is_featured' => true]);
                    $message = count($listings) . ' products featured successfully.';
                    break;
                    
                case 'unfeature':
                    $listings->each->update(['is_featured' => false]);
                    $message = count($listings) . ' products unfeatured successfully.';
                    break;
            }

            // Log bulk action
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'admin_listings_bulk_' . $action,
                'model' => 'Listing',
                'model_id' => null,
                'new_values' => [
                    'action' => $action,
                    'count' => count($listings),
                    'listing_ids' => $request->listings
                ],
                'ip' => $request->ip(),
            ]);

            DB::commit();
            return back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Admin listings bulk action failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to perform bulk action.');
        }
    }

    /**
     * Export listings to CSV
     */
    public function exportCSV(Request $request)
    {
        $listings = Listing::with(['vendor.user', 'category'])
            ->when($request->has('vendor'), function($q) use ($request) {
                $q->where('vendor_profile_id', $request->vendor);
            })
            ->when($request->has('category'), function($q) use ($request) {
                $q->where('category_id', $request->category);
            })
            ->when($request->has('status'), function($q) use ($request) {
                $q->where('is_active', $request->status === 'active');
            })
            ->get();

        $csv = \League\Csv\Writer::createFromString('');
        $csv->insertOne([
            'ID', 'SKU', 'Title', 'Vendor', 'Category', 'Price', 'Stock', 
            'Weight (kg)', 'Origin', 'Condition', 'Active', 'Featured',
            'Created At', 'Updated At'
        ]);

        foreach ($listings as $listing) {
            $csv->insertOne([
                $listing->id,
                $listing->sku,
                $listing->title,
                $listing->vendor->business_name ?? 'N/A',
                $listing->category->name ?? 'N/A',
                $listing->price,
                $listing->stock,
                $listing->weight_kg,
                $listing->origin,
                $listing->condition,
                $listing->is_active ? 'Yes' : 'No',
                $listing->is_featured ? 'Yes' : 'No',
                $listing->created_at->format('Y-m-d H:i:s'),
                $listing->updated_at->format('Y-m-d H:i:s'),
            ]);
        }

        return response((string) $csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="listings_' . date('Y-m-d') . '.csv"',
        ]);
    }

    /**
     * Import listings from CSV
     */
    public function importCSV(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
            'vendor_id' => 'required|exists:vendor_profiles,id',
            'update_existing' => 'boolean',
        ]);

        try {
            $csv = \League\Csv\Reader::createFromPath($request->file('csv_file')->getPathname());
            $csv->setHeaderOffset(0);
            
            $imported = 0;
            $updated = 0;
            $errors = [];
            
            foreach ($csv->getRecords() as $index => $record) {
                try {
                    // Find existing listing by SKU
                    $existing = Listing::where('sku', $record['sku'])->first();
                    
                    if ($existing && $request->update_existing) {
                        // Update existing listing
                        $existing->update([
                            'title' => $record['title'] ?? $existing->title,
                            'description' => $record['description'] ?? $existing->description,
                            'price' => $record['price'] ?? $existing->price,
                            'stock' => $record['stock'] ?? $existing->stock,
                            'weight_kg' => $record['weight_kg'] ?? $existing->weight_kg,
                            'is_active' => isset($record['is_active']) ? $record['is_active'] === 'true' : $existing->is_active,
                        ]);
                        $updated++;
                    } elseif (!$existing) {
                        // Create new listing
                        Listing::create([
                            'vendor_profile_id' => $request->vendor_id,
                            'title' => $record['title'],
                            'description' => $record['description'] ?? '',
                            'sku' => $record['sku'],
                            'price' => $record['price'],
                            'stock' => $record['stock'] ?? 0,
                            'weight_kg' => $record['weight_kg'] ?? 0,
                            'origin' => $record['origin'] ?? 'local',
                            'condition' => $record['condition'] ?? 'new',
                            'is_active' => ($record['is_active'] ?? 'true') === 'true',
                            'category_id' => $record['category_id'] ?? null,
                            'imported_via_csv' => true,
                        ]);
                        $imported++;
                    }
                } catch (\Exception $e) {
                    $errors[] = "Row " . ($index + 1) . ": " . $e->getMessage();
                }
            }
            
            // Log import
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'admin_listings_csv_import',
                'model' => 'Listing',
                'model_id' => null,
                'new_values' => [
                    'imported' => $imported,
                    'updated' => $updated,
                    'errors' => $errors,
                    'vendor_id' => $request->vendor_id,
                ],
                'ip' => $request->ip(),
            ]);
            
            $message = "Import completed. Imported: {$imported}, Updated: {$updated}";
            if (!empty($errors)) {
                $message .= ", Errors: " . count($errors);
            }
            
            return back()->with('success', $message);
            
        } catch (\Exception $e) {
            Log::error('CSV import failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to import CSV: ' . $e->getMessage());
        }
    }
}