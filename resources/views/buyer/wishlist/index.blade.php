@extends('layouts.buyer')

@section('title', 'My Wishlist - ' . config('app.name'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold">My Wishlist</h1>
        @if($wishlistItems->count() > 0)
        <div class="text-gray-600">
            {{ $wishlistItems->count() }} {{ Str::plural('item', $wishlistItems->count()) }}
        </div>
        @endif
    </div>
    
    @if(session('success'))
    <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6">
        <p class="text-green-700">{{ session('success') }}</p>
    </div>
    @endif
    
    @if($wishlistItems->count() > 0)
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @foreach($wishlistItems as $item)
        <div class="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-md transition wishlist-item" 
             data-listing-id="{{ $item->listing_id }}">
            <div class="relative">
                <!-- Product Image -->
                <a href="{{ $item->listing->category ? route('marketplace.show.category', ['category_slug' => $item->listing->category->slug, 'listing' => $item->listing->slug]) : route('marketplace.show', $item->listing) }}">
                    @if($item->listing->images->first())
                    <img src="{{ asset('storage/' . $item->listing->images->first()->path) }}" 
                         alt="{{ $item->listing->title }}" 
                         class="w-full h-48 object-cover">
                    @else
                    <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                        <i class="fas fa-image text-gray-400 text-4xl"></i>
                    </div>
                    @endif
                </a>
                
                <!-- Badges -->
                <div class="absolute top-3 left-3">
                    @if($item->listing->origin == 'imported')
                    <span class="px-2 py-1 bg-blue-500 text-white text-xs font-bold rounded">
                        <i class="fas fa-plane mr-1"></i> Imported
                    </span>
                    @else
                    <span class="px-2 py-1 bg-green-500 text-white text-xs font-bold rounded">
                        <i class="fas fa-home mr-1"></i> Local
                    </span>
                    @endif
                </div>
                
                <!-- Remove Button -->
                <button onclick="removeFromWishlist({{ $item->listing_id }})"
                        class="absolute top-3 right-3 w-8 h-8 bg-white rounded-full flex items-center justify-center text-red-500 hover:bg-red-50 shadow">
                    <i class="fas fa-heart"></i>
                </button>
                
                <!-- Stock Status Badge -->
                @if($item->listing->stock <= 0)
                <div class="absolute bottom-3 left-3 right-3">
                    <div class="bg-red-500 text-white text-xs font-bold px-2 py-1 rounded text-center">
                        Out of Stock
                    </div>
                </div>
                @elseif($item->listing->stock <= 5)
                <div class="absolute bottom-3 left-3 right-3">
                    <div class="bg-orange-500 text-white text-xs font-bold px-2 py-1 rounded text-center">
                        Only {{ $item->listing->stock }} left!
                    </div>
                </div>
                @endif
            </div>
            
            <!-- Product Info -->
            <div class="p-4">
                <!-- Category -->
                <div class="mb-2">
                    <span class="text-xs text-gray-500">
                        {{ $item->listing->category->name ?? 'Uncategorized' }}
                    </span>
                </div>
                
                <!-- Title -->
                <a href="{{ $item->listing->category ? route('marketplace.show.category', ['category_slug' => $item->listing->category->slug, 'listing' => $item->listing->slug]) : route('marketplace.show', $item->listing) }}">
                    <h3 class="font-bold text-gray-800 mb-2 line-clamp-2 hover:text-primary">
                        {{ $item->listing->title }}
                    </h3>
                </a>
                
                <!-- Vendor -->
                <div class="flex items-center mb-3">
                    <div class="w-5 h-5 bg-primary text-white rounded-full flex items-center justify-center text-xs mr-2">
                        <i class="fas fa-store"></i>
                    </div>
                    <span class="text-sm text-gray-700">
                        {{ $item->listing->vendor->business_name ?? 'Vendor' }}
                    </span>
                </div>
                
                <!-- Price -->
                <div class="mb-4">
                    <div class="text-2xl font-bold text-primary">
                        UGX {{ number_format($item->listing->price, 2) }}
                    </div>
                    @if(isset($item->meta['price_when_added']) && $item->meta['price_when_added'] != $item->listing->price)
                        @if($item->listing->price < $item->meta['price_when_added'])
                        <div class="text-xs text-green-600">
                            <i class="fas fa-arrow-down mr-1"></i>
                            Price decreased by UGX {{ number_format($item->meta['price_when_added'] - $item->listing->price, 2) }}
                        </div>
                        @else
                        <div class="text-xs text-red-600">
                            <i class="fas fa-arrow-up mr-1"></i>
                            Price increased by UGX {{ number_format($item->listing->price - $item->meta['price_when_added'], 2) }}
                        </div>
                        @endif
                    @endif
                </div>
                
                <!-- Actions -->
                <div class="space-y-2">
                    @if($item->listing->stock > 0 && $item->listing->is_active)
                    <button onclick="moveToCart({{ $item->listing_id }})"
                            class="w-full px-4 py-2 bg-primary text-white rounded-lg hover:bg-indigo-700 font-medium transition">
                        <i class="fas fa-shopping-cart mr-2"></i> Add to Cart
                    </button>
                    @else
                    <button disabled
                            class="w-full px-4 py-2 bg-gray-300 text-gray-500 rounded-lg font-medium cursor-not-allowed">
                        <i class="fas fa-ban mr-2"></i> Unavailable
                    </button>
                    @endif
                    
                    <a href="{{ $item->listing->category ? route('marketplace.show.category', ['category_slug' => $item->listing->category->slug, 'listing' => $item->listing->slug]) : route('marketplace.show', $item->listing) }}"
                       class="block w-full px-4 py-2 text-center border border-primary text-primary rounded-lg hover:bg-primary hover:text-white font-medium transition">
                        <i class="fas fa-eye mr-2"></i> View Details
                    </a>
                </div>
                
                <!-- Added Date -->
                <div class="mt-3 pt-3 border-t text-xs text-gray-500">
                    <i class="fas fa-clock mr-1"></i>
                    Added {{ $item->created_at->diffForHumans() }}
                </div>
            </div>
        </div>
        @endforeach
    </div>
    
    @else
    <!-- Empty Wishlist -->
    <div class="bg-white rounded-lg shadow p-12 text-center">
        <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-6">
            <i class="fas fa-heart text-gray-400 text-4xl"></i>
        </div>
        <h2 class="text-2xl font-bold text-gray-800 mb-2">Your wishlist is empty</h2>
        <p class="text-gray-600 mb-6">Save your favorite items to buy them later!</p>
        <a href="{{ route('marketplace.index') }}" 
           class="inline-flex items-center px-6 py-3 bg-primary text-white rounded-lg font-semibold hover:bg-indigo-700 transition">
            <i class="fas fa-shopping-bag mr-2"></i> Start Shopping
        </a>
    </div>
    @endif
</div>

<script>
// Move to cart
function moveToCart(listingId) {
    const button = event.target.closest('button');
    const originalHtml = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Adding...';
    button.disabled = true;
    
    fetch(`/buyer/wishlist/move-to-cart/${listingId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove item from DOM
            const item = document.querySelector(`.wishlist-item[data-listing-id="${listingId}"]`);
            if (item) {
                item.classList.add('opacity-0', 'transform', 'scale-95');
                setTimeout(() => {
                    if (data.wishlist_count === 0) {
                        location.reload();
                    } else {
                        item.remove();
                    }
                }, 300);
            }
            
            // Update cart count
            updateCartCount(data.cart_count);
            
            showToast(data.message, 'success');
        } else {
            button.innerHTML = originalHtml;
            button.disabled = false;
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        button.innerHTML = originalHtml;
        button.disabled = false;
        showToast('Failed to move item to cart', 'error');
    });
}

// Remove from wishlist
function removeFromWishlist(listingId) {
    if (!confirm('Remove this item from wishlist?')) return;
    
    fetch(`/buyer/wishlist/remove/${listingId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove item from DOM
            const item = document.querySelector(`.wishlist-item[data-listing-id="${listingId}"]`);
            if (item) {
                item.classList.add('opacity-0', 'transform', 'scale-95');
                setTimeout(() => {
                    if (data.wishlist_count === 0) {
                        location.reload();
                    } else {
                        item.remove();
                    }
                }, 300);
            }
            
            showToast(data.message, 'success');
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Failed to remove item', 'error');
    });
}

// Update cart count in navbar
function updateCartCount(count) {
    document.querySelectorAll('.cart-count').forEach(element => {
        element.textContent = count;
        element.classList.add('animate-bounce');
        setTimeout(() => {
            element.classList.remove('animate-bounce');
        }, 1000);
    });
}

// Show toast notification
function showToast(message, type = 'info') {
    const existingToasts = document.querySelectorAll('.custom-toast');
    existingToasts.forEach(toast => toast.remove());
    
    const toast = document.createElement('div');
    toast.className = `custom-toast fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg transform transition-all duration-300 translate-x-0`;
    
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
</script>

<style>
.wishlist-item {
    transition: all 0.3s ease;
}
</style>
@endsection