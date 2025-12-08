@extends('layouts.buyer')

@section('title', 'Shopping Cart - ' . config('app.name'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">Shopping Cart</h1>
    
    @if(session('success'))
    <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6">
        <p class="text-green-700">{{ session('success') }}</p>
    </div>
    @endif
    
    @if(session('error'))
    <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
        <p class="text-red-700">{{ session('error') }}</p>
    </div>
    @endif
    
    @if($cart && !empty($cart->items))
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Cart Items -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="p-6 border-b bg-gray-50">
                    <div class="flex justify-between items-center">
                        <h2 class="text-xl font-bold">Cart Items ({{ count($cart->items) }})</h2>
                        <form action="{{ route('buyer.cart.clear') }}" method="POST" onsubmit="return confirm('Are you sure you want to clear your cart?')">
                            @csrf
                            @method('POST')
                            <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">
                                <i class="fas fa-trash mr-1"></i> Clear Cart
                            </button>
                        </form>
                    </div>
                </div>
                
                <div class="divide-y">
                    @foreach($cart->items as $item)
                    <div class="p-6 cart-item" data-listing-id="{{ $item['listing_id'] }}">
                        <div class="flex space-x-4">
                            <!-- Product Image -->
                            <div class="w-24 h-24 flex-shrink-0">
                                @if($item['image'])
                                <img src="{{ $item['image'] }}" alt="{{ $item['title'] }}" 
                                     class="w-full h-full object-cover rounded-lg">
                                @else
                                <div class="w-full h-full bg-gray-200 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-image text-gray-400 text-2xl"></i>
                                </div>
                                @endif
                            </div>
                            
                            <!-- Product Details -->
                            <div class="flex-1">
                                <div class="flex justify-between">
                                    <div>
                                        <h3 class="font-bold text-lg mb-1">{{ $item['title'] }}</h3>
                                        <p class="text-sm text-gray-600 mb-2">
                                            <i class="fas fa-store mr-1"></i> {{ $item['vendor_name'] }}
                                        </p>
                                        <div class="flex items-center space-x-4 text-sm text-gray-500">
                                            @if(isset($item['origin']))
                                            <span class="px-2 py-1 bg-{{ $item['origin'] == 'imported' ? 'blue' : 'green' }}-100 text-{{ $item['origin'] == 'imported' ? 'blue' : 'green' }}-800 rounded text-xs">
                                                <i class="fas fa-{{ $item['origin'] == 'imported' ? 'plane' : 'home' }} mr-1"></i>
                                                {{ ucfirst($item['origin']) }}
                                            </span>
                                            @endif
                                            @if(isset($item['weight_kg']))
                                            <span><i class="fas fa-weight-hanging mr-1"></i> {{ $item['weight_kg'] }}kg</span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <!-- Remove Button -->
                                    <button onclick="removeFromCart({{ $item['listing_id'] }})" 
                                            class="text-red-600 hover:text-red-800 h-8">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                
                                <!-- Quantity and Price -->
                                <div class="mt-4 flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <span class="text-sm text-gray-600">Quantity:</span>
                                        <div class="flex items-center border border-gray-300 rounded-lg">
                                            <button type="button" 
                                                    onclick="updateQuantity({{ $item['listing_id'] }}, {{ $item['quantity'] - 1 }})"
                                                    class="px-3 py-1 hover:bg-gray-100"
                                                    {{ $item['quantity'] <= 1 ? 'disabled' : '' }}>
                                                <i class="fas fa-minus text-sm"></i>
                                            </button>
                                            <input type="number" 
                                                   id="qty-{{ $item['listing_id'] }}"
                                                   value="{{ $item['quantity'] }}" 
                                                   min="1" 
                                                   max="{{ $item['stock'] ?? 99 }}"
                                                   class="w-16 text-center border-0 focus:ring-0 py-1"
                                                   onchange="updateQuantity({{ $item['listing_id'] }}, this.value)">
                                            <button type="button" 
                                                    onclick="updateQuantity({{ $item['listing_id'] }}, {{ $item['quantity'] + 1 }})"
                                                    class="px-3 py-1 hover:bg-gray-100"
                                                    {{ isset($item['stock']) && $item['quantity'] >= $item['stock'] ? 'disabled' : '' }}>
                                                <i class="fas fa-plus text-sm"></i>
                                            </button>
                                        </div>
                                        @if(isset($item['stock']))
                                        <span class="text-xs text-gray-500">({{ $item['stock'] }} available)</span>
                                        @endif
                                    </div>
                                    
                                    <div class="text-right">
                                        <div class="text-sm text-gray-600">${{ number_format($item['unit_price'], 2) }} each</div>
                                        <div class="text-lg font-bold text-primary item-total" id="total-{{ $item['listing_id'] }}">
                                            ${{ number_format($item['total'], 2) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        
        <!-- Order Summary -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow p-6 sticky top-4">
                <h2 class="text-xl font-bold mb-4">Order Summary</h2>
                
                <div class="space-y-3 mb-6">
                    <div class="flex justify-between text-gray-600">
                        <span>Subtotal</span>
                        <span id="cart-subtotal">${{ number_format($cart->subtotal, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>Shipping</span>
                        <span id="cart-shipping">${{ number_format($cart->shipping, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>Tax (18%)</span>
                        <span id="cart-tax">${{ number_format($cart->tax, 2) }}</span>
                    </div>
                    <div class="pt-3 border-t">
                        <div class="flex justify-between font-bold text-lg">
                            <span>Total</span>
                            <span class="text-primary" id="cart-total">${{ number_format($cart->total, 2) }}</span>
                        </div>
                    </div>
                </div>
                
                <a href="{{ route('buyer.orders.checkout') }}" 
                   class="block w-full bg-primary text-white text-center py-3 rounded-lg font-semibold hover:bg-indigo-700 transition mb-3">
                    <i class="fas fa-lock mr-2"></i> Proceed to Checkout
                </a>
                
                <a href="{{ route('marketplace.index') }}" 
                   class="block w-full text-center py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                    <i class="fas fa-shopping-bag mr-2"></i> Continue Shopping
                </a>
                
                <div class="mt-6 pt-6 border-t">
                    <div class="flex items-start space-x-2 text-sm text-gray-600">
                        <i class="fas fa-shield-alt text-green-600 mt-1"></i>
                        <div>
                            <p class="font-medium text-gray-800">Secure Checkout</p>
                            <p>Your payment is protected by escrow</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    @else
    <!-- Empty Cart -->
    <div class="bg-white rounded-lg shadow p-12 text-center">
        <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-6">
            <i class="fas fa-shopping-cart text-gray-400 text-4xl"></i>
        </div>
        <h2 class="text-2xl font-bold text-gray-800 mb-2">Your cart is empty</h2>
        <p class="text-gray-600 mb-6">Add some products to get started!</p>
        <a href="{{ route('marketplace.index') }}" 
           class="inline-flex items-center px-6 py-3 bg-primary text-white rounded-lg font-semibold hover:bg-indigo-700 transition">
            <i class="fas fa-shopping-bag mr-2"></i> Start Shopping
        </a>
    </div>
    @endif
</div>

<script>
// Update quantity
function updateQuantity(listingId, quantity) {
    quantity = parseInt(quantity);
    if (quantity < 1) return;
    
    const qtyInput = document.getElementById(`qty-${listingId}`);
    qtyInput.value = quantity;
    
    fetch(`/buyer/cart/update/${listingId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ quantity: quantity })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update item total
            document.getElementById(`total-${listingId}`).textContent = '$' + data.item_total;
            
            // Update cart summary
            document.getElementById('cart-subtotal').textContent = '$' + data.subtotal;
            document.getElementById('cart-shipping').textContent = '$' + data.shipping;
            document.getElementById('cart-tax').textContent = '$' + data.tax;
            document.getElementById('cart-total').textContent = '$' + data.cart_total;
            
            showToast(data.message, 'success');
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Failed to update quantity', 'error');
    });
}

// Remove from cart
function removeFromCart(listingId) {
    if (!confirm('Remove this item from cart?')) return;
    
    fetch(`/buyer/cart/remove/${listingId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove item from DOM
            const item = document.querySelector(`.cart-item[data-listing-id="${listingId}"]`);
            if (item) {
                item.remove();
            }
            
            // Update cart summary
            if (data.cart_count === 0) {
                location.reload(); // Reload to show empty cart message
            } else {
                document.getElementById('cart-subtotal').textContent = '$' + data.subtotal;
                document.getElementById('cart-shipping').textContent = '$' + data.shipping;
                document.getElementById('cart-tax').textContent = '$' + data.tax;
                document.getElementById('cart-total').textContent = '$' + data.cart_total;
            }
            
            // Update cart count in navbar
            updateCartCount(data.cart_count);
            
            showToast(data.message, 'success');
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Failed to remove item', 'error');
    });
}

// Update cart count in navbar
function updateCartCount(count) {
    document.querySelectorAll('.cart-count').forEach(element => {
        element.textContent = count;
    });
}

// Show toast notification
function showToast(message, type = 'info') {
    const existingToasts = document.querySelectorAll('.custom-toast');
    existingToasts.forEach(toast => toast.remove());
    
    const toast = document.createElement('div');
    toast.className = `custom-toast fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg transform transition-all duration-300 translate-x-0`;
    
    const typeStyles = {
        success: 'bg-green-500 text-white',
        error: 'bg-red-500 text-white',
        warning: 'bg-yellow-500 text-white',
        info: 'bg-blue-500 text-white'
    };
    
    toast.className += ` ${typeStyles[type] || typeStyles.info}`;
    
    const icons = {
        success: 'fa-check-circle',
        error: 'fa-times-circle',
        warning: 'fa-exclamation-triangle',
        info: 'fa-info-circle'
    };
    
    toast.innerHTML = `
        <div class="flex items-center">
            <i class="fas ${icons[type] || icons.info} mr-3"></i>
            <span>${message}</span>
            <button class="ml-4 text-white hover:text-gray-200" onclick="this.parentElement.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        if (toast.parentElement) {
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.remove();
                }
            }, 300);
        }
    }, 5000);
}
</script>
@endsection