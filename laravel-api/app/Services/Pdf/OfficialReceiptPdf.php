<?php

namespace App\Services\Pdf;

use setasign\Fpdi\Fpdi;

/**
 * OfficialReceiptPdf
 *
 * Lightweight OR PDF renderer using FPDF/FPDI.
 * Returns a binary PDF string suitable for Laravel response() streaming.
 */
class OfficialReceiptPdf
{
    /**
     * Render the Official Receipt into a PDF string.
     *
     * Expected $dto keys:
     * - or_no: string|int
     * - payment_date?: string (m/d/Y)
     * - account_no?: string|null
     * - method?: string|null ('cash'|'check'|'online'|others - case/variant tolerant)
     * - company_name?: string (default: iACADEMY, Inc.)
     * - company_lines?: string[] (1-2 lines; long string will be split)
     * - company_tin?: string (default VAT REG TIN string)
     * - received_from_name?: string|null
     * - received_from_tin?: string|null
     * - received_from_address?: string|null
     * - items: array<array{description:string, amount:float}>
     * - total: float
     * - invoice_ref_no?: string|int|null
     * - received_by_name?: string|null
     */
    public function render(array $dto): string
    {
        // Header/company
        $companyName  = (string)($dto['company_name'] ?? 'iACADEMY, Inc.');
        $companyLines = is_array($dto['company_lines'] ?? null) ? $dto['company_lines'] : [];
        if (count($companyLines) === 1) {
            $addr = trim((string)$companyLines[0]);
            if ($addr !== '') {
                $wrapped = wordwrap($addr, 75, "\n", true);
                $parts = explode("\n", $wrapped);
                if (count($parts) >= 2) {
                    $companyLines = [$parts[0], implode(' ', array_slice($parts, 1))];
                } else {
                    $companyLines = [$addr];
                }
            } else {
                $companyLines = [];
            }
        }
        $companyTin   = (string)($dto['company_tin'] ?? 'VAT REG TIN: 214-749-003-00003');

        // Payment meta
        $orNo        = (string)($dto['or_no'] ?? '');
        $payDate     = (string)($dto['payment_date'] ?? '');
        $accountNo   = (string)($dto['account_no'] ?? '');
        $method      = strtolower(trim((string)($dto['method'] ?? '')));

        // Received from (payer)
        $rfName      = (string)($dto['received_from_name'] ?? '');
        $rfTin       = (string)($dto['received_from_tin'] ?? '');
        $rfAddress   = (string)($dto['received_from_address'] ?? '');

        // Items and total
        $items = is_array($dto['items'] ?? null) ? $dto['items'] : [];
        $total = (float)($dto['total'] ?? 0);

        // Footer/meta
        $invoiceRefNo   = array_key_exists('invoice_ref_no', $dto) ? (string)$dto['invoice_ref_no'] : '';
        $receivedByName = (string)($dto['received_by_name'] ?? '');

        // PDF page
        $pdf = new Fpdi('P', 'mm', 'Letter');
        $pdf->AddPage('P', 'Letter');
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->SetTextColor(0, 0, 0);
        $pageW = $pdf->GetPageWidth();

        // Outside thick border (gray)        
        $pdf->SetLineWidth(0.5);
        $pdf->SetDrawColor(120,120,120);

        // Letterhead image on left
        $imgPath = $this->resolveLetterhead();
        if ($imgPath && @file_exists($imgPath)) {
            $pdf->Image($imgPath, 12, 10, 28);
        }

        // Company header on the right; append VAT REG TIN to the second line
        $headerLines = $companyLines;
        if (!empty($companyTin)) {
            if (count($headerLines) >= 2) {
                $headerLines[1] = rtrim($headerLines[1]) . ' ' . $companyTin;
            } elseif (count($headerLines) === 1) {
                $headerLines[0] = rtrim($headerLines[0]) . ' ' . $companyTin;
            } else {
                $headerLines = [$companyTin];
            }
        }
        $rightX = 40;                 // start x for header block (to the right of logo)
        $rightW = $pageW - $rightX - 12;
        $pdf->SetFont('Helvetica', 'B', 16);
        $this->text($pdf, $rightX, 16, strtoupper($companyName), 'L', $rightW);
        $pdf->SetFont('Helvetica', '', 9);
        $yHdr = 22;
        foreach ($headerLines as $ln) {
            $this->text($pdf, $rightX, $yHdr, trim($ln), 'L', $rightW);
            $yHdr += 5;
        }

        // Right title + OR No.
        $pdf->SetFont('Helvetica', 'B', 15);
        $this->text($pdf, 165, 18, 'OFFICIAL RECEIPT', 'L', 40);
        $pdf->SetFont('Helvetica', '', 11);
        $this->text($pdf, 150, 26, $this->padRight('3637A', 0), 'L', 25); // visual anchor only (empty)
        $pdf->SetFont('Helvetica', 'B', 12);
        $this->text($pdf, 165, 26, 'No. ' . ($orNo !== '' ? $orNo : '-'), 'L', 40);

        // Right meta box: Payment Date / Account No.
        $boxX = 138; $boxY = 32; $boxW = 68; $rowH = 8;
        $pdf->SetDrawColor(120,120,120); $pdf->SetLineWidth(0.4);
        $pdf->Rect($boxX, $boxY, $boxW, $rowH * 2);
        $pdf->SetFont('Helvetica', 'B', 10);
        $this->cellRow($pdf, $boxX, $boxY, $boxW, 'Payment Date', (string)$payDate);
        $this->cellRow($pdf, $boxX, $boxY + $rowH, $boxW, 'Account No.', (string)$accountNo);

        // Payment method checkboxes row (left) in 2 columns
        $pdf->SetFont('Helvetica', 'B', 12);
        $yChk = 42;
        $x1 = 18;
        $x2 = 90;
        $this->checkbox($pdf, $x1, $yChk, $this->isMethod($method, 'cash')); $this->text($pdf, $x1 + 7, $yChk + 2, 'CASH');
        $this->checkbox($pdf, $x2, $yChk, $this->isMethod($method, 'online')); $this->text($pdf, $x2 + 7, $yChk + 2, 'ONLINE TRANSFER');
        $yChk2 = $yChk + 10;
        $this->checkbox($pdf, $x1, $yChk2, $this->isMethod($method, 'check')); $this->text($pdf, $x1 + 7, $yChk2 + 2, 'CHECK');
        // Fix: make sure the last checkbox and label are visible by moving right a bit
        $this->checkbox($pdf, $x2 + 5, $yChk2, (!$this->isMethod($method, 'cash') && !$this->isMethod($method, 'online') && !$this->isMethod($method, 'check'))); $this->text($pdf, $x2 + 12, $yChk2 + 2, 'OTHERS');

        // RECEIVED FROM box
        $rfX = 12; $rfY = 52; $rfW = 194; $rfH = 28;
        $pdf->SetDrawColor(120,120,120); $pdf->SetLineWidth(0.5);
        $pdf->Rect($rfX, $rfY, $rfW, $rfH);
        // Title strip        
        $pdf->Rect($rfX, $rfY, $rfW, 7, 'F');
        $pdf->SetFont('Helvetica', 'B', 10);
        $this->text($pdf, $rfX + 3, $rfY + 5, 'RECEIVED FROM');

        $pdf->SetFont('Helvetica', '', 10);
        $lineY = $rfY + 12;
        $this->labelValueUnderline($pdf, $rfX + 3, $lineY, 'Registered Name :', strtoupper($rfName), $rfW - 10); $lineY += 8;
        $this->labelValueUnderline($pdf, $rfX + 3, $lineY, 'TIN', $rfTin, $rfW - 10); $lineY += 8;
        // Business Address in one line (gray field)
        $addr = strtoupper($rfAddress);
        $this->labelValueUnderline($pdf, $rfX + 3, $lineY, 'Business Address :', $this->truncate($addr, 90), $rfW - 10);
        $lineY += 8;

        // Items table
        $tableX = 12; $tableW = 194; $tableY = $rfY + $rfH + 6;
        $descW = 150; $amtW = 44;
        $pdf->SetDrawColor(120,120,120); $pdf->SetLineWidth(0.5);
        // Header strip        
        $pdf->Rect($tableX, $tableY, $tableW, 7, 'F');
        $pdf->SetFont('Helvetica', 'B', 10);
        $this->text($pdf, $tableX + 3, $tableY + 5, 'ITEM DESCRIPTION / NATURE OF SERVICE');
        $this->text($pdf, $tableX + $descW + 3, $tableY + 5, 'AMOUNT');

        // Rows border box
        $rowsStartY = $tableY + 7;
        $rowsH = 20; // allow up to ~3 visible rows comfortably (adjust dynamically below)
        $pdf->Rect($tableX, $rowsStartY, $descW, $rowsH);
        $pdf->Rect($tableX + $descW, $rowsStartY, $amtW, $rowsH);

        // Render first row: "PAYMENT FOR" + gray value + gray amount
        $rowH = 10;
        $y = $rowsStartY;
        $pdf->SetFont('Helvetica', '', 10);
        $first = isset($items[0]) ? $items[0] : ['description' => '', 'amount' => 0];
        $desc = isset($first['description']) ? (string)$first['description'] : '';
        $amt  = isset($first['amount']) ? (float)$first['amount'] : 0.0;
        // Left label
        $pdf->SetXY($tableX + 3, $y + 2);
        $pdf->Cell(35, 5.5, 'PAYMENT FOR', 0, 0, 'L');
        // Dynamic text in gray (no fill backgrounds)
        $pdf->SetTextColor(110,110,110);
        $pdf->SetXY($tableX + 42, $y + 2);
        $pdf->Cell($descW - 46, 5.5, $this->truncate($desc, 70), 0, 0, 'L');
        $pdf->SetXY($tableX + $descW, $y + 2);
        $pdf->Cell($amtW - 3, 5.5, $this->money($amt), 0, 0, 'R');
        $pdf->SetTextColor(0,0,0);
        $y += $rowH;
        // Remaining rows left blank (within the bordered box)

        // TOTAL PAID AMOUNT box on the right under items
        $totBoxW = 70; $totBoxH = 12;
        $totBoxX = $tableX + $tableW - $totBoxW; $totBoxY = $rowsStartY + $rowsH + 6;
        $pdf->SetDrawColor(120,120,120); $pdf->SetLineWidth(0.5);
        $pdf->Rect($totBoxX, $totBoxY, $totBoxW, $totBoxH);
        $pdf->SetFont('Helvetica', 'B', 10);
        $this->text($pdf, $totBoxX + 3, $totBoxY + 4, 'TOTAL PAID AMOUNT');
        $pdf->SetFont('Helvetica', '', 11);
        $pdf->SetTextColor(110,110,110);
        $this->text($pdf, $totBoxX + 3, $totBoxY + 9, $this->money($total), 'R', $totBoxW - 6);
        $pdf->SetTextColor(0,0,0);

        // Footer right: payer and invoice ref
        $footY = $totBoxY + $totBoxH + 14;
        if ($receivedByName !== '') {
            $pdf->SetFont('Helvetica', 'B', 11);
            $this->text($pdf, 12, $footY, strtoupper($receivedByName), 'L', 100);
        }
        $pdf->SetFont('Helvetica', '', 10);
        $refTxt = 'Invoice Ref. No. ' . (($invoiceRefNo !== '') ? $invoiceRefNo : '00000');
        $this->text($pdf, 120, $footY, $refTxt, 'L', 80);

        // Disclaimer box
        $pdf->SetFont('Helvetica', 'I', 9);
        $pdf->SetDrawColor(120,120,120);
        $disc = 'This Document is not valid for claim of Input Tax.';
        $discW = $pdf->GetStringWidth($disc) + 10;
        $discX = $pageW - $discW - 12;
        $discY = $footY + 8;
        $pdf->Rect($discX, $discY - 5, $discW, 10);
        $this->text($pdf, $discX, $discY + 1, $disc, 'C', $discW);

        return $pdf->Output('S');
    }

