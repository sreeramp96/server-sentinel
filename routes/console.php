<?php

use App\Models\UptimeCheck;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('sites:check')->everyMinute()->withoutOverlapping()->runInBackground();
Schedule::call(function () {
        UptimeCheck::where('checked_at', '<', now()->subDays(30))->delete();
})->daily();
