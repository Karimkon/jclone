<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Escrow;
use App\Services\EscrowService;
use Illuminate\Http\Request;

class EscrowController extends Controller
{
    protected EscrowService $escrowService;

    public function __construct(EscrowService $escrowService)
    {
        $this->escrowService = $escrowService;
    }

    /**
     * Buyer confirms receipt of order (releases funds to vendor)
     */
    public function confirmReceipt(Request $request, Order $order)
    {
        // Ensure buyer owns this order
        if ($order->buyer_id !== auth()->id()) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            abort(403);
        }

        // Check order status
        if (!in_array($order->status, ['paid', 'shipped', 'delivered'])) {
            $message = 'Order must be paid and delivered before confirming receipt.';
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $message], 400);
            }
            return back()->with('error', $message);
        }

        // Check escrow exists and is held
        $escrow = $order->escrow;
        if (!$escrow || $escrow->status !== 'held') {
            $message = 'No funds to release for this order.';
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $message], 400);
            }
            return back()->with('error', $message);
        }

        // Release funds
        $success = $this->escrowService->releaseFunds($escrow, 'buyer_confirmed');

        if ($success) {
            $message = 'Order confirmed! Funds have been released to the vendor.';
            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => $message]);
            }
            return redirect()->route('buyer.orders.show', $order)->with('success', $message);
        }

        $message = 'Failed to process confirmation. Please try again.';
        if ($request->wantsJson()) {
            return response()->json(['success' => false, 'message' => $message], 500);
        }
        return back()->with('error', $message);
    }

    /**
     * Buyer requests refund (opens dispute)
     */
    public function requestRefund(Request $request, Order $order)
    {
        $request->validate([
            'reason' => 'required|string|max:1000',
            'evidence' => 'nullable|array',
            'evidence.*' => 'image|max:2048',
        ]);

        // Ensure buyer owns this order
        if ($order->buyer_id !== auth()->id()) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            abort(403);
        }

        // Check escrow status
        $escrow = $order->escrow;
        if (!$escrow || $escrow->status !== 'held') {
            $message = 'No refundable funds for this order.';
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $message], 400);
            }
            return back()->with('error', $message);
        }

        // Create dispute
        $dispute = \App\Models\Dispute::create([
            'order_id' => $order->id,
            'raised_by' => auth()->id(),
            'type' => 'refund_request',
            'reason' => $request->reason,
            'status' => 'open',
            'meta' => [
                'escrow_amount' => $escrow->amount,
                'created_at' => now()->toDateTimeString(),
            ]
        ]);

        // Handle evidence uploads
        if ($request->hasFile('evidence')) {
            $paths = [];
            foreach ($request->file('evidence') as $file) {
                $paths[] = $file->store('disputes/' . $dispute->id, 'public');
            }
            $dispute->update([
                'meta' => array_merge($dispute->meta, ['evidence_paths' => $paths])
            ]);
        }

        // Extend escrow hold while dispute is open
        $this->escrowService->extendHold($escrow, 14, 'dispute_opened');

        // Notify vendor
        \App\Models\NotificationQueue::create([
            'user_id' => $order->vendorProfile->user_id,
            'type' => 'dispute_opened',
            'title' => 'Refund Request Received',
            'message' => "A refund has been requested for Order #{$order->order_number}. Please review.",
            'meta' => [
                'order_id' => $order->id,
                'dispute_id' => $dispute->id,
            ],
            'status' => 'pending',
        ]);

        $message = 'Refund request submitted. Our team will review it shortly.';
        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $message, 'dispute_id' => $dispute->id]);
        }
        return redirect()->route('buyer.disputes.show', $dispute)->with('success', $message);
    }

    /**
     * Get escrow status for an order (AJAX)
     */
    public function getStatus(Order $order)
    {
        // Ensure user is part of this order
        $userId = auth()->id();
        $isVendor = $order->vendorProfile && $order->vendorProfile->user_id === $userId;
        $isBuyer = $order->buyer_id === $userId;

        if (!$isBuyer && !$isVendor) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $summary = $this->escrowService->getEscrowSummary($order);

        return response()->json([
            'success' => true,
            'escrow' => $summary,
            'is_buyer' => $isBuyer,
            'is_vendor' => $isVendor,
        ]);
    }

    // ============================================
    // ADMIN ACTIONS
    // ============================================

    /**
     * Admin: Force release escrow
     */
    public function adminRelease(Request $request, Escrow $escrow)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $success = $this->escrowService->releaseFunds($escrow, 'admin_release: ' . $request->reason);

        if ($success) {
            return back()->with('success', 'Escrow funds released successfully.');
        }

        return back()->with('error', 'Failed to release escrow funds.');
    }

    /**
     * Admin: Force refund escrow
     */
    public function adminRefund(Request $request, Escrow $escrow)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
            'amount' => 'nullable|numeric|min:0|max:' . $escrow->amount,
        ]);

        $success = $this->escrowService->refundToBuyer(
            $escrow,
            'admin_refund: ' . $request->reason,
            $request->amount
        );

        if ($success) {
            return back()->with('success', 'Refund processed successfully.');
        }

        return back()->with('error', 'Failed to process refund.');
    }

    /**
     * Admin: Extend escrow hold
     */
    public function adminExtend(Request $request, Escrow $escrow)
    {
        $request->validate([
            'days' => 'required|integer|min:1|max:30',
            'reason' => 'required|string|max:500',
        ]);

        $success = $this->escrowService->extendHold($escrow, $request->days, $request->reason);

        if ($success) {
            return back()->with('success', "Escrow hold extended by {$request->days} days.");
        }

        return back()->with('error', 'Failed to extend escrow hold.');
    }
}
