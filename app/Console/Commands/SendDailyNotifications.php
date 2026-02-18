<?php

namespace App\Console\Commands;

use App\Services\NotificationAlgorithmService;
use App\Services\PushNotificationService;
use Illuminate\Console\Command;

class SendDailyNotifications extends Command
{
    protected $signature = 'notifications:send-daily';
    protected $description = 'Send personalized daily push notifications to all users';

    public function handle(): int
    {
        $this->info('Starting daily notification generation...');

        $pushService = new PushNotificationService();
        $algorithmService = new NotificationAlgorithmService($pushService);

        $stats = $algorithmService->sendDailyNotifications();

        $this->info("Done! Total users: {$stats['total_users']}, Sent: {$stats['sent']}, Skipped: {$stats['skipped']}");

        return Command::SUCCESS;
    }
}
