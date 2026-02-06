<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Category;
use App\Models\VendorProfile;
use App\Models\ListingImage;
use App\Models\ListingVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Services\ListingRankingService;

class ListingController extends Controller
{
    /**
     * Display public marketplace - WITH SUBSCRIPTION RANKING
     */
    public function indexPublic(Request $request)
    {
        $rankingService = app(ListingRankingService::class);

        $query = Listing::with(['images', 'vendor.user', 'vendor.activeSubscription.plan', 'category'])
            ->where('is_active', true)
            ->whereHas('user', fn($q) => $q->where('is_active', true));

        // Search filter - searches products AND vendor/business names
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhereHas('vendor', function ($vq) use ($search) {
                      $vq->where('business_name', 'like', "%{$search}%");
                  });
            });
        }

        // Category filter
        $selectedCategory = null;
        if ($request->has('category') && $request->category) {
            $selectedCategory = Category::find($request->category);
            if ($selectedCategory) {
                // Get ALL descendant category IDs including the selected category
                $categoryIds = $selectedCategory->getDescendantIds();
                $query->whereIn('category_id', $categoryIds);
            }
        }
        // Origin filter
        if ($request->has('origin') && $request->origin) {
            $query->where('origin', $request->origin);
        }

        // Condition filter
        if ($request->has('condition') && $request->condition) {
            $query->where('condition', $request->condition);
        }

        // Price range filter
        if ($request->has('min_price') && $request->min_price) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price') && $request->max_price) {
            $query->where('price', '<=', $request->max_price);
        }

        // Sort options - DEFAULT is now 'recommended' (subscription-boosted ranking)
        $sort = $request->get('sort', 'recommended');
        $page = $request->get('page', 1);
        $perPage = 24;

        if ($sort === 'recommended' || $sort === 'popular') {
            // Use ranking service for subscription-boosted sorting
            $ranked = $rankingService->getRankedListingsPaginated($query, $perPage, $page);
            $listings = new \Illuminate\Pagination\LengthAwarePaginator(
                $ranked['data'],
                $ranked['total'],
                $ranked['per_page'],
                $ranked['current_page'],
                ['path' => $request->url(), 'query' => $request->query()]
            );
        } else {
            // Traditional sorting (price, newest)
            switch ($sort) {
                case 'price_low':
                    $query->orderBy('price', 'asc');
                    break;
                case 'price_high':
                    $query->orderBy('price', 'desc');
                    break;
                case 'newest':
                default:
                    $query->orderBy('created_at', 'desc');
            }
            $listings = $query->paginate($perPage);
        }
         $categories = Category::where('is_active', true)
        ->whereNull('parent_id')
        ->with(['children' => function($query) {
            $query->where('is_active', true);
        }])
        ->orderBy('order')
        ->get();

          $categories->each(function($category) {
        $category->listings_count = $category->total_listings_count;
        
        $category->children->each(function($child) {
            $child->listings_count = $child->total_listings_count;
        });
    });
         $totalProducts = Listing::where('is_active', true)
            ->whereHas('user', fn($q) => $q->where('is_active', true))
            ->count();
        
        return view('marketplace.index', compact('listings', 'categories', 'totalProducts', 'selectedCategory'));
    }

   /**
 * Display single listing (public)
 */
