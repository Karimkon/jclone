<?php

namespace App\Observers;

use App\Models\User;
use App\Models\BuyerWallet;

class UserObserver
{
    public function created(User $user)
    {
        // Create wallet for buyers automatically
        if ($user->role === 'buyer') {
            BuyerWallet::create([
                'user_id' => $user->id,
                'balance' => 0,
                'locked_balance' => 0,
                'currency' => 'USD',
                'meta' => [
                    'created_at' => now()->toDateTimeString(),
                    'initial_balance' => 0,
                ]
            ]);
        }
    }

    public function updated(User $user)
    {
        // Handle role changes
        if ($user->isDirty('role') && $user->role === 'buyer') {
            if (!$user->buyerWallet()->exists()) {
                BuyerWallet::create([
                    'user_id' => $user->id,
                    'balance' => 0,
                    'locked_balance' => 0,
                    'currency' => 'USD',
                ]);
            }
        }
    }
}