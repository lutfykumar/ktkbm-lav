<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// TKBM Scheduled Tasks
Schedule::command('cron:run-scheduled')->everyMinute()->withoutOverlapping();
Schedule::command('cron:email-process')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('cron:log-cleanup')->dailyAt('00:00')->withoutOverlapping();
