<?php

namespace App\Console\Commands;

use App\Services\NotificationAlgorithmService;
use App\Services\PushNotificationService;
use Illuminate\Console\Command;

class SendCartAbandonmentNotifications extends Command
{
    protected $signature = 'notifications:cart-abandonment';
    protected $description = 'Send cart abandonment reminders to users with items left in cart';

    public function handle(): int
    {
        $this->info('Checking for abandoned carts...');

        $pushService = new PushNotificationService();
        $algorithmService = new NotificationAlgorithmService($pushService);

        $stats = $algorithmService->sendCartAbandonmentReminders();

        $this->info("Done! Sent: {$stats['sent']}, Skipped: {$stats['skipped']}");

        return Command::SUCCESS;
    }
}
