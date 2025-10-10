<?php

namespace App\Listeners;

use App\Models\Log;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Request;

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
            Log::create([
                'user_id' => $user->id,
                'activity' => 'Logged out',
                'ip_address' => Request::ip(),
                'user_agent' => Request::header('User-Agent'),
            ]);
        }
    }
}