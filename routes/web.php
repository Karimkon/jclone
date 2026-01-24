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
use App\Http\Controllers\Buyer\ReviewController;
use App\Http\Controllers\Vendor\VendorReviewController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\Payment\CheckoutPaymentController;
use App\Http\Controllers\Payment\FlutterwaveWebhookController;
use App\Http\Controllers\Marketplace\JobsServicesController;
use App\Http\Controllers\Vendor\VendorJobController;
use App\Http\Controllers\Vendor\VendorServiceController;
use App\Http\Controllers\Buyer\BuyerJobsServicesController;
use App\Http\Controllers\Admin\ContactMessageController;
use \App\Http\Controllers\Admin\ReportController;
use \App\Http\Controllers\Admin\AdminProfileController;
use \App\Http\Controllers\Admin\AdminSettingsController;

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

// Account Deletion Request (Google Play Store requirement)
Route::get('/delete-account', function () {
    return view('auth.delete-account');
})->name('delete-account');

Route::get('/vendors/{vendor}', [App\Http\Controllers\Marketplace\VendorController::class, 'showStore'])->name('vendor.store.show');

Route::post('/delete-account', function (\Illuminate\Http\Request $request) {
    $request->validate([
        'email' => 'required|email',
        'confirm' => 'required|accepted',
    ]);

    $user = \App\Models\User::where('email', $request->email)->first();

    if (!$user) {
        return back()->with('error', 'No account found with this email address.');
    }

    // Check if there's already a pending deletion request
    $existingRequest = \DB::table('account_deletion_requests')
        ->where('user_id', $user->id)
        ->where('status', 'pending')
        ->first();

    if ($existingRequest) {
        return back()->with('error', 'A deletion request is already pending for this account. You will receive an email once processed.');
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

    return back()->with('success', 'Your account deletion request has been submitted. You will receive an email confirmation shortly. Your account will be deleted within 30 days.');
})->name('delete-account.request');

// Jobs
Route::prefix('jobs')->name('jobs.')->group(function () {
    Route::get('/', [JobsServicesController::class, 'jobs'])->name('index');
    Route::get('/{slug}', [JobsServicesController::class, 'showJob'])->name('show');
    Route::post('/{slug}/apply', [JobsServicesController::class, 'applyJob'])->name('apply')->middleware('auth');
});

// Services
Route::prefix('services')->name('services.')->group(function () {
    Route::get('/', [JobsServicesController::class, 'services'])->name('index');
    Route::get('/{slug}', [JobsServicesController::class, 'showService'])->name('show');
    Route::post('/{slug}/request', [JobsServicesController::class, 'requestService'])->name('request')->middleware('auth');
    Route::post('/{slug}/inquiry', [JobsServicesController::class, 'sendInquiry'])->name('inquiry');
});

// Category
Route::get('/category/{slug}', [JobsServicesController::class, 'category'])->name('category.show');

// ====================
// VENDOR ROUTES - Manage Jobs
// ====================
Route::middleware(['auth', 'check.vendor.status'])->prefix('vendor/jobs')->name('vendor.jobs.')->group(function () {
    Route::get('/', [VendorJobController::class, 'index'])->name('index');
    Route::get('/create', [VendorJobController::class, 'create'])->name('create');
    Route::post('/', [VendorJobController::class, 'store'])->name('store');
    Route::get('/{id}', [VendorJobController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [VendorJobController::class, 'edit'])->name('edit');
    Route::put('/{id}', [VendorJobController::class, 'update'])->name('update');
    Route::delete('/{id}', [VendorJobController::class, 'destroy'])->name('destroy');
    Route::post('/{id}/toggle', [VendorJobController::class, 'toggleStatus'])->name('toggle');
    
    // Applications
    Route::get('/{jobId}/applications/{applicationId}', [VendorJobController::class, 'showApplication'])->name('applications.show');
    Route::post('/{jobId}/applications/{applicationId}/status', [VendorJobController::class, 'updateApplicationStatus'])->name('applications.status');
    Route::get('/{jobId}/applications/{applicationId}/cv', [VendorJobController::class, 'downloadCV'])->name('applications.cv');
});


// ====================
// VENDOR ROUTES - Manage Services
// ====================
Route::middleware(['auth', 'check.vendor.status'])->prefix('vendor/services')->name('vendor.services.')->group(function () {
    Route::get('/', [VendorServiceController::class, 'index'])->name('index');
    Route::get('/create', [VendorServiceController::class, 'create'])->name('create');
    Route::post('/', [VendorServiceController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [VendorServiceController::class, 'edit'])->name('edit');
    Route::put('/{id}', [VendorServiceController::class, 'update'])->name('update');
    Route::delete('/{id}', [VendorServiceController::class, 'destroy'])->name('destroy');
    Route::post('/{id}/toggle', [VendorServiceController::class, 'toggleStatus'])->name('toggle');
    Route::delete('/{id}/image', [VendorServiceController::class, 'deleteImage'])->name('delete-image');
    
    // Service Requests
    Route::get('/requests', [VendorServiceController::class, 'requests'])->name('requests');
    Route::get('/requests/{id}', [VendorServiceController::class, 'showRequest'])->name('requests.show');
    Route::post('/requests/{id}/quote', [VendorServiceController::class, 'submitQuote'])->name('requests.quote');
    Route::post('/requests/{id}/status', [VendorServiceController::class, 'updateRequestStatus'])->name('requests.status');
    
    // Inquiries
    Route::get('/inquiries', [VendorServiceController::class, 'inquiries'])->name('inquiries');
    Route::post('/inquiries/{id}/status', [VendorServiceController::class, 'updateInquiryStatus'])->name('inquiries.status');
    
    // Reviews
    Route::get('/reviews', [VendorServiceController::class, 'reviews'])->name('reviews');
    Route::post('/reviews/{id}/respond', [VendorServiceController::class, 'respondToReview'])->name('reviews.respond');
});


// ====================
// BUYER ROUTES - My Applications & Service Requests
// ====================
Route::middleware(['auth'])->prefix('buyer')->name('buyer.')->group(function () {
    // Job Applications
    Route::get('/my-applications', [BuyerJobsServicesController::class, 'myApplications'])->name('applications.index');
    Route::get('/my-applications/{id}', [BuyerJobsServicesController::class, 'showApplication'])->name('applications.show');
    Route::delete('/my-applications/{id}', [BuyerJobsServicesController::class, 'withdrawApplication'])->name('applications.withdraw');
    
    // Service Requests
    Route::get('/service-requests', [BuyerJobsServicesController::class, 'myServiceRequests'])->name('service-requests.index');
    Route::get('/service-requests/{id}', [BuyerJobsServicesController::class, 'showServiceRequest'])->name('service-requests.show');
    Route::post('/service-requests/{id}/accept', [BuyerJobsServicesController::class, 'acceptQuote'])->name('service-requests.accept');
    Route::post('/service-requests/{id}/cancel', [BuyerJobsServicesController::class, 'cancelServiceRequest'])->name('service-requests.cancel');
    Route::post('/service-requests/{id}/complete', [BuyerJobsServicesController::class, 'confirmCompletion'])->name('service-requests.complete');
    
    // Reviews
    Route::get('/service-requests/{id}/review', [BuyerJobsServicesController::class, 'showReviewForm'])->name('service-requests.review');
    Route::post('/service-requests/{id}/review', [BuyerJobsServicesController::class, 'submitReview'])->name('service-requests.review.submit');
});

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

// Static Pages
Route::get('/about', [LandingController::class, 'about'])->name('site.about');
Route::get('/contact', [LandingController::class, 'contact'])->name('site.contact');
Route::post('/contact', [LandingController::class, 'submitContact'])->name('site.contact.submit');
Route::get('/faq', [LandingController::class, 'faq'])->name('site.faq');
Route::get('/terms', [LandingController::class, 'terms'])->name('site.terms');
Route::get('/privacy', [LandingController::class, 'privacy'])->name('site.privacy');
Route::get('/vendor-benefits', [LandingController::class, 'vendorBenefits'])->name('site.vendorBenefits');
Route::get('/how-it-works', [LandingController::class, 'howItWorks'])->name('site.howItWorks');
Route::get('/returns-policy', [LandingController::class, 'returns'])->name('site.returns');


// ====================
// CHAT ROUTES (require authentication)
// ====================
Route::middleware(['auth'])->prefix('chat')->name('chat.')->group(function () {
    // Conversations list
    Route::get('/', [ChatController::class, 'index'])->name('index');
    
    // Start new conversation (from product page)
    Route::post('/start', [ChatController::class, 'startConversation'])->name('start');
    
    // IMPORTANT: Put specific routes BEFORE the {conversation} wildcard
    // Get unread count (for header badge) - MOVED BEFORE {conversation}
    Route::get('/unread-count', [ChatController::class, 'getUnreadCount'])->name('unread-count');
    
    // View specific conversation
    Route::get('/{conversation}', [ChatController::class, 'show'])->name('show');
    
    // Send message in conversation
    Route::post('/{conversation}/send', [ChatController::class, 'sendMessage'])->name('send');
    
    // Get new messages (for polling)
    Route::get('/{conversation}/new-messages', [ChatController::class, 'getNewMessages'])->name('new-messages');
    
    // Archive conversation
    Route::post('/{conversation}/archive', [ChatController::class, 'archive'])->name('archive');
    
    // Delete message
    Route::delete('/message/{message}', [ChatController::class, 'deleteMessage'])->name('delete-message');
});


// ====================
// PAYMENT ROUTES (Buyer Checkout)
// ====================
Route::middleware(['auth'])->prefix('payment')->name('payment.')->group(function () {
    // Show payment options for an order
    Route::get('/order/{order}', [CheckoutPaymentController::class, 'showPaymentOptions'])
        ->name('options');
    
    // Initialize mobile money payment (PesaPal)
    Route::post('/order/{order}/mobile-money', [CheckoutPaymentController::class, 'initializePesapalPayment'])
        ->name('mobile-money.initiate');
    
    // Initialize card payment (Flutterwave)
    Route::post('/order/{order}/card', [CheckoutPaymentController::class, 'initializeFlutterwavePayment'])
        ->name('card.initiate');
    
    // Retry failed payment
    Route::get('/order/{order}/retry', [CheckoutPaymentController::class, 'retryPayment'])
        ->name('retry');
    
    // Check payment status (for AJAX polling)
    Route::get('/order/{order}/status', [CheckoutPaymentController::class, 'checkPaymentStatus'])
        ->name('status.check');
});

// ====================
// PAYMENT CALLBACKS (No auth - external redirects)
// ====================
Route::prefix('payment')->name('payment.')->group(function () {
    // Flutterwave callback (user redirect after payment)
    Route::get('/flutterwave/callback', [CheckoutPaymentController::class, 'flutterwaveCallback'])
        ->name('flutterwave.callback');
    
    // PesaPal callback (user redirect after payment)
    Route::get('/pesapal/callback', [CheckoutPaymentController::class, 'pesapalCallback'])
        ->name('pesapal.callback');
    
    // PesaPal IPN (Instant Payment Notification)
    Route::post('/pesapal/ipn', [CheckoutPaymentController::class, 'pesapalIPN'])
        ->name('pesapal.ipn');
});

// ====================
// BUYER CALLBACK ROUTES (Public - no auth required, but can be used by authenticated users)
// ====================
Route::post('/buyer/listings/{listing}/callback', [App\Http\Controllers\Buyer\CallbackRequestController::class, 'store'])
    ->name('buyer.listings.callback');

// ====================
// VENDOR CALLBACK ROUTES (Authenticated vendors only)
// ====================
Route::middleware(['auth', 'check.vendor.status'])->prefix('vendor')->name('vendor.')->group(function () {
    // Callback management
    Route::prefix('callbacks')->name('callbacks.')->group(function () {
        Route::get('/', [App\Http\Controllers\Vendor\VendorCallbackController::class, 'index'])->name('index');
        Route::get('/{callback}', [App\Http\Controllers\Vendor\VendorCallbackController::class, 'show'])->name('show');
        Route::post('/{callback}/status', [App\Http\Controllers\Vendor\VendorCallbackController::class, 'updateStatus'])->name('update-status');
    });
});

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

        // View and Respond to Reviews
        Route::get('/reviews', [VendorReviewController::class, 'index'])->name('reviews.index');
        Route::get('/reviews/{review}', [VendorReviewController::class, 'show'])->name('reviews.show');
        Route::post('/reviews/{review}/respond', [VendorReviewController::class, 'respond'])->name('reviews.respond');
        Route::put('/reviews/{review}/respond', [VendorReviewController::class, 'updateResponse'])->name('reviews.update-response');
        Route::delete('/reviews/{review}/respond', [VendorReviewController::class, 'deleteResponse'])->name('reviews.delete-response');

        Route::get('/performance', [VendorOrderController::class, 'performance'])->name('performance');
    });

    // ====================
    // PUBLIC API ROUTES (for AJAX)
    // ====================
    Route::get('/api/listings/{listing}/reviews', [ReviewController::class, 'getListingReviews'])->name('api.listings.reviews');

    // ====================
    // ADMIN ROUTES
    // ====================
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        
        // Admin User Management Routes
    Route::resource('users', AdminUserController::class);
    
    // Advertisement Routes
    Route::resource('advertisements', App\Http\Controllers\Admin\AdvertisementController::class);
    Route::post('advertisements/{advertisement}/toggle', [App\Http\Controllers\Admin\AdvertisementController::class, 'toggleStatus'])->name('advertisements.toggle');
        Route::post('/users/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::post('/users/{user}/verify-email', [AdminUserController::class, 'verifyEmail'])->name('users.verify-email');
        Route::post('/users/{user}/toggle-verified', [AdminUserController::class, 'toggleVerified'])->name('users.toggle-verified');

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
        
        // Orders
        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\AdminOrderController::class, 'index'])->name('index');
            Route::get('/{order}', [\App\Http\Controllers\Admin\AdminOrderController::class, 'show'])->name('show');
            Route::post('/{order}/status', [\App\Http\Controllers\Admin\AdminOrderController::class, 'updateStatus'])->name('update-status');
            Route::post('/{order}/refund', [\App\Http\Controllers\Admin\AdminOrderController::class, 'refund'])->name('refund');
            Route::get('/{order}/invoice', [\App\Http\Controllers\Admin\AdminOrderController::class, 'invoice'])->name('invoice');
            Route::get('/export', [\App\Http\Controllers\Admin\AdminOrderController::class, 'export'])->name('export');
        });
        
        // Disputes
        Route::get('/disputes', [\App\Http\Controllers\Admin\DisputeController::class, 'index'])->name('disputes.index');
        Route::get('/disputes/{dispute}', [\App\Http\Controllers\Admin\DisputeController::class, 'show'])->name('disputes.show');

        // Escrow management
        Route::prefix('escrows')->name('escrows.')->group(function () {
            Route::get('/pending', [EscrowController::class, 'pending'])->name('pending');
            Route::post('/{escrow}/release', [EscrowController::class, 'release'])->name('release');
            Route::post('/{escrow}/refund', [EscrowController::class, 'refund'])->name('refund');
        });

        // Document Routes
        Route::prefix('documents')->name('documents.')->group(function () {
            Route::get('/{document}/view', [AdminVendorController::class, 'viewDocument'])->name('view');
            Route::post('/{id}/verify', [AdminVendorController::class, 'verifyDocument'])->name('verify');
            Route::post('/{id}/reject', [AdminVendorController::class, 'rejectDocument'])->name('reject');
        });

        // Withdrawal Management
        Route::prefix('withdrawals')->name('withdrawals.')->group(function () {
            Route::get('/pending', [\App\Http\Controllers\Admin\WithdrawalController::class, 'pending'])->name('pending');
            Route::get('/', [\App\Http\Controllers\Admin\WithdrawalController::class, 'index'])->name('index');
            Route::get('/{withdrawal}', [\App\Http\Controllers\Admin\WithdrawalController::class, 'show'])->name('show');
            Route::post('/{withdrawal}/approve', [\App\Http\Controllers\Admin\WithdrawalController::class, 'approve'])->name('approve');
            Route::post('/{withdrawal}/reject', [\App\Http\Controllers\Admin\WithdrawalController::class, 'reject'])->name('reject');
            Route::post('/{withdrawal}/process', [\App\Http\Controllers\Admin\WithdrawalController::class, 'process'])->name('process');
            Route::post('/{withdrawal}/complete', [\App\Http\Controllers\Admin\WithdrawalController::class, 'complete'])->name('complete');
        });

      // Admin Product Management Routes :
    Route::prefix('listings')->name('listings.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\AdminListingController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\AdminListingController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\AdminListingController::class, 'store'])->name('store');
        Route::get('/{listing}', [\App\Http\Controllers\Admin\AdminListingController::class, 'show'])->name('show');
        Route::get('/{listing}/edit', [\App\Http\Controllers\Admin\AdminListingController::class, 'edit'])->name('edit');
        Route::put('/{listing}', [\App\Http\Controllers\Admin\AdminListingController::class, 'update'])->name('update');
        Route::delete('/{listing}', [\App\Http\Controllers\Admin\AdminListingController::class, 'destroy'])->name('destroy');
        Route::post('/{listing}/toggle-status', [\App\Http\Controllers\Admin\AdminListingController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/{listing}/feature', [\App\Http\Controllers\Admin\AdminListingController::class, 'toggleFeatured'])->name('toggle-featured');
        Route::post('/bulk-actions', [\App\Http\Controllers\Admin\AdminListingController::class, 'bulkActions'])->name('bulk-actions');
        Route::get('/export/csv', [\App\Http\Controllers\Admin\AdminListingController::class, 'exportCSV'])->name('export.csv');
        Route::post('/import/csv', [\App\Http\Controllers\Admin\AdminListingController::class, 'importCSV'])->name('import.csv');
    });
    
     Route::prefix('contact-messages')->name('contact-messages.')->group(function () {
        Route::get('/', [ContactMessageController::class, 'index'])->name('index');
        Route::get('/{id}', [ContactMessageController::class, 'show'])->name('show');
        Route::post('/{id}/status', [ContactMessageController::class, 'updateStatus'])->name('update-status');
        Route::post('/{id}/response', [ContactMessageController::class, 'sendResponse'])->name('send-response');
        Route::delete('/{id}', [ContactMessageController::class, 'destroy'])->name('destroy');
        Route::post('/bulk-actions', [ContactMessageController::class, 'bulkActions'])->name('bulk-actions');
    });

    Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/', [ReportController::class, 'index'])->name('index');
    Route::get('/sales-detailed', [ReportController::class, 'salesDetailed'])->name('sales.detailed');
    Route::get('/financial', [ReportController::class, 'financialReport'])->name('financial');
    Route::get('/user-acquisition', [ReportController::class, 'userAcquisition'])->name('user.acquisition');
    Route::get('/vendor-performance', [ReportController::class, 'vendorPerformance'])->name('vendor.performance');
    Route::get('/category-performance', [ReportController::class, 'categoryPerformance'])->name('category.performance');
    Route::get('/platform-analytics', [ReportController::class, 'platformAnalytics'])->name('platform.analytics');
    Route::get('/export', [ReportController::class, 'export'])->name('export');
});

