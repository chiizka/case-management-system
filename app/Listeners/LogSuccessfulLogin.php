<?php

namespace App\Listeners;

use App\Models\Log;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Request;

class LogSuccessfulLogin
{
    public function handle(Login $event): void
    {
        // Skip logging if flag is set (2FA re-login)
        if (session('skip_login_log')) {
            return;
        }

        Log::create([
            'user_id' => $event->user->id,
            'activity' => 'Logged in',
            'ip_address' => Request::ip(),
            'user_agent' => Request::header('User-Agent'),
        ]);
    }
}