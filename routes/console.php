<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

// Daily incremental QB sync at 2:00 AM CST (forced=false pulls only recent changes)
Schedule::job(new \App\Jobs\DailyQuickBooksSync(forced: false))
    ->dailyAt('02:00')
    ->timezone('America/Chicago')
    ->name('daily-qb-sync')
    ->withoutOverlapping();