// Profile & Settings Routes
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [AdminProfileController::class, 'index'])->name('index');
        Route::put('/update', [AdminProfileController::class, 'update'])->name('update');
        Route::put('/change-password', [AdminProfileController::class, 'changePassword'])->name('change-password');
        Route::post('/upload-photo', [AdminProfileController::class, 'uploadPhoto'])->name('upload-photo');
        Route::delete('/remove-photo', [AdminProfileController::class, 'removePhoto'])->name('remove-photo');
    });
    
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [AdminSettingsController::class, 'index'])->name('index');
        Route::put('/general', [AdminSettingsController::class, 'updateGeneral'])->name('general.update');
        Route::put('/email', [AdminSettingsController::class, 'updateEmail'])->name('email.update');
        Route::put('/notifications', [AdminSettingsController::class, 'updateNotifications'])->name('notifications.update');
        Route::put('/security', [AdminSettingsController::class, 'updateSecurity'])->name('security.update');
        Route::post('/backup', [AdminSettingsController::class, 'createBackup'])->name('backup.create');
        Route::get('/logs', [AdminSettingsController::class, 'viewLogs'])->name('logs');
        Route::delete('/logs/clear', [AdminSettingsController::class, 'clearLogs'])->name('logs.clear');
    });

    Route::get('/activity-logs', function () {
        $logs = \App\Models\ActivityLog::orderBy('created_at', 'desc')->paginate(20);
        return view('admin.activity-logs.index', compact('logs'));
    })->name('activity-logs');
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
    Route::middleware(['auth', 'role:buyer,vendor_local,vendor_international'])->prefix('buyer')->name('buyer.')->group(function () {
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
            Route::get('/transactions/export', [\App\Http\Controllers\Buyer\WalletController::class, 'exportTransactions'])->name('transactions.export');
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
             Route::get('/{order}/payment', [\App\Http\Controllers\Buyer\OrderController::class, 'payment'])->name('payment'); 
            Route::post('/{order}/pay-with-wallet', [\App\Http\Controllers\Buyer\OrderController::class, 'payWithWallet'])->name('pay-with-wallet');
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

        // Review Management
        Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');
        Route::get('/reviews/create', [ReviewController::class, 'create'])->name('reviews.create');
        Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');
        Route::get('/reviews/{review}/edit', [ReviewController::class, 'edit'])->name('reviews.edit');
        Route::put('/reviews/{review}', [ReviewController::class, 'update'])->name('reviews.update');
        Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
        
         // Shipping Addresses
        Route::resource('addresses', \App\Http\Controllers\Buyer\ShippingAddressController::class);
        Route::post('addresses/{id}/set-default', [\App\Http\Controllers\Buyer\ShippingAddressController::class, 'setDefault'])
            ->name('addresses.set-default');

        // Review Voting
        Route::post('/reviews/{review}/vote', [ReviewController::class, 'vote'])->name('reviews.vote');
    });
});


