@extends('layouts.admin')

@section('title', 'Product Management')
@section('page-title', 'Product Management')
@section('page-description', 'Manage all product listings')

@section('content')
<div class="space-y-6">
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="stat-card bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center">
                <div class="bg-blue-100 p-3 rounded-lg mr-4">
                    <i class="fas fa-box text-blue-600 text-2xl"></i>
                </div>
                <div>
                    <p class="text-gray-600">Total Products</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['total'] }}</p>
                </div>
            </div>
        </div>
        
        <div class="stat-card bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center">
                <div class="bg-green-100 p-3 rounded-lg mr-4">
                    <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                </div>
                <div>
                    <p class="text-gray-600">Active</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['active'] }}</p>
                </div>
            </div>
        </div>
        
        <div class="stat-card bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center">
                <div class="bg-yellow-100 p-3 rounded-lg mr-4">
                    <i class="fas fa-star text-yellow-600 text-2xl"></i>
                </div>
                <div>
                    <p class="text-gray-600">Featured</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['featured'] }}</p>
                </div>
            </div>
        </div>
        
        <div class="stat-card bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center">
                <div class="bg-red-100 p-3 rounded-lg mr-4">
                    <i class="fas fa-exclamation-circle text-red-600 text-2xl"></i>
                </div>
                <div>
                    <p class="text-gray-600">Out of Stock</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['out_of_stock'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex flex-wrap gap-3">
        <a href="{{ route('admin.listings.create') }}" 
           class="bg-primary text-white px-6 py-3 rounded-lg font-semibold hover:bg-indigo-700 transition flex items-center">
            <i class="fas fa-plus mr-2"></i> Add New Product
        </a>
        
        <a href="{{ route('admin.listings.export.csv') }}" 
           class="bg-green-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-700 transition flex items-center">
            <i class="fas fa-file-export mr-2"></i> Export CSV
        </a>
        
        <button onclick="toggleImportModal()" 
                class="bg-purple-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-purple-700 transition flex items-center">
            <i class="fas fa-file-import mr-2"></i> Import CSV
        </button>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Title, SKU, Vendor..."
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                <select name="category" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Vendor</label>
                <select name="vendor" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="">All Vendors</option>
                    @foreach($vendors as $vendor)
                    <option value="{{ $vendor->id }}" {{ request('vendor') == $vendor->id ? 'selected' : '' }}>
                        {{ $vendor->business_name }}
                    </option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Origin</label>
                <select name="origin" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="">All Origins</option>
                    <option value="local" {{ request('origin') == 'local' ? 'selected' : '' }}>Local</option>
                    <option value="imported" {{ request('origin') == 'imported' ? 'selected' : '' }}>Imported</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Featured</label>
                <select name="featured" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="">All Products</option>
                    <option value="true" {{ request('featured') == 'true' ? 'selected' : '' }}>Featured Only</option>
                    <option value="false" {{ request('featured') == 'false' ? 'selected' : '' }}>Not Featured</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sort By</label>
                <select name="sort" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest First</option>
                    <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest First</option>
                    <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                    <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                    <option value="stock_low" {{ request('sort') == 'stock_low' ? 'selected' : '' }}>Stock: Low to High</option>
                    <option value="stock_high" {{ request('sort') == 'stock_high' ? 'selected' : '' }}>Stock: High to Low</option>
                </select>
            </div>
            
            <div class="flex items-end">
                <button type="submit" class="w-full bg-primary text-white px-4 py-2 rounded-lg font-semibold hover:bg-indigo-700 transition">
                    <i class="fas fa-filter mr-2"></i> Apply Filters
                </button>
                <a href="{{ route('admin.listings.index') }}" class="ml-2 bg-gray-200 text-gray-800 px-4 py-2 rounded-lg font-semibold hover:bg-gray-300 transition">
                    <i class="fas fa-times"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Bulk Actions Bar -->
    <div class="bg-white rounded-xl shadow-sm p-4 hidden" id="bulkActionsBar">
        <div class="flex flex-wrap items-center justify-between">
            <div class="flex items-center space-x-3">
                <span class="text-sm font-medium text-gray-700" id="selectedCount">0 products selected</span>
                <div class="flex space-x-2">
                    <select id="bulkActionSelect" class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">Choose Action</option>
                        <option value="activate">Activate Selected</option>
                        <option value="deactivate">Deactivate Selected</option>
                        <option value="feature">Mark as Featured</option>
                        <option value="unfeature">Remove Featured</option>
                        <option value="delete">Delete Selected</option>
                    </select>
                    <button onclick="handleBulkAction()" 
                            class="bg-primary text-white px-4 py-1.5 rounded-lg font-semibold hover:bg-indigo-700 transition text-sm">
                        Apply
                    </button>
                </div>
            </div>
            <button onclick="clearSelection()" class="text-sm text-gray-600 hover:text-gray-800">
                Clear Selection
            </button>
        </div>
    </div>

    <!-- Products Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        @if($listings->isEmpty())
        <div class="text-center py-12">
            <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-6">
                <i class="fas fa-box text-gray-400 text-3xl"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">No products found</h3>
            <p class="text-gray-600 mb-6">Try adjusting your filters or add a new product</p>
            <a href="{{ route('admin.listings.create') }}" 
               class="inline-flex items-center px-6 py-3 bg-primary text-white rounded-lg hover:bg-indigo-700 font-medium">
                <i class="fas fa-plus mr-2"></i> Add New Product
            </a>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 border-b">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-12">
                            <input type="checkbox" id="selectAll" class="h-4 w-4 text-primary rounded border-gray-300">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Product
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Vendor
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Price
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Stock
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($listings as $listing)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="checkbox" value="{{ $listing->id }}" 
                                   class="listing-checkbox h-4 w-4 text-primary rounded border-gray-300">
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center mr-4">
                                    @if($listing->images->count() > 0)
                                    <img src="{{ Storage::url($listing->images->first()->path) }}" 
                                         alt="{{ $listing->title }}" 
                                         class="w-full h-full object-cover rounded-lg">
                                    @else
                                    <i class="fas fa-image text-gray-400 text-lg"></i>
                                    @endif
                                </div>
                                <div>
                                    <div class="flex items-center">
                                        <a href="{{ route('admin.listings.show', $listing) }}" 
                                           class="font-medium text-gray-900 hover:text-primary transition">
                                            {{ Str::limit($listing->title, 50) }}
                                        </a>
                                        @if($listing->is_featured)
                                        <span class="ml-2 px-2 py-0.5 bg-yellow-100 text-yellow-800 text-xs font-medium rounded-full">
                                            <i class="fas fa-star mr-1"></i> Featured
                                        </span>
                                        @endif
                                    </div>
                                    <p class="text-sm text-gray-600 mt-1">SKU: {{ $listing->sku }}</p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        <i class="fas fa-tag mr-1"></i> {{ $listing->category->name ?? 'Uncategorized' }}
                                        <span class="mx-2">•</span>
                                        <i class="fas fa-globe mr-1"></i> {{ ucfirst($listing->origin) }}
                                    </p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-primary-100 text-primary-800 rounded-full flex items-center justify-center mr-3">
                                    <i class="fas fa-store text-xs"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ $listing->vendor->business_name ?? 'Unknown Vendor' }}
                                    </p>
                                    <p class="text-xs text-gray-500">{{ $listing->vendor->user->email ?? '' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-bold text-gray-900">
                                UGX {{ number_format($listing->price, 0) }}
                            </div>
                            @if($listing->compare_at_price)
                            <div class="text-sm text-gray-500 line-through">
                                UGX {{ number_format($listing->compare_at_price, 0) }}
                            </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium {{ $listing->stock > 10 ? 'text-green-600' : ($listing->stock > 0 ? 'text-yellow-600' : 'text-red-600') }}">
                                {{ $listing->stock }}
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                                @php
                                    $stockPercentage = min(100, ($listing->stock / 100) * 100);
                                @endphp
                                <div class="h-2 rounded-full {{ $listing->stock > 10 ? 'bg-green-500' : ($listing->stock > 0 ? 'bg-yellow-500' : 'bg-red-500') }}" 
                                     style="width: {{ $stockPercentage }}%"></div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-3 py-1 rounded-full text-xs font-medium {{ $listing->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $listing->is_active ? 'Active' : 'Inactive' }}
                            </span>
                            @if($listing->stock == 0)
                            <span class="ml-2 px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                Out of Stock
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('admin.listings.show', $listing) }}" 
                                   class="text-blue-600 hover:text-blue-800 p-2 hover:bg-blue-50 rounded-lg transition"
                                   title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.listings.edit', $listing) }}" 
                                   class="text-green-600 hover:text-green-800 p-2 hover:bg-green-50 rounded-lg transition"
                                   title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.listings.toggle-status', $listing) }}" method="POST" class="inline">
                                    @csrf
                                    @method('POST')
                                    <button type="submit" 
                                            class="text-{{ $listing->is_active ? 'yellow' : 'green' }}-600 hover:text-{{ $listing->is_active ? 'yellow' : 'green' }}-800 p-2 hover:bg-{{ $listing->is_active ? 'yellow' : 'green' }}-50 rounded-lg transition"
                                            title="{{ $listing->is_active ? 'Deactivate' : 'Activate' }}">
                                        <i class="fas fa-{{ $listing->is_active ? 'pause' : 'play' }}"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.listings.toggle-featured', $listing) }}" method="POST" class="inline">
                                    @csrf
                                    @method('POST')
                                    <button type="submit" 
                                            class="text-{{ $listing->is_featured ? 'gray' : 'purple' }}-600 hover:text-{{ $listing->is_featured ? 'gray' : 'purple' }}-800 p-2 hover:bg-{{ $listing->is_featured ? 'gray' : 'purple' }}-50 rounded-lg transition"
                                            title="{{ $listing->is_featured ? 'Remove Featured' : 'Mark as Featured' }}">
                                        <i class="fas fa-star"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.listings.destroy', $listing) }}" method="POST" class="inline" 
                                      onsubmit="return confirm('Are you sure you want to delete this product? This action cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="text-red-600 hover:text-red-800 p-2 hover:bg-red-50 rounded-lg transition"
                                            title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($listings->hasPages())
        <div class="px-6 py-4 border-t">
            {{ $listings->links() }}
        </div>
        @endif
        @endif
    </div>
