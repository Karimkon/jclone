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

// Logout
Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
})->name('logout');

// ====================
// VENDOR ONBOARDING ROUTES (Public view, but submission requires auth)
// ====================
Route::prefix('vendor')->name('vendor.')->group(function () {
    // Public view of onboarding form
    Route::get('/onboard', [VendorOnboardingController::class, 'create'])->name('onboard.create');
    
    // Protected routes (require login)
    Route::middleware(['auth'])->group(function () {
        Route::post('/onboard', [VendorOnboardingController::class, 'store'])->name('onboard.store');
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
Route::middleware(['check.vendor.status'])->prefix('vendor')->name('vendor.')->group(function () {    Route::get('/dashboard', [VendorDashboardController::class, 'index'])->name('dashboard');
    
    // Listings management
    Route::resource('listings', ListingController::class)->except(['index', 'show']);
    
    // Import requests
    Route::prefix('imports')->name('imports.')->group(function () {
        Route::get('/', [ImportController::class, 'index'])->name('index');
        Route::post('/request', [ImportController::class, 'store'])->name('request.store');
        Route::post('/{import}/calculate', [ImportController::class, 'calculate'])->name('request.calculate');
        Route::post('/{import}/start', [ImportController::class, 'startImport'])->name('request.start');
    });
    
    // Orders
    Route::get('/orders', [\App\Http\Controllers\Vendor\VendorOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [\App\Http\Controllers\Vendor\VendorOrderController::class, 'show'])->name('orders.show');
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
        Route::post('/vendors/{vendor}/approve', [AdminVendorController::class, 'approve'])->name('vendors.approve');
        Route::post('/vendors/{vendor}/reject', [AdminVendorController::class, 'reject'])->name('vendors.reject');
        Route::post('/vendors/{id}/toggle-status', [AdminVendorController::class, 'toggleStatus'])->name('vendors.toggleStatus');
        Route::post('/vendors/{id}/update-score', [AdminVendorController::class, 'updateScore'])->name('vendors.updateScore');

         // Document verification
        Route::post('/documents/{id}/verify', [AdminVendorController::class, 'verifyDocument'])->name('documents.verify');
        Route::post('/documents/{id}/reject', [AdminVendorController::class, 'rejectDocument'])->name('documents.reject');
        
        // Category management
        Route::resource('categories', CategoryController::class);
        Route::post('/categories/{category}/toggle', [CategoryController::class, 'toggle'])->name('categories.toggle');
        
        // Orders
        Route::get('/orders', [\App\Http\Controllers\Admin\AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [\App\Http\Controllers\Admin\AdminOrderController::class, 'show'])->name('orders.show');
        
        // Disputes
        Route::get('/disputes', [\App\Http\Controllers\Admin\DisputeController::class, 'index'])->name('disputes.index');
        Route::get('/disputes/{dispute}', [\App\Http\Controllers\Admin\DisputeController::class, 'show'])->name('disputes.show');
        
        // Reports
        Route::get('/reports', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports.index');
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
    // BUYER ROUTES (Regular Users)
    // ====================
    Route::middleware(['role:buyer'])->group(function () {
        // Orders
        Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        
        // Cart
        Route::get('/cart', [\App\Http\Controllers\Buyer\CartController::class, 'index'])->name('cart.index');
        Route::post('/cart/add/{listing}', [\App\Http\Controllers\Buyer\CartController::class, 'add'])->name('cart.add');
        Route::delete('/cart/remove/{item}', [\App\Http\Controllers\Buyer\CartController::class, 'remove'])->name('cart.remove');
        
        // Wishlist
        Route::get('/wishlist', [\App\Http\Controllers\Buyer\WishlistController::class, 'index'])->name('wishlist.index');
        Route::post('/wishlist/add/{listing}', [\App\Http\Controllers\Buyer\WishlistController::class, 'add'])->name('wishlist.add');
        
        // Disputes
        Route::post('/orders/{order}/dispute', [\App\Http\Controllers\Buyer\DisputeController::class, 'store'])->name('disputes.store');
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
// DEBUG ROUTES (Development only)
// ====================
if (config('app.debug')) {
    Route::get('/debug/import-sample', function () {
        return \App\Services\ImportCalculator::calculateAdValorem(100, 20, 0, 0.10, 0.18, 0.05);
    });
    
    Route::get('/debug/setup', function () {
        // Quick setup for testing
        return view('debug.setup');
    });
}