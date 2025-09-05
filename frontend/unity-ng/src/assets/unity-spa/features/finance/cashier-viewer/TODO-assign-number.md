# TODO — Cashier Viewer: Assign Number from Payments Table

Goal: Add an “Assign Number” action in the Payments table (cashier-viewer.html) to assign an OR or Invoice number to a payment row via a Tailwind modal, calling the AdminPaymentDetailsService.update PATCH endpoint, then refresh UI.

Plan (from implementation_plan.md):

- Add a new “Assign” column to the Payments table.
- Show an “Assign Number” button per row for Finance/Admin users when a cashier is resolved (vm.myCashier).
- Clicking opens a Tailwind modal to select number type (OR or Invoice), shows next available number from vm.myCashier, allows custom number entry (optional), validates, then assigns.
- On success: close modal, refresh Payment Details and Invoices, recompute invoice paid/remaining and any caps.

Steps:

1) Controller state and DI
- [X] Inject AdminPaymentDetailsService into CashierViewerController.
- [ ] Add UI state:
  - [ ] vm.ui.showAssignNumberModal = false
  - [ ] vm.assignNumber = { type: null, customNumber: null }
  - [ ] vm.assignNumberPayment = null
  - [ ] vm.assignNumberError = null
  - [ ] vm.loading.assignNumber = false

2) Controller functions
- [ ] vm.openAssignNumberModal(payment, preferredType?)
  - Initialize vm.assignNumberPayment and vm.assignNumber.
  - Clear previous errors; set vm.ui.showAssignNumberModal = true.
- [ ] vm.closeAssignNumberModal()
  - Reset state; hide modal.
- [ ] vm.hasAvailableNumbers()
  - Check vm.myCashier and selected type’s current pointer availability (or/invoice).
- [ ] vm.canAssignNumber()
  - Validate canEdit, myCashier present, payment selected, type chosen, not loading; optionally ensure availability or valid customNumber.
- [ ] vm.confirmAssignNumber()
  - Build payload:
    - If type='or': { or_no: customNumber || myCashier.or.current }
    - If type='invoice': { invoice_number: customNumber || myCashier.invoice.current }
  - Call AdminPaymentDetailsService.update(payment.id, payload).
  - On success: close modal, then force refresh flows:
    - [ ] vm.loadPaymentDetails(true)
    - [ ] vm.loadInvoices(true)
    - [ ] Recompute caps (e.g., vm.computeInvoiceRemaining()) if needed.

3) View updates — Payments table
- [ ] Add a new <th>Assign</th> column header.
- [ ] Add a new <td> with an “Assign Number” button per row:
  - Visible when vm.canEdit && vm.myCashier.
  - Button: ng-click="vm.openAssignNumberModal(p)".

4) View — Tailwind modal (Assign Number)
- [ ] Add modal markup similar to Tuition/Invoice modals:
  - [ ] Header: “Assign Number to Payment”
  - [ ] Payment info (date, amount, description).
  - [ ] Radio select for type (OR / Invoice) bound to vm.assignNumber.type.
  - [ ] Next available number display based on vm.myCashier pointer for the selected type with range info.
  - [ ] Custom Number (optional) input bound to vm.assignNumber.customNumber.
  - [ ] Warning if no numbers available (vm.hasAvailableNumbers() === false).
  - [ ] Footer:
    - [ ] Cancel → vm.closeAssignNumberModal()
    - [ ] Assign → vm.confirmAssignNumber() disabled when !vm.canAssignNumber() || vm.loading.assignNumber.

5) API wiring and refresh flow
- [ ] Use AdminPaymentDetailsService.update(payment.id, payload) to PATCH number assignment.
- [ ] On success:
  - [ ] Close modal.
  - [ ] Refresh payment details (force true).
  - [ ] Refresh invoices (force true).
  - [ ] Recompute per-invoice paid/remaining and update any active caps.

6) Manual tests
- [ ] Finance/Admin with vm.myCashier resolved:
  - [ ] Payments table shows Assign column and per-row button.
  - [ ] Assigning OR without custom number (uses next available) updates row.
  - [ ] Assigning Invoice without custom number updates row; verify invoice paid/remaining stays consistent.
  - [ ] Custom number within range updates row.
  - [ ] Validation errors from backend surface in modal.
- [ ] Registrar (no canEdit): no Assign column.
- [ ] No vm.myCashier: hide button (or disabled).
- [ ] After assignment, lists refresh and UI reflects new values.

Notes:
- Do not use the Bootstrap modal file (assign-number-modal.html). We will embed a Tailwind modal inline in cashier-viewer.html to stay consistent with current SPA modals.
- Backend is authoritative for number uniqueness and range checks; client performs soft validation and displays server errors.
