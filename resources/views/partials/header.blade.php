<header class="bg-white shadow-sm sticky top-0 z-50">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-between py-3">
            <!-- Logo -->
            <a href="{{ route('welcome') }}" class="flex items-center gap-3">
                <img src="{{ asset('images/logo.png') }}"
                     alt="{{ config('app.name') }}"
                     class="w-10 h-10 object-contain rounded-lg"
                     onerror="this.src='https://ui-avatars.com/api/?name=B+M&background=6366f1&color=fff'">

                <div>
                    <span class="text-xl font-bold text-gray-900 font-display block leading-none">
                        {{ config('app.name') }}
                    </span>
                    <span class="hidden lg:block text-xs text-gray-400 mt-0.5">
                        Trusted Marketplace
                    </span>
                </div>
            </a>

            <!-- Search (Desktop) -->
            <div class="hidden md:flex flex-1 max-w-xl mx-6">
                <form method="GET" action="{{ route('marketplace.index') }}" class="relative w-full">
                    <input type="text"
                           name="search"
                           placeholder="Search products, brands, categories..."
                           class="w-full pl-10 pr-24 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition text-sm"
                           value="{{ request('search') ?? '' }}">
                    <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <button type="submit" class="absolute right-1.5 top-1/2 -translate-y-1/2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-semibold text-sm transition">
                        Search
                    </button>
                </form>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-2 sm:gap-3">
                @auth
                    @if(auth()->user()->role === 'admin')
                        {{-- Admin: Show dashboard link instead of buyer links --}}
                        <a href="{{ route('admin.dashboard') }}" class="hidden sm:flex items-center gap-2 text-gray-600 hover:text-indigo-600 transition p-2 rounded-lg hover:bg-gray-50">
                            <i class="fas fa-shield-alt text-lg"></i>
                            <span class="text-sm font-medium">Admin Panel</span>
                        </a>
                    @else
                        {{-- Buyer/Vendor: Show account link --}}
                        <a href="{{ route('buyer.dashboard') }}" class="hidden sm:flex items-center gap-2 text-gray-600 hover:text-indigo-600 transition p-2 rounded-lg hover:bg-gray-50">
                            <i class="fas fa-user text-lg"></i>
                            <span class="text-sm font-medium">Account</span>
                        </a>

                        {{-- Wishlist (only for non-admin) --}}
                        <a href="{{ route('buyer.wishlist.index') }}" class="relative p-2 text-gray-600 hover:text-red-500 transition rounded-lg hover:bg-red-50">
                            <i class="fas fa-heart text-lg"></i>
                            <span class="wishlist-count absolute -top-0.5 -right-0.5 bg-red-500 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center font-bold hidden">0</span>
                        </a>

                        {{-- Cart (only for non-admin) --}}
                        <a href="{{ route('buyer.cart.index') }}" class="relative p-2 text-gray-600 hover:text-indigo-600 transition rounded-lg hover:bg-indigo-50">
                            <i class="fas fa-shopping-cart text-lg"></i>
                            <span class="cart-count absolute -top-0.5 -right-0.5 bg-indigo-600 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center font-bold hidden">0</span>
                        </a>
                    @endif
                @else
                    {{-- Guest: Show login button (visible on mobile too) --}}
                    <a href="{{ route('login') }}" class="flex items-center gap-2 text-white bg-indigo-600 hover:bg-indigo-700 transition px-3 py-2 rounded-lg">
                        <i class="fas fa-sign-in-alt text-sm"></i>
                        <span class="text-sm font-semibold">Login</span>
                    </a>
                    <a href="{{ route('register') }}" class="hidden sm:flex items-center gap-2 text-indigo-600 border border-indigo-600 hover:bg-indigo-50 transition px-3 py-2 rounded-lg">
                        <i class="fas fa-user-plus text-sm"></i>
                        <span class="text-sm font-semibold">Register</span>
                    </a>
                @endauth

                <!-- Mobile Menu Toggle -->
                <button class="lg:hidden p-2 text-gray-600 hover:text-indigo-600 transition rounded-lg hover:bg-gray-50" onclick="toggleMobileMenu()">
                    <i class="fas fa-bars text-xl" id="menuIcon"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Navigation Bar -->
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between">
                <nav class="hidden lg:flex items-center -ml-2">
                    <a href="{{ route('marketplace.index') }}" class="text-white/90 hover:text-white px-4 py-3 hover:bg-white/10 transition rounded-lg flex items-center gap-2 text-sm font-medium">
                        <i class="fas fa-fire text-amber-400"></i>Deals
                    </a>
                    <a href="{{ route('marketplace.index', ['origin' => 'imported']) }}" class="text-white/90 hover:text-white px-4 py-3 hover:bg-white/10 transition rounded-lg flex items-center gap-2 text-sm font-medium">
                        <i class="fas fa-plane text-cyan-400"></i>Imported
                    </a>
                    <a href="{{ route('marketplace.index', ['origin' => 'local']) }}" class="text-white/90 hover:text-white px-4 py-3 hover:bg-white/10 transition rounded-lg flex items-center gap-2 text-sm font-medium">
                        <i class="fas fa-map-marker-alt text-emerald-400"></i>Local
                    </a>
                    <a href="{{ route('vendor.onboard.create') }}" class="text-white/90 hover:text-white px-4 py-3 hover:bg-white/10 transition rounded-lg flex items-center gap-2 text-sm font-medium">
                        <i class="fas fa-store text-pink-400"></i>Sell
                    </a>

                    <!-- Additional Links -->
                    <div class="ml-auto flex items-center">
                        <a href="{{ route('site.howItWorks') }}" class="text-white/90 hover:text-white px-4 py-3 hover:bg-white/10 transition rounded-lg flex items-center gap-2 text-sm font-medium">
                            <i class="fas fa-question-circle text-yellow-400"></i>How It Works
                        </a>
                        <a href="{{ route('site.vendorBenefits') }}" class="text-white/90 hover:text-white px-4 py-3 hover:bg-white/10 transition rounded-lg flex items-center gap-2 text-sm font-medium">
                            <i class="fas fa-star text-amber-400"></i>Vendor Benefits
                        </a>
                        <a href="{{ route('site.faq') }}" class="text-white/90 hover:text-white px-4 py-3 hover:bg-white/10 transition rounded-lg flex items-center gap-2 text-sm font-medium">
                            <i class="fas fa-comments text-green-400"></i>FAQ
                        </a>
                    </div>
                </nav>

                <!-- Mobile Category Quick Links -->
                <div class="lg:hidden flex items-center gap-1 py-2 overflow-x-auto scrollbar-hide -mx-4 px-4">
                    <a href="{{ route('marketplace.index') }}" class="text-white/90 hover:text-white px-3 py-1.5 bg-white/10 rounded-full text-xs font-medium whitespace-nowrap">
                        <i class="fas fa-fire text-amber-400 mr-1"></i>Deals
                    </a>
                    <a href="{{ route('marketplace.index', ['origin' => 'imported']) }}" class="text-white/90 hover:text-white px-3 py-1.5 bg-white/10 rounded-full text-xs font-medium whitespace-nowrap">
                        <i class="fas fa-plane text-cyan-400 mr-1"></i>Imported
                    </a>
                    <a href="{{ route('marketplace.index', ['origin' => 'local']) }}" class="text-white/90 hover:text-white px-3 py-1.5 bg-white/10 rounded-full text-xs font-medium whitespace-nowrap">
                        <i class="fas fa-map-marker-alt text-emerald-400 mr-1"></i>Local
                    </a>
                    <a href="{{ route('categories.index') }}" class="text-white/90 hover:text-white px-3 py-1.5 bg-white/10 rounded-full text-xs font-medium whitespace-nowrap">
                        <i class="fas fa-th-large text-purple-300 mr-1"></i>Categories
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Search -->
    <div class="md:hidden px-4 py-3 bg-gray-50 border-t border-gray-100">
        <form method="GET" action="{{ route('marketplace.index') }}" class="relative">
            <input type="text"
                   name="search"
                   placeholder="Search products..."
                   class="w-full pl-10 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 text-sm"
                   value="{{ request('search') ?? '' }}">
            <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
        </form>
    </div>
