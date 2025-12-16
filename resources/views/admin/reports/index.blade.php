@extends('layouts.admin')

@section('title', 'Reports Dashboard')

@section('content')
<div class="py-4">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Reports Dashboard</h1>
            <p class="text-gray-600 mt-1">Analyze platform performance and insights</p>
        </div>

        <!-- Quick Report Links -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
            <a href="{{ route('admin.reports.sales.detailed') }}?date_from={{ $dateFrom }}&date_to={{ $dateTo }}" 
               class="bg-white rounded-lg shadow p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Sales Detailed</h3>
                        <p class="text-sm text-gray-500">Transaction-level analysis</p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-full">
                        <i class="fas fa-shopping-cart text-blue-600"></i>
                    </div>
                </div>
                <div class="text-sm text-blue-600 font-medium flex items-center">
                    View Report <i class="fas fa-arrow-right ml-2 text-xs"></i>
                </div>
            </a>

            <a href="{{ route('admin.reports.financial') }}?year={{ date('Y') }}&month={{ date('n') }}" 
               class="bg-white rounded-lg shadow p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Financial Report</h3>
                        <p class="text-sm text-gray-500">Revenue, commission & profit</p>
                    </div>
                    <div class="p-3 bg-green-100 rounded-full">
                        <i class="fas fa-money-bill-wave text-green-600"></i>
                    </div>
                </div>
                <div class="text-sm text-green-600 font-medium flex items-center">
                    View Report <i class="fas fa-arrow-right ml-2 text-xs"></i>
                </div>
            </a>

            <a href="{{ route('admin.reports.user.acquisition') }}?date_from={{ $dateFrom }}&date_to={{ $dateTo }}" 
               class="bg-white rounded-lg shadow p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">User Acquisition</h3>
                        <p class="text-sm text-gray-500">User growth & demographics</p>
                    </div>
                    <div class="p-3 bg-purple-100 rounded-full">
                        <i class="fas fa-users text-purple-600"></i>
                    </div>
                </div>
                <div class="text-sm text-purple-600 font-medium flex items-center">
                    View Report <i class="fas fa-arrow-right ml-2 text-xs"></i>
                </div>
            </a>

            <a href="{{ route('admin.reports.vendor.performance') }}?date_from={{ $dateFrom }}&date_to={{ $dateTo }}" 
               class="bg-white rounded-lg shadow p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Vendor Performance</h3>
                        <p class="text-sm text-gray-500">Sales & rating metrics</p>
                    </div>
                    <div class="p-3 bg-yellow-100 rounded-full">
                        <i class="fas fa-store text-yellow-600"></i>
                    </div>
                </div>
                <div class="text-sm text-yellow-600 font-medium flex items-center">
                    View Report <i class="fas fa-arrow-right ml-2 text-xs"></i>
                </div>
            </a>

            <a href="{{ route('admin.reports.category.performance') }}?date_from={{ $dateFrom }}&date_to={{ $dateTo }}" 
               class="bg-white rounded-lg shadow p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Category Performance</h3>
                        <p class="text-sm text-gray-500">Sales by product categories</p>
                    </div>
                    <div class="p-3 bg-indigo-100 rounded-full">
                        <i class="fas fa-tags text-indigo-600"></i>
                    </div>
                </div>
                <div class="text-sm text-indigo-600 font-medium flex items-center">
                    View Report <i class="fas fa-arrow-right ml-2 text-xs"></i>
                </div>
            </a>

            <a href="{{ route('admin.reports.platform.analytics') }}?date_from={{ $dateFrom }}&date_to={{ $dateTo }}" 
               class="bg-white rounded-lg shadow p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Platform Analytics</h3>
                        <p class="text-sm text-gray-500">Traffic & engagement insights</p>
                    </div>
                    <div class="p-3 bg-pink-100 rounded-full">
                        <i class="fas fa-chart-line text-pink-600"></i>
                    </div>
                </div>
                <div class="text-sm text-pink-600 font-medium flex items-center">
                    View Report <i class="fas fa-arrow-right ml-2 text-xs"></i>
                </div>
            </a>
        </div>

        <!-- Date Filter -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <form method="GET" action="{{ route('admin.reports.index') }}" class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex items-end">
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Apply Filter
                    </button>
                </div>
                <div class="flex items-end">
                    <a href="{{ route('admin.reports.index') }}" 
                       class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <!-- Sales Card -->
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Total Revenue</p>
                        <p class="text-2xl font-bold text-gray-900">
                            UGX {{ number_format($salesStats['total_revenue']) }}
                        </p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-full">
                        <i class="fas fa-chart-line text-blue-600 text-xl"></i>
                    </div>
                </div>
                <div class="mt-2 text-sm text-gray-500">
                    {{ number_format($salesStats['total_orders']) }} orders
                </div>
            </div>

            <!-- Users Card -->
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">New Users</p>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ number_format($userStats['total_users']) }}
                        </p>
                    </div>
                    <div class="p-3 bg-green-100 rounded-full">
                        <i class="fas fa-users text-green-600 text-xl"></i>
                    </div>
                </div>
                <div class="mt-2 text-sm text-gray-500">
                    {{ $userStats['new_buyers'] }} buyers • {{ $userStats['new_vendors'] }} vendors
                </div>
            </div>

            <!-- Vendors Card -->
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Vendors</p>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ number_format($vendorStats['total_vendors']) }}
                        </p>
                    </div>
                    <div class="p-3 bg-purple-100 rounded-full">
                        <i class="fas fa-store text-purple-600 text-xl"></i>
                    </div>
                </div>
                <div class="mt-2 text-sm text-gray-500">
                    {{ $vendorStats['approved_vendors'] }} approved • {{ $vendorStats['pending_vendors'] }} pending
                </div>
            </div>

            <!-- Commission Card -->
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Platform Commission</p>
                        <p class="text-2xl font-bold text-gray-900">
                            UGX {{ number_format($salesStats['platform_commission']) }}
                        </p>
                    </div>
                    <div class="p-3 bg-yellow-100 rounded-full">
                        <i class="fas fa-money-bill-wave text-yellow-600 text-xl"></i>
                    </div>
                </div>
                <div class="mt-2 text-sm text-gray-500">
                    UGX {{ number_format($salesStats['refund_amount']) }} refunds
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Sales Trend Chart -->
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Sales Trend (Last 7 Days)</h3>
                <div class="h-64">
                    <canvas id="salesTrendChart"></canvas>
                </div>
            </div>

            <!-- Top Categories Chart -->
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Categories</h3>
                <div class="h-64">
                    <canvas id="categoriesChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Top Products Section -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="p-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Top Selling Products</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Product
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Category
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Vendor
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Quantity Sold
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Revenue
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($topProducts as $product)
                            @if($product->listing)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            @if($product->listing->images->first())
                                                <img class="h-10 w-10 rounded object-cover" 
                                                     src="{{ asset('storage/' . $product->listing->images->first()->path) }}" 
                                                     alt="{{ $product->listing->title }}">
                                            @else
                                                <div class="h-10 w-10 rounded bg-gray-200 flex items-center justify-center">
                                                    <i class="fas fa-image text-gray-400"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ Str::limit($product->listing->title, 40) }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ $product->listing->sku ?? 'N/A' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $product->listing->category->name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $product->listing->vendor->business_name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ number_format($product->total_quantity) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                    UGX {{ number_format($product->total_revenue) }}
                                </td>
                            </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                    No sales data available for this period
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top Vendors Section -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="p-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Top Performing Vendors</h3>
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
                                Orders
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Total Revenue
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Avg Order Value
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($topVendors as $vendor)
                            @if($vendor->vendorProfile)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            @if($vendor->vendorProfile->logo_path)
                                                <img class="h-10 w-10 rounded-full object-cover" 
                                                     src="{{ asset('storage/' . $vendor->vendorProfile->logo_path) }}" 
                                                     alt="{{ $vendor->vendorProfile->business_name }}">
                                            @else
                                                <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                    <i class="fas fa-store text-blue-600"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $vendor->vendorProfile->business_name }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ $vendor->vendorProfile->user->name ?? 'N/A' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span class="px-2 py-1 text-xs rounded-full 
                                        {{ $vendor->vendorProfile->vendor_type == 'international' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800' }}">
                                        {{ ucfirst($vendor->vendorProfile->vendor_type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ number_format($vendor->total_orders) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                    UGX {{ number_format($vendor->total_revenue) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    UGX {{ number_format($vendor->avg_order_value) }}
                                </td>
                            </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                    No vendor data available for this period
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Export Options -->
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Export Reports</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="{{ route('admin.reports.export', ['type' => 'sales', 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}" 
                   class="flex items-center justify-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                    <div class="text-center">
                        <i class="fas fa-chart-bar text-blue-600 text-2xl mb-2"></i>
                        <p class="font-medium text-blue-900">Sales Report</p>
                        <p class="text-sm text-blue-700">Export to CSV</p>
                    </div>
                </a>
                
                <a href="{{ route('admin.reports.export', ['type' => 'vendors', 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}" 
                   class="flex items-center justify-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition">
                    <div class="text-center">
                        <i class="fas fa-store text-purple-600 text-2xl mb-2"></i>
                        <p class="font-medium text-purple-900">Vendor Report</p>
                        <p class="text-sm text-purple-700">Export to CSV</p>
                    </div>
                </a>
                
                <a href="{{ route('admin.reports.export', ['type' => 'products', 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}" 
                   class="flex items-center justify-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition">
                    <div class="text-center">
                        <i class="fas fa-box text-green-600 text-2xl mb-2"></i>
                        <p class="font-medium text-green-900">Product Report</p>
                        <p class="text-sm text-green-700">Export to CSV</p>
                    </div>
                </a>
                
                <a href="{{ route('admin.reports.export', ['type' => 'users', 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}" 
                   class="flex items-center justify-center p-4 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition">
                    <div class="text-center">
                        <i class="fas fa-users text-yellow-600 text-2xl mb-2"></i>
                        <p class="font-medium text-yellow-900">User Report</p>
                        <p class="text-sm text-yellow-700">Export to CSV</p>
                    </div>
                </a>
            </div>
        </div>

        <!-- Report Guide -->
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg shadow p-6 mt-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">📊 Report Guide</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <h4 class="font-medium text-gray-900 mb-2">Available Reports:</h4>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mr-2 mt-0.5"></i>
                            <span><strong>Sales Detailed</strong> - Individual transaction analysis</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mr-2 mt-0.5"></i>
                            <span><strong>Financial Report</strong> - Revenue, expenses & profit margins</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mr-2 mt-0.5"></i>
                            <span><strong>User Acquisition</strong> - Signups, demographics & growth</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mr-2 mt-0.5"></i>
                            <span><strong>Vendor Performance</strong> - Sales, ratings & fulfillment metrics</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mr-2 mt-0.5"></i>
                            <span><strong>Category Performance</strong> - Sales by product categories</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mr-2 mt-0.5"></i>
                            <span><strong>Platform Analytics</strong> - Traffic, engagement & conversion</span>
                        </li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-medium text-gray-900 mb-2">Tips:</h4>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-start">
                            <i class="fas fa-lightbulb text-yellow-500 mr-2 mt-0.5"></i>
                            <span>Use date filters to analyze specific periods</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-lightbulb text-yellow-500 mr-2 mt-0.5"></i>
                            <span>Export reports for offline analysis</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-lightbulb text-yellow-500 mr-2 mt-0.5"></i>
                            <span>Compare month-over-month performance</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-lightbulb text-yellow-500 mr-2 mt-0.5"></i>
                            <span>Check vendor performance for commission optimization</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-lightbulb text-yellow-500 mr-2 mt-0.5"></i>
                            <span>Monitor user acquisition costs and conversion rates</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Sales Trend Chart
    const salesTrendCtx = document.getElementById('salesTrendChart').getContext('2d');
    new Chart(salesTrendCtx, {
        type: 'line',
        data: {
            labels: @json($salesTrend['dates']),
            datasets: [{
                label: 'Daily Revenue (UGX)',
                data: @json($salesTrend['sales']),
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'UGX ' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Categories Chart (Example - You'll need to implement getTopCategories in controller)
    const categoriesCtx = document.getElementById('categoriesChart').getContext('2d');
    // This is a placeholder - you need to add getTopCategories method in controller
    new Chart(categoriesCtx, {
        type: 'doughnut',
        data: {
            labels: ['Electronics', 'Fashion', 'Home & Garden', 'Others'],
            datasets: [{
                data: [40, 25, 20, 15],
                backgroundColor: [
                    '#3b82f6',
                    '#8b5cf6',
                    '#10b981',
                    '#f59e0b'
                ]
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
</script>
@endpush