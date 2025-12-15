@extends('layouts.buyer')

@section('title', 'Edit Review - ' . config('app.name'))

@push('styles')
<style>
    /* Enhanced Star Rating Styles */
    .star-rating-wrapper {
        margin-bottom: 1.5rem;
    }
    
    .star-rating-input {
        display: inline-flex;
        flex-direction: row-reverse;
        gap: 8px;
        padding: 8px 0;
    }
    
    .star-rating-input input[type="radio"] {
        display: none;
    }
    
    .star-rating-input label {
        cursor: pointer;
        font-size: 40px;
        color: #e5e7eb;
        transition: all 0.2s ease;
        line-height: 1;
        position: relative;
    }
    
    /* Hover effect */
    .star-rating-input label:hover,
    .star-rating-input label:hover ~ label {
        color: #fbbf24;
        transform: scale(1.1);
    }
    
    /* Selected state */
    .star-rating-input input[type="radio"]:checked ~ label {
        color: #f59e0b;
        text-shadow: 0 0 10px rgba(251, 191, 36, 0.5);
    }
    
    /* Active/clicking animation */
    .star-rating-input label:active {
        transform: scale(0.95);
    }
    
    /* Glow effect on hover */
    .star-rating-input label:hover::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 120%;
        height: 120%;
        background: radial-gradient(circle, rgba(251, 191, 36, 0.2) 0%, transparent 70%);
        border-radius: 50%;
        z-index: -1;
    }
    
    /* Small star rating variant */
    .star-rating-small {
        gap: 4px;
    }
    
    .star-rating-small label {
        font-size: 28px;
    }
    
    /* Rating label indicator */
    .rating-label {
        display: inline-block;
        margin-left: 16px;
        font-size: 16px;
        font-weight: 600;
        color: #6b7280;
        transition: all 0.2s ease;
        vertical-align: middle;
        min-width: 150px;
    }
    
    .star-rating-wrapper:hover .rating-label,
    .star-rating-wrapper.has-selection .rating-label {
        color: #f59e0b;
    }
    
    /* Accessibility focus styles */
    .star-rating-input input[type="radio"]:focus-visible ~ label {
        outline: 2px solid #6366f1;
        outline-offset: 4px;
        border-radius: 4px;
    }
    
    /* Animation for initial load */
    @keyframes starPop {
        0% { transform: scale(0.8); opacity: 0; }
        50% { transform: scale(1.1); }
        100% { transform: scale(1); opacity: 1; }
    }
    
    .star-rating-input label {
        animation: starPop 0.3s ease forwards;
    }
    
    .star-rating-input label:nth-child(2) { animation-delay: 0.05s; }
    .star-rating-input label:nth-child(4) { animation-delay: 0.1s; }
    .star-rating-input label:nth-child(6) { animation-delay: 0.15s; }
    .star-rating-input label:nth-child(8) { animation-delay: 0.2s; }
    .star-rating-input label:nth-child(10) { animation-delay: 0.25s; }
    
    /* Image preview styles */
    .image-preview {
        position: relative;
        width: 120px;
        height: 120px;
        border-radius: 12px;
        overflow: hidden;
        transition: all 0.2s ease;
    }
    
    .image-preview:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
    
    .image-preview.marked-for-removal {
        opacity: 0.4;
        filter: grayscale(100%);
    }
    
    .image-preview.marked-for-removal::after {
        content: 'Will be removed';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: rgba(239, 68, 68, 0.9);
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 10px;
        font-weight: bold;
        white-space: nowrap;
    }
    
    .image-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .image-preview .remove-btn {
        position: absolute;
        top: -8px;
        right: -8px;
        width: 28px;
        height: 28px;
        background: #ef4444;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 14px;
        opacity: 0;
        transition: opacity 0.2s, transform 0.2s;
        box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
        z-index: 10;
        border: none;
    }
    
    .image-preview:hover .remove-btn {
        opacity: 1;
    }
    
    .image-preview .remove-btn:hover {
        transform: scale(1.1);
        background: #dc2626;
    }
    
    /* Upload button styling */
    .upload-btn {
        width: 120px;
        height: 120px;
        border: 2px dashed #d1d5db;
        border-radius: 12px;
        transition: all 0.2s ease;
    }
    
    .upload-btn:hover {
        border-color: #6366f1;
        background-color: rgba(99, 102, 241, 0.05);
        transform: translateY(-2px);
    }
    
    .upload-btn:active {
        transform: scale(0.98);
    }
    
    /* Responsive adjustments */
    @media (max-width: 640px) {
        .star-rating-input label {
            font-size: 36px;
            gap: 6px;
        }
        
        .star-rating-small label {
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
            <a href="{{ route('buyer.reviews.index') }}" class="text-primary hover:text-indigo-700 font-medium inline-flex items-center mb-4 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i> Back to Reviews
            </a>
            <h1 class="text-3xl font-bold text-gray-800">Edit Review</h1>
            <p class="text-gray-600 mt-2">Update your review and help other buyers</p>
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
                    @if($review->listing && $review->listing->images->first())
                    <img src="{{ asset('storage/' . $review->listing->images->first()->path) }}" 
                         alt="{{ $review->listing->title }}"
                         class="w-full h-full object-cover">
                    @else
                    <div class="w-full h-full flex items-center justify-center bg-gray-100">
                        <i class="fas fa-image text-gray-300 text-2xl"></i>
                    </div>
                    @endif
                </div>
                <div class="flex-1">
                    <h2 class="text-lg font-bold text-gray-800 mb-1">{{ $review->listing->title ?? 'Product' }}</h2>
                    <p class="text-gray-600 text-sm mb-1">Order #{{ $review->order->order_number }}</p>
                    <p class="text-gray-600 text-sm mb-2">Originally reviewed {{ $review->created_at->format('F d, Y') }}</p>
                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">
                        <i class="fas fa-check-circle"></i>
                        Verified Purchase
                    </span>
                </div>
            </div>
        </div>

        <!-- Review Form -->
        <form action="{{ route('buyer.reviews.update', $review->id) }}" method="POST" enctype="multipart/form-data" 
              class="bg-white rounded-2xl shadow-sm overflow-hidden" id="reviewForm">
            @csrf
            @method('PUT')
            
            <div class="p-8 space-y-8">
                <!-- Overall Rating -->
                <div class="star-rating-wrapper">
                    <label class="block text-lg font-bold text-gray-800 mb-3">
                        Overall Rating <span class="text-red-500">*</span>
                    </label>
                    <div class="flex flex-col sm:flex-row sm:items-center">
                        <div class="star-rating-input" data-rating-type="overall">
                            <input type="radio" name="rating" id="star5" value="5" {{ old('rating', $review->rating) == 5 ? 'checked' : '' }} required>
                            <label for="star5" title="Excellent - 5 stars"><i class="fas fa-star"></i></label>
                            
                            <input type="radio" name="rating" id="star4" value="4" {{ old('rating', $review->rating) == 4 ? 'checked' : '' }}>
                            <label for="star4" title="Good - 4 stars"><i class="fas fa-star"></i></label>
                            
                            <input type="radio" name="rating" id="star3" value="3" {{ old('rating', $review->rating) == 3 ? 'checked' : '' }}>
                            <label for="star3" title="Average - 3 stars"><i class="fas fa-star"></i></label>
                            
                            <input type="radio" name="rating" id="star2" value="2" {{ old('rating', $review->rating) == 2 ? 'checked' : '' }}>
                            <label for="star2" title="Poor - 2 stars"><i class="fas fa-star"></i></label>
                            
                            <input type="radio" name="rating" id="star1" value="1" {{ old('rating', $review->rating) == 1 ? 'checked' : '' }}>
                            <label for="star1" title="Terrible - 1 star"><i class="fas fa-star"></i></label>
                        </div>
                        <span class="rating-label">Select a rating</span>
                    </div>
                    @error('rating')
                    <p class="text-red-500 text-sm mt-2"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                    @enderror
                </div>

                <!-- Review Title -->
                <div>
                    <label for="title" class="block text-sm font-semibold text-gray-700 mb-2">
                        Review Title (Optional)
                    </label>
                    <input type="text" id="title" name="title" 
                           value="{{ old('title', $review->title) }}"
                           placeholder="e.g., 'Great quality, exceeded expectations!'"
                           maxlength="100"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent transition text-gray-800 placeholder-gray-400">
                    <p class="text-xs text-gray-500 mt-1">Help others find your review with a catchy title</p>
                    @error('title')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Review Comment -->
                <div>
                    <label for="comment" class="block text-sm font-semibold text-gray-700 mb-2">
                        Your Review (Optional)
                    </label>
                    <textarea id="comment" name="comment" rows="6"
                              placeholder="Share your experience with this product. What did you like? How was the quality? Would you recommend it?"
                              class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent transition resize-none text-gray-800 placeholder-gray-400">{{ old('comment', $review->comment) }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">Be specific and honest to help other shoppers</p>
                    @error('comment')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Detailed Ratings -->
                <div class="border-t border-gray-200 pt-8">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">Rate Specific Aspects</h3>
                            <p class="text-sm text-gray-600 mt-1">Optional but helps others understand your experience better</p>
                        </div>
                    </div>
                    
                    <div class="grid md:grid-cols-3 gap-8">
                        <!-- Quality -->
                        <div class="star-rating-wrapper">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-award text-purple-500 mr-1"></i> Product Quality
                            </label>
                            <div class="star-rating-input star-rating-small" data-rating-type="quality">
                                @for($i = 5; $i >= 1; $i--)
                                <input type="radio" name="quality_rating" id="quality{{ $i }}" value="{{ $i }}" {{ old('quality_rating', $review->quality_rating) == $i ? 'checked' : '' }}>
                                <label for="quality{{ $i }}" title="{{ $i }} stars"><i class="fas fa-star"></i></label>
                                @endfor
                            </div>
                            <span class="rating-label text-sm"></span>
                        </div>
                        
                        <!-- Value -->
                        <div class="star-rating-wrapper">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-dollar-sign text-green-500 mr-1"></i> Value for Money
                            </label>
                            <div class="star-rating-input star-rating-small" data-rating-type="value">
                                @for($i = 5; $i >= 1; $i--)
                                <input type="radio" name="value_rating" id="value{{ $i }}" value="{{ $i }}" {{ old('value_rating', $review->value_rating) == $i ? 'checked' : '' }}>
                                <label for="value{{ $i }}" title="{{ $i }} stars"><i class="fas fa-star"></i></label>
                                @endfor
                            </div>
                            <span class="rating-label text-sm"></span>
                        </div>
                        
                        <!-- Shipping -->
                        <div class="star-rating-wrapper">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-shipping-fast text-blue-500 mr-1"></i> Shipping Speed
                            </label>
                            <div class="star-rating-input star-rating-small" data-rating-type="shipping">
                                @for($i = 5; $i >= 1; $i--)
                                <input type="radio" name="shipping_rating" id="shipping{{ $i }}" value="{{ $i }}" {{ old('shipping_rating', $review->shipping_rating) == $i ? 'checked' : '' }}>
                                <label for="shipping{{ $i }}" title="{{ $i }} stars"><i class="fas fa-star"></i></label>
                                @endfor
                            </div>
                            <span class="rating-label text-sm"></span>
                        </div>
                    </div>
                </div>

                <!-- Existing & New Images -->
                <div class="border-t border-gray-200 pt-8">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-camera mr-1"></i> Photos
                    </label>
                    <p class="text-sm text-gray-600 mb-4">
                        Update your review photos. Click the X to remove existing images or add new ones.
                    </p>
                    
                    <div class="flex flex-wrap gap-4" id="imagePreviewContainer">
                        <!-- Existing Images -->
                        @if($review->images)
                        @foreach($review->images as $image)
                        <div class="image-preview existing-image" data-image-path="{{ $image }}">
                            <img src="{{ asset('storage/' . $image) }}" alt="Review image" class="w-full h-full object-cover">
                            <button type="button" class="remove-btn" onclick="markForRemoval(this, '{{ $image }}')" title="Remove image">
                                <i class="fas fa-times"></i>
                            </button>
                            <input type="checkbox" name="remove_images[]" value="{{ $image }}" class="hidden remove-checkbox">
                        </div>
                        @endforeach
                        @endif
                        
                        <!-- Add New Image Button -->
                        <label class="upload-btn flex flex-col items-center justify-center cursor-pointer">
                            <i class="fas fa-camera text-gray-400 text-2xl mb-2"></i>
                            <span class="text-sm text-gray-500 font-medium">Add Photo</span>
                            <span class="text-xs text-gray-400 mt-1">Up to 5 total</span>
                            <input type="file" name="images[]" multiple accept="image/*" class="hidden" id="imageInput" onchange="previewImages(this)">
                        </label>
                    </div>
                    
                    @error('images.*')
                    <p class="text-red-500 text-sm mt-2"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tips Section -->
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-6">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-lightbulb text-blue-600"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-800 mb-2">Tips for Updating Your Review</h4>
                            <ul class="space-y-1 text-sm text-gray-700">
                                <li class="flex items-start gap-2">
                                    <i class="fas fa-check text-green-500 mt-0.5"></i>
                                    <span>Update if your opinion has changed after more use</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <i class="fas fa-check text-green-500 mt-0.5"></i>
                                    <span>Add new photos showing the product over time</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <i class="fas fa-check text-green-500 mt-0.5"></i>
                                    <span>Include any durability or longevity insights</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="p-6 bg-gray-50 border-t border-gray-200">
                <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                    <!-- Delete Review Button -->
                    <button type="button" 
                            onclick="confirmDelete()"
                            class="w-full sm:w-auto px-6 py-3 text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg font-medium transition flex items-center justify-center gap-2">
                        <i class="fas fa-trash"></i>
                        Delete Review
                    </button>
                    
                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                        <a href="{{ route('buyer.reviews.index') }}" 
                           class="w-full sm:w-auto px-6 py-3 text-center text-gray-600 hover:text-gray-800 font-medium transition">
                            <i class="fas fa-times mr-2"></i>Cancel
                        </a>
                        <button type="submit" 
                                class="w-full sm:w-auto px-8 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-bold hover:from-indigo-700 hover:to-purple-700 transition shadow-lg hover:shadow-xl flex items-center justify-center gap-2">
                            <i class="fas fa-save"></i>
                            Update Review
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <!-- Hidden Delete Form -->
        <form id="deleteForm" action="{{ route('buyer.reviews.destroy', $review->id) }}" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Rating labels configuration
    const ratingLabels = {
        1: 'Terrible',
        2: 'Poor',
        3: 'Average',
        4: 'Good',
        5: 'Excellent'
    };
    
    // Initialize all star rating containers
    const starContainers = document.querySelectorAll('.star-rating-input');
    
    starContainers.forEach(container => {
        const inputs = container.querySelectorAll('input[type="radio"]');
        const labels = container.querySelectorAll('label');
        const wrapper = container.closest('.star-rating-wrapper');
        const ratingText = wrapper?.querySelector('.rating-label');
        
        if (!ratingText) return;
        
        // Set initial text if there's a checked input
        const checkedInput = container.querySelector('input:checked');
        if (checkedInput) {
            const value = checkedInput.value;
            ratingText.textContent = `${ratingLabels[value]} (${value} star${value > 1 ? 's' : ''})`;
            wrapper.classList.add('has-selection');
        }
        
        // Update rating text on selection
        inputs.forEach(input => {
            input.addEventListener('change', function() {
                const value = this.value;
                ratingText.textContent = `${ratingLabels[value]} (${value} star${value > 1 ? 's' : ''})`;
                wrapper.classList.add('has-selection');
                
                // Add a little celebration animation
                const label = this.nextElementSibling;
                label.style.animation = 'none';
                setTimeout(() => {
                    label.style.animation = 'starPop 0.3s ease forwards';
                }, 10);
            });
        });
        
        // Hover preview
        labels.forEach((label) => {
            label.addEventListener('mouseenter', function() {
                const input = this.previousElementSibling;
                const value = input.value;
                ratingText.textContent = `${ratingLabels[value]} (${value} star${value > 1 ? 's' : ''})`;
            });
        });
        
        // Reset text on mouse leave
        container.addEventListener('mouseleave', function() {
            const checked = container.querySelector('input:checked');
            if (checked) {
                const value = checked.value;
                ratingText.textContent = `${ratingLabels[value]} (${value} star${value > 1 ? 's' : ''})`;
            } else {
                ratingText.textContent = 'Select a rating';
            }
        });
    });
});

