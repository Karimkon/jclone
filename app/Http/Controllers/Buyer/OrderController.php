<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Cart;
use App\Models\BuyerWallet;
use App\Models\Listing;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Auth::user()->orders()
            ->with(['vendorProfile.user', 'items.listing']);
        
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        $orders = $query->orderBy('created_at', 'desc')
            ->paginate(15);
        
        $stats = [
            'total' => Auth::user()->orders()->count(),
            'pending' => Auth::user()->orders()->where('status', 'pending')->count(),
            'paid' => Auth::user()->orders()->where('status', 'paid')->count(),
            'processing' => Auth::user()->orders()->whereIn('status', ['processing', 'shipped'])->count(),
            'delivered' => Auth::user()->orders()->where('status', 'delivered')->count(),
            'cancelled' => Auth::user()->orders()->where('status', 'cancelled')->count(),
        ];
        
        return view('buyer.orders.index', compact('orders', 'stats'));
    }
    
    public function show(Order $order)
    {
        // Ensure user owns this order
        if ($order->buyer_id !== Auth::id()) {
            abort(403);
        }
        
        $order->load([
            'vendorProfile.user',
            'items.listing.images',
            'payments',
            'escrow'
        ]);
        
        return view('buyer.orders.show', compact('order'));
    }
    
    public function checkout(Request $request)
    {
        $cart = Auth::user()->cart;
        
        if (!$cart || empty($cart->items)) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }
        
        // Verify stock availability
        foreach ($cart->items as $item) {
            $listing = Listing::find($item['listing_id']);
            if (!$listing || $listing->stock < $item['quantity']) {
                return back()->with('error', "{$listing->title} is out of stock.");
            }
        }
        
        $wallet = Auth::user()->buyerWallet;
        $addresses = Auth::user()->meta['addresses'] ?? [];
        
        return view('buyer.checkout', compact('cart', 'wallet', 'addresses'));
    }
    
    public function placeOrder(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|in:wallet,card,bank_transfer,mobile_money',
            'shipping_address' => 'required|string|max:500',
            'shipping_city' => 'required|string|max:100',
            'shipping_country' => 'required|string|max:100',
            'shipping_postal_code' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        $user = Auth::user();
        $cart = $user->cart;
        
        if (!$cart || empty($cart->items)) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }
        
        DB::beginTransaction();
        try {
            // Group items by vendor
            $vendorItems = [];
            foreach ($cart->items as $item) {
                $listing = Listing::findOrFail($item['listing_id']);
                $vendorId = $listing->vendor_profile_id;
                
                if (!isset($vendorItems[$vendorId])) {
                    $vendorItems[$vendorId] = [
                        'vendor_profile_id' => $vendorId,
                        'items' => [],
                        'subtotal' => 0,
                    ];
                }
                
                $vendorItems[$vendorId]['items'][] = [
                    'listing_id' => $listing->id,
                    'title' => $listing->title,
                    'quantity' => $item['quantity'],
                    'unit_price' => $listing->price,
                    'line_total' => $listing->price * $item['quantity'],
                ];
                
                $vendorItems[$vendorId]['subtotal'] += $listing->price * $item['quantity'];
            }
            
            // Create orders for each vendor
            $orders = [];
            foreach ($vendorItems as $vendorId => $vendorData) {
                // Calculate shipping for this vendor
                $vendorWeight = array_sum(array_map(function($item) use ($vendorData) {
                    $listing = Listing::find($item['listing_id']);
                    return $listing->weight_kg * $item['quantity'];
                }, $vendorData['items']));
                
                $shipping = $this->calculateShipping($vendorWeight);
                $tax = $vendorData['subtotal'] * 0.18;
                $platformCommission = $vendorData['subtotal'] * 0.08; // 8% platform fee
                $total = $vendorData['subtotal'] + $shipping + $tax + $platformCommission;
                
                $order = Order::create([
                    'order_number' => 'ORD-' . strtoupper(Str::random(8)),
                    'buyer_id' => $user->id,
                    'vendor_profile_id' => $vendorId,
                    'status' => 'pending',
                    'subtotal' => $vendorData['subtotal'],
                    'shipping' => $shipping,
                    'taxes' => $tax,
                    'platform_commission' => $platformCommission,
                    'total' => $total,
                    'meta' => [
                        'shipping_address' => $request->shipping_address,
                        'shipping_city' => $request->shipping_city,
                        'shipping_country' => $request->shipping_country,
                        'shipping_postal_code' => $request->shipping_postal_code,
                        'notes' => $request->notes,
                        'payment_method' => $request->payment_method,
                    ]
                ]);
                
                // Add order items
                foreach ($vendorData['items'] as $item) {
                    $order->items()->create($item);
                    
                    // Update stock
                    $listing = Listing::find($item['listing_id']);
                    $listing->decrement('stock', $item['quantity']);
                }
                
                $orders[] = $order;
                
                // Process payment based on method
                if ($request->payment_method === 'wallet') {
                    $this->processWalletPayment($user, $total, $order);
                }
                
                // Create escrow for order protection
                \App\Models\Escrow::create([
                    'order_id' => $order->id,
                    'amount' => $total,
                    'status' => 'held',
                    'release_at' => now()->addDays(3), // Release after 3 days if no dispute
                    'meta' => ['payment_method' => $request->payment_method]
                ]);
                
                // Notify vendor
                \App\Models\NotificationQueue::create([
                    'user_id' => $order->vendorProfile->user_id,
                    'type' => 'new_order',
                    'title' => 'New Order Received',
                    'message' => "You have received a new order #{$order->order_number}",
                    'meta' => ['order_id' => $order->id],
                    'status' => 'pending',
                ]);
            }
            
            // Clear cart
            $cart->update(['items' => [], 'subtotal' => 0, 'shipping' => 0, 'tax' => 0, 'total' => 0]);
            
            DB::commit();
            
            if (count($orders) === 1) {
                return redirect()->route('buyer.orders.show', $orders[0])
                    ->with('success', 'Order placed successfully!');
            } else {
                return redirect()->route('buyer.orders.index')
                    ->with('success', 'Your orders have been placed successfully!');
            }
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to place order: ' . $e->getMessage());
        }
    }
    
    public function cancelOrder(Request $request, Order $order)
    {
        if ($order->buyer_id !== Auth::id()) {
            abort(403);
        }
        
        if (!in_array($order->status, ['pending', 'paid'])) {
            return back()->with('error', 'Order cannot be cancelled at this stage.');
        }
        
        DB::beginTransaction();
        try {
            $oldStatus = $order->status;
            $order->update(['status' => 'cancelled']);
            
            // Restock items
            foreach ($order->items as $item) {
                $item->listing->increment('stock', $item->quantity);
            }
            
            // Refund if paid
            if ($oldStatus === 'paid') {
                $this->processRefund($order);
            }
            
            DB::commit();
            
            return back()->with('success', 'Order cancelled successfully.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to cancel order: ' . $e->getMessage());
        }
    }
    
    public function confirmDelivery(Request $request, Order $order)
    {
        if ($order->buyer_id !== Auth::id()) {
            abort(403);
        }
        
        if ($order->status !== 'delivered') {
            return back()->with('error', 'Order is not marked as delivered yet.');
        }
        
        $order->update(['status' => 'completed']);
        
        // Release escrow to vendor
        if ($order->escrow) {
            $order->escrow->update([
                'status' => 'released',
                'release_at' => now(),
            ]);
        }
        
        return back()->with('success', 'Delivery confirmed. Thank you for your purchase!');
    }
    
    private function processWalletPayment($user, $amount, $order)
    {
        $wallet = $user->buyerWallet;
        
        if (!$wallet || $wallet->available_balance < $amount) {
            throw new \Exception('Insufficient wallet balance.');
        }
        
        // Lock the amount
        $wallet->increment('locked_balance', $amount);
        
        // Create transaction record
        $user->walletTransactions()->create([
            'type' => 'payment',
            'amount' => -$amount,
            'balance_before' => $wallet->balance,
            'balance_after' => $wallet->balance,
            'reference' => $order->order_number,
            'status' => 'completed',
            'description' => 'Payment for order #' . $order->order_number,
            'meta' => [
                'order_id' => $order->id,
                'locked' => true, // Amount is locked in escrow
            ]
        ]);
        
        // Update order status
        $order->update(['status' => 'paid']);
        
        // Create payment record
        \App\Models\Payment::create([
            'order_id' => $order->id,
            'provider' => 'wallet',
            'provider_payment_id' => 'WLT-' . time(),
            'amount' => $amount,
            'status' => 'completed',
            'meta' => ['wallet_transaction' => true]
        ]);
    }
    
    private function processRefund($order)
    {
        // Refund to wallet if paid with wallet
        if ($order->payments()->where('provider', 'wallet')->exists()) {
            $wallet = $order->buyer->buyerWallet;
            $wallet->decrement('locked_balance', $order->total);
            
            $order->buyer->walletTransactions()->create([
                'type' => 'refund',
                'amount' => $order->total,
                'balance_before' => $wallet->balance,
                'balance_after' => $wallet->balance + $order->total,
                'reference' => 'REF-' . $order->order_number,
                'status' => 'completed',
                'description' => 'Refund for cancelled order #' . $order->order_number,
                'meta' => ['order_id' => $order->id]
            ]);
            
            $wallet->increment('balance', $order->total);
        }
    }
    
    private function calculateShipping($weight)
    {
        // Same as cart calculation
        if ($weight <= 1) return 5;
        if ($weight <= 5) return 10;
        if ($weight <= 10) return 15;
        if ($weight <= 20) return 25;
        return 50;
    }
}