<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} - Your Trusted Marketplace</title>
    <meta name="description" content="Shop securely with escrow protection. Buy local and imported products with confidence.">
     <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon.png') }}?v=2">
<link rel="shortcut icon" href="{{ asset('favicon.png') }}?v=2">
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
        
        /* Category Sidebar with Subcategory on Hover - FIXED Z-INDEX */
        .cat-sidebar-item { 
            position: relative; 
            transition: all 0.2s ease; 
            border-left: 3px solid transparent; 
            overflow: visible !important;
        }
        .cat-sidebar-item:hover { 
            background: linear-gradient(90deg, rgba(99,102,241,0.08) 0%, transparent 100%); 
            border-left-color: #6366f1; 
        }
        .cat-sidebar-item:hover > a .cat-icon { 
            color: #6366f1; 
            transform: scale(1.1);
        }
        
        /* Subcategory Panel - Shows on parent hover - FIXED Z-INDEX */
        .cat-submenu { 
            display: none; 
            position: absolute; 
            left: 100%; 
            top: 0;
            min-width: 420px;
            max-width: 500px;
            background: white; 
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); 
            border-radius: 16px; 
            z-index: 9999; /* MUCH HIGHER Z-INDEX */
            padding: 24px;
            border: 1px solid #e2e8f0;
            animation: fadeIn 0.2s ease forwards;
            transform-origin: top left;
        }
        
        
        /* Ensure the sidebar container doesn't clip the submenu */
        aside > div {
            overflow: visible !important;
        }

        /* Modal overlay */
#optionsModal {
    backdrop-filter: blur(8px);
}

/* Modal option buttons - Default state */
.modal-option-btn,
#modalColorOptions button,
#modalSizeOptions button {
    background: white !important;
    color: #374151 !important; /* gray-700 */
    border: 2px solid #d1d5db !important; /* gray-300 */
    font-weight: 500 !important;
    transition: all 0.2s ease !important;
}

.modal-option-btn:hover,
#modalColorOptions button:hover,
#modalSizeOptions button:hover {
    border-color: #6366f1 !important; /* primary/indigo */
    background: #eef2ff !important; /* indigo-50 */
    color: #4f46e5 !important;
}

/* Selected state */
.modal-option-btn.selected,
#modalColorOptions button.selected,
#modalSizeOptions button.selected,
button[data-option].selected {
    background: #6366f1 !important; /* primary/indigo-600 */
    color: white !important;
    border-color: #4f46e5 !important;
    font-weight: 600 !important;
}

/* Ensure proper contrast for selected state */
.option-btn.selected,
button[data-option].bg-primary {
    background: #6366f1 !important;
    color: white !important;
    border-color: #4f46e5 !important;
}

/* Variant info box */
#modalVariantInfo {
    background: linear-gradient(135deg, #eff6ff 0%, #e0e7ff 100%) !important;
    border-color: #c7d2fe !important;
}

#modalVariantInfo h4 {
    color: #1e293b !important;
}

#modalVariantInfo .text-gray-600 {
    color: #475569 !important;
}

#modalVariantPrice {
    color: #6366f1 !important;
}

#modalVariantStock {
    color: #475569 !important;
}

/* Confirm button - Make it visible and attractive */
#confirmModalOptionsBtn {
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%) !important;
    color: white !important;
    font-weight: 700 !important;
    border: none !important;
    box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3) !important;
}

#confirmModalOptionsBtn:hover:not(:disabled) {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%) !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4) !important;
}

#confirmModalOptionsBtn:disabled {
    background: #d1d5db !important;
    color: #9ca3af !important;
    cursor: not-allowed !important;
    box-shadow: none !important;
}

/* Cancel button */
button[onclick="closeOptionsModal()"] {
    background: white !important;
    color: #374151 !important;
    border: 2px solid #d1d5db !important;
    font-weight: 600 !important;
}

button[onclick="closeOptionsModal()"]:hover {
    background: #f9fafb !important;
    border-color: #9ca3af !important;
}

/* Close button (X) */
#optionsModal button[onclick="closeOptionsModal()"].w-8 {
    background: #f3f4f6 !important;
    color: #6b7280 !important;
}

#optionsModal button[onclick="closeOptionsModal()"].w-8:hover {
    background: #e5e7eb !important;
    color: #374151 !important;
}

/* Labels */
#optionsModal label {
    color: #1f2937 !important;
    font-weight: 600 !important;
}

/* Required asterisk */
#optionsModal label .text-red-500 {
    color: #ef4444 !important;
}

/* Modal title and description */
#optionsModal h3 {
    color: #111827 !important;
}

#optionsModal .text-gray-500 {
    color: #6b7280 !important;
}

/* Icon in modal header */
#optionsModal .fa-cogs {
    color: #6366f1 !important;
}

/* Selected options text */
#modalSelectedColorText,
#modalSelectedSizeText {
    color: #374151 !important;
    font-weight: 500 !important;
}

/* Ensure modal content is readable */
#optionsModal .bg-white {
    background: white !important;
}


.product-card .badge-imported,
.product-card .badge-local,
.product-card .badge-hot,
.product-card .badge-new,
.product-card .badge-sale {
    font-size: 0.65rem !important;
    padding: 0.15rem 0.4rem !important;
    line-height: 1.2 !important;
    margin: 0.05rem !important;
    border-radius: 0.25rem !important;
}

/* Reduce icon size in badges */
.product-card [class*="badge-"] i {
    font-size: 0.55rem !important;
    margin-right: 0.15rem !important;
}
        
        /* Adjust positioning for better visibility */
        .cat-sidebar-item:hover > .cat-submenu { 
            display: block; 
            margin-left: 4px;
        }
        
        /* Product Cards - Ensure they have lower z-index */
        .product-card { 
            transition: all 0.3s ease; 
            background: white; 
            border-radius: 16px; 
            overflow: hidden; 
            position: relative;
            z-index: 1; /* Lower z-index than submenu */
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

        /* Verified Badge - Twitter Style SVG */
        .verified-badge {
            width: 14px;
            height: 14px;
            margin-left: 3px;
            flex-shrink: 0;
            filter: drop-shadow(0 1px 2px rgba(29, 155, 240, 0.3));
        }
        
        /* Stars */
        .star-filled { color: #fbbf24; }
        .star-empty { color: #e2e8f0; }
        
        /* Calculator */
        .calc-card { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); }
        
        /* Promo */
        .promo-gradient { background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); }
        
        /* Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #6366f1; border-radius: 3px; }
        
        .line-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }
        .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        html { scroll-behavior: smooth; }

        /* Enhanced Subcategory Panel with Products */
.cat-submenu { 
    display: none; 
    position: absolute; 
    left: 100%; 
    top: 0;
    min-width: 480px; /* Slightly wider for better product display */
    max-width: 600px;
    background: white; 
    box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); 
    border-radius: 16px; 
    z-index: 9999; 
    padding: 20px;
    border: 1px solid #e2e8f0;
    animation: fadeIn 0.2s ease forwards;
    transform-origin: top left;
}

/* Subcategory product items */
.subcategory-product-item {
    transition: all 0.2s ease;
}

