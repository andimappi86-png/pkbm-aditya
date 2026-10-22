<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('attendance:generate')
    ->dailyAt('05:00')
    ->timezone('Asia/Makassar')
    ->withoutOverlapping()
    ->runInBackground();
