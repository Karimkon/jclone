<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VendorWithdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WithdrawalController extends Controller
{
    /**
     * Show pending withdrawals
     */
    public function pending()
    {
        $withdrawals = VendorWithdrawal::with(['vendor.user'])
            ->whereIn('status', [VendorWithdrawal::STATUS_PENDING, VendorWithdrawal::STATUS_PROCESSING])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        $stats = [
            'total' => VendorWithdrawal::count(),
            'pending' => VendorWithdrawal::where('status', VendorWithdrawal::STATUS_PENDING)->count(),
            'processing' => VendorWithdrawal::where('status', VendorWithdrawal::STATUS_PROCESSING)->count(),
            'completed' => VendorWithdrawal::where('status', VendorWithdrawal::STATUS_COMPLETED)->count(),
            'rejected' => VendorWithdrawal::where('status', VendorWithdrawal::STATUS_REJECTED)->count(),
            'total_amount' => VendorWithdrawal::whereIn('status', [VendorWithdrawal::STATUS_PENDING, VendorWithdrawal::STATUS_PROCESSING])->sum('amount'),
        ];
            
        return view('admin.withdrawals.pending', compact('withdrawals', 'stats'));
    }
    
    /**
     * Show all withdrawals
     */
    public function index()
    {
        $withdrawals = VendorWithdrawal::with(['vendor.user'])
            ->orderBy('created_at', 'desc')
            ->paginate(25);
            
        $stats = [
            'total' => VendorWithdrawal::count(),
            'pending' => VendorWithdrawal::where('status', VendorWithdrawal::STATUS_PENDING)->count(),
            'processing' => VendorWithdrawal::where('status', VendorWithdrawal::STATUS_PROCESSING)->count(),
            'completed' => VendorWithdrawal::where('status', VendorWithdrawal::STATUS_COMPLETED)->count(),
            'rejected' => VendorWithdrawal::where('status', VendorWithdrawal::STATUS_REJECTED)->count(),
            'total_amount' => VendorWithdrawal::sum('amount'),
        ];
            
        return view('admin.withdrawals.index', compact('withdrawals', 'stats'));
    }
    
    /**
     * Show withdrawal details
     */
    public function show(VendorWithdrawal $withdrawal)
    {
        $withdrawal->load(['vendor.user', 'vendor.balanceRecord']);
        return view('admin.withdrawals.show', compact('withdrawal'));
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
            \App\Models\AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'withdrawal_approved',
                'model' => 'VendorWithdrawal',
                'model_id' => $withdrawal->id,
                'old_values' => ['status' => VendorWithdrawal::STATUS_PENDING],
                'new_values' => ['status' => VendorWithdrawal::STATUS_PROCESSING],
                'ip' => $request->ip(),
            ]);
            
            // Notify vendor
            \App\Models\NotificationQueue::create([
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
     * Mark withdrawal as processing (admin is processing it)
     */
    public function process(Request $request, VendorWithdrawal $withdrawal)
    {
        $request->validate([
            'transaction_id' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);
        
        if ($withdrawal->status !== VendorWithdrawal::STATUS_PROCESSING) {
            return back()->with('error', 'Withdrawal is not in processing status.');
        }
        
        DB::beginTransaction();
        try {
            $withdrawal->update([
                'transaction_id' => $request->transaction_id,
                'meta' => array_merge($withdrawal->meta ?? [], [
                    'processing_notes' => $request->notes,
                    'processed_by' => auth()->id(),
                    'processed_at' => now()->toDateTimeString(),
                ])
            ]);
            
            // Log the processing
            \App\Models\AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'withdrawal_processing',
                'model' => 'VendorWithdrawal',
                'model_id' => $withdrawal->id,
                'new_values' => [
                    'transaction_id' => $request->transaction_id,
                    'processed_by' => auth()->id(),
                ],
                'ip' => $request->ip(),
            ]);
            
            DB::commit();
            
            return back()->with('success', 'Withdrawal processing details updated.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update withdrawal: ' . $e->getMessage());
        }
    }
    
    /**
     * Complete a withdrawal (mark as completed)
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
            \App\Models\AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'withdrawal_completed',
                'model' => 'VendorWithdrawal',
                'model_id' => $withdrawal->id,
                'old_values' => ['status' => $withdrawal->status],
                'new_values' => ['status' => VendorWithdrawal::STATUS_COMPLETED],
                'ip' => $request->ip(),
            ]);
            
            // Notify vendor
            \App\Models\NotificationQueue::create([
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
            \App\Models\AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'withdrawal_rejected',
                'model' => 'VendorWithdrawal',
                'model_id' => $withdrawal->id,
                'old_values' => ['status' => $withdrawal->status],
                'new_values' => ['status' => VendorWithdrawal::STATUS_REJECTED, 'reason' => $request->reason],
                'ip' => $request->ip(),
            ]);
            
            // Notify vendor
            \App\Models\NotificationQueue::create([
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