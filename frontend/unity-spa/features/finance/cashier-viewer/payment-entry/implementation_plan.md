# Implementation Plan

[Overview]
Add a cashier payment entry capability directly on the Finance Cashier Viewer page so authorized users can post a student payment into payment_details using the next OR/Invoice number reserved for the acting cashier, then automatically increment the cashier pointer and refresh the UI (payments, ledger, and tuition summaries).

This feature reuses the existing backend endpoint POST /api/v1/cashiers/{id}/payments and CashiersService.createPayment(). On the viewer, an "Add Payment" form will be shown to Finance/Admin users. The form will capture minimal required fields (mode, amount, description, remarks, method optional, posted_at optional) and will derive student_id, term (SYID), and campus context (student campus). After a successful entry, the page reloads payment details and dependent summaries. The UI will automatically detect the acting cashier based on the logged-in faculty_id (X-Faculty-ID) by filtering existing cashiers; if none is found, the form is disabled with a helpful notice. Route middleware will be adjusted to allow finance role for the payment create endpoint to match product requirements.

[Types]  
Introduce a controller-local model for payment entry and cashier resolution.

Detailed types and structures:
- PaymentEntryModel (controller-local)
  - mode: 'or' | 'invoice' (default 'or', required)
  - amount: number (required, > 0)
  - description: string (required, max 255; e.g., 'Tuition Payment' or 'Reservation Payment')
  - remarks: string (required, max 1000)
  - method: string | null (optional, max 100)
  - posted_at: string | null (optional, ISO 8601 or 'Y-m-d H:i:s')
- CashierResolution
  - myCashier: { id: number, campus_id?: number, or: { start?: number, end?: number, current?: number }, invoice: { start?: number, end?: number, current?: number } } | null
  - error: string | null (error or warning message if myCashier cannot be resolved)
- Backend Request (CashierPaymentStoreRequest payload)
  - student_id: number (required; tb_mas_users.intID)
  - term: number (required; SYID)
  - mode: 'or' | 'invoice' (required)
  - amount: number (required; maps to payment_details.subtotal_order)
  - description: string (required)
  - remarks: string (required)
  - method: string | null (optional)
  - posted_at: string | null (optional)
  - campus_id: number | null (optional; use the student campus when available)

Validation rules and relationships:
- Only enable submit if:
  - vm.student exists with id (student_id), and vm.term selected
  - myCashier resolved
  - amount > 0, description non-empty, remarks non-empty
  - mode is either 'or' or 'invoice'
- campus_id must be sourced from student campus per requirement. If not available in current response shape, omit (backend treats it as optional).

[Files]
Modify the Cashier Viewer controller and template; ensure backend route allows finance role; no new services required.

Detailed breakdown:
- Existing files to be modified
  - laravel-api/routes/api.php
    - Update middleware on POST /api/v1/cashiers/{id}/payments to include role finance in addition to cashier_admin and admin.
      - From: middleware('role:cashier_admin,admin')
      - To: middleware('role:cashier_admin,finance,admin')
  - frontend/unity-spa/features/finance/cashier-viewer/cashier-viewer.controller.js
    - Add: cashier resolution via CashiersService.list(); select the row where faculty_id == loginState.faculty_id; cache in vm.myCashier.
    - Add: vm.payment (PaymentEntryModel) state and defaults.
    - Add: vm.canSubmitPayment() computed guard.
    - Add: vm.submitPayment() to call CashiersService.createPayment(myCashier.id, payload) and chain UI refresh (reload payments, ledger, tuition summary).
    - Add: vm.resetPaymentForm() to clear form after success.
    - Add: ensure amount_paid/remaining is recomputed after successful payment.
  - frontend/unity-spa/features/finance/cashier-viewer/cashier-viewer.html
    - Add: "Add Payment" collapsible panel within the Payments card (above the table) visible if vm.canEdit and vm.myCashier resolved.
    - Fields: [Mode (radio or select: OR/invoice default=OR), Amount (number), Description (text), Remarks (textarea, required), Method (text optional), Date (optional)].
    - Disable submit if !vm.canSubmitPayment() or vm.loading.*.
    - Show inline validation and helpful notice if myCashier is missing or term/student not selected.
    - After submit, show success/failure states consistent with existing UI patterns.
- Files to be created
  - None (reusing existing services and components).
- Files to be deleted or moved
  - None.
- Configuration file updates
  - None.

[Functions]
Add new controller functions and integrate into the bootstrap flow; reuse existing backend and service methods.

