@extends('layouts.app')

@section('title', 'Browse Services - BebaMart')

@section('content')
<!-- Hero -->
<section class="bg-gradient-to-br from-purple-600 to-indigo-700 text-white py-12">
    <div class="container mx-auto px-4">
        <div class="max-w-3xl mx-auto text-center">
            <h1 class="text-3xl md:text-4xl font-bold mb-4">Find Professional Services</h1>
            <p class="text-lg text-white/80 mb-6">Browse {{ $services->total() }} services from trusted providers</p>
            
            <!-- Search -->
            <form action="{{ route('services.index') }}" method="GET" class="max-w-2xl mx-auto">
                <div class="flex flex-col md:flex-row gap-2">
                    <div class="flex-1 relative">
                        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="q" value="{{ request('q') }}" 
                               placeholder="What service do you need?" 
                               class="w-full pl-12 pr-4 py-3 rounded-lg text-gray-800 focus:ring-2 focus:ring-white/50 outline-none">
                    </div>
                    <select name="city" class="px-4 py-3 rounded-lg text-gray-800 bg-white">
                        <option value="">All Cities</option>
                        @foreach($cities as $city)
                        <option value="{{ $city }}" {{ request('city') == $city ? 'selected' : '' }}>{{ $city }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="bg-white text-purple-600 px-6 py-3 rounded-lg font-semibold hover:bg-gray-100">
                        Search
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col lg:flex-row gap-8">
        <!-- Filters Sidebar -->
        <div class="lg:w-64 flex-shrink-0">
            <div class="bg-white rounded-lg shadow-sm p-4 sticky top-4">
                <h3 class="font-semibold text-gray-800 mb-4">Filters</h3>
                
                <form action="{{ route('services.index') }}" method="GET">
                    @if(request('q'))
                    <input type="hidden" name="q" value="{{ request('q') }}">
                    @endif
                    
                    <!-- Category -->
                    <div class="mb-4">
                        <label class="text-sm font-medium text-gray-700 block mb-2">Category</label>
                        <select name="category" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" onchange="this.form.submit()">
                            <option value="">All Categories</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }} ({{ $cat->services_count }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Price Range -->
                    <div class="mb-4">
                        <label class="text-sm font-medium text-gray-700 block mb-2">Price Range (UGX)</label>
                        <div class="flex gap-2">
                            <input type="number" name="price_min" value="{{ request('price_min') }}" 
                                   placeholder="Min" class="w-1/2 px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            <input type="number" name="price_max" value="{{ request('price_max') }}" 
                                   placeholder="Max" class="w-1/2 px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        </div>
                        <button type="submit" class="w-full mt-2 px-3 py-1.5 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200">
                            Apply
                        </button>
                    </div>
                    
                    @if(request()->hasAny(['category', 'price_min', 'price_max', 'city']))
                    <a href="{{ route('services.index') }}" class="text-sm text-red-600 hover:underline">
                        <i class="fas fa-times mr-1"></i> Clear Filters
                    </a>
                    @endif
                </form>
            </div>
        </div>
        
        <!-- Services Grid -->
        <div class="flex-1">
            <!-- Sort & Count -->
            <div class="flex items-center justify-between mb-4">
                <p class="text-gray-600">{{ $services->total() }} services found</p>
                <select onchange="window.location.href=this.value" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="{{ request()->fullUrlWithQuery(['sort' => 'popular']) }}" {{ request('sort', 'popular') == 'popular' ? 'selected' : '' }}>Most Popular</option>
                    <option value="{{ request()->fullUrlWithQuery(['sort' => 'newest']) }}" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest First</option>
                    <option value="{{ request()->fullUrlWithQuery(['sort' => 'price_low']) }}" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                    <option value="{{ request()->fullUrlWithQuery(['sort' => 'price_high']) }}" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                    <option value="{{ request()->fullUrlWithQuery(['sort' => 'rating']) }}" {{ request('sort') == 'rating' ? 'selected' : '' }}>Top Rated</option>
                </select>
            </div>
            
            <!-- Services Grid -->
            @if($services->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($services as $service)
                <div class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition group">
                    <!-- Image -->
                    <div class="h-44 bg-gray-200 relative overflow-hidden">
                        @if($service->primary_image)
                            <img src="{{ $service->primary_image }}" alt="{{ $service->title }}" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center">
                                <i class="fas fa-tools text-4xl text-white/50"></i>
                            </div>
                        @endif
                        
                        @if($service->is_featured)
                        <div class="absolute top-2 left-2">
                            <span class="px-2 py-1 bg-purple-500 text-white text-xs font-medium rounded-full">Featured</span>
                        </div>
                        @endif
                        
                        @if($service->is_mobile)
                        <div class="absolute top-2 right-2">
                            <span class="px-2 py-1 bg-green-500 text-white text-xs font-medium rounded-full">
                                <i class="fas fa-truck mr-1"></i> Mobile
                            </span>
                        </div>
                        @endif
                    </div>
                    
                    <!-- Content -->
                    <div class="p-4">
                        @if($service->category)
                        <span class="text-xs text-purple-600 font-medium">{{ $service->category->name }}</span>
                        @endif
                        
                        <a href="{{ route('services.show', $service->slug) }}" class="block mt-1">
                            <h3 class="font-semibold text-gray-900 hover:text-purple-600 line-clamp-2">
                                {{ $service->title }}
                            </h3>
                        </a>
                        
                        <!-- Vendor Info -->
                        <div class="flex items-center gap-2 mt-2">
                            <div class="w-6 h-6 rounded-full bg-gray-200 overflow-hidden">
                                @if($service->vendor->logo)
                                    <img src="{{ $service->vendor->logo }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full bg-purple-500 flex items-center justify-center text-white text-xs font-bold">
                                        {{ strtoupper(substr($service->vendor->business_name, 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                            <span class="text-sm text-gray-600 truncate">{{ $service->vendor->business_name }}</span>
                        </div>
                        
                        <!-- Location -->
                        <div class="text-sm text-gray-500 mt-2">
                            <i class="fas fa-map-marker-alt mr-1"></i> {{ $service->city }}
                            @if($service->location), {{ $service->location }}@endif
                        </div>
                        
                        <!-- Rating -->
                        @if($service->reviews_count > 0)
                        <div class="flex items-center gap-1 mt-2">
                            <i class="fas fa-star text-yellow-400 text-sm"></i>
                            <span class="text-sm font-medium">{{ number_format($service->average_rating, 1) }}</span>
                            <span class="text-sm text-gray-400">({{ $service->reviews_count }} reviews)</span>
                        </div>
                        @endif
                        
                        <!-- Price & Actions -->
                        <div class="flex items-center justify-between mt-4 pt-3 border-t">
                            <span class="font-bold text-purple-600">{{ $service->formatted_price }}</span>
                            <a href="{{ route('services.show', $service->slug) }}" 
                               class="text-sm text-purple-600 hover:underline font-medium">
                                View Details →
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            
            <div class="mt-6">
                {{ $services->links() }}
            </div>
            @else
            <div class="bg-white rounded-lg shadow-sm text-center py-12">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-tools text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No Services Found</h3>
                <p class="text-gray-500">Try adjusting your search or filters</p>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- CTA Section -->
<section class="bg-gray-100 py-12 mt-8">
    <div class="container mx-auto px-4 text-center">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Are You a Service Provider?</h2>
        <p class="text-gray-600 mb-6">Join BebaMart and reach thousands of customers looking for your services</p>
        <a href="{{ route('vendor.onboard.create') }}" class="inline-flex items-center px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
            <i class="fas fa-store mr-2"></i> Become a Vendor
        </a>
    </div>
</section>
@endsection