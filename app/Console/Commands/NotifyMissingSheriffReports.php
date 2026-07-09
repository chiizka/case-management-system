<?php

namespace App\Console\Commands;

use App\Mail\MissingSheriffReportNotification;
use App\Models\CaseFile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotifyMissingSheriffReports extends Command
{
    protected $signature = 'notify:missing-sheriff-reports
                            {--force : Run even if today is not the scheduled weekday-adjusted 1st}
                            {--dry-run : Preview recipients and missing cases without sending any email}';

    protected $description = 'Email each sheriff a list of their currently assigned cases missing a sheriff report for last month';

    public function handle()
    {
        if (!$this->option('force') && !$this->isScheduledRunDay()) {
            $this->line('Not the scheduled run day (first weekday on/after the 1st). Skipping. Use --force to override.');
            return self::SUCCESS;
        }

        $dryRun = $this->option('dry-run');

        // "Last month" — stable regardless of which day within this month we actually run on.
        $targetMonth = Carbon::now()->startOfMonth()->subMonth();
        $monthLabel  = $targetMonth->format('F Y');

        $this->info("Checking missing sheriff reports for {$monthLabel}" . ($dryRun ? ' (dry run)' : ''));

        $totalEmailsSent = 0;

        foreach (User::SHERIFF_ROLES as $role) {
            $missingCases = $this->getMissingCasesForRole($role, $targetMonth);

            if (empty($missingCases)) {
                continue;
            }

            $sheriffUsers = User::where('role', $role)->get();

            if ($sheriffUsers->isEmpty()) {
                $this->warn("No user found for role {$role}, but " . count($missingCases) . " case(s) missing reports. Skipped.");
                continue;
            }

            foreach ($sheriffUsers as $user) {
                if (empty($user->email)) {
                    $this->warn("User {$user->fname} {$user->lname} ({$role}) has no email. Skipped.");
                    continue;
                }

                if ($dryRun) {
                    $this->line("[DRY RUN] Would email {$user->email} ({$role}) — " . count($missingCases) . " missing case(s): "
                        . collect($missingCases)->pluck('case_no')->implode(', '));
                    continue;
                }

                Mail::to($user->email)->send(new MissingSheriffReportNotification(
                    trim($user->fname . ' ' . $user->lname),
                    $monthLabel,
                    $missingCases
                ));

                $totalEmailsSent++;
            }
        }

        $this->info($dryRun ? 'Dry run complete.' : "Done. {$totalEmailsSent} email(s) sent.");

        return self::SUCCESS;
    }

    /**
     * Cases currently assigned (Received) to the given sheriff role that
     * have no SheriffsReport row for the target month.
     */
    private function getMissingCasesForRole(string $role, Carbon $targetMonth): array
    {
        $cases = CaseFile::where('overall_status', 'Active')
            ->whereHas('documentTracking', function ($q) use ($role) {
                $q->where('current_role', $role)->where('status', 'Received');
            })
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
                    'case_no'       => $case->case_no ?? $case->inspection_id ?? 'N/A',
                    'establishment' => $case->establishment_name ?? 'Unknown',
                ];
            }
        }

        return $missing;
    }

    /**
     * True only on the first weekday on/after the 1st of the current month.
     */
    private function isScheduledRunDay(): bool
    {
        $target = Carbon::now()->startOfMonth();

        while ($target->isWeekend()) {
            $target->addDay();
        }

        return Carbon::now()->isSameDay($target);
    }
}