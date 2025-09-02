# Cashier Viewer — Amount Input: Enforce up to 2 Decimal Places

Goal:
- Ensure the Amount input on the Cashier Viewer "Add Payment" form only accepts up to 2 decimal places.

Information Gathered:
- HTML input (amount) located at: `frontend/unity-spa/features/finance/cashier-viewer/cashier-viewer.html`
  - Current attributes: `type="number" step="0.01" min="0.01" ng-model="vm.payment.amount" ng-attr-max="{{ vm.amountMax() || undefined }}" ng-change="vm.onAmountChange()"`
- Controller: `frontend/unity-spa/features/finance/cashier-viewer/cashier-viewer.controller.js`
  - `vm.onAmountChange` currently clamps amount only to invoice remaining max (if applicable).
  - No enforcement of decimal precision.
- Request: accept up to 2 decimal places (reject/sanitize additional decimals).

Plan:
- [ ] Update HTML input:
  - [ ] Add `ng-pattern="/^\d+(\.\d{1,2})?$/"` to validate up to 2 decimals.
  - [ ] Add `ng-blur="vm.onAmountBlur()"` to sanitize value on blur.
  - [ ] Add `inputmode="decimal"` to improve mobile keyboard UX.
- [ ] Update Controller:
  - [ ] Add helper `vm._toTwoDecimals(val)` to coerce to number and round to 2 decimals.
  - [ ] Update `vm.onAmountChange()` to trim to 2 decimals while typing and still clamp to max if present.
  - [ ] Add `vm.onAmountBlur()` to finalize sanitation (round to 2dp, apply min=0.01 and clamp to max).

Acceptance Criteria:
- Typing 10.1, 10.12 should be accepted as-is.
- Typing 10.123 should result in 10.12 after change/blur (max 2 decimals).
- Values less than 0.01 are raised to 0.01 (respect `min`).
- If an invoice is selected and a max cap exists, final value respects the cap.
- No backend changes required.

Testing:
- Manually test input behavior with examples: `0.009`, `0.01`, `1`, `1.2`, `1.23`, `1.234`.
- Test with invoice selected where remaining is less than typed amount.
- Ensure form submission still works and validation remains consistent.
