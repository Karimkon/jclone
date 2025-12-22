<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Listing extends Model 
{
    use HasFactory;

    protected $fillable = [
        'vendor_profile_id', 'title', 'description', 'sku', 'price', 
        'weight_kg', 'origin', 'condition', 'category_id', 'stock', 
        'attributes', 'is_active', 'has_video',  'view_count', 'click_count', 'wishlist_count', 
        'cart_add_count', 'purchase_count', 'share_count', 'last_viewed_at'
    ];

    protected $casts = [
        'attributes' => 'array',
        'price' => 'decimal:2',
        'weight_kg' => 'decimal:2',
        'is_active' => 'boolean',
        'has_video' => 'boolean', 
        'last_viewed_at' => 'datetime',
    ];

    public function vendor() 
    {
        return $this->belongsTo(VendorProfile::class, 'vendor_profile_id');
    }

    public function category() 
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function images() 
    {
        return $this->hasMany(ListingImage::class);
    }

    public function scopeImported($query) 
    {
        return $query->where('origin', 'imported');
    }

    /**
     * Get reviews for this listing
     */
    public function reviews()
    {
        return $this->hasMany(\App\Models\Review::class);
    }

    /**
     * Get approved reviews
     */
    public function approvedReviews()
    {
        return $this->reviews()->where('status', 'approved');
    }

    /**
     * Get average rating
     */
    public function getAverageRatingAttribute()
    {
        return $this->approvedReviews()->avg('rating') ?? 0;
    }

    /**
     * Get reviews count
     */
    public function getReviewsCountAttribute()
    {
        return $this->approvedReviews()->count();
    }

     /**
     * Get all media (images and videos)
     */
    public function media()
    {
        return $this->hasMany(ListingImage::class)->orderBy('order');
    }


    /**
     * Get only videos
     */
    public function videos()
    {
        return $this->hasMany(ListingImage::class)
                    ->orderBy('order')
                    ->where('type', 'video');
    }


     /**
     * Get the main image/video (first one)
     */
    public function getMainMediaAttribute()
    {
        return $this->media()->first();
    }

      /**
     * Get thumbnail URL
     */
    public function getThumbnailUrlAttribute()
    {
        $mainMedia = $this->mainMedia;
        return $mainMedia ? $mainMedia->thumbnail_url : asset('images/default-product.png');
    }

     public function checkHasVideo()
    {
        return $this->media()->where('type', 'video')->exists();
    }

    /**
     * Get product variants
     */
    public function variants()
    {
        return $this->hasMany(ListingVariant::class);
    }

    /**
     * Check if product has variations
     */
    public function getHasVariationsAttribute()
    {
        return $this->variants()->where('stock', '>', 0)->count() > 0;
    }

    /**
     * Get available colors from variants
     */
    public function getAvailableColorsAttribute()
    {
        return $this->variants()
            ->where('stock', '>', 0)
            ->get()
            ->map(function($variant) {
                return $variant->color();
            })
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Get available sizes from variants
     */
    public function getAvailableSizesAttribute()
    {
        return $this->variants()
            ->where('stock', '>', 0)
            ->get()
            ->map(function($variant) {
                return $variant->size();
            })
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Find variant by attributes
     */
    public function findVariantByAttributes($color = null, $size = null)
    {
        return $this->variants()
            ->get()
            ->first(function($variant) use ($color, $size) {
                $colorMatch = !$color || $variant->color() === $color;
                $sizeMatch = !$size || $variant->size() === $size;
                return $colorMatch && $sizeMatch;
            });
    }

    /**
     * Get default variant
     */
    public function getDefaultVariantAttribute()
    {
        return $this->variants()->where('is_default', true)->first()
            ?? $this->variants()->first();
    }

     /**
     * Analytics relationship
     */
    public function interactions()
    {
        return $this->hasMany(\App\Models\ProductInteraction::class);
    }

    public function analytics()
    {
        return $this->hasMany(\App\Models\ProductAnalytic::class);
    }

    /**
     * Get the user that owns this listing through the vendor profile
     */
    public function user()
    {
        return $this->hasOneThrough(
            User::class,
            VendorProfile::class,
            'id',           // Foreign key on vendor_profiles table (vendor_profile.id)
            'id',           // Foreign key on users table (user.id) 
            'vendor_profile_id', // Local key on listings table (listing.vendor_profile_id)
            'user_id'       // Local key on vendor_profiles table (vendor_profile.user_id)
        );
    }
}