@extends('layouts.app')

@section('title', 'Categories - ' . config('app.name'))
@section('description', 'Browse products by categories')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Categories Header -->
    <div class="bg-gradient-to-r from-primary to-indigo-600 text-white py-12">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-4xl font-bold mb-4">Browse Categories</h1>
            <p class="text-xl opacity-90 max-w-2xl mx-auto">
                Discover products organized by categories. Find exactly what you're looking for.
            </p>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8">
        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="bg-primary p-3 rounded-full mr-4">
                        <i class="fas fa-tags text-white text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Total Categories</p>
                        <p class="text-2xl font-bold text-gray-800">{{ $categories->count() }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="bg-green-500 p-3 rounded-full mr-4">
                        <i class="fas fa-store text-white text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Active Vendors</p>
                        <p class="text-2xl font-bold text-gray-800">{{ \App\Models\VendorProfile::where('vetting_status', 'approved')->count() }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="bg-blue-500 p-3 rounded-full mr-4">
                        <i class="fas fa-boxes text-white text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Total Products</p>
                        <p class="text-2xl font-bold text-gray-800">{{ \App\Models\Listing::where('is_active', true)->count() }}</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Categories Grid -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">All Categories</h2>
            
            @if($categories->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach($categories as $category)
                <a href="{{ route('categories.show', $category) }}" 
                   class="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition group">
                    <div class="flex flex-col items-center text-center">
                        <!-- Category Icon -->
                        <div class="w-16 h-16 bg-primary/10 text-primary rounded-full flex items-center justify-center mb-4 group-hover:bg-primary group-hover:text-white transition">
                            <i class="fas fa-{{ $category->icon ?? 'tag' }} text-2xl"></i>
                        </div>
                        
                        <!-- Category Name -->
                        <h3 class="text-lg font-bold text-gray-800 mb-2 group-hover:text-primary transition">
                            {{ $category->name }}
                        </h3>
                        
                        <!-- Product Count -->
                        <div class="text-sm text-gray-600 mb-3">
                            {{ $category->listings_count ?? 0 }} products
                        </div>
                        
                        <!-- Description -->
                        @if($category->description)
                        <p class="text-sm text-gray-500 line-clamp-2 mb-4">
                            {{ $category->description }}
                        </p>
                        @endif
                        
                        <!-- View Button -->
                        <div class="mt-4 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg group-hover:bg-primary group-hover:text-white transition">
                            Browse Category
                        </div>
                    </div>
                    
                    <!-- Subcategories -->
                    @if($category->children->count() > 0)
                    <div class="mt-6 pt-6 border-t border-gray-100">
                        <h4 class="text-sm font-semibold text-gray-600 mb-3">Subcategories</h4>
                        <div class="flex flex-wrap gap-2">
                            @foreach($category->children->take(3) as $child)
                            <a href="{{ route('categories.show', $child) }}" 
                               class="px-3 py-1 bg-gray-50 text-gray-700 text-xs rounded-full hover:bg-primary hover:text-white transition">
                                {{ $child->name }}
                            </a>
                            @endforeach
                            @if($category->children->count() > 3)
                            <span class="px-3 py-1 bg-gray-50 text-gray-500 text-xs rounded-full">
                                +{{ $category->children->count() - 3 }} more
                            </span>
                            @endif
                        </div>
                    </div>
                    @endif
                </a>
                @endforeach
            </div>
            @else
            <!-- No Categories -->
            <div class="bg-white rounded-xl shadow-sm p-12 text-center">
                <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-6">
                    <i class="fas fa-tags text-gray-400 text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">No categories found</h3>
                <p class="text-gray-600 mb-6">
                    Categories will appear here once they are added to the system.
                </p>
                @auth
                    @if(auth()->user()->role === 'admin')
                    <a href="{{ route('admin.categories.create') }}" 
                       class="inline-flex items-center px-6 py-3 bg-primary text-white rounded-lg hover:bg-indigo-700 font-medium">
                        <i class="fas fa-plus mr-2"></i> Add Category
                    </a>
                    @endif
                @endauth
            </div>
            @endif
        </div>
        
        <!-- Featured Categories -->
        @php
            $featuredCategories = $categories->where('is_active', true)->take(8);
        @endphp
        @if($featuredCategories->count() > 0)
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Featured Categories</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-8 gap-4">
                @foreach($featuredCategories as $category)
                <a href="{{ route('categories.show', $category) }}" 
                   class="bg-white p-4 rounded-xl shadow-sm hover:shadow-md transition text-center group">
                    <div class="w-12 h-12 bg-primary/10 text-primary rounded-lg flex items-center justify-center mx-auto mb-3 group-hover:bg-primary group-hover:text-white transition">
                        <i class="fas fa-{{ $category->icon ?? 'tag' }}"></i>
                    </div>
                    <div class="font-medium text-gray-800 group-hover:text-primary transition text-sm">
                        {{ $category->name }}
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif
        
        <!-- CTA Section -->
        <div class="bg-gradient-to-r from-primary to-indigo-600 text-white rounded-xl p-8 text-center">
            <h2 class="text-2xl font-bold mb-4">Can't find what you're looking for?</h2>
            <p class="text-lg opacity-90 mb-6 max-w-2xl mx-auto">
                Try searching our marketplace or contact our support team for assistance.
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="{{ route('marketplace.index') }}" 
                   class="px-6 py-3 bg-white text-primary rounded-lg font-bold hover:bg-gray-100">
                    <i class="fas fa-search mr-2"></i> Search Marketplace
                </a>
                <a href="{{ route('vendor.login') }}" 
                   class="px-6 py-3 bg-transparent border-2 border-white text-white rounded-lg font-bold hover:bg-white hover:text-primary transition">
                    <i class="fas fa-store mr-2"></i> Become a Seller
                </a>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
    // Category search functionality
    document.addEventListener('DOMContentLoaded', function() {
        // Add animation to category cards
        const categoryCards = document.querySelectorAll('.bg-white.rounded-xl.shadow-sm');
        categoryCards.forEach((card, index) => {
            card.style.animationDelay = `${index * 0.1}s`;
            card.classList.add('animate-fade-in-up');
        });
        
        // Quick category search
        const searchInput = document.createElement('input');
        searchInput.type = 'text';
        searchInput.placeholder = 'Search categories...';
        searchInput.className = 'w-full md:w-96 px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent';
        
        const categoriesContainer = document.querySelector('.grid.grid-cols-1.sm\\:grid-cols-2');
        if (categoriesContainer) {
            const container = categoriesContainer.parentElement;
            container.insertBefore(searchInput, categoriesContainer);
            
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const cards = categoriesContainer.querySelectorAll('a.bg-white');
                
                cards.forEach(card => {
                    const categoryName = card.querySelector('h3').textContent.toLowerCase();
                    const categoryDesc = card.querySelector('p')?.textContent.toLowerCase() || '';
                    
                    if (categoryName.includes(searchTerm) || categoryDesc.includes(searchTerm)) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        }
    });
</script>

<style>
    @keyframes fade-in-up {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .animate-fade-in-up {
        animation: fade-in-up 0.6s ease-out forwards;
        opacity: 0;
    }
</style>
@endsection
@endsection