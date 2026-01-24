<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;
use App\Models\BuyerWallet;
use App\Models\Conversation;
use App\Models\Message;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable; 

    protected $fillable = [
        'name', 'phone', 'email', 'password', 'role', 'is_active', 'meta',
        'otp_code', 'otp_expires_at', 'is_verified', 'google_id', 'avatar',
        'phone_otp_code', 'phone_otp_expires_at', 'phone_verified', 'phone_verified_at',
        'is_admin_verified', 'admin_verified_at'
    ];

    protected $casts = [
        'meta' => 'array',
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
        'is_admin_verified' => 'boolean',
        'phone_verified' => 'boolean',
        'otp_expires_at' => 'datetime',
        'phone_otp_expires_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'admin_verified_at' => 'datetime',
    ];

    protected $hidden = [
        'password', 'otp_code', 'phone_otp_code', 'remember_token',
    ];

    /**
     * Generate OTP code for email verification
     */
    public function generateOtp()
    {
        $this->otp_code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $this->otp_expires_at = now()->addMinutes(10); // OTP valid for 10 minutes
        $this->save();

        return $this->otp_code;
    }

    /**
     * Verify OTP code
     */
    public function verifyOtp($code)
    {
        if ($this->otp_code !== $code) {
            return false;
        }

        if ($this->otp_expires_at && $this->otp_expires_at->isPast()) {
            return false;
        }

        // Clear OTP and mark as verified
        $this->otp_code = null;
        $this->otp_expires_at = null;
        $this->is_verified = true;
        $this->email_verified_at = now();
        $this->save();

        return true;
    }

    /**
     * Check if OTP is expired
     */
    public function isOtpExpired()
    {
        return $this->otp_expires_at && $this->otp_expires_at->isPast();
    }

    /**
     * Generate OTP code for phone verification (SMS)
     */
    public function generatePhoneOtp()
    {
        $this->phone_otp_code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $this->phone_otp_expires_at = now()->addMinutes(10);
        $this->save();

        return $this->phone_otp_code;
    }

    /**
     * Verify phone OTP code
     */
    public function verifyPhoneOtp($code)
    {
        if ($this->phone_otp_code !== $code) {
            return false;
        }

        if ($this->phone_otp_expires_at && $this->phone_otp_expires_at->isPast()) {
            return false;
        }

        // Clear OTP and mark phone as verified
        $this->phone_otp_code = null;
        $this->phone_otp_expires_at = null;
        $this->phone_verified = true;
        $this->phone_verified_at = now();

        // Also mark user as verified (since phone is now primary verification)
        $this->is_verified = true;
        $this->email_verified_at = now();
        $this->save();

        return true;
    }

    /**
     * Check if phone OTP is expired
     */
    public function isPhoneOtpExpired()
    {
        return $this->phone_otp_expires_at && $this->phone_otp_expires_at->isPast();
    }

    /**
     * Check if phone is verified
     */
    public function isPhoneVerified()
    {
        return $this->phone_verified === true;
    }

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
 * Check if user can buy items (Buyer or Vendor)
 */
public function canBuy()
{
    return in_array($this->role, ['buyer', 'vendor_local', 'vendor_international']);
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

    /**
 * Get all conversations where user is the buyer
 */
public function buyerConversations()
{
    return $this->hasMany(Conversation::class, 'buyer_id');
}

/**
 * Get all conversations where user is the vendor (through vendor profile)
 */
public function vendorConversations()
{
    return $this->hasManyThrough(
        Conversation::class,
        VendorProfile::class,
        'user_id',           // Foreign key on vendor_profiles table
        'vendor_profile_id', // Foreign key on conversations table
        'id',                // Local key on users table
        'id'                 // Local key on vendor_profiles table
    );
}

/**
 * Get all conversations (as buyer or vendor)
 */
public function allConversations()
{
    return Conversation::forUser($this->id);
}

/**
 * Get all messages sent by this user
 */
public function sentMessages()
{
    return $this->hasMany(Message::class, 'sender_id');
}

/**
 * Get total unread message count
 */
public function getUnreadMessageCountAttribute()
{
    return Message::whereHas('conversation', function ($query) {
        $query->forUser($this->id)->active();
    })
    ->where('sender_id', '!=', $this->id)
    ->whereNull('read_at')
    ->count();
}

/**
 * Check if user can start conversation with a vendor
 */
public function canMessageVendor(VendorProfile $vendorProfile): bool
{
    // Can't message yourself
    if ($vendorProfile->user_id === $this->id) {
        return false;
    }
    
    // Vendor must be approved
    if (!$vendorProfile->isApproved()) {
        return false;
    }
    
    return true;
}

/**
 * Relationship with shipping addresses
 */
public function shippingAddresses()
{
    return $this->hasMany(ShippingAddress::class);
}

/**
 * Get default shipping address
 */
public function defaultShippingAddress()
{
    return $this->hasOne(ShippingAddress::class)->where('is_default', true);
}

/**
 * Get or create default shipping address
 */
public function getOrCreateDefaultAddress()
{
    $default = $this->defaultShippingAddress;
    
    if (!$default) {
        // Create from user profile if exists
        $default = $this->shippingAddresses()->create([
            'label' => 'Home',
            'recipient_name' => $this->name,
            'recipient_phone' => $this->phone ?? '',
            'address_line_1' => $this->meta['addresses'][0]['address'] ?? '',
            'city' => $this->meta['addresses'][0]['city'] ?? '',
            'country' => $this->meta['addresses'][0]['country'] ?? 'Uganda',
            'is_default' => true
        ]);
    }
    
    return $default;
}

/**
 * Check if user is an admin
 * 
 * @return bool
 */
public function isAdmin()
{
    return $this->role === 'admin';
}

/**
 * Check if user is a CEO
 * 
 * @return bool
 */
public function isCEO()
{
    return $this->role === 'ceo';
}

/**
 * Check if user has administrative privileges (admin or CEO)
 * 
 * @return bool
 */
public function hasAdminAccess()
{
    return in_array($this->role, ['admin', 'ceo']);
}

/**
 * Check if user is in logistics role
 * 
 * @return bool
 */
public function isLogistics()
{
    return $this->role === 'logistics';
}

/**
 * Check if user is in finance role
 * 
 * @return bool
 */
public function isFinance()
{
    return $this->role === 'finance';
}

/**
 * Get the appropriate dashboard route for this user's role
 * 
 * @return string
 */
public function getDashboardRoute()
{
    $routes = [
        'admin' => 'admin.dashboard',
        'ceo' => 'ceo.dashboard',
        'vendor_local' => 'vendor.dashboard',
        'vendor_international' => 'vendor.dashboard',
        'logistics' => 'logistics.dashboard',
        'finance' => 'finance.dashboard',
        'buyer' => 'welcome',
    ];
    
    return $routes[$this->role] ?? 'welcome';
}

}
