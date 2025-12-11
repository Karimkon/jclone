<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\CallbackRequest;
use Illuminate\Http\Request;

class VendorCallbackController extends Controller
{
    public function index()
    {
        $vendorProfile = auth()->user()->vendorProfile;
        
        $callbacks = CallbackRequest::with(['listing', 'buyer'])
            ->forVendor($vendorProfile->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        $pendingCount = CallbackRequest::forVendor($vendorProfile->id)
            ->pending()
            ->count();
        
        return view('vendor.callbacks.index', compact('callbacks', 'pendingCount'));
    }

    public function show(CallbackRequest $callback)
    {
        // Manual authorization check
        $vendorProfile = auth()->user()->vendorProfile;
        if (!$vendorProfile || $vendorProfile->id !== $callback->vendor_profile_id) {
            abort(403, 'Unauthorized action.');
        }
        
        $callback->load(['listing', 'buyer']);
        
        return view('vendor.callbacks.show', compact('callback'));
    }

    public function updateStatus(Request $request, CallbackRequest $callback)
{
    // Manual authorization check
    $vendorProfile = auth()->user()->vendorProfile;
    if (!$vendorProfile || $vendorProfile->id !== $callback->vendor_profile_id) {
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.',
            ], 403);
        }
        return redirect()->route('vendor.callbacks.index')->with('error', 'Unauthorized action.');
    }
    
    $validated = $request->validate([
        'status' => 'required|in:contacted,completed,cancelled',
        'vendor_notes' => 'nullable|string|max:1000',
    ]);

    $callback->update([
        'status' => $validated['status'],
        'vendor_notes' => $validated['vendor_notes'] ?? $callback->vendor_notes,
        'contacted_at' => $validated['status'] === 'contacted' ? now() : $callback->contacted_at,
    ]);

    // Check if it's an AJAX request
    if ($request->wantsJson() || $request->ajax()) {
        return response()->json([
            'success' => true,
            'message' => 'Callback status updated successfully',
        ]);
    }

    // For regular form submission, redirect back to index
    return redirect()->route('vendor.callbacks.index')->with('success', 'Callback status updated successfully');
}
}