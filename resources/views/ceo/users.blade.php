@extends('layouts.ceo')

@section('title', 'Users Overview')
@section('page-title', 'Users Overview')
@section('page-description', 'Platform user directory and demographics')

@section('content')
<!-- Stats Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @php
        $stats = [
            ['label' => 'Total Users', 'value' => number_format($totalUsers), 'icon' => 'fa-users', 'color' => 'text-indigo-400', 'bg' => 'rgba(99,102,241,0.1)'],
            ['label' => 'Vendors', 'value' => number_format($totalVendors), 'icon' => 'fa-store', 'color' => 'text-purple-400', 'bg' => 'rgba(139,92,246,0.1)'],
            ['label' => 'Buyers', 'value' => number_format($totalBuyers), 'icon' => 'fa-shopping-bag', 'color' => 'text-cyan-400', 'bg' => 'rgba(6,182,212,0.1)'],
            ['label' => 'Staff', 'value' => number_format($totalStaff), 'icon' => 'fa-user-shield', 'color' => 'text-green-400', 'bg' => 'rgba(34,197,94,0.1)'],
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
    <form method="GET" action="{{ route('ceo.users') }}" class="flex flex-wrap items-end gap-3">
        <div class="flex-1 min-w-[200px]">
            <label class="block text-xs text-dark-400 mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, email, or phone..."
                   class="w-full px-3 py-2 rounded-lg text-sm text-white placeholder-slate-500 border border-slate-700 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none"
                   style="background: rgba(15,23,42,0.6);">
        </div>
        <div class="min-w-[150px]">
            <label class="block text-xs text-dark-400 mb-1">Role</label>
            <select name="role" class="w-full px-3 py-2 rounded-lg text-sm text-white border border-slate-700 focus:border-indigo-500 outline-none" style="background: rgba(15,23,42,0.8);">
                <option value="">All Roles</option>
                <option value="buyer" {{ request('role') === 'buyer' ? 'selected' : '' }}>Buyer</option>
                <option value="vendor_local" {{ request('role') === 'vendor_local' ? 'selected' : '' }}>Vendor (Local)</option>
                <option value="vendor_international" {{ request('role') === 'vendor_international' ? 'selected' : '' }}>Vendor (International)</option>
                <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="logistics" {{ request('role') === 'logistics' ? 'selected' : '' }}>Logistics</option>
                <option value="clearing_agent" {{ request('role') === 'clearing_agent' ? 'selected' : '' }}>Clearing Agent</option>
                <option value="finance" {{ request('role') === 'finance' ? 'selected' : '' }}>Finance</option>
                <option value="ceo" {{ request('role') === 'ceo' ? 'selected' : '' }}>CEO</option>
            </select>
        </div>
        <div class="min-w-[130px]">
            <label class="block text-xs text-dark-400 mb-1">Status</label>
            <select name="status" class="w-full px-3 py-2 rounded-lg text-sm text-white border border-slate-700 focus:border-indigo-500 outline-none" style="background: rgba(15,23,42,0.8);">
                <option value="">All</option>
                <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="px-4 py-2 rounded-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 transition-colors">
                <i class="fas fa-search mr-1"></i> Filter
            </button>
            <a href="{{ route('ceo.users') }}" class="px-4 py-2 rounded-lg text-sm font-medium text-slate-300 hover:text-white border border-slate-700 hover:border-slate-600 transition-colors">
                Clear
            </a>
        </div>
    </form>
</div>

<!-- Users Table -->
<div class="chart-card">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-semibold text-slate-300"><i class="fas fa-list mr-1"></i> Users ({{ $users->total() }})</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-700/50">
                    <th class="text-left py-3 px-3 text-xs font-semibold text-slate-500 uppercase">Name</th>
                    <th class="text-left py-3 px-3 text-xs font-semibold text-slate-500 uppercase">Email</th>
                    <th class="text-left py-3 px-3 text-xs font-semibold text-slate-500 uppercase">Role</th>
                    <th class="text-left py-3 px-3 text-xs font-semibold text-slate-500 uppercase">Status</th>
                    <th class="text-left py-3 px-3 text-xs font-semibold text-slate-500 uppercase">Joined</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr class="border-b border-slate-800/50 hover:bg-slate-800/30 transition-colors">
                    <td class="py-3 px-3">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold text-white" style="background: linear-gradient(135deg, #6366f1, #8b5cf6);">
                                {{ strtoupper(substr($user->name ?? '?', 0, 1)) }}
                            </div>
                            <span class="text-white font-medium">{{ $user->name ?? 'N/A' }}</span>
                        </div>
                    </td>
                    <td class="py-3 px-3 text-slate-400">{{ $user->email ?? '—' }}</td>
                    <td class="py-3 px-3">
                        @php
                            $roleBadges = [
                                'buyer' => 'bg-cyan-500/15 text-cyan-400',
                                'vendor_local' => 'bg-purple-500/15 text-purple-400',
                                'vendor_international' => 'bg-violet-500/15 text-violet-400',
                                'admin' => 'bg-red-500/15 text-red-400',
                                'logistics' => 'bg-amber-500/15 text-amber-400',
                                'clearing_agent' => 'bg-teal-500/15 text-teal-400',
                                'finance' => 'bg-green-500/15 text-green-400',
                                'ceo' => 'bg-indigo-500/15 text-indigo-400',
                            ];
                            $badge = $roleBadges[$user->role] ?? 'bg-slate-500/15 text-slate-400';
                        @endphp
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $badge }}">
                            {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                        </span>
                    </td>
                    <td class="py-3 px-3">
                        @if($user->is_active)
                            <span class="inline-flex items-center gap-1 text-xs text-green-400"><i class="fas fa-circle text-[6px]"></i> Active</span>
                        @else
                            <span class="inline-flex items-center gap-1 text-xs text-red-400"><i class="fas fa-circle text-[6px]"></i> Inactive</span>
                        @endif
                    </td>
                    <td class="py-3 px-3 text-slate-500 text-xs">{{ \Carbon\Carbon::parse($user->created_at)->format('M d, Y') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-8 text-center text-slate-500">No users found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($users->hasPages())
    <div class="mt-4 flex justify-center">
        {{ $users->links() }}
    </div>
    @endif
</div>
@endsection
