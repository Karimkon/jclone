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

                    <!-- Category Select -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Category *
                        </label>
                        <select name="category_id" id="category_id" required
                                class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                @if($category->children->count() > 0)
                                    {{-- Parent category with children - make it a disabled header --}}
                                    <optgroup label="{{ $category->name }}">
                                        @foreach($category->children as $child)
                                            @if($child->children->count() > 0)
                                                {{-- Sub-category with children - show children only --}}
                                                @foreach($child->children as $subchild)
                                                    <option value="{{ $subchild->id }}" {{ old('category_id') == $subchild->id ? 'selected' : '' }}>
                                                        {{ $child->name }} › {{ $subchild->name }}
                                                    </option>
                                                @endforeach
                                            @else
                                                {{-- Leaf subcategory - selectable --}}
                                                <option value="{{ $child->id }}" {{ old('category_id') == $child->id ? 'selected' : '' }}>
                                                    {{ $child->name }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </optgroup>
                                @else
                                    {{-- Category without children (leaf) - selectable --}}
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        <p class="mt-1 text-sm text-gray-500">Select a specific subcategory, not a main category</p>
                        @error('category_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- SKU (Optional) -->
                    <div class="md:col-span-2">
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
                            Price (UGX) *
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-3 text-gray-500">UGX</span>
                            <input type="number" name="price" step="0.01" min="0" required
                                   class="w-full pl-12 border border-gray-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
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

                <!-- Product Variations -->
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Product Variations</h2>
                    
                    <div class="mb-6">
                        <div class="flex items-center">
                            <input type="checkbox" name="enable_variations" id="enable_variations" value="1" 
                                   class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded"
                                   onchange="toggleVariations(this.checked)">
                            <label for="enable_variations" class="ml-2 block text-sm font-medium text-gray-700">
                                Enable product variations (different colors/sizes with different prices/stock)
                            </label>
                        </div>
                        <p class="mt-1 text-sm text-gray-500">
                            If checked, customers will be able to select color/size options.
                        </p>
                    </div>
                    
                    <!-- Variations Container -->
                    <div id="variationsContainer" class="hidden">
                        <!-- Variation Generator -->
                        <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                            <h3 class="text-md font-medium text-gray-900 mb-3">Create Variations</h3>
                            
                            <!-- Colors -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Available Colors</label>
                                <div class="flex flex-wrap gap-2 mb-2" id="colorChips">
                                    <!-- Color chips will be added here -->
                                </div>
                                <div class="flex gap-2">
                                    <input type="text" id="newColor" placeholder="Add color (e.g., Red)" 
                                           class="flex-1 border border-gray-300 rounded-lg px-3 py-2">
                                    <button type="button" onclick="addColor()" 
                                            class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-indigo-700">
                                        Add Color
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Sizes -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Available Sizes</label>
                                <div class="flex flex-wrap gap-2 mb-2" id="sizeChips">
                                    <!-- Size chips will be added here -->
                                </div>
                                <div class="flex gap-2">
                                    <input type="text" id="newSize" placeholder="Add size (e.g., M)" 
                                           class="flex-1 border border-gray-300 rounded-lg px-3 py-2">
                                    <button type="button" onclick="addSize()" 
                                            class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-indigo-700">
                                        Add Size
                                    </button>
                                </div>
                            </div>
                            
                            <button type="button" onclick="generateVariations()" 
                                    class="w-full px-4 py-3 bg-green-500 text-white rounded-lg hover:bg-green-600 font-medium">
                                <i class="fas fa-magic mr-2"></i> Generate Variations
                            </button>
                        </div>
                        
                        <!-- Variations Table -->
                        <div class="mb-6">
                            <h3 class="text-md font-medium text-gray-900 mb-3">Variations</h3>
                            <div id="variationsTable" class="overflow-x-auto">
                                <!-- Variations table will be generated here -->
                            </div>
                        </div>
                        
                        <!-- Hidden inputs for variations -->
                        <div id="variationsData"></div>
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
                    <textarea name="description" id="description" rows="6" required
                              class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                              placeholder="Describe your product in detail. Include features, specifications, benefits, etc.">{{ old('description') }}</textarea>
                    <p class="mt-1 text-sm text-gray-500">Minimum 100 characters. Describe your product clearly to attract buyers.</p>
                    <div class="mt-1 text-sm">
                        <span id="charCount">0</span> characters
                    </div>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Enhanced Images Section -->
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Product Images</h2>
                
                <div class="mb-6">
                    <!-- Upload Zone -->
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Upload Images *
                    </label>
                    <div id="uploadZone" 
                         class="border-3 border-dashed border-gray-300 rounded-xl p-10 text-center hover:border-primary transition cursor-pointer bg-gray-50 hover:bg-gray-100">
                        <div class="flex flex-col items-center justify-center">
                            <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mb-4">
                                <i class="fas fa-images text-primary text-2xl"></i>
                            </div>
                            <p class="text-xl font-medium text-gray-700 mb-2">Drag & Drop Images Here</p>
                            <p class="text-sm text-gray-500 mb-4">Or click to browse your computer</p>
                            <div class="flex items-center gap-4 mb-4">
                                <span class="px-3 py-1.5 bg-blue-100 text-blue-700 rounded-full text-sm font-medium">
                                    <i class="fas fa-check-circle mr-1"></i> Up to 5 images
                                </span>
                                <span class="px-3 py-1.5 bg-green-100 text-green-700 rounded-full text-sm font-medium">
                                    <i class="fas fa-check-circle mr-1"></i> 2MB max each
                                </span>
                                <span class="px-3 py-1.5 bg-purple-100 text-purple-700 rounded-full text-sm font-medium">
                                    <i class="fas fa-check-circle mr-1"></i> JPG, PNG, WebP
                                </span>
                            </div>
                            <button type="button" onclick="document.getElementById('images').click()" 
                                    class="px-6 py-3 bg-primary text-white rounded-lg font-medium hover:bg-indigo-700 transition flex items-center gap-2">
                                <i class="fas fa-folder-open"></i>
                                Browse Files
                            </button>
                        </div>
                        <input type="file" name="images[]" id="images" multiple accept="image/*" 
                               class="hidden" onchange="handleFileSelect(this)">
                    </div>
                    
                    <p class="mt-4 text-sm text-gray-500 flex items-center justify-center gap-2">
                        <i class="fas fa-lightbulb text-yellow-500"></i>
                        <span>Tip: First image will be the main product thumbnail</span>
                    </p>
                    @error('images')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Image Preview Gallery -->
                <div id="imagePreview" class="hidden">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-md font-bold text-gray-900">Image Gallery</h3>
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="selectAllImages()" 
                                    class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">
                                <i class="fas fa-check-square mr-1"></i> Select All
                            </button>
                            <button type="button" onclick="clearAllImages()" 
                                    class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-red-50 text-red-600">
                                <i class="fas fa-trash mr-1"></i> Clear All
                            </button>
                        </div>
                    </div>
                    
                    <!-- Image Counter -->
                    <div class="mb-4 p-3 bg-blue-50 rounded-lg border border-blue-100">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="text-sm font-medium text-blue-700">
                                    <span id="selectedCount">0</span> of 5 images selected
                                </span>
                                <span id="fileSizeInfo" class="text-xs text-blue-600"></span>
                            </div>
                            <div class="text-sm text-gray-500">
                                <i class="fas fa-arrows-alt mr-1"></i> Drag to reorder
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sortable Image Grid -->
                    <div id="newImagesSortable" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-4">
                        <!-- Images will be added here dynamically -->
                    </div>
                    
                    <!-- Batch Actions -->
                    <div id="batchActions" class="hidden flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-check-circle text-green-500"></i>
                            <span class="text-sm font-medium text-gray-700">
                                <span id="batchCount">0</span> images selected
                            </span>
                        </div>
                        <div class="flex gap-2">
                            <button type="button" onclick="setAsMain()" 
                                    class="px-4 py-2 bg-primary/10 text-primary text-sm font-medium rounded-lg hover:bg-primary hover:text-white transition">
                                <i class="fas fa-star mr-1"></i> Set as Main
                            </button>
                            <button type="button" onclick="removeSelected()" 
                                    class="px-4 py-2 bg-red-50 text-red-600 text-sm font-medium rounded-lg hover:bg-red-100 transition">
                                <i class="fas fa-trash mr-1"></i> Remove Selected
                            </button>
                            <button type="button" onclick="clearSelection()" 
                                    class="px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-100 transition">
                                <i class="fas fa-times mr-1"></i> Clear Selection
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="p-6">
                <div class="flex justify-end space-x-4">
                    <button type="reset" 
                            class="px-6 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 font-medium">
                        Reset Form
                    </button>
                    <button type="submit" id="submitBtn"
                            class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-indigo-700 font-medium">
                        <i class="fas fa-plus-circle mr-2"></i> Create Product
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Enhanced upload zone */
    #uploadZone {
        transition: all 0.3s ease;
    }

    #uploadZone.drag-over {
        border-color: #4f46e5 !important;
        background: linear-gradient(135deg, rgba(79,70,229,0.05) 0%, rgba(124,58,237,0.05) 100%);
        transform: scale(1.01);
    }

    /* Image preview animations */
    .image-preview-item {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .image-preview-item:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    /* Selection styles */
    .image-preview-item.selected {
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.3);
        transform: scale(1.02);
    }

    /* Progress bar animation */
    #progressBar {
        transition: width 0.3s ease;
    }

    /* Toast notifications */
    .custom-toast {
        animation: slideInRight 0.3s ease-out;
    }

    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(100%);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    /* Sortable ghost element */
    .sortable-ghost {
        opacity: 0.4;
        background: #e5e7eb;
    }

    .sortable-chosen {
        box-shadow: 0 0 20px rgba(0,0,0,0.1);
    }

    /* Main image badge */
    .main-image-badge {
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.7; }
        100% { opacity: 1; }
    }

    /* File size indicator */
    .file-size {
        font-family: 'Courier New', monospace;
        font-size: 0.8em;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
// Image Upload Variables
let uploadedImages = [];
let selectedImages = new Set();

// Variations Variables
let colors = [];
let sizes = [];
let variations = [];

// Character counter for description
document.addEventListener('DOMContentLoaded', function() {
    const description = document.getElementById('description');
    if (description) {
        const charCount = document.getElementById('charCount');
        charCount.textContent = description.value.length;
        
        description.addEventListener('input', function() {
            charCount.textContent = this.value.length;
        });
    }
    
    // Initialize Select2 for category
    $('#category_id').select2({
        placeholder: 'Search for a category...',
        allowClear: true,
        width: '100%'
    });
    
    // Initialize drag and drop
    initDragAndDrop();
});

// Drag and drop functionality
function initDragAndDrop() {
    const uploadZone = document.getElementById('uploadZone');
    if (!uploadZone) return;
    
    // Prevent default drag behaviors
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        uploadZone.addEventListener(eventName, preventDefaults, false);
        document.body.addEventListener(eventName, preventDefaults, false);
    });
    
    // Highlight drop zone when item is dragged over it
    ['dragenter', 'dragover'].forEach(eventName => {
        uploadZone.addEventListener(eventName, highlight, false);
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
        uploadZone.addEventListener(eventName, unhighlight, false);
    });
    
    // Handle dropped files
    uploadZone.addEventListener('drop', handleDrop, false);
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey || e.metaKey) {
            if (e.key === 'a') {
                e.preventDefault();
                selectAllImages();
            }
        }
        if (e.key === 'Delete' || e.key === 'Backspace') {
            if (selectedImages.size > 0) {
                e.preventDefault();
                removeSelected();
            }
        }
    });
}

