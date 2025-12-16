@extends('layouts.admin')

@section('title', 'Detailed Sales Report')

@section('content')
<div class="py-4">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Detailed Sales Report</h1>
                    <p class="text-gray-600 mt-1">Complete sales data and transaction details</p>
                </div>
                <a href="{{ route('admin.reports.export', ['type' => 'sales', 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}" 
                   class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2">
                    <i class="fas fa-download"></i> Export CSV
                </a>
            </div>
        </div>

        <!-- Date Filter -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <form method="GET" action="{{ route('admin.reports.sales.detailed') }}" class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Apply Filter
                    </button>
                    <a href="{{ route('admin.reports.sales.detailed') }}" 
                       class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Total Orders</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($summary['total_orders']) }}</p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-full">
                        <i class="fas fa-shopping-cart text-blue-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Total Revenue</p>
                        <p class="text-2xl font-bold text-green-600">UGX {{ number_format($summary['total_revenue']) }}</p>
                    </div>
                    <div class="p-3 bg-green-100 rounded-full">
                        <i class="fas fa-money-bill-wave text-green-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Platform Commission</p>
                        <p class="text-2xl font-bold text-yellow-600">UGX {{ number_format($summary['total_commission']) }}</p>
                    </div>
                    <div class="p-3 bg-yellow-100 rounded-full">
                        <i class="fas fa-percentage text-yellow-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Avg Order Value</p>
                        <p class="text-2xl font-bold text-purple-600">UGX {{ number_format($summary['avg_order_value']) }}</p>
                    </div>
                    <div class="p-3 bg-purple-100 rounded-full">
                        <i class="fas fa-chart-line text-purple-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-900">Sales Transactions</h3>
                <p class="text-sm text-gray-600 mt-1">Showing {{ $orders->count() }} of {{ $orders->total() }} orders</p>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Order Details
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Buyer
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Vendor
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Items
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Amount
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($orders as $order)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">
                                        <a href="{{ route('admin.orders.show', $order) }}" class="hover:text-blue-600">
                                            {{ $order->order_number }}
                                        </a>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ $order->created_at->format('M d, Y H:i') }}
                                    </div>
                                    <div class="text-xs text-gray-400">
                                        {{ $order->items->count() }} item(s)
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8 bg-blue-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-user text-blue-600 text-sm"></i>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $order->buyer->name ?? 'Guest' }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ $order->buyer->email ?? 'N/A' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8 bg-green-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-store text-green-600 text-sm"></i>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $order->vendorProfile->business_name ?? 'N/A' }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ $order->vendorProfile->vendor_type ?? 'N/A' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">
                                    @foreach($order->items->take(2) as $item)
                                        <div class="truncate max-w-xs">{{ $item->listing->title ?? 'N/A' }}</div>
                                    @endforeach
                                    @if($order->items->count() > 2)
                                        <span class="text-xs text-gray-500">+{{ $order->items->count() - 2 }} more</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    UGX {{ number_format($order->total) }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    Commission: UGX {{ number_format($order->platform_commission) }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'paid' => 'bg-blue-100 text-blue-800',
                                        'processing' => 'bg-purple-100 text-purple-800',
                                        'shipped' => 'bg-indigo-100 text-indigo-800',
                                        'delivered' => 'bg-green-100 text-green-800',
                                        'cancelled' => 'bg-red-100 text-red-800',
                                    ];
                                @endphp
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusColors[$order->status] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @if($orders->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $orders->links() }}
            </div>
            @endif
        </div>

        <!-- Payment Method Breakdown -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Payment Method Distribution</h3>
                <div class="h-64">
                    <canvas id="paymentMethodsChart"></canvas>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Order Status Distribution</h3>
                <div class="h-64">
                    <canvas id="orderStatusChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Payment Methods Chart
    const paymentCtx = document.getElementById('paymentMethodsChart').getContext('2d');
    new Chart(paymentCtx, {
        type: 'doughnut',
        data: {
            labels: ['Mobile Money', 'Credit Card', 'Bank Transfer', 'Cash on Delivery'],
            datasets: [{
                data: [45, 30, 15, 10],
                backgroundColor: [
                    '#10b981',
                    '#3b82f6',
                    '#8b5cf6',
                    '#f59e0b'
                ],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Order Status Chart
    const statusCtx = document.getElementById('orderStatusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'bar',
        data: {
            labels: ['Pending', 'Paid', 'Processing', 'Shipped', 'Delivered', 'Cancelled'],
            datasets: [{
                label: 'Orders',
                data: [12, 45, 18, 32, 67, 8],
                backgroundColor: [
                    '#f59e0b',
                    '#3b82f6',
                    '#8b5cf6',
                    '#6366f1',
                    '#10b981',
                    '#ef4444'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });
</script>
@endpush
@endsection