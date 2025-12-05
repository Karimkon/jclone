@extends('layouts.app')

@section('title', 'Application Status - JClone')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-12 px-4">
    <div class="max-w-4xl mx-auto">
        
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">Vendor Application Status</h1>
            <p class="text-gray-600 text-lg">Track the progress of your vendor application</p>
        </div>

        <!-- Main Card -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="p-8">
                
                <!-- Success Message -->
                @if(session('success'))
                <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-8">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-green-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Application Status -->
                <div class="mb-10">
                    <h2 class="text-xl font-bold text-gray-800 mb-6 pb-3 border-b">Application Status</h2>
                    
                    @php
                        $statusColors = [
                            'pending' => 'bg-yellow-100 text-yellow-800',
                            'approved' => 'bg-green-100 text-green-800',
                            'rejected' => 'bg-red-100 text-red-800',
                            'under_review' => 'bg-blue-100 text-blue-800',
                        ];
                        
                        $statusIcons = [
                            'pending' => 'fas fa-clock',
                            'approved' => 'fas fa-check-circle',
                            'rejected' => 'fas fa-times-circle',
                            'under_review' => 'fas fa-search',
                        ];
                        
                        $statusMessages = [
                            'pending' => 'Your application is pending review',
                            'approved' => 'Your application has been approved!',
                            'rejected' => 'Your application was rejected',
                            'under_review' => 'Your application is under review',
                        ];
                    @endphp
                    
                    <div class="flex items-center justify-between p-6 bg-gray-50 rounded-xl">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full {{ $statusColors[$vendorProfile->vetting_status] ?? 'bg-gray-100 text-gray-800' }}">
                                <i class="{{ $statusIcons[$vendorProfile->vetting_status] ?? 'fas fa-info-circle' }} text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    {{ ucfirst(str_replace('_', ' ', $vendorProfile->vetting_status)) }}
                                </h3>
                                <p class="text-gray-600">
                                    {{ $statusMessages[$vendorProfile->vetting_status] ?? 'Status unknown' }}
                                </p>
                            </div>
                        </div>
                        
                        @if($vendorProfile->vetting_status === 'pending')
                        <div class="animate-pulse">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                <span class="w-2 h-2 bg-yellow-400 rounded-full mr-2"></span>
                                Processing
                            </span>
                        </div>
                        @endif
                    </div>
                    
                    <!-- Status Notes -->
                    @if($vendorProfile->vetting_notes)
                    <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <h4 class="font-semibold text-blue-800 mb-2">Review Notes:</h4>
                        <p class="text-blue-700">{{ $vendorProfile->vetting_notes }}</p>
                    </div>
                    @endif
                </div>

                <!-- Business Information -->
                <div class="mb-10">
                    <h2 class="text-xl font-bold text-gray-800 mb-6 pb-3 border-b">Business Information</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Business Name</label>
                            <p class="text-gray-900 font-medium">{{ $vendorProfile->business_name }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Vendor Type</label>
                            <p class="text-gray-900 font-medium">
                                @switch($vendorProfile->vendor_type)
                                    @case('local_retail')
                                        Local Retailer
                                        @break
                                    @case('china_supplier')
                                        International Supplier
                                        @break
                                    @case('dropship')
                                        Dropshipper
                                        @break
                                    @default
                                        {{ $vendorProfile->vendor_type }}
                                @endswitch
                            </p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Country</label>
                            <p class="text-gray-900 font-medium">{{ $vendorProfile->country }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">City</label>
                            <p class="text-gray-900 font-medium">{{ $vendorProfile->city }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Currency</label>
                            <p class="text-gray-900 font-medium">{{ $vendorProfile->preferred_currency }}</p>
                        </div>
                        
                        @if($vendorProfile->annual_turnover)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Annual Turnover</label>
                            <p class="text-gray-900 font-medium">${{ number_format($vendorProfile->annual_turnover, 2) }}</p>
                        </div>
                        @endif
                    </div>
                    
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-500 mb-1">Business Address</label>
                        <p class="text-gray-900">{{ $vendorProfile->address }}</p>
                    </div>
                </div>

                <!-- Uploaded Documents -->
                <div class="mb-10">
                    <h2 class="text-xl font-bold text-gray-800 mb-6 pb-3 border-b">Uploaded Documents</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($documents as $document)
                        <div class="border border-gray-200 rounded-lg p-4 hover:border-indigo-300 transition">
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
                                <div class="ml-3">
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
                                        Uploaded: {{ \Carbon\Carbon::parse($document->created_at)->format('M d, Y') }}
                                    </p>
                                    <p class="text-xs text-gray-400 mt-1">
                                        {{ $document->mime }}
                                    </p>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <a href="{{ Storage::url($document->path) }}" 
                                   target="_blank"
                                   class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-800">
                                    <i class="fas fa-eye mr-2"></i>
                                    View Document
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Next Steps -->
                <div class="mb-10">
                    <h2 class="text-xl font-bold text-gray-800 mb-6 pb-3 border-b">What Happens Next?</h2>
                    
                    <div class="bg-indigo-50 border-l-4 border-indigo-400 p-6 rounded-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-info-circle text-indigo-400 text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-indigo-800 mb-2">Application Review Process</h3>
                                
                                <ul class="space-y-3 text-gray-700">
                                    <li class="flex items-start">
                                        <i class="fas fa-check text-green-500 mt-1 mr-3"></i>
                                        <span><strong>Step 1:</strong> Application submitted successfully ✓</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="fas fa-clock text-yellow-500 mt-1 mr-3"></i>
                                        <span><strong>Step 2:</strong> Our team reviews your documents (24-48 hours)</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="fas fa-envelope text-blue-500 mt-1 mr-3"></i>
                                        <span><strong>Step 3:</strong> You'll receive email notification</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="fas fa-store text-purple-500 mt-1 mr-3"></i>
                                        <span><strong>Step 4:</strong> Start listing products after approval</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Score (if available) -->
                @if($score)
                <div class="mb-10">
                    <h2 class="text-xl font-bold text-gray-800 mb-6 pb-3 border-b">Vendor Score</h2>
                    
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-6 rounded-xl">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">Initial Trust Score</h3>
                                <p class="text-gray-600">Based on document completeness and verification</p>
                            </div>
                            
                            <div class="text-center">
                                <div class="text-5xl font-bold text-indigo-600 mb-1">{{ $score->score }}/100</div>
                                <div class="text-sm text-gray-500">Trust Score</div>
                            </div>
                        </div>
                        
                        <!-- Progress bar -->
                        <div class="mt-6">
                            <div class="flex justify-between text-sm text-gray-600 mb-1">
                                <span>Document Completeness</span>
                                <span>{{ $score->score }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ $score->score }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Guarantor Information -->
                @if($vendorProfile->meta && isset($vendorProfile->meta['guarantor']))
                <div class="mb-10">
                    <h2 class="text-xl font-bold text-gray-800 mb-6 pb-3 border-b">Guarantor Information</h2>
                    
                    <div class="bg-gray-50 p-6 rounded-xl">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Guarantor Name</label>
                                <p class="text-gray-900 font-medium">{{ $vendorProfile->meta['guarantor']['name'] }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Guarantor Phone</label>
                                <p class="text-gray-900 font-medium">{{ $vendorProfile->meta['guarantor']['phone'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 justify-between pt-8 border-t">
                    <div>
                        <a href="{{ route('welcome') }}" 
                           class="inline-flex items-center px-6 py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition">
                            <i class="fas fa-home mr-2"></i>
                            Back to Home
                        </a>
                    </div>
                    
                    <div class="flex gap-4">
                        <!-- Only show dashboard button if approved -->
                        @if($vendorProfile->vetting_status === 'approved')
                        <a href="{{ route('vendor.dashboard') }}" 
                           class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 transition">
                            <i class="fas fa-tachometer-alt mr-2"></i>
                            Go to Dashboard
                        </a>
                        @endif
                        
                        <!-- Logout button -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" 
                                    class="inline-flex items-center px-6 py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition">
                                <i class="fas fa-sign-out-alt mr-2"></i>
                                Logout
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>

        <!-- Support Info -->
        <div class="mt-8 text-center">
            <p class="text-gray-600">
                <i class="fas fa-question-circle mr-2"></i>
                Need help? Contact support at 
                <a href="mailto:support@jclone.com" class="text-indigo-600 hover:text-indigo-800">support@jclone.com</a>
            </p>
            <p class="text-sm text-gray-500 mt-2">
                Application ID: VENDOR-{{ str_pad($vendorProfile->id, 6, '0', STR_PAD_LEFT) }}
            </p>
        </div>

    </div>
</div>

<script>
    // Auto-refresh page every 30 seconds if status is pending
    @if($vendorProfile->vetting_status === 'pending')
    setTimeout(function() {
        window.location.reload();
    }, 30000); // 30 seconds
    @endif
</script>
@endsection