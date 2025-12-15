<?php
// app/Models/ProductInteraction.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductInteraction extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'listing_id',
        'user_id',
        'session_id',
        'type',
        'source',
        'device_type',
        'browser',
        'ip_address',
        'referrer',
        'meta',
        'created_at'
    ];

    protected $casts = [
        'meta' => 'array',
        'created_at' => 'datetime'
    ];

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Track a product interaction
     */
    public static function track($listingId, $type, $options = [])
    {
        $data = [
            'listing_id' => $listingId,
            'user_id' => auth()->id(),
            'session_id' => session()->getId(),
            'type' => $type,
            'source' => $options['source'] ?? request()->input('source', 'direct'),
            'device_type' => self::getDeviceType(),
            'browser' => request()->userAgent(),
            'ip_address' => request()->ip(),
            'referrer' => request()->header('referer'),
            'meta' => $options['meta'] ?? null,
            'created_at' => now()
        ];

        // Create interaction record
        self::create($data);

        // Update listing counters
        self::updateListingCounters($listingId, $type);
    }

    /**
     * Update listing real-time counters
     */
    private static function updateListingCounters($listingId, $type)
    {
        $listing = Listing::find($listingId);
        if (!$listing) return;

        $updates = ['last_viewed_at' => now()];

        switch ($type) {
            case 'view':
                $updates['view_count'] = $listing->view_count + 1;
                break;
            case 'click':
                $updates['click_count'] = $listing->click_count + 1;
                break;
            case 'add_to_cart':
                $updates['cart_add_count'] = $listing->cart_add_count + 1;
                break;
            case 'add_to_wishlist':
                $updates['wishlist_count'] = $listing->wishlist_count + 1;
                break;
            case 'purchase':
                $updates['purchase_count'] = $listing->purchase_count + 1;
                break;
            case 'share':
                $updates['share_count'] = $listing->share_count + 1;
                break;
        }

        $listing->update($updates);
    }

    /**
     * Get device type from user agent
     */
    private static function getDeviceType()
    {
        $userAgent = request()->userAgent();
        
        if (preg_match('/mobile|android|iphone|ipad|tablet/i', $userAgent)) {
            if (preg_match('/tablet|ipad/i', $userAgent)) {
                return 'tablet';
            }
            return 'mobile';
        }
        
        return 'desktop';
    }
}

