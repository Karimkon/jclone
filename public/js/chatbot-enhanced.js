// public/js/chatbot-pro.js - COMPLETE FILE
class ProfessionalChatbot {
    constructor() {
        this.isOpen = false;
        this.conversationContext = {
            lastQuestion: null,
            awaitingResponse: false,
            pendingAction: null
        };
        this.chatHistory = [];
        this.initialize();
    }

    initialize() {
        this.createChatbotHTML();
        this.loadKnowledgeBase();
        this.attachEventListeners();
    }

    createChatbotHTML() {
        const chatbotHTML = `
            <!-- Chatbot Widget -->
            <div id="chatbotWidget" class="fixed bottom-6 right-6 z-50">
                <!-- Main Chat Button -->
                <button id="chatbotToggle" class="w-14 h-14 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-full shadow-xl flex items-center justify-center hover:shadow-2xl transition-all duration-300 hover:scale-110 group">
                    <i class="fas fa-comment-dots text-xl"></i>
                    <span class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white text-xs rounded-full flex items-center justify-center animate-pulse hidden" id="notificationBadge">!</span>
                </button>
                
                <!-- Chat Window -->
                <div id="chatWindow" class="hidden absolute bottom-20 right-0 w-96 bg-white rounded-2xl shadow-2xl border border-ink-100">
                    <!-- Chat Header -->
                    <div class="bg-gradient-to-r from-brand-600 via-purple-600 to-indigo-700 text-white p-4 rounded-t-2xl flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center backdrop-blur-sm">
                                <i class="fas fa-robot text-lg"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-sm">${window.marketplaceName || 'Marketplace'} AI Assistant</h3>
                                <div class="flex items-center gap-1">
                                    <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
                                    <p class="text-xs opacity-90">Ready to help</p>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button id="minimizeChat" class="w-8 h-8 bg-white/10 rounded-full flex items-center justify-center hover:bg-white/20 transition">
                                <i class="fas fa-minus text-xs"></i>
                            </button>
                            <button id="closeChat" class="w-8 h-8 bg-white/10 rounded-full flex items-center justify-center hover:bg-white/20 transition">
                                <i class="fas fa-times text-sm"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Chat Messages -->
                    <div id="chatMessages" class="h-96 p-4 overflow-y-auto bg-gradient-to-b from-ink-50 to-white">
                        <!-- Initial Bot Message -->
                        <div class="mb-4 animate-slide-up">
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 bg-gradient-to-r from-brand-500 to-purple-500 rounded-full flex items-center justify-center flex-shrink-0 shadow-md">
                                    <i class="fas fa-robot text-white text-sm"></i>
                                </div>
                                <div class="bg-white px-4 py-3 rounded-2xl shadow-sm max-w-[85%] border border-ink-100">
                                    <p class="text-sm text-ink-800">Hello! I'm your ${window.marketplaceName || 'marketplace'} AI assistant. 😊 I can help you with:</p>
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        <span class="text-xs bg-brand-50 text-brand-600 px-2 py-1 rounded-full">Buying Guide</span>
                                        <span class="text-xs bg-emerald-50 text-emerald-600 px-2 py-1 rounded-full">Escrow Info</span>
                                        <span class="text-xs bg-blue-50 text-blue-600 px-2 py-1 rounded-full">Shipping</span>
                                        <span class="text-xs bg-purple-50 text-purple-600 px-2 py-1 rounded-full">Vendor Help</span>
                                    </div>
                                    <p class="text-xs text-ink-500 mt-2">Just now</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Quick Questions -->
                        <div class="mb-4">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="w-1.5 h-4 bg-brand-500 rounded-full"></div>
                                <p class="text-xs font-medium text-ink-600">Common questions:</p>
                            </div>
                            <div class="grid grid-cols-2 gap-2" id="quickQuestions">
                                <!-- Quick questions will be populated by JavaScript -->
                            </div>
                        </div>
                    </div>
                    
                    <!-- Chat Input Area -->
                    <div class="p-4 border-t border-ink-100 bg-white rounded-b-2xl">
                        <!-- Typing Indicator -->
                        <div id="typingIndicator" class="hidden mb-3">
                            <div class="flex items-center gap-2 text-ink-500 text-sm">
                                <div class="flex gap-1">
                                    <div class="w-2 h-2 bg-brand-400 rounded-full animate-bounce"></div>
                                    <div class="w-2 h-2 bg-brand-400 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                                    <div class="w-2 h-2 bg-brand-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                                </div>
                                <span>AI is thinking...</span>
                            </div>
                        </div>
                        
                        <!-- Input Area -->
                        <div class="flex gap-2">
                            <div class="flex-1 relative">
                                <input type="text" 
                                       id="chatInput" 
                                       placeholder="Ask me anything..."
                                       class="w-full pl-4 pr-10 py-3 bg-ink-50 border border-ink-200 rounded-xl focus:border-brand-500 focus:bg-white focus:outline-none text-sm focus:ring-2 focus:ring-brand-200 transition-all">
                                <button id="voiceInput" class="absolute right-2 top-1/2 -translate-y-1/2 text-ink-400 hover:text-brand-500 transition">
                                    <i class="fas fa-microphone text-sm"></i>
                                </button>
                            </div>
                            <button id="sendMessage" class="px-4 py-3 bg-gradient-to-r from-brand-500 to-purple-500 text-white rounded-xl hover:shadow-lg hover:scale-105 transition-all duration-200 active:scale-95">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                        
                        <!-- Quick Actions -->
                        <div class="mt-3 pt-3 border-t border-ink-100">
                            <div class="flex justify-between">
                                <button id="clearChat" class="text-xs text-ink-500 hover:text-ink-700 transition flex items-center gap-1">
                                    <i class="fas fa-eraser text-xs"></i> Clear
                                </button>
                                <a href="https://wa.me/256782971912?text=Hello%20${encodeURIComponent(window.marketplaceName || 'Marketplace')}%20Support" 
                                   target="_blank"
                                   class="text-xs bg-gradient-to-r from-green-500 to-emerald-600 text-white px-3 py-1.5 rounded-lg hover:shadow-md transition flex items-center gap-2">
                                    <i class="fab fa-whatsapp"></i>
                                    Human Support
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FAQ Modal -->
            <div id="faqModal" class="hidden fixed inset-0 z-[60] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-ink-900/80 backdrop-blur-sm" onclick="window.chatbotPro.closeFaqModal()"></div>
                <div class="bg-white rounded-2xl max-w-2xl w-full p-6 relative z-10 shadow-2xl animate-slide-up">
                    <button onclick="window.chatbotPro.closeFaqModal()" class="absolute top-4 right-4 w-9 h-9 bg-ink-100 rounded-full flex items-center justify-center text-ink-400 hover:text-ink-600 hover:bg-ink-200 transition">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                    <div id="faqContent" class="max-h-[70vh] overflow-y-auto pr-2">
                        <!-- FAQ content will be loaded here -->
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', chatbotHTML);
    }

    loadKnowledgeBase() {
        this.knowledgeBase = {
            'offices': {
                title: 'Our Offices & Locations',
                content: `
                    <div class="space-y-6">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-xl flex items-center justify-center">
                                <i class="fas fa-building text-white text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-ink-800">Our Offices & Locations</h3>
                                <p class="text-ink-500">Visit us or get in touch</p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-gradient-to-br from-blue-50 to-white p-5 rounded-xl border border-blue-100">
                                <h4 class="font-bold text-ink-800 mb-2 flex items-center gap-2">
                                    <i class="fas fa-map-marker-alt text-blue-500"></i>
                                    Headquarters
                                </h4>
                                <p class="text-sm text-ink-600 mb-1"><strong>Address:</strong> Plot 123, Kisaasi, Kampala</p>
                                <p class="text-sm text-ink-600 mb-1"><strong>Phone:</strong> +256 707 208 954</p>
                                <p class="text-sm text-ink-600 mb-1"><strong>Email:</strong> info@${window.marketplaceDomain || 'bebamart.com'}</p>
                                <p class="text-sm text-ink-500 mt-2"><i class="fas fa-clock mr-1"></i> Mon-Fri: 8AM-6PM, Sat: 9AM-2PM</p>
                            </div>
                            
                            <div class="bg-gradient-to-br from-emerald-50 to-white p-5 rounded-xl border border-emerald-100">
                                <h4 class="font-bold text-ink-800 mb-2 flex items-center gap-2">
                                    <i class="fas fa-store text-emerald-500"></i>
                                    Regional Offices
                                </h4>
                                <div class="space-y-2">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-ink-600">Entebbe Branch</span>
                                        <span class="text-xs bg-emerald-100 text-emerald-600 px-2 py-1 rounded-full">Airport Road</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-ink-600">Jinja Branch</span>
                                        <span class="text-xs bg-emerald-100 text-emerald-600 px-2 py-1 rounded-full">Main Street</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-ink-600">Mbarara Branch</span>
                                        <span class="text-xs bg-emerald-100 text-emerald-600 px-2 py-1 rounded-full">Mbarara Town</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-ink-600">Gulu Branch</span>
                                        <span class="text-xs bg-emerald-100 text-emerald-600 px-2 py-1 rounded-full">City Center</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-gradient-to-r from-ink-50 to-white p-4 rounded-xl border border-ink-200">
                            <h4 class="font-medium text-ink-700 mb-2 flex items-center gap-2">
                                <i class="fas fa-headset text-brand-500"></i>
                                Contact Options
                            </h4>
                            <div class="grid grid-cols-2 gap-3">
                                <a href="tel:+256707208954" class="flex items-center gap-2 p-3 bg-white border border-ink-200 rounded-lg hover:border-brand-300 hover:bg-brand-50 transition">
                                    <i class="fas fa-phone text-brand-500"></i>
                                    <span class="text-sm font-medium">Call Now</span>
                                </a>
                                <a href="https://wa.me/256782971912" target="_blank" class="flex items-center gap-2 p-3 bg-white border border-ink-200 rounded-lg hover:border-green-300 hover:bg-green-50 transition">
                                    <i class="fab fa-whatsapp text-green-500"></i>
                                    <span class="text-sm font-medium">WhatsApp</span>
                                </a>
                            </div>
                        </div>
                    </div>
                `,
                quickResponses: [
                    "Would you like our detailed office locations and contact information?",
                    "I can show you all our office locations. Interested?",
                    "Ready to see our contact details and office addresses?"
                ]
            },

            'contact': {
                title: 'Contact & Support Information',
                content: `
                    <div class="space-y-6">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-pink-500 rounded-xl flex items-center justify-center">
                                <i class="fas fa-headset text-white text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-ink-800">Contact & Support</h3>
                                <p class="text-ink-500">Multiple ways to reach us</p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="p-4 bg-white border border-ink-200 rounded-xl hover:border-purple-300 hover:shadow-md transition">
                                <div class="w-10 h-10 bg-purple-100 text-purple-600 rounded-lg flex items-center justify-center mb-3">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <h4 class="font-bold text-ink-800 mb-1">Phone Support</h4>
                                <p class="text-sm text-ink-600 mb-2">Call us directly</p>
                                <a href="tel:+256707208954" class="text-brand-600 font-medium text-sm hover:underline">+256 707 208 954</a>
                            </div>
                            
                            <div class="p-4 bg-white border border-ink-200 rounded-xl hover:border-green-300 hover:shadow-md transition">
                                <div class="w-10 h-10 bg-green-100 text-green-600 rounded-lg flex items-center justify-center mb-3">
                                    <i class="fab fa-whatsapp"></i>
                                </div>
                                <h4 class="font-bold text-ink-800 mb-1">WhatsApp</h4>
                                <p class="text-sm text-ink-600 mb-2">Instant messaging</p>
                                <a href="https://wa.me/256782971912" target="_blank" class="text-green-600 font-medium text-sm hover:underline">Chat Now</a>
                            </div>
                            
                            <div class="p-4 bg-white border border-ink-200 rounded-xl hover:border-blue-300 hover:shadow-md transition">
                                <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center mb-3">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <h4 class="font-bold text-ink-800 mb-1">Email</h4>
                                <p class="text-sm text-ink-600 mb-2">24-hour response</p>
                                <a href="mailto:support@${window.marketplaceDomain || 'bebamart.com'}" class="text-blue-600 font-medium text-sm hover:underline">support@${window.marketplaceDomain || 'bebamart.com'}</a>
                            </div>
                        </div>
                        
                        <div class="bg-gradient-to-r from-amber-50 to-white p-5 rounded-xl border border-amber-200">
                            <h4 class="font-bold text-ink-800 mb-2 flex items-center gap-2">
                                <i class="fas fa-clock text-amber-500"></i>
                                Support Hours
                            </h4>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <p class="text-sm font-medium text-ink-700">Monday - Friday</p>
                                    <p class="text-sm text-ink-600">8:00 AM - 6:00 PM</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-ink-700">Saturday</p>
                                    <p class="text-sm text-ink-600">9:00 AM - 2:00 PM</p>
                                </div>
                                <div class="col-span-2">
                                    <p class="text-sm font-medium text-ink-700">24/7 Emergency</p>
                                    <p class="text-sm text-ink-600">WhatsApp & Email (priority response)</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex gap-3">
                            <a href="https://wa.me/256782971912" target="_blank" class="flex-1 bg-gradient-to-r from-green-500 to-emerald-600 text-white py-3 rounded-xl font-medium text-center hover:shadow-lg transition flex items-center justify-center gap-2">
                                <i class="fab fa-whatsapp"></i> WhatsApp Now
                            </a>
                            <a href="tel:+256707208954" class="flex-1 bg-white border border-brand-500 text-brand-600 py-3 rounded-xl font-medium text-center hover:bg-brand-50 transition flex items-center justify-center gap-2">
                                <i class="fas fa-phone"></i> Call Now
                            </a>
                        </div>
                    </div>
                `,
                quickResponses: [
                    "Would you like our detailed contact information and support hours?",
                    "I can show you all our contact options. Ready to see them?",
                    "Interested in our contact details and support availability?"
                ]
            },

            // Add more knowledge base entries as needed
            'how to buy': {
                title: 'How to Buy Products',
                content: `
                    <div class="space-y-6">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-gradient-to-r from-brand-500 to-purple-500 rounded-xl flex items-center justify-center">
                                <i class="fas fa-shopping-cart text-white text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-ink-800">How to Buy Products</h3>
                                <p class="text-ink-500">Simple steps to shop with confidence</p>
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 bg-brand-100 text-brand-600 rounded-full flex items-center justify-center flex-shrink-0">
                                    <span class="font-bold">1</span>
                                </div>
                                <div>
                                    <h4 class="font-medium text-ink-700">Browse & Search</h4>
                                    <p class="text-sm text-ink-600">Find products by category or use the search bar</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 bg-brand-100 text-brand-600 rounded-full flex items-center justify-center flex-shrink-0">
                                    <span class="font-bold">2</span>
                                </div>
                                <div>
                                    <h4 class="font-medium text-ink-700">Add to Cart</h4>
                                    <p class="text-sm text-ink-600">Select quantity and add to shopping cart</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 bg-brand-100 text-brand-600 rounded-full flex items-center justify-center flex-shrink-0">
                                    <span class="font-bold">3</span>
                                </div>
                                <div>
                                    <h4 class="font-medium text-ink-700">Checkout Securely</h4>
                                    <p class="text-sm text-ink-600">Proceed to checkout with escrow protection</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 bg-brand-100 text-brand-600 rounded-full flex items-center justify-center flex-shrink-0">
                                    <span class="font-bold">4</span>
                                </div>
                                <div>
                                    <h4 class="font-medium text-ink-700">Confirm Delivery</h4>
                                    <p class="text-sm text-ink-600">Release payment after receiving your items</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-gradient-to-r from-emerald-50 to-white p-4 rounded-xl border border-emerald-200">
                            <h4 class="font-medium text-emerald-700 mb-2 flex items-center gap-2">
                                <i class="fas fa-shield-alt"></i>
                                Escrow Protection
                            </h4>
                            <p class="text-sm text-emerald-600">Your payment is held securely until you confirm delivery. This protects both buyers and sellers!</p>
                        </div>
                    </div>
                `,
                quickResponses: [
                    "Would you like step-by-step instructions for buying products?",
                    "I can guide you through the buying process. Interested?",
                    "Ready to learn how to shop securely on our platform?"
                ]
            }
        };

        // Populate quick questions
        const quickQuestionsContainer = document.getElementById('quickQuestions');
        const quickQuestions = [
            { text: '📍 Offices', key: 'offices', icon: 'map-marker-alt', color: 'blue' },
            { text: '📞 Contact', key: 'contact', icon: 'phone', color: 'purple' },
            { text: '🛒 How to Buy', key: 'how to buy', icon: 'shopping-cart', color: 'brand' },
            { text: '🛡️ Escrow', key: 'escrow protection', icon: 'shield-alt', color: 'emerald' },
            { text: '🚚 Shipping', key: 'shipping info', icon: 'truck', color: 'cyan' },
            { text: '🏪 Vendor Signup', key: 'vendor registration', icon: 'store', color: 'amber' },
            { text: '💳 Payments', key: 'payment methods', icon: 'credit-card', color: 'pink' },
            { text: '↩️ Returns', key: 'returns refunds', icon: 'undo', color: 'rose' }
        ];

        quickQuestions.forEach(q => {
            const button = document.createElement('button');
            button.className = `quick-question text-xs bg-${q.color}-50 text-${q.color}-600 px-3 py-2.5 rounded-xl hover:bg-${q.color}-100 transition-all text-left group`;
            button.innerHTML = `
                <div class="flex items-center gap-2">
                    <i class="fas fa-${q.icon} text-${q.color}-500 group-hover:scale-110 transition-transform"></i>
                    <span>${q.text}</span>
                </div>
            `;
            button.addEventListener('click', () => this.handleQuickQuestion(q.text, q.key));
            quickQuestionsContainer.appendChild(button);
        });
    }

    attachEventListeners() {
        // Toggle chat window
        document.getElementById('chatbotToggle').addEventListener('click', () => this.toggleChat());
        document.getElementById('closeChat').addEventListener('click', () => this.closeChat());
        document.getElementById('minimizeChat').addEventListener('click', () => this.minimizeChat());

        // Send message
        document.getElementById('sendMessage').addEventListener('click', () => this.sendUserMessage());
        document.getElementById('chatInput').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') this.sendUserMessage();
        });

        // Clear chat
        document.getElementById('clearChat').addEventListener('click', () => this.clearChat());

        // Voice input
        document.getElementById('voiceInput').addEventListener('click', () => this.toggleVoiceInput());

        // Help link in header
        const helpLink = document.querySelector('#helpLink');
        if (helpLink) {
            helpLink.addEventListener('click', (e) => {
                e.preventDefault();
                this.openChat();
            });
        }
    }

    // Core chatbot methods
    toggleChat() {
        const chatWindow = document.getElementById('chatWindow');
        this.isOpen = !this.isOpen;
        
        if (this.isOpen) {
            chatWindow.classList.remove('hidden');
            chatWindow.classList.add('animate-slide-up');
            document.getElementById('chatInput').focus();
            document.getElementById('notificationBadge').classList.add('hidden');
        } else {
            chatWindow.classList.add('hidden');
        }
    }

    openChat() {
        const chatWindow = document.getElementById('chatWindow');
        this.isOpen = true;
        chatWindow.classList.remove('hidden');
        chatWindow.classList.add('animate-slide-up');
        document.getElementById('chatInput').focus();
    }

    closeChat() {
        const chatWindow = document.getElementById('chatWindow');
        this.isOpen = false;
        chatWindow.classList.add('hidden');
    }

    minimizeChat() {
        const chatWindow = document.getElementById('chatWindow');
        chatWindow.classList.add('animate-slide-down');
        setTimeout(() => {
            chatWindow.classList.add('hidden');
            chatWindow.classList.remove('animate-slide-down');
            this.isOpen = false;
        }, 300);
    }

    showTypingIndicator() {
        document.getElementById('typingIndicator').classList.remove('hidden');
    }

    hideTypingIndicator() {
        document.getElementById('typingIndicator').classList.add('hidden');
    }

    async sendUserMessage() {
        const chatInput = document.getElementById('chatInput');
        const message = chatInput.value.trim();
        
        if (!message) return;
        
        // Add user message
        this.addMessageToChat(message, 'user');
        chatInput.value = '';
        
        // Show typing indicator
        this.showTypingIndicator();
        
        // Get AI response with delay for realism
        setTimeout(() => {
            const response = this.getAIResponse(message);
            this.addMessageToChat(response.text, 'bot');
            this.hideTypingIndicator();
            
            // Handle follow-up if needed
            if (response.followUp) {
                this.conversationContext.awaitingResponse = true;
                this.conversationContext.pendingAction = response.action;
            }
        }, 800 + Math.random() * 500);
    }

    addMessageToChat(message, sender) {
        const chatMessages = document.getElementById('chatMessages');
        const messageDiv = document.createElement('div');
        messageDiv.className = `mb-4 animate-slide-up ${sender === 'user' ? 'ml-auto max-w-[85%]' : ''}`;
        
        const timestamp = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        
        if (sender === 'user') {
            messageDiv.innerHTML = `
                <div class="flex items-end gap-2 justify-end">
                    <div class="flex flex-col items-end">
                        <div class="bg-gradient-to-r from-brand-500 to-purple-500 text-white px-4 py-3 rounded-2xl rounded-br-none shadow-sm max-w-full">
                            <p class="text-sm">${this.escapeHtml(message)}</p>
                        </div>
                        <p class="text-xs text-ink-400 mt-1">${timestamp}</p>
                    </div>
                    <div class="w-8 h-8 bg-gradient-to-r from-brand-400 to-purple-400 rounded-full flex items-center justify-center flex-shrink-0 shadow">
                        <i class="fas fa-user text-white text-xs"></i>
                    </div>
                </div>
            `;
        } else {
            messageDiv.innerHTML = `
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 bg-gradient-to-r from-brand-500 to-purple-500 rounded-full flex items-center justify-center flex-shrink-0 shadow">
                        <i class="fas fa-robot text-white text-xs"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="bg-white px-4 py-3 rounded-2xl rounded-tl-none shadow-sm border border-ink-100 max-w-full">
                            <p class="text-sm text-ink-800">${this.escapeHtml(message)}</p>
                            ${this.conversationContext.awaitingResponse ? `
                                <div class="mt-3 flex gap-2">
                                    <button class="quick-response-yes text-xs bg-emerald-50 text-emerald-600 px-3 py-1.5 rounded-lg hover:bg-emerald-100 transition">
                                        <i class="fas fa-check mr-1"></i> Yes, please
                                    </button>
                                    <button class="quick-response-no text-xs bg-ink-100 text-ink-600 px-3 py-1.5 rounded-lg hover:bg-ink-200 transition">
                                        <i class="fas fa-times mr-1"></i> No, thanks
                                    </button>
                                </div>
                            ` : ''}
                        </div>
                        <p class="text-xs text-ink-400 mt-1">${timestamp}</p>
                    </div>
                </div>
            `;
            
            // Add event listeners for quick responses
            setTimeout(() => {
                const yesBtn = messageDiv.querySelector('.quick-response-yes');
                const noBtn = messageDiv.querySelector('.quick-response-no');
                
                if (yesBtn) {
                    yesBtn.addEventListener('click', () => this.handleQuickResponse('yes'));
                }
                if (noBtn) {
                    noBtn.addEventListener('click', () => this.handleQuickResponse('no'));
                }
            }, 100);
        }
        
        chatMessages.appendChild(messageDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
        
        // Save to history
        this.chatHistory.push({ sender, message, timestamp: new Date() });
    }

    getAIResponse(userMessage) {
        const message = userMessage.toLowerCase().trim();
        
        // Check if this is a response to a previous question
        if (this.conversationContext.awaitingResponse) {
            return this.handleFollowUpResponse(message);
        }
        
        // Handle common greetings
        if (this.isGreeting(message)) {
            return {
                text: `Hello! I'm your ${window.marketplaceName || 'marketplace'} AI assistant. 😊 How can I help you today?`,
                followUp: false
            };
        }
        
        // Handle "yes" responses (context aware)
        if (message.includes('yes') || message === 'y' || message === 'yeah' || message === 'yep') {
            if (this.conversationContext.lastQuestion) {
                const lastQ = this.conversationContext.lastQuestion.toLowerCase();
                if (lastQ.includes('office') || lastQ.includes('location')) {
                    setTimeout(() => this.showFaqAnswer('offices'), 300);
                    return {
                        text: "Perfect! Here are our office locations and contact details.",
                        followUp: false
                    };
                } else if (lastQ.includes('contact') || lastQ.includes('support')) {
                    setTimeout(() => this.showFaqAnswer('contact'), 300);
                    return {
                        text: "Great! Here's all our contact information and support options.",
                        followUp: false
                    };
                } else if (lastQ.includes('buy') || lastQ.includes('purchase')) {
                    setTimeout(() => this.showFaqAnswer('how to buy'), 300);
                    return {
                        text: "Excellent! Here's a step-by-step guide to buying products.",
                        followUp: false
                    };
                }
            }
        }
        
        // Handle specific queries
        const queries = [
            {
                keywords: ['office', 'location', 'address', 'where', 'headquarters', 'branch'],
                response: "We have offices in Kisaasi and several regional branches across Uganda. Would you like to see our detailed office locations and contact information?",
                action: 'show_offices',
                followUp: true
            },
            {
                keywords: ['contact', 'support', 'help', 'phone', 'email', 'whatsapp', 'call'],
                response: "You can contact our support team via phone, email, or WhatsApp. We're available 24/7 for urgent issues. Would you like our contact details?",
                action: 'show_contact',
                followUp: true
            },
            {
                keywords: ['buy', 'purchase', 'shop', 'how to buy', 'shopping'],
                response: "I can help you with the buying process! Our escrow system ensures your payment is protected until you receive your items. Would you like step-by-step instructions?",
                action: 'show_buying_guide',
                followUp: true
            },
            {
                keywords: ['sell', 'vendor', 'seller', 'become vendor'],
                response: "Becoming a vendor is easy! You can register online and start selling immediately after verification. Would you like to see the vendor registration process?",
                action: 'show_vendor_info',
                followUp: true
            },
            {
                keywords: ['ship', 'delivery', 'deliver', 'shipping'],
                response: "We offer both local and international shipping with various delivery options. Local delivery takes 1-3 days, while international takes 7-14 days. Would you like more details?",
                action: 'show_shipping_info',
                followUp: true
            },
            {
                keywords: ['escrow', 'secure', 'protection', 'safe'],
                response: "Our escrow system holds your payment securely until you confirm delivery. This protects both buyers and sellers. Would you like to know how it works?",
                action: 'show_escrow_info',
                followUp: true
            },
            {
                keywords: ['payment', 'pay', 'money', 'credit card', 'mobile money'],
                response: "We accept Visa/Mastercard, Mobile Money, Bank Transfer, and Digital Wallets. All payments are protected by our escrow system. Would you like to see all payment methods?",
                action: 'show_payment_methods',
                followUp: true
            },
            {
                keywords: ['return', 'refund', 'exchange', 'warranty'],
                response: "We have a 7-day return policy for unused items in original packaging. Refunds are processed within 14 business days. Would you like to see the full returns policy?",
                action: 'show_returns_policy',
                followUp: true
            }
        ];
        
        for (const query of queries) {
            if (query.keywords.some(keyword => message.includes(keyword))) {
                this.conversationContext.lastQuestion = userMessage;
                return {
                    text: query.response,
                    followUp: query.followUp,
                    action: query.action
                };
            }
        }
        
        // Handle thank you
        if (message.includes('thank') || message.includes('thanks')) {
            return {
                text: "You're welcome! 😊 Is there anything else I can help you with?",
                followUp: false
            };
        }
        
        // Default intelligent response
        const defaultResponses = [
            "I'm here to help! Could you tell me what you need assistance with?",
            "I understand you're looking for information. Could you be more specific about what you need?",
            "I'd love to help! What specific information are you looking for?",
            "I'm your AI assistant ready to help! What can I assist you with today?"
        ];
        
        return {
            text: defaultResponses[Math.floor(Math.random() * defaultResponses.length)],
            followUp: false
        };
    }

    handleFollowUpResponse(response) {
        const resp = response.toLowerCase();
        this.conversationContext.awaitingResponse = false;
        
        if (resp.includes('yes') || resp === 'y' || resp === 'yeah' || resp === 'yep') {
            switch (this.conversationContext.pendingAction) {
                case 'show_offices':
                    setTimeout(() => this.showFaqAnswer('offices'), 300);
                    return { text: "Perfect! Here are our office locations and contact details.", followUp: false };
                case 'show_contact':
                    setTimeout(() => this.showFaqAnswer('contact'), 300);
                    return { text: "Great! Here's all our contact information and support options.", followUp: false };
                case 'show_buying_guide':
                    setTimeout(() => this.showFaqAnswer('how to buy'), 300);
                    return { text: "Excellent! Here's a step-by-step guide to buying products.", followUp: false };
                default:
                    return { text: "I'll show you that information now.", followUp: false };
            }
        } else {
            return { text: "No problem! Is there something else I can help you with?", followUp: false };
        }
    }

    handleQuickResponse(response) {
        const chatInput = document.getElementById('chatInput');
        chatInput.value = response === 'yes' ? 'yes' : 'no thanks';
        this.sendUserMessage();
    }

    handleQuickQuestion(question, key) {
        this.addMessageToChat(question, 'user');
        setTimeout(() => {
            if (key === 'offices') {
                const response = this.getAIResponse('where are your offices');
                this.addMessageToChat(response.text, 'bot');
            } else if (key === 'contact') {
                const response = this.getAIResponse('contact information');
                this.addMessageToChat(response.text, 'bot');
            } else {
                this.addMessageToChat(`I can help you with ${question.toLowerCase()}! Would you like more information?`, 'bot');
                this.conversationContext.awaitingResponse = true;
                this.conversationContext.pendingAction = `show_${key.replace(' ', '_')}`;
            }
        }, 500);
    }

    showFaqAnswer(key) {
        const faq = this.knowledgeBase[key];
        if (faq) {
            const faqContent = document.getElementById('faqContent');
            faqContent.innerHTML = faq.content;
            
            const faqModal = document.getElementById('faqModal');
            faqModal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            
            // Add smooth animation
            faqModal.style.animation = 'slideUp 0.3s ease-out';
        }
    }

    closeFaqModal() {
        const faqModal = document.getElementById('faqModal');
        faqModal.classList.add('hidden');
        document.body.style.overflow = '';
        this.conversationContext.awaitingResponse = false;
        this.conversationContext.pendingAction = null;
    }

    clearChat() {
        const chatMessages = document.getElementById('chatMessages');
        const initialMessage = chatMessages.children[0];
        const quickQuestions = chatMessages.children[1];
        
        chatMessages.innerHTML = '';
        chatMessages.appendChild(initialMessage);
        chatMessages.appendChild(quickQuestions);
        
        this.chatHistory = [];
        this.conversationContext = {
            lastQuestion: null,
            awaitingResponse: false,
            pendingAction: null
        };
        
        this.addMessageToChat("Chat cleared! How can I help you now? 😊", 'bot');
    }

    toggleVoiceInput() {
        const voiceBtn = document.getElementById('voiceInput');
        if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            const recognition = new SpeechRecognition();
            
            recognition.lang = 'en-US';
            recognition.interimResults = false;
            
            voiceBtn.classList.add('text-red-500', 'animate-pulse');
            
            recognition.onresult = (event) => {
                const transcript = event.results[0][0].transcript;
                document.getElementById('chatInput').value = transcript;
                voiceBtn.classList.remove('text-red-500', 'animate-pulse');
            };
            
            recognition.onerror = () => {
                voiceBtn.classList.remove('text-red-500', 'animate-pulse');
                this.addMessageToChat("Voice input failed. Please try typing instead.", 'bot');
            };

            recognition.onend = () => {
                voiceBtn.classList.remove('text-red-500', 'animate-pulse');
            };

            recognition.start();
        } else {
            this.addMessageToChat("Voice recognition is not supported in your browser. Please type your message.", 'bot');
        }
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    isGreeting(message) {
        const greetings = ['hello', 'hi', 'hey', 'good morning', 'good afternoon', 'good evening'];
        return greetings.some(greeting => message.includes(greeting));
    }
}

