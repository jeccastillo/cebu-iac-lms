<?php

namespace App\Services\Pdf;

use setasign\Fpdi\Fpdi;

/**
 * EnrollmentStatisticsPdf
 *
 * Renders a landscape A4 PDF table:
 * - Leftmost column: Program Name
 * - Dynamic year columns (e.g., ID2024, ID2023, ...), descending
 * - Final column: Total
 * - Footer totals row
 *
 * Expected DTO:
 * [
 *   'title'      => 'Enrollment Statistics',
 *   'term_label' => '3rd Term/SY 2024-2025', // optional, free text
 *   'years'      => [2024, 2023, ...],       // descending
 *   'rows'       => [
 *      [ 'program' => 'Bachelor of ...', 'counts' => [2024 => 34, 2023 => 1, ...], 'total' => 36 ],
 *      ...
 *   ],
 *   'totals'     => [ 'by_year' => [2024 => 381, ...], 'grand' => 1310 ]
 * ]
 */
class EnrollmentStatisticsPdf
{
    public function render(array $dto): string
    {
        $years      = is_array($dto['years'] ?? null) ? $dto['years'] : [];
        $rows       = is_array($dto['rows'] ?? null) ? $dto['rows'] : [];
        $totals     = is_array($dto['totals'] ?? null) ? $dto['totals'] : ['by_year' => [], 'grand' => 0];
        $title      = (string) ($dto['title'] ?? 'Enrollment Statistics');
        $termLabel  = (string) ($dto['term_label'] ?? '');
        $campusAddress = (string) ($dto['campus_address'] ?? '');

        // PDF setup (Landscape A4)
        $pdf = new Fpdi('L', 'mm', 'A4');
        $pdf->AddPage('L', 'A4');
        $pdf->SetAutoPageBreak(true, 12);
        $pdf->SetTextColor(0, 0, 0);

        $marginL = 10;
        $marginR = 10;
        $usableW = 297 - $marginL - $marginR;

        // Header
        $this->header($pdf, $title, $termLabel, $campusAddress, $marginL, $usableW);

        // Table header + body
        $this->table($pdf, $marginL, $usableW, $years, $rows, $totals);

        return $pdf->Output('S');
    }

    private function header(Fpdi $pdf, string $title, string $termLabel, string $campusAddress, float $marginL, float $usableW): void
    {
        // Title
        $pdf->SetFont('Helvetica', 'B', 13);
        $this->text($pdf, $marginL, 15, 'iACADEMY', 'L');
        $pdf->SetFont('Helvetica', '', 8.5);

        $addrLine = trim($campusAddress) !== '' 
            ? ('Address: ' . trim($campusAddress))
            : 'Address: iACADEMY Nexus 7434 Yakal St., Barangay San Antonio Makati City Contact No. 889-5555';
        $this->text($pdf, $marginL, 20, $addrLine, 'L');

        $pdf->SetFont('Helvetica', 'B', 11.5);
        $this->text($pdf, $marginL + ($usableW / 2) - 35, 28, $title, 'L', 70);

        $pdf->SetFont('Helvetica', '', 9);
        if ($termLabel !== '') {
            $this->text($pdf, $marginL, 34, 'Term/SY: ' . $termLabel, 'L');
        }

        // Spacer to table
        $pdf->Ln(10);
    }

