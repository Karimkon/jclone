<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $category->name }} - {{ config('app.name') }}</title>
    @include('partials.head')
</head>
<body class="bg-ink-50">
    @include('partials.header')
    
    <!-- Category Header -->
    <div class="py-12 text-white" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                <div class="mb-6 md:mb-0">
                    <!-- Breadcrumbs -->
                    <div class="flex items-center text-sm mb-4 opacity-80 font-body">
                        <a href="{{ route('welcome') }}" class="hover:text-white">Home</a>
                        <i class="fas fa-chevron-right mx-2 text-xs"></i>
                        <a href="{{ route('categories.index') }}" class="hover:text-white">Categories</a>
                        @if($category->parent)
                        <i class="fas fa-chevron-right mx-2 text-xs"></i>
                        <a href="{{ route('categories.show', $category->parent) }}" class="hover:text-white">{{ $category->parent->name }}</a>
                        @endif
                        <i class="fas fa-chevron-right mx-2 text-xs"></i>
                        <span class="font-semibold">{{ $category->name }}</span>
                    </div>
                    
                    <h1 class="text-3xl font-bold mb-4 font-display">{{ $category->name }}</h1>
                    @if($category->description)
                    <p class="text-lg opacity-90 max-w-2xl font-body">{{ strip_tags($category->description) }}</p>
                    @endif
                </div>
                
                <!-- Category Stats -->
                <div class="flex space-x-6">
                    <div class="text-center">
                        <div class="text-2xl font-bold">{{ $listings->total() }}</div>
                        <div class="text-sm opacity-80 font-body">Products</div>
                    </div>
                    @if($category->parent)
                    <div class="text-center">
                        <div class="text-2xl font-bold">{{ $subcategories->count() }}</div>
                        <div class="text-sm opacity-80 font-body">Subcategories</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8">
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Sidebar -->
            <div class="lg:w-1/4">
                <div class="bg-white rounded-xl shadow-sm p-6 sticky top-24 border border-ink-100">
                    <!-- Back to Categories -->
                    <a href="{{ route('categories.index') }}" 
                       class="flex items-center text-brand-600 hover:text-brand-700 font-medium mb-6 font-body">
                        <i class="fas fa-arrow-left mr-2"></i> All Categories
                    </a>
                    
                    <!-- Parent Category -->
                    @if($category->parent)
                    <div class="mb-6">
                        <h3 class="text-sm font-semibold text-ink-500 uppercase tracking-wider mb-3 font-body">Parent Category</h3>
                        <a href="{{ route('categories.show', $category->parent) }}" 
                           class="flex items-center p-3 bg-ink-50 rounded-lg hover:bg-brand-600 hover:text-white group transition-all duration-300">
                            <div class="w-10 h-10 bg-brand-100 text-brand-600 rounded-full flex items-center justify-center mr-3 group-hover:bg-white group-hover:text-brand-600 transition-all duration-300">
                                <i class="fas fa-{{ $category->parent->icon ?? 'tag' }}"></i>
                            </div>
                            <div>
                                <div class="font-medium font-body">{{ $category->parent->name }}</div>
                                <div class="text-xs text-ink-500 group-hover:text-white/80 font-body">
                                    {{ $category->parent->listings_count ?? 0 }} products
                                </div>
                            </div>
                        </a>
                    </div>
                    @endif
                    
                    <!-- Subcategories -->
                    @if($subcategories->count() > 0)
                    <div class="mb-6">
                        <h3 class="text-sm font-semibold text-ink-500 uppercase tracking-wider mb-3 font-body">Subcategories</h3>
                        <div class="space-y-2">
                            @foreach($subcategories as $subcategory)
                            <a href="{{ route('categories.show', $subcategory) }}" 
                               class="flex items-center justify-between p-3 rounded-lg hover:bg-ink-50 transition-all duration-300 {{ request()->is('categories/' . $subcategory->slug) ? 'bg-brand-600 text-white hover:bg-brand-700' : 'text-ink-700' }} font-body">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center mr-3">
                                        <i class="fas fa-{{ $subcategory->icon ?? 'tag' }} text-sm"></i>
                                    </div>
                                    <span>{{ $subcategory->name }}</span>
                                </div>
                                <span class="text-xs bg-ink-100 text-ink-600 px-2 py-1 rounded-full {{ request()->is('categories/' . $subcategory->slug) ? 'bg-white/20 text-white' : '' }}">
                                    {{ $subcategory->listings_count ?? 0 }}
                                </span>
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    
                    <!-- Filters -->
                    <div>
                        <h3 class="text-sm font-semibold text-ink-500 uppercase tracking-wider mb-3 font-body">Filters</h3>
                        <div class="space-y-3">
                            <!-- Origin Filter -->
                            <div>
                                <h4 class="text-sm font-medium text-ink-700 mb-2 font-body">Product Origin</h4>
                                <div class="space-y-2">
                                    <a href="{{ route('categories.show', array_merge(['category' => $category], request()->except('origin'))) }}" 
                                       class="block px-3 py-2 rounded-lg hover:bg-ink-50 transition-all duration-300 {{ !request('origin') ? 'bg-brand-600 text-white hover:bg-brand-700' : 'text-ink-700' }} font-body">
                                        All Products
                                    </a>
                                    <a href="{{ route('categories.show', array_merge(['category' => $category], request()->except('origin'), ['origin' => 'local'])) }}" 
                                       class="block px-3 py-2 rounded-lg hover:bg-ink-50 transition-all duration-300 {{ request('origin') == 'local' ? 'bg-emerald-100 text-emerald-800' : 'text-ink-700' }} font-body">
                                        <i class="fas fa-home mr-2"></i> Local Only
                                    </a>
                                    <a href="{{ route('categories.show', array_merge(['category' => $category], request()->except('origin'), ['origin' => 'imported'])) }}" 
                                       class="block px-3 py-2 rounded-lg hover:bg-ink-50 transition-all duration-300 {{ request('origin') == 'imported' ? 'bg-blue-100 text-blue-800' : 'text-ink-700' }} font-body">
                                        <i class="fas fa-plane mr-2"></i> Imported Only
                                    </a>
                                </div>
                            </div>
                            
                            <!-- Sort Options -->
                            <div>
                                <h4 class="text-sm font-medium text-ink-700 mb-2 font-body">Sort By</h4>
                                <div class="space-y-1">
                                    @php
                                        $sortOptions = [
                                            'newest' => 'Newest First',
                                            'price_low' => 'Price: Low to High',
                                            'price_high' => 'Price: High to Low'
                                        ];
                                    @endphp
                                    @foreach($sortOptions as $value => $label)
                                    <a href="{{ route('categories.show', array_merge(['category' => $category], request()->except('sort'), ['sort' => $value])) }}" 
                                       class="block px-3 py-2 rounded-lg hover:bg-ink-50 transition-all duration-300 {{ request('sort', 'newest') == $value ? 'bg-brand-600 text-white hover:bg-brand-700' : 'text-ink-700' }} font-body">
                                        {{ $label }}
                                    </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Clear Filters -->
                    @if(request()->anyFilled(['origin', 'sort']))
                    <div class="mt-6 pt-6 border-t border-ink-200">
                        <a href="{{ route('categories.show', $category) }}" 
                           class="w-full px-4 py-2 border border-ink-300 text-ink-700 rounded-lg hover:bg-ink-50 font-medium text-center block transition-all duration-300 font-body">
                            Clear Filters
                        </a>
                    </div>
                    @endif
                </div>
            </div>
            
            <!-- Products Grid -->
            <div class="lg:w-3/4">
                <!-- Active Filters -->
                @if(request()->anyFilled(['origin', 'sort']))
                <div class="mb-6">
                    <div class="flex flex-wrap gap-2">
                        @if(request('origin'))
                        <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm flex items-center font-body">
                            Origin: {{ ucfirst(request('origin')) }}
                            <a href="{{ route('categories.show', array_merge(['category' => $category], request()->except('origin'))) }}" class="ml-2 text-blue-600 hover:text-blue-800">
                                <i class="fas fa-times"></i>
                            </a>
                        </span>
                        @endif
                        
                        @if(request('sort') && request('sort') != 'newest')
                        <span class="px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-sm flex items-center font-body">
                            Sort: {{ $sortOptions[request('sort')] ?? ucfirst(request('sort')) }}
                            <a href="{{ route('categories.show', array_merge(['category' => $category], request()->except('sort'))) }}" class="ml-2 text-purple-600 hover:text-purple-800">
                                <i class="fas fa-times"></i>
                            </a>
                        </span>
                        @endif
                    </div>
                </div>
                @endif
                
                <!-- Results Header -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-ink-800 font-display">
                            {{ $listings->total() }} Products in {{ $category->name }}
                        </h2>
                        @if($listings->total() > 0)
                        <p class="text-ink-600 font-body">Showing {{ $listings->firstItem() }}-{{ $listings->lastItem() }} of {{ $listings->total() }} products</p>
                        @endif
                    </div>
                </div>
                
                <!-- Products Grid -->
                @if($listings->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($listings as $listing)
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-md transition-all duration-300 border border-ink-100">
                        <div class="relative">
                            <!-- Product Image -->
                            <a href="{{ $listing->category ? route('marketplace.show.category', ['category_slug' => $listing->category->slug, 'listing' => $listing->slug]) : route('marketplace.show', $listing) }}">
                                @if($listing->images->first())
                                <img src="{{ asset('storage/' . $listing->images->first()->path) }}" 
                                     alt="{{ $listing->title }}" 
                                     class="w-full h-48 object-cover">
                                @else
                                <div class="w-full h-48 bg-ink-100 flex items-center justify-center">
                                    <i class="fas fa-image text-ink-400 text-4xl"></i>
                                </div>
                                @endif
                            </a>
                            
                            <!-- Badges -->
                            <div class="absolute top-3 left-3">
                                @if($listing->origin == 'imported')
                                <span class="px-2 py-1 bg-blue-500 text-white text-xs font-bold rounded-full">
                                    <i class="fas fa-plane mr-1"></i> Imported
                                </span>
                                @else
                                <span class="px-2 py-1 bg-emerald-500 text-white text-xs font-bold rounded-full">
                                    <i class="fas fa-home mr-1"></i> Local
                                </span>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Product Info -->
                        <div class="p-4">
                            <!-- Title -->
                            <a href="{{ $listing->category ? route('marketplace.show.category', ['category_slug' => $listing->category->slug, 'listing' => $listing->slug]) : route('marketplace.show', $listing) }}">
                                <h3 class="font-bold text-ink-800 mb-2 line-clamp-1 hover:text-brand-600 transition-colors duration-300 font-display">
                                    {{ $listing->title }}
                                </h3>
                            </a>
                            
                            <!-- Description -->
                            <p class="text-sm text-ink-600 mb-4 line-clamp-2 font-body">
                                {{ $listing->description }}
                            </p>
                            
                            <!-- Price and Actions -->
                            <div class="flex items-center justify-between">
                                <div>
                                   <div class="text-xl font-bold text-brand-600 font-display">
                                        UGX {{ number_format($listing->price, 0) }}
                                    </div>
                                    <div class="text-xs text-ink-500 font-body">
                                        <i class="fas fa-store mr-1"></i> {{ $listing->vendor->business_name ?? 'Vendor' }}
                                    </div>
                                </div>
                                
                                <a href="{{ $listing->category ? route('marketplace.show.category', ['category_slug' => $listing->category->slug, 'listing' => $listing->slug]) : route('marketplace.show', $listing) }}" 
                                   class="px-4 py-2 bg-brand-600 text-white rounded-lg hover:bg-brand-700 font-medium text-sm transition-all duration-300 font-body">
                                    View Details
                                </a>
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
                <!-- No Products -->
                <div class="bg-white rounded-xl shadow-sm p-12 text-center border border-ink-100">
                    <div class="mx-auto w-24 h-24 bg-ink-100 rounded-full flex items-center justify-center mb-6">
                        <i class="fas fa-box-open text-ink-400 text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-ink-800 mb-2 font-display">No products found</h3>
                    <p class="text-ink-600 mb-6 font-body">
                        There are no products in this category yet.
                        @if(request()->anyFilled(['origin']))
                        Try clearing your filters.
                        @endif
                    </p>
                    @if(request()->anyFilled(['origin']))
                    <a href="{{ route('categories.show', $category) }}" 
                       class="px-6 py-3 bg-brand-600 text-white rounded-lg hover:bg-brand-700 font-medium transition-all duration-300 font-body">
                        Clear Filters
                    </a>
                    @endif
                </div>
                @endif
                
                <!-- Related Categories -->
                @if($category->parent && $category->parent->children->count() > 1)
                <div class="mt-12">
                    <h3 class="text-2xl font-bold text-ink-800 mb-6 font-display">Related Categories</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                        @foreach($category->parent->children->where('id', '!=', $category->id)->take(8) as $related)
                        <a href="{{ route('categories.show', $related) }}" 
                           class="bg-white p-4 rounded-xl shadow-sm hover:shadow-md transition-all duration-300 text-center group border border-ink-100">
                            <div class="w-12 h-12 bg-brand-50 text-brand-600 rounded-lg flex items-center justify-center mx-auto mb-3 group-hover:bg-brand-600 group-hover:text-white transition-all duration-300">
                                <i class="fas fa-{{ $related->icon ?? 'tag' }}"></i>
                            </div>
                            <div class="font-medium text-ink-800 group-hover:text-brand-600 transition-colors duration-300 font-display">
                                {{ $related->name }}
                            </div>
                            <div class="text-xs text-ink-500 mt-1 font-body">
                                {{ $related->listings_count ?? 0 }} products
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    @include('partials.footer')

    <script>
        // Filter functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Price range validation
            const priceInputs = document.querySelectorAll('input[type="number"]');
            priceInputs.forEach(input => {
                input.addEventListener('change', function() {
                    const form = this.closest('form');
                    if (form) {
                        const minPrice = parseFloat(form.querySelector('[name="min_price"]')?.value) || 0;
                        const maxPrice = parseFloat(form.querySelector('[name="max_price"]')?.value) || 0;
                        
                        if (maxPrice > 0 && minPrice > maxPrice) {
                            alert('Minimum price cannot be greater than maximum price');
                            this.value = '';
                            this.focus();
                        }
                    }
                });
            });
            
            // Update product count on filter change
            const filterLinks = document.querySelectorAll('a[href*="origin="], a[href*="sort="]');
            filterLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    // For non-AJAX, the link will navigate normally
                    // For AJAX implementation, you could add loading indicator here
                });
            });
        });
    </script>
</body>
</html>