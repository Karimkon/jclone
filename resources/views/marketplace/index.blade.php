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
                                <button class="w-8 h-8 bg-white rounded-full flex items-center justify-center text-gray-600 hover:text-red-500 shadow">
                                    <i class="fas fa-heart"></i>
                                </button>
                                <button class="w-8 h-8 bg-white rounded-full flex items-center justify-center text-gray-600 hover:text-primary shadow">
                                    <i class="fas fa-eye"></i>
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
                                    <button class="p-2 text-gray-600 hover:text-primary">
                                        <i class="fas fa-shopping-cart text-lg"></i>
                                    </button>
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
    // Price range filter validation
    document.addEventListener('DOMContentLoaded', function() {
        const minPriceInput = document.querySelector('input[name="min_price"]');
        const maxPriceInput = document.querySelector('input[name="max_price"]');
        
        if (minPriceInput && maxPriceInput) {
            const priceForm = minPriceInput.closest('form');
            
            priceForm.addEventListener('submit', function(e) {
                const minPrice = parseFloat(minPriceInput.value) || 0;
                const maxPrice = parseFloat(maxPriceInput.value) || 0;
                
                if (maxPrice > 0 && minPrice > maxPrice) {
                    e.preventDefault();
                    alert('Minimum price cannot be greater than maximum price');
                    minPriceInput.focus();
                }
            });
        }
        
        // Add to cart functionality
        document.querySelectorAll('[data-add-to-cart]').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const listingId = this.getAttribute('data-listing-id');
                
                // Add to cart logic here
                console.log('Adding to cart:', listingId);
                
                // Show success message
                const originalText = this.innerHTML;
                this.innerHTML = '<i class="fas fa-check mr-2"></i> Added!';
                this.classList.remove('bg-primary');
                this.classList.add('bg-green-500');
                this.disabled = true;
                
                setTimeout(() => {
                    this.innerHTML = originalText;
                    this.classList.remove('bg-green-500');
                    this.classList.add('bg-primary');
                    this.disabled = false;
                }, 2000);
            });
        });
        
        // Update product count on filter change
        const filterInputs = document.querySelectorAll('input[name], select[name]');
        filterInputs.forEach(input => {
            input.addEventListener('change', function() {
                // You could add AJAX filtering here
                // For now, just submit the form
                if (this.closest('form')) {
                    this.closest('form').submit();
                }
            });
        });
    });
</script>
@endsection