@extends('layouts.admin')

@section('title', 'All Vendors - ' . config('app.name'))
@section('page-title', 'All Vendors')
@section('page-description', 'Manage all registered vendors')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">All Vendors</h1>
            <p class="text-gray-600">Manage all registered vendors on the platform</p>
        </div>
        <div class="bg-indigo-50 px-4 py-2 rounded-lg">
            <span class="text-indigo-700 font-bold">{{ $stats['total'] }}</span>
            <span class="text-gray-600">total vendors</span>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-blue-500">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg mr-3">
                    <i class="fas fa-users text-blue-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Total Vendors</p>
                    <p class="text-2xl font-bold">{{ $stats['total'] }}</p>
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

        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-green-500">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 rounded-lg mr-3">
                    <i class="fas fa-check-circle text-green-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Approved</p>
                    <p class="text-2xl font-bold">{{ $stats['approved'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-red-500">
            <div class="flex items-center">
                <div class="p-2 bg-red-100 rounded-lg mr-3">
                    <i class="fas fa-times-circle text-red-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Rejected</p>
                    <p class="text-2xl font-bold">{{ $stats['rejected'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filters -->
    <form method="GET" action="{{ route('admin.vendors.index') }}" id="vendorFilterForm">
    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="relative flex-1">
                <input type="text"
                       name="search"
                       id="vendorSearchInput"
                       value="{{ request('search') }}"
                       placeholder="Search vendors by name, email, or business..."
                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
            </div>

            <div class="flex flex-wrap gap-2">
                <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>

                <select name="type" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    <option value="local_retail" {{ request('type') == 'local_retail' ? 'selected' : '' }}>Local Retailer</option>
                    <option value="china_supplier" {{ request('type') == 'china_supplier' ? 'selected' : '' }}>International Supplier</option>
                    <option value="dropship" {{ request('type') == 'dropship' ? 'selected' : '' }}>Dropshipper</option>
                </select>

                <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                    <i class="fas fa-search mr-2"></i> Search
                </button>

                @if(request('search') || request('status') || request('type'))
                <a href="{{ route('admin.vendors.index') }}" class="inline-flex items-center px-3 py-2 text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200" title="Clear filters">
                    <i class="fas fa-times mr-1"></i> Clear
                </a>
                @endif
            </div>
        </div>
    </div>
    </form>

    @if(request('search') || request('status') || request('type'))
    <div class="mb-4 text-sm text-gray-600">
        Showing {{ $vendors->total() }} result(s)
        @if(request('search')) for "<strong>{{ request('search') }}</strong>" @endif
        @if(request('status')) with status <strong>{{ ucfirst(request('status')) }}</strong> @endif
        @if(request('type')) of type <strong>{{ ucfirst(str_replace('_', ' ', request('type'))) }}</strong> @endif
    </div>
    @endif

    <!-- Vendors Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Business</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Owner</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Score</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($vendors as $vendor)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-store text-indigo-600"></i>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        <a href="{{ route('admin.vendors.show', $vendor) }}" class="hover:text-primary">
                                            {{ $vendor->business_name }}
                                        </a>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ $vendor->city }}, {{ $vendor->country }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">{{ $vendor->user->name }}</div>
                            <div class="text-sm text-gray-500">{{ $vendor->user->email }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded-full
                                @if($vendor->vendor_type == 'local_retail') bg-blue-100 text-blue-800
                                @elseif($vendor->vendor_type == 'china_supplier') bg-green-100 text-green-800
                                @else bg-purple-100 text-purple-800 @endif">
                                {{ str_replace('_', ' ', ucfirst($vendor->vendor_type)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $statusColors = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'approved' => 'bg-green-100 text-green-800',
                                    'rejected' => 'bg-red-100 text-red-800',
                                ];
                            @endphp
                            <span class="px-2 py-1 text-xs rounded-full {{ $statusColors[$vendor->vetting_status] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst(str_replace('_', ' ', $vendor->vetting_status)) }}
                            </span>
                            <div class="text-xs text-gray-500 mt-1">
                                @if($vendor->user->is_active)
                                    <span class="text-green-600">Active</span>
                                @else
                                    <span class="text-red-600">Inactive</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $latestScore = $vendor->scores()->latest()->first();
                                $score = $latestScore->score ?? 0;
                            @endphp
                            <div class="flex items-center">
                                <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                    <div class="bg-primary h-2 rounded-full" style="width: {{ $score }}%"></div>
                                </div>
                                <span class="text-sm font-medium text-gray-900">{{ $score }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $vendor->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 text-sm font-medium">
                            <div class="flex space-x-2">
                                <a href="{{ route('admin.vendors.show', $vendor) }}"
                                   class="text-indigo-600 hover:text-indigo-900"
                                   title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if(auth()->user()->role !== 'support')
                                <form action="{{ route('admin.vendors.toggleStatus', $vendor->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit"
                                            class="text-gray-600 hover:text-gray-900"
                                            title="{{ $vendor->user->is_active ? 'Deactivate' : 'Activate' }}">
                                        <i class="fas fa-power-off"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="text-gray-400">
                                <i class="fas fa-store-slash text-4xl mb-3"></i>
                                <p class="text-lg">No vendors found</p>
                                @if(request('search') || request('status') || request('type'))
                                    <p class="text-sm mt-1">Try adjusting your search or filters</p>
                                    <a href="{{ route('admin.vendors.index') }}" class="text-primary hover:underline mt-2 inline-block">Clear all filters</a>
                                @else
                                    <p class="text-sm mt-1">No vendors have registered yet</p>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($vendors->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $vendors->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        let searchTimer;
        const searchInput = document.getElementById('vendorSearchInput');
        const filterForm = document.getElementById('vendorFilterForm');

        searchInput.addEventListener('keyup', function(e) {
            clearTimeout(searchTimer);
            if (e.key === 'Enter') {
                filterForm.submit();
                return;
            }
            searchTimer = setTimeout(function() {
                filterForm.submit();
            }, 500);
        });
    });
</script>
@endsection
