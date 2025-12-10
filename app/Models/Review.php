<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'listing_id',
        'order_id',
        'order_item_id',
        'vendor_profile_id',
        'rating',
        'title',
        'comment',
        'quality_rating',
        'value_rating',
        'shipping_rating',
        'images',
        'status',
        'vendor_response',
        'vendor_responded_at',
        'is_verified_purchase',
        'helpful_count',
        'unhelpful_count',
        'meta',
    ];

    protected $casts = [
        'images' => 'array',
        'meta' => 'array',
        'is_verified_purchase' => 'boolean',
        'vendor_responded_at' => 'datetime',
    ];

    /**
     * Get the user who wrote the review
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the listing being reviewed
     */
    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    /**
     * Get the order associated with this review
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the order item associated with this review
     */
    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    /**
     * Get the vendor profile
     */
    public function vendorProfile()
    {
        return $this->belongsTo(VendorProfile::class);
    }

    /**
     * Get review votes
     */
    public function votes()
    {
        return $this->hasMany(ReviewVote::class);
    }

    /**
     * Scope for approved reviews
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for verified purchases
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified_purchase', true);
    }

    /**
     * Get average rating for a listing
     */
    public static function getAverageRating($listingId)
    {
        return static::where('listing_id', $listingId)
            ->where('status', 'approved')
            ->avg('rating') ?? 0;
    }

    /**
     * Get rating distribution for a listing
     */
    public static function getRatingDistribution($listingId)
    {
        $reviews = static::where('listing_id', $listingId)
            ->where('status', 'approved')
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->pluck('count', 'rating')
            ->toArray();

        $distribution = [];
        for ($i = 5; $i >= 1; $i--) {
            $distribution[$i] = $reviews[$i] ?? 0;
        }

        return $distribution;
    }

    /**
     * Get reviews count for a listing
     */
    public static function getReviewsCount($listingId)
    {
        return static::where('listing_id', $listingId)
            ->where('status', 'approved')
            ->count();
    }

    /**
     * Check if user can review this order item
     */
    public static function canUserReview($userId, $orderItemId)
    {
        // Check if review already exists
        $exists = static::where('user_id', $userId)
            ->where('order_item_id', $orderItemId)
            ->exists();

        if ($exists) {
            return false;
        }

        // Check if the order item belongs to the user and is delivered
        $orderItem = OrderItem::with('order')->find($orderItemId);
        
        if (!$orderItem) {
            return false;
        }

        return $orderItem->order->buyer_id === $userId 
            && $orderItem->order->status === 'delivered';
    }

    /**
     * Get vendor's average rating
     */
    public static function getVendorAverageRating($vendorProfileId)
    {
        return static::where('vendor_profile_id', $vendorProfileId)
            ->where('status', 'approved')
            ->avg('rating') ?? 0;
    }

    /**
     * Get vendor's total reviews count
     */
    public static function getVendorReviewsCount($vendorProfileId)
    {
        return static::where('vendor_profile_id', $vendorProfileId)
            ->where('status', 'approved')
            ->count();
    }

    /**
     * Format time ago
     */
    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Get formatted rating
     */
    public function getFormattedRatingAttribute()
    {
        return number_format($this->rating, 1);
    }

    /**
     * Check if review has images
     */
    public function hasImages()
    {
        return !empty($this->images);
    }

    /**
     * Check if vendor has responded
     */
    public function hasVendorResponse()
    {
        return !empty($this->vendor_response);
    }
}