@extends('layouts.vendor')

@section('title', 'Promotions - Vendor Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Promotion Management</h1>
            <p class="text-gray-600">Boost your product visibility with promotions</p>
        </div>
        <div class="flex items-center space-x-4">
            <a href="{{ route('vendor.promotions.create') }}" 
               class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-indigo-700 font-medium">
                <i class="fas fa-bullhorn mr-2"></i> Create Promotion
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-blue-500">
            <p class="text-sm text-gray-600">Total Promotions</p>
            <p class="text-2xl font-bold">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-green-500">
            <p class="text-sm text-gray-600">Active Now</p>
            <p class="text-2xl font-bold">{{ $stats['active'] }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-yellow-500">
            <p class="text-sm text-gray-600">Scheduled</p>
            <p class="text-2xl font-bold">{{ $stats['pending'] }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-purple-500">
            <p class="text-sm text-gray-600">Budget Spent</p>
            <p class="text-2xl font-bold">${{ number_format($promotions->sum('fee'), 2) }}</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="GET" action="{{ route('vendor.promotions.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Status Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full border border-gray-300 rounded-lg p-2">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Scheduled</option>
                    <option value="expired" {{ request('status') == 'expelled' ? 'selected' : '' }}>Expired</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            <!-- Type Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                <select name="type" class="w-full border border-gray-300 rounded-lg p-2">
                    <option value="">All Types</option>
                    @foreach($promotionTypes as $type)
                    <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>
                        {{ ucfirst(str_replace('_', ' ', $type)) }}
                    </option>
                    @endforeach
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="md:col-span-3 flex justify-end space-x-3 mt-2">
                <button type="reset" onclick="window.location='{{ route('vendor.promotions.index') }}'" 
                        class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Clear Filters
                </button>
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-indigo-700">
                    <i class="fas fa-filter mr-2"></i> Apply Filters
                </button>
            </div>
        </form>
    </div>

    <!-- Promotions Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Promotion</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dates</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cost</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($promotions as $promotion)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-medium text-gray-900">{{ $promotion->title }}</div>
                            <div class="text-sm text-gray-500 line-clamp-1">{{ $promotion->description }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                @if($promotion->listing->images->first())
                                <div class="flex-shrink-0 h-10 w-10 mr-3">
                                    <img src="{{ asset('storage/' . $promotion->listing->images->first()->path) }}" 
                                         alt="{{ $promotion->listing->title }}" 
                                         class="h-10 w-10 object-cover rounded">
                                </div>
                                @endif
                                <div>
                                    <div class="font-medium text-gray-900">{{ $promotion->listing->title }}</div>
                                    <div class="text-sm text-gray-500">${{ number_format($promotion->listing->price, 2) }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-medium rounded-full 
                                @if($promotion->type == 'featured') bg-blue-100 text-blue-800
                                @elseif($promotion->type == 'spotlight') bg-purple-100 text-purple-800
                                @elseif($promotion->type == 'discount') bg-green-100 text-green-800
                                @else bg-yellow-100 text-yellow-800 @endif">
                                {{ ucfirst(str_replace('_', ' ', $promotion->type)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <div>{{ $promotion->starts_at->format('M d, Y') }}</div>
                            <div>to {{ $promotion->ends_at->format('M d, Y') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            ${{ number_format($promotion->fee, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $status = $promotion->status;
                                $statusColors = [
                                    'active' => 'bg-green-100 text-green-800',
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'expired' => 'bg-gray-100 text-gray-800',
                                    'cancelled' => 'bg-red-100 text-red-800',
                                ];
                            @endphp
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusColors[$status] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst($status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <a href="{{ route('vendor.promotions.show', $promotion) }}" 
                                   class="text-indigo-600 hover:text-indigo-900">
                                    <i class="fas fa-eye mr-1"></i> View
                                </a>
                                @if($promotion->isActive() || $promotion->status == 'pending')
                                <form action="{{ route('vendor.promotions.cancel', $promotion) }}" 
                                      method="POST" class="inline">
                                    @csrf
                                    <button type="submit" 
                                            onclick="return confirm('Cancel this promotion?')"
                                            class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-times mr-1"></i> Cancel
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
                                <i class="fas fa-bullhorn text-4xl mb-3"></i>
                                <p class="text-lg">No promotions yet</p>
                                <p class="text-sm mt-1">Create your first promotion to boost product visibility</p>
                                <a href="{{ route('vendor.promotions.create') }}" 
                                   class="mt-4 inline-block px-4 py-2 bg-primary text-white rounded-lg hover:bg-indigo-700">
                                    <i class="fas fa-bullhorn mr-2"></i> Create First Promotion
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($promotions->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $promotions->links() }}
        </div>
        @endif
    </div>
</div>
@endsection