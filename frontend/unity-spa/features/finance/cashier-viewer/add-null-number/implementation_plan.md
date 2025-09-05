# Implementation Plan

[Overview]
Enable Cashier users to add a payment in the Cashier Viewer without assigning an OR or Invoice number at creation time. This is achieved by introducing a "none" numbering mode that submits a valid payment while leaving both OR and Invoice unassigned for later assignment.

This change aligns the UI with current backend capabilities. The backend already supports a "mode" field that can be "or", "invoice", or "none" (per CashierPaymentStoreRequest). When "none" is used, the server persists payment_details without consuming an OR/Invoice number or setting invoice_number/or_no, while still recording the payment and allowing assignment later via separate processes.

[Types]  
Introduce a third mode option on the frontend: 'none' to represent "no number assigned yet".

Type specifications and validations:
- payment.mode: string, enum: 'or' | 'invoice' | 'none'
- When mode === 'none':
  - Do not send invoice_id/invoice_number (omit or ensure null).
  - No number is assigned on the server. No pointer increments occur.
- When mode !== 'or' (i.e., mode === 'invoice' or 'none'), clear invoice selection in the UI to prevent accidental linking.

[Files]
Modify the Cashier Viewer UI and controller only; backend already supports "none" and requires no changes.

Detailed breakdown:
- Existing files to be modified:
  - frontend/unity-spa/features/finance/cashier-viewer/cashier-viewer.html
    - Add a new option to the "Numbering Mode" select:
      - value="none", label="No number yet (assign later)"
    - Show a small helper hint when mode === 'none' explaining that no OR/Invoice will be assigned now.
    - Keep the existing behavior that invoice selection dropdown is visible only for mode === 'or'.
  - frontend/unity-spa/features/finance/cashier-viewer/cashier-viewer.controller.js
    - Expand validation and submission logic to support mode === 'none'.
    - vm.canSubmitPayment(): include 'none' as an allowed mode.
    - vm.submitPayment(): pass 'none' through to backend instead of coercing to 'or'.
    - vm.onModeChange(): ensure invoice selection is cleared when mode !== 'or' (current logic already does this; verify compatibility with 'none').
    - Ensure invoice selection is not submitted when mode === 'none' (omit invoice_id and invoice_number).

- No files to be created for backend or services.
- No files to be deleted or moved.
- No configuration updates.

[Functions]
Extend existing functions in the Cashier Viewer controller to support the "none" mode without assigning numbers.

Detailed breakdown:
- Modified functions:
  - vm.canSubmitPayment() in cashier-viewer.controller.js
    - Current: modeOk := (p.mode === 'or' || p.mode === 'invoice')
    - Change: modeOk := (p.mode === 'or' || p.mode === 'invoice' || p.mode === 'none')
  - vm.submitPayment() in cashier-viewer.controller.js
    - Current: payload.mode = (p.mode === 'invoice' ? 'invoice' : 'or')
    - Change: payload.mode = (p.mode === 'invoice' ? 'invoice' : (p.mode === 'none' ? 'none' : 'or'))
    - When mode === 'none': do not include invoice_id and invoice_number in payload.
  - vm.onModeChange() in cashier-viewer.controller.js
    - Current: when mode !== 'or', clear invoice selection.
    - Confirm/retain: 'none' triggers the same clearing behavior; no further changes needed.
- New functions: None
- Removed functions: None

[Classes]
No class definitions are added or removed. AngularJS controller remains the same; only method logic and template bindings change.

[Dependencies]
No new dependencies. Uses existing services:
- CashiersService.createPayment (unchanged)
- UnityService (unchanged)
Backend already supports:
- App\Http\Requests\Api\V1\CashierPaymentStoreRequest -> mode in: or,invoice,none.
- App\Http\Controllers\Api\V1\CashierController::createPayment (handles 'none').

[Testing]
Manual validation and light integration checks using existing endpoints.

Test cases:
- UI:
  - Numbering Mode shows: OR, Invoice, No number yet (assign later).
  - When "No number yet" is selected, invoice selection UI disappears and hint displays.
- Submissions:
  - Submit with mode='none' and valid amount/description/remarks/mode_of_payment_id:
    - Expect 201 with success; backend response includes "mode":"none" and no number consumption.
    - Verify payment appears in the Payments table (payment_details) without or_no and without invoice_number set.
  - Submit with mode='or' and with optional invoice selected (existing behavior unchanged, capped by remaining).
  - Submit with mode='invoice' (existing behavior unchanged).
- Regressions:
  - Amount clamping, 2-decimal behavior continue to work.
  - canSubmitPayment still enforces required fields.
  - Invoices list and remaining computations unaffected.

[Implementation Order]
Implement UI/controller changes first, then test end-to-end.

1. Update cashier-viewer.controller.js:
   - Modify vm.canSubmitPayment() to accept 'none'.
   - Modify vm.submitPayment() to pass 'none' through and omit invoice fields when mode === 'none'.
   - Verify vm.onModeChange() clears invoice when mode !== 'or'.
2. Update cashier-viewer.html:
   - Add "none" option to Numbering Mode select.
   - Add helper text when mode === 'none'.
3. Manual testing in the browser:
   - Add a payment with each mode (or, invoice, none).
   - Confirm backend persisted rows and that no number is consumed for 'none'.
4. Verify no console errors, and the Payments table shows the new entry correctly without a number for 'none'.
