@extends('layouts.app')

@section('title', 'Marketplace - ' . config('app.name'))

@section('content')

<style>
/* Variation Modal Styles for Marketplace Index */

/* Modal overlay */
#variationModal {
    backdrop-filter: blur(8px);
}

/* Modal option buttons - Default state */
#variationModal button[data-option] {
    background: white !important;
    color: #374151 !important; /* gray-700 */
    border: 2px solid #d1d5db !important; /* gray-300 */
    font-weight: 500 !important;
    transition: all 0.2s ease !important;
}

#variationModal button[data-option]:hover {
    border-color: #6366f1 !important; /* primary/indigo */
    background: #eef2ff !important; /* indigo-50 */
    color: #4f46e5 !important;
}

/* Selected state */
#variationModal button[data-option].selected,
#variationModal button[data-option].border-primary {
    background: #6366f1 !important; /* primary/indigo-600 */
    color: white !important;
    border-color: #4f46e5 !important;
    font-weight: 600 !important;
}

/* Variant info box */
#variationModal #selectedVariantInfo {
    background: linear-gradient(135deg, #eff6ff 0%, #e0e7ff 100%) !important;
    border-color: #c7d2fe !important;
}

#variationModal #selectedVariantInfo h4 {
    color: #1e293b !important;
}

#variationModal #selectedVariantInfo .text-gray-600,
#variationModal #selectedOptionsText {
    color: #475569 !important;
    font-weight: 500 !important;
}

#variationModal #variantPrice {
    color: #6366f1 !important;
    font-weight: 700 !important;
}

#variationModal #variantStock {
    color: #475569 !important;
}

/* Add to Cart button in modal */
#variationModal #addWithVariationBtn {
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%) !important;
    color: white !important;
    font-weight: 700 !important;
    border: none !important;
    box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3) !important;
}

#variationModal #addWithVariationBtn:hover:not(:disabled) {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%) !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4) !important;
}

#variationModal #addWithVariationBtn:disabled {
    background: #d1d5db !important;
    color: #9ca3af !important;
    cursor: not-allowed !important;
    box-shadow: none !important;
}

/* Cancel button */
#variationModal button[onclick="closeVariationModal()"] {
    background: white !important;
    color: #374151 !important;
    border: 2px solid #d1d5db !important;
    font-weight: 600 !important;
}

#variationModal button[onclick="closeVariationModal()"]:hover {
    background: #f9fafb !important;
    border-color: #9ca3af !important;
}

/* Close button (X) at top */
#variationModal .bg-gray-100 {
    background: #f3f4f6 !important;
    color: #6b7280 !important;
}

#variationModal .bg-gray-100:hover {
    background: #e5e7eb !important;
    color: #374151 !important;
}

/* Labels */
#variationModal label {
    color: #1f2937 !important;
    font-weight: 600 !important;
}

/* Required asterisk */
#variationModal .text-red-500 {
    color: #ef4444 !important;
}

/* Modal title */
#variationModal h3 {
    color: #111827 !important;
    font-weight: 700 !important;
}

/* Modal background */
#variationModal .bg-white {
    background: white !important;
}

/* Ensure text is visible */
#variationModal .text-gray-900 {
    color: #111827 !important;
}

#variationModal .text-gray-700 {
    color: #374151 !important;
}

#variationModal .text-gray-600 {
    color: #475569 !important;
}

#variationModal .text-gray-800 {
    color: #1f2937 !important;
}

/* Primary color text */
#variationModal .text-primary {
    color: #6366f1 !important;
}

/* Auth Modal Specific Styles */
#authModal .bg-white {
    background: white !important;
}

#authModal a {
    text-decoration: none !important;
}

#authModal .bg-gradient-to-r {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%) !important;
    color: white !important;
}

#authModal .border-indigo-600 {
    border-color: #4f46e5 !important;
    color: #4f46e5 !important;
    background: white !important;
}

#authModal .border-indigo-600:hover {
    background: #4f46e5 !important;
    color: white !important;
}

#authModal h3,
#authModal p {
    color: inherit !important;
}

#authModal .text-gray-900 {
    color: #111827 !important;
}

#authModal .text-gray-500 {
    color: #6b7280 !important;
}

#authModal .text-primary {
    color: #4f46e5 !important;
}

/* Animation classes */
#authModalContent.opacity-0 {
    opacity: 0;
}

#authModalContent.opacity-100 {
    opacity: 1;
}

#authModalContent.scale-95 {
    transform: scale(0.95);
}

#authModalContent.scale-100 {
    transform: scale(1);
}

[data-quick-cart] {
    background: #6366f1 !important; /* indigo-600 */
    color: white !important;
    opacity: 1 !important;
    visibility: visible !important;
    display: flex !important;
}

[data-quick-cart]:hover {
    background: #4f46e5 !important; /* indigo-700 */
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.4) !important;
}

/* Remove the opacity/visibility transitions that hide the button */
.product-card .quick-add {
    opacity: 1 !important;
    visibility: visible !important;
    transition: transform 0.2s ease, background 0.2s ease !important;
}

/* Ensure the button container is always visible */
.product-card [data-quick-cart] {
    background: #6366f1 !important;
    color: white !important;
}

/* Alternative: If you want it to be slightly transparent but still visible */
/* Remove the above and use this instead: */
/*
.product-card .quick-add {
    opacity: 0.8 !important;
    visibility: visible !important;
}

.product-card:hover .quick-add {
    opacity: 1 !important;
}
*/

