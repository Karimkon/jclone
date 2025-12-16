@extends('layouts.admin')

@section('title', 'Platform Analytics')

@section('content')
<div class="py-4">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Platform Analytics</h1>
                    <p class="text-gray-600 mt-1">Traffic, engagement, and conversion insights</p>
                </div>
                <div class="flex items-center gap-3">
                    <button onclick="refreshAnalytics()" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2">
                        <i class="fas fa-sync-alt"></i> Refresh Data
                    </button>
                    <button onclick="exportAnalytics()" 
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center gap-2">
                        <i class="fas fa-download"></i> Export
                    </button>
                </div>
            </div>
        </div>

        <!-- Date Filter -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <form method="GET" action="{{ route('admin.reports.platform.analytics') }}" class="flex flex-col md:flex-row gap-4">
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
                    <a href="{{ route('admin.reports.platform.analytics') }}" 
                       class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Key Metrics -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Total Visitors</p>
                        <p class="text-2xl font-bold text-blue-600">10,250</p>
                    </div>
                    <div class="p-2 bg-blue-100 rounded-full">
                        <i class="fas fa-users text-blue-600"></i>
                    </div>
                </div>
                <div class="mt-2 text-xs text-green-600">
                    <i class="fas fa-arrow-up mr-1"></i>12.5% from last period
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Conversion Rate</p>
                        <p class="text-2xl font-bold text-green-600">2.8%</p>
                    </div>
                    <div class="p-2 bg-green-100 rounded-full">
                        <i class="fas fa-percentage text-green-600"></i>
                    </div>
                </div>
                <div class="mt-2 text-xs text-green-600">
                    <i class="fas fa-arrow-up mr-1"></i>0.4% improvement
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Avg Session</p>
                        <p class="text-2xl font-bold text-purple-600">3:45</p>
                    </div>
                    <div class="p-2 bg-purple-100 rounded-full">
                        <i class="fas fa-clock text-purple-600"></i>
                    </div>
                </div>
                <div class="mt-2 text-xs text-red-600">
                    <i class="fas fa-arrow-down mr-1"></i>0:15 decrease
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Bounce Rate</p>
                        <p class="text-2xl font-bold text-yellow-600">42%</p>
                    </div>
                    <div class="p-2 bg-yellow-100 rounded-full">
                        <i class="fas fa-sign-out-alt text-yellow-600"></i>
                    </div>
                </div>
                <div class="mt-2 text-xs text-green-600">
                    <i class="fas fa-arrow-down mr-1"></i>3% improvement
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Traffic Sources -->
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Traffic Sources</h3>
                <div class="h-64">
                    <canvas id="trafficSourcesChart"></canvas>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-2">
                    @foreach($trafficSources as $source)
                    <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                        <span class="text-sm text-gray-700">{{ $source['source'] }}</span>
                        <span class="text-sm font-medium text-gray-900">{{ number_format($source['visits']) }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Device Breakdown -->
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Device Breakdown</h3>
                <div class="h-64">
                    <canvas id="deviceBreakdownChart"></canvas>
                </div>
                <div class="mt-4">
                    @foreach($deviceBreakdown as $device)
                    <div class="mb-2">
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-700">{{ $device['device'] }}</span>
                            <span class="font-medium text-gray-900">{{ $device['percentage'] }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full" 
                                 style="width: {{ $device['percentage'] }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- User Engagement -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">User Engagement Metrics</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($userEngagement as $metric => $value)
                    <div class="text-center">
                        <div class="text-3xl font-bold 
                            @if(strpos($value, '%')) text-purple-600
                            @elseif(strpos($value, 'm')) text-green-600
                            @else text-blue-600
                            @endif mb-2">
                            {{ $value }}
                        </div>
                        <p class="text-sm text-gray-600 capitalize">
                            {{ str_replace('_', ' ', $metric) }}
                        </p>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Conversion Funnel -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Conversion Funnel</h3>
                <p class="text-sm text-gray-600 mt-1">User journey from visit to purchase</p>
            </div>
            <div class="p-6">
                <div class="relative">
                    <!-- Funnel Visualization -->
                    <div class="flex flex-col items-center">
                        @foreach($conversionFunnel as $index => $stage)
                        <div class="w-full max-w-2xl mb-4">
                            <div class="flex items-center justify-between mb-2">
                                <div>
                                    <span class="text-sm font-medium text-gray-900">{{ $stage['stage'] }}</span>
                                    <span class="ml-2 text-sm text-gray-500">{{ number_format($stage['count']) }}</span>
                                </div>
                                <div class="text-sm font-medium text-gray-900">
                                    @if($index > 0)
                                        @php
                                            $prevCount = $conversionFunnel[$index - 1]['count'];
                                            $conversionRate = $prevCount > 0 ? round(($stage['count'] / $prevCount) * 100, 1) : 0;
                                        @endphp
                                        {{ $conversionRate }}% conversion
                                    @endif
                                </div>
                            </div>
                            <div class="relative">
                                <!-- Funnel segment -->
                                <div class="h-10 rounded-lg 
                                    @switch($index)
                                        @case(0) bg-blue-500 @break
                                        @case(1) bg-blue-400 @break
                                        @case(2) bg-blue-300 @break
                                        @case(3) bg-blue-200 @break
                                        @case(4) bg-blue-100 @break
                                    @endswitch" 
                                    style="width: {{ 100 - ($index * 15) }}%">
                                </div>
                                <!-- Drop percentage -->
                                @if($index > 0)
                                <div class="absolute right-0 top-1/2 transform -translate-y-1/2 translate-x-6">
                                    <div class="text-xs text-red-600 whitespace-nowrap">
                                        <i class="fas fa-arrow-down mr-1"></i>
                                        @php
                                            $prevCount = $conversionFunnel[$index - 1]['count'];
                                            $dropPercentage = $prevCount > 0 ? round((($prevCount - $stage['count']) / $prevCount) * 100, 1) : 0;
                                        @endphp
                                        {{ $dropPercentage }}% dropoff
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                    
                    <!-- Overall Conversion -->
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-600">
                                @php
                                    $first = $conversionFunnel[0]['count'];
                                    $last = $conversionFunnel[count($conversionFunnel) - 1]['count'];
                                    $overallConversion = $first > 0 ? round(($last / $first) * 100, 2) : 0;
                                @endphp
                                {{ $overallConversion }}%
                            </div>
                            <p class="text-sm text-gray-600">Overall conversion rate (Visitors → Orders)</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Geographic Distribution -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Top Countries -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Countries by Traffic</h3>
                <div class="space-y-3">
                    @php
                        $countries = [
                            ['name' => 'Uganda', 'visitors' => 4250, 'percentage' => 41.5],
                            ['name' => 'Kenya', 'visitors' => 1850, 'percentage' => 18.0],
                            ['name' => 'Tanzania', 'visitors' => 1200, 'percentage' => 11.7],
                            ['name' => 'Rwanda', 'visitors' => 850, 'percentage' => 8.3],
                            ['name' => 'Other', 'visitors' => 2100, 'percentage' => 20.5],
                        ];
                    @endphp
                    
                    @foreach($countries as $country)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center mr-3">
                                <i class="fas fa-globe-africa text-blue-600 text-sm"></i>
                            </div>
                            <span class="text-sm text-gray-900">{{ $country['name'] }}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-32 bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full" 
                                     style="width: {{ $country['percentage'] }}%"></div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-medium text-gray-900">{{ number_format($country['visitors']) }}</div>
                                <div class="text-xs text-gray-500">{{ $country['percentage'] }}%</div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Time of Day Analysis -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Peak Traffic Hours</h3>
                <div class="h-64">
                    <canvas id="peakHoursChart"></canvas>
                </div>
                <div class="mt-4 text-sm text-gray-600">
                    <i class="fas fa-info-circle mr-1"></i>
                    Peak traffic occurs between 7 PM - 9 PM local time
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Traffic Sources Chart
    const trafficCtx = document.getElementById('trafficSourcesChart').getContext('2d');
    new Chart(trafficCtx, {
        type: 'polarArea',
        data: {
            labels: @json(collect($trafficSources)->pluck('source')),
            datasets: [{
                data: @json(collect($trafficSources)->pluck('visits')),
                backgroundColor: [
                    '#3b82f6', // Direct
                    '#10b981', // Organic
                    '#8b5cf6', // Social
                    '#f59e0b', // Referral
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
                    position: 'right'
                }
            }
        }
    });

    // Device Breakdown Chart
    const deviceCtx = document.getElementById('deviceBreakdownChart').getContext('2d');
    new Chart(deviceCtx, {
        type: 'pie',
        data: {
            labels: @json(collect($deviceBreakdown)->pluck('device')),
            datasets: [{
                data: @json(collect($deviceBreakdown)->pluck('percentage')),
                backgroundColor: [
                    '#3b82f6', // Mobile
                    '#10b981', // Desktop
                    '#f59e0b', // Tablet
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
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `${context.label}: ${context.raw}%`;
                        }
                    }
                }
            }
        }
    });

    // Peak Hours Chart
    const peakCtx = document.getElementById('peakHoursChart').getContext('2d');
    const hours = Array.from({length: 24}, (_, i) => i);
    const trafficData = hours.map(hour => {
        // Simulate traffic pattern
        const base = 100;
        const peak = 350;
        const peakHour = 19; // 7 PM
        const variance = Math.abs(hour - peakHour);
        return Math.round(base + (peak - base) * Math.exp(-variance * variance / 8));
    });

    new Chart(peakCtx, {
        type: 'line',
        data: {
            labels: hours.map(h => `${h}:00`),
            datasets: [{
                label: 'Visitors',
                data: trafficData,
                borderColor: '#8b5cf6',
                backgroundColor: 'rgba(139, 92, 246, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        maxTicksLimit: 12
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });

    function refreshAnalytics() {
        // Show loading state
        const button = event.target.closest('button');
        const originalHTML = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Refreshing...';
        button.disabled = true;

        // Simulate API call
        setTimeout(() => {
            location.reload();
        }, 1500);
    }

    function exportAnalytics() {
        alert('Analytics export functionality would be implemented here.');
    }
</script>
@endpush
@endsection