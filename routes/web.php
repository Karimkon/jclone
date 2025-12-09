<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Controllers
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Site\LandingController;
use App\Http\Controllers\Marketplace\ListingController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminVendorController;
use App\Http\Controllers\Vendor\VendorDashboardController;
use App\Http\Controllers\Logistics\LogisticsDashboardController;
use App\Http\Controllers\Finance\FinanceDashboardController;
use App\Http\Controllers\CEO\CEODashboardController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\Marketplace\VendorOnboardingController;
use App\Http\Controllers\Marketplace\ImportController;
use App\Http\Controllers\Marketplace\OrderController;
use App\Http\Controllers\Logistics\WarehouseController;
use App\Http\Controllers\Logistics\ShipmentController;
use App\Http\Controllers\Vendor\VendorOrderController;
use App\Http\Controllers\Vendor\PromotionController;
use App\Http\Controllers\Finance\EscrowController;

// ====================
// PUBLIC ROUTES
// ====================

// Landing page
Route::get('/', [LandingController::class, 'index'])->name('welcome');

// Marketplace browsing (public)
Route::get('/marketplace', [ListingController::class, 'indexPublic'])->name('marketplace.index');
Route::get('/marketplace/{listing}', [ListingController::class, 'showPublic'])->name('marketplace.show');

// Categories (public)
Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/categories/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');

// ====================
// AUTHENTICATION ROUTES
// ====================

// Show login pages
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::get('/vendor/login', [AuthController::class, 'showVendorLogin'])->name('vendor.login');
Route::get('/admin/login', [AuthController::class, 'showAdminLogin'])->name('admin.login');
Route::get('/logistics/login', [AuthController::class, 'showLogisticsLogin'])->name('logistics.login');
Route::get('/finance/login', [AuthController::class, 'showFinanceLogin'])->name('finance.login');
Route::get('/ceo/login', [AuthController::class, 'showCEOLogin'])->name('ceo.login');

// Handle login submissions
Route::post('/login', [AuthController::class, 'buyerLogin'])->name('login.submit');
Route::post('/admin/login', [AuthController::class, 'adminLogin'])->name('admin.login.submit');
Route::post('/vendor/login', [AuthController::class, 'vendorLogin'])->name('vendor.login.submit');
Route::post('/logistics/login', [AuthController::class, 'logisticsLogin'])->name('logistics.login.submit');
Route::post('/finance/login', [AuthController::class, 'financeLogin'])->name('finance.login.submit');
Route::post('/ceo/login', [AuthController::class, 'ceoLogin'])->name('ceo.login.submit');

// ====================
// REGISTRATION ROUTES
// ====================
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
Route::get('/register/buyer', function () {
    return view('auth.register');
})->name('register.buyer');

// Logout
Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
})->name('logout');

// ====================
// VENDOR ONBOARDING ROUTES (Public view and submission, status requires auth)
// ====================
Route::prefix('vendor')->name('vendor.')->group(function () {
    // Public routes (form view and submission - handles both authenticated and unauthenticated users)
    Route::get('/onboard', [VendorOnboardingController::class, 'create'])->name('onboard.create');
    Route::post('/onboard', [VendorOnboardingController::class, 'store'])->name('onboard.store');
    
    // Protected routes (require login)
    Route::middleware(['auth'])->group(function () {
        Route::get('/onboard/status', [VendorOnboardingController::class, 'show'])->name('onboard.status');
        Route::post('/onboard/additional', [VendorOnboardingController::class, 'uploadAdditional'])->name('onboard.additional');
    });
});

// ====================
// AUTHENTICATED ROUTES
// ====================

