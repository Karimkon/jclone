<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
    /**
     * Display all contact messages
     */
    public function index(Request $request)
    {
        $query = ContactMessage::query();
        
        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by contact type
        if ($request->has('contact_type')) {
            $query->where('contact_type', $request->contact_type);
        }
        
        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }
        
        $messages = $query->latest()->paginate(20);
        
        $stats = [
            'total' => ContactMessage::count(),
            'new' => ContactMessage::where('status', 'new')->count(),
            'read' => ContactMessage::where('status', 'read')->count(),
            'responded' => ContactMessage::where('status', 'responded')->count(),
        ];
        
        return view('admin.contact-messages.index', compact('messages', 'stats'));
    }

    /**
     * Show a specific contact message
     */
    public function show($id)
    {
        $message = ContactMessage::findOrFail($id);
        
        // Mark as read when viewed
        if ($message->status == 'new') {
            $message->update(['status' => 'read']);
        }
        
        return view('admin.contact-messages.show', compact('message'));
    }

    /**
     * Update message status
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:new,read,responded,archived',
            'response' => 'nullable|string|max:2000',
        ]);
        
        $message = ContactMessage::findOrFail($id);
        
        $updateData = ['status' => $request->status];
        
        if ($request->filled('response')) {
            $meta = $message->meta ?? [];
            $meta['admin_response'] = $request->response;
            $meta['responded_at'] = now();
            $meta['responded_by'] = auth()->id();
            $updateData['meta'] = $meta;
        }
        
        $message->update($updateData);
        
        return back()->with('success', 'Message status updated successfully.');
    }

    /**
     * Send email response
     */
    public function sendResponse(Request $request, $id)
    {
        $request->validate([
            'response_message' => 'required|string|min:10|max:2000',
            'subject' => 'nullable|string|max:255',
        ]);
        
        $message = ContactMessage::findOrFail($id);
        
        // Send email to the contact
        $subject = $request->subject ?: 'Re: ' . $message->subject;
        
        // You'll need to implement email sending here
        // \Mail::to($message->email)->send(new ContactResponseMail($subject, $request->response_message));
        
        // Update message status
        $meta = $message->meta ?? [];
        $meta['admin_response'] = $request->response_message;
        $meta['response_sent_at'] = now();
        $meta['response_sent_by'] = auth()->id();
        
        $message->update([
            'status' => 'responded',
            'meta' => $meta,
        ]);
        
        return back()->with('success', 'Response sent successfully.');
    }

    /**
     * Delete a contact message
     */
    public function destroy($id)
    {
        $message = ContactMessage::findOrFail($id);
        $message->delete();
        
        return redirect()->route('admin.contact-messages.index')
            ->with('success', 'Contact message deleted successfully.');
    }

    /**
     * Bulk actions
     */
    public function bulkActions(Request $request)
    {
        $request->validate([
            'action' => 'required|in:mark_read,mark_responded,delete',
            'messages' => 'required|array',
            'messages.*' => 'exists:contact_messages,id',
        ]);
        
        switch ($request->action) {
            case 'mark_read':
                ContactMessage::whereIn('id', $request->messages)
                    ->update(['status' => 'read']);
                $message = 'Messages marked as read.';
                break;
                
            case 'mark_responded':
                ContactMessage::whereIn('id', $request->messages)
                    ->update(['status' => 'responded']);
                $message = 'Messages marked as responded.';
                break;
                
            case 'delete':
                ContactMessage::whereIn('id', $request->messages)->delete();
                $message = 'Messages deleted successfully.';
                break;
        }
        
        return back()->with('success', $message);
    }
}