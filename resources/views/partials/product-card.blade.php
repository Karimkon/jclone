{{-- resources/views/partials/product-card.blade.php --}}
{{-- Reusable product card component --}}

@php
    $product = $product ?? $listing ?? null;
    $showBadge = $showBadge ?? true;
    $showRating = $showRating ?? true;
    $showActions = $showActions ?? true;
    $cardSize = $cardSize ?? 'normal'; // 'small', 'normal', 'large'
@endphp

@if($product)
<div class="product-card bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-lg transition-all duration-300 group">
    <!-- Product Image -->
    <div class="relative aspect-square overflow-hidden bg-gray-100">
        <a href="{{ $product->category ? route('marketplace.show.category', ['category_slug' => $product->category->slug, 'listing' => $product->slug]) : route('marketplace.show', $product) }}">
            @if($product->images && $product->images->first())
            <img src="{{ asset('storage/' . $product->images->first()->path) }}" 
                 alt="{{ $product->title }}"
                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                 loading="lazy">
            @else
            <div class="w-full h-full flex items-center justify-center bg-gray-100">
                <i class="fas fa-image text-gray-300 text-4xl"></i>
            </div>
            @endif
        </a>
        
        <!-- Badges -->
        @if($showBadge)
        <div class="absolute top-2 left-2 flex flex-col gap-1">
            @if($product->origin == 'imported')
            <span class="bg-blue-500 text-white text-xs px-2 py-1 rounded-full font-medium">
                <i class="fas fa-plane mr-1"></i>Imported
            </span>
            @else
            <span class="bg-green-500 text-white text-xs px-2 py-1 rounded-full font-medium">
                <i class="fas fa-map-marker-alt mr-1"></i>Local
            </span>
            @endif
            
            @if($product->condition == 'new')
            <span class="bg-purple-500 text-white text-xs px-2 py-1 rounded-full font-medium">
                NEW
            </span>
            @endif
            
            @if(isset($product->discount) && $product->discount > 0)
            <span class="bg-red-500 text-white text-xs px-2 py-1 rounded-full font-bold">
                -{{ $product->discount }}%
            </span>
            @endif
        </div>
        @endif
        
        <!-- Quick Actions (visible on hover) -->
        @if($showActions)
        <div class="product-actions absolute top-2 right-2 flex flex-col gap-2 opacity-0 translate-y-2 group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-300">
            <button data-quick-wishlist 
                    data-listing-id="{{ $product->id }}"
                    class="w-8 h-8 bg-white rounded-full shadow-md flex items-center justify-center hover:bg-red-50 transition-colors"
                    title="Add to Wishlist">
                <i class="far fa-heart text-gray-600 hover:text-red-500 transition-colors"></i>
            </button>
            <button onclick="quickView({{ $product->id }})"
                    class="w-8 h-8 bg-white rounded-full shadow-md flex items-center justify-center hover:bg-blue-50 transition-colors"
                    title="Quick View">
                <i class="fas fa-eye text-gray-600 hover:text-primary transition-colors"></i>
            </button>
            <button onclick="shareProduct({{ $product->id }})"
                    class="w-8 h-8 bg-white rounded-full shadow-md flex items-center justify-center hover:bg-green-50 transition-colors"
                    title="Share">
                <i class="fas fa-share-alt text-gray-600 hover:text-green-500 transition-colors"></i>
            </button>
        </div>
        @endif
        
        <!-- Stock Status Overlay -->
        @if($product->stock <= 0)
        <div class="absolute inset-0 bg-black/50 flex items-center justify-center">
            <span class="bg-red-500 text-white px-4 py-2 rounded-lg font-bold text-sm">
                Out of Stock
            </span>
        </div>
        @elseif($product->stock <= 5)
        <div class="absolute bottom-2 left-2 right-2">
            <div class="bg-orange-100 text-orange-700 text-xs px-2 py-1 rounded-full text-center">
                <i class="fas fa-exclamation-triangle mr-1"></i>
                Only {{ $product->stock }} left!
            </div>
        </div>
        @endif
    </div>
    
    <!-- Product Info -->
    <div class="p-3 {{ $cardSize == 'small' ? 'p-2' : 'p-3' }}">
        <!-- Category -->
        @if($product->category)
        <p class="text-xs text-gray-400 mb-1 truncate">
            {{ $product->category->name }}
        </p>
        @endif
        
        <!-- Title -->
        <a href="{{ $product->category ? route('marketplace.show.category', ['category_slug' => $product->category->slug, 'listing' => $product->slug]) : route('marketplace.show', $product) }}">
            <h3 class="text-sm font-medium text-gray-700 line-clamp-2 mb-2 hover:text-primary transition-colors {{ $cardSize == 'small' ? 'text-xs' : 'text-sm' }}">
                {{ $product->title }}
            </h3>
        </a>
        
        <!-- Rating -->
        @if($showRating)
        @php
            $rating = $product->average_rating ?? $product->rating ?? 0;
            $reviewCount = $product->reviews_count ?? 0;
            $fullStars = floor($rating);
            $hasHalfStar = ($rating - $fullStars) >= 0.5;
        @endphp
        @if($reviewCount > 0)
        <div class="flex items-center gap-1 mb-2">
            <div class="flex text-yellow-400">
                @for($i = 0; $i < 5; $i++)
                    @if($i < $fullStars)
                        <i class="fas fa-star text-xs"></i>
                    @elseif($hasHalfStar && $i == $fullStars)
                        <i class="fas fa-star-half-alt text-xs"></i>
                    @else
                        <i class="far fa-star text-xs text-gray-300"></i>
                    @endif
                @endfor
            </div>
            <span class="text-xs text-gray-500">{{ number_format($rating, 1) }}</span>
            <span class="text-xs text-gray-400">({{ $reviewCount }})</span>
        </div>
        @else
        <div class="flex items-center gap-1 mb-2">
            <div class="flex text-gray-300">
                @for($i = 0; $i < 5; $i++)
                    <i class="far fa-star text-xs"></i>
                @endfor
            </div>
            <span class="text-xs text-gray-400">No reviews</span>
        </div>
        @endif
        @endif
        
        <!-- Price & Add to Cart -->
        <div class="flex items-center justify-between">
            <div class="flex flex-col">
                <span class="text-lg font-bold text-primary {{ $cardSize == 'small' ? 'text-base' : 'text-lg' }}">
                    ${{ number_format($product->price, 2) }}
                </span>
                @if(isset($product->original_price) && $product->original_price > $product->price)
                <span class="text-xs text-gray-400 line-through">
                    ${{ number_format($product->original_price, 2) }}
                </span>
                @endif
            </div>
            
            @if($product->stock > 0)
            <button data-quick-cart 
                    data-listing-id="{{ $product->id }}"
                    class="w-9 h-9 bg-primary text-white rounded-lg flex items-center justify-center hover:bg-primary-dark transition-colors shadow-sm hover:shadow-md"
                    title="Add to Cart">
                <i class="fas fa-shopping-cart text-sm"></i>
            </button>
            @else
            <button disabled 
                    class="w-9 h-9 bg-gray-200 text-gray-400 rounded-lg flex items-center justify-center cursor-not-allowed"
                    title="Out of Stock">
                <i class="fas fa-ban text-sm"></i>
            </button>
            @endif
        </div>
        
        <!-- Vendor Info (optional) -->
       @if(isset($showVendor) && $showVendor && $product->vendor)
<div class="mt-2 pt-2 border-t border-gray-100">
    <div class="flex flex-col gap-1">
        {{-- Business Name & Verification Badge --}}
        <div class="flex items-center gap-1.5">
            <span class="text-xs font-semibold text-gray-700 truncate max-w-[140px]">
                {{ $product->vendor->business_name ?? 'Vendor' }}
            </span>
            @if($product->vendor->is_verified ?? false)
                <i class="fas fa-check-circle text-blue-500 text-[10px]"></i>
            @endif
        </div>

        {{-- Time on BebaMart --}}
        <div class="flex items-center gap-1.5 text-[10px] text-gray-500">
            <i class="fas fa-user-clock opacity-70"></i>
            <span>{{ $product->vendor->created_at ? $product->vendor->created_at->diffForHumans(null, true) : 'New' }} on BebaMart</span>
        </div>
    </div>
</div>
@endif
    </div>
</div>
@endif