Route::middleware(['auth'])->group(function () {
    
    // ====================
    // VENDOR ROUTES
    // ====================
    Route::middleware(['check.vendor.status'])->prefix('vendor')->name('vendor.')->group(function () {
        Route::get('/dashboard', [VendorDashboardController::class, 'index'])->name('dashboard');
        
        // Import management
        Route::prefix('imports')->name('imports.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Marketplace\ImportController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Marketplace\ImportController::class, 'create'])->name('create');
            Route::post('/request', [\App\Http\Controllers\Marketplace\ImportController::class, 'store'])->name('request.store');
            Route::post('/{import}/calculate', [\App\Http\Controllers\Marketplace\ImportController::class, 'calculate'])->name('request.calculate');
            Route::post('/{import}/start', [\App\Http\Controllers\Marketplace\ImportController::class, 'startImport'])->name('request.start');
        });
        
        // Vendor listings
        Route::get('/listings', [ListingController::class, 'index'])->name('listings.index');
        Route::get('/listings/create', [ListingController::class, 'create'])->name('listings.create');
        Route::post('/listings', [ListingController::class, 'store'])->name('listings.store');
        Route::get('/listings/{listing}/edit', [ListingController::class, 'edit'])->name('listings.edit');
        Route::put('/listings/{listing}', [ListingController::class, 'update'])->name('listings.update');
        Route::delete('/listings/{listing}', [ListingController::class, 'destroy'])->name('listings.destroy');
        Route::post('/listings/{listing}/toggle-status', [ListingController::class, 'toggleStatus'])->name('listings.toggleStatus');
        Route::post('/listings/bulk-update', [ListingController::class, 'bulkUpdate'])->name('listings.bulkUpdate');

        // Vendor orders
        Route::get('/orders', [VendorOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [VendorOrderController::class, 'show'])->name('orders.show');
        Route::post('/orders/{order}/status', [VendorOrderController::class, 'updateStatus'])->name('orders.updateStatus');
        Route::post('/orders/{order}/ship', [VendorOrderController::class, 'markShipped'])->name('orders.markShipped');
        Route::post('/orders/{order}/cancel', [VendorOrderController::class, 'requestCancel'])->name('orders.requestCancel');
        Route::get('/orders/{order}/packing-slip', [VendorOrderController::class, 'packingSlip'])->name('orders.packingSlip');

        // Vendor promotions
        Route::prefix('promotions')->name('promotions.')->group(function () {
            Route::get('/', [PromotionController::class, 'index'])->name('index');
            Route::get('/create', [PromotionController::class, 'create'])->name('create');
            Route::post('/', [PromotionController::class, 'store'])->name('store');
            Route::get('/{promotion}', [PromotionController::class, 'show'])->name('show');
            Route::post('/{promotion}/cancel', [PromotionController::class, 'cancel'])->name('cancel');
            Route::post('/{promotion}/extend', [PromotionController::class, 'extend'])->name('extend');
            Route::get('/statistics', [PromotionController::class, 'statistics'])->name('statistics');
        });

        // Vendor profile
        Route::get('/profile', [\App\Http\Controllers\Vendor\VendorProfileController::class, 'show'])->name('profile.show');
        Route::put('/profile', [\App\Http\Controllers\Vendor\VendorProfileController::class, 'update'])->name('profile.update');
        
        // Vendor analytics
        Route::get('/analytics', function () {
            return view('vendor.analytics.index');
        })->name('analytics');
    });

    // ====================
// ADMIN ROUTES
// ====================
Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    
    // User management
    Route::resource('users', AdminUserController::class);
    
    // Vendor vetting
    Route::get('/vendors/pending', [AdminVendorController::class, 'pending'])->name('vendors.pending');
    Route::get('/vendors', [AdminVendorController::class, 'index'])->name('vendors.index');
    Route::get('/vendors/{vendor}', [AdminVendorController::class, 'show'])->name('vendors.show');
    Route::post('/vendors/{vendor}/approve', [AdminVendorController::class, 'approve'])->name('vendors.approve');
    Route::post('/vendors/{vendor}/reject', [AdminVendorController::class, 'reject'])->name('vendors.reject');
    Route::post('/vendors/{id}/toggle-status', [AdminVendorController::class, 'toggleStatus'])->name('vendors.toggleStatus');
    Route::post('/vendors/{id}/update-score', [AdminVendorController::class, 'updateScore'])->name('vendors.updateScore');

    // Document verification
    Route::post('/documents/{id}/verify', [AdminVendorController::class, 'verifyDocument'])->name('documents.verify');
    Route::post('/documents/{id}/reject', [AdminVendorController::class, 'rejectDocument'])->name('documents.reject');
    
    // Category management
    Route::get('/categories', [CategoryController::class, 'adminIndex'])->name('categories.index');
    Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    Route::post('/categories/{category}/toggle', [CategoryController::class, 'toggle'])->name('categories.toggle');
    
    // Orders - ADD THESE ROUTES
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\AdminOrderController::class, 'index'])->name('index');
        Route::get('/{order}', [\App\Http\Controllers\Admin\AdminOrderController::class, 'show'])->name('show');
        Route::post('/{order}/status', [\App\Http\Controllers\Admin\AdminOrderController::class, 'updateStatus'])->name('update-status'); // THIS LINE IS MISSING!
        Route::post('/{order}/refund', [\App\Http\Controllers\Admin\AdminOrderController::class, 'refund'])->name('refund');
        Route::get('/{order}/invoice', [\App\Http\Controllers\Admin\AdminOrderController::class, 'invoice'])->name('invoice');
        Route::get('/export', [\App\Http\Controllers\Admin\AdminOrderController::class, 'export'])->name('export');
    });
    
    // Disputes
    Route::get('/disputes', [\App\Http\Controllers\Admin\DisputeController::class, 'index'])->name('disputes.index');
    Route::get('/disputes/{dispute}', [\App\Http\Controllers\Admin\DisputeController::class, 'show'])->name('disputes.show');
    
    // Reports
    Route::get('/reports', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports.index');

    // Escrow management
    Route::prefix('escrows')->name('escrows.')->group(function () {
        Route::get('/pending', [EscrowController::class, 'pending'])->name('pending');
        Route::post('/{escrow}/release', [EscrowController::class, 'release'])->name('release');
        Route::post('/{escrow}/refund', [EscrowController::class, 'refund'])->name('refund');
    });
});
    
    // ====================
    // LOGISTICS ROUTES
    // ====================
    Route::middleware(['role:logistics,admin'])->prefix('logistics')->name('logistics.')->group(function () {
        Route::get('/dashboard', [LogisticsDashboardController::class, 'index'])->name('dashboard');
        
        // Warehouses
        Route::resource('warehouses', WarehouseController::class);
        Route::post('/warehouses/{warehouse}/receive', [WarehouseController::class, 'receive'])->name('warehouses.receive');
        Route::get('/warehouses/{warehouse}/stock', [WarehouseController::class, 'stock'])->name('warehouses.stock');
        
        // Shipments
        Route::resource('shipments', ShipmentController::class);
        Route::post('/shipments/{shipment}/clear', [ShipmentController::class, 'markCleared'])->name('shipments.markCleared');
        Route::post('/shipments/{shipment}/deliver', [ShipmentController::class, 'markDelivered'])->name('shipments.markDelivered');
        
        // Clearing agents
        Route::resource('clearing-agents', \App\Http\Controllers\Logistics\ClearingAgentController::class);
    });
    
    // ====================
    // FINANCE ROUTES
    // ====================
    Route::middleware(['role:finance,admin'])->prefix('finance')->name('finance.')->group(function () {
        Route::get('/dashboard', [FinanceDashboardController::class, 'index'])->name('dashboard');
        
        // Payouts
        Route::get('/payouts', [\App\Http\Controllers\Finance\PayoutController::class, 'index'])->name('payouts.index');
        Route::post('/payouts/{vendor}/create', [\App\Http\Controllers\Finance\PayoutController::class, 'createPayout'])->name('payouts.create');
        
        // Transactions
        Route::get('/transactions', [\App\Http\Controllers\Finance\TransactionController::class, 'index'])->name('transactions.index');
        
        // Escrow management
        Route::get('/escrows', [\App\Http\Controllers\Finance\EscrowController::class, 'index'])->name('escrows.index');
        Route::post('/escrows/{escrow}/release', [\App\Http\Controllers\Finance\EscrowController::class, 'release'])->name('escrows.release');
    });
    
    // ====================
    // CEO ROUTES
    // ====================
    Route::middleware(['role:ceo,admin'])->prefix('ceo')->name('ceo.')->group(function () {
        Route::get('/dashboard', [CEODashboardController::class, 'index'])->name('dashboard');
        
        // Analytics & Reports
        Route::get('/analytics', [CEODashboardController::class, 'analytics'])->name('analytics');
        Route::get('/financials', [CEODashboardController::class, 'financials'])->name('financials');
        Route::get('/performance', [CEODashboardController::class, 'performance'])->name('performance');
    });
    
    // ====================
    // BUYER ROUTES (AUTHENTICATION REQUIRED)
    // ====================
    Route::middleware(['auth', 'role:buyer'])->prefix('buyer')->name('buyer.')->group(function () {
        // Dashboard
        Route::get('/dashboard', [\App\Http\Controllers\Buyer\BuyerDashboardController::class, 'index'])->name('dashboard');
        Route::get('/profile', [\App\Http\Controllers\Buyer\BuyerDashboardController::class, 'profile'])->name('profile');
        Route::post('/profile', [\App\Http\Controllers\Buyer\BuyerDashboardController::class, 'updateProfile'])->name('profile.update');
        Route::post('/change-password', [\App\Http\Controllers\Buyer\BuyerDashboardController::class, 'changePassword'])->name('profile.change-password');
        
        // Wallet
        Route::prefix('wallet')->name('wallet.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Buyer\WalletController::class, 'index'])->name('index');
            Route::post('/deposit', [\App\Http\Controllers\Buyer\WalletController::class, 'deposit'])->name('deposit');
            Route::post('/withdraw', [\App\Http\Controllers\Buyer\WalletController::class, 'withdraw'])->name('withdraw');
            Route::get('/transactions', [\App\Http\Controllers\Buyer\WalletController::class, 'transactions'])->name('transactions');
            Route::get('/balance', [\App\Http\Controllers\Buyer\WalletController::class, 'getBalance'])->name('balance');
        });
        
        // Cart (PROTECTED - requires authentication)
        Route::get('/cart', [\App\Http\Controllers\Buyer\CartController::class, 'index'])->name('cart.index');
        Route::post('/cart/add/{listing}', [\App\Http\Controllers\Buyer\CartController::class, 'add'])->name('cart.add');
        Route::post('/cart/update/{listingId}', [\App\Http\Controllers\Buyer\CartController::class, 'update'])->name('cart.update');
        Route::delete('/cart/remove/{listingId}', [\App\Http\Controllers\Buyer\CartController::class, 'remove'])->name('cart.remove');
        Route::post('/cart/clear', [\App\Http\Controllers\Buyer\CartController::class, 'clear'])->name('cart.clear');
        Route::get('/cart/summary', [\App\Http\Controllers\Buyer\CartController::class, 'getCartSummary'])->name('cart.summary');
        
        // Wishlist (PROTECTED - requires authentication)
        Route::get('/wishlist', [\App\Http\Controllers\Buyer\WishlistController::class, 'index'])->name('wishlist.index');
        Route::post('/wishlist/add/{listing}', [\App\Http\Controllers\Buyer\WishlistController::class, 'add'])->name('wishlist.add');
        Route::delete('/wishlist/remove/{listing}', [\App\Http\Controllers\Buyer\WishlistController::class, 'remove'])->name('wishlist.remove');
        Route::post('/wishlist/toggle/{listing}', [\App\Http\Controllers\Buyer\WishlistController::class, 'toggle'])->name('wishlist.toggle');
        Route::post('/wishlist/move-to-cart/{listing}', [\App\Http\Controllers\Buyer\WishlistController::class, 'moveToCart'])->name('wishlist.move-to-cart');
        Route::get('/wishlist/count', [\App\Http\Controllers\Buyer\WishlistController::class, 'getCount'])->name('wishlist.count');
        
        // Orders
        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Buyer\OrderController::class, 'index'])->name('index');
            Route::get('/checkout', [\App\Http\Controllers\Buyer\OrderController::class, 'checkout'])->name('checkout');
            Route::post('/place-order', [\App\Http\Controllers\Buyer\OrderController::class, 'placeOrder'])->name('place-order');
            Route::get('/{order}', [\App\Http\Controllers\Buyer\OrderController::class, 'show'])->name('show');
            Route::post('/{order}/cancel', [\App\Http\Controllers\Buyer\OrderController::class, 'cancelOrder'])->name('cancel');
            Route::post('/{order}/confirm-delivery', [\App\Http\Controllers\Buyer\OrderController::class, 'confirmDelivery'])->name('confirm-delivery');
        });
        
        // Disputes
        Route::prefix('disputes')->name('disputes.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Buyer\DisputeController::class, 'index'])->name('index');
            Route::get('/create/{order}', [\App\Http\Controllers\Buyer\DisputeController::class, 'create'])->name('create');
            Route::post('/store/{order}', [\App\Http\Controllers\Buyer\DisputeController::class, 'store'])->name('store');
            Route::get('/{dispute}', [\App\Http\Controllers\Buyer\DisputeController::class, 'show'])->name('show');
            Route::post('/{dispute}/add-evidence', [\App\Http\Controllers\Buyer\DisputeController::class, 'addEvidence'])->name('add-evidence');
            Route::post('/{dispute}/accept-resolution', [\App\Http\Controllers\Buyer\DisputeController::class, 'acceptResolution'])->name('accept-resolution');
        });
    });
});

