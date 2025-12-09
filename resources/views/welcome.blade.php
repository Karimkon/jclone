<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} - Buy & Sell Everything</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#4f46e5',
                        secondary: '#10b981',
                        accent: '#f59e0b'
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.6s ease-out',
                        'slide-up': 'slideUp 0.8s ease-out',
                        'scale-in': 'scaleIn 0.5s ease-out',
                        'float': 'float 3s ease-in-out infinite',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' }
                        },
                        slideUp: {
                            '0%': { transform: 'translateY(30px)', opacity: '0' },
                            '100%': { transform: 'translateY(0)', opacity: '1' }
                        },
                        scaleIn: {
                            '0%': { transform: 'scale(0.9)', opacity: '0' },
                            '100%': { transform: 'scale(1)', opacity: '1' }
                        },
                        float: {
                            '0%, 100%': { transform: 'translateY(0px)' },
                            '50%': { transform: 'translateY(-20px)' }
                        }
                    }
                }
            }
        }
    </script>
    
    <style>
        /* Performance optimizations */
        * {
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        /* Smooth transitions */
        .smooth-transition {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        /* Glassmorphism effect */
        .glass {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        /* Gradient backgrounds */
        .hero-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
            overflow: hidden;
        }
        
        .hero-gradient::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 50px 50px;
            animation: grid-move 20s linear infinite;
        }
        
        @keyframes grid-move {
            0% { transform: translate(0, 0); }
            100% { transform: translate(50px, 50px); }
        }
        
        /* Card hover effects */
        .card-hover {
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        }
        
        .card-hover:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        
        /* Category card effects */
        .category-card {
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .category-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s;
        }
        
        .category-card:hover::before {
            left: 100%;
        }
        
        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(79, 70, 229, 0.2);
        }
        
        /* Product card */
        .product-card {
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            position: relative;
        }
        
        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
        }
        
        .product-card img {
            transition: transform 0.4s ease;
        }
        
        .product-card:hover img {
            transform: scale(1.1);
        }
        
        /* Button animations */
        .btn-primary {
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .btn-primary::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255,255,255,0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }
        
        .btn-primary:hover::before {
            width: 300px;
            height: 300px;
        }
        
        /* Smooth scroll */
        html {
            scroll-behavior: smooth;
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 10px;
            height: 10px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #4f46e5;
            border-radius: 5px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #4338ca;
        }
        
        /* Trust badge pulse */
        .trust-badge {
            animation: badge-pulse 2s ease-in-out infinite;
        }
        
        @keyframes badge-pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        /* Newsletter input */
        .newsletter-input:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Top Navigation -->
    <nav class="bg-white shadow-lg sticky top-0 z-50 animate-slide-up">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <!-- Logo -->
                <div class="flex items-center space-x-2 animate-fade-in">
                    <a href="{{ route('welcome') }}" class="text-2xl font-bold text-primary smooth-transition hover:scale-110">
                        <i class="fas fa-store mr-2"></i>{{ config('app.name') }}
                    </a>
                    <span class="text-xs bg-gradient-to-r from-primary to-purple-600 text-white px-2 py-1 rounded-full animate-pulse">Beta</span>
                </div>

                <!-- Desktop Navigation -->
                <div class="hidden lg:flex items-center space-x-8">
                    <a href="{{ route('marketplace.index') }}" class="text-gray-700 hover:text-primary font-medium smooth-transition hover:scale-105">
                        <i class="fas fa-th-large mr-2"></i>Marketplace
                    </a>
                    <a href="#how-it-works" class="text-gray-700 hover:text-primary font-medium smooth-transition hover:scale-105">
                        <i class="fas fa-play-circle mr-2"></i>How it Works
                    </a>
                    <a href="#import-calculator" class="text-gray-700 hover:text-primary font-medium smooth-transition hover:scale-105">
                        <i class="fas fa-calculator mr-2"></i>Calculator
                    </a>
                    <a href="#categories" class="text-gray-700 hover:text-primary font-medium smooth-transition hover:scale-105">
                        <i class="fas fa-tags mr-2"></i>Categories
                    </a>
                </div>

                <!-- User Actions -->
                <div class="flex items-center space-x-4">
                    @auth
                        @if(auth()->user()->isVendor())
                            <a href="{{ route('vendor.dashboard') }}" class="hidden md:inline-flex bg-gradient-to-r from-primary to-indigo-600 text-white px-6 py-2 rounded-lg font-medium hover:shadow-lg smooth-transition">
                                <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                            </a>
                        @elseif(auth()->user()->isInVendorOnboarding())
                            <a href="{{ route('vendor.onboard.status') }}" class="hidden md:inline-flex bg-yellow-600 text-white px-6 py-2 rounded-lg font-medium hover:shadow-lg smooth-transition">
                                <i class="fas fa-clipboard-check mr-2"></i>Status
                            </a>
                        @elseif(auth()->user()->isBuyer())
                            <a href="{{ route('buyer.cart.index') }}" class="relative text-gray-700 hover:text-primary smooth-transition">
                                <i class="fas fa-shopping-cart text-xl"></i>
                                <span class="cart-count absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center hidden">0</span>
                            </a>
                            
                            <a href="{{ route('buyer.wishlist.index') }}" class="relative text-gray-700 hover:text-primary smooth-transition">
                                <i class="fas fa-heart text-xl"></i>
                                <span class="wishlist-count absolute -top-2 -right-2 bg-pink-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center hidden">0</span>
                            </a>
                            
                            <a href="{{ route('buyer.dashboard') }}" class="hidden md:inline-flex text-gray-700 hover:text-primary font-medium smooth-transition">
                                <i class="fas fa-user-circle mr-2"></i>Account
                            </a>
                        @endif
                    @else
                        <button class="relative text-gray-700 hover:text-primary smooth-transition">
                            <i class="fas fa-shopping-cart text-xl"></i>
                            <span class="cart-count absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center hidden">0</span>
                        </button>
                        
                        <a href="{{ route('login') }}" class="text-gray-700 hover:text-primary font-medium smooth-transition hover:scale-105">
                            <i class="fas fa-sign-in-alt mr-2"></i>Login
                        </a>
                    @endauth
                    
                    <a href="{{ route('vendor.onboard.create') }}" class="btn-primary bg-gradient-to-r from-primary to-indigo-600 text-white px-6 py-2 rounded-lg font-medium hover:shadow-lg hidden md:inline-flex">
                        <i class="fas fa-store mr-2"></i>Sell Now
                    </a>
                    
                    <!-- Mobile Menu Button -->
                    <button class="lg:hidden text-gray-700" onclick="toggleMobileMenu()">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-gradient text-white py-20 lg:py-32 relative overflow-hidden">
        <div class="container mx-auto px-4 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div class="animate-slide-up">
                    <div class="inline-block mb-4">
                        <span class="bg-white/20 backdrop-blur-md text-white px-4 py-2 rounded-full text-sm font-medium">
                            🎉 Trusted by 10,000+ Users
                        </span>
                    </div>
                    
                    <h1 class="text-4xl md:text-6xl font-bold mb-6 leading-tight">
                        Buy Local & Imported Products
                        <span class="text-yellow-300 block mt-2">with Confidence</span>
                    </h1>
                    
                    <p class="text-xl mb-8 opacity-90 leading-relaxed">
                        Secure marketplace with escrow protection, integrated logistics, and customs clearance for hassle-free shopping.
                    </p>
                    
                    <div class="flex flex-wrap gap-4 mb-12">
                        <a href="{{ route('marketplace.index') }}" class="btn-primary bg-white text-primary px-8 py-4 rounded-xl font-semibold hover:shadow-2xl smooth-transition flex items-center group">
                            <i class="fas fa-shopping-bag mr-2 group-hover:scale-110 smooth-transition"></i> 
                            Start Shopping
                        </a>
                        
                        <a href="{{ route('vendor.onboard.create') }}" class="glass border-2 border-white text-white px-8 py-4 rounded-xl font-semibold hover:bg-white hover:text-primary smooth-transition flex items-center group">
                            <i class="fas fa-store mr-2 group-hover:scale-110 smooth-transition"></i> 
                            Start Selling
                        </a>
                    </div>
                    
                    <!-- Trust Indicators -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6 animate-fade-in">
                        <div class="text-center trust-badge">
                            <div class="text-3xl font-bold mb-1">10K+</div>
                            <div class="text-sm opacity-80">Happy Customers</div>
                        </div>
                        <div class="text-center trust-badge">
                            <div class="text-3xl font-bold mb-1">{{ $categories->count() }}+</div>
                            <div class="text-sm opacity-80">Categories</div>
                        </div>
                        <div class="text-center trust-badge">
                            <div class="text-3xl font-bold mb-1">24/7</div>
                            <div class="text-sm opacity-80">Support</div>
                        </div>
                        <div class="text-center trust-badge">
                            <div class="text-3xl font-bold mb-1">100%</div>
                            <div class="text-sm opacity-80">Secure</div>
                        </div>
                    </div>
                </div>
                
                <!-- Hero Image with Floating Cards -->
                <div class="relative animate-scale-in">
                    <div class="relative z-10 float-animation">
                        <img src="https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?w=800&q=80" 
                             alt="Shopping" class="rounded-2xl shadow-2xl">
                    </div>
                    
                    <!-- Floating Card 1 -->
                    <div class="absolute -top-6 -left-6 glass p-4 rounded-xl shadow-2xl animate-float z-20" style="animation-delay: 0.2s">
                        <div class="flex items-center">
                            <div class="bg-primary text-white p-3 rounded-xl mr-3">
                                <i class="fas fa-shield-alt text-xl"></i>
                            </div>
                            <div>
                                <div class="font-bold text-white text-sm">Escrow Protection</div>
                                <div class="text-xs text-white/80">Money safe until delivery</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Floating Card 2 -->
                    <div class="absolute -bottom-6 -right-6 glass p-4 rounded-xl shadow-2xl animate-float z-20" style="animation-delay: 0.5s">
                        <div class="flex items-center">
                            <div class="bg-secondary text-white p-3 rounded-xl mr-3">
                                <i class="fas fa-truck text-xl"></i>
                            </div>
                            <div>
                                <div class="font-bold text-white text-sm">Fast Delivery</div>
                                <div class="text-xs text-white/80">Doorstep delivery</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section id="categories" class="py-20 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12 animate-slide-up">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">Shop by Category</h2>
                <p class="text-gray-600 max-w-2xl mx-auto text-lg">
                    Browse through our wide range of categories from local artisans and imported goods
                </p>
            </div>
            
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-6">
                @php
                    $categoryColors = [
                        'blue' => ['from' => 'from-blue-50', 'to' => 'to-blue-100', 'icon' => 'text-primary'],
                        'pink' => ['from' => 'from-pink-50', 'to' => 'to-pink-100', 'icon' => 'text-pink-600'],
                        'green' => ['from' => 'from-green-50', 'to' => 'to-green-100', 'icon' => 'text-green-600'],
                        'purple' => ['from' => 'from-purple-50', 'to' => 'to-purple-100', 'icon' => 'text-purple-600'],
                        'yellow' => ['from' => 'from-yellow-50', 'to' => 'to-yellow-100', 'icon' => 'text-yellow-600'],
                        'red' => ['from' => 'from-red-50', 'to' => 'to-red-100', 'icon' => 'text-red-600'],
                    ];
                    $colorKeys = array_keys($categoryColors);
                @endphp
                
                @foreach($categories->take(12) as $index => $category)
                    @php
                        $color = $categoryColors[$colorKeys[$index % count($colorKeys)]];
                    @endphp
                    <a href="{{ route('marketplace.index', ['category' => $category->id]) }}" 
                       class="category-card bg-gradient-to-br {{ $color['from'] }} {{ $color['to'] }} rounded-2xl p-6 text-center cursor-pointer">
                        <div class="bg-white w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                            <i class="fas fa-{{ $category->icon ?? 'tag' }} {{ $color['icon'] }} text-2xl"></i>
                        </div>
                        <h3 class="font-semibold text-gray-800 mb-1">{{ $category->name }}</h3>
                        <p class="text-xs text-gray-500">{{ $category->listings_count ?? 0 }} products</p>
                    </a>
                @endforeach
            </div>
            
            <div class="text-center mt-10">
                <a href="{{ route('marketplace.index') }}" class="inline-flex items-center text-primary font-semibold hover:text-indigo-700 smooth-transition text-lg">
                    View All Categories 
                    <i class="fas fa-arrow-right ml-2 smooth-transition hover:translate-x-2"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Featured Products Section -->
    <section class="py-20 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center mb-12">
                <div class="animate-slide-up">
                    <h2 class="text-4xl font-bold text-gray-800 mb-2">🔥 Trending Products</h2>
                    <p class="text-gray-600 text-lg">Hot deals you don't want to miss</p>
                </div>
                <a href="{{ route('marketplace.index') }}" class="hidden md:inline-flex items-center text-primary font-semibold hover:text-indigo-700 smooth-transition">
                    View All <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                @foreach($newArrivals as $listing)
                <div class="product-card bg-white rounded-2xl shadow-lg overflow-hidden">
                    <div class="relative overflow-hidden h-64">
                        <a href="{{ route('marketplace.show', $listing) }}">
                            @if($listing->images->first())
                            <img src="{{ asset('storage/' . $listing->images->first()->path) }}" 
                                 alt="{{ $listing->title }}" class="w-full h-full object-cover">
                            @else
                            <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                                <i class="fas fa-image text-gray-400 text-4xl"></i>
                            </div>
                            @endif
                        </a>
                        
                        <div class="absolute top-4 left-4">
                            @if($listing->origin == 'imported')
                            <span class="bg-blue-500 text-white text-xs font-bold px-3 py-1 rounded-full">
                                <i class="fas fa-plane mr-1"></i>Imported
                            </span>
                            @else
                            <span class="bg-green-500 text-white text-xs font-bold px-3 py-1 rounded-full">
                                <i class="fas fa-home mr-1"></i>Local
                            </span>
                            @endif
                        </div>
                        
                        <button data-quick-wishlist 
                                data-listing-id="{{ $listing->id }}"
                                class="absolute top-4 right-4 bg-white w-10 h-10 rounded-full flex items-center justify-center shadow-lg hover:bg-red-50 smooth-transition">
                            <i class="far fa-heart text-gray-600 hover:text-red-500"></i>
                        </button>
                    </div>
                    
                    <div class="p-6">
                        <div class="text-xs text-gray-500 mb-2">{{ $listing->category->name ?? 'Uncategorized' }}</div>
                        <a href="{{ route('marketplace.show', $listing) }}">
                            <h3 class="font-bold text-lg text-gray-800 mb-2 line-clamp-2 hover:text-primary smooth-transition">
                                {{ $listing->title }}
                            </h3>
                        </a>
                        <p class="text-sm text-gray-600 mb-4 line-clamp-2">
                            {{ $listing->description }}
                        </p>
                        
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <span class="text-2xl font-bold text-primary">${{ number_format($listing->price, 2) }}</span>
                            </div>
                            <div class="flex items-center text-yellow-400">
                                <i class="fas fa-star text-xs"></i>
                                <span class="text-sm text-gray-600 ml-1">{{ number_format(rand(40, 50) / 10, 1) }}</span>
                            </div>
                        </div>
                        
                        @if($listing->stock > 0)
                        <button data-quick-cart 
                                data-listing-id="{{ $listing->id }}"
                                class="w-full bg-gradient-to-r from-primary to-indigo-600 text-white py-3 rounded-xl font-semibold hover:shadow-lg smooth-transition">
                            <i class="fas fa-shopping-cart mr-2"></i>Add to Cart
                        </button>
                        @else
                        <button disabled class="w-full bg-gray-300 text-gray-500 py-3 rounded-xl font-semibold cursor-not-allowed">
                            <i class="fas fa-ban mr-2"></i>Out of Stock
                        </button>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="py-20 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16 animate-slide-up">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">🎯 How It Works</h2>
                <p class="text-gray-600 max-w-2xl mx-auto text-lg">
                    Simple steps to buy and sell on our secure marketplace
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- For Buyers -->
                <div class="card-hover bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl p-8">
                    <div class="flex items-center mb-6">
                        <div class="bg-gradient-to-br from-primary to-indigo-600 text-white w-12 h-12 rounded-xl flex items-center justify-center mr-4 shadow-lg">
                            <i class="fas fa-shopping-cart text-xl"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-800">For Buyers</h3>
                    </div>
                    
                    <div class="space-y-6">
                        <div class="flex items-start">
                            <span class="bg-gradient-to-br from-primary to-indigo-600 text-white text-sm font-bold w-8 h-8 rounded-lg flex items-center justify-center mr-4 mt-1 shadow-lg">1</span>
                            <div>
                                <h4 class="font-bold text-gray-800 mb-1">Browse & Select</h4>
                                <p class="text-sm text-gray-600">Find products from verified local and international vendors</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <span class="bg-gradient-to-br from-primary to-indigo-600 text-white text-sm font-bold w-8 h-8 rounded-lg flex items-center justify-center mr-4 mt-1 shadow-lg">2</span>
                            <div>
                                <h4 class="font-bold text-gray-800 mb-1">Secure Payment</h4>
                                <p class="text-sm text-gray-600">Pay through escrow - money held safely until delivery</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <span class="bg-gradient-to-br from-primary to-indigo-600 text-white text-sm font-bold w-8 h-8 rounded-lg flex items-center justify-center mr-4 mt-1 shadow-lg">3</span>
                            <div>
                                <h4 class="font-bold text-gray-800 mb-1">Receive & Confirm</h4>
                                <p class="text-sm text-gray-600">Get delivery and confirm receipt to release payment</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- For Vendors -->
                <div class="card-hover bg-gradient-to-br from-green-50 to-green-100 rounded-2xl p-8">
                    <div class="flex items-center mb-6">
                        <div class="bg-gradient-to-br from-secondary to-green-600 text-white w-12 h-12 rounded-xl flex items-center justify-center mr-4 shadow-lg">
                            <i class="fas fa-store text-xl"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-800">For Vendors</h3>
                    </div>
                    
                    <div class="space-y-6">
                        <div class="flex items-start">
                            <span class="bg-gradient-to-br from-secondary to-green-600 text-white text-sm font-bold w-8 h-8 rounded-lg flex items-center justify-center mr-4 mt-1 shadow-lg">1</span>
                            <div>
                                <h4 class="font-bold text-gray-800 mb-1">Get Verified</h4>
                                <p class="text-sm text-gray-600">Complete onboarding with document verification</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <span class="bg-gradient-to-br from-secondary to-green-600 text-white text-sm font-bold w-8 h-8 rounded-lg flex items-center justify-center mr-4 mt-1 shadow-lg">2</span>
                            <div>
                                <h4 class="font-bold text-gray-800 mb-1">List Products</h4>
                                <p class="text-sm text-gray-600">Add products with detailed descriptions and images</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <span class="bg-gradient-to-br from-secondary to-green-600 text-white text-sm font-bold w-8 h-8 rounded-lg flex items-center justify-center mr-4 mt-1 shadow-lg">3</span>
                            <div>
                                <h4 class="font-bold text-gray-800 mb-1">Sell & Get Paid</h4>
                                <p class="text-sm text-gray-600">Receive orders and get paid after successful delivery</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- For Importers -->
                <div class="card-hover bg-gradient-to-br from-yellow-50 to-orange-100 rounded-2xl p-8">
                    <div class="flex items-center mb-6">
                        <div class="bg-gradient-to-br from-accent to-orange-600 text-white w-12 h-12 rounded-xl flex items-center justify-center mr-4 shadow-lg">
                            <i class="fas fa-plane text-xl"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-800">For Importers</h3>
                    </div>
                    
                    <div class="space-y-6">
                        <div class="flex items-start">
                            <span class="bg-gradient-to-br from-accent to-orange-600 text-white text-sm font-bold w-8 h-8 rounded-lg flex items-center justify-center mr-4 mt-1 shadow-lg">1</span>
                            <div>
                                <h4 class="font-bold text-gray-800 mb-1">Calculate Costs</h4>
                                <p class="text-sm text-gray-600">Use our import calculator for accurate cost estimation</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <span class="bg-gradient-to-br from-accent to-orange-600 text-white text-sm font-bold w-8 h-8 rounded-lg flex items-center justify-center mr-4 mt-1 shadow-lg">2</span>
                            <div>
                                <h4 class="font-bold text-gray-800 mb-1">Request Import</h4>
                                <p class="text-sm text-gray-600">Submit import request with product details</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <span class="bg-gradient-to-br from-accent to-orange-600 text-white text-sm font-bold w-8 h-8 rounded-lg flex items-center justify-center mr-4 mt-1 shadow-lg">3</span>
                            <div>
                                <h4 class="font-bold text-gray-800 mb-1">Track & Sell</h4>
                                <p class="text-sm text-gray-600">Track shipment and sell imported goods</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Import Calculator Section -->
    <section id="import-calculator" class="py-20 bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-white relative overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute inset-0" style="background-image: radial-gradient(circle, white 1px, transparent 1px); background-size: 50px 50px;"></div>
        </div>
        
        <div class="container mx-auto px-4 relative z-10">
            <div class="text-center mb-12 animate-slide-up">
                <h2 class="text-4xl font-bold mb-4">📊 Import Cost Calculator</h2>
                <p class="text-gray-300 max-w-2xl mx-auto text-lg">
                    Calculate customs duties, shipping costs, and total landed cost for imported products
                </p>
            </div>
            
            <div class="max-w-5xl mx-auto bg-white/10 backdrop-blur-lg rounded-3xl p-8 md:p-12 border border-white/20 shadow-2xl">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                    <div>
                        <h3 class="text-2xl font-bold mb-6">Calculate Your Import Costs</h3>
                        <form class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium mb-2">Product Value ($)</label>
                                <input type="number" id="productValue" 
                                       class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/20 focus:border-primary focus:ring-2 focus:ring-primary/50 smooth-transition text-white placeholder-gray-400" 
                                       placeholder="e.g., 1000">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium mb-2">Shipping Cost ($)</label>
                                <input type="number" id="shippingCost" 
                                       class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/20 focus:border-primary focus:ring-2 focus:ring-primary/50 smooth-transition text-white placeholder-gray-400" 
                                       placeholder="e.g., 200">
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium mb-2">Duty Rate (%)</label>
                                    <input type="number" id="dutyRate" 
                                           class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/20 focus:border-primary focus:ring-2 focus:ring-primary/50 smooth-transition text-white" 
                                           value="10" step="0.1">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-2">VAT Rate (%)</label>
                                    <input type="number" id="vatRate" 
                                           class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/20 focus:border-primary focus:ring-2 focus:ring-primary/50 smooth-transition text-white" 
                                           value="18" step="0.1">
                                </div>
                            </div>
                            
                            <button type="button" onclick="calculateImportCost()" 
                                    class="w-full bg-gradient-to-r from-primary to-indigo-600 text-white py-4 rounded-xl font-bold hover:shadow-2xl smooth-transition">
                                <i class="fas fa-calculator mr-2"></i> Calculate Total Cost
                            </button>
                        </form>
                    </div>
                    
                    <div>
                        <h3 class="text-2xl font-bold mb-6">Cost Breakdown</h3>
                        <div id="costBreakdown" class="space-y-4 bg-white/5 rounded-2xl p-6 border border-white/10">
                            <div class="flex justify-between items-center pb-3 border-b border-white/10">
                                <span class="text-gray-300">Product Value:</span>
                                <span id="productValueResult" class="font-bold text-xl">$0.00</span>
                            </div>
                            <div class="flex justify-between items-center pb-3 border-b border-white/10">
                                <span class="text-gray-300">Shipping Cost:</span>
                                <span id="shippingCostResult" class="font-bold text-xl">$0.00</span>
                            </div>
                            <div class="flex justify-between items-center pb-3 border-b border-white/10">
                                <span class="text-gray-300">Import Duty (10%):</span>
                                <span id="dutyCost" class="font-bold text-xl">$0.00</span>
                            </div>
                            <div class="flex justify-between items-center pb-4 border-b border-white/10">
                                <span class="text-gray-300">VAT (18%):</span>
                                <span id="vatCost" class="font-bold text-xl">$0.00</span>
                            </div>
                            <div class="flex justify-between items-center pt-2">
                                <span class="text-xl font-bold">Total Landed Cost:</span>
                                <span id="totalCost" class="text-3xl font-bold text-yellow-400">$0.00</span>
                            </div>
                        </div>
                        
                        <div class="mt-8 bg-gradient-to-br from-blue-500/20 to-purple-500/20 p-6 rounded-2xl border border-blue-400/30">
                            <h4 class="font-bold mb-3 flex items-center">
                                <i class="fas fa-lightbulb text-yellow-400 mr-2"></i>
                                Pro Tip
                            </h4>
                            <p class="text-sm text-gray-200 leading-relaxed">
                                For accurate calculations, include insurance and handling fees. Our platform automatically calculates all costs when vendors list imported products.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-gradient-to-r from-primary via-indigo-600 to-purple-600 text-white relative overflow-hidden">
        <div class="absolute inset-0 opacity-20">
            <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width=&quot;60&quot; height=&quot;60&quot; viewBox=&quot;0 0 60 60&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cg fill=&quot;none&quot; fill-rule=&quot;evenodd&quot;%3E%3Cg fill=&quot;%23ffffff&quot; fill-opacity=&quot;0.4&quot;%3E%3Cpath d=&quot;M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z&quot;/%3E%3C/g%3E%3C/g%3E%3C/svg%3E'); background-size: 60px 60px;"></div>
        </div>
        
        <div class="container mx-auto px-4 text-center relative z-10">
            <h2 class="text-4xl md:text-5xl font-bold mb-6 animate-slide-up">
                Ready to Start Your Journey?
            </h2>
            <p class="text-xl opacity-90 mb-10 max-w-3xl mx-auto leading-relaxed animate-fade-in">
                Join thousands of satisfied customers and vendors on our secure marketplace platform. Experience seamless buying and selling today!
            </p>
            <div class="flex flex-wrap justify-center gap-4 animate-scale-in">
                <a href="{{ route('marketplace.index') }}" class="bg-white text-primary px-10 py-4 rounded-xl font-bold hover:shadow-2xl smooth-transition flex items-center text-lg">
                    <i class="fas fa-shopping-bag mr-3"></i> Browse Products
                </a>
                <a href="{{ route('vendor.onboard.create') }}" class="glass border-2 border-white text-white px-10 py-4 rounded-xl font-bold hover:bg-white hover:text-primary smooth-transition flex items-center text-lg">
                    <i class="fas fa-store mr-3"></i> Start Selling
                </a>
            </div>
            
            <!-- Trust Badges -->
            <div class="mt-16 grid grid-cols-2 md:grid-cols-4 gap-8 max-w-4xl mx-auto">
                <div class="text-center">
                    <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-shield-alt text-3xl"></i>
                    </div>
                    <div class="font-bold">100% Secure</div>
                    <div class="text-sm opacity-75">Escrow Protection</div>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-truck text-3xl"></i>
                    </div>
                    <div class="font-bold">Fast Delivery</div>
                    <div class="text-sm opacity-75">Nationwide Shipping</div>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-headset text-3xl"></i>
                    </div>
                    <div class="font-bold">24/7 Support</div>
                    <div class="text-sm opacity-75">Always Here to Help</div>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-undo text-3xl"></i>
                    </div>
                    <div class="font-bold">Easy Returns</div>
                    <div class="text-sm opacity-75">30-Day Policy</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-16">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-12">
                <!-- Company Info -->
                <div>
                    <div class="flex items-center mb-6">
                        <i class="fas fa-store text-3xl text-primary mr-3"></i>
                        <span class="text-2xl font-bold">{{ config('app.name') }}</span>
                    </div>
                    <p class="text-gray-400 mb-6 leading-relaxed">
                        Secure marketplace for local and imported goods with integrated logistics and escrow protection.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="w-10 h-10 bg-gray-800 rounded-lg flex items-center justify-center hover:bg-primary smooth-transition">
                            <i class="fab fa-facebook"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-800 rounded-lg flex items-center justify-center hover:bg-primary smooth-transition">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-800 rounded-lg flex items-center justify-center hover:bg-primary smooth-transition">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-800 rounded-lg flex items-center justify-center hover:bg-primary smooth-transition">
                            <i class="fab fa-linkedin"></i>
                        </a>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div>
                    <h4 class="text-lg font-bold mb-6">Quick Links</h4>
                    <ul class="space-y-3">
                        <li><a href="{{ route('marketplace.index') }}" class="text-gray-400 hover:text-white smooth-transition hover:translate-x-1 inline-block">Marketplace</a></li>
                        <li><a href="#how-it-works" class="text-gray-400 hover:text-white smooth-transition hover:translate-x-1 inline-block">How It Works</a></li>
                        <li><a href="#import-calculator" class="text-gray-400 hover:text-white smooth-transition hover:translate-x-1 inline-block">Import Calculator</a></li>
                        <li><a href="#categories" class="text-gray-400 hover:text-white smooth-transition hover:translate-x-1 inline-block">Categories</a></li>
                        <li><a href="{{ route('vendor.onboard.create') }}" class="text-gray-400 hover:text-white smooth-transition hover:translate-x-1 inline-block">Vendor Benefits</a></li>
                    </ul>
                </div>
                
                <!-- Support -->
                <div>
                    <h4 class="text-lg font-bold mb-6">Support</h4>
                    <ul class="space-y-3">
                        <li><a href="#" class="text-gray-400 hover:text-white smooth-transition hover:translate-x-1 inline-block">Help Center</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white smooth-transition hover:translate-x-1 inline-block">FAQs</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white smooth-transition hover:translate-x-1 inline-block">Contact Us</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white smooth-transition hover:translate-x-1 inline-block">Dispute Resolution</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white smooth-transition hover:translate-x-1 inline-block">Shipping Policy</a></li>
                    </ul>
                </div>
                
                <!-- Newsletter -->
                <div>
                    <h4 class="text-lg font-bold mb-6">Stay Updated</h4>
                    <p class="text-gray-400 mb-4">Subscribe to get special offers and updates</p>
                    <form class="mb-6">
                        <div class="flex">
                            <input type="email" placeholder="Your email" 
                                   class="newsletter-input flex-1 px-4 py-3 rounded-l-xl text-gray-800 focus:outline-none border-2 border-transparent focus:border-primary smooth-transition">
                            <button type="submit" class="bg-gradient-to-r from-primary to-indigo-600 px-6 py-3 rounded-r-xl hover:shadow-lg smooth-transition">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </form>
                    
                    <!-- Payment Methods -->
                    <div>
                        <p class="text-gray-400 mb-3 text-sm">Secure Payments:</p>
                        <div class="flex space-x-3">
                            <div class="w-12 h-8 bg-gray-800 rounded flex items-center justify-center">
                                <i class="fab fa-cc-visa text-xl text-blue-500"></i>
                            </div>
                            <div class="w-12 h-8 bg-gray-800 rounded flex items-center justify-center">
                                <i class="fab fa-cc-mastercard text-xl text-red-500"></i>
                            </div>
                            <div class="w-12 h-8 bg-gray-800 rounded flex items-center justify-center">
                                <i class="fab fa-cc-paypal text-xl text-blue-400"></i>
                            </div>
                            <div class="w-12 h-8 bg-gray-800 rounded flex items-center justify-center">
                                <i class="fas fa-mobile-alt text-xl text-green-500"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Bottom Bar -->
            <div class="border-t border-gray-800 pt-8 text-center">
                <p class="text-gray-400 mb-3">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                <div class="flex flex-wrap justify-center gap-6 text-sm">
                    <a href="#" class="text-gray-400 hover:text-white smooth-transition">Terms of Service</a>
                    <span class="text-gray-700">|</span>
                    <a href="#" class="text-gray-400 hover:text-white smooth-transition">Privacy Policy</a>
                    <span class="text-gray-700">|</span>
                    <a href="#" class="text-gray-400 hover:text-white smooth-transition">Cookie Policy</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Authentication Modal -->
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

    <!-- Scroll to Top Button -->
    <button id="scrollTop" class="fixed bottom-8 right-8 bg-gradient-to-r from-primary to-indigo-600 text-white w-14 h-14 rounded-full shadow-2xl hover:shadow-3xl smooth-transition opacity-0 pointer-events-none z-50">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
    
    <!-- Main JavaScript -->
    <script>
        // Check if user is authenticated
        const isAuthenticated = @json(auth()->check());
        
        // Import Calculator
        function calculateImportCost() {
            const productValue = parseFloat(document.getElementById('productValue').value) || 0;
            const shippingCost = parseFloat(document.getElementById('shippingCost').value) || 0;
            const dutyRate = parseFloat(document.getElementById('dutyRate').value) || 10;
            const vatRate = parseFloat(document.getElementById('vatRate').value) || 18;
            
            const cif = productValue + shippingCost;
            const duty = cif * (dutyRate / 100);
            const vatBase = cif + duty;
            const vat = vatBase * (vatRate / 100);
            const totalCost = cif + duty + vat;
            
            // Animate numbers
            animateValue('productValueResult', 0, productValue, 500);
            animateValue('shippingCostResult', 0, shippingCost, 500);
            animateValue('dutyCost', 0, duty, 500);
            animateValue('vatCost', 0, vat, 500);
            animateValue('totalCost', 0, totalCost, 800);
        }
        
        // Animate number counting
        function animateValue(id, start, end, duration) {
            const obj = document.getElementById(id);
            const range = end - start;
            const increment = range / (duration / 16);
            let current = start;
            
            const timer = setInterval(() => {
                current += increment;
                if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
                    current = end;
                    clearInterval(timer);
                }
                obj.textContent = ' + current.toFixed(2);
            }, 16);
        }
        
        // Show authentication modal
        function showAuthModal() {
            const modal = document.getElementById('authModal');
            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }
        }
        
        // Close authentication modal
        function closeAuthModal() {
            const modal = document.getElementById('authModal');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
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
        
        // Quick add to cart
        async function quickAddToCart(listingId, button) {
            if (!isAuthenticated) {
                showAuthModal();
                return;
            }
            
            const originalHtml = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            button.disabled = true;
            
            try {
                const response = await fetch(`/buyer/cart/add/${listingId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ quantity: 1 })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    button.innerHTML = '<i class="fas fa-check"></i>';
                    showToast(data.message || 'Added to cart!', 'success');
                    
                    setTimeout(() => {
                        button.innerHTML = originalHtml;
                        button.disabled = false;
                    }, 2000);
                } else {
                    button.innerHTML = originalHtml;
                    button.disabled = false;
                    showToast(data.message || 'Failed to add to cart', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                button.innerHTML = originalHtml;
                button.disabled = false;
                showToast('Network error. Please try again.', 'error');
            }
        }
        
        // Quick add to wishlist
        async function quickAddToWishlist(listingId, button) {
            if (!isAuthenticated) {
                showAuthModal();
                return;
            }
            
            const icon = button.querySelector('i');
            const isFilled = icon.classList.contains('fas');
            
            try {
                const response = await fetch(`/buyer/wishlist/toggle/${listingId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    if (data.in_wishlist) {
                        icon.classList.remove('far');
                        icon.classList.add('fas');
                        button.classList.remove('text-gray-600');
                        button.classList.add('text-red-500');
                    } else {
                        icon.classList.remove('fas');
                        icon.classList.add('far');
                        button.classList.remove('text-red-500');
                        button.classList.add('text-gray-600');
                    }
                    showToast(data.message || 'Wishlist updated!', 'success');
                } else {
                    showToast(data.message || 'Failed to update wishlist', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('Failed to update wishlist', 'error');
            }
        }
        
        // Scroll to top
        const scrollTopBtn = document.getElementById('scrollTop');
        
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                scrollTopBtn.style.opacity = '1';
                scrollTopBtn.style.pointerEvents = 'auto';
            } else {
                scrollTopBtn.style.opacity = '0';
                scrollTopBtn.style.pointerEvents = 'none';
            }
        });
        
        scrollTopBtn.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
        
        // Mobile menu toggle
        function toggleMobileMenu() {
            alert('Mobile menu - implement navigation drawer');
        }
        
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', () => {
            // Setup all quick action buttons
            document.querySelectorAll('[data-quick-cart]').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const listingId = this.getAttribute('data-listing-id');
                    quickAddToCart(listingId, this);
                });
            });
            
            document.querySelectorAll('[data-quick-wishlist]').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const listingId = this.getAttribute('data-listing-id');
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
            
            // Add entrance animations on scroll
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, observerOptions);
            
            // Observe elements
            document.querySelectorAll('.card-hover, .product-card, .category-card').forEach(el => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(30px)';
                el.style.transition = 'all 0.6s ease-out';
                observer.observe(el);
            });
            
            // Initialize calculator with default calculation
            calculateImportCost();
        });
    </script>
    
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
</body>
</html>