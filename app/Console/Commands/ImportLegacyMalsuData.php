<?php

namespace App\Console\Commands;

use App\Models\Malsu;
use App\Models\SheriffsReport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Carbon\Carbon;

class ImportLegacyMalsuData extends Command
{
    protected $signature = 'malsu:import-legacy 
                            {path : Path to the xlsx file, relative to storage/app or absolute}
                            {--dry-run : Preview results without writing to the database}
                            {--sample=2 : Print this many sample rows per sheet for eyeballing}';

    protected $description = 'One-time import of legacy MALSU register data from Excel into the malsu table';

    // Which sheets to import, and which row their real headers sit on.
    // MAS has one fewer banner row than the rest.
    private array $sheetConfig = [
        'APO' => ['header_row' => 6],
        'CN'  => ['header_row' => 6],
        'CS'  => ['header_row' => 6],
        'CAT' => ['header_row' => 6],
        'MAS' => ['header_row' => 5],
        'SOR' => ['header_row' => 6],
    ];

    // Excel "(N)" numbered columns -> malsu column name.
    // Numbers deliberately missing here (1, 6, 8-14, 19, 25) have no home in the
    // schema and are skipped, per the earlier design decision.
    private array $numberedFieldMap = [
        2  => 'case_title',
        3  => 'regional_docket_number',
        4  => 'date_compliance_order',
        5  => 'total_gls_monetary_award',
        7  => 'amount_penalty_double_indemnity',
        15 => 'date_writ_of_execution_served',
        16 => 'voluntary_compliance',
        17 => 'action_taken',
        18 => 'total_gls_monetary_satisfied',
        20 => 'complied_oshs_violations',
        21 => 'total_penalty_double_indemnity_collected',
        22 => 'total_oshs_penalty_admin_fines_collected',
        23 => 'total_workers_absorbed',
        24 => 'full_or_partial',
    ];

    // All 5 date-typed columns on malsu. Previously only the first two were
    // listed here, which meant the other three were stored as raw Excel
    // serial numbers instead of real dates. Fixed.
    private array $dateFields = [
        'date_compliance_order',
        'date_writ_of_execution_served',
        'date_indorsed_to_po',
        'po_date_received',
        'ro_received_sheriffs_return',
    ];

    private array $decimalFields = [
        'total_gls_monetary_award',
        'amount_penalty_double_indemnity',
        'total_gls_monetary_satisfied',
        'total_penalty_double_indemnity_collected',
        'total_oshs_penalty_admin_fines_collected',
    ];
    private array $integerFields = ['total_workers_absorbed'];

    private array $monthNames = [
        'january', 'february', 'march', 'april', 'may', 'june',
        'july', 'august', 'september', 'october', 'november', 'december',
    ];

    // Strict formats a date cell's full trimmed content must match exactly.
    // Anything with extra characters left over (a second date, a sentence,
    // a misspelled month) fails all of these and gets logged instead of guessed.
    private array $strictDateFormats = ['m/d/Y', 'n/j/Y', 'm/d/y', 'F d, Y', 'M d, Y', 'Y-m-d'];

    // Running totals / logs for the end-of-run summary.
    private array $summary = [];
    private array $skippedAmbiguousHeaders = [];
    private array $skippedMessyDates = [];
    private array $legendStops = [];

    public function handle(): int
    {
        $path = $this->argument('path');
        $fullPath = str_starts_with($path, '/') || str_contains($path, ':')
            ? $path
            : storage_path('app/' . ltrim($path, '/'));

        if (!file_exists($fullPath)) {
            $this->error("File not found: {$fullPath}");
            return self::FAILURE;
        }

        $isDryRun = $this->option('dry-run');
        $this->info($isDryRun ? '=== DRY RUN — nothing will be written ===' : '=== LIVE RUN ===');

        $spreadsheet = IOFactory::load($fullPath);

        if (!$isDryRun) {
            DB::beginTransaction();
        }

        try {
            foreach ($this->sheetConfig as $sheetName => $config) {
                if (!$spreadsheet->sheetNameExists($sheetName)) {
                    $this->warn("Sheet '{$sheetName}' not found in workbook, skipping.");
                    continue;
                }

                $this->importSheet($spreadsheet->getSheetByName($sheetName), $sheetName, $config, $isDryRun);
            }

            if (!$isDryRun) {
                DB::commit();
            }

            $this->printSummary();

            return self::SUCCESS;
        } catch (\Throwable $e) {
            if (!$isDryRun) {
                DB::rollBack();
            }

            $this->error('Import failed: ' . $e->getMessage());
            Log::error('MALSU legacy import failed', ['exception' => $e]);

            return self::FAILURE;
        }
    }

    private function importSheet($sheet, string $sheetName, array $config, bool $isDryRun): void
    {
        $headerRow = $config['header_row'];
        $dataStartRow = $headerRow + 2; // skip header row + male/female sub-header row
        $highestRow = $sheet->getHighestRow();
        $highestCol = Coordinate::columnIndexFromString($sheet->getHighestColumn());

        $columnMap = $this->buildColumnMap($sheet, $headerRow, $highestCol, $sheetName);

        $this->summary[$sheetName] = ['rows' => 0, 'reports' => 0];

        for ($row = $dataStartRow; $row <= $highestRow; $row++) {
            $caseTitle = null;
            foreach ($columnMap as $colIndex => $field) {
                if ($field === 'case_title') {
                    $caseTitle = trim((string) $this->rawValue($sheet, $colIndex, $row));
                    break;
                }
            }

            // Stop entirely once we hit the trailing legend/summary block —
            // everything after this is tally counts, not case data.
            // (Covers sheets where "LEGEND" lands directly in the case_title
            // column, e.g. APO/CN/CS/MAS. Sheets like CAT, where the legend
            // block's title column is blank, are already excluded by the
            // blank-case_title skip below — no special handling needed there.)
            if ($caseTitle !== null && str_contains(strtoupper($caseTitle), 'LEGEND')) {
                $this->legendStops[] = "{$sheetName}: stopped at row {$row} — {$this->summary[$sheetName]['rows']} real rows found above it";
                break;
            }

            // Skip fully blank rows (also catches shifted-column legend rows
            // like CAT's, where case_title is blank but other columns aren't)
            if (empty($caseTitle)) {
                continue;
            }

            $malsuData = ['case_id' => null];

            foreach ($columnMap as $colIndex => $field) {
                if ($field === null || str_starts_with($field, 'REPORT:')) {
                    continue;
                }
                $malsuData[$field] = $this->readTypedValue(
                    $sheet, $colIndex, $row, $field, $sheetName, $caseTitle
                );
            }

            $malsuId = null;

            if (!$isDryRun) {
                $malsu = Malsu::create($malsuData);
                $malsuId = $malsu->id;
            }

            $this->summary[$sheetName]['rows']++;

            $sampleLimit = (int) $this->option('sample');
            if ($sampleLimit > 0 && $this->summary[$sheetName]['rows'] <= $sampleLimit) {
                $this->newLine();
                $this->line("--- {$sheetName} sample row {$this->summary[$sheetName]['rows']} (sheet row {$row}) ---");
                foreach ($malsuData as $k => $v) {
                    if ($k === 'case_id') continue;
                    $this->line("  {$k}: " . ($v === null ? '(null)' : $v));
                }
            }

            // Monthly sheriff report columns for this row
            foreach ($columnMap as $colIndex => $field) {
                if ($field === null || !str_starts_with($field, 'REPORT:')) {
                    continue;
                }

                $content = trim((string) $this->rawValue($sheet, $colIndex, $row));
                if (empty($content)) {
                    continue; // exclude blank reports
                }

                $label = substr($field, 7);
                $reportMonth = $this->parseReportMonth($label);

                if ($reportMonth === null) {
                    continue; // already logged as ambiguous during column mapping
                }

                if (!$isDryRun) {
                    SheriffsReport::create([
                        'malsu_id'              => $malsuId,
                        'report_month'          => $reportMonth,
                        'report_date_submitted' => null,
                        'report_content'        => $content,
                        'submitted_by_user_id'  => null,
                    ]);
                }

                $this->summary[$sheetName]['reports']++;
            }
        }
    }

    private function buildColumnMap($sheet, int $headerRow, int $highestCol, string $sheetName): array
    {
        $map = [];

        for ($col = 1; $col <= $highestCol; $col++) {
            $raw = $this->rawValue($sheet, $col, $headerRow);
            if ($raw === null || trim((string) $raw) === '') {
                continue;
            }

            $header = trim(preg_replace('/\s+/', ' ', (string) $raw));
            $normalized = strtoupper($header);

            if (str_contains($normalized, 'SHERIFF-DESIGNATE') || str_contains($normalized, 'SHERIFF DESIGNATE')) {
                continue;
            }

            if (preg_match('/\((\d+)\)\s*$/', $header, $m)) {
                $num = (int) $m[1];
                if (isset($this->numberedFieldMap[$num])) {
                    $map[$col] = $this->numberedFieldMap[$num];
                }
                continue;
            }

            if (str_contains($normalized, 'DATE INDORSED TO PO')) {
                $map[$col] = 'date_indorsed_to_po';
                continue;
            }
            if (str_contains($normalized, 'PO DATE RECEIVE')) {
                $map[$col] = 'po_date_received';
                continue;
            }
            if (str_contains(str_replace("'", '', $normalized), 'RO RECEIVED SHERIFFS RETURN')) {
                $map[$col] = 'ro_received_sheriffs_return';
                continue;
            }

            if (str_contains($normalized, 'SHERIFF') && str_contains($normalized, 'REPORT')) {
                if ($this->parseReportMonth($header) === null) {
                    $this->skippedAmbiguousHeaders[] = "{$sheetName} col {$col}: \"{$header}\"";
                    continue;
                }
                $map[$col] = 'REPORT:' . $header;
                continue;
            }
        }

        return $map;
    }

    private function parseReportMonth(string $label): ?string
    {
        $lower = strtolower($label);

        if (!preg_match('/(20\d{2})/', $lower, $yearMatch)) {
            return null;
        }

        $foundMonths = [];
        foreach ($this->monthNames as $i => $name) {
            if (str_contains($lower, $name)) {
                $foundMonths[] = $i + 1;
            }
        }

        if (count($foundMonths) !== 1) {
            return null;
        }

        $year = (int) $yearMatch[1];
        $month = $foundMonths[0];

        return Carbon::createFromDate($year, $month, 1)->format('Y-m-d');
    }

    private function rawValue($sheet, int $colIndex, int $row)
    {
        $columnLetter = Coordinate::stringFromColumnIndex($colIndex);
        $cell = $sheet->getCell($columnLetter . $row);
        return $cell?->getValue();
    }

    private function readTypedValue($sheet, int $colIndex, int $row, string $field, string $sheetName, string $caseTitle)
    {
        $columnLetter = Coordinate::stringFromColumnIndex($colIndex);
        $cell = $sheet->getCell($columnLetter . $row);
        if ($cell === null) {
            return null;
        }

        $raw = $cell->getValue();
        if ($raw === null || trim((string) $raw) === '') {
            return null;
        }

        if (in_array($field, $this->dateFields, true)) {
            return $this->parseStrictDate($raw, $field, $sheetName, $row, $caseTitle);
        }

        if (in_array($field, $this->decimalFields, true)) {
            $clean = preg_replace('/[^0-9.\-]/', '', (string) $raw);
            return $clean === '' ? null : (float) $clean;
        }

        if (in_array($field, $this->integerFields, true)) {
            $clean = preg_replace('/[^0-9\-]/', '', (string) $raw);
            return $clean === '' ? null : (int) $clean;
        }

        return trim((string) $raw);
    }

    /**
     * Only accepts a cell as a date if its ENTIRE trimmed content is a single,
     * unambiguous date. Anything else (two dates in one cell, a misspelled
     * month, a full sentence with a date buried in it, a bare year like
     * "2024" mistaken for an Excel serial) is rejected and logged instead
     * of guessed at.
     */
    private function parseStrictDate($raw, string $field, string $sheetName, int $row, string $caseTitle): ?string
    {
        // Numeric Excel serial — only plausible if it falls in a realistic
        // date range (~1954 to ~2074). A bare "2024" or "0" or "55" fails this.
        if (is_numeric($raw)) {
            if ($raw >= 20000 && $raw <= 60000) {
                try {
                    return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($raw)->format('Y-m-d');
                } catch (\Exception $e) {
                    // fall through to logging below
                }
            }
            $this->logMessyDate($sheetName, $row, $caseTitle, $field, (string) $raw);
            return null;
        }

        $trimmed = trim(preg_replace('/\s+/', ' ', (string) $raw));

        // Explicit "no date" markers
        if (in_array(strtoupper($trimmed), ['N/A', 'NA', 'NONE', '-', 'TBD'], true)) {
            return null;
        }

        foreach ($this->strictDateFormats as $format) {
            $dt = \DateTime::createFromFormat($format, $trimmed);
            $errors = \DateTime::getLastErrors();
            $hasProblems = $errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0);

            if ($dt !== false && !$hasProblems) {
                return $dt->format('Y-m-d');
            }
        }

        // Nothing matched cleanly — log it for manual review rather than guess.
        $this->logMessyDate($sheetName, $row, $caseTitle, $field, $trimmed);
        return null;
    }

    private function logMessyDate(string $sheetName, int $row, string $caseTitle, string $field, string $raw): void
    {
        $shortTitle = strlen($caseTitle) > 35 ? substr($caseTitle, 0, 35) . '...' : $caseTitle;
        $shortRaw = strlen($raw) > 60 ? substr($raw, 0, 60) . '...' : $raw;
        $this->skippedMessyDates[] = "{$sheetName} row {$row} ({$shortTitle}) [{$field}]: \"{$shortRaw}\"";
    }

    private function printSummary(): void
    {
        $this->newLine();
        $this->info('=== IMPORT SUMMARY ===');

        $totalRows = 0;
        $totalReports = 0;

        foreach ($this->summary as $sheetName => $counts) {
            $this->line("{$sheetName}: {$counts['rows']} malsu rows, {$counts['reports']} sheriff reports");
            $totalRows += $counts['rows'];
            $totalReports += $counts['reports'];
        }

        $this->line("TOTAL: {$totalRows} malsu rows, {$totalReports} sheriff reports");

        if (!empty($this->legendStops)) {
            $this->newLine();
            $this->info('Legend/summary blocks excluded:');
            foreach ($this->legendStops as $item) {
                $this->line("  - {$item}");
            }
        }

        if (!empty($this->skippedAmbiguousHeaders)) {
            $this->newLine();
            $this->warn('Skipped ambiguous month headers (no year or combined months) — enter these manually if needed:');
            foreach ($this->skippedAmbiguousHeaders as $item) {
                $this->line("  - {$item}");
            }
        }

        if (!empty($this->skippedMessyDates)) {
            $this->newLine();
            $this->warn('Skipped messy date cells (' . count($this->skippedMessyDates) . ') — needs manual entry:');
            foreach ($this->skippedMessyDates as $item) {
                $this->line("  - {$item}");
            }
        }
    }
}