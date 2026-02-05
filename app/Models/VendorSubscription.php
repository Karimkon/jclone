<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class VendorSubscription extends Model
{
    protected $fillable = [
        'vendor_profile_id',
        'subscription_plan_id',
        'status',
        'starts_at',
        'expires_at',
        'auto_renew',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'auto_renew' => 'boolean',
    ];

    /**
     * Get the vendor profile
     */
    public function vendorProfile(): BelongsTo
    {
        return $this->belongsTo(VendorProfile::class);
    }

    /**
     * Get the subscription plan
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    /**
     * Get payments for this subscription
     */
    public function payments(): HasMany
    {
        return $this->hasMany(SubscriptionPayment::class);
    }

    /**
     * Scope to get active subscriptions
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where('expires_at', '>', now());
    }

    /**
     * Scope to get subscriptions expiring soon (within days)
     */
    public function scopeExpiringSoon($query, int $days = 7)
    {
        return $query->where('status', 'active')
            ->whereBetween('expires_at', [now(), now()->addDays($days)]);
    }

    /**
     * Scope to get expired subscriptions
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'active')
            ->where('expires_at', '<=', now());
    }

    /**
     * Check if subscription is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active' &&
               $this->expires_at &&
               $this->expires_at->isFuture();
    }

    /**
     * Get days remaining until expiration
     */
    public function daysRemaining(): int
    {
        if (!$this->expires_at || !$this->isActive()) {
            return 0;
        }
        return max(0, now()->diffInDays($this->expires_at, false));
    }

    /**
     * Activate the subscription
     */
    public function activate(): void
    {
        $duration = $this->plan->billing_cycle === 'yearly' ? 365 : 30;

        $this->update([
            'status' => 'active',
            'starts_at' => now(),
            'expires_at' => now()->addDays($duration),
        ]);
    }

    /**
     * Cancel the subscription
     */
    public function cancel(): void
    {
        $this->update([
            'status' => 'cancelled',
            'auto_renew' => false,
        ]);
    }

    /**
     * Mark as expired
     */
    public function markAsExpired(): void
    {
        $this->update(['status' => 'expired']);
    }

    /**
     * Renew the subscription
     */
    public function renew(): void
    {
        $duration = $this->plan->billing_cycle === 'yearly' ? 365 : 30;
        $startFrom = $this->expires_at && $this->expires_at->isFuture()
            ? $this->expires_at
            : now();

        $this->update([
            'status' => 'active',
            'starts_at' => $startFrom,
            'expires_at' => $startFrom->copy()->addDays($duration),
        ]);
    }

    /**
     * Check if subscription is expiring within days
     */
    public function isExpiringSoon(int $days = 7): bool
    {
        return $this->isActive() &&
               $this->daysRemaining() <= $days;
    }
}
