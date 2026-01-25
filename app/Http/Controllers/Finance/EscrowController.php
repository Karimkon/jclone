<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Escrow;
use App\Models\AuditLog;
use App\Models\NotificationQueue;
use App\Services\EscrowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EscrowController extends Controller
{
    protected $escrowService;

    public function __construct(EscrowService $escrowService)
    {
        $this->escrowService = $escrowService;
    }

    /**
     * Display all escrows
     */
    public function index(Request $request)
    {
        $query = Escrow::with(['order.buyer', 'order.vendor.user']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search by order number
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('order', function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%");
            });
        }

        $escrows = $query->orderBy('created_at', 'desc')->paginate(25);

        // Escrow statistics
        $stats = [
            'total_held' => Escrow::where('status', 'held')->sum('amount'),
            'held_count' => Escrow::where('status', 'held')->count(),
            'total_released' => Escrow::where('status', 'released')->sum('amount'),
            'released_count' => Escrow::where('status', 'released')->count(),
            'total_refunded' => Escrow::where('status', 'refunded')->sum('amount'),
            'refunded_count' => Escrow::where('status', 'refunded')->count(),
        ];

        return view('finance.escrows.index', compact('escrows', 'stats'));
    }

    /**
     * Show escrow details
     */
    public function show(Escrow $escrow)
    {
        $escrow->load(['order.buyer', 'order.vendor.user', 'order.items', 'order.payment']);

        return view('finance.escrows.show', compact('escrow'));
    }

    /**
     * Release escrow funds to vendor
     */
    public function release(Request $request, Escrow $escrow)
    {
        $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        if ($escrow->status !== 'held') {
            return back()->with('error', 'Escrow is not in held status.');
        }

        DB::beginTransaction();
        try {
            // Use the escrow service to release funds
            $this->escrowService->releaseFunds($escrow->order);

            // Log the action
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'escrow_released',
                'model' => 'Escrow',
                'model_id' => $escrow->id,
                'old_values' => ['status' => 'held'],
                'new_values' => ['status' => 'released', 'notes' => $request->notes],
                'ip' => $request->ip(),
            ]);

            // Notify vendor
            NotificationQueue::create([
                'user_id' => $escrow->order->vendor->user_id,
                'type' => 'escrow_released',
                'title' => 'Funds Released',
                'message' => "Funds for order #{$escrow->order->order_number} have been released to your account.",
                'meta' => [
                    'escrow_id' => $escrow->id,
                    'order_id' => $escrow->order_id,
                    'amount' => $escrow->amount,
                ],
                'status' => 'pending',
            ]);

            DB::commit();

            return back()->with('success', 'Escrow funds released successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to release escrow: ' . $e->getMessage());
        }
    }

    /**
     * Refund escrow to buyer
     */
    public function refund(Request $request, Escrow $escrow)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        if ($escrow->status !== 'held') {
            return back()->with('error', 'Escrow is not in held status.');
        }

        DB::beginTransaction();
        try {
            // Use the escrow service to refund
            $this->escrowService->refundToBuyer($escrow->order, $request->reason);

            // Log the action
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'escrow_refunded',
                'model' => 'Escrow',
                'model_id' => $escrow->id,
                'old_values' => ['status' => 'held'],
                'new_values' => ['status' => 'refunded', 'reason' => $request->reason],
                'ip' => $request->ip(),
            ]);

            // Notify buyer
            NotificationQueue::create([
                'user_id' => $escrow->order->buyer_id,
                'type' => 'escrow_refunded',
                'title' => 'Refund Processed',
                'message' => "Your payment for order #{$escrow->order->order_number} has been refunded. Reason: {$request->reason}",
                'meta' => [
                    'escrow_id' => $escrow->id,
                    'order_id' => $escrow->order_id,
                    'amount' => $escrow->amount,
                    'reason' => $request->reason,
                ],
                'status' => 'pending',
            ]);

            DB::commit();

            return back()->with('success', 'Escrow refunded to buyer successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to refund escrow: ' . $e->getMessage());
        }
    }

    /**
     * Extend escrow hold period
     */
    public function extend(Request $request, Escrow $escrow)
    {
        $request->validate([
            'days' => 'required|integer|min:1|max:30',
            'reason' => 'nullable|string|max:500',
        ]);

        if ($escrow->status !== 'held') {
            return back()->with('error', 'Escrow is not in held status.');
        }

        DB::beginTransaction();
        try {
            $oldReleaseAt = $escrow->release_at;
            $escrow->update([
                'release_at' => $escrow->release_at->addDays($request->days),
                'meta' => array_merge($escrow->meta ?? [], [
                    'extended_by' => auth()->id(),
                    'extended_at' => now()->toDateTimeString(),
                    'extension_days' => $request->days,
                    'extension_reason' => $request->reason,
                ]),
            ]);

            // Log the action
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'escrow_extended',
                'model' => 'Escrow',
                'model_id' => $escrow->id,
                'old_values' => ['release_at' => $oldReleaseAt],
                'new_values' => ['release_at' => $escrow->release_at, 'days' => $request->days],
                'ip' => $request->ip(),
            ]);

            DB::commit();

            return back()->with('success', "Escrow hold extended by {$request->days} day(s).");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to extend escrow: ' . $e->getMessage());
        }
    }
}
