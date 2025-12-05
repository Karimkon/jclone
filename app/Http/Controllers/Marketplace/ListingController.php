<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Category;
use App\Models\VendorProfile;
use App\Models\ListingImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ListingController extends Controller
{
    /**
     * Display public marketplace
     */
    public function indexPublic(Request $request)
    {
        $query = Listing::with(['images', 'vendor', 'category'])
            ->where('is_active', true);
        
        // Search filter
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }
        
        // Category filter
        if ($request->has('category') && $request->category) {
            $query->where('category_id', $request->category);
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
        
        // Sort options
        $sort = $request->get('sort', 'newest');
        switch ($sort) {
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'popular':
                // In real app, you'd sort by views/sales
                $query->orderBy('created_at', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }
        
        $listings = $query->paginate(24);
        $categories = Category::where('is_active', true)->whereNull('parent_id')->get();
        
        return view('marketplace.index', compact('listings', 'categories'));
    }

    /**
     * Display single listing (public)
     */
    public function showPublic(Listing $listing)
    {
        if (!$listing->is_active) {
            abort(404);
        }
        
        $listing->load(['images', 'vendor.user', 'category']);
        
        // Increment views (you can implement this later)
        // $listing->increment('views');
        
        // Get related listings
        $related = Listing::where('category_id', $listing->category_id)
            ->where('id', '!=', $listing->id)
            ->where('is_active', true)
            ->with('images')
            ->take(4)
            ->get();
        
        return view('marketplace.show', compact('listing', 'related'));
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
    public function create()
    {
        $vendor = Auth::user()->vendorProfile;
        if (!$vendor) {
            return redirect()->route('vendor.onboard.create');
        }
        
        $categories = Category::where('is_active', true)->get();
        
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