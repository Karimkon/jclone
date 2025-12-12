@extends('layouts.vendor')

@section('title', 'Order Details - ' . $order->order_number)

@section('content')
<div class="container-fluid px-4">
    <!-- Back Button & Header -->
    <div class="flex items-center justify-between mb-6">
        <a href="{{ route('vendor.orders.index') }}" class="inline-flex items-center text-primary hover:text-indigo-700">
            <i class="fas fa-arrow-left mr-2"></i> Back to Orders
        </a>
        <div class="flex items-center space-x-3">
            <!-- Packing Slip -->
            <a href="{{ route('vendor.orders.packingSlip', $order) }}" 
               target="_blank"
               class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 text-sm">
                <i class="fas fa-print mr-2"></i> Packing Slip
            </a>
            
            <!-- Quick Actions -->
            @if(in_array($order->status, ['paid', 'processing']))
            <button onclick="showShipModal()" 
                    class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-indigo-700 text-sm">
                <i class="fas fa-shipping-fast mr-2"></i> Mark as Shipped
            </button>
            @endif
        </div>
    </div>

    <!-- Compact Order Header -->
    <div class="bg-white rounded-lg shadow p-4 mb-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900 mb-1">Order #{{ $order->order_number }}</h1>
                <div class="flex items-center space-x-2">
                    <span class="px-2 py-1 text-xs font-medium rounded-full 
                        @if($order->status == 'pending') bg-yellow-100 text-yellow-800
                        @elseif($order->status == 'paid') bg-blue-100 text-blue-800
                        @elseif($order->status == 'processing') bg-purple-100 text-purple-800
                        @elseif($order->status == 'shipped') bg-indigo-100 text-indigo-800
                        @elseif($order->status == 'delivered') bg-green-100 text-green-800
                        @else bg-gray-100 text-gray-800 @endif">
                        {{ ucfirst($order->status) }}
                    </span>
                    <span class="text-xs text-gray-500">
                        <i class="far fa-clock mr-1"></i> {{ $order->created_at->format('M d, Y') }}
                    </span>
                </div>
            </div>
            
            <div class="text-center">
                <div class="text-sm text-gray-600">Customer</div>
                <div class="font-medium">{{ $order->buyer->name ?? 'N/A' }}</div>
                <div class="text-xs text-gray-500">{{ $order->buyer->email ?? '' }}</div>
            </div>
            
            <div class="text-right">
                <div class="text-sm text-gray-600">Order Total</div>
                <div class="text-xl font-bold text-primary">UGX {{ number_format($order->total, 2) }}</div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- Left Column: Compact Order Details -->
        <div class="lg:col-span-2 space-y-4">
            <!-- Order Items (Compact) -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-4 py-3 border-b border-gray-200">
                    <h2 class="font-bold text-gray-900">Order Items ({{ $order->items->count() }})</h2>
                </div>
                <div class="p-3">
                    <div class="space-y-3">
                        @foreach($order->items as $item)
                        <div class="flex items-center justify-between border-b border-gray-100 pb-3 last:border-0 last:pb-0">
                            <div class="flex items-center space-x-3">
                                @if($item->listing->images->first())
                                <div class="flex-shrink-0 h-12 w-12">
                                    <img src="{{ asset('storage/' . $item->listing->images->first()->path) }}" 
                                         alt="{{ $item->listing->title }}" 
                                         class="h-12 w-12 object-cover rounded">
                                </div>
                                @endif
                                <div>
                                    <div class="font-medium text-sm text-gray-900">{{ $item->listing->title }}</div>
                                    <div class="text-xs text-gray-500">SKU: {{ $item->listing->sku }}</div>
                                    <div class="text-xs text-gray-500">Qty: {{ $item->quantity }}</div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-medium text-gray-900">${{ number_format($item->line_total, 2) }}</div>
                                <div class="text-xs text-gray-500">${{ number_format($item->unit_price, 2) }} each</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Shipping Information (Compact) -->
            @if($order->meta && isset($order->meta['shipping_address']))
            <div class="bg-white rounded-lg shadow">
                <div class="px-4 py-3 border-b border-gray-200">
                    <h2 class="font-bold text-gray-900">Shipping Information</h2>
                </div>
                <div class="p-3">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <div class="text-xs text-gray-600 mb-1">Shipping Address</div>
                            <div class="text-sm">{{ $order->meta['shipping_address'] }}</div>
                        </div>
                        
                        @if($order->meta && isset($order->meta['shipping_info']))
                        <div>
                            <div class="text-xs text-gray-600 mb-1">Shipping Details</div>
                            <div class="text-sm">
                                <div><span class="font-medium">Carrier:</span> {{ $order->meta['shipping_info']['carrier'] ?? 'N/A' }}</div>
                                <div><span class="font-medium">Tracking:</span> {{ $order->meta['shipping_info']['tracking_number'] ?? 'N/A' }}</div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column: Summary & Actions -->
        <div class="space-y-4">
            <!-- Order Summary (Compact) -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-4 py-3 border-b border-gray-200">
                    <h2 class="font-bold text-gray-900">Order Summary</h2>
                </div>
                <div class="p-3">
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Subtotal</span>
                            <span>UGX {{ number_format($order->subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Shipping</span>
                            <span>UGX {{ number_format($order->shipping, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Taxes</span>
                            <span>${{ number_format($order->taxes, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Platform Fee</span>
                            <span>UGX {{ number_format($order->platform_commission, 2) }}</span>
                        </div>
                        <div class="border-t pt-2 mt-2">
                            <div class="flex justify-between font-bold">
                                <span>Total</span>
                                <span class="text-primary">UGX {{ number_format($order->total, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status Update (Enhanced) -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-4 py-3 border-b border-gray-200">
                    <h2 class="font-bold text-gray-900">Update Order Status</h2>
                </div>
                <div class="p-3">
                    <form action="{{ route('vendor.orders.updateStatus', $order) }}" method="POST" id="statusForm">
                        @csrf
                        <div class="space-y-3">
                            <!-- Status Options -->
                            <div class="grid grid-cols-2 gap-2">
                                @php
                                    $statusOptions = [
                                        'processing' => ['label' => 'Processing', 'color' => 'bg-purple-100 text-purple-800', 'icon' => 'fa-cog'],
                                        'shipped' => ['label' => 'Shipped', 'color' => 'bg-indigo-100 text-indigo-800', 'icon' => 'fa-shipping-fast'],
                                        'delivered' => ['label' => 'Delivered', 'color' => 'bg-green-100 text-green-800', 'icon' => 'fa-check-circle'],
                                        'cancelled' => ['label' => 'Cancel', 'color' => 'bg-red-100 text-red-800', 'icon' => 'fa-times'],
                                    ];
                                    
                                    // Determine next possible statuses
                                    $currentStatus = $order->status;
                                    $nextStatuses = [];
                                    
                                    if ($currentStatus === 'processing') {
                                        $nextStatuses = ['shipped', 'cancelled'];
                                    } elseif ($currentStatus === 'shipped') {
                                        $nextStatuses = ['delivered'];
                                    } elseif ($currentStatus === 'delivered') {
                                        $nextStatuses = [];
                                    } else {
                                        $nextStatuses = ['processing', 'shipped', 'delivered', 'cancelled'];
                                    }
                                @endphp
                                
                                @foreach($statusOptions as $status => $option)
                                    @if(in_array($status, $nextStatuses) || $status === $currentStatus)
                                    <button type="button" 
                                            onclick="setStatus('{{ $status }}')"
                                            class="status-btn flex items-center justify-center p-3 rounded-lg border {{ $status === $currentStatus ? 'border-primary bg-blue-50' : 'border-gray-200 hover:bg-gray-50' }}"
                                            data-status="{{ $status }}">
                                        <i class="fas {{ $option['icon'] }} mr-2 {{ $status === $currentStatus ? 'text-primary' : 'text-gray-500' }}"></i>
                                        <span class="font-medium">{{ $option['label'] }}</span>
                                    </button>
                                    @endif
                                @endforeach
                            </div>
                            
                            <!-- Hidden Status Input -->
                            <input type="hidden" name="status" id="selectedStatus" value="{{ $order->status }}">
                            
                            <!-- Submit Button -->
                            <button type="submit" 
                                    id="submitBtn"
                                    class="w-full px-4 py-2 bg-primary text-white rounded-lg hover:bg-indigo-700 font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                                    {{ $order->status === 'delivered' ? 'disabled' : '' }}>
                                <i class="fas fa-sync-alt mr-2"></i>
                                Update Status
                            </button>
                            
                            <!-- Status Info -->
                            <div class="text-xs text-gray-500 text-center">
                                @if($order->status === 'shipped')
                                <i class="fas fa-info-circle mr-1"></i> Mark as "Delivered" when customer receives package
                                @elseif($order->status === 'delivered')
                                <i class="fas fa-check-circle mr-1 text-green-500"></i> Order completed
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-4 py-3 border-b border-gray-200">
                    <h2 class="font-bold text-gray-900">Quick Actions</h2>
                </div>
                <div class="p-3">
                    <div class="space-y-2">
                        <!-- Message Customer -->
                        <button class="w-full px-3 py-2 text-left border border-gray-200 rounded-lg hover:bg-gray-50 text-sm">
                            <i class="fas fa-envelope mr-2 text-gray-500"></i> Message Customer
                        </button>
                        
                        <!-- View Chat -->
                        <button class="w-full px-3 py-2 text-left border border-gray-200 rounded-lg hover:bg-gray-50 text-sm">
                            <i class="fas fa-comments mr-2 text-gray-500"></i> View Conversation
                        </button>
                        
                        <!-- Generate Invoice -->
                        <a href="#" class="block w-full px-3 py-2 text-left border border-gray-200 rounded-lg hover:bg-gray-50 text-sm">
                            <i class="fas fa-file-invoice mr-2 text-gray-500"></i> Generate Invoice
                        </a>
                    </div>
                </div>
            </div>

            <!-- Payment Status -->
            @if($order->payments->count() > 0)
            <div class="bg-white rounded-lg shadow">
                <div class="px-4 py-3 border-b border-gray-200">
                    <h2 class="font-bold text-gray-900">Payment</h2>
                </div>
                <div class="p-3">
                    @foreach($order->payments as $payment)
                    <div class="flex items-center justify-between mb-2 last:mb-0">
                        <div>
                            <div class="font-medium text-sm">{{ ucfirst($payment->method) }}</div>
                            <div class="text-xs text-gray-500">{{ $payment->created_at->format('M d') }}</div>
                        </div>
                        <div class="text-right">
                            <div class="font-medium text-sm">UGX {{ number_format($payment->amount, 2) }}</div>
                            <div class="text-xs {{ $payment->status == 'completed' ? 'text-green-600' : 'text-yellow-600' }}">
                                {{ ucfirst($payment->status) }}
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Ship Modal -->
<div id="shipModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-bold text-gray-900 mb-2">Mark Order as Shipped</h3>
        <p class="text-gray-600 mb-4 text-sm">Order: {{ $order->order_number }}</p>
        
        <form action="{{ route('vendor.orders.markShipped', $order) }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm text-gray-700 mb-1">Tracking Number *</label>
                    <input type="text" name="tracking_number" required 
                           class="w-full border border-gray-300 rounded-lg p-2 text-sm"
                           placeholder="Enter tracking number">
                </div>
                
                <div>
                    <label class="block text-sm text-gray-700 mb-1">Carrier *</label>
                    <select name="carrier" required class="w-full border border-gray-300 rounded-lg p-2 text-sm">
                        <option value="">Select Carrier</option>
                        <option value="dhl">DHL</option>
                        <option value="fedex">FedEx</option>
                        <option value="ups">UPS</option>
                        <option value="usps">USPS</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm text-gray-700 mb-1">Estimated Delivery Date</label>
                    <input type="date" name="estimated_delivery" 
                           class="w-full border border-gray-300 rounded-lg p-2 text-sm"
                           min="{{ now()->format('Y-m-d') }}">
                </div>
            </div>
            
            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" onclick="closeShipModal()"
                        class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 text-sm">
                    Cancel
                </button>
                <button type="submit"
                        class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-indigo-700 text-sm">
                    Mark as Shipped
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function showShipModal() {
        document.getElementById('shipModal').classList.remove('hidden');
    }
    
    function closeShipModal() {
        document.getElementById('shipModal').classList.add('hidden');
    }
    
    // Status management
    function setStatus(status) {
        document.getElementById('selectedStatus').value = status;
        
        // Update button styles
        document.querySelectorAll('.status-btn').forEach(btn => {
            if (btn.dataset.status === status) {
                btn.classList.add('border-primary', 'bg-blue-50');
                btn.querySelector('i').classList.add('text-primary');
                btn.querySelector('i').classList.remove('text-gray-500');
            } else {
                btn.classList.remove('border-primary', 'bg-blue-50');
                btn.querySelector('i').classList.remove('text-primary');
                btn.querySelector('i').classList.add('text-gray-500');
            }
        });
        
        // Update submit button
        const submitBtn = document.getElementById('submitBtn');
        if (status === '{{ $order->status }}') {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Current Status';
        } else {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-sync-alt mr-2"></i> Update to ' + 
                (status === 'processing' ? 'Processing' : 
                 status === 'shipped' ? 'Shipped' : 
                 status === 'delivered' ? 'Delivered' : 'Cancelled');
        }
    }
    
    // Initialize status
    document.addEventListener('DOMContentLoaded', function() {
        setStatus('{{ $order->status }}');
    });
    
    // Close modals when clicking outside
    document.getElementById('shipModal').addEventListener('click', function(e) {
        if (e.target === this) closeShipModal();
    });
</script>

<style>
    .status-btn {
        transition: all 0.2s ease;
    }
    
    .status-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
</style>
@endsection