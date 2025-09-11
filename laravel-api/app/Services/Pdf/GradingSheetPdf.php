<?php

namespace App\Services\Pdf;

use setasign\Fpdi\Fpdi;

/**
 * GradingSheetPdf
 *
 * Renders a per-student grading sheet PDF using FPDI/tFPDF.
 * Input $dto shape is documented in GradingSheetService::buildDto().
 */
class GradingSheetPdf
{
    public function render(array $dto): string
    {
        $header   = (array)($dto['header'] ?? []);
        $student  = (array)($dto['student'] ?? []);
        $period   = (string)($dto['period'] ?? 'final');
        $rows     = is_array($dto['rows'] ?? null) ? $dto['rows'] : [];
        $summary  = (array)($dto['summary'] ?? []);
        $notes    = (string)($dto['grading_system_notes'] ?? '');
        $genBy    = (string)($dto['generated_by'] ?? '');
        $genAt    = (string)($dto['generated_at'] ?? '');
        $logoPath = isset($dto['logo_path']) && is_string($dto['logo_path']) ? $dto['logo_path'] : null;

        $schoolName = (string)($header['school_name'] ?? 'iACADEMY');
        $title      = (string)($header['title'] ?? ($period === 'midterm' ? 'Midterm Grade' : 'Finals Grade'));
        $termLabel  = (string)($header['term_label'] ?? '');
        $program    = (string)($header['program'] ?? '');

        $studNo     = (string)($student['number'] ?? '');
        $studName   = (string)($student['name'] ?? '');

        $colGradeHeader = ($period === 'midterm') ? 'Midterm Grade' : 'Final Grade';

        $pdf = new Fpdi('P', 'mm', 'A4');
        $pdf->AddPage('P', 'A4');
        $pdf->SetAutoPageBreak(true, 14);
        $pdf->SetTextColor(0, 0, 0);

        // Header: logo + headings
        if ($logoPath && @file_exists($logoPath)) {
            try {
                $pdf->Image($logoPath, 12, 12, 20);
            } catch (\Throwable $e) {
                // ignore image errors
            }
        }

        $pdf->SetFont('Helvetica', 'B', 12);
        $this->text($pdf, 36, 16, $schoolName);
        $pdf->SetFont('Helvetica', '', 11);
        $this->text($pdf, 36, 22, 'iACADEMY');
        $pdf->SetFont('Helvetica', 'B', 11);
        $this->text($pdf, 36, 28, $title . ' ' . $termLabel);

        // Student meta
        $pdf->SetFont('Helvetica', '', 9);
        $this->text($pdf, 12, 40, 'Student Number:');
        $pdf->SetFont('Helvetica', 'B', 9);
        $this->text($pdf, 45, 40, $studNo);
        $pdf->SetFont('Helvetica', '', 9);
        $this->text($pdf, 100, 40, 'Student Name:');
        $pdf->SetFont('Helvetica', 'B', 9);
        $this->text($pdf, 130, 40, $studName);
        $pdf->SetFont('Helvetica', '', 9);
        $this->text($pdf, 12, 46, 'Course:');
        $pdf->SetFont('Helvetica', 'B', 9);
        $this->text($pdf, 45, 46, $program);

        // Table header
        $y = 56;
        $pdf->SetLineWidth(0.3);
        $pdf->SetDrawColor(0,0,0);
        $pdf->Line(12, $y - 4, 198, $y - 4);

        $pdf->SetFont('Helvetica', 'B', 9);
        $this->text($pdf, 12, $y, 'Course Code');
        $this->text($pdf, 50, $y, 'Descriptive Title');
        $this->text($pdf, 135, $y, 'Units');
        $this->text($pdf, 152, $y, $colGradeHeader);
        $this->text($pdf, 182, $y, 'Units Earned');

        $pdf->Line(12, $y + 2, 198, $y + 2);
        $y += 6;
        $lineH = 6;

        // Table rows
        $pdf->SetFont('Helvetica', '', 9);
        foreach ($rows as $line) {
            $code   = (string)($line['code'] ?? '');
            $title2 = (string)($line['title'] ?? '');
            $units  = $this->num($line['units'] ?? 0);
            $grade  = (string)($line['grade'] ?? '');
            $uearn  = $this->num($line['units_earned'] ?? 0);

            // Code
            $pdf->SetXY(12, $y);
            $pdf->Cell(34, $lineH, $this->truncate($code, 18), 0, 0, 'L');
            // Title
            $pdf->SetXY(46, $y);
            $pdf->Cell(86, $lineH, $this->truncate($title2, 55), 0, 0, 'L');
            // Units
            $pdf->SetXY(132, $y);
            $pdf->Cell(16, $lineH, $units, 0, 0, 'R');
            // Grade
            $pdf->SetXY(150, $y);
            $pdf->Cell(26, $lineH, $grade, 0, 0, 'C');
            // Units Earned
            $pdf->SetXY(178, $y);
            $pdf->Cell(20, $lineH, $uearn, 0, 1, 'R');

            $y += $lineH;
            if ($y > 230) {
                $pdf->AddPage('P', 'A4');
                $y = 20;
            }
        }

        // Footer summary lines
        $pdf->SetLineWidth(0.3);
        $pdf->Line(12, $y + 2, 198, $y + 2);
        $y += 8;

        $gwa  = isset($summary['gwa']) ? $this->moneyFlexible($summary['gwa']) : '';
        $tue  = isset($summary['units_earned']) ? $this->num($summary['units_earned']) : '';

        $pdf->SetFont('Helvetica', 'B', 9);
        $this->text($pdf, 12, $y, 'General Weighted Average (GWA)');
        $pdf->SetFont('Helvetica', '', 9);
        $this->text($pdf, 90, $y, $gwa);

        $y += 6;
        $pdf->SetFont('Helvetica', 'B', 9);
        $this->text($pdf, 12, $y, 'Total Units Earned');
        $pdf->SetFont('Helvetica', '', 9);
        $this->text($pdf, 90, $y, $tue);

        // Grading system paragraph
        $y += 10;
        if (!empty($notes)) {
            $pdf->SetFont('Helvetica', 'B', 9);
            $this->text($pdf, 12, $y, 'Grading System');
            $y += 4;
            $pdf->SetFont('Helvetica', '', 8);
            $this->multiLine($pdf, 12, $y, 180, 4.2, $notes);
            $y += 14;
        }

        // Registrar signature line
        $pdf->SetDrawColor(0,0,0);
        $pdf->Line(120, $y + 10, 190, $y + 10);
        $pdf->SetFont('Helvetica', '', 8.5);
        $this->text($pdf, 120, $y + 13, 'Registrar', 'C', 70);

        // Generated footer
        $pdf->SetFont('Helvetica', '', 7.5);
        $footer = 'GENERATED BY:' . ($genBy !== '' ? (' ' . strtoupper($genBy)) : ' System');
        $this->text($pdf, 12, 200, $footer);
        if ($genAt !== '') {
            $this->text($pdf, 140, 200, 'RUNDATE&TIME:' . $genAt, 'L', 60);
        }

        return $pdf->Output('S');
    }

    private function num($v): string
    {
        $f = (float)$v;
        // Show up to one decimal when needed
        $s = rtrim(rtrim(number_format($f, 2, '.', ''), '0'), '.');
        return $s === '' ? '0' : $s;
    }

    private function moneyFlexible($v): string
    {
        if ($v === null || $v === '') return '';
        $f = (float)$v;
        return rtrim(rtrim(number_format($f, 3, '.', ''), '0'), '.');
    }

    private function truncate(string $s, int $limit): string
    {
        if (mb_strlen($s) <= $limit) return $s;
        return rtrim(mb_substr($s, 0, $limit - 1)) . '…';
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
}
