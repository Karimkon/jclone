<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\VendorWithdrawal;
use App\Models\VendorProfile;
use App\Models\AuditLog;
use App\Models\NotificationQueue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayoutController extends Controller
{
    /**
     * Display pending withdrawals (for quick processing)
     */
    public function pending()
    {
        $withdrawals = VendorWithdrawal::with(['vendor.user'])
            ->whereIn('status', [VendorWithdrawal::STATUS_PENDING, VendorWithdrawal::STATUS_PROCESSING])
            ->orderBy('created_at', 'asc')
            ->paginate(20);

        $stats = [
            'pending' => VendorWithdrawal::where('status', VendorWithdrawal::STATUS_PENDING)->count(),
            'processing' => VendorWithdrawal::where('status', VendorWithdrawal::STATUS_PROCESSING)->count(),
            'pending_amount' => VendorWithdrawal::where('status', VendorWithdrawal::STATUS_PENDING)->sum('amount'),
            'processing_amount' => VendorWithdrawal::where('status', VendorWithdrawal::STATUS_PROCESSING)->sum('amount'),
        ];

        return view('finance.payouts.pending', compact('withdrawals', 'stats'));
    }

    /**
     * Display all payouts/withdrawals
     */
    public function index(Request $request)
    {
        $query = VendorWithdrawal::with(['vendor.user']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by method
        if ($request->filled('method')) {
            $query->where('method', $request->method);
        }

        // Filter by vendor
        if ($request->filled('vendor_id')) {
            $query->where('vendor_profile_id', $request->vendor_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('transaction_id', 'like', "%{$search}%")
                  ->orWhereHas('vendor.user', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $withdrawals = $query->orderBy('created_at', 'desc')->paginate(25);

        // Statistics
        $stats = [
            'total' => VendorWithdrawal::count(),
            'pending' => VendorWithdrawal::where('status', VendorWithdrawal::STATUS_PENDING)->count(),
            'processing' => VendorWithdrawal::where('status', VendorWithdrawal::STATUS_PROCESSING)->count(),
            'completed' => VendorWithdrawal::where('status', VendorWithdrawal::STATUS_COMPLETED)->count(),
            'rejected' => VendorWithdrawal::where('status', VendorWithdrawal::STATUS_REJECTED)->count(),
            'total_paid' => VendorWithdrawal::where('status', VendorWithdrawal::STATUS_COMPLETED)->sum('net_amount'),
        ];

        // Get vendors for filter
        $vendors = VendorProfile::with('user')->where('vetting_status', 'approved')->get();

        return view('finance.payouts.index', compact('withdrawals', 'stats', 'vendors'));
    }

    /**
     * Show withdrawal details
     */
    public function show(VendorWithdrawal $withdrawal)
    {
        $withdrawal->load(['vendor.user', 'vendor.balanceRecord']);

        return view('finance.payouts.show', compact('withdrawal'));
    }

    /**
     * Approve a withdrawal (mark as processing)
     */
    public function approve(Request $request, VendorWithdrawal $withdrawal)
    {
        $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        if ($withdrawal->status !== VendorWithdrawal::STATUS_PENDING) {
            return back()->with('error', 'Withdrawal is not in pending status.');
        }

        DB::beginTransaction();
        try {
            $withdrawal->markAsProcessing();

            // Add notes if provided
            if ($request->filled('notes')) {
                $withdrawal->update([
                    'meta' => array_merge($withdrawal->meta ?? [], [
                        'admin_notes' => $request->notes,
                        'approved_by' => auth()->id(),
                        'approved_at' => now()->toDateTimeString(),
                    ])
                ]);
            }

            // Log the approval
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'withdrawal_approved',
                'model' => 'VendorWithdrawal',
                'model_id' => $withdrawal->id,
                'old_values' => ['status' => VendorWithdrawal::STATUS_PENDING],
                'new_values' => ['status' => VendorWithdrawal::STATUS_PROCESSING],
                'ip' => $request->ip(),
            ]);

            // Notify vendor
            NotificationQueue::create([
                'user_id' => $withdrawal->vendor->user_id,
                'type' => 'withdrawal_processing',
                'title' => 'Withdrawal Approved',
                'message' => "Your withdrawal request of \${$withdrawal->amount} has been approved and is now being processed.",
                'meta' => [
                    'withdrawal_id' => $withdrawal->id,
                    'amount' => $withdrawal->amount,
                ],
                'status' => 'pending',
            ]);

            DB::commit();

            return back()->with('success', 'Withdrawal approved and marked as processing.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to approve withdrawal: ' . $e->getMessage());
        }
    }

    /**
     * Complete a withdrawal
     */
    public function complete(Request $request, VendorWithdrawal $withdrawal)
    {
        $request->validate([
            'transaction_id' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        if (!in_array($withdrawal->status, [VendorWithdrawal::STATUS_PROCESSING, VendorWithdrawal::STATUS_PENDING])) {
            return back()->with('error', 'Withdrawal cannot be completed in current status.');
        }

        DB::beginTransaction();
        try {
            $withdrawal->markAsCompleted($request->transaction_id);

            // Add notes if provided
            if ($request->filled('notes')) {
                $withdrawal->update([
                    'meta' => array_merge($withdrawal->meta ?? [], [
                        'completion_notes' => $request->notes,
                        'completed_by' => auth()->id(),
                    ])
                ]);
            }

            // Log the completion
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'withdrawal_completed',
                'model' => 'VendorWithdrawal',
                'model_id' => $withdrawal->id,
                'old_values' => ['status' => $withdrawal->status],
                'new_values' => ['status' => VendorWithdrawal::STATUS_COMPLETED],
                'ip' => $request->ip(),
            ]);

            // Notify vendor
            NotificationQueue::create([
                'user_id' => $withdrawal->vendor->user_id,
                'type' => 'withdrawal_completed',
                'title' => 'Withdrawal Completed',
                'message' => "Your withdrawal of \${$withdrawal->net_amount} has been completed and funds have been transferred.",
                'meta' => [
                    'withdrawal_id' => $withdrawal->id,
                    'amount' => $withdrawal->net_amount,
                    'transaction_id' => $request->transaction_id,
                ],
                'status' => 'pending',
            ]);

            DB::commit();

            return back()->with('success', 'Withdrawal marked as completed.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to complete withdrawal: ' . $e->getMessage());
        }
    }

    /**
     * Reject a withdrawal
     */
    public function reject(Request $request, VendorWithdrawal $withdrawal)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        if (!in_array($withdrawal->status, [VendorWithdrawal::STATUS_PENDING, VendorWithdrawal::STATUS_PROCESSING])) {
            return back()->with('error', 'Withdrawal cannot be rejected in current status.');
        }

        DB::beginTransaction();
        try {
            $withdrawal->markAsRejected($request->reason);

            // Refund to vendor balance
            $withdrawal->vendor->balanceRecord->credit(
                $withdrawal->amount,
                'Withdrawal rejection #' . $withdrawal->id,
                'WDR-REJECT-' . $withdrawal->id,
                [
                    'withdrawal_id' => $withdrawal->id,
                    'reason' => $request->reason,
                ]
            );

            // Log the rejection
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'withdrawal_rejected',
                'model' => 'VendorWithdrawal',
                'model_id' => $withdrawal->id,
                'old_values' => ['status' => $withdrawal->status],
                'new_values' => ['status' => VendorWithdrawal::STATUS_REJECTED, 'reason' => $request->reason],
                'ip' => $request->ip(),
            ]);

            // Notify vendor
            NotificationQueue::create([
                'user_id' => $withdrawal->vendor->user_id,
                'type' => 'withdrawal_rejected',
                'title' => 'Withdrawal Rejected',
                'message' => "Your withdrawal request has been rejected. Reason: {$request->reason}. Amount has been refunded to your balance.",
                'meta' => [
                    'withdrawal_id' => $withdrawal->id,
                    'reason' => $request->reason,
                ],
                'status' => 'pending',
            ]);

            DB::commit();

            return back()->with('success', 'Withdrawal rejected and amount refunded to vendor.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to reject withdrawal: ' . $e->getMessage());
        }
    }
}
