<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - {{ config('app.name') }}</title>
    @include('partials.head')
</head>
<body class="bg-gray-50">
    @include('partials.header')
    
    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-blue-600 to-purple-600 text-white py-16">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-5xl font-bold mb-6">About {{ config('app.name') }}</h1>
            <p class="text-xl max-w-3xl mx-auto opacity-90">
                Building Africa's most trusted marketplace with escrow protection and seamless cross-border trade.
            </p>
        </div>
    </section>

    <div class="container mx-auto px-4 py-12">
        <!-- Our Story -->
        <div class="max-w-4xl mx-auto mb-16">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div>
                    <h2 class="text-3xl font-bold text-gray-800 mb-6">Our Story</h2>
                    <p class="text-gray-600 mb-4">
                        Founded in 2023, {{ config('app.name') }} emerged from a simple observation: online commerce in Africa lacked trust and security. Buyers were hesitant to pay upfront, while sellers worried about non-payment.
                    </p>
                    <p class="text-gray-600 mb-4">
                        We built a platform that solves this fundamental challenge through our innovative escrow system. Your payment is protected until you confirm delivery, creating a safe environment for both buyers and sellers.
                    </p>
                    <p class="text-gray-600">
                        Today, we're proud to connect thousands of buyers with verified sellers across Africa and beyond.
                    </p>
                </div>
                <div class="relative">
                    <img src="https://images.unsplash.com/photo-1556742049-0cfed4f6a45d" 
                         alt="Our Team" 
                         class="rounded-2xl shadow-xl">
                    <div class="absolute -bottom-6 -left-6 bg-white p-6 rounded-xl shadow-lg">
                        <p class="text-3xl font-bold text-blue-600">10,000+</p>
                        <p class="text-gray-600">Happy Customers</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mission & Vision -->
        <div class="grid md:grid-cols-2 gap-8 mb-16">
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                <div class="w-16 h-16 bg-blue-100 rounded-xl flex items-center justify-center mb-6">
                    <i class="fas fa-bullseye text-blue-600 text-2xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-800 mb-4">Our Mission</h3>
                <p class="text-gray-600">
                    To democratize e-commerce in Africa by providing a secure, transparent, and accessible platform that empowers both buyers and sellers to trade with confidence.
                </p>
            </div>
            
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                <div class="w-16 h-16 bg-purple-100 rounded-xl flex items-center justify-center mb-6">
                    <i class="fas fa-eye text-purple-600 text-2xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-800 mb-4">Our Vision</h3>
                <p class="text-gray-600">
                    To become Africa's most trusted marketplace, facilitating billions in secure transactions and connecting African businesses to global opportunities.
                </p>
            </div>
        </div>

        <!-- Values -->
        <div class="mb-16">
            <h2 class="text-3xl font-bold text-gray-800 text-center mb-12">Our Core Values</h2>
            <div class="grid md:grid-cols-3 gap-6">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 text-center">
                    <div class="w-14 h-14 bg-green-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-shield-alt text-green-600 text-xl"></i>
                    </div>
                    <h4 class="text-xl font-bold text-gray-800 mb-3">Trust & Security</h4>
                    <p class="text-gray-600">
                        We prioritize your security above all else. Our escrow system ensures every transaction is protected.
                    </p>
                </div>
                
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 text-center">
                    <div class="w-14 h-14 bg-blue-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-handshake text-blue-600 text-xl"></i>
                    </div>
                    <h4 class="text-xl font-bold text-gray-800 mb-3">Transparency</h4>
                    <p class="text-gray-600">
                        Clear pricing, honest reviews, and open communication define every interaction on our platform.
                    </p>
                </div>
                
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 text-center">
                    <div class="w-14 h-14 bg-purple-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-users text-purple-600 text-xl"></i>
                    </div>
                    <h4 class="text-xl font-bold text-gray-800 mb-3">Community</h4>
                    <p class="text-gray-600">
                        We build tools that empower communities, creating economic opportunities for all.
                    </p>
                </div>
            </div>
        </div>

        <!-- How It Works -->
        <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-3xl p-8 md:p-12 mb-16">
            <h2 class="text-3xl font-bold text-gray-800 text-center mb-12">How It Works</h2>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
                        <span class="text-2xl font-bold text-blue-600">1</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">Browse & Select</h3>
                    <p class="text-gray-600">
                        Discover thousands of products from verified local and international sellers.
                    </p>
                </div>
                
                <div class="text-center">
                    <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
                        <span class="text-2xl font-bold text-blue-600">2</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">Secure Payment</h3>
                    <p class="text-gray-600">
                        Pay through our escrow system. Your money is protected until you confirm delivery.
                    </p>
                </div>
                
                <div class="text-center">
                    <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
                        <span class="text-2xl font-bold text-blue-600">3</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">Receive & Confirm</h3>
                    <p class="text-gray-600">
                        Get your order delivered and confirm receipt to release payment to the seller.
                    </p>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="text-center">
            <h2 class="text-3xl font-bold text-gray-800 mb-6">Join Our Growing Community</h2>
            <p class="text-gray-600 mb-8 max-w-2xl mx-auto">
                Whether you're looking to buy with confidence or sell with security, {{ config('app.name') }} is here for you.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('register') }}" 
                   class="px-8 py-3 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 transition transform hover:-translate-y-1">
                    Start Shopping
                </a>
                <a href="{{ route('vendor.onboard.create') }}" 
                   class="px-8 py-3 border-2 border-blue-600 text-blue-600 font-bold rounded-lg hover:bg-blue-50 transition">
                    Become a Seller
                </a>
            </div>
        </div>
    </div>

    @include('partials.footer')
</body>
</html>