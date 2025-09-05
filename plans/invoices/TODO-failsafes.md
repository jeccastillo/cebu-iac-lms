# Invoice Failsafes (Cashier-required and Current Invoice Pointer)

Goal:
- Prevent creating an invoice when:
  1) the acting user does not have a cashier account, and
  2) the cashier's current invoice number is not set,
- Applied when no explicit invoice_number is provided by the client (conditional policy).

Files:
- laravel-api/app/Http/Controllers/Api/V1/InvoiceController.php

Policy Variant Implemented:
- Conditional enforcement:
  - If request does NOT include `invoice_number`, then:
    - Require a resolvable cashier (via `cashier_id` in payload, or `X-Faculty-ID` header),
    - Require `invoice_current` to be set and non-empty for the acting cashier.
  - If request includes explicit `invoice_number`, skip these checks (admin/manual workflows remain possible).

Change Log:
- [x] Add controller-level guardrails in `InvoiceController::generate`:
  - Return 422 `NO_CASHIER` when no acting cashier can be resolved and `invoice_number` is not provided.
  - Return 422 `NO_CASHIER_INVOICE_CURRENT` when acting cashier exists but `invoice_current` is not set and `invoice_number` is not provided.

Testing Tips:
- Negative: Remove `invoice_number` from payload and do not provide `cashier_id` header values.
  - Expect 422 with:
    - code: NO_CASHIER
    - message: "Cashier account is required to generate an invoice without an explicit invoice_number."
- Negative: Provide `cashier_id` (or `X-Faculty-ID`) for a cashier whose `invoice_current` is null/0 (and omit `invoice_number`).
  - Expect 422 with:
    - code: NO_CASHIER_INVOICE_CURRENT
    - message: "Cashier current invoice is not set."
- Positive: Provide explicit `invoice_number` in the payload (admin/manual), even without cashier context.
  - Expect 201, invoice created.
- Positive: Provide `cashier_id` (or `X-Faculty-ID`) with a valid `invoice_current`, no `invoice_number` in payload.
  - Expect 201, invoice created and controller increments `invoice_current`.

Follow-ups (optional/hardening):
- [ ] Validate that `invoice_current` is within `[invoice_start, invoice_end]` when both bounds are present; return 422 if out-of-range.
- [ ] Enforce stricter policy (always require a cashier with valid current), even when `invoice_number` is provided.
- [ ] Add feature tests for the endpoint covering 422 branches.

Notes:
- Service-level (App\Services\InvoiceService) behavior remains unchanged; only the HTTP path is guarded.
- Existing scripts using the service directly (e.g. `scripts/test_invoices.php`) are unaffected by this controller-level check.
