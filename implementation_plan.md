# Implementation Plan

[Overview]
Enhance the Registrar Transcript page to stop displaying a separate “Paid Billing — Transcript / Change of Grades” list and instead surface the payment status directly within the History table. The “Paid” status must be computed across all terms by matching invoices/billings for the same transcript type, and a new “Paid” column will be added to the History grid.

This change removes the per-term restriction for paid billing visibility and consolidates status into History entries. The backend will compute the paid flags (to avoid Registrar hitting finance-protected endpoints), using robust invoice payment checks that work across environments: an invoice is considered fully paid when the sum of payment_details amounts for its invoice_number is greater than or equal to invoice.amount_total OR invoice.status is “Paid.” The frontend will update the transcript History UI to show the Paid status and remove the separate paid billing section.

[Types]  
Add server-computed fields to Transcript History items returned by ReportsController@listTranscriptRequests.

Detailed type definitions:
- TranscriptHistoryItem (server response)
  - id: int
  - created_at: string
  - date_issued: string|null
  - type: 'transcript' | 'copy'
  - amount: number|null
  - payment_description_id: number|null
  - term_ids: number[]
  - has_billing: boolean           (existing)
  - billing_id: number|null        (existing)
  - [new] paid: boolean            (server-computed across all terms)
  - [new] paid_invoice_numbers?: string[] (optional; for visibility/debugging)
  - [new] paid_at?: string|null (optional; if determinable from latest payment or invoice posted_at)
Validation/relationships:
- Matching invoices via billing linkage: tb_mas_invoices.billing_id → tb_mas_student_billing.intID. The matched billing descriptions must be one of:
  - Transcript: “Transcript of Records”
  - Copy of Grades: “Copy of Grades”
- Fully paid check: SUM(payment_details.subtotal_order WHERE status='Paid' AND invoice_number = i.invoice_number) >= i.amount_total OR i.status == 'Paid'.

[Files]
Frontend transcript page and Reports endpoints will be modified; no new endpoints are required.

Detailed breakdown:
- Existing files to be modified:
  - laravel-api/app/Http/Controllers/Api/V1/ReportsController.php
    - listTranscriptRequests(): augment returned items with a new boolean field paid (computed across all terms).
    - Add private helpers to compute invoice paid state (across all terms) for the given student and type.
  - frontend/unity-spa/features/registrar/transcripts/transcripts.html
    - Remove the “Paid Billing — Transcript / Change of Grades” section entirely.
    - Add a new “Paid” column to the History table; show a badge “Paid” or “Unpaid”.
  - frontend/unity-spa/features/registrar/transcripts/transcripts.controller.js
    - Remove vm.billingPaid state and loadTranscriptBilling() logic, including invocations on student select/term change.
    - Keep History loading via ReportsService.listTranscriptRequests() (now includes paid flag).
  - frontend/unity-spa/features/registrar/reports.service.js
    - No API change; ensure client consumes the new paid field in the UI (no code change necessary beyond reading h.paid in controller/template).
- Files to be deleted or moved
  - None.
- Configuration file updates
  - None.

[Functions]
Add paid computation and remove per-term paid billing UI.

Detailed breakdown:
- New backend helpers (ReportsController.php)
  - private function isInvoiceFullyPaid(array $inv): bool
    - Logic:
      - If $inv['status'] == 'Paid' => return true.
      - Else if payment_details table and invoice_number column exist:
        - Sum paid credits: SUM(payment_details.subtotal_order WHERE status='Paid' AND invoice_number = i.invoice_number)
        - If sum >= $inv['amount_total'] => true; else false.
      - Else false.
  - private function anyFullyPaidInvoicesForType(int $studentId, string $type): array
    - Normalize $type: 'transcript' => 'Transcript of Records'; 'copy' => 'Copy of Grades'.
    - Query tb_mas_student_billing sb WHERE sb.intStudentID = :studentId AND LOWER(sb.description) = LOWER($desc).
    - Join tb_mas_invoices i ON i.billing_id = sb.intID AND i.intStudentID = :studentId (across all syid/terms).
    - For each i row, call isInvoiceFullyPaid(); collect matches and the invoice_numbers that are fully paid.
    - Return [ 'paid' => bool, 'invoice_numbers' => string[] ].
