# Cashier Viewer — Reservation Fee Generate Button

Goal: On the Cashier page, when the globally selected term (syid) equals the applicant&#39;s tb_mas_applicant_data.syid:
1) Check paid_application_fee == 1 and interviewed == 1.
2) If both true, check if Reservation Fee billing and invoice exist; if either exists, do nothing.
3) If both are missing, display a button to generate Reservation Fee billing and invoice.

## Steps

- [ ] Controller: Capture applicant flags for decision logic
  - [ ] In loadApplicantInfo(), store:
    - vm.paidApplicationFee (from response.paid_application_fee)
    - vm.interviewed (from response.interviewed)
- [ ] Controller: Add helpers and flags
  - [ ] _isReservationFeeDesc(s): case-insensitive check for &#34;Reservation Payment&#34;
  - [ ] vm.hasReservationBilling: set during loadBilling()
  - [ ] vm.hasReservationInvoice(): scan vm.invoices items for Reservation Payment
- [ ] Controller: Visibility and action
  - [ ] vm.canShowGenerateReservationFee(): returns true only if:
    - vm.canEdit and acting cashier exists (vm.myCashier.id)
    - vm.student.id and vm.term exist
    - applicantSyid equals selected term
    - vm.paidApplicationFee === true AND vm.interviewed === true
    - vm.hasReservationBilling === false AND vm.hasReservationInvoice() === false
  - [ ] vm.generateReservationPayment(): resolve amount from PaymentDescriptions (&#34;Reservation Payment&#34;), call StudentBillingService.create with generate_invoice: true; then refresh billing, invoices, and payment details
- [ ] UI: Add panel
  - [ ] In cashier-viewer.html, add block below Application Fee panel:
    - Shown when vm.canShowGenerateReservationFee() is true
    - Info alert text: &#34;Reservation fee billing and invoice not found for this term.&#34;
    - Error alert bound to vm.resFeeGenerateError
    - Button: &#34;Generate Reservation Fee&#34; → vm.generateReservationPayment(), disabled while vm.generatingResFee

## Files to change

- frontend/unity-spa/features/finance/cashier-viewer/cashier-viewer.controller.js
- frontend/unity-spa/features/finance/cashier-viewer/cashier-viewer.html

## Test Plan

1) Select a student applicant where:
   - applicant_data.syid matches the global term
   - paid_application_fee = 1
   - interviewed = 1
   - No Reservation Payment billing and no invoice
   Expect: Reservation panel appears with &#34;Generate Reservation Fee&#34; button.

2) Click &#34;Generate Reservation Fee&#34;:
   - Expect StudentBillingService.create() called with description &#34;Reservation Payment&#34;, generate_invoice = true
   - After success: panels refresh; Reservation panel disappears; invoices list includes the generated invoice.

3) Negative cases:
   - If term mismatch (global term != applicant syid) → no Reservation panel.
   - If paid_application_fee = 0 or interviewed = 0 → no Reservation panel.
   - If either Reservation billing or invoice already exists → no Reservation panel.

## Notes

- Uses existing PaymentDescriptionsService index to resolve amount. Ensure a &#34;Reservation Payment&#34; amount is configured; otherwise show a clear error.
- Mirrors the existing Application Fee implementation for consistency.
