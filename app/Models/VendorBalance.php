<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorBalance extends Model
{
    protected $fillable = [
        'vendor_profile_id',
        'balance',
        'pending_balance',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'pending_balance' => 'decimal:2',
    ];

    public function vendor()
    {
        return $this->belongsTo(VendorProfile::class, 'vendor_profile_id');
    }

    public function transactions()
    {
        return $this->hasMany(VendorTransaction::class, 'vendor_profile_id', 'vendor_profile_id');
    }

    public function withdrawals()
    {
        return $this->hasMany(VendorWithdrawal::class, 'vendor_profile_id', 'vendor_profile_id');
    }

    /**
     * Get total available balance (balance - pending withdrawals)
     */
    public function getAvailableBalanceAttribute()
    {
        $pendingWithdrawals = $this->withdrawals()
            ->whereIn('status', ['pending', 'processing'])
            ->sum('amount');

        return max(0, $this->balance - $pendingWithdrawals);
    }

    /**
     * Add to balance
     */
    public function credit($amount, $description, $reference = null, $meta = [])
    {
        $this->balance += $amount;
        $this->save();

        // Log transaction
        VendorTransaction::create([
            'vendor_profile_id' => $this->vendor_profile_id,
            'type' => 'credit',
            'amount' => $amount,
            'balance_before' => $this->balance - $amount,
            'balance_after' => $this->balance,
            'reference' => $reference,
            'description' => $description,
            'meta' => $meta,
        ]);

        return $this;
    }

    /**
     * Deduct from balance
     */
    public function debit($amount, $description, $reference = null, $meta = [])
    {
        if ($this->balance < $amount) {
            throw new \Exception('Insufficient balance');
        }

        $this->balance -= $amount;
        $this->save();

        // Log transaction
        VendorTransaction::create([
            'vendor_profile_id' => $this->vendor_profile_id,
            'type' => 'debit',
            'amount' => $amount,
            'balance_before' => $this->balance + $amount,
            'balance_after' => $this->balance,
            'reference' => $reference,
            'description' => $description,
            'meta' => $meta,
        ]);

        return $this;
    }

    /**
     * Add to pending balance (escrow)
     */
    public function addPending($amount, $orderId = null)
    {
        $this->pending_balance += $amount;
        $this->save();

        return $this;
    }

    /**
     * Release pending balance to available balance
     */
    public function releasePending($amount, $orderId, $commission = 0)
    {
        if ($this->pending_balance < $amount) {
            throw new \Exception('Insufficient pending balance');
        }

        DB::transaction(function () use ($amount, $orderId, $commission) {
            // Reduce pending balance
            $this->pending_balance -= $amount;
            
            // Add to balance minus commission
            $netAmount = $amount - $commission;
            $this->balance += $netAmount;
            
            $this->save();

            // Log transaction for sale
            VendorTransaction::create([
                'vendor_profile_id' => $this->vendor_profile_id,
                'type' => 'sale',
                'amount' => $netAmount,
                'balance_before' => $this->balance - $netAmount,
                'balance_after' => $this->balance,
                'order_id' => $orderId,
                'description' => 'Order #' . $orderId,
                'meta' => [
                    'gross_amount' => $amount,
                    'commission' => $commission,
                    'net_amount' => $netAmount,
                ],
            ]);

            // Log commission separately if > 0
            if ($commission > 0) {
                VendorTransaction::create([
                    'vendor_profile_id' => $this->vendor_profile_id,
                    'type' => 'commission',
                    'amount' => -$commission,
                    'balance_before' => $this->balance + $commission,
                    'balance_after' => $this->balance,
                    'order_id' => $orderId,
                    'description' => 'Platform commission for order #' . $orderId,
                    'meta' => [
                        'rate' => '8%',
                        'order_amount' => $amount,
                    ],
                ]);
            }
        });

        return $this;
    }
}