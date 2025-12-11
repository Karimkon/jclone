@extends('layouts.buyer')

@section('title', 'Complete Payment - Order #' . $order->order_number)

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Complete Payment</h1>
                <p class="text-gray-600">Order #{{ $order->order_number }}</p>
            </div>
            <div class="text-right">
                <p class="text-3xl font-bold text-primary">UGX {{ number_format($order->total, 0) }}</p>
                <p class="text-sm text-gray-600">Total Amount</p>
            </div>
        </div>
    </div>

    <!-- Payment Methods -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Select Payment Method</h2>
        
        <!-- Mobile Money Option -->
        <div class="border-2 border-gray-200 rounded-lg p-6 mb-4">
            <div class="flex items-center mb-4">
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mr-4">
                    <i class="fas fa-mobile-alt text-2xl text-purple-600"></i>
                </div>
                <div>
                    <h3 class="font-bold text-gray-800">Mobile Money</h3>
                    <p class="text-gray-600">Pay with MTN or Airtel Money</p>
                </div>
            </div>

            <!-- Mobile Money Form -->
            <form id="mobileMoneyForm" class="space-y-4">
                @csrf
                
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Mobile Network *</label>
                    <div class="grid grid-cols-2 gap-4">
                        <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-purple-500 transition">
                            <input type="radio" name="mobile_money_provider" value="mtn" required class="mr-3 h-5 w-5 text-purple-600">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-signal text-yellow-600"></i>
                                </div>
                                <div>
                                    <p class="font-semibold">MTN Mobile Money</p>
                                    <p class="text-sm text-gray-600">Uganda</p>
                                </div>
                            </div>
                        </label>
                        
                        <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-red-500 transition">
                            <input type="radio" name="mobile_money_provider" value="airtel" required class="mr-3 h-5 w-5 text-red-600">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-wifi text-red-600"></i>
                                </div>
                                <div>
                                    <p class="font-semibold">Airtel Money</p>
                                    <p class="text-sm text-gray-600">Uganda</p>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-gray-700 font-medium mb-2">Phone Number *</label>
                    <input type="tel" 
                           name="phone_number" 
                           required
                           placeholder="07XXXXXXXX"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    <p class="text-sm text-gray-500 mt-1">Enter the phone number registered with your mobile money account</p>
                </div>

                <div id="paymentStatus" class="hidden">
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                        <div class="flex items-center">
                            <i class="fas fa-spinner fa-spin text-yellow-600 mr-3"></i>
                            <div>
                                <p class="font-semibold text-yellow-800">Processing Payment</p>
                                <p class="text-yellow-700 text-sm">Please wait while we initialize your payment...</p>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="button" 
                        onclick="initiateMobileMoneyPayment()"
                        id="payButton"
                        class="w-full bg-purple-600 text-white py-3 rounded-lg font-semibold hover:bg-purple-700 transition flex items-center justify-center">
                    <i class="fas fa-lock mr-2"></i>
                    Pay UGX {{ number_format($order->total, 0) }} with Mobile Money
                </button>
            </form>
        </div>

        <!-- Other Payment Methods -->
        <div class="space-y-3">
            <button class="w-full flex items-center p-4 border-2 border-gray-200 rounded-lg hover:border-blue-500 transition text-left">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                    <i class="fas fa-credit-card text-2xl text-blue-600"></i>
                </div>
                <div class="flex-1">
                    <p class="font-semibold text-gray-800">Credit/Debit Card</p>
                    <p class="text-gray-600">Visa, Mastercard, etc. (Coming Soon)</p>
                </div>
                <i class="fas fa-chevron-right text-gray-400"></i>
            </button>

            <button class="w-full flex items-center p-4 border-2 border-gray-200 rounded-lg hover:border-green-500 transition text-left">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mr-4">
                    <i class="fas fa-wallet text-2xl text-green-600"></i>
                </div>
                <div class="flex-1">
                    <p class="font-semibold text-gray-800">Wallet Balance</p>
                    <p class="text-gray-600">Use your wallet funds (Coming Soon)</p>
                </div>
                <i class="fas fa-chevron-right text-gray-400"></i>
            </button>
        </div>
    </div>

    <!-- Order Summary -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Order Summary</h2>
        
        <div class="space-y-3 mb-4">
            @foreach($order->items as $item)
            <div class="flex items-center justify-between py-2 border-b">
                <div>
                    <p class="font-medium">{{ $item->title }}</p>
                    <p class="text-sm text-gray-600">Qty: {{ $item->quantity }}</p>
                </div>
                <p class="font-semibold">UGX {{ number_format($item->line_total, 0) }}</p>
            </div>
            @endforeach
        </div>

        <div class="space-y-2 pt-4 border-t">
            <div class="flex justify-between text-gray-600">
                <span>Subtotal</span>
                <span>UGX {{ number_format($order->subtotal, 0) }}</span>
            </div>
            <div class="flex justify-between text-gray-600">
                <span>Shipping</span>
                <span>UGX {{ number_format($order->shipping, 0) }}</span>
            </div>
            <div class="flex justify-between text-gray-600">
                <span>Taxes</span>
                <span>UGX {{ number_format($order->taxes, 0) }}</span>
            </div>
            <div class="flex justify-between font-bold text-lg pt-2">
                <span>Total</span>
                <span class="text-primary">UGX {{ number_format($order->total, 0) }}</span>
            </div>
        </div>
    </div>
</div>

<script>
const csrfToken = '{{ csrf_token() }}';
const orderId = {{ $order->id }};

async function initiateMobileMoneyPayment() {
    const form = document.getElementById('mobileMoneyForm');
    const provider = form.querySelector('input[name="mobile_money_provider"]:checked');
    const phoneInput = form.querySelector('input[name="phone_number"]');
    const phone = phoneInput.value;

    if (!provider || !phone) {
        alert('Please select a mobile network and enter your phone number');
        return;
    }

    // Validate phone number
    if (!/^07\d{8}$/.test(phone)) {
        alert('Please enter a valid Ugandan phone number starting with 07');
        return;
    }

    // Show processing status
    const statusDiv = document.getElementById('paymentStatus');
    const payButton = document.getElementById('payButton');
    statusDiv.classList.remove('hidden');
    payButton.disabled = true;
    payButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Processing...';

    try {
        const response = await fetch(`/payment/order/${orderId}/mobile-money`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                mobile_money_provider: provider.value,
                phone_number: phone
            })
        });

        const data = await response.json();

        if (data.success && data.payment_url) {
            // Redirect to PesaPal payment page
            window.location.href = data.payment_url;
        } else {
            alert(data.message || 'Failed to initiate payment');
            statusDiv.classList.add('hidden');
            payButton.disabled = false;
            payButton.innerHTML = '<i class="fas fa-lock mr-2"></i> Pay UGX {{ number_format($order->total, 0) }}';
        }
    } catch (error) {
        console.error('Payment initiation error:', error);
        alert('An error occurred. Please try again.');
        statusDiv.classList.add('hidden');
        payButton.disabled = false;
        payButton.innerHTML = '<i class="fas fa-lock mr-2"></i> Pay UGX {{ number_format($order->total, 0) }}';
    }
}

// Auto-check payment status if returning from payment
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('OrderTrackingId')) {
        checkPaymentStatus();
    }
});

async function checkPaymentStatus() {
    try {
        const response = await fetch(`/payment/order/${orderId}/status`, {
            headers: {
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success && data.status === 'completed') {
            window.location.href = `/buyer/orders/${orderId}?payment=success`;
        }
    } catch (error) {
        console.error('Status check error:', error);
    }
}
</script>
@endsection