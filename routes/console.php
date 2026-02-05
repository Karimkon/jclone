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
