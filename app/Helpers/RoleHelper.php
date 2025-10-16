<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;

class RoleHelper
{
    /**
     * Define which roles can access which features
     * This makes it easy to manage permissions in one place
     */
    private static $rolePermissions = [
        'user_management' => ['admin'],
        'cases' => ['admin', 'province', 'malsu', 'case_management'],
        'audit_logs' => ['admin'],
        'charts' => ['admin'],
        'tables' => ['admin', 'case_management'],
        'inspections' => ['admin', 'case_management', 'malsu'],
        'docketing' => ['admin', 'case_management'],
        'hearing_process' => ['admin', 'case_management'],
        'review_drafting' => ['admin', 'case_management'],
        'orders_disposition' => ['admin', 'case_management'],
        'compliance_awards' => ['admin', 'case_management'],
        'appeals_resolution' => ['admin', 'case_management'],
    ];

    /**
     * Check if current user can access a feature
     *
     * @param string $feature
     * @return bool
     */
    public static function can($feature)
    {
        if (!Auth::check()) {
            return false;
        }

        $user = Auth::user();

        // Admin can access everything
        if ($user->role === 'admin') {
            return true;
        }

        // Check if feature is defined in permissions
        if (!isset(self::$rolePermissions[$feature])) {
            return false;
        }

        // Check if user's role is in the allowed roles for this feature
        return in_array($user->role, self::$rolePermissions[$feature]);
    }

    /**
     * Check if current user can access one of multiple features
     *
     * @param array $features
     * @return bool
     */
    public static function canAny($features)
    {
        foreach ($features as $feature) {
            if (self::can($feature)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get all allowed features for current user
     *
     * @return array
     */
    public static function getAllowedFeatures()
    {
        if (!Auth::check()) {
            return [];
        }

        $user = Auth::user();
        $allowed = [];

        foreach (self::$rolePermissions as $feature => $roles) {
            if ($user->role === 'admin' || in_array($user->role, $roles)) {
                $allowed[] = $feature;
            }
        }

        return $allowed;
    }

    /**
     * Get user's role display name
     *
     * @param string|null $role
     * @return string
     */
    public static function getRoleDisplayName($role = null)
    {
        if (Auth::check() && $role === null) {
            $role = Auth::user()->role;
        }

        $roleNames = [
            'admin' => 'Administrator',
            'user' => 'User',
            'province' => 'Province Officer',
            'malsu' => 'MALSU Officer',
            'case_management' => 'Case Manager',
        ];

        return $roleNames[$role] ?? ucfirst(str_replace('_', ' ', $role));
    }
}