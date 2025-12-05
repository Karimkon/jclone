@extends('layouts.admin')

@section('title', 'Categories Management - Admin')

@section('content')
<div class="p-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Categories Management</h1>
            <p class="text-gray-600">Manage product categories and subcategories</p>
        </div>
        <a href="{{ route('admin.categories.create') }}" 
           class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-indigo-700 font-medium">
            <i class="fas fa-plus mr-2"></i> Add New Category
        </a>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-4 rounded-lg shadow">
            <div class="text-sm text-gray-600">Total Categories</div>
            <div class="text-2xl font-bold text-gray-800">{{ \App\Models\Category::count() }}</div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
            <div class="text-sm text-gray-600">Active Categories</div>
            <div class="text-2xl font-bold text-gray-800">{{ \App\Models\Category::where('is_active', true)->count() }}</div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
            <div class="text-sm text-gray-600">Parent Categories</div>
            <div class="text-2xl font-bold text-gray-800">{{ \App\Models\Category::whereNull('parent_id')->count() }}</div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
            <div class="text-sm text-gray-600">Products in Categories</div>
            <div class="text-2xl font-bold text-gray-800">{{ \App\Models\Listing::count() }}</div>
        </div>
    </div>

    <!-- Search & Filters -->
    <div class="mb-6 bg-white rounded-lg shadow p-4">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <!-- Search -->
            <div class="flex-1">
                <div class="relative">
                    <input type="text" placeholder="Search categories..." 
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                </div>
            </div>
            
            <!-- Filters -->
            <div class="flex items-center space-x-4">
                <select class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option>All Status</option>
                    <option>Active Only</option>
                    <option>Inactive Only</option>
                </select>
                
                <select class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option>All Types</option>
                    <option>Main Categories</option>
                    <option>Subcategories</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Categories Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Parent</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Products</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($categories as $category)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                @if($category->icon)
                                <div class="h-10 w-10 bg-primary text-white rounded-full flex items-center justify-center mr-3">
                                    <i class="fas fa-{{ $category->icon }}"></i>
                                </div>
                                @else
                                <div class="h-10 w-10 bg-gray-100 text-gray-600 rounded-full flex items-center justify-center mr-3">
                                    <i class="fas fa-tag"></i>
                                </div>
                                @endif
                                <div>
                                    <div class="text-sm font-medium text-gray-900">
                                        <a href="{{ route('admin.categories.edit', $category) }}" class="hover:text-primary">
                                            {{ $category->name }}
                                        </a>
                                    </div>
                                    <div class="text-sm text-gray-500">{{ $category->slug }}</div>
                                    @if($category->children_count > 0)
                                    <div class="text-xs text-blue-600 mt-1">
                                        <i class="fas fa-folder mr-1"></i> {{ $category->children_count }} subcategories
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($category->parent)
                            <div class="flex items-center">
                                <div class="h-8 w-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mr-2">
                                    <i class="fas fa-level-up-alt text-xs"></i>
                                </div>
                                <span class="text-sm text-gray-700">{{ $category->parent->name }}</span>
                            </div>
                            @else
                            <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">
                                <i class="fas fa-star mr-1"></i> Main
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-3 py-1 text-sm bg-gray-100 text-gray-800 rounded-full">
                                <i class="fas fa-box mr-1"></i> {{ $category->listings_count }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <form action="{{ route('admin.categories.toggle', $category) }}" method="POST">
                                @csrf
                                <button type="submit" class="focus:outline-none">
                                    @if($category->is_active)
                                    <span class="px-3 py-1 text-xs bg-green-100 text-green-800 rounded-full">
                                        <i class="fas fa-check-circle mr-1"></i> Active
                                    </span>
                                    @else
                                    <span class="px-3 py-1 text-xs bg-red-100 text-red-800 rounded-full">
                                        <i class="fas fa-times-circle mr-1"></i> Inactive
                                    </span>
                                    @endif
                                </button>
                            </form>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-3 py-1 text-sm bg-blue-100 text-blue-800 rounded-full">
                                {{ $category->order }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('admin.categories.edit', $category) }}" 
                                   class="text-indigo-600 hover:text-indigo-900 p-2 hover:bg-indigo-50 rounded-lg"
                                   title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                
                                <a href="{{ route('admin.categories.create', ['parent_id' => $category->id]) }}"
                                   class="text-green-600 hover:text-green-900 p-2 hover:bg-green-50 rounded-lg"
                                   title="Add Subcategory">
                                    <i class="fas fa-plus-circle"></i>
                                </a>
                                
                                <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" 
                                      onsubmit="return confirm('Delete this category?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="text-red-600 hover:text-red-900 p-2 hover:bg-red-50 rounded-lg"
                                            title="Delete"
                                            {{ $category->listings_count > 0 ? 'disabled' : '' }}>
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center">
                            <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                <i class="fas fa-tags text-gray-400 text-3xl"></i>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No categories found</h3>
                            <p class="text-gray-500 mb-4">Get started by creating your first category</p>
                            <a href="{{ route('admin.categories.create') }}" 
                               class="inline-flex items-center px-6 py-3 bg-primary text-white rounded-lg hover:bg-indigo-700 font-medium">
                                <i class="fas fa-plus mr-2"></i> Create Category
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($categories->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $categories->links() }}
        </div>
        @endif
    </div>

    <!-- Bulk Actions -->
    <div class="mt-6 bg-white rounded-lg shadow p-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-sm font-medium text-gray-700">Bulk Actions</h3>
                <p class="text-sm text-gray-500">Apply actions to multiple categories</p>
            </div>
            <div class="flex items-center space-x-4">
                <select class="px-4 py-2 border border-gray-300 rounded-lg">
                    <option>Select Action</option>
                    <option>Activate Selected</option>
                    <option>Deactivate Selected</option>
                    <option>Delete Selected</option>
                </select>
                <button class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-indigo-700">
                    Apply
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Category search functionality
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.querySelector('input[placeholder="Search categories..."]');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const rows = document.querySelectorAll('tbody tr');
                
                rows.forEach(row => {
                    const categoryName = row.querySelector('.text-sm.font-medium').textContent.toLowerCase();
                    const categorySlug = row.querySelector('.text-gray-500').textContent.toLowerCase();
                    const parentName = row.querySelector('.text-gray-700')?.textContent?.toLowerCase() || '';
                    
                    if (categoryName.includes(searchTerm) || 
                        categorySlug.includes(searchTerm) || 
                        parentName.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        }
        
        // Confirm before deletion
        const deleteForms = document.querySelectorAll('form[onsubmit*="confirm"]');
        deleteForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                if (!confirm('Are you sure you want to delete this category?')) {
                    e.preventDefault();
                }
            });
        });
    });
</script>
@endsection