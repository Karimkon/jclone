<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VendorProfile;
use App\Models\Order;
use App\Models\User;
use App\Models\Listing;
use App\Models\Category;
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
            'totalRevenue' => 0,
            'monthlyRevenue' => 0,
        ];

        // Get recent activities (you can implement this later)
        $recentActivities = [];
        
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