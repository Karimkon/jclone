@extends('layouts.buyer')

@section('title', 'My Disputes - ' . config('app.name'))

@section('content')
<div class="container mx-auto px-2 sm:px-4 py-4 sm:py-8 pb-20 sm:pb-8">
    <h1 class="text-2xl sm:text-3xl font-bold mb-6">My Disputes</h1>

    @if(session('success'))
    <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6">
        <p class="text-green-700">{{ session('success') }}</p>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
        <p class="text-red-700">{{ session('error') }}</p>
    </div>
    @endif

    @if(isset($disputes) && $disputes->count() > 0)
    <!-- Mobile Card View -->
    <div class="sm:hidden space-y-4">
        @foreach($disputes as $dispute)
        <div class="bg-white rounded-lg shadow-sm p-4">
            <div class="flex justify-between items-start mb-3">
                <div>
                    <div class="text-sm font-bold text-blue-600">#{{ $dispute->id }}</div>
                    <div class="text-xs text-gray-500">{{ $dispute->created_at->format('M d, Y') }}</div>
                </div>
                <span class="px-2 py-1 rounded-full text-xs font-medium
                    @if($dispute->status == 'open') bg-yellow-100 text-yellow-800
                    @elseif($dispute->status == 'under_review') bg-blue-100 text-blue-800
                    @elseif($dispute->status == 'resolved') bg-green-100 text-green-800
                    @elseif($dispute->status == 'closed') bg-gray-100 text-gray-800
                    @else bg-gray-100 text-gray-800 @endif">
                    {{ ucfirst(str_replace('_', ' ', $dispute->status)) }}
                </span>
            </div>

            <h3 class="font-semibold text-gray-800 mb-2">{{ Str::limit($dispute->subject ?? $dispute->reason, 50) }}</h3>

            @if($dispute->order)
            <div class="flex items-center text-sm text-gray-600 mb-2">
                <i class="fas fa-shopping-bag mr-2 text-gray-400"></i>
                Order: {{ $dispute->order->order_number }}
            </div>
            @endif

            <div class="flex justify-between items-center pt-3 border-t">
                <div class="text-xs text-gray-500">
                    @if($dispute->resolution)
                    <span class="text-green-600"><i class="fas fa-check mr-1"></i> Resolved</span>
                    @else
                    <span><i class="fas fa-clock mr-1"></i> Pending</span>
                    @endif
                </div>
                <a href="{{ route('buyer.disputes.show', $dispute) }}"
                   class="px-3 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700">
                    <i class="fas fa-eye mr-1"></i> View
                </a>
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($disputes as $dispute)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-medium text-primary">#{{ $dispute->id }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">{{ Str::limit($dispute->subject ?? $dispute->reason, 40) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($dispute->order)
                            <a href="{{ route('buyer.orders.show', $dispute->order) }}" class="text-sm text-blue-600 hover:underline">
                                {{ $dispute->order->order_number }}
                            </a>
                            @else
                            <span class="text-sm text-gray-500">N/A</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $dispute->created_at->format('M d, Y') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-3 py-1 rounded-full text-xs font-medium
                                @if($dispute->status == 'open') bg-yellow-100 text-yellow-800
                                @elseif($dispute->status == 'under_review') bg-blue-100 text-blue-800
                                @elseif($dispute->status == 'resolved') bg-green-100 text-green-800
                                @elseif($dispute->status == 'closed') bg-gray-100 text-gray-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst(str_replace('_', ' ', $dispute->status)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('buyer.disputes.show', $dispute) }}"
                               class="text-primary hover:text-indigo-700">
                                <i class="fas fa-eye mr-1"></i> View Details
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($disputes->hasPages())
        <div class="px-6 py-4 border-t">
            {{ $disputes->links() }}
        </div>
        @endif
    </div>

    <!-- Mobile Pagination -->
    @if($disputes->hasPages())
    <div class="sm:hidden mt-4">
        {{ $disputes->links() }}
    </div>
    @endif

    @else
    <!-- Empty State -->
    <div class="bg-white rounded-xl shadow-sm p-8 text-center">
        <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-6">
            <i class="fas fa-check-circle text-green-400 text-4xl"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-800 mb-2">No disputes</h3>
        <p class="text-gray-600 mb-6">You don't have any disputes at the moment. That's great!</p>
        <a href="{{ route('buyer.orders.index') }}"
           class="inline-flex items-center px-6 py-3 bg-primary text-white rounded-lg hover:bg-indigo-700 font-medium">
            <i class="fas fa-shopping-bag mr-2"></i> View My Orders
        </a>
    </div>
    @endif

    <!-- Help Section -->
    <div class="mt-8 bg-blue-50 rounded-xl p-6">
        <h3 class="font-bold text-blue-800 mb-2"><i class="fas fa-info-circle mr-2"></i>Need Help?</h3>
        <p class="text-blue-700 text-sm mb-4">
            If you have an issue with an order that hasn't been resolved, you can open a dispute.
            Our team will review your case and help mediate between you and the vendor.
        </p>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('buyer.orders.index') }}" class="text-sm text-blue-600 hover:underline">
                <i class="fas fa-shopping-bag mr-1"></i> View Orders
            </a>
            <span class="text-blue-300">|</span>
            <a href="{{ route('chat.index') }}" class="text-sm text-blue-600 hover:underline">
                <i class="fas fa-comments mr-1"></i> Contact Vendor
            </a>
        </div>
    </div>
</div>
@endsection
