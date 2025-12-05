@extends('layouts.app')

@section('title', $listing->title . ' - ' . config('app.name'))
@section('description', $listing->description)

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Product Header -->
    <div class="bg-white border-b">
        <div class="container mx-auto px-4 py-4">
            <!-- Breadcrumbs -->
            <div class="flex items-center text-sm text-gray-600">
                <a href="{{ route('welcome') }}" class="hover:text-primary">Home</a>
                <i class="fas fa-chevron-right mx-2 text-xs"></i>
                <a href="{{ route('marketplace.index') }}" class="hover:text-primary">Marketplace</a>
                @if($listing->category)
                <i class="fas fa-chevron-right mx-2 text-xs"></i>
                <a href="{{ route('categories.show', $listing->category) }}" class="hover:text-primary">{{ $listing->category->name }}</a>
                @endif
                <i class="fas fa-chevron-right mx-2 text-xs"></i>
                <span class="font-semibold text-gray-800">{{ $listing->title }}</span>
            </div>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8">
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Left Column - Images & Details -->
            <div class="lg:w-2/3">
                <!-- Product Images -->
                <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                    <div class="flex flex-col md:flex-row gap-6">
                        <!-- Main Image -->
                        <div class="md:w-2/3">
                            <div class="relative">
                                @if($listing->images->first())
                                <img id="mainImage" 
                                     src="{{ asset('storage/' . $listing->images->first()->path) }}" 
                                     alt="{{ $listing->title }}" 
                                     class="w-full h-96 object-contain rounded-lg bg-gray-100">
                                @else
                                <div class="w-full h-96 bg-gray-200 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-image text-gray-400 text-6xl"></i>
                                </div>
                                @endif
                                
                                <!-- Badges -->
                                <div class="absolute top-4 left-4 flex flex-col gap-2">
                                    @if($listing->origin == 'imported')
                                    <span class="px-3 py-1 bg-blue-500 text-white text-sm font-bold rounded-full">
                                        <i class="fas fa-plane mr-1"></i> Imported
                                    </span>
                                    @else
                                    <span class="px-3 py-1 bg-green-500 text-white text-sm font-bold rounded-full">
                                        <i class="fas fa-home mr-1"></i> Local
                                    </span>
                                    @endif
                                    
                                    @if($listing->condition == 'new')
                                    <span class="px-3 py-1 bg-purple-500 text-white text-sm font-bold rounded-full">
                                        <i class="fas fa-certificate mr-1"></i> New
                                    </span>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Thumbnail Images -->
                            @if($listing->images->count() > 1)
                            <div class="mt-4 flex space-x-2 overflow-x-auto pb-2">
                                @foreach($listing->images as $image)
                                <button onclick="changeMainImage('{{ asset('storage/' . $image->path) }}')"
                                        class="flex-shrink-0 w-20 h-20 border-2 border-transparent hover:border-primary rounded-lg overflow-hidden">
                                    <img src="{{ asset('storage/' . $image->path) }}" 
                                         alt="{{ $listing->title }}" 
                                         class="w-full h-full object-cover">
                                </button>
                                @endforeach
                            </div>
                            @endif
                        </div>
                        
                        <!-- Product Actions -->
                        <div class="md:w-1/3">
                            <!-- Price & Stock -->
                            <div class="mb-6">
                                <div class="text-3xl font-bold text-primary mb-2">
                                    ${{ number_format($listing->price, 2) }}
                                </div>
                                
                                <!-- Stock Status -->
                                @if($listing->stock > 10)
                                <div class="text-sm text-green-600 mb-4">
                                    <i class="fas fa-check-circle mr-1"></i> In Stock ({{ $listing->stock }} available)
                                </div>
                                @elseif($listing->stock > 0)
                                <div class="text-sm text-orange-600 mb-4">
                                    <i class="fas fa-exclamation-triangle mr-1"></i> Only {{ $listing->stock }} left
                                </div>
                                @else
                                <div class="text-sm text-red-600 mb-4">
                                    <i class="fas fa-times-circle mr-1"></i> Out of Stock
                                </div>
                                @endif
                                
                                <!-- Weight -->
                                @if($listing->weight_kg)
                                <div class="text-sm text-gray-600 mb-2">
                                    <i class="fas fa-weight-hanging mr-1"></i> Weight: {{ $listing->weight_kg }}kg
                                </div>
                                @endif
                                
                                <!-- SKU -->
                                @if($listing->sku)
                                <div class="text-sm text-gray-600">
                                    <i class="fas fa-barcode mr-1"></i> SKU: {{ $listing->sku }}
                                </div>
                                @endif
                            </div>
                            
                            <!-- Add to Cart -->
                            @if($listing->stock > 0)
                            <div class="space-y-4">
                                <!-- Quantity -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                                    <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden">
                                        <button onclick="updateQuantity(-1)" 
                                                class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <input type="number" id="quantity" 
                                               value="1" min="1" max="{{ $listing->stock }}" 
                                               class="w-16 text-center border-0 focus:ring-0">
                                        <button onclick="updateQuantity(1)" 
                                                class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Action Buttons -->
                                <div class="space-y-3">
                                    <button onclick="addToCart()"
                                            class="w-full px-6 py-3 bg-primary text-white rounded-lg hover:bg-indigo-700 font-bold flex items-center justify-center">
                                        <i class="fas fa-shopping-cart mr-2"></i> Add to Cart
                                    </button>
                                    
                                    <button onclick="addToWishlist()"
                                            class="w-full px-6 py-3 border-2 border-primary text-primary rounded-lg hover:bg-primary hover:text-white font-bold flex items-center justify-center">
                                        <i class="fas fa-heart mr-2"></i> Add to Wishlist
                                    </button>
                                    
                                    @auth
                                        @if(auth()->user()->role === 'buyer')
                                        <button onclick="buyNow()"
                                                class="w-full px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-lg hover:opacity-90 font-bold flex items-center justify-center">
                                            <i class="fas fa-bolt mr-2"></i> Buy Now
                                        </button>
                                        @endif
                                    @endauth
                                </div>
                            </div>
                            @else
                            <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
                                <div class="text-red-700 text-center">
                                    <i class="fas fa-times-circle text-xl mb-2"></i>
                                    <p class="font-medium">This product is currently out of stock</p>
                                    <p class="text-sm mt-1">Check back later or contact the vendor</p>
                                </div>
                            </div>
                            @endif
                            
                            <!-- Vendor Info -->
                            <div class="mt-6 pt-6 border-t">
                                <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Sold by</h4>
                                <a href="#" class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100">
                                    <div class="w-10 h-10 bg-primary text-white rounded-full flex items-center justify-center mr-3">
                                        <i class="fas fa-store"></i>
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-800">{{ $listing->vendor->business_name ?? 'Vendor' }}</div>
                                        <div class="text-xs text-gray-500">
                                            @if($listing->vendor->vendor_type == 'china_supplier')
                                            International Vendor
                                            @else
                                            Local Vendor
                                            @endif
                                        </div>
                                    </div>
                                    <i class="fas fa-chevron-right ml-auto text-gray-400"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Product Details Tabs -->
                <div class="bg-white rounded-xl shadow-sm">
                    <!-- Tabs -->
                    <div class="border-b">
                        <nav class="flex">
                            <button id="descriptionTab" 
                                    class="px-6 py-4 font-medium border-b-2 border-primary text-primary">
                                Description
                            </button>
                            <button id="specsTab" 
                                    class="px-6 py-4 font-medium text-gray-600 hover:text-primary">
                                Specifications
                            </button>
                            <button id="shippingTab" 
                                    class="px-6 py-4 font-medium text-gray-600 hover:text-primary">
                                Shipping & Returns
                            </button>
                            <button id="reviewsTab" 
                                    class="px-6 py-4 font-medium text-gray-600 hover:text-primary">
                                Reviews (0)
                            </button>
                        </nav>
                    </div>
                    
                    <!-- Tab Content -->
                    <div class="p-6">
                        <!-- Description -->
                        <div id="descriptionContent">
                            <h3 class="text-xl font-bold text-gray-800 mb-4">Product Description</h3>
                            <div class="prose max-w-none">
                                {!! nl2br(e($listing->description)) !!}
                            </div>
                        </div>
                        
                        <!-- Specifications -->
                        <div id="specsContent" class="hidden">
                            <h3 class="text-xl font-bold text-gray-800 mb-4">Product Specifications</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @if($listing->attributes && is_array($listing->attributes))
                                    @foreach($listing->attributes as $key => $value)
                                    <div class="flex justify-between py-3 border-b border-gray-100">
                                        <span class="text-gray-600 capitalize">{{ str_replace('_', ' ', $key) }}</span>
                                        <span class="font-medium text-gray-800">{{ $value }}</span>
                                    </div>
                                    @endforeach
                                @endif
                                
                                <!-- Default Specifications -->
                                <div class="flex justify-between py-3 border-b border-gray-100">
                                    <span class="text-gray-600">Condition</span>
                                    <span class="font-medium text-gray-800 capitalize">{{ $listing->condition }}</span>
                                </div>
                                <div class="flex justify-between py-3 border-b border-gray-100">
                                    <span class="text-gray-600">Origin</span>
                                    <span class="font-medium text-gray-800 capitalize">{{ $listing->origin }}</span>
                                </div>
                                @if($listing->weight_kg)
                                <div class="flex justify-between py-3 border-b border-gray-100">
                                    <span class="text-gray-600">Weight</span>
                                    <span class="font-medium text-gray-800">{{ $listing->weight_kg }} kg</span>
                                </div>
                                @endif
                                @if($listing->sku)
                                <div class="flex justify-between py-3 border-b border-gray-100">
                                    <span class="text-gray-600">SKU</span>
                                    <span class="font-medium text-gray-800">{{ $listing->sku }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Shipping & Returns -->
                        <div id="shippingContent" class="hidden">
                            <h3 class="text-xl font-bold text-gray-800 mb-4">Shipping & Returns</h3>
                            <div class="space-y-6">
                                <div>
                                    <h4 class="font-bold text-gray-700 mb-2">Shipping Information</h4>
                                    <ul class="space-y-2 text-gray-600">
                                        <li class="flex items-start">
                                            <i class="fas fa-check text-green-500 mr-2 mt-1"></i>
                                            <span>Standard shipping: 3-7 business days</span>
                                        </li>
                                        <li class="flex items-start">
                                            <i class="fas fa-check text-green-500 mr-2 mt-1"></i>
                                            <span>Express shipping available at checkout</span>
                                        </li>
                                        <li class="flex items-start">
                                            <i class="fas fa-check text-green-500 mr-2 mt-1"></i>
                                            <span>Free shipping on orders over $100</span>
                                        </li>
                                    </ul>
                                </div>
                                
                                <div>
                                    <h4 class="font-bold text-gray-700 mb-2">Return Policy</h4>
                                    <ul class="space-y-2 text-gray-600">
                                        <li class="flex items-start">
                                            <i class="fas fa-check text-green-500 mr-2 mt-1"></i>
                                            <span>30-day return policy</span>
                                        </li>
                                        <li class="flex items-start">
                                            <i class="fas fa-check text-green-500 mr-2 mt-1"></i>
                                            <span>Items must be in original condition</span>
                                        </li>
                                        <li class="flex items-start">
                                            <i class="fas fa-check text-green-500 mr-2 mt-1"></i>
                                            <span>Free returns for defective items</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Reviews -->
                        <div id="reviewsContent" class="hidden">
                            <h3 class="text-xl font-bold text-gray-800 mb-4">Customer Reviews</h3>
                            <div class="text-center py-12">
                                <div class="mx-auto w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                    <i class="fas fa-comments text-gray-400 text-3xl"></i>
                                </div>
                                <h4 class="text-lg font-medium text-gray-700 mb-2">No reviews yet</h4>
                                <p class="text-gray-600">Be the first to review this product</p>
                                <button onclick="writeReview()"
                                        class="mt-4 px-6 py-2 bg-primary text-white rounded-lg hover:bg-indigo-700 font-medium">
                                    Write a Review
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Column - Sidebar -->
            <div class="lg:w-1/3">
                <!-- Vendor Information -->
                <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Vendor Information</h3>
                    
                    <div class="space-y-4">
                        <!-- Vendor Details -->
                        <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                            <div class="w-12 h-12 bg-primary text-white rounded-full flex items-center justify-center mr-4">
                                <i class="fas fa-store text-xl"></i>
                            </div>
                            <div>
                                <div class="font-bold text-gray-800">{{ $listing->vendor->business_name ?? 'Vendor' }}</div>
                                <div class="text-sm text-gray-600">
                                    @if($listing->vendor->vendor_type == 'china_supplier')
                                    <i class="fas fa-globe-americas mr-1"></i> International Supplier
                                    @else
                                    <i class="fas fa-map-marker-alt mr-1"></i> {{ $listing->vendor->city ?? 'Local' }}
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <!-- Vendor Stats -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-center p-3 bg-gray-50 rounded-lg">
                                <div class="text-2xl font-bold text-primary">4.8</div>
                                <div class="text-xs text-gray-600">Rating</div>
                            </div>
                            <div class="text-center p-3 bg-gray-50 rounded-lg">
                                <div class="text-2xl font-bold text-primary">98%</div>
                                <div class="text-xs text-gray-600">Positive</div>
                            </div>
                        </div>
                        
                        <!-- Vendor Actions -->
                        <div class="space-y-2">
                            <button onclick="contactVendor()"
                                    class="w-full px-4 py-2 border border-primary text-primary rounded-lg hover:bg-primary hover:text-white font-medium">
                                <i class="fas fa-envelope mr-2"></i> Contact Vendor
                            </button>
                            <a href="#"
                               class="block text-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium">
                                <i class="fas fa-store mr-2"></i> View Store
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Safety & Security -->
                <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Safety & Security</h3>
                    
                    <div class="space-y-3">
                        <div class="flex items-start">
                            <div class="w-8 h-8 bg-green-100 text-green-600 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <div>
                                <div class="font-medium text-gray-800">Escrow Protection</div>
                                <p class="text-sm text-gray-600">Your payment is held securely until you confirm delivery</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div>
                                <div class="font-medium text-gray-800">Verified Vendor</div>
                                <p class="text-sm text-gray-600">This vendor has been verified by our team</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="w-8 h-8 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                                <i class="fas fa-headset"></i>
                            </div>
                            <div>
                                <div class="font-medium text-gray-800">24/7 Support</div>
                                <p class="text-sm text-gray-600">Our support team is available to help</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Related Products -->
                @if($related->count() > 0)
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Related Products</h3>
                    
                    <div class="space-y-4">
                        @foreach($related as $relatedListing)
                        <a href="{{ route('marketplace.show', $relatedListing) }}" 
                           class="flex items-center p-3 hover:bg-gray-50 rounded-lg group">
                            @if($relatedListing->images->first())
                            <img src="{{ asset('storage/' . $relatedListing->images->first()->path) }}" 
                                 alt="{{ $relatedListing->title }}" 
                                 class="w-16 h-16 object-cover rounded-lg mr-4">
                            @else
                            <div class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center mr-4">
                                <i class="fas fa-image text-gray-400"></i>
                            </div>
                            @endif
                            
                            <div class="flex-1">
                                <div class="font-medium text-gray-800 group-hover:text-primary line-clamp-1">
                                    {{ $relatedListing->title }}
                                </div>
                                <div class="text-sm text-primary font-bold">
                                    ${{ number_format($relatedListing->price, 2) }}
                                </div>
                            </div>
                        </a>
                        @endforeach
                    </div>
                    
                    @if($category)
                    <div class="mt-4 pt-4 border-t">
                        <a href="{{ route('categories.show', $category) }}" 
                           class="block text-center px-4 py-2 border border-primary text-primary rounded-lg hover:bg-primary hover:text-white font-medium">
                            View More in {{ $category->name }}
                        </a>
                    </div>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
    // Tab functionality
    document.addEventListener('DOMContentLoaded', function() {
        // Tab switching
        const tabs = {
            descriptionTab: 'descriptionContent',
            specsTab: 'specsContent',
            shippingTab: 'shippingContent',
            reviewsTab: 'reviewsContent'
        };
        
        Object.keys(tabs).forEach(tabId => {
            document.getElementById(tabId)?.addEventListener('click', function() {
                // Hide all content
                Object.values(tabs).forEach(contentId => {
                    document.getElementById(contentId)?.classList.add('hidden');
                });
                
                // Remove active from all tabs
                Object.keys(tabs).forEach(tab => {
                    const tabElement = document.getElementById(tab);
                    tabElement.classList.remove('border-primary', 'text-primary');
                    tabElement.classList.add('text-gray-600');
                });
                
                // Show selected content
                document.getElementById(tabs[tabId])?.classList.remove('hidden');
                
                // Activate selected tab
                this.classList.remove('text-gray-600');
                this.classList.add('border-primary', 'text-primary');
            });
        });
    });
    
    // Image gallery
    function changeMainImage(src) {
        document.getElementById('mainImage').src = src;
    }
    
    // Quantity control
    function updateQuantity(change) {
        const input = document.getElementById('quantity');
        let current = parseInt(input.value) || 1;
        const max = parseInt(input.max) || 99;
        const min = parseInt(input.min) || 1;
        
        current += change;
        
        if (current < min) current = min;
        if (current > max) current = max;
        
        input.value = current;
    }
    
    // Add to cart
    function addToCart() {
        const quantity = document.getElementById('quantity').value;
        
        // Show loading
        const button = event.target;
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Adding...';
        button.disabled = true;
        
        // Simulate API call
        setTimeout(() => {
            // Show success
            button.innerHTML = '<i class="fas fa-check mr-2"></i> Added to Cart!';
            button.classList.remove('bg-primary');
            button.classList.add('bg-green-500');
            
            // Reset after 2 seconds
            setTimeout(() => {
                button.innerHTML = originalText;
                button.classList.remove('bg-green-500');
                button.classList.add('bg-primary');
                button.disabled = false;
            }, 2000);
            
            // Update cart count (you would update this from API response)
            updateCartCount(parseInt(quantity));
        }, 1000);
    }
    
    // Add to wishlist
    function addToWishlist() {
        const button = event.target;
        const originalText = button.innerHTML;
        
        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Adding...';
        button.disabled = true;
        
        setTimeout(() => {
            button.innerHTML = '<i class="fas fa-heart mr-2"></i> Added to Wishlist';
            button.classList.remove('border-primary', 'text-primary');
            button.classList.add('bg-red-500', 'border-red-500', 'text-white');
            button.disabled = true;
        }, 1000);
    }
    
    // Buy now
    function buyNow() {
        const quantity = document.getElementById('quantity').value;
        // Redirect to checkout
        window.location.href = `/checkout?listing_id={{ $listing->id }}&quantity=${quantity}`;
    }
    
    // Contact vendor
    function contactVendor() {
        // Open contact modal or redirect to messages
        alert('Contact vendor feature coming soon!');
    }
    
    // Write review
    function writeReview() {
        // Open review modal
        alert('Review feature coming soon!');
    }
    
    // Update cart count in header
    function updateCartCount(quantity) {
        const cartCount = document.querySelector('.cart-count');
        if (cartCount) {
            const current = parseInt(cartCount.textContent) || 0;
            cartCount.textContent = current + quantity;
        }
    }
    
    // Validate quantity input
    document.getElementById('quantity')?.addEventListener('change', function() {
        let value = parseInt(this.value) || 1;
        const max = parseInt(this.max) || 99;
        const min = parseInt(this.min) || 1;
        
        if (value < min) value = min;
        if (value > max) value = max;
        
        this.value = value;
    });
</script>

<style>
    /* Custom styles for product page */
    #mainImage {
        max-height: 480px;
    }
    
    .tab-content {
        transition: all 0.3s ease;
    }
    
    /* Smooth transitions */
    .transition-all {
        transition: all 0.3s ease;
    }
    
    /* Custom scrollbar for thumbnails */
    .overflow-x-auto::-webkit-scrollbar {
        height: 4px;
    }
    
    .overflow-x-auto::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 2px;
    }
    
    .overflow-x-auto::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 2px;
    }
    
    .overflow-x-auto::-webkit-scrollbar-thumb:hover {
        background: #a1a1a1;
    }
</style>
@endsection