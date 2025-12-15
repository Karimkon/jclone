@extends('layouts.vendor')

@section('title', 'Request Details - Vendor Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Back button -->
    <div class="mb-6">
        <a href="{{ route('vendor.services.requests') }}" class="text-blue-600 hover:underline">
            <i class="fas fa-arrow-left mr-2"></i> Back to Requests
        </a>
    </div>

    <!-- Main content -->
    <div class="grid lg:grid-cols-3 gap-6">
        <!-- Left Column -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Request Info -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 mb-2">Request #REQ{{ str_pad($request->id, 6, '0', STR_PAD_LEFT) }}</h1>
                        <div class="flex items-center gap-2">
                            @php
                                $statusColors = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'quoted' => 'bg-blue-100 text-blue-800',
                                    'in_progress' => 'bg-purple-100 text-purple-800',
                                    'completed' => 'bg-green-100 text-green-800',
                                    'cancelled' => 'bg-red-100 text-red-800'
                                ];
                            @endphp
                            <span class="px-3 py-1 text-sm font-medium rounded-full {{ $statusColors[$request->status] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                            </span>
                            <span class="text-sm text-gray-500">
                                <i class="far fa-calendar mr-1"></i>{{ $request->created_at->format('M d, Y') }}
                            </span>
                        </div>
                    </div>
                    
                    <!-- Action buttons based on status -->
                    @if($request->status === 'pending')
                    <button onclick="showQuoteModal()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        <i class="fas fa-file-invoice-dollar mr-2"></i>Submit Quote
                    </button>
                    @elseif($request->status === 'quoted')
                    <button onclick="showUpdateStatusModal('in_progress')" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                        <i class="fas fa-play mr-2"></i>Start Work
                    </button>
                    @elseif($request->status === 'in_progress')
                    <button onclick="showUpdateStatusModal('completed')" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        <i class="fas fa-check mr-2"></i>Mark Complete
                    </button>
                    @endif
                </div>

                <!-- Service Details -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">Service Details</h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex items-start gap-4">
                            @if($request->service && $request->service->images)
                            <div class="flex-shrink-0">
                                <img src="{{ asset('storage/' . $request->service->images[0]) }}" 
                                     alt="{{ $request->service->title }}" 
                                     class="w-20 h-20 rounded-lg object-cover">
                            </div>
                            @endif
                            <div>
                                <h4 class="font-semibold text-gray-900">{{ $request->service->title ?? 'Service Deleted' }}</h4>
                                <p class="text-sm text-gray-600 mt-1">{{ $request->service->description ?? '' }}</p>
                                @if($request->service)
                                <div class="flex items-center gap-4 mt-2 text-sm text-gray-500">
                                    <span><i class="fas fa-tag mr-1"></i> {{ $request->service->category->name ?? 'N/A' }}</span>
                                    <span><i class="fas fa-clock mr-1"></i> {{ $request->service->duration ?? 'N/A' }}</span>
                                    <span><i class="fas fa-map-marker-alt mr-1"></i> {{ $request->service->city ?? 'N/A' }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Customer Message -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">Customer Message</h3>
                    <div class="bg-blue-50 rounded-lg p-4">
                        <p class="text-gray-700">{{ $request->customer_message ?? 'No message provided.' }}</p>
                        @if($request->preferred_date)
                        <div class="mt-3 flex items-center gap-4 text-sm">
                            <span class="bg-white px-3 py-1 rounded-lg">
                                <i class="far fa-calendar-alt mr-1"></i>
                                Preferred Date: {{ \Carbon\Carbon::parse($request->preferred_date)->format('M d, Y') }}
                            </span>
                            @if($request->preferred_time)
                            <span class="bg-white px-3 py-1 rounded-lg">
                                <i class="far fa-clock mr-1"></i>
                                Preferred Time: {{ $request->preferred_time }}
                            </span>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Quote Section (if quoted) -->
                @if($request->status === 'quoted' || $request->status === 'in_progress' || $request->status === 'completed')
                <div id="quote" class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">Your Quote</h3>
                    <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                        <div class="flex justify-between items-center">
                            <div>
                                <div class="text-2xl font-bold text-gray-900">UGX {{ number_format($request->quoted_price) }}</div>
                                <p class="text-sm text-gray-600">Quoted on {{ $request->updated_at->format('M d, Y') }}</p>
                            </div>
                            <div class="text-right">
                                @if($request->status === 'quoted')
                                <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-medium">
                                    Awaiting Acceptance
                                </span>
                                @endif
                            </div>
                        </div>
                        @if($request->vendor_notes)
                        <div class="mt-4 pt-4 border-t border-green-200">
                            <p class="text-sm text-gray-700"><strong>Your notes:</strong> {{ $request->vendor_notes }}</p>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>

            <!-- Customer Info -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Customer Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Name</p>
                        <p class="font-medium text-gray-900">{{ $request->user->name ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Email</p>
                        <p class="font-medium text-gray-900">{{ $request->user->email ?? 'N/A' }}</p>
                    </div>
                    @if($request->customer_phone)
                    <div>
                        <p class="text-sm text-gray-500">Phone</p>
                        <p class="font-medium text-gray-900">{{ $request->customer_phone }}</p>
                    </div>
                    @endif
                    @if($request->customer_address)
                    <div>
                        <p class="text-sm text-gray-500">Address</p>
                        <p class="font-medium text-gray-900">{{ $request->customer_address }}</p>
                    </div>
                    @endif
                </div>
                
                <!-- Contact buttons -->
                <div class="flex gap-2 mt-6 pt-6 border-t border-gray-200">
                    @if($request->customer_phone)
                    <a href="tel:{{ $request->customer_phone }}" 
                       class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <i class="fas fa-phone mr-2"></i> Call Customer
                    </a>
                    <a href="https://wa.me/{{ $request->customer_phone }}" target="_blank"
                       class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        <i class="fab fa-whatsapp mr-2"></i> WhatsApp
                    </a>
                    @endif
                    <a href="mailto:{{ $request->user->email ?? '' }}"
                       class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                        <i class="fas fa-envelope mr-2"></i> Email
                    </a>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="space-y-6">
            <!-- Status Timeline -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Status Timeline</h3>
                <div class="space-y-4">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-check text-green-600 text-sm"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Request Submitted</p>
                            <p class="text-sm text-gray-500">{{ $request->created_at->format('M d, Y h:i A') }}</p>
                        </div>
                    </div>

                    @if($request->status === 'quoted' || $request->status === 'in_progress' || $request->status === 'completed')
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-file-invoice-dollar text-blue-600 text-sm"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Quote Submitted</p>
                            <p class="text-sm text-gray-500">{{ $request->updated_at->format('M d, Y h:i A') }}</p>
                            <p class="text-sm text-gray-600">UGX {{ number_format($request->quoted_price) }}</p>
                        </div>
                    </div>
                    @endif

                    @if($request->status === 'in_progress' || $request->status === 'completed')
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-play text-purple-600 text-sm"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Work Started</p>
                            <p class="text-sm text-gray-500">{{ $request->status_updated_at ?? 'N/A' }}</p>
                        </div>
                    </div>
                    @endif

                    @if($request->status === 'completed')
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-check-circle text-green-600 text-sm"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Completed</p>
                            <p class="text-sm text-gray-500">{{ $request->completed_at ? \Carbon\Carbon::parse($request->completed_at)->format('M d, Y h:i A') : 'N/A' }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Actions Card -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Quick Actions</h3>
                <div class="space-y-2">
                    @if($request->status === 'pending')
                    <button onclick="showQuoteModal()" 
                            class="w-full text-left px-4 py-3 bg-green-50 text-green-700 rounded-lg hover:bg-green-100">
                        <i class="fas fa-file-invoice-dollar mr-2"></i>Submit Quote
                    </button>
                    @endif
                    
                    @if($request->status === 'quoted')
                    <button onclick="showUpdateStatusModal('in_progress')"
                            class="w-full text-left px-4 py-3 bg-purple-50 text-purple-700 rounded-lg hover:bg-purple-100">
                        <i class="fas fa-play mr-2"></i>Start Work
                    </button>
                    @endif
                    
                    @if($request->status === 'in_progress')
                    <button onclick="showUpdateStatusModal('completed')"
                            class="w-full text-left px-4 py-3 bg-green-50 text-green-700 rounded-lg hover:bg-green-100">
                        <i class="fas fa-check mr-2"></i>Mark Complete
                    </button>
                    @endif
                    
                    <button onclick="showUpdateStatusModal('cancelled')"
                            class="w-full text-left px-4 py-3 bg-red-50 text-red-700 rounded-lg hover:bg-red-100">
                        <i class="fas fa-times mr-2"></i>Cancel Request
                    </button>
                    
                    <button onclick="printRequest()"
                            class="w-full text-left px-4 py-3 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100">
                        <i class="fas fa-print mr-2"></i>Print Details
                    </button>
                </div>
            </div>

            <!-- Review (if completed and reviewed) -->
            @if($request->review)
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Customer Review</h3>
                <div class="bg-yellow-50 rounded-lg p-4">
                    <div class="flex items-center gap-2 mb-2">
                        @for($i = 1; $i <= 5; $i++)
                        <i class="fas fa-star {{ $i <= $request->review->rating ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                        @endfor
                    </div>
                    <p class="text-gray-700 italic mb-2">"{{ $request->review->comment }}"</p>
                    <p class="text-sm text-gray-500">- {{ $request->user->name }}, {{ $request->review->created_at->format('M d, Y') }}</p>
                    
                    @if($request->review->vendor_response)
                    <div class="mt-3 pt-3 border-t border-yellow-200">
                        <p class="text-sm font-medium text-gray-700 mb-1">Your Response:</p>
                        <p class="text-sm text-gray-600">{{ $request->review->vendor_response }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Quote Modal -->
<div id="quoteModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="hideQuoteModal()"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 animate-scale-in">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-gray-900">Submit Quote</h3>
                <button onclick="hideQuoteModal()" class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center hover:bg-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="quoteForm" method="POST" action="{{ route('vendor.services.submit-quote', $request->id) }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Quoted Price (UGX) *</label>
                        <input type="number" name="quoted_price" required min="0" step="1000"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes to Customer (Optional)</label>
                        <textarea name="vendor_notes" rows="3"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                                  placeholder="Include any details about the quote..."></textarea>
                    </div>
                    <div class="bg-blue-50 rounded-lg p-3">
                        <p class="text-sm text-blue-700">
                            <i class="fas fa-info-circle mr-1"></i>
                            Customer will need to accept this quote before work can begin.
                        </p>
                    </div>
                </div>
                
                <div class="flex gap-2 mt-6">
                    <button type="button" onclick="hideQuoteModal()" 
                            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        Submit Quote
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Status Update Modal -->
<div id="statusModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="hideStatusModal()"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 animate-scale-in">
            <div class="flex items-center justify-between mb-4">
                <h3 id="statusModalTitle" class="text-xl font-bold text-gray-900"></h3>
                <button onclick="hideStatusModal()" class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center hover:bg-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="statusForm" method="POST">
                @csrf
                <input type="hidden" name="status" id="statusInput">
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
                        <textarea name="vendor_notes" rows="3"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                  placeholder="Add any notes..."></textarea>
                    </div>
                    
                    <div id="finalPriceSection" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Final Price (UGX)</label>
                        <input type="number" name="final_price" value="{{ $request->quoted_price }}" min="0"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                
                <div class="flex gap-2 mt-6">
                    <button type="button" onclick="hideStatusModal()" 
                            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Update Status
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showQuoteModal() {
    document.getElementById('quoteModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function hideQuoteModal() {
    document.getElementById('quoteModal').classList.add('hidden');
    document.body.style.overflow = '';
}

function showUpdateStatusModal(status) {
    const modal = document.getElementById('statusModal');
    const title = document.getElementById('statusModalTitle');
    const form = document.getElementById('statusForm');
    const statusInput = document.getElementById('statusInput');
    const finalPriceSection = document.getElementById('finalPriceSection');
    
    statusInput.value = status;
    
    // Set title based on status
    const titles = {
        'in_progress': 'Start Work',
        'completed': 'Mark as Complete',
        'cancelled': 'Cancel Request'
    };
    
    title.textContent = titles[status] || 'Update Status';
    
    // Show/hide final price for completion
    if (status === 'completed') {
        finalPriceSection.classList.remove('hidden');
    } else {
        finalPriceSection.classList.add('hidden');
    }
    
    // Set form action
    form.action = "{{ route('vendor.services.update-request-status', $request->id) }}";
    
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function hideStatusModal() {
    document.getElementById('statusModal').classList.add('hidden');
    document.body.style.overflow = '';
}

function printRequest() {
    window.print();
}

// Handle form submissions
document.getElementById('quoteForm')?.addEventListener('submit', function(e) {
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Submitting...';
});

document.getElementById('statusForm')?.addEventListener('submit', function(e) {
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Updating...';
});
</script>
@endsection