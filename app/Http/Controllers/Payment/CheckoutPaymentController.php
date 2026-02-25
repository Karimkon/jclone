<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Escrow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\PushNotificationService;

class CheckoutPaymentController extends Controller
{
    protected $pesapalService;

    public function __construct()
    {
        $this->pesapalService = app(\App\Services\PesapalService::class);
    }

    /**
     * Show payment options page
     */
    public function showPaymentOptions(Order $order)
    {
        // Ensure user owns this order
        if ($order->buyer_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        // Ensure order is pending payment
        if (!in_array($order->status, ['payment_pending', 'pending'])) {
            return redirect()->route('buyer.orders.show', $order)
                ->with('error', 'This order has already been processed.');
        }

        return view('buyer.orders.payment', [
            'order' => $order->load(['items.listing.images', 'vendorProfile']),
        ]);
    }

    /**
     * Initialize PesaPal payment (Mobile Money)
     */
    public function initializePesapalPayment(Request $request, Order $order)
    {
        $request->validate([
            'mobile_money_provider' => 'required|in:mtn,airtel',
            'phone_number' => 'required|regex:/^[0-9]{10,15}$/',
        ]);

        // Verify ownership and status
        if ($order->buyer_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if (!in_array($order->status, ['payment_pending', 'pending'])) {
            return response()->json(['success' => false, 'message' => 'Order already processed'], 400);
        }

        $txRef = 'JCLONE-' . time() . '-' . $order->id;

        try {
            // Get buyer info
            $buyer = $order->buyer;
            $nameParts = explode(' ', $buyer->name ?? 'Buyer');
            $firstName = $nameParts[0] ?? 'Buyer';
            $lastName = $nameParts[1] ?? '';

            // Prepare PesaPal order data
            $orderData = [
                'id' => $txRef,
                'currency' => 'UGX',
                'amount' => (float) $order->total,
                'description' => "Payment for Order #{$order->order_number}",
                'callback_url' => route('payment.pesapal.callback'),
                'notification_id' => config('services.pesapal.notification_id'),
                'billing_address' => [
                    'email_address' => $buyer->email ?? 'buyer@bebamart.com',
                    'phone_number' => $request->phone_number,
                    'country_code' => 'UG',
                    'first_name' => $firstName,
                    'middle_name' => '',
                    'last_name' => $lastName,
                    'line_1' => 'Kampala',
                    'city' => 'Kampala',
                    'state' => 'Central',
                    'postal_code' => '256',
                    'zip_code' => '256',
                ],
            ];

            Log::info('Initiating PesaPal payment', [
                'order_id' => $order->id,
                'tx_ref' => $txRef,
                'amount' => $order->total,
                'phone' => $request->phone_number,
            ]);

            // Submit order to PesaPal
            $result = $this->pesapalService->submitOrder($orderData);

            Log::info('PesaPal submitOrder response', ['result' => $result]);

            if (isset($result['redirect_url'])) {
                // Create pending payment record
                $payment = Payment::create([
                    'order_id' => $order->id,
                    'provider' => 'pesapal',
                    'provider_payment_id' => $txRef,
                    'amount' => $order->total,
                    'status' => 'pending',
                    'meta' => [
                        'payment_method' => 'mobile_money',
                        'mobile_provider' => $request->mobile_money_provider,
                        'phone_number' => $request->phone_number,
                        'order_tracking_id' => $result['order_tracking_id'] ?? null,
                        'merchant_reference' => $result['merchant_reference'] ?? $txRef,
                        'initiated_at' => now()->toDateTimeString(),
                        'payment_url' => $result['redirect_url'],
                    ]
                ]);

                // Update order meta - FIXED: ensure meta is always an array
                $currentMeta = $order->meta;
                if (!is_array($currentMeta)) {
                    $currentMeta = [];
                }
                
                $order->update([
                    'meta' => array_merge($currentMeta, [
                        'payment_reference' => $txRef,
                        'mobile_payment_details' => [
                            'provider' => $request->mobile_money_provider,
                            'phone' => $request->phone_number,
                        ]
                    ])
                ]);

                return response()->json([
                    'success' => true,
                    'payment_url' => $result['redirect_url'],
                    'tx_ref' => $txRef,
                    'order_tracking_id' => $result['order_tracking_id'] ?? null,
                ]);
            }

            // Handle error response from PesaPal
            $errorMessage = 'Failed to initialize mobile money payment';
            if (isset($result['error'])) {
                $errorMessage = is_array($result['error']) 
                    ? ($result['error']['message'] ?? $errorMessage)
                    : $result['error'];
            }

            Log::error('PesaPal payment failed - no redirect URL', ['result' => $result]);

            return response()->json([
                'success' => false,
                'message' => $errorMessage
            ], 500);

        } catch (\Exception $e) {
            Log::error('PesaPal payment initialization failed', [
                'error' => $e->getMessage(),
                'order_id' => $order->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Payment initialization failed. Please try again.'
            ], 500);
        }
    }

    /**
     * PesaPal callback handler
     */
    public function pesapalCallback(Request $request)
    {
        $orderTrackingId = $request->query('OrderTrackingId');
        $orderMerchantReference = $request->query('OrderMerchantReference');

        Log::info('PesaPal callback received', [
            'orderTrackingId' => $orderTrackingId,
            'orderMerchantReference' => $orderMerchantReference,
            'all_params' => $request->all()
        ]);

        if (!$orderTrackingId) {
            Log::error('PesaPal callback missing OrderTrackingId');
            return redirect()->route('buyer.orders.index')
                ->with('error', 'Invalid payment response');
        }

        try {
            // Get transaction status from PesaPal
            $status = $this->pesapalService->getTransactionStatus($orderTrackingId);

            Log::info('PesaPal transaction status', ['status' => $status]);

            if ($status && $this->pesapalService->isPaymentSuccessful($status)) {
                return $this->processSuccessfulPayment($orderMerchantReference, $status);
            }

            $statusCode = $status['status_code'] ?? 0;
            $statusDesc = $this->pesapalService->getStatusDescription($statusCode);
            
            Log::warning('PesaPal payment not successful', [
                'status_code' => $statusCode,
                'status_desc' => $statusDesc
            ]);
            
            return $this->handlePaymentFailed($orderMerchantReference, $statusDesc);

        } catch (\Exception $e) {
            Log::error('PesaPal callback error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('buyer.orders.index')
                ->with('error', 'Payment verification failed. Please contact support.');
        }
    }

    /**
     * PesaPal IPN (Instant Payment Notification) handler
     */
    public function pesapalIPN(Request $request)
    {
        Log::info('PesaPal IPN received', $request->all());

        $orderTrackingId = $request->input('OrderTrackingId');
        $orderMerchantReference = $request->input('OrderMerchantReference');
        $notificationType = $request->input('OrderNotificationType');

        if (!$orderTrackingId) {
            return response()->json([
                'orderNotificationType' => $notificationType,
                'orderTrackingId' => $orderTrackingId,
                'orderMerchantReference' => $orderMerchantReference,
                'status' => 400
            ]);
        }

        try {
            $status = $this->pesapalService->getTransactionStatus($orderTrackingId);

            if ($status && $this->pesapalService->isPaymentSuccessful($status)) {
                // Find and update payment
                $payment = Payment::where('provider_payment_id', $orderMerchantReference)
                    ->where('provider', 'pesapal')
                    ->first();

                if ($payment && $payment->status !== 'completed') {
                    $this->completePaymentTransaction($payment, $status);
                }
            }

            return response()->json([
                'orderNotificationType' => $notificationType,
                'orderTrackingId' => $orderTrackingId,
                'orderMerchantReference' => $orderMerchantReference,
                'status' => 200
            ]);

        } catch (\Exception $e) {
            Log::error('PesaPal IPN error', ['error' => $e->getMessage()]);
            
            return response()->json([
                'orderNotificationType' => $notificationType,
                'orderTrackingId' => $orderTrackingId,
                'orderMerchantReference' => $orderMerchantReference,
                'status' => 500
            ]);
        }
    }

    /**
     * Process successful payment
     */
    private function processSuccessfulPayment(string $txRef, array $providerData)
    {
        $payment = Payment::where('provider_payment_id', $txRef)
            ->where('provider', 'pesapal')
            ->first();

        if (!$payment) {
            Log::error("Payment not found for PesaPal", ['tx_ref' => $txRef]);
            return redirect()->route('buyer.orders.index')
                ->with('error', 'Payment record not found');
        }

        if ($payment->status === 'completed') {
            return redirect()->route('buyer.orders.show', $payment->order)
                ->with('success', 'Payment already completed!');
        }

        try {
            $this->completePaymentTransaction($payment, $providerData);

            return redirect()->route('buyer.orders.show', $payment->order)
                ->with('success', 'Payment completed successfully! Your order is being processed.');

        } catch (\Exception $e) {
            Log::error('Payment completion error', [
                'error' => $e->getMessage(),
                'payment_id' => $payment->id
            ]);
            
            return redirect()->route('buyer.orders.index')
                ->with('error', 'Payment recorded but order update failed. Please contact support.');
        }
    }

    /**
     * Complete payment transaction (DB operations)
     */
    private function completePaymentTransaction(Payment $payment, array $providerData)
    {
        DB::beginTransaction();

        try {
            // FIXED: Ensure meta is always an array before merging
            $currentMeta = $payment->meta;
            if (!is_array($currentMeta)) {
                $currentMeta = [];
            }

            // Update payment status
            $payment->update([
                'status' => 'completed',
                'meta' => array_merge($currentMeta, [
                    'provider_response' => $providerData,
                    'completed_at' => now()->toDateTimeString(),
                    'payment_confirmation' => $providerData['confirmation_code'] ?? null,
                ])
            ]);

            $order = $payment->order;

            // Update order status
            $order->update(['status' => 'paid']);

            // Create escrow for buyer protection
            Escrow::create([
                'order_id' => $order->id,
                'amount' => $order->total,
                'status' => 'held',
                'release_at' => now()->addDays(7),
                'meta' => [
                    'release_condition' => 'buyer_confirmation_or_auto',
                    'auto_release_days' => 7,
                    'payment_provider' => 'pesapal',
                    'payment_reference' => $payment->provider_payment_id,
                ]
            ]);

            // Notify vendor
            try {
                (new \App\Services\PushNotificationService())->sendToUser(
                    $order->vendorProfile->user_id,
                    'vendor_order',
                    "New order received! 🎉",
                    "Order #{$order->order_number} — UGX " . number_format($order->total, 0) . ". Payment confirmed!",
                    ['route' => '/vendor/orders/' . $order->id]
                );
            } catch (\Exception $e) { \Illuminate\Support\Facades\Log::warning('Vendor push failed', ['error' => $e->getMessage()]); }

            // Notify buyer
            try {
                (new \App\Services\PushNotificationService())->sendToUser(
                    $order->buyer_id,
                    'order_update',
                    "Payment Confirmed! ✅",
                    "Your payment for order #{$order->order_number} was successful. The vendor will process your order soon.",
                    ['route' => '/orders/' . $order->id]
                );
            } catch (\Exception $e) { \Illuminate\Support\Facades\Log::warning('Buyer push failed', ['error' => $e->getMessage()]); }

            DB::commit();

            Log::info("Payment completed successfully", [
                'payment_id' => $payment->id,
                'order_id' => $order->id,
                'amount' => $payment->amount,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment transaction failed', [
                'error' => $e->getMessage(),
                'payment_id' => $payment->id
            ]);
            throw $e;
        }
    }

    /**
     * Handle payment failure
     */
    private function handlePaymentFailed(string $txRef, string $reason)
    {
        $payment = Payment::where('provider_payment_id', $txRef)
            ->where('provider', 'pesapal')
            ->first();

        if ($payment) {
            // FIXED: Ensure meta is always an array before merging
            $currentMeta = $payment->meta;
            if (!is_array($currentMeta)) {
                $currentMeta = [];
            }

            $payment->update([
                'status' => 'failed',
                'meta' => array_merge($currentMeta, [
                    'failure_reason' => $reason,
                    'failed_at' => now()->toDateTimeString(),
                ])
            ]);

            return redirect()->route('buyer.orders.show', $payment->order)
                ->with('error', "Payment failed: {$reason}. Please try again.");
        }

        return redirect()->route('buyer.orders.index')
            ->with('error', "Payment failed: {$reason}");
    }

    /**
     * Check PesaPal payment status (AJAX)
     */
    public function checkPaymentStatus(Request $request, Order $order)
    {
        if ($order->buyer_id !== auth()->id()) {
            abort(403);
        }

        $payment = $order->payments()
            ->where('provider', 'pesapal')
            ->where('status', 'pending')
            ->latest()
            ->first();

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'No pending PesaPal payment found'
            ]);
        }

        // Get order_tracking_id from meta
        $meta = $payment->meta;
        if (!is_array($meta)) {
            $meta = [];
        }
        
        $orderTrackingId = $meta['order_tracking_id'] ?? null;

        if (!$orderTrackingId) {
            return response()->json([
                'success' => false,
                'message' => 'Missing payment tracking ID'
            ]);
        }

        try {
            $status = $this->pesapalService->getTransactionStatus($orderTrackingId);

            if ($status && $this->pesapalService->isPaymentSuccessful($status)) {
                // Payment is successful, process it
                $this->completePaymentTransaction($payment, $status);
                
                return response()->json([
                    'success' => true,
                    'status' => 'completed',
                    'message' => 'Payment completed successfully!'
                ]);
            }

            return response()->json([
                'success' => true,
                'status' => $status['payment_status_description'] ?? 'pending',
                'status_code' => $status['status_code'] ?? 0,
                'message' => 'Payment is still processing'
            ]);

        } catch (\Exception $e) {
            Log::error('Check payment status error', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to check payment status'
            ]);
        }
    }

    /**
     * Retry failed payment
     */
    public function retryPayment(Order $order)
    {
        if ($order->buyer_id !== auth()->id()) {
            abort(403);
        }

        if (!in_array($order->status, ['pending', 'payment_pending', 'payment_failed'])) {
            return redirect()->route('buyer.orders.show', $order)
                ->with('error', 'This order cannot be retried.');
        }

        // Reset order status to payment_pending
        $order->update(['status' => 'payment_pending']);

        return redirect()->route('buyer.orders.payment', $order);
    }
}