/* Fix for wishlist button too */
[data-quick-wishlist] {
    opacity: 1 !important;
    visibility: visible !important;
}

/* Ensure product actions are always visible */
.product-actions {
    opacity: 1 !important;
    visibility: visible !important;
    transform: translateY(0) !important;
}

/* Make sure the cart icon is visible */
[data-quick-cart] i {
    color: white !important;
    opacity: 1 !important;
}
</style>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-6">
        <div class="flex flex-col lg:flex-row gap-6">
            <!-- Sidebar Filters -->
            <aside class="lg:w-1/4 filter-sidebar">
                <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-filter text-primary mr-2"></i>Filters
                    </h3>
                    
                    <!-- Categories -->
                    <div class="mb-6">
                        <h4 class="font-semibold text-gray-700 mb-3">Categories</h4>
                        <div class="space-y-2">
                            <a href="{{ route('marketplace.index', request()->except('category')) }}" 
   class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-gray-50 {{ !request('category') ? 'active-filter' : 'text-gray-600' }}">
    <span>All Categories</span>
    <span class="text-sm opacity-75">{{ $totalProducts ?? 0 }}</span>
</a>
@foreach($categories as $category)
    <a href="{{ route('marketplace.index', array_merge(request()->except('category'), ['category' => $category->id])) }}" 
       class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-gray-50 {{ request('category') == $category->id ? 'active-filter' : 'text-gray-600' }}">
        <span>{{ $category->name }}</span>
        <span class="text-sm opacity-75">{{ $category->listings_count ?? 0 }}</span>
    </a>