.subcategory-product-item:hover {
    background: #f1f5f9 !important;
    transform: translateX(3px);
}

/* Product images in submenu */
.subcategory-product-image {
    width: 32px;
    height: 32px;
    object-fit: cover;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
}

/* Featured products in main category */
.top-product-item {
    transition: all 0.2s ease;
}

.top-product-item:hover {
    background: #eef2ff !important;
}

/* Category product count badge */
.category-product-count {
    min-width: 24px;
    text-align: center;
}

/* Ensure proper spacing */
.cat-submenu-header {
    border-bottom: 1px solid #f1f5f9;
    padding-bottom: 12px;
}

/* Smooth transitions */
.cat-submenu {
    opacity: 0;
    transform: translateX(-10px);
    animation: slideInRight 0.2s ease forwards;
}

@keyframes slideInRight {
    from {
        opacity: 0;
        transform: translateX(-10px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

/* Make sure main content has proper stacking */
main {
    position: relative;
    z-index: 1;
}

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

/* Option Selection */
.option-btn {
    transition: all 0.2s ease;
}

.option-btn.selected {
    border-color: #4f46e5 !important;
    background-color: rgba(79, 70, 229, 0.1) !important;
    color: #4f46e5;
}

        /* Chatbot Styles */
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeOut {
            from {
                opacity: 1;
                transform: translateY(0);
            }
            to {
                opacity: 0;
                transform: translateY(-10px);
            }
        }
        
        .cat-sidebar-item.hover-active {
            background: linear-gradient(90deg, rgba(99,102,241,0.08) 0%, transparent 100%);
            border-left-color: #6366f1;
        }
        
        .cat-sidebar-item.hover-active > a .cat-icon {
            color: #6366f1;
        }
        
        /* Loading spinner */
        .loading-spinner {
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Improved Subcategory Links */
        .cat-submenu a {
            transition: all 0.2s ease;
            padding: 8px 12px;
            border-radius: 8px;
        }
        
        .cat-submenu a:hover {
            background: #f8fafc;
            transform: translateX(4px);
        }
        
        @media (max-width: 1024px) { 
            .cat-submenu { 
                display: none !important; 
            } 
        }
        
        /* IMPORTANT: Ensure the main content area has proper stacking */
        main {
            position: relative;
            z-index: 1;
        }
        
        /* Ensure sidebar has proper stacking context */
        aside {
            position: relative;
            z-index: 10; /* Higher than main content */
        }
    </style>
</head>
<body class="bg-ink-50 font-body">

<!-- SVG Gradient Definition for Verified Badge -->
<svg style="position:absolute;width:0;height:0;overflow:hidden" aria-hidden="true">
    <defs>
        <linearGradient id="verifiedGradient" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#1d9bf0"/>
            <stop offset="100%" style="stop-color:#1a8cd8"/>
        </linearGradient>
    </defs>
</svg>

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
<div class="flex items-center gap-3">
    <!-- App Download Buttons - More Visible -->
    <div class="flex items-center gap-2">
        <span class="hidden lg:inline text-ink-400 text-xs">Get the App:</span>
        <a href="/bebamart.apk" 
           class="flex items-center gap-1.5 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white px-3 py-1.5 rounded-full transition font-medium text-xs shadow-lg shadow-green-500/25 hover:shadow-green-500/40 hover:scale-105 transform" 
           download="bebamart.apk">
            <i class="fab fa-android text-sm"></i>
            <span class="hidden sm:inline">Android</span>
        </a>
        <a href="#" 
           class="flex items-center gap-1.5 bg-gradient-to-r from-gray-700 to-gray-900 hover:from-gray-800 hover:to-black text-white px-3 py-1.5 rounded-full transition font-medium text-xs shadow-lg shadow-gray-500/25 hover:shadow-gray-500/40 hover:scale-105 transform"
           id="iosDownloadBtn">
            <i class="fab fa-apple text-sm"></i>
            <span class="hidden sm:inline">iOS</span>
        </a>
    </div>
    
    <span class="text-ink-700 hidden sm:inline">|</span>
    
    <a href="{{ route('vendor.onboard.create') }}" class="hover:text-brand-300 transition">
        <i class="fas fa-store text-xs mr-1"></i>
        <span class="hidden sm:inline">Sell on {{ config('app.name') }}</span>
    </a>
    
    <span class="text-ink-600">|</span>
    
    <a href="#" id="helpLink" class="hover:text-brand-300 transition">
        <i class="fas fa-headset text-xs mr-1"></i>Help
    </a>
</div>
    </div>
</div>

<!-- HEADER -->
<header class="bg-white shadow-sm sticky top-0 z-50">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-between py-3">
           <a href="{{ route('welcome') }}" class="flex items-center gap-3 group">
    <div class="relative">
        {{-- Logo Image --}}
        <img src="{{ asset('images/logo.png') }}" 
             alt="{{ config('app.name') }} Logo" 
             class="w-12 h-12 object-contain transition-transform group-hover:scale-105"
             onerror="this.src='https://ui-avatars.com/api/?name=B+M&background=6366f1&color=fff'">
    </div>
    <div class="flex flex-col">
        <span class="text-xl font-bold text-gray-900 leading-tight font-display tracking-tight">
            {{ config('app.name') }}
        </span>
        <span class="hidden lg:block text-xs text-gray-500 font-medium">
            Trusted Marketplace
        </span>
    </div>
</a>
            
            <!-- Search - IMPROVED WITH FORM -->
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
    
  <!-- Navigation - IMPROVED WITH NEW LINKS -->
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
                
                <!-- NEW ADDITIONAL LINKS ON THE RIGHT -->
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
    
    <!-- Mobile Search - IMPROVED WITH FORM -->
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
            
<!-- Left Sidebar - Categories with Subcategory Hover -->
<aside class="hidden lg:block w-72 flex-shrink-0" style="z-index: 50;">
    <div class="bg-white rounded-xl shadow-sm sticky top-32 border border-ink-100" style="overflow: visible;">
        <div class="bg-gradient-to-r from-brand-600 to-purple-600 text-white px-4 py-3 font-semibold flex items-center gap-2 text-sm">
            <i class="fas fa-th-large"></i>Browse Categories
            <span class="text-xs bg-white/20 px-2 py-0.5 rounded ml-auto">
                {{ $categories->sum('listings_count') ?? $categories->count() * 10 }}+ Products
            </span>
        </div>
        
        <div class="category-sidebar-container">
           
            @foreach($categories->take(15) as $i => $cat)
            <div class="cat-sidebar-item">
                <a href="{{ route('marketplace.index', ['category' => $cat->id]) }}" 
                   class="flex items-center justify-between px-4 py-3 text-ink-600 transition hover:no-underline">
       <span class="flex items-center gap-2">
            <!-- Category Icon -->
            <i class="fas fa-{{ $cat->icon ?? 'tag' }} cat-icon text-ink-400 w-4 text-sm transition-transform"></i>
            
            <!-- Category Name -->
            <span class="text-sm font-medium">
                {{ $cat->name }} 
                <span class="text-brand-600 font-bold ml-1">
                    ({{ isset($cat->listings_count) ? ($cat->listings_count > 99 ? '99+' : $cat->listings_count) : rand(5,50) }})
                </span>
            </span>
        </span>
                    
                    <!-- Chevron if has subcategories -->
                    @if($cat->children && $cat->children->count() > 0)
                    <i class="fas fa-chevron-right text-xs text-ink-300"></i>
                    @endif
                </a>
                
                @if($cat->children && $cat->children->count() > 0)
                <div class="cat-submenu">
                    <!-- Header -->
                    <div class="cat-submenu-header flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <h4 class="text-lg font-bold text-ink-800">{{ $cat->name }}</h4>
                            <span class="text-xs bg-brand-100 text-brand-600 px-2 py-0.5 rounded-full font-bold">
                                {{ $cat->listings_count ?? rand(10,100) }}
                            </span>
                        </div>
                        <a href="{{ route('marketplace.index', ['category' => $cat->id]) }}" 
                           class="text-brand-600 text-sm font-medium hover:underline">
                            View All
                        </a>
                    </div>
                    
                    <!-- Subcategories Grid -->
                    <div class="grid grid-cols-2 gap-4">
                        @foreach($cat->children as $childIndex => $child)
                        <div class="subcategory-with-products">
                            <div class="mb-3">
                                <!-- Subcategory Header with Count -->
                                <div class="flex items-center justify-between mb-2">
                                    <a href="{{ route('marketplace.index', ['category' => $child->id]) }}" 
                                       class="font-semibold text-ink-700 hover:text-brand-600 text-sm flex items-center gap-1">
                                        <i class="fas fa-folder-open text-xs text-brand-500"></i>
                                        <span>{{ $child->name }}</span>
                                    </a>
                                    <span class="text-xs bg-ink-100 text-ink-600 px-2 py-0.5 rounded font-medium">
                                        {{ $child->listings_count ?? rand(2,20) }}
                                    </span>
                                </div>
                                
                                <!-- Products Display - ALWAYS VISIBLE (UP TO 5 PRODUCTS) -->
                                @if($child->top_products && $child->top_products->count() > 0)
                                    <div class="space-y-2">
                                        @foreach($child->top_products->take(5) as $product)
                                        <a href="{{ route('marketplace.show', $product) }}" 
                                           class="subcategory-product-item flex items-center gap-2 p-2 hover:bg-ink-50 rounded-lg transition">
                                            @if($product->images->first())
                                                <img src="{{ asset('storage/' . $product->images->first()->path) }}" 
                                                    alt="{{ $product->title }}" 
                                                    class="subcategory-product-image w-8 h-8 object-cover rounded border border-ink-100">
                                            @else
                                                <div class="w-8 h-8 bg-ink-100 rounded border border-ink-200 flex items-center justify-center">
                                                    <i class="fas fa-image text-ink-300 text-xs"></i>
                                                </div>
                                            @endif
                                            <div class="flex-1 min-w-0">
                                                <div class="subcategory-product-name text-xs text-ink-600 font-medium truncate" 
                                                     title="{{ $product->title }}">
                                                    {{ Str::limit($product->title, 20) }}
                                                </div>
                                                <div class="subcategory-product-price text-xs font-bold text-brand-600">
                                                    UGX {{ number_format($product->price) }}
                                                </div>
                                            </div>
                                        </a>
                                        @endforeach
                                        
                                        <!-- Show More Link -->
                                        @if(($child->listings_count ?? 0) > 5)
                                        <a href="{{ route('marketplace.index', ['category' => $child->id]) }}" 
                                           class="text-xs text-brand-600 font-medium hover:underline flex items-center gap-1 justify-end">
                                            +{{ ($child->listings_count ?? 5) - 5 }} more
                                            <i class="fas fa-chevron-right text-xs"></i>
                                        </a>
                                        @endif
                                    </div>
                                @else
                                    <div class="text-center py-2">
                                        <p class="text-xs text-ink-400">No products yet</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                    
                    <!-- Featured Products in Main Category -->
                    @if($cat->top_products && $cat->top_products->count() > 0)
                    <div class="mt-6 pt-6 border-t border-ink-100">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <h5 class="text-sm font-bold text-ink-800 flex items-center gap-2">
                                    <i class="fas fa-star text-amber-400"></i>
                                    Popular in {{ $cat->name }}
                                </h5>
                                <span class="text-xs bg-amber-100 text-amber-600 px-2 py-0.5 rounded-full">
                                    TOP {{ min(5, $cat->top_products->count()) }}
                                </span>
                            </div>
                            <a href="{{ route('marketplace.index', ['category' => $cat->id]) }}" 
                               class="text-brand-600 text-xs font-medium hover:underline">
                                See all {{ $cat->listings_count ?? 0 }}
                            </a>
                        </div>
                        <div class="space-y-2">
                            @foreach($cat->top_products->take(5) as $index => $product)
                            <a href="{{ route('marketplace.show', $product) }}" 
                               class="top-product-item flex items-center gap-3 p-2 hover:bg-brand-50 rounded-lg transition">
                                <span class="top-product-rank w-6 h-6 bg-brand-100 text-brand-600 text-xs font-bold rounded-full flex items-center justify-center">
                                    {{ $index + 1 }}
                                </span>
                                <div class="flex-1 min-w-0">
                                    <div class="top-product-name text-sm font-medium text-ink-700 truncate" 
                                         title="{{ $product->title }}">
                                        {{ Str::limit($product->title, 30) }}
                                    </div>
                                    <div class="top-product-price text-sm font-bold text-brand-600">
                                        UGX {{ number_format($product->price) }}
                                    </div>
                                </div>
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    
                    <!-- View All Button -->
                    <div class="mt-6 pt-4 border-t border-ink-100">
                        <a href="{{ route('marketplace.index', ['category' => $cat->id]) }}" 
                           class="flex items-center justify-center gap-2 w-full py-3 bg-brand-50 text-brand-600 rounded-lg font-semibold text-sm hover:bg-brand-100 transition">
                            <i class="fas fa-store text-brand-500"></i>
                            Browse all {{ $cat->listings_count ?? rand(10,100) }} products in {{ $cat->name }}
                            <i class="fas fa-arrow-right text-xs"></i>
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
                            @if($product->vendor)
    <div class="flex items-center text-xs text-ink-500 mb-2">
        <i class="fas fa-user-clock mr-1"></i>
        @php
            $createdAt = $product->vendor->created_at;
            $vy = $createdAt ? floor($createdAt->diffInDays(now()) / 365) : 0;
            $vm = $createdAt ? floor(($createdAt->diffInDays(now()) % 365) / 30) : 0;
            $totalDays = $createdAt ? $createdAt->diffInDays(now()) : 0;

            if ($vy >= 1) {
                $dt = $vy == 1 ? '1 year' : $vy . '+ years';
            } elseif ($vm >= 1) {
                $dt = $vm == 1 ? '1 month' : $vm . ' months';
            } else {
                $dt = $totalDays == 0 ? 'New' : $totalDays . 'd';
            }
        @endphp
        <span>{{ $dt }}</span>
        
        @if($product->vendor->user && $product->vendor->user->is_admin_verified)
            {{-- Professional X-Style Scalloped Badge --}}
            <svg class="ml-1" viewBox="0 0 24 24" style="width: 14px; height: 14px;" title="Verified Seller">
                <path fill="#1d9bf0" d="M22.25 12c0-1.43-.88-2.67-2.19-3.34.46-1.39.2-2.9-.81-3.91s-2.52-1.27-3.91-.81c-.67-1.31-1.91-2.19-3.34-2.19s-2.67.88-3.33 2.19c-1.4-.46-2.9-.2-3.92.81s-1.26 2.52-.8 3.91c-1.31.67-2.2 1.91-2.2 3.34s.89 2.67 2.2 3.34c-.46 1.39-.21 2.9.8 3.91s2.52 1.26 3.91.81c.67 1.31 1.91 2.19 3.34 2.19s2.67-.88 3.34-2.19c1.39.45 2.9.2 3.91-.81s1.27-2.52.81-3.91c1.31-.67 2.19-1.91 2.19-3.34z"></path>
                <path fill="#ffffff" d="M10.5 16.5l-3.5-3.5 1.4-1.4 2.1 2.1 5.6-5.6 1.4 1.4-7 7z"></path>
            </svg>
        @endif
    </div>
@endif
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
        <a href="{{ route('categories.index') ?? route('marketplace.index') }}" class="text-brand-600 font-medium text-sm hover:underline">View All Categories →</a>
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
                    {{ isset($cat->listings_count) ? ($cat->listings_count > 9 ? '9+' : $cat->listings_count) : rand(5,50) }}
                                </span>
                            </div>
                            <h3 class="text-xs font-medium text-ink-700 group-hover:text-brand-600 transition line-clamp-1">{{ $cat->name }}</h3>
                        </a>
                        @endforeach
                    </div>
                </section>

                <!-- TRENDING NOW - LARGE GRID -->
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
                                @if($product->vendor)
    <div class="flex items-center text-xs text-ink-500 mb-2">
        <i class="fas fa-user-clock mr-1"></i>
        @php
            $createdAt = $product->vendor->created_at;
            $vy = $createdAt ? floor($createdAt->diffInDays(now()) / 365) : 0;
            $vm = $createdAt ? floor(($createdAt->diffInDays(now()) % 365) / 30) : 0;
            $totalDays = $createdAt ? $createdAt->diffInDays(now()) : 0;

            // Clean logic for full words and plurals
            if ($vy >= 1) {
                $dt = $vy == 1 ? '1 year' : $vy . '+ years';
            } elseif ($vm >= 1) {
                $dt = $vm == 1 ? '1 month' : $vm . ' months';
            } else {
                $dt = $totalDays == 0 ? 'New' : $totalDays . 'd';
            }
        @endphp
        <span>{{ $dt }}</span>
        
        @if($product->vendor->user && $product->vendor->user->is_admin_verified)
            {{-- X-Style Verified Badge --}}
            <svg class="ml-1" viewBox="0 0 24 24" style="width: 14px; height: 14px;" title="Verified Seller">
                <path fill="#1d9bf0" d="M22.25 12c0-1.43-.88-2.67-2.19-3.34.46-1.39.2-2.9-.81-3.91s-2.52-1.27-3.91-.81c-.67-1.31-1.91-2.19-3.34-2.19s-2.67.88-3.33 2.19c-1.4-.46-2.9-.2-3.92.81s-1.26 2.52-.8 3.91c-1.31.67-2.2 1.91-2.2 3.34s.89 2.67 2.2 3.34c-.46 1.39-.21 2.9.8 3.91s2.52 1.26 3.91.81c.67 1.31 1.91 2.19 3.34 2.19s2.67-.88 3.34-2.19c1.39.45 2.9.2 3.91-.81s1.27-2.52.81-3.91c1.31-.67 2.19-1.91 2.19-3.34z"></path>
                <path fill="#ffffff" d="M10.5 16.5l-3.5-3.5 1.4-1.4 2.1 2.1 5.6-5.6 1.4 1.4-7 7z"></path>
            </svg>
        @endif
    </div>
@endif
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
                
                <!-- Quick Actions - ADD THIS -->
                <div class="product-actions absolute top-2 right-2 flex flex-col gap-1">
                    <button data-quick-wishlist data-listing-id="{{ $product->id }}" 
                            class="w-8 h-8 bg-white rounded-full shadow flex items-center justify-center hover:bg-coral-50 transition">
                        <i class="far fa-heart text-ink-500 hover:text-coral-500 text-sm"></i>
                    </button>
                </div>
            </div>
            
            <!-- UPDATED: Add price AND cart button -->
            <div class="p-3">
                <p class="text-xs text-ink-400 mb-1">{{ $product->category->name ?? 'General' }}</p>
                <a href="{{ route('marketplace.show', $product) }}">
                    <h3 class="text-sm font-medium text-ink-700 line-clamp-2 mb-2 hover:text-brand-600 transition h-10">
                        {{ $product->title }}
                    </h3>
                </a>
               @if($product->vendor)
    <div class="flex items-center text-xs text-ink-500 mb-2">
        <i class="fas fa-user-clock mr-1"></i>
        @php
            $createdAt = $product->vendor->created_at;
            $vy = $createdAt ? floor($createdAt->diffInDays(now()) / 365) : 0;
            $vm = $createdAt ? floor(($createdAt->diffInDays(now()) % 365) / 30) : 0;
            $totalDays = $createdAt ? $createdAt->diffInDays(now()) : 0;

            if ($vy >= 1) {
                $dt = $vy == 1 ? '1 year' : $vy . '+ years';
            } elseif ($vm >= 1) {
                $dt = $vm == 1 ? '1 month' : $vm . ' months';
            } else {
                $dt = $totalDays == 0 ? 'New' : $totalDays . 'd';
            }
        @endphp
        <span>{{ $dt }}</span>
        
        @if($product->vendor->user && $product->vendor->user->is_admin_verified)
            {{-- Perfect X-Style Scalloped Badge --}}
            <svg class="ml-1" viewBox="0 0 24 24" style="width: 14px; height: 14px;" title="Verified Seller">
                <path fill="#1d9bf0" d="M22.25 12c0-1.43-.88-2.67-2.19-3.34.46-1.39.2-2.9-.81-3.91s-2.52-1.27-3.91-.81c-.67-1.31-1.91-2.19-3.34-2.19s-2.67.88-3.33 2.19c-1.4-.46-2.9-.2-3.92.81s-1.26 2.52-.8 3.91c-1.31.67-2.2 1.91-2.2 3.34s.89 2.67 2.2 3.34c-.46 1.39-.21 2.9.8 3.91s2.52 1.26 3.91.81c.67 1.31 1.91 2.19 3.34 2.19s2.67-.88 3.34-2.19c1.39.45 2.9.2 3.91-.81s1.27-2.52.81-3.91c1.31-.67 2.19-1.91 2.19-3.34z"></path>
                <path fill="#ffffff" d="M10.5 16.5l-3.5-3.5 1.4-1.4 2.1 2.1 5.6-5.6 1.4 1.4-7 7z"></path>
            </svg>
        @endif
    </div>
@endif
 <div class="flex items-center justify-between mt-3">
                    <div class="flex items-baseline gap-1">
                        <span class="text-xs text-ink-500">UGX</span>
                        <span class="text-sm font-bold text-brand-600">{{ number_format($product->price) }}</span>
                    </div>

                    <!-- ADD TO CART BUTTON - ADD THIS -->
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
                    <!-- Advertisements Carousel -->
                    @if(isset($advertisements) && $advertisements->count() > 0)
                    <div class="bg-white rounded-xl shadow-sm border border-ink-100 overflow-hidden relative group" id="adCarousel">
                         @foreach($advertisements as $index => $ad)
                            <div class="ad-slide transition-opacity duration-500 {{ $index === 0 ? 'block' : 'hidden' }}" data-index="{{ $index }}">
                                <a href="{{ $ad->link ?? '#' }}" {{ $ad->link ? 'target="_blank"' : '' }} class="block relative aspect-[4/5]">
                                    @if($ad->media_type == 'image')
                                        <img src="{{ Storage::url($ad->media_path) }}" alt="{{ $ad->title }}" class="w-full h-full object-cover">
                                    @else
                                        <video src="{{ Storage::url($ad->media_path) }}" class="w-full h-full object-cover" autoplay muted loop playsinline></video>
                                    @endif
                                    
                                    <!-- Optional Title Overlay -->
                                    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent p-3 text-white">
                                        <p class="text-xs font-bold line-clamp-1">{{ $ad->title }}</p>
                                    </div>
                                </a>
                            </div>
                         @endforeach
                         
                         <!-- Indicators -->
                         @if($advertisements->count() > 1)
                         <div class="absolute bottom-2 left-0 right-0 flex justify-center gap-1 z-10">
                             @foreach($advertisements as $index => $ad)
                                <button class="w-1.5 h-1.5 rounded-full {{ $index === 0 ? 'bg-white' : 'bg-white/50' }} transition-colors" onclick="showAd({{ $index }})"></button>
                             @endforeach
                         </div>
                         @endif
                    </div>

                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const slides = document.querySelectorAll('.ad-slide');
                            const dots = document.querySelectorAll('#adCarousel button');
                            let currentAd = 0;
                            const totalAds = slides.length;
                            
                            if (totalAds <= 1) return;

                            function showAd(index) {
                                slides.forEach(s => s.classList.add('hidden'));
                                slides[index].classList.remove('hidden');
                                
                                if (dots.length) {
                                    dots.forEach((d, i) => {
                                        if (i === index) {
                                            d.classList.remove('bg-white/50');
                                            d.classList.add('bg-white');
                                        } else {
                                            d.classList.add('bg-white/50');
                                            d.classList.remove('bg-white');
                                        }
                                    });
                                }
                                currentAd = index;
                            }
                            
                            // Expose to window for onclick
                            window.showAd = showAd;

                            setInterval(() => {
                                let next = (currentAd + 1) % totalAds;
                                showAd(next);
                            }, 5000); // 5 seconds
                        });
                    </script>
                    @endif
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
                    <li><a href="{{ route('site.returns') }}" class="text-ink-400 hover:text-white transition">Returns & Refunds</a></li>
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

<!-- AUTH MODAL -->
<div id="authModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-ink-900/80 backdrop-blur-sm" onclick="closeAuthModal()"></div>
    <div class="bg-white rounded-2xl max-w-sm w-full p-6 relative z-10 shadow-2xl">
        <button onclick="closeAuthModal()" class="absolute top-3 right-3 w-8 h-8 bg-ink-100 rounded-full flex items-center justify-center text-ink-400 hover:text-ink-600 transition">
            <i class="fas fa-times text-sm"></i>
        </button>
        <div class="text-center mb-6">
            <div class="w-14 h-14 bg-brand-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-lock text-brand-600 text-xl"></i>
            </div>
            <h3 class="text-xl font-bold text-ink-800 mb-1 font-display">Sign In Required</h3>
            <p class="text-ink-500 text-sm">Please sign in to continue</p>
        </div>
        <div class="space-y-3">
            <a href="{{ route('login') }}" class="block w-full btn-primary py-3 rounded-lg font-bold text-center">
                <i class="fas fa-sign-in-alt mr-2"></i>Sign In
            </a>
            <a href="{{ route('register') }}" class="block w-full border-2 border-brand-600 text-brand-600 py-3 rounded-lg font-bold text-center hover:bg-brand-50 transition">
                <i class="fas fa-user-plus mr-2"></i>Create Account
            </a>
        </div>
    </div>
</div>

<!-- Options Modal for Product Variations -->
<div id="optionsModal" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeOptionsModal()"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 relative animate-scale-in">
            <button onclick="closeOptionsModal()" class="absolute top-4 right-4 w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 hover:bg-gray-200 transition">
                <i class="fas fa-times"></i>
            </button>
            
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-cogs text-primary text-2xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Select Options</h3>
                <p class="text-gray-500">Please select product options before adding to cart</p>
            </div>
            
            <!-- Options Form -->
            <div id="optionsForm" class="space-y-6">
                <!-- Color Selection -->
                <div id="colorOptionsSection" class="hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        Color <span class="text-red-500">*</span>
                    </label>
                    <div class="flex flex-wrap gap-2" id="modalColorOptions">
                        <!-- Color options will be dynamically added -->
                    </div>
                </div>
                
                <!-- Size Selection -->
                <div id="sizeOptionsSection" class="hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        Size <span class="text-red-500">*</span>
                    </label>
                    <div class="flex flex-wrap gap-2" id="modalSizeOptions">
                        <!-- Size options will be dynamically added -->
                    </div>
                </div>
                
                <!-- Selected Variant Info -->
                <div id="modalVariantInfo" class="hidden p-4 bg-blue-50 rounded-lg border border-blue-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="font-bold text-gray-800">Selected Variant</h4>
                            <div class="text-sm text-gray-600 mt-1 space-y-1">
                                <p id="modalSelectedColorText"></p>
                                <p id="modalSelectedSizeText"></p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-bold text-primary" id="modalVariantPrice"></p>
                            <p class="text-sm text-gray-600" id="modalVariantStock"></p>
                        </div>
                    </div>
                </div>
                
                <div class="pt-4 border-t border-gray-200">
                    <div class="flex gap-3">
                        <button type="button" 
                                onclick="closeOptionsModal()" 
                                class="flex-1 py-3 px-4 border-2 border-gray-300 text-gray-700 rounded-xl font-medium hover:bg-gray-50 transition">
                            Cancel
                        </button>
                        <button type="button" 
                                onclick="confirmModalOptions()"
                                id="confirmModalOptionsBtn"
                                disabled
                                class="flex-1 py-3 px-4 bg-primary text-white rounded-xl font-bold hover:bg-indigo-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                            Confirm Selection
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Product Variation Loader -->
<div id="variationLoader" class="hidden">
    <!-- This will be used to load product variations via AJAX -->
</div>

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
            <h4 class="font-bold text-ink-800 mb-2 text-sm">Categories</h4>
            @foreach($categories->take(8) as $cat)
            <a href="{{ route('marketplace.index', ['category' => $cat->id]) }}" class="block py-1.5 text-sm text-ink-500 hover:text-brand-600 transition">
                {{ $cat->name }}
            </a>
            @endforeach
        </div>
    </div>
</div>

<!-- TOAST -->
<div id="toastContainer" class="fixed top-4 right-4 z-50 space-y-2"></div>

<!-- SCRIPTS -->
<script>
const isAuthenticated = @json(auth()->check());
const csrfToken = '{{ csrf_token() }}';

// Track if buttons are already setup to prevent duplicate listeners
let buttonsSetup = false;

// Function to setup quick action buttons
function setupQuickActionButtons() {
    console.log('Setting up quick action buttons...');
    
    // Use event delegation for cart buttons
    document.addEventListener('click', async function(e) {
        // Check if clicked element or its parent has data-quick-cart
        const cartBtn = e.target.closest('[data-quick-cart]');
        if (cartBtn) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Cart button clicked via delegation');
            const listingId = cartBtn.getAttribute('data-listing-id');
            await quickAddToCart(listingId, cartBtn);
            return;
        }
        
        // Check if clicked element or its parent has data-quick-wishlist
        const wishlistBtn = e.target.closest('[data-quick-wishlist]');
        if (wishlistBtn) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Wishlist button clicked via delegation');
            const listingId = wishlistBtn.getAttribute('data-listing-id');
            await quickAddToWishlist(listingId, wishlistBtn);
            return;
        }
    });
    
    console.log('Quick action buttons setup complete (event delegation)');
}

