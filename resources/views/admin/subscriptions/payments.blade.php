@extends('layouts.admin')

@section('title', 'Subscription Payments - ' . config('app.name'))
@section('page-title', 'Subscription Payments')
@section('page-description', 'Track who paid and how much was collected')

@section('content')
<div class="container-fluid">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Subscription Payments</h1>
            <p class="text-gray-600">Full record of all subscription payments via Pesapal</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.subscriptions.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                <i class="fas fa-arrow-left mr-2"></i>Subscriptions
            </a>
            <a href="{{ route('admin.subscriptions.revenue') }}" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                <i class="fas fa-chart-line mr-2"></i>Revenue
            </a>
        </div>
    </div>

    <!-- All-time Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-5 rounded-lg shadow border-l-4 border-green-500">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Collected (All Time)</p>
            <p class="text-3xl font-bold text-green-600 mt-1">UGX {{ number_format($stats['all_time_revenue']) }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ number_format($stats['all_time_count']) }} completed payments</p>
        </div>
        <div class="bg-white p-5 rounded-lg shadow border-l-4 border-blue-500">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Completed Payments</p>
            <p class="text-3xl font-bold text-blue-600 mt-1">{{ number_format($stats['all_time_count']) }}</p>
            <p class="text-xs text-gray-400 mt-1">Successfully processed</p>
        </div>
        <div class="bg-white p-5 rounded-lg shadow border-l-4 border-yellow-500">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Pending Payments</p>
            <p class="text-3xl font-bold text-yellow-600 mt-1">{{ number_format($stats['pending_count']) }}</p>
            <p class="text-xs text-gray-400 mt-1">Awaiting confirmation</p>
        </div>
        <div class="bg-white p-5 rounded-lg shadow border-l-4 border-red-500">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Failed Payments</p>
            <p class="text-3xl font-bold text-red-600 mt-1">{{ number_format($stats['failed_count']) }}</p>
            <p class="text-xs text-gray-400 mt-1">Could not be processed</p>
        </div>
    </div>

    <!-- Advanced Filters -->
    <div class="bg-white rounded-lg shadow-sm p-5 mb-6">
        <h3 class="text-sm font-semibold text-gray-700 mb-4 uppercase tracking-wide">
            <i class="fas fa-filter mr-2 text-primary-500"></i>Advanced Filters
        </h3>
        <form method="GET" id="filterForm" class="space-y-4">
            <!-- Row 1: Search + Reference + Status -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Search Vendor</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Business name, vendor name or email..."
                               class="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary focus:border-transparent">
                        <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-xs"></i>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Merchant Reference</label>
                    <input type="text" name="reference" value="{{ request('reference') }}"
                           placeholder="SUB-..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Payment Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary">
                        <option value="">All Statuses</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="pending"   {{ request('status') == 'pending'   ? 'selected' : '' }}>Pending</option>
                        <option value="failed"    {{ request('status') == 'failed'    ? 'selected' : '' }}>Failed</option>
                        <option value="refunded"  {{ request('status') == 'refunded'  ? 'selected' : '' }}>Refunded</option>
                    </select>
                </div>
            </div>

            <!-- Row 2: Plan + Date From + Date To -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Subscription Plan</label>
                    <select name="plan_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary">
                        <option value="">All Plans</option>
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}" {{ request('plan_id') == $plan->id ? 'selected' : '' }}>
                                {{ $plan->name }} (UGX {{ number_format($plan->price) }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Date From</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Date To</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary">
                </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-wrap gap-2 items-center">
                <button type="submit" class="px-5 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 text-sm font-medium">
                    <i class="fas fa-search mr-1"></i>Apply Filters
                </button>
                <a href="{{ route('admin.subscriptions.payments') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm">
                    <i class="fas fa-times mr-1"></i>Clear
                </a>

                <!-- Quick date presets -->
                <div class="ml-auto flex gap-2 flex-wrap">
                    <button type="button" onclick="setDateRange(7)" class="px-3 py-1.5 text-xs border border-gray-300 rounded hover:bg-gray-100">Last 7 days</button>
                    <button type="button" onclick="setDateRange(30)" class="px-3 py-1.5 text-xs border border-gray-300 rounded hover:bg-gray-100">Last 30 days</button>
                    <button type="button" onclick="setDateRange(90)" class="px-3 py-1.5 text-xs border border-gray-300 rounded hover:bg-gray-100">Last 90 days</button>
                    <button type="button" onclick="setDateRange(365)" class="px-3 py-1.5 text-xs border border-gray-300 rounded hover:bg-gray-100">This year</button>
                    <a href="{{ route('admin.subscriptions.payments.export', request()->query()) }}"
                       class="px-4 py-1.5 bg-emerald-600 text-white rounded text-xs hover:bg-emerald-700 font-medium">
                        <i class="fas fa-download mr-1"></i>Export CSV
                    </a>
                </div>
            </div>
        </form>
    </div>

    @if(request()->hasAny(['search','reference','status','plan_id','date_from','date_to']))
    <div class="mb-4 flex items-center gap-2 text-sm text-gray-600">
        <i class="fas fa-filter text-primary-500"></i>
        <span>Showing <strong>{{ $payments->total() }}</strong> filtered results</span>
        @if($payments->total() > 0)
            <span class="text-green-700 font-semibold">
                — Total: UGX {{ number_format($payments->sum('amount')) }}
            </span>
        @endif
    </div>
    @endif

    <!-- Payments Table -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b bg-gray-50 flex justify-between items-center">
            <h3 class="font-semibold text-gray-800">
                Payment Records
                <span class="ml-2 text-sm font-normal text-gray-500">({{ $payments->total() }} total)</span>
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Vendor</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Plan</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Amount</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Merchant Reference</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Pesapal Tracking</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">View</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($payments as $payment)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <!-- Vendor -->
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-primary-100 flex-shrink-0 flex items-center justify-center">
                                    <span class="text-primary-600 font-bold text-xs">
                                        {{ strtoupper(substr($payment->vendorProfile?->business_name ?? 'V', 0, 1)) }}
                                    </span>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $payment->vendorProfile?->business_name ?? 'N/A' }}</p>
                                    <p class="text-xs text-gray-500">{{ $payment->vendorProfile?->user?->email ?? '' }}</p>
                                </div>
                            </div>
                        </td>

                        <!-- Plan -->
                        <td class="px-4 py-3">
                            @php $plan = $payment->vendorSubscription?->plan; @endphp
                            @if($plan)
                                <span class="px-2 py-1 text-xs font-semibold rounded-full
                                    @if($plan->slug == 'gold')   bg-yellow-100 text-yellow-800
                                    @elseif($plan->slug == 'silver') bg-gray-100 text-gray-700
                                    @elseif($plan->slug == 'bronze') bg-orange-100 text-orange-800
                                    @else bg-blue-100 text-blue-700
                                    @endif">
                                    {{ $plan->name }}
                                </span>
                            @else
                                <span class="text-gray-400 text-xs">—</span>
                            @endif
                        </td>

                        <!-- Amount -->
                        <td class="px-4 py-3 text-right">
                            <span class="text-sm font-bold {{ $payment->status == 'completed' ? 'text-green-600' : 'text-gray-600' }}">
                                UGX {{ number_format($payment->amount) }}
                            </span>
                        </td>

                        <!-- Status -->
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-1 inline-flex text-xs font-semibold rounded-full
                                @if($payment->status == 'completed') bg-green-100 text-green-800
                                @elseif($payment->status == 'pending') bg-yellow-100 text-yellow-800
                                @elseif($payment->status == 'failed') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-600
                                @endif">
                                @if($payment->status == 'completed') <i class="fas fa-check-circle mr-1"></i>
                                @elseif($payment->status == 'pending') <i class="fas fa-clock mr-1"></i>
                                @elseif($payment->status == 'failed') <i class="fas fa-times-circle mr-1"></i>
                                @else <i class="fas fa-undo mr-1"></i>
                                @endif
                                {{ ucfirst($payment->status) }}
                            </span>
                        </td>

                        <!-- Merchant Reference -->
                        <td class="px-4 py-3">
                            <code class="text-xs text-gray-700 bg-gray-100 px-2 py-0.5 rounded">
                                {{ $payment->pesapal_merchant_reference }}
                            </code>
                        </td>

                        <!-- Pesapal Tracking -->
                        <td class="px-4 py-3">
                            @if($payment->pesapal_order_tracking_id)
                                <code class="text-xs text-gray-500 bg-gray-50 px-1 py-0.5 rounded">
                                    {{ Str::limit($payment->pesapal_order_tracking_id, 20) }}
                                </code>
                            @else
                                <span class="text-gray-300 text-xs">—</span>
                            @endif
                        </td>

                        <!-- Date -->
                        <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">
                            <div>{{ $payment->created_at->format('M d, Y') }}</div>
                            <div class="text-xs text-gray-400">{{ $payment->created_at->format('H:i') }}</div>
                        </td>

                        <!-- View Subscription -->
                        <td class="px-4 py-3 text-center">
                            @if($payment->vendorSubscription)
                                <a href="{{ route('admin.subscriptions.show', $payment->vendorSubscription->id) }}"
                                   class="text-primary-600 hover:text-primary-800" title="View Subscription">
                                    <i class="fas fa-eye"></i>
                                </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-16 text-center">
                            <div class="text-gray-300 text-5xl mb-4"><i class="fas fa-receipt"></i></div>
                            <p class="text-gray-500 font-medium">No payments found</p>
                            <p class="text-gray-400 text-sm mt-1">Try adjusting your filters</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($payments->count() > 0)
                <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                    <tr>
                        <td colspan="2" class="px-4 py-3 text-sm font-semibold text-gray-700">
                            Page Total ({{ $payments->count() }} records)
                        </td>
                        <td class="px-4 py-3 text-right text-sm font-bold text-green-600">
                            UGX {{ number_format($payments->sum('amount')) }}
                        </td>
                        <td colspan="5"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        @if($payments->hasPages())
        <div class="px-6 py-4 border-t bg-white">
            {{ $payments->links() }}
        </div>
        @endif
    </div>

</div>

<script>
function setDateRange(days) {
    const today = new Date();
    const from  = new Date(today);
    from.setDate(today.getDate() - days);

    const fmt = d => d.toISOString().split('T')[0];
    document.querySelector('[name="date_from"]').value = fmt(from);
    document.querySelector('[name="date_to"]').value   = fmt(today);
    document.getElementById('filterForm').submit();
}
</script>
@endsection
