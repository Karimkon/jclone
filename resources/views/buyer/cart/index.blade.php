@extends('layouts.buyer')

@section('title', 'Shopping Cart - ' . config('app.name'))

@push('styles')
<style>
/* ── Cart Page Styles ─────────────────────────────── */
.cart-page { background: #f5f6fa; min-height: 100vh; }

/* Header gradient */
.cart-header-bar {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    padding: 28px 0 60px;
}

/* Lift card above the header gradient */
.cart-lift { margin-top: -44px; }

/* Cart item card */
.cart-item {
    background: #fff;
    border-radius: 16px;
    transition: box-shadow .25s ease, transform .2s ease;
    overflow: hidden;
}
.cart-item:hover { box-shadow: 0 8px 32px rgba(79,70,229,.09); transform: translateY(-1px); }

/* Custom checkbox */
.custom-check {
    width: 20px; height: 20px;
    accent-color: #4f46e5;
    cursor: pointer;
    flex-shrink: 0;
}

/* Product image */
.product-thumb {
    width: 96px; height: 96px;
    border-radius: 14px;
    object-fit: cover;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(0,0,0,.1);
}
.product-thumb-placeholder {
    width: 96px; height: 96px;
    border-radius: 14px;
    background: linear-gradient(135deg, #e0e7ff, #f3f4f6);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}

/* Quantity stepper */
.qty-stepper {
    display: inline-flex;
    align-items: center;
    background: #f5f6fa;
    border-radius: 50px;
    overflow: hidden;
    border: 1.5px solid #e5e7eb;
}
.qty-stepper button {
    width: 34px; height: 34px;
    display: flex; align-items: center; justify-content: center;
    background: transparent;
    color: #4f46e5;
    font-size: 14px;
    cursor: pointer;
    transition: background .2s;
    border: none;
}
.qty-stepper button:hover { background: #e0e7ff; }
.qty-stepper button:disabled { opacity: .35; cursor: not-allowed; }
.qty-stepper input {
    width: 44px;
    text-align: center;
    border: none;
    background: transparent;
    font-weight: 700;
    font-size: 15px;
    color: #1f2937;
    outline: none;
}

/* Remove button */
.btn-remove {
    width: 32px; height: 32px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    background: #fef2f2;
    color: #ef4444;
    border: none;
    cursor: pointer;
    transition: background .2s, transform .15s;
    flex-shrink: 0;
}
.btn-remove:hover { background: #ef4444; color: #fff; transform: scale(1.1); }

/* Badge variants */
.badge-imported { background: #dbeafe; color: #1d4ed8; }
.badge-local    { background: #dcfce7; color: #15803d; }
.badge-pill {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 3px 10px; border-radius: 50px;
    font-size: 11px; font-weight: 600;
}

/* Order Summary card */
.summary-card {
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 8px 32px rgba(79,70,229,.1);
    overflow: hidden;
}
.summary-card-header {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    padding: 20px 24px;
    color: #fff;
}
.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    font-size: 14px;
    color: #6b7280;
}
.summary-row + .summary-row { border-top: 1px solid #f3f4f6; }
.summary-total { font-size: 18px; font-weight: 700; color: #111827; }

/* Checkout button */
.btn-checkout {
    display: flex; align-items: center; justify-content: center; gap: 8px;
    width: 100%;
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    color: #fff;
    font-weight: 700;
    font-size: 15px;
    border: none;
    border-radius: 14px;
    padding: 15px;
    cursor: pointer;
    transition: opacity .2s, transform .2s, box-shadow .2s;
    box-shadow: 0 4px 20px rgba(79,70,229,.35);
}
.btn-checkout:hover { opacity: .93; transform: translateY(-2px); box-shadow: 0 8px 28px rgba(79,70,229,.45); }
.btn-checkout:disabled { background: #d1d5db; box-shadow: none; cursor: not-allowed; transform: none; }

/* Continue shopping */
.btn-continue {
    display: flex; align-items: center; justify-content: center; gap: 8px;
    width: 100%;
    background: #f5f6fa;
    color: #4f46e5;
    font-weight: 600;
    font-size: 14px;
    border: none;
    border-radius: 14px;
    padding: 13px;
    cursor: pointer;
    transition: background .2s;
    text-decoration: none;
}
.btn-continue:hover { background: #e0e7ff; }

/* Trust badge row */
.trust-row { display: flex; align-items: center; gap: 8px; font-size: 13px; color: #6b7280; }

/* Clear cart / delete selected */
.btn-danger-text {
    display: inline-flex; align-items: center; gap-5px;
    color: #ef4444; background: none; border: none;
    font-size: 13px; font-weight: 500; cursor: pointer;
    padding: 6px 10px; border-radius: 8px;
    transition: background .2s, color .2s;
}
.btn-danger-text:hover { background: #fef2f2; }

/* Select-all header */
.cart-items-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 16px 20px;
    background: #f9fafb;
    border-radius: 16px 16px 0 0;
    border-bottom: 1.5px solid #f3f4f6;
}

/* Empty cart */
.empty-cart-icon {
    width: 100px; height: 100px;
    background: linear-gradient(135deg, #e0e7ff, #f3e8ff);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 24px;
}

/* Toast */
.toast-wrap {
    position: fixed; top: 20px; right: 20px; z-index: 9999;
    display: flex; flex-direction: column; gap: 10px;
    pointer-events: none;
}
.toast {
    pointer-events: auto;
    padding: 14px 20px;
    border-radius: 12px;
    box-shadow: 0 8px 30px rgba(0,0,0,.12);
    font-weight: 600;
    font-size: 14px;
    display: flex; align-items: center; gap: 10px;
    animation: toastIn .35s ease;
    min-width: 260px;
}
@keyframes toastIn {
    from { transform: translateX(120%); opacity: 0; }
    to   { transform: translateX(0);    opacity: 1; }
}
@keyframes toastOut {
    from { transform: translateX(0);    opacity: 1; }
    to   { transform: translateX(120%); opacity: 0; }
}
.toast-success { background: #ecfdf5; color: #065f46; border-left: 4px solid #10b981; }
.toast-error   { background: #fef2f2; color: #991b1b; border-left: 4px solid #ef4444; }
.toast-warning { background: #fffbeb; color: #92400e; border-left: 4px solid #f59e0b; }
.toast-info    { background: #eff6ff; color: #1e40af; border-left: 4px solid #3b82f6; }

/* Progress bar shimmer on update */
@keyframes shimmer {
    from { background-position: -200% 0; }
    to   { background-position:  200% 0; }
}
.shimmer {
    background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,.6) 50%, transparent 100%);
    background-size: 200% 100%;
    animation: shimmer .6s ease;
}

/* Animations */
.fade-slide-in { animation: fadeSlideIn .3s ease; }
@keyframes fadeSlideIn {
    from { opacity:0; transform:translateY(8px); }
    to   { opacity:1; transform:translateY(0); }
}

/* Responsive tweaks */
@media(max-width:640px){
    .cart-header-bar { padding: 20px 0 50px; }
    .cart-lift { margin-top: -36px; }
    .product-thumb, .product-thumb-placeholder { width:76px; height:76px; }
}
</style>
@endpush

@section('content')
<div class="cart-page">

    {{-- ── Gradient Header ───────────────────────────────────── --}}
    <div class="cart-header-bar">
        <div class="container mx-auto px-4 sm:px-6">
            <div class="flex items-center gap-3 text-white/80 text-sm mb-2">
                <a href="{{ route('buyer.dashboard') }}" class="hover:text-white transition">Dashboard</a>
                <i class="fas fa-chevron-right text-xs"></i>
                <span class="text-white font-medium">Shopping Cart</span>
            </div>
            <div class="flex items-center gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-white">Your Cart</h1>
                    <p class="text-white/70 text-sm mt-1">
                        @if($cart && !empty($cart->items))
                            {{ count($cart->items) }} {{ Str::plural('item', count($cart->items)) }} in your cart
                        @else
                            No items yet
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Flash Messages ─────────────────────────────────────── --}}
    @if(session('success') || session('error'))
    <div class="container mx-auto px-4 sm:px-6 pt-4">
        @if(session('success'))
        <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-4 py-3 text-sm font-medium">
            <i class="fas fa-check-circle text-emerald-500"></i> {{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 text-sm font-medium">
            <i class="fas fa-exclamation-circle text-red-400"></i> {{ session('error') }}
        </div>
        @endif
    </div>
    @endif

    <div class="container mx-auto px-4 sm:px-6 pb-12">

        @if($cart && !empty($cart->items))
        @php $enrichedItems = $cart->getEnrichedItems(); @endphp

        <div class="cart-lift grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- ── Left: Cart Items ────────────────────────────── --}}
            <div class="lg:col-span-2 space-y-4 fade-slide-in">

                {{-- Select-all / Clear header --}}
                <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                    <div class="cart-items-header">
                        <label class="flex items-center gap-3 cursor-pointer select-none">
                            <input type="checkbox" id="select-all" checked class="custom-check"
                                   onchange="toggleSelectAll(this.checked)">
                            <div>
                                <span class="font-700 text-gray-800 text-base font-bold">Cart Items</span>
                                <span class="ml-2 text-sm text-gray-500">(<span id="selected-count">{{ count($enrichedItems) }}</span> / {{ count($enrichedItems) }} selected)</span>
                            </div>
                        </label>
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="deleteSelected()"
                                    id="delete-selected-btn"
                                    class="btn-danger-text hidden">
                                <i class="fas fa-trash-alt mr-1.5"></i> Remove Selected
                            </button>
                            <div class="w-px h-4 bg-gray-200 hidden sm:block"></div>
                            <form action="{{ route('buyer.cart.clear') }}" method="POST"
                                  onsubmit="return confirm('Clear all items from your cart?')">
                                @csrf @method('POST')
                                <button type="submit" class="btn-danger-text">
                                    <i class="fas fa-trash mr-1.5"></i>
                                    <span class="hidden sm:inline">Clear Cart</span>
                                    <span class="sm:hidden">Clear</span>
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Items list --}}
                    <div class="divide-y divide-gray-50 px-4 sm:px-5">
                        @foreach($enrichedItems as $item)
                        @php
                            $variant          = $item['variant'] ?? null;
                            $color            = $item['color'] ?? null;
                            $size             = $item['size'] ?? null;
                            $variantId        = $item['variant_id'] ?? null;
                            $itemKey          = $item['listing_id'].'_'.($variantId ?? 'base').'_'.($color ?? 'nocolor').'_'.($size ?? 'nosize');
                            $variantAttrs     = $variant['attributes'] ?? [];
                            $displayColor     = $color ?? $variantAttrs['color'] ?? $variantAttrs['Color'] ?? null;
                            $displaySize      = $size  ?? $variantAttrs['size']  ?? $variantAttrs['Size']  ?? null;
                            $stock            = $item['stock']      ?? ($variant['stock']         ?? ($item['listing']['stock'] ?? 0));
                            $unitPrice        = $item['unit_price'] ?? ($variant['display_price'] ?? ($variant['price']        ?? 0));
                            $itemTotal        = $item['total']      ?? ($unitPrice * $item['quantity']);
                            $vendorName       = $item['vendor_name'] ?? ($item['listing']['vendor']['business_name'] ?? 'Vendor');
                        @endphp

                        <div class="cart-item py-5"
                             data-listing-id="{{ $item['listing_id'] }}"
                             data-item-key="{{ $itemKey }}"
                             data-unit-price="{{ $unitPrice }}"
                             data-tax="{{ $item['tax_amount'] ?? 0 }}">

                            <div class="flex items-start gap-3 sm:gap-4">

                                {{-- Checkbox --}}
                                <div class="pt-1 flex-shrink-0">
                                    <input type="checkbox" class="item-checkbox custom-check"
                                           data-item-key="{{ $itemKey }}"
                                           checked
                                           onchange="onItemSelectionChange()">
                                </div>

                                {{-- Product Image --}}
                                @if($item['image'] ?? null)
                                    <img src="{{ $item['image'] }}" alt="{{ $item['title'] }}" class="product-thumb">
                                @elseif(isset($item['listing']['images'][0]))
                                    <img src="{{ asset('storage/' . $item['listing']['images'][0]['path']) }}"
                                         alt="{{ $item['title'] }}" class="product-thumb">
                                @else
                                    <div class="product-thumb-placeholder">
                                        <i class="fas fa-image text-indigo-300 text-2xl"></i>
                                    </div>
                                @endif

                                {{-- Details --}}
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0">
                                            <h3 class="font-bold text-gray-900 text-sm sm:text-base leading-snug truncate sm:whitespace-normal">
                                                {{ $item['title'] }}
                                            </h3>
                                            <p class="text-xs text-gray-500 mt-0.5 flex items-center gap-1">
                                                <i class="fas fa-store text-indigo-400"></i>
                                                {{ $vendorName }}
                                            </p>
                                        </div>
                                        {{-- Remove --}}
                                        <button onclick="removeFromCart({{ $item['listing_id'] }}, '{{ $variantId }}', '{{ $color }}', '{{ $size }}')"
                                                class="btn-remove flex-shrink-0" title="Remove">
                                            <i class="fas fa-times text-xs"></i>
                                        </button>
                                    </div>

                                    {{-- Badges row --}}
                                    <div class="flex flex-wrap items-center gap-2 mt-2">
                                        @if(isset($item['origin']))
                                        <span class="badge-pill {{ $item['origin'] == 'imported' ? 'badge-imported' : 'badge-local' }}">
                                            <i class="fas fa-{{ $item['origin'] == 'imported' ? 'plane' : 'leaf' }}"></i>
                                            {{ ucfirst($item['origin']) }}
                                        </span>
                                        @endif
                                        @if($displayColor)
                                        <span class="badge-pill" style="background:#f3f4f6;color:#374151;">
                                            <i class="fas fa-circle" style="font-size:8px;color:{{ strtolower($displayColor) }};"></i>
                                            {{ $displayColor }}
                                        </span>
                                        @endif
                                        @if($displaySize)
                                        <span class="badge-pill" style="background:#f3f4f6;color:#374151;">
                                            <i class="fas fa-ruler-combined" style="font-size:9px;"></i>
                                            {{ $displaySize }}
                                        </span>
                                        @endif
                                        @if(isset($item['weight_kg']) && $item['weight_kg'])
                                        <span class="badge-pill" style="background:#faf5ff;color:#7c3aed;">
                                            <i class="fas fa-weight-hanging" style="font-size:9px;"></i>
                                            {{ $item['weight_kg'] }}kg
                                        </span>
                                        @endif
                                    </div>

                                    {{-- Qty + Price row --}}
                                    <div class="flex flex-wrap items-center justify-between gap-3 mt-3">
                                        <div class="flex items-center gap-3">
                                            <div class="qty-stepper">
                                                <button type="button"
                                                        onclick="updateQuantity({{ $item['listing_id'] }}, {{ $item['quantity'] - 1 }}, '{{ $variantId }}', '{{ $color }}', '{{ $size }}')"
                                                        {{ $item['quantity'] <= 1 ? 'disabled' : '' }}>
                                                    <i class="fas fa-minus"></i>
                                                </button>
                                                <input type="number"
                                                       id="qty-{{ $itemKey }}"
                                                       value="{{ $item['quantity'] }}"
                                                       min="1" max="{{ $stock }}"
                                                       onchange="updateQuantity({{ $item['listing_id'] }}, this.value, '{{ $variantId }}', '{{ $color }}', '{{ $size }}')">
                                                <button type="button"
                                                        onclick="updateQuantity({{ $item['listing_id'] }}, {{ $item['quantity'] + 1 }}, '{{ $variantId }}', '{{ $color }}', '{{ $size }}')"
                                                        {{ $item['quantity'] >= $stock ? 'disabled' : '' }}>
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                            @if($stock)
                                            <span class="text-xs text-gray-400">{{ $stock }} left</span>
                                            @endif
                                        </div>

                                        <div class="text-right">
                                            <div class="text-xs text-gray-400 mb-0.5">
                                                UGX {{ number_format($unitPrice, 0) }} × {{ $item['quantity'] }}
                                            </div>
                                            <div class="text-base sm:text-lg font-bold text-indigo-600 item-total"
                                                 id="total-{{ $itemKey }}">
                                                UGX {{ number_format($itemTotal, 0) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    {{-- Footer hint --}}
                    <div class="px-5 py-3 bg-gray-50 border-t border-gray-100 flex items-center justify-between text-xs text-gray-400">
                        <span><i class="fas fa-shield-alt text-green-400 mr-1"></i> Escrow-protected payments</span>
                        <span><i class="fas fa-undo text-indigo-400 mr-1"></i> 30-day returns</span>
                    </div>
                </div>

            </div><!-- /left -->

            {{-- ── Right: Order Summary ─────────────────────────── --}}
            <div class="lg:col-span-1 fade-slide-in" style="animation-delay:.08s">
                <div class="summary-card sticky top-5">

                    {{-- Summary header --}}
                    <div class="summary-card-header">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 bg-white/20 rounded-xl flex items-center justify-center">
                                <i class="fas fa-receipt text-white"></i>
                            </div>
                            <div>
                                <p class="font-bold text-lg text-white">Order Summary</p>
                                <p class="text-white/70 text-xs"><span id="summary-item-count">{{ count($enrichedItems) }}</span> item(s) selected</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-5 sm:p-6">

                        {{-- Breakdown --}}
                        <div class="space-y-1 mb-5">
                            <div class="summary-row">
                                <span class="flex items-center gap-2">
                                    <i class="fas fa-tag text-indigo-400 text-xs"></i>
                                    Subtotal
                                </span>
                                <span class="font-semibold text-gray-700" id="cart-subtotal">
                                    UGX {{ number_format($cart->subtotal, 0) }}
                                </span>
                            </div>
                            <div class="summary-row">
                                <span class="flex items-center gap-2">
                                    <i class="fas fa-truck text-indigo-400 text-xs"></i>
                                    Shipping
                                </span>
                                <span class="font-semibold text-gray-700" id="cart-shipping">
                                    @if($cart->shipping == 0)
                                    <span class="text-green-600 font-bold">Free</span>
                                    @else
                                    UGX {{ number_format($cart->shipping, 0) }}
                                    @endif
                                </span>
                            </div>
                            <div class="summary-row">
                                <span class="flex items-center gap-2">
                                    <i class="fas fa-percent text-indigo-400 text-xs"></i>
                                    Tax (18%)
                                </span>
                                <span class="font-semibold text-gray-700" id="cart-tax">
                                    UGX {{ number_format($cart->tax, 0) }}
                                </span>
                            </div>
                        </div>

                        {{-- Total --}}
                        <div class="bg-indigo-50 rounded-2xl p-4 mb-5 border border-indigo-100">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-700 font-semibold">Total</span>
                                <span class="text-xl font-extrabold text-indigo-600" id="cart-total">
                                    UGX {{ number_format($cart->total, 0) }}
                                </span>
                            </div>
                        </div>

                        {{-- Checkout --}}
                        <button type="button" onclick="proceedToCheckout()" id="checkout-btn" class="btn-checkout mb-3">
                            <i class="fas fa-lock text-sm"></i>
                            Proceed to Checkout
                        </button>

                        <p id="no-selection-msg" class="text-xs text-red-500 text-center mb-3 hidden">
                            <i class="fas fa-exclamation-circle mr-1"></i>
                            Please select at least one item
                        </p>

                        <a href="{{ route('marketplace.index') }}" class="btn-continue">
                            <i class="fas fa-arrow-left text-sm"></i>
                            Continue Shopping
                        </a>

                        {{-- Trust badges --}}
                        <div class="mt-5 pt-5 border-t border-gray-100 space-y-3">
                            <div class="trust-row">
                                <div class="w-8 h-8 bg-green-50 rounded-xl flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-shield-alt text-green-500 text-sm"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-800 text-xs">Escrow Protection</p>
                                    <p class="text-gray-500 text-xs">Money held until delivery confirmed</p>
                                </div>
                            </div>
                            <div class="trust-row">
                                <div class="w-8 h-8 bg-blue-50 rounded-xl flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-undo text-blue-500 text-sm"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-800 text-xs">30-Day Returns</p>
                                    <p class="text-gray-500 text-xs">Hassle-free return policy</p>
                                </div>
                            </div>
                            <div class="trust-row">
                                <div class="w-8 h-8 bg-purple-50 rounded-xl flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-headset text-purple-500 text-sm"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-800 text-xs">24/7 Support</p>
                                    <p class="text-gray-500 text-xs">We're here to help anytime</p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div><!-- /right -->

        </div><!-- /grid -->

        @else
        {{-- ── Empty Cart ───────────────────────────────────────── --}}
        <div class="cart-lift fade-slide-in">
            <div class="bg-white rounded-2xl shadow-sm p-10 sm:p-16 text-center max-w-md mx-auto">
                <div class="empty-cart-icon">
                    <i class="fas fa-shopping-cart text-indigo-400 text-3xl"></i>
                </div>
                <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-2">Your cart is empty</h2>
                <p class="text-gray-500 mb-8">Looks like you haven't added anything yet. Start shopping!</p>
                <a href="{{ route('marketplace.index') }}"
                   class="inline-flex items-center gap-2 px-7 py-3 rounded-2xl text-white font-bold shadow-lg hover:shadow-xl transition"
                   style="background:linear-gradient(135deg,#4f46e5,#7c3aed)">
                    <i class="fas fa-shopping-bag"></i> Explore Marketplace
                </a>
            </div>
        </div>
        @endif

    </div>
</div>

{{-- Toast container --}}
<div class="toast-wrap" id="toast-wrap"></div>

<script>
// ── Selection State ──────────────────────────────────────────
const allItemKeys  = new Set();
const selectedItems = new Set();

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.item-checkbox').forEach(cb => {
        const key = cb.dataset.itemKey;
        allItemKeys.add(key);
        if (cb.checked) selectedItems.add(key);
    });
    updateSelectionSummary();
});

function toggleSelectAll(checked) {
    document.querySelectorAll('.item-checkbox').forEach(cb => {
        cb.checked = checked;
        checked ? selectedItems.add(cb.dataset.itemKey) : selectedItems.delete(cb.dataset.itemKey);
    });
    updateSelectionSummary();
}

function onItemSelectionChange() {
    document.querySelectorAll('.item-checkbox').forEach(cb => {
        cb.checked ? selectedItems.add(cb.dataset.itemKey) : selectedItems.delete(cb.dataset.itemKey);
    });
    const selectAll = document.getElementById('select-all');
    const total = document.querySelectorAll('.item-checkbox').length;
    if (selectAll) {
        selectAll.checked       = selectedItems.size === total;
        selectAll.indeterminate = selectedItems.size > 0 && selectedItems.size < total;
    }
    updateSelectionSummary();
}

function updateSelectionSummary() {
    let subtotal = 0, taxTotal = 0, selectedCount = 0;

    document.querySelectorAll('.cart-item').forEach(el => {
        const key = el.dataset.itemKey;
        if (!selectedItems.has(key)) return;
        selectedCount++;
        const unitPrice = parseFloat(el.dataset.unitPrice) || 0;
        const tax       = parseFloat(el.dataset.tax) || 0;
        const qtyInput  = el.querySelector('input[type="number"]');
        const qty       = qtyInput ? parseInt(qtyInput.value) || 1 : 1;
        subtotal  += unitPrice * qty;
        taxTotal  += tax * qty;
    });

    const total = subtotal + taxTotal;
    const fmt = n => 'UGX ' + Math.round(n).toLocaleString('en-US');

    _setText('cart-subtotal', fmt(subtotal));
    _setText('cart-tax',      fmt(taxTotal));
    _setText('cart-total',    fmt(total));
    _setText('selected-count',   selectedCount);
    _setText('summary-item-count', selectedCount);

    // Shipping display
    const shipEl = document.getElementById('cart-shipping');
    if (shipEl) shipEl.innerHTML = subtotal === 0 ? '<span class="text-green-600 font-bold">Free</span>' : fmt(0);

    // Checkout button
    const btn   = document.getElementById('checkout-btn');
    const noSel = document.getElementById('no-selection-msg');
    if (btn) btn.disabled = selectedCount === 0;
    if (noSel) noSel.classList.toggle('hidden', selectedCount > 0);

    // Delete selected btn
    const delBtn = document.getElementById('delete-selected-btn');
    if (delBtn) delBtn.classList.toggle('hidden', !(selectedCount > 0 && selectedCount < allItemKeys.size));
}

function _setText(id, val) {
    const el = document.getElementById(id);
    if (el) el.textContent = val;
}

// ── Checkout ────────────────────────────────────────────────
function proceedToCheckout() {
    if (selectedItems.size === 0) {
        showToast('Please select at least one item', 'warning');
        return;
    }
    fetch('{{ route("buyer.cart.selection") }}', {
        method:'POST',
        headers:{ 'Content-Type':'application/json', 'X-CSRF-TOKEN':'{{ csrf_token() }}', 'Accept':'application/json' },
        body: JSON.stringify({ selected_keys: Array.from(selectedItems) })
    })
    .then(r => r.json())
    .then(d => {
        const url = '{{ route("buyer.orders.checkout") }}?selected=' + encodeURIComponent(JSON.stringify(Array.from(selectedItems)));
        window.location.href = url;
    })
    .catch(() => {
        window.location.href = '{{ route("buyer.orders.checkout") }}?selected=' + encodeURIComponent(JSON.stringify(Array.from(selectedItems)));
    });
}

// ── Delete Selected ─────────────────────────────────────────
function deleteSelected() {
    const keys = Array.from(selectedItems);
    if (!keys.length) return;
    if (!confirm(`Remove ${keys.length} selected item(s) from cart?`)) return;

    fetch('{{ route("buyer.cart.remove.selected") }}', {
        method:'POST',
        headers:{ 'Content-Type':'application/json', 'X-CSRF-TOKEN':'{{ csrf_token() }}', 'Accept':'application/json' },
        body: JSON.stringify({ selected_keys: keys })
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            keys.forEach(key => {
                const el = document.querySelector(`.cart-item[data-item-key="${key}"]`);
                if (el) { el.style.animation = 'none'; el.style.opacity = '0'; el.style.transform = 'scale(.97)'; el.style.transition = 'all .3s'; setTimeout(() => el.remove(), 300); }
                selectedItems.delete(key);
                allItemKeys.delete(key);
            });
            d.cart_count === 0 ? setTimeout(() => location.reload(), 600) : (onItemSelectionChange(), updateCartCount(d.cart_count));
            showToast(d.message, 'success');
        } else showToast(d.message, 'error');
    })
    .catch(() => showToast('Failed to delete items', 'error'));
}

// ── Update Quantity ──────────────────────────────────────────
function updateQuantity(listingId, quantity, variantId=null, color=null, size=null) {
    quantity = parseInt(quantity);
    if (quantity < 1) return;

    const itemKey  = `${listingId}_${variantId||'base'}_${color||'nocolor'}_${size||'nosize'}`;
    const qtyInput = document.getElementById(`qty-${itemKey}`);
    if (qtyInput) qtyInput.value = quantity;

    fetch(`/buyer/cart/update/${listingId}`, {
        method:'POST',
        headers:{ 'Content-Type':'application/json', 'X-CSRF-TOKEN':'{{ csrf_token() }}', 'Accept':'application/json' },
        body: JSON.stringify({ quantity, variant_id:variantId, color, size })
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            const totalEl = document.getElementById(`total-${itemKey}`);
            if (totalEl) {
                totalEl.textContent = 'UGX ' + Math.round(d.item_total).toLocaleString('en-US');
                totalEl.classList.add('shimmer');
                setTimeout(() => totalEl.classList.remove('shimmer'), 600);
            }
            updateSelectionSummary();
            showToast(d.message, 'success');
        } else showToast(d.message, 'error');
    })
    .catch(() => showToast('Failed to update quantity', 'error'));
}

// ── Remove from Cart ─────────────────────────────────────────
function removeFromCart(listingId, variantId=null, color=null, size=null) {
    if (!confirm('Remove this item from cart?')) return;

    fetch(`/buyer/cart/remove/${listingId}`, {
        method:'DELETE',
        headers:{ 'Content-Type':'application/json', 'X-CSRF-TOKEN':'{{ csrf_token() }}', 'Accept':'application/json' },
        body: JSON.stringify({ variant_id:variantId, color, size })
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            const itemKey = `${listingId}_${variantId||'base'}_${color||'nocolor'}_${size||'nosize'}`;
            const el = document.querySelector(`.cart-item[data-item-key="${itemKey}"]`);
            if (el) {
                el.style.transition = 'all .3s ease';
                el.style.opacity    = '0';
                el.style.transform  = 'translateX(20px)';
                setTimeout(() => el.remove(), 300);
            }
            selectedItems.delete(itemKey);
            allItemKeys.delete(itemKey);
            setTimeout(() => {
                if (d.cart_count === 0) { location.reload(); return; }
                onItemSelectionChange();
                updateCartCount(d.cart_count);
            }, 320);
            showToast(d.message, 'success');
        } else showToast(d.message, 'error');
    })
    .catch(() => showToast('Failed to remove item', 'error'));
}

// ── Cart Count in Navbar ─────────────────────────────────────
function updateCartCount(count) {
    document.querySelectorAll('.cart-count').forEach(el => {
        el.textContent = count;
        el.classList.toggle('hidden', count === 0);
    });
}

// ── Toast ────────────────────────────────────────────────────
function showToast(message, type='info') {
    const wrap  = document.getElementById('toast-wrap');
    const icons = { success:'fa-check-circle', error:'fa-times-circle', warning:'fa-exclamation-triangle', info:'fa-info-circle' };
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `<i class="fas ${icons[type]||icons.info}"></i><span>${message}</span>
        <button onclick="this.parentElement.remove()" style="margin-left:auto;background:none;border:none;cursor:pointer;opacity:.6;font-size:16px;">×</button>`;
    wrap.appendChild(toast);
    setTimeout(() => {
        toast.style.animation = 'toastOut .35s ease forwards';
        setTimeout(() => toast.remove(), 380);
    }, 4000);
}
</script>
@endsection
