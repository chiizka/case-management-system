<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CaseFile;
use Illuminate\Support\Facades\DB;

class RecomputeAllCases extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cases:recompute-all 
                            {--dry-run : Show what would be updated without actually updating}
                            {--batch=100 : Number of cases to process per batch}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recompute all auto-calculated fields for all existing cases';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $batchSize = (int) $this->option('batch');
        
        if ($dryRun) {
            $this->warn('🔍 DRY RUN MODE - No changes will be saved to database');
        }
        
        $this->info('Starting case recomputation...');
        $this->newLine();
        
        // Get total count
        $totalCases = CaseFile::count();
        $this->info("Total cases to process: {$totalCases}");
        $this->newLine();
        
        $bar = $this->output->createProgressBar($totalCases);
        $bar->start();
        
        $updated = 0;
        $errors = 0;
        $skipped = 0;
        
        // Process in batches to avoid memory issues
        CaseFile::chunk($batchSize, function ($cases) use (&$updated, &$errors, &$skipped, $bar, $dryRun) {
            foreach ($cases as $case) {
                try {
                    // Store original values for comparison
                    $original = [
                        'lapse_20_day_period' => $case->lapse_20_day_period,
                        'pct_for_docketing' => $case->pct_for_docketing,
                        'aging_docket' => $case->aging_docket,
                        'status_docket' => $case->status_docket,
                        'first_mc_pct' => $case->first_mc_pct,
                        'status_1st_mc' => $case->status_1st_mc,
                        'second_last_mc_pct' => $case->second_last_mc_pct,
                        'status_2nd_mc' => $case->status_2nd_mc,
                        'po_pct' => $case->po_pct,
                        'aging_po_pct' => $case->aging_po_pct,
                        'status_po_pct' => $case->status_po_pct,
                    ];
                    
                    // Compute new values
                    $case->computeFields();
                    
                    // Check if anything changed
                    $hasChanges = false;
                    foreach ($original as $field => $oldValue) {
                        if ($case->$field != $oldValue) {
                            $hasChanges = true;
                            break;
                        }
                    }
                    
                    if ($hasChanges) {
                        if (!$dryRun) {
                            // Save without triggering events (to avoid infinite loop)
                            $case->saveQuietly();
                        }
                        $updated++;
                    } else {
                        $skipped++;
                    }
                    
                } catch (\Exception $e) {
                    $errors++;
                    \Log::error("Error recomputing case {$case->id}: " . $e->getMessage());
                }
                
                $bar->advance();
            }
        });
        
        $bar->finish();
        $this->newLine(2);
        
        // Display summary
        $this->info('════════════════════════════════════════════════════════');
        $this->info('                    SUMMARY                             ');
        $this->info('════════════════════════════════════════════════════════');
        $this->line("Total cases processed: <info>{$totalCases}</info>");
        $this->line("Cases updated: <info>{$updated}</info>");
        $this->line("Cases skipped (no changes): <comment>{$skipped}</comment>");
        if ($errors > 0) {
            $this->line("Errors encountered: <error>{$errors}</error>");
        } else {
            $this->line("Errors encountered: <info>0</info>");
        }
        $this->info('════════════════════════════════════════════════════════');
        
        if ($dryRun) {
            $this->newLine();
            $this->warn('🔍 This was a DRY RUN - No changes were saved');
            $this->info('Run without --dry-run to apply the changes');
        } else {
            $this->newLine();
            $this->info('✅ Recomputation complete!');
        }
        
        return 0;
    }
}