function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
}

function highlight() {
    const uploadZone = document.getElementById('uploadZone');
    if (uploadZone) {
        uploadZone.classList.add('border-primary', 'bg-primary/5');
    }
}

function unhighlight() {
    const uploadZone = document.getElementById('uploadZone');
    if (uploadZone) {
        uploadZone.classList.remove('border-primary', 'bg-primary/5');
    }
}

function handleDrop(e) {
    const dt = e.dataTransfer;
    const files = dt.files;
    handleFiles(files);
}

function handleFileSelect(input) {
    handleFiles(input.files);
}

function handleFiles(files) {
    if (!files || files.length === 0) return;
    
    // Limit to 5 files
    let filesArray = Array.from(files).slice(0, 5);
    
    // Check if adding these files would exceed limit
    const totalAfterAdd = uploadedImages.length + filesArray.length;
    if (totalAfterAdd > 5) {
        alert(`Maximum 5 images allowed. You already have ${uploadedImages.length} images.`);
        filesArray = filesArray.slice(0, 5 - uploadedImages.length);
    }
    
    // Check file sizes
    const oversizedFiles = filesArray.filter(file => file.size > 2 * 1024 * 1024);
    if (oversizedFiles.length > 0) {
        alert(`Some files exceed 2MB limit: ${oversizedFiles.map(f => f.name).join(', ')}`);
        filesArray = filesArray.filter(file => file.size <= 2 * 1024 * 1024);
    }
    
    // Process files
    let processedCount = 0;
    const totalFiles = filesArray.length;
    
    filesArray.forEach((file, index) => {
        const reader = new FileReader();
        reader.onload = function(e) {
            uploadedImages.push({
                id: Date.now() + index,
                file: file,
                preview: e.target.result,
                name: file.name,
                size: formatFileSize(file.size),
                selected: false,
                isMain: uploadedImages.length === 0
            });
            
            processedCount++;
            
            if (processedCount === totalFiles) {
                updateImagePreviews();
                updateFileInputOrder();
                showSuccessMessage(`${totalFiles} images uploaded successfully!`);
            }
        };
        reader.readAsDataURL(file);
    });
}

