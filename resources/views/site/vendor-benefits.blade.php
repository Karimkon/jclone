@extends('layouts.app')

@section('title', 'Vendor Agreement - ' . config('app.name'))
@section('description', 'Terms and conditions for vendors on our marketplace platform')

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <div class="container mx-auto px-4">
        <!-- Header -->
        <div class="max-w-4xl mx-auto text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">Vendor Agreement</h1>
            <p class="text-lg text-gray-600">
                Terms and conditions governing the relationship between vendors and {{ config('app.name') }}
            </p>
            <div class="mt-6">
                <span class="inline-block px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg">
                    Last Updated: {{ date('F j, Y') }}
                </span>
            </div>
        </div>

        <!-- Content -->
        <div class="max-w-4xl mx-auto bg-white rounded-2xl shadow-lg overflow-hidden">
            <!-- Navigation Tabs -->
            <div class="border-b border-gray-200">
                <nav class="flex flex-wrap" aria-label="Tabs">
                    <a href="#overview" 
                       class="flex-1 text-center py-4 px-6 text-sm font-medium border-b-2 border-primary text-primary">
                        <i class="fas fa-file-contract mr-2"></i> Overview
                    </a>
                    <a href="#responsibilities" 
                       class="flex-1 text-center py-4 px-6 text-sm font-medium text-gray-500 hover:text-gray-700">
                        <i class="fas fa-tasks mr-2"></i> Responsibilities
                    </a>
                    <a href="#commissions" 
                       class="flex-1 text-center py-4 px-6 text-sm font-medium text-gray-500 hover:text-gray-700">
                        <i class="fas fa-percentage mr-2"></i> Commissions
                    </a>
                    <a href="#termination" 
                       class="flex-1 text-center py-4 px-6 text-sm font-medium text-gray-500 hover:text-gray-700">
                        <i class="fas fa-ban mr-2"></i> Termination
                    </a>
                </nav>
            </div>

            <div class="p-8 md:p-12">
                <!-- Overview Section -->
                <section id="overview" class="mb-12">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">1. Agreement Overview</h2>
                    
                    <div class="space-y-6">
                        <div class="bg-blue-50 border-l-4 border-blue-500 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-info-circle text-blue-500"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-blue-700">
                                        This Vendor Agreement ("Agreement") is entered into between you ("Vendor") and 
                                        {{ config('app.name') }} ("Platform"). By registering as a vendor, you agree to be bound by these terms.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <h3 class="text-xl font-semibold text-gray-800">1.1 Definitions</h3>
                            <ul class="space-y-3">
                                <li class="flex items-start">
                                    <i class="fas fa-check text-green-500 mt-1 mr-3"></i>
                                    <span><strong>Platform:</strong> The {{ config('app.name') }} online marketplace and related services.</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check text-green-500 mt-1 mr-3"></i>
                                    <span><strong>Vendor:</strong> Any individual or business entity registered to sell products on the Platform.</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check text-green-500 mt-1 mr-3"></i>
                                    <span><strong>Products:</strong> Goods listed for sale by the Vendor on the Platform.</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check text-green-500 mt-1 mr-3"></i>
                                    <span><strong>Commission:</strong> The percentage or fixed fee retained by the Platform on each sale.</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </section>

                <!-- Responsibilities Section -->
                <section id="responsibilities" class="mb-12">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">2. Vendor Responsibilities</h2>
                    
                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="bg-gray-50 rounded-xl p-6">
                            <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center mb-4">
                                <i class="fas fa-box text-primary text-xl"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-3">Product Quality</h3>
                            <ul class="space-y-2">
                                <li class="flex items-start">
                                    <i class="fas fa-circle text-xs text-primary mt-2 mr-2"></i>
                                    <span>Products must match descriptions and images provided</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-circle text-xs text-primary mt-2 mr-2"></i>
                                    <span>Comply with all applicable laws and regulations</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-circle text-xs text-primary mt-2 mr-2"></i>
                                    <span>No counterfeit or prohibited items</span>
                                </li>
                            </ul>
                        </div>

                        <div class="bg-gray-50 rounded-xl p-6">
                            <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center mb-4">
                                <i class="fas fa-truck text-primary text-xl"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-3">Shipping & Delivery</h3>
                            <ul class="space-y-2">
                                <li class="flex items-start">
                                    <i class="fas fa-circle text-xs text-primary mt-2 mr-2"></i>
                                    <span>Ship products within 2 business days of order</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-circle text-xs text-primary mt-2 mr-2"></i>
                                    <span>Provide accurate tracking information</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-circle text-xs text-primary mt-2 mr-2"></i>
                                    <span>Use approved logistics partners</span>
                                </li>
                            </ul>
                        </div>

                        <div class="bg-gray-50 rounded-xl p-6">
                            <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center mb-4">
                                <i class="fas fa-headset text-primary text-xl"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-3">Customer Service</h3>
                            <ul class="space-y-2">
                                <li class="flex items-start">
                                    <i class="fas fa-circle text-xs text-primary mt-2 mr-2"></i>
                                    <span>Respond to customer inquiries within 24 hours</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-circle text-xs text-primary mt-2 mr-2"></i>
                                    <span>Handle returns and refunds promptly</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-circle text-xs text-primary mt-2 mr-2"></i>
                                    <span>Maintain minimum 4.0-star rating</span>
                                </li>
                            </ul>
                        </div>

                        <div class="bg-gray-50 rounded-xl p-6">
                            <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center mb-4">
                                <i class="fas fa-chart-line text-primary text-xl"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-3">Reporting</h3>
                            <ul class="space-y-2">
                                <li class="flex items-start">
                                    <i class="fas fa-circle text-xs text-primary mt-2 mr-2"></i>
                                    <span>Update inventory regularly</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-circle text-xs text-primary mt-2 mr-2"></i>
                                    <span>Report any issues or disputes immediately</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-circle text-xs text-primary mt-2 mr-2"></i>
                                    <span>Maintain accurate business records</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </section>

                <!-- Commissions Section -->
                <section id="commissions" class="mb-12">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">3. Commissions & Fees</h2>
                    
                    <div class="bg-gradient-to-r from-primary/10 to-indigo-100 rounded-xl p-6 mb-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xl font-bold text-gray-800">Commission Structure</h3>
                            <span class="px-4 py-2 bg-primary text-white rounded-full font-bold">15%</span>
                        </div>
                        <p class="text-gray-700 mb-4">
                            The Platform charges a commission on each successful sale. This fee covers payment processing, 
                            escrow services, customer support, and platform maintenance.
                        </p>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <h4 class="text-lg font-semibold text-gray-800 mb-3">3.1 Commission Rates</h4>
                            <div class="overflow-x-auto">
                                <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">Product Category</th>
                                            <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">Commission Rate</th>
                                            <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">Minimum Fee</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        <tr>
                                            <td class="py-3 px-4 text-sm text-gray-700">Electronics</td>
                                            <td class="py-3 px-4 text-sm text-gray-700">15%</td>
                                            <td class="py-3 px-4 text-sm text-gray-700">UGX 1,000</td>
                                        </tr>
                                        <tr class="bg-gray-50">
                                            <td class="py-3 px-4 text-sm text-gray-700">Clothing & Fashion</td>
                                            <td class="py-3 px-4 text-sm text-gray-700">12%</td>
                                            <td class="py-3 px-4 text-sm text-gray-700">UGX 500</td>
                                        </tr>
                                        <tr>
                                            <td class="py-3 px-4 text-sm text-gray-700">Home & Appliances</td>
                                            <td class="py-3 px-4 text-sm text-gray-700">10%</td>
                                            <td class="py-3 px-4 text-sm text-gray-700">UGX 1,500</td>
                                        </tr>
                                        <tr class="bg-gray-50">
                                            <td class="py-3 px-4 text-sm text-gray-700">Imported Goods</td>
                                            <td class="py-3 px-4 text-sm text-gray-700">18%</td>
                                            <td class="py-3 px-4 text-sm text-gray-700">UGX 2,000</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div>
                            <h4 class="text-lg font-semibold text-gray-800 mb-3">3.2 Payment Schedule</h4>
                            <div class="space-y-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                        <i class="fas fa-calendar-check text-green-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-gray-700"><strong>Weekly Payouts:</strong> Every Monday for orders delivered the previous week</p>
                                    </div>
                                </div>
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                        <i class="fas fa-clock text-blue-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-gray-700"><strong>Processing Time:</strong> 1-3 business days after payout request</p>
                                    </div>
                                </div>
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center mr-3">
                                        <i class="fas fa-money-bill-wave text-purple-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-gray-700"><strong>Payment Methods:</strong> Bank transfer, Mobile Money, Platform Wallet</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Termination Section -->
                <section id="termination">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">4. Termination & Suspension</h2>
                    
                    <div class="space-y-6">
                        <div class="border-l-4 border-red-500 bg-red-50 p-4">
                            <h4 class="text-lg font-semibold text-red-800 mb-2">Grounds for Immediate Termination</h4>
                            <ul class="space-y-2 text-red-700">
                                <li class="flex items-start">
                                    <i class="fas fa-times-circle mt-1 mr-2"></i>
                                    <span>Selling counterfeit or prohibited items</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-times-circle mt-1 mr-2"></i>
                                    <span>Multiple customer complaints or disputes</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-times-circle mt-1 mr-2"></i>
                                    <span>Violation of intellectual property rights</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-times-circle mt-1 mr-2"></i>
                                    <span>Fraudulent activities or misrepresentation</span>
                                </li>
                            </ul>
                        </div>

                        <div>
                            <h4 class="text-lg font-semibold text-gray-800 mb-3">4.1 Account Suspension</h4>
                            <p class="text-gray-700 mb-4">
                                The Platform reserves the right to suspend vendor accounts for investigation due to:
                            </p>
                            <div class="grid md:grid-cols-2 gap-4">
                                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                    <h5 class="font-semibold text-yellow-800 mb-2">Temporary Suspension</h5>
                                    <ul class="text-sm text-yellow-700 space-y-1">
                                        <li>• Low performance metrics</li>
                                        <li>• Late shipping issues</li>
                                        <li>• Customer service delays</li>
                                    </ul>
                                </div>
                                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                    <h5 class="font-semibold text-red-800 mb-2">Permanent Termination</h5>
                                    <ul class="text-sm text-red-700 space-y-1">
                                        <li>• Repeated violations</li>
                                        <li>• Legal compliance issues</li>
                                        <li>• Platform policy abuse</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h4 class="text-lg font-semibold text-gray-800 mb-3">4.2 Appeal Process</h4>
                            <div class="bg-green-50 rounded-lg p-5">
                                <div class="flex items-center mb-3">
                                    <i class="fas fa-gavel text-green-600 text-xl mr-3"></i>
                                    <h5 class="text-lg font-semibold text-green-800">How to Appeal</h5>
                                </div>
                                <ol class="space-y-3 text-green-700">
                                    <li class="flex items-start">
                                        <span class="font-bold mr-2">1.</span>
                                        <span>Submit appeal via vendor dashboard within 7 days of suspension</span>
                                    </li>
                                    <li class="flex items-start">
                                        <span class="font-bold mr-2">2.</span>
                                        <span>Provide supporting evidence and documentation</span>
                                    </li>
                                    <li class="flex items-start">
                                        <span class="font-bold mr-2">3.</span>
                                        <span>Review by platform admin within 14 business days</span>
                                    </li>
                                    <li class="flex items-start">
                                        <span class="font-bold mr-2">4.</span>
                                        <span>Receive final decision via email and dashboard notification</span>
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Agreement Acceptance -->
                <div class="mt-12 pt-8 border-t border-gray-200">
                    <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                        <div class="text-center md:text-left">
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Accept Agreement</h3>
                            <p class="text-gray-600">By registering as a vendor, you acknowledge reading and agreeing to these terms</p>
                        </div>
                        <div class="flex items-center space-x-4">
                            <a href="{{ route('vendor.onboard.create') }}" 
                               class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-indigo-700 font-medium">
                                <i class="fas fa-check-circle mr-2"></i> I Agree & Continue
                            </a>
                            <a href="{{ route('site.vendorBenefits') }}" 
                               class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium">
                                Learn About Benefits
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Resources -->
        <div class="max-w-4xl mx-auto mt-8 grid md:grid-cols-3 gap-6">
            <a href="{{ route('site.terms') }}" 
               class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition text-center">
                <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-file-alt text-xl"></i>
                </div>
                <h4 class="font-semibold text-gray-800 mb-2">Platform Terms</h4>
                <p class="text-sm text-gray-600">General terms and conditions for all users</p>
            </a>
            
            <a href="{{ route('site.privacy') }}" 
               class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition text-center">
                <div class="w-12 h-12 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-user-shield text-xl"></i>
                </div>
                <h4 class="font-semibold text-gray-800 mb-2">Privacy Policy</h4>
                <p class="text-sm text-gray-600">How we handle your data and information</p>
            </a>
            
            <a href="{{ route('site.faq') }}" 
               class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition text-center">
                <div class="w-12 h-12 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-question-circle text-xl"></i>
                </div>
                <h4 class="font-semibold text-gray-800 mb-2">FAQ & Support</h4>
                <p class="text-sm text-gray-600">Common questions and support resources</p>
            </a>
        </div>
    </div>
