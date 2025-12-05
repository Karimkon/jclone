@extends('layouts.vendor')

@section('title', 'Create Promotion - Vendor Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Create New Promotion</h1>
            <p class="text-gray-600">Boost your product visibility and sales</p>
        </div>
        <div>
            <a href="{{ route('vendor.promotions.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                <i class="fas fa-arrow-left mr-2"></i> Back to Promotions
            </a>
        </div>
    </div>

    <!-- Promotion Types -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        @foreach($promotionTypes as $type => $details)
        <div class="promotion-type-card" data-type="{{ $type }}">
            <div class="text-center p-6 border-2 border-gray-200 rounded-xl hover:border-primary transition cursor-pointer">
                <div class="w-12 h-12 mx-auto mb-4 rounded-lg flex items-center justify-center
                    @if($type == 'featured') bg-blue-100 text-blue-600
                    @elseif($type == 'spotlight') bg-purple-100 text-purple-600
                    @elseif($type == 'discount') bg-green-100 text-green-600
                    @else bg-yellow-100 text-yellow-600 @endif">
                    <i class="fas 
                        @if($type == 'featured') fa-star
                        @elseif($type == 'spotlight') fa-bullseye
                        @elseif($type == 'discount') fa-percentage
                        @else fa-bolt @endif text-xl"></i>
                </div>
                <h3 class="font-bold text-gray-900 mb-2">{{ $details['name'] }}</h3>
                <p class="text-sm text-gray-600 mb-3">{{ $details['description'] }}</p>
                <div class="text-lg font-bold text-primary">
                    ${{ number_format($details['price'], 2) }}
                    <span class="text-sm text-gray-500">/{{ $details['duration'] }} days</span>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow">
        <form action="{{ route('vendor.promotions.store') }}" method="POST" id="promotionForm">
            @csrf
            
            <!-- Step 1: Select Product -->
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900 mb-4">1. Select Product to Promote</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="productList">
                    @foreach($listings as $listing)
                    <div class="product-card border-2 border-gray-200 rounded-xl p-4 hover:border-primary transition cursor-pointer" 
                         data-product-id="{{ $listing->id }}">
                        <div class="flex items-start">
                            @if($listing->images->first())
                            <img src="{{ asset('storage/' . $listing->images->first()->path) }}" 
                                 alt="{{ $listing->title }}" 
                                 class="w-16 h-16 object-cover rounded-lg mr-4">
                            @else
                            <div class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center mr-4">
                                <i class="fas fa-image text-gray-400"></i>
                            </div>
                            @endif
                            
                            <div class="flex-1">
                                <h4 class="font-medium text-gray-800 line-clamp-2">{{ $listing->title }}</h4>
                                <p class="text-lg font-bold text-primary">${{ number_format($listing->price, 2) }}</p>
                                <div class="text-sm text-gray-600">
                                    Stock: {{ $listing->stock }}
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                
                @if($listings->isEmpty())
                <div class="text-center py-8">
                    <i class="fas fa-box-open text-gray-400 text-3xl mb-3"></i>
                    <p class="text-gray-600">No active listings found</p>
                    <p class="text-sm text-gray-500 mt-1">Create a product listing first</p>
                    <a href="{{ route('vendor.listings.create') }}" 
                       class="mt-4 inline-block bg-primary text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                        <i class="fas fa-plus mr-2"></i>Create Product
                    </a>
                </div>
                @endif
            </div>

            <!-- Step 2: Promotion Details -->
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900 mb-4">2. Promotion Details</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Promotion Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Promotion Type *
                        </label>
                        <select name="type" required 
                                class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">Select Promotion Type</option>
                            @foreach($promotionTypes as $type => $details)
                            <option value="{{ $type }}">{{ $details['name'] }} - ${{ $details['price'] }}/{{ $details['duration'] }} days</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Listing (hidden, will be filled by JS) -->
                    <input type="hidden" name="listing_id" id="selectedListingId" required>

                    <!-- Title -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Promotion Title *
                        </label>
                        <input type="text" name="title" required
                               class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-primary focus:border-transparent"
                               placeholder="e.g., Summer Flash Sale - 50% Off"
                               value="{{ old('title') }}">
                    </div>

                    <!-- Description -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Description (Optional)
                        </label>
                        <textarea name="description" rows="3"
                                  class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-primary focus:border-transparent"
                                  placeholder="Describe your promotion...">{{ old('description') }}</textarea>
                    </div>

                    <!-- Duration -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Duration (Days) *
                        </label>
                        <input type="number" name="duration" min="1" max="30" required
                               class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-primary focus:border-transparent"
                               placeholder="e.g., 7"
                               value="{{ old('duration', 7) }}">
                    </div>

                    <!-- Start Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Start Date *
                        </label>
                        <input type="date" name="starts_at" required
                               class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-primary focus:border-transparent"
                               value="{{ old('starts_at', date('Y-m-d')) }}"
                               min="{{ date('Y-m-d') }}">
                    </div>
                </div>

                <!-- Discount Fields (shown for discount/flash sale) -->
                <div id="discountFields" class="hidden mt-6">
                    <h3 class="text-md font-medium text-gray-900 mb-3">Discount Settings</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Discount Amount *
                            </label>
                            <input type="number" name="discount_amount" min="0" step="0.01"
                                   class="w-full border border-gray-300 rounded-lg p-3"
                                   placeholder="e.g., 10">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Discount Type *
                            </label>
                            <select name="discount_type" class="w-full border border-gray-300 rounded-lg p-3">
                                <option value="percentage">Percentage (%)</option>
                                <option value="fixed">Fixed Amount ($)</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 3: Summary -->
            <div class="p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">3. Summary & Cost</h2>
                
                <div class="bg-gray-50 rounded-xl p-6 mb-6">
                    <div class="space-y-4" id="promotionSummary">
                        <div class="text-center py-8">
                            <i class="fas fa-chart-line text-gray-400 text-3xl mb-3"></i>
                            <p class="text-gray-600">Select a product and promotion type to see details</p>
                        </div>
                    </div>
                    
                    <div class="border-t pt-4 mt-4">
                        <div class="flex justify-between text-lg font-bold">
                            <span>Total Cost:</span>
                            <span id="totalCost" class="text-primary">$0.00</span>
                        </div>
                    </div>
                </div>

                <!-- Terms -->
                <div class="mb-6">
                    <label class="flex items-start">
                        <input type="checkbox" name="terms" required 
                               class="mt-1 mr-3 h-5 w-5 text-primary rounded focus:ring-primary">
                        <span class="text-gray-700 text-sm">
                            I agree to the promotion terms and conditions. I understand that:
                            <ul class="list-disc pl-5 mt-2 space-y-1">
                                <li>Promotion fees are non-refundable</li>
                                <li>Promotions require admin approval</li>
                                <li>Payment will be deducted from my vendor balance</li>
                                <li>I can cancel a promotion up to 24 hours before start</li>
                            </ul>
                        </span>
                    </label>
                </div>

                <!-- Submit -->
                <div class="flex justify-end space-x-4">
                    <button type="reset" 
                            class="px-6 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 font-medium">
                        Reset Form
                    </button>
                    <button type="submit" 
                            class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-indigo-700 font-medium">
                        <i class="fas fa-check-circle mr-2"></i> Create Promotion
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    // Product selection
    document.querySelectorAll('.product-card').forEach(card => {
        card.addEventListener('click', function() {
            // Remove selection from all cards
            document.querySelectorAll('.product-card').forEach(c => {
                c.classList.remove('border-primary', 'bg-blue-50');
                c.classList.add('border-gray-200');
            });
            
            // Add selection to clicked card
            this.classList.remove('border-gray-200');
            this.classList.add('border-primary', 'bg-blue-50');
            
            // Set selected product ID
            document.getElementById('selectedListingId').value = this.dataset.productId;
            
            updatePromotionSummary();
        });
    });

    // Promotion type selection
    document.querySelectorAll('.promotion-type-card').forEach(card => {
        card.addEventListener('click', function() {
            const type = this.dataset.type;
            
            // Remove selection from all cards
            document.querySelectorAll('.promotion-type-card > div').forEach(c => {
                c.classList.remove('border-primary');
                c.classList.add('border-gray-200');
            });
            
            // Add selection to clicked card
            this.querySelector('div').classList.remove('border-gray-200');
            this.querySelector('div').classList.add('border-primary');
            
            // Set selected type
            document.querySelector('select[name="type"]').value = type;
            
            // Show/hide discount fields
            const discountFields = document.getElementById('discountFields');
            if (type === 'discount' || type === 'flash_sale') {
                discountFields.classList.remove('hidden');
                document.querySelectorAll('#discountFields input, #discountFields select').forEach(el => {
                    el.required = true;
                });
            } else {
                discountFields.classList.add('hidden');
                document.querySelectorAll('#discountFields input, #discountFields select').forEach(el => {
                    el.required = false;
                });
            }
            
            updatePromotionSummary();
        });
    });

    // Update promotion summary
    function updatePromotionSummary() {
        const productCard = document.querySelector('.product-card.border-primary');
        const typeSelect = document.querySelector('select[name="type"]');
        const durationInput = document.querySelector('input[name="duration"]');
        const startsAtInput = document.querySelector('input[name="starts_at"]');
        const summaryDiv = document.getElementById('promotionSummary');
        const totalCostSpan = document.getElementById('totalCost');
        
        if (!productCard || !typeSelect.value || !durationInput.value) {
            return;
        }

        // Calculate end date
        const startDate = new Date(startsAtInput.value);
        const endDate = new Date(startDate);
        endDate.setDate(startDate.getDate() + parseInt(durationInput.value));
        
        // Calculate cost (simplified)
        const basePrices = {
            'featured': 10,
            'spotlight': 25,
            'discount': 5,
            'flash_sale': 15
        };
        
        const basePrice = basePrices[typeSelect.value] || 5;
        const weeks = Math.ceil(parseInt(durationInput.value) / 7);
        let totalCost = basePrice * weeks;
        
        // Update summary
        summaryDiv.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-600">Promotion Type</p>
                    <p class="font-medium text-gray-900">${typeSelect.options[typeSelect.selectedIndex].text.split(' - ')[0]}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Duration</p>
                    <p class="font-medium text-gray-900">${durationInput.value} days</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Start Date</p>
                    <p class="font-medium text-gray-900">${formatDate(startDate)}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">End Date</p>
                    <p class="font-medium text-gray-900">${formatDate(endDate)}</p>
                </div>
            </div>
        `;
        
        totalCostSpan.textContent = `$${totalCost.toFixed(2)}`;
    }

    function formatDate(date) {
        return date.toLocaleDateString('en-US', { 
            weekday: 'short', 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric' 
        });
    }

    // Listen for changes
    document.querySelector('select[name="type"]').addEventListener('change', function() {
        updatePromotionSummary();
    });
    
    document.querySelector('input[name="duration"]').addEventListener('input', function() {
        updatePromotionSummary();
    });
    
    document.querySelector('input[name="starts_at"]').addEventListener('change', function() {
        updatePromotionSummary();
    });

    // Form validation
    document.getElementById('promotionForm').addEventListener('submit', function(e) {
        if (!document.getElementById('selectedListingId').value) {
            e.preventDefault();
            alert('Please select a product to promote.');
            return false;
        }
        
        if (!document.querySelector('select[name="type"]').value) {
            e.preventDefault();
            alert('Please select a promotion type.');
            return false;
        }
        
        return true;
    });
</script>

<style>
    .product-card.border-primary {
        border-color: #4f46e5 !important;
    }
    
    .promotion-type-card > div.border-primary {
        border-color: #4f46e5 !important;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }
</style>
@endsection