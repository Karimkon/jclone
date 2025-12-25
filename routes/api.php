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

// Authentication
Route::post('/login', function (Request $request) {
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
            'created_at' => $user->created_at,
            'vendor_profile' => $user->vendorProfile ? [
                'id' => $user->vendorProfile->id,
                'user_id' => $user->vendorProfile->user_id,
                'business_name' => $user->vendorProfile->business_name ?? $user->vendorProfile->store_name,
                'store_name' => $user->vendorProfile->store_name,
                'vetting_status' => $user->vendorProfile->vetting_status,
            ] : null,
        ],
    ]);
});

Route::post('/register', function (Request $request) {
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8|confirmed',
        'phone' => 'nullable|string|max:20',
        'role' => 'nullable|in:buyer,vendor_local,vendor_international',
    ]);

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'phone' => $request->phone ?? '',
        'role' => $request->role ?? 'buyer',
        'is_verified' => false,
    ]);

    // Generate and send OTP
    $otp = $user->generateOtp();

    // Send OTP email
    try {
        \Mail::raw("Your BebaMart verification code is: $otp\n\nThis code expires in 10 minutes.", function ($message) use ($user) {
            $message->to($user->email)
                    ->subject('BebaMart - Email Verification Code');
        });
    } catch (\Exception $e) {
        \Log::error('Failed to send OTP email: ' . $e->getMessage());
    }

    return response()->json([
        'success' => true,
        'message' => 'Registration successful. Please verify your email with the OTP sent.',
        'requires_verification' => true,
        'user_id' => $user->id,
        'email' => $user->email,
    ], 201);
});

// Google Authentication
Route::post('/auth/google', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'name' => 'required|string|max:255',
        'google_id' => 'required|string',
        'avatar' => 'nullable|string',
        'id_token' => 'nullable|string',
        'access_token' => 'nullable|string',
    ]);

    // Find existing user or create new one
    $user = User::where('email', $request->email)->first();

    if (!$user) {
        // Create new user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make(\Str::random(32)), // Random password for Google users
            'phone' => '',
            'role' => 'buyer',
            'is_verified' => true, // Google accounts are verified
            'email_verified_at' => now(),
            'google_id' => $request->google_id,
        ]);
    } else {
        // Update Google ID if not set
        if (!$user->google_id) {
            $user->google_id = $request->google_id;
            $user->save();
        }
        // Mark as verified if not already
        if (!$user->email_verified_at) {
            $user->email_verified_at = now();
            $user->is_verified = true;
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
            'avatar' => $request->avatar ?? ($user->avatar ? asset('storage/' . $user->avatar) : null),
            'email_verified_at' => $user->email_verified_at,
            'created_at' => $user->created_at,
            'vendor_profile' => $user->vendorProfile ? [
                'id' => $user->vendorProfile->id,
                'user_id' => $user->vendorProfile->user_id,
                'business_name' => $user->vendorProfile->business_name ?? $user->vendorProfile->store_name,
                'store_name' => $user->vendorProfile->store_name,
                'vetting_status' => $user->vendorProfile->vetting_status,
            ] : null,
        ],
    ]);
});

// Verify OTP
Route::post('/verify-otp', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'otp' => 'required|string|size:6',
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'User not found',
        ], 404);
    }

    if ($user->is_verified) {
        return response()->json([
            'success' => false,
            'message' => 'Email already verified',
        ], 400);
    }

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

    // Create auth token
    $token = $user->createToken('mobile-app')->plainTextToken;

    return response()->json([
        'success' => true,
        'message' => 'Email verified successfully',
        'token' => $token,
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $user->role,
            'avatar' => $user->avatar,
            'is_verified' => $user->is_verified,
            'created_at' => $user->created_at,
            'vendor_profile' => $user->vendorProfile ? [
                'id' => $user->vendorProfile->id,
                'business_name' => $user->vendorProfile->business_name,
                'vetting_status' => $user->vendorProfile->vetting_status,
            ] : null,
        ],
    ]);
});

