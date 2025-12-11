@extends('layouts.admin')

@section('title', 'Product Details: ' . $listing->title)
@section('page-title', 'Product Details')

@section('content')
<div class="max-w-6xl mx-auto">
    <!-- Header with Actions -->
    <div class="flex flex-wrap justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ $listing->title }}</h1>
            <p class="text-gray-600">SKU: {{ $listing->sku }} | Product ID: {{ $listing->id }}</p>
        </div>
        
        <div class="flex space-x-3">
            <a href="{{ route('admin.listings.edit', $listing) }}" 
               class="bg-primary text-white px-6 py-2 rounded-lg font-semibold hover:bg-indigo-700 transition flex items-center">
                <i class="fas fa-edit mr-2"></i> Edit
            </a>
            
            <form action="{{ route('admin.listings.toggle-status', $listing) }}" method="POST" class="inline">
                @csrf
                @method('POST')
                <button type="submit" 
                        class="bg-{{ $listing->is_active ? 'yellow' : 'green' }}-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-{{ $listing->is_active ? 'yellow' : 'green' }}-700 transition flex items-center">
                    <i class="fas fa-{{ $listing->is_active ? 'pause' : 'play' }} mr-2"></i> 
                    {{ $listing->is_active ? 'Deactivate' : 'Activate' }}
                </button>
            </form>
            
            <form action="{{ route('admin.listings.toggle-featured', $listing) }}" method="POST" class="inline">
                @csrf
                @method('POST')
                <button type="submit" 
                        class="bg-{{ $listing->is_featured ? 'gray' : 'purple' }}-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-{{ $listing->is_featured ? 'gray' : 'purple' }}-700 transition flex items-center">
                    <i class="fas fa-star mr-2"></i> 
                    {{ $listing->is_featured ? 'Unfeature' : 'Feature' }}
                </button>
            </form>
        </div>
    </div>

    <!-- Product Status Badges -->
    <div class="flex flex-wrap gap-2 mb-6">
        <span class="px-3 py-1 rounded-full text-sm font-medium {{ $listing->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
            {{ $listing->is_active ? 'Active' : 'Inactive' }}
        </span>
        @if($listing->is_featured)
        <span class="px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
            <i class="fas fa-star mr-1"></i> Featured
        </span>
        @endif
        <span class="px-3 py-1 rounded-full text-sm font-medium {{ $listing->origin == 'imported' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800' }}">
            {{ ucfirst($listing->origin) }}
        </span>
        <span class="px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
            {{ ucfirst($listing->condition) }}
        </span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: Images & Description -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Images -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Product Images</h3>
                
                @if($listing->images->count() > 0)
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    @foreach($listing->images as $image)
                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                        <img src="{{ Storage::url($image->path) }}" alt="Product image" 
                             class="w-full h-64 object-cover cursor-pointer" 
                             onclick="openImageModal('{{ Storage::url($image->path) }}')">
                        <div class="p-3 bg-gray-50">
                            <p class="text-sm text-gray-600">Image {{ $loop->iteration }}</p>
                            <p class="text-xs text-gray-500">Order: {{ $image->order }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-8">
                    <i class="fas fa-image text-4xl text-gray-300 mb-3"></i>
                    <p class="text-gray-500">No images uploaded</p>
                </div>
                @endif
            </div>

            <!-- Description -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Description</h3>
                <div class="prose max-w-none">
                    {!! nl2br(e($listing->description)) !!}
                </div>
            </div>

            <!-- Attributes -->
            @if(!empty($listing->attributes))
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Product Attributes</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($listing->attributes as $key => $value)
                        @if($value)
                        <div class="border border-gray-100 rounded-lg p-4">
                            <dt class="text-sm font-medium text-gray-500 uppercase">{{ $key }}</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900">{{ $value }}</dd>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column: Details & Stats -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Product Details -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Product Details</h3>
                
                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-gray-600">Price</p>
                        <p class="text-2xl font-bold text-gray-800">UGX {{ number_format($listing->price, 2) }}</p>
                        @if($listing->compare_at_price)
                        <p class="text-lg text-gray-500 line-through">UGX {{ number_format($listing->compare_at_price, 2) }}</p>
                        @endif
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Stock</p>
                            <p class="text-xl font-bold {{ $listing->stock > 10 ? 'text-green-600' : ($listing->stock > 0 ? 'text-yellow-600' : 'text-red-600') }}">
                                {{ $listing->stock }} units
                            </p>
                        </div>
                        
                        <div>
                            <p class="text-sm text-gray-600">Weight</p>
                            <p class="text-xl font-bold text-gray-800">{{ $listing->weight_kg }} kg</p>
                            @if($listing->weight_lbs)
                            <p class="text-sm text-gray-500">{{ $listing->weight_lbs }} lbs</p>
                            @endif
                        </div>
                    </div>
                    
                    <div class="border-t pt-4">
                        <p class="text-sm text-gray-600">Vendor</p>
                        <div class="flex items-center mt-2">
                            <div class="w-10 h-10 bg-primary text-white rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-store"></i>
                            </div>
                            <div>
                                <p class="font-medium">{{ $listing->vendor->business_name ?? 'N/A' }}</p>
                                <p class="text-sm text-gray-600">{{ $listing->vendor->user->email ?? '' }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-600">Category</p>
                        <p class="font-medium">{{ $listing->category->name ?? 'Uncategorized' }}</p>
                    </div>
                </div>
            </div>

            <!-- Sales Statistics -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Sales Statistics</h3>
                
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Total Orders</span>
                        <span class="font-bold text-gray-800">{{ $salesData['total_orders'] ?? 0 }}</span>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Total Quantity Sold</span>
                        <span class="font-bold text-gray-800">{{ $salesData['total_quantity'] ?? 0 }}</span>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Total Revenue</span>
                        <span class="font-bold text-green-600">UGX {{ number_format($salesData['total_revenue'] ?? 0, 2) }}</span>
                    </div>
                    
                    <div class="pt-4 border-t">
                        <p class="text-sm text-gray-600">Average Monthly Sales</p>
                        <p class="text-2xl font-bold text-gray-800">Coming Soon</p>
                    </div>
                </div>
            </div>

            <!-- Product Meta -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Product Meta</h3>
                
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Created Date</span>
                        <span class="font-medium">{{ $listing->created_at->format('M d, Y H:i') }}</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600">Last Updated</span>
                        <span class="font-medium">{{ $listing->updated_at->format('M d, Y H:i') }}</span>
                    </div>
                    
                    @if($listing->created_by_admin)
                    <div class="flex justify-between">
                        <span class="text-gray-600">Created By Admin</span>
                        <span class="font-medium text-green-600">Yes</span>
                    </div>
                    @endif
                    
                    @if($listing->last_updated_by_admin)
                    <div class="flex justify-between">
                        <span class="text-gray-600">Last Updated By Admin</span>
                        <span class="font-medium">{{ $listing->last_updated_by_admin->format('M d, Y H:i') }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Admin Notes -->
            @if($listing->admin_notes)
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Admin Notes</h3>
                <p class="text-gray-700">{{ $listing->admin_notes }}</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Image Modal -->
    <div id="imageModal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-75 items-center justify-center p-4">
        <div class="relative max-w-4xl max-h-screen">
            <button onclick="closeImageModal()" 
                    class="absolute top-4 right-4 bg-white text-gray-800 rounded-full w-10 h-10 flex items-center justify-center hover:bg-gray-100 transition z-10">
                <i class="fas fa-times"></i>
            </button>
            <img id="modalImage" src="" alt="Product image" class="max-w-full max-h-screen rounded-lg">
        </div>
    </div>
</div>

<script>
function openImageModal(src) {
    document.getElementById('modalImage').src = src;
    document.getElementById('imageModal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeImageModal() {
    document.getElementById('imageModal').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeImageModal();
    }
});

// Close modal when clicking outside image
document.getElementById('imageModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeImageModal();
    }
});
</script>

<style>
#imageModal {
    display: none;
}
#imageModal:not(.hidden) {
    display: flex;
}
</style>
@endsection