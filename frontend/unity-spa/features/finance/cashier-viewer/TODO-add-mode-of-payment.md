# TODO - Add mode_of_payment_id dropdown to Add Payment (Cashier Viewer)

Scope: Frontend (AngularJS) + Backend (Laravel)

Goals:
- Add a dropdown in the Add Payment form to select mode_of_payment_id (from payment_modes).
- Include mode_of_payment_id in the POST payload when creating a payment.
- Validate and persist mode_of_payment_id on the backend to satisfy DB NOT NULL constraint.

Steps:
- [ ] Backend: Update request validation to accept and require mode_of_payment_id.
- [ ] Backend: Insert mode_of_payment_id into payment_details when column exists.
- [ ] Frontend: Inject PaymentModesService into CashierViewerController.
- [ ] Frontend: Load active payment modes on bootstrap and bind to vm.paymentModes.
- [ ] Frontend: Extend vm.payment model with mode_of_payment_id.
- [ ] Frontend: Require mode_of_payment_id in canSubmitPayment().
- [ ] Frontend: Include mode_of_payment_id in submit payload.
- [ ] Frontend: Add the dropdown UI in cashier-viewer.html (select2, placeholder, validation hint).
- [ ] Test: Manually verify create payment succeeds and DB no longer errors on missing mode_of_payment_id.
- [ ] Optional: Display selected mode in payments table (future enhancement).

Notes:
- Service to reuse: frontend/unity-spa/features/finance/payment-modes/payment-modes.service.js (PaymentModesService.list()).
- Laravel validator file: laravel-api/app/Http/Requests/Api/V1/CashierPaymentStoreRequest.php
- Laravel controller method: laravel-api/app/Http/Controllers/Api/V1/CashierController.php::createPayment()
