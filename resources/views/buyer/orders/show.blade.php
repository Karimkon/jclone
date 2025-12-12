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
                <p class="text-2xl font-bold text-primary"> UGX {{ number_format($order->total, 2) }}</p>
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

        {{-- Add this section after the Order Actions section --}}
@if(in_array($order->status, ['pending', 'payment_pending']))
<!-- Wallet Payment Option -->
<div class="mt-6 p-6 bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl border border-green-200">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-lg font-bold text-gray-800">Pay with Wallet</h3>
            <p class="text-sm text-gray-600">Use your wallet balance to complete payment</p>
        </div>
        <div class="text-right">
            <p class="text-2xl font-bold text-green-600">UGX {{ number_format($walletBalance ?? 0, 2) }}</p>
            <p class="text-xs text-gray-500">Available Balance</p>
        </div>
    </div>
    
    <div class="space-y-4">
        <div class="flex items-center justify-between p-4 bg-white rounded-lg border">
            <div>
                <p class="font-medium text-gray-800">Order Total</p>
                <p class="text-sm text-gray-600">Including shipping & taxes</p>
            </div>
            <p class="text-xl font-bold text-gray-900"> UGX {{ number_format($order->total, 2) }}</p>
        </div>
        
        @if(($walletBalance ?? 0) >= $order->total)
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-2">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-green-500 mr-3"></i>
                <p class="text-green-700">Sufficient balance available</p>
            </div>
        </div>
        
        <form id="walletPaymentForm">
            @csrf
            <button type="button" 
                    id="payWithWalletBtn"
                    data-order-id="{{ $order->id }}"
                    class="w-full bg-green-600 text-white py-3 rounded-lg font-semibold hover:bg-green-700 transition flex items-center justify-center">
                <i class="fas fa-wallet mr-2"></i>
                Pay UGX {{ number_format($order->total, 2) }} with Wallet
            </button>
        </form>
        @else
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-2">
            <div class="flex items-start">
                <i class="fas fa-exclamation-triangle text-yellow-500 mr-3 mt-0.5"></i>
                <div>
                    <p class="text-yellow-700 font-medium">Insufficient Balance</p>
                    <p class="text-sm text-yellow-600">
                        You need UGX {{ number_format($order->total - ($walletBalance ?? 0), 2) }} more
                    </p>
                </div>
            </div>
        </div>
        
        <div class="flex space-x-3">
            <a href="{{ route('buyer.wallet.index') }}" 
               class="flex-1 bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition flex items-center justify-center">
                <i class="fas fa-plus-circle mr-2"></i>
                Add Funds
            </a>
            <a href="{{ route('buyer.orders.payment', $order) }}" 
               class="flex-1 bg-gray-200 text-gray-700 py-3 rounded-lg font-semibold hover:bg-gray-300 transition flex items-center justify-center">
                <i class="fas fa-credit-card mr-2"></i>
                Other Methods
            </a>
        </div>
        @endif
        
        <div id="paymentMessage" class="mt-3 hidden"></div>
        
        <p class="text-xs text-gray-500 text-center">
            <i class="fas fa-shield-alt mr-1"></i> Secure payment · Instant confirmation
        </p>
    </div>
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
                            <p class="text-sm text-gray-600">Price: UGX {{ number_format($item->unit_price, 2) }}</p>
                            
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
                            <p class="font-bold text-gray-800">UGX {{ number_format($item->line_total, 2) }}</p>
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
                        <span>UGX {{ number_format($order->subtotal, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Shipping</span>
                        <span>UGX {{ number_format($order->shipping, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Taxes</span>
                        <span>UGX {{ number_format($order->taxes, 2) }}</span>
                    </div>
                    <div class="pt-2 border-t">
                        <div class="flex justify-between font-bold text-lg">
                            <span>Total</span>
                            <span class="text-primary">UGX {{ number_format($order->total, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Shipping Information -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Shipping Information</h2>
                
                @php
                    $meta = $order->meta ?? [];
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const payWithWalletBtn = document.getElementById('payWithWalletBtn');
    const paymentMessage = document.getElementById('paymentMessage');
    
    if (payWithWalletBtn) {
        payWithWalletBtn.addEventListener('click', async function() {
            const orderId = this.getAttribute('data-order-id');
            
            // Disable button and show loading
            payWithWalletBtn.disabled = true;
            payWithWalletBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Processing...';
            paymentMessage.innerHTML = '';
            paymentMessage.classList.add('hidden');
            
            try {
                const response = await fetch(`/buyer/orders/${orderId}/pay-with-wallet`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    paymentMessage.innerHTML = `
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                            <div class="flex items-center">
                                <i class="fas fa-check-circle mr-3"></i>
                                <div>
                                    <p class="font-semibold">Payment Successful!</p>
                                    <p class="text-sm">${data.message}</p>
                                </div>
                            </div>
                        </div>
                    `;
                    paymentMessage.classList.remove('hidden');
                    
                    // Update page elements
                    const statusBadge = document.querySelector('.px-3.py-1.rounded-full');
                    if (statusBadge) {
                        statusBadge.textContent = 'Paid';
                        statusBadge.className = 'px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800';
                    }
                    
                    // Remove payment section
                    const paymentSection = payWithWalletBtn.closest('.bg-gradient-to-r.from-green-50.to-emerald-50');
                    if (paymentSection) {
                        setTimeout(() => {
                            paymentSection.remove();
                        }, 3000);
                    }
                    
                    // Show success message at top
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'bg-green-50 border-l-4 border-green-400 p-4 mb-6 rounded-r-lg';
                    alertDiv.innerHTML = `
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-400 mr-3"></i>
                            <p class="text-green-700">Payment successful! Your order is now confirmed.</p>
                        </div>
                    `;
                    document.querySelector('.container.mx-auto.px-4.py-8').insertBefore(alertDiv, document.querySelector('.mb-8'));
                    
                    // Redirect after 3 seconds
                    setTimeout(() => {
                        window.location.reload();
                    }, 3000);
                } else {
                    paymentMessage.innerHTML = `
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                            <div class="flex items-center">
                                <i class="fas fa-exclamation-circle mr-3"></i>
                                <div>
                                    <p class="font-semibold">Payment Failed</p>
                                    <p class="text-sm">${data.message}</p>
                                </div>
                            </div>
                        </div>
                    `;
                    paymentMessage.classList.remove('hidden');
                    payWithWalletBtn.disabled = false;
                    payWithWalletBtn.innerHTML = '<i class="fas fa-wallet mr-2"></i> Pay UGX {{ number_format($order->total, 2) }} with Wallet';
                }
            } catch (error) {
                paymentMessage.innerHTML = `
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle mr-3"></i>
                            <div>
                                <p class="font-semibold">Error</p>
                                <p class="text-sm">An error occurred. Please try again.</p>
                            </div>
                        </div>
                    </div>
                `;
                paymentMessage.classList.remove('hidden');
                payWithWalletBtn.disabled = false;
                payWithWalletBtn.innerHTML = '<i class="fas fa-wallet mr-2"></i> Pay UGX {{ number_format($order->total, 2) }} with Wallet';
                console.error('Payment error:', error);
            }
        });
    }
});
</script>
@endsection