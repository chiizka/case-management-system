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
    protected $description = 'Send email notifications to users about Beyond deadline cases (runs daily at 7AM)';

    // Maps province role → po_office name (matches CaseFile.po_office values)
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
        $sentCount  = 0;

        // ── 1. Fetch ALL active Beyond cases ─────────────────────────────────
        $allBeyondCases = CaseFile::whereNotIn('overall_status', ['Completed', 'Disposed', 'Appealed'])
            ->where(function ($q) {
                $q->where('status_docket',  'Beyond')
                ->orWhere('status_1st_mc', 'Beyond')
                ->orWhere('status_2nd_mc', 'Beyond')
                ->orWhere('status_po_pct', 'Beyond')
                ->orWhere('status_pct',    'Beyond');
            })
            ->whereHas('documentTracking', function ($q) {
                $q->where('status', 'Received'); // ✅ only cases already received
            })
            ->get([
                'id', 'case_no', 'inspection_id',
                'establishment_name', 'po_office',
                'status_docket', 'status_1st_mc',
                'status_2nd_mc', 'status_po_pct', 'status_pct',
            ]);

        if ($allBeyondCases->isEmpty()) {
            $this->info('No Beyond cases found. No emails sent.');
            return 0;
        }

        $fieldLabels = [
            'status_docket'  => 'Docket',
            'status_1st_mc'  => '1st MC',
            'status_2nd_mc'  => '2nd MC',
            'status_po_pct'  => 'PO PCT',
            'status_pct'     => 'PCT (96 days)',
        ];

        // ── 2. Format cases into mail-ready arrays ────────────────────────────
        $formatCases = function ($casesCollection) use ($fieldLabels) {
            return $casesCollection->map(function ($case) use ($fieldLabels) {
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

        // ── 3. Send to ADMIN users — all Beyond cases ─────────────────────────
        $admins = User::where('role', User::ROLE_ADMIN)->get();
        $allFormatted = $formatCases($allBeyondCases);

        foreach ($admins as $admin) {
            try {
                Mail::to($admin->email)
                    ->send(new BeyondCaseNotification(
                        $admin->fname . ' ' . $admin->lname,
                        $allFormatted,
                        $reportDate
                    ));
                $sentCount++;
                $this->info("✓ Sent to admin: {$admin->email}");
            } catch (\Exception $e) {
                Log::error("Failed to send Beyond notification to admin {$admin->email}: " . $e->getMessage());
                $this->error("✗ Failed: {$admin->email} — " . $e->getMessage());
            }
        }

        // ── 4. Send to CASE MANAGEMENT users — all Beyond cases ───────────────
        $caseManagers = User::where('role', User::ROLE_CASE_MANAGEMENT)->get();

        foreach ($caseManagers as $cm) {
            try {
                Mail::to($cm->email)
                    ->send(new BeyondCaseNotification(
                        $cm->fname . ' ' . $cm->lname,
                        $allFormatted,
                        $reportDate
                    ));
                $sentCount++;
                $this->info("✓ Sent to case management: {$cm->email}");
            } catch (\Exception $e) {
                Log::error("Failed to send Beyond notification to case management {$cm->email}: " . $e->getMessage());
                $this->error("✗ Failed: {$cm->email} — " . $e->getMessage());
            }
        }

        // ── 5. Send to PROVINCE users — only their own province's Beyond cases ─
        foreach ($this->provinceRoleToOffice as $role => $officeName) {

            // Filter Beyond cases that belong to this province
            $provinceCases = $allBeyondCases->where('po_office', $officeName);

            if ($provinceCases->isEmpty()) {
                $this->info("No Beyond cases for {$officeName}, skipping.");
                continue;
            }

            $provinceFormatted = $formatCases($provinceCases);

            // Get all users in this province role
            $provinceUsers = User::where('role', $role)->get();

            foreach ($provinceUsers as $provinceUser) {
                try {
                    Mail::to($provinceUser->email)
                        ->send(new BeyondCaseNotification(
                            $provinceUser->fname . ' ' . $provinceUser->lname,
                            $provinceFormatted,
                            $reportDate
                        ));
                    $sentCount++;
                    $this->info("✓ Sent to {$officeName} user: {$provinceUser->email}");
                } catch (\Exception $e) {
                    Log::error("Failed to send Beyond notification to {$provinceUser->email}: " . $e->getMessage());
                    $this->error("✗ Failed: {$provinceUser->email} — " . $e->getMessage());
                }
            }
        }

        $this->info("Done. Total emails sent: {$sentCount}");
        return 0;
    }
}