function updateImagePreviews() {
    const container = document.getElementById('newImagesSortable');
    if (!container) return;
    
    container.innerHTML = '';
    
    if (uploadedImages.length === 0) {
        const imagePreview = document.getElementById('imagePreview');
        if (imagePreview) imagePreview.classList.add('hidden');
        return;
    }
    
    const imagePreview = document.getElementById('imagePreview');
    if (imagePreview) imagePreview.classList.remove('hidden');
    
    uploadedImages.forEach((img, index) => {
        const col = document.createElement('div');
        col.className = `relative group image-preview-item ${img.selected ? 'ring-2 ring-primary' : ''}`;
        col.setAttribute('data-id', img.id);
        
        col.innerHTML = `
            <!-- Selection Checkbox -->
            <div class="absolute top-2 left-2 z-10">
                <input type="checkbox" 
                       id="select-${img.id}" 
                       ${img.selected ? 'checked' : ''}
                       onchange="toggleSelect(${img.id}, this)"
                       class="hidden">
                <label for="select-${img.id}" 
                       class="w-6 h-6 bg-white rounded-full flex items-center justify-center cursor-pointer shadow-sm hover:bg-gray-100 transition ${img.selected ? 'bg-primary text-white' : 'text-gray-400 hover:text-gray-600'}">
                    <i class="fas fa-check text-xs"></i>
                </label>
            </div>
            
            <!-- Main Image Badge -->
            ${img.isMain ? 
                `<div class="absolute top-2 right-2 bg-yellow-500 text-white text-xs font-bold px-2 py-1 rounded-full shadow-sm">
                    <i class="fas fa-star mr-1"></i> Main
                </div>` : 
                `<button type="button" 
                        onclick="setImageAsMain(${img.id})"
                        class="absolute top-2 right-2 bg-white/90 hover:bg-white text-gray-600 hover:text-yellow-600 w-7 h-7 rounded-full flex items-center justify-center text-sm shadow-sm transition opacity-0 group-hover:opacity-100">
                    <i class="far fa-star"></i>
                </button>`
            }
            
            <!-- Image -->
            <div class="aspect-square rounded-lg overflow-hidden border border-gray-200 bg-gray-100">
                <img src="${img.preview}" 
                     alt="${img.name}" 
                     class="w-full h-full object-cover transition-transform group-hover:scale-105">
            </div>
            
            <!-- Image Info -->
            <div class="mt-2 p-2 bg-gray-50 rounded-lg">
                <div class="flex justify-between items-center mb-1">
                    <p class="text-xs font-medium text-gray-700 truncate" title="${img.name}">
                        Image ${index + 1}
                    </p>
                    <span class="text-xs text-gray-500">${img.size}</span>
                </div>
                
                <!-- Remove Button -->
                <button type="button" 
                        onclick="removeImage(${img.id})"
                        class="w-full mt-1 px-2 py-1 bg-red-50 hover:bg-red-100 text-red-600 text-xs font-medium rounded flex items-center justify-center gap-1 transition">
                    <i class="fas fa-trash text-xs"></i>
                    Remove
                </button>
            </div>
            
            <!-- Drag Handle -->
            <div class="absolute bottom-2 left-2 text-gray-400 cursor-move opacity-0 group-hover:opacity-100 transition">
                <i class="fas fa-grip-vertical"></i>
            </div>
            
            <!-- Image Number -->
            <div class="absolute bottom-2 right-2 bg-black/70 text-white text-xs w-6 h-6 rounded-full flex items-center justify-center">
                ${index + 1}
            </div>
        `;
        
        container.appendChild(col);
    });
    
    // Initialize Sortable with better configuration
    initImageSorting();
    
    // Update counters
    updateCounters();
    updateFileSizeInfo();
}