// ====================
// IMPORT CALCULATOR (Public API)
// ====================
Route::prefix('api')->name('api.')->group(function () {
    Route::get('/import-calculate', [ImportController::class, 'calculateApi'])->name('import.calculate');
    Route::get('/listings/{listing}/check-variations', function($listing) {
        try {
            $listing = \App\Models\Listing::findOrFail($listing);
            
            return response()->json([
                'has_variations' => $listing->has_variations ?? false,
                'available_colors' => $listing->available_colors ?? [],
                'available_sizes' => $listing->available_sizes ?? [],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Product not found',
                'has_variations' => false,
                'available_colors' => [],
                'available_sizes' => [],
            ], 200); // Return 200 even if not found to avoid breaking the flow
        }
    })->name('listings.check-variations');
    
    Route::get('/listings/{listing}/variations', function($listing) {
        try {
            $listing = \App\Models\Listing::with('variants')->findOrFail($listing);
            
            $variations = $listing->variants->map(function($variant) {
                return [
                    'id' => $variant->id,
                    'sku' => $variant->sku,
                    'price' => $variant->price,
                    'display_price' => $variant->display_price ?? $variant->price,
                    'stock' => $variant->stock,
                    'attributes' => $variant->attributes ?? [],
                ];
            });
            
            return response()->json([
                'variations' => $variations,
                'colors' => $listing->available_colors ?? [],
                'sizes' => $listing->available_sizes ?? [],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Product not found',
                'variations' => [],
                'colors' => [],
                'sizes' => [],
            ], 200);
        }
    })->name('listings.variations'); 
});

