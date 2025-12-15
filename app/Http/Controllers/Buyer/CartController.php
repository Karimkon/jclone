<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\ListingVariant;

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
    // Start with basic validation
    $rules = [
        'quantity' => 'required|integer|min:1',
    ];
    
    // Load listing early for downstream checks/analytics
    $listing = Listing::findOrFail($listingId);
    
    // Only add variant_id validation if it's present in the request
    if ($request->has('variant_id') && $request->input('variant_id') !== null) {
        $rules['variant_id'] = 'exists:listing_variants,id';
        $rules['color'] = 'nullable|string|max:50';
        $rules['size'] = 'nullable|string|max:50';
    }
    
    $validated = $request->validate($rules);

    // TRACK CART ADD 
    app(\App\Services\ProductAnalyticsService::class)->trackAddToCart(
        $listing->id, 
        $validated['quantity']
    );
    
    // Check if listing is active
    if (!$listing->is_active) {
        return response()->json([
            'success' => false,
            'message' => 'Product is not available'
        ], 400);
    }
    
    // Check stock
    if (isset($validated['variant_id'])) {
        // Check variant stock
        $variant = \App\Models\ListingVariant::find($validated['variant_id']);
        if (!$variant) {
            return response()->json([
                'success' => false,
                'message' => 'Selected variant not found'
            ], 404);
        }
        
        if ($variant->stock < $validated['quantity']) {
            return response()->json([
                'success' => false,
                'message' => 'Selected variant is out of stock'
            ], 400);
        }
    } else {
        // Check main listing stock
        if ($listing->stock < $validated['quantity']) {
            return response()->json([
                'success' => false,
                'message' => 'Product is out of stock'
            ], 400);
        }
    }
    
    // Get or create cart
    $cart = Cart::firstOrCreate(
        ['user_id' => auth()->id()],
        [
            'items' => [], 
            'subtotal' => 0,
            'shipping' => 0,
            'tax' => 0,
            'total' => 0
        ]
    );
    
    // Add item to cart using the model method
    $cart->addItem(
        $listing,
        $validated['quantity'],
        $validated['variant_id'] ?? null,
        $validated['color'] ?? null,
        $validated['size'] ?? null
    );
    
    return response()->json([
        'success' => true,
        'message' => 'Product added to cart successfully',
        'cart_count' => $cart->item_count,
        'cart_total' => $cart->total,
        'cart' => $cart->getSummary()
    ]);
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
    public function remove($listingId, Request $request)
{
    if (!Auth::check()) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized'
        ], 401);
    }

    try {
        $cart = $this->getOrCreateCart();
        
        $variantId = $request->input('variant_id');
        $color = $request->input('color');
        $size = $request->input('size');
        
        $cart->removeVariantItem($listingId, $variantId, $color, $size);
        
        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart',
            'cart_count' => $cart->item_count,
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