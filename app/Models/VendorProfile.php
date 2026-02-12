<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorProfile extends Model
{
    protected $fillable = [
        'user_id',
        'vendor_type',
        'business_name',
        'country',
        'city',
        'address',
        'annual_turnover',
        'preferred_currency',
        'meta',
        'latitude',
        'longitude',
        'uscc',
    ];

    // vetting_status and vetting_notes are set explicitly via approve()/reject() methods only

    protected $casts = [
        'annual_turnover' => 'float',
        'meta' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function documents()
    {
        return $this->hasMany(VendorDocument::class);
    }

    public function listings()
    {
        return $this->hasMany(Listing::class);
    }

    public function scores()
    {
        return $this->hasMany(VendorScore::class);
    }
    
    /**
     * Get the latest vendor score
     */
    public function latestScore()
    {
        return $this->scores()->latest()->first();
    }
    
    /**
     * Check if vendor is approved
     */
    public function isApproved()
    {
        return $this->vetting_status === 'approved';
    }
    
    /**
     * Check if vendor is pending
     */
    public function isPending()
    {
        return $this->vetting_status === 'pending';
    }
    
    /**
     * Get guarantor information
     */
    public function getGuarantorAttribute()
    {
        return $this->meta['guarantor'] ?? null;
    }

    /**
     * Get China verification data from meta
     */
    public function getChinaVerificationAttribute()
    {
        return $this->meta['china_verification'] ?? null;
    }

    /**
     * Get vendor logo from meta
     */
    public function getLogoAttribute()
    {
        $logo = $this->meta['logo'] ?? null;
        if ($logo && !str_starts_with($logo, 'http')) {
            return asset('storage/' . $logo);
        }
        return $logo;
    }

    /**
     * Get vendor banner from meta
     */
    public function getBannerAttribute()
    {
        $banner = $this->meta['banner'] ?? null;
        if ($banner && !str_starts_with($banner, 'http')) {
            return asset('storage/' . $banner);
        }
        return $banner;
    }

    /**
     * Get business description from meta
     */
    public function getBusinessDescriptionAttribute()
    {
        return $this->meta['description'] ?? null;
    }

    /**
     * Get business phone from meta
     */
    public function getBusinessPhoneAttribute()
    {
        return $this->meta['phone'] ?? null;
    }

    /**
     * Get rating attribute (alias for average_rating)
     */
    public function getRatingAttribute()
    {
        return $this->average_rating;
    }

    /**
     * Get total sales (total delivered orders)
     */
    public function getTotalSalesAttribute()
    {
        return Order::where('vendor_profile_id', $this->id)
            ->where('status', 'delivered')
            ->count();
    }

    /**
     * Get reviews count attribute
     */
    public function getReviewsCountAttribute()
    {
        return $this->total_reviews;
    }

public function balance()
{
    return $this->hasOne(VendorBalance::class);
}

public function transactions()
{
    return $this->hasMany(VendorTransaction::class);
}

public function withdrawals()
{
    return $this->hasMany(VendorWithdrawal::class);
}

/**
 * Get or create vendor balance
 */
public function getBalanceRecordAttribute()
{
    if (!$this->balance) {
        return VendorBalance::create([
            'vendor_profile_id' => $this->id,
            'balance' => 0,
            'pending_balance' => 0,
        ]);
    }
    return $this->balance;
}

/**
 * Get available balance
 */
public function getAvailableBalanceAttribute()
{
    return $this->balanceRecord->available_balance ?? 0;
}

/**
 * Get pending balance (in escrow)
 */
public function getPendingBalanceAttribute()
{
    return $this->balanceRecord->pending_balance ?? 0;
}

/**
 * Get reviews for this vendor's products
 */
public function reviews()
{
    return $this->hasMany(\App\Models\Review::class);
}

/**
 * Get vendor's average rating
 */
public function getAverageRatingAttribute()
{
    return $this->reviews()->where('status', 'approved')->avg('rating') ?? 0;
}
/**
 * Get vendor's total review count
 */
public function getTotalReviewsAttribute()
{
    return $this->reviews()->where('status', 'approved')->count();
}

/**
 * Get positive review percentage (4+ stars)
 */
public function getPositiveRatingPercentageAttribute()
{
    $total = $this->reviews()->where('status', 'approved')->count();
    if ($total === 0) return 0;
    
    $positive = $this->reviews()
        ->where('status', 'approved')
        ->where('rating', '>=', 4)
        ->count();
    
    return round(($positive / $total) * 100);
}

public function getPerformanceAttribute()
{
    return VendorPerformance::where('vendor_profile_id', $this->id)->first();
}

/**
 * Get all subscriptions for this vendor
 */
public function subscriptions()
{
    return $this->hasMany(VendorSubscription::class);
}

/**
 * Get the active subscription for this vendor
 */
public function activeSubscription()
{
    return $this->hasOne(VendorSubscription::class)
        ->where('status', 'active')
        ->where('expires_at', '>', now())
        ->latest();
}

/**
 * Get subscription payments
 */
public function subscriptionPayments()
{
    return $this->hasMany(SubscriptionPayment::class);
}

/**
 * Get the current active subscription instance
 */
public function getCurrentSubscriptionAttribute()
{
    return $this->activeSubscription;
}

/**
 * Get the boost multiplier from the active subscription
 */
public function getBoostMultiplier(): float
{
    $subscription = $this->activeSubscription;

    if ($subscription && $subscription->plan) {
        return (float) $subscription->plan->boost_multiplier;
    }

    return 1.0; // Default for free/no subscription
}

/**
 * Get the subscription badge text
 */
public function getSubscriptionBadge(): ?string
{
    $subscription = $this->activeSubscription;

    if ($subscription && $subscription->plan && $subscription->plan->badge_enabled) {
        return $subscription->plan->badge_text;
    }

    return null;
}

/**
 * Check if vendor has an active paid subscription
 */
public function hasPaidSubscription(): bool
{
    $subscription = $this->activeSubscription;

    return $subscription &&
           $subscription->plan &&
           !$subscription->plan->is_free_plan;
}

/**
 * Get subscription plan name
 */
public function getSubscriptionPlanNameAttribute(): string
{
    $subscription = $this->activeSubscription;

    return $subscription?->plan?->name ?? 'Free';
}

/**
 * Get vendor's delivery rating (star-based)
 */
public function getDeliveryRatingAttribute()
{
    $performance = $this->performance;
    
    if (!$performance || $performance->delivered_orders < 5) {
        return 3; // Default 3 stars for new vendors
    }
    
    // Convert score (0-100) to stars (1-5)
    $score = $performance->delivery_score;
    
    if ($score >= 90) return 5;
    if ($score >= 80) return 4;
    if ($score >= 70) return 3;
    if ($score >= 60) return 2;
    return 1;
}

/**
 * Get vendor ranking text
 */
public function getRankingTextAttribute()
{
    $performance = $this->performance;
    
    if (!$performance || $performance->delivered_orders < 5) {
        return 'Not enough data';
    }
    
    $score = $performance->delivery_score;
    
    if ($score >= 90) return 'Excellent';
    if ($score >= 80) return 'Very Good';
    if ($score >= 70) return 'Good';
    if ($score >= 60) return 'Average';
    return 'Needs Improvement';
}
}