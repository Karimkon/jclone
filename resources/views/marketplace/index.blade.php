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

/* Verified Badge - Twitter Style SVG */
.verified-badge {
    width: 16px;
    height: 16px;
    margin-left: 4px;
    flex-shrink: 0;
    filter: drop-shadow(0 1px 2px rgba(29, 155, 240, 0.3));
}

/* Fix for filter buttons being hidden */
#priceForm button[type="submit"] {
    opacity: 1 !important;
    visibility: visible !important;
    display: block !important;
    background-color: #6366f1 !important; /* Forces the indigo color */
    color: white !important;
}

/* Ensure the slider itself is visible */
.price-range {
    display: block !important;
    opacity: 1 !important;
}
</style>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-6">
        <div class="flex flex-col lg:flex-row gap-6">
            <!-- Sidebar Filters -->
            <aside class="lg:w-64 xl:w-72 flex-shrink-0 filter-sidebar hidden lg:block">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden sticky top-24">
                    <!-- Filter Header -->
                    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-5 py-4">
                        <h3 class="text-white font-bold flex items-center">
                            <i class="fas fa-sliders-h mr-2"></i>Filters & Sort
                        </h3>
                    </div>

                    <div class="p-5 max-h-[calc(100vh-200px)] overflow-y-auto">
                        <!-- Product Origin - Quick Toggle -->
                        <div class="mb-5">
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Product Type</h4>
                            <div class="flex gap-2">
                                <a href="{{ route('marketplace.index', request()->except('origin')) }}"
                                   class="flex-1 py-2 px-3 text-center text-xs font-semibold rounded-lg transition {{ !request('origin') ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                                    All
                                </a>
                                <a href="{{ route('marketplace.index', array_merge(request()->except('origin'), ['origin' => 'local'])) }}"
                                   class="flex-1 py-2 px-3 text-center text-xs font-semibold rounded-lg transition {{ request('origin') == 'local' ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                                    <i class="fas fa-map-marker-alt mr-1"></i>Local
                                </a>
                                <a href="{{ route('marketplace.index', array_merge(request()->except('origin'), ['origin' => 'imported'])) }}"
                                   class="flex-1 py-2 px-3 text-center text-xs font-semibold rounded-lg transition {{ request('origin') == 'imported' ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                                    <i class="fas fa-plane mr-1"></i>Import
                                </a>
                            </div>
                        </div>

                        <!-- Sort By - Dropdown Style -->
                        <div class="mb-5">
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Sort By</h4>
                            <div class="grid grid-cols-2 gap-2">
                                @php
                                    $sortOptions = [
                                        'newest' => ['label' => 'Newest', 'icon' => 'clock'],
                                        'price_low' => ['label' => 'Price ↑', 'icon' => 'sort-amount-up'],
                                        'price_high' => ['label' => 'Price ↓', 'icon' => 'sort-amount-down'],
                                        'popular' => ['label' => 'Popular', 'icon' => 'fire']
                                    ];
                                @endphp
                                @foreach($sortOptions as $value => $option)
                                    <a href="{{ route('marketplace.index', array_merge(request()->except('sort'), ['sort' => $value])) }}"
                                       class="py-2 px-3 text-xs font-medium rounded-lg transition flex items-center justify-center gap-1 {{ request('sort', 'newest') == $value ? 'bg-indigo-100 text-indigo-700 border-2 border-indigo-300' : 'bg-gray-50 text-gray-600 hover:bg-gray-100 border border-gray-200' }}">
                                        <i class="fas fa-{{ $option['icon'] }} text-[10px]"></i>
                                        {{ $option['label'] }}
                                    </a>
                                @endforeach
                            </div>
                        </div>

                        <!-- Price Range -->
                        <div class="mb-5 pb-5 border-b border-gray-100">
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Price Range</h4>
                            <form method="GET" action="{{ route('marketplace.index') }}" id="priceForm">
                                @foreach(request()->except(['min_price', 'max_price']) as $key => $value)
                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                @endforeach
                                <div class="flex items-center gap-2 mb-3">
                                    <div class="flex-1">
                                        <input type="number" name="min_price" value="{{ request('min_price', '') }}"
                                               placeholder="Min"
                                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                                    </div>
                                    <span class="text-gray-400">-</span>
                                    <div class="flex-1">
                                        <input type="number" name="max_price" value="{{ request('max_price', '') }}"
                                               placeholder="Max"
                                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                                    </div>
                                </div>
                                <button type="submit" class="w-full py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition">
                                    Apply Price
                                </button>
                            </form>
                        </div>

                        <!-- Categories -->
                        <div class="mb-5">
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Categories</h4>
                            <div class="space-y-1 max-h-48 overflow-y-auto">
                                <a href="{{ route('marketplace.index', request()->except('category')) }}"
                                   class="flex items-center justify-between px-3 py-2 rounded-lg text-sm transition {{ !request('category') ? 'bg-indigo-100 text-indigo-700 font-semibold' : 'text-gray-600 hover:bg-gray-50' }}">
                                    <span>All Categories</span>
                                    <span class="text-xs bg-gray-200 text-gray-600 px-2 py-0.5 rounded-full">{{ $totalProducts ?? 0 }}</span>
                                </a>
                                @foreach($categories as $category)
                                    <a href="{{ route('marketplace.index', array_merge(request()->except('category'), ['category' => $category->id])) }}"
                                       class="flex items-center justify-between px-3 py-2 rounded-lg text-sm transition {{ request('category') == $category->id ? 'bg-indigo-100 text-indigo-700 font-semibold' : 'text-gray-600 hover:bg-gray-50' }}">
                                        <span class="truncate">{{ $category->name }}</span>
                                        <span class="text-xs {{ request('category') == $category->id ? 'bg-indigo-200 text-indigo-700' : 'bg-gray-100 text-gray-500' }} px-2 py-0.5 rounded-full ml-2">{{ $category->listings_count ?? 0 }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>

                        <!-- Clear Filters -->
                        @if(request()->anyFilled(['search', 'category', 'origin', 'min_price', 'max_price', 'sort']))
                            <a href="{{ route('marketplace.index') }}"
                               class="flex items-center justify-center w-full py-2.5 border-2 border-red-200 text-red-600 rounded-lg hover:bg-red-50 font-semibold text-sm transition">
                                <i class="fas fa-times mr-2"></i>Clear All Filters
                            </a>
                        @endif
                    </div>
                </div>
            </aside>

            <!-- Mobile Filter Button -->
            <div class="lg:hidden fixed bottom-20 left-4 right-4 z-40">
                <button onclick="toggleMobileFilters()" class="w-full py-3 bg-indigo-600 text-white rounded-xl shadow-lg font-semibold flex items-center justify-center gap-2">
                    <i class="fas fa-filter"></i>
                    Filters & Sort
                    @if(request()->anyFilled(['category', 'origin', 'min_price', 'max_price']))
                        <span class="bg-white text-indigo-600 text-xs px-2 py-0.5 rounded-full">Active</span>
                    @endif
                </button>
            </div>

            <!-- Mobile Filters Panel -->
            <div id="mobileFiltersPanel" class="fixed inset-0 z-50 hidden lg:hidden">
                <div class="absolute inset-0 bg-black/50" onclick="toggleMobileFilters()"></div>
                <div class="absolute bottom-0 left-0 right-0 bg-white rounded-t-3xl max-h-[85vh] overflow-hidden transform translate-y-full transition-transform duration-300" id="mobileFiltersContent">
                    <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="font-bold text-gray-800 text-lg">Filters & Sort</h3>
                        <button onclick="toggleMobileFilters()" class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-times text-gray-600"></i>
                        </button>
                    </div>
                    <div class="p-4 overflow-y-auto max-h-[70vh]">
                        <!-- Same filter content as sidebar -->
                        <!-- Product Origin -->
                        <div class="mb-5">
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Product Type</h4>
                            <div class="flex gap-2">
                                <a href="{{ route('marketplace.index', request()->except('origin')) }}"
                                   class="flex-1 py-2.5 px-3 text-center text-sm font-semibold rounded-xl transition {{ !request('origin') ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600' }}">
                                    All
                                </a>
                                <a href="{{ route('marketplace.index', array_merge(request()->except('origin'), ['origin' => 'local'])) }}"
                                   class="flex-1 py-2.5 px-3 text-center text-sm font-semibold rounded-xl transition {{ request('origin') == 'local' ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-600' }}">
                                    Local
                                </a>
                                <a href="{{ route('marketplace.index', array_merge(request()->except('origin'), ['origin' => 'imported'])) }}"
                                   class="flex-1 py-2.5 px-3 text-center text-sm font-semibold rounded-xl transition {{ request('origin') == 'imported' ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-600' }}">
                                    Imported
                                </a>
                            </div>
                        </div>

                        <!-- Sort -->
                        <div class="mb-5">
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Sort By</h4>
                            <div class="grid grid-cols-2 gap-2">
                                @foreach($sortOptions as $value => $option)
                                    <a href="{{ route('marketplace.index', array_merge(request()->except('sort'), ['sort' => $value])) }}"
                                       class="py-2.5 px-3 text-sm font-medium rounded-xl transition flex items-center justify-center gap-1 {{ request('sort', 'newest') == $value ? 'bg-indigo-100 text-indigo-700 border-2 border-indigo-300' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $option['label'] }}
                                    </a>
                                @endforeach
                            </div>
                        </div>

                        <!-- Categories -->
                        <div class="mb-5">
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Categories</h4>
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('marketplace.index', request()->except('category')) }}"
                                   class="px-3 py-1.5 rounded-full text-sm font-medium transition {{ !request('category') ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600' }}">
                                    All
                                </a>
                                @foreach($categories->take(10) as $category)
                                    <a href="{{ route('marketplace.index', array_merge(request()->except('category'), ['category' => $category->id])) }}"
                                       class="px-3 py-1.5 rounded-full text-sm font-medium transition {{ request('category') == $category->id ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $category->name }}
                                    </a>
                                @endforeach
                            </div>
                        </div>

                        @if(request()->anyFilled(['search', 'category', 'origin', 'min_price', 'max_price']))
                            <a href="{{ route('marketplace.index') }}"
                               class="block w-full py-3 border-2 border-red-200 text-red-600 rounded-xl text-center font-semibold">
                                <i class="fas fa-times mr-2"></i>Clear All Filters
                            </a>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Main Products Section -->
            <main class="flex-1 min-w-0">
                <!-- Header -->
                <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <h1 class="text-lg sm:text-xl font-bold text-gray-800">
                                @if(request('search'))
                                    Results for "{{ Str::limit(request('search'), 20) }}"
                                @elseif(request('category') && $selectedCategory = $categories->firstWhere('id', request('category')))
                                    {{ $selectedCategory->name }}
                                @else
                                    All Products
                                @endif
                            </h1>
                            <p class="text-sm text-gray-500">
                                @if($listings->total() > 0)
                                    {{ $listings->total() }} products found
                                @else
                                    No products found
                                @endif
                            </p>
                        </div>

                        <!-- Active Filters - Compact -->
                        @if(request()->anyFilled(['search', 'category', 'origin', 'min_price', 'max_price']))
                            <div class="flex flex-wrap gap-1.5">
                                @if(request('search'))
                                    <span class="inline-flex items-center px-2 py-1 bg-indigo-100 text-indigo-700 rounded text-xs font-medium">
                                        "{{ Str::limit(request('search'), 10) }}"
                                        <a href="{{ route('marketplace.index', request()->except('search')) }}" class="ml-1.5 hover:text-indigo-900">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    </span>
                                @endif

                                @if(request('category') && $selectedCategory)
                                    <span class="inline-flex items-center px-2 py-1 bg-purple-100 text-purple-700 rounded text-xs font-medium">
                                        {{ Str::limit($selectedCategory->name, 12) }}
                                        <a href="{{ route('marketplace.index', request()->except('category')) }}" class="ml-1.5 hover:text-purple-900">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    </span>
                                @endif

                                @if(request('origin'))
                                    <span class="inline-flex items-center px-2 py-1 {{ request('origin') == 'local' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700' }} rounded text-xs font-medium">
                                        {{ ucfirst(request('origin')) }}
                                        <a href="{{ route('marketplace.index', request()->except('origin')) }}" class="ml-1.5">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    </span>
                                @endif

                                @if(request('min_price') || request('max_price'))
                                    <span class="inline-flex items-center px-2 py-1 bg-yellow-100 text-yellow-700 rounded text-xs font-medium">
                                        @if(request('min_price') && request('max_price'))
                                            {{ number_format(request('min_price')/1000) }}k-{{ number_format(request('max_price')/1000) }}k
                                        @elseif(request('min_price'))
                                            >{{ number_format(request('min_price')/1000) }}k
                                        @else
                                            <{{ number_format(request('max_price')/1000) }}k
                                        @endif
                                        <a href="{{ route('marketplace.index', request()->except(['min_price', 'max_price'])) }}" class="ml-1.5">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    </span>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
                
                <!-- Products Grid -->
                @if($listings->count() > 0)
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                        @foreach($listings as $listing)
                            <div class="product-card bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group">
                                <!-- Product Image -->
                                <div class="relative overflow-hidden aspect-square">
                                    <a href="{{ $listing->category ? route('marketplace.show.category', ['category_slug' => $listing->category->slug, 'listing' => $listing->slug]) : route('marketplace.show', $listing) }}" class="block h-full">
                                        @if($listing->images->first())
                                            <img src="{{ asset('storage/' . $listing->images->first()->path) }}"
                                                 alt="{{ $listing->title }}"
                                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                        @else
                                            <div class="w-full h-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                                                <i class="fas fa-image text-gray-300 text-3xl"></i>
                                            </div>
                                        @endif
                                    </a>

                                    <!-- Badges -->
                                    <div class="absolute top-2 left-2">
                                        @if($listing->origin == 'imported')
                                            <span class="inline-flex items-center px-1.5 py-0.5 bg-blue-500 text-white text-[10px] font-bold rounded">
                                                <i class="fas fa-plane mr-0.5"></i> Import
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-1.5 py-0.5 bg-green-500 text-white text-[10px] font-bold rounded">
                                                <i class="fas fa-map-marker-alt mr-0.5"></i> Local
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Quick Actions -->
                                    <div class="absolute top-2 right-2 flex flex-col gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button data-quick-wishlist data-listing-id="{{ $listing->id }}"
                                                class="w-7 h-7 bg-white/90 backdrop-blur rounded-full shadow flex items-center justify-center hover:bg-red-50 transition">
                                            <i class="far fa-heart text-gray-600 hover:text-red-500 text-sm"></i>
                                        </button>
                                        @if($listing->stock > 0)
                                        <button data-quick-cart data-listing-id="{{ $listing->id }}"
                                                class="w-7 h-7 bg-indigo-600 text-white rounded-full shadow flex items-center justify-center hover:bg-indigo-700 transition">
                                            <i class="fas fa-shopping-cart text-xs"></i>
                                        </button>
                                        @endif
                                    </div>

                                    <!-- Stock Badge -->
                                    @if($listing->stock <= 0)
                                        <div class="absolute inset-0 bg-black/40 flex items-center justify-center">
                                            <span class="bg-red-500 text-white px-3 py-1 rounded-full text-xs font-bold">Out of Stock</span>
                                        </div>
                                    @elseif($listing->stock <= 5)
                                        <div class="absolute bottom-2 left-2">
                                            <span class="bg-orange-500 text-white px-1.5 py-0.5 rounded text-[10px] font-bold">
                                                Only {{ $listing->stock }} left
                                            </span>
                                        </div>
                                    @endif
                                </div>

                                <!-- Product Info -->
                                <div class="p-3">
                                    <!-- Category -->
                                    <span class="text-[10px] text-gray-400 font-medium uppercase tracking-wide">
                                        {{ $listing->category->name ?? 'General' }}
                                    </span>

                                    <!-- Title -->
                                    <a href="{{ $listing->category ? route('marketplace.show.category', ['category_slug' => $listing->category->slug, 'listing' => $listing->slug]) : route('marketplace.show', $listing) }}">
                                        <h3 class="font-semibold text-gray-800 text-sm line-clamp-2 hover:text-indigo-600 transition mt-1 leading-tight">
                                            {{ $listing->title }}
                                        </h3>
                                    </a>

                                    <!-- Vendor Info -->
                                    <div class="flex items-center gap-1 mt-2">
                                        <span class="text-[11px] text-gray-500 truncate">
                                            {{ Str::limit($listing->vendor->business_name ?? 'Vendor', 15) }}
                                        </span>
                                        @if($listing->vendor && $listing->vendor->user && $listing->vendor->user->is_admin_verified)
                                            <svg viewBox="0 0 24 24" class="w-3.5 h-3.5 flex-shrink-0" title="Verified">
                                                <path fill="#1d9bf0" d="M22.25 12c0-1.43-.88-2.67-2.19-3.34.46-1.39.2-2.9-.81-3.91s-2.52-1.27-3.91-.81c-.67-1.31-1.91-2.19-3.34-2.19s-2.67.88-3.33 2.19c-1.4-.46-2.9-.2-3.92.81s-1.26 2.52-.8 3.91c-1.31.67-2.2 1.91-2.2 3.34s.89 2.67 2.2 3.34c-.46 1.39-.21 2.9.8 3.91s2.52 1.26 3.91.81c.67 1.31 1.91 2.19 3.34 2.19s2.67-.88 3.34-2.19c1.39.45 2.9.2 3.91-.81s1.27-2.52.81-3.91c1.31-.67 2.19-1.91 2.19-3.34z"></path>
                                                <path fill="#ffffff" d="M10.5 16.5l-3.5-3.5 1.4-1.4 2.1 2.1 5.6-5.6 1.4 1.4-7 7z"></path>
                                            </svg>
                                        @endif
                                    </div>

                                    <!-- Price -->
                                    <div class="mt-2 pt-2 border-t border-gray-100">
                                        <div class="text-base font-bold text-indigo-600">
                                            UGX {{ number_format($listing->price, 0) }}
                                        </div>
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
                    <div class="mt-8">
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                            <div class="flex items-center justify-between mb-5">
                                <h2 class="text-lg font-bold text-gray-800">Browse by Category</h2>
                                <a href="{{ route('categories.index') }}" class="text-indigo-600 hover:text-indigo-700 text-sm font-semibold">
                                    View All <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            </div>
                            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-3">
                                @foreach($categories->take(16) as $category)
                                    <a href="{{ route('marketplace.index', ['category' => $category->id]) }}"
                                       class="group text-center p-3 rounded-xl hover:bg-indigo-50 transition">
                                        <div class="w-10 h-10 mx-auto mb-2 rounded-full bg-gradient-to-br from-indigo-100 to-purple-100 flex items-center justify-center group-hover:from-indigo-500 group-hover:to-purple-500 transition-all">
                                            <i class="fas fa-{{ $category->icon ?? 'tag' }} text-sm text-indigo-600 group-hover:text-white"></i>
                                        </div>
                                        <div class="font-medium text-gray-700 text-xs line-clamp-1 group-hover:text-indigo-600">
                                            {{ $category->name }}
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
                
                <!-- Trust Badges -->
                <div class="mt-8 hidden md:block">
                    <div class="bg-gradient-to-r from-indigo-50 to-purple-50 rounded-2xl p-4">
                        <div class="grid grid-cols-4 gap-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-shield-alt text-green-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-800 text-sm">Secure Escrow</h4>
                                    <p class="text-xs text-gray-500">Protected payments</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-truck text-blue-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-800 text-sm">Fast Delivery</h4>
                                    <p class="text-xs text-gray-500">Nationwide shipping</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-headset text-purple-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-800 text-sm">24/7 Support</h4>
                                    <p class="text-xs text-gray-500">Always here to help</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-undo text-yellow-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-800 text-sm">Easy Returns</h4>
                                    <p class="text-xs text-gray-500">30-day policy</p>
                                </div>
                            </div>
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
    // Mobile filters toggle
    function toggleMobileFilters() {
        const panel = document.getElementById('mobileFiltersPanel');
        const content = document.getElementById('mobileFiltersContent');

        if (panel.classList.contains('hidden')) {
            panel.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            setTimeout(() => {
                content.style.transform = 'translateY(0)';
            }, 10);
        } else {
            content.style.transform = 'translateY(100%)';
            document.body.style.overflow = '';
            setTimeout(() => {
                panel.classList.add('hidden');
            }, 300);
        }
    }

    // Price range slider fix
    document.addEventListener('DOMContentLoaded', function() {
        const slider = document.getElementById('priceSlider');
        const priceLabel = document.getElementById('priceValue');
        const maxInput = document.getElementById('maxPriceInput');

        if (slider) {
            slider.addEventListener('input', function() {
                const val = parseInt(this.value);
                // Update the Blue Label
                priceLabel.textContent = 'UGX ' + val.toLocaleString();
                // Update the Max Price Input Box
                if (maxInput) {
                    maxInput.value = val;
                }
            });
        }
    });
    
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