<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CaseFile;
use App\Helpers\ActivityLogger;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory as PhpWordIOFactory;
use PhpOffice\PhpWord\SimpleType\Jc;

class NoticeOfFinalityController extends Controller
{
    /**
     * TEMPORARY DIAGNOSTIC VERSION.
     * Ignores case data entirely and always generates the exact same
     * static sample document, word-for-word, regardless of which case
     * was clicked. Used to confirm the PHPWord pipeline itself produces
     * a valid, openable .docx before reintroducing dynamic case data.
     */
    public function generate(Request $request, $id)
    {
        // Still validate the case exists / route works, but its data is unused for now
        $case = CaseFile::findOrFail($id);

        $tempPath = $this->buildDocument();

        ActivityLogger::logAction(
            'GENERATE',
            'Case',
            $case->inspection_id,
            'Generated Notice of Finality document (static test)',
            ['establishment' => $case->establishment_name]
        );

        return response()->download($tempPath, 'Notice_of_Finality.docx')->deleteFileAfterSend(true);
    }

    private function buildDocument()
    {
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(10);

        $section = $phpWord->addSection([
            'marginLeft' => 900, 'marginRight' => 900, 'marginTop' => 700, 'marginBottom' => 700,
        ]);

        $center = ['alignment' => Jc::CENTER];
        $bold   = ['bold' => true];
        $italic = ['italic' => true];

        // ── Letterhead ─────────────────────────────────────────────
        $section->addText('Republic of the Philippines', ['size' => 10], $center);
        $section->addText('DEPARTMENT OF LABOR AND EMPLOYMENT', array_merge($bold, ['size' => 11]), $center);
        $section->addText('Regional Office No. 5', ['size' => 9], $center);
        $section->addText('DOLE RO5 Bldg., Doña Aurora St., Old Albay, Legazpi City', ['size' => 8], $center);
        $section->addText('ORD: 0981-461-8788   TSSD: 0963-206-0008   IMSD: 0912-330-4751', ['size' => 8], $center);
        $section->addText('ro5@dole.gov.ph', ['size' => 8, 'color' => '0000FF'], $center);
        $section->addTextBreak(1);

        // ── Case info table ────────────────────────────────────────
        $table = $section->addTable(['borderSize' => 6, 'borderColor' => '000000']);
        $table->addRow();
        $table->addCell(6000)->addText('IN THE MATTER OF LABOR INSPECTION CONDUCTED AT:', array_merge($bold, ['size' => 9]));
        $table->addCell(4000)->addText('CASE NO. RO5-CSFO-LI-2025-11-0074-O', array_merge($bold, ['size' => 9]));
        $section->addTextBreak(1);

        $section->addText('CO SAY AND COMPANY INC.', $bold);
        $section->addText('La Purisima, Pili, Camarines Sur');
        $section->addTextBreak(1);
        $section->addText('MR. MAXIMO CO SAY', $bold);
        $section->addText('President');
        $section->addTextBreak(1);
        $section->addText('x' . str_repeat('-', 44) . 'x');
        $section->addTextBreak(1);

        // ── Title ──────────────────────────────────────────────────
        $section->addText('NOTICE OF FINALITY', array_merge($bold, ['size' => 12]), $center);
        $section->addTextBreak(1);

        $section->addText(
            'This Office issued an Order dated 03 June 2026, the dispositive portion of which is here quoted as follows:',
            ['size' => 10]
        );
        $section->addTextBreak(1);

        // ── Dispositive paragraph (single bold block) ─────────────
        $dispositive = '"WHEREFORE, respondent CO SAY AND COMPANY INC. and/or MR. MAXIMO CO SAY is hereby ORDERED to submit a copy of Permit to Operate for pressure vessel & boiler (refinery) and proof of existence of competent personnel in boiler operation. Submission shall be within the same period of ten (10) days from receipt hereof, otherwise, an administrative fine of EIGHT THOUSAND PESOS (PhP8,000.00) per day as provided for in Department Order No. 252-25 or the Revised Implementing Rules and Regulations of Republic Act No. 11058 entitled. "An Act Strengthening Compliance Safety and Health Standards and Providing Penalties- for Violations Thereof" shall be imposed.';

        $section->addText($dispositive, array_merge($bold, ['size' => 10]), [
            'indentation' => ['left' => 720, 'right' => 720],
        ]);
        $section->addTextBreak(1);

        $section->addText(
            'A Writ of Execution shall be issued upon finality of its Order.',
            ['size' => 10],
            ['indentation' => ['left' => 720, 'right' => 720]]
        );
        $section->addText('SO ORDERED.', ['size' => 10], ['indentation' => ['left' => 720, 'right' => 720]]);
        $section->addText(
            'Legazpi City, Philippines, 03 June 2026."',
            ['size' => 10],
            ['indentation' => ['left' => 720, 'right' => 720]]
        );
        $section->addTextBreak(1);

        // ── Delivery paragraph ─────────────────────────────────────
        $section->addText(
            'A copy of the Order was delivered through courier, LBC Express, to respondent CO SAY AND COMPANY INC. on 15 June 2026 and was duly received by through its representative Mr. Ryan Co Say, as evidenced by LBC Track and Trace Number 153075215841.',
            ['size' => 10]
        );
        $section->addTextBreak(1);

        $textRun = $section->addTextRun();
        $textRun->addText('Hence, the said Order has become ');
        $textRun->addText('FINAL AND EXECUTORY', $bold);
        $textRun->addText(' on ');
        $textRun->addText('25 June 2026', $bold);
        $textRun->addText('.');
        $section->addTextBreak(2);

        $section->addText('Legazpi City, Philippines, _________________________.', ['size' => 10]);
        $section->addTextBreak(2);

        // ── Signature block ────────────────────────────────────────
        $section->addText('ATTY. NEPOMUCENO A. LEAÑO II, CPA', $bold, $center);
        $section->addText('OIC - Regional Director', $italic + ['size' => 9], $center);
        $section->addTextBreak(1);

        $section->addText('Copy furnished:', ['size' => 9]);
        $section->addText('MR. MAXIMO CO SAY', array_merge($bold, ['size' => 9]));
        $section->addText('President', ['size' => 9]);
        $section->addText('CO SAY AND COMPANY INC.', array_merge($bold, ['size' => 9]));
        $section->addText('La Purisima, Pili, Camarines Sur', ['size' => 9]);
        $section->addText('0998-561-6109', ['size' => 9]);
        $section->addTextBreak(1);

        // ── Footer: provincial offices table (3 columns x 2 rows) ──
        $footerTable = $section->addTable(['borderSize' => 6, 'borderColor' => '000000']);

        $footerTable->addRow();
        $footerTable->addCell(3300)->addText(
            "DOLE ALBAY PROVINCIAL OFFICE\n4F Ayala Malls, Legazpi City, Albay\nro5_albay@dole.gov.ph\n0938-161-7978 / 0956-399-1015",
            ['size' => 7]
        );
        $footerTable->addCell(3300)->addText(
            "DOLE CAMARINES SUR PROVINCIAL OFFICE\n2F DOLE Bldg., City Hall Compound, Concepcion Pequeña, Naga City\nro5_camarinessur@dole.gov.ph\n0929-283-5382 / 0915-928-5037",
            ['size' => 7]
        );
        $footerTable->addCell(3300)->addText(
            "DOLE MASBATE PROVINCIAL OFFICE\n2F, Sanchez Bldg., Crossing, Quezon St. Masbate City\nro5_masbate@dole.gov.ph\n0948-443-2990 / 0966-215-9284",
            ['size' => 7]
        );

        $footerTable->addRow();
        $footerTable->addCell(3300)->addText(
            "DOLE CAMARINES NORTE PROVINCIAL OFFICE\n2F Tanzo Bldg., National Diversion Rd. Junction, Brgy. Itomang, Talisay, Camarines Norte\nro5_camarinesnorte@dole.gov.ph\n0946-397-4375",
            ['size' => 7]
        );
        $footerTable->addCell(3300)->addText(
            "DOLE CATANDUANES PROVINCIAL OFFICE\nLlantino Bldg., Brgy. Concepcion, Virac, Catanduanes\nro5_catanduanes@dole.gov.ph\n0931-890-5032",
            ['size' => 7]
        );
        $footerTable->addCell(3300)->addText(
            "DOLE SORSOGON PROVINCIAL OFFICE\n2F DOLE Bldg., City Hall Complex, Cabid-an, Sorsogon City\nro5_sorsogon@dole.gov.ph\n0981-002-3132 / 0919-755-2721",
            ['size' => 7]
        );

        // ── Save ───────────────────────────────────────────────────
        $tempDir = storage_path('app/temp_notices');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }
        $tempPath = $tempDir . '/notice_static_test_' . time() . '.docx';

        PhpWordIOFactory::createWriter($phpWord, 'Word2007')->save($tempPath);

        return $tempPath;
    }

    public function getData($id)
    {
        $case = CaseFile::with('latestExecution')->findOrFail($id);
        $exec = $case->latestExecution;

        return response()->json([
            'success' => true,
            'case' => [
                'case_no'               => $case->case_no,
                'establishment_name'    => $case->establishment_name,
                'establishment_address' => $case->establishment_address,
                'date_of_order_actual'  => optional($case->date_of_order_actual)->format('Y-m-d'),
                'disposition_actual'    => $case->disposition_actual,
            ],
            'execution' => $exec ? [
                'received_by'   => $exec->received_by,
                'date_received' => optional($exec->date_received)->format('Y-m-d'),
                'tracking_no'   => $exec->tracking_no,
                'courier'       => $exec->courier,
            ] : null,
        ]);
    }
}