@extends('layouts.vendor')

@section('title', 'Service Reviews - Vendor Dashboard')
@section('page_title', 'Service Reviews')

@section('content')
<div class="container-fluid">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Reviews</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $reviews->total() }}</p>
                </div>
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-star text-blue-500"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Average Rating</p>
                    <p class="text-2xl font-bold text-gray-800">
                        {{ number_format($reviews->avg('rating') ?? 0, 1) }}
                    </p>
                </div>
                <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-chart-line text-yellow-500"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">With Response</p>
                    <p class="text-2xl font-bold text-gray-800">
                        {{ $reviews->whereNotNull('vendor_response')->count() }}
                    </p>
                </div>
                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-reply text-purple-500"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Verified Reviews</p>
                    <p class="text-2xl font-bold text-gray-800">
                        {{ $reviews->where('is_verified', true)->count() }}
                    </p>
                </div>
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-500"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Reviews Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <!-- Header -->
        <div class="p-6 border-b border-gray-200">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <h2 class="text-xl font-bold text-gray-800">Customer Reviews</h2>
                
                <div class="flex items-center gap-2">
                    <a href="{{ route('vendor.services.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                        <i class="fas fa-list mr-2"></i> My Services
                    </a>
                </div>
            </div>
        </div>

        @if($reviews->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Service</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rating</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Review</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Response</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($reviews as $review)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    @if($review->service && $review->service->images)
                                    <img class="h-10 w-10 rounded-lg object-cover" 
                                         src="{{ asset('storage/' . $review->service->images[0]) }}" 
                                         alt="{{ $review->service->title }}">
                                    @else
                                    <div class="h-10 w-10 bg-gray-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-concierge-bell text-gray-400"></i>
                                    </div>
                                    @endif
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $review->service->title ?? 'Service Deleted' }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        @if($review->service && $review->service->category)
                                        {{ $review->service->category->name }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-8 w-8">
                                    <div class="h-8 w-8 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold">
                                        {{ strtoupper(substr($review->user->name ?? 'U', 0, 1)) }}
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $review->user->name ?? 'Anonymous' }}
                                    </div>
                                    @if($review->is_verified)
                                    <span class="text-xs text-green-600 font-medium">
                                        <i class="fas fa-check-circle"></i> Verified
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                @for($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star text-sm {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-200' }}"></i>
                                @endfor
                                <span class="ml-2 text-sm font-medium text-gray-700">{{ $review->rating }}</span>
                            </div>
                        </td>
                        
                        <td class="px-6 py-4">
                            @if($review->comment)
                            <div class="text-sm text-gray-700 line-clamp-2 max-w-xs">
                                "{{ $review->comment }}"
                            </div>
                            @else
                            <span class="text-sm text-gray-400">No comment</span>
                            @endif
                            
                            @if($review->images)
                            <div class="flex gap-1 mt-1">
                                @foreach(array_slice($review->images, 0, 2) as $image)
                                <img src="{{ asset('storage/' . $image) }}" 
                                     alt="Review image" 
                                     class="w-8 h-8 rounded object-cover cursor-pointer hover:opacity-80"
                                     onclick="openReviewImage('{{ asset('storage/' . $image) }}')">
                                @endforeach
                                @if(count($review->images) > 2)
                                <div class="w-8 h-8 rounded bg-gray-100 flex items-center justify-center text-xs text-gray-500">
                                    +{{ count($review->images) - 2 }}
                                </div>
                                @endif
                            </div>
                            @endif
                        </td>
                        
                        <td class="px-6 py-4">
                            @if($review->vendor_response)
                            <div class="text-sm text-gray-600 line-clamp-2 max-w-xs">
                                {{ $review->vendor_response }}
                            </div>
                            <div class="text-xs text-gray-400 mt-1">
                                {{ $review->responded_at ? $review->responded_at->format('M d') : '' }}
                            </div>
                            @else
                            <span class="text-sm text-gray-400">No response yet</span>
                            @endif
                        </td>
                        
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $review->created_at->format('M d, Y') }}
                        </td>
                        
                        <td class="px-6 py-4 text-sm font-medium">
                            <button onclick="openResponseModal({{ $review->id }}, {{ json_encode($review->vendor_response) }})" 
                                    class="text-blue-600 hover:text-blue-900 mr-3">
                                <i class="fas fa-reply mr-1"></i>
                                {{ $review->vendor_response ? 'Edit' : 'Respond' }}
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $reviews->links() }}
        </div>
        
        @else
        <!-- Empty State -->
        <div class="text-center py-12">
            <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                <i class="fas fa-star text-gray-300 text-3xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No reviews yet</h3>
            <p class="text-gray-500 mb-4">Customer reviews for your services will appear here.</p>
            <a href="{{ route('vendor.services.index') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                <i class="fas fa-list mr-2"></i> View My Services
            </a>
        </div>
        @endif
    </div>

    <!-- Response Modal -->
    <div id="responseModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeResponseModal()"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 animate-scale-in">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-gray-900">Respond to Review</h3>
                    <button onclick="closeResponseModal()" class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center hover:bg-gray-200">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form id="responseForm" method="POST" action="">
                    @csrf
                    <input type="hidden" name="review_id" id="reviewId">
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Your Response *</label>
                        <textarea name="response" id="responseText" rows="4" required
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                  placeholder="Thank the customer for their feedback..."></textarea>
                        <p class="text-xs text-gray-500 mt-1">Your response will be visible to the customer and other users.</p>
                    </div>
                    
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-4">
                        <div class="flex items-start gap-2">
                            <i class="fas fa-lightbulb text-yellow-500 mt-0.5"></i>
                            <div class="text-sm text-yellow-700">
                                <p class="font-medium mb-1">Tips for a good response:</p>
                                <ul class="list-disc list-inside space-y-1">
                                    <li>Be professional and polite</li>
                                    <li>Acknowledge their feedback</li>
                                    <li>Address any concerns raised</li>
                                    <li>Thank them for their business</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex gap-2">
                        <button type="button" onclick="closeResponseModal()" 
                                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                            Submit Response
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Image Viewer Modal -->
    <div id="imageViewerModal" class="fixed inset-0 z-[60] hidden">
        <div class="absolute inset-0 bg-black/90" onclick="closeImageViewer()"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="relative w-full max-w-4xl">
                <button onclick="closeImageViewer()" 
                        class="absolute top-4 right-4 text-white text-2xl hover:text-gray-300 z-10">
                    <i class="fas fa-times"></i>
                </button>
                <img id="reviewFullImage" src="" alt="Review image" class="max-w-full max-h-screen rounded-lg">
            </div>
        </div>
    </div>