// ====================
// WEBHOOKS (No CSRF protection - server-to-server)
// These are defined separately and excluded from CSRF in VerifyCsrfToken middleware
// ====================
Route::prefix('webhooks')->name('webhooks.')->group(function () {

    // PesaPal IPN (Instant Payment Notification)
    Route::match(['get', 'post'], '/pesapal/ipn', [CheckoutPaymentController::class, 'pesapalIPN'])
        ->name('pesapal.ipn');
    // Flutterwave webhook
    Route::post('/flutterwave', [FlutterwaveWebhookController::class, 'webhook'])->name('flutterwave');
    
});

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


// Analytics API (for AJAX tracking)
Route::prefix('api/analytics')->name('api.analytics.')->group(function () {
    Route::post('/track', [App\Http\Controllers\Api\AnalyticsApiController::class, 'track'])
        ->name('track');
});

// Admin Analytics Routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::prefix('analytics/products')->name('analytics.products.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\ProductAnalyticsController::class, 'index'])
            ->name('index');
        Route::get('/{id}', [App\Http\Controllers\Admin\ProductAnalyticsController::class, 'show'])
            ->name('show');
        Route::get('/export/clicked-not-bought', [App\Http\Controllers\Admin\ProductAnalyticsController::class, 'exportClickedNotBought'])
            ->name('export-clicked-not-bought');
    });
});

// Vendor Analytics Routes
Route::middleware(['auth', 'check.vendor.status'])->prefix('vendor')->name('vendor.')->group(function () {
    Route::get('/analytics', [App\Http\Controllers\Vendor\VendorAnalyticsController::class, 'index'])
        ->name('analytics');
    Route::get('/analytics/{id}', [App\Http\Controllers\Vendor\VendorAnalyticsController::class, 'show'])
        ->name('analytics.show');
});

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