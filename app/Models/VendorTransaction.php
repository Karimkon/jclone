<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorTransaction extends Model
{
    protected $fillable = [
        'vendor_profile_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'order_id',
        'payment_id',
        'promotion_id',
        'reference',
        'description',
        'status',
        'meta',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'meta' => 'array',
    ];

    // Transaction types
    const TYPE_SALE = 'sale';
    const TYPE_COMMISSION = 'commission';
    const TYPE_REFUND = 'refund';
    const TYPE_PROMOTION = 'promotion';
    const TYPE_WITHDRAWAL = 'withdrawal';
    const TYPE_DEPOSIT = 'deposit';
    const TYPE_ADJUSTMENT = 'adjustment';

    public function vendor()
    {
        return $this->belongsTo(VendorProfile::class, 'vendor_profile_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }

    /**
     * Get transaction type label
     */
    public function getTypeLabelAttribute()
    {
        $labels = [
            self::TYPE_SALE => 'Sale',
            self::TYPE_COMMISSION => 'Commission',
            self::TYPE_REFUND => 'Refund',
            self::TYPE_PROMOTION => 'Promotion',
            self::TYPE_WITHDRAWAL => 'Withdrawal',
            self::TYPE_DEPOSIT => 'Deposit',
            self::TYPE_ADJUSTMENT => 'Adjustment',
        ];

        return $labels[$this->type] ?? ucfirst($this->type);
    }

    /**
     * Get formatted amount with sign
     */
    public function getFormattedAmountAttribute()
    {
        $sign = in_array($this->type, [self::TYPE_SALE, self::TYPE_DEPOSIT]) ? '+' : '-';
        return $sign . '$' . number_format(abs($this->amount), 2);
    }

    /**
     * Get CSS class for amount display
     */
    public function getAmountColorClassAttribute()
    {
        return in_array($this->type, [self::TYPE_SALE, self::TYPE_DEPOSIT]) 
            ? 'text-green-600' 
            : 'text-red-600';
    }
}