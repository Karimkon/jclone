@extends('layouts.finance')

@section('title', 'Finance Dashboard')
@section('page-title', 'Finance Dashboard')
@section('page-description', 'Financial overview and metrics')

@section('content')
<div class="space-y-6">
    <!-- Revenue Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Total Sales</p>
                    <p class="text-2xl font-bold text-gray-900">${{ number_format($revenueStats['total_sales'], 2) }}</p>
                </div>
                <div class="bg-green-100 p-3 rounded-full">
                    <i class="fas fa-dollar-sign text-green-600 text-xl"></i>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-2">All time revenue</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Platform Commission</p>
                    <p class="text-2xl font-bold text-gray-900">${{ number_format($revenueStats['total_commission'], 2) }}</p>
                </div>
                <div class="bg-blue-100 p-3 rounded-full">
                    <i class="fas fa-percentage text-blue-600 text-xl"></i>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-2">8% commission earned</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Today's Sales</p>
                    <p class="text-2xl font-bold text-gray-900">${{ number_format($revenueStats['today_sales'], 2) }}</p>
                </div>
                <div class="bg-purple-100 p-3 rounded-full">
                    <i class="fas fa-calendar-day text-purple-600 text-xl"></i>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-2">{{ now()->format('M d, Y') }}</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-orange-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">This Month</p>
                    <p class="text-2xl font-bold text-gray-900">${{ number_format($revenueStats['month_sales'], 2) }}</p>
                </div>
                <div class="bg-orange-100 p-3 rounded-full">
                    <i class="fas fa-chart-line text-orange-600 text-xl"></i>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-2">{{ now()->format('F Y') }}</p>
        </div>
    </div>

    <!-- Escrow & Withdrawal Stats -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Escrow Overview -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-lock text-yellow-500 mr-2"></i>Escrow Overview
            </h3>
            <div class="grid grid-cols-3 gap-4">
                <div class="text-center p-4 bg-yellow-50 rounded-lg">
                    <p class="text-2xl font-bold text-yellow-600">${{ number_format($escrowStats['total_held'], 2) }}</p>
                    <p class="text-sm text-gray-600">Held</p>
                    <p class="text-xs text-gray-500">{{ $escrowStats['pending_count'] }} orders</p>
                </div>
                <div class="text-center p-4 bg-green-50 rounded-lg">
                    <p class="text-2xl font-bold text-green-600">${{ number_format($escrowStats['total_released'], 2) }}</p>
                    <p class="text-sm text-gray-600">Released</p>
                </div>
                <div class="text-center p-4 bg-red-50 rounded-lg">
                    <p class="text-2xl font-bold text-red-600">${{ number_format($escrowStats['total_refunded'], 2) }}</p>
                    <p class="text-sm text-gray-600">Refunded</p>
                </div>
            </div>
            <a href="{{ route('finance.escrows.index') }}" class="block mt-4 text-center text-sm text-green-600 hover:text-green-700">
                View All Escrows <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>

        <!-- Withdrawal Overview -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-money-bill-wave text-green-500 mr-2"></i>Withdrawal Overview
            </h3>
            <div class="grid grid-cols-2 gap-4">
                <div class="text-center p-4 bg-orange-50 rounded-lg">
                    <p class="text-2xl font-bold text-orange-600">{{ $withdrawalStats['pending'] }}</p>
                    <p class="text-sm text-gray-600">Pending</p>
                </div>
                <div class="text-center p-4 bg-blue-50 rounded-lg">
                    <p class="text-2xl font-bold text-blue-600">{{ $withdrawalStats['processing'] }}</p>
                    <p class="text-sm text-gray-600">Processing</p>
                </div>
            </div>
            <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Pending Amount:</span>
                    <span class="font-bold text-gray-900">${{ number_format($withdrawalStats['pending_amount'], 2) }}</span>
                </div>
                <div class="flex justify-between items-center mt-2">
                    <span class="text-sm text-gray-600">Paid This Month:</span>
                    <span class="font-bold text-green-600">${{ number_format($withdrawalStats['completed_this_month'], 2) }}</span>
                </div>
            </div>
            <a href="{{ route('finance.payouts.pending') }}" class="block mt-4 text-center text-sm text-green-600 hover:text-green-700">
                Process Withdrawals <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
    </div>

    <!-- Chart and Pending Withdrawals -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Revenue Chart -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-chart-area text-blue-500 mr-2"></i>Revenue (Last 7 Days)
            </h3>
            <canvas id="revenueChart" height="120"></canvas>
        </div>

        <!-- Pending Withdrawals Quick View -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-clock text-orange-500 mr-2"></i>Pending Withdrawals
            </h3>
            <div class="space-y-3">
                @forelse($pendingWithdrawals as $withdrawal)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div>
                            <p class="font-medium text-sm">{{ $withdrawal->vendor->user->name ?? 'Vendor' }}</p>
                            <p class="text-xs text-gray-500">{{ $withdrawal->method }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-green-600">${{ number_format($withdrawal->amount, 2) }}</p>
                            <span class="text-xs px-2 py-0.5 rounded-full {{ $withdrawal->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-blue-100 text-blue-700' }}">
                                {{ ucfirst($withdrawal->status) }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-check-circle text-4xl text-green-300 mb-2"></i>
                        <p>No pending withdrawals</p>
                    </div>
                @endforelse
            </div>
            @if($pendingWithdrawals->count() > 0)
                <a href="{{ route('finance.payouts.pending') }}" class="block mt-4 text-center text-sm text-green-600 hover:text-green-700">
                    View All <i class="fas fa-arrow-right ml-1"></i>
                </a>
            @endif
        </div>
    </div>

    <!-- Payment Gateway Stats -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="fas fa-credit-card text-purple-500 mr-2"></i>Payment Gateway Statistics
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="text-center p-4 border rounded-lg">
                <p class="text-2xl font-bold text-gray-900">${{ number_format($paymentStats['total_payments'], 2) }}</p>
                <p class="text-sm text-gray-600">Total Processed</p>
            </div>
            <div class="text-center p-4 border rounded-lg">
                <p class="text-2xl font-bold text-blue-600">${{ number_format($paymentStats['flutterwave'], 2) }}</p>
                <p class="text-sm text-gray-600">Flutterwave</p>
            </div>
            <div class="text-center p-4 border rounded-lg">
                <p class="text-2xl font-bold text-green-600">${{ number_format($paymentStats['pesapal'], 2) }}</p>
                <p class="text-sm text-gray-600">PesaPal</p>
            </div>
            <div class="text-center p-4 border rounded-lg">
                <p class="text-2xl font-bold text-red-600">{{ $paymentStats['failed_payments'] }}</p>
                <p class="text-sm text-gray-600">Failed Payments</p>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="bg-white rounded-xl shadow-sm">
        <div class="p-6 border-b">
            <h3 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-exchange-alt text-green-500 mr-2"></i>Recent Transactions
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vendor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($recentTransactions as $transaction)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $transaction->created_at->format('M d, H:i') }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $transaction->vendor->user->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded-full
                                    @if($transaction->type === 'sale') bg-green-100 text-green-700
                                    @elseif($transaction->type === 'commission') bg-blue-100 text-blue-700
                                    @elseif($transaction->type === 'withdrawal') bg-orange-100 text-orange-700
                                    @elseif($transaction->type === 'refund') bg-red-100 text-red-700
                                    @else bg-gray-100 text-gray-700
                                    @endif">
                                    {{ ucfirst($transaction->type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 font-mono">{{ $transaction->reference ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm font-bold text-right {{ $transaction->amount >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $transaction->amount >= 0 ? '+' : '' }}${{ number_format($transaction->amount, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">No transactions yet</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t">
            <a href="{{ route('finance.transactions.index') }}" class="text-sm text-green-600 hover:text-green-700">
                View All Transactions <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const ctx = document.getElementById('revenueChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($chartData['labels']) !!},
            datasets: [
                {
                    label: 'Sales',
                    data: {!! json_encode($chartData['sales']) !!},
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Commission',
                    data: {!! json_encode($chartData['commissions']) !!},
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: true,
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
</script>
@endsection
