@extends('layouts.vendor')

@section('title', 'Vendor Dashboard - JClone')
@section('page_title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Welcome Message -->
    <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-500 text-white rounded-2xl p-6 shadow-lg shadow-purple-500/20">
        <div class="flex flex-col md:flex-row items-center justify-between">
            <div class="md:w-2/3">
                <h2 class="text-2xl font-bold mb-2">Welcome back, {{ auth()->user()->name }}!</h2>
                <p class="text-white/90 mb-4">Manage your store, track sales, and grow your business with BebaMart.</p>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('vendor.listings.create') }}"
                       class="px-5 py-2.5 bg-white text-indigo-600 rounded-xl font-semibold hover:bg-gray-100 transition inline-flex items-center shadow-lg">
                        <i class="fas fa-plus mr-2"></i> Add Product
                    </a>
                    <a href="{{ route('vendor.orders.index') }}"
                       class="px-5 py-2.5 bg-white/20 backdrop-blur border-2 border-white/50 text-white rounded-xl font-semibold hover:bg-white/30 transition inline-flex items-center">
                        <i class="fas fa-shopping-bag mr-2"></i> View Orders
                    </a>
                </div>
            </div>
            <div class="mt-6 md:mt-0">
                <div class="w-24 h-24 bg-white/20 backdrop-blur rounded-full flex items-center justify-center">
                    <i class="fas fa-store text-4xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Sales -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="bg-green-100 p-3 rounded-lg mr-4">
                    <i class="fas fa-dollar-sign text-green-600 text-2xl"></i>
                </div>
                <div>
                    <p class="text-gray-600">Total Sales</p>
                    <p class="text-2xl font-bold text-gray-800">UGX {{ number_format($stats['total_sales'] ?? 0, 2) }}</p>
                </div>
            </div>
        </div>

        <!-- Active Listings -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="bg-blue-100 p-3 rounded-lg mr-4">
                    <i class="fas fa-boxes text-blue-600 text-2xl"></i>
                </div>
                <div>
                    <p class="text-gray-600">Active Listings</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['active_listings'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <!-- Pending Orders -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="bg-yellow-100 p-3 rounded-lg mr-4">
                    <i class="fas fa-shopping-cart text-yellow-600 text-2xl"></i>
                </div>
                <div>
                    <p class="text-gray-600">Pending Orders</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['pending_orders'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <!-- Store Rating -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="bg-purple-100 p-3 rounded-lg mr-4">
                    <i class="fas fa-star text-purple-600 text-2xl"></i>
                </div>
                <div>
                    <p class="text-gray-600">Store Rating</p>
                    <p class="text-2xl font-bold text-gray-800">4.8/5.0</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Add New Product -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Quick Actions</h3>
            <div class="space-y-3">
                <a href="{{ route('vendor.listings.create') }}" 
                   class="flex items-center p-3 bg-indigo-50 text-indigo-700 rounded-lg hover:bg-indigo-100 transition">
                    <i class="fas fa-plus-circle mr-3 text-xl"></i>
                    <span>Add New Product</span>
                    <i class="fas fa-arrow-right ml-auto"></i>
                </a>
                
                <a href="{{ route('vendor.orders.index') }}" 
                   class="flex items-center p-3 bg-green-50 text-green-700 rounded-lg hover:bg-green-100 transition">
                    <i class="fas fa-shopping-bag mr-3 text-xl"></i>
                    <span>View Orders</span>
                    <i class="fas fa-arrow-right ml-auto"></i>
                </a>
                
                <a href="{{ route('vendor.imports.index') }}" 
                   class="flex items-center p-3 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition">
                    <i class="fas fa-plane mr-3 text-xl"></i>
                    <span>Import Products</span>
                    <i class="fas fa-arrow-right ml-auto"></i>
                </a>
                
                <a href="{{ route('vendor.promotions.index') }}" 
                   class="flex items-center p-3 bg-purple-50 text-purple-700 rounded-lg hover:bg-purple-100 transition">
                    <i class="fas fa-bullhorn mr-3 text-xl"></i>
                    <span>Create Promotion</span>
                    <i class="fas fa-arrow-right ml-auto"></i>
                </a>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-800">Recent Orders</h3>
                <a href="{{ route('vendor.orders.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm">
                    View All
                </a>
            </div>
            
            @if($recentOrders && $recentOrders->count() > 0)
                <div class="space-y-3">
                    @foreach($recentOrders as $order)
                    <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                        <div>
                            <p class="font-medium">Order #{{ $order->order_number }}</p>
                            <p class="text-sm text-gray-600">{{ $order->buyer->name }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-bold">UGX {{ number_format($order->total, 2) }}</p>
                            <span class="text-xs px-2 py-1 rounded-full 
                                @if($order->status == 'pending') bg-yellow-100 text-yellow-800
                                @elseif($order->status == 'paid') bg-blue-100 text-blue-800
                                @elseif($order->status == 'delivered') bg-green-100 text-green-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst($order->status) }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <i class="fas fa-shopping-cart text-gray-400 text-3xl mb-3"></i>
                    <p class="text-gray-600">No orders yet</p>
                    <p class="text-sm text-gray-500 mt-1">Start by adding products</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Recent Listings -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-gray-800">Your Listings</h3>
            <a href="{{ route('vendor.listings.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm">
                View All
            </a>
        </div>
        
        @if($recentListings && $recentListings->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($recentListings as $listing)
                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                    <div class="flex items-start">
                        @if($listing->images->first())
                        <img src="{{ asset('storage/' . $listing->images->first()->path) }}" 
                             alt="{{ $listing->title }}" 
                             class="w-16 h-16 object-cover rounded-lg mr-4">
                        @else
                        <div class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-image text-gray-400"></i>
                        </div>
                        @endif
                        
                        <div class="flex-1">
                            <h4 class="font-medium text-gray-800 line-clamp-1">{{ $listing->title }}</h4>
                            <p class="text-lg font-bold text-indigo-600">UGX {{ number_format($listing->price, 2) }}</p>
                            <div class="flex items-center justify-between mt-2">
                                <span class="text-sm text-gray-600">
                                    Stock: {{ $listing->stock }}
                                </span>
                                <span class="text-xs px-2 py-1 rounded-full 
                                    @if($listing->is_active) bg-green-100 text-green-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ $listing->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8">
                <i class="fas fa-box-open text-gray-400 text-3xl mb-3"></i>
                <p class="text-gray-600">No listings yet</p>
                <p class="text-sm text-gray-500 mt-1">Start by creating your first product</p>
                <a href="{{ route('vendor.listings.create') }}" 
                   class="mt-4 inline-block bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                    <i class="fas fa-plus mr-2"></i>Add First Product
                </a>
            </div>
        @endif
    </div>
</div>
@endsection