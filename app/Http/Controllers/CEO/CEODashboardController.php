<?php

namespace App\Http\Controllers\CEO;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CEODashboardController extends Controller
{
    /**
     * Get date range from period parameter.
     */
    private function getDateRange(string $period): array
    {
        $end = Carbon::now();
        switch ($period) {
            case 'today':
                $start = Carbon::today();
                break;
            case '7days':
                $start = Carbon::now()->subDays(7);
                break;
            case '30days':
                $start = Carbon::now()->subDays(30);
                break;
            case '90days':
                $start = Carbon::now()->subDays(90);
                break;
            case 'year':
                $start = Carbon::now()->startOfYear();
                break;
            case 'all':
            default:
                $start = Carbon::create(2020, 1, 1);
                break;
        }
        return [$start, $end];
    }

    /**
     * Calculate percentage change between two values.
     */
    private function percentChange($current, $previous): float
    {
        if ($previous == 0) return $current > 0 ? 100 : 0;
        return round((($current - $previous) / $previous) * 100, 1);
    }

    /**
     * Dashboard Overview
     */
    public function index(Request $request)
    {
        $period = $request->get('period', '30days');
        [$start, $end] = $this->getDateRange($period);

        // Previous period for comparison
        $diff = $start->diffInDays($end);
        $prevStart = (clone $start)->subDays($diff);
        $prevEnd = (clone $start);

        // Current period KPIs
        $totalRevenue = DB::table('orders')
            ->whereBetween('created_at', [$start, $end])
            ->whereIn('status', ['delivered', 'shipped', 'processing', 'confirmed'])
            ->sum('total');

        $totalOrders = DB::table('orders')
            ->whereBetween('created_at', [$start, $end])
            ->count();

        $totalUsers = DB::table('users')
            ->whereBetween('created_at', [$start, $end])
            ->count();

        $totalProducts = DB::table('listings')
            ->where('is_active', 1)
            ->count();

        $totalCommissions = DB::table('orders')
            ->whereBetween('created_at', [$start, $end])
            ->whereIn('status', ['delivered', 'shipped', 'processing', 'confirmed'])
            ->sum('platform_commission');

        $escrowHeld = DB::table('escrows')
            ->where('status', 'held')
            ->sum('amount');

        // Previous period for comparisons
        $prevRevenue = DB::table('orders')
            ->whereBetween('created_at', [$prevStart, $prevEnd])
            ->whereIn('status', ['delivered', 'shipped', 'processing', 'confirmed'])
            ->sum('total');

        $prevOrders = DB::table('orders')
            ->whereBetween('created_at', [$prevStart, $prevEnd])
            ->count();

        $prevUsers = DB::table('users')
            ->whereBetween('created_at', [$prevStart, $prevEnd])
            ->count();

        $prevCommissions = DB::table('orders')
            ->whereBetween('created_at', [$prevStart, $prevEnd])
            ->whereIn('status', ['delivered', 'shipped', 'processing', 'confirmed'])
            ->sum('platform_commission');

        // 12-month revenue trend
        $monthlyRevenue = DB::table('orders')
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, SUM(total) as revenue, COUNT(*) as orders")
            ->where('created_at', '>=', Carbon::now()->subMonths(12))
            ->whereIn('status', ['delivered', 'shipped', 'processing', 'confirmed'])
            ->groupByRaw("DATE_FORMAT(created_at, '%Y-%m')")
            ->orderBy('month')
            ->get();

        // User growth (12 months)
        $userGrowth = DB::table('users')
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count")
            ->where('created_at', '>=', Carbon::now()->subMonths(12))
            ->groupByRaw("DATE_FORMAT(created_at, '%Y-%m')")
            ->orderBy('month')
            ->get();

        // Order status distribution
        $orderStatuses = DB::table('orders')
            ->selectRaw("status, COUNT(*) as count")
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('status')
            ->get();

        // Recent 10 orders
        $recentOrders = DB::table('orders')
            ->join('users', 'orders.buyer_id', '=', 'users.id')
            ->select('orders.*', 'users.name as buyer_name')
            ->orderBy('orders.created_at', 'desc')
            ->limit(10)
            ->get();

        // Today's snapshot
        $todayRevenue = DB::table('orders')
            ->whereDate('created_at', Carbon::today())
            ->whereIn('status', ['delivered', 'shipped', 'processing', 'confirmed'])
            ->sum('total');
        $todayOrders = DB::table('orders')
            ->whereDate('created_at', Carbon::today())
            ->count();
        $todayUsers = DB::table('users')
            ->whereDate('created_at', Carbon::today())
            ->count();

        // Alerts / Requires Attention
        $pendingWithdrawals = DB::table('vendor_withdrawals')->whereIn('status', ['pending', 'processing'])->count();
        $pendingWithdrawalAmount = DB::table('vendor_withdrawals')->whereIn('status', ['pending', 'processing'])->sum('amount');
        $openDisputes = DB::table('disputes')->where('status', 'open')->count();
        $pendingVendors = DB::table('vendor_profiles')->where('vetting_status', 'pending')->count();
        $lowStockProducts = DB::table('listings')->where('is_active', 1)->where('stock', '<=', 5)->where('stock', '>', 0)->count();
        $outOfStockProducts = DB::table('listings')->where('is_active', 1)->where('stock', '<=', 0)->count();

        // Top 5 vendors by revenue (period)
        $topVendorsByRevenue = DB::table('orders')
            ->join('vendor_profiles', 'orders.vendor_profile_id', '=', 'vendor_profiles.id')
            ->whereBetween('orders.created_at', [$start, $end])
            ->whereIn('orders.status', ['delivered', 'shipped', 'processing', 'confirmed'])
            ->selectRaw('vendor_profiles.business_name, SUM(orders.total) as revenue, COUNT(*) as order_count')
            ->groupBy('vendor_profiles.business_name')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get();

        return view('ceo.dashboard', compact(
            'period', 'totalRevenue', 'totalOrders', 'totalUsers', 'totalProducts',
            'totalCommissions', 'escrowHeld',
            'prevRevenue', 'prevOrders', 'prevUsers', 'prevCommissions',
            'monthlyRevenue', 'userGrowth', 'orderStatuses', 'recentOrders',
            'todayRevenue', 'todayOrders', 'todayUsers',
            'pendingWithdrawals', 'pendingWithdrawalAmount', 'openDisputes',
            'pendingVendors', 'lowStockProducts', 'outOfStockProducts',
            'topVendorsByRevenue'
        ));
    }

