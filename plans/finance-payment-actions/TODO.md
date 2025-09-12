# Finance Payment Actions - Implementation TODO

Scope: Add Finance-admin page and API to search payment details by OR/Invoice and perform actions: Void (status="Void") and Retract (hard delete, audit via SystemLog). Roles: finance_admin, admin.

Progress Checklist:
- [x] Step 1: Backend Service
  - [x] Create laravel-api/app/Services/FinancePaymentActionsService.php
  - [x] Implement search(filters, page, perPage) leveraging PaymentDetailAdminService::search
  - [x] Implement void(id, request) using PaymentDetailAdminService::update(['status' => 'Void', ...])
  - [x] Implement retract(id, request) using PaymentDetailAdminService::delete
- [x] Step 2: Backend Controller
  - [x] Create laravel-api/app/Http/Controllers/Api/V1/FinancePaymentActionsController.php
  - [x] Implement search(Request): validates params; returns items/meta
  - [x] Implement void(int $id, Request): returns updated item
  - [x] Implement retract(int $id, Request): returns confirmation message
- [x] Step 3: Routes
  - [x] Update laravel-api/routes/api.php: 
        GET  /api/v1/finance/payment-actions/search
        POST /api/v1/finance/payment-actions/{id}/void
        DELETE /api/v1/finance/payment-actions/{id}/retract
        All with middleware('role:finance_admin,admin')
- [x] Step 4: Frontend Service
  - [x] Create frontend/unity-spa/features/finance/payment-actions/payment-actions.service.js
  - [x] Methods: search, void, retract; include X-Faculty-ID header
- [x] Step 5: Frontend Controller
  - [x] Create frontend/unity-spa/features/finance/payment-actions/payment-actions.controller.js
  - [x] Manage filters (or_number, invoice_number, page, per_page), results, actions, pagination, messaging
- [x] Step 6: Frontend Template
  - [x] Create frontend/unity-spa/features/finance/payment-actions/payment-actions.html
  - [x] Build search form, results table, Void/Retract buttons with confirm dialogs
- [x] Step 7: UI Wiring
  - [x] Update frontend/unity-spa/core/routes.js to add "/finance/payment-actions" with requiredRoles ["finance_admin","admin"]
  - [x] Update frontend/unity-spa/shared/components/sidebar/sidebar.controller.js to add "Payment Actions" under Finance
- [ ] Step 8: Thorough Testing
  - [ ] API: search, void, retract (happy paths, errors, idempotency, schema variance)
  - [ ] UI: role visibility, interactions, pagination, error handling, confirmations
  - [ ] Regression: admin payment-details, FinanceService listPaymentDetails, CashierViewerAggregate unaffected
  - [ ] System logs: validate update/delete records for PaymentDetail

Notes:
- Void label strictly "Void".
- Retract is hard delete; audit via SystemLogService (via PaymentDetailAdminService).
- Dedicated finance endpoints; do not reuse admin endpoints for actions.
