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
        $sentCount  = 0;

        // ── Base query ────────────────────────────────────────────────────
        // Only Active cases that are currently Received at some role
        $baseQuery = CaseFile::where('overall_status', 'Active')
            ->whereHas('documentTracking', function ($q) {
                $q->where('status', 'Received');
            });

        // ── Common select fields ──────────────────────────────────────────
        $selectFields = [
            'id', 'case_no', 'inspection_id', 'establishment_name', 'po_office',
            // Docket
            'status_docket',      'aging_docket',        'pct_for_docketing',
            // 1st MC
            'status_1st_mc',      'first_mc_pct',        'lapse_20_day_period',
            // 2nd MC
            'status_2nd_mc',      'second_last_mc_pct',  'date_1st_mc_actual',
            // PO PCT
            'status_po_pct',      'aging_po_pct',        'po_pct',
            // PCT 96 days
            'status_pct',         'pct_96_days',
        ];

        // ══════════════════════════════════════════════════════════════════
        // FETCH ALL BEYOND — we'll filter per recipient below
        // ══════════════════════════════════════════════════════════════════
        $allBeyondCases = (clone $baseQuery)
            ->where(function ($q) {
                $q->where('status_docket',  'Beyond')
                  ->orWhere('status_1st_mc', 'Beyond')
                  ->orWhere('status_2nd_mc', 'Beyond')
                  ->orWhere('status_po_pct', 'Beyond')
                  ->orWhere('status_pct',    'Beyond');
            })
            ->with('documentTracking')
            ->get($selectFields);

        // ══════════════════════════════════════════════════════════════════
        // FETCH ALL UPCOMING — we'll filter per recipient below
        // ══════════════════════════════════════════════════════════════════
        $in5Days = Carbon::today()->addDays(5);

        $allUpcomingCases = (clone $baseQuery)
            ->where(function ($q) use ($today, $in5Days) {
                $q->orWhere(function ($q2) {
                    $q2->whereBetween('aging_docket', [-5, 0])
                       ->where(function ($q3) {
                           $q3->where('status_docket', '!=', 'Beyond')->orWhereNull('status_docket');
                       });
                });
                $q->orWhere(function ($q2) {
                    $q2->whereBetween('aging_po_pct', [-5, 0])
                       ->where(function ($q3) {
                           $q3->where('status_po_pct', '!=', 'Beyond')->orWhereNull('status_po_pct');
                       });
                });
                $q->orWhere(function ($q2) use ($today, $in5Days) {
                    $q2->whereBetween('pct_96_days', [$today, $in5Days])
                       ->where(function ($q3) {
                           $q3->where('status_pct', '!=', 'Beyond')->orWhereNull('status_pct');
                       });
                });
                $q->orWhere(function ($q2) {
                    $q2->whereBetween('first_mc_pct', [-5, 0])
                       ->where(function ($q3) {
                           $q3->where('status_1st_mc', '!=', 'Beyond')->orWhereNull('status_1st_mc');
                       });
                });
                $q->orWhere(function ($q2) {
                    $q2->whereBetween('second_last_mc_pct', [-5, 0])
                       ->where(function ($q3) {
                           $q3->where('status_2nd_mc', '!=', 'Beyond')->orWhereNull('status_2nd_mc');
                       });
                });
            })
            ->with('documentTracking')
            ->get($selectFields);

        if ($allBeyondCases->isEmpty() && $allUpcomingCases->isEmpty()) {
            $this->info('No Beyond or Upcoming cases found. No emails sent.');
            return 0;
        }

        // ══════════════════════════════════════════════════════════════════
        // FORMAT HELPERS
        // Each formatter accepts a collection and a set of flags controlling
        // which PCT fields to include in the output.
        // ══════════════════════════════════════════════════════════════════

        /**
         * Build the beyond array for a given collection, respecting field flags.
         */
        $formatBeyond = function (
            $collection,
            bool $showDocket,
            bool $show1stMC,
            bool $show2ndMC,
            bool $showPoPct,
            bool $showPct96
        ) {
            $labelMap = [];
            if ($showDocket) $labelMap['status_docket'] = 'Docket';
            if ($show1stMC)  $labelMap['status_1st_mc'] = '1st MC';
            if ($show2ndMC)  $labelMap['status_2nd_mc'] = '2nd MC';
            if ($showPoPct)  $labelMap['status_po_pct'] = 'PO PCT';
            if ($showPct96)  $labelMap['status_pct']    = 'PCT (96 days)';

            return $collection->map(function ($case) use ($labelMap) {
                $beyondFields = [];
                foreach ($labelMap as $field => $label) {
                    if ($case->$field === 'Beyond') {
                        $beyondFields[] = $label;
                    }
                }
                if (empty($beyondFields)) return null;
                return [
                    'case_no'        => $case->case_no ?? $case->inspection_id ?? 'N/A',
                    'establishment'  => $case->establishment_name ?? 'Unknown',
                    'po_office'      => $case->po_office ?? '-',
                    'beyond_summary' => implode(', ', $beyondFields),
                ];
            })->filter()->values()->toArray();
        };

        /**
         * Build the upcoming array for a given collection, respecting field flags.
         */
        $formatUpcoming = function (
            $collection,
            bool $showDocket,
            bool $show1stMC,
            bool $show2ndMC,
            bool $showPoPct,
            bool $showPct96
        ) use ($today) {
            return $collection->map(function ($case) use (
                $today, $showDocket, $show1stMC, $show2ndMC, $showPoPct, $showPct96
            ) {
                $upcomingFields = [];

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
                    $upcomingFields[] = "Docket (due {$deadline} — {$daysLeft} day(s) left)";
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
                    $upcomingFields[] = "PO PCT (due {$deadline} — {$daysLeft} day(s) left)";
                }

                if ($showPct96 && $case->pct_96_days && $case->status_pct !== 'Beyond') {
                    $deadline = Carbon::parse($case->pct_96_days);
                    $daysLeft = (int) $today->diffInDays($deadline, false);
                    if ($daysLeft >= 0 && $daysLeft <= 5) {
                        $upcomingFields[] = "PCT 96 days (due {$deadline->format('M d, Y')} — {$daysLeft} day(s) left)";
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
                    $upcomingFields[] = "1st MC (due {$deadline} — {$daysLeft} day(s) left)";
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
                    $upcomingFields[] = "2nd MC (due {$deadline} — {$daysLeft} day(s) left)";
                }

                if (empty($upcomingFields)) return null;
                return [
                    'case_no'          => $case->case_no ?? $case->inspection_id ?? 'N/A',
                    'establishment'    => $case->establishment_name ?? 'Unknown',
                    'po_office'        => $case->po_office ?? '-',
                    'upcoming_summary' => implode('; ', $upcomingFields),
                ];
            })->filter()->values()->toArray();
        };

        // ── Send helper ───────────────────────────────────────────────────
        $sendEmail = function ($user, $beyondCases, $upcomingCases) use ($reportDate, &$sentCount) {
            if (empty($beyondCases) && empty($upcomingCases)) {
                return;
            }
            try {
                Mail::to($user->email)
                    ->send(new BeyondCaseNotification(
                        $user->fname . ' ' . $user->lname,
                        $beyondCases,
                        $upcomingCases,
                        $reportDate
                    ));
                $sentCount++;
                $this->info("✓ Sent to [{$user->role}] {$user->email}");
            } catch (\Exception $e) {
                Log::error("Failed to send notification to {$user->email}: " . $e->getMessage());
                $this->error("✗ Failed: {$user->email} — " . $e->getMessage());
            }
        };

        // ══════════════════════════════════════════════════════════════════
        // SEND: Admin — all five PCT fields, all cases region-wide
        // ══════════════════════════════════════════════════════════════════
        $adminBeyond   = $formatBeyond($allBeyondCases,   true, true, true, true, true);
        $adminUpcoming = $formatUpcoming($allUpcomingCases, true, true, true, true, true);

        foreach (User::where('role', User::ROLE_ADMIN)->get() as $admin) {
            $sendEmail($admin, $adminBeyond, $adminUpcoming);
        }

        // ══════════════════════════════════════════════════════════════════
        // SEND: Case Management — Docket, PO PCT, PCT 96 days only
        //       (no 1st MC, no 2nd MC)
        // ══════════════════════════════════════════════════════════════════
        $cmBeyond   = $formatBeyond($allBeyondCases,    true, false, false, true, true);
        $cmUpcoming = $formatUpcoming($allUpcomingCases, true, false, false, true, true);

        foreach (User::where('role', User::ROLE_CASE_MANAGEMENT)->get() as $cm) {
            if ($cm->isProvincialCaseManagement()) {
                $provinceName = $cm->getCaseManagementProvinceName();

                $scopedBeyond   = $allBeyondCases->where('po_office', $provinceName);
                $scopedUpcoming = $allUpcomingCases->where('po_office', $provinceName);

                $beyondFormatted   = $formatBeyond($scopedBeyond,   true, false, false, true, true);
                $upcomingFormatted = $formatUpcoming($scopedUpcoming, true, false, false, true, true);

                $sendEmail($cm, $beyondFormatted, $upcomingFormatted);
            } else {
                // Regional CM — sees all provinces
                $sendEmail($cm, $cmBeyond, $cmUpcoming);
            }
        }

        // ══════════════════════════════════════════════════════════════════
        // SEND: MALSU — no PCT notifications at all, skip entirely
        // ══════════════════════════════════════════════════════════════════
        $this->info('MALSU: skipped (no PCT notifications).');

        // ══════════════════════════════════════════════════════════════════
        // SEND: Province roles — 1st MC and 2nd MC only
        //       Scoped to cases currently located at their role
        // ══════════════════════════════════════════════════════════════════
        foreach ($this->provinceRoleToOffice as $role => $officeName) {

            // Scope to cases currently at this province
            $provinceBeyond = $allBeyondCases->filter(
                fn($case) => $case->documentTracking
                    && $case->documentTracking->current_role === $role
                    && $case->documentTracking->status === 'Received'
            );

            $provinceUpcoming = $allUpcomingCases->filter(
                fn($case) => $case->documentTracking
                    && $case->documentTracking->current_role === $role
                    && $case->documentTracking->status === 'Received'
            );

            // Province: only 1st MC and 2nd MC (showDocket=false, showPoPct=false, showPct96=false)
            $provinceBeyondFormatted   = $formatBeyond($provinceBeyond,   false, true, true, false, false);
            $provinceUpcomingFormatted = $formatUpcoming($provinceUpcoming, false, true, true, false, false);

            if (empty($provinceBeyondFormatted) && empty($provinceUpcomingFormatted)) {
                $this->info("Nothing to report for {$officeName}, skipping.");
                continue;
            }

            foreach (User::where('role', $role)->get() as $provinceUser) {
                $sendEmail($provinceUser, $provinceBeyondFormatted, $provinceUpcomingFormatted);
            }
        }

        $this->info("Done. Total emails sent: {$sentCount}");
        return 0;
    }
}