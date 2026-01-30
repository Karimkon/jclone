@extends('layouts.ceo')

@section('title', 'Analytics')
@section('page-title', 'Analytics')
@section('page-description', 'Deep dive into products, users, and market trends')

@section('content')
<!-- Customer Retention -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="kpi-card text-center">
        <i class="fas fa-user-check text-green-400 text-lg mb-2"></i>
        <div class="text-2xl font-bold text-white tabular-nums">{{ number_format($totalBuyers) }}</div>
        <div class="text-xs text-dark-400">Unique Buyers</div>
    </div>
    <div class="kpi-card text-center">
        <i class="fas fa-redo text-indigo-400 text-lg mb-2"></i>
        <div class="text-2xl font-bold text-white tabular-nums">{{ number_format($repeatBuyers) }}</div>
        <div class="text-xs text-dark-400">Repeat Buyers</div>
        @if($totalBuyers > 0)
        <div class="text-xs text-indigo-500 mt-1">{{ round(($repeatBuyers / $totalBuyers) * 100, 1) }}% retention</div>
        @endif
    </div>
    <div class="kpi-card text-center">
        <i class="fas fa-chart-bar text-blue-400 text-lg mb-2"></i>
        <div class="text-2xl font-bold text-white tabular-nums">{{ $avgOrdersPerBuyer }}</div>
        <div class="text-xs text-dark-400">Avg Orders/Buyer</div>
    </div>
</div>

<!-- Conversion Funnel -->
<div class="chart-card mb-6">
    <h3 class="text-sm font-semibold text-indigo-400 mb-4">Conversion Funnel</h3>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @php
            $funnelSteps = [
                ['label' => 'Views', 'value' => $funnel->views ?? 0, 'icon' => 'fa-eye', 'color' => 'text-blue-400'],
                ['label' => 'Clicks', 'value' => $funnel->clicks ?? 0, 'icon' => 'fa-mouse-pointer', 'color' => 'text-purple-400'],
                ['label' => 'Add to Cart', 'value' => $funnel->carts ?? 0, 'icon' => 'fa-cart-plus', 'color' => 'text-indigo-400'],
                ['label' => 'Purchases', 'value' => $funnel->purchases ?? 0, 'icon' => 'fa-check-circle', 'color' => 'text-green-400'],
            ];
        @endphp
        @foreach($funnelSteps as $i => $step)
        <div class="text-center p-4 rounded-lg" style="background: rgba(17,24,39,0.4);">
            <i class="fas {{ $step['icon'] }} {{ $step['color'] }} text-2xl mb-2"></i>
            <div class="text-xl font-bold text-white tabular-nums">{{ number_format($step['value']) }}</div>
            <div class="text-xs text-dark-400">{{ $step['label'] }}</div>
            @if($i > 0 && ($funnelSteps[$i-1]['value'] ?? 0) > 0)
                <div class="text-xs text-indigo-500 mt-1">{{ round(($step['value'] / $funnelSteps[$i-1]['value']) * 100, 1) }}%</div>
            @endif
        </div>
        @endforeach
    </div>
</div>

