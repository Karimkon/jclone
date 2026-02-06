<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\VendorSubscription;
use App\Models\SubscriptionPayment;
use App\Services\PesapalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VendorSubscriptionController extends Controller
{
    protected $pesapalService;

    public function __construct(PesapalService $pesapalService)
    {
        $this->pesapalService = $pesapalService;
    }

    /**
     * Show subscription management page
     */
    public function index()
    {
        $vendor = auth()->user()->vendorProfile;

        if (!$vendor) {
            return redirect()->route('vendor.onboard.create')
                ->with('error', 'Please complete your vendor profile first.');
        }

        // Get all available plans
        $plans = SubscriptionPlan::active()->ordered()->get();

        // Get current subscription
        $currentSubscription = $vendor->activeSubscription;
        $currentSubscription?->load('plan');

        // Get payment history
        $paymentHistory = SubscriptionPayment::where('vendor_profile_id', $vendor->id)
            ->with('vendorSubscription.plan')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('vendor.subscription.index', compact(
            'vendor',
            'plans',
            'currentSubscription',
            'paymentHistory'
        ));
    }

    /**
     * Subscribe to a plan
     */
    public function subscribe(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
        ]);

        $vendor = auth()->user()->vendorProfile;

        if (!$vendor) {
            return back()->with('error', 'Vendor profile not found.');
        }

        $plan = SubscriptionPlan::active()->findOrFail($request->plan_id);

        // Check if subscribing to the same plan
        $currentSub = $vendor->activeSubscription;
        if ($currentSub && $currentSub->subscription_plan_id == $plan->id && $currentSub->status == 'active') {
            return back()->with('info', 'You are already subscribed to this plan.');
        }

        // Handle free plan
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
                'auto_renew' => $request->boolean('auto_renew', false),
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
            $user = auth()->user();
            $callbackUrl = route('vendor.subscription.callback');

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

            // Redirect to Pesapal payment page
            return redirect()->away($pesapalResponse['redirect_url']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Subscription payment failed: ' . $e->getMessage());

            return back()->with('error', 'Failed to initiate payment. Please try again.');
        }
    }

    /**
     * Handle free subscription
     */
    protected function handleFreeSubscription($vendor, $plan)
    {
        // Cancel any existing subscriptions
        $vendor->subscriptions()
            ->where('status', 'active')
            ->update(['status' => 'cancelled']);

        // Create and activate free subscription
        VendorSubscription::create([
            'vendor_profile_id' => $vendor->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
            'expires_at' => now()->addYears(100),
            'auto_renew' => false,
        ]);

        return redirect()->route('vendor.subscription.index')
            ->with('success', 'Successfully subscribed to Free plan!');
    }

    /**
     * Payment callback from Pesapal
     */
    public function paymentCallback(Request $request)
    {
        $orderTrackingId = $request->input('OrderTrackingId');
        $merchantReference = $request->input('OrderMerchantReference');

        Log::info('Vendor subscription payment callback', [
            'order_tracking_id' => $orderTrackingId,
            'merchant_reference' => $merchantReference,
        ]);

        if (!$merchantReference) {
            return redirect()->route('vendor.subscription.index')
                ->with('error', 'Invalid payment callback.');
        }

        $payment = SubscriptionPayment::where('pesapal_merchant_reference', $merchantReference)->first();

        if (!$payment) {
            return redirect()->route('vendor.subscription.index')
                ->with('error', 'Payment not found.');
        }

        // Check payment status with Pesapal
        $status = $this->pesapalService->getTransactionStatus($orderTrackingId);

        if ($this->pesapalService->isPaymentSuccessful($status ?? [])) {
            $this->activateSubscription($payment, $status);
            return redirect()->route('vendor.subscription.index')
                ->with('success', 'Payment successful! Your subscription is now active.');
        }

        return redirect()->route('vendor.subscription.index')
            ->with('info', 'Payment is being processed. Please wait...');
    }

    /**
     * Activate subscription after successful payment
     */
    protected function activateSubscription(SubscriptionPayment $payment, array $status)
    {
        if ($payment->isCompleted()) {
            return;
        }

        DB::transaction(function () use ($payment, $status) {
            // Mark payment as completed
            $payment->markAsCompleted($status);

            // Cancel any other active subscriptions
            VendorSubscription::where('vendor_profile_id', $payment->vendor_profile_id)
                ->where('id', '!=', $payment->vendor_subscription_id)
                ->where('status', 'active')
                ->update(['status' => 'cancelled']);

            // Activate the subscription
            $payment->vendorSubscription->activate();
        });

        Log::info('Vendor subscription activated via web', [
            'subscription_id' => $payment->vendor_subscription_id,
            'vendor_id' => $payment->vendor_profile_id,
        ]);
    }

    /**
     * Toggle auto-renew
     */
    public function toggleAutoRenew(Request $request)
    {
        $vendor = auth()->user()->vendorProfile;
        $subscription = $vendor?->activeSubscription;

        if (!$subscription || $subscription->plan->is_free_plan) {
            return back()->with('error', 'No paid subscription found.');
        }

        $subscription->update([
            'auto_renew' => !$subscription->auto_renew,
        ]);

        $message = $subscription->auto_renew
            ? 'Auto-renewal enabled.'
            : 'Auto-renewal disabled.';

        return back()->with('success', $message);
    }

    /**
     * Cancel subscription (stops auto-renew)
     */
    public function cancel(Request $request)
    {
        $vendor = auth()->user()->vendorProfile;
        $subscription = $vendor?->activeSubscription;

        if (!$subscription) {
            return back()->with('error', 'No active subscription to cancel.');
        }

        if ($subscription->plan->is_free_plan) {
            return back()->with('error', 'Cannot cancel free subscription.');
        }

        $subscription->update(['auto_renew' => false]);

        return back()->with('success', 'Auto-renewal cancelled. Your subscription will expire on ' . $subscription->expires_at->format('M d, Y'));
    }
}
