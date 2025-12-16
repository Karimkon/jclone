@extends('layouts.admin')

@section('title', 'User Acquisition Report')

@section('content')
<div class="py-4">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">User Acquisition Report</h1>
                    <p class="text-gray-600 mt-1">User growth, demographics, and engagement metrics</p>
                </div>
                <a href="{{ route('admin.reports.export', ['type' => 'users', 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}" 
                   class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2">
                    <i class="fas fa-download"></i> Export CSV
                </a>
            </div>
        </div>

        <!-- Date Filter -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <form method="GET" action="{{ route('admin.reports.user.acquisition') }}" class="flex flex-col md:flex-row gap-4">
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
                    <a href="{{ route('admin.reports.user.acquisition') }}" 
                       class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Total New Users</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_users']) }}</p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-full">
                        <i class="fas fa-user-plus text-blue-600"></i>
                    </div>
                </div>
                <div class="mt-2 text-sm text-gray-500">
                    {{ $signupsTrend->sum('count') }} this period
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">New Buyers</p>
                        <p class="text-2xl font-bold text-green-600">{{ number_format($stats['buyers']) }}</p>
                    </div>
                    <div class="p-3 bg-green-100 rounded-full">
                        <i class="fas fa-shopping-cart text-green-600"></i>
                    </div>
                </div>
                <div class="mt-2 text-sm text-gray-500">
                    {{ $stats['buyers'] > 0 ? round(($stats['buyers'] / $stats['total_users']) * 100, 1) : 0 }}% of total
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">New Vendors</p>
                        <p class="text-2xl font-bold text-purple-600">{{ number_format($stats['vendors']) }}</p>
                    </div>
                    <div class="p-3 bg-purple-100 rounded-full">
                        <i class="fas fa-store text-purple-600"></i>
                    </div>
                </div>
                <div class="mt-2 text-sm text-gray-500">
                    {{ $stats['vendors'] > 0 ? round(($stats['vendors'] / $stats['total_users']) * 100, 1) : 0 }}% of total
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Verified Users</p>
                        <p class="text-2xl font-bold text-yellow-600">{{ number_format($stats['verified_users']) }}</p>
                    </div>
                    <div class="p-3 bg-yellow-100 rounded-full">
                        <i class="fas fa-user-check text-yellow-600"></i>
                    </div>
                </div>
                <div class="mt-2 text-sm text-gray-500">
                    {{ $stats['total_users'] > 0 ? round(($stats['verified_users'] / $stats['total_users']) * 100, 1) : 0 }}% verified
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Signups Trend Chart -->
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Daily Signups Trend</h3>
                <div class="h-64">
                    <canvas id="signupsChart"></canvas>
                </div>
            </div>

            <!-- User Type Distribution -->
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">User Type Distribution</h3>
                <div class="h-64">
                    <canvas id="userTypeChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Users Table -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">User Registrations</h3>
                        <p class="text-sm text-gray-600 mt-1">Showing {{ $users->count() }} of {{ $users->total() }} users</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="text" placeholder="Search users..." 
                               class="px-3 py-2 border border-gray-300 rounded-md text-sm w-64">
                    </div>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                User
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Role
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Registration Date
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Last Active
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($users as $user)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center">
                                        @if($user->profile_picture)
                                            <img src="{{ asset('storage/' . $user->profile_picture) }}" 
                                                 class="h-10 w-10 rounded-full object-cover">
                                        @else
                                            <i class="fas fa-user text-blue-600"></i>
                                        @endif
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $user->name }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $user->email }}
                                        </div>
                                        @if($user->phone)
                                        <div class="text-xs text-gray-400">
                                            {{ $user->phone }}
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $roleColors = [
                                        'buyer' => 'bg-green-100 text-green-800',
                                        'vendor_local' => 'bg-blue-100 text-blue-800',
                                        'vendor_international' => 'bg-purple-100 text-purple-800',
                                        'admin' => 'bg-red-100 text-red-800',
                                        'logistics' => 'bg-yellow-100 text-yellow-800',
                                        'finance' => 'bg-indigo-100 text-indigo-800',
                                        'ceo' => 'bg-pink-100 text-pink-800',
                                    ];
                                    $roleLabels = [
                                        'buyer' => 'Buyer',
                                        'vendor_local' => 'Local Vendor',
                                        'vendor_international' => 'Intl Vendor',
                                        'admin' => 'Admin',
                                        'logistics' => 'Logistics',
                                        'finance' => 'Finance',
                                        'ceo' => 'CEO',
                                    ];
                                @endphp
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $roleColors[$user->role] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ $roleLabels[$user->role] ?? ucfirst($user->role) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $user->created_at->format('M d, Y') }}
                                <div class="text-xs text-gray-400">
                                    {{ $user->created_at->diffForHumans() }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($user->is_active)
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i> Active
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">
                                        <i class="fas fa-times-circle mr-1"></i> Inactive
                                    </span>
                                @endif
                                @if($user->email_verified_at)
                                    <div class="mt-1 text-xs text-blue-600">
                                        <i class="fas fa-check"></i> Email Verified
                                    </div>
                                @else
                                    <div class="mt-1 text-xs text-yellow-600">
                                        <i class="fas fa-exclamation-triangle"></i> Email Unverified
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @php
                                    $lastActivity = $user->last_login_at ?? $user->created_at;
                                @endphp
                                {{ $lastActivity->format('M d, Y') }}
                                <div class="text-xs text-gray-400">
                                    {{ $lastActivity->diffForHumans() }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('admin.users.edit', $user) }}" 
                                   class="text-blue-600 hover:text-blue-900 mr-3">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="{{ route('admin.users.show', $user) }}" 
                                   class="text-green-600 hover:text-green-900">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @if($users->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $users->links() }}
            </div>
            @endif
        </div>

        <!-- User Activity Metrics -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">User Engagement Metrics</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="text-3xl font-bold text-blue-600 mb-2">
                        {{ round($stats['total_users'] / max(30, 1)) }}
                    </div>
                    <p class="text-sm text-gray-600">Avg Daily Signups</p>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-green-600 mb-2">
                        {{ $stats['total_users'] > 0 ? round(($stats['active_users'] / $stats['total_users']) * 100, 1) : 0 }}%
                    </div>
                    <p class="text-sm text-gray-600">Active User Rate</p>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-purple-600 mb-2">
                        {{ $stats['total_users'] > 0 ? round(($stats['verified_users'] / $stats['total_users']) * 100, 1) : 0 }}%
                    </div>
                    <p class="text-sm text-gray-600">Verification Rate</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Signups Trend Chart
    const signupsCtx = document.getElementById('signupsChart').getContext('2d');
    const signupsData = {
        labels: @json($signupsTrend->pluck('date')),
        datasets: [{
            label: 'Daily Signups',
            data: @json($signupsTrend->pluck('count')),
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4
        }]
    };

    new Chart(signupsCtx, {
        type: 'line',
        data: signupsData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
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

    // User Type Chart
    const userTypeCtx = document.getElementById('userTypeChart').getContext('2d');
    new Chart(userTypeCtx, {
        type: 'pie',
        data: {
            labels: ['Buyers', 'Vendors', 'Other'],
            datasets: [{
                data: [
                    {{ $stats['buyers'] }},
                    {{ $stats['vendors'] }},
                    {{ $stats['total_users'] - $stats['buyers'] - $stats['vendors'] }}
                ],
                backgroundColor: [
                    '#10b981',
                    '#8b5cf6',
                    '#f59e0b'
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
                            const total = {{ $stats['total_users'] }};
                            const value = context.raw;
                            const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                            return `${context.label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
</script>
@endpush
@endsection