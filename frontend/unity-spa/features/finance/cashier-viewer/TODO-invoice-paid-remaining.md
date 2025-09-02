# Cashier Viewer — Invoices: Display Amount Paid and Remaining

Goal: On the Cashier Viewer page, under the Invoices section, display the Amount Paid and Remaining for each invoice.

Information Gathered:
- cashier-viewer.html currently shows Invoices table with columns: Date, Invoice, Type, Status, Amount, Remarks.
- cashier-viewer.controller.js provides:
  - vm.loadInvoices() — loads and sorts invoices.
  - vm.loadPaymentDetails() — loads payment_details including items and meta.total_paid_filtered.
  - vm.computeInvoiceRemaining() — computes remaining for the currently selected invoice (for payment cap) by summing Paid payment_details with matching invoice_number.

Approach:
- Compute paid and remaining per invoice using existing payment_details data, and attach to each invoice object for display.

Implementation Plan:
1) Controller
   - Add utility `vm.recomputeInvoicesPayments()`:
     - Build a map of `invoice_number` => sum(subtotal_order) for `payment_details.items` with `status === 'Paid'`.
     - For each invoice:
       - Resolve `invNo = inv.invoice_number || inv.number`.
       - Resolve `total = first numeric of [inv.amount_total, inv.amount, inv.total]` (nullable).
       - `paid = map[invNo] || 0`.
       - `remaining = (total != null) ? max(total - paid, 0) : null`.
       - Attach to invoice: `inv._total`, `inv._paid`, `inv._remaining`.
   - Invoke `vm.recomputeInvoicesPayments()` in:
     - `vm.loadInvoices()` after `vm.invoices` is assigned (if paymentDetails already loaded).
     - `vm.loadPaymentDetails()` after `vm.paymentDetails` and `vm.paymentDetailsTotal` are computed (definitive recompute).
     - After successful `vm.submitPayment()` refresh chain (post `loadPaymentDetails(true)`), ensure it recomputes (it will via `loadPaymentDetails`; add a safe call as well).

2) Template
   - Update Invoices table header to add:
     - Paid (right-aligned)
     - Remaining (right-aligned)
   - In each invoice row, render:
     - Paid: `P{{ vm.currency(inv._paid || 0) }}`
     - Remaining: `P{{ vm.currency(inv._remaining || 0) }}` with:
       - Red text if remaining > 0
       - Gray text if remaining === 0

3) Edge Cases &amp; Fallbacks
   - If `paymentDetails` not yet loaded, `_paid` defaults to 0; `_remaining` equals `_total` if available, otherwise null.
   - If invoice has no numeric total, `_remaining` stays null and the cell shows `P0.00` (can be refined later if needed).
   - Overpayments are clamped at remaining = 0 for display.

Testing:
- Select term with invoices and payment details: verify Paid and Remaining per row.
- Generate a new tuition invoice: verify it appears and shows correct totals (Paid 0, Remaining = total).
- Post a payment linked to an invoice: verify the Paid and Remaining update.
- Terms without invoices: show empty-state message.

Status:
- [ ] Add vm.recomputeInvoicesPayments()
- [ ] Call from loadInvoices()
- [ ] Call from loadPaymentDetails()
- [ ] Optional call after submitPayment() refresh chain
- [ ] Update HTML headers for Invoices
- [ ] Update HTML rows to render Paid and Remaining
- [ ] Manual verification
