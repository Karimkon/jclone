<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VendorProfileController extends Controller
{
    /**
     * Display vendor's profile
     */
    public function show()
    {
        $vendor = Auth::user()->vendorProfile;
        $user = Auth::user();
        
        if (!$vendor) {
            return redirect()->route('vendor.onboard.create');
        }

        return view('vendor.profile.show', compact('vendor', 'user'));
    }

    /**
     * Update vendor's profile
     */
    public function update(Request $request)
    {
        $vendor = Auth::user()->vendorProfile;
        $user = Auth::user();
        
        if (!$vendor) {
            return redirect()->route('vendor.onboard.create');
        }

        $request->validate([
            'business_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'description' => 'nullable|string|max:1000',
        ]);

        // Update vendor profile
        $vendor->update([
            'business_name' => $request->business_name,
            'address' => $request->address,
            'meta' => array_merge($vendor->meta ?? [], [
                'description' => $request->description,
                'updated_at' => now()->toDateTimeString(),
            ])
        ]);

        // Update user phone
        $user->update([
            'phone' => $request->phone,
        ]);

        return redirect()->route('vendor.profile.show')
            ->with('success', 'Profile updated successfully!');
    }
}