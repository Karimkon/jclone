@extends('layouts.admin')

@section('title', 'Admin Dashboard - JClone')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Dashboard Overview</h1>
            <p class="text-gray-600">Welcome back, {{ auth()->user()->name }}!</p>
        </div>
        <div class="text-sm text-gray-500">
            {{ now()->format('l, F j, Y') }}
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Pending Vendors -->
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Pending Vendors</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['vendorPending'] }}</p>
                </div>
                <div class="bg-yellow-100 p-3 rounded-full">
                    <i class="fas fa-store text-yellow-600 text-2xl"></i>
                </div>
            </div>
            <a href="{{ route('admin.vendors.pending') }}" class="text-sm text-yellow-600 hover:text-yellow-800 mt-4 inline-block">
                <i class="fas fa-arrow-right mr-1"></i> Review Applications
            </a>
        </div>

        <!-- Today's Orders -->
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Today's Orders</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['ordersToday'] }}</p>
                </div>
                <div class="bg-blue-100 p-3 rounded-full">
                    <i class="fas fa-shopping-cart text-blue-600 text-2xl"></i>
                </div>
            </div>
            <a href="{{ route('admin.orders.index') }}" class="text-sm text-blue-600 hover:text-blue-800 mt-4 inline-block">
                <i class="fas fa-arrow-right mr-1"></i> View Orders
            </a>
        </div>

        <!-- Total Users -->
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Total Users</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['users'] }}</p>
                </div>
                <div class="bg-green-100 p-3 rounded-full">
                    <i class="fas fa-users text-green-600 text-2xl"></i>
                </div>
            </div>
            <a href="{{ route('admin.users.index') }}" class="text-sm text-green-600 hover:text-green-800 mt-4 inline-block">
                <i class="fas fa-arrow-right mr-1"></i> Manage Users
            </a>
        </div>

        <!-- Revenue -->
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Monthly Revenue</p>
                    <p class="text-3xl font-bold text-gray-900">${{ number_format(0) }}</p>
                </div>
                <div class="bg-purple-100 p-3 rounded-full">
                    <i class="fas fa-chart-line text-purple-600 text-2xl"></i>
                </div>
            </div>
            <a href="{{ route('admin.reports.index') }}" class="text-sm text-purple-600 hover:text-purple-800 mt-4 inline-block">
                <i class="fas fa-arrow-right mr-1"></i> View Reports
            </a>
        </div>
    </div>

    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Recent Activity -->
        <div class="bg-white rounded-xl shadow-lg">
            <div class="p-6 border-b">
                <h2 class="text-xl font-bold text-gray-900">Recent Activity</h2>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @php
                        $activities = [
                            ['icon' => 'fas fa-user-plus', 'color' => 'text-green-500', 'text' => 'New vendor registered', 'time' => '5 minutes ago'],
                            ['icon' => 'fas fa-shopping-cart', 'color' => 'text-blue-500', 'text' => 'New order #ORD-001', 'time' => '15 minutes ago'],
                            ['icon' => 'fas fa-exclamation-circle', 'color' => 'text-red-500', 'text' => 'New dispute opened', 'time' => '30 minutes ago'],
                            ['icon' => 'fas fa-check-circle', 'color' => 'text-green-500', 'text' => 'Vendor approved', 'time' => '1 hour ago'],
                            ['icon' => 'fas fa-upload', 'color' => 'text-purple-500', 'text' => 'New product listed', 'time' => '2 hours ago'],
                        ];
                    @endphp
                    
                    @foreach($activities as $activity)
                    <div class="flex items-start">
                        <div class="mr-3">
                            <i class="{{ $activity['icon'] }} {{ $activity['color'] }} text-lg"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-gray-900">{{ $activity['text'] }}</p>
                            <p class="text-sm text-gray-500">{{ $activity['time'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-xl shadow-lg">
            <div class="p-6 border-b">
                <h2 class="text-xl font-bold text-gray-900">Quick Actions</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 gap-4">
                    <a href="{{ route('admin.vendors.pending') }}" class="bg-yellow-50 hover:bg-yellow-100 p-4 rounded-lg text-center transition">
                        <i class="fas fa-store text-yellow-600 text-2xl mb-2"></i>
                        <p class="font-medium text-yellow-800">Review Vendors</p>
                        <p class="text-sm text-yellow-600">{{ $stats['vendorPending'] }} pending</p>
                    </a>
                    
                    <a href="{{ route('admin.categories.index') }}" class="bg-blue-50 hover:bg-blue-100 p-4 rounded-lg text-center transition">
                        <i class="fas fa-list text-blue-600 text-2xl mb-2"></i>
                        <p class="font-medium text-blue-800">Manage Categories</p>
                        <p class="text-sm text-blue-600">Organize products</p>
                    </a>
                    
                    <a href="{{ route('admin.users.index') }}" class="bg-green-50 hover:bg-green-100 p-4 rounded-lg text-center transition">
                        <i class="fas fa-users text-green-600 text-2xl mb-2"></i>
                        <p class="font-medium text-green-800">User Management</p>
                        <p class="text-sm text-green-600">{{ $stats['users'] }} users</p>
                    </a>
                    
                    <a href="{{ route('admin.reports.index') }}" class="bg-purple-50 hover:bg-purple-100 p-4 rounded-lg text-center transition">
                        <i class="fas fa-chart-bar text-purple-600 text-2xl mb-2"></i>
                        <p class="font-medium text-purple-800">View Reports</p>
                        <p class="text-sm text-purple-600">Analytics & insights</p>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- System Status -->
    <div class="bg-white rounded-xl shadow-lg mb-8">
        <div class="p-6 border-b">
            <h2 class="text-xl font-bold text-gray-900">System Status</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center p-4 border rounded-lg">
                    <div class="inline-block p-3 bg-green-100 rounded-full mb-3">
                        <i class="fas fa-database text-green-600 text-2xl"></i>
                    </div>
                    <h3 class="font-bold text-gray-900">Database</h3>
                    <p class="text-sm text-green-600 font-medium">✅ Operational</p>
                </div>
                
                <div class="text-center p-4 border rounded-lg">
                    <div class="inline-block p-3 bg-blue-100 rounded-full mb-3">
                        <i class="fas fa-server text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="font-bold text-gray-900">Application</h3>
                    <p class="text-sm text-green-600 font-medium">✅ Running</p>
                </div>
                
                <div class="text-center p-4 border rounded-lg">
                    <div class="inline-block p-3 bg-purple-100 rounded-full mb-3">
                        <i class="fas fa-shield-alt text-purple-600 text-2xl"></i>
                    </div>
                    <h3 class="font-bold text-gray-900">Security</h3>
                    <p class="text-sm text-green-600 font-medium">✅ Protected</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Stats -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Pending Tasks -->
        <div class="bg-white rounded-xl shadow-lg">
            <div class="p-6 border-b">
                <h2 class="text-xl font-bold text-gray-900">Pending Tasks</h2>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @if($stats['vendorPending'] > 0)
                    <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-store text-yellow-600 mr-3"></i>
                            <span>Vendor Applications</span>
                        </div>
                        <span class="bg-yellow-600 text-white text-xs font-bold px-2 py-1 rounded-full">{{ $stats['vendorPending'] }}</span>
                    </div>
                    @endif
                    
                    <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-shopping-cart text-blue-600 mr-3"></i>
                            <span>Pending Orders</span>
                        </div>
                        <span class="bg-blue-600 text-white text-xs font-bold px-2 py-1 rounded-full">0</span>
                    </div>
                    
                    <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-triangle text-red-600 mr-3"></i>
                            <span>Open Disputes</span>
                        </div>
                        <span class="bg-red-600 text-white text-xs font-bold px-2 py-1 rounded-full">0</span>
                    </div>
                    
                    <div class="flex items-center justify-between p-3 bg-purple-50 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-comment-dollar text-purple-600 mr-3"></i>
                            <span>Pending Payouts</span>
                        </div>
                        <span class="bg-purple-600 text-white text-xs font-bold px-2 py-1 rounded-full">0</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Platform Stats -->
        <div class="bg-white rounded-xl shadow-lg">
            <div class="p-6 border-b">
                <h2 class="text-xl font-bold text-gray-900">Platform Statistics</h2>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Total Products</span>
                        <span class="font-bold">{{ $stats['totalProducts'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Active Vendors</span>
                        <span class="font-bold">{{ $stats['activeVendors'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Total Categories</span>
                        <span class="font-bold">{{ $stats['categories'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Today's Revenue</span>
                        <span class="font-bold text-green-600">$0.00</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">This Month</span>
                        <span class="font-bold text-blue-600">$0.00</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Info -->
        <div class="bg-white rounded-xl shadow-lg">
            <div class="p-6 border-b">
                <h2 class="text-xl font-bold text-gray-900">System Information</h2>
            </div>
            <div class="p-6">
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Laravel Version</span>
                        <span class="font-mono text-sm">v{{ app()->version() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">PHP Version</span>
                        <span class="font-mono text-sm">{{ phpversion() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Database</span>
                        <span class="font-mono text-sm">MySQL</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Environment</span>
                        <span class="px-2 py-1 text-xs rounded-full {{ app()->environment('production') ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                            {{ app()->environment() }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Debug Mode</span>
                        <span class="px-2 py-1 text-xs rounded-full {{ config('app.debug') ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ config('app.debug') ? 'ON' : 'OFF' }}
                        </span>
                    </div>
                </div>
                <div class="mt-6 pt-6 border-t">
                    <a href="{{ route('admin.reports.index') }}" class="block w-full text-center bg-indigo-600 text-white py-2 rounded-lg hover:bg-indigo-700 transition">
                        <i class="fas fa-chart-line mr-2"></i>View Detailed Analytics
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection