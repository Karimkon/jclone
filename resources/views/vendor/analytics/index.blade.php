@extends('layouts.vendor')

@section('title', 'Analytics - Vendor Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Analytics Dashboard</h1>
            <p class="text-gray-600">Track your store performance and sales</p>
        </div>
        <div class="flex items-center space-x-4">
            <select class="border border-gray-300 rounded-lg p-2">
                <option>Last 7 Days</option>
                <option>Last 30 Days</option>
                <option>Last 90 Days</option>
                <option>This Year</option>
            </select>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex items-center">
                <div class="bg-blue-100 p-3 rounded-lg mr-4">
                    <i class="fas fa-chart-line text-blue-600 text-2xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Total Revenue</p>
                    <p class="text-2xl font-bold text-gray-800">$0.00</p>
                    <p class="text-xs text-gray-500">↗️ 0% from last period</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex items-center">
                <div class="bg-green-100 p-3 rounded-lg mr-4">
                    <i class="fas fa-shopping-cart text-green-600 text-2xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Total Orders</p>
                    <p class="text-2xl font-bold text-gray-800">0</p>
                    <p class="text-xs text-gray-500">↗️ 0% from last period</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex items-center">
                <div class="bg-purple-100 p-3 rounded-lg mr-4">
                    <i class="fas fa-eye text-purple-600 text-2xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Product Views</p>
                    <p class="text-2xl font-bold text-gray-800">0</p>
                    <p class="text-xs text-gray-500">↗️ 0% from last period</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex items-center">
                <div class="bg-yellow-100 p-3 rounded-lg mr-4">
                    <i class="fas fa-star text-yellow-600 text-2xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Conversion Rate</p>
                    <p class="text-2xl font-bold text-gray-800">0%</p>
                    <p class="text-xs text-gray-500">↗️ 0% from last period</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Area -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Revenue Chart -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Revenue Overview</h3>
            <div class="h-64 flex items-center justify-center text-gray-400">
                <div class="text-center">
                    <i class="fas fa-chart-bar text-4xl mb-3"></i>
                    <p>Revenue data will appear here</p>
                    <p class="text-sm">Start selling to see analytics</p>
                </div>
            </div>
        </div>
        
        <!-- Top Products -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-900">Top Products</h3>
                <a href="{{ route('vendor.listings.index') }}" class="text-sm text-primary hover:text-indigo-700">
                    View All
                </a>
            </div>
            <div class="space-y-4">
                @forelse(auth()->user()->vendorProfile->listings->take(5) as $listing)
                <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                    <div class="flex items-center">
                        @if($listing->images->first())
                        <div class="w-10 h-10 mr-3">
                            <img src="{{ asset('storage/' . $listing->images->first()->path) }}" 
                                 alt="{{ $listing->title }}" 
                                 class="w-10 h-10 object-cover rounded">
                        </div>
                        @endif
                        <div>
                            <p class="font-medium text-gray-900">{{ $listing->title }}</p>
                            <p class="text-sm text-gray-600">${{ number_format($listing->price, 2) }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="font-bold text-gray-900">0 sales</p>
                        <p class="text-xs text-gray-500">$0.00 revenue</p>
                    </div>
                </div>
                @empty
                <div class="text-center py-8">
                    <i class="fas fa-box-open text-gray-400 text-3xl mb-3"></i>
                    <p class="text-gray-600">No products yet</p>
                    <p class="text-sm text-gray-500 mt-1">Add products to see analytics</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Order Analytics -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Order Analytics</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="border border-gray-200 rounded-lg p-4">
                <div class="text-center">
                    <div class="text-3xl font-bold text-blue-600 mb-2">0</div>
                    <p class="text-gray-700">Pending Orders</p>
                </div>
            </div>
            <div class="border border-gray-200 rounded-lg p-4">
                <div class="text-center">
                    <div class="text-3xl font-bold text-green-600 mb-2">0</div>
                    <p class="text-gray-700">Completed Orders</p>
                </div>
            </div>
            <div class="border border-gray-200 rounded-lg p-4">
                <div class="text-center">
                    <div class="text-3xl font-bold text-red-600 mb-2">0</div>
                    <p class="text-gray-700">Cancelled Orders</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Coming Soon Features -->
    <div class="mt-8">
        <div class="bg-gradient-to-r from-primary to-indigo-600 rounded-lg p-6 text-white">
            <div class="flex items-center">
                <div class="mr-4">
                    <i class="fas fa-rocket text-3xl"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold mb-2">Advanced Analytics Coming Soon!</h3>
                    <p class="opacity-90">We're working on more detailed analytics including:</p>
                    <ul class="list-disc pl-5 mt-2 opacity-90">
                        <li>Real-time sales tracking</li>
                        <li>Customer demographics</li>
                        <li>Product performance reports</li>
                        <li>Promotion effectiveness</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection