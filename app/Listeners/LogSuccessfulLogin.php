<?php

namespace App\Listeners;

use App\Models\Log;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Request;

class LogSuccessfulLogin
{
    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        $user = $event->user;

        // 1. Check for a very recent log entry for this user (within 2 seconds).
        // This is a necessary defense against near-simultaneous event dispatches.
        $recentLog = Log::where('user_id', $user->id)
                           ->where('created_at', '>', now()->subSeconds(2))
                           ->first();

        // If a recent log entry exists, skip logging to prevent duplicates.
        if ($recentLog) {
            return;
        }

        // 2. Log the successful login
        Log::create([
            'user_id' => $user->id,
            'activity' => 'Logged in',
            'ip_address' => Request::ip(),
            'user_agent' => Request::header('User-Agent'),
        ]);
    }
}