{{-- resources/views/admin/analytics/products/show.blade.php --}}
@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    {{-- Header --}}
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('admin.analytics.products.index') }}" class="text-blue-600 hover:text-blue-800 mb-2 inline-block">
                    <i class="fas fa-arrow-left"></i> Back to Analytics
                </a>
                <h1 class="text-3xl font-bold">{{ $listing->title }}</h1>
                <p class="text-gray-600">SKU: {{ $listing->sku }} | {{ $listing->vendor->business_name }}</p>
            </div>
            
            <div class="text-right">
                <select id="daysFilter" class="px-4 py-2 border rounded mb-2" onchange="filterByDays(this.value)">
                    <option value="7" {{ $days == 7 ? 'selected' : '' }}>Last 7 days</option>
                    <option value="30" {{ $days == 30 ? 'selected' : '' }}>Last 30 days</option>
                    <option value="60" {{ $days == 60 ? 'selected' : '' }}>Last 60 days</option>
                    <option value="90" {{ $days == 90 ? 'selected' : '' }}>Last 90 days</option>
                </select>
                <div class="flex gap-2">
                    <a href="{{ route('marketplace.show', $listing) }}" target="_blank" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        <i class="fas fa-external-link-alt"></i> View Product
                    </a>
                    <a href="{{ route('admin.listings.edit', $listing) }}" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                        <i class="fas fa-edit"></i> Edit Product
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Key Metrics Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex items-center justify-between mb-2">
                <span class="text-gray-600">Total Views</span>
                <i class="fas fa-eye text-blue-500 text-xl"></i>
            </div>
            <div class="text-3xl font-bold">{{ number_format($metrics['total_views']) }}</div>
            <div class="text-sm text-gray-500 mt-1">All time</div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex items-center justify-between mb-2">
                <span class="text-gray-600">Total Clicks</span>
                <i class="fas fa-mouse-pointer text-purple-500 text-xl"></i>
            </div>
            <div class="text-3xl font-bold">{{ number_format($metrics['total_clicks']) }}</div>
            <div class="text-sm text-gray-500 mt-1">Detail page visits</div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex items-center justify-between mb-2">
                <span class="text-gray-600">Purchases</span>
                <i class="fas fa-shopping-cart text-green-500 text-xl"></i>
            </div>
            <div class="text-3xl font-bold">{{ number_format($metrics['total_purchases']) }}</div>
            <div class="text-sm text-gray-500 mt-1">Total sales</div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex items-center justify-between mb-2">
                <span class="text-gray-600">Conversion Rate</span>
                <i class="fas fa-chart-line text-orange-500 text-xl"></i>
            </div>
            <div class="text-3xl font-bold">{{ $metrics['conversion_rate'] }}%</div>
            <div class="text-sm {{ $metrics['conversion_rate'] >= 5 ? 'text-green-600' : 'text-red-600' }} mt-1">
                {{ $metrics['conversion_rate'] >= 5 ? 'Good' : 'Needs improvement' }}
            </div>
        </div>
    </div>

    {{-- Additional Metrics --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="text-gray-600 mb-2">Cart Additions</div>
            <div class="text-2xl font-bold">{{ number_format($metrics['total_cart_adds']) }}</div>
            <div class="mt-2">
                @php
                    $cartToSale = $metrics['total_cart_adds'] > 0 
                        ? round(($metrics['total_purchases'] / $metrics['total_cart_adds']) * 100, 1) 
                        : 0;
                @endphp
                <span class="text-sm text-gray-600">Cart-to-sale rate: </span>
                <span class="font-semibold {{ $cartToSale >= 50 ? 'text-green-600' : 'text-orange-600' }}">
                    {{ $cartToSale }}%
                </span>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow">
            <div class="text-gray-600 mb-2">Wishlist Additions</div>
            <div class="text-2xl font-bold">{{ number_format($metrics['total_wishlist']) }}</div>
            <div class="mt-2">
                <span class="text-sm text-gray-600">Interest indicator</span>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow">
            <div class="text-gray-600 mb-2">Current Stock</div>
            <div class="text-2xl font-bold {{ $listing->stock < 10 ? 'text-red-600' : 'text-green-600' }}">
                {{ number_format($listing->stock) }}
            </div>
            <div class="mt-2">
                <span class="text-sm text-gray-600">Price: ${{ number_format($listing->price, 2) }}</span>
            </div>
        </div>
    </div>

    {{-- Performance Chart --}}
    <div class="bg-white rounded-lg shadow mb-8 p-6">
        <h2 class="text-xl font-semibold mb-4">Performance Trend (Last {{ $days }} Days)</h2>
        <canvas id="performanceChart" height="80"></canvas>
    </div>

    {{-- Two Column Layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        {{-- Traffic Sources --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Traffic Sources</h2>
            <div class="space-y-3">
                @forelse($source_stats as $source)
                @php
                    $total = $source_stats->sum('count');
                    $percentage = $total > 0 ? round(($source->count / $total) * 100, 1) : 0;
                @endphp
                <div>
                    <div class="flex justify-between items-center mb-1">
                        <span class="font-medium capitalize">{{ $source->source }}</span>
                        <span class="text-gray-600">{{ number_format($source->count) }} ({{ $percentage }}%)</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                    </div>
                </div>
                @empty
                <p class="text-gray-500">No traffic data available</p>
                @endforelse
            </div>
        </div>

        {{-- Device Breakdown --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Device Breakdown</h2>
            <div class="space-y-3">
                @forelse($device_stats as $device)
                @php
                    $total = $device_stats->sum('count');
                    $percentage = $total > 0 ? round(($device->count / $total) * 100, 1) : 0;
                    $colors = [
                        'mobile' => 'bg-purple-600',
                        'desktop' => 'bg-blue-600',
                        'tablet' => 'bg-green-600'
                    ];
                    $color = $colors[$device->device_type] ?? 'bg-gray-600';
                @endphp
                <div>
                    <div class="flex justify-between items-center mb-1">
                        <span class="font-medium capitalize">{{ $device->device_type }}</span>
                        <span class="text-gray-600">{{ number_format($device->count) }} ({{ $percentage }}%)</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="{{ $color }} h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                    </div>
                </div>
                @empty
                <p class="text-gray-500">No device data available</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Daily Performance Table --}}
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <h2 class="text-xl font-semibold">Daily Performance</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Views</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Clicks</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cart Adds</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Purchases</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Conv. Rate</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($daily_stats as $stat)
                    @php
                        $convRate = $stat->clicks > 0 ? round(($stat->purchases / $stat->clicks) * 100, 1) : 0;
                    @endphp
                    <tr>
                        <td class="px-6 py-4">{{ \Carbon\Carbon::parse($stat->date)->format('M d, Y') }}</td>
                        <td class="px-6 py-4">{{ number_format($stat->views) }}</td>
                        <td class="px-6 py-4">{{ number_format($stat->clicks) }}</td>
                        <td class="px-6 py-4">{{ number_format($stat->cart_adds) }}</td>
                        <td class="px-6 py-4">{{ number_format($stat->purchases) }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded {{ $convRate >= 5 ? 'bg-green-100 text-green-800' : ($convRate > 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                                {{ $convRate }}%
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No data available for this period</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function filterByDays(days) {
    window.location.href = '{{ route('admin.analytics.products.show', $listing->id) }}?days=' + days;
}

// Performance Chart
const ctx = document.getElementById('performanceChart').getContext('2d');
const dailyData = @json($daily_stats);

new Chart(ctx, {
    type: 'line',
    data: {
        labels: dailyData.map(d => {
            const date = new Date(d.date);
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        }),
        datasets: [
            {
                label: 'Views',
                data: dailyData.map(d => d.views),
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.3
            },
            {
                label: 'Clicks',
                data: dailyData.map(d => d.clicks),
                borderColor: 'rgb(168, 85, 247)',
                backgroundColor: 'rgba(168, 85, 247, 0.1)',
                tension: 0.3
            },
            {
                label: 'Cart Adds',
                data: dailyData.map(d => d.cart_adds),
                borderColor: 'rgb(234, 179, 8)',
                backgroundColor: 'rgba(234, 179, 8, 0.1)',
                tension: 0.3
            },
            {
                label: 'Purchases',
                data: dailyData.map(d => d.purchases),
                borderColor: 'rgb(34, 197, 94)',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                tension: 0.3
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom'
            },
            tooltip: {
                mode: 'index',
                intersect: false
            }
        },
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
@endsection