    /**
     * Analytics Page
     */
    public function analytics(Request $request)
    {
        $period = $request->get('period', '30days');
        [$start, $end] = $this->getDateRange($period);

        // Top 10 categories by revenue
        $topCategories = DB::table('order_items')
            ->join('listings', 'order_items.listing_id', '=', 'listings.id')
            ->join('categories', 'listings.category_id', '=', 'categories.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$start, $end])
            ->whereIn('orders.status', ['delivered', 'shipped', 'processing', 'confirmed'])
            ->selectRaw('categories.name, SUM(order_items.line_total) as revenue, COUNT(DISTINCT orders.id) as orders')
            ->groupBy('categories.name')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        // Top 10 products by revenue
        $topProducts = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$start, $end])
            ->whereIn('orders.status', ['delivered', 'shipped', 'processing', 'confirmed'])
            ->selectRaw('order_items.title, SUM(order_items.line_total) as revenue, SUM(order_items.quantity) as units_sold')
            ->groupBy('order_items.title')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        // Conversion funnel from product_analytics
        $funnel = DB::table('product_analytics')
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('SUM(views) as views, SUM(clicks) as clicks, SUM(add_to_cart) as carts, SUM(purchases) as purchases')
            ->first();

        // Daily registrations (30 days)
        $dailyRegistrations = DB::table('users')
            ->selectRaw("DATE(created_at) as date, COUNT(*) as count")
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupByRaw("DATE(created_at)")
            ->orderBy('date')
            ->get();