</div>

<script>
let currentReviewId = null;

function openResponseModal(reviewId, existingResponse = null) {
    currentReviewId = reviewId;
    const modal = document.getElementById('responseModal');
    const form = document.getElementById('responseForm');
    const responseText = document.getElementById('responseText');
    
    // Set form action
    form.action = `/vendor/services/reviews/${reviewId}/respond`;
    
    // Set review ID
    document.getElementById('reviewId').value = reviewId;
    
    // Set existing response if editing
    if (existingResponse) {
        responseText.value = existingResponse;
    } else {
        responseText.value = '';
    }
    
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    
    // Focus the textarea
    setTimeout(() => responseText.focus(), 100);
}

function closeResponseModal() {
    document.getElementById('responseModal').classList.add('hidden');
    document.body.style.overflow = '';
    currentReviewId = null;
}

function openReviewImage(imageUrl) {
    document.getElementById('reviewFullImage').src = imageUrl;
    document.getElementById('imageViewerModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeImageViewer() {
    document.getElementById('imageViewerModal').classList.add('hidden');
    document.body.style.overflow = '';
}

// Handle form submission
document.getElementById('responseForm').addEventListener('submit', function(e) {
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Submitting...';
    
    // Form will submit normally
});

// Keyboard shortcuts
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeResponseModal();
        closeImageViewer();
    }
});

// Initialize on load
document.addEventListener('DOMContentLoaded', function() {
    // Check if there's a response success message
    if (window.location.search.includes('response=success')) {
        showToast('Response submitted successfully!', 'success');
    }
});

// Toast notification function (if not already defined)
function showToast(message, type = 'success') {
    const colors = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        info: 'bg-blue-500'
    };
    
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center gap-3`;
    toast.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
        <span>${message}</span>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}
</script>

<style>
.animate-scale-in {
    animation: scale-in 0.3s ease forwards;
}

@keyframes scale-in {
    from { opacity: 0; transform: scale(0.9); }
    to { opacity: 1; transform: scale(1); }
}
</style>
@endsection