<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\CallbackRequest;
use App\Models\Listing;
use Illuminate\Http\Request;

class CallbackRequestController extends Controller
{
    public function store(Request $request, Listing $listing)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'message' => 'nullable|string|max:500',
        ]);

        $callbackRequest = CallbackRequest::create([
            'listing_id' => $listing->id,
            'buyer_id' => auth()->id(),
            'vendor_profile_id' => $listing->vendor_profile_id,
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'message' => $validated['message'] ?? null,
            'status' => 'pending',
        ]);

        // You can add notification to vendor here
        // event(new CallbackRequestCreated($callbackRequest));

        return response()->json([
            'success' => true,
            'message' => 'Callback request sent! The vendor will contact you soon.',
        ]);
    }
}