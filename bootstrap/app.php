<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        $middleware->alias([
            'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
            'role' => App\Http\Middleware\RoleMiddleware::class,
            'check.vendor.status' => \App\Http\Middleware\CheckVendorStatus::class,
        ]);

        // Return JSON 401 for unauthenticated API requests instead of redirecting
        $middleware->redirectGuestsTo(function ($request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return null; // Return null to trigger 401 response
            }
            return route('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Handle authentication exceptions for API
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated. Please login again.',
                ], 401);
            }
        });
    })->create();
