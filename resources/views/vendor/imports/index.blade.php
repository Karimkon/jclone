@extends('layouts.vendor')

@section('title', 'Import Products - Vendor Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Import Products</h1>
            <p class="text-gray-600">Manage your imported products and shipments</p>
        </div>
        <div>
            <a href="{{ route('vendor.imports.create') }}" 
               class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-indigo-700 font-medium">
                <i class="fas fa-plus mr-2"></i> New Import Request
            </a>
        </div>
    </div>

    <!-- Import Process -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">How to Import Products</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="text-center">
                <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-1 text-xl"></i>
                </div>
                <h3 class="font-bold text-gray-900">Calculate Costs</h3>
                <p class="text-sm text-gray-600 mt-1">Use our import calculator</p>
            </div>
            
            <div class="text-center">
                <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-2 text-xl"></i>
                </div>
                <h3 class="font-bold text-gray-900">Submit Request</h3>
                <p class="text-sm text-gray-600 mt-1">Provide product details</p>
            </div>
            
            <div class="text-center">
                <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-3 text-xl"></i>
                </div>
                <h3 class="font-bold text-gray-900">Clearance & Shipping</h3>
                <p class="text-sm text-gray-600 mt-1">We handle customs clearance</p>
            </div>
            
            <div class="text-center">
                <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-4 text-xl"></i>
                </div>
                <h3 class="font-bold text-gray-900">Receive & Sell</h3>
                <p class="text-sm text-gray-600 mt-1">List and sell your imported goods</p>
            </div>
        </div>
    </div>

    <!-- Import Calculator -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Import Cost Calculator</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <form id="importCalculator">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Product Value ($)
                            </label>
                            <input type="number" id="productValue" 
                                   class="w-full border border-gray-300 rounded-lg p-3"
                                   placeholder="e.g., 1000" value="1000">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Shipping Cost ($)
                            </label>
                            <input type="number" id="shippingCost" 
                                   class="w-full border border-gray-300 rounded-lg p-3"
                                   placeholder="e.g., 200" value="200">
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Duty Rate (%)
                                </label>
                                <input type="number" id="dutyRate" 
                                       class="w-full border border-gray-300 rounded-lg p-3"
                                       value="10" step="0.1">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    VAT Rate (%)
                                </label>
                                <input type="number" id="vatRate" 
                                       class="w-full border border-gray-300 rounded-lg p-3"
                                       value="18" step="0.1">
                            </div>
                        </div>
                        
                        <button type="button" onclick="calculateImportCost()" 
                                class="w-full bg-primary text-white py-3 rounded-lg font-medium hover:bg-indigo-700">
                            Calculate Total Cost
                        </button>
                    </div>
                </form>
            </div>
            
            <div>
                <h3 class="text-lg font-bold text-gray-900 mb-4">Cost Breakdown</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Product Value:</span>
                        <span id="productValueResult" class="font-medium">$1,000.00</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Shipping Cost:</span>
                        <span id="shippingCostResult" class="font-medium">$200.00</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Import Duty (10%):</span>
                        <span id="dutyCost" class="font-medium">$120.00</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">VAT (18%):</span>
                        <span id="vatCost" class="font-medium">$237.60</span>
                    </div>
                    <div class="border-t pt-3 mt-3">
                        <div class="flex justify-between text-lg font-bold">
                            <span>Total Landed Cost:</span>
                            <span id="totalCost" class="text-primary">$1,557.60</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CTA -->
    <div class="text-center">
        <a href="{{ route('vendor.imports.create') }}" 
           class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-primary to-indigo-600 text-white rounded-lg font-bold hover:opacity-90">
            <i class="fas fa-plane mr-3"></i>
            Start Your First Import
        </a>
        <p class="text-gray-600 mt-3">
            Get help with your import at <a href="mailto:imports@jclone.com" class="text-primary">imports@jclone.com</a>
        </p>
    </div>
</div>

<script>
function calculateImportCost() {
    const productValue = parseFloat(document.getElementById('productValue').value) || 0;
    const shippingCost = parseFloat(document.getElementById('shippingCost').value) || 0;
    const dutyRate = parseFloat(document.getElementById('dutyRate').value) || 10;
    const vatRate = parseFloat(document.getElementById('vatRate').value) || 18;
    
    const cif = productValue + shippingCost;
    const duty = cif * (dutyRate / 100);
    const vatBase = cif + duty;
    const vat = vatBase * (vatRate / 100);
    const totalCost = cif + duty + vat;
    
    // Update results
    document.getElementById('productValueResult').textContent = '$' + productValue.toFixed(2);
    document.getElementById('shippingCostResult').textContent = '$' + shippingCost.toFixed(2);
    document.getElementById('dutyCost').textContent = '$' + duty.toFixed(2);
    document.getElementById('vatCost').textContent = '$' + vat.toFixed(2);
    document.getElementById('totalCost').textContent = '$' + totalCost.toFixed(2);
    
    // Update duty and VAT rates in display
    document.querySelectorAll('.flex.justify-between')[2].querySelector('span:first-child').textContent = 
        `Import Duty (${dutyRate}%):`;
    document.querySelectorAll('.flex.justify-between')[3].querySelector('span:first-child').textContent = 
        `VAT (${vatRate}%):`;
}

// Initialize calculator
document.addEventListener('DOMContentLoaded', calculateImportCost);
</script>
@endsection