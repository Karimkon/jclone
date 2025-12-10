<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories - {{ config('app.name') }}</title>
    @include('partials.head')
</head>
<body class="bg-ink-50">
    @include('partials.header')
    
    <!-- Categories Header -->
    <div class="py-12 text-white" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-4xl font-bold mb-4 font-display">Browse Categories</h1>
            <p class="text-xl opacity-90 max-w-2xl mx-auto font-body">
                Discover products organized by categories. Find exactly what you're looking for.
            </p>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8">
        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm p-6 border border-ink-100">
                <div class="flex items-center">
                    <div class="bg-brand-600 p-3 rounded-full mr-4">
                        <i class="fas fa-tags text-white text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-ink-500">Total Categories</p>
                        <p class="text-2xl font-bold text-ink-800">{{ $categories->count() }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm p-6 border border-ink-100">
                <div class="flex items-center">
                    <div class="bg-emerald-500 p-3 rounded-full mr-4">
                        <i class="fas fa-store text-white text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-ink-500">Active Vendors</p>
                        <p class="text-2xl font-bold text-ink-800">{{ \App\Models\VendorProfile::where('vetting_status', 'approved')->count() }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm p-6 border border-ink-100">
                <div class="flex items-center">
                    <div class="bg-blue-500 p-3 rounded-full mr-4">
                        <i class="fas fa-boxes text-white text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-ink-500">Total Products</p>
                        <p class="text-2xl font-bold text-ink-800">{{ \App\Models\Listing::where('is_active', true)->count() }}</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Search Bar -->
        <div class="mb-8">
            <div class="relative">
                <input type="text" 
                       id="categorySearch" 
                       placeholder="Search categories..." 
                       class="w-full px-4 py-3 pl-10 border border-ink-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent bg-white text-ink-800 font-body">
                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-ink-400"></i>
            </div>
        </div>
        
        <!-- Categories Grid -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-ink-800 font-display">All Categories</h2>
                <span class="text-sm text-ink-500">{{ $categories->count() }} categories</span>
            </div>
            
            @if($categories->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6" id="categoriesGrid">
                @foreach($categories as $category)
                <div class="category-card bg-white rounded-xl shadow-sm border border-ink-100 p-6 hover:shadow-md transition-all duration-300">
                    <div class="flex flex-col items-center text-center h-full">
                        <!-- Category Icon -->
                        <div class="w-16 h-16 bg-brand-50 text-brand-600 rounded-full flex items-center justify-center mb-4 group-hover:bg-brand-600 group-hover:text-white transition-colors duration-300">
                            <i class="fas fa-{{ $category->icon ?? 'tag' }} text-2xl"></i>
                        </div>
                        
                        <!-- Category Name -->
                        <h3 class="text-lg font-bold text-ink-800 mb-2 font-display">
                            {{ $category->name }}
                        </h3>
                        
                        <!-- Product Count -->
                        <div class="text-sm text-ink-500 mb-3">
                            {{ $category->listings_count ?? 0 }} products
                        </div>
                        
                        <!-- Description -->
                        @if($category->description)
                        <p class="text-sm text-ink-500 line-clamp-2 mb-4 flex-grow font-body">
                            {{ strip_tags($category->description) }}
                        </p>
                        @endif
                        
                        <!-- View Button -->
                        <a href="{{ route('categories.show', $category) }}" 
                           class="mt-4 px-4 py-2 bg-brand-600 text-white rounded-lg hover:bg-brand-700 transition-all duration-300 font-medium w-full font-body">
                            Browse Category
                        </a>
                        
                        <!-- Subcategories -->
                        @if($category->children->count() > 0)
                        <div class="mt-6 pt-6 border-t border-ink-100 w-full">
                            <h4 class="text-sm font-semibold text-ink-600 mb-3 font-body">Popular Subcategories</h4>
                            <div class="flex flex-wrap gap-2">
                                @foreach($category->children->take(3) as $child)
                                <a href="{{ route('categories.show', $child) }}" 
                                   class="px-3 py-1 bg-ink-50 text-ink-700 text-xs rounded-full hover:bg-brand-600 hover:text-white transition-all duration-300 font-body">
                                    {{ $child->name }}
                                </a>
                                @endforeach
                                @if($category->children->count() > 3)
                                <span class="px-3 py-1 bg-ink-50 text-ink-500 text-xs rounded-full font-body">
                                    +{{ $category->children->count() - 3 }} more
                                </span>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <!-- No Categories -->
            <div class="bg-white rounded-xl shadow-sm p-12 text-center border border-ink-100">
                <div class="mx-auto w-24 h-24 bg-ink-100 rounded-full flex items-center justify-center mb-6">
                    <i class="fas fa-tags text-ink-400 text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold text-ink-800 mb-2 font-display">No categories found</h3>
                <p class="text-ink-600 mb-6 font-body">
                    Categories will appear here once they are added to the system.
                </p>
                @auth
                    @if(auth()->user()->role === 'admin')
                    <a href="{{ route('admin.categories.create') }}" 
                       class="inline-flex items-center px-6 py-3 bg-brand-600 text-white rounded-lg hover:bg-brand-700 font-medium font-body">
                        <i class="fas fa-plus mr-2"></i> Add Category
                    </a>
                    @endif
                @endauth
            </div>
            @endif
        </div>
        
        <!-- CTA Section -->
        <div class="rounded-xl p-8 text-center border border-ink-800 text-white" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
            <h2 class="text-2xl font-bold mb-4 font-display">Can't find what you're looking for?</h2>
            <p class="text-lg opacity-90 mb-6 max-w-2xl mx-auto font-body">
                Try searching our marketplace or contact our support team for assistance.
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="{{ route('marketplace.index') }}" 
                   class="px-6 py-3 bg-white text-ink-900 rounded-lg font-bold hover:bg-ink-100 transition-all duration-300 font-body">
                    <i class="fas fa-search mr-2"></i> Search Marketplace
                </a>
                <a href="{{ route('vendor.login') }}" 
                   class="px-6 py-3 bg-transparent border-2 border-white text-white rounded-lg font-bold hover:bg-white hover:text-ink-900 transition-all duration-300 font-body">
                    <i class="fas fa-store mr-2"></i> Become a Seller
                </a>
            </div>
        </div>
    </div>

    @include('partials.footer')

    <!-- Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Search functionality
            const searchInput = document.getElementById('categorySearch');
            const categoryCards = document.querySelectorAll('.category-card');
            
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase().trim();
                    
                    categoryCards.forEach(card => {
                        const categoryName = card.querySelector('h3').textContent.toLowerCase();
                        const categoryDesc = card.querySelector('p')?.textContent.toLowerCase() || '';
                        
                        if (categoryName.includes(searchTerm) || categoryDesc.includes(searchTerm)) {
                            card.style.display = 'block';
                            card.classList.add('animate-fade-in-up');
                        } else {
                            card.style.display = 'none';
                        }
                    });
                    
                    // If empty search, show all
                    if (searchTerm === '') {
                        categoryCards.forEach(card => {
                            card.style.display = 'block';
                            card.classList.add('animate-fade-in-up');
                        });
                    }
                });
            }
            
            // Add animation to cards
            categoryCards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
                card.classList.add('animate-fade-in-up');
            });
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
</body>
</html>