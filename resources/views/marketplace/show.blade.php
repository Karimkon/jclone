@extends('layouts.app')

@section('title', $listing->title . ' - ' . config('app.name'))
@section('description', Str::limit($listing->description, 160))

@php
    // Get review statistics
    $reviewStats = [
        'average' => \App\Models\Review::getAverageRating($listing->id),
        'count' => \App\Models\Review::getReviewsCount($listing->id),
        'distribution' => \App\Models\Review::getRatingDistribution($listing->id),
    ];
    $totalDistribution = array_sum($reviewStats['distribution']) ?: 1;
    
    // Get reviews for this listing
    $reviews = \App\Models\Review::where('listing_id', $listing->id)
        ->where('status', 'approved')
        ->with(['user:id,name', 'votes'])
        ->orderBy('helpful_count', 'desc')
        ->orderBy('created_at', 'desc')
        ->take(5)
        ->get();
    
    // Check if current user can write a review
    $canReview = false;
    $pendingOrderItem = null;
    if (auth()->check()) {
        $pendingOrderItem = \App\Models\OrderItem::whereHas('order', function($q) use ($listing) {
            $q->where('buyer_id', auth()->id())
              ->where('status', 'delivered');
        })
        ->where('listing_id', $listing->id)
        ->whereDoesntHave('review', function($q) {
            $q->where('user_id', auth()->id());
        })
        ->first();
        
        $canReview = $pendingOrderItem !== null;
    }
    
   // Get vendor stats
$vendorStats = [
    'rating' => \App\Models\Review::getVendorAverageRating($listing->vendor_profile_id),
    'reviews' => \App\Models\Review::getVendorReviewsCount($listing->vendor_profile_id),
    'positive' => $listing->vendor ? ($listing->vendor->positive_rating_percentage ?? 98) : 98,
];

// Get vendor delivery performance
$deliveryPerformance = null;
$deliveryStats = [
    'score' => 50,
    'avg_time' => 0,
    'on_time_rate' => 0,
    'rating' => 3,
    'delivered_orders' => 0
];

if ($listing->vendor && method_exists($listing->vendor, 'performance')) {
    $deliveryPerformance = $listing->vendor->performance;
    if ($deliveryPerformance) {
        $deliveryStats['score'] = $deliveryPerformance->delivery_score ?? 50;
        $deliveryStats['avg_time'] = $deliveryPerformance->avg_delivery_time_days ?? 0;
        $deliveryStats['on_time_rate'] = $deliveryPerformance->on_time_delivery_rate ?? 0;
        $deliveryStats['delivered_orders'] = $deliveryPerformance->delivered_orders ?? 0;
        
        // Calculate star rating (1-5)
        if ($deliveryStats['delivered_orders'] >= 5) {
            if ($deliveryStats['score'] >= 90) $deliveryStats['rating'] = 5;
            elseif ($deliveryStats['score'] >= 80) $deliveryStats['rating'] = 4;
            elseif ($deliveryStats['score'] >= 70) $deliveryStats['rating'] = 3;
            elseif ($deliveryStats['score'] >= 60) $deliveryStats['rating'] = 2;
            else $deliveryStats['rating'] = 1;
        }
    }
}
     // Check if listing has variations
     $hasVariations = $listing->variants && $listing->variants->where('stock', '>', 0)->count() > 0;
    
    // Get all variants with stock > 0
    $variants = $listing->variants
        ->where('stock', '>', 0)
        ->where('is_active', true)
        ->map(function($variant) {
            return [
                'id' => $variant->id,
                'price' => $variant->price,
                'display_price' => $variant->display_price ?? $variant->price,
                'stock' => $variant->stock,
                'attributes' => $variant->attributes ?? []
            ];
        })->toArray();
    
    // Get available colors and sizes from variants WITH STOCK
    $availableColors = [];
    $availableSizes = [];
    
    if ($hasVariations) {
        foreach ($listing->variants->where('stock', '>', 0) as $variant) {
            $attrs = $variant->attributes ?? [];
            if (isset($attrs['color']) && $variant->stock > 0) {
                $availableColors[] = $attrs['color'];
            }
            if (isset($attrs['size']) && $variant->stock > 0) {
                $availableSizes[] = $attrs['size'];
            }
        }
        
        // Remove duplicates
        $availableColors = array_unique($availableColors);
        $availableSizes = array_unique($availableSizes);
    }
@endphp

@push('styles')
<style>
    /* Image Gallery */
    .main-image-container {
        position: relative;
        overflow: hidden;
        border-radius: 16px;
        background: #f8f9fa;
    }
    
    .main-image {
        width: 100%;
        height: 500px;
        object-fit: contain;
        transition: transform 0.5s ease;
    }
    
    .main-image-container:hover .main-image {
        transform: scale(1.02);
    }
    
    .thumbnail-btn {
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }
    
    .thumbnail-btn:hover,
    .thumbnail-btn.active {
        border-color: #4f46e5;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
    }
    
    /* Quantity Selector */
    .qty-btn {
        width: 44px;
        height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f3f4f6;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .qty-btn:hover {
        background: #e5e7eb;
    }
    
    .qty-btn:active {
        transform: scale(0.95);
    }
    
    .qty-input {
        width: 60px;
        text-align: center;
        font-weight: 600;
        border: none;
        background: transparent;
    }
    
    .qty-input:focus {
        outline: none;
    }
    
    /* Action Buttons */
    .btn-cart {
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .btn-cart::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s;
    }
    
    .btn-cart:hover::before {
        left: 100%;
    }
    
    .btn-cart:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(79, 70, 229, 0.35);
    }
    
    .btn-buy {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        transition: all 0.3s ease;
    }
    
    .btn-buy:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(16, 185, 129, 0.35);
    }
    
    .btn-wishlist {
        transition: all 0.3s ease;
    }
    
    .btn-wishlist:hover {
        background: #fef2f2;
        border-color: #ef4444;
        color: #ef4444;
    }
    
    .btn-wishlist.active {
        background: #ef4444;
        border-color: #ef4444;
        color: white;
    }
    
    /* Tabs */
    .tab-btn {
        position: relative;
        padding: 16px 24px;
        font-weight: 600;
        color: #6b7280;
        transition: all 0.3s ease;
    }
    
    .tab-btn::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 3px;
        background: #4f46e5;
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }
    
    .tab-btn:hover {
        color: #4f46e5;
    }
    
    .tab-btn.active {
        color: #4f46e5;
    }
    
    .tab-btn.active::after {
        transform: scaleX(1);
    }
    
    /* Rating Stars */
    .star-rating {
        display: inline-flex;
        gap: 2px;
    }
    
    .star-rating .star {
        color: #fbbf24;
        font-size: 16px;
    }
    
    .star-rating .star.empty {
        color: #d1d5db;
    }
    
    /* Trust Badges */
    .trust-badge {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        background: #f9fafb;
        border-radius: 12px;
        transition: all 0.3s ease;
    }
    
    .trust-badge:hover {
        background: #f3f4f6;
        transform: translateX(4px);
    }
    
    /* Vendor Card */
    .vendor-card {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-radius: 16px;
        padding: 20px;
        transition: all 0.3s ease;
    }
    
    .vendor-card:hover {
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    }
    
    /* Related Product Card */
    .related-card {
        transition: all 0.3s ease;
    }
    
    .related-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.1);
    }
    
    /* Breadcrumb */
    .breadcrumb-item {
        display: inline-flex;
        align-items: center;
        color: #6b7280;
        font-size: 14px;
        transition: color 0.2s;
    }
    
    .breadcrumb-item:hover {
        color: #4f46e5;
    }
    
    .breadcrumb-separator {
        margin: 0 8px;
        color: #d1d5db;
    }
    
    /* Stock Progress */
    .stock-progress {
        height: 6px;
        border-radius: 3px;
        background: #e5e7eb;
        overflow: hidden;
    }
    
    .stock-progress-bar {
        height: 100%;
        border-radius: 3px;
        transition: width 0.5s ease;
    }

    .modal-option-btn,
#modalColorOptions button,
#modalSizeOptions button {
    background: white !important;
    color: #374151 !important; /* gray-700 */
    border: 2px solid #d1d5db !important; /* gray-300 */
    font-weight: 500 !important;
    transition: all 0.2s ease !important;
}

.modal-option-btn:hover,
#modalColorOptions button:hover,
#modalSizeOptions button:hover {
    border-color: #6366f1 !important; /* primary/indigo */
    background: #eef2ff !important; /* indigo-50 */
    color: #4f46e5 !important;
}

/* Selected state */
.modal-option-btn.selected,
#modalColorOptions button.selected,
#modalSizeOptions button.selected,
button[data-option].selected {
    background: #6366f1 !important; /* primary/indigo-600 */
    color: white !important;
    border-color: #4f46e5 !important;
    font-weight: 600 !important;
}

/* Ensure proper contrast for selected state */
.option-btn.selected,
button[data-option].bg-primary {
    background: #6366f1 !important;
    color: white !important;
    border-color: #4f46e5 !important;
}

/* Variant info box */
#modalVariantInfo {
    background: linear-gradient(135deg, #eff6ff 0%, #e0e7ff 100%) !important;
    border-color: #c7d2fe !important;
}

#modalVariantInfo h4 {
    color: #1e293b !important;
}

#modalVariantInfo .text-gray-600 {
    color: #475569 !important;
}

#modalVariantPrice {
    color: #6366f1 !important;
}

#modalVariantStock {
    color: #475569 !important;
}

/* Confirm button - Make it visible and attractive */
#confirmModalOptionsBtn {
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%) !important;
    color: white !important;
    font-weight: 700 !important;
    border: none !important;
    box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3) !important;
}

