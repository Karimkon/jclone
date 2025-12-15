<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Buyer Dashboard - BebaMart')</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .buyer-sidebar {
            width: 240px;
            background: linear-gradient(180deg, #1e40af 0%, #1e3a8a 100%);
            color: white;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            overflow-y: auto;
        }
        
        .buyer-content {
            margin-left: 240px;
            padding: 20px;
            min-height: 100vh;
            background: #f8fafc;
        }
        
        .nav-section-title {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0.7;
            padding: 12px 20px 6px;
            margin-top: 8px;
        }
        
        .nav-item {
            display: flex;
            align-items: center;
            padding: 10px 20px;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }
        
        .nav-item:hover {
            background: rgba(255,255,255,0.1);
            border-left-color: rgba(255,255,255,0.5);
        }
        
        .nav-item.active {
            background: rgba(255,255,255,0.15);
            border-left-color: #fff;
        }
        
        .nav-badge {
            margin-left: auto;
            min-width: 20px;
            height: 20px;
            padding: 0 6px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .wallet-badge {
            background: linear-gradient(135deg, #10b981, #059669);
        }
        
        @media (max-width: 768px) {
            .buyer-sidebar { width: 100%; height: auto; position: relative; }
            .buyer-content { margin-left: 0; }
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <div class="buyer-sidebar">
            <!-- Header -->
            <div class="p-5 border-b border-blue-800">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center">
                        <i class="fas fa-shopping-bag text-blue-600"></i>
                    </div>
                    <div>
                        <h2 class="font-bold">Buyer Dashboard</h2>
                        <p class="text-xs opacity-75">BebaMart</p>
                    </div>
                </div>
            </div>
            
            <!-- User Info & Wallet -->
            <div class="p-4 border-b border-blue-800">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center">
                        <i class="fas fa-user"></i>
                    </div>
                    <div>
                        <div class="font-semibold text-sm">{{ Auth::user()->name }}</div>
                        <div class="text-xs opacity-75">Buyer Account</div>
                    </div>
                </div>
                
                @php
                    $wallet = Auth::user()->buyerWallet;
                    $balance = $wallet ? $wallet->balance : 0;
                @endphp
                <div class="wallet-badge rounded-lg p-3">
                    <div class="text-xs opacity-90">Wallet Balance</div>
                    <div class="text-lg font-bold">UGX {{ number_format($balance, 0) }}</div>
                    <a href="{{ route('buyer.wallet.index') }}" class="text-xs hover:underline">Add Funds →</a>
                </div>
            </div>
            
            <!-- Navigation -->
            <nav class="py-4">
                <a href="{{ route('buyer.dashboard') }}" class="nav-item {{ request()->routeIs('buyer.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt w-5 mr-3"></i> Dashboard
                </a>
                
                <a href="{{ route('marketplace.index') }}" class="nav-item">
                    <i class="fas fa-store w-5 mr-3"></i> Marketplace
                </a>
                
                <!-- Shopping Section -->
                <div class="nav-section-title">Shopping</div>
                
                <a href="{{ route('buyer.cart.index') }}" class="nav-item {{ request()->routeIs('buyer.cart.*') ? 'active' : '' }}">
                    <i class="fas fa-shopping-cart w-5 mr-3"></i> Cart
                    @php $cartCount = Auth::user()->cart ? count(Auth::user()->cart->items ?? []) : 0; @endphp
                    @if($cartCount > 0)
                    <span class="nav-badge bg-red-500">{{ $cartCount }}</span>
                    @endif
                </a>
                
                <a href="{{ route('buyer.orders.index') }}" class="nav-item {{ request()->routeIs('buyer.orders.*') ? 'active' : '' }}">
                    <i class="fas fa-shopping-bag w-5 mr-3"></i> My Orders
                </a>
                
                <a href="{{ route('buyer.wishlist.index') }}" class="nav-item {{ request()->routeIs('buyer.wishlist.*') ? 'active' : '' }}">
                    <i class="fas fa-heart w-5 mr-3"></i> Wishlist
                </a>
                
                <!-- Jobs & Services Section -->
                <div class="nav-section-title">Jobs & Services</div>
                
                <a href="{{ route('jobs.index') }}" class="nav-item {{ request()->is('jobs') ? 'active' : '' }}">
                    <i class="fas fa-briefcase w-5 mr-3"></i> Browse Jobs
                </a>
                
                <a href="{{ route('services.index') }}" class="nav-item {{ request()->is('services') ? 'active' : '' }}">
                    <i class="fas fa-tools w-5 mr-3"></i> Browse Services
                </a>
                
                <a href="{{ route('buyer.applications.index') }}" class="nav-item {{ request()->is('buyer/my-applications*') ? 'active' : '' }}">
                    <i class="fas fa-file-alt w-5 mr-3"></i> My Applications
                    @php
                        $activeApps = \App\Models\JobApplication::where('user_id', auth()->id())
                            ->whereIn('status', ['pending', 'reviewed', 'shortlisted'])->count();
                    @endphp
                    @if($activeApps > 0)
                    <span class="nav-badge bg-blue-500">{{ $activeApps }}</span>
                    @endif
                </a>
                
                <a href="{{ route('buyer.service-requests.index') }}" class="nav-item {{ request()->is('buyer/service-requests*') ? 'active' : '' }}">
                    <i class="fas fa-clipboard-list w-5 mr-3"></i> Service Requests
                    @php
                        $activeRequests = \App\Models\ServiceRequest::where('user_id', auth()->id())
                            ->whereIn('status', ['pending', 'quoted', 'accepted', 'in_progress'])->count();
                    @endphp
                    @if($activeRequests > 0)
                    <span class="nav-badge bg-green-500">{{ $activeRequests }}</span>
                    @endif
                </a>
                
                <!-- Account Section -->
                <div class="nav-section-title">Account</div>
                
                <a href="{{ route('chat.index') }}" class="nav-item {{ request()->is('chat*') ? 'active' : '' }}">
                    <i class="fas fa-comments w-5 mr-3"></i> Messages
                    <span id="chatBadge" class="nav-badge bg-red-500 hidden">0</span>
                </a>
                
                <a href="{{ route('buyer.wallet.index') }}" class="nav-item {{ request()->routeIs('buyer.wallet.*') ? 'active' : '' }}">
                    <i class="fas fa-wallet w-5 mr-3"></i> Wallet
                </a>
                
                <a href="{{ route('buyer.disputes.index') }}" class="nav-item {{ request()->routeIs('buyer.disputes.*') ? 'active' : '' }}">
                    <i class="fas fa-exclamation-triangle w-5 mr-3"></i> Disputes
                </a>
                
                <a href="{{ route('buyer.profile') }}" class="nav-item {{ request()->routeIs('buyer.profile') ? 'active' : '' }}">
                    <i class="fas fa-user-circle w-5 mr-3"></i> Profile
                </a>
                
                <!-- Quick Links -->
                <div class="nav-section-title">Quick Links</div>
                
                <a href="{{ route('categories.index') }}" class="nav-item text-sm">
                    <i class="fas fa-tags w-5 mr-3"></i> Categories
                </a>
                
                <a href="{{ route('vendor.onboard.create') }}" class="nav-item text-sm">
                    <i class="fas fa-store-alt w-5 mr-3"></i> Become a Seller
                </a>
            </nav>
            
            <!-- Footer -->
            <div class="p-4 border-t border-blue-800 mt-auto">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full text-left text-sm opacity-75 hover:opacity-100 py-2">
                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="buyer-content flex-1">
            <!-- Top Bar -->
            <header class="bg-white shadow-sm rounded-lg mb-6 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-xl font-bold text-gray-800">@yield('page_title', 'Dashboard')</h1>
                        <p class="text-sm text-gray-600">@yield('page_description', '')</p>
                    </div>
                    
                    <div class="flex items-center gap-4">
                        <!-- Search -->
                        <form action="{{ route('marketplace.index') }}" method="GET" class="flex">
                            <input type="text" name="search" placeholder="Search products..." 
                                   class="px-4 py-2 border border-gray-300 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-blue-500 w-64 text-sm">
                            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-r-lg hover:bg-blue-700">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                        
                        <!-- Quick Cart -->
                        <a href="{{ route('buyer.cart.index') }}" class="relative p-2 text-gray-600 hover:text-blue-600">
                            <i class="fas fa-shopping-cart text-xl"></i>
                            @if($cartCount > 0)
                            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                                {{ $cartCount }}
                            </span>
                            @endif
                        </a>
                    </div>
                </div>
            </header>
            
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
            
            @if(session('info'))
            <div class="mb-6 bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded">
                {{ session('info') }}
            </div>
            @endif
            
            @if($errors->any())
            <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
            
            <!-- Page Content -->
            @yield('content')
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        updateChatBadge();
        setInterval(updateChatBadge, 30000);
    });

    async function updateChatBadge() {
        try {
            const response = await fetch('/chat/api/unread-count', {
                headers: { 'Accept': 'application/json' }
            });
            const data = await response.json();
            if (data.success) {
                const badge = document.getElementById('chatBadge');
                if (data.unread_count > 0) {
                    badge.textContent = data.unread_count > 9 ? '9+' : data.unread_count;
                    badge.classList.remove('hidden');
                } else {
                    badge.classList.add('hidden');
                }
            }
        } catch (error) {
            console.error('Failed to update chat badge:', error);
        }
    }
    </script>
    
    @yield('scripts')
    @stack('scripts')
</body>
</html>
