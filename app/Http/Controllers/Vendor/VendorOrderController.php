<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\VendorProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VendorOrderController extends Controller
{
    /**
     * Display vendor's orders
     */
    public function index(Request $request)
    {
        $vendor = Auth::user()->vendorProfile;
        if (!$vendor) {
            return redirect()->route('vendor.onboard.create');
        }

        $query = Order::with(['buyer', 'items.listing'])
            ->where('vendor_profile_id', $vendor->id);
        
        // Status filter
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        // Search filter
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('buyer', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
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
            'total' => Order::where('vendor_profile_id', $vendor->id)->count(),
            'pending' => Order::where('vendor_profile_id', $vendor->id)
                ->where('status', 'pending')->count(),
            'paid' => Order::where('vendor_profile_id', $vendor->id)
                ->where('status', 'paid')->count(),
            'processing' => Order::where('vendor_profile_id', $vendor->id)
                ->where('status', 'processing')->count(),
            'delivered' => Order::where('vendor_profile_id', $vendor->id)
                ->where('status', 'delivered')->count(),
            'revenue' => Order::where('vendor_profile_id', $vendor->id)
                ->where('status', 'delivered')->sum('total'),
        ];
        
        return view('vendor.orders.index', compact('orders', 'stats'));
    }

    /**
     * Show order details
     */
    public function show(Order $order)
    {
        // Verify ownership
        if ($order->vendor_profile_id !== Auth::user()->vendorProfile->id) {
            abort(403);
        }
        
        $order->load([
            'buyer', 
            'items.listing', 
            'items.listing.images',
            'payments',
            'escrow'
        ]);
        
        return view('vendor.orders.show', compact('order'));
    }

    /**
     * Update order status (vendor side)
     */
    public function updateStatus(Request $request, Order $order)
    {
        // Verify ownership
        if ($order->vendor_profile_id !== Auth::user()->vendorProfile->id) {
            abort(403);
        }

        $request->validate([
            'status' => 'required|in:pending,processing,shipped',
            'tracking_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
        ]);

        $oldStatus = $order->status;
        
        // Update order
        $order->update([
            'status' => $request->status,
            'meta' => array_merge($order->meta ?? [], [
                'vendor_notes' => $request->notes,
                'tracking_number' => $request->tracking_number,
                'status_updated_at' => now()->toDateTimeString(),
            ])
        ]);

        // Log status change
        \App\Models\AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'order_status_updated_vendor',
            'model' => 'Order',
            'model_id' => $order->id,
            'old_values' => ['status' => $oldStatus],
            'new_values' => [
                'status' => $request->status,
                'tracking_number' => $request->tracking_number,
            ],
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

        return back()->with('success', 'Order status updated successfully.');
    }

    /**
     * Mark order as shipped
     */
    public function markShipped(Request $request, Order $order)
    {
        // Verify ownership
        if ($order->vendor_profile_id !== Auth::user()->vendorProfile->id) {
            abort(403);
        }

        $request->validate([
            'tracking_number' => 'required|string|max:100',
            'carrier' => 'required|string|max:50',
            'estimated_delivery' => 'nullable|date',
        ]);

        $order->update([
            'status' => 'shipped',
            'meta' => array_merge($order->meta ?? [], [
                'shipping_info' => [
                    'tracking_number' => $request->tracking_number,
                    'carrier' => $request->carrier,
                    'estimated_delivery' => $request->estimated_delivery,
                    'shipped_at' => now()->toDateTimeString(),
                ]
            ])
        ]);

        // Create shipment record
        \App\Models\Shipment::create([
            'tracking_number' => $request->tracking_number,
            'order_id' => $order->id,
            'status' => 'shipped',
            'documents' => [
                'shipped_by_vendor' => Auth::id(),
                'shipped_at' => now()->toDateTimeString(),
            ]
        ]);

        // Notify buyer
        \App\Models\NotificationQueue::create([
            'user_id' => $order->buyer_id,
            'type' => 'order_shipped',
            'title' => 'Order Shipped',
            'message' => "Your order {$order->order_number} has been shipped. Tracking: {$request->tracking_number}",
            'meta' => [
                'order_id' => $order->id,
                'tracking_number' => $request->tracking_number,
                'carrier' => $request->carrier,
            ],
            'status' => 'pending',
        ]);

        return back()->with('success', 'Order marked as shipped successfully.');
    }

    /**
     * Cancel order (vendor request)
     */
    public function requestCancel(Request $request, Order $order)
    {
        // Verify ownership
        if ($order->vendor_profile_id !== Auth::user()->vendorProfile->id) {
            abort(403);
        }

        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        // Only allow cancellation for pending/paid orders
        if (!in_array($order->status, ['pending', 'paid'])) {
            return back()->with('error', 'Cannot cancel order in current status.');
        }

        $order->update([
            'meta' => array_merge($order->meta ?? [], [
                'cancel_request' => [
                    'requested_by' => Auth::id(),
                    'reason' => $request->reason,
                    'requested_at' => now()->toDateTimeString(),
                ]
            ])
        ]);

        // Notify admin
        \App\Models\NotificationQueue::create([
            'type' => 'admin_notification',
            'title' => 'Vendor Requested Order Cancellation',
            'message' => "Vendor requested to cancel order {$order->order_number}. Reason: {$request->reason}",
            'meta' => [
                'order_id' => $order->id,
                'vendor_id' => Auth::user()->vendorProfile->id,
                'reason' => $request->reason,
                'action_url' => route('admin.orders.show', $order),
            ],
            'status' => 'pending',
        ]);

        return back()->with('success', 'Cancellation request submitted. Admin will review.');
    }

    /**
     * Generate packing slip
     */
    public function packingSlip(Order $order)
    {
        // Verify ownership
        if ($order->vendor_profile_id !== Auth::user()->vendorProfile->id) {
            abort(403);
        }

        $order->load(['buyer', 'items.listing']);
        
        // For MVP, just show view
        // In production, generate PDF
        return view('vendor.orders.packing-slip', compact('order'));
    }

    /**
     * Export orders
     */
    public function export(Request $request)
    {
        $vendor = Auth::user()->vendorProfile;
        if (!$vendor) {
            return redirect()->route('vendor.onboard.create');
        }

        $query = Order::with(['buyer', 'items.listing'])
            ->where('vendor_profile_id', $vendor->id);
        
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

        // Transform for export
        $exportData = $orders->map(function($order) {
            return [
                'Order Number' => $order->order_number,
                'Date' => $order->created_at->format('Y-m-d H:i:s'),
                'Buyer Name' => $order->buyer->name ?? 'N/A',
                'Buyer Email' => $order->buyer->email ?? 'N/A',
                'Status' => $order->status,
                'Items Count' => $order->items->count(),
                'Subtotal' => $order->subtotal,
                'Shipping' => $order->shipping,
                'Total' => $order->total,
            ];
        });

        // For MVP, return JSON
        // In production, generate CSV/Excel
        return response()->json([
            'orders' => $exportData,
            'vendor' => $vendor->business_name,
            'exported_at' => now()->toDateTimeString(),
            'total_orders' => $orders->count(),
            'total_revenue' => $orders->sum('total'),
        ]);
    }

    /**
     * Get order statistics for dashboard
     */
    public function statistics(Request $request)
    {
        $vendor = Auth::user()->vendorProfile;
        if (!$vendor) {
            return response()->json(['error' => 'No vendor profile'], 403);
        }

        $period = $request->get('period', 'month'); // day, week, month, year
        
        switch ($period) {
            case 'day':
                $date = now()->subDay();
                break;
            case 'week':
                $date = now()->subWeek();
                break;
            case 'year':
                $date = now()->subYear();
                break;
            default: // month
                $date = now()->subMonth();
        }

        $stats = [
            'total_orders' => Order::where('vendor_profile_id', $vendor->id)
                ->where('created_at', '>=', $date)
                ->count(),
            'total_revenue' => Order::where('vendor_profile_id', $vendor->id)
                ->where('created_at', '>=', $date)
                ->where('status', 'delivered')
                ->sum('total'),
            'pending_orders' => Order::where('vendor_profile_id', $vendor->id)
                ->where('status', 'pending')
                ->where('created_at', '>=', $date)
                ->count(),
            'avg_order_value' => Order::where('vendor_profile_id', $vendor->id)
                ->where('created_at', '>=', $date)
                ->where('status', 'delivered')
                ->avg('total') ?? 0,
        ];

        // Monthly trend for chart
        $monthlyTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthDate = now()->subMonths($i);
            $month = $monthDate->format('M');
            
            $revenue = Order::where('vendor_profile_id', $vendor->id)
                ->whereYear('created_at', $monthDate->year)
                ->whereMonth('created_at', $monthDate->month)
                ->where('status', 'delivered')
                ->sum('total');
            
            $orders = Order::where('vendor_profile_id', $vendor->id)
                ->whereYear('created_at', $monthDate->year)
                ->whereMonth('created_at', $monthDate->month)
                ->count();
            
            $monthlyTrend[] = [
                'month' => $month,
                'revenue' => $revenue,
                'orders' => $orders,
            ];
        }

        return response()->json([
            'stats' => $stats,
            'monthly_trend' => $monthlyTrend,
            'period' => $period,
        ]);
    }
}