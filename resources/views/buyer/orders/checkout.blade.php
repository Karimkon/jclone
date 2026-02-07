@extends('layouts.buyer')

@section('title', 'Checkout - ' . config('app.name'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">Checkout</h1>
    
    @if(session('error'))
    <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
        <p class="text-red-700">{{ session('error') }}</p>
    </div>
    @endif
    
    <form action="{{ route('buyer.orders.place-order') }}" method="POST" id="checkoutForm">
        @csrf
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column: Shipping & Payment -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Shipping Address -->
               
                <!-- Shipping Address -->
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold">Shipping Address</h2>
        <a href="{{ route('buyer.addresses.create') }}" 
           class="text-sm text-primary hover:underline flex items-center gap-1">
            <i class="fas fa-plus-circle"></i> Add New Address
        </a>
    </div>
    
    @if($addresses->count() > 0)
        <div class="space-y-3">
            @foreach($addresses as $address)
            <label class="flex items-start p-4 border-2 rounded-lg cursor-pointer transition {{ $address->is_default ? 'border-primary bg-primary/5' : 'border-gray-200 hover:border-primary' }}">
                <input type="radio" 
                       name="shipping_address_id" 
                       value="{{ $address->id }}" 
                       {{ $address->is_default ? 'checked' : '' }}
                       required
                       class="mt-1 mr-4 h-5 w-5 text-primary">
                <div class="flex-1">
                    <div class="flex items-start justify-between">
                        <div>
                            @if($address->label)
                                <span class="inline-block px-2 py-0.5 bg-gray-100 text-gray-700 text-xs rounded mb-2">
                                    <i class="fas fa-tag"></i> {{ $address->label }}
                                </span>
                            @endif
                            @if($address->is_default)
                                <span class="inline-block px-2 py-0.5 bg-primary text-white text-xs rounded mb-2">
                                    <i class="fas fa-star"></i> Default
                                </span>
                            @endif
                            
                            <p class="font-semibold text-gray-900">{{ $address->recipient_name }}</p>
                            <p class="text-sm text-gray-600">{{ $address->recipient_phone }}</p>
                            <p class="text-sm text-gray-700 mt-2">
                                {{ $address->address_line_1 }}
                                @if($address->address_line_2), {{ $address->address_line_2 }}@endif
                            </p>
                            <p class="text-sm text-gray-600">
                                {{ $address->city }}@if($address->state_region), {{ $address->state_region }}@endif
                                @if($address->postal_code) - {{ $address->postal_code }}@endif
                            </p>
                            <p class="text-sm text-gray-600">{{ $address->country }}</p>
                            
                            @if($address->delivery_instructions)
                                <p class="text-xs text-gray-500 mt-2 italic">
                                    <i class="fas fa-info-circle"></i> {{ $address->delivery_instructions }}
                                </p>
                            @endif
                        </div>
                        
                        <a href="{{ route('buyer.addresses.edit', $address->id) }}" 
                           class="text-sm text-gray-500 hover:text-primary ml-4">
                            <i class="fas fa-edit"></i>
                        </a>
                    </div>
                </div>
            </label>
            @endforeach
        </div>
    @else
        <div class="text-center py-8 bg-gray-50 rounded-lg">
            <i class="fas fa-map-marker-alt text-gray-300 text-4xl mb-3"></i>
            <p class="text-gray-600 mb-4">No shipping addresses found</p>
            <a href="{{ route('buyer.addresses.create') }}" 
               class="inline-block px-6 py-2 bg-primary text-white rounded-lg hover:bg-indigo-700">
                Add Your First Address
            </a>
        </div>
    @endif
    
    @error('shipping_address_id')
        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
    @enderror
</div>
                
                <!-- Payment Method -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-bold mb-4">Payment Method</h2>
                    
                    <div class="space-y-3">
                        <!-- Cash on Delivery -->
                        <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-indigo-500 transition payment-method">
                            <input type="radio" name="payment_method" value="cash_on_delivery" required
                                   class="mr-4 h-5 w-5 text-indigo-600"
                                   {{ old('payment_method') == 'cash_on_delivery' ? 'checked' : '' }}>
                            <div class="flex-1">
                                <div class="flex items-center">
                                    <i class="fas fa-money-bill-wave text-2xl text-green-600 mr-3"></i>
                                    <div>
                                        <p class="font-semibold">Cash on Delivery</p>
                                        <p class="text-sm text-gray-600">Pay when you receive your order</p>
                                    </div>
                                </div>
                            </div>
                        </label>
                        
                        <!-- Wallet -->
                        @if($wallet && $wallet->available_balance > 0)
                        <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-indigo-500 transition payment-method">
                            <input type="radio" name="payment_method" value="wallet" required
                                   class="mr-4 h-5 w-5 text-indigo-600"
                                   {{ old('payment_method') == 'wallet' ? 'checked' : '' }}>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <i class="fas fa-wallet text-2xl text-indigo-600 mr-3"></i>
                                        <div>
                                            <p class="font-semibold">Wallet</p>
                                            <p class="text-sm text-gray-600">Balance: UGX {{ number_format($wallet->available_balance, 2) }}</p>
                                        </div>
                                    </div>
                                    @if($wallet->available_balance < $cart->total)
                                        <span class="text-red-500 text-sm">Insufficient balance</span>
                                    @endif
                                </div>
                            </div>
                        </label>
                        @endif
                        
                        <!-- Card Payment -->
                        <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-indigo-500 transition payment-method">
                            <input type="radio" name="payment_method" value="card" required
                                   class="mr-4 h-5 w-5 text-indigo-600"
                                   {{ old('payment_method') == 'card' ? 'checked' : '' }}>
                            <div class="flex-1">
                                <div class="flex items-center">
                                    <i class="fas fa-credit-card text-2xl text-blue-600 mr-3"></i>
                                    <div>
                                        <p class="font-semibold">Credit/Debit Card</p>
                                        <p class="text-sm text-gray-600">Visa, Mastercard, etc.</p>
                                    </div>
                                </div>
                            </div>
                        </label>
                        
                        <!-- Mobile Money -->
                        <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-indigo-500 transition payment-method">
                            <input type="radio" name="payment_method" value="mobile_money" required
                                   class="mr-4 h-5 w-5 text-indigo-600"
                                   {{ old('payment_method') == 'mobile_money' ? 'checked' : '' }}>
                            <div class="flex-1">
                                <div class="flex items-center">
                                    <i class="fas fa-mobile-alt text-2xl text-purple-600 mr-3"></i>
                                    <div>
                                        <p class="font-semibold">Mobile Money</p>
                                        <p class="text-sm text-gray-600">MTN, Airtel, etc.</p>
                                    </div>
                                </div>
                            </div>
                        </label>
                        
                        <!-- Bank Transfer -->
                        <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-indigo-500 transition payment-method">
                            <input type="radio" name="payment_method" value="bank_transfer" required
                                   class="mr-4 h-5 w-5 text-indigo-600"
                                   {{ old('payment_method') == 'bank_transfer' ? 'checked' : '' }}>
                            <div class="flex-1">
                                <div class="flex items-center">
                                    <i class="fas fa-university text-2xl text-gray-600 mr-3"></i>
                                    <div>
                                        <p class="font-semibold">Bank Transfer</p>
                                        <p class="text-sm text-gray-600">Direct bank transfer</p>
                                    </div>
                                </div>
                            </div>
                        </label>
                    </div>
                    
                    @error('payment_method')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Order Notes -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-bold mb-4">Order Notes (Optional)</h2>
                    <textarea name="notes" rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                              placeholder="Any special instructions for delivery...">{{ old('notes') }}</textarea>
                </div>
            </div>
            
            <!-- Right Column: Order Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow p-6 sticky top-4">
                    <h2 class="text-xl font-bold mb-4">Order Summary</h2>
                    
                    <!-- Cart Items -->
                    <div class="space-y-3 mb-4 max-h-64 overflow-y-auto">
                        @foreach($cart->items as $item)
                        <div class="flex items-center space-x-3 pb-3 border-b">
                            <div class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center">
                                @if(isset($item['image']))
                                    <img src="{{ $item['image'] }}" alt="{{ $item['title'] }}" class="w-full h-full object-cover rounded-lg">
                                @else
                                    <i class="fas fa-image text-gray-400"></i>
                                @endif
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-sm">{{ $item['title'] }}</p>
                                <p class="text-gray-600 text-xs">Qty: {{ $item['quantity'] }}</p>
                            </div>
                            <p class="font-semibold">UGX {{ number_format($item['total'], 2) }}</p>
                        </div>
                        @endforeach
                    </div>
                    
                    <!-- Totals -->
                    <div class="space-y-2 mb-4 pt-4 border-t">
                        <div class="flex justify-between text-gray-600">
                            <span>Subtotal</span>
                            <span>UGX {{ number_format($cart->subtotal, 2) }}</span>
                        </div>
                        @if($cart->tax > 0)
                        <div class="flex justify-between text-gray-600">
                            <span>Import/Tax Charges</span>
                            <span>UGX {{ number_format($cart->tax, 2) }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between font-bold text-lg pt-2 border-t">
                            <span>Total</span>
                            <span class="text-indigo-600">UGX {{ number_format($cart->total, 2) }}</span>
                        </div>
                    </div>

                    <!-- Safety Warning -->
                    <div class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-lg flex items-center gap-2">
                        <i class="fas fa-exclamation-triangle text-amber-600"></i>
                        <span class="text-amber-800 text-sm font-medium">Avoid paying in advance! Even for delivery.</span>
                    </div>

                    <!-- Place Order Button -->
                    <button type="submit"
                            class="w-full bg-indigo-600 text-white py-3 rounded-lg font-semibold hover:bg-indigo-700 transition flex items-center justify-center">
                        <i class="fas fa-lock mr-2"></i>
                        Place Order
                    </button>
                    
                    <p class="text-xs text-gray-500 text-center mt-3">
                        <i class="fas fa-shield-alt mr-1"></i>
                        Your payment information is secure and encrypted
                    </p>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Highlight selected payment method
    const paymentMethods = document.querySelectorAll('.payment-method');
    paymentMethods.forEach(method => {
        const radio = method.querySelector('input[type="radio"]');
        if (radio.checked) {
            method.classList.add('border-indigo-500', 'bg-indigo-50');
        }
        
        radio.addEventListener('change', function() {
            paymentMethods.forEach(m => {
                m.classList.remove('border-indigo-500', 'bg-indigo-50');
            });
            if (this.checked) {
                method.classList.add('border-indigo-500', 'bg-indigo-50');
            }
        });
    });
    
    // Form validation
    document.getElementById('checkoutForm').addEventListener('submit', function(e) {
        const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
        if (!paymentMethod) {
            e.preventDefault();
            alert('Please select a payment method');
            return false;
        }
        
        // Check wallet balance if wallet is selected
        if (paymentMethod.value === 'wallet') {
            const walletBalance = {{ $wallet ? $wallet->available_balance : 0 }};
            const total = {{ $cart->total }};
            if (walletBalance < total) {
                e.preventDefault();
                alert('Insufficient wallet balance. Please choose another payment method.');
                return false;
            }
        }
    });
});
</script>
@endsection

