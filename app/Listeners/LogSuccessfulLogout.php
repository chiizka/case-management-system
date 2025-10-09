<?php

namespace App\Listeners;

use App\Models\Log;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Request;

class LogSuccessfulLogout
{
    /**
     * Handle the event.
     */
    public function handle(Logout $event): void
    {
        // CRITICAL FIX: The user object must be accessed directly from the $event->user property.
        // This is necessary because the Auth::logout() call destroys the session state before 
        // this listener fully executes.
        $user = $event->user;

        // Ensure the user object is available (it should be, but it's a good defensive check)
        if ($user) {
            Log::create([
                // Use the user's ID from the event object
                'user_id' => $user->id,
                'activity' => 'Logged out',
                'ip_address' => Request::ip(),
                'user_agent' => Request::header('User-Agent'),
            ]);
        }
    }
}