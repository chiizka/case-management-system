<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CaseFile;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $user       = Auth::user();
        $isProvince = $user->isProvince();
        $userRole   = $user->role; // e.g. 'province_albay'

        $year  = (int) $request->get('year',  now()->year);
        $month = (int) $request->get('month', now()->month);

        $startOfYear = Carbon::create($year, 1, 1)->startOfYear();
        $monthStart  = Carbon::create($year, $month, 1)->startOfMonth();
        $monthEnd    = Carbon::create($year, $month, 1)->endOfMonth();

        $archived = ['Completed', 'Disposed', 'Appealed'];

        // ─────────────────────────────────────────────────────────────────────
        // Base scope helpers
        //
        // Province users:
        //   - "origin" queries  → filter by po_office (their province)
        //   - "received" queries → also require documentTracking.status = Received
        //     so unacknowledged cases are excluded from all counts
        //
        // Regional roles: no scoping, see everything system-wide
        // ─────────────────────────────────────────────────────────────────────

        // Applies province + received filter to any query builder
        $scopeToProvince = function ($q) use ($isProvince, $user, $userRole) {
            if ($isProvince) {
                $q->where('po_office', $user->getProvinceName())
                  ->whereHas('documentTracking', function ($dq) use ($userRole) {
                      $dq->where('current_role', $userRole)
                         ->where('status', 'Received');
                  });
            }
            return $q;
        };

        // ── Carry-over: created before this year, still active ───────────────
        $carryOver = $scopeToProvince(
            CaseFile::whereDate('created_at', '<', $startOfYear)
                ->whereNotIn('overall_status', $archived)
        )->count();

        // ── New cases this month ──────────────────────────────────────────────
        $newCases = $scopeToProvince(
            CaseFile::whereDate('created_at', '>=', $monthStart)
                ->whereDate('created_at', '<=', $monthEnd)
        )->count();

        // ── Total cases handled this month ────────────────────────────────────
        $newUpToMonth = $scopeToProvince(
            CaseFile::whereDate('created_at', '>=', $startOfYear)
                ->whereDate('created_at', '<=', $monthEnd)
        )->count();

        $totalHandled = $carryOver + $newUpToMonth;

        // ── Disposed helper ───────────────────────────────────────────────────
        $disposedQuery = function ($start, $end) use ($archived, $scopeToProvince) {
            return $scopeToProvince(
                CaseFile::whereIn('overall_status', $archived)
                    ->where(fn($q) => $q
                        ->where(fn($q2) => $q2
                            ->whereNotNull('date_of_order_actual')
                            ->whereDate('date_of_order_actual', '>=', $start)
                            ->whereDate('date_of_order_actual', '<=', $end))
                        ->orWhere(fn($q2) => $q2
                            ->whereNull('date_of_order_actual')
                            ->whereDate('updated_at', '>=', $start)
                            ->whereDate('updated_at', '<=', $end))
                    )
            );
        };

        // ── Disposed this month ───────────────────────────────────────────────
        $disposedThisMonth = $disposedQuery($monthStart, $monthEnd)->count();

        // ── Within / beyond PCT ───────────────────────────────────────────────
        $disposedWithin = $disposedQuery($monthStart, $monthEnd)
            ->where(fn($q) => $q->where('status_pct', 'Within PCT')->orWhereNull('status_pct'))
            ->count();

        $disposedBeyond = $disposedQuery($monthStart, $monthEnd)
            ->where('status_pct', 'Beyond PCT')
            ->count();

        // ── Pending ───────────────────────────────────────────────────────────
        $disposedYTD = $disposedQuery($startOfYear, $monthEnd)->count();
        $pending     = $totalHandled - $disposedYTD;

        // ── Disposition rate ──────────────────────────────────────────────────
        $dispositionRate = $newCases > 0
            ? round(($disposedThisMonth / $newCases) * 100, 1)
            : 0;

        // ── Monetary & workers ────────────────────────────────────────────────
        $monetary = $disposedQuery($monthStart, $monthEnd)
            ->sum('compliance_order_monetary_award') ?? 0;

        $workers = $disposedQuery($monthStart, $monthEnd)
            ->selectRaw('SUM(COALESCE(affected_male,0) + COALESCE(affected_female,0)) as total')
            ->value('total') ?? 0;

        // ── By province breakdown (regional roles only) ───────────────────────
        // Province users don't need this — they only see their own data.
        $byProvince = collect();

        if (!$isProvince) {
            $provinceRoleMap = [
                'Albay'           => 'province_albay',
                'Camarines Sur'   => 'province_camarines_sur',
                'Camarines Norte' => 'province_camarines_norte',
                'Catanduanes'     => 'province_catanduanes',
                'Masbate'         => 'province_masbate',
                'Sorsogon'        => 'province_sorsogon',
            ];

            $byProvince = collect($provinceRoleMap)->map(function ($role, $prov) use ($archived, $monthStart, $monthEnd) {
                $total = CaseFile::where('po_office', $prov)->count();

                // Active cases currently physically at this province (received)
                $active = CaseFile::whereNotIn('overall_status', $archived)
                    ->whereHas('documentTracking', fn($q) => $q
                        ->where('current_role', $role)
                        ->where('status', 'Received'))
                    ->count();

                $disposed = CaseFile::where('po_office', $prov)
                    ->whereIn('overall_status', $archived)
                    ->where(fn($q) => $q
                        ->where(fn($q2) => $q2
                            ->whereNotNull('date_of_order_actual')
                            ->whereDate('date_of_order_actual', '>=', $monthStart)
                            ->whereDate('date_of_order_actual', '<=', $monthEnd))
                        ->orWhere(fn($q2) => $q2
                            ->whereNull('date_of_order_actual')
                            ->whereDate('updated_at', '>=', $monthStart)
                            ->whereDate('updated_at', '<=', $monthEnd))
                    )->count();

                return [
                    'name'     => $prov,
                    'total'    => $total,
                    'active'   => $active,
                    'disposed' => $disposed,
                ];
            });
        }

        // ── Monthly trend ─────────────────────────────────────────────────────
        $monthlyTrend = [];
        for ($m = 1; $m <= $month; $m++) {
            $s = Carbon::create($year, $m, 1)->startOfMonth();
            $e = Carbon::create($year, $m, 1)->endOfMonth();

            $monthlyTrend[$m] = [
                'new'      => $scopeToProvince(
                    CaseFile::whereDate('created_at', '>=', $s)
                        ->whereDate('created_at', '<=', $e)
                )->count(),

                'disposed' => $disposedQuery($s, $e)->count(),
            ];
        }

        // ── Stage distribution (active cases, scoped) ─────────────────────────
        $stageDistribution = $scopeToProvince(
            CaseFile::whereNotIn('overall_status', $archived)
        )
            ->select('current_stage', DB::raw('count(*) as count'))
            ->groupBy('current_stage')
            ->orderBy('current_stage')
            ->pluck('count', 'current_stage');

        // ── Recent activity (last 5 archived cases, scoped) ───────────────────
        $recentActivity = $scopeToProvince(
            CaseFile::whereIn('overall_status', $archived)
        )
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get(['inspection_id', 'case_no', 'establishment_name', 'po_office', 'overall_status', 'updated_at']);

        return view('frontend.analytics', compact(
            'year', 'month',
            'carryOver', 'newCases', 'totalHandled',
            'disposedThisMonth', 'disposedWithin', 'disposedBeyond',
            'pending', 'dispositionRate',
            'monetary', 'workers',
            'byProvince', 'monthlyTrend', 'stageDistribution',
            'recentActivity',
            'isProvince'
        ));
    }
}