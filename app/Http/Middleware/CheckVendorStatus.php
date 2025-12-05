<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckVendorStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login to access vendor dashboard.');
        }

        $user = Auth::user();
        
        // Check if user has a vendor profile
        if (!$user->vendorProfile) {
            return redirect()->route('vendor.onboard.create')
                ->with('info', 'Please complete vendor onboarding to access vendor features.');
        }

        $vendorProfile = $user->vendorProfile;
        
        // Check vendor status
        if ($vendorProfile->vetting_status === 'pending') {
            return redirect()->route('vendor.onboard.status')
                ->with('info', 'Your vendor application is pending review. Please wait for approval.');
        }

        if ($vendorProfile->vetting_status === 'rejected') {
            return redirect()->route('vendor.onboard.status')
                ->with('error', 'Your vendor application was rejected. Please contact support.');
        }

        if ($vendorProfile->vetting_status === 'approved') {
            // Vendor is approved, allow access
            return $next($request);
        }

        // Unknown status
        return redirect()->route('vendor.onboard.status')
            ->with('error', 'Unable to determine your vendor status. Please contact support.');
    }
}