function initImageSorting() {
    const container = document.getElementById('newImagesSortable');
    if (!container) return;
    
    new Sortable(container, {
        animation: 200,
        ghostClass: 'bg-blue-50',
        chosenClass: 'ring-2 ring-primary',
        dragClass: 'opacity-50',
        handle: '.fa-grip-vertical, .image-preview-item',
        onEnd: function(evt) {
            // Update order in uploadedImages array
            const movedItem = uploadedImages.splice(evt.oldIndex, 1)[0];
            uploadedImages.splice(evt.newIndex, 0, movedItem);
            
            // Update file input (preserve order for form submission)
            const dataTransfer = new DataTransfer();
            uploadedImages.forEach(img => {
                dataTransfer.items.add(img.file);
            });
            
            const fileInput = document.getElementById('images');
            if (fileInput) {
                fileInput.files = dataTransfer.files;
            }
            
            // Update UI
            updateImagePreviews();
            showToast('Images reordered!', 'success');
        }
    });
}

function updateCounters() {
    const totalImages = uploadedImages.length;
    const selectedCount = uploadedImages.filter(img => img.selected).length;
    
    const selectedCountEl = document.getElementById('selectedCount');
    const batchCountEl = document.getElementById('batchCount');
    
    if (selectedCountEl) selectedCountEl.textContent = totalImages;
    if (batchCountEl) batchCountEl.textContent = selectedCount;
    
    // Show/hide batch actions
    const batchActions = document.getElementById('batchActions');
    if (batchActions) {
        if (selectedCount > 0) {
            batchActions.classList.remove('hidden');
        } else {
            batchActions.classList.add('hidden');
        }
    }
    
    // Update upload zone text
    const uploadText = document.querySelector('#uploadZone p.text-xl');
    if (uploadText) {
        if (totalImages > 0) {
            uploadText.textContent = `Add More Images (${totalImages}/5 uploaded)`;
        } else {
            uploadText.textContent = 'Drag & Drop Images Here';
        }
    }
}

