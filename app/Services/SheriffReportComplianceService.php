<?php

namespace App\Services;

use App\Models\CaseFile;
use Carbon\Carbon;

class SheriffReportComplianceService
{
    /**
     * Whole-case report history for a sheriff's currently assigned cases.
     * No month-locking, no urgency — just a reference view.
     */
    public function getCasesWithHistory(string $role)
    {
        return CaseFile::where('overall_status', 'Active')
            ->whereHas('documentTracking', fn ($q) => $q
                ->where('current_role', $role)
                ->where('status', 'Received')
            )
            ->whereHas('malsu')
            ->with(['malsu.sheriffsReports' => fn ($q) => $q->orderByDesc('report_month')])
            ->get()
            ->map(function ($case) {
                $reports = $case->malsu->sheriffsReports;
                $latest  = $reports->first();

                return [
                    'case_id'             => $case->id,
                    'case_no'             => $case->case_no ?? $case->inspection_id ?? 'N/A',
                    'establishment'       => $case->establishment_name ?? 'Unknown',
                    'total_reports'       => $reports->count(),
                    'latest_month_label'  => $latest ? $latest->report_month->format('F Y') : null,
                    'latest_submitted_at' => $latest ? optional($latest->report_date_submitted)->format('M d, Y') : null,
                ];
            })
            ->values();
    }

    /**
     * Cases missing a report for $targetMonth (normally "last month"),
     * with how many consecutive months in a row have been missed.
     */
    public function getMissingCasesForRole(string $role, Carbon $targetMonth): array
    {
        $cases = CaseFile::where('overall_status', 'Active')
            ->whereHas('documentTracking', fn ($q) => $q
                ->where('current_role', $role)
                ->where('status', 'Received')
            )
            ->whereHas('malsu')
            ->with('malsu.sheriffsReports')
            ->get();

        $missing = [];

        foreach ($cases as $case) {
            $hasReport = $case->malsu->sheriffsReports->contains(
                fn ($report) => $report->report_month && $report->report_month->isSameMonth($targetMonth)
            );

            if (!$hasReport) {
                $missing[] = [
                    'case_id'                    => $case->id,
                    'case_no'                    => $case->case_no ?? $case->inspection_id ?? 'N/A',
                    'establishment'              => $case->establishment_name ?? 'Unknown',
                    'po_office'                  => $case->po_office ?? '-',
                    'consecutive_missing_months' => $this->countConsecutiveMissingMonths($case, $targetMonth),
                ];
            }
        }

        return $missing;
    }

    /**
     * Walks backward month-by-month from $fromMonth counting how many
     * consecutive months have no report, stopping at the case's creation month.
     */
    private function countConsecutiveMissingMonths($case, Carbon $fromMonth): int
    {
        $reportedMonths = $case->malsu->sheriffsReports
            ->pluck('report_month')
            ->filter()
            ->map(fn ($d) => $d->format('Y-m'))
            ->flip();

        $floor = $case->created_at
            ? $case->created_at->copy()->startOfMonth()
            : $fromMonth->copy()->subMonths(24);

        $count  = 0;
        $cursor = $fromMonth->copy();

        while ($cursor->greaterThanOrEqualTo($floor) && !isset($reportedMonths[$cursor->format('Y-m')])) {
            $count++;
            $cursor->subMonth();
        }

        return $count;
    }
}