@extends('layouts.buyer')

@section('title', 'My Reviews - ' . config('app.name'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">My Reviews</h1>
        <p class="text-gray-600">Manage your product reviews and see items waiting for feedback</p>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6 rounded-r-lg">
        <div class="flex">
            <i class="fas fa-check-circle text-green-400 mr-3 mt-0.5"></i>
            <p class="text-green-700">{{ session('success') }}</p>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6 rounded-r-lg">
        <div class="flex">
            <i class="fas fa-exclamation-circle text-red-400 mr-3 mt-0.5"></i>
            <p class="text-red-700">{{ session('error') }}</p>
        </div>
    </div>
    @endif

    <!-- Pending Reviews Section -->
    @if($pendingReviews->count() > 0)
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 mb-8">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center">
                <i class="fas fa-star text-amber-600"></i>
            </div>
            <div>
                <h2 class="text-lg font-bold text-gray-800">Items Waiting for Review</h2>
                <p class="text-sm text-gray-600">Share your experience with these purchased items</p>
            </div>
        </div>
        
        <div class="grid gap-4">
            @foreach($pendingReviews as $item)
            <div class="bg-white rounded-xl p-4 flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                        @if($item->listing && $item->listing->images->first())
                        <img src="{{ asset('storage/' . $item->listing->images->first()->path) }}" 
                             alt="{{ $item->title }}"
                             class="w-full h-full object-cover">
                        @else
                        <div class="w-full h-full flex items-center justify-center">
                            <i class="fas fa-image text-gray-300"></i>
                        </div>
                        @endif
                    </div>
                    <div>
                        <h3 class="font-medium text-gray-800">{{ $item->title }}</h3>
                        <p class="text-sm text-gray-500">
                            Order #{{ $item->order->order_number }} • 
                            Delivered {{ $item->order->updated_at->diffForHumans() }}
                        </p>
                    </div>
                </div>
                <a href="{{ route('buyer.reviews.create', ['order_item_id' => $item->id]) }}" 
                   class="px-4 py-2 bg-primary text-white rounded-lg font-medium hover:bg-indigo-700 transition flex items-center gap-2">
                    <i class="fas fa-pen"></i>
                    <span class="hidden sm:inline">Write Review</span>
                </a>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- My Reviews Section -->
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h2 class="text-xl font-bold text-gray-800">My Reviews</h2>
        </div>

        @if($reviews->count() > 0)
        <div class="divide-y divide-gray-100">
            @foreach($reviews as $review)
            <div class="p-6 hover:bg-gray-50 transition">
                <div class="flex gap-4">
                    <!-- Product Image -->
                    <div class="w-20 h-20 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                        @if($review->listing && $review->listing->images->first())
                        <img src="{{ asset('storage/' . $review->listing->images->first()->path) }}" 
                             alt="{{ $review->listing->title ?? 'Product' }}"
                             class="w-full h-full object-cover">
                        @else
                        <div class="w-full h-full flex items-center justify-center">
                            <i class="fas fa-image text-gray-300"></i>
                        </div>
                        @endif
                    </div>
                    
                    <!-- Review Content -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="font-medium text-gray-800 line-clamp-1">
                                    {{ $review->listing->title ?? 'Product Unavailable' }}
                                </h3>
                                <div class="flex items-center gap-2 mt-1">
                                    <!-- Star Rating -->
                                    <div class="flex gap-0.5">
                                        @for($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-200' }} text-sm"></i>
                                        @endfor
                                    </div>
                                    <span class="text-sm text-gray-500">{{ $review->created_at->format('M d, Y') }}</span>
                                </div>
                            </div>
                            
                            <!-- Actions -->
                            <div class="flex items-center gap-2">
                                <a href="{{ route('buyer.reviews.edit', $review->id) }}" 
                                   class="p-2 text-gray-400 hover:text-primary transition" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('buyer.reviews.destroy', $review->id) }}" method="POST" 
                                      onsubmit="return confirm('Are you sure you want to delete this review?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-gray-400 hover:text-red-500 transition" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        <!-- Review Title & Comment -->
                        @if($review->title)
                        <h4 class="font-medium text-gray-700 mt-2">{{ $review->title }}</h4>
                        @endif
                        
                        @if($review->comment)
                        <p class="text-gray-600 mt-1 line-clamp-2">{{ $review->comment }}</p>
                        @endif
                        
                        <!-- Review Images -->
                        @if($review->hasImages())
                        <div class="flex gap-2 mt-3">
                            @foreach($review->images as $image)
                            <div class="w-12 h-12 rounded-lg overflow-hidden">
                                <img src="{{ asset('storage/' . $image) }}" alt="Review image" class="w-full h-full object-cover">
                            </div>
                            @endforeach
                        </div>
                        @endif
                        
                        <!-- Status & Stats -->
                        <div class="flex items-center gap-4 mt-3 text-sm">
                            <span class="px-2 py-1 rounded-full {{ $review->status === 'approved' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                {{ ucfirst($review->status) }}
                            </span>
                            @if($review->is_verified_purchase)
                            <span class="text-green-600">
                                <i class="fas fa-check-circle mr-1"></i>Verified Purchase
                            </span>
                            @endif
                            <span class="text-gray-400">
                                <i class="fas fa-thumbs-up mr-1"></i>{{ $review->helpful_count }} found helpful
                            </span>
                        </div>
                        
                        <!-- Vendor Response -->
                        @if($review->hasVendorResponse())
                        <div class="mt-4 p-3 bg-blue-50 rounded-lg border-l-4 border-blue-400">
                            <p class="text-sm font-medium text-blue-800 mb-1">
                                <i class="fas fa-store mr-1"></i>Vendor Response
                            </p>
                            <p class="text-sm text-blue-700">{{ $review->vendor_response }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        
        <!-- Pagination -->
        <div class="p-6 border-t border-gray-100">
            {{ $reviews->links() }}
        </div>
        @else
        <div class="p-12 text-center">
            <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-comment-dots text-gray-300 text-3xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-700 mb-2">No Reviews Yet</h3>
            <p class="text-gray-500 mb-6">You haven't written any reviews yet. Share your experience with products you've purchased!</p>
            <a href="{{ route('buyer.orders.index') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-primary text-white rounded-lg font-medium hover:bg-indigo-700 transition">
                <i class="fas fa-shopping-bag"></i>
                View My Orders
            </a>
        </div>
        @endif
    </div>
</div>
@endsection