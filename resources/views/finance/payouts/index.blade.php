@extends('layouts.finance')

@section('title', 'All Payouts')
@section('page-title', 'All Payouts')
@section('page-description', 'Complete withdrawal history')

@section('content')
<div class="space-y-6">
    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
        <div class="bg-white rounded-lg shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
            <p class="text-xs text-gray-500">Total</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-yellow-600">{{ $stats['pending'] }}</p>
            <p class="text-xs text-gray-500">Pending</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-blue-600">{{ $stats['processing'] }}</p>
            <p class="text-xs text-gray-500">Processing</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-green-600">{{ $stats['completed'] }}</p>
            <p class="text-xs text-gray-500">Completed</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-red-600">{{ $stats['rejected'] }}</p>
            <p class="text-xs text-gray-500">Rejected</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-green-600">${{ number_format($stats['total_paid'], 2) }}</p>
            <p class="text-xs text-gray-500">Total Paid</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by transaction ID or vendor..." class="w-full border rounded-lg px-4 py-2">
            </div>
            <select name="status" class="border rounded-lg px-4 py-2">
                <option value="">All Status</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
            <select name="method" class="border rounded-lg px-4 py-2">
                <option value="">All Methods</option>
                <option value="bank_transfer" {{ request('method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                <option value="mobile_money" {{ request('method') === 'mobile_money' ? 'selected' : '' }}>Mobile Money</option>
                <option value="paypal" {{ request('method') === 'paypal' ? 'selected' : '' }}>PayPal</option>
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="border rounded-lg px-4 py-2" placeholder="From">
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="border rounded-lg px-4 py-2" placeholder="To">
            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                <i class="fas fa-search mr-2"></i>Filter
            </button>
            <a href="{{ route('finance.payouts.index') }}" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Clear</a>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vendor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($withdrawals as $withdrawal)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-mono text-gray-500">#{{ $withdrawal->id }}</td>
                            <td class="px-6 py-4">
                                <p class="font-medium text-gray-900">{{ $withdrawal->vendor->user->name ?? 'Vendor' }}</p>
                                <p class="text-xs text-gray-500">{{ $withdrawal->vendor->business_name ?? '' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-bold text-gray-900">${{ number_format($withdrawal->amount, 2) }}</p>
                                <p class="text-xs text-green-600">Net: ${{ number_format($withdrawal->net_amount, 2) }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-700">
                                    {{ str_replace('_', ' ', ucfirst($withdrawal->method)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded-full
                                    @if($withdrawal->status === 'pending') bg-yellow-100 text-yellow-700
                                    @elseif($withdrawal->status === 'processing') bg-blue-100 text-blue-700
                                    @elseif($withdrawal->status === 'completed') bg-green-100 text-green-700
                                    @else bg-red-100 text-red-700
                                    @endif">
                                    {{ ucfirst($withdrawal->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $withdrawal->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('finance.payouts.show', $withdrawal) }}" class="text-green-600 hover:text-green-700">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">No withdrawals found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($withdrawals->hasPages())
            <div class="p-4 border-t">
                {{ $withdrawals->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
