@extends('layouts.admin')

@section('title', 'Financial Report')

@section('content')
<div class="py-4">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Financial Report</h1>
                    <p class="text-gray-600 mt-1">Platform financial performance and analysis</p>
                </div>
                <div class="flex items-center gap-3">
                    <button onclick="printReport()" 
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 flex items-center gap-2">
                        <i class="fas fa-print"></i> Print
                    </button>
                    <button onclick="exportFinancialPDF()" 
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 flex items-center gap-2">
                        <i class="fas fa-file-pdf"></i> Export PDF
                    </button>
                </div>
            </div>
        </div>

        <!-- Month/Year Filter -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <form method="GET" class="flex flex-col md:flex-row gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Year</label>
                    <select name="year" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        @for($y = 2023; $y <= now()->year; $y++)
                            <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Month</label>
                    <select name="month" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                                {{ Carbon\Carbon::create()->month($m)->format('F') }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Apply Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Financial Overview -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Monthly Revenue</p>
                        <p class="text-2xl font-bold text-green-600">UGX {{ number_format($monthlyRevenue) }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $monthlyOrders }} orders</p>
                    </div>
                    <div class="p-3 bg-green-100 rounded-full">
                        <i class="fas fa-money-bill-wave text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Platform Commission</p>
                        <p class="text-2xl font-bold text-blue-600">UGX {{ number_format($monthlyCommission) }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ round(($monthlyCommission / max($monthlyRevenue, 1)) * 100, 1) }}% of revenue</p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-full">
                        <i class="fas fa-percentage text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Net Profit</p>
                        <p class="text-2xl font-bold text-purple-600">UGX {{ number_format($monthlyRevenue - $monthlyCommission - $monthlyExpenses) }}</p>
                        <p class="text-xs text-gray-500 mt-1">After expenses</p>
                    </div>
                    <div class="p-3 bg-purple-100 rounded-full">
                        <i class="fas fa-chart-line text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Financials -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Income Statement -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Income Statement</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-gray-600">Gross Revenue</span>
                            <span class="font-semibold text-green-600">UGX {{ number_format($monthlyRevenue) }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-gray-600">Platform Commission</span>
                            <span class="font-semibold text-blue-600">UGX {{ number_format($monthlyCommission) }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-gray-600">Refunds & Cancellations</span>
                            <span class="font-semibold text-red-600">-UGX {{ number_format($monthlyRefunds) }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-gray-600">Operating Expenses</span>
                            <span class="font-semibold text-orange-600">-UGX {{ number_format($monthlyExpenses) }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-200">
                            <span class="font-semibold text-gray-900">Net Income</span>
                            <span class="font-bold text-purple-600">UGX {{ number_format($monthlyRevenue - $monthlyCommission - $monthlyExpenses - $monthlyRefunds) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payouts -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Vendor Payouts</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-gray-600">Total Payouts</span>
                            <span class="font-semibold text-green-600">UGX {{ number_format($payouts) }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-gray-600">Pending Payouts</span>
                            <span class="font-semibold text-yellow-600">UGX 0</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-gray-600">Processing Payouts</span>
                            <span class="font-semibold text-blue-600">UGX 0</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-gray-600">Failed Payouts</span>
                            <span class="font-semibold text-red-600">UGX 0</span>
                        </div>
                        <div class="text-sm text-gray-500 mt-4">
                            <i class="fas fa-info-circle mr-1"></i>
                            Payouts processed to vendors after order completion
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Yearly Trend Chart -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Yearly Revenue Trend - {{ $year }}</h3>
            </div>
            <div class="p-6">
                <div class="h-80">
                    <canvas id="yearlyTrendChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Monthly Comparison Table -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Monthly Performance - {{ $year }}</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Month
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Revenue
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Commission
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Orders
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Profit
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Margin
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($yearlyTrend as $monthData)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $monthData['month'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                UGX {{ number_format($monthData['revenue']) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                UGX {{ number_format($monthData['commission']) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ number_format($monthData['orders']) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold 
                                {{ $monthData['profit'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                UGX {{ number_format($monthData['profit']) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $monthData['revenue'] > 0 ? round(($monthData['profit'] / $monthData['revenue']) * 100, 1) : 0 }}%
                            </td>
                        </tr>
                        @endforeach
                        <!-- Totals Row -->
                        @php
                            $totalRevenue = collect($yearlyTrend)->sum('revenue');
                            $totalCommission = collect($yearlyTrend)->sum('commission');
                            $totalOrders = collect($yearlyTrend)->sum('orders');
                            $totalProfit = collect($yearlyTrend)->sum('profit');
                        @endphp
                        <tr class="bg-gray-50 font-semibold">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                {{ $year }} Total
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                UGX {{ number_format($totalRevenue) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                UGX {{ number_format($totalCommission) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                {{ number_format($totalOrders) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-green-600">
                                UGX {{ number_format($totalProfit) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                {{ $totalRevenue > 0 ? round(($totalProfit / $totalRevenue) * 100, 1) : 0 }}%
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Yearly Trend Chart
    const trendCtx = document.getElementById('yearlyTrendChart').getContext('2d');
    const trendData = {
        labels: @json(collect($yearlyTrend)->pluck('month')),
        datasets: [
            {
                label: 'Revenue',
                data: @json(collect($yearlyTrend)->pluck('revenue')),
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                borderWidth: 3,
                fill: true,
                yAxisID: 'y'
            },
            {
                label: 'Profit',
                data: @json(collect($yearlyTrend)->pluck('profit')),
                borderColor: '#8b5cf6',
                backgroundColor: 'rgba(139, 92, 246, 0.1)',
                borderWidth: 2,
                fill: false,
                yAxisID: 'y'
            },
            {
                label: 'Orders',
                data: @json(collect($yearlyTrend)->pluck('orders')),
                borderColor: '#3b82f6',
                backgroundColor: 'transparent',
                borderWidth: 2,
                fill: false,
                yAxisID: 'y1',
                type: 'line'
            }
        ]
    };

    new Chart(trendCtx, {
        type: 'bar',
        data: trendData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            stacked: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label.includes('Revenue') || label.includes('Profit')) {
                                return label + ': UGX ' + context.parsed.y.toLocaleString();
                            }
                            return label + ': ' + context.parsed.y.toLocaleString();
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
                        text: 'Amount (UGX)'
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

    // Print function
    function printReport() {
        window.print();
    }

    // Export PDF function (placeholder)
    function exportFinancialPDF() {
        alert('PDF export functionality would be implemented here with a PDF library.');
    }
</script>
@endpush
@endsection