Detailed breakdown:
- New functions (controller)
  - loadMyCashier(): Promise<void> — file: frontend/unity-spa/features/finance/cashier-viewer/cashier-viewer.controller.js
    - Purpose: Resolve the acting cashier by listing cashiers and filtering with loginState.faculty_id; assign to vm.myCashier or record an error.
  - canSubmitPayment(): boolean — same file
    - Purpose: Return true only when required fields and contexts (student, term, myCashier) are satisfied and not loading.
  - submitPayment(): Promise — same file
    - Signature: function submitPayment()
    - Purpose: Build payload and call CashiersService.createPayment(vm.myCashier.id, payload); on success, clear form and force-refresh vm.loadPaymentDetails(true), vm.loadLedger(), and vm.refreshTuitionSummary().
  - resetPaymentForm(): void — same file
    - Purpose: Reset vm.payment to defaults (mode='or', amount=null, description='', remarks='', method=null, posted_at=null).
- Modified functions
  - Bootstrap chain: Insert loadMyCashier step after loadStudents and before loading payment details to ensure button gating.
  - loadPaymentDetails(force): No signature change; after submitPayment(), call with force=true and recompute totals.
  - refreshTuitionSummary(): Invoked post-submit to reflect updated amount_paid/remaining.
- Removed functions
  - None.

[Classes]
No new classes. Controller remains a function-based AngularJS controller with additional methods and state.

Detailed breakdown:
- Modified classes
  - CashierViewerController (frontend/unity-spa/features/finance/cashier-viewer/cashier-viewer.controller.js)
    - Add payment entry state, cashier resolution, submit handlers, and guards.

[Dependencies]
No new external packages.

Details:
- Reuse CashiersService.createPayment(cashierId, payload).
- Reuse StudentController show response as the source for student.id; use UnityService.paymentDetails for lists.
- Update route middleware to include 'finance' role on the payment endpoint.

[Testing]
Integrate manual acceptance and targeted API checks.

Test file requirements and validation strategies:
- UI (manual)
  - As finance:
    - Open /#/finance/cashier/:id with a valid student and selected term; ensure "Add Payment" form appears.
    - Post a payment: mode=OR, amount>0, description='Tuition Payment', remarks='Test entry'; expect success toast (or visual feedback), payment details refresh with new row, totals updated, remaining reduced, and ledger reflects change (if comparable).
    - Switch to Invoice and repeat.
    - If no assigned cashier (faculty_id not mapped), form is disabled with notice.
  - As registrar:
    - "Add Payment" form should not be visible (read-only).
  - As admin:
    - Same behavior as finance with access.
- Backend
  - Verify POST /api/v1/cashiers/{id}/payments accepts X-Faculty-ID for finance role after middleware update.
  - Check that payment_details row is created with the correct number column (or_no/or_number or invoice_number), subtotal_order, status='Paid', sy_reference=SYID, and remarks/method/date mapped when present.
  - Check cashier pointer incremented by 1.
- Edge cases
  - Missing student campus: omit campus_id; API should still succeed.
  - Current pointer outside range or already used: API returns 422; UI surfaces error and does not refresh lists.

[Implementation Order]
Implement in a sequence that minimizes breakage and allows quick validation.

1) Backend route middleware
   - Update laravel-api/routes/api.php to allow finance role on POST /cashiers/{id}/payments: middleware('role:cashier_admin,finance,admin').
2) Controller changes (cashier-viewer.controller.js)
   - Add vm.myCashier resolution (loadMyCashier) based on loginState.faculty_id via CashiersService.list().
   - Add vm.payment model with defaults and guards (canSubmitPayment, resetPaymentForm).
   - Add submitPayment() implementation to call CashiersService.createPayment and then force-refresh: loadPaymentDetails(true) → loadLedger() → refreshTuitionSummary().
   - Insert loadMyCashier into bootstrap sequence.
3) Template changes (cashier-viewer.html)
   - Add "Add Payment" panel in Payments section; display only if vm.canEdit and vm.myCashier; provide field validation, disable submit when invalid or loading; show warnings if myCashier not found.
4) Validation and UX polish
   - Disable submit until student, term, mode, amount, description, remarks are set.
   - Provide inline error display on API failures (e.g., number conflict).
5) Manual test across roles
   - Confirm finance and admin can add payment; registrar sees read-only.
   - Confirm calculations update post-submit (payment details total, remaining, and ledger comparison).
