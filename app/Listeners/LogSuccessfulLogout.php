<?php

namespace App\Listeners;

use App\Helpers\ActivityLogger;
use Illuminate\Auth\Events\Logout;

class LogSuccessfulLogout
{
    public function handle(Logout $event): void
    {
        // Skip logging if this is a 2FA intermediate logout
        if (session('2fa_in_progress')) {
            return;
        }

        $user = $event->user;

        if ($user) {
            ActivityLogger::log('Logged out');
        }
    }
}