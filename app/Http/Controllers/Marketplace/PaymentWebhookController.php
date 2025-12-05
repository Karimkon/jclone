<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Order;
use App\Models\Escrow;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    // Flutterwave webhook handler
    public function flutterwave(Request $request)
    {
        // Validate signature (implementation depends on your secret)
        // Example: $signature = $request->header('verif-hash') ...
        $payload = $request->all();
        Log::info('Flutterwave webhook', $payload);

        // find order by our metadata (provider ref)
        $txRef = $payload['data']['tx_ref'] ?? null;
        $status = $payload['data']['status'] ?? null;
        $amount = $payload['data']['amount'] ?? 0;

        if (!$txRef) return response()->json(['message'=>'ignored'], 200);

        // tx_ref should map to order id / payment record; example format ORD-{id}-{rand}
        // We'll attempt to locate order
        $order = Order::where('order_number', $txRef)->first();
        if (!$order) {
            Log::warning("Order not found for tx_ref: {$txRef}");
            return response()->json(['message'=>'order not found'], 404);
        }

        // create or update payment
        $payment = Payment::updateOrCreate(
            ['provider_payment_id' => $payload['data']['id'] ?? null],
            [
                'order_id' => $order->id,
                'provider' => 'flutterwave',
                'provider_payment_id' => $payload['data']['id'] ?? null,
                'amount' => $amount,
                'status' => $status === 'successful' ? 'completed' : 'failed',
                'meta' => $payload
            ]
        );

        if ($status === 'successful') {
            $order->update(['status' => 'paid']);
            // place funds in escrow record
            Escrow::create([
                'order_id' => $order->id,
                'amount' => $amount,
                'status' => 'held',
                'release_at' => now()->addDays(config('marketplace.inspection_days', 3)),
                'meta' => ['provider' => 'flutterwave', 'payment_id' => $payment->id]
            ]);
        }

        return response()->json(['received' => true]);
    }

    // PesaPal webhook handler (same pattern)
    public function pesapal(Request $request)
    {
        Log::info('PesaPal webhook', $request->all());
        // Validate and process similarly to flutterwave
        return response()->json(['received' => true]);
    }
}
