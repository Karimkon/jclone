@extends('layouts.buyer')

@section('title', 'Write Review - ' . config('app.name'))

@push('styles')
<style>
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .animate-fade-in {
        animation: fadeIn 0.3s ease-out;
    }

    /* Star Rating System - Clickable Stars */
    .star-rating-wrapper {
        margin-bottom: 1.5rem;
    }

    .star-rating-container {
        display: inline-flex;
        gap: 8px;
        align-items: center;
    }

    .star-button {
        background: none;
        border: none;
        cursor: pointer;
        padding: 0;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
    }

    .star-button i {
        font-size: 40px;
        color: #e5e7eb;
        transition: all 0.2s ease;
    }

    .star-button:hover i,
    .star-button.hover-active i {
        color: #fbbf24;
        transform: scale(1.15);
    }

    .star-button.active i {
        color: #f59e0b;
        text-shadow: 0 0 10px rgba(251, 191, 36, 0.5);
    }

    .star-button:active {
        transform: scale(0.95);
    }

    /* Glow effect */
    .star-button:hover::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 50px;
        height: 50px;
        background: radial-gradient(circle, rgba(251, 191, 36, 0.3) 0%, transparent 70%);
        border-radius: 50%;
        z-index: -1;
        animation: pulse 0.6s ease-out;
    }

    @keyframes pulse {
        0% { opacity: 0; transform: translate(-50%, -50%) scale(0.5); }
        50% { opacity: 1; }
        100% { opacity: 0; transform: translate(-50%, -50%) scale(1.5); }
    }

    /* Small stars for detailed ratings */
    .star-small i {
        font-size: 28px;
    }

    /* Rating label */
    .rating-label {
        font-size: 16px;
        font-weight: 600;
        color: #6b7280;
        margin-left: 12px;
        min-width: 120px;
        transition: color 0.2s ease;
    }

    .rating-label.active {
        color: #f59e0b;
    }

    /* Image preview styles */
    .image-preview {
        position: relative;
        width: 120px;
        height: 120px;
        border-radius: 12px;
        overflow: hidden;
        border: 2px solid #e5e7eb;
        transition: all 0.2s ease;
    }

    .image-preview:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        border-color: #6366f1;
    }

    .image-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .remove-btn {
        position: absolute;
        top: -10px;
        right: -10px;
        width: 32px;
        height: 32px;
        background: #ef4444;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        opacity: 0;
        transition: all 0.2s ease;
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
        z-index: 10;
        border: none;
    }

    .image-preview:hover .remove-btn {
        opacity: 1;
    }

    .remove-btn:hover {
        background: #dc2626;
        transform: scale(1.1);
    }

    /* Upload button */
    .upload-btn {
        width: 120px;
        height: 120px;
        border: 2px dashed #d1d5db;
        border-radius: 12px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        background: white;
    }

    .upload-btn:hover {
        border-color: #6366f1;
        background: rgba(99, 102, 241, 0.05);
        transform: translateY(-2px);
    }

    /* Responsive adjustments */
    @media (max-width: 640px) {
        .star-button i {
            font-size: 36px;
        }
        
        .star-small i {
            font-size: 24px;
        }
        
        .rating-label {
            display: block;
            margin-left: 0;
            margin-top: 8px;
            font-size: 14px;
        }
        
        .image-preview, .upload-btn {
            width: 100px;
            height: 100px;
        }
    }
