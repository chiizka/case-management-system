<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CaseFile;
use App\Models\Inspection;
use App\Models\DocumentTracking;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class FrontController extends Controller
{
    public function index()
    {
        $user       = Auth::user();
        $isProvince = $user->isProvince();
        $userRole   = $user->role; // e.g. 'province_albay'

        // ─────────────────────────────────────────────────────────────────────
        // For province users, all counts follow the same rule:
        //
        //   - Case must have originated from their province (po_office)
        //   - The document must have been RECEIVED by their office
        //     (documentTracking.status = 'Received')
        //   - Active Cases:  overall_status = Active  + received
        //   - Disposed Cases: overall_status = Disposed + received
        //   - Total Cases:  Active + Disposed (both received) — no raw count
        //   - Closed Cases: overall_status = Completed + received
        //
        // For non-province users: no scoping, see everything system-wide.
        // ─────────────────────────────────────────────────────────────────────

        if ($isProvince) {
            $provinceName = $user->getProvinceName();

            // Base: cases from this province that this office has already received
            $receivedByProvince = CaseFile::where('po_office', $provinceName)
                ->whereHas('documentTracking', function ($q) use ($userRole) {
                    $q->where('current_role', $userRole)
                      ->where('status', 'Received');
                });

            $activeCases         = (clone $receivedByProvince)->where('overall_status', 'Active')->count();
            $disposedCases       = (clone $receivedByProvince)->where('overall_status', 'Disposed')->count();
            $actualDisposedCases = 0;
            $misDisposedCases    = 0;

            // Total Handled = ALL cases that originated from this province (regardless of current location)
            $totalCases = CaseFile::where('po_office', $provinceName)->count();

        } else {
            // Regional roles: system-wide counts, no scoping
            $activeCases         = CaseFile::where('overall_status', 'Active')->count();
            $disposedCases       = CaseFile::where('overall_status', 'Disposed')->count();
           $actualDisposedCases = CaseFile::where('overall_status', 'Completed')->count();
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
            $totalCases          = CaseFile::count();
        }

        // ── Active Cases Modal Breakdown ──────────────────────────────────────
        if ($isProvince) {
            $activeByRole = [
                $userRole => $activeCases,
            ];
        } else {
            $rolesForBreakdown = [
                'admin', 'malsu', 'case_management', 'records',
                'province_albay', 'province_camarines_sur', 'province_camarines_norte',
                'province_catanduanes', 'province_masbate', 'province_sorsogon',
            ];

            $activeByRole = [];
            foreach ($rolesForBreakdown as $role) {
                $activeByRole[$role] = CaseFile::where('overall_status', 'Active')
                    ->whereHas('documentTracking', fn($q) => $q->where('current_role', $role))
                    ->count();
            }
        }

        // ── Pending Documents for THIS user's role ────────────────────────────
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
                // Only received cases from this province per month
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

        // ── Document Tracking Stats (global) ──────────────────────────────────
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
            'activeByRole',
            'isProvince'
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