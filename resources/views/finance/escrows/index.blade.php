@extends('layouts.finance')

@section('title', 'Escrow Management')
@section('page-title', 'Escrow Management')
@section('page-description', 'Monitor and manage escrow funds')

@section('content')
<div class="space-y-6">
    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Funds Held</p>
                    <p class="text-2xl font-bold text-yellow-600">${{ number_format($stats['total_held'], 2) }}</p>
                    <p class="text-xs text-gray-500">{{ $stats['held_count'] }} escrows</p>
                </div>
                <i class="fas fa-lock text-yellow-300 text-3xl"></i>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Released</p>
                    <p class="text-2xl font-bold text-green-600">${{ number_format($stats['total_released'], 2) }}</p>
                    <p class="text-xs text-gray-500">{{ $stats['released_count'] }} escrows</p>
                </div>
                <i class="fas fa-unlock text-green-300 text-3xl"></i>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-red-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Refunded</p>
                    <p class="text-2xl font-bold text-red-600">${{ number_format($stats['total_refunded'], 2) }}</p>
                    <p class="text-xs text-gray-500">{{ $stats['refunded_count'] }} escrows</p>
                </div>
                <i class="fas fa-undo text-red-300 text-3xl"></i>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by order number..." class="w-full border rounded-lg px-4 py-2">
            </div>
            <select name="status" class="border rounded-lg px-4 py-2">
                <option value="">All Status</option>
                <option value="held" {{ request('status') === 'held' ? 'selected' : '' }}>Held</option>
                <option value="released" {{ request('status') === 'released' ? 'selected' : '' }}>Released</option>
                <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>Refunded</option>
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="border rounded-lg px-4 py-2">
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="border rounded-lg px-4 py-2">
            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                <i class="fas fa-search mr-2"></i>Filter
            </button>
            <a href="{{ route('finance.escrows.index') }}" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Clear</a>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Buyer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vendor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Release Date</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($escrows as $escrow)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <p class="font-mono font-medium text-gray-900">#{{ $escrow->order->order_number ?? 'N/A' }}</p>
                                <p class="text-xs text-gray-500">{{ $escrow->created_at->format('M d, Y') }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-medium text-gray-900">{{ $escrow->order->buyer->name ?? 'N/A' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-medium text-gray-900">{{ $escrow->order->vendor->user->name ?? 'N/A' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-bold text-gray-900">${{ number_format($escrow->amount, 2) }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded-full
                                    @if($escrow->status === 'held') bg-yellow-100 text-yellow-700
                                    @elseif($escrow->status === 'released') bg-green-100 text-green-700
                                    @else bg-red-100 text-red-700
                                    @endif">
                                    {{ ucfirst($escrow->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                @if($escrow->release_at)
                                    {{ $escrow->release_at->format('M d, Y') }}
                                    @if($escrow->status === 'held' && $escrow->release_at->isPast())
                                        <span class="text-red-600 text-xs block">Overdue</span>
                                    @elseif($escrow->status === 'held')
                                        <span class="text-gray-400 text-xs block">{{ $escrow->release_at->diffForHumans() }}</span>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('finance.escrows.show', $escrow) }}" class="p-2 text-gray-600 hover:text-green-600 hover:bg-green-50 rounded-lg" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($escrow->status === 'held')
                                        <form action="{{ route('finance.escrows.release', $escrow) }}" method="POST" class="inline" onsubmit="return confirm('Release funds to vendor?')">
                                            @csrf
                                            <button type="submit" class="p-2 text-green-600 hover:bg-green-50 rounded-lg" title="Release">
                                                <i class="fas fa-unlock"></i>
                                            </button>
                                        </form>
                                        <button onclick="showRefundModal({{ $escrow->id }})" class="p-2 text-red-600 hover:bg-red-50 rounded-lg" title="Refund">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">No escrows found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($escrows->hasPages())
            <div class="p-4 border-t">
                {{ $escrows->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Refund Modal -->
<div id="refundModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 w-full max-w-md">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Refund Escrow to Buyer</h3>
        <form id="refundForm" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Refund Reason</label>
                <textarea name="reason" rows="3" class="w-full border rounded-lg p-3" required placeholder="Enter reason for refund..."></textarea>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="hideRefundModal()" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Refund</button>
            </div>
        </form>
    </div>
</div>

<script>
function showRefundModal(id) {
    document.getElementById('refundForm').action = '/finance/escrows/' + id + '/refund';
    document.getElementById('refundModal').classList.remove('hidden');
    document.getElementById('refundModal').classList.add('flex');
}

function hideRefundModal() {
    document.getElementById('refundModal').classList.add('hidden');
    document.getElementById('refundModal').classList.remove('flex');
}
</script>
@endsection
