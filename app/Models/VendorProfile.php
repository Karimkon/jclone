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
        'vetting_status',
        'vetting_notes',
        'meta',
    ];

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