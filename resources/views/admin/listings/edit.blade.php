@extends('layouts.admin')

@section('title', 'Edit Product: ' . $listing->title)
@section('page-title', 'Edit Product: ' . Str::limit($listing->title, 50))

@section('content')
<div class="max-w-6xl mx-auto pb-24">

    <!-- Flash Messages -->
    @if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg flex items-center">
        <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex items-center">
        <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
    </div>
    @endif
    @if($errors->any())
    <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
        <div class="flex items-center mb-2"><i class="fas fa-exclamation-triangle mr-2"></i> <strong>Please fix the following errors:</strong></div>
        <ul class="list-disc list-inside text-sm">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('admin.listings.update', $listing) }}" method="POST" enctype="multipart/form-data" id="editForm">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Basic Information -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-100">
                        <h3 class="text-base font-semibold text-gray-800 flex items-center">
                            <i class="fas fa-info-circle text-primary mr-2"></i> Basic Information
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
                            <input type="text" name="title" required
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition"
                                   value="{{ old('title', $listing->title) }}">
                            @error('title') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
                            <textarea name="description" rows="5" required
                                      class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition">{{ old('description', $listing->description) }}</textarea>
                            @error('description') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                                <select name="category_id" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition">
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ (old('category_id') ?? $listing->category_id) == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                        @if($category->children)
                                            @foreach($category->children as $subcategory)
                                                <option value="{{ $subcategory->id }}" {{ (old('category_id') ?? $listing->category_id) == $subcategory->id ? 'selected' : '' }}>
                                                    &nbsp;&nbsp;↳ {{ $subcategory->name }}
                                                </option>
                                            @endforeach
                                        @endif
                                    @endforeach
                                </select>
                                @error('category_id') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Vendor *</label>
                                <select name="vendor_profile_id" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition">
                                    <option value="">Select Vendor</option>
                                    @foreach($vendors as $vendor)
                                        <option value="{{ $vendor->id }}" {{ (old('vendor_profile_id') ?? $listing->vendor_profile_id) == $vendor->id ? 'selected' : '' }}>
                                            {{ $vendor->business_name }} ({{ $vendor->user->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('vendor_profile_id') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pricing & Inventory -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-100">
                        <h3 class="text-base font-semibold text-gray-800 flex items-center">
                            <i class="fas fa-tags text-green-600 mr-2"></i> Pricing & Inventory
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Price * (UGX)</label>
                                <input type="number" name="price" step="0.01" min="0" required
                                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition"
                                       value="{{ old('price', $listing->price) }}">
                                @error('price') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Compare Price (UGX)</label>
                                <input type="number" name="compare_at_price" step="0.01" min="0"
                                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition"
                                       value="{{ old('compare_at_price', $listing->compare_at_price) }}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Stock *</label>
                                <input type="number" name="stock" min="0" required
                                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition"
                                       value="{{ old('stock', $listing->stock) }}">
                                @error('stock') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Weight (kg) *</label>
                                <input type="number" name="weight_kg" step="0.01" min="0" required
                                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition"
                                       value="{{ old('weight_kg', $listing->weight_kg) }}">
                                @error('weight_kg') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Weight (lbs)</label>
                                <input type="number" name="weight_lbs" step="0.01" min="0"
                                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition"
                                       value="{{ old('weight_lbs', $listing->weight_lbs) }}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">SKU</label>
                                <input type="text" name="sku"
                                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition"
                                       value="{{ old('sku', $listing->sku) }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product Details & Attributes -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-100">
                        <h3 class="text-base font-semibold text-gray-800 flex items-center">
                            <i class="fas fa-list-alt text-purple-600 mr-2"></i> Product Details & Attributes
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Origin *</label>
                                <select name="origin" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition">
                                    <option value="local" {{ (old('origin') ?? $listing->origin) == 'local' ? 'selected' : '' }}>Local</option>
                                    <option value="imported" {{ (old('origin') ?? $listing->origin) == 'imported' ? 'selected' : '' }}>Imported</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Condition *</label>
                                <select name="condition" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition">
                                    <option value="new" {{ (old('condition') ?? $listing->condition) == 'new' ? 'selected' : '' }}>New</option>
                                    <option value="used" {{ (old('condition') ?? $listing->condition) == 'used' ? 'selected' : '' }}>Used</option>
                                    <option value="refurbished" {{ (old('condition') ?? $listing->condition) == 'refurbished' ? 'selected' : '' }}>Refurbished</option>
                                </select>
                            </div>
                        </div>

                        @php
                            $attributes = is_array($listing->attributes) ? $listing->attributes : json_decode($listing->attributes, true) ?? [];
                        @endphp

                        <div class="border-t border-gray-100 pt-4 mt-2">
                            <p class="text-sm font-medium text-gray-500 mb-3">Attributes</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Brand</label>
                                    <input type="text" name="attributes[brand]"
                                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition"
                                           value="{{ old('attributes.brand', $attributes['brand'] ?? '') }}">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Model</label>
                                    <input type="text" name="attributes[model]"
                                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition"
                                           value="{{ old('attributes.model', $attributes['model'] ?? '') }}">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Color</label>
                                    <input type="text" name="attributes[color]"
                                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition"
                                           value="{{ old('attributes.color', $attributes['color'] ?? '') }}">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Size</label>
                                    <input type="text" name="attributes[size]"
                                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition"
                                           value="{{ old('attributes.size', $attributes['size'] ?? '') }}">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Material</label>
                                    <input type="text" name="attributes[material]"
                                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition"
                                           value="{{ old('attributes.material', $attributes['material'] ?? '') }}">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Warranty</label>
                                    <input type="text" name="attributes[warranty]"
                                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition"
                                           value="{{ old('attributes.warranty', $attributes['warranty'] ?? '') }}">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Dimensions</label>
                                    <input type="text" name="attributes[dimensions]"
                                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition"
                                           value="{{ old('attributes.dimensions', $attributes['dimensions'] ?? '') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Images -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-100">
                        <h3 class="text-base font-semibold text-gray-800 flex items-center">
                            <i class="fas fa-images text-amber-600 mr-2"></i> Product Images
                        </h3>
                    </div>
                    <div class="p-6 space-y-6">
                        <!-- Current Images -->
                        @if($listing->images->count() > 0)
                        <div>
                            <p class="text-sm font-medium text-gray-700 mb-3">Current Images ({{ $listing->images->count() }})</p>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                @foreach($listing->images as $image)
                                <div class="relative group border border-gray-200 rounded-lg overflow-hidden">
                                    <img src="{{ Storage::url($image->path) }}" alt="Product image" class="w-full h-40 object-cover">
                                    <div class="p-2.5 bg-white space-y-2">
                                        <div class="flex items-center gap-2">
                                            <label class="text-xs text-gray-500">Order:</label>
                                            <input type="number" name="image_order[{{ $image->id }}]" min="0"
                                                   class="w-16 px-2 py-1 border border-gray-300 rounded text-sm focus:ring-1 focus:ring-primary"
                                                   value="{{ $image->order }}">
                                        </div>
                                        <label class="flex items-center cursor-pointer">
                                            <input type="checkbox" name="delete_images[]" value="{{ $image->id }}"
                                                   class="h-4 w-4 text-red-600 rounded border-gray-300 focus:ring-red-500">
                                            <span class="ml-2 text-xs text-red-600 font-medium">Delete this image</span>
                                        </label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @else
                        <div class="text-center py-6 bg-gray-50 rounded-lg">
                            <i class="fas fa-image text-gray-300 text-3xl mb-2"></i>
                            <p class="text-gray-500 text-sm">No images uploaded yet</p>
                        </div>
                        @endif

                        <!-- Upload New Images -->
                        <div class="border-t border-gray-100 pt-6">
                            <p class="text-sm font-medium text-gray-700 mb-1">Add New Images</p>
                            <p class="text-xs text-gray-500 mb-3">Max 8 total images, 5MB each. JPG, PNG, WebP accepted.</p>

                            <div id="imageUploadArea" class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center cursor-pointer hover:border-primary hover:bg-indigo-50 transition"
                                 onclick="document.getElementById('new_images').click()">
                                <input type="file" name="new_images[]" id="new_images" multiple accept="image/*"
                                       class="hidden" onchange="previewNewImages()">
                                <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                                <p class="text-sm text-gray-600">Drag & drop images here or <span class="text-primary font-medium">click to browse</span></p>
                            </div>

                            <div id="newImagePreview" class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Sidebar (Sticky) -->
            <div class="lg:col-span-1">
                <div class="lg:sticky lg:top-4 space-y-6">

                    <!-- Status & Visibility -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="bg-gray-50 px-5 py-3 border-b border-gray-100">
                            <h3 class="text-sm font-semibold text-gray-800">Status & Visibility</h3>
                        </div>
                        <div class="p-5 space-y-3">
                            <label class="flex items-center cursor-pointer">
                                <input type="checkbox" name="is_active" id="is_active" value="1"
                                       class="h-5 w-5 text-primary rounded focus:ring-primary border-gray-300"
                                       {{ (old('is_active') ?? $listing->is_active) ? 'checked' : '' }}>
                                <span class="ml-3 text-sm font-medium text-gray-700">Active Product</span>
                            </label>
                            <label class="flex items-center cursor-pointer">
                                <input type="checkbox" name="is_featured" id="is_featured" value="1"
                                       class="h-5 w-5 text-primary rounded focus:ring-primary border-gray-300"
                                       {{ (old('is_featured') ?? $listing->is_featured) ? 'checked' : '' }}>
                                <span class="ml-3 text-sm font-medium text-gray-700">Featured Product</span>
                            </label>
                        </div>
                    </div>

                    <!-- Product Info -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="bg-gray-50 px-5 py-3 border-b border-gray-100">
                            <h3 class="text-sm font-semibold text-gray-800">Product Information</h3>
                        </div>
                        <div class="p-5 space-y-2.5 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Vendor:</span>
                                <span class="font-medium text-gray-800">{{ $listing->vendor->business_name ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Created:</span>
                                <span class="font-medium">{{ $listing->created_at->format('M d, Y') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Last Updated:</span>
                                <span class="font-medium">{{ $listing->updated_at->format('M d, Y') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Status:</span>
                                <span class="font-semibold {{ $listing->is_active ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $listing->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Admin Notes -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="bg-gray-50 px-5 py-3 border-b border-gray-100">
                            <h3 class="text-sm font-semibold text-gray-800">Admin Notes</h3>
                        </div>
                        <div class="p-5">
                            <textarea name="admin_notes" rows="3" placeholder="Internal notes about this product..."
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition text-sm">{{ old('admin_notes', $listing->admin_notes) }}</textarea>
                        </div>
                    </div>

                    <!-- Sidebar Action Buttons -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 space-y-3">
                        <button type="submit" form="editForm"
                                class="w-full bg-primary text-white py-3 rounded-lg font-semibold hover:bg-indigo-700 transition flex items-center justify-center shadow-sm">
                            <i class="fas fa-save mr-2"></i> Update Product
                        </button>
                        <a href="{{ route('admin.listings.index') }}"
                           class="block w-full bg-gray-100 text-gray-700 py-3 rounded-lg font-medium hover:bg-gray-200 transition text-center">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Fixed Bottom Bar (always visible) -->
<div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-lg z-40 lg:left-64">
    <div class="max-w-6xl mx-auto px-6 py-3 flex items-center justify-between">
        <div class="text-sm text-gray-500">
            Editing: <strong class="text-gray-800">{{ Str::limit($listing->title, 40) }}</strong>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.listings.index') }}" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200 transition text-sm">
                Cancel
            </a>
            <button type="submit" form="editForm"
                    class="px-6 py-2.5 bg-primary text-white rounded-lg font-semibold hover:bg-indigo-700 transition flex items-center text-sm shadow-sm">
                <i class="fas fa-save mr-2"></i> Update Product
            </button>
        </div>
    </div>
</div>

<script>
function previewNewImages() {
    const preview = document.getElementById('newImagePreview');
    const files = document.getElementById('new_images').files;
    preview.innerHTML = '';
    if (files.length === 0) return;

    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        const reader = new FileReader();
        reader.onload = function(e) {
            const div = document.createElement('div');
            div.className = 'relative border border-gray-200 rounded-lg overflow-hidden';
            div.innerHTML = `
                <img src="${e.target.result}" alt="Preview" class="w-full h-40 object-cover">
                <div class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center cursor-pointer hover:bg-red-600 transition" onclick="removeNewImage(${i})">
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
        if (i !== index) dt.items.add(input.files[i]);
    }
    input.files = dt.files;
    previewNewImages();
}

// Drag and drop
const uploadArea = document.getElementById('imageUploadArea');
['dragenter', 'dragover', 'dragleave', 'drop'].forEach(e => {
    uploadArea.addEventListener(e, function(ev) { ev.preventDefault(); ev.stopPropagation(); }, false);
});
['dragenter', 'dragover'].forEach(e => {
    uploadArea.addEventListener(e, () => uploadArea.classList.add('border-primary', 'bg-indigo-50'), false);
});
['dragleave', 'drop'].forEach(e => {
    uploadArea.addEventListener(e, () => uploadArea.classList.remove('border-primary', 'bg-indigo-50'), false);
});
uploadArea.addEventListener('drop', function(e) {
    const files = e.dataTransfer.files;
    const input = document.getElementById('new_images');
    const newDt = new DataTransfer();
    if (input.files) Array.from(input.files).forEach(f => newDt.items.add(f));
    Array.from(files).forEach(f => newDt.items.add(f));
    input.files = newDt.files;
    previewNewImages();
}, false);
</script>
@endsection
