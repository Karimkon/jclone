@extends('layouts.admin')

@section('title', 'Pending Withdrawals')
@section('page-title', 'Withdrawal Management')
@section('page-description', 'Review and process vendor withdrawal requests')

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="stat-card bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center">
                <div class="bg-blue-100 p-3 rounded-lg mr-4">
                    <i class="fas fa-money-bill-wave text-blue-600 text-2xl"></i>
                </div>
                <div>
                    <p class="text-gray-600">Pending</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['pending'] }}</p>
                </div>
            </div>
        </div>
        
        <div class="stat-card bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center">
                <div class="bg-yellow-100 p-3 rounded-lg mr-4">
                    <i class="fas fa-cog text-yellow-600 text-2xl"></i>
                </div>
                <div>
                    <p class="text-gray-600">Processing</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['processing'] }}</p>
                </div>
            </div>
        </div>
        
        <div class="stat-card bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center">
                <div class="bg-green-100 p-3 rounded-lg mr-4">
                    <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                </div>
                <div>
                    <p class="text-gray-600">Completed</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['completed'] }}</p>
                </div>
            </div>
        </div>
        
        <div class="stat-card bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center">
                <div class="bg-red-100 p-3 rounded-lg mr-4">
                    <i class="fas fa-times-circle text-red-600 text-2xl"></i>
                </div>
                <div>
                    <p class="text-gray-600">Rejected</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['rejected'] }}</p>
                </div>
            </div>
        </div>
        
        <div class="stat-card bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center">
                <div class="bg-purple-100 p-3 rounded-lg mr-4">
                    <i class="fas fa-dollar-sign text-purple-600 text-2xl"></i>
                </div>
                <div>
                    <p class="text-gray-600">Total Amount</p>
                    <p class="text-2xl font-bold text-gray-800">${{ number_format($stats['total_amount'], 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="bg-white rounded-xl shadow-sm">
        <div class="border-b border-gray-200">
            <nav class="flex -mb-px">
                <a href="{{ route('admin.withdrawals.pending') }}" 
                   class="py-4 px-6 text-sm font-medium border-b-2 border-transparent {{ request()->routeIs('admin.withdrawals.pending') ? 'border-primary-500 text-primary-600' : 'text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Pending & Processing
                </a>
                <a href="{{ route('admin.withdrawals.index') }}" 
                   class="py-4 px-6 text-sm font-medium border-b-2 border-transparent {{ request()->routeIs('admin.withdrawals.index') ? 'border-primary-500 text-primary-600' : 'text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    All Withdrawals
                </a>
            </nav>
        </div>
        
        <!-- Withdrawals Table -->
        <div class="overflow-x-auto">
            @if($withdrawals->count() > 0)
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vendor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($withdrawals as $withdrawal)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 bg-primary-100 rounded-full flex items-center justify-center mr-3">
                                    <i class="fas fa-store text-primary-600"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $withdrawal->vendor->business_name }}</div>
                                    <div class="text-xs text-gray-500">{{ $withdrawal->vendor->user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-bold text-gray-900">${{ number_format($withdrawal->amount, 2) }}</div>
                            <div class="text-xs text-gray-500">Fee: ${{ number_format($withdrawal->fee, 2) }}</div>
                            <div class="text-xs text-green-600">Net: ${{ number_format($withdrawal->net_amount, 2) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $withdrawal->method_label }}</div>
                            <div class="text-xs text-gray-500">
                                @if($withdrawal->method === 'bank_transfer')
                                {{ $withdrawal->account_details['bank_name'] ?? 'N/A' }}
                                @elseif($withdrawal->method === 'mobile_money')
                                {{ $withdrawal->account_details['mobile_provider'] ?? 'N/A' }}
                                @else
                                PayPal
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $withdrawal->created_at->format('M d, Y') }}</div>
                            <div class="text-xs text-gray-500">{{ $withdrawal->created_at->format('h:i A') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                @if($withdrawal->status === 'pending') bg-yellow-100 text-yellow-800
                                @elseif($withdrawal->status === 'processing') bg-blue-100 text-blue-800
                                @elseif($withdrawal->status === 'completed') bg-green-100 text-green-800
                                @elseif($withdrawal->status === 'rejected') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ $withdrawal->status_label }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                @if($withdrawal->status === 'pending')
                                <form action="{{ route('admin.withdrawals.approve', $withdrawal) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" 
                                            onclick="return confirm('Approve this withdrawal?')"
                                            class="text-green-600 hover:text-green-900">
                                        <i class="fas fa-check mr-1"></i> Approve
                                    </button>
                                </form>
                                @endif
                                
                                @if($withdrawal->status === 'processing')
                                <form action="{{ route('admin.withdrawals.complete', $withdrawal) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="button" 
                                            onclick="showCompleteModal('{{ $withdrawal->id }}')"
                                            class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-check-circle mr-1"></i> Complete
                                    </button>
                                </form>
                                @endif
                                
                                @if(in_array($withdrawal->status, ['pending', 'processing']))
                                <button onclick="showRejectModal('{{ $withdrawal->id }}')" 
                                        class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-times mr-1"></i> Reject
                                </button>
                                @endif
                                
                                <a href="{{ route('admin.withdrawals.show', $withdrawal) }}" 
                                   class="text-primary-600 hover:text-primary-900">
                                    <i class="fas fa-eye mr-1"></i> View
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            
            <!-- Pagination -->
            <div class="px-6 py-4 border-t">
                {{ $withdrawals->links() }}
            </div>
            @else
            <div class="text-center py-12">
                <div class="text-gray-400 text-5xl mb-4">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No pending withdrawals</h3>
                <p class="text-gray-500">All withdrawal requests have been processed.</p>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Reject Withdrawal</h3>
        <form id="rejectForm" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-gray-700 mb-2">Reason for Rejection *</label>
                <textarea name="reason" rows="3" required
                          class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                          placeholder="Provide a reason for rejecting this withdrawal..."></textarea>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 mb-2">Notes (Optional)</label>
                <textarea name="notes" rows="2"
                          class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                          placeholder="Additional notes..."></textarea>
            </div>
            
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeRejectModal()"
                        class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    <i class="fas fa-times mr-2"></i> Reject Withdrawal
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Complete Modal -->
<div id="completeModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Complete Withdrawal</h3>
        <form id="completeForm" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-gray-700 mb-2">Transaction ID *</label>
                <input type="text" name="transaction_id" required
                       class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                       placeholder="Enter the transaction/reference ID">
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 mb-2">Notes (Optional)</label>
                <textarea name="notes" rows="2"
                          class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                          placeholder="Additional notes..."></textarea>
            </div>
            
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeCompleteModal()"
                        class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    <i class="fas fa-check mr-2"></i> Mark as Completed
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    let currentWithdrawalId = null;
    
    function showRejectModal(withdrawalId) {
        currentWithdrawalId = withdrawalId;
        document.getElementById('rejectForm').action = `/admin/withdrawals/${withdrawalId}/reject`;
        document.getElementById('rejectModal').classList.remove('hidden');
    }
    
    function closeRejectModal() {
        document.getElementById('rejectModal').classList.add('hidden');
        document.getElementById('rejectForm').reset();
        currentWithdrawalId = null;
    }
    
    function showCompleteModal(withdrawalId) {
        currentWithdrawalId = withdrawalId;
        document.getElementById('completeForm').action = `/admin/withdrawals/${withdrawalId}/complete`;
        document.getElementById('completeModal').classList.remove('hidden');
    }
    
    function closeCompleteModal() {
        document.getElementById('completeModal').classList.add('hidden');
        document.getElementById('completeForm').reset();
        currentWithdrawalId = null;
    }
    
    // Close modals when clicking outside
    document.getElementById('rejectModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeRejectModal();
    });
    
    document.getElementById('completeModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeCompleteModal();
    });
</script>
@endsection