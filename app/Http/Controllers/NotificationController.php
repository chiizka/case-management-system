<?php

namespace App\Http\Controllers;

use App\Models\DocumentTracking;
use App\Models\CaseFile;
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
     * Return cases with at least one "Beyond" status, scoped to the user's role.
     * - Admin and Case Management: see ALL beyond cases
     * - Province users: only cases currently at their location
     * - MALSU: all cases (handles appeals across all provinces)
     */
    public function getBeyond()
    {
        $user = Auth::user();

        $query = CaseFile::whereNotIn('overall_status', ['Completed', 'Disposed', 'Appealed'])
            ->where(function ($q) {
                $q->where('status_docket',  'Beyond')
                  ->orWhere('status_1st_mc', 'Beyond')
                  ->orWhere('status_2nd_mc', 'Beyond')
                  ->orWhere('status_po_pct', 'Beyond')
                  ->orWhere('status_pct',    'Beyond');
            });

        if (!$user->isAdmin() && !$user->isCaseManagement()) {
            $query->whereHas('documentTracking', function ($q) use ($user) {
                $q->where('current_role', $user->role)
                ->where('status', 'Received');
            });
        }
        // Admin, case_management, malsu — no extra filter (see all)

        $cases = $query->orderBy('updated_at', 'desc')
                       ->get([
                           'id', 'case_no', 'inspection_id',
                           'establishment_name', 'po_office',
                           'status_docket', 'status_1st_mc',
                           'status_2nd_mc', 'status_po_pct',
                           'status_pct', 'updated_at',
                       ]);

        $fieldLabels = [
            'status_docket'  => 'Docket',
            'status_1st_mc'  => '1st MC',
            'status_2nd_mc'  => '2nd MC',
            'status_po_pct'  => 'PO PCT',
            'status_pct'     => 'PCT (96 days)',
        ];

        $items = $cases->map(function ($case) use ($fieldLabels) {
            $beyondFields = [];
            foreach ($fieldLabels as $field => $label) {
                if ($case->$field === 'Beyond') {
                    $beyondFields[] = $label;
                }
            }

            return [
                'id'            => $case->id,
                'case_no'       => $case->case_no ?? $case->inspection_id ?? 'N/A',
                'establishment' => $case->establishment_name ?? 'Unknown',
                'po_office'     => $case->po_office ?? '-',
                'beyond_fields' => $beyondFields,
                'updated_at'    => $case->updated_at
                                    ? $case->updated_at->diffForHumans()
                                    : 'N/A',
            ];
        });

        return response()->json([
            'success' => true,
            'count'   => $items->count(),
            'items'   => $items->values(),
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