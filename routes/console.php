<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Process expired subscriptions daily at midnight
Schedule::command('subscriptions:process-expired --send-reminders')
    ->daily()
    ->at('00:00')
    ->withoutOverlapping()
    ->runInBackground();

// Process scheduled campaigns every minute
Schedule::command('campaigns:process-scheduled')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();

// Send personalized daily push notifications at 10 AM
Schedule::command('notifications:send-daily')
    ->dailyAt('10:00')
    ->withoutOverlapping()
    ->runInBackground();

// Send cart abandonment reminders every 4 hours
Schedule::command('notifications:cart-abandonment')
    ->everyFourHours()
    ->withoutOverlapping()
    ->runInBackground();
