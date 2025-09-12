<?php

namespace App\Services\Pdf;

use setasign\Fpdi\Fpdi;

/**
 * StudentTranscriptPdf
 *
 * Renders a multi-page portrait A4 "Transcript/Copy of Grades" PDF per student.
 *
 * Expected DTO shape:
 * [
 *   'type' => 'transcript' | 'copy',
 *   'date_issued' => 'YYYY-mm-dd HH:ii:ss',
 *   'remarks' => 'string',
 *   'prepared_by' => 'string',
 *   'verified_by' => 'string',
 *   'registrar_signatory' => 'string',
 *   'signatory' => 'string', // optional alternative signatory
 *   'legend' => 'string', // grading system legend text
 *   'note'   => 'string', // footer note text
 *   'student' => [
 *       'name' => 'Last, First Middle',
 *       'program' => 'Bachelor of ...',
 *       'student_number' => '2024-00001',
 *       'date_of_birth' => 'Jun 11, 2006',
 *       'place_of_birth' => 'City',
 *       'gender' => 'Female',
 *       'citizenship' => 'Filipino',
 *       'secondary_school' => '...',
 *       'tertiary_school'  => '...',
 *       'date_of_admission' => 'YYYY-mm-dd',
 *       'date_of_graduation' => 'YYYY-mm-dd|XXXXXXX',
 *       'nstp_serial_no' => 'XXXXXXX',
 *   ],
 *   'terms' => [
 *     [
 *       'label' => 'SY 2024-2025 Trimester 1',
 *       'records' => [
 *         [ 'code' => 'CONWORLD', 'description' => 'The Contemporary World', 'units' => 3, 'grades' => ['final' => 1.75], 'remarks' => 'NGS' ],
 *         ...
 *       ]
 *     ],
 *     ...
 *   ]
 * ]
 */