function initCategoryNavigation() {
    const sidebarItems = document.querySelectorAll('.cat-sidebar-item');
    
    sidebarItems.forEach(item => {
        const link = item.querySelector('a');
        const submenu = item.querySelector('.cat-submenu');
        
        if (submenu) {
            let hideTimeout;
            
            // Show submenu immediately on hover
            item.addEventListener('mouseenter', function() {
                clearTimeout(hideTimeout);
                this.classList.add('hover-active');
                if (submenu) {
                    submenu.style.display = 'block';
                    submenu.style.opacity = '1';
                    submenu.style.transform = 'translateX(0)';
                    submenu.style.zIndex = '9999';
                }
            });
            
            // Hide with slight delay to prevent flickering
            item.addEventListener('mouseleave', function(e) {
                const relatedTarget = e.relatedTarget || e.toElement;
                
                // Check if moving to submenu
                if (relatedTarget && submenu.contains(relatedTarget)) {
                    return; // Don't hide if moving to submenu
                }
                
                hideTimeout = setTimeout(() => {
                    this.classList.remove('hover-active');
                    if (submenu) {
                        submenu.style.opacity = '0';
                        submenu.style.transform = 'translateX(-10px)';
                        setTimeout(() => {
                            if (!this.matches(':hover') && !submenu.matches(':hover')) {
                                submenu.style.display = 'none';
                            }
                        }, 200);
                    }
                }, 150);
            });
            
            // Keep submenu open when hovering over it
            submenu.addEventListener('mouseenter', function() {
                clearTimeout(hideTimeout);
                item.classList.add('hover-active');
                this.style.display = 'block';
                this.style.opacity = '1';
                this.style.transform = 'translateX(0)';
                this.style.zIndex = '9999';
            });
            
            submenu.addEventListener('mouseleave', function(e) {
                const relatedTarget = e.relatedTarget || e.toElement;
                
                // Check if moving back to parent item
                if (relatedTarget && item.contains(relatedTarget)) {
                    return; // Don't hide if moving back to parent
                }
                
                hideTimeout = setTimeout(() => {
                    item.classList.remove('hover-active');
                    this.style.opacity = '0';
                    this.style.transform = 'translateX(-10px)';
                    setTimeout(() => {
                        if (!item.matches(':hover') && !this.matches(':hover')) {
                            this.style.display = 'none';
                        }
                    }, 200);
                }, 150);
            });
        }
    });
}
// Toggle subcategory products
function toggleSubcategoryProducts(toggleBtn) {
    const container = toggleBtn.closest('.subcategory-with-products').querySelector('.subcategory-products-container');
    const isExpanded = container.style.display === 'block';
    
    if (isExpanded) {
        container.style.display = 'none';
        toggleBtn.classList.remove('expanded');
    } else {
        container.style.display = 'block';
        toggleBtn.classList.add('expanded');
        
        // Close other open subcategories
        const otherContainers = document.querySelectorAll('.subcategory-products-container');
        otherContainers.forEach(otherContainer => {
            if (otherContainer !== container && otherContainer.style.display === 'block') {
                otherContainer.style.display = 'none';
                const otherToggle = otherContainer.closest('.subcategory-with-products').querySelector('.subcategory-toggle');
                if (otherToggle) otherToggle.classList.remove('expanded');
            }
        });
    }
}

