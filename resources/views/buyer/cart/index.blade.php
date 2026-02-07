@extends('layouts.buyer')

@section('title', 'Shopping Cart - ' . config('app.name'))

@section('content')
<div class="container mx-auto px-2 sm:px-4 py-4 sm:py-8 pb-20 sm:pb-8">
    <h1 class="text-2xl sm:text-3xl font-bold mb-4 sm:mb-8">Shopping Cart</h1>

    @if(session('success'))
    <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6">
        <p class="text-green-700">{{ session('success') }}</p>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
        <p class="text-red-700">{{ session('error') }}</p>
    </div>
    @endif

    @if($cart && !empty($cart->items))
    @php
        $enrichedItems = $cart->getEnrichedItems();
    @endphp
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-8">
        <!-- Cart Items -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="p-3 sm:p-6 border-b bg-gray-50">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <label class="flex items-center cursor-pointer">
                                <input type="checkbox" id="select-all" checked
                                       class="h-5 w-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                                       onchange="toggleSelectAll(this.checked)">
                            </label>
                            <h2 class="text-base sm:text-xl font-bold">
                                Cart Items (<span id="selected-count">{{ count($enrichedItems) }}</span>/{{ count($enrichedItems) }})
                            </h2>
                        </div>
                        <div class="flex items-center gap-2 sm:gap-3">
                            <button type="button" onclick="deleteSelected()"
                                    id="delete-selected-btn"
                                    class="text-red-600 hover:text-red-800 text-xs sm:text-sm font-medium hidden">
                                <i class="fas fa-trash mr-1"></i> <span class="hidden sm:inline">Delete Selected</span><span class="sm:hidden">Delete</span>
                            </button>
                            <form action="{{ route('buyer.cart.clear') }}" method="POST" onsubmit="return confirm('Are you sure you want to clear your cart?')">
                                @csrf
                                @method('POST')
                                <button type="submit" class="text-red-600 hover:text-red-800 text-xs sm:text-sm font-medium">
                                    <i class="fas fa-trash mr-1"></i> <span class="hidden sm:inline">Clear Cart</span><span class="sm:hidden">Clear</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="divide-y">
                  @foreach($enrichedItems as $item)
@php
    // Properly extract variation data from the cart item structure
    $variant = $item['variant'] ?? null;
    $color = $item['color'] ?? null;
    $size = $item['size'] ?? null;
    $variantId = $item['variant_id'] ?? null;

    // Create unique item key
    $itemKey = $item['listing_id'] . '_' . ($variantId ?? 'base') . '_' . ($color ?? 'nocolor') . '_' . ($size ?? 'nosize');

    // Get correct attributes from variant or direct item
    $variantAttributes = $variant['attributes'] ?? [];
    $displayColor = $color ?? $variantAttributes['color'] ?? $variantAttributes['Color'] ?? null;
    $displaySize = $size ?? $variantAttributes['size'] ?? $variantAttributes['Size'] ?? null;

    // Add missing variables
    $stock = $item['stock'] ?? ($variant['stock'] ?? ($item['listing']['stock'] ?? 0));
    $unitPrice = $item['unit_price'] ?? ($variant['display_price'] ?? ($variant['price'] ?? 0));
@endphp

