# Implementation Plan

[Overview]
Add a dedicated Finance Admin page and API to allow authorized users to search payment details by OR number or Invoice number, and perform two actions: Retract (hard delete with system log) and Void (set status to "Void"). Access is restricted to roles finance_admin and admin. The UI provides a focused workflow separate from the Admin payment-details page.

This enhancement introduces dedicated finance endpoints to encapsulate business rules for retracting and voiding payments. It uses existing PaymentDetailAdminService mapping and logging behavior to ensure schema safety and auditability across environments while preserving logs via SystemLogService.

[Types]  
No global type system changes; introduce API DTOs for the new finance actions.

Detailed type definitions:
- PaymentDetailItem (normalized)
  - id: int
  - student_information_id: int|null
  - student_number: string|null
  - sy_reference: int|null
  - description: string|null
  - subtotal_order: number|null
  - total_amount_due: number|null
  - status: string|null
  - remarks: string|null
  - mode_of_payment_id: int|null
  - method: string|null
  - or_no: string|int|null
  - invoice_number: string|null
  - posted_at: string|null
  - source: 'payment_details'
- Search Request
  - or_number?: string
  - invoice_number?: string
  - student_number?: string (optional future-proof)
  - syid?: int (optional filter by term)
  - page?: int (default 1)
  - per_page?: int (default 20, max 200)
- Search Response
  - items: PaymentDetailItem[]
  - meta: { page: int, per_page: int, total: int }
- Void Request (by id)
  - id: int (path param)
  - remarks?: string (optional annotation)
- Void Response
  - success: true
  - data: PaymentDetailItem (updated; status == 'Void')
- Retract Request (by id)
  - id: int (path param)
  - notes?: string (optional annotation for logs)
- Retract Response
  - success: true
  - message: string

Validation rules:
- or_number/invoice_number at least one required on search; all string filters trimmed; per_page bounded [1..200].
- Void: id required; if already status == 'Void', operation is idempotent (no-op but still logged as update with no change).
- Retract: id required; hard delete with SystemLog create-delete audit pair preserved via existing service logging.

[Files]
Introduce a dedicated finance page and controller/service pair; update routes and sidebar/menu.

Detailed breakdown:
- New files to be created:
  - laravel-api/app/Http/Controllers/Api/V1/FinancePaymentActionsController.php
    - Purpose: Dedicated endpoints for searching and applying Void/Retract to payment_details with finance_admin access.
  - laravel-api/app/Services/FinancePaymentActionsService.php
    - Purpose: Orchestrate search/void/retract by leveraging PaymentDetailAdminService mapping and ensure logging.
  - frontend/unity-spa/features/finance/payment-actions/payment-actions.service.js
    - Purpose: Angular service calling the new finance endpoints.
  - frontend/unity-spa/features/finance/payment-actions/payment-actions.controller.js
    - Purpose: Angular controller managing search and actions UI for finance admin.
  - frontend/unity-spa/features/finance/payment-actions/payment-actions.html
    - Purpose: Angular template for the page.

- Existing files to be modified (specific changes):
  - laravel-api/routes/api.php
    - Add routes (finance-only set):
      - GET /api/v1/finance/payment-actions/search
      - POST /api/v1/finance/payment-actions/{id}/void
      - DELETE /api/v1/finance/payment-actions/{id}/retract
    - Middleware: role:finance_admin,admin
  - frontend/unity-spa/core/routes.js
    - Add route:
      - path: "/finance/payment-actions"
      - templateUrl: "features/finance/payment-actions/payment-actions.html"
      - controller: "FinancePaymentActionsController"
      - requiredRoles: ["finance_admin", "admin"]
  - frontend/unity-spa/shared/components/sidebar/sidebar.controller.js
    - Add menu item under Finance:
      - { label: 'Payment Actions', path: '/finance/payment-actions' }

- Files to be deleted or moved
  - None.

- Configuration file updates
  - None (assumes 'finance_admin' role already exists in role middleware and RoleService).

[Functions]
Add new functions for searching and updating payment details under a finance-admin context.

Detailed breakdown:
- New backend functions
  - FinancePaymentActionsService::search(array $filters, int $page, int $perPage): array
    - Uses PaymentDetailAdminService::detectColumns/selectList to build query.
    - Filters supported: or_number, invoice_number, student_number (optional), syid (optional).
    - Returns items/meta with normalized rows like Admin service.
  - FinancePaymentActionsService::void(int $id, ?Request $request): array
    - Loads current normalized row (via PaymentDetailAdminService::getById).
    - If not found → 404/ValidationException.
    - If status != 'Void' → PaymentDetailAdminService::update($id, ['status' => 'Void', 'remarks' => append optional marker], $request).
    - Returns updated normalized item. Leverage SystemLogService in PaymentDetailAdminService::update for audit trail.
  - FinancePaymentActionsService::retract(int $id, ?Request $request): void
    - Ensures row exists; calls PaymentDetailAdminService::delete($id, $request) for hard delete (already logs delete).
  - FinancePaymentActionsController
    - search(Request): GET /finance/payment-actions/search
      - Validates query: require at least one of or_number|invoice_number; page/per_page bounds.
      - Calls service->search and returns { success, data }.
    - void(int $id, Request): POST /finance/payment-actions/{id}/void
      - Calls service->void() and returns updated item.
    - retract(int $id, Request): DELETE /finance/payment-actions/{id}/retract
      - Calls service->retract() and returns { success: true, message }.

