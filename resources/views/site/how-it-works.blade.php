<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>How It Works - {{ config('app.name') }}</title>
    @include('partials.head')
</head>
<body class="bg-gray-50">
    @include('partials.header')
    
    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-brand-600 to-purple-600 text-white py-16">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-5xl font-bold mb-6">How It Works</h1>
            <p class="text-xl max-w-3xl mx-auto opacity-90">
                Simple, secure, and efficient marketplace for buyers and sellers.
            </p>
        </div>
    </section>

    <div class="container mx-auto px-4 py-12">
        <!-- For Buyers -->
        <div class="mb-20">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-800 mb-4">For Buyers</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">Shop with confidence using our secure escrow system.</p>
            </div>
            
            <div class="relative">
                <!-- Vertical Line for Desktop -->
                <div class="hidden lg:block absolute left-1/2 transform -translate-x-1/2 h-full w-1 bg-gray-200"></div>
                
                @foreach($buyerSteps as $step)
                <div class="relative mb-12 lg:mb-16">
                    <div class="flex flex-col lg:flex-row items-center {{ $step['step'] % 2 == 1 ? '' : 'lg:flex-row-reverse' }}">
                        <!-- Step Content -->
                        <div class="lg:w-1/2 {{ $step['step'] % 2 == 1 ? 'lg:pr-12' : 'lg:pl-12' }} mb-6 lg:mb-0">
                            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                                <div class="inline-flex items-center justify-center w-12 h-12 bg-brand-600 text-white rounded-full text-xl font-bold mb-4">
                                    {{ $step['step'] }}
                                </div>
                                <h3 class="text-2xl font-bold text-gray-800 mb-3">{{ $step['title'] }}</h3>
                                <p class="text-gray-600">{{ $step['description'] }}</p>
                            </div>
                        </div>
                        
                        <!-- Step Icon (Centered for mobile, side for desktop) -->
                        <div class="lg:w-1/2 flex justify-center lg:justify-{{ $step['step'] % 2 == 1 ? 'start' : 'end' }}">
                            <div class="w-24 h-24 {{ $step['step'] % 2 == 1 ? 'bg-brand-100' : 'bg-purple-100' }} rounded-full flex items-center justify-center">
                                @if($step['step'] == 1)
                                <i class="fas fa-search text-brand-600 text-3xl"></i>
                                @elseif($step['step'] == 2)
                                <i class="fas fa-shopping-cart text-brand-600 text-3xl"></i>
                                @elseif($step['step'] == 3)
                                <i class="fas fa-lock text-emerald-600 text-3xl"></i>
                                @elseif($step['step'] == 4)
                                <i class="fas fa-truck text-blue-600 text-3xl"></i>
                                @else
                                <i class="fas fa-check-circle text-purple-600 text-3xl"></i>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- For Sellers -->
        <div class="mb-20">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-800 mb-4">For Sellers</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">Reach more customers with our powerful selling tools.</p>
            </div>
            
            <div class="relative">
                <!-- Vertical Line for Desktop -->
                <div class="hidden lg:block absolute left-1/2 transform -translate-x-1/2 h-full w-1 bg-gray-200"></div>
                
                @foreach($vendorSteps as $step)
                <div class="relative mb-12 lg:mb-16">
                    <div class="flex flex-col lg:flex-row items-center {{ $step['step'] % 2 == 1 ? '' : 'lg:flex-row-reverse' }}">
                        <!-- Step Icon (Centered for mobile, side for desktop) -->
                        <div class="lg:w-1/2 flex justify-center lg:justify-{{ $step['step'] % 2 == 1 ? 'end' : 'start' }} mb-6 lg:mb-0">
                            <div class="w-24 h-24 {{ $step['step'] % 2 == 1 ? 'bg-emerald-100' : 'bg-blue-100' }} rounded-full flex items-center justify-center">
                                @if($step['step'] == 1)
                                <i class="fas fa-user-plus text-emerald-600 text-3xl"></i>
                                @elseif($step['step'] == 2)
                                <i class="fas fa-list-alt text-emerald-600 text-3xl"></i>
                                @elseif($step['step'] == 3)
                                <i class="fas fa-bell text-blue-600 text-3xl"></i>
                                @elseif($step['step'] == 4)
                                <i class="fas fa-box text-blue-600 text-3xl"></i>
                                @else
                                <i class="fas fa-money-bill-wave text-purple-600 text-3xl"></i>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Step Content -->
                        <div class="lg:w-1/2 {{ $step['step'] % 2 == 1 ? 'lg:pl-12' : 'lg:pr-12' }}">
                            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                                <div class="inline-flex items-center justify-center w-12 h-12 bg-emerald-600 text-white rounded-full text-xl font-bold mb-4">
                                    {{ $step['step'] }}
                                </div>
                                <h3 class="text-2xl font-bold text-gray-800 mb-3">{{ $step['title'] }}</h3>
                                <p class="text-gray-600">{{ $step['description'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Escrow System Explanation -->
        <div class="bg-gradient-to-r from-brand-50 to-purple-50 rounded-3xl p-8 mb-16">
            <div class="text-center mb-10">
                <h2 class="text-3xl font-bold text-gray-800 mb-4">How Escrow Protects You</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">Your payment is secure until you confirm delivery.</p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                        <i class="fas fa-lock text-brand-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">Secure Payment</h3>
                    <p class="text-gray-600">Payment is held securely in escrow account</p>
                </div>
                
                <div class="text-center">
                    <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                        <i class="fas fa-truck text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">Order Processing</h3>
                    <p class="text-gray-600">Seller ships product after payment confirmation</p>
                </div>
                
                <div class="text-center">
                    <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                        <i class="fas fa-check-circle text-emerald-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">Delivery Confirmation</h3>
                    <p class="text-gray-600">Payment released only after you confirm receipt</p>
                </div>
            </div>
        </div>

        <!-- Import Process -->
        <div class="mb-16">
            <div class="text-center mb-10">
                <h2 class="text-3xl font-bold text-gray-800 mb-4">Import Made Easy</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">We handle the complex process for you</p>
            </div>
            
            <div class="bg-white rounded-2xl shadow-sm p-8 border border-gray-100">
                <div class="grid md:grid-cols-4 gap-6">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-globe text-blue-600 text-xl"></i>
                        </div>
                        <h4 class="font-bold text-gray-800 mb-2">Source Products</h4>
                        <p class="text-sm text-gray-600">Find products from international suppliers</p>
                    </div>
                    
                    <div class="text-center">
                        <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-calculator text-purple-600 text-xl"></i>
                        </div>
                        <h4 class="font-bold text-gray-800 mb-2">Calculate Costs</h4>
                        <p class="text-sm text-gray-600">Get instant duty and shipping estimates</p>
                    </div>
                    
                    <div class="text-center">
                        <div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-file-contract text-emerald-600 text-xl"></i>
                        </div>
                        <h4 class="font-bold text-gray-800 mb-2">Clear Customs</h4>
                        <p class="text-sm text-gray-600">We handle all documentation and clearance</p>
                    </div>
                    
                    <div class="text-center">
                        <div class="w-16 h-16 bg-brand-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-home text-brand-600 text-xl"></i>
                        </div>
                        <h4 class="font-bold text-gray-800 mb-2">Deliver to Door</h4>
                        <p class="text-sm text-gray-600">Final delivery to your location</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="text-center">
            <h2 class="text-3xl font-bold text-gray-800 mb-6">Ready to Get Started?</h2>
            <p class="text-gray-600 mb-8 max-w-2xl mx-auto">
                Join our growing community of buyers and sellers today.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('register') }}" 
                   class="px-8 py-3 bg-brand-600 text-white font-bold rounded-lg hover:bg-brand-700 transition transform hover:-translate-y-1">
                    Start Shopping
                </a>
                <a href="{{ route('vendor.onboard.create') }}" 
                   class="px-8 py-3 border-2 border-brand-600 text-brand-600 font-bold rounded-lg hover:bg-brand-50 transition">
                    Become a Seller
                </a>
                <a href="{{ route('site.faq') }}" 
                   class="px-8 py-3 bg-gray-100 text-gray-700 font-bold rounded-lg hover:bg-gray-200 transition">
                    View FAQs
                </a>
            </div>
        </div>
    </div>

    @include('partials.footer')
</body>
</html>