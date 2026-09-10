<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Advance the market every minute.
// Requires a cron entry: * * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1
Schedule::command('market:tick')->everyMinute();
