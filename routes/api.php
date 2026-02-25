<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Listing;
use App\Models\ListingImage;
use App\Models\ListingVariant;
use App\Models\Category;
use App\Models\Cart;
use App\Models\Wishlist;
use App\Models\Order;
use App\Models\VendorProfile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Api\ListingApiController;
use App\Http\Controllers\Vendor\VendorDashboardController;
use App\Http\Controllers\Vendor\VendorListingController;
use App\Http\Controllers\Vendor\VendorOrderController;
use App\Http\Controllers\Vendor\VendorProfileController;

/*
|--------------------------------------------------------------------------
| API Routes for Flutter Mobile App
|--------------------------------------------------------------------------
| These routes use token-based authentication (Laravel Sanctum)
| All responses are JSON
*/

// ====================
// PUBLIC ROUTES (No Authentication Required)
// ====================

// Health check
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'BebaMart API is running',
        'timestamp' => now()->toISOString(),
    ]);
});

// Authentication (rate limited: 5 attempts per minute)
Route::middleware('throttle:5,1')->post('/login', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $user = User::with('vendorProfile')->where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }

    // Create a new token
    $token = $user->createToken('mobile-app')->plainTextToken;

    return response()->json([
        'success' => true,
        'message' => 'Login successful',
        'token' => $token,
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $user->role,
            'avatar' => $user->avatar ? asset('storage/' . $user->avatar) : null,
            'email_verified_at' => $user->email_verified_at,
            'is_admin_verified' => $user->is_admin_verified ?? false,
            'created_at' => $user->created_at,
            'vendor_profile' => $user->vendorProfile ? [
                'id' => $user->vendorProfile->id,
                'user_id' => $user->vendorProfile->user_id,
                'business_name' => $user->vendorProfile->business_name ?? $user->vendorProfile->store_name,
                'business_description' => $user->vendorProfile->business_description,
                'business_address' => $user->vendorProfile->address,
                'phone' => $user->vendorProfile->business_phone,
                'email' => $user->vendorProfile->email ?? $user->email,
                'logo' => $user->vendorProfile->logo,
                'banner' => $user->vendorProfile->banner,
                'vendor_type' => $user->vendorProfile->vendor_type,
                'vetting_status' => $user->vendorProfile->vetting_status,
                'country' => $user->vendorProfile->country,
                'city' => $user->vendorProfile->city,
                'rating' => $user->vendorProfile->rating ?? 0,
                'total_sales' => $user->vendorProfile->total_sales ?? 0,
                'created_at' => $user->vendorProfile->created_at?->toIso8601String(),
                'updated_at' => $user->vendorProfile->updated_at?->toIso8601String(),
            ] : null,
        ],
    ]);
});

Route::post('/register', function (Request $request) {
    try {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20|unique:users,phone',
            'role' => 'nullable|in:buyer,vendor_local,vendor_international',
        ], [
            'email.unique' => 'This email is already registered. Please sign in instead.',
            'phone.unique' => 'This phone number is already registered. Please sign in instead.',
        ]);

        // Create user with requested role
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone ?: null,
            'role' => $request->role ?? 'buyer',
            'is_verified' => false,
        ]);

        // Generate phone OTP
        $otp = $user->generatePhoneOtp();

        // Send OTP via SMS (only if phone provided)
        $smsSent = false;
        if (!empty($user->phone)) {
            try {
                $smsService = new \App\Services\EgoSmsService();
                $result = $smsService->sendOtp($user->phone, $otp);
                $smsSent = $smsService->isSuccess($result);

                if (!$smsSent) {
                    \Log::warning('SMS OTP send failed', ['phone' => $user->phone, 'result' => $result]);
                }
            } catch (\Exception $e) {
                \Log::error('Failed to send SMS OTP: ' . $e->getMessage());
            }
        }

        // Also send OTP email as backup
        try {
            \Mail::raw("Your BebaMart verification code is: $otp\n\nThis code expires in 10 minutes.", function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('BebaMart - Verification Code');
            });
        } catch (\Exception $e) {
            \Log::error('Failed to send OTP email: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => $smsSent
                ? 'Registration successful. Please verify with the OTP sent to your phone.'
                : 'Registration successful. Please verify with the OTP sent to your email.',
            'requires_verification' => true,
            'verification_type' => $smsSent ? 'sms' : 'email',
            'user_id' => $user->id,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $user->role,
        ], 201);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => $e->errors()[array_key_first($e->errors())][0] ?? 'Validation failed',
            'errors' => $e->errors(),
        ], 422);
    } catch (\Illuminate\Database\QueryException $e) {
        \Log::error('Registration database error: ' . $e->getMessage());
        // Check for duplicate entry
        if (str_contains($e->getMessage(), 'Duplicate entry')) {
            if (str_contains($e->getMessage(), 'email')) {
                return response()->json([
                    'success' => false,
                    'message' => 'This email is already registered. Please sign in instead.',
                ], 422);
            }
            if (str_contains($e->getMessage(), 'phone')) {
                return response()->json([
                    'success' => false,
                    'message' => 'This phone number is already registered. Please sign in instead.',
                ], 422);
            }
        }
        return response()->json([
            'success' => false,
            'message' => 'Registration failed. Please try again.',
        ], 500);
    } catch (\Exception $e) {
        \Log::error('Registration error: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Registration failed. Please try again.',
        ], 500);
    }
});

// Google Authentication
Route::post('/auth/google', function (Request $request) {
    try {
        $request->validate([
            'email' => 'required|email',
            'name' => 'required|string|max:255',
            'google_id' => 'required|string',
            'avatar' => 'nullable|string',
            'id_token' => 'nullable|string',
            'access_token' => 'nullable|string',
        ]);

        // Find existing user by google_id first, then by email
        $user = User::where('google_id', $request->google_id)->first();

        if (!$user) {
            $user = User::where('email', $request->email)->first();
        }

        if (!$user) {
            // Create new user with unique placeholder phone
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make(\Str::random(32)),
                'phone' => 'google_' . $request->google_id, // Unique placeholder
                'role' => 'buyer',
                'is_verified' => true,
                'email_verified_at' => now(),
                'google_id' => $request->google_id,
                'avatar' => $request->avatar,
            ]);
        } else {
            // Update Google ID and avatar if not set
            $updated = false;
            if (!$user->google_id) {
                $user->google_id = $request->google_id;
                $updated = true;
            }
            if ($request->avatar && !$user->avatar) {
                $user->avatar = $request->avatar;
                $updated = true;
            }
            if (!$user->email_verified_at) {
                $user->email_verified_at = now();
                $user->is_verified = true;
                $updated = true;
            }
            if ($updated) {
                $user->save();
            }
        }

        // Load vendor profile
        $user->load('vendorProfile');

        // Create token
        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Google sign-in successful',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'avatar' => $user->avatar ?? $request->avatar,
                'email_verified_at' => $user->email_verified_at,
                'created_at' => $user->created_at,
                'vendor_profile' => $user->vendorProfile ? [
                    'id' => $user->vendorProfile->id,
                    'user_id' => $user->vendorProfile->user_id,
                    'business_name' => $user->vendorProfile->business_name ?? $user->vendorProfile->store_name,
                    'business_description' => $user->vendorProfile->business_description,
                    'business_address' => $user->vendorProfile->address,
                    'phone' => $user->vendorProfile->business_phone,
                    'email' => $user->vendorProfile->email ?? $user->email,
                    'logo' => $user->vendorProfile->logo,
                    'banner' => $user->vendorProfile->banner,
                    'vendor_type' => $user->vendorProfile->vendor_type,
                    'vetting_status' => $user->vendorProfile->vetting_status,
                    'country' => $user->vendorProfile->country,
                    'city' => $user->vendorProfile->city,
                    'rating' => $user->vendorProfile->rating ?? 0,
                    'total_sales' => $user->vendorProfile->total_sales ?? 0,
                    'created_at' => $user->vendorProfile->created_at?->toIso8601String(),
                    'updated_at' => $user->vendorProfile->updated_at?->toIso8601String(),
                ] : null,
            ],
        ]);
    } catch (\Exception $e) {
        \Log::error('Google sign-in error: ' . $e->getMessage() . ' | ' . $e->getTraceAsString());
        return response()->json([
            'success' => false,
            'message' => 'Google sign-in failed. Please try again.',
        ], 500);
    }
});

// Apple Authentication
Route::post('/auth/apple', function (Request $request) {
    try {
        $request->validate([
            'email' => 'nullable|email',
            'name' => 'nullable|string|max:255',
            'apple_user_id' => 'required|string',
            'identity_token' => 'nullable|string',
            'authorization_code' => 'nullable|string',
        ]);

        // Find existing user by apple_user_id first, then by email
        $user = User::where('apple_user_id', $request->apple_user_id)->first();

        if (!$user && $request->email) {
            $user = User::where('email', $request->email)->first();
        }

        if (!$user) {
            // Apple may not provide email on subsequent sign-ins
            $email = $request->email;
            if (!$email) {
                // Generate a private relay-style placeholder
                $email = 'apple_' . $request->apple_user_id . '@privaterelay.bebamart.com';
            }

            $name = $request->name;
            if (!$name || trim($name) === '') {
                $name = 'Apple User';
            }

            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make(\Str::random(32)),
                'phone' => 'apple_' . substr($request->apple_user_id, 0, 20),
                'role' => 'buyer',
                'is_verified' => true,
                'email_verified_at' => now(),
                'apple_user_id' => $request->apple_user_id,
            ]);
        } else {
            // Update Apple user ID if not set
            $updated = false;
            if (!$user->apple_user_id) {
                $user->apple_user_id = $request->apple_user_id;
                $updated = true;
            }
            if ($request->name && trim($request->name) !== '' && $user->name === 'Apple User') {
                $user->name = $request->name;
                $updated = true;
            }
            if (!$user->email_verified_at) {
                $user->email_verified_at = now();
                $user->is_verified = true;
                $updated = true;
            }
            if ($updated) {
                $user->save();
            }
        }

        // Load vendor profile
        $user->load('vendorProfile');

        // Create token
        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Apple sign-in successful',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'avatar' => $user->avatar,
                'email_verified_at' => $user->email_verified_at,
                'created_at' => $user->created_at,
                'vendor_profile' => $user->vendorProfile ? [
                    'id' => $user->vendorProfile->id,
                    'user_id' => $user->vendorProfile->user_id,
                    'business_name' => $user->vendorProfile->business_name ?? $user->vendorProfile->store_name,
                    'business_description' => $user->vendorProfile->business_description,
                    'business_address' => $user->vendorProfile->address,
                    'phone' => $user->vendorProfile->business_phone,
                    'email' => $user->vendorProfile->email ?? $user->email,
                    'logo' => $user->vendorProfile->logo,
                    'banner' => $user->vendorProfile->banner,
                    'vendor_type' => $user->vendorProfile->vendor_type,
                    'vetting_status' => $user->vendorProfile->vetting_status,
                    'country' => $user->vendorProfile->country,
                    'city' => $user->vendorProfile->city,
                    'rating' => $user->vendorProfile->rating ?? 0,
                    'total_sales' => $user->vendorProfile->total_sales ?? 0,
                    'created_at' => $user->vendorProfile->created_at?->toIso8601String(),
                    'updated_at' => $user->vendorProfile->updated_at?->toIso8601String(),
                ] : null,
            ],
        ]);
    } catch (\Exception $e) {
        \Log::error('Apple sign-in error: ' . $e->getMessage() . ' | ' . $e->getTraceAsString());
        return response()->json([
            'success' => false,
            'message' => 'Apple sign-in failed. Please try again.',
        ], 500);
    }
});

// Verify OTP (supports both email and phone verification)
Route::post('/verify-otp', function (Request $request) {
    $request->validate([
        'email' => 'required_without:phone|email',
        'phone' => 'required_without:email|string',
        'otp' => 'required|string|size:6',
    ]);

    // Find user by email or phone
    $user = null;
    if ($request->email) {
        $user = User::where('email', $request->email)->first();
    } elseif ($request->phone) {
        $user = User::where('phone', $request->phone)->first();
    }

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'User not found',
        ], 404);
    }

    if ($user->is_verified && $user->phone_verified) {
        return response()->json([
            'success' => false,
            'message' => 'Account already verified',
        ], 400);
    }

    // Try phone OTP first (primary verification method)
    if ($user->phone_otp_code) {
        if ($user->isPhoneOtpExpired()) {
            return response()->json([
                'success' => false,
                'message' => 'OTP has expired. Please request a new one.',
                'expired' => true,
            ], 400);
        }

        if (!$user->verifyPhoneOtp($request->otp)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP code',
            ], 400);
        }
    }
    // Fallback to email OTP
    elseif ($user->otp_code) {
        if ($user->isOtpExpired()) {
            return response()->json([
                'success' => false,
                'message' => 'OTP has expired. Please request a new one.',
                'expired' => true,
            ], 400);
        }

        if (!$user->verifyOtp($request->otp)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP code',
            ], 400);
        }
    } else {
        return response()->json([
            'success' => false,
            'message' => 'No OTP found. Please request a new one.',
        ], 400);
    }

    // Load vendor profile
    $user->load('vendorProfile');

    // Create auth token
    $token = $user->createToken('mobile-app')->plainTextToken;

    return response()->json([
        'success' => true,
        'message' => 'Account verified successfully',
        'token' => $token,
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $user->role,
            'avatar' => $user->avatar ? asset('storage/' . $user->avatar) : null,
            'is_verified' => $user->is_verified,
            'phone_verified' => $user->phone_verified,
            'created_at' => $user->created_at,
            'vendor_profile' => $user->vendorProfile ? [
                'id' => $user->vendorProfile->id,
                'user_id' => $user->vendorProfile->user_id,
                'business_name' => $user->vendorProfile->business_name,
                'vetting_status' => $user->vendorProfile->vetting_status,
            ] : null,
        ],
    ]);
});

// Resend OTP (supports SMS and email)
Route::post('/resend-otp', function (Request $request) {
    $request->validate([
        'email' => 'required_without:phone|email',
        'phone' => 'required_without:email|string',
        'type' => 'nullable|in:sms,email',
    ]);

    // Find user by email or phone
    $user = null;
    if ($request->email) {
        $user = User::where('email', $request->email)->first();
    } elseif ($request->phone) {
        $user = User::where('phone', $request->phone)->first();
    }

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'User not found',
        ], 404);
    }

    if ($user->is_verified && $user->phone_verified) {
        return response()->json([
            'success' => false,
            'message' => 'Account already verified',
        ], 400);
    }

    // Generate new phone OTP
    $otp = $user->generatePhoneOtp();

    // Determine verification type
    $sendViaSms = $request->type === 'sms' || (!$request->type && $user->phone);
    $smsSent = false;

    // Send OTP via SMS if requested
    if ($sendViaSms && $user->phone) {
        try {
            $smsService = new \App\Services\EgoSmsService();
            $result = $smsService->sendOtp($user->phone, $otp);
            $smsSent = $smsService->isSuccess($result);
        } catch (\Exception $e) {
            \Log::error('Failed to send SMS OTP: ' . $e->getMessage());
        }
    }

    // Send OTP email (as primary if SMS not requested, or as backup)
    $emailSent = false;
    try {
        \Mail::raw("Your BebaMart verification code is: $otp\n\nThis code expires in 10 minutes.", function ($message) use ($user) {
            $message->to($user->email)
                    ->subject('BebaMart - Verification Code');
        });
        $emailSent = true;
    } catch (\Exception $e) {
        \Log::error('Failed to send OTP email: ' . $e->getMessage());
    }

    if ($smsSent || $emailSent) {
        $message = $smsSent
            ? 'OTP sent to your phone via SMS'
            : 'OTP sent to your email';

        return response()->json([
            'success' => true,
            'message' => $message,
            'verification_type' => $smsSent ? 'sms' : 'email',
        ]);
    }

    return response()->json([
        'success' => false,
        'message' => 'Failed to send OTP. Please try again.',
    ], 500);
});

// NOTE: Duplicate /auth/google route removed - using the simpler version above

// Complete Google sign-in with phone number (for new users)
Route::post('/auth/google/complete', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'phone' => 'required|string|max:20',
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'User not found',
        ], 404);
    }

    if (!$user->google_id) {
        return response()->json([
            'success' => false,
            'message' => 'This endpoint is only for Google sign-in users',
        ], 400);
    }

    // Update phone number
    $user->phone = $request->phone;
    $user->save();

    // Generate and send OTP
    $otp = $user->generatePhoneOtp();

    $smsSent = false;
    try {
        $smsService = new \App\Services\EgoSmsService();
        $result = $smsService->sendOtp($user->phone, $otp);
        $smsSent = $smsService->isSuccess($result);
    } catch (\Exception $e) {
        \Log::error('Failed to send SMS OTP: ' . $e->getMessage());
    }

    // Send email backup
    try {
        \Mail::raw("Your BebaMart verification code is: $otp\n\nThis code expires in 10 minutes.", function ($message) use ($user) {
            $message->to($user->email)
                    ->subject('BebaMart - Verification Code');
        });
    } catch (\Exception $e) {
        \Log::error('Failed to send OTP email: ' . $e->getMessage());
    }

    return response()->json([
        'success' => true,
        'message' => $smsSent
            ? 'OTP sent to your phone. Please verify to complete registration.'
            : 'OTP sent to your email. Please verify to complete registration.',
        'requires_verification' => true,
        'verification_type' => $smsSent ? 'sms' : 'email',
        'email' => $user->email,
        'phone' => $user->phone,
    ]);
});

