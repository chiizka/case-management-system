<?php

namespace App\Http\Controllers;

use App\Models\DocumentTracking;
use App\Models\CaseFile;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

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
    $user  = Auth::user();
    $today = Carbon::today();

    $baseQuery = CaseFile::where('overall_status', 'Active')
        ->whereHas('documentTracking', function ($q) {
            $q->where('status', 'Received');
        });

    // Scope to province users only
    if (!$user->isAdmin() && !$user->isCaseManagement()) {
        $baseQuery->whereHas('documentTracking', function ($q) use ($user) {
            $q->where('current_role', $user->role)
              ->where('status', 'Received');
        });
    }

    $selectFields = [
        'id', 'case_no', 'inspection_id', 'establishment_name', 'po_office',
        'status_docket',  'aging_docket',        'pct_for_docketing',
        'status_1st_mc',  'first_mc_pct',        'lapse_20_day_period',
        'status_2nd_mc',  'second_last_mc_pct',  'date_1st_mc_actual',
        'status_po_pct',  'aging_po_pct',        'po_pct',
        'status_pct',     'pct_96_days',
        'updated_at',
    ];

    // ── BEYOND ────────────────────────────────────────────────────────────
    $beyondCases = (clone $baseQuery)
        ->where(function ($q) {
            $q->where('status_docket',  'Beyond')
              ->orWhere('status_1st_mc', 'Beyond')
              ->orWhere('status_2nd_mc', 'Beyond')
              ->orWhere('status_po_pct', 'Beyond')
              ->orWhere('status_pct',    'Beyond');
        })
        ->get($selectFields)
        ->map(function ($case) {
            $fields = [];
            foreach ([
                'status_docket'  => 'Docket',
                'status_1st_mc'  => '1st MC',
                'status_2nd_mc'  => '2nd MC',
                'status_po_pct'  => 'PO PCT',
                'status_pct'     => 'PCT (96 days)',
            ] as $field => $label) {
                if ($case->$field === 'Beyond') $fields[] = $label;
            }
            return [
                'id'            => $case->id,
                'case_no'       => $case->case_no ?? $case->inspection_id ?? 'N/A',
                'establishment' => $case->establishment_name ?? 'Unknown',
                'po_office'     => $case->po_office ?? '-',
                'beyond_fields' => $fields,
            ];
        })->filter(fn($c) => !empty($c['beyond_fields']))->values();

    // ── NEARING (exact same logic as the email command) ───────────────────
    $in5Days = $today->copy()->addDays(5);

    $nearingRaw = (clone $baseQuery)
        ->where(function ($q) use ($today, $in5Days) {

            // Docket: aging_docket in [-5, 0] and not Beyond
            $q->orWhere(function ($q2) {
                $q2->whereBetween('aging_docket', [-5, 0])
                   ->where(function ($q3) {
                       $q3->where('status_docket', '!=', 'Beyond')
                          ->orWhereNull('status_docket');
                   });
            });

            // PO PCT: aging_po_pct in [-5, 0] and not Beyond
            $q->orWhere(function ($q2) {
                $q2->whereBetween('aging_po_pct', [-5, 0])
                   ->where(function ($q3) {
                       $q3->where('status_po_pct', '!=', 'Beyond')
                          ->orWhereNull('status_po_pct');
                   });
            });

            // PCT 96 days: date within next 5 days and not Beyond
            $q->orWhere(function ($q2) use ($today, $in5Days) {
                $q2->whereBetween('pct_96_days', [$today, $in5Days])
                   ->where(function ($q3) {
                       $q3->where('status_pct', '!=', 'Beyond')
                          ->orWhereNull('status_pct');
                   });
            });

            // 1st MC: first_mc_pct in [-5, 0] and not Beyond
            $q->orWhere(function ($q2) {
                $q2->whereBetween('first_mc_pct', [-5, 0])
                   ->where(function ($q3) {
                       $q3->where('status_1st_mc', '!=', 'Beyond')
                          ->orWhereNull('status_1st_mc');
                   });
            });

            // 2nd MC: second_last_mc_pct in [-5, 0] and not Beyond
            $q->orWhere(function ($q2) {
                $q2->whereBetween('second_last_mc_pct', [-5, 0])
                   ->where(function ($q3) {
                       $q3->where('status_2nd_mc', '!=', 'Beyond')
                          ->orWhereNull('status_2nd_mc');
                   });
            });
        })
        ->get($selectFields);

    $nearingCases = $nearingRaw->map(function ($case) use ($today) {
        $fields = [];

        // Docket
        if ($case->aging_docket !== null && $case->aging_docket >= -5 && $case->aging_docket <= 0 && $case->status_docket !== 'Beyond') {
            $daysLeft = abs($case->aging_docket);
            $deadline = $case->pct_for_docketing ? Carbon::parse($case->pct_for_docketing)->format('M d, Y') : 'N/A';
            $fields[] = ['label' => 'Docket', 'due_date' => $deadline, 'days_left' => $daysLeft];
        }

        // PO PCT
        if ($case->aging_po_pct !== null && $case->aging_po_pct >= -5 && $case->aging_po_pct <= 0 && $case->status_po_pct !== 'Beyond') {
            $daysLeft = abs($case->aging_po_pct);
            $deadline = $case->po_pct ? Carbon::parse($case->po_pct)->format('M d, Y') : 'N/A';
            $fields[] = ['label' => 'PO PCT', 'due_date' => $deadline, 'days_left' => $daysLeft];
        }

        // PCT 96 days
        if ($case->pct_96_days && $case->status_pct !== 'Beyond') {
            $deadline = Carbon::parse($case->pct_96_days);
            $daysLeft = (int) $today->diffInDays($deadline, false);
            if ($daysLeft >= 0 && $daysLeft <= 5) {
                $fields[] = ['label' => 'PCT 96 days', 'due_date' => $deadline->format('M d, Y'), 'days_left' => $daysLeft];
            }
        }

        // 1st MC
        if ($case->first_mc_pct !== null && $case->first_mc_pct >= -5 && $case->first_mc_pct <= 0 && $case->status_1st_mc !== 'Beyond') {
            $daysLeft = abs($case->first_mc_pct);
            $deadline = $case->lapse_20_day_period ? Carbon::parse($case->lapse_20_day_period)->addDays(15)->format('M d, Y') : 'N/A';
            $fields[] = ['label' => '1st MC', 'due_date' => $deadline, 'days_left' => $daysLeft];
        }

        // 2nd MC
        if ($case->second_last_mc_pct !== null && $case->second_last_mc_pct >= -5 && $case->second_last_mc_pct <= 0 && $case->status_2nd_mc !== 'Beyond') {
            $daysLeft = abs($case->second_last_mc_pct);
            $deadline = $case->date_1st_mc_actual ? Carbon::parse($case->date_1st_mc_actual)->addDays(30)->format('M d, Y') : 'N/A';
            $fields[] = ['label' => '2nd MC', 'due_date' => $deadline, 'days_left' => $daysLeft];
        }

        if (empty($fields)) return null;

        return [
            'id'             => $case->id,
            'case_no'        => $case->case_no ?? $case->inspection_id ?? 'N/A',
            'establishment'  => $case->establishment_name ?? 'Unknown',
            'po_office'      => $case->po_office ?? '-',
            'nearing_fields' => $fields,
        ];
    })->filter()->values();

    return response()->json([
        'success'       => true,
        'count'         => $beyondCases->count() + $nearingCases->count(),
        'beyond_cases'  => $beyondCases,
        'nearing_cases' => $nearingCases,
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