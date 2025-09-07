# Invoice PDF: Reservation Payment Offset

Goal:
- On invoice printing, fetch the sum of Reservation Fee payments for the same student and term (syid).
- Append a negative line item "Reservation Payment" with the summed amount.
- New total shown in PDF = old total - reservation sum.
- No DB schema changes.

Scope:
- Laravel API only, print time (PDF endpoint).
- File to edit: laravel-api/app/Http/Controllers/Api/V1/InvoiceController.php

Plan / Tasks:
- [ ] Add import for App\Services\PaymentDetailAdminService in InvoiceController.
- [ ] In pdf($id): after items/total are resolved but before DTO is built:
  - [ ] Use PaymentDetailAdminService::detectColumns() to safely resolve `payment_details` table/columns.
  - [ ] Sum Reservation payments:
        WHERE student_id = invoice.student_id
          AND sy_reference = invoice.syid
          AND status = 'Paid'
          AND description LIKE 'Reservation%'
        SUM(subtotal_order)
  - [ ] If sum > 0, push negative line:
        { description: 'Reservation Payment', qty: 1, price: -sum, amount: -sum }
  - [ ] Adjust total variable: total = total - sum
  - [ ] Wrap with try/catch and column guards to avoid errors when table/columns are missing.
- [ ] Keep InvoicePdf renderer unchanged.
- [ ] Test cases:
  - [ ] Invoice with no Reservation payments: unchanged output.
  - [ ] Invoice with Reservation payments only: one negative line printed; total reduced accordingly.
  - [ ] Invoice with existing positive Reservation charge line (billing): negative line appears to offset payments; final total reflects payments.
  - [ ] Environments without payment_details or columns: PDF endpoint still works; no offset injected.

Notes:
- Follow existing FinanceService heuristics that use sy_reference == term (syid) in this environment.
- Do not mutate invoice DB rows; change is purely presentational at PDF time.
