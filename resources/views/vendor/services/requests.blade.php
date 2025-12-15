@extends('layouts.vendor')

@section('title', 'Service Requests - Vendor Dashboard')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Service Requests</h1>
            <p class="text-gray-600">Manage customer requests for your services</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('vendor.services.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                <i class="fas fa-list mr-2"></i> My Services
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Pending</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $statusCounts['pending'] }}</p>
                </div>
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clock text-blue-500"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Quoted</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $statusCounts['quoted'] }}</p>
                </div>
                <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-file-invoice-dollar text-yellow-500"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">In Progress</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $statusCounts['in_progress'] }}</p>
                </div>
                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-tasks text-purple-500"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Completed</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $statusCounts['completed'] }}</p>
                </div>
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-500"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <span class="text-sm font-medium text-gray-700">Filter by:</span>
                <a href="{{ route('vendor.services.requests') }}" 
                   class="px-3 py-1 rounded-lg {{ !request('status') ? 'bg-blue-100 text-blue-700' : 'text-gray-600 hover:bg-gray-100' }}">
                    All
                </a>
                <a href="{{ route('vendor.services.requests', ['status' => 'pending']) }}" 
                   class="px-3 py-1 rounded-lg {{ request('status') == 'pending' ? 'bg-blue-100 text-blue-700' : 'text-gray-600 hover:bg-gray-100' }}">
                    Pending
                </a>
                <a href="{{ route('vendor.services.requests', ['status' => 'quoted']) }}" 
                   class="px-3 py-1 rounded-lg {{ request('status') == 'quoted' ? 'bg-blue-100 text-blue-700' : 'text-gray-600 hover:bg-gray-100' }}">
                    Quoted
                </a>
                <a href="{{ route('vendor.services.requests', ['status' => 'in_progress']) }}" 
                   class="px-3 py-1 rounded-lg {{ request('status') == 'in_progress' ? 'bg-blue-100 text-blue-700' : 'text-gray-600 hover:bg-gray-100' }}">
                    In Progress
                </a>
                <a href="{{ route('vendor.services.requests', ['status' => 'completed']) }}" 
                   class="px-3 py-1 rounded-lg {{ request('status') == 'completed' ? 'bg-blue-100 text-blue-700' : 'text-gray-600 hover:bg-gray-100' }}">
                    Completed
                </a>
            </div>

            <div class="flex items-center gap-2">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="text" 
                           placeholder="Search requests..." 
                           class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>
        </div>
    </div>

    <!-- Requests Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        @if($requests->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Request ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Service</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($requests as $request)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-medium text-gray-900">#REQ{{ str_pad($request->id, 6, '0', STR_PAD_LEFT) }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    @if($request->service && $request->service->images)
                                    <img class="h-10 w-10 rounded-lg object-cover" 
                                         src="{{ asset('storage/' . $request->service->images[0]) }}" 
                                         alt="{{ $request->service->title }}">
                                    @else
                                    <div class="h-10 w-10 bg-gray-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-concierge-bell text-gray-400"></i>
                                    </div>
                                    @endif
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $request->service->title ?? 'Service Deleted' }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ $request->service->category->name ?? 'N/A' }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">{{ $request->user->name ?? 'N/A' }}</div>
                            <div class="text-sm text-gray-500">{{ $request->user->email ?? '' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $request->created_at->format('M d, Y') }}</div>
                            <div class="text-sm text-gray-500">{{ $request->created_at->format('h:i A') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $statusColors = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'quoted' => 'bg-blue-100 text-blue-800',
                                    'in_progress' => 'bg-purple-100 text-purple-800',
                                    'completed' => 'bg-green-100 text-green-800',
                                    'cancelled' => 'bg-red-100 text-red-800'
                                ];
                            @endphp
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusColors[$request->status] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                @if($request->quoted_price)
                                UGX {{ number_format($request->quoted_price) }}
                                @elseif($request->service && $request->service->price)
                                UGX {{ number_format($request->service->price) }}
                                @else
                                <span class="text-gray-400">Not quoted</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('vendor.services.request-detail', $request->id) }}" 
                               class="text-blue-600 hover:text-blue-900 mr-3">
                                <i class="fas fa-eye mr-1"></i> View
                            </a>
                            @if($request->status === 'pending')
                            <a href="{{ route('vendor.services.request-detail', $request->id) }}#quote" 
                               class="text-green-600 hover:text-green-900">
                                <i class="fas fa-file-invoice-dollar mr-1"></i> Quote
                            </a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $requests->links() }}
        </div>

        @else
        <!-- Empty State -->
        <div class="text-center py-12">
            <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                <i class="fas fa-inbox text-gray-400 text-3xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No service requests yet</h3>
            <p class="text-gray-500 mb-6">When customers request your services, they'll appear here.</p>
            <a href="{{ route('vendor.services.index') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                <i class="fas fa-list mr-2"></i> View My Services
            </a>
        </div>
        @endif
    </div>

    <!-- Quick Actions -->
    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Need Help?</h3>
            <ul class="space-y-3">
                <li class="flex items-center gap-2">
                    <i class="fas fa-question-circle text-blue-500"></i>
                    <a href="#" class="text-blue-600 hover:text-blue-800">How to respond to requests</a>
                </li>
                <li class="flex items-center gap-2">
                    <i class="fas fa-file-invoice-dollar text-green-500"></i>
                    <a href="#" class="text-blue-600 hover:text-blue-800">Pricing guidelines</a>
                </li>
                <li class="flex items-center gap-2">
                    <i class="fas fa-star text-yellow-500"></i>
                    <a href="#" class="text-blue-600 hover:text-blue-800">Getting good reviews</a>
                </li>
            </ul>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Stats</h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Response Rate</span>
                    <span class="font-semibold text-gray-800">94%</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Avg. Response Time</span>
                    <span class="font-semibold text-gray-800">2.4 hrs</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Completion Rate</span>
                    <span class="font-semibold text-gray-800">98%</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Tips</h3>
            <div class="space-y-3">
                <p class="text-sm text-gray-600">
                    <i class="fas fa-lightbulb text-yellow-500 mr-2"></i>
                    Respond to requests within 24 hours for better conversion
                </p>
                <p class="text-sm text-gray-600">
                    <i class="fas fa-lightbulb text-yellow-500 mr-2"></i>
                    Provide detailed quotes to reduce back-and-forth
                </p>
                <p class="text-sm text-gray-600">
                    <i class="fas fa-lightbulb text-yellow-500 mr-2"></i>
                    Update request status promptly to keep customers informed
                </p>
            </div>
        </div>
    </div>
</div>

<script>
// Search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('input[placeholder="Search requests..."]');
    if (searchInput) {
        searchInput.addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                const searchTerm = this.value.trim();
                if (searchTerm) {
                    const url = new URL(window.location.href);
                    url.searchParams.set('search', searchTerm);
                    window.location.href = url.toString();
                }
            }
        });
    }
});
</script>
@endsection