# Implementation Plan

[Overview]
Implement dynamic Tuition Year Installment Plans across the SPA so that the UI renders installment options coming from the backend payload instead of hardcoded 50% and 30% down payments. Apply this to the Cashier view and the Registrar Enlistment tuition preview, and fix the current error in the Enlistment page by removing legacy increaseInfo references and using the selected plan metadata.

This change scopes to frontend only and assumes the backend already provides tuition.summary.installments with a plans array and a selected_plan_id. The Cashier page must allow selection from available plans and compute per-registration remaining installments accordingly. The Enlistment tuition preview must render dynamic tabs from these plans and compute DP, per-installment fee, and total installment from the selected plan. All templates must handle variable installment_count values (not always 5). When no plans are returned, legacy display continues to work through existing guards in the controllers.

[Types]  
Define and use normalized data structures in controllers to read server-provided plans and compute display state.

Data structures (JS/AngularJS; documentation-only types):
- Backend Installments payload (read-only, from API):
  - tuition.summary.installments: {
      down_payment?: number,                // legacy fallback
      installment_fee?: number,             // legacy fallback
      total_installment?: number,           // legacy fallback
      plans?: Array<InstallmentPlan>,
      selected_plan_id?: number | null
    }
  - InstallmentPlan: {
      id: number,
      code: string,                         // e.g. 'standard', 'dp30', 'dp50', or custom
      label?: string,                       // display label; fallback to code
      increase_percent?: number,            // apply-to Tuition/Lab (notes only for now)
      installment_count?: number,           // count of installments (e.g. 5)
      down_payment: number,
      installment_fee: number,              // per-installment amount
      total_installment: number
    }

- Cashier installments UI (computed on controller):
  - vm.installmentsUI: {
      show: boolean,
      summary: { dp: number, total: number, count: number, fee?: number } | null,
      list: Array<{ key: string, label: string, amount: number }>,
      firstDue: { key: 'dp' | 'i1'|'i2'|..., label: string, amount: number } | null
    }

- Enlistment controller helpers:
  - vm.installmentPlans: InstallmentPlan[]
  - vm.selectedInstallmentPlanId: number | null
  - installmentData(): { dp: number, fee: number, total: number } | null
  - displayedTotalDue(): number

Validation rules:
- Guard all derived reads with null checks.
- When plans array is empty or undefined, fallback to legacy totals (down_payment, installment_fee, total_installment) and legacy tabs only when required by template guards.

[Files]
Modify the SPA controllers and templates to render dynamic plans and remove legacy dp30/dp50 assumptions. No backend or migration changes.

Detailed breakdown:
- Files to be modified:
  - frontend/unity-spa/features/finance/cashier-viewer/cashier-viewer.html
    - Ensure an Installment Plan selector is present above the plan panel:
      - Select box bound to vm.selectedInstallmentPlanId, options from vm.installmentPlans, label = p.label || p.code.
      - Call vm.onInstallmentPlanChange() on change (controller should recompute panel).
    - Replace fixed "Installments (5x)" with dynamic count:
      - "Installments (x{{ (vm.installmentsSummary() &amp;&amp; vm.installmentsSummary().count) || 5 }})"
    - Confirm "Auto Amount" button uses vm.installmentsFirstDue() and works for DP and first installment.

  - frontend/unity-spa/features/finance/cashier-viewer/cashier-viewer.controller.js
    - Ensure controller exposes:
      - vm.installmentPlans: InstallmentPlan[]
      - vm.selectedInstallmentPlanId: number | null
      - vm.onInstallmentPlanChange(): void that updates any persisted registration field if applicable and calls vm._recomputeInstallmentsPanel()
      - vm._recomputeInstallmentsPanel() consumes selected plan (if required by implementation) and computes vm.installmentsUI properly.
    - Keep existing helpers:
      - vm.shouldShowInstallmentPanel(), vm.installmentsSummary(), vm.installmentsList(), vm.installmentsFirstDue(), vm.useFirstDueAmount()
    - Persist selection via vm.updateRegistration() if the backend registration accepts tuition_installment_plan_id in fields (controller already has update flow; wire it if present). If not supported, keep selection client-only.

  - frontend/unity-spa/features/registrar/enlistment/enlistment.controller.js
    - De-duplicate and remove legacy dp30/dp50 redundant implementations.
    - Keep only one set of functions:
      - vm.installmentPlans: from tuition.summary.installments.plans
      - vm.selectedInstallmentPlanId: set to installments.selected_plan_id or first plan id
      - selectInstallmentTab(tab): when plans exist, map tab code to plan.id and set vm.selectedInstallmentPlanId; else keep legacy behavior
      - installmentData(): returns { dp, fee, total } from selected plan when plans exist; fallback to legacy fields when not
      - displayedTotalDue(): return selected plan total_installment when plans exist; fallback to legacy
    - Remove undefined function binding (vm.increaseInfo = increaseInfo) if increaseInfo is not declared; rely on selected plan metadata in template for notes.

  - frontend/unity-spa/features/registrar/enlistment/enlistment.html
    - Replace hardcoded dp30/dp50 tabs with dynamic tabs when plans exist:
      - <button ng-repeat="p in vm.tuition.summary.installments.plans" ng-click="vm.selectedInstallmentPlanId=p.id">...</button>
      - Retain legacy tab buttons only when no plans are provided (existing ng-if split remains).
    - Update Installment Figures to use vm.installmentData().dp/fee/total and sp.installment_count for per-installment count.
    - Replace plan-specific notes using increaseInfo with generic: "Plan increase: {{ sp.increase_percent || 0 }}% applied to Tuition and Lab. Misc and Additional are unchanged."
    - Remove all template references to vm.increaseInfo() to fix ReferenceError.
    - Total Due display to use vm.displayedTotalDue().