@endforeach
                        </div>
                    </div>
                    
                    <!-- Price Range -->
                    <div class="mb-6">
                        <h4 class="font-semibold text-gray-700 mb-3">Price Range</h4>
                        <form method="GET" action="{{ route('marketplace.index') }}" id="priceForm">
                            <div class="mb-4">
                                <input type="range" min="0" max="1000" value="{{ request('max_price', 1000) }}" 
                                       class="price-range w-full" id="priceSlider">
                            </div>
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-sm text-gray-600">UGX 0</span>
<span class="text-sm font-semibold text-primary" id="priceValue">UGX {{ number_format(request('max_price', 1000000)) }}</span>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <input type="number" name="min_price" value="{{ request('min_price', 0) }}" 
                                       placeholder="Min" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                                <input type="number" name="max_price" value="{{ request('max_price', 1000000) }}" 
       placeholder="Max (e.g., 5000000)" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                            </div>
                            <button type="submit" class="w-full mt-4 bg-primary text-white py-2.5 rounded-lg font-semibold hover:bg-indigo-700 transition">
                                Apply Price Filter
                            </button>
                        </form>
                    </div>
                    
                    <!-- Product Origin -->
                    <div class="mb-6">
                        <h4 class="font-semibold text-gray-700 mb-3">Product Origin</h4>
                        <div class="space-y-2">
                            <a href="{{ route('marketplace.index', request()->except('origin')) }}" 
                               class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-50 {{ !request('origin') ? 'active-filter' : 'text-gray-600' }}">
                                <i class="fas fa-globe-americas mr-2 text-sm"></i>
                                <span>All Products</span>
                            </a>
                            <a href="{{ route('marketplace.index', array_merge(request()->except('origin'), ['origin' => 'local'])) }}" 
                               class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-50 {{ request('origin') == 'local' ? 'active-filter' : 'text-gray-600' }}">
                                <i class="fas fa-map-marker-alt mr-2 text-sm"></i>
                                <span>Local Products</span>
                            </a>
                            <a href="{{ route('marketplace.index', array_merge(request()->except('origin'), ['origin' => 'imported'])) }}" 
                               class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-50 {{ request('origin') == 'imported' ? 'active-filter' : 'text-gray-600' }}">
                                <i class="fas fa-plane mr-2 text-sm"></i>
                                <span>Imported Products</span>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Sort By -->
                    <div>
                        <h4 class="font-semibold text-gray-700 mb-3">Sort By</h4>
                        <div class="space-y-2">
                            @php
                                $sortOptions = [
                                    'newest' => ['label' => 'Newest First', 'icon' => 'calendar-plus'],
                                    'price_low' => ['label' => 'Price: Low to High', 'icon' => 'arrow-up'],
                                    'price_high' => ['label' => 'Price: High to Low', 'icon' => 'arrow-down'],
                                    'popular' => ['label' => 'Most Popular', 'icon' => 'fire']
                                ];
                            @endphp
                            @foreach($sortOptions as $value => $option)
                                <a href="{{ route('marketplace.index', array_merge(request()->except('sort'), ['sort' => $value])) }}" 
                                   class="flex items-center px-3 py-2 rounded-lg hover:bg-gray-50 {{ request('sort', 'newest') == $value ? 'active-filter' : 'text-gray-600' }}">
                                    <i class="fas fa-{{ $option['icon'] }} mr-2 text-sm"></i>
                                    <span>{{ $option['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                    
                    <!-- Clear Filters -->
                    @if(request()->anyFilled(['search', 'category', 'origin', 'min_price', 'max_price', 'sort']))
                        <div class="mt-6 pt-6 border-t">
                            <a href="{{ route('marketplace.index') }}" 
                               class="flex items-center justify-center w-full py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium">
                                <i class="fas fa-times mr-2"></i>Clear All Filters
                            </a>
                        </div>
                    @endif
                </div>
                
                <!-- Category Tags -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Popular Categories</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($categories->take(8) as $category)
                            <a href="{{ route('marketplace.index', ['category' => $category->id]) }}" 
                               class="category-tag inline-flex items-center px-3 py-1.5 bg-gray-100 text-gray-700 rounded-full hover:bg-primary hover:text-white transition">
                                <i class="fas fa-{{ $category->icon ?? 'tag' }} mr-1.5 text-xs"></i>
                                <span class="text-sm">{{ $category->name }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </aside>
            
            <!-- Main Products Section -->
            <main class="lg:w-3/4">
                <!-- Header -->
                <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <h1 class="heading-responsive font-bold text-gray-800 mb-1">
                                @if(request('search'))
                                    Search Results for "{{ request('search') }}"
                                @elseif(request('category') && $selectedCategory = $categories->firstWhere('id', request('category')))
                                    {{ $selectedCategory->name }}
                                @else
                                    All Products
                                @endif
                            </h1>
                            <p class="text-gray-600">
                                @if($listings->total() > 0)
                                    Showing {{ $listings->firstItem() }}-{{ $listings->lastItem() }} of {{ $listings->total() }} products
                                @else
                                    No products found
                                @endif
                            </p>
                        </div>
                        
                        <!-- Active Filters -->
                        @if(request()->anyFilled(['search', 'category', 'origin', 'min_price', 'max_price']))
                            <div class="flex flex-wrap gap-2">
                                @if(request('search'))
                                    <span class="inline-flex items-center px-3 py-1.5 bg-primary/10 text-primary rounded-full text-sm">
                                        Search: "{{ request('search') }}"
                                        <a href="{{ route('marketplace.index', request()->except('search')) }}" class="ml-2 text-primary hover:text-indigo-700">
                                            <i class="fas fa-times text-xs"></i>
                                        </a>
                                    </span>
                                @endif
                                
                                @if(request('category') && $selectedCategory)
                                    <span class="inline-flex items-center px-3 py-1.5 bg-primary/10 text-primary rounded-full text-sm">
                                        Category: {{ $selectedCategory->name }}
                                        <a href="{{ route('marketplace.index', request()->except('category')) }}" class="ml-2 text-primary hover:text-indigo-700">
                                            <i class="fas fa-times text-xs"></i>
                                        </a>
                                    </span>
                                @endif
                                
                                @if(request('origin'))
                                    <span class="inline-flex items-center px-3 py-1.5 bg-primary/10 text-primary rounded-full text-sm">
                                        Origin: {{ ucfirst(request('origin')) }}
                                        <a href="{{ route('marketplace.index', request()->except('origin')) }}" class="ml-2 text-primary hover:text-indigo-700">
                                            <i class="fas fa-times text-xs"></i>
                                        </a>
                                    </span>
                                @endif
                                
                                @if(request('min_price') || request('max_price'))
                                    <span class="inline-flex items-center px-3 py-1.5 bg-primary/10 text-primary rounded-full text-sm">
                                       Price: 
                                        @if(request('min_price'))UGX {{ number_format(request('min_price')) }} @endif
                                        @if(request('min_price') && request('max_price')) - @endif
                                        @if(request('max_price'))UGX {{ number_format(request('max_price')) }} @endif
                                        <a href="{{ route('marketplace.index', request()->except(['min_price', 'max_price'])) }}" class="ml-2 text-primary hover:text-indigo-700">
                                            <i class="fas fa-times text-xs"></i>
                                        </a>
                                    </span>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
                
                <!-- Products Grid -->
                @if($listings->count() > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($listings as $listing)
                            <div class="product-card bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
                                <!-- Product Image -->
                                <div class="relative overflow-hidden">
                                    <a href="{{ route('marketplace.show', $listing) }}" class="block">
                                        @if($listing->images->first())
                                            <img src="{{ asset('storage/' . $listing->images->first()->path) }}" 
                                                 alt="{{ $listing->title }}" 
                                                 class="w-full h-48 object-cover product-image">
                                        @else
                                            <div class="w-full h-48 bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                                                <i class="fas fa-image text-gray-300 text-4xl"></i>
                                            </div>
                                        @endif
                                    </a>
                                    
                                    <!-- Badges -->
                                    <div class="absolute top-3 left-3">
                                        @if($listing->origin == 'imported')
                                            <span class="inline-flex items-center px-2 py-1 bg-blue-500 text-white text-xs font-bold rounded-full">
                                                <i class="fas fa-plane mr-1"></i> Imported
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 bg-green-500 text-white text-xs font-bold rounded-full">
                                                <i class="fas fa-home mr-1"></i> Local
                                            </span>
                                        @endif
                                    </div>
                                    
                                    <!-- Quick Actions -->
                                    <div class="absolute top-3 right-3">
                                        <button data-quick-wishlist data-listing-id="{{ $listing->id }}" 
                                                class="w-8 h-8 bg-white rounded-full shadow flex items-center justify-center hover:bg-red-50 transition">
                                            <i class="far fa-heart text-gray-600 hover:text-red-500"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Product Info -->
                                <div class="p-5">
                                    <!-- Category -->
                                    <div class="mb-2">
                                        <span class="text-xs text-gray-500 font-medium">
                                            {{ $listing->category->name ?? 'General' }}
                                        </span>
                                    </div>
                                    
                                    <!-- Title -->
                                    <a href="{{ route('marketplace.show', $listing) }}">
                                        <h3 class="font-semibold text-gray-800 mb-2 line-clamp-2 hover:text-primary transition">
                                            {{ $listing->title }}
                                        </h3>
                                    </a>
                                    
                                    <!-- Description -->
                                    <p class="text-sm text-gray-600 mb-4 line-clamp-2">
                                        {{ Str::limit($listing->description, 100) }}
                                    </p>
                                    
                                    <!-- Vendor Info -->
                                    <div class="flex items-center mb-4">
                                        <div class="w-8 h-8 bg-primary text-white rounded-full flex items-center justify-center text-xs mr-3">
                                            <i class="fas fa-store"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-700">
                                                {{ $listing->vendor->business_name ?? 'Vendor' }}
                                            </p>
                                            <div class="flex items-center">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <i class="fas fa-star text-xs {{ $i <= 4 ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                                                @endfor
                                                <span class="text-xs text-gray-500 ml-1">({{ rand(10,200) }})</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Price and Actions -->
                                    <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                        <div>
                                           <div class="text-xl font-bold text-primary">
                                                UGX {{ number_format($listing->price, 0) }}
                                            </div>
                                            @if($listing->weight_kg)
                                                <div class="text-xs text-gray-500 mt-1">
                                                    <i class="fas fa-weight-hanging mr-1"></i>{{ $listing->weight_kg }}kg
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <div class="flex items-center gap-2">
                                            @if($listing->stock > 0)
                                                <button data-quick-cart data-listing-id="{{ $listing->id }}" 
                                                        class="w-10 h-10 bg-primary text-white rounded-lg flex items-center justify-center hover:bg-indigo-700 transition">
                                                    <i class="fas fa-shopping-cart"></i>
                                                </button>
                                            @endif
                                            <a href="{{ route('marketplace.show', $listing) }}" 
                                               class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium text-sm transition">
                                                View Details
                                            </a>
                                        </div>
                                    </div>
                                    
                                    <!-- Stock Status -->
                                    <div class="mt-4 pt-4 border-t border-gray-100">
                                        @if($listing->stock > 10)
                                            <div class="text-sm text-green-600 flex items-center">
                                                <i class="fas fa-check-circle mr-2"></i>
                                                In Stock ({{ $listing->stock }} available)
                                            </div>
                                        @elseif($listing->stock > 0)
                                            <div class="text-sm text-yellow-600 flex items-center">
                                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                                Only {{ $listing->stock }} left in stock
                                            </div>
                                        @else
                                            <div class="text-sm text-red-600 flex items-center">
                                                <i class="fas fa-times-circle mr-2"></i>
                                                Out of Stock
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <!-- Pagination -->
                    @if($listings->hasPages())
                        <div class="mt-8">
                            <div class="flex justify-center">
                                <div class="flex items-center space-x-2">
                                    <!-- Previous -->
                                    @if($listings->onFirstPage())
                                        <span class="px-3 py-2 border rounded-lg text-gray-400 cursor-not-allowed">
                                            <i class="fas fa-chevron-left"></i>
                                        </span>
                                    @else
                                        <a href="{{ $listings->previousPageUrl() }}" class="px-3 py-2 border rounded-lg hover:bg-gray-50 pagination-link">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    @endif
                                    
                                    <!-- Page Numbers -->
                                    @foreach(range(1, $listings->lastPage()) as $page)
                                        @if($page == $listings->currentPage())
                                            <span class="px-4 py-2 bg-primary text-white rounded-lg font-medium">
                                                {{ $page }}
                                            </span>
                                        @else
                                            <a href="{{ $listings->url($page) }}" class="px-4 py-2 border rounded-lg hover:bg-gray-50 pagination-link">
                                                {{ $page }}
                                            </a>
                                        @endif
                                    @endforeach
                                    
                                    <!-- Next -->
                                    @if($listings->hasMorePages())
                                        <a href="{{ $listings->nextPageUrl() }}" class="px-3 py-2 border rounded-lg hover:bg-gray-50 pagination-link">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    @else
                                        <span class="px-3 py-2 border rounded-lg text-gray-400 cursor-not-allowed">
                                            <i class="fas fa-chevron-right"></i>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                @else
                    <!-- No Results -->
                    <div class="bg-white rounded-xl shadow-sm p-12 text-center">
                        <div class="w-24 h-24 mx-auto mb-6 bg-gray-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-search text-gray-400 text-3xl"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-800 mb-3">No products found</h3>
                        <p class="text-gray-600 mb-6 max-w-md mx-auto">
                            @if(request()->anyFilled(['search', 'category', 'origin', 'min_price', 'max_price']))
                                No products match your current filters. Try adjusting your search criteria.
                            @else
                                No products are currently available. Check back soon!
                            @endif
                        </p>
                        @if(request()->anyFilled(['search', 'category', 'origin', 'min_price', 'max_price']))
                            <a href="{{ route('marketplace.index') }}" 
                               class="inline-flex items-center px-6 py-3 bg-primary text-white rounded-lg hover:bg-indigo-700 font-medium">
                                <i class="fas fa-times mr-2"></i>Clear All Filters
                            </a>
                        @endif
                    </div>
                @endif
                
                <!-- Browse Categories Section -->
                @if(!request()->filled('category') && $categories->count() > 0)
                    <div class="mt-12">
                        <div class="bg-white rounded-xl shadow-sm p-6">
                            <div class="flex items-center justify-between mb-6">
                                <h2 class="text-xl font-bold text-gray-800">Browse by Category</h2>
                                <a href="{{ route('categories.index') }}" class="text-primary hover:text-indigo-700 font-medium">
                                    View All Categories <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-4">
                                @foreach($categories->take(12) as $category)
                                    <a href="{{ route('marketplace.index', ['category' => $category->id]) }}" 
                                       class="category-card bg-gray-50 rounded-lg p-4 text-center hover:bg-primary hover:text-white transition group">
                                        <div class="w-12 h-12 mx-auto mb-3 rounded-lg bg-white group-hover:bg-white/20 flex items-center justify-center">
                                            <i class="fas fa-{{ $category->icon ?? 'tag' }} text-lg text-primary group-hover:text-white"></i>
                                        </div>
                                        <div class="font-medium text-gray-800 group-hover:text-white text-sm line-clamp-1">
                                            {{ $category->name }}
                                        </div>
                                        <div class="text-xs text-gray-500 group-hover:text-white/80 mt-1">
                                            {{ $category->listings_count ?? 0 }} products
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
                
                <!-- Trust Badges -->
                <div class="mt-8">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="bg-white rounded-xl p-6 text-center shadow-sm border border-gray-100">
                            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-shield-alt text-green-600 text-xl"></i>
                            </div>
                            <h4 class="font-bold text-gray-800 mb-1">Secure Escrow</h4>
                            <p class="text-sm text-gray-600">Money protected until delivery</p>
                        </div>
                        <div class="bg-white rounded-xl p-6 text-center shadow-sm border border-gray-100">
                            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-truck text-blue-600 text-xl"></i>
                            </div>
                            <h4 class="font-bold text-gray-800 mb-1">Fast Delivery</h4>
                            <p class="text-sm text-gray-600">Nationwide shipping network</p>
                        </div>
                        <div class="bg-white rounded-xl p-6 text-center shadow-sm border border-gray-100">
                            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-headset text-purple-600 text-xl"></i>
                            </div>
                            <h4 class="font-bold text-gray-800 mb-1">24/7 Support</h4>
                            <p class="text-sm text-gray-600">Always here to help you</p>
                        </div>
                        <div class="bg-white rounded-xl p-6 text-center shadow-sm border border-gray-100">
                            <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-undo text-yellow-600 text-xl"></i>
                            </div>
                            <h4 class="font-bold text-gray-800 mb-1">Easy Returns</h4>
                            <p class="text-sm text-gray-600">30-day return policy</p>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Become Vendor CTA -->
<div class="bg-gradient-to-r from-indigo-700 to-indigo-900 mt-12">
    <div class="container mx-auto px-4 py-16">
        <div class="max-w-3xl mx-auto text-center text-white">
            <h2 class="text-3xl font-bold mb-4">Ready to Start Selling?</h2>
            <p class="text-xl mb-8 text-white/90">
                Join our marketplace of trusted vendors. Reach thousands of customers with our secure escrow protection.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('vendor.onboard.create') }}" 
                   class="inline-flex items-center justify-center gap-3 px-8 py-4 bg-white text-indigo-700 rounded-xl font-bold hover:bg-gray-100 hover:scale-105 transition-all duration-300">
                    <i class="fas fa-store"></i>
                    <span>Become a Vendor</span>
                </a>
                <a href="{{ route('vendor.login') }}" 
                   class="inline-flex items-center justify-center gap-3 px-8 py-4 bg-transparent border-2 border-white text-white rounded-xl font-bold hover:bg-white hover:text-indigo-700 transition-all duration-300">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Vendor Login</span>
                </a>
            </div>
        </div>
    </div>
</div>
    
    

    
    <!-- JavaScript -->

<script>
    // Price range slider
    const priceSlider = document.getElementById('priceSlider');
    const priceValue = document.getElementById('priceValue');
    const maxPriceInput = document.querySelector('input[name="max_price"]');
    
    if (priceSlider && priceValue) {
        priceSlider.addEventListener('input', function() {
            const value = this.value;
            priceValue.textContent = 'UGX ' + value.toLocaleString();
            if (maxPriceInput) {
                maxPriceInput.value = value;
            }
        });
    }
    
   let cartProcessing = false;
async function quickAddToCart(listingId, button) {
    // Prevent multiple clicks
    if (cartProcessing) {
        console.log('Cart action already in progress, skipping...');
        return;
    }
    
    cartProcessing = true;
    console.log('=== QuickAddToCart called for listing:', listingId);
    
    const isAuthenticated = @json(auth()->check());
    
    if (!isAuthenticated) { 
        console.log('User not authenticated, showing auth modal');
        showAuthModal(); 
        cartProcessing = false;
        return; 
    }
    
    // Check if product has variations
    try {
        console.log('Checking for variations...');
        const checkResponse = await fetch(`/api/listings/${listingId}/check-variations`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        console.log('Check response status:', checkResponse.status);
        
        if (checkResponse.ok) {
            const checkData = await checkResponse.json();
            console.log('Variation check data:', checkData);
            
           if (checkData.has_variations && (checkData.available_colors.length > 0 || checkData.available_sizes.length > 0)) {
    cartProcessing = false;
    showVariationModal(listingId, button);
    return;
}
 else {
                console.log('Product has no variations (or empty options)');
            }
        }
    } catch (error) {
        console.error('Error checking variations:', error);
    }
    
    console.log('Adding directly to cart (no variations)');
    await addToCartSimple(listingId, button);
    cartProcessing = false;
}

// Add this helper function
async function addToCartSimple(listingId, button) {
    console.log('Adding to cart (simple):', listingId);
    
    const originalContent = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    button.disabled = true;
    
    try {
        const response = await fetch(`/buyer/cart/add/${listingId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ quantity: 1 })
        });
        
        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            throw new Error(errorData.message || `Server error: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('Cart add response:', data);
        
        if (data.success) {
            button.innerHTML = '<i class="fas fa-check"></i>';
            button.classList.remove('bg-primary', 'hover:bg-indigo-700');
            button.classList.add('bg-green-500', 'hover:bg-green-600');
            
            if (data.cart_count) {
                updateCartCount(data.cart_count);
            }
            
            showToast('Added to cart!', 'success');
            
            setTimeout(() => {
                button.innerHTML = originalContent;
                button.classList.remove('bg-green-500', 'hover:bg-green-600');
                button.classList.add('bg-primary', 'hover:bg-indigo-700');
                button.disabled = false;
            }, 1500);
        } else {
            throw new Error(data.message || 'Failed to add to cart');
        }
    } catch (error) {
        console.error('Cart error:', error);
        button.innerHTML = originalContent;
        button.disabled = false;
        showToast(error.message || 'Failed to add to cart', 'error');
    }
} 

async function showVariationModal(listingId, button) {
    console.log('Showing variation modal for listing:', listingId);
    
    try {
        const response = await fetch(`/api/listings/${listingId}/variations`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        
        if (!response.ok) {
            throw new Error(`Failed to load variations: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('Variations data:', data);
        
        // Create and show modal
        const modal = createVariationModalHTML(data, listingId, button);
        document.body.appendChild(modal);
        
        setTimeout(() => {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }, 10);
        
    } catch (error) {
        console.error('Error loading variations:', error);
        showToast('Failed to load product options', 'error');
        // Fallback: add to cart without variations
        await addToCartSimple(listingId, button);
    }
}

function createVariationModalHTML(data, listingId, triggerButton) {
    const modal = document.createElement('div');
    modal.id = 'variationModal';
    modal.className = 'fixed inset-0 z-[100] hidden items-center justify-center p-4';
    modal.dataset.listingId = listingId;
    
    const { variations, colors, sizes } = data;
    
    console.log('Creating modal with:', { colors, sizes, variationCount: variations?.length || 0 });
    
    let colorOptionsHTML = '';
    if (colors && colors.length > 0) {
        colorOptionsHTML = `
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-3">
                    Select Color <span class="text-red-500">*</span>
                </label>
                <div class="grid grid-cols-3 gap-2">
                    ${colors.map(color => `
                        <button type="button" 
                                onclick="selectVariationOption('color', '${color}')"
                                data-option="color"
                                data-value="${color}"
                                class="px-4 py-2.5 border-2 border-gray-200 rounded-lg text-sm font-medium hover:border-primary transition-all">
                            ${color}
                        </button>
                    `).join('')}
                </div>
            </div>
        `;
    }
    
    let sizeOptionsHTML = '';
    if (sizes && sizes.length > 0) {
        sizeOptionsHTML = `
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-3">
                    Select Size <span class="text-red-500">*</span>
                </label>
                <div class="grid grid-cols-3 gap-2">
                    ${sizes.map(size => `
                        <button type="button" 
                                onclick="selectVariationOption('size', '${size}')"
                                data-option="size"
                                data-value="${size}"
                                class="px-4 py-2.5 border-2 border-gray-200 rounded-lg text-sm font-medium hover:border-primary transition-all">
                            ${size}
                        </button>
                    `).join('')}
                </div>
            </div>
        `;
    }
    
    modal.innerHTML = `
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeVariationModal()"></div>
        <div class="relative bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl transform transition-all">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-2xl font-bold text-gray-900">Select Options</h3>
                <button onclick="closeVariationModal()" 
                        class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 hover:bg-gray-200 hover:text-gray-700 transition">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            
            <div id="variationOptions">
                ${colorOptionsHTML}
                ${sizeOptionsHTML}
            </div>
            
            <div id="selectedVariantInfo" class="hidden mb-6 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl border border-blue-100">
                <div class="flex justify-between items-center">
                    <div>
                        <h4 class="font-bold text-gray-800 mb-2">Selected Variant</h4>
                        <div id="selectedOptionsText" class="text-sm text-gray-600"></div>
                    </div>
                    <div class="text-right">
                        <p id="variantPrice" class="text-xl font-bold text-primary"></p>
                        <p id="variantStock" class="text-sm text-gray-600 mt-1"></p>
                    </div>
                </div>
            </div>
            
            <div class="flex gap-3">
                <button type="button" 
                        onclick="closeVariationModal()" 
                        class="flex-1 py-3 px-4 border-2 border-gray-300 text-gray-700 rounded-xl font-semibold hover:bg-gray-50 transition">
                    Cancel
                </button>
                <button type="button" 
                        onclick="addToCartWithVariation()"
                        id="addWithVariationBtn"
                        disabled
                        class="flex-1 py-3 px-4 bg-primary text-white rounded-xl font-bold hover:bg-indigo-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-shopping-cart mr-2"></i>Add to Cart
                </button>
            </div>
        </div>
    `;
    
    // Store data in modal dataset
    modal.dataset.variations = JSON.stringify(variations || []);
    modal.dataset.triggerButtonId = triggerButton?.getAttribute('data-listing-id') || '';
    
    return modal;
}

// Make these functions global
window.selectVariationOption = function(optionType, value) {
    console.log('Selected:', optionType, value);
    
    const modal = document.getElementById('variationModal');
    if (!modal) return;
    
    // Update selected value
    modal.dataset[`selected${optionType.charAt(0).toUpperCase() + optionType.slice(1)}`] = value;
    
    // Update UI
    const allButtons = modal.querySelectorAll(`[data-option="${optionType}"]`);
    allButtons.forEach(btn => {
        btn.classList.remove('border-primary', 'bg-primary', 'text-white');
        btn.classList.add('border-gray-200', 'text-gray-700');
    });
    
    const selectedButton = modal.querySelector(`[data-option="${optionType}"][data-value="${value}"]`);
    if (selectedButton) {
        selectedButton.classList.remove('border-gray-200', 'text-gray-700');
        selectedButton.classList.add('border-primary', 'bg-primary', 'text-white');
    }
    
    // Find and display matching variant
    findAndDisplayMatchingVariant(modal);
};

function findAndDisplayMatchingVariant(modal) {
    const variations = JSON.parse(modal.dataset.variations || '[]');
    const selectedColor = modal.dataset.selectedColor;
    const selectedSize = modal.dataset.selectedSize;
    
    console.log('Finding variant for:', { selectedColor, selectedSize, variationsCount: variations.length });
    
    // Find variant that matches selected options
    const matchingVariant = variations.find(variant => {
        const attrs = variant.attributes || {};
        const variantColor = attrs.color || attrs.Color || attrs.COLOR;
        const variantSize = attrs.size || attrs.Size || attrs.SIZE;
        
        const colorMatch = !selectedColor || variantColor === selectedColor;
        const sizeMatch = !selectedSize || variantSize === selectedSize;
        
        return colorMatch && sizeMatch;
    });
    
    const variantInfo = modal.querySelector('#selectedVariantInfo');
    const addBtn = modal.querySelector('#addWithVariationBtn');
    
    if (matchingVariant) {
        console.log('Found matching variant:', matchingVariant);
        
        variantInfo.classList.remove('hidden');
        
        let optionsText = [];
        if (selectedColor) optionsText.push(`Color: ${selectedColor}`);
        if (selectedSize) optionsText.push(`Size: ${selectedSize}`);
        
        modal.querySelector('#selectedOptionsText').textContent = optionsText.join(' • ');
        modal.querySelector('#variantPrice').textContent = `UGX ${(matchingVariant.display_price || matchingVariant.price || 0).toLocaleString()}`;
        modal.querySelector('#variantStock').textContent = matchingVariant.stock > 0 
            ? `${matchingVariant.stock} in stock` 
            : 'Out of stock';
        
        modal.dataset.selectedVariantId = matchingVariant.id;
        addBtn.disabled = matchingVariant.stock <= 0;
        
        if (matchingVariant.stock <= 0) {
            addBtn.innerHTML = '<i class="fas fa-times-circle mr-2"></i>Out of Stock';
        } else {
            addBtn.innerHTML = '<i class="fas fa-shopping-cart mr-2"></i>Add to Cart';
        }
    } else {
        console.log('No matching variant found');
        variantInfo.classList.add('hidden');
        modal.dataset.selectedVariantId = '';
        addBtn.disabled = true;
        addBtn.innerHTML = '<i class="fas fa-shopping-cart mr-2"></i>Add to Cart';
    }
}

window.addToCartWithVariation = async function() {
    console.log('Adding to cart with variation');
    
    const modal = document.getElementById('variationModal');
    if (!modal) {
        console.error('Modal not found');
        return;
    }
    
    const variantId = modal.dataset.selectedVariantId;
    const listingId = modal.dataset.listingId;
    const color = modal.dataset.selectedColor || null;
    const size = modal.dataset.selectedSize || null;
    
    console.log('Adding variant:', { variantId, listingId, color, size });
    
    if (!variantId) {
        showToast('Please select all required options', 'error');
        return;
    }
    
    const buttonId = modal.dataset.triggerButtonId;
    const button = document.querySelector(`[data-listing-id="${buttonId}"]`);
    
    closeVariationModal();
    
    if (!button) {
        console.error('Original button not found');
        showToast('Added to cart!', 'success');
        return;
    }
    
    const originalContent = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    button.disabled = true;
    
    try {
        const response = await fetch(`/buyer/cart/add/${listingId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ 
                quantity: 1,
                variant_id: variantId,
                color: color,
                size: size
            })
        });
        
        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            throw new Error(errorData.message || `Server error: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('Cart response:', data);
        
        if (data.success) {
            button.innerHTML = '<i class="fas fa-check"></i>';
            button.classList.remove('bg-primary', 'hover:bg-indigo-700');
            button.classList.add('bg-green-500', 'hover:bg-green-600');
            
            if (data.cart_count !== undefined) {
                updateCartCount(data.cart_count);
            }
            
            showToast('Added to cart!', 'success');
            
            setTimeout(() => {
                button.innerHTML = originalContent;
                button.classList.remove('bg-green-500', 'hover:bg-green-600');
                button.classList.add('bg-primary', 'hover:bg-indigo-700');
                button.disabled = false;
            }, 2000);
        } else {
            throw new Error(data.message || 'Failed to add to cart');
        }
    } catch (error) {
        console.error('Cart error:', error);
        if (button) {
            button.innerHTML = originalContent;
            button.disabled = false;
        }
        showToast(error.message || 'Failed to add to cart', 'error');
    }
};

window.closeVariationModal = function() {
    const modal = document.getElementById('variationModal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        setTimeout(() => {
            modal.remove();
            document.body.style.overflow = 'auto';
        }, 200);
    }
};
    async function quickAddToWishlist(listingId, button) {
        const isAuthenticated = @json(auth()->check());
        
        if (!isAuthenticated) {
            showAuthModal();
            return;
        }
        
        const icon = button.querySelector('i');
        const originalIconClass = icon.className;
        icon.className = 'fas fa-spinner fa-spin';
        
        try {
            const response = await fetch(`/buyer/wishlist/toggle/${listingId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            });
            
            if (!response.ok) {
                throw new Error(`Server responded with status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                if (data.in_wishlist) {
                    icon.className = 'fas fa-heart text-red-500';
                } else {
                    icon.className = 'far fa-heart';
                }
                showToast(data.message || 'Wishlist updated!', 'success');
            } else {
                throw new Error(data.message || 'Failed to update wishlist');
            }
        } catch (error) {
            console.error('Wishlist error:', error);
            icon.className = originalIconClass;
            showToast(error.message || 'Failed to update wishlist', 'error');
        }
    }
    
    // Update cart count
    function updateCartCount(count) {
        const cartCountElements = document.querySelectorAll('.cart-count');
        cartCountElements.forEach(element => {
            element.textContent = count;
            element.classList.remove('hidden');
            element.classList.add('animate-pulse');
            setTimeout(() => element.classList.remove('animate-pulse'), 1000);
        });
    }
    
    // Show toast
    function showToast(message, type = 'info') {
        // Remove existing toasts
        const existingToasts = document.querySelectorAll('.toast');
        existingToasts.forEach(toast => toast.remove());
        
        const toast = document.createElement('div');
        toast.className = `toast fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg flex items-center gap-2`;
        
        if (type === 'success') {
            toast.classList.add('bg-green-500', 'text-white');
            toast.innerHTML = `<i class="fas fa-check-circle"></i> ${message}`;
        } else if (type === 'error') {
            toast.classList.add('bg-red-500', 'text-white');
            toast.innerHTML = `<i class="fas fa-times-circle"></i> ${message}`;
        } else {
            toast.classList.add('bg-blue-500', 'text-white');
            toast.innerHTML = `<i class="fas fa-info-circle"></i> ${message}`;
        }
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.3s';
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, 3000);
    }
    
   function showAuthModal() {
    const modal = document.getElementById('authModal');
    const content = document.getElementById('authModalContent');

    if (!modal || !content) return;

    modal.classList.remove('hidden');
    modal.classList.add('flex');

    requestAnimationFrame(() => {
        content.classList.remove('opacity-0', 'scale-95');
        content.classList.add('opacity-100', 'scale-100');
    });

    document.body.style.overflow = 'hidden';
}

function closeAuthModal() {
    const modal = document.getElementById('authModal');
    const content = document.getElementById('authModalContent');

    if (!modal || !content) return;

    content.classList.add('opacity-0', 'scale-95');
    content.classList.remove('opacity-100', 'scale-100');

    setTimeout(() => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = 'auto';
    }, 200);
}

    
    // Event listeners
    document.addEventListener('DOMContentLoaded', function() {
        // Quick cart buttons
        document.querySelectorAll('[data-quick-cart]').forEach(btn => {
        // Remove any existing listeners first
        btn.replaceWith(btn.cloneNode(true));
        const newBtn = document.querySelector(`[data-quick-cart][data-listing-id="${btn.getAttribute('data-listing-id')}"]`);
        
        newBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation(); // Prevent other listeners
            console.log('Cart button clicked (single event)');
            const listingId = this.getAttribute('data-listing-id');
            quickAddToCart(listingId, this);
        }, { once: false });
    });
    
        
        // Quick wishlist buttons
        document.querySelectorAll('[data-quick-wishlist]').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const listingId = this.getAttribute('data-listing-id');
                quickAddToWishlist(listingId, this);
            });
        });
        
        // Close auth modal on background click
        const authModal = document.getElementById('authModal');
        if (authModal) {
            authModal.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeAuthModal();
                }
            });
        }
        
        // Close auth modal on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeAuthModal();
            }
        });
    });
</script>
    
 <!-- Auth Modal -->
<div id="authModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeAuthModal()"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-8 relative shadow-2xl transform transition-all duration-300" id="authModalContent">
            <button onclick="closeAuthModal()" class="absolute top-4 right-4 w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 hover:bg-gray-200 transition">
                <i class="fas fa-times"></i>
            </button>
            
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-lock text-primary text-2xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Sign In Required</h3>
                <p class="text-gray-500">Please login or create an account to continue with this action.</p>
            </div>
            
            <div class="space-y-3">
                <a href="{{ route('login') }}?redirect={{ urlencode(url()->current()) }}" 
                   class="block w-full py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-bold text-center hover:from-indigo-700 hover:to-purple-700 transition shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                    <i class="fas fa-sign-in-alt mr-2"></i>Login
                </a>
                <a href="{{ route('register') }}?redirect={{ urlencode(url()->current()) }}" 
                   class="block w-full py-3 border-2 border-indigo-600 text-indigo-600 rounded-xl font-bold text-center hover:bg-indigo-600 hover:text-white transition">
                    <i class="fas fa-user-plus mr-2"></i>Create Account
                </a>
            </div>
        </div>
    </div>
</div>



<!-- Product Variation Loader -->
<div id="variationLoader" class="hidden">
    <!-- This will be used to load product variations via AJAX -->
</div>


@endsection
