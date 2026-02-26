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

        // Column widths
        $ws->getColumnDimension('A')->setWidth(40);
        $ws->getColumnDimension('B')->setWidth(12);
        foreach (range('C', 'N') as $c) {
            $ws->getColumnDimension($c)->setWidth(11);
        }

        // Colours
        $BLUE       = '003087';
        $WHITE      = 'FFFFFF';
        $LIGHT_BLUE = 'D6E4F0';
        $YELLOW     = 'FFF3CD';
        $GREEN      = 'D4EDDA';
        $GRAY       = 'F2F2F2';

        // ── Style helpers ─────────────────────────────────────────────────────────
        $fill = function(string $range, string $hex) use ($ws) {
            $ws->getStyle($range)->getFill()
               ->setFillType(Fill::FILL_SOLID)
               ->getStartColor()->setARGB('FF' . $hex);
        };

        $font = function(string $range, bool $bold = false, string $color = '000000', int $size = 10) use ($ws) {
            $f = $ws->getStyle($range)->getFont();
            $f->setName('Arial')->setSize($size)->setBold($bold);
            $f->getColor()->setARGB('FF' . $color);
        };

        $align = function(string $range, string $h = 'left', string $v = 'center') use ($ws) {
            $ws->getStyle($range)->getAlignment()
               ->setHorizontal($h)->setVertical($v)->setWrapText(true);
        };

        $border = function(string $range) use ($ws) {
            $ws->getStyle($range)->getBorders()
               ->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        };

        $v = fn(string $cell, $value) => $ws->setCellValue($cell, $value);

        // ── Header block (rows 1–7) ───────────────────────────────────────────────
        $endDay      = Carbon::create($year, $month, 1)->endOfMonth()->day;
        $monthName   = self::MONTH_NAMES[$month];

        foreach (range(1, 7) as $r) {
            $ws->mergeCells("A{$r}:N{$r}");
            $ws->getRowDimension($r)->setRowHeight(15);
        }

        $v('A1', 'Project Speed Form No. 1');
        $font('A1', true, $BLUE, 13);

        $v('A2', 'PROJECT SPEED 7');
        $font('A2', true, '000000', 11);

        $v('A3', 'REPORT ON CASES HANDLED BY MONTH: PHILIPPINES');
        $font('A3', true);

        $v('A4', "as of {$endDay} {$monthName} {$year}");
        $v('A5', 'Name of Office/Agency: DOLE-5');
        $v('A6', 'Type of Case: Labor Standard Case');
        $v('A7', 'Process Cycle Time: 96 days');

        // ── Column headers (row 9) ────────────────────────────────────────────────
        // Only show columns up to the selected month — future months are hidden.
        $ws->getRowDimension(9)->setRowHeight(22);

        // Fixed columns: Indicators + Total
        $v('A9', 'INDICATORS');
        $v('B9', 'TOTAL');

        // All 12 month columns always visible; future months will just show 0
        for ($m = 1; $m <= 12; $m++) {
            $col = $this->monthCol($m);
            $v($col . '9', self::MONTH_NAMES[$m]);
        }

        $fill('A9:N9', $BLUE);
        $font('A9:N9', true, $WHITE);
        $align('A9:N9', 'center');
        $border('A9:N9');

        // ── Row definitions ───────────────────────────────────────────────────────
        // [row => [label, background|null, bold, text-color]]
        $rowDefs = [
            12 => ['Carry-over cases from previous year', null,        false, '000000'],
            14 => ['NEW CASES',                           null,        true,  '000000'],
            16 => ['CASES HANDLED',                       $LIGHT_BLUE, true,  $BLUE  ],
            17 => ['        Within PCT',                  null,        false, '000000'],
            18 => ['        Beyond PCT',                  null,        false, '000000'],
            20 => ['TOTAL CASES HANDLED',                 $YELLOW,     true,  '000000'],
            22 => ['NET CASES HANDLED',                   null,        false, '000000'],
            24 => ['DISPOSED CASES',                      $LIGHT_BLUE, true,  $BLUE  ],
            25 => ['        Within PCT',                  null,        false, '000000'],
            26 => ['        Beyond PCT',                  null,        false, '000000'],
            28 => ['TOTAL DISPOSED CASES',                $YELLOW,     true,  '000000'],
            30 => ['DISPOSITION RATE',                    $GREEN,      true,  '000000'],
            32 => ['PENDING CASES',                       $LIGHT_BLUE, true,  $BLUE  ],
            33 => ['        Within PCT',                  null,        false, '000000'],
            34 => ['        Beyond PCT',                  null,        false, '000000'],
            36 => ['TOTAL PENDING CASES',                 $YELLOW,     true,  '000000'],
            38 => ['MONETARY BENEFITS',                   $GRAY,       false, '000000'],
            39 => ['WORKERS BENEFITTED',                  $GRAY,       false, '000000'],
        ];

        foreach ($rowDefs as $row => [$label, $bg, $bold, $color]) {
            $v("A{$row}", $label);
            $font("A{$row}:N{$row}", $bold, $color);
            if ($bg) {
                $fill("A{$row}:N{$row}", $bg);
            }
            $ws->getRowDimension($row)->setRowHeight(18);
        }

        // ── Populate values & formulas ────────────────────────────────────────────
        // NOTE: For all data rows, months BEYOND the selected month are written as 0.
        // The DB queries collect all 12 months, but we only show up to $month.

        \Illuminate\Support\Facades\Log::info('=== REPORT MONTH DATA ===', [
            'selected_year'  => $year,
            'selected_month' => $month,
            'newByMonth'     => $newByMonth,
            'disposedWithin' => $disposedWithin,
            'carryOver'      => $carryOver,
        ]);

        // Row 12 – Carry-over (only Jan; future months = 0)
        $v('B12', $carryOver);
        for ($m = 1; $m <= 12; $m++) {
            $v($this->monthCol($m) . '12', $m === 1 ? $carryOver : 0);
        }

        // Row 14 – New cases (0 for months beyond selected)
        for ($m = 1; $m <= 12; $m++) {
            $v($this->monthCol($m) . '14', $m <= $month ? $newByMonth[$m] : 0);
        }
        $v('B14', "=SUM(C14:N14)");

        // Row 17 – Cases handled within PCT (0 for months beyond selected)
        for ($m = 1; $m <= 12; $m++) {
            $c = $this->monthCol($m);
            $v($c . '17', $m <= $month ? ($m === 1 ? $carryOver : 0) + $newByMonth[$m] : 0);
        }
        $v('B17', "=SUM(C17:N17)");

        // Row 18 – Cases handled beyond PCT (always 0)
        for ($m = 1; $m <= 12; $m++) {
            $v($this->monthCol($m) . '18', 0);
        }
        $v('B18', 0);

        // Row 16 – Cases handled subtotal
        for ($m = 1; $m <= 12; $m++) {
            $c = $this->monthCol($m);
            $v($c . '16', "=SUM({$c}17:{$c}18)");
        }
        $v('B16', "=SUM(C16:N16)");

        // Row 20 – Total cases handled
        for ($m = 1; $m <= 12; $m++) {
            $c = $this->monthCol($m);
            $v($c . '20', "={$c}12+{$c}14");
        }
        $v('B20', "=SUM(C20:N20)");

        // Row 25 – Disposed within PCT (0 for months beyond selected)
        for ($m = 1; $m <= 12; $m++) {
            $v($this->monthCol($m) . '25', $m <= $month ? $disposedWithin[$m] : 0);
        }
        $v('B25', "=SUM(C25:N25)");

        // Row 26 – Disposed beyond PCT (0 for months beyond selected)
        for ($m = 1; $m <= 12; $m++) {
            $v($this->monthCol($m) . '26', $m <= $month ? $disposedBeyond[$m] : 0);
        }
        $v('B26', "=SUM(C26:N26)");

        // Row 24 – Disposed total
        // Row 24 – Disposed total
        for ($m = 1; $m <= 12; $m++) {
            $c = $this->monthCol($m);
            $v($c . '24', "=SUM({$c}25:{$c}26)");
        }
        $v('B24', '=SUM(B25:B26)');

        // Row 28 – Total disposed (mirrors row 24)
        // Row 28 – Total disposed (mirrors row 24)
        for ($m = 1; $m <= 12; $m++) {
            $c = $this->monthCol($m);
            $v($c . '28', "={$c}24");
        }
        $v('B28', "=SUM(C28:N28)");

        // Row 30 – Disposition rate
        // Row 30 – Disposition rate
        for ($m = 1; $m <= 12; $m++) {
            $c = $this->monthCol($m);
            $v($c . '30', "=IF({$c}20=0,0,{$c}24/{$c}20)");
            $ws->getStyle($c . '30')->getNumberFormat()
               ->setFormatCode(NumberFormat::FORMAT_PERCENTAGE);
        }
        $v('B30', '=IF(B20=0,0,B24/B20)');
        $ws->getStyle('B30')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE);

        // Row 33 – Pending within PCT
        // Row 33 – Pending within PCT
        for ($m = 1; $m <= 12; $m++) {
            $c = $this->monthCol($m);
            $v($c . '33', "={$c}17-{$c}25");
        }
        $v('B33', "=SUM(C33:N33)");

        // Row 34 – Pending beyond PCT
        // Row 34 – Pending beyond PCT
        for ($m = 1; $m <= 12; $m++) {
            $c = $this->monthCol($m);
            $v($c . '34', "={$c}18-{$c}26");
        }
        $v('B34', "=SUM(C34:N34)");

        // Row 32 – Pending total
        // Row 32 – Pending total
        for ($m = 1; $m <= 12; $m++) {
            $c = $this->monthCol($m);
            $v($c . '32', "=SUM({$c}33:{$c}34)");
        }
        $v('B32', '=SUM(B33:B34)');

        // Row 36 – Total pending
        // Row 36 – Total pending
        for ($m = 1; $m <= 12; $m++) {
            $c = $this->monthCol($m);
            $v($c . '36', "={$c}20-{$c}24");
        }
        $v('B36', '=B20-B24');

        // Row 22 – Net cases handled
        // Row 22 – Net cases handled
        for ($m = 1; $m <= 12; $m++) {
            $c = $this->monthCol($m);
            $v($c . '22', "={$c}20-{$c}36");
        }
        $v('B22', '=B20-B36');

        // Row 38 – Monetary benefits (selected month only)
        for ($m = 1; $m <= 12; $m++) {
            $c   = $this->monthCol($m);
            $val = $m === $month ? round((float)$monetary, 2) : 0;
            $v($c . '38', $val);
            $ws->getStyle($c . '38')->getNumberFormat()
               ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2);
        }
        $v('B38', "=SUM(C38:N38)");
        $ws->getStyle('B38')->getNumberFormat()
           ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2);

        // Row 39 – Workers benefitted (selected month only)
        for ($m = 1; $m <= 12; $m++) {
            $v($this->monthCol($m) . '39', $m === $month ? (int)$workers : 0);
        }
        $v('B39', "=SUM(C39:N39)");

        // ── Alignment for data area ───────────────────────────────────────────────
        $dataRows = [12,14,16,17,18,20,22,24,25,26,28,30,32,33,34,36,38,39];
        foreach ($dataRows as $r) {
            $align("B{$r}:N{$r}", 'center');
        }
        $align('A9:A39', 'left');

        // ── Borders for entire data table ─────────────────────────────────────────
        $border('A9:N39');

        // ── Signature block ───────────────────────────────────────────────────────
        $ws->getRowDimension(41)->setRowHeight(8);

        $sigLabels = ['A42' => 'Prepared by:', 'D42' => 'Reviewed by:', 'G42' => 'Noted by:', 'K42' => 'Approved by:'];
        foreach ($sigLabels as $cell => $label) {
            $v($cell, $label);
            $font($cell, true, '555555', 9);
        }

        $ws->getRowDimension(43)->setRowHeight(30);
        $ws->getRowDimension(44)->setRowHeight(30);

        $sigTitles = ['A46' => 'LEO III', 'D46' => 'Supervising LEO', 'G46' => 'TSSD Chief', 'K46' => 'Regional Director'];
        foreach ($sigTitles as $cell => $title) {
            $v($cell, $title);
            $font($cell, false, '888888', 9);
            $ws->getStyle($cell)->getFont()->setItalic(true);
        }

        // ── Freeze top rows & header col ─────────────────────────────────────────
        $ws->freezePane('C10');

        // ── Stream download ───────────────────────────────────────────────────────
        $officePart = $office ? str_replace(' ', '_', strtoupper($office)) . '_' : '';
        $filename   = "Form1_{$officePart}{$monthName}_{$year}.xlsx";

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
    // ══════════════════════════════════════════════════════════════════════════
    public function generateForm3(Request $request)
    {
        $request->validate([
            'year'  => 'required|integer|min:2020|max:2099',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $year  = (int) $request->year;
        $month = (int) $request->month;

        // Month col mapping: Jan=D(col4), Feb=E(5) ... Dec=O(15)
        $mCol = fn(int $m): string => \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($m + 3);

        // ── New Decisions = disposed/archived cases, per month up to selected ──
        $newDecisions = [];
        for ($m = 1; $m <= 12; $m++) {
            if ($m > $month) {
                $newDecisions[$m] = 0;
                continue;
            }
            $s = Carbon::create($year, $m, 1)->startOfMonth();
            $e = Carbon::create($year, $m, 1)->endOfMonth();
            $newDecisions[$m] = CaseFile::whereIn('overall_status', ['Completed', 'Disposed', 'Appealed'])
                ->where(fn($q) => $q
                    ->where(fn($q2) => $q2->whereNotNull('date_of_order_actual')
                        ->whereDate('date_of_order_actual', '>=', $s)
                        ->whereDate('date_of_order_actual', '<=', $e))
                    ->orWhere(fn($q2) => $q2->whereNull('date_of_order_actual')
                        ->whereDate('updated_at', '>=', $s)
                        ->whereDate('updated_at', '<=', $e))
                )->count();
        }

        // Monetary award for selected month only
        $selStart = Carbon::create($year, $month, 1)->startOfMonth();
        $selEnd   = Carbon::create($year, $month, 1)->endOfMonth();
        $monetary = CaseFile::whereIn('overall_status', ['Completed', 'Disposed', 'Appealed'])
            ->where(fn($q) => $q
                ->where(fn($q2) => $q2->whereNotNull('date_of_order_actual')
                    ->whereDate('date_of_order_actual', '>=', $selStart)
                    ->whereDate('date_of_order_actual', '<=', $selEnd))
                ->orWhere(fn($q2) => $q2->whereNull('date_of_order_actual')
                    ->whereDate('updated_at', '>=', $selStart)
                    ->whereDate('updated_at', '<=', $selEnd))
            )->sum('compliance_order_monetary_award') ?? 0;

        // ── Build spreadsheet ─────────────────────────────────────────────────
        $spreadsheet = new Spreadsheet();
        $ws = $spreadsheet->getActiveSheet();
        $ws->setTitle('STATISTICAL TABLE Part 1');

        // Column widths
        $ws->getColumnDimension('A')->setWidth(30);
        $ws->getColumnDimension('B')->setWidth(44);
        $ws->getColumnDimension('C')->setWidth(12);
        foreach (range('D', 'O') as $c) {
            $ws->getColumnDimension($c)->setWidth(10);
        }

        // Colours
        $BLUE  = '003087'; $WHITE = 'FFFFFF'; $LBLUE = 'D6E4F0';
        $YELL  = 'FFF3CD'; $GREEN = 'D4EDDA'; $GRAY  = 'F2F2F2';
        $ORNG  = 'FCE4D6';

        // Style helpers
        $fill = fn(string $r, string $h) =>
            $ws->getStyle($r)->getFill()->setFillType(Fill::FILL_SOLID)
               ->getStartColor()->setARGB('FF'.$h);
        $font = fn(string $r, bool $b=false, string $c='000000', int $s=9) =>
            $ws->getStyle($r)->getFont()->setName('Arial')->setSize($s)->setBold($b)
               ->getColor()->setARGB('FF'.$c);
        $al = fn(string $r, string $h='left', string $v='center') =>
            $ws->getStyle($r)->getAlignment()->setHorizontal($h)->setVertical($v)->setWrapText(true);
        $bd = fn(string $r) =>
            $ws->getStyle($r)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $mg = fn(string $r) => $ws->mergeCells($r);
        $v  = fn(string $cell, $val) => $ws->setCellValue($cell, $val);

        $monthName      = self::MONTH_NAMES[$month];
        $monthNameTitle = ucwords(strtolower($monthName));

        // ── Header rows 1–9 ──────────────────────────────────────────────────
        foreach (range(1,9) as $r) {
            $mg("A{$r}:O{$r}");
            $ws->getRowDimension($r)->setRowHeight(13);
        }
        $v('A1', 'Project Speed Form No. 3');       $font('A1', true, $BLUE, 12);
        $v('A3', 'PROJECT SPEED 7');                $font('A3', true, '000000', 10);
        $v('A4', 'REPORT ON EXECUTION AND SATISFACTION OF DECISIONS/ORDERS BY MONTH:  PHILIPPINES');
        $v('A5', 'ORIGINAL CASES');
        $v('A6', 'Name of Office/Agency:  DOLE Region V');
        $v('A7', "As of {$monthNameTitle} {$year}");
        $v('A9', 'Part 1:  BEFORE THE ISSUANCE OF WRIT OF EXECUTION');
        $font('A9', true, $BLUE);

        // ── Column header row 10 ─────────────────────────────────────────────
        $ws->getRowDimension(10)->setRowHeight(20);
        $mg('A10:B10');
        $v('A10', 'INDICATORS');
        $v('C10', 'TOTAL');
        $mNames = ['January','February','March','April','May','June',
                   'July','August','September','October','November','December'];
        foreach ($mNames as $i => $mn) { $v($mCol($i+1).'10', $mn); }
        $fill('A10:O10', $BLUE); $font('A10:O10', true, $WHITE); $al('A10:O10','center'); $bd('A10:O10');

        // ── Helper: write a data row (all months, total=SUM formula) ─────────
        $dataRow = function(int $row, array $vals, bool $subRow=false) use ($ws,$v,$mCol,$al,$bd,$font) {
            $v('C'.$row, '=SUM(D'.$row.':O'.$row.')');
            for ($m=1;$m<=12;$m++) { $v($mCol($m).$row, $vals[$m] ?? 0); }
            $al('C'.$row.':O'.$row, 'center');
            $bd('A'.$row.':O'.$row);
            $ws->getRowDimension($row)->setRowHeight($subRow ? 14 : 20);
        };

        // ── Row 11: Carry-over (all zeros) ────────────────────────────────────
        $mg('A11:B11');
        $v('A11', 'CARRY-OVER DECISIONS/ COMPLIANCE ORDERS ISSUED');
        $font('A11', true); $ws->getRowDimension(11)->setRowHeight(28);
        $dataRow(11, array_fill(1,12,0));

        // ── Row 12: Carry-over monetary (sub-row, gray) ───────────────────────
        $ws->getRowDimension(12)->setRowHeight(14);
        $v('B12', 'Total Monetary Award');
        $v('D12', '-');
        $al('B12:O12','center'); $bd('A12:O12'); $fill('A12:O12',$GRAY);

        // ── Row 14: New decisions ─────────────────────────────────────────────
        $mg('A14:B14');
        $v('A14', 'Add:   NEW DECISIONS/ COMPLIANCE ORDERS ISSUED');
        $font('A14', true); $ws->getRowDimension(14)->setRowHeight(20);
        $dataRow(14, $newDecisions);

        // ── Row 15: Monetary award new decisions (gray sub-row) ───────────────
        $ws->getRowDimension(15)->setRowHeight(14);
        $v('B15', 'Monetary Award (New Decisions/ Compliance Orders)');
        $v('C15', '=SUM(D15:O15)');
        $v('D15', round((float)$monetary, 2));
        for ($m=2;$m<=12;$m++) { $v($mCol($m).'15', 0); }
        $ws->getStyle('C15:O15')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2);
        $al('C15:O15','center'); $bd('A15:O15'); $fill('A15:O15',$GRAY);

        // ── Row 16: Total decisions handled ──────────────────────────────────
        $mg('A16:B16');
        $v('A16', 'TOTAL DECISIONS/ COMPLIANCE ORDERS HANDLED');
        $font('A16', true); $ws->getRowDimension(16)->setRowHeight(20);
        $v('C16', '=SUM(D16:O16)');
        for ($m=1;$m<=12;$m++) { $col=$mCol($m); $v($col.'16', "={$col}11+{$col}14"); }
        $al('C16:O16','center'); $bd('A16:O16'); $fill('A16:O16',$YELL); $font('A16:O16',true);

        // ── Row 17: Monetary award carry-over+new (gray sub-row) ─────────────
        $ws->getRowDimension(17)->setRowHeight(14);
        $v('B17', 'Monetary Award (Carry-Over and New Decisions/ Compliance Orders)');
        $al('C17:O17','center'); $bd('A17:O17'); $fill('A17:O17',$GRAY);

        // ── Row 19: Section — Complied before finality ────────────────────────
        $mg('A19:O19');
        $v('A19', 'COMPLIED WITH BEFORE ISSUANCE OF FINALITY');
        $fill('A19:O19',$LBLUE); $font('A19:O19',true,$BLUE); $al('A19:O19','left'); $bd('A19:O19');
        $ws->getRowDimension(19)->setRowHeight(20);

        // ── Row 21: Voluntarily complied before finality ──────────────────────
        $mg('A21:B21');
        $v('A21', 'NO. OF DECISIONS/ COMPLIANCE ORDERS VOLUNTARILY COMPLIED WITH BEFORE ISSUANCE OF FINALITY');
        $font('A21', true); $ws->getRowDimension(21)->setRowHeight(36);
        $dataRow(21, array_fill(1,12,0));

        // ── Rows 23–25: Full judgment before finality (gray sub-rows) ─────────
        foreach ([23=>'NO. OF DECISIONS WITH FULL JUDGMENT AWARD PAID',
                  24=>'Amount of Judgment Award',
                  25=>'Workers Benefitted'] as $row=>$lbl) {
            $v('B'.$row, $lbl);
            $dataRow($row, array_fill(1,12,0), true);
            $fill("A{$row}:O{$row}", $GRAY);
        }

        // ── Rows 27–29: Compromise before finality (gray sub-rows) ───────────
        foreach ([27=>'NO. OF DECISIONS SATISFIED THROUGH COMPROMISE SETTLEMENT',
                  28=>'Amount of Monetary Award',
                  29=>'Workers Benefitted'] as $row=>$lbl) {
            $v('B'.$row, $lbl);
            $dataRow($row, array_fill(1,12,0), true);
            $fill("A{$row}:O{$row}", $GRAY);
        }

        // ── Row 31: Section — Complied after finality ─────────────────────────
        $mg('A31:O31');
        $v('A31', 'COMPLIED WITH AFTER ISSUANCE OF FINALITY');
        $fill('A31:O31',$LBLUE); $font('A31:O31',true,$BLUE); $al('A31:O31','left'); $bd('A31:O31');
        $ws->getRowDimension(31)->setRowHeight(20);

        // ── Row 33: Notice of finality ────────────────────────────────────────
        $mg('A33:B33');
        $v('A33', 'NO. OF DECISIONS / ORDERS WITH NOTICE OF FINALITY');
        $font('A33', true); $ws->getRowDimension(33)->setRowHeight(28);
        $dataRow(33, array_fill(1,12,0));

        // ── Row 35: Voluntarily complied after finality ───────────────────────
        $mg('A35:B35');
        $v('A35', 'NO. OF DECISIONS / ORDERS WITH NOTICE OF FINALITY VOLUNTARILY COMPLIED WITH');
        $font('A35', true); $ws->getRowDimension(35)->setRowHeight(36);
        $dataRow(35, array_fill(1,12,0));

        // ── Rows 37–39: Full judgment after finality (gray sub-rows) ──────────
        foreach ([37=>'NO. OF DECISIONS WITH FULL JUDGMENT AWARD PAID',
                  38=>'Amount of Judgment Award',
                  39=>'Workers Benefitted'] as $row=>$lbl) {
            $v('B'.$row, $lbl);
            $dataRow($row, array_fill(1,12,0), true);
            $fill("A{$row}:O{$row}", $GRAY);
        }

        // ── Rows 41–43: Compromise after finality (gray sub-rows) ────────────
        foreach ([41=>'NO. OF DECISIONS SATISFIED THROUGH COMPROMISE SETTLEMENT',
                  42=>'Amount of Monetary Award',
                  43=>'Workers Benefitted'] as $row=>$lbl) {
            $v('B'.$row, $lbl);
            $dataRow($row, array_fill(1,12,0), true);
            $fill("A{$row}:O{$row}", $GRAY);
        }

        // ── Row 45: Total voluntarily complied ───────────────────────────────
        $mg('A45:B45');
        $v('A45', 'TOTAL NO. OF DECISIONS/ COMPLIANCE ORDERS COMPLIED WITH VOLUNTARILY (BEFORE AND AFTER ISSUANCE OF FINALITY)');
        $font('A45', true); $ws->getRowDimension(45)->setRowHeight(36);
        $v('C45', '=SUM(D45:O45)');
        for ($m=1;$m<=12;$m++) { $col=$mCol($m); $v($col.'45', "={$col}21+{$col}35"); }
        $al('C45:O45','center'); $bd('A45:O45'); $fill('A45:O45',$YELL); $font('A45:O45',true);

        // ── Rows 46–47: Monetary & workers in voluntary compliance (gray) ──────
        foreach ([46=>'Total Monetary Award in Voluntary Compliance',
                  47=>'Total No. of Workers Benefitted'] as $row=>$lbl) {
            $v('B'.$row, $lbl);
            $dataRow($row, array_fill(1,12,0), true);
            $fill("A{$row}:O{$row}", $GRAY);
        }

        // ── Row 48: Voluntary satisfaction rate ──────────────────────────────
        $mg('A48:B48');
        $v('A48', 'VOLUNTARY SATISFACTION RATE');
        $font('A48', true); $ws->getRowDimension(48)->setRowHeight(20);
        $v('C48', '=IF(C16=0,0,C45/C16)');
        $ws->getStyle('C48')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE);
        $al('C48:O48','center'); $bd('A48:O48'); $fill('A48:O48',$GREEN); $font('A48:O48',true);

        // ── Row 49: Non-monetary award header ────────────────────────────────
        $mg('A49:B49');
        $v('A49', 'NON-MONETARY AWARD');
        $font('A49', true, $BLUE); $ws->getRowDimension(49)->setRowHeight(16);
        $fill('A49:O49',$LBLUE); $al('A49:O49','left'); $bd('A49:O49');

        // ── Rows 50–52: Non-monetary sub-rows ────────────────────────────────
        foreach ([50=>'No. of Workers Regularized/Absorbed',
                  51=>'No. of Workers Reinstated',
                  52=>'Others (Please Specify)'] as $row=>$lbl) {
            $v('B'.$row, $lbl);
            $dataRow($row, array_fill(1,12,0), true);
            $fill("A{$row}:O{$row}", $GRAY);
        }

        // ── Row 53: Total fines/penalties ─────────────────────────────────────
        $mg('A53:B53');
        $v('A53', 'TOTAL AMOUNT OF FINES/ PENALTIES');
        $font('A53', true); $ws->getRowDimension(53)->setRowHeight(20);
        $dataRow(53, array_fill(1,12,0));
        $fill('A53:O53',$YELL); $font('A53:O53',true);

        // ── Row 54: Pending decisions ─────────────────────────────────────────
        $mg('A54:B54');
        $v('A54', 'PENDING DECISIONS/ COMPLIANCE ORDERS ISSUED');
        $font('A54', true); $ws->getRowDimension(54)->setRowHeight(28);
        $v('C54', '=C16-C45');
        for ($m=1;$m<=12;$m++) { $col=$mCol($m); $v($col.'54', "={$col}16-{$col}45"); }
        $al('C54:O54','center'); $bd('A54:O54'); $fill('A54:O54',$ORNG); $font('A54:O54',true);

        // ── Collapse empty gap rows (34, 36, 40, 44) to zero height ─────────
        foreach ([34, 36, 40, 44] as $emptyRow) {
            $ws->getRowDimension($emptyRow)->setRowHeight(3);
        }

        // ── Left-align indicator column, freeze header ────────────────────────
        $al('A1:B60','left');
        $ws->freezePane('D11');

        // ── Stream download ───────────────────────────────────────────────────
        $filename = "Form3_{$monthName}_{$year}.xlsx";
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