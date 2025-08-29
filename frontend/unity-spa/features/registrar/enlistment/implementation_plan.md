
# Implementation Plan

[Overview]
Add installment tabs to the Registrar Enlistment Tuition Details panel to display tuition payment options for Standard (configured installment), 30% DP, and 50% DP. The Total Due card should dynamically reflect the selected installment option.

This enhancement introduces a tabbed UI within the existing Tuition Details section. Each tab shows a concise breakdown: Down Payment, Per-Installment (5 payments), and Total Installment, derived from the backend-provided summary.installments structure returned by the existing /unity/tuition-preview compute pipeline. No backend changes are required; we will consume the current payload as-is. The summary cards for tuition, misc, lab, additional, scholarships, and discounts remain unchanged; only the Total Due figure switches contextually to selected installment totals for 30% and 50% tabs, while Standard defaults to the existing total_due baseline where applicable.

The implementation focuses on minimal, additive changes to the AngularJS controller and template: adding view-model state for the selected tab, computing the displayed installment values, and updating the Total Due card binding to reflect the selected option. Styling uses existing Tailwind-like utility classes present in the app to maintain visual consistency.


[Types]  
Introduce lightweight UI state and typings for installment selection; no backend type changes.

Detailed type structures (conceptual):
- TabKey (UI):
  - 'standard' | 'dp30' | 'dp50'
- Installments (from API; already present on vm.tuition.summary.installments):
  - total_installment: number
  - total_installment30: number
  - total_installment50: number
  - down_payment: number
  - down_payment30: number
  - down_payment50: number
  - installment_fee: number
  - installment_fee30: number
  - installment_fee50: number

Validation/assumptions:
- Guard against missing summary.installments (e.g., when tuition is not yet loaded or compute failed). The UI will render tabs but show a helpful notice or zeroed values if installments are absent.
- Maintain numeric display using existing currency pipe formatting in the view (| number:2).


[Files]
Modify the existing AngularJS controller and template to add tabs and computed display logic; no backend file changes.

Detailed breakdown:
- Existing files to be modified
  - frontend/unity-spa/features/registrar/enlistment/enlistment.controller.js
    - Add view-model state: vm.installmentTab with default 'standard'.
    - Add helpers:
      - vm.selectInstallmentTab(tabKey: 'standard'|'dp30'|'dp50')
      - vm.installmentData(): picks the correct down payment, per-installment fee, and total based on selected tab and available summary.installments.
      - vm.displayedTotalDue(): returns the number to display in the Total Due card:
        - 'standard' → vm.tuition.summary.total_due (existing baseline)
        - 'dp30' → summary.installments.total_installment30
        - 'dp50' → summary.installments.total_installment50
      - Ensure vm.installmentTab resets to 'standard' on student/term changes and after loading new tuition.
  - frontend/unity-spa/features/registrar/enlistment/enlistment.html
    - Add a tabs control inside Tuition Details (beneath the header and action buttons) with three tabs: Standard, 30% DP, 50% DP. Highlight the active tab.
    - Insert a small grid under the tabs that shows for the selected tab:
      - Down Payment
      - Per-Installment (x5)
      - Total Installment
    - Update the existing Total Due card binding to use vm.displayedTotalDue() to reflect the selected tab’s total where applicable.

- New files to be created
  - None

- Files to be deleted or moved
  - None

- Configuration file updates
  - None


[Functions]
Add minimal controller helpers for tab state and computed installment values.

Detailed breakdown:
- New functions (frontend)
  - EnlistmentController.selectInstallmentTab(tabKey: string): void
    - File: frontend/unity-spa/features/registrar/enlistment/enlistment.controller.js
    - Purpose: Switch the active installment tab. Valid keys: 'standard', 'dp30', 'dp50'.
  - EnlistmentController.installmentData(): { dp: number, fee: number, total: number } | null
    - File: frontend/unity-spa/features/registrar/enlistment/enlistment.controller.js
    - Purpose: Resolve the correct down payment, per-installment fee, and total installment based on vm.installmentTab and vm.tuition.summary.installments.
    - Behavior:
      - For 'standard': use down_payment, installment_fee, total_installment
      - For 'dp30': use down_payment30, installment_fee30, total_installment30
      - For 'dp50': use down_payment50, installment_fee50, total_installment50
      - Returns null when installments are missing; view handles fallback gracefully.
  - EnlistmentController.displayedTotalDue(): number
    - File: frontend/unity-spa/features/registrar/enlistment/enlistment.controller.js
    - Purpose: Provide the numeric value to display in the Total Due card per selected tab.
    - Behavior:
      - 'standard' → vm.tuition.summary.total_due
      - 'dp30' → installments.total_installment30 (if available; else fallback to total_due)
      - 'dp50' → installments.total_installment50 (if available; else fallback to total_due)

