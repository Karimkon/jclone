@extends('layouts.admin')

@section('title', 'Newsletter Subscribers - Admin Dashboard')
@section('page-title', 'Newsletter Subscribers')

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Subscribers</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-users text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Active</p>
                    <p class="text-2xl font-bold text-green-600">{{ $stats['subscribed'] }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Unsubscribed</p>
                    <p class="text-2xl font-bold text-red-600">{{ $stats['unsubscribed'] }}</p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-user-minus text-red-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Actions -->
    <div class="bg-white rounded-xl shadow-sm p-5">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <form action="{{ route('admin.newsletters.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
                <div>
                    <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        <option value="">All Statuses</option>
                        <option value="subscribed" {{ request('status') == 'subscribed' ? 'selected' : '' }}>Subscribed</option>
                        <option value="unsubscribed" {{ request('status') == 'unsubscribed' ? 'selected' : '' }}>Unsubscribed</option>
                    </select>
                </div>
                <div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by email..."
                           class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                </div>
                <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-lg text-sm hover:bg-gray-700 transition">
                    <i class="fas fa-search mr-1"></i> Filter
                </button>
                @if(request()->hasAny(['status', 'search']))
                    <a href="{{ route('admin.newsletters.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200 transition">
                        Clear
                    </a>
                @endif
            </form>

            <a href="{{ route('admin.newsletters.export') }}" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700 transition inline-flex items-center gap-2">
                <i class="fas fa-file-csv"></i> Export CSV
            </a>
        </div>
    </div>

    <!-- Bulk Actions Form -->
    <form action="{{ route('admin.newsletters.bulk-action') }}" method="POST" id="bulkForm">
        @csrf

        <!-- Bulk Action Bar -->
        <div id="bulkActionBar" class="bg-indigo-50 rounded-xl p-4 hidden">
            <div class="flex items-center justify-between">
                <span class="text-sm text-indigo-700">
                    <span id="selectedCount">0</span> subscriber(s) selected
                </span>
                <div class="flex gap-2">
                    <button type="submit" name="action" value="unsubscribe"
                            class="px-3 py-1.5 bg-yellow-500 text-white rounded-lg text-sm hover:bg-yellow-600 transition"
                            onclick="return confirm('Unsubscribe selected subscribers?')">
                        <i class="fas fa-user-minus mr-1"></i> Unsubscribe
                    </button>
                    <button type="submit" name="action" value="delete"
                            class="px-3 py-1.5 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700 transition"
                            onclick="return confirm('Delete selected subscribers? This cannot be undone.')">
                        <i class="fas fa-trash mr-1"></i> Delete
                    </button>
                </div>
            </div>
        </div>

        <!-- Subscribers Table -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden mt-4">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 w-10">
                                <input type="checkbox" id="selectAll" class="rounded text-indigo-600 focus:ring-indigo-500"
                                       onchange="toggleSelectAll()">
                            </th>
                            <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase">Email</th>
                            <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase">Subscribed At</th>
                            <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase">IP Address</th>
                            <th class="text-right px-6 py-3 text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($subscribers as $subscriber)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3">
                                <input type="checkbox" name="ids[]" value="{{ $subscriber->id }}"
                                       class="subscriber-checkbox rounded text-indigo-600 focus:ring-indigo-500"
                                       onchange="updateBulkBar()">
                            </td>
                            <td class="px-6 py-3 text-sm font-medium text-gray-900">{{ $subscriber->email }}</td>
                            <td class="px-6 py-3">
                                @if($subscriber->status === 'subscribed')
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Subscribed</span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Unsubscribed</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-sm text-gray-500">
                                {{ $subscriber->subscribed_at?->format('M d, Y H:i') ?? $subscriber->created_at->format('M d, Y H:i') }}
                            </td>
                            <td class="px-6 py-3 text-sm text-gray-500">{{ $subscriber->ip_address ?? '-' }}</td>
                            <td class="px-6 py-3 text-right">
                                <form action="{{ route('admin.newsletters.destroy', $subscriber) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Delete this subscriber?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:text-red-600" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <i class="fas fa-newspaper text-4xl text-gray-300 mb-3"></i>
                                <p>No subscribers found.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($subscribers->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $subscribers->links() }}
            </div>
            @endif
        </div>
    </form>
</div>

<script>
    function toggleSelectAll() {
        const checked = document.getElementById('selectAll').checked;
        document.querySelectorAll('.subscriber-checkbox').forEach(cb => cb.checked = checked);
        updateBulkBar();
    }

    function updateBulkBar() {
        const checked = document.querySelectorAll('.subscriber-checkbox:checked').length;
        document.getElementById('selectedCount').textContent = checked;
        document.getElementById('bulkActionBar').classList.toggle('hidden', checked === 0);
    }
</script>
@endsection
