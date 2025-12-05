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
    
    public function transactions()
    {
        $transactions = Auth::user()->walletTransactions()
            ->orderBy('created_at', 'desc')
            ->paginate(50);
        
        return view('buyer.wallet.transactions', compact('transactions'));
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