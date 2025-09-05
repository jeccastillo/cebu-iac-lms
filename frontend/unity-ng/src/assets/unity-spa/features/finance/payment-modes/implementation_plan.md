# Implementation Plan

[Overview]
Implement end-to-end CRUD for payment_modes under Finance using Laravel API (v1 namespace) and AngularJS (unity-spa). The feature will manage payment modes configuration (name, type, charge, channels/methods, active flags, image url, nonbank flag) with soft delete and restore, gated to finance and admin roles.

The backend will expose RESTful endpoints under /api/v1/payment-modes with role middleware. The frontend will add Finance pages for listing and editing payment modes, route-gated to ["finance","admin"], and linked under the Finance section in the sidebar. Validation will follow the current schema and the business decisions: flat currency charge; no name uniqueness; soft delete with a restore endpoint.

[Types]  
Introduce a PaymentMode entity and related DTOs for consistency across API and UI.

- Database Table: payment_modes (migration already present)
  - id: int (PK, increments)
  - name: varchar(64), required
  - image_url: text|null, optional URL
  - type: varchar(12), required, free-form (examples: bank, ewallet, otc, online)
  - charge: float default 0, flat currency amount (>= 0, 2 decimals suggested)
  - is_active: boolean default 1
  - pchannel: varchar(32), required (payment channel)
  - pmethod: varchar(32), required (payment method)
  - is_nonbank: boolean default 0
  - created_at/updated_at: timestamps
  - deleted_at: soft deletes

- Backend DTOs (API responses)
  - PaymentModeResource (single/list item)
    {
      id: number,
      name: string,
      image_url: string|null,
      type: string,
      charge: number,                // flat currency
      is_active: boolean|0|1,
      pchannel: string,
      pmethod: string,
      is_nonbank: boolean|0|1,
      created_at: string,
      updated_at: string
    }

- Validation Rules (Store/Update)
  - name: required|string|max:64
  - image_url: nullable|string|url
  - type: required|string|max:12
  - charge: required|numeric|min:0
  - is_active: sometimes|boolean
  - pchannel: required|string|max:32
  - pmethod: required|string|max:32
  - is_nonbank: sometimes|boolean

[Files]
Create new backend and frontend files and modify routing and navigation.

- Laravel (API)
  - New
    - app/Models/PaymentMode.php
      - Eloquent model with SoftDeletes, $table = 'payment_modes', $fillable matching columns, $casts for booleans and numeric charge.
    - app/Http/Controllers/Api/V1/PaymentModeController.php
      - index(Request): list with optional filters/sorting/pagination
      - show(int $id)
      - store(PaymentModeStoreRequest)
      - update(PaymentModeUpdateRequest, int $id)
      - destroy(int $id) // soft delete
      - restore(int $id) // POST restore endpoint
    - app/Http/Requests/Api/V1/PaymentModeStoreRequest.php
      - authorization true; rules as specified
    - app/Http/Requests/Api/V1/PaymentModeUpdateRequest.php
      - authorization true; rules same but all sometimes
    - app/Http/Resources/PaymentModeResource.php
      - Transforms model to API shape
    - tests/Feature/PaymentModesTest.php
      - Feature tests for auth, validation, CRUD, restore
  - Modified
    - routes/api.php
      - Add routes under Route::prefix('v1') with role middleware:
        - GET /payment-modes -> index (role:finance,admin)
        - GET /payment-modes/{id} -> show (role:finance,admin)
        - POST /payment-modes -> store (role:finance,admin)
        - PUT /payment-modes/{id} -> update (role:finance,admin)
        - DELETE /payment-modes/{id} -> destroy (role:finance,admin)
        - POST /payment-modes/{id}/restore -> restore (role:finance,admin)

- AngularJS (frontend/unity-spa)
  - New
    - features/finance/payment-modes/list.html
      - Table of payment modes with filters and actions (edit/delete/restore)
    - features/finance/payment-modes/edit.html
      - Form for create/update with validation (name, type, charge, pchannel, pmethod, image_url, flags)
    - features/finance/payment-modes/payment-modes.service.js
      - Wrap API calls (index/show/create/update/delete/restore)
    - features/finance/payment-modes/payment-modes.controller.js
      - List controller: loads data, handles filters, actions, pagination
    - features/finance/payment-modes/payment-mode-edit.controller.js
      - Edit controller: create/update forms, client-side validation, submit
  - Modified
    - core/routes.js
      - Add routes:
        - "/finance/payment-modes" -> list.html, PaymentModesController, requiredRoles ["finance","admin"]
        - "/finance/payment-modes/new" -> edit.html, PaymentModeEditController, requiredRoles ["finance","admin"]
        - "/finance/payment-modes/:id/edit" -> edit.html, PaymentModeEditController, requiredRoles ["finance","admin"]
    - shared/components/sidebar/sidebar.html
      - Under Finance section, add link to "#/finance/payment-modes" labeled "Payment Modes" visible to finance/admin

[Functions]
Define key backend controller methods and frontend functions to support CRUD.

