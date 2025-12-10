<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ - {{ config('app.name') }}</title>
    @include('partials.head')
    <style>
        .faq-item {
            border-bottom: 1px solid #e5e7eb;
        }
        .faq-question {
            cursor: pointer;
            padding: 1.25rem 0;
            position: relative;
        }
        .faq-question::after {
            content: '+';
            position: absolute;
            right: 0;
            font-size: 1.5rem;
            color: #6b7280;
            transition: transform 0.3s ease;
        }
        .faq-question.active::after {
            content: '-';
            transform: rotate(180deg);
        }
        .faq-answer {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }
        .faq-answer.open {
            max-height: 500px;
        }
    </style>
</head>
<body class="bg-gray-50">
    @include('partials.header')
    
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-10">
                <h1 class="text-4xl font-bold text-gray-800 mb-3">Frequently Asked Questions</h1>
                <p class="text-gray-600 max-w-2xl mx-auto">
                    Find quick answers to common questions about buying, selling, and using our platform.
                </p>
            </div>

            <!-- Search Bar -->
            <div class="mb-8">
                <div class="relative">
                    <input type="text" id="faqSearch" 
                        class="w-full px-5 py-4 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg"
                        placeholder="Search for questions...">
                    <i class="fas fa-search absolute right-5 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                </div>
            </div>

            <!-- FAQ Categories -->
            <div class="flex flex-wrap gap-3 mb-8">
                <button class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium" data-category="all">All Questions</button>
                <button class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300 transition" data-category="buying">Buying</button>
                <button class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300 transition" data-category="selling">Selling</button>
                <button class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300 transition" data-category="payments">Payments</button>
                <button class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300 transition" data-category="shipping">Shipping</button>
                <button class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300 transition" data-category="account">Account</button>
            </div>

            <!-- FAQ List -->
            <div class="space-y-2">
                @foreach($faqs as $index => $faq)
                <div class="faq-item bg-white rounded-lg shadow-sm">
                    <div class="faq-question px-6" onclick="toggleFAQ({{ $index }})">
                        <h3 class="text-lg font-semibold text-gray-800">{{ $faq['question'] }}</h3>
                    </div>
                    <div class="faq-answer px-6">
                        <div class="pb-6 pt-2">
                            <p class="text-gray-600">{{ $faq['answer'] }}</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Additional Support -->
            <div class="mt-12 bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl p-8 text-white text-center">
                <h2 class="text-2xl font-bold mb-3">Still Need Help?</h2>
                <p class="mb-6 max-w-2xl mx-auto opacity-90">
                    Can't find what you're looking for? Our support team is ready to assist you.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('site.contact') }}" 
                       class="px-6 py-3 bg-white text-blue-600 font-bold rounded-lg hover:bg-gray-100 transition">
                        Contact Support
                    </a>
                    <a href="{{ route('site.howItWorks') }}" 
                       class="px-6 py-3 border-2 border-white text-white font-bold rounded-lg hover:bg-white/10 transition">
                        How It Works
                    </a>
                </div>
            </div>
        </div>
    </div>

    @include('partials.footer')
    
    <script>
        // FAQ Toggle
        function toggleFAQ(index) {
            const question = document.querySelectorAll('.faq-question')[index];
            const answer = document.querySelectorAll('.faq-answer')[index];
            
            question.classList.toggle('active');
            answer.classList.toggle('open');
        }
        
        // FAQ Search
        document.getElementById('faqSearch').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const questions = document.querySelectorAll('.faq-question h3');
            const answers = document.querySelectorAll('.faq-answer p');
            
            questions.forEach((question, index) => {
                const questionText = question.textContent.toLowerCase();
                const answerText = answers[index].textContent.toLowerCase();
                const faqItem = question.closest('.faq-item');
                
                if (questionText.includes(searchTerm) || answerText.includes(searchTerm)) {
                    faqItem.style.display = '';
                } else {
                    faqItem.style.display = 'none';
                }
            });
        });
        
        // FAQ Category Filter
        document.querySelectorAll('[data-category]').forEach(button => {
            button.addEventListener('click', function() {
                const category = this.dataset.category;
                
                // Update active button
                document.querySelectorAll('[data-category]').forEach(btn => {
                    btn.classList.remove('bg-blue-600', 'text-white');
                    btn.classList.add('bg-gray-200', 'text-gray-700', 'hover:bg-gray-300');
                });
                
                this.classList.remove('bg-gray-200', 'text-gray-700', 'hover:bg-gray-300');
                this.classList.add('bg-blue-600', 'text-white');
                
                // Filter FAQ items (you would need to add data-category to your FAQ items)
                // For now, just show all
                if (category === 'all') {
                    document.querySelectorAll('.faq-item').forEach(item => {
                        item.style.display = '';
                    });
                }
            });
        });
    </script>
</body>
</html>