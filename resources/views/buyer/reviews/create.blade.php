@extends('layouts.buyer')

@section('title', 'Write Review - ' . config('app.name'))

@push('styles')
<style>
    .star-rating-input {
        display: flex;
        flex-direction: row-reverse;
        justify-content: flex-end;
        gap: 4px;
    }
    
    .star-rating-input input {
        display: none;
    }
    
    .star-rating-input label {
        cursor: pointer;
        font-size: 32px;
        color: #d1d5db;
        transition: color 0.2s;
    }
    
    .star-rating-input label:hover,
    .star-rating-input label:hover ~ label,
    .star-rating-input input:checked ~ label {
        color: #fbbf24;
    }
    
    .star-rating-small label {
        font-size: 24px;
    }
    
    .image-preview {
        position: relative;
    }
    
    .image-preview .remove-btn {
        position: absolute;
        top: -8px;
        right: -8px;
        width: 24px;
        height: 24px;
        background: #ef4444;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 12px;
        opacity: 0;
        transition: opacity 0.2s;
    }
    
    .image-preview:hover .remove-btn {
        opacity: 1;
    }
</style>
@endpush

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('buyer.reviews.index') }}" class="text-primary hover:text-indigo-700 font-medium inline-flex items-center mb-4">
                <i class="fas fa-arrow-left mr-2"></i> Back to Reviews
            </a>
            <h1 class="text-3xl font-bold text-gray-800">Write a Review</h1>
        </div>

        @if(session('error'))
        <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6 rounded-r-lg">
            <p class="text-red-700">{{ session('error') }}</p>
        </div>
        @endif

        <!-- Product Info Card -->
        <div class="bg-white rounded-2xl shadow-sm p-6 mb-6">
            <div class="flex gap-4">
                <div class="w-24 h-24 bg-gray-100 rounded-xl overflow-hidden flex-shrink-0">
                    @if($orderItem->listing && $orderItem->listing->images->first())
                    <img src="{{ asset('storage/' . $orderItem->listing->images->first()->path) }}" 
                         alt="{{ $orderItem->title }}"
                         class="w-full h-full object-cover">
                    @else
                    <div class="w-full h-full flex items-center justify-center">
                        <i class="fas fa-image text-gray-300 text-2xl"></i>
                    </div>
                    @endif
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-800">{{ $orderItem->title }}</h2>
                    <p class="text-gray-500 mt-1">Order #{{ $orderItem->order->order_number }}</p>
                    <p class="text-gray-500 text-sm">Delivered {{ $orderItem->order->updated_at->format('F d, Y') }}</p>
                    <span class="inline-flex items-center gap-1 mt-2 text-sm text-green-600">
                        <i class="fas fa-check-circle"></i>
                        Verified Purchase
                    </span>
                </div>
            </div>
        </div>

        <!-- Review Form -->
        <form action="{{ route('buyer.reviews.store') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-2xl shadow-sm overflow-hidden">
            @csrf
            <input type="hidden" name="order_item_id" value="{{ $orderItem->id }}">
            
            <div class="p-6 space-y-6">
                <!-- Overall Rating -->
                <div>
                    <label class="block text-lg font-bold text-gray-800 mb-3">Overall Rating <span class="text-red-500">*</span></label>
                    <div class="star-rating-input">
                        @for($i = 5; $i >= 1; $i--)
                        <input type="radio" name="rating" id="star{{ $i }}" value="{{ $i }}" {{ old('rating') == $i ? 'checked' : '' }} required>
                        <label for="star{{ $i }}"><i class="fas fa-star"></i></label>
                        @endfor
                    </div>
                    @error('rating')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Review Title -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Review Title</label>
                    <input type="text" id="title" name="title" 
                           value="{{ old('title') }}"
                           placeholder="Sum up your experience in a few words"
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent transition">
                    @error('title')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Review Comment -->
                <div>
                    <label for="comment" class="block text-sm font-medium text-gray-700 mb-2">Your Review</label>
                    <textarea id="comment" name="comment" rows="5"
                              placeholder="Tell others what you liked or didn't like about this product. Was the quality good? Did it match the description?"
                              class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent transition resize-none">{{ old('comment') }}</textarea>
                    @error('comment')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Detailed Ratings -->
                <div class="border-t border-gray-100 pt-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Rate Specific Aspects (Optional)</h3>
                    <div class="grid md:grid-cols-3 gap-6">
                        <!-- Quality -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Product Quality</label>
                            <div class="star-rating-input star-rating-small">
                                @for($i = 5; $i >= 1; $i--)
                                <input type="radio" name="quality_rating" id="quality{{ $i }}" value="{{ $i }}" {{ old('quality_rating') == $i ? 'checked' : '' }}>
                                <label for="quality{{ $i }}"><i class="fas fa-star"></i></label>
                                @endfor
                            </div>
                        </div>
                        
                        <!-- Value -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Value for Money</label>
                            <div class="star-rating-input star-rating-small">
                                @for($i = 5; $i >= 1; $i--)
                                <input type="radio" name="value_rating" id="value{{ $i }}" value="{{ $i }}" {{ old('value_rating') == $i ? 'checked' : '' }}>
                                <label for="value{{ $i }}"><i class="fas fa-star"></i></label>
                                @endfor
                            </div>
                        </div>
                        
                        <!-- Shipping -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Shipping Speed</label>
                            <div class="star-rating-input star-rating-small">
                                @for($i = 5; $i >= 1; $i--)
                                <input type="radio" name="shipping_rating" id="shipping{{ $i }}" value="{{ $i }}" {{ old('shipping_rating') == $i ? 'checked' : '' }}>
                                <label for="shipping{{ $i }}"><i class="fas fa-star"></i></label>
                                @endfor
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Image Upload -->
                <div class="border-t border-gray-100 pt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Add Photos (Optional)</label>
                    <p class="text-sm text-gray-500 mb-4">Share photos to help other buyers. You can upload up to 5 images.</p>
                    
                    <div class="flex flex-wrap gap-4" id="imagePreviewContainer">
                        <label class="w-24 h-24 border-2 border-dashed border-gray-300 rounded-xl flex flex-col items-center justify-center cursor-pointer hover:border-primary hover:bg-primary/5 transition">
                            <i class="fas fa-camera text-gray-400 text-xl mb-1"></i>
                            <span class="text-xs text-gray-500">Add Photo</span>
                            <input type="file" name="images[]" multiple accept="image/*" class="hidden" id="imageInput" onchange="previewImages(this)">
                        </label>
                    </div>
                    @error('images.*')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Submit Button -->
            <div class="p-6 bg-gray-50 border-t border-gray-100 flex justify-end gap-4">
                <a href="{{ route('buyer.reviews.index') }}" class="px-6 py-3 text-gray-600 hover:text-gray-800 font-medium transition">
                    Cancel
                </a>
                <button type="submit" class="px-8 py-3 bg-primary text-white rounded-xl font-bold hover:bg-indigo-700 transition flex items-center gap-2">
                    <i class="fas fa-paper-plane"></i>
                    Submit Review
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function previewImages(input) {
    const container = document.getElementById('imagePreviewContainer');
    const maxImages = 5;
    const existingPreviews = container.querySelectorAll('.image-preview').length;
    
    if (input.files) {
        const remainingSlots = maxImages - existingPreviews;
        const filesToAdd = Math.min(input.files.length, remainingSlots);
        
        for (let i = 0; i < filesToAdd; i++) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.createElement('div');
                preview.className = 'image-preview w-24 h-24 rounded-xl overflow-hidden relative';
                preview.innerHTML = `
                    <img src="${e.target.result}" class="w-full h-full object-cover">
                    <button type="button" class="remove-btn" onclick="removePreview(this)">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                container.insertBefore(preview, container.lastElementChild);
            }
            reader.readAsDataURL(input.files[i]);
        }
        
        if (existingPreviews + filesToAdd >= maxImages) {
            container.lastElementChild.style.display = 'none';
        }
    }
}

function removePreview(btn) {
    const container = document.getElementById('imagePreviewContainer');
    btn.parentElement.remove();
    container.lastElementChild.style.display = 'flex';
}
</script>
@endpush