- Modified backend function
  - ReportsController::listTranscriptRequests(Request $request, int $studentId)
    - For each transcript request row:
      - Determine $type = transcript|copy.
      - Compute $paidInfo = anyFullyPaidInvoicesForType($studentId, $type).
      - Set item['paid'] = $paidInfo['paid'].
      - Optionally include item['paid_invoice_numbers'].
    - Preserve existing has_billing and billing_id behavior honoring optional ?term_id.
- Frontend controller/template changes
  - transcripts.controller.js
    - Remove:
      - vm.loading.billing, vm.error.billing, vm.billingPaid
      - vm.loadTranscriptBilling and calls (in selectStudent() and term change watcher)
    - History remains: vm.history returned items now include h.paid.
  - transcripts.html
    - Remove entire “Paid Billing — Transcript / Change of Grades” section.
    - In History grid header, insert a “Paid” column.
    - In History rows, render a badge:
      - if h.paid => green “Paid”
      - else => gray “Unpaid”

[Classes]
Add helper methods within an existing controller; no new classes required.

Detailed breakdown:
- New classes
  - None.
- Modified classes
  - App\Http\Controllers\Api\V1\ReportsController
    - Add two private helpers (isInvoiceFullyPaid, anyFullyPaidInvoicesForType).
    - Modify listTranscriptRequests() to enrich items with paid flag computed across all terms.
- Removed classes
  - None.

[Dependencies]
No new packages.

- Reuse:
  - Illuminate\Support\Facades\DB, Illuminate\Support\Facades\Schema (for safety across environments).
  - Existing tables: tb_mas_student_billing, tb_mas_invoices, payment_details.
  - Existing logic patterns from Finance/StudentLedger services for payment_details summation semantics (status='Paid', subtotal_order).

[Testing]
Add manual and targeted verification; automated tests if test harness is used.

- Backend:
  - Unit/Feature:
    - listTranscriptRequests returns paid=true when there exists at least one fully paid invoice for the matching type across any term.
    - paid detection falls back to invoice.status == 'Paid' if payment_details invoice_number is unavailable.
    - Ensure when no invoices exist, paid=false.
    - Preserve term_id behavior for has_billing/billing_id (does not affect paid).
- Frontend:
  - Transcript page displays History with Paid column; badges appear correctly.
  - The previous “Paid Billing — Transcript / Change of Grades” section is removed.
  - Selecting different terms (global term change) must not restrict paid computation; History paid status unchanged (computed on server across all terms).
  - Generation, reprint continue to function unchanged.

[Implementation Order]
Implement backend paid computation first, then update the frontend UI and controller to remove the paid billing section and display the new Paid column.

1) Backend: ReportsController
   1.1 Add private helper isInvoiceFullyPaid(array $inv): bool.
       - Check invoice.status
       - If available, sum payment_details.subtotal_order where status='Paid' and invoice_number matches; compare to amount_total
   1.2 Add private helper anyFullyPaidInvoicesForType(int $studentId, string $type): array
       - Map type => description ("Transcript of Records"|"Copy of Grades")
       - Join student_billing→invoices, fetch across all terms
       - Evaluate each invoice via isInvoiceFullyPaid; collect fully paid invoice_numbers
   1.3 Modify listTranscriptRequests()
       - For each item, compute paid info using anyFullyPaidInvoicesForType($studentId, $type)
       - Merge paid boolean and optional paid_invoice_numbers into response
       - Keep existing has_billing/billing_id behavior for provided ?term_id
2) Frontend: transcripts.html
   2.1 Remove the entire “Paid Billing — Transcript / Change of Grades” section
   2.2 Add a “Paid” column in the History header
   2.3 In row: render a badge Paid/Unpaid using h.paid
3) Frontend: transcripts.controller.js
   3.1 Remove billingPaid state and loaders:
       - vm.loading.billing, vm.error.billing, vm.billingPaid
       - function loadTranscriptBilling(studentId) and references
       - Stop invoking loadTranscriptBilling() on selectStudent and on termChanged
   3.2 Keep history load as-is; it will receive paid flags from backend
4) QA
   4.1 Seed test data: student billing rows for Transcript/Copy across two terms; invoices linked to some billings; payment_details entries for invoice_number partially and fully paying them
   4.2 Confirm History paid column reflects true if any fully paid invoice exists across all terms for the type
   4.3 Confirm removal of Paid Billing section and no per-term restriction remains in UI