// Categories for mobile - ONLY parent categories (parent_id IS NULL) with children nested
Route::get('/categories', function () {
    try {
        // IMPORTANT: Only fetch categories where parent_id IS NULL (top-level/main categories)
        $categories = Category::where('is_active', true)
            ->whereNull('parent_id')  // ONLY parent categories
            ->with(['children' => function($query) {
                $query->where('is_active', true)
                    ->orderBy('order')
                    ->with(['children' => function($q) {
                        $q->where('is_active', true)->orderBy('order');
                    }]);
            }])
            ->orderBy('order')
            ->get()
            ->map(function ($category) {
                $listingsCount = $category->total_listings_count;
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                    'icon' => $category->icon ?? 'category',
                    'parent_id' => null,  // Always null for parent categories
                    'is_parent' => true,  // Explicit flag
                    'listings_count' => $listingsCount,
                    'meta' => $category->meta,
                    'children' => $category->children->map(function ($child) {
                        return [
                            'id' => $child->id,
                            'name' => $child->name,
                            'slug' => $child->slug,
                            'description' => $child->description,
                            'icon' => $child->icon ?? 'category',
                            'parent_id' => $child->parent_id,
                            'is_parent' => $child->children->isNotEmpty(),
                            'listings_count' => $child->total_listings_count,
                            'meta' => $child->meta,
                            'children' => $child->children->map(function ($grandchild) {
                                return [
                                    'id' => $grandchild->id,
                                    'name' => $grandchild->name,
                                    'slug' => $grandchild->slug,
                                    'description' => $grandchild->description,
                                    'icon' => $grandchild->icon ?? 'category',
                                    'parent_id' => $grandchild->parent_id,
                                    'is_parent' => false,
                                    'listings_count' => $grandchild->total_listings_count,
                                    'meta' => $grandchild->meta,
                                ];
                            })->values()->toArray(),
                        ];
                    })->values()->toArray(),
                ];
            })
            ->sortByDesc('listings_count')
            ->values();

        return response()->json([
            'success' => true,
            'data' => $categories,
            'count' => $categories->count(),
            'message' => 'Parent categories only (with children nested)',
        ]);
    } catch (\Exception $e) {
        \Log::error('Categories API error: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Failed to load categories. Please try again.',
            'data' => []
        ], 500);
    }
});

// Category Attributes (with parent inheritance)
Route::get('/categories/{id}/attributes', function ($id) {
    $category = Category::find($id);
    if (!$category) {
        return response()->json(['success' => false, 'message' => 'Category not found'], 404);
    }

    // Walk up the hierarchy to find attribute_fields
    $current = $category;
    $fields = null;
    while ($current) {
        $meta = $current->meta;
        if (!empty($meta['attribute_fields'])) {
            $fields = $meta['attribute_fields'];
            break;
        }
        $current = $current->parent_id ? Category::find($current->parent_id) : null;
    }

    return response()->json([
        'success' => true,
        'category_id' => (int) $id,
        'attribute_fields' => $fields ?? [],
    ]);
});

// Subscription Plans (Public)
Route::get('/subscription-plans', [\App\Http\Controllers\Marketplace\SubscriptionController::class, 'plans']);

// Category with listings - WITH SUBSCRIPTION RANKING
Route::get('/categories/{slug}', function ($slug) {
    try {
        // Use ranking service for subscription-boosted sorting
        $rankingService = app(\App\Services\ListingRankingService::class);

        $category = Category::where('slug', $slug)
            ->where('is_active', true)
            ->with(['children' => function($query) {
                $query->where('is_active', true);
            }])
            ->first();

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
            ], 404);
        }

        $categoryIds = [$category->id];
        if ($category->children->isNotEmpty()) {
            $categoryIds = array_merge($categoryIds, $category->children->pluck('id')->toArray());
        }

        // Build query
        $query = Listing::with(['images', 'category', 'user.vendorProfile'])
            ->whereIn('category_id', $categoryIds)
            ->where('is_active', true)
            ->whereHas('user', fn($q) => $q->where('is_active', true));

        // Try to apply ranking, fall back to simple sort if it fails
        try {
            $rankedListings = $rankingService->getRankedListings($query, 100);
        } catch (\Exception $e) {
            // Ranking failed, use simple sort
            $rankedListings = $query->orderBy('created_at', 'desc')->limit(100)->get();
        }

        $listings = $rankedListings->map(function ($listing) {
                return [
                    'id' => $listing->id,
                    'vendor_profile_id' => $listing->vendor_profile_id ?? 1,
                    'title' => $listing->title,
                    'description' => $listing->description,
                    'price' => $listing->price,
                    'thumbnail' => $listing->images && $listing->images->isNotEmpty()
                        ? $listing->images->first()->path
                        : null,
                    'images' => $listing->images ? $listing->images->map(function($image) {
                        return [
                            'id' => $image->id,
                            'path' => $image->path,
                            'listing_id' => $image->listing_id,
                            'sort_order' => $image->sort_order ?? 0,
                        ];
                    })->toArray() : [],
                    'vendor' => $listing->user && $listing->user->vendorProfile ? [
                        'id' => $listing->user->vendorProfile->id,
                        'business_name' => $listing->user->vendorProfile->business_name ?? $listing->user->name,
                        'created_at' => $listing->user->vendorProfile->created_at?->toIso8601String(),
                        'is_verified' => $listing->user->is_admin_verified ?? false,
                        'subscription' => [
                            'plan_name' => method_exists($listing->user->vendorProfile, 'getSubscriptionPlanNameAttribute')
                                ? ($listing->user->vendorProfile->subscription_plan_name ?? 'Free')
                                : 'Free',
                            'badge_text' => method_exists($listing->user->vendorProfile, 'getSubscriptionBadge')
                                ? $listing->user->vendorProfile->getSubscriptionBadge()
                                : null,
                            'has_paid_subscription' => method_exists($listing->user->vendorProfile, 'hasPaidSubscription')
                                ? $listing->user->vendorProfile->hasPaidSubscription()
                                : false,
                        ],
                    ] : null,
                    'category' => $listing->category ? [
                        'id' => $listing->category->id,
                        'name' => $listing->category->name,
                        'slug' => $listing->category->slug,
                    ] : null,
                    'average_rating' => $listing->average_rating ?? 0,
                    'reviews_count' => $listing->reviews_count ?? 0,
                    'stock' => $listing->stock,
                    'is_active' => $listing->is_active,
                    'ranking_score' => $listing->ranking_score ?? null,
                    'boost_multiplier' => $listing->boost_multiplier ?? 1.0,
                ];
            })->values();
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'icon' => $category->icon ?? 'category',
                'parent_id' => $category->parent_id,
                'listings_count' => $category->total_listings_count,
                'children' => $category->children->map(function ($child) {
                    return [
                        'id' => $child->id,
                        'name' => $child->name,
                        'slug' => $child->slug,
                        'description' => $child->description,
                        'icon' => $child->icon ?? 'category',
                        'parent_id' => $child->parent_id,
                        'listings_count' => $child->total_listings_count,
                    ];
                })->values()->toArray(),
                'listings' => $listings,
            ],
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
        ], 500);
    }
});

// Jobs & Services (Public)
Route::get('/service-categories', function (Request $request) {
    $type = $request->get('type'); // job, service
    $query = \App\Models\ServiceCategory::active()->parents();
    
    if ($type === 'job') {
        $query->forJobs();
    } elseif ($type === 'service') {
        $query->forServices();
    }
    
    $categories = $query->with(['children' => function($q) use ($type) {
        $q->active();
        if ($type === 'job') $q->forJobs();
        if ($type === 'service') $q->forServices();
    }])->orderBy('sort_order')->get();
    
    return response()->json([
        'success' => true,
        'data' => $categories->map(function($cat) {
            return [
                'id' => $cat->id,
                'name' => $cat->name,
                'slug' => $cat->slug,
                'type' => $cat->type,
                'icon' => $cat->icon,
                'children' => $cat->children->map(function($child) {
                    return [
                        'id' => $child->id,
                        'name' => $child->name,
                        'slug' => $child->slug,
                        'type' => $child->type,
                        'icon' => $child->icon,
                    ];
                })
            ];
        })
    ]);
});

