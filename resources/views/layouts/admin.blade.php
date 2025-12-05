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
                        primary: '#4f46e5',
                        secondary: '#10b981',
                        accent: '#f59e0b',
                        danger: '#ef4444'
                    }
                }
            }
        }
    </script>
    
    <style>
        /* Custom Styles */
        .sidebar {
            transition: all 0.3s ease;
        }
        
        .sidebar-item:hover {
            background-color: rgba(79, 70, 229, 0.1);
        }
        
        .sidebar-item.active {
            background-color: #4f46e5;
            color: white;
        }
        
        .stat-card {
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .table-row:hover {
            background-color: #f9fafb;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Sidebar -->
    <div class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-lg sidebar">
        <!-- Logo -->
        <div class="flex items-center justify-center h-16 border-b">
            <div class="flex items-center">
                <i class="fas fa-store text-2xl text-primary mr-2"></i>
                <span class="text-xl font-bold">{{ config('app.name') }}</span>
                <span class="ml-2 text-xs bg-primary text-white px-2 py-1 rounded">Admin</span>
            </div>
        </div>
        
        <!-- User Info -->
        <div class="p-4 border-b">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-primary text-white rounded-full flex items-center justify-center mr-3">
                    <i class="fas fa-user"></i>
                </div>
                <div>
                    <div class="font-semibold">{{ Auth::user()->name }}</div>
                    <div class="text-xs text-gray-500 capitalize">{{ Auth::user()->role }}</div>
                </div>
            </div>
        </div>
        
        <!-- Navigation -->
        <nav class="p-4">
            <div class="space-y-1">
                <a href="{{ route('admin.dashboard') }}" 
                   class="sidebar-item flex items-center px-4 py-3 rounded-lg {{ request()->routeIs('admin.dashboard') ? 'active' : 'text-gray-700 hover:text-primary' }}">
                    <i class="fas fa-tachometer-alt mr-3"></i>
                    <span>Dashboard</span>
                </a>
                
                <!-- User Management -->
                <div class="mt-4">
                    <div class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">User Management</div>
                    <a href="{{ route('admin.users.index') }}" 
                       class="sidebar-item flex items-center px-4 py-3 rounded-lg {{ request()->routeIs('admin.users.*') ? 'active' : 'text-gray-700 hover:text-primary' }}">
                        <i class="fas fa-users mr-3"></i>
                        <span>Users</span>
                    </a>
                    <a href="{{ route('admin.vendors.pending') }}" 
                       class="sidebar-item flex items-center px-4 py-3 rounded-lg {{ request()->routeIs('admin.vendors.*') ? 'active' : 'text-gray-700 hover:text-primary' }}">
                        <i class="fas fa-store mr-3"></i>
                        <span>Vendors</span>
                        @if(\App\Models\VendorProfile::where('vetting_status', 'pending')->count() > 0)
                        <span class="ml-auto bg-red-500 text-white text-xs rounded-full px-2 py-1">
                            {{ \App\Models\VendorProfile::where('vetting_status', 'pending')->count() }}
                        </span>
                        @endif
                    </a>
                </div>
                
                <!-- Content Management -->
                <div class="mt-4">
                    <div class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Content Management</div>
                    <a href="{{ route('admin.categories.index') }}" 
                       class="sidebar-item flex items-center px-4 py-3 rounded-lg {{ request()->routeIs('admin.categories.*') ? 'active' : 'text-gray-700 hover:text-primary' }}">
                        <i class="fas fa-tags mr-3"></i>
                        <span>Categories</span>
                    </a>
                    <a href="{{ route('admin.orders.index') }}" 
                       class="sidebar-item flex items-center px-4 py-3 rounded-lg {{ request()->routeIs('admin.orders.*') ? 'active' : 'text-gray-700 hover:text-primary' }}">
                        <i class="fas fa-shopping-bag mr-3"></i>
                        <span>Orders</span>
                    </a>
                    <a href="{{ route('admin.disputes.index') }}" 
                       class="sidebar-item flex items-center px-4 py-3 rounded-lg {{ request()->routeIs('admin.disputes.*') ? 'active' : 'text-gray-700 hover:text-primary' }}">
                        <i class="fas fa-exclamation-triangle mr-3"></i>
                        <span>Disputes</span>
                        @if(\App\Models\Dispute::where('status', 'open')->count() > 0)
                        <span class="ml-auto bg-red-500 text-white text-xs rounded-full px-2 py-1">
                            {{ \App\Models\Dispute::where('status', 'open')->count() }}
                        </span>
                        @endif
                    </a>
                </div>
                
                <!-- Reports & Analytics -->
                <div class="mt-4">
                    <div class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Analytics</div>
                    <a href="{{ route('admin.reports.index') }}" 
                       class="sidebar-item flex items-center px-4 py-3 rounded-lg {{ request()->routeIs('admin.reports.*') ? 'active' : 'text-gray-700 hover:text-primary' }}">
                        <i class="fas fa-chart-bar mr-3"></i>
                        <span>Reports</span>
                    </a>
                </div>
                
                <!-- System -->
                <div class="mt-4">
                    <div class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">System</div>
                    <a href="#" class="sidebar-item flex items-center px-4 py-3 rounded-lg text-gray-700 hover:text-primary">
                        <i class="fas fa-cog mr-3"></i>
                        <span>Settings</span>
                    </a>
                    <form action="{{ route('logout') }}" method="POST" class="block">
                        @csrf
                        <button type="submit" 
                                class="sidebar-item w-full flex items-center px-4 py-3 rounded-lg text-gray-700 hover:text-red-600">
                            <i class="fas fa-sign-out-alt mr-3"></i>
                            <span>Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="ml-64">
        <!-- Top Bar -->
        <header class="bg-white shadow-sm">
            <div class="flex items-center justify-between px-8 py-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">@yield('page-title', 'Dashboard')</h1>
                    <p class="text-gray-600">@yield('page-description', 'Welcome to the admin dashboard')</p>
                </div>
                
                <div class="flex items-center space-x-4">
                    <!-- Notifications -->
                    <div class="relative">
                        <button class="p-2 text-gray-600 hover:text-primary relative">
                            <i class="fas fa-bell text-xl"></i>
                            <span class="absolute top-1 right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">3</span>
                        </button>
                        <!-- Notification Dropdown -->
                        <div class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg py-2 hidden">
                            <div class="px-4 py-2 border-b">
                                <h3 class="font-semibold">Notifications</h3>
                            </div>
                            <div class="max-h-96 overflow-y-auto">
                                <!-- Notification items -->
                            </div>
                        </div>
                    </div>
                    
                    <!-- Search -->
                    <div class="relative">
                        <input type="text" placeholder="Search..." 
                               class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                </div>
            </div>
        </header>

        <!-- Content Area -->
        <main class="p-8">
            @yield('content')
        </main>
        
        <!-- Footer -->
        <footer class="bg-white border-t px-8 py-4">
            <div class="flex justify-between items-center text-sm text-gray-600">
                <div>
                    &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                </div>
                <div>
                    <span class="mr-4">Version 1.0.0</span>
                    <span>Server: {{ now()->format('Y-m-d H:i:s') }}</span>
                </div>
            </div>
        </footer>
    </div>

    <!-- JavaScript -->
    <script>
        // Toggle sidebar on mobile
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('-translate-x-full');
        }

        // Initialize tooltips
        document.addEventListener('DOMContentLoaded', function() {
            // Notification dropdown toggle
            const notificationBtn = document.querySelector('[aria-label="Notifications"]');
            const notificationDropdown = notificationBtn.nextElementSibling;
            
            notificationBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                notificationDropdown.classList.toggle('hidden');
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!notificationBtn.contains(e.target) && !notificationDropdown.contains(e.target)) {
                    notificationDropdown.classList.add('hidden');
                }
            });
            
            // Set active sidebar item
            const currentPath = window.location.pathname;
            document.querySelectorAll('.sidebar-item').forEach(item => {
                if (item.getAttribute('href') === currentPath) {
                    item.classList.add('active');
                }
            });
        });
    </script>
    
    @yield('scripts')
</body>
</html>