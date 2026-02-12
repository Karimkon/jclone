@extends('layouts.app')

@section('title', 'Become a Vendor - ' . config('app.name'))

@section('content')
<div class="min-h-screen bg-gray-100 py-6">
    <div class="max-w-3xl mx-auto px-4">
        <!-- Header -->
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Become a Vendor</h1>
            <p class="text-gray-600 text-sm mt-1">Complete the form below to start selling</p>
        </div>

        @if(session('error'))
        <div class="bg-red-50 border-l-4 border-red-400 p-3 mb-4 rounded">
            <p class="text-sm text-red-700"><i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}</p>
        </div>
        @endif

        @if($errors->any())
        <div class="bg-red-50 border-l-4 border-red-400 p-3 mb-4 rounded">
            <ul class="list-disc list-inside text-sm text-red-700">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('vendor.onboard.store') }}" method="POST" enctype="multipart/form-data" id="vendorForm" class="space-y-4">
            @csrf

            <!-- Account Info (For New Users) -->
            @guest
            <div class="bg-white rounded-lg shadow-sm p-4">
                <h2 class="font-semibold text-gray-800 mb-3 flex items-center text-sm border-b pb-2">
                    <i class="fas fa-user text-indigo-500 mr-2"></i>Account Information
                </h2>
                <div class="grid grid-cols-2 gap-3">
                    <div class="flex items-center">
                        <label class="w-24 text-sm text-gray-600 flex-shrink-0">Name *</label>
                        <input type="text" name="name" required value="{{ old('name') }}"
                               class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="Full name">
                    </div>
                    <div class="flex items-center">
                        <label class="w-24 text-sm text-gray-600 flex-shrink-0">Email *</label>
                        <input type="email" name="email" required value="{{ old('email') }}"
                               class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="email@example.com">
                    </div>
                    <div class="flex items-center">
                        <label class="w-24 text-sm text-gray-600 flex-shrink-0">Phone *</label>
                        <input type="tel" name="phone" required value="{{ old('phone') }}"
                               class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="+256 XXX XXX XXX">
                    </div>
                    <div class="flex items-center">
                        <label class="w-24 text-sm text-gray-600 flex-shrink-0">Password *</label>
                        <input type="password" name="password" required
                               class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="Min 8 characters">
                    </div>
                    <div class="flex items-center col-span-2">
                        <label class="w-24 text-sm text-gray-600 flex-shrink-0">Confirm *</label>
                        <input type="password" name="password_confirmation" required
                               class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="Confirm password">
                    </div>
                </div>
            </div>
            @endguest

            <!-- Business Info -->
            <div class="bg-white rounded-lg shadow-sm p-4">
                <h2 class="font-semibold text-gray-800 mb-3 flex items-center text-sm border-b pb-2">
                    <i class="fas fa-store text-indigo-500 mr-2"></i>Business Information
                </h2>

                <!-- Vendor Type -->
                <div class="flex items-start mb-3">
                    <label class="w-24 text-sm text-gray-600 flex-shrink-0 pt-2">Type *</label>
                    <div class="flex-1 flex gap-2">
                        <label class="flex-1 border rounded p-2 cursor-pointer hover:border-indigo-400 transition text-center vendor-type-option {{ old('vendor_type') == 'local_retail' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-300' }}">
                            <input type="radio" name="vendor_type" value="local_retail" class="hidden" required {{ old('vendor_type') == 'local_retail' ? 'checked' : '' }}>
                            <i class="fas fa-store text-blue-500 block mb-1"></i>
                            <span class="text-xs font-medium">Local</span>
                        </label>
                        <label class="flex-1 border rounded p-2 cursor-pointer hover:border-indigo-400 transition text-center vendor-type-option {{ old('vendor_type') == 'china_supplier' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-300' }}">
                            <input type="radio" name="vendor_type" value="china_supplier" class="hidden" required {{ old('vendor_type') == 'china_supplier' ? 'checked' : '' }}>
                            <i class="fas fa-plane text-green-500 block mb-1"></i>
                            <span class="text-xs font-medium">Importer</span>
                        </label>
                        <label class="flex-1 border rounded p-2 cursor-pointer hover:border-indigo-400 transition text-center vendor-type-option {{ old('vendor_type') == 'dropship' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-300' }}">
                            <input type="radio" name="vendor_type" value="dropship" class="hidden" required {{ old('vendor_type') == 'dropship' ? 'checked' : '' }}>
                            <i class="fas fa-box-open text-purple-500 block mb-1"></i>
                            <span class="text-xs font-medium">Dropship</span>
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="flex items-center">
                        <label class="w-24 text-sm text-gray-600 flex-shrink-0">Business *</label>
                        <input type="text" name="business_name" required value="{{ old('business_name') }}"
                               class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="Business name">
                    </div>
                    <div class="flex items-center">
                        <label class="w-24 text-sm text-gray-600 flex-shrink-0">Country *</label>
                        <select name="country" id="countrySelect" required class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Select</option>
                            <option value="Uganda" {{ old('country') == 'Uganda' ? 'selected' : '' }}>Uganda</option>
                            <option value="Kenya" {{ old('country') == 'Kenya' ? 'selected' : '' }}>Kenya</option>
                            <option value="Tanzania" {{ old('country') == 'Tanzania' ? 'selected' : '' }}>Tanzania</option>
                            <option value="Rwanda" {{ old('country') == 'Rwanda' ? 'selected' : '' }}>Rwanda</option>
                            <option value="China" {{ old('country') == 'China' ? 'selected' : '' }}>China</option>
                        </select>
                    </div>
                    <div class="flex items-center">
                        <label class="w-24 text-sm text-gray-600 flex-shrink-0">City</label>
                        <input type="text" name="city" value="{{ old('city') }}"
                               class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="City (optional)">
                    </div>
                    <div class="flex items-center">
                        <label class="w-24 text-sm text-gray-600 flex-shrink-0">Currency *</label>
                        <select name="preferred_currency" id="currencySelect" required class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Select</option>
                            <option value="UGX" {{ old('preferred_currency') == 'UGX' ? 'selected' : '' }}>UGX</option>
                            <option value="USD" {{ old('preferred_currency') == 'USD' ? 'selected' : '' }}>USD</option>
                            <option value="KES" {{ old('preferred_currency') == 'KES' ? 'selected' : '' }}>KES</option>
                            <option value="CNY" {{ old('preferred_currency') == 'CNY' ? 'selected' : '' }}>CNY</option>
                        </select>
                    </div>
                    <div class="flex items-center col-span-2">
                        <label class="w-24 text-sm text-gray-600 flex-shrink-0">Address</label>
                        <input type="text" name="address" value="{{ old('address') }}"
                               class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="Business address (optional)">
                    </div>
                    <div class="flex items-center col-span-2">
                        <label class="w-24 text-sm text-gray-600 flex-shrink-0">Turnover</label>
                        <div class="flex-1 relative">
                            <span id="turnoverPrefix" class="absolute left-3 top-2 text-gray-400 text-sm">UGX</span>
                            <input type="text" name="annual_turnover" id="annualTurnover" value="{{ old('annual_turnover') }}"
                                   class="w-full pl-12 px-3 py-2 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                                   placeholder="Optional" inputmode="numeric">
                            <p id="turnoverFeedback" class="text-xs mt-1 hidden"></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- China Verification Section (hidden by default) -->
            <div id="chinaVerificationSection" style="display: none;" class="transition-all duration-300">
                <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-red-500">
                    <h2 class="font-semibold text-gray-800 mb-3 flex items-center text-sm border-b pb-2">
                        <i class="fas fa-flag text-red-500 mr-2"></i>Chinese Company Verification
                        <span class="ml-auto text-xs text-red-400 font-normal">Required for China suppliers</span>
                    </h2>

                    <div class="grid grid-cols-2 gap-3">
                        <!-- Company Chinese Name -->
                        <div class="flex items-center col-span-2">
                            <label class="w-24 text-sm text-gray-600 flex-shrink-0">Company <span class="text-red-500">*</span></label>
                            <input type="text" name="china_company_name" id="chinaCompanyName" value="{{ old('china_company_name') }}"
                                   class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-red-500 focus:border-red-500"
                                   placeholder="Chinese company name (e.g. &#28145;&#22323;&#24066;&#21326;&#20026;&#25216;&#26415;&#26377;&#38480;&#20844;&#21496;)">
                        </div>

                        <!-- USCC -->
                        <div class="flex items-center col-span-2">
                            <label class="w-24 text-sm text-gray-600 flex-shrink-0">USCC <span class="text-red-500">*</span></label>
                            <div class="flex-1">
                                <input type="text" name="uscc" id="usccInput" value="{{ old('uscc') }}" maxlength="18"
                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-red-500 focus:border-red-500 font-mono tracking-wider"
                                       placeholder="18-character Unified Social Credit Code">
                                <p id="usccFeedback" class="text-xs mt-1 hidden"></p>
                            </div>
                        </div>

                        <!-- Legal Representative -->
                        <div class="flex items-center">
                            <label class="w-24 text-sm text-gray-600 flex-shrink-0">Legal Rep <span class="text-red-500">*</span></label>
                            <input type="text" name="legal_representative" value="{{ old('legal_representative') }}"
                                   class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-red-500 focus:border-red-500"
                                   placeholder="Legal representative name">
                        </div>

                        <!-- Registered Capital -->
                        <div class="flex items-center">
                            <label class="w-24 text-sm text-gray-600 flex-shrink-0">Capital</label>
                            <input type="text" name="registered_capital" value="{{ old('registered_capital') }}"
                                   class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-red-500 focus:border-red-500"
                                   placeholder="e.g. 1,000,000 CNY (optional)">
                        </div>

                        <!-- Business Scope -->
                        <div class="flex items-start col-span-2">
                            <label class="w-24 text-sm text-gray-600 flex-shrink-0 pt-2">Scope <span class="text-red-500">*</span></label>
                            <textarea name="business_scope" id="businessScope" rows="2"
                                      class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-red-500 focus:border-red-500"
                                      placeholder="Business scope (&#32463;&#33829;&#33539;&#22260;)">{{ old('business_scope') }}</textarea>
                        </div>

                        <!-- Registered Address -->
                        <div class="flex items-center col-span-2">
                            <label class="w-24 text-sm text-gray-600 flex-shrink-0">Reg. Addr <span class="text-red-500">*</span></label>
                            <input type="text" name="china_registered_address" value="{{ old('china_registered_address') }}"
                                   class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-red-500 focus:border-red-500"
                                   placeholder="Registered company address in China">
                        </div>
                    </div>

                    <!-- Business License Upload -->
                    <div class="mt-3 flex items-start">
                        <label class="w-24 text-sm text-gray-600 flex-shrink-0 pt-1">License <span class="text-red-500">*</span></label>
                        <div class="flex-1">
                            <div class="file-upload-box border-2 border-dashed border-red-300 rounded p-3 text-center cursor-pointer hover:border-red-400 transition bg-red-50"
                                 onclick="document.getElementById('business_license').click()">
                                <div id="licensePreview" class="text-center py-1">
                                    <i class="fas fa-file-certificate text-red-400 text-lg"></i>
                                </div>
                                <p class="text-xs text-red-500 font-medium">Business License (&#33829;&#19994;&#25191;&#29031;) *</p>
                                <input type="file" name="business_license" id="business_license" class="hidden" accept=".jpg,.jpeg,.png,.pdf">
                            </div>
                        </div>
                    </div>

                    <!-- Industry Permits -->
                    <div class="mt-3 flex items-start">
                        <label class="w-24 text-sm text-gray-600 flex-shrink-0 pt-1">Permits</label>
                        <div class="flex-1">
                            <div class="file-upload-box border-2 border-dashed border-gray-200 rounded p-3 text-center cursor-pointer hover:border-gray-400 transition"
                                 onclick="document.getElementById('industry_permits').click()">
                                <div id="permitsPreview" class="text-center py-1">
                                    <i class="fas fa-folder-open text-gray-300"></i>
                                </div>
                                <p class="text-xs text-gray-400">Industry Permits (optional, max 5)</p>
                                <input type="file" name="industry_permits[]" id="industry_permits" class="hidden" accept=".jpg,.jpeg,.png,.pdf" multiple>
                            </div>
                            <div id="permitsFileList" class="mt-1"></div>
                        </div>
                    </div>

                    <!-- Verification Resources -->
                    <div class="mt-4 p-3 bg-red-50 rounded border border-red-100">
                        <h3 class="text-xs font-semibold text-red-800 mb-2"><i class="fas fa-link mr-1"></i>Verification Resources</h3>
                        <div class="grid grid-cols-2 gap-2">
                            <a href="https://www.gsxt.gov.cn" target="_blank" rel="noopener" class="text-xs text-red-600 hover:underline flex items-center">
                                <i class="fas fa-external-link-alt mr-1"></i>GSXT (National Enterprise Credit)
                            </a>
                            <a href="http://www.customs.gov.cn" target="_blank" rel="noopener" class="text-xs text-red-600 hover:underline flex items-center">
                                <i class="fas fa-external-link-alt mr-1"></i>China Customs
                            </a>
                            <a href="https://www.chinverify.com" target="_blank" rel="noopener" class="text-xs text-red-600 hover:underline flex items-center">
                                <i class="fas fa-external-link-alt mr-1"></i>ChinVerify
                            </a>
                            <a href="https://www.qincheck.com" target="_blank" rel="noopener" class="text-xs text-red-600 hover:underline flex items-center">
                                <i class="fas fa-external-link-alt mr-1"></i>QINCheck
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Documents Section -->
            <div class="bg-white rounded-lg shadow-sm p-4">
                <h2 class="font-semibold text-gray-800 mb-3 flex items-center text-sm border-b pb-2">
                    <i class="fas fa-file-alt text-indigo-500 mr-2"></i>
                    <span id="docsTitle">Required Documents</span>
                </h2>

                <!-- National ID -->
                <div id="nationalIdSection" class="flex items-start mb-3">
                    <label class="w-24 text-sm text-gray-600 flex-shrink-0 pt-1">
                        National ID <span id="nationalIdRequired" class="text-red-500">*</span>
                    </label>
                    <div class="flex-1 grid grid-cols-2 gap-2">
                        <div class="file-upload-box border-2 border-dashed border-gray-300 rounded p-2 text-center cursor-pointer hover:border-indigo-400 transition"
                             onclick="document.getElementById('national_id_front').click()">
                            <div id="frontPreview" class="text-center py-1">
                                <i class="fas fa-id-card text-gray-400"></i>
                            </div>
                            <p class="text-xs text-gray-500" id="frontLabel">Front Side *</p>
                            <input type="file" name="national_id_front" id="national_id_front" class="hidden" accept=".jpg,.jpeg,.png,.pdf">
                        </div>
                        <div class="file-upload-box border-2 border-dashed border-gray-300 rounded p-2 text-center cursor-pointer hover:border-indigo-400 transition"
                             onclick="document.getElementById('national_id_back').click()">
                            <div id="backPreview" class="text-center py-1">
                                <i class="fas fa-id-card text-gray-400"></i>
                            </div>
                            <p class="text-xs text-gray-500" id="backLabel">Back Side *</p>
                            <input type="file" name="national_id_back" id="national_id_back" class="hidden" accept=".jpg,.jpeg,.png,.pdf">
                        </div>
                    </div>
                </div>

                <!-- Optional: Bank Statement & Proof of Address -->
                <div class="flex items-start mb-3">
                    <label class="w-24 text-sm text-gray-600 flex-shrink-0 pt-1">Financial</label>
                    <div class="flex-1 grid grid-cols-2 gap-2">
                        <div class="file-upload-box border-2 border-dashed border-gray-200 rounded p-2 text-center cursor-pointer hover:border-gray-400 transition"
                             onclick="document.getElementById('bank_statement').click()">
                            <div id="bankPreview" class="text-center py-1">
                                <i class="fas fa-file-invoice-dollar text-gray-300"></i>
                            </div>
                            <p class="text-xs text-gray-400">Bank Statement (opt)</p>
                            <input type="file" name="bank_statement" id="bank_statement" class="hidden" accept=".jpg,.jpeg,.png,.pdf">
                        </div>
                        <div class="file-upload-box border-2 border-dashed border-gray-200 rounded p-2 text-center cursor-pointer hover:border-gray-400 transition"
                             onclick="document.getElementById('proof_of_address').click()">
                            <div id="addressPreview" class="text-center py-1">
                                <i class="fas fa-home text-gray-300"></i>
                            </div>
                            <p class="text-xs text-gray-400">Address Proof (opt)</p>
                            <input type="file" name="proof_of_address" id="proof_of_address" class="hidden" accept=".jpg,.jpeg,.png,.pdf">
                        </div>
                    </div>
                </div>

                <!-- Optional: Guarantor Section -->
                <div class="flex items-start mb-3">
                    <label class="w-24 text-sm text-gray-600 flex-shrink-0 pt-1">Guarantor</label>
                    <div class="flex-1 grid grid-cols-3 gap-2">
                        <input type="text" name="guarantor_name" value="{{ old('guarantor_name') }}"
                               class="px-3 py-2 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="Name (optional)">
                        <input type="tel" name="guarantor_phone" value="{{ old('guarantor_phone') }}"
                               class="px-3 py-2 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="Phone (optional)">
                        <div class="file-upload-box border-2 border-dashed border-gray-200 rounded p-2 text-center cursor-pointer hover:border-gray-400 transition"
                             onclick="document.getElementById('guarantor_id').click()">
                            <div id="guarantorPreview" class="text-center">
                                <i class="fas fa-id-badge text-gray-300 text-sm"></i>
                            </div>
                            <p class="text-xs text-gray-400">ID (opt)</p>
                            <input type="file" name="guarantor_id" id="guarantor_id" class="hidden" accept=".jpg,.jpeg,.png,.pdf">
                        </div>
                    </div>
                </div>

                <!-- Optional Company Docs -->
                <div class="flex items-start">
                    <label class="w-24 text-sm text-gray-600 flex-shrink-0 pt-1">Company</label>
                    <div class="flex-1 grid grid-cols-2 gap-2">
                        <div class="file-upload-box border-2 border-dashed border-gray-200 rounded p-2 text-center cursor-pointer hover:border-gray-400 transition"
                             onclick="document.getElementById('company_registration').click()">
                            <div id="companyPreview" class="text-center py-1">
                                <i class="fas fa-file-contract text-gray-300"></i>
                            </div>
                            <p class="text-xs text-gray-400">Registration (opt)</p>
                            <input type="file" name="company_registration" id="company_registration" class="hidden" accept=".jpg,.jpeg,.png,.pdf">
                        </div>
                        <div class="file-upload-box border-2 border-dashed border-gray-200 rounded p-2 text-center cursor-pointer hover:border-gray-400 transition"
                             onclick="document.getElementById('tax_certificate').click()">
                            <div id="taxPreview" class="text-center py-1">
                                <i class="fas fa-receipt text-gray-300"></i>
                            </div>
                            <p class="text-xs text-gray-400">Tax Cert (opt)</p>
                            <input type="file" name="tax_certificate" id="tax_certificate" class="hidden" accept=".jpg,.jpeg,.png,.pdf">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Terms & Submit -->
            <div class="bg-white rounded-lg shadow-sm p-4">
                <div class="flex items-center justify-between">
                    <label class="flex items-center text-sm">
                        <input type="checkbox" name="terms" required class="mr-2 h-4 w-4 text-indigo-600 rounded focus:ring-indigo-500">
                        <span class="text-gray-600">I agree to the <a href="{{ route('site.terms') }}" class="text-indigo-600 hover:underline">Terms</a></span>
                    </label>
                    <button type="submit" id="submitBtn" class="bg-indigo-600 text-white font-medium py-2 px-6 rounded hover:bg-indigo-700 transition text-sm">
                        <i class="fas fa-paper-plane mr-2"></i>Submit Application
                    </button>
                </div>
            </div>
        </form>

        <p class="text-center text-xs text-gray-500 mt-4">
            <i class="fas fa-lock mr-1"></i>Secure • Review: 24-48 hours • Already have an account? <a href="{{ route('login') }}" class="text-indigo-600 hover:underline">Sign in</a>
        </p>
    </div>
</div>

<script>
// Get current vendor type
function getVendorType() {
    const checked = document.querySelector('input[name="vendor_type"]:checked');
    return checked ? checked.value : null;
}

function isChinaSupplier() {
    return getVendorType() === 'china_supplier';
}

// Toggle China verification section
function updateFormForVendorType() {
    const chinaSection = document.getElementById('chinaVerificationSection');
    const countrySelect = document.getElementById('countrySelect');
    const currencySelect = document.getElementById('currencySelect');
    const turnoverPrefix = document.getElementById('turnoverPrefix');
    const nationalIdRequired = document.getElementById('nationalIdRequired');
    const frontLabel = document.getElementById('frontLabel');
    const backLabel = document.getElementById('backLabel');
    const docsTitle = document.getElementById('docsTitle');
    const idFront = document.getElementById('national_id_front');
    const idBack = document.getElementById('national_id_back');

    if (isChinaSupplier()) {
        // Show China section
        chinaSection.style.display = 'block';

        // Auto-set country and currency
        countrySelect.value = 'China';
        currencySelect.value = 'CNY';
        turnoverPrefix.textContent = 'CNY';

        // National ID becomes optional
        nationalIdRequired.textContent = '';
        frontLabel.textContent = 'Front Side (opt)';
        backLabel.textContent = 'Back Side (opt)';
        docsTitle.textContent = 'Identity & Other Documents';
        idFront.removeAttribute('required');
        idBack.removeAttribute('required');

        // Make china fields required
        setChinaFieldsRequired(true);
    } else {
        // Hide China section
        chinaSection.style.display = 'none';

        // Reset currency prefix
        turnoverPrefix.textContent = currencySelect.value || 'UGX';

        // National ID required again
        nationalIdRequired.textContent = '*';
        frontLabel.textContent = 'Front Side *';
        backLabel.textContent = 'Back Side *';
        docsTitle.textContent = 'Required Documents';

        // China fields not required
        setChinaFieldsRequired(false);
    }
}

function setChinaFieldsRequired(required) {
    const chinaFields = ['chinaCompanyName', 'usccInput', 'businessScope'];
    const chinaInputNames = ['china_company_name', 'uscc', 'legal_representative', 'business_scope', 'china_registered_address'];

    chinaInputNames.forEach(name => {
        const el = document.querySelector(`[name="${name}"]`);
        if (el) {
            if (required) {
                el.setAttribute('required', 'required');
            } else {
                el.removeAttribute('required');
            }
        }
    });

    // Business license required for china
    const bizLicense = document.getElementById('business_license');
    if (bizLicense) {
        if (required) {
            bizLicense.setAttribute('required', 'required');
        } else {
            bizLicense.removeAttribute('required');
        }
    }
}

// Vendor type selection styling + toggle
document.querySelectorAll('.vendor-type-option').forEach(option => {
    option.addEventListener('click', function() {
        document.querySelectorAll('.vendor-type-option').forEach(o => {
            o.classList.remove('border-indigo-500', 'bg-indigo-50');
            o.classList.add('border-gray-300');
        });
        this.classList.remove('border-gray-300');
        this.classList.add('border-indigo-500', 'bg-indigo-50');

        // Update form based on vendor type
        setTimeout(updateFormForVendorType, 50);
    });
});

// Currency change updates turnover prefix
document.getElementById('currencySelect').addEventListener('change', function() {
    document.getElementById('turnoverPrefix').textContent = this.value || 'UGX';
});

// Real-time turnover validation (numbers only)
document.getElementById('annualTurnover').addEventListener('input', function(e) {
    const feedback = document.getElementById('turnoverFeedback');
    // Remove non-numeric characters
    this.value = this.value.replace(/[^0-9]/g, '');

    if (this.value && isNaN(parseInt(this.value))) {
        feedback.textContent = 'Please enter numbers only';
        feedback.classList.remove('hidden', 'text-green-600');
        feedback.classList.add('text-red-500');
        this.classList.add('border-red-400');
        this.classList.remove('border-gray-300');
    } else if (this.value) {
        // Format with commas for display hint
        feedback.textContent = 'Value: ' + parseInt(this.value).toLocaleString();
        feedback.classList.remove('hidden', 'text-red-500');
        feedback.classList.add('text-green-600');
        this.classList.remove('border-red-400');
        this.classList.add('border-gray-300');
    } else {
        feedback.classList.add('hidden');
        this.classList.remove('border-red-400');
        this.classList.add('border-gray-300');
    }
});

// Real-time USCC validation
document.getElementById('usccInput').addEventListener('input', function(e) {
    const feedback = document.getElementById('usccFeedback');
    // Remove non-alphanumeric
    this.value = this.value.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();

    if (this.value.length === 0) {
        feedback.classList.add('hidden');
        this.classList.remove('border-red-400', 'border-green-400');
        this.classList.add('border-gray-300');
    } else if (this.value.length < 18) {
        feedback.textContent = this.value.length + '/18 characters';
        feedback.classList.remove('hidden', 'text-green-600');
        feedback.classList.add('text-yellow-600');
        this.classList.remove('border-green-400');
        this.classList.add('border-yellow-400');
    } else if (this.value.length === 18) {
        feedback.textContent = 'Valid USCC format';
        feedback.classList.remove('hidden', 'text-yellow-600', 'text-red-500');
        feedback.classList.add('text-green-600');
        this.classList.remove('border-yellow-400', 'border-red-400');
        this.classList.add('border-green-400');
    }
});

// Industry permits file count validation
document.getElementById('industry_permits').addEventListener('change', function() {
    const fileList = document.getElementById('permitsFileList');
    if (this.files.length > 5) {
        alert('Maximum 5 industry permits allowed. Only the first 5 will be uploaded.');
    }
    // Show file count
    if (this.files.length > 0) {
        fileList.innerHTML = '<p class="text-xs text-green-600 font-medium">' + Math.min(this.files.length, 5) + ' file(s) selected</p>';
    } else {
        fileList.innerHTML = '';
    }
});

// File upload preview (for all file inputs)
document.querySelectorAll('input[type="file"]').forEach(input => {
    input.addEventListener('change', function() {
        // Skip industry_permits as it has its own handler
        if (this.id === 'industry_permits') return;

        const previewMap = {
            'national_id_front': 'frontPreview',
            'national_id_back': 'backPreview',
            'bank_statement': 'bankPreview',
            'proof_of_address': 'addressPreview',
            'guarantor_id': 'guarantorPreview',
            'company_registration': 'companyPreview',
            'tax_certificate': 'taxPreview',
            'business_license': 'licensePreview'
        };

        const previewId = previewMap[this.id];
        if (!previewId) return;

        const preview = document.getElementById(previewId);
        const box = this.closest('.file-upload-box');

        if (this.files && this.files[0]) {
            const file = this.files[0];

            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = '<img src="' + e.target.result + '" class="h-6 w-auto mx-auto rounded">';
                }
                reader.readAsDataURL(file);
            } else {
                preview.innerHTML = '<i class="fas fa-file-pdf text-red-500"></i>';
            }

            box.classList.remove('border-gray-300', 'border-gray-200', 'border-red-300');
            box.classList.add('border-green-400', 'bg-green-50');
            const label = box.querySelector('p');
            if (label) {
                label.textContent = 'Uploaded';
                label.classList.add('text-green-600', 'font-medium');
            }
        }
    });
});

