<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Buyer Dashboard - JClone')</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .buyer-sidebar {
            background: linear-gradient(180deg, #1e40af 0%, #1e3a8a 100%);
        }
        
        .nav-item {
            transition: all 0.3s ease;
        }
        
        .nav-item:hover {
            background-color: rgba(255, 255, 255, 0.1);
            border-left: 4px solid #60a5fa;
        }
        
        .nav-item.active {
            background-color: rgba(255, 255, 255, 0.15);
            border-left: 4px solid #3b82f6;
        }
        
        .wallet-badge {
            background: linear-gradient(135deg, #10b981, #059669);
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <div class="buyer-sidebar text-white w-64 flex flex-col">
            <!-- Header -->
            <div class="p-6 border-b border-blue-800">
                <div class="flex items-center space-x-3">
                    <div class="bg-white p-2 rounded-lg">
                        <i class="fas fa-shopping-bag text-blue-600 text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold">Buyer Dashboard</h2>
                        <p class="text-sm opacity-75">JClone Marketplace</p>
                    </div>
                </div>
            </div>
            
            <!-- User Info -->
            <div class="p-6 border-b border-blue-800">
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-xl"></i>
                    </div>
                    <div class="flex-1">
                        <div class="font-semibold">{{ Auth::user()->name }}</div>
                        <div class="text-sm opacity-75">Buyer Account</div>
                    </div>
                </div>
                
                <!-- Wallet Balance -->
                @php
                    $wallet = Auth::user()->buyerWallet;
                    $balance = $wallet ? $wallet->balance : 0;
                @endphp
                <div class="mt-4 wallet-badge rounded-lg p-3">
                    <div class="text-sm opacity-90">Wallet Balance</div>
                    <div class="text-xl font-bold">${{ number_format($balance, 2) }}</div>
                    <a href="{{ route('buyer.wallet.index') }}" class="text-sm hover:underline mt-1 block">
                        Add Funds <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
            
            <!-- Navigation -->
            <nav class="flex-1 p-4">
                <div class="space-y-1">
                    <a href="{{ route('buyer.dashboard') }}" 
                       class="nav-item flex items-center px-4 py-3 rounded-lg {{ request()->routeIs('buyer.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-tachometer-alt mr-3"></i>
                        <span>Dashboard</span>
                    </a>
                    
                    <a href="{{ route('marketplace.index') }}" 
                       class="nav-item flex items-center px-4 py-3 rounded-lg">
                        <i class="fas fa-store mr-3"></i>
                        <span>Browse Marketplace</span>
                    </a>
                    
                    <a href="{{ route('cart.index') }}" 
                       class="nav-item flex items-center px-4 py-3 rounded-lg {{ request()->routeIs('cart.*') ? 'active' : '' }}">
                        <i class="fas fa-shopping-cart mr-3"></i>
                        <span>Shopping Cart</span>
                        @php
                            $cartCount = Auth::user()->cart ? count(Auth::user()->cart->items ?? []) : 0;
                        @endphp
                        @if($cartCount > 0)
                        <span class="ml-auto bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                            {{ $cartCount }}
                        </span>
                        @endif
                    </a>
                    
                    <a href="{{ route('buyer.orders.index') }}" 
                       class="nav-item flex items-center px-4 py-3 rounded-lg {{ request()->routeIs('buyer.orders.*') ? 'active' : '' }}">
                        <i class="fas fa-shopping-bag mr-3"></i>
                        <span>My Orders</span>
                    </a>
                    
                    <a href="{{ route('buyer.wishlist.index') }}" 
                       class="nav-item flex items-center px-4 py-3 rounded-lg {{ request()->routeIs('buyer.wishlist.*') ? 'active' : '' }}">
                        <i class="fas fa-heart mr-3"></i>
                        <span>Wishlist</span>
                    </a>
                    
                    <a href="{{ route('buyer.wallet.index') }}" 
                       class="nav-item flex items-center px-4 py-3 rounded-lg {{ request()->routeIs('buyer.wallet.*') ? 'active' : '' }}">
                        <i class="fas fa-wallet mr-3"></i>
                        <span>Wallet</span>
                    </a>
                    
                    <a href="{{ route('buyer.disputes.index') }}" 
                       class="nav-item flex items-center px-4 py-3 rounded-lg {{ request()->routeIs('buyer.disputes.*') ? 'active' : '' }}">
                        <i class="fas fa-exclamation-triangle mr-3"></i>
                        <span>Disputes</span>
                    </a>
                    
                    <a href="{{ route('buyer.profile') }}" 
                       class="nav-item flex items-center px-4 py-3 rounded-lg {{ request()->routeIs('buyer.profile') ? 'active' : '' }}">
                        <i class="fas fa-user-circle mr-3"></i>
                        <span>Profile</span>
                    </a>
                </div>
                
                <!-- Marketplace Quick Links -->
                <div class="mt-8 pt-6 border-t border-blue-800">
                    <div class="px-4 text-sm font-semibold opacity-75 mb-3">Marketplace</div>
                    <a href="{{ route('categories.index') }}" class="nav-item flex items-center px-4 py-2 rounded-lg text-sm">
                        <i class="fas fa-tags mr-2"></i>
                        Categories
                    </a>
                    <a href="{{ route('vendor.onboard.create') }}" class="nav-item flex items-center px-4 py-2 rounded-lg text-sm">
                        <i class="fas fa-store-alt mr-2"></i>
                        Become a Seller
                    </a>
                </div>
            </nav>
            
            <!-- Footer -->
            <div class="p-4 border-t border-blue-800">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="nav-item flex items-center px-4 py-3 rounded-lg w-full text-left hover:text-red-300">
                        <i class="fas fa-sign-out-alt mr-3"></i>
                        Logout
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="flex-1">
            <!-- Top Bar -->
            <header class="bg-white shadow-sm border-b">
                <div class="flex items-center justify-between px-6 py-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">@yield('page_title', 'Dashboard')</h1>
                        <p class="text-gray-600">@yield('page_description', 'Welcome to your buyer dashboard')</p>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <!-- Search -->
                        <div class="relative">
                            <form action="{{ route('marketplace.index') }}" method="GET" class="flex">
                                <input type="text" name="search" 
                                       placeholder="Search products..." 
                                       class="px-4 py-2 border border-gray-300 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent w-64">
                                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-r-lg hover:bg-blue-700">
                                    <i class="fas fa-search"></i>
                                </button>
                            </form>
                        </div>
                        
                        <!-- Notifications -->
                        <div class="relative">
                            <button class="p-2 text-gray-600 hover:text-blue-600 relative">
                                <i class="fas fa-bell text-xl"></i>
                                <span class="absolute top-0 right-0 bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center">3</span>
                            </button>
                        </div>
                        
                        <!-- Quick Cart -->
                        <a href="{{ route('cart.index') }}" class="p-2 text-gray-600 hover:text-blue-600 relative">
                            <i class="fas fa-shopping-cart text-xl"></i>
                            @php
                                $cartCount = Auth::user()->cart ? count(Auth::user()->cart->items ?? []) : 0;
                            @endphp
                            @if($cartCount > 0)
                            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                                {{ $cartCount }}
                            </span>
                            @endif
                        </a>
                    </div>
                </div>
            </header>
            
            <!-- Content Area -->
            <main class="p-6">
                <!-- Alerts -->
                @if(session('success'))
                <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
                @endif
                
                @if(session('error'))
                <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
                @endif
                
                @if($errors->any())
                <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <ul>
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
                
                <!-- Page Content -->
                @yield('content')
            </main>
        </div>
    </div>
    
    <!-- JavaScript -->
    <script>
        // Mobile menu toggle (if needed)
        function toggleMobileMenu() {
            const sidebar = document.querySelector('.buyer-sidebar');
            sidebar.classList.toggle('hidden');
        }
        
        // Wallet update animation
        document.addEventListener('DOMContentLoaded', function() {
            // Update wallet balance with animation
            const walletBalance = document.querySelector('.wallet-badge .text-xl');
            if (walletBalance) {
                const balance = parseFloat(walletBalance.textContent.replace('$', '').replace(',', ''));
                if (balance > 0) {
                    walletBalance.classList.add('animate-pulse');
                    setTimeout(() => {
                        walletBalance.classList.remove('animate-pulse');
                    }, 2000);
                }
            }
            
            // Mark current active nav item
            const currentPath = window.location.pathname;
            document.querySelectorAll('.nav-item').forEach(item => {
                if (item.getAttribute('href') === currentPath) {
                    item.classList.add('active');
                }
            });
        });
    </script>
    
    @stack('scripts')
</body>
</html>