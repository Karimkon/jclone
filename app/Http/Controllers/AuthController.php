<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\VendorProfile;
use App\Models\BuyerWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /**
     * Show buyer login form
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Show vendor login form
     */
    public function showVendorLogin()
    {
        return view('auth.login');
    }

    /**
     * Show admin login form
     */
    public function showAdminLogin()
    {
        return view('auth.login');
    }

    /**
     * Show logistics login form
     */
    public function showLogisticsLogin()
    {
        return view('auth.login');
    }

    /**
     * Show finance login form
     */
    public function showFinanceLogin()
    {
        return view('auth.login');
    }

    /**
     * Show CEO login form
     */
    public function showCEOLogin()
    {
        return view('auth.ceo-login');
    }

     /**
     * Handle user registration
     */
     public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20|unique:users',
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => 'nullable|in:buyer,vendor_local,vendor_international',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $role = $request->role ?? 'buyer';
        
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => $role,
        ]);

        // If vendor registration, create vendor profile
        if (in_array($role, ['vendor_local', 'vendor_international'])) {
            VendorProfile::create([
                'user_id' => $user->id,
                'vendor_type' => $role === 'vendor_international' ? 'china_supplier' : 'local_retail',
                'business_name' => $request->business_name ?? $request->name . "'s Store",
                'country' => $request->country ?? 'Uganda',
                'city' => $request->city ?? 'Kampala',
                'vetting_status' => 'pending',
            ]);
        } 
        // If buyer registration, create wallet automatically
        elseif ($role === 'buyer') {
            BuyerWallet::create([
                'user_id' => $user->id,
                'balance' => 0,
                'locked_balance' => 0,
                'currency' => 'USD',
                'meta' => [
                    'created_at' => now()->toDateTimeString(),
                    'initial_balance' => 0,
                ]
            ]);
        }

        Auth::login($user);

        return $this->redirectBasedOnRole($user);
    }

    /**
     * Handle buyer login
     */
    public function buyerLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            $user = Auth::user();
            
            // Redirect based on role
            return $this->redirectBasedOnRole($user);
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Handle admin login
     */
    public function adminLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            
            // Check if user is admin
            if (!$user->isAdmin() && $user->role !== 'ceo') {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Access denied. Admin privileges required.',
                ])->onlyInput('email');
            }

            $request->session()->regenerate();
            return redirect()->route('admin.dashboard');
        }

        return back()->withErrors([
            'email' => 'Invalid credentials.',
        ])->onlyInput('email');
    }

    /**
     * Handle vendor login
     */
    public function vendorLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            
            // Check if user is a vendor
            if (!$user->isVendor()) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Access denied. Vendor account required.',
                ])->onlyInput('email');
            }

            // Check if vendor profile exists and is approved
            if ($user->vendorProfile) {
                if ($user->vendorProfile->vetting_status !== 'approved') {
                    Auth::logout();
                    return back()->withErrors([
                        'email' => 'Vendor account is pending approval.',
                    ])->onlyInput('email');
                }
            } else {
                // No vendor profile, redirect to onboarding
                $request->session()->regenerate();
                return redirect()->route('vendor.onboard.create');
            }

            $request->session()->regenerate();
            return redirect()->route('vendor.dashboard');
        }

        return back()->withErrors([
            'email' => 'Invalid credentials.',
        ])->onlyInput('email');
    }

    /**
     * Handle logistics login
     */
    public function logisticsLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            
            // Check if user is logistics
            if ($user->role !== 'logistics' && !$user->isAdmin()) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Access denied. Logistics privileges required.',
                ])->onlyInput('email');
            }

            $request->session()->regenerate();
            return redirect()->route('logistics.dashboard');
        }

        return back()->withErrors([
            'email' => 'Invalid credentials.',
        ])->onlyInput('email');
    }

    /**
     * Handle finance login
     */
    public function financeLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            
            // Check if user is finance
            if ($user->role !== 'finance' && !$user->isAdmin()) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Access denied. Finance privileges required.',
                ])->onlyInput('email');
            }

            $request->session()->regenerate();
            return redirect()->route('finance.dashboard');
        }

        return back()->withErrors([
            'email' => 'Invalid credentials.',
        ])->onlyInput('email');
    }

    /**
     * Handle CEO login
     */
    public function ceoLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            
            // Check if user is CEO or admin
            if ($user->role !== 'ceo' && !$user->isAdmin()) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Access denied. CEO privileges required.',
                ])->onlyInput('email');
            }

            $request->session()->regenerate();
            return redirect()->route('ceo.dashboard');
        }

        return back()->withErrors([
            'email' => 'Invalid credentials.',
        ])->onlyInput('email');
    }

    /**
     * Show registration form
     */
    public function showRegister()
    {
        return view('auth.register');
    }

   
    

    /**
     * Redirect user based on role
     */
    private function redirectBasedOnRole($user)
    {
        switch ($user->role) {
            case 'admin':
                return redirect()->route('admin.dashboard');
            case 'vendor_local':
            case 'vendor_international':
                if ($user->vendorProfile && $user->vendorProfile->vetting_status === 'approved') {
                    return redirect()->route('vendor.dashboard');
                } else {
                    return redirect()->route('vendor.onboard.create');
                }
            case 'logistics':
                return redirect()->route('logistics.dashboard');
            case 'finance':
                return redirect()->route('finance.dashboard');
            case 'ceo':
                return redirect()->route('ceo.dashboard');
            default: // buyer
                return redirect()->intended('/');
        }
    }

    /**
     * Show forgot password form
     */
    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle forgot password request
     */
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with(['status' => __($status)])
            : back()->withErrors(['email' => __($status)]);
    }

    /**
     * Show reset password form
     */
    public function showResetPassword($token)
    {
        return view('auth.reset-password', ['token' => $token]);
    }

    /**
     * Handle reset password
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->save();
            }
        );

        return $status == Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }
}