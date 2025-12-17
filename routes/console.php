<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule auto-checkout to run daily at midnight
Schedule::command('bookings:auto-checkout')
    ->daily()
    ->at('00:01')
    ->appendOutputTo(storage_path('logs/auto-checkout.log'));
