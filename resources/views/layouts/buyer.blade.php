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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: #f8fafc;
        }

        /* Sidebar */
        .buyer-sidebar {
            width: 260px;
            background: linear-gradient(180deg, #1e40af 0%, #1e3a8a 100%);
            color: white;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            overflow-y: auto;
            z-index: 50;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.15);
        }

        .buyer-content {
            margin-left: 260px;
            padding: 24px;
            min-height: 100vh;
            background: #f8fafc;
            padding-bottom: 40px;
            transition: margin-left 0.3s ease;
        }

        .nav-section-title {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0.6;
            padding: 16px 20px 8px;
            margin-top: 8px;
            font-weight: 600;
        }

        .nav-item {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            transition: all 0.2s ease;
            border-left: 3px solid transparent;
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }

        .nav-item:hover {
            background: rgba(255,255,255,0.1);
            border-left-color: rgba(255,255,255,0.5);
            color: white;
        }

        .nav-item.active {
            background: rgba(255,255,255,0.15);
            border-left-color: #fff;
            color: white;
        }

        .nav-item i {
            width: 20px;
            text-align: center;
            margin-right: 12px;
            font-size: 15px;
        }

        .nav-badge {
            margin-left: auto;
            min-width: 22px;
            height: 22px;
            padding: 0 7px;
            border-radius: 11px;
            font-size: 11px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .wallet-badge {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border-radius: 12px;
            padding: 16px;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }

        /* Mobile sidebar overlay */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            backdrop-filter: blur(4px);
            z-index: 45;
        }

        .sidebar-overlay.open {
            display: block;
        }

        /* Mobile bottom navigation */
        .mobile-bottom-nav {
            display: none;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            box-shadow: 0 -4px 20px rgba(0,0,0,0.1);
            z-index: 35;
            padding: 8px 0;
            padding-bottom: max(8px, env(safe-area-inset-bottom));
        }

        .mobile-bottom-nav a {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 6px 4px;
            color: #6b7280;
            font-size: 10px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s;
            position: relative;
        }

        .mobile-bottom-nav a.active {
            color: #1e40af;
        }

        .mobile-bottom-nav a i {
            font-size: 20px;
            margin-bottom: 4px;
        }

        .mobile-bottom-nav .nav-badge-mobile {
            position: absolute;
            top: 0;
            right: 50%;
            transform: translateX(14px);
            min-width: 16px;
            height: 16px;
            padding: 0 4px;
            border-radius: 8px;
            font-size: 10px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #ef4444;
            color: white;
        }

        /* Mobile header */
        .mobile-header {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
            box-shadow: 0 4px 20px rgba(30, 64, 175, 0.3);
            z-index: 40;
            padding: 12px 16px;
            color: white;
        }

        /* Cards */
        .card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            border: 1px solid #e5e7eb;
            transition: all 0.2s ease;
        }

        .card:hover {
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        }

        /* Alerts */
        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .alert-success {
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
            border: 1px solid #a7f3d0;
            color: #065f46;
        }

        .alert-error {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        .alert-info {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border: 1px solid #bfdbfe;
            color: #1e40af;
        }

        /* Desktop Header */
        .desktop-header {
            background: white;
            border-radius: 16px;
            padding: 20px 24px;
            margin-bottom: 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            border: 1px solid #e5e7eb;
        }

        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.5);
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .buyer-sidebar {
                transform: translateX(-100%);
            }

            .buyer-sidebar.open {
                transform: translateX(0);
            }

            .buyer-content {
                margin-left: 0;
                padding: 16px;
                padding-top: 72px;
                padding-bottom: 90px;
            }

            .mobile-bottom-nav {
                display: flex;
            }

            .mobile-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
            }

            .desktop-header {
                display: none;
            }
        }

        @media (max-width: 640px) {
            .buyer-content {
                padding: 12px;
                padding-top: 68px;
                padding-bottom: 85px;
            }

            .card {
                border-radius: 12px;
            }

            .alert {
                padding: 12px 16px;
                border-radius: 10px;
            }
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Mobile Header -->
    <div class="mobile-header">
        <button onclick="toggleSidebar()" class="p-2 -ml-2 text-white/90 hover:text-white transition">
            <i class="fas fa-bars text-xl"></i>
        </button>

        <a href="{{ route('buyer.dashboard') }}" class="flex items-center gap-2">
            <div class="w-8 h-8 bg-white/20 backdrop-blur rounded-lg flex items-center justify-center">
                <i class="fas fa-shopping-bag text-white text-sm"></i>
            </div>
            <span class="font-bold">BebaMart</span>
        </a>

        <a href="{{ route('buyer.cart.index') }}" class="relative p-2 -mr-2 text-white/90 hover:text-white transition">
            <i class="fas fa-shopping-cart text-xl"></i>
            @php $cartCount = Auth::user()->cart ? count(Auth::user()->cart->items ?? []) : 0; @endphp
            @if($cartCount > 0)
            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-semibold cart-count">
                {{ $cartCount > 9 ? '9+' : $cartCount }}
            </span>
            @endif
        </a>
    </div>

    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>

    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <div class="buyer-sidebar" id="sidebar">
            <!-- Close button for mobile -->
            <button onclick="toggleSidebar()" class="lg:hidden absolute top-4 right-4 text-white/80 hover:text-white p-2 transition">
                <i class="fas fa-times text-xl"></i>
            </button>

            <!-- Header -->
            <div class="p-5 border-b border-blue-700/30">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 bg-white/15 backdrop-blur rounded-xl flex items-center justify-center">
                        <i class="fas fa-shopping-bag text-white text-lg"></i>
                    </div>
                    <div>
                        <h2 class="font-bold text-lg">Buyer Dashboard</h2>
                        <p class="text-xs text-white/70">BebaMart</p>
                    </div>
                </div>
            </div>

            <!-- User Info & Wallet -->
            <div class="p-4 border-b border-blue-700/30">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-blue-400/30 rounded-full flex items-center justify-center">
                        @if(Auth::user()->avatar)
                            <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="" class="w-10 h-10 rounded-full object-cover">
                        @else
                            <i class="fas fa-user text-white/80"></i>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-semibold text-sm truncate">{{ Auth::user()->name }}</div>
                        <div class="text-xs text-white/60">Buyer Account</div>
                    </div>
                </div>

                @php
                    $wallet = Auth::user()->buyerWallet;
                    $balance = $wallet ? $wallet->balance : 0;
                @endphp
                <div class="wallet-badge">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs text-white/80">Wallet Balance</span>
                        <i class="fas fa-wallet text-white/60"></i>
                    </div>
                    <div class="text-xl font-bold">UGX {{ number_format($balance, 0) }}</div>
                    <a href="{{ route('buyer.wallet.index') }}" class="text-xs text-white/80 hover:text-white mt-2 inline-flex items-center gap-1 transition">
                        Add Funds <i class="fas fa-arrow-right text-xs"></i>
                    </a>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="py-4 overflow-y-auto" style="max-height: calc(100vh - 320px);">
                <a href="{{ route('buyer.dashboard') }}" class="nav-item {{ request()->routeIs('buyer.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>

                <a href="{{ route('marketplace.index') }}" class="nav-item">
                    <i class="fas fa-store"></i> Marketplace
                </a>

                <!-- Shopping Section -->
                <div class="nav-section-title">Shopping</div>

                <a href="{{ route('buyer.cart.index') }}" class="nav-item {{ request()->routeIs('buyer.cart.*') ? 'active' : '' }}">
                    <i class="fas fa-shopping-cart"></i> Cart
                    @if($cartCount > 0)
                    <span class="nav-badge bg-red-500">{{ $cartCount }}</span>
                    @endif
                </a>

                <a href="{{ route('buyer.orders.index') }}" class="nav-item {{ request()->routeIs('buyer.orders.*') ? 'active' : '' }}">
                    <i class="fas fa-shopping-bag"></i> My Orders
                </a>

                <a href="{{ route('buyer.wishlist.index') }}" class="nav-item {{ request()->routeIs('buyer.wishlist.*') ? 'active' : '' }}">
                    <i class="fas fa-heart"></i> Wishlist
                </a>

                <!-- Jobs & Services Section -->
                <div class="nav-section-title">Jobs & Services</div>

                <a href="{{ route('jobs.index') }}" class="nav-item {{ request()->is('jobs') ? 'active' : '' }}">
                    <i class="fas fa-briefcase"></i> Browse Jobs
                </a>

                <a href="{{ route('services.index') }}" class="nav-item {{ request()->is('services') ? 'active' : '' }}">
                    <i class="fas fa-tools"></i> Browse Services
                </a>

                <a href="{{ route('buyer.applications.index') }}" class="nav-item {{ request()->is('buyer/my-applications*') ? 'active' : '' }}">
                    <i class="fas fa-file-alt"></i> My Applications
                    @php
                        $activeApps = \App\Models\JobApplication::where('user_id', auth()->id())
                            ->whereIn('status', ['pending', 'reviewed', 'shortlisted'])->count();
                    @endphp
                    @if($activeApps > 0)
                    <span class="nav-badge bg-blue-500">{{ $activeApps }}</span>
                    @endif
                </a>

                <a href="{{ route('buyer.service-requests.index') }}" class="nav-item {{ request()->is('buyer/service-requests*') ? 'active' : '' }}">
                    <i class="fas fa-clipboard-list"></i> Service Requests
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
                    <i class="fas fa-comments"></i> Messages
                    <span id="chatBadge" class="nav-badge bg-red-500 hidden">0</span>
                </a>

                <a href="{{ route('buyer.wallet.index') }}" class="nav-item {{ request()->routeIs('buyer.wallet.*') ? 'active' : '' }}">
                    <i class="fas fa-wallet"></i> Wallet
                </a>

                <a href="{{ route('buyer.disputes.index') }}" class="nav-item {{ request()->routeIs('buyer.disputes.*') ? 'active' : '' }}">
                    <i class="fas fa-exclamation-triangle"></i> Disputes
                </a>

                <a href="{{ route('buyer.profile') }}" class="nav-item {{ request()->routeIs('buyer.profile') ? 'active' : '' }}">
                    <i class="fas fa-user-circle"></i> Profile
                </a>

                <!-- Quick Links -->
                <div class="nav-section-title">Quick Links</div>

                <a href="{{ route('categories.index') }}" class="nav-item">
                    <i class="fas fa-tags"></i> Categories
                </a>

                <a href="{{ route('vendor.onboard.create') }}" class="nav-item">
                    <i class="fas fa-store-alt"></i> Become a Seller
                </a>
            </nav>

            <!-- Footer -->
            <div class="p-4 border-t border-blue-700/30 mt-auto">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center gap-2 text-sm text-white/70 hover:text-white py-2.5 hover:bg-white/10 rounded-lg transition">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </form>
            </div>
        </div>

        <!-- Main Content -->
        <div class="buyer-content flex-1">
            <!-- Desktop Top Bar -->
            <header class="desktop-header">
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">@yield('page_title', 'Dashboard')</h1>
                        <p class="text-gray-500 text-sm mt-1">@yield('page_description', '')</p>
                    </div>

                    <div class="flex items-center gap-4">
                        <!-- Search -->
                        <form action="{{ route('marketplace.index') }}" method="GET" class="flex">
                            <input type="text" name="search" placeholder="Search products..."
                                   class="px-4 py-2.5 border border-gray-200 rounded-l-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent w-64 text-sm bg-gray-50">
                            <button type="submit" class="bg-blue-600 text-white px-5 py-2.5 rounded-r-xl hover:bg-blue-700 transition">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>

                        <!-- Quick Cart -->
                        <a href="{{ route('buyer.cart.index') }}" class="relative p-2.5 text-gray-500 hover:text-blue-600 bg-gray-100 rounded-xl hover:bg-blue-50 transition">
                            <i class="fas fa-shopping-cart text-xl"></i>
                            @if($cartCount > 0)
                            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-semibold cart-count">
                                {{ $cartCount > 9 ? '9+' : $cartCount }}
                            </span>
                            @endif
                        </a>
                    </div>
                </div>
            </header>

            <!-- Alerts -->
            @if(session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle text-emerald-500 text-lg"></i>
                <span>{{ session('success') }}</span>
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle text-red-500 text-lg"></i>
                <span>{{ session('error') }}</span>
            </div>
            @endif

            @if(session('info'))
            <div class="alert alert-info">
                <i class="fas fa-info-circle text-blue-500 text-lg"></i>
                <span>{{ session('info') }}</span>
            </div>
            @endif

            @if($errors->any())
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle text-red-500 text-lg"></i>
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

    <!-- Mobile Bottom Navigation -->
    <nav class="mobile-bottom-nav">
        <a href="{{ route('buyer.dashboard') }}" class="{{ request()->routeIs('buyer.dashboard') ? 'active' : '' }}">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
        <a href="{{ route('marketplace.index') }}" class="{{ request()->routeIs('marketplace.*') ? 'active' : '' }}">
            <i class="fas fa-store"></i>
            <span>Shop</span>
        </a>
        <a href="{{ route('buyer.cart.index') }}" class="relative {{ request()->routeIs('buyer.cart.*') ? 'active' : '' }}">
            <i class="fas fa-shopping-cart"></i>
            <span>Cart</span>
            @if($cartCount > 0)
            <span class="nav-badge-mobile cart-count">{{ $cartCount > 9 ? '9+' : $cartCount }}</span>
            @endif
        </a>
        <a href="{{ route('buyer.wishlist.index') }}" class="{{ request()->routeIs('buyer.wishlist.*') ? 'active' : '' }}">
            <i class="fas fa-heart"></i>
            <span>Wishlist</span>
        </a>
        <a href="{{ route('buyer.orders.index') }}" class="{{ request()->routeIs('buyer.orders.*') ? 'active' : '' }}">
            <i class="fas fa-shopping-bag"></i>
            <span>Orders</span>
        </a>
        <a href="{{ route('buyer.profile') }}" class="{{ request()->routeIs('buyer.profile') ? 'active' : '' }}">
            <i class="fas fa-user"></i>
            <span>Profile</span>
        </a>
    </nav>

    <script>
    // Toggle sidebar
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.querySelector('.sidebar-overlay');

        sidebar.classList.toggle('open');
        overlay.classList.toggle('open');

        // Prevent body scroll when sidebar is open
        document.body.style.overflow = sidebar.classList.contains('open') ? 'hidden' : '';
    }

    // Close sidebar when clicking a link (mobile)
    document.querySelectorAll('.buyer-sidebar .nav-item').forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 1024) {
                toggleSidebar();
            }
        });
    });

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

    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 1024) {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            sidebar.classList.remove('open');
            overlay.classList.remove('open');
            document.body.style.overflow = '';
        }
    });
    </script>

    @yield('scripts')
    @stack('scripts')
</body>
</html>
