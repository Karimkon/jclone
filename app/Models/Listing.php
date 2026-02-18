<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Listing extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_profile_id', 'title', 'slug', 'description', 'sku', 'price',
        'compare_at_price', 'tax_amount', 'tax_description',
        'weight_kg', 'origin', 'condition', 'category_id', 'stock',
        'attributes', 'is_active',  'view_count', 'click_count', 'wishlist_count',
        'cart_add_count', 'purchase_count', 'share_count', 'last_viewed_at'
    ];

    protected $casts = [
        'attributes' => 'array',
        'price' => 'decimal:2',
        'compare_at_price' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'weight_kg' => 'decimal:2',
        'is_active' => 'boolean',
        'last_viewed_at' => 'datetime',
    ];

    /**
     * Boot the model - auto-generate slug
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($listing) {
            if (empty($listing->slug)) {
                $listing->slug = $listing->generateUniqueSlug($listing->title);
            }
        });

        static::updating(function ($listing) {
            // Regenerate slug if title changed and slug wasn't manually set
            if ($listing->isDirty('title') && !$listing->isDirty('slug')) {
                $listing->slug = $listing->generateUniqueSlug($listing->title);
            }
        });
    }

    /**
     * Generate a unique slug
     */
    public function generateUniqueSlug(string $title): string
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $counter = 1;

        while (static::where('slug', $slug)->where('id', '!=', $this->id ?? 0)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Get the route key name for Laravel route model binding
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Resolve the model by slug or ID for backward compatibility
     */
    public function resolveRouteBinding($value, $field = null)
    {
        // If field is explicitly set, use it
        if ($field) {
            return $this->where($field, $value)->first();
        }

        // Try slug first, then ID (for backward compatibility with API)
        return $this->where('slug', $value)->first()
            ?? $this->where('id', $value)->first();
    }

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