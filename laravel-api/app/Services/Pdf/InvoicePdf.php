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
        // Convert a single long campus address into two centered lines (max 2 lines)
        if (count($companyLines) === 1) {
            $addr = trim((string) $companyLines[0]);
            if ($addr !== '') {
                // Wrap at ~60 chars; collapse overflow into second line
                $wrapped = wordwrap($addr, 60, "\n", true);
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
        $companyTin   = (string) ($dto['company_tin'] ?? 'VAT REG TIN: 214-749-003-00003');

        $cashSale        = !empty($dto['cash_sale']); // default: charge sales
        $soldToAddress   = (string) ($dto['sold_to_address'] ?? '');
        $soldToTin       = (string) ($dto['sold_to_tin'] ?? '');
        $paymentForm     = is_array($dto['payment_form'] ?? null) ? $dto['payment_form'] : [];

        // VAT/EWT computations (defaults: VAT 12% inclusive; EWT from withholding_pct if provided)
        // When dto['vat_disabled'] is true, skip VAT math and mirror total to Total Sale, Vatable, and Total Amount Due.
        $vatDisabled = !empty($dto['vat_disabled']);

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

        // Base totals
        $totalSalesVatIncl = round($vatable + $vat, 2);
        $totalAmountDue    = round($total + $addVat - $scPwd - $lessEwt, 2);

        // Override using explicit invoice fields when present (spec-compliant compute)
        if (!$vatDisabled && isset($dto['invoice_amount']) && is_numeric($dto['invoice_amount'])) {
            $invAmt = (float) $dto['invoice_amount'];
            $ves    = isset($dto['invoice_amount_ves']) && is_numeric($dto['invoice_amount_ves']) ? (float)$dto['invoice_amount_ves'] : 0.0;
            $vzrs   = isset($dto['invoice_amount_vzrs']) && is_numeric($dto['invoice_amount_vzrs']) ? (float)$dto['invoice_amount_vzrs'] : 0.0;
            $wpct   = isset($dto['withholding_pct']) && is_numeric($dto['withholding_pct']) ? (float)$dto['withholding_pct'] : 0.0;

            $netVat     = $invAmt / 1.12;
            $lessVat    = $netVat * 0.12;
            $totalSales = $netVat + $ves + $vzrs;
            $lessEwtCalc= $totalSales * $wpct;

            // Assign with rounding
            $vatable            = round($netVat, 2);
            $vat                = round($lessVat, 2);
            $vatExempt          = round($ves, 2);
            $vatZeroRated       = round($vzrs, 2);
            $lessEwt            = round($lessEwtCalc, 2);
            $totalSalesVatIncl  = round($totalSales, 2);
            $totalAmountDue     = round($totalSales + $vat - $lessEwt, 2);
        }

        // Override when VAT is disabled (all VAT fields are null on invoice)
        if ($vatDisabled) {
            $vat = 0.0;
            $addVat = 0.0;
            $lessEwt = 0.0;
            // preserve scPwd only if explicitly provided; else 0
            if (!isset($dto['sc_pwd_discount'])) {
                $scPwd = 0.0;
            }
            $vatZeroRated = 0.0;
            $vatExempt = 0.0;

            $totalAmountDue = round($total, 2);
            $vatable = $totalAmountDue;         // Show total in Vatable
            $totalSalesVatIncl = $totalAmountDue; // And in Total Sale
        }

        // Create PDF
        $pdf = new Fpdi('P', 'mm', 'Letter');
        $pdf->AddPage('P', 'Letter');
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->SetTextColor(0, 0, 0);
        $pageW = $pdf->GetPageWidth();

        // Header: Company name and address lines with letterhead image on the left
        // Try to resolve the letterhead image path robustly
        $imgPath = null;
        try {
            $candidates = [];
            if (function_exists('base_path')) {
                // Running inside laravel-api; repo assets are one level up
                $candidates[] = base_path('../assets/img/letter-head-img.jpg');
                // In case assets were copied under laravel-api
                $candidates[] = base_path('assets/img/letter-head-img.jpg');
            }
            // Fallbacks relative to this file location
            $candidates[] = realpath(__DIR__ . '/../../../../assets/img/letter-head-img.jpg');
            $candidates[] = __DIR__ . '/../../../../assets/img/letter-head-img.jpg';
            foreach ($candidates as $cand) {
                if ($cand && @file_exists($cand)) { $imgPath = $cand; break; }
            }
        } catch (\Throwable $e) {
            $imgPath = null;
        }
        if ($imgPath && @file_exists($imgPath)) {
            // Place image on the left side of the header area
            // Coordinates tuned to avoid overlapping centered text
            $pdf->Image($imgPath, 35, 4, 28); // x=12mm, y=6mm, width=28mm (height auto)
        }

        $pdf->SetFont('Helvetica', 'B', 15);
        $this->text($pdf, 0, 12, $companyName, 'C', $pageW);
        $pdf->SetFont('Helvetica', '', 9);
        $yHdr = 19;        
        foreach ($companyLines as $ln) {
            $this->text($pdf, 0, $yHdr, trim((string)$ln), 'C', $pageW);
            $yHdr += 5;
        }
        if ($companyTin !== '') {
            $this->text($pdf, 0, $yHdr, (string)$companyTin, 'C', $pageW);
            $yHdr += 6;
        }

        $invoice_number = ($number === null || $number === '') ? '-' : (string) $number;
        // Title and invoice number/date on the right
        $pdf->SetFont('Helvetica', 'B', 16);
        $this->text($pdf, 170, 30, 'INVOICE', 'L', 30);
        $pdf->SetFont('Helvetica', '', 10);
        $this->text($pdf, 140, 38, 'Invoice No:'.$invoice_number, 'L', 25);
        $pdf->SetFont('Helvetica', 'B', 12);
        $this->text($pdf, 174, 38, 'No. '.$invoice_number, 'R', 30);
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

        // Underlines (lighter gray) under Sold to, Address (up to 2 lines), Date and TIN
        $pdf->SetDrawColor(180,180,180);
        $pdf->SetLineWidth(0.35);
        // Sold to
        $pdf->Line($addrStartX, 48, $addrStartX + $addrW, 48);
        // Address (compute number of lines rendered; cap to 2) — underline very close to text baseline
        $addrLinesCount = isset($addrEndY) ? max(1, (int)ceil(($addrEndY - $addrStartY) / $addrLineH)) : 1;
        $addrUnderY1 = $addrStartY + ($addrLineH - 0.8);
        $pdf->Line($addrStartX, $addrUnderY1, $addrStartX + $addrW, $addrUnderY1);
        if ($addrLinesCount > 1) {
            $addrUnderY2 = $addrUnderY1 + $addrLineH;
            $pdf->Line($addrStartX, $addrUnderY2, $addrStartX + $addrW, $addrUnderY2);
        }
        // Date and TIN underlines (right column)
        $rightValX = 170; $rightValW = 32;
        $pdf->Line($rightValX, 48, $rightValX + $rightValW, 48); // Date
        $pdf->Line($rightValX, 54, $rightValX + $rightValW, 54); // TIN

        // Items table
        $tableX = 8; $tableW = 196;
        // Expand column widths to better utilize Letter width and reduce side margins
        $colDescW = 130; $colQtyW = 18; $colPriceW = 26; $colAmtW = 26; // total = 200mm
        // Start table closer to address lines (moved up) and use light gray thicker borders
        $yTable = max(62, (isset($addrEndY) ? ($addrEndY + 4) : 62));
        // Light gray thicker borders for items/payment sections
        $pdf->SetDrawColor(180,180,180); 
        $pdf->SetLineWidth(0.5);

        // Header row
        $pdf->SetXY($tableX, $yTable);
        $pdf->SetFont('Helvetica', 'B', 9);
        $pdf->Cell($colDescW, 8, 'ITEM DESCRIPTION', 1, 0, 'C');
        $pdf->Cell($colQtyW, 8, 'Quantity', 1, 0, 'C');
        $pdf->Cell($colPriceW, 8, 'Unit Cost/Price', 1, 0, 'C');
        $pdf->Cell($colAmtW, 8, 'Amount', 1, 1, 'C');

        // Body rows
        $pdf->SetFont('Helvetica', '', 9);
        $maxRows = 4; // limit to 4 rows as requested
        $rowH = 8;
        $rowsPrinted = 0;
        // Collect reservation note lines to render as a "stamp" outside of the table
        $reservationNotes = [];

        foreach ($items as $line) {
            $desc = isset($line['description']) ? (string) $line['description'] : '';
            $qty  = isset($line['qty']) && is_numeric($line['qty']) ? (float) $line['qty'] : 1.0;
            $price = isset($line['price']) && is_numeric($line['price'])
                ? (float) $line['price']
                : (isset($line['amount']) ? (float) $line['amount'] : 0.0);
            $amount = isset($line['amount']) && is_numeric($line['amount'])
                ? (float) $line['amount']
                : ($qty * $price);

            // Reservation stamp collection and table inclusion rules:
            // - Always exclude "note_only" lines from the table; push to stamp when reservation-related.
            // - For actual reservation item rows (with qty/price/amount), KEEP them in the table
            //   and ALSO include their description in the stamp.
            $noteOnly = !empty($line['note_only']);
            $isReservationKeyword = (
                stripos($desc, 'reservation') !== false
                || stripos($desc, 'non refundable') !== false
                || stripos($desc, 'non transferable') !== false
            );

            if ($showReservationSignature && $isReservationKeyword && $desc !== '') {
                $reservationNotes[] = $desc;
            }

            // Exclude note-only rows from table, and also exclude reservation rows that carry no numeric values
            $hasNumeric = (($qty ?? 0) > 0) || (($price ?? 0) > 0) || (($amount ?? 0) > 0);
            if ($noteOnly || ($showReservationSignature && $isReservationKeyword && !$hasNumeric)) {
                continue;
            }

            // Respect the 4-row limit for the table, but continue scanning remaining lines for stamp text
            if ($rowsPrinted >= $maxRows) {
                continue;
            }

            $pdf->SetXY($tableX, $yTable + 8 + ($rowsPrinted * $rowH));
            $pdf->Cell($colDescW, $rowH, $this->truncate($desc, 85), 1, 0, 'L');
            $pdf->Cell($colQtyW,  $rowH, rtrim(rtrim(number_format($qty, 2, '.', ''), '0'), '.'), 1, 0, 'C');
            $pdf->Cell($colPriceW,$rowH, $this->money($price), 1, 0, 'R');
            $pdf->Cell($colAmtW,  $rowH, $this->money($amount), 1, 1, 'R');

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

        // Track if we drew the reservation signature within the stamp block
        $stampSignatureDrawn = false;

        // Reservation "stamp" text (render outside of table when requested)
        if (!empty($showReservationSignature) && !empty($reservationNotes)) {
            // blue-ish stamp style; make text slightly smaller
            $pdf->SetTextColor(14, 62, 160);
            $pdf->SetFont('Helvetica', 'B', 8);
            $stampX = $tableX + 4;
            // Position near the bottom of the items box but above the next section
            $stampY = $yAfterTable - 24;
            if ($stampY < $yTable + 12) { $stampY = $yTable + 12; }
            $pdf->SetXY($stampX, $stampY);
            $stampW = ($colDescW + $colQtyW + $colPriceW + $colAmtW) - 8;

            foreach ($reservationNotes as $sline) {
                $pdf->Cell($stampW, 5.0, strtoupper($sline), 0, 1, 'L');
            }

            // Draw signature line and label as part of the stamp (same color and text style as the stamp)
            $pdf->SetTextColor(14, 62, 160);
            $pdf->SetDrawColor(14, 62, 160);
            $pdf->SetLineWidth(0.2);
            // Align with the stamp text block (left-aligned) and same width
            $sigLineW = $stampW; // full stamp width
            $sigLineX = $stampX; // left aligned with stamp text
            $sigLineY = $pdf->GetY() + 1.8;
            $pdf->Line($sigLineX, $sigLineY,$colDescW - 50, $sigLineY);
            $pdf->SetFont('Helvetica', 'B', 8);
            // Left-align the label so it lines up with the stamp text
            $this->text($pdf, $sigLineX + 25, $sigLineY + 1.5, 'SIGNATURE', 'L', $sigLineW);
            $stampSignatureDrawn = true;

            // restore defaults
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('Helvetica', '', 9);
        }
        $pdf->SetDrawColor(180,180,180); 
        // Left block: PAYMENT IN FORM OF
        // Align bottom three columns with items table edges and make equal widths
        $colTotalW = $colDescW + $colQtyW + $colPriceW + $colAmtW; // items table total width (200mm)
        $colW = round($colTotalW / 3, 2); // equal width per column

        // Prepare remarks lines for "Others" + 3 extra rows beneath
        $othersRemarks = (string) ($dto['payment_form_remarks'] ?? '');
        $othersLines = [];
        if ($othersRemarks !== '') {
            $wrapped = wordwrap($othersRemarks, 38, "\n", true);
            $othersLines = explode("\n", $wrapped);
        }
        // Ensure exactly 4 lines available (1 for 'Others' row + 3 for extra rows)
        $othersLines = array_slice($othersLines, 0, 4);
        while (count($othersLines) < 4) { $othersLines[] = ''; }

        $baseLabels = [
            ['key' => 'cash',     'label' => 'Cash'],
            ['key' => 'check',    'label' => 'Check'],
            ['key' => 'bank',     'label' => 'Bank'],
            ['key' => 'check_no', 'label' => 'Check No.'],
            ['key' => 'others',   'label' => 'Others'],
        ];
        $extraRowsUnderOthers = 3;
        $rowsCount = count($baseLabels) + $extraRowsUnderOthers;

        $leftX = $tableX; 
        $leftW = $colW; 
        // Dynamic height: 10 (header gap) + rows*8 + 10 (total payment row)
        $leftH = 10 + ($rowsCount * 8) + 10;

        // Outline
        $pdf->Rect($leftX, $yAfterTable, $leftW, $leftH);
        $pdf->SetFont('Helvetica', 'B', 9);
        $this->text($pdf, $leftX + 3, $yAfterTable + 5, 'PAYMENT IN FORM OF:');

        // Rows
        $pdf->SetFont('Helvetica', '', 9);
        $ry = $yAfterTable + 10;
        foreach ($baseLabels as $row) {
            $pdf->SetXY($leftX, $ry);
            $pdf->Cell($leftW * 0.55, 8, $row['label'], 1, 0, 'L');

            // Resolve value
            $val = '';
            if ($row['key'] === 'others') {
                $val = $othersLines[0] ?? '';
                // Value left-aligned for remarks
                $pdf->Cell($leftW * 0.45, 8, $val, 1, 1, 'L');
                $ry += 8;
                // Add 3 extra rows under "Others"
                for ($k = 1; $k <= 3; $k++) {
                    $pdf->SetXY($leftX, $ry);
                    $pdf->Cell($leftW * 0.55, 8, '', 1, 0, 'L');
                    $pdf->Cell($leftW * 0.45, 8, $othersLines[$k] ?? '', 1, 1, 'L');
                    $ry += 8;
                }
                continue;
            } else {
                if (isset($paymentForm[$row['key']]) && $paymentForm[$row['key']] !== '') {
                    $v = $paymentForm[$row['key']];
                    $val = is_numeric($v) ? $this->money((float)$v) : (string)$v;
                }
                $pdf->Cell($leftW * 0.45, 8, $val, 1, 1, 'R');
                $ry += 8;
            }
        }

        // Total Payment row (bottom of the box)
        $pdf->SetXY($leftX, $yAfterTable + $leftH - 10);
        $pdf->SetFont('Helvetica', 'B', 9);
        $pdf->Cell($leftW * 0.55, 10, 'Total Payment', 1, 0, 'L');
        $pdf->SetFont('Helvetica', '', 9);
        $totalPayment = isset($paymentForm['total']) && is_numeric($paymentForm['total']) ? $this->money((float)$paymentForm['total']) : '';
        $pdf->Cell($leftW * 0.45, 10, $totalPayment, 1, 1, 'R');

        // Three-column bottom section (Payment form | Middle labels | Right values)
        // Equal-width columns, aligned to items table edges (from $tableX to $tableX + $colTotalW)
        $midX = $leftX + $leftW;         // middle column start (exactly after left column)
        $midW = $colW;                   // equal middle width
        $rtX  = $midX + $midW;           // right column start
        $rtW  = $colTotalW - ($leftW + $midW); // ensure rightmost edge aligns with items table

        $pdf->SetFont('Helvetica', '', 9);
        $lineGap = 6;
        $yyMid = $yAfterTable + 2;
        $yyRt  = $yAfterTable + 2;

        // Helpers
        $lineLabel = function(Fpdi $p, float $x, float $y, string $label, float $w, ?string $value = null) {
            $p->SetFont('Helvetica','',9);
            $p->SetXY($x, $y);
            $lblW = $p->GetStringWidth($label) + 2;
            $p->Cell($lblW, 6, $label, 0, 0, 'L');
            // underline
            $p->Line($x + $lblW + 1, $y + 4.5, $x + $w - 2, $y + 4.5);
            if ($value !== null && $value !== '') {
                $p->SetXY($x, $y);
                $p->Cell($w - 2, 6, $value, 0, 0, 'R');
            }
        };
        $rightLine = function(Fpdi $p, float $x, float $y, string $label, float $w, ?string $value = null) {
            $leftW = $w * 0.5;
            $p->SetXY($x, $y);
            $p->Cell($leftW, 6, $label, 0, 0, 'L');
            // underline for value field
            $p->Line($x + $leftW + 2, $y + 4.5, $x + $w - 2, $y + 4.5);
            if ($value !== null && $value !== '') {
                $p->SetXY($x, $y);
                $p->Cell($w, 6, $value, 0, 0, 'R');
            }
        };

        // Middle column labels with lines
        $lineLabel($pdf, $midX, $yyMid, 'Total Sales (Vat Included)', $midW); $yyMid += $lineGap;
        $lineLabel($pdf, $midX, $yyMid, 'Less VAT', $midW); $yyMid += $lineGap;
        $lineLabel($pdf, $midX, $yyMid, 'Amount Net of VAT', $midW); $yyMid += $lineGap + 2;
        $lineLabel($pdf, $midX, $yyMid, 'Less SC/PWD Discount', $midW); $yyMid += $lineGap;
        $lineLabel($pdf, $midX, $yyMid, 'Add VAT', $midW); $yyMid += $lineGap;
        $lineLabel($pdf, $midX, $yyMid, 'Less Withholding Tax', $midW); $yyMid += $lineGap + 2;
        $lineLabel($pdf, $midX, $yyMid, 'Vatable', $midW, $this->money($vatable)); $yyMid += $lineGap;
        $lineLabel($pdf, $midX, $yyMid, 'Vat Exempt Sale', $midW); $yyMid += $lineGap;

        // Right column numeric values with lines
        $rightLine($pdf, $rtX, $yyRt, 'Vat Zero Rated Sale', $rtW, $vatZeroRated > 0 ? $this->money($vatZeroRated) : ''); $yyRt += $lineGap;
        $rightLine($pdf, $rtX, $yyRt, 'Total Sale', $rtW, $this->money($totalSalesVatIncl)); $yyRt += $lineGap;
        $rightLine($pdf, $rtX, $yyRt, 'Value Added tax', $rtW, ($vat > 0 ? $this->money($vat) : '')); $yyRt += $lineGap;
        $rightLine($pdf, $rtX, $yyRt, 'Less EWT', $rtW, $lessEwt > 0 ? $this->money($lessEwt) : ''); $yyRt += $lineGap;
        $pdf->SetFont('Helvetica', 'B', 10);
        $rightLine($pdf, $rtX, $yyRt, 'Total Amount Due', $rtW, $this->money($totalAmountDue)); $yyRt += $lineGap + 2;
        $pdf->SetFont('Helvetica', '', 9);

        // Signature/info lines on right column (bold labels)
        $pdf->SetFont('Helvetica', 'B', 9);
        $rightLine($pdf, $rtX, $yyRt, 'OSCA / PWD ID NO.', $rtW, ''); $yyRt += $lineGap;
        $rightLine($pdf, $rtX, $yyRt, 'SC / PWD TIN:', $rtW, '');     $yyRt += $lineGap;
        $rightLine($pdf, $rtX, $yyRt, 'Solo Parent I.D No.', $rtW, ''); $yyRt += $lineGap;
        $rightLine($pdf, $rtX, $yyRt, 'SC / PWD SIGNATURE', $rtW, ''); $yyRt += $lineGap + 2;
        $pdf->SetFont('Helvetica', '', 9);

        // Reservation signature line (when requested) under the items table on left side
        // Skip this if we already drew the signature along with the stamp above.
        if (!empty($showReservationSignature) && empty($stampSignatureDrawn)) {
            $sigY = $yAfterTable - 6;
            $pdf->SetDrawColor(0, 0, 0);
            $pdf->SetLineWidth(0.2);
            $pdf->Line(18, $sigY, 90, $sigY);
            $pdf->SetFont('Helvetica', '', 8.8);
            $this->text($pdf, 18, $sigY + 2, 'SIGNATURE', 'C', 72);
        }

        // Right-side signature block: position under the right column (below SC / PWD SIGNATURE)
        // Company name under SC/PWD SIGNATURE, centered within the right column
        $companyY = $yyRt + 6;
        $pdf->SetFont('Helvetica', 'B', 9);
        $this->text($pdf, $rtX, $companyY, $companyName, 'C', $rtW);

        if (!empty($footerName)) {
            // Authorized signer name and line inside the right column
            $pdf->SetFont('Helvetica', 'B', 11);
            $this->text($pdf, $rtX, $companyY + 10, strtoupper((string)$footerName), 'C', $rtW);

            $pdf->SetDrawColor(0, 0, 0);
            $pdf->SetLineWidth(0.2);
            $lineLeft  = $rtX + 4;
            $lineRight = $rtX + $rtW - 4;
            $lineY     = $companyY + 13.5;
            $pdf->Line($lineLeft, $lineY, $lineRight, $lineY);

            $pdf->SetFont('Helvetica', 'I', 9);
            $this->text($pdf, $rtX, $lineY + 4.0, 'Authorized Signature', 'C', $rtW);
        }

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