- Modified backend functions
  - None in PaymentDetailAdminService (reused as-is).

- New frontend functions
  - PaymentActionsService.search(filters)
    - GET /finance/payment-actions/search with params { or_number?, invoice_number?, page?, per_page? }
  - PaymentActionsService.void(id, payload?)
    - POST /finance/payment-actions/{id}/void with optional body { remarks? }
  - PaymentActionsService.retract(id)
    - DELETE /finance/payment-actions/{id}/retract
  - FinancePaymentActionsController
    - vm.filters = { or_number, invoice_number, page, per_page }
    - vm.search(resetPage)
    - vm.void(item)
    - vm.retract(item)
    - Helpers: vm.dateOnly(x), vm.resetFilters(), vm.goPage(delta)

- Removed functions
  - None.

[Classes]
Add a new controller and service class to isolate finance-only actions from admin payment-details.

Detailed breakdown:
- New classes
  - App\Http\Controllers\Api\V1\FinancePaymentActionsController
    - Methods: search(), void($id), retract($id)
  - App\Services\FinancePaymentActionsService
    - Methods: search(), void(), retract()
    - Depends on: App\Services\PaymentDetailAdminService for column mapping, normalization, update/delete, and logs.
- Modified classes
  - None (except route registration).
- Removed classes
  - None.

[Dependencies]
No new packages.

Integration and reuse:
- App\Services\PaymentDetailAdminService for:
  - detectColumns(), getById(), update(), delete()
- App\Services\SystemLogService (indirectly via PaymentDetailAdminService)
- Illuminate\Support\Facades\DB, Schema for safe, environment-agnostic queries (used in PaymentDetailAdminService)
- Frontend reuses existing StorageService/RoleService auth header helpers (X-Faculty-ID) for audit user resolution.

[Testing]
Manual and targeted API/SPA tests to cover search, void, and retract flows.

Test file requirements and strategies:
- API (manual or automated):
  - Search by exact OR number → returns expected item(s) with matching or_no.
  - Search by invoice number → returns expected item(s) with matching invoice_number.
  - Void behavior:
    - Given a 'Paid' item, POST void → returns status 'Void'; subsequent GET search shows 'Void'.
    - Idempotency: void again → success with no state change; still logged as update attempt.
  - Retract behavior:
    - DELETE → item removed; subsequent search by id returns not found. Verify SystemLog contains delete record for entity PaymentDetail with entity_id.
  - Authorization:
    - Access denied for non finance_admin/admin.
- UI:
  - Route visibility: Sidebar shows Payment Actions under Finance for finance_admin and admin only.
  - Search UX: entering OR or Invoice number filters results; pagination works.
  - Action buttons:
    - Void prompts confirm; updates row status to 'Void' and shows success toast.
    - Retract prompts confirm; removes row from list and shows success toast.
  - Edge cases:
    - Attempt to void a 'Void' row → handles gracefully, displays success/no-op messaging.
    - Attempt to retract an already deleted row → displays error from API.

[Implementation Order]
Implement backend endpoints first, then wire up frontend service, route, controller, template, and menu entry.

1) Backend: Routes and Controller/Service
   1.1 Create FinancePaymentActionsService with:
       - search(filters, page, perPage)
       - void(id, request)
       - retract(id, request)
   1.2 Create FinancePaymentActionsController with:
       - GET /finance/payment-actions/search (middleware role:finance_admin,admin)
       - POST /finance/payment-actions/{id}/void (middleware role:finance_admin,admin)
       - DELETE /finance/payment-actions/{id}/retract (middleware role:finance_admin,admin)
   1.3 Update laravel-api/routes/api.php to register routes (place near other finance routes).
2) Frontend: Service, Route, Controller, Template
   2.1 Create features/finance/payment-actions/payment-actions.service.js:
       - search(params), void(id, payload?), retract(id)
   2.2 Add route in core/routes.js for "/finance/payment-actions" with requiredRoles ["finance_admin","admin"].
   2.3 Create controller features/finance/payment-actions/payment-actions.controller.js:
       - manage filters, results, actions, pagination, and messaging.
   2.4 Create template features/finance/payment-actions/payment-actions.html:
       - Simple search form (OR/Invoice), results table, and buttons for Void/Retract.
   2.5 Update sidebar.controller.js to add "Payment Actions" under Finance.
3) QA
   3.1 Seed test data; verify search/void/retract end-to-end. Confirm SystemLog entries for update/delete.
   3.2 Validate RBAC blocks non-authorized users and shows menu appropriately.
