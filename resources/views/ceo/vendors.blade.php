@extends('layouts.ceo')

@section('title', 'Vendors Overview')
@section('page-title', 'Vendors Overview')
@section('page-description', 'Vendor directory and vetting status')

@section('content')
<!-- Stats Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @php
        $stats = [
            ['label' => 'Total Vendors', 'value' => number_format($totalVendors), 'icon' => 'fa-store', 'color' => 'text-indigo-400', 'bg' => 'rgba(99,102,241,0.1)'],
            ['label' => 'Pending', 'value' => number_format($pendingVendors), 'icon' => 'fa-clock', 'color' => 'text-amber-400', 'bg' => 'rgba(245,158,11,0.1)'],
            ['label' => 'Approved', 'value' => number_format($approvedVendors), 'icon' => 'fa-check-circle', 'color' => 'text-green-400', 'bg' => 'rgba(34,197,94,0.1)'],
            ['label' => 'Rejected', 'value' => number_format($rejectedVendors), 'icon' => 'fa-times-circle', 'color' => 'text-red-400', 'bg' => 'rgba(239,68,68,0.1)'],
        ];
    @endphp
    @foreach($stats as $stat)
    <div class="kpi-card">
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center {{ $stat['color'] }}" style="background: {{ $stat['bg'] }};">
                <i class="fas {{ $stat['icon'] }}"></i>
            </div>
        </div>
        <div class="text-xl font-bold text-white tabular-nums">{{ $stat['value'] }}</div>
        <div class="text-xs text-dark-400 mt-1">{{ $stat['label'] }}</div>
    </div>
    @endforeach
</div>

<!-- Search & Filters -->
<div class="chart-card mb-6">
    <form method="GET" action="{{ route('ceo.vendors') }}" class="flex flex-wrap items-end gap-3">
        <div class="flex-1 min-w-[200px]">
            <label class="block text-xs text-dark-400 mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Business name, owner name, or email..."
                   class="w-full px-3 py-2 rounded-lg text-sm text-white placeholder-slate-500 border border-slate-700 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none"
                   style="background: rgba(15,23,42,0.6);">
        </div>
        <div class="min-w-[150px]">
            <label class="block text-xs text-dark-400 mb-1">Vetting Status</label>
            <select name="status" class="w-full px-3 py-2 rounded-lg text-sm text-white border border-slate-700 focus:border-indigo-500 outline-none" style="background: rgba(15,23,42,0.8);">
                <option value="">All</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                <option value="manual_review" {{ request('status') === 'manual_review' ? 'selected' : '' }}>Manual Review</option>
            </select>
        </div>
        <div class="min-w-[150px]">
            <label class="block text-xs text-dark-400 mb-1">Vendor Type</label>
            <select name="type" class="w-full px-3 py-2 rounded-lg text-sm text-white border border-slate-700 focus:border-indigo-500 outline-none" style="background: rgba(15,23,42,0.8);">
                <option value="">All Types</option>
                <option value="local_retail" {{ request('type') === 'local_retail' ? 'selected' : '' }}>Local Retail</option>
                <option value="china_supplier" {{ request('type') === 'china_supplier' ? 'selected' : '' }}>China Supplier</option>
                <option value="dropship" {{ request('type') === 'dropship' ? 'selected' : '' }}>Dropship</option>
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="px-4 py-2 rounded-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 transition-colors">
                <i class="fas fa-search mr-1"></i> Filter
            </button>
            <a href="{{ route('ceo.vendors') }}" class="px-4 py-2 rounded-lg text-sm font-medium text-slate-300 hover:text-white border border-slate-700 hover:border-slate-600 transition-colors">
                Clear
            </a>
        </div>
    </form>
</div>

<!-- Vendors Table -->
<div class="chart-card">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-semibold text-slate-300"><i class="fas fa-list mr-1"></i> Vendors ({{ $vendors->total() }})</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-700/50">
                    <th class="text-left py-3 px-3 text-xs font-semibold text-slate-500 uppercase">Business</th>
                    <th class="text-left py-3 px-3 text-xs font-semibold text-slate-500 uppercase">Owner</th>
                    <th class="text-left py-3 px-3 text-xs font-semibold text-slate-500 uppercase">Type</th>
                    <th class="text-left py-3 px-3 text-xs font-semibold text-slate-500 uppercase">Status</th>
                    <th class="text-left py-3 px-3 text-xs font-semibold text-slate-500 uppercase">Country</th>
                    <th class="text-left py-3 px-3 text-xs font-semibold text-slate-500 uppercase">Joined</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vendors as $vendor)
                <tr class="border-b border-slate-800/50 hover:bg-slate-800/30 transition-colors">
                    <td class="py-3 px-3">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold text-white" style="background: linear-gradient(135deg, #8b5cf6, #6366f1);">
                                {{ strtoupper(substr($vendor->business_name ?? '?', 0, 1)) }}
                            </div>
                            <span class="text-white font-medium">{{ $vendor->business_name ?? 'N/A' }}</span>
                        </div>
                    </td>
                    <td class="py-3 px-3">
                        <div class="text-slate-300">{{ $vendor->owner_name }}</div>
                        <div class="text-xs text-slate-500">{{ $vendor->owner_email ?? '—' }}</div>
                    </td>
                    <td class="py-3 px-3">
                        @php
                            $typeBadges = [
                                'local_retail' => 'bg-cyan-500/15 text-cyan-400',
                                'china_supplier' => 'bg-amber-500/15 text-amber-400',
                                'dropship' => 'bg-violet-500/15 text-violet-400',
                            ];
                            $typeBadge = $typeBadges[$vendor->vendor_type] ?? 'bg-slate-500/15 text-slate-400';
                        @endphp
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $typeBadge }}">
                            {{ ucfirst(str_replace('_', ' ', $vendor->vendor_type)) }}
                        </span>
                    </td>
                    <td class="py-3 px-3">
                        @php
                            $statusStyles = [
                                'pending' => 'bg-amber-500/15 text-amber-400',
                                'approved' => 'bg-green-500/15 text-green-400',
                                'rejected' => 'bg-red-500/15 text-red-400',
                                'manual_review' => 'bg-blue-500/15 text-blue-400',
                            ];
                            $statusStyle = $statusStyles[$vendor->vetting_status] ?? 'bg-slate-500/15 text-slate-400';
                        @endphp
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusStyle }}">
                            {{ ucfirst(str_replace('_', ' ', $vendor->vetting_status)) }}
                        </span>
                    </td>
                    <td class="py-3 px-3 text-slate-400">{{ $vendor->country ?? '—' }}</td>
                    <td class="py-3 px-3 text-slate-500 text-xs">{{ \Carbon\Carbon::parse($vendor->created_at)->format('M d, Y') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-8 text-center text-slate-500">No vendors found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($vendors->hasPages())
    <div class="mt-4 flex justify-center">
        {{ $vendors->links() }}
    </div>
    @endif
</div>
@endsection
