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
    
    .trust-badge-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
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
                        <div class="flex items-center gap-4 text-sm text-gray-500">
                            <span><i class="far fa-eye mr-1"></i> {{ rand(100, 1500) }} views</span>
                            <span><i class="far fa-heart mr-1"></i> {{ rand(10, 200) }} saved</span>
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
                            <span class="text-4xl font-bold text-primary">
                                ${{ number_format($listing->price, 2) }}
                            </span>
                            @php $originalPrice = $listing->price * 1.25; @endphp
                            <span class="text-lg text-gray-400 line-through mb-1">
                                ${{ number_format($originalPrice, 2) }}
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
                    
                    <!-- Quantity & Actions -->
                    @if($listing->stock > 0)
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
                                <span class="text-sm text-gray-500">{{ $listing->stock }} pieces available</span>
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
                    @else
                    <!-- Out of Stock Notice -->
                    <div class="bg-red-50 border border-red-200 rounded-2xl p-6 mb-6 text-center">
                        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-box-open text-red-500 text-2xl"></i>
                        </div>
                        <h4 class="font-bold text-red-700 mb-2">Currently Out of Stock</h4>
                        <p class="text-red-600 text-sm mb-4">This item is temporarily unavailable</p>
                        <button class="px-6 py-2 bg-red-500 text-white rounded-lg font-medium hover:bg-red-600 transition">
                            <i class="fas fa-bell mr-2"></i>Notify When Available
                        </button>
                    </div>
                    @endif
                    
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
                        
                        <!-- Reviews Tab - DYNAMIC -->
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
                                    
                                    <!-- Review Title -->
                                    @if($review->title)
                                    <h4 class="font-semibold text-gray-800 mb-2">{{ $review->title }}</h4>
                                    @endif
                                    
                                    <!-- Review Comment -->
                                    @if($review->comment)
                                    <p class="text-gray-600 leading-relaxed mb-4">{{ $review->comment }}</p>
                                    @endif
                                    
                                    <!-- Review Images -->
                                    @if($review->images && count($review->images) > 0)
                                    <div class="flex flex-wrap gap-2 mb-4">
                                        @foreach($review->images as $image)
                                        <div class="w-20 h-20 rounded-lg overflow-hidden review-image" onclick="openReviewImage('{{ asset('storage/' . $image) }}')">
                                            <img src="{{ asset('storage/' . $image) }}" alt="Review image" class="w-full h-full object-cover">
                                        </div>
                                        @endforeach
                                    </div>
                                    @endif
                                    
                                    <!-- Detailed Ratings -->
                                    @if($review->quality_rating || $review->value_rating || $review->shipping_rating)
                                    <div class="flex flex-wrap gap-4 mb-4 p-3 bg-gray-50 rounded-lg">
                                        @if($review->quality_rating)
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm text-gray-500">Quality:</span>
                                            <div class="flex">
                                                @for($i = 1; $i <= 5; $i++)
                                                <i class="fas fa-star text-xs {{ $i <= $review->quality_rating ? 'text-yellow-400' : 'text-gray-200' }}"></i>
                                                @endfor
                                            </div>
                                        </div>
                                        @endif
                                        @if($review->value_rating)
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm text-gray-500">Value:</span>
                                            <div class="flex">
                                                @for($i = 1; $i <= 5; $i++)
                                                <i class="fas fa-star text-xs {{ $i <= $review->value_rating ? 'text-yellow-400' : 'text-gray-200' }}"></i>
                                                @endfor
                                            </div>
                                        </div>
                                        @endif
                                        @if($review->shipping_rating)
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm text-gray-500">Shipping:</span>
                                            <div class="flex">
                                                @for($i = 1; $i <= 5; $i++)
                                                <i class="fas fa-star text-xs {{ $i <= $review->shipping_rating ? 'text-yellow-400' : 'text-gray-200' }}"></i>
                                                @endfor
                                            </div>
                                        </div>
                                        @endif
                                    </div>
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
                                    
                                    <!-- Vendor Response -->
                                    @if($review->vendor_response)
                                    <div class="mt-4 p-4 bg-blue-50 rounded-xl border-l-4 border-blue-500">
                                        <div class="flex items-center gap-2 mb-2">
                                            <i class="fas fa-store text-blue-600"></i>
                                            <span class="font-semibold text-blue-800">Vendor Response</span>
                                            @if($review->vendor_responded_at)
                                            <span class="text-sm text-blue-600">{{ $review->vendor_responded_at->diffForHumans() }}</span>
                                            @endif
                                        </div>
                                        <p class="text-blue-700">{{ $review->vendor_response }}</p>
                                    </div>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                            
                            <!-- Load More Reviews -->
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
                <!-- Vendor Card - DYNAMIC STATS -->
                <div class="bg-white rounded-2xl shadow-sm p-6">
                    <h4 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-store text-primary"></i>
                        Vendor Information
                    </h4>
                    
                    <div class="vendor-card mb-4">
                        <div class="flex items-center gap-4">
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
                    </div>
                    
                    <!-- Vendor Stats - DYNAMIC -->
                    <div class="grid grid-cols-3 gap-3 mb-4">
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
                    </div>
                    
                    <!-- Vendor Actions -->
                    <div class="space-y-2">
                        <button onclick="contactVendor()" class="w-full py-2.5 px-4 border-2 border-primary text-primary rounded-xl font-medium hover:bg-primary hover:text-white transition flex items-center justify-center gap-2">
                            <i class="fas fa-comment-dots"></i>
                            Chat with Vendor
                        </button>
                        <a href="#" class="w-full py-2.5 px-4 bg-gray-100 text-gray-700 rounded-xl font-medium hover:bg-gray-200 transition flex items-center justify-center gap-2">
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
                                <p class="font-bold text-primary">${{ number_format($relatedItem->price, 2) }}</p>
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
        <div class="bg-white rounded-2xl max-w-md w-full p-8 relative animate-scale-in">
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
                   class="block w-full py-3 bg-primary text-white rounded-xl font-bold text-center hover:bg-indigo-700 transition">
                    <i class="fas fa-sign-in-alt mr-2"></i>Sign In
                </a>
                <a href="{{ route('register') }}?redirect={{ urlencode(url()->current()) }}" 
                   class="block w-full py-3 border-2 border-primary text-primary rounded-xl font-bold text-center hover:bg-primary hover:text-white transition">
                    <i class="fas fa-user-plus mr-2"></i>Create Account
                </a>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const isAuthenticated = @json(auth()->check());
