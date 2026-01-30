@extends('layouts.ceo')

@section('title', 'CEO Dashboard')
@section('page-title', 'Dashboard Overview')
@section('page-description', 'Real-time business intelligence at a glance')

@section('content')
<!-- Today's Snapshot -->
<div class="mb-6 p-4 rounded-xl" style="background: linear-gradient(135deg, rgba(99,102,241,0.1), rgba(139,92,246,0.05)); border: 1px solid rgba(99,102,241,0.15);">
    <div class="flex items-center gap-2 mb-3">
        <i class="fas fa-sun text-indigo-500"></i>
        <span class="text-sm font-semibold text-indigo-400">Today's Snapshot</span>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="text-center">
            <div class="text-2xl font-bold text-white tabular-nums">UGX {{ number_format($todayRevenue, 2) }}</div>
            <div class="text-xs text-dark-400">Revenue Today</div>
        </div>
        <div class="text-center">
            <div class="text-2xl font-bold text-white tabular-nums">{{ number_format($todayOrders) }}</div>
            <div class="text-xs text-dark-400">Orders Today</div>
        </div>
        <div class="text-center">
            <div class="text-2xl font-bold text-white tabular-nums">{{ number_format($todayUsers) }}</div>
            <div class="text-xs text-dark-400">New Users Today</div>
        </div>
    </div>
</div>

<!-- KPI Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-6">
    @php
        $kpis = [
            ['label' => 'Total Revenue', 'value' => 'UGX ' . number_format($totalRevenue, 2), 'icon' => 'fa-dollar-sign', 'change' => $prevRevenue > 0 ? round(($totalRevenue - $prevRevenue) / $prevRevenue * 100, 1) : ($totalRevenue > 0 ? 100 : 0), 'color' => 'text-green-400'],
            ['label' => 'Total Orders', 'value' => number_format($totalOrders), 'icon' => 'fa-shopping-cart', 'change' => $prevOrders > 0 ? round(($totalOrders - $prevOrders) / $prevOrders * 100, 1) : ($totalOrders > 0 ? 100 : 0), 'color' => 'text-blue-400'],
            ['label' => 'New Users', 'value' => number_format($totalUsers), 'icon' => 'fa-users', 'change' => $prevUsers > 0 ? round(($totalUsers - $prevUsers) / $prevUsers * 100, 1) : ($totalUsers > 0 ? 100 : 0), 'color' => 'text-purple-400'],
            ['label' => 'Active Products', 'value' => number_format($totalProducts), 'icon' => 'fa-box', 'change' => null, 'color' => 'text-cyan-400'],
            ['label' => 'Commissions', 'value' => 'UGX ' . number_format($totalCommissions, 2), 'icon' => 'fa-percentage', 'change' => $prevCommissions > 0 ? round(($totalCommissions - $prevCommissions) / $prevCommissions * 100, 1) : ($totalCommissions > 0 ? 100 : 0), 'color' => 'text-indigo-400'],
            ['label' => 'Escrow Held', 'value' => 'UGX ' . number_format($escrowHeld, 2), 'icon' => 'fa-lock', 'change' => null, 'color' => 'text-orange-400'],
        ];
    @endphp
    @foreach($kpis as $kpi)
    <div class="kpi-card">
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center {{ $kpi['color'] }}" style="background: rgba(99,102,241,0.1);">
                <i class="fas {{ $kpi['icon'] }}"></i>
            </div>
            @if($kpi['change'] !== null)
                <span class="text-xs font-semibold {{ $kpi['change'] >= 0 ? 'trend-up' : 'trend-down' }}">
                    <i class="fas {{ $kpi['change'] >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }} mr-1"></i>{{ abs($kpi['change']) }}%
                </span>
            @endif
        </div>
        <div class="text-xl font-bold text-white tabular-nums">{{ $kpi['value'] }}</div>
        <div class="text-xs text-dark-400 mt-1">{{ $kpi['label'] }}</div>
    </div>
    @endforeach
</div>

