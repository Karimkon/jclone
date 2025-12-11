<?php

namespace App\Services;

use App\Models\Escrow;
use App\Models\Order;
use App\Models\VendorBalance;
use App\Models\VendorTransaction;
use App\Models\NotificationQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EscrowService
{
    /**
     * Release escrow funds to vendor
     */
    public function releaseFunds(Escrow $escrow, string $reason = 'buyer_confirmed'): bool
    {
        if ($escrow->status !== 'held') {
            Log::warning("Cannot release escrow {$escrow->id}: status is {$escrow->status}");
            return false;
        }

        DB::beginTransaction();

        try {
            $order = $escrow->order;
            $vendorProfile = $order->vendorProfile;

            // Calculate amounts
            $totalAmount = $escrow->amount;
            $platformCommission = $order->platform_commission ?? ($totalAmount * 0.08); // 8% default
            $vendorAmount = $totalAmount - $platformCommission;

            // Get or create vendor balance
            $vendorBalance = VendorBalance::firstOrCreate(
                ['vendor_profile_id' => $vendorProfile->id],
                ['balance' => 0, 'pending_balance' => 0]
            );

            // Update vendor balance
            $vendorBalance->increment('balance', $vendorAmount);

            // Create vendor transaction
            VendorTransaction::create([
                'vendor_profile_id' => $vendorProfile->id,
                'type' => 'credit',
                'amount' => $vendorAmount,
                'description' => "Payment for Order #{$order->order_number}",
                'reference' => "ESCROW-{$escrow->id}",
                'meta' => [
                    'order_id' => $order->id,
                    'escrow_id' => $escrow->id,
                    'gross_amount' => $totalAmount,
                    'platform_commission' => $platformCommission,
                    'net_amount' => $vendorAmount,
                    'release_reason' => $reason,
                ]
            ]);

            // Update escrow status
            $escrow->update([
                'status' => 'released',
                'released_at' => now(),
                'meta' => array_merge($escrow->meta ?? [], [
                    'release_reason' => $reason,
                    'released_at' => now()->toDateTimeString(),
                    'vendor_amount' => $vendorAmount,
                    'platform_commission' => $platformCommission,
                ])
            ]);

            // Update order status
            $order->update(['status' => 'completed']);

            // Notify vendor
            NotificationQueue::create([
                'user_id' => $vendorProfile->user_id,
                'type' => 'funds_released',
                'title' => 'Payment Released!',
                'message' => "UGX " . number_format($vendorAmount, 2) . " has been released to your balance for Order #{$order->order_number}",
                'meta' => [
                    'order_id' => $order->id,
                    'amount' => $vendorAmount,
                ],
                'status' => 'pending',
            ]);

            // Notify buyer
            NotificationQueue::create([
                'user_id' => $order->buyer_id,
                'type' => 'order_completed',
                'title' => 'Order Completed',
                'message' => "Order #{$order->order_number} has been marked as completed.",
                'meta' => ['order_id' => $order->id],
                'status' => 'pending',
            ]);

            DB::commit();

            Log::info("Escrow {$escrow->id} released", [
                'order_id' => $order->id,
                'vendor_amount' => $vendorAmount,
                'commission' => $platformCommission,
                'reason' => $reason,
            ]);

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Escrow release failed: " . $e->getMessage(), [
                'escrow_id' => $escrow->id,
            ]);
            return false;
        }
    }

    /**
     * Refund escrow to buyer
     */
    public function refundToBuyer(Escrow $escrow, string $reason, float $amount = null): bool
    {
        if ($escrow->status !== 'held') {
            Log::warning("Cannot refund escrow {$escrow->id}: status is {$escrow->status}");
            return false;
        }

        DB::beginTransaction();

        try {
            $order = $escrow->order;
            $refundAmount = $amount ?? $escrow->amount;

            // If partial refund, calculate remaining
            $remainingAmount = $escrow->amount - $refundAmount;

            // Update buyer wallet (if using wallet system)
            $buyerWallet = $order->buyer->buyerWallet;
            if ($buyerWallet) {
                $buyerWallet->increment('balance', $refundAmount);

                // Create wallet transaction
                \App\Models\WalletTransaction::create([
                    'user_id' => $order->buyer_id,
                    'type' => 'refund',
                    'amount' => $refundAmount,
                    'description' => "Refund for Order #{$order->order_number}",
                    'reference' => "REFUND-{$escrow->id}",
                    'meta' => [
                        'order_id' => $order->id,
                        'escrow_id' => $escrow->id,
                        'reason' => $reason,
                    ]
                ]);
            }

            // Update escrow
            $newStatus = $remainingAmount > 0 ? 'partial_refund' : 'refunded';
            $escrow->update([
                'status' => $newStatus,
                'amount' => $remainingAmount,
                'meta' => array_merge($escrow->meta ?? [], [
                    'refund_reason' => $reason,
                    'refunded_amount' => $refundAmount,
                    'refunded_at' => now()->toDateTimeString(),
                ])
            ]);

            // Update order status
            $order->update(['status' => 'refunded']);

            // Notify buyer
            NotificationQueue::create([
                'user_id' => $order->buyer_id,
                'type' => 'refund_processed',
                'title' => 'Refund Processed',
                'message' => "UGX " . number_format($refundAmount, 2) . " has been refunded to your wallet for Order #{$order->order_number}",
                'meta' => [
                    'order_id' => $order->id,
                    'amount' => $refundAmount,
                ],
                'status' => 'pending',
            ]);

            // Notify vendor
            NotificationQueue::create([
                'user_id' => $order->vendorProfile->user_id,
                'type' => 'order_refunded',
                'title' => 'Order Refunded',
                'message' => "Order #{$order->order_number} has been refunded. Reason: {$reason}",
                'meta' => ['order_id' => $order->id],
                'status' => 'pending',
            ]);

            DB::commit();

            Log::info("Escrow {$escrow->id} refunded", [
                'order_id' => $order->id,
                'refund_amount' => $refundAmount,
                'reason' => $reason,
            ]);

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Escrow refund failed: " . $e->getMessage(), [
                'escrow_id' => $escrow->id,
            ]);
            return false;
        }
    }

    /**
     * Process auto-release for expired escrows
     * Run this via scheduler: $schedule->call(...)->daily();
     */
    public function processAutoRelease(): int
    {
        $released = 0;

        $expiredEscrows = Escrow::where('status', 'held')
            ->where('release_at', '<=', now())
            ->get();

        foreach ($expiredEscrows as $escrow) {
            if ($this->releaseFunds($escrow, 'auto_release_expired')) {
                $released++;
            }
        }

        Log::info("Auto-released {$released} escrows");
        return $released;
    }

    /**
     * Extend escrow hold period
     */
    public function extendHold(Escrow $escrow, int $days, string $reason): bool
    {
        if ($escrow->status !== 'held') {
            return false;
        }

        $escrow->update([
            'release_at' => $escrow->release_at->addDays($days),
            'meta' => array_merge($escrow->meta ?? [], [
                'extensions' => array_merge($escrow->meta['extensions'] ?? [], [
                    [
                        'days' => $days,
                        'reason' => $reason,
                        'extended_at' => now()->toDateTimeString(),
                    ]
                ])
            ])
        ]);

        return true;
    }

    /**
     * Buyer confirms order receipt (triggers release)
     */
    public function buyerConfirmReceipt(Order $order): bool
    {
        if ($order->buyer_id !== auth()->id()) {
            return false;
        }

        $escrow = $order->escrow;
        if (!$escrow || $escrow->status !== 'held') {
            return false;
        }

        return $this->releaseFunds($escrow, 'buyer_confirmed');
    }

    /**
     * Get escrow status summary for order
     */
    public function getEscrowSummary(Order $order): array
    {
        $escrow = $order->escrow;

        if (!$escrow) {
            return [
                'has_escrow' => false,
                'message' => 'No escrow for this order',
            ];
        }

        return [
            'has_escrow' => true,
            'status' => $escrow->status,
            'amount' => $escrow->amount,
            'created_at' => $escrow->created_at,
            'release_at' => $escrow->release_at,
            'days_until_release' => $escrow->release_at ? now()->diffInDays($escrow->release_at, false) : null,
            'can_release' => $escrow->status === 'held',
            'can_refund' => $escrow->status === 'held',
        ];
    }
}
