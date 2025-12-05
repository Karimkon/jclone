<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    protected $fillable = [
        'vendor_profile_id',
        'listing_id',
        'type',
        'title',
        'description',
        'fee',
        'starts_at',
        'ends_at',
        'is_active',
        'meta'
    ];

    protected $casts = [
        'meta' => 'array',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
        'fee' => 'decimal:2'
    ];

    protected $dates = [
        'starts_at',
        'ends_at'
    ];

    // Promotion types
    const TYPE_FEATURED = 'featured';
    const TYPE_BANNER = 'banner';
    const TYPE_SPOTLIGHT = 'spotlight';
    const TYPE_DISCOUNT = 'discount';
    const TYPE_FLASH_SALE = 'flash_sale';

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_ACTIVE = 'active';
    const STATUS_EXPIRED = 'expired';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Get the vendor that owns the promotion
     */
    public function vendor()
    {
        return $this->belongsTo(VendorProfile::class, 'vendor_profile_id');
    }

    /**
     * Get the listing that is being promoted
     */
    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    /**
     * Scope for active promotions
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where('starts_at', '<=', now())
                    ->where('ends_at', '>=', now());
    }

    /**
     * Scope for vendor's promotions
     */
    public function scopeForVendor($query, $vendorId)
    {
        return $query->where('vendor_profile_id', $vendorId);
    }

    /**
     * Check if promotion is currently active
     */
    public function isActive()
    {
        return $this->is_active && 
               $this->starts_at <= now() && 
               $this->ends_at >= now();
    }

    /**
     * Get promotion status
     */
    public function getStatusAttribute()
    {
        if (!$this->is_active) {
            return self::STATUS_CANCELLED;
        }

        if (now() < $this->starts_at) {
            return self::STATUS_PENDING;
        }

        if (now() > $this->ends_at) {
            return self::STATUS_EXPIRED;
        }

        return self::STATUS_ACTIVE;
    }

    /**
     * Get promotion type label
     */
    public function getTypeLabelAttribute()
    {
        $labels = [
            self::TYPE_FEATURED => 'Featured Listing',
            self::TYPE_BANNER => 'Homepage Banner',
            self::TYPE_SPOTLIGHT => 'Product Spotlight',
            self::TYPE_DISCOUNT => 'Special Discount',
            self::TYPE_FLASH_SALE => 'Flash Sale'
        ];

        return $labels[$this->type] ?? ucfirst($this->type);
    }

    /**
     * Get promotion cost
     */
    public function getCostAttribute()
    {
        return $this->fee;
    }
}