function setupSearchForm() {
    // Setup desktop search
    const desktopSearchForm = document.querySelector('#searchForm');
    const desktopSearchInput = desktopSearchForm?.querySelector('input[name="search"]');
    
    // Setup mobile search
    const mobileSearchForm = document.querySelector('.md\\:hidden form');
    const mobileSearchInput = mobileSearchForm?.querySelector('input[name="search"]');
    
    // Handle desktop search
    if (desktopSearchForm) {
        desktopSearchForm.addEventListener('submit', function(e) {
            const searchTerm = desktopSearchInput.value.trim();
            if (!searchTerm) {
                e.preventDefault();
                desktopSearchInput.focus();
                showToast('Please enter a search term', 'info');
            }
        });
        
        // Add keyboard shortcut for search
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                desktopSearchInput.focus();
            }
        });
        
        // Add search hint
        desktopSearchInput.addEventListener('focus', function() {
            this.setAttribute('title', 'Press Ctrl+K to focus search');
        });
    }
    
    // Handle mobile search
    if (mobileSearchForm) {
        mobileSearchForm.addEventListener('submit', function(e) {
            const searchTerm = mobileSearchInput.value.trim();
            if (!searchTerm) {
                e.preventDefault();
                mobileSearchInput.focus();
                showToast('Please enter a search term', 'info');
            }
        });
    }
}