// ====================
// IMPORT CALCULATOR (Public API)
// ====================
Route::prefix('api')->name('api.')->group(function () {
    Route::get('/import-calculate', [ImportController::class, 'calculateApi'])->name('import.calculate');
});

// ====================
// PAYMENT WEBHOOKS (Must be public)
// ====================
Route::post('/webhooks/flutterwave', [\App\Http\Controllers\Payment\FlutterwaveWebhookController::class, 'handle'])->name('webhooks.flutterwave');
Route::post('/webhooks/pesapal', [\App\Http\Controllers\Payment\PesaPalWebhookController::class, 'handle'])->name('webhooks.pesapal');

// ====================
// PUBLIC AJAX ROUTES (for cart/wishlist counts)
// ====================
Route::get('/cart/count', function() {
    if (auth()->check()) {
        $cart = \App\Models\Cart::where('user_id', auth()->id())->first();
        return response()->json([
            'authenticated' => true,
            'cart_count' => $cart ? count($cart->items ?? []) : 0
        ]);
    }
    return response()->json(['authenticated' => false, 'cart_count' => 0]);
})->name('cart.count');

Route::get('/wishlist/count', function() {
    if (auth()->check()) {
        $count = \App\Models\Wishlist::where('user_id', auth()->id())->count();
        return response()->json([
            'authenticated' => true,
            'count' => $count
        ]);
    }
    return response()->json(['authenticated' => false, 'count' => 0]);
})->name('wishlist.count');

// ====================
// DEBUG ROUTES (Development only)
// ====================
if (config('app.debug')) {
    Route::get('/debug/import-sample', function () {
        return \App\Services\ImportCalculator::calculateAdValorem(100, 20, 0, 0.10, 0.18, 0.05);
    });
    
    Route::get('/debug/setup', function () {
        return view('debug.setup');
    });
}