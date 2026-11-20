<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Live rates: checks every 5 minutes; the command itself honours the
// interval / on-off switch configured in Admin → Update rates.
Schedule::command('rates:fetch')->everyFiveMinutes()->withoutOverlapping();
