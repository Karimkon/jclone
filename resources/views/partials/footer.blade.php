<footer class="bg-ink-900 text-white pt-10 pb-6">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-2 md:grid-cols-5 gap-6 mb-8">
            <div class="col-span-2 md:col-span-1">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-8 h-8 bg-gradient-to-br from-brand-500 to-purple-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-store text-white text-sm"></i>
                    </div>
                    <span class="text-lg font-bold font-display">{{ config('app.name') }}</span>
                </div>
                <p class="text-ink-400 text-xs mb-4 leading-relaxed">Your trusted marketplace with escrow protection.</p>
               <div class="flex flex-wrap gap-2">
                <a href="#" class="w-8 h-8 bg-ink-800 rounded-lg flex items-center justify-center hover:bg-brand-600 transition text-sm">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <a href="#" class="w-8 h-8 bg-ink-800 rounded-lg flex items-center justify-center hover:bg-sky-500 transition text-sm">
                    <i class="fab fa-twitter"></i>
                </a>
                <a href="#" class="w-8 h-8 bg-ink-800 rounded-lg flex items-center justify-center hover:bg-pink-600 transition text-sm">
                    <i class="fab fa-instagram"></i>
                </a>
                <a href="#" class="w-8 h-8 bg-ink-800 rounded-lg flex items-center justify-center hover:bg-black transition text-sm">
                    <i class="fab fa-tiktok"></i>
                </a>
                <a href="#" class="w-8 h-8 bg-ink-800 rounded-lg flex items-center justify-center hover:bg-blue-700 transition text-sm">
                    <i class="fab fa-linkedin-in"></i>
                </a>
            </div>
            </div>
            
            <div>
                <h5 class="font-bold mb-3 text-sm">Company</h5>
                <ul class="space-y-2 text-xs">
                    <li><a href="{{ route('site.about') }}" class="text-ink-400 hover:text-white transition">About Us</a></li>
                    <li><a href="{{ route('site.howItWorks') }}" class="text-ink-400 hover:text-white transition">How It Works</a></li>
                    <li><a href="{{ route('site.vendorBenefits') }}" class="text-ink-400 hover:text-white transition">Vendor Benefits</a></li>
                    <li><a href="{{ route('site.contact') }}" class="text-ink-400 hover:text-white transition">Contact Us</a></li>
                </ul>
            </div>
            
            <div>
                <h5 class="font-bold mb-3 text-sm">Support</h5>
                <ul class="space-y-2 text-xs">
                    <li><a href="{{ route('site.faq') }}" class="text-ink-400 hover:text-white transition">FAQ</a></li>
                    <li><a href="#" class="text-ink-400 hover:text-white transition">Help Center</a></li>
                    <li><a href="#" class="text-ink-400 hover:text-white transition">Shipping Info</a></li>
                    <li><a href="#" class="text-ink-400 hover:text-white transition">Returns & Refunds</a></li>
                </ul>
            </div>
            
            <div>
                <h5 class="font-bold mb-3 text-sm">Legal</h5>
                <ul class="space-y-2 text-xs">
                    <li><a href="{{ route('site.terms') }}" class="text-ink-400 hover:text-white transition">Terms & Conditions</a></li>
                    <li><a href="{{ route('site.privacy') }}" class="text-ink-400 hover:text-white transition">Privacy Policy</a></li>
                    <li><a href="#" class="text-ink-400 hover:text-white transition">Cookie Policy</a></li>
                    <li><a href="#" class="text-ink-400 hover:text-white transition">Dispute Resolution</a></li>
                </ul>
            </div>
            
            <div>
                <h5 class="font-bold mb-3 text-sm">Newsletter</h5>
                <p class="text-ink-400 text-xs mb-2">Get exclusive deals</p>
                <div class="flex">
                    <input type="email" placeholder="Email" class="flex-1 px-3 py-2 bg-ink-800 border border-ink-700 rounded-l-lg text-xs focus:outline-none focus:border-brand-500 text-white placeholder-ink-500">
                    <button class="bg-brand-600 px-3 py-2 rounded-r-lg hover:bg-brand-700 transition"><i class="fas fa-paper-plane text-xs"></i></button>
                </div>
            </div>
        </div>
        
        <div class="border-t border-ink-800 pt-4">
            <div class="flex flex-col md:flex-row items-center justify-between gap-3">
                <p class="text-ink-500 text-xs">© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                <div class="flex items-center gap-2">
                    <span class="text-ink-500 text-xs">We accept:</span>
                    <div class="flex gap-1">
                        <div class="w-8 h-5 bg-ink-800 rounded flex items-center justify-center"><i class="fab fa-cc-visa text-ink-400 text-sm"></i></div>
                        <div class="w-8 h-5 bg-ink-800 rounded flex items-center justify-center"><i class="fab fa-cc-mastercard text-ink-400 text-sm"></i></div>
                        <div class="w-8 h-5 bg-ink-800 rounded flex items-center justify-center"><i class="fas fa-mobile-alt text-ink-400 text-xs"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>