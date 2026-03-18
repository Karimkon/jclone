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
        <div class="flex gap-2 flex-wrap">
            <a href="{{ route('admin.subscriptions.payments') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                <i class="fas fa-money-bill-wave mr-2"></i>Payments
            </a>
            <a href="{{ route('admin.subscriptions.revenue') }}" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                <i class="fas fa-chart-line mr-2"></i>Revenue
            </a>
            <a href="{{ route('admin.subscriptions.plans.index') }}" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition">
                <i class="fas fa-cog mr-2"></i>Plans
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

    <!-- Advanced Filters -->
    <div class="bg-white rounded-lg shadow-sm p-5 mb-6">
        <h3 class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-4">
            <i class="fas fa-filter mr-2 text-primary-500"></i>Filters
        </h3>
        <form method="GET" id="subFilterForm" class="space-y-4">
            <!-- Row 1 -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Search</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Vendor business name or email..."
                               class="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary focus:border-transparent">
                        <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-xs"></i>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary">
                        <option value="">All Statuses</option>
                        <option value="active"    {{ request('status') == 'active'    ? 'selected' : '' }}>Active</option>
                        <option value="pending"   {{ request('status') == 'pending'   ? 'selected' : '' }}>Pending</option>
                        <option value="expired"   {{ request('status') == 'expired'   ? 'selected' : '' }}>Expired</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Plan</label>
                    <select name="plan_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary">
                        <option value="">All Plans</option>
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}" {{ request('plan_id') == $plan->id ? 'selected' : '' }}>{{ $plan->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <!-- Row 2 -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Subscribed From</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Subscribed To</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Auto-Renew</label>
                    <select name="auto_renew" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary">
                        <option value="">Any</option>
                        <option value="1" {{ request('auto_renew') === '1' ? 'selected' : '' }}>Enabled</option>
                        <option value="0" {{ request('auto_renew') === '0' ? 'selected' : '' }}>Disabled</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Expiring Within</label>
                    <select name="expiring" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary">
                        <option value="">Any</option>
                        <option value="3"  {{ request('expiring') == '3'  ? 'selected' : '' }}>3 days</option>
                        <option value="7"  {{ request('expiring') == '7'  ? 'selected' : '' }}>7 days</option>
                        <option value="14" {{ request('expiring') == '14' ? 'selected' : '' }}>14 days</option>
                        <option value="30" {{ request('expiring') == '30' ? 'selected' : '' }}>30 days</option>
                    </select>
                </div>
            </div>
            <!-- Actions -->
            <div class="flex gap-2 flex-wrap items-center">
                <button type="submit" class="px-5 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 text-sm font-medium">
                    <i class="fas fa-search mr-1"></i>Apply Filters
                </button>
                <a href="{{ route('admin.subscriptions.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm">
                    <i class="fas fa-times mr-1"></i>Clear
                </a>
                <div class="ml-auto">
                    <a href="{{ route('admin.subscriptions.export', request()->query()) }}"
                       class="px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-800 text-sm">
                        <i class="fas fa-download mr-1"></i>Export CSV
                    </a>
                </div>
            </div>
        </form>
    </div>

    @if(request()->hasAny(['search','status','plan_id','date_from','date_to','auto_renew','expiring']))
    <div class="mb-4 text-sm text-gray-600">
        <i class="fas fa-filter text-primary-500 mr-1"></i>
        Showing <strong>{{ $subscriptions->total() }}</strong> filtered results
    </div>
    @endif

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
            {{ $subscriptions->appends(request()->query())->links() }}
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