const csrfToken = '{{ csrf_token() }}';

// Quantity Controls
document.addEventListener('DOMContentLoaded', function() {
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
    
    // Add to Cart
    document.getElementById('addToCartBtn')?.addEventListener('click', function() {
        addToCart(this.dataset.listingId);
    });
    
    // Buy Now
    document.getElementById('buyNowBtn')?.addEventListener('click', function() {
        buyNow(this.dataset.listingId);
    });
    
    // Wishlist
    document.getElementById('wishlistBtn')?.addEventListener('click', function() {
        toggleWishlist(this.dataset.listingId, this);
    });
    
    // Check URL for tab parameter
    const urlParams = new URLSearchParams(window.location.search);
    const tab = urlParams.get('tab');
    if (tab) {
        switchTab(tab);
    }
});

// Image Gallery
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

// Review Image Lightbox
function openReviewImage(src) {
    document.getElementById('reviewLightboxImage').src = src;
    document.getElementById('reviewImageLightbox').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeReviewImageLightbox() {
    document.getElementById('reviewImageLightbox').classList.add('hidden');
    document.body.style.overflow = '';
}

// Tabs
function switchTab(tabName) {
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(content => content.classList.add('hidden'));
    
    document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');
    document.getElementById(`tab-${tabName}`).classList.remove('hidden');
    
    // Scroll to tabs if switching from link
    if (tabName === 'reviews') {
        document.querySelector('.tab-btn').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

// Cart Functions
async function addToCart(listingId) {
    if (!isAuthenticated) {
        showAuthModal();
        return;
    }
    
    const btn = document.getElementById('addToCartBtn');
    const qty = parseInt(document.getElementById('quantity')?.value) || 1;
    const originalHtml = btn.innerHTML;
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
    btn.disabled = true;
    
    try {
        const res = await fetch(`/buyer/cart/add/${listingId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ quantity: qty })
        });
        
        const data = await res.json();
        
        if (data.success) {
            btn.innerHTML = '<i class="fas fa-check"></i> Added!';
            btn.style.background = 'linear-gradient(135deg, #10b981 0%, #059669 100%)';
            showToast('Added to cart successfully!', 'success');
            
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

async function buyNow(listingId) {
    if (!isAuthenticated) {
        showAuthModal();
        return;
    }
    
    const qty = parseInt(document.getElementById('quantity')?.value) || 1;
    
    try {
        const res = await fetch(`/buyer/cart/add/${listingId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ quantity: qty })
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

// Vote on Review
async function voteReview(reviewId, voteType, btn) {
    if (!isAuthenticated) {
        showAuthModal();
        return;
    }
    
    try {
        const res = await fetch(`/buyer/reviews/${reviewId}/vote`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ vote: voteType })
        });
        
        const data = await res.json();
        
        if (data.success) {
            // Update counts in UI
            const reviewCard = btn.closest('.review-card');
            reviewCard.querySelector('.helpful-count').textContent = data.helpful_count;
            reviewCard.querySelector('.unhelpful-count').textContent = data.unhelpful_count;
            showToast('Vote recorded!', 'success');
        } else {
            showToast(data.message || 'Failed to vote', 'error');
        }
    } catch (error) {
        showToast('Failed to submit vote', 'error');
    }
}

// Share Functions
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

// Auth Modal
function showAuthModal() {
    document.getElementById('authModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeAuthModal() {
    document.getElementById('authModal').classList.add('hidden');
    document.body.style.overflow = '';
}

// Toast Notification
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

// Other Functions
function contactVendor() {
    if (!isAuthenticated) {
        showAuthModal();
        return;
    }
    showToast('Chat feature coming soon!', 'info');
}

// Keyboard shortcuts
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeLightbox();
        closeReviewImageLightbox();
        closeAuthModal();
    }
});
</script>
@endpush