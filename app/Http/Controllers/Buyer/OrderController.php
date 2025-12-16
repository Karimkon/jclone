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
use App\Models\ShippingAddress;
use App\Models\Payment;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * Display buyer's orders - ADD DELIVERY INFO
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
        
        // Get shipping addresses
        $addresses = Auth::user()->shippingAddresses()
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Get or create default address if none exist
        if ($addresses->isEmpty()) {
            $defaultAddress = Auth::user()->getOrCreateDefaultAddress();
            $addresses = collect([$defaultAddress]);
        }

        return view('buyer.orders.checkout', compact('cart', 'wallet', 'addresses'));
    }

    /**
     * Place order - UPDATED with confirmed_at timestamp
     */
    public function placeOrder(Request $request)
    {
        $validated = $request->validate([
            'shipping_address_id' => 'required|exists:shipping_addresses,id',
            'payment_method' => 'required|in:cash_on_delivery,wallet,card,mobile_money,bank_transfer',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Verify address belongs to user
        $shippingAddress = ShippingAddress::where('id', $validated['shipping_address_id'])
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Get cart
        $cart = Cart::where('user_id', Auth::id())->first();
        
        if (!$cart || empty($cart->items)) {
            return back()->with('error', 'Your cart is empty');
        }

        ActivityLogger::logOrderCreated($order);
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
                    throw new \Exception('Product "' . ($item['title'] ?? $listing?->title ?? '') . '" is no longer available');
                }

                // Ensure unit_price is present for downstream calculations
                $item['unit_price'] = $item['unit_price'] ?? ($item['price'] ?? $listing->price);
                
                // Stock check: use variant stock when applicable, otherwise listing stock
                if (!empty($item['variant_id'])) {
                    $variant = \App\Models\ListingVariant::find($item['variant_id']);
                    if (!$variant || $variant->stock < $item['quantity']) {
                        throw new \Exception('Insufficient stock for "' . ($item['title'] ?? $listing->title) . '"');
                    }
                } else {
                    if ($listing->stock < $item['quantity']) {
                        throw new \Exception('Insufficient stock for "' . ($item['title'] ?? $listing->title) . '"');
                    }
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
                    $cartItem = $itemData['cart_item'];
                    $unitPrice = $cartItem['unit_price'] ?? ($cartItem['price'] ?? $itemData['listing']->price);
                    $subtotal += $unitPrice * ($cartItem['quantity'] ?? 0);
                }
                
                $shipping = $this->calculateShipping($vendorData['items']);
                $taxes = $subtotal * 0.18; // 18% VAT
                $platformCommission = $subtotal * 0.15; // 15% platform fee
                $total = $subtotal + $shipping + $taxes;

                // Determine order status and timestamps
                $orderStatus = 'pending';
                $confirmedAt = null;
                
                if ($validated['payment_method'] === 'cash_on_delivery') {
                    $orderStatus = 'pending';
                } elseif ($validated['payment_method'] === 'wallet') {
                    $orderStatus = 'confirmed';
                    $confirmedAt = now();
                } else {
                    $orderStatus = 'payment_pending';
                }

                // Create order with shipping address reference
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
                    'confirmed_at' => $confirmedAt, // ADDED
                    'meta' => [
                        'shipping_address_id' => $shippingAddress->id,
                        'shipping_address' => [
                            'label' => $shippingAddress->label,
                            'recipient_name' => $shippingAddress->recipient_name,
                            'recipient_phone' => $shippingAddress->recipient_phone,
                            'address_line_1' => $shippingAddress->address_line_1,
                            'address_line_2' => $shippingAddress->address_line_2,
                            'city' => $shippingAddress->city,
                            'state_region' => $shippingAddress->state_region,
                            'postal_code' => $shippingAddress->postal_code,
                            'country' => $shippingAddress->country,
                            'delivery_instructions' => $shippingAddress->delivery_instructions,
                            'full_address' => $shippingAddress->full_address
                        ],
                        'payment_method' => $validated['payment_method'],
                        'notes' => $validated['notes'] ?? null,
                    ]
                ]);

                 // TRACK PURCHASES - Add this after order creation
    $cart = Cart::where('user_id', auth()->id())->first();
    
    if ($cart && $cart->items) {
        $analyticsService = app(\App\Services\ProductAnalyticsService::class);
        
        foreach ($cart->items as $item) {
            $listing = Listing::find($item['listing_id']);
            if ($listing) {
                $unitPrice = $item['unit_price'] ?? $listing->price;
                $analyticsService->trackPurchase(
                    $listing->id,
                    $order->id,
                    $item['quantity'],
                    $unitPrice * $item['quantity']
                );
            }
        }
    }
    

                // Create order items (with variant support)
                foreach ($vendorData['items'] as $itemData) {
                    $listing = $itemData['listing'];
                    $cartItem = $itemData['cart_item'];
                    $unitPrice = $cartItem['unit_price'] ?? ($cartItem['price'] ?? $listing->price);
                    $lineTotal = $unitPrice * ($cartItem['quantity'] ?? 0);
                    
                    // Enrich attributes for vendor visibility
                    $variant = null;
                    if (!empty($cartItem['variant_id'])) {
                        $variant = \App\Models\ListingVariant::find($cartItem['variant_id']);
                    }
                    
                    OrderItem::create([
                        'order_id' => $order->id,
                        'listing_id' => $listing->id,
                        'title' => $listing->title,
                        'quantity' => $cartItem['quantity'],
                        'unit_price' => $unitPrice,
                        'line_total' => $lineTotal,
                        'attributes' => [
                            'sku' => $listing->sku ?? '',
                            'weight_kg' => $listing->weight_kg ?? 0,
                            'origin' => $listing->origin ?? 'local',
                            'variant_id' => $cartItem['variant_id'] ?? null,
                            'variant_sku' => $variant?->sku,
                            'variant_name' => $variant?->display_name,
                            'color' => $cartItem['color'] ?? ($variant?->color() ?? null),
                            'size' => $cartItem['size'] ?? ($variant?->size() ?? null),
                            'attributes' => $variant?->attributes,
                        ]
                    ]);

                    // Reduce stock
                    if (isset($cartItem['variant_id'])) {
                        $variant = \App\Models\ListingVariant::find($cartItem['variant_id']);
                        if ($variant) {
                            $variant->decrement('stock', $cartItem['quantity']);
                        }
                    } else {
                        $listing->decrement('stock', $cartItem['quantity']);
                    }
                }

                // Handle payment based on method
                if ($validated['payment_method'] === 'cash_on_delivery') {
                    Payment::create([
                        'order_id' => $order->id,
                        'provider' => 'cash',
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
                    $wallet = BuyerWallet::where('user_id', Auth::id())->first();
                    $wallet->decrement('balance', $total);
                    
                    // Create escrow
                    Escrow::create([
                        'order_id' => $order->id,
                        'amount' => $total,
                        'status' => 'held',
                        'released_at' => null,
                        'meta' => [
                            'payment_method' => 'wallet',
                            'created_at' => now()->toDateTimeString()
                        ]
                    ]);
                    
                    // Create payment record
                    Payment::create([
                        'order_id' => $order->id,
                        'provider' => 'wallet',
                        'amount' => $total,
                        'payment_method' => 'wallet',
                        'status' => 'completed',
                        'transaction_id' => 'WALLET-' . Str::random(12),
                        'meta' => [
                            'wallet_id' => $wallet->id,
                            'paid_at' => now()->toDateTimeString()
                        ]
                    ]);
                    
                } else {
                    // For other payment methods
                    Payment::create([
                        'order_id' => $order->id,
                        'provider' => $validated['payment_method'],
                        'amount' => $total,
                        'payment_method' => $validated['payment_method'],
                        'status' => 'pending',
                        'meta' => [
                            'payment_url' => null,
                            'created_at' => now()->toDateTimeString()
                        ]
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

            // Use the new method for cancellation
            $order->updateStatusWithTimestamps('cancelled');
            
            // Add cancellation reason
            $order->update([
                'meta' => array_merge($order->meta ?? [], [
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

 public function confirmDelivery($id)
{
    $order = Order::findOrFail($id);
    
    // Check ownership
    if ($order->buyer_id !== Auth::id()) {
        abort(403);
    }

    // Check if order can be confirmed (must be shipped)
    if ($order->status !== 'shipped') {
        return back()->with('error', 'Order must be shipped before confirming delivery');
    }

    // Check if this is a COD order
    $meta = $order->meta ?? [];
    $isCOD = isset($meta['payment_method']) && $meta['payment_method'] === 'cash_on_delivery';
    
    if (!$isCOD) {
        return back()->with('error', 'This order is not cash on delivery');
    }

    DB::beginTransaction();
    try {
        // Calculate delivery time in days
        $deliveryTimeDays = 0;
        if ($order->shipped_at) {
            $deliveryTimeDays = $order->shipped_at->diffInDays(now());
        } else {
            // Fallback to created_at if shipped_at is null
            $deliveryTimeDays = $order->created_at->diffInDays(now());
        }
        
        // Calculate delivery score
        $deliveryScore = $this->calculateDeliveryScore($deliveryTimeDays);
        
        // Update order status using the new method
        $order->updateStatusWithTimestamps('delivered');
        
        // Add COD confirmation details
        $order->update([
            'delivery_time_days' => $deliveryTimeDays,
            'delivery_score' => $deliveryScore,
            'meta' => array_merge($meta, [
                'confirmed_by_buyer' => true,
                'buyer_confirmed_at' => now()->toDateTimeString(),
                'payment_confirmed' => true,
                'delivery_metrics' => [
                    'delivery_time_days' => $deliveryTimeDays,
                    'delivery_score' => $deliveryScore,
                    'calculated_at' => now()->toDateTimeString()
                ]
            ])
        ]);
        
        // Update vendor performance
        $this->updateVendorPerformance($order);

        // Handle COD payment
        $payment = $order->payments()->where('provider', 'cash')->first();
        if ($payment) {
            $payment->update([
                'status' => 'completed',
                'meta' => array_merge($payment->meta ?? [], [
                    'paid_at' => now()->toDateTimeString(),
                    'payment_confirmed_by_buyer' => true,
                    'payment_confirmation_time' => now()->toDateTimeString()
                ])
            ]);
            
            // Create platform commission transaction
            $platformWallet = BuyerWallet::firstOrCreate(
                ['user_id' => 1], // Assuming user ID 1 is platform admin
                ['balance' => 0]
            );
            
            $platformWallet->increment('balance', $order->platform_commission);
            
            // Create vendor wallet transaction
            $vendorWallet = BuyerWallet::firstOrCreate(
                ['user_id' => $order->vendorProfile->user_id],
                ['balance' => 0]
            );
            
            $amountToVendor = $order->total - $order->platform_commission;
            $vendorWallet->increment('balance', $amountToVendor);
            
            // Log transactions
            WalletTransaction::create([
                'user_id' => $order->vendorProfile->user_id,
                'type' => 'vendor_payout',
                'amount' => $amountToVendor,
                'balance_before' => $vendorWallet->balance - $amountToVendor,
                'balance_after' => $vendorWallet->balance,
                'reference' => 'COD-' . $order->order_number,
                'description' => 'Cash on Delivery payment received for Order #' . $order->order_number,
                'status' => 'completed',
                'meta' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'is_cod' => true
                ]
            ]);
        }

        DB::commit();

        return back()->with('success', 'Delivery confirmed and payment recorded! Thank you for your purchase.');

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Confirm delivery error: ' . $e->getMessage());
        \Log::error($e->getTraceAsString());
        
        return back()->with('error', 'Failed to confirm delivery: ' . $e->getMessage());
    }
}

/**
 * Calculate delivery score based on delivery time
 */
private function calculateDeliveryScore($deliveryTimeDays)
{
    // Score calculation:
    // 0-3 days: 100-90 points
    // 4-7 days: 89-70 points  
    // 8-14 days: 69-50 points
    // 15+ days: 49-30 points
    
    if ($deliveryTimeDays <= 3) {
        return max(90, 100 - ($deliveryTimeDays * 3));
    } elseif ($deliveryTimeDays <= 7) {
        return max(70, 89 - (($deliveryTimeDays - 3) * 5));
    } elseif ($deliveryTimeDays <= 14) {
        return max(50, 69 - (($deliveryTimeDays - 7) * 3));
    } else {
        return max(30, 49 - (($deliveryTimeDays - 14) * 2));
    }
}


/**
 * Update vendor performance metrics
 */
private function updateVendorPerformance($order)
{
    $vendorId = $order->vendor_profile_id;
    
    // Get vendor performance record or create new
    $performance = \App\Models\VendorPerformance::where('vendor_profile_id', $vendorId)->first();
    
    if (!$performance) {
        $performance = new \App\Models\VendorPerformance();
        $performance->vendor_profile_id = $vendorId;
        $performance->delivered_orders = 0;
        $performance->total_delivery_days = 0;
        $performance->total_delivery_score = 0;
        $performance->total_processing_hours = 0;
        $performance->on_time_deliveries = 0;
    }
    
    // Calculate processing time if available
    $processingHours = 0;
    if ($order->processing_at && $order->shipped_at) {
        $processingHours = $order->processing_at->diffInHours($order->shipped_at);
    }
    
    // Check if delivery was on time (within 7 days)
    $isOnTime = $order->delivery_time_days <= 7;
    
    // Update performance metrics
    $performance->delivered_orders += 1;
    $performance->total_delivery_days += $order->delivery_time_days;
    $performance->total_delivery_score += $order->delivery_score;
    $performance->total_processing_hours += $processingHours;
    
    if ($isOnTime) {
        $performance->on_time_deliveries += 1;
    }
    
    // Calculate averages
    $performance->avg_delivery_time_days = $performance->total_delivery_days / $performance->delivered_orders;
    $performance->avg_delivery_score = $performance->total_delivery_score / $performance->delivered_orders;
    $performance->on_time_delivery_rate = ($performance->on_time_deliveries / $performance->delivered_orders) * 100;
    $performance->avg_processing_time_hours = $performance->total_processing_hours / $performance->delivered_orders;
    
    // Calculate overall performance score
    $performance->performance_score = $this->calculateOverallScore($performance);
    
    $performance->save();
}

/**
 * Calculate overall vendor performance score
 */
private function calculateOverallScore($performance)
{
    $score = 0;
    
    // Delivery time weight: 40%
    if ($performance->avg_delivery_time_days <= 3) {
        $score += 40;
    } elseif ($performance->avg_delivery_time_days <= 5) {
        $score += 35;
    } elseif ($performance->avg_delivery_time_days <= 7) {
        $score += 30;
    } elseif ($performance->avg_delivery_time_days <= 10) {
        $score += 25;
    } else {
        $score += 20;
    }
    
    // On-time rate weight: 30%
    if ($performance->on_time_delivery_rate >= 95) {
        $score += 30;
    } elseif ($performance->on_time_delivery_rate >= 90) {
        $score += 27;
    } elseif ($performance->on_time_delivery_rate >= 85) {
        $score += 24;
    } elseif ($performance->on_time_delivery_rate >= 80) {
        $score += 21;
    } else {
        $score += 18;
    }
    
    // Delivery score weight: 30%
    $score += ($performance->avg_delivery_score / 100) * 30;
    
    return round($score, 2);
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
        if (!in_array($order->status, ['pending', 'payment_pending'])) {
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
            
            // 4. Update order status WITH confirmed_at timestamp
            $order->update([
                'status' => 'confirmed',
                'confirmed_at' => now(),
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
    
    /**
     * Add tracking info view (for buyer)
     */
    public function tracking($id)
    {
        $order = Order::findOrFail($id);
        
        // Check ownership
        if ($order->buyer_id !== Auth::id()) {
            abort(403);
        }

        $order->load(['vendorProfile.user']);
        
        return view('buyer.orders.tracking', compact('order'));
    }
    
    /**
     * Request refund
     */
    public function requestRefund(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        
        // Check ownership
        if ($order->buyer_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'reason' => 'required|string|max:1000',
            'evidence' => 'nullable|array',
            'evidence.*' => 'image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Only allow refund requests for delivered orders within 30 days
        if ($order->status !== 'delivered') {
            return back()->with('error', 'Only delivered orders can be refunded');
        }

        if (!$order->delivered_at || $order->delivered_at->diffInDays(now()) > 30) {
            return back()->with('error', 'Refund requests must be made within 30 days of delivery');
        }

        // Store evidence files
        $evidencePaths = [];
        if ($request->hasFile('evidence')) {
            foreach ($request->file('evidence') as $file) {
                $path = $file->store('refunds/' . $order->id, 'public');
                $evidencePaths[] = $path;
            }
        }

        // Create refund request
        \App\Models\RefundRequest::create([
            'order_id' => $order->id,
            'buyer_id' => Auth::id(),
            'vendor_id' => $order->vendor_profile_id,
            'amount' => $order->total,
            'reason' => $request->reason,
            'evidence' => $evidencePaths,
            'status' => 'pending',
            'meta' => [
                'requested_at' => now()->toDateTimeString(),
                'delivery_date' => $order->delivered_at->toDateTimeString(),
                'delivery_time_days' => $order->delivery_time_days,
                'delivery_score' => $order->delivery_score,
            ]
        ]);

        return back()->with('success', 'Refund request submitted successfully. Admin will review.');
    }
}