</div>

<!-- Import CSV Modal -->
<div id="importModal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-75 items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-lg max-w-md w-full">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Import Products from CSV</h3>
            
            <form action="{{ route('admin.listings.import.csv') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">CSV File *</label>
                        <input type="file" name="csv_file" accept=".csv" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <p class="text-xs text-gray-500 mt-1">Max 10MB, CSV format only</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Vendor *</label>
                        <select name="vendor_id" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">Select Vendor</option>
                            @foreach($vendors as $vendor)
                            <option value="{{ $vendor->id }}">{{ $vendor->business_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="flex items-center">
                        <input type="checkbox" name="update_existing" id="update_existing" value="1"
                               class="h-4 w-4 text-primary rounded border-gray-300">
                        <label for="update_existing" class="ml-2 text-sm text-gray-700">
                            Update existing products (match by SKU)
                        </label>
                    </div>
                    
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h4 class="font-medium text-blue-800 mb-2">CSV Format Required:</h4>
                        <ul class="text-sm text-blue-700 space-y-1">
                            <li><code>sku</code> - Product SKU (required, unique)</li>
                            <li><code>title</code> - Product title (required)</li>
                            <li><code>description</code> - Product description</li>
                            <li><code>price</code> - Price (required)</li>
                            <li><code>stock</code> - Stock quantity</li>
                            <li><code>weight_kg</code> - Weight in kilograms</li>
                            <li><code>origin</code> - local or imported</li>
                            <li><code>condition</code> - new, used, refurbished</li>
                            <li><code>is_active</code> - true or false</li>
                        </ul>
                        <a href="{{ asset('samples/products_sample.csv') }}" 
                           class="inline-block mt-3 text-sm text-primary hover:underline">
                            <i class="fas fa-download mr-1"></i> Download Sample CSV
                        </a>
                    </div>
                </div>
                
                <div class="mt-6 flex space-x-3">
                    <button type="submit"
                            class="flex-1 bg-primary text-white py-3 rounded-lg font-semibold hover:bg-indigo-700 transition">
                        Import CSV
                    </button>
                    <button type="button" onclick="toggleImportModal()"
                            class="flex-1 bg-gray-200 text-gray-800 py-3 rounded-lg font-semibold hover:bg-gray-300 transition">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Bulk selection functionality
let selectedProducts = new Set();

function updateBulkActionsBar() {
    const selectedCount = selectedProducts.size;
    const bulkActionsBar = document.getElementById('bulkActionsBar');
    const selectedCountElement = document.getElementById('selectedCount');
    
    if (selectedCount > 0) {
        bulkActionsBar.classList.remove('hidden');
        selectedCountElement.textContent = `${selectedCount} product${selectedCount > 1 ? 's' : ''} selected`;
    } else {
        bulkActionsBar.classList.add('hidden');
    }
    
    // Update select all checkbox
    const selectAllCheckbox = document.getElementById('selectAll');
    const allCheckboxes = document.querySelectorAll('.listing-checkbox');
    const checkedCheckboxes = document.querySelectorAll('.listing-checkbox:checked');
    
    selectAllCheckbox.indeterminate = checkedCheckboxes.length > 0 && checkedCheckboxes.length < allCheckboxes.length;
    selectAllCheckbox.checked = checkedCheckboxes.length === allCheckboxes.length;
}

// Select all functionality
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.listing-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
        if (this.checked) {
            selectedProducts.add(checkbox.value);
        } else {
            selectedProducts.delete(checkbox.value);
        }
    });
    updateBulkActionsBar();
});

