<?php

namespace App\Http\Controllers;

use App\Models\DocumentTracking;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Return pending documents for the logged-in user's role.
     * Used by the navbar bell icon via AJAX polling.
     */
    public function getPending()
    {
        $user = Auth::user();

        $pending = DocumentTracking::with(['case', 'transferredBy'])
            ->active()
            ->where('current_role', $user->role)
            ->where('status', 'Pending Receipt')
            ->orderBy('transferred_at', 'desc')
            ->get()
            ->map(function ($doc) {
                return [
                    'id'              => $doc->id,
                    'case_no'         => $doc->case->case_no ?? 'N/A',
                    'establishment'   => $doc->case->establishment_name ?? 'N/A',
                    'transferred_by'  => $doc->transferredBy
                        ? $doc->transferredBy->fname . ' ' . $doc->transferredBy->lname
                        : 'System',
                    'transferred_at'  => $doc->transferred_at
                        ? $doc->transferred_at->diffForHumans()
                        : 'N/A',
                ];
            });

        return response()->json([
            'success' => true,
            'count'   => $pending->count(),
            'items'   => $pending,
        ]);
    }

    /**
     * Called when the user opens the dropdown — resets the "new" indicator.
     * Stored in session so it resets the badge without DB changes.
     */
    public function markSeen()
    {
        session(['notifications_last_seen' => now()->toISOString()]);

        return response()->json(['success' => true]);
    }
}