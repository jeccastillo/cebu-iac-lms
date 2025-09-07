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
        $number      = $dto['number'] ?? null;
        $date        = $dto['date'] ?? null;
        $studentName = (string) ($dto['student_name'] ?? '');
        $studentNumber = (string) ($dto['student_number'] ?? '');
        $termLabel   = (string) ($dto['term_label'] ?? '');
        $items       = is_array($dto['items'] ?? null) ? $dto['items'] : [];
        $total       = (float) ($dto['total'] ?? 0);
        $footerName  = $dto['footer_name'] ?? null;
        // Reservation signature toggle (for reservation invoices)
        $showReservationSignature = !empty($dto['reservation_signature']);
        // Optional: amount paid (first tuition payment) to display on PDF as per spec
        $amountPaidFirstTuition = null;
        if (isset($dto['amount_paid_first_tuition']) && is_numeric($dto['amount_paid_first_tuition'])) {
            $amountPaidFirstTuition = (float) $dto['amount_paid_first_tuition'];
        }

        $pdf = new Fpdi('P', 'mm', 'A4');
        $pdf->AddPage('P', 'A4');
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->SetTextColor(0, 0, 0);

        // Header
        $pdf->SetFont('Helvetica', '', 8.8);
        $this->text($pdf, 14, 47, $studentNumber.' '.strtoupper($studentName));
        // if ($termLabel !== '') {
        //     $this->text($pdf, 10, 22, $termLabel);
        
        
        $pdf->SetFont('Helvetica', '', 9);
        $this->text($pdf, 165, 47, ($date ?: ''), 'R', 25);

        $pdf->SetFont('Helvetica', '', 9);
        $this->text($pdf, 128, 35, 'Invoice No:', 'R', 30);
        $pdf->SetFont('Helvetica', '', 9);
        $this->text($pdf, 144, 35, ($number === null || $number === '') ? '-' : (string) $number, 'R', 25);

        
        

        // Items header
        $yStart = 57.5;
        // $pdf->SetFont('Helvetica', 'B', 8.5);
        // $this->text($pdf, 10, $yStart, 'DESCRIPTION');
        // $this->text($pdf, 142, $yStart, 'QTY', 'R', 15);
        // $this->text($pdf, 162, $yStart, 'PRICE', 'R', 25);
        // $this->text($pdf, 187, $yStart, 'AMOUNT', 'R', 25);

        // Items body
        $y = $yStart + 7;
        $pdf->SetFont('Helvetica', '', 8.8);
        $lineH = 6;

        foreach ($items as $line) {
            $desc = isset($line['description']) ? (string) $line['description'] : '';
            $qty  = isset($line['qty']) && is_numeric($line['qty']) ? (float) $line['qty'] : 1.0;
            $price = isset($line['price']) && is_numeric($line['price'])
                ? (float) $line['price']
                : (isset($line['amount']) ? (float) $line['amount'] : 0.0);
            $amount = isset($line['amount']) && is_numeric($line['amount'])
                ? (float) $line['amount']
                : ($qty * $price);

            // Note-only line support: when note_only=true, show description only and skip qty/price/amount
            $noteOnly = !empty($line['note_only']);
            if ($noteOnly) {
                $pdf->SetXY(1.5, $y);
                // Wider description cell when rendering a note-only line
                $pdf->Cell(168, $lineH, $this->truncate($desc, 110), 0, 1, 'L');
            } else {
                // Description (truncate softly to fit one line)
                $pdf->SetXY(1.5, $y);
                $pdf->Cell(128, $lineH, $this->truncate($desc, 80), 0, 0, 'L');

                // Qty
                $pdf->SetXY(125, $y);
                $pdf->Cell(20, $lineH, rtrim(rtrim(number_format($qty, 2, '.', ''), '0'), '.'), 0, 0, 'L');

                // Price
                $pdf->SetXY(140, $y);
                $pdf->Cell(30, $lineH, $this->money($price), 0, 0, 'L');

                // Amount
                $pdf->SetXY(170, $y);
                $pdf->Cell(30, $lineH, $this->money($amount), 0, 1, 'L');
            }

            $y += $lineH;
            if ($y > 230) {
                $pdf->AddPage('P', 'Letter');
                $y = 20;
            }
        }

        // Signature line for reservation invoices
        if (!empty($showReservationSignature)) {
            $sigY = $y + 4;
            if ($sigY > 230) {
                $pdf->AddPage('P', 'Letter');
                $sigY = 20;
            }
            $pdf->SetDrawColor(0, 0, 0);
            $pdf->SetLineWidth(0.2);
            // Draw line on the left-half section
            $pdf->Line(12, $sigY, 80, $sigY);
            $pdf->SetFont('Helvetica', '', 8.8);
            // Center the label under the line
            $this->text($pdf, 12, $sigY + 2, 'SIGNATURE', 'C', 68);
            // Advance cursor below the signature block
            $y = $sigY + 6;
        }

        // Total section
        $y += 4;                

        // Echo totals similar to the screenshot (visual anchors)
        $pdf->SetFont('Helvetica', '', 9);
        $this->text($pdf, 170, 95,  $this->money($total), 'L', 25);
        $this->text($pdf, 170.5, 109,  $this->money($total), 'L', 25);
        $this->text($pdf, 80, 131,  $this->money($total), 'L', 25);

        // Show the first Tuition payment amount (e.g., "13,358.88") at a left anchor position
        // Coordinates tuned to appear between the middle and bottom totals per provided screenshot
        if ($amountPaidFirstTuition !== null) {
            $pdf->SetFont('Helvetica', '', 9);
            $this->text($pdf, 25.5, 102.5, $this->money($amountPaidFirstTuition), 'L', 25);
            $this->text($pdf, 12, 151.5, $this->money($amountPaidFirstTuition), 'L', 25);
        }

        // Footer signature name (optional)
        if (!empty($footerName)) {
            $pdf->SetFont('Helvetica', 'B', 10);
            $this->text($pdf, 150, 152, strtoupper($footerName), 'L', 60);
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
