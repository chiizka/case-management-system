<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\CaseFile;
use App\Models\User;
use App\Mail\BeyondCaseNotification;
use Carbon\Carbon;

class SendBeyondCaseNotifications extends Command
{
    protected $signature   = 'notify:beyond-cases';
    protected $description = 'Send email notifications about Beyond deadline cases and upcoming deadlines (runs daily at 7AM)';

    private $provinceRoleToOffice = [
        User::ROLE_PROVINCE_ALBAY           => 'Albay',
        User::ROLE_PROVINCE_CAMARINES_SUR   => 'Camarines Sur',
        User::ROLE_PROVINCE_CAMARINES_NORTE => 'Camarines Norte',
        User::ROLE_PROVINCE_CATANDUANES     => 'Catanduanes',
        User::ROLE_PROVINCE_MASBATE         => 'Masbate',
        User::ROLE_PROVINCE_SORSOGON        => 'Sorsogon',
    ];

    public function handle()
    {
        $reportDate = Carbon::now()->format('F d, Y');
        $today      = Carbon::today();
        $in5Days    = Carbon::today()->addDays(5);
        $sentCount  = 0;

        // ── Base query: active cases that are received ────────────────────────
        $baseQuery = CaseFile::whereNotIn('overall_status', ['Completed', 'Disposed', 'Appealed'])
            ->whereHas('documentTracking', function ($q) {
                $q->where('status', 'Received');
            });

        // ── 1. Fetch Beyond cases ─────────────────────────────────────────────
        $allBeyondCases = (clone $baseQuery)
            ->where(function ($q) {
                $q->where('status_docket',  'Beyond')
                  ->orWhere('status_1st_mc', 'Beyond')
                  ->orWhere('status_2nd_mc', 'Beyond')
                  ->orWhere('status_po_pct', 'Beyond')
                  ->orWhere('status_pct',    'Beyond');
            })
            ->get([
                'id', 'case_no', 'inspection_id', 'establishment_name', 'po_office',
                'status_docket', 'status_1st_mc', 'status_2nd_mc',
                'status_po_pct', 'status_pct',
            ]);

        // ── 2. Fetch Upcoming cases (deadline within 5 days, not yet Beyond) ──
        // We check each PCT deadline date against today + 5 days
        $allUpcomingCases = (clone $baseQuery)
            ->where(function ($q) use ($today, $in5Days) {
                // aging_docket between -5 and 0 = deadline within 5 days, not yet beyond
                $q->orWhere(function ($q2) {
                    $q2->whereBetween('aging_docket', [-5, 0]);
                })
                // aging_po_pct between -5 and 0
                ->orWhere(function ($q2) {
                    $q2->whereBetween('aging_po_pct', [-5, 0]);
                })
                // pct_96_days within 5 days from today and not yet Beyond
                ->orWhere(function ($q2) use ($today, $in5Days) {
                    $q2->whereBetween('pct_96_days', [$today, $in5Days])
                    ->where(function ($q3) {
                        $q3->where('status_pct', '!=', 'Beyond')
                            ->orWhereNull('status_pct');
                    });
                });
            })
            ->get([
                'id', 'case_no', 'inspection_id', 'establishment_name', 'po_office',
                'aging_docket', 'aging_po_pct', 'pct_96_days',
                'pct_for_docketing', 'po_pct',
                'status_docket', 'status_po_pct', 'status_pct',
            ]);

        // Skip entirely if nothing to report
        if ($allBeyondCases->isEmpty() && $allUpcomingCases->isEmpty()) {
            $this->info('No Beyond or Upcoming cases found. No emails sent.');
            return 0;
        }

        $fieldLabels = [
            'status_docket'  => 'Docket',
            'status_1st_mc'  => '1st MC',
            'status_2nd_mc'  => '2nd MC',
            'status_po_pct'  => 'PO PCT',
            'status_pct'     => 'PCT (96 days)',
        ];

        // ── Format Beyond cases ───────────────────────────────────────────────
        $formatBeyond = function ($collection) use ($fieldLabels) {
            return $collection->map(function ($case) use ($fieldLabels) {
                $beyondFields = [];
                foreach ($fieldLabels as $field => $label) {
                    if ($case->$field === 'Beyond') {
                        $beyondFields[] = $label;
                    }
                }
                return [
                    'case_no'        => $case->case_no ?? $case->inspection_id ?? 'N/A',
                    'establishment'  => $case->establishment_name ?? 'Unknown',
                    'po_office'      => $case->po_office ?? '-',
                    'beyond_summary' => implode(', ', $beyondFields),
                ];
            })->values()->toArray();
        };

        // ── Format Upcoming cases ─────────────────────────────────────────────
        $formatUpcoming = function ($collection) use ($today) {
            return $collection->map(function ($case) use ($today) {
                $upcomingFields = [];

                // aging_docket: negative = days remaining before pct_for_docketing deadline
                if ($case->aging_docket !== null && $case->aging_docket >= -5 && $case->aging_docket <= 0) {
                    $daysLeft = abs($case->aging_docket);
                    $deadline = $case->pct_for_docketing
                        ? \Carbon\Carbon::parse($case->pct_for_docketing)->format('M d, Y')
                        : 'N/A';
                    $upcomingFields[] = "Docket (due {$deadline} — {$daysLeft} day(s) left)";
                }

                // aging_po_pct: negative = days remaining before po_pct deadline
                if ($case->aging_po_pct !== null && $case->aging_po_pct >= -5 && $case->aging_po_pct <= 0) {
                    $daysLeft = abs($case->aging_po_pct);
                    $deadline = $case->po_pct
                        ? \Carbon\Carbon::parse($case->po_pct)->format('M d, Y')
                        : 'N/A';
                    $upcomingFields[] = "PO PCT (due {$deadline} — {$daysLeft} day(s) left)";
                }

                // pct_96_days: compare date directly since there's no aging field for it
                if ($case->pct_96_days && $case->status_pct !== 'Beyond') {
                    $deadline = \Carbon\Carbon::parse($case->pct_96_days);
                    $daysLeft = $today->diffInDays($deadline, false);
                    if ($daysLeft >= 0 && $daysLeft <= 5) {
                        $upcomingFields[] = "PCT 96 days (due " . $deadline->format('M d, Y') . " — {$daysLeft} day(s) left)";
                    }
                }

                return [
                    'case_no'          => $case->case_no ?? $case->inspection_id ?? 'N/A',
                    'establishment'    => $case->establishment_name ?? 'Unknown',
                    'po_office'        => $case->po_office ?? '-',
                    'upcoming_summary' => implode('; ', $upcomingFields),
                ];
            })->filter(function ($case) {
                return !empty($case['upcoming_summary']);
            })->values()->toArray();
        };

        // ── Helper: send email to one user ────────────────────────────────────
        $sendEmail = function ($user, $beyondCases, $upcomingCases) use ($reportDate, &$sentCount) {
            try {
                Mail::to($user->email)
                    ->send(new BeyondCaseNotification(
                        $user->fname . ' ' . $user->lname,
                        $beyondCases,
                        $upcomingCases,
                        $reportDate
                    ));
                $sentCount++;
                $this->info("✓ Sent to {$user->role}: {$user->email}");
            } catch (\Exception $e) {
                Log::error("Failed to send notification to {$user->email}: " . $e->getMessage());
                $this->error("✗ Failed: {$user->email} — " . $e->getMessage());
            }
        };

        // ── 3. Send to ADMIN — all cases ──────────────────────────────────────
        $allBeyondFormatted   = $formatBeyond($allBeyondCases);
        $allUpcomingFormatted = $formatUpcoming($allUpcomingCases);

        foreach (User::where('role', User::ROLE_ADMIN)->get() as $admin) {
            $sendEmail($admin, $allBeyondFormatted, $allUpcomingFormatted);
        }

        // ── 4. Send to CASE MANAGEMENT — all cases ────────────────────────────
        foreach (User::where('role', User::ROLE_CASE_MANAGEMENT)->get() as $cm) {
            $sendEmail($cm, $allBeyondFormatted, $allUpcomingFormatted);
        }

        // ── 5. Send to PROVINCE users — their province only ───────────────────
        foreach ($this->provinceRoleToOffice as $role => $officeName) {
            $provinceBeyond   = $allBeyondCases->where('po_office', $officeName);
            $provinceUpcoming = $allUpcomingCases->where('po_office', $officeName);

            if ($provinceBeyond->isEmpty() && $provinceUpcoming->isEmpty()) {
                $this->info("Nothing to report for {$officeName}, skipping.");
                continue;
            }

            $provinceBeyondFormatted   = $formatBeyond($provinceBeyond);
            $provinceUpcomingFormatted = $formatUpcoming($provinceUpcoming);

            foreach (User::where('role', $role)->get() as $provinceUser) {
                $sendEmail($provinceUser, $provinceBeyondFormatted, $provinceUpcomingFormatted);
            }
        }

        $this->info("Done. Total emails sent: {$sentCount}");
        return 0;
    }
}