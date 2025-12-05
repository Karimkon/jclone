<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\VendorProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminOrderController extends Controller
{
    /**
     * Display all orders
     */
    public function index(Request $request)
    {
        $query = Order::with(['buyer', 'vendorProfile.user', 'items.listing']);
        
        // Search filter
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('buyer', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                  })
                  ->orWhereHas('vendorProfile.user', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }
        
        // Status filter
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        // Date filter
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $orders = $query->orderBy('created_at', 'desc')->paginate(20);
        
        $stats = [
            'total' => Order::count(),
            'pending' => Order::where('status', 'pending')->count(),
            'paid' => Order::where('status', 'paid')->count(),
            'processing' => Order::where('status', 'processing')->count(),
            'delivered' => Order::where('status', 'delivered')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
            'revenue' => Order::where('status', 'delivered')->sum('total'),
        ];
        
        return view('admin.orders.index', compact('orders', 'stats'));
    }

    /**
     * Show order details
     */
    public function show(Order $order)
    {
        $order->load([
            'buyer', 
            'vendorProfile.user', 
            'items.listing', 
            'items.listing.images',
            'payments',
            'escrow'
        ]);
        
        return view('admin.orders.show', compact('order'));
    }

    /**
     * Update order status
     */
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,paid,processing,shipped,delivered,cancelled,refunded',
            'notes' => 'nullable|string|max:1000',
        ]);

        $oldStatus = $order->status;
        $order->update(['status' => $request->status]);

        // Log status change
        \App\Models\AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'order_status_updated',
            'model' => 'Order',
            'model_id' => $order->id,
            'old_values' => ['status' => $oldStatus],
            'new_values' => ['status' => $request->status, 'notes' => $request->notes],
            'ip' => $request->ip(),
        ]);

        // Notify buyer
        \App\Models\NotificationQueue::create([
            'user_id' => $order->buyer_id,
            'type' => 'order_update',
            'title' => 'Order Status Updated',
            'message' => "Your order {$order->order_number} status changed to " . ucfirst($request->status),
            'meta' => ['order_id' => $order->id, 'status' => $request->status],
            'status' => 'pending',
        ]);

        // Notify vendor if status affects them
        if (in_array($request->status, ['processing', 'shipped', 'delivered', 'cancelled'])) {
            \App\Models\NotificationQueue::create([
                'user_id' => $order->vendorProfile->user_id,
                'type' => 'order_update',
                'title' => 'Order Status Updated',
                'message' => "Order {$order->order_number} status changed to " . ucfirst($request->status),
                'meta' => ['order_id' => $order->id, 'status' => $request->status],
                'status' => 'pending',
            ]);
        }

        return back()->with('success', 'Order status updated successfully.');
    }

    /**
     * Generate order invoice
     */
    public function invoice(Order $order)
    {
        $order->load(['buyer', 'vendorProfile.user', 'items.listing']);
        
        // In a real app, you would generate a PDF
        // For now, just show a view
        return view('admin.orders.invoice', compact('order'));
    }

    /**
     * Refund order
     */
    public function refund(Request $request, Order $order)
    {
        $request->validate([
            'reason' => 'required|string|max:1000',
            'amount' => 'required|numeric|min:0|max:' . $order->total,
        ]);

        // Check if order can be refunded
        if (!in_array($order->status, ['paid', 'delivered'])) {
            return back()->with('error', 'Order cannot be refunded in its current status.');
        }

        DB::beginTransaction();
        try {
            // Update order status
            $order->update(['status' => 'refunded']);
            
            // Create refund record
            \App\Models\Payment::create([
                'order_id' => $order->id,
                'provider' => 'manual_refund',
                'provider_payment_id' => 'refund-' . time(),
                'amount' => -$request->amount, // Negative for refund
                'status' => 'completed',
                'meta' => [
                    'reason' => $request->reason,
                    'refunded_by' => auth()->id(),
                    'refunded_at' => now()->toDateTimeString(),
                ]
            ]);

            // Log refund
            \App\Models\AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'order_refunded',
                'model' => 'Order',
                'model_id' => $order->id,
                'new_values' => [
                    'status' => 'refunded',
                    'refund_amount' => $request->amount,
                    'refund_reason' => $request->reason,
                ],
                'ip' => $request->ip(),
            ]);

            DB::commit();

            return back()->with('success', 'Order refunded successfully.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to process refund: ' . $e->getMessage());
        }
    }

    /**
     * Export orders
     */
    public function export(Request $request)
    {
        $query = Order::with(['buyer', 'vendorProfile.user']);
        
        // Apply filters
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        // In a real app, you would generate CSV/Excel
        // For MVP, return JSON
        return response()->json([
            'orders' => $orders,
            'exported_at' => now()->toDateTimeString(),
            'total' => $orders->count(),
            'total_revenue' => $orders->sum('total'),
        ]);
    }
}