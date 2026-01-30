@extends('layouts.ceo')

@section('title', 'Performance')
@section('page-title', 'Vendor Performance')
@section('page-description', 'Delivery metrics, fulfillment rates, and vendor scoreboard')

@section('content')
<!-- Platform Metrics -->
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
    @php
        $metrics = [
            ['label' => 'Avg Delivery Score', 'value' => number_format($avgMetrics->avg_delivery_score ?? 0, 1), 'icon' => 'fa-star', 'color' => 'text-indigo-400'],
            ['label' => 'Avg Delivery Days', 'value' => number_format($avgMetrics->avg_delivery_days ?? 0, 1), 'icon' => 'fa-truck', 'color' => 'text-blue-400'],
            ['label' => 'Avg Processing Hrs', 'value' => number_format($avgMetrics->avg_processing_hours ?? 0, 1), 'icon' => 'fa-clock', 'color' => 'text-purple-400'],
            ['label' => 'On-Time Rate', 'value' => number_format($avgMetrics->avg_on_time_rate ?? 0, 1) . '%', 'icon' => 'fa-check-circle', 'color' => 'text-green-400'],
            ['label' => 'Total Delivered', 'value' => number_format($avgMetrics->total_delivered ?? 0), 'icon' => 'fa-box-open', 'color' => 'text-cyan-400'],
            ['label' => 'Total Cancelled', 'value' => number_format($avgMetrics->total_cancelled ?? 0), 'icon' => 'fa-times-circle', 'color' => 'text-red-400'],
        ];
    @endphp
    @foreach($metrics as $m)
    <div class="kpi-card text-center">
        <i class="fas {{ $m['icon'] }} {{ $m['color'] }} text-lg mb-2"></i>
        <div class="text-xl font-bold text-white tabular-nums">{{ $m['value'] }}</div>
        <div class="text-xs text-dark-400">{{ $m['label'] }}</div>
    </div>
    @endforeach
</div>

<!-- Fulfillment & Delivery Distribution -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Fulfillment Breakdown -->
    <div class="chart-card">
        <h3 class="text-sm font-semibold text-indigo-400 mb-4">Order Fulfillment (Period)</h3>
        <div style="position:relative;height:250px;"><canvas id="fulfillmentChart"></canvas></div>
        @if($fulfillmentData->total > 0)
        <div class="grid grid-cols-3 gap-2 mt-4">
            <div class="text-center p-2 rounded" style="background:rgba(17,24,39,0.4);">
                <div class="text-lg font-bold text-green-400 tabular-nums">{{ round(($fulfillmentData->delivered / $fulfillmentData->total) * 100, 1) }}%</div>
                <div class="text-xs text-dark-400">Fulfilled</div>
            </div>
            <div class="text-center p-2 rounded" style="background:rgba(17,24,39,0.4);">
                <div class="text-lg font-bold text-red-400 tabular-nums">{{ round(($fulfillmentData->cancelled / $fulfillmentData->total) * 100, 1) }}%</div>
                <div class="text-xs text-dark-400">Cancelled</div>
            </div>
            <div class="text-center p-2 rounded" style="background:rgba(17,24,39,0.4);">
                <div class="text-lg font-bold text-blue-400 tabular-nums">{{ $fulfillmentData->total }}</div>
                <div class="text-xs text-dark-400">Total Orders</div>
            </div>
        </div>
        @endif
    </div>

    <!-- Delivery Time Distribution -->
    <div class="chart-card">
        <h3 class="text-sm font-semibold text-indigo-400 mb-4">Delivery Time Distribution</h3>
        <div style="position:relative;height:250px;"><canvas id="deliveryDistChart"></canvas></div>
    </div>
</div>

