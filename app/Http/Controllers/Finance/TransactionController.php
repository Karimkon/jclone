<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\VendorTransaction;
use App\Models\Payment;
use App\Models\VendorProfile;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * Display all transactions
     */
    public function index(Request $request)
    {
        $query = VendorTransaction::with(['vendor.user', 'order']);

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
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

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by reference
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(25);

        // Transaction statistics
        $stats = [
            'total_sales' => VendorTransaction::where('type', 'sale')->sum('amount'),
            'total_commissions' => VendorTransaction::where('type', 'commission')->sum('amount'),
            'total_withdrawals' => VendorTransaction::where('type', 'withdrawal')->sum('amount'),
            'total_refunds' => VendorTransaction::where('type', 'refund')->sum('amount'),
        ];

        // Transaction types for filter
        $transactionTypes = [
            'sale' => 'Sales',
            'commission' => 'Commissions',
            'refund' => 'Refunds',
            'withdrawal' => 'Withdrawals',
            'deposit' => 'Deposits',
            'adjustment' => 'Adjustments',
            'promotion' => 'Promotions',
        ];

        // Get vendors for filter
        $vendors = VendorProfile::with('user')->where('vetting_status', 'approved')->get();

        return view('finance.transactions.index', compact('transactions', 'stats', 'transactionTypes', 'vendors'));
    }

    /**
     * Display payment transactions
     */
    public function payments(Request $request)
    {
        $query = Payment::with(['order.buyer', 'order.vendor']);

        // Filter by provider
        if ($request->filled('provider')) {
            $query->where('provider', $request->provider);
        }

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

        // Search by payment ID
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('provider_payment_id', 'like', "%{$search}%")
                  ->orWhereHas('order', function ($q) use ($search) {
                      $q->where('order_number', 'like', "%{$search}%");
                  });
            });
        }

        $payments = $query->orderBy('created_at', 'desc')->paginate(25);

        // Payment statistics
        $stats = [
            'total_completed' => Payment::where('status', 'completed')->sum('amount'),
            'total_pending' => Payment::where('status', 'pending')->sum('amount'),
            'total_failed' => Payment::where('status', 'failed')->count(),
            'total_refunded' => Payment::where('status', 'refunded')->sum('amount'),
        ];

        return view('finance.transactions.payments', compact('payments', 'stats'));
    }

    /**
     * Show transaction details
     */
    public function show(VendorTransaction $transaction)
    {
        $transaction->load(['vendor.user', 'order.buyer', 'order.payment']);

        return view('finance.transactions.show', compact('transaction'));
    }
}
