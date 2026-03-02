<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CaseFile;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $year  = (int) $request->get('year',  now()->year);
        $month = (int) $request->get('month', now()->month);

        $startOfYear  = Carbon::create($year, 1, 1)->startOfYear();
        $monthStart   = Carbon::create($year, $month, 1)->startOfMonth();
        $monthEnd     = Carbon::create($year, $month, 1)->endOfMonth();

        $archived = ['Completed', 'Disposed', 'Appealed'];
        $active   = fn($q) => $q->whereNotIn('overall_status', $archived);

        // ── Carry-over: created before this year, still active ───────────────
        $carryOver = CaseFile::whereDate('created_at', '<', $startOfYear)
            ->whereNotIn('overall_status', $archived)
            ->count();

        // ── New cases this month ──────────────────────────────────────────────
        $newCases = CaseFile::whereDate('created_at', '>=', $monthStart)
            ->whereDate('created_at', '<=', $monthEnd)
            ->count();

        // ── Total cases handled this month (carry-over at year start + new up to selected month) ──
        $newUpToMonth = CaseFile::whereDate('created_at', '>=', $startOfYear)
            ->whereDate('created_at', '<=', $monthEnd)
            ->count();
        $totalHandled = $carryOver + $newUpToMonth;

        // ── Disposed helper (uses updated_at fallback) ───────────────────────
        $disposedQuery = function($start, $end) use ($archived) {
            return CaseFile::whereIn('overall_status', $archived)
                ->where(fn($q) => $q
                    ->where(fn($q2) => $q2
                        ->whereNotNull('date_of_order_actual')
                        ->whereDate('date_of_order_actual', '>=', $start)
                        ->whereDate('date_of_order_actual', '<=', $end))
                    ->orWhere(fn($q2) => $q2
                        ->whereNull('date_of_order_actual')
                        ->whereDate('updated_at', '>=', $start)
                        ->whereDate('updated_at', '<=', $end))
                );
        };

        // ── Disposed this month ───────────────────────────────────────────────
        $disposedThisMonth = $disposedQuery($monthStart, $monthEnd)->count();

        // ── Disposed within / beyond PCT ─────────────────────────────────────
        $disposedWithin = $disposedQuery($monthStart, $monthEnd)
            ->where(fn($q) => $q->where('status_pct', 'Within PCT')->orWhereNull('status_pct'))
            ->count();
        $disposedBeyond = $disposedQuery($monthStart, $monthEnd)
            ->where('status_pct', 'Beyond PCT')
            ->count();

        // ── Pending = total handled - total disposed year-to-date ─────────────
        $disposedYTD = $disposedQuery($startOfYear, $monthEnd)->count();
        $pending     = $totalHandled - $disposedYTD;

        // ── Disposition rate (of new cases only — DOLE standard) ─────────────
        $dispositionRate = $newCases > 0
            ? round(($disposedThisMonth / $newCases) * 100, 1)
            : 0;

        // ── Monetary & workers this month ─────────────────────────────────────
        $monetary = $disposedQuery($monthStart, $monthEnd)
            ->sum('compliance_order_monetary_award') ?? 0;

        $workers = $disposedQuery($monthStart, $monthEnd)
            ->selectRaw('SUM(COALESCE(affected_male,0) + COALESCE(affected_female,0)) as total')
            ->value('total') ?? 0;

        // ── By province breakdown ─────────────────────────────────────────────
        // Map province display names → document_tracking current_role values
        $provinceRoleMap = [
            'Albay'            => 'province_albay',
            'Camarines Sur'    => 'province_camarines_sur',
            'Camarines Norte'  => 'province_camarines_norte',
            'Catanduanes'      => 'province_catanduanes',
            'Masbate'          => 'province_masbate',
            'Sorsogon'         => 'province_sorsogon',
        ];

        $byProvince = collect($provinceRoleMap)->map(function($role, $prov) use ($archived, $monthStart, $monthEnd) {
            // Total cases that originated from this province (po_office = origin)
            $total = CaseFile::where('po_office', $prov)->count();

            // Active cases CURRENTLY located at this province (via DocumentTracking.current_role)
            // This excludes cases transferred out to regional or another province
            $active = CaseFile::whereNotIn('overall_status', $archived)
                ->whereHas('documentTracking', fn($q) => $q->where('current_role', $role))
                ->count();

            // Disposed cases that originated from this province this month
            $disposed = CaseFile::where('po_office', $prov)
                ->whereIn('overall_status', $archived)
                ->where(fn($q) => $q
                    ->where(fn($q2) => $q2->whereNotNull('date_of_order_actual')
                        ->whereDate('date_of_order_actual', '>=', $monthStart)
                        ->whereDate('date_of_order_actual', '<=', $monthEnd))
                    ->orWhere(fn($q2) => $q2->whereNull('date_of_order_actual')
                        ->whereDate('updated_at', '>=', $monthStart)
                        ->whereDate('updated_at', '<=', $monthEnd))
                )->count();

            return [
                'name'     => $prov,
                'total'    => $total,
                'active'   => $active,  // Only cases physically still at this province
                'disposed' => $disposed,
            ];
        });

        // ── Monthly trend (all months up to selected) ─────────────────────────
        $monthlyTrend = [];
        for ($m = 1; $m <= $month; $m++) {
            $s = Carbon::create($year, $m, 1)->startOfMonth();
            $e = Carbon::create($year, $m, 1)->endOfMonth();
            $monthlyTrend[$m] = [
                'new'      => CaseFile::whereDate('created_at', '>=', $s)->whereDate('created_at', '<=', $e)->count(),
                'disposed' => CaseFile::whereIn('overall_status', $archived)
                    ->where(fn($q) => $q
                        ->where(fn($q2) => $q2->whereNotNull('date_of_order_actual')
                            ->whereDate('date_of_order_actual', '>=', $s)->whereDate('date_of_order_actual', '<=', $e))
                        ->orWhere(fn($q2) => $q2->whereNull('date_of_order_actual')
                            ->whereDate('updated_at', '>=', $s)->whereDate('updated_at', '<=', $e))
                    )->count(),
            ];
        }

        // ── Stage distribution (active cases) ────────────────────────────────
        $stageDistribution = CaseFile::whereNotIn('overall_status', $archived)
            ->select('current_stage', DB::raw('count(*) as count'))
            ->groupBy('current_stage')
            ->orderBy('current_stage')
            ->pluck('count', 'current_stage');

        // ── Recent activity (last 5 archived cases) ───────────────────────────
        $recentActivity = CaseFile::whereIn('overall_status', $archived)
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get(['inspection_id', 'establishment_name', 'po_office', 'overall_status', 'updated_at']);

        return view('frontend.analytics', compact(
            'year', 'month',
            'carryOver', 'newCases', 'totalHandled',
            'disposedThisMonth', 'disposedWithin', 'disposedBeyond',
            'pending', 'dispositionRate',
            'monetary', 'workers',
            'byProvince', 'monthlyTrend', 'stageDistribution',
            'recentActivity'
        ));
    }
}