<!-- Requires Attention -->
@if($pendingWithdrawals > 0 || $openDisputes > 0 || $pendingVendors > 0 || $outOfStockProducts > 0 || $lowStockProducts > 0)
<div class="chart-card mb-6" style="border-color: rgba(239,68,68,0.2);">
    <h3 class="text-sm font-semibold text-red-400 mb-4"><i class="fas fa-exclamation-circle mr-1"></i> Requires Attention</h3>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
        @if($pendingWithdrawals > 0)
        <div class="flex items-center gap-3 p-3 rounded-lg" style="background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.15);">
            <div class="w-9 h-9 rounded-lg flex items-center justify-center bg-red-500/10 text-red-400"><i class="fas fa-money-bill-wave"></i></div>
            <div>
                <div class="text-lg font-bold text-white tabular-nums">{{ $pendingWithdrawals }}</div>
                <div class="text-xs text-dark-400">Pending Withdrawals</div>
                <div class="text-xs text-red-400 tabular-nums">UGX {{ number_format($pendingWithdrawalAmount, 0) }}</div>
            </div>
        </div>
        @endif
        @if($openDisputes > 0)
        <div class="flex items-center gap-3 p-3 rounded-lg" style="background:rgba(99,102,241,0.08); border:1px solid rgba(99,102,241,0.15);">
            <div class="w-9 h-9 rounded-lg flex items-center justify-center bg-indigo-500/10 text-indigo-400"><i class="fas fa-gavel"></i></div>
            <div>
                <div class="text-lg font-bold text-white tabular-nums">{{ $openDisputes }}</div>
                <div class="text-xs text-dark-400">Open Disputes</div>
            </div>
        </div>
        @endif
        @if($pendingVendors > 0)
        <div class="flex items-center gap-3 p-3 rounded-lg" style="background:rgba(59,130,246,0.08); border:1px solid rgba(59,130,246,0.15);">
            <div class="w-9 h-9 rounded-lg flex items-center justify-center bg-blue-500/10 text-blue-400"><i class="fas fa-store"></i></div>
            <div>
                <div class="text-lg font-bold text-white tabular-nums">{{ $pendingVendors }}</div>
                <div class="text-xs text-dark-400">Vendor Applications</div>
            </div>
        </div>
        @endif
        @if($lowStockProducts > 0)
        <div class="flex items-center gap-3 p-3 rounded-lg" style="background:rgba(99,102,241,0.08); border:1px solid rgba(99,102,241,0.15);">
            <div class="w-9 h-9 rounded-lg flex items-center justify-center bg-yellow-500/10 text-yellow-400"><i class="fas fa-exclamation-triangle"></i></div>
            <div>
                <div class="text-lg font-bold text-white tabular-nums">{{ $lowStockProducts }}</div>
                <div class="text-xs text-dark-400">Low Stock (1-5)</div>
            </div>
        </div>
        @endif
        @if($outOfStockProducts > 0)
        <div class="flex items-center gap-3 p-3 rounded-lg" style="background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.15);">
            <div class="w-9 h-9 rounded-lg flex items-center justify-center bg-red-500/10 text-red-400"><i class="fas fa-times-circle"></i></div>
            <div>
                <div class="text-lg font-bold text-white tabular-nums">{{ $outOfStockProducts }}</div>
                <div class="text-xs text-dark-400">Out of Stock</div>
            </div>
        </div>
        @endif
    </div>
</div>
@endif

<!-- Charts Row -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Revenue Trend -->
    <div class="chart-card">
        <h3 class="text-sm font-semibold text-indigo-400 mb-4">12-Month Revenue Trend</h3>
        <div style="position:relative;height:280px;"><canvas id="revenueChart"></canvas></div>
    </div>

    <!-- User Growth -->
    <div class="chart-card">
        <h3 class="text-sm font-semibold text-indigo-400 mb-4">User Growth</h3>
        <div style="position:relative;height:280px;"><canvas id="userGrowthChart"></canvas></div>
    </div>
</div>

