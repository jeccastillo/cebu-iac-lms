# Implementation Plan

[Overview]
Display Tuition invoices in the Cashier Viewer with the Reservation payments automatically subtracted from the invoice total, while leaving the PDF output unchanged. The offset equals the sum of Paid Payment Details for the same student and term whose description matches Reservation% (case-insensitive).

This change is a UI-only adjustment in the AngularJS Cashier Viewer. It does not modify invoice persistence, amounts stored in tb_mas_invoices, or the Invoice PDF. The cashier sees at-a-glance the effective total and remaining for Tuition invoices after applying Reservation payments collected for the term.

[Types]  
Add view-model fields to surface the reservation offset and effective totals.

- Controller VM augmentations (AngularJS)
  - vm.meta.reservation_paid: number
    - Sum of payment_details.subtotal_order for rows with:
      - status = 'Paid'
      - description LIKE 'Reservation%'
      - scoped to current student and term
    - Computed client-side from existing vm.paymentDetails.items
  - For each invoice in vm.invoices (display-only fields):
    - inv._total: number|null (existing computed total; already set)
    - inv._paid: number (existing paid-by-invoice sum; already set)
    - inv._remaining: number|null (existing; will remain but not used for Tuition effective display)
    - inv._reservation_applied: number
      - Same as vm.meta.reservation_paid (copied per-invoice to simplify template)
    - inv._total_effective: number
      - For Tuition only: max(0, inv._total - inv._reservation_applied)
      - For non-Tuition: equals inv._total
    - inv._remaining_effective: number
      - For Tuition only: max(0, inv._total_effective - inv._paid)
      - For non-Tuition: equals inv._remaining

- Invoice Modal totals (display object)
  - vm.invoiceModal.totals (existing) is extended logically:
    - total_effective: number (Tuition only; effective total after reservation offset)
    - reservation_applied: number (the applied reservation payments sum)
    - remaining_effective: number (effective remaining = total_effective - paid; clamped to 0)
  - Validation rules (display):
    - reservation_applied >= 0
    - total_effective, remaining_effective are clamped to 0 when negative

[Files]
Scope limited to Cashier Viewer UI. No backend or PDF renderer changes.

- Modified files:
  - frontend/unity-spa/features/finance/cashier-viewer/cashier-viewer.controller.js
    - Add a helper to compute vm.meta.reservation_paid from vm.paymentDetails.items.
    - In vm.loadPaymentDetails(): after setting vm.paymentDetails, compute and store vm.meta.reservation_paid.
    - In vm.recomputeInvoicesPayments(): for each invoice:
      - Detect Tuition invoices (type === 'tuition').
      - Set inv._reservation_applied = vm.meta.reservation_paid (default 0).
      - Compute inv._total_effective and inv._remaining_effective as per Types.
    - In vm.openInvoiceModal(inv): compute totals.total_effective, totals.reservation_applied, totals.remaining_effective for Tuition type and use those values in modal binding.
  - frontend/unity-spa/features/finance/cashier-viewer/cashier-viewer.html
    - In the Invoices panel rows:
      - Display effective totals/remaining for Tuition invoices (e.g., use inv._total_effective and inv._remaining_effective), while keeping non-Tuition unchanged.
      - Optionally add a small note below Tuition invoice amounts: “Reservation payments applied: -{{ inv._reservation_applied | currency }}”.
    - In the Invoice Details modal totals block:
      - Show “Total (after Reservation): {{ totals.total_effective | currency }}” for Tuition invoices.
      - Show “Reservation payments applied: -{{ totals.reservation_applied | currency }}” as a subtext.
      - Remaining should use remaining_effective for Tuition invoices.

