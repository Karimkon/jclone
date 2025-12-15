{{-- ============================================ --}}
{{-- Admin Analytics Dashboard View --}}
{{-- resources/views/admin/analytics/products/index.blade.php --}}
{{-- ============================================ --}}

@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Product Analytics</h1>
        
        <div class="flex gap-2">
            <select id="daysFilter" class="px-4 py-2 border rounded" onchange="filterByDays(this.value)">
                <option value="7" {{ $days == 7 ? 'selected' : '' }}>Last 7 days</option>
                <option value="30" {{ $days == 30 ? 'selected' : '' }}>Last 30 days</option>
                <option value="60" {{ $days == 60 ? 'selected' : '' }}>Last 60 days</option>
                <option value="90" {{ $days == 90 ? 'selected' : '' }}>Last 90 days</option>
            </select>
            
            <a href="{{ route('admin.analytics.export-clicked-not-bought', ['days' => $days]) }}" 
               class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                <i class="fas fa-download"></i> Export Report
            </a>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="text-gray-600 mb-2">Total Active Products</div>
            <div class="text-3xl font-bold">{{ number_format($totalListings) }}</div>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="text-gray-600 mb-2">Products with Views</div>
            <div class="text-3xl font-bold">{{ number_format($listingsWithViews) }}</div>
            <div class="text-sm text-gray-500">
                {{ $totalListings > 0 ? round(($listingsWithViews / $totalListings) * 100, 1) : 0 }}% of total
            </div>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="text-gray-600 mb-2">Products with Sales</div>
            <div class="text-3xl font-bold">{{ number_format($listingsWithPurchases) }}</div>
            <div class="text-sm text-gray-500">
                {{ $totalListings > 0 ? round(($listingsWithPurchases / $totalListings) * 100, 1) : 0 }}% conversion
            </div>
        </div>
    </div>

    {{-- Trending Products --}}
    <div class="bg-white rounded-lg shadow mb-8">
        <div class="p-6 border-b">
            <h2 class="text-xl font-semibold">🔥 Trending Products (Last 7 Days)</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Views</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Clicks</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unique Visitors</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($trending as $product)
                    <tr>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                @if($product->images->first())
                                <img src="{{ asset('storage/' . $product->images->first()->path) }}" 
                                     class="w-12 h-12 object-cover rounded">
                                @endif
                                <div>
                                    <div class="font-medium">{{ Str::limit($product->title, 50) }}</div>
                                    <div class="text-sm text-gray-500">{{ $product->vendor->business_name }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">{{ number_format($product->recent_views) }}</td>
                        <td class="px-6 py-4">{{ number_format($product->recent_clicks) }}</td>
                        <td class="px-6 py-4">{{ number_format($product->unique_visitors) }}</td>
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.analytics.products.show', $product->id) }}" 
                               class="text-blue-600 hover:text-blue-800">
                                View Details
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">No trending products</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Products Clicked But Not Bought --}}
    <div class="bg-white rounded-lg shadow mb-8">
        <div class="p-6 border-b">
            <h2 class="text-xl font-semibold">⚠️ Products Clicked But Not Purchased</h2>
            <p class="text-sm text-gray-600 mt-1">Products with high interest but low conversion</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Clicks</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cart Adds</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Purchases</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Conversion</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($clickedNotBought as $product)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="font-medium">{{ Str::limit($product->title, 40) }}</div>
                            <div class="text-sm text-gray-500">{{ $product->vendor->business_name }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="font-semibold">{{ number_format($product->total_clicks) }}</span>
                        </td>
                        <td class="px-6 py-4">{{ number_format($product->cart_adds) }}</td>
                        <td class="px-6 py-4">{{ number_format($product->total_purchases) }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded {{ $product->conversion_rate < 2 ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ $product->conversion_rate }}%
                            </span>
                        </td>
                        <td class="px-6 py-4">${{ number_format($product->price, 2) }}</td>
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.analytics.products.show', $product->id) }}" 
                               class="text-blue-600 hover:text-blue-800 mr-3">
                                Details
                            </a>
                            <a href="{{ route('admin.listings.edit', $product->id) }}" 
                               class="text-green-600 hover:text-green-800">
                                Edit
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                            No products found with this criteria
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Cart Abandonment --}}
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <h2 class="text-xl font-semibold">🛒 High Cart Abandonment Products</h2>
            <p class="text-sm text-gray-600 mt-1">Products frequently added to cart but not purchased</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cart Adds</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Purchases</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Abandon Rate</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($cartAbandonment->take(15) as $product)
                    <tr>
                        <td class="px-6 py-4">
                            <div class="font-medium">{{ Str::limit($product->title, 40) }}</div>
                            <div class="text-sm text-gray-500">{{ $product->category->name ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4">{{ number_format($product->cart_adds) }}</td>
                        <td class="px-6 py-4">{{ number_format($product->purchases) }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded {{ $product->abandon_rate > 75 ? 'bg-red-100 text-red-800' : 'bg-orange-100 text-orange-800' }}">
                                {{ number_format($product->abandon_rate, 1) }}%
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.analytics.products.show', $product->id) }}" 
                               class="text-blue-600 hover:text-blue-800">
                                View Details
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">No data available</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function filterByDays(days) {
    window.location.href = '{{ route('admin.analytics.products.index') }}?days=' + days;
}
</script>
@endsection