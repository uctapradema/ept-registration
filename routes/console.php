<?php

use App\Console\Commands\CheckExpiredRegistrations;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule the expired registrations check to run every hour
Schedule::command(CheckExpiredRegistrations::class)->hourly();
