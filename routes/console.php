<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Clean up expired sessions daily
Schedule::command('session:gc')->daily();

// Send Beyond deadline case notifications every day at 7AM
Schedule::command('notify:beyond-cases')->dailyAt('07:00');