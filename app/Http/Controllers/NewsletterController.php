<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $existing = NewsletterSubscriber::where('email', $request->email)->first();

        if ($existing) {
            if ($existing->status === 'subscribed') {
                return response()->json([
                    'message' => 'You are already subscribed to our newsletter!',
                ], 200);
            }

            // Re-subscribe
            $existing->update([
                'status' => 'subscribed',
                'subscribed_at' => now(),
                'unsubscribed_at' => null,
                'ip_address' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'Welcome back! You have been re-subscribed successfully.',
            ]);
        }

        NewsletterSubscriber::create([
            'email' => $request->email,
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'message' => 'Thank you for subscribing to our newsletter!',
        ]);
    }

    public function unsubscribe(string $token)
    {
        $subscriber = NewsletterSubscriber::where('token', $token)->first();

        if (!$subscriber) {
            return view('newsletter.unsubscribe', [
                'success' => false,
                'message' => 'Invalid unsubscribe link.',
            ]);
        }

        if ($subscriber->status === 'unsubscribed') {
            return view('newsletter.unsubscribe', [
                'success' => true,
                'message' => 'You have already been unsubscribed.',
            ]);
        }

        $subscriber->update([
            'status' => 'unsubscribed',
            'unsubscribed_at' => now(),
        ]);

        return view('newsletter.unsubscribe', [
            'success' => true,
            'message' => 'You have been successfully unsubscribed from our newsletter.',
        ]);
    }
}
