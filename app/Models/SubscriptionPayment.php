<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionPayment extends Model
{
    protected $fillable = [
        'vendor_subscription_id',
        'vendor_profile_id',
        'pesapal_order_tracking_id',
        'pesapal_merchant_reference',
        'amount',
        'currency',
        'status',
        'payment_response',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_response' => 'array',
    ];

    /**
     * Get the vendor subscription
     */
    public function vendorSubscription(): BelongsTo
    {
        return $this->belongsTo(VendorSubscription::class);
    }

    /**
     * Get the vendor profile
     */
    public function vendorProfile(): BelongsTo
    {
        return $this->belongsTo(VendorProfile::class);
    }

    /**
     * Check if payment is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if payment is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if payment failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Mark payment as completed
     */
    public function markAsCompleted(array $response = []): void
    {
        $this->update([
            'status' => 'completed',
            'payment_response' => array_merge($this->payment_response ?? [], $response),
        ]);
    }

    /**
     * Mark payment as failed
     */
    public function markAsFailed(array $response = []): void
    {
        $this->update([
            'status' => 'failed',
            'payment_response' => array_merge($this->payment_response ?? [], $response),
        ]);
    }

    /**
     * Generate unique merchant reference
     */
    public static function generateMerchantReference(): string
    {
        return 'SUB-' . strtoupper(uniqid()) . '-' . time();
    }
}
