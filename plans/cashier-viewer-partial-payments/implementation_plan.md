# Implementation Plan

[Overview]
Display a computed Installment Plan panel on the Payments section of the Cashier Viewer when the selected student’s registration paymentType is 'partial'. The panel will show the Down Payment and a list of five equal installments (amounts sourced from tuition.summary.installments) alongside the Payments box, without altering existing payment creation workflows.

This enhancement improves cashier visibility when handling installment-paying students. It uses the same computed tuition payload already available on the page (vm.tuitionPayload()), avoids backend changes, and keeps the UI consistent with existing Tailwind-styled panels. The plan ensures the panel appears only when paymentType === 'partial' and installment data is available in the tuition summary.

[Types]  
Introduce lightweight controller-local helpers and view-model shape to expose installment data clearly.

- Existing payload (from TuitionBreakdownResource):
  - Path: vm.tuitionPayload().summary.installments
  - Expected keys (standard plan):
    - down_payment: number
    - installment_fee: number
    - total_installment: number

- New controller-local type shapes (JS-doc style):
  - InstallmentPlanEntry:
    - key: string               // 'dp' or 'i1'..'i5'
    - label: string             // 'Down Payment', 'Installment 1'..'Installment 5'
    - amount: number            // Derived from summary.installments
  - InstallmentPlan:
    - dp: number                // summary.installments.down_payment
    - fee: number               // summary.installments.installment_fee
    - total: number             // summary.installments.total_installment
    - entries: InstallmentPlanEntry[]  // [DP, Inst1..Inst5]

- Validation rules/assumptions:
  - Only show when (registration.paymentType || edit.paymentType) === 'partial'
  - Only show when tuitionPayload().summary.installments exists and numbers are finite
  - Default missing values to 0 for display; hide panel when summary.installments is absent

[Files]
Modify the Cashier Viewer controller and template to expose and render the installment plan panel in the Payments section.

- New files to be created:
  - None

- Existing files to be modified:
  - frontend/unity-spa/features/finance/cashier-viewer/cashier-viewer.controller.js
    - Add helpers:
      - vm.shouldShowInstallmentPanel(): boolean
      - vm.installmentsSummary(): { dp:number, fee:number, total:number } | null
      - vm.installmentsList(): InstallmentPlanEntry[] (DP + 5 installments)
    - Ensure helpers are safe against missing tuition/summary/installments and run after tuition and registration are loaded.
    - Keep helpers side-effect free and pure for digest-friendly rendering.

  - frontend/unity-spa/features/finance/cashier-viewer/cashier-viewer.html
    - In the Payments section, wrap the internal content with a responsive grid:
      - Left area (md:col-span-2): Existing content (Cashier numbers, Add Payment form, totals, payments table)
      - Right area (md:col-span-1): New "Installment Plan" side panel:
        - Header: "Installment Plan"
        - Rows:
          - Down Payment: ₱ {vm.installmentsSummary().dp | number:2}
          - Installment 1..5: ₱ {vm.installmentsSummary().fee | number:2} each
          - Footer card: Total Installment: ₱ {vm.installmentsSummary().total | number:2}
        - Guard by vm.shouldShowInstallmentPanel()

- Files to be deleted or moved:
  - None

- Configuration file updates:
  - None

[Functions]
Add minimal, single-purpose helpers to expose a safe, digest-friendly data shape for the view.

- New functions (frontend/unity-spa/features/finance/cashier-viewer/cashier-viewer.controller.js):
  - CashierViewerController.shouldShowInstallmentPanel(): boolean
    - Purpose: Return true when paymentType === 'partial' and tuitionPayload().summary.installments exists.
    - Logic:
      - const pt = (vm.registration && vm.registration.paymentType) || vm.edit.paymentType || null;
      - if (pt !== 'partial') return false;
      - const p = vm.tuitionPayload(); return !!(p && p.summary && p.summary.installments);

  - CashierViewerController.installmentsSummary(): { dp:number, fee:number, total:number } | null
    - Purpose: Extract numeric down_payment, installment_fee, and total_installment; return null when unavailable.
    - Behavior: parseFloat; fallback to 0 in UI; keep controller return null if not available.

  - CashierViewerController.installmentsList(): InstallmentPlanEntry[]
    - Purpose: Produce a 6-row list: Down Payment + 5 installments.
    - Behavior: Use installmentsSummary(); if null, return [].
      - [{ key:'dp', label:'Down Payment', amount:dp },
         { key:'i1', label:'Installment 1', amount:fee }, ... 'i5' ]

- Modified functions:
  - None required (helpers are additive).

- Removed functions:
  - None

[Classes]
No class-level changes. AngularJS controller is function-scoped; helpers are simple function declarations on vm.

- New classes: None
- Modified classes: None
- Removed classes: None

[Dependencies]
No new dependencies or backend changes.

- Data source: vm.tuitionPayload().summary.installments (already computed/fetched).
- Registration context: vm.registration.paymentType or vm.edit.paymentType (already loaded).

[Testing]
Manual validation on the Cashier Viewer with a student whose registration.paymentType is 'partial'.

- Preconditions:
  - User has Finance/Admin role (for visibility of Payments box; panel display does not require canEdit but page access context assumed).
  - Selected Term is resolved; tuition has been loaded (tuition.compute and/or tuition.saved).

- Test cases:
  1) paymentType = 'partial', tuition summary has installments:
     - Payments section displays a right-side panel titled "Installment Plan".
     - Shows Down Payment with correct amount.
     - Shows exactly 5 Installment rows with the same per-installment amount.
     - Shows Total Installment matching summary.installments.total_installment.
  2) paymentType = 'full':
     - The Installment Plan side panel is hidden.
  3) Missing summary.installments:
     - The Installment Plan side panel is hidden with no errors in console.
  4) Snapshot vs computed:
     - Panel reflects whichever payload vm.tuitionPayload() resolves to (saved or computed).
  5) Regression:
     - Add Payment form, payments list, invoices panel, and other cards remain visually and functionally identical.
     - Responsive layout: on md and up, panel shows at right; on small screens, it stacks below/above content gracefully.

[Implementation Order]
Implement helpers first, then wire the template, then validate layout and data-driven guards.

1. Controller: add vm.shouldShowInstallmentPanel, vm.installmentsSummary, vm.installmentsList (pure helpers; safe parsing).
2. Template: in Payments card body, wrap existing content in a grid (md:grid-cols-3). Move current content into md:col-span-2; add right column for the Installment Plan with ng-if="vm.shouldShowInstallmentPanel()".
3. Template: render values using helpers; format via number:2 and existing vm.currency where applicable.
4. Manual QA across:
   - Student with partial vs full paymentType
   - With and without tuitionSaved payload
   - With/without summary.installments (simulate by preventing tuition load or using a student with incomplete data).
5. Visual polish: ensure spacing, colors, and font sizes align with existing Tailwind utility classes in the page; no overflow at 900x600 viewport.
