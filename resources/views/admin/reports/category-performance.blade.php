@extends('layouts.admin')

@section('title', 'Category Performance Report')

@section('content')
<div class="py-4">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Category Performance Report</h1>
                    <p class="text-gray-600 mt-1">Sales and performance metrics by product category</p>
                </div>
                <button onclick="printReport()" 
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 flex items-center gap-2">
                    <i class="fas fa-print"></i> Print Report
                </button>
            </div>
        </div>

        <!-- Date Filter -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <form method="GET" action="{{ route('admin.reports.category.performance') }}" class="flex flex-col md:flex-row gap-4">
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
                    <a href="{{ route('admin.reports.category.performance') }}" 
                       class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Category Performance Summary -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            @php
                $topCategories = $categories->take(4);
            @endphp
            
            @foreach($topCategories as $index => $category)
                @php
                    $colors = ['bg-blue-500', 'bg-green-500', 'bg-purple-500', 'bg-yellow-500'];
                    $textColors = ['text-blue-500', 'text-green-500', 'text-purple-500', 'text-yellow-500'];
                @endphp
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <div class="text-xs text-gray-500 uppercase tracking-wide font-medium">#{{ $index + 1 }}</div>
                            <h4 class="text-sm font-semibold text-gray-900 truncate">{{ $category->name }}</h4>
                        </div>
                        <div class="p-2 rounded-full {{ str_replace('text', 'bg', $textColors[$index]) }} bg-opacity-10">
                            <i class="fas {{ $category->icon ?? 'fa-tag' }} {{ $textColors[$index] }}"></i>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Revenue:</span>
                            <span class="font-semibold {{ $textColors[$index] }}">
                                UGX {{ number_format($category->sales_data['total_revenue']) }}
                            </span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Orders:</span>
                            <span class="font-medium">{{ number_format($category->sales_data['total_orders']) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Products:</span>
                            <span class="font-medium">{{ number_format($category->listings_count) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Buyers:</span>
                            <span class="font-medium">{{ number_format($category->sales_data['unique_buyers']) }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Category Performance Chart -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Category Revenue Performance</h3>
            </div>
            <div class="p-6">
                <div class="h-80">
                    <canvas id="categoryPerformanceChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Detailed Category Table -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900">Category Performance Details</h3>
                    <div class="text-sm text-gray-600">
                        Showing {{ $categories->count() }} categories
                    </div>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Category
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Products
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Total Orders
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Quantity Sold
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Total Revenue
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Unique Buyers
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Avg Order Value
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($categories as $category)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 bg-blue-50 rounded-lg flex items-center justify-center">
                                        <i class="fas {{ $category->icon ?? 'fa-tag' }} text-blue-600"></i>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $category->name }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            @if($category->parent)
                                                {{ $category->parent->name }}
                                            @else
                                                Parent Category
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    {{ number_format($category->listings_count) }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    Active products
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    {{ number_format($category->sales_data['total_orders']) }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    {{ number_format($category->sales_data['total_quantity']) }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    units sold
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900">
                                    UGX {{ number_format($category->sales_data['total_revenue']) }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    {{ number_format($category->sales_data['unique_buyers']) }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    unique customers
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    UGX {{ 
                                        $category->sales_data['total_orders'] > 0 
                                            ? number_format($category->sales_data['total_revenue'] / $category->sales_data['total_orders'])
                                            : 0 
                                    }}
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Subcategory Analysis -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Performing Subcategories</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @php
                    $topSubcategories = collect();
                    foreach ($categories as $category) {
                        foreach ($category->children->take(2) as $child) {
                            $topSubcategories->push($child);
                        }
                    }
                    $topSubcategories = $topSubcategories->sortByDesc(function($cat) {
                        return $cat->sales_data['total_revenue'] ?? 0;
                    })->take(6);
                @endphp
                
                @foreach($topSubcategories as $subcategory)
                <div class="border rounded-lg p-4 hover:bg-gray-50 transition">
                    <div class="flex items-center justify-between mb-2">
                        <div class="text-sm font-medium text-gray-900 truncate">
                            {{ $subcategory->name }}
                        </div>
                        <div class="text-xs text-gray-500">
                            {{ $subcategory->parent->name ?? 'N/A' }}
                        </div>
                    </div>
                    <div class="space-y-1 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Revenue:</span>
                            <span class="font-semibold text-green-600">
                                UGX {{ number_format($subcategory->sales_data['total_revenue'] ?? 0) }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Products:</span>
                            <span>{{ number_format($subcategory->listings_count) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Growth:</span>
                            <span class="text-green-600">+12.5%</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Category Performance Chart
    const categoryCtx = document.getElementById('categoryPerformanceChart').getContext('2d');
    const top10Categories = @json($categories->take(10));
    
    const categoryData = {
        labels: top10Categories.map(cat => cat.name.substring(0, 15) + (cat.name.length > 15 ? '...' : '')),
        datasets: [
            {
                label: 'Revenue (UGX)',
                data: top10Categories.map(cat => cat.sales_data.total_revenue),
                backgroundColor: [
                    '#3b82f6', '#10b981', '#8b5cf6', '#f59e0b', '#ef4444',
                    '#84cc16', '#06b6d4', '#f97316', '#8b5cf6', '#64748b'
                ],
                borderWidth: 1
            },
            {
                label: 'Orders',
                data: top10Categories.map(cat => cat.sales_data.total_orders),
                backgroundColor: 'rgba(255, 255, 255, 0.8)',
                borderColor: '#374151',
                borderWidth: 1,
                type: 'line',
                yAxisID: 'y1'
            }
        ]
    };

    new Chart(categoryCtx, {
        type: 'bar',
        data: categoryData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            if (context.dataset.label === 'Revenue (UGX)') {
                                return `Revenue: UGX ${context.raw.toLocaleString()}`;
                            }
                            return `Orders: ${context.raw.toLocaleString()}`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Revenue (UGX)'
                    },
                    ticks: {
                        callback: function(value) {
                            return 'UGX ' + value.toLocaleString();
                        }
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Orders'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });

    function printReport() {
        window.print();
    }
</script>
@endpush
@endsection