<!-- Vendor Scoreboard -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Top Vendors -->
    <div class="chart-card">
        <h3 class="text-sm font-semibold text-indigo-400 mb-4"><i class="fas fa-trophy text-indigo-500 mr-1"></i> Top 10 Vendors</h3>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead><tr><th>#</th><th>Vendor</th><th>Score</th><th>Orders</th><th>Delivered</th><th>On-Time</th></tr></thead>
                <tbody>
                    @forelse($topVendors as $i => $v)
                    <tr>
                        <td class="text-indigo-500 font-bold">{{ $i + 1 }}</td>
                        <td class="max-w-[150px] truncate">{{ $v->business_name ?? 'N/A' }}</td>
                        <td>
                            <span class="inline-flex items-center gap-1 text-sm font-bold {{ ($v->delivery_score ?? 0) >= 4 ? 'text-green-400' : (($v->delivery_score ?? 0) >= 2.5 ? 'text-indigo-400' : 'text-red-400') }}">
                                <i class="fas fa-star text-xs"></i>{{ number_format($v->delivery_score ?? 0, 1) }}
                            </span>
                        </td>
                        <td class="tabular-nums">{{ $v->total_orders }}</td>
                        <td class="tabular-nums">{{ $v->delivered_orders }}</td>
                        <td class="tabular-nums">{{ number_format($v->on_time_delivery_rate ?? 0, 0) }}%</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-dark-500 py-6">No data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bottom Vendors -->
    <div class="chart-card">
        <h3 class="text-sm font-semibold text-indigo-400 mb-4"><i class="fas fa-exclamation-triangle text-red-400 mr-1"></i> Bottom 10 Vendors</h3>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead><tr><th>#</th><th>Vendor</th><th>Score</th><th>Orders</th><th>Cancelled</th><th>On-Time</th></tr></thead>
                <tbody>
                    @forelse($bottomVendors as $i => $v)
                    <tr>
                        <td class="text-red-400 font-bold">{{ $i + 1 }}</td>
                        <td class="max-w-[150px] truncate">{{ $v->business_name ?? 'N/A' }}</td>
                        <td>
                            <span class="inline-flex items-center gap-1 text-sm font-bold text-red-400">
                                <i class="fas fa-star text-xs"></i>{{ number_format($v->delivery_score ?? 0, 1) }}
                            </span>
                        </td>
                        <td class="tabular-nums">{{ $v->total_orders }}</td>
                        <td class="tabular-nums text-red-400">{{ $v->cancelled_orders }}</td>
                        <td class="tabular-nums">{{ number_format($v->on_time_delivery_rate ?? 0, 0) }}%</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-dark-500 py-6">No data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Disputes & Vetting -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <!-- Disputes -->
    <div class="chart-card">
        <h3 class="text-sm font-semibold text-indigo-400 mb-4">Disputes ({{ $disputeTotal }})</h3>
        @if($disputeTotal > 0)
        <div style="position:relative;height:200px;"><canvas id="disputeChart"></canvas></div>
        <div class="grid grid-cols-1 gap-2 mt-4">
            @foreach($disputeStats as $ds)
            <div class="flex items-center justify-between text-sm p-2 rounded" style="background:rgba(17,24,39,0.3);">
                <span class="status-badge status-{{ $ds->status === 'open' ? 'pending' : ($ds->status === 'resolved' ? 'completed' : ($ds->status === 'rejected' ? 'failed' : 'processing')) }}">{{ ucfirst(str_replace('_', ' ', $ds->status)) }}</span>
                <span class="text-white font-bold tabular-nums">{{ $ds->count }}</span>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center text-dark-500 py-8">No disputes</div>
        @endif
    </div>

    <!-- Vetting Pipeline -->
    <div class="chart-card">
        <h3 class="text-sm font-semibold text-indigo-400 mb-4">Vendor Vetting Pipeline</h3>
        <div class="space-y-3">
            @foreach($vettingPipeline as $vp)
            @php
                $total = $vettingPipeline->sum('count');
                $pct = $total > 0 ? round(($vp->count / $total) * 100) : 0;
                $statusColor = match($vp->vetting_status) {
                    'approved' => 'bg-green-500', 'pending' => 'bg-yellow-500',
                    'rejected' => 'bg-red-500', default => 'bg-blue-500'
                };
            @endphp
            <div>
                <div class="flex items-center justify-between text-sm mb-1">
                    <span class="text-dark-300">{{ ucfirst(str_replace('_', ' ', $vp->vetting_status)) }}</span>
                    <span class="text-white font-bold tabular-nums">{{ $vp->count }}</span>
                </div>
                <div class="w-full h-2 rounded-full" style="background:rgba(99,102,241,0.1);">
                    <div class="{{ $statusColor }} h-2 rounded-full" style="width: {{ $pct }}%;"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Monthly Vendor Registrations -->
    <div class="chart-card">
        <h3 class="text-sm font-semibold text-indigo-400 mb-4">Monthly New Vendors</h3>
        <div style="position:relative;height:200px;"><canvas id="vendorRegChart"></canvas></div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    Chart.defaults.color = '#94a3b8';
    Chart.defaults.borderColor = 'rgba(99,102,241,0.1)';

    // Fulfillment Chart
    const fd = @json($fulfillmentData);
    new Chart(document.getElementById('fulfillmentChart'), {
        type: 'doughnut',
        data: {
            labels: ['Delivered', 'Shipped', 'Processing', 'Pending', 'Cancelled'],
            datasets: [{
                data: [fd.delivered, fd.shipped, fd.processing, fd.pending, fd.cancelled],
                backgroundColor: ['#10b981','#3b82f6','#8b5cf6','#a78bfa','#ef4444'],
                borderWidth: 0,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false, cutout: '60%',
            plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 6, padding: 10, font: { size: 10 } } } }
        }
    });

    // Delivery Distribution
    const dd = @json($deliveryDistribution);
    new Chart(document.getElementById('deliveryDistChart'), {
        type: 'bar',
        data: {
            labels: ['Same Day', '2-3 Days', '4-7 Days', '7+ Days'],
            datasets: [{
                data: [dd.same_day || 0, dd.two_three || 0, dd.four_seven || 0, dd.over_week || 0],
                backgroundColor: ['#10b981','#3b82f6','#6366f1','#ef4444'],
                borderWidth: 0, borderRadius: 4,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { grid: { color: 'rgba(99,102,241,0.05)' }, beginAtZero: true }, x: { grid: { display: false } } }
        }
    });

    // Disputes
    @if($disputeTotal > 0)
    const dsData = @json($disputeStats);
    const dsColors = { open: '#a78bfa', under_review: '#3b82f6', resolved: '#10b981', rejected: '#ef4444' };
    new Chart(document.getElementById('disputeChart'), {
        type: 'doughnut',
        data: {
            labels: dsData.map(d => d.status.replace('_',' ').replace(/\b\w/g, l => l.toUpperCase())),
            datasets: [{ data: dsData.map(d => d.count), backgroundColor: dsData.map(d => dsColors[d.status] || '#64748b'), borderWidth: 0 }]
        },
        options: {
            responsive: true, maintainAspectRatio: false, cutout: '55%',
            plugins: { legend: { display: false } }
        }
    });
    @endif

    // Monthly Vendor Registrations
    const vrData = @json($monthlyVendors);
    new Chart(document.getElementById('vendorRegChart'), {
        type: 'bar',
        data: {
            labels: vrData.map(d => d.month),
            datasets: [{ data: vrData.map(d => d.count), backgroundColor: 'rgba(99,102,241,0.6)', borderColor: '#6366f1', borderWidth: 1, borderRadius: 4 }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { grid: { color: 'rgba(99,102,241,0.05)' }, beginAtZero: true }, x: { grid: { display: false }, ticks: { maxTicksLimit: 6 } } }
        }
    });
});
</script>
@endsection
