@extends('layouts.vendor')

@section('title', 'Promotion Details - ' . $promotion->title)

@section('content')
<div class="container-fluid">
    <!-- Back Button -->
    <div class="mb-6">
        <a href="{{ route('vendor.promotions.index') }}" class="inline-flex items-center text-primary hover:text-indigo-700">
            <i class="fas fa-arrow-left mr-2"></i> Back to Promotions
        </a>
    </div>

    <!-- Promotion Header -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ $promotion->title }}</h1>
                <div class="flex items-center space-x-4">
                    <span class="px-3 py-1 text-sm font-medium rounded-full 
                        @if($promotion->status == 'active') bg-green-100 text-green-800
                        @elseif($promotion->status == 'pending') bg-yellow-100 text-yellow-800
                        @elseif($promotion->status == 'expired') bg-gray-100 text-gray-800
                        @else bg-red-100 text-red-800 @endif">
                        {{ ucfirst($promotion->status) }}
                    </span>
                    <span class="text-gray-600">
                        <i class="far fa-clock mr-1"></i> Created {{ $promotion->created_at->diffForHumans() }}
                    </span>
                </div>
            </div>
            
            <div class="mt-4 md:mt-0 space-x-3">
                @if($promotion->isActive() || $promotion->status == 'pending')
                <button onclick="showCancelModal()" 
                        class="px-4 py-2 border border-red-300 text-red-600 rounded-lg hover:bg-red-50">
                    <i class="fas fa-times mr-2"></i> Cancel Promotion
                </button>
                @endif
                
                @if($promotion->isActive())
                <button onclick="showExtendModal()" 
                        class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-indigo-700">
                    <i class="fas fa-calendar-plus mr-2"></i> Extend Promotion
                </button>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: Promotion Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Product Information -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-bold text-gray-900">Promoted Product</h2>
                </div>
                <div class="p-6">
                    <div class="flex items-start">
                        @if($promotion->listing->images->first())
                        <div class="flex-shrink-0 w-24 h-24 mr-6">
                            <img src="{{ asset('storage/' . $promotion->listing->images->first()->path) }}" 
                                 alt="{{ $promotion->listing->title }}" 
                                 class="w-24 h-24 object-cover rounded-lg">
                        </div>
                        @endif
                        
                        <div class="flex-1">
                            <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $promotion->listing->title }}</h3>
                            <p class="text-gray-600 mb-4">{{ $promotion->listing->description }}</p>
                            
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <div>
                                    <p class="text-sm text-gray-600">Price</p>
                                    <p class="text-lg font-bold text-primary">${{ number_format($promotion->listing->price, 2) }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Stock</p>
                                    <p class="font-medium text-gray-900">{{ $promotion->listing->stock }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Category</p>
                                    <p class="font-medium text-gray-900">{{ $promotion->listing->category->name ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Status</p>
                                    <span class="px-2 py-1 text-xs font-medium rounded-full 
                                        @if($promotion->listing->is_active) bg-green-100 text-green-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ $promotion->listing->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Promotion Timeline -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-bold text-gray-900">Promotion Timeline</h2>
                </div>
                <div class="p-6">
                    <div class="relative">
                        <!-- Timeline -->
                        <div class="flex items-center justify-between mb-8">
                            <div class="flex flex-col items-center">
                                <div class="w-8 h-8 rounded-full bg-green-500 flex items-center justify-center mb-2">
                                    <i class="fas fa-play text-white text-sm"></i>
                                </div>
                                <p class="text-sm font-medium text-gray-900">Start</p>
                                <p class="text-xs text-gray-500">{{ $promotion->starts_at->format('M d, Y') }}</p>
                            </div>
                            
                            <div class="flex-1 h-1 bg-gray-200 mx-4">
                                <div class="h-1 bg-green-500" style="width: {{ $promotion->isActive() ? '50%' : '100%' }}"></div>
                            </div>
                            
                            <div class="flex flex-col items-center">
                                <div class="w-8 h-8 rounded-full 
                                    @if($promotion->isActive()) border-2 border-gray-300
                                    @elseif($promotion->status == 'expired') bg-red-500
                                    @else bg-green-500 @endif flex items-center justify-center mb-2">
                                    <i class="fas 
                                        @if($promotion->isActive()) fa-clock text-gray-400
                                        @elseif($promotion->status == 'expired') fa-times text-white
                                        @else fa-check text-white @endif text-sm"></i>
                                </div>
                                <p class="text-sm font-medium text-gray-900">End</p>
                                <p class="text-xs text-gray-500">{{ $promotion->ends_at->format('M d, Y') }}</p>
                            </div>
                        </div>
                        
                        <!-- Progress Bar -->
                        <div class="mt-8">
                            <div class="flex justify-between text-sm text-gray-600 mb-1">
                                <span>Time Remaining</span>
                                <span>
                                    @if($promotion->isActive())
                                        {{ $promotion->ends_at->diffForHumans() }}
                                    @elseif($promotion->status == 'pending')
                                        Starts {{ $promotion->starts_at->diffForHumans() }}
                                    @else
                                        Ended {{ $promotion->ends_at->diffForHumans() }}
                                    @endif
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                @php
                                    $totalDuration = $promotion->starts_at->diffInDays($promotion->ends_at);
                                    $elapsedDuration = $promotion->starts_at->diffInDays(now());
                                    $progress = $totalDuration > 0 ? min(100, max(0, ($elapsedDuration / $totalDuration) * 100)) : 0;
                                @endphp
                                <div class="h-2 rounded-full 
                                    @if($promotion->isActive()) bg-green-500
                                    @elseif($promotion->status == 'pending') bg-yellow-500
                                    @else bg-gray-500 @endif" 
                                     style="width: {{ $progress }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Summary & Actions -->
        <div class="space-y-6">
            <!-- Promotion Summary -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-bold text-gray-900">Promotion Details</h2>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div>
                            <h3 class="text-sm font-medium text-gray-600">Type</h3>
                            <p class="font-medium text-gray-900">{{ $promotion->type_label }}</p>
                        </div>
                        
                        <div>
                            <h3 class="text-sm font-medium text-gray-600">Description</h3>
                            <p class="text-gray-900">{{ $promotion->description ?? 'No description' }}</p>
                        </div>
                        
                        <div>
                            <h3 class="text-sm font-medium text-gray-600">Duration</h3>
                            <p class="font-medium text-gray-900">
                                {{ $promotion->starts_at->format('M d, Y') }} - 
                                {{ $promotion->ends_at->format('M d, Y') }}
                            </p>
                        </div>
                        
                        @if($promotion->meta && isset($promotion->meta['discount']))
                        <div>
                            <h3 class="text-sm font-medium text-gray-600">Discount</h3>
                            <p class="font-medium text-gray-900">
                                {{ $promotion->meta['discount']['amount'] }}
                                {{ $promotion->meta['discount']['type'] == 'percentage' ? '%' : '$' }}
                            </p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Cost & Payment -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-bold text-gray-900">Cost & Payment</h2>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Promotion Fee</span>
                            <span class="font-medium">${{ number_format($promotion->fee, 2) }}</span>
                        </div>
                        
                        @if($promotion->meta && isset($promotion->meta['extensions']))
                        <div class="flex justify-between">
                            <span class="text-gray-600">Extensions</span>
                            <span class="font-medium">${{ number_format($promotion->meta['extensions']['extension_fee'], 2) }}</span>
                        </div>
                        @endif
                        
                        <div class="border-t pt-3">
                            <div class="flex justify-between font-bold text-lg">
                                <span>Total Cost</span>
                                <span class="text-primary">${{ number_format($promotion->fee + ($promotion->meta['extensions']['extension_fee'] ?? 0), 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Promotion Statistics (Placeholder) -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-bold text-gray-900">Performance</h2>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex justify-between">
                            <div>
                                <p class="text-sm text-gray-600">Impressions</p>
                                <p class="text-2xl font-bold text-gray-900">1,245</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Clicks</p>
                                <p class="text-2xl font-bold text-gray-900">89</p>
                            </div>
                        </div>
                        <div class="flex justify-between">
                            <div>
                                <p class="text-sm text-gray-600">Conversion</p>
                                <p class="text-2xl font-bold text-gray-900">12</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">CTR</p>
                                <p class="text-2xl font-bold text-gray-900">7.1%</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Modal -->
<div id="cancelModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4">
        <h3 class="text-lg font-bold text-gray-900 mb-2">Cancel Promotion</h3>
        <p class="text-gray-600 mb-4">Are you sure you want to cancel "{{ $promotion->title }}"?</p>
        <p class="text-sm text-gray-500 mb-6">
            <i class="fas fa-info-circle mr-2"></i>
            This action cannot be undone. You may not receive a refund.
        </p>
        
        <form action="{{ route('vendor.promotions.cancel', $promotion) }}" method="POST">
            @csrf
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeCancelModal()"
                        class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Keep Promotion
                </button>
                <button type="submit"
                        class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
                    Yes, Cancel Promotion
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Extend Modal -->
<div id="extendModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4">
        <h3 class="text-lg font-bold text-gray-900 mb-2">Extend Promotion</h3>
        <p class="text-gray-600 mb-4">Extend "{{ $promotion->title }}"</p>
        
        <form action="{{ route('vendor.promotions.extend', $promotion) }}" method="POST">
            @csrf
            <div class="mb-6">
                <label class="block text-gray-700 mb-2">Extension Days *</label>
                <select name="extension_days" required class="w-full border border-gray-300 rounded-lg p-2">
                    <option value="7">7 days - ${{ number_format($this->calculateExtensionFee($promotion->type, 7), 2) }}</option>
                    <option value="14">14 days - ${{ number_format($this->calculateExtensionFee($promotion->type, 14), 2) }}</option>
                    <option value="30">30 days - ${{ number_format($this->calculateExtensionFee($promotion->type, 30), 2) }}</option>
                </select>
                <p class="mt-1 text-sm text-gray-500">
                    New end date: {{ $promotion->ends_at->copy()->addDays(7)->format('M d, Y') }}
                </p>
            </div>
            
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeExtendModal()"
                        class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit"
                        class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                    Extend Promotion
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function showCancelModal() {
        document.getElementById('cancelModal').classList.remove('hidden');
    }
    
    function closeCancelModal() {
        document.getElementById('cancelModal').classList.add('hidden');
    }
    
    function showExtendModal() {
        document.getElementById('extendModal').classList.remove('hidden');
    }
    
    function closeExtendModal() {
        document.getElementById('extendModal').classList.add('hidden');
    }
    
    // Close modals when clicking outside
    document.getElementById('cancelModal').addEventListener('click', function(e) {
        if (e.target === this) closeCancelModal();
    });
    
    document.getElementById('extendModal').addEventListener('click', function(e) {
        if (e.target === this) closeExtendModal();
    });
</script>
@endsection