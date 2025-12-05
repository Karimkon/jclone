@extends('layouts.app')

@section('title', 'Become a Vendor - JClone')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-12 px-4">
    <div class="max-w-6xl mx-auto">
        <!-- Progress Steps -->
        <div class="mb-12">
            <div class="flex items-center justify-center mb-8">
                <div class="flex items-center">
                    <div class="flex items-center justify-center w-10 h-10 bg-indigo-600 rounded-full">
                        <span class="text-white font-bold">1</span>
                    </div>
                    <div class="w-24 h-1 bg-indigo-600"></div>
                    <div class="flex items-center justify-center w-10 h-10 bg-indigo-600 rounded-full">
                        <span class="text-white font-bold">2</span>
                    </div>
                    <div class="w-24 h-1 bg-indigo-600"></div>
                    <div class="flex items-center justify-center w-10 h-10 bg-indigo-600 rounded-full">
                        <span class="text-white font-bold">3</span>
                    </div>
                </div>
            </div>
            
            <div class="flex justify-between px-8">
                <div class="text-center">
                    <p class="font-semibold text-indigo-600">Account Info</p>
                </div>
                <div class="text-center">
                    <p class="font-semibold text-indigo-600">Business Info</p>
                </div>
                <div class="text-center">
                    <p class="font-semibold text-indigo-600">Documents</p>
                </div>
            </div>
        </div>

        <!-- Form -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="p-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Become a JClone Vendor</h1>
                <p class="text-gray-600 mb-8">
                    @auth
                        Complete your vendor application to start selling
                    @else
                        Create your account and submit your vendor application
                    @endauth
                </p>
                
                @if(session('error'))
                <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700 font-semibold">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
                @endif

                @if(session('success'))
                <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6">
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
                <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700 font-semibold mb-2">Please correct the following errors:</p>
                            <ul class="list-disc list-inside text-sm text-red-600 space-y-1">
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
                    
                    <!-- User Registration Fields (Only show if not logged in) -->
                    @guest
                    <div class="mb-12">
                        <h2 class="text-xl font-bold text-gray-800 mb-6 pb-3 border-b">Account Information</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Full Name -->
                            <div>
                                <label class="block text-gray-700 font-medium mb-2">Full Name *</label>
                                <input type="text" name="name" required 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('name') border-red-500 @enderror"
                                       placeholder="John Doe"
                                       value="{{ old('name') }}">
                                @error('name')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div>
                                <label class="block text-gray-700 font-medium mb-2">Email Address *</label>
                                <input type="email" name="email" required 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('email') border-red-500 @enderror"
                                       placeholder="vendor@example.com"
                                       value="{{ old('email') }}">
                                @error('email')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Phone -->
                            <div>
                                <label class="block text-gray-700 font-medium mb-2">Phone Number *</label>
                                <input type="tel" name="phone" required 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('phone') border-red-500 @enderror"
                                       placeholder="+256 XXX XXX XXX"
                                       value="{{ old('phone') }}">
                                @error('phone')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Password -->
                            <div>
                                <label class="block text-gray-700 font-medium mb-2">Password *</label>
                                <input type="password" name="password" required 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('password') border-red-500 @enderror"
                                       placeholder="Minimum 8 characters">
                                @error('password')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Confirm Password -->
                            <div class="md:col-span-2">
                                <label class="block text-gray-700 font-medium mb-2">Confirm Password *</label>
                                <input type="password" name="password_confirmation" required 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                       placeholder="Confirm your password">
                            </div>
                        </div>
                    </div>
                    @endguest
                    
                    <!-- Business Information -->
                    <div class="mb-12">
                        <h2 class="text-xl font-bold text-gray-800 mb-6 pb-3 border-b">Business Information</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Vendor Type -->
                            <div class="col-span-2">
                                <label class="block text-gray-700 font-medium mb-2">What type of vendor are you? *</label>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <label class="vendor-type-label border-2 border-gray-300 rounded-lg p-4 cursor-pointer hover:border-indigo-500 transition">
                                        <input type="radio" name="vendor_type" value="local_retail" class="hidden" required {{ old('vendor_type') == 'local_retail' ? 'checked' : '' }}>
                                        <div class="text-center">
                                            <div class="bg-blue-100 p-3 rounded-full w-16 h-16 mx-auto mb-3 flex items-center justify-center">
                                                <i class="fas fa-store text-blue-600 text-2xl"></i>
                                            </div>
                                            <p class="font-semibold">Local Retailer</p>
                                            <p class="text-sm text-gray-600 mt-1">Sell products locally in Uganda</p>
                                        </div>
                                    </label>
                                    
                                    <label class="vendor-type-label border-2 border-gray-300 rounded-lg p-4 cursor-pointer hover:border-indigo-500 transition">
                                        <input type="radio" name="vendor_type" value="china_supplier" class="hidden" required {{ old('vendor_type') == 'china_supplier' ? 'checked' : '' }}>
                                        <div class="text-center">
                                            <div class="bg-green-100 p-3 rounded-full w-16 h-16 mx-auto mb-3 flex items-center justify-center">
                                                <i class="fas fa-plane text-green-600 text-2xl"></i>
                                            </div>
                                            <p class="font-semibold">International Supplier</p>
                                            <p class="text-sm text-gray-600 mt-1">Import goods from China/abroad</p>
                                        </div>
                                    </label>
                                    
                                    <label class="vendor-type-label border-2 border-gray-300 rounded-lg p-4 cursor-pointer hover:border-indigo-500 transition">
                                        <input type="radio" name="vendor_type" value="dropship" class="hidden" required {{ old('vendor_type') == 'dropship' ? 'checked' : '' }}>
                                        <div class="text-center">
                                            <div class="bg-purple-100 p-3 rounded-full w-16 h-16 mx-auto mb-3 flex items-center justify-center">
                                                <i class="fas fa-box-open text-purple-600 text-2xl"></i>
                                            </div>
                                            <p class="font-semibold">Dropshipper</p>
                                            <p class="text-sm text-gray-600 mt-1">No inventory, ship directly</p>
                                        </div>
                                    </label>
                                </div>
                                @error('vendor_type')
                                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Business Name -->
                            <div>
                                <label class="block text-gray-700 font-medium mb-2">Business Name *</label>
                                <input type="text" name="business_name" required 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('business_name') border-red-500 @enderror"
                                       placeholder="Enter your business name"
                                       value="{{ old('business_name') }}">
                                @error('business_name')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Country -->
                            <div>
                                <label class="block text-gray-700 font-medium mb-2">Country *</label>
                                <select name="country" required 
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('country') border-red-500 @enderror">
                                    <option value="">Select Country</option>
                                    <option value="Uganda" {{ old('country') == 'Uganda' ? 'selected' : '' }}>Uganda</option>
                                    <option value="Kenya" {{ old('country') == 'Kenya' ? 'selected' : '' }}>Kenya</option>
                                    <option value="Tanzania" {{ old('country') == 'Tanzania' ? 'selected' : '' }}>Tanzania</option>
                                    <option value="Rwanda" {{ old('country') == 'Rwanda' ? 'selected' : '' }}>Rwanda</option>
                                    <option value="China" {{ old('country') == 'China' ? 'selected' : '' }}>China</option>
                                    <option value="Other" {{ old('country') == 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('country')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- City -->
                            <div>
                                <label class="block text-gray-700 font-medium mb-2">City *</label>
                                <input type="text" name="city" required 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('city') border-red-500 @enderror"
                                       placeholder="Enter city"
                                       value="{{ old('city') }}">
                                @error('city')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Address -->
                            <div class="col-span-2">
                                <label class="block text-gray-700 font-medium mb-2">Business Address *</label>
                                <textarea name="address" rows="3" required 
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('address') border-red-500 @enderror"
                                          placeholder="Full business address">{{ old('address') }}</textarea>
                                @error('address')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Annual Turnover -->
                            <div>
                                <label class="block text-gray-700 font-medium mb-2">Annual Turnover (Optional)</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-3 text-gray-500">$</span>
                                    <input type="number" name="annual_turnover" step="0.01" 
                                           class="w-full pl-8 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('annual_turnover') border-red-500 @enderror"
                                           placeholder="0.00"
                                           value="{{ old('annual_turnover') }}">
                                </div>
                                @error('annual_turnover')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Preferred Currency -->
                            <div>
                                <label class="block text-gray-700 font-medium mb-2">Preferred Currency *</label>
                                <select name="preferred_currency" required 
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('preferred_currency') border-red-500 @enderror">
                                    <option value="">Select Currency</option>
                                    <option value="USD" {{ old('preferred_currency') == 'USD' ? 'selected' : '' }}>US Dollar (USD)</option>
                                    <option value="UGX" {{ old('preferred_currency') == 'UGX' ? 'selected' : '' }}>Ugandan Shilling (UGX)</option>
                                    <option value="KES" {{ old('preferred_currency') == 'KES' ? 'selected' : '' }}>Kenyan Shilling (KES)</option>
                                    <option value="CNY" {{ old('preferred_currency') == 'CNY' ? 'selected' : '' }}>Chinese Yuan (CNY)</option>
                                </select>
                                @error('preferred_currency')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Required Documents -->
                    <div class="mb-12">
                        <h2 class="text-xl font-bold text-gray-800 mb-6 pb-3 border-b">Required Documents</h2>
                        
                        <div class="space-y-8">
                            <!-- National ID -->
                            <div class="bg-blue-50 p-6 rounded-xl">
                                <h3 class="font-bold text-lg text-blue-800 mb-4">National ID Card</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-gray-700 font-medium mb-2">Front Side *</label>
                                        <div class="border-2 border-dashed border-blue-300 rounded-lg p-6 text-center hover:border-blue-500 transition cursor-pointer" 
                                             onclick="document.getElementById('national_id_front').click()">
                                            <i class="fas fa-id-card text-blue-500 text-4xl mb-3"></i>
                                            <p class="text-blue-600 font-medium">Upload Front of ID</p>
                                            <p class="text-gray-500 text-sm mt-1">JPG, PNG or PDF (Max 5MB)</p>
                                            <input type="file" name="national_id_front" id="national_id_front" class="hidden" accept=".jpg,.jpeg,.png,.pdf" required>
                                        </div>
                                        <p id="frontFileName" class="text-sm text-gray-600 mt-2"></p>
                                        @error('national_id_front')
                                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    
                                    <div>
                                        <label class="block text-gray-700 font-medium mb-2">Back Side *</label>
                                        <div class="border-2 border-dashed border-blue-300 rounded-lg p-6 text-center hover:border-blue-500 transition cursor-pointer"
                                             onclick="document.getElementById('national_id_back').click()">
                                            <i class="fas fa-id-card text-blue-500 text-4xl mb-3"></i>
                                            <p class="text-blue-600 font-medium">Upload Back of ID</p>
                                            <p class="text-gray-500 text-sm mt-1">JPG, PNG or PDF (Max 5MB)</p>
                                            <input type="file" name="national_id_back" id="national_id_back" class="hidden" accept=".jpg,.jpeg,.png,.pdf" required>
                                        </div>
                                        <p id="backFileName" class="text-sm text-gray-600 mt-2"></p>
                                        @error('national_id_back')
                                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Bank Statement -->
                            <div class="bg-green-50 p-6 rounded-xl">
                                <h3 class="font-bold text-lg text-green-800 mb-4">Bank Statement (Last 3 Months)</h3>
                                <div class="border-2 border-dashed border-green-300 rounded-lg p-8 text-center hover:border-green-500 transition cursor-pointer"
                                     onclick="document.getElementById('bank_statement').click()">
                                    <i class="fas fa-file-invoice-dollar text-green-500 text-4xl mb-3"></i>
                                    <p class="text-green-600 font-medium">Upload Bank Statement</p>
                                    <p class="text-gray-500 text-sm mt-1">PDF format preferred (Max 5MB)</p>
                                    <input type="file" name="bank_statement" id="bank_statement" class="hidden" accept=".jpg,.jpeg,.png,.pdf" required>
                                </div>
                                <p id="bankFileName" class="text-sm text-gray-600 mt-2"></p>
                                @error('bank_statement')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Proof of Address -->
                            <div class="bg-purple-50 p-6 rounded-xl">
                                <h3 class="font-bold text-lg text-purple-800 mb-4">Proof of Address</h3>
                                <div class="border-2 border-dashed border-purple-300 rounded-lg p-8 text-center hover:border-purple-500 transition cursor-pointer"
                                     onclick="document.getElementById('proof_of_address').click()">
                                    <i class="fas fa-home text-purple-500 text-4xl mb-3"></i>
                                    <p class="text-purple-600 font-medium">Upload Utility Bill/Rental Agreement</p>
                                    <p class="text-gray-500 text-sm mt-1">Must show your name and address (Max 5MB)</p>
                                    <input type="file" name="proof_of_address" id="proof_of_address" class="hidden" accept=".jpg,.jpeg,.png,.pdf" required>
                                </div>
                                <p id="addressFileName" class="text-sm text-gray-600 mt-2"></p>
                                @error('proof_of_address')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Guarantor Information -->
                            <div class="bg-yellow-50 p-6 rounded-xl">
                                <h3 class="font-bold text-lg text-yellow-800 mb-4">Guarantor Information</h3>
                                <p class="text-gray-600 mb-4">Provide details of someone who can guarantee your business</p>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                    <div>
                                        <label class="block text-gray-700 font-medium mb-2">Guarantor Full Name *</label>
                                        <input type="text" name="guarantor_name" required 
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('guarantor_name') border-red-500 @enderror"
                                               placeholder="Full name"
                                               value="{{ old('guarantor_name') }}">
                                        @error('guarantor_name')
                                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    
                                    <div>
                                        <label class="block text-gray-700 font-medium mb-2">Guarantor Phone Number *</label>
                                        <input type="tel" name="guarantor_phone" required 
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('guarantor_phone') border-red-500 @enderror"
                                               placeholder="+256 XXX XXX XXX"
                                               value="{{ old('guarantor_phone') }}">
                                        @error('guarantor_phone')
                                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-gray-700 font-medium mb-2">Guarantor ID Card *</label>
                                    <div class="border-2 border-dashed border-yellow-300 rounded-lg p-8 text-center hover:border-yellow-500 transition cursor-pointer"
                                         onclick="document.getElementById('guarantor_id').click()">
                                        <i class="fas fa-user-shield text-yellow-500 text-4xl mb-3"></i>
                                        <p class="text-yellow-600 font-medium">Upload Guarantor's ID</p>
                                        <p class="text-gray-500 text-sm mt-1">Front side only (Max 5MB)</p>
                                        <input type="file" name="guarantor_id" id="guarantor_id" class="hidden" accept=".jpg,.jpeg,.png,.pdf" required>
                                    </div>
                                    <p id="guarantorFileName" class="text-sm text-gray-600 mt-2"></p>
                                    @error('guarantor_id')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Optional: Company Documents -->
                            <div class="bg-gray-50 p-6 rounded-xl">
                                <h3 class="font-bold text-lg text-gray-800 mb-4">Company Documents (Optional)</h3>
                                <p class="text-gray-600 mb-4">If you're registering as a company, upload these documents</p>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-gray-700 font-medium mb-2">Company Registration Certificate</label>
                                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-gray-500 transition cursor-pointer"
                                             onclick="document.getElementById('company_registration').click()">
                                            <i class="fas fa-file-contract text-gray-500 text-4xl mb-3"></i>
                                            <p class="text-gray-600 font-medium">Upload Registration</p>
                                            <p class="text-gray-500 text-sm mt-1">PDF format (Max 5MB)</p>
                                            <input type="file" name="company_registration" id="company_registration" class="hidden" accept=".jpg,.jpeg,.png,.pdf">
                                        </div>
                                        <p id="companyFileName" class="text-sm text-gray-600 mt-2"></p>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-gray-700 font-medium mb-2">Tax Certificate</label>
                                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-gray-500 transition cursor-pointer"
                                             onclick="document.getElementById('tax_certificate').click()">
                                            <i class="fas fa-receipt text-gray-500 text-4xl mb-3"></i>
                                            <p class="text-gray-600 font-medium">Upload Tax Certificate</p>
                                            <p class="text-gray-500 text-sm mt-1">PDF format (Max 5MB)</p>
                                            <input type="file" name="tax_certificate" id="tax_certificate" class="hidden" accept=".jpg,.jpeg,.png,.pdf">
                                        </div>
                                        <p id="taxFileName" class="text-sm text-gray-600 mt-2"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Terms and Conditions -->
                    <div class="mb-8">
                        <label class="flex items-start">
                            <input type="checkbox" name="terms" required 
                                   class="mt-1 mr-3 h-5 w-5 text-indigo-600 rounded focus:ring-indigo-500">
                            <span class="text-gray-700">
                                I agree to the 
                                <a href="#" class="text-indigo-600 hover:text-indigo-800">Terms and Conditions</a> 
                                and 
                                <a href="#" class="text-indigo-600 hover:text-indigo-800">Vendor Agreement</a>. 
                                I confirm that all information provided is accurate.
                            </span>
                        </label>
                        @error('terms')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-between items-center pt-6 border-t">
                        <a href="{{ route('welcome') }}" class="text-gray-600 hover:text-gray-800">
                            <i class="fas fa-arrow-left mr-2"></i>Back to Home
                        </a>
                        
                        <button type="submit" 
                                class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-bold py-4 px-8 rounded-lg hover:from-indigo-700 hover:to-purple-700 transition duration-300 transform hover:-translate-y-1 hover:shadow-lg">
                            <i class="fas fa-paper-plane mr-2"></i>Submit Application
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Info Section -->
        <div class="mt-8 text-center">
            <p class="text-gray-600">
                <i class="fas fa-lock mr-2"></i>All documents are encrypted and stored securely
            </p>
            <p class="text-sm text-gray-500 mt-2">
                Verification usually takes 24-48 hours. You'll be notified via email.
            </p>
        </div>
    </div>
</div>

<script>
    // File upload preview
    document.addEventListener('DOMContentLoaded', function() {
        const fileInputs = [
            { input: 'national_id_front', display: 'frontFileName' },
            { input: 'national_id_back', display: 'backFileName' },
            { input: 'bank_statement', display: 'bankFileName' },
            { input: 'proof_of_address', display: 'addressFileName' },
            { input: 'guarantor_id', display: 'guarantorFileName' },
            { input: 'company_registration', display: 'companyFileName' },
            { input: 'tax_certificate', display: 'taxFileName' }
        ];

        fileInputs.forEach(item => {
            const input = document.getElementById(item.input);
            const display = document.getElementById(item.display);
            
            if (input && display) {
                input.addEventListener('change', function() {
                    if (this.files.length > 0) {
                        display.textContent = `Selected: ${this.files[0].name}`;
                        display.className = 'text-sm text-green-600 mt-2';
                    } else {
                        display.textContent = '';
                    }
                });
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
            return true;
        });
    });
</script>
@endsection