// FIXED: Updated quickAddToCart function with debouncing
let cartProcessing = false;
async function quickAddToCart(id, btn) {
    // Prevent multiple clicks
    if (cartProcessing) {
        console.log('Cart action already in progress, skipping...');
        return;
    }
    
    cartProcessing = true;
    console.log('quickAddToCart called with ID:', id);
    
    if (!isAuthenticated) { 
        console.log('User not authenticated, showing auth modal');
        showAuthModal(); 
        cartProcessing = false;
        return; 
    }
    
    // Check if product has variations
    try {
        console.log('Checking for variations...');
        const checkResponse = await fetch(`/api/listings/${id}/check-variations`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        console.log('Check response status:', checkResponse.status);
        
        if (checkResponse.ok) {
            const checkData = await checkResponse.json();
            console.log('Variation check data:', checkData);
            
            if (checkData.has_variations && (checkData.available_colors.length > 0 || checkData.available_sizes.length > 0)) {
                console.log('Product has variations, showing modal');
                await showVariationModal(id, btn);
                cartProcessing = false;
                return;
            } else {
                console.log('Product has no variations (or empty options)');
            }
        }
    } catch (error) {
        console.error('Error checking variations:', error);
    }
    
    console.log('Adding directly to cart (no variations)');
    await addToCartSimple(id, btn);
    cartProcessing = false;
}

// Add this function after quickAddToCart function (around line 3140)
async function addToCartSimple(listingId, button) {
    console.log('Adding to cart (simple):', listingId);
    
    const originalContent = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin text-xs"></i>';
    button.disabled = true;
    
    try {
        const response = await fetch(`/buyer/cart/add/${listingId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ quantity: 1 })
        });
        
        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            throw new Error(errorData.message || `Server error: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('Cart add response:', data);
        
        if (data.success) {
            button.innerHTML = '<i class="fas fa-check text-xs"></i>';
            button.classList.remove('bg-brand-600', 'hover:bg-brand-700');
            button.classList.add('bg-green-500', 'hover:bg-green-600');
            
            if (data.cart_count) {
                updateCartCount(data.cart_count);
            }
            
            showToast('Added to cart!', 'success');
            
            setTimeout(() => {
                button.innerHTML = originalContent;
                button.classList.remove('bg-green-500', 'hover:bg-green-600');
                button.classList.add('bg-brand-600', 'hover:bg-brand-700');
                button.disabled = false;
            }, 1500);
        } else {
            throw new Error(data.message || 'Failed to add to cart');
        }
    } catch (error) {
        console.error('Cart error:', error);
        button.innerHTML = originalContent;
        button.disabled = false;
        showToast(error.message || 'Failed to add to cart', 'error');
    }
}

// Show variation modal
async function showVariationModal(listingId, button) {
    console.log('Showing variation modal for listing:', listingId);
    
    try {
       const response = await fetch(`{{ url('/api/listings') }}/${listingId}/variations`, {
    method: 'GET',
    headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrfToken
    }
});
        
        if (!response.ok) {
            throw new Error(`Failed to load variations: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('Variations data:', data);
        
        // Store the button reference for later
        window.pendingCartButton = button;
        window.pendingListingId = listingId;
        
        // Populate and show the modal
        populateVariationModal(data, listingId);
        
        const modal = document.getElementById('optionsModal');
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            console.log('Modal shown');
        }
        
    } catch (error) {
        console.error('Error loading variations:', error);
        showToast('Failed to load product options', 'error');
        await addToCartSimple(listingId, button);
    }
}

// Populate variation modal with data
function populateVariationModal(data, listingId) {
    const { variations, colors, sizes } = data;
    
    // Store data globally
    window.currentVariationData = {
        listingId: listingId,
        variations: variations,
        colors: colors,
        sizes: sizes,
        selectedColor: null,
        selectedSize: null,
        selectedVariant: null
    };
    
    // Reset modal
    document.getElementById('colorOptionsSection').classList.add('hidden');
    document.getElementById('sizeOptionsSection').classList.add('hidden');
    document.getElementById('modalVariantInfo').classList.add('hidden');
    document.getElementById('confirmModalOptionsBtn').disabled = true;
    
    // Clear options
    document.getElementById('modalColorOptions').innerHTML = '';
    document.getElementById('modalSizeOptions').innerHTML = '';
    
    // Add color options if available
    if (colors && colors.length > 0) {
        document.getElementById('colorOptionsSection').classList.remove('hidden');
        colors.forEach(color => {
            const colorBtn = document.createElement('button');
            colorBtn.type = 'button';
            colorBtn.className = 'px-4 py-2.5 border-2 border-gray-200 rounded-lg hover:border-primary transition text-gray-700';
            colorBtn.setAttribute('data-option', 'color');
            colorBtn.setAttribute('data-value', color);
            colorBtn.textContent = color;
            colorBtn.onclick = () => selectModalOption('color', color);
            document.getElementById('modalColorOptions').appendChild(colorBtn);
        });
    }
    
    // Add size options if available
    if (sizes && sizes.length > 0) {
        document.getElementById('sizeOptionsSection').classList.remove('hidden');
        sizes.forEach(size => {
            const sizeBtn = document.createElement('button');
            sizeBtn.type = 'button';
            sizeBtn.className = 'px-4 py-2.5 border-2 border-gray-200 rounded-lg hover:border-primary transition text-gray-700';
            sizeBtn.setAttribute('data-option', 'size');
            sizeBtn.setAttribute('data-value', size);
            sizeBtn.textContent = size;
            sizeBtn.onclick = () => selectModalOption('size', size);
            document.getElementById('modalSizeOptions').appendChild(sizeBtn);
        });
    }
}

// Select option in modal
function selectModalOption(type, value) {
    const data = window.currentVariationData;
    if (!data) return;
    
    // Update selected value
    data[`selected${type.charAt(0).toUpperCase() + type.slice(1)}`] = value;
    
    // Update UI - remove selected class from all buttons of this type
    document.querySelectorAll(`[data-option="${type}"]`).forEach(btn => {
        btn.classList.remove('border-primary', 'bg-primary', 'text-white');
        btn.classList.add('border-gray-200', 'text-gray-700');
    });
    
    // Add selected class to clicked button
    const clickedBtn = document.querySelector(`[data-option="${type}"][data-value="${value}"]`);
    if (clickedBtn) {
        clickedBtn.classList.remove('border-gray-200', 'text-gray-700');
        clickedBtn.classList.add('border-primary', 'bg-primary', 'text-white');
    }
    
    // Find matching variant
    findMatchingVariant();
}

// Find matching variant based on selected options
function findMatchingVariant() {
    const data = window.currentVariationData;
    if (!data) return;
    
    const { colors, sizes, selectedColor, selectedSize, variations } = data;
    
    // Check if required options are selected
    if ((colors.length > 0 && !selectedColor) || (sizes.length > 0 && !selectedSize)) {
        document.getElementById('modalVariantInfo').classList.add('hidden');
        document.getElementById('confirmModalOptionsBtn').disabled = true;
        return;
    }
    
    // Find variant that matches selected attributes
    const matchingVariant = variations.find(variant => {
        const variantAttrs = variant.attributes || {};
        
        let colorMatch = true;
        let sizeMatch = true;
        
        if (selectedColor && variantAttrs.color !== selectedColor) {
            colorMatch = false;
        }
        
        if (selectedSize && variantAttrs.size !== selectedSize) {
            sizeMatch = false;
        }
        
        return colorMatch && sizeMatch;
    });
    
    if (matchingVariant) {
        data.selectedVariant = matchingVariant;
        updateModalVariantInfo(matchingVariant);
        document.getElementById('confirmModalOptionsBtn').disabled = false;
    } else {
        document.getElementById('modalVariantInfo').classList.add('hidden');
        document.getElementById('confirmModalOptionsBtn').disabled = true;
    }
}

// Update modal variant info
function updateModalVariantInfo(variant) {
    const data = window.currentVariationData;
    if (!data) return;
    
    const modalVariantInfo = document.getElementById('modalVariantInfo');
    modalVariantInfo.classList.remove('hidden');
    
    // Update text
    const colorText = data.selectedColor ? `Color: ${data.selectedColor}` : '';
    const sizeText = data.selectedSize ? `Size: ${data.selectedSize}` : '';
    
    document.getElementById('modalSelectedColorText').textContent = colorText;
    document.getElementById('modalSelectedSizeText').textContent = sizeText;
    
    // Update price and stock
    document.getElementById('modalVariantPrice').textContent = `UGX ${variant.display_price.toLocaleString()}`;
    
    const stockText = variant.stock > 0 
        ? `${variant.stock} in stock` 
        : 'Out of stock';
    document.getElementById('modalVariantStock').textContent = stockText;
    
    // Enable/disable confirm button based on stock
    document.getElementById('confirmModalOptionsBtn').disabled = variant.stock <= 0;
    
    if (variant.stock <= 0) {
        document.getElementById('confirmModalOptionsBtn').innerHTML = 'Out of Stock';
    } else {
        document.getElementById('confirmModalOptionsBtn').innerHTML = 'Add to Cart';
    }
}

// Close options modal
function closeOptionsModal() {
    const modal = document.getElementById('optionsModal');
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
        
        // Clear stored data
        window.currentVariationData = null;
        window.pendingCartButton = null;
        window.pendingListingId = null;
    }
}

// Confirm modal options and add to cart
async function confirmModalOptions() {
    const data = window.currentVariationData;
    const button = window.pendingCartButton;
    const listingId = window.pendingListingId;
    
    if (!data || !data.selectedVariant || !button) {
        showToast('Please select all required options', 'error');
        return;
    }
    
    closeOptionsModal();
    
    const originalContent = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin text-xs"></i>';
    button.disabled = true;
    
    try {
        const response = await fetch(`/buyer/cart/add/${listingId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ 
                quantity: 1,
                variant_id: data.selectedVariant.id,
                color: data.selectedColor,
                size: data.selectedSize
            })
        });
        
        const cartData = await response.json();
        
        if (cartData.success) {
            button.innerHTML = '<i class="fas fa-check text-xs"></i>';
            button.classList.remove('bg-brand-600', 'hover:bg-brand-700');
            button.classList.add('bg-green-500', 'hover:bg-green-600');
            
            if (cartData.cart_count) {
                updateCartCount(cartData.cart_count);
            }
            
            showToast('Added to cart!', 'success');
            
            setTimeout(() => {
                button.innerHTML = originalContent;
                button.classList.remove('bg-green-500', 'hover:bg-green-600');
                button.classList.add('bg-brand-600', 'hover:bg-brand-700');
                button.disabled = false;
            }, 1500);
        } else {
            throw new Error(cartData.message || 'Failed to add to cart');
        }
    } catch (error) {
        console.error('Cart error:', error);
        button.innerHTML = originalContent;
        button.disabled = false;
        showToast(error.message || 'Failed to add to cart', 'error');
    }
}

