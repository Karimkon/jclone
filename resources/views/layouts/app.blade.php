<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'JClone Marketplace')</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <style>
        [x-cloak] { display: none !important; }
    </style>
    
    @yield('styles')
    @stack('styles')
</head>
<body class="bg-gray-50">
    <!-- Public Navigation -->
    <nav class="bg-white shadow-md">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <!-- Logo -->
                <a href="{{ route('welcome') }}" class="text-2xl font-bold text-indigo-600">
                    <i class="fas fa-store mr-2"></i>JClone
                </a>

                <!-- Public Navigation -->
                <div class="hidden md:flex items-center space-x-6">
                    <a href="{{ route('welcome') }}" class="text-gray-700 hover:text-indigo-600">Home</a>
                    <a href="{{ route('marketplace.index') }}" class="text-gray-700 hover:text-indigo-600">Marketplace</a>
                    <a href="{{ route('categories.index') }}" class="text-gray-700 hover:text-indigo-600">Categories</a>
                    
                    @auth
                        <!-- User Menu -->
                        <div class="relative inline-block">
                            <button class="flex items-center space-x-2">
                                <i class="fas fa-user-circle text-xl"></i>
                                <span>{{ auth()->user()->name }}</span>
                            </button>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-700 hover:text-indigo-600">Login</a>
                        <a href="{{ route('vendor.login') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg">Sell on JClone</a>
                    @endauth
                </div>

                @auth
<a href="{{ route('chat.index') }}" class="relative p-2 text-gray-600 hover:text-primary transition" title="Messages">
    <i class="fas fa-comment-dots text-xl"></i>
    <span id="chatBadge" class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs font-bold rounded-full hidden items-center justify-center">
        0
    </span>
</a>

<script>
// Update chat badge on page load and periodically
document.addEventListener('DOMContentLoaded', function() {
    updateChatBadge();
    // Update every 30 seconds
    setInterval(updateChatBadge, 30000);
});

async function updateChatBadge() {
    try {
        const response = await fetch('/chat/api/unread-count', {
            headers: {
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            const badge = document.getElementById('chatBadge');
            if (data.unread_count > 0) {
                badge.textContent = data.unread_count > 9 ? '9+' : data.unread_count;
                badge.classList.remove('hidden');
                badge.classList.add('flex');
            } else {
                badge.classList.add('hidden');
                badge.classList.remove('flex');
            }
        }
    } catch (error) {
        console.error('Failed to update chat badge:', error);
    }
}
</script>
@endauth


                <!-- Mobile Menu Button -->
                <button class="md:hidden" id="mobileMenuButton">
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>
            
            <!-- Mobile Menu -->
            <div class="md:hidden hidden" id="mobileMenu">
                <div class="py-4 border-t">
                    <a href="{{ route('welcome') }}" class="block py-2">Home</a>
                    <a href="{{ route('marketplace.index') }}" class="block py-2">Marketplace</a>
                    <a href="{{ route('categories.index') }}" class="block py-2">Categories</a>
                    
                    @auth
                        <div class="pt-4 border-t">
                            <p class="font-medium">{{ auth()->user()->name }}</p>
                            <form action="{{ route('logout') }}" method="POST" class="mt-2">
                                @csrf
                                <button type="submit" class="text-red-600">Logout</button>
                            </form>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="block py-2">Login</a>
                        <a href="{{ route('vendor.login') }}" class="block py-2 bg-indigo-600 text-white px-4 py-2 rounded-lg mt-2 text-center">Sell on JClone</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white mt-12">
        <div class="container mx-auto px-4 py-8">
            <div class="text-center">
                <p>&copy; {{ date('Y') }} JClone Marketplace. All rights reserved.</p>
            </div>
        </div>
    </footer>

   

    @yield('scripts')
    @stack('scripts')
    <script>
// Product Analytics Tracker
class ProductAnalytics {
    constructor(listingId, source = 'direct') {
        this.listingId = listingId;
        this.source = source;
        this.tracked = new Set();
    }

    async track(type, meta = {}) {
        const key = `${type}_${this.listingId}`;
        if (this.tracked.has(key)) return;

        try {
            const response = await fetch('/api/analytics/track', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    listing_id: this.listingId,
                    type: type,
                    source: this.source,
                    meta: meta
                })
            });

            if (response.ok) this.tracked.add(key);
        } catch (error) {
            console.error('Analytics tracking error:', error);
        }
    }

    trackView() { this.track('view'); }
    trackClick() { this.track('click'); }
    trackAddToCart(quantity = 1) { this.track('add_to_cart', { quantity }); }
    trackAddToWishlist() { this.track('add_to_wishlist'); }
    trackShare(platform = 'unknown') { this.track('share', { platform }); }
}

// Auto-track view on product page
@if(isset($listing) && request()->routeIs('marketplace.show'))
document.addEventListener('DOMContentLoaded', () => {
    const analytics = new ProductAnalytics({{ $listing->id }}, '{{ request()->input("source", "direct") }}');
    analytics.trackView();
});
@endif
</script>
</body>
</html>