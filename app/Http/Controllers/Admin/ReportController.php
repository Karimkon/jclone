<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
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

    /**
     * Generate financial report
     */
    public function financial(Request $request)
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
        
        // Yearly trend
        $yearlyTrend = [];
        for ($i = 1; $i <= 12; $i++) {
            $revenue = Order::whereYear('created_at', $year)
                ->whereMonth('created_at', $i)
                ->where('status', 'delivered')
                ->sum('total');
            
            $yearlyTrend[] = [
                'month' => Carbon::create($year, $i, 1)->format('M'),
                'revenue' => $revenue,
                'orders' => Order::whereYear('created_at', $year)
                    ->whereMonth('created_at', $i)
                    ->count(),
            ];
        }
        
        return response()->json([
            'year' => $year,
            'month' => $month,
            'monthly_revenue' => $monthlyRevenue,
            'monthly_commission' => $monthlyCommission,
            'monthly_refunds' => $monthlyRefunds,
            'monthly_orders' => $monthlyOrders,
            'yearly_trend' => $yearlyTrend,
        ]);
    }
}