</header>

<!-- Mobile Menu Overlay -->
<div id="mobileMenuOverlay" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[60] hidden" onclick="toggleMobileMenu()"></div>

<!-- Mobile Menu Slide-in -->
<div id="mobileMenu" class="fixed top-0 right-0 w-80 max-w-[85%] h-full bg-white z-[70] transform translate-x-full transition-transform duration-300 ease-out shadow-2xl">
    <!-- Header -->
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-5 py-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-store text-white"></i>
                </div>
                <div>
                    <span class="text-white font-bold">{{ config('app.name') }}</span>
                    <p class="text-white/70 text-xs">Menu</p>
                </div>
            </div>
            <button onclick="toggleMobileMenu()" class="p-2 text-white/80 hover:text-white transition">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
    </div>

    <!-- User Section -->
    <div class="p-4 border-b border-gray-100">
        @auth
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center">
                @if(Auth::user()->avatar)
                    <img src="{{ asset('storage/' . Auth::user()->avatar) }}" class="w-12 h-12 rounded-full object-cover">
                @else
                    @if(Auth::user()->role === 'admin')
                        <i class="fas fa-shield-alt text-indigo-600 text-lg"></i>
                    @else
                        <i class="fas fa-user text-indigo-600 text-lg"></i>
                    @endif
                @endif
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-semibold text-gray-800 truncate">{{ Auth::user()->name }}</p>
                @if(Auth::user()->role === 'admin')
                    <span class="text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded-full font-medium">Administrator</span>
                @else
                    <a href="{{ route('buyer.dashboard') }}" class="text-sm text-indigo-600 hover:underline">View Dashboard</a>
                @endif
            </div>
        </div>

        @if(Auth::user()->role === 'admin')
        {{-- Admin Quick Links --}}
        <div class="mt-3 grid grid-cols-2 gap-2">
            <a href="{{ route('admin.dashboard') }}" class="py-2 bg-indigo-600 text-white rounded-lg text-center font-medium text-sm hover:bg-indigo-700 transition">
                <i class="fas fa-tachometer-alt mr-1"></i> Dashboard
            </a>
            <a href="{{ route('admin.vendors.pending') }}" class="py-2 bg-yellow-500 text-white rounded-lg text-center font-medium text-sm hover:bg-yellow-600 transition">
                <i class="fas fa-store mr-1"></i> Vendors
            </a>
        </div>
        @endif
        @else
        <div class="flex gap-3">
            <a href="{{ route('login') }}" class="flex-1 py-2.5 bg-indigo-600 text-white rounded-xl text-center font-medium text-sm hover:bg-indigo-700 transition">
                Sign In
            </a>
            <a href="{{ route('register') }}" class="flex-1 py-2.5 border-2 border-indigo-600 text-indigo-600 rounded-xl text-center font-medium text-sm hover:bg-indigo-50 transition">
                Register
            </a>
        </div>
        @endauth
    </div>

    <!-- Navigation Links -->
    <nav class="py-2 overflow-y-auto" style="max-height: calc(100vh - 200px);">
        <div class="px-3">
            <p class="px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wide">Shop</p>
        </div>

        <a href="{{ route('marketplace.index') }}" class="flex items-center gap-3 px-6 py-3 text-gray-700 hover:bg-gray-50 transition" onclick="toggleMobileMenu()">
            <i class="fas fa-shopping-bag text-indigo-500 w-5"></i>
            <span class="font-medium">Marketplace</span>
        </a>
        <a href="{{ route('marketplace.index') }}" class="flex items-center gap-3 px-6 py-3 text-gray-700 hover:bg-gray-50 transition" onclick="toggleMobileMenu()">
            <i class="fas fa-fire text-orange-500 w-5"></i>
            <span class="font-medium">Hot Deals</span>
        </a>
        <a href="{{ route('categories.index') }}" class="flex items-center gap-3 px-6 py-3 text-gray-700 hover:bg-gray-50 transition" onclick="toggleMobileMenu()">
            <i class="fas fa-th-large text-purple-500 w-5"></i>
            <span class="font-medium">Categories</span>
        </a>

        <div class="px-3 mt-4">
            <p class="px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wide">Browse By</p>
        </div>

        <a href="{{ route('marketplace.index', ['origin' => 'imported']) }}" class="flex items-center gap-3 px-6 py-3 text-gray-700 hover:bg-gray-50 transition" onclick="toggleMobileMenu()">
            <i class="fas fa-plane text-cyan-500 w-5"></i>
            <span class="font-medium">Imported Products</span>
        </a>
        <a href="{{ route('marketplace.index', ['origin' => 'local']) }}" class="flex items-center gap-3 px-6 py-3 text-gray-700 hover:bg-gray-50 transition" onclick="toggleMobileMenu()">
            <i class="fas fa-map-marker-alt text-emerald-500 w-5"></i>
            <span class="font-medium">Local Products</span>
        </a>

        <div class="px-3 mt-4">
            <p class="px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wide">Services</p>
        </div>

        <a href="{{ route('jobs.index') }}" class="flex items-center gap-3 px-6 py-3 text-gray-700 hover:bg-gray-50 transition" onclick="toggleMobileMenu()">
            <i class="fas fa-briefcase text-blue-500 w-5"></i>
            <span class="font-medium">Jobs</span>
        </a>
        <a href="{{ route('services.index') }}" class="flex items-center gap-3 px-6 py-3 text-gray-700 hover:bg-gray-50 transition" onclick="toggleMobileMenu()">
            <i class="fas fa-tools text-green-500 w-5"></i>
            <span class="font-medium">Services</span>
        </a>

        <div class="px-3 mt-4">
            <p class="px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wide">Help & Info</p>
        </div>

        <a href="{{ route('site.howItWorks') }}" class="flex items-center gap-3 px-6 py-3 text-gray-700 hover:bg-gray-50 transition" onclick="toggleMobileMenu()">
            <i class="fas fa-question-circle text-yellow-500 w-5"></i>
            <span class="font-medium">How It Works</span>
        </a>
        <a href="{{ route('site.vendorBenefits') }}" class="flex items-center gap-3 px-6 py-3 text-gray-700 hover:bg-gray-50 transition" onclick="toggleMobileMenu()">
            <i class="fas fa-star text-amber-500 w-5"></i>
            <span class="font-medium">Vendor Benefits</span>
        </a>
        <a href="{{ route('site.faq') }}" class="flex items-center gap-3 px-6 py-3 text-gray-700 hover:bg-gray-50 transition" onclick="toggleMobileMenu()">
            <i class="fas fa-comments text-teal-500 w-5"></i>
            <span class="font-medium">FAQ</span>
        </a>
        @auth
            @if(Auth::user()->isVendor())
            <a href="{{ route('vendor.dashboard') }}" class="flex items-center gap-3 px-6 py-3 text-gray-700 hover:bg-gray-50 transition" onclick="toggleMobileMenu()">
                <i class="fas fa-store text-pink-500 w-5"></i>
                <span class="font-medium">Vendor Dashboard</span>
            </a>
            @else
            <a href="{{ route('vendor.onboard.create') }}" class="flex items-center gap-3 px-6 py-3 text-gray-700 hover:bg-gray-50 transition" onclick="toggleMobileMenu()">
                <i class="fas fa-store text-pink-500 w-5"></i>
                <span class="font-medium">Become a Seller</span>
            </a>
            @endif
        @else
        <a href="{{ route('vendor.onboard.create') }}" class="flex items-center gap-3 px-6 py-3 text-gray-700 hover:bg-gray-50 transition" onclick="toggleMobileMenu()">
            <i class="fas fa-store text-pink-500 w-5"></i>
            <span class="font-medium">Become a Seller</span>
        </a>
        @endauth

        @auth
        <div class="px-3 mt-4 border-t border-gray-100 pt-4">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full flex items-center gap-3 px-6 py-3 text-red-600 hover:bg-red-50 transition rounded-lg">
                    <i class="fas fa-sign-out-alt w-5"></i>
                    <span class="font-medium">Logout</span>
                </button>
            </form>
        </div>
        @endauth
    </nav>
