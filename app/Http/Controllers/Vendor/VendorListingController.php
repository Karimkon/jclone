<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Category;
use App\Models\ListingImage;
use App\Models\ListingVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        // Load only top-level categories with their children hierarchy
        $categories = Category::whereNull('parent_id')
            ->where('is_active', true)
            ->with(['children' => function($query) {
                $query->where('is_active', true)
                    ->with(['children' => function($q) {
                        $q->where('is_active', true);
                    }]);
            }])
            ->orderBy('name')
            ->get();

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

            // Tax / Import charges
            'tax_amount' => 'nullable|numeric|min:0',
            'tax_description' => 'nullable|string|max:255',

            // Attributes
            'attributes.brand' => 'nullable|string|max:100',
            'attributes.model' => 'nullable|string|max:100',
            'attributes.color' => 'nullable|string|max:50',
            'attributes.size' => 'nullable|string|max:50',

            // Media files - accept both images and videos
            'media_files' => 'required|array|min:1|max:5',
            'media_files.*' => 'mimetypes:image/jpeg,image/png,image/jpg,image/webp,video/mp4,video/mpeg,video/quicktime,video/x-msvideo,video/webm|max:20480', // 20MB max for videos

            // Variations
            'variations' => 'nullable|array',
            'variations.*.sku' => 'nullable|string',
            'variations.*.price' => 'required_with:variations|numeric|min:0',
            'variations.*.sale_price' => 'nullable|numeric|min:0',
            'variations.*.stock' => 'required_with:variations|integer|min:0',
            'variations.*.color' => 'nullable|string',
            'variations.*.size' => 'nullable|string',
            'variations.*.attributes' => 'nullable|array',
        ]);

        // Validate that the category is a leaf category (has no children)
        $category = Category::withCount('children')->find($validated['category_id']);
        if ($category && $category->children_count > 0) {
            return back()->withErrors(['category_id' => 'Please select a specific subcategory, not a parent category.'])->withInput();
        }

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
                'tax_amount' => $validated['tax_amount'] ?? 0,
                'tax_description' => $validated['tax_description'] ?? null,
                'stock' => $validated['stock'],
                'weight_kg' => $validated['weight_kg'],
                'origin' => $validated['origin'],
                'condition' => $validated['condition'],
                'sku' => $validated['sku'],
                'attributes' => $attributes,
                'is_active' => true,
                'has_variations' => $request->has('variations') && count($request->variations) > 0,
                'has_video' => false, // Will update if videos are uploaded
            ]);

            // Upload media files (images and videos)
            if ($request->hasFile('media_files')) {
                $hasVideo = false;
                
                foreach ($request->file('media_files') as $index => $file) {
                    $path = $file->store('listings/' . $listing->id, 'public');
                    $mimeType = $file->getMimeType();
                    $isVideo = strpos($mimeType, 'video/') === 0;
                    
                    if ($isVideo) {
                        $hasVideo = true;
                    }
                    
                    // Get video metadata if it's a video
                    $metadata = null;
                    if ($isVideo) {
                        $metadata = [
                            'size' => $file->getSize(),
                            'mime_type' => $mimeType,
                            'original_name' => $file->getClientOriginalName(),
                        ];
                    }
                    
                    ListingImage::create([
                        'listing_id' => $listing->id,
                        'path' => $path,
                        'type' => $isVideo ? 'video' : 'image',
                        'order' => $index,
                        'is_main' => $index === 0,
                        'metadata' => $metadata,
                    ]);
                }
                
                // Update listing if it has videos
                if ($hasVideo) {
                    $listing->update(['has_video' => true]);
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
            }

            DB::commit();

            return redirect()->route('vendor.listings.index')
                ->with('success', 'Product created successfully!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to create product: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
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

        // Load only top-level categories with their children hierarchy
        $categories = Category::whereNull('parent_id')
            ->where('is_active', true)
            ->with(['children' => function($query) {
                $query->where('is_active', true)
                    ->with(['children' => function($q) {
                        $q->where('is_active', true);
                    }]);
            }])
            ->orderBy('name')
            ->get();

        $listing->load('media'); // Changed from 'images' to 'media' to load all files

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
            
            // Tax / Import charges
            'tax_amount' => 'nullable|numeric|min:0',
            'tax_description' => 'nullable|string|max:255',

            // Attributes
            'attributes.brand' => 'nullable|string|max:100',
            'attributes.model' => 'nullable|string|max:100',
            'attributes.color' => 'nullable|string|max:50',
            'attributes.size' => 'nullable|string|max:50',

            // New images/videos
            'new_media' => 'nullable|array|max:5',
            'new_media.*' => 'mimetypes:image/jpeg,image/png,image/jpg,image/webp,video/mp4,video/mpeg,video/quicktime|max:20480',
            
            // Delete media
            'delete_media' => 'nullable|array',
        ]);

        // Validate that the category is a leaf category (has no children)
        $category = Category::withCount('children')->find($validated['category_id']);
        if ($category && $category->children_count > 0) {
            return back()->withErrors(['category_id' => 'Please select a specific subcategory, not a parent category.'])->withInput();
        }

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
                'tax_amount' => $validated['tax_amount'] ?? 0,
                'tax_description' => $validated['tax_description'] ?? null,
                'stock' => $validated['stock'],
                'weight_kg' => $validated['weight_kg'],
                'origin' => $validated['origin'],
                'condition' => $validated['condition'],
                'sku' => $validated['sku'] ?? $listing->sku,
                'attributes' => $attributes,
                'is_active' => $validated['is_active'] ?? $listing->is_active,
            ]);

            // Delete media if requested
            if ($request->has('delete_media')) {
                foreach ($request->delete_media as $mediaId) {
                    $media = ListingImage::find($mediaId);
                    if ($media && $media->listing_id === $listing->id) {
                        Storage::delete('public/' . $media->path);
                        $media->delete();
                    }
                }
            }

            // Add new media (images and videos)
            if ($request->hasFile('new_media')) {
                $currentMaxOrder = $listing->media()->max('order') ?? -1;
                $hasNewVideo = false;
                
                foreach ($request->file('new_media') as $file) {
                    $currentMaxOrder++;
                    $path = $file->store('listings/' . $listing->id, 'public');
                    $mimeType = $file->getMimeType();
                    $isVideo = strpos($mimeType, 'video/') === 0;
                    
                    if ($isVideo) {
                        $hasNewVideo = true;
                    }
                    
                    // Get video metadata if it's a video
                    $metadata = null;
                    if ($isVideo) {
                        $metadata = [
                            'size' => $file->getSize(),
                            'mime_type' => $mimeType,
                            'original_name' => $file->getClientOriginalName(),
                        ];
                    }
                    
                    ListingImage::create([
                        'listing_id' => $listing->id,
                        'path' => $path,
                        'type' => $isVideo ? 'video' : 'image',
                        'order' => $currentMaxOrder,
                        'metadata' => $metadata,
                    ]);
                }
                
                // Update listing if it has new videos
                if ($hasNewVideo || $listing->has_video) {
                    $listing->update(['has_video' => true]);
                }
            }

            DB::commit();

            return redirect()->route('vendor.listings.index')
                ->with('success', 'Product updated successfully!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to update product: ' . $e->getMessage());
            return back()->with('error', 'Failed to update product: ' . $e->getMessage());
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
            // Delete media
            foreach ($listing->media as $media) {
                Storage::delete('public/' . $media->path);
                $media->delete();
            }
            
            // Delete variants
            $listing->variants()->delete();
            
            // Delete the listing
            $listing->delete();
            
            DB::commit();
            
            return redirect()->route('vendor.listings.index')
                ->with('success', 'Product deleted successfully!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to delete product: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete product: ' . $e->getMessage());
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
                        // Delete media
                        foreach ($listing->media as $media) {
                            Storage::delete('public/' . $media->path);
                            $media->delete();
                        }
                        // Delete variants
                        $listing->variants()->delete();
                        $listing->delete();
                    }
                    $message = 'Products deleted successfully.';
                    break;
            }

            DB::commit();
            
            return back()->with('success', $message);
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed bulk update: ' . $e->getMessage());
            return back()->with('error', 'Failed to perform bulk action: ' . $e->getMessage());
        }
    }
}