function updateFileSizeInfo() {
    const totalSize = uploadedImages.reduce((sum, img) => sum + img.file.size, 0);
    const formattedSize = formatFileSize(totalSize);
    const fileSizeInfo = document.getElementById('fileSizeInfo');
    if (fileSizeInfo) {
        fileSizeInfo.textContent = `Total: ${formattedSize}`;
    }
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
}

// Selection Functions
function toggleSelect(imageId, checkbox) {
    const image = uploadedImages.find(img => img.id === imageId);
    if (image) {
        image.selected = checkbox.checked;
        updateCounters();
        updateImagePreviews();
    }
}

function selectAllImages() {
    uploadedImages.forEach(img => img.selected = true);
    updateCounters();
    updateImagePreviews();
}

function clearSelection() {
    uploadedImages.forEach(img => img.selected = false);
    updateCounters();
    updateImagePreviews();
}

function selectImage(imageId) {
    const image = uploadedImages.find(img => img.id === imageId);
    if (image) {
        image.selected = !image.selected;
        updateCounters();
        updateImagePreviews();
    }
}

// Image Management Functions
function setImageAsMain(imageId) {
    uploadedImages.forEach(img => img.isMain = false);
    const image = uploadedImages.find(img => img.id === imageId);
    if (image) {
        image.isMain = true;
        
        // Move to first position
        const index = uploadedImages.indexOf(image);
        uploadedImages.splice(index, 1);
        uploadedImages.unshift(image);
        
        // Update file input order
        updateFileInputOrder();
        updateImagePreviews();
        
        showToast('Main image set!', 'success');
    }
}

function setAsMain() {
    const selected = uploadedImages.filter(img => img.selected);
    if (selected.length === 0) {
        showToast('Please select an image first', 'error');
        return;
    }
    
    if (selected.length > 1) {
        showToast('Please select only one image to set as main', 'error');
        return;
    }
    
    setImageAsMain(selected[0].id);
}

function removeImage(imageId) {
    if (!confirm('Remove this image?')) return;
    
    const index = uploadedImages.findIndex(img => img.id === imageId);
    if (index !== -1) {
        const wasMain = uploadedImages[index].isMain;
        uploadedImages.splice(index, 1);
        
        // If main image was removed, set new main
        if (wasMain && uploadedImages.length > 0) {
            uploadedImages[0].isMain = true;
        }
        
        updateFileInputOrder();
        updateImagePreviews();
        showToast('Image removed', 'success');
    }
}

