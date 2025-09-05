1. Allow Creating a Payment Without OR/Invoice
File to Edit: laravel-api/app/Http/Requests/Api/V1/CashierPaymentStoreRequest.php

Change: Update the rules for 'mode' to allow 'none' (in: or, invoice, none) or make it nullable with a default to 'none'.
File to Edit: laravel-api/app/Http/Controllers/Api/V1/CashierController.php

Method: createPayment
Change:
If mode === 'none':
Do not resolve number column, validate pointer/range, or increment any pointers.
Insert the row without or_no, or_number, or invoice_number unless invoice linking is explicitly provided.
If mode === 'or' or invoice: Maintain existing behavior.
2. Add an Endpoint to Assign a Number to an Existing Payment
File to Create: laravel-api/app/Http/Requests/Api/V1/CashierPaymentAssignNumberRequest.php

Rules:
mode: required|in:or,invoice;
number: nullable|integer|min:1 (if provided);
Optionally allow invoice_number when mode='or'.
File to Edit: laravel-api/app/Http/Controllers/Api/V1/CashierController.php

Method: assignPaymentNumber(int $cashierId, int $paymentId, CashierPaymentAssignNumberRequest $request)
Change:
Verify payment exists and determine target column (or_no/or_number for OR; invoice_number for invoice).
If payment already has a value in the target column, return a validation error.
If number provided: validate within configured cashier range; ensure uniqueness via CashierService->validateRangeUsage; update payment; do not change cashier pointer unless the provided number equals the current pointer.
If number not provided: consume the next available number from the cashier pointer with the same validations, update payment, and increment the respective pointer.
Log the update via PaymentDetailAdminService->getById for normalized payload.
Route:

POST /api/v1/cashiers/{id}/payments/{paymentId}/assign-number with middleware role:cashier_admin,finance,admin.
Dependent Files to be Edited/Created
Edit: laravel-api/app/Http/Requests/Api/V1/CashierPaymentStoreRequest.php (allow 'none')
Edit: laravel-api/app/Http/Controllers/Api/V1/CashierController.php (support mode 'none' in createPayment; add assignPaymentNumber method)
Create: laravel-api/app/Http/Requests/Api/V1/CashierPaymentAssignNumberRequest.php
Edit: laravel-api/routes/api.php (new route for assign-number)