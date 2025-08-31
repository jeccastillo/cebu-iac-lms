# Implementation Plan

[Overview]
Add a new Admin-only Payment Details management page under the Admin menu that allows searching, viewing, and editing rows from the payment_details table, with Laravel API endpoints for search/show/update and an AngularJS SPA screen styled using Tailwind utility classes.

This feature centralizes payment_details maintenance for system administrators. It builds on the existing Finance read endpoints but introduces an Admin-managed flow to correct or update records when necessary. The page will be accessible only to users with the admin role, routed at /admin/payment-details, grouped under Admin in the sidebar, and will use Tailwind CSS already applied across the SPA for consistent styling. The Laravel API will expose secure endpoints with admin authorization and dynamic column detection to support environments where payment_details schema varies.

[Types]  
Introduce request/response DTO shapes for the admin endpoints and the SPA controller models.

- PaymentDetailItem (API response item)
  - id: number (payment_details.id)
  - student_information_id?: number|null
  - student_number?: string|null
  - sy_reference?: number|null
  - description?: string|null
  - subtotal_order?: number|null
  - total_amount_due?: number|null
  - status?: string|null
  - or_no?: string|number|null (present when column exists)
  - or_number?: string|number|null (present when column exists)
  - invoice_number?: string|number|null (present when column exists)
  - method?: string|null (or payment_method?: string|null when present)
  - remarks?: string|null
  - mode_of_payment_id?: number|null
  - posted_at?: string|null (coalesced from paid_at/date/created_at)
  - created_at?: string|null
  - updated_at?: string|null
  - source: 'payment_details' (constant)
  - sy_label?: string (optional, if resolved/joined)

- PaymentDetailSearchFilters (API query params)
  - q?: string (search term against student_number, description, number columns)
  - student_number?: string
  - student_id?: number
  - syid?: number (term filter, maps to sy_reference where applicable)
  - mode?: 'or'|'invoice' (hints which number column to search)
  - or_number?: string|number (exact or partial, matches or_no/or_number)
  - invoice_number?: string|number
  - status?: string
  - date_from?: string (Y-m-d or ISO)
  - date_to?: string (Y-m-d or ISO)
  - page?: number (pagination)
  - per_page?: number (default 20)

- PaymentDetailUpdatePayload (API body for PATCH)
  - description?: string
  - subtotal_order?: number
  - total_amount_due?: number
  - status?: string
  - remarks?: string
  - method?: string (or payment_method?: string)
  - mode_of_payment_id?: number
  - posted_at?: string (maps to paid_at/date/created_at depending on column)
  - or_no?: string|number (if column exists and allowed to change)
  - or_number?: string|number (if column exists and allowed to change)
  - invoice_number?: string|number (if column exists and allowed to change)

Validation rules:
- subtotal_order: numeric, gt:0
- total_amount_due: numeric, gte:subtotal_order (if provided)
- status: string in ['Paid','Void','Pending','Cancelled','Error'] (configurable)
- posted_at: valid date/datetime
- or_no/or_number/invoice_number: numeric or string, unique vs other rows (validated server-side)
- mode_of_payment_id: integer, must exist in payment_modes.id when table present

[Files]
Create new admin SPA feature and Laravel API endpoints; update routes and sidebar.

- New frontend files
  - frontend/unity-spa/features/admin/payment-details/edit.html
    - Tailwind-styled page:
      - Filters: q, student_number, term selector (reuse TermService), mode, number fields, date range, status.
      - Results table with pagination.
      - Inline edit modal/drawer to edit selected row fields.
      - Admin-only guards and UX feedback.
  - frontend/unity-spa/features/admin/payment-details/payment-details.controller.js
    - AngularJS controller for search, load, select, edit, and save updates.
  - frontend/unity-spa/features/admin/payment-details/payment-details.service.js
    - AngularJS service calling Laravel admin endpoints (search/show/update).

- Existing frontend to modify
  - frontend/unity-spa/core/routes.js
    - Add route .when('/admin/payment-details', { templateUrl: 'features/admin/payment-details/edit.html', controller: 'AdminPaymentDetailsController', controllerAs: 'vm', requiredRoles: ['admin'] })
  - frontend/unity-spa/shared/components/sidebar/sidebar.controller.js
    - Under group key: 'admin', add child: { label: 'Payment Details', path: '/admin/payment-details' }
  - frontend/unity-spa/shared/components/sidebar/sidebar.html
    - No structural change required (links rendered from controller), verify access gating via vm.canAccess.

- New backend files
  - laravel-api/app/Http/Controllers/Api/V1/PaymentDetailAdminController.php
    - index(): search and pagination
    - show(int $id): fetch single row with dynamic fields
    - update(int $id, PaymentDetailUpdateRequest $request): apply updates with validation and dynamic columns
  - laravel-api/app/Http/Requests/Api/V1/PaymentDetailUpdateRequest.php
    - Validation and normalization of update payload
  - laravel-api/app/Services/PaymentDetailAdminService.php
    - Column detection (Schema::hasColumn), search builder, uniqueness checks for number columns, coalescing date fields, safe-update writer

- Existing backend to modify
  - laravel-api/routes/api.php
    - Register:
      - GET /api/v1/finance/payment-details/admin → PaymentDetailAdminController@index
      - GET /api/v1/finance/payment-details/{id} → PaymentDetailAdminController@show
      - PATCH /api/v1/finance/payment-details/{id} → PaymentDetailAdminController@update

