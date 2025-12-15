<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\VendorProfile;
use App\Models\VendorPerformance;
use App\Models\NotificationQueue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        
        // Update stats to include delivery metrics
        $stats = [
            'total' => Order::where('vendor_profile_id', $vendor->id)->count(),
            'pending' => Order::where('vendor_profile_id', $vendor->id)
                ->where('status', 'pending')->count(),
            'paid' => Order::where('vendor_profile_id', $vendor->id)
                ->where('status', 'paid')->count(),
            'processing' => Order::where('vendor_profile_id', $vendor->id)
                ->where('status', 'processing')->count(),
            'shipped' => Order::where('vendor_profile_id', $vendor->id)
                ->where('status', 'shipped')->count(),
            'delivered' => Order::where('vendor_profile_id', $vendor->id)
                ->where('status', 'delivered')->count(),
            'cancelled' => Order::where('vendor_profile_id', $vendor->id)
                ->where('status', 'cancelled')->count(),
            'revenue' => Order::where('vendor_profile_id', $vendor->id)
                ->where('status', 'delivered')->sum('total'),
            'avg_delivery_time' => Order::where('vendor_profile_id', $vendor->id)
                ->where('status', 'delivered')
                ->whereNotNull('delivery_time_days')
                ->avg('delivery_time_days') ?? 0,
            'avg_delivery_score' => Order::where('vendor_profile_id', $vendor->id)
                ->where('status', 'delivered')
                ->whereNotNull('delivery_score')
                ->avg('delivery_score') ?? 0,
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
     * Update order status with timestamp tracking - SIMPLIFIED VERSION
     */
    public function updateStatus(Request $request, $id)
    {
        $order = Order::where('id', $id)
            ->where('vendor_profile_id', Auth::user()->vendorProfile->id)
            ->firstOrFail();

        $validated = $request->validate([
            'status' => 'required|in:pending,paid,processing,shipped,delivered,cancelled'
        ]);

        DB::beginTransaction();
        try {
            // Use the new Order model method
            $order->updateStatusWithTimestamps($validated['status']);

            // Create notification for buyer
            if (in_array($validated['status'], ['processing', 'shipped', 'delivered'])) {
                NotificationQueue::create([
                    'user_id' => $order->buyer_id,
                    'type' => 'order_status_update',
                    'title' => 'Order Status Update',
                    'message' => "Your order {$order->order_number} has been marked as " . strtoupper($validated['status']),
                    'meta' => [
                        'order_id' => $order->id,
                        'new_status' => $validated['status'],
                        'order_number' => $order->order_number,
                    ],
                    'status' => 'pending',
                ]);
            }

            DB::commit();

            return back()->with('success', 'Order status updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order status update failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to update status: ' . $e->getMessage());
        }
    }
    
    /**
     * Show vendor performance stats
     */
    public function performance()
    {
        $vendor = Auth::user()->vendorProfile;
        
        // Get or calculate performance
        $performance = VendorPerformance::where('vendor_profile_id', $vendor->id)->first();
        
        if (!$performance) {
            // Calculate on the fly
            $performance = VendorPerformance::updateForVendor($vendor->id);
        }
        
        // Get recent delivered orders for timeline
        $recentOrders = Order::where('vendor_profile_id', $vendor->id)
            ->where('status', 'delivered')
            ->orderBy('delivered_at', 'desc')
            ->limit(10)
            ->get(['order_number', 'delivery_time_days', 'delivery_score', 'delivered_at', 'processing_at', 'shipped_at']);
        
        // Delivery distribution with more categories
        $distribution = [
            'same_day' => Order::where('vendor_profile_id', $vendor->id)
                ->where('status', 'delivered')
                ->where('delivery_time_days', 0)
                ->count(),
            '1_2_days' => Order::where('vendor_profile_id', $vendor->id)
                ->where('status', 'delivered')
                ->whereBetween('delivery_time_days', [1, 2])
                ->count(),
            '3_5_days' => Order::where('vendor_profile_id', $vendor->id)
                ->where('status', 'delivered')
                ->whereBetween('delivery_time_days', [3, 5])
                ->count(),
            '6_10_days' => Order::where('vendor_profile_id', $vendor->id)
                ->where('status', 'delivered')
                ->whereBetween('delivery_time_days', [6, 10])
                ->count(),
            'over_10_days' => Order::where('vendor_profile_id', $vendor->id)
                ->where('status', 'delivered')
                ->where('delivery_time_days', '>', 10)
                ->count(),
        ];
        
        // Processing time distribution
        $processingDistribution = [
            'same_day' => Order::where('vendor_profile_id', $vendor->id)
                ->where('status', 'delivered')
                ->where('processing_time_hours', '<=', 24)
                ->count(),
            '1_2_days' => Order::where('vendor_profile_id', $vendor->id)
                ->where('status', 'delivered')
                ->whereBetween('processing_time_hours', [25, 48])
                ->count(),
            '3_5_days' => Order::where('vendor_profile_id', $vendor->id)
                ->where('status', 'delivered')
                ->whereBetween('processing_time_hours', [49, 120])
                ->count(),
            'over_5_days' => Order::where('vendor_profile_id', $vendor->id)
                ->where('status', 'delivered')
                ->where('processing_time_hours', '>', 120)
                ->count(),
        ];
        
        return view('vendor.performance.index', compact(
            'performance', 
            'recentOrders', 
            'distribution',
            'processingDistribution'
        ));
    }

    /**
     * Mark order as shipped - UPDATED
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

        DB::beginTransaction();
        try {
            // Use the new method
            $order->updateStatusWithTimestamps('shipped');
            
            // Update meta with shipping info
            $order->update([
                'meta' => array_merge($order->meta ?? [], [
                    'shipping_info' => [
                        'tracking_number' => $request->tracking_number,
                        'carrier' => $request->carrier,
                        'estimated_delivery' => $request->estimated_delivery,
                        'shipped_at' => now()->toDateTimeString(),
                        'shipped_by' => Auth::user()->name,
                        'shipped_by_vendor_id' => Auth::user()->vendorProfile->id,
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
                    'carrier' => $request->carrier,
                    'estimated_delivery' => $request->estimated_delivery,
                ]
            ]);

            // Notify buyer
            NotificationQueue::create([
                'user_id' => $order->buyer_id,
                'type' => 'order_shipped',
                'title' => 'Order Shipped',
                'message' => "Your order {$order->order_number} has been shipped via {$request->carrier}. Tracking: {$request->tracking_number}",
                'meta' => [
                    'order_id' => $order->id,
                    'tracking_number' => $request->tracking_number,
                    'carrier' => $request->carrier,
                    'estimated_delivery' => $request->estimated_delivery,
                ],
                'status' => 'pending',
            ]);

            DB::commit();

            return back()->with('success', 'Order marked as shipped successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Mark shipped failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to mark as shipped: ' . $e->getMessage());
        }
    }

    /**
     * Cancel order (vendor request) - UPDATED
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
        if (!in_array($order->status, ['pending', 'paid', 'confirmed'])) {
            return back()->with('error', 'Cannot cancel order in current status: ' . $order->status);
        }

        DB::beginTransaction();
        try {
            // Use the new method
            $order->updateStatusWithTimestamps('cancelled');
            
            // Add cancellation reason to meta
            $order->update([
                'meta' => array_merge($order->meta ?? [], [
                    'cancel_request' => [
                        'requested_by' => 'vendor',
                        'requested_by_vendor_id' => Auth::user()->vendorProfile->id,
                        'reason' => $request->reason,
                        'requested_at' => now()->toDateTimeString(),
                        'approved_at' => now()->toDateTimeString(),
                        'approved_by' => Auth::user()->name,
                    ]
                ])
            ]);

            // Restore stock for order items
            foreach ($order->items as $item) {
                if ($item->listing) {
                    $item->listing->increment('stock', $item->quantity);
                }
            }

            // Notify buyer
            NotificationQueue::create([
                'user_id' => $order->buyer_id,
                'type' => 'order_cancelled',
                'title' => 'Order Cancelled',
                'message' => "Order {$order->order_number} has been cancelled by the vendor. Reason: {$request->reason}",
                'meta' => [
                    'order_id' => $order->id,
                    'cancelled_by' => 'vendor',
                    'reason' => $request->reason,
                ],
                'status' => 'pending',
            ]);

            // Notify admin
            NotificationQueue::create([
                'type' => 'admin_notification',
                'title' => 'Order Cancelled by Vendor',
                'message' => "Order {$order->order_number} was cancelled by vendor. Reason: {$request->reason}",
                'meta' => [
                    'order_id' => $order->id,
                    'vendor_id' => Auth::user()->vendorProfile->id,
                    'reason' => $request->reason,
                    'action_url' => route('admin.orders.show', $order),
                ],
                'status' => 'pending',
            ]);

            DB::commit();

            return back()->with('success', 'Order cancelled successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Request cancel failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to cancel order: ' . $e->getMessage());
        }
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
     * Export orders - ADD DELIVERY METRICS
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

        // Transform for export - ADD DELIVERY FIELDS
        $exportData = $orders->map(function($order) {
            return [
                'Order Number' => $order->order_number,
                'Date' => $order->created_at->format('Y-m-d H:i:s'),
                'Buyer Name' => $order->buyer->name ?? 'N/A',
                'Buyer Email' => $order->buyer->email ?? 'N/A',
                'Status' => $order->status,
                'Processing Date' => $order->processing_at?->format('Y-m-d H:i:s') ?? 'N/A',
                'Shipping Date' => $order->shipped_at?->format('Y-m-d H:i:s') ?? 'N/A',
                'Delivery Date' => $order->delivered_at?->format('Y-m-d H:i:s') ?? 'N/A',
                'Delivery Time (Days)' => $order->delivery_time_days ?? 'N/A',
                'Processing Time (Hours)' => $order->processing_time_hours ?? 'N/A',
                'Delivery Score' => $order->delivery_score ?? 'N/A',
                'Items Count' => $order->items->count(),
                'Subtotal' => $order->subtotal,
                'Shipping' => $order->shipping,
                'Total' => $order->total,
            ];
        });

        return response()->json([
            'orders' => $exportData,
            'vendor' => $vendor->business_name,
            'exported_at' => now()->toDateTimeString(),
            'total_orders' => $orders->count(),
            'total_revenue' => $orders->sum('total'),
        ]);
    }

    /**
     * Get order statistics for dashboard - ADD DELIVERY METRICS
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
            // DELIVERY METRICS
            'avg_delivery_time' => Order::where('vendor_profile_id', $vendor->id)
                ->where('status', 'delivered')
                ->where('delivered_at', '>=', $date)
                ->whereNotNull('delivery_time_days')
                ->avg('delivery_time_days') ?? 0,
            'avg_delivery_score' => Order::where('vendor_profile_id', $vendor->id)
                ->where('status', 'delivered')
                ->where('delivered_at', '>=', $date)
                ->whereNotNull('delivery_score')
                ->avg('delivery_score') ?? 0,
            'on_time_delivery_rate' => $this->calculateOnTimeRate($vendor->id, $date),
        ];

        // Monthly trend for chart - ADD DELIVERY METRICS
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
            
            // Delivery metrics
            $deliveredOrders = Order::where('vendor_profile_id', $vendor->id)
                ->whereYear('delivered_at', $monthDate->year)
                ->whereMonth('delivered_at', $monthDate->month)
                ->where('status', 'delivered')
                ->whereNotNull('delivery_time_days')
                ->get();
            
            $avgDeliveryTime = $deliveredOrders->avg('delivery_time_days') ?? 0;
            $avgDeliveryScore = $deliveredOrders->avg('delivery_score') ?? 0;
            
            $monthlyTrend[] = [
                'month' => $month,
                'revenue' => $revenue,
                'orders' => $orders,
                'avg_delivery_time' => round($avgDeliveryTime, 1),
                'avg_delivery_score' => round($avgDeliveryScore, 1),
            ];
        }

        return response()->json([
            'stats' => $stats,
            'monthly_trend' => $monthlyTrend,
            'period' => $period,
        ]);
    }
    
    /**
     * Calculate on-time delivery rate
     */
    private function calculateOnTimeRate($vendorId, $date)
    {
        $deliveredOrders = Order::where('vendor_profile_id', $vendorId)
            ->where('status', 'delivered')
            ->where('delivered_at', '>=', $date)
            ->whereNotNull('delivery_time_days')
            ->get();
        
        if ($deliveredOrders->count() === 0) {
            return 0;
        }
        
        $onTimeDeliveries = $deliveredOrders->where('delivery_time_days', '<=', 7)->count();
        return round(($onTimeDeliveries / $deliveredOrders->count()) * 100, 1);
    }
}