- Files not changed:
  - Backend controllers/services/migrations (no changes per scope).
  - UnityService: assumed to already return the required installments payload for preview.

[Functions]
Introduce or finalize minimal controller functions to support dynamic plans; remove legacy/undefined references.

Detailed breakdown:
- New or ensured functions (Cashier controller):
  - onInstallmentPlanChange(): void
    - Purpose: Update registration (if supported) and refresh the installments panel based on vm.selectedInstallmentPlanId.
    - Path: frontend/unity-spa/features/finance/cashier-viewer/cashier-viewer.controller.js
  - _recomputeInstallmentsPanel(): void
    - Purpose: Build vm.installmentsUI from tuition payload and selected plan; compute remaining DP and installment rows by subtracting prior payments (existing logic retained).
    - Path: same file
  - selectedInvoiceIsTuition(): boolean (existing)
  - installmentsFirstDue(): { key, label, amount } | null (existing)
  - useFirstDueAmount(): void (existing)

- Modified functions (Enlistment controller):
  - selectInstallmentTab(tab: string): void
    - When plans exist, map tab code to plan.id; fallback to legacy when no plans exist. Remove duplicated legacy version.
    - Path: frontend/unity-spa/features/registrar/enlistment/enlistment.controller.js
  - installmentData(): { dp, fee, total } | null
    - Return selected plan values; fallback to legacy when no plans exist. Remove duplicated legacy version.
    - Path: same file
  - displayedTotalDue(): number
    - Return selected plan total; fallback to legacy or summary.total_due
    - Path: same file
  - loadTuition(force?: boolean): void
    - After loading, initialize vm.installmentPlans and vm.selectedInstallmentPlanId using installments.plans and installments.selected_plan_id when present.
    - Path: same file

- Removed functions:
  - increaseInfo(): any (and any vm.increaseInfo = increaseInfo binding) — replace template usage with selected plan metadata.

[Classes]
No new classes; modify existing AngularJS controllers only.

Detailed breakdown:
- Modified controllers:
  - CashierViewerController (frontend/unity-spa/features/finance/cashier-viewer/cashier-viewer.controller.js)
    - Ensure plan selection state and recomputation hooks.
  - EnlistmentController (frontend/unity-spa/features/registrar/enlistment/enlistment.controller.js)
    - Consolidate dynamic plan logic, remove undefined increaseInfo references, ensure stable default selection and guards.

[Dependencies]
No external dependency changes.

Assumptions:
- Backend Tuition preview response already includes tuition.summary.installments.plans[] and selected_plan_id.
- Legacy fields remain for backward compatibility when plans are absent.

[Testing]
Adopt manual QA steps and smoke checks focused on dynamic rendering and backward compatibility.

Test scenarios:
- Cashier Viewer:
  - When paymentType is partial and tuition.summary.installments exists:
    - Plan dropdown appears populated by vm.installmentPlans.
    - Changing plan updates vm.installmentsUI.summary.dp, total, count and the list of installment rows; "Auto Amount" button appears for DP or first due installment.
    - If persisted via updateRegistration, saving and reloading retains selected plan.
  - When installments are absent:
    - Installment Plan panel hides; page remains functional.

- Enlistment Tuition Preview:
  - Tabs render from tuition.summary.installments.plans; clicking a tab updates figures:
    - Down Payment = plan.down_payment
    - Per-Installment (xN) displays plan.installment_count
    - Total Installment displays plan.total_installment
    - Notes display plan.increase_percent
  - When installments are absent:
    - Legacy tabs (Standard/30%/50%) display if template guards trigger; no JS errors.
  - Regression fix: No ReferenceError for increaseInfo; search confirms removal of calls in template and controller.

- Edge cases:
  - Single plan present.
  - installment_count ≠ 5 (e.g., 3, 6).
  - Missing labels (use code fallback).
  - Zero or negative amounts are guarded and shown as 0.00 in UI.

[Implementation Order]
Work in a sequence that unblocks QA early and fixes current runtime error first.

1. Enlistment template fix (runtime): remove vm.increaseInfo references; replace notes with selected plan metadata; use dynamic per-installment count from selected plan.
2. Enlistment controller cleanup: remove duplicate legacy functions; consolidate selectInstallmentTab, installmentData, displayedTotalDue; initialize vm.installmentPlans and vm.selectedInstallmentPlanId in loadTuition.
3. Cashier viewer template: confirm dropdown exists and dynamic count "Installments (x{{ count }})" is wired; adjust if missing.
4. Cashier viewer controller: ensure vm.installmentPlans, vm.selectedInstallmentPlanId, onInstallmentPlanChange, and _recomputeInstallmentsPanel use selected plan; persist via updateRegistration if available.
5. Manual QA on both pages: verify dynamic plans render and change figures; verify no console errors; verify fallback behavior with missing plans.
6. Polish strings and guards; ensure number formatting with number:2 across figures.
