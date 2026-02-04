<footer class="bg-gray-900 text-white pt-12 pb-8">
    <div class="container mx-auto px-4">
        <!-- Main Footer Content -->
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-8 mb-10">
            <!-- Brand Section -->
            <div class="col-span-2 md:col-span-4 lg:col-span-1">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-500/20">
                        <i class="fas fa-store text-white"></i>
                    </div>
                    <span class="text-xl font-bold">{{ config('app.name') }}</span>
                </div>
                <p class="text-gray-400 text-sm mb-5 leading-relaxed max-w-xs">
                    Your trusted marketplace with escrow protection. Shop safely with confidence.
                </p>
                <div class="flex flex-wrap gap-2">
                    <a href="#" class="w-9 h-9 bg-gray-800 rounded-lg flex items-center justify-center hover:bg-blue-600 transition-all duration-200 hover:scale-110">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="w-9 h-9 bg-gray-800 rounded-lg flex items-center justify-center hover:bg-sky-500 transition-all duration-200 hover:scale-110">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="w-9 h-9 bg-gray-800 rounded-lg flex items-center justify-center hover:bg-gradient-to-br hover:from-purple-600 hover:to-pink-500 transition-all duration-200 hover:scale-110">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="w-9 h-9 bg-gray-800 rounded-lg flex items-center justify-center hover:bg-black transition-all duration-200 hover:scale-110">
                        <i class="fab fa-tiktok"></i>
                    </a>
                    <a href="#" class="w-9 h-9 bg-gray-800 rounded-lg flex items-center justify-center hover:bg-green-500 transition-all duration-200 hover:scale-110">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                </div>
            </div>

            <!-- Company Links -->
            <div>
                <h5 class="font-semibold mb-4 text-sm uppercase tracking-wide text-gray-300">Company</h5>
                <ul class="space-y-3">
                    <li>
                        <a href="{{ route('site.about') }}" class="text-gray-400 hover:text-white transition text-sm flex items-center gap-2 group">
                            <i class="fas fa-chevron-right text-xs text-gray-600 group-hover:text-indigo-400 transition"></i>
                            About Us
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('site.howItWorks') }}" class="text-gray-400 hover:text-white transition text-sm flex items-center gap-2 group">
                            <i class="fas fa-chevron-right text-xs text-gray-600 group-hover:text-indigo-400 transition"></i>
                            How It Works
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('site.vendorBenefits') }}" class="text-gray-400 hover:text-white transition text-sm flex items-center gap-2 group">
                            <i class="fas fa-chevron-right text-xs text-gray-600 group-hover:text-indigo-400 transition"></i>
                            Vendor Benefits
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('site.contact') }}" class="text-gray-400 hover:text-white transition text-sm flex items-center gap-2 group">
                            <i class="fas fa-chevron-right text-xs text-gray-600 group-hover:text-indigo-400 transition"></i>
                            Contact Us
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Support Links -->
            <div>
                <h5 class="font-semibold mb-4 text-sm uppercase tracking-wide text-gray-300">Support</h5>
                <ul class="space-y-3">
                    <li>
                        <a href="{{ route('site.faq') }}" class="text-gray-400 hover:text-white transition text-sm flex items-center gap-2 group">
                            <i class="fas fa-chevron-right text-xs text-gray-600 group-hover:text-indigo-400 transition"></i>
                            FAQ
                        </a>
                    </li>
                    <li>
                        <a href="#" class="text-gray-400 hover:text-white transition text-sm flex items-center gap-2 group">
                            <i class="fas fa-chevron-right text-xs text-gray-600 group-hover:text-indigo-400 transition"></i>
                            Help Center
                        </a>
                    </li>
                    <li>
                        <a href="#" class="text-gray-400 hover:text-white transition text-sm flex items-center gap-2 group">
                            <i class="fas fa-chevron-right text-xs text-gray-600 group-hover:text-indigo-400 transition"></i>
                            Shipping Info
                        </a>
                    </li>
                    <li>
                        <a href="#" class="text-gray-400 hover:text-white transition text-sm flex items-center gap-2 group">
                            <i class="fas fa-chevron-right text-xs text-gray-600 group-hover:text-indigo-400 transition"></i>
                            Returns & Refunds
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Legal Links -->
            <div>
                <h5 class="font-semibold mb-4 text-sm uppercase tracking-wide text-gray-300">Legal</h5>
                <ul class="space-y-3">
                    <li>
                        <a href="{{ route('site.terms') }}" class="text-gray-400 hover:text-white transition text-sm flex items-center gap-2 group">
                            <i class="fas fa-chevron-right text-xs text-gray-600 group-hover:text-indigo-400 transition"></i>
                            Terms & Conditions
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('site.privacy') }}" class="text-gray-400 hover:text-white transition text-sm flex items-center gap-2 group">
                            <i class="fas fa-chevron-right text-xs text-gray-600 group-hover:text-indigo-400 transition"></i>
                            Privacy Policy
                        </a>
                    </li>
                    <li>
                        <a href="#" class="text-gray-400 hover:text-white transition text-sm flex items-center gap-2 group">
                            <i class="fas fa-chevron-right text-xs text-gray-600 group-hover:text-indigo-400 transition"></i>
                            Cookie Policy
                        </a>
                    </li>
                    <li>
                        <a href="#" class="text-gray-400 hover:text-white transition text-sm flex items-center gap-2 group">
                            <i class="fas fa-chevron-right text-xs text-gray-600 group-hover:text-indigo-400 transition"></i>
                            Dispute Resolution
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Newsletter Section -->
            <div class="col-span-2 md:col-span-2 lg:col-span-1">
                <h5 class="font-semibold mb-4 text-sm uppercase tracking-wide text-gray-300">Newsletter</h5>
                <p class="text-gray-400 text-sm mb-4">Subscribe for exclusive deals and updates.</p>
                <form class="space-y-3">
                    <div class="relative">
                        <input type="email" placeholder="Enter your email"
                               class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-sm focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 text-white placeholder-gray-500 transition">
                        <button type="submit" class="absolute right-1.5 top-1/2 -translate-y-1/2 bg-indigo-600 hover:bg-indigo-700 px-4 py-1.5 rounded-lg transition">
                            <i class="fas fa-paper-plane text-sm"></i>
                        </button>
                    </div>
                </form>

                <!-- Contact Info -->
                <div class="mt-6 space-y-2">
                    <a href="tel:+256700000000" class="flex items-center gap-2 text-gray-400 hover:text-white text-sm transition">
                        <i class="fas fa-phone text-indigo-400"></i>
                        +256 700 000 000
                    </a>
                    <a href="mailto:support@bebamart.com" class="flex items-center gap-2 text-gray-400 hover:text-white text-sm transition">
                        <i class="fas fa-envelope text-indigo-400"></i>
                        support@bebamart.com
                    </a>
                </div>
            </div>
        </div>

        <!-- Bottom Bar -->
        <div class="border-t border-gray-800 pt-6">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <p class="text-gray-500 text-sm text-center md:text-left">
                    © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                </p>

                <!-- Payment Methods -->
                <div class="flex items-center gap-3">
                    <span class="text-gray-500 text-sm">We accept:</span>
                    <div class="flex gap-2">
                        <div class="w-10 h-6 bg-gray-800 rounded flex items-center justify-center" title="Visa">
                            <i class="fab fa-cc-visa text-gray-400"></i>
                        </div>
                        <div class="w-10 h-6 bg-gray-800 rounded flex items-center justify-center" title="Mastercard">
                            <i class="fab fa-cc-mastercard text-gray-400"></i>
                        </div>
                        <div class="w-10 h-6 bg-gray-800 rounded flex items-center justify-center" title="Mobile Money">
                            <i class="fas fa-mobile-alt text-gray-400 text-sm"></i>
                        </div>
                        <div class="w-10 h-6 bg-gray-800 rounded flex items-center justify-center" title="Airtel Money">
                            <span class="text-gray-400 text-xs font-bold">AM</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Trust Badges (Mobile) -->
        <div class="mt-6 pt-6 border-t border-gray-800 md:hidden">
            <div class="flex items-center justify-center gap-6 text-gray-500 text-xs">
                <div class="flex items-center gap-2">
                    <i class="fas fa-shield-alt text-green-500"></i>
                    <span>Secure Payments</span>
                </div>
                <div class="flex items-center gap-2">
                    <i class="fas fa-lock text-indigo-400"></i>
                    <span>Escrow Protected</span>
                </div>
            </div>
        </div>
    </div>
</footer>