#confirmModalOptionsBtn:hover:not(:disabled) {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%) !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4) !important;
}

#confirmModalOptionsBtn:disabled {
    background: #d1d5db !important;
    color: #9ca3af !important;
    cursor: not-allowed !important;
    box-shadow: none !important;
}

/* Cancel button */
button[onclick="closeOptionsModal()"] {
    background: white !important;
    color: #374151 !important;
    border: 2px solid #d1d5db !important;
    font-weight: 600 !important;
}

button[onclick="closeOptionsModal()"]:hover {
    background: #f9fafb !important;
    border-color: #9ca3af !important;
}

/* Close button (X) */
#optionsModal button[onclick="closeOptionsModal()"].w-8 {
    background: #f3f4f6 !important;
    color: #6b7280 !important;
}

#optionsModal button[onclick="closeOptionsModal()"].w-8:hover {
    background: #e5e7eb !important;
    color: #374151 !important;
}

/* Labels */
#optionsModal label {
    color: #1f2937 !important;
    font-weight: 600 !important;
}

/* Required asterisk */
#optionsModal label .text-red-500 {
    color: #ef4444 !important;
}

/* Modal title and description */
#optionsModal h3 {
    color: #111827 !important;
}

#optionsModal .text-gray-500 {
    color: #6b7280 !important;
}

/* Icon in modal header */
#optionsModal .fa-cogs {
    color: #6366f1 !important;
}

/* Selected options text */
#modalSelectedColorText,
#modalSelectedSizeText {
    color: #374151 !important;
    font-weight: 500 !important;
}