</div>

@section('scripts')
<script>
    // Smooth scroll for anchor links
    document.addEventListener('DOMContentLoaded', function() {
        const tabLinks = document.querySelectorAll('nav a[href^="#"]');
        
        tabLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                const targetId = this.getAttribute('href');
                const targetElement = document.querySelector(targetId);
                
                if (targetElement) {
                    // Update active tab
                    tabLinks.forEach(l => {
                        l.classList.remove('border-primary', 'text-primary');
                        l.classList.add('text-gray-500');
                    });
                    this.classList.remove('text-gray-500');
                    this.classList.add('border-primary', 'text-primary');
                    
                    // Scroll to section
                    window.scrollTo({
                        top: targetElement.offsetTop - 100,
                        behavior: 'smooth'
                    });
                }
            });
        });
        
        // Highlight current section on scroll
        const sections = document.querySelectorAll('section[id]');
        const observerOptions = {
            root: null,
            rootMargin: '-100px 0px -50px 0px',
            threshold: 0.1
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const id = entry.target.getAttribute('id');
                    const activeLink = document.querySelector(`nav a[href="#${id}"]`);
                    
                    if (activeLink) {
                        tabLinks.forEach(l => {
                            l.classList.remove('border-primary', 'text-primary');
                            l.classList.add('text-gray-500');
                        });
                        activeLink.classList.remove('text-gray-500');
                        activeLink.classList.add('border-primary', 'text-primary');
                    }
                }
            });
        }, observerOptions);
        
        sections.forEach(section => {
            observer.observe(section);
        });
    });
</script>
@endsection
@endsection