@extends('layouts.admin')

@section('title', 'Subscription Revenue - ' . config('app.name'))
@section('page-title', 'Subscription Revenue')
@section('page-description', 'Analytics and revenue tracking')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Subscription Revenue</h1>
            <p class="text-gray-600">Track subscription revenue and analytics</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.subscriptions.payments') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                <i class="fas fa-list mr-2"></i>All Payments
            </a>
            <a href="{{ route('admin.subscriptions.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                <i class="fas fa-arrow-left mr-2"></i>Subscriptions
            </a>
        </div>
    </div>

    <!-- Analytics Filters -->
    <div class="bg-white rounded-lg shadow-sm p-5 mb-6">
        <h3 class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-4">
            <i class="fas fa-filter mr-2 text-green-500"></i>Analytics Filters
        </h3>
        <form method="GET" id="revenueFilterForm" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Period preset -->
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Quick Period</label>
                    <select name="period" id="periodSelect"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary">
                        <option value="7"   {{ $period == '7'   && !$dateFrom ? 'selected' : '' }}>Last 7 days</option>
                        <option value="30"  {{ $period == '30'  && !$dateFrom ? 'selected' : '' }}>Last 30 days</option>
                        <option value="90"  {{ $period == '90'  && !$dateFrom ? 'selected' : '' }}>Last 90 days</option>
                        <option value="365" {{ $period == '365' && !$dateFrom ? 'selected' : '' }}>Last 12 months</option>
                    </select>
                </div>
                <!-- Custom date from -->
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        Custom From <span class="text-gray-400 font-normal">(overrides period)</span>
                    </label>
                    <input type="date" name="date_from" id="dateFrom" value="{{ $dateFrom }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary">
                </div>
                <!-- Custom date to -->
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Custom To</label>
                    <input type="date" name="date_to" id="dateTo" value="{{ $dateTo }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary">
                </div>
                <!-- Plan filter -->
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Subscription Plan</label>
                    <select name="plan_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary">
                        <option value="">All Plans</option>
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}" {{ $planId == $plan->id ? 'selected' : '' }}>
                                {{ $plan->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="flex gap-2 flex-wrap items-center">
                <button type="submit" class="px-5 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium">
                    <i class="fas fa-chart-line mr-1"></i>Update Analytics
                </button>
                <a href="{{ route('admin.subscriptions.revenue') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm">
                    <i class="fas fa-times mr-1"></i>Reset
                </a>

                <!-- Quick presets -->
                <div class="ml-2 flex gap-1 flex-wrap">
                    <button type="button" onclick="setRevenueDateRange(7)"  class="px-3 py-1.5 text-xs border rounded hover:bg-gray-100">7d</button>
                    <button type="button" onclick="setRevenueDateRange(30)" class="px-3 py-1.5 text-xs border rounded hover:bg-gray-100">30d</button>
                    <button type="button" onclick="setRevenueDateRange(90)" class="px-3 py-1.5 text-xs border rounded hover:bg-gray-100">90d</button>
                    <button type="button" onclick="setThisMonth()"          class="px-3 py-1.5 text-xs border rounded hover:bg-gray-100">This month</button>
                    <button type="button" onclick="setThisYear()"           class="px-3 py-1.5 text-xs border rounded hover:bg-gray-100">This year</button>
                </div>

                @if($dateFrom)
                <span class="ml-auto px-3 py-2 bg-green-50 text-green-700 rounded-lg text-xs font-medium border border-green-200">
                    <i class="fas fa-calendar mr-1"></i>
                    {{ \Carbon\Carbon::parse($dateFrom)->format('M d, Y') }} — {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}
                    @if($planId) &nbsp;·&nbsp; Plan: {{ $plans->firstWhere('id', $planId)?->name }} @endif
                </span>
                @endif
            </div>
        </form>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-green-500">
            <p class="text-sm text-gray-600">Total Revenue</p>
            <p class="text-2xl font-bold text-green-600">UGX {{ number_format($stats['total_revenue']) }}</p>
        </div>

        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-blue-500">
            <p class="text-sm text-gray-600">Payments</p>
            <p class="text-2xl font-bold">{{ $stats['total_payments'] }}</p>
        </div>

        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-purple-500">
            <p class="text-sm text-gray-600">Avg. Payment</p>
            <p class="text-2xl font-bold">UGX {{ number_format($stats['average_payment']) }}</p>
        </div>

        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-indigo-500">
            <p class="text-sm text-gray-600">MRR</p>
            <p class="text-2xl font-bold">UGX {{ number_format($stats['mrr']) }}</p>
        </div>

        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-teal-500">
            <p class="text-sm text-gray-600">Active Subs</p>
            <p class="text-2xl font-bold">{{ $stats['active_subscriptions'] }}</p>
        </div>

        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-orange-500">
            <p class="text-sm text-gray-600">Expiring Soon</p>
            <p class="text-2xl font-bold {{ $stats['expiring_soon'] > 0 ? 'text-orange-600' : '' }}">{{ $stats['expiring_soon'] }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Revenue Chart -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold mb-4">Revenue Over Time</h3>
            <canvas id="revenueChart" height="200"></canvas>
        </div>

        <!-- Revenue by Plan -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold mb-4">Revenue by Plan</h3>
            <canvas id="planChart" height="200"></canvas>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Revenue by Plan Table -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b">
                <h3 class="text-lg font-semibold">Revenue Breakdown</h3>
            </div>
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Plan</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Revenue</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Payments</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($revenueByPlan as $item)
                    <tr>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $item->name }}</td>
                        <td class="px-6 py-4 text-sm text-right text-green-600 font-semibold">UGX {{ number_format($item->total) }}</td>
                        <td class="px-6 py-4 text-sm text-right text-gray-500">{{ $item->count }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-6 py-4 text-center text-gray-500">No revenue data for this period</td>
                    </tr>
                    @endforelse
                </tbody>
                @if($revenueByPlan->count() > 0)
                <tfoot class="bg-gray-50 border-t-2">
                    <tr>
                        <td class="px-6 py-3 text-sm font-bold">Total</td>
                        <td class="px-6 py-3 text-right text-sm font-bold text-green-600">UGX {{ number_format($revenueByPlan->sum('total')) }}</td>
                        <td class="px-6 py-3 text-right text-sm font-bold">{{ $revenueByPlan->sum('count') }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        <!-- Active Subscriptions by Plan -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b">
                <h3 class="text-lg font-semibold">Active Subscriptions by Plan</h3>
            </div>
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Plan</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Subscribers</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Monthly Value</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($activeByPlan as $item)
                    <tr>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $item->name }}</td>
                        <td class="px-6 py-4 text-sm text-right">{{ $item->count }}</td>
                        <td class="px-6 py-4 text-sm text-right text-green-600 font-semibold">UGX {{ number_format($item->price * $item->count) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-6 py-4 text-center text-gray-500">No active subscriptions</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td class="px-6 py-4 text-sm font-bold">Total MRR</td>
                        <td class="px-6 py-4 text-sm text-right font-bold">{{ $activeByPlan->sum('count') }}</td>
                        <td class="px-6 py-4 text-sm text-right text-green-600 font-bold">UGX {{ number_format($stats['mrr']) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Revenue over time chart
    const revenueData = @json($revenueByDay);
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: revenueData.map(d => d.date),
            datasets: [{
                label: 'Revenue (UGX)',
                data: revenueData.map(d => d.total),
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
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

    // Revenue by plan chart
    const planData = @json($revenueByPlan);
    const planCtx = document.getElementById('planChart').getContext('2d');
    new Chart(planCtx, {
        type: 'doughnut',
        data: {
            labels: planData.map(d => d.name),
            datasets: [{
                data: planData.map(d => d.total),
                backgroundColor: ['#f59e0b', '#9ca3af', '#f97316', '#6366f1']
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } }
        }
    });
});

function setRevenueDateRange(days) {
    const today = new Date();
    const from  = new Date(today);
    from.setDate(today.getDate() - days);
    const fmt = d => d.toISOString().split('T')[0];
    document.getElementById('dateFrom').value = fmt(from);
    document.getElementById('dateTo').value   = fmt(today);
    document.getElementById('revenueFilterForm').submit();
}

function setThisMonth() {
    const now = new Date();
    const fmt = d => d.toISOString().split('T')[0];
    document.getElementById('dateFrom').value = fmt(new Date(now.getFullYear(), now.getMonth(), 1));
    document.getElementById('dateTo').value   = fmt(now);
    document.getElementById('revenueFilterForm').submit();
}

function setThisYear() {
    const now = new Date();
    const fmt = d => d.toISOString().split('T')[0];
    document.getElementById('dateFrom').value = fmt(new Date(now.getFullYear(), 0, 1));
    document.getElementById('dateTo').value   = fmt(now);
    document.getElementById('revenueFilterForm').submit();
}
</script>
@endsection
