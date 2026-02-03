@extends('layouts.admin')

@section('title', 'Categories Missing Icons - Admin')

@push('styles')
<style>
    .icon-btn {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s;
    }
    .icon-btn:hover {
        border-color: #6366f1;
        background-color: #eef2ff;
    }
    .icon-btn.selected {
        border-color: #6366f1;
        background-color: #6366f1;
        color: white;
    }
    .category-row.saved {
        background-color: #ecfdf5 !important;
    }
</style>
@endpush

@section('content')
<div class="p-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <div class="flex items-center mb-2">
                <a href="{{ route('admin.categories.index') }}" class="text-primary hover:text-indigo-700 mr-3">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-2xl font-bold text-gray-800">Categories Missing Icons</h1>
            </div>
            <p class="text-gray-600">{{ $categories->count() }} categories need icons</p>
        </div>
    </div>

    @if($categories->count() == 0)
    <div class="bg-green-50 border border-green-200 rounded-lg p-8 text-center">
        <i class="fas fa-check-circle text-green-500 text-4xl mb-3"></i>
        <h3 class="text-lg font-medium text-green-800">All categories have icons!</h3>
        <p class="text-green-600 mt-2">Great job keeping your categories organized.</p>
        <a href="{{ route('admin.categories.index') }}" class="inline-block mt-4 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
            Back to Categories
        </a>
    </div>
    @else

    <!-- Common Icons Reference -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Common Icons (click to copy name)</h3>
        <div class="flex flex-wrap gap-2">
            @php
                $commonIcons = [
                    'tag' => 'Generic',
                    'utensils' => 'Food',
                    'hamburger' => 'Fast Food',
                    'pizza-slice' => 'Pizza',
                    'coffee' => 'Drinks',
                    'wine-glass' => 'Alcohol',
                    'apple-alt' => 'Fruits',
                    'carrot' => 'Vegetables',
                    'bread-slice' => 'Bakery',
                    'cookie' => 'Snacks',
                    'tshirt' => 'Clothing',
                    'shoe-prints' => 'Shoes',
                    'hat-wizard' => 'Hats',
                    'glasses' => 'Eyewear',
                    'gem' => 'Jewelry',
                    'ring' => 'Accessories',
                    'mobile-alt' => 'Phones',
                    'laptop' => 'Computers',
                    'tv' => 'Electronics',
                    'headphones' => 'Audio',
                    'camera' => 'Cameras',
                    'gamepad' => 'Gaming',
                    'car' => 'Cars',
                    'motorcycle' => 'Motorcycles',
                    'bicycle' => 'Bicycles',
                    'truck' => 'Trucks',
                    'ship' => 'Boats',
                    'plane' => 'Aviation',
                    'home' => 'Home',
                    'couch' => 'Furniture',
                    'bed' => 'Bedroom',
                    'bath' => 'Bathroom',
                    'blender' => 'Appliances',
                    'lightbulb' => 'Lighting',
                    'tools' => 'Tools',
                    'hammer' => 'Construction',
                    'paint-roller' => 'Paint',
                    'baby' => 'Baby',
                    'child' => 'Kids',
                    'futbol' => 'Sports',
                    'dumbbell' => 'Fitness',
                    'basketball-ball' => 'Basketball',
                    'guitar' => 'Music',
                    'book' => 'Books',
                    'graduation-cap' => 'Education',
                    'briefcase' => 'Business',
                    'building' => 'Real Estate',
                    'tractor' => 'Agriculture',
                    'paw' => 'Pets',
                    'heartbeat' => 'Health',
                    'pills' => 'Pharmacy',
                    'spa' => 'Beauty',
                    'cut' => 'Hair',
                    'gift' => 'Gifts',
                    'seedling' => 'Garden',
                    'leaf' => 'Organic',
                ];
            @endphp
            @foreach($commonIcons as $icon => $label)
            <button type="button" onclick="copyIcon('{{ $icon }}')"
                    class="flex items-center gap-2 px-3 py-1.5 bg-gray-50 hover:bg-primary hover:text-white rounded-lg text-sm transition"
                    title="{{ $label }}">
                <i class="fas fa-{{ $icon }}"></i>
                <span class="text-xs">{{ $icon }}</span>
            </button>
            @endforeach
        </div>
    </div>

    <!-- Categories Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Parent</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Select Icon</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Custom</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($categories as $category)
                <tr id="row-{{ $category->id }}" class="category-row hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div id="preview-{{ $category->id }}" class="h-10 w-10 bg-gray-100 text-gray-400 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-tag"></i>
                            </div>
                            <div>
                                <div class="font-medium text-gray-900">{{ $category->name }}</div>
                                <div class="text-xs text-gray-500">{{ $category->slug }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        @if($category->parent)
                            <span class="text-sm text-gray-600">{{ $category->parent->name }}</span>
                        @else
                            <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">Main</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex flex-wrap gap-1">
                            @php
                                // Suggest icons based on category name
                                $name = strtolower($category->name);
                                $suggestions = ['tag'];
                                if (str_contains($name, 'food') || str_contains($name, 'beverage') || str_contains($name, 'drink')) $suggestions = ['utensils', 'hamburger', 'coffee', 'wine-glass'];
                                elseif (str_contains($name, 'cloth') || str_contains($name, 'apparel') || str_contains($name, 'fashion')) $suggestions = ['tshirt', 'user-tie', 'vest'];
                                elseif (str_contains($name, 'shoe') || str_contains($name, 'foot')) $suggestions = ['shoe-prints', 'socks', 'hiking'];
                                elseif (str_contains($name, 'phone') || str_contains($name, 'mobile')) $suggestions = ['mobile-alt', 'phone', 'tablet-alt'];
                                elseif (str_contains($name, 'computer') || str_contains($name, 'laptop')) $suggestions = ['laptop', 'desktop', 'keyboard'];
                                elseif (str_contains($name, 'electronic') || str_contains($name, 'tech')) $suggestions = ['tv', 'plug', 'microchip'];
                                elseif (str_contains($name, 'car') || str_contains($name, 'vehicle') || str_contains($name, 'auto')) $suggestions = ['car', 'car-side', 'truck'];
                                elseif (str_contains($name, 'home') || str_contains($name, 'house') || str_contains($name, 'furniture')) $suggestions = ['home', 'couch', 'chair'];
                                elseif (str_contains($name, 'beauty') || str_contains($name, 'cosmetic')) $suggestions = ['spa', 'magic', 'kiss'];
                                elseif (str_contains($name, 'health') || str_contains($name, 'medical')) $suggestions = ['heartbeat', 'medkit', 'pills'];
                                elseif (str_contains($name, 'sport') || str_contains($name, 'fitness')) $suggestions = ['futbol', 'dumbbell', 'running'];
                                elseif (str_contains($name, 'kid') || str_contains($name, 'toy') || str_contains($name, 'baby')) $suggestions = ['baby', 'child', 'gamepad'];
                                elseif (str_contains($name, 'book') || str_contains($name, 'office')) $suggestions = ['book', 'briefcase', 'pen'];
                                elseif (str_contains($name, 'jewelry') || str_contains($name, 'watch') || str_contains($name, 'accessor')) $suggestions = ['gem', 'ring', 'glasses'];
                                elseif (str_contains($name, 'bag') || str_contains($name, 'luggage')) $suggestions = ['shopping-bag', 'suitcase', 'briefcase'];
                                elseif (str_contains($name, 'appliance')) $suggestions = ['blender', 'fan', 'tv'];
                                elseif (str_contains($name, 'tool') || str_contains($name, 'equipment')) $suggestions = ['tools', 'wrench', 'hammer'];
                                elseif (str_contains($name, 'real estate') || str_contains($name, 'property')) $suggestions = ['building', 'home', 'key'];
                                elseif (str_contains($name, 'agri') || str_contains($name, 'farm')) $suggestions = ['tractor', 'seedling', 'leaf'];
                                elseif (str_contains($name, 'craft') || str_contains($name, 'art')) $suggestions = ['palette', 'paint-brush', 'cut'];
                            @endphp
                            @foreach($suggestions as $icon)
                            <button type="button" onclick="selectIcon({{ $category->id }}, '{{ $icon }}')"
                                    class="icon-btn" id="btn-{{ $category->id }}-{{ $icon }}">
                                <i class="fas fa-{{ $icon }}"></i>
                            </button>
                            @endforeach
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <input type="text" id="input-{{ $category->id }}"
                               class="w-24 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary"
                               placeholder="icon name"
                               oninput="previewIcon({{ $category->id }}, this.value)">
                    </td>
                    <td class="px-6 py-4">
                        <button type="button" onclick="saveIcon({{ $category->id }})"
                                id="save-{{ $category->id }}"
                                class="px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:bg-indigo-700 disabled:opacity-50">
                            <i class="fas fa-save mr-1"></i> Save
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

@push('scripts')
<script>
let selectedIcons = {};

function copyIcon(icon) {
    navigator.clipboard.writeText(icon);
    alert('Copied: ' + icon);
}

function selectIcon(categoryId, icon) {
    selectedIcons[categoryId] = icon;
    document.getElementById('input-' + categoryId).value = icon;
    previewIcon(categoryId, icon);

    // Update button states
    document.querySelectorAll('[id^="btn-' + categoryId + '-"]').forEach(btn => {
        btn.classList.remove('selected');
    });
    const btn = document.getElementById('btn-' + categoryId + '-' + icon);
    if (btn) btn.classList.add('selected');
}

function previewIcon(categoryId, icon) {
    const preview = document.getElementById('preview-' + categoryId);
    if (icon) {
        preview.innerHTML = '<i class="fas fa-' + icon + '"></i>';
        preview.className = 'h-10 w-10 bg-primary text-white rounded-full flex items-center justify-center mr-3';
    } else {
        preview.innerHTML = '<i class="fas fa-tag"></i>';
        preview.className = 'h-10 w-10 bg-gray-100 text-gray-400 rounded-full flex items-center justify-center mr-3';
    }
    selectedIcons[categoryId] = icon;
}

function saveIcon(categoryId) {
    const icon = document.getElementById('input-' + categoryId).value.trim();
    if (!icon) {
        alert('Please enter or select an icon');
        return;
    }

    const btn = document.getElementById('save-' + categoryId);
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Saving...';

    fetch('/admin/categories/' + categoryId + '/icon', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ icon: icon })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            btn.innerHTML = '<i class="fas fa-check mr-1"></i> Saved!';
            btn.classList.remove('bg-primary', 'hover:bg-indigo-700');
            btn.classList.add('bg-green-600');
            document.getElementById('row-' + categoryId).classList.add('saved');

            // Fade out row after 2 seconds
            setTimeout(() => {
                document.getElementById('row-' + categoryId).style.opacity = '0.5';
            }, 2000);
        } else {
            alert('Error saving icon');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save mr-1"></i> Save';
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save mr-1"></i> Save';
    });
}
</script>
@endpush
@endsection
