<?php

use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
*/

// Quote bawaan laravel (biar tetep ada)
Artisan::command('inspire', function () {
    $this->comment(\Illuminate\Foundation\Inspiring::quote());
})->purpose('Display an inspiring quote');

// 🔥 SCHEDULER: Jalankan command update status setiap menit
Schedule::command('app:update-status-terlambat')->everyMinute();