    /**
     * Render the statistics table with dynamic year columns.
     */
    private function table(Fpdi $pdf, float $marginL, float $usableW, array $years, array $rows, array $totals): void
    {
        // Layout calculations
        $nYears = \count($years);
        $hasYears = $nYears > 0;

        // Reserve minimum width per numeric column; adjust program col accordingly
        $minYearW = 16.0;
        $border = 1;

        // Columns: Program + N years + Total
        $nNumericCols = ($hasYears ? $nYears : 0) + 1; // +1 for Total
        $numericMinWidthTotal = $nNumericCols * $minYearW;

        // Set program width as remaining space (keep minimum 70)
        $programW = max(70.0, $usableW - $numericMinWidthTotal);
        $yearW = ($usableW - $programW) / $nNumericCols;

        // If programW ends up too big and yearW too small, cap programW to 120
        if ($yearW < $minYearW && $programW > 90.0) {
            $programW = 90.0;
            $yearW = ($usableW - $programW) / $nNumericCols;
        }
        // If still below min, shrink minimum further a bit to fit
        if ($yearW < 12.0) {
            $yearW = 12.0;
            $programW = $usableW - ($nNumericCols * $yearW);
        }

        // Start position
        $x = $marginL;
        $y = 44; // after header
        $pdf->SetXY($x, $y);

        // Header row
        $pdf->SetFont('Helvetica', 'B', 8.5);
        $pdf->SetFillColor(245, 245, 245);
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetLineWidth(0.2);

        $pdf->Cell($programW, 8, 'Program Name', $border, 0, 'L', true);

        foreach ($years as $yr) {
            $hdr = 'ID' . (string)$yr;
            $pdf->Cell($yearW, 8, $hdr, $border, 0, 'C', true);
        }
        $pdf->Cell($yearW, 8, 'Total', $border, 1, 'C', true);

        // Body rows
        $pdf->SetFont('Helvetica', '', 8.2);
        $pdf->SetFillColor(255, 255, 255);

        foreach ($rows as $r) {
            // Page break guard
            if ($pdf->GetY() > 190) {
                $pdf->AddPage('L', 'A4');
                // reprint header row on new page
                $pdf->SetFont('Helvetica', 'B', 8.5);
                $pdf->SetFillColor(245, 245, 245);
                $pdf->Cell($programW, 8, 'Program Name', $border, 0, 'L', true);
                foreach ($years as $yr) {
                    $hdr = 'ID' . (string)$yr;
                    $pdf->Cell($yearW, 8, $hdr, $border, 0, 'C', true);
                }
                $pdf->Cell($yearW, 8, 'Total', $border, 1, 'C', true);
                $pdf->SetFont('Helvetica', '', 8.2);
                $pdf->SetFillColor(255, 255, 255);
            }

            $program = isset($r['program']) ? (string)$r['program'] : '';
            $counts  = is_array($r['counts'] ?? null) ? $r['counts'] : [];
            $total   = (int) ($r['total'] ?? 0);

            $pdf->Cell($programW, 6, $this->truncate($program, $this->charsForWidth($programW, 2.0)), $border, 0, 'L', false);

            foreach ($years as $yr) {
                $val = (int) ($counts[$yr] ?? 0);
                $pdf->Cell($yearW, 6, $val > 0 ? (string)$val : '0', $border, 0, 'C', false);
            }
            $pdf->Cell($yearW, 6, (string)$total, $border, 1, 'C', false);
        }

        // Totals row
        $pdf->SetFont('Helvetica', 'B', 8.5);
        $pdf->SetFillColor(240, 240, 240);
        $pdf->Cell($programW, 7, 'Total', $border, 0, 'L', true);
        foreach ($years as $yr) {
            $sum = (int) (($totals['by_year'][$yr] ?? 0));
            $pdf->Cell($yearW, 7, (string)$sum, $border, 0, 'C', true);
        }
        $grand = (int) ($totals['grand'] ?? 0);
        $pdf->Cell($yearW, 7, (string)$grand, $border, 1, 'C', true);
    }

    private function money(float $v): string
    {
        return number_format($v, 2, '.', ',');
    }

    private function truncate(string $s, int $limit): string
    {
        if ($limit <= 3) $limit = 4;
        if (mb_strlen($s) <= $limit) return $s;
        return rtrim(mb_substr($s, 0, $limit - 1)) . '…';
    }

    private function charsForWidth(float $w, float $approxCharW = 2.2): int
    {
        // crude estimation: Helvetica ~2.2mm per char at 8-9pt
        return (int) max(8, floor($w / $approxCharW));
    }

    /**
     * Helper for positioned text.
     */
    private function text(Fpdi $pdf, float $x, float $y, string $txt, string $align = 'L', float $w = 0): void
    {
        $pdf->SetXY($x, $y);
        $pdf->Cell($w > 0 ? $w : 0, 0, $txt, 0, 1, $align);
    }
}
