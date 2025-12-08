@extends('layouts.app')

@section('title', 'Marketplace - ' . config('app.name'))
@section('description', 'Browse products from local and international vendors')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Marketplace Header -->
    <div class="bg-gradient-to-r from-primary to-indigo-600 text-white py-8">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                <div class="mb-6 md:mb-0">
                    <h1 class="text-3xl font-bold mb-2">Marketplace</h1>
                    <p class="text-lg opacity-90">Discover amazing products from verified vendors</p>
                </div>
                
                <!-- Quick Stats -->
                <div class="flex space-x-6">
                    <div class="text-center">
                        <div class="text-2xl font-bold">{{ $listings->total() }}</div>
                        <div class="text-sm opacity-80">Products</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold">{{ $categories->count() }}</div>
                        <div class="text-sm opacity-80">Categories</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8">
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Sidebar Filters -->
            <div class="lg:w-1/4">
                <div class="bg-white rounded-xl shadow-sm p-6 sticky top-24">
                    <!-- Search -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Search Products</h3>
                        <form method="GET" action="{{ route('marketplace.index') }}" class="relative">
                            <input type="text" name="search" value="{{ request('search') }}" 
                                   placeholder="What are you looking for?"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            <button type="submit" class="absolute right-3 top-3 text-gray-500 hover:text-primary">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>
                    
                    <!-- Categories -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Categories</h3>
                        <div class="space-y-2">
                            <a href="{{ route('marketplace.index') }}" 
                               class="block px-3 py-2 rounded-lg hover:bg-gray-100 {{ !request('category') ? 'bg-primary text-white hover:bg-indigo-700' : 'text-gray-700' }}">
                                All Categories
                            </a>
                            @foreach($categories as $category)
                            <a href="{{ route('marketplace.index', ['category' => $category->id]) }}" 
                               class="block px-3 py-2 rounded-lg hover:bg-gray-100 {{ request('category') == $category->id ? 'bg-primary text-white hover:bg-indigo-700' : 'text-gray-700' }}">
                                {{ $category->name }}
                                <span class="float-right text-sm opacity-75">{{ $category->listings_count ?? 0 }}</span>
                            </a>
                            @endforeach
                        </div>
                    </div>
                    
                    <!-- Origin Filter -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Product Origin</h3>
                        <div class="space-y-2">
                            <a href="{{ route('marketplace.index', request()->except('origin')) }}" 
                               class="block px-3 py-2 rounded-lg hover:bg-gray-100 {{ !request('origin') ? 'bg-primary text-white hover:bg-indigo-700' : 'text-gray-700' }}">
                                All Products
                            </a>
                            <a href="{{ route('marketplace.index', array_merge(request()->except('origin'), ['origin' => 'local'])) }}" 
                               class="block px-3 py-2 rounded-lg hover:bg-gray-100 {{ request('origin') == 'local' ? 'bg-green-100 text-green-800' : 'text-gray-700' }}">
                                <i class="fas fa-home mr-2"></i> Local Products
                            </a>
                            <a href="{{ route('marketplace.index', array_merge(request()->except('origin'), ['origin' => 'imported'])) }}" 
                               class="block px-3 py-2 rounded-lg hover:bg-gray-100 {{ request('origin') == 'imported' ? 'bg-blue-100 text-blue-800' : 'text-gray-700' }}">
                                <i class="fas fa-plane mr-2"></i> Imported Products
                            </a>
                        </div>
                    </div>
                    
                    <!-- Price Range -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Price Range</h3>
                        <form method="GET" action="{{ route('marketplace.index') }}" class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm text-gray-600 mb-1">Min ($)</label>
                                    <input type="number" name="min_price" value="{{ request('min_price') }}" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-primary">
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-600 mb-1">Max ($)</label>
                                    <input type="number" name="max_price" value="{{ request('max_price') }}" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-primary">
                                </div>
                            </div>
                            <button type="submit" class="w-full px-4 py-2 bg-primary text-white rounded-lg hover:bg-indigo-700 font-medium">
                                Apply Price Filter
                            </button>
                        </form>
                    </div>
                    
                    <!-- Sort Options -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Sort By</h3>
                        <div class="space-y-2">
                            @php
                                $sortOptions = [
                                    'newest' => 'Newest First',
                                    'price_low' => 'Price: Low to High',
                                    'price_high' => 'Price: High to Low',
                                    'popular' => 'Most Popular'
                                ];
                            @endphp
                            @foreach($sortOptions as $value => $label)
                            <a href="{{ route('marketplace.index', array_merge(request()->except('sort'), ['sort' => $value])) }}" 
                               class="block px-3 py-2 rounded-lg hover:bg-gray-100 {{ request('sort', 'newest') == $value ? 'bg-primary text-white hover:bg-indigo-700' : 'text-gray-700' }}">
                                {{ $label }}
                            </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Products Grid -->
            <div class="lg:w-3/4">
                <!-- Active Filters -->
                @if(request()->anyFilled(['search', 'category', 'origin', 'min_price', 'max_price']))
                <div class="mb-6">
                    <div class="flex flex-wrap gap-2 mb-4">
                        @if(request('search'))
                        <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm flex items-center">
                            Search: "{{ request('search') }}"
                            <a href="{{ route('marketplace.index', request()->except('search')) }}" class="ml-2 text-blue-600 hover:text-blue-800">
                                <i class="fas fa-times"></i>
                            </a>
                        </span>
                        @endif
                        
                        @if(request('category') && $selectedCategory = $categories->firstWhere('id', request('category')))
                        <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm flex items-center">
                            Category: {{ $selectedCategory->name }}
                            <a href="{{ route('marketplace.index', request()->except('category')) }}" class="ml-2 text-green-600 hover:text-green-800">
                                <i class="fas fa-times"></i>
                            </a>
                        </span>
                        @endif
                        
                        @if(request('origin'))
                        <span class="px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-sm flex items-center">
                            Origin: {{ ucfirst(request('origin')) }}
                            <a href="{{ route('marketplace.index', request()->except('origin')) }}" class="ml-2 text-purple-600 hover:text-purple-800">
                                <i class="fas fa-times"></i>
                            </a>
                        </span>
                        @endif
                        
                        @if(request('min_price') || request('max_price'))
                        <span class="px-3 py-1 bg-orange-100 text-orange-800 rounded-full text-sm flex items-center">
                            Price: 
                            @if(request('min_price'))${{ request('min_price') }} @endif
                            @if(request('min_price') && request('max_price')) - @endif
                            @if(request('max_price'))${{ request('max_price') }} @endif
                            <a href="{{ route('marketplace.index', request()->except(['min_price', 'max_price'])) }}" class="ml-2 text-orange-600 hover:text-orange-800">
                                <i class="fas fa-times"></i>
                            </a>
                        </span>
                        @endif
                        
                        @if(request()->anyFilled(['search', 'category', 'origin', 'min_price', 'max_price']))
                        <a href="{{ route('marketplace.index') }}" class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm hover:bg-gray-200">
                            Clear All Filters
                        </a>
                        @endif
                    </div>
                </div>
                @endif
                
                <!-- Results Header -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">
                            {{ $listings->total() }} Products Found
                            @if(request('search'))
                            for "{{ request('search') }}"
                            @endif
                        </h2>
                        @if($listings->total() > 0)
                        <p class="text-gray-600">Showing {{ $listings->firstItem() }}-{{ $listings->lastItem() }} of {{ $listings->total() }} products</p>
                        @endif
                    </div>
                    
                    <div class="mt-4 md:mt-0 flex items-center space-x-4">
                        <!-- View Toggle -->
                        <div class="flex border border-gray-300 rounded-lg overflow-hidden">
                            <button class="p-2 bg-white hover:bg-gray-50">
                                <i class="fas fa-th-large text-gray-600"></i>
                            </button>
                            <button class="p-2 bg-gray-100 hover:bg-gray-200">
                                <i class="fas fa-list text-gray-600"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Products Grid -->
                @if($listings->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($listings as $listing)
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-md transition">
                        <div class="relative">
                            <!-- Product Image -->
                            <a href="{{ route('marketplace.show', $listing) }}">
                                @if($listing->images->first())
                                <img src="{{ asset('storage/' . $listing->images->first()->path) }}" 
                                     alt="{{ $listing->title }}" 
                                     class="w-full h-48 object-cover">
                                @else
                                <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                                    <i class="fas fa-image text-gray-400 text-4xl"></i>
                                </div>
                                @endif
                            </a>
                            
                            <!-- Badges -->
                            <div class="absolute top-3 left-3">
                                @if($listing->origin == 'imported')
                                <span class="px-2 py-1 bg-blue-500 text-white text-xs font-bold rounded">
                                    <i class="fas fa-plane mr-1"></i> Imported
                                </span>
                                @else
                                <span class="px-2 py-1 bg-green-500 text-white text-xs font-bold rounded">
                                    <i class="fas fa-home mr-1"></i> Local
                                </span>
                                @endif
                            </div>
                            
                            <!-- Quick Actions -->
                           <div class="absolute top-3 right-3 flex flex-col space-y-2">
                                <button data-quick-wishlist 
                                        data-listing-id="{{ $listing->id }}"
                                        class="w-8 h-8 bg-white rounded-full flex items-center justify-center text-gray-600 hover:text-red-500 shadow transition">
                                    <i class="far fa-heart"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Product Info -->
                        <div class="p-4">
                            <!-- Category -->
                            <div class="mb-2">
                                <span class="text-xs text-gray-500">
                                    {{ $listing->category->name ?? 'Uncategorized' }}
                                </span>
                            </div>
                            
                            <!-- Title -->
                            <a href="{{ route('marketplace.show', $listing) }}">
                                <h3 class="font-bold text-gray-800 mb-2 line-clamp-1 hover:text-primary">
                                    {{ $listing->title }}
                                </h3>
                            </a>
                            
                            <!-- Description -->
                            <p class="text-sm text-gray-600 mb-4 line-clamp-2">
                                {{ $listing->description }}
                            </p>
                            
                            <!-- Vendor Info -->
                            <div class="flex items-center mb-4">
                                <div class="w-6 h-6 bg-primary text-white rounded-full flex items-center justify-center text-xs mr-2">
                                    <i class="fas fa-store"></i>
                                </div>
                                <span class="text-sm text-gray-700">
                                    {{ $listing->vendor->business_name ?? 'Vendor' }}
                                </span>
                            </div>
                            
                            <!-- Price and Actions -->
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-xl font-bold text-primary">
                                        ${{ number_format($listing->price, 2) }}
                                    </div>
                                    @if($listing->weight_kg)
                                    <div class="text-xs text-gray-500">
                                        <i class="fas fa-weight-hanging mr-1"></i> {{ $listing->weight_kg }}kg
                                    </div>
                                    @endif
                                </div>
                                
                                <div class="flex space-x-2">
                                    @if($listing->stock > 0)
                                    <button data-quick-cart 
                                            data-listing-id="{{ $listing->id }}"
                                            class="p-2 text-gray-600 hover:text-primary">
                                        <i class="fas fa-shopping-cart text-lg"></i>
                                    </button>
                                    @else
                                    <button disabled class="p-2 text-gray-400 cursor-not-allowed">
                                        <i class="fas fa-shopping-cart text-lg"></i>
                                    </button>
                                    @endif
                                    <a href="{{ route('marketplace.show', $listing) }}" 
                                    class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-indigo-700 font-medium text-sm">
                                        View Details
                                    </a>
                                </div>
                            </div>
                            
                            <!-- Stock Status -->
                            <div class="mt-4 pt-4 border-t border-gray-100">
                                @if($listing->stock > 10)
                                <div class="text-sm text-green-600">
                                    <i class="fas fa-check-circle mr-1"></i> In Stock ({{ $listing->stock }} available)
                                </div>
                                @elseif($listing->stock > 0)
                                <div class="text-sm text-orange-600">
                                    <i class="fas fa-exclamation-triangle mr-1"></i> Only {{ $listing->stock }} left
                                </div>
                                @else
                                <div class="text-sm text-red-600">
                                    <i class="fas fa-times-circle mr-1"></i> Out of Stock
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
                    {{ $listings->links() }}
                </div>
                @endif
                
                @else
                <!-- No Results -->
                <div class="bg-white rounded-xl shadow-sm p-12 text-center">
                    <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-6">
                        <i class="fas fa-search text-gray-400 text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">No products found</h3>
                    <p class="text-gray-600 mb-6 max-w-md mx-auto">
                        @if(request()->anyFilled(['search', 'category', 'origin', 'min_price', 'max_price']))
                        Try adjusting your filters or search term
                        @else
                        No products are currently available. Check back later!
                        @endif
                    </p>
                    @if(request()->anyFilled(['search', 'category', 'origin', 'min_price', 'max_price']))
                    <a href="{{ route('marketplace.index') }}" 
                       class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-indigo-700 font-medium">
                        Clear All Filters
                    </a>
                    @endif
                </div>
                @endif
                
                <!-- Categories Section -->
                @if(!request()->filled('category') && $categories->count() > 0)
                <div class="mt-12">
                    <h3 class="text-2xl font-bold text-gray-800 mb-6">Browse by Category</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-4">
                        @foreach($categories->take(12) as $category)
                        <a href="{{ route('marketplace.index', ['category' => $category->id]) }}" 
                           class="bg-white p-4 rounded-xl shadow-sm hover:shadow-md transition text-center group">
                            <div class="w-12 h-12 bg-primary/10 text-primary rounded-lg flex items-center justify-center mx-auto mb-3 group-hover:bg-primary group-hover:text-white transition">
                                <i class="fas fa-{{ $category->icon ?? 'tag' }}"></i>
                            </div>
                            <div class="font-medium text-gray-800 group-hover:text-primary transition">
                                {{ $category->name }}
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                {{ $category->listings_count ?? 0 }} products
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- CTA Section -->
    <div class="bg-gradient-to-r from-primary to-indigo-600 text-white py-12">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl font-bold mb-4">Ready to Start Selling?</h2>
            <p class="text-xl opacity-90 mb-8 max-w-2xl mx-auto">
                Join our marketplace and reach thousands of customers with secure payment protection and logistics support.
            </p>
            <a href="{{ route('vendor.login') }}" 
               class="inline-flex items-center px-8 py-4 bg-white text-primary rounded-lg font-bold hover:bg-gray-100 text-lg">
                <i class="fas fa-store mr-3"></i> Become a Vendor
            </a>
        </div>
    </div>
</div>

@section('scripts')
<script>

// Check if user is authenticated - FIXED VERSION
const isAuthenticated = @json(auth()->check());

// Quick add to cart from product cards - FIXED
function quickAddToCart(listingId, button) {
    console.log('quickAddToCart called', listingId, isAuthenticated);
    
    if (!isAuthenticated) {
        showAuthModal();
        return;
    }
    
    const originalHtml = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    button.disabled = true;
    
    fetch(`/buyer/cart/add/${listingId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ quantity: 1 })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Response data:', data);
        
        if (data.success) {
            button.innerHTML = '<i class="fas fa-check"></i>';
            button.classList.remove('text-gray-600');
            button.classList.add('text-green-500');
            
            // Update cart count
            if (data.cart_count) {
                updateCartCount(data.cart_count);
            }
            
            showToast(data.message || 'Added to cart!', 'success');
            
            setTimeout(() => {
                button.innerHTML = originalHtml;
                button.classList.remove('text-green-500');
                button.classList.add('text-gray-600');
                button.disabled = false;
            }, 2000);
        } else {
            button.innerHTML = originalHtml;
            button.disabled = false;
            showToast(data.message || 'Failed to add to cart', 'error');
            
            if (data.redirect) {
                setTimeout(() => window.location.href = data.redirect, 1500);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        button.innerHTML = originalHtml;
        button.disabled = false;
        showToast('Failed to add to cart', 'error');
    });
}

// Quick add to wishlist from product cards - FIXED
function quickAddToWishlist(listingId, button) {
    console.log('quickAddToWishlist called', listingId, isAuthenticated);
    
    if (!isAuthenticated) {
        showAuthModal();
        return;
    }
    
    const icon = button.querySelector('i');
    const isFilled = icon.classList.contains('fas');
    
    fetch(`/buyer/wishlist/toggle/${listingId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        console.log('Wishlist response:', data);
        
        if (data.success) {
            if (data.in_wishlist) {
                // Added to wishlist
                icon.classList.remove('far');
                icon.classList.add('fas');
                button.classList.add('text-red-500');
            } else {
                // Removed from wishlist
                icon.classList.remove('fas');
                icon.classList.add('far');
                button.classList.remove('text-red-500');
            }
            
            // Update wishlist count
            if (data.wishlist_count !== undefined) {
                updateWishlistCount(data.wishlist_count);
            }
            
            showToast(data.message || 'Wishlist updated!', 'success');
        } else {
            showToast(data.message || 'Failed to update wishlist', 'error');
            
            if (data.redirect) {
                setTimeout(() => window.location.href = data.redirect, 1500);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Failed to update wishlist', 'error');
    });
}

// Update cart count in navbar
function updateCartCount(count) {
    console.log('Updating cart count:', count);
    document.querySelectorAll('.cart-count').forEach(element => {
        element.textContent = count;
        if (count > 0) {
            element.classList.remove('hidden');
            element.classList.add('animate-bounce');
            setTimeout(() => element.classList.remove('animate-bounce'), 1000);
        }
    });
}

// Update wishlist count in navbar
function updateWishlistCount(count) {
    console.log('Updating wishlist count:', count);
    document.querySelectorAll('.wishlist-count').forEach(element => {
        element.textContent = count;
        if (count > 0) {
            element.classList.remove('hidden');
        } else {
            element.classList.add('hidden');
        }
    });
}

// Show authentication modal - FIXED
function showAuthModal() {
    console.log('Showing auth modal');
    const modal = document.getElementById('authModal');
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    } else {
        console.error('Auth modal not found!');
        // Fallback: redirect to login
        window.location.href = '/login?redirect=' + encodeURIComponent(window.location.pathname);
    }
}

// Close authentication modal - FIXED
function closeAuthModal() {
    console.log('Closing auth modal');
    const modal = document.getElementById('authModal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = 'auto';
    }
}

// Show toast notification
function showToast(message, type = 'info') {
    const existingToasts = document.querySelectorAll('.custom-toast');
    existingToasts.forEach(toast => toast.remove());
    
    const toast = document.createElement('div');
    toast.className = `custom-toast fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg transform transition-all duration-300`;
    
    const typeStyles = {
        success: 'bg-green-500 text-white',
        error: 'bg-red-500 text-white',
        warning: 'bg-yellow-500 text-white',
        info: 'bg-blue-500 text-white'
    };
    
    toast.className += ` ${typeStyles[type] || typeStyles.info}`;
    
    const icons = {
        success: 'fa-check-circle',
        error: 'fa-times-circle',
        warning: 'fa-exclamation-triangle',
        info: 'fa-info-circle'
    };
    
    toast.innerHTML = `
        <div class="flex items-center">
            <i class="fas ${icons[type] || icons.info} mr-3"></i>
            <span>${message}</span>
            <button class="ml-4 text-white hover:text-gray-200" onclick="this.parentElement.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        if (toast.parentElement) {
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.remove();
                }
            }, 300);
        }
    }, 5000);
}

// Initialize on page load - FIXED
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, isAuthenticated:', isAuthenticated);
    
    // Setup all quick action buttons
    document.querySelectorAll('[data-quick-cart]').forEach(button => {
        console.log('Setting up cart button:', button);
        button.addEventListener('click', function(e) {
            console.log('Cart button clicked');
            e.preventDefault();
            e.stopPropagation();
            const listingId = this.getAttribute('data-listing-id');
            console.log('Listing ID:', listingId);
            quickAddToCart(listingId, this);
        });
    });
    
    document.querySelectorAll('[data-quick-wishlist]').forEach(button => {
        console.log('Setting up wishlist button:', button);
        button.addEventListener('click', function(e) {
            console.log('Wishlist button clicked');
            e.preventDefault();
            e.stopPropagation();
            const listingId = this.getAttribute('data-listing-id');
            console.log('Listing ID:', listingId);
            quickAddToWishlist(listingId, this);
        });
    });
    
    // Close modal on background click
    const authModal = document.getElementById('authModal');
    if (authModal) {
        authModal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeAuthModal();
            }
        });
    }
    
    // Close modal on close button
    const closeButtons = document.querySelectorAll('[onclick="closeAuthModal()"]');
    closeButtons.forEach(button => {
        button.addEventListener('click', closeAuthModal);
    });
    
    // Load cart and wishlist counts if authenticated
    if (isAuthenticated) {
        console.log('User is authenticated, loading counts...');
        
        fetch('/cart/count')
            .then(response => response.json())
            .then(data => {
                console.log('Cart count response:', data);
                if (data.authenticated && data.cart_count > 0) {
                    updateCartCount(data.cart_count);
                }
            })
            .catch(error => console.error('Error fetching cart:', error));
        
        fetch('/wishlist/count')
            .then(response => response.json())
            .then(data => {
                console.log('Wishlist count response:', data);
                if (data.authenticated && data.count > 0) {
                    updateWishlistCount(data.count);
                }
            })
            .catch(error => console.error('Error fetching wishlist:', error));
    }
});
</script>

<!-- Authentication Modal (Add this to both pages if not present) -->
<div id="authModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl max-w-md w-full p-8 relative">
        <button onclick="closeAuthModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
            <i class="fas fa-times text-xl"></i>
        </button>
        
        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-lock text-blue-600 text-2xl"></i>
            </div>
            <h3 class="text-2xl font-bold text-gray-800 mb-2">Sign in Required</h3>
            <p class="text-gray-600">Please sign in or create an account to continue.</p>
        </div>
        
        <div class="space-y-3">
            <a href="{{ route('login') }}?redirect={{ urlencode(url()->current()) }}" 
               class="block w-full px-6 py-3 bg-primary text-white rounded-lg hover:bg-indigo-700 font-bold text-center">
                <i class="fas fa-sign-in-alt mr-2"></i> Sign In
            </a>
            
            <a href="{{ route('register') }}?redirect={{ urlencode(url()->current()) }}" 
               class="block w-full px-6 py-3 border-2 border-primary text-primary rounded-lg hover:bg-primary hover:text-white font-bold text-center">
                <i class="fas fa-user-plus mr-2"></i> Create Account
            </a>
            
            <button onclick="closeAuthModal()" 
                    class="block w-full px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium text-center">
                Cancel
            </button>
        </div>
    </div>
</div>

<style>
.custom-toast {
    animation: slideInRight 0.3s ease-out;
}

@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}
</style>
@endsection