class StudentTranscriptPdf
{
    public function render(array $dto): string
    {
        $type   = strtolower((string)($dto['type'] ?? 'transcript')); // transcript|copy
        $issued = (string)($dto['date_issued'] ?? '');
        $remarksFooter = (string)($dto['remarks'] ?? '');

        $preparedBy  = (string)($dto['prepared_by'] ?? '');
        $verifiedBy  = (string)($dto['verified_by'] ?? '');
        $registrar   = (string)($dto['registrar_signatory'] ?? '');
        $signatory   = (string)($dto['signatory'] ?? '');

        $legend = (string)($dto['legend'] ?? $this->defaultLegend());
        $note   = (string)($dto['note'] ?? $this->defaultNote());

        $student = (array)($dto['student'] ?? []);
        $terms   = is_array($dto['terms'] ?? null) ? $dto['terms'] : [];

        $title = ($type === 'copy') ? 'Copy of Grades' : 'Transcript of Records';

        $pdf = new Fpdi('P', 'mm', 'Legal');
        $pdf->AddPage('P', 'Legal');
        // Enable auto page break with a bottom margin that protects the footer area
        // Reserve ~80mm for footer and safe spacing.
        $pdf->SetAutoPageBreak(false);
        $pdf->SetTextColor(0, 0, 0);

        // Optional watermark for "Copy of Grades"
        if ($type === 'copy') {
            $this->watermark($pdf, 'COPY OF GRADES');
        }

        $marginL = 12;
        $marginR = 12;
        $pageWidth = method_exists($pdf, 'GetPageWidth') ? (float)$pdf->GetPageWidth() : 210.0;
        $usableW = $pageWidth - $marginL - $marginR;

        // Header
        $y = $this->header($pdf, $title, $student, $marginL, $usableW);

        // Draw fixed footer for this page (legend/note/signatories) at bottom
        $this->drawFooter($pdf, $remarksFooter, $legend, $note, $preparedBy, $verifiedBy, $registrar, $signatory, $issued, $marginL, $usableW);

        // Gap before table
        $y += 6;

        // Columns layout (fit within Legal width with 12mm margins; usableW ≈ 192mm)
        // New total width = 34 + 90 + 16 + 20 + 16 + 16 = 192
        $colX = [
            'code'        => $marginL,             // 34mm
            'title'       => $marginL + 34,        // 90mm
            'units'       => $marginL + 119,       // 16mm
            'grade'       => $marginL + 135,       // 20mm
            'completion'  => $marginL + 155,       // 16mm
            'uearn'       => $marginL + 171,       // 16mm
        ];
        $colW = [
            'code'        => 34,
            'title'       => 90,
            'units'       => 16,
            'grade'       => 20,
            'completion'  => 16,
            'uearn'       => 16,
        ];

        $lineH = 5.0;
        $footerReservedH = 70; // space we keep for the footer legend/signatories

        $pageNo = 1;
        $printedAnyRow = false;

        // Iterate terms in order provided
        foreach ($terms as $idx => $term) {
            $label = (string)($term['label'] ?? '');
            $records = is_array($term['records'] ?? null) ? $term['records'] : [];

            // Term heading
            $pdf->SetFont('Helvetica', 'B', 9.5);
            // If not enough space for term header + table header + at least 1 row, break page
            if (!$this->hasSpace($pdf, $y, $footerReservedH, $lineH * 4)) {
                $this->continuedFooter($pdf, $pageNo);
                $pdf->AddPage('P', 'Legal');
                if ($type === 'copy') $this->watermark($pdf, 'COPY OF GRADES');
                // Draw footer on the new page
                $this->drawFooter($pdf, $remarksFooter, $legend, $note, $preparedBy, $verifiedBy, $registrar, $signatory, $issued, $marginL, $usableW);
                $y = 16;
                $pageNo++;
            }
            $pdf->SetFont('Helvetica', 'B', 8.8);
            $pdf->SetXY($marginL, $y);
            $pdf->Cell($usableW, 6, $label, 0, 1, 'L');
            // Add a bit more space so the term label isn't too close to the top rule
            $y += 5;

            // Table header            
            $pdf->SetDrawColor(0,0,0);
            $pdf->Line($marginL, $y, $marginL + $usableW, $y);
            // Tighten header spacing
            $y += 1;
            $this->cell($pdf, $colX['code'], $y, $colW['code'], $lineH, 'Course Code', 'L');
            $this->cell($pdf, $colX['title'], $y, $colW['title'], $lineH, 'Descriptive Title', 'L');
            $this->cell($pdf, $colX['units'], $y, $colW['units'], $lineH, 'Units', 'C');
            $this->cell($pdf, $colX['grade'], $y, $colW['grade'], $lineH, 'Grade', 'C');
            $this->cell($pdf, $colX['completion'], $y, $colW['completion'], $lineH, 'Completion', 'C');
            $this->cell($pdf, $colX['uearn'], $y, $colW['uearn'], $lineH, 'Units', 'C');            
            // Slightly closer header lines
            $y += 4;
            $pdf->Line($marginL, $y, $marginL + $usableW, $y);
            $y += 3;

            // Rows
            $pdf->SetFont('Helvetica', '', 8.8);
            foreach ($records as $r) {
                // Ensure space for this row; else break page with "Continued..."
                if (!$this->hasSpace($pdf, $y, $footerReservedH, $lineH + 6)) {
                    $this->continuedFooter($pdf, $pageNo);
                    $pdf->AddPage('P', 'Legal');
                    if ($type === 'copy') $this->watermark($pdf, 'COPY OF GRADES');
                    // Draw footer on the new page
                    $this->drawFooter($pdf, $remarksFooter, $legend, $note, $preparedBy, $verifiedBy, $registrar, $signatory, $issued, $marginL, $usableW);
                    $y = 16;
                    $pageNo++;
                    // Reprint slim table header on new page for continuity
                    $pdf->SetFont('Helvetica', 'B', 8.8);
                    $this->cell($pdf, $colX['code'], $y, $colW['code'], $lineH, 'Course Code', 'L');
                    $this->cell($pdf, $colX['title'], $y, $colW['title'], $lineH, 'Descriptive Title', 'L');
                    $this->cell($pdf, $colX['units'], $y, $colW['units'], $lineH, 'Units', 'C');
                    $this->cell($pdf, $colX['grade'], $y, $colW['grade'], $lineH, 'Grade', 'C');
                    $this->cell($pdf, $colX['completion'], $y, $colW['completion'], $lineH, 'Completion', 'C');
                    $this->cell($pdf, $colX['uearn'], $y, $colW['uearn'], $lineH, 'Units', 'C');  
                    // Tighten spacing on continued pages
                    $y += 4;
                    $pdf->Line($marginL, $y, $marginL + $usableW, $y);
                    $y += 3;
                    $pdf->SetFont('Helvetica', '', 8.8);
                }

                $code   = (string)($r['code'] ?? ($r['strCode'] ?? ''));
                $title2 = (string)($r['description'] ?? ($r['title'] ?? ''));
                $units  = $this->num($r['units'] ?? 0);
                $final  = $this->safeFinal($r);
                $remarks= (string)($r['remarks'] ?? '');
                $grade  = ($final !== '') ? $final : ($remarks !== '' ? $remarks : 'NGS');

                $completion = ($remarks !== '') ? $remarks : (($final !== '') ? '' : 'NGS');
                $uEarned = $this->unitsEarned($r);

                // Render columns
                $this->cell($pdf, $colX['code'], $y, $colW['code'], $lineH, $this->truncate($code, 18), 'L');
                $this->cell($pdf, $colX['title'], $y, $colW['title'], $lineH, $this->truncate($title2, 56), 'L');
                $this->cell($pdf, $colX['units'], $y, $colW['units'], $lineH, $units, 'C');
                $this->cell($pdf, $colX['grade'], $y, $colW['grade'], $lineH, $grade, 'C');
                $this->cell($pdf, $colX['completion'], $y, $colW['completion'], $lineH, $completion, 'C');
                $this->cell($pdf, $colX['uearn'], $y, $colW['uearn'], $lineH, $uEarned, 'C');

                $y += $lineH;
                $printedAnyRow = true;
            }

            // Small gap after each term
            $y += 3;
        }

        // Footer reserved area is drawn per page; no extra page needed here.

        // "Nothing Follows" on final page if at least one row printed
        if ($printedAnyRow) {
            $pdf->SetFont('Helvetica', '', 8.5);
            $pdf->SetTextColor(60, 60, 60);
            $pdf->SetXY($marginL, $y);
            $pdf->Cell($usableW, 6, '---------------------------------------- Nothing Follows ----------------------------------------', 0, 1, 'C');
            $y += 6;
            $pdf->SetTextColor(0, 0, 0);
        }

        // Footer text/signatories are already drawn at the bottom of each page.

        return $pdf->Output('S');
    }