// Resend OTP
Route::post('/resend-otp', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'User not found',
        ], 404);
    }

    if ($user->is_verified) {
        return response()->json([
            'success' => false,
            'message' => 'Email already verified',
        ], 400);
    }

    // Generate new OTP
    $otp = $user->generateOtp();

    // Send OTP email
    try {
        \Mail::raw("Your BebaMart verification code is: $otp\n\nThis code expires in 10 minutes.", function ($message) use ($user) {
            $message->to($user->email)
                    ->subject('BebaMart - Email Verification Code');
        });

        return response()->json([
            'success' => true,
            'message' => 'OTP sent successfully',
        ]);
    } catch (\Exception $e) {
        \Log::error('Failed to send OTP email: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Failed to send OTP. Please try again.',
        ], 500);
    }
});

// Google Sign-In (for mobile apps)
Route::post('/auth/google', function (Request $request) {
    $request->validate([
        'id_token' => 'required_without:access_token|string',
        'access_token' => 'required_without:id_token|string',
        'email' => 'required|email',
        'name' => 'required|string',
        'google_id' => 'required|string',
        'avatar' => 'nullable|string',
    ]);

    try {
        // Check if user already exists with this Google ID
        $user = User::where('google_id', $request->google_id)->first();

        if (!$user) {
            // Check if user exists with this email
            $user = User::where('email', $request->email)->first();

            if ($user) {
                // Link Google account to existing user
                $user->google_id = $request->google_id;
                $user->avatar = $request->avatar;
                $user->is_verified = true;
                $user->email_verified_at = now();
                $user->save();
            } else {
                // Create new user
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'google_id' => $request->google_id,
                    'avatar' => $request->avatar,
                    'phone' => '',
                    'role' => 'buyer',
                    'is_verified' => true, // Google already verified the email
                    'email_verified_at' => now(),
                    'password' => Hash::make(\Str::random(32)), // Random password for Google users
                ]);
            }
        } else {
            // Update avatar if changed
            if ($request->avatar && $user->avatar !== $request->avatar) {
                $user->avatar = $request->avatar;
                $user->save();
            }
        }

        // Create auth token
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
                'avatar' => $user->avatar,
                'is_verified' => $user->is_verified,
                'created_at' => $user->created_at,
                'vendor_profile' => $user->vendorProfile ? [
                    'id' => $user->vendorProfile->id,
                    'business_name' => $user->vendorProfile->business_name,
                    'vetting_status' => $user->vendorProfile->vetting_status,
                ] : null,
            ],
            'is_new_user' => $user->wasRecentlyCreated,
        ]);
    } catch (\Exception $e) {
        \Log::error('Google sign-in error: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Google sign-in failed. Please try again.',
        ], 500);
    }
});

// Categories for mobile - ONLY parent categories (parent_id IS NULL) with children nested
Route::get('/categories', function () {
    try {
        // IMPORTANT: Only fetch categories where parent_id IS NULL (top-level/main categories)
        $categories = Category::where('is_active', true)
            ->whereNull('parent_id')  // ONLY parent categories
            ->with(['children' => function($query) {
                $query->where('is_active', true)
                    ->orderBy('order');
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
                    'children' => $category->children->map(function ($child) {
                        return [
                            'id' => $child->id,
                            'name' => $child->name,
                            'slug' => $child->slug,
                            'description' => $child->description,
                            'icon' => $child->icon ?? 'category',
                            'parent_id' => $child->parent_id,
                            'is_parent' => false,
                            'listings_count' => $child->total_listings_count,
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
            'message' => 'Failed to load categories: ' . $e->getMessage(),
            'data' => []
        ], 500);
    }
});

// Category with listings
Route::get('/categories/{slug}', function ($slug) {
    try {
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
        
        $listings = Listing::with(['images', 'category', 'user.vendorProfile'])
            ->whereIn('category_id', $categoryIds)
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($listing) {
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
                ];
            })->values();
        
        return response()->json([
            'success' => true,
            'data' => [
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                ],
                'listings' => $listings,
            ],
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Server error: ' . $e->getMessage(),
        ], 500);
    }
});

// Marketplace/Listings (Public)
Route::get('/marketplace', function (Request $request) {
    $query = Listing::with(['category', 'user.vendorProfile', 'images'])
        ->where('is_active', true);

    if ($request->has('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    if ($request->has('category_id')) {
        $query->where('category_id', $request->category_id);
    }

    if ($request->has('min_price')) {
        $query->where('price', '>=', $request->min_price);
    }
    if ($request->has('max_price')) {
        $query->where('price', '<=', $request->max_price);
    }

    $sortBy = $request->get('sort_by', 'created_at');
    $sortOrder = $request->get('sort_order', 'desc');
    $query->orderBy($sortBy, $sortOrder);

    $perPage = $request->get('per_page', 20);
    $listings = $query->paginate($perPage);

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
            ] : null,
            'average_rating' => $listing->average_rating ?? 0,
            'reviews_count' => $listing->reviews_count ?? 0,
            'is_active' => $listing->is_active,
        ];
    });

    return response()->json([
        'success' => true,
        'data' => $data,
        'meta' => [
            'current_page' => $listings->currentPage(),
            'last_page' => $listings->lastPage(),
            'per_page' => $listings->perPage(),
            'total' => $listings->total(),
        ],
    ]);
});

