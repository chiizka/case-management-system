<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CaseFile;
use App\Models\Inspection;
use App\Models\DocumentTracking;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AnalyticsController;

class FrontController extends Controller
{
    public function index()
    {
        $user       = Auth::user();
        $isProvince = $user->isProvince();
        $userRole   = $user->role;
        $isMalsu            = false;
        $malsuActiveCases   = 0;
        $malsuDisposedCases = 0;

        if ($isProvince) {
            $provinceName              = $user->getProvinceName();
            $caseManagementActiveCases = 0;
            $byProvince                = collect();
            $provincePendingDocs       = [];

            // Base query: cases received by this province
            $receivedByProvince = CaseFile::where('po_office', $provinceName)
                ->whereHas('documentTracking', function ($q) use ($userRole) {
                    $q->where('current_role', $userRole)
                      ->where('status', 'Received');
                });

            $activeCases = (clone $receivedByProvince)
                ->where('overall_status', 'Active')
                ->count();

            $disposedCases = CaseFile::where('po_office', $provinceName)
                ->where('overall_status', 'Disposed')
                ->count();

            $actualDisposedCases  = 0;
            $misDisposedCases     = 0;
            $misDisposedCasesList = collect();

            // ── Total Cases Handled ───────────────────────────────────────────
            $currentlyReceivedIds = \App\Models\DocumentTracking::where('current_role', $userRole)
                ->where('status', 'Received')
                ->pluck('case_id')
                ->unique();

            $historicalTrackingIds = \App\Models\DocumentTrackingHistory::where('to_role', $userRole)
                ->whereNotNull('received_by_user_id')
                ->whereNotNull('received_at')
                ->pluck('document_tracking_id')
                ->unique();

            $historicallyReceivedIds = \App\Models\DocumentTracking::whereIn('id', $historicalTrackingIds)
                ->pluck('case_id')
                ->unique();

            $totalCases = collect()
                ->merge($currentlyReceivedIds)
                ->merge($historicallyReceivedIds)
                ->unique()
                ->count();

            // ── Province: pending docs for THIS user ──────────────────────────
            $myPendingDocs = \App\Models\DocumentTracking::with(['case', 'transferredBy'])
                ->active()
                ->where('current_role', $userRole)
                ->where('status', 'Pending Receipt')
                ->orderBy('transferred_at', 'desc')
                ->get();
            $myPendingCount = $myPendingDocs->count();

            // ── Province: activeByRole (just this province) ───────────────────
            $activeByRole = [
                $userRole => [
                    'received' => $activeCases,
                    'pending'  => CaseFile::where('overall_status', 'Active')
                        ->whereHas('documentTracking', fn($q) => $q
                            ->where('current_role', $userRole)
                            ->where('status', 'Pending Receipt')
                        )
                        ->count(),
                ],
            ];

        } elseif ($user->role === 'malsu') {
            $isMalsu = true;

            $activeCases = CaseFile::where('overall_status', 'Active')->count();

            $malsuActiveCases = CaseFile::where('overall_status', 'Active')
                ->whereHas('documentTracking', fn($q) => $q
                    ->where('current_role', 'malsu')
                    ->where('status', 'Received')
                )
                ->count();

            $malsuDisposedCases = CaseFile::whereIn('overall_status', ['Completed', 'Disposed', 'Dismissed'])
                ->whereHas('documentTracking', fn($q) => $q
                    ->where('current_role', 'malsu')
                )
                ->count();

            $myPendingDocs = \App\Models\DocumentTracking::with(['case', 'transferredBy'])
                ->where('current_role', 'malsu')
                ->where('status', 'Pending Receipt')
                ->orderBy('transferred_at', 'desc')
                ->get();
            $myPendingCount = $myPendingDocs->count();

            // ── Same breakdown as admin/case_management ───────────────────────
            $rolesForBreakdown = [
                'admin', 'malsu', 'case_management', 'records',
                'province_albay', 'province_camarines_sur', 'province_camarines_norte',
                'province_catanduanes', 'province_masbate', 'province_sorsogon',
            ];

            $activeByRole = [];
            foreach ($rolesForBreakdown as $role) {
                $activeByRole[$role] = [
                    'received' => CaseFile::where('overall_status', 'Active')
                        ->whereHas('documentTracking', fn($q) => $q
                            ->where('current_role', $role)
                            ->where('status', 'Received')
                        )
                        ->count(),
                    'pending' => CaseFile::where('overall_status', 'Active')
                        ->whereHas('documentTracking', fn($q) => $q
                            ->where('current_role', $role)
                            ->where('status', 'Pending Receipt')
                        )
                        ->count(),
                ];
            }

            $provinceRoleKeys = [
                'province_albay', 'province_camarines_sur', 'province_camarines_norte',
                'province_catanduanes', 'province_masbate', 'province_sorsogon',
            ];

            $provincePendingDocs = [];
            foreach ($provinceRoleKeys as $role) {
                $provincePendingDocs[$role] = \App\Models\DocumentTracking::with(['case', 'transferredBy'])
                    ->active()
                    ->where('current_role', $role)
                    ->where('status', 'Pending Receipt')
                    ->orderBy('transferred_at', 'desc')
                    ->get(['id', 'case_id', 'transferred_by_user_id', 'transferred_at', 'transfer_notes']);
            }

            $totalCases                = CaseFile::count();
            $actualDisposedCases       = 0;
            $disposedCases             = 0;
            $misDisposedCases          = 0;
            $misDisposedCasesList      = collect();
            $caseManagementActiveCases = 0;
            $byProvince                = collect();

        } else {
            // ── Regional roles: system-wide counts ───────────────────────────
            $isMalsu             = false;
            $malsuActiveCases    = 0;
            $malsuDisposedCases  = 0;
            $activeCases         = CaseFile::where('overall_status', 'Active')->count();
            $actualDisposedCases = CaseFile::where('overall_status', 'Completed')->count();
            $disposedCases       = CaseFile::where('overall_status', 'Disposed')->count();
            $totalCases          = CaseFile::count();

            $misDisposedCases = CaseFile::where('overall_status', 'Active')
                ->whereNotNull('date_signed_mis')
                ->whereMonth('date_signed_mis', Carbon::now()->month)
                ->whereYear('date_signed_mis', Carbon::now()->year)
                ->count();

            $misDisposedCasesList = CaseFile::where('overall_status', 'Active')
                ->whereNotNull('date_signed_mis')
                ->whereMonth('date_signed_mis', Carbon::now()->month)
                ->whereYear('date_signed_mis', Carbon::now()->year)
                ->select('case_no', 'po_office', 'inspection_id', 'establishment_name', 'pct_96_days', 'date_signed_mis', 'date_scheduled_docketed')
                ->orderBy('po_office')
                ->get();

            $caseManagementActiveCases = CaseFile::where('overall_status', 'Active')
                ->whereHas('documentTracking', fn($q) => $q
                    ->where('current_role', 'case_management')
                    ->where('status', 'Received')
                )
                ->count();

            // ── Province Breakdown for admin/case_management panel ────────────
            $byProvince = collect();
            if (in_array($userRole, ['case_management', 'admin'])) {
                $monthStart = Carbon::now()->startOfMonth();
                $monthEnd   = Carbon::now()->endOfMonth();
                $byProvince = AnalyticsController::getByProvince($monthStart, $monthEnd);
            }

            // ── Active Cases Modal Breakdown (received + pending per role) ────
            $rolesForBreakdown = [
                'admin', 'malsu', 'case_management', 'records',
                'province_albay', 'province_camarines_sur', 'province_camarines_norte',
                'province_catanduanes', 'province_masbate', 'province_sorsogon',
            ];

            $activeByRole = [];
            foreach ($rolesForBreakdown as $role) {
                $activeByRole[$role] = [
                    'received' => CaseFile::where('overall_status', 'Active')
                        ->whereHas('documentTracking', fn($q) => $q
                            ->where('current_role', $role)
                            ->where('status', 'Received')
                        )
                        ->count(),
                    'pending' => CaseFile::where('overall_status', 'Active')
                        ->whereHas('documentTracking', fn($q) => $q
                            ->where('current_role', $role)
                            ->where('status', 'Pending Receipt')
                        )
                        ->count(),
                ];
            }

            // ── Pending Documents per Province (for admin modal) ──────────────
            $provinceRoleKeys = [
                'province_albay', 'province_camarines_sur', 'province_camarines_norte',
                'province_catanduanes', 'province_masbate', 'province_sorsogon',
            ];

            $provincePendingDocs = [];
            foreach ($provinceRoleKeys as $role) {
                $provincePendingDocs[$role] = \App\Models\DocumentTracking::with(['case', 'transferredBy'])
                    ->active()
                    ->where('current_role', $role)
                    ->where('status', 'Pending Receipt')
                    ->orderBy('transferred_at', 'desc')
                    ->get(['id', 'case_id', 'transferred_by_user_id', 'transferred_at', 'transfer_notes']);
            }

            // ── Regional users have no personal pending docs card ─────────────
            $myPendingDocs  = collect();
            $myPendingCount = 0;
        }

        // ── Pending Documents for THIS user's role (navbar bell) ─────────────
        $pendingDocuments = DocumentTracking::where('current_role', $userRole)
            ->where('status', 'Pending Receipt')
            ->whereNull('received_by_user_id')
            ->with(['case', 'transferredBy'])
            ->orderBy('transferred_at', 'desc')
            ->limit(5)
            ->get();

        $totalPendingDocs = DocumentTracking::where('current_role', $userRole)
            ->where('status', 'Pending Receipt')
            ->whereNull('received_by_user_id')
            ->count();

        // ── Monthly Trend — Last 6 Months ────────────────────────────────────
        $monthLabels = [];
        $monthlyData = [];

        for ($i = 5; $i >= 0; $i--) {
            $date          = Carbon::now()->subMonths($i);
            $monthLabels[] = $date->format('M Y');

            if ($isProvince) {
                $monthlyData[] = CaseFile::where('po_office', $user->getProvinceName())
                    ->whereHas('documentTracking', function ($q) use ($userRole) {
                        $q->where('current_role', $userRole)
                          ->where('status', 'Received');
                    })
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count();
            } else {
                $monthlyData[] = CaseFile::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count();
            }
        }

        // ── Top Establishments ────────────────────────────────────────────────
        $estQuery = $isProvince
            ? CaseFile::where('po_office', $user->getProvinceName())
                ->whereHas('documentTracking', function ($q) use ($userRole) {
                    $q->where('current_role', $userRole)->where('status', 'Received');
                })
            : CaseFile::query();

        $locationStats = $estQuery
            ->select('establishment_name', DB::raw('count(*) as total'))
            ->whereNotNull('establishment_name')
            ->where('establishment_name', '!=', '')
            ->groupBy('establishment_name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $locationLabels = $locationStats->pluck('establishment_name')->toArray();
        $locationData   = $locationStats->pluck('total')->toArray();

        if (empty($locationLabels)) {
            $locationLabels = ['No Data Available'];
            $locationData   = [0];
        }

        // ── Stage / Priority Breakdown ────────────────────────────────────────
        $stageBaseQuery = $isProvince
            ? CaseFile::where('po_office', $user->getProvinceName())
                ->whereHas('documentTracking', function ($q) use ($userRole) {
                    $q->where('current_role', $userRole)->where('status', 'Received');
                })
            : CaseFile::query();

        $stageData = [
            (clone $stageBaseQuery)->where('current_stage', 'like', '%Inspections%')->count(),
            (clone $stageBaseQuery)->where('current_stage', 'like', '%Docketing%')->count(),
            (clone $stageBaseQuery)->where('current_stage', 'like', '%Hearing%')->count(),
            (clone $stageBaseQuery)->where('current_stage', 'like', '%Resolution%')->count(),
        ];

        $criticalCases = (clone $stageBaseQuery)->where('current_stage', 'like', '%Hearing%')->count();
        $highCases     = (clone $stageBaseQuery)->where('current_stage', 'like', '%Docketing%')->count();
        $mediumCases   = (clone $stageBaseQuery)->where('current_stage', 'like', '%Inspections%')->count();
        $lowCases      = (clone $stageBaseQuery)->where('current_stage', 'like', '%Resolution%')->count();

        $totalForPercentage = $totalCases > 0 ? $totalCases : 1;

        $criticalPercentage = round(($criticalCases / $totalForPercentage) * 100);
        $highPercentage     = round(($highCases     / $totalForPercentage) * 100);
        $mediumPercentage   = round(($mediumCases   / $totalForPercentage) * 100);
        $lowPercentage      = round(($lowCases      / $totalForPercentage) * 100);

        // ── Recent Cases ──────────────────────────────────────────────────────
        $recentQuery = $isProvince
            ? CaseFile::where('po_office', $user->getProvinceName())
                ->whereHas('documentTracking', function ($q) use ($userRole) {
                    $q->where('current_role', $userRole)->where('status', 'Received');
                })
            : CaseFile::query();

        $recentCases = $recentQuery
            ->with('inspections')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // ── Document Tracking Stats (global) ─────────────────────────────────
        $documentsInTransit = DocumentTracking::where('status', 'in_transit')->count();
        $documentsPending   = DocumentTracking::where('status', 'pending')->count();
        $documentsReceived  = DocumentTracking::where('status', 'received')->count();

        $totalDocuments = max($documentsInTransit + $documentsPending + $documentsReceived, 1);

        $documentsInTransitPercent = round(($documentsInTransit / $totalDocuments) * 100);
        $documentsPendingPercent   = round(($documentsPending   / $totalDocuments) * 100);
        $documentsReceivedPercent  = round(($documentsReceived  / $totalDocuments) * 100);

        return view('frontend.index', compact(
            'totalCases',
            'activeCases',
            'disposedCases',
            'actualDisposedCases',
            'misDisposedCases',
            'misDisposedCasesList',
            'pendingDocuments',
            'totalPendingDocs',
            'stageData',
            'monthLabels',
            'monthlyData',
            'locationLabels',
            'locationData',
            'criticalCases',
            'highCases',
            'mediumCases',
            'lowCases',
            'criticalPercentage',
            'highPercentage',
            'mediumPercentage',
            'lowPercentage',
            'recentCases',
            'documentsInTransit',
            'documentsPending',
            'documentsReceived',
            'documentsInTransitPercent',
            'documentsPendingPercent',
            'documentsReceivedPercent',
            'caseManagementActiveCases',
            'activeByRole',
            'provincePendingDocs',
            'myPendingDocs',
            'myPendingCount',
            'byProvince',
            'isProvince',
            'isMalsu',
            'malsuActiveCases',
            'malsuDisposedCases',
        ));
    }

    public function login()
    {
        return view('frontend.login');
    }

    public function users()
    {
        $users = \App\Models\User::all();
        return view('frontend.users', compact('users'));
    }
}