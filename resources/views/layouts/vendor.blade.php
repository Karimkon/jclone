<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Vendor Dashboard - JClone')</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Select2 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    
    <style>
        :root {
            --sidebar-bg: #1e293b;
            --sidebar-active: #3b82f6;
            --sidebar-hover: #334155;
            --primary-color: #3b82f6;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
        }
        
        /* Modern Sidebar */
        .vendor-sidebar {
            width: 280px;
            background: var(--sidebar-bg);
            color: white;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            border-right: 1px solid #334155;
            box-shadow: 4px 0 15px rgba(0, 0, 0, 0.1);
            transition: width 0.3s ease;
            z-index: 1000;
        }
        
        .sidebar-collapsed {
            width: 80px;
        }
        
        .vendor-content {
            margin-left: 280px;
            padding: 24px;
            min-height: 100vh;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            transition: margin-left 0.3s ease;
        }
        
        .content-collapsed {
            margin-left: 80px;
        }
        
        /* Logo/Brand Area */
        .brand-area {
            padding: 24px;
            border-bottom: 1px solid #334155;
            height: 80px;
        }
        
        .brand-content {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .brand-logo {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        
        .brand-text {
            transition: opacity 0.3s ease;
        }
        
        .sidebar-collapsed .brand-text {
            opacity: 0;
            width: 0;
        }
        
        /* Navigation */
        .nav-container {
            padding: 16px 0;
            height: calc(100vh - 180px);
            overflow-y: auto;
        }
        
        .nav-container::-webkit-scrollbar {
            width: 4px;
        }
        
        .nav-container::-webkit-scrollbar-track {
            background: transparent;
        }
        
        .nav-container::-webkit-scrollbar-thumb {
            background: #475569;
            border-radius: 2px;
        }
        
        .nav-item {
            display: flex;
            align-items: center;
            padding: 14px 24px;
            margin: 4px 12px;
            border-radius: 10px;
            color: #cbd5e1;
            text-decoration: none;
            transition: all 0.2s ease;
            position: relative;
            overflow: hidden;
        }
        
        .nav-item:hover {
            background: var(--sidebar-hover);
            color: white;
            transform: translateX(5px);
        }
        
        .nav-item.active {
            background: var(--sidebar-active);
            color: white;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }
        
        .nav-item.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background: white;
            border-radius: 0 2px 2px 0;
        }
        
        .nav-icon {
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }
        
        .nav-label {
            margin-left: 16px;
            font-weight: 500;
            white-space: nowrap;
            transition: opacity 0.3s ease;
        }
        
        .sidebar-collapsed .nav-label {
            opacity: 0;
            width: 0;
        }
        
        .badge {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            min-width: 20px;
            height: 20px;
            background: #ef4444;
            color: white;
            font-size: 11px;
            font-weight: 600;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 6px;
        }
        
        .sidebar-collapsed .badge {
            right: 8px;
            transform: translateY(-50%) scale(0.8);
        }
        
        /* Account Status Area */
        .account-area {
            position: absolute;
            bottom: 0;
            width: 100%;
            padding: 20px;
            border-top: 1px solid #334155;
            background: rgba(15, 23, 42, 0.5);
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            gap: 6px;
        }
        
        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }
        
        .status-approved .status-dot { background: #10b981; }
        .status-pending .status-dot { background: #f59e0b; }
        .status-rejected .status-dot { background: #ef4444; }
        
        .status-approved { background: rgba(16, 185, 129, 0.15); color: #10b981; }
        .status-pending { background: rgba(245, 158, 11, 0.15); color: #f59e0b; }
        .status-rejected { background: rgba(239, 68, 68, 0.15); color: #ef4444; }
        
        .logout-btn {
            width: 100%;
            display: flex;
            align-items: center;
            padding: 12px;
            margin-top: 12px;
            background: rgba(239, 68, 68, 0.1);
            color: #f87171;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .logout-btn:hover {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
        }
        
        .sidebar-collapsed .logout-btn span {
            display: none;
        }
        
        /* Toggle Button */
        .sidebar-toggle {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1001;
            width: 40px;
            height: 40px;
            background: white;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            color: var(--sidebar-bg);
            font-size: 18px;
        }
        
        /* Main Content Header */
        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
            padding: 20px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        
        .header-title {
            font-size: 28px;
            font-weight: 700;
            background: linear-gradient(135deg, #1e293b, #475569);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .header-actions {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        
        .balance-card {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            background: linear-gradient(135deg, #dbeafe, #e0e7ff);
            border-radius: 12px;
            gap: 12px;
        }
        
        .balance-icon {
            width: 36px;
            height: 36px;
            background: var(--primary-color);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }
        
        .balance-info {
            display: flex;
            flex-direction: column;
        }
        
        .balance-label {
            font-size: 12px;
            color: var(--text-secondary);
        }
        
        .balance-amount {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-primary);
        }
        
        .add-listing-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .add-listing-btn:hover {
            background: #2563eb;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.3);
        }
        
        /* Responsive */
        @media (max-width: 1024px) {
            .vendor-sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar-mobile-open {
                transform: translateX(0);
            }
            
            .vendor-content {
                margin-left: 0;
            }
        }
        
        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .animate-fadeIn {
            animation: fadeIn 0.3s ease-out;
        }
        
        /* Select2 Customization */
        .select2-container--default .select2-selection--single {
            height: 48px;
            border: 1px solid #e2e8f0 !important;
            border-radius: 10px !important;
            background: white;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 46px;
            padding-left: 16px;
            color: var(--text-primary);
        }
        
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 46px;
            right: 10px;
        }
        
        .select2-dropdown {
            border: 1px solid #e2e8f0 !important;
            border-radius: 10px !important;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1) !important;
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Toggle Button -->
    <button id="sidebarToggle" class="sidebar-toggle">
        <i class="fas fa-bars"></i>
    </button>
    
    <!-- Vendor Sidebar -->
    <div class="vendor-sidebar" id="sidebar">
        <!-- Brand Area -->
        <div class="brand-area">
            <div class="brand-content">
                <div class="brand-logo">
                    <i class="fas fa-store"></i>
                </div>
                <div class="brand-text">
                    <h2 class="text-lg font-bold">Vendor Dashboard</h2>
                    <p class="text-sm text-gray-400 mt-1 truncate">
                        {{ auth()->user()->vendorProfile->business_name ?? 'My Store' }}
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Navigation -->
        <div class="nav-container">
            <nav>
                <a href="{{ route('vendor.dashboard') }}" 
                   class="nav-item {{ request()->routeIs('vendor.dashboard') ? 'active' : '' }}">
                    <div class="nav-icon">
                        <i class="fas fa-tachometer-alt"></i>
                    </div>
                    <span class="nav-label">Overview</span>
                </a>
                
                <a href="{{ route('vendor.listings.index') }}" 
                   class="nav-item {{ request()->is('vendor/listings*') ? 'active' : '' }}">
                    <div class="nav-icon">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <span class="nav-label">My Listings</span>
                </a>
                
                <a href="{{ route('vendor.orders.index') }}" 
                   class="nav-item {{ request()->is('vendor/orders*') ? 'active' : '' }}">
                    <div class="nav-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <span class="nav-label">Orders</span>
                </a>
                
                <!-- Callback Requests -->
                <a href="{{ route('vendor.callbacks.index') }}" 
                   class="nav-item {{ request()->is('vendor/callbacks*') ? 'active' : '' }}">
                    <div class="nav-icon">
                        <i class="fas fa-phone-alt"></i>
                    </div>
                    <span class="nav-label">Callback Requests</span>
                    @php
                        $pendingCallbacks = \App\Models\CallbackRequest::where('vendor_profile_id', auth()->user()->vendorProfile->id ?? 0)
                            ->where('status', 'pending')
                            ->count();
                    @endphp
                    @if($pendingCallbacks > 0)
                    <span class="badge">{{ $pendingCallbacks > 9 ? '9+' : $pendingCallbacks }}</span>
                    @endif
                </a>
                
                <!-- Messages -->
                <a href="{{ route('chat.index') }}" 
                   class="nav-item {{ request()->is('chat*') ? 'active' : '' }}">
                    <div class="nav-icon">
                        <i class="fas fa-comments"></i>
                    </div>
                    <span class="nav-label">Messages</span>
                    <span id="chatBadge" class="badge hidden">0</span>
                </a>
                
                <!-- Profile -->
                <a href="{{ route('vendor.profile.show') }}" 
                   class="nav-item {{ request()->is('vendor/profile*') ? 'active' : '' }}">
                    <div class="nav-icon">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <span class="nav-label">Profile</span>
                </a>
                
                <!-- Import Goods -->
                <a href="{{ route('vendor.imports.index') }}" 
                   class="nav-item {{ request()->is('vendor/imports*') ? 'active' : '' }}">
                    <div class="nav-icon">
                        <i class="fas fa-plane"></i>
                    </div>
                    <span class="nav-label">Import Goods</span>
                </a>
                
                <!-- Promotions -->
                <a href="{{ route('vendor.promotions.index') }}" 
                   class="nav-item {{ request()->is('vendor/promotions*') ? 'active' : '' }}">
                    <div class="nav-icon">
                        <i class="fas fa-bullhorn"></i>
                    </div>
                    <span class="nav-label">Promotions</span>
                </a>
                
                <!-- Analytics -->
                <a href="{{ route('vendor.analytics') }}" 
                   class="nav-item {{ request()->is('vendor/analytics*') ? 'active' : '' }}">
                    <div class="nav-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <span class="nav-label">Analytics</span>
                </a>
            </nav>
        </div>
        
        <!-- Account Status Area -->
        <div class="account-area">
            @php
                $vendor = auth()->user()->vendorProfile;
                $status = $vendor->vetting_status ?? 'pending';
                $statusClasses = [
                    'approved' => 'status-approved',
                    'pending' => 'status-pending', 
                    'rejected' => 'status-rejected',
                    'manual_review' => 'status-pending'
                ][$status] ?? 'status-pending';
            @endphp
            
            <div class="{{ $statusClasses }} status-badge">
                <span class="status-dot"></span>
                <span class="status-text">Account {{ ucfirst($status) }}</span>
            </div>
            
            <form action="{{ route('logout') }}" method="POST" class="mt-3">
                @csrf
                <button type="submit" class="logout-btn">
                    <i class="fas fa-sign-out-alt mr-2"></i>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="vendor-content" id="mainContent">
        <!-- Header -->
        <div class="content-header animate-fadeIn">
            <div>
                <h1 class="header-title">@yield('page_title', 'Vendor Dashboard')</h1>
                <p class="text-gray-500 mt-2">Manage your store, orders, and performance</p>
            </div>
            
            <div class="header-actions">
                <div class="balance-card">
                    <div class="balance-icon">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div class="balance-info">
                        <span class="balance-label">Available Balance</span>
                        <span class="balance-amount">$0.00</span>
                    </div>
                </div>
                
                <a href="{{ route('vendor.listings.create') }}" class="add-listing-btn">
                    <i class="fas fa-plus"></i>
                    <span>Add Listing</span>
                </a>
            </div>
        </div>
        
        <!-- Alerts -->
        @if(session('success'))
        <div class="animate-fadeIn bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded-lg">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-green-500 mr-3"></i>
                <div>
                    <p class="font-medium text-green-800">{{ session('success') }}</p>
                </div>
            </div>
        </div>
        @endif
        
        @if(session('error'))
        <div class="animate-fadeIn bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-lg">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                <div>
                    <p class="font-medium text-red-800">{{ session('error') }}</p>
                </div>
            </div>
        </div>
        @endif
        
        <!-- Page Content -->
        <div class="animate-fadeIn">
            @yield('content')
        </div>
    </div>
    
    <!-- jQuery (required for Select2) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    
    <script>
        // Sidebar Toggle
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const toggleBtn = document.getElementById('sidebarToggle');
            
            // Toggle sidebar
            toggleBtn.addEventListener('click', function() {
                sidebar.classList.toggle('sidebar-collapsed');
                mainContent.classList.toggle('content-collapsed');
                
                // Update toggle icon
                const icon = toggleBtn.querySelector('i');
                if (sidebar.classList.contains('sidebar-collapsed')) {
                    icon.className = 'fas fa-chevron-right';
                } else {
                    icon.className = 'fas fa-bars';
                }
            });
            
            // Auto-collapse on mobile
            function handleResize() {
                if (window.innerWidth <= 1024) {
                    sidebar.classList.remove('sidebar-collapsed');
                    mainContent.classList.remove('content-collapsed');
                }
            }
            
            window.addEventListener('resize', handleResize);
            handleResize(); // Initial check
            
            // Mobile menu toggle
            if (window.innerWidth <= 1024) {
                toggleBtn.addEventListener('click', function() {
                    sidebar.classList.toggle('sidebar-mobile-open');
                });
                
                // Close sidebar when clicking outside on mobile
                document.addEventListener('click', function(event) {
                    if (!sidebar.contains(event.target) && !toggleBtn.contains(event.target)) {
                        sidebar.classList.remove('sidebar-mobile-open');
                    }
                });
            }
            
            // Update chat badge
            updateChatBadge();
            setInterval(updateChatBadge, 30000);
        });
        
        // Chat Badge Function
        async function updateChatBadge() {
            try {
                const response = await fetch('/chat/unread-count', {
                    headers: { 'Accept': 'application/json' }
                });
                
                if (response.ok) {
                    const data = await response.json();
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
        
        // Initialize Select2 for better dropdowns
        $(document).ready(function() {
            $('.select2').select2({
                theme: 'default',
                width: '100%',
                placeholder: 'Select an option',
                allowClear: true
            });
        });
    </script>
    
    @stack('scripts')
</body>
</html>