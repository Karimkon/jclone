@extends('layouts.vendor')

@section('title', 'Edit Service - Vendor Dashboard')
@section('page_title', 'Edit Service')

@section('content')
<div class="max-w-4xl">
    <div class="mb-6">
        <a href="{{ route('vendor.services.index') }}" class="text-indigo-600 hover:underline">
            <i class="fas fa-arrow-left mr-2"></i> Back to My Services
        </a>
    </div>
    
    <form action="{{ route('vendor.services.update', $service->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')
        
        <!-- Basic Info -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Service Details</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Service Title *</label>
                    <input type="text" name="title" value="{{ old('title', $service->title) }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                           placeholder="e.g. Professional House Cleaning Service">
                    @error('title')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                    <select name="service_category_id" id="serviceCategorySelect" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option value="">Select Category</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('service_category_id', $service->service_category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    @error('service_category_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Duration (Optional)</label>
                    <input type="text" name="duration" value="{{ old('duration', $service->duration) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                           placeholder="e.g. 2-3 hours, 1 day, etc.">
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
                    <textarea name="description" rows="5" required
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                              placeholder="Describe your service in detail. What's included? What makes it special?">{{ old('description', $service->description) }}</textarea>
                    @error('description')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
        
        <!-- Pricing -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Pricing</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pricing Type *</label>
                    <select name="pricing_type" id="pricingType" required 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                            onchange="togglePriceFields()">
                        <option value="fixed" {{ old('pricing_type', $service->pricing_type) == 'fixed' ? 'selected' : '' }}>Fixed Price</option>
                        <option value="hourly" {{ old('pricing_type', $service->pricing_type) == 'hourly' ? 'selected' : '' }}>Per Hour</option>
                        <option value="starting_from" {{ old('pricing_type', $service->pricing_type) == 'starting_from' ? 'selected' : '' }}>Starting From</option>
                        <option value="negotiable" {{ old('pricing_type', $service->pricing_type) == 'negotiable' ? 'selected' : '' }}>Negotiable</option>
                        <option value="free_quote" {{ old('pricing_type', $service->pricing_type) == 'free_quote' ? 'selected' : '' }}>Free Quote / Contact for Price</option>
                    </select>
                </div>
                
                <div id="priceField">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Price (UGX) *</label>
                    <input type="number" name="price" value="{{ old('price', $service->price) }}" min="0"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                           placeholder="e.g. 50000">
                    @error('price')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div id="priceMaxField">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Maximum Price (Optional)</label>
                    <input type="number" name="price_max" value="{{ old('price_max', $service->price_max) }}" min="0"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                           placeholder="For price range">
                </div>
            </div>
        </div>
        
        <!-- Location -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Service Location</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">City *</label>
                    <input type="text" name="city" value="{{ old('city', $service->city ?? 'Kampala') }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Area / Location</label>
                    <input type="text" name="location" value="{{ old('location', $service->location) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                           placeholder="e.g. Kololo, Ntinda, Wandegeya">
                </div>
                
                <div class="md:col-span-2">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_mobile" value="1" {{ old('is_mobile', $service->is_mobile) ? 'checked' : '' }}
                               class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                        <span class="text-sm text-gray-700">
                            <strong>Mobile Service</strong> - I can travel to the customer's location
                        </span>
                    </label>
                </div>
            </div>
        </div>
        
        <!-- Features -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">What's Included (Features)</h2>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Features (one per line)</label>
                @php
                    $featuresText = is_array($service->features) ? implode("\n", $service->features) : $service->features;
                @endphp
                <textarea name="features" rows="4"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                          placeholder="Professional equipment&#10;Eco-friendly products&#10;Satisfaction guaranteed&#10;Free consultation">{{ old('features', $featuresText) }}</textarea>
                <p class="text-xs text-gray-500 mt-1">Enter each feature on a new line. These will appear as bullet points.</p>
            </div>
        </div>
        
        <!-- Images -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Service Images</h2>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Current Images</label>
                <div class="grid grid-cols-5 gap-2">
                    @if($service->images && count($service->images) > 0)
                        @foreach($service->images as $img)
                        <div class="relative aspect-square rounded-lg overflow-hidden bg-gray-100 border">
                            <img src="{{ asset('storage/' . $img) }}" class="w-full h-full object-cover">
                        </div>
                        @endforeach
                    @else
                        <p class="text-gray-500 text-sm">No images uploaded.</p>
                    @endif
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Upload New Images (Will replace existing)</label>
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-purple-500 transition cursor-pointer" onclick="document.getElementById('images').click()">
                    <input type="file" name="images[]" id="images" multiple accept="image/*" class="hidden" onchange="previewImages(this)">
                    <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-2"></i>
                    <p class="text-gray-600">Click to upload or drag and drop</p>
                    <p class="text-sm text-gray-400 mt-1">PNG, JPG, JPEG up to 2MB each</p>
                </div>
                
                <div id="imagePreview" class="grid grid-cols-5 gap-2 mt-4"></div>
            </div>
        </div>
        
        <!-- Contact -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Contact Information</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Contact Phone</label>
                    <input type="text" name="contact_phone" value="{{ old('contact_phone', $service->meta['contact_phone'] ?? auth()->user()->vendorProfile->phone ?? '') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Contact Email</label>
                    <input type="email" name="contact_email" value="{{ old('contact_email', $service->meta['contact_email'] ?? auth()->user()->email) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">WhatsApp Number</label>
                    <input type="text" name="whatsapp" value="{{ old('whatsapp', $service->meta['whatsapp'] ?? '') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                           placeholder="e.g. 256700123456">
                </div>
            </div>
        </div>
        
        <!-- Submit -->
        <div class="flex items-center justify-end gap-4">
            <a href="{{ route('vendor.services.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                <i class="fas fa-save mr-2"></i> Update Service
            </button>
        </div>
    </form>
</div>

<script>
function togglePriceFields() {
    const type = document.getElementById('pricingType').value;
    const priceField = document.getElementById('priceField');
    const priceMaxField = document.getElementById('priceMaxField');
    
    if (type === 'free_quote' || type === 'negotiable') {
        priceField.style.display = 'none';
        priceMaxField.style.display = 'none';
    } else {
        priceField.style.display = 'block';
        priceMaxField.style.display = type === 'fixed' ? 'none' : 'block';
    }
}

function previewImages(input) {
    const preview = document.getElementById('imagePreview');
    preview.innerHTML = '';
    
    if (input.files) {
        const files = Array.from(input.files).slice(0, 5); // Max 5 images
        
        files.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'relative aspect-square rounded-lg overflow-hidden bg-gray-100';
                div.innerHTML = `
                    <img src="${e.target.result}" class="w-full h-full object-cover">
                    ${index === 0 ? '<span class="absolute bottom-1 left-1 bg-purple-600 text-white text-xs px-2 py-0.5 rounded">Primary</span>' : ''}
                `;
                preview.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    }
}

// Initialize on load
document.addEventListener('DOMContentLoaded', function() {
    togglePriceFields();
    
    // Initialize Select2 for service category
    if (typeof jQuery !== 'undefined' && $.fn.select2) {
        $('#serviceCategorySelect').select2({
            placeholder: "Type to search for a category...",
            allowClear: true,
            width: '100%',
            dropdownParent: $('form'),
            minimumResultsForSearch: 0
        });
    }
});
</script>
@endsection