// Services with subscription-based ranking (FAIL-SAFE)
Route::get('/marketplace/services', function (Request $request) {
    try {
        $services = \App\Models\VendorService::active()
            ->with(['vendor', 'category'])
            ->whereHas('vendor', fn($q) => $q->where('vetting_status', 'approved'))
            ->get()
            ->map(function($service) {
                // Calculate boost from vendor subscription (safely)
                $boostMultiplier = 1.0;
                try {
                    if ($service->vendor && method_exists($service->vendor, 'getBoostMultiplier')) {
                        $boostMultiplier = $service->vendor->getBoostMultiplier();
                    }
                } catch (\Exception $e) {
                    $boostMultiplier = 1.0;
                }
                $service->boost_multiplier = $boostMultiplier;
                $service->is_boosted = $boostMultiplier > 1.0;
                return $service;
            });

        // Separate boosted and free services
        $boosted = $services->filter(fn($s) => $s->is_boosted)->sortByDesc('boost_multiplier');
        $free = $services->filter(fn($s) => !$s->is_boosted)->sortByDesc('created_at');

        // Interleave with fair exposure (30% for free vendors)
        $result = collect();
        $bIdx = 0; $fIdx = 0;
        $bCount = $boosted->count(); $fCount = $free->count();
        $total = $bCount + $fCount;
        $freeInterval = 3; // Every 3rd slot for free vendors

        for ($i = 0; $i < $total; $i++) {
            if ($i > 0 && $i % $freeInterval === 0 && $fIdx < $fCount) {
                $result->push($free->values()[$fIdx++]);
            } elseif ($bIdx < $bCount) {
                $result->push($boosted->values()[$bIdx++]);
            } elseif ($fIdx < $fCount) {
                $result->push($free->values()[$fIdx++]);
            }
        }

        // Paginate
        $perPage = $request->get('per_page', 10);
        $page = $request->get('page', 1);
        $paginated = $result->forPage($page, $perPage);

        return response()->json([
            'success' => true,
            'data' => $paginated->map(function($service) {
                return [
                    'id' => $service->id,
                    'title' => $service->title,
                    'slug' => $service->slug,
                    'price' => $service->price,
                    'image' => $service->images && count($service->images) > 0 ? $service->images[0] : null,
                    'description' => $service->description,
                    'location' => $service->location,
                    'city' => $service->city,
                    'vendor' => [
                        'id' => $service->vendor->id,
                        'business_name' => $service->vendor->business_name,
                        'subscription' => [
                            'plan_name' => method_exists($service->vendor, 'getSubscriptionPlanNameAttribute')
                                ? ($service->vendor->subscription_plan_name ?? 'Free')
                                : 'Free',
                            'badge_text' => method_exists($service->vendor, 'getSubscriptionBadge')
                                ? $service->vendor->getSubscriptionBadge()
                                : null,
                            'has_paid_subscription' => method_exists($service->vendor, 'hasPaidSubscription')
                                ? $service->vendor->hasPaidSubscription()
                                : false,
                        ],
                    ],
                    'category' => $service->category ? [
                        'id' => $service->category->id,
                        'name' => $service->category->name,
                        'slug' => $service->category->slug,
                    ] : null,
                    'boost_multiplier' => $service->boost_multiplier ?? 1.0,
                    'is_promoted' => $service->is_boosted ?? false,
                ];
            })->values(),
            'meta' => [
                'current_page' => (int)$page,
                'last_page' => (int)ceil($result->count() / $perPage),
                'per_page' => (int)$perPage,
                'total' => $result->count(),
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => 'An unexpected error occurred. Please try again.'], 500);
    }
});

// Jobs with subscription-based ranking (FAIL-SAFE)
Route::get('/marketplace/jobs', function (Request $request) {
    try {
        $jobs = \App\Models\JobListing::active()->notExpired()
            ->with(['vendor', 'category'])
            ->get()
            ->map(function($job) {
                // Calculate boost from vendor subscription (safely)
                $boostMultiplier = 1.0;
                try {
                    if ($job->vendor && method_exists($job->vendor, 'getBoostMultiplier')) {
                        $boostMultiplier = $job->vendor->getBoostMultiplier();
                    }
                } catch (\Exception $e) {
                    $boostMultiplier = 1.0;
                }
                $job->boost_multiplier = $boostMultiplier;
                $job->is_boosted = $boostMultiplier > 1.0;
                return $job;
            });

        // Separate boosted and free jobs
        $boosted = $jobs->filter(fn($j) => $j->is_boosted)->sortByDesc('boost_multiplier');
        $free = $jobs->filter(fn($j) => !$j->is_boosted)->sortByDesc('created_at');

        // Interleave with fair exposure (30% for free vendors)
        $result = collect();
        $bIdx = 0; $fIdx = 0;
        $bCount = $boosted->count(); $fCount = $free->count();
        $total = $bCount + $fCount;
        $freeInterval = 3;

        for ($i = 0; $i < $total; $i++) {
            if ($i > 0 && $i % $freeInterval === 0 && $fIdx < $fCount) {
                $result->push($free->values()[$fIdx++]);
            } elseif ($bIdx < $bCount) {
                $result->push($boosted->values()[$bIdx++]);
            } elseif ($fIdx < $fCount) {
                $result->push($free->values()[$fIdx++]);
            }
        }

        // Paginate
        $perPage = $request->get('per_page', 10);
        $page = $request->get('page', 1);
        $paginated = $result->forPage($page, $perPage);

        return response()->json([
            'success' => true,
            'data' => $paginated->map(function($job) {
                return [
                    'id' => $job->id,
                    'title' => $job->title,
                    'slug' => $job->slug,
                    'company_name' => $job->company_name ?? $job->vendor->business_name,
                    'job_type' => $job->job_type,
                    'salary_min' => $job->salary_min,
                    'salary_max' => $job->salary_max,
                    'requirements' => $job->requirements,
                    'city' => $job->city,
                    'description' => $job->description,
                    'location' => $job->location,
                    'vendor' => [
                        'id' => $job->vendor->id,
                        'business_name' => $job->vendor->business_name,
                        'subscription' => [
                            'plan_name' => method_exists($job->vendor, 'getSubscriptionPlanNameAttribute')
                                ? ($job->vendor->subscription_plan_name ?? 'Free')
                                : 'Free',
                            'badge_text' => method_exists($job->vendor, 'getSubscriptionBadge')
                                ? $job->vendor->getSubscriptionBadge()
                                : null,
                            'has_paid_subscription' => method_exists($job->vendor, 'hasPaidSubscription')
                                ? $job->vendor->hasPaidSubscription()
                                : false,
                        ],
                    ],
                    'category' => $job->category ? [
                        'id' => $job->category->id,
                        'name' => $job->category->name,
                        'slug' => $job->category->slug,
                    ] : null,
                    'boost_multiplier' => $job->boost_multiplier ?? 1.0,
                    'is_promoted' => $job->is_boosted ?? false,
                ];
            })->values(),
            'meta' => [
                'current_page' => (int)$page,
                'last_page' => (int)ceil($result->count() / $perPage),
                'per_page' => (int)$perPage,
                'total' => $result->count(),
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => 'An unexpected error occurred. Please try again.'], 500);
    }
});

// Marketplace/Listings (Public) - WITH SUBSCRIPTION RANKING (FAIL-SAFE)
Route::get('/marketplace', function (Request $request) {
    try {
        $query = Listing::with(['category', 'user.vendorProfile', 'images'])
            ->where('is_active', true)
            ->whereHas('user', fn($q) => $q->where('is_active', true));

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('user.vendorProfile', function ($vq) use ($search) {
                      $vq->where('business_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Exclude specific product (for related products)
        if ($request->has('exclude')) {
            $query->where('id', '!=', $request->exclude);
        }

        // Filter by vendor
        if ($request->has('vendor_id')) {
            $query->whereHas('user.vendorProfile', function($q) use ($request) {
                $q->where('id', $request->vendor_id);
            });
        }

        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $perPage = $request->get('per_page', 20);

        // Try to use ranking service, fall back to simple sorting if it fails
        $useRanking = ($sortBy === 'ranking' || $sortBy === 'recommended');

        if ($useRanking) {
            try {
                $rankingService = app(\App\Services\ListingRankingService::class);
                $page = $request->get('page', 1);
                $ranked = $rankingService->getRankedListingsPaginated($query, $perPage, $page);
                $listings = collect($ranked['data']);
                $meta = [
                    'current_page' => $ranked['current_page'],
                    'last_page' => $ranked['last_page'],
                    'per_page' => $ranked['per_page'],
                    'total' => $ranked['total'],
                ];
            } catch (\Exception $e) {
                // Ranking failed, fall back to simple sorting
                \Log::warning('Ranking service failed: ' . $e->getMessage());
                $useRanking = false;
            }
        }

        if (!$useRanking) {
            // Simple sorting (fallback or explicit)
            $query->orderBy($sortBy === 'ranking' ? 'created_at' : $sortBy, $sortOrder);
            $paginated = $query->paginate($perPage);
            $listings = $paginated->getCollection();
            $meta = [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ];
        }

    $data = $listings->map(function ($listing) {
        return [
            'id' => $listing->id,
            'vendor_profile_id' => $listing->vendor_profile_id ?? 1,
            'title' => $listing->title,
            'description' => $listing->description,
            'price' => $listing->price,
            'stock' => $listing->stock,
            'images' => $listing->images ? $listing->images->map(function($image) {
                return [
                    'id' => $image->id,
                    'listing_id' => $image->listing_id,
                    'path' => $image->path,
                    'sort_order' => $image->sort_order ?? 0,
                ];
            })->toArray() : [],
            'thumbnail' => $listing->images && $listing->images->isNotEmpty() 
                ? $listing->images->first()->path 
                : null,
            'category' => $listing->category ? [
                'id' => $listing->category->id,
                'name' => $listing->category->name,
                'slug' => $listing->category->slug,
            ] : null,
            'vendor' => $listing->user && $listing->user->vendorProfile ? [
                'id' => $listing->user->vendorProfile->id,
                'user_id' => $listing->user->id,
                'business_name' => $listing->user->vendorProfile->business_name ?? $listing->user->name,
                'created_at' => $listing->user->vendorProfile->created_at?->toIso8601String(),
                'is_verified' => $listing->user->is_admin_verified ?? false,
                'subscription' => [
                    'plan_name' => method_exists($listing->user->vendorProfile, 'getSubscriptionPlanNameAttribute')
                        ? ($listing->user->vendorProfile->subscription_plan_name ?? 'Free')
                        : 'Free',
                    'badge_text' => method_exists($listing->user->vendorProfile, 'getSubscriptionBadge')
                        ? $listing->user->vendorProfile->getSubscriptionBadge()
                        : null,
                    'has_paid_subscription' => method_exists($listing->user->vendorProfile, 'hasPaidSubscription')
                        ? $listing->user->vendorProfile->hasPaidSubscription()
                        : false,
                    'boost_multiplier' => method_exists($listing->user->vendorProfile, 'getBoostMultiplier')
                        ? $listing->user->vendorProfile->getBoostMultiplier()
                        : 1.0,
                ],
            ] : null,
            'average_rating' => $listing->average_rating ?? 0,
            'reviews_count' => $listing->reviews_count ?? 0,
            'is_active' => $listing->is_active,
            'ranking_score' => $listing->ranking_score ?? null,
            'boost_multiplier' => $listing->boost_multiplier ?? 1.0,
            'is_promoted' => ($listing->boost_multiplier ?? 1.0) > 1.0, // For badge display in app
        ];
    });

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => $meta,
        ]);
    } catch (\Exception $e) {
        \Log::error('Marketplace API error: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Failed to load products',
            'error' => config('app.debug') ? $e->getMessage() : null,
        ], 500);
    }
});

Route::get('/marketplace/{id}', function ($id) {
    $listing = Listing::with(['category', 'user.vendorProfile', 'variants', 'images', 'reviews.user'])
        ->where('is_active', true)
        ->whereHas('user', fn($q) => $q->where('is_active', true))
        ->find($id);

    if (!$listing) {
        return response()->json(['success' => false, 'message' => 'Listing not found'], 404);
    }

    // Check if vendor is deactivated
    if ($listing->user && !$listing->user->is_active) {
        return response()->json(['success' => false, 'message' => 'This product is currently unavailable'], 404);
    }

    return response()->json([
        'success' => true,
        'data' => [
            'id' => $listing->id,
            'vendor_profile_id' => $listing->vendor_profile_id ?? 1,
            'title' => $listing->title,
            'description' => $listing->description,
            'price' => $listing->price,
            'stock' => $listing->stock,
            'sku' => $listing->sku,
            'images' => $listing->images ? $listing->images->map(function($image) {
                return ['id' => $image->id, 'listing_id' => $image->listing_id, 'path' => $image->path, 'sort_order' => $image->sort_order ?? 0];
            })->toArray() : [],
            'variants' => $listing->variants ? $listing->variants->map(function ($v) {
                return ['id' => $v->id, 'listing_id' => $v->listing_id, 'sku' => $v->sku, 'price' => $v->price, 'stock' => $v->stock, 'attributes' => $v->attributes ?? []];
            }) : [],
            'attributes' => $listing->attributes ?? [],
            'category' => $listing->category ? ['id' => $listing->category->id, 'name' => $listing->category->name] : null,
            'vendor' => $listing->user && $listing->user->vendorProfile ? [
                'id' => $listing->user->vendorProfile->id,
                'business_name' => $listing->user->vendorProfile->business_name ?? $listing->user->name,
                'created_at' => $listing->user->vendorProfile->created_at?->toIso8601String(),
                'is_verified' => $listing->user->is_admin_verified ?? false,
            ] : null,
            'average_rating' => $listing->average_rating ?? 0,
            'reviews_count' => $listing->reviews ? $listing->reviews->count() : 0,
            'is_active' => $listing->is_active,
        ],
    ]);
});

// Featured listings (ranked by subscription boost + organic score)
Route::get('/featured-listings', function () {
    try {
        $rankingService = app(\App\Services\ListingRankingService::class);

        $query = Listing::query()
            ->where('is_active', true)
            ->whereHas('user', fn($q) => $q->where('is_active', true))
            ->whereHas('vendor', fn($q) => $q->where('vetting_status', 'approved'));

        $listings = $rankingService->getRankedListings($query, 20);
    } catch (\Exception $e) {
        // Ranking failed, fall back to simple query
        $listings = Listing::with(['images', 'vendor'])
            ->where('is_active', true)
            ->whereHas('user', fn($q) => $q->where('is_active', true))
            ->whereHas('vendor', fn($q) => $q->where('vetting_status', 'approved'))
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();
    }

    $listings = $listings
        ->map(function ($listing) {
            return [
                'id' => $listing->id,
                'vendor_profile_id' => $listing->vendor_profile_id ?? 1,
                'title' => $listing->title,
                'description' => $listing->description,
                'price' => $listing->price,
                'stock' => $listing->stock,
                'images' => $listing->images ? $listing->images->map(function($image) {
                    return ['id' => $image->id, 'listing_id' => $image->listing_id, 'path' => $image->path, 'sort_order' => $image->sort_order ?? 0];
                })->toArray() : [],
                'vendor' => $listing->vendor ? [
                    'id' => $listing->vendor->id,
                    'business_name' => $listing->vendor->business_name ?? 'Vendor',
                    'created_at' => $listing->vendor->created_at?->toIso8601String(),
                    'is_verified' => $listing->vendor->user?->is_admin_verified ?? false,
                    'subscription' => [
                        'plan_name' => method_exists($listing->vendor, 'getSubscriptionPlanNameAttribute')
                            ? ($listing->vendor->subscription_plan_name ?? 'Free')
                            : 'Free',
                        'badge_text' => method_exists($listing->vendor, 'getSubscriptionBadge')
                            ? $listing->vendor->getSubscriptionBadge()
                            : null,
                        'has_paid_subscription' => method_exists($listing->vendor, 'hasPaidSubscription')
                            ? $listing->vendor->hasPaidSubscription()
                            : false,
                    ],
                ] : null,
                'category' => $listing->category ? ['id' => $listing->category->id, 'name' => $listing->category->name] : null,
                'average_rating' => $listing->average_rating ?? 0,
                'reviews_count' => $listing->reviews_count ?? 0,
                'is_active' => $listing->is_active,
                'ranking_score' => $listing->ranking_score ?? 0,
                'boost_multiplier' => $listing->boost_multiplier ?? 1.0,
            ];
        });

    return response()->json(['success' => true, 'data' => $listings]);
});

// Listing variations
Route::prefix('listings')->group(function () {
    Route::get('{listing}/check-variations', function ($listingId) {
        $listing = Listing::with(['variants'])->find($listingId);
        if (!$listing) {
            return response()->json(['success' => false, 'message' => 'Listing not found'], 404);
        }

        $variants = $listing->variants->where('stock', '>', 0);
        $hasVariations = $variants->isNotEmpty();

        $availableColors = $variants->filter(function($v) {
            $attrs = is_array($v->attributes) ? $v->attributes : (is_string($v->attributes) ? json_decode($v->attributes, true) : []);
            return !empty($attrs['color'] ?? $attrs['Color'] ?? null);
        })->map(function($v) {
            $attrs = is_array($v->attributes) ? $v->attributes : (is_string($v->attributes) ? json_decode($v->attributes, true) : []);
            return $attrs['color'] ?? $attrs['Color'] ?? null;
        })->filter()->unique()->values();

        $availableSizes = $variants->filter(function($v) {
            $attrs = is_array($v->attributes) ? $v->attributes : (is_string($v->attributes) ? json_decode($v->attributes, true) : []);
            return !empty($attrs['size'] ?? $attrs['Size'] ?? null);
        })->map(function($v) {
            $attrs = is_array($v->attributes) ? $v->attributes : (is_string($v->attributes) ? json_decode($v->attributes, true) : []);
            return $attrs['size'] ?? $attrs['Size'] ?? null;
        })->filter()->unique()->values();

        return response()->json([
            'has_variations' => $hasVariations,
            'available_colors' => $availableColors,
            'available_sizes' => $availableSizes,
            'variant_count' => $variants->count(),
        ]);
    });

    Route::get('{listing}/variations', function ($listingId) {
        $listing = Listing::with(['variants'])->find($listingId);
        if (!$listing) {
            return response()->json(['success' => false, 'message' => 'Listing not found'], 404);
        }

        $variations = $listing->variants->where('stock', '>', 0)->map(function($v) {
            $attrs = is_array($v->attributes) ? $v->attributes : (is_string($v->attributes) ? json_decode($v->attributes, true) : []);
            return [
                'id' => $v->id,
                'listing_id' => $v->listing_id,
                'sku' => $v->sku,
                'display_name' => $v->display_name,
                'price' => (float) $v->price,
                'display_price' => $v->sale_price ? (float) $v->sale_price : null,
                'stock' => (int) $v->stock,
                'attributes' => $attrs,
                'is_default' => (bool) $v->is_default,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'variations' => $variations,
        ]);
    });
});

// ==================== JOB APPLICATION API ====================
Route::middleware('auth:sanctum')->post('/jobs/{slug}/apply', function (Request $request, $slug) {
    $job = \App\Models\JobListing::where('slug', $slug)->active()->notExpired()->firstOrFail();

    if ($job->hasUserApplied($request->user()->id)) {
        return response()->json(['success' => false, 'message' => 'You have already applied for this job.'], 400);
    }

    $request->validate([
        'applicant_name'  => 'required|string|max:255',
        'applicant_email' => 'required|email|max:255',
        'applicant_phone' => 'nullable|string|max:20',
        'cover_letter'    => 'nullable|string|max:10000',
        'expected_salary' => 'nullable|numeric|min:0',
    ]);

    \App\Models\JobApplication::create([
        'job_listing_id' => $job->id,
        'user_id'        => $request->user()->id,
        'applicant_name' => $request->applicant_name,
        'applicant_email'=> $request->applicant_email,
        'applicant_phone'=> $request->applicant_phone,
        'cover_letter'   => $request->cover_letter,
        'expected_salary'=> $request->expected_salary,
    ]);

    $job->increment('applications_count');

    return response()->json(['success' => true, 'message' => 'Your application has been submitted successfully!']);
});

// ====================
// AUTHENTICATED ROUTES
// ====================
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', function (Request $request) {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['success' => true, 'message' => 'Logged out successfully']);
    });

    // ====================
    // PUSH NOTIFICATIONS
    // ====================
    Route::post('/device-token', [\App\Http\Controllers\Api\NotificationController::class, 'registerToken']);
    Route::delete('/device-token', [\App\Http\Controllers\Api\NotificationController::class, 'removeToken']);
    Route::get('/notifications', [\App\Http\Controllers\Api\NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\Api\NotificationController::class, 'markRead']);
    Route::post('/notifications/read-all', [\App\Http\Controllers\Api\NotificationController::class, 'markAllRead']);
    Route::get('/notifications/unread-count', [\App\Http\Controllers\Api\NotificationController::class, 'unreadCount']);
    Route::get('/notification-preferences', [\App\Http\Controllers\Api\NotificationController::class, 'getPreferences']);
    Route::put('/notification-preferences', [\App\Http\Controllers\Api\NotificationController::class, 'updatePreferences']);
    Route::post('/search-queries', [\App\Http\Controllers\Api\NotificationController::class, 'logSearch']);

    Route::get('/user', function (Request $request) {
        $user = $request->user()->load('vendorProfile');

        $vendorProfileData = null;
        if ($user->vendorProfile) {
            $vp = $user->vendorProfile;
            $vendorProfileData = [
                'id' => $vp->id,
                'user_id' => $vp->user_id,
                'business_name' => $vp->business_name,
                'business_description' => $vp->business_description,
                'business_address' => $vp->address,
                'phone' => $vp->business_phone,
                'email' => $vp->email ?? $user->email,
                'logo' => $vp->logo,
                'banner' => $vp->banner,
                'vendor_type' => $vp->vendor_type,
                'vetting_status' => $vp->vetting_status,
                'country' => $vp->country,
                'city' => $vp->city,
                'rating' => $vp->rating ?? 0,
                'total_sales' => $vp->total_sales ?? 0,
                'created_at' => $vp->created_at,
                'updated_at' => $vp->updated_at,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'avatar' => $user->avatar ? asset('storage/' . $user->avatar) : null,
                'is_verified' => $user->is_verified,
                'is_admin_verified' => $user->is_admin_verified ?? false,
                'phone_verified' => $user->phone_verified ?? false,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
                'vendor_profile' => $vendorProfileData,
            ],
        ]);
    });

    // Update User Profile
    Route::put('/user/profile', function (Request $request) {
        $user = $request->user();

        // Build validation rules - skip phone validation if it's a Google placeholder
        $rules = [
            'name' => 'sometimes|string|max:255',
        ];

        // Only validate phone if provided and not a Google placeholder
        if ($request->has('phone') && !str_starts_with($request->phone, 'google_')) {
            $rules['phone'] = 'sometimes|string|max:20';
        }

        $request->validate($rules);

        if ($request->has('name')) {
            $user->name = $request->name;
        }

        // Only update phone if it's not a Google placeholder being sent back
        if ($request->has('phone') && !str_starts_with($request->phone, 'google_')) {
            $user->phone = $request->phone;
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
            ],
        ]);
    });

    // Update User Avatar/Profile Picture
    Route::post('/user/avatar', function (Request $request) {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = $request->user();

        // Delete old avatar if exists
        if ($user->avatar && \Storage::disk('public')->exists($user->avatar)) {
            \Storage::disk('public')->delete($user->avatar);
        }

        // Store new avatar
        $path = $request->file('avatar')->store('avatars', 'public');
        $user->avatar = $path;
        $user->save();
        return response()->json([
            'success' => true,
            'message' => 'Avatar updated successfully',
            'avatar' => asset('storage/' . $user->avatar),
        ]);
    });

    // Vendor Onboarding
    Route::post('/vendor/onboard', [App\Http\Controllers\Marketplace\VendorOnboardingController::class, 'store']);
    Route::get('/vendor/onboard/status', [App\Http\Controllers\Marketplace\VendorOnboardingController::class, 'show']);
    Route::post('/vendor/onboard/additional', [App\Http\Controllers\Marketplace\VendorOnboardingController::class, 'uploadAdditional']);

    // Request Account Deletion (Google Play Store requirement)
    Route::post('/user/delete-account', function (Request $request) {
        $request->validate([
            'reason' => 'nullable|string|max:255',
            'comments' => 'nullable|string|max:1000',
        ]);

        $user = $request->user();

        // Check if there's already a pending deletion request
        $existingRequest = \DB::table('account_deletion_requests')
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if ($existingRequest) {
            return response()->json([
                'success' => false,
                'message' => 'A deletion request is already pending for your account.',
            ], 400);
        }

        // Create deletion request
        \DB::table('account_deletion_requests')->insert([
            'user_id' => $user->id,
            'email' => $user->email,
            'reason' => $request->reason,
            'comments' => $request->comments,
            'status' => 'pending',
            'requested_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Send confirmation email
        try {
            \Mail::raw(
                "Dear {$user->name},\n\n" .
                "We have received your request to delete your BebaMart account.\n\n" .
                "Your request will be processed within 30 days. You will receive a confirmation email once your account and data have been deleted.\n\n" .
                "If you did not make this request, please contact us immediately at support@bebamart.com.\n\n" .
                "Thank you for using BebaMart.\n\n" .
                "Best regards,\nThe BebaMart Team",
                function ($message) use ($user) {
                    $message->to($user->email)
                        ->subject('BebaMart - Account Deletion Request Received');
                }
            );
        } catch (\Exception $e) {
            \Log::error('Failed to send deletion confirmation email: ' . $e->getMessage());
        }

        // Revoke all tokens (log out user)
        $user->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Your account deletion request has been submitted. Your account will be deleted within 30 days.',
        ]);
    });

    // Get deletion request status
    Route::get('/user/delete-account/status', function (Request $request) {
        $user = $request->user();

        $deletionRequest = \DB::table('account_deletion_requests')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$deletionRequest) {
            return response()->json([
                'success' => true,
                'has_pending_request' => false,
            ]);
        }

        return response()->json([
            'success' => true,
            'has_pending_request' => $deletionRequest->status === 'pending',
            'request' => [
                'status' => $deletionRequest->status,
                'requested_at' => $deletionRequest->requested_at,
                'processed_at' => $deletionRequest->processed_at,
            ],
        ]);
    });

    // Cancel deletion request
    Route::post('/user/delete-account/cancel', function (Request $request) {
        $user = $request->user();

        $affected = \DB::table('account_deletion_requests')
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->update([
                'status' => 'cancelled',
                'updated_at' => now(),
            ]);

        if ($affected === 0) {
            return response()->json([
                'success' => false,
                'message' => 'No pending deletion request found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Your deletion request has been cancelled.',
        ]);
    });

    // Vendor Profile Update with Avatar
    Route::post('/vendor/profile', function (Request $request) {
        $request->validate([
            'business_name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'address' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
            'logo' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
            'avatar' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = $request->user();
        $vendor = $user->vendorProfile;

        if (!$vendor) {
            return response()->json(['success' => false, 'message' => 'Vendor profile not found'], 404);
        }

        // Update vendor profile fields
        if ($request->has('business_name')) {
            $vendor->business_name = $request->business_name;
        }
        if ($request->has('description')) {
            $vendor->meta = array_merge($vendor->meta ?? [], ['description' => $request->description]);
        }
        if ($request->has('address')) {
            $vendor->address = $request->address;
        }
        if ($request->has('phone')) {
            $vendor->meta = array_merge($vendor->meta ?? [], ['phone' => $request->phone]);
        }

        // Handle logo upload for vendor profile
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('vendor-logos', 'public');
            $vendor->meta = array_merge($vendor->meta ?? [], ['logo' => $logoPath]);
        }

        $vendor->save();

        // Handle avatar upload for user (profile picture)
        if ($request->hasFile('avatar')) {
            if ($user->avatar && \Storage::disk('public')->exists($user->avatar)) {
                \Storage::disk('public')->delete($user->avatar);
            }
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $avatarPath;
            $user->save();
        }

        // Reload vendor to get fresh data with accessors
        $vendor->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'vendor' => [
                'id' => $vendor->id,
                'business_name' => $vendor->business_name,
                'vetting_status' => $vendor->vetting_status,
                'logo' => $vendor->logo,
                'banner' => $vendor->banner,
                'business_description' => $vendor->business_description,
                'business_phone' => $vendor->business_phone,
                'address' => $vendor->address,
            ],
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'avatar' => $user->avatar ? asset('storage/' . $user->avatar) : null,
            ],
        ]);
    });

    // Change Password
    Route::put('/user/change-password', function (Request $request) {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!\Hash::check($request->current_password, $user->password)) {
            return response()->json(['success' => false, 'message' => 'Current password is incorrect'], 400);
        }

        $user->password = \Hash::make($request->new_password);
        $user->save();

        return response()->json(['success' => true, 'message' => 'Password changed successfully']);
    });

    // My Reviews
    Route::get('/reviews/my', function (Request $request) {
        $reviews = \App\Models\Review::where('user_id', $request->user()->id)
            ->with(['listing.images', 'orderItem'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $reviews->map(function ($review) {
                return [
                    'id' => $review->id,
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'created_at' => $review->created_at,
                    'listing' => $review->listing ? [
                        'id' => $review->listing->id,
                        'title' => $review->listing->title,
                        'image' => $review->listing->images->isNotEmpty() ? $review->listing->images->first()->path : null,
                    ] : null,
                    'vendor_response' => $review->vendor_response,
                    'vendor_response_at' => $review->vendor_response_at,
                ];
            }),
            'meta' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'total' => $reviews->total(),
            ],
        ]);
    });

    // CART
    Route::get('/cart', function (Request $request) {
        $cart = Cart::where('user_id', $request->user()->id)->first();
        if (!$cart || empty($cart->items)) {
            return response()->json(['success' => true, 'data' => ['items' => [], 'subtotal' => 0, 'total' => 0]]);
        }
        $items = collect($cart->items)->map(function ($item) {
            $listing = Listing::with('images')->find($item['listing_id']);
            if (!$listing) return null;

            $cartItem = [
                'listing_id' => $listing->id,
                'title' => $listing->title,
                'price' => $item['price'] ?? $listing->price,
                'quantity' => $item['quantity'],
                'thumbnail' => $listing->images && $listing->images->isNotEmpty() ? $listing->images->first()->path : null,
                'stock' => $listing->stock,
                'tax_amount' => $listing->tax_amount ?? 0,
                'tax_description' => $listing->tax_description,
            ];

            // Include variant info if present
            if (isset($item['variant_id'])) {
                $cartItem['variant_id'] = $item['variant_id'];
                $cartItem['variant'] = $item['variant'] ?? null;
                $cartItem['attributes'] = $item['attributes'] ?? null;

                // Append variant display to title
                $variantDisplay = [];
                if (isset($item['attributes']['color'])) {
                    $variantDisplay[] = $item['attributes']['color'];
                }
                if (isset($item['attributes']['size'])) {
                    $variantDisplay[] = $item['attributes']['size'];
                }
                if (!empty($variantDisplay)) {
                    $cartItem['title'] = $listing->title . ' (' . implode(', ', $variantDisplay) . ')';
                }

                // Get variant stock if available
                if ($item['variant_id']) {
                    $variant = \App\Models\ListingVariant::find($item['variant_id']);
                    if ($variant) {
                        $cartItem['stock'] = $variant->stock;
                    }
                }
            }

            return $cartItem;
        })->filter()->values();
        $subtotal = $items->sum(fn($item) => $item['price'] * $item['quantity']);
        $totalTax = $items->sum(fn($item) => ($item['tax_amount'] ?? 0) * $item['quantity']);
        return response()->json(['success' => true, 'data' => ['items' => $items, 'subtotal' => $subtotal, 'tax' => $totalTax, 'total' => $subtotal + $totalTax]]);
    });

    Route::post('/cart/add/{listingId}', function (Request $request, $listingId) {
        $listing = Listing::find($listingId);
        if (!$listing) return response()->json(['success' => false, 'message' => 'Listing not found'], 404);

        $cart = Cart::firstOrCreate(['user_id' => $request->user()->id], ['items' => []]);
        $items = $cart->items ?? [];
        $quantity = $request->input('quantity', 1);
        $variantId = $request->input('variant_id');
        $attributes = $request->input('attributes', []);

        // Debug logging
        \Log::info('Cart add request', [
            'listing_id' => $listingId,
            'quantity' => $quantity,
            'variant_id' => $variantId,
            'attributes' => $attributes,
            'all_input' => $request->all(),
        ]);

        // Determine the price - use variant price if variant is specified
        $price = $listing->price;
        $variantData = null;

        if ($variantId) {
            $variant = \App\Models\ListingVariant::find($variantId);
            if ($variant && $variant->listing_id == $listingId) {
                $price = $variant->sale_price ?? $variant->price;
                $variantData = [
                    'id' => $variant->id,
                    'sku' => $variant->sku,
                    'display_name' => $variant->display_name,
                    'color' => $attributes['color'] ?? ($variant->attributes['color'] ?? null),
                    'size' => $attributes['size'] ?? ($variant->attributes['size'] ?? null),
                ];
            }
        }

        // For variant products, match by both listing_id AND variant_id
        // For non-variant products, match by listing_id only
        $existingIndex = collect($items)->search(function($item) use ($listingId, $variantId) {
            if ($item['listing_id'] != $listingId) return false;
            // If adding a variant, match by variant_id too
            if ($variantId) {
                return isset($item['variant_id']) && $item['variant_id'] == $variantId;
            }
            // If adding non-variant, make sure existing item is also non-variant
            return !isset($item['variant_id']) || $item['variant_id'] === null;
        });

        if ($existingIndex !== false) {
            $items[$existingIndex]['quantity'] += $quantity;
        } else {
            $newItem = [
                'listing_id' => (int)$listingId,
                'quantity' => $quantity,
                'price' => $price,
                'title' => $listing->title,
                'tax_amount' => $listing->tax_amount ?? 0,
                'tax_description' => $listing->tax_description,
            ];

            // Add variant info if present
            if ($variantId && $variantData) {
                $newItem['variant_id'] = (int)$variantId;
                $newItem['variant'] = $variantData;
                $newItem['attributes'] = $attributes;
            }

            $items[] = $newItem;
        }
        $cart->update(['items' => $items]);
        return response()->json(['success' => true, 'message' => 'Item added to cart', 'cart_count' => count($items)]);
    });

    Route::post('/cart/update/{listingId}', function (Request $request, $listingId) {
        $cart = Cart::where('user_id', $request->user()->id)->first();
        if (!$cart) return response()->json(['success' => false, 'message' => 'Cart not found'], 404);

        $items = $cart->items ?? [];
        $quantity = $request->input('quantity', 1);
        $variantId = $request->input('variant_id');

        // Find item by listing_id and optionally variant_id
        $index = collect($items)->search(function($item) use ($listingId, $variantId) {
            if ($item['listing_id'] != $listingId) return false;
            if ($variantId) {
                return isset($item['variant_id']) && $item['variant_id'] == $variantId;
            }
            return !isset($item['variant_id']) || $item['variant_id'] === null;
        });

        if ($index !== false) {
            if ($quantity <= 0) { unset($items[$index]); $items = array_values($items); }
            else { $items[$index]['quantity'] = $quantity; }
            $cart->update(['items' => $items]);
        }
        return response()->json(['success' => true, 'message' => 'Cart updated']);
    });

    Route::delete('/cart/remove/{listingId}', function (Request $request, $listingId) {
        $cart = Cart::where('user_id', $request->user()->id)->first();
        if (!$cart) return response()->json(['success' => false, 'message' => 'Cart not found'], 404);

        // Accept variant_id from query params, body, or input
        $variantId = $request->query('variant_id') ?? $request->input('variant_id');

        // Filter by listing_id and optionally variant_id
        $items = collect($cart->items ?? [])->filter(function($item) use ($listingId, $variantId) {
            if ($item['listing_id'] != $listingId) return true; // Keep items with different listing_id

            // If variant_id specified, only remove matching variant
            if ($variantId) {
                return !(isset($item['variant_id']) && $item['variant_id'] == $variantId);
            }

            // If no variant_id, remove non-variant items
            return isset($item['variant_id']) && $item['variant_id'] !== null;
        })->values()->toArray();

        $cart->update(['items' => $items]);
        return response()->json(['success' => true, 'message' => 'Item removed from cart']);
    });

    Route::post('/cart/clear', function (Request $request) {
        Cart::where('user_id', $request->user()->id)->update(['items' => []]);
        return response()->json(['success' => true, 'message' => 'Cart cleared']);
    });

    // WISHLIST
    Route::get('/wishlist', function (Request $request) {
        $items = Wishlist::with(['listing.images'])->where('user_id', $request->user()->id)->get()->map(function ($item) {
            if (!$item->listing) return null;
            return [
                'id' => $item->id,
                'listing_id' => $item->listing->id,
                'title' => $item->listing->title,
                'price' => $item->listing->price,
                'thumbnail' => $item->listing->images && $item->listing->images->isNotEmpty() ? $item->listing->images->first()->path : null,
                'in_stock' => $item->listing->stock > 0,
                'added_at' => $item->created_at,
            ];
        })->filter()->values();
        return response()->json(['success' => true, 'data' => $items]);
    });

    Route::post('/wishlist/add/{listingId}', function (Request $request, $listingId) {
        if (!Listing::find($listingId)) return response()->json(['success' => false, 'message' => 'Listing not found'], 404);
        Wishlist::firstOrCreate(['user_id' => $request->user()->id, 'listing_id' => $listingId]);
        return response()->json(['success' => true, 'message' => 'Added to wishlist']);
    });

    Route::delete('/wishlist/remove/{listingId}', function (Request $request, $listingId) {
        Wishlist::where('user_id', $request->user()->id)->where('listing_id', $listingId)->delete();
        return response()->json(['success' => true, 'message' => 'Removed from wishlist']);
    });

    Route::post('/wishlist/toggle/{listingId}', function (Request $request, $listingId) {
        $exists = Wishlist::where('user_id', $request->user()->id)->where('listing_id', $listingId)->first();
        if ($exists) {
            $exists->delete();
            return response()->json(['success' => true, 'in_wishlist' => false, 'message' => 'Removed from wishlist']);
        }
        Wishlist::create(['user_id' => $request->user()->id, 'listing_id' => $listingId]);
        return response()->json(['success' => true, 'in_wishlist' => true, 'message' => 'Added to wishlist']);
    });

    Route::post('/wishlist/move-to-cart/{listingId}', function (Request $request, $listingId) {
        $listing = Listing::find($listingId);
        if (!$listing) return response()->json(['success' => false, 'message' => 'Listing not found'], 404);
        
        $userId = $request->user()->id;
        Wishlist::where('user_id', $userId)->where('listing_id', $listingId)->delete();
        
        $cart = Cart::firstOrCreate(['user_id' => $userId], ['items' => []]);
        $items = $cart->items ?? [];
        $existingIndex = collect($items)->search(fn($item) => $item['listing_id'] == $listingId);
        
        if ($existingIndex !== false) { $items[$existingIndex]['quantity'] += 1; }
        else { $items[] = ['listing_id' => (int)$listingId, 'quantity' => 1, 'price' => $listing->price, 'title' => $listing->title]; }
        
        $cart->update(['items' => $items]);
        return response()->json(['success' => true, 'message' => 'Moved to cart', 'cart_count' => count($items)]);
    });

    // ORDERS
    Route::get('/orders', function (Request $request) {
        $orders = Order::withCount('items')
            ->where('buyer_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        return response()->json([
            'success' => true,
            'data' => $orders->map(fn($o) => [
                'id' => $o->id,
                'order_number' => $o->order_number,
                'status' => $o->status,
                'total' => $o->total,
                'subtotal' => $o->subtotal,
                'shipping' => $o->shipping,
                'taxes' => $o->taxes,
                'items_count' => $o->items_count,
                'meta' => $o->meta,
                'created_at' => $o->created_at,
            ]),
            'meta' => ['current_page' => $orders->currentPage(), 'last_page' => $orders->lastPage(), 'total' => $orders->total()],
        ]);
    });

    Route::get('/orders/{id}', function (Request $request, $id) {
        $order = Order::with(['items.listing.images', 'vendorProfile'])
            ->where('buyer_id', $request->user()->id)
            ->find($id);
        if (!$order) return response()->json(['success' => false, 'message' => 'Order not found'], 404);

        // Transform to include all necessary data
        $orderData = $order->toArray();
        $orderData['items'] = $order->items->map(function ($item) {
            $listing = $item->listing;
            $attributes = $item->attributes ?? [];
            $imageUrl = $attributes['thumbnail'] ?? null;

            // Fallback to listing image if no thumbnail in attributes
            if (!$imageUrl && $listing && $listing->images->isNotEmpty()) {
                $image = $listing->images->first();
                $imageUrl = $image->path;
            }

            return [
                'id' => $item->id,
                'order_id' => $item->order_id,
                'listing_id' => $item->listing_id,
                'variant_id' => $attributes['variant_id'] ?? null,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'total' => $item->line_total ?? ($item->unit_price * $item->quantity),
                'meta' => [
                    'thumbnail' => $imageUrl,
                    'color' => $attributes['color'] ?? null,
                    'size' => $attributes['size'] ?? null,
                ],
                'listing' => $listing ? [
                    'id' => $listing->id,
                    'title' => $item->title ?? $listing->title,
                    'slug' => $listing->slug,
                    'price' => $listing->price,
                    'images' => $listing->images->map(fn($img) => [
                        'id' => $img->id,
                        'path' => $img->path,
                        'is_primary' => $img->is_primary ?? false,
                    ])->toArray(),
                ] : [
                    'id' => $item->listing_id,
                    'title' => $item->title,
                    'slug' => null,
                    'price' => $item->unit_price,
                    'images' => [],
                ],
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
            ];
        })->toArray();

        $orderData['items_count'] = count($orderData['items']);

        return response()->json(['success' => true, 'data' => $orderData]);
    });

    // Confirm Delivery (for COD orders)
    Route::post('/orders/{id}/confirm-delivery', function (Request $request, $id) {
        $order = Order::where('buyer_id', $request->user()->id)->find($id);
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        // Check if order can be confirmed (must be shipped)
        if ($order->status !== 'shipped') {
            return response()->json(['success' => false, 'message' => 'Order must be shipped before confirming delivery'], 400);
        }

        // Check if this is a COD order
        $meta = $order->meta ?? [];
        $isCOD = isset($meta['payment_method']) && $meta['payment_method'] === 'cash_on_delivery';

        if (!$isCOD) {
            return response()->json(['success' => false, 'message' => 'This order is not cash on delivery'], 400);
        }

        try {
            \DB::beginTransaction();

            // Calculate delivery time
            $deliveryTimeDays = 0;
            if ($order->shipped_at) {
                $deliveryTimeDays = $order->shipped_at->diffInDays(now());
            } else {
                $deliveryTimeDays = $order->created_at->diffInDays(now());
            }

            // Calculate delivery score
            $deliveryScore = match(true) {
                $deliveryTimeDays <= 1 => 100,
                $deliveryTimeDays <= 2 => 95,
                $deliveryTimeDays <= 3 => 90,
                $deliveryTimeDays <= 5 => 80,
                $deliveryTimeDays <= 7 => 70,
                $deliveryTimeDays <= 10 => 60,
                $deliveryTimeDays <= 14 => 50,
                default => 40,
            };

            // Update order status
            $order->update([
                'status' => 'delivered',
                'delivered_at' => now(),
                'delivery_time_days' => $deliveryTimeDays,
                'delivery_score' => $deliveryScore,
                'meta' => array_merge($meta, [
                    'confirmed_by_buyer' => true,
                    'buyer_confirmed_at' => now()->toDateTimeString(),
                    'payment_confirmed' => true,
                ]),
            ]);

            // Handle COD payment
            $payment = $order->payments()->where('provider', 'cash')->first();
            if ($payment) {
                $payment->update([
                    'status' => 'completed',
                    'meta' => array_merge($payment->meta ?? [], [
                        'paid_at' => now()->toDateTimeString(),
                        'payment_confirmed_by_buyer' => true,
                    ]),
                ]);
            }

            \DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Delivery confirmed successfully',
                'data' => $order->fresh(),
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to confirm delivery. Please try again.'], 500);
        }
    });

    // ==================== BUYER SERVICE REQUESTS ====================
    Route::post('/service-requests', function (Request $request) {
        $request->validate([
            'vendor_service_id' => 'required|exists:vendor_services,id',
            'description'       => 'required|string|max:2000',
            'customer_name'     => 'required|string|max:255',
            'customer_phone'    => 'required|string|max:20',
            'customer_email'    => 'nullable|email',
            'location'          => 'nullable|string|max:255',
            'address'           => 'nullable|string|max:500',
            'preferred_date'    => 'nullable|date',
            'preferred_time'    => 'nullable|string|max:50',
            'urgency'           => 'nullable|in:normal,urgent,emergency',
            'budget_min'        => 'nullable|numeric|min:0',
            'budget_max'        => 'nullable|numeric|min:0',
        ]);

        $service = \App\Models\VendorService::findOrFail($request->vendor_service_id);

        $sr = \App\Models\ServiceRequest::create([
            'vendor_service_id'  => $service->id,
            'vendor_profile_id'  => $service->vendor_profile_id,
            'user_id'            => $request->user()->id,
            'request_number'     => 'SR-' . strtoupper(uniqid()),
            'description'        => $request->description,
            'customer_name'      => $request->customer_name,
            'customer_phone'     => $request->customer_phone,
            'customer_email'     => $request->customer_email ?? $request->user()->email,
            'location'           => $request->location,
            'address'            => $request->address,
            'preferred_date'     => $request->preferred_date,
            'preferred_time'     => $request->preferred_time,
            'urgency'            => $request->urgency ?? 'normal',
            'budget_min'         => $request->budget_min,
            'budget_max'         => $request->budget_max,
            'status'             => 'pending',
        ]);

        $service->increment('inquiries_count');

        return response()->json(['success' => true, 'message' => 'Service request submitted.', 'data' => $sr->load('service')], 201);
    });

    Route::get('/service-requests', function (Request $request) {
        $query = \App\Models\ServiceRequest::where('user_id', $request->user()->id)
            ->with(['service:id,title,pricing_type,price,images'])
            ->latest();

        if ($request->filled('status')) $query->where('status', $request->status);

        $items = $query->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true, 'data' => $items->items(),
            'current_page' => $items->currentPage(), 'last_page' => $items->lastPage(), 'total' => $items->total(),
        ]);
    });

    Route::get('/service-requests/{id}', function (Request $request, $id) {
        $sr = \App\Models\ServiceRequest::where('user_id', $request->user()->id)
            ->with(['service', 'review'])->findOrFail($id);
        return response()->json(['success' => true, 'data' => $sr]);
    });

    Route::post('/service-requests/{id}/accept', function (Request $request, $id) {
        $sr = \App\Models\ServiceRequest::where('user_id', $request->user()->id)
            ->where('status', 'quoted')->findOrFail($id);
        $sr->update(['status' => 'accepted', 'accepted_at' => now()]);
        return response()->json(['success' => true, 'message' => 'Quote accepted.', 'data' => $sr->fresh()]);
    });

    Route::post('/service-requests/{id}/cancel', function (Request $request, $id) {
        $sr = \App\Models\ServiceRequest::where('user_id', $request->user()->id)
            ->whereIn('status', ['pending', 'quoted'])->findOrFail($id);
        $sr->update(['status' => 'cancelled']);
        return response()->json(['success' => true, 'message' => 'Request cancelled.']);
    });

    Route::post('/service-requests/{id}/complete', function (Request $request, $id) {
        $sr = \App\Models\ServiceRequest::where('user_id', $request->user()->id)
            ->where('status', 'in_progress')->findOrFail($id);
        $sr->update(['status' => 'completed', 'completed_at' => now()]);
        return response()->json(['success' => true, 'message' => 'Service marked as completed.', 'data' => $sr->fresh()]);
    });

    // Place Order - FIXED: Now includes title in order_items
    Route::post('/orders/place-order', function (Request $request) {
        $request->validate([
            'shipping_address_id' => 'required|exists:shipping_addresses,id',
            'payment_method' => 'required|string',
            'notes' => 'nullable|string',
            'selected_items' => 'nullable|array',
            'selected_items.*.listing_id' => 'integer',
            'selected_items.*.variant_id' => 'nullable|integer',
        ]);

        $user = $request->user();
        $cart = Cart::where('user_id', $user->id)->first();
        
        if (!$cart || empty($cart->items)) {
            return response()->json(['success' => false, 'message' => 'Cart is empty'], 400);
        }

        // If selected_items provided, filter cart to only include those items
        $cartItems = $cart->items;
        if ($request->has('selected_items') && !empty($request->selected_items)) {
            $selectedKeys = collect($request->selected_items)->map(function ($si) {
                return $si['listing_id'] . '_' . ($si['variant_id'] ?? 0);
            })->toArray();

            $cartItems = array_values(array_filter($cartItems, function ($item) use ($selectedKeys) {
                $key = $item['listing_id'] . '_' . ($item['variant_id'] ?? 0);
                return in_array($key, $selectedKeys);
            }));

            if (empty($cartItems)) {
                return response()->json(['success' => false, 'message' => 'No matching items found in cart'], 400);
            }
        }

        $shippingAddress = \App\Models\ShippingAddress::find($request->shipping_address_id);
        if (!$shippingAddress || $shippingAddress->user_id != $user->id) {
            return response()->json(['success' => false, 'message' => 'Invalid shipping address'], 400);
        }

        // Check if any vendor in cart is deactivated
        foreach ($cartItems as $item) {
            $listing = Listing::with('user')->find($item['listing_id']);
            if ($listing && $listing->user && !$listing->user->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Some products in your cart are from a vendor that is currently unavailable. Please remove them and try again.',
                    'unavailable_listing_id' => $listing->id,
                ], 400);
            }
        }

        try {
            \DB::beginTransaction();

            $subtotal = 0;
            $orderItems = [];

            foreach ($cartItems as $item) {
                $listing = Listing::with('images')->find($item['listing_id']);
                if (!$listing) continue;

                $itemPrice = $item['price'] ?? $listing->price;
                $itemTotal = $itemPrice * $item['quantity'];
                $subtotal += $itemTotal;

                // Get thumbnail from listing images
                $thumbnail = null;
                if ($listing->images->isNotEmpty()) {
                    $thumbnail = $listing->images->first()->path;
                }

                $itemTax = ($listing->tax_amount ?? 0) * $item['quantity'];

                $orderItems[] = [
                    'listing_id' => $listing->id,
                    'title' => $item['title'] ?? $listing->title,
                    'quantity' => $item['quantity'],
                    'unit_price' => $itemPrice,
                    'line_total' => $itemTotal,
                    'attributes' => [
                        'thumbnail' => $thumbnail,
                        'color' => $item['color'] ?? null,
                        'size' => $item['size'] ?? null,
                        'variant_id' => $item['variant_id'] ?? null,
                        'tax_amount' => $listing->tax_amount ?? 0,
                        'tax_description' => $listing->tax_description,
                    ],
                ];
            }

            $shipping = 0;
            $taxes = collect($orderItems)->sum(function ($item) {
                return ($item['attributes']['tax_amount'] ?? 0) * $item['quantity'];
            });
            $platformCommission = $subtotal * 0.15; // 15% commission on subtotal only
            $total = $subtotal + $taxes;

            // Generate order number
            $orderNumber = 'BM-' . strtoupper(uniqid()) . '-' . date('Ymd');

            // Get vendor profile ID from first listing
            $firstListing = Listing::find($cartItems[0]['listing_id']);
            $vendorProfileId = $firstListing ? $firstListing->vendor_profile_id : 1;

            $order = Order::create([
                'order_number' => $orderNumber,
                'buyer_id' => $user->id,
                'vendor_profile_id' => $vendorProfileId,
                'status' => $request->payment_method === 'cash_on_delivery' ? 'pending' : 'payment_pending',
                'subtotal' => $subtotal,
                'shipping' => $shipping,
                'taxes' => $taxes,
                'platform_commission' => $platformCommission,
                'total' => $total,
                'meta' => [
                    'payment_method' => $request->payment_method,
                    'notes' => $request->notes,
                    'shipping_address' => [
                        'recipient_name' => $shippingAddress->recipient_name,
                        'recipient_phone' => $shippingAddress->recipient_phone,
                        'address_line_1' => $shippingAddress->address_line_1,
                        'address_line_2' => $shippingAddress->address_line_2,
                        'city' => $shippingAddress->city,
                        'state_region' => $shippingAddress->state_region,
                        'country' => $shippingAddress->country ?? 'Uganda',
                    ],
                ],
            ]);

            // Create order items with attributes
            foreach ($orderItems as $item) {
                \App\Models\OrderItem::create([
                    'order_id' => $order->id,
                    'listing_id' => $item['listing_id'],
                    'title' => $item['title'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'line_total' => $item['line_total'],
                    'attributes' => $item['attributes'] ?? null,
                ]);

                // Decrease stock
                Listing::where('id', $item['listing_id'])->decrement('stock', $item['quantity']);
            }

            // Remove ordered items from cart (keep unselected items)
            if ($request->has('selected_items') && !empty($request->selected_items)) {
                $orderedKeys = collect($cartItems)->map(function ($item) {
                    return $item['listing_id'] . '_' . ($item['variant_id'] ?? 0);
                })->toArray();

                $remainingItems = array_values(array_filter($cart->items, function ($item) use ($orderedKeys) {
                    $key = $item['listing_id'] . '_' . ($item['variant_id'] ?? 0);
                    return !in_array($key, $orderedKeys);
                }));

                // Recalculate cart totals for remaining items
                $remainingSubtotal = 0;
                foreach ($remainingItems as $ri) {
                    $remainingSubtotal += ($ri['price'] ?? 0) * ($ri['quantity'] ?? 1);
                }

                $cart->update([
                    'items' => $remainingItems,
                    'subtotal' => $remainingSubtotal,
                    'tax' => $remainingSubtotal * 0.18,
                    'total' => $remainingSubtotal + ($remainingSubtotal * 0.18),
                ]);
            } else {
                // No selected_items sent = order all, clear cart
                $cart->update(['items' => []]);
            }

            \DB::commit();

            // Notify vendor about new order
            try {
                $vendorUser = \App\Models\VendorProfile::find($order->vendor_profile_id)?->user;
                if ($vendorUser) {
                    $pushService = new \App\Services\PushNotificationService();
                    $pushService->sendToUser(
                        $vendorUser->id,
                        'vendor_order',
                        "New order received! 🎉",
                        "Order #{$order->order_number} — UGX " . number_format($order->total) . ". Tap to view details.",
                        ['route' => '/vendor/orders/' . $order->id]
                    );
                }
            } catch (\Exception $e) {
                // Non-critical
            }

            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully',
                'data' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'subtotal' => $order->subtotal,
                    'shipping' => $order->shipping,
                    'taxes' => $order->taxes,
                    'total' => $order->total,
                    'buyer_id' => $order->buyer_id,
                    'vendor_profile_id' => $order->vendor_profile_id,
                    'platform_commission' => $order->platform_commission,
                    'created_at' => $order->created_at,
                ],
            ], 201);

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Place order error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to place order. Please try again.',
            ], 500);
        }
    });

    // PESAPAL PAYMENT - Works for both Card and Mobile Money
    Route::post('/orders/{order}/pay/pesapal', function (Request $request, Order $order) {
        $request->validate([
            'payment_type' => 'required|in:card,mobile_money',
            'phone_number' => 'required_if:payment_type,mobile_money|nullable|string',
            'mobile_money_provider' => 'required_if:payment_type,mobile_money|nullable|in:mtn,airtel',
        ]);

        $user = $request->user();

        // Verify ownership
        if ($order->buyer_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Check order status
        if (!in_array($order->status, ['payment_pending', 'pending'])) {
            return response()->json(['success' => false, 'message' => 'Order already processed'], 400);
        }

        try {
            $pesapalService = app(\App\Services\PesapalService::class);
            $txRef = 'BM-' . time() . '-' . $order->id;

            // Get buyer info
            $nameParts = explode(' ', $user->name ?? 'Buyer');
            $firstName = $nameParts[0] ?? 'Buyer';
            $lastName = $nameParts[1] ?? '';

            // Prepare PesaPal order data
            $orderData = [
                'id' => $txRef,
                'currency' => 'UGX',
                'amount' => (float) $order->total,
                'description' => "Payment for Order #{$order->order_number}",
                'callback_url' => config('app.url') . '/api/payments/pesapal/callback',
                'notification_id' => config('services.pesapal.notification_id'),
                'billing_address' => [
                    'email_address' => $user->email ?? 'buyer@bebamart.com',
                    'phone_number' => $request->phone_number ?? $user->phone ?? '',
                    'country_code' => 'UG',
                    'first_name' => $firstName,
                    'middle_name' => '',
                    'last_name' => $lastName,
                    'line_1' => 'Uganda',
                    'city' => 'Kampala',
                    'state' => 'Central',
                    'postal_code' => '256',
                    'zip_code' => '256',
                ],
            ];

            \Log::info('Initiating Pesapal payment (API)', [
                'order_id' => $order->id,
                'payment_type' => $request->payment_type,
                'amount' => $order->total,
            ]);

            // Submit to Pesapal
            $result = $pesapalService->submitOrder($orderData);

            if (isset($result['redirect_url'])) {
                // Create payment record
                \App\Models\Payment::create([
                    'order_id' => $order->id,
                    'provider' => 'pesapal',
                    'provider_payment_id' => $txRef,
                    'amount' => $order->total,
                    'status' => 'pending',
                    'meta' => [
                        'payment_type' => $request->payment_type,
                        'mobile_provider' => $request->mobile_money_provider,
                        'phone_number' => $request->phone_number,
                        'order_tracking_id' => $result['order_tracking_id'] ?? null,
                        'merchant_reference' => $result['merchant_reference'] ?? $txRef,
                        'initiated_at' => now()->toDateTimeString(),
                    ]
                ]);

                // Update order meta
                $currentMeta = $order->meta ?? [];
                if (!is_array($currentMeta)) $currentMeta = [];
                $order->update([
                    'meta' => array_merge($currentMeta, ['payment_reference' => $txRef])
                ]);

                return response()->json([
                    'success' => true,
                    'payment_url' => $result['redirect_url'],
                    'tx_ref' => $txRef,
                    'order_tracking_id' => $result['order_tracking_id'] ?? null,
                    'message' => 'Redirect user to payment_url to complete payment',
                ]);
            }

            $errorMessage = $result['error']['message'] ?? 'Failed to initialize payment';
            \Log::error('Pesapal payment failed', ['result' => $result]);

            return response()->json([
                'success' => false,
                'message' => $errorMessage,
            ], 500);

        } catch (\Exception $e) {
            \Log::error('Pesapal payment error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Payment initialization failed. Please try again.',
            ], 500);
        }
    });

    // Pesapal Callback (API) - handles redirect after payment
    Route::get('/payments/pesapal/callback', function (Request $request) {
        $orderTrackingId = $request->query('OrderTrackingId');
        $orderMerchantReference = $request->query('OrderMerchantReference');

        \Log::info('Pesapal API callback', [
            'orderTrackingId' => $orderTrackingId,
            'orderMerchantReference' => $orderMerchantReference,
        ]);

        if (!$orderTrackingId) {
            return response()->json(['success' => false, 'message' => 'Missing tracking ID'], 400);
        }

        try {
            $pesapalService = app(\App\Services\PesapalService::class);
            $status = $pesapalService->getTransactionStatus($orderTrackingId);

            $payment = \App\Models\Payment::where('provider_payment_id', $orderMerchantReference)
                ->where('provider', 'pesapal')
                ->first();

            if (!$payment) {
                return response()->json(['success' => false, 'message' => 'Payment not found'], 404);
            }

            if ($status && $pesapalService->isPaymentSuccessful($status)) {
                // Prevent duplicate processing with DB transaction
                \DB::transaction(function () use ($payment, $status) {
                    // Re-fetch with lock to prevent race conditions
                    $payment = $payment->fresh();
                    if ($payment->status === 'completed') {
                        return; // Already processed
                    }

                    // Update payment
                    $payment->update([
                        'status' => 'completed',
                        'meta' => array_merge($payment->meta ?? [], [
                            'provider_response' => $status,
                            'completed_at' => now()->toDateTimeString(),
                        ])
                    ]);

                    // Update order
                    $payment->order->update(['status' => 'paid']);

                    // Create escrow (idempotent - skip if already exists)
                    \App\Models\Escrow::firstOrCreate(
                        ['order_id' => $payment->order_id],
                        [
                            'amount' => $payment->order->total,
                            'status' => 'held',
                            'release_at' => now()->addDays(7),
                        ]
                    );
                });

                return response()->json([
                    'success' => true,
                    'message' => 'Payment completed successfully',
                    'order_id' => $payment->order_id,
                    'status' => 'completed',
                ]);
            }

            $statusCode = $status['status_code'] ?? 0;
            return response()->json([
                'success' => false,
                'message' => 'Payment not successful',
                'status_code' => $statusCode,
                'status' => $pesapalService->getStatusDescription($statusCode),
            ]);

        } catch (\Exception $e) {
            \Log::error('Pesapal callback error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Verification failed'], 500);
        }
    })->withoutMiddleware(['auth:sanctum']);

    // Check payment status
    Route::get('/orders/{order}/payment-status', function (Request $request, Order $order) {
        if ($order->buyer_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $payment = $order->payments()->where('provider', 'pesapal')->latest()->first();

        if (!$payment) {
            return response()->json([
                'success' => true,
                'status' => 'no_payment',
                'order_status' => $order->status,
            ]);
        }

        // If pending, check with Pesapal
        if ($payment->status === 'pending') {
            $trackingId = $payment->meta['order_tracking_id'] ?? null;
            if ($trackingId) {
                try {
                    $pesapalService = app(\App\Services\PesapalService::class);
                    $status = $pesapalService->getTransactionStatus($trackingId);

                    if ($status && $pesapalService->isPaymentSuccessful($status)) {
                        $payment->update(['status' => 'completed']);
                        $order->update(['status' => 'paid']);

                        return response()->json([
                            'success' => true,
                            'status' => 'completed',
                            'message' => 'Payment completed',
                        ]);
                    }
                } catch (\Exception $e) {
                    \Log::error('Payment status check error: ' . $e->getMessage());
                }
            }
        }

        return response()->json([
            'success' => true,
            'status' => $payment->status,
            'order_status' => $order->status,
        ]);
    });

    // ADDRESSES
    Route::get('/addresses', function (Request $request) {
        $addresses = \App\Models\ShippingAddress::where('user_id', $request->user()->id)->orderBy('is_default', 'desc')->get();
        return response()->json(['success' => true, 'data' => $addresses]);
    });

    Route::post('/addresses', function (Request $request) {
        $request->validate([
            'recipient_name' => 'required',
            'recipient_phone' => 'required',
            'address_line_1' => 'required',
            'city' => 'required'
        ]);

        $userId = $request->user()->id;

        // If this is set as default, unset other defaults
        if ($request->is_default) {
            \App\Models\ShippingAddress::where('user_id', $userId)->update(['is_default' => false]);
        }

        $address = \App\Models\ShippingAddress::create([
            'user_id' => $userId,
            'recipient_name' => $request->recipient_name,
            'recipient_phone' => $request->recipient_phone,
            'address_line_1' => $request->address_line_1,
            'address_line_2' => $request->address_line_2,
            'city' => $request->city,
            'state_region' => $request->state_region,
            'country' => $request->country ?? 'Uganda',
            'is_default' => $request->is_default ?? false,
        ]);

        return response()->json(['success' => true, 'data' => $address], 201);
    });

    Route::put('/addresses/{id}', function (Request $request, $id) {
        $address = \App\Models\ShippingAddress::where('user_id', $request->user()->id)->find($id);
        if (!$address) {
            return response()->json(['success' => false, 'message' => 'Address not found'], 404);
        }

        $address->update($request->only([
            'recipient_name', 'recipient_phone', 'address_line_1', 'address_line_2',
            'city', 'state_region', 'country', 'label', 'delivery_instructions'
        ]));

        return response()->json(['success' => true, 'data' => $address]);
    });

    Route::delete('/addresses/{id}', function (Request $request, $id) {
        $address = \App\Models\ShippingAddress::where('user_id', $request->user()->id)->find($id);
        if (!$address) {
            return response()->json(['success' => false, 'message' => 'Address not found'], 404);
        }

        $address->delete();
        return response()->json(['success' => true, 'message' => 'Address deleted']);
    });

    Route::post('/addresses/{id}/set-default', function (Request $request, $id) {
        $userId = $request->user()->id;
        $address = \App\Models\ShippingAddress::where('user_id', $userId)->find($id);
        if (!$address) {
            return response()->json(['success' => false, 'message' => 'Address not found'], 404);
        }

        // Unset other defaults
        \App\Models\ShippingAddress::where('user_id', $userId)->update(['is_default' => false]);
        $address->is_default = true;
        $address->save();

        return response()->json(['success' => true, 'data' => $address]);
    });

    // WALLET
    Route::get('/wallet', function (Request $request) {
        $wallet = \App\Models\BuyerWallet::firstOrCreate(['user_id' => $request->user()->id], ['balance' => 0]);
        return response()->json(['success' => true, 'data' => ['balance' => $wallet->balance, 'currency' => 'UGX']]);
    });

    Route::get('/wallet/transactions', function (Request $request) {
        $wallet = \App\Models\BuyerWallet::where('user_id', $request->user()->id)->first();
        if (!$wallet) {
            return response()->json(['success' => true, 'data' => [], 'balance' => 0]);
        }

        $transactions = \App\Models\WalletTransaction::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'balance' => $wallet->balance,
            'data' => $transactions->map(function ($tx) {
                return [
                    'id' => $tx->id,
                    'type' => $tx->type,
                    'amount' => $tx->amount,
                    'balance_after' => $tx->balance_after,
                    'description' => $tx->description,
                    'reference' => $tx->reference,
                    'created_at' => $tx->created_at,
                ];
            }),
            'meta' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'total' => $transactions->total(),
            ],
        ]);
    });
});

