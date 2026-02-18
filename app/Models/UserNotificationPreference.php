<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserNotificationPreference extends Model
{
    protected $fillable = [
        'user_id', 'push_enabled', 'email_enabled',
        'order_updates', 'promotions', 'recommendations',
        'price_drops', 'cart_reminders',
        'new_orders', 'reviews', 'payouts', 'vendor_tips',
    ];

    protected $casts = [
        'push_enabled' => 'boolean',
        'email_enabled' => 'boolean',
        'order_updates' => 'boolean',
        'promotions' => 'boolean',
        'recommendations' => 'boolean',
        'price_drops' => 'boolean',
        'cart_reminders' => 'boolean',
        'new_orders' => 'boolean',
        'reviews' => 'boolean',
        'payouts' => 'boolean',
        'vendor_tips' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get or create preferences for a user (defaults are all enabled)
     */
    public static function getOrCreate(int $userId): self
    {
        return self::firstOrCreate(['user_id' => $userId]);
    }
}
