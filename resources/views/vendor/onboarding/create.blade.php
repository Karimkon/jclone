@extends('layouts.app')

@section('title', 'Become a Vendor - ' . config('app.name'))

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-8 px-4">
    <div class="max-w-5xl mx-auto">
        <!-- Progress Steps -->
        <div class="mb-8">
            <div class="flex items-center justify-center mb-4">
                <div class="flex items-center">
                    <div class="flex items-center justify-center w-8 h-8 bg-indigo-600 rounded-full">
                        <span class="text-white text-sm font-bold">1</span>
                    </div>
                    <div class="w-16 h-1 bg-indigo-600"></div>
                    <div class="flex items-center justify-center w-8 h-8 bg-indigo-600 rounded-full">
                        <span class="text-white text-sm font-bold">2</span>
                    </div>
                    <div class="w-16 h-1 bg-indigo-600"></div>
                    <div class="flex items-center justify-center w-8 h-8 bg-indigo-600 rounded-full">
                        <span class="text-white text-sm font-bold">3</span>
                    </div>
                </div>
            </div>
            
            <div class="flex justify-between px-6">
                <div class="text-center">
                    <p class="font-medium text-indigo-600 text-sm">Account Info</p>
                </div>
                <div class="text-center">
                    <p class="font-medium text-indigo-600 text-sm">Business Info</p>
                </div>
                <div class="text-center">
                    <p class="font-medium text-indigo-600 text-sm">Documents</p>
                </div>
            </div>
        </div>

        <!-- Form -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="p-6">
                <h1 class="text-2xl font-bold text-gray-900 mb-1">Become a Vendor</h1>
                <p class="text-gray-600 text-sm mb-6">
                    @auth
                        Complete your vendor application
                    @else
                        Create account & submit application
                    @endauth
                </p>
                
                @if(session('error'))
                <div class="bg-red-50 border-l-4 border-red-400 p-3 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700 font-medium">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
                @endif

                @if(session('success'))
                <div class="bg-green-50 border-l-4 border-green-400 p-3 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-green-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
                @endif

                @if($errors->any())
                <div class="bg-red-50 border-l-4 border-red-400 p-3 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700 font-medium mb-1">Please correct errors:</p>
                            <ul class="list-disc list-inside text-xs text-red-600 space-y-0.5">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                @endif
                
                <form action="{{ route('vendor.onboard.store') }}" method="POST" enctype="multipart/form-data" id="vendorForm">
                    @csrf
                    
                    <!-- User Registration Fields -->
                    @guest
                    <div class="mb-8">
                        <h2 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b">Account Information</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-700 font-medium mb-1 text-sm">Full Name *</label>
                                <input type="text" name="name" required 
                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('name') border-red-500 @enderror"
                                       placeholder="John Doe"
                                       value="{{ old('name') }}">
                            </div>

                            <div>
                                <label class="block text-gray-700 font-medium mb-1 text-sm">Email *</label>
                                <input type="email" name="email" required 
                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('email') border-red-500 @enderror"
                                       placeholder="vendor@example.com"
                                       value="{{ old('email') }}">
                            </div>

                            <div>
                                <label class="block text-gray-700 font-medium mb-1 text-sm">Phone *</label>
                                <input type="tel" name="phone" required 
                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('phone') border-red-500 @enderror"
                                       placeholder="+256 XXX XXX XXX"
                                       value="{{ old('phone') }}">
                            </div>

                            <div>
                                <label class="block text-gray-700 font-medium mb-1 text-sm">Password *</label>
                                <input type="password" name="password" required 
                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('password') border-red-500 @enderror"
                                       placeholder="Min 8 characters">
                            </div>

                            <div>
                                <label class="block text-gray-700 font-medium mb-1 text-sm">Confirm Password *</label>
                                <input type="password" name="password_confirmation" required 
                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                       placeholder="Confirm password">
                            </div>
                        </div>
                    </div>
                    @endguest
                    
                    <!-- Business Information -->
                    <div class="mb-8">
                        <h2 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b">Business Information</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Vendor Type -->
                            <div class="col-span-2 mb-4">
                                <label class="block text-gray-700 font-medium mb-2 text-sm">Vendor Type *</label>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                    <label class="vendor-type-label border border-gray-300 rounded p-3 cursor-pointer hover:border-indigo-500 transition text-center">
                                        <input type="radio" name="vendor_type" value="local_retail" class="hidden" required {{ old('vendor_type') == 'local_retail' ? 'checked' : '' }}>
                                        <i class="fas fa-store text-blue-600 text-lg mb-2 block"></i>
                                        <p class="font-medium text-sm">Local Retailer</p>
                                        <p class="text-xs text-gray-500">Sell locally</p>
                                    </label>
                                    
                                    <label class="vendor-type-label border border-gray-300 rounded p-3 cursor-pointer hover:border-indigo-500 transition text-center">
                                        <input type="radio" name="vendor_type" value="china_supplier" class="hidden" required {{ old('vendor_type') == 'china_supplier' ? 'checked' : '' }}>
                                        <i class="fas fa-plane text-green-600 text-lg mb-2 block"></i>
                                        <p class="font-medium text-sm">Importer</p>
                                        <p class="text-xs text-gray-500">Import goods</p>
                                    </label>
                                    
                                    <label class="vendor-type-label border border-gray-300 rounded p-3 cursor-pointer hover:border-indigo-500 transition text-center">
                                        <input type="radio" name="vendor_type" value="dropship" class="hidden" required {{ old('vendor_type') == 'dropship' ? 'checked' : '' }}>
                                        <i class="fas fa-box-open text-purple-600 text-lg mb-2 block"></i>
                                        <p class="font-medium text-sm">Dropshipper</p>
                                        <p class="text-xs text-gray-500">No inventory</p>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label class="block text-gray-700 font-medium mb-1 text-sm">Business Name *</label>
                                <input type="text" name="business_name" required 
                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                       placeholder="Your business name"
                                       value="{{ old('business_name') }}">
                            </div>

                            <div>
                                <label class="block text-gray-700 font-medium mb-1 text-sm">Country *</label>
                                <select name="country" required 
                                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                    <option value="">Select Country</option>
                                    <option value="Uganda" {{ old('country') == 'Uganda' ? 'selected' : '' }}>Uganda</option>
                                    <option value="Kenya" {{ old('country') == 'Kenya' ? 'selected' : '' }}>Kenya</option>
                                    <option value="Tanzania" {{ old('country') == 'Tanzania' ? 'selected' : '' }}>Tanzania</option>
                                    <option value="Rwanda" {{ old('country') == 'Rwanda' ? 'selected' : '' }}>Rwanda</option>
                                    <option value="China" {{ old('country') == 'China' ? 'selected' : '' }}>China</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-gray-700 font-medium mb-1 text-sm">City *</label>
                                <input type="text" name="city" required 
                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                       placeholder="City"
                                       value="{{ old('city') }}">
                            </div>

                            <div>
                                <label class="block text-gray-700 font-medium mb-1 text-sm">Preferred Currency *</label>
                                <select name="preferred_currency" required 
                                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                    <option value="">Select Currency</option>
                                    <option value="USD" {{ old('preferred_currency') == 'USD' ? 'selected' : '' }}>US Dollar (USD)</option>
                                    <option value="UGX" {{ old('preferred_currency') == 'UGX' ? 'selected' : '' }}>Ugandan Shilling (UGX)</option>
                                    <option value="KES" {{ old('preferred_currency') == 'KES' ? 'selected' : '' }}>Kenyan Shilling (KES)</option>
                                </select>
                            </div>

                            <div class="col-span-2">
                                <label class="block text-gray-700 font-medium mb-1 text-sm">Business Address *</label>
                                <textarea name="address" rows="2" required 
                                          class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                          placeholder="Full business address">{{ old('address') }}</textarea>
                            </div>

                            <div class="col-span-2">
                                <label class="block text-gray-700 font-medium mb-1 text-sm">Annual Turnover (Optional)</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2.5 text-gray-500 text-sm">UGX</span>
                                    <input type="number" name="annual_turnover" step="1" 
                                           class="w-full pl-12 px-3 py-2 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                           placeholder="0"
                                           value="{{ old('annual_turnover') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Required Documents -->
                    <div class="mb-8">
                        <h2 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b">Required Documents</h2>
                        
                        <div class="space-y-6">
                            <!-- National ID - Side by side layout -->
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <h3 class="font-bold text-blue-800 mb-3 flex items-center text-sm">
                                    <i class="fas fa-id-card mr-2"></i>National ID Card
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- Front Side -->
                                    <div>
                                        <label class="block text-gray-700 font-medium mb-2 text-sm">Front Side *</label>
                                        <div class="relative">
                                            <div class="border-2 border-dashed border-blue-300 rounded-lg p-4 text-center hover:border-blue-500 transition cursor-pointer bg-white"
                                                 onclick="document.getElementById('national_id_front').click()">
                                                <div id="frontPreview" class="mb-2 min-h-16 flex items-center justify-center">
                                                    <i class="fas fa-id-card text-blue-500 text-2xl"></i>
                                                </div>
                                                <p class="text-blue-600 font-medium text-xs">Upload Front</p>
                                                <p class="text-gray-500 text-xs mt-1">JPG, PNG, PDF (≤5MB)</p>
                                            </div>
                                            <input type="file" name="national_id_front" id="national_id_front" class="hidden" accept=".jpg,.jpeg,.png,.pdf" required
                                                   onchange="previewFile(this, 'frontPreview')">
                                        </div>
                                        <p id="frontFileName" class="text-xs text-gray-600 mt-1"></p>
                                    </div>
                                    
                                    <!-- Back Side -->
                                    <div>
                                        <label class="block text-gray-700 font-medium mb-2 text-sm">Back Side *</label>
                                        <div class="relative">
                                            <div class="border-2 border-dashed border-blue-300 rounded-lg p-4 text-center hover:border-blue-500 transition cursor-pointer bg-white"
                                                 onclick="document.getElementById('national_id_back').click()">
                                                <div id="backPreview" class="mb-2 min-h-16 flex items-center justify-center">
                                                    <i class="fas fa-id-card text-blue-500 text-2xl"></i>
                                                </div>
                                                <p class="text-blue-600 font-medium text-xs">Upload Back</p>
                                                <p class="text-gray-500 text-xs mt-1">JPG, PNG, PDF (≤5MB)</p>
                                            </div>
                                            <input type="file" name="national_id_back" id="national_id_back" class="hidden" accept=".jpg,.jpeg,.png,.pdf" required
                                                   onchange="previewFile(this, 'backPreview')">
                                        </div>
                                        <p id="backFileName" class="text-xs text-gray-600 mt-1"></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Bank Statement & Proof of Address - Side by side -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Bank Statement -->
                                <div class="bg-green-50 p-4 rounded-lg">
                                    <h3 class="font-bold text-green-800 mb-3 flex items-center text-sm">
                                        <i class="fas fa-file-invoice-dollar mr-2"></i>Bank Statement *
                                    </h3>
                                    <div class="relative">
                                        <div class="border-2 border-dashed border-green-300 rounded-lg p-4 text-center hover:border-green-500 transition cursor-pointer bg-white"
                                             onclick="document.getElementById('bank_statement').click()">
                                            <div id="bankPreview" class="mb-2 min-h-20 flex items-center justify-center">
                                                <i class="fas fa-file-invoice-dollar text-green-500 text-2xl"></i>
                                            </div>
                                            <p class="text-green-600 font-medium text-xs">Upload Statement</p>
                                            <p class="text-gray-500 text-xs mt-1">Last 3 months (≤5MB)</p>
                                        </div>
                                        <input type="file" name="bank_statement" id="bank_statement" class="hidden" accept=".jpg,.jpeg,.png,.pdf" required
                                               onchange="previewFile(this, 'bankPreview')">
                                    </div>
                                    <p id="bankFileName" class="text-xs text-gray-600 mt-1"></p>
                                </div>

                                <!-- Proof of Address -->
                                <div class="bg-purple-50 p-4 rounded-lg">
                                    <h3 class="font-bold text-purple-800 mb-3 flex items-center text-sm">
                                        <i class="fas fa-home mr-2"></i>Proof of Address *
                                    </h3>
                                    <div class="relative">
                                        <div class="border-2 border-dashed border-purple-300 rounded-lg p-4 text-center hover:border-purple-500 transition cursor-pointer bg-white"
                                             onclick="document.getElementById('proof_of_address').click()">
                                            <div id="addressPreview" class="mb-2 min-h-20 flex items-center justify-center">
                                                <i class="fas fa-home text-purple-500 text-2xl"></i>
                                            </div>
                                            <p class="text-purple-600 font-medium text-xs">Upload Document</p>
                                            <p class="text-gray-500 text-xs mt-1">Utility bill/rental (≤5MB)</p>
                                        </div>
                                        <input type="file" name="proof_of_address" id="proof_of_address" class="hidden" accept=".jpg,.jpeg,.png,.pdf" required
                                               onchange="previewFile(this, 'addressPreview')">
                                    </div>
                                    <p id="addressFileName" class="text-xs text-gray-600 mt-1"></p>
                                </div>
                            </div>

                            <!-- Guarantor Information -->
                            <div class="bg-yellow-50 p-4 rounded-lg">
                                <h3 class="font-bold text-yellow-800 mb-3 flex items-center text-sm">
                                    <i class="fas fa-user-shield mr-2"></i>Guarantor Information
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label class="block text-gray-700 font-medium mb-1 text-sm">Full Name *</label>
                                        <input type="text" name="guarantor_name" required 
                                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                               placeholder="Guarantor name"
                                               value="{{ old('guarantor_name') }}">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-gray-700 font-medium mb-1 text-sm">Phone *</label>
                                        <input type="tel" name="guarantor_phone" required 
                                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                               placeholder="+256 XXX XXX XXX"
                                               value="{{ old('guarantor_phone') }}">
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-gray-700 font-medium mb-2 text-sm">Guarantor ID *</label>
                                    <div class="relative">
                                        <div class="border-2 border-dashed border-yellow-300 rounded-lg p-4 text-center hover:border-yellow-500 transition cursor-pointer bg-white"
                                             onclick="document.getElementById('guarantor_id').click()">
                                            <div id="guarantorPreview" class="mb-2 min-h-20 flex items-center justify-center">
                                                <i class="fas fa-user-shield text-yellow-500 text-2xl"></i>
                                            </div>
                                            <p class="text-yellow-600 font-medium text-xs">Upload ID</p>
                                            <p class="text-gray-500 text-xs mt-1">Front side (≤5MB)</p>
                                        </div>
                                        <input type="file" name="guarantor_id" id="guarantor_id" class="hidden" accept=".jpg,.jpeg,.png,.pdf" required
                                               onchange="previewFile(this, 'guarantorPreview')">
                                    </div>
                                    <p id="guarantorFileName" class="text-xs text-gray-600 mt-1"></p>
                                </div>
                            </div>

                            <!-- Company Documents (Optional) -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="font-bold text-gray-800 mb-3 flex items-center text-sm">
                                    <i class="fas fa-building mr-2"></i>Company Documents (Optional)
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-gray-700 font-medium mb-2 text-sm">Registration Certificate</label>
                                        <div class="relative">
                                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-gray-500 transition cursor-pointer bg-white"
                                                 onclick="document.getElementById('company_registration').click()">
                                                <div id="companyPreview" class="mb-2 min-h-16 flex items-center justify-center">
                                                    <i class="fas fa-file-contract text-gray-500 text-xl"></i>
                                                </div>
                                                <p class="text-gray-600 font-medium text-xs">Upload Certificate</p>
                                            </div>
                                            <input type="file" name="company_registration" id="company_registration" class="hidden" accept=".jpg,.jpeg,.png,.pdf"
                                                   onchange="previewFile(this, 'companyPreview')">
                                        </div>
                                        <p id="companyFileName" class="text-xs text-gray-600 mt-1"></p>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-gray-700 font-medium mb-2 text-sm">Tax Certificate</label>
                                        <div class="relative">
                                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-gray-500 transition cursor-pointer bg-white"
                                                 onclick="document.getElementById('tax_certificate').click()">
                                                <div id="taxPreview" class="mb-2 min-h-16 flex items-center justify-center">
                                                    <i class="fas fa-receipt text-gray-500 text-xl"></i>
                                                </div>
                                                <p class="text-gray-600 font-medium text-xs">Upload Tax Cert</p>
                                            </div>
                                            <input type="file" name="tax_certificate" id="tax_certificate" class="hidden" accept=".jpg,.jpeg,.png,.pdf"
                                                   onchange="previewFile(this, 'taxPreview')">
                                        </div>
                                        <p id="taxFileName" class="text-xs text-gray-600 mt-1"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Terms and Conditions -->
                    <div class="mb-6">
                        <label class="flex items-start">
                            <input type="checkbox" name="terms" required 
                                   class="mt-0.5 mr-2 h-4 w-4 text-indigo-600 rounded focus:ring-indigo-500">
                            <span class="text-gray-700 text-sm">
                                I agree to the <a href="{{ route('site.terms') }}" class="text-indigo-600 hover:text-indigo-800">Terms</a> 
                                and <a href="{{ route('site.vendorBenefits') }}" class="text-indigo-600 hover:text-indigo-800">Vendor Agreement</a>
                            </span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex flex-col sm:flex-row justify-between items-center gap-4 pt-6 border-t">
                        <a href="{{ route('welcome') }}" class="text-gray-600 hover:text-gray-800 text-sm">
                            <i class="fas fa-arrow-left mr-1"></i>Back to Home
                        </a>
                        
                        <button type="submit" 
                                class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold py-3 px-6 rounded-lg hover:from-indigo-700 hover:to-purple-700 transition w-full sm:w-auto">
                            <i class="fas fa-paper-plane mr-2"></i>Submit Application
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Info Section -->
        <div class="mt-6 text-center">
            <p class="text-gray-600 text-sm">
                <i class="fas fa-lock mr-1"></i>Documents are encrypted & secure
            </p>
            <p class="text-xs text-gray-500 mt-1">
                Verification: 24-48 hours. Email notifications sent.
            </p>
        </div>
    </div>