<div class="p-3 sm:p-6 cart-item" data-listing-id="{{ $item['listing_id'] }}" data-item-key="{{ $itemKey }}" data-unit-price="{{ $unitPrice }}" data-tax="{{ $item['tax_amount'] ?? 0 }}">
    <div class="flex flex-col sm:flex-row sm:space-x-4">
        <!-- Checkbox + Product Image & Remove (Mobile: side by side, Desktop: image only) -->
        <div class="flex items-start gap-3 sm:gap-3 mb-3 sm:mb-0">
            <label class="flex items-center cursor-pointer mt-1 sm:mt-7">
                <input type="checkbox" class="item-checkbox h-5 w-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                       data-item-key="{{ $itemKey }}"
                       checked
                       onchange="onItemSelectionChange()">
            </label>
            <div class="w-20 h-20 sm:w-24 sm:h-24 flex-shrink-0">
                @if($item['image'])
                <img src="{{ $item['image'] }}" alt="{{ $item['title'] }}"
                     class="w-full h-full object-cover rounded-lg">
                @elseif(isset($item['listing']['images'][0]))
                <img src="{{ asset('storage/' . $item['listing']['images'][0]['path']) }}"
                     alt="{{ $item['title'] }}"
                     class="w-full h-full object-cover rounded-lg">
                @else
                <div class="w-full h-full bg-gray-200 rounded-lg flex items-center justify-center">
                    <i class="fas fa-image text-gray-400 text-xl"></i>
                </div>
                @endif
            </div>
            <!-- Mobile: Title next to image -->
            <div class="flex-1 sm:hidden">
                <h3 class="font-bold text-sm leading-tight mb-1">{{ $item['title'] }}</h3>
                <p class="text-xs text-gray-600 mb-1">
                    <i class="fas fa-store mr-1"></i> {{ $item['vendor_name'] ?? ($item['listing']['vendor']['business_name'] ?? 'Vendor') }}
                </p>
                <div class="text-sm font-bold text-blue-600">UGX {{ number_format($unitPrice, 0) }}</div>
            </div>
            <!-- Mobile remove button -->
            <button onclick="removeFromCart({{ $item['listing_id'] }}, '{{ $variantId }}', '{{ $color }}', '{{ $size }}')"
                    class="sm:hidden text-red-500 p-1">
                <i class="fas fa-trash text-lg"></i>
            </button>
        </div>

        <!-- Product Details (Desktop) -->
        <div class="flex-1">
            <div class="hidden sm:flex justify-between">
                <div>
                    <h3 class="font-bold text-lg mb-1">{{ $item['title'] }}</h3>
                    <p class="text-sm text-gray-600 mb-2">
                        <i class="fas fa-store mr-1"></i> {{ $item['vendor_name'] ?? ($item['listing']['vendor']['business_name'] ?? 'Vendor') }}
                    </p>

                    <!-- Display Variations if they exist -->
                    @if($variant || $displayColor || $displaySize)
                    <div class="mb-2">
                        <div class="text-sm text-gray-700 space-y-1">
                            @if($displayColor)
                            <div class="flex items-center">
                                <span class="font-medium w-16">Color:</span>
                                <span class="px-2 py-1 bg-gray-100 rounded text-sm">{{ $displayColor }}</span>
                            </div>
                            @endif

                            @if($displaySize)
                            <div class="flex items-center">
                                <span class="font-medium w-16">Size:</span>
                                <span class="px-2 py-1 bg-gray-100 rounded text-sm">{{ $displaySize }}</span>
                            </div>
                            @endif

                            @if($variant && isset($variant['sku']))
                            <div class="flex items-center">
                                <span class="font-medium w-16">SKU:</span>
                                <span class="text-gray-600 text-sm">{{ $variant['sku'] }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <div class="flex items-center space-x-4 text-sm text-gray-500">
                        @if(isset($item['origin']))
                        <span class="px-2 py-1 bg-{{ $item['origin'] == 'imported' ? 'blue' : 'green' }}-100 text-{{ $item['origin'] == 'imported' ? 'blue' : 'green' }}-800 rounded text-xs">
                            <i class="fas fa-{{ $item['origin'] == 'imported' ? 'plane' : 'home' }} mr-1"></i>
                            {{ ucfirst($item['origin']) }}
                        </span>
                        @endif
                        @if(isset($item['weight_kg']))
                        <span><i class="fas fa-weight-hanging mr-1"></i> {{ $item['weight_kg'] }}kg</span>
                        @endif
                    </div>
                </div>

                <!-- Remove Button -->
                <button onclick="removeFromCart({{ $item['listing_id'] }}, '{{ $variantId }}', '{{ $color }}', '{{ $size }}')"
                        class="text-red-600 hover:text-red-800 h-8">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Mobile: Variations display -->
            @if(($variant || $displayColor || $displaySize) && ($displayColor || $displaySize))
            <div class="sm:hidden flex flex-wrap gap-2 mb-3">
                @if($displayColor)
                <span class="px-2 py-1 bg-gray-100 rounded text-xs"><strong>Color:</strong> {{ $displayColor }}</span>
                @endif
                @if($displaySize)
                <span class="px-2 py-1 bg-gray-100 rounded text-xs"><strong>Size:</strong> {{ $displaySize }}</span>
                @endif
            </div>
            @endif

            <!-- Quantity and Price -->
            <div class="mt-2 sm:mt-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-center justify-between sm:justify-start sm:space-x-3">
                    <span class="text-sm text-gray-600">Qty:</span>
                    <div class="flex items-center border border-gray-300 rounded-lg">
                        <button type="button"
                                onclick="updateQuantity({{ $item['listing_id'] }}, {{ $item['quantity'] - 1 }}, '{{ $variantId }}', '{{ $color }}', '{{ $size }}')"
                                class="px-2 sm:px-3 py-1 hover:bg-gray-100"
                                {{ $item['quantity'] <= 1 ? 'disabled' : '' }}>
                            <i class="fas fa-minus text-xs sm:text-sm"></i>
                        </button>
                        <input type="number"
                               id="qty-{{ $itemKey }}"
                               value="{{ $item['quantity'] }}"
                               min="1"
                               max="{{ $stock }}"
                               class="w-12 sm:w-16 text-center border-0 focus:ring-0 py-1 text-sm"
                               onchange="updateQuantity({{ $item['listing_id'] }}, this.value, '{{ $variantId }}', '{{ $color }}', '{{ $size }}')">
                        <button type="button"
                                onclick="updateQuantity({{ $item['listing_id'] }}, {{ $item['quantity'] + 1 }}, '{{ $variantId }}', '{{ $color }}', '{{ $size }}')"
                                class="px-2 sm:px-3 py-1 hover:bg-gray-100"
                                {{ $item['quantity'] >= $stock ? 'disabled' : '' }}>
                            <i class="fas fa-plus text-xs sm:text-sm"></i>
                        </button>
                    </div>
                    @if($stock)
                    <span class="text-xs text-gray-500 hidden sm:inline">({{ $stock }} available)</span>
                    @endif
                </div>

                <div class="flex items-center justify-between sm:block sm:text-right border-t sm:border-0 pt-2 sm:pt-0">
                    <div class="text-xs sm:text-sm text-gray-600">UGX {{ number_format($unitPrice, 0) }} each</div>
                    <div class="text-base sm:text-lg font-bold text-primary item-total" id="total-{{ $itemKey }}">
                        UGX {{ number_format($item['total'] ?? ($unitPrice * $item['quantity']), 0) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endforeach


                </div>
            </div>
        </div>

        <!-- Order Summary -->
<div class="lg:col-span-1">
    <div class="bg-white rounded-lg shadow p-4 sm:p-6 sticky top-4">
        <h2 class="text-lg sm:text-xl font-bold mb-3 sm:mb-4">Order Summary</h2>

        <div class="space-y-2 sm:space-y-3 mb-4 sm:mb-6 text-sm sm:text-base">
            <div class="flex justify-between text-gray-600">
                <span>Subtotal (<span id="summary-item-count">{{ count($enrichedItems) }}</span> items)</span>
                <span id="cart-subtotal">UGX {{ number_format($cart->subtotal, 0) }}</span>
            </div>
            <div class="flex justify-between text-gray-600">
                <span>Shipping</span>
                <span id="cart-shipping">UGX {{ number_format($cart->shipping, 0) }}</span>
            </div>
            <div class="flex justify-between text-gray-600">
                <span>Tax (18%)</span>
                <span id="cart-tax">UGX {{ number_format($cart->tax, 0) }}</span>
            </div>
            <div class="pt-2 sm:pt-3 border-t">
                <div class="flex justify-between font-bold text-base sm:text-lg">
                    <span>Total</span>
                    <span class="text-primary" id="cart-total">UGX {{ number_format($cart->total, 0) }}</span>
                </div>
            </div>
        </div>

        <button type="button" onclick="proceedToCheckout()" id="checkout-btn"
           class="block w-full text-white text-center py-2.5 sm:py-3 rounded-lg font-bold transition mb-2 sm:mb-3 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 text-sm sm:text-base cursor-pointer"
           style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%) !important;">
            <i class="fas fa-lock mr-2"></i> Proceed to Checkout
        </button>

        <p id="no-selection-msg" class="text-xs text-red-500 text-center mb-2 hidden">
            Please select at least one item to checkout
        </p>

        <a href="{{ route('marketplace.index') }}"
           class="block w-full text-center py-2.5 sm:py-3 border-2 border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition font-semibold text-sm sm:text-base">
            <i class="fas fa-shopping-bag mr-2"></i> Continue Shopping
        </a>

        <div class="mt-4 sm:mt-6 pt-4 sm:pt-6 border-t hidden sm:block">
            <div class="flex items-start space-x-2 text-sm text-gray-600">
                <i class="fas fa-shield-alt text-green-600 mt-1"></i>
                <div>
                    <p class="font-medium text-gray-800">Secure Checkout</p>
                    <p>Your payment is protected by escrow</p>
                </div>
            </div>
        </div>
    </div>
</div>

    @else
    <!-- Empty Cart -->
    <div class="bg-white rounded-lg shadow p-6 sm:p-12 text-center">
        <div class="mx-auto w-16 h-16 sm:w-24 sm:h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4 sm:mb-6">
            <i class="fas fa-shopping-cart text-gray-400 text-2xl sm:text-4xl"></i>
        </div>
        <h2 class="text-xl sm:text-2xl font-bold text-gray-800 mb-2">Your cart is empty</h2>
        <p class="text-sm sm:text-base text-gray-600 mb-4 sm:mb-6">Add some products to get started!</p>
        <a href="{{ route('marketplace.index') }}"
           class="inline-flex items-center px-4 sm:px-6 py-2 sm:py-3 bg-primary text-white rounded-lg font-semibold hover:bg-indigo-700 transition text-sm sm:text-base">
            <i class="fas fa-shopping-bag mr-2"></i> Start Shopping
        </a>
    </div>
    @endif
</div>

<script>
// ===== SELECTION STATE =====
const allItemKeys = new Set();
const selectedItems = new Set();

// Initialize: all items selected by default
document.addEventListener('DOMContentLoaded', function() {
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
        if (checked) {
            selectedItems.add(cb.dataset.itemKey);
        } else {
            selectedItems.delete(cb.dataset.itemKey);
        }
    });
    updateSelectionSummary();
}

function onItemSelectionChange() {
    document.querySelectorAll('.item-checkbox').forEach(cb => {
        if (cb.checked) {
            selectedItems.add(cb.dataset.itemKey);
        } else {
            selectedItems.delete(cb.dataset.itemKey);
        }
    });

    // Update select-all checkbox state
    const selectAll = document.getElementById('select-all');
    const total = document.querySelectorAll('.item-checkbox').length;
    if (selectAll) {
        selectAll.checked = selectedItems.size === total;
        selectAll.indeterminate = selectedItems.size > 0 && selectedItems.size < total;
    }

    updateSelectionSummary();
}

function updateSelectionSummary() {
    let subtotal = 0;
    let taxTotal = 0;
    let selectedCount = 0;

    document.querySelectorAll('.cart-item').forEach(itemEl => {
        const key = itemEl.dataset.itemKey;
        if (selectedItems.has(key)) {
            selectedCount++;
            const unitPrice = parseFloat(itemEl.dataset.unitPrice) || 0;
            const tax = parseFloat(itemEl.dataset.tax) || 0;
            const qtyInput = itemEl.querySelector('input[type="number"]');
            const qty = qtyInput ? parseInt(qtyInput.value) || 1 : 1;
            subtotal += unitPrice * qty;
            taxTotal += tax * qty;
        }
    });

    const total = subtotal + taxTotal;

    // Update summary display
    const subtotalEl = document.getElementById('cart-subtotal');
    const taxEl = document.getElementById('cart-tax');
    const totalEl = document.getElementById('cart-total');
    const countEl = document.getElementById('selected-count');
    const summaryCountEl = document.getElementById('summary-item-count');

    if (subtotalEl) subtotalEl.textContent = 'UGX ' + Math.round(subtotal).toLocaleString('en-US');
    if (taxEl) taxEl.textContent = 'UGX ' + Math.round(taxTotal).toLocaleString('en-US');
    if (totalEl) totalEl.textContent = 'UGX ' + Math.round(total).toLocaleString('en-US');
    if (countEl) countEl.textContent = selectedCount;
    if (summaryCountEl) summaryCountEl.textContent = selectedCount;

    // Toggle checkout button state
    const checkoutBtn = document.getElementById('checkout-btn');
    const noSelectionMsg = document.getElementById('no-selection-msg');
    const deleteBtn = document.getElementById('delete-selected-btn');

    if (selectedCount === 0) {
        if (checkoutBtn) {
            checkoutBtn.style.opacity = '0.5';
            checkoutBtn.style.pointerEvents = 'none';
        }
        if (noSelectionMsg) noSelectionMsg.classList.remove('hidden');
    } else {
        if (checkoutBtn) {
            checkoutBtn.style.opacity = '1';
            checkoutBtn.style.pointerEvents = 'auto';
        }
        if (noSelectionMsg) noSelectionMsg.classList.add('hidden');
    }

    // Show/hide delete selected button
    if (deleteBtn) {
        const allChecked = document.querySelectorAll('.item-checkbox').length;
        if (selectedCount > 0 && selectedCount < allChecked) {
            deleteBtn.classList.remove('hidden');
        } else {
            deleteBtn.classList.add('hidden');
        }
    }
}

function proceedToCheckout() {
    if (selectedItems.size === 0) {
        showToast('Please select at least one item to checkout', 'warning');
        return;
    }

    // Save selection to server, then redirect
    fetch('{{ route("buyer.cart.selection") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ selected_keys: Array.from(selectedItems) })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = '{{ route("buyer.orders.checkout") }}?selected=' + encodeURIComponent(JSON.stringify(Array.from(selectedItems)));
        } else {
            showToast('Failed to save selection', 'error');
        }
    })
    .catch(() => {
        // Fallback: just redirect with query params
        window.location.href = '{{ route("buyer.orders.checkout") }}?selected=' + encodeURIComponent(JSON.stringify(Array.from(selectedItems)));
    });
}

