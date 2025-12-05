@extends('layouts.vendor')

@section('title', 'Add New Product - Vendor Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Add New Product</h1>
            <p class="text-gray-600">Create a new listing for your products</p>
        </div>
        <div>
            <a href="{{ route('vendor.listings.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                <i class="fas fa-arrow-left mr-2"></i> Back to Products
            </a>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow">
        <form action="{{ route('vendor.listings.store') }}" method="POST" enctype="multipart/form-data" id="listingForm">
            @csrf
            
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
                               value="{{ old('title') }}">
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
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
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
                               value="{{ old('sku') }}">
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
                                   value="{{ old('price') }}">
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
                               value="{{ old('stock') }}">
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
                                   value="{{ old('weight_kg') }}">
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
                            <option value="local" {{ old('origin') == 'local' ? 'selected' : '' }}>Local Product</option>
                            <option value="imported" {{ old('origin') == 'imported' ? 'selected' : '' }}>Imported Product</option>
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
                            <option value="new" {{ old('condition') == 'new' ? 'selected' : '' }}>New</option>
                            <option value="used" {{ old('condition') == 'used' ? 'selected' : '' }}>Used</option>
                            <option value="refurbished" {{ old('condition') == 'refurbished' ? 'selected' : '' }}>Refurbished</option>
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
                                   value="{{ old('attributes.brand') }}">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Model</label>
                            <input type="text" name="attributes[model]"
                                   class="w-full border border-gray-300 rounded-lg p-2"
                                   placeholder="e.g., WH-1000XM4"
                                   value="{{ old('attributes.model') }}">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Color</label>
                            <input type="text" name="attributes[color]"
                                   class="w-full border border-gray-300 rounded-lg p-2"
                                   placeholder="e.g., Black"
                                   value="{{ old('attributes.color') }}">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Size</label>
                            <input type="text" name="attributes[size]"
                                   class="w-full border border-gray-300 rounded-lg p-2"
                                   placeholder="e.g., Large, 10x20cm"
                                   value="{{ old('attributes.size') }}">
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
                              placeholder="Describe your product in detail. Include features, specifications, benefits, etc.">{{ old('description') }}</textarea>
                    <p class="mt-1 text-sm text-gray-500">Minimum 100 characters. Describe your product clearly to attract buyers.</p>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Images -->
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Product Images</h2>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Upload Images *
                    </label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-primary transition cursor-pointer"
                         onclick="document.getElementById('images').click()">
                        <i class="fas fa-cloud-upload-alt text-gray-400 text-4xl mb-3"></i>
                        <p class="text-lg font-medium text-gray-700 mb-1">Click to upload images</p>
                        <p class="text-sm text-gray-500">Upload 1-5 images (JPG, PNG). First image will be the main product image.</p>
                        <input type="file" name="images[]" id="images" multiple accept="image/*" 
                               class="hidden" onchange="previewImages(this)">
                    </div>
                    <p class="mt-2 text-sm text-gray-500">
                        <i class="fas fa-info-circle mr-1"></i>
                        Recommended size: 800x800px. Max 2MB per image.
                    </p>
                    @error('images')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Image Preview -->
                <div id="imagePreview" class="grid grid-cols-2 md:grid-cols-5 gap-4 hidden">
                    <h3 class="col-span-full text-md font-medium text-gray-900 mb-2">Selected Images:</h3>
                </div>
            </div>

            <!-- Submit -->
            <div class="p-6">
                <div class="flex justify-end space-x-4">
                    <button type="reset" 
                            class="px-6 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 font-medium">
                        Reset Form
                    </button>
                    <button type="submit" 
                            class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-indigo-700 font-medium">
                        <i class="fas fa-plus-circle mr-2"></i> Create Product
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    // Image preview
    function previewImages(input) {
        const preview = document.getElementById('imagePreview');
        preview.innerHTML = '';
        preview.classList.remove('hidden');
        
        if (input.files && input.files.length > 0) {
            // Add header
            const header = document.createElement('h3');
            header.className = 'col-span-full text-md font-medium text-gray-900 mb-2';
            header.textContent = `Selected Images (${input.files.length}/5):`;
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
                    badge.textContent = i === 0 ? 'Main' : i + 1;
                    
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
        
        // Check images
        const imagesInput = document.getElementById('images');
        if (imagesInput.files.length === 0) {
            e.preventDefault();
            alert('Please upload at least one product image.');
            imagesInput.closest('.border-dashed').classList.add('border-red-500');
            setTimeout(() => {
                imagesInput.closest('.border-dashed').classList.remove('border-red-500');
            }, 2000);
            return false;
        }
        
        return true;
    });
</script>
@endsection