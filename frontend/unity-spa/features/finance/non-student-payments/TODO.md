# Non-Student Payments (Payees) — Frontend Implementation

Scope: Add Finance page to create Non-Student (Payee) payments calling Laravel endpoint:
POST /api/v1/cashiers/{id}/payee-payments (roles: cashier_admin, finance, admin)

Backend payload (required):
- payee_id (int)
- id_number (string)
- mode (or | invoice | none)
- amount (number > 0)
- description (string)
- mode_of_payment_id (int)
- remarks (string)

Optional payload fields:
- method (string), posted_at (datetime), or_date (date), convenience_fee (number)
- campus_id (int), invoice_id (int), invoice_number (int), number (int - explicit assignment)

Implementation Steps

1) Routing &amp; Navigation
- [x] Add route in core/routes.js:
  - Path: /finance/non-student-payments
  - Template: features/finance/non-student-payments/non-student-payments.html
  - Controller: NonStudentPaymentsController
  - requiredRoles: ['finance', 'cashier_admin', 'admin']
- [x] Add Finance sidebar link:
  - Label: Non-Student Payments
  - Path: /finance/non-student-payments
  - RBAC match: finance | cashier_admin | admin

2) Service Layer (feature-scoped)
- [x] Create non-student-payments.service.js exposing:
  - getMyCashier() → GET /cashiers/me
  - create(cashierId, payload) → POST /cashiers/{id}/payee-payments
  - searchPayees(q) → GET /payees?search=... (optional; hide feature if 403)
- [x] Use StorageService loginState to attach X-Faculty-ID header (follow UnityService pattern).
- [x] Reuse PaymentModesService.list() for mode_of_payment options.

3) Controller
- [x] Create non-student-payments.controller.js:
  - [x] On init: resolve myCashier; if absent → show guidance to get assigned on Cashier Admin.
  - [x] Load payment modes; (optional) load description suggestions.
  - [x] Form model:
    - payee: { id, id_number, name } (autocomplete or manual fallback)
    - mode, number (optional), amount, description, mode_of_payment_id, method (optional), remarks
    - posted_at (optional), or_date (optional), convenience_fee (optional), campus_id (optional)
    - invoice_id/number (optional)
  - [x] Validation: required fields, amount clamp to 2 decimals.
  - [x] Submit: call service.create(); show success with returned id + number_used; reset form.

4) Template
- [x] Create non-student-payments.html:
  - [x] Header: Non-Student Payments (Payees)
  - [x] Alert if myCashier missing with link to #!/cashier-admin
  - [x] Autocomplete for Payee (if allowed) or manual payee_id + id_number inputs.
  - [x] Inputs: mode selector, optional number, mode_of_payment dropdown, amount, description, method (optional), remarks
  - [x] Optional inputs: or_date, posted_at, convenience_fee, campus_id, invoice_id/number
  - [x] Submit/Reset buttons; loading state; error display

5) Wire-in Scripts
- [x] Update index.html to include:
  - features/finance/non-student-payments/non-student-payments.service.js
  - features/finance/non-student-payments/non-student-payments.controller.js

6) Testing
- [ ] Login as finance or cashier_admin.
- [ ] Navigate to #!/finance/non-student-payments (visible in sidebar).
- [ ] If no myCashier: verify guidance appears.
- [ ] If myCashier exists:
  - [ ] Enter valid payee + id_number, choose mode, amount, description, mode_of_payment_id, remarks.
  - [ ] Submit; expect 201 with data.id and number_used in response.
  - [ ] Verify payment_details row created; cashier number increments when expected.
- [ ] Mode=or with provided invoice_number → confirm skip OR number (controller returns number_used=invoice_number).
- [ ] Mode=invoice → number increments from invoice_current.
- [ ] Mode=none → allows submission without assigning number.

Notes
- Payees endpoints require finance_admin or admin. If user lacks role, hide search/auto-complete; allow manual payee_id + id_number fields.
- Client does soft validations; server is authoritative for number range/usage.