    private function resolveLetterhead(): ?string
    {
        try {
            $candidates = [];
            if (function_exists('base_path')) {
                $candidates[] = base_path('../assets/img/letter-head-img.jpg');
                $candidates[] = base_path('assets/img/letter-head-img.jpg');
            }
            $candidates[] = realpath(__DIR__ . '/../../../../assets/img/letter-head-img.jpg');
            $candidates[] = __DIR__ . '/../../../../assets/img/letter-head-img.jpg';
            foreach ($candidates as $cand) {
                if ($cand && @file_exists($cand)) return $cand;
            }
        } catch (\Throwable $e) { }
        return null;
    }

    private function cellRow(Fpdi $pdf, float $x, float $y, float $w, string $label, string $value): void
    {
        $rowH = 8;
        $pdf->SetXY($x, $y);
        // Label
        $pdf->Cell($w * 0.55, $rowH, $label, 1, 0, 'L', false);
        // Value as gray text (no fill)
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetTextColor(110,110,110);
        $pdf->Cell($w * 0.45, $rowH, $value, 1, 0, 'R', false);
        $pdf->SetTextColor(0,0,0);
        $pdf->SetFont('Helvetica', 'B', 10);
    }

    private function labelValueUnderline(Fpdi $pdf, float $x, float $y, string $label, string $value, float $totalW): void
    {
        // Label
        $pdf->SetXY($x, $y - 4);
        $pdf->Cell(40, 8, $label, 0, 0, 'L');
        // Gray text value with underline (no fill)
        $valX = $x + 42;
        $valW = $totalW - 46;
        $pdf->SetXY($valX, $y - 4);
        $pdf->SetTextColor(80,80,80);
        $pdf->Cell($valW, 8, $value, 0, 0, 'L');
        $pdf->SetTextColor(0,0,0);
        // underline
        $pdf->SetDrawColor(120,120,120);
        $pdf->SetLineWidth(0.3);
        $pdf->Line($valX, $y + 2.8, $x + $totalW - 4, $y + 2.8);
    }