function removeSelected() {
    const selected = uploadedImages.filter(img => img.selected);
    if (selected.length === 0) {
        showToast('No images selected', 'error');
        return;
    }
    
    if (!confirm(`Remove ${selected.length} selected image(s)?`)) return;
    
    // Remove selected images
    uploadedImages = uploadedImages.filter(img => !img.selected);
    
    // Ensure there's a main image
    if (uploadedImages.length > 0 && !uploadedImages.some(img => img.isMain)) {
        uploadedImages[0].isMain = true;
    }
    
    updateFileInputOrder();
    updateImagePreviews();
    showToast(`${selected.length} image(s) removed`, 'success');
}

function clearAllImages() {
    if (uploadedImages.length === 0) return;
    
    if (!confirm('Remove all images?')) return;
    
    uploadedImages = [];
    const fileInput = document.getElementById('images');
    if (fileInput) fileInput.value = '';
    updateImagePreviews();
    showToast('All images cleared', 'success');
}

function updateFileInputOrder() {
    const dataTransfer = new DataTransfer();
    uploadedImages.forEach(img => {
        dataTransfer.items.add(img.file);
    });
    
    const fileInput = document.getElementById('images');
    if (fileInput) {
        fileInput.files = dataTransfer.files;
    }
}

// Variations Functions
function toggleVariations(enabled) {
    const container = document.getElementById('variationsContainer');
    if (container) {
        if (enabled) {
            container.classList.remove('hidden');
        } else {
            container.classList.add('hidden');
        }
    }
}

function addColor() {
    const input = document.getElementById('newColor');
    const color = input.value.trim();
    
    if (color && !colors.includes(color)) {
        colors.push(color);
        updateColorChips();
        input.value = '';
    }
}

function addSize() {
    const input = document.getElementById('newSize');
    const size = input.value.trim();
    
    if (size && !sizes.includes(size)) {
        sizes.push(size);
        updateSizeChips();
        input.value = '';
    }
}

function updateColorChips() {
    const container = document.getElementById('colorChips');
    if (container) {
        container.innerHTML = colors.map(color => `
            <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm flex items-center gap-1">
                ${color}
                <button type="button" onclick="removeColor('${color}')" class="text-blue-600 hover:text-blue-800">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </span>
        `).join('');
    }
}

function updateSizeChips() {
    const container = document.getElementById('sizeChips');
    if (container) {
        container.innerHTML = sizes.map(size => `
            <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm flex items-center gap-1">
                ${size}
                <button type="button" onclick="removeSize('${size}')" class="text-green-600 hover:text-green-800">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </span>
        `).join('');
    }
}

function removeColor(color) {
    colors = colors.filter(c => c !== color);
    updateColorChips();
}

function removeSize(size) {
    sizes = sizes.filter(s => s !== size);
    updateSizeChips();
}

function generateVariations() {
    if (colors.length === 0 && sizes.length === 0) {
        alert('Please add at least one color or size');
        return;
    }
    
    variations = [];
    
    // If no colors or sizes, create single variation
    if (colors.length === 0) colors = [''];
    if (sizes.length === 0) sizes = [''];
    
    // Generate all combinations
    colors.forEach(color => {
        sizes.forEach(size => {
            const variation = {
                id: Date.now() + Math.random(),
                color: color || '',
                size: size || '',
                sku: '',
                price: '',
                sale_price: '',
                stock: 1,
                attributes: {}
            };
            
            if (color) variation.attributes.color = color;
            if (size) variation.attributes.size = size;
            
            variations.push(variation);
        });
    });
    
    updateVariationsTable();
}