    private function header(Fpdi $pdf, string $title, array $student, float $marginL, float $usableW): float
    {
        $pdf->SetFont('Helvetica', 'B', 12);
        $this->text($pdf, $marginL, 14, 'iACADEMY', 'L');
        $pdf->SetFont('Helvetica', '', 9);
        $this->text($pdf, $marginL, 19, 'iACADEMY Nexus 7434 Yakal St., Brgy. San Antonio, Makati City • Tel. 889-5555', 'L');

        $pdf->SetFont('Helvetica', 'B', 11.5);
        $this->text($pdf, $marginL + ($usableW/2) - 25, 26, $title, 'L', 50);

        // Student biodata block (two columns-ish)
        $y = 34;
        $pdf->SetFont('Helvetica', '', 8.6);

        $name   = (string)($student['name'] ?? '');
        $program= (string)($student['program'] ?? '');
        $sn     = (string)($student['student_number'] ?? '');

        $dob    = (string)($student['date_of_birth'] ?? '');
        $pob    = (string)($student['place_of_birth'] ?? '');
        $gender = (string)($student['gender'] ?? '');
        $ctz    = (string)($student['citizenship'] ?? '');

        $hs     = (string)($student['secondary_school'] ?? '');
        $ts     = (string)($student['tertiary_school'] ?? '');

        $doa    = (string)($student['date_of_admission'] ?? '');
        $dog    = (string)($student['date_of_graduation'] ?? 'XXXXXXX');
        $nstp   = (string)($student['nstp_serial_no'] ?? 'XXXXXXX');

        $leftX  = $marginL;
        $rightX = $marginL + $usableW/2 + 5;

        $this->pair($pdf, $leftX,  $y, 'Name', $name);
        $this->pair($pdf, $rightX, $y, 'ID No.', $sn);
        $y += 5;

        $this->pair($pdf, $leftX,  $y, 'Program Pursued', $program);
        $this->pair($pdf, $rightX, $y, 'Gender', $gender);
        $y += 5;

        $this->pair($pdf, $leftX,  $y, 'Date of Birth', $dob);
        $this->pair($pdf, $rightX, $y, 'Place of Birth', $pob);
        $y += 5;

        $this->pair($pdf, $leftX,  $y, 'Citizenship', $ctz);
        $this->pair($pdf, $rightX, $y, 'Date of Admission', $doa);
        $y += 5;

        $this->pair($pdf, $leftX,  $y, 'Secondary School', $hs);
        $this->pair($pdf, $rightX, $y, 'Date of Graduation', $dog);
        $y += 5;

        $this->pair($pdf, $leftX,  $y, 'Tertiary School', $ts);
        $this->pair($pdf, $rightX, $y, 'NSTP Serial No.', $nstp);
        $y += 8;

        // Table header top rule
        $pdf->SetDrawColor(0,0,0);
        $pdf->Line($marginL, $y, $marginL + $usableW, $y);
        return $y;
    }

