<?php

namespace App\Helpers;

use App\Models\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    /**
     * Log any user activity into the system logs.
     *
     * @param  string  $activity
     * @param  array|null  $context
     * @return void
     */
    public static function log($activity, $context = null)
    {
        try {
            Log::create([
                'user_id' => Auth::id(),
                'activity' => $activity . ($context ? ' | ' . json_encode($context) : ''),
                'ip_address' => Request::ip(),
                'user_agent' => Request::header('User-Agent'),
            ]);
        } catch (\Exception $e) {
            // Optional: store fail-safe log in laravel.log
            \Illuminate\Support\Facades\Log::error('Failed to create audit trail: ' . $e->getMessage());
        }
    }
}
