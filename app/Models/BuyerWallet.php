<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BuyerWallet extends Model
{
    protected $fillable = [
        'user_id', 'balance', 'locked_balance', 'currency', 'meta'
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'locked_balance' => 'decimal:2',
        'meta' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class, 'user_id', 'user_id');
    }

    public function getAvailableBalanceAttribute()
    {
        return $this->balance - $this->locked_balance;
    }

    public function deposit($amount, $description = '', $meta = [])
    {
        return $this->user->walletTransactions()->create([
            'type' => 'deposit',
            'amount' => $amount,
            'balance_before' => $this->balance,
            'balance_after' => $this->balance + $amount,
            'description' => $description,
            'meta' => $meta,
        ]);
    }

    public function withdraw($amount, $description = '', $meta = [])
    {
        if ($this->available_balance < $amount) {
            throw new \Exception('Insufficient balance');
        }

        $transaction = $this->user->walletTransactions()->create([
            'type' => 'withdrawal',
            'amount' => -$amount,
            'balance_before' => $this->balance,
            'balance_after' => $this->balance - $amount,
            'description' => $description,
            'meta' => $meta,
        ]);

        $this->decrement('balance', $amount);
        
        return $transaction;
    }
}