<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - {{ config('app.name') }}</title>
    @include('partials.head')
</head>
<body class="bg-gray-50">
    @include('partials.header')
    
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-6xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-10">
                <h1 class="text-4xl font-bold text-gray-800 mb-3">Contact Us</h1>
                <p class="text-gray-600 max-w-2xl mx-auto">
                    Have questions? We're here to help. Get in touch with our team for assistance.
                </p>
            </div>

            <div class="grid md:grid-cols-2 gap-8">
                <!-- Contact Form -->
                <div class="bg-white rounded-xl shadow-sm p-6 md:p-8">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6">Send us a Message</h2>
                    
                    @if(session('success'))
                        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                            <p class="text-green-700 font-medium">{{ session('success') }}</p>
                        </div>
                    @endif
                    
                    <form action="{{ route('site.contact.submit') }}" method="POST">
                        @csrf
                        
                        <div class="space-y-5">
                            <!-- Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                                <input type="text" id="name" name="name" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                    placeholder="Your name">
                            </div>
                            
                            <!-- Email -->
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address *</label>
                                <input type="email" id="email" name="email" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                    placeholder="you@example.com">
                            </div>
                            
                            <!-- Contact Type -->
                            <div>
                                <label for="contact_type" class="block text-sm font-medium text-gray-700 mb-2">I am a *</label>
                                <select id="contact_type" name="contact_type" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                                    <option value="">Select one...</option>
                                    <option value="buyer">Buyer/Customer</option>
                                    <option value="vendor">Vendor/Seller</option>
                                    <option value="support">Support Inquiry</option>
                                    <option value="partner">Partnership/Business</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            
                            <!-- Subject -->
                            <div>
                                <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">Subject *</label>
                                <input type="text" id="subject" name="subject" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                    placeholder="What is this regarding?">
                            </div>
                            
                            <!-- Message -->
                            <div>
                                <label for="message" class="block text-sm font-medium text-gray-700 mb-2">Message *</label>
                                <textarea id="message" name="message" rows="5" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                    placeholder="Please describe your inquiry in detail..."></textarea>
                            </div>
                            
                            <!-- Submit -->
                            <div>
                                <button type="submit"
                                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-300 transform hover:-translate-y-1">
                                    <i class="fas fa-paper-plane mr-2"></i>Send Message
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Contact Information -->
                <div class="space-y-6">
                    <!-- Contact Methods -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="text-xl font-bold text-gray-800 mb-6">Contact Methods</h3>
                        <div class="space-y-4">
                            <div class="flex items-start space-x-4">
                                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-phone text-blue-600 text-lg"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-800">Phone Support</h4>
                                    <p class="text-gray-600">+256 782 971 912</p>
                                    <p class="text-sm text-gray-500">Mon-Fri: 8:00 AM - 6:00 PM EAT</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start space-x-4">
                                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-envelope text-green-600 text-lg"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-800">Email</h4>
                                    <p class="text-gray-600">support@bebamart.com</p>
                                    <p class="text-sm text-gray-500">Response within 24 hours</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start space-x-4">
                                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-comments text-purple-600 text-lg"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-800">Live Chat</h4>
                                    <p class="text-gray-600">Available on website</p>
                                    <p class="text-sm text-gray-500">24/7 automated assistance</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ Preview -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="text-xl font-bold text-gray-800 mb-4">Quick Questions</h3>
                        <div class="space-y-3">
                            <div class="border-l-4 border-blue-500 pl-4 py-1">
                                <h4 class="font-bold text-gray-700">How do I track my order?</h4>
                                <p class="text-sm text-gray-600">Check your order status in your dashboard</p>
                            </div>
                            <div class="border-l-4 border-green-500 pl-4 py-1">
                                <h4 class="font-bold text-gray-700">What is escrow protection?</h4>
                                <p class="text-sm text-gray-600">Your payment is held securely until delivery</p>
                            </div>
                            <div class="border-l-4 border-purple-500 pl-4 py-1">
                                <h4 class="font-bold text-gray-700">How do I become a vendor?</h4>
                                <p class="text-sm text-gray-600">Complete our vendor onboarding process</p>
                            </div>
                        </div>
                        <a href="{{ route('site.faq') }}" class="inline-block mt-4 text-blue-600 hover:text-blue-800 font-medium">
                            View all FAQs <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>

                    <!-- Social Media -->
<div class="bg-white rounded-xl shadow-sm p-6">
    <h3 class="text-xl font-bold text-gray-800 mb-4">Follow Us</h3>
    <div class="flex space-x-3">
        <a href="https://www.facebook.com/share/1AmT9d3Xji/?mibextid=wwXIfr" 
           target="_blank" 
           rel="noopener noreferrer" 
           class="w-10 h-10 bg-blue-600 text-white rounded-lg flex items-center justify-center hover:bg-blue-700 transition hover:scale-105">
            <i class="fab fa-facebook-f"></i>
        </a>
        <a href="https://x.com/BebamartGlobal" 
           target="_blank" 
           rel="noopener noreferrer" 
           class="w-10 h-10 bg-black text-white rounded-lg flex items-center justify-center hover:bg-gray-800 transition hover:scale-105">
            <!-- X Logo -->
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
            </svg>
        </a>
        <a href="http://instagram.com/bebamartglobal" 
           target="_blank" 
           rel="noopener noreferrer" 
           class="w-10 h-10 bg-gradient-to-r from-pink-500 to-purple-600 text-white rounded-lg flex items-center justify-center hover:opacity-90 transition hover:scale-105">
            <i class="fab fa-instagram"></i>
        </a>
        <a href="https://www.tiktok.com/@bebamart.global?lang=en" 
           target="_blank" 
           rel="noopener noreferrer" 
           class="w-10 h-10 bg-black text-white rounded-lg flex items-center justify-center hover:bg-gray-800 transition hover:scale-105">
            <i class="fab fa-tiktok"></i>
        </a>
        <a href="https://www.linkedin.com/company/beba-mart-global-limited" 
           target="_blank" 
           rel="noopener noreferrer" 
           class="w-10 h-10 bg-blue-700 text-white rounded-lg flex items-center justify-center hover:bg-blue-800 transition hover:scale-105">
            <i class="fab fa-linkedin-in"></i>
        </a>
        <a href="https://www.youtube.com/@bebamartglobal" 
           target="_blank" 
           rel="noopener noreferrer" 
           class="w-10 h-10 bg-red-600 text-white rounded-lg flex items-center justify-center hover:bg-red-700 transition hover:scale-105">
            <i class="fab fa-youtube"></i>
        </a>
    </div>
    <p class="text-sm text-gray-500 mt-4">Stay updated with our latest news and offers!</p>
</div>
                </div>
            </div>
        </div>
    </div>

    @include('partials.footer')
</body>
</html>