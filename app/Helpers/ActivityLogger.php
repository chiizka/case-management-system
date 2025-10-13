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
                'action' => self::extractAction($activity),
                'ip_address' => Request::ip(),
                'user_agent' => Request::header('User-Agent'),
            ]);
        } catch (\Exception $e) {
            // Optional: store fail-safe log in laravel.log
            \Illuminate\Support\Facades\Log::error('Failed to create audit trail: ' . $e->getMessage());
        }
    }

    /**
     * Extract action type from activity string.
     *
     * @param  string  $activity
     * @return string
     */
    private static function extractAction($activity)
{
    $activity = strtolower($activity);
    
    // Create actions
    if (str_contains($activity, 'created') || str_contains($activity, 'create')) {
        return 'create';
    }
    
    // Progress/Move actions (ADD THIS BEFORE UPDATE)
    if (str_contains($activity, 'moved') || str_contains($activity, 'move to')) {
        return 'progress';
    }
    
    // Update actions
    if (str_contains($activity, 'updated') || str_contains($activity, 'update') || 
        str_contains($activity, 'inline updated')) {
        return 'update';
    }
    
    // Delete actions
    if (str_contains($activity, 'deleted') || str_contains($activity, 'delete')) {
        return 'delete';
    }
    
    // View actions
    if (str_contains($activity, 'viewed') || str_contains($activity, 'view') || 
        str_contains($activity, 'opened')) {
        return 'view';
    }
    
    // Authentication actions
    if (str_contains($activity, 'login') || str_contains($activity, 'logged in') || 
        str_contains($activity, 'signed in')) {
        return 'login';
    }
    
    if (str_contains($activity, 'logout') || str_contains($activity, 'logged out') || 
        str_contains($activity, 'signed out')) {
        return 'logout';
    }
    
    // Export actions
    if (str_contains($activity, 'export') || str_contains($activity, 'download')) {
        return 'export';
    }
    
    // Import actions
    if (str_contains($activity, 'import') || str_contains($activity, 'upload')) {
        return 'import';
    }
    
    // Default
    return 'other';
}
}