        // Vendor type distribution
        $vendorTypes = DB::table('vendor_profiles')
            ->selectRaw("vendor_type, COUNT(*) as count")
            ->groupBy('vendor_type')
            ->get();

        // Vendor country distribution (top 10)
        $vendorCountries = DB::table('vendor_profiles')
            ->selectRaw("COALESCE(country, 'Unknown') as country, COUNT(*) as count")
            ->groupBy('country')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // Review stats
        $reviewStats = DB::table('reviews')
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw("COUNT(*) as total, AVG(rating) as avg_rating,
                SUM(CASE WHEN rating >= 4 THEN 1 ELSE 0 END) as positive,
                SUM(CASE WHEN rating <= 2 THEN 1 ELSE 0 END) as negative")
            ->first();

        // Rating distribution
        $ratingDistribution = DB::table('reviews')
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw("rating, COUNT(*) as count")
            ->groupBy('rating')
            ->orderBy('rating')
            ->get();

        // Order patterns by hour
        $ordersByHour = DB::table('orders')
            ->selectRaw("HOUR(created_at) as hour, COUNT(*) as count")
            ->whereBetween('created_at', [$start, $end])
            ->groupByRaw("HOUR(created_at)")
            ->orderBy('hour')
            ->get();

        // Order patterns by day of week
        $ordersByDay = DB::table('orders')
            ->selectRaw("DAYNAME(created_at) as day, DAYOFWEEK(created_at) as day_num, COUNT(*) as count")
            ->whereBetween('created_at', [$start, $end])
            ->groupByRaw("DAYNAME(created_at), DAYOFWEEK(created_at)")
            ->orderBy('day_num')
            ->get();

        // User role distribution
        $userRoles = DB::table('users')
            ->selectRaw("role, COUNT(*) as count")
            ->groupBy('role')
            ->get();

        // Repeat customer stats
        $totalBuyers = DB::table('orders')
            ->whereBetween('created_at', [$start, $end])
            ->distinct('buyer_id')
            ->count('buyer_id');
        $repeatBuyers = DB::table('orders')
            ->whereBetween('created_at', [$start, $end])
            ->select('buyer_id')
            ->groupBy('buyer_id')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->count();
        $avgOrdersPerBuyer = $totalBuyers > 0
            ? round(DB::table('orders')->whereBetween('created_at', [$start, $end])->count() / $totalBuyers, 1)
            : 0;

        return view('ceo.analytics', compact(
            'period', 'topCategories', 'topProducts', 'funnel',
            'dailyRegistrations', 'vendorTypes', 'vendorCountries',
            'reviewStats', 'ratingDistribution', 'ordersByHour', 'ordersByDay', 'userRoles',
            'totalBuyers', 'repeatBuyers', 'avgOrdersPerBuyer'
        ));
    }

