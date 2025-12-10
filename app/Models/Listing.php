<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Listing extends Model {
    use HasFactory;

    protected $fillable = [
        'vendor_profile_id', 'title', 'description', 'sku', 'price', 
        'weight_kg', 'origin', 'condition', 'category_id', 'stock', 
        'attributes', 'is_active'
    ];

    protected $casts = [
        'attributes' => 'array',
        'price' => 'decimal:2',
        'weight_kg' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function vendor() {
        return $this->belongsTo(VendorProfile::class, 'vendor_profile_id');
    }

    // ADD THIS RELATIONSHIP
    public function category() {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function images() {
        return $this->hasMany(ListingImage::class);
    }

    public function scopeImported($query) {
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


}