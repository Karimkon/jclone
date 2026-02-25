<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Models\Payment as PaymentModel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class FlutterwaveWebhookController extends Controller
{
    private $publicKey;
    private $secretKey;
    private $baseUrl = 'https://api.flutterwave.com/v3';

    public function __construct()
    {
        $this->publicKey = config('services.flutterwave.public_key');
        $this->secretKey = config('services.flutterwave.secret_key');
    }

    /**
     * Initialize payment
     */
    public function initialize(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'amount' => 'required|numeric',
            'currency' => 'required|string|size:3',
        ]);

        $order = Order::findOrFail($request->order_id);
        
        // Verify amount matches order total
        if (round($order->total, 2) !== round($request->amount, 2)) {
            return response()->json([
                'error' => 'Amount mismatch with order total'
            ], 422);
        }

        $payload = [
            'tx_ref' => 'JCLONE-' . time() . '-' . $order->id,
            'amount' => $order->total,
            'currency' => $request->currency,
            'redirect_url' => route('payment.flutterwave.callback'),
            'payment_options' => 'card,mobilemoneyuganda,ussd',
            'customer' => [
                'email' => $order->buyer->email,
                'phonenumber' => $order->buyer->phone,
                'name' => $order->buyer->name,
            ],
            'customizations' => [
                'title' => 'JClone Marketplace Payment',
                'description' => "Payment for Order #{$order->order_number}",
                'logo' => asset('images/logo.png'),
            ],
            'meta' => [
                'order_id' => $order->id,
                'buyer_id' => $order->buyer_id,
            ]
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/payments', $payload);

            $data = $response->json();

            if ($data['status'] === 'success') {
                // Create payment record
                PaymentModel::create([
                    'order_id' => $order->id,
                    'provider' => 'flutterwave',
                    'provider_payment_id' => $payload['tx_ref'],
                    'amount' => $order->total,
                    'status' => 'pending',
                    'meta' => [
                        'init_data' => $payload,
                        'response' => $data,
                        'payment_link' => $data['data']['link'],
                    ]
                ]);

                return response()->json([
                    'success' => true,
                    'payment_url' => $data['data']['link'],
                    'payment_reference' => $payload['tx_ref'],
                ]);
            } else {
                throw new \Exception($data['message'] ?? 'Payment initialization failed');
            }

        } catch (\Exception $e) {
            Log::error('Flutterwave payment init error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Payment initialization failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Payment callback
     */
    public function callback(Request $request)
    {
        $transactionId = $request->query('transaction_id');
        
        if (!$transactionId) {
            return redirect()->route('orders.index')
                ->with('error', 'Invalid payment response');
        }

        try {
            // Verify transaction with Flutterwave
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Content-Type' => 'application/json',
            ])->get($this->baseUrl . '/transactions/' . $transactionId . '/verify');

            $data = $response->json();

            if ($data['status'] === 'success' && $data['data']['status'] === 'successful') {
                $txRef = $data['data']['tx_ref'];
                
                // Find payment record
                $payment = PaymentModel::where('provider_payment_id', $txRef)->first();
                
                if (!$payment) {
                    throw new \Exception('Payment record not found');
                }

                DB::beginTransaction();

                // Update payment status
                $payment->update([
                    'status' => 'completed',
                    'meta' => array_merge($payment->meta ?? [], [
                        'verification_response' => $data,
                        'verified_at' => now()->toDateTimeString(),
                    ])
                ]);

                // Update order status
                $payment->order->update(['status' => 'paid']);

                // Create escrow if needed
                if (in_array($payment->order->vendorProfile->vendor_type, ['china_supplier', 'international'])) {
                    \App\Models\Escrow::create([
                        'order_id' => $payment->order->id,
                        'amount' => $payment->order->total,
                        'status' => 'held',
                        'release_at' => now()->addDays(7),
                        'meta' => [
                            'release_condition' => 'buyer_confirmation',
                            'auto_release_days' => 7,
                        ]
                    ]);
                }

                // Notify vendor
                try {
                    (new \App\Services\PushNotificationService())->sendToUser(
                        $payment->order->vendorProfile->user_id,
                        'vendor_order',
                        "New order received! 🎉",
                        "Order #{$payment->order->order_number} — Amount: {$payment->amount}. Payment confirmed!",
                        ['route' => '/vendor/orders/' . $payment->order->id]
                    );
                } catch (\Exception $e) { \Illuminate\Support\Facades\Log::warning('Vendor push failed', ['error' => $e->getMessage()]); }

                // Notify buyer
                try {
                    (new \App\Services\PushNotificationService())->sendToUser(
                        $payment->order->buyer_id,
                        'order_update',
                        "Payment Confirmed! ✅",
                        "Your payment for order #{$payment->order->order_number} was successful. The vendor will process your order soon.",
                        ['route' => '/orders/' . $payment->order->id]
                    );
                } catch (\Exception $e) { \Illuminate\Support\Facades\Log::warning('Buyer push failed', ['error' => $e->getMessage()]); }

                DB::commit();

                return redirect()->route('orders.show', $payment->order)
                    ->with('success', 'Payment completed successfully!');

            } else {
                throw new \Exception('Payment verification failed: ' . ($data['message'] ?? 'Unknown error'));
            }

        } catch (\Exception $e) {
            Log::error('Flutterwave callback error: ' . $e->getMessage());
            
            return redirect()->route('orders.index')
                ->with('error', 'Payment processing failed: ' . $e->getMessage());
        }
    }

    /**
     * Webhook handler
     */
    public function webhook(Request $request)
    {
        $signature = $request->header('verif-hash');
        $secretHash = config('services.flutterwave.secret_hash');
        
        // Verify webhook signature
        if ($signature !== $secretHash) {
            Log::warning('Invalid Flutterwave webhook signature');
            abort(401);
        }

        $payload = $request->all();
        Log::info('Flutterwave webhook received:', $payload);

        // Process webhook based on event type
        switch ($payload['event']) {
            case 'charge.completed':
                $this->handleChargeCompleted($payload);
                break;
                
            case 'transfer.completed':
                $this->handleTransferCompleted($payload);
                break;
                
            case 'refund.completed':
                $this->handleRefundCompleted($payload);
                break;
        }

        return response()->json(['status' => 'success'], 200);
    }

    /**
     * Handle charge completed webhook
     */
    private function handleChargeCompleted($payload)
    {
        $transaction = $payload['data'];
        
        // Find payment by transaction reference
        $payment = PaymentModel::where('provider_payment_id', $transaction['tx_ref'])->first();
        
        if ($payment && $payment->status !== 'completed') {
            $payment->update([
                'status' => 'completed',
                'meta' => array_merge($payment->meta ?? [], [
                    'webhook_data' => $payload,
                    'webhook_received_at' => now()->toDateTimeString(),
                ])
            ]);

            // Update order status
            $payment->order->update(['status' => 'paid']);
        }
    }

    /**
     * Handle transfer completed webhook
     */
    private function handleTransferCompleted($payload)
    {
        $transfer = $payload['data'];
        
        // Handle vendor payout completion
        Log::info('Transfer completed:', $transfer);
    }

    /**
     * Handle refund completed webhook
     */
    private function handleRefundCompleted($payload)
    {
        $refund = $payload['data'];
        
        // Update order and payment status for refund
        if ($refund['meta'] && isset($refund['meta']['order_id'])) {
            $order = Order::find($refund['meta']['order_id']);
            if ($order) {
                $order->update(['status' => 'refunded']);
                
                // Create refund payment record
                PaymentModel::create([
                    'order_id' => $order->id,
                    'provider' => 'flutterwave',
                    'provider_payment_id' => $refund['id'],
                    'amount' => -$refund['amount'], // Negative for refund
                    'status' => 'completed',
                    'meta' => [
                        'type' => 'refund',
                        'refund_data' => $refund,
                        'original_transaction_id' => $refund['flw_ref'],
                    ]
                ]);
            }
        }
    }
}