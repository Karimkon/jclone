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

    if ($cart && !empty($cart->items)) {
        // Remove out-of-stock or inactive items, but preserve associative keys
        $items = $cart->items;
        $changed = false;

        foreach ($items as $itemKey => $item) {
            $listing = Listing::find($item['listing_id']);

            if (!$listing || !$listing->is_active) {
                unset($items[$itemKey]);
                $changed = true;
                continue;
            }

            $stock = $listing->stock;
            if (!empty($item['variant_id'])) {
                $variant = ListingVariant::find($item['variant_id']);
                if ($variant) {
                    $stock = $variant->stock;
                }
            }

            if ($stock <= 0) {
                unset($items[$itemKey]);
                $changed = true;
            }
        }

        if ($changed) {
            $cart->items = $items;
            $cart->recalculateTotals();
            $cart->save();
        }
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
    
    // Always accept color and size parameters
    $rules['color'] = 'nullable|string|max:50';
    $rules['size'] = 'nullable|string|max:50';
    
    // Only add variant_id validation if it's present in the request
    if ($request->has('variant_id') && $request->input('variant_id') !== null) {
        $rules['variant_id'] = 'exists:listing_variants,id';
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
    
    $variant = null;
    $stockAvailable = true;
    $variantPrice = $listing->price;
    
    // Check variant if provided
    if (isset($validated['variant_id'])) {
        $variant = \App\Models\ListingVariant::find($validated['variant_id']);
        if (!$variant) {
            return response()->json([
                'success' => false,
                'message' => 'Selected variant not found'
            ], 404);
        }
        
        // Check variant stock
        if ($variant->stock < $validated['quantity']) {
            return response()->json([
                'success' => false,
                'message' => 'Selected variant is out of stock'
            ], 400);
        }
        
        $variantPrice = $variant->display_price;
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
    
    // Get color and size from request or variant attributes
    $color = $validated['color'] ?? null;
    $size = $validated['size'] ?? null;
    
    // If variant exists, use its attributes if not provided in request
    if ($variant && empty($color) && isset($variant->attributes['color'])) {
        $color = $variant->attributes['color'];
    }
    
    if ($variant && empty($size) && isset($variant->attributes['size'])) {
        $size = $variant->attributes['size'];
    }
    
    // Add item to cart using the model method
    $cart->addItem(
        $listing,
        $validated['quantity'],
        $validated['variant_id'] ?? null,
        $color,
        $size
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
        $variantId = $request->input('variant_id');
        $color = $request->input('color');
        $size = $request->input('size');

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

        $unitPrice = $listing->price;
        $stock = $listing->stock;

        if ($variantId) {
            $variant = ListingVariant::find($variantId);
            if ($variant) {
                $stock = $variant->stock;
                $unitPrice = $variant->display_price ?? $variant->price;
            }
        }

        if ($stock < $quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock. Only ' . $stock . ' available.'
            ], 400);
        }

        try {
            $cart = $this->getOrCreateCart();
            $cart->updateQuantity($listingId, $quantity, $variantId, $color, $size);

            return response()->json([
                'success' => true,
                'message' => 'Cart updated successfully',
                'cart_count' => count($cart->items ?? []),
                'cart_total' => number_format($cart->total, 2),
                'item_total' => number_format($quantity * $unitPrice, 2),
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
     * Update selected items in cart meta
     */
    public function updateSelection(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $cart = $this->getOrCreateCart();
        $selectedKeys = $request->input('selected_keys', []);

        $meta = $cart->meta ?? [];
        $meta['selected_items'] = $selectedKeys;
        $cart->meta = $meta;
        $cart->save();

        return response()->json(['success' => true]);
    }

    /**
     * Remove selected items from cart
     */
    public function removeSelected(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $selectedKeys = $request->input('selected_keys', []);
        if (empty($selectedKeys)) {
            return response()->json(['success' => false, 'message' => 'No items selected'], 400);
        }

        $cart = $this->getOrCreateCart();
        $cart->removeByKeys($selectedKeys);

        return response()->json([
            'success' => true,
            'message' => count($selectedKeys) . ' item(s) removed',
            'cart_count' => $cart->item_count,
            'cart_total' => number_format($cart->total, 2),
            'subtotal' => number_format($cart->subtotal, 2),
            'shipping' => number_format($cart->shipping, 2),
            'tax' => number_format($cart->tax, 2),
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