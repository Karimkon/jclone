<?php

namespace App\Policies;

use App\Models\CallbackRequest;
use App\Models\User;

class CallbackRequestPolicy
{
    /**
     * Determine if the user can view the callback request.
     */
    public function view(User $user, CallbackRequest $callback): bool
    {
        // Only the vendor who owns the listing can view
        return $user->vendorProfile && 
               $user->vendorProfile->id === $callback->vendor_profile_id;
    }

    /**
     * Determine if the user can update the callback request.
     */
    public function update(User $user, CallbackRequest $callback): bool
    {
        return $user->vendorProfile && 
               $user->vendorProfile->id === $callback->vendor_profile_id;
    }
}