// Form validation
document.getElementById('vendorForm').addEventListener('submit', function(e) {
    const vendorType = getVendorType();
    if (!vendorType) {
        e.preventDefault();
        alert('Please select a vendor type');
        return false;
    }

    if (isChinaSupplier()) {
        // Validate China-specific fields
        const uscc = document.getElementById('usccInput').value;
        if (uscc.length !== 18) {
            e.preventDefault();
            alert('USCC must be exactly 18 characters');
            document.getElementById('usccInput').focus();
            return false;
        }

        const bizLicense = document.getElementById('business_license');
        if (!bizLicense.files.length) {
            e.preventDefault();
            alert('Please upload your Business License');
            return false;
        }
    } else {
        // Validate National ID for non-china vendors
        const requiredFiles = ['national_id_front', 'national_id_back'];
        let missing = requiredFiles.filter(id => !document.getElementById(id).files.length);

        if (missing.length > 0) {
            e.preventDefault();
            alert('Please upload National ID (front and back)');
            return false;
        }
    }

    // Show loading state
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Submitting...';
});

// Initialize form state on page load (handles old() data)
document.addEventListener('DOMContentLoaded', function() {
    updateFormForVendorType();
});
</script>

<style>
.file-upload-box { min-height: 50px; display: flex; flex-direction: column; justify-content: center; align-items: center; }
.file-upload-box:hover { transform: translateY(-1px); }
#chinaVerificationSection { animation: slideDown 0.3s ease-out; }
@keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
@media (max-width: 640px) {
    .grid-cols-2 { grid-template-columns: 1fr; }
    .grid-cols-3 { grid-template-columns: 1fr; }
    .w-24 { width: 100%; margin-bottom: 4px; }
    .flex.items-center, .flex.items-start { flex-direction: column; align-items: stretch; }
}
</style>
@endsection