    private function continuedFooter(Fpdi $pdf, int $pageNo, bool $continued = true): void
    {
        $pdf->SetFont('Helvetica', '', 8.2);
        $pdf->SetTextColor(80,80,80);
        $txt = $continued ? ('---------------------------------------- Continued on Page ' . ($pageNo + 1) . ' ----------------------------------------') : '---------------------------------------- Continued ----------------------------------------';
        // Position just above reserved footer area (auto-break bottom margin is 80mm)
        $pageHeight = method_exists($pdf, 'GetPageHeight') ? (float)$pdf->GetPageHeight() : 297.0;
        $pageWidth  = method_exists($pdf, 'GetPageWidth') ? (float)$pdf->GetPageWidth() : 210.0;
        $x = 12;
        $y = $pageHeight - 80; // a bit above the auto-break margin
        $w = $pageWidth - (2 * $x);
        $this->text($pdf, $x, $y, $txt, 'C', $w);
        $pdf->SetTextColor(0,0,0);
    }

    private function signatureLine(Fpdi $pdf, float $x, float $y, float $w, string $name, string $label, bool $compact = false): void
    {
        $lineY = $y + 10;
        $pdf->SetDrawColor(0,0,0);
        $pdf->Line($x, $lineY, $x + $w, $lineY);

        $pdf->SetFont('Helvetica', 'B', 8.8);
        $this->text($pdf, $x, $y + 2, trim($name) !== '' ? $name : ' ', 'C', $w);
        $pdf->SetFont('Helvetica', '', 8.2);
        $this->text($pdf, $x, $lineY + 3.2, $label, 'C', $w);
    }

    private function defaultLegend(): string
    {
        // Copied from sample (verbatim as requested)
        return '1.00 (98-100) Excellent; 1.25 (95-97); 1.50 (92-94) Very Good; 1.75 (89-91); 2.00 (86-88); 2.25 (83-85); 2.50 (80-82) Satisfactory; 2.75 (77-79) Fair; 3.00 (75-76); 5.00 (Below 75) Failed; OD (Officially Dropped); UD (Unofficially Dropped); FA (Failure due to Absences); IP (In Progress) for internship only; P (Passed); F (Failed); OW (Officially Withdrawn); UW (Unofficially Withdrawn); NGS (No Grade Submitted)';
    }

    private function defaultNote(): string
    {
        return 'This document is valid only when it bears the seal of the College and affixed with the original signature in ink. Any erasure or alteration made on this copy renders the whole document invalid.';
    }

    private function watermark(Fpdi $pdf, string $text): void
    {
        // Light, large centered text (no rotation for simplicity)
        $pdf->SetFont('Helvetica', 'B', 36);
        $pdf->SetTextColor(220, 220, 220);
        $this->text($pdf, 15, 150, $text, 'L');
        $pdf->SetTextColor(0, 0, 0);
    }

    private function hasSpace(Fpdi $pdf, float $currentY, float $footerReserved, float $needed): bool
    {
        // Use current page height to support different paper sizes (e.g., Legal)
        $pageHeight = method_exists($pdf, 'GetPageHeight') ? (float)$pdf->GetPageHeight() : 297.0;
        $bottom = $pageHeight - 60; // bottom margin of ~10mm
        return ($currentY + $needed + $footerReserved) < $bottom;
    }

    private function pair(Fpdi $pdf, float $x, float $y, string $label, string $value): void
    {
        $pdf->SetFont('Helvetica', '', 8.3);
        $this->text($pdf, $x, $y, $label . ' : ');
        $pdf->SetFont('Helvetica', 'B', 8.3);
        $this->text($pdf, $x + 26, $y, $value);
    }

    private function unitsEarned(array $r): string
    {
        $units = isset($r['units']) ? (float)$r['units'] : (isset($r['strUnits']) ? (float)$r['strUnits'] : 0.0);
        $final = $this->finalNumeric($r);
        $remarks = (string)($r['remarks'] ?? '');

        $passed = false;
        if ($final !== null) {
            $passed = ($final <= 3.0);
        } else {
            $passed = (strtoupper(trim($remarks)) === 'P');
        }

        $ue = $passed ? $units : 0.0;
        return $this->num($ue);
    }

    private function safeFinal(array $r): string
    {
        $f = $this->finalNumeric($r);
        if ($f === null) return '';
        // keep up to 2 decimals; trim trailing zeros
        $s = rtrim(rtrim(number_format($f, 2, '.', ''), '0'), '.');
        return $s;
    }

