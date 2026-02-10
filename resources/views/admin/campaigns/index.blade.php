@extends('layouts.admin')

@section('title', 'Campaigns - Admin Dashboard')
@section('page-title', 'Campaigns')

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Campaigns</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-bullhorn text-blue-600 text-xl"></i>
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
                    <p class="text-sm text-gray-500">Scheduled</p>
                    <p class="text-2xl font-bold text-orange-600">{{ $stats['scheduled'] }}</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-clock text-orange-600 text-xl"></i>
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
            <form action="{{ route('admin.campaigns.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
                <div>
                    <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        <option value="">All Statuses</option>
                        @foreach(['draft', 'scheduled', 'sending', 'sent', 'failed', 'cancelled'] as $s)
                            <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select name="type" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        <option value="">All Types</option>
                        <option value="email" {{ request('type') == 'email' ? 'selected' : '' }}>Email</option>
                        <option value="sms" {{ request('type') == 'sms' ? 'selected' : '' }}>SMS</option>
                    </select>
                </div>
                <div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search campaigns..."
                           class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                </div>
                <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-lg text-sm hover:bg-gray-700 transition">
                    <i class="fas fa-search mr-1"></i> Filter
                </button>
                @if(request()->hasAny(['status', 'type', 'search']))
                    <a href="{{ route('admin.campaigns.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200 transition">
                        Clear
                    </a>
                @endif
            </form>

            <a href="{{ route('admin.campaigns.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 transition inline-flex items-center gap-2">
                <i class="fas fa-plus"></i> New Campaign
            </a>
        </div>
    </div>

    <!-- Campaigns Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase">Campaign</th>
                        <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase">Audience</th>
                        <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase">Recipients</th>
                        <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase">Created</th>
                        <th class="text-right px-6 py-3 text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($campaigns as $campaign)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.campaigns.show', $campaign) }}" class="font-medium text-gray-900 hover:text-indigo-600">
                                {{ $campaign->title }}
                            </a>
                            @if($campaign->subject)
                                <p class="text-xs text-gray-500 mt-1">{{ Str::limit($campaign->subject, 50) }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($campaign->type === 'email')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <i class="fas fa-envelope text-xs"></i> Email
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                    <i class="fas fa-sms text-xs"></i> SMS
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ ucfirst($campaign->audience) }}</td>
                        <td class="px-6 py-4">
                            @php
                                $statusColors = [
                                    'draft' => 'bg-gray-100 text-gray-800',
                                    'scheduled' => 'bg-yellow-100 text-yellow-800',
                                    'sending' => 'bg-blue-100 text-blue-800',
                                    'sent' => 'bg-green-100 text-green-800',
                                    'failed' => 'bg-red-100 text-red-800',
                                    'cancelled' => 'bg-gray-100 text-gray-600',
                                ];
                            @endphp
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$campaign->status] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst($campaign->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            @if($campaign->total_recipients > 0)
                                {{ $campaign->sent_count }}/{{ $campaign->total_recipients }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $campaign->created_at->format('M d, Y') }}
                            @if($campaign->creator)
                                <br><span class="text-xs">by {{ $campaign->creator->name }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.campaigns.show', $campaign) }}" class="text-gray-400 hover:text-gray-600" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if(in_array($campaign->status, ['draft', 'scheduled']))
                                    <a href="{{ route('admin.campaigns.edit', $campaign) }}" class="text-gray-400 hover:text-indigo-600" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif
                                <form action="{{ route('admin.campaigns.duplicate', $campaign) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-gray-400 hover:text-green-600" title="Duplicate">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-bullhorn text-4xl text-gray-300 mb-3"></i>
                            <p>No campaigns found.</p>
                            <a href="{{ route('admin.campaigns.create') }}" class="text-indigo-600 hover:text-indigo-800 mt-2 inline-block">Create your first campaign</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($campaigns->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $campaigns->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
