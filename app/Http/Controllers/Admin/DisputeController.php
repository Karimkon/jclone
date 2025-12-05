<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dispute;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DisputeController extends Controller
{
    /**
     * Display all disputes
     */
    public function index(Request $request)
    {
        $query = Dispute::with(['order.buyer', 'order.vendorProfile.user', 'raisedBy']);
        
        // Status filter
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        // Search filter
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('order', function($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%");
                })
                ->orWhereHas('raisedBy', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            });
        }
        
        $disputes = $query->orderBy('created_at', 'desc')->paginate(20);
        
        $stats = [
            'total' => Dispute::count(),
            'open' => Dispute::where('status', 'open')->count(),
            'in_review' => Dispute::where('status', 'in_review')->count(),
            'resolved' => Dispute::where('status', 'resolved')->count(),
            'escalated' => Dispute::where('status', 'escalated')->count(),
        ];
        
        return view('admin.disputes.index', compact('disputes', 'stats'));
    }

    /**
     * Show dispute details
     */
    public function show(Dispute $dispute)
    {
        $dispute->load([
            'order.buyer',
            'order.vendorProfile.user',
            'order.items.listing',
            'order.items.listing.images',
            'raisedBy',
            'order.escrow'
        ]);
        
        return view('admin.disputes.show', compact('dispute'));
    }

    /**
     * Update dispute status
     */
    public function updateStatus(Request $request, Dispute $dispute)
    {
        $request->validate([
            'status' => 'required|in:open,in_review,resolved,escalated',
            'resolution' => 'nullable|string|max:2000',
            'resolution_type' => 'nullable|in:refund_buyer,pay_vendor,partial_refund,other',
            'refund_amount' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $oldStatus = $dispute->status;
            $dispute->update([
                'status' => $request->status,
                'meta' => array_merge($dispute->meta ?? [], [
                    'resolution' => $request->resolution,
                    'resolution_type' => $request->resolution_type,
                    'refund_amount' => $request->refund_amount,
                    'resolved_by' => auth()->id(),
                    'resolved_at' => now()->toDateTimeString(),
                ])
            ]);

            // If resolved with refund, process it
            if ($request->status == 'resolved' && $request->refund_amount > 0) {
                $this->processDisputeRefund($dispute, $request);
            }

            // Log dispute update
            \App\Models\AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'dispute_updated',
                'model' => 'Dispute',
                'model_id' => $dispute->id,
                'old_values' => ['status' => $oldStatus],
                'new_values' => [
                    'status' => $request->status,
                    'resolution' => $request->resolution,
                    'refund_amount' => $request->refund_amount,
                ],
                'ip' => $request->ip(),
            ]);

            // Notify parties
            $this->notifyDisputeParties($dispute, $request->status, $request->resolution);

            DB::commit();

            return back()->with('success', 'Dispute status updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update dispute: ' . $e->getMessage());
        }
    }

    /**
     * Add comment to dispute
     */
    public function addComment(Request $request, Dispute $dispute)
    {
        $request->validate([
            'comment' => 'required|string|max:1000',
            'is_internal' => 'boolean',
        ]);

        $comments = $dispute->meta['comments'] ?? [];
        $comments[] = [
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name,
            'comment' => $request->comment,
            'is_internal' => $request->is_internal ?? false,
            'created_at' => now()->toDateTimeString(),
        ];

        $dispute->update([
            'meta' => array_merge($dispute->meta ?? [], ['comments' => $comments])
        ]);

        return back()->with('success', 'Comment added successfully.');
    }

    /**
     * Request more evidence
     */
    public function requestEvidence(Request $request, Dispute $dispute)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'deadline_days' => 'required|integer|min:1|max:30',
        ]);

        $dispute->update([
            'status' => 'in_review',
            'meta' => array_merge($dispute->meta ?? [], [
                'evidence_request' => [
                    'message' => $request->message,
                    'requested_by' => auth()->id(),
                    'requested_at' => now()->toDateTimeString(),
                    'deadline' => now()->addDays($request->deadline_days)->toDateTimeString(),
                ]
            ])
        ]);

        // Notify the user who raised the dispute
        \App\Models\NotificationQueue::create([
            'user_id' => $dispute->raised_by,
            'type' => 'dispute_evidence_request',
            'title' => 'Evidence Required for Dispute',
            'message' => $request->message . "\n\nPlease submit evidence within {$request->deadline_days} days.",
            'meta' => ['dispute_id' => $dispute->id, 'deadline' => $request->deadline_days],
            'status' => 'pending',
        ]);

        return back()->with('success', 'Evidence request sent successfully.');
    }

    /**
     * Process dispute refund
     */
    private function processDisputeRefund(Dispute $dispute, Request $request)
    {
        // Create refund payment record
        \App\Models\Payment::create([
            'order_id' => $dispute->order_id,
            'provider' => 'dispute_refund',
            'provider_payment_id' => 'dispute-refund-' . $dispute->id,
            'amount' => -$request->refund_amount, // Negative for refund
            'status' => 'completed',
            'meta' => [
                'dispute_id' => $dispute->id,
                'reason' => 'Dispute resolution: ' . ($request->resolution ?? 'No reason provided'),
                'processed_by' => auth()->id(),
                'processed_at' => now()->toDateTimeString(),
            ]
        ]);

        // Update escrow if exists
        if ($dispute->order->escrow) {
            $dispute->order->escrow()->update([
                'status' => 'refunded',
                'meta' => array_merge($dispute->order->escrow->meta ?? [], [
                    'refund_amount' => $request->refund_amount,
                    'refund_reason' => 'Dispute resolution',
                ])
            ]);
        }
    }

    /**
     * Notify dispute parties
     */
    private function notifyDisputeParties(Dispute $dispute, $status, $resolution = null)
    {
        $message = "Dispute for order {$dispute->order->order_number} has been {$status}.";
        if ($resolution) {
            $message .= "\n\nResolution: {$resolution}";
        }

        // Notify buyer
        \App\Models\NotificationQueue::create([
            'user_id' => $dispute->order->buyer_id,
            'type' => 'dispute_update',
            'title' => 'Dispute Update',
            'message' => $message,
            'meta' => ['dispute_id' => $dispute->id, 'status' => $status],
            'status' => 'pending',
        ]);

        // Notify vendor
        \App\Models\NotificationQueue::create([
            'user_id' => $dispute->order->vendorProfile->user_id,
            'type' => 'dispute_update',
            'title' => 'Dispute Update',
            'message' => $message,
            'meta' => ['dispute_id' => $dispute->id, 'status' => $status],
            'status' => 'pending',
        ]);
    }
}