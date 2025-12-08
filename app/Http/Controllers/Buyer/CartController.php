<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    /**
     * Display cart
     */
    public function index()
    {
        $cart = $this->getOrCreateCart();
        $cartItems = [];
        
        if ($cart && !empty($cart->items)) {
            // Enrich cart items with current listing data
            foreach ($cart->items as $item) {
                $listing = Listing::with('images', 'vendor')->find($item['listing_id']);
                if ($listing && $listing->is_active && $listing->stock > 0) {
                    $cartItems[] = [
                        'listing_id' => $listing->id,
                        'title' => $listing->title,
                        'image' => $listing->images->first() ? asset('storage/' . $listing->images->first()->path) : null,
                        'vendor_name' => $listing->vendor->business_name ?? 'Vendor',
                        'unit_price' => $listing->price,
                        'quantity' => $item['quantity'],
                        'total' => $listing->price * $item['quantity'],
                        'weight_kg' => $listing->weight_kg,
                        'origin' => $listing->origin,
                        'stock' => $listing->stock,
                    ];
                }
            }
            
            // Update cart items with current data
            $cart->items = $cartItems;
            $cart->recalculateTotals();
            $cart->save();
        }
        
        return view('buyer.cart.index', compact('cart'));
    }

    /**
     * Add item to cart
     */
    public function add(Request $request, $listingId)
    {
        // Check authentication
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Please login to add items to cart',
                'redirect' => route('login', ['redirect' => url()->previous()])
            ], 401);
        }

        // Validate quantity
        $quantity = $request->input('quantity', 1);
        if (!is_numeric($quantity) || $quantity < 1) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid quantity'
            ], 400);
        }

        // Find listing
        $listing = Listing::with('images', 'vendor')->find($listingId);
        
        if (!$listing) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        if (!$listing->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'This product is no longer available'
            ], 400);
        }

        if ($listing->stock < $quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock. Only ' . $listing->stock . ' available.'
            ], 400);
        }

        try {
            DB::beginTransaction();
            
            $cart = $this->getOrCreateCart();
            $cart->addItem($listing, $quantity);
            
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Product added to cart successfully!',
                'cart_count' => count($cart->items ?? []),
                'cart_total' => number_format($cart->total, 2)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Add to cart error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to add product to cart. Please try again.'
            ], 500);
        }
    }

    /**
     * Update cart item quantity
     */
    public function update(Request $request, $listingId)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $quantity = $request->input('quantity', 1);
        
        if (!is_numeric($quantity) || $quantity < 1) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid quantity'
            ], 400);
        }

        // Check stock
        $listing = Listing::find($listingId);
        if (!$listing) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        if ($listing->stock < $quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock. Only ' . $listing->stock . ' available.'
            ], 400);
        }

        try {
            $cart = $this->getOrCreateCart();
            $cart->updateQuantity($listingId, $quantity);

            return response()->json([
                'success' => true,
                'message' => 'Cart updated successfully',
                'cart_count' => count($cart->items ?? []),
                'cart_total' => number_format($cart->total, 2),
                'item_total' => number_format($quantity * $listing->price, 2),
                'subtotal' => number_format($cart->subtotal, 2),
                'shipping' => number_format($cart->shipping, 2),
                'tax' => number_format($cart->tax, 2)
            ]);

        } catch (\Exception $e) {
            \Log::error('Update cart error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update cart'
            ], 500);
        }
    }

    /**
     * Remove item from cart
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
            $cart = $this->getOrCreateCart();
            $cart->removeItem($listingId);

            return response()->json([
                'success' => true,
                'message' => 'Item removed from cart',
                'cart_count' => count($cart->items ?? []),
                'cart_total' => number_format($cart->total, 2),
                'subtotal' => number_format($cart->subtotal, 2),
                'shipping' => number_format($cart->shipping, 2),
                'tax' => number_format($cart->tax, 2)
            ]);

        } catch (\Exception $e) {
            \Log::error('Remove from cart error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove item'
            ], 500);
        }
    }

    /**
     * Clear cart
     */
    public function clear()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        try {
            $cart = Cart::where('user_id', Auth::id())->first();
            if ($cart) {
                $cart->items = [];
                $cart->subtotal = 0;
                $cart->shipping = 0;
                $cart->tax = 0;
                $cart->total = 0;
                $cart->save();
            }

            return back()->with('success', 'Cart cleared successfully');

        } catch (\Exception $e) {
            \Log::error('Clear cart error: ' . $e->getMessage());
            return back()->with('error', 'Failed to clear cart');
        }
    }

    /**
     * Get cart summary (AJAX)
     */
    public function getCartSummary()
    {
        if (!Auth::check()) {
            return response()->json([
                'authenticated' => false,
                'cart_count' => 0,
                'cart_total' => '0.00'
            ]);
        }

        $cart = Cart::where('user_id', Auth::id())->first();
        
        return response()->json([
            'authenticated' => true,
            'cart_count' => $cart ? count($cart->items ?? []) : 0,
            'cart_total' => $cart ? number_format($cart->total, 2) : '0.00',
            'subtotal' => $cart ? number_format($cart->subtotal, 2) : '0.00',
            'shipping' => $cart ? number_format($cart->shipping, 2) : '0.00',
            'tax' => $cart ? number_format($cart->tax, 2) : '0.00'
        ]);
    }

    /**
     * Get or create cart for authenticated user
     */
    private function getOrCreateCart()
    {
        if (!Auth::check()) {
            return null;
        }

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

        return $cart;
    }
}