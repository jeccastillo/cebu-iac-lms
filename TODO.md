# Cashier Viewer — Missing Billing Invoices Implementation TODO

Plan Source: implementation_plan.md

Scope:
- Backend (Laravel API): Add endpoints to list student billing items without invoices and to generate an invoice for a billing item.
- Frontend (AngularJS SPA): Cashier Viewer — auto-open modal listing missing-invoice billings, per-row generate action, and bottom-right floating bell icon with badge to reopen the modal.

Testing Mode: Critical-path testing (as requested).

---

## Task Progress

- [x] Step 1: Backend — Create StudentBillingExtrasService (service layer)
  - [x] listMissingInvoices(studentId:int, term:int): array
  - [x] generateInvoiceForBilling(billingId:int, opts: {posted_at?, remarks?}): array { invoice_id, invoice_number }
  - [x] augmentBillingWithInvoiceInfo(rows, studentId, term) (internal helper) — covered within listMissingInvoices/hasInvoice heuristics
  - [x] Strategy for determining existing invoice: explicit linkage (remarks "Billing #id") or deterministic match on {description, amount}

- [x] Step 2: Backend — Create StudentBillingExtrasController
  - [x] missingInvoices(Request): validate student_id and term, call service, return JSON
  - [x] generateInvoice(int $id, Request): validate billing ownership/term/un-invoiced, call service, return invoice info
  - [x] Add role middleware: finance,admin

- [x] Step 3: Backend — Wire routes
  - [x] GET /api/v1/finance/student-billing/missing-invoices → missingInvoices
  - [x] POST /api/v1/finance/student-billing/{id}/generate-invoice → generateInvoice

- [x] Step 4: Frontend Service — Extend StudentBillingService
  - [x] missingInvoices(filters:{student_id, term})
  - [x] generateInvoiceForBilling(id:number, body?:{posted_at?, remarks?})

- [x] Step 5: Frontend Controller — Update CashierViewerController
  - [x] Add state: vm.missingBilling, vm.ui.showMissingBillingModal, vm.ui.dismissedMissingBilling, vm.badge
  - [x] Add methods: loadMissingBilling(force), openMissingBillingModal(), closeMissingBillingModal(), generateInvoiceForBilling(billing)
  - [x] Insert loadMissingBilling in bootstrap and onTermChange chains
  - [x] After generate success: refresh invoices, billing, and missing list; update badge

- [x] Step 6: Frontend Template — Update cashier-viewer.html
  - [x] Modal: "Billings Without Invoice" (date, description, amount, action)
  - [x] Floating bell icon (bottom-right) with badge indicating count; click opens modal
  - [x] Visibility gated by Finance/Admin role

- [ ] Step 7: Critical-Path Testing
  - [ ] Backend: missing-invoices returns correct rows; generate-invoice creates billing-type invoice, forbids duplicates, role-guarded
  - [ ] Frontend:
    - [ ] Auto-open modal when items exist
    - [ ] Generate Invoice button works; refresh lists and badge
    - [ ] Close modal → floating bell shows; click reopens modal; hides at count=0
    - [ ] Term change re-evaluates and auto-opens if needed
    - [ ] Role gating (Finance/Admin only)

  Testing instructions (manual / curl):
  - Backend:
    1) List missing-invoices
       curl -X GET "http://localhost/laravel-api/public/api/v1/finance/student-billing/missing-invoices?student_id=123&amp;term=20251" -H "X-Faculty-ID: <faculty_id>"
    2) Generate invoice for a billing id
       curl -X POST "http://localhost/laravel-api/public/api/v1/finance/student-billing/456/generate-invoice" -H "Content-Type: application/json" -H "X-Faculty-ID: <faculty_id>" -d "{}"
    3) Verify invoices list:
       curl -X GET "http://localhost/laravel-api/public/api/v1/finance/invoices?student_id=123&amp;term=20251" -H "X-Faculty-ID: <faculty_id>"
  - Frontend:
    - Open Cashier Viewer for the student/term that has un-invoiced billing items.
    - Confirm modal auto-opens and lists items; click Generate Invoice per-row; verify count shrinks and invoices panel updates.
    - Close modal; confirm floating bell shows with correct badge; clicking reopens modal; bell hides when count reaches zero.
    - Switch term via global selector; confirm behavior re-evaluates correctly.
    - Confirm only Finance/Admin role can see modal/bell.

- [ ] Step 8: Polish and Error Handling
  - [ ] User feedback via ToastService on success/error
  - [ ] Loading states and disabled buttons during calls

Notes:
- There is currently no explicit backend field indicating invoice linkage for billing items; we will add dedicated endpoints and compute linkage server-side.
- Invoice generation for billing will target existing /finance/invoices/generate semantics with type='billing' and single item matching the billing row.
