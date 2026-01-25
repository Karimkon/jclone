<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Escrow;
use App\Models\VendorWithdrawal;
use App\Models\VendorTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinanceDashboardController extends Controller
{
    /**
     * Display the finance dashboard
     */
    public function index()
    {
        // Revenue Statistics
        $revenueStats = [
            'total_sales' => Order::whereIn('status', ['paid', 'processing', 'shipped', 'delivered'])->sum('total'),
            'total_commission' => Order::whereIn('status', ['paid', 'processing', 'shipped', 'delivered'])->sum('platform_commission'),
            'today_sales' => Order::whereIn('status', ['paid', 'processing', 'shipped', 'delivered'])
                ->whereDate('created_at', today())
                ->sum('total'),
            'month_sales' => Order::whereIn('status', ['paid', 'processing', 'shipped', 'delivered'])
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('total'),
        ];

        // Escrow Statistics
        $escrowStats = [
            'total_held' => Escrow::where('status', 'held')->sum('amount'),
            'total_released' => Escrow::where('status', 'released')->sum('amount'),
            'total_refunded' => Escrow::where('status', 'refunded')->sum('amount'),
            'pending_count' => Escrow::where('status', 'held')->count(),
        ];

        // Withdrawal Statistics
        $withdrawalStats = [
            'pending' => VendorWithdrawal::where('status', 'pending')->count(),
            'processing' => VendorWithdrawal::where('status', 'processing')->count(),
            'pending_amount' => VendorWithdrawal::whereIn('status', ['pending', 'processing'])->sum('amount'),
            'completed_this_month' => VendorWithdrawal::where('status', 'completed')
                ->whereMonth('completed_at', now()->month)
                ->sum('net_amount'),
        ];

        // Payment Statistics
        $paymentStats = [
            'total_payments' => Payment::where('status', 'completed')->sum('amount'),
            'flutterwave' => Payment::where('status', 'completed')->where('provider', 'flutterwave')->sum('amount'),
            'pesapal' => Payment::where('status', 'completed')->where('provider', 'pesapal')->sum('amount'),
            'failed_payments' => Payment::where('status', 'failed')->count(),
        ];

        // Recent Transactions (last 10)
        $recentTransactions = VendorTransaction::with(['vendor.user'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Pending Withdrawals (for quick action)
        $pendingWithdrawals = VendorWithdrawal::with(['vendor.user'])
            ->whereIn('status', ['pending', 'processing'])
            ->orderBy('created_at', 'asc')
            ->limit(5)
            ->get();

        // Chart Data - Last 7 days revenue
        $chartData = $this->getRevenueChartData(7);

        return view('finance.dashboard', compact(
            'revenueStats',
            'escrowStats',
            'withdrawalStats',
            'paymentStats',
            'recentTransactions',
            'pendingWithdrawals',
            'chartData'
        ));
    }

    /**
     * Get revenue chart data for the last N days
     */
    private function getRevenueChartData($days = 7)
    {
        $labels = [];
        $sales = [];
        $commissions = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $labels[] = $date->format('M d');

            $daySales = Order::whereIn('status', ['paid', 'processing', 'shipped', 'delivered'])
                ->whereDate('created_at', $date)
                ->sum('total');

            $dayCommission = Order::whereIn('status', ['paid', 'processing', 'shipped', 'delivered'])
                ->whereDate('created_at', $date)
                ->sum('platform_commission');

            $sales[] = round($daySales, 2);
            $commissions[] = round($dayCommission, 2);
        }

        return [
            'labels' => $labels,
            'sales' => $sales,
            'commissions' => $commissions,
        ];
    }
}
