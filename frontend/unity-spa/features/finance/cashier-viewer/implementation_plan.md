# Implementation Plan

[Overview]
Add an Assign Number action in the Payments list of the Finance Cashier Viewer that opens an Assign Number modal to assign either an OR or an Invoice number to a specific payment row, using the acting cashier’s next available sequence or a custom number.

This update targets the Payments panel in the Cashier Viewer (frontend/unity-spa/features/finance/cashier-viewer/cashier-viewer.html) where payment_details rows are shown. A per-row button will open a new modal allowing Finance/Admin users to assign an OR or Invoice number to that payment, showing the next available number from the user’s resolved cashier (vm.myCashier) with an optional override. The modal will follow the SPA’s Tailwind/Angular overlay pattern (like the existing Tuition and Invoice modals), ensuring no Bootstrap dependency. After successful assignment, the UI will refresh the payment details and update any dependent totals or invoice caps.

[Types]  
Controller-local UI state and models will be extended to support assign-number workflow.

- AssignNumberModel (controller-local):
  - type: 'or' | 'invoice' | null
  - customNumber: number | null
- AssignNumberContext:
  - payment: PaymentDetailsRow | null
  - error: string | null
  - loading: boolean
- PaymentDetailsRow (already exists from UnityService.paymentDetails):
  - id?: number
  - or_no?: string|number|null
  - invoice_number?: string|number|null
  - subtotal_order: number
  - description: string
  - or_date?: string
  - posted_at?: string
  - status: 'Paid' | string
  - method?: string
  - sy_reference?: string|number
- CashierResolution (existing):
  - myCashier: { id: number, or?: { start?: number, end?: number, current?: number }, invoice?: { start?: number, end?: number, current?: number } } | null

Validation rules:
- type must be 'or' or 'invoice'
- customNumber is optional; if provided, must be positive integer within cashier’s allowed range (client-side soft check; backend authoritative)
- payment must be a valid payment_details row lacking the corresponding number type (e.g., no or_no for assigning OR, no invoice_number for assigning Invoice)

[Files]
Add a button in the Payments table and integrate a Tailwind modal; adjust the controller to handle state and API interaction.

- New files to be created:
  - None required (the existing assign-number-modal.html won’t be used as-is due to Bootstrap; a Tailwind modal will be embedded in cashier-viewer.html)

- Existing files to be modified:
  - frontend/unity-spa/features/finance/cashier-viewer/cashier-viewer.html
    - Add per-row “Assign Number” action button in the Payments table rows where assignment is applicable.
    - Add a new modal overlay (Angular ng-if block) for Assign Number, visually consistent with existing modals (Tuition/Invoice modals).
    - Modal fields: Type radio (OR/Invoice), read-only next-available info based on vm.myCashier, optional custom number input, validation/display messages, Cancel/Confirm buttons.
  - frontend/unity-spa/features/finance/cashier-viewer/cashier-viewer.controller.js
    - Add UI state: vm.ui.showAssignNumberModal
    - Add model: vm.assignNumber = { type: null, customNumber: null }
    - Add context: vm.assignNumberPayment = null, vm.assignNumberError = null
    - Add loading flag: vm.loading.assignNumber = false
    - Add functions:
      - vm.canShowAssignButton(p): boolean — returns true when current user can edit, a cashier is resolved, payment row exists, and the selected number type is missing (logic: show if p has neither or_no nor invoice_number, or at least the target type is missing)
      - vm.openAssignNumberModal(payment, preferredType?): void
      - vm.closeAssignNumberModal(): void
      - vm.hasAvailableNumbers(): boolean — checks vm.myCashier.{or|invoice}.current and range presence
      - vm.canAssignNumber(): boolean — validate type, cashier present, and payment row present
      - vm.confirmAssignNumber(): Promise<void> — call service to assign number and refresh UI
    - Add API integration: use Admin Payment Details service endpoint to assign number (see Dependencies). Fallback to a generic PUT/PATCH against a dedicated endpoint if provided.

