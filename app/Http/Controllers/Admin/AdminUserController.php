<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VendorProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    /**
     * Display all users
     */
    public function index(Request $request)
    {
        $query = User::with('vendorProfile');
        
        // Search filter
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        // Role filter
        if ($request->has('role') && $request->role) {
            $query->where('role', $request->role);
        }
        
        // Status filter
        if ($request->has('status') && $request->status) {
            $query->where('is_active', $request->status == 'active');
        }
        
        $users = $query->orderBy('created_at', 'desc')->paginate(20);
        
        $stats = [
            'total' => User::count(),
            'vendors' => User::whereIn('role', ['vendor_local', 'vendor_international'])->count(),
            'buyers' => User::where('role', 'buyer')->count(),
            'staff' => User::whereIn('role', ['admin', 'logistics', 'finance', 'ceo'])->count(),
        ];
        
        return view('admin.users.index', compact('users', 'stats'));
    }

    /**
     * Show user creation form
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store new user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'phone' => 'required|string|max:20',
            'role' => 'required|in:admin,buyer,vendor_local,vendor_international,logistics,finance,ceo',
            'password' => 'required|string|min:8|confirmed',
            'is_active' => 'boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $validated['is_active'] ?? true;

        $user = User::create($validated);

        // If vendor, create vendor profile
        if (in_array($validated['role'], ['vendor_local', 'vendor_international'])) {
            VendorProfile::create([
                'user_id' => $user->id,
                'vendor_type' => $validated['role'] == 'vendor_international' ? 'china_supplier' : 'local_retail',
                'business_name' => $user->name . "'s Store",
                'country' => 'Uganda',
                'city' => 'Kampala',
                'address' => 'To be updated',
                'vetting_status' => 'approved',
            ]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Show user details
     */
    public function show(User $user)
    {
        $user->load(['vendorProfile', 'vendorProfile.listings', 'disputes']);
        
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show user edit form
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update user
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'required|string|max:20',
            'role' => 'required|in:admin,buyer,vendor_local,vendor_international,logistics,finance,ceo',
            'password' => 'nullable|string|min:8|confirmed',
            'is_active' => 'boolean',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        // Handle vendor profile if role changed
        $isVendor = in_array($validated['role'], ['vendor_local', 'vendor_international']);
        $hasProfile = $user->vendorProfile()->exists();
        
        if ($isVendor && !$hasProfile) {
            VendorProfile::create([
                'user_id' => $user->id,
                'vendor_type' => $validated['role'] == 'vendor_international' ? 'china_supplier' : 'local_retail',
                'business_name' => $user->name . "'s Store",
                'country' => 'Uganda',
                'city' => 'Kampala',
                'address' => 'To be updated',
                'vetting_status' => 'approved',
            ]);
        } elseif (!$isVendor && $hasProfile) {
            $user->vendorProfile()->delete();
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Toggle user status
     */
    public function toggleStatus(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);
        
        $status = $user->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "User {$status} successfully.");
    }

    /**
     * Verify user email
     */
      public function verifyEmail(User $user)
    {
        try {
            $user->update(['email_verified_at' => now()]);

            return back()->with('success', 'Email verified successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to verify email.');
        }
    }

    /**
     * Toggle user admin verified status (blue tick badge)
     */
    public function toggleVerified(User $user)
    {
        $user->update([
            'is_admin_verified' => !$user->is_admin_verified,
            'admin_verified_at' => !$user->is_admin_verified ? now() : null,
        ]);

        $status = $user->is_admin_verified ? 'verified' : 'unverified';
        return back()->with('success', "User marked as {$status}.");
    }

    /**
     * Reset user password
     */
    public function resetPassword(Request $request, User $user)
    {
        $request->validate([
            'new_password' => 'nullable|string|min:6',
        ]);

        // Generate a random password if not provided
        $plainPassword = $request->new_password ?: \Illuminate\Support\Str::random(10);

        $user->update([
            'password' => Hash::make($plainPassword),
        ]);

        return back()->with('success', "Password reset successfully. New password: {$plainPassword}")
                     ->with('new_password', $plainPassword);
    }

    /**
     * Delete user
     */
    public function destroy(User $user)
    {
        // Prevent deleting self
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        // Check if user has orders or other dependencies
        if ($user->vendorProfile && $user->vendorProfile->listings()->count() > 0) {
            return back()->with('error', 'Cannot delete vendor with active listings.');
        }

        // Delete vendor profile if exists
        $user->vendorProfile()->delete();
        
        // Delete user
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }
}