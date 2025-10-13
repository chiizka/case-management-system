<?php

namespace App\Listeners;

use App\Helpers\ActivityLogger;
use Illuminate\Auth\Events\Login;

class LogSuccessfulLogin
{
    public function handle(Login $event): void
    {
        // Skip logging if flag is set (2FA re-login)
        if (session('skip_login_log')) {
            return;
        }

        ActivityLogger::log('Logged in');
    }
}