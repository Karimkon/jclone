<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\ShippingAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShippingAddressController extends Controller
{
    /**
     * Display all addresses
     */
    public function index()
    {
        $addresses = Auth::user()->shippingAddresses()
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('buyer.addresses.index', compact('addresses'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        return view('buyer.addresses.create');
    }

    /**
     * Store new address
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'label' => 'nullable|string|max:50',
            'recipient_name' => 'required|string|max:100',
            'recipient_phone' => 'required|string|max:20',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'state_region' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'required|string|max:100',
            'delivery_instructions' => 'nullable|string|max:500',
            'is_default' => 'boolean'
        ]);

        // Check address limit (max 3)
        if (Auth::user()->shippingAddresses()->count() >= 3 && !$request->is_default) {
            return back()->with('error', 'You can only have up to 3 shipping addresses.');
        }

        $validated['user_id'] = Auth::id();
        
        // If this is the first address, make it default
        if (Auth::user()->shippingAddresses()->count() === 0) {
            $validated['is_default'] = true;
        }

        $address = ShippingAddress::create($validated);

        return redirect()->route('buyer.addresses.index')
            ->with('success', 'Shipping address added successfully!');
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $address = ShippingAddress::where('user_id', Auth::id())
            ->findOrFail($id);
        
        return view('buyer.addresses.edit', compact('address'));
    }

    /**
     * Update address
     */
    public function update(Request $request, $id)
    {
        $address = ShippingAddress::where('user_id', Auth::id())
            ->findOrFail($id);

        $validated = $request->validate([
            'label' => 'nullable|string|max:50',
            'recipient_name' => 'required|string|max:100',
            'recipient_phone' => 'required|string|max:20',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'state_region' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'required|string|max:100',
            'delivery_instructions' => 'nullable|string|max:500',
            'is_default' => 'boolean'
        ]);

        $address->update($validated);

        return redirect()->route('buyer.addresses.index')
            ->with('success', 'Shipping address updated successfully!');
    }

    /**
     * Delete address
     */
    public function destroy($id)
    {
        $address = ShippingAddress::where('user_id', Auth::id())
            ->findOrFail($id);

        // Prevent deleting if it's the only address
        if (Auth::user()->shippingAddresses()->count() === 1) {
            return back()->with('error', 'You must have at least one shipping address.');
        }

        $address->delete();

        return redirect()->route('buyer.addresses.index')
            ->with('success', 'Shipping address deleted successfully!');
    }

    /**
     * Set as default
     */
    public function setDefault($id)
    {
        $address = ShippingAddress::where('user_id', Auth::id())
            ->findOrFail($id);

        $address->setAsDefault();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Default address updated'
            ]);
        }

        return back()->with('success', 'Default address updated!');
    }
}