</div>

<script>
// File preview and name display
function previewFile(input, previewId) {
    const preview = document.getElementById(previewId);
    const fileName = document.getElementById(input.id + 'FileName');
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const reader = new FileReader();
        
        // Show file name
        if (fileName) {
            fileName.textContent = `✓ ${file.name}`;
            fileName.className = 'text-xs text-green-600 mt-1 font-medium';
        }
        
        // Preview image if it's an image
        if (file.type.startsWith('image/')) {
            reader.onload = function(e) {
                preview.innerHTML = `<img src="${e.target.result}" class="max-h-20 max-w-full rounded object-contain">`;
            }
            reader.readAsDataURL(file);
        } else if (file.type === 'application/pdf') {
            // Show PDF icon for PDFs
            preview.innerHTML = `<i class="fas fa-file-pdf text-red-500 text-3xl"></i><span class="text-xs text-red-600 block mt-1">PDF</span>`;
        } else {
            // Show generic file icon
            preview.innerHTML = `<i class="fas fa-file text-gray-500 text-3xl"></i><span class="text-xs text-gray-600 block mt-1">Document</span>`;
        }
        
        // Show success border
        input.closest('.relative').querySelector('.border-dashed').classList.add('border-green-400');
    }
}

// File upload feedback for all inputs
document.addEventListener('DOMContentLoaded', function() {
    const fileInputs = [
        'national_id_front', 'national_id_back', 'bank_statement', 
        'proof_of_address', 'guarantor_id', 'company_registration', 'tax_certificate'
    ];

    fileInputs.forEach(id => {
        const input = document.getElementById(id);
        if (input) {
            // Set up change event if not already set
            if (!input.hasAttribute('data-preview-setup')) {
                input.setAttribute('data-preview-setup', 'true');
                input.addEventListener('change', function() {
                    const previewId = this.id.replace(/_/g, '') + 'Preview';
                    previewFile(this, previewId);
                });
            }
        }
    });

    // Form validation
    document.getElementById('vendorForm').addEventListener('submit', function(e) {
        const vendorType = document.querySelector('input[name="vendor_type"]:checked');
        if (!vendorType) {
            e.preventDefault();
            alert('Please select a vendor type');
            return false;
        }
        
        // Check required files
        const requiredFiles = ['national_id_front', 'national_id_back', 'bank_statement', 'proof_of_address', 'guarantor_id'];
        let missingFiles = [];
        
        requiredFiles.forEach(fileId => {
            const input = document.getElementById(fileId);
            if (input && !input.files.length) {
                const label = input.closest('div').querySelector('label');
                missingFiles.push(label ? label.textContent.trim().replace('*', '') : fileId);
            }
        });
        
        if (missingFiles.length > 0) {
            e.preventDefault();
            alert('Please upload all required documents:\n' + missingFiles.join('\n'));
            return false;
        }
        
        return true;
    });

    // Add hover effects for file upload areas
    document.querySelectorAll('.border-dashed').forEach(area => {
        area.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('border-blue-500', 'bg-blue-50');
        });
        
        area.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.classList.remove('border-blue-500', 'bg-blue-50');
        });
        
        area.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('border-blue-500', 'bg-blue-50');
        });
    });
});
</script>

<style>
.vendor-type-label:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.1);
}

.vendor-type-label input:checked + div {
    border-color: #4f46e5;
    background-color: rgba(79, 70, 229, 0.05);
}

.border-dashed {
    transition: all 0.2s ease;
}

/* Make form more compact */
@media (max-width: 768px) {
    .grid-cols-2 {
        grid-template-columns: 1fr;
    }
    
    .md\:grid-cols-2 {
        grid-template-columns: 1fr;
    }
    
    .md\:grid-cols-3 {
        grid-template-columns: 1fr;
    }
}

/* Compact spacing */
.space-y-6 > * + * {
    margin-top: 1rem;
}
</style>
@endsection