<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') - {{ config('app.name') }}</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            200: '#c7d2fe',
                            300: '#a5b4fc',
                            400: '#818cf8',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            800: '#3730a3',
                            900: '#312e81',
                        },
                        secondary: '#10b981',
                        accent: '#f59e0b',
                        danger: '#ef4444',
                        dark: '#111827'
                    }
                }
            }
        }
    </script>
    
    <style>
        /* Custom Styles */
        .sidebar {
            transition: all 0.3s ease;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.05);
        }
        
        .sidebar-item {
            transition: all 0.2s ease;
            position: relative;
        }
        
        .sidebar-item:hover {
            background-color: rgba(79, 70, 229, 0.08);
            transform: translateX(4px);
        }
        
        .sidebar-item.active {
            background-color: #4f46e5;
            color: white;
            font-weight: 600;
        }
        
        .sidebar-item.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background-color: #4f46e5;
            border-radius: 0 2px 2px 0;
        }
        
        .sidebar-item.active:hover {
            background-color: #4338ca;
        }
        
        .stat-card {
            transition: all 0.3s ease;
            border: 1px solid #e5e7eb;
        }
        
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
        
        .table-row:hover {
            background-color: #f8fafc;
        }
        
        /* Mobile menu backdrop */
        .mobile-backdrop {
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
        }
        
        /* Notification badge */
        .notification-badge {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        
        /* Smooth scrollbar */
        .scrollbar-thin {
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 #f1f5f9;
        }
        
        .scrollbar-thin::-webkit-scrollbar {
            width: 6px;
        }
        
        .scrollbar-thin::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 3px;
        }
        
        .scrollbar-thin::-webkit-scrollbar-thumb {
            background-color: #cbd5e1;
            border-radius: 3px;
        }
        
        /* Page transitions */
        .page-transition {
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Loading skeleton */
        .skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
        }
        
        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
    </style>