- Not modified:
  - laravel-api/app/Http/Controllers/Api/V1/InvoiceController.php (PDF remains unchanged)
  - laravel-api/app/Services/* (no API shape changes)
  - laravel-api/app/Services/Pdf/InvoicePdf.php (no changes)

[Functions]
Add one helper function and modify two existing ones in the controller. No class additions.

- New functions
  - _sumReservationPaidFromPaymentDetails(): number
    - Location: cashier-viewer.controller.js (internal helper)
    - Purpose: Sum Paid reservation payments for the current term using vm.paymentDetails.items
    - Algorithm:
      - Iterate items
      - Skip rows where status !== 'Paid'
      - Normalize description to lower-case and match “reservation” prefix (existing _isReservationFeeDesc can be reused; matches 'reservation payment' or 'reservation fee') and fallback to .indexOf('reservation') === 0 if needed
      - Sum parseFloat(subtotal_order), skip NaN
      - Return 0 when no items

- Modified functions
  - vm.loadPaymentDetails(force)
    - After assigning vm.paymentDetails and computing vm.paymentDetailsTotal and vm.meta.billing_paid:
      - Compute vm.meta.reservation_paid = _sumReservationPaidFromPaymentDetails()
      - Default to 0 when not computable
      - Trigger vm.recomputeInvoicesPayments() to propagate effective totals
  - vm.recomputeInvoicesPayments()
    - Keep existing computation of inv._total, inv._paid, inv._remaining
    - New:
      - const res = isFinite(vm.meta.reservation_paid) ? vm.meta.reservation_paid : 0
      - For Tuition (inv.type === 'tuition'):
        - inv._reservation_applied = res
        - inv._total_effective = Math.max(0, (inv._total || 0) - res)
        - inv._remaining_effective = Math.max(0, (inv._total_effective || 0) - (inv._paid || 0))
      - For non-Tuition:
        - inv._reservation_applied = 0
        - inv._total_effective = inv._total
        - inv._remaining_effective = inv._remaining
  - vm.openInvoiceModal(inv)
    - After computing total and paid:
      - If Tuition: 
        - reservation = vm.meta.reservation_paid || 0
        - total_effective = Math.max(0, (total || 0) - reservation)
        - remaining_effective = Math.max(0, total_effective - paid)
        - Extend vm.invoiceModal.totals with { reservation_applied: reservation, total_effective, remaining_effective }
      - If not Tuition:
        - reservation_applied = 0
        - total_effective = total
        - remaining_effective = remaining

[Classes]
No new classes; augment existing Angular controller view-model only.

- Modified classes/components
  - AngularJS Controller: CashierViewerController (as above)
    - Add display-only fields on invoice objects (inv._reservation_applied, inv._total_effective, inv._remaining_effective)
  - AngularJS Template: Cashier Viewer HTML
    - Bind to new effective fields for Tuition invoice rows and modal

[Dependencies]
No new runtime dependencies.

- Reuse existing:
  - vm.paymentDetails loading via UnityService.paymentDetails()
  - Existing helpers: _isReservationFeeDesc, _isTuitionDesc

[Testing]
UI-focused validation in Cashier Viewer.

- Preconditions:
  - Select a student and term in which Reservation payments exist in payment_details for the same student and term
  - Ensure at least one Tuition invoice exists for the term

- Test cases:
  1) Tuition invoice, Reservation payments exist:
     - Expect per-invoice row to display:
       - Total (effective) = original total − sum(reservation payments), clamped at 0
       - Remaining (effective) = Total (effective) − Paid (for that invoice number), clamped at 0
       - A note shows “Reservation payments applied: -X.XX”
     - Invoice Details modal shows:
       - Total (after Reservation): X.XX (equals effective total)
       - Reservation payments applied: -Y.YY (the sum)
       - Remaining equals effective remaining

  2) Tuition invoice, no Reservation payments for the term:
     - Effective Total equals original total; note line may be hidden or shows “-0.00”

  3) Non-Tuition invoices (billing/others):
     - Display remains unchanged; no reservation offset applied

  4) Reservation payments exceed original Tuition total:
     - Effective Total displays 0.00
     - Effective Remaining displays 0.00

  5) Robustness:
     - payment_details missing some optional columns (already handled by service)
     - Mixed items and descriptions; only status='Paid' and Reservation% descriptions counted

- Regression:
  - Printing PDFs remains unchanged (no offset line, no total modification)
  - Existing per-invoice paid computations (by matching invoice_number) continue to work

[Implementation Order]
Implement front-end changes first, then verify with sample data.

1) Controller helper: add _sumReservationPaidFromPaymentDetails() to compute vm.meta.reservation_paid.
2) Modify vm.loadPaymentDetails() to compute and store vm.meta.reservation_paid, then trigger vm.recomputeInvoicesPayments().
3) Modify vm.recomputeInvoicesPayments() to compute and attach _reservation_applied, _total_effective, _remaining_effective.
4) Modify vm.openInvoiceModal() to compute totals.total_effective, totals.reservation_applied, totals.remaining_effective for Tuition invoices.
5) Update cashier-viewer.html to display effective totals/remaining and the reservation-applied note for Tuition invoices, and modal totals.
6) Manual QA in the Cashier Viewer against the five test cases above.
