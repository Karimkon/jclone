<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorWithdrawal extends Model
{
    protected $fillable = [
        'vendor_profile_id',
        'amount',
        'fee',
        'net_amount',
        'method',
        'account_details',
        'status',
        'transaction_id',
        'rejection_reason',
        'processed_at',
        'completed_at',
        'meta',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'account_details' => 'array',
        'meta' => 'array',
        'processed_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_REJECTED = 'rejected';
    const STATUS_CANCELLED = 'cancelled';

    // Method constants
    const METHOD_BANK = 'bank_transfer';
    const METHOD_MOBILE = 'mobile_money';
    const METHOD_PAYPAL = 'paypal';

    public function vendor()
    {
        return $this->belongsTo(VendorProfile::class, 'vendor_profile_id');
    }

    /**
     * Calculate withdrawal fee
     */
    public static function calculateFee($amount, $method)
    {
        $fees = [
            self::METHOD_BANK => 2.50, // Fixed fee for bank transfer
            self::METHOD_MOBILE => 0.02, // 2% for mobile money
            self::METHOD_PAYPAL => 0.035, // 3.5% for PayPal
        ];

        $feeRate = $fees[$method] ?? 0.03; // Default 3%
        
        if ($method === self::METHOD_BANK) {
            return $feeRate; // Fixed fee
        } else {
            return round($amount * $feeRate, 2); // Percentage
        }
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_PROCESSING => 'Processing',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_CANCELLED => 'Cancelled',
        ];

        return $labels[$this->status] ?? ucfirst($this->status);
    }

    /**
     * Get method label
     */
    public function getMethodLabelAttribute()
    {
        $labels = [
            self::METHOD_BANK => 'Bank Transfer',
            self::METHOD_MOBILE => 'Mobile Money',
            self::METHOD_PAYPAL => 'PayPal',
        ];

        return $labels[$this->method] ?? ucfirst($this->method);
    }

    /**
     * Check if withdrawal can be cancelled
     */
    public function canBeCancelled()
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_PROCESSING]);
    }

    /**
     * Mark as processing
     */
    public function markAsProcessing()
    {
        $this->update([
            'status' => self::STATUS_PROCESSING,
            'processed_at' => now(),
        ]);
    }

    /**
     * Mark as completed
     */
    public function markAsCompleted($transactionId = null)
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'transaction_id' => $transactionId,
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark as rejected
     */
    public function markAsRejected($reason)
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'rejection_reason' => $reason,
        ]);
    }
}