<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Listing;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $cart = $user->cart ?? Cart::create([
            'user_id' => $user->id,
            'items' => [],
            'subtotal' => 0,
            'shipping' => 0,
            'tax' => 0,
            'total' => 0
        ]);
        
        return view('buyer.cart.index', compact('cart'));
    }
    
    /**
     * Add item to cart - requires authentication
     */
    public function add(Request $request, Listing $listing)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'redirect' => route('login'),
                    'message' => 'Please login to add items to cart'
                ], 401);
            }
            
            return redirect()->route('login')
                ->with('warning', 'Please login to add items to your cart')
                ->with('intended', url()->previous());
        }
        
        // Validate quantity
        $request->validate([
            'quantity' => 'nullable|integer|min:1|max:' . $listing->stock
        ]);
        
        $quantity = $request->input('quantity', 1);
        
        // Check if listing is active and in stock
        if (!$listing->is_active) {
            return back()->with('error', 'This product is no longer available.');
        }
        
        if ($listing->stock < $quantity) {
            return back()->with('error', 'Insufficient stock available.');
        }
        
        // Get or create cart
        $user = Auth::user();
        $cart = $user->cart;
        
        if (!$cart) {
            $cart = Cart::create([
                'user_id' => $user->id,
                'items' => [],
                'subtotal' => 0,
                'shipping' => 0,
                'tax' => 0,
                'total' => 0
            ]);
        }
        
        // Add item to cart
        $cart->addItem($listing, $quantity);
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Product added to cart',
                'cart_count' => $cart->getItemCountAttribute()
            ]);
        }
        
        return back()->with('success', 'Product added to cart successfully!');
    }
    
    /**
     * Update cart item quantity
     */
    public function update(Request $request, $listingId)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
        }
        
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);
        
        $cart = Auth::user()->cart;
        
        if (!$cart) {
            return response()->json([
                'success' => false,
                'message' => 'Cart not found'
            ], 404);
        }
        
        // Check stock availability
        $listing = Listing::find($listingId);
        if (!$listing || $listing->stock < $request->quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock available'
            ], 422);
        }
        
        $cart->updateQuantity($listingId, $request->quantity);
        
        return response()->json([
            'success' => true,
            'cart' => $cart,
            'message' => 'Cart updated successfully'
        ]);
    }
    
    /**
     * Remove item from cart
     */
    public function remove(Request $request, $listingId)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
        }
        
        $cart = Auth::user()->cart;
        
        if (!$cart) {
            return response()->json([
                'success' => false,
                'message' => 'Cart not found'
            ], 404);
        }
        
        $cart->removeItem($listingId);
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Item removed from cart',
                'cart_count' => $cart->getItemCountAttribute()
            ]);
        }
        
        return back()->with('success', 'Item removed from cart');
    }
    
    /**
     * Clear entire cart
     */
    public function clear(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
        }
        
        $cart = Auth::user()->cart;
        
        if ($cart) {
            $cart->update([
                'items' => [],
                'subtotal' => 0,
                'shipping' => 0,
                'tax' => 0,
                'total' => 0
            ]);
        }
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Cart cleared'
            ]);
        }
        
        return back()->with('success', 'Cart cleared successfully');
    }
    
    /**
     * Get cart summary (AJAX)
     */
    public function getCartSummary()
    {
        if (!Auth::check()) {
            return response()->json([
                'authenticated' => false,
                'cart_count' => 0
            ]);
        }
        
        $cart = Auth::user()->cart;
        
        return response()->json([
            'authenticated' => true,
            'cart_count' => $cart ? $cart->getItemCountAttribute() : 0,
            'cart' => $cart
        ]);
    }
}