function updateVariationsTable() {
    const container = document.getElementById('variationsTable');
    const dataContainer = document.getElementById('variationsData');
    
    if (!container || !dataContainer) return;
    
    if (variations.length === 0) {
        container.innerHTML = '<p class="text-gray-500 text-center p-4">No variations generated yet</p>';
        dataContainer.innerHTML = '';
        return;
    }
    
    let tableHTML = `
        <table class="min-w-full divide-y divide-gray-200 border">
            <thead class="bg-gray-50">
                <tr>
                    ${colors.length > 0 ? '<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Color</th>' : ''}
                    ${sizes.length > 0 ? '<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Size</th>' : ''}
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">SKU</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price (UGX)</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sale Price (UGX)</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
    `;
    
    let dataHTML = '';
    
    variations.forEach((variation, index) => {
        tableHTML += `
            <tr>
                ${colors.length > 0 ? `<td class="px-4 py-3">${variation.color || '-'}</td>` : ''}
                ${sizes.length > 0 ? `<td class="px-4 py-3">${variation.size || '-'}</td>` : ''}
                <td class="px-4 py-3">
                    <input type="text" 
                           name="variations[${index}][sku]" 
                           value="${variation.sku}"
                           class="w-full border border-gray-300 rounded px-2 py-1 text-sm"
                           placeholder="SKU-${index+1}">
                </td>
                <td class="px-4 py-3">
                    <input type="number" 
                           name="variations[${index}][price]" 
                           value="${variation.price}"
                           step="0.01" min="0"
                           required
                           class="w-full border border-gray-300 rounded px-2 py-1 text-sm"
                           placeholder="0.00">
                </td>
                <td class="px-4 py-3">
                    <input type="number" 
                           name="variations[${index}][sale_price]" 
                           value="${variation.sale_price}"
                           step="0.01" min="0"
                           class="w-full border border-gray-300 rounded px-2 py-1 text-sm"
                           placeholder="Optional">
                </td>
                <td class="px-4 py-3">
                    <input type="number" 
                           name="variations[${index}][stock]" 
                           value="${variation.stock}"
                           min="0"
                           required
                           class="w-full border border-gray-300 rounded px-2 py-1 text-sm"
                           placeholder="0">
                </td>
                <td class="px-4 py-3">
                    <button type="button" onclick="removeVariation(${index})" 
                            class="text-red-600 hover:text-red-800">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        
        // Hidden inputs for attributes
        dataHTML += `
            <input type="hidden" name="variations[${index}][color]" value="${variation.color}">
            <input type="hidden" name="variations[${index}][size]" value="${variation.size}">
            <input type="hidden" name="variations[${index}][attributes][color]" value="${variation.color}">
            <input type="hidden" name="variations[${index}][attributes][size]" value="${variation.size}">
        `;
    });
    
    tableHTML += '</tbody></table>';
    container.innerHTML = tableHTML;
    dataContainer.innerHTML = dataHTML;
}

function removeVariation(index) {
    if (confirm('Remove this variation?')) {
        variations.splice(index, 1);
        updateVariationsTable();
    }
}

// Form Validation Enhancement
document.getElementById('listingForm').addEventListener('submit', function(e) {
    const submitBtn = document.getElementById('submitBtn');
    const originalText = submitBtn.innerHTML;
    
    // Description validation
    const description = document.getElementById('description');
    if (description.value.trim().length < 100) {
        e.preventDefault();
        showToast('Description must be at least 100 characters', 'error');
        description.focus();
        description.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return false;
    }
    
    // Images validation
    if (uploadedImages.length === 0) {
        e.preventDefault();
        showToast('Please upload at least one image', 'error');
        return false;
    }
    
    // Disable button and show loading
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Creating...';
    
    // Allow form to submit
    return true;
});

// Toast Notification
function showToast(message, type = 'info') {
    const colors = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        info: 'bg-blue-500',
        warning: 'bg-yellow-500'
    };
    
    const icons = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-circle',
        info: 'fa-info-circle',
        warning: 'fa-exclamation-triangle'
    };
    
    // Remove existing toasts
    const existingToasts = document.querySelectorAll('.custom-toast');
    existingToasts.forEach(toast => toast.remove());
    
    const toast = document.createElement('div');
    toast.className = `custom-toast fixed top-6 right-6 ${colors[type]} text-white px-5 py-3 rounded-xl shadow-xl z-50 flex items-center gap-3`;
    toast.innerHTML = `
        <i class="fas ${icons[type]} text-lg"></i>
        <span class="font-medium">${message}</span>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

function showSuccessMessage(message) {
    showToast(message, 'success');
}

// Add animation styles
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
    
    .image-preview-item {
        transition: all 0.2s ease;
    }
    
    .image-preview-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    #uploadZone.drag-over {
        border-color: #4f46e5;
        background: linear-gradient(135deg, rgba(79,70,229,0.05) 0%, rgba(124,58,237,0.05) 100%);
    }
`;
document.head.appendChild(style);
</script>
@endpush