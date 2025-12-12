<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use App\Models\Listing;
use App\Models\Escrow;
use App\Models\BuyerWallet;
use App\Models\WalletTransaction;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class OrderController extends Controller
{
    /**
     * Display buyer's orders
     */
    public function index()
    {
        $orders = Order::where('buyer_id', Auth::id())
            ->with(['items.listing', 'vendorProfile.user'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('buyer.orders.index', compact('orders'));
    }

    /**
     * Display single order - FIXED METHOD
     */
   public function show($id)
{
    $order = Order::findOrFail($id);
    
    // Check ownership
    if ($order->buyer_id !== Auth::id()) {
        abort(403);
    }

    $order->load(['items.listing.images', 'vendorProfile.user', 'payments', 'escrow']);
    
    // Add this line to get wallet balance:
    $wallet = BuyerWallet::where('user_id', Auth::id())->first();
    $walletBalance = $wallet ? $wallet->balance : 0;

    return view('buyer.orders.show', compact('order', 'walletBalance'));
}

    /**
     * Display checkout page
     */
    public function checkout()
    {
        $cart = Cart::where('user_id', Auth::id())->first();
        
        if (!$cart || empty($cart->items)) {
            return redirect()->route('buyer.cart.index')
                ->with('error', 'Your cart is empty');
        }

        // Get user's wallet
        $wallet = BuyerWallet::where('user_id', Auth::id())->first();

        return view('buyer.orders.checkout', compact('cart', 'wallet'));
    }

    /**
     * Place order
     */
    public function placeOrder(Request $request)
    {
        $validated = $request->validate([
            'shipping_address' => 'required|string|max:500',
            'shipping_city' => 'required|string|max:100',
            'shipping_country' => 'required|string|max:100',
            'shipping_postal_code' => 'nullable|string|max:20',
            'payment_method' => 'required|in:cash_on_delivery,wallet,card,mobile_money,bank_transfer',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Get cart
        $cart = Cart::where('user_id', Auth::id())->first();
        
        if (!$cart || empty($cart->items)) {
            return back()->with('error', 'Your cart is empty');
        }

        // Validate wallet balance if wallet payment
        if ($validated['payment_method'] === 'wallet') {
            $wallet = BuyerWallet::where('user_id', Auth::id())->first();
            if (!$wallet || $wallet->balance < $cart->total) {
                return back()->with('error', 'Insufficient wallet balance');
            }
        }

        DB::beginTransaction();
        try {
            // Group items by vendor
            $itemsByVendor = [];
            foreach ($cart->items as $item) {
                $listing = Listing::with('vendor')->find($item['listing_id']);
                
                if (!$listing || !$listing->is_active) {
                    throw new \Exception('Product "' . $item['title'] . '" is no longer available');
                }
                
                if ($listing->stock < $item['quantity']) {
                    throw new \Exception('Insufficient stock for "' . $item['title'] . '"');
                }
                
                $vendorId = $listing->vendor_profile_id;
                if (!isset($itemsByVendor[$vendorId])) {
                    $itemsByVendor[$vendorId] = [
                        'items' => [],
                        'vendor' => $listing->vendor
                    ];
                }
                $itemsByVendor[$vendorId]['items'][] = [
                    'listing' => $listing,
                    'cart_item' => $item
                ];
            }

            $orders = [];
            
            // Create separate order for each vendor
            foreach ($itemsByVendor as $vendorId => $vendorData) {
                $orderNumber = 'ORD-' . strtoupper(Str::random(8));
                
                // Calculate order totals
                $subtotal = 0;
                foreach ($vendorData['items'] as $itemData) {
                    $subtotal += $itemData['listing']->price * $itemData['cart_item']['quantity'];
                }
                
                $shipping = $this->calculateShipping($vendorData['items']);
                $taxes = $subtotal * 0.18; // 18% VAT
                $platformCommission = $subtotal * 0.15; // 15% platform fee
                $total = $subtotal + $shipping + $taxes;

                // Create order with proper status
                $orderStatus = 'pending';
                if ($validated['payment_method'] === 'cash_on_delivery') {
                    $orderStatus = 'pending';
                } elseif ($validated['payment_method'] === 'wallet') {
                    $orderStatus = 'confirmed';
                } else {
                    $orderStatus = 'payment_pending';
                }

                $order = Order::create([
                    'order_number' => $orderNumber,
                    'buyer_id' => Auth::id(),
                    'vendor_profile_id' => $vendorId,
                    'status' => $orderStatus,
                    'subtotal' => $subtotal,
                    'shipping' => $shipping,
                    'taxes' => $taxes,
                    'platform_commission' => $platformCommission,
                    'total' => $total,
                    'meta' => json_encode([
                        'shipping_address' => $validated['shipping_address'],
                        'shipping_city' => $validated['shipping_city'],
                        'shipping_country' => $validated['shipping_country'],
                        'shipping_postal_code' => $validated['shipping_postal_code'] ?? null,
                        'payment_method' => $validated['payment_method'],
                        'notes' => $validated['notes'] ?? null,
                    ])
                ]);

                // Create order items
                foreach ($vendorData['items'] as $itemData) {
                    $listing = $itemData['listing'];
                    $cartItem = $itemData['cart_item'];
                    
                    OrderItem::create([
                        'order_id' => $order->id,
                        'listing_id' => $listing->id,
                        'title' => $listing->title,
                        'quantity' => $cartItem['quantity'],
                        'unit_price' => $listing->price,
                        'line_total' => $listing->price * $cartItem['quantity'],
                        'attributes' => json_encode([
                            'sku' => $listing->sku ?? '',
                            'weight_kg' => $listing->weight_kg ?? 0,
                            'origin' => $listing->origin ?? 'local',
                        ])
                    ]);

                    // Reduce stock
                    $listing->decrement('stock', $cartItem['quantity']);
                }

                // Handle payment based on method
                if ($validated['payment_method'] === 'cash_on_delivery') {
                    Payment::create([
                        'order_id' => $order->id,
                        'provider' => 'cash',
                        'amount' => $total,
                        'payment_method' => 'cash_on_delivery',
                        'status' => 'pending',
                        'meta' => json_encode([
                            'payment_due' => 'on_delivery',
                            'created_at' => now()->toDateTimeString()
                        ])
                    ]);
                    
                } elseif ($validated['payment_method'] === 'wallet') {
                    // Deduct from wallet
                    $wallet = BuyerWallet::where('user_id', Auth::id())->first();
                    $wallet->decrement('balance', $total);
                    
                    // Create escrow
                    Escrow::create([
                        'order_id' => $order->id,
                        'amount' => $total,
                        'status' => 'holding',
                        'released_at' => null,
                        'meta' => json_encode([
                            'payment_method' => 'wallet',
                            'created_at' => now()->toDateTimeString()
                        ])
                    ]);
                    
                    // Create payment record
                    Payment::create([
                        'order_id' => $order->id,
                        'provider' => 'wallet',
                        'amount' => $total,
                        'payment_method' => 'wallet',
                        'status' => 'completed',
                        'transaction_id' => 'WALLET-' . Str::random(12),
                        'meta' => json_encode([
                            'wallet_id' => $wallet->id,
                            'paid_at' => now()->toDateTimeString()
                        ])
                    ]);
                    
                } else {
                    // For other payment methods
                    Payment::create([
                        'order_id' => $order->id,
                        'provider' => $validated['payment_method'],
                        'amount' => $total,
                        'payment_method' => $validated['payment_method'],
                        'status' => 'pending',
                        'meta' => json_encode([
                            'payment_url' => null,
                            'created_at' => now()->toDateTimeString()
                        ])
                    ]);
                }

                $orders[] = $order;
            }

            // Clear cart
            $cart->update([
                'items' => [],
                'subtotal' => 0,
                'shipping' => 0,
                'tax' => 0,
                'total' => 0
            ]);

            DB::commit();

            // Redirect based on payment method
            if ($validated['payment_method'] === 'cash_on_delivery' || $validated['payment_method'] === 'wallet') {
                return redirect()->route('buyer.orders.show', $orders[0]->id)
                    ->with('success', 'Order placed successfully!');
            } else {
                return redirect()->route('buyer.orders.index')
                    ->with('success', 'Order created. Please complete payment to confirm your order.');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Place order error: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            return back()->withInput()->with('error', 'Failed to place order: ' . $e->getMessage());
        }
    }

    /**
     * Cancel order
     */
    public function cancelOrder(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        
        // Check ownership
        if ($order->buyer_id !== Auth::id()) {
            abort(403);
        }

        // Can only cancel pending or confirmed orders
        if (!in_array($order->status, ['pending', 'confirmed', 'payment_pending'])) {
            return back()->with('error', 'This order cannot be cancelled');
        }

        DB::beginTransaction();
        try {
            // Restore stock
            foreach ($order->items as $item) {
                $item->listing->increment('stock', $item->quantity);
            }

            // Refund if payment was made
            if ($order->escrow) {
                $wallet = BuyerWallet::where('user_id', Auth::id())->first();
                $wallet->increment('balance', $order->escrow->amount);
                
                $order->escrow->update([
                    'status' => 'refunded',
                    'released_at' => now()
                ]);
            }

            $order->update([
                'status' => 'cancelled',
                'meta' => array_merge($order->meta ?? [], [
                    'cancelled_at' => now()->toDateTimeString(),
                    'cancelled_by' => 'buyer',
                    'cancellation_reason' => $request->input('reason', 'Buyer requested cancellation')
                ])
            ]);

            DB::commit();

            return back()->with('success', 'Order cancelled successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Cancel order error: ' . $e->getMessage());
            
            return back()->with('error', 'Failed to cancel order');
        }
    }

    /**
     * Confirm delivery
     */
    public function confirmDelivery($id)
    {
        $order = Order::findOrFail($id);
        
        // Check ownership
        if ($order->buyer_id !== Auth::id()) {
            abort(403);
        }

        if ($order->status !== 'shipped') {
            return back()->with('error', 'Order must be shipped before confirming delivery');
        }

        DB::beginTransaction();
        try {
            // Update order status
            $order->update([
                'status' => 'delivered',
                'meta' => array_merge($order->meta ?? [], [
                    'delivered_at' => now()->toDateTimeString(),
                    'confirmed_by_buyer' => true
                ])
            ]);

            // Release escrow to vendor
            if ($order->escrow && $order->escrow->status === 'holding') {
                $vendorWallet = BuyerWallet::firstOrCreate(
                    ['user_id' => $order->vendorProfile->user_id],
                    ['balance' => 0]
                );

                $amountToVendor = $order->total - $order->platform_commission;
                
                $vendorWallet->increment('balance', $amountToVendor);
                
                $order->escrow->update([
                    'status' => 'released',
                    'released_at' => now()
                ]);
            }

            // Handle COD payment if applicable
            $meta = json_decode($order->meta, true);
            if (isset($meta['payment_method']) && $meta['payment_method'] === 'cash_on_delivery') {
                $payment = $order->payments()->where('status', 'pending')->first();
                if ($payment) {
                    $payment->update([
                        'status' => 'completed',
                        'meta' => array_merge($payment->meta ?? [], [  
                            'paid_at' => now()->toDateTimeString(),
                            'payment_confirmed' => true
                        ])
                    ]);
                }
            }

            DB::commit();

            return back()->with('success', 'Delivery confirmed! Thank you for your purchase.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Confirm delivery error: ' . $e->getMessage());
            
            return back()->with('error', 'Failed to confirm delivery');
        }
    }

    /**
     * Pay for order using wallet balance
     */
   public function payWithWallet(Request $request, $id)
{
    $order = Order::findOrFail($id);
    
    // Check ownership
    if ($order->buyer_id !== Auth::id()) {
        abort(403);
    }
    
    // Check if order can be paid
    if (!in_array($order->status, ['pending', 'payment_pending', 'confirmed'])) {
        return back()->with('error', 'This order cannot be paid. Current status: ' . $order->status);
    }
    
    // Get buyer's wallet
    $wallet = BuyerWallet::where('user_id', Auth::id())->first();
    
    if (!$wallet) {
        return back()->with('error', 'Wallet not found');
    }
    
    // Check if payment already exists
    $existingPayment = Payment::where('order_id', $order->id)
        ->whereIn('status', ['completed', 'processing'])
        ->first();
    
    if ($existingPayment) {
        return back()->with('error', 'Payment already exists for this order');
    }
    
    // Check balance
    if ($wallet->balance < $order->total) {
        $required = $order->total - $wallet->balance;
        return back()->with('error', 'Insufficient wallet balance. Please deposit UGX ' . number_format($required, 0) . ' more.');
    }
    
    DB::beginTransaction();
    try {
        // 1. Create wallet transaction
        $transaction = WalletTransaction::create([
            'user_id' => Auth::id(),
            'type' => 'order_payment',
            'amount' => -$order->total, // Negative amount for deduction
            'balance_before' => $wallet->balance,
            'balance_after' => $wallet->balance - $order->total,
            'reference' => 'ORD-' . $order->order_number,
            'description' => 'Payment for Order #' . $order->order_number,
            'status' => 'completed',
            'meta' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number
            ]
        ]);
        
        // 2. Deduct from wallet
        $wallet->decrement('balance', $order->total);
        
        // 3. Create payment record
        $payment = Payment::create([
            'order_id' => $order->id,
            'provider' => 'wallet',
            'provider_payment_id' => 'WALLET-' . $transaction->id,
            'amount' => $order->total,
            'status' => 'completed'
        ]);
        
        // 4. Update order status
        $order->update([
            'status' => 'paid'
        ]);
        
        // 5. Create escrow record
        $escrow = Escrow::create([
            'order_id' => $order->id,
            'amount' => $order->total - ($order->platform_commission ?? 0),
            'status' => 'held', // ← THIS MUST BE 'held' (not 'holding' or 'hold')
            'release_at' => now()->addDays(7),
            'meta' => [
                'buyer_id' => Auth::id(),
                'vendor_id' => $order->vendor_profile_id,
                'order_total' => $order->total,
                'commission' => $order->platform_commission ?? 0,
                'created_at' => now()->toDateTimeString()
            ]
        ]);
        
        DB::commit();
        
        return redirect()->route('buyer.orders.show', $order->id)
            ->with('success', 'Payment successful! Order #' . $order->order_number . ' has been confirmed.');
            
    } catch (\Exception $e) {
        DB::rollBack();
        
        \Log::error('Wallet payment failed: ' . $e->getMessage());
        
        return back()->with('error', 'Payment failed: ' . $e->getMessage());
    }
}
    
    /**
     * Calculate shipping cost
     */
    private function calculateShipping($items)
    {
        $totalWeight = 0;
        foreach ($items as $itemData) {
            $listing = $itemData['listing'];
            $cartItem = $itemData['cart_item'];
            $totalWeight += ($listing->weight_kg ?? 0) * $cartItem['quantity'];
        }

        // Simplified shipping calculation
        if ($totalWeight <= 1) return 5;
        if ($totalWeight <= 5) return 10;
        if ($totalWeight <= 10) return 15;
        if ($totalWeight <= 20) return 25;
        return 50;
    }


/**
 * Show payment page for order
 */
public function payment($id)
{
    $order = Order::findOrFail($id);
    
    // Check ownership
    if ($order->buyer_id !== Auth::id()) {
        abort(403);
    }

    // Check if payment is pending
    if ($order->status !== 'payment_pending') {
        return redirect()->route('buyer.orders.show', $order)
            ->with('error', 'This order does not require payment');
    }

    // Get wallet balance
    $wallet = BuyerWallet::where('user_id', Auth::id())->first();
    $walletBalance = $wallet ? $wallet->balance : 0;

    return view('buyer.orders.payment', compact('order', 'walletBalance'));
}
}