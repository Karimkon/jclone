<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ListingApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Listing variation routes
Route::prefix('listings')->group(function () {
    Route::get('{listing}/check-variations', [ListingApiController::class, 'checkVariations'])
        ->name('api.listings.check-variations');
    
    Route::get('{listing}/variations', [ListingApiController::class, 'getVariations'])
        ->name('api.listings.variations');
});