/*
|--------------------------------------------------------------------------
| VENDOR API ROUTES (Authenticated)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum'])->prefix('vendor')->group(function () {

    // ==================== DASHBOARD ====================
    Route::get('/dashboard', function (Request $request) {
        $user = $request->user();
        $vendor = $user->vendorProfile;

        // Check if user has a vendor profile at all
        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'You need to complete vendor onboarding first.',
                'requires_onboarding' => true,
                'vetting_status' => null,
            ], 404);
        }

        // Check if vendor account is deactivated
        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'is_deactivated' => true,
                'message' => 'Your vendor account has been deactivated. If you believe this was a mistake, please contact support or submit an appeal.',
                'support_email' => 'support@bebamart.com',
                'support_phone' => '+256700000000',
            ], 403);
        }

        // Check if vendor is approved - allow dashboard access but with status info
        $isApproved = $vendor->vetting_status === 'approved';
        $isPending = $vendor->vetting_status === 'pending';
        $isRejected = $vendor->vetting_status === 'rejected';

        // Get stats
        $stats = [
            'total_listings' => Listing::where('vendor_profile_id', $vendor->id)->count(),
            'active_listings' => Listing::where('vendor_profile_id', $vendor->id)
                ->where('is_active', true)->count(),
            'total_orders' => Order::where('vendor_profile_id', $vendor->id)->count(),
            'pending_orders' => Order::where('vendor_profile_id', $vendor->id)
                ->where('status', 'pending')->count(),
            'processing_orders' => Order::where('vendor_profile_id', $vendor->id)
                ->where('status', 'processing')->count(),
            'shipped_orders' => Order::where('vendor_profile_id', $vendor->id)
                ->where('status', 'shipped')->count(),
            'delivered_orders' => Order::where('vendor_profile_id', $vendor->id)
                ->where('status', 'delivered')->count(),
            'total_revenue' => Order::where('vendor_profile_id', $vendor->id)
                ->where('status', 'delivered')->sum('total'),
            'monthly_revenue' => Order::where('vendor_profile_id', $vendor->id)
                ->where('status', 'delivered')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('total'),
            'pending_balance' => $vendor->pending_balance ?? 0,
            'available_balance' => $vendor->available_balance ?? 0,
            'average_rating' => $vendor->rating ?? 0,
            'total_reviews' => $vendor->reviews_count ?? 0,
            'total_views' => Listing::where('vendor_profile_id', $vendor->id)->sum('view_count'),
        ];

        // Get recent orders
        $recentOrders = Order::where('vendor_profile_id', $vendor->id)
            ->with(['buyer', 'items.listing'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'recent_orders' => $recentOrders,
            'vetting_status' => $vendor->vetting_status,
            'is_approved' => $isApproved,
            'is_pending' => $isPending,
            'is_rejected' => $isRejected,
            'vetting_notes' => $isRejected ? $vendor->vetting_notes : null,
        ]);
    });

    // ==================== LISTINGS ====================
    Route::prefix('listings')->group(function () {
        
        // Get all vendor listings
        Route::get('/', function (Request $request) {
            $vendor = $request->user()->vendorProfile;
            
            if (!$vendor) {
                return response()->json(['success' => false, 'message' => 'Vendor not found'], 404);
            }

            $query = Listing::where('vendor_profile_id', $vendor->id)
                ->with(['images', 'category']);

            // Filter by status
            if ($request->has('status') && $request->status !== 'all') {
                if ($request->status === 'active') {
                    $query->where('is_active', true);
                } elseif ($request->status === 'inactive') {
                    $query->where('is_active', false);
                } elseif ($request->status === 'pending') {
                    // Pending = newly created inactive listings (no approval_status column exists)
                    $query->where('is_active', false);
                }
            }

            $listings = $query->orderBy('created_at', 'desc')
                ->paginate($request->get('per_page', 20));

            return response()->json([
                'success' => true,
                'data' => $listings->items(),
                'current_page' => $listings->currentPage(),
                'last_page' => $listings->lastPage(),
                'total' => $listings->total(),
            ]);
        });

        // Create new listing
        Route::post('/', function (Request $request) {
            $vendor = $request->user()->vendorProfile;

            if (!$vendor) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must complete vendor onboarding before listing products.',
                    'requires_onboarding' => true,
                ], 403);
            }

            // SECURITY: Check if vendor is approved before allowing product creation
            if ($vendor->vetting_status !== 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Your vendor application is pending approval. You cannot list products until approved.',
                    'vetting_status' => $vendor->vetting_status,
                    'requires_approval' => true,
                ], 403);
            }

            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'price' => 'required|numeric|min:0',
                'category_id' => 'required|exists:categories,id',
                'quantity' => 'nullable|integer|min:1',
                'stock' => 'nullable|integer|min:0',
                'weight' => 'nullable|numeric|min:0',
                'condition' => 'nullable|in:new,used,refurbished',
                'images' => 'nullable|array',
                'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
                'tax_amount' => 'nullable|numeric|min:0',
                'tax_description' => 'nullable|string|max:255',
                // Dynamic attributes (category-specific)
                'attributes' => 'nullable|array',
                'attributes.*' => 'nullable|string|max:255',
            ]);

            // Create slug
            $slug = \Str::slug($request->title) . '-' . uniqid();

            // Create listing
            $listing = Listing::create([
                'vendor_profile_id' => $vendor->id,
                'category_id' => $request->category_id,
                'title' => $request->title,
                'slug' => $slug,
                'description' => $request->description,
                'price' => $request->price,
                'tax_amount' => $request->tax_amount ?? 0,
                'tax_description' => $request->tax_description,
                'stock' => $request->stock ?? $request->quantity ?? 1,
                'weight_kg' => $request->weight ?? null,
                'condition' => $request->condition ?? 'new',
                'attributes' => $request->has('attributes') ? array_filter($request->input('attributes')) : null,
                'is_active' => true,
            ]);

            // Handle images
            if ($request->hasFile('images')) {
                $order = 0;
                foreach ($request->file('images') as $image) {
                    $storedPath = $image->store('listings', 'public');

                    ListingImage::create([
                        'listing_id' => $listing->id,
                        'path' => $storedPath,  // Fixed: was 'image_path', model uses 'path'
                        'is_main' => $order === 0,  // Fixed: was 'is_primary', model uses 'is_main'
                        'order' => $order,
                    ]);
                    $order++;
                }
            }

            // Handle variants (colors/sizes)
            if ($request->has('variations') && is_array($request->variations)) {
                $isFirst = true;
                foreach ($request->variations as $index => $variation) {
                    if (!empty($variation['price'])) {
                        $attributes = [];
                        if (!empty($variation['color'])) $attributes['color'] = $variation['color'];
                        if (!empty($variation['size'])) $attributes['size'] = $variation['size'];

                        ListingVariant::create([
                            'listing_id' => $listing->id,
                            'sku' => $variation['sku'] ?? ($listing->slug . '-' . ($index + 1)),
                            'display_name' => trim(($variation['color'] ?? '') . ' ' . ($variation['size'] ?? '')),
                            'price' => $variation['price'],
                            'sale_price' => $variation['sale_price'] ?? null,
                            'stock' => $variation['stock'] ?? 1,
                            'attributes' => $attributes,
                            'is_default' => $isFirst,
                            'is_active' => true,
                        ]);
                        $isFirst = false;
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Listing created successfully',
                'listing' => $listing->load(['images', 'category', 'variants']),
            ], 201);
        });

        // Get single listing
        Route::get('/{id}', function (Request $request, $id) {
            $vendor = $request->user()->vendorProfile;
            
            $listing = Listing::where('id', $id)
                ->where('vendor_profile_id', $vendor->id)
                ->with(['images', 'category', 'variants'])
                ->first();

            if (!$listing) {
                return response()->json(['success' => false, 'message' => 'Listing not found'], 404);
            }

            return response()->json([
                'success' => true,
                'listing' => $listing,
            ]);
        });

        // Update listing
        Route::put('/{id}', function (Request $request, $id) {
            $vendor = $request->user()->vendorProfile;
            
            $listing = Listing::where('id', $id)
                ->where('vendor_profile_id', $vendor->id)
                ->first();

            if (!$listing) {
                return response()->json(['success' => false, 'message' => 'Listing not found'], 404);
            }

            $request->validate([
                'title' => 'sometimes|required|string|max:255',
                'description' => 'sometimes|required|string',
                'price' => 'sometimes|required|numeric|min:0',
                'category_id' => 'sometimes|required|exists:categories,id',
                'quantity' => 'nullable|integer|min:1',
                'stock' => 'nullable|integer|min:0',
                'condition' => 'nullable|in:new,used,refurbished',
                'tax_amount' => 'nullable|numeric|min:0',
                'tax_description' => 'nullable|string|max:255',
                // Dynamic attributes (category-specific)
                'attributes' => 'nullable|array',
                'attributes.*' => 'nullable|string|max:255',
            ]);

            $updateData = $request->only([
                'title', 'description', 'price', 'category_id', 'condition',
                'tax_amount', 'tax_description'
            ]);

            // Update attributes if provided
            if ($request->has('attributes')) {
                $updateData['attributes'] = array_filter($request->input('attributes'));
            }

            // Map quantity to stock (DB column is 'stock')
            if ($request->has('quantity')) {
                $updateData['stock'] = $request->quantity;
            } elseif ($request->has('stock')) {
                $updateData['stock'] = $request->stock;
            }

            $listing->update($updateData);

            // Handle new images
            if ($request->hasFile('images')) {
                $maxOrder = $listing->images()->max('order') ?? -1;
                foreach ($request->file('images') as $image) {
                    $maxOrder++;
                    $storedPath = $image->store('listings', 'public');

                    ListingImage::create([
                        'listing_id' => $listing->id,
                        'path' => $storedPath,  // Fixed: model uses 'path'
                        'is_main' => false,  // Fixed: model uses 'is_main'
                        'order' => $maxOrder,
                    ]);
                }
            }

            // Handle deleted images
            if ($request->has('delete_images')) {
                ListingImage::whereIn('id', $request->delete_images)
                    ->where('listing_id', $listing->id)
                    ->delete();
            }

            return response()->json([
                'success' => true,
                'message' => 'Listing updated successfully',
                'listing' => $listing->fresh(['images', 'category']),
            ]);
        });

        // Delete listing
        Route::delete('/{id}', function (Request $request, $id) {
            $vendor = $request->user()->vendorProfile;
            
            $listing = Listing::where('id', $id)
                ->where('vendor_profile_id', $vendor->id)
                ->first();

            if (!$listing) {
                return response()->json(['success' => false, 'message' => 'Listing not found'], 404);
            }

            // Delete images from storage
            foreach ($listing->images as $image) {
                \Storage::disk('public')->delete($image->image_path);
            }

            $listing->delete();

            return response()->json([
                'success' => true,
                'message' => 'Listing deleted successfully',
            ]);
        });

        // Toggle listing status (active/inactive)
        Route::post('/{id}/toggle-status', function (Request $request, $id) {
            $vendor = $request->user()->vendorProfile;
            
            $listing = Listing::where('id', $id)
                ->where('vendor_profile_id', $vendor->id)
                ->first();

            if (!$listing) {
                return response()->json(['success' => false, 'message' => 'Listing not found'], 404);
            }

            $listing->is_active = !$listing->is_active;
            $listing->save();

            return response()->json([
                'success' => true,
                'message' => $listing->is_active ? 'Listing activated' : 'Listing deactivated',
                'is_active' => $listing->is_active,
            ]);
        });
    });

    // ==================== ORDERS ====================
    Route::prefix('orders')->group(function () {
        
        // Get all vendor orders
        Route::get('/', function (Request $request) {
            $vendor = $request->user()->vendorProfile;

            if (!$vendor) {
                return response()->json(['success' => false, 'message' => 'Vendor not found'], 404);
            }

            $query = Order::where('vendor_profile_id', $vendor->id)
                ->with(['buyer', 'items.listing']); // Note: shippingAddress is an accessor in $appends, not a relationship

            // Filter by status
            if ($request->has('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            $orders = $query->orderBy('created_at', 'desc')
                ->paginate($request->get('per_page', 20));

            // Transform orders to include payment info at root level
            $transformedOrders = collect($orders->items())->map(function ($order) {
                $orderData = $order->toArray();
                $meta = $order->meta ?? [];
                $orderData['payment_method'] = $meta['payment_method'] ?? null;
                $orderData['payment_status'] = $meta['payment_status'] ?? 'pending';
                $orderData['is_cod'] = isset($meta['payment_method']) && $meta['payment_method'] === 'cash_on_delivery';
                return $orderData;
            });

            return response()->json([
                'success' => true,
                'data' => $transformedOrders,
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'total' => $orders->total(),
            ]);
        });

        // Get single order
        Route::get('/{id}', function (Request $request, $id) {
            $vendor = $request->user()->vendorProfile;

            $order = Order::where('id', $id)
                ->where('vendor_profile_id', $vendor->id)
                ->with(['buyer', 'items.listing.images', 'payments']) // Note: shippingAddress is in $appends
                ->first();

            if (!$order) {
                return response()->json(['success' => false, 'message' => 'Order not found'], 404);
            }

            // Transform order data to include payment info at root level
            $orderData = $order->toArray();
            $meta = $order->meta ?? [];
            $orderData['payment_method'] = $meta['payment_method'] ?? null;
            $orderData['payment_status'] = $meta['payment_status'] ?? ($order->payments->first()?->status ?? 'pending');
            $orderData['is_cod'] = isset($meta['payment_method']) && $meta['payment_method'] === 'cash_on_delivery';
            $orderData['cod_payment_confirmed'] = $meta['cod_payment_confirmed_by_vendor'] ?? false;

            return response()->json([
                'success' => true,
                'order' => $orderData,
            ]);
        });

        // Update order status
        Route::post('/{id}/status', function (Request $request, $id) {
            $vendor = $request->user()->vendorProfile;

            $order = Order::where('id', $id)
                ->where('vendor_profile_id', $vendor->id)
                ->first();

            if (!$order) {
                return response()->json(['success' => false, 'message' => 'Order not found'], 404);
            }

            $request->validate([
                'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
            ]);

            $meta = $order->meta ?? [];
            $isCOD = isset($meta['payment_method']) && $meta['payment_method'] === 'cash_on_delivery';

            // For COD orders, vendor cannot directly mark as "delivered"
            // They must use the confirm-cod-payment endpoint instead
            if ($isCOD && $request->status === 'delivered') {
                return response()->json([
                    'success' => false,
                    'message' => 'For Cash on Delivery orders, please use "Confirm Payment Received" to mark as delivered.',
                    'requires_payment_confirmation' => true,
                ], 400);
            }

            $oldStatus = $order->status;

            // Use the model method for proper timestamp tracking
            $order->updateStatusWithTimestamps($request->status);

            // Send notification to buyer about status change
            try {
                $pushService = new \App\Services\PushNotificationService();
                $statusMessages = [
                    'processing' => ['title' => 'Order Being Processed! 📦', 'body' => "Your order #{$order->order_number} is being prepared"],
                    'shipped' => ['title' => 'Order Shipped! 🚚', 'body' => "Your order #{$order->order_number} is on its way!"],
                    'delivered' => ['title' => 'Order Delivered! 🎉', 'body' => "Your order #{$order->order_number} has been delivered"],
                    'cancelled' => ['title' => 'Order Cancelled 😔', 'body' => "Your order #{$order->order_number} has been cancelled"],
                ];
                $msg = $statusMessages[$request->status] ?? ['title' => 'Order Update 📋', 'body' => "Order #{$order->order_number} status: {$request->status}"];
                $pushService->sendToUser(
                    $order->user_id,
                    'order_update',
                    $msg['title'],
                    $msg['body'],
                    ['route' => '/orders/' . $order->id]
                );
            } catch (\Exception $e) {
                // Non-critical — don't fail the status update
            }

            return response()->json([
                'success' => true,
                'message' => "Order status updated from {$oldStatus} to {$request->status}",
                'order' => $order->fresh(['buyer', 'items']),
            ]);
        });

        // Confirm COD Payment Received (Vendor confirms they received cash payment)
        Route::post('/{id}/confirm-cod-payment', function (Request $request, $id) {
            $vendor = $request->user()->vendorProfile;

            $order = Order::where('id', $id)
                ->where('vendor_profile_id', $vendor->id)
                ->first();

            if (!$order) {
                return response()->json(['success' => false, 'message' => 'Order not found'], 404);
            }

            // Check if order is shipped
            if ($order->status !== 'shipped') {
                return response()->json([
                    'success' => false,
                    'message' => 'Order must be shipped before confirming payment',
                ], 400);
            }

            // Check if this is a COD order
            $meta = $order->meta ?? [];
            $isCOD = isset($meta['payment_method']) && $meta['payment_method'] === 'cash_on_delivery';

            if (!$isCOD) {
                return response()->json([
                    'success' => false,
                    'message' => 'This order is not Cash on Delivery',
                ], 400);
            }

            // Check if already confirmed
            if (isset($meta['cod_payment_confirmed_by_vendor']) && $meta['cod_payment_confirmed_by_vendor']) {
                return response()->json([
                    'success' => false,
                    'message' => 'COD payment has already been confirmed',
                ], 400);
            }

            try {
                \DB::beginTransaction();

                // Calculate delivery metrics
                $deliveryTimeDays = 0;
                if ($order->shipped_at) {
                    $deliveryTimeDays = $order->shipped_at->diffInDays(now());
                } else {
                    $deliveryTimeDays = $order->created_at->diffInDays(now());
                }

                // Calculate delivery score
                $deliveryScore = match(true) {
                    $deliveryTimeDays <= 1 => 100,
                    $deliveryTimeDays <= 2 => 95,
                    $deliveryTimeDays <= 3 => 90,
                    $deliveryTimeDays <= 5 => 80,
                    $deliveryTimeDays <= 7 => 70,
                    $deliveryTimeDays <= 10 => 60,
                    $deliveryTimeDays <= 14 => 50,
                    default => 40,
                };

                // Update order - mark as delivered with COD payment confirmed
                $order->update([
                    'status' => 'delivered',
                    'delivered_at' => now(),
                    'delivery_time_days' => $deliveryTimeDays,
                    'delivery_score' => $deliveryScore,
                    'meta' => array_merge($meta, [
                        'cod_payment_confirmed_by_vendor' => true,
                        'cod_payment_confirmed_at' => now()->toDateTimeString(),
                        'cod_payment_amount' => $order->total,
                        'payment_type' => 'cash_on_delivery',
                        // COD goes directly to vendor, NOT to escrow
                        'payment_method_note' => 'Cash received directly by vendor - no escrow',
                    ]),
                ]);

                // Update the payment record (mark as completed)
                $payment = $order->payments()->where('provider', 'cash')->first();
                if ($payment) {
                    $payment->update([
                        'status' => 'completed',
                        'meta' => array_merge($payment->meta ?? [], [
                            'paid_at' => now()->toDateTimeString(),
                            'confirmed_by_vendor' => true,
                            'vendor_confirmed_at' => now()->toDateTimeString(),
                        ]),
                    ]);
                }

                // DO NOT create escrow for COD - vendor already has the cash
                // No wallet transaction needed - cash was received directly

                \DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'COD payment confirmed and order marked as delivered',
                    'order' => $order->fresh(['buyer', 'items']),
                ]);

            } catch (\Exception $e) {
                \DB::rollBack();
                \Log::error('COD payment confirmation failed', [
                    'order_id' => $order->id,
                    'vendor_id' => $vendor->id,
                    'error' => $e->getMessage(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to confirm payment. Please try again.',
                ], 500);
            }
        });
    });

    // ==================== PROFILE ====================
    Route::get('/profile', function (Request $request) {
        $vendor = $request->user()->vendorProfile;
        
        if (!$vendor) {
            return response()->json(['success' => false, 'message' => 'Vendor profile not found'], 404);
        }

        return response()->json([
            'success' => true,
            'profile' => $vendor,
        ]);
    });

    Route::put('/profile', function (Request $request) {
        $vendor = $request->user()->vendorProfile;
        
        if (!$vendor) {
            return response()->json(['success' => false, 'message' => 'Vendor profile not found'], 404);
        }

        $request->validate([
            'business_name' => 'sometimes|string|max:255',
            'business_description' => 'sometimes|string',
            'business_address' => 'sometimes|string',
            'business_phone' => 'sometimes|string|max:20',
            'business_email' => 'sometimes|email',
            'logo' => 'sometimes|image|mimes:jpeg,png,jpg|max:1024',
            'banner' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Update text fields
        $vendor->fill($request->only([
            'business_name', 'business_description', 'business_address', 
            'business_phone', 'business_email'
        ]));

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo
            if ($vendor->logo) {
                \Storage::disk('public')->delete($vendor->logo);
            }
            $vendor->logo = $request->file('logo')->store('vendor/logos', 'public');
        }

        // Handle banner upload
        if ($request->hasFile('banner')) {
            // Delete old banner
            if ($vendor->banner) {
                \Storage::disk('public')->delete($vendor->banner);
            }
            $vendor->banner = $request->file('banner')->store('vendor/banners', 'public');
        }

        $vendor->save();

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'profile' => $vendor,
        ]);
    });

    // ==================== ANALYTICS ====================
    Route::get('/analytics', function (Request $request) {
        $vendor = $request->user()->vendorProfile;
        
        if (!$vendor) {
            return response()->json(['success' => false, 'message' => 'Vendor not found'], 404);
        }

        // Get analytics data
        $analytics = [
            'total_views' => Listing::where('vendor_profile_id', $vendor->id)->sum('view_count'),
            'total_orders' => Order::where('vendor_profile_id', $vendor->id)->count(),
            'delivered_orders' => Order::where('vendor_profile_id', $vendor->id)
                ->where('status', 'delivered')->count(),
            'avg_rating' => $vendor->rating ?? 0,
            
            // Sales by day (last 7 days)
            'sales_by_day' => Order::where('vendor_profile_id', $vendor->id)
                ->where('status', 'delivered')
                ->where('created_at', '>=', now()->subDays(7))
                ->selectRaw('DATE(created_at) as date, SUM(total) as total, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
            
            // Top products
            'top_products' => Listing::where('vendor_profile_id', $vendor->id)
                ->orderBy('view_count', 'desc')
                ->take(5)
                ->get(['id', 'title', 'view_count', 'price']),
        ];

        return response()->json([
            'success' => true,
            'analytics' => $analytics,
        ]);
    });

    // Vendor Wallet
    Route::get('/wallet', function (Request $request) {
        $vendor = $request->user()->vendorProfile;

        if (!$vendor) {
            return response()->json(['success' => false, 'message' => 'Vendor not found'], 404);
        }

        // Calculate available balance from delivered orders minus payouts
        $totalEarnings = Order::where('vendor_profile_id', $vendor->id)
            ->where('status', 'delivered')
            ->sum('total');

        // Assuming there's a payouts table or tracking in meta
        $totalPayouts = 0; // Could be from a payouts table
        $pendingBalance = Order::where('vendor_profile_id', $vendor->id)
            ->whereIn('status', ['processing', 'shipped'])
            ->sum('total');

        return response()->json([
            'success' => true,
            'data' => [
                'available_balance' => $totalEarnings - $totalPayouts,
                'pending_balance' => $pendingBalance,
                'total_earnings' => $totalEarnings,
                'total_payouts' => $totalPayouts,
            ],
        ]);
    });

    // Vendor Transactions
    Route::get('/transactions', function (Request $request) {
        $vendor = $request->user()->vendorProfile;

        if (!$vendor) {
            return response()->json(['success' => false, 'message' => 'Vendor not found'], 404);
        }

        // Get completed orders as transactions (sales)
        $transactions = Order::where('vendor_profile_id', $vendor->id)
            ->whereIn('status', ['delivered', 'processing', 'shipped'])
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'type' => 'sale',
                    'description' => "Order #{$order->id}",
                    'amount' => $order->total,
                    'status' => $order->status === 'delivered' ? 'completed' : 'pending',
                    'created_at' => $order->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $transactions,
        ]);
    });

    // ==================== VENDOR SERVICES ====================
    Route::prefix('services')->group(function () {

        // Get all vendor services
        Route::get('/', function (Request $request) {
            $vendor = $request->user()->vendorProfile;

            if (!$vendor) {
                return response()->json(['success' => false, 'message' => 'Vendor not found'], 404);
            }

            $query = \App\Models\VendorService::where('vendor_profile_id', $vendor->id)
                ->with('category');

            if ($request->filled('status')) {
                $query->where('is_active', $request->status === 'active');
            }

            $services = $query->latest()->paginate($request->get('per_page', 20));

            return response()->json([
                'success' => true,
                'data' => $services->items(),
                'current_page' => $services->currentPage(),
                'last_page' => $services->lastPage(),
                'total' => $services->total(),
            ]);
        });

        // Create new service
        Route::post('/', function (Request $request) {
            $vendor = $request->user()->vendorProfile;

            if (!$vendor) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must complete vendor onboarding before adding services.',
                    'requires_onboarding' => true,
                ], 403);
            }

            if ($vendor->vetting_status !== 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Your vendor application is pending approval.',
                    'requires_approval' => true,
                ], 403);
            }

            $request->validate([
                'title' => 'required|string|max:255',
                'service_category_id' => 'nullable|exists:service_categories,id',
                'description' => 'required|string|max:10000',
                'pricing_type' => 'required|in:fixed,hourly,negotiable,starting_from,free_quote',
                'price' => 'nullable|numeric|min:0',
                'price_max' => 'nullable|numeric|min:0',
                'duration' => 'nullable|string|max:100',
                'location' => 'nullable|string|max:255',
                'city' => 'required|string|max:100',
                'is_mobile' => 'boolean',
                'features' => 'nullable|string',
                'images.*' => 'nullable|image|max:5120',
            ]);

            // Handle images
            $images = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $images[] = $image->store('vendor-services', 'public');
                }
            }

            // Convert features text to array
            $features = $request->features ? array_filter(array_map('trim', explode("\n", $request->features))) : null;

            $service = \App\Models\VendorService::create([
                'vendor_profile_id' => $vendor->id,
                'service_category_id' => $request->service_category_id,
                'title' => $request->title,
                'slug' => \Illuminate\Support\Str::slug($request->title) . '-' . \Illuminate\Support\Str::random(4),
                'description' => $request->description,
                'pricing_type' => $request->pricing_type,
                'price' => $request->price,
                'price_max' => $request->price_max,
                'duration' => $request->duration,
                'location' => $request->location ?? $vendor->business_address,
                'city' => $request->city,
                'is_mobile' => $request->boolean('is_mobile'),
                'features' => $features,
                'images' => $images,
                'is_active' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Service created successfully!',
                'service' => $service,
            ], 201);
        });

        // Toggle service status
        Route::post('/{id}/toggle-status', function (Request $request, $id) {
            $vendor = $request->user()->vendorProfile;
            $service = \App\Models\VendorService::where('vendor_profile_id', $vendor->id)->findOrFail($id);

            $service->is_active = !$service->is_active;
            $service->save();

            return response()->json([
                'success' => true,
                'message' => 'Service status updated.',
                'is_active' => $service->is_active,
            ]);
        });

        // Delete service
        Route::delete('/{id}', function (Request $request, $id) {
            $vendor = $request->user()->vendorProfile;
            $service = \App\Models\VendorService::where('vendor_profile_id', $vendor->id)->findOrFail($id);

            // Delete images
            if ($service->images) {
                foreach ($service->images as $image) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($image);
                }
            }

            $service->delete();

            return response()->json([
                'success' => true,
                'message' => 'Service deleted.',
            ]);
        });
    });

    // ==================== VENDOR SERVICE REQUESTS ====================
    Route::prefix('service-requests')->group(function () {

        // List vendor's service requests
        Route::get('/', function (Request $request) {
            $vendor = $request->user()->vendorProfile;
            if (!$vendor) return response()->json(['success' => false, 'message' => 'Vendor not found'], 404);

            $query = \App\Models\ServiceRequest::where('vendor_profile_id', $vendor->id)
                ->with(['service:id,title,pricing_type,price', 'user:id,name,phone,email'])
                ->latest();

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $requests = $query->paginate($request->get('per_page', 20));

            $statusCounts = [
                'pending'     => \App\Models\ServiceRequest::where('vendor_profile_id', $vendor->id)->where('status', 'pending')->count(),
                'quoted'      => \App\Models\ServiceRequest::where('vendor_profile_id', $vendor->id)->where('status', 'quoted')->count(),
                'accepted'    => \App\Models\ServiceRequest::where('vendor_profile_id', $vendor->id)->where('status', 'accepted')->count(),
                'in_progress' => \App\Models\ServiceRequest::where('vendor_profile_id', $vendor->id)->where('status', 'in_progress')->count(),
                'completed'   => \App\Models\ServiceRequest::where('vendor_profile_id', $vendor->id)->where('status', 'completed')->count(),
            ];

            return response()->json([
                'success'       => true,
                'data'          => $requests->items(),
                'status_counts' => $statusCounts,
                'current_page'  => $requests->currentPage(),
                'last_page'     => $requests->lastPage(),
                'total'         => $requests->total(),
            ]);
        });

        // Get single request
        Route::get('/{id}', function (Request $request, $id) {
            $vendor = $request->user()->vendorProfile;
            if (!$vendor) return response()->json(['success' => false, 'message' => 'Vendor not found'], 404);

            $sr = \App\Models\ServiceRequest::where('vendor_profile_id', $vendor->id)
                ->with(['service', 'user:id,name,phone,email', 'review'])
                ->findOrFail($id);

            return response()->json(['success' => true, 'data' => $sr]);
        });

        // Submit quote
        Route::post('/{id}/quote', function (Request $request, $id) {
            $vendor = $request->user()->vendorProfile;
            $sr = \App\Models\ServiceRequest::where('vendor_profile_id', $vendor->id)
                ->where('status', 'pending')->findOrFail($id);

            $request->validate([
                'quoted_price' => 'required|numeric|min:0',
                'vendor_notes' => 'nullable|string|max:1000',
            ]);

            $notes = $sr->vendor_notes ?? [];
            $notes[] = ['note' => $request->vendor_notes, 'at' => now()->toDateTimeString()];

            $sr->update([
                'quoted_price' => $request->quoted_price,
                'vendor_notes' => $notes,
                'status'       => 'quoted',
            ]);

            return response()->json(['success' => true, 'message' => 'Quote submitted.', 'data' => $sr->fresh()]);
        });

        // Update status
        Route::post('/{id}/status', function (Request $request, $id) {
            $vendor = $request->user()->vendorProfile;
            $sr = \App\Models\ServiceRequest::where('vendor_profile_id', $vendor->id)->findOrFail($id);

            $request->validate(['status' => 'required|in:in_progress,completed,cancelled']);

            $allowed = [
                'accepted'    => ['in_progress', 'cancelled'],
                'in_progress' => ['completed', 'cancelled'],
            ];

            if (!isset($allowed[$sr->status]) || !in_array($request->status, $allowed[$sr->status])) {
                return response()->json(['success' => false, 'message' => 'Invalid status transition'], 422);
            }

            $update = ['status' => $request->status];
            if ($request->status === 'completed') $update['completed_at'] = now();

            $sr->update($update);

            return response()->json(['success' => true, 'message' => 'Status updated.', 'data' => $sr->fresh()]);
        });
    });

    // Get service categories
    Route::get('/service-categories', function () {
        $categories = \App\Models\ServiceCategory::active()
            ->forServices()
            ->parents()
            ->with(['children' => function($q) {
                $q->active()->forServices()->orderBy('name');
            }])
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories->map(function($cat) {
                return [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'slug' => $cat->slug,
                    'icon' => $cat->icon,
                    'children' => $cat->children->map(function($child) {
                        return [
                            'id' => $child->id,
                            'name' => $child->name,
                            'slug' => $child->slug,
                            'icon' => $child->icon,
                        ];
                    }),
                ];
            }),
        ]);
    });
});

// ==================== VENDOR SUBSCRIPTION ROUTES ====================
Route::middleware('auth:sanctum')->prefix('vendor/subscription')->group(function () {
    Route::get('/', [\App\Http\Controllers\Marketplace\SubscriptionController::class, 'current']);
    Route::post('/subscribe', [\App\Http\Controllers\Marketplace\SubscriptionController::class, 'subscribe']);
    Route::post('/cancel', [\App\Http\Controllers\Marketplace\SubscriptionController::class, 'cancel']);
    Route::post('/toggle-auto-renew', [\App\Http\Controllers\Marketplace\SubscriptionController::class, 'toggleAutoRenew']);
    Route::get('/history', [\App\Http\Controllers\Marketplace\SubscriptionController::class, 'paymentHistory']);
});

// Subscription payment callbacks (no auth - external redirects)
Route::get('/vendor/subscription/payment-callback', [\App\Http\Controllers\Marketplace\SubscriptionController::class, 'paymentCallback']);
Route::match(['get', 'post'], '/vendor/subscription/ipn', [\App\Http\Controllers\Marketplace\SubscriptionController::class, 'ipn']);

// ==================== CHAT/MESSAGING ROUTES ====================
Route::middleware('auth:sanctum')->prefix('chat')->group(function () {

    // Get all conversations for current user (buyer or vendor)
    Route::get('/conversations', function (Request $request) {
        $user = $request->user();
        $isVendor = $user->role === 'vendor_local' || $user->role === 'vendor_international';

        // Get vendor profile ID if user is a vendor
        $vendorProfileId = null;
        if ($isVendor) {
            $vendorProfile = \DB::table('vendor_profiles')->where('user_id', $user->id)->first();
            $vendorProfileId = $vendorProfile ? $vendorProfile->id : null;
        }

        // Fetch ALL active conversations where user is EITHER buyer OR seller
        $allConversations = \DB::table('conversations')
            ->where('status', 'active')
            ->where(function ($query) use ($user, $isVendor, $vendorProfileId) {
                // Always check if user is the buyer
                $query->where('buyer_id', $user->id);

                // Also check if user is the seller (if they have a vendor profile)
                if ($isVendor && $vendorProfileId) {
                    $query->orWhere('vendor_profile_id', $vendorProfileId);
                }
            });

        $conversations = $allConversations->orderBy('last_message_at', 'desc')->get();

        // Group by participants in PHP to be absolutely sure - picks the latest one for each pair
        $grouped = $conversations->groupBy(function ($conv) {
            // Ensure grouping is based on the pair, regardless of who is buyer/vendor
            // But since one is always 'me', we just need the other person's ID type
            return $conv->buyer_id . '-' . $conv->vendor_profile_id;
        })->map(function ($group) {
            return $group->first(); // Conversations are already sorted by last_message_at desc
        })->values();

        // Enrich with participant info and last message
        $enriched = $grouped->map(function ($conv) use ($user, $isVendor, $vendorProfileId) {
            // Determine if user is the seller in THIS specific conversation
            $isSellerInConv = $isVendor && $vendorProfileId && $conv->vendor_profile_id == $vendorProfileId;

            // Get other participant - show buyer if user is seller, show seller if user is buyer
            if ($isSellerInConv) {
                $otherUser = \DB::table('users')->where('id', $conv->buyer_id)->first();
                $participantName = $otherUser->name ?? 'Buyer';
                $participantAvatar = $otherUser->avatar ?? null;
            } else {
                $vendorProfile = \DB::table('vendor_profiles')->where('id', $conv->vendor_profile_id)->first();
                $participantName = $vendorProfile->business_name ?? 'Vendor';
                $participantAvatar = $vendorProfile->logo ?? null;
            }

            // Get last message
            $lastMessage = \DB::table('messages')
                ->where('conversation_id', $conv->id)
                ->where('is_deleted', false)
                ->orderBy('created_at', 'desc')
                ->first();

            // Get unread count
            $unreadCount = \DB::table('messages')
                ->where('conversation_id', $conv->id)
                ->where('sender_id', '!=', $user->id)
                ->whereNull('read_at')
                ->where('is_deleted', false)
                ->count();

            // Get listing info if linked
            $listing = null;
            if ($conv->listing_id) {
                $listingData = \DB::table('listings')->where('id', $conv->listing_id)->first();
                if ($listingData) {
                    // Get first image from listing_images table
                    $listingImage = \DB::table('listing_images')
                        ->where('listing_id', $conv->listing_id)
                        ->first();
                    $listing = [
                        'id' => $listingData->id,
                        'title' => $listingData->title,
                        'image' => $listingImage ? $listingImage->path : null,
                    ];
                }
            }

            return [
                'id' => $conv->id,
                'participant_name' => $participantName,
                'participant_avatar' => $participantAvatar,
                'listing' => $listing,
                'subject' => $conv->subject,
                'last_message' => $lastMessage ? [
                    'body' => $lastMessage->body,
                    'type' => $lastMessage->type,
                    'is_mine' => $lastMessage->sender_id == $user->id,
                    'created_at' => $lastMessage->created_at,
                ] : null,
                'unread_count' => $unreadCount,
                'last_message_at' => $conv->last_message_at,
                'created_at' => $conv->created_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $enriched,
        ]);
    });

    // Get or create conversation with vendor (for buyers)
    Route::post('/conversations', function (Request $request) {
        $request->validate([
            'vendor_profile_id' => 'required|exists:vendor_profiles,id',
            'listing_id' => 'nullable|exists:listings,id',
            'initial_message' => 'nullable|string|max:2000',
        ]);

        $user = $request->user();
        $vendorProfileId = $request->vendor_profile_id;
        $listingId = $request->listing_id;

        // Prevent users from messaging their own vendor profile
        $targetVendor = \DB::table('vendor_profiles')->where('id', $vendorProfileId)->first();
        if ($targetVendor && $targetVendor->user_id == $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot message yourself',
            ], 400);
        }

        try {
        // Check if conversation already exists between these participants
        $existing = \DB::table('conversations')
            ->where('buyer_id', $user->id)
            ->where('vendor_profile_id', $vendorProfileId)
            ->first();

        if ($existing) {
            // Reactivate if archived
            if ($existing->status !== 'active') {
                \DB::table('conversations')
                    ->where('id', $existing->id)
                    ->update(['status' => 'active']);
            }
            $conversationId = $existing->id;
        } else {
            // Create new conversation
            $subject = null;
            if ($listingId) {
                $listing = \DB::table('listings')->where('id', $listingId)->first();
                $subject = $listing ? "Inquiry about: {$listing->title}" : null;
            }

            $conversationId = \DB::table('conversations')->insertGetId([
                'buyer_id' => $user->id,
                'vendor_profile_id' => $vendorProfileId,
                'listing_id' => $listingId,
                'subject' => $subject,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Add automated safety message for new conversations
            \DB::table('messages')->insert([
                'conversation_id' => $conversationId,
                'sender_id' => null, // null indicates system message
                'body' => 'Avoid paying in advance! Even for delivery',
                'type' => 'system',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Send initial message if provided
        if ($request->initial_message) {
            \DB::table('messages')->insert([
                'conversation_id' => $conversationId,
                'sender_id' => $user->id,
                'body' => $request->initial_message,
                'type' => 'text',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            \DB::table('conversations')
                ->where('id', $conversationId)
                ->update(['last_message_at' => now()]);
        }

        return response()->json([
            'success' => true,
            'conversation_id' => $conversationId,
            'message' => $existing ? 'Existing conversation found' : 'Conversation created',
        ]);
        } catch (\Exception $e) {
            \Log::error('Failed to start conversation: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to start conversation. Please try again.',
            ], 500);
        }
    });

    // Vendor starts conversation with buyer (for order inquiries)
    Route::post('/conversations/with-buyer', function (Request $request) {
        $request->validate([
            'buyer_id' => 'required|exists:users,id',
            'initial_message' => 'nullable|string|max:2000',
            'subject' => 'nullable|string|max:255',
        ]);

        $user = $request->user();
        $isVendor = $user->role === 'vendor_local' || $user->role === 'vendor_international';

        if (!$isVendor) {
            return response()->json([
                'success' => false,
                'message' => 'Only vendors can use this endpoint',
            ], 403);
        }

        // Get vendor profile
        $vendorProfile = \DB::table('vendor_profiles')->where('user_id', $user->id)->first();
        if (!$vendorProfile) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor profile not found',
            ], 404);
        }

        $buyerId = $request->buyer_id;

        // Prevent vendor from messaging themselves
        if ($buyerId == $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot message yourself',
            ], 400);
        }

        // Check if conversation already exists
        $existing = \DB::table('conversations')
            ->where('buyer_id', $buyerId)
            ->where('vendor_profile_id', $vendorProfile->id)
            ->first();

        if ($existing) {
            // Reactivate if archived
            if ($existing->status !== 'active') {
                \DB::table('conversations')
                    ->where('id', $existing->id)
                    ->update(['status' => 'active']);
            }
            $conversationId = $existing->id;
        } else {
            // Create new conversation
            $conversationId = \DB::table('conversations')->insertGetId([
                'buyer_id' => $buyerId,
                'vendor_profile_id' => $vendorProfile->id,
                'listing_id' => null,
                'subject' => $request->subject,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Add automated safety message for new conversations
            \DB::table('messages')->insert([
                'conversation_id' => $conversationId,
                'sender_id' => null,
                'body' => 'Avoid paying in advance! Even for delivery',
                'type' => 'system',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Send initial message if provided
        if ($request->initial_message) {
            \DB::table('messages')->insert([
                'conversation_id' => $conversationId,
                'sender_id' => $user->id,
                'body' => $request->initial_message,
                'type' => 'text',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            \DB::table('conversations')
                ->where('id', $conversationId)
                ->update(['last_message_at' => now()]);
        }

        return response()->json([
            'success' => true,
            'conversation_id' => $conversationId,
            'message' => $existing ? 'Existing conversation found' : 'Conversation created',
        ]);
    });

    // Get messages for a conversation
    Route::get('/conversations/{id}/messages', function (Request $request, $id) {
        $user = $request->user();
        $isVendor = $user->role === 'vendor_local' || $user->role === 'vendor_international';

        // Get vendor profile ID if user is a vendor
        $vendorProfileId = null;
        if ($isVendor) {
            $vendorProfile = \DB::table('vendor_profiles')->where('user_id', $user->id)->first();
            $vendorProfileId = $vendorProfile ? $vendorProfile->id : null;
        }

        // Verify user has access to this conversation
        $conversation = \DB::table('conversations')->where('id', $id)->first();

        if (!$conversation) {
            return response()->json(['success' => false, 'message' => 'Conversation not found'], 404);
        }

        // User has access if they're EITHER the buyer OR the vendor (seller)
        $isBuyer = $conversation->buyer_id == $user->id;
        $isSeller = $isVendor && $vendorProfileId && $conversation->vendor_profile_id == $vendorProfileId;

        if (!$isBuyer && !$isSeller) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        // Get messages
        $messages = \DB::table('messages')
            ->where('conversation_id', $id)
            ->where('is_deleted', false)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($msg) use ($user) {
                return [
                    'id' => $msg->id,
                    'body' => $msg->body,
                    'type' => $msg->type,
                    'attachment_path' => $msg->attachment_path,
                    'attachment_name' => $msg->attachment_name,
                    'is_mine' => $msg->sender_id == $user->id,
                    'read_at' => $msg->read_at,
                    'created_at' => $msg->created_at,
                ];
            });

        // Mark messages as read
        \DB::table('messages')
            ->where('conversation_id', $id)
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        // Get conversation details - show the OTHER participant
        // If user is the seller in this conversation, show buyer info
        // If user is the buyer in this conversation, show seller info
        if ($isSeller) {
            $otherUser = \DB::table('users')->where('id', $conversation->buyer_id)->first();
            $participantName = $otherUser->name ?? 'Buyer';
            $participantAvatar = $otherUser->avatar ?? null;
        } else {
            $vendorProfile = \DB::table('vendor_profiles')->where('id', $conversation->vendor_profile_id)->first();
            $participantName = $vendorProfile->business_name ?? 'Vendor';
            $participantAvatar = $vendorProfile->logo ?? null;
        }

        return response()->json([
            'success' => true,
            'conversation' => [
                'id' => $conversation->id,
                'participant_name' => $participantName,
                'participant_avatar' => $participantAvatar,
                'subject' => $conversation->subject,
                'listing_id' => $conversation->listing_id,
            ],
            'messages' => $messages,
        ]);
    });

    // Send a message
    Route::post('/conversations/{id}/messages', function (Request $request, $id) {
        $request->validate([
            'body' => 'required_without:attachment|string|max:2000',
            'attachment' => 'nullable|file|max:5120', // 5MB max
        ]);

        $user = $request->user();
        $isVendor = $user->role === 'vendor_local' || $user->role === 'vendor_international';

        // Get vendor profile ID if user is a vendor
        $vendorProfileId = null;
        if ($isVendor) {
            $vendorProfile = \DB::table('vendor_profiles')->where('user_id', $user->id)->first();
            $vendorProfileId = $vendorProfile ? $vendorProfile->id : null;
        }

        // Verify access
        $conversation = \DB::table('conversations')->where('id', $id)->first();

        if (!$conversation) {
            return response()->json(['success' => false, 'message' => 'Conversation not found'], 404);
        }

        // User has access if they're EITHER the buyer OR the vendor (seller)
        $isBuyer = $conversation->buyer_id == $user->id;
        $isSeller = $isVendor && $vendorProfileId && $conversation->vendor_profile_id == $vendorProfileId;

        if (!$isBuyer && !$isSeller) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        // Handle attachment
        $attachmentPath = null;
        $attachmentName = null;
        $type = 'text';

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentPath = $file->store('chat_attachments', 'public');
            $attachmentName = $file->getClientOriginalName();

            // Determine type
            $mimeType = $file->getMimeType();
            if (str_starts_with($mimeType, 'image/')) {
                $type = 'image';
            } else {
                $type = 'file';
            }
        }

        // Create message
        $messageId = \DB::table('messages')->insertGetId([
            'conversation_id' => $id,
            'sender_id' => $user->id,
            'body' => $request->body ?? ($type === 'image' ? 'Sent an image' : 'Sent a file'),
            'type' => $type,
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Update conversation last_message_at
        \DB::table('conversations')
            ->where('id', $id)
            ->update(['last_message_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => [
                'id' => $messageId,
                'body' => $request->body ?? ($type === 'image' ? 'Sent an image' : 'Sent a file'),
                'type' => $type,
                'attachment_path' => $attachmentPath,
                'attachment_name' => $attachmentName,
                'is_mine' => true,
                'created_at' => now()->toDateTimeString(),
            ],
        ]);
    });

    // Get unread message count
    Route::get('/unread-count', function (Request $request) {
        $user = $request->user();
        $isVendor = $user->role === 'vendor_local' || $user->role === 'vendor_international';

        // Get vendor profile ID if user is a vendor
        $vendorProfileId = null;
        if ($isVendor) {
            $vendorProfile = \DB::table('vendor_profiles')->where('user_id', $user->id)->first();
            $vendorProfileId = $vendorProfile ? $vendorProfile->id : null;
        }

        // Get user's conversation IDs
        if ($isVendor && $vendorProfileId) {
            $conversationIds = \DB::table('conversations')
                ->where('vendor_profile_id', $vendorProfileId)
                ->where('status', 'active')
                ->pluck('id');
        } else {
            $conversationIds = \DB::table('conversations')
                ->where('buyer_id', $user->id)
                ->where('status', 'active')
                ->pluck('id');
        }

        $unreadCount = \DB::table('messages')
            ->whereIn('conversation_id', $conversationIds)
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->where('is_deleted', false)
            ->count();

        return response()->json([
            'success' => true,
            'unread_count' => $unreadCount,
        ]);
    });
});
