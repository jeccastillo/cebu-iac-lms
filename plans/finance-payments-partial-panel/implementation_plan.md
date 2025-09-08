# Implementation Plan

[Overview]
Display a computed Installment Plan panel within the Payments section of the Cashier Viewer that appears when the selected student’s registration paymentType is 'partial'. The panel must show the Down Payment and exactly five equal installment entries alongside the Payments box, using the already-available tuition breakdown payload.

This feature improves cashier visibility and guidance during payment posting for installment-paying students. It uses the existing tuition compute/saved payloads and registration context already loaded on the page, requires no backend changes, and follows existing AngularJS + Tailwind patterns used across the Cashier Viewer. The panel must render only when paymentType === 'partial' and when installments data exists in tuition summary.

[Types]  
No framework type system changes. We will use controller-local data shapes for clarity and safety.

- Existing payload from TuitionBreakdownResource:
  - Source: vm.tuitionPayload().summary.installments
  - Expected keys:
    - down_payment: number
    - installment_fee: number
    - total_installment: number

- Controller-local shapes (conceptual):
  - InstallmentPlanSummary:
    - dp: number                // from installments.down_payment
    - fee: number               // from installments.installment_fee
    - total: number             // from installments.total_installment
  - InstallmentPlanEntry:
    - key: 'dp' | 'i1' | 'i2' | 'i3' | 'i4' | 'i5'
    - label: 'Down Payment' | 'Installment N'
    - amount: number

Validation/assumptions:
- Render panel only if (registration.paymentType || edit.paymentType) === 'partial'
- Render panel only if tuitionPayload().summary.installments is present
- When available, amounts are parsed as floats; non-finite are treated as 0 only for display
- Exactly five installment entries (DP + 5x installments), as specified

[Files]
The feature modifies only the Cashier Viewer front-end.

- New files to be created:
  - None

- Existing files to be modified:
  - frontend/unity-spa/features/finance/cashier-viewer/cashier-viewer.controller.js
    - Provide or ensure presence of controller helpers:
      - vm.shouldShowInstallmentPanel(): boolean
      - vm.installmentsSummary(): InstallmentPlanSummary | null
      - vm.installmentsList(): InstallmentPlanEntry[] (DP + 5x installments)
    - Helpers must be safe against missing tuition/summary/installments, with no side effects.
    - Ensure recomputation hooks run whenever tuition/registration updates (e.g., after loads and option saves).

  - frontend/unity-spa/features/finance/cashier-viewer/cashier-viewer.html
    - In the Payments card body, render a right-hand Installment Plan panel using Tailwind classes.
    - Guard the panel with ng-if="vm.shouldShowInstallmentPanel()".
    - Content:
      - Down Payment amount
      - Exactly five installment items (equal amounts)
      - Footer card showing total_installment

- Files to be deleted or moved:
  - None

- Configuration updates:
  - None

[Functions]
Minimal, pure helpers exposed on vm for the template.

- New or ensured functions (frontend/unity-spa/features/finance/cashier-viewer/cashier-viewer.controller.js):
  - shouldShowInstallmentPanel(): boolean
    - Purpose: visible only when registration/edit paymentType is 'partial' and tuitionPayload().summary.installments exists.
    - Logic:
      - const pt = (vm.registration && vm.registration.paymentType) || vm.edit.paymentType || null;
      - if (pt !== 'partial') return false;
      - const p = vm.tuitionPayload(); return !!(p && p.summary && p.summary.installments);

  - installmentsSummary(): { dp:number, fee:number, total:number } | null
    - Purpose: map summary.installments fields to dp/fee/total or return null if not present.
    - Behavior: parseFloat; ignore non-finite by returning 0 at display-time only.

  - installmentsList(): InstallmentPlanEntry[]
    - Purpose: produce 6 entries: Down Payment + 5 equal installments (exactly five).
    - Behavior:
      - Use installmentsSummary() for dp/fee.
      - Return:
        - [{key:'dp', label:'Down Payment', amount:dp},
           {key:'i1', label:'Installment 1', amount:fee}, ... , {key:'i5', label:'Installment 5', amount:fee}]

- Modified functions:
  - _recomputeInstallmentsPanel(): If already present, ensure the internal precomputed UI model reflects DP + 5 installments and is called after tuition/registration changes.
  - refreshTuitionSummary(): Ensure it invokes installment recomputation hook after totals selection is resolved.

- Removed functions:
  - None

[Classes]
No classes involved (AngularJS controller is a function). All changes are controller-scoped helpers and view bindings.

- New classes: None
- Modified classes: None
- Removed classes: None

[Dependencies]
No additional dependencies required.

- Data dependencies: 
  - Registration: vm.registration.paymentType (or vm.edit.paymentType while editing)
  - Tuition payload: vm.tuitionPayload().summary.installments (from /tuition/compute and/or saved snapshot)
- No backend or package changes.

[Testing]
Manual UI validation using a Finance/Admin user.

- Preconditions:
  - A student and term selection that loads registration and tuition payloads
  - A registration row where paymentType is 'partial'
  - A tuition payload where summary.installments exists

- Test cases:
  1) paymentType = 'partial', installments present:
     - Installment Plan panel appears at the right of Payments box.
     - Down Payment amount displays and matches installments.down_payment.
     - Exactly 5 installment rows display; each equals installments.installment_fee.
     - Total Installment equals installments.total_installment.
  2) paymentType = 'full':
     - Installment Plan panel is hidden.
  3) Missing installments:
     - Installment Plan panel is hidden; no console errors.
  4) Saved vs computed:
     - Panel reflects whichever payload vm.tuitionPayload() currently returns (saved snapshot preferred).
  5) Regression:
     - Payments creation form and list are not affected.
     - Page remains responsive; panel stacks properly on small screens.

[Implementation Order]
Perform controller helper implementation first, then template wiring, then validate.

1. Controller: add/ensure vm.shouldShowInstallmentPanel, vm.installmentsSummary, vm.installmentsList; guard all data access defensively.
2. Controller: add/ensure vm._recomputeInstallmentsPanel integrates these helpers and is called after tuition/registration loads or updates.
3. Template: add/confirm the right-hand Installment Plan panel with ng-if="vm.shouldShowInstallmentPanel()".
4. Template: render amounts via number:2; use provided helpers; ensure exactly five installment rows.
5. Manual QA across partial vs full, with and without saved tuition payload, and responsive breakpoints to confirm layout and visibility rules.
