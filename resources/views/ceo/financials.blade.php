@extends('layouts.ceo')

@section('title', 'Financials')
@section('page-title', 'Financial Reports')
@section('page-description', 'Revenue, payments, escrow, and withdrawal insights')

@section('content')
<!-- Financial KPIs -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="kpi-card">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center text-green-400" style="background:rgba(16,185,129,0.1);"><i class="fas fa-dollar-sign"></i></div>
            <div class="text-xs text-dark-400">Period Revenue</div>
        </div>
        <div class="text-2xl font-bold text-white tabular-nums">UGX {{ number_format($periodRevenue, 2) }}</div>
    </div>
    <div class="kpi-card">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center text-indigo-400" style="background:rgba(99,102,241,0.1);"><i class="fas fa-percentage"></i></div>
            <div class="text-xs text-dark-400">Commission Earned</div>
        </div>
        <div class="text-2xl font-bold text-white tabular-nums">UGX {{ number_format($periodCommission, 2) }}</div>
    </div>
    <div class="kpi-card">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center text-blue-400" style="background:rgba(59,130,246,0.1);"><i class="fas fa-receipt"></i></div>
            <div class="text-xs text-dark-400">Avg Order Value</div>
        </div>
        <div class="text-2xl font-bold text-white tabular-nums">UGX {{ number_format($periodAvgOrderValue, 2) }}</div>
    </div>
    <div class="kpi-card">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center text-red-400" style="background:rgba(239,68,68,0.1);"><i class="fas fa-undo"></i></div>
            <div class="text-xs text-dark-400">Refunds</div>
        </div>
        <div class="text-2xl font-bold text-white tabular-nums">UGX {{ number_format($refundStats->total ?? 0, 2) }}</div>
        <div class="text-xs text-dark-500">{{ $refundStats->count ?? 0 }} transactions</div>
    </div>
</div>

<!-- Revenue & Commission Trend -->
<div class="chart-card mb-6">
    <h3 class="text-sm font-semibold text-indigo-400 mb-4">Monthly Revenue & Commission (12 Months)</h3>
    <div style="position:relative;height:280px;"><canvas id="financialTrendChart"></canvas></div>
</div>