<!-- Top Products & Categories -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Top Products -->
    <div class="chart-card">
        <h3 class="text-sm font-semibold text-indigo-400 mb-4">Top 10 Products by Revenue</h3>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead><tr><th>#</th><th>Product</th><th>Revenue</th><th>Units</th></tr></thead>
                <tbody>
                    @forelse($topProducts as $i => $p)
                    <tr>
                        <td class="text-indigo-500 font-bold">{{ $i + 1 }}</td>
                        <td class="max-w-[200px] truncate">{{ $p->title }}</td>
                        <td class="tabular-nums">UGX {{ number_format($p->revenue, 2) }}</td>
                        <td class="tabular-nums">{{ number_format($p->units_sold) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center text-dark-500 py-6">No data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Top Categories -->
    <div class="chart-card">
        <h3 class="text-sm font-semibold text-indigo-400 mb-4">Top Categories by Revenue</h3>
        <div style="position:relative;height:280px;"><canvas id="categoriesChart"></canvas></div>
    </div>
</div>

<!-- User Analytics -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <!-- Daily Registrations -->
    <div class="chart-card lg:col-span-2">
        <h3 class="text-sm font-semibold text-indigo-400 mb-4">Daily User Registrations (30 Days)</h3>
        <div style="position:relative;height:220px;"><canvas id="registrationsChart"></canvas></div>
    </div>

    <!-- User Role Distribution -->
    <div class="chart-card">
        <h3 class="text-sm font-semibold text-indigo-400 mb-4">User Roles</h3>
        <div style="position:relative;height:220px;"><canvas id="rolesChart"></canvas></div>
    </div>
</div>

<!-- Vendor & Review Analytics -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Vendor Type Distribution -->
    <div class="chart-card">
        <h3 class="text-sm font-semibold text-indigo-400 mb-4">Vendor Types</h3>
        <div class="grid grid-cols-1 gap-3">
            @foreach($vendorTypes as $vt)
            <div class="flex items-center justify-between p-3 rounded-lg" style="background: rgba(17,24,39,0.4);">
                <span class="text-sm text-dark-300">{{ str_replace('_', ' ', ucfirst($vt->vendor_type)) }}</span>
                <span class="text-sm font-bold text-indigo-400 tabular-nums">{{ $vt->count }}</span>
            </div>
            @endforeach
        </div>
        <h4 class="text-xs font-semibold text-dark-400 mt-4 mb-2">Top Countries</h4>
        <div class="grid grid-cols-2 gap-2">
            @foreach($vendorCountries as $vc)
            <div class="flex items-center justify-between text-xs p-2 rounded" style="background: rgba(17,24,39,0.3);">
                <span class="text-dark-300">{{ $vc->country }}</span>
                <span class="text-indigo-400 font-semibold tabular-nums">{{ $vc->count }}</span>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Review Stats -->
    <div class="chart-card">
        <h3 class="text-sm font-semibold text-indigo-400 mb-4">Review Analytics</h3>
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div class="text-center p-3 rounded-lg" style="background: rgba(17,24,39,0.4);">
                <div class="text-2xl font-bold text-white tabular-nums">{{ number_format($reviewStats->total ?? 0) }}</div>
                <div class="text-xs text-dark-400">Total Reviews</div>
            </div>
            <div class="text-center p-3 rounded-lg" style="background: rgba(17,24,39,0.4);">
                <div class="text-2xl font-bold text-indigo-400 tabular-nums">{{ number_format($reviewStats->avg_rating ?? 0, 1) }}</div>
                <div class="text-xs text-dark-400">Avg Rating</div>
            </div>
            <div class="text-center p-3 rounded-lg" style="background: rgba(17,24,39,0.4);">
                <div class="text-2xl font-bold text-green-400 tabular-nums">{{ number_format($reviewStats->positive ?? 0) }}</div>
                <div class="text-xs text-dark-400">Positive (4-5)</div>
            </div>
            <div class="text-center p-3 rounded-lg" style="background: rgba(17,24,39,0.4);">
                <div class="text-2xl font-bold text-red-400 tabular-nums">{{ number_format($reviewStats->negative ?? 0) }}</div>
                <div class="text-xs text-dark-400">Negative (1-2)</div>
            </div>
        </div>
        <h4 class="text-xs font-semibold text-dark-400 mb-2">Rating Distribution</h4>
        <div style="position:relative;height:120px;"><canvas id="ratingChart"></canvas></div>
    </div>
</div>

<!-- Order Patterns -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="chart-card">
        <h3 class="text-sm font-semibold text-indigo-400 mb-4">Orders by Hour of Day</h3>
        <div style="position:relative;height:220px;"><canvas id="hourlyChart"></canvas></div>
    </div>
    <div class="chart-card">
        <h3 class="text-sm font-semibold text-indigo-400 mb-4">Orders by Day of Week</h3>
        <div style="position:relative;height:220px;"><canvas id="dailyChart"></canvas></div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    Chart.defaults.color = '#94a3b8';
    Chart.defaults.borderColor = 'rgba(99,102,241,0.1)';

    // Categories Chart
    const catData = @json($topCategories);
    new Chart(document.getElementById('categoriesChart'), {
        type: 'bar',
        data: {
            labels: catData.map(d => d.name),
            datasets: [{
                label: 'Revenue',
                data: catData.map(d => parseFloat(d.revenue)),
                backgroundColor: 'rgba(99,102,241,0.6)',
                borderColor: '#6366f1',
                borderWidth: 1,
                borderRadius: 4,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { color: 'rgba(99,102,241,0.05)' }, ticks: { callback: v => 'UGX ' + v.toLocaleString() } },
                y: { grid: { display: false } }
            }
        }
    });

    // Daily Registrations
    const regData = @json($dailyRegistrations);
    new Chart(document.getElementById('registrationsChart'), {
        type: 'line',
        data: {
            labels: regData.map(d => d.date),
            datasets: [{
                label: 'Registrations',
                data: regData.map(d => parseInt(d.count)),
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99,102,241,0.1)',
                fill: true, tension: 0.4, pointRadius: 2,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { grid: { color: 'rgba(99,102,241,0.05)' }, beginAtZero: true },
                x: { grid: { display: false }, ticks: { maxTicksLimit: 10 } }
            }
        }
    });

    // User Roles
    const rolesData = @json($userRoles);
    const roleColors = ['#6366f1','#3b82f6','#10b981','#8b5cf6','#ef4444','#ec4899','#06b6d4','#84cc16'];
    new Chart(document.getElementById('rolesChart'), {
        type: 'doughnut',
        data: {
            labels: rolesData.map(d => d.role.replace('_',' ').replace(/\b\w/g, l => l.toUpperCase())),
            datasets: [{ data: rolesData.map(d => d.count), backgroundColor: roleColors.slice(0, rolesData.length), borderWidth: 0 }]
        },
        options: {
            responsive: true, maintainAspectRatio: false, cutout: '60%',
            plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 6, padding: 10, font: { size: 10 } } } }
        }
    });

    // Rating Distribution
    const ratingData = @json($ratingDistribution);
    const ratingColors = ['#ef4444','#f97316','#a78bfa','#84cc16','#10b981'];
    new Chart(document.getElementById('ratingChart'), {
        type: 'bar',
        data: {
            labels: ratingData.map(d => d.rating + ' Star'),
            datasets: [{ data: ratingData.map(d => d.count), backgroundColor: ratingData.map(d => ratingColors[d.rating - 1] || '#64748b'), borderWidth: 0, borderRadius: 4 }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { grid: { color: 'rgba(99,102,241,0.05)' }, beginAtZero: true }, x: { grid: { display: false } } }
        }
    });

    // Hourly Orders
    const hourData = @json($ordersByHour);
    const hours = Array.from({length: 24}, (_, i) => i);
    const hourCounts = hours.map(h => { const found = hourData.find(d => d.hour === h); return found ? found.count : 0; });
    new Chart(document.getElementById('hourlyChart'), {
        type: 'bar',
        data: {
            labels: hours.map(h => h.toString().padStart(2,'0') + ':00'),
            datasets: [{ data: hourCounts, backgroundColor: 'rgba(99,102,241,0.5)', borderRadius: 2 }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { grid: { color: 'rgba(99,102,241,0.05)' }, beginAtZero: true }, x: { grid: { display: false }, ticks: { maxTicksLimit: 12 } } }
        }
    });

    // Daily Orders
    const dayData = @json($ordersByDay);
    new Chart(document.getElementById('dailyChart'), {
        type: 'bar',
        data: {
            labels: dayData.map(d => d.day),
            datasets: [{ data: dayData.map(d => d.count), backgroundColor: 'rgba(99,102,241,0.6)', borderColor: '#6366f1', borderWidth: 1, borderRadius: 4 }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { grid: { color: 'rgba(99,102,241,0.05)' }, beginAtZero: true }, x: { grid: { display: false } } }
        }
    });
});
</script>
@endsection
