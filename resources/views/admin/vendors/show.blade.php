@extends('layouts.admin')

@section('title', 'Vendor Details - ' . config('app.name'))
@section('page-title', 'Vendor Details')
@section('page-description', 'Review vendor application and documents')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <div class="mb-6">
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-primary">
                        <i class="fas fa-home mr-2"></i>
                        Dashboard
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400"></i>
                        <a href="{{ route('admin.vendors.pending') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-primary md:ml-2">Vendors</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400"></i>
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ $vendor->business_name }}</span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>

    <!-- Vendor Information Card -->
    <div class="bg-white rounded-lg shadow-sm mb-6">
        <div class="p-6 border-b">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 flex items-center">
                        {{ $vendor->business_name }}
                        @if($vendor->user->is_admin_verified)
                            <span class="inline-flex items-center justify-center w-5 h-5 ml-2 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 shadow-sm" title="Verified Seller">
                                <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                            </span>
                        @endif
                    </h2>
                    <div class="flex items-center mt-1">
                        <span class="text-sm text-gray-600 mr-3">
                            <i class="fas fa-user mr-1"></i>{{ $vendor->user->name }}
                        </span>
                        <span class="text-sm text-gray-600 mr-3">
                            <i class="fas fa-envelope mr-1"></i>{{ $vendor->user->email }}
                        </span>
                        <span class="text-sm text-gray-600">
                            <i class="fas fa-phone mr-1"></i>{{ $vendor->user->phone }}
                        </span>
                    </div>
                </div>
                
                @php
                    $statusColors = [
                        'pending' => 'bg-yellow-100 text-yellow-800',
                        'approved' => 'bg-green-100 text-green-800',
                        'rejected' => 'bg-red-100 text-red-800',
                        'under_review' => 'bg-blue-100 text-blue-800',
                    ];
                @endphp
                
                <span class="px-3 py-1 rounded-full text-sm font-medium {{ $statusColors[$vendor->vetting_status] ?? 'bg-gray-100 text-gray-800' }}">
                    {{ ucfirst(str_replace('_', ' ', $vendor->vetting_status)) }}
                </span>
            </div>
        </div>
        
        <!-- Vendor Details -->
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Business Information</h3>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Business Name</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $vendor->business_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Vendor Type</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            @switch($vendor->vendor_type)
                                @case('local_retail')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <i class="fas fa-store mr-1"></i> Local Retailer
                                    </span>
                                    @break
                                @case('china_supplier')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-plane mr-1"></i> International Supplier
                                    </span>
                                    @break
                                @case('dropship')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        <i class="fas fa-truck mr-1"></i> Dropshipper
                                    </span>
                                    @break
                            @endswitch
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Location</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $vendor->city }}, {{ $vendor->country }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Address</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $vendor->address }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Currency</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $vendor->preferred_currency }}</dd>
                    </div>
                    @if($vendor->annual_turnover)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Annual Turnover</dt>
                        <dd class="mt-1 text-sm text-gray-900">${{ number_format($vendor->annual_turnover, 2) }}</dd>
                    </div>
                    @endif
                </dl>
            </div>
            
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Application Details</h3>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Application Date</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $vendor->created_at->format('M d, Y h:i A') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $vendor->updated_at->format('M d, Y h:i A') }}</dd>
                    </div>
                    @if($vendor->vetting_notes)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Review Notes</dt>
                        <dd class="mt-1 text-sm text-gray-900 bg-gray-50 p-3 rounded">{{ $vendor->vetting_notes }}</dd>
                    </div>
                    @endif
                    
                    <!-- Vendor Score -->
                    @if($vendor->scores->count() > 0)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Current Score</dt>
                        <dd class="mt-1">
                            @php
                                $latestScore = $vendor->scores()->latest()->first();
                                $score = $latestScore->score ?? 0;
                            @endphp
                            <div class="flex items-center">
                                <div class="w-full bg-gray-200 rounded-full h-2.5 mr-3">
                                    <div class="bg-primary h-2.5 rounded-full" style="width: {{ $score }}%"></div>
                                </div>
                                <span class="text-sm font-bold text-gray-900">{{ $score }}/100</span>
                            </div>
                        </dd>
                    </div>
                    @endif
                    
                    <!-- Guarantor Info -->
                    @if($vendor->meta && isset($vendor->meta['guarantor']))
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Guarantor</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $vendor->meta['guarantor']['name'] }} 
                            ({{ $vendor->meta['guarantor']['phone'] }})
                        </dd>
                    </div>
                    @endif
                </dl>
            </div>
        </div>
    </div>

    <!-- Documents Section -->
    <div class="bg-white rounded-lg shadow-sm mb-6">
        <div class="p-6 border-b">
            <h3 class="text-lg font-semibold text-gray-900">Uploaded Documents</h3>
            <p class="text-sm text-gray-600 mt-1">Review and verify vendor documents</p>
        </div>
        
        <div class="p-6">
            @if($documents->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($documents as $document)
                    <div class="border border-gray-200 rounded-lg p-4 hover:border-gray-300 transition">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                @if(in_array($document->mime, ['image/jpeg', 'image/png', 'image/jpg']))
                                    <i class="fas fa-image text-indigo-500 text-2xl"></i>
                                @elseif($document->mime == 'application/pdf')
                                    <i class="fas fa-file-pdf text-red-500 text-2xl"></i>
                                @else
                                    <i class="fas fa-file text-gray-500 text-2xl"></i>
                                @endif
                            </div>
                            <div class="ml-3 flex-1">
                                <h4 class="font-medium text-gray-900">
                                    @php
                                        $docTypes = [
                                            'national_id' => 'National ID',
                                            'bank_statement' => 'Bank Statement',
                                            'proof_of_address' => 'Proof of Address',
                                            'guarantor_id' => 'Guarantor ID',
                                            'company_docs' => 'Company Document',
                                        ];
                                    @endphp
                                    {{ $docTypes[$document->type] ?? ucfirst($document->type) }}
                                    @if(isset($document->ocr_data['side']))
                                        <span class="text-sm text-gray-500">({{ ucfirst($document->ocr_data['side']) }})</span>
                                    @endif
                                </h4>
                                <p class="text-sm text-gray-500 mt-1">
                                    Uploaded: {{ $document->created_at->format('M d, Y') }}
                                </p>
                                <div class="flex items-center mt-2">
                                    @php
                                        $docStatusColors = [
                                            'uploaded' => 'bg-gray-100 text-gray-800',
                                            'verified' => 'bg-green-100 text-green-800',
                                            'rejected' => 'bg-red-100 text-red-800',
                                        ];
                                    @endphp
                                    <span class="text-xs px-2 py-1 rounded-full {{ $docStatusColors[$document->status] ?? 'bg-gray-100' }}">
                                        {{ ucfirst($document->status) }}
                                    </span>
                                    
                                    @if($document->ocr_data['verified_by'] ?? false)
                                    <span class="ml-2 text-xs text-gray-500">
                                        Verified by: {{ $document->ocr_data['verified_by'] }}
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4 flex space-x-2">
                            <a href="{{ route('admin.documents.view', $document->id) }}"
                               target="_blank"
                               class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50">
                                <i class="fas fa-eye mr-1"></i> View
                            </a>

                            @if(auth()->user()->role !== 'support')
                            @if($document->status != 'verified')
                            <form action="{{ route('admin.documents.verify', $document->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit"
                                        class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-lg text-white bg-green-600 hover:bg-green-700"
                                        onclick="return confirm('Verify this document?')">
                                    <i class="fas fa-check mr-1"></i> Verify
                                </button>
                            </form>
                            @endif

                            @if($document->status != 'rejected')
                            <button type="button"
                                    onclick="showRejectDocumentModal('{{ $document->id }}', '{{ $document->type }}')"
                                    class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-lg text-white bg-red-600 hover:bg-red-700">
                                <i class="fas fa-times mr-1"></i> Reject
                            </button>
                            @endif
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <i class="fas fa-file-archive text-4xl text-gray-300 mb-3"></i>
                    <p class="text-gray-500">No documents uploaded</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Vendor Actions -->
    <div class="bg-white rounded-lg shadow-sm">
        <div class="p-6 border-b">
            <h3 class="text-lg font-semibold text-gray-900">Vendor Actions</h3>
        </div>
        
        <div class="p-6">
            <div class="flex flex-wrap gap-3">
                @if(auth()->user()->role !== 'support')
                @if($vendor->vetting_status == 'pending' || $vendor->vetting_status == 'under_review')
                <!-- Approve Form -->
                <form action="{{ route('admin.vendors.approve', $vendor->id) }}" method="POST" class="inline">
                    @csrf
                    <div class="flex items-center">
                        <input type="number"
                               name="score"
                               min="0"
                               max="100"
                               value="50"
                               class="w-20 px-3 py-2 border border-gray-300 rounded-lg mr-2 text-center"
                               placeholder="Score">
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-green-600 hover:bg-green-700"
                                onclick="return confirm('Approve this vendor with the selected score?')">
                            <i class="fas fa-check mr-2"></i> Approve Vendor
                        </button>
                    </div>
                </form>

                <!-- Reject Button -->
                <button type="button"
                        onclick="showRejectModal('{{ $vendor->id }}', '{{ $vendor->business_name }}')"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-red-600 hover:bg-red-700">
                    <i class="fas fa-times mr-2"></i> Reject Application
                </button>
                @endif

                <!-- Update Score -->
                <button type="button"
                        onclick="showScoreUpdateModal('{{ $vendor->id }}', '{{ $vendor->scores()->latest()->first()->score ?? 0 }}')"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50">
                    <i class="fas fa-chart-line mr-2"></i> Update Score
                </button>

                <!-- Toggle Status -->
                <form action="{{ route('admin.vendors.toggleStatus', $vendor->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg {{ $vendor->user->is_active ? 'text-red-600 hover:bg-red-50' : 'text-green-600 hover:bg-green-50' }}"
                            onclick="return confirm('{{ $vendor->user->is_active ? 'Deactivate this vendor?' : 'Activate this vendor?' }}')">
                        <i class="fas fa-power-off mr-2"></i>
                        {{ $vendor->user->is_active ? 'Deactivate' : 'Activate' }}
                    </button>
                </form>

                <!-- Toggle Verified Badge -->
                <form action="{{ route('admin.users.toggle-verified', $vendor->user->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg {{ $vendor->user->is_admin_verified ? 'text-blue-600 bg-blue-50 hover:bg-blue-100' : 'text-gray-600 hover:bg-gray-50' }}"
                            onclick="return confirm('{{ $vendor->user->is_admin_verified ? 'Remove verified badge from this vendor?' : 'Give this vendor a verified badge (blue tick)?' }}')">
                        <i class="fas fa-check-circle mr-2"></i>
                        {{ $vendor->user->is_admin_verified ? 'Remove Verified' : 'Mark as Verified' }}
                    </button>
                </form>
                @endif
                
                <!-- Back Button -->
                <a href="{{ route('admin.vendors.pending') }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50">
                    <i class="fas fa-arrow-left mr-2"></i> Back to List
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Reject Application Modal -->
<div id="rejectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4">
        <h3 class="text-lg font-bold text-gray-900 mb-2">Reject Vendor Application</h3>
        <p class="text-gray-600 mb-4">You are rejecting: <span id="vendorName" class="font-semibold"></span></p>
        
        <form id="rejectForm" method="POST">
            @csrf
            <div class="mb-6">
                <label class="block text-gray-700 mb-2">Reason for rejection *</label>
                <textarea name="reason" rows="4" required
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                          placeholder="Explain why this application is being rejected..."></textarea>
                <p class="text-sm text-gray-500 mt-1">This will be sent to the vendor</p>
            </div>
            
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeRejectModal()"
                        class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit"
                        class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
                    Confirm Reject
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Reject Document Modal -->
<div id="rejectDocumentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4">
        <h3 class="text-lg font-bold text-gray-900 mb-2">Reject Document</h3>
        <p class="text-gray-600 mb-4">Document: <span id="documentType" class="font-semibold"></span></p>
        
        <form id="rejectDocumentForm" method="POST">
            @csrf
            <div class="mb-6">
                <label class="block text-gray-700 mb-2">Reason for rejection *</label>
                <textarea name="reason" rows="4" required
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                          placeholder="Explain why this document is being rejected..."></textarea>
                <p class="text-sm text-gray-500 mt-1">This will be sent to the vendor</p>
            </div>
            
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeRejectDocumentModal()"
                        class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit"
                        class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
                    Confirm Reject
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Update Score Modal -->
<div id="updateScoreModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4">
        <h3 class="text-lg font-bold text-gray-900 mb-2">Update Vendor Score</h3>
        
        <form id="updateScoreForm" method="POST">
            @csrf
            <div class="mb-6">
                <label class="block text-gray-700 mb-2">New Score (0-100) *</label>
                <input type="number" 
                       name="score" 
                       id="newScoreInput"
                       min="0" 
                       max="100" 
                       required
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
            </div>
            
            <div class="mb-6">
                <label class="block text-gray-700 mb-2">Notes (Optional)</label>
                <textarea name="notes" rows="3"
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                          placeholder="Reason for score update..."></textarea>
            </div>
            
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeUpdateScoreModal()"
                        class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit"
                        class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-700">
                    Update Score
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // Application Reject Modal
    function showRejectModal(vendorId, vendorName) {
        document.getElementById('vendorName').textContent = vendorName;
        document.getElementById('rejectForm').action = `/admin/vendors/${vendorId}/reject`;
        document.getElementById('rejectModal').classList.remove('hidden');
    }
    
    function closeRejectModal() {
        document.getElementById('rejectModal').classList.add('hidden');
        document.getElementById('rejectForm').reset();
    }
    
    // Document Reject Modal
    function showRejectDocumentModal(documentId, documentType) {
        document.getElementById('documentType').textContent = documentType;
        document.getElementById('rejectDocumentForm').action = `/admin/documents/${documentId}/reject`;
        document.getElementById('rejectDocumentModal').classList.remove('hidden');
    }
    
    function closeRejectDocumentModal() {
        document.getElementById('rejectDocumentModal').classList.add('hidden');
        document.getElementById('rejectDocumentForm').reset();
    }
    
    // Update Score Modal
    function showScoreUpdateModal(vendorId, currentScore) {
        document.getElementById('newScoreInput').value = currentScore;
        document.getElementById('updateScoreForm').action = `/admin/vendors/${vendorId}/update-score`;
        document.getElementById('updateScoreModal').classList.remove('hidden');
    }
    
    function closeUpdateScoreModal() {
        document.getElementById('updateScoreModal').classList.add('hidden');
        document.getElementById('updateScoreForm').reset();
    }
    
    // Close modals when clicking outside
    document.getElementById('rejectModal').addEventListener('click', function(e) {
        if (e.target === this) closeRejectModal();
    });
    
    document.getElementById('rejectDocumentModal').addEventListener('click', function(e) {
        if (e.target === this) closeRejectDocumentModal();
    });
    
    document.getElementById('updateScoreModal').addEventListener('click', function(e) {
        if (e.target === this) closeUpdateScoreModal();
    });
</script>
@endsection