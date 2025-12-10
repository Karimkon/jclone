<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;
use App\Models\BuyerWallet;

class User extends Authenticatable
{
    use HasApiTokens;

    protected $fillable = [
        'name', 'phone', 'email', 'password', 'role', 'is_active', 'meta'
    ];

    protected $casts = [
        'meta' => 'array',
        'is_active' => 'boolean',
    ];

    public function vendorProfile()
    {
        return $this->hasOne(VendorProfile::class);
    }

    public function disputes()
    {
        return $this->hasMany(Dispute::class, 'raised_by');
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
 * Check if user is a vendor
 */
public function isVendor()
{
    return $this->vendorProfile && 
           $this->vendorProfile->vetting_status === 'approved' &&
           in_array($this->role, ['vendor_local', 'vendor_international']);
}

/**
 * Check if user is a buyer
 */
public function isBuyer()
{
    return $this->role === 'buyer';
}

/**
 * Check if user is in vendor onboarding
 */
public function isInVendorOnboarding()
{
    return $this->vendorProfile && 
           $this->vendorProfile->vetting_status !== 'approved';
}

public function cart()
{
    return $this->hasOne(\App\Models\Cart::class);
}

public function wishlists()
{
    return $this->hasMany(\App\Models\Wishlist::class);
}

public function orders()
{
    return $this->hasMany(\App\Models\Order::class, 'buyer_id');
}

// In User.php model
public function buyerWallet()
{
    return $this->hasOne(\App\Models\BuyerWallet::class);
}

public function walletTransactions()
{
    return $this->hasMany(\App\Models\WalletTransaction::class);
}

/**
 * Get user's reviews
 */
public function reviews()
{
    return $this->hasMany(\App\Models\Review::class);
}

/**
 * Get user's review votes
 */
public function reviewVotes()
{
    return $this->hasMany(\App\Models\ReviewVote::class);
}

    
    /**
     * Get total wallet balance
     */
    public function getWalletBalance()
    {
        return $this->buyerWallet()->first()->balance ?? 0.00;
    }
    
    /**
     * Get available balance (excluding held funds)
     */
    public function getAvailableBalance()
    {
        $wallet = $this->buyerWallet()->first();
        if (!$wallet) {
            return 0.00;
        }
        
        return $wallet->balance - $wallet->held_balance;
    }
    
    /**
     * Check if user has enough balance
     */
    public function hasSufficientBalance($amount)
    {
        return $this->getAvailableBalance() >= $amount;
    }

}
