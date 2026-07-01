<?php

use App\Jobs\SendDailyPatrolReminders;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new SendDailyPatrolReminders)
    ->dailyAt('23:00')
    ->timezone('UTC')
    ->name('send-daily-patrol-reminders');
