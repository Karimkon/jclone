@extends('layouts.buyer')

@section('title', 'Wallet Transactions - ' . config('app.name'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Wallet Transactions</h1>
            <p class="text-gray-600 mt-2">View all your deposit, withdrawal, and payment history</p>
        </div>
        <a href="{{ route('buyer.wallet.index') }}" class="bg-primary text-white px-6 py-3 rounded-lg font-semibold hover:bg-indigo-700 transition inline-flex items-center">
            <i class="fas fa-arrow-left mr-2"></i> Back to Wallet
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6 rounded">
        <p class="text-green-700">{{ session('success') }}</p>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6 rounded">
        <p class="text-red-700">{{ session('error') }}</p>
    </div>
    @endif

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <h2 class="text-lg font-bold text-gray-800 mb-4">Filter Transactions</h2>
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                <select name="type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="">All Types</option>
                    <option value="deposit" {{ request('type') == 'deposit' ? 'selected' : '' }}>Deposit</option>
                    <option value="withdrawal" {{ request('type') == 'withdrawal' ? 'selected' : '' }}>Withdrawal</option>
                    <option value="payment" {{ request('type') == 'payment' ? 'selected' : '' }}>Payment</option>
                    <option value="refund" {{ request('type') == 'refund' ? 'selected' : '' }}>Refund</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="">All Status</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                <input type="date" name="from_date" value="{{ request('from_date') }}" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
            </div>
            
            <div class="flex items-end space-x-2">
                <button type="submit" class="bg-primary text-white px-6 py-2 rounded-lg font-semibold hover:bg-indigo-700 transition">
                    Apply Filters
                </button>
                <a href="{{ route('buyer.wallet.transactions') }}" class="border border-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-50 transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Transactions Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        @if($transactions->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($transactions as $transaction)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $transaction->created_at->format('M d, Y') }}</div>
                            <div class="text-sm text-gray-500">{{ $transaction->created_at->format('h:i A') }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $transaction->description }}</div>
                            @if($transaction->reference)
                            <div class="text-sm text-gray-500">Ref: {{ $transaction->reference }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                                @if($transaction->type === 'deposit') bg-green-100 text-green-800
                                @elseif($transaction->type === 'withdrawal') bg-red-100 text-red-800
                                @elseif($transaction->type === 'payment') bg-blue-100 text-blue-800
                                @elseif($transaction->type === 'refund') bg-yellow-100 text-yellow-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst($transaction->type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-lg font-semibold @if($transaction->amount > 0) text-green-600 @else text-red-600 @endif">
                                @if($transaction->amount > 0)
                                +${{ number_format($transaction->amount, 2) }}
                                @else
                                -${{ number_format(abs($transaction->amount), 2) }}
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">${{ number_format($transaction->balance_after, 2) }}</div>
                            <div class="text-xs text-gray-500">After transaction</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                @if($transaction->status === 'completed') bg-green-100 text-green-800
                                @elseif($transaction->status === 'pending') bg-yellow-100 text-yellow-800
                                @elseif($transaction->status === 'failed') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst($transaction->status) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="bg-gray-50 px-6 py-4">
            {{ $transactions->links() }}
        </div>
        @else
        <div class="text-center py-16">
            <div class="text-gray-400 text-6xl mb-4">
                <i class="fas fa-exchange-alt"></i>
            </div>
            <h3 class="text-xl font-medium text-gray-900 mb-2">No transactions found</h3>
            <p class="text-gray-500 mb-6">Your transaction history will appear here when you make deposits or payments</p>
            <a href="{{ route('buyer.wallet.index') }}" 
               class="inline-flex items-center px-6 py-3 bg-primary text-white rounded-lg font-semibold hover:bg-indigo-700 transition">
                <i class="fas fa-wallet mr-2"></i> Go to Wallet
            </a>
        </div>
        @endif
    </div>

    <!-- Export Option -->
    @if($transactions->count() > 0)
    <div class="mt-6 text-center">
        <button onclick="exportTransactions()" 
                class="inline-flex items-center px-6 py-3 border border-primary text-primary rounded-lg font-semibold hover:bg-primary hover:text-white transition">
            <i class="fas fa-file-export mr-2"></i> Export as CSV
        </button>
    </div>
    @endif
</div>

<script>
function exportTransactions() {
    // Create export URL with current filters
    const params = new URLSearchParams(window.location.search);
    window.open(`{{ route('buyer.wallet.transactions') }}/export?${params.toString()}`, '_blank');
}
</script>
@endsection