- Modified functions
  - Backend: TuitionCalculator::computeInstallments(totals, tuitionYear, level, yearLevel)
    - File: laravel-api/app/Services/TuitionCalculator.php
    - Change: For 30% and 50% schemes, apply increase factors to lab, misc, and additional buckets in addition to tuition before computing totals, down payments, and per-installment fees. Keep Standard using tuitionYear.installmentIncrease on tuition and lab only (misc/additional unchanged) unless a future config dictates otherwise.
    - Resulting formulas (high-level):
      - 30%: gross30 = (tuition * 1.15) + (lab * 1.15) + (misc * 1.15) + (additional * 1.15)
      - 50%: gross50 = (tuition * 1.09) + (lab * 1.09) + (misc * 1.09) + (additional * 1.09)
      - Apply discounts/scholarships after these increases; compute DP and 5-installment fees as before.
  - Frontend: EnlistmentController.loadTuition(force?: boolean)
    - File: frontend/unity-spa/features/registrar/enlistment/enlistment.controller.js
    - Change: After a successful load (vm.tuition is set), ensure vm.installmentTab resets to 'standard' when tuition is cleared on student/term change.

- Removed functions
  - None


[Classes]
No new classes or backend modifications.

Detailed breakdown:
- New classes
  - None

- Modified classes
  - None

- Removed classes
  - None


[Dependencies]
One backend computation adjustment; no new packages.

Details:
- Update TuitionCalculator::computeInstallments to apply plan-specific increases to lab, misc, and additional for 30% and 50% schemes (in addition to tuition). Keep Standard behavior using tuitionYear.installmentIncrease on tuition and lab only unless configured otherwise.
- Preserve API shape: summary.installments keeps the same keys; only internal formulas change.
- Reuse existing AngularJS app stack. No composer/npm changes.


[Testing]
UI-focused testing using existing dev data and the tuition preview action.

Test file requirements: None (manual UI verification sufficient at this stage).

Validation strategies:
- Preconditions: Pick a student and term with current enlisted subjects so that “Load Tuition” returns a full summary with installments.
- Verify tabs render: Standard, 30% DP, 50% DP.
- For each tab:
  - Down Payment shows the correct field: down_payment | down_payment30 | down_payment50.
  - Per-Installment (x5) shows the right installment_fee | installment_fee30 | installment_fee50.
  - Total Installment shows total_installment | total_installment30 | total_installment50.
- Verify installment increases:
  - Under 30% tab, confirm lab, misc, and additional are increased per scheme factor (currently +15%) within installment totals (even though individual summary cards remain baseline).
  - Under 50% tab, confirm lab, misc, and additional are increased per scheme factor (+9%) within installment totals.
  - Confirm Standard keeps misc/additional unincreased; only tuition and lab affected by tuitionYear.installmentIncrease.
- Total Due card updates:
  - Standard → shows tuition.summary.total_due
  - 30% DP → shows summary.installments.total_installment30
  - 50% DP → shows summary.installments.total_installment50
- Fallbacks:
  - If summary.installments is missing, tab content displays either zeroed values or “Unavailable” notice, and Total Due card falls back to summary.total_due.
- Regression:
  - Other summary cards (Tuition, Misc, Lab, Additional, Scholarships, Discounts) stay unchanged across tabs.
  - Saving tuition is unaffected.


[Implementation Order]
Update controller state and helpers first, then integrate the template, and finally verify visually.

1) Controller state and helpers
   - Add vm.installmentTab = 'standard'.
   - Add vm.selectInstallmentTab(tabKey), vm.installmentData(), vm.displayedTotalDue().
   - Reset vm.installmentTab to 'standard' on student change or when tuition is cleared.

2) Template changes
   - Add tab buttons within Tuition Details header area (after the Load/Save Tuition buttons).
   - Insert a small grid under the tabs for the three figures (DP, Per-Installment x5, Total Installment) for the active tab.
   - Replace the Total Due binding to use vm.displayedTotalDue().

3) Validation and polish
   - Test with multiple students/terms.
   - Confirm formatting (currency) and active tab styling.
   - Confirm no backend calls or payloads need changes.
