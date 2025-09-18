<?php

namespace App\Services\Pdf;

use setasign\Fpdi\Fpdi;

/**
 * InvoicePdf
 *
 * Lightweight invoice PDF renderer using FPDF/FPDI.
 * Returns a binary PDF string suitable for Laravel response() streaming.
 */
class InvoicePdf
{
    /**
     * Render the invoice into a PDF string.
     *
     * Expected $dto keys:
     * - number: string|int|null
     * - date: string|null (already formatted e.g. m/d/Y)
     * - student_name: string
     * - term_label: string|null
     * - items: array<array{description:string, qty?:float, price?:float, amount?:float, note_only?:bool}>
     * - total: float
     * - footer_name?: string|null
     * - reservation_signature?: bool (when true, add a signature line and label under items)
     */
    public function render(array $dto): string
    {
        // Inputs (existing)
        $number        = $dto['number'] ?? null;
        $date          = $dto['date'] ?? null;
        $studentName   = (string) ($dto['student_name'] ?? '');
        $studentNumber = (string) ($dto['student_number'] ?? '');
        $termLabel     = (string) ($dto['term_label'] ?? '');
        $items         = is_array($dto['items'] ?? null) ? $dto['items'] : [];
        $total         = (float) ($dto['total'] ?? 0);
        $footerName    = $dto['footer_name'] ?? null;
        $showReservationSignature = !empty($dto['reservation_signature']);

        // Added for new layout
        $companyName  = (string) ($dto['company_name'] ?? 'iACADEMY, Inc.');
        $companyLines = is_array($dto['company_lines'] ?? null) ? $dto['company_lines'] : [];
        $companyTin   = (string) ($dto['company_tin'] ?? 'VAT REG TIN: 214-749-003-00003');

        $cashSale        = !empty($dto['cash_sale']); // default: charge sales
        $soldToAddress   = (string) ($dto['sold_to_address'] ?? '');
        $soldToTin       = (string) ($dto['sold_to_tin'] ?? '');
        $paymentForm     = is_array($dto['payment_form'] ?? null) ? $dto['payment_form'] : [];

        // VAT/EWT computations (defaults: VAT 12% inclusive; EWT from withholding_pct if provided)
        $vatable = isset($dto['vatable']) && is_numeric($dto['vatable']) ? (float) $dto['vatable'] : round(($total > 0 ? $total / 1.12 : 0), 2);
        $vat     = isset($dto['vat']) && is_numeric($dto['vat']) ? (float) $dto['vat'] : round($total - $vatable, 2);
        $scPwd   = isset($dto['sc_pwd_discount']) && is_numeric($dto['sc_pwd_discount']) ? (float) $dto['sc_pwd_discount'] : 0.0;
        $addVat  = !empty($dto['add_vat']) ? $vat : 0.0;
        $withholdingPct = isset($dto['withholding_pct']) && is_numeric($dto['withholding_pct']) ? (float) $dto['withholding_pct'] : 0.0;
        $lessEwt = isset($dto['ewt_amount']) && is_numeric($dto['ewt_amount'])
            ? (float) $dto['ewt_amount']
            : round($vatable * $withholdingPct, 2);
        $vatZeroRated = isset($dto['vat_zero_rated']) && is_numeric($dto['vat_zero_rated']) ? (float) $dto['vat_zero_rated'] : 0.0;
        $vatExempt    = isset($dto['vat_exempt']) && is_numeric($dto['vat_exempt']) ? (float) $dto['vat_exempt'] : 0.0;

        $totalSalesVatIncl = round($vatable + $vat, 2);
        $totalAmountDue    = round($total + $addVat - $scPwd - $lessEwt, 2);

        // Create PDF
        $pdf = new Fpdi('P', 'mm', 'Letter');
        $pdf->AddPage('P', 'Letter');
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->SetTextColor(0, 0, 0);
        $pageW = $pdf->GetPageWidth();

        // Header: Company name and address lines
        $pdf->SetFont('Helvetica', 'B', 15);
        $this->text($pdf, 0, 18, strtoupper($companyName), 'C', $pageW);
        $pdf->SetFont('Helvetica', '', 9);
        $yHdr = 24;
        foreach ($companyLines as $ln) {
            $this->text($pdf, 0, $yHdr, trim((string)$ln), 'C', $pageW);
            $yHdr += 5;
        }
        if ($companyTin !== '') {
            $this->text($pdf, 0, $yHdr, (string)$companyTin, 'C', $pageW);
            $yHdr += 6;
        }

        // Title and invoice number/date on the right
        $pdf->SetFont('Helvetica', 'B', 16);
        $this->text($pdf, 170, 30, 'INVOICE', 'L', 30);
        $pdf->SetFont('Helvetica', '', 10);
        $this->text($pdf, 150, 38, 'Invoice No:', 'L', 25);
        $pdf->SetFont('Helvetica', 'B', 12);
        $this->text($pdf, 174, 38, ($number === null || $number === '') ? '-' : (string) $number, 'R', 30);
        $pdf->SetFont('Helvetica', '', 9);
        // $this->text($pdf, 150, 44, 'Date:', 'L', 25);
        // $this->text($pdf, 174, 44, ($date ?: ''), 'R', 30);

        // Sales type checkboxes (Cash vs Charge)
        // Draw square helper
        $drawCheckbox = function (Fpdi $p, float $x, float $y, bool $checked) {
            $size = 4.2;
            $p->Rect($x, $y, $size, $size);
            if ($checked) {
                $p->SetLineWidth(0.3);
                $p->Line($x + 0.8, $y + 2.1, $x + 1.9, $y + 3.4);
                $p->Line($x + 1.9, $y + 3.4, $x + 3.4, $y + 0.8);
                $p->SetLineWidth(0.2);
            }
        };
        $pdf->SetFont('Helvetica', '', 10);
        // Align checkboxes row with the "Invoice No:" row (y ~ 38)
        $drawCheckbox($pdf, 20, 36, $cashSale === true);
        $this->text($pdf, 26, 38, 'Cash Sales');
        $drawCheckbox($pdf, 60, 36, $cashSale === false);
        $this->text($pdf, 66, 38, 'Charge Sales');

        // Buyer block (Sold to / Address / TIN)
        $pdf->SetFont('Helvetica', '', 10);
        $this->text($pdf, 15, 46, 'Sold to:');
        $pdf->SetFont('Helvetica', 'B', 10);
        $this->text($pdf, 35, 46, strtoupper(trim($studentName)));
        $pdf->SetFont('Helvetica', '', 10);
        $this->text($pdf, 15, 52, 'Address:');
        // MultiCell for long address; keep clear off the right info column
        $addrStartX = 35; $addrStartY = 50; $addrW = 110; $addrLineH = 5;
        $pdf->SetXY($addrStartX, $addrStartY);
        $pdf->MultiCell($addrW, $addrLineH, ($soldToAddress !== '' ? strtoupper($soldToAddress) : ''), 0, 'L');
        $addrEndY = $pdf->GetY();

        // Right info column
        $this->text($pdf, 150, 46, 'Date:', 'L', 20);
        $this->text($pdf, 170, 46, ($date ?: ''), 'L', 32);
        $this->text($pdf, 150, 52, 'TIN:', 'L', 20);
        $this->text($pdf, 170, 52, $soldToTin, 'L', 32);

        // Items table
        $tableX = 8; $tableW = 196;
        // Expand column widths to better utilize Letter width and reduce side margins
        $colDescW = 130; $colQtyW = 18; $colPriceW = 26; $colAmtW = 26; // total = 200mm
        // Start table lower if address wrapped to multiple lines
        $yTable = max(70, (isset($addrEndY) ? ($addrEndY + 8) : 70));
        $pdf->SetDrawColor(0,0,0); $pdf->SetLineWidth(0.2);

        // Header row
        $pdf->SetXY($tableX, $yTable);
        $pdf->SetFont('Helvetica', 'B', 9);
        $pdf->Cell($colDescW, 8, 'ITEM DESCRIPTION', 1, 0, 'C');
        $pdf->Cell($colQtyW, 8, 'Quantity', 1, 0, 'C');
        $pdf->Cell($colPriceW, 8, 'Unit Cost/Price', 1, 0, 'C');
        $pdf->Cell($colAmtW, 8, 'Amount', 1, 1, 'C');

        // Body rows
        $pdf->SetFont('Helvetica', '', 9);
        $maxRows = 8;
        $rowH = 8;
        $rowsPrinted = 0;

        foreach ($items as $line) {
            if ($rowsPrinted >= $maxRows) break;
            $desc = isset($line['description']) ? (string) $line['description'] : '';
            $qty  = isset($line['qty']) && is_numeric($line['qty']) ? (float) $line['qty'] : 1.0;
            $price = isset($line['price']) && is_numeric($line['price'])
                ? (float) $line['price']
                : (isset($line['amount']) ? (float) $line['amount'] : 0.0);
            $amount = isset($line['amount']) && is_numeric($line['amount'])
                ? (float) $line['amount']
                : ($qty * $price);

            $noteOnly = !empty($line['note_only']);
            $pdf->SetXY($tableX, $yTable + 8 + ($rowsPrinted * $rowH));
            if ($noteOnly) {
                // note-only spans description column
                $pdf->Cell($colDescW + $colQtyW + $colPriceW + $colAmtW, $rowH, $this->truncate($desc, 110), 1, 1, 'L');
            } else {
                $pdf->Cell($colDescW, $rowH, $this->truncate($desc, 85), 1, 0, 'L');
                $pdf->Cell($colQtyW,  $rowH, rtrim(rtrim(number_format($qty, 2, '.', ''), '0'), '.'), 1, 0, 'C');
                $pdf->Cell($colPriceW,$rowH, $this->money($price), 1, 0, 'R');
                $pdf->Cell($colAmtW,  $rowH, $this->money($amount), 1, 1, 'R');
            }
            $rowsPrinted++;
        }
        // Fill remaining blank rows
        while ($rowsPrinted < $maxRows) {
            $pdf->SetXY($tableX, $yTable + 8 + ($rowsPrinted * $rowH));
            $pdf->Cell($colDescW, $rowH, '', 1, 0);
            $pdf->Cell($colQtyW,  $rowH, '', 1, 0);
            $pdf->Cell($colPriceW,$rowH, '', 1, 0);
            $pdf->Cell($colAmtW,  $rowH, '', 1, 1);
            $rowsPrinted++;
        }

        $yAfterTable = $yTable + 8 + ($maxRows * $rowH) + 4;

        // Left block: PAYMENT IN FORM OF
        $leftX = 12; $leftW = 96; $leftH = 60;
        $pdf->Rect($leftX, $yAfterTable, $leftW, $leftH);
        $pdf->SetFont('Helvetica', 'B', 9);
        $this->text($pdf, $leftX + 3, $yAfterTable + 5, 'PAYMENT IN FORM OF:');
        $pdf->SetFont('Helvetica', '', 9);
        $labels = [
            ['key' => 'cash',     'label' => 'Cash'],
            ['key' => 'check',    'label' => 'Check'],
            ['key' => 'bank',     'label' => 'Bank'],
            ['key' => 'check_no', 'label' => 'Check No.'],
            ['key' => 'others',   'label' => 'Others'],
        ];
        $ry = $yAfterTable + 10;
        foreach ($labels as $i => $row) {
            $pdf->SetXY($leftX, $ry);
            $pdf->Cell($leftW * 0.55, 8, $row['label'], 1, 0, 'L');
            $val = '';
            if (isset($paymentForm[$row['key']]) && $paymentForm[$row['key']] !== '') {
                $v = $paymentForm[$row['key']];
                $val = is_numeric($v) ? $this->money((float)$v) : (string)$v;
            }
            $pdf->Cell($leftW * 0.45, 8, $val, 1, 1, 'R');
            $ry += 8;
        }
        // Total Payment row
        $pdf->SetXY($leftX, $yAfterTable + $leftH - 10);
        $pdf->SetFont('Helvetica', 'B', 9);
        $pdf->Cell($leftW * 0.55, 10, 'Total Payment', 1, 0, 'L');
        $pdf->SetFont('Helvetica', '', 9);
        $totalPayment = isset($paymentForm['total']) && is_numeric($paymentForm['total']) ? $this->money((float)$paymentForm['total']) : '';
        $pdf->Cell($leftW * 0.45, 10, $totalPayment, 1, 1, 'R');

        // Right totals/VAT block
        $rightX = $leftX + $leftW + 4;
        $pdf->SetFont('Helvetica', '', 9);
        $lineGap = 6;
        $yy = $yAfterTable + 2;

        $pair = function(Fpdi $p, $x, $y, $label, $value) {
            $p->SetXY($x, $y);
            $p->Cell(70, 6, $label, 0, 0, 'L');
            $p->Cell(30, 6, $value, 0, 1, 'R');
        };

        $pair($pdf, $rightX, $yy, 'Total Sales (Vat Included)', $this->money($totalSalesVatIncl)); $yy += $lineGap;
        $pair($pdf, $rightX, $yy, 'Less VAT', $this->money($vat));                                 $yy += $lineGap;
        $pair($pdf, $rightX, $yy, 'Amount Net of VAT', $this->money($vatable));                     $yy += $lineGap + 2;
        $pair($pdf, $rightX, $yy, 'Less SC/PWD Discount', $scPwd > 0 ? $this->money($scPwd) : '');  $yy += $lineGap;
        $pair($pdf, $rightX, $yy, 'Add VAT', $addVat > 0 ? $this->money($addVat) : '');             $yy += $lineGap;
        $pair($pdf, $rightX, $yy, 'Less Withholding Tax', $lessEwt > 0 ? $this->money($lessEwt) : ''); $yy += $lineGap + 2;

        // Subtotals
        $pair($pdf, $rightX, $yy, 'Vatable', $this->money($vatable));                               $yy += $lineGap;
        $pair($pdf, $rightX, $yy, 'Vat Zero Rated Sale', $vatZeroRated > 0 ? $this->money($vatZeroRated) : ''); $yy += $lineGap;
        $pair($pdf, $rightX, $yy, 'Vat Exempt Sale', $vatExempt > 0 ? $this->money($vatExempt) : ''); $yy += $lineGap;

        // Emphasized totals
        $pdf->SetFont('Helvetica', 'B', 10);
        $pair($pdf, $rightX, $yy, 'Value Added Tax', $this->money($vat));                           $yy += $lineGap;
        $pair($pdf, $rightX, $yy, 'Less EWT', $lessEwt > 0 ? $this->money($lessEwt) : '');          $yy += $lineGap + 1.5;
        $pdf->SetFont('Helvetica', 'B', 11);
        $pair($pdf, $rightX, $yy, 'Total Amount Due', $this->money($totalAmountDue));               $yy += $lineGap;

        // Reservation signature line (when requested) under the items table on left side
        if (!empty($showReservationSignature)) {
            $sigY = $yAfterTable - 6;
            $pdf->SetDrawColor(0, 0, 0);
            $pdf->SetLineWidth(0.2);
            $pdf->Line(18, $sigY, 90, $sigY);
            $pdf->SetFont('Helvetica', '', 8.8);
            $this->text($pdf, 18, $sigY + 2, 'SIGNATURE', 'C', 72);
        }

        // Footer: Authorized signature and company text
        if (!empty($footerName)) {
            $pdf->SetFont('Helvetica', 'B', 10);
            $this->text($pdf, 140, 265, strtoupper((string)$footerName), 'L', 60);
        }
        $pdf->SetFont('Helvetica', 'I', 9);
        $this->text($pdf, 0, 255, $companyName, 'C', $pageW);

        return $pdf->Output('S');
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

    /**
     * Helper for simple positioned text.
     *
     * @param Fpdi   $pdf
     * @param float  $x
     * @param float  $y
     * @param string $txt
     * @param string $align 'L'|'C'|'R'
     * @param float  $w cell width for alignment context
     */
    private function text(Fpdi $pdf, float $x, float $y, string $txt, string $align = 'L', float $w = 0): void
    {
        $pdf->SetXY($x, $y);
        $pdf->Cell($w > 0 ? $w : 0, 0, $txt, 0, 1, $align);
    }
}