    private function checkbox(Fpdi $pdf, float $x, float $y, bool $checked): void
    {
        $size = 5.2;
        $pdf->SetDrawColor(80,80,80);
        $pdf->SetLineWidth(0.35);
        $pdf->Rect($x, $y, $size, $size);
        if ($checked) {
            $pdf->SetLineWidth(0.6);
            $pdf->Line($x + 1.0, $y + 2.8, $x + 2.4, $y + 4.0);
            $pdf->Line($x + 2.4, $y + 4.0, $x + 4.2, $y + 1.2);
            $pdf->SetLineWidth(0.35);
        }
    }

    private function isMethod(string $method, string $cmp): bool
    {
        $m = strtolower($method);
        $cmp = strtolower($cmp);
        if ($cmp === 'online') {
            return str_contains($m, 'online') || str_contains($m, 'transfer') || str_contains($m, 'bank') || str_contains($m, 'maya') || str_contains($m, 'gcash') || str_contains($m, 'bpi') || str_contains($m, 'bdo');
        }
        if ($cmp === 'check') {
            return str_contains($m, 'check') || str_contains($m, 'cheque');
        }
        if ($cmp === 'cash') {
            return str_contains($m, 'cash');
        }
        return false;
    }

    private function money(float $v): string
    {
        return number_format($v, 2, '.', ',');
    }

    private function truncate(string $s, int $limit): string
    {
        if (mb_strlen($s) <= $limit) return $s;
        return rtrim(mb_substr($s, 0, $limit - 1)) . '…';
    }

    private function padRight(string $s, int $len): string
    {
        if ($len <= 0) return '';
        return str_pad($s, $len);
    }

    /**
     * Helper for simple positioned text.
     */
    private function text(Fpdi $pdf, float $x, float $y, string $txt, string $align = 'L', float $w = 0): void
    {
        $pdf->SetXY($x, $y);
        $pdf->Cell($w > 0 ? $w : 0, 0, $txt, 0, 1, $align);
    }
}