</style>
@endpush

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('buyer.reviews.index') }}" class="text-indigo-600 hover:text-indigo-700 font-medium inline-flex items-center mb-4 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i> Back to Reviews
            </a>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Write a Review</h1>
            <p class="text-gray-600">Share your experience to help other buyers make informed decisions</p>
        </div>

        @if(session('error'))
        <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6 rounded-r-lg animate-fade-in">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle text-red-400 mr-3"></i>
                <p class="text-red-700">{{ session('error') }}</p>
            </div>
        </div>
        @endif

        <!-- Product Info Card -->
        <div class="bg-gradient-to-r from-indigo-50 to-purple-50 rounded-2xl shadow-sm p-6 mb-6 border border-indigo-100">
            <div class="flex gap-4">
                <div class="w-24 h-24 bg-white rounded-xl overflow-hidden flex-shrink-0 shadow-md">
                    @if($orderItem->listing && $orderItem->listing->images->first())
                    <img src="{{ asset('storage/' . $orderItem->listing->images->first()->path) }}" 
                         alt="{{ $orderItem->title }}"
                         class="w-full h-full object-cover">
                    @else
                    <div class="w-full h-full flex items-center justify-center bg-gray-100">
                        <i class="fas fa-image text-gray-300 text-2xl"></i>
                    </div>
                    @endif
                </div>
                <div class="flex-1">
                    <h2 class="text-lg font-bold text-gray-900 mb-1">{{ $orderItem->title }}</h2>
                    <p class="text-gray-600 text-sm mb-1">Order #{{ $orderItem->order->order_number }}</p>
                    <p class="text-gray-600 text-sm mb-2">Delivered {{ $orderItem->order->updated_at->format('F d, Y') }}</p>
                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">
                        <i class="fas fa-check-circle"></i>
                        Verified Purchase
                    </span>
                </div>
            </div>
        </div>

        <!-- Review Form -->
        <form action="{{ route('buyer.reviews.store') }}" method="POST" enctype="multipart/form-data" 
              class="bg-white rounded-2xl shadow-sm overflow-hidden" id="reviewForm">
            @csrf
            <input type="hidden" name="order_item_id" value="{{ $orderItem->id }}">
            
            <!-- Hidden inputs for ratings (will be updated by JavaScript) -->
            <input type="hidden" name="rating" id="rating_input" value="{{ old('rating') }}">
            <input type="hidden" name="quality_rating" id="quality_rating_input" value="{{ old('quality_rating') }}">
            <input type="hidden" name="value_rating" id="value_rating_input" value="{{ old('value_rating') }}">
            <input type="hidden" name="shipping_rating" id="shipping_rating_input" value="{{ old('shipping_rating') }}">
            
            <div class="p-8 space-y-8">
                <!-- Overall Rating -->
                <div class="star-rating-wrapper">
                    <label class="block text-lg font-bold text-gray-900 mb-4">
                        Overall Rating <span class="text-red-500">*</span>
                    </label>
                    <div class="flex flex-col sm:flex-row sm:items-center">
                        <div class="star-rating-container" id="mainRating">
                            <button type="button" class="star-button" data-rating="1">
                                <i class="fas fa-star"></i>
                            </button>
                            <button type="button" class="star-button" data-rating="2">
                                <i class="fas fa-star"></i>
                            </button>
                            <button type="button" class="star-button" data-rating="3">
                                <i class="fas fa-star"></i>
                            </button>
                            <button type="button" class="star-button" data-rating="4">
                                <i class="fas fa-star"></i>
                            </button>
                            <button type="button" class="star-button" data-rating="5">
                                <i class="fas fa-star"></i>
                            </button>
                        </div>
                        <span class="rating-label" id="mainRatingLabel">Select a rating</span>
                    </div>
                    @error('rating')
                    <p class="text-red-500 text-sm mt-2"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                    @enderror
                </div>

                <!-- Review Title -->
                <div>
                    <label for="title" class="block text-sm font-semibold text-gray-700 mb-2">
                        Review Title <span class="text-gray-400 text-xs">(Optional)</span>
                    </label>
                    <input type="text" id="title" name="title" 
                           value="{{ old('title') }}"
                           placeholder="e.g., 'Great quality, exceeded expectations!'"
                           maxlength="100"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                    <p class="text-xs text-gray-500 mt-1">Help others find your review with a catchy title</p>
                    @error('title')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Review Comment -->
                <div>
                    <label for="comment" class="block text-sm font-semibold text-gray-700 mb-2">
                        Your Review <span class="text-gray-400 text-xs">(Optional)</span>
                    </label>
                    <textarea id="comment" name="comment" rows="6"
                              placeholder="Share your experience with this product. What did you like? How was the quality? Would you recommend it?"
                              class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition resize-none">{{ old('comment') }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">Be specific and honest to help other shoppers</p>
                    @error('comment')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Detailed Ratings -->
                <div class="border-t border-gray-200 pt-8">
                    <div class="mb-6">
                        <h3 class="text-lg font-bold text-gray-900">Rate Specific Aspects</h3>
                        <p class="text-sm text-gray-600 mt-1">Optional but helps others understand your experience better</p>
                    </div>
                    
                    <div class="grid md:grid-cols-3 gap-6">
                        <!-- Quality -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                <i class="fas fa-award text-purple-500 mr-1"></i> Product Quality
                            </label>
                            <div class="flex items-center">
                                <div class="star-rating-container" id="qualityRating">
                                    <button type="button" class="star-button star-small" data-rating="1">
                                        <i class="fas fa-star"></i>
                                    </button>
                                    <button type="button" class="star-button star-small" data-rating="2">
                                        <i class="fas fa-star"></i>
                                    </button>
                                    <button type="button" class="star-button star-small" data-rating="3">
                                        <i class="fas fa-star"></i>
                                    </button>
                                    <button type="button" class="star-button star-small" data-rating="4">
                                        <i class="fas fa-star"></i>
                                    </button>
                                    <button type="button" class="star-button star-small" data-rating="5">
                                        <i class="fas fa-star"></i>
                                    </button>
                                </div>
                            </div>
                            <span class="rating-label text-sm mt-2 block" id="qualityRatingLabel"></span>
                        </div>
                        
                        <!-- Value -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                <i class="fas fa-dollar-sign text-green-500 mr-1"></i> Value for Money
                            </label>
                            <div class="flex items-center">
                                <div class="star-rating-container" id="valueRating">
                                    <button type="button" class="star-button star-small" data-rating="1">
                                        <i class="fas fa-star"></i>
                                    </button>
                                    <button type="button" class="star-button star-small" data-rating="2">
                                        <i class="fas fa-star"></i>
                                    </button>
                                    <button type="button" class="star-button star-small" data-rating="3">
                                        <i class="fas fa-star"></i>
                                    </button>
                                    <button type="button" class="star-button star-small" data-rating="4">
                                        <i class="fas fa-star"></i>
                                    </button>
                                    <button type="button" class="star-button star-small" data-rating="5">
                                        <i class="fas fa-star"></i>
                                    </button>
                                </div>
                            </div>
                            <span class="rating-label text-sm mt-2 block" id="valueRatingLabel"></span>
                        </div>
                        
                        <!-- Shipping -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                <i class="fas fa-shipping-fast text-blue-500 mr-1"></i> Shipping Speed
                            </label>
                            <div class="flex items-center">
                                <div class="star-rating-container" id="shippingRating">
                                    <button type="button" class="star-button star-small" data-rating="1">
                                        <i class="fas fa-star"></i>
                                    </button>
                                    <button type="button" class="star-button star-small" data-rating="2">
                                        <i class="fas fa-star"></i>
                                    </button>
                                    <button type="button" class="star-button star-small" data-rating="3">
                                        <i class="fas fa-star"></i>
                                    </button>
                                    <button type="button" class="star-button star-small" data-rating="4">
                                        <i class="fas fa-star"></i>
                                    </button>
                                    <button type="button" class="star-button star-small" data-rating="5">
                                        <i class="fas fa-star"></i>
                                    </button>
                                </div>
                            </div>
                            <span class="rating-label text-sm mt-2 block" id="shippingRatingLabel"></span>
                        </div>
                    </div>
                </div>

                <!-- Image Upload -->
                <div class="border-t border-gray-200 pt-8">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-camera mr-1"></i> Add Photos
                    </label>
                    <p class="text-sm text-gray-600 mb-4">
                        Show others what you received. Upload up to 5 images (JPG, PNG, max 5MB each)
                    </p>
                    
                    <div class="flex flex-wrap gap-4" id="imagePreviewContainer">
                        <label class="upload-btn">
                            <i class="fas fa-camera text-gray-400 text-2xl mb-2"></i>
                            <span class="text-sm text-gray-500 font-medium">Add Photo</span>
                            <span class="text-xs text-gray-400 mt-1">Up to 5 images</span>
                            <input type="file" name="images[]" multiple accept="image/*" class="hidden" id="imageInput">
                        </label>
                    </div>
                    
                    @error('images.*')
                    <p class="text-red-500 text-sm mt-2"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tips -->
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-6">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-lightbulb text-blue-600"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-900 mb-2">Tips for Writing a Great Review</h4>
                            <ul class="space-y-1 text-sm text-gray-700">
                                <li class="flex items-start gap-2">
                                    <i class="fas fa-check text-green-500 mt-0.5"></i>
                                    <span>Be specific about what you liked or didn't like</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <i class="fas fa-check text-green-500 mt-0.5"></i>
                                    <span>Mention if the product matched its description</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <i class="fas fa-check text-green-500 mt-0.5"></i>
                                    <span>Include details about quality, size, and value</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <i class="fas fa-check text-green-500 mt-0.5"></i>
                                    <span>Add photos to show the actual product</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="p-6 bg-gray-50 border-t border-gray-200 flex flex-col sm:flex-row justify-between items-center gap-4">
                <a href="{{ route('buyer.reviews.index') }}" 
                   class="w-full sm:w-auto px-6 py-3 text-gray-600 hover:text-gray-900 font-medium transition text-center">
                    <i class="fas fa-times mr-2"></i>Cancel
                </a>
                <button type="submit" 
                        class="w-full sm:w-auto px-8 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-bold hover:from-indigo-700 hover:to-purple-700 transition shadow-lg hover:shadow-xl flex items-center justify-center gap-2">
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
document.addEventListener('DOMContentLoaded', function() {
    // Rating labels
    const ratingLabels = {
        1: 'Terrible',
        2: 'Poor',
        3: 'Average',
        4: 'Good',
        5: 'Excellent'
    };

    // Star rating functionality
    function initStarRating(containerId, labelId, inputId) {
        const container = document.getElementById(containerId);
        const label = document.getElementById(labelId);
        const hiddenInput = document.getElementById(inputId);
        const stars = container.querySelectorAll('.star-button');
        let selectedRating = hiddenInput ? parseInt(hiddenInput.value) || 0 : 0;

        // Initialize from old value
        if (selectedRating > 0) {
            updateStars(selectedRating);
            updateLabel(selectedRating);
        }

        stars.forEach((star) => {
            // Click to select
            star.addEventListener('click', () => {
                selectedRating = parseInt(star.dataset.rating);
                updateStars(selectedRating);
                updateLabel(selectedRating);
                if (hiddenInput) {
                    hiddenInput.value = selectedRating;
                }
            });

            // Hover effect
            star.addEventListener('mouseenter', () => {
                const rating = parseInt(star.dataset.rating);
                highlightStars(rating);
                if (label) {
                    label.textContent = `${ratingLabels[rating]} (${rating} star${rating > 1 ? 's' : ''})`;
                    label.classList.add('active');
                }
            });
        });

        // Reset on mouse leave
        container.addEventListener('mouseleave', () => {
            updateStars(selectedRating);
            if (selectedRating === 0 && label) {
                label.textContent = 'Select a rating';
                label.classList.remove('active');
            } else if (label) {
                updateLabel(selectedRating);
            }
        });

        function highlightStars(rating) {
            stars.forEach((star) => {
                const starRating = parseInt(star.dataset.rating);
                if (starRating <= rating) {
                    star.classList.add('hover-active');
                } else {
                    star.classList.remove('hover-active');
                }
            });
        }

        function updateStars(rating) {
            stars.forEach((star) => {
                star.classList.remove('hover-active');
                const starRating = parseInt(star.dataset.rating);
                if (starRating <= rating) {
                    star.classList.add('active');
                } else {
                    star.classList.remove('active');
                }
            });
        }

        function updateLabel(rating) {
            if (label && rating > 0) {
                label.textContent = `${ratingLabels[rating]} (${rating} star${rating > 1 ? 's' : ''})`;
                label.classList.add('active');
            }
        }
    }

    // Initialize all rating systems
    initStarRating('mainRating', 'mainRatingLabel', 'rating_input');
    initStarRating('qualityRating', 'qualityRatingLabel', 'quality_rating_input');
    initStarRating('valueRating', 'valueRatingLabel', 'value_rating_input');
    initStarRating('shippingRating', 'shippingRatingLabel', 'shipping_rating_input');

    // Image upload functionality
    const imageInput = document.getElementById('imageInput');
    const imageContainer = document.getElementById('imagePreviewContainer');
    const uploadBtn = imageContainer.querySelector('.upload-btn');
    const maxImages = 5;
    let imageCount = 0;

    imageInput.addEventListener('change', function(e) {
        const files = Array.from(e.target.files);
        const remainingSlots = maxImages - imageCount;
        const filesToAdd = files.slice(0, remainingSlots);

        filesToAdd.forEach(file => {
            if (file.size > 5 * 1024 * 1024) {
                alert(`Image too large: ${file.name} (max 5MB)`);
                return;
            }

            if (!file.type.startsWith('image/')) {
                alert(`Invalid file type: ${file.name}`);
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.createElement('div');
                preview.className = 'image-preview animate-fade-in';
                preview.innerHTML = `
                    <img src="${e.target.result}" alt="Preview">
                    <button type="button" class="remove-btn">
                        <i class="fas fa-times"></i>
                    </button>
                `;

                preview.querySelector('.remove-btn').addEventListener('click', function() {
                    preview.remove();
                    imageCount--;
                    checkImageLimit();
                });

                imageContainer.insertBefore(preview, uploadBtn);
                imageCount++;
                checkImageLimit();
            };
            reader.readAsDataURL(file);
        });

        e.target.value = '';
    });

    function checkImageLimit() {
        uploadBtn.style.display = imageCount >= maxImages ? 'none' : 'flex';
    }

    // Form validation
    document.getElementById('reviewForm').addEventListener('submit', function(e) {
        const rating = document.getElementById('rating_input').value;
        
        if (!rating || rating === '0') {
            e.preventDefault();
            alert('Please select an overall rating before submitting.');
            document.getElementById('mainRating').scrollIntoView({ behavior: 'smooth', block: 'center' });
            return false;
        }
    });
});
</script>
@endpush