function deleteSelected() {
    const keys = Array.from(selectedItems);
    if (keys.length === 0) return;
    if (!confirm(`Remove ${keys.length} selected item(s) from cart?`)) return;

    fetch('{{ route("buyer.cart.remove.selected") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ selected_keys: keys })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove items from DOM
            keys.forEach(key => {
                const el = document.querySelector(`.cart-item[data-item-key="${key}"]`);
                if (el) el.remove();
                selectedItems.delete(key);
                allItemKeys.delete(key);
            });

            if (data.cart_count === 0) {
                setTimeout(() => location.reload(), 500);
            } else {
                onItemSelectionChange();
                updateCartCount(data.cart_count);
            }
            showToast(data.message, 'success');
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Failed to delete items', 'error');
    });
}

// ===== EXISTING FUNCTIONS (updated) =====

// Update quantity with variation support
function updateQuantity(listingId, quantity, variantId = null, color = null, size = null) {
    quantity = parseInt(quantity);
    if (quantity < 1) return;

    const itemKey = `${listingId}_${variantId || 'base'}_${color || 'nocolor'}_${size || 'nosize'}`;
    const qtyInput = document.getElementById(`qty-${itemKey}`);
    if (qtyInput) {
        qtyInput.value = quantity;
    }

    fetch(`/buyer/cart/update/${listingId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            quantity: quantity,
            variant_id: variantId,
            color: color,
            size: size
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update item total
            const itemTotalElement = document.getElementById(`total-${itemKey}`);
            if (itemTotalElement) {
                itemTotalElement.textContent = 'UGX ' + Math.round(data.item_total).toLocaleString('en-US');
            }

            // Update selection-aware summary
            updateSelectionSummary();

            // Update stock availability
            if (data.stock !== undefined && qtyInput) {
                const plusButton = qtyInput.nextElementSibling;
                if (plusButton) {
                    plusButton.disabled = quantity >= data.stock;
                }
                const minusButton = qtyInput.previousElementSibling;
                if (minusButton) {
                    minusButton.disabled = quantity <= 1;
                }
            }

            showToast(data.message, 'success');
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Failed to update quantity', 'error');
    });
}

// Remove from cart with variation support
function removeFromCart(listingId, variantId = null, color = null, size = null) {
    if (!confirm('Remove this item from cart?')) return;

    const data = {
        variant_id: variantId,
        color: color,
        size: size
    };

    fetch(`/buyer/cart/remove/${listingId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove item from DOM
            const itemKey = `${listingId}_${variantId || 'base'}_${color || 'nocolor'}_${size || 'nosize'}`;
            const item = document.querySelector(`.cart-item[data-item-key="${itemKey}"]`);
            if (item) {
                item.remove();
            }

            // Remove from selection tracking
            selectedItems.delete(itemKey);
            allItemKeys.delete(itemKey);

            // Update selection summary
            onItemSelectionChange();

            // Update cart count in navbar
            if (data.cart_count !== undefined) {
                updateCartCount(data.cart_count);
            }

            // If cart is now empty, reload page
            if (data.cart_count === 0) {
                setTimeout(() => {
                    location.reload();
                }, 1000);
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
        if (count > 0) {
            element.classList.remove('hidden');
        } else {
            element.classList.add('hidden');
        }
    });
}

// Show toast notification
function showToast(message, type = 'info') {
    // Remove existing toasts
    const existingToasts = document.querySelectorAll('.custom-toast');
    existingToasts.forEach(toast => toast.remove());

    const toast = document.createElement('div');
    toast.className = 'custom-toast fixed top-4 right-4 z-50 transform transition-all duration-300';

    const typeStyles = {
        success: 'bg-green-500 text-white',
        error: 'bg-red-500 text-white',
        warning: 'bg-yellow-500 text-white',
        info: 'bg-blue-500 text-white'
    };

    const icons = {
        success: 'fa-check-circle',
        error: 'fa-times-circle',
        warning: 'fa-exclamation-triangle',
        info: 'fa-info-circle'
    };

    toast.className += ` ${typeStyles[type] || typeStyles.info} px-6 py-4 rounded-lg shadow-lg flex items-center gap-3 animate-slideIn`;

    toast.innerHTML = `
        <i class="fas ${icons[type] || icons.info}"></i>
        <span class="font-medium">${message}</span>
        <button class="ml-2 hover:opacity-80" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;

    document.body.appendChild(toast);

    setTimeout(() => {
        if (toast.parentElement) {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.remove();
                }
            }, 300);
        }
    }, 4000);
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl + A to select all inputs in the focused quantity field
    if ((e.ctrlKey || e.metaKey) && e.key === 'a') {
        const activeElement = document.activeElement;
        if (activeElement && activeElement.type === 'number' && activeElement.id.startsWith('qty-')) {
            e.preventDefault();
            activeElement.select();
        }
    }

    // Arrow keys for quantity adjustment
    if (e.key === 'ArrowUp' || e.key === 'ArrowDown') {
        const activeElement = document.activeElement;
        if (activeElement && activeElement.type === 'number' && activeElement.id.startsWith('qty-')) {
            e.preventDefault();

            // Find the parent cart item to get variation details
            const cartItem = activeElement.closest('.cart-item');
            if (cartItem) {
                const listingId = cartItem.dataset.listingId;
                const itemKey = cartItem.dataset.itemKey;
                const parts = itemKey.split('_');
                const variantId = parts[1] !== 'base' ? parts[1] : null;
                const color = parts[2] || null;
                const size = parts[3] || null;

                let currentQty = parseInt(activeElement.value);
                let newQty = e.key === 'ArrowUp' ? currentQty + 1 : currentQty - 1;

                if (newQty >= 1) {
                    updateQuantity(listingId, newQty, variantId, color, size);
                }
            }
        }
    }
});

// Add some CSS for animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(100%);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .animate-slideIn {
        animation: slideIn 0.3s ease-out;
    }

    .cart-item {
        transition: all 0.3s ease;
    }

    .cart-item:hover {
        background-color: #f9fafb;
    }

    .custom-toast {
        min-width: 300px;
    }
`;
document.head.appendChild(style);
</script>
@endsection
