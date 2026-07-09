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
     * Return cases with Beyond or Nearing status, scoped by role.
     *
     * Role rules:
     *  - MALSU        → no PCT notifications at all (returns empty)
     *  - Province     → only 1st MC and 2nd MC (no Docket / PO PCT / PCT 96 days)
     *  - Case Mgmt    → only Docket, PO PCT, PCT 96 days (no 1st MC / 2nd MC)
     *  - Admin        → all fields
     */
    public function getBeyond()
    {
        $user  = Auth::user();
        $today = Carbon::today();

        // ── MALSU: no PCT notifications ───────────────────────────────────
         if ($user->role === 'malsu') {
            return response()->json([
                'success'       => true,
                'count'         => 0,
                'beyond_cases'  => [],
                'nearing_cases' => [],
            ]);
        }

        if ($user->isSheriff()) {
            $service    = app(\App\Services\SheriffReportComplianceService::class);
            $lastMonth  = Carbon::now()->startOfMonth()->subMonth();
            $missing    = $service->getMissingCasesForRole($user->role, $lastMonth);

            $beyondCases = collect($missing)->map(function ($case) use ($lastMonth) {
                $months = $case['consecutive_missing_months'];
                $label  = $months > 1
                    ? "{$months} months overdue"
                    : "Missing {$lastMonth->format('F')} report";

                return [
                    'id'            => $case['case_id'],
                    'case_no'       => $case['case_no'],
                    'establishment' => $case['establishment'],
                    'po_office'     => $case['po_office'],
                    'beyond_fields' => [$label],
                ];
            })->values();

            return response()->json([
                'success'       => true,
                'count'         => $beyondCases->count(),
                'beyond_cases'  => $beyondCases,
                'nearing_cases' => [],
            ]);
        }


        // ── Determine which PCT fields this role cares about ──────────────
        $isProvince       = $user->isProvince();
        $isCaseManagement = $user->isCaseManagement();

        // Province   → 1st MC + 2nd MC only
        // Case Mgmt  → Docket + PO PCT + PCT 96 days only
        // Admin      → all five fields
        $showMC       = $isProvince || (!$isProvince && !$isCaseManagement); // admin gets all
        $showDocket   = $isCaseManagement || (!$isProvince && !$isCaseManagement);
        $showPoPct    = $isCaseManagement || (!$isProvince && !$isCaseManagement);
        $showPct96    = $isCaseManagement || (!$isProvince && !$isCaseManagement);

        // Simpler explicit flags per role group
        if ($isProvince) {
            $show1stMC  = true;
            $show2ndMC  = true;
            $showDocket = false;
            $showPoPct  = false;
            $showPct96  = false;
        } elseif ($isCaseManagement) {
            $show1stMC  = false;
            $show2ndMC  = false;
            $showDocket = true;
            $showPoPct  = true;
            $showPct96  = true;
        } else {
            // Admin (and any other role): all fields
            $show1stMC  = true;
            $show2ndMC  = true;
            $showDocket = true;
            $showPoPct  = true;
            $showPct96  = true;
        }

        // ── Base query ────────────────────────────────────────────────────
        $baseQuery = CaseFile::where('overall_status', 'Active')
            ->whereHas('documentTracking', function ($q) {
                $q->where('status', 'Received');
            });

        // Province users: only cases currently at their location
        if ($isProvince) {
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

        // ── BEYOND ────────────────────────────────────────────────────────
        $beyondQuery = (clone $baseQuery)->where(function ($q) use (
            $show1stMC, $show2ndMC, $showDocket, $showPoPct, $showPct96
        ) {
            $first = true;
            $add = function ($condition) use ($q, &$first) {
                if ($first) { $q->where($condition); $first = false; }
                else        { $q->orWhere($condition); }
            };

            if ($showDocket) $q->orWhere('status_docket',  'Beyond');
            if ($show1stMC)  $q->orWhere('status_1st_mc',  'Beyond');
            if ($show2ndMC)  $q->orWhere('status_2nd_mc',  'Beyond');
            if ($showPoPct)  $q->orWhere('status_po_pct',  'Beyond');
            if ($showPct96)  $q->orWhere('status_pct',     'Beyond');
        });

        // Label map — only include labels for fields this role sees
        $beyondLabels = [];
        if ($showDocket) $beyondLabels['status_docket'] = 'Docket';
        if ($show1stMC)  $beyondLabels['status_1st_mc'] = '1st MC';
        if ($show2ndMC)  $beyondLabels['status_2nd_mc'] = '2nd MC';
        if ($showPoPct)  $beyondLabels['status_po_pct'] = 'PO PCT';
        if ($showPct96)  $beyondLabels['status_pct']    = 'PCT (96 days)';

        $beyondCases = $beyondQuery->get($selectFields)->map(function ($case) use ($beyondLabels) {
            $fields = [];
            foreach ($beyondLabels as $field => $label) {
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

        // ── NEARING ───────────────────────────────────────────────────────
        $in5Days = $today->copy()->addDays(5);

        $nearingQuery = (clone $baseQuery)->where(function ($q) use (
            $today, $in5Days, $show1stMC, $show2ndMC, $showDocket, $showPoPct, $showPct96
        ) {
            if ($showDocket) {
                $q->orWhere(function ($q2) {
                    $q2->whereBetween('aging_docket', [-5, 0])
                       ->where(function ($q3) {
                           $q3->where('status_docket', '!=', 'Beyond')
                              ->orWhereNull('status_docket');
                       });
                });
            }

            if ($showPoPct) {
                $q->orWhere(function ($q2) {
                    $q2->whereBetween('aging_po_pct', [-5, 0])
                       ->where(function ($q3) {
                           $q3->where('status_po_pct', '!=', 'Beyond')
                              ->orWhereNull('status_po_pct');
                       });
                });
            }

            if ($showPct96) {
                $q->orWhere(function ($q2) use ($today, $in5Days) {
                    $q2->whereBetween('pct_96_days', [$today, $in5Days])
                       ->where(function ($q3) {
                           $q3->where('status_pct', '!=', 'Beyond')
                              ->orWhereNull('status_pct');
                       });
                });
            }

            if ($show1stMC) {
                $q->orWhere(function ($q2) {
                    $q2->whereBetween('first_mc_pct', [-5, 0])
                       ->where(function ($q3) {
                           $q3->where('status_1st_mc', '!=', 'Beyond')
                              ->orWhereNull('status_1st_mc');
                       });
                });
            }

            if ($show2ndMC) {
                $q->orWhere(function ($q2) {
                    $q2->whereBetween('second_last_mc_pct', [-5, 0])
                       ->where(function ($q3) {
                           $q3->where('status_2nd_mc', '!=', 'Beyond')
                              ->orWhereNull('status_2nd_mc');
                       });
                });
            }
        });

        $nearingCases = $nearingQuery->get($selectFields)->map(function ($case) use (
            $today, $show1stMC, $show2ndMC, $showDocket, $showPoPct, $showPct96
        ) {
            $fields = [];

            if ($showDocket
                && $case->aging_docket !== null
                && $case->aging_docket >= -5
                && $case->aging_docket <= 0
                && $case->status_docket !== 'Beyond'
            ) {
                $daysLeft = abs($case->aging_docket);
                $deadline = $case->pct_for_docketing
                    ? Carbon::parse($case->pct_for_docketing)->format('M d, Y')
                    : 'N/A';
                $fields[] = ['label' => 'Docket', 'due_date' => $deadline, 'days_left' => $daysLeft];
            }

            if ($showPoPct
                && $case->aging_po_pct !== null
                && $case->aging_po_pct >= -5
                && $case->aging_po_pct <= 0
                && $case->status_po_pct !== 'Beyond'
            ) {
                $daysLeft = abs($case->aging_po_pct);
                $deadline = $case->po_pct
                    ? Carbon::parse($case->po_pct)->format('M d, Y')
                    : 'N/A';
                $fields[] = ['label' => 'PO PCT', 'due_date' => $deadline, 'days_left' => $daysLeft];
            }

            if ($showPct96 && $case->pct_96_days && $case->status_pct !== 'Beyond') {
                $deadline = Carbon::parse($case->pct_96_days);
                $daysLeft = (int) $today->diffInDays($deadline, false);
                if ($daysLeft >= 0 && $daysLeft <= 5) {
                    $fields[] = ['label' => 'PCT 96 days', 'due_date' => $deadline->format('M d, Y'), 'days_left' => $daysLeft];
                }
            }

            if ($show1stMC
                && $case->first_mc_pct !== null
                && $case->first_mc_pct >= -5
                && $case->first_mc_pct <= 0
                && $case->status_1st_mc !== 'Beyond'
            ) {
                $daysLeft = abs($case->first_mc_pct);
                $deadline = $case->lapse_20_day_period
                    ? Carbon::parse($case->lapse_20_day_period)->addDays(15)->format('M d, Y')
                    : 'N/A';
                $fields[] = ['label' => '1st MC', 'due_date' => $deadline, 'days_left' => $daysLeft];
            }

            if ($show2ndMC
                && $case->second_last_mc_pct !== null
                && $case->second_last_mc_pct >= -5
                && $case->second_last_mc_pct <= 0
                && $case->status_2nd_mc !== 'Beyond'
            ) {
                $daysLeft = abs($case->second_last_mc_pct);
                $deadline = $case->date_1st_mc_actual
                    ? Carbon::parse($case->date_1st_mc_actual)->addDays(30)->format('M d, Y')
                    : 'N/A';
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
     */
    public function markSeen()
    {
        session(['notifications_last_seen' => now()->toISOString()]);

        return response()->json(['success' => true]);
    }
}