@extends('layouts.vendor')

@section('title', 'Order Details - ' . $order->order_number)

@section('content')
<div class="container-fluid">
    <!-- Back Button -->
    <div class="mb-6">
        <a href="{{ route('vendor.orders.index') }}" class="inline-flex items-center text-primary hover:text-indigo-700">
            <i class="fas fa-arrow-left mr-2"></i> Back to Orders
        </a>
    </div>

    <!-- Order Header -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Order #{{ $order->order_number }}</h1>
                <div class="flex items-center space-x-4">
                    <span class="px-3 py-1 text-sm font-medium rounded-full 
                        @if($order->status == 'pending') bg-yellow-100 text-yellow-800
                        @elseif($order->status == 'paid') bg-blue-100 text-blue-800
                        @elseif($order->status == 'processing') bg-purple-100 text-purple-800
                        @elseif($order->status == 'shipped') bg-indigo-100 text-indigo-800
                        @elseif($order->status == 'delivered') bg-green-100 text-green-800
                        @else bg-gray-100 text-gray-800 @endif">
                        {{ ucfirst($order->status) }}
                    </span>
                    <span class="text-gray-600">
                        <i class="far fa-clock mr-1"></i> {{ $order->created_at->format('F d, Y h:i A') }}
                    </span>
                </div>
            </div>
            
            <div class="mt-4 md:mt-0 space-x-3">
                <!-- Packing Slip -->
                <a href="{{ route('vendor.orders.packing-slip', $order) }}" 
                   target="_blank"
                   class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                    <i class="fas fa-print mr-2"></i> Packing Slip
                </a>
                
                <!-- Status Update -->
                @if(in_array($order->status, ['paid', 'processing']))
                <button onclick="showShipModal()" 
                        class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-indigo-700">
                    <i class="fas fa-shipping-fast mr-2"></i> Mark as Shipped
                </button>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: Order Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Order Items -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-bold text-gray-900">Order Items</h2>
                </div>
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($order->items as $item)
                                <tr>
                                    <td class="px-4 py-4">
                                        <div class="flex items-center">
                                            @if($item->listing->images->first())
                                            <div class="flex-shrink-0 h-16 w-16 mr-4">
                                                <img src="{{ asset('storage/' . $item->listing->images->first()->path) }}" 
                                                     alt="{{ $item->listing->title }}" 
                                                     class="h-16 w-16 object-cover rounded-lg">
                                            </div>
                                            @endif
                                            <div>
                                                <div class="font-medium text-gray-900">
                                                    {{ $item->listing->title }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    SKU: {{ $item->listing->sku }}
                                                </div>
                                                @if($item->listing->attributes)
                                                <div class="text-xs text-gray-500">
                                                    @foreach($item->listing->attributes as $key => $value)
                                                    {{ ucfirst($key) }}: {{ $value }}@if(!$loop->last), @endif
                                                    @endforeach
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-900">
                                        ${{ number_format($item->unit_price, 2) }}
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-900">
                                        {{ $item->quantity }}
                                    </td>
                                    <td class="px-4 py-4 text-sm font-medium text-gray-900">
                                        ${{ number_format($item->line_total, 2) }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Shipping Information -->
            @if($order->meta && isset($order->meta['shipping_address']))
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-bold text-gray-900">Shipping Information</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="font-medium text-gray-700 mb-2">Shipping Address</h3>
                            <p class="text-gray-900">{{ $order->meta['shipping_address'] }}</p>
                        </div>
                        
                        @if($order->meta && isset($order->meta['shipping_info']))
                        <div>
                            <h3 class="font-medium text-gray-700 mb-2">Shipping Details</h3>
                            <p class="text-gray-900">
                                <strong>Carrier:</strong> {{ $order->meta['shipping_info']['carrier'] ?? 'N/A' }}<br>
                                <strong>Tracking:</strong> {{ $order->meta['shipping_info']['tracking_number'] ?? 'N/A' }}<br>
                                <strong>Estimated Delivery:</strong> 
                                @if(isset($order->meta['shipping_info']['estimated_delivery']))
                                    {{ \Carbon\Carbon::parse($order->meta['shipping_info']['estimated_delivery'])->format('M d, Y') }}
                                @else
                                    N/A
                                @endif
                            </p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column: Summary & Actions -->
        <div class="space-y-6">
            <!-- Order Summary -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-bold text-gray-900">Order Summary</h2>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Subtotal</span>
                            <span class="font-medium">${{ number_format($order->subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Shipping</span>
                            <span class="font-medium">${{ number_format($order->shipping, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Taxes</span>
                            <span class="font-medium">${{ number_format($order->taxes, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Platform Commission</span>
                            <span class="font-medium">${{ number_format($order->platform_commission, 2) }}</span>
                        </div>
                        <div class="border-t pt-3">
                            <div class="flex justify-between text-lg font-bold">
                                <span>Total</span>
                                <span class="text-primary">${{ number_format($order->total, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Information -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-bold text-gray-900">Customer</h2>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        <div>
                            <h3 class="font-medium text-gray-700 mb-1">Name</h3>
                            <p class="text-gray-900">{{ $order->buyer->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <h3 class="font-medium text-gray-700 mb-1">Email</h3>
                            <p class="text-gray-900">{{ $order->buyer->email ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <h3 class="font-medium text-gray-700 mb-1">Phone</h3>
                            <p class="text-gray-900">{{ $order->buyer->phone ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-bold text-gray-900">Actions</h2>
                </div>
                <div class="p-6 space-y-3">
                    <!-- Status Update -->
                    <form action="{{ route('vendor.orders.updateStatus', $order) }}" method="POST" class="mb-4">
                        @csrf
                        <label class="block text-sm font-medium text-gray-700 mb-2">Update Status</label>
                        <div class="flex space-x-2">
                            <select name="status" class="flex-1 border border-gray-300 rounded-lg p-2">
                                <option value="processing" {{ $order->status == 'processing' ? 'selected' : '' }}>Processing</option>
                                <option value="shipped" {{ $order->status == 'shipped' ? 'selected' : '' }}>Shipped</option>
                                <option value="delivered" {{ $order->status == 'delivered' ? 'selected' : '' }}>Delivered</option>
                            </select>
                            <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-indigo-700">
                                Update
                            </button>
                        </div>
                    </form>

                    <!-- Cancel Request -->
                    @if(in_array($order->status, ['pending', 'paid']))
                    <button onclick="showCancelModal()" 
                            class="w-full px-4 py-2 border border-red-300 text-red-600 rounded-lg hover:bg-red-50">
                        <i class="fas fa-times mr-2"></i> Request Cancellation
                    </button>
                    @endif

                    <!-- Message Customer -->
                    <button class="w-full px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                        <i class="fas fa-envelope mr-2"></i> Message Customer
                    </button>
                </div>
            </div>

            <!-- Payment Status -->
            @if($order->payments->count() > 0)
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-bold text-gray-900">Payment Status</h2>
                </div>
                <div class="p-6">
                    @foreach($order->payments as $payment)
                    <div class="mb-3 last:mb-0">
                        <div class="flex justify-between">
                            <span class="font-medium">{{ ucfirst($payment->method) }}</span>
                            <span class="{{ $payment->status == 'completed' ? 'text-green-600' : 'text-yellow-600' }}">
                                {{ ucfirst($payment->status) }}
                            </span>
                        </div>
                        <div class="text-sm text-gray-500">
                            ${{ number_format($payment->amount, 2) }} • {{ $payment->created_at->format('M d, Y') }}
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
    <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4">
        <h3 class="text-lg font-bold text-gray-900 mb-2">Mark Order as Shipped</h3>
        <p class="text-gray-600 mb-4">Order: {{ $order->order_number }}</p>
        
        <form action="{{ route('vendor.orders.markShipped', $order) }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-gray-700 mb-2">Tracking Number *</label>
                <input type="text" name="tracking_number" required 
                       class="w-full border border-gray-300 rounded-lg p-2"
                       placeholder="Enter tracking number">
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 mb-2">Carrier *</label>
                <select name="carrier" required class="w-full border border-gray-300 rounded-lg p-2">
                    <option value="">Select Carrier</option>
                    <option value="dhl">DHL</option>
                    <option value="fedex">FedEx</option>
                    <option value="ups">UPS</option>
                    <option value="usps">USPS</option>
                    <option value="other">Other</option>
                </select>
            </div>
            
            <div class="mb-6">
                <label class="block text-gray-700 mb-2">Estimated Delivery Date</label>
                <input type="date" name="estimated_delivery" 
                       class="w-full border border-gray-300 rounded-lg p-2">
            </div>
            
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeShipModal()"
                        class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit"
                        class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                    Mark as Shipped
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Cancel Modal -->
<div id="cancelModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4">
        <h3 class="text-lg font-bold text-gray-900 mb-2">Request Order Cancellation</h3>
        <p class="text-gray-600 mb-4">Order: {{ $order->order_number }}</p>
        
        <form action="{{ route('vendor.orders.requestCancel', $order) }}" method="POST">
            @csrf
            <div class="mb-6">
                <label class="block text-gray-700 mb-2">Reason for Cancellation *</label>
                <textarea name="reason" rows="4" required
                          class="w-full border border-gray-300 rounded-lg p-2"
                          placeholder="Explain why you need to cancel this order..."></textarea>
            </div>
            
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeCancelModal()"
                        class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit"
                        class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
                    Submit Request
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
    
    function showCancelModal() {
        document.getElementById('cancelModal').classList.remove('hidden');
    }
    
    function closeCancelModal() {
        document.getElementById('cancelModal').classList.add('hidden');
    }
    
    // Close modals when clicking outside
    document.getElementById('shipModal').addEventListener('click', function(e) {
        if (e.target === this) closeShipModal();
    });
    
    document.getElementById('cancelModal').addEventListener('click', function(e) {
        if (e.target === this) closeCancelModal();
    });
</script>
@endsection