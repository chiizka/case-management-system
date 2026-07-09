<?php

namespace App\Console\Commands;

use App\Mail\MissingSheriffReportNotification;
use App\Models\User;
use App\Services\SheriffReportComplianceService;
use Carbon\Carbon;
use Illuminate\Console\Command;
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

    private function getMissingCasesForRole(string $role, Carbon $targetMonth): array
    {
        return app(SheriffReportComplianceService::class)->getMissingCasesForRole($role, $targetMonth);
    }

    private function isScheduledRunDay(): bool
    {
        $target = Carbon::now()->startOfMonth();

        while ($target->isWeekend()) {
            $target->addDay();
        }

        return Carbon::now()->isSameDay($target);
    }
}