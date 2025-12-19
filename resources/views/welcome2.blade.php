<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} - Your Trusted Marketplace</title>
    <meta name="description" content="Shop securely with escrow protection. Buy local and imported products with confidence.">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Sora:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { 'body': ['Outfit', 'sans-serif'], 'display': ['Sora', 'sans-serif'] },
                    colors: {
                        brand: { 50: '#eef2ff', 100: '#e0e7ff', 200: '#c7d2fe', 300: '#a5b4fc', 400: '#818cf8', 500: '#6366f1', 600: '#4f46e5', 700: '#4338ca', 800: '#3730a3', 900: '#312e81' },
                        mint: { 400: '#34d399', 500: '#10b981', 600: '#059669' },
                        coral: { 400: '#fb7185', 500: '#f43f5e', 600: '#e11d48' },
                        gold: { 400: '#fbbf24', 500: '#f59e0b', 600: '#d97706' },
                        ink: { 50: '#f8fafc', 100: '#f1f5f9', 200: '#e2e8f0', 300: '#cbd5e1', 400: '#94a3b8', 500: '#64748b', 600: '#475569', 700: '#334155', 800: '#1e293b', 900: '#0f172a', 950: '#020617' }
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'slide-up': 'slideUp 0.5s ease forwards',
                        'bounce-soft': 'bounceSoft 2s ease-in-out infinite',
                    },
                    keyframes: {
                        float: { '0%, 100%': { transform: 'translateY(0)' }, '50%': { transform: 'translateY(-8px)' } },
                        slideUp: { '0%': { opacity: '0', transform: 'translateY(20px)' }, '100%': { opacity: '1', transform: 'translateY(0)' } },
                        bounceSoft: { '0%, 100%': { transform: 'translateY(0)' }, '50%': { transform: 'translateY(-5px)' } },
                    }
                }
            }
        }
    </script>
    
    <style>
        * { -webkit-font-smoothing: antialiased; }
        body { font-family: 'Outfit', sans-serif; }
        h1, h2, h3, h4, h5, h6, .font-display { font-family: 'Sora', sans-serif; }
        
        /* Enhanced Category Sidebar Styles */
        .category-sidebar-container {
            position: relative;
            max-height: 700px;
            overflow-y: auto;
            overflow-x: visible !important;
            scrollbar-width: thin;
            scrollbar-color: #6366f1 #f1f5f9;
        }
        
        .category-sidebar-container::-webkit-scrollbar {
            width: 6px;
        }
        
        .category-sidebar-container::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 10px;
        }
        
        .category-sidebar-container::-webkit-scrollbar-thumb {
            background: #6366f1;
            border-radius: 10px;
        }
        
        .category-sidebar-container::-webkit-scrollbar-thumb:hover {
            background: #4f46e5;
        }
        
        /* Category Item Styling */
        .category-sidebar-item { 
            position: relative; 
            transition: all 0.2s ease; 
            border-left: 3px solid transparent; 
            overflow: visible !important;
        }
        
        .category-sidebar-item:hover { 
            background: linear-gradient(90deg, rgba(99,102,241,0.08) 0%, transparent 100%); 
            border-left-color: #6366f1; 
        }
        
        .category-sidebar-item:hover > a .cat-icon { 
            color: #6366f1; 
            transform: scale(1.1);
        }
        
        /* Product Count Badge */
        .category-product-count {
            font-size: 0.7rem;
            font-weight: 700;
            min-width: 26px;
            height: 26px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            border-radius: 6px;
            box-shadow: 0 2px 6px rgba(99, 102, 241, 0.3);
            transition: all 0.3s ease;
            margin-right: 8px;
            flex-shrink: 0;
        }
        
        .category-sidebar-item:hover .category-product-count {
            transform: scale(1.1);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
        }
        
        /* Enhanced Subcategory Panel */
        .cat-submenu-enhanced { 
            display: none; 
            position: absolute; 
            left: 100%; 
            top: 0;
            min-width: 420px;
            max-width: 500px;
            background: white; 
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); 
            border-radius: 16px; 
            z-index: 9999;
            padding: 20px;
            border: 1px solid #e2e8f0;
            animation: fadeIn 0.2s ease forwards;
            transform-origin: top left;
            overflow: hidden;
        }
        
        .cat-submenu-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 12px;
            margin-bottom: 16px;
            border-bottom: 2px solid #f3f4f6;
        }
        
        .cat-submenu-header h4 {
            font-weight: 700;
            color: #1f2937;
            font-size: 1.1rem;
        }
        
        .total-products-badge {
            background: #f3f4f6;
            color: #6b7280;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 20px;
        }
        
        .subcategory-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-bottom: 16px;
        }
        
        .subcategory-item-enhanced {
            padding: 10px 12px;
            background: #f9fafb;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
            transition: all 0.2s ease;
        }
        
        .subcategory-item-enhanced:hover {
            background: white;
            border-color: #6366f1;
            transform: translateX(3px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.1);
        }
        
        .subcategory-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 6px;
        }
        
        .subcategory-name {
            font-weight: 600;
            color: #374151;
            font-size: 0.85rem;
        }
        
        .subcategory-count {
            background: #6366f1;
            color: white;
            font-size: 0.7rem;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 10px;
            min-width: 28px;
            text-align: center;
        }
        
        .grandchildren-list {
            margin-top: 6px;
            padding-left: 8px;
            border-left: 2px solid #e5e7eb;
        }
        
        .grandchild-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 4px 0;
            color: #6b7280;
            font-size: 0.8rem;
        }
        
        .grandchild-name {
            flex: 1;
        }
        
        .grandchild-count {
            color: #9ca3af;
            font-size: 0.7rem;
            font-weight: 600;
        }
        
        /* Top Products Section */
        .top-products-section {
            margin-top: 16px;
            padding-top: 16px;
            border-top: 2px solid #f3f4f6;
        }
        
        .top-products-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }
        
        .top-products-title {
            font-weight: 700;
            color: #1f2937;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .view-all-link {
            color: #6366f1;
            font-weight: 600;
            font-size: 0.8rem;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        
        .view-all-link:hover {
            text-decoration: underline;
        }
        
        .top-products-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .top-product-item {
            display: flex;
            align-items: center;
            padding: 8px 10px;
            background: white;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            transition: all 0.2s ease;
            text-decoration: none !important;
        }
        
        .top-product-item:hover {
            background: #f8fafc;
            border-color: #6366f1;
            transform: translateX(3px);
        }
        
        .top-product-rank {
            background: #f3f4f6;
            color: #6b7280;
            font-size: 0.75rem;
            font-weight: 700;
            width: 22px;
            height: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            margin-right: 10px;
            flex-shrink: 0;
        }
        
        .top-product-info {
            flex: 1;
            min-width: 0;
        }
        
        .top-product-name {
            color: #374151;
            font-size: 0.8rem;
            font-weight: 500;
            margin-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .top-product-price {
            color: #6366f1;
            font-size: 0.75rem;
            font-weight: 700;
        }
        
        /* Animation for submenu */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateX(-10px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        /* Ensure the sidebar container doesn't clip the submenu */
        aside > div {
            overflow: visible !important;
        }
        
        .category-sidebar-item:hover > .cat-submenu-enhanced { 
            display: block; 
            margin-left: 4px;
        }
        
        /* Responsive adjustments */
        @media (max-width: 1024px) { 
            .cat-submenu-enhanced { 
                display: none !important; 
            } 
            
            .category-sidebar-container {
                max-height: 500px;
            }
        }
        
        @media (max-width: 768px) {
            .category-product-count {
                min-width: 22px;
                height: 22px;
                font-size: 0.65rem;
            }
        }
        
        /* Ensure proper stacking */
        main {
            position: relative;
            z-index: 1;
        }
        
        aside {
            position: relative;
            z-index: 10;
        }
        
        /* Product Cards */
        .product-card { 
            transition: all 0.3s ease; 
            background: white; 
            border-radius: 16px; 
            overflow: hidden; 
            position: relative;
            z-index: 1;
        }
        .product-card:hover { transform: translateY(-6px); box-shadow: 0 20px 40px -15px rgba(0,0,0,0.15); }
        .product-card:hover .product-image { transform: scale(1.05); }
        .product-card:hover .product-actions { opacity: 1; transform: translateY(0); }
        .product-card:hover .quick-add { opacity: 1; }
        .product-image { transition: transform 0.4s ease; }
        .product-actions { opacity: 0; transform: translateY(8px); transition: all 0.3s ease; }
        .quick-add { opacity: 0; transition: opacity 0.3s ease; }
        
        /* Buttons */
        .btn-primary { background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: white; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(99,102,241,0.3); }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(99,102,241,0.4); }
        
        /* Hero Banner - Smaller */
        .hero-banner { background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4c1d95 100%); }
        .flash-gradient { background: linear-gradient(135deg, #dc2626 0%, #ea580c 50%, #f59e0b 100%); }
        
        /* Timer */
        .timer-box { background: rgba(0,0,0,0.2); backdrop-filter: blur(8px); }
        
        /* Category Cards */
        .category-card { transition: all 0.3s ease; }
        .category-card:hover { transform: translateY(-4px); box-shadow: 0 15px 30px -10px rgba(0,0,0,0.12); }
        
        /* Trust Badges */
        .trust-badge { transition: all 0.3s ease; }
        .trust-badge:hover { transform: translateY(-3px); }
        
        /* Section Headers */
        .section-line { position: relative; padding-left: 16px; }
        .section-line::before { content: ''; position: absolute; left: 0; top: 50%; transform: translateY(-50%); width: 4px; height: 24px; border-radius: 4px; }
        .section-line.brand::before { background: linear-gradient(180deg, #6366f1, #a855f7); }
        .section-line.fire::before { background: linear-gradient(180deg, #ef4444, #f97316); }
        .section-line.mint::before { background: linear-gradient(180deg, #10b981, #14b8a6); }
        .section-line.purple::before { background: linear-gradient(180deg, #8b5cf6, #a855f7); }
        
        /* Glass */
        .glass { background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); }
        
        /* Badges */
        .badge-new { background: linear-gradient(135deg, #8b5cf6, #a855f7); }
        .badge-hot { background: linear-gradient(135deg, #ef4444, #f97316); }
        .badge-sale { background: linear-gradient(135deg, #dc2626, #ea580c); }
        .badge-imported { background: linear-gradient(135deg, #0ea5e9, #06b6d4); }
        .badge-local { background: linear-gradient(135deg, #10b981, #14b8a6); }
        
        /* Stars */
        .star-filled { color: #fbbf24; }
        .star-empty { color: #e2e8f0; }
        
        /* Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #6366f1; border-radius: 3px; }
        
        .line-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }
        .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        html { scroll-behavior: smooth; }

        /* Modal Animations */
        @keyframes scale-in {
            from { transform: scale(0.9); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        .animate-scale-in {
            animation: scale-in 0.3s ease forwards;
        }

        /* Toast Animation */
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        .toast-notification {
            animation: slideIn 0.3s ease forwards;
        }
    </style>
</head>
<body class="bg-ink-50 font-body">

<!-- TOP BAR -->
<div class="bg-ink-900 text-white text-sm py-2">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-5">
                <span class="flex items-center gap-2">
                    <i class="fas fa-shield-alt text-mint-400 text-xs"></i>
                    <span class="hidden sm:inline">100% Secure Escrow</span>
                </span>
                <span class="hidden md:flex items-center gap-2">
                    <i class="fas fa-truck text-gold-400 text-xs"></i>
                    <span>Free Shipping $50+</span>
                </span>
            </div>
            <div class="flex items-center gap-4">
                <a href="{{ route('vendor.onboard.create') }}" class="hover:text-brand-300 transition">
                    <i class="fas fa-store text-xs mr-1"></i><span class="hidden sm:inline">Sell on {{ config('app.name') }}</span>
                </a>
                <span class="text-ink-600">|</span>
                <a href="#" id="helpLink" class="hover:text-brand-300 transition"><i class="fas fa-headset text-xs mr-1"></i>Help</a>
            </div>
        </div>
    </div>
</div>

<!-- HEADER -->
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
                <form method="GET" action="{{ route('marketplace.index') }}" class="relative w-full" id="searchForm">
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
    <div class="bg-gradient-to-r from-brand-600 to-purple-600 relative">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between">
                <!-- Navigation links -->
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
                    <a href="{{ route('jobs.index') }}" class="text-white px-4 py-3 hover:bg-white/10 transition rounded-lg flex items-center gap-2 text-sm font-medium">
                        <i class="fas fa-briefcase text-emerald-400"></i>Jobs
                    </a>
                    <a href="{{ route('services.index') }}" class="text-white px-4 py-3 hover:bg-white/10 transition rounded-lg flex items-center gap-2 text-sm font-medium">
                        <i class="fas fa-tools text-purple-300"></i>Services
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

<!-- MAIN -->
<main class="bg-ink-50 min-h-screen">
    <div class="container mx-auto px-4 py-4">
        <div class="flex gap-5">
            
            <!-- Left Sidebar - ENHANCED CATEGORIES WITH PRODUCT COUNTS -->
            <aside class="hidden lg:block w-64 flex-shrink-0" style="z-index: 50;">
                <div class="bg-white rounded-xl shadow-sm sticky top-32 border border-ink-100" style="overflow: visible;">
                    <div class="bg-gradient-to-r from-brand-600 to-purple-600 text-white px-4 py-3 font-semibold flex items-center gap-2 text-sm">
                        <i class="fas fa-th-large"></i>Browse Categories
                        <span class="text-xs bg-white/20 px-2 py-0.5 rounded ml-auto">
                            {{ $categories->sum('listings_count') }}+ Products
                        </span>
                    </div>
                    
                    <div class="category-sidebar-container">
                        @php 
                        $catIcons = [
                            'fas fa-car', 'fas fa-laptop', 'fas fa-mobile-alt', 'fas fa-couch', 
                            'fas fa-tshirt', 'fas fa-blender', 'fas fa-futbol', 'fas fa-baby-carriage',
                            'fas fa-gem', 'fas fa-book', 'fas fa-pills', 'fas fa-gamepad',
                            'fas fa-utensils', 'fas fa-tools', 'fas fa-seedling', 'fas fa-bath'
                        ]; 
                        @endphp
                        
                        @foreach($categories as $i => $cat)
                        <div class="category-sidebar-item">
                            <a href="{{ route('marketplace.index', ['category' => $cat->id]) }}" 
                               class="flex items-center justify-between px-4 py-3 text-ink-600 transition hover:no-underline">
                                <span class="flex items-center gap-2">
                                    <!-- Product Count Badge -->
                                    <span class="category-product-count" title="{{ $cat->listings_count }} products">
                                        {{ $cat->listings_count > 99 ? '99+' : $cat->listings_count }}
                                    </span>
                                    
                                    <!-- Category Icon -->
                                    <i class="{{ $catIcons[$i % count($catIcons)] }} cat-icon text-ink-400 w-4 text-sm transition-transform"></i>
                                    
                                    <!-- Category Name -->
                                    <span class="text-sm font-medium">{{ $cat->name }}</span>
                                </span>
                                
                                <!-- Chevron if has subcategories -->
                                @if($cat->children && $cat->children->count() > 0)
                                <i class="fas fa-chevron-right text-xs text-ink-300"></i>
                                @endif
                            </a>
                            
                            <!-- Enhanced Subcategory Panel - Shows on Hover -->
                            @if($cat->children && $cat->children->count() > 0)
                            <div class="cat-submenu-enhanced">
                                <!-- Header -->
                                <div class="cat-submenu-header">
                                    <h4>{{ $cat->name }}</h4>
                                    <span class="total-products-badge">{{ $cat->listings_count }} products</span>
                                </div>
                                
                                <!-- Subcategories Grid -->
                                <div class="subcategory-grid">
                                    @foreach($cat->children as $child)
                                    <div class="subcategory-item-enhanced">
                                        <div class="subcategory-header">
                                            <a href="{{ route('marketplace.index', ['category' => $child->id]) }}" 
                                               class="subcategory-name hover:text-brand-600 transition">
                                                {{ $child->name }}
                                            </a>
                                            <span class="subcategory-count">{{ $child->listings_count }}</span>
                                        </div>
                                        
                                        <!-- Grandchildren (if any) -->
                                        @if($child->children && $child->children->count() > 0)
                                        <div class="grandchildren-list">
                                            @foreach($child->children as $grandchild)
                                            <div class="grandchild-item">
                                                <a href="{{ route('marketplace.index', ['category' => $grandchild->id]) }}" 
                                                   class="grandchild-name hover:text-brand-600 transition">
                                                    {{ $grandchild->name }}
                                                </a>
                                                <span class="grandchild-count">{{ $grandchild->listings_count }}</span>
                                            </div>
                                            @endforeach
                                        </div>
                                        @endif
                                    </div>
                                    @endforeach
                                </div>
                                
                                <!-- Top 5 Products in this Category -->
                                @if($cat->top_products && $cat->top_products->count() > 0)
                                <div class="top-products-section">
                                    <div class="top-products-header">
                                        <div class="top-products-title">
                                            <i class="fas fa-star text-amber-400"></i>
                                            Popular Products
                                        </div>
                                        <a href="{{ route('marketplace.index', ['category' => $cat->id]) }}" class="view-all-link">
                                            View All
                                        </a>
                                    </div>
                                    
                                    <div class="top-products-list">
                                        @foreach($cat->top_products as $index => $product)
                                        <a href="{{ route('marketplace.show', $product) }}" class="top-product-item">
                                            <span class="top-product-rank">{{ $index + 1 }}</span>
                                            <div class="top-product-info">
                                                <div class="top-product-name" title="{{ $product->title }}">
                                                    {{ Str::limit($product->title, 35) }}
                                                </div>
                                                <div class="top-product-price">
                                                    UGX {{ number_format($product->price) }}
                                                </div>
                                            </div>
                                        </a>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                                
                                <!-- View All Link -->
                                <div class="mt-4 pt-4 border-t border-ink-100">
                                    <a href="{{ route('marketplace.index', ['category' => $cat->id]) }}" 
                                       class="inline-flex items-center justify-center w-full py-2 bg-brand-50 text-brand-600 rounded-lg font-semibold text-sm hover:bg-brand-100 transition">
                                        <i class="fas fa-store mr-2 text-sm"></i>
                                        View all {{ $cat->listings_count }} products in {{ $cat->name }}
                                    </a>
                                </div>
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
            </aside>
            
            <!-- Main Content -->
            <div class="flex-1 min-w-0 space-y-6" style="z-index: 1;">
                <!-- Featured Products Section -->
                @if(isset($featuredProducts) && $featuredProducts->count())
                <section>
                    <div class="flex items-center justify-between mb-4">
                        <div class="section-line brand">
                            <h2 class="text-lg font-bold text-ink-800 font-display">⭐ Featured Products</h2>
                        </div>
                        <a href="{{ route('marketplace.index') }}" class="text-brand-600 font-medium text-sm hover:underline">View All →</a>
                    </div>
                    <div id="featuredProductsGrid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 gap-4">
                        @foreach($featuredProducts->take(10) as $product)
                        <div class="product-card shadow-sm border border-ink-100">
                            <div class="relative aspect-square overflow-hidden bg-ink-50">
                                <a href="{{ route('marketplace.show', $product) }}">
                                    @if($product->images->first())
                                    <img src="{{ asset('storage/' . $product->images->first()->path) }}" alt="{{ $product->title }}" class="product-image w-full h-full object-cover">
                                    @else
                                    <div class="w-full h-full flex items-center justify-center"><i class="fas fa-image text-ink-300 text-3xl"></i></div>
                                    @endif
                                </a>
                                <div class="absolute top-2 left-2">
                                    @if($product->origin == 'imported')
                                    <span class="badge-imported text-white text-xs px-2 py-0.5 rounded-full font-medium"><i class="fas fa-plane mr-1"></i>Import</span>
                                    @else
                                    <span class="badge-local text-white text-xs px-2 py-0.5 rounded-full font-medium"><i class="fas fa-map-pin mr-1"></i>Local</span>
                                    @endif
                                </div>
                                <div class="product-actions absolute top-2 right-2 flex flex-col gap-1">
                                    <button data-quick-wishlist data-listing-id="{{ $product->id }}" class="w-8 h-8 bg-white rounded-full shadow flex items-center justify-center hover:bg-coral-50 transition">
                                        <i class="far fa-heart text-ink-500 hover:text-coral-500 text-sm"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="p-3">
                                <p class="text-xs text-ink-400 mb-1">{{ $product->category->name ?? 'General' }}</p>
                                <a href="{{ route('marketplace.show', $product) }}">
                                    <h3 class="text-sm font-medium text-ink-700 line-clamp-2 mb-2 hover:text-brand-600 transition h-10">{{ $product->title }}</h3>
                                </a>
                                <div class="flex items-center gap-1 mb-2">
                                    @for($s = 1; $s <= 5; $s++)<i class="fas fa-star text-xs {{ $s <= 4 ? 'star-filled' : 'star-empty' }}"></i>@endfor
                                    <span class="text-xs text-ink-400 ml-1">({{ rand(10,200) }})</span>
                                </div>
                                <div class="flex items-center justify-between mt-2">
                                    <div class="flex items-baseline">
                                        <span class="text-xs text-ink-500 mr-1">UGX</span>
                                        <span class="text-sm font-bold text-brand-600">{{ number_format($product->price) }}</span>
                                    </div>
                                    @if($product->stock > 0)
                                    <button data-quick-cart data-listing-id="{{ $product->id }}" 
                                            class="quick-add w-7 h-7 btn-primary rounded-lg flex items-center justify-center ml-2 flex-shrink-0">
                                        <i class="fas fa-shopping-cart text-xs"></i>
                                    </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </section>
                @endif
                
                <!-- SHOP BY CATEGORY -->
                <section>
                    <div class="flex items-center justify-between mb-4">
                        <div class="section-line mint">
                            <h2 class="text-lg font-bold text-ink-800 font-display">🏷️ Top Categories</h2>
                        </div>
                        <a href="{{ route('categories.index') }}" class="text-brand-600 font-medium text-sm hover:underline">View All Categories →</a>
                    </div>
                    <div class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 gap-3">
                        @php 
                        $catColors = [
                            ['bg-brand-50','text-brand-600'],['bg-pink-50','text-pink-600'],['bg-amber-50','text-amber-600'],
                            ['bg-emerald-50','text-emerald-600'],['bg-cyan-50','text-cyan-600'],['bg-purple-50','text-purple-600'],
                            ['bg-rose-50','text-rose-600'],['bg-teal-50','text-teal-600']
                        ]; 
                        @endphp
                        @foreach($categories->take(8) as $i => $cat)
                        @php $cc = $catColors[$i % 8]; @endphp
                        <a href="{{ route('marketplace.index', ['category' => $cat->id]) }}" class="category-card bg-white rounded-xl p-3 text-center shadow-sm border border-ink-100 group">
                            <div class="relative">
                                <div class="w-10 h-10 mx-auto mb-2 rounded-xl {{ $cc[0] }} {{ $cc[1] }} flex items-center justify-center transition-transform group-hover:scale-110">
                                    <i class="fas fa-{{ $cat->icon ?? $catIcons[$i % 12] }} text-lg"></i>
                                </div>
                                <!-- Category Product Count Badge -->
                                <span class="absolute -top-1 -right-1 bg-brand-600 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center">
                                    {{ $cat->listings_count > 9 ? '9+' : $cat->listings_count }}
                                </span>
                            </div>
                            <h3 class="text-xs font-medium text-ink-700 group-hover:text-brand-600 transition line-clamp-1">{{ $cat->name }}</h3>
                        </a>
                        @endforeach
                    </div>
                </section>

                <!-- TRENDING NOW -->
                <section>
                    <div class="flex items-center justify-between mb-4">
                        <div class="section-line fire">
                            <h2 class="text-lg font-bold text-ink-800 font-display">🔥 Trending Now</h2>
                        </div>
                        <a href="{{ route('marketplace.index') }}" class="text-brand-600 font-medium text-sm hover:underline">View All →</a>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 gap-4">
                        @foreach($newArrivals as $index => $product)
                        <div class="product-card shadow-sm border border-ink-100">
                            <div class="relative aspect-square overflow-hidden bg-ink-50">
                                <a href="{{ route('marketplace.show', $product) }}">
                                    @if($product->images->first())
                                    <img src="{{ asset('storage/' . $product->images->first()->path) }}" alt="{{ $product->title }}" class="product-image w-full h-full object-cover">
                                    @else
                                    <div class="w-full h-full flex items-center justify-center"><i class="fas fa-image text-ink-300 text-3xl"></i></div>
                                    @endif
                                </a>
                                <div class="absolute top-2 left-2 flex flex-col gap-1">
                                    @if($index < 3)
                                    <span class="badge-hot text-white text-xs px-2 py-0.5 rounded-full font-bold"><i class="fas fa-fire mr-1"></i>HOT</span>
                                    @endif
                                    @if($product->origin == 'imported')
                                    <span class="badge-imported text-white text-xs px-2 py-0.5 rounded-full font-medium"><i class="fas fa-plane mr-1"></i>Import</span>
                                    @else
                                    <span class="badge-local text-white text-xs px-2 py-0.5 rounded-full font-medium"><i class="fas fa-map-pin mr-1"></i>Local</span>
                                    @endif
                                </div>
                                <div class="product-actions absolute top-2 right-2">
                                    <button data-quick-wishlist data-listing-id="{{ $product->id }}" class="w-8 h-8 bg-white rounded-full shadow flex items-center justify-center hover:bg-coral-50 transition">
                                        <i class="far fa-heart text-ink-500 hover:text-coral-500 text-sm"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="p-3">
                                <p class="text-xs text-ink-400 mb-1">{{ $product->category->name ?? 'General' }}</p>
                                <a href="{{ route('marketplace.show', $product) }}">
                                    <h3 class="text-sm font-medium text-ink-700 line-clamp-2 mb-2 hover:text-brand-600 transition h-10">{{ $product->title }}</h3>
                                </a>
                                <div class="flex items-center gap-1 mb-2">
                                    @for($s = 1; $s <= 5; $s++)<i class="fas fa-star text-xs {{ $s <= rand(3,5) ? 'star-filled' : 'star-empty' }}"></i>@endfor
                                    <span class="text-xs text-ink-400 ml-1">({{ rand(10,200) }})</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-baseline gap-1">
                                        <span class="text-xs text-ink-500">UGX</span>
                                        <span class="text-base font-bold text-brand-600">{{ number_format($product->price) }}</span>
                                    </div>
                                    @if($product->stock > 0)
                                    <button data-quick-cart data-listing-id="{{ $product->id }}" class="quick-add w-8 h-8 btn-primary rounded-lg flex items-center justify-center">
                                        <i class="fas fa-shopping-cart text-xs"></i>
                                    </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </section>
                
                <!-- JUST ARRIVED -->
                @if(isset($recentProducts) && $recentProducts->count())
                <section>
                    <div class="flex items-center justify-between mb-4">
                        <div class="section-line purple">
                            <h2 class="text-lg font-bold text-ink-800 font-display">🆕 Just Arrived</h2>
                        </div>
                        <a href="{{ route('marketplace.index', ['sort' => 'newest']) }}" class="text-brand-600 font-medium text-sm hover:underline">View All →</a>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-6 gap-4">
                        @foreach($recentProducts->take(6) as $product)
                        <div class="product-card shadow-sm border border-ink-100">
                            <div class="relative aspect-square overflow-hidden bg-ink-50">
                                <a href="{{ route('marketplace.show', $product) }}">
                                    @if($product->images->first())
                                    <img src="{{ asset('storage/' . $product->images->first()->path) }}" 
                                         alt="{{ $product->title }}" 
                                         class="product-image w-full h-full object-cover">
                                    @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <i class="fas fa-image text-ink-300 text-3xl"></i>
                                    </div>
                                    @endif
                                </a>
                                <span class="absolute top-2 left-2 badge-new text-white text-xs px-2 py-0.5 rounded-full font-medium">NEW</span>
                                
                                <div class="product-actions absolute top-2 right-2 flex flex-col gap-1">
                                    <button data-quick-wishlist data-listing-id="{{ $product->id }}" 
                                            class="w-8 h-8 bg-white rounded-full shadow flex items-center justify-center hover:bg-coral-50 transition">
                                        <i class="far fa-heart text-ink-500 hover:text-coral-500 text-sm"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="p-3">
                                <p class="text-xs text-ink-400 mb-1">{{ $product->category->name ?? 'General' }}</p>
                                <a href="{{ route('marketplace.show', $product) }}">
                                    <h3 class="text-sm font-medium text-ink-700 line-clamp-2 mb-2 hover:text-brand-600 transition h-10">
                                        {{ $product->title }}
                                    </h3>
                                </a>
                                
                                <div class="flex items-center justify-between mt-3">
                                    <div class="flex items-baseline gap-1">
                                        <span class="text-xs text-ink-500">UGX</span>
                                        <span class="text-sm font-bold text-brand-600">{{ number_format($product->price) }}</span>
                                    </div>
                                    
                                    @if($product->stock > 0)
                                    <button data-quick-cart data-listing-id="{{ $product->id }}" 
                                            class="quick-add w-7 h-7 btn-primary rounded-lg flex items-center justify-center ml-2 flex-shrink-0">
                                        <i class="fas fa-shopping-cart text-xs"></i>
                                    </button>
                                    @else
                                    <button class="w-7 h-7 bg-gray-200 rounded-lg flex items-center justify-center ml-2 cursor-not-allowed" 
                                            title="Out of Stock">
                                        <i class="fas fa-times text-gray-500 text-xs"></i>
                                    </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </section>
                @endif
                
                <!-- TRUST BADGES -->
                <section>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <div class="trust-badge bg-white rounded-xl p-4 flex items-center gap-3 shadow-sm border border-ink-100">
                            <div class="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-shield-alt text-emerald-500 text-lg"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-ink-800 text-sm">Secure Escrow</h4>
                                <p class="text-xs text-ink-500">Money protected</p>
                            </div>
                        </div>
                        <div class="trust-badge bg-white rounded-xl p-4 flex items-center gap-3 shadow-sm border border-ink-100">
                            <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-truck text-blue-500 text-lg"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-ink-800 text-sm">Fast Delivery</h4>
                                <p class="text-xs text-ink-500">Nationwide</p>
                            </div>
                        </div>
                        <div class="trust-badge bg-white rounded-xl p-4 flex items-center gap-3 shadow-sm border border-ink-100">
                            <div class="w-10 h-10 bg-purple-50 rounded-xl flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-headset text-purple-500 text-lg"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-ink-800 text-sm">24/7 Support</h4>
                                <p class="text-xs text-ink-500">Always here</p>
                            </div>
                        </div>
                        <div class="trust-badge bg-white rounded-xl p-4 flex items-center gap-3 shadow-sm border border-ink-100">
                            <div class="w-10 h-10 bg-amber-50 rounded-xl flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-undo text-amber-500 text-lg"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-ink-800 text-sm">Easy Returns</h4>
                                <p class="text-xs text-ink-500">30-day policy</p>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
            
            <!-- Right Sidebar - SMALLER -->
            <aside class="hidden xl:block w-44 flex-shrink-0">
                <div class="sticky top-32 space-y-4">
                    <!-- New User Promo -->
                    <div class="promo-gradient rounded-xl p-4 text-white text-center">
                        <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-2">
                            <i class="fas fa-gift text-lg"></i>
                        </div>
                        <h4 class="font-bold text-sm mb-1">New User?</h4>
                        <p class="text-white/80 text-xs mb-3">10% off first order</p>
                        <a href="{{ route('register') }}" class="block bg-white text-brand-600 py-2 rounded-lg font-bold text-xs hover:bg-ink-100 transition">
                            Sign Up
                        </a>
                    </div>
                    
                    <!-- Top Selling -->
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-ink-100">
                        <h4 class="font-bold text-ink-800 mb-3 flex items-center gap-1 text-sm">
                            <i class="fas fa-trophy text-gold-500"></i>Top Selling
                        </h4>
                        <div class="space-y-3">
                            @foreach(($topSelling ?? $newArrivals)->take(4) as $i => $product)
                            <a href="{{ route('marketplace.show', $product) }}" class="flex items-center gap-2 group">
                                <span class="w-5 h-5 rounded flex items-center justify-center text-xs font-bold 
                                    {{ $i == 0 ? 'bg-gold-100 text-gold-600' : 'bg-ink-100 text-ink-500' }}">
                                    {{ $i + 1 }}
                                </span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs text-ink-600 line-clamp-1 group-hover:text-brand-600">{{ $product->title }}</p>
                                    <p class="text-xs font-bold text-brand-600">UGX {{ number_format($product->price) }}</p>
                                </div>
                            </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</main>

<!-- FOOTER -->
<footer class="bg-ink-900 text-white pt-10 pb-6">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-2 md:grid-cols-5 gap-6 mb-8">
            <div class="col-span-2 md:col-span-1">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-8 h-8 bg-gradient-to-br from-brand-500 to-purple-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-store text-white text-sm"></i>
                    </div>
                    <span class="text-lg font-bold font-display">{{ config('app.name') }}</span>
                </div>
                <p class="text-ink-400 text-xs mb-4 leading-relaxed">Your trusted marketplace with escrow protection.</p>
                <div class="flex gap-2">
                    <a href="#" class="w-8 h-8 bg-ink-800 rounded-lg flex items-center justify-center hover:bg-brand-600 transition text-sm"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="w-8 h-8 bg-ink-800 rounded-lg flex items-center justify-center hover:bg-sky-500 transition text-sm"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="w-8 h-8 bg-ink-800 rounded-lg flex items-center justify-center hover:bg-pink-600 transition text-sm"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
            
            <div>
                <h5 class="font-bold mb-3 text-sm">Company</h5>
                <ul class="space-y-2 text-xs">
                    <li><a href="{{ route('site.about') }}" class="text-ink-400 hover:text-white transition">About Us</a></li>
                    <li><a href="{{ route('site.howItWorks') }}" class="text-ink-400 hover:text-white transition">How It Works</a></li>
                    <li><a href="{{ route('site.vendorBenefits') }}" class="text-ink-400 hover:text-white transition">Vendor Benefits</a></li>
                    <li><a href="{{ route('site.contact') }}" class="text-ink-400 hover:text-white transition">Contact Us</a></li>
                </ul>
            </div>

            <div>
                <h5 class="font-bold mb-3 text-sm">Jobs & Services</h5>
                <ul class="space-y-2 text-xs">
                    <li><a href="{{ route('jobs.index') }}" class="text-ink-400 hover:text-white transition">Browse Jobs</a></li>
                    <li><a href="{{ route('services.index') }}" class="text-ink-400 hover:text-white transition">Find Services</a></li>
                </ul>
            </div>
            
            <div>
                <h5 class="font-bold mb-3 text-sm">Support</h5>
                <ul class="space-y-2 text-xs">
                    <li><a href="{{ route('site.faq') }}" class="text-ink-400 hover:text-white transition">FAQ</a></li>
                    <li><a href="#" class="text-ink-400 hover:text-white transition">Help Center</a></li>
                    <li><a href="#" class="text-ink-400 hover:text-white transition">Shipping Info</a></li>
                    <li><a href="#" class="text-ink-400 hover:text-white transition">Returns & Refunds</a></li>
                </ul>
            </div>
            
            <div>
                <h5 class="font-bold mb-3 text-sm">Legal</h5>
                <ul class="space-y-2 text-xs">
                    <li><a href="{{ route('site.terms') }}" class="text-ink-400 hover:text-white transition">Terms & Conditions</a></li>
                    <li><a href="{{ route('site.privacy') }}" class="text-ink-400 hover:text-white transition">Privacy Policy</a></li>
                    <li><a href="#" class="text-ink-400 hover:text-white transition">Cookie Policy</a></li>
                    <li><a href="#" class="text-ink-400 hover:text-white transition">Dispute Resolution</a></li>
                </ul>
            </div>
            
            <div>
                <h5 class="font-bold mb-3 text-sm">For Vendors</h5>
                <ul class="space-y-2 text-xs">
                    <li><a href="{{ route('vendor.onboard.create') }}" class="text-ink-400 hover:text-white transition">Sell on Platform</a></li>
                    <li><a href="#" class="text-ink-400 hover:text-white transition">Vendor Dashboard</a></li>
                    <li><a href="#" class="text-ink-400 hover:text-white transition">Seller Resources</a></li>
                    <li><a href="#" class="text-ink-400 hover:text-white transition">Commission Rates</a></li>
                </ul>
            </div>
        </div>
        
        <div class="border-t border-ink-800 pt-4">
            <div class="flex flex-col md:flex-row items-center justify-between gap-3">
                <p class="text-ink-500 text-xs">© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                <div class="flex items-center gap-2">
                    <span class="text-ink-500 text-xs">We accept:</span>
                    <div class="flex gap-1">
                        <div class="w-8 h-5 bg-ink-800 rounded flex items-center justify-center"><i class="fab fa-cc-visa text-ink-400 text-sm"></i></div>
                        <div class="w-8 h-5 bg-ink-800 rounded flex items-center justify-center"><i class="fab fa-cc-mastercard text-ink-400 text-sm"></i></div>
                        <div class="w-8 h-5 bg-ink-800 rounded flex items-center justify-center"><i class="fas fa-mobile-alt text-ink-400 text-xs"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- MOBILE MENU -->
<div id="mobileMenu" class="hidden fixed inset-0 z-50">
    <div class="absolute inset-0 bg-ink-900/80 backdrop-blur-sm" onclick="toggleMobileMenu()"></div>
    <div class="absolute left-0 top-0 h-full w-72 bg-white shadow-2xl overflow-y-auto">
        <div class="p-4 border-b border-ink-100 flex items-center justify-between">
            <span class="text-lg font-bold text-ink-800 font-display">Menu</span>
            <button onclick="toggleMobileMenu()" class="w-8 h-8 bg-ink-100 rounded-full flex items-center justify-center">
                <i class="fas fa-times text-ink-500 text-sm"></i>
            </button>
        </div>
        <nav class="p-4 space-y-2">
            <a href="{{ route('marketplace.index') }}" class="flex items-center gap-3 py-2.5 text-ink-600 hover:text-brand-600 transition">
                <i class="fas fa-fire w-5 text-gold-500"></i>Deals
            </a>
            <a href="{{ route('marketplace.index', ['origin' => 'imported']) }}" class="flex items-center gap-3 py-2.5 text-ink-600 hover:text-brand-600 transition">
                <i class="fas fa-plane w-5 text-cyan-500"></i>Imported
            </a>
            <a href="{{ route('marketplace.index', ['origin' => 'local']) }}" class="flex items-center gap-3 py-2.5 text-ink-600 hover:text-brand-600 transition">
                <i class="fas fa-map-marker-alt w-5 text-mint-500"></i>Local
            </a>
            <a href="{{ route('jobs.index') }}" class="flex items-center gap-3 py-2.5 text-ink-600 hover:text-brand-600 transition">
                <i class="fas fa-briefcase w-5 text-emerald-500"></i>Jobs
            </a>
            <a href="{{ route('services.index') }}" class="flex items-center gap-3 py-2.5 text-ink-600 hover:text-brand-600 transition">
                <i class="fas fa-tools w-5 text-purple-500"></i>Services
            </a>
            <a href="{{ route('vendor.onboard.create') }}" class="flex items-center gap-3 py-2.5 text-ink-600 hover:text-brand-600 transition">
                <i class="fas fa-store w-5 text-pink-500"></i>Sell
            </a>
            <div class="border-t border-gray-200 pt-2 mt-2"></div>
            <a href="{{ route('site.howItWorks') }}" class="flex items-center gap-3 py-2.5 text-ink-600 hover:text-brand-600 transition">
                <i class="fas fa-question-circle w-5 text-yellow-500"></i>How It Works
            </a>
            <a href="{{ route('site.vendorBenefits') }}" class="flex items-center gap-3 py-2.5 text-ink-600 hover:text-brand-600 transition">
                <i class="fas fa-star w-5 text-gold-500"></i>Vendor Benefits
            </a>
            <a href="{{ route('site.faq') }}" class="flex items-center gap-3 py-2.5 text-ink-600 hover:text-brand-600 transition">
                <i class="fas fa-comments w-5 text-green-500"></i>FAQ
            </a>
            <a href="{{ route('site.about') }}" class="flex items-center gap-3 py-2.5 text-ink-600 hover:text-brand-600 transition">
                <i class="fas fa-info-circle w-5 text-blue-500"></i>About Us
            </a>
            <a href="{{ route('site.contact') }}" class="flex items-center gap-3 py-2.5 text-ink-600 hover:text-brand-600 transition">
                <i class="fas fa-envelope w-5 text-purple-500"></i>Contact
            </a>
        </nav>
        <div class="p-4 border-t border-ink-100">
            <h4 class="font-bold text-ink-800 mb-2 text-sm">Top Categories</h4>
            @foreach($categories->take(6) as $cat)
            <a href="{{ route('marketplace.index', ['category' => $cat->id]) }}" class="block py-1.5 text-sm text-ink-500 hover:text-brand-600 transition flex items-center justify-between">
                <span>{{ $cat->name }}</span>
                <span class="text-xs bg-brand-100 text-brand-600 px-2 py-0.5 rounded">{{ $cat->listings_count }}</span>
            </a>
            @endforeach
        </div>
    </div>
</div>

<!-- TOAST -->
<div id="toastContainer" class="fixed top-4 right-4 z-50 space-y-2"></div>

<!-- JavaScript -->
<script>
const isAuthenticated = @json(auth()->check());
const csrfToken = '{{ csrf_token() }}';

// Function to setup quick action buttons
function setupQuickActionButtons() {
    document.addEventListener('click', async function(e) {
        // Cart buttons
        const cartBtn = e.target.closest('[data-quick-cart]');
        if (cartBtn) {
            e.preventDefault();
            e.stopPropagation();
            const listingId = cartBtn.getAttribute('data-listing-id');
            await quickAddToCart(listingId, cartBtn);
            return;
        }
        
        // Wishlist buttons
        const wishlistBtn = e.target.closest('[data-quick-wishlist]');
        if (wishlistBtn) {
            e.preventDefault();
            e.stopPropagation();
            const listingId = wishlistBtn.getAttribute('data-listing-id');
            await quickAddToWishlist(listingId, wishlistBtn);
            return;
        }
    });
}

// Enhanced category hover functionality
function initCategoryNavigation() {
    const sidebarItems = document.querySelectorAll('.category-sidebar-item');
    
    sidebarItems.forEach(item => {
        const link = item.querySelector('a');
        const submenu = item.querySelector('.cat-submenu-enhanced');
        
        if (submenu) {
            let hideTimeout;
            let showTimeout;
            
            // Show submenu on hover with a slight delay
            item.addEventListener('mouseenter', function() {
                clearTimeout(hideTimeout);
                showTimeout = setTimeout(() => {
                    this.classList.add('hover-active');
                    if (submenu) {
                        submenu.style.display = 'block';
                        submenu.style.animation = 'fadeIn 0.2s ease forwards';
                        submenu.style.zIndex = '9999';
                    }
                }, 100);
            });
            
            item.addEventListener('mouseleave', function(e) {
                clearTimeout(showTimeout);
                const relatedTarget = e.relatedTarget || e.toElement;
                if (relatedTarget && !submenu.contains(relatedTarget)) {
                    hideTimeout = setTimeout(() => {
                        this.classList.remove('hover-active');
                        submenu.style.animation = 'fadeOut 0.2s ease forwards';
                        setTimeout(() => {
                            if (!this.matches(':hover') && !submenu.matches(':hover')) {
                                submenu.style.display = 'none';
                            }
                        }, 200);
                    }, 200);
                }
            });
            
            // Keep submenu open when hovering over it
            submenu.addEventListener('mouseenter', function() {
                clearTimeout(hideTimeout);
                item.classList.add('hover-active');
                this.style.display = 'block';
                this.style.animation = 'fadeIn 0.2s ease forwards';
                this.style.zIndex = '9999';
            });
            
            submenu.addEventListener('mouseleave', function(e) {
                const relatedTarget = e.relatedTarget || e.toElement;
                if (relatedTarget && !item.contains(relatedTarget)) {
                    hideTimeout = setTimeout(() => {
                        item.classList.remove('hover-active');
                        this.style.animation = 'fadeOut 0.2s ease forwards';
                        setTimeout(() => {
                            if (!item.matches(':hover') && !this.matches(':hover')) {
                                this.style.display = 'none';
                            }
                        }, 200);
                    }, 200);
                }
            });
        }
    });
}

// Add to cart function
let cartProcessing = false;
async function quickAddToCart(id, btn) {
    if (cartProcessing) return;
    cartProcessing = true;
    
    if (!isAuthenticated) { 
        showAuthModal(); 
        cartProcessing = false;
        return; 
    }
    
    const originalContent = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin text-xs"></i>';
    btn.disabled = true;
    
    try {
        const response = await fetch(`/buyer/cart/add/${id}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ quantity: 1 })
        });
        
        const data = await response.json();
        
        if (data.success) {
            btn.innerHTML = '<i class="fas fa-check text-xs"></i>';
            btn.classList.remove('bg-brand-600', 'hover:bg-brand-700');
            btn.classList.add('bg-green-500', 'hover:bg-green-600');
            
            if (data.cart_count) {
                updateCartCount(data.cart_count);
            }
            
            showToast('Added to cart!', 'success');
            
            setTimeout(() => {
                btn.innerHTML = originalContent;
                btn.classList.remove('bg-green-500', 'hover:bg-green-600');
                btn.classList.add('bg-brand-600', 'hover:bg-brand-700');
                btn.disabled = false;
            }, 1500);
        } else {
            throw new Error(data.message || 'Failed to add to cart');
        }
    } catch (error) {
        console.error('Cart error:', error);
        btn.innerHTML = originalContent;
        btn.disabled = false;
        showToast(error.message || 'Failed to add to cart', 'error');
    } finally {
        cartProcessing = false;
    }
}

// Wishlist function
let wishlistProcessing = false;
async function quickAddToWishlist(id, btn) {
    if (wishlistProcessing) return;
    wishlistProcessing = true;
    
    if (!isAuthenticated) { 
        showAuthModal(); 
        wishlistProcessing = false;
        return; 
    }
    
    const icon = btn.querySelector('i');
    
    try {
        const response = await fetch(`/buyer/wishlist/toggle/${id}`, { 
            method: 'POST', 
            headers: { 
                'Content-Type': 'application/json', 
                'X-CSRF-TOKEN': csrfToken, 
                'Accept': 'application/json' 
            } 
        });
        
        const data = await response.json();
        
        if (data.success) {
            if (data.in_wishlist) { 
                icon.classList.remove('far'); 
                icon.classList.add('fas', 'text-coral-500'); 
            } else { 
                icon.classList.remove('fas', 'text-coral-500'); 
                icon.classList.add('far'); 
            }
            showToast(data.message || 'Updated!', 'success');
            if (data.wishlist_count !== undefined) updateWishlistCount(data.wishlist_count);
        } else {
            throw new Error(data.message || 'Failed to update wishlist');
        }
    } catch (e) { 
        showToast('Failed to update wishlist', 'error'); 
    } finally {
        setTimeout(() => {
            wishlistProcessing = false;
        }, 500);
    }
}

// Update counts
function updateCartCount(c) { 
    document.querySelectorAll('.cart-count').forEach(el => { 
        el.textContent = c; 
        el.classList.toggle('hidden', c === 0); 
        if (c > 0) {
            el.classList.add('animate-pulse');
            setTimeout(() => el.classList.remove('animate-pulse'), 1000);
        }
    }); 
}

function updateWishlistCount(c) { 
    document.querySelectorAll('.wishlist-count').forEach(el => { 
        el.textContent = c; 
        el.classList.toggle('hidden', c === 0); 
        if (c > 0) {
            el.classList.add('animate-pulse');
            setTimeout(() => el.classList.remove('animate-pulse'), 1000);
        }
    }); 
}

// Load counts on page load
async function loadCartCount() { 
    try { 
        const res = await fetch('/cart/count'); 
        const data = await res.json(); 
        if (data.cart_count) updateCartCount(data.cart_count); 
    } catch(e){} 
}

async function loadWishlistCount() { 
    try { 
        const res = await fetch('/wishlist/count'); 
        const data = await res.json(); 
        if (data.count) updateWishlistCount(data.count); 
    } catch(e){} 
}

// Toast notification
function showToast(msg, type = 'info') {
    const colors = { success: 'bg-emerald-500', error: 'bg-coral-500', info: 'bg-brand-500' };
    const icons = {
        success: 'fa-check-circle',
        error: 'fa-times-circle',
        info: 'fa-info-circle'
    };
    
    const toast = document.createElement('div');
    toast.className = `${colors[type]} text-white px-4 py-3 rounded-xl shadow-lg flex items-center gap-2 text-sm animate-slide-up`;
    toast.innerHTML = `<i class="fas ${icons[type]}"></i><span>${msg}</span>`;
    document.getElementById('toastContainer').appendChild(toast);
    
    setTimeout(() => { 
        toast.style.opacity = '0'; 
        toast.style.transition = 'opacity 0.3s'; 
        setTimeout(() => toast.remove(), 300); 
    }, 3000);
}

// Modal functions
function showAuthModal() { 
    const modal = document.getElementById('authModal');
    if (modal) {
        modal.classList.remove('hidden'); 
        document.body.style.overflow = 'hidden'; 
    }
}

function closeAuthModal() { 
    const modal = document.getElementById('authModal');
    if (modal) {
        modal.classList.add('hidden'); 
        document.body.style.overflow = ''; 
    }
}

function toggleMobileMenu() { 
    const m = document.getElementById('mobileMenu'); 
    m.classList.toggle('hidden'); 
    document.body.style.overflow = m.classList.contains('hidden') ? '' : 'hidden'; 
}

// Initialize everything
document.addEventListener('DOMContentLoaded', function() {
    setupQuickActionButtons();
    initCategoryNavigation();
    
    // Load counts
    loadCartCount();
    loadWishlistCount();
});

// Close modals with Escape key
document.addEventListener('keydown', e => { 
    if (e.key === 'Escape') { 
        const mobileMenu = document.getElementById('mobileMenu');
        if (mobileMenu && !mobileMenu.classList.contains('hidden')) {
            toggleMobileMenu();
        }
    } 
});
</script>
</body>
</html>