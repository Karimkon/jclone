@extends('layouts.vendor')

@section('title', 'Edit Product - Vendor Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Edit Product</h1>
            <p class="text-gray-600">Update your product listing</p>
        </div>
        <div>
            <a href="{{ route('vendor.listings.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                <i class="fas fa-arrow-left mr-2"></i> Back to Products
            </a>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow">
        <form action="{{ route('vendor.listings.update', $listing) }}" method="POST" enctype="multipart/form-data" id="listingForm">
            @csrf
            @method('PUT')
            
            <!-- Basic Information -->
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Basic Information</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Product Title -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Product Title *
                        </label>
                        <input type="text" name="title" required
                               class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                               placeholder="e.g., Premium Wireless Headphones with Noise Cancellation"
                               value="{{ old('title', $listing->title) }}">
                        @error('title')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Category -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Category *
                        </label>
                        <select name="category_id" required
                                class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $listing->category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- SKU (Optional) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            SKU (Stock Keeping Unit)
                        </label>
                        <input type="text" name="sku"
                               class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                               placeholder="e.g., HW-2023-BLACK-001"
                               value="{{ old('sku', $listing->sku) }}">
                        <p class="mt-1 text-sm text-gray-500">Leave blank to auto-generate</p>
                        @error('sku')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Pricing & Stock -->
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Pricing & Stock</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Price -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Price ($) *
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-3 text-gray-500">$</span>
                            <input type="number" name="price" step="0.01" min="0" required
                                   class="w-full pl-8 border border-gray-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                   placeholder="0.00"
                                   value="{{ old('price', $listing->price) }}">
                        </div>
                        @error('price')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Stock -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Stock Quantity *
                        </label>
                        <input type="number" name="stock" min="0" required
                               class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                               placeholder="0"
                               value="{{ old('stock', $listing->stock) }}">
                        @error('stock')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Weight -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Weight (kg) *
                        </label>
                        <div class="relative">
                            <input type="number" name="weight_kg" step="0.01" min="0" required
                                   class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                   placeholder="0.00"
                                   value="{{ old('weight_kg', $listing->weight_kg) }}">
                            <span class="absolute right-3 top-3 text-gray-500">kg</span>
                        </div>
                        @error('weight_kg')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Product Details -->
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Product Details</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Origin -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Product Origin *
                        </label>
                        <select name="origin" required
                                class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">Select Origin</option>
                            <option value="local" {{ old('origin', $listing->origin) == 'local' ? 'selected' : '' }}>Local Product</option>
                            <option value="imported" {{ old('origin', $listing->origin) == 'imported' ? 'selected' : '' }}>Imported Product</option>
                        </select>
                        @error('origin')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Condition -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Condition *
                        </label>
                        <select name="condition" required
                                class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">Select Condition</option>
                            <option value="new" {{ old('condition', $listing->condition) == 'new' ? 'selected' : '' }}>New</option>
                            <option value="used" {{ old('condition', $listing->condition) == 'used' ? 'selected' : '' }}>Used</option>
                            <option value="refurbished" {{ old('condition', $listing->condition) == 'refurbished' ? 'selected' : '' }}>Refurbished</option>
                        </select>
                        @error('condition')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Product Attributes -->
                <div class="mt-6">
                    <h3 class="text-md font-medium text-gray-900 mb-3">Product Attributes (Optional)</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Brand</label>
                            <input type="text" name="attributes[brand]"
                                   class="w-full border border-gray-300 rounded-lg p-2"
                                   placeholder="e.g., Sony"
                                   value="{{ old('attributes.brand', $listing->attributes['brand'] ?? '') }}">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Model</label>
                            <input type="text" name="attributes[model]"
                                   class="w-full border border-gray-300 rounded-lg p-2"
                                   placeholder="e.g., WH-1000XM4"
                                   value="{{ old('attributes.model', $listing->attributes['model'] ?? '') }}">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Color</label>
                            <input type="text" name="attributes[color]"
                                   class="w-full border border-gray-300 rounded-lg p-2"
                                   placeholder="e.g., Black"
                                   value="{{ old('attributes.color', $listing->attributes['color'] ?? '') }}">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Size</label>
                            <input type="text" name="attributes[size]"
                                   class="w-full border border-gray-300 rounded-lg p-2"
                                   placeholder="e.g., Large, 10x20cm"
                                   value="{{ old('attributes.size', $listing->attributes['size'] ?? '') }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Product Description</h2>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Description *
                    </label>
                    <textarea name="description" rows="6" required
                              class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                              placeholder="Describe your product in detail. Include features, specifications, benefits, etc.">{{ old('description', $listing->description) }}</textarea>
                    <p class="mt-1 text-sm text-gray-500">Minimum 100 characters. Describe your product clearly to attract buyers.</p>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Images (Current Images) -->
            @if($listing->images->count() > 0)
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Current Images</h2>
                
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
                    @foreach($listing->images as $image)
                    <div class="relative">
                        <img src="{{ asset('storage/' . $image->path) }}" 
                             alt="Product Image"
                             class="w-full h-32 object-cover rounded-lg border border-gray-200">
                        <div class="absolute top-2 right-2">
                            <input type="checkbox" name="delete_images[]" value="{{ $image->id }}" 
                                   class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                        </div>
                        <div class="absolute top-2 left-2 bg-black bg-opacity-50 text-white text-xs px-2 py-1 rounded">
                            Image {{ $loop->iteration }}
                        </div>
                    </div>
                    @endforeach
                </div>
                <p class="text-sm text-gray-600">
                    <i class="fas fa-info-circle mr-1"></i>
                    Check images you want to delete. Upload new images below.
                </p>
            </div>
            @endif

            <!-- New Images -->
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900 mb-4">
                    {{ $listing->images->count() > 0 ? 'Add More Images' : 'Upload Images' }}
                </h2>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Upload New Images (Optional)
                    </label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-primary transition cursor-pointer"
                         onclick="document.getElementById('new_images').click()">
                        <i class="fas fa-cloud-upload-alt text-gray-400 text-4xl mb-3"></i>
                        <p class="text-lg font-medium text-gray-700 mb-1">Click to upload new images</p>
                        <p class="text-sm text-gray-500">Upload 1-5 images (JPG, PNG). Max 2MB per image.</p>
                        <input type="file" name="new_images[]" id="new_images" multiple accept="image/*" 
                               class="hidden" onchange="previewNewImages(this)">
                    </div>
                </div>

                <!-- New Image Preview -->
                <div id="newImagePreview" class="grid grid-cols-2 md:grid-cols-5 gap-4 hidden">
                    <h3 class="col-span-full text-md font-medium text-gray-900 mb-2">New Images:</h3>
                </div>
            </div>

            <!-- Active Status -->
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Listing Status</h2>
                
                <div class="flex items-center">
                    <input type="checkbox" name="is_active" id="is_active" value="1"
                           class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded"
                           {{ old('is_active', $listing->is_active) ? 'checked' : '' }}>
                    <label for="is_active" class="ml-2 block text-sm text-gray-900">
                        Make this product visible to buyers
                    </label>
                </div>
                <p class="mt-2 text-sm text-gray-500">
                    When checked, your product will be visible in the marketplace. When unchecked, it will be hidden.
                </p>
            </div>

            <!-- Submit -->
            <div class="p-6">
                <div class="flex justify-between items-center">
                    <div>
                        <a href="{{ route('vendor.listings.index') }}" 
                           class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 font-medium">
                            Cancel
                        </a>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <button type="submit" 
                                class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-indigo-700 font-medium">
                            <i class="fas fa-save mr-2"></i> Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    // Preview new images
    function previewNewImages(input) {
        const preview = document.getElementById('newImagePreview');
        preview.innerHTML = '';
        preview.classList.remove('hidden');
        
        if (input.files && input.files.length > 0) {
            // Add header
            const header = document.createElement('h3');
            header.className = 'col-span-full text-md font-medium text-gray-900 mb-2';
            header.textContent = `New Images (${input.files.length}/5):`;
            preview.appendChild(header);
            
            // Preview each image
            for (let i = 0; i < input.files.length && i < 5; i++) {
                const file = input.files[i];
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const col = document.createElement('div');
                    col.className = 'relative';
                    
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'w-full h-32 object-cover rounded-lg border border-gray-200';
                    
                    const badge = document.createElement('div');
                    badge.className = 'absolute top-2 right-2 bg-black bg-opacity-50 text-white text-xs px-2 py-1 rounded';
                    badge.textContent = 'New';
                    
                    col.appendChild(img);
                    col.appendChild(badge);
                    preview.appendChild(col);
                };
                
                reader.readAsDataURL(file);
            }
        }
    }
    
    // Form validation
    document.getElementById('listingForm').addEventListener('submit', function(e) {
        // Check minimum description length
        const description = document.querySelector('textarea[name="description"]');
        if (description.value.trim().length < 100) {
            e.preventDefault();
            alert('Description must be at least 100 characters long.');
            description.focus();
            return false;
        }
        
        return true;
    });
</script>
@endsection