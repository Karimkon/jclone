<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VendorProfile;
use App\Models\Order;
use App\Models\User;
use App\Models\Listing;
use App\Models\Category;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // Get statistics
        $stats = [
            'vendorPending' => VendorProfile::where('vetting_status', 'pending')->count(),
            'ordersToday' => Order::whereDate('created_at', now()->toDateString())->count(),
            'users' => User::count(),
            'totalProducts' => Listing::count(),
            'activeVendors' => VendorProfile::where('vetting_status', 'approved')->count(),
            'categories' => Category::count(),
            'totalRevenue' => Order::where('status', 'completed')->sum('total'), // Changed total_amount to total
            'monthlyRevenue' => Order::where('status', 'completed')
                ->whereMonth('created_at', now()->month)
                ->sum('total'), // Changed total_amount to total
        ];

        // Get recent activities
        $recentActivities = ActivityLogger::getRecentActivities(5);
        
        // Get system info
        $systemInfo = [
            'laravelVersion' => app()->version(),
            'phpVersion' => phpversion(),
            'environment' => app()->environment(),
            'debugMode' => config('app.debug'),
        ];

        return view('admin.dashboard', compact('stats', 'recentActivities', 'systemInfo'));
    }
}