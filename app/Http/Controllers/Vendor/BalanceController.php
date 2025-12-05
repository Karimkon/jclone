<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VendorWithdrawal;
use App\Models\VendorTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BalanceController extends Controller
{
    /**
     * Show vendor balance dashboard
     */
    public function index()
    {
        $vendor = Auth::user()->vendorProfile;
        
        if (!$vendor) {
            return redirect()->route('vendor.onboard.create');
        }

        $balance = $vendor->balanceRecord;
        
        // Recent transactions
        $transactions = VendorTransaction::where('vendor_profile_id', $vendor->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Withdrawal history
        $withdrawals = $vendor->withdrawals()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Stats
        $stats = [
            'total_earned' => VendorTransaction::where('vendor_profile_id', $vendor->id)
                ->whereIn('type', ['sale', 'deposit', 'adjustment'])
                ->where('amount', '>', 0)
                ->sum('amount'),
            'total_commission' => abs(VendorTransaction::where('vendor_profile_id', $vendor->id)
                ->where('type', 'commission')
                ->sum('amount')),
            'total_withdrawn' => $vendor->withdrawals()
                ->where('status', VendorWithdrawal::STATUS_COMPLETED)
                ->sum('amount'),
            'pending_withdrawals' => $vendor->withdrawals()
                ->whereIn('status', [VendorWithdrawal::STATUS_PENDING, VendorWithdrawal::STATUS_PROCESSING])
                ->sum('amount'),
        ];

        return view('vendor.balance.index', compact(
            'balance', 
            'transactions', 
            'withdrawals', 
            'stats'
        ));
    }

    /**
     * Show withdrawal form
     */
    public function withdraw()
    {
        $vendor = Auth::user()->vendorProfile;
        
        if (!$vendor) {
            return redirect()->route('vendor.onboard.create');
        }

        $balance = $vendor->balanceRecord;
        $availableBalance = $balance->available_balance;

        // Get vendor's saved payment methods
        $paymentMethods = $vendor->user->meta['payment_methods'] ?? [];

        return view('vendor.balance.withdraw', compact(
            'balance', 
            'availableBalance',
            'paymentMethods'
        ));
    }

    /**
     * Process withdrawal request
     */
    public function processWithdrawal(Request $request)
    {
        $vendor = Auth::user()->vendorProfile;
        
        if (!$vendor) {
            return redirect()->route('vendor.onboard.create');
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:10|max:10000',
            'method' => 'required|in:bank_transfer,mobile_money,paypal',
            'account_details' => 'required|array',
            'account_details.account_name' => 'required|string',
            'account_details.account_number' => 'required|string',
            'account_details.bank_name' => 'required_if:method,bank_transfer',
            'account_details.mobile_provider' => 'required_if:method,mobile_money',
            'account_details.paypal_email' => 'required_if:method,paypal|email',
        ]);

        $balance = $vendor->balanceRecord;
        $availableBalance = $balance->available_balance;

        // Check if vendor has sufficient balance
        if ($availableBalance < $validated['amount']) {
            return back()->with('error', 'Insufficient balance for withdrawal.');
        }

        // Calculate fee
        $fee = VendorWithdrawal::calculateFee($validated['amount'], $validated['method']);
        $netAmount = $validated['amount'] - $fee;

        DB::beginTransaction();
        try {
            // Create withdrawal request
            $withdrawal = VendorWithdrawal::create([
                'vendor_profile_id' => $vendor->id,
                'amount' => $validated['amount'],
                'fee' => $fee,
                'net_amount' => $netAmount,
                'method' => $validated['method'],
                'account_details' => $validated['account_details'],
                'status' => VendorWithdrawal::STATUS_PENDING,
            ]);

            // Deduct from balance
            $balance->debit(
                $validated['amount'],
                'Withdrawal request #' . $withdrawal->id,
                'WDR-' . str_pad($withdrawal->id, 6, '0', STR_PAD_LEFT),
                [
                    'withdrawal_id' => $withdrawal->id,
                    'method' => $validated['method'],
                    'fee' => $fee,
                    'net_amount' => $netAmount,
                ]
            );

            DB::commit();

            // Notify admin
            \App\Models\NotificationQueue::create([
                'type' => 'admin_notification',
                'title' => 'New Withdrawal Request',
                'message' => "Vendor {$vendor->business_name} requested withdrawal of \${$validated['amount']}",
                'meta' => [
                    'vendor_id' => $vendor->id,
                    'withdrawal_id' => $withdrawal->id,
                    'amount' => $validated['amount'],
                    'action_url' => route('admin.withdrawals.pending'),
                ],
                'status' => 'pending',
            ]);

            return redirect()->route('vendor.balance.index')
                ->with('success', 'Withdrawal request submitted successfully. It will be processed within 3-5 business days.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to process withdrawal: ' . $e->getMessage());
        }
    }

    /**
     * Cancel withdrawal request
     */
    public function cancelWithdrawal(VendorWithdrawal $withdrawal)
    {
        // Check ownership
        if ($withdrawal->vendor_profile_id !== Auth::user()->vendorProfile->id) {
            abort(403);
        }

        if (!$withdrawal->canBeCancelled()) {
            return back()->with('error', 'This withdrawal cannot be cancelled.');
        }

        DB::beginTransaction();
        try {
            // Refund to balance
            $balance = Auth::user()->vendorProfile->balanceRecord;
            $balance->credit(
                $withdrawal->amount,
                'Withdrawal cancellation #' . $withdrawal->id,
                'WDR-CANCEL-' . $withdrawal->id,
                ['withdrawal_id' => $withdrawal->id]
            );

            // Update withdrawal status
            $withdrawal->update([
                'status' => VendorWithdrawal::STATUS_CANCELLED,
                'meta' => array_merge($withdrawal->meta ?? [], [
                    'cancelled_at' => now()->toDateTimeString(),
                    'cancelled_by' => Auth::id(),
                ])
            ]);

            DB::commit();

            return back()->with('success', 'Withdrawal cancelled successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to cancel withdrawal.');
        }
    }

    /**
     * Export transactions
     */
    public function exportTransactions(Request $request)
    {
        $vendor = Auth::user()->vendorProfile;
        
        if (!$vendor) {
            return response()->json(['error' => 'No vendor profile'], 403);
        }

        $query = VendorTransaction::where('vendor_profile_id', $vendor->id);

        // Apply filters
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $transactions = $query->orderBy('created_at', 'desc')->get();

        // Format for CSV
        $csvData = $transactions->map(function($transaction) {
            return [
                'Date' => $transaction->created_at->format('Y-m-d H:i:s'),
                'Type' => $transaction->type_label,
                'Description' => $transaction->description,
                'Amount' => $transaction->amount,
                'Balance Before' => $transaction->balance_before,
                'Balance After' => $transaction->balance_after,
                'Reference' => $transaction->reference,
                'Status' => $transaction->status,
            ];
        });

        return response()->json([
            'transactions' => $csvData,
            'vendor' => $vendor->business_name,
            'exported_at' => now()->toDateTimeString(),
            'total_records' => $transactions->count(),
        ]);
    }
}