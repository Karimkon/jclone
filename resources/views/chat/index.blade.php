{{-- resources/views/chat/index.blade.php --}}
@extends($isVendor ? 'layouts.vendor' : 'layouts.buyer')

@section('title', 'Messages')

@section('content')
<div class="max-w-6xl mx-auto px-2 sm:px-4 py-4 sm:py-8 pb-20 sm:pb-8">
    <!-- Header -->
    <div class="flex items-center justify-between mb-4 sm:mb-8">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Messages</h1>
            <p class="text-sm sm:text-base text-gray-600 mt-1">Your conversations with {{ $isVendor ? 'buyers' : 'vendors' }}</p>
        </div>
        <div class="flex items-center gap-4">
            <span class="text-xs sm:text-sm text-gray-500">
                {{ $conversations->total() }} {{ Str::plural('conversation', $conversations->total()) }}
            </span>
        </div>
    </div>

    @if($conversations->isEmpty())
        <!-- Empty State -->
        <div class="bg-white rounded-2xl shadow-sm p-6 sm:p-12 text-center">
            <div class="w-16 h-16 sm:w-20 sm:h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4 sm:mb-6">
                <i class="fas fa-comments text-2xl sm:text-3xl text-gray-400"></i>
            </div>
            <h3 class="text-lg sm:text-xl font-semibold text-gray-900 mb-2">No messages yet</h3>
            <p class="text-sm sm:text-base text-gray-500 mb-4 sm:mb-6">
                @if($isVendor)
                    When buyers contact you about your products, their messages will appear here.
                @else
                    Start a conversation by contacting a vendor from any product page.
                @endif
            </p>
            @if(!$isVendor)
                <a href="{{ route('marketplace.index') }}" class="inline-flex items-center gap-2 px-4 sm:px-6 py-2 sm:py-3 bg-primary text-white rounded-xl font-medium hover:bg-indigo-700 transition text-sm sm:text-base">
                    <i class="fas fa-store"></i>
                    Browse Products
                </a>
            @endif
        </div>
    @else
        <!-- Conversations List -->
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="divide-y divide-gray-100">
                @foreach($conversations as $conversation)
                    @php
                        $otherParticipant = $conversation->getOtherParticipant(auth()->id());
                        $isUnread = $conversation->unread_count > 0;
                    @endphp
                    <a href="{{ route('chat.show', $conversation) }}"
                       class="flex items-center gap-3 sm:gap-4 p-3 sm:p-5 hover:bg-gray-50 transition {{ $isUnread ? 'bg-indigo-50/50' : '' }}">
                        <!-- Avatar -->
                        <div class="relative flex-shrink-0">
                            <div class="w-12 h-12 sm:w-14 sm:h-14 bg-gradient-to-br from-primary to-purple-600 text-white rounded-full flex items-center justify-center text-base sm:text-lg font-bold">
                                {{ strtoupper(substr($otherParticipant->name ?? 'U', 0, 1)) }}
                            </div>
                            @if($isUnread)
                                <div class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs font-bold rounded-full flex items-center justify-center">
                                    {{ $conversation->unread_count > 9 ? '9+' : $conversation->unread_count }}
                                </div>
                            @endif
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2 sm:gap-4">
                                <h4 class="font-semibold text-sm sm:text-base text-gray-900 truncate {{ $isUnread ? 'text-primary' : '' }}">
                                    {{ $otherParticipant->name ?? 'Unknown User' }}
                                    @if(!$isVendor && $conversation->vendorProfile)
                                        <span class="hidden sm:inline text-xs font-normal text-gray-500 ml-2">
                                            {{ $conversation->vendorProfile->business_name }}
                                        </span>
                                    @endif
                                </h4>
                                <span class="text-xs text-gray-500 flex-shrink-0">
                                    {{ $conversation->last_message_at ? $conversation->last_message_at->diffForHumans(null, true) : '' }}
                                </span>
                            </div>

                            @if($conversation->listing)
                                <p class="text-xs text-primary mb-1 truncate">
                                    <i class="fas fa-tag mr-1"></i>
                                    {{ Str::limit($conversation->listing->title, 30) }}
                                </p>
                            @endif

                            @if($conversation->latestMessage)
                                <p class="text-xs sm:text-sm text-gray-600 truncate {{ $isUnread ? 'font-medium' : '' }}">
                                    @if($conversation->latestMessage->sender_id === auth()->id())
                                        <span class="text-gray-400">You: </span>
                                    @endif
                                    @if($conversation->latestMessage->type === 'image')
                                        <i class="fas fa-image mr-1"></i>Photo
                                    @elseif($conversation->latestMessage->type === 'file')
                                        <i class="fas fa-paperclip mr-1"></i>Attachment
                                    @else
                                        {{ Str::limit($conversation->latestMessage->body, 40) }}
                                    @endif
                                </p>
                            @endif
                        </div>

                        <!-- Arrow -->
                        <i class="fas fa-chevron-right text-gray-400 hidden sm:block"></i>
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Pagination -->
        @if($conversations->hasPages())
            <div class="mt-4 sm:mt-6">
                {{ $conversations->links() }}
            </div>
        @endif
    @endif
</div>
@endsection
