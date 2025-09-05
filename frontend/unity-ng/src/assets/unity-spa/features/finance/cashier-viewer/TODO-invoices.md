# Cashier Viewer — Invoices List (before Payments)

Goal: Show a list of invoices before the Payments area in the Cashier Viewer.

Information Gathered:
- UnityService exposes invoicesList(params) and invoicesGenerate(payload).
- Cashier Viewer already loads student, registration, tuition, ledger, and payment details; it also handles tuition invoice check/generation.
- cashier-viewer.html currently has no invoices list; Payments card follows Tuition Summary.
- Registration provides intRegistrationID which is useful to filter tuition-related invoices; student.id and term are available.

Implementation Plan:

1) Controller (cashier-viewer.controller.js)
   - [ ] Add state:
     - [ ] vm.invoices = []
     - [ ] vm.loading.invoices, vm.error.invoices
     - [ ] extend vm._last with invoices cache key
   - [ ] Add vm.loadInvoices(force):
     - [ ] Params: { term }, plus student_id when available, and registration_id when available
     - [ ] Call UnityService.invoicesList(params), normalize to array (data or data.items)
     - [ ] Sort desc by posted_at/created_at/updated_at/id
     - [ ] Store into vm.invoices; update loading/error and _last cache
   - [ ] Trigger load:
     - [ ] In bootstrap chain, after loadTuition() and before loadPaymentDetails()
     - [ ] On term change sequence (onTermChange), insert vm.loadInvoices between vm.loadTuition and vm.loadPaymentDetails
   - [ ] After vm.generateTuitionInvoice() success, call vm.loadInvoices(true) to refresh list

2) Template (cashier-viewer.html)
   - [ ] Insert a new “Invoices” card ABOVE the Payments card.
   - [ ] Show loading state, error, and a table with:
     - Date (posted_at or created_at)
     - Invoice Number
     - Type
     - Status
     - Amount (total/amount/subtotal/total_amount)
     - Remarks (optional)
   - [ ] When no invoices: show “No invoices found for the selected term.”

Files to change:
- frontend/unity-spa/features/finance/cashier-viewer/cashier-viewer.controller.js
- frontend/unity-spa/features/finance/cashier-viewer/cashier-viewer.html

Verification:
- Select a term with/without invoices and verify correct render.
- Generate a tuition invoice and confirm it appears in the list.
- Submit a payment to ensure no regressions in Payments panel; invoices list should remain intact.
