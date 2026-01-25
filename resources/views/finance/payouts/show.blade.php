@extends('layouts.finance')

@section('title', 'Withdrawal Details')
@section('page-title', 'Withdrawal #' . $withdrawal->id)
@section('page-description', 'View withdrawal details')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Back Link -->
    <a href="{{ url()->previous() }}" class="inline-flex items-center text-gray-600 hover:text-green-600">
        <i class="fas fa-arrow-left mr-2"></i> Back
    </a>

    <!-- Status Banner -->
    <div class="p-4 rounded-xl
        @if($withdrawal->status === 'pending') bg-yellow-50 border border-yellow-200
        @elseif($withdrawal->status === 'processing') bg-blue-50 border border-blue-200
        @elseif($withdrawal->status === 'completed') bg-green-50 border border-green-200
        @else bg-red-50 border border-red-200
        @endif">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <i class="fas
                    @if($withdrawal->status === 'pending') fa-clock text-yellow-600
                    @elseif($withdrawal->status === 'processing') fa-spinner text-blue-600
                    @elseif($withdrawal->status === 'completed') fa-check-circle text-green-600
                    @else fa-times-circle text-red-600
                    @endif text-2xl mr-3"></i>
                <div>
                    <p class="font-semibold text-gray-900">Status: {{ ucfirst($withdrawal->status) }}</p>
                    <p class="text-sm text-gray-600">Created {{ $withdrawal->created_at->diffForHumans() }}</p>
                </div>
            </div>
            <div class="text-right">
                <p class="text-3xl font-bold text-gray-900">${{ number_format($withdrawal->net_amount, 2) }}</p>
                <p class="text-sm text-gray-500">Net Amount</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Withdrawal Info -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Withdrawal Information</h3>
            <dl class="space-y-3">
                <div class="flex justify-between">
                    <dt class="text-gray-600">Amount Requested:</dt>
                    <dd class="font-medium">${{ number_format($withdrawal->amount, 2) }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-600">Fee:</dt>
                    <dd class="font-medium text-red-600">-${{ number_format($withdrawal->fee, 2) }}</dd>
                </div>
                <div class="flex justify-between border-t pt-3">
                    <dt class="text-gray-900 font-semibold">Net Amount:</dt>
                    <dd class="font-bold text-green-600">${{ number_format($withdrawal->net_amount, 2) }}</dd>
                </div>
                <div class="flex justify-between pt-3">
                    <dt class="text-gray-600">Method:</dt>
                    <dd class="font-medium">{{ str_replace('_', ' ', ucfirst($withdrawal->method)) }}</dd>
                </div>
                @if($withdrawal->transaction_id)
                <div class="flex justify-between">
                    <dt class="text-gray-600">Transaction ID:</dt>
                    <dd class="font-mono text-sm">{{ $withdrawal->transaction_id }}</dd>
                </div>
                @endif
            </dl>
        </div>

        <!-- Vendor Info -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Vendor Information</h3>
            <div class="flex items-center mb-4">
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center text-green-600 font-bold text-xl">
                    {{ substr($withdrawal->vendor->user->name ?? 'V', 0, 1) }}
                </div>
                <div class="ml-3">
                    <p class="font-semibold text-gray-900">{{ $withdrawal->vendor->user->name ?? 'Vendor' }}</p>
                    <p class="text-sm text-gray-500">{{ $withdrawal->vendor->user->email ?? '' }}</p>
                </div>
            </div>
            <dl class="space-y-2">
                <div class="flex justify-between">
                    <dt class="text-gray-600">Business:</dt>
                    <dd class="font-medium">{{ $withdrawal->vendor->business_name ?? 'N/A' }}</dd>
                </div>
                @if($withdrawal->vendor->balanceRecord)
                <div class="flex justify-between">
                    <dt class="text-gray-600">Current Balance:</dt>
                    <dd class="font-medium text-green-600">${{ number_format($withdrawal->vendor->balanceRecord->balance ?? 0, 2) }}</dd>
                </div>
                @endif
            </dl>
        </div>
    </div>

    <!-- Account Details -->
    @if($withdrawal->account_details)
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="font-semibold text-gray-900 mb-4">Payment Account Details</h3>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            @foreach($withdrawal->account_details as $key => $value)
                <div class="p-3 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-500 uppercase">{{ str_replace('_', ' ', $key) }}</p>
                    <p class="font-medium text-gray-900">{{ $value }}</p>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Actions -->
    @if(in_array($withdrawal->status, ['pending', 'processing']))
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="font-semibold text-gray-900 mb-4">Actions</h3>
        <div class="flex flex-wrap gap-4">
            @if($withdrawal->status === 'pending')
                <form action="{{ route('finance.payouts.approve', $withdrawal) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        <i class="fas fa-check mr-2"></i>Approve & Process
                    </button>
                </form>
            @endif

            <form action="{{ route('finance.payouts.complete', $withdrawal) }}" method="POST" class="flex gap-2">
                @csrf
                <input type="text" name="transaction_id" placeholder="Transaction ID (optional)" class="border rounded-lg px-4 py-2">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <i class="fas fa-check-double mr-2"></i>Mark Complete
                </button>
            </form>

            <button onclick="document.getElementById('rejectForm').classList.toggle('hidden')" class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                <i class="fas fa-times mr-2"></i>Reject
            </button>
        </div>

        <!-- Reject Form -->
        <form id="rejectForm" action="{{ route('finance.payouts.reject', $withdrawal) }}" method="POST" class="hidden mt-4 p-4 bg-red-50 rounded-lg">
            @csrf
            <label class="block text-sm font-medium text-gray-700 mb-2">Rejection Reason</label>
            <textarea name="reason" rows="3" class="w-full border rounded-lg p-3 mb-3" required placeholder="Enter reason for rejection..."></textarea>
            <button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                Confirm Rejection
            </button>
        </form>
    </div>
    @endif

    <!-- Rejection Info -->
    @if($withdrawal->status === 'rejected' && $withdrawal->rejection_reason)
    <div class="bg-red-50 border border-red-200 rounded-xl p-6">
        <h3 class="font-semibold text-red-800 mb-2">Rejection Reason</h3>
        <p class="text-red-700">{{ $withdrawal->rejection_reason }}</p>
    </div>
    @endif
</div>
@endsection
