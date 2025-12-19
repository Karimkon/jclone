<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Listing;
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
        'phone' => $request->phone,
        'role' => $request->role ?? 'buyer',
    ]);

    $token = $user->createToken('mobile-app')->plainTextToken;

    return response()->json([
        'success' => true,
        'message' => 'Registration successful',
        'token' => $token,
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $user->role,
            'avatar' => null,
            'created_at' => $user->created_at,
        ],
    ], 201);
});

// Categories for mobile
Route::get('/categories', function () {
    try {
        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name', 'slug', 'description', 'icon'])
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                    'icon' => $category->icon ?? 'category',
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to load categories',
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
            });
        
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
    Route::get('{listing}/check-variations', [ListingApiController::class, 'checkVariations']);
    Route::get('{listing}/variations', [ListingApiController::class, 'getVariations']);
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

    // CART
    Route::get('/cart', function (Request $request) {
        $cart = Cart::where('user_id', $request->user()->id)->first();
        if (!$cart || empty($cart->items)) {
            return response()->json(['success' => true, 'data' => ['items' => [], 'subtotal' => 0, 'total' => 0]]);
        }
        $items = collect($cart->items)->map(function ($item) {
            $listing = Listing::with('images')->find($item['listing_id']);
            if (!$listing) return null;
            return [
                'listing_id' => $listing->id,
                'title' => $listing->title,
                'price' => $item['price'] ?? $listing->price,
                'quantity' => $item['quantity'],
                'thumbnail' => $listing->images && $listing->images->isNotEmpty() ? $listing->images->first()->path : null,
            ];
        })->filter()->values();
        $subtotal = $items->sum(fn($item) => $item['price'] * $item['quantity']);
        return response()->json(['success' => true, 'data' => ['items' => $items, 'subtotal' => $subtotal, 'total' => $subtotal]]);
    });

    Route::post('/cart/add/{listingId}', function (Request $request, $listingId) {
        $listing = Listing::find($listingId);
        if (!$listing) return response()->json(['success' => false, 'message' => 'Listing not found'], 404);
        
        $cart = Cart::firstOrCreate(['user_id' => $request->user()->id], ['items' => []]);
        $items = $cart->items ?? [];
        $quantity = $request->get('quantity', 1);
        
        $existingIndex = collect($items)->search(fn($item) => $item['listing_id'] == $listingId);
        if ($existingIndex !== false) {
            $items[$existingIndex]['quantity'] += $quantity;
        } else {
            $items[] = [
                'listing_id' => (int)$listingId, 
                'quantity' => $quantity, 
                'price' => $listing->price,
                'title' => $listing->title, // IMPORTANT: Include title for order items
            ];
        }
        $cart->update(['items' => $items]);
        return response()->json(['success' => true, 'message' => 'Item added to cart', 'cart_count' => count($items)]);
    });

    Route::post('/cart/update/{listingId}', function (Request $request, $listingId) {
        $cart = Cart::where('user_id', $request->user()->id)->first();
        if (!$cart) return response()->json(['success' => false, 'message' => 'Cart not found'], 404);
        
        $items = $cart->items ?? [];
        $quantity = $request->get('quantity', 1);
        $index = collect($items)->search(fn($item) => $item['listing_id'] == $listingId);
        
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
        
        $items = collect($cart->items ?? [])->filter(fn($item) => $item['listing_id'] != $listingId)->values()->toArray();
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
        $orders = Order::where('buyer_id', $request->user()->id)->orderBy('created_at', 'desc')->paginate(20);
        return response()->json([
            'success' => true,
            'data' => $orders->map(fn($o) => ['id' => $o->id, 'order_number' => $o->order_number, 'status' => $o->status, 'total' => $o->total, 'created_at' => $o->created_at]),
            'meta' => ['current_page' => $orders->currentPage(), 'last_page' => $orders->lastPage(), 'total' => $orders->total()],
        ]);
    });

    Route::get('/orders/{id}', function (Request $request, $id) {
        $order = Order::where('buyer_id', $request->user()->id)->find($id);
        if (!$order) return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        return response()->json(['success' => true, 'data' => $order]);
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
                $listing = Listing::find($item['listing_id']);
                if (!$listing) continue;

                $itemPrice = $item['price'] ?? $listing->price;
                $itemTotal = $itemPrice * $item['quantity'];
                $subtotal += $itemTotal;

                $orderItems[] = [
                    'listing_id' => $listing->id,
                    'title' => $item['title'] ?? $listing->title, // FIXED: Include title
                    'quantity' => $item['quantity'],
                    'unit_price' => $itemPrice,
                    'line_total' => $itemTotal,
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

            // Create order items - FIXED: Now includes title
            foreach ($orderItems as $item) {
                \App\Models\OrderItem::create([
                    'order_id' => $order->id,
                    'listing_id' => $item['listing_id'],
                    'title' => $item['title'], // FIXED: Include title field
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'line_total' => $item['line_total'],
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

    // WALLET
    Route::get('/wallet', function (Request $request) {
        $wallet = \App\Models\BuyerWallet::firstOrCreate(['user_id' => $request->user()->id], ['balance' => 0]);
        return response()->json(['success' => true, 'data' => ['balance' => $wallet->balance, 'currency' => 'UGX']]);
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
                    $query->where('approval_status', 'pending');
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
                'quantity' => $request->quantity ?? 1,
                'condition' => $request->condition ?? 'new',
                'is_active' => true,
                'approval_status' => 'approved', // Auto-approve or set to 'pending' for moderation
            ]);

            // Handle images
            if ($request->hasFile('images')) {
                $order = 0;
                foreach ($request->file('images') as $image) {
                    $path = $image->store('listings', 'public');
                    
                    ListingImage::create([
                        'listing_id' => $listing->id,
                        'image_path' => $path,
                        'is_primary' => $order === 0,
                        'order' => $order,
                    ]);
                    $order++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Listing created successfully',
                'listing' => $listing->load(['images', 'category']),
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
                    $path = $image->store('listings', 'public');
                    
                    ListingImage::create([
                        'listing_id' => $listing->id,
                        'image_path' => $path,
                        'is_primary' => false,
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
                ->with(['buyer', 'items.listing', 'shippingAddress']);

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
                ->with(['buyer', 'items.listing.images', 'shippingAddress', 'payment'])
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
});
