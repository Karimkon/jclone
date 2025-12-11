<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BuyerWallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $wallet = $user->buyerWallet ?? $this->createWallet($user);
        
        $transactions = $user->walletTransactions()
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('buyer.wallet.index', compact('wallet', 'transactions'));
    }
    
    public function deposit(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1|max:10000',
            'payment_method' => 'required|in:card,bank_transfer,mobile_money',
            'reference' => 'nullable|string|max:255',
        ]);
        
        $user = Auth::user();
        $wallet = $user->buyerWallet ?? $this->createWallet($user);
        
        DB::beginTransaction();
        try {
            // Create pending transaction
            $transaction = $user->walletTransactions()->create([
                'type' => 'deposit',
                'amount' => $request->amount,
                'balance_before' => $wallet->balance,
                'balance_after' => $wallet->balance + $request->amount,
                'reference' => $request->reference ?? 'DEP-' . time(),
                'status' => 'pending',
                'description' => 'Wallet deposit via ' . $request->payment_method,
                'meta' => [
                    'payment_method' => $request->payment_method,
                    'deposited_at' => now()->toDateTimeString(),
                ]
            ]);
            
            // In a real app, integrate with payment gateway here
            // For MVP, auto-complete the deposit
            $transaction->update(['status' => 'completed']);
            $wallet->increment('balance', $request->amount);
            
            DB::commit();
            
            return back()->with('success', 'Deposit successful! Amount will reflect in your wallet shortly.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Deposit failed: ' . $e->getMessage());
        }
    }

    /**
     * Get current wallet balance (AJAX)
     */
    public function getBalance(Request $request)
    {
        $wallet = BuyerWallet::firstOrCreate(
            ['user_id' => Auth::id()],
            ['balance' => 0]
        );
        
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'balance' => $wallet->balance,
                'formatted_balance' => number_format($wallet->balance, 2),
            ]);
        }
        
        // For non-AJAX requests, redirect to wallet page
        return redirect()->route('buyer.wallet.index');
    }
    
    public function withdraw(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'withdrawal_method' => 'required|in:bank_account,mobile_money',
            'account_details' => 'required|string|max:500',
        ]);
        
        $user = Auth::user();
        $wallet = $user->buyerWallet;
        
        if (!$wallet || $wallet->available_balance < $request->amount) {
            return back()->with('error', 'Insufficient balance.');
        }
        
        DB::beginTransaction();
        try {
            // Create withdrawal transaction
            $transaction = $user->walletTransactions()->create([
                'type' => 'withdrawal',
                'amount' => -$request->amount,
                'balance_before' => $wallet->balance,
                'balance_after' => $wallet->balance - $request->amount,
                'reference' => 'WTH-' . time(),
                'status' => 'pending',
                'description' => 'Wallet withdrawal via ' . $request->withdrawal_method,
                'meta' => [
                    'withdrawal_method' => $request->withdrawal_method,
                    'account_details' => $request->account_details,
                    'requested_at' => now()->toDateTimeString(),
                ]
            ]);
            
            // Lock the amount
            $wallet->increment('locked_balance', $request->amount);
            
            DB::commit();
            
            // In a real app, you would process the withdrawal here
            // For MVP, we'll just mark it as pending
            
            return back()->with('success', 'Withdrawal request submitted. It will be processed within 24-48 hours.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Withdrawal failed: ' . $e->getMessage());
        }
    }
    
   public function transactions(Request $request)
{
    $query = Auth::user()->walletTransactions()
        ->orderBy('created_at', 'desc');
    
    // Apply filters
    if ($request->filled('type')) {
        $query->where('type', $request->type);
    }
    
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }
    
    if ($request->filled('from_date')) {
        $query->whereDate('created_at', '>=', $request->from_date);
    }
    
    if ($request->filled('to_date')) {
        $query->whereDate('created_at', '<=', $request->to_date);
    }
    
    $transactions = $query->paginate(50);
    
    return view('buyer.wallet.transactions', compact('transactions'));
}

public function exportTransactions(Request $request)
{
    $query = Auth::user()->walletTransactions()
        ->orderBy('created_at', 'desc');
    
     if ($request->filled('type')) {
        $query->where('type', $request->type);
    }
    
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }
    
    if ($request->filled('from_date')) {
        $query->whereDate('created_at', '>=', $request->from_date);
    }
    
    if ($request->filled('to_date')) {
        $query->whereDate('created_at', '<=', $request->to_date);
    }
    
    $transactions = $query->get();
    
    $filename = 'wallet-transactions-' . date('Y-m-d') . '.csv';
    
    $headers = [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => "attachment; filename=\"$filename\"",
    ];
    
    $callback = function() use ($transactions) {
        $file = fopen('php://output', 'w');
        
        // Add CSV headers
        fputcsv($file, ['Date', 'Type', 'Description', 'Amount', 'Balance After', 'Status', 'Reference']);
        
        // Add data rows
        foreach ($transactions as $transaction) {
            fputcsv($file, [
                $transaction->created_at->format('Y-m-d H:i:s'),
                $transaction->type,
                $transaction->description,
                $transaction->amount,
                $transaction->balance_after,
                $transaction->status,
                $transaction->reference
            ]);
        }
        
        fclose($file);
    };
    
    return response()->stream($callback, 200, $headers);
}
    
    private function createWallet($user)
    {
        return BuyerWallet::create([
            'user_id' => $user->id,
            'balance' => 0,
            'locked_balance' => 0,
            'currency' => 'USD',
            'meta' => ['created_at' => now()->toDateTimeString()]
        ]);
    }
}