- Backend (PaymentModeController)
  - index(Request $request): JsonResponse
    - Query params (optional): page, per_page (defaults 1, 20); sort (name|type|pchannel|pmethod|charge), order (asc|desc); filters: name (like), type, is_active, is_nonbank, pchannel, pmethod
    - Returns paginated list (data, meta: pagination)
  - show(int $id): JsonResponse
    - 404 if not found
  - store(PaymentModeStoreRequest $request): JsonResponse 201
    - Creates a PaymentMode
  - update(PaymentModeUpdateRequest $request, int $id): JsonResponse
    - Updates provided fields
  - destroy(int $id): JsonResponse
    - Soft deletes record; idempotent on already-deleted -> 204 or success message
  - restore(int $id): JsonResponse
    - Restores soft-deleted record; 404 if id not found at all

- Frontend (Angular)
  - PaymentModesService
    - list(params): GET /api/v1/payment-modes
    - get(id): GET /api/v1/payment-modes/{id}
    - create(payload): POST /api/v1/payment-modes
    - update(id, payload): PUT /api/v1/payment-modes/{id}
    - remove(id): DELETE /api/v1/payment-modes/{id}
    - restore(id): POST /api/v1/payment-modes/{id}/restore
  - PaymentModesController
    - vm.filters = { search, type, is_active, is_nonbank, pchannel, pmethod }
    - vm.sort = { field: 'name', order: 'asc' }
    - vm.page/perPage; vm.load()
    - vm.edit(id); vm.add(); vm.delete(id); vm.restore(id)
  - PaymentModeEditController
    - vm.model = { name, type, charge, pchannel, pmethod, image_url, is_active, is_nonbank }
    - vm.save() -> create/update with basic form validation
    - vm.cancel() -> navigate back

[Classes]
Enumerate new PHP classes with key details.

- app/Models/PaymentMode (new)
  - use SoftDeletes;
  - protected $table = 'payment_modes';
  - protected $fillable = ['name','image_url','type','charge','is_active','pchannel','pmethod','is_nonbank'];
  - protected $casts = ['is_active' => 'boolean','is_nonbank' => 'boolean','charge' => 'float'];

- app/Http/Controllers/Api/V1/PaymentModeController (new)
  - Methods as specified in [Functions], inject Request objects, use PaymentModeResource

- app/Http/Requests/Api/V1/PaymentModeStoreRequest (new)
  - rules(): as specified in [Types]; authorize(): true

- app/Http/Requests/Api/V1/PaymentModeUpdateRequest (new)
  - rules(): same but all fields sometimes; authorize(): true

- app/Http/Resources/PaymentModeResource (new)
  - toArray(): map model to API shape; ensure numeric charge casting

[Dependencies]
No new composer/npm dependencies are required. Use existing role middleware ("role:...") already employed in routes/api.php.

[Testing]
Adopt a layered testing approach across API and UI.

- Laravel Feature Tests (tests/Feature/PaymentModesTest.php)
  - AuthZ: deny non-finance/admin; allow finance/admin
  - Create: valid payload -> 201; invalid -> 422
  - Update: partial update; numeric/boolean casting
  - Delete: soft delete; ensure record present in withTrashed(); visible in trashed queries
  - Restore: restore soft-deleted record; 404 on unknown ID
  - Index filters/sorting/pagination: ensure correct set and metadata

- Manual QA (Angular)
  - Navigation: sidebar link visible in finance/admin
  - List: loads, filters work, pagination, sorting
  - Create/Update: validation messages and success toasts
  - Delete/Restore: record disappears/reappears as expected
  - Charge entry: enforces numeric (>= 0), two-decimal display

[Implementation Order]
Sequence tasks to minimize integration risk.

1) Backend scaffolding
   - Add PaymentMode model, requests, resource, controller.
   - Wire routes in routes/api.php with middleware role:finance,admin for all endpoints.

2) Controller logic
   - Implement index with filters/sort/pagination; show/store/update/destroy/restore; wrap responses with PaymentModeResource.

3) Feature tests
   - Add tests covering core behaviors; run locally to validate endpoints.

4) Frontend service and routing
   - Create payment-modes.service.js; add routes in core/routes.js; secure with requiredRoles ["finance","admin"].

5) UI pages and controllers
   - Implement list.html and payment-modes.controller.js (table, filters, actions).
   - Implement edit.html and payment-mode-edit.controller.js (form, save/cancel).

6) Sidebar integration
   - Add “Payment Modes” under Finance group in shared/components/sidebar/sidebar.html.

7) QA and polish
   - Validate formatting, casting, and error handling; add user feedback via toast.service.js.

Task Progress Items:
- [ ] Step 1: Add Laravel model, form requests, resource, controller; register routes with role gating
- [ ] Step 2: Implement controller methods (index/show/store/update/destroy/restore) with validation and casting
- [ ] Step 3: Add Laravel feature tests for auth, validation, CRUD, restore, and filtering/pagination
- [ ] Step 4: Create Angular service and register Finance routes for payment modes
- [ ] Step 5: Build Angular list and edit pages with controllers and client-side validation
- [ ] Step 6: Add sidebar link under Finance for “Payment Modes”
- [ ] Step 7: Perform manual QA and finalize UX copy and error handling