Route::get('/marketplace/{id}', function ($id) {
    $listing = Listing::with(['category', 'user.vendorProfile', 'variants', 'images', 'reviews.user'])
        ->where('is_active', true)
        ->find($id);

    if (!$listing) {
        return response()->json(['success' => false, 'message' => 'Listing not found'], 404);
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
            'category' => $listing->category ? ['id' => $listing->category->id, 'name' => $listing->category->name] : null,
            'vendor' => $listing->user && $listing->user->vendorProfile ? [
                'id' => $listing->user->vendorProfile->id,
                'business_name' => $listing->user->vendorProfile->business_name ?? $listing->user->name,
            ] : null,
            'average_rating' => $listing->average_rating ?? 0,
            'reviews_count' => $listing->reviews ? $listing->reviews->count() : 0,
            'is_active' => $listing->is_active,
        ],
    ]);
});

// Featured listings
Route::get('/featured-listings', function () {
    $listings = Listing::with(['images', 'category', 'user.vendorProfile'])
        ->where('is_active', true)
        ->orderBy('created_at', 'desc')
        ->limit(20)
        ->get()
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
                'vendor' => $listing->user && $listing->user->vendorProfile ? [
                    'id' => $listing->user->vendorProfile->id,
                    'business_name' => $listing->user->vendorProfile->business_name ?? $listing->user->name,
                ] : null,
                'category' => $listing->category ? ['id' => $listing->category->id, 'name' => $listing->category->name] : null,
                'average_rating' => $listing->average_rating ?? 0,
                'reviews_count' => $listing->reviews_count ?? 0,
                'is_active' => $listing->is_active,
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

// ====================
// AUTHENTICATED ROUTES
// ====================
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', function (Request $request) {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['success' => true, 'message' => 'Logged out successfully']);
    });

    Route::get('/user', function (Request $request) {
        $user = $request->user()->load('vendorProfile');
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'avatar' => $user->avatar ? asset('storage/' . $user->avatar) : null,
                'vendor_profile' => $user->vendorProfile ? [
                    'id' => $user->vendorProfile->id,
                    'user_id' => $user->vendorProfile->user_id,
                    'business_name' => $user->vendorProfile->business_name ?? $user->vendorProfile->store_name,
                    'vetting_status' => $user->vendorProfile->vetting_status,
                ] : null,
            ],
        ]);
    });

    // Update User Profile
    Route::put('/user/profile', function (Request $request) {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
        ]);

        $user = $request->user();

        if ($request->has('name')) {
            $user->name = $request->name;
        }
        if ($request->has('phone')) {
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
            'avatar' => asset('storage/' . $path),
        ]);
    });

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

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'vendor' => [
                'id' => $vendor->id,
                'business_name' => $vendor->business_name,
                'vetting_status' => $vendor->vetting_status,
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
        return response()->json(['success' => true, 'data' => ['items' => $items, 'subtotal' => $subtotal, 'total' => $subtotal]]);
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

        $variantId = $request->input('variant_id');

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
            return response()->json(['success' => false, 'message' => 'Failed to confirm delivery: ' . $e->getMessage()], 500);
        }
    });

    // Place Order - FIXED: Now includes title in order_items
    Route::post('/orders/place-order', function (Request $request) {
        $request->validate([
            'shipping_address_id' => 'required|exists:shipping_addresses,id',
            'payment_method' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $user = $request->user();
        $cart = Cart::where('user_id', $user->id)->first();
        
        if (!$cart || empty($cart->items)) {
            return response()->json(['success' => false, 'message' => 'Cart is empty'], 400);
        }

        $shippingAddress = \App\Models\ShippingAddress::find($request->shipping_address_id);
        if (!$shippingAddress || $shippingAddress->user_id != $user->id) {
            return response()->json(['success' => false, 'message' => 'Invalid shipping address'], 400);
        }

        try {
            \DB::beginTransaction();

            $subtotal = 0;
            $orderItems = [];

            foreach ($cart->items as $item) {
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
                    ],
                ];
            }

            $shipping = 0; // Free shipping for now
            $taxes = $subtotal * 0.18; // 18% VAT
            $platformCommission = $subtotal * 0.15; // 15% commission
            $total = $subtotal + $shipping + $taxes;

            // Generate order number
            $orderNumber = 'BM-' . strtoupper(uniqid()) . '-' . date('Ymd');

            // Get vendor profile ID from first listing
            $firstListing = Listing::find($cart->items[0]['listing_id']);
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

            // Clear cart
            $cart->update(['items' => []]);

            \DB::commit();

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
                'message' => 'Failed to place order: ' . $e->getMessage(),
            ], 500);
        }
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
        
        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor profile not found',
            ], 404);
        }

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
                return response()->json(['success' => false, 'message' => 'Vendor not found'], 404);
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
                'stock' => $request->stock ?? $request->quantity ?? 1,
                'weight_kg' => $request->weight ?? null,
                'condition' => $request->condition ?? 'new',
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
                'condition' => 'nullable|in:new,used,refurbished',
            ]);

            $listing->update($request->only([
                'title', 'description', 'price', 'category_id', 'quantity', 'condition'
            ]));

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

            return response()->json([
                'success' => true,
                'data' => $orders->items(),
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

            return response()->json([
                'success' => true,
                'order' => $order,
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

            $oldStatus = $order->status;
            $order->status = $request->status;
            $order->save();

            // TODO: Send notification to buyer about status change

            return response()->json([
                'success' => true,
                'message' => "Order status updated from {$oldStatus} to {$request->status}",
                'order' => $order->fresh(['buyer', 'items']),
            ]);
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
});

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

        $query = \DB::table('conversations')
            ->select([
                'conversations.id',
                'conversations.buyer_id',
                'conversations.vendor_profile_id',
                'conversations.listing_id',
                'conversations.subject',
                'conversations.last_message_at',
                'conversations.status',
                'conversations.created_at',
            ]);

        if ($isVendor && $vendorProfileId) {
            $query->where('conversations.vendor_profile_id', $vendorProfileId);
        } else {
            $query->where('conversations.buyer_id', $user->id);
        }

        $conversations = $query->where('conversations.status', 'active')
            ->orderBy('conversations.last_message_at', 'desc')
            ->get();

        // Enrich with participant info and last message
        $enriched = $conversations->map(function ($conv) use ($user, $isVendor) {
            // Get other participant
            if ($isVendor) {
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

        // Check if conversation already exists
        $existing = \DB::table('conversations')
            ->where('buyer_id', $user->id)
            ->where('vendor_profile_id', $vendorProfileId)
            ->where(function ($q) use ($listingId) {
                if ($listingId) {
                    $q->where('listing_id', $listingId);
                } else {
                    $q->whereNull('listing_id');
                }
            })
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

        $hasAccess = false;
        if ($isVendor && $vendorProfileId) {
            $hasAccess = $conversation->vendor_profile_id == $vendorProfileId;
        } else {
            $hasAccess = $conversation->buyer_id == $user->id;
        }

        if (!$hasAccess) {
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

        // Get conversation details
        if ($isVendor) {
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

        $hasAccess = false;
        if ($isVendor && $vendorProfileId) {
            $hasAccess = $conversation->vendor_profile_id == $vendorProfileId;
        } else {
            $hasAccess = $conversation->buyer_id == $user->id;
        }

        if (!$hasAccess) {
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
