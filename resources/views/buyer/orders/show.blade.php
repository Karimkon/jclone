@extends('layouts.buyer')

@section('title', 'Order Details - ' . config('app.name'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <a href="{{ route('buyer.orders.index') }}" class="text-primary hover:text-indigo-700 font-medium">
            <i class="fas fa-arrow-left mr-2"></i> Back to Orders
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6 rounded-r-lg">
        <div class="flex items-center">
            <i class="fas fa-check-circle text-green-400 mr-3"></i>
            <p class="text-green-700">{{ session('success') }}</p>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6 rounded-r-lg">
        <div class="flex items-center">
            <i class="fas fa-exclamation-circle text-red-400 mr-3"></i>
            <p class="text-red-700">{{ session('error') }}</p>
        </div>
    </div>
    @endif

    <!-- Review Prompt (Show if order is delivered and has items not yet reviewed) -->
    @if($order->status == 'delivered')
        @php
            $unreviewedItems = $order->items->filter(function($item) {
                return !\App\Models\Review::where('user_id', auth()->id())
                    ->where('order_item_id', $item->id)
                    ->exists();
            });
        @endphp
        
        @if($unreviewedItems->count() > 0)
        <div class="bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-2xl p-6 mb-6">
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 bg-amber-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-star text-amber-500 text-2xl"></i>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-gray-800 mb-1">Share Your Experience!</h3>
                    <p class="text-gray-600 mb-4">Help other buyers by reviewing the products you received.</p>
                    
                    <div class="flex flex-wrap gap-3">
                        @foreach($unreviewedItems as $item)
                        <a href="{{ route('buyer.reviews.create', ['order_item_id' => $item->id]) }}" 
                           class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-amber-300 rounded-lg text-amber-700 font-medium hover:bg-amber-50 transition">
                            <i class="fas fa-pen"></i>
                            Review "{{ Str::limit($item->title, 25) }}"
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif
    @endif

    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 mb-2">Order #{{ $order->order_number }}</h1>
                <div class="flex items-center space-x-4">
                    <span class="px-3 py-1 rounded-full text-sm font-medium
                        @if($order->status == 'pending') bg-yellow-100 text-yellow-800
                        @elseif($order->status == 'confirmed') bg-blue-100 text-blue-800
                        @elseif($order->status == 'shipped') bg-purple-100 text-purple-800
                        @elseif($order->status == 'delivered') bg-green-100 text-green-800
                        @elseif($order->status == 'cancelled') bg-red-100 text-red-800
                        @else bg-gray-100 text-gray-800 @endif">
                        {{ ucfirst($order->status) }}
                    </span>
                    <span class="text-gray-600">{{ $order->created_at->format('F d, Y') }}</span>
                </div>
            </div>
            
            <div class="mt-4 md:mt-0">
                <p class="text-2xl font-bold text-primary">${{ number_format($order->total, 2) }}</p>
            </div>
        </div>

        <!-- Order Actions -->
        @if($order->status == 'pending' || $order->status == 'confirmed')
        <div class="flex space-x-3 mb-6">
            <form action="{{ route('buyer.orders.cancel', $order->id) }}" method="POST" 
                  onsubmit="return confirm('Are you sure you want to cancel this order?')">
                @csrf
                <button type="submit" class="px-4 py-2 border border-red-600 text-red-600 rounded-lg hover:bg-red-50">
                    <i class="fas fa-times mr-2"></i> Cancel Order
                </button>
            </form>
        </div>
        @endif

        @if($order->status == 'shipped')
        <div class="flex space-x-3 mb-6">
            <form action="{{ route('buyer.orders.confirm-delivery', $order->id) }}" method="POST"
                  onsubmit="return confirm('Confirm that you have received your order?')">
                @csrf
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-indigo-700">
                    <i class="fas fa-check mr-2"></i> Confirm Delivery
                </button>
            </form>
        </div>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Order Items -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Order Items</h2>
                
                <div class="space-y-4">
                    @foreach($order->items as $item)
                    @php
                        $hasReview = \App\Models\Review::where('user_id', auth()->id())
                            ->where('order_item_id', $item->id)
                            ->exists();
                    @endphp
                    <div class="flex items-start border-b pb-4">
                        @if($item->listing && $item->listing->images->first())
                        <div class="w-20 h-20 flex-shrink-0 mr-4">
                            <img src="{{ asset('storage/' . $item->listing->images->first()->path) }}" 
                                 alt="{{ $item->title }}" 
                                 class="w-full h-full object-cover rounded-lg">
                        </div>
                        @else
                        <div class="w-20 h-20 flex-shrink-0 mr-4 bg-gray-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-image text-gray-300"></i>
                        </div>
                        @endif
                        
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-800">{{ $item->title }}</h3>
                            <p class="text-sm text-gray-600">Qty: {{ $item->quantity }}</p>
                            <p class="text-sm text-gray-600">Price: ${{ number_format($item->unit_price, 2) }}</p>
                            
                            <!-- Review Status -->
                            @if($order->status == 'delivered')
                                @if($hasReview)
                                <span class="inline-flex items-center gap-1 mt-2 text-sm text-green-600">
                                    <i class="fas fa-check-circle"></i>
                                    Reviewed
                                </span>
                                @else
                                <a href="{{ route('buyer.reviews.create', ['order_item_id' => $item->id]) }}" 
                                   class="inline-flex items-center gap-1 mt-2 text-sm text-primary hover:underline">
                                    <i class="fas fa-star"></i>
                                    Write Review
                                </a>
                                @endif
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

        <!-- Order Summary -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Order Summary</h2>
                
                <div class="space-y-2 mb-4">
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
                    <div class="pt-2 border-t">
                        <div class="flex justify-between font-bold text-lg">
                            <span>Total</span>
                            <span class="text-primary">${{ number_format($order->total, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Shipping Information -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Shipping Information</h2>
                
                @php
                    $meta = json_decode($order->meta, true) ?? [];
                @endphp
                
                <div class="space-y-2">
                    <p class="font-medium">{{ $meta['shipping_address'] ?? 'N/A' }}</p>
                    <p class="text-gray-600">{{ $meta['shipping_city'] ?? '' }}, {{ $meta['shipping_country'] ?? '' }}</p>
                    <p class="text-gray-600">{{ $meta['shipping_postal_code'] ?? '' }}</p>
                    
                    @if(isset($meta['payment_method']))
                    <div class="mt-4 pt-4 border-t">
                        <p class="text-gray-600">Payment Method:</p>
                        <p class="font-medium">{{ ucfirst(str_replace('_', ' ', $meta['payment_method'])) }}</p>
                    </div>
                    @endif
                </div>
            </div>
            
            <!-- Vendor Info -->
            @if($order->vendorProfile)
            <div class="bg-white rounded-xl shadow-sm p-6 mt-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Vendor</h2>
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-gradient-to-br from-primary to-purple-600 rounded-full flex items-center justify-center text-white font-bold">
                        {{ strtoupper(substr($order->vendorProfile->business_name ?? 'V', 0, 1)) }}
                    </div>
                    <div>
                        <p class="font-medium text-gray-800">{{ $order->vendorProfile->business_name ?? 'Vendor' }}</p>
                        <p class="text-sm text-gray-500">{{ ucfirst($order->vendorProfile->vendor_type ?? 'Vendor') }}</p>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection