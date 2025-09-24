<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;

/**
 * PaymentDetailsTemplateExport
 *
 * Builds the Payment Details import template Spreadsheet.
 * Headers are aligned with schema-safe mapping handled in PaymentDetailAdminService
 * and the planned PaymentDetailsImportService.
 *
 * Columns:
 * - id (optional; when present, updates the existing payment_details row)
 * - student_number (required)
 * - syid (optional; used for invoice creation when needed)
 * - description (optional; used for invoice type classification)
 * - subtotal_order (optional; used as payment amount and basis for invoice amount)
 * - total_amount_due (optional; written if column exists; not used for invoice amount)
 * - method (optional) OR payment_method (optional)
 * - mode_of_payment_id (optional)
 * - status (optional)
 * - posted_at (optional; datetime)
 * - or_no (optional) OR or_number (optional)
 * - invoice_number (optional; if provided and missing in invoices, an invoice is created)
 * - remarks (optional)
 */
class PaymentDetailsTemplateExport
{
    /**
     * Build and return the template Spreadsheet instance.
     */
    public function build(): Spreadsheet
    {
        $ss = new Spreadsheet();
        $sheet = $ss->getActiveSheet();
        $sheet->setTitle('payment_details');

        // Header row
        $headers = [
            'id',
            'student_number',
            'syid',
            'description',
            'subtotal_order',
            'total_amount_due',
            'method',
            'payment_method',
            'mode_of_payment_id',
            'status',
            'posted_at',
            'or_no',
            'or_number',
            'invoice_number',
            'remarks',
        ];

        $col = 1;
        foreach ($headers as $h) {
            $sheet->setCellValueByColumnAndRow($col, 1, $h);
            $sheet->getStyleByColumnAndRow($col, 1)->getFont()->setBold(true);
            $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
            $col++;
        }

        // Add a couple of sample rows (non-bold)
        $r = 2;
        $sheet->setCellValue('A' . $r, ''); // id blank means insert
        $sheet->setCellValue('B' . $r, '2020-00001');
        $sheet->setCellValue('C' . $r, '20241'); // example syid
        $sheet->setCellValue('D' . $r, 'Reservation Payment');
        $sheet->setCellValue('E' . $r, '5000');
        $sheet->setCellValue('F' . $r, '');
        $sheet->setCellValue('G' . $r, 'Cash');
        $sheet->setCellValue('H' . $r, '');
        $sheet->setCellValue('I' . $r, '1');
        $sheet->setCellValue('J' . $r, 'Paid');
        $sheet->setCellValue('K' . $r, '2025-01-15 10:00:00');
        $sheet->setCellValue('L' . $r, ''); // or_no
        $sheet->setCellValue('M' . $r, ''); // or_number
        $sheet->setCellValue('N' . $r, '8200001'); // invoice_number (will auto-create invoice if missing)
        $sheet->setCellValue('O' . $r, 'Sample reservation');

        $r++;
        $sheet->setCellValue('A' . $r, ''); // insert
        $sheet->setCellValue('B' . $r, '2020-00002');
        $sheet->setCellValue('C' . $r, '20241');
        $sheet->setCellValue('D' . $r, 'Tuition Partial Payment');
        $sheet->setCellValue('E' . $r, '3500.50');
        $sheet->setCellValue('F' . $r, '');
        $sheet->setCellValue('G' . $r, 'Online');
        $sheet->setCellValue('H' . $r, '');
        $sheet->setCellValue('I' . $r, '2');
        $sheet->setCellValue('J' . $r, 'Paid');
        $sheet->setCellValue('K' . $r, '2025-01-20 09:30:00');
        $sheet->setCellValue('L' . $r, '');
        $sheet->setCellValue('M' . $r, '');
        $sheet->setCellValue('N' . $r, '8200002'); // invoice_number
        $sheet->setCellValue('O' . $r, 'Tuition payment');

        // Notes sheet
        try {
            $notes = $ss->createSheet();
            $notes->setTitle('Notes');
            $notes->setCellValue('A1', 'Payment Details Import Instructions');
            $notes->getStyle('A1')->getFont()->setBold(true);
            $row = 3;
            $notes->setCellValue('A2', 'Required: student_number. Upsert: provide id to update, leave blank to insert.');
            $notes->setCellValue('A' . $row++, 'Invoice auto-creation: if invoice_number provided and not found:');
            $notes->setCellValue('A' . $row++, '  - Type is based on description: "Application Payment" => application payment; "Reservation Payment" => reservation payment;');
            $notes->setCellValue('A' . $row++, '    contains "Tuition" => tuition; else => billing. Status = Issued.');
            $notes->setCellValue('A' . $row++, '  - Invoice amount_total comes from subtotal_order. syid is used if present.');
            $notes->setCellValue('A' . $row++, 'On insert, OR number uniqueness is pre-validated if an OR column exists.');
            $notes->setCellValue('A' . $row++, 'Date/Time: posted_at should be "YYYY-MM-DD HH:MM:SS".');
            $notes->setCellValue('A' . $row++, 'Only student_number is required; the rest are optional and mapped schema-safely.');
        } catch (\Throwable $e) {
            // ignore if cannot create notes sheet
        }

        return $ss;
    }
}
