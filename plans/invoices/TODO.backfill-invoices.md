# Backfill invoices from payment_details

Goal:
- Create a one-off script to generate tb_mas_invoices rows for invoice numbers appearing in payment_details that do not yet have a corresponding invoice.
- Ensure idempotency (safe dry-run, only create when --apply is provided).
- Respect data integrity: only create when a single student_information_id and a single term (sy_reference) can be determined for the invoice_number.

Scope:
- Laravel project: laravel-api
- New script: laravel-api/scripts/backfill_invoices_from_payment_details.php

Data sources:
- payment_details: columns commonly present: id, student_information_id, sy_reference, description, subtotal_order, status, or_date, invoice_number, cashier_id, campus_id, paid_at, date, created_at (presence guarded via Schema)
- tb_mas_invoices: target table for creation via App\Services\InvoiceService::generate()

High-level Behavior:
1) Scan payment_details for distinct non-empty invoice_number values.
2) For each invoice_number that does not exist in tb_mas_invoices:
   - Determine student_information_id (must be unique among its payment_details rows). If multiple or none: skip.
   - Determine term sy_reference (must be unique among its rows). If multiple and ambiguous: skip.
   - Derive earliest posted_at from available date columns: or_date, paid_at, date, created_at.
   - Optionally capture campus_id/cashier_id when a single distinct value is present; otherwise omit.
   - Compute paid total using InvoiceService::getInvoicePaidTotal(invoice_number). If zero, invoice status may default to Issued; if >0, set status=Paid.
   - Create invoice with type=other, status per above, invoice_number set to the exact value, posted_at=earliest date, remarks="Backfilled from payment_details", campus_id/cashier_id when available.
3) Default to DRY-RUN (no DB writes). Only create rows when --apply is provided.

CLI Options:
- --apply        Persist changes (otherwise DRY-RUN).
- --limit=N      Maximum number of invoices to create.
- --start=NNN    Restrict to invoice_number >= start (string/number; filter applied in PHP).
- --end=NNN      Restrict to invoice_number <= end (string/number; filter applied in PHP).
- --campus=ID    Restrict to payment_details rows with campus_id=ID (only when the column exists).
- --verbose      Increase logs per invoice number.
- --student=ID   Restrict to a specific student_information_id (optional).
- --term=SYID    Restrict to a specific term sy_reference (optional).

Validation Rules:
- Skip when:
  - payment_details table missing or required columns missing (id, student_information_id, description, subtotal_order, status, invoice_number).
  - Multiple distinct student_information_id for the same invoice_number.
  - Multiple distinct sy_reference for the same invoice_number (unless explicitly filtered by --term).
- Safe joins:
  - Existence of tb_mas_invoices row for invoice_number is always checked; do not create duplicates.
  - No cashier pointer changes are invoked; InvoiceService::generate() is used with explicit invoice_number.

Checklist:
- [ ] Create laravel-api/scripts/backfill_invoices_from_payment_details.php.
- [ ] Implement Laravel bootstrap (vendor/autoload + bootstrap/app + Kernel bootstrap).
- [ ] Parse CLI options: --apply, --limit, --start, --end, --campus, --verbose, --student, --term.
- [ ] Verify required tables exist (payment_details, tb_mas_invoices).
- [ ] Discover candidate invoice_numbers from payment_details (non-null, non-empty) with optional filters.
- [ ] For each candidate, verify no existing tb_mas_invoices row (invoice_number exact match).
- [ ] Aggregate and validate student_information_id and sy_reference.
- [ ] Resolve earliest posted_at from or_date/paid_at/date/created_at.
- [ ] Compute paid total via InvoiceService::getInvoicePaidTotal(invoice_number).
- [ ] Prepare options: amount, status, posted_at, remarks, campus_id, cashier_id, invoice_number.
- [ ] Dry-run logging of would-create rows.
- [ ] Apply creation via InvoiceService->generate('other', studentId, syid, options) when --apply is present.
- [ ] Summary print: scanned, skipped (with reason), created count, first/last created numbers.
- [ ] Test: run dry-run without flags, try with --limit, then with --apply on a small subset/range.
- [ ] Capture results in this document.

Usage Examples:
- Dry-run all:
  php laravel-api/scripts/backfill_invoices_from_payment_details.php

- Dry-run limited to 20:
  php laravel-api/scripts/backfill_invoices_from_payment_details.php --limit=20

- Apply for a range:
  php laravel-api/scripts/backfill_invoices_from_payment_details.php --start=800000 --end=899999 --apply

- Apply for specific campus id:
  php laravel-api/scripts/backfill_invoices_from_payment_details.php --campus=2 --apply

- Apply for a specific student and term:
  php laravel-api/scripts/backfill_invoices_from_payment_details.php --student=12345 --term=20241 --apply

Notes:
- If payment_details.invoice_number is not numeric, range filters are applied lexicographically in PHP (exact match creation check always uses the exact stored string).
- Status selection heuristic:
  - If paid total > 0: status='Paid'
  - Else: status='Issued'
- This script is idempotent per invoice_number; reruns will not recreate existing numbers.
