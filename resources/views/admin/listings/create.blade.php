@extends('layouts.admin')

@section('title', 'Add New Product')
@section('page-title', 'Add New Product')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <form action="{{ route('admin.listings.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="space-y-6">
                <!-- Basic Information -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-800 border-b pb-2">Basic Information</h3>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
                        <input type="text" name="title" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                               placeholder="Product title" value="{{ old('title') }}">
                        @error('title')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
                        <textarea name="description" rows="5" required
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">{{ old('description') }}</textarea>
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
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                    @if($category->children)
                                        @foreach($category->children as $subcategory)
                                            <option value="{{ $subcategory->id }}" {{ old('category_id') == $subcategory->id ? 'selected' : '' }}>
                                                &nbsp;&nbsp;↳ {{ $subcategory->name }}
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
                                    <option value="{{ $vendor->id }}" {{ old('vendor_profile_id') == $vendor->id ? 'selected' : '' }}>
                                        {{ $vendor->business_name }}
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
                    <h3 class="text-lg font-semibold text-gray-800 border-b pb-2">Pricing & Inventory</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Price * (UGX)</label>
                            <input type="number" name="price" step="0.01" min="0" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                   value="{{ old('price') }}">
                            @error('price')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Stock *</label>
                            <input type="number" name="stock" min="0" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                   value="{{ old('stock', 0) }}">
                            @error('stock')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Weight (kg) *</label>
                            <input type="number" name="weight_kg" step="0.01" min="0" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                   value="{{ old('weight_kg') }}">
                            @error('weight_kg')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">SKU</label>
                            <input type="text" name="sku"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                   placeholder="Auto-generated if empty" value="{{ old('sku') }}">
                            @error('sku')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Product Details -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-800 border-b pb-2">Product Details</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Origin *</label>
                            <select name="origin" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="local" {{ old('origin') == 'local' ? 'selected' : '' }}>Local</option>
                                <option value="imported" {{ old('origin') == 'imported' ? 'selected' : '' }}>Imported</option>
                            </select>
                            @error('origin')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Condition *</label>
                            <select name="condition" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="new" {{ old('condition') == 'new' ? 'selected' : '' }}>New</option>
                                <option value="used" {{ old('condition') == 'used' ? 'selected' : '' }}>Used</option>
                                <option value="refurbished" {{ old('condition') == 'refurbished' ? 'selected' : '' }}>Refurbished</option>
                            </select>
                            @error('condition')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Attributes -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-800 border-b pb-2">Attributes (Optional)</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Brand</label>
                            <input type="text" name="attributes[brand]"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                   value="{{ old('attributes.brand') }}">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Model</label>
                            <input type="text" name="attributes[model]"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                   value="{{ old('attributes.model') }}">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Color</label>
                            <input type="text" name="attributes[color]"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                   value="{{ old('attributes.color') }}">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Size</label>
                            <input type="text" name="attributes[size]"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                   value="{{ old('attributes.size') }}">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Material</label>
                            <input type="text" name="attributes[material]"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                   value="{{ old('attributes.material') }}">
                        </div>
                    </div>
                </div>

                <!-- Images -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-800 border-b pb-2">Product Images</h3>
                    <p class="text-sm text-gray-600 mb-4">Upload at least 1 image (max 5 images, 2MB each)</p>
                    
                    <div id="imageUploadArea" class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                        <input type="file" name="images[]" id="images" multiple accept="image/*" 
                               class="hidden" onchange="previewImages()">
                        
                        <div class="mb-4">
                            <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-2"></i>
                            <p class="text-gray-600">Drag & drop images or click to browse</p>
                            <p class="text-sm text-gray-500 mt-1">Recommended: 800x800 pixels, JPG or PNG</p>
                        </div>
                        
                        <button type="button" onclick="document.getElementById('images').click()"
                                class="bg-primary text-white px-6 py-2 rounded-lg font-medium hover:bg-indigo-700 transition">
                            <i class="fas fa-upload mr-2"></i> Select Images
                        </button>
                    </div>
                    
                    <div id="imagePreview" class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                        <!-- Images will be previewed here -->
                    </div>
                    
                    @error('images')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-800 border-b pb-2">Status</h3>
                    
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <input type="checkbox" name="is_active" id="is_active" value="1" 
                                   class="h-5 w-5 text-primary rounded focus:ring-primary border-gray-300"
                                   {{ old('is_active', true) ? 'checked' : '' }}>
                            <label for="is_active" class="ml-3 text-sm font-medium text-gray-700">
                                Active Product
                            </label>
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" name="is_featured" id="is_featured" value="1"
                                   class="h-5 w-5 text-primary rounded focus:ring-primary border-gray-300"
                                   {{ old('is_featured') ? 'checked' : '' }}>
                            <label for="is_featured" class="ml-3 text-sm font-medium text-gray-700">
                                Featured Product
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex space-x-3 pt-6 border-t">
                    <button type="submit"
                            class="bg-primary text-white px-6 py-3 rounded-lg font-semibold hover:bg-indigo-700 transition flex items-center">
                        <i class="fas fa-save mr-2"></i> Create Product
                    </button>
                    
                    <a href="{{ route('admin.listings.index') }}"
                       class="bg-gray-200 text-gray-800 px-6 py-3 rounded-lg font-semibold hover:bg-gray-300 transition flex items-center">
                        <i class="fas fa-times mr-2"></i> Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function previewImages() {
    const preview = document.getElementById('imagePreview');
    const files = document.getElementById('images').files;
    
    preview.innerHTML = '';
    
    if (files.length === 0) {
        preview.innerHTML = '<p class="text-gray-500 text-center col-span-full">No images selected</p>';
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
                <div class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center cursor-pointer" onclick="removeImage(${i})">
                    <i class="fas fa-times text-xs"></i>
                </div>
                <div class="p-2 bg-white">
                    <p class="text-xs text-gray-600 truncate">${file.name}</p>
                    <p class="text-xs text-gray-500">${(file.size / 1024).toFixed(1)} KB</p>
                </div>
            `;
            preview.appendChild(div);
        };
        
        reader.readAsDataURL(file);
    }
}

function removeImage(index) {
    const input = document.getElementById('images');
    const dt = new DataTransfer();
    
    for (let i = 0; i < input.files.length; i++) {
        if (i !== index) {
            dt.items.add(input.files[i]);
        }
    }
    
    input.files = dt.files;
    previewImages();
}

// Handle drag and drop
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
    const input = document.getElementById('images');
    
    // Merge existing files with dropped files
    const allFiles = Array.from(input.files).concat(Array.from(files));
    
    // Create new DataTransfer object
    const newDt = new DataTransfer();
    allFiles.forEach(file => newDt.items.add(file));
    
    // Update input files
    input.files = newDt.files;
    previewImages();
}
</script>
@endsection