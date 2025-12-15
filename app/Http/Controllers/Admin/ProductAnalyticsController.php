<?php
// app/Http/Controllers/Admin/ProductAnalyticsController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ProductAnalyticsService;
use App\Models\Listing;
use Illuminate\Http\Request;

class ProductAnalyticsController extends Controller
{
    protected $analyticsService;

    public function __construct(ProductAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Analytics overview dashboard
     */
    public function index(Request $request)
    {
        $days = $request->input('days', 30);

        // Get products clicked but not bought
        $clickedNotBought = $this->analyticsService->getClickedButNotBought($days, 5);

        // Get cart abandonment insights
        $cartAbandonment = $this->analyticsService->getCartAbandonmentInsights($days);

        // Get trending products
        $trending = $this->analyticsService->getTrendingProducts(7, 10);

        // Get overall stats
        $totalListings = Listing::where('is_active', true)->count();
        $listingsWithViews = Listing::where('view_count', '>', 0)->count();
        $listingsWithPurchases = Listing::where('purchase_count', '>', 0)->count();

        return view('admin.analytics.products.index', compact(
            'clickedNotBought',
            'cartAbandonment',
            'trending',
            'totalListings',
            'listingsWithViews',
            'listingsWithPurchases',
            'days'
        ));
    }

    /**
     * Individual product analytics
     */
    public function show(Request $request, $id)
    {
        $days = $request->input('days', 30);
        $data = $this->analyticsService->getProductPerformance($id, $days);

        return view('admin.analytics.products.show', array_merge($data, ['days' => $days]));
    }

    /**
     * Export clicked but not bought report
     */
    public function exportClickedNotBought(Request $request)
    {
        $days = $request->input('days', 30);
        $products = $this->analyticsService->getClickedButNotBought($days, 1);

        $filename = 'clicked-not-bought-' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($products) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, [
                'Product ID',
                'Product Title',
                'SKU',
                'Vendor',
                'Category',
                'Price',
                'Stock',
                'Total Clicks',
                'Cart Adds',
                'Purchases',
                'Conversion Rate %',
                'Status'
            ]);

            // Data
            foreach ($products as $product) {
                fputcsv($file, [
                    $product->id,
                    $product->title,
                    $product->sku,
                    $product->vendor->business_name ?? 'N/A',
                    $product->category->name ?? 'N/A',
                    $product->price,
                    $product->stock,
                    $product->total_clicks,
                    $product->cart_adds,
                    $product->total_purchases,
                    $product->conversion_rate,
                    $product->is_active ? 'Active' : 'Inactive'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Product comparison
     */
    public function compare(Request $request)
    {
        $request->validate([
            'listing_ids' => 'required|array|min:2|max:5',
            'listing_ids.*' => 'exists:listings,id'
        ]);

        $days = $request->input('days', 30);
        $listingIds = $request->input('listing_ids');

        $comparisons = [];
        foreach ($listingIds as $id) {
            $comparisons[] = $this->analyticsService->getProductPerformance($id, $days);
        }

        return view('admin.analytics.products.compare', compact('comparisons', 'days'));
    }
}