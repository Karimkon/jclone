@extends('layouts.finance')

@section('title', 'Escrow Details')
@section('page-title', 'Escrow Details')
@section('page-description', 'Order #' . ($escrow->order->order_number ?? 'N/A'))

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <a href="{{ route('finance.escrows.index') }}" class="inline-flex items-center text-gray-600 hover:text-green-600">
        <i class="fas fa-arrow-left mr-2"></i> Back to Escrows
    </a>

    <!-- Status Banner -->
    <div class="p-4 rounded-xl
        @if($escrow->status === 'held') bg-yellow-50 border border-yellow-200
        @elseif($escrow->status === 'released') bg-green-50 border border-green-200
        @else bg-red-50 border border-red-200
        @endif">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <i class="fas
                    @if($escrow->status === 'held') fa-lock text-yellow-600
                    @elseif($escrow->status === 'released') fa-unlock text-green-600
                    @else fa-undo text-red-600
                    @endif text-2xl mr-3"></i>
                <div>
                    <p class="font-semibold text-gray-900">Status: {{ ucfirst($escrow->status) }}</p>
                    <p class="text-sm text-gray-600">Created {{ $escrow->created_at->diffForHumans() }}</p>
                </div>
            </div>
            <div class="text-right">
                <p class="text-3xl font-bold text-gray-900">${{ number_format($escrow->amount, 2) }}</p>
                <p class="text-sm text-gray-500">Escrow Amount</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Order Info -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Order Information</h3>
            <dl class="space-y-3">
                <div class="flex justify-between">
                    <dt class="text-gray-600">Order Number:</dt>
                    <dd class="font-mono font-medium">#{{ $escrow->order->order_number ?? 'N/A' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-600">Order Status:</dt>
                    <dd><span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-700">{{ ucfirst($escrow->order->status ?? 'N/A') }}</span></dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-600">Order Total:</dt>
                    <dd class="font-medium">${{ number_format($escrow->order->total ?? 0, 2) }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-600">Commission (8%):</dt>
                    <dd class="font-medium text-blue-600">${{ number_format($escrow->order->platform_commission ?? 0, 2) }}</dd>
                </div>
                @if($escrow->release_at)
                <div class="flex justify-between">
                    <dt class="text-gray-600">Auto-Release Date:</dt>
                    <dd class="font-medium">{{ $escrow->release_at->format('M d, Y H:i') }}</dd>
                </div>
                @endif
            </dl>
        </div>

        <!-- Parties -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Parties</h3>
            <div class="space-y-4">
                <div class="p-3 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-500 uppercase mb-1">Buyer</p>
                    <p class="font-medium text-gray-900">{{ $escrow->order->buyer->name ?? 'N/A' }}</p>
                    <p class="text-sm text-gray-500">{{ $escrow->order->buyer->email ?? '' }}</p>
                </div>
                <div class="p-3 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-500 uppercase mb-1">Vendor</p>
                    <p class="font-medium text-gray-900">{{ $escrow->order->vendor->user->name ?? 'N/A' }}</p>
                    <p class="text-sm text-gray-500">{{ $escrow->order->vendor->business_name ?? '' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Items -->
    @if($escrow->order->items && $escrow->order->items->count() > 0)
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="font-semibold text-gray-900 mb-4">Order Items</h3>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Item</th>
                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500">Qty</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Price</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($escrow->order->items as $item)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $item->title }}</td>
                            <td class="px-4 py-3 text-sm text-center text-gray-600">{{ $item->quantity }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-600">${{ number_format($item->unit_price, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-right font-medium text-gray-900">${{ number_format($item->line_total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Actions -->
    @if($escrow->status === 'held')
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="font-semibold text-gray-900 mb-4">Actions</h3>
        <div class="flex flex-wrap gap-4">
            <form action="{{ route('finance.escrows.release', $escrow) }}" method="POST" onsubmit="return confirm('Release funds to vendor? This will credit the vendor\'s balance minus 8% commission.')">
                @csrf
                <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    <i class="fas fa-unlock mr-2"></i>Release to Vendor
                </button>
            </form>

            <button onclick="document.getElementById('refundForm').classList.toggle('hidden')" class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                <i class="fas fa-undo mr-2"></i>Refund to Buyer
            </button>

            <button onclick="document.getElementById('extendForm').classList.toggle('hidden')" class="px-6 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">
                <i class="fas fa-clock mr-2"></i>Extend Hold
            </button>
        </div>

        <!-- Refund Form -->
        <form id="refundForm" action="{{ route('finance.escrows.refund', $escrow) }}" method="POST" class="hidden mt-4 p-4 bg-red-50 rounded-lg">
            @csrf
            <label class="block text-sm font-medium text-gray-700 mb-2">Refund Reason</label>
            <textarea name="reason" rows="3" class="w-full border rounded-lg p-3 mb-3" required placeholder="Enter reason for refund..."></textarea>
            <button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                Confirm Refund
            </button>
        </form>

        <!-- Extend Form -->
        <form id="extendForm" action="{{ route('finance.escrows.extend', $escrow) }}" method="POST" class="hidden mt-4 p-4 bg-yellow-50 rounded-lg">
            @csrf
            <div class="grid grid-cols-2 gap-4 mb-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Extension Days</label>
                    <input type="number" name="days" min="1" max="30" value="7" class="w-full border rounded-lg p-3" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Reason (Optional)</label>
                    <input type="text" name="reason" class="w-full border rounded-lg p-3" placeholder="Reason for extension...">
                </div>
            </div>
            <button type="submit" class="px-6 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">
                Extend Hold Period
            </button>
        </form>
    </div>
    @endif
</div>
@endsection
