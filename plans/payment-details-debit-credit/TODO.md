# Payment Details: Debit/Credit — TODO

This file tracks the implementation steps for the Debit/Credit feature. Keep checkboxes updated as work progresses.

- [x] 1) Backend Service: PaymentJournalService
  - [x] Create App/Services/PaymentJournalService.php
  - [x] Implement createDebit(payload, request?) — negative subtotal_order, status='Journal', no number assignment
  - [x] Implement createCredit(payload, request?) — positive subtotal_order, status='Paid', no number assignment
  - [x] Shared builder: detect columns (pmethod/payment_method, paid_at/date/created_at, or_date, mode_of_payment_id, student fields)
  - [x] Enforce invoice remaining for credits when invoice linked (invoice_id or invoice_number) if tb_mas_invoices present
  - [x] SystemLogService::log('create','PaymentDetail', id, null, normalized)

- [x] 2) Backend Controller: PaymentJournalController
  - [x] Create App/Http/Controllers/Api/V1/PaymentJournalController.php
  - [x] debit(): validate { student_id, term, amount>0, description; optional: remarks, method, posted_at, invoice_id, invoice_number, campus_id, mode_of_payment_id }
  - [x] credit(): same validation as debit + enforce_invoice_remaining flag (default true)
  - [x] Return 201 with { id, entry_type: 'debit'|'credit', syid, invoice_number|null, posted_at|null, amount }

- [x] 3) Routes
  - [x] Register routes in laravel-api/routes/api.php under /api/v1 with middleware('role:finance,admin'):
    - [x] POST /finance/payment-details/debit
    - [x] POST /finance/payment-details/credit

- [x] 4) Ledger Integration
  - [x] Modify App/Services/StudentLedgerService.php
  - [x] Include payment_details debit rows (subtotal_order < 0 or status='Journal') as assessment lines (positive abs(amount))
  - [x] Keep payment_details credit rows (status='Paid', subtotal_order > 0) as payment lines
  - [x] Preserve sorted order and existing labels/meta

- [x] 5) Frontend — Feature
  - [x] Add frontend/unity-spa/features/finance/debit-credit/debit-credit.service.js
  - [x] Add frontend/unity-spa/features/finance/debit-credit/debit-credit.controller.js
  - [x] Add frontend/unity-spa/features/finance/debit-credit/debit-credit.html
  - [x] Implement student selection, term selection, invoice optional link, forms for debit and credit (validations), call APIs, refresh FinanceService payment-details view

- [x] 6) Frontend — Navigation
  - [x] Update frontend/unity-spa/shared/components/sidebar/sidebar.html to add Finance > Debit/Credit (visible to roles: finance, admin)

- [ ] 7) Testing
  - [ ] Critical-path tests:
    - [ ] Debit creation: negative subtotal_order, status='Journal', no number assignment, optional invoice linkage
    - [ ] Credit creation: positive subtotal_order, status='Paid', no number assignment; with/without invoice linkage
    - [ ] Invoice remaining enforcement for credits
    - [ ] Payment details list reflects new entries; ledger reflects debit as assessment and credit as payment
  - [ ] Edge cases:
    - [ ] Missing optional columns on payment_details
    - [ ] No registration for term; still allow entries
    - [ ] Missing tb_mas_invoices; skip remaining enforcement
    - [ ] invoice_id vs invoice_number linkage
    - [ ] posted_at/or_date formatting
    - [ ] Concurrency on same invoice credits

- [ ] 8) Docs
  - [ ] Add brief README.md in feature dir with example requests/responses for debit/credit
