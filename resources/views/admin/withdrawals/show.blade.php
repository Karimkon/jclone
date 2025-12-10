@extends('layouts.admin')

@section('title', 'Withdrawal Details #' . $withdrawal->id)
@section('page-title', 'Withdrawal Details')
@section('page-description', 'View withdrawal request details')

@section('breadcrumb')
<nav class="flex" aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1 md:space-x-3">
        <li class="inline-flex items-center">
            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-primary-600">
                <i class="fas fa-home mr-2"></i>Dashboard
            </a>
        </li>
        <li>
            <div class="flex items-center">
                <i class="fas fa-chevron-right text-gray-400"></i>
                <a href="{{ route('admin.withdrawals.pending') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-primary-600 md:ml-2">Withdrawals</a>
            </div>
        </li>
        <li aria-current="page">
            <div class="flex items-center">
                <i class="fas fa-chevron-right text-gray-400"></i>
                <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">#{{ $withdrawal->id }}</span>
            </div>
        </li>
    </ol>
</nav>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 mb-2">Withdrawal #{{ $withdrawal->id }}</h1>
                <div class="flex items-center space-x-4">
                    <span class="px-3 py-1 rounded-full text-sm font-medium
                        @if($withdrawal->status === 'pending') bg-yellow-100 text-yellow-800
                        @elseif($withdrawal->status === 'processing') bg-blue-100 text-blue-800
                        @elseif($withdrawal->status === 'completed') bg-green-100 text-green-800
                        @elseif($withdrawal->status === 'rejected') bg-red-100 text-red-800
                        @else bg-gray-100 text-gray-800 @endif">
                        {{ $withdrawal->status_label }}
                    </span>
                    <span class="text-gray-600">{{ $withdrawal->created_at->format('F d, Y h:i A') }}</span>
                </div>
            </div>
            
            <div class="mt-4 md:mt-0">
                <p class="text-3xl font-bold text-primary-600">${{ number_format($withdrawal->amount, 2) }}</p>
                <p class="text-sm text-gray-500 text-right">Net: ${{ number_format($withdrawal->net_amount, 2) }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Vendor Information -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Vendor Information</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="font-semibold text-gray-700 mb-2">Business Details</h3>
                        <div class="space-y-2">
                            <p class="text-lg font-medium">{{ $withdrawal->vendor->business_name }}</p>
                            <p class="text-gray-600">{{ $withdrawal->vendor->user->name }}</p>
                            <p class="text-gray-600">{{ $withdrawal->vendor->user->email }}</p>
                            <p class="text-gray-600">{{ $withdrawal->vendor->user->phone }}</p>
                        </div>
                    </div>
                    
                    <div>
                        <h3 class="font-semibold text-gray-700 mb-2">Balance Information</h3>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Current Balance:</span>
                                <span class="font-bold">${{ number_format($withdrawal->vendor->balanceRecord->balance ?? 0, 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Available Balance:</span>
                                <span class="font-bold">${{ number_format($withdrawal->vendor->balanceRecord->available_balance ?? 0, 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Pending Balance:</span>
                                <span class="font-bold">${{ number_format($withdrawal->vendor->balanceRecord->pending_balance ?? 0, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Details -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Payment Details</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="font-semibold text-gray-700 mb-2">Withdrawal Method</h3>
                        <p class="text-lg font-medium">{{ $withdrawal->method_label }}</p>
                        
                        <div class="mt-4 space-y-2">
                            <h4 class="font-semibold text-gray-700">Account Details:</h4>
                            @if($withdrawal->method === 'bank_transfer')
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <p><span class="font-medium">Bank:</span> {{ $withdrawal->account_details['bank_name'] ?? 'N/A' }}</p>
                                <p><span class="font-medium">Account Name:</span> {{ $withdrawal->account_details['account_name'] ?? 'N/A' }}</p>
                                <p><span class="font-medium">Account Number:</span> {{ $withdrawal->account_details['account_number'] ?? 'N/A' }}</p>
                            </div>
                            @elseif($withdrawal->method === 'mobile_money')
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <p><span class="font-medium">Provider:</span> {{ $withdrawal->account_details['mobile_provider'] ?? 'N/A' }}</p>
                                <p><span class="font-medium">Phone Number:</span> {{ $withdrawal->account_details['account_number'] ?? 'N/A' }}</p>
                                <p><span class="font-medium">Account Name:</span> {{ $withdrawal->account_details['account_name'] ?? 'N/A' }}</p>
                            </div>
                            @elseif($withdrawal->method === 'paypal')
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <p><span class="font-medium">PayPal Email:</span> {{ $withdrawal->account_details['paypal_email'] ?? 'N/A' }}</p>
                                <p><span class="font-medium">Account Name:</span> {{ $withdrawal->account_details['account_name'] ?? 'N/A' }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    <div>
                        <h3 class="font-semibold text-gray-700 mb-2">Financial Details</h3>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Requested Amount:</span>
                                <span class="font-bold">${{ number_format($withdrawal->amount, 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Processing Fee:</span>
                                <span class="font-bold text-red-600">-${{ number_format($withdrawal->fee, 2) }}</span>
                            </div>
                            <div class="border-t pt-2">
                                <div class="flex justify-between font-bold text-lg">
                                    <span>Net Amount:</span>
                                    <span class="text-green-600">${{ number_format($withdrawal->net_amount, 2) }}</span>
                                </div>
                            </div>
                        </div>
                        
                        @if($withdrawal->transaction_id)
                        <div class="mt-4">
                            <h4 class="font-semibold text-gray-700">Transaction Reference</h4>
                            <div class="bg-blue-50 p-3 rounded-lg mt-2">
                                <p><span class="font-medium">Transaction ID:</span> {{ $withdrawal->transaction_id }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="space-y-6">
            <!-- Timeline -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Status Timeline</h2>
                
                <div class="space-y-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-8 h-8 bg-green-100 text-green-600 rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Request Submitted</p>
                            <p class="text-sm text-gray-500">{{ $withdrawal->created_at->format('M d, Y h:i A') }}</p>
                        </div>
                    </div>
                    
                    @if($withdrawal->processed_at)
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-cog"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Processing Started</p>
                            <p class="text-sm text-gray-500">{{ $withdrawal->processed_at->format('M d, Y h:i A') }}</p>
                            @if($withdrawal->meta['processing_notes'] ?? false)
                            <p class="text-xs text-gray-500 mt-1">{{ $withdrawal->meta['processing_notes'] }}</p>
                            @endif
                        </div>
                    </div>
                    @endif
                    
                    @if($withdrawal->completed_at)
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-8 h-8 bg-green-100 text-green-600 rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Completed</p>
                            <p class="text-sm text-gray-500">{{ $withdrawal->completed_at->format('M d, Y h:i A') }}</p>
                            @if($withdrawal->meta['completion_notes'] ?? false)
                            <p class="text-xs text-gray-500 mt-1">{{ $withdrawal->meta['completion_notes'] }}</p>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Actions</h2>
                
                <div class="space-y-3">
                    @if($withdrawal->status === 'pending')
                    <form action="{{ route('admin.withdrawals.approve', $withdrawal) }}" method="POST">
                        @csrf
                        <button type="submit" 
                                class="w-full bg-green-600 text-white py-2.5 rounded-lg font-medium hover:bg-green-700 transition flex items-center justify-center">
                            <i class="fas fa-check mr-2"></i> Approve & Start Processing
                        </button>
                    </form>
                    @endif
                    
                    @if($withdrawal->status === 'processing')
                    <form action="{{ route('admin.withdrawals.complete', $withdrawal) }}" method="POST" id="completeFormInline">
                        @csrf
                        <div class="mb-3">
                            <label class="block text-gray-700 mb-1 text-sm">Transaction ID *</label>
                            <input type="text" name="transaction_id" required
                                   class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        </div>
                        <button type="submit" 
                                class="w-full bg-green-600 text-white py-2.5 rounded-lg font-medium hover:bg-green-700 transition flex items-center justify-center">
                            <i class="fas fa-check-circle mr-2"></i> Mark as Completed
                        </button>
                    </form>
                    @endif
                    
                    @if(in_array($withdrawal->status, ['pending', 'processing']))
                    <button onclick="document.getElementById('rejectFormInline').classList.toggle('hidden')"
                            class="w-full bg-red-600 text-white py-2.5 rounded-lg font-medium hover:bg-red-700 transition flex items-center justify-center">
                        <i class="fas fa-times mr-2"></i> Reject Withdrawal
                    </button>
                    
                    <form action="{{ route('admin.withdrawals.reject', $withdrawal) }}" method="POST" id="rejectFormInline" class="hidden">
                        @csrf
                        <div class="mb-3">
                            <label class="block text-gray-700 mb-1 text-sm">Reason *</label>
                            <textarea name="reason" rows="2" required
                                      class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent"></textarea>
                        </div>
                        <button type="submit" 
                                class="w-full bg-red-600 text-white py-2.5 rounded-lg font-medium hover:bg-red-700 transition flex items-center justify-center">
                            <i class="fas fa-times mr-2"></i> Confirm Rejection
                        </button>
                    </form>
                    @endif
                    
                    <a href="{{ route('admin.withdrawals.pending') }}" 
                       class="block w-full border border-gray-300 text-gray-700 py-2.5 rounded-lg font-medium hover:bg-gray-50 transition text-center">
                        <i class="fas fa-arrow-left mr-2"></i> Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection