{{-- resources/views/chat/show.blade.php --}}
@extends($isVendorInConversation ? 'layouts.vendor' : 'layouts.buyer')

@section('title', 'Chat with ' . $otherParticipant->name)

@push('styles')
<style>
    .chat-container {
        height: calc(100vh - 200px);
        min-height: 500px;
        display: flex;
        flex-direction: column;
    }
    
    .messages-area {
        flex: 1;
        overflow-y: auto;
        scroll-behavior: smooth;
    }
    
    .message-bubble {
        max-width: 70%;
        word-wrap: break-word;
    }
    
    .message-sent {
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        color: white;
        border-radius: 20px 20px 4px 20px;
    }
    
    .message-received {
        background: #f3f4f6;
        color: #1f2937;
        border-radius: 20px 20px 20px 4px;
    }
    
    .typing-indicator span {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background-color: #9ca3af;
        animation: typing 1.4s infinite ease-in-out both;
    }
    
    .typing-indicator span:nth-child(1) { animation-delay: -0.32s; }
    .typing-indicator span:nth-child(2) { animation-delay: -0.16s; }
    
    @keyframes typing {
        0%, 80%, 100% { transform: scale(0.6); opacity: 0.5; }
        40% { transform: scale(1); opacity: 1; }
    }
    
    .image-preview {
        max-width: 280px;
        max-height: 200px;
        object-fit: cover;
        border-radius: 12px;
        cursor: pointer;
    }
    
    .attachment-preview {
        background: rgba(255,255,255,0.9);
        border-radius: 8px;
        padding: 8px 12px;
    }
    
    /* Custom scrollbar */
    .messages-area::-webkit-scrollbar {
        width: 6px;
    }
    
    .messages-area::-webkit-scrollbar-track {
        background: transparent;
    }
    
    .messages-area::-webkit-scrollbar-thumb {
        background: #d1d5db;
        border-radius: 3px;
    }
    
    .messages-area::-webkit-scrollbar-thumb:hover {
        background: #9ca3af;
    }
</style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto px-4 py-6">
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden chat-container">
        <!-- Chat Header -->
        <div class="border-b border-gray-100 p-4 flex items-center justify-between bg-white sticky top-0 z-10">
            <div class="flex items-center gap-4">
                <a href="{{ route('chat.index') }}" class="lg:hidden w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 hover:bg-gray-200 transition">
                    <i class="fas fa-arrow-left"></i>
                </a>
                
                <!-- User Avatar & Info -->
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-gradient-to-br from-primary to-purple-600 text-white rounded-full flex items-center justify-center font-bold">
                        {{ strtoupper(substr($otherParticipant->name ?? 'U', 0, 1)) }}
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">{{ $otherParticipant->name }}</h3>
                        @if(!$isVendorInConversation && $conversation->vendorProfile)
                            <p class="text-sm text-gray-500">
                                <i class="fas fa-store mr-1"></i>
                                {{ $conversation->vendorProfile->business_name }}
                            </p>
                        @else
                            <p class="text-sm text-gray-500">
                                <span class="w-2 h-2 bg-green-500 rounded-full inline-block mr-1"></span>
                                Online
                            </p>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="flex items-center gap-2">
                @if($conversation->listing)
                    <a href="{{ route('marketplace.show', $conversation->listing) }}" 
                       class="hidden sm:flex items-center gap-2 px-4 py-2 text-sm text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition"
                       title="View Product">
                        <i class="fas fa-external-link-alt"></i>
                        <span class="hidden md:inline">View Product</span>
                    </a>
                @endif
                <button onclick="toggleChatOptions()" class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 hover:bg-gray-200 transition">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
            </div>
        </div>

        <!-- Product Context (if applicable) -->
        @if($conversation->listing)
            <div class="bg-gray-50 px-4 py-3 border-b border-gray-100">
                <div class="flex items-center gap-3">
                    @if($conversation->listing->images->first())
                        <img src="{{ asset('storage/' . $conversation->listing->images->first()->path) }}" 
                             alt="{{ $conversation->listing->title }}"
                             class="w-12 h-12 rounded-lg object-cover">
                    @else
                        <div class="w-12 h-12 bg-gray-200 rounded-lg flex items-center justify-center">
                            <i class="fas fa-image text-gray-400"></i>
                        </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $conversation->listing->title }}</p>
                        <p class="text-sm text-primary font-semibold">UGX {{ number_format($conversation->listing->price, 2) }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Messages Area -->
        <div class="messages-area p-4 space-y-4" id="messagesArea">
            @php $lastDate = null; @endphp
            
            @foreach($messages as $message)
                @php 
                    $messageDate = $message->created_at->format('Y-m-d');
                    $showDateDivider = $messageDate !== $lastDate;
                    $lastDate = $messageDate;
                    $isSent = $message->sender_id === auth()->id();
                @endphp
                
                {{-- Date Divider --}}
                @if($showDateDivider)
                    <div class="flex items-center justify-center my-6">
                        <div class="px-4 py-1 bg-gray-100 rounded-full text-xs text-gray-500 font-medium">
                            @if($message->created_at->isToday())
                                Today
                            @elseif($message->created_at->isYesterday())
                                Yesterday
                            @else
                                {{ $message->created_at->format('F j, Y') }}
                            @endif
                        </div>
                    </div>
                @endif
                
                {{-- Message Bubble --}}
                <div class="flex {{ $isSent ? 'justify-end' : 'justify-start' }}" data-message-id="{{ $message->id }}">
                    <div class="message-bubble {{ $isSent ? 'message-sent' : 'message-received' }} px-4 py-3">
                        {{-- Attachment --}}
                        @if($message->attachment_path)
                            @if($message->type === 'image')
                                <img src="{{ $message->attachment_url }}" 
                                     alt="Shared image"
                                     class="image-preview mb-2"
                                     onclick="openImageViewer('{{ $message->attachment_url }}')">
                            @else
                                <a href="{{ $message->attachment_url }}" 
                                   target="_blank"
                                   class="attachment-preview flex items-center gap-2 mb-2 {{ $isSent ? 'text-white/90' : 'text-gray-700' }}">
                                    <i class="fas fa-file-alt"></i>
                                    <span class="text-sm truncate">{{ $message->attachment_name }}</span>
                                    <i class="fas fa-download text-xs"></i>
                                </a>
                            @endif
                        @endif
                        
                        {{-- Message Text --}}
                        @if($message->body)
                            <p class="text-sm whitespace-pre-wrap">{{ $message->body }}</p>
                        @endif
                        
                        {{-- Time & Status --}}
                        <div class="flex items-center justify-end gap-1 mt-1 {{ $isSent ? 'text-white/70' : 'text-gray-400' }}">
                            <span class="text-xs">{{ $message->created_at->format('g:i A') }}</span>
                            @if($isSent)
                                @if($message->read_at)
                                    <i class="fas fa-check-double text-xs" title="Read"></i>
                                @else
                                    <i class="fas fa-check text-xs" title="Sent"></i>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
            
            {{-- Typing Indicator (hidden by default) --}}
            <div id="typingIndicator" class="flex justify-start hidden">
                <div class="message-bubble message-received px-4 py-3">
                    <div class="typing-indicator">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Message Input -->
        <div class="border-t border-gray-100 p-4 bg-white">
            <form id="messageForm" class="flex items-end gap-3">
                @csrf
                
                {{-- Attachment Preview --}}
                <div id="attachmentPreview" class="hidden absolute bottom-full left-0 right-0 p-4 bg-gray-50 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div id="previewContent"></div>
                            <span id="previewFileName" class="text-sm text-gray-600"></span>
                        </div>
                        <button type="button" onclick="clearAttachment()" class="text-red-500 hover:text-red-700">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                
                {{-- Attachment Button --}}
                <label class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 hover:bg-gray-200 transition cursor-pointer flex-shrink-0">
                    <i class="fas fa-paperclip"></i>
                    <input type="file" 
                           id="attachmentInput" 
                           name="attachment" 
                           class="hidden" 
                           accept="image/*,.pdf,.doc,.docx"
                           onchange="previewAttachment(this)">
                </label>
                
                {{-- Text Input --}}
                <div class="flex-1 relative">
                    <textarea id="messageInput" 
                              name="body" 
                              rows="1"
                              class="w-full px-4 py-3 bg-gray-100 border-0 rounded-2xl resize-none focus:ring-2 focus:ring-primary focus:bg-white transition"
                              placeholder="Type a message..."
                              maxlength="2000"
                              onkeydown="handleKeyDown(event)"
                              oninput="autoResize(this)"></textarea>
                </div>
                
                {{-- Send Button --}}
                <button type="submit" 
                        id="sendButton"
                        class="w-12 h-12 rounded-full bg-primary text-white flex items-center justify-center hover:bg-indigo-700 transition flex-shrink-0 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </form>
        </div>
    </div>
</div>

{{-- Image Viewer Modal --}}
<div id="imageViewer" class="fixed inset-0 z-50 bg-black/90 hidden flex items-center justify-center" onclick="closeImageViewer()">
    <button class="absolute top-4 right-4 text-white text-2xl hover:text-gray-300">
        <i class="fas fa-times"></i>
    </button>
    <img id="viewerImage" src="" class="max-w-[90%] max-h-[90vh] object-contain">
</div>

{{-- Chat Options Dropdown --}}
<div id="chatOptions" class="hidden fixed top-20 right-8 bg-white rounded-xl shadow-lg py-2 z-50 min-w-[180px]">
    <a href="#" onclick="archiveConversation()" class="flex items-center gap-3 px-4 py-2 text-gray-700 hover:bg-gray-50 transition">
        <i class="fas fa-archive"></i>
        Archive Chat
    </a>
    <a href="#" onclick="reportConversation()" class="flex items-center gap-3 px-4 py-2 text-red-600 hover:bg-red-50 transition">
        <i class="fas fa-flag"></i>
        Report
    </a>
</div>

@push('scripts')
<script>
const conversationId = {{ $conversation->id }};
const currentUserId = {{ auth()->id() }};
const csrfToken = '{{ csrf_token() }}';
let lastMessageId = {{ $messages->last()->id ?? 0 }};
let isPolling = true;
let pollInterval;

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    scrollToBottom();
    startPolling();
    
    // Auto-resize textarea
    const textarea = document.getElementById('messageInput');
    autoResize(textarea);
});

// Scroll to bottom of messages
function scrollToBottom() {
    const messagesArea = document.getElementById('messagesArea');
    messagesArea.scrollTop = messagesArea.scrollHeight;
}

// Auto-resize textarea
function autoResize(textarea) {
    textarea.style.height = 'auto';
    textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
}

// Handle Enter key
function handleKeyDown(event) {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        document.getElementById('messageForm').dispatchEvent(new Event('submit'));
    }
}

// Send Message
document.getElementById('messageForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const messageInput = document.getElementById('messageInput');
    const attachmentInput = document.getElementById('attachmentInput');
    const sendButton = document.getElementById('sendButton');
    
    const body = messageInput.value.trim();
    const attachment = attachmentInput.files[0];
    
    if (!body && !attachment) return;
    
    // Disable button
    sendButton.disabled = true;
    sendButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    
    try {
        const formData = new FormData();
        formData.append('_token', csrfToken);
        if (body) formData.append('body', body);
        if (attachment) formData.append('attachment', attachment);
        
        const response = await fetch(`/chat/${conversationId}/send`, {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Add message to UI
            appendMessage(data.message, true);
            
            // Clear input
            messageInput.value = '';
            autoResize(messageInput);
            clearAttachment();
            
            // Update last message ID
            lastMessageId = data.message.id;
            
            // Scroll to bottom
            scrollToBottom();
        } else {
            showToast(data.message || 'Failed to send message', 'error');
        }
    } catch (error) {
        console.error('Send error:', error);
        showToast('Failed to send message', 'error');
    } finally {
        sendButton.disabled = false;
        sendButton.innerHTML = '<i class="fas fa-paper-plane"></i>';
    }
});

// Append message to UI
function appendMessage(message, isSent) {
    const messagesArea = document.getElementById('messagesArea');
    const typingIndicator = document.getElementById('typingIndicator');
    
    const messageHtml = `
        <div class="flex ${isSent ? 'justify-end' : 'justify-start'}" data-message-id="${message.id}">
            <div class="message-bubble ${isSent ? 'message-sent' : 'message-received'} px-4 py-3">
                ${message.attachment_path && message.type === 'image' ? `
                    <img src="${message.attachment_url}" 
                         alt="Shared image"
                         class="image-preview mb-2"
                         onclick="openImageViewer('${message.attachment_url}')">
                ` : ''}
                ${message.attachment_path && message.type === 'file' ? `
                    <a href="${message.attachment_url}" 
                       target="_blank"
                       class="attachment-preview flex items-center gap-2 mb-2 ${isSent ? 'text-white/90' : 'text-gray-700'}">
                        <i class="fas fa-file-alt"></i>
                        <span class="text-sm truncate">${message.attachment_name}</span>
                        <i class="fas fa-download text-xs"></i>
                    </a>
                ` : ''}
                ${message.body ? `<p class="text-sm whitespace-pre-wrap">${escapeHtml(message.body)}</p>` : ''}
                <div class="flex items-center justify-end gap-1 mt-1 ${isSent ? 'text-white/70' : 'text-gray-400'}">
                    <span class="text-xs">${formatTime(message.created_at)}</span>
                    ${isSent ? '<i class="fas fa-check text-xs" title="Sent"></i>' : ''}
                </div>
            </div>
        </div>
    `;
    
    typingIndicator.insertAdjacentHTML('beforebegin', messageHtml);
}

// Poll for new messages
function startPolling() {
    pollInterval = setInterval(async function() {
        if (!isPolling) return;
        
        try {
            const response = await fetch(`/chat/${conversationId}/new-messages?last_message_id=${lastMessageId}`, {
                headers: {
                    'Accept': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (data.success && data.messages.length > 0) {
                data.messages.forEach(message => {
                    if (message.sender_id !== currentUserId) {
                        appendMessage(message, false);
                    }
                    lastMessageId = Math.max(lastMessageId, message.id);
                });
                scrollToBottom();
            }
        } catch (error) {
            console.error('Polling error:', error);
        }
    }, 3000); // Poll every 3 seconds
}

// Stop polling when page is hidden
document.addEventListener('visibilitychange', function() {
    isPolling = document.visibilityState === 'visible';
});

// Attachment preview
function previewAttachment(input) {
    const file = input.files[0];
    if (!file) return;
    
    const preview = document.getElementById('attachmentPreview');
    const content = document.getElementById('previewContent');
    const fileName = document.getElementById('previewFileName');
    
    fileName.textContent = file.name;
    
    if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(e) {
            content.innerHTML = `<img src="${e.target.result}" class="w-12 h-12 object-cover rounded-lg">`;
        };
        reader.readAsDataURL(file);
    } else {
        content.innerHTML = '<i class="fas fa-file text-2xl text-gray-400"></i>';
    }
    
    preview.classList.remove('hidden');
}

function clearAttachment() {
    document.getElementById('attachmentInput').value = '';
    document.getElementById('attachmentPreview').classList.add('hidden');
}

// Image viewer
function openImageViewer(src) {
    document.getElementById('viewerImage').src = src;
    document.getElementById('imageViewer').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeImageViewer() {
    document.getElementById('imageViewer').classList.add('hidden');
    document.body.style.overflow = '';
}

// Chat options
function toggleChatOptions() {
    document.getElementById('chatOptions').classList.toggle('hidden');
}

async function archiveConversation() {
    if (!confirm('Are you sure you want to archive this conversation?')) return;
    
    try {
        const response = await fetch(`/chat/${conversationId}/archive`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            window.location.href = '/chat';
        }
    } catch (error) {
        showToast('Failed to archive conversation', 'error');
    }
}

function reportConversation() {
    showToast('Report feature coming soon', 'info');
    document.getElementById('chatOptions').classList.add('hidden');
}

// Utility functions
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
}

function showToast(message, type = 'info') {
    const colors = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        info: 'bg-blue-500'
    };
    
    const toast = document.createElement('div');
    toast.className = `fixed bottom-4 right-4 ${colors[type]} text-white px-6 py-3 rounded-xl shadow-lg z-50`;
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeImageViewer();
        document.getElementById('chatOptions').classList.add('hidden');
    }
});

// Click outside to close dropdowns
document.addEventListener('click', function(e) {
    if (!e.target.closest('#chatOptions') && !e.target.closest('[onclick*="toggleChatOptions"]')) {
        document.getElementById('chatOptions').classList.add('hidden');
    }
});

// Cleanup
window.addEventListener('beforeunload', function() {
    clearInterval(pollInterval);
});
</script>
@endpush
@endsection
