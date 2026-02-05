@extends('layouts.admin')

@section('title', 'Vendor Subscriptions - ' . config('app.name'))
@section('page-title', 'Vendor Subscriptions')
@section('page-description', 'Manage all vendor subscriptions')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Vendor Subscriptions</h1>
            <p class="text-gray-600">Manage all vendor subscription plans and billing</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.subscriptions.plans.index') }}" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition">
                <i class="fas fa-cog mr-2"></i>Manage Plans
            </a>
            <a href="{{ route('admin.subscriptions.revenue') }}" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                <i class="fas fa-chart-line mr-2"></i>Revenue
            </a>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-blue-500">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg mr-3">
                    <i class="fas fa-users text-blue-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Total</p>
                    <p class="text-2xl font-bold">{{ $stats['total'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-green-500">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 rounded-lg mr-3">
                    <i class="fas fa-check-circle text-green-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Active</p>
                    <p class="text-2xl font-bold">{{ $stats['active'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-yellow-500">
            <div class="flex items-center">
                <div class="p-2 bg-yellow-100 rounded-lg mr-3">
                    <i class="fas fa-clock text-yellow-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Pending</p>
                    <p class="text-2xl font-bold">{{ $stats['pending'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-gray-500">
            <div class="flex items-center">
                <div class="p-2 bg-gray-100 rounded-lg mr-3">
                    <i class="fas fa-hourglass-end text-gray-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Expired</p>
                    <p class="text-2xl font-bold">{{ $stats['expired'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-red-500">
            <div class="flex items-center">
                <div class="p-2 bg-red-100 rounded-lg mr-3">
                    <i class="fas fa-times-circle text-red-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Cancelled</p>
                    <p class="text-2xl font-bold">{{ $stats['cancelled'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <form method="GET" class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="relative flex-1">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search by vendor name or email..."
                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
            </div>

            <div class="flex space-x-2">
                <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>

                <select name="plan_id" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="">All Plans</option>
                    @foreach($plans as $plan)
                        <option value="{{ $plan->id }}" {{ request('plan_id') == $plan->id ? 'selected' : '' }}>{{ $plan->name }}</option>
                    @endforeach
                </select>

                <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                    Filter
                </button>

                <a href="{{ route('admin.subscriptions.export') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                    <i class="fas fa-download mr-1"></i>Export
                </a>
            </div>
        </form>
    </div>

    <!-- Subscriptions Table -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vendor</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expires</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Auto Renew</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($subscriptions as $subscription)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                                <div class="h-10 w-10 rounded-full bg-primary-100 flex items-center justify-center">
                                    <span class="text-primary-600 font-medium">{{ substr($subscription->vendorProfile?->business_name ?? 'V', 0, 1) }}</span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">{{ $subscription->vendorProfile?->business_name ?? 'N/A' }}</div>
                                <div class="text-sm text-gray-500">{{ $subscription->vendorProfile?->user?->email ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($subscription->plan)
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                @if($subscription->plan->slug == 'gold') bg-yellow-100 text-yellow-800
                                @elseif($subscription->plan->slug == 'silver') bg-gray-100 text-gray-800
                                @elseif($subscription->plan->slug == 'bronze') bg-orange-100 text-orange-800
                                @else bg-blue-100 text-blue-800
                                @endif">
                                {{ $subscription->plan->name }}
                            </span>
                            <div class="text-xs text-gray-500 mt-1">{{ $subscription->plan->boost_multiplier }}x boost</div>
                        @else
                            <span class="text-gray-400">N/A</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                            @if($subscription->status == 'active') bg-green-100 text-green-800
                            @elseif($subscription->status == 'pending') bg-yellow-100 text-yellow-800
                            @elseif($subscription->status == 'expired') bg-gray-100 text-gray-800
                            @else bg-red-100 text-red-800
                            @endif">
                            {{ ucfirst($subscription->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        @if($subscription->expires_at)
                            {{ $subscription->expires_at->format('M d, Y') }}
                            @if($subscription->status == 'active')
                                <div class="text-xs {{ $subscription->expires_at->diffInDays(now()) <= 7 ? 'text-red-500' : 'text-gray-400' }}">
                                    {{ $subscription->expires_at->diffForHumans() }}
                                </div>
                            @endif
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        @if($subscription->auto_renew)
                            <span class="text-green-600"><i class="fas fa-check"></i> Yes</span>
                        @else
                            <span class="text-gray-400"><i class="fas fa-times"></i> No</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2">
                            <a href="{{ route('admin.subscriptions.show', $subscription->id) }}"
                               class="text-primary-600 hover:text-primary-900" title="View Details">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if($subscription->status == 'active')
                                <button onclick="openExtendModal({{ $subscription->id }})"
                                        class="text-green-600 hover:text-green-900" title="Extend">
                                    <i class="fas fa-plus-circle"></i>
                                </button>
                                <form action="{{ route('admin.subscriptions.cancel', $subscription->id) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Cancel this subscription?')">
                                    @csrf
                                    <button type="submit" class="text-red-600 hover:text-red-900" title="Cancel">
                                        <i class="fas fa-times-circle"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                        <i class="fas fa-inbox text-4xl mb-4 text-gray-300"></i>
                        <p>No subscriptions found</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($subscriptions->hasPages())
        <div class="px-6 py-4 border-t">
            {{ $subscriptions->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Extend Modal -->
<div id="extendModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-96">
        <h3 class="text-lg font-semibold mb-4">Extend Subscription</h3>
        <form id="extendForm" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Days to extend</label>
                <input type="number" name="days" min="1" max="365" value="30"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="closeExtendModal()"
                        class="px-4 py-2 text-gray-600 hover:text-gray-800">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">Extend</button>
            </div>
        </form>
    </div>
</div>

<script>
function openExtendModal(subscriptionId) {
    document.getElementById('extendForm').action = '/admin/subscriptions/' + subscriptionId + '/extend';
    document.getElementById('extendModal').classList.remove('hidden');
    document.getElementById('extendModal').classList.add('flex');
}

function closeExtendModal() {
    document.getElementById('extendModal').classList.add('hidden');
    document.getElementById('extendModal').classList.remove('flex');
}
</script>
@endsection
