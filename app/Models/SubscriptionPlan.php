<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'price',
        'billing_cycle',
        'boost_multiplier',
        'max_featured_listings',
        'badge_enabled',
        'badge_text',
        'features',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'boost_multiplier' => 'decimal:2',
        'max_featured_listings' => 'integer',
        'badge_enabled' => 'boolean',
        'features' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get vendor subscriptions for this plan
     */
    public function vendorSubscriptions(): HasMany
    {
        return $this->hasMany(VendorSubscription::class);
    }

    /**
     * Scope to get active plans
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by sort_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Check if this is the free plan
     */
    public function getIsFreePlanAttribute(): bool
    {
        return $this->price == 0 || $this->slug === 'free';
    }

    /**
     * Get yearly price (12 months)
     */
    public function getYearlyPriceAttribute(): float
    {
        if ($this->billing_cycle === 'yearly') {
            return $this->price;
        }
        return $this->price * 12;
    }

    /**
     * Get monthly equivalent price
     */
    public function getMonthlyEquivalentAttribute(): float
    {
        if ($this->billing_cycle === 'monthly') {
            return $this->price;
        }
        return $this->price / 12;
    }
}
