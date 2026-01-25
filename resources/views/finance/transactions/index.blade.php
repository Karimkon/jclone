@extends('layouts.finance')

@section('title', 'Transactions')
@section('page-title', 'All Transactions')
@section('page-description', 'View vendor transaction history')

@section('content')
<div class="space-y-6">
    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow-sm p-4 text-center border-l-4 border-green-500">
            <p class="text-2xl font-bold text-green-600">${{ number_format($stats['total_sales'], 2) }}</p>
            <p class="text-xs text-gray-500">Total Sales</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4 text-center border-l-4 border-blue-500">
            <p class="text-2xl font-bold text-blue-600">${{ number_format($stats['total_commissions'], 2) }}</p>
            <p class="text-xs text-gray-500">Commissions</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4 text-center border-l-4 border-orange-500">
            <p class="text-2xl font-bold text-orange-600">${{ number_format($stats['total_withdrawals'], 2) }}</p>
            <p class="text-xs text-gray-500">Withdrawals</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4 text-center border-l-4 border-red-500">
            <p class="text-2xl font-bold text-red-600">${{ number_format($stats['total_refunds'], 2) }}</p>
            <p class="text-xs text-gray-500">Refunds</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by reference..." class="w-full border rounded-lg px-4 py-2">
            </div>
            <select name="type" class="border rounded-lg px-4 py-2">
                <option value="">All Types</option>
                @foreach($transactionTypes as $key => $label)
                    <option value="{{ $key }}" {{ request('type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <select name="vendor_id" class="border rounded-lg px-4 py-2">
                <option value="">All Vendors</option>
                @foreach($vendors as $vendor)
                    <option value="{{ $vendor->id }}" {{ request('vendor_id') == $vendor->id ? 'selected' : '' }}>
                        {{ $vendor->user->name ?? 'Vendor #' . $vendor->id }}
                    </option>
                @endforeach
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="border rounded-lg px-4 py-2">
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="border rounded-lg px-4 py-2">
            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                <i class="fas fa-search mr-2"></i>Filter
            </button>
            <a href="{{ route('finance.transactions.index') }}" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Clear</a>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vendor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Balance</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($transactions as $transaction)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $transaction->created_at->format('M d, Y') }}
                                <br><span class="text-xs">{{ $transaction->created_at->format('H:i') }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-medium text-gray-900">{{ $transaction->vendor->user->name ?? 'N/A' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded-full
                                    @if($transaction->type === 'sale') bg-green-100 text-green-700
                                    @elseif($transaction->type === 'commission') bg-blue-100 text-blue-700
                                    @elseif($transaction->type === 'withdrawal') bg-orange-100 text-orange-700
                                    @elseif($transaction->type === 'refund') bg-red-100 text-red-700
                                    @elseif($transaction->type === 'deposit') bg-purple-100 text-purple-700
                                    @else bg-gray-100 text-gray-700
                                    @endif">
                                    {{ ucfirst($transaction->type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate">
                                {{ $transaction->description ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm font-mono text-gray-500">
                                {{ $transaction->reference ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm font-bold text-right {{ $transaction->amount >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $transaction->amount >= 0 ? '+' : '' }}${{ number_format($transaction->amount, 2) }}
                            </td>
                            <td class="px-6 py-4 text-sm text-right text-gray-600">
                                ${{ number_format($transaction->balance_after ?? 0, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">No transactions found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transactions->hasPages())
            <div class="p-4 border-t">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
