@extends('layouts.finance')

@section('title', 'Pending Payouts')
@section('page-title', 'Pending Payouts')
@section('page-description', 'Process vendor withdrawal requests')

@section('content')
<div class="space-y-6">
    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-yellow-500">
            <p class="text-sm text-gray-600">Pending</p>
            <p class="text-2xl font-bold text-yellow-600">{{ $stats['pending'] }}</p>
            <p class="text-xs text-gray-500">${{ number_format($stats['pending_amount'], 2) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-blue-500">
            <p class="text-sm text-gray-600">Processing</p>
            <p class="text-2xl font-bold text-blue-600">{{ $stats['processing'] }}</p>
            <p class="text-xs text-gray-500">${{ number_format($stats['processing_amount'], 2) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-green-500">
            <p class="text-sm text-gray-600">Total Pending Amount</p>
            <p class="text-2xl font-bold text-green-600">${{ number_format($stats['pending_amount'] + $stats['processing_amount'], 2) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4">
            <a href="{{ route('finance.payouts.index') }}" class="flex items-center justify-center h-full text-green-600 hover:text-green-700">
                <i class="fas fa-list mr-2"></i> View All Payouts
            </a>
        </div>
    </div>

    <!-- Withdrawals Table -->
    <div class="bg-white rounded-xl shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vendor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Requested</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($withdrawals as $withdrawal)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center text-green-600 font-bold">
                                        {{ substr($withdrawal->vendor->user->name ?? 'V', 0, 1) }}
                                    </div>
                                    <div class="ml-3">
                                        <p class="font-medium text-gray-900">{{ $withdrawal->vendor->user->name ?? 'Vendor' }}</p>
                                        <p class="text-xs text-gray-500">{{ $withdrawal->vendor->user->email ?? '' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-bold text-gray-900">${{ number_format($withdrawal->amount, 2) }}</p>
                                <p class="text-xs text-gray-500">Fee: ${{ number_format($withdrawal->fee, 2) }}</p>
                                <p class="text-xs text-green-600">Net: ${{ number_format($withdrawal->net_amount, 2) }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded-full
                                    @if($withdrawal->method === 'bank_transfer') bg-blue-100 text-blue-700
                                    @elseif($withdrawal->method === 'mobile_money') bg-green-100 text-green-700
                                    @else bg-purple-100 text-purple-700
                                    @endif">
                                    {{ str_replace('_', ' ', ucfirst($withdrawal->method)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded-full
                                    @if($withdrawal->status === 'pending') bg-yellow-100 text-yellow-700
                                    @else bg-blue-100 text-blue-700
                                    @endif">
                                    {{ ucfirst($withdrawal->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $withdrawal->created_at->format('M d, Y') }}
                                <br><span class="text-xs">{{ $withdrawal->created_at->diffForHumans() }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('finance.payouts.show', $withdrawal) }}" class="p-2 text-gray-600 hover:text-green-600 hover:bg-green-50 rounded-lg" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($withdrawal->status === 'pending')
                                        <form action="{{ route('finance.payouts.approve', $withdrawal) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="p-2 text-green-600 hover:bg-green-50 rounded-lg" title="Approve">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                    @endif
                                    @if(in_array($withdrawal->status, ['pending', 'processing']))
                                        <form action="{{ route('finance.payouts.complete', $withdrawal) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg" title="Mark Complete">
                                                <i class="fas fa-check-double"></i>
                                            </button>
                                        </form>
                                        <button onclick="showRejectModal({{ $withdrawal->id }})" class="p-2 text-red-600 hover:bg-red-50 rounded-lg" title="Reject">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <i class="fas fa-check-circle text-5xl text-green-300 mb-4"></i>
                                <p class="text-gray-500">No pending withdrawals</p>
                            </td>
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

<!-- Reject Modal -->
<div id="rejectModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 w-full max-w-md">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Reject Withdrawal</h3>
        <form id="rejectForm" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Rejection Reason</label>
                <textarea name="reason" rows="3" class="w-full border rounded-lg p-3" required placeholder="Enter reason for rejection..."></textarea>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="hideRejectModal()" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Reject</button>
            </div>
        </form>
    </div>
</div>

<script>
function showRejectModal(id) {
    document.getElementById('rejectForm').action = '/finance/payouts/' + id + '/reject';
    document.getElementById('rejectModal').classList.remove('hidden');
    document.getElementById('rejectModal').classList.add('flex');
}

function hideRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
    document.getElementById('rejectModal').classList.remove('flex');
}
</script>
@endsection
