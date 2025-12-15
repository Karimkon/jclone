<?php

// ============================================
// app/Models/ProductAnalytic.php
// ============================================

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProductAnalytic extends Model
{
    protected $fillable = [
        'listing_id',
        'date',
        'views',
        'clicks',
        'add_to_cart',
        'add_to_wishlist',
        'purchases',
        'shares',
        'conversion_rate',
        'cart_abandon_rate',
        'top_sources'
    ];

    protected $casts = [
        'date' => 'date',
        'top_sources' => 'array',
        'conversion_rate' => 'decimal:2',
        'cart_abandon_rate' => 'decimal:2'
    ];

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    /**
     * Aggregate daily analytics for a listing
     */
    public static function aggregateDaily($listingId, $date = null)
    {
        $date = $date ?? today();
        
        // Get counts by type
        $interactions = ProductInteraction::where('listing_id', $listingId)
            ->whereDate('created_at', $date)
            ->select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();

        // Get top sources
        $topSources = ProductInteraction::where('listing_id', $listingId)
            ->whereDate('created_at', $date)
            ->select('source', DB::raw('count(*) as count'))
            ->groupBy('source')
            ->orderByDesc('count')
            ->limit(5)
            ->pluck('count', 'source')
            ->toArray();

        // Calculate metrics
        $views = $interactions['view'] ?? 0;
        $clicks = $interactions['click'] ?? 0;
        $addToCart = $interactions['add_to_cart'] ?? 0;
        $purchases = $interactions['purchase'] ?? 0;

        $conversionRate = $clicks > 0 ? ($purchases / $clicks) * 100 : 0;
        $cartAbandonRate = $addToCart > 0 ? (($addToCart - $purchases) / $addToCart) * 100 : 0;

        // Update or create record
        return self::updateOrCreate(
            [
                'listing_id' => $listingId,
                'date' => $date
            ],
            [
                'views' => $views,
                'clicks' => $clicks,
                'add_to_cart' => $addToCart,
                'add_to_wishlist' => $interactions['add_to_wishlist'] ?? 0,
                'purchases' => $purchases,
                'shares' => $interactions['share'] ?? 0,
                'conversion_rate' => round($conversionRate, 2),
                'cart_abandon_rate' => round($cartAbandonRate, 2),
                'top_sources' => $topSources
            ]
        );
    }

    /**
     * Get analytics summary for a period
     */
    public static function getSummary($listingId, $startDate, $endDate)
    {
        return self::where('listing_id', $listingId)
            ->whereBetween('date', [$startDate, $endDate])
            ->selectRaw('
                SUM(views) as total_views,
                SUM(clicks) as total_clicks,
                SUM(add_to_cart) as total_cart_adds,
                SUM(add_to_wishlist) as total_wishlist_adds,
                SUM(purchases) as total_purchases,
                SUM(shares) as total_shares,
                AVG(conversion_rate) as avg_conversion_rate,
                AVG(cart_abandon_rate) as avg_cart_abandon_rate
            ')
            ->first();
    }
}