// Individual checkbox functionality
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('listing-checkbox')) {
        if (e.target.checked) {
            selectedProducts.add(e.target.value);
        } else {
            selectedProducts.delete(e.target.value);
        }
        updateBulkActionsBar();
    }
});

// Clear selection
function clearSelection() {
    selectedProducts.clear();
    const checkboxes = document.querySelectorAll('.listing-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    document.getElementById('selectAll').checked = false;
    updateBulkActionsBar();
}

// Handle bulk action
function handleBulkAction() {
    const action = document.getElementById('bulkActionSelect').value;
    const selectedIds = Array.from(selectedProducts);
    
    if (!action) {
        alert('Please select an action');
        return;
    }
    
    if (selectedIds.length === 0) {
        alert('Please select at least one product');
        return;
    }
    
    if (action === 'delete' && !confirm(`Are you sure you want to delete ${selectedIds.length} product(s)? This action cannot be undone.`)) {
        return;
    }
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = "{{ route('admin.listings.bulk-actions') }}";
    
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = "{{ csrf_token() }}";
    form.appendChild(csrfToken);
    
    const actionInput = document.createElement('input');
    actionInput.type = 'hidden';
    actionInput.name = 'action';
    actionInput.value = action;
    form.appendChild(actionInput);
    
    selectedIds.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'listings[]';
        input.value = id;
        form.appendChild(input);
    });
    
    document.body.appendChild(form);
    form.submit();
}

// Toggle import modal
function toggleImportModal() {
    const modal = document.getElementById('importModal');
    modal.classList.toggle('hidden');
    document.body.style.overflow = modal.classList.contains('hidden') ? '' : 'hidden';
}

// Close modal when clicking outside
document.getElementById('importModal').addEventListener('click', function(e) {
    if (e.target === this) {
        toggleImportModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && !document.getElementById('importModal').classList.contains('hidden')) {
        toggleImportModal();
    }
});
</script>
@endpush
@endsection