// Image management
const maxImages = 5;
let imageCount = {{ $review->images ? count($review->images) : 0 }};

function markForRemoval(btn, imagePath) {
    const preview = btn.closest('.image-preview');
    const checkbox = preview.querySelector('.remove-checkbox');
    
    if (preview.classList.contains('marked-for-removal')) {
        // Unmark for removal
        preview.classList.remove('marked-for-removal');
        checkbox.checked = false;
        imageCount++;
    } else {
        // Mark for removal
        preview.classList.add('marked-for-removal');
        checkbox.checked = true;
        imageCount--;
    }
    
    updateUploadButton();
}

function previewImages(input) {
    const container = document.getElementById('imagePreviewContainer');
    const uploadBtn = container.querySelector('.upload-btn');
    
    if (!input.files || input.files.length === 0) return;
    
    const remainingSlots = maxImages - imageCount;
    const filesToAdd = Math.min(input.files.length, remainingSlots);
    
    for (let i = 0; i < filesToAdd; i++) {
        const file = input.files[i];
        
        // Validate file size (5MB max)
        if (file.size > 5 * 1024 * 1024) {
            alert('Image size should not exceed 5MB: ' + file.name);
            continue;
        }
        
        // Validate file type
        if (!file.type.startsWith('image/')) {
            alert('Please upload only image files: ' + file.name);
            continue;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.createElement('div');
            preview.className = 'image-preview new-image';
            preview.innerHTML = `
                <img src="${e.target.result}" alt="New preview">
                <button type="button" class="remove-btn" onclick="removeNewPreview(this)" title="Remove image">
                    <i class="fas fa-times"></i>
                </button>
            `;
            
            container.insertBefore(preview, uploadBtn);
            imageCount++;
            updateUploadButton();
        };
        reader.readAsDataURL(file);
    }
    
    // Clear the input
    input.value = '';
}

function removeNewPreview(btn) {
    btn.closest('.image-preview').remove();
    imageCount--;
    updateUploadButton();
}

function updateUploadButton() {
    const container = document.getElementById('imagePreviewContainer');
    const uploadBtn = container.querySelector('.upload-btn');
    
    if (imageCount >= maxImages) {
        uploadBtn.style.display = 'none';
    } else {
        uploadBtn.style.display = 'flex';
    }
}

// Delete confirmation
function confirmDelete() {
    if (confirm('Are you sure you want to delete this review? This action cannot be undone.')) {
        document.getElementById('deleteForm').submit();
    }
}

// Form validation
document.getElementById('reviewForm').addEventListener('submit', function(e) {
    const ratingInput = document.querySelector('input[name="rating"]:checked');
    
    if (!ratingInput) {
        e.preventDefault();
        alert('Please select an overall rating before submitting.');
        document.querySelector('.star-rating-input').scrollIntoView({ behavior: 'smooth', block: 'center' });
        return false;
    }
});

// Initialize upload button visibility
updateUploadButton();
</script>
@endpush