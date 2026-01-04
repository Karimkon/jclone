{{-- resources/views/marketplace/vendor-store.blade.php --}}
@extends('layouts.app')

@section('title', $vendor->business_name . ' Store - ' . config('app.name'))
@section('description', 'Browse products from ' . $vendor->business_name . ' on ' . config('app.name'))

@php
    // Get current user info
    use Illuminate\Support\Facades\Auth;
    $isAuthenticated = Auth::check();
    $userId = $isAuthenticated ? Auth::id() : null;
    
    // Get vendor's user ID for chat
    $vendorUserId = $vendor->user_id ?? null;
@endphp

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
    .vendor-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 16px;
        padding: 2rem;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
    }
    
    .vendor-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.2);
        z-index: 1;
    }
    
    .vendor-header-content {
        position: relative;
        z-index: 2;
    }
    
    .vendor-avatar {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        border: 4px solid white;
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        color: white;
        font-weight: bold;
    }
    
    .stats-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .stats-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 15px rgba(0,0,0,0.1);
    }
    
    .product-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
    }
    
    .product-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0,0,0,0.1);
        border-color: #6366f1;
    }
    
    .badge-store {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
        padding: 0.25rem 1rem;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 600;
    }
    
    .delivery-badge {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .rating-badge {
        background: rgba(251, 191, 36, 0.1);
        color: #f59e0b;
        border: 1px solid #fbbf24;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 600;
    }
    
    .filter-btn {
        padding: 0.5rem 1rem;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        background: white;
        color: #4b5563;
        font-weight: 500;
        transition: all 0.2s ease;
    }
    
    .filter-btn:hover, .filter-btn.active {
        border-color: #6366f1;
        background: #6366f1;
        color: white;
    }
    
    .vendor-description {
        line-height: 1.8;
        color: #4b5563;
    }
    
    /* Product card styles matching index */
    .product-image {
        height: 200px;
        width: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }
    
    .product-card:hover .product-image {
        transform: scale(1.05);
    }
    
    .quick-add {
        opacity: 1 !important;
        visibility: visible !important;
    }
    
    .active-filter {
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        color: white;
    }
    
    /* Toast notification */
    .toast {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1000;
        padding: 12px 24px;
        border-radius: 8px;
        color: white;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        animation: slideIn 0.3s ease;
    }
    
    .toast-success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }
    
    .toast-error {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    }
    
    .toast-info {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    }
    
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }

    /* Modal Styles */
    .animate-scale-in {
        animation: scale-in 0.3s ease forwards;
    }
    
    @keyframes scale-in {
        from { transform: scale(0.9); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Vendor Header -->
    <div class="vendor-header">
        <div class="container mx-auto px-4">
            <div class="vendor-header-content flex flex-col md:flex-row items-center md:items-start gap-6">
                <!-- Vendor Avatar -->
                <div class="flex-shrink-0">
                    @if($vendor->logo)
                    <img src="{{ $vendor->logo }}" alt="{{ $vendor->business_name }}" class="vendor-avatar object-cover">
                    @else
                    <div class="vendor-avatar">
                        {{ strtoupper(substr($vendor->business_name, 0, 2)) }}
                    </div>
                    @endif
                </div>
                
                <!-- Vendor Info -->
                <div class="flex-1 text-center md:text-left">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
                        <div>
                            <h1 class="text-3xl font-bold mb-2">{{ $vendor->business_name }}</h1>
                            <div class="flex items-center gap-3 flex-wrap justify-center md:justify-start">
                                <div class="flex items-center gap-1">
                                    <div class="star-rating">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star text-sm {{ $i <= round($vendorStats['rating']) ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                                        @endfor
                                    </div>
                                    <span class="text-white font-medium">{{ number_format($vendorStats['rating'], 1) }}</span>
                                    <span class="text-white/80">({{ $vendorStats['reviews'] }} reviews)</span>
                                </div>
                                <span class="badge-store">
                                    <i class="fas fa-check-circle mr-1"></i>Verified Vendor
                                </span>
                                @if($vendor->vendor_type == 'china_supplier')
                                <span class="bg-blue-500 text-white px-3 py-1 rounded-full text-sm">
                                    <i class="fas fa-globe mr-1"></i>International Supplier
                                </span>
                                @else
                                <span class="bg-green-500 text-white px-3 py-1 rounded-full text-sm">
                                    <i class="fas fa-map-marker-alt mr-1"></i>Local Vendor
                                </span>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="flex gap-2">
                            @if(auth()->check())
                            <button onclick="openChatModal()" 
                                    class="px-4 py-2 bg-white text-indigo-600 rounded-lg font-medium hover:bg-indigo-50 transition flex items-center gap-2">
                                <i class="fas fa-comment-dots"></i>
                                Chat with Vendor
                            </button>
                            @else
                            <button onclick="showAuthModal()" 
                                    class="px-4 py-2 bg-white text-indigo-600 rounded-lg font-medium hover:bg-indigo-50 transition flex items-center gap-2">
                                <i class="fas fa-comment-dots"></i>
                                Chat with Vendor
                            </button>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Vendor Description -->
                    @if($vendor->business_description)
                    <p class="text-white/90 mb-4 max-w-3xl">{{ $vendor->business_description }}</p>
                    @endif
                    
                    <!-- Store Stats -->
                    <div class="flex flex-wrap gap-4">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-box text-white/80"></i>
                            <span class="text-white">{{ $vendorStats['total_products'] }} Products</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="fas fa-shopping-cart text-white/80"></i>
                            <span class="text-white">{{ $vendorStats['total_sales'] }} Sales</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="fas fa-calendar-alt text-white/80"></i>
                            <span class="text-white">Member since {{ $vendorStats['joined_date'] }}</span>
                        </div>
                        @if($vendor->business_phone)
                        <div class="flex items-center gap-2">
                            <i class="fas fa-phone text-white/80"></i>
                            <span class="text-white">{{ $vendor->business_phone }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8">
        <div class="grid lg:grid-cols-4 gap-8">
            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <!-- Vendor Stats -->
                <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                    <h3 class="font-bold text-gray-900 mb-4">Store Statistics</h3>
                    <div class="space-y-4">
                        <!-- Positive Rating -->
                        <div class="stats-card">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-500">Positive Rating</p>
                                    <p class="text-2xl font-bold text-green-600">{{ $vendorStats['positive'] }}%</p>
                                </div>
                                <i class="fas fa-thumbs-up text-green-500 text-2xl"></i>
                            </div>
                        </div>
                        
                        <!-- Delivery Performance -->
                        @if($deliveryStats['delivered_orders'] >= 5)
                        <div class="stats-card">
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <h4 class="font-semibold text-gray-900">Delivery Performance</h4>
                                    <span class="rating-badge">
                                        <i class="fas fa-star mr-1"></i>
                                        {{ number_format($deliveryStats['rating'], 1) }}
                                    </span>
                                </div>
                                
                                <div class="space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Score</span>
                                        <span class="font-semibold {{ $deliveryStats['score'] >= 80 ? 'text-green-600' : ($deliveryStats['score'] >= 60 ? 'text-yellow-600' : 'text-red-600') }}">
                                            {{ $deliveryStats['score'] }}/100
                                        </span>
                                    </div>
                                    @if($deliveryStats['avg_time'] > 0)
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Avg. Delivery</span>
                                        <span class="font-semibold">{{ $deliveryStats['avg_time'] }} days</span>
                                    </div>
                                    @endif
                                    @if($deliveryStats['on_time_rate'] > 0)
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">On-Time Rate</span>
                                        <span class="font-semibold">{{ $deliveryStats['on_time_rate'] }}%</span>
                                    </div>
                                    @endif
                                    @if($deliveryStats['delivered_orders'] > 0)
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Orders Delivered</span>
                                        <span class="font-semibold">{{ $deliveryStats['delivered_orders'] }}</span>
                                    </div>
                                    @endif
                                </div>
                                
                                @if($deliveryStats['score'] >= 90)
                                <div class="delivery-badge">
                                    <i class="fas fa-bolt"></i>
                                    Excellent Delivery
                                </div>
                                @elseif($deliveryStats['score'] >= 80)
                                <div class="bg-blue-500 text-white px-3 py-1 rounded text-sm font-medium">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    Reliable Delivery
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif
                        
                        <!-- Business Info -->
                        <div class="stats-card">
                            <h4 class="font-semibold text-gray-900 mb-3">Business Information</h4>
                            <div class="space-y-2">
                                @if($vendor->country)
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Location</span>
                                    <span class="font-medium">{{ $vendor->city ?? '' }}, {{ $vendor->country }}</span>
                                </div>
                                @endif
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Status</span>
                                    <span class="font-medium text-green-600">Active</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Verified</span>
                                    <span class="font-medium text-green-600">
                                        <i class="fas fa-check-circle mr-1"></i>Yes
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Store Location Map -->
                        @if($vendor->latitude && $vendor->longitude)
                        <div class="stats-card">
                            <h4 class="font-semibold text-gray-900 mb-3">Store Location</h4>
                            <div id="storeMap" style="height: 200px; z-index: 1;" class="w-full rounded-lg border border-gray-200"></div>
                            <div class="mt-2 text-xs text-gray-500 flex justify-between">
                                <span>{{ $vendor->city ?? 'Unknown City' }}, {{ $vendor->country }}</span>
                                <a href="https://www.google.com/maps/search/?api=1&query={{ $vendor->latitude }},{{ $vendor->longitude }}" 
                                   target="_blank" 
                                   class="text-primary hover:underline">
                                    Get Directions
                                </a>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Products -->
            <div class="lg:col-span-3">
                <!-- Products Header -->
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Products</h2>
                        <p class="text-gray-600">{{ $listings->total() }} products available</p>
                    </div>
                </div>
                
                <!-- Products Grid -->
                @if($listings->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($listings as $product)
                    <div class="product-card bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
                        <!-- Product Image -->
                        <div class="relative overflow-hidden">
                            <a href="{{ route('marketplace.show', $product) }}" class="block">
                                @if($product->images->first())
                                    <img src="{{ asset('storage/' . $product->images->first()->path) }}" 
                                         alt="{{ $product->title }}" 
                                         class="w-full h-48 object-cover product-image">
                                @else
                                    <div class="w-full h-48 bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                                        <i class="fas fa-image text-gray-300 text-4xl"></i>
                                    </div>
                                @endif
                            </a>
                            
                            <!-- Badges -->
                            <div class="absolute top-3 left-3">
                                @if($product->origin == 'imported')
                                    <span class="inline-flex items-center px-2 py-1 bg-blue-500 text-white text-xs font-bold rounded-full">
                                        <i class="fas fa-plane mr-1"></i> Imported
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 bg-green-500 text-white text-xs font-bold rounded-full">
                                        <i class="fas fa-home mr-1"></i> Local
                                    </span>
                                @endif
                            </div>
                            
                            <!-- Quick Actions -->
                            <div class="absolute top-3 right-3 flex flex-col gap-2">
                                <button onclick="quickAddToWishlist({{ $product->id }}, this)" 
                                        data-listing-id="{{ $product->id }}"
                                        class="w-8 h-8 bg-white rounded-full shadow flex items-center justify-center hover:bg-red-50 transition quick-add">
                                    <i class="far fa-heart text-gray-600 hover:text-red-500"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Product Info -->
                        <div class="p-5">
                            <!-- Category -->
                            <div class="mb-2">
                                <span class="text-xs text-gray-500 font-medium">
                                    {{ $product->category->name ?? 'General' }}
                                </span>
                            </div>
                            
                            <!-- Title -->
                            <a href="{{ route('marketplace.show', $product) }}">
                                <h3 class="font-semibold text-gray-800 mb-2 line-clamp-2 hover:text-primary transition">
                                    {{ $product->title }}
                                </h3>
                            </a>
                            
                            <!-- Rating -->
                            @php
                                $productRating = \App\Models\Review::getAverageRating($product->id);
                                $reviewCount = \App\Models\Review::getReviewsCount($product->id);
                            @endphp
                            @if($reviewCount > 0)
                            <div class="flex items-center gap-1 mb-4">
                                <div class="star-rating">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star text-xs {{ $i <= round($productRating) ? 'text-yellow-400' : 'text-gray-200' }}"></i>
                                    @endfor
                                </div>
                                <span class="text-xs text-gray-500">({{ $reviewCount }})</span>
                            </div>
                            @endif
                            
                            <!-- Price and Actions -->
                            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                <div>
                                   <div class="text-xl font-bold text-primary">
                                        UGX {{ number_format($product->price, 0) }}
                                    </div>
                                    @if($product->weight_kg)
                                        <div class="text-xs text-gray-500 mt-1">
                                            <i class="fas fa-weight-hanging mr-1"></i>{{ $product->weight_kg }}kg
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="flex items-center gap-2">
                                    @if($product->stock > 0)
                                        <button onclick="quickAddToCart({{ $product->id }}, this)" 
                                                data-listing-id="{{ $product->id }}"
                                                class="w-10 h-10 bg-primary text-white rounded-lg flex items-center justify-center hover:bg-indigo-700 transition quick-add">
                                            <i class="fas fa-shopping-cart"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Stock Status -->
                            <div class="mt-4 pt-4 border-t border-gray-100">
                                @if($product->stock > 10)
                                    <div class="text-sm text-green-600 flex items-center">
                                        <i class="fas fa-check-circle mr-2"></i>
                                        In Stock ({{ $product->stock }} available)
                                    </div>
                                @elseif($product->stock > 0)
                                    <div class="text-sm text-yellow-600 flex items-center">
                                        <i class="fas fa-exclamation-triangle mr-2"></i>
                                        Only {{ $product->stock }} left in stock
                                    </div>
                                @else
                                    <div class="text-sm text-red-600 flex items-center">
                                        <i class="fas fa-times-circle mr-2"></i>
                                        Out of Stock
                                    </div>
                                @endif
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
                <div class="bg-white rounded-xl shadow-sm p-12 text-center">
                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-box-open text-gray-300 text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">No Products Available</h3>
                    <p class="text-gray-500 mb-6">This vendor hasn't listed any products yet.</p>
                    
                    @if(auth()->check())
                    <button onclick="openChatModal()" 
                            class="px-6 py-3 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition inline-flex items-center gap-2">
                        <i class="fas fa-comment-dots"></i>
                        Chat with Vendor
                    </button>
                    @else
                    <button onclick="showAuthModal()" 
                            class="px-6 py-3 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition inline-flex items-center gap-2">
                        <i class="fas fa-comment-dots"></i>
                        Chat with Vendor
                    </button>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Auth Modal -->
<div id="authModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeAuthModal()"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-8 relative shadow-2xl transform transition-all duration-300" id="authModalContent">
            <button onclick="closeAuthModal()" class="absolute top-4 right-4 w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 hover:bg-gray-200 transition">
                <i class="fas fa-times"></i>
            </button>
            
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-lock text-primary text-2xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Sign In Required</h3>
                <p class="text-gray-500">Please login or create an account to continue with this action.</p>
            </div>
            
            <div class="space-y-3">
                <a href="{{ route('login') }}?redirect={{ urlencode(url()->current()) }}" 
                   class="block w-full py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-bold text-center hover:from-indigo-700 hover:to-purple-700 transition shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                    <i class="fas fa-sign-in-alt mr-2"></i>Login
                </a>
                <a href="{{ route('register') }}?redirect={{ urlencode(url()->current()) }}" 
                   class="block w-full py-3 border-2 border-indigo-600 text-indigo-600 rounded-xl font-bold text-center hover:bg-indigo-600 hover:text-white transition">
                    <i class="fas fa-user-plus mr-2"></i>Create Account
                </a>
            </div>
        </div>
    </div>
</div>



<!-- Chat Modal -->
<div id="chatModal" class="fixed inset-0 z-[110] hidden">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeChatModal()"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl w-full max-w-4xl h-[80vh] flex flex-col relative animate-scale-in">
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200">
                <div class="flex items-center gap-3">
                    @if($vendor->logo)
                    <img src="{{ $vendor->logo }}" alt="{{ $vendor->business_name }}" class="w-12 h-12 rounded-full object-cover">
                    @else
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-primary to-purple-600 flex items-center justify-center text-white font-bold">
                        {{ strtoupper(substr($vendor->business_name, 0, 2)) }}
                    </div>
                    @endif
                    <div>
                        <h3 class="font-bold text-gray-900">Chat with {{ $vendor->business_name }}</h3>
                        <p class="text-sm text-gray-500">Typically responds within minutes</p>
                    </div>
                </div>
                <button onclick="closeChatModal()" class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 hover:bg-gray-200 transition">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <!-- Messages Container -->
            <div id="chatMessages" class="flex-1 overflow-y-auto p-6 space-y-4">
                <!-- Messages will be loaded here -->
                <div id="noMessages" class="text-center py-12">
                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-comment-dots text-gray-300 text-3xl"></i>
                    </div>
                    <h4 class="text-lg font-semibold text-gray-700 mb-2">No messages yet</h4>
                    <p class="text-gray-500">Start a conversation with {{ $vendor->business_name }}</p>
                </div>
            </div>
            
            <!-- Message Input -->
            <div class="p-6 border-t border-gray-200">
                <div class="flex gap-3">
                    <textarea id="chatMessageInput" 
                              placeholder="Type your message here..." 
                              rows="2"
                              class="flex-1 px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent resize-none"></textarea>
                    <button onclick="sendChatMessage()" 
                            id="sendChatButton"
                            class="self-end px-6 py-3 bg-primary text-white rounded-xl font-medium hover:bg-indigo-700 transition flex items-center gap-2">
                        <i class="fas fa-paper-plane"></i>
                        Send
                    </button>
                </div>
                <p class="text-xs text-gray-500 mt-2">
                    <i class="fas fa-shield-alt mr-1"></i> Your messages are private and secure
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const csrfToken = '{{ csrf_token() }}';
    const isAuthenticated = @json($isAuthenticated);
    const vendorId = {{ $vendor->id }};
    const vendorUserId = @json($vendorUserId);
    let chatConversationId = null;
    let chatPollingInterval = null;

    // Price range slider
    const priceSlider = document.getElementById('priceSlider');
    const priceValue = document.getElementById('priceValue');
    const maxPriceInput = document.querySelector('input[name="max_price"]');
    
    if (priceSlider && priceValue) {
        priceSlider.addEventListener('input', function() {
            const value = this.value;
            priceValue.textContent = 'UGX ' + value.toLocaleString();
            if (maxPriceInput) {
                maxPriceInput.value = value;
            }
        });
    }
    
    let cartProcessing = false;
    
    async function quickAddToCart(listingId, button) {
        // Prevent multiple clicks
        if (cartProcessing) {
            console.log('Cart action already in progress, skipping...');
            return;
        }
        
        cartProcessing = true;
        console.log('=== QuickAddToCart called for listing:', listingId);
        
        if (!isAuthenticated) { 
            console.log('User not authenticated, showing auth modal');
            showAuthModal(); 
            cartProcessing = false;
            return; 
        }
        
        // Check if product has variations
        try {
            console.log('Checking for variations...');
            const checkResponse = await fetch(`/api/listings/${listingId}/check-variations`, {
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
                
                if (checkData.has_variations && (checkData.available_colors?.length > 0 || checkData.available_sizes?.length > 0)) {
                    cartProcessing = false;
                    showVariationModal(listingId, button);
                    return;
                } else {
                    console.log('Product has no variations (or empty options)');
                }
            }
        } catch (error) {
            console.error('Error checking variations:', error);
        }
        
        console.log('Adding directly to cart (no variations)');
        await addToCartSimple(listingId, button);
        cartProcessing = false;
    }

    // Add this helper function
    async function addToCartSimple(listingId, button) {
        console.log('Adding to cart (simple):', listingId);
        
        const originalContent = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
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
                button.innerHTML = '<i class="fas fa-check"></i>';
                button.classList.remove('bg-primary', 'hover:bg-indigo-700');
                button.classList.add('bg-green-500', 'hover:bg-green-600');
                
                if (data.cart_count) {
                    updateCartCount(data.cart_count);
                }
                
                showToast('Added to cart!', 'success');
                
                setTimeout(() => {
                    button.innerHTML = originalContent;
                    button.classList.remove('bg-green-500', 'hover:bg-green-600');
                    button.classList.add('bg-primary', 'hover:bg-indigo-700');
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
    
    async function quickAddToWishlist(listingId, button) {
        console.log('=== QuickAddToWishlist called for listing:', listingId);
        
        if (!isAuthenticated) {
            showAuthModal();
            return;
        }
        
        const icon = button.querySelector('i');
        const originalIconClass = icon.className;
        icon.className = 'fas fa-spinner fa-spin';
        
        try {
            const response = await fetch(`/buyer/wishlist/toggle/${listingId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });
            
            if (!response.ok) {
                throw new Error(`Server responded with status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                if (data.in_wishlist) {
                    icon.className = 'fas fa-heart text-red-500';
                } else {
                    icon.className = 'far fa-heart text-gray-600';
                }
                showToast(data.message || 'Wishlist updated!', 'success');
            } else {
                throw new Error(data.message || 'Failed to update wishlist');
            }
        } catch (error) {
            console.error('Wishlist error:', error);
            icon.className = originalIconClass;
            showToast(error.message || 'Failed to update wishlist', 'error');
        }
    }
    
    // Update cart count
    function updateCartCount(count) {
        const cartCountElements = document.querySelectorAll('.cart-count');
        cartCountElements.forEach(element => {
            element.textContent = count;
            element.classList.remove('hidden');
            element.classList.add('animate-pulse');
            setTimeout(() => element.classList.remove('animate-pulse'), 1000);
        });
    }
    
    // Show toast
    function showToast(message, type = 'info') {
        // Remove existing toasts
        const existingToasts = document.querySelectorAll('.toast');
        existingToasts.forEach(toast => toast.remove());
        
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        
        let icon = '';
        if (type === 'success') {
            icon = '<i class="fas fa-check-circle"></i>';
        } else if (type === 'error') {
            icon = '<i class="fas fa-times-circle"></i>';
        } else {
            icon = '<i class="fas fa-info-circle"></i>';
        }
        
        toast.innerHTML = `${icon} ${message}`;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.3s';
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, 3000);
    }
    
    function showAuthModal() {
        const modal = document.getElementById('authModal');
        const content = document.getElementById('authModalContent');

        if (!modal || !content) return;

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        requestAnimationFrame(() => {
            content.classList.remove('opacity-0', 'scale-95');
            content.classList.add('opacity-100', 'scale-100');
        });

        document.body.style.overflow = 'hidden';
    }

    function closeAuthModal() {
        const modal = document.getElementById('authModal');
        const content = document.getElementById('authModalContent');

        if (!modal || !content) return;

        content.classList.add('opacity-0', 'scale-95');
        content.classList.remove('opacity-100', 'scale-100');

        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = 'auto';
        }, 200);
    }
    
    async function openChatModal() {
        if (!isAuthenticated) {
            showAuthModal();
            return;
        }

        const modal = document.getElementById('chatModal');
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        // If we have a conversation ID, load messages
        if (chatConversationId) {
            loadMessages();
            startChatPolling();
        } else {
            // Check one more time if conversation exists (in case it was created elsewhere or just now loaded)
            await checkExistingConversation();
            if (chatConversationId) {
                loadMessages();
                startChatPolling();
            } else {
                // Prepare empty state
                const messagesContainer = document.getElementById('chatMessages');
                const noMessages = document.getElementById('noMessages');
                messagesContainer.innerHTML = '';
                if (noMessages) noMessages.classList.remove('hidden');
                messagesContainer.appendChild(noMessages);
            }
        }
    }

    function closeChatModal() {
        const modal = document.getElementById('chatModal');
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
        
        // Stop polling
        stopChatPolling();
    }



    async function loadMessages() {
        if (!chatConversationId) return;

        try {
            const response = await fetch(`/chat/${chatConversationId}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) throw new Error('Failed to load messages');
            
            const data = await response.json();
            displayMessages(data.messages || []);
        } catch (error) {
            console.error('Error loading messages:', error);
        }
    }

    async function sendChatMessage() {
        if (!isAuthenticated) return;

        const input = document.getElementById('chatMessageInput');
        const message = input.value.trim();
        if (!message) return;

        const sendButton = document.getElementById('sendChatButton');
        const originalText = sendButton.innerHTML;
        sendButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        sendButton.disabled = true;

        try {
            let response;
            
            // If we have a conversation ID, send to that conversation
            if (chatConversationId) {
                response = await fetch(`/chat/${chatConversationId}/send`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ body: message })
                });
            } else {
                // Start NEW conversation
                response = await fetch('/chat/start', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        vendor_profile_id: vendorId, // Correct parameter name
                        message: message,
                        listing_id: null
                    })
                });
            }

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Failed to send message');
            }
            
            const data = await response.json();
            if (data.success) {
                input.value = '';
                
                // If we just started a conversation, save the ID
                if (!chatConversationId && data.conversation_id) {
                    chatConversationId = data.conversation_id;
                    startChatPolling(); // Start polling now that we have an ID
                }

                // Add message to display
                // Controller returns 'message' object either way?
                // sendMessage returns: { success: true, message: {...} }
                // startConversation returns: { success: true, conversation_id: ..., message: 'Message sent successfully!' } -> Wait, it doesn't return the message object!
                
                // We need to handle the response format difference
                if (data.message && typeof data.message === 'object') {
                     // createMessage response
                    addMessageToDisplay(data.message);
                } else {
                    // startConversation response - we might need to manually construct the message object or fetch messages
                    // Since startConversation creates the verified message in DB, we can just loadMessages() or construct it temporarily
                    // But simpler to just refresh messages
                    loadMessages();
                }

                // Scroll to bottom
                const messagesContainer = document.getElementById('chatMessages');
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }
        } catch (error) {
            console.error('Error sending message:', error);
            showToast(error.message || 'Failed to send message', 'error');
        } finally {
            sendButton.innerHTML = originalText;
            sendButton.disabled = false;
        }
    }

    function displayMessages(messages) {
        const messagesContainer = document.getElementById('chatMessages');
        const noMessages = document.getElementById('noMessages');
        
        if (messages.length === 0) {
            noMessages.classList.remove('hidden');
            messagesContainer.innerHTML = '';
            messagesContainer.appendChild(noMessages);
            return;
        }
        
        noMessages.classList.add('hidden');
        messagesContainer.innerHTML = '';
        
        messages.forEach(message => {
            addMessageToDisplay(message);
        });
        
        // Scroll to bottom
        setTimeout(() => {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }, 100);
    }

    function addMessageToDisplay(message) {
        const messagesContainer = document.getElementById('chatMessages');
        const noMessages = document.getElementById('noMessages');
        
        if (noMessages && !noMessages.classList.contains('hidden')) {
            noMessages.classList.add('hidden');
        }
        
        const isCurrentUser = message.sender_id === {{ auth()->id() ?? 0 }};
        const messageDiv = document.createElement('div');
        messageDiv.className = `flex ${isCurrentUser ? 'justify-end' : 'justify-start'}`;
        
        const messageDate = new Date(message.created_at);
        const timeString = messageDate.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
        
        messageDiv.innerHTML = `
            <div class="max-w-[70%] ${isCurrentUser ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-800'} rounded-2xl px-4 py-3">
                <p class="text-sm">${message.body}</p>
                <div class="text-xs mt-1 ${isCurrentUser ? 'text-indigo-200' : 'text-gray-500'}">
                    ${timeString}
                </div>
            </div>
        `;
        
        messagesContainer.appendChild(messageDiv);
    }

    function startChatPolling() {
        // Poll for new messages every 3 seconds
        chatPollingInterval = setInterval(async () => {
            if (chatConversationId) {
                try {
                    const response = await fetch(`/chat/${chatConversationId}/new-messages`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (response.ok) {
                        const data = await response.json();
                        if (data.messages && data.messages.length > 0) {
                            // Add new messages
                            data.messages.forEach(message => addMessageToDisplay(message));
                            // Scroll to bottom
                            const messagesContainer = document.getElementById('chatMessages');
                            messagesContainer.scrollTop = messagesContainer.scrollHeight;
                        }
                    }
                } catch (error) {
                    console.error('Error polling for messages:', error);
                }
            }
        }, 3000);
    }

    function stopChatPolling() {
        if (chatPollingInterval) {
            clearInterval(chatPollingInterval);
            chatPollingInterval = null;
        }
    }



    // Event listeners
    document.addEventListener('DOMContentLoaded', function() {
        // Quick cart buttons
        document.querySelectorAll('[onclick*="quickAddToCart"]').forEach(btn => {
            const listingId = btn.getAttribute('data-listing-id');
            btn.setAttribute('onclick', `quickAddToCart(${listingId}, this)`);
        });
        
        // Quick wishlist buttons
        document.querySelectorAll('[onclick*="quickAddToWishlist"]').forEach(btn => {
            const listingId = btn.getAttribute('data-listing-id');
            btn.setAttribute('onclick', `quickAddToWishlist(${listingId}, this)`);
        });
        
        // Close modals on background click
        const modals = ['authModal', 'chatModal'];
        modals.forEach(modalId => {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        if (modalId === 'authModal') closeAuthModal();
                        else if (modalId === 'chatModal') closeChatModal();
                    }
                });
            }
        });
        
        // Close modals on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeAuthModal();
                closeChatModal();
            }
        });

        // Chat message input - allow Enter to send (with Shift+Enter for new line)
        const chatInput = document.getElementById('chatMessageInput');
        if (chatInput) {
            chatInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendChatMessage();
                }
            });
        }
        
        // Initialize any existing conversation if user is authenticated
        if (isAuthenticated) {
            // Check if there's an existing conversation with this vendor
            checkExistingConversation();
        }
    });

    // Check for existing conversation
    async function checkExistingConversation() {
        try {
            const response = await fetch('/chat', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                const data = await response.json();
                if (data.conversations && Array.isArray(data.conversations)) {
                    // Look for conversation with this vendor
                    const existingConv = data.conversations.find(conv => 
                        conv.vendor_id === vendorId || 
                        (conv.participants && conv.participants.some(p => p.id === vendorUserId))
                    );
                    if (existingConv) {
                        chatConversationId = existingConv.id;
                    }
                }
            }
        } catch (error) {
            console.error('Error checking existing conversations:', error);
        }
    }

</script>

    @if($vendor->latitude && $vendor->longitude)
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Pass data safely using json_encode to avoid syntax errors
        const mapData = @json([
            'lat' => (float) $vendor->latitude,
            'lng' => (float) $vendor->longitude,
            'name' => $vendor->business_name,
            'address' => $vendor->address
        ]);

        console.log('Initializing vendor store map with data:', mapData);
        
        if (typeof L === 'undefined') {
            console.error('Leaflet library (L) is not loaded.');
            return;
        }

        if (!document.getElementById('storeMap')) {
            console.error('Map container element "storeMap" not found.');
            return;
        }
        
        try {
            const map = L.map('storeMap', {
                scrollWheelZoom: false
            }).setView([mapData.lat, mapData.lng], 14);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            const popupContent = `<b>${mapData.name}</b><br>${mapData.address}`;
            
            L.marker([mapData.lat, mapData.lng]).addTo(map)
                .bindPopup(popupContent)
                .openPopup();
                
            setTimeout(function() {
                map.invalidateSize();
            }, 500);
            
        } catch (error) {
            console.error('Error creating Leaflet map:', error);
        }
    });
    </script>
    @endif
@endpush