@extends('layouts.admin')

@section('title', 'Edit Category - Admin')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container--default .select2-selection--single {
        height: 42px;
        padding: 6px 12px;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 28px;
        padding-left: 0;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px;
    }
    .select2-dropdown {
        border-radius: 0.5rem;
        border: 1px solid #d1d5db;
    }
    .select2-results__option {
        padding: 8px 12px;
    }
    .select2-results__option--highlighted {
        background-color: #6366f1 !important;
    }
    .select2-search--dropdown .select2-search__field {
        padding: 8px 12px;
        border-radius: 0.375rem;
    }
    .level-0 { font-weight: 600; color: #1f2937; }
    .level-1 { padding-left: 15px; color: #4b5563; }
    .level-2 { padding-left: 30px; color: #6b7280; font-size: 0.9em; }
</style>
@endpush

@section('content')
<div class="p-8">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center mb-2">
            <a href="{{ route('admin.categories.index') }}" class="text-primary hover:text-indigo-700 mr-3">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="text-2xl font-bold text-gray-800">Edit Category</h1>
        </div>
        <p class="text-gray-600">Update category: <strong>{{ $category->name }}</strong></p>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow p-6 max-w-2xl">
        <form action="{{ route('admin.categories.update', $category) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <!-- Basic Information -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Category Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                Category Name *
                            </label>
                            <input type="text" name="name" id="name" required
                                   value="{{ old('name', $category->name) }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Parent Category (Select2 Searchable) -->
                        <div>
                            <label for="parent_id" class="block text-sm font-medium text-gray-700 mb-1">
                                Parent Category
                            </label>
                            <select name="parent_id" id="parent_id" class="w-full parent-select">
                                <option value="">None (Main Category)</option>
                                @foreach($allCategories as $cat)
                                <option value="{{ $cat['id'] }}"
                                        data-level="{{ $cat['level'] }}"
                                        data-parent="{{ $cat['parent'] ?? '' }}"
                                        {{ old('parent_id', $category->parent_id) == $cat['id'] ? 'selected' : '' }}>
                                    {{ $cat['display'] }}
                                </option>
                                @endforeach
                            </select>
                            @if($category->parent)
                            <p class="mt-1 text-sm text-green-600">
                                Currently under: <strong>{{ $category->parent->name }}</strong>
                            </p>
                            @endif
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mt-4">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                            Description
                        </label>
                        <textarea name="description" id="description" rows="3"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">{{ old('description', $category->description) }}</textarea>
                    </div>

                    <!-- Slug -->
                    <div class="mt-4">
                        <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">
                            URL Slug
                        </label>
                        <input type="text" id="slug"
                               value="{{ $category->slug }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50"
                               readonly disabled>
                        <p class="mt-1 text-sm text-gray-500">Auto-generated from name</p>
                    </div>
                </div>

                <!-- Display Settings -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Display Settings</h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Icon -->
                        <div>
                            <label for="icon" class="block text-sm font-medium text-gray-700 mb-1">
                                Icon
                            </label>
                            <div class="relative">
                                <input type="text" name="icon" id="icon"
                                       value="{{ old('icon', $category->icon) }}"
                                       class="w-full px-4 py-2 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                       placeholder="tag">
                                <div class="absolute left-3 top-2.5 text-gray-400">
                                    <i id="iconPreviewSmall" class="fas fa-{{ $category->icon ?? 'tag' }}"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Order -->
                        <div>
                            <label for="order" class="block text-sm font-medium text-gray-700 mb-1">
                                Display Order
                            </label>
                            <input type="number" name="order" id="order"
                                   value="{{ old('order', $category->order) }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Status
                            </label>
                            <div class="mt-2">
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="is_active" value="1"
                                           {{ old('is_active', $category->is_active) ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-primary focus:ring-primary">
                                    <span class="ml-2">Active</span>
                                </label>
                            </div>
                            <p class="mt-1 text-sm text-gray-500">
                                {{ $category->is_active ? 'Category is visible to users' : 'Category is hidden' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Category Statistics -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Category Statistics</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="text-center p-3 bg-white rounded-lg">
                            <div class="text-2xl font-bold text-primary">{{ $category->listings_count ?? 0 }}</div>
                            <div class="text-sm text-gray-600">Products</div>
                        </div>
                        <div class="text-center p-3 bg-white rounded-lg">
                            <div class="text-2xl font-bold text-green-600">
                                {{ \App\Models\Category::where('parent_id', $category->id)->count() }}
                            </div>
                            <div class="text-sm text-gray-600">Subcategories</div>
                        </div>
                        <div class="text-center p-3 bg-white rounded-lg">
                            <div class="text-2xl font-bold text-blue-600">{{ $category->order }}</div>
                            <div class="text-sm text-gray-600">Display Order</div>
                        </div>
                        <div class="text-center p-3 bg-white rounded-lg">
                            <div class="text-2xl font-bold text-purple-600">
                                {{ $category->created_at->format('M d, Y') }}
                            </div>
                            <div class="text-sm text-gray-600">Created</div>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                    <div>
                        <a href="{{ route('admin.categories.create', ['parent_id' => $category->id]) }}"
                           class="text-primary hover:text-indigo-700 font-medium">
                            <i class="fas fa-plus mr-1"></i> Add Subcategory
                        </a>
                    </div>

                    <div class="flex items-center space-x-4">
                        <a href="{{ route('admin.categories.index') }}"
                           class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                            Cancel
                        </a>
                        <button type="submit"
                                class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-indigo-700 font-medium">
                            <i class="fas fa-save mr-2"></i> Update Category
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Danger Zone -->
    <div class="mt-8 bg-white rounded-lg shadow p-6 max-w-2xl border border-red-200">
        <h3 class="text-lg font-medium text-red-700 mb-4">Danger Zone</h3>

        <div class="space-y-4">
            @if(($category->listings_count ?? 0) > 0)
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle text-yellow-600 mr-3"></i>
                    <div>
                        <h4 class="font-medium text-yellow-800">Cannot Delete Category</h4>
                        <p class="text-sm text-yellow-700">
                            This category contains {{ $category->listings_count }} products.
                            Move or delete products before deleting the category.
                        </p>
                    </div>
                </div>
            </div>
            @endif

            <div>
                <h4 class="font-medium text-gray-900 mb-2">Delete Category</h4>
                <p class="text-sm text-gray-600 mb-4">
                    Once deleted, this category cannot be recovered.
                    All subcategories will become main categories.
                </p>

                <form action="{{ route('admin.categories.destroy', $category) }}" method="POST"
                      onsubmit="return confirm('Are you sure you want to delete this category? This action cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                            {{ ($category->listings_count ?? 0) > 0 ? 'disabled' : '' }}>
                        <i class="fas fa-trash mr-2"></i> Delete Category
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Select2 for parent category
    $('#parent_id').select2({
        placeholder: 'Search for a category...',
        allowClear: true,
        width: '100%',
        templateResult: formatCategory,
        templateSelection: formatCategorySelection
    });

    function formatCategory(category) {
        if (!category.id) return category.text;

        var level = $(category.element).data('level') || 0;
        var parent = $(category.element).data('parent') || '';
        var levelClass = 'level-' + level;

        var html = '<div class="' + levelClass + '">' + category.text;
        if (parent && level > 0) {
            html += '<div class="text-xs text-gray-400 mt-0.5">in ' + parent + '</div>';
        }
        html += '</div>';

        return $(html);
    }

    function formatCategorySelection(category) {
        return category.text || 'None (Main Category)';
    }
});

// Icon preview
document.getElementById('icon').addEventListener('input', function() {
    var icon = this.value.trim();
    var preview = document.getElementById('iconPreviewSmall');
    if (preview && icon) {
        preview.className = 'fas fa-' + icon;
    }
});
</script>
@endpush
@endsection
