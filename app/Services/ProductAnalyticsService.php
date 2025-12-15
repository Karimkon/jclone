<?php
// app/Services/ProductAnalyticsService.php

namespace App\Services;

use App\Models\ProductInteraction;
use App\Models\ProductAnalytic;
use App\Models\Listing;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProductAnalyticsService
{
    /**
     * Track product view
     */
    public function trackView($listingId, $source = 'direct')
    {
        ProductInteraction::track($listingId, 'view', [
            'source' => $source
        ]);
    }

    /**
     * Track product click (e.g., clicked to view details)
     */
    public function trackClick($listingId, $source = 'direct')
    {
        ProductInteraction::track($listingId, 'click', [
            'source' => $source
        ]);
    }

    /**
     * Track add to cart
     */
    public function trackAddToCart($listingId, $quantity = 1)
    {
        ProductInteraction::track($listingId, 'add_to_cart', [
            'meta' => ['quantity' => $quantity]
        ]);
    }

    /**
     * Track add to wishlist
     */
    public function trackAddToWishlist($listingId)
    {
        ProductInteraction::track($listingId, 'add_to_wishlist');
    }

    /**
     * Track purchase
     */
    public function trackPurchase($listingId, $orderId, $quantity = 1, $amount = 0)
    {
        ProductInteraction::track($listingId, 'purchase', [
            'meta' => [
                'order_id' => $orderId,
                'quantity' => $quantity,
                'amount' => $amount
            ]
        ]);
    }

    /**
     * Track product share
     */
    public function trackShare($listingId, $platform = 'unknown')
    {
        ProductInteraction::track($listingId, 'share', [
            'meta' => ['platform' => $platform]
        ]);
    }

    /**
     * Get products with low conversion (clicked but not bought)
     */
    public function getClickedButNotBought($days = 30, $minClicks = 10)
    {
        $startDate = Carbon::now()->subDays($days);

        return Listing::select('listings.*')
            ->selectRaw('
                COUNT(DISTINCT CASE WHEN pi.type = "click" THEN pi.id END) as total_clicks,
                COUNT(DISTINCT CASE WHEN pi.type = "purchase" THEN pi.id END) as total_purchases,
                COUNT(DISTINCT CASE WHEN pi.type = "add_to_cart" THEN pi.id END) as cart_adds,
                ROUND((COUNT(DISTINCT CASE WHEN pi.type = "purchase" THEN pi.id END) / 
                       COUNT(DISTINCT CASE WHEN pi.type = "click" THEN pi.id END) * 100), 2) as conversion_rate
            ')
            ->leftJoin('product_interactions as pi', function($join) use ($startDate) {
                $join->on('listings.id', '=', 'pi.listing_id')
                     ->where('pi.created_at', '>=', $startDate);
            })
            ->groupBy('listings.id')
            ->havingRaw('total_clicks >= ?', [$minClicks])
            ->havingRaw('total_purchases = 0 OR conversion_rate < 5')
            ->orderByDesc('total_clicks')
            ->with('vendor', 'category')
            ->get();
    }

    /**
     * Get cart abandonment insights
     */
    public function getCartAbandonmentInsights($days = 30)
    {
        $startDate = Carbon::now()->subDays($days);

        return Listing::select('listings.*')
            ->selectRaw('
                COUNT(DISTINCT CASE WHEN pi.type = "add_to_cart" THEN pi.id END) as cart_adds,
                COUNT(DISTINCT CASE WHEN pi.type = "purchase" THEN pi.id END) as purchases,
                ROUND((1 - COUNT(DISTINCT CASE WHEN pi.type = "purchase" THEN pi.id END) / 
                       COUNT(DISTINCT CASE WHEN pi.type = "add_to_cart" THEN pi.id END)) * 100, 2) as abandon_rate
            ')
            ->leftJoin('product_interactions as pi', function($join) use ($startDate) {
                $join->on('listings.id', '=', 'pi.listing_id')
                     ->where('pi.created_at', '>=', $startDate);
            })
            ->groupBy('listings.id')
            ->havingRaw('cart_adds > 0')
            ->orderByDesc('abandon_rate')
            ->with('vendor', 'category')
            ->limit(50)
            ->get();
    }

    /**
     * Get trending products (high recent views/clicks)
     */
    public function getTrendingProducts($days = 7, $limit = 20)
    {
        $startDate = Carbon::now()->subDays($days);

        return Listing::select('listings.*')
            ->selectRaw('
                COUNT(DISTINCT CASE WHEN pi.type = "view" THEN pi.id END) as recent_views,
                COUNT(DISTINCT CASE WHEN pi.type = "click" THEN pi.id END) as recent_clicks,
                COUNT(DISTINCT pi.user_id) as unique_visitors
            ')
            ->leftJoin('product_interactions as pi', function($join) use ($startDate) {
                $join->on('listings.id', '=', 'pi.listing_id')
                     ->where('pi.created_at', '>=', $startDate);
            })
            ->where('listings.is_active', true)
            ->where('listings.stock', '>', 0)
            ->groupBy('listings.id')
            ->havingRaw('recent_views > 0')
            ->orderByDesc('recent_clicks')
            ->orderByDesc('recent_views')
            ->with('vendor', 'category', 'images')
            ->limit($limit)
            ->get();
    }

    /**
     * Get product performance dashboard data
     */
    public function getProductPerformance($listingId, $days = 30)
    {
        $startDate = Carbon::now()->subDays($days);
        $listing = Listing::findOrFail($listingId);

        // Get daily breakdown
        $dailyStats = ProductInteraction::where('listing_id', $listingId)
            ->where('created_at', '>=', $startDate)
            ->selectRaw('
                DATE(created_at) as date,
                COUNT(CASE WHEN type = "view" THEN 1 END) as views,
                COUNT(CASE WHEN type = "click" THEN 1 END) as clicks,
                COUNT(CASE WHEN type = "add_to_cart" THEN 1 END) as cart_adds,
                COUNT(CASE WHEN type = "purchase" THEN 1 END) as purchases
            ')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Get source breakdown
        $sourceStats = ProductInteraction::where('listing_id', $listingId)
            ->where('created_at', '>=', $startDate)
            ->select('source', DB::raw('COUNT(*) as count'))
            ->groupBy('source')
            ->orderByDesc('count')
            ->get();

        // Get device breakdown
        $deviceStats = ProductInteraction::where('listing_id', $listingId)
            ->where('created_at', '>=', $startDate)
            ->select('device_type', DB::raw('COUNT(*) as count'))
            ->groupBy('device_type')
            ->orderByDesc('count')
            ->get();

        // Calculate key metrics
        $totalViews = $listing->view_count;
        $totalClicks = $listing->click_count;
        $totalPurchases = $listing->purchase_count;
        $conversionRate = $totalClicks > 0 ? ($totalPurchases / $totalClicks) * 100 : 0;

        return [
            'listing' => $listing,
            'metrics' => [
                'total_views' => $totalViews,
                'total_clicks' => $totalClicks,
                'total_cart_adds' => $listing->cart_add_count,
                'total_purchases' => $totalPurchases,
                'total_wishlist' => $listing->wishlist_count,
                'conversion_rate' => round($conversionRate, 2),
            ],
            'daily_stats' => $dailyStats,
            'source_stats' => $sourceStats,
            'device_stats' => $deviceStats,
        ];
    }

    /**
     * Get vendor analytics summary
     */
    public function getVendorAnalytics($vendorProfileId, $days = 30)
    {
        $startDate = Carbon::now()->subDays($days);

        $listings = Listing::where('vendor_profile_id', $vendorProfileId)
            ->pluck('id');

        if ($listings->isEmpty()) {
            return null;
        }

        $stats = ProductInteraction::whereIn('listing_id', $listings)
            ->where('created_at', '>=', $startDate)
            ->selectRaw('
                COUNT(CASE WHEN type = "view" THEN 1 END) as views,
                COUNT(CASE WHEN type = "click" THEN 1 END) as clicks,
                COUNT(CASE WHEN type = "add_to_cart" THEN 1 END) as cart_adds,
                COUNT(CASE WHEN type = "purchase" THEN 1 END) as purchases
            ')
            ->first();

        // Get top performing products
        $topProducts = Listing::whereIn('id', $listings)
            ->orderByDesc('view_count')
            ->limit(10)
            ->with('images')
            ->get();

        return [
            'summary' => $stats,
            'top_products' => $topProducts,
            'conversion_rate' => $stats->clicks > 0 
                ? round(($stats->purchases / $stats->clicks) * 100, 2) 
                : 0
        ];
    }
}