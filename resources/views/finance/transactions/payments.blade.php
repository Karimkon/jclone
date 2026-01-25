@extends('layouts.finance')

@section('title', 'Payment Transactions')
@section('page-title', 'Payment Transactions')
@section('page-description', 'View payment gateway transactions')

@section('content')
<div class="space-y-6">
    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow-sm p-4 text-center border-l-4 border-green-500">
            <p class="text-2xl font-bold text-green-600">${{ number_format($stats['total_completed'], 2) }}</p>
            <p class="text-xs text-gray-500">Completed</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4 text-center border-l-4 border-yellow-500">
            <p class="text-2xl font-bold text-yellow-600">${{ number_format($stats['total_pending'], 2) }}</p>
            <p class="text-xs text-gray-500">Pending</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4 text-center border-l-4 border-red-500">
            <p class="text-2xl font-bold text-red-600">{{ $stats['total_failed'] }}</p>
            <p class="text-xs text-gray-500">Failed</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4 text-center border-l-4 border-purple-500">
            <p class="text-2xl font-bold text-purple-600">${{ number_format($stats['total_refunded'], 2) }}</p>
            <p class="text-xs text-gray-500">Refunded</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by payment ID or order number..." class="w-full border rounded-lg px-4 py-2">
            </div>
            <select name="provider" class="border rounded-lg px-4 py-2">
                <option value="">All Providers</option>
                <option value="flutterwave" {{ request('provider') === 'flutterwave' ? 'selected' : '' }}>Flutterwave</option>
                <option value="pesapal" {{ request('provider') === 'pesapal' ? 'selected' : '' }}>PesaPal</option>
            </select>
            <select name="status" class="border rounded-lg px-4 py-2">
                <option value="">All Status</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>Refunded</option>
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="border rounded-lg px-4 py-2">
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="border rounded-lg px-4 py-2">
            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                <i class="fas fa-search mr-2"></i>Filter
            </button>
            <a href="{{ route('finance.transactions.payments') }}" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Clear</a>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Provider</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payment ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Buyer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($payments as $payment)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $payment->created_at->format('M d, Y') }}
                                <br><span class="text-xs">{{ $payment->created_at->format('H:i') }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-mono text-sm font-medium text-gray-900">#{{ $payment->order->order_number ?? 'N/A' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded-full
                                    @if($payment->provider === 'flutterwave') bg-orange-100 text-orange-700
                                    @else bg-green-100 text-green-700
                                    @endif">
                                    {{ ucfirst($payment->provider) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm font-mono text-gray-500">
                                {{ $payment->provider_payment_id ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ $payment->order->buyer->name ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded-full
                                    @if($payment->status === 'completed') bg-green-100 text-green-700
                                    @elseif($payment->status === 'pending') bg-yellow-100 text-yellow-700
                                    @elseif($payment->status === 'refunded') bg-purple-100 text-purple-700
                                    @else bg-red-100 text-red-700
                                    @endif">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm font-bold text-right text-gray-900">
                                ${{ number_format($payment->amount, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">No payments found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($payments->hasPages())
            <div class="p-4 border-t">
                {{ $payments->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