// FIXED: Updated quickAddToWishlist function with debouncing
let wishlistProcessing = false;
async function quickAddToWishlist(id, btn) {
    // Prevent multiple clicks
    if (wishlistProcessing) {
        console.log('Wishlist action already in progress, skipping...');
        return;
    }
    
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
        // Reset processing flag after a short delay
        setTimeout(() => {
            wishlistProcessing = false;
        }, 500);
    }
}

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

// Initialize everything when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, setting up quick action buttons...');
    setupQuickActionButtons();
    initCategoryNavigation();
    setupSearchForm();
    
    // Load counts
    loadCartCount();
    loadWishlistCount();
    
    console.log('Quick action buttons setup complete');
});

// REMOVE THE DELAYED SETUP - This was causing duplicate listeners
// setTimeout(() => {
//     console.log('Delayed setup of quick action buttons...');
//     setupQuickActionButtons();
// }, 1000);

// Close modals with Escape key
document.addEventListener('keydown', e => { 
    if (e.key === 'Escape') { 
        closeAuthModal(); 
        const m = document.getElementById('mobileMenu'); 
        if (m && !m.classList.contains('hidden')) toggleMobileMenu(); 
    } 
});

// Toggle subcategory products
function toggleSubcategoryProducts(toggleBtn) {
    const container = toggleBtn.closest('.subcategory-with-products').querySelector('.subcategory-products-container');
    const isExpanded = container.style.display === 'block';
    
    if (isExpanded) {
        container.style.display = 'none';
        toggleBtn.classList.remove('expanded');
    } else {
        container.style.display = 'block';
        toggleBtn.classList.add('expanded');
        
        // Close other open subcategories
        const otherContainers = document.querySelectorAll('.subcategory-products-container');
        otherContainers.forEach(otherContainer => {
            if (otherContainer !== container && otherContainer.style.display === 'block') {
                otherContainer.style.display = 'none';
                const otherToggle = otherContainer.closest('.subcategory-with-products').querySelector('.subcategory-toggle');
                if (otherToggle) otherToggle.classList.remove('expanded');
            }
        });
    }
}

