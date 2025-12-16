@extends('layouts.admin')

@section('title', 'Vendor Performance Report')

@section('content')
<div class="py-4">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Vendor Performance Report</h1>
                    <p class="text-gray-600 mt-1">Vendor sales, ratings, and performance metrics</p>
                </div>
                <a href="{{ route('admin.reports.export', ['type' => 'vendors', 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}" 
                   class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2">
                    <i class="fas fa-download"></i> Export CSV
                </a>
            </div>
        </div>

        <!-- Date Filter -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <form method="GET" action="{{ route('admin.reports.vendor.performance') }}" class="flex flex-col md:flex-row gap-4">
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
                    <a href="{{ route('admin.reports.vendor.performance') }}" 
                       class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Summary Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Total Vendors</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($summary['total_vendors']) }}</p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-full">
                        <i class="fas fa-store text-blue-600"></i>
                    </div>
                </div>
                <div class="mt-2 text-sm text-gray-500">
                    {{ $summary['active_vendors'] }} active
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
                        <p class="text-sm text-gray-500">Avg Rating</p>
                        <p class="text-2xl font-bold text-yellow-600">{{ number_format($summary['avg_rating'], 1) }}/5</p>
                    </div>
                    <div class="p-3 bg-yellow-100 rounded-full">
                        <i class="fas fa-star text-yellow-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Avg Orders/Vendor</p>
                        <p class="text-2xl font-bold text-purple-600">
                            {{ $summary['total_vendors'] > 0 ? round(collect($vendors)->sum('stats.total_orders') / $summary['total_vendors'], 1) : 0 }}
                        </p>
                    </div>
                    <div class="p-3 bg-purple-100 rounded-full">
                        <i class="fas fa-chart-line text-purple-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Revenue Distribution -->
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Vendor Revenue Distribution</h3>
                <div class="h-64">
                    <canvas id="revenueDistributionChart"></canvas>
                </div>
            </div>

            <!-- Rating Distribution -->
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Vendor Rating Distribution</h3>
                <div class="h-64">
                    <canvas id="ratingDistributionChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Vendors Table -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900">Vendor Performance Details</h3>
                    <div class="flex items-center gap-2">
                        <select class="px-3 py-2 border border-gray-300 rounded-md text-sm">
                            <option>Sort by Revenue</option>
                            <option>Sort by Rating</option>
                            <option>Sort by Orders</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Vendor
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Type
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Performance Metrics
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Revenue
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Rating
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($vendors as $vendor)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-12 w-12">
                                        @if($vendor->logo_path)
                                            <img class="h-12 w-12 rounded-full object-cover border" 
                                                 src="{{ asset('storage/' . $vendor->logo_path) }}" 
                                                 alt="{{ $vendor->business_name }}">
                                        @else
                                            <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center">
                                                <i class="fas fa-store text-blue-600 text-lg"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $vendor->business_name }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $vendor->user->name ?? 'N/A' }}
                                        </div>
                                        <div class="text-xs text-gray-400">
                                            {{ $vendor->city }}, {{ $vendor->country }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full 
                                    {{ $vendor->vendor_type == 'international' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800' }}">
                                    {{ ucfirst($vendor->vendor_type) }}
                                </span>
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ $vendor->created_at->format('M Y') }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="space-y-1">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Orders:</span>
                                        <span class="font-medium">{{ number_format($vendor->stats['total_orders']) }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">On-Time Delivery:</span>
                                        <span class="font-medium {{ $vendor->stats['on_time_delivery'] >= 80 ? 'text-green-600' : 'text-yellow-600' }}">
                                            {{ number_format($vendor->stats['on_time_delivery'], 1) }}%
                                        </span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Reviews:</span>
                                        <span class="font-medium">{{ number_format($vendor->stats['total_reviews']) }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900">
                                    UGX {{ number_format($vendor->stats['total_revenue']) }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    Avg: UGX {{ number_format($vendor->stats['avg_order_value']) }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="mr-2">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= floor($vendor->stats['avg_rating']))
                                                <i class="fas fa-star text-yellow-400"></i>
                                            @elseif($i <= $vendor->stats['avg_rating'])
                                                <i class="fas fa-star-half-alt text-yellow-400"></i>
                                            @else
                                                <i class="far fa-star text-gray-300"></i>
                                            @endif
                                        @endfor
                                    </div>
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ number_format($vendor->stats['avg_rating'], 1) }}
                                    </div>
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ number_format($vendor->stats['total_reviews']) }} reviews
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('admin.vendors.show', $vendor) }}" 
                                   class="text-blue-600 hover:text-blue-900 mr-3">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="{{ route('admin.orders.index') }}?vendor={{ $vendor->id }}" 
                                   class="text-green-600 hover:text-green-900">
                                    <i class="fas fa-shopping-cart"></i> Orders
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @if($vendors->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $vendors->links() }}
            </div>
            @endif
        </div>

        <!-- Top Performers -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Top Revenue -->
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Revenue Vendors</h3>
                <div class="space-y-3">
                    @foreach($vendors->take(5) as $index => $vendor)
                    <div class="flex items-center justify-between p-2 hover:bg-gray-50 rounded">
                        <div class="flex items-center">
                            <span class="text-sm font-medium text-gray-500 w-6">{{ $index + 1 }}.</span>
                            <div class="ml-2">
                                <div class="text-sm font-medium text-gray-900 truncate max-w-xs">
                                    {{ $vendor->business_name }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    UGX {{ number_format($vendor->stats['total_revenue']) }}
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Top Rated -->
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Rated Vendors</h3>
                <div class="space-y-3">
                    @foreach($vendors->sortByDesc('stats.avg_rating')->take(5) as $index => $vendor)
                    <div class="flex items-center justify-between p-2 hover:bg-gray-50 rounded">
                        <div class="flex items-center">
                            <span class="text-sm font-medium text-gray-500 w-6">{{ $index + 1 }}.</span>
                            <div class="ml-2">
                                <div class="text-sm font-medium text-gray-900 truncate max-w-xs">
                                    {{ $vendor->business_name }}
                                </div>
                                <div class="flex items-center text-xs">
                                    <span class="text-yellow-400 mr-1">
                                        <i class="fas fa-star"></i>
                                    </span>
                                    {{ number_format($vendor->stats['avg_rating'], 1) }}
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Most Orders -->
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Most Orders</h3>
                <div class="space-y-3">
                    @foreach($vendors->sortByDesc('stats.total_orders')->take(5) as $index => $vendor)
                    <div class="flex items-center justify-between p-2 hover:bg-gray-50 rounded">
                        <div class="flex items-center">
                            <span class="text-sm font-medium text-gray-500 w-6">{{ $index + 1 }}.</span>
                            <div class="ml-2">
                                <div class="text-sm font-medium text-gray-900 truncate max-w-xs">
                                    {{ $vendor->business_name }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ number_format($vendor->stats['total_orders']) }} orders
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Revenue Distribution Chart
    const revenueCtx = document.getElementById('revenueDistributionChart').getContext('2d');
    const revenueData = {
        labels: ['Top 20%', 'Next 30%', 'Remaining 50%'],
        datasets: [{
            data: [65, 25, 10],
            backgroundColor: [
                '#10b981',
                '#3b82f6',
                '#f59e0b'
            ],
            borderWidth: 2,
            borderColor: '#ffffff'
        }]
    };

    new Chart(revenueCtx, {
        type: 'doughnut',
        data: revenueData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `${context.label}: ${context.raw}% of total revenue`;
                        }
                    }
                }
            }
        }
    });

    // Rating Distribution Chart
    const ratingCtx = document.getElementById('ratingDistributionChart').getContext('2d');
    new Chart(ratingCtx, {
        type: 'bar',
        data: {
            labels: ['1 Star', '2 Stars', '3 Stars', '4 Stars', '5 Stars'],
            datasets: [{
                label: 'Number of Vendors',
                data: [2, 5, 8, 15, 20],
                backgroundColor: [
                    '#ef4444',
                    '#f59e0b',
                    '#eab308',
                    '#84cc16',
                    '#10b981'
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