- Files to be deleted or moved:
  - None

- Configuration file updates:
  - None

[Functions]
Add controller functions to drive the Assign Number UX and API call.

- New functions:
  - openAssignNumberModal(payment: PaymentDetailsRow, preferredType?: 'or'|'invoice'): void
    - Purpose: Initialize vm.assignNumberPayment with the chosen payment row, preselect type based on current vm.payment.mode (if meaningful) or leave null, then set vm.ui.showAssignNumberModal = true.
  - closeAssignNumberModal(): void
    - Purpose: Reset assign-number state and close modal.
  - hasAvailableNumbers(): boolean
    - Purpose: Check current type selection against vm.myCashier sequences; truthy when a current value exists.
  - canAssignNumber(): boolean
    - Purpose: Guard confirm button; requires canEdit, myCashier set, a payment selected, a type selected, and either next-available exists or a valid custom number is entered.
  - confirmAssignNumber(): Promise<void>
    - Purpose: Build payload { type: 'or'|'invoice', number?: customNumber } and submit via service; on success: close modal; refresh payment details and recompute caps/totals.

- Modified functions:
  - loadPaymentDetails(force): no signature changes; after successful assignment, call with force=true in confirmAssignNumber flow to refresh table.
  - recomputeInvoicesPayments(): will naturally re-run after loadPaymentDetails(true).
  - computeInvoiceRemaining(): ensure recalc if current selection is linked to an invoice, after assignment.

- Removed functions:
  - None

[Classes]
No class changes are required (AngularJS controller functions only).

- New classes:
  - None

- Modified classes:
  - None

- Removed classes:
  - None

[Dependencies]
No new package dependencies. Use existing Admin Payment Details service or UnityService if it exposes a suitable endpoint.

- Expected API integration:
  - Prefer a Payment Details API to assign a number:
    - Endpoint example: PATCH /api/v1/payment-details/{id}/assign-number
    - Payload: { type: 'or'|'invoice', number?: integer }
    - Response: updated payment_details row
  - If not present, use an existing service under features/admin/payment-details/payment-details.service.js, e.g., PaymentDetailsService.assignNumber(id, payload).
  - If neither exists, implement a fallback using $http.patch with the known backend route (to be validated during implementation).

[Testing]
Manual testing in the SPA to validate number assignment and UI refresh.

- Tests:
  - Payment row without or_no/invoice_number shows “Assign Number” button (for Finance/Admin and when myCashier resolved).
  - Clicking button opens modal; type selection and next-available numbers display correctly.
  - Entering a custom number works; leaving blank uses next available.
  - Confirm assigns the number; row updates with new or_no or invoice_number; totals unchanged except constraints (no regression).
  - For invoice-linked payments, ensure invoice remaining caps recalculate as expected post-refresh.
  - Guard rails:
    - No button for users without canEdit or without myCashier.
    - No button when both or_no and invoice_number already set.
    - Error message shows when backend fails; modal remains open and allows retry/cancel.

[Implementation Order]
Implement UI changes and controller wiring before wiring API call, then test end-to-end.

1. Controller state: add vm.ui.showAssignNumberModal, vm.assignNumber, vm.assignNumberPayment, vm.assignNumberError, vm.loading.assignNumber.
2. Controller functions: openAssignNumberModal, closeAssignNumberModal, hasAvailableNumbers, canAssignNumber, confirmAssignNumber (with empty $http call stub initially).
3. HTML: add per-row “Assign Number” button in the Payments table; add a Tailwind modal block similar to existing modals (ng-if on vm.ui.showAssignNumberModal).
4. Wire API call in confirmAssignNumber using the appropriate service or $http to backend endpoint; handle success/error, close modal, refresh payment details, recompute caps.
5. Manual validation across different payment rows and roles.
