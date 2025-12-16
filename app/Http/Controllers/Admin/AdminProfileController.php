<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AdminProfileController extends Controller
{
    /**
     * Display admin profile
     */
    public function index()
    {
        $user = Auth::user();
        $activities = \App\Models\AdminActivity::where('admin_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        return view('admin.profile.index', compact('user', 'activities'));
    }

    /**
     * Update admin profile
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:500',
            'location' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        $user->update($validator->validated());
        
        // Log activity
        $this->logActivity('Profile updated', $user);
        
        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'user' => $user->fresh()
        ]);
    }

    /**
     * Change password
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
            'new_password_confirmation' => 'required'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        $user = Auth::user();
        
        // Check current password
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect'
            ], 422);
        }
        
        // Update password
        $user->update([
            'password' => Hash::make($request->new_password)
        ]);
        
        // Log activity
        $this->logActivity('Password changed', $user);
        
        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully'
        ]);
    }

    /**
     * Upload profile photo
     */
    public function uploadPhoto(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        $user = Auth::user();
        
        // Delete old photo if exists
        if ($user->profile_photo) {
            Storage::delete($user->profile_photo);
        }
        
        // Upload new photo
        $path = $request->file('photo')->store('profile-photos', 'public');
        
        $user->update([
            'profile_photo' => $path
        ]);
        
        // Log activity
        $this->logActivity('Profile photo updated', $user);
        
        return response()->json([
            'success' => true,
            'message' => 'Profile photo updated successfully',
            'photo_url' => asset('storage/' . $path)
        ]);
    }

    /**
     * Remove profile photo
     */
    public function removePhoto(Request $request)
    {
        $user = Auth::user();
        
        if ($user->profile_photo) {
            Storage::delete($user->profile_photo);
            
            $user->update([
                'profile_photo' => null
            ]);
            
            // Log activity
            $this->logActivity('Profile photo removed', $user);
            
            return response()->json([
                'success' => true,
                'message' => 'Profile photo removed successfully'
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'No profile photo found'
        ], 404);
    }

    /**
     * Log admin activity
     */
    private function logActivity($action, $user)
    {
        \App\Models\AdminActivity::create([
            'admin_id' => $user->id,
            'action' => $action,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
    }
}