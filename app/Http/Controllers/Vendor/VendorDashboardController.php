<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Listing;
use App\Models\Order;
use App\Models\VendorProfile;

class VendorDashboardController extends Controller
{
    public function index()
    {
        $vendor = auth()->user()->vendorProfile;
        
        if (!$vendor) {
            return redirect()->route('vendor.onboard.create')
                ->with('error', 'Please complete vendor onboarding first.');
        }
        
        // Check if vendor is approved
        if ($vendor->vetting_status != 'approved') {
            return redirect()->route('vendor.onboard.status');
        }
        
        $stats = [
            'total_listings' => $vendor->listings()->count(),
            'active_listings' => $vendor->listings()->where('is_active', true)->count(),
            'pending_orders' => Order::where('vendor_profile_id', $vendor->id)
                ->whereIn('status', ['pending', 'paid', 'processing'])
                ->count(),
            'total_sales' => Order::where('vendor_profile_id', $vendor->id)
                ->where('status', 'delivered')
                ->sum('total'),
            'monthly_revenue' => Order::where('vendor_profile_id', $vendor->id)
                ->where('status', 'delivered')
                ->whereMonth('created_at', now()->month)
                ->sum('total'),
        ];
        
        $recentOrders = Order::where('vendor_profile_id', $vendor->id)
            ->with('buyer')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
            
        $recentListings = $vendor->listings()
            ->with('images')
            ->orderBy('created_at', 'desc')
            ->take(6)
            ->get();
            
        return view('vendor.dashboard', compact('stats', 'recentOrders', 'recentListings'));
    }
}