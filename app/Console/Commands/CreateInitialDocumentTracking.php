<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CaseFile;
use App\Models\DocumentTracking;
use Illuminate\Support\Facades\Log;

class CreateInitialDocumentTracking extends Command
{
    protected $signature = 'documents:create-initial-tracking';
    protected $description = 'Create initial document tracking for all existing active cases based on PO office';

    public function handle()
    {
        $this->info('Creating initial document tracking for existing cases...');

        // Map PO office to roles
        $poOfficeToRole = [
            'Albay' => 'province_albay',
            'Camarines Sur' => 'province_camarines_sur',
            'Camarines Norte' => 'province_camarines_norte',
            'Catanduanes' => 'province_catanduanes',
            'Masbate' => 'province_masbate',
            'Sorsogon' => 'province_sorsogon',
        ];

        // Get all active cases without tracking
        $cases = CaseFile::whereDoesntHave('documentTracking')
            ->where('overall_status', '!=', 'Completed')
            ->whereNotNull('po_office')
            ->get();

        $created = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($cases as $case) {
            $initialRole = $poOfficeToRole[$case->po_office] ?? null;

            if (!$initialRole) {
                $this->warn("⚠️  Case {$case->inspection_id}: Cannot map PO office '{$case->po_office}'");
                $skipped++;
                continue;
            }

            try {
                DocumentTracking::create([
                    'case_id' => $case->id,
                    'current_role' => $initialRole,
                    'status' => 'Received',
                    'transferred_by_user_id' => 1, // System/Admin user
                    'transferred_at' => $case->created_at ?? now(),
                    'received_by_user_id' => 1,
                    'received_at' => $case->created_at ?? now(),
                    'transfer_notes' => 'Initial document location (retroactive): ' . $case->po_office,
                ]);

                $this->info("✓ Created tracking for case {$case->inspection_id} at {$case->po_office}");
                $created++;
            } catch (\Exception $e) {
                $this->error("✗ Failed for case {$case->inspection_id}: " . $e->getMessage());
                $errors++;
            }
        }

        $this->newLine();
        $this->info("Summary:");
        $this->info("Created: {$created}");
        $this->info("Skipped: {$skipped}");
        $this->info("Errors: {$errors}");
    }
}