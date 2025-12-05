<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Wishlist;
use App\Models\Listing;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    public function index()
    {
        $wishlists = Auth::user()->wishlists()
            ->with(['listing.images', 'listing.vendor.user'])
            ->paginate(12);
        
        return view('buyer.wishlist.index', compact('wishlists'));
    }
    
    public function add(Request $request, Listing $listing)
    {
        $user = Auth::user();
        
        // Check if already in wishlist
        $exists = Wishlist::where('user_id', $user->id)
            ->where('listing_id', $listing->id)
            ->exists();
        
        if ($exists) {
            return back()->with('info', 'Product already in wishlist.');
        }
        
        Wishlist::create([
            'user_id' => $user->id,
            'listing_id' => $listing->id,
            'meta' => ['added_at' => now()->toDateTimeString()]
        ]);
        
        return back()->with('success', 'Product added to wishlist.');
    }
    
    public function remove(Request $request, Listing $listing)
    {
        Wishlist::where('user_id', Auth::id())
            ->where('listing_id', $listing->id)
            ->delete();
        
        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }
        
        return back()->with('success', 'Product removed from wishlist.');
    }
    
    public function toggle(Request $request, Listing $listing)
    {
        $wishlist = Wishlist::where('user_id', Auth::id())
            ->where('listing_id', $listing->id)
            ->first();
        
        if ($wishlist) {
            $wishlist->delete();
            $added = false;
        } else {
            Wishlist::create([
                'user_id' => Auth::id(),
                'listing_id' => $listing->id,
                'meta' => ['added_at' => now()->toDateTimeString()]
            ]);
            $added = true;
        }
        
        if ($request->ajax()) {
            return response()->json(['success' => true, 'added' => $added]);
        }
        
        return back();
    }
    
    public function moveToCart(Request $request, Listing $listing)
    {
        // Remove from wishlist
        Wishlist::where('user_id', Auth::id())
            ->where('listing_id', $listing->id)
            ->delete();
        
        // Add to cart
        $cartController = new CartController();
        $request->merge(['quantity' => 1]);
        
        return $cartController->add($request, $listing);
    }
}