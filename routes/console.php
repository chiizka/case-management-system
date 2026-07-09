<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Clean up expired sessions daily
Schedule::command('session:gc')->daily();

// Send Beyond deadline case notifications every weekday at 7AM
Schedule::command('notify:beyond-cases')->dailyAt('07:00')->weekdays()
    ->onSuccess(function () {
        \Log::info('Beyond case notification sent successfully at ' . now());
    })
    ->onFailure(function () {
        \Log::error('Beyond case notification FAILED at ' . now());
    });

// Notify sheriffs of missing monthly reports — first weekday on/after the 1st
Schedule::command('notify:missing-sheriff-reports')->dailyAt('07:05')->weekdays()
    ->onSuccess(function () {
        \Log::info('Missing sheriff report notification run completed at ' . now());
    })
    ->onFailure(function () {
        \Log::error('Missing sheriff report notification run FAILED at ' . now());
    });