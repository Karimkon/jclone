<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
                    }
                }
            }
        }
    </script>
    
    <style>
        /* Custom Styles */
        .category-card:hover {
            transform: translateY(-5px);
            transition: all 0.3s ease;
        }
        
        .hero-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .product-card {
            transition: all 0.3s ease;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        
        .countdown-timer {
            background: linear-gradient(135deg, #ff416c, #ff4b2b);
        }
        
        .import-badge {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        }
        
        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Top Navigation -->
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <!-- Logo -->
                <div class="flex items-center space-x-2">
                    <a href="{{ route('welcome') }}" class="text-2xl font-bold text-primary">
                        <i class="fas fa-store mr-2"></i>{{ config('app.name') }}
                    </a>
                    <span class="text-xs bg-primary text-white px-2 py-1 rounded-full">Beta</span>
                </div>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center space-x-6">
                    <a href="{{ route('marketplace.index') }}" class="text-gray-700 hover:text-primary font-medium">
                        <i class="fas fa-th-large mr-2"></i>Marketplace
                    </a>
                    <a href="#how-it-works" class="text-gray-700 hover:text-primary font-medium">
                        <i class="fas fa-play-circle mr-2"></i>How it Works
                    </a>
                    <a href="#import-calculator" class="text-gray-700 hover:text-primary font-medium">
                        <i class="fas fa-calculator mr-2"></i>Import Calculator
                    </a>
                    <a href="#categories" class="text-gray-700 hover:text-primary font-medium">
                        <i class="fas fa-tags mr-2"></i>Categories
                    </a>
                </div>

          <!-- User Actions -->
<div class="flex items-center space-x-4">
    @auth
        <!-- Check vendor status using the new helper methods -->
        @if(auth()->user()->isVendor())
            <!-- Approved vendor -->
            <a href="{{ route('vendor.dashboard') }}" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-indigo-700 font-medium">
                <i class="fas fa-tachometer-alt mr-2"></i>Vendor Dashboard
            </a>
            
            <!-- Vendor user dropdown (optional) -->
            <div class="relative group">
                <button class="flex items-center space-x-2 text-gray-700 hover:text-primary">
                    <i class="fas fa-user-circle text-xl"></i>
                    <span class="hidden md:inline">{{ auth()->user()->name }}</span>
                    <i class="fas fa-chevron-down text-xs"></i>
                </button>
                <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 hidden group-hover:block">
                    <a href="{{ route('vendor.profile.show') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-store mr-2"></i>Store Profile
                    </a>
                    <a href="{{ route('vendor.orders.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-shopping-bag mr-2"></i>Orders
                    </a>
                    <hr class="my-2">
                    <form action="{{ route('logout') }}" method="POST" class="block px-4 py-2 text-red-600 hover:bg-gray-100 cursor-pointer">
                        @csrf
                        <button type="submit" class="w-full text-left">
                            <i class="fas fa-sign-out-alt mr-2"></i>Logout
                        </button>
                    </form>
                </div>
            </div>
            
        @elseif(auth()->user()->isInVendorOnboarding())
            <!-- User is in vendor onboarding process -->
            <a href="{{ route('vendor.onboard.status') }}" class="bg-yellow-600 text-white px-4 py-2 rounded-lg hover:bg-yellow-700 font-medium">
                <i class="fas fa-clipboard-check mr-2"></i>Application Status
            </a>
            
        @elseif(auth()->user()->isBuyer())
            <!-- Regular buyer -->
            <a href="{{ route('cart.index') }}" class="text-gray-700 hover:text-primary relative">
                <i class="fas fa-shopping-cart text-xl"></i>
                <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">0</span>
            </a>
            <div class="relative group">
                <button class="flex items-center space-x-2 text-gray-700 hover:text-primary">
                    <i class="fas fa-user-circle text-xl"></i>
                    <span class="hidden md:inline">{{ auth()->user()->name }}</span>
                    <i class="fas fa-chevron-down text-xs"></i>
                </button>
                <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 hidden group-hover:block">
                    <a href="{{ route('orders.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-shopping-bag mr-2"></i>My Orders
                    </a>
                    <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-heart mr-2"></i>Wishlist
                    </a>
                    <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-cog mr-2"></i>Settings
                    </a>
                    <hr class="my-2">
                    <form action="{{ route('logout') }}" method="POST" class="block px-4 py-2 text-red-600 hover:bg-gray-100 cursor-pointer">
                        @csrf
                        <button type="submit" class="w-full text-left">
                            <i class="fas fa-sign-out-alt mr-2"></i>Logout
                        </button>
                    </form>
                </div>
            </div>
            
        @else
            <!-- Logged in but not a buyer or in vendor process (could be admin, logistics, etc.) -->
            <!-- Or logged in buyer who hasn't started vendor onboarding yet -->
            @if(auth()->user()->role === 'admin' || auth()->user()->role === 'logistics' || auth()->user()->role === 'finance' || auth()->user()->role === 'ceo')
                <!-- Staff users - show their dashboard -->
                <a href="{{ route(auth()->user()->role . '.dashboard') }}" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-indigo-700 font-medium">
                    <i class="fas fa-tachometer-alt mr-2"></i>{{ ucfirst(auth()->user()->role) }} Dashboard
                </a>
            @else
                <!-- Regular user who can become a seller -->
                <a href="{{ route('vendor.onboard.create') }}" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-indigo-700 font-medium">
                    <i class="fas fa-store mr-2"></i>Become a Seller
                </a>
            @endif
        @endif
    @else
        <!-- Not logged in -->
        <a href="{{ route('login') }}" class="text-gray-700 hover:text-primary font-medium">
            <i class="fas fa-sign-in-alt mr-2"></i>Login
        </a>
         <a href="{{ route('vendor.onboard.create') }}" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-indigo-700 font-medium">
            <i class="fas fa-store mr-2"></i>Become a Seller
        </a>
    @endauth
</div>
            </div>
            
            <!-- Mobile Search -->
            <div class="md:hidden mt-2">
                <div class="relative">
                    <input type="text" placeholder="Search products, brands, and categories..." 
                           class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary">
                    <button class="absolute right-2 top-2 text-gray-500">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-gradient text-white py-16">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div>
                    <h1 class="text-4xl md:text-5xl font-bold mb-6 leading-tight">
                        Buy Local & Imported Products <span class="text-accent">with Confidence</span>
                    </h1>
                    <p class="text-xl mb-8 opacity-90">
                        Secure marketplace with escrow protection, integrated logistics, and customs clearance for hassle-free shopping.
                    </p>
                    <div class="flex flex-wrap gap-4">
                        <a href="{{ route('marketplace.index') }}" class="bg-white text-primary px-6 py-3 rounded-lg font-semibold hover:bg-gray-100 transition flex items-center">
                            <i class="fas fa-shopping-bag mr-2"></i> Start Shopping
                        </a>
                       <a href="{{ route('vendor.onboard.create') }}" class="border-2 border-white text-white px-6 py-3 rounded-lg font-semibold hover:bg-white hover:text-primary transition flex items-center">
                            <i class="fas fa-store mr-2"></i> Start Selling
                        </a>
                        <a href="#how-it-works" class="bg-transparent border border-white text-white px-6 py-3 rounded-lg font-semibold hover:bg-white hover:text-primary transition flex items-center">
                            <i class="fas fa-play-circle mr-2"></i> How It Works
                        </a>
                    </div>
                    
                    <!-- Trust Indicators -->
                    <div class="mt-12 grid grid-cols-2 md:grid-cols-4 gap-6">
                        <div class="text-center">
                            <div class="text-2xl font-bold">10,000+</div>
                            <div class="text-sm opacity-80">Happy Customers</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold">500+</div>
                            <div class="text-sm opacity-80">Verified Vendors</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold">24/7</div>
                            <div class="text-sm opacity-80">Support</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold">100%</div>
                            <div class="text-sm opacity-80">Secure Payment</div>
                        </div>
                    </div>
                </div>
                <div class="relative">
                    <div class="relative z-10">
                        <img src="https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                             alt="Shopping Experience" class="rounded-xl shadow-2xl">
                    </div>
                    <!-- Floating Cards -->
                    <div class="absolute -top-4 -left-4 bg-white p-4 rounded-lg shadow-lg z-20">
                        <div class="flex items-center">
                            <div class="bg-primary text-white p-2 rounded-full">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <div class="ml-3">
                                <div class="font-bold text-gray-800">Escrow Protection</div>
                                <div class="text-sm text-gray-600">Money safe until delivery</div>
                            </div>
                        </div>
                    </div>
                    <div class="absolute -bottom-4 -right-4 bg-white p-4 rounded-lg shadow-lg z-20">
                        <div class="flex items-center">
                            <div class="bg-secondary text-white p-2 rounded-full">
                                <i class="fas fa-truck"></i>
                            </div>
                            <div class="ml-3">
                                <div class="font-bold text-gray-800">Fast Delivery</div>
                                <div class="text-sm text-gray-600">Doorstep delivery</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section id="categories" class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-800 mb-4">Shop by Category</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">Browse through our wide range of categories from local artisans and imported goods</p>
            </div>
            
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                @foreach($categories as $category)
                <a href="{{ route('categories.show', $category) }}" class="category-card bg-gray-50 rounded-xl p-4 text-center hover:bg-primary hover:text-white transition">
                    <div class="bg-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-{{ $category->icon ?? 'tag' }} text-primary text-xl"></i>
                    </div>
                    <h3 class="font-semibold text-sm">{{ $category->name }}</h3>
                    <p class="text-xs text-gray-500 mt-1">{{ $category->listings_count ?? 0 }} products</p>
                </a>
                @endforeach
            </div>
            
            <div class="text-center mt-8">
                <a href="{{ route('categories.index') }}" class="inline-flex items-center text-primary font-semibold hover:text-indigo-700">
                    View All Categories <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Flash Sales Section -->
    @if($flashSales->count() > 0)
    <section class="py-16 bg-gradient-to-r from-orange-50 to-red-50">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row justify-between items-center mb-8">
                <div>
                    <h2 class="text-3xl font-bold text-gray-800 mb-2">🔥 Flash Sales</h2>
                    <p class="text-gray-600">Limited time offers. Don't miss out!</p>
                </div>
                <div class="countdown-timer text-white px-6 py-3 rounded-lg mt-4 md:mt-0">
                    <div class="flex items-center space-x-4">
                        <div class="text-center">
                            <div id="hours" class="text-2xl font-bold">24</div>
                            <div class="text-xs">Hours</div>
                        </div>
                        <div class="text-2xl">:</div>
                        <div class="text-center">
                            <div id="minutes" class="text-2xl font-bold">59</div>
                            <div class="text-xs">Minutes</div>
                        </div>
                        <div class="text-2xl">:</div>
                        <div class="text-center">
                            <div id="seconds" class="text-2xl font-bold">59</div>
                            <div class="text-xs">Seconds</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="swiper-container">
                <div class="swiper-wrapper">
                    @foreach($flashSales as $promotion)
                    <div class="swiper-slide">
                        <div class="product-card bg-white rounded-xl shadow-md overflow-hidden">
                            <div class="relative">
                                @if($promotion->listing->images->first())
                                <img src="{{ asset('storage/' . $promotion->listing->images->first()->path) }}" 
                                     alt="{{ $promotion->listing->title }}" 
                                     class="w-full h-56 object-cover">
                                @else
                                <div class="w-full h-56 bg-gray-200 flex items-center justify-center">
                                    <i class="fas fa-image text-gray-400 text-4xl"></i>
                                </div>
                                @endif
                                <div class="absolute top-3 right-3 bg-red-500 text-white text-sm font-bold px-3 py-1 rounded-full">
                                    -{{ rand(20, 50) }}%
                                </div>
                                @if($promotion->listing->origin == 'imported')
                                <div class="absolute top-3 left-3 import-badge text-white text-xs font-bold px-2 py-1 rounded">
                                    <i class="fas fa-plane mr-1"></i> Imported
                                </div>
                                @endif
                            </div>
                            <div class="p-5">
                                <div class="flex justify-between items-start mb-3">
                                    <h3 class="font-bold text-lg text-gray-800 truncate">{{ $promotion->listing->title }}</h3>
                                    <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded">
                                        <i class="fas fa-store mr-1"></i> {{ $promotion->listing->vendor->business_name ?? 'Vendor' }}
                                    </span>
                                </div>
                                <p class="text-gray-600 text-sm mb-4 line-clamp-2">{{ $promotion->listing->description }}</p>
                                
                                <div class="flex items-center justify-between mb-4">
                                    <div>
                                        <span class="text-xl font-bold text-red-600">${{ number_format($promotion->listing->price * 0.8, 2) }}</span>
                                        <span class="text-gray-500 line-through ml-2 text-sm">${{ number_format($promotion->listing->price, 2) }}</span>
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        <i class="fas fa-fire text-orange-500 mr-1"></i> {{ rand(50, 200) }} sold
                                    </div>
                                </div>
                                
                                <button class="w-full bg-gradient-to-r from-red-500 to-orange-500 text-white py-3 rounded-lg font-semibold hover:opacity-90 transition flex items-center justify-center">
                                    <i class="fas fa-bolt mr-2"></i> Buy Now
                                </button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                <!-- Add Navigation -->
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
            </div>
        </div>
    </section>
    @endif

    <!-- New Arrivals Section -->
    <section class="py-16">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row justify-between items-center mb-8">
                <div>
                    <h2 class="text-3xl font-bold text-gray-800 mb-2">🆕 New Arrivals</h2>
                    <p class="text-gray-600">Freshly imported products just for you</p>
                </div>
                <a href="{{ route('marketplace.index') }}" class="mt-4 md:mt-0 inline-flex items-center text-primary font-semibold hover:text-indigo-700">
                    View All Products <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach($newArrivals as $listing)
                <div class="product-card bg-white rounded-xl shadow-md overflow-hidden">
                    <div class="relative">
                        @if($listing->images->first())
                        <img src="{{ asset('storage/' . $listing->images->first()->path) }}" 
                             alt="{{ $listing->title }}" 
                             class="w-full h-48 object-cover">
                        @else
                        <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                            <i class="fas fa-image text-gray-400 text-4xl"></i>
                        </div>
                        @endif
                        @if($listing->origin == 'imported')
                        <div class="absolute top-2 left-2 import-badge text-white text-xs font-bold px-2 py-1 rounded">
                            <i class="fas fa-plane mr-1"></i> Imported
                        </div>
                        @endif
                        <button class="absolute top-2 right-2 bg-white w-8 h-8 rounded-full flex items-center justify-center text-gray-600 hover:text-red-500">
                            <i class="fas fa-heart"></i>
                        </button>
                    </div>
                    
                    <div class="p-4">
                        <h3 class="font-bold text-gray-800 mb-2 line-clamp-1">{{ $listing->title }}</h3>
                        <p class="text-gray-600 text-sm mb-3 line-clamp-2">{{ $listing->description }}</p>
                        
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <span class="text-xl font-bold text-primary">${{ number_format($listing->price, 2) }}</span>
                                @if($listing->weight_kg)
                                <div class="text-xs text-gray-500 mt-1">
                                    <i class="fas fa-weight-hanging mr-1"></i> {{ $listing->weight_kg }}kg
                                </div>
                                @endif
                            </div>
                            <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded">
                                <i class="fas fa-store mr-1"></i> {{ $listing->vendor->business_name ?? 'Vendor' }}
                            </span>
                        </div>
                        
                        <button class="w-full bg-primary text-white py-2 rounded-lg font-medium hover:bg-indigo-700 transition flex items-center justify-center">
                            <i class="fas fa-shopping-cart mr-2"></i> Add to Cart
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Import Calculator Section -->
    <section id="import-calculator" class="py-16 bg-gray-900 text-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold mb-4">📊 Import Cost Calculator</h2>
                <p class="text-gray-300 max-w-2xl mx-auto">Calculate customs duties, shipping costs, and total landed cost for imported products</p>
            </div>
            
            <div class="max-w-4xl mx-auto bg-gray-800 rounded-xl p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <h3 class="text-xl font-bold mb-6">Calculate Your Import Costs</h3>
                        <form id="importCalculatorForm">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium mb-2">Product Value ($)</label>
                                    <input type="number" id="productValue" class="w-full px-4 py-2 rounded-lg bg-gray-700 border border-gray-600 focus:border-primary focus:ring-1 focus:ring-primary" placeholder="e.g., 1000">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium mb-2">Shipping Cost ($)</label>
                                    <input type="number" id="shippingCost" class="w-full px-4 py-2 rounded-lg bg-gray-700 border border-gray-600 focus:border-primary focus:ring-1 focus:ring-primary" placeholder="e.g., 200">
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium mb-2">Duty Rate (%)</label>
                                        <input type="number" id="dutyRate" class="w-full px-4 py-2 rounded-lg bg-gray-700 border border-gray-600 focus:border-primary focus:ring-1 focus:ring-primary" value="10" step="0.1">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-2">VAT Rate (%)</label>
                                        <input type="number" id="vatRate" class="w-full px-4 py-2 rounded-lg bg-gray-700 border border-gray-600 focus:border-primary focus:ring-1 focus:ring-primary" value="18" step="0.1">
                                    </div>
                                </div>
                                
                                <button type="button" onclick="calculateImportCost()" class="w-full bg-primary text-white py-3 rounded-lg font-semibold hover:bg-indigo-700 transition">
                                    <i class="fas fa-calculator mr-2"></i> Calculate Total Cost
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <div>
                        <h3 class="text-xl font-bold mb-6">Cost Breakdown</h3>
                        <div id="costBreakdown" class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-300">Product Value:</span>
                                <span id="productValueResult" class="font-bold">$0.00</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-300">Shipping Cost:</span>
                                <span id="shippingCostResult" class="font-bold">$0.00</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-300">Import Duty (10%):</span>
                                <span id="dutyCost" class="font-bold">$0.00</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-300">VAT (18%):</span>
                                <span id="vatCost" class="font-bold">$0.00</span>
                            </div>
                            <div class="flex justify-between items-center border-t border-gray-700 pt-3">
                                <span class="text-lg font-bold">Total Landed Cost:</span>
                                <span id="totalCost" class="text-xl font-bold text-primary">$0.00</span>
                            </div>
                        </div>
                        
                        <div class="mt-8 bg-gray-700 p-4 rounded-lg">
                            <h4 class="font-bold mb-2">💡 Pro Tip</h4>
                            <p class="text-sm text-gray-300">
                                For accurate calculations, include insurance and handling fees. Our platform automatically calculates all costs when vendors list imported products.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-800 mb-4">🎯 How It Works</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">Simple steps to buy and sell on our secure marketplace</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- For Buyers -->
                <div class="bg-gray-50 rounded-xl p-6">
                    <div class="flex items-center mb-6">
                        <div class="bg-primary text-white w-10 h-10 rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <h3 class="text-xl font-bold">For Buyers</h3>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <span class="bg-primary text-white text-sm font-bold w-6 h-6 rounded-full flex items-center justify-center mr-3 mt-1">1</span>
                            <div>
                                <h4 class="font-semibold">Browse & Select</h4>
                                <p class="text-sm text-gray-600">Find products from verified local and international vendors</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <span class="bg-primary text-white text-sm font-bold w-6 h-6 rounded-full flex items-center justify-center mr-3 mt-1">2</span>
                            <div>
                                <h4 class="font-semibold">Secure Payment</h4>
                                <p class="text-sm text-gray-600">Pay through escrow - money held safely until delivery</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <span class="bg-primary text-white text-sm font-bold w-6 h-6 rounded-full flex items-center justify-center mr-3 mt-1">3</span>
                            <div>
                                <h4 class="font-semibold">Receive & Confirm</h4>
                                <p class="text-sm text-gray-600">Get delivery and confirm receipt to release payment</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- For Vendors -->
                <div class="bg-gray-50 rounded-xl p-6">
                    <div class="flex items-center mb-6">
                        <div class="bg-secondary text-white w-10 h-10 rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-store"></i>
                        </div>
                        <h3 class="text-xl font-bold">For Vendors</h3>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <span class="bg-secondary text-white text-sm font-bold w-6 h-6 rounded-full flex items-center justify-center mr-3 mt-1">1</span>
                            <div>
                                <h4 class="font-semibold">Get Verified</h4>
                                <p class="text-sm text-gray-600">Complete onboarding with document verification</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <span class="bg-secondary text-white text-sm font-bold w-6 h-6 rounded-full flex items-center justify-center mr-3 mt-1">2</span>
                            <div>
                                <h4 class="font-semibold">List Products</h4>
                                <p class="text-sm text-gray-600">Add products with detailed descriptions and images</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <span class="bg-secondary text-white text-sm font-bold w-6 h-6 rounded-full flex items-center justify-center mr-3 mt-1">3</span>
                            <div>
                                <h4 class="font-semibold">Sell & Get Paid</h4>
                                <p class="text-sm text-gray-600">Receive orders and get paid after successful delivery</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- For Importers -->
                <div class="bg-gray-50 rounded-xl p-6">
                    <div class="flex items-center mb-6">
                        <div class="bg-accent text-white w-10 h-10 rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-plane"></i>
                        </div>
                        <h3 class="text-xl font-bold">For Importers</h3>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <span class="bg-accent text-white text-sm font-bold w-6 h-6 rounded-full flex items-center justify-center mr-3 mt-1">1</span>
                            <div>
                                <h4 class="font-semibold">Calculate Costs</h4>
                                <p class="text-sm text-gray-600">Use our import calculator for accurate cost estimation</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <span class="bg-accent text-white text-sm font-bold w-6 h-6 rounded-full flex items-center justify-center mr-3 mt-1">2</span>
                            <div>
                                <h4 class="font-semibold">Request Import</h4>
                                <p class="text-sm text-gray-600">Submit import request with product details</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <span class="bg-accent text-white text-sm font-bold w-6 h-6 rounded-full flex items-center justify-center mr-3 mt-1">3</span>
                            <div>
                                <h4 class="font-semibold">Track & Sell</h4>
                                <p class="text-sm text-gray-600">Track shipment and sell imported goods</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

   <!-- CTA Section -->
<section class="py-16 bg-gradient-to-r from-primary to-indigo-600 text-white">
    <div class="container mx-auto px-4 text-center">
        <h2 class="text-3xl font-bold mb-6">Ready to Start Buying or Selling?</h2>
        <p class="text-xl opacity-90 mb-8 max-w-2xl mx-auto">
            Join thousands of satisfied customers and vendors on our secure marketplace platform.
        </p>
        <div class="flex flex-wrap justify-center gap-4">
            <a href="{{ route('marketplace.index') }}" class="bg-white text-primary px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition flex items-center">
                <i class="fas fa-shopping-bag mr-2"></i> Browse Products
            </a>
            
            @auth
                @if(auth()->user()->isVendor())
                    <a href="{{ route('vendor.dashboard') }}" class="bg-transparent border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-primary transition flex items-center">
                        <i class="fas fa-tachometer-alt mr-2"></i> Go to Dashboard
                    </a>
                @elseif(auth()->user()->isInVendorOnboarding())
                    <a href="{{ route('vendor.onboard.status') }}" class="bg-yellow-600 border-2 border-yellow-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-yellow-700 transition flex items-center">
                        <i class="fas fa-clipboard-check mr-2"></i> Check Status
                    </a>
                @else
                    <a href="{{ route('vendor.onboard.create') }}" class="bg-transparent border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-primary transition flex items-center">
                        <i class="fas fa-store mr-2"></i> Start Selling
                    </a>
                @endif
            @else
                <a href="{{ route('vendor.login') }}" class="bg-transparent border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-primary transition flex items-center">
                    <i class="fas fa-store mr-2"></i> Start Selling
                </a>
            @endauth
        </div>
    </div>
</section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Company Info -->
                <div>
                    <div class="flex items-center mb-4">
                        <i class="fas fa-store text-2xl text-primary mr-2"></i>
                        <span class="text-2xl font-bold">{{ config('app.name') }}</span>
                    </div>
                    <p class="text-gray-400 mb-4">
                        Secure marketplace for local and imported goods with integrated logistics and escrow protection.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white">
                            <i class="fab fa-facebook text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white">
                            <i class="fab fa-twitter text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white">
                            <i class="fab fa-instagram text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white">
                            <i class="fab fa-linkedin text-xl"></i>
                        </a>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div>
                    <h4 class="text-lg font-bold mb-4">Quick Links</h4>
                    <ul class="space-y-2">
                        <li><a href="{{ route('marketplace.index') }}" class="text-gray-400 hover:text-white">Marketplace</a></li>
                        <li><a href="#how-it-works" class="text-gray-400 hover:text-white">How It Works</a></li>
                        <li><a href="#import-calculator" class="text-gray-400 hover:text-white">Import Calculator</a></li>
                        <li><a href="{{ route('categories.index') }}" class="text-gray-400 hover:text-white">Categories</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Vendor Benefits</a></li>
                    </ul>
                </div>
                
                <!-- Support -->
                <div>
                    <h4 class="text-lg font-bold mb-4">Support</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white">Help Center</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">FAQs</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Contact Us</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Dispute Resolution</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Shipping Policy</a></li>
                    </ul>
                </div>
                
                <!-- Newsletter -->
                <div>
                    <h4 class="text-lg font-bold mb-4">Stay Updated</h4>
                    <p class="text-gray-400 mb-4">Subscribe to get special offers and updates</p>
                    <form class="flex">
                        <input type="email" placeholder="Your email" 
                               class="flex-1 px-4 py-2 rounded-l-lg text-gray-800 focus:outline-none focus:ring-2 focus:ring-primary">
                        <button type="submit" class="bg-primary px-4 py-2 rounded-r-lg hover:bg-indigo-700">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                    
                    <!-- Payment Methods -->
                    <div class="mt-6">
                        <p class="text-gray-400 mb-2">Secure Payments:</p>
                        <div class="flex space-x-2">
                            <i class="fab fa-cc-visa text-2xl text-gray-400"></i>
                            <i class="fab fa-cc-mastercard text-2xl text-gray-400"></i>
                            <i class="fab fa-cc-paypal text-2xl text-gray-400"></i>
                            <i class="fas fa-mobile-alt text-2xl text-gray-400"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Bottom Bar -->
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                <div class="mt-2 text-sm">
                    <a href="#" class="hover:text-white mx-2">Terms of Service</a> | 
                    <a href="#" class="hover:text-white mx-2">Privacy Policy</a> | 
                    <a href="#" class="hover:text-white mx-2">Cookie Policy</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
    <script>
        // Initialize Swiper
        const swiper = new Swiper('.swiper-container', {
            slidesPerView: 1,
            spaceBetween: 20,
            loop: true,
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            breakpoints: {
                640: { slidesPerView: 2 },
                768: { slidesPerView: 3 },
                1024: { slidesPerView: 4 },
            }
        });

        // Flash Sale Countdown
        function updateCountdown() {
            const now = new Date();
            const endTime = new Date(now.getTime() + 24 * 60 * 60 * 1000); // 24 hours from now
            
            function update() {
                const now = new Date();
                const timeLeft = endTime - now;
                
                if (timeLeft <= 0) {
                    document.getElementById('hours').textContent = '00';
                    document.getElementById('minutes').textContent = '00';
                    document.getElementById('seconds').textContent = '00';
                    return;
                }
                
                const hours = Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);
                
                document.getElementById('hours').textContent = hours.toString().padStart(2, '0');
                document.getElementById('minutes').textContent = minutes.toString().padStart(2, '0');
                document.getElementById('seconds').textContent = seconds.toString().padStart(2, '0');
            }
            
            update();
            setInterval(update, 1000);
        }

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
            
            // Update results
            document.getElementById('productValueResult').textContent = '$' + productValue.toFixed(2);
            document.getElementById('shippingCostResult').textContent = '$' + shippingCost.toFixed(2);
            document.getElementById('dutyCost').textContent = '$' + duty.toFixed(2);
            document.getElementById('vatCost').textContent = '$' + vat.toFixed(2);
            document.getElementById('totalCost').textContent = '$' + totalCost.toFixed(2);
            
            // Update duty and VAT rates in display
            document.querySelector('#costBreakdown div:nth-child(3) span:first-child').textContent = 
                `Import Duty (${dutyRate}%):`;
            document.querySelector('#costBreakdown div:nth-child(4) span:first-child').textContent = 
                `VAT (${vatRate}%):`;
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateCountdown();
            calculateImportCost(); // Show initial calculation
            
            // Smooth scrolling for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const targetId = this.getAttribute('href');
                    if (targetId === '#') return;
                    
                    const targetElement = document.querySelector(targetId);
                    if (targetElement) {
                        targetElement.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>