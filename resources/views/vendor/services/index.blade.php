@extends('layouts.vendor')

@section('title', 'My Services - Vendor Dashboard')
@section('page_title', 'My Services')

@section('content')
<!-- Stats -->
<div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
    <div class="bg-white rounded-lg p-4 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Total Services</p>
                <p class="text-2xl font-bold text-gray-800">{{ $stats['total'] }}</p>
            </div>
            <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                <i class="fas fa-tools text-purple-600"></i>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg p-4 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Active</p>
                <p class="text-2xl font-bold text-green-600">{{ $stats['active'] }}</p>
            </div>
            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                <i class="fas fa-check text-green-600"></i>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg p-4 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Total Requests</p>
                <p class="text-2xl font-bold text-blue-600">{{ $stats['total_requests'] }}</p>
            </div>
            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                <i class="fas fa-clipboard-list text-blue-600"></i>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg p-4 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Pending</p>
                <p class="text-2xl font-bold text-orange-600">{{ $stats['pending_requests'] }}</p>
            </div>
            <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center">
                <i class="fas fa-clock text-orange-600"></i>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg p-4 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">New Inquiries</p>
                <p class="text-2xl font-bold text-purple-600">{{ $stats['new_inquiries'] }}</p>
            </div>
            <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                <i class="fas fa-envelope text-purple-600"></i>
            </div>
        </div>
    </div>
</div>

<!-- Actions -->
<div class="bg-white rounded-lg p-4 shadow-sm mb-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex items-center gap-2">
            <a href="{{ route('vendor.services.index') }}" 
               class="px-3 py-1.5 rounded-lg text-sm {{ !request('status') ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                All Services
            </a>
            <a href="{{ route('vendor.services.index', ['status' => 'active']) }}" 
               class="px-3 py-1.5 rounded-lg text-sm {{ request('status') == 'active' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                Active
            </a>
            <a href="{{ route('vendor.services.index', ['status' => 'inactive']) }}" 
               class="px-3 py-1.5 rounded-lg text-sm {{ request('status') == 'inactive' ? 'bg-gray-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                Inactive
            </a>
        </div>
        
        <a href="{{ route('vendor.services.create') }}" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 inline-flex items-center">
            <i class="fas fa-plus mr-2"></i> Add New Service
        </a>
    </div>
</div>

<!-- Services Grid -->
@if($services->count() > 0)
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @foreach($services as $service)
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <!-- Image -->
        <div class="h-40 bg-gray-200 relative">
            @if($service->primary_image)
                <img src="{{ $service->primary_image }}" alt="{{ $service->title }}" class="w-full h-full object-cover">
            @else
                <div class="w-full h-full bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center">
                    <i class="fas fa-tools text-4xl text-white/50"></i>
                </div>
            @endif
            
            <!-- Status Badge -->
            <div class="absolute top-2 right-2">
                @if($service->is_active)
                <span class="px-2 py-1 bg-green-500 text-white text-xs rounded-full">Active</span>
                @else
                <span class="px-2 py-1 bg-gray-500 text-white text-xs rounded-full">Inactive</span>
                @endif
            </div>
        </div>
        
        <!-- Content -->
        <div class="p-4">
            <div class="flex items-start justify-between gap-2 mb-2">
                <h3 class="font-semibold text-gray-900 line-clamp-2">{{ $service->title }}</h3>
            </div>
            
            @if($service->category)
            <span class="text-xs text-purple-600">{{ $service->category->name }}</span>
            @endif
            
            <div class="flex items-center gap-2 mt-2 text-sm text-gray-500">
                <i class="fas fa-map-marker-alt"></i>
                <span>{{ $service->city }}</span>
                @if($service->is_mobile)
                <span class="text-green-600"><i class="fas fa-truck ml-2"></i> Mobile</span>
                @endif
            </div>
            
            <div class="mt-3 text-lg font-bold text-purple-600">
                {{ $service->formatted_price }}
            </div>
            
            <!-- Stats -->
            <div class="flex items-center gap-4 mt-3 pt-3 border-t text-sm text-gray-500">
                <span><i class="fas fa-eye mr-1"></i> {{ $service->views_count }}</span>
                <span><i class="fas fa-clipboard-list mr-1"></i> {{ $service->requests_count ?? 0 }}</span>
                <span><i class="fas fa-envelope mr-1"></i> {{ $service->inquiries_count ?? 0 }}</span>
                @if($service->average_rating > 0)
                <span><i class="fas fa-star text-yellow-400 mr-1"></i> {{ number_format($service->average_rating, 1) }}</span>
                @endif
            </div>
            
            <!-- Actions -->
            <div class="flex items-center gap-2 mt-4">
                <a href="{{ route('vendor.services.edit', $service) }}" 
                   class="flex-1 text-center px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm">
                    <i class="fas fa-edit mr-1"></i> Edit
                </a>
                <form action="{{ route('vendor.services.toggle', $service) }}" method="POST" class="flex-1">
                    @csrf
                    <button type="submit" 
                            class="w-full px-3 py-2 {{ $service->is_active ? 'bg-yellow-100 text-yellow-700 hover:bg-yellow-200' : 'bg-green-100 text-green-700 hover:bg-green-200' }} rounded-lg text-sm">
                        <i class="fas {{ $service->is_active ? 'fa-pause' : 'fa-play' }} mr-1"></i> 
                        {{ $service->is_active ? 'Pause' : 'Activate' }}
                    </button>
                </form>
                <form action="{{ route('vendor.services.destroy', $service) }}" method="POST" onsubmit="return confirm('Delete this service?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-3 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 text-sm">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
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
    <h3 class="text-lg font-medium text-gray-900 mb-2">No Services Yet</h3>
    <p class="text-gray-500 mb-4">Start offering services to customers</p>
    <a href="{{ route('vendor.services.create') }}" class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
        <i class="fas fa-plus mr-2"></i> Add Your First Service
    </a>
</div>
@endif
@endsection
