<header class="bg-white shadow-sm sticky top-0 z-50">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-between py-3">
            <!-- Logo -->
            <a href="{{ route('welcome') }}" class="flex items-center gap-2">
                <div class="w-10 h-10 bg-gradient-to-br from-brand-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-store text-white"></i>
                </div>
                <div>
                    <span class="text-xl font-bold text-ink-800 font-display">{{ config('app.name') }}</span>
                    <span class="hidden lg:block text-xs text-ink-400 -mt-0.5">Trusted Marketplace</span>
                </div>
            </a>
            
            <!-- Search -->
            <div class="hidden md:flex flex-1 max-w-xl mx-6">
                <form method="GET" action="{{ route('marketplace.index') }}" class="relative w-full">
                    <input type="text" 
                           name="search" 
                           placeholder="Search products, brands, categories..." 
                           class="w-full pl-10 pr-24 py-3 bg-ink-50 border border-ink-200 rounded-xl focus:border-brand-500 focus:bg-white focus:outline-none transition text-sm"
                           value="{{ request('search') ?? '' }}">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-ink-400"></i>
                    <button type="submit" class="absolute right-1.5 top-1/2 -translate-y-1/2 btn-primary px-4 py-2 rounded-lg font-semibold text-sm">Search</button>
                </form>
            </div>
            
            <!-- Actions -->
            <div class="flex items-center gap-3">
                @auth
                <a href="{{ route('buyer.dashboard') }}" class="hidden sm:flex items-center gap-2 text-ink-600 hover:text-brand-600 transition p-2">
                    <i class="fas fa-user text-lg"></i>
                    <span class="text-sm font-medium">Account</span>
                </a>
                @else
                <a href="{{ route('login') }}" class="hidden sm:flex items-center gap-2 text-ink-600 hover:text-brand-600 transition p-2">
                    <i class="fas fa-user text-lg"></i>
                    <span class="text-sm font-medium">Login</span>
                </a>
                @endauth
                
                <a href="{{ route('buyer.wishlist.index') }}" class="relative p-2 text-ink-600 hover:text-coral-500 transition">
                    <i class="fas fa-heart text-lg"></i>
                    <span class="wishlist-count absolute -top-1 -right-1 bg-coral-500 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center font-bold hidden">0</span>
                </a>
                
                <a href="{{ route('buyer.cart.index') }}" class="relative p-2 text-ink-600 hover:text-brand-600 transition">
                    <i class="fas fa-shopping-cart text-lg"></i>
                    <span class="cart-count absolute -top-1 -right-1 bg-brand-600 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center font-bold hidden">0</span>
                </a>
            </div>
        </div>
    </div>
    
    <!-- Navigation -->
    <div class="bg-gradient-to-r from-brand-600 to-purple-600">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between">
                <nav class="hidden lg:flex items-center">
                    <a href="{{ route('marketplace.index') }}" class="text-white px-4 py-3 hover:bg-white/10 transition rounded-lg flex items-center gap-2 text-sm font-medium">
                        <i class="fas fa-fire text-gold-400"></i>Deals
                    </a>
                    <a href="{{ route('marketplace.index', ['origin' => 'imported']) }}" class="text-white px-4 py-3 hover:bg-white/10 transition rounded-lg flex items-center gap-2 text-sm font-medium">
                        <i class="fas fa-plane text-cyan-400"></i>Imported
                    </a>
                    <a href="{{ route('marketplace.index', ['origin' => 'local']) }}" class="text-white px-4 py-3 hover:bg-white/10 transition rounded-lg flex items-center gap-2 text-sm font-medium">
                        <i class="fas fa-map-marker-alt text-mint-400"></i>Local
                    </a>
                    <a href="{{ route('vendor.onboard.create') }}" class="text-white px-4 py-3 hover:bg-white/10 transition rounded-lg flex items-center gap-2 text-sm font-medium">
                        <i class="fas fa-store text-pink-400"></i>Sell
                    </a>
                    
                    <!-- Additional Links -->
                    <div class="ml-auto flex items-center">
                        <a href="{{ route('site.howItWorks') }}" class="text-white px-4 py-3 hover:bg-white/10 transition rounded-lg flex items-center gap-2 text-sm font-medium">
                            <i class="fas fa-question-circle text-yellow-400"></i>How It Works
                        </a>
                        <a href="{{ route('site.vendorBenefits') }}" class="text-white px-4 py-3 hover:bg-white/10 transition rounded-lg flex items-center gap-2 text-sm font-medium">
                            <i class="fas fa-star text-gold-400"></i>Vendor Benefits
                        </a>
                        <a href="{{ route('site.faq') }}" class="text-white px-4 py-3 hover:bg-white/10 transition rounded-lg flex items-center gap-2 text-sm font-medium">
                            <i class="fas fa-comments text-green-400"></i>FAQ
                        </a>
                    </div>
                </nav>
                
                <button class="lg:hidden ml-auto text-white p-2" onclick="toggleMobileMenu()">
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Mobile Search -->
    <div class="md:hidden px-4 py-2 bg-ink-50 border-t">
        <form method="GET" action="{{ route('marketplace.index') }}" class="relative">
            <input type="text" 
                   name="search" 
                   placeholder="Search products..." 
                   class="w-full pl-9 pr-4 py-2.5 bg-white border border-ink-200 rounded-lg focus:border-brand-500 focus:outline-none text-sm"
                   value="{{ request('search') ?? '' }}">
            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-ink-400 text-sm"></i>
        </form>
    </div>
</header>