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
        // Cases created before this year that are still active (not yet closed)
        $carryOver = (clone $base())
            ->whereDate('created_at', '<', $startOfYear)
            ->whereNotIn('overall_status', ['Completed', 'Dismissed', 'Disposed', 'Appealed'])
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
        // Disposed = overall_status IN (Completed, Dismissed, Disposed)
        // Date used = date_of_order_actual (when the order was signed/issued)
        // Within PCT  = status_pct = 'Within PCT' OR null
        // Beyond PCT  = status_pct = 'Beyond PCT'
        $disposedWithin = [];
        $disposedBeyond = [];
        for ($m = 1; $m <= 12; $m++) {
            $s = Carbon::create($year, $m, 1)->startOfMonth();
            $e = Carbon::create($year, $m, 1)->endOfMonth();

            $disposedWithin[$m] = (clone $base())
                ->whereIn('overall_status', ['Completed', 'Dismissed', 'Disposed'])
                ->whereDate('date_of_order_actual', '>=', $s)
                ->whereDate('date_of_order_actual', '<=', $e)
                ->where(fn($q) => $q->where('status_pct', 'Within PCT')->orWhereNull('status_pct'))
                ->count();

            $disposedBeyond[$m] = (clone $base())
                ->whereIn('overall_status', ['Completed', 'Dismissed', 'Disposed'])
                ->whereDate('date_of_order_actual', '>=', $s)
                ->whereDate('date_of_order_actual', '<=', $e)
                ->where('status_pct', 'Beyond PCT')
                ->count();
        }

        // ── Monetary & workers for selected month ─────────────────────────────────
        $selStart = Carbon::create($year, $month, 1)->startOfMonth();
        $selEnd   = Carbon::create($year, $month, 1)->endOfMonth();

        $monetary = (clone $base())
            ->whereDate('date_of_order_actual', '>=', $selStart)
            ->whereDate('date_of_order_actual', '<=', $selEnd)
            ->sum('compliance_order_monetary_award') ?? 0;

        $workers = (clone $base())
            ->whereDate('date_of_order_actual', '>=', $selStart)
            ->whereDate('date_of_order_actual', '<=', $selEnd)
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
        $officeLabel = $office ?: 'DOLE-5 (All Offices)';

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
        $v('A5', "Name of Office/Agency: {$officeLabel}");
        $v('A6', 'Type of Case: Labor Standard Case');
        $v('A7', 'Process Cycle Time: 96 days');

        // ── Column headers (row 9) ────────────────────────────────────────────────
        $ws->getRowDimension(9)->setRowHeight(22);
        $headers = [
            'INDICATORS','TOTAL','JANUARY','FEBRUARY','MARCH','APRIL',
            'MAY','JUNE','JULY','AUGUST','SEPTEMBER','OCTOBER','NOVEMBER','DECEMBER',
        ];
        foreach ($headers as $i => $label) {
            $col = Coordinate::stringFromColumnIndex($i + 1);
            $v($col . '9', $label);
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

        // Row 12 – Carry-over (Jan col = carryOver; rest = 0)
        $v('B12', $carryOver);
        for ($m = 1; $m <= 12; $m++) {
            $v($this->monthCol($m) . '12', $m === 1 ? $carryOver : 0);
        }

        // Row 14 – New cases
        for ($m = 1; $m <= 12; $m++) {
            $v($this->monthCol($m) . '14', $newByMonth[$m]);
        }
        $v('B14', '=SUM(C14:N14)');

        // Row 17 – Cases handled within PCT
        for ($m = 1; $m <= 12; $m++) {
            $c = $this->monthCol($m);
            $v($c . '17', ($m === 1 ? $carryOver : 0) + $newByMonth[$m]);
        }
        $v('B17', '=SUM(C17:N17)');

        // Row 18 – Cases handled beyond PCT (0 — PCT tracking is per disposition)
        for ($m = 1; $m <= 12; $m++) {
            $v($this->monthCol($m) . '18', 0);
        }
        $v('B18', 0);

        // Row 16 – Cases handled subtotal
        for ($m = 1; $m <= 12; $m++) {
            $c = $this->monthCol($m);
            $v($c . '16', "=SUM({$c}17:{$c}18)");
        }
        $v('B16', '=SUM(C16:N16)');

        // Row 20 – Total cases handled
        for ($m = 1; $m <= 12; $m++) {
            $c = $this->monthCol($m);
            $v($c . '20', "={$c}12+{$c}14");
        }
        $v('B20', '=SUM(C20:N20)');

        // Row 25 – Disposed within PCT
        for ($m = 1; $m <= 12; $m++) {
            $v($this->monthCol($m) . '25', $disposedWithin[$m]);
        }
        $v('B25', '=SUM(C25:N25)');

        // Row 26 – Disposed beyond PCT
        for ($m = 1; $m <= 12; $m++) {
            $v($this->monthCol($m) . '26', $disposedBeyond[$m]);
        }
        $v('B26', '=SUM(C26:N26)');

        // Row 24 – Disposed total
        for ($m = 1; $m <= 12; $m++) {
            $c = $this->monthCol($m);
            $v($c . '24', "=SUM({$c}25:{$c}26)");
        }
        $v('B24', '=SUM(B25:B26)');

        // Row 28 – Total disposed (mirrors row 24)
        for ($m = 1; $m <= 12; $m++) {
            $c = $this->monthCol($m);
            $v($c . '28', "={$c}24");
        }
        $v('B28', '=SUM(C28:N28)');

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
        for ($m = 1; $m <= 12; $m++) {
            $c = $this->monthCol($m);
            $v($c . '33', "={$c}17-{$c}25");
        }
        $v('B33', '=SUM(C33:N33)');

        // Row 34 – Pending beyond PCT
        for ($m = 1; $m <= 12; $m++) {
            $c = $this->monthCol($m);
            $v($c . '34', "={$c}18-{$c}26");
        }
        $v('B34', '=SUM(C34:N34)');

        // Row 32 – Pending total
        for ($m = 1; $m <= 12; $m++) {
            $c = $this->monthCol($m);
            $v($c . '32', "=SUM({$c}33:{$c}34)");
        }
        $v('B32', '=SUM(B33:B34)');

        // Row 36 – Total pending
        for ($m = 1; $m <= 12; $m++) {
            $c = $this->monthCol($m);
            $v($c . '36', "={$c}20-{$c}24");
        }
        $v('B36', '=B20-B24');

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
        $v('B38', '=SUM(C38:N38)');
        $ws->getStyle('B38')->getNumberFormat()
           ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2);

        // Row 39 – Workers benefitted (selected month only)
        for ($m = 1; $m <= 12; $m++) {
            $v($this->monthCol($m) . '39', $m === $month ? (int)$workers : 0);
        }
        $v('B39', '=SUM(C39:N39)');

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
}