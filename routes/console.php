<?php

use App\Console\Commands\CheckExpiredRegistrations;
use App\Console\Commands\EnableExamScoring;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule the expired registrations check to run every hour
Schedule::command(CheckExpiredRegistrations::class)->hourly();

// Schedule enable exam scoring to run every minute
Schedule::command(EnableExamScoring::class)->everyMinute();
