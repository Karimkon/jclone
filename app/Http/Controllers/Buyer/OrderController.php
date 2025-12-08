<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use App\Models\Listing;
use App\Models\Escrow;
use App\Models\Wallet;
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
     * Display single order
     */
    public function show(Order $order)
    {
        // Check ownership
        if ($order->buyer_id !== Auth::id()) {
            abort(403);
        }

        $order->load(['items.listing.images', 'vendorProfile.user', 'payments', 'escrow']);

        return view('buyer.orders.show', compact('order'));
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

        // Validate cart items still exist and have stock
        $validItems = [];
        foreach ($cart->items as $item) {
            $listing = Listing::find($item['listing_id']);
            if ($listing && $listing->is_active && $listing->stock >= $item['quantity']) {
                $validItems[] = $item;
            }
        }

        if (empty($validItems)) {
            return redirect()->route('buyer.cart.index')
                ->with('error', 'Some items in your cart are no longer available');
        }

        // Update cart with valid items only
        if (count($validItems) != count($cart->items)) {
            $cart->items = $validItems;
            $cart->recalculateTotals();
            $cart->save();
        }

        // Get user's wallet
        $wallet = Wallet::where('user_id', Auth::id())->first();

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
            $wallet = Wallet::where('user_id', Auth::id())->first();
            if (!$wallet || $wallet->available_balance < $cart->total) {
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
                    throw new \Exception('Product ' . $item['title'] . ' is no longer available');
                }
                
                if ($listing->stock < $item['quantity']) {
                    throw new \Exception('Insufficient stock for ' . $item['title']);
                }
                
                $vendorId = $listing->vendor_profile_id;
                if (!isset($itemsByVendor[$vendorId])) {
                    $itemsByVendor[$vendorId] = [];
                }
                $itemsByVendor[$vendorId][] = [
                    'listing' => $listing,
                    'quantity' => $item['quantity']
                ];
            }

            $orders = [];
            
            // Create separate order for each vendor
            foreach ($itemsByVendor as $vendorId => $items) {
                $orderNumber = 'ORD-' . strtoupper(Str::random(10));
                
                // Calculate order totals
                $subtotal = 0;
                foreach ($items as $item) {
                    $subtotal += $item['listing']->price * $item['quantity'];
                }
                
                $shipping = $this->calculateShipping($items);
                $taxes = $subtotal * 0.18; // 18% VAT
                $platformCommission = $subtotal * 0.15; // 15% platform fee
                $total = $subtotal + $shipping + $taxes;

                // Create order
                $order = Order::create([
                    'order_number' => $orderNumber,
                    'buyer_id' => Auth::id(),
                    'vendor_profile_id' => $vendorId,
                    'status' => $validated['payment_method'] === 'cash_on_delivery' ? 'pending' : 'payment_pending',
                    'subtotal' => $subtotal,
                    'shipping' => $shipping,
                    'taxes' => $taxes,
                    'platform_commission' => $platformCommission,
                    'total' => $total,
                    'meta' => [
                        'shipping_address' => $validated['shipping_address'],
                        'shipping_city' => $validated['shipping_city'],
                        'shipping_country' => $validated['shipping_country'],
                        'shipping_postal_code' => $validated['shipping_postal_code'] ?? null,
                        'payment_method' => $validated['payment_method'],
                        'notes' => $validated['notes'] ?? null,
                    ]
                ]);

                // Create order items
                foreach ($items as $item) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'listing_id' => $item['listing']->id,
                        'title' => $item['listing']->title,
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['listing']->price,
                        'line_total' => $item['listing']->price * $item['quantity'],
                        'attributes' => [
                            'sku' => $item['listing']->sku,
                            'weight_kg' => $item['listing']->weight_kg,
                            'origin' => $item['listing']->origin,
                        ]
                    ]);

                    // Reduce stock
                    $item['listing']->decrement('stock', $item['quantity']);
                }

                // Handle payment based on method
                if ($validated['payment_method'] === 'cash_on_delivery') {
                    // For COD, order is confirmed but payment is pending
                    $order->update(['status' => 'confirmed']);
                    
                    // Create payment record
                    Payment::create([
                        'order_id' => $order->id,
                        'amount' => $total,
                        'payment_method' => 'cash_on_delivery',
                        'status' => 'pending',
                        'meta' => [
                            'payment_due' => 'on_delivery',
                            'created_at' => now()->toDateTimeString()
                        ]
                    ]);
                    
                } elseif ($validated['payment_method'] === 'wallet') {
                    // Deduct from wallet
                    $wallet = Wallet::where('user_id', Auth::id())->first();
                    $wallet->decrement('available_balance', $total);
                    $wallet->increment('pending_balance', $total);
                    
                    // Create escrow
                    Escrow::create([
                        'order_id' => $order->id,
                        'amount' => $total,
                        'status' => 'holding',
                        'released_at' => null,
                        'meta' => [
                            'payment_method' => 'wallet',
                            'created_at' => now()->toDateTimeString()
                        ]
                    ]);
                    
                    // Create payment record
                    Payment::create([
                        'order_id' => $order->id,
                        'amount' => $total,
                        'payment_method' => 'wallet',
                        'status' => 'completed',
                        'transaction_id' => 'WALLET-' . Str::random(12),
                        'meta' => [
                            'wallet_id' => $wallet->id,
                            'paid_at' => now()->toDateTimeString()
                        ]
                    ]);
                    
                    $order->update(['status' => 'confirmed']);
                    
                } else {
                    // For other payment methods (card, mobile money, bank transfer)
                    // We'll handle payment gateway integration later
                    // For now, just create payment record and keep status as payment_pending
                    
                    Payment::create([
                        'order_id' => $order->id,
                        'amount' => $total,
                        'payment_method' => $validated['payment_method'],
                        'status' => 'pending',
                        'meta' => [
                            'payment_url' => null, // Will be populated by payment gateway
                            'created_at' => now()->toDateTimeString()
                        ]
                    ]);
                }

                $orders[] = $order;
            }

            // Clear cart
            $cart->items = [];
            $cart->subtotal = 0;
            $cart->shipping = 0;
            $cart->tax = 0;
            $cart->total = 0;
            $cart->save();

            DB::commit();

            // Redirect based on payment method
            if ($validated['payment_method'] === 'cash_on_delivery' || $validated['payment_method'] === 'wallet') {
                return redirect()->route('buyer.orders.show', $orders[0])
                    ->with('success', 'Order placed successfully!');
            } else {
                // For other payment methods, redirect to payment gateway
                // For now, just show success message
                return redirect()->route('buyer.orders.index')
                    ->with('success', 'Order created. Please complete payment to confirm your order.');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Place order error: ' . $e->getMessage());
            
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Cancel order
     */
    public function cancelOrder(Request $request, Order $order)
    {
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
                $wallet = Wallet::where('user_id', Auth::id())->first();
                $wallet->decrement('pending_balance', $order->escrow->amount);
                $wallet->increment('available_balance', $order->escrow->amount);
                
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
    public function confirmDelivery(Order $order)
    {
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
                $vendorWallet = Wallet::firstOrCreate(
                    ['user_id' => $order->vendorProfile->user_id],
                    ['available_balance' => 0, 'pending_balance' => 0]
                );

                $amountToVendor = $order->total - $order->platform_commission;
                
                $vendorWallet->increment('available_balance', $amountToVendor);
                
                // Update buyer's wallet
                $buyerWallet = Wallet::where('user_id', Auth::id())->first();
                if ($buyerWallet) {
                    $buyerWallet->decrement('pending_balance', $order->escrow->amount);
                }
                
                $order->escrow->update([
                    'status' => 'released',
                    'released_at' => now()
                ]);
            }

            // Handle COD payment if applicable
            if ($order->meta['payment_method'] === 'cash_on_delivery') {
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
     * Calculate shipping cost
     */
    private function calculateShipping($items)
    {
        $totalWeight = 0;
        foreach ($items as $item) {
            $totalWeight += ($item['listing']->weight_kg ?? 0) * $item['quantity'];
        }

        // Simplified shipping calculation
        if ($totalWeight <= 1) return 5;
        if ($totalWeight <= 5) return 10;
        if ($totalWeight <= 10) return 15;
        if ($totalWeight <= 20) return 25;
        return 50;
    }
}