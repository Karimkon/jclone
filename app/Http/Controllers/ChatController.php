<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\VendorProfile;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    /**
     * Display list of conversations for the current user
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        $conversations = Conversation::forUser($user->id)
            ->active()
            ->with(['buyer:id,name', 'vendorProfile.user:id,name', 'listing:id,title', 'latestMessage'])
            ->withCount(['messages as unread_count' => function ($query) use ($user) {
                $query->where('sender_id', '!=', $user->id)
                      ->whereNull('read_at');
            }])
            ->orderByDesc('last_message_at')
            ->paginate(20);

        // Check if user is a vendor
        $isVendor = $user->isVendor();

        return view('chat.index', compact('conversations', 'isVendor'));
    }

    /**
     * Show a specific conversation
     */
    public function show(Conversation $conversation)
    {
        $user = auth()->user();
        
        // Check if user is participant
        if (!$conversation->isParticipant($user->id)) {
            abort(403, 'You are not part of this conversation.');
        }

        // Mark messages as read
        $conversation->markAsReadFor($user->id);

        // Load messages with sender info
        $messages = $conversation->messages()
            ->notDeleted()
            ->with('sender:id,name')
            ->get();

        // Get the other participant
        $otherParticipant = $conversation->getOtherParticipant($user->id);
        
        // Check if current user is the vendor in this conversation
        $isVendorInConversation = $conversation->vendorProfile && 
                                   $conversation->vendorProfile->user_id === $user->id;

        return view('chat.show', compact('conversation', 'messages', 'otherParticipant', 'isVendorInConversation'));
    }

    /**
     * Start a new conversation with a vendor (from product page)
     */
    public function startConversation(Request $request)
    {
        $request->validate([
            'vendor_profile_id' => 'required|exists:vendor_profiles,id',
            'listing_id' => 'nullable|exists:listings,id',
            'message' => 'required|string|max:1000',
        ]);

        $user = auth()->user();
        $vendorProfile = VendorProfile::findOrFail($request->vendor_profile_id);

        // Prevent vendor from messaging themselves
        if ($vendorProfile->user_id === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot message yourself.'
            ], 400);
        }

        // Get or create conversation
        $conversation = Conversation::findOrCreateBetween(
            $user->id,
            $vendorProfile->id,
            $request->listing_id
        );

        // Create the first message
        $message = $conversation->messages()->create([
            'sender_id' => $user->id,
            'body' => $request->message,
            'type' => 'text',
        ]);

        // If there's a listing, add context as system message
        if ($request->listing_id && !$conversation->wasRecentlyCreated) {
            // Only add listing context if this is a new conversation
        } elseif ($request->listing_id && $conversation->wasRecentlyCreated) {
            $listing = Listing::find($request->listing_id);
            if ($listing) {
                $conversation->update(['subject' => 'Inquiry about: ' . $listing->title]);
            }
        }

        return response()->json([
            'success' => true,
            'conversation_id' => $conversation->id,
            'message' => 'Message sent successfully!',
            'redirect' => route('chat.show', $conversation)
        ]);
    }

    /**
     * Send a message in an existing conversation
     */
    public function sendMessage(Request $request, Conversation $conversation)
    {
        $user = auth()->user();
        
        // Check if user is participant
        if (!$conversation->isParticipant($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not part of this conversation.'
            ], 403);
        }

        // Check if conversation is active
        if ($conversation->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'This conversation is no longer active.'
            ], 400);
        }

        $request->validate([
            'body' => 'required_without:attachment|string|max:2000',
            'attachment' => 'nullable|file|max:5120|mimes:jpg,jpeg,png,gif,pdf,doc,docx',
        ]);

        $messageData = [
            'sender_id' => $user->id,
            'body' => $request->body ?? '',
            'type' => 'text',
        ];

        // Handle file attachment
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('chat-attachments/' . $conversation->id, 'public');
            
            $messageData['attachment_path'] = $path;
            $messageData['attachment_name'] = $file->getClientOriginalName();
            $messageData['type'] = in_array($file->getClientMimeType(), ['image/jpeg', 'image/png', 'image/gif']) 
                ? 'image' 
                : 'file';
        }

        $message = $conversation->messages()->create($messageData);

        // Load sender relationship
        $message->load('sender:id,name');

        return response()->json([
            'success' => true,
            'message' => $message,
            'formatted_time' => $message->formatted_time,
        ]);
    }

    /**
     * Get new messages (for polling/real-time updates)
     */
    public function getNewMessages(Request $request, Conversation $conversation)
    {
        $user = auth()->user();
        
        if (!$conversation->isParticipant($user->id)) {
            return response()->json(['success' => false], 403);
        }

        $lastMessageId = $request->input('last_message_id', 0);

        $messages = $conversation->messages()
            ->notDeleted()
            ->where('id', '>', $lastMessageId)
            ->with('sender:id,name')
            ->get();

        // Mark new messages from other user as read
        $conversation->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->where('id', '>', $lastMessageId)
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'messages' => $messages,
            'current_user_id' => $user->id,
        ]);
    }

    /**
     * Get unread message count for header badge
     */
    public function getUnreadCount()
    {
        $user = auth()->user();
        
        $unreadCount = Message::whereHas('conversation', function ($query) use ($user) {
            $query->forUser($user->id)->active();
        })
        ->where('sender_id', '!=', $user->id)
        ->whereNull('read_at')
        ->count();

        return response()->json([
            'success' => true,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Archive a conversation
     */
    public function archive(Conversation $conversation)
    {
        $user = auth()->user();
        
        if (!$conversation->isParticipant($user->id)) {
            return response()->json(['success' => false], 403);
        }

        $conversation->update(['status' => 'archived']);

        return response()->json([
            'success' => true,
            'message' => 'Conversation archived successfully.'
        ]);
    }

    /**
     * Delete a message (soft delete)
     */
    public function deleteMessage(Message $message)
    {
        $user = auth()->user();
        
        // Only sender can delete their own messages
        if ($message->sender_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You can only delete your own messages.'
            ], 403);
        }

        $message->update(['is_deleted' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Message deleted.'
        ]);
    }
}