// Initialize chatbot when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    // Add some CSS animations
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes slideDown {
            from {
                opacity: 1;
                transform: translateY(0);
            }
            to {
                opacity: 0;
                transform: translateY(20px);
            }
        }
        
        .animate-slide-up {
            animation: slideUp 0.3s ease-out;
        }
        
        .animate-slide-down {
            animation: slideDown 0.3s ease-out;
        }
        
        .quick-question {
            transition: all 0.2s ease;
        }
        
        .quick-question:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        #chatMessages::-webkit-scrollbar {
            width: 6px;
        }
        
        #chatMessages::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 3px;
        }
        
        #chatMessages::-webkit-scrollbar-thumb {
            background: #6366f1;
            border-radius: 3px;
        }
        
        #faqContent::-webkit-scrollbar {
            width: 6px;
        }
        
        #faqContent::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 3px;
        }
        
        #faqContent::-webkit-scrollbar-thumb {
            background: #8b5cf6;
            border-radius: 3px;
        }
    `;
    document.head.appendChild(style);

    // Initialize chatbot
    window.chatbotPro = new ProfessionalChatbot();
    
    // Auto-open after 10 seconds (if first visit)
    setTimeout(() => {
        if (!localStorage.getItem('chatbotProShown')) {
            const notificationBadge = document.getElementById('notificationBadge');
            notificationBadge.classList.remove('hidden');
            notificationBadge.textContent = '👋';
            
            // Show notification
            setTimeout(() => {
                window.chatbotPro.openChat();
                window.chatbotPro.addMessageToChat("Hi there! I'm here to help you with any questions about our marketplace. 😊", 'bot');
                localStorage.setItem('chatbotProShown', 'true');
            }, 10000);
        }
    }, 10000);

    // Add welcome notification after 30 seconds if not interacted
    setTimeout(() => {
        if (!window.chatbotPro.isOpen && !localStorage.getItem('chatbotWelcomeShown')) {
            const notificationBadge = document.getElementById('notificationBadge');
            notificationBadge.classList.remove('hidden');
            notificationBadge.textContent = '💬';
            localStorage.setItem('chatbotWelcomeShown', 'true');
        }
    }, 30000);
});

// Add keyboard shortcut (Ctrl+/ or Cmd+/ to open chatbot)
document.addEventListener('keydown', (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key === '/') {
        e.preventDefault();
        window.chatbotPro.openChat();
        document.getElementById('chatInput').focus();
    }
    
    // Escape to close
    if (e.key === 'Escape' && window.chatbotPro.isOpen) {
        window.chatbotPro.closeChat();
    }
});