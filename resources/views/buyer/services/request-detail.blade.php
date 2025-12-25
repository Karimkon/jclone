@extends('layouts.buyer')

@section('title', 'Service Request Details - ' . config('app.name'))

@section('content')
<div class="container mx-auto px-2 sm:px-4 py-4 sm:py-8 pb-20 sm:pb-8">
    <!-- Back Button -->
    <a href="{{ route('buyer.service-requests.index') }}" class="inline-flex items-center text-gray-600 hover:text-primary mb-4">
        <i class="fas fa-arrow-left mr-2"></i> Back to Requests
    </a>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-4 sm:space-y-6">
            <!-- Service Details -->
            <div class="bg-white rounded-xl shadow-sm p-4 sm:p-6">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-4">
                    <div>
                        <h1 class="text-xl sm:text-2xl font-bold text-gray-900">{{ $request->service->title ?? 'Service' }}</h1>
                        <p class="text-gray-600 mt-1">
                            <i class="fas fa-store mr-1"></i>
                            {{ $request->service->vendor->business_name ?? 'Service Provider' }}
                        </p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-sm font-medium self-start
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

                @if($request->service->category)
                <div class="text-sm text-gray-500 mb-4">
                    <i class="fas fa-tag mr-1"></i> {{ $request->service->category->name }}
                </div>
                @endif
            </div>

            <!-- Your Request -->
            <div class="bg-white rounded-xl shadow-sm p-4 sm:p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Your Request Details</h2>

                @if($request->description)
                <div class="mb-4">
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Description</h3>
                    <div class="bg-gray-50 rounded-lg p-4 text-sm text-gray-700 whitespace-pre-wrap">{{ $request->description }}</div>
                </div>
                @endif

                @if($request->requirements)
                <div class="mb-4">
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Requirements</h3>
                    <div class="bg-gray-50 rounded-lg p-4 text-sm text-gray-700 whitespace-pre-wrap">{{ $request->requirements }}</div>
                </div>
                @endif

                <div class="grid grid-cols-2 gap-4 text-sm">
                    @if($request->preferred_date)
                    <div>
                        <span class="text-gray-500">Preferred Date</span>
                        <p class="font-medium">{{ \Carbon\Carbon::parse($request->preferred_date)->format('M d, Y') }}</p>
                    </div>
                    @endif
                    @if($request->budget)
                    <div>
                        <span class="text-gray-500">Your Budget</span>
                        <p class="font-medium">UGX {{ number_format($request->budget, 0) }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Quote (if received) -->
            @if($request->quoted_price)
            <div class="bg-white rounded-xl shadow-sm p-4 sm:p-6 border-2 border-blue-200">
                <h2 class="text-lg font-bold text-gray-900 mb-4">
                    <i class="fas fa-file-invoice-dollar text-blue-600 mr-2"></i>Vendor Quote
                </h2>

                <div class="text-center py-4">
                    <p class="text-3xl font-bold text-blue-600">UGX {{ number_format($request->quoted_price, 0) }}</p>
                    @if($request->quote_notes)
                    <p class="text-sm text-gray-600 mt-3">{{ $request->quote_notes }}</p>
                    @endif
                </div>

                @if($request->status == 'quoted')
                <div class="flex gap-3 mt-4">
                    <form action="{{ route('buyer.service-requests.accept', $request->id) }}" method="POST" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium">
                            <i class="fas fa-check mr-2"></i> Accept Quote
                        </button>
                    </form>
                    <form action="{{ route('buyer.service-requests.cancel', $request->id) }}" method="POST"
                          onsubmit="return confirm('Cancel this request?')" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full px-4 py-3 border border-red-300 text-red-600 rounded-lg hover:bg-red-50 font-medium">
                            <i class="fas fa-times mr-2"></i> Decline
                        </button>
                    </form>
                </div>
                @endif
            </div>
            @endif

            <!-- Review -->
            @if($request->review)
            <div class="bg-white rounded-xl shadow-sm p-4 sm:p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Your Review</h2>
                <div class="flex items-center mb-3">
                    @for($i = 1; $i <= 5; $i++)
                    <i class="fas fa-star {{ $i <= $request->review->rating ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                    @endfor
                    <span class="ml-2 text-gray-600">({{ $request->review->rating }}/5)</span>
                </div>
                @if($request->review->comment)
                <p class="text-gray-700">{{ $request->review->comment }}</p>
                @endif
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-4 sm:space-y-6">
            <!-- Status Timeline -->
            <div class="bg-white rounded-xl shadow-sm p-4 sm:p-6">
                <h3 class="font-bold text-gray-900 mb-4">Request Timeline</h3>
                <div class="space-y-4">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-paper-plane text-green-600 text-sm"></i>
                        </div>
                        <div>
                            <p class="font-medium text-sm">Request Sent</p>
                            <p class="text-xs text-gray-500">{{ $request->created_at->format('M d, Y') }}</p>
                        </div>
                    </div>

                    @if($request->quoted_at)
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-file-invoice text-blue-600 text-sm"></i>
                        </div>
                        <div>
                            <p class="font-medium text-sm">Quote Received</p>
                            <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($request->quoted_at)->format('M d, Y') }}</p>
                        </div>
                    </div>
                    @endif

                    @if($request->accepted_at)
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-handshake text-indigo-600 text-sm"></i>
                        </div>
                        <div>
                            <p class="font-medium text-sm">Quote Accepted</p>
                            <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($request->accepted_at)->format('M d, Y') }}</p>
                        </div>
                    </div>
                    @endif

                    @if($request->completed_at)
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-check-double text-green-600 text-sm"></i>
                        </div>
                        <div>
                            <p class="font-medium text-sm text-green-600">Completed</p>
                            <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($request->completed_at)->format('M d, Y') }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            @if($request->status == 'in_progress')
            <div class="bg-white rounded-xl shadow-sm p-4 sm:p-6">
                <h3 class="font-bold text-gray-900 mb-4">Actions</h3>
                <form action="{{ route('buyer.service-requests.complete', $request->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium">
                        <i class="fas fa-check-double mr-2"></i> Mark as Completed
                    </button>
                </form>
                <p class="text-xs text-gray-500 mt-2 text-center">Confirm when work is done</p>
            </div>
            @endif

            @if($request->status == 'completed' && !$request->review)
            <div class="bg-yellow-50 rounded-xl p-4 border border-yellow-200">
                <h3 class="font-bold text-yellow-800 mb-2">Leave a Review</h3>
                <p class="text-sm text-yellow-700 mb-3">Share your experience with this service</p>
                <a href="{{ route('buyer.service-requests.review', $request->id) }}"
                   class="block w-full px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 text-center font-medium">
                    <i class="fas fa-star mr-2"></i> Write Review
                </a>
            </div>
            @endif

            <!-- Contact Provider -->
            <div class="bg-blue-50 rounded-xl p-4">
                <p class="text-sm text-blue-800 mb-3">Need to discuss details?</p>
                <a href="{{ route('chat.index') }}"
                   class="inline-flex items-center text-sm text-blue-600 hover:underline font-medium">
                    <i class="fas fa-comments mr-2"></i> Message Provider
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
