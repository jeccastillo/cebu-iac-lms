# TODO — Payment Modes CRUD (Finance)

This TODO tracks implementation progress for Payment Modes CRUD across Laravel API and AngularJS SPA. Keep this file updated as steps complete.

Task Progress Items:
- [x] Step 1: Backend scaffolding — Add Laravel model (PaymentMode), form requests (store/update), resource, controller; register routes with role gating (finance,admin)
- [x] Step 2: Backend logic — Implement controller methods (index/show/store/update/destroy/restore) with validation, filtering, sorting, pagination, and casting
- [ ] Step 3: Backend tests — Add Laravel feature tests for authz, validation, CRUD, restore, filtering/pagination
- [x] Step 4: Frontend service & routing — Create Angular service (payment-modes.service.js) and register Finance routes (/finance/payment-modes, /new, /:id/edit) with requiredRoles ["finance","admin"]
- [x] Step 5: Frontend pages — Build list.html and edit.html with controllers, client validation, CRUD actions (edit/delete/restore)
- [x] Step 6: Navigation — Add “Payment Modes” under Finance in the sidebar
- [ ] Step 7: Thorough testing — Execute thorough API/UI tests and Finance regression smoke (Ledger & Cashier Viewer), refine UX and error handling

Implementation Notes:
- Roles: Both read and write restricted to finance and admin.
- Charge: Flat currency amount (>= 0). Use numeric validation and float casting.
- Name: No uniqueness constraint beyond max length.
- Deletion: Soft delete via DELETE; add POST /payment-modes/{id}/restore.
- API base: /api/v1/payment-modes endpoints.
- UI routes:
  - #/finance/payment-modes
  - #/finance/payment-modes/new
  - #/finance/payment-modes/:id/edit

Files to Add/Modify:

Backend (Laravel)
- New:
  - app/Models/PaymentMode.php
  - app/Http/Controllers/Api/V1/PaymentModeController.php
  - app/Http/Requests/Api/V1/PaymentModeStoreRequest.php
  - app/Http/Requests/Api/V1/PaymentModeUpdateRequest.php
  - app/Http/Resources/PaymentModeResource.php
  - tests/Feature/PaymentModesTest.php
- Modified:
  - routes/api.php (add routes with role:finance,admin)

Frontend (AngularJS)
- New:
  - frontend/unity-spa/features/finance/payment-modes/list.html
  - frontend/unity-spa/features/finance/payment-modes/edit.html
  - frontend/unity-spa/features/finance/payment-modes/payment-modes.service.js
  - frontend/unity-spa/features/finance/payment-modes/payment-modes.controller.js
  - frontend/unity-spa/features/finance/payment-modes/payment-mode-edit.controller.js
- Modified:
  - frontend/unity-spa/core/routes.js (add three routes for payment modes)
  - frontend/unity-spa/shared/components/sidebar/sidebar.html (add Finance link)

Testing Scope (Thorough):
- API: authz, CRUD, soft delete/restore, pagination/sort/filter, type casting, timestamps
- UI: RBAC routing, list table behavior, edit form validation, delete/restore flows, toasts
- Regression: Finance Ledger and Cashier Viewer basic smoke after menu/route updates

Links:
- Implementation plan: frontend/unity-spa/features/finance/payment-modes/implementation_plan.md
