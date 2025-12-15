<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Category;
use App\Models\ListingImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VendorListingController extends Controller
{
    /**
     * Display vendor's listings
     */
    public function index()
    {
        $vendor = Auth::user()->vendorProfile;
        
        $listings = Listing::where('vendor_profile_id', $vendor->id)
            ->with(['images', 'category'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
            
        return view('vendor.listings.index', compact('listings'));
    }

    /**
     * Show create listing form
     */
    public function create()
    {
        $categories = Category::where('is_active', true)->get();
        return view('vendor.listings.create', compact('categories'));
    }

    /**
     * Store new listing
     */
    public function store(Request $request)
{
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'required|string|min:100',
        'category_id' => 'required|exists:categories,id',
        'price' => 'required|numeric|min:0',
        'stock' => 'required|integer|min:0',
        'weight_kg' => 'required|numeric|min:0',
        'origin' => 'required|in:local,imported',
        'condition' => 'required|in:new,used,refurbished',
        'sku' => 'nullable|string|max:100|unique:listings,sku',
        
        // Attributes
        'attributes.brand' => 'nullable|string|max:100',
        'attributes.model' => 'nullable|string|max:100',
        'attributes.color' => 'nullable|string|max:50',
        'attributes.size' => 'nullable|string|max:50',
        
        // Images
        'images' => 'required|array|min:1|max:5',
        'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048',
        
        // Variations - only validate if enable_variations is checked
        'variations' => 'nullable|array',
        'variations.*.sku' => 'nullable|string',
        'variations.*.price' => 'required_with:variations|numeric|min:0',
        'variations.*.sale_price' => 'nullable|numeric|min:0',
        'variations.*.stock' => 'required_with:variations|integer|min:0',
        'variations.*.color' => 'nullable|string',
        'variations.*.size' => 'nullable|string',
        'variations.*.attributes' => 'nullable|array',
    ]);

    $vendor = Auth::user()->vendorProfile;

    // Generate SKU if not provided
    if (empty($validated['sku'])) {
        $validated['sku'] = 'SKU-' . strtoupper(Str::random(10));
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
            'stock' => $validated['stock'],
            'weight_kg' => $validated['weight_kg'],
            'origin' => $validated['origin'],
            'condition' => $validated['condition'],
            'sku' => $validated['sku'],
            'attributes' => $attributes,
            'is_active' => true,
            'has_variations' => $request->has('variations') && count($request->variations) > 0,
        ]);

        // Upload images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $path = $image->store('listings/' . $listing->id, 'public');
                
                ListingImage::create([
                    'listing_id' => $listing->id,
                    'path' => $path,
                    'order' => $index,
                ]);
            }
        }

        // Create variations if enabled
        if ($request->has('variations') && count($request->variations) > 0) {
            foreach ($request->variations as $variationData) {
                // Prepare attributes for variant
                $variantAttributes = [];
                if (isset($variationData['attributes'])) {
                    $variantAttributes = $variationData['attributes'];
                } else {
                    // Build attributes from color/size
                    if (isset($variationData['color']) && $variationData['color']) {
                        $variantAttributes['color'] = $variationData['color'];
                    }
                    if (isset($variationData['size']) && $variationData['size']) {
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
            
            // Update listing to indicate it has variations
            $listing->update(['has_variations' => true]);
        }

        DB::commit();

        return redirect()->route('vendor.listings.index')
            ->with('success', 'Product created successfully!');
            
    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Failed to create product: ' . $e->getMessage());
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
            'description' => 'required|string|min:100',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'weight_kg' => 'required|numeric|min:0',
            'origin' => 'required|in:local,imported',
            'condition' => 'required|in:new,used,refurbished',
            'sku' => 'nullable|string|max:100|unique:listings,sku,' . $listing->id,
            'is_active' => 'boolean',
            
            // Attributes
            'attributes.brand' => 'nullable|string|max:100',
            'attributes.model' => 'nullable|string|max:100',
            'attributes.color' => 'nullable|string|max:50',
            'attributes.size' => 'nullable|string|max:50',
            
            // New images
            'new_images' => 'nullable|array|max:5',
            'new_images.*' => 'image|mimes:jpg,jpeg,png|max:2048',
            
            // Delete images
            'delete_images' => 'nullable|array',
        ]);

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
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'weight_kg' => $validated['weight_kg'],
            'origin' => $validated['origin'],
            'condition' => $validated['condition'],
            'sku' => $validated['sku'] ?? $listing->sku,
            'attributes' => $attributes,
            'is_active' => $validated['is_active'] ?? $listing->is_active,
        ]);

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

        return redirect()->route('vendor.listings.index')
            ->with('success', 'Product updated successfully!');
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

        // Delete images
        foreach ($listing->images as $image) {
            Storage::delete('public/' . $image->path);
            $image->delete();
        }
        
        // Delete the listing
        $listing->delete();
        
        return redirect()->route('vendor.listings.index')
            ->with('success', 'Product deleted successfully!');
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

        return back()->with('success', $message);
    }
}