</script>

<!-- Chatbot Configuration -->
<script>
    // Set marketplace information for chatbot
    window.marketplaceName = "{{ config('app.name') }}";
    window.marketplaceDomain = "{{ parse_url(config('app.url'), PHP_URL_HOST) }}";
    window.vendorRegistrationUrl = "{{ route('vendor.onboard.create') }}";
</script>

<!-- Load enhanced chatbot -->
<script src="{{ asset('js/chatbot-enhanced.js') }}"></script>

<!-- Chatbot Styles (keep minimal styles) -->
<style>
    #chatWindow {
        animation: slideUp 0.3s ease-out;
        /* Ensure the window doesn't overflow on small screens */
        max-width: 95vw; 
        max-height: 80vh;
    }

    #chatMessages {
        scrollbar-width: thin;
        scrollbar-color: #6366f1 #f1f5f9;
    }

    #chatMessages::-webkit-scrollbar {
        width: 4px;
    }

    #chatMessages::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 2px;
    }

    #chatMessages::-webkit-scrollbar-thumb {
        background: #6366f1;
        border-radius: 2px;
    }

    .quick-question {
        transition: all 0.2s ease;
    }

    .quick-question:hover {
        transform: translateY(-1px);
    }

    /* MOBILE FIXES */
@media (max-width: 768px) {
    #chatWindow {
        position: fixed;
        bottom: 80px; /* Position above the mobile navigation bar if present */
        right: 10px;
        left: 10px;
        width: auto !important;
        height: 70vh !important;
        max-width: none;
        z-index: 9999;
        border-radius: 1rem;
    }

    /* Adjust the message area height for mobile */
    #chatMessages {
        height: calc(100% - 130px) !important; 
    }

    /* Make the common question buttons easier to tap on mobile */
    .quick-question {
        padding: 10px !important;
        font-size: 0.85rem !important;
    }
}

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
</body>
</html>