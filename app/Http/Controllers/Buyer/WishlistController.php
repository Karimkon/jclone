<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use App\Models\Listing;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WishlistController extends Controller
{
    /**
     * Display wishlist
     */
    public function index()
    {
        $wishlistItems = Wishlist::where('user_id', Auth::id())
            ->with(['listing.images', 'listing.vendor'])
            ->latest()
            ->get();

        return view('buyer.wishlist.index', compact('wishlistItems'));
    }

    /**
     * Add item to wishlist
     */
    public function add(Request $request, $listingId)
    {
        // Check authentication
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Please login to add items to wishlist',
                'redirect' => route('login', ['redirect' => url()->previous()])
            ], 401);
        }

        // Find listing
        $listing = Listing::find($listingId);
        
        if (!$listing) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        try {
            // Check if already in wishlist
            $existing = Wishlist::where('user_id', Auth::id())
                ->where('listing_id', $listingId)
                ->first();

            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product already in wishlist'
                ], 400);
            }

            // Add to wishlist
            Wishlist::create([
                'user_id' => Auth::id(),
                'listing_id' => $listingId,
                'meta' => [
                    'added_at' => now()->toDateTimeString(),
                    'price_when_added' => $listing->price
                ]
            ]);

            $wishlistCount = Wishlist::where('user_id', Auth::id())->count();

            return response()->json([
                'success' => true,
                'message' => 'Product added to wishlist successfully!',
                'wishlist_count' => $wishlistCount
            ]);

        } catch (\Exception $e) {
            \Log::error('Add to wishlist error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to add product to wishlist. Please try again.'
            ], 500);
        }
    }

    /**
     * Remove item from wishlist
     */
    public function remove($listingId)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        try {
            $deleted = Wishlist::where('user_id', Auth::id())
                ->where('listing_id', $listingId)
                ->delete();

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item not found in wishlist'
                ], 404);
            }

            $wishlistCount = Wishlist::where('user_id', Auth::id())->count();

            return response()->json([
                'success' => true,
                'message' => 'Item removed from wishlist',
                'wishlist_count' => $wishlistCount
            ]);

        } catch (\Exception $e) {
            \Log::error('Remove from wishlist error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove item'
            ], 500);
        }
    }

    /**
     * Toggle wishlist (add if not exists, remove if exists)
     */
    public function toggle($listingId)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Please login to manage wishlist',
                'redirect' => route('login', ['redirect' => url()->previous()])
            ], 401);
        }

        try {
            $wishlistItem = Wishlist::where('user_id', Auth::id())
                ->where('listing_id', $listingId)
                ->first();

            if ($wishlistItem) {
                // Remove from wishlist
                $wishlistItem->delete();
                $message = 'Removed from wishlist';
                $inWishlist = false;
            } else {
                // Add to wishlist
                $listing = Listing::find($listingId);
                
                if (!$listing) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Product not found'
                    ], 404);
                }

                Wishlist::create([
                    'user_id' => Auth::id(),
                    'listing_id' => $listingId,
                    'meta' => [
                        'added_at' => now()->toDateTimeString(),
                        'price_when_added' => $listing->price
                    ]
                ]);
                
                $message = 'Added to wishlist';
                $inWishlist = true;
            }

            $wishlistCount = Wishlist::where('user_id', Auth::id())->count();

            return response()->json([
                'success' => true,
                'message' => $message,
                'in_wishlist' => $inWishlist,
                'wishlist_count' => $wishlistCount
            ]);

        } catch (\Exception $e) {
            \Log::error('Toggle wishlist error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update wishlist'
            ], 500);
        }
    }

    /**
     * Get wishlist count (AJAX)
     */
    public function getCount()
    {
        if (!Auth::check()) {
            return response()->json([
                'authenticated' => false,
                'count' => 0
            ]);
        }

        $count = Wishlist::where('user_id', Auth::id())->count();
        
        return response()->json([
            'authenticated' => true,
            'count' => $count
        ]);
    }

    /**
     * Move item from wishlist to cart
     */
    public function moveToCart($listingId)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        try {
            DB::beginTransaction();

            // Check if item is in wishlist
            $wishlistItem = Wishlist::where('user_id', Auth::id())
                ->where('listing_id', $listingId)
                ->first();

            if (!$wishlistItem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item not found in wishlist'
                ], 404);
            }

            // Get listing
            $listing = Listing::with('images', 'vendor')->find($listingId);
            
            if (!$listing || !$listing->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product is no longer available'
                ], 400);
            }

            if ($listing->stock < 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product is out of stock'
                ], 400);
            }

            // Add to cart
            $cart = Cart::firstOrCreate(
                ['user_id' => Auth::id()],
                [
                    'items' => [],
                    'subtotal' => 0,
                    'shipping' => 0,
                    'tax' => 0,
                    'total' => 0
                ]
            );

            $cart->addItem($listing, 1);

            // Remove from wishlist
            $wishlistItem->delete();

            DB::commit();

            $wishlistCount = Wishlist::where('user_id', Auth::id())->count();
            $cartCount = count($cart->items ?? []);

            return response()->json([
                'success' => true,
                'message' => 'Item moved to cart successfully!',
                'wishlist_count' => $wishlistCount,
                'cart_count' => $cartCount
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Move to cart error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to move item to cart'
            ], 500);
        }
    }
}