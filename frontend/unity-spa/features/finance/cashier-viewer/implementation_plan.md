# Implementation Plan

[Overview]
Add a “Use” button next to the first non-zero amount in the Installment Plan (Down Payment or first remaining Installment) on the Cashier Viewer so that, when a Tuition invoice is selected, clicking the button copies that amount into the Add Payment form’s Amount input, enforcing existing sanitation and clamping rules.

This enhancement improves cashier workflow by enabling one-click entry of the next due amount in a partial-payment plan. The button should appear only when a Tuition invoice is selected (i.e., an invoice is chosen and its type is tuition, and invoice selection is only possible in OR mode per existing controller logic). The client already computes the “first due” using the Installment Panel logic; we will surface a contextual “Use” button inline where that due amount is shown. On click, the controller will copy the value into vm.payment.amount (truncated to 2 decimals) and run existing clamping (respect invoice remaining, maximum amounts, etc.).

[Types]  
No new global types. Add one small optional helper in the controller for UI gating.

Detailed type definitions:
- Controller-local helper (optional):
  - canUseFirstDue(): boolean
    - Returns true when:
      - A Tuition invoice is currently selected (vm.selectedInvoiceIsTuition() === true).
      - A first due object exists (vm.installmentsFirstDue()).
      - The first due amount is a finite number > 0.

Existing structures already present and used in this plan:
- vm.installmentsUI: { show: boolean, summary: { dp, fee, total }, list: Array<{ key, label, amount }>, firstDue: { key, label, amount } | null }
- vm.installmentsFirstDue(): () => { key, label, amount } | null
- vm.useFirstDueAmount(): () => void
- vm.selectedInvoiceIsTuition(): () => boolean
- vm.payment: { mode: 'or'|'invoice'|'none', amount, invoice_id, invoice_number, ... }
- vm.onAmountChange(): () => void
- vm.amountMax(): () => number|null

[Files]
Add a “Use” button inline within the Installment Plan panel.

- New files to be created:
  - None.

- Existing files to be modified:
  - frontend/unity-spa/features/finance/cashier-viewer/cashier-viewer.html
    - In the Installment Plan section:
      - Show a small “Use” button adjacent to the first non-zero amount:
        - If first due is Down Payment (dp), render the “Use” button next to the Down Payment value in the top two-column summary.
        - If first due is one of i1..i5, render the “Use” button inline on that list row (the row whose it.key equals firstDue.key).
      - Visibility/enablement:
        - Render the button only when a Tuition invoice is selected (vm.selectedInvoiceIsTuition()).
        - Button disabled if vm.installmentsFirstDue() is null or amount <= 0.
      - Action: ng-click="vm.useFirstDueAmount()".
    - No layout breakage to other panels. Use existing Tailwind utility classes for minimal UI affordance (small inline button).
  - frontend/unity-spa/features/finance/cashier-viewer/cashier-viewer.controller.js
    - Optional: add vm.canUseFirstDue() as a thin wrapper around:
      - vm.selectedInvoiceIsTuition()
      - vm.installmentsFirstDue() presence with amount > 0
    - Retain and reuse existing:
      - vm.installmentsFirstDue()
      - vm.useFirstDueAmount() (already truncates via Math.floor(x*100)/100 and invokes vm.onAmountChange()).
      - vm.selectedInvoiceIsTuition() (already checks current selection and invoice type).

- Files to be deleted or moved:
  - None.

- Configuration file updates:
  - None.

[Functions]
The “Use” button will call a controller function that already exists.

- New functions:
  - Optional: canUseFirstDue(): boolean
    - File: cashier-viewer.controller.js
    - Signature: vm.canUseFirstDue = function() { ... }
    - Purpose: Centralize the gating logic for the “Use” button and avoid template duplication:
      - return !!(vm.selectedInvoiceIsTuition &amp;&amp; vm.selectedInvoiceIsTuition() &amp;&amp; vm.installmentsFirstDue &amp;&amp; vm.installmentsFirstDue() &amp;&amp; isFinite(parseFloat(vm.installmentsFirstDue().amount)) &amp;&amp; parseFloat(vm.installmentsFirstDue().amount) > 0);

- Modified functions:
  - useFirstDueAmount(): void (already implemented)
    - File: cashier-viewer.controller.js
    - Behavior (existing and correct):
      - Reads vm.installmentsFirstDue().amount
      - Requires Tuition invoice selected via vm.selectedInvoiceIsTuition()
      - Sets vm.payment.amount = floor(amount*100)/100
      - Invokes vm.onAmountChange() to clamp to invoice remaining and normalize.

- Removed functions:
  - None.

[Classes]
No classes introduced or modified. Changes are constrained to the AngularJS controller functions and the HTML template.

- New classes:
  - None.

- Modified classes:
  - None.

- Removed classes:
  - None.

[Dependencies]
No new runtime or build dependencies.

- AngularJS and Tailwind are already in use in the feature.
- No backend changes required (client-only UX improvement).

[Testing]
Manual test scenarios to validate behavior and guardrails.

- Preconditions:
  - User has Finance/Admin role (vm.canEdit === true).
  - Partial payment plan is active and Installment Plan panel is visible (vm.shouldShowInstallmentPanel()).
  - vm.installmentsUI.firstDue computed and reflects remaining DP or first unpaid installment.

- Cases:
  1) No invoice selected
     - The “Use” button does not appear.
  2) Non-tuition invoice selected
     - The “Use” button does not appear.
  3) Tuition invoice selected, first due is DP > 0
     - “Use” button appears next to Down Payment amount.
     - Click populates Amount, truncates to two decimals, clamps to invoice remaining via onAmountChange().
  4) Tuition invoice selected, DP covered, first due is Installment 1..5 > 0
     - “Use” button appears on the first such installment row.
     - Same behavior on click as above.
  5) First due amount is 0 or missing
     - “Use” button is hidden/disabled.
  6) Amount exceeds selected invoice remaining
     - After click, onAmountChange() clamps it down to remaining. No validation errors shown; standard warning text in HTML (existing) continues to apply.
  7) Switching invoice selection or payment mode
     - onModeChange clears invoice selection if mode != 'or'; the “Use” button disappears accordingly.

[Implementation Order]
Implement the template changes first and optionally add a tiny helper for clarity.

1) Template gating
   - In cashier-viewer.html, compute a local firstDue with ng-init="firstDue = vm.installmentsFirstDue()".
   - Add an inline button next to DP when firstDue.key === 'dp' and vm.selectedInvoiceIsTuition().
   - Add an inline button for the first matching list row when firstDue.key !== 'dp' and vm.selectedInvoiceIsTuition().
2) Hook the action
   - Wire ng-click="vm.useFirstDueAmount()".
3) Optional helper
   - Add vm.canUseFirstDue() if desired to centralize the gating condition and simplify the template.
4) UX polish
   - Use small Tailwind-styled button (“Use”) with subtle style (e.g., inline-flex px-2 py-1 text-xs).
5) Manual validation
   - Verify against all testing scenarios above to confirm visibility gates and clamping behavior.
