<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');





Schedule::command('app:handle-inactive-users')->daily();
Schedule::command('app:handle-announcement-dates')->everyMinute();
Schedule::command('app:handle-survey-status')->everyMinute();
Schedule::command('app:handle-expired-rewards')->hourly(); // Run the voucher expiration check hourly
Schedule::command('app:handle-low-trust-score-users')->everyFourHours(); // Archive users with low trust scores daily