/* Ensure modal content is readable */
#optionsModal .bg-white {
    background: white !important;
}
    
    /* Toast Animation */
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    .toast-notification {
        animation: slideIn 0.3s ease forwards;
    }
    
    /* Image Lightbox */
    .lightbox {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.9);
        z-index: 100;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }
    
    .lightbox.active {
        opacity: 1;
        visibility: visible;
    }
    
    .lightbox img {
        max-width: 90%;
        max-height: 90vh;
        object-fit: contain;
    }
    
    /* Review Card */
    .review-card {
        transition: all 0.3s ease;
    }
    
    .review-card:hover {
        background: #f9fafb;
    }
    
    /* Review Images */
    .review-image {
        cursor: pointer;
        transition: transform 0.2s;
    }
    
    .review-image:hover {
        transform: scale(1.05);
    }
    
    /* Option Selection */
    .option-btn {
        transition: all 0.2s ease;
    }
    
    .option-btn.selected {
        border-color: #4f46e5 !important;
        background-color: rgba(79, 70, 229, 0.1) !important;
        color: #4f46e5;
    }
    
    /* Modal Animations */
    @keyframes scale-in {
        from { transform: scale(0.9); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }
    
    .animate-scale-in {
        animation: scale-in 0.3s ease forwards;
    }
    @media (max-width: 768px) {
    /* Prevent the main container from expanding */
    .container {
        padding-left: 12px !important;
        padding-right: 12px !important;
        width: 100% !important;
        max-width: 100vw !important;
        overflow-x: hidden !important;
    }

    /* Fix the main product image container */
    .main-image-container {
        height: auto !important;
        min-height: 300px;
    }

    .main-image {
        height: 300px !important; /* Forces height to stay reasonable on phones */
        width: 100% !important;
        object-fit: cover !important;
    }

    /* Force the grid to stay inside the screen */
    .grid {
        display: flex !important;
        flex-direction: column !important;
        width: 100% !important;
    }

    /* Fix the breadcrumb from pushing the screen width */
    nav.flex.items-center {
        flex-wrap: nowrap !important;
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch;
        white-space: nowrap;
    }
}
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Breadcrumb -->
    <div class="bg-white border-b">
        <div class="container mx-auto px-4 py-4">
            <nav class="flex items-center flex-wrap gap-1">
                <a href="{{ route('welcome') }}" class="breadcrumb-item">
                    <i class="fas fa-home mr-1"></i> Home
                </a>
                <span class="breadcrumb-separator"><i class="fas fa-chevron-right text-xs"></i></span>
                <a href="{{ route('marketplace.index') }}" class="breadcrumb-item">Marketplace</a>
                @if($listing->category)
                <span class="breadcrumb-separator"><i class="fas fa-chevron-right text-xs"></i></span>
                <a href="{{ route('marketplace.index', ['category' => $listing->category->id]) }}" class="breadcrumb-item">
                    {{ $listing->category->name }}
                </a>
                @endif
                <span class="breadcrumb-separator"><i class="fas fa-chevron-right text-xs"></i></span>
                <span class="text-gray-900 font-medium text-sm truncate max-w-xs">{{ $listing->title }}</span>
            </nav>
        </div>
    </div>
    
    <!-- Main Product Section -->
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="grid lg:grid-cols-2 gap-0">
                <!-- Left: Image Gallery -->
                <div class="p-6 lg:p-8 border-b lg:border-b-0 lg:border-r border-gray-100">
                    <!-- Main Image -->
                    <div class="main-image-container mb-4 cursor-zoom-in" onclick="openLightbox()">
                        @if($listing->images->first())
                        <img id="mainImage" 
                             src="{{ asset('storage/' . $listing->images->first()->path) }}" 
                             alt="{{ $listing->title }}" 
                             class="main-image">
                        @else
                        <div class="w-full h-[500px] flex items-center justify-center bg-gray-100">
                            <div class="text-center">
                                <i class="fas fa-image text-gray-300 text-6xl mb-4"></i>
                                <p class="text-gray-400">No image available</p>
                            </div>
                        </div>
                        @endif
                        
                        <!-- Badges -->
                        <div class="absolute top-4 left-4 flex flex-col gap-2">
                            @if($listing->origin == 'imported')
                            <span class="px-3 py-1.5 bg-blue-500 text-white text-sm font-semibold rounded-full shadow-lg">
                                <i class="fas fa-plane mr-1"></i> Imported
                            </span>
                            @else
                            <span class="px-3 py-1.5 bg-green-500 text-white text-sm font-semibold rounded-full shadow-lg">
                                <i class="fas fa-map-marker-alt mr-1"></i> Local
                            </span>
                            @endif
                            
                            @if($listing->condition == 'new')
                            <span class="px-3 py-1.5 bg-purple-500 text-white text-sm font-semibold rounded-full shadow-lg">
                                <i class="fas fa-sparkles mr-1"></i> New
                            </span>
                            @endif
                        </div>
                        
                        <!-- Zoom Hint -->
                        <div class="absolute bottom-4 right-4 bg-black/50 text-white px-3 py-1.5 rounded-full text-sm backdrop-blur-sm">
                            <i class="fas fa-search-plus mr-1"></i> Click to zoom
                        </div>
                    </div>
                    
                    <!-- Thumbnails -->
                    @if($listing->images->count() > 1)
                    <div class="flex gap-3 overflow-x-auto pb-2 scrollbar-thin">
                        @foreach($listing->images as $index => $image)
                        <button onclick="changeImage('{{ asset('storage/' . $image->path) }}', this)" 
                                class="thumbnail-btn flex-shrink-0 w-20 h-20 rounded-xl overflow-hidden {{ $index === 0 ? 'active' : '' }}">
                            <img src="{{ asset('storage/' . $image->path) }}" 
                                 alt="{{ $listing->title }}" 
                                 class="w-full h-full object-cover">
                        </button>
                        @endforeach
                    </div>
                    @endif
                    
                    <!-- Share & Actions Row -->
                    <div class="flex items-center justify-between mt-6 pt-6 border-t border-gray-100">
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-gray-500">Share:</span>
                            <button onclick="shareOn('facebook')" class="w-9 h-9 rounded-full bg-blue-100 text-blue-600 hover:bg-blue-600 hover:text-white transition flex items-center justify-center">
                                <i class="fab fa-facebook-f"></i>
                            </button>
                            <button onclick="shareOn('twitter')" class="w-9 h-9 rounded-full bg-sky-100 text-sky-500 hover:bg-sky-500 hover:text-white transition flex items-center justify-center">
                                <i class="fab fa-twitter"></i>
                            </button>
                            <button onclick="shareOn('whatsapp')" class="w-9 h-9 rounded-full bg-green-100 text-green-600 hover:bg-green-600 hover:text-white transition flex items-center justify-center">
                                <i class="fab fa-whatsapp"></i>
                            </button>
                            <button onclick="copyLink()" class="w-9 h-9 rounded-full bg-gray-100 text-gray-600 hover:bg-gray-600 hover:text-white transition flex items-center justify-center">
                                <i class="fas fa-link"></i>
                            </button>
                        </div>
                        {{-- Product Analytics Display --}}
<div class="flex items-center gap-4 text-sm text-gray-600 mb-4">
    <div class="flex items-center gap-1">
        <i class="fas fa-eye text-blue-500"></i>
        <span>{{ number_format($listing->view_count) }} views</span>
    </div>
    
    @if($listing->purchase_count > 0)
    <div class="flex items-center gap-1">
        <i class="fas fa-shopping-cart text-green-500"></i>
        <span>{{ number_format($listing->purchase_count) }} sold</span>
    </div>
    @endif
    
    @if($listing->wishlist_count > 0)
    <div class="flex items-center gap-1">
        <i class="fas fa-heart text-red-500"></i>
        <span>{{ number_format($listing->wishlist_count) }} wishlisted</span>
    </div>
    @endif
</div>
                    </div>
                </div>
                
                <!-- Right: Product Info -->
                <div class="p-6 lg:p-8">
                    <!-- Category & SKU -->
                    <div class="flex items-center justify-between mb-3">
                        @if($listing->category)
                        <a href="{{ route('marketplace.index', ['category' => $listing->category->id]) }}" 
                           class="text-sm text-primary font-medium hover:underline">
                            {{ $listing->category->name }}
                        </a>
                        @endif
                        @if($listing->sku)
                        <span class="text-xs text-gray-400">SKU: {{ $listing->sku }}</span>
                        @endif
                    </div>
                    
                    <!-- Title -->
                    <h1 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-4 leading-tight">
                        {{ $listing->title }}
                    </h1>

                    
<!-- Delivery Performance Badge -->
@if($deliveryStats['delivered_orders'] >= 10)
<div class="mb-4">
    <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-green-50 text-green-700 rounded-full text-sm font-medium border border-green-100">
        <i class="fas fa-bolt"></i>
        @if($deliveryStats['avg_time'] <= 3)
            Fast Delivery • {{ $deliveryStats['avg_time'] }} days avg.
        @elseif($deliveryStats['avg_time'] <= 7)
            Reliable Delivery • {{ $deliveryStats['avg_time'] }} days avg.
        @else
            Standard Delivery • {{ $deliveryStats['avg_time'] }} days avg.
        @endif
    </div>
</div>
@endif
                    
                    <!-- Rating & Reviews - DYNAMIC -->
                    <div class="flex items-center gap-4 mb-6">
                        <div class="flex items-center gap-2">
                            <div class="star-rating">
                                @for($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star star {{ $i <= round($reviewStats['average']) ? '' : 'empty' }}"></i>
                                @endfor
                            </div>
                            <span class="text-sm font-medium text-gray-700">{{ number_format($reviewStats['average'], 1) }}</span>
                        </div>
                        <span class="text-gray-300">|</span>
                        <a href="#reviews" onclick="switchTab('reviews')" class="text-sm text-gray-500 hover:text-primary">
                            {{ $reviewStats['count'] }} {{ Str::plural('Review', $reviewStats['count']) }}
                        </a>
                        <span class="text-gray-300">|</span>
                        <span class="text-sm text-green-600">{{ rand(50, 500) }}+ Sold</span>
                    </div>
                    
                    <!-- Price Section -->
                    <div class="bg-gradient-to-r from-orange-50 to-amber-50 rounded-2xl p-5 mb-6">
                        <div class="flex items-end gap-4">
                            <span id="productPrice" class="text-4xl font-bold text-primary">
                                UGX {{ number_format($listing->price, 0) }}
                            </span>
                            @php $originalPrice = $listing->price * 1.25; @endphp
                            <span id="productOriginalPrice" class="text-lg text-gray-400 line-through mb-1">
                                UGX {{ number_format($originalPrice, 0) }}
                            </span>
                            <span class="px-2 py-1 bg-red-500 text-white text-sm font-bold rounded-lg mb-1">
                                -20% OFF
                            </span>
                        </div>
                        <p class="text-sm text-gray-500 mt-2">
                            <i class="fas fa-clock mr-1"></i> Sale ends in <span class="font-semibold text-red-500">2d 14h 32m</span>
                        </p>
                    </div>
                    
                    <!-- Stock Status -->
                    <div class="mb-6">
                        <div id="stockStatus">
                            @if($listing->stock > 10)
                            <div class="flex items-center gap-2 text-green-600">
                                <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                                <span class="font-medium">In Stock</span>
                                <span class="text-gray-500 text-sm">({{ $listing->stock }} available)</span>
                            </div>
                            @elseif($listing->stock > 0)
                            <div class="space-y-2">
                                <div class="flex items-center gap-2 text-orange-600">
                                    <div class="w-3 h-3 bg-orange-500 rounded-full animate-pulse"></div>
                                    <span class="font-medium">Low Stock</span>
                                    <span class="text-gray-500 text-sm">- Only {{ $listing->stock }} left!</span>
                                </div>
                                <div class="stock-progress">
                                    <div class="stock-progress-bar bg-orange-500" style="width: {{ min($listing->stock * 10, 100) }}%"></div>
                                </div>
                            </div>
                            @else
                            <div class="flex items-center gap-2 text-red-600">
                                <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                                <span class="font-medium">Out of Stock</span>
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Product Highlights -->
                    @if($listing->weight_kg || $listing->origin)
                    <div class="grid grid-cols-2 gap-3 mb-6">
                        @if($listing->weight_kg)
                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl">
                            <i class="fas fa-weight-hanging text-gray-400"></i>
                            <div>
                                <p class="text-xs text-gray-500">Weight</p>
                                <p class="font-medium text-gray-700">{{ $listing->weight_kg }} kg</p>
                            </div>
                        </div>
                        @endif
                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl">
                            <i class="fas fa-{{ $listing->origin == 'imported' ? 'plane' : 'map-marker-alt' }} text-gray-400"></i>
                            <div>
                                <p class="text-xs text-gray-500">Origin</p>
                                <p class="font-medium text-gray-700 capitalize">{{ $listing->origin }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl">
                            <i class="fas fa-certificate text-gray-400"></i>
                            <div>
                                <p class="text-xs text-gray-500">Condition</p>
                                <p class="font-medium text-gray-700 capitalize">{{ $listing->condition }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl">
                            <i class="fas fa-shield-alt text-gray-400"></i>
                            <div>
                                <p class="text-xs text-gray-500">Warranty</p>
                                <p class="font-medium text-gray-700">1 Year</p>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    <!-- Product Options Section -->
                    <div id="productOptionsSection" class="mb-6 {{ $hasVariations ? '' : 'hidden' }}">
                        <h3 class="text-md font-bold text-gray-800 mb-3">Choose Options</h3>
                        
                        <!-- Color Options -->
                        @if(count($availableColors) > 0)
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Color</label>
                            <div class="flex flex-wrap gap-2">
                                @foreach($availableColors as $color)
                                <button type="button" 
                                        onclick="selectOption('color', '{{ $color }}')"
                                        class="option-btn px-4 py-2 border-2 border-gray-200 rounded-lg hover:border-primary transition text-gray-700"
                                        data-option="color"
                                        data-value="{{ $color }}">
                                    {{ $color }}
                                </button>
                                @endforeach
                            </div>
                        </div>
                        @endif
                        
                        <!-- Size Options -->
                        @if(count($availableSizes) > 0)
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Size</label>
                            <div class="flex flex-wrap gap-2">
                                @foreach($availableSizes as $size)
                                <button type="button" 
                                        onclick="selectOption('size', '{{ $size }}')"
                                        class="option-btn px-4 py-2 border-2 border-gray-200 rounded-lg hover:border-primary transition text-gray-700"
                                        data-option="size"
                                        data-value="{{ $size }}">
                                    {{ $size }}
                                </button>
                                @endforeach
                            </div>
                        </div>
                        @endif
                        
                        <!-- Selected Variant Info -->
                        <div id="selectedVariantInfo" class="hidden p-4 bg-blue-50 rounded-lg border border-blue-100 mb-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="font-bold text-gray-800">Selected Variant</h4>
                                    <div class="text-sm text-gray-600 mt-1">
                                        <p id="selectedOptionsText"></p>
                                        <p id="variantSku" class="text-xs text-gray-500"></p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-bold text-primary" id="variantPrice"></p>
                                    <p class="text-sm text-gray-600" id="variantStock"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Hidden inputs for cart submission -->
                    <input type="hidden" id="selectedColor" name="color" value="">
                    <input type="hidden" id="selectedSize" name="size" value="">
                    <input type="hidden" id="selectedVariantId" name="variant_id" value="">
                    
                    <!-- Quantity & Actions -->
                    <div class="space-y-4 mb-6">
                        <!-- Quantity Selector -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                            <div class="flex items-center gap-4">
                                <div class="flex items-center border-2 border-gray-200 rounded-xl overflow-hidden">
                                    <button type="button" id="qtyMinus" class="qty-btn">
                                        <i class="fas fa-minus text-gray-600"></i>
                                    </button>
                                    <input type="number" id="quantity" value="1" min="1" max="{{ $listing->stock }}" class="qty-input">
                                    <button type="button" id="qtyPlus" class="qty-btn">
                                        <i class="fas fa-plus text-gray-600"></i>
                                    </button>
                                </div>
                                <span id="stockInfo" class="text-sm text-gray-500">{{ $listing->stock }} pieces available</span>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <button id="addToCartBtn" 
                                    data-listing-id="{{ $listing->id }}"
                                    class="btn-cart w-full py-4 px-6 text-white font-bold rounded-xl flex items-center justify-center gap-2">
                                <i class="fas fa-shopping-cart"></i>
                                <span>Add to Cart</span>
                            </button>
                            
                            <button id="buyNowBtn"
                                    data-listing-id="{{ $listing->id }}"
                                    class="btn-buy w-full py-4 px-6 text-white font-bold rounded-xl flex items-center justify-center gap-2">
                                <i class="fas fa-bolt"></i>
                                <span>Buy Now</span>
                            </button>
                        </div>
                        
                        <!-- Wishlist Button -->
                        <button id="wishlistBtn"
                                data-listing-id="{{ $listing->id }}"
                                class="btn-wishlist w-full py-3 px-6 border-2 border-gray-200 text-gray-700 font-semibold rounded-xl flex items-center justify-center gap-2 hover:border-red-500 hover:text-red-500 transition">
                            <i class="far fa-heart"></i>
                            <span>Add to Wishlist</span>
                        </button>
                    </div>
                    
                    <!-- Trust Badges -->
                    <div class="space-y-3">
                        <div class="trust-badge">
                            <div class="trust-badge-icon bg-green-100 text-green-600">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <div>
                                <h5 class="font-semibold text-gray-800">Escrow Protection</h5>
                                <p class="text-sm text-gray-500">Payment secured until delivery confirmed</p>
                            </div>
                        </div>
                        <div class="trust-badge">
                            <div class="trust-badge-icon bg-blue-100 text-blue-600">
                                <i class="fas fa-truck"></i>
                            </div>
                            <div>
                                <h5 class="font-semibold text-gray-800">Fast Delivery</h5>
                                <p class="text-sm text-gray-500">Estimated 3-7 business days</p>
                            </div>
                        </div>
                        <div class="trust-badge">
                            <div class="trust-badge-icon bg-purple-100 text-purple-600">
                                <i class="fas fa-undo"></i>
                            </div>
                            <div>
                                <h5 class="font-semibold text-gray-800">Easy Returns</h5>
                                <p class="text-sm text-gray-500">30-day hassle-free returns</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Product Details & Sidebar -->
        <div class="grid lg:grid-cols-3 gap-8 mt-8">
            <!-- Left: Tabs Content -->
            <div class="lg:col-span-2">
                <!-- Tabs Navigation -->
                <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                    <div class="border-b border-gray-100">
                        <div class="flex overflow-x-auto">
                            <button onclick="switchTab('description')" class="tab-btn active" data-tab="description">
                                <i class="fas fa-align-left mr-2"></i>Description
                            </button>
                            <button onclick="switchTab('specs')" class="tab-btn" data-tab="specs">
                                <i class="fas fa-list-ul mr-2"></i>Specifications
                            </button>
                            <button onclick="switchTab('shipping')" class="tab-btn" data-tab="shipping">
                                <i class="fas fa-truck mr-2"></i>Shipping
                            </button>
                            <button onclick="switchTab('reviews')" class="tab-btn" data-tab="reviews">
                                <i class="fas fa-star mr-2"></i>Reviews 
                                @if($reviewStats['count'] > 0)
                                <span class="ml-1 px-2 py-0.5 bg-primary/10 text-primary text-xs rounded-full">{{ $reviewStats['count'] }}</span>
                                @endif
                            </button>
                        </div>
                    </div>
                    
                    <!-- Tab Contents -->
                    <div class="p-6 lg:p-8">
                        <!-- Description Tab -->
                        <div id="tab-description" class="tab-content">
                            <h3 class="text-xl font-bold text-gray-900 mb-4">Product Description</h3>
                            <div class="prose prose-gray max-w-none">
                                {!! nl2br(e($listing->description)) !!}
                            </div>
                            
                            <!-- Key Features -->
                            <div class="mt-8 p-6 bg-blue-50 rounded-2xl">
                                <h4 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                                    <i class="fas fa-check-circle text-blue-500"></i>
                                    Key Features
                                </h4>
                                <ul class="grid md:grid-cols-2 gap-3">
                                    <li class="flex items-start gap-2">
                                        <i class="fas fa-check text-green-500 mt-1"></i>
                                        <span class="text-gray-700">Premium quality materials</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <i class="fas fa-check text-green-500 mt-1"></i>
                                        <span class="text-gray-700">Authentic product guarantee</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <i class="fas fa-check text-green-500 mt-1"></i>
                                        <span class="text-gray-700">1 Year manufacturer warranty</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <i class="fas fa-check text-green-500 mt-1"></i>
                                        <span class="text-gray-700">Secure packaging for delivery</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        
                        <!-- Specifications Tab -->
                        <div id="tab-specs" class="tab-content hidden">
                            <h3 class="text-xl font-bold text-gray-900 mb-4">Product Specifications</h3>
                            <div class="overflow-hidden rounded-xl border border-gray-200">
                                <table class="w-full">
                                    <tbody class="divide-y divide-gray-200">
                                        @if($listing->attributes && is_array($listing->attributes))
                                            @foreach($listing->attributes as $key => $value)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-4 py-3 bg-gray-50 font-medium text-gray-700 w-1/3 capitalize">
                                                    {{ str_replace('_', ' ', $key) }}
                                                </td>
                                                <td class="px-4 py-3 text-gray-600">{{ $value }}</td>
                                            </tr>
                                            @endforeach
                                        @endif
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 bg-gray-50 font-medium text-gray-700">Condition</td>
                                            <td class="px-4 py-3 text-gray-600 capitalize">{{ $listing->condition }}</td>
                                        </tr>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 bg-gray-50 font-medium text-gray-700">Origin</td>
                                            <td class="px-4 py-3 text-gray-600 capitalize">{{ $listing->origin }}</td>
                                        </tr>
                                        @if($listing->weight_kg)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 bg-gray-50 font-medium text-gray-700">Weight</td>
                                            <td class="px-4 py-3 text-gray-600">{{ $listing->weight_kg }} kg</td>
                                        </tr>
                                        @endif
                                        @if($listing->sku)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 bg-gray-50 font-medium text-gray-700">SKU</td>
                                            <td class="px-4 py-3 text-gray-600">{{ $listing->sku }}</td>
                                        </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Shipping Tab -->
                        <div id="tab-shipping" class="tab-content hidden">
                            <h3 class="text-xl font-bold text-gray-900 mb-4">Shipping & Returns</h3>
                            
                            <!-- Delivery Time Estimate -->
    @if($deliveryStats['delivered_orders'] >= 5)
    <div class="mb-6 p-4 bg-blue-50 rounded-2xl border border-blue-100">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                <i class="fas fa-shipping-fast text-blue-600"></i>
            </div>
            <div>
                <h4 class="font-semibold text-gray-800">This Vendor's Delivery Stats</h4>
                <p class="text-sm text-gray-600">
                    Based on {{ $deliveryStats['delivered_orders'] }} orders: 
                    <span class="font-medium">Average {{ $deliveryStats['avg_time'] }} days</span> • 
                    <span class="font-medium {{ $deliveryStats['on_time_rate'] >= 90 ? 'text-green-600' : ($deliveryStats['on_time_rate'] >= 80 ? 'text-yellow-600' : 'text-red-600') }}">
                        {{ $deliveryStats['on_time_rate'] }}% on-time rate
                    </span>
                </p>
            </div>
        </div>
    </div>
    @endif
                            <div class="grid md:grid-cols-2 gap-6">
                                <!-- Shipping Info -->
                                <div class="p-5 bg-green-50 rounded-2xl">
                                    <h4 class="font-bold text-green-800 mb-4 flex items-center gap-2">
                                        <i class="fas fa-truck"></i>
                                        Shipping Information
                                    </h4>
                                    <ul class="space-y-3">
                                        <li class="flex items-start gap-3">
                                            <i class="fas fa-check-circle text-green-600 mt-0.5"></i>
                                            <span class="text-gray-700">Standard shipping: 3-7 business days</span>
                                        </li>
                                        <li class="flex items-start gap-3">
                                            <i class="fas fa-check-circle text-green-600 mt-0.5"></i>
                                            <span class="text-gray-700">Express shipping: 1-3 business days</span>
                                        </li>
                                        <li class="flex items-start gap-3">
                                            <i class="fas fa-check-circle text-green-600 mt-0.5"></i>
                                            <span class="text-gray-700">Free shipping on orders over $100</span>
                                        </li>
                                        <li class="flex items-start gap-3">
                                            <i class="fas fa-check-circle text-green-600 mt-0.5"></i>
                                            <span class="text-gray-700">Tracking number provided</span>
                                        </li>
                                    </ul>
                                </div>
                                
                                <!-- Returns Info -->
                                <div class="p-5 bg-blue-50 rounded-2xl">
                                    <h4 class="font-bold text-blue-800 mb-4 flex items-center gap-2">
                                        <i class="fas fa-undo"></i>
                                        Return Policy
                                    </h4>
                                    <ul class="space-y-3">
                                        <li class="flex items-start gap-3">
                                            <i class="fas fa-check-circle text-blue-600 mt-0.5"></i>
                                            <span class="text-gray-700">30-day return policy</span>
                                        </li>
                                        <li class="flex items-start gap-3">
                                            <i class="fas fa-check-circle text-blue-600 mt-0.5"></i>
                                            <span class="text-gray-700">Items must be in original condition</span>
                                        </li>
                                        <li class="flex items-start gap-3">
                                            <i class="fas fa-check-circle text-blue-600 mt-0.5"></i>
                                            <span class="text-gray-700">Free returns for defective items</span>
                                        </li>
                                        <li class="flex items-start gap-3">
                                            <i class="fas fa-check-circle text-blue-600 mt-0.5"></i>
                                            <span class="text-gray-700">Refund within 5-7 business days</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Reviews Tab -->
                        <div id="tab-reviews" class="tab-content hidden">
                            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                                <h3 class="text-xl font-bold text-gray-900">Customer Reviews</h3>
                                @if($canReview)
                                <a href="{{ route('buyer.reviews.create', ['order_item_id' => $pendingOrderItem->id]) }}" 
                                   class="px-5 py-2 bg-primary text-white rounded-lg font-medium hover:bg-indigo-700 transition">
                                    <i class="fas fa-pen mr-2"></i>Write a Review
                                </a>
                                @elseif(!auth()->check())
                                <button onclick="showAuthModal()" class="px-5 py-2 bg-primary text-white rounded-lg font-medium hover:bg-indigo-700 transition">
                                    <i class="fas fa-pen mr-2"></i>Write a Review
                                </button>
                                @endif
                            </div>
                            
                            <!-- Review Summary -->
                            <div class="grid md:grid-cols-3 gap-6 mb-8 p-6 bg-gray-50 rounded-2xl">
                                <div class="text-center">
                                    <div class="text-5xl font-bold text-primary mb-2">{{ number_format($reviewStats['average'], 1) }}</div>
                                    <div class="star-rating justify-center mb-2">
                                        @for($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star star {{ $i <= round($reviewStats['average']) ? '' : 'empty' }}"></i>
                                        @endfor
                                    </div>
                                    <p class="text-sm text-gray-500">Based on {{ $reviewStats['count'] }} {{ Str::plural('review', $reviewStats['count']) }}</p>
                                </div>
                                <div class="col-span-2">
                                    @foreach([5,4,3,2,1] as $stars)
                                    @php
                                        $count = $reviewStats['distribution'][$stars] ?? 0;
                                        $percentage = $totalDistribution > 0 ? round(($count / $totalDistribution) * 100) : 0;
                                    @endphp
                                    <div class="flex items-center gap-3 mb-2">
                                        <span class="text-sm w-12">{{ $stars }} star</span>
                                        <div class="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
                                            <div class="h-full bg-yellow-400 rounded-full transition-all duration-500" style="width: {{ $percentage }}%"></div>
                                        </div>
                                        <span class="text-sm text-gray-500 w-8">{{ $count }}</span>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            
                            <!-- Reviews List -->
                            @if($reviews->count() > 0)
                            <div class="space-y-6">
                                @foreach($reviews as $review)
                                <div class="review-card p-6 border border-gray-100 rounded-2xl">
                                    <div class="flex items-start justify-between mb-4">
                                        <div class="flex items-center gap-3">
                                            <!-- User Avatar -->
                                            <div class="w-12 h-12 bg-gradient-to-br from-primary to-purple-600 rounded-full flex items-center justify-center text-white font-bold text-lg">
                                                {{ strtoupper(substr($review->user->name ?? 'U', 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <span class="font-semibold text-gray-800">{{ $review->user->name ?? 'Anonymous' }}</span>
                                                    @if($review->is_verified_purchase)
                                                    <span class="px-2 py-0.5 bg-green-100 text-green-700 text-xs font-medium rounded-full">
                                                        <i class="fas fa-check-circle mr-1"></i>Verified
                                                    </span>
                                                    @endif
                                                </div>
                                                <div class="flex items-center gap-2 mt-1">
                                                    <div class="star-rating">
                                                        @for($i = 1; $i <= 5; $i++)
                                                        <i class="fas fa-star text-sm {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-200' }}"></i>
                                                        @endfor
                                                    </div>
                                                    <span class="text-sm text-gray-500">{{ $review->created_at->diffForHumans() }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Review Content -->
                                    @if($review->title)
                                    <h4 class="font-semibold text-gray-800 mb-2">{{ $review->title }}</h4>
                                    @endif
                                    
                                    @if($review->comment)
                                    <p class="text-gray-600 leading-relaxed mb-4">{{ $review->comment }}</p>
                                    @endif
                                    
                                    <!-- Helpful Votes -->
                                    <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                        <div class="flex items-center gap-4">
                                            <span class="text-sm text-gray-500">Was this helpful?</span>
                                            <button onclick="voteReview({{ $review->id }}, 'helpful', this)" 
                                                    class="flex items-center gap-1 text-sm text-gray-500 hover:text-green-600 transition">
                                                <i class="far fa-thumbs-up"></i>
                                                <span class="helpful-count">{{ $review->helpful_count }}</span>
                                            </button>
                                            <button onclick="voteReview({{ $review->id }}, 'unhelpful', this)" 
                                                    class="flex items-center gap-1 text-sm text-gray-500 hover:text-red-600 transition">
                                                <i class="far fa-thumbs-down"></i>
                                                <span class="unhelpful-count">{{ $review->unhelpful_count }}</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            
                            @if($reviewStats['count'] > 5)
                            <div class="mt-6 text-center">
                                <a href="{{ route('marketplace.show', $listing) }}?tab=reviews&page=all" 
                                   class="px-6 py-3 border-2 border-primary text-primary rounded-xl font-medium hover:bg-primary hover:text-white transition inline-block">
                                    View All {{ $reviewStats['count'] }} Reviews
                                </a>
                            </div>
                            @endif
                            
                            @else
                            <!-- No Reviews Yet -->
                            <div class="text-center py-12 bg-gray-50 rounded-2xl">
                                <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-comment-dots text-gray-300 text-3xl"></i>
                                </div>
                                <h4 class="text-lg font-semibold text-gray-700 mb-2">No Reviews Yet</h4>
                                <p class="text-gray-500 mb-4">Be the first to share your experience with this product!</p>
                                @if($canReview)
                                <a href="{{ route('buyer.reviews.create', ['order_item_id' => $pendingOrderItem->id]) }}" 
                                   class="inline-flex items-center gap-2 px-6 py-3 bg-primary text-white rounded-xl font-medium hover:bg-indigo-700 transition">
                                    <i class="fas fa-pen"></i>
                                    Write the First Review
                                </a>
                                @elseif(!auth()->check())
                                <button onclick="showAuthModal()" class="inline-flex items-center gap-2 px-6 py-3 bg-primary text-white rounded-xl font-medium hover:bg-indigo-700 transition">
                                    <i class="fas fa-pen"></i>
                                    Write the First Review
                                </button>
                                @else
                                <p class="text-sm text-gray-400">Purchase this product to leave a review</p>
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right: Sidebar -->
            <div class="space-y-6">
                <!-- Vendor Card -->
                <div class="bg-white rounded-2xl shadow-sm p-6">
                    <h4 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-store text-primary"></i>
                        Vendor Information
                    </h4>
                    
                   <div class="vendor-card mb-4">
    <div class="flex items-center gap-4 mb-3">
        <div class="w-14 h-14 bg-gradient-to-br from-primary to-purple-600 text-white rounded-xl flex items-center justify-center">
            <i class="fas fa-store text-xl"></i>
        </div>
        <div>
            <h5 class="font-bold text-gray-900">{{ $listing->vendor->business_name ?? 'Verified Vendor' }}</h5>
            <p class="text-sm text-gray-500">
                @if(($listing->vendor->vendor_type ?? '') == 'china_supplier')
                <i class="fas fa-globe mr-1"></i>International Supplier
                @else
                <i class="fas fa-map-marker-alt mr-1"></i>Local Vendor
                @endif
            </p>
        </div>
    </div>
    
   <!-- Delivery Performance Info -->
@if($deliveryPerformance && $deliveryPerformance->delivered_orders >= 5)
<div class="pt-3 border-t border-gray-100">
    <div class="space-y-2">
        <div class="flex items-center justify-between text-sm">
            <span class="text-gray-600">Delivery Score:</span>
            <span class="font-semibold {{ $deliveryStats['score'] >= 80 ? 'text-green-600' : ($deliveryStats['score'] >= 60 ? 'text-yellow-600' : 'text-red-600') }}">
                {{ $deliveryStats['score'] }}/100
            </span>
        </div>
        <div class="flex items-center justify-between text-sm">
            <span class="text-gray-600">On-Time Rate:</span>
            <span class="font-semibold">{{ $deliveryStats['on_time_rate'] }}%</span>
        </div>
        <div class="flex items-center justify-between text-sm">
            <span class="text-gray-600">Avg. Delivery:</span>
            <span class="font-semibold">{{ $deliveryStats['avg_time'] }} days</span>
        </div>
    </div>
</div>
@endif
</div> 
      <!-- Vendor Stats -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
    <div class="text-center p-3 bg-gray-50 rounded-xl">
        <div class="text-xl font-bold text-primary">{{ $vendorStats['positive'] }}%</div>
        <div class="text-xs text-gray-500">Positive</div>
    </div>
    <div class="text-center p-3 bg-gray-50 rounded-xl">
        <div class="text-xl font-bold text-primary">{{ $vendorStats['reviews'] > 0 ? $vendorStats['reviews'] : rand(100, 500) }}+</div>
        <div class="text-xs text-gray-500">Reviews</div>
    </div>
    <div class="text-center p-3 bg-gray-50 rounded-xl">
        <div class="text-xl font-bold text-primary">{{ number_format($vendorStats['rating'] ?: 4.5, 1) }}</div>
        <div class="text-xs text-gray-500">Rating</div>
    </div>
    <!-- Delivery Performance -->
    <div class="text-center p-3 bg-gray-50 rounded-xl" title="Based on {{ $deliveryStats['delivered_orders'] ?? 0 }} deliveries">
        <div class="flex items-center justify-center gap-1 mb-1">
            @for($i = 1; $i <= 5; $i++)
                <i class="fas fa-star text-xs {{ $i <= $deliveryStats['rating'] ? 'text-yellow-400' : 'text-gray-300' }}"></i>
            @endfor
        </div>
        <div class="text-sm text-gray-600">
            @if($deliveryStats['delivered_orders'] >= 5)
                {{ $deliveryStats['avg_time'] }} days avg.
            @else
                New vendor
            @endif
        </div>
    </div>
</div>
                    
                    <!-- Vendor Actions -->
                    <div class="space-y-2">
                        <button onclick="openCallbackModal()" 
                                class="w-full py-2.5 px-4 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-xl font-medium hover:shadow-lg transition flex items-center justify-center gap-2">
                            <i class="fas fa-phone-alt"></i>
                            Request Callback
                        </button>

                        <button onclick="openChatModal()" 
                                class="w-full py-2.5 px-4 border-2 border-primary text-primary rounded-xl font-medium hover:bg-primary hover:text-white transition flex items-center justify-center gap-2">
                            <i class="fas fa-comment-dots"></i>
                            Chat with Vendor
                        </button>
                        
                        <a href="#" 
                        class="w-full py-2.5 px-4 bg-gray-100 text-gray-700 rounded-xl font-medium hover:bg-gray-200 transition flex items-center justify-center gap-2">
                            <i class="fas fa-store"></i>
                            View Store
                        </a>
                    </div>
                </div>
                
                <!-- Related Products -->
                @if(isset($related) && $related->count() > 0)
                <div class="bg-white rounded-2xl shadow-sm p-6">
                    <h4 class="font-bold text-gray-900 mb-4">Related Products</h4>
                    
                    <div class="space-y-4">
                        @foreach($related as $relatedItem)
                        <a href="{{ route('marketplace.show', $relatedItem) }}" class="related-card flex gap-4 p-3 rounded-xl hover:bg-gray-50 transition">
                            <div class="w-20 h-20 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                                @if($relatedItem->images->first())
                                <img src="{{ asset('storage/' . $relatedItem->images->first()->path) }}" 
                                     alt="{{ $relatedItem->title }}"
                                     class="w-full h-full object-cover">
                                @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <i class="fas fa-image text-gray-300"></i>
                                </div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <h5 class="font-medium text-gray-800 line-clamp-2 text-sm mb-1">{{ $relatedItem->title }}</h5>
                                <div class="flex items-center gap-1 mb-1">
                                    @php
                                        $relatedRating = \App\Models\Review::getAverageRating($relatedItem->id);
                                    @endphp
                                    @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star text-xs {{ $i <= round($relatedRating) ? 'text-yellow-400' : 'text-gray-200' }}"></i>
                                    @endfor
                                </div>
                                <p class="font-bold text-primary">UGX {{ number_format($relatedItem->price, 2) }}</p>
                            </div>
                        </a>
                        @endforeach
                    </div>
                    
                    @if($listing->category)
                    <a href="{{ route('marketplace.index', ['category' => $listing->category->id]) }}" 
                       class="block mt-4 text-center text-primary font-medium hover:underline">
                        View All in {{ $listing->category->name }} →
                    </a>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Image Lightbox -->
<div id="lightbox" class="lightbox" onclick="closeLightbox()">
    <button class="absolute top-4 right-4 text-white text-3xl hover:text-gray-300 transition" onclick="closeLightbox()">
        <i class="fas fa-times"></i>
    </button>
    <img id="lightboxImage" src="" alt="Product Image">
</div>

<!-- Review Image Lightbox -->
<div id="reviewImageLightbox" class="fixed inset-0 z-[110] bg-black/90 flex items-center justify-center hidden" onclick="closeReviewImageLightbox()">
    <button class="absolute top-4 right-4 text-white text-3xl hover:text-gray-300 transition">
        <i class="fas fa-times"></i>
    </button>
    <img id="reviewLightboxImage" src="" alt="Review image" class="max-w-[90%] max-h-[90vh] object-contain">
</div>

<!-- Auth Modal -->
<div id="authModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeAuthModal()"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-8 relative animate-scale-in shadow-2xl">
            <button onclick="closeAuthModal()" class="absolute top-4 right-4 w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 hover:bg-gray-200 transition">
                <i class="fas fa-times"></i>
            </button>
            
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-lock text-primary text-2xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Sign In Required</h3>
                <p class="text-gray-500">Please sign in to continue shopping</p>
            </div>
            
            <div class="space-y-3">
                <a href="{{ route('login') }}?redirect={{ urlencode(url()->current()) }}" 
                   class="block w-full py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-bold text-center hover:from-indigo-700 hover:to-purple-700 transition shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                    <i class="fas fa-sign-in-alt mr-2"></i>Sign In
                </a>
                <a href="{{ route('register') }}?redirect={{ urlencode(url()->current()) }}" 
                   class="block w-full py-3 border-2 border-indigo-600 text-indigo-600 rounded-xl font-bold text-center hover:bg-indigo-600 hover:text-white transition">
                    <i class="fas fa-user-plus mr-2"></i>Create Account
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Options Confirmation Modal -->
<div id="optionsModal" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeOptionsModal()"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 relative animate-scale-in">
            <button onclick="closeOptionsModal()" class="absolute top-4 right-4 w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 hover:bg-gray-200 transition">
                <i class="fas fa-times"></i>
            </button>
            
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-cogs text-primary text-2xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Select Options</h3>
                <p class="text-gray-500">Please select product options before adding to cart</p>
            </div>
            
            <!-- Options Form -->
            <div id="optionsForm" class="space-y-6">
                <!-- Color Selection -->
                @if(count($availableColors) > 0)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        Color <span class="text-red-500">*</span>
                    </label>
                    <div class="flex flex-wrap gap-2" id="modalColorOptions">
                        @foreach($availableColors as $color)
                        <button type="button" 
                                onclick="selectModalOption('color', '{{ $color }}')"
                                class="modal-option-btn px-4 py-2.5 border-2 border-gray-200 rounded-lg hover:border-primary transition text-gray-700"
                                data-option="color"
                                data-value="{{ $color }}">
                            {{ $color }}
                        </button>
                        @endforeach
                    </div>
                </div>
                @endif
                
                <!-- Size Selection -->
                @if(count($availableSizes) > 0)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        Size <span class="text-red-500">*</span>
                    </label>
                    <div class="flex flex-wrap gap-2" id="modalSizeOptions">
                        @foreach($availableSizes as $size)
                        <button type="button" 
                                onclick="selectModalOption('size', '{{ $size }}')"
                                class="modal-option-btn px-4 py-2.5 border-2 border-gray-200 rounded-lg hover:border-primary transition text-gray-700"
                                data-option="size"
                                data-value="{{ $size }}">
                            {{ $size }}
                        </button>
                        @endforeach
                    </div>
                </div>
                @endif
                
                <!-- Selected Variant Info -->
                <div id="modalVariantInfo" class="hidden p-4 bg-blue-50 rounded-lg border border-blue-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="font-bold text-gray-800">Selected Variant</h4>
                            <div class="text-sm text-gray-600 mt-1 space-y-1">
                                <p id="modalSelectedColorText"></p>
                                <p id="modalSelectedSizeText"></p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-bold text-primary" id="modalVariantPrice"></p>
                            <p class="text-sm text-gray-600" id="modalVariantStock"></p>
                        </div>
                    </div>
                </div>
                
                <div class="pt-4 border-t border-gray-200">
                    <div class="flex gap-3">
                        <button type="button" 
                                onclick="closeOptionsModal()" 
                                class="flex-1 py-3 px-4 border-2 border-gray-300 text-gray-700 rounded-xl font-medium hover:bg-gray-50 transition">
                            Cancel
                        </button>
                        <button type="button" 
                                onclick="confirmModalOptions()"
                                id="confirmModalOptionsBtn"
                                disabled
                                class="flex-1 py-3 px-4 bg-primary text-white rounded-xl font-bold hover:bg-indigo-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                            Confirm Selection
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Request Callback Modal -->
<div id="callbackModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeCallbackModal()"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-8 relative animate-scale-in">
            <button onclick="closeCallbackModal()" class="absolute top-4 right-4 w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 hover:bg-gray-200 transition">
                <i class="fas fa-times"></i>
            </button>
            
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-phone text-green-600 text-2xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Request a Callback</h3>
                <p class="text-gray-500">Share your details and the vendor will call you back</p>
            </div>
            
            <form id="callbackForm" class="space-y-4">
                <!-- Callback form fields -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Full Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="name" 
                           id="callback_name"
                           value="{{ auth()->check() ? auth()->user()->name : '' }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent" 
                           placeholder="Enter your full name"
                           required>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Phone Number <span class="text-red-500">*</span>
                    </label>
                    <div class="flex gap-2">
                        <select class="px-3 py-3 border border-gray-300 rounded-lg bg-gray-50">
                            <option>+256</option>
                            <option>+254</option>
                            <option>+255</option>
                        </select>
                        <input type="tel" 
                               name="phone" 
                               id="callback_phone"
                               class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent" 
                               placeholder="700 000 000"
                               required>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">We'll use this number to contact you</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Message (Optional)
                    </label>
                    <textarea name="message" 
                              id="callback_message"
                              rows="3" 
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent resize-none" 
                              placeholder="Any specific questions or best time to call?"></textarea>
                </div>
                
                <div class="bg-blue-50 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-info-circle text-blue-600 mt-0.5"></i>
                        <div class="text-sm text-blue-800">
                            <p class="font-medium mb-1">What happens next?</p>
                            <ul class="space-y-1 text-xs">
                                <li>• Vendor receives your callback request</li>
                                <li>• They'll call you at the provided number</li>
                                <li>• Average response time: 30 minutes</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="flex gap-3 pt-2">
                    <button type="button" 
                            onclick="closeCallbackModal()" 
                            class="flex-1 py-3 px-4 border-2 border-gray-300 text-gray-700 rounded-xl font-medium hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <button type="submit" 
                            id="submitCallbackBtn"
                            class="flex-1 py-3 px-4 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-xl font-bold hover:shadow-lg transition flex items-center justify-center gap-2">
                        <i class="fas fa-phone"></i>
                        Request Callback
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('components.chat-modal', ['listing' => $listing])
@endsection

@push('scripts')
<script>
const isAuthenticated = @json(auth()->check());
const csrfToken = '{{ csrf_token() }}';
const listingId = {{ $listing->id }};
const hasVariations = @json($hasVariations);
const variants = @json($variants);
const availableColors = @json($availableColors);
const availableSizes = @json($availableSizes);

// State management
let selectedOptions = {
    color: null,
    size: null,
    variant: null
};
let pendingAction = null; // 'addToCart' or 'buyNow'
let pendingButton = null;

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Loaded - Variations:', hasVariations);
    console.log('Variants:', variants);
    console.log('Available colors:', availableColors);
    console.log('Available sizes:', availableSizes);
    
    // Quantity controls
    const qtyInput = document.getElementById('quantity');
    const qtyMinus = document.getElementById('qtyMinus');
    const qtyPlus = document.getElementById('qtyPlus');
    
    if (qtyMinus && qtyPlus && qtyInput) {
        qtyMinus.addEventListener('click', () => {
            let val = parseInt(qtyInput.value) || 1;
            if (val > 1) qtyInput.value = val - 1;
        });
        
        qtyPlus.addEventListener('click', () => {
            let val = parseInt(qtyInput.value) || 1;
            const max = parseInt(qtyInput.max) || 99;
            if (val < max) qtyInput.value = val + 1;
        });
    }
    
    // Add to Cart button
    const addToCartBtn = document.getElementById('addToCartBtn');
    if (addToCartBtn) {
        addToCartBtn.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Add to cart clicked');
            handleAddToCart(this);
        });
    }
    
    // Buy Now button
    const buyNowBtn = document.getElementById('buyNowBtn');
    if (buyNowBtn) {
        buyNowBtn.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Buy now clicked');
            handleBuyNow(this);
        });
    }
    
    // Wishlist button
    const wishlistBtn = document.getElementById('wishlistBtn');
    if (wishlistBtn) {
        wishlistBtn.addEventListener('click', function() {
            toggleWishlist(this.dataset.listingId, this);
        });
    }
    
    // Check URL for tab parameter
    const urlParams = new URLSearchParams(window.location.search);
    const tab = urlParams.get('tab');
    if (tab) {
        switchTab(tab);
    }
    
    // Initialize options section visibility
    const optionsSection = document.getElementById('productOptionsSection');
    if (hasVariations && optionsSection) {
        optionsSection.classList.remove('hidden');
        console.log('Options section shown');
    }
});

// Update the handleAddToCart and handleBuyNow functions:
function handleAddToCart(button) {
    if (!isAuthenticated) {
        showAuthModal();
        return;
    }
    
    pendingAction = 'addToCart';
    pendingButton = button;
    
    console.log('Variation check:', {
        hasVariations: hasVariations,
        availableColors: availableColors,
        availableSizes: availableSizes,
        selectedVariantId: document.getElementById('selectedVariantId')?.value
    });
    
    // Show modal if product has variations AND no variant is selected
    if (hasVariations) {
        const selectedVariantId = document.getElementById('selectedVariantId')?.value;
        console.log('Selected variant ID:', selectedVariantId);
        
        if (!selectedVariantId || selectedVariantId === '') {
            console.log('Opening options modal...');
            openOptionsModal();
            return;
        }
    }
    
    console.log('Adding directly...');
    addToCartDirect();
}

// Handle Buy Now with modal
function handleBuyNow(button) {
    if (!isAuthenticated) {
        showAuthModal();
        return;
    }
    
    pendingAction = 'buyNow';
    pendingButton = button;
    
    if (hasVariations) {
        const selectedVariantId = document.getElementById('selectedVariantId')?.value;
        if (!selectedVariantId || selectedVariantId === '') {
            openOptionsModal();
            return;
        }
    }
    
    buyNowDirect();
}
// Check if a concrete variant is selected; for variation products we require a variant_id
function isVariantSelected() {
    if (!hasVariations) return true;
    
    const variantId = document.getElementById('selectedVariantId')?.value;
    const color = document.getElementById('selectedColor')?.value;
    const size = document.getElementById('selectedSize')?.value;
    
    // If no colors/sizes available, then variant ID alone is enough
    if ((availableColors.length === 0 && availableSizes.length === 0) && variantId) {
        return true;
    }
    
    // If colors/sizes are available, check if all required options are selected
    let requiredSelected = true;
    
    if (availableColors.length > 0 && !color) {
        requiredSelected = false;
    }
    
    if (availableSizes.length > 0 && !size) {
        requiredSelected = false;
    }
    
    return requiredSelected && variantId;
}
// Open options modal
// openOptionsModal function:
function openOptionsModal() {
    console.log('openOptionsModal called');
    
    // Reset modal options first
    resetModalOptions();
    
    // Pre-select options if already chosen on main page
    const mainColor = document.getElementById('selectedColor').value;
    const mainSize = document.getElementById('selectedSize').value;
    
    if (mainColor) {
        selectedOptions.color = mainColor;
        const colorBtn = document.querySelector(`[data-option="color"][data-value="${mainColor}"]`);
        if (colorBtn) {
            colorBtn.classList.add('selected');
        }
    }
    
    if (mainSize) {
        selectedOptions.size = mainSize;
        const sizeBtn = document.querySelector(`[data-option="size"][data-value="${mainSize}"]`);
        if (sizeBtn) {
            sizeBtn.classList.add('selected');
        }
    }
    
    // Find matching variant with pre-selected options
    if (mainColor || mainSize) {
        findMatchingVariant();
    }
    
    // Show modal
    const modal = document.getElementById('optionsModal');
    if (modal) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        console.log('Modal shown');
    }
}

// Close options modal
function closeOptionsModal() {
    document.getElementById('optionsModal').classList.add('hidden');
    document.body.style.overflow = '';
}

// Reset modal options
function resetModalOptions() {
    // Clear selections
    selectedOptions.color = null;
    selectedOptions.size = null;
    selectedOptions.variant = null;
    
    // Reset UI
    document.querySelectorAll('.modal-option-btn').forEach(btn => {
        btn.classList.remove('selected');
    });
    
    // Hide variant info
    document.getElementById('modalVariantInfo').classList.add('hidden');
    
    // Disable confirm button
    document.getElementById('confirmModalOptionsBtn').disabled = true;
}

// Select option in modal
function selectModalOption(type, value) {
    // Update UI
    document.querySelectorAll(`[data-option="${type}"]`).forEach(btn => {
        btn.classList.remove('selected');
    });
    event.target.classList.add('selected');
    
    // Update selected options
    selectedOptions[type] = value;
    
    // Find matching variant
    findMatchingVariant();
}

// Find matching variant based on selected options
function findMatchingVariant() {
    const { color, size } = selectedOptions;
    
    if ((availableColors.length > 0 && !color) || (availableSizes.length > 0 && !size)) {
        // Required options not selected
        document.getElementById('modalVariantInfo').classList.add('hidden');
        document.getElementById('confirmModalOptionsBtn').disabled = true;
        return;
    }
    
    // Find variant that matches selected attributes
    const matchingVariant = variants.find(variant => {
        const variantAttrs = variant.attributes || {};
        
        let colorMatch = true;
        let sizeMatch = true;
        
        if (color && variantAttrs.color !== color) {
            colorMatch = false;
        }
        
        if (size && variantAttrs.size !== size) {
            sizeMatch = false;
        }
        
        return colorMatch && sizeMatch;
    });
    
    if (matchingVariant) {
        selectedOptions.variant = matchingVariant;
        updateModalVariantInfo(matchingVariant);
        document.getElementById('confirmModalOptionsBtn').disabled = false;
    } else {
        document.getElementById('modalVariantInfo').classList.add('hidden');
        document.getElementById('confirmModalOptionsBtn').disabled = true;
    }
}

// Update modal variant info
function updateModalVariantInfo(variant) {
    const modalVariantInfo = document.getElementById('modalVariantInfo');
    modalVariantInfo.classList.remove('hidden');
    
    // Update text
    const colorText = selectedOptions.color ? `Color: ${selectedOptions.color}` : '';
    const sizeText = selectedOptions.size ? `Size: ${selectedOptions.size}` : '';
    
    document.getElementById('modalSelectedColorText').textContent = colorText;
    document.getElementById('modalSelectedSizeText').textContent = sizeText;
    
    // Update price and stock
    document.getElementById('modalVariantPrice').textContent = `UGX ${variant.display_price.toLocaleString()}`;
    
    const stockText = variant.stock > 0 
        ? `${variant.stock} in stock` 
        : 'Out of stock';
    document.getElementById('modalVariantStock').textContent = stockText;
    
    // Enable/disable confirm button based on stock
    document.getElementById('confirmModalOptionsBtn').disabled = variant.stock <= 0;
}

// Confirm modal options
function confirmModalOptions() {
    if (!selectedOptions.variant) return;
    
    // Update main page with selected options
    updateMainPageOptions(selectedOptions.variant);
    
    // Close modal
    closeOptionsModal();
    
    // Execute pending action
    if (pendingAction === 'addToCart') {
        addToCartDirect();
    } else if (pendingAction === 'buyNow') {
        buyNowDirect();
    }
    
    // Reset pending state after action triggered
    pendingAction = null;
    pendingButton = null;
}

// Update main page with selected options
function updateMainPageOptions(variant) {
    // Update hidden inputs
    if (selectedOptions.color) {
        document.getElementById('selectedColor').value = selectedOptions.color;
    }
    if (selectedOptions.size) {
        document.getElementById('selectedSize').value = selectedOptions.size;
    }
    document.getElementById('selectedVariantId').value = variant.id;
    
    // Update UI buttons
    if (selectedOptions.color) {
        document.querySelectorAll(`[data-option="color"]`).forEach(btn => {
            btn.classList.remove('selected');
            if (btn.dataset.value === selectedOptions.color) {
                btn.classList.add('selected');
            }
        });
    }
    
    if (selectedOptions.size) {
        document.querySelectorAll(`[data-option="size"]`).forEach(btn => {
            btn.classList.remove('selected');
            if (btn.dataset.value === selectedOptions.size) {
                btn.classList.add('selected');
            }
        });
    }
    
    // Update price and stock display
    document.getElementById('productPrice').textContent = `UGX ${variant.display_price.toLocaleString()}`;
    document.getElementById('stockInfo').textContent = `${variant.stock} pieces available`;
    
    // Update stock status
    const stockStatusDiv = document.getElementById('stockStatus');
    if (variant.stock > 10) {
        stockStatusDiv.innerHTML = `
            <div class="flex items-center gap-2 text-green-600">
                <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                <span class="font-medium">In Stock</span>
                <span class="text-gray-500 text-sm">(${variant.stock} available)</span>
            </div>
        `;
    } else if (variant.stock > 0) {
        stockStatusDiv.innerHTML = `
            <div class="space-y-2">
                <div class="flex items-center gap-2 text-orange-600">
                    <div class="w-3 h-3 bg-orange-500 rounded-full animate-pulse"></div>
                    <span class="font-medium">Low Stock</span>
                    <span class="text-gray-500 text-sm">- Only ${variant.stock} left!</span>
                </div>
                <div class="stock-progress">
                    <div class="stock-progress-bar bg-orange-500" style="width: ${Math.min(variant.stock * 10, 100)}%"></div>
                </div>
            </div>
        `;
    } else {
        stockStatusDiv.innerHTML = `
            <div class="flex items-center gap-2 text-red-600">
                <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                <span class="font-medium">Out of Stock</span>
            </div>
        `;
    }
    
    // Show variant info
    const variantInfoDiv = document.getElementById('selectedVariantInfo');
    variantInfoDiv.classList.remove('hidden');
    
    const optionsText = [];
    if (selectedOptions.color) optionsText.push(`Color: ${selectedOptions.color}`);
    if (selectedOptions.size) optionsText.push(`Size: ${selectedOptions.size}`);
    
    document.getElementById('selectedOptionsText').textContent = optionsText.join(' | ');
    document.getElementById('variantPrice').textContent = `UGX ${variant.display_price.toLocaleString()}`;
    document.getElementById('variantStock').textContent = `${variant.stock} in stock`;
    
    // Update quantity max
    const qtyInput = document.getElementById('quantity');
    qtyInput.max = variant.stock;
    if (parseInt(qtyInput.value) > variant.stock) {
        qtyInput.value = variant.stock;
    }
}

// Select option from main page (if user selects without modal)
function selectOption(type, value) {
    // Update UI
    document.querySelectorAll(`[data-option="${type}"]`).forEach(btn => {
        btn.classList.remove('selected');
    });
    event.target.classList.add('selected');
    
    // Update hidden input
    if (type === 'color') {
        document.getElementById('selectedColor').value = value;
        selectedOptions.color = value;
    } else if (type === 'size') {
        document.getElementById('selectedSize').value = value;
        selectedOptions.size = value;
    }
    
    // Find matching variant
    findMatchingVariantForMainPage();
}

// Find matching variant for main page
function findMatchingVariantForMainPage() {
    const color = document.getElementById('selectedColor').value;
    const size = document.getElementById('selectedSize').value;
    
    // Find matching variant
    const matchingVariant = variants.find(variant => {
        const variantAttrs = variant.attributes || {};
        
        let colorMatch = true;
        let sizeMatch = true;
        
        if (color && variantAttrs.color !== color) {
            colorMatch = false;
        }
        
        if (size && variantAttrs.size !== size) {
            sizeMatch = false;
        }
        
        return colorMatch && sizeMatch;
    });
    
    if (matchingVariant) {
        selectedOptions.variant = matchingVariant;
        document.getElementById('selectedVariantId').value = matchingVariant.id;
        updateMainPageOptions(matchingVariant);
    } else {
        // Hide variant info if no match
        document.getElementById('selectedVariantInfo').classList.add('hidden');
        document.getElementById('selectedVariantId').value = '';
    }
}

// Direct add to cart (without modal)
async function addToCartDirect() {
    const btn = pendingButton || document.getElementById('addToCartBtn');
    const qty = parseInt(document.getElementById('quantity')?.value) || 1;
    const originalHtml = btn.innerHTML;
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
    btn.disabled = true;
    
    try {
        const cartData = {
            quantity: qty,
            variant_id: document.getElementById('selectedVariantId')?.value || null,
            color: document.getElementById('selectedColor')?.value || null,
            size: document.getElementById('selectedSize')?.value || null
        };
        
        const res = await fetch(`/buyer/cart/add/${listingId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify(cartData)
        });
        
        const data = await res.json();
        
        if (data.success) {
            btn.innerHTML = '<i class="fas fa-check"></i> Added!';
            btn.style.background = 'linear-gradient(135deg, #10b981 0%, #059669 100%)';
            showToast('Added to cart successfully!', 'success');
            
            if (data.cart_count) {
                updateCartCount(data.cart_count);
            }
            
            setTimeout(() => {
                btn.innerHTML = originalHtml;
                btn.style.background = '';
                btn.disabled = false;
            }, 2000);
        } else {
            throw new Error(data.message);
        }
    } catch (error) {
        btn.innerHTML = originalHtml;
        btn.disabled = false;
        showToast(error.message || 'Failed to add to cart', 'error');
    }
}

// Direct buy now (without modal)
async function buyNowDirect() {
    const qty = parseInt(document.getElementById('quantity')?.value) || 1;
    
    try {
        const cartData = {
            quantity: qty,
            variant_id: document.getElementById('selectedVariantId')?.value || null,
            color: document.getElementById('selectedColor')?.value || null,
            size: document.getElementById('selectedSize')?.value || null
        };
        
        const res = await fetch(`/buyer/cart/add/${listingId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify(cartData)
        });
        
        const data = await res.json();
        
        if (data.success) {
            window.location.href = '/buyer/orders/checkout';
        } else {
            throw new Error(data.message);
        }
    } catch (error) {
        showToast(error.message || 'Failed to proceed', 'error');
    }
}

// Toggle wishlist
async function toggleWishlist(listingId, btn) {
    if (!isAuthenticated) {
        showAuthModal();
        return;
    }
    
    try {
        const res = await fetch(`/buyer/wishlist/toggle/${listingId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        });
        
        const data = await res.json();
        
        if (data.success) {
            if (data.in_wishlist) {
                btn.classList.add('active');
                btn.innerHTML = '<i class="fas fa-heart"></i><span>In Wishlist</span>';
            } else {
                btn.classList.remove('active');
                btn.innerHTML = '<i class="far fa-heart"></i><span>Add to Wishlist</span>';
            }
            showToast(data.message || 'Wishlist updated!', 'success');
        }
    } catch (error) {
        showToast('Failed to update wishlist', 'error');
    }
}

// Update cart count
function updateCartCount(count) {
    const cartCountElements = document.querySelectorAll('.cart-count');
    cartCountElements.forEach(element => {
        element.textContent = count;
        if (count > 0) {
            element.classList.remove('hidden');
            element.classList.add('animate-pulse');
            setTimeout(() => element.classList.remove('animate-pulse'), 1000);
        }
    });
}

// Toast notification
function showToast(message, type = 'info') {
    const colors = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        info: 'bg-blue-500'
    };
    
    const icons = {
        success: 'fa-check-circle',
        error: 'fa-times-circle',
        info: 'fa-info-circle'
    };
    
    const toast = document.createElement('div');
    toast.className = `toast-notification fixed top-4 right-4 ${colors[type]} text-white px-6 py-4 rounded-xl shadow-lg z-50 flex items-center gap-3`;
    toast.innerHTML = `
        <i class="fas ${icons[type]}"></i>
        <span>${message}</span>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Image gallery functions
function changeImage(src, btn) {
    document.getElementById('mainImage').src = src;
    document.querySelectorAll('.thumbnail-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
}

function openLightbox() {
    const img = document.getElementById('mainImage').src;
    document.getElementById('lightboxImage').src = img;
    document.getElementById('lightbox').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeLightbox() {
    document.getElementById('lightbox').classList.remove('active');
    document.body.style.overflow = '';
}

// Tab switching
function switchTab(tabName) {
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(content => content.classList.add('hidden'));
    
    document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');
    document.getElementById(`tab-${tabName}`).classList.remove('hidden');
}

// Auth modal
function showAuthModal() {
    document.getElementById('authModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeAuthModal() {
    document.getElementById('authModal').classList.add('hidden');
    document.body.style.overflow = '';
}

// Callback modal functions
function openCallbackModal() {
    document.getElementById('callbackModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeCallbackModal() {
    document.getElementById('callbackModal').classList.add('hidden');
    document.body.style.overflow = '';
    document.getElementById('callbackForm').reset();
}

// Handle callback form submission
document.getElementById('callbackForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const btn = document.getElementById('submitCallbackBtn');
    const originalHtml = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Sending...';
    
    const formData = {
        name: document.getElementById('callback_name').value,
        phone: document.getElementById('callback_phone').value,
        message: document.getElementById('callback_message').value,
    };
    
    try {
        const response = await fetch(`/buyer/listings/${listingId}/callback`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(formData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            closeCallbackModal();
            showToast('Callback request sent successfully! The vendor will contact you soon.', 'success');
        } else {
            throw new Error(data.message || 'Failed to send callback request');
        }
    } catch (error) {
        showToast(error.message || 'Failed to send callback request', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    }
});

// Keyboard shortcuts
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeLightbox();
        closeAuthModal();
        closeOptionsModal();
        closeCallbackModal();
    }
});

// Share functions
function shareOn(platform) {
    const url = encodeURIComponent(window.location.href);
    const title = encodeURIComponent('{{ $listing->title }}');
    
    const urls = {
        facebook: `https://www.facebook.com/sharer/sharer.php?u=${url}`,
        twitter: `https://twitter.com/intent/tweet?url=${url}&text=${title}`,
        whatsapp: `https://wa.me/?text=${title}%20${url}`
    };
    
    window.open(urls[platform], '_blank', 'width=600,height=400');
}

function copyLink() {
    navigator.clipboard.writeText(window.location.href);
    showToast('Link copied to clipboard!', 'success');
}
</script>
@endpush