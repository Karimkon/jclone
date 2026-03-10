@extends('layouts.admin')

@section('title', 'Broadcasts - Admin Dashboard')
@section('page-title', 'Broadcasts')

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Broadcasts</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-bell text-red-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Sent</p>
                    <p class="text-2xl font-bold text-green-600">{{ $stats['sent'] }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Drafts</p>
                    <p class="text-2xl font-bold text-gray-600">{{ $stats['draft'] }}</p>
                </div>
                <div class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-file-alt text-gray-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-5">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <form action="{{ route('admin.broadcasts.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
                <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    <option value="">All Statuses</option>
                    @foreach(['draft', 'sending', 'sent', 'failed'] as $s)
                        <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
                <select name="audience" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    <option value="">All Audiences</option>
                    <option value="all" {{ request('audience') == 'all' ? 'selected' : '' }}>All Users</option>
                    <option value="buyers" {{ request('audience') == 'buyers' ? 'selected' : '' }}>Buyers</option>
                    <option value="vendors" {{ request('audience') == 'vendors' ? 'selected' : '' }}>Vendors</option>
                    <option value="specific_user" {{ request('audience') == 'specific_user' ? 'selected' : '' }}>Specific User</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-lg text-sm hover:bg-gray-700 transition">
                    <i class="fas fa-search mr-1"></i> Filter
                </button>
                @if(request()->hasAny(['status', 'audience']))
                    <a href="{{ route('admin.broadcasts.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200 transition">Clear</a>
                @endif
            </form>

            <a href="{{ route('admin.broadcasts.create') }}" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700 transition inline-flex items-center gap-2">
                <i class="fas fa-plus"></i> New Broadcast
            </a>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase">Title</th>
                        <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase">Audience</th>
                        <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase">Recipients</th>
                        <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase">Sent At</th>
                        <th class="text-right px-6 py-3 text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($broadcasts as $broadcast)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <p class="font-medium text-gray-900">{{ $broadcast->title }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ Str::limit($broadcast->body, 60) }}</p>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            @if($broadcast->audience === 'specific_user' && $broadcast->targetUser)
                                <span class="text-xs">{{ $broadcast->targetUser->name }}</span>
                            @else
                                {{ ucfirst(str_replace('_', ' ', $broadcast->audience)) }}
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $statusColors = [
                                    'draft'   => 'bg-gray-100 text-gray-800',
                                    'sending' => 'bg-blue-100 text-blue-800',
                                    'sent'    => 'bg-green-100 text-green-800',
                                    'failed'  => 'bg-red-100 text-red-800',
                                ];
                            @endphp
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$broadcast->status] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst($broadcast->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            @if($broadcast->total_recipients > 0)
                                {{ number_format($broadcast->sent_count) }}/{{ number_format($broadcast->total_recipients) }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $broadcast->sent_at ? $broadcast->sent_at->format('M d, Y H:i') : '-' }}
                            @if($broadcast->creator)
                                <br><span class="text-xs">by {{ $broadcast->creator->name }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            @if(in_array($broadcast->status, ['draft', 'sending']))
                                <form action="{{ route('admin.broadcasts.cancel', $broadcast) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Cancel this broadcast?')">
                                    @csrf
                                    <button type="submit" class="text-red-400 hover:text-red-600 text-sm" title="Cancel">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-bell text-4xl text-gray-300 mb-3"></i>
                            <p>No broadcasts yet.</p>
                            <a href="{{ route('admin.broadcasts.create') }}" class="text-red-600 hover:text-red-800 mt-2 inline-block">Send your first broadcast</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($broadcasts->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $broadcasts->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
