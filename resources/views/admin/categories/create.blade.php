@extends('layouts.admin')

@section('title', 'Create Category - Admin')

@section('content')
<div class="p-8">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center mb-2">
            <a href="{{ route('admin.categories.index') }}" class="text-primary hover:text-indigo-700 mr-3">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="text-2xl font-bold text-gray-800">Create New Category</h1>
        </div>
        <p class="text-gray-600">Add a new category or subcategory to organize products</p>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow p-6 max-w-2xl">
        <form action="{{ route('admin.categories.store') }}" method="POST">
            @csrf
            
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
                                   value="{{ old('name') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                   placeholder="e.g., Electronics, Clothing, Furniture">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Parent Category -->
                        <div>
                            <label for="parent_id" class="block text-sm font-medium text-gray-700 mb-1">
                                Parent Category
                            </label>
                            <select name="parent_id" id="parent_id"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="">None (Main Category)</option>
                                @foreach($parentCategories as $parent)
                                <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                                    {{ $parent->name }}
                                </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-sm text-gray-500">Select parent for subcategory</p>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mt-4">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                            Description
                        </label>
                        <textarea name="description" id="description" rows="3"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                  placeholder="Describe this category...">{{ old('description') }}</textarea>
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
                                       value="{{ old('icon') }}"
                                       class="w-full px-4 py-2 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                       placeholder="tag">
                                <div class="absolute left-3 top-2.5 text-gray-400">
                                    <i class="fas fa-icons"></i>
                                </div>
                            </div>
                            <p class="mt-1 text-sm text-gray-500">Font Awesome icon name</p>
                        </div>

                        <!-- Order -->
                        <div>
                            <label for="order" class="block text-sm font-medium text-gray-700 mb-1">
                                Display Order
                            </label>
                            <input type="number" name="order" id="order"
                                   value="{{ old('order', 0) }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <p class="mt-1 text-sm text-gray-500">Lower numbers display first</p>
                        </div>

                        <!-- Status -->
                        <div>
                            <label for="is_active" class="block text-sm font-medium text-gray-700 mb-1">
                                Status
                            </label>
                            <div class="mt-2">
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="is_active" id="is_active" value="1" 
                                           {{ old('is_active', true) ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-primary focus:ring-primary">
                                    <span class="ml-2">Active</span>
                                </label>
                            </div>
                            <p class="mt-1 text-sm text-gray-500">Show category to users</p>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                    <a href="{{ route('admin.categories.index') }}" 
                       class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit"
                            class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-indigo-700 font-medium">
                        <i class="fas fa-save mr-2"></i> Create Category
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Icon Preview -->
    <div class="mt-8 bg-white rounded-lg shadow p-6 max-w-2xl">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Icon Preview</h3>
        <div class="grid grid-cols-6 gap-4">
            @php
                $commonIcons = ['tag', 'tv', 'tshirt', 'mobile-alt', 'laptop', 'home', 
                              'car', 'utensils', 'book', 'music', 'camera', 'gamepad'];
            @endphp
            @foreach($commonIcons as $icon)
            <button type="button" 
                    onclick="document.getElementById('icon').value = '{{ $icon }}'"
                    class="p-4 border border-gray-200 rounded-lg hover:border-primary hover:bg-primary/5 text-center group">
                <i class="fas fa-{{ $icon }} text-2xl text-gray-600 group-hover:text-primary mb-2"></i>
                <div class="text-xs text-gray-500">{{ $icon }}</div>
            </button>
            @endforeach
        </div>
    </div>
</div>

<script>
    // Live preview of selected icon
    const iconInput = document.getElementById('icon');
    const iconPreview = document.getElementById('iconPreview');
    
    if (iconInput && iconPreview) {
        iconInput.addEventListener('input', function() {
            const icon = this.value.trim();
            iconPreview.innerHTML = icon ? `<i class="fas fa-${icon} text-4xl text-primary"></i>` : '';
        });
    }
</script>
@endsection