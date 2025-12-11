{{-- resources/views/components/chat-modal.blade.php --}}
{{-- 
    Chat Modal Component
    Include this in your product page (marketplace/show.blade.php)
    
    Usage: @include('components.chat-modal', ['listing' => $listing])
--}}

<!-- Chat Modal -->
<div id="chatModal" class="fixed inset-0 z-50 hidden">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeChatModal()"></div>
    
    <!-- Modal Content -->
    <div class="absolute inset-0 flex items-center justify-center p-4 pointer-events-none">
        <div class="bg-white rounded-2xl max-w-lg w-full max-h-[80vh] overflow-hidden shadow-2xl pointer-events-auto transform transition-all animate-scale-in">
            <!-- Header -->
            <div class="bg-gradient-to-r from-primary to-purple-600 text-white p-5">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                            <i class="fas fa-comment-dots text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg">Contact Vendor</h3>
                            <p class="text-white/80 text-sm">
                                {{ $listing->vendor->business_name ?? 'Verified Vendor' }}
                            </p>
                        </div>
                    </div>
                    <button onclick="closeChatModal()" class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center hover:bg-white/20 transition">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            
            <!-- Product Preview -->
            <div class="bg-gray-50 px-5 py-4 border-b border-gray-100">
                <div class="flex items-center gap-4">
                    @if($listing->images->first())
                        <img src="{{ asset('storage/' . $listing->images->first()->path) }}" 
                             alt="{{ $listing->title }}"
                             class="w-16 h-16 rounded-xl object-cover">
                    @else
                        <div class="w-16 h-16 bg-gray-200 rounded-xl flex items-center justify-center">
                            <i class="fas fa-image text-gray-400"></i>
                        </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-gray-900 truncate">{{ $listing->title }}</p>
                        <p class="text-lg font-bold text-primary">UGX {{ number_format($listing->price, 2) }}</p>
                    </div>
                </div>
            </div>
            
            <!-- Message Form -->
            <form id="chatModalForm" class="p-5">
                @csrf
                <input type="hidden" name="vendor_profile_id" value="{{ $listing->vendor_profile_id }}">
                <input type="hidden" name="listing_id" value="{{ $listing->id }}">
                
                <!-- Quick Message Options -->
                <div class="mb-4">
                    <p class="text-sm text-gray-500 mb-3">Quick messages:</p>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" onclick="setQuickMessage('Is this item still available?')" 
                                class="quick-msg-btn px-3 py-1.5 bg-gray-100 text-gray-700 text-sm rounded-full hover:bg-gray-200 transition">
                            Is this available?
                        </button>
                        <button type="button" onclick="setQuickMessage('What is the lowest price for this item?')"
                                class="quick-msg-btn px-3 py-1.5 bg-gray-100 text-gray-700 text-sm rounded-full hover:bg-gray-200 transition">
                            Best price?
                        </button>
                        <button type="button" onclick="setQuickMessage('Can you deliver to my location?')"
                                class="quick-msg-btn px-3 py-1.5 bg-gray-100 text-gray-700 text-sm rounded-full hover:bg-gray-200 transition">
                            Delivery options?
                        </button>
                        <button type="button" onclick="setQuickMessage('I would like more details about this product.')"
                                class="quick-msg-btn px-3 py-1.5 bg-gray-100 text-gray-700 text-sm rounded-full hover:bg-gray-200 transition">
                            More details
                        </button>
                    </div>
                </div>
                
                <!-- Message Input -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Your message</label>
                    <textarea id="chatMessageInput" 
                              name="message" 
                              rows="4"
                              class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary transition resize-none"
                              placeholder="Type your message to the vendor..."
                              required
                              maxlength="1000"></textarea>
                    <div class="flex justify-end mt-1">
                        <span id="charCount" class="text-xs text-gray-400">0/1000</span>
                    </div>
                </div>
                
                <!-- Submit Button -->
                <button type="submit" 
                        id="sendChatBtn"
                        class="w-full py-3.5 bg-primary text-white rounded-xl font-bold hover:bg-indigo-700 transition flex items-center justify-center gap-2">
                    <i class="fas fa-paper-plane"></i>
                    Send Message
                </button>
                
                <!-- Info Note -->
                <p class="text-xs text-gray-400 text-center mt-4">
                    <i class="fas fa-shield-alt mr-1"></i>
                    Your message will be sent securely to the vendor
                </p>
            </form>
        </div>
    </div>
</div>

<style>
@keyframes scale-in {
    from {
        transform: scale(0.95);
        opacity: 0;
    }
    to {
        transform: scale(1);
        opacity: 1;
    }
}

.animate-scale-in {
    animation: scale-in 0.2s ease-out;
}

.quick-msg-btn:focus {
    outline: none;
    ring: 2px;
    ring-color: #4f46e5;
}
</style>

<script>
// Open chat modal
function openChatModal() {
    if (!isAuthenticated) {
        showAuthModal();
        return;
    }
    document.getElementById('chatModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    document.getElementById('chatMessageInput').focus();
}

// Close chat modal
function closeChatModal() {
    document.getElementById('chatModal').classList.add('hidden');
    document.body.style.overflow = '';
}

// Set quick message
function setQuickMessage(message) {
    const input = document.getElementById('chatMessageInput');
    input.value = message;
    updateCharCount();
    input.focus();
}

// Update character count
function updateCharCount() {
    const input = document.getElementById('chatMessageInput');
    const count = document.getElementById('charCount');
    count.textContent = `${input.value.length}/1000`;
}

document.getElementById('chatMessageInput')?.addEventListener('input', updateCharCount);

// Submit chat form
document.getElementById('chatModalForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const btn = document.getElementById('sendChatBtn');
    const originalHtml = btn.innerHTML;
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
    btn.disabled = true;
    
    try {
        const formData = new FormData(this);
        
        const response = await fetch('/chat/start', {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Show success and redirect to conversation
            btn.innerHTML = '<i class="fas fa-check"></i> Message Sent!';
            btn.classList.remove('bg-primary', 'hover:bg-indigo-700');
            btn.classList.add('bg-green-500');
            
            showToast('Message sent successfully!', 'success');
            
            setTimeout(() => {
                window.location.href = data.redirect;
            }, 1000);
        } else {
            throw new Error(data.message || 'Failed to send message');
        }
    } catch (error) {
        btn.innerHTML = originalHtml;
        btn.disabled = false;
        showToast(error.message || 'Failed to send message', 'error');
    }
});

// Close on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeChatModal();
    }
});
</script>
