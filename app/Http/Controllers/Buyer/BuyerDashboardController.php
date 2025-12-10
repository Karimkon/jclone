<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Wishlist;
use App\Models\BuyerWallet;
use Illuminate\Support\Facades\Auth;

class BuyerDashboardController extends Controller
{
   public function index()
{
    $user = Auth::user();
    
    // Get wallet or create default
    $wallet = $user->buyerWallet;
    if (!$wallet) {
        $wallet = BuyerWallet::create([
            'user_id' => $user->id,
            'balance' => 0.00,
            'held_balance' => 0.00,
        ]);
    }
    
    $stats = [
        'total_orders' => $user->orders()->count(),
        'pending_orders' => $user->orders()->where('status', 'pending')->count(),
        'active_orders' => $user->orders()->whereIn('status', ['paid', 'processing', 'shipped'])->count(),
        'delivered_orders' => $user->orders()->where('status', 'delivered')->count(),
        'wishlist_items' => $user->wishlists()->count(),
        'wallet_balance' => $wallet->balance,
        'available_balance' => $wallet->balance - $wallet->held_balance,
    ];
    
    $recentOrders = $user->orders()
        ->with(['vendorProfile.user', 'items.listing'])
        ->orderBy('created_at', 'desc')
        ->take(5)
        ->get();
    
    $wishlistItems = $user->wishlists()
        ->with('listing.images', 'listing.vendor')
        ->take(5)
        ->get();
    
    $walletTransactions = $user->walletTransactions()
        ->orderBy('created_at', 'desc')
        ->take(10)
        ->get();
    
    return view('buyer.dashboard', compact(
        'stats', 'recentOrders', 'wishlistItems', 'walletTransactions'
    ));
}
    
    public function profile()
    {
        $user = Auth::user();
        $addresses = $user->meta['addresses'] ?? [];
        
        return view('buyer.profile', compact('user', 'addresses'));
    }
    
    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
        ]);
        
        $user = Auth::user();
        $meta = $user->meta ?? [];
        
        // Update shipping address
        if ($request->filled('address')) {
            $address = [
                'type' => 'shipping',
                'address' => $request->address,
                'city' => $request->city,
                'country' => $request->country,
                'postal_code' => $request->postal_code,
                'is_default' => true,
                'created_at' => now()->toDateTimeString(),
            ];
            
            $meta['addresses'] = [$address];
        }
        
        $user->update([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'meta' => $meta,
        ]);
        
        return back()->with('success', 'Profile updated successfully.');
    }
    
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|string|min:8|confirmed',
        ]);
        
        $user = Auth::user();
        $user->update([
            'password' => bcrypt($request->password)
        ]);
        
        return back()->with('success', 'Password changed successfully.');
    }
}