    /**
     * Financials Page
     */
    public function financials(Request $request)
    {
        $period = $request->get('period', '30days');
        [$start, $end] = $this->getDateRange($period);

        // Monthly revenue & commission (12 months)
        $monthlyFinancials = DB::table('orders')
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month,
                SUM(total) as revenue,
                SUM(platform_commission) as commission,
                COUNT(*) as order_count,
                AVG(total) as avg_order_value")
            ->where('created_at', '>=', Carbon::now()->subMonths(12))
            ->whereIn('status', ['delivered', 'shipped', 'processing', 'confirmed'])
            ->groupByRaw("DATE_FORMAT(created_at, '%Y-%m')")
            ->orderBy('month')
            ->get();

        // Payment status breakdown
        $paymentStatuses = DB::table('payments')
            ->join('orders', 'payments.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$start, $end])
            ->selectRaw("payments.status, COUNT(*) as count, SUM(payments.amount) as total")
            ->groupBy('payments.status')
            ->get();

        // Payment provider breakdown
        $paymentProviders = DB::table('payments')
            ->join('orders', 'payments.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$start, $end])
            ->where('payments.status', 'completed')
            ->selectRaw("payments.provider, COUNT(*) as count, SUM(payments.amount) as total")
            ->groupBy('payments.provider')
            ->get();

        // Escrow summary
        $escrowSummary = DB::table('escrows')
            ->selectRaw("status, COUNT(*) as count, SUM(amount) as total")
            ->groupBy('status')
            ->get();

        // Withdrawal summary
        $withdrawalSummary = DB::table('vendor_withdrawals')
            ->selectRaw("status, COUNT(*) as count, SUM(amount) as total, SUM(fee) as total_fees")
            ->groupBy('status')
            ->get();

        // Vendor balances overview
        $vendorBalancesTotal = DB::table('vendor_balances')->sum('balance');
        $vendorPendingTotal = DB::table('vendor_balances')->sum('pending_balance');
        $vendorBalanceCount = DB::table('vendor_balances')->where('balance', '>', 0)->count();

        // Promotion revenue
        $promotionRevenue = DB::table('promotions')
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw("type, COUNT(*) as count, SUM(fee) as total_fees")
            ->groupBy('type')
            ->get();

        // Refund stats
        $refundStats = DB::table('payments')
            ->where('status', 'refunded')
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw("COUNT(*) as count, SUM(amount) as total")
            ->first();

        // Period totals
        $periodRevenue = DB::table('orders')
            ->whereBetween('created_at', [$start, $end])
            ->whereIn('status', ['delivered', 'shipped', 'processing', 'confirmed'])
            ->sum('total');
        $periodCommission = DB::table('orders')
            ->whereBetween('created_at', [$start, $end])
            ->whereIn('status', ['delivered', 'shipped', 'processing', 'confirmed'])
            ->sum('platform_commission');
        $periodAvgOrderValue = DB::table('orders')
            ->whereBetween('created_at', [$start, $end])
            ->whereIn('status', ['delivered', 'shipped', 'processing', 'confirmed'])
            ->avg('total') ?? 0;

        // Revenue by vendor type
        $revenueByVendorType = DB::table('orders')
            ->join('vendor_profiles', 'orders.vendor_profile_id', '=', 'vendor_profiles.id')
            ->whereBetween('orders.created_at', [$start, $end])
            ->whereIn('orders.status', ['delivered', 'shipped', 'processing', 'confirmed'])
            ->selectRaw("vendor_profiles.vendor_type, SUM(orders.total) as revenue, COUNT(*) as orders")
            ->groupBy('vendor_profiles.vendor_type')
            ->get();

        return view('ceo.financials', compact(
            'period', 'monthlyFinancials', 'paymentStatuses', 'paymentProviders',
            'escrowSummary', 'withdrawalSummary',
            'vendorBalancesTotal', 'vendorPendingTotal', 'vendorBalanceCount',
            'promotionRevenue', 'refundStats',
            'periodRevenue', 'periodCommission', 'periodAvgOrderValue',
            'revenueByVendorType'
        ));
    }

    /**
     * Performance Page
     */
    public function performance(Request $request)
    {
        $period = $request->get('period', '30days');
        [$start, $end] = $this->getDateRange($period);

        // Top 10 vendors by delivery score
        $topVendors = DB::table('vendor_performances')
            ->join('vendor_profiles', 'vendor_performances.vendor_profile_id', '=', 'vendor_profiles.id')
            ->select('vendor_profiles.business_name', 'vendor_performances.*')
            ->orderByDesc('vendor_performances.delivery_score')
            ->limit(10)
            ->get();

        // Bottom 10 vendors
        $bottomVendors = DB::table('vendor_performances')
            ->join('vendor_profiles', 'vendor_performances.vendor_profile_id', '=', 'vendor_profiles.id')
            ->select('vendor_profiles.business_name', 'vendor_performances.*')
            ->where('vendor_performances.total_orders', '>', 0)
            ->orderBy('vendor_performances.delivery_score')
            ->limit(10)
            ->get();

        // Average metrics
        $avgMetrics = DB::table('vendor_performances')
            ->where('total_orders', '>', 0)
            ->selectRaw("
                AVG(delivery_score) as avg_delivery_score,
                AVG(avg_delivery_time_days) as avg_delivery_days,
                AVG(avg_processing_time_hours) as avg_processing_hours,
                AVG(on_time_delivery_rate) as avg_on_time_rate,
                AVG(performance_score) as avg_performance_score,
                SUM(total_orders) as total_platform_orders,
                SUM(delivered_orders) as total_delivered,
                SUM(cancelled_orders) as total_cancelled
            ")
            ->first();

        // Fulfillment rate
        $fulfillmentData = DB::table('orders')
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
                SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing,
                SUM(CASE WHEN status = 'shipped' THEN 1 ELSE 0 END) as shipped,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending
            ")
            ->first();

        // Dispute stats
        $disputeStats = DB::table('disputes')
            ->selectRaw("status, COUNT(*) as count")
            ->groupBy('status')
            ->get();

        $disputeTotal = $disputeStats->sum('count');

        // Vetting pipeline
        $vettingPipeline = DB::table('vendor_profiles')
            ->selectRaw("vetting_status, COUNT(*) as count")
            ->groupBy('vetting_status')
            ->get();

        // Monthly new vendor registrations (12 months)
        $monthlyVendors = DB::table('vendor_profiles')
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count")
            ->where('created_at', '>=', Carbon::now()->subMonths(12))
            ->groupByRaw("DATE_FORMAT(created_at, '%Y-%m')")
            ->orderBy('month')
            ->get();

        // Delivery time distribution
        $deliveryDistribution = DB::table('orders')
            ->whereBetween('created_at', [$start, $end])
            ->where('status', 'delivered')
            ->whereNotNull('delivery_time_days')
            ->selectRaw("
                SUM(CASE WHEN delivery_time_days <= 1 THEN 1 ELSE 0 END) as same_day,
                SUM(CASE WHEN delivery_time_days BETWEEN 2 AND 3 THEN 1 ELSE 0 END) as two_three,
                SUM(CASE WHEN delivery_time_days BETWEEN 4 AND 7 THEN 1 ELSE 0 END) as four_seven,
                SUM(CASE WHEN delivery_time_days > 7 THEN 1 ELSE 0 END) as over_week
            ")
            ->first();

        return view('ceo.performance', compact(
            'period', 'topVendors', 'bottomVendors', 'avgMetrics',
            'fulfillmentData', 'disputeStats', 'disputeTotal',
            'vettingPipeline', 'monthlyVendors', 'deliveryDistribution'
        ));
    }

    /**
     * Users Page (read-only)
     */
    public function users(Request $request)
    {
        $query = DB::table('users');

        // Search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Role filter
        if ($role = $request->get('role')) {
            $query->where('role', $role);
        }

        // Status filter
        if ($request->has('status') && $request->get('status') !== '') {
            $query->where('is_active', $request->get('status'));
        }

        $users = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        // Stats
        $totalUsers = DB::table('users')->count();
        $totalVendors = DB::table('users')->whereIn('role', ['vendor_local', 'vendor_international'])->count();
        $totalBuyers = DB::table('users')->where('role', 'buyer')->count();
        $totalStaff = DB::table('users')->whereIn('role', ['admin', 'logistics', 'clearing_agent', 'finance', 'ceo'])->count();

        return view('ceo.users', compact('users', 'totalUsers', 'totalVendors', 'totalBuyers', 'totalStaff'));
    }

    /**
     * Vendors Page (read-only)
     */
    public function vendors(Request $request)
    {
        $query = DB::table('vendor_profiles')
            ->join('users', 'vendor_profiles.user_id', '=', 'users.id')
            ->select(
                'vendor_profiles.*',
                'users.name as owner_name',
                'users.email as owner_email',
                'users.is_active as user_active'
            );

        // Search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('vendor_profiles.business_name', 'like', "%{$search}%")
                  ->orWhere('users.name', 'like', "%{$search}%")
                  ->orWhere('users.email', 'like', "%{$search}%");
            });
        }

        // Vetting status filter
        if ($status = $request->get('status')) {
            $query->where('vendor_profiles.vetting_status', $status);
        }

        // Vendor type filter
        if ($type = $request->get('type')) {
            $query->where('vendor_profiles.vendor_type', $type);
        }

        $vendors = $query->orderByDesc('vendor_profiles.created_at')->paginate(15)->withQueryString();

        // Stats
        $totalVendors = DB::table('vendor_profiles')->count();
        $pendingVendors = DB::table('vendor_profiles')->where('vetting_status', 'pending')->count();
        $approvedVendors = DB::table('vendor_profiles')->where('vetting_status', 'approved')->count();
        $rejectedVendors = DB::table('vendor_profiles')->where('vetting_status', 'rejected')->count();

        return view('ceo.vendors', compact('vendors', 'totalVendors', 'pendingVendors', 'approvedVendors', 'rejectedVendors'));
    }

    /**
     * Export Dashboard
     */
    public function exportDashboard(Request $request)
    {
        $format = $request->get('format', 'csv');
        $period = $request->get('period', '30days');
        [$start, $end] = $this->getDateRange($period);

        if ($format === 'pdf') {
            return $this->exportPdf('Dashboard Overview', $period, function () use ($start, $end) {
                $orders = DB::table('orders')
                    ->whereBetween('created_at', [$start, $end])
                    ->get(['order_number', 'status', 'total', 'platform_commission', 'created_at']);
                return ['orders' => $orders];
            });
        }

        return $this->exportCsv('dashboard', function () use ($start, $end) {
            $orders = DB::table('orders')
                ->join('users', 'orders.buyer_id', '=', 'users.id')
                ->whereBetween('orders.created_at', [$start, $end])
                ->select('orders.order_number', 'users.name as buyer', 'orders.status', 'orders.total', 'orders.platform_commission', 'orders.created_at')
                ->orderByDesc('orders.created_at')
                ->get();

            $rows = [['Order #', 'Buyer', 'Status', 'Total', 'Commission', 'Date']];
            foreach ($orders as $o) {
                $rows[] = [$o->order_number, $o->buyer, $o->status, $o->total, $o->platform_commission, $o->created_at];
            }
            return $rows;
        });
    }

    /**
     * Export Analytics
     */
    public function exportAnalytics(Request $request)
    {
        $format = $request->get('format', 'csv');
        $period = $request->get('period', '30days');
        [$start, $end] = $this->getDateRange($period);

        if ($format === 'pdf') {
            return $this->exportPdf('Analytics Report', $period, function () use ($start, $end) {
                $topProducts = DB::table('order_items')
                    ->join('orders', 'order_items.order_id', '=', 'orders.id')
                    ->whereBetween('orders.created_at', [$start, $end])
                    ->whereIn('orders.status', ['delivered', 'shipped', 'processing', 'confirmed'])
                    ->selectRaw('order_items.title, SUM(order_items.line_total) as revenue, SUM(order_items.quantity) as units')
                    ->groupBy('order_items.title')
                    ->orderByDesc('revenue')
                    ->limit(20)
                    ->get();
                return ['topProducts' => $topProducts];
            });
        }

        return $this->exportCsv('analytics', function () use ($start, $end) {
            $products = DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->whereBetween('orders.created_at', [$start, $end])
                ->whereIn('orders.status', ['delivered', 'shipped', 'processing', 'confirmed'])
                ->selectRaw('order_items.title, SUM(order_items.line_total) as revenue, SUM(order_items.quantity) as units')
                ->groupBy('order_items.title')
                ->orderByDesc('revenue')
                ->limit(50)
                ->get();

            $rows = [['Product', 'Revenue', 'Units Sold']];
            foreach ($products as $p) {
                $rows[] = [$p->title, $p->revenue, $p->units];
            }
            return $rows;
        });
    }

    /**
     * Export Financials
     */
    public function exportFinancials(Request $request)
    {
        $format = $request->get('format', 'csv');
        $period = $request->get('period', '30days');
        [$start, $end] = $this->getDateRange($period);

        if ($format === 'pdf') {
            return $this->exportPdf('Financial Report', $period, function () use ($start, $end) {
                $monthly = DB::table('orders')
                    ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, SUM(total) as revenue, SUM(platform_commission) as commission")
                    ->where('created_at', '>=', Carbon::now()->subMonths(12))
                    ->whereIn('status', ['delivered', 'shipped', 'processing', 'confirmed'])
                    ->groupByRaw("DATE_FORMAT(created_at, '%Y-%m')")
                    ->orderBy('month')
                    ->get();
                return ['monthly' => $monthly];
            });
        }

        return $this->exportCsv('financials', function () use ($start, $end) {
            $payments = DB::table('payments')
                ->join('orders', 'payments.order_id', '=', 'orders.id')
                ->whereBetween('orders.created_at', [$start, $end])
                ->select('orders.order_number', 'payments.provider', 'payments.amount', 'payments.status', 'payments.created_at')
                ->orderByDesc('payments.created_at')
                ->get();

            $rows = [['Order #', 'Provider', 'Amount', 'Status', 'Date']];
            foreach ($payments as $p) {
                $rows[] = [$p->order_number, $p->provider, $p->amount, $p->status, $p->created_at];
            }
            return $rows;
        });
    }

    /**
     * Export Performance
     */
    public function exportPerformance(Request $request)
    {
        $format = $request->get('format', 'csv');
        $period = $request->get('period', '30days');

        if ($format === 'pdf') {
            return $this->exportPdf('Performance Report', $period, function () {
                $vendors = DB::table('vendor_performances')
                    ->join('vendor_profiles', 'vendor_performances.vendor_profile_id', '=', 'vendor_profiles.id')
                    ->select('vendor_profiles.business_name', 'vendor_performances.total_orders', 'vendor_performances.delivered_orders', 'vendor_performances.cancelled_orders', 'vendor_performances.delivery_score', 'vendor_performances.performance_score')
                    ->orderByDesc('vendor_performances.delivery_score')
                    ->get();
                return ['vendors' => $vendors];
            });
        }

        return $this->exportCsv('performance', function () {
            $vendors = DB::table('vendor_performances')
                ->join('vendor_profiles', 'vendor_performances.vendor_profile_id', '=', 'vendor_profiles.id')
                ->select('vendor_profiles.business_name', 'vendor_performances.total_orders', 'vendor_performances.delivered_orders', 'vendor_performances.cancelled_orders', 'vendor_performances.delivery_score', 'vendor_performances.avg_delivery_time_days', 'vendor_performances.performance_score')
                ->orderByDesc('vendor_performances.delivery_score')
                ->get();

            $rows = [['Vendor', 'Total Orders', 'Delivered', 'Cancelled', 'Delivery Score', 'Avg Delivery Days', 'Performance Score']];
            foreach ($vendors as $v) {
                $rows[] = [$v->business_name, $v->total_orders, $v->delivered_orders, $v->cancelled_orders, $v->delivery_score, $v->avg_delivery_time_days, $v->performance_score];
            }
            return $rows;
        });
    }

    /**
     * Generate CSV download.
     */
    private function exportCsv(string $name, callable $dataFn)
    {
        $filename = "bebamart-{$name}-" . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($dataFn) {
            $handle = fopen('php://output', 'w');
            foreach ($dataFn() as $row) {
                fputcsv($handle, (array) $row);
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * Generate print-friendly PDF view.
     */
    private function exportPdf(string $title, string $period, callable $dataFn)
    {
        $data = $dataFn();
        $data['title'] = $title;
        $data['period'] = $period;
        $data['generatedAt'] = now()->format('Y-m-d H:i:s');

        return view('ceo.exports.report-print', $data);
    }
}
