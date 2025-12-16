@extends('layouts.vendor')

@section('title', 'My Products - Vendor Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Product Listings</h1>
            <p class="text-gray-600">Manage your products and inventory</p>
        </div>
        <div class="flex items-center space-x-4">
            <a href="{{ route('vendor.listings.create') }}" 
               class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-indigo-700 font-medium">
                <i class="fas fa-plus-circle mr-2"></i> Add New Product
            </a>
            <!-- Bulk Actions Dropdown -->
            <div class="relative">
                <button id="bulkActions" 
                        class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                    <i class="fas fa-tasks mr-2"></i> Bulk Actions
                </button>
                <div id="bulkMenu" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg z-10 hidden">
                    <button onclick="bulkAction('activate')" 
                            class="block w-full text-left px-4 py-2 hover:bg-gray-100">
                        <i class="fas fa-check text-green-600 mr-2"></i> Activate Selected
                    </button>
                    <button onclick="bulkAction('deactivate')" 
                            class="block w-full text-left px-4 py-2 hover:bg-gray-100">
                        <i class="fas fa-times text-red-600 mr-2"></i> Deactivate Selected
                    </button>
                    <button onclick="bulkAction('delete')" 
                            class="block w-full text-left px-4 py-2 hover:bg-gray-100 text-red-600">
                        <i class="fas fa-trash mr-2"></i> Delete Selected
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-blue-500">
            <p class="text-sm text-gray-600">Total Products</p>
            <p class="text-2xl font-bold">{{ $listings->total() }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-green-500">
            <p class="text-sm text-gray-600">Active Listings</p>
            <p class="text-2xl font-bold">{{ $listings->where('is_active', true)->count() }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-yellow-500">
            <p class="text-sm text-gray-600">Out of Stock</p>
            <p class="text-2xl font-bold">{{ $listings->where('stock', 0)->count() }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-purple-500">
            <p class="text-sm text-gray-600">Low Stock (< 10)</p>
            <p class="text-2xl font-bold">{{ $listings->where('stock', '<', 10)->where('stock', '>', 0)->count() }}</p>
        </div>
    </div>

    <!-- Products Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <input type="checkbox" id="selectAll" class="rounded">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($listings as $listing)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="checkbox" name="listings[]" value="{{ $listing->id }}" 
                                   class="listing-checkbox rounded">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 mr-4">
                                    @if($listing->images->first())
                                    <img src="{{ asset('storage/' . $listing->images->first()->path) }}" 
                                         alt="{{ $listing->title }}" 
                                         class="h-10 w-10 object-cover rounded">
                                    @else
                                    <div class="h-10 w-10 bg-gray-200 rounded flex items-center justify-center">
                                        <i class="fas fa-image text-gray-400"></i>
                                    </div>
                                    @endif
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900 line-clamp-1">
                                        {{ $listing->title }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        SKU: {{ $listing->sku }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $listing->category->name ?? 'Uncategorized' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            UGX {{ number_format($listing->price, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $listing->stock }}</div>
                            @if($listing->stock == 0)
                            <div class="text-xs text-red-600">Out of Stock</div>
                            @elseif($listing->stock < 10)
                            <div class="text-xs text-yellow-600">Low Stock</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($listing->is_active)
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                Active
                            </span>
                            @else
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">
                                Inactive
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <a href="{{ route('vendor.listings.edit', $listing) }}" 
                                   class="text-indigo-600 hover:text-indigo-900">
                                    <i class="fas fa-edit mr-1"></i> Edit
                                </a>
                                <form action="{{ route('vendor.listings.toggleStatus', $listing) }}" 
                                      method="POST" class="inline">
                                    @csrf
                                    <button type="submit" 
                                            class="text-gray-600 hover:text-gray-900">
                                        @if($listing->is_active)
                                        <i class="fas fa-eye-slash mr-1"></i> Deactivate
                                        @else
                                        <i class="fas fa-eye mr-1"></i> Activate
                                        @endif
                                    </button>
                                </form>
                                <form action="{{ route('vendor.listings.destroy', $listing) }}" 
                                      method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            onclick="return confirm('Delete this product?')"
                                            class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash mr-1"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="text-gray-400">
                                <i class="fas fa-box-open text-4xl mb-3"></i>
                                <p class="text-lg">No products yet</p>
                                <p class="text-sm mt-1">Start by adding your first product</p>
                                <a href="{{ route('vendor.listings.create') }}" 
                                   class="mt-4 inline-block px-4 py-2 bg-primary text-white rounded-lg hover:bg-indigo-700">
                                    <i class="fas fa-plus-circle mr-2"></i> Add First Product
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($listings->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $listings->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Bulk Actions Modal -->
<div id="bulkModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4">
        <h3 class="text-lg font-bold text-gray-900 mb-2" id="modalTitle"></h3>
        <p class="text-gray-600 mb-4" id="modalMessage"></p>
        
        <form id="bulkForm" method="POST" action="{{ route('vendor.listings.bulkUpdate') }}">
            @csrf
            <input type="hidden" name="action" id="bulkActionInput">
            <div id="selectedListings" class="hidden"></div>
            
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeBulkModal()"
                        class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit" id="modalSubmit"
                        class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-indigo-700">
                    Confirm
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Bulk Actions Dropdown
    document.getElementById('bulkActions').addEventListener('click', function(e) {
        e.stopPropagation();
        const menu = document.getElementById('bulkMenu');
        menu.classList.toggle('hidden');
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function() {
        document.getElementById('bulkMenu').classList.add('hidden');
    });
    
    // Select All functionality
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.listing-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });
    
    // Bulk Action function
    function bulkAction(action) {
        const checkboxes = document.querySelectorAll('.listing-checkbox:checked');
        
        if (checkboxes.length === 0) {
            alert('Please select at least one product.');
            return;
        }
        
        // Get selected listings
        const selected = Array.from(checkboxes).map(cb => cb.value);
        
        // Set modal content based on action
        const modal = document.getElementById('bulkModal');
        const title = document.getElementById('modalTitle');
        const message = document.getElementById('modalMessage');
        const submitBtn = document.getElementById('modalSubmit');
        const listingsDiv = document.getElementById('selectedListings');
        
        listingsDiv.innerHTML = '';
        selected.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'listings[]';
            input.value = id;
            listingsDiv.appendChild(input);
        });
        
        document.getElementById('bulkActionInput').value = action;
        
        switch(action) {
            case 'activate':
                title.textContent = 'Activate Products';
                message.textContent = `Are you sure you want to activate ${selected.length} product(s)?`;
                submitBtn.textContent = 'Activate Products';
                submitBtn.className = 'px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700';
                break;
            case 'deactivate':
                title.textContent = 'Deactivate Products';
                message.textContent = `Are you sure you want to deactivate ${selected.length} product(s)?`;
                submitBtn.textContent = 'Deactivate Products';
                submitBtn.className = 'px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700';
                break;
            case 'delete':
                title.textContent = 'Delete Products';
                message.textContent = `Are you sure you want to permanently delete ${selected.length} product(s)? This action cannot be undone.`;
                submitBtn.textContent = 'Delete Products';
                submitBtn.className = 'px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700';
                break;
        }
        
        modal.classList.remove('hidden');
    }
    
    function closeBulkModal() {
        document.getElementById('bulkModal').classList.add('hidden');
    }
    
    // Close modal when clicking outside
    document.getElementById('bulkModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeBulkModal();
        }
    });
</script>
@endsection