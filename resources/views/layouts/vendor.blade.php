<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Vendor Dashboard - BebaMart')</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

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
        .vendor-sidebar {
            width: 260px;
            background: linear-gradient(180deg, #4f46e5 0%, #3730a3 100%);
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

        .vendor-content {
            margin-left: 260px;
            padding: 24px;
            min-height: 100vh;
            background: #f8fafc;
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

        /* Mobile Header */
        .mobile-header {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);
            box-shadow: 0 4px 20px rgba(79, 70, 229, 0.3);
            z-index: 40;
            padding: 12px 16px;
            color: white;
        }

        /* Sidebar Overlay */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            z-index: 45;
        }

        .sidebar-overlay.open {
            display: block;
        }

        /* Mobile Bottom Navigation */
        .mobile-bottom-nav {
            display: none;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.1);
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
            color: #4f46e5;
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

        /* Buttons */
        .btn-primary {
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
            color: white;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 500;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.4);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 500;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-secondary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
        }

        .btn-tertiary {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 500;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-tertiary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(139, 92, 246, 0.4);
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
            .vendor-sidebar {
                transform: translateX(-100%);
            }

            .vendor-sidebar.open {
                transform: translateX(0);
            }

            .vendor-content {
                margin-left: 0;
                padding: 16px;
                padding-top: 72px;
                padding-bottom: 90px;
            }

            .mobile-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
            }

            .mobile-bottom-nav {
                display: flex;
            }

            .desktop-header {
                display: none;
            }

            .desktop-actions {
                display: none !important;
            }
        }

        @media (max-width: 640px) {
            .vendor-content {
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
    @stack('styles')
</head>
<body>
    <!-- Mobile Header -->
    <div class="mobile-header">
        <button onclick="toggleSidebar()" class="p-2 -ml-2 text-white/90 hover:text-white transition">
            <i class="fas fa-bars text-xl"></i>
        </button>

        <a href="{{ route('vendor.dashboard') }}" class="flex items-center gap-2">
            <div class="w-8 h-8 bg-white/20 backdrop-blur rounded-lg flex items-center justify-center">
                <i class="fas fa-store text-white text-sm"></i>
            </div>
            <span class="font-bold">Vendor Panel</span>
        </a>

        <a href="{{ route('vendor.orders.index') }}" class="relative p-2 -mr-2 text-white/90 hover:text-white transition">
            <i class="fas fa-shopping-cart text-xl"></i>
            @php
                $pendingOrders = \App\Models\Order::where('vendor_profile_id', auth()->user()->vendorProfile->id ?? 0)
                    ->whereIn('status', ['pending', 'paid'])->count();
            @endphp
            @if($pendingOrders > 0)
            <span class="absolute -top-1 -right-1 bg-orange-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-semibold">
                {{ $pendingOrders > 9 ? '9+' : $pendingOrders }}
            </span>
            @endif
        </a>
    </div>

    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>

    <!-- Sidebar -->
    <div class="vendor-sidebar" id="sidebar">
        <!-- Close button for mobile -->
        <button onclick="toggleSidebar()" class="lg:hidden absolute top-4 right-4 text-white/80 hover:text-white p-2 transition">
            <i class="fas fa-times text-xl"></i>
        </button>

        <!-- Header -->
        <div class="p-5 border-b border-indigo-600/30">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 bg-white/15 backdrop-blur rounded-xl flex items-center justify-center">
                    <i class="fas fa-store text-white text-lg"></i>
                </div>
                <div>
                    <h2 class="font-bold text-lg">Vendor Panel</h2>
                    <p class="text-xs text-white/70 truncate max-w-[150px]">{{ auth()->user()->vendorProfile->business_name ?? 'My Store' }}</p>
                </div>
            </div>
        </div>

        <!-- User Info -->
        <div class="p-4 border-b border-indigo-600/30">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-indigo-400/30 rounded-full flex items-center justify-center">
                    @if(auth()->user()->avatar)
                        <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="" class="w-10 h-10 rounded-full object-cover">
                    @else
                        <i class="fas fa-user text-white/80"></i>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <div class="font-semibold text-sm truncate">{{ Auth::user()->name }}</div>
                    <div class="text-xs text-white/60">Vendor Account</div>
                </div>
            </div>

            @php $vendor = auth()->user()->vendorProfile; @endphp
            @if($vendor)
            <div class="mt-3 flex items-center justify-between">
                <span class="text-xs text-white/60">Status</span>
                @php
                    $statusColors = [
                        'pending' => 'bg-yellow-500',
                        'approved' => 'bg-emerald-500',
                        'rejected' => 'bg-red-500',
                    ];
                @endphp
                <span class="{{ $statusColors[$vendor->vetting_status ?? 'pending'] ?? 'bg-gray-500' }} text-white text-xs px-2.5 py-1 rounded-full font-medium">
                    {{ ucfirst($vendor->vetting_status ?? 'pending') }}
                </span>
            </div>
            @endif
        </div>

        <!-- Navigation -->
        <nav class="py-4 overflow-y-auto" style="max-height: calc(100vh - 280px);">
            <!-- Main -->
            <a href="{{ route('vendor.dashboard') }}" class="nav-item {{ request()->routeIs('vendor.dashboard') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>

            <!-- Products Section -->
            <div class="nav-section-title">Products</div>

            <a href="{{ route('vendor.listings.index') }}" class="nav-item {{ request()->is('vendor/listings*') ? 'active' : '' }}">
                <i class="fas fa-boxes"></i> My Listings
            </a>

            <a href="{{ route('vendor.orders.index') }}" class="nav-item {{ request()->is('vendor/orders*') ? 'active' : '' }}">
                <i class="fas fa-shopping-cart"></i> Orders
                @if($pendingOrders > 0)
                <span class="nav-badge bg-orange-500">{{ $pendingOrders }}</span>
                @endif
            </a>

            <a href="{{ route('buyer.orders.index') }}" class="nav-item {{ request()->routeIs('buyer.orders*') ? 'active' : '' }}">
                <i class="fas fa-shopping-bag"></i> My Purchases
            </a>

            <!-- Jobs & Services Section -->
            <div class="nav-section-title">Jobs & Services</div>

            <a href="{{ route('vendor.jobs.index') }}" class="nav-item {{ request()->is('vendor/jobs*') ? 'active' : '' }}">
                <i class="fas fa-briefcase"></i> Job Listings
                @php
                    $pendingApps = \App\Models\JobApplication::whereHas('job', fn($q) => $q->where('vendor_profile_id', auth()->user()->vendorProfile->id ?? 0))
                        ->where('status', 'pending')->count();
                @endphp
                @if($pendingApps > 0)
                <span class="nav-badge bg-blue-500">{{ $pendingApps }}</span>
                @endif
            </a>

            <a href="{{ route('vendor.services.index') }}" class="nav-item {{ request()->is('vendor/services') ? 'active' : '' }}">
                <i class="fas fa-tools"></i> My Services
            </a>

            <a href="{{ route('vendor.services.requests') }}" class="nav-item {{ request()->is('vendor/services/requests*') ? 'active' : '' }}">
                <i class="fas fa-clipboard-list"></i> Service Requests
                @php
                    $pendingRequests = \App\Models\ServiceRequest::where('vendor_profile_id', auth()->user()->vendorProfile->id ?? 0)
                        ->where('status', 'pending')->count();
                @endphp
                @if($pendingRequests > 0)
                <span class="nav-badge bg-green-500">{{ $pendingRequests }}</span>
                @endif
            </a>

            <!-- Communication Section -->
            <div class="nav-section-title">Communication</div>

            <a href="{{ route('vendor.callbacks.index') }}" class="nav-item {{ request()->is('vendor/callbacks*') ? 'active' : '' }}">
                <i class="fas fa-phone-alt"></i> Callbacks
                @php
                    $pendingCallbacks = \App\Models\CallbackRequest::where('vendor_profile_id', auth()->user()->vendorProfile->id ?? 0)
                        ->where('status', 'pending')->count();
                @endphp
                @if($pendingCallbacks > 0)
                <span class="nav-badge bg-orange-500">{{ $pendingCallbacks }}</span>
                @endif
            </a>

            <a href="{{ route('chat.index') }}" class="nav-item {{ request()->is('chat*') ? 'active' : '' }}">
                <i class="fas fa-comments"></i> Messages
                <span id="chatBadge" class="nav-badge bg-red-500 hidden">0</span>
            </a>

            <a href="{{ route('vendor.services.inquiries') }}" class="nav-item {{ request()->is('vendor/services/inquiries*') ? 'active' : '' }}">
                <i class="fas fa-envelope"></i> Inquiries
                @php
                    $newInquiries = \App\Models\ServiceInquiry::where('vendor_profile_id', auth()->user()->vendorProfile->id ?? 0)
                        ->where('status', 'new')->count();
                @endphp
                @if($newInquiries > 0)
                <span class="nav-badge bg-purple-500">{{ $newInquiries }}</span>
                @endif
            </a>

            <!-- More Section -->
            <div class="nav-section-title">More</div>

            <a href="{{ route('vendor.subscription.index') }}" class="nav-item {{ request()->is('vendor/subscription*') ? 'active' : '' }}">
                <i class="fas fa-crown"></i> Subscription
                @php
                    $vendorSub = auth()->user()->vendorProfile?->activeSubscription;
                    $subPlan = $vendorSub?->plan;
                @endphp
                @if($subPlan && !$subPlan->is_free_plan)
                    <span class="nav-badge {{ $subPlan->slug == 'gold' ? 'bg-yellow-500' : ($subPlan->slug == 'silver' ? 'bg-gray-400' : 'bg-orange-500') }}">
                        {{ strtoupper(substr($subPlan->name, 0, 1)) }}
                    </span>
                @endif
            </a>

            <a href="{{ route('vendor.services.reviews') }}" class="nav-item {{ request()->is('vendor/services/reviews*') ? 'active' : '' }}">
                <i class="fas fa-star"></i> Reviews
            </a>

            <a href="{{ route('vendor.imports.index') }}" class="nav-item {{ request()->is('vendor/imports*') ? 'active' : '' }}">
                <i class="fas fa-plane"></i> Import Goods
            </a>

            <a href="{{ route('vendor.promotions.index') }}" class="nav-item {{ request()->is('vendor/promotions*') ? 'active' : '' }}">
                <i class="fas fa-bullhorn"></i> Promotions
            </a>

            <a href="{{ route('vendor.analytics') }}" class="nav-item {{ request()->is('vendor/analytics*') ? 'active' : '' }}">
                <i class="fas fa-chart-line"></i> Analytics
            </a>

            <a href="{{ route('vendor.profile.show') }}" class="nav-item {{ request()->is('vendor/profile*') ? 'active' : '' }}">
                <i class="fas fa-user-circle"></i> Profile
            </a>
        </nav>

        <!-- Footer -->
        <div class="p-4 border-t border-indigo-600/30 mt-auto">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center gap-2 text-sm text-white/70 hover:text-white py-2.5 hover:bg-white/10 rounded-lg transition">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </form>
        </div>
    </div>

    <!-- Main Content -->
    <div class="vendor-content">
        <!-- Desktop Header -->
        <div class="desktop-header">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">@yield('page_title', 'Vendor Dashboard')</h1>
                    <p class="text-gray-500 text-sm mt-1">@yield('page_description', '')</p>
                </div>
                <div class="flex items-center gap-3 desktop-actions">
                    <a href="{{ route('vendor.listings.create') }}" class="btn-primary">
                        <i class="fas fa-plus"></i> Add Listing
                    </a>
                    <a href="{{ route('vendor.jobs.create') }}" class="btn-secondary">
                        <i class="fas fa-briefcase"></i> Post Job
                    </a>
                    <a href="{{ route('vendor.services.create') }}" class="btn-tertiary">
                        <i class="fas fa-tools"></i> Add Service
                    </a>
                </div>
            </div>
        </div>

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

    <!-- Mobile Bottom Navigation -->
    <nav class="mobile-bottom-nav">
        <a href="{{ route('vendor.dashboard') }}" class="{{ request()->routeIs('vendor.dashboard') ? 'active' : '' }}">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
        <a href="{{ route('vendor.listings.index') }}" class="{{ request()->is('vendor/listings*') ? 'active' : '' }}">
            <i class="fas fa-boxes"></i>
            <span>Products</span>
        </a>
        <a href="{{ route('vendor.orders.index') }}" class="relative {{ request()->is('vendor/orders*') ? 'active' : '' }}">
            <i class="fas fa-receipt"></i>
            <span>Orders</span>
            @if($pendingOrders > 0)
            <span class="nav-badge-mobile bg-orange-500">{{ $pendingOrders > 9 ? '9+' : $pendingOrders }}</span>
            @endif
        </a>
        <a href="{{ route('chat.index') }}" class="relative {{ request()->is('chat*') ? 'active' : '' }}">
            <i class="fas fa-comments"></i>
            <span>Chat</span>
            <span id="chatBadgeMobile" class="nav-badge-mobile bg-red-500 hidden">0</span>
        </a>
        <a href="{{ route('vendor.profile.show') }}" class="{{ request()->is('vendor/profile*') ? 'active' : '' }}">
            <i class="fas fa-user"></i>
            <span>Profile</span>
        </a>
    </nav>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

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
    document.querySelectorAll('.vendor-sidebar .nav-item').forEach(link => {
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
                const badgeMobile = document.getElementById('chatBadgeMobile');

                if (data.unread_count > 0) {
                    const count = data.unread_count > 9 ? '9+' : data.unread_count;

                    if (badge) {
                        badge.textContent = count;
                        badge.classList.remove('hidden');
                    }
                    if (badgeMobile) {
                        badgeMobile.textContent = count;
                        badgeMobile.classList.remove('hidden');
                    }
                } else {
                    if (badge) badge.classList.add('hidden');
                    if (badgeMobile) badgeMobile.classList.add('hidden');
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

    @stack('scripts')
</body>
</html>