    private function finalNumeric(array $r): ?float
    {
        // records may hold: ['grades' => ['final' => x]]
        if (isset($r['grades']) && is_array($r['grades']) && array_key_exists('final', $r['grades'])) {
            $v = $r['grades']['final'];
            if ($v === null || $v === '') return null;
            if (is_numeric($v)) return (float)$v;
            // Sometimes final may arrive as string '1.75'
            $t = trim((string)$v);
            if (is_numeric($t)) return (float)$t;
            return null;
        }
        if (isset($r['final'])) {
            $v = $r['final'];
            if ($v === null || $v === '') return null;
            if (is_numeric($v)) return (float)$v;
            $t = trim((string)$v);
            return is_numeric($t) ? (float)$t : null;
        }
        return null;
    }

    private function num($v): string
    {
        $f = (float)$v;
        $s = rtrim(rtrim(number_format($f, 2, '.', ''), '0'), '.');
        return $s === '' ? '0' : $s;
    }

    private function truncate(string $s, int $limit): string
    {
        if ($limit <= 3) $limit = 4;
        if (mb_strlen($s) <= $limit) return $s;
        return rtrim(mb_substr($s, 0, $limit - 1)) . '…';
    }

    private function cell(Fpdi $pdf, float $x, float $y, float $w, float $h, string $txt, string $align = 'L'): void
    {
        $pdf->SetXY($x, $y);
        $pdf->Cell($w, $h, $txt, 0, 0, $align);
    }

    private function multiLine(Fpdi $pdf, float $x, float $y, float $w, float $h, string $text): void
    {
        $pdf->SetXY($x, $y);
        $pdf->MultiCell($w, $h, $text, 0, 'L');
    }

    private function text(Fpdi $pdf, float $x, float $y, string $txt, string $align = 'L', float $w = 0): void
    {
        $pdf->SetXY($x, $y);
        $pdf->Cell($w > 0 ? $w : 0, 0, $txt, 0, 1, $align);
    }

    /**
     * Draws the repeating footer (remarks, legend, note, signatories, date) at the bottom of the page.
     */
    private function drawFooter(Fpdi $pdf, string $remarks, string $legend, string $note, string $preparedBy, string $verifiedBy, string $registrar, string $signatory, string $issued, float $marginL, float $usableW): void
    {
        $pageHeight = method_exists($pdf, 'GetPageHeight') ? (float)$pdf->GetPageHeight() : 297.0;

        // Start footer ~64mm from bottom to accommodate text + signatories (reserve about 70mm total)
        $y = $pageHeight - 64;

        // Remarks
        if (trim($remarks) !== '') {
            $pdf->SetFont('Helvetica', 'B', 9);
            $this->text($pdf, $marginL, $y, 'Remarks');
            $y += 4;
            $pdf->SetFont('Helvetica', '', 8.5);
            $this->multiLine($pdf, $marginL, $y, $usableW, 4.2, $remarks);
            $y += 10;
        }

        // Grading System
        if (trim($legend) !== '') {
            $pdf->SetFont('Helvetica', 'B', 9);
            $this->text($pdf, $marginL, $y, 'Grading System');
            $y += 2;
            $pdf->SetFont('Helvetica', '', 7.9);
            $this->multiLine($pdf, $marginL, $y, $usableW, 4.0, $legend);
            $y += 16;
        }

        // Note
        if (trim($note) !== '') {
            $pdf->SetFont('Helvetica', 'B', 9);
            $this->text($pdf, $marginL, $y, 'Note');
            $y += 2;
            $pdf->SetFont('Helvetica', '', 7.9);
            $this->multiLine($pdf, $marginL, $y, $usableW, 4.0, $note);
            $y += 12;
        }

        // Signatories (aligned across the width)
        $colW2 = ($usableW - 10) / 3;
        $pdf->SetFont('Helvetica', '', 9);
        $this->signatureLine($pdf, $marginL, $y, $colW2, $preparedBy, 'Prepared By');
        $this->signatureLine($pdf, $marginL + $colW2 + 5, $y, $colW2, $verifiedBy, 'Verified By');
        $this->signatureLine($pdf, $marginL + 2*$colW2 + 10, $y, $colW2, $registrar, 'Registrar/Signatory', true);
        $this->signatureLine($pdf, $marginL + 2*$colW2 + 10, $y + 18, $colW2, $signatory, 'Signatory', true);

        if (trim($issued) !== '') {
            $pdf->SetFont('Helvetica', '', 8.5);
            $this->text($pdf, $marginL + 2*$colW2 + 10, $y + 36, 'Date Issued: ' . $issued);
        }
    }
}
