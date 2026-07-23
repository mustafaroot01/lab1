<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule the coverage expiry checker every hour
use Illuminate\Support\Facades\Schedule;
Schedule::command('coverage:check-expiry')->hourly();

