@extends('layouts.admin')

@section('title', 'Orders Management')
@section('page-title', 'Orders Management')
@section('page-description', 'Manage all orders in the system')

@section('content')
<div class="space-y-6">
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="stat-card bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center">
                <div class="bg-blue-100 p-3 rounded-lg mr-4">
                    <i class="fas fa-shopping-bag text-blue-600 text-2xl"></i>
                </div>
                <div>
                    <p class="text-gray-600">Total Orders</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['total'] }}</p>
                </div>
            </div>
        </div>
        
        <div class="stat-card bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center">
                <div class="bg-yellow-100 p-3 rounded-lg mr-4">
                    <i class="fas fa-clock text-yellow-600 text-2xl"></i>
                </div>
                <div>
                    <p class="text-gray-600">Pending</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['pending'] }}</p>
                </div>
            </div>
        </div>
        
        <div class="stat-card bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center">
                <div class="bg-green-100 p-3 rounded-lg mr-4">
                    <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                </div>
                <div>
                    <p class="text-gray-600">Delivered</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['delivered'] }}</p>
                </div>
            </div>
        </div>
        
        <div class="stat-card bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center">
                <div class="bg-red-100 p-3 rounded-lg mr-4">
                    <i class="fas fa-times-circle text-red-600 text-2xl"></i>
                </div>
                <div>
                    <p class="text-gray-600">Cancelled</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['cancelled'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Order #, Customer, Vendor..."
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                    <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>Shipped</option>
                    <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
            </div>
            
            <div class="md:col-span-4 flex space-x-3">
                <button type="submit" class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-indigo-700 font-medium">
                    <i class="fas fa-filter mr-2"></i> Apply Filters
                </button>
                <a href="{{ route('admin.orders.index') }}" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium">
                    <i class="fas fa-times mr-2"></i> Clear Filters
                </a>
            </div>
        </form>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <h2 class="text-lg font-bold text-gray-800">Orders List</h2>
            <div class="text-gray-600">
                Showing {{ $orders->firstItem() }}-{{ $orders->lastItem() }} of {{ $orders->total() }}
            </div>
        </div>
        
        @if($orders->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Order #
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Customer
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Vendor
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Date
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Amount
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($orders as $order)
                    <tr class="table-row hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-medium text-gray-900">{{ $order->order_number }}</div>
                            <div class="text-sm text-gray-500">
                                {{ $order->items->count() }} item(s)
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-medium text-gray-900">{{ $order->buyer->name }}</div>
                            <div class="text-sm text-gray-500">{{ $order->buyer->email }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-medium text-gray-900">
                                {{ $order->vendorProfile->business_name ?? 'N/A' }}
                            </div>
                            <div class="text-sm text-gray-500">
                                {{ $order->vendorProfile->user->name ?? '' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $order->created_at->format('M d, Y') }}</div>
                            <div class="text-sm text-gray-500">{{ $order->created_at->format('h:i A') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-bold text-gray-900">${{ number_format($order->total, 2) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                @if($order->status == 'pending') bg-yellow-100 text-yellow-800
                                @elseif($order->status == 'confirmed') bg-blue-100 text-blue-800
                                @elseif($order->status == 'processing') bg-purple-100 text-purple-800
                                @elseif($order->status == 'shipped') bg-indigo-100 text-indigo-800
                                @elseif($order->status == 'delivered') bg-green-100 text-green-800
                                @elseif($order->status == 'cancelled') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst($order->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('admin.orders.show', $order) }}" 
                               class="text-primary hover:text-indigo-900 mr-3">
                                <i class="fas fa-eye"></i> View
                            </a>
                            <a href="#" class="text-green-600 hover:text-green-900">
                                <i class="fas fa-print"></i> Invoice
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="px-6 py-4 border-t">
            {{ $orders->links() }}
        </div>
        @else
        <div class="text-center py-12">
            <div class="text-gray-400 text-5xl mb-4">
                <i class="fas fa-shopping-bag"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No orders found</h3>
            <p class="text-gray-500">
                @if(request()->anyFilled(['search', 'status', 'date_from', 'date_to']))
                Try adjusting your filters
                @else
                No orders have been placed yet
                @endif
            </p>
        </div>
        @endif
    </div>
</div>
@endsection