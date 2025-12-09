@extends('layouts.admin')

@section('title', 'Order Details - ' . $order->order_number)
@section('page-title', 'Order Details')
@section('page-description', 'View and manage order details')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 mb-2">Order #{{ $order->order_number }}</h1>
                <div class="flex items-center space-x-4">
                    <span class="px-3 py-1 rounded-full text-sm font-medium
                        @if($order->status == 'pending') bg-yellow-100 text-yellow-800
                        @elseif($order->status == 'confirmed') bg-blue-100 text-blue-800
                        @elseif($order->status == 'processing') bg-purple-100 text-purple-800
                        @elseif($order->status == 'shipped') bg-indigo-100 text-indigo-800
                        @elseif($order->status == 'delivered') bg-green-100 text-green-800
                        @elseif($order->status == 'cancelled') bg-red-100 text-red-800
                        @else bg-gray-100 text-gray-800 @endif">
                        {{ ucfirst($order->status) }}
                    </span>
                    <span class="text-gray-600">{{ $order->created_at->format('F d, Y h:i A') }}</span>
                </div>
            </div>
            
            <div class="mt-4 md:mt-0">
                <p class="text-3xl font-bold text-primary">${{ number_format($order->total, 2) }}</p>
            </div>
        </div>
    </div>

    <!-- Order Actions -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-lg font-bold text-gray-800 mb-4">Update Status</h2>
        
        <form action="{{ route('admin.orders.update-status', $order) }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" required 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="confirmed" {{ $order->status == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="processing" {{ $order->status == 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="shipped" {{ $order->status == 'shipped' ? 'selected' : '' }}>Shipped</option>
                        <option value="delivered" {{ $order->status == 'delivered' ? 'selected' : '' }}>Delivered</option>
                        <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
                    <input type="text" name="notes" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                           placeholder="Add notes about status change">
                </div>
                
                <div class="flex items-end">
                    <button type="submit" 
                            class="w-full px-6 py-2 bg-primary text-white rounded-lg hover:bg-indigo-700 font-medium">
                        <i class="fas fa-save mr-2"></i> Update Status
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Order Items -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Order Items</h2>
                
                <div class="space-y-4">
                    @foreach($order->items as $item)
                    <div class="flex items-start border-b pb-4">
                        @if($item->listing && $item->listing->images->first())
                        <div class="w-20 h-20 flex-shrink-0 mr-4">
                            <img src="{{ asset('storage/' . $item->listing->images->first()->path) }}" 
                                 alt="{{ $item->title }}" 
                                 class="w-full h-full object-cover rounded-lg">
                        </div>
                        @endif
                        
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-800">{{ $item->title }}</h3>
                            <p class="text-sm text-gray-600">Qty: {{ $item->quantity }}</p>
                            <p class="text-sm text-gray-600">Unit Price: ${{ number_format($item->unit_price, 2) }}</p>
                            @if($item->listing)
                            <p class="text-sm text-gray-600">SKU: {{ $item->listing->sku ?? 'N/A' }}</p>
                            @endif
                        </div>
                        
                        <div class="text-right">
                            <p class="font-bold text-gray-800">${{ number_format($item->line_total, 2) }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Order Information -->
        <div class="space-y-6">
            <!-- Customer Information -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Customer Information</h2>
                
                <div class="space-y-2">
                    <p class="font-medium">{{ $order->buyer->name }}</p>
                    <p class="text-gray-600">{{ $order->buyer->email }}</p>
                    <p class="text-gray-600">{{ $order->buyer->phone }}</p>
                    
                    @php
                        $meta = json_decode($order->meta, true) ?? [];
                    @endphp
                    
                    <div class="mt-4 pt-4 border-t">
                        <h3 class="font-semibold text-gray-700 mb-2">Shipping Address</h3>
                        <p class="text-gray-600">{{ $meta['shipping_address'] ?? 'N/A' }}</p>
                        <p class="text-gray-600">{{ $meta['shipping_city'] ?? '' }}, {{ $meta['shipping_country'] ?? '' }}</p>
                        <p class="text-gray-600">{{ $meta['shipping_postal_code'] ?? '' }}</p>
                    </div>
                </div>
            </div>

            <!-- Vendor Information -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Vendor Information</h2>
                
                <div class="space-y-2">
                    <p class="font-medium">{{ $order->vendorProfile->business_name ?? 'N/A' }}</p>
                    <p class="text-gray-600">{{ $order->vendorProfile->user->name ?? '' }}</p>
                    <p class="text-gray-600">{{ $order->vendorProfile->user->email ?? '' }}</p>
                    <p class="text-gray-600">{{ $order->vendorProfile->user->phone ?? '' }}</p>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Order Summary</h2>
                
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Subtotal</span>
                        <span>${{ number_format($order->subtotal, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Shipping</span>
                        <span>${{ number_format($order->shipping, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Taxes</span>
                        <span>${{ number_format($order->taxes, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Platform Commission</span>
                        <span>${{ number_format($order->platform_commission, 2) }}</span>
                    </div>
                    <div class="pt-2 border-t">
                        <div class="flex justify-between font-bold text-lg">
                            <span>Total</span>
                            <span class="text-primary">${{ number_format($order->total, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Information -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Payment Information</h2>
        
        @php
            $meta = json_decode($order->meta, true) ?? [];
        @endphp
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="font-semibold text-gray-700 mb-2">Payment Method</h3>
                <p class="text-gray-800">{{ ucfirst(str_replace('_', ' ', $meta['payment_method'] ?? 'N/A')) }}</p>
                <p class="text-sm text-gray-600 mt-1">
                    @if(($meta['payment_method'] ?? '') == 'cash_on_delivery')
                    Payment due on delivery
                    @elseif(($meta['payment_method'] ?? '') == 'wallet')
                    Paid via wallet
                    @endif
                </p>
            </div>
            
            @if($order->payments->count() > 0)
            <div>
                <h3 class="font-semibold text-gray-700 mb-2">Payment History</h3>
                <div class="space-y-2">
                    @foreach($order->payments as $payment)
                    <div class="border-b pb-2">
                        <p class="text-gray-800">${{ number_format($payment->amount, 2) }} - {{ ucfirst($payment->status) }}</p>
                        <p class="text-sm text-gray-600">{{ $payment->created_at->format('M d, Y h:i A') }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@if(session('success'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        alert('{{ session('success') }}');
    });
</script>
@endif

@if(session('error'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        alert('{{ session('error') }}');
    });
</script>
@endif
@endsection