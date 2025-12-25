@extends('layouts.buyer')

@section('title', 'Wallet Transactions - ' . config('app.name'))

@section('content')
<div class="container mx-auto px-2 sm:px-4 py-4 sm:py-8 pb-20 sm:pb-8">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6 sm:mb-8">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Wallet Transactions</h1>
            <p class="text-sm sm:text-base text-gray-600 mt-1 sm:mt-2">View all your deposit, withdrawal, and payment history</p>
        </div>
        <a href="{{ route('buyer.wallet.index') }}" class="w-full sm:w-auto bg-primary text-white px-6 py-2.5 sm:py-3 rounded-lg font-semibold hover:bg-indigo-700 transition inline-flex items-center justify-center text-sm sm:text-base">
            <i class="fas fa-arrow-left mr-2"></i> Back to Wallet
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6 rounded shadow-sm">
        <p class="text-green-700 text-sm sm:text-base">{{ session('success') }}</p>
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm p-4 sm:p-6 mb-6">
        <h2 class="text-lg font-bold text-gray-800 mb-4">Filter Transactions</h2>
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Type</label>
                <select name="type" class="w-full px-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="">All Types</option>
                    <option value="deposit" {{ request('type') == 'deposit' ? 'selected' : '' }}>Deposit</option>
                    <option value="withdrawal" {{ request('type') == 'withdrawal' ? 'selected' : '' }}>Withdrawal</option>
                    <option value="payment" {{ request('type') == 'payment' ? 'selected' : '' }}>Payment</option>
                    <option value="refund" {{ request('type') == 'refund' ? 'selected' : '' }}>Refund</option>
                </select>
            </div>
            
            <div>
                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full px-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="">All Status</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                </select>
            </div>
            
            <div>
                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">From Date</label>
                <input type="date" name="from_date" value="{{ request('from_date') }}" 
                       class="w-full px-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
            </div>
            
            <div class="flex items-end space-x-2">
                <button type="submit" class="flex-1 bg-primary text-white px-4 py-2 rounded-lg font-semibold hover:bg-indigo-700 transition text-sm">
                    Apply
                </button>
                <a href="{{ route('buyer.wallet.transactions') }}" class="flex-1 border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition text-sm text-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    @if($transactions->count() > 0)
    <!-- Mobile Card View -->
    <div class="sm:hidden space-y-3">
        @foreach($transactions as $transaction)
        <div class="bg-white rounded-lg shadow-sm p-4">
            <div class="flex justify-between items-start mb-2">
                <div class="flex items-center gap-2">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0
                        @if($transaction->type === 'deposit') bg-green-100
                        @elseif($transaction->type === 'withdrawal') bg-red-100
                        @elseif($transaction->type === 'payment') bg-blue-100
                        @elseif($transaction->type === 'refund') bg-yellow-100
                        @else bg-gray-100 @endif">
                        <i class="fas
                            @if($transaction->type === 'deposit') fa-arrow-down text-green-600
                            @elseif($transaction->type === 'withdrawal') fa-arrow-up text-red-600
                            @elseif($transaction->type === 'payment') fa-shopping-cart text-blue-600
                            @elseif($transaction->type === 'refund') fa-undo text-yellow-600
                            @else fa-exchange-alt text-gray-600 @endif text-sm"></i>
                    </div>
                    <div>
                        <span class="px-2 py-0.5 rounded text-xs font-bold uppercase
                            @if($transaction->type === 'deposit') bg-green-100 text-green-700
                            @elseif($transaction->type === 'withdrawal') bg-red-100 text-red-700
                            @elseif($transaction->type === 'payment') bg-blue-100 text-blue-700
                            @elseif($transaction->type === 'refund') bg-yellow-100 text-yellow-700
                            @else bg-gray-100 text-gray-700 @endif">
                            {{ $transaction->type }}
                        </span>
                        <p class="text-xs text-gray-500 mt-1">{{ $transaction->created_at->format('M d, Y \a\t h:i A') }}</p>
                    </div>
                </div>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                    @if($transaction->status === 'completed') bg-green-100 text-green-800
                    @elseif($transaction->status === 'pending') bg-yellow-100 text-yellow-800
                    @else bg-red-100 text-red-800 @endif">
                    {{ ucfirst($transaction->status) }}
                </span>
            </div>

            <p class="text-sm text-gray-700 mb-3">{{ Str::limit($transaction->description, 60) }}</p>

            <div class="flex justify-between items-center pt-2 border-t">
                <div class="text-xs text-gray-500">
                    Balance: UGX {{ number_format($transaction->balance_after, 0) }}
                </div>
                <div class="text-lg font-bold @if($transaction->amount > 0) text-green-600 @else text-red-600 @endif">
                    {{ $transaction->amount > 0 ? '+' : '-' }}UGX {{ number_format(abs($transaction->amount), 0) }}
                </div>
            </div>
        </div>
        @endforeach

        <!-- Mobile Pagination -->
        <div class="mt-4">
            {{ $transactions->links() }}
        </div>
    </div>

    <!-- Desktop Table View -->
    <div class="hidden sm:block bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($transactions as $transaction)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $transaction->created_at->format('M d, Y') }}</div>
                            <div class="text-xs text-gray-500">{{ $transaction->created_at->format('h:i A') }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="text-sm font-medium text-gray-900">{{ Str::limit($transaction->description, 50) }}</span>
                                <span class="inline-block mt-1">
                                    <span class="px-2 py-0.5 rounded text-xs font-bold uppercase
                                        @if($transaction->type === 'deposit') bg-green-100 text-green-700
                                        @elseif($transaction->type === 'withdrawal') bg-red-100 text-red-700
                                        @elseif($transaction->type === 'payment') bg-blue-100 text-blue-700
                                        @elseif($transaction->type === 'refund') bg-yellow-100 text-yellow-700
                                        @else bg-gray-100 text-gray-700 @endif">
                                        {{ $transaction->type }}
                                    </span>
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-bold @if($transaction->amount > 0) text-green-600 @else text-red-600 @endif">
                                {{ $transaction->amount > 0 ? '+' : '-' }}UGX {{ number_format(abs($transaction->amount), 0) }}
                            </div>
                            <div class="text-xs text-gray-400">Bal: UGX {{ number_format($transaction->balance_after, 0) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($transaction->status === 'completed') bg-green-100 text-green-800
                                @elseif($transaction->status === 'pending') bg-yellow-100 text-yellow-800
                                @else bg-red-100 text-red-800 @endif">
                                {{ ucfirst($transaction->status) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
            {{ $transactions->links() }}
        </div>
    </div>
    @else
    <!-- Empty State -->
    <div class="bg-white rounded-xl shadow-sm text-center py-12 px-4">
        <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-exchange-alt text-gray-400 text-3xl"></i>
        </div>
        <h3 class="text-lg font-bold text-gray-900 mb-2">No transactions found</h3>
        <p class="text-sm text-gray-500 mb-6">Your transaction history will appear here once you start using your wallet.</p>
        <a href="{{ route('buyer.wallet.index') }}"
           class="inline-flex items-center px-6 py-3 bg-primary text-white rounded-lg font-semibold hover:bg-indigo-700 transition text-sm">
            <i class="fas fa-wallet mr-2"></i> Go to Wallet
        </a>
    </div>
    @endif

    @if($transactions->count() > 0)
    <div class="mt-8 mb-4 flex justify-center">
        <button onclick="exportTransactions()" 
                class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3 border border-primary text-primary rounded-lg font-semibold hover:bg-primary hover:text-white transition text-sm">
            <i class="fas fa-file-export mr-2"></i> Export as CSV
        </button>
    </div>
    @endif
</div>

<script>
function exportTransactions() {
    const params = new URLSearchParams(window.location.search);
    // Be sure the route exists in your web.php
    window.location.href = `{{ route('buyer.wallet.transactions') }}/export?${params.toString()}`;
}
</script>
@endsection