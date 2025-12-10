@extends('layouts.vendor')

@section('title', 'Product Reviews - ' . config('app.name'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Product Reviews</h1>
            <p class="text-gray-600">Manage and respond to customer reviews</p>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6 rounded-r-lg">
        <p class="text-green-700">{{ session('success') }}</p>
    </div>
    @endif

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-2xl shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Reviews</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $stats['total_reviews'] }}</p>
                </div>
                <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center">
                    <i class="fas fa-comments text-primary text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-2xl shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Average Rating</p>
                    <div class="flex items-center gap-2">
                        <p class="text-3xl font-bold text-gray-800">{{ number_format($stats['average_rating'], 1) }}</p>
                        <i class="fas fa-star text-yellow-400"></i>
                    </div>
                </div>
                <div class="w-12 h-12 bg-yellow-50 rounded-xl flex items-center justify-center">
                    <i class="fas fa-star text-yellow-500 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-2xl shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Pending Response</p>
                    <p class="text-3xl font-bold text-orange-600">{{ $stats['pending_response'] }}</p>
                </div>
                <div class="w-12 h-12 bg-orange-50 rounded-xl flex items-center justify-center">
                    <i class="fas fa-clock text-orange-500 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-2xl shadow-sm p-6">
            <div>
                <p class="text-sm text-gray-500 mb-3">Rating Distribution</p>
                <div class="space-y-1">
                    @foreach([5,4,3,2,1] as $stars)
                    @php
                        $count = $stats['distribution'][$stars] ?? 0;
                        $total = array_sum($stats['distribution']) ?: 1;
                        $percentage = round(($count / $total) * 100);
                    @endphp
                    <div class="flex items-center gap-2">
                        <span class="text-xs w-3">{{ $stars }}</span>
                        <div class="flex-1 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full bg-yellow-400 rounded-full" style="width: {{ $percentage }}%"></div>
                        </div>
                        <span class="text-xs text-gray-400 w-6">{{ $count }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="bg-white rounded-2xl shadow-sm mb-6">
        <div class="flex border-b">
            <a href="{{ route('vendor.reviews.index', ['filter' => 'all']) }}" 
               class="px-6 py-4 font-medium {{ $filter === 'all' ? 'text-primary border-b-2 border-primary' : 'text-gray-500 hover:text-gray-700' }}">
                All Reviews
            </a>
            <a href="{{ route('vendor.reviews.index', ['filter' => 'pending_response']) }}" 
               class="px-6 py-4 font-medium {{ $filter === 'pending_response' ? 'text-primary border-b-2 border-primary' : 'text-gray-500 hover:text-gray-700' }}">
                Pending Response
                @if($stats['pending_response'] > 0)
                <span class="ml-2 px-2 py-0.5 bg-orange-100 text-orange-600 text-xs rounded-full">{{ $stats['pending_response'] }}</span>
                @endif
            </a>
            <a href="{{ route('vendor.reviews.index', ['filter' => 'responded']) }}" 
               class="px-6 py-4 font-medium {{ $filter === 'responded' ? 'text-primary border-b-2 border-primary' : 'text-gray-500 hover:text-gray-700' }}">
                Responded
            </a>
        </div>
    </div>

    <!-- Reviews List -->
    @if($reviews->count() > 0)
    <div class="space-y-4">
        @foreach($reviews as $review)
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="p-6">
                <!-- Review Header -->
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-primary to-purple-600 rounded-full flex items-center justify-center text-white font-bold">
                            {{ strtoupper(substr($review->user->name ?? 'U', 0, 1)) }}
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-gray-800">{{ $review->user->name ?? 'Anonymous' }}</span>
                                @if($review->is_verified_purchase)
                                <span class="px-2 py-0.5 bg-green-100 text-green-700 text-xs font-medium rounded-full">
                                    Verified
                                </span>
                                @endif
                            </div>
                            <div class="flex items-center gap-2 mt-1">
                                <div class="flex">
                                    @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star text-sm {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-200' }}"></i>
                                    @endfor
                                </div>
                                <span class="text-sm text-gray-500">{{ $review->created_at->format('M d, Y') }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-right">
                        <p class="text-sm text-gray-500">Order #{{ $review->order->order_number ?? 'N/A' }}</p>
                    </div>
                </div>
                
                <!-- Product Info -->
                <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg mb-4">
                    <div class="w-12 h-12 bg-gray-200 rounded-lg overflow-hidden flex-shrink-0">
                        @if($review->listing && $review->listing->images && $review->listing->images->first())
                        <img src="{{ asset('storage/' . $review->listing->images->first()->path) }}" class="w-full h-full object-cover">
                        @else
                        <div class="w-full h-full flex items-center justify-center">
                            <i class="fas fa-image text-gray-400"></i>
                        </div>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-gray-800 truncate">{{ $review->listing->title ?? 'Product Unavailable' }}</p>
                        <p class="text-sm text-gray-500">Product Review</p>
                    </div>
                </div>
                
                <!-- Review Content -->
                @if($review->title)
                <h4 class="font-semibold text-gray-800 mb-2">{{ $review->title }}</h4>
                @endif
                
                @if($review->comment)
                <p class="text-gray-600 mb-4">{{ $review->comment }}</p>
                @endif
                
                <!-- Review Images -->
                @if($review->hasImages())
                <div class="flex flex-wrap gap-2 mb-4">
                    @foreach($review->images as $image)
                    <div class="w-16 h-16 rounded-lg overflow-hidden">
                        <img src="{{ asset('storage/' . $image) }}" alt="Review image" class="w-full h-full object-cover">
                    </div>
                    @endforeach
                </div>
                @endif
                
                <!-- Helpful Stats -->
                <div class="flex items-center gap-4 text-sm text-gray-500 mb-4">
                    <span><i class="fas fa-thumbs-up mr-1"></i>{{ $review->helpful_count }} helpful</span>
                    <span><i class="fas fa-thumbs-down mr-1"></i>{{ $review->unhelpful_count }} unhelpful</span>
                </div>
            </div>
            
            <!-- Vendor Response Section -->
            <div class="border-t border-gray-100 p-6 bg-gray-50">
                @if($review->hasVendorResponse())
                <div class="mb-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-medium text-gray-700">
                            <i class="fas fa-reply text-primary mr-2"></i>Your Response
                        </span>
                        <span class="text-sm text-gray-500">{{ $review->vendor_responded_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-gray-600 bg-white p-4 rounded-lg">{{ $review->vendor_response }}</p>
                    
                    <div class="flex gap-2 mt-3">
                        <button onclick="showEditResponseForm({{ $review->id }})" class="text-sm text-primary hover:underline">
                            <i class="fas fa-edit mr-1"></i>Edit
                        </button>
                        <form action="{{ route('vendor.reviews.delete-response', $review->id) }}" method="POST" class="inline"
                              onsubmit="return confirm('Delete your response?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm text-red-500 hover:underline">
                                <i class="fas fa-trash mr-1"></i>Delete
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Edit Response Form (Hidden) -->
                <div id="editResponseForm{{ $review->id }}" class="hidden">
                    <form action="{{ route('vendor.reviews.update-response', $review->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <textarea name="vendor_response" rows="3" 
                                  class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent"
                                  placeholder="Update your response...">{{ $review->vendor_response }}</textarea>
                        <div class="flex justify-end gap-2 mt-3">
                            <button type="button" onclick="hideEditResponseForm({{ $review->id }})" class="px-4 py-2 text-gray-600 hover:text-gray-800">
                                Cancel
                            </button>
                            <button type="submit" class="px-6 py-2 bg-primary text-white rounded-lg font-medium hover:bg-indigo-700 transition">
                                Update Response
                            </button>
                        </div>
                    </form>
                </div>
                @else
                <!-- Add Response Form -->
                <form action="{{ route('vendor.reviews.respond', $review->id) }}" method="POST" id="responseForm{{ $review->id }}">
                    @csrf
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-reply text-primary mr-2"></i>Respond to this review
                    </label>
                    <textarea name="vendor_response" rows="3" 
                              class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent"
                              placeholder="Thank the customer or address their feedback professionally..."
                              required></textarea>
                    <div class="flex justify-end mt-3">
                        <button type="submit" class="px-6 py-2 bg-primary text-white rounded-lg font-medium hover:bg-indigo-700 transition">
                            <i class="fas fa-paper-plane mr-2"></i>Submit Response
                        </button>
                    </div>
                </form>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    
    <!-- Pagination -->
    <div class="mt-8">
        {{ $reviews->appends(['filter' => $filter])->links() }}
    </div>
    @else
    <div class="bg-white rounded-2xl shadow-sm p-12 text-center">
        <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-comment-dots text-gray-300 text-3xl"></i>
        </div>
        <h3 class="text-lg font-medium text-gray-700 mb-2">No Reviews Yet</h3>
        <p class="text-gray-500">When customers review your products, they'll appear here.</p>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function showEditResponseForm(reviewId) {
    document.getElementById('editResponseForm' + reviewId).classList.remove('hidden');
}

function hideEditResponseForm(reviewId) {
    document.getElementById('editResponseForm' + reviewId).classList.add('hidden');
}
</script>
@endpush