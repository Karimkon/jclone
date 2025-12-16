<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\VendorProfile;
use App\Models\Listing;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Display reports dashboard
     */
    public function index(Request $request)
    {
        // Date range (default: last 30 days)
        $dateFrom = $request->get('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        
        // Sales statistics
        $salesStats = $this->getSalesStats($dateFrom, $dateTo);
        
        // User statistics
        $userStats = $this->getUserStats($dateFrom, $dateTo);
        
        // Vendor statistics
        $vendorStats = $this->getVendorStats($dateFrom, $dateTo);
        
        // Top selling products
        $topProducts = $this->getTopProducts($dateFrom, $dateTo);
        
        // Top vendors
        $topVendors = $this->getTopVendors($dateFrom, $dateTo);
        
        // Sales trend (last 7 days)
        $salesTrend = $this->getSalesTrend(7);
        
        return view('admin.reports.index', compact(
            'salesStats',
            'userStats',
            'vendorStats',
            'topProducts',
            'topVendors',
            'salesTrend',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Export reports
     */
    public function export(Request $request)
    {
        $type = $request->get('type', 'sales');
        $dateFrom = $request->get('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        
        switch ($type) {
            case 'sales':
                $data = $this->exportSalesReport($dateFrom, $dateTo);
                $filename = "sales-report-{$dateFrom}-to-{$dateTo}.csv";
                break;
                
            case 'vendors':
                $data = $this->exportVendorReport($dateFrom, $dateTo);
                $filename = "vendor-report-{$dateFrom}-to-{$dateTo}.csv";
                break;
                
            case 'products':
                $data = $this->exportProductReport($dateFrom, $dateTo);
                $filename = "product-report-{$dateFrom}-to-{$dateTo}.csv";
                break;
                
            case 'users':
                $data = $this->exportUserReport($dateFrom, $dateTo);
                $filename = "user-report-{$dateFrom}-to-{$dateTo}.csv";
                break;
                
            default:
                return back()->with('error', 'Invalid report type.');
        }
        
        // For MVP, return JSON
        // In production, you would generate CSV/Excel
        return response()->json([
            'report_type' => $type,
            'date_range' => "{$dateFrom} to {$dateTo}",
            'data' => $data,
            'exported_at' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Get sales statistics
     */
    private function getSalesStats($dateFrom, $dateTo)
    {
        return [
            'total_orders' => Order::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'total_revenue' => Order::whereBetween('created_at', [$dateFrom, $dateTo])
                ->where('status', 'delivered')
                ->sum('total'),
            'avg_order_value' => Order::whereBetween('created_at', [$dateFrom, $dateTo])
                ->where('status', 'delivered')
                ->avg('total') ?? 0,
            'platform_commission' => Order::whereBetween('created_at', [$dateFrom, $dateTo])
                ->where('status', 'delivered')
                ->sum('platform_commission'),
            'refund_amount' => \App\Models\Payment::where('amount', '<', 0)
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->sum('amount') * -1,
        ];
    }

    /**
     * Get user statistics
     */
    private function getUserStats($dateFrom, $dateTo)
    {
        return [
            'total_users' => User::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'new_buyers' => User::where('role', 'buyer')
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->count(),
            'new_vendors' => User::whereIn('role', ['vendor_local', 'vendor_international'])
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->count(),
            'active_users' => User::where('is_active', true)
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->count(),
        ];
    }

    /**
     * Get vendor statistics
     */
    private function getVendorStats($dateFrom, $dateTo)
    {
        return [
            'total_vendors' => VendorProfile::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'approved_vendors' => VendorProfile::where('vetting_status', 'approved')
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->count(),
            'pending_vendors' => VendorProfile::where('vetting_status', 'pending')
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->count(),
            'rejected_vendors' => VendorProfile::where('vetting_status', 'rejected')
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->count(),
        ];
    }

    /**
     * Get top products
     */
    private function getTopProducts($dateFrom, $dateTo, $limit = 10)
    {
        return OrderItem::select(
                'listing_id',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('SUM(line_total) as total_revenue'),
                DB::raw('COUNT(DISTINCT order_id) as order_count')
            )
            ->whereHas('order', function($query) use ($dateFrom, $dateTo) {
                $query->whereBetween('created_at', [$dateFrom, $dateTo])
                      ->where('status', 'delivered');
            })
            ->with('listing')
            ->groupBy('listing_id')
            ->orderBy('total_quantity', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get top vendors
     */
    private function getTopVendors($dateFrom, $dateTo, $limit = 10)
    {
        return Order::select(
                'vendor_profile_id',
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(total) as total_revenue'),
                DB::raw('AVG(total) as avg_order_value')
            )
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('status', 'delivered')
            ->with('vendorProfile.user')
            ->groupBy('vendor_profile_id')
            ->orderBy('total_revenue', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get sales trend
     */
    private function getSalesTrend($days = 7)
    {
        $dates = [];
        $sales = [];
        
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dates[] = $date;
            
            $sales[] = Order::whereDate('created_at', $date)
                ->where('status', 'delivered')
                ->sum('total') ?? 0;
        }
        
        return [
            'dates' => $dates,
            'sales' => $sales,
        ];
    }

    /**
     * Export sales report
     */
    private function exportSalesReport($dateFrom, $dateTo)
    {
        return Order::with(['buyer', 'vendorProfile.user'])
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($order) {
                return [
                    'Order Number' => $order->order_number,
                    'Date' => $order->created_at->format('Y-m-d H:i:s'),
                    'Buyer' => $order->buyer->name ?? 'N/A',
                    'Vendor' => $order->vendorProfile->business_name ?? 'N/A',
                    'Status' => $order->status,
                    'Subtotal' => $order->subtotal,
                    'Shipping' => $order->shipping,
                    'Taxes' => $order->taxes,
                    'Commission' => $order->platform_commission,
                    'Total' => $order->total,
                ];
            });
    }

    /**
     * Export vendor report
     */
    private function exportVendorReport($dateFrom, $dateTo)
    {
        return VendorProfile::with(['user', 'listings'])
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($vendor) {
                return [
                    'Business Name' => $vendor->business_name,
                    'Owner' => $vendor->user->name ?? 'N/A',
                    'Email' => $vendor->user->email ?? 'N/A',
                    'Type' => $vendor->vendor_type,
                    'Status' => $vendor->vetting_status,
                    'Country' => $vendor->country,
                    'City' => $vendor->city,
                    'Listings Count' => $vendor->listings->count(),
                    'Registration Date' => $vendor->created_at->format('Y-m-d'),
                ];
            });
    }

   // Add these methods to your ReportController class

/**
 * Get detailed sales report
 */
public function salesDetailed(Request $request)
{
    $dateFrom = $request->get('date_from', now()->subDays(30)->format('Y-m-d'));
    $dateTo = $request->get('date_to', now()->format('Y-m-d'));
    
    $orders = Order::with(['buyer', 'vendorProfile.user', 'items.listing'])
        ->whereBetween('created_at', [$dateFrom, $dateTo])
        ->orderBy('created_at', 'desc')
        ->paginate(50);
    
    $summary = [
        'total_orders' => $orders->total(),
        'total_revenue' => $orders->sum('total'),
        'total_commission' => $orders->sum('platform_commission'),
        'avg_order_value' => $orders->avg('total') ?? 0,
    ];
    
    return view('admin.reports.sales-detailed', compact('orders', 'summary', 'dateFrom', 'dateTo'));
}

/**
 * Get financial report
 */
public function financialReport(Request $request)
{
    $year = $request->get('year', now()->year);
    $month = $request->get('month', now()->month);
    
    // Monthly revenue
    $monthlyRevenue = Order::whereYear('created_at', $year)
        ->whereMonth('created_at', $month)
        ->where('status', 'delivered')
        ->sum('total');
    
    // Monthly commission
    $monthlyCommission = Order::whereYear('created_at', $year)
        ->whereMonth('created_at', $month)
        ->where('status', 'delivered')
        ->sum('platform_commission');
    
    // Refunds
    $monthlyRefunds = \App\Models\Payment::where('amount', '<', 0)
        ->whereYear('created_at', $year)
        ->whereMonth('created_at', $month)
        ->sum('amount') * -1;
    
    // Monthly orders
    $monthlyOrders = Order::whereYear('created_at', $year)
        ->whereMonth('created_at', $month)
        ->count();
    
    // Monthly expenses (simplified)
    $monthlyExpenses = 0; // You can add expense tracking later
    
    // Yearly trend
    $yearlyTrend = [];
    for ($i = 1; $i <= 12; $i++) {
        $revenue = Order::whereYear('created_at', $year)
            ->whereMonth('created_at', $i)
            ->where('status', 'delivered')
            ->sum('total');
        
        $commission = Order::whereYear('created_at', $year)
            ->whereMonth('created_at', $i)
            ->where('status', 'delivered')
            ->sum('platform_commission');
        
        $orders = Order::whereYear('created_at', $year)
            ->whereMonth('created_at', $i)
            ->count();
        
        $yearlyTrend[] = [
            'month' => Carbon::create($year, $i, 1)->format('M'),
            'revenue' => $revenue,
            'commission' => $commission,
            'orders' => $orders,
            'profit' => $revenue - $commission,
        ];
    }
    
    // Payouts to vendors
    $payouts = \App\Models\Payout::whereYear('created_at', $year)
        ->whereMonth('created_at', $month)
        ->where('status', 'completed')
        ->sum('amount');
    
    return view('admin.reports.financial', compact(
        'year',
        'month',
        'monthlyRevenue',
        'monthlyCommission',
        'monthlyRefunds',
        'monthlyOrders',
        'monthlyExpenses',
        'payouts',
        'yearlyTrend'
    ));
}

/**
 * Get user acquisition report
 */
public function userAcquisition(Request $request)
{
    $dateFrom = $request->get('date_from', now()->subDays(30)->format('Y-m-d'));
    $dateTo = $request->get('date_to', now()->format('Y-m-d'));
    
    $users = User::whereBetween('created_at', [$dateFrom, $dateTo])
        ->orderBy('created_at', 'desc')
        ->paginate(50);
    
    $stats = [
        'total_users' => $users->total(),
        'buyers' => User::where('role', 'buyer')->whereBetween('created_at', [$dateFrom, $dateTo])->count(),
        'vendors' => User::whereIn('role', ['vendor_local', 'vendor_international'])->whereBetween('created_at', [$dateFrom, $dateTo])->count(),
        'active_users' => User::where('is_active', true)->whereBetween('created_at', [$dateFrom, $dateTo])->count(),
        'verified_users' => User::whereNotNull('email_verified_at')->whereBetween('created_at', [$dateFrom, $dateTo])->count(),
    ];
    
    // Daily signups trend
    $signupsTrend = User::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
        ->whereBetween('created_at', [$dateFrom, $dateTo])
        ->groupBy('date')
        ->orderBy('date')
        ->get();
    
    return view('admin.reports.user-acquisition', compact('users', 'stats', 'signupsTrend', 'dateFrom', 'dateTo'));
}

/**
 * Get vendor performance report
 */
public function vendorPerformance(Request $request)
{
    $dateFrom = $request->get('date_from', now()->subDays(90)->format('Y-m-d'));
    $dateTo = $request->get('date_to', now()->format('Y-m-d'));
    
    $vendors = VendorProfile::with(['user', 'performance'])
        ->where('vetting_status', 'approved')
        ->whereBetween('created_at', [$dateFrom, $dateTo])
        ->orderBy('created_at', 'desc')
        ->paginate(20);
    
    // Add performance metrics to each vendor
    $vendors->each(function($vendor) use ($dateFrom, $dateTo) {
        $vendor->stats = $this->getVendorPerformanceStats($vendor->id, $dateFrom, $dateTo);
    });
    
    $summary = [
        'total_vendors' => $vendors->total(),
        'active_vendors' => $vendors->where('is_active', true)->count(),
        'avg_rating' => $vendors->avg('performance.avg_rating') ?? 0,
        'total_revenue' => $vendors->sum('stats.total_revenue'),
    ];
    
    return view('admin.reports.vendor-performance', compact('vendors', 'summary', 'dateFrom', 'dateTo'));
}

/**
 * Get category performance report
 */
public function categoryPerformance(Request $request)
{
    $dateFrom = $request->get('date_from', now()->subDays(90)->format('Y-m-d'));
    $dateTo = $request->get('date_to', now()->format('Y-m-d'));
    
    $categories = Category::withCount(['listings' => function($query) {
        $query->where('is_active', true);
    }])
    ->where('is_active', true)
    ->get();
    
    // Add sales data to each category
    $categories->each(function($category) use ($dateFrom, $dateTo) {
        $category->sales_data = $this->getCategorySalesData($category->id, $dateFrom, $dateTo);
    });
    
    // Sort by revenue
    $categories = $categories->sortByDesc(function($category) {
        return $category->sales_data['total_revenue'];
    });
    
    return view('admin.reports.category-performance', compact('categories', 'dateFrom', 'dateTo'));
}

/**
 * Get vendor performance stats
 */
private function getVendorPerformanceStats($vendorId, $dateFrom, $dateTo)
{
    $orders = Order::where('vendor_profile_id', $vendorId)
        ->whereBetween('created_at', [$dateFrom, $dateTo])
        ->where('status', 'delivered')
        ->get();
    
    $reviews = \App\Models\Review::where('vendor_profile_id', $vendorId)
        ->where('status', 'approved')
        ->whereBetween('created_at', [$dateFrom, $dateTo])
        ->get();
    
    return [
        'total_orders' => $orders->count(),
        'total_revenue' => $orders->sum('total'),
        'avg_order_value' => $orders->avg('total') ?? 0,
        'total_reviews' => $reviews->count(),
        'avg_rating' => $reviews->avg('rating') ?? 0,
        'on_time_delivery' => $orders->where('delivery_score', '>=', 80)->count() / max($orders->count(), 1) * 100,
    ];
}

/**
 * Get category sales data
 */
private function getCategorySalesData($categoryId, $dateFrom, $dateTo)
{
    // Get all listings in this category and its subcategories
    $category = Category::with('children')->find($categoryId);
    $categoryIds = $this->getCategoryIdsWithChildren($category);
    
    // Get order items for these listings
    $orderItems = OrderItem::whereHas('listing', function($query) use ($categoryIds) {
            $query->whereIn('category_id', $categoryIds);
        })
        ->whereHas('order', function($query) use ($dateFrom, $dateTo) {
            $query->whereBetween('created_at', [$dateFrom, $dateTo])
                  ->where('status', 'delivered');
        })
        ->get();
    
    return [
        'total_orders' => $orderItems->groupBy('order_id')->count(),
        'total_quantity' => $orderItems->sum('quantity'),
        'total_revenue' => $orderItems->sum('line_total'),
        'unique_buyers' => Order::whereIn('id', $orderItems->pluck('order_id')->unique())
            ->distinct('buyer_id')
            ->count(),
    ];
}

/**
 * Get all category IDs including children
 */
private function getCategoryIdsWithChildren($category)
{
    $ids = [$category->id];
    
    foreach ($category->children as $child) {
        $ids = array_merge($ids, $this->getCategoryIdsWithChildren($child));
    }
    
    return $ids;
}

/**
 * Get platform analytics
 */
public function platformAnalytics(Request $request)
{
    $dateFrom = $request->get('date_from', now()->subDays(30)->format('Y-m-d'));
    $dateTo = $request->get('date_to', now()->format('Y-m-d'));
    
    // Traffic sources (simplified)
    $trafficSources = [
        ['source' => 'Direct', 'visits' => 4500],
        ['source' => 'Organic Search', 'visits' => 3200],
        ['source' => 'Social Media', 'visits' => 1800],
        ['source' => 'Referral', 'visits' => 1200],
    ];
    
    // Device breakdown
    $deviceBreakdown = [
        ['device' => 'Mobile', 'percentage' => 65],
        ['device' => 'Desktop', 'percentage' => 30],
        ['device' => 'Tablet', 'percentage' => 5],
    ];
    
    // User engagement
    $userEngagement = [
        'avg_session_duration' => '3m 45s',
        'pages_per_session' => 4.2,
        'bounce_rate' => '42%',
        'returning_users' => '35%',
    ];
    
    // Conversion funnel
    $conversionFunnel = [
        ['stage' => 'Visitors', 'count' => 10000],
        ['stage' => 'Product Views', 'count' => 3500],
        ['stage' => 'Add to Cart', 'count' => 850],
        ['stage' => 'Checkout Started', 'count' => 320],
        ['stage' => 'Orders Completed', 'count' => 280],
    ];
    
    return view('admin.reports.platform-analytics', compact(
        'trafficSources',
        'deviceBreakdown',
        'userEngagement',
        'conversionFunnel',
        'dateFrom',
        'dateTo'
    ));
}
/**
 * Export product report
 */
private function exportProductReport($dateFrom, $dateTo)
{
    return Listing::with(['category', 'vendorProfile.user'])
        ->whereBetween('created_at', [$dateFrom, $dateTo])
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function($product) {
            return [
                'SKU' => $product->sku,
                'Title' => $product->title,
                'Category' => $product->category->name ?? 'N/A',
                'Vendor' => $product->vendorProfile->business_name ?? 'N/A',
                'Price' => $product->price,
                'Stock' => $product->stock,
                'Status' => $product->is_active ? 'Active' : 'Inactive',
                'Views' => $product->views ?? 0,
                'Created Date' => $product->created_at->format('Y-m-d'),
            ];
        });
}

/**
 * Export user report
 */
private function exportUserReport($dateFrom, $dateTo)
{
    return User::whereBetween('created_at', [$dateFrom, $dateTo])
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function($user) {
            return [
                'Name' => $user->name,
                'Email' => $user->email,
                'Phone' => $user->phone ?? 'N/A',
                'Role' => $user->role,
                'Status' => $user->is_active ? 'Active' : 'Inactive',
                'Email Verified' => $user->email_verified_at ? 'Yes' : 'No',
                'Registration Date' => $user->created_at->format('Y-m-d H:i:s'),
            ];
        });
}
}