- Files to delete or move
  - None.

- Configuration updates
  - None (reuse existing Tailwind utilities in SPA and existing auth guard/middleware in Laravel).

[Functions]
Introduce controller methods on both frontend and backend and minimally update routing.

- New frontend functions
  - AdminPaymentDetailsController (payment-details.controller.js)
    - init(): void — bootstrap roles, load term from TermService, default filters
    - search(resetPage: boolean): Promise — call service.search with filters, update vm.items and vm.pagination
    - select(item: PaymentDetailItem): void — set vm.selected and open edit drawer/modal
    - save(): Promise — validate client-side, call service.update(selected.id, payload), toast success, refresh search results
    - resetFilters(): void — clear filters and reload
  - AdminPaymentDetailsService (payment-details.service.js)
    - search(params: PaymentDetailSearchFilters): Promise<Paginated<PaymentDetailItem>>
    - get(id: number): Promise<PaymentDetailItem>
    - update(id: number, payload: PaymentDetailUpdatePayload): Promise<PaymentDetailItem>

- Modified frontend functions
  - routes.js — add new route definition
  - sidebar.controller.js — push new Admin child link

- New backend functions (PaymentDetailAdminController.php)
  - index(Request $request): JsonResponse
    - Authorize admin, parse filters, delegate to service, return paginated response
  - show(int $id): JsonResponse
    - Authorize admin, fetch item, coalesce fields, return
  - update(int $id, PaymentDetailUpdateRequest $request): JsonResponse
    - Authorize admin, validate, delegate to service.update, return updated item

- New backend service methods (PaymentDetailAdminService.php)
  - detectColumns(): array — map number/payment/date/name/email/contact columns if present
  - search(array $filters): LengthAwarePaginator|array — query builder with dynamic column support
  - getById(int $id): array|null — normalized item
  - update(int $id, array $payload): array — whitelist fields, apply column remapping, validate uniqueness for number columns, safe transaction, return updated

[Classes]
Add one Laravel controller, one form request, and one service.

- New classes
  - App\Http\Controllers\Api\V1\PaymentDetailAdminController
    - Methods: index, show, update
    - Middleware: auth (CodeIgniterSessionGuard) and explicit admin check via UserContextResolver
  - App\Http\Requests\Api\V1\PaymentDetailUpdateRequest
    - Rules and messages; authorize() returns true but controller checks admin role
  - App\Services\PaymentDetailAdminService
    - No inheritance; pure service for DB operations on payment_details with Schema::hasColumn checks

- Modified classes
  - None functionally; only api.php route registrations.

[Dependencies]
No new external packages.

- Frontend uses existing AngularJS and Tailwind utility classes (already present).
- Backend uses Illuminate DB, Validation, Schema facade (already present).

[Testing]
Use manual test procedures and an optional smoke script; consider adding lightweight Laravel feature tests if time permits.

- Manual tests
  1) Access control:
     - Login as non-admin → navigate to /#/admin/payment-details → expect redirect/403/hidden.
     - Login as admin → page loads; Admin group shows Payment Details link.
  2) Search:
     - Search by student_number, term, mode=or, exact or_number; results render newest-first (by posted_at desc then id).
     - Date range filters and pagination work.
  3) Edit and save:
     - Edit description, amount (subtotal_order), status, remarks, method/payment_method, posted_at, mode_of_payment_id.
     - If changing or_number/invoice_number: server validates uniqueness across payment_details and applicable cross-checks (if implemented).
     - Save → success toast → list refresh shows updated values.
  4) Column variants:
     - Environments missing certain columns (e.g., method vs payment_method) continue to work; fields absent from schema are hidden and not sent.
  5) Error cases:
     - Invalid subtotal_order (<=0) → 422.
     - Duplicate or_number → 422 with helpful message.
     - Server errors log to console; SPA shows toast "Update failed."

- Optional scripts
  - laravel-api/scripts/test_payment_details_admin.php
    - Simulate GET /finance/payment-details/admin and PATCH update for a known id to smoke the flow.

[Implementation Order]
Implement backend first, then frontend integration, then wiring and verification.

1) Backend: Service
   - Create PaymentDetailAdminService with detectColumns, search, getById, update; add uniqueness check for number columns and coalesced date handling.
2) Backend: Request
   - Add PaymentDetailUpdateRequest with strict validation and conditional rules.
3) Backend: Controller
   - Add PaymentDetailAdminController (index, show, update) with admin authorization using UserContextResolver.
4) Backend: Routes
   - Register routes in routes/api.php under /api/v1/finance/payment-details/admin (index) and /api/v1/finance/payment-details/{id} (show, update).
5) Frontend: Service
   - Create AdminPaymentDetailsService with search/get/update methods pointing to the new endpoints.
6) Frontend: Controller + View
   - Create AdminPaymentDetailsController and edit.html with Tailwind UI:
     - Filters section, results table with paging, and edit drawer/modal.
7) Frontend: Routes
   - Update core/routes.js to register /admin/payment-details with requiredRoles ['admin'].
8) Frontend: Sidebar
   - Update sidebar.controller.js to add Admin → Payment Details link (path '/admin/payment-details'); visibility via canAccess.
9) QA Pass
   - Verify end-to-end flows and role restrictions; validate editing across variant schemas.
