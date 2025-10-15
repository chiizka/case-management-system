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
     * USAGE EXAMPLES:
     * 
     * Basic (old format - still works):
     * ActivityLogger::log('Created case', ['case_id' => 1, 'name' => 'ABC Corp']);
     * 
     * New format (better):
     * ActivityLogger::logAction('CREATE', 'Case', 'INS-2024-001', 'Created new case', ['establishment' => 'ABC Corp']);
     *
     * @param  string  $activity
     * @param  array|null  $context
     * @return void
     */
    public static function log($activity, $context = null)
    {
        try {
            // Extract resource info from context if available
            $resourceType = self::extractResourceType($activity, $context);
            $resourceId = self::extractResourceId($context);
            $description = self::buildDescription($activity, $context);

            Log::create([
                'user_id' => Auth::id(),
                'activity' => $activity . ($context ? ' | ' . json_encode($context) : ''), // Keep for backwards compatibility
                'action' => self::extractAction($activity),
                'resource_type' => $resourceType,
                'resource_id' => $resourceId,
                'description' => $description,
                'ip_address' => Request::ip(),
                'user_agent' => Request::header('User-Agent'),
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to create audit trail: ' . $e->getMessage());
        }
    }

    /**
     * New method for structured logging (preferred method)
     *
     * @param  string  $action (CREATE, UPDATE, DELETE, VIEW, etc.)
     * @param  string|null  $resourceType (Case, Inspection, Docketing, etc.)
     * @param  string|null  $resourceId (inspection_id, case_no, etc.)
     * @param  string|null  $description (human-readable description)
     * @param  array  $metadata (additional context)
     * @return void
     */
    public static function logAction(
        string $action,
        ?string $resourceType = null,
        ?string $resourceId = null,
        ?string $description = null,
        array $metadata = []
    ) {
        try {
            $finalDescription = $description ?? self::generateDescription($action, $resourceType, $resourceId, $metadata);
            
            // Build activity string for backwards compatibility
            $activity = ucfirst(strtolower($action)) . ($resourceType ? " {$resourceType}" : '');
            if ($resourceId) {
                $activity .= " #{$resourceId}";
            }

            Log::create([
                'user_id' => Auth::id(),
                'activity' => $activity . (!empty($metadata) ? ' | ' . json_encode($metadata) : ''),
                'action' => strtolower($action),
                'resource_type' => $resourceType,
                'resource_id' => $resourceId,
                'description' => $finalDescription,
                'ip_address' => Request::ip(),
                'user_agent' => Request::header('User-Agent'),
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to create audit trail: ' . $e->getMessage());
        }
    }

    /**
     * Extract action type from activity string.
     */
    private static function extractAction($activity)
    {
        $activity = strtolower($activity);
        
        if (str_contains($activity, 'created') || str_contains($activity, 'create')) {
            return 'create';
        }
        
        if (str_contains($activity, 'moved') || str_contains($activity, 'move to')) {
            return 'progress';
        }
        
        if (str_contains($activity, 'updated') || str_contains($activity, 'update') || 
            str_contains($activity, 'inline updated')) {
            return 'update';
        }
        
        if (str_contains($activity, 'deleted') || str_contains($activity, 'delete')) {
            return 'delete';
        }
        
        if (str_contains($activity, 'viewed') || str_contains($activity, 'view') || 
            str_contains($activity, 'opened')) {
            return 'view';
        }
        
        if (str_contains($activity, 'login') || str_contains($activity, 'logged in') || 
            str_contains($activity, 'signed in')) {
            return 'login';
        }
        
        if (str_contains($activity, 'logout') || str_contains($activity, 'logged out') || 
            str_contains($activity, 'signed out')) {
            return 'logout';
        }
        
        if (str_contains($activity, 'export') || str_contains($activity, 'download')) {
            return 'export';
        }
        
        if (str_contains($activity, 'import') || str_contains($activity, 'upload')) {
            return 'import';
        }
        
        return 'other';
    }

    /**
     * Extract resource type from activity or context
     */
    private static function extractResourceType($activity, $context)
    {
        $activity = strtolower($activity);
        
        // Check context first
        if (is_array($context)) {
            if (isset($context['resource_type'])) {
                return $context['resource_type'];
            }
            
            // Infer from context keys
            if (isset($context['case_id']) || isset($context['establishment'])) {
                return 'Case';
            }
            if (isset($context['inspection_id'])) {
                return 'Inspection';
            }
            if (isset($context['tab'])) {
                return 'Tab View';
            }
        }
        
        // Infer from activity string
        if (str_contains($activity, 'case')) return 'Case';
        if (str_contains($activity, 'inspection')) return 'Inspection';
        if (str_contains($activity, 'docketing')) return 'Docketing';
        if (str_contains($activity, 'hearing')) return 'Hearing Process';
        if (str_contains($activity, 'review') || str_contains($activity, 'drafting')) return 'Review & Drafting';
        if (str_contains($activity, 'order') || str_contains($activity, 'disposition')) return 'Orders & Disposition';
        if (str_contains($activity, 'compliance') || str_contains($activity, 'award')) return 'Compliance & Awards';
        if (str_contains($activity, 'appeal') || str_contains($activity, 'resolution')) return 'Appeals & Resolution';
        if (str_contains($activity, 'tab')) return 'Tab View';
        if (str_contains($activity, 'login') || str_contains($activity, 'logout')) return 'Authentication';
        
        return null;
    }

    /**
     * Extract resource ID from context
     */
    private static function extractResourceId($context)
    {
        if (!is_array($context)) {
            return null;
        }

        return $context['resource_id']
            ?? $context['inspection_id']
            ?? $context['case_no']
            ?? $context['case_id']
            ?? $context['id']
            ?? null;
    }

    /**
     * Build description from activity and context
     */
    private static function buildDescription($activity, $context)
    {
        $user = Auth::user();
        $userName = $user ? ($user->fname . ' ' . $user->lname) : 'System';
        
        $description = "{$userName} - {$activity}";
        
        if (is_array($context) && !empty($context)) {
            $details = [];
            
            foreach ($context as $key => $value) {
                // Skip IDs and resource_type (already in resource fields)
                if (in_array($key, ['id', 'case_id', 'inspection_id', 'resource_type', 'resource_id'])) {
                    continue;
                }
                
                if ($value) {
                    $label = ucfirst(str_replace('_', ' ', $key));
                    $details[] = "{$label}: {$value}";
                }
            }
            
            if (!empty($details)) {
                $description .= ' (' . implode(', ', $details) . ')';
            }
        }
        
        return $description;
    }

    /**
     * Generate description for new logAction method
     */
    private static function generateDescription($action, $resourceType, $resourceId, $metadata)
    {
        $user = Auth::user();
        $userName = $user ? ($user->fname . ' ' . $user->lname) : 'System';
        
        $actionText = strtolower($action);
        $resource = $resourceType ?? '';
        $id = $resourceId ? " #{$resourceId}" : '';
        
        $description = "{$userName} {$actionText} {$resource}{$id}";
        
        if (!empty($metadata)) {
            $details = [];
            foreach ($metadata as $key => $value) {
                if ($value) {
                    $label = ucfirst(str_replace('_', ' ', $key));
                    $details[] = "{$label}: {$value}";
                }
            }
            
            if (!empty($details)) {
                $description .= ' (' . implode(', ', $details) . ')';
            }
        }
        
        return trim($description);
    }
}