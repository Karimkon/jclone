<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\VendorSubscription;
use App\Models\SubscriptionPayment;
use App\Models\VendorProfile;
use App\Services\PesapalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    protected $pesapalService;

    public function __construct(PesapalService $pesapalService)
    {
        $this->pesapalService = $pesapalService;
    }

    /**
     * Get all available subscription plans
     */
    public function plans()
    {
        $plans = SubscriptionPlan::active()
            ->ordered()
            ->get()
            ->map(function ($plan) {
                return [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'slug' => $plan->slug,
                    'price' => (float) $plan->price,
                    'billing_cycle' => $plan->billing_cycle,
                    'boost_multiplier' => (float) $plan->boost_multiplier,
                    'max_featured_listings' => $plan->max_featured_listings,
                    'badge_enabled' => $plan->badge_enabled,
                    'badge_text' => $plan->badge_text,
                    'features' => $plan->features ?? [],
                    'is_free_plan' => $plan->is_free_plan,
                ];
            });

        return response()->json([
            'success' => true,
            'plans' => $plans,
        ]);
    }

    /**
     * Get current vendor subscription
     */
    public function current(Request $request)
    {
        $vendor = $this->getVendorProfile($request);

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor profile not found',
            ], 404);
        }

        $subscription = $vendor->activeSubscription;

        if (!$subscription) {
            return response()->json([
                'success' => true,
                'subscription' => null,
                'plan_name' => 'Free',
                'message' => 'No active subscription',
            ]);
        }

        $subscription->load('plan');

        return response()->json([
            'success' => true,
            'subscription' => [
                'id' => $subscription->id,
                'plan' => [
                    'id' => $subscription->plan->id,
                    'name' => $subscription->plan->name,
                    'slug' => $subscription->plan->slug,
                    'price' => (float) $subscription->plan->price,
                    'billing_cycle' => $subscription->plan->billing_cycle,
                    'boost_multiplier' => (float) $subscription->plan->boost_multiplier,
                    'badge_enabled' => $subscription->plan->badge_enabled,
                    'badge_text' => $subscription->plan->badge_text,
                    'features' => $subscription->plan->features ?? [],
                ],
                'status' => $subscription->status,
                'starts_at' => $subscription->starts_at?->toISOString(),
                'expires_at' => $subscription->expires_at?->toISOString(),
                'days_remaining' => $subscription->daysRemaining(),
                'auto_renew' => $subscription->auto_renew,
                'is_expiring_soon' => $subscription->isExpiringSoon(7),
            ],
        ]);
    }

    /**
     * Subscribe to a plan
     */
    public function subscribe(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
        ]);

        $vendor = $this->getVendorProfile($request);

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor profile not found',
            ], 404);
        }

        $plan = SubscriptionPlan::active()->findOrFail($request->plan_id);

        // Check if plan is free
        if ($plan->is_free_plan) {
            return $this->handleFreeSubscription($vendor, $plan);
        }

        DB::beginTransaction();
        try {
            // Create pending subscription
            $subscription = VendorSubscription::create([
                'vendor_profile_id' => $vendor->id,
                'subscription_plan_id' => $plan->id,
                'status' => 'pending',
                'auto_renew' => $request->input('auto_renew', false),
            ]);

            // Create pending payment
            $merchantReference = SubscriptionPayment::generateMerchantReference();
            $payment = SubscriptionPayment::create([
                'vendor_subscription_id' => $subscription->id,
                'vendor_profile_id' => $vendor->id,
                'pesapal_merchant_reference' => $merchantReference,
                'amount' => $plan->price,
                'currency' => 'UGX',
                'status' => 'pending',
            ]);

            // Prepare Pesapal order
            $user = $request->user();
            $callbackUrl = url('/api/vendor/subscription/payment-callback');

            $orderData = [
                'id' => $merchantReference,
                'currency' => 'UGX',
                'amount' => (float) $plan->price,
                'description' => "BebaMart {$plan->name} Subscription ({$plan->billing_cycle})",
                'callback_url' => $callbackUrl,
                'notification_id' => config('services.pesapal.notification_id'),
                'billing_address' => [
                    'email_address' => $user->email,
                    'phone_number' => $user->phone ?? '',
                    'first_name' => $user->name ?? 'Vendor',
                    'last_name' => '',
                ],
            ];

            $pesapalResponse = $this->pesapalService->submitOrder($orderData);

            if (!isset($pesapalResponse['redirect_url'])) {
                throw new \Exception('Failed to get Pesapal redirect URL');
            }

            // Update payment with Pesapal tracking ID
            $payment->update([
                'pesapal_order_tracking_id' => $pesapalResponse['order_tracking_id'] ?? null,
                'payment_response' => $pesapalResponse,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Redirecting to payment',
                'payment_url' => $pesapalResponse['redirect_url'],
                'merchant_reference' => $merchantReference,
                'subscription_id' => $subscription->id,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Subscription creation failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to initiate subscription payment. Please try again.',
            ], 500);
        }
    }

    /**
     * Handle free subscription
     */
    protected function handleFreeSubscription(VendorProfile $vendor, SubscriptionPlan $plan)
    {
        // Cancel any existing subscriptions
        $vendor->subscriptions()
            ->where('status', 'active')
            ->update(['status' => 'cancelled']);

        // Create and activate free subscription
        $subscription = VendorSubscription::create([
            'vendor_profile_id' => $vendor->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
            'expires_at' => now()->addYears(100), // Effectively never expires
            'auto_renew' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully subscribed to Free plan',
            'subscription' => [
                'id' => $subscription->id,
                'plan_name' => $plan->name,
                'status' => 'active',
            ],
        ]);
    }

    /**
     * Payment callback from Pesapal redirect
     */
    public function paymentCallback(Request $request)
    {
        $orderTrackingId = $request->input('OrderTrackingId');
        $merchantReference = $request->input('OrderMerchantReference');

        Log::info('Subscription payment callback', [
            'order_tracking_id' => $orderTrackingId,
            'merchant_reference' => $merchantReference,
        ]);

        if (!$merchantReference) {
            return redirect()->away(config('app.flutter_app_url', 'bebamart://') . '/subscription?status=error&message=Invalid+callback');
        }

        $payment = SubscriptionPayment::where('pesapal_merchant_reference', $merchantReference)->first();

        if (!$payment) {
            return redirect()->away(config('app.flutter_app_url', 'bebamart://') . '/subscription?status=error&message=Payment+not+found');
        }

        // Check payment status with Pesapal
        $status = $this->pesapalService->getTransactionStatus($orderTrackingId);

        if ($this->pesapalService->isPaymentSuccessful($status ?? [])) {
            $this->activateSubscription($payment, $status);
            return redirect()->away(config('app.flutter_app_url', 'bebamart://') . '/subscription?status=success');
        }

        return redirect()->away(config('app.flutter_app_url', 'bebamart://') . '/subscription?status=pending');
    }

    /**
     * IPN webhook from Pesapal
     */
    public function ipn(Request $request)
    {
        $orderTrackingId = $request->input('OrderTrackingId');
        $merchantReference = $request->input('OrderMerchantReference');
        $notificationType = $request->input('OrderNotificationType');

        Log::info('Subscription IPN received', [
            'order_tracking_id' => $orderTrackingId,
            'merchant_reference' => $merchantReference,
            'notification_type' => $notificationType,
        ]);

        if (!$merchantReference) {
            return response()->json(['status' => 'error', 'message' => 'Missing merchant reference']);
        }

        $payment = SubscriptionPayment::where('pesapal_merchant_reference', $merchantReference)->first();

        if (!$payment) {
            return response()->json(['status' => 'error', 'message' => 'Payment not found']);
        }

        // Get transaction status from Pesapal
        $status = $this->pesapalService->getTransactionStatus($orderTrackingId);

        if (!$status) {
            return response()->json(['status' => 'error', 'message' => 'Failed to get transaction status']);
        }

        $payment->update([
            'pesapal_order_tracking_id' => $orderTrackingId,
            'payment_response' => $status,
        ]);

        if ($this->pesapalService->isPaymentSuccessful($status)) {
            $this->activateSubscription($payment, $status);
            return response()->json(['status' => 'success', 'message' => 'Payment processed']);
        }

        if (isset($status['status_code']) && $status['status_code'] == 2) {
            $payment->markAsFailed($status);
            $payment->vendorSubscription->update(['status' => 'cancelled']);
        }

        return response()->json(['status' => 'received', 'message' => 'IPN processed']);
    }

    /**
     * Activate subscription after successful payment
     */
    protected function activateSubscription(SubscriptionPayment $payment, array $status)
    {
        if ($payment->isCompleted()) {
            return; // Already processed
        }

        DB::transaction(function () use ($payment, $status) {
            // Mark payment as completed
            $payment->markAsCompleted($status);

            // Cancel any other active subscriptions for this vendor
            VendorSubscription::where('vendor_profile_id', $payment->vendor_profile_id)
                ->where('id', '!=', $payment->vendor_subscription_id)
                ->where('status', 'active')
                ->update(['status' => 'cancelled']);

            // Activate the subscription
            $payment->vendorSubscription->activate();
        });

        Log::info('Subscription activated', [
            'subscription_id' => $payment->vendor_subscription_id,
            'vendor_id' => $payment->vendor_profile_id,
        ]);
    }

    /**
     * Cancel subscription
     */
    public function cancel(Request $request)
    {
        $vendor = $this->getVendorProfile($request);

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor profile not found',
            ], 404);
        }

        $subscription = $vendor->activeSubscription;

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'No active subscription to cancel',
            ], 400);
        }

        // Don't cancel free subscriptions
        if ($subscription->plan->is_free_plan) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot cancel free subscription',
            ], 400);
        }

        $subscription->update(['auto_renew' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Auto-renewal cancelled. Subscription will expire on ' . $subscription->expires_at->format('M d, Y'),
            'expires_at' => $subscription->expires_at->toISOString(),
        ]);
    }

    /**
     * Toggle auto-renew
     */
    public function toggleAutoRenew(Request $request)
    {
        $vendor = $this->getVendorProfile($request);

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor profile not found',
            ], 404);
        }

        $subscription = $vendor->activeSubscription;

        if (!$subscription || $subscription->plan->is_free_plan) {
            return response()->json([
                'success' => false,
                'message' => 'No paid subscription found',
            ], 400);
        }

        $subscription->update([
            'auto_renew' => !$subscription->auto_renew,
        ]);

        return response()->json([
            'success' => true,
            'auto_renew' => $subscription->auto_renew,
            'message' => $subscription->auto_renew
                ? 'Auto-renewal enabled'
                : 'Auto-renewal disabled',
        ]);
    }

    /**
     * Get payment history
     */
    public function paymentHistory(Request $request)
    {
        $vendor = $this->getVendorProfile($request);

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor profile not found',
            ], 404);
        }

        $payments = $vendor->subscriptionPayments()
            ->with('vendorSubscription.plan')
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'payments' => $payments->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'plan_name' => $payment->vendorSubscription?->plan?->name ?? 'Unknown',
                    'amount' => (float) $payment->amount,
                    'currency' => $payment->currency,
                    'status' => $payment->status,
                    'merchant_reference' => $payment->pesapal_merchant_reference,
                    'created_at' => $payment->created_at->toISOString(),
                ];
            }),
            'pagination' => [
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
                'total' => $payments->total(),
            ],
        ]);
    }

    /**
     * Get vendor profile from request
     */
    protected function getVendorProfile(Request $request): ?VendorProfile
    {
        $user = $request->user();

        if (!$user) {
            return null;
        }

        return VendorProfile::where('user_id', $user->id)->first();
    }
}
