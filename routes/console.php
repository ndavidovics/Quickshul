<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

// Daily incremental QB sync at 2:00 AM CST (forced=false pulls only recent changes)
// Runs inline (no queue worker required) — mirrors what QbController::syncPull() does manually.
Schedule::call(function () {
    $job = new \App\Jobs\DailyQuickBooksSync(forced: false);
    app()->call([$job, 'handle']);
})
    ->dailyAt('02:00')
    ->timezone('America/Chicago')
    ->name('daily-qb-sync')
    ->withoutOverlapping();