public function showPublic(Listing $listing)
{
    if (!$listing->is_active) {
        abort(404);
    }

    // Check if vendor is deactivated
    if ($listing->user && !$listing->user->is_active) {
        abort(404, 'This product is currently unavailable');
    }

     app(\App\Services\ProductAnalyticsService::class)->trackView(
        $listing->id,
        request()->input('source', 'direct')
    );
    
    $listing->load(['images', 'vendor.user', 'category']);
    
    // Get vendor delivery performance data
    $deliveryPerformance = null;
    $deliveryStats = [
        'score' => 50,
        'avg_time' => 0,
        'on_time_rate' => 0,
        'rating' => 3,
        'delivered_orders' => 0
    ];
    
    if ($listing->vendor) {
        // Use the existing performance relationship
        $deliveryPerformance = $listing->vendor->performance;
        
        if ($deliveryPerformance) {
            $deliveryStats = [
                'score' => $deliveryPerformance->delivery_score ?? 50,
                'avg_time' => $deliveryPerformance->avg_delivery_time_days ?? 0,
                'on_time_rate' => $deliveryPerformance->on_time_delivery_rate ?? 0,
                'delivered_orders' => $deliveryPerformance->delivered_orders ?? 0,
                'rating' => $listing->vendor->delivery_rating // Use your existing accessor
            ];
        } else {
            // No performance record yet, use vendor's delivery_rating accessor
            $deliveryStats['rating'] = $listing->vendor->delivery_rating;
        }
    }
    
    // Get vendor stats for other metrics
    $vendorStats = [
        'rating' => $listing->vendor->average_rating ?? 0,
        'reviews' => $listing->vendor->total_reviews ?? 0,
        'positive' => $listing->vendor->positive_rating_percentage ?? 98,
    ];
    
    // Get review statistics
    $reviewStats = [
        'average' => \App\Models\Review::getAverageRating($listing->id),
        'count' => \App\Models\Review::getReviewsCount($listing->id),
        'distribution' => \App\Models\Review::getRatingDistribution($listing->id),
    ];
    $totalDistribution = array_sum($reviewStats['distribution']) ?: 1;
    
    // Get reviews for this listing
    $reviews = \App\Models\Review::where('listing_id', $listing->id)
        ->where('status', 'approved')
        ->with(['user:id,name', 'votes'])
        ->orderBy('helpful_count', 'desc')
        ->orderBy('created_at', 'desc')
        ->take(5)
        ->get();
    
    // Check if current user can write a review
    $canReview = false;
    $pendingOrderItem = null;
    if (auth()->check()) {
        $pendingOrderItem = \App\Models\OrderItem::whereHas('order', function($q) use ($listing) {
            $q->where('buyer_id', auth()->id())
              ->where('status', 'delivered');
        })
        ->where('listing_id', $listing->id)
        ->whereDoesntHave('review', function($q) {
            $q->where('user_id', auth()->id());
        })
        ->first();
        
        $canReview = $pendingOrderItem !== null;
    }
    
    // Get all variants for this listing
    $variants = $listing->variants->map(function($variant) {
        return [
            'id' => $variant->id,
            'sku' => $variant->sku,
            'price' => $variant->price,
            'sale_price' => $variant->sale_price,
            'stock' => $variant->stock,
            'attributes' => $variant->attributes ?? [],
            'image' => $variant->image ? asset('storage/' . $variant->image) : null,
            'display_price' => $variant->sale_price ?? $variant->price,
            'display_name' => $variant->display_name,
            'in_stock' => $variant->stock > 0
        ];
    });
    
    // Get unique colors and sizes from variants
    $availableColors = $listing->available_colors;
    $availableSizes = $listing->available_sizes;
    
    // Check if product has variations
    $hasVariations = $listing->has_variations;
    
    // Get related listings
    $related = Listing::where('category_id', $listing->category_id)
        ->where('id', '!=', $listing->id)
        ->where('is_active', true)
        ->whereHas('user', fn($q) => $q->where('is_active', true))
        ->with('images')
        ->take(4)
        ->get();

    return view('marketplace.show', compact(
        'listing', 
        'related',
        'reviewStats',
        'totalDistribution',
        'reviews',
        'canReview',
        'pendingOrderItem',
        'vendorStats',
        'variants',
        'availableColors',
        'availableSizes',
        'hasVariations',
        'deliveryPerformance',
        'deliveryStats'
    ));
}


    /**
     * Display vendor's listings
     */
    public function index()
    {
        $vendor = Auth::user()->vendorProfile;
        if (!$vendor) {
            return redirect()->route('vendor.onboard.create');
        }
        
        $listings = $vendor->listings()
            ->with(['images', 'category'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('vendor.listings.index', compact('listings'));
    }

    /**
     * Show create listing form
     */
   // In your controller (ListingController@create)
    public function create()
    {
        $vendor = Auth::user()->vendorProfile;
        if (!$vendor) {
            return redirect()->route('vendor.onboard.create');
        }
        
        // Get categories with hierarchy
        $categories = Category::where('is_active', true)
            ->with(['children' => function($query) {
                $query->where('is_active', true)
                    ->with(['children' => function($q) {
                        $q->where('is_active', true);
                    }])
                    ->orderBy('order');
            }])
            ->whereNull('parent_id')
            ->orderBy('order')
            ->get();
        
        return view('vendor.listings.create', compact('categories'));
    }

    /**
     * Store new listing
     */
    public function store(Request $request)
    {
        $vendor = Auth::user()->vendorProfile;
        if (!$vendor) {
            return redirect()->route('vendor.onboard.create');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'weight_kg' => 'required|numeric|min:0',
            'origin' => 'required|in:local,imported',
            'condition' => 'required|in:new,used,refurbished',
            'stock' => 'required|integer|min:0',
            'sku' => 'nullable|string|max:100|unique:listings,sku',
            
            // Attributes (JSON)
            'attributes.brand' => 'nullable|string|max:100',
            'attributes.model' => 'nullable|string|max:100',
            'attributes.color' => 'nullable|string|max:50',
            'attributes.size' => 'nullable|string|max:50',
            'attributes.material' => 'nullable|string|max:100',
            
            // Images
            'images' => 'required|array|min:1|max:5',
            'images.*' => 'image|mimes:jpg,jpeg,png|max:2048',

            // Variations (optional)
            'variations' => 'nullable|array',
            'variations.*.sku' => 'nullable|string',
            'variations.*.price' => 'required_with:variations|numeric|min:0',
            'variations.*.sale_price' => 'nullable|numeric|min:0',
            'variations.*.stock' => 'required_with:variations|integer|min:0',
            'variations.*.color' => 'nullable|string',
            'variations.*.size' => 'nullable|string',
            'variations.*.attributes' => 'nullable|array',
        ]);

        // Generate SKU if not provided
        if (empty($validated['sku'])) {
            $validated['sku'] = 'SKU-' . strtoupper(Str::random(8));
        }

        // Prepare attributes
        $attributes = [];
        if ($request->has('attributes')) {
            $attributes = array_filter($request->input('attributes'));
        }

        DB::beginTransaction();
        try {
            // Create listing
            $listing = Listing::create([
                'vendor_profile_id' => $vendor->id,
                'title' => $validated['title'],
                'description' => $validated['description'],
                'category_id' => $validated['category_id'],
                'price' => $validated['price'],
                'weight_kg' => $validated['weight_kg'],
                'origin' => $validated['origin'],
                'condition' => $validated['condition'],
                'stock' => $validated['stock'],
                'sku' => $validated['sku'],
                'attributes' => $attributes,
                'is_active' => true,
            ]);

            // Upload images
            foreach ($request->file('images') as $index => $image) {
                $path = $image->store('listings/' . $listing->id, 'public');
                
                ListingImage::create([
                    'listing_id' => $listing->id,
                    'path' => $path,
                    'order' => $index,
                ]);
            }

            // Create variants if provided
            if ($request->has('variations') && is_array($request->variations) && count($request->variations) > 0) {
                foreach ($request->variations as $variationData) {
                    // Build attributes array for the variant
                    $variantAttributes = [];
                    if (isset($variationData['attributes']) && is_array($variationData['attributes'])) {
                        $variantAttributes = $variationData['attributes'];
                    } else {
                        if (!empty($variationData['color'] ?? null)) {
                            $variantAttributes['color'] = $variationData['color'];
                        }
                        if (!empty($variationData['size'] ?? null)) {
                            $variantAttributes['size'] = $variationData['size'];
                        }
                    }

                    ListingVariant::create([
                        'listing_id' => $listing->id,
                        'sku' => $variationData['sku'] ?? null,
                        'price' => $variationData['price'],
                        'sale_price' => $variationData['sale_price'] ?? null,
                        'stock' => $variationData['stock'],
                        'attributes' => $variantAttributes,
                        'is_default' => false,
                        'is_active' => true,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('vendor.listings.index')
                ->with('success', 'Product listed successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create listing: ' . $e->getMessage());
        }
    }

    /**
     * Show edit listing form
     */
    public function edit(Listing $listing)
    {
        // Check ownership
        if ($listing->vendor_profile_id !== Auth::user()->vendorProfile->id) {
            abort(403);
        }
        
        $categories = Category::where('is_active', true)->get();
        $listing->load('images');
        
        return view('vendor.listings.edit', compact('listing', 'categories'));
    }

    /**
     * Update listing
     */
    public function update(Request $request, Listing $listing)
    {
        // Check ownership
        if ($listing->vendor_profile_id !== Auth::user()->vendorProfile->id) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'weight_kg' => 'required|numeric|min:0',
            'origin' => 'required|in:local,imported',
            'condition' => 'required|in:new,used,refurbished',
            'stock' => 'required|integer|min:0',
            'sku' => 'nullable|string|max:100|unique:listings,sku,' . $listing->id,
            'is_active' => 'boolean',
            
            // Attributes
            'attributes.brand' => 'nullable|string|max:100',
            'attributes.model' => 'nullable|string|max:100',
            'attributes.color' => 'nullable|string|max:50',
            'attributes.size' => 'nullable|string|max:50',
            'attributes.material' => 'nullable|string|max:100',
            
            // New images
            'new_images' => 'nullable|array|max:5',
            'new_images.*' => 'image|mimes:jpg,jpeg,png|max:2048',
            
            // Existing image order
            'image_order' => 'nullable|array',
        ]);

        // Prepare attributes
        $attributes = [];
        if ($request->has('attributes')) {
            $attributes = array_filter($request->input('attributes'));
        }

        DB::beginTransaction();
        try {
            // Update listing
            $listing->update([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'category_id' => $validated['category_id'],
                'price' => $validated['price'],
                'weight_kg' => $validated['weight_kg'],
                'origin' => $validated['origin'],
                'condition' => $validated['condition'],
                'stock' => $validated['stock'],
                'sku' => $validated['sku'] ?? $listing->sku,
                'attributes' => $attributes,
                'is_active' => $validated['is_active'] ?? $listing->is_active,
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
                    ]);
                }
            }

             if ($request->has('image_order')) {
            foreach ($request->image_order as $imageId => $order) {
                \App\Models\ListingImage::where('id', $imageId)
                    ->where('listing_id', $listing->id)
                    ->update(['order' => $order]);
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

            DB::commit();

            return redirect()->route('vendor.listings.index')
                ->with('success', 'Product updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update listing: ' . $e->getMessage());
        }
    }

    /**
     * Delete listing
     */
    public function destroy(Listing $listing)
    {
        // Check ownership
        if ($listing->vendor_profile_id !== Auth::user()->vendorProfile->id) {
            abort(403);
        }

        DB::beginTransaction();
        try {
            // Delete images
            foreach ($listing->images as $image) {
                Storage::delete('public/' . $image->path);
                $image->delete();
            }
            
            // Delete the listing
            $listing->delete();
            
            DB::commit();

            return redirect()->route('vendor.listings.index')
                ->with('success', 'Product deleted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete listing: ' . $e->getMessage());
        }
    }

    /**
     * Toggle listing status
     */
    public function toggleStatus(Listing $listing)
    {
        // Check ownership
        if ($listing->vendor_profile_id !== Auth::user()->vendorProfile->id) {
            abort(403);
        }

        $listing->update(['is_active' => !$listing->is_active]);
        
        $status = $listing->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Product {$status} successfully.");
    }

    /**
     * Bulk update listings
     */
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'listings' => 'required|array',
            'listings.*' => 'exists:listings,id',
        ]);

        $vendor = Auth::user()->vendorProfile;
        $listings = Listing::whereIn('id', $request->listings)
            ->where('vendor_profile_id', $vendor->id)
            ->get();

        DB::beginTransaction();
        try {
            switch ($request->action) {
                case 'activate':
                    $listings->each->update(['is_active' => true]);
                    $message = 'Products activated successfully.';
                    break;
                    
                case 'deactivate':
                    $listings->each->update(['is_active' => false]);
                    $message = 'Products deactivated successfully.';
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
                    $message = 'Products deleted successfully.';
                    break;
            }

            DB::commit();
            return back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to perform bulk action: ' . $e->getMessage());
        }
    }
}