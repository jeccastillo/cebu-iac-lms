# Student Billing: Optional Invoice Generation (Add Item)

Goal: Add an option to generate an invoice or not when adding a student billing item.

Status Legend:
- [ ] Pending
- [x] Done

## Backend

1) Request validation: accept the flag
- File: laravel-api/app/Http/Requests/Api/V1/StudentBillingStoreRequest.php
- Changes:
  - Add optional boolean `generate_invoice` with default true
  - rules(): `'generate_invoice' => ['sometimes', 'boolean']`
  - attributes(): add label
  - validated(): cast to boolean and default to true when missing
- [ ] Pending

2) Controller: conditionally create invoice
- File: laravel-api/app/Http/Controllers/Api/V1/StudentBillingController.php
- Changes:
  - Determine `$generateInvoice` from validated data (default true)
  - If `$generateInvoice === true`:
    - Enforce cashier presence and invoice_current
    - Generate invoice via InvoiceService
    - Increment `invoice_current`
  - If `$generateInvoice === false`:
    - Skip cashier/invoice checks
    - Create billing only; do not generate invoice nor increment pointer
  - Update PHPDoc to include `generate_invoice?: boolean (default: true)`
- [ ] Pending

## Frontend

3) UI: Add checkbox in Add modal
- File: frontend/unity-spa/features/finance/student-billing/list.html
- Changes:
  - Add "Generate invoice now" checkbox (default checked) shown only on Add
- [ ] Pending

4) Controller: wire the flag
- File: frontend/unity-spa/features/finance/student-billing/student-billing.controller.js
- Changes:
  - Initialize `vm.current.generate_invoice = true` in `openAdd()`
  - Include `generate_invoice` in payload on create
- [ ] Pending

5) Service: passthrough (doc only)
- File: frontend/unity-spa/features/finance/student-billing/student-billing.service.js
- Changes:
  - Update comment for create() payload to include `generate_invoice?`
  - No functional change required
- [ ] Pending

## Follow-up / Verification

6) Manual Smoke Tests
- Case A (default): generate_invoice = true
  - Expect: Billing item created, invoice generated, cashier invoice_current incremented
  - Enforces cashier presence and invoice number availability
- Case B: generate_invoice = false
  - Expect: Billing item created, no invoice generated, no increment, no cashier requirement
- [ ] Pending

7) Optional: Add feature tests for controller store behavior on both paths
- [ ] Pending
