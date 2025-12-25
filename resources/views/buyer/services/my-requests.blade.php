@extends('layouts.buyer')

@section('title', 'My Service Requests - ' . config('app.name'))

@section('content')
<div class="container mx-auto px-2 sm:px-4 py-4 sm:py-8 pb-20 sm:pb-8">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold">My Service Requests</h1>
            <p class="text-sm text-gray-600 mt-1">Track your service requests</p>
        </div>
        <a href="{{ route('services.index') }}" class="inline-flex items-center px-4 py-2 bg-primary text-white rounded-lg hover:bg-indigo-700 text-sm font-medium">
            <i class="fas fa-search mr-2"></i> Browse Services
        </a>
    </div>

    <!-- Filter -->
    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-3 items-center">
            <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary">
                <option value="">All Status</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="quoted" {{ request('status') == 'quoted' ? 'selected' : '' }}>Quoted</option>
                <option value="accepted" {{ request('status') == 'accepted' ? 'selected' : '' }}>Accepted</option>
                <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm">
                <i class="fas fa-filter mr-1"></i> Filter
            </button>
            @if(request('status'))
            <a href="{{ route('buyer.service-requests.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Clear</a>
            @endif
        </form>
    </div>

    @if($requests->count() > 0)
    <!-- Mobile Card View -->
    <div class="sm:hidden space-y-4">
        @foreach($requests as $request)
        <div class="bg-white rounded-lg shadow-sm p-4">
            <div class="flex justify-between items-start mb-3">
                <div class="flex-1">
                    <h3 class="font-semibold text-gray-900">{{ Str::limit($request->service->title ?? 'Service', 40) }}</h3>
                    <p class="text-xs text-gray-500 mt-1">
                        <i class="fas fa-store mr-1"></i>
                        {{ $request->service->vendor->business_name ?? 'Provider' }}
                    </p>
                </div>
                <span class="px-2 py-1 rounded-full text-xs font-medium flex-shrink-0 ml-2
                    @if($request->status == 'pending') bg-yellow-100 text-yellow-800
                    @elseif($request->status == 'quoted') bg-blue-100 text-blue-800
                    @elseif($request->status == 'accepted') bg-indigo-100 text-indigo-800
                    @elseif($request->status == 'in_progress') bg-purple-100 text-purple-800
                    @elseif($request->status == 'completed') bg-green-100 text-green-800
                    @elseif($request->status == 'cancelled') bg-red-100 text-red-800
                    @else bg-gray-100 text-gray-800 @endif">
                    {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                </span>
            </div>

            <div class="flex items-center gap-4 text-xs text-gray-500 mb-3">
                <span><i class="fas fa-calendar mr-1"></i> {{ $request->created_at->format('M d, Y') }}</span>
                @if($request->quoted_price)
                <span class="text-green-600 font-medium">
                    <i class="fas fa-tag mr-1"></i> UGX {{ number_format($request->quoted_price, 0) }}
                </span>
                @endif
            </div>

            @if($request->description)
            <p class="text-sm text-gray-600 mb-3">{{ Str::limit($request->description, 100) }}</p>
            @endif

            <div class="flex justify-between items-center pt-3 border-t gap-2">
                <a href="{{ route('buyer.service-requests.show', $request->id) }}"
                   class="text-primary text-sm font-medium">
                    <i class="fas fa-eye mr-1"></i> View
                </a>

                @if($request->status == 'quoted')
                <form action="{{ route('buyer.service-requests.accept', $request->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="text-green-600 text-sm font-medium">
                        <i class="fas fa-check mr-1"></i> Accept
                    </button>
                </form>
                @endif

                @if(in_array($request->status, ['pending', 'quoted']))
                <form action="{{ route('buyer.service-requests.cancel', $request->id) }}" method="POST"
                      onsubmit="return confirm('Cancel this request?')" class="inline">
                    @csrf
                    <button type="submit" class="text-red-600 text-sm">
                        <i class="fas fa-times mr-1"></i> Cancel
                    </button>
                </form>
                @endif

                @if($request->status == 'in_progress')
                <form action="{{ route('buyer.service-requests.complete', $request->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="text-green-600 text-sm font-medium">
                        <i class="fas fa-check-double mr-1"></i> Mark Done
                    </button>
                </form>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <!-- Desktop Table View -->
    <div class="hidden sm:block bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 border-b">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Service</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Provider</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quote</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($requests as $request)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900">{{ Str::limit($request->service->title ?? 'Service', 35) }}</div>
                            @if($request->service->category)
                            <div class="text-xs text-gray-500">{{ $request->service->category->name }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $request->service->vendor->business_name ?? 'Provider' }}
                        </td>
                        <td class="px-6 py-4">
                            @if($request->quoted_price)
                            <span class="text-sm font-medium text-green-600">UGX {{ number_format($request->quoted_price, 0) }}</span>
                            @else
                            <span class="text-sm text-gray-400">Pending</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $request->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 rounded-full text-xs font-medium
                                @if($request->status == 'pending') bg-yellow-100 text-yellow-800
                                @elseif($request->status == 'quoted') bg-blue-100 text-blue-800
                                @elseif($request->status == 'accepted') bg-indigo-100 text-indigo-800
                                @elseif($request->status == 'in_progress') bg-purple-100 text-purple-800
                                @elseif($request->status == 'completed') bg-green-100 text-green-800
                                @elseif($request->status == 'cancelled') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('buyer.service-requests.show', $request->id) }}"
                                   class="text-primary hover:text-indigo-700 text-sm">View</a>

                                @if($request->status == 'quoted')
                                <form action="{{ route('buyer.service-requests.accept', $request->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="text-green-600 hover:text-green-800 text-sm">Accept</button>
                                </form>
                                @endif

                                @if(in_array($request->status, ['pending', 'quoted']))
                                <form action="{{ route('buyer.service-requests.cancel', $request->id) }}" method="POST"
                                      onsubmit="return confirm('Cancel?')">
                                    @csrf
                                    <button type="submit" class="text-red-600 hover:text-red-800 text-sm">Cancel</button>
                                </form>
                                @endif

                                @if($request->status == 'in_progress')
                                <form action="{{ route('buyer.service-requests.complete', $request->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="text-green-600 hover:text-green-800 text-sm">Complete</button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($requests->hasPages())
        <div class="px-6 py-4 border-t">
            {{ $requests->links() }}
        </div>
        @endif
    </div>

    @if($requests->hasPages())
    <div class="sm:hidden mt-4">
        {{ $requests->links() }}
    </div>
    @endif

    @else
    <!-- Empty State -->
    <div class="bg-white rounded-xl shadow-sm p-8 text-center">
        <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-6">
            <i class="fas fa-clipboard-list text-gray-400 text-4xl"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-800 mb-2">No service requests</h3>
        <p class="text-gray-600 mb-6">Request a service to see your requests here</p>
        <a href="{{ route('services.index') }}"
           class="inline-flex items-center px-6 py-3 bg-primary text-white rounded-lg hover:bg-indigo-700 font-medium">
            <i class="fas fa-search mr-2"></i> Browse Services
        </a>
    </div>
    @endif
</div>
@endsection