</head>
<body class="bg-gray-50 font-sans antialiased">
    <!-- Mobile Menu Button -->
    <div class="lg:hidden fixed top-4 left-4 z-50">
        <button id="mobileMenuButton" class="p-2 bg-white rounded-lg shadow-md hover:bg-gray-50 transition">
            <i class="fas fa-bars text-gray-700 text-lg"></i>
        </button>
    </div>

    <!-- Sidebar -->
    <div id="sidebar" class="fixed inset-y-0 left-0 z-40 w-64 bg-white shadow-lg sidebar lg:translate-x-0 -translate-x-full">
        <!-- Logo -->
        <div class="flex items-center justify-between h-16 px-4 border-b">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-purple-600 rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-store text-white text-lg"></i>
                </div>
                <div>
                    <span class="text-lg font-bold text-gray-900">{{ config('app.name') }}</span>
                    <span class="block text-xs text-primary-600 font-medium">Admin Panel</span>
                </div>
            </div>
            <button id="closeSidebar" class="lg:hidden p-1 hover:bg-gray-100 rounded">
                <i class="fas fa-times text-gray-500"></i>
            </button>
        </div>
        
        <!-- User Info -->
        <div class="p-4 border-b">
            <div class="flex items-center">
                <div class="relative">
                    <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-indigo-600 text-white rounded-full flex items-center justify-center mr-3">
                        @if(Auth::user()->profile_photo)
                            <img src="{{ asset('storage/' . Auth::user()->profile_photo) }}" alt="{{ Auth::user()->name }}" class="w-10 h-10 rounded-full">
                        @else
                            <i class="fas fa-user text-lg"></i>
                        @endif
                    </div>
                    <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-500 border-2 border-white rounded-full"></div>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="font-semibold text-gray-900 truncate">{{ Auth::user()->name }}</div>
                    <div class="text-xs text-gray-500 capitalize flex items-center">
                        <span class="bg-primary-100 text-primary-800 px-2 py-0.5 rounded mr-2">{{ Auth::user()->role }}</span>
                        <span class="text-green-600">● Online</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto scrollbar-thin py-4">
            <div class="space-y-1 px-2">
                <a href="{{ route('admin.dashboard') }}" 
                   class="sidebar-item flex items-center px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.dashboard') ? 'active' : 'text-gray-700 hover:text-primary-600' }}">
                    <i class="fas fa-tachometer-alt w-5 mr-3 text-center"></i>
                    <span class="text-sm font-medium">Dashboard</span>
                </a>
                
                <!-- User Management -->
                <div class="mt-6">
                    <div class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">User Management</div>
                    <a href="{{ route('admin.users.index') }}" 
                       class="sidebar-item flex items-center px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.users.*') ? 'active' : 'text-gray-700 hover:text-primary-600' }}">
                        <i class="fas fa-users w-5 mr-3 text-center"></i>
                        <span class="text-sm font-medium">Users</span>
                    </a>
                    <a href="{{ route('admin.vendors.pending') }}" 
                       class="sidebar-item flex items-center px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.vendors.pending') ? 'active' : 'text-gray-700 hover:text-primary-600' }}">
                        <i class="fas fa-store w-5 mr-3 text-center"></i>
                        <span class="text-sm font-medium">Vendors</span>
                        @if(\App\Models\VendorProfile::where('vetting_status', 'pending')->count() > 0)
                        <span class="ml-auto bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center notification-badge">
                            {{ \App\Models\VendorProfile::where('vetting_status', 'pending')->count() }}
                        </span>
                        @endif
                    </a>
                    <a href="{{ route('admin.vendors.index') }}" 
                       class="sidebar-item flex items-center px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.vendors.index') ? 'active' : 'text-gray-700 hover:text-primary-600' }}">
                        <i class="fas fa-list-alt w-5 mr-3 text-center"></i>
                        <span class="text-sm font-medium">All Vendors</span>
                    </a>
                </div>
                
                <!-- Content Management -->
                <div class="mt-6">
                    <div class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Content Management</div>
                    <a href="{{ route('admin.categories.index') }}" 
                       class="sidebar-item flex items-center px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.categories.*') ? 'active' : 'text-gray-700 hover:text-primary-600' }}">
                        <i class="fas fa-tags w-5 mr-3 text-center"></i>
                        <span class="text-sm font-medium">Categories</span>
                    </a>
                    <a href="{{ route('admin.orders.index') }}" 
                       class="sidebar-item flex items-center px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.orders.*') ? 'active' : 'text-gray-700 hover:text-primary-600' }}">
                        <i class="fas fa-shopping-bag w-5 mr-3 text-center"></i>
                        <span class="text-sm font-medium">Orders</span>
                    </a>
                    <a href="{{ route('admin.disputes.index') }}" 
                       class="sidebar-item flex items-center px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.disputes.*') ? 'active' : 'text-gray-700 hover:text-primary-600' }}">
                        <i class="fas fa-exclamation-triangle w-5 mr-3 text-center"></i>
                        <span class="text-sm font-medium">Disputes</span>
                        @if(\App\Models\Dispute::where('status', 'open')->count() > 0)
                        <span class="ml-auto bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                            {{ \App\Models\Dispute::where('status', 'open')->count() }}
                        </span>
                        @endif
                    </a>
                </div>
                
                <!-- Analytics -->
                <div class="mt-6">
                    <div class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Analytics</div>
                    <a href="{{ route('admin.reports.index') }}" 
                       class="sidebar-item flex items-center px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.reports.*') ? 'active' : 'text-gray-700 hover:text-primary-600' }}">
                        <i class="fas fa-chart-bar w-5 mr-3 text-center"></i>
                        <span class="text-sm font-medium">Reports</span>
                    </a>
                    <a href="#" class="sidebar-item flex items-center px-3 py-2.5 rounded-lg text-gray-700 hover:text-primary-600">
                        <i class="fas fa-chart-line w-5 mr-3 text-center"></i>
                        <span class="text-sm font-medium">Analytics</span>
                    </a>
                </div>
                
                <!-- System -->
                <div class="mt-6">
                    <div class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">System</div>
                    <a href="#" class="sidebar-item flex items-center px-3 py-2.5 rounded-lg text-gray-700 hover:text-primary-600">
                        <i class="fas fa-cog w-5 mr-3 text-center"></i>
                        <span class="text-sm font-medium">Settings</span>
                    </a>
                    <a href="#" class="sidebar-item flex items-center px-3 py-2.5 rounded-lg text-gray-700 hover:text-primary-600">
                        <i class="fas fa-shield-alt w-5 mr-3 text-center"></i>
                        <span class="text-sm font-medium">Security</span>
                    </a>
                </div>
            </div>
        </nav>
        
        <!-- Bottom Section - Logout -->
        <div class="p-4 border-t">
            <form action="{{ route('logout') }}" method="POST" class="block">
                @csrf
                <button type="submit" 
                        class="sidebar-item w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-gray-700 hover:text-red-600 hover:bg-red-50 transition group">
                    <div class="flex items-center">
                        <i class="fas fa-sign-out-alt w-5 mr-3 text-center group-hover:scale-110 transition-transform"></i>
                        <span class="text-sm font-medium">Logout</span>
                    </div>
                    <i class="fas fa-chevron-right text-xs text-gray-400 group-hover:text-red-600"></i>
                </button>
            </form>
            <div class="mt-2 text-xs text-gray-400 text-center">
                {{ config('app.name') }} v1.0.0
            </div>
        </div>
    </div>

    <!-- Mobile Backdrop -->
    <div id="mobileBackdrop" class="lg:hidden fixed inset-0 bg-black bg-opacity-50 z-30 hidden mobile-backdrop"></div>

    <!-- Main Content -->
    <div class="lg:ml-64 min-h-screen flex flex-col">
        <!-- Top Bar -->
        <header class="bg-white shadow-sm border-b sticky top-0 z-20">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between px-4 sm:px-6 lg:px-8 py-4">
                <div class="mb-4 sm:mb-0">
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900">@yield('page-title', 'Dashboard')</h1>
                    <p class="text-sm text-gray-600 mt-1">@yield('page-description', 'Welcome to the admin dashboard')</p>
                </div>
                
                <div class="flex items-center space-x-3 sm:space-x-4">
                    <!-- Quick Actions -->
                    <div class="hidden sm:flex items-center space-x-2">
                        <button class="p-2 text-gray-600 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition">
                            <i class="fas fa-plus"></i>
                        </button>
                        <button class="p-2 text-gray-600 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition">
                            <i class="fas fa-download"></i>
                        </button>
                        <button class="p-2 text-gray-600 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition">
                            <i class="fas fa-filter"></i>
                        </button>
                    </div>
                    
                    <!-- Search -->
                    <div class="relative flex-1 sm:flex-none sm:w-64">
                        <input type="text" 
                               placeholder="Search..." 
                               class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent text-sm">
                        <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-sm"></i>
                    </div>
                    
                    <!-- Notifications -->
                   <div class="relative">
    <button id="notificationButton" class="relative p-2 text-gray-600 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition">
        <i class="fas fa-bell text-lg"></i>
        @php
            $pendingWithdrawals = \App\Models\VendorWithdrawal::whereIn('status', ['pending', 'processing'])->count();
            $pendingVendors = \App\Models\VendorProfile::where('vetting_status', 'pending')->count();
            $pendingDisputes = \App\Models\Dispute::where('status', 'open')->count();
            $totalNotifications = $pendingWithdrawals + $pendingVendors + $pendingDisputes;
        @endphp
        @if($totalNotifications > 0)
        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center notification-badge">
            {{ $totalNotifications }}
        </span>
        @endif
    </button>
                        
                        <!-- Notification Dropdown -->
                        <div id="notificationDropdown" class="absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-lg shadow-xl border hidden z-50">
                            <div class="px-4 py-3 border-b bg-gray-50 rounded-t-lg">
                                <div class="flex items-center justify-between">
                                    <h3 class="font-semibold text-gray-900">Notifications</h3>
                                    <span class="text-xs text-primary-600 cursor-pointer hover:text-primary-700">Mark all as read</span>
                                </div>
                            </div>
                            <div class="max-h-96 overflow-y-auto scrollbar-thin">
                                <!-- Notification items would go here -->
                                <div class="p-4 border-b hover:bg-gray-50">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mr-3">
                                            <i class="fas fa-store text-sm"></i>
                                        </div>
                                        <div class="flex-1">
                                            <p class="text-sm text-gray-900">New vendor application received</p>
                                            <p class="text-xs text-gray-500 mt-1">2 minutes ago</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-4 border-b hover:bg-gray-50">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 w-8 h-8 bg-green-100 text-green-600 rounded-full flex items-center justify-center mr-3">
                                            <i class="fas fa-shopping-cart text-sm"></i>
                                        </div>
                                        <div class="flex-1">
                                            <p class="text-sm text-gray-900">New order placed #ORD-1234</p>
                                            <p class="text-xs text-gray-500 mt-1">10 minutes ago</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-4 hover:bg-gray-50">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 w-8 h-8 bg-yellow-100 text-yellow-600 rounded-full flex items-center justify-center mr-3">
                                            <i class="fas fa-exclamation-triangle text-sm"></i>
                                        </div>
                                        <div class="flex-1">
                                            <p class="text-sm text-gray-900">New dispute opened</p>
                                            <p class="text-xs text-gray-500 mt-1">1 hour ago</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="px-4 py-3 border-t bg-gray-50 rounded-b-lg">
                                <a href="#" class="text-sm text-primary-600 hover:text-primary-700 font-medium flex items-center justify-center">
                                    <span>View all notifications</span>
                                    <i class="fas fa-chevron-right ml-2 text-xs"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- User Menu -->
                    <div class="relative">
                        <button id="userMenuButton" class="flex items-center space-x-2 p-2 rounded-lg hover:bg-gray-100 transition">
                            <div class="w-8 h-8 bg-gradient-to-br from-primary-500 to-indigo-600 text-white rounded-full flex items-center justify-center text-sm font-semibold">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                            <i class="fas fa-chevron-down text-gray-400 text-xs hidden sm:block"></i>
                        </button>
                        
                        <!-- User Dropdown -->
                        <div id="userDropdown" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border hidden z-50">
                            <div class="p-4 border-b">
                                <div class="text-sm font-medium text-gray-900">{{ Auth::user()->name }}</div>
                                <div class="text-xs text-gray-500">{{ Auth::user()->email }}</div>
                            </div>
                            <div class="p-2">
                                <a href="#" class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded">
                                    <i class="fas fa-user w-5 mr-2 text-gray-400"></i>
                                    <span>My Profile</span>
                                </a>
                                <a href="#" class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded">
                                    <i class="fas fa-cog w-5 mr-2 text-gray-400"></i>
                                    <span>Settings</span>
                                </a>
                                <a href="#" class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded">
                                    <i class="fas fa-question-circle w-5 mr-2 text-gray-400"></i>
                                    <span>Help & Support</span>
                                </a>
                            </div>

                        <a href="{{ route('admin.withdrawals.pending') }}" 
                        class="sidebar-item flex items-center px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.withdrawals.*') ? 'active' : 'text-gray-700 hover:text-primary-600' }}">
                            <i class="fas fa-money-bill-wave w-5 mr-3 text-center"></i>
                            <span class="text-sm font-medium">Withdrawals</span>
                            @if(\App\Models\VendorWithdrawal::whereIn('status', ['pending', 'processing'])->count() > 0)
                            <span class="ml-auto bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                                {{ \App\Models\VendorWithdrawal::whereIn('status', ['pending', 'processing'])->count() }}
                            </span>
                            @endif
                        </a>
            
                            <div class="p-2 border-t">
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="flex items-center w-full px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded">
                                        <i class="fas fa-sign-out-alt w-5 mr-2"></i>
                                        <span>Sign Out</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Breadcrumb (Optional - can be added in child views) -->
        @hasSection('breadcrumb')
        <div class="bg-gray-50 border-b">
            <div class="px-4 sm:px-6 lg:px-8 py-3">
                @yield('breadcrumb')
            </div>
        </div>
        @endif

        <!-- Content Area -->
        <main class="flex-1 page-transition">
            <div class="p-4 sm:p-6 lg:p-8">
                @yield('content')
            </div>
        </main>
        
        <!-- Footer -->
        <footer class="bg-white border-t mt-auto">
            <div class="px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between text-sm text-gray-600">
                    <div class="mb-2 sm:mb-0">
                        &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="hidden sm:inline">Server: {{ now()->format('Y-m-d H:i:s') }}</span>
                        <div class="flex items-center space-x-2">
                            <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                            <span>System Status: <span class="text-green-600 font-medium">Operational</span></span>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <!-- JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mobile sidebar toggle
            const mobileMenuButton = document.getElementById('mobileMenuButton');
            const closeSidebar = document.getElementById('closeSidebar');
            const sidebar = document.getElementById('sidebar');
            const mobileBackdrop = document.getElementById('mobileBackdrop');
            
            function openSidebar() {
                sidebar.classList.remove('-translate-x-full');
                mobileBackdrop.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }
            
            function closeSidebarFunc() {
                sidebar.classList.add('-translate-x-full');
                mobileBackdrop.classList.add('hidden');
                document.body.style.overflow = '';
            }
            
            mobileMenuButton.addEventListener('click', openSidebar);
            closeSidebar.addEventListener('click', closeSidebarFunc);
            mobileBackdrop.addEventListener('click', closeSidebarFunc);
            
            // Notification dropdown
            const notificationButton = document.getElementById('notificationButton');
            const notificationDropdown = document.getElementById('notificationDropdown');
            
            notificationButton.addEventListener('click', function(e) {
                e.stopPropagation();
                notificationDropdown.classList.toggle('hidden');
                userDropdown.classList.add('hidden');
            });
            
            // User menu dropdown
            const userMenuButton = document.getElementById('userMenuButton');
            const userDropdown = document.getElementById('userDropdown');
            
            userMenuButton.addEventListener('click', function(e) {
                e.stopPropagation();
                userDropdown.classList.toggle('hidden');
                notificationDropdown.classList.add('hidden');
            });
            
            // Close dropdowns when clicking outside
            document.addEventListener('click', function(e) {
                if (!notificationButton.contains(e.target) && !notificationDropdown.contains(e.target)) {
                    notificationDropdown.classList.add('hidden');
                }
                if (!userMenuButton.contains(e.target) && !userDropdown.contains(e.target)) {
                    userDropdown.classList.add('hidden');
                }
            });
            
            // Set active sidebar item
            const currentPath = window.location.pathname;
            document.querySelectorAll('.sidebar-item').forEach(item => {
                const href = item.getAttribute('href');
                if (href && currentPath.startsWith(href) && href !== '/') {
                    item.classList.add('active');
                }
            });
            
            // Handle window resize
            function handleResize() {
                if (window.innerWidth >= 1024) {
                    closeSidebarFunc();
                }
            }
            
            window.addEventListener('resize', handleResize);
            
            // Auto-hide notifications after 5 seconds
            setTimeout(() => {
                notificationDropdown.classList.add('hidden');
            }, 5000);
            
            // Add loading state to buttons with loading class
            document.querySelectorAll('button[type="submit"]').forEach(button => {
                button.addEventListener('click', function() {
                    if (this.classList.contains('loading')) {
                        this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Processing...';
                        this.disabled = true;
                    }
                });
            });
            
            // Add smooth scrolling to anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetId = this.getAttribute('href');
                    if (targetId === '#') return;
                    
                    const targetElement = document.querySelector(targetId);
                    if (targetElement) {
                        window.scrollTo({
                            top: targetElement.offsetTop - 20,
                            behavior: 'smooth'
                        });
                    }
                });
            });
        });
    </script>
    
    @yield('scripts')
</body>
</html>