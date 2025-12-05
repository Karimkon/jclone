@extends('layouts.buyer')

@section('title', 'Buyer Dashboard - ' . config('app.name'))
@section('page_title', 'Dashboard')
@section('page_description', 'Welcome to your buyer dashboard')

@section('content')
<div class="space-y-6">
    <!-- Welcome Card -->
    <div class="bg-gradient-to-r from-primary to-indigo-600 text-white rounded-2xl p-6">
        <div class="flex flex-col md:flex-row items-center justify-between">
            <div class="md:w-2/3">
                <h2 class="text-2xl font-bold mb-2">Welcome back, {{ Auth::user()->name }}!</h2>
                <p class="opacity-90 mb-4">
                    Track your orders, manage your wallet, and discover amazing products from local and international vendors.
                </p>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('marketplace.index') }}" 
                       class="px-4 py-2 bg-white text-primary rounded-lg font-semibold hover:bg-gray-100 transition inline-flex items-center">
                        <i class="fas fa-store mr-2"></i> Shop Now
                    </a>
                    <a href="{{ route('buyer.wallet.index') }}" 
                       class="px-4 py-2 bg-primary border-2 border-white text-white rounded-lg font-semibold hover:bg-indigo-700 transition inline-flex items-center">
                        <i class="fas fa-wallet mr-2"></i> My Wallet
                    </a>
                </div>
            </div>
            <div class="mt-6 md:mt-0">
                <div class="relative">
                    <div class="w-24 h-24 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                        <i class="fas fa-shopping-bag text-4xl"></i>
                    </div>
                    @php
                        $cartCount = Auth::user()->cart ? count(Auth::user()->cart->items ?? []) : 0;
                    @endphp
                    @if($cartCount > 0)
                    <div class="absolute -top-2 -right-2 bg-red-500 text-white text-sm font-bold rounded-full w-8 h-8 flex items-center justify-center">
                        {{ $cartCount }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Wallet Balance -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center mb-4">
                <div class="bg-green-100 p-3 rounded-lg mr-4">
                    <i class="fas fa-wallet text-green-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-gray-600">Wallet Balance</p>
                    <p class="text-2xl font-bold text-gray-800">${{ number_format($stats['wallet_balance'], 2) }}</p>
                </div>
            </div>
            <a href="{{ route('buyer.wallet.index') }}" 
               class="block text-center bg-green-600 text-white py-2 rounded-lg font-semibold hover:bg-green-700 transition">
                <i class="fas fa-plus mr-2"></i> Add Funds
            </a>
        </div>

        <!-- Total Orders -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center mb-4">
                <div class="bg-blue-100 p-3 rounded-lg mr-4">
                    <i class="fas fa-shopping-bag text-blue-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-gray-600">Total Orders</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['total_orders'] }}</p>
                </div>
            </div>
            <a href="{{ route('buyer.orders.index') }}" 
               class="block text-center bg-blue-600 text-white py-2 rounded-lg font-semibold hover:bg-blue-700 transition">
                <i class="fas fa-eye mr-2"></i> View Orders
            </a>
        </div>

        <!-- Wishlist Items -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center mb-4">
                <div class="bg-pink-100 p-3 rounded-lg mr-4">
                    <i class="fas fa-heart text-pink-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-gray-600">Wishlist Items</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['wishlist_items'] }}</p>
                </div>
            </div>
            <a href="{{ route('buyer.wishlist.index') }}" 
               class="block text-center bg-pink-600 text-white py-2 rounded-lg font-semibold hover:bg-pink-700 transition">
                <i class="fas fa-heart mr-2"></i> View Wishlist
            </a>
        </div>

        <!-- Pending Orders -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center mb-4">
                <div class="bg-yellow-100 p-3 rounded-lg mr-4">
                    <i class="fas fa-clock text-yellow-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-gray-600">Pending Orders</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['pending_orders'] }}</p>
                </div>
            </div>
            <a href="{{ route('buyer.orders.index', ['status' => 'pending']) }}" 
               class="block text-center bg-yellow-600 text-white py-2 rounded-lg font-semibold hover:bg-yellow-700 transition">
                <i class="fas fa-external-link-alt mr-2"></i> Track Orders
            </a>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Orders -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-bold text-gray-800">Recent Orders</h3>
                <a href="{{ route('buyer.orders.index') }}" class="text-primary hover:text-indigo-700 text-sm font-medium">
                    View All <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
            
            @if($recentOrders->count() > 0)
                <div class="space-y-4">
                    @foreach($recentOrders as $order)
                    <a href="{{ route('buyer.orders.show', $order) }}" 
                       class="block border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="font-semibold text-gray-800">#{{ $order->order_number }}</div>
                                <div class="text-sm text-gray-600">
                                    {{ $order->vendorProfile->business_name ?? 'Vendor' }}
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-bold text-gray-800">${{ number_format($order->total, 2) }}</div>
                                <span class="text-xs px-2 py-1 rounded-full 
                                    @if($order->status == 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($order->status == 'paid') bg-blue-100 text-blue-800
                                    @elseif($order->status == 'processing') bg-purple-100 text-purple-800
                                    @elseif($order->status == 'delivered') bg-green-100 text-green-800
                                    @elseif($order->status == 'cancelled') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </div>
                        </div>
                    </a>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <i class="fas fa-shopping-bag text-gray-400 text-3xl mb-3"></i>
                    <p class="text-gray-600">No orders yet</p>
                    <p class="text-sm text-gray-500 mt-1">Start shopping to see your orders here</p>
                    <a href="{{ route('marketplace.index') }}" 
                       class="mt-4 inline-block bg-primary text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition">
                        <i class="fas fa-store mr-2"></i> Browse Products
                    </a>
                </div>
            @endif
        </div>

        <!-- Recent Wallet Transactions -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-bold text-gray-800">Recent Transactions</h3>
                <a href="{{ route('buyer.wallet.transactions') }}" class="text-primary hover:text-indigo-700 text-sm font-medium">
                    View All <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
            
            @if($walletTransactions->count() > 0)
                <div class="space-y-4">
                    @foreach($walletTransactions as $transaction)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="flex items-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium 
                                        @if($transaction->type === 'deposit') bg-green-100 text-green-800
                                        @elseif($transaction->type === 'withdrawal') bg-red-100 text-red-800
                                        @elseif($transaction->type === 'payment') bg-blue-100 text-blue-800
                                        @elseif($transaction->type === 'refund') bg-yellow-100 text-yellow-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ ucfirst($transaction->type) }}
                                    </span>
                                    <span class="ml-3 text-sm text-gray-500">
                                        {{ $transaction->created_at->diffForHumans() }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600 mt-1">
                                    {{ $transaction->description }}
                                </p>
                            </div>
                            <div class="text-right">
                                <div class="font-bold @if($transaction->amount > 0) text-green-600 @else text-red-600 @endif">
                                    @if($transaction->amount > 0)
                                    +${{ number_format($transaction->amount, 2) }}
                                    @else
                                    -${{ number_format(abs($transaction->amount), 2) }}
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    Balance: ${{ number_format($transaction->balance_after, 2) }}
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <i class="fas fa-exchange-alt text-gray-400 text-3xl mb-3"></i>
                    <p class="text-gray-600">No transactions yet</p>
                    <p class="text-sm text-gray-500 mt-1">Your wallet transaction history will appear here</p>
                    <a href="{{ route('buyer.wallet.index') }}" 
                       class="mt-4 inline-block bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
                        <i class="fas fa-plus mr-2"></i> Add Funds to Wallet
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Quick Links -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-6">Quick Actions</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="{{ route('marketplace.index') }}" 
               class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-center hover:bg-blue-100 transition group">
                <i class="fas fa-store text-blue-600 text-2xl mb-2 group-hover:scale-110 transition"></i>
                <div class="font-semibold text-gray-800">Shop Now</div>
                <div class="text-sm text-gray-600">Browse products</div>
            </a>
            
            <a href="{{ route('buyer.wallet.index') }}" 
               class="bg-green-50 border border-green-200 rounded-lg p-4 text-center hover:bg-green-100 transition group">
                <i class="fas fa-wallet text-green-600 text-2xl mb-2 group-hover:scale-110 transition"></i>
                <div class="font-semibold text-gray-800">My Wallet</div>
                <div class="text-sm text-gray-600">Add funds</div>
            </a>
            
            <a href="{{ route('buyer.orders.index') }}" 
               class="bg-purple-50 border border-purple-200 rounded-lg p-4 text-center hover:bg-purple-100 transition group">
                <i class="fas fa-shopping-bag text-purple-600 text-2xl mb-2 group-hover:scale-110 transition"></i>
                <div class="font-semibold text-gray-800">My Orders</div>
                <div class="text-sm text-gray-600">Track orders</div>
            </a>
            
            <a href="{{ route('buyer.profile') }}" 
               class="bg-orange-50 border border-orange-200 rounded-lg p-4 text-center hover:bg-orange-100 transition group">
                <i class="fas fa-user-cog text-orange-600 text-2xl mb-2 group-hover:scale-110 transition"></i>
                <div class="font-semibold text-gray-800">Profile</div>
                <div class="text-sm text-gray-600">Update details</div>
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Update cart count animation
    document.addEventListener('DOMContentLoaded', function() {
        const cartCount = {{ $cartCount }};
        if (cartCount > 0) {
            const cartIcon = document.querySelector('nav a[href*="cart"]');
            if (cartIcon) {
                cartIcon.classList.add('animate-pulse');
                setTimeout(() => {
                    cartIcon.classList.remove('animate-pulse');
                }, 2000);
            }
        }
        
        // Auto-refresh wallet balance every 30 seconds
        setInterval(() => {
            fetch('{{ route("buyer.wallet.balance") }}')
                .then(response => response.json())
                .then(data => {
                    const balanceElement = document.querySelector('.wallet-balance');
                    if (balanceElement && data.balance) {
                        const oldBalance = parseFloat(balanceElement.textContent.replace('$', '').replace(',', ''));
                        const newBalance = parseFloat(data.balance);
                        if (newBalance > oldBalance) {
                            balanceElement.classList.add('text-green-500');
                            setTimeout(() => {
                                balanceElement.classList.remove('text-green-500');
                            }, 1000);
                        }
                        balanceElement.textContent = '$' + newBalance.toFixed(2);
                    }
                });
        }, 30000);
    });
</script>
@endpush