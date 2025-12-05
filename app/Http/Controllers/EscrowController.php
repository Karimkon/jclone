<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Escrow;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class EscrowController extends Controller
{
    /**
     * Release escrow funds to vendor (admin action)
     */
    public function release(Request $request, Escrow $escrow)
    {
        // Only admin can release escrow
        if (!auth()->user()->hasRole('admin')) {
            abort(403);
        }

        $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $order = $escrow->order;
            
            if (!$order || !$order->vendorProfile) {
                throw new \Exception('Order or vendor not found');
            }

            $vendor = $order->vendorProfile;
            $balance = $vendor->balanceRecord;
            
            // Calculate commission (8%)
            $commission = $escrow->amount * 0.08;
            $netAmount = $escrow->amount - $commission;
            
            // Release from escrow to vendor balance
            $balance->releasePending($escrow->amount, $order->id, $commission);
            
            // Update escrow status
            $escrow->update([
                'status' => 'released',
                'released_at' => now(),
                'released_by' => auth()->id(),
                'meta' => array_merge($escrow->meta ?? [], [
                    'release_notes' => $request->notes,
                    'commission' => $commission,
                    'net_amount' => $netAmount,
                ])
            ]);
            
            // Update order status
            $order->update(['status' => 'completed']);

            // Log admin action
            \App\Models\AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'escrow_released',
                'model' => 'Escrow',
                'model_id' => $escrow->id,
                'new_values' => [
                    'status' => 'released',
                    'amount' => $escrow->amount,
                    'commission' => $commission,
                    'vendor_received' => $netAmount,
                ],
                'ip' => $request->ip(),
            ]);

            // Notify vendor
            \App\Models\NotificationQueue::create([
                'user_id' => $vendor->user_id,
                'type' => 'email',
                'title' => 'Escrow Funds Released',
                'message' => "Escrow funds of \${$escrow->amount} for order #{$order->order_number} have been released to your balance. Commission: \${$commission}, Net: \${$netAmount}",
                'meta' => [
                    'order_id' => $order->id,
                    'amount' => $escrow->amount,
                    'commission' => $commission,
                    'net_amount' => $netAmount,
                ],
                'status' => 'pending',
            ]);

            DB::commit();

            return back()->with('success', "Escrow released successfully. Vendor received \${$netAmount}.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to release escrow: ' . $e->getMessage());
        }
    }

    /**
     * Refund escrow funds to buyer (admin action)
     */
    public function refund(Request $request, Escrow $escrow)
    {
        // Only admin can refund escrow
        if (!auth()->user()->hasRole('admin')) {
            abort(403);
        }

        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $order = $escrow->order;
            
            // Update escrow status
            $escrow->update([
                'status' => 'refunded',
                'refunded_at' => now(),
                'refunded_by' => auth()->id(),
                'meta' => array_merge($escrow->meta ?? [], [
                    'refund_reason' => $request->reason,
                ])
            ]);
            
            // Update order status
            $order->update(['status' => 'refunded']);

            // Log admin action
            \App\Models\AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'escrow_refunded',
                'model' => 'Escrow',
                'model_id' => $escrow->id,
                'new_values' => [
                    'status' => 'refunded',
                    'reason' => $request->reason,
                ],
                'ip' => $request->ip(),
            ]);

            // Notify buyer
            \App\Models\NotificationQueue::create([
                'user_id' => $order->buyer_id,
                'type' => 'email',
                'title' => 'Order Refunded',
                'message' => "Your order #{$order->order_number} has been refunded. Reason: {$request->reason}",
                'meta' => [
                    'order_id' => $order->id,
                    'amount' => $escrow->amount,
                    'reason' => $request->reason,
                ],
                'status' => 'pending',
            ]);

            DB::commit();

            return back()->with('success', 'Escrow refunded successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to refund escrow: ' . $e->getMessage());
        }
    }

    /**
     * Get pending escrows for admin
     */
    public function pending()
    {
        $pendingEscrows = Escrow::with(['order.vendor.user', 'order.buyer'])
            ->where('status', 'held')
            ->where('release_at', '<=', now()) // Ready for release
            ->orderBy('release_at')
            ->paginate(20);

        return view('admin.escrows.pending', compact('pendingEscrows'));
    }
}