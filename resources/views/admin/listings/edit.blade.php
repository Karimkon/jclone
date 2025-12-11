@extends('layouts.admin')

@section('title', 'Edit Product: ' . $listing->title)
@section('page-title', 'Edit Product: ' . Str::limit($listing->title, 50))

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <form action="{{ route('admin.listings.update', $listing) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column: Basic Info -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Basic Information -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-gray-800">Basic Information</h3>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
                            <input type="text" name="title" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                   value="{{ old('title', $listing->title) }}">
                            @error('title')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
                            <textarea name="description" rows="5" required
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">{{ old('description', $listing->description) }}</textarea>
                            @error('description')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                                <select name="category_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ (old('category_id') ?? $listing->category_id) == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                        @if($category->children)
                                            @foreach($category->children as $subcategory)
                                                <option value="{{ $subcategory->id }}" {{ (old('category_id') ?? $listing->category_id) == $subcategory->id ? 'selected' : '' }}>
                                                    &nbsp;&nbsp;&nbsp;{{ $subcategory->name }}
                                                </option>
                                            @endforeach
                                        @endif
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Vendor *</label>
                                <select name="vendor_profile_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <option value="">Select Vendor</option>
                                    @foreach($vendors as $vendor)
                                        <option value="{{ $vendor->id }}" {{ (old('vendor_profile_id') ?? $listing->vendor_profile_id) == $vendor->id ? 'selected' : '' }}>
                                            {{ $vendor->business_name }} ({{ $vendor->user->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('vendor_profile_id')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Pricing & Inventory -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-gray-800">Pricing & Inventory</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Price * (UGX)</label>
                                <input type="number" name="price" step="0.01" min="0" required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                       value="{{ old('price', $listing->price) }}">
                                @error('price')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Compare Price (UGX)</label>
                                <input type="number" name="compare_at_price" step="0.01" min="0"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                       value="{{ old('compare_at_price', $listing->compare_at_price) }}">
                                @error('compare_at_price')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Stock *</label>
                                <input type="number" name="stock" min="0" required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                       value="{{ old('stock', $listing->stock) }}">
                                @error('stock')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Weight (kg) *</label>
                                <input type="number" name="weight_kg" step="0.01" min="0" required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                       value="{{ old('weight_kg', $listing->weight_kg) }}">
                                @error('weight_kg')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Weight (lbs)</label>
                                <input type="number" name="weight_lbs" step="0.01" min="0"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                       value="{{ old('weight_lbs', $listing->weight_lbs) }}">
                                @error('weight_lbs')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">SKU</label>
                                <input type="text" name="sku"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                       value="{{ old('sku', $listing->sku) }}">
                                @error('sku')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Product Details -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-gray-800">Product Details</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Origin *</label>
                                <select name="origin" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <option value="local" {{ (old('origin') ?? $listing->origin) == 'local' ? 'selected' : '' }}>Local</option>
                                    <option value="imported" {{ (old('origin') ?? $listing->origin) == 'imported' ? 'selected' : '' }}>Imported</option>
                                </select>
                                @error('origin')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Condition *</label>
                                <select name="condition" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <option value="new" {{ (old('condition') ?? $listing->condition) == 'new' ? 'selected' : '' }}>New</option>
                                    <option value="used" {{ (old('condition') ?? $listing->condition) == 'used' ? 'selected' : '' }}>Used</option>
                                    <option value="refurbished" {{ (old('condition') ?? $listing->condition) == 'refurbished' ? 'selected' : '' }}>Refurbished</option>
                                </select>
                                @error('condition')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Attributes -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-gray-800">Attributes</h3>
                        
                        @php
                            $attributes = is_array($listing->attributes) ? $listing->attributes : json_decode($listing->attributes, true) ?? [];
                        @endphp
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Brand</label>
                                <input type="text" name="attributes[brand]"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                       value="{{ old('attributes.brand', $attributes['brand'] ?? '') }}">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Model</label>
                                <input type="text" name="attributes[model]"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                       value="{{ old('attributes.model', $attributes['model'] ?? '') }}">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Color</label>
                                <input type="text" name="attributes[color]"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                       value="{{ old('attributes.color', $attributes['color'] ?? '') }}">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Size</label>
                                <input type="text" name="attributes[size]"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                       value="{{ old('attributes.size', $attributes['size'] ?? '') }}">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Material</label>
                                <input type="text" name="attributes[material]"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                       value="{{ old('attributes.material', $attributes['material'] ?? '') }}">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Warranty</label>
                                <input type="text" name="attributes[warranty]"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                       value="{{ old('attributes.warranty', $attributes['warranty'] ?? '') }}">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Dimensions</label>
                                <input type="text" name="attributes[dimensions]"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                       value="{{ old('attributes.dimensions', $attributes['dimensions'] ?? '') }}">
                            </div>
                        </div>
                    </div>

                    <!-- Existing Images -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-gray-800">Current Images</h3>
                        
                        @if($listing->images->count() > 0)
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            @foreach($listing->images as $image)
                            <div class="relative border border-gray-200 rounded-lg overflow-hidden">
                                <img src="{{ Storage::url($image->path) }}" alt="Product image" class="w-full h-48 object-cover">
                                <div class="p-2 bg-white">
                                    <div class="flex items-center space-x-2 mb-2">
                                        <label class="text-xs text-gray-600">Order:</label>
                                        <input type="number" name="image_order[{{ $image->id }}]" min="0" 
                                               class="w-16 px-2 py-1 border border-gray-300 rounded text-sm"
                                               value="{{ $image->order }}">
                                    </div>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="delete_images[]" value="{{ $image->id }}" 
                                               class="h-4 w-4 text-red-600 rounded border-gray-300">
                                        <span class="ml-2 text-xs text-red-600">Delete</span>
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <p class="text-gray-500 text-center py-4">No images uploaded yet</p>
                        @endif
                    </div>

                    <!-- New Images -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-gray-800">Add New Images</h3>
                        <p class="text-sm text-gray-600 mb-4">Add more images (max 8 total images, 5MB each)</p>
                        
                        <div id="imageUploadArea" class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                            <input type="file" name="new_images[]" id="new_images" multiple accept="image/*" 
                                   class="hidden" onchange="previewNewImages()">
                            
                            <div class="mb-4">
                                <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-2"></i>
                                <p class="text-gray-600">Drag & drop images or click to browse</p>
                            </div>
                            
                            <button type="button" onclick="document.getElementById('new_images').click()"
                                    class="bg-primary text-white px-6 py-2 rounded-lg font-medium hover:bg-indigo-700 transition">
                                <i class="fas fa-plus mr-2"></i> Add More Images
                            </button>
                        </div>
                        
                        <div id="newImagePreview" class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                            <!-- New images will be previewed here -->
                        </div>
                    </div>
                </div>

                <!-- Right Column: Settings -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- Status & Visibility -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Status & Visibility</h3>
                        
                        <div class="space-y-4">
                            <div class="flex items-center">
                                <input type="checkbox" name="is_active" id="is_active" value="1" 
                                       class="h-5 w-5 text-primary rounded focus:ring-primary border-gray-300"
                                       {{ (old('is_active') ?? $listing->is_active) ? 'checked' : '' }}>
                                <label for="is_active" class="ml-3 text-sm font-medium text-gray-700">
                                    Active Product
                                </label>
                            </div>
                            
                            <div class="flex items-center">
                                <input type="checkbox" name="is_featured" id="is_featured" value="1"
                                       class="h-5 w-5 text-primary rounded focus:ring-primary border-gray-300"
                                       {{ (old('is_featured') ?? $listing->is_featured) ? 'checked' : '' }}>
                                <label for="is_featured" class="ml-3 text-sm font-medium text-gray-700">
                                    Featured Product
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Product Information -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Product Information</h3>
                        
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Vendor:</span>
                                <span class="font-medium">{{ $listing->vendor->business_name ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Created:</span>
                                <span class="font-medium">{{ $listing->created_at->format('M d, Y') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Last Updated:</span>
                                <span class="font-medium">{{ $listing->updated_at->format('M d, Y') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Status:</span>
                                <span class="font-medium {{ $listing->is_active ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $listing->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Admin Notes -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Admin Notes</h3>
                        <textarea name="admin_notes" rows="4"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">{{ old('admin_notes', $listing->admin_notes) }}</textarea>
                    </div>

                    <!-- Action Buttons -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <div class="space-y-3">
                            <button type="submit"
                                    class="w-full bg-primary text-white py-3 rounded-lg font-semibold hover:bg-indigo-700 transition flex items-center justify-center">
                                <i class="fas fa-save mr-2"></i> Update Product
                            </button>
                            
                            <a href="{{ route('admin.listings.index') }}"
                               class="block w-full bg-gray-200 text-gray-800 py-3 rounded-lg font-semibold hover:bg-gray-300 transition text-center">
                                Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function previewNewImages() {
    const preview = document.getElementById('newImagePreview');
    const files = document.getElementById('new_images').files;
    
    preview.innerHTML = '';
    
    if (files.length === 0) {
        return;
    }
    
    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const div = document.createElement('div');
            div.className = 'relative border border-gray-200 rounded-lg overflow-hidden';
            div.innerHTML = `
                <img src="${e.target.result}" alt="Preview" class="w-full h-48 object-cover">
                <div class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center cursor-pointer" onclick="removeNewImage(${i})">
                    <i class="fas fa-times text-xs"></i>
                </div>
                <div class="p-2 bg-white">
                    <p class="text-xs text-gray-600 truncate">${file.name}</p>
                </div>
            `;
            preview.appendChild(div);
        };
        
        reader.readAsDataURL(file);
    }
}

function removeNewImage(index) {
    const input = document.getElementById('new_images');
    const dt = new DataTransfer();
    
    for (let i = 0; i < input.files.length; i++) {
        if (i !== index) {
            dt.items.add(input.files[i]);
        }
    }
    
    input.files = dt.files;
    previewNewImages();
}

// Handle drag and drop for new images
const uploadArea = document.getElementById('imageUploadArea');
['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
    uploadArea.addEventListener(eventName, preventDefaults, false);
});

function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
}

['dragenter', 'dragover'].forEach(eventName => {
    uploadArea.addEventListener(eventName, highlight, false);
});

['dragleave', 'drop'].forEach(eventName => {
    uploadArea.addEventListener(eventName, unhighlight, false);
});

function highlight() {
    uploadArea.classList.add('border-primary', 'bg-blue-50');
}

function unhighlight() {
    uploadArea.classList.remove('border-primary', 'bg-blue-50');
}

uploadArea.addEventListener('drop', handleDrop, false);

function handleDrop(e) {
    const dt = e.dataTransfer;
    const files = dt.files;
    const input = document.getElementById('new_images');
    
    // Merge existing files with dropped files
    const existingFiles = input.files ? Array.from(input.files) : [];
    const allFiles = existingFiles.concat(Array.from(files));
    
    // Create new DataTransfer object
    const newDt = new DataTransfer();
    allFiles.forEach(file => newDt.items.add(file));
    
    // Update input files
    input.files = newDt.files;
    previewNewImages();
}
</script>
@endsection