<?php

namespace App\Http\Controllers;

use App\Helpers\ActivityLogger;
use App\Models\CaseFile;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\IOFactory as PhpWordIOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;

class NoticeOfFinalityController extends Controller
{
    /**
     * Generate a Notice of Finality from the values reviewed in the modal.
     * The document is generated only for download; no case data is changed.
     */
    public function generate(Request $request, $id)
    {
        $validated = $request->validate([
            'order_date' => ['nullable', 'date'],
            'dispositive_paragraph' => ['nullable', 'string', 'max:65000'],
            'courier' => ['nullable', 'string', 'max:255'],
            'date_received' => ['nullable', 'date'],
            'received_by' => ['nullable', 'string', 'max:255'],
            'tracking_no' => ['nullable', 'string', 'max:255'],
            'finality_date' => ['nullable', 'date'],
        ]);

        $case = CaseFile::findOrFail($id);
        $path = $this->buildDocument($case, $validated);

        ActivityLogger::logAction(
            'GENERATE',
            'Case',
            $case->inspection_id,
            'Generated Notice of Finality document',
            ['establishment' => $case->establishment_name]
        );

        return response()
            ->download(
                $path,
                'Notice_of_Finality.docx',
                ['Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']
            )
            ->deleteFileAfterSend(true);
    }

