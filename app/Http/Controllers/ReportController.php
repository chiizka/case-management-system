<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CaseFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Carbon\Carbon;

class ReportController extends Controller
{
    private const MONTH_NAMES = [
        1 => 'JANUARY',   2 => 'FEBRUARY', 3 => 'MARCH',    4 => 'APRIL',
        5 => 'MAY',        6 => 'JUNE',     7 => 'JULY',     8 => 'AUGUST',
        9 => 'SEPTEMBER', 10 => 'OCTOBER', 11 => 'NOVEMBER',12 => 'DECEMBER',
    ];

    // Jan = column C (index 3), Feb = D (4) ... Dec = N (14)
    private function monthCol(int $month): string
    {
        return Coordinate::stringFromColumnIndex($month + 2);
    }

    public function generateForm1(Request $request)
    {
        $request->validate([
            'year'   => 'required|integer|min:2020|max:2099',
            'month'  => 'required|integer|min:1|max:12',
            'office' => 'nullable|string',
        ]);

        $year   = (int) $request->year;
        $month  = (int) $request->month;
        $office = $request->office ?: null;

        // ── Base query ────────────────────────────────────────────────────────────
        $base = fn() => CaseFile::query()
            ->when($office, fn($q) => $q->where('po_office', $office));

        $startOfYear = Carbon::create($year, 1, 1)->startOfYear();

        // ── Carry-over cases ──────────────────────────────────────────────────────
        // Cases created BEFORE this year that are still active (not yet archived).
        // Active = NOT IN (Completed, Disposed, Appealed) — mirrors CasesController::index()
        $carryOver = (clone $base())
            ->whereDate('created_at', '<', $startOfYear)
            ->whereNotIn('overall_status', ['Completed', 'Disposed', 'Appealed'])
            ->count();

        // ── New cases per month ───────────────────────────────────────────────────
        $newByMonth = [];
        for ($m = 1; $m <= 12; $m++) {
            $s = Carbon::create($year, $m, 1)->startOfMonth();
            $e = Carbon::create($year, $m, 1)->endOfMonth();
            $newByMonth[$m] = (clone $base())
                ->whereDate('created_at', '>=', $s)
                ->whereDate('created_at', '<=', $e)
                ->count();
        }

        // ── Disposed cases per month ──────────────────────────────────────────────
        // Disposed = archived cases: overall_status IN (Completed, Disposed, Appealed)
        // Date field: date_of_order_actual if set, otherwise fall back to updated_at
        //   (date_of_order_actual is null on all current records — cases are archived
        //    via moveToNextStage() which only sets overall_status, not the date field)
        // PCT split: status_pct is null on all current records, so all go to Within PCT.
        //   When staff start filling date_of_order_actual / status_pct, this will
        //   automatically start using those values instead.
        $disposedWithin = [];
        $disposedBeyond = [];
        for ($m = 1; $m <= 12; $m++) {
            $s = Carbon::create($year, $m, 1)->startOfMonth();
            $e = Carbon::create($year, $m, 1)->endOfMonth();

            // Use date_of_order_actual when available, fall back to updated_at
            $disposedWithin[$m] = (clone $base())
                ->whereIn('overall_status', ['Completed', 'Disposed', 'Appealed'])
                ->where(fn($q) => $q
                    ->where(fn($q2) => $q2
                        ->whereNotNull('date_of_order_actual')
                        ->whereDate('date_of_order_actual', '>=', $s)
                        ->whereDate('date_of_order_actual', '<=', $e)
                    )
                    ->orWhere(fn($q2) => $q2
                        ->whereNull('date_of_order_actual')
                        ->whereDate('updated_at', '>=', $s)
                        ->whereDate('updated_at', '<=', $e)
                    )
                )
                ->where(fn($q) => $q->where('status_pct', 'Within PCT')->orWhereNull('status_pct'))
                ->count();

            $disposedBeyond[$m] = (clone $base())
                ->whereIn('overall_status', ['Completed', 'Disposed', 'Appealed'])
                ->where(fn($q) => $q
                    ->where(fn($q2) => $q2
                        ->whereNotNull('date_of_order_actual')
                        ->whereDate('date_of_order_actual', '>=', $s)
                        ->whereDate('date_of_order_actual', '<=', $e)
                    )
                    ->orWhere(fn($q2) => $q2
                        ->whereNull('date_of_order_actual')
                        ->whereDate('updated_at', '>=', $s)
                        ->whereDate('updated_at', '<=', $e)
                    )
                )
                ->where('status_pct', 'Beyond PCT')
                ->count();
        }

        // ── Monetary & workers for selected month ─────────────────────────────────
        $selStart = Carbon::create($year, $month, 1)->startOfMonth();
        $selEnd   = Carbon::create($year, $month, 1)->endOfMonth();

        // Same date fallback: date_of_order_actual if set, else updated_at
        $dateFilter = fn($q) => $q->where(fn($q2) => $q2
            ->where(fn($q3) => $q3
                ->whereNotNull('date_of_order_actual')
                ->whereDate('date_of_order_actual', '>=', $selStart)
                ->whereDate('date_of_order_actual', '<=', $selEnd)
            )
            ->orWhere(fn($q3) => $q3
                ->whereNull('date_of_order_actual')
                ->whereDate('updated_at', '>=', $selStart)
                ->whereDate('updated_at', '<=', $selEnd)
            )
        );

        $monetary = (clone $base())
            ->whereIn('overall_status', ['Completed', 'Disposed', 'Appealed'])
            ->where($dateFilter)
            ->sum('compliance_order_monetary_award') ?? 0;

        $workers = (clone $base())
            ->whereIn('overall_status', ['Completed', 'Disposed', 'Appealed'])
            ->where($dateFilter)
            ->selectRaw('SUM(COALESCE(affected_male,0) + COALESCE(affected_female,0)) as total')
            ->value('total') ?? 0;

        // ── Build spreadsheet ─────────────────────────────────────────────────────
        $spreadsheet = new Spreadsheet();
        $ws = $spreadsheet->getActiveSheet();
        $ws->setTitle('Speed Report F1');

        // ── Column widths (matching reference file exactly) ───────────────────────
        $ws->getColumnDimension('A')->setWidth(43.57);
        $ws->getColumnDimension('B')->setWidth(19.71);
        $ws->getColumnDimension('C')->setWidth(18.57);
        $ws->getColumnDimension('D')->setWidth(16.71);
        $ws->getColumnDimension('E')->setWidth(18.43);
        $ws->getColumnDimension('F')->setWidth(17.71);
        $ws->getColumnDimension('G')->setWidth(19.14);
        $ws->getColumnDimension('H')->setWidth(18.0);
        $ws->getColumnDimension('I')->setWidth(17.86);
        $ws->getColumnDimension('J')->setWidth(16.29);
        $ws->getColumnDimension('K')->setWidth(15.71);
        $ws->getColumnDimension('L')->setWidth(15.71);
        $ws->getColumnDimension('M')->setWidth(16.43);
        $ws->getColumnDimension('N')->setWidth(16.0);

        // ── Style helpers (no colors — plain black/white) ─────────────────────────
        $font = function(string $range, bool $bold = false, int $size = 12) use ($ws) {
            $f = $ws->getStyle($range)->getFont();
            $f->setName('Calibri')->setSize($size)->setBold($bold);
        };

        $align = function(string $range, string $h = 'left', string $v = 'center') use ($ws) {
            $ws->getStyle($range)->getAlignment()
               ->setHorizontal($h)->setVertical($v)->setWrapText(true);
        };

        $thinBorder = function(string $range) use ($ws) {
            $ws->getStyle($range)->getBorders()
               ->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        };

        $medBorder = function(string $range) use ($ws) {
            $bs = $ws->getStyle($range)->getBorders();
            $bs->getTop()->setBorderStyle(Border::BORDER_MEDIUM);
            $bs->getBottom()->setBorderStyle(Border::BORDER_MEDIUM);
            $bs->getLeft()->setBorderStyle(Border::BORDER_MEDIUM);
            $bs->getRight()->setBorderStyle(Border::BORDER_MEDIUM);
        };

        $v = fn(string $cell, $value) => $ws->setCellValue($cell, $value);

        // ── Header block (rows 1–8) — matching reference exactly ─────────────────
        $endDay    = Carbon::create($year, $month, 1)->endOfMonth()->day;
        $monthName = self::MONTH_NAMES[$month];

        // Row heights
        foreach ([1=>21.75, 2=>21.75, 3=>21.75, 4=>21.75, 5=>21.75,
                  6=>19.5, 7=>19.5, 8=>19.5, 9=>15.75] as $r => $h) {
            $ws->getRowDimension($r)->setRowHeight($h);
        }

        // Row 1 — Title (bold, size 12, no merge needed — reference leaves un-merged)
        $v('A1', 'Project Speed Form No. 1');
        $font('A1', true, 12);

        // Rows 3–6 merged across all 14 columns (A:N) — matching reference merges
        foreach ([3,4,5,6] as $r) {
            $ws->mergeCells("A{$r}:N{$r}");
        }

        $v('A3', 'PROJECT SPEED 7');
        $font('A3', true, 12);

        $v('A4', 'REPORT ON CASES HANDLED BY MONTH:  PHILIPPINES');
        $font('A4', false, 12);

        $v('A5', "as of {$endDay} {$monthName} {$year}");
        $font('A5', false, 12);

        $v('A6', 'Name of Office/Agency: DOLE-5');
        $font('A6', false, 12);

        $v('A7', 'Type of Case: Labor Standard Case');
        $font('A7', false, 12);

        $v('A8', 'Process Cycle Time: 96 days');
        $font('A8', false, 12);

        // ── Column headers (row 10) — reference uses row 10, not row 9 ───────────
        $ws->getRowDimension(10)->setRowHeight(31.5);

        $v('A10', 'INDICATORS');
        $v('B10', 'TOTAL');
        for ($m = 1; $m <= 12; $m++) {
            $v($this->monthCol($m) . '10', self::MONTH_NAMES[$m]);
        }

        $font('A10:N10', true, 12);
        $align('A10:N10', 'center', 'center');

        // Medium border on outer edge of header, thin inside
        $ws->getStyle('A10:N10')->getBorders()->getAllBorders()
           ->setBorderStyle(Border::BORDER_THIN);
        $ws->getStyle('A10')->getBorders()->getTop()
           ->setBorderStyle(Border::BORDER_MEDIUM);
        $ws->getStyle('A10:N10')->getBorders()->getTop()
           ->setBorderStyle(Border::BORDER_MEDIUM);
        $ws->getStyle('A10')->getBorders()->getLeft()
           ->setBorderStyle(Border::BORDER_MEDIUM);
        $ws->getStyle('N10')->getBorders()->getRight()
           ->setBorderStyle(Border::BORDER_MEDIUM);

        // ── Row definitions — labels, bold, row heights ───────────────────────────
        // All rows use size 12, no background colors (plain white)
        // Row numbers match reference file exactly (rows 12,14,16,17,18,20,22,24...)
        $rowDefs = [
            12 => ['Carry-over cases from previous year', false, 15.75],
            14 => ['NEW CASES',                           false, 15.75],
            16 => ['CASES HANDLED',                       true,  15.75],
            17 => ['        Within PCT',                  false, 15.75],
            18 => ['        Beyond PCT',                  false, 15.75],
            20 => ['TOTAL CASES HANDLED',                 true,  15.75],
            22 => ['NET CASES HANDLED',                   false, 15.75],
            24 => ['DISPOSED CASES',                      true,  15.75],
            25 => ['        Within PCT',                  false, 15.75],
            26 => ['        Beyond PCT',                  false, 15.75],
            28 => ['TOTAL DISPOSED CASES',                true,  15.75],
            30 => ['DISPOSITION RATE',                    true,  15.75],
            32 => ['PENDING CASES',                       true,  15.75],
            33 => ['        Within PCT',                  false, 15.75],
            34 => ['        Beyond PCT',                  false, 15.75],
            36 => ['TOTAL PENDING CASES',                 true,  15.75],
            38 => ['MONETARY BENEFITS',                   false, 15.75],
            39 => ['WORKERS BENEFITTED',                  false, 23.25],
        ];

        foreach ($rowDefs as $row => [$label, $bold, $height]) {
            $v("A{$row}", $label);
            $font("A{$row}:N{$row}", $bold, 12);
            $ws->getRowDimension($row)->setRowHeight($height);
        }

        // ── Pre-compute per-month values in PHP ──────────────────────────────────
        // Cases Handled per month = pending from previous month + new cases this month.
        // This is a rolling calculation — Excel formulas can't look backwards across
        // month columns, so we compute it here and write plain numbers.
        //
        // Formula:
        //   pendingStart[1] = carryOver
        //   handledWithin[m] = pendingStart[m] + newByMonth[m]   (within PCT)
        //   handledBeyond[m] = 0                                  (beyond PCT — no data yet)
        //   pendingEnd[m]    = handledWithin[m] - disposedWithin[m] - disposedBeyond[m]
        //   pendingStart[m+1] = pendingEnd[m]

        $handledWithin = [];
        $handledBeyond = [];
        $pendingWithin = [];
        $pendingBeyond = [];
        $pendingRolling = $carryOver; // carry-over feeds into January

        for ($m = 1; $m <= 12; $m++) {
            if ($m > $month) {
                // Future months: all zeros
                $handledWithin[$m] = 0;
                $handledBeyond[$m] = 0;
                $pendingWithin[$m] = 0;
                $pendingBeyond[$m] = 0;
            } else {
                $handledWithin[$m] = $pendingRolling + $newByMonth[$m];
                $handledBeyond[$m] = 0;
                $pendingWithin[$m] = max(0, $handledWithin[$m] - $disposedWithin[$m] - $disposedBeyond[$m]);
                $pendingBeyond[$m] = 0;
                // This month's pending becomes next month's opening balance
                $pendingRolling = $pendingWithin[$m];
            }
        }

        // Row 12 – Carry-over (Jan only; future months blank/null like reference)
        $v('B12', $carryOver);
        $v('C12', $carryOver); // Jan column
        // Feb–Dec left blank (matching reference which leaves future months null)

        // Row 14 – New cases
        for ($m = 1; $m <= 12; $m++) {
            $v($this->monthCol($m) . '14', $m <= $month ? $newByMonth[$m] : null);
        }
        $v('B14', "=SUM(C14:N14)");

        // Row 17 – Cases handled within PCT (rolling: prev pending + new)
        for ($m = 1; $m <= 12; $m++) {
            $v($this->monthCol($m) . '17', $m <= $month ? $handledWithin[$m] : null);
        }
        $v('B17', "=B12+B14");

        // Row 18 – Cases handled beyond PCT
        $v('B18', 0);
        $v('C18', 0);

        // Row 16 – Cases handled total (SUM of 17+18 per month)
        for ($m = 1; $m <= 12; $m++) {
            $c = $this->monthCol($m);
            $v($c . '16', $m <= $month ? "=SUM({$c}17:{$c}18)" : 0);
        }
        $v('B16', "=SUM(B17:B18)");

        // Row 20 – Total cases handled
        for ($m = 1; $m <= 12; $m++) {
            $c = $this->monthCol($m);
            $v($c . '20', $m <= $month ? "={$c}12+{$c}14" : 0);
        }
        $v('B20', "=B12+B14");

        // Row 25 – Disposed within PCT
        for ($m = 1; $m <= 12; $m++) {
            $v($this->monthCol($m) . '25', $m <= $month ? $disposedWithin[$m] : null);
        }
        $v('B25', "=SUM(C25:N25)");

        // Row 26 – Disposed beyond PCT
        $v('B26', "=SUM(C26:N26)");
        $v('C26', 0);

        // Row 24 – Disposed cases total
        for ($m = 1; $m <= 12; $m++) {
            $c = $this->monthCol($m);
            $v($c . '24', $m <= $month ? "=SUM({$c}25:{$c}26)" : 0);
        }
        $v('B24', "=SUM(B25:B26)");

        // Row 28 – Total disposed cases (mirrors row 24 with SUM formula)
        $v('B28', "=SUM(C28:N28)");
        for ($m = 1; $m <= 12; $m++) {
            $c = $this->monthCol($m);
            $v($c . '28', $m <= $month ? "={$c}24" : null);
        }

        // Row 30 – Disposition rate = disposed / net cases handled
        // Reference uses =B24/B22 (disposed / net), not disposed / total handled
        for ($m = 1; $m <= 12; $m++) {
            $c = $this->monthCol($m);
            if ($m <= $month) {
                $v($c . '30', "=IF({$c}22=0,0,{$c}24/{$c}22)");
                $ws->getStyle($c . '30')->getNumberFormat()
                   ->setFormatCode(NumberFormat::FORMAT_PERCENTAGE);
            }
        }
        $v('B30', '=IF(B22=0,0,B24/B22)');
        $ws->getStyle('B30')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE);

        // Row 33 – Pending within PCT (rolling)
        for ($m = 1; $m <= 12; $m++) {
            $v($this->monthCol($m) . '33', $m <= $month ? $pendingWithin[$m] : null);
        }
        $v('B33', $pendingWithin[$month] ?? 0); // Total = end-of-selected-month pending

        // Row 34 – Pending beyond PCT
        $v('B34', 0);
        $v('C34', 0);

        // Row 32 – Pending cases total
        for ($m = 1; $m <= 12; $m++) {
            $c = $this->monthCol($m);
            $v($c . '32', $m <= $month ? "=SUM({$c}33:{$c}34)" : 0);
        }
        $v('B32', $carryOver); // Reference hardcodes carry-over here for TOTAL

        // Row 36 – Total pending (reference uses =C20-C24 formula per month)
        for ($m = 1; $m <= 12; $m++) {
            $c = $this->monthCol($m);
            $v($c . '36', $m <= $month ? "={$c}20-{$c}24" : 0);
        }
        $v('B36', $carryOver); // Reference hardcodes carry-over for TOTAL

        // Row 22 – Net cases handled = total handled - total pending
        for ($m = 1; $m <= 12; $m++) {
            $c = $this->monthCol($m);
            $v($c . '22', $m <= $month ? "={$c}20-{$c}33" : 0);
        }
        $v('B22', "=B20-B33");

        // Row 38 – Monetary benefits (selected month; reference shows '-' when zero)
        for ($m = 1; $m <= 12; $m++) {
            $c = $this->monthCol($m);
            if ($m === $month) {
                $val = round((float)$monetary, 2);
                $v($c . '38', $val > 0 ? $val : '-');
            }
            // Other months left blank like reference
        }
        $v('B38', "=SUM(C38:N38)");
        $ws->getStyle('B38')->getNumberFormat()
           ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2);

        // Row 39 – Workers benefitted
        for ($m = 1; $m <= 12; $m++) {
            $c = $this->monthCol($m);
            if ($m === $month) {
                $v($c . '39', (int)$workers > 0 ? (int)$workers : '0');
            }
        }
        $v('B39', "=SUM(C39:N39)");

        // ── Alignment for data area ───────────────────────────────────────────────
        $dataRows = [12,14,16,17,18,20,22,24,25,26,28,30,32,33,34,36,38,39];
        foreach ($dataRows as $r) {
            $align("B{$r}:N{$r}", 'center', 'center');
        }
        $align('A10:A39', 'left', 'center');
        $align('A1:A8', 'left', 'center');

        // ── Borders for entire data table (A10:N39) ───────────────────────────────
        $thinBorder('A10:N39');
        // Reinforce outer border as medium
        $ws->getStyle('A10:N10')->getBorders()->getTop()->setBorderStyle(Border::BORDER_MEDIUM);
        $ws->getStyle('A39:N39')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM);
        $ws->getStyle('A10:A39')->getBorders()->getLeft()->setBorderStyle(Border::BORDER_MEDIUM);
        $ws->getStyle('N10:N39')->getBorders()->getRight()->setBorderStyle(Border::BORDER_MEDIUM);

        // ── Signature block (rows 42–46, matching reference) ─────────────────────
        $ws->getRowDimension(40)->setRowHeight(8);
        foreach ([42,43,44,45,46] as $r) {
            $ws->getRowDimension($r)->setRowHeight(15);
        }

        // Labels row 42
        $v('A42', 'Prepared by:');
        $v('B42', 'Reviewed by:');
        $v('D42', 'Noted by:');
        $v('G42', 'Approved by:');
        $font('A42:N42', false, 12);

        // Names row 45
        $v('A45', 'SEAN LEVI B. ALPAJARO');
        $v('B45', 'ENGR. ROBERTO L. ARANAS');
        $v('D45', 'CHING B. BANANIA');
        $v('G45', 'IMELDA F. GATINAO');
        $font('A45:N45', true, 12);

        // Titles row 46
        $v('A46', 'LEO III');
        $v('B46', 'Supervising LEO');
        $v('D46', 'TSSD Chief');
        $v('G46', 'Regional Director');
        $font('A46:N46', false, 12);

        // ── No freeze pane ────────────────────────────────────────────────────────
        // (removed — header rows should scroll normally)

        // ── Stream download ───────────────────────────────────────────────────────
        $officePart = $office ? str_replace(' ', '_', strtoupper($office)) . '_' : 'RO_5_';
        $filename   = "Project_Speed_Form_No__1-{$monthName}_{$year}.xlsx";

        $writer = new Xlsx($spreadsheet);

        // ob_clean() discards any whitespace PHP may have buffered before this
        // response (e.g. blank lines before <?php in any loaded file).
        // Without it, Excel sees a corrupt file because the ZIP header is offset.
        return response()->streamDownload(function () use ($writer) {
            if (ob_get_level() > 0) {
                ob_clean();
            }
            $writer->save('php://output');
        }, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control'       => 'max-age=0',
            'Content-Disposition' => 'attachment',
        ]);
    }

        // ══════════════════════════════════════════════════════════════════════════
    //  FORM NO. 3 — Execution and Satisfaction of Decisions/Orders
    //  Layout matches: Project-Speed-Form-No_-3-JANUARY_2026.xlsx exactly.
    //  Col A=narrow(4.29), B=wide label(47), C=TOTAL, D=Jan…O=Dec. No colors.
    // ══════════════════════════════════════════════════════════════════════════
    public function generateForm3(Request $request)
    {
        $request->validate([
            'year'  => 'required|integer|min:2020|max:2099',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $year  = (int) $request->year;
        $month = (int) $request->month;

        // D=Jan(4), E=Feb(5) … O=Dec(15)
        $mCol = fn(int $m): string => Coordinate::stringFromColumnIndex($m + 3);

        // ── DB queries ────────────────────────────────────────────────────────
        $newDecisions = [];
        for ($m = 1; $m <= 12; $m++) {
            $s = Carbon::create($year, $m, 1)->startOfMonth();
            $e = Carbon::create($year, $m, 1)->endOfMonth();
            $newDecisions[$m] = $m > $month ? 0
                : CaseFile::whereIn('overall_status', ['Completed','Disposed','Appealed'])
                    ->where(fn($q) => $q
                        ->where(fn($q2) => $q2->whereNotNull('date_of_order_actual')
                            ->whereDate('date_of_order_actual','>=',$s)
                            ->whereDate('date_of_order_actual','<=',$e))
                        ->orWhere(fn($q2) => $q2->whereNull('date_of_order_actual')
                            ->whereDate('updated_at','>=',$s)
                            ->whereDate('updated_at','<=',$e))
                    )->count();
        }

        $selStart = Carbon::create($year, $month, 1)->startOfMonth();
        $selEnd   = Carbon::create($year, $month, 1)->endOfMonth();
        $monetary = CaseFile::whereIn('overall_status', ['Completed','Disposed','Appealed'])
            ->where(fn($q) => $q
                ->where(fn($q2) => $q2->whereNotNull('date_of_order_actual')
                    ->whereDate('date_of_order_actual','>=',$selStart)
                    ->whereDate('date_of_order_actual','<=',$selEnd))
                ->orWhere(fn($q2) => $q2->whereNull('date_of_order_actual')
                    ->whereDate('updated_at','>=',$selStart)
                    ->whereDate('updated_at','<=',$selEnd))
            )->sum('compliance_order_monetary_award') ?? 0;

        // ── Build spreadsheet ─────────────────────────────────────────────────
        $spreadsheet = new Spreadsheet();
        $ws = $spreadsheet->getActiveSheet();
        $ws->setTitle('STATISTICAL TABLE Part 1');

        // ── Column widths (exact from reference) ──────────────────────────────
        $ws->getColumnDimension('A')->setWidth(4.29);
        $ws->getColumnDimension('B')->setWidth(47.0);
        $ws->getColumnDimension('C')->setWidth(16.0);
        $ws->getColumnDimension('D')->setWidth(13.0);
        $ws->getColumnDimension('E')->setWidth(14.14);
        $ws->getColumnDimension('F')->setWidth(13.0);
        $ws->getColumnDimension('G')->setWidth(14.29);
        $ws->getColumnDimension('H')->setWidth(13.0);
        $ws->getColumnDimension('I')->setWidth(20.29);
        $ws->getColumnDimension('J')->setWidth(14.57);
        $ws->getColumnDimension('K')->setWidth(13.0);
        $ws->getColumnDimension('L')->setWidth(14.86);
        $ws->getColumnDimension('M')->setWidth(16.0);
        $ws->getColumnDimension('N')->setWidth(17.57);
        $ws->getColumnDimension('O')->setWidth(18.0);

        // ── Style helpers — NO fills/colors ───────────────────────────────────
        $font = fn(string $r, bool $b=false, int $s=12) =>
            $ws->getStyle($r)->getFont()->setName('Calibri')->setSize($s)->setBold($b);
        $al = fn(string $r, string $h='left', string $v='center') =>
            $ws->getStyle($r)->getAlignment()->setHorizontal($h)->setVertical($v)->setWrapText(true);
        $bd = fn(string $r) =>
            $ws->getStyle($r)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $mg = fn(string $r) => $ws->mergeCells($r);
        $v  = fn(string $cell, $val) => $ws->setCellValue($cell, $val);
        $rh = fn(int $r, float $h) => $ws->getRowDimension($r)->setRowHeight($h);

        $monthName = self::MONTH_NAMES[$month];
        $monthTitle = ucfirst(strtolower($monthName));

        // ── Rows 1–8: Header (exact heights from reference) ───────────────────
        $rh(1, 18.75); $mg('A1:B1');
        $v('A1', 'Project Speed Form No. 3'); $font('A1', true, 11);

        $rh(2, 18.75); // row 2 empty (date placeholder in reference)

        $rh(3, 18.75); $mg('A3:O3');
        $v('A3', 'PROJECT SPEED 7'); $font('A3', true, 12);

        $rh(4, 18.75); $mg('A4:O4');
        $v('A4', 'REPORT ON EXECUTION AND SATISFACTION OF DECISIONS/ORDERS BY MONTH:  PHILIPPINES');
        $font('A4', false, 12);

        $rh(5, 18.75); $mg('A5:O5');
        $v('A5', 'ORIGINAL CASES '); $font('A5', false, 12);

        $rh(6, 15.0); $mg('A6:O6');
        $v('A6', 'Name of Office/Agency:  DOLE Region V'); $font('A6', false, 12);

        $rh(7, 16.5); $mg('A7:O7');
        $v('A7', "As of {$monthTitle} {$year}"); $font('A7', false, 12);

        $rh(8, 10.5); // blank gap row

        // ── Row 9: Part header ────────────────────────────────────────────────
        $rh(9, 21.75); $mg('A9:O9');
        $v('A9', 'Part 1:  BEFORE THE ISSUANCE OF WRIT OF EXECUTION');
        $font('A9', true, 14); $al('A9:O9', 'left', 'center'); $bd('A9:O9');

        // ── Row 10: Column headers ────────────────────────────────────────────
        $rh(10, 28.5); $mg('A10:B10');
        $v('A10', 'INDICATORS'); $v('C10', 'TOTAL');
        $mNames = ['January','February','March','April','May','June',
                   'July','August','September','October','November','December'];
        foreach ($mNames as $i => $mn) { $v($mCol($i+1).'10', $mn); }
        $font('A10:O10', true, 12); $al('A10:O10','center','center'); $bd('A10:O10');

        // ── Helper: standard data row (A+B merged label, C=SUM(D:O), D–O values)
        $dataRow = function(int $row, array $vals, float $height) use ($ws,$v,$mCol,$al,$bd,$rh) {
            $rh($row, $height);
            $v('C'.$row, '=SUM(D'.$row.':O'.$row.')');
            for ($m=1; $m<=12; $m++) { $v($mCol($m).$row, $vals[$m] ?? 0); }
            $al('C'.$row.':O'.$row, 'center', 'center');
            $bd('A'.$row.':O'.$row);
        };

        // Helper: sub-row (B=label, C=SUM, D–O=0, no A merge)
        $subRow = function(int $row, string $lbl, float $height) use ($ws,$v,$mCol,$al,$bd,$rh,$font) {
            $rh($row, $height);
            $v('B'.$row, $lbl); $font('B'.$row, false, 12);
            $v('C'.$row, '=SUM(D'.$row.':O'.$row.')');
            for ($m=1; $m<=12; $m++) { $v($mCol($m).$row, 0); }
            $al('C'.$row.':O'.$row, 'center', 'center');
            $bd('A'.$row.':O'.$row);
        };

        // ── Row 11: Carry-over ────────────────────────────────────────────────
        $mg('A11:B11');
        $v('A11', 'CARRY-OVER DECISIONS/ COMPLIANCE ORDERS ISSUED                 ');
        $font('A11', false, 12);
        $dataRow(11, array_fill(1,12,0), 28.5);

        // ── Row 12: Carry-over monetary sub-row ───────────────────────────────
        $rh(12, 18.0);
        $v('B12', 'Total Monetary Award'); $font('B12', false, 12);
        $v('D12', '-');
        $al('B12:O12', 'center', 'center'); $bd('A12:O12');

        // ── Row 13: gap ───────────────────────────────────────────────────────
        $rh(13, 12.0);

        // ── Row 14: New decisions ─────────────────────────────────────────────
        $mg('A14:B14');
        $v('A14', 'Add:   NEW DECISIONS/ COMPLIANCE ORDERS ISSUED');
        $font('A14', false, 12);
        $dataRow(14, $newDecisions, 30.0);

        // ── Row 15: Monetary award new decisions sub-row ──────────────────────
        $rh(15, 30.75);
        $v('B15', 'Monetary Award (New Decisions/ Compliance Orders)'); $font('B15', false, 12);
        $v('C15', '=SUM(D15:O15)');
        $v('D15', round((float)$monetary, 2));
        for ($m=2; $m<=12; $m++) { $v($mCol($m).'15', 0); }
        $ws->getStyle('C15:O15')->getNumberFormat()
           ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2);
        $al('C15:O15','center','center'); $bd('A15:O15');

        // ── Row 16: Total decisions handled ──────────────────────────────────
        $rh(16, 34.5); $mg('A16:B16');
        $v('A16', 'TOTAL DECISIONS/ COMPLIANCE ORDERS HANDLED');
        $font('A16', false, 12);
        $v('C16', '=SUM(D16:O16)');
        for ($m=1; $m<=12; $m++) { $col=$mCol($m); $v($col.'16', "={$col}11+{$col}14"); }
        $al('C16:O16','center','center'); $bd('A16:O16');

        // ── Row 17: Monetary award carry-over+new sub-row ─────────────────────
        $rh(17, 30.0);
        $v('B17', 'Monetary Award (Carry-Over and New Decisions/ Compliance Orders)');
        $font('B17', false, 12); $al('C17:O17','center','center'); $bd('A17:O17');

        // ── Row 18: gap ───────────────────────────────────────────────────────
        $rh(18, 10.5);

        // ── Row 19: Section — Before finality ─────────────────────────────────
        $rh(19, 21.75); $mg('A19:O19');
        $v('A19', 'COMPLIED WITH BEFORE ISSUANCE OF FINALITY');
        $font('A19', true, 12); $al('A19:O19','left','center'); $bd('A19:O19');

        // ── Row 20: gap ───────────────────────────────────────────────────────
        $rh(20, 10.5);

        // ── Row 21: Voluntarily complied before finality ──────────────────────
        $rh(21, 47.25); $mg('A21:B21');
        $v('A21', 'NO. OF DECISIONS/ COMPLIANCE ORDERS VOLUNTARILY COMPLIED WITH BEFORE ISSUANCE  OF FINALITY');
        $font('A21', false, 12);
        $dataRow(21, array_fill(1,12,0), 47.25);

        // ── Row 22: gap ───────────────────────────────────────────────────────
        $rh(22, 11.25);

        // ── Rows 23–25: Full judgment before finality ─────────────────────────
        $subRow(23, 'NO. OF DECISIONS WITH FULL JUDGMENT AWARD PAID', 30.75);
        $subRow(24, 'Amount of Judgment Award',                        18.75);
        $subRow(25, 'Workers Benefitted',                              18.75);

        // ── Row 26: gap ───────────────────────────────────────────────────────
        $rh(26, 18.75);

        // ── Rows 27–29: Compromise before finality ────────────────────────────
        $subRow(27, 'NO. OF DECISIONS SATISFIED THROUGH COMPROMISE SETTLEMENT', 30.0);
        $subRow(28, 'Amount of Monetary Award',                                  18.75);
        $subRow(29, 'Workers Benefitted',                                        18.75);

        // ── Row 30: gap ───────────────────────────────────────────────────────
        $rh(30, 11.25);

        // ── Row 31: Section — After finality ──────────────────────────────────
        $rh(31, 20.25); $mg('A31:O31');
        $v('A31', 'COMPLIED WITH AFTER ISSUANCE OF FINALITY');
        $font('A31', true, 12); $al('A31:O31','left','center'); $bd('A31:O31');

        // ── Row 32: gap ───────────────────────────────────────────────────────
        $rh(32, 11.25);

        // ── Row 33: Notice of finality ────────────────────────────────────────
        $rh(33, 30.0); $mg('A33:B33');
        $v('A33', 'NO. OF DECISIONS / ORDERS WITH NOTICE OF FINALITY');
        $font('A33', false, 12);
        $dataRow(33, array_fill(1,12,0), 30.0);

        // ── Row 34: gap ───────────────────────────────────────────────────────
        $rh(34, 9.75);

        // ── Row 35: Voluntarily complied after finality ───────────────────────
        $rh(35, 30.75); $mg('A35:B35');
        $v('A35', 'NO. OF DECISIONS / ORDERS WITH NOTICE OF FINALITY VOLUNTARILY COMPLIED WITH');
        $font('A35', false, 12);
        $dataRow(35, array_fill(1,12,0), 30.75);

        // ── Row 36: gap ───────────────────────────────────────────────────────
        $rh(36, 12.75);

        // ── Rows 37–39: Full judgment after finality ──────────────────────────
        $subRow(37, 'NO. OF DECISIONS WITH FULL JUDGMENT AWARD PAID', 31.5);
        $subRow(38, 'Amount of Judgment Award',                        18.75);
        $subRow(39, 'Workers Benefitted',                              18.75);

        // ── Row 40: gap ───────────────────────────────────────────────────────
        $rh(40, 12.75);

        // ── Rows 41–43: Compromise after finality ─────────────────────────────
        $subRow(41, 'NO. OF DECISIONS SATISFIED THROUGH COMPROMISE SETTLEMENT', 30.0);
        $subRow(42, 'Amount of Monetary Award',                                  17.25);
        $subRow(43, 'Workers Benefitted',                                        18.0);

        // ── Row 44: gap ───────────────────────────────────────────────────────
        $rh(44, 12.75);

        // ── Row 45: Total voluntarily complied ───────────────────────────────
        $rh(45, 63.4); $mg('A45:B45');
        $v('A45', 'TOTAL NO. OF DECISIONS/ COMPLIANCE ORDERS COMPLIED WITH VOLUNTARILY (BEFORE AND AFTER ISSUANCE OF FINALITY)');
        $font('A45', true, 12);
        $v('C45', '=SUM(D45:O45)');
        for ($m=1; $m<=12; $m++) { $col=$mCol($m); $v($col.'45', "={$col}21+{$col}35"); }
        $al('C45:O45','center','center'); $bd('A45:O45');

        // ── Rows 46–47: Monetary & workers voluntary compliance ───────────────
        $subRow(46, 'Total Monetary Award in Voluntary Compliance', 20.25);
        $subRow(47, 'Total No. of Workers Benefitted',              20.25);

        // ── Row 48: Voluntary satisfaction rate ──────────────────────────────
        $rh(48, 24.0); $mg('A48:B48');
        $v('A48', 'VOLUNTARY SATISFACTION RATE                             ');
        $font('A48', false, 12);
        $v('C48', '=C45/C16');
        $ws->getStyle('C48')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE);
        $al('C48:O48','center','center'); $bd('A48:O48');

        // ── Row 49: Non-monetary award ────────────────────────────────────────
        $rh(49, 19.5); $mg('A49:B49');
        $v('A49', 'NON-MONETARY AWARD');
        $font('A49', false, 12); $al('A49:B49','left','center'); $bd('A49:O49');

        // ── Rows 50–52: Non-monetary sub-rows ────────────────────────────────
        $subRow(50, 'No. of Workers Regularized/Absorbed', 18.0);
        $subRow(51, 'No. of Workers Reinstated',           18.0);
        $rh(52, 24.0);
        $v('B52', 'Others (Please Specify)'); $font('B52', false, 12);
        $al('B52:O52','left','center'); $bd('A52:O52');

        // ── Row 53: Total fines/penalties ─────────────────────────────────────
        $rh(53, 23.25); $mg('A53:B53');
        $v('A53', 'TOTAL AMOUNT OF FINES/ PENALTIES');
        $font('A53', false, 12);
        $v('C53', '=SUM(D53:O53)');
        for ($m=1; $m<=12; $m++) { $v($mCol($m).'53', 0); }
        $al('C53:O53','center','center'); $bd('A53:O53');

        // ── Row 54: Pending decisions ─────────────────────────────────────────
        $rh(54, 39.75); $mg('A54:B54');
        $v('A54', 'PENDING DECISIONS/ COMPLIANCE ORDERS ISSUED                 ');
        $font('A54', false, 12);
        $v('C54', '=C16-C45');
        for ($m=1; $m<=12; $m++) { $col=$mCol($m); $v($col.'54', "={$col}16-{$col}45"); }
        $al('C54:O54','center','center'); $bd('A54:O54');

        // ── Global left-align for label columns ───────────────────────────────
        $al('A1:B54','left','center');

        // ── Stream download ───────────────────────────────────────────────────
        $filename = "Project_Speed_Form_No__3-{$monthName}_{$year}.xlsx";
        $writer   = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            if (ob_get_level() > 0) { ob_clean(); }
            $writer->save('php://output');
        }, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control'       => 'max-age=0',
            'Content-Disposition' => 'attachment',
        ]);
    }
}