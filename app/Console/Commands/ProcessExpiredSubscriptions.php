<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\VendorSubscription;
use App\Models\SubscriptionPayment;
use App\Services\PesapalService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ProcessExpiredSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:process-expired
                            {--dry-run : Run without making changes}
                            {--send-reminders : Send expiry reminder emails}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process expired subscriptions, send reminders, and handle auto-renewals';

    /**
     * Execute the console command.
     */
    public function handle(PesapalService $pesapalService)
    {
        $dryRun = $this->option('dry-run');
        $sendReminders = $this->option('send-reminders');

        $this->info('Processing subscriptions...');

        // 1. Mark expired subscriptions
        $expiredCount = $this->markExpiredSubscriptions($dryRun);
        $this->info("Marked {$expiredCount} subscriptions as expired");

        // 2. Send expiry reminders
        if ($sendReminders) {
            $reminderCount = $this->sendExpiryReminders($dryRun);
            $this->info("Sent {$reminderCount} expiry reminders");
        }

        // 3. Process auto-renewals
        $renewalCount = $this->processAutoRenewals($pesapalService, $dryRun);
        $this->info("Processed {$renewalCount} auto-renewals");

        $this->info('Subscription processing completed!');

        return Command::SUCCESS;
    }

    /**
     * Mark subscriptions as expired
     */
    protected function markExpiredSubscriptions(bool $dryRun): int
    {
        $expired = VendorSubscription::where('status', 'active')
            ->where('expires_at', '<=', now())
            ->get();

        if ($dryRun) {
            return $expired->count();
        }

        foreach ($expired as $subscription) {
            $subscription->markAsExpired();

            Log::info('Subscription expired', [
                'subscription_id' => $subscription->id,
                'vendor_id' => $subscription->vendor_profile_id,
                'plan' => $subscription->plan?->name,
            ]);
        }

        return $expired->count();
    }

    /**
     * Send expiry reminder notifications
     */
    protected function sendExpiryReminders(bool $dryRun): int
    {
        $count = 0;

        // 7 days before expiry
        $expiring7Days = VendorSubscription::active()
            ->whereBetween('expires_at', [
                now()->addDays(6)->startOfDay(),
                now()->addDays(7)->endOfDay()
            ])
            ->with(['vendorProfile.user', 'plan'])
            ->get();

        foreach ($expiring7Days as $subscription) {
            if (!$dryRun) {
                $this->sendReminderNotification($subscription, 7);
            }
            $count++;
        }

        // 3 days before expiry
        $expiring3Days = VendorSubscription::active()
            ->whereBetween('expires_at', [
                now()->addDays(2)->startOfDay(),
                now()->addDays(3)->endOfDay()
            ])
            ->with(['vendorProfile.user', 'plan'])
            ->get();

        foreach ($expiring3Days as $subscription) {
            if (!$dryRun) {
                $this->sendReminderNotification($subscription, 3);
            }
            $count++;
        }

        // 1 day before expiry
        $expiring1Day = VendorSubscription::active()
            ->whereBetween('expires_at', [
                now()->startOfDay(),
                now()->addDay()->endOfDay()
            ])
            ->with(['vendorProfile.user', 'plan'])
            ->get();

        foreach ($expiring1Day as $subscription) {
            if (!$dryRun) {
                $this->sendReminderNotification($subscription, 1);
            }
            $count++;
        }

        return $count;
    }

    /**
     * Send reminder notification
     */
    protected function sendReminderNotification(VendorSubscription $subscription, int $daysLeft): void
    {
        $user = $subscription->vendorProfile?->user;

        if (!$user || !$user->email) {
            return;
        }

        // Create notification queue entry
        DB::table('notification_queues')->insert([
            'recipient_id' => $user->id,
            'channel' => 'email',
            'type' => 'subscription_expiry_reminder',
            'payload' => json_encode([
                'days_left' => $daysLeft,
                'plan_name' => $subscription->plan?->name,
                'expires_at' => $subscription->expires_at->toISOString(),
                'auto_renew' => $subscription->auto_renew,
            ]),
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Log::info('Subscription expiry reminder queued', [
            'subscription_id' => $subscription->id,
            'user_id' => $user->id,
            'days_left' => $daysLeft,
        ]);
    }

    /**
     * Process auto-renewals for expiring subscriptions
     */
    protected function processAutoRenewals(PesapalService $pesapalService, bool $dryRun): int
    {
        $count = 0;

        // Get subscriptions expiring today that have auto-renew enabled
        $autoRenewals = VendorSubscription::where('status', 'active')
            ->where('auto_renew', true)
            ->whereBetween('expires_at', [
                now()->startOfDay(),
                now()->endOfDay()
            ])
            ->with(['vendorProfile.user', 'plan'])
            ->get();

        foreach ($autoRenewals as $subscription) {
            if ($dryRun) {
                $count++;
                continue;
            }

            try {
                $this->initiateAutoRenewal($subscription, $pesapalService);
                $count++;
            } catch (\Exception $e) {
                Log::error('Auto-renewal failed', [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $count;
    }

    /**
     * Initiate auto-renewal payment
     */
    protected function initiateAutoRenewal(VendorSubscription $subscription, PesapalService $pesapalService): void
    {
        $plan = $subscription->plan;
        $user = $subscription->vendorProfile?->user;

        if (!$plan || !$user) {
            throw new \Exception('Missing plan or user');
        }

        // Free plans don't need renewal
        if ($plan->price == 0) {
            $subscription->renew();
            return;
        }

        // Create payment record for auto-renewal
        $merchantReference = SubscriptionPayment::generateMerchantReference();

        $payment = SubscriptionPayment::create([
            'vendor_subscription_id' => $subscription->id,
            'vendor_profile_id' => $subscription->vendor_profile_id,
            'pesapal_merchant_reference' => $merchantReference,
            'amount' => $plan->price,
            'currency' => 'UGX',
            'status' => 'pending',
            'payment_response' => ['type' => 'auto_renewal'],
        ]);

        // Queue notification to user about pending auto-renewal
        DB::table('notification_queues')->insert([
            'recipient_id' => $user->id,
            'channel' => 'email',
            'type' => 'subscription_auto_renewal_pending',
            'payload' => json_encode([
                'plan_name' => $plan->name,
                'amount' => $plan->price,
                'merchant_reference' => $merchantReference,
            ]),
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Log::info('Auto-renewal initiated', [
            'subscription_id' => $subscription->id,
            'payment_id' => $payment->id,
            'amount' => $plan->price,
        ]);
    }
}