    private function buildDocument(CaseFile $case, array $data): string
    {
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(10);

        $section = $phpWord->addSection([
            'marginTop' => 680,
            'marginBottom' => 680,
            'marginLeft' => 1700,   // was 900 — a touch more left indentation overall
            'marginRight' => 700,
        ]);

        $center = ['alignment' => Jc::CENTER];
        $right = ['alignment' => Jc::RIGHT];
        $bold = ['bold' => true];
        $body = ['spaceAfter' => 130, 'lineHeight' => 1.15, 'alignment' => Jc::BOTH];       // justified
        $indented = ['indentation' => ['left' => 720, 'right' => 720], 'spaceAfter' => 130, 'lineHeight' => 1.15, 'alignment' => Jc::BOTH]; // justified

        $this->addLetterhead($section);

        $caseInfo = $section->addTable(['cellMargin' => 0]);
        $caseInfo->addRow();
        $caseInfo->addCell(5000)->addText('IN THE MATTER OF LABOR INSPECTION CONDUCTED AT:', array_merge($bold, ['size' => 9]));
        $caseInfo->addCell(4000)->addText('CASE NO. ' . $this->clean($case->case_no), array_merge($bold, ['size' => 9]), $right);

        $section->addText($this->clean($case->establishment_name), $bold, ['spaceBefore' => 150, 'spaceAfter' => 0]);
        $section->addText($this->clean($case->establishment_address), [], ['spaceAfter' => 110]);
        $section->addText('x' . str_repeat('-', 44) . 'x', [], ['spaceAfter' => 250]);

        $section->addText('NOTICE OF FINALITY', array_merge($bold, ['size' => 12]), ['alignment' => Jc::CENTER, 'spaceAfter' => 400]);

        $orderDate = $this->formatDate($data['order_date'] ?? null);
        $section->addText(
            'This Office issued an Order dated ' . $orderDate . ', the dispositive portion of which is here quoted as follows:',
            [],
            array_merge($body, ['spaceAfter' => 300]) // was 130, now leaves a gap before the quote
        );

        $dispositive = $this->clean($data['dispositive_paragraph'] ?? '');
        if ($dispositive !== '') {
            foreach (preg_split('/\R/u', $dispositive) as $paragraph) {
                if (trim($paragraph) !== '') {
                    $section->addText($paragraph, $bold, $indented);
                }
            }
        }

        $closingIndent = ['indentation' => ['left' => 1440, 'right' => 720], 'spaceAfter' => 260, 'lineHeight' => 1.5];

        $section->addText('A Writ of Execution shall be issued upon finality of its Order.', [], $closingIndent);
        $section->addText('SO ORDERED.', $bold, $closingIndent);
        $section->addText('Legazpi City, Philippines, ' . $orderDate, [], $closingIndent);

        $courier = $this->clean($data['courier'] ?? '');
        $recipient = $this->clean($case->establishment_name);
        $receivedDate = $this->formatDate($data['date_received'] ?? null);
        $receivedBy = $this->clean($data['received_by'] ?? '');
        $trackingNo = $this->clean($data['tracking_no'] ?? '');
        $section->addText(
            'A copy of the Order was delivered through courier' . $this->withComma($courier)
            . ', to respondent ' . $recipient
            . ' on ' . $receivedDate
            . ' and was duly received by ' . $receivedBy
            . ' as evidenced by Tracking Number: ' . $trackingNo . '.',
            [],
            ['spaceBefore' => 400, 'spaceAfter' => 190, 'lineHeight' => 1.5, 'alignment' => Jc::BOTH]  // wider spacing + justified
        );

         $finalitySpacing = ['indentation' => ['left' => 720], 'spaceBefore' => 240, 'spaceAfter' => 240];

        $finalityDate = $this->formatDate($data['finality_date'] ?? null);
        $finality = $section->addTextRun(array_merge($finalitySpacing, ['alignment' => Jc::LEFT]));
        $finality->addText('Hence, the said Order has become ');
        $finality->addText('FINAL AND EXECUTORY', $bold);
        $finality->addText(' on ' . $finalityDate . '.');

        $section->addText('Legazpi City, Philippines, _________________________.', [], $finalitySpacing);
        $section->addTextBreak(2);
        $section->addText('ATTY. NEPOMUCENO A. LEAÑO II, CPA', $bold, $right);
        $section->addText('OIC - Regional Director', ['italic' => true, 'size' => 9], $right);

        $footer = $section->addFooter();
        $footer->addText('Department of Labor and Employment - Regional Office No. 5', ['size' => 7], $center);

        $directory = storage_path('app/temp_notices');
        if (! is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $path = tempnam($directory, 'notice_of_finality_');
        PhpWordIOFactory::createWriter($phpWord, 'Word2007')->save($path);

        return $path;
    }

    private function addLetterhead($section): void
    {
        $header = $section->addHeader();
        $table = $header->addTable([
            'cellMargin' => 0,
            'layout' => \PhpOffice\PhpWord\Style\Table::LAYOUT_FIXED,
        ]);
        $table->addRow();

        $dole = $table->addCell(1200, ['valign' => 'center']);
        $dole->addImage(public_path('img/notice-of-finality/dole-bicol.png'), ['width' => 60, 'height' => 60, 'alignment' => Jc::LEFT]);

        $office = $table->addCell(5600, ['valign' => 'center']);
        $office->addText('Republic of the Philippines', ['size' => 8], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);
        $office->addText('DEPARTMENT OF LABOR AND EMPLOYMENT', ['bold' => true, 'size' => 10], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);
        $office->addText('Regional Office No. 5', ['size' => 8], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);
        $office->addText('DOLE RO5 Bldg., Doña Aurora St., Old Albay, Legazpi City', ['size' => 7], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);
        $office->addText('ORD: 0981-461-8788   TSSD: 0963-206-0008   IMSD: 0912-330-4751', ['size' => 7], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);
        $office->addText('ro5@dole.gov.ph', ['size' => 7, 'color' => '0000FF'], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);

        // Wide enough to actually hold both images at their current sizes, left-aligned
        // so they hug the office-text column instead of drifting to the page edge
        $rightLogos = $table->addCell(2600, ['valign' => 'center', 'noWrap' => true]);
        $logosRun = $rightLogos->addTextRun(['alignment' => Jc::LEFT]);
        $logosRun->addImage(public_path('img/notice-of-finality/bagong-pilipinas.png'), ['width' => 52, 'height' => 52]);
        $logosRun->addText(' ');
        $logosRun->addImage(public_path('img/notice-of-finality/bureau-veritas.png'), ['width' => 74, 'height' => 38]);
    }

    private function clean(?string $value): string
    {
        $value = (string) $value;
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $value) ?? '';

        // PHPWord writes text values directly to its XML template. Escaping here
        // keeps values such as "J&T Express" from creating malformed .docx XML.
        return htmlspecialchars(
            trim(str_replace(["\r\n", "\r"], "\n", $value)),
            ENT_XML1 | ENT_QUOTES,
            'UTF-8'
        );
    }

    private function formatDate(?string $date): string
    {
        return $date ? Carbon::parse($date)->format('d F Y') : '';
    }

    private function withComma(string $value): string
    {
        return $value === '' ? '' : ', ' . $value;
    }

    public function getData($id)
    {
        $case = CaseFile::with('latestExecution')->findOrFail($id);
        $execution = $case->latestExecution;

        return response()->json([
            'success' => true,
            'case' => [
                'case_no' => $case->case_no,
                'establishment_name' => $case->establishment_name,
                'establishment_address' => $case->establishment_address,
                'date_of_order_actual' => optional($case->date_of_order_actual)->format('Y-m-d'),
                'disposition_actual' => $case->disposition_actual,
            ],
            'execution' => $execution ? [
                'received_by' => $execution->received_by,
                'date_received' => optional($execution->date_received)->format('Y-m-d'),
                'tracking_no' => $execution->tracking_no,
                'courier' => $execution->courier,
            ] : null,
        ]);
    }
}
