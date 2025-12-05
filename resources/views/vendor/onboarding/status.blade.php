@extends('layouts.vendor')

@section('title', 'Onboarding Status - JClone')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Status Header -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-2xl p-8 text-white mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">Vendor Application Status</h1>
                <p class="text-blue-100">Your application is being reviewed by our team</p>
            </div>
            <div class="bg-white/20 p-4 rounded-xl">
                <i class="fas fa-clipboard-check text-4xl"></i>
            </div>
        </div>
    </div>

    <!-- Status Card -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        <!-- Current Status -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Current Status</h3>
            
            @php
                $statusColors = [
                    'pending' => 'bg-yellow-100 text-yellow-800',
                    'approved' => 'bg-green-100 text-green-800',
                    'rejected' => 'bg-red-100 text-red-800',
                    'manual_review' => 'bg-blue-100 text-blue-800'
                ];
                
                $statusIcons = [
                    'pending' => 'fas fa-clock',
                    'approved' => 'fas fa-check-circle',
                    'rejected' => 'fas fa-times-circle',
                    'manual_review' => 'fas fa-user-check'
                ];
            @endphp
            
            <div class="flex items-center mb-4">
                <div class="mr-4">
                    <span class="{{ $statusColors[$vendorProfile->vetting_status] }} px-4 py-2 rounded-full font-bold">
                        <i class="{{ $statusIcons[$vendorProfile->vetting_status] }} mr-2"></i>
                        {{ strtoupper(str_replace('_', ' ', $vendorProfile->vetting_status)) }}
                    </span>
                </div>
            </div>
            
            <p class="text-gray-600 mb-4">
                @if($vendorProfile->vetting_status == 'pending')
                    Your application has been received and is in the queue for review.
                @elseif($vendorProfile->vetting_status == 'manual_review')
                    Your application requires additional manual verification.
                @elseif($vendorProfile->vetting_status == 'approved')
                    Congratulations! Your vendor account has been approved.
                @elseif($vendorProfile->vetting_status == 'rejected')
                    Your application was not approved at this time.
                @endif
            </p>
            
            @if($vendorProfile->vetting_notes)
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="font-medium text-gray-700">Admin Notes:</p>
                    <p class="text-gray-600">{{ $vendorProfile->vetting_notes }}</p>
                </div>
            @endif
        </div>

        <!-- Vendor Score -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Vendor Score</h3>
            
            @if($score)
                <div class="text-center mb-6">
                    <div class="inline-block relative">
                        <svg class="w-32 h-32" viewBox="0 0 36 36">
                            <path d="M18 2.0845
                                    a 15.9155 15.9155 0 0 1 0 31.831
                                    a 15.9155 15.9155 0 0 1 0 -31.831"
                                  fill="none" stroke="#e6e6e6" stroke-width="3"/>
                            <path d="M18 2.0845
                                    a 15.9155 15.9155 0 0 1 0 31.831
                                    a 15.9155 15.9155 0 0 1 0 -31.831"
                                  fill="none" stroke="#4f46e5" 
                                  stroke-width="3" stroke-dasharray="{{ $score->score }}, 100"/>
                        </svg>
                        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
                            <span class="text-3xl font-bold text-gray-800">{{ $score->score }}</span>
                            <span class="block text-sm text-gray-600">out of 100</span>
                        </div>
                    </div>
                </div>
                
                @if($score->factors)
                    <div class="space-y-3">
                        @foreach($score->factors as $factor => $value)
                            @if(is_bool($value) && $value)
                                <div class="flex items-center text-green-600">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    <span>{{ str_replace('_', ' ', ucfirst($factor)) }}</span>
                                </div>
                            @elseif(is_numeric($value))
                                <div class="flex justify-between items-center">
                                    <span>{{ str_replace('_', ' ', ucfirst($factor)) }}</span>
                                    <span class="font-bold">{{ $value }} points</span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif
            @else
                <p class="text-gray-500 text-center py-4">Score not available yet</p>
            @endif
        </div>

        <!-- Next Steps -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Next Steps</h3>
            
            <div class="space-y-4">
                @if($vendorProfile->vetting_status == 'pending')
                    <div class="flex items-start">
                        <div class="bg-blue-100 p-2 rounded-lg mr-3">
                            <i class="fas fa-clock text-blue-600"></i>
                        </div>
                        <div>
                            <p class="font-medium">Awaiting Review</p>
                            <p class="text-sm text-gray-600">Our team will review your documents within 24-48 hours</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="bg-gray-100 p-2 rounded-lg mr-3">
                            <i class="fas fa-check text-gray-400"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-400">Document Verification</p>
                            <p class="text-sm text-gray-400">Will begin after initial review</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="bg-gray-100 p-2 rounded-lg mr-3">
                            <i class="fas fa-check text-gray-400"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-400">Account Activation</p>
                            <p class="text-sm text-gray-400">You'll receive email notification</p>
                        </div>
                    </div>
                    
                @elseif($vendorProfile->vetting_status == 'approved')
                    <div class="bg-green-50 p-4 rounded-lg">
                        <div class="flex items-center mb-2">
                            <i class="fas fa-check-circle text-green-600 text-2xl mr-3"></i>
                            <h4 class="font-bold text-green-800">Account Approved!</h4>
                        </div>
                        <p class="text-green-700">Your vendor account is now active. You can start:</p>
                        <ul class="mt-2 space-y-2">
                            <li class="flex items-center">
                                <i class="fas fa-arrow-right text-green-500 mr-2"></i>
                                <a href="{{ route('vendor.listings.create') }}" class="text-green-600 hover:text-green-800">Create your first listing</a>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-arrow-right text-green-500 mr-2"></i>
                                <a href="{{ route('vendor.profile.show') }}" class="text-green-600 hover:text-green-800">Complete your store profile</a>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-arrow-right text-green-500 mr-2"></i>
                                <a href="{{ route('vendor.dashboard') }}" class="text-green-600 hover:text-green-800">Go to vendor dashboard</a>
                            </li>
                        </ul>
                    </div>
                    
                @elseif($vendorProfile->vetting_status == 'rejected')
                    <div class="bg-red-50 p-4 rounded-lg">
                        <div class="flex items-center mb-2">
                            <i class="fas fa-times-circle text-red-600 text-2xl mr-3"></i>
                            <h4 class="font-bold text-red-800">Application Rejected</h4>
                        </div>
                        <p class="text-red-700">Your application did not meet our requirements.</p>
                        <div class="mt-3">
                            <a href="{{ route('vendor.onboard.create') }}" 
                               class="inline-block bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
                                Re-apply with corrected documents
                            </a>
                        </div>
                    </div>
                    
                @elseif($vendorProfile->vetting_status == 'manual_review')
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <div class="flex items-center mb-2">
                            <i class="fas fa-user-check text-blue-600 text-2xl mr-3"></i>
                            <h4 class="font-bold text-blue-800">Manual Review Required</h4>
                        </div>
                        <p class="text-blue-700">Our team needs additional verification. You may be contacted for more information.</p>
                        <div class="mt-3">
                            <button onclick="uploadAdditional()" 
                                    class="inline-block bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                                Upload Additional Documents
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Uploaded Documents -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold text-gray-800">Uploaded Documents</h3>
            <button onclick="uploadAdditional()" 
                    class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                <i class="fas fa-upload mr-2"></i>Upload Additional
            </button>
        </div>
        
        @if($documents->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($documents as $document)
                    @php
                        $statusColors = [
                            'uploaded' => 'bg-yellow-100 text-yellow-800',
                            'verified' => 'bg-green-100 text-green-800',
                            'rejected' => 'bg-red-100 text-red-800'
                        ];
                        
                        $typeLabels = [
                            'national_id' => 'National ID',
                            'bank_statement' => 'Bank Statement',
                            'proof_of_address' => 'Proof of Address',
                            'guarantor_id' => 'Guarantor ID',
                            'company_docs' => 'Company Document',
                            'additional_id' => 'Additional ID',
                            'bank_statement_update' => 'Bank Statement Update',
                            'business_license' => 'Business License',
                            'other' => 'Other Document'
                        ];
                    @endphp
                    
                    <div class="border rounded-lg p-4 hover:shadow-md transition">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-medium">{{ $typeLabels[$document->type] ?? ucfirst($document->type) }}</span>
                            <span class="text-xs {{ $statusColors[$document->status] }} px-2 py-1 rounded-full">
                                {{ ucfirst($document->status) }}
                            </span>
                        </div>
                        
                        <p class="text-sm text-gray-600 mb-3">
                            {{ basename($document->path) }}
                        </p>
                        
                        <div class="flex space-x-2">
                            <a href="{{ Storage::url($document->path) }}" target="_blank" 
                               class="text-sm text-blue-600 hover:text-blue-800">
                                <i class="fas fa-eye mr-1"></i>View
                            </a>
                            <span class="text-gray-300">|</span>
                            <a href="{{ Storage::url($document->path) }}" download 
                               class="text-sm text-green-600 hover:text-green-800">
                                <i class="fas fa-download mr-1"></i>Download
                            </a>
                        </div>
                        
                        @if($document->ocr_data && isset($document->ocr_data['uploaded_at']))
                            <p class="text-xs text-gray-500 mt-2">
                                Uploaded: {{ \Carbon\Carbon::parse($document->ocr_data['uploaded_at'])->format('M d, Y') }}
                            </p>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500 text-center py-4">No documents uploaded yet</p>
        @endif
    </div>

    <!-- Additional Info -->
    <div class="bg-gray-50 rounded-xl p-6">
        <h3 class="text-xl font-bold text-gray-800 mb-4">Need Help?</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="text-center">
                <div class="bg-white p-4 rounded-xl inline-block mb-3">
                    <i class="fas fa-envelope text-indigo-600 text-2xl"></i>
                </div>
                <h4 class="font-bold mb-2">Email Support</h4>
                <p class="text-gray-600 text-sm">vendors@jclone.com</p>
            </div>
            
            <div class="text-center">
                <div class="bg-white p-4 rounded-xl inline-block mb-3">
                    <i class="fas fa-phone text-green-600 text-2xl"></i>
                </div>
                <h4 class="font-bold mb-2">Phone Support</h4>
                <p class="text-gray-600 text-sm">+256 700 123 456</p>
            </div>
            
            <div class="text-center">
                <div class="bg-white p-4 rounded-xl inline-block mb-3">
                    <i class="fas fa-comments text-purple-600 text-2xl"></i>
                </div>
                <h4 class="font-bold mb-2">Live Chat</h4>
                <p class="text-gray-600 text-sm">Available Mon-Fri, 9AM-5PM</p>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Additional Documents -->
<div id="additionalModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl p-8 max-w-md w-full mx-4">
        <h3 class="text-xl font-bold text-gray-800 mb-4">Upload Additional Document</h3>
        
        <form action="{{ route('vendor.onboard.additional') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="mb-4">
                <label class="block text-gray-700 mb-2">Document Type</label>
                <select name="document_type" required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                    <option value="">Select Type</option>
                    <option value="additional_id">Additional ID</option>
                    <option value="bank_statement_update">Updated Bank Statement</option>
                    <option value="business_license">Business License</option>
                    <option value="other">Other</option>
                </select>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 mb-2">Document</label>
                <input type="file" name="document" required 
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg"
                       accept=".jpg,.jpeg,.png,.pdf">
                <p class="text-sm text-gray-500 mt-1">Max file size: 5MB</p>
            </div>
            
            <div class="mb-6">
                <label class="block text-gray-700 mb-2">Description (Optional)</label>
                <textarea name="description" rows="3"
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg"
                          placeholder="Describe what this document is for..."></textarea>
            </div>
            
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeAdditionalModal()" 
                        class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit" 
                        class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                    Upload
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function uploadAdditional() {
        document.getElementById('additionalModal').classList.remove('hidden');
    }
    
    function closeAdditionalModal() {
        document.getElementById('additionalModal').classList.add('hidden');
    }
    
    // Close modal when clicking outside
    document.getElementById('additionalModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeAdditionalModal();
        }
    });
</script>
@endsection