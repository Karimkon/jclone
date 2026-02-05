@extends('layouts.admin')

@section('title', 'Subscription Revenue - ' . config('app.name'))
@section('page-title', 'Subscription Revenue')
@section('page-description', 'Analytics and revenue tracking')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Subscription Revenue</h1>
            <p class="text-gray-600">Track subscription revenue and analytics</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.subscriptions.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                <i class="fas fa-arrow-left mr-2"></i>Back
            </a>
            <form method="GET" class="flex gap-2">
                <select name="period" onchange="this.form.submit()"
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                    <option value="7" {{ $period == '7' ? 'selected' : '' }}>Last 7 days</option>
                    <option value="30" {{ $period == '30' ? 'selected' : '' }}>Last 30 days</option>
                    <option value="90" {{ $period == '90' ? 'selected' : '' }}>Last 90 days</option>
                    <option value="365" {{ $period == '365' ? 'selected' : '' }}>Last year</option>
                </select>
            </form>
        </div>
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
                        <td colspan="3" class="px-6 py-4 text-center text-gray-500">No revenue data</td>
                    </tr>
                    @endforelse
                </tbody>
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
            plugins: {
                legend: { display: false }
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

    // Revenue by plan chart
    const planData = @json($revenueByPlan);
    const planCtx = document.getElementById('planChart').getContext('2d');
    new Chart(planCtx, {
        type: 'doughnut',
        data: {
            labels: planData.map(d => d.name),
            datasets: [{
                data: planData.map(d => d.total),
                backgroundColor: [
                    '#6366f1',
                    '#f59e0b',
                    '#9ca3af',
                    '#ef4444'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
});
</script>
@endsection