<!-- Payment Breakdown -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Payment Status -->
    <div class="chart-card">
        <h3 class="text-sm font-semibold text-indigo-400 mb-4">Payment Status Breakdown</h3>
        <div class="grid grid-cols-1 gap-3 mb-4">
            @foreach($paymentStatuses as $ps)
            <div class="flex items-center justify-between p-3 rounded-lg" style="background:rgba(17,24,39,0.4);">
                <div class="flex items-center gap-2">
                    <span class="status-badge status-{{ $ps->status }}">{{ ucfirst($ps->status) }}</span>
                </div>
                <div class="text-right">
                    <div class="text-sm font-bold text-white tabular-nums">UGX {{ number_format($ps->total, 2) }}</div>
                    <div class="text-xs text-dark-500">{{ $ps->count }} payments</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Payment Providers -->
    <div class="chart-card">
        <h3 class="text-sm font-semibold text-indigo-400 mb-4">Payment Providers</h3>
        <div style="position:relative;height:200px;"><canvas id="providerChart"></canvas></div>
        <div class="grid grid-cols-1 gap-2 mt-4">
            @foreach($paymentProviders as $pp)
            <div class="flex items-center justify-between text-sm p-2 rounded" style="background:rgba(17,24,39,0.3);">
                <span class="text-dark-300">{{ ucfirst($pp->provider) }}</span>
                <div class="flex items-center gap-3">
                    <span class="text-dark-400">{{ $pp->count }} txns</span>
                    <span class="text-indigo-400 font-bold tabular-nums">UGX {{ number_format($pp->total, 2) }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Escrow & Withdrawals -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Escrow -->
    <div class="chart-card">
        <h3 class="text-sm font-semibold text-indigo-400 mb-4">Escrow Summary</h3>
        <div class="grid grid-cols-1 gap-3">
            @foreach($escrowSummary as $es)
            <div class="flex items-center justify-between p-3 rounded-lg" style="background:rgba(17,24,39,0.4);">
                <span class="status-badge status-{{ $es->status === 'held' ? 'pending' : ($es->status === 'released' ? 'completed' : 'failed') }}">{{ ucfirst($es->status) }}</span>
                <div class="text-right">
                    <div class="text-sm font-bold text-white tabular-nums">UGX {{ number_format($es->total, 2) }}</div>
                    <div class="text-xs text-dark-500">{{ $es->count }} escrows</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Withdrawals -->
    <div class="chart-card">
        <h3 class="text-sm font-semibold text-indigo-400 mb-4">Withdrawal Summary</h3>
        <div class="grid grid-cols-1 gap-3">
            @foreach($withdrawalSummary as $ws)
            <div class="flex items-center justify-between p-3 rounded-lg" style="background:rgba(17,24,39,0.4);">
                <span class="status-badge status-{{ $ws->status }}">{{ ucfirst($ws->status) }}</span>
                <div class="text-right">
                    <div class="text-sm font-bold text-white tabular-nums">UGX {{ number_format($ws->total, 2) }}</div>
                    <div class="text-xs text-dark-500">{{ $ws->count }} requests | Fees: UGX {{ number_format($ws->total_fees, 2) }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Vendor Balances & Promotions -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <!-- Vendor Balances -->
    <div class="chart-card">
        <h3 class="text-sm font-semibold text-indigo-400 mb-4">Vendor Balances</h3>
        <div class="space-y-4">
            <div class="text-center p-4 rounded-lg" style="background:rgba(17,24,39,0.4);">
                <div class="text-2xl font-bold text-green-400 tabular-nums">UGX {{ number_format($vendorBalancesTotal, 2) }}</div>
                <div class="text-xs text-dark-400">Available Balance</div>
            </div>
            <div class="text-center p-4 rounded-lg" style="background:rgba(17,24,39,0.4);">
                <div class="text-2xl font-bold text-indigo-400 tabular-nums">UGX {{ number_format($vendorPendingTotal, 2) }}</div>
                <div class="text-xs text-dark-400">Pending Balance</div>
            </div>
            <div class="text-center p-4 rounded-lg" style="background:rgba(17,24,39,0.4);">
                <div class="text-xl font-bold text-white tabular-nums">{{ $vendorBalanceCount }}</div>
                <div class="text-xs text-dark-400">Vendors with Balance</div>
            </div>
        </div>
    </div>

    <!-- Revenue by Vendor Type -->
    <div class="chart-card">
        <h3 class="text-sm font-semibold text-indigo-400 mb-4">Revenue by Vendor Type</h3>
        <div style="position:relative;height:200px;"><canvas id="vendorTypeRevenueChart"></canvas></div>
    </div>

    <!-- Promotions -->
    <div class="chart-card">
        <h3 class="text-sm font-semibold text-indigo-400 mb-4">Promotion Revenue</h3>
        @forelse($promotionRevenue as $pr)
        <div class="flex items-center justify-between p-3 rounded-lg mb-2" style="background:rgba(17,24,39,0.4);">
            <span class="text-sm text-dark-300">{{ str_replace('_', ' ', ucfirst($pr->type)) }}</span>
            <div class="text-right">
                <div class="text-sm font-bold text-indigo-400 tabular-nums">UGX {{ number_format($pr->total_fees, 2) }}</div>
                <div class="text-xs text-dark-500">{{ $pr->count }} promotions</div>
            </div>
        </div>
        @empty
        <div class="text-center text-dark-500 py-8">No promotion data</div>
        @endforelse
    </div>
</div>

<!-- Monthly Financial Table -->
<div class="chart-card">
    <h3 class="text-sm font-semibold text-indigo-400 mb-4">Monthly Financial Summary</h3>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr><th>Month</th><th>Revenue</th><th>Commission</th><th>Orders</th><th>Avg Order</th></tr>
            </thead>
            <tbody>
                @foreach($monthlyFinancials as $mf)
                <tr>
                    <td>{{ $mf->month }}</td>
                    <td class="tabular-nums">UGX {{ number_format($mf->revenue, 2) }}</td>
                    <td class="tabular-nums">UGX {{ number_format($mf->commission, 2) }}</td>
                    <td class="tabular-nums">{{ number_format($mf->order_count) }}</td>
                    <td class="tabular-nums">UGX {{ number_format($mf->avg_order_value, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    Chart.defaults.color = '#94a3b8';
    Chart.defaults.borderColor = 'rgba(99,102,241,0.1)';

    // Financial Trend
    const finData = @json($monthlyFinancials);
    new Chart(document.getElementById('financialTrendChart'), {
        type: 'line',
        data: {
            labels: finData.map(d => d.month),
            datasets: [{
                label: 'Revenue',
                data: finData.map(d => parseFloat(d.revenue)),
                borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.1)',
                fill: true, tension: 0.4, pointRadius: 3,
            }, {
                label: 'Commission',
                data: finData.map(d => parseFloat(d.commission)),
                borderColor: '#6366f1', backgroundColor: 'rgba(99,102,241,0.1)',
                fill: true, tension: 0.4, pointRadius: 3,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { labels: { usePointStyle: true, boxWidth: 6 } } },
            scales: {
                y: { grid: { color: 'rgba(99,102,241,0.05)' }, ticks: { callback: v => 'UGX ' + v.toLocaleString() } },
                x: { grid: { color: 'rgba(99,102,241,0.05)' } }
            }
        }
    });

    // Provider Chart
    const provData = @json($paymentProviders);
    const provColors = ['#6366f1','#3b82f6','#10b981','#8b5cf6','#ef4444','#06b6d4'];
    new Chart(document.getElementById('providerChart'), {
        type: 'pie',
        data: {
            labels: provData.map(d => d.provider),
            datasets: [{ data: provData.map(d => parseFloat(d.total)), backgroundColor: provColors.slice(0, provData.length), borderWidth: 0 }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 6, padding: 10, font: { size: 10 } } } }
        }
    });

    // Revenue by Vendor Type
    const vtData = @json($revenueByVendorType);
    new Chart(document.getElementById('vendorTypeRevenueChart'), {
        type: 'doughnut',
        data: {
            labels: vtData.map(d => d.vendor_type.replace('_',' ').replace(/\b\w/g, l => l.toUpperCase())),
            datasets: [{ data: vtData.map(d => parseFloat(d.revenue)), backgroundColor: ['#6366f1','#3b82f6','#10b981'], borderWidth: 0 }]
        },
        options: {
            responsive: true, maintainAspectRatio: false, cutout: '60%',
            plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 6, font: { size: 10 } } } }
        }
    });
});
</script>
@endsection