<!-- Order Status, Top Vendors & Recent Orders -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <!-- Order Status Distribution -->
    <div class="chart-card">
        <h3 class="text-sm font-semibold text-indigo-400 mb-4">Order Status Distribution</h3>
        <div style="position:relative;height:250px;"><canvas id="orderStatusChart"></canvas></div>
    </div>

    <!-- Top Vendors by Revenue -->
    <div class="chart-card lg:col-span-2">
        <h3 class="text-sm font-semibold text-indigo-400 mb-4"><i class="fas fa-trophy text-indigo-500 mr-1"></i> Top Vendors by Revenue</h3>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead><tr><th>#</th><th>Vendor</th><th>Revenue</th><th>Orders</th></tr></thead>
                <tbody>
                    @forelse($topVendorsByRevenue as $i => $v)
                    <tr>
                        <td class="text-indigo-500 font-bold">{{ $i + 1 }}</td>
                        <td>{{ $v->business_name ?? 'N/A' }}</td>
                        <td class="tabular-nums">UGX {{ number_format($v->revenue, 0) }}</td>
                        <td class="tabular-nums">{{ $v->order_count }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center text-dark-500 py-6">No vendor data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Recent Orders -->
<div class="grid grid-cols-1 gap-6">
    <!-- Recent Orders -->
    <div class="chart-card">
        <h3 class="text-sm font-semibold text-indigo-400 mb-4">Recent Orders</h3>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Buyer</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentOrders as $order)
                    <tr>
                        <td class="font-mono text-xs">{{ $order->order_number }}</td>
                        <td>{{ $order->buyer_name ?? 'N/A' }}</td>
                        <td>
                            <span class="status-badge status-{{ $order->status }}">{{ ucfirst($order->status) }}</span>
                        </td>
                        <td class="tabular-nums">UGX {{ number_format($order->total, 2) }}</td>
                        <td class="text-xs text-dark-400">{{ \Carbon\Carbon::parse($order->created_at)->format('M d, H:i') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-dark-500 py-8">No orders found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const chartDefaults = {
        color: '#94a3b8',
        borderColor: 'rgba(99,102,241,0.1)',
    };
    Chart.defaults.color = chartDefaults.color;
    Chart.defaults.borderColor = chartDefaults.borderColor;

    // Revenue Trend
    const revenueData = @json($monthlyRevenue);
    new Chart(document.getElementById('revenueChart'), {
        type: 'line',
        data: {
            labels: revenueData.map(d => d.month),
            datasets: [{
                label: 'Revenue (UGX)',
                data: revenueData.map(d => parseFloat(d.revenue)),
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99,102,241,0.1)',
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#6366f1',
                pointRadius: 3,
            }, {
                label: 'Orders',
                data: revenueData.map(d => parseInt(d.orders)),
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59,130,246,0.1)',
                fill: false,
                tension: 0.4,
                yAxisID: 'y1',
                pointRadius: 3,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { labels: { usePointStyle: true, boxWidth: 6 } } },
            scales: {
                y: { grid: { color: 'rgba(99,102,241,0.05)' }, ticks: { callback: v => 'UGX ' + v.toLocaleString() } },
                y1: { position: 'right', grid: { display: false }, ticks: { callback: v => v.toLocaleString() } },
                x: { grid: { color: 'rgba(99,102,241,0.05)' } }
            }
        }
    });

    // User Growth
    const userData = @json($userGrowth);
    new Chart(document.getElementById('userGrowthChart'), {
        type: 'bar',
        data: {
            labels: userData.map(d => d.month),
            datasets: [{
                label: 'New Users',
                data: userData.map(d => parseInt(d.count)),
                backgroundColor: 'rgba(99,102,241,0.6)',
                borderColor: '#6366f1',
                borderWidth: 1,
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { grid: { color: 'rgba(99,102,241,0.05)' }, beginAtZero: true },
                x: { grid: { display: false } }
            }
        }
    });

    // Order Status
    const statusData = @json($orderStatuses);
    const statusColors = {
        pending: '#fbbf24', confirmed: '#60a5fa', processing: '#818cf8',
        shipped: '#34d399', delivered: '#10b981', cancelled: '#f87171',
    };
    new Chart(document.getElementById('orderStatusChart'), {
        type: 'doughnut',
        data: {
            labels: statusData.map(d => d.status.charAt(0).toUpperCase() + d.status.slice(1)),
            datasets: [{
                data: statusData.map(d => parseInt(d.count)),
                backgroundColor: statusData.map(d => statusColors[d.status] || '#64748b'),
                borderWidth: 0,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8, padding: 15 } } }
        }
    });
});
</script>
@endsection