</div>

<style>
    .scrollbar-hide::-webkit-scrollbar {
        display: none;
    }
    .scrollbar-hide {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>

<script>
function toggleMobileMenu() {
    const menu = document.getElementById('mobileMenu');
    const overlay = document.getElementById('mobileMenuOverlay');
    const icon = document.getElementById('menuIcon');

    if (menu.classList.contains('translate-x-full')) {
        menu.classList.remove('translate-x-full');
        menu.classList.add('translate-x-0');
        overlay.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        icon.classList.remove('fa-bars');
        icon.classList.add('fa-times');
    } else {
        menu.classList.remove('translate-x-0');
        menu.classList.add('translate-x-full');
        overlay.classList.add('hidden');
        document.body.style.overflow = '';
        icon.classList.remove('fa-times');
        icon.classList.add('fa-bars');
    }
}

// Update cart and wishlist counts
document.addEventListener('DOMContentLoaded', function() {
    updateCartCount();
    updateWishlistCount();
});

function updateCartCount() {
    fetch('/api/cart/count')
        .then(r => r.json())
        .then(data => {
            document.querySelectorAll('.cart-count').forEach(el => {
                if (data.count > 0) {
                    el.textContent = data.count > 99 ? '99+' : data.count;
                    el.classList.remove('hidden');
                } else {
                    el.classList.add('hidden');
                }
            });
        })
        .catch(() => {});
}

function updateWishlistCount() {
    fetch('/api/wishlist/count')
        .then(r => r.json())
        .then(data => {
            document.querySelectorAll('.wishlist-count').forEach(el => {
                if (data.count > 0) {
                    el.textContent = data.count > 99 ? '99+' : data.count;
                    el.classList.remove('hidden');
                } else {
                    el.classList.add('hidden');
                }
            });
        })
        .catch(() => {});
}
</script>
