# Implementation Plan

[Overview]
Deliver full CRUD for the Scholarship catalog stored in tb_mas_scholarships, plus AngularJS SPA pages for managing rows. CRUD includes create, read, update, soft-delete (status=inactive), and restore. The existing read-only endpoints remain; we add RESTful writes and a small SPA under /scholarship/scholarships.

The Laravel API already exposes read-only listing/assignment queries for scholarships and discounts. This plan adds admin-facing catalog management (definitions master) for scholarships/discounts that are referenced by student assignments and tuition computation. Soft-delete adheres to operational expectations while preserving historical references. The SPA enables users with scholarship or admin roles to manage the catalog in a unified UI and is designed to be consistent with existing AngularJS patterns in the repo.

[Types]  
Introduce constrained enums for scholarship attributes and enforce uniqueness of code and name.

Domain model and validation:
- Scholarship (tb_mas_scholarships)
  - intID: int (PK)
  - code: string (unique, non-empty, max ~64)
  - name: string (unique, non-empty, max ~255)
  - deduction_type: enum('scholarship', 'discount')
  - deduction_from: enum('in-house', 'external')
  - status: enum('active', 'inactive', 'suspended', 'revoked', 'expired', 'pending')
  - percent/percentage: float [0..100] nullable
  - fixed_amount/amount: float [0..] nullable
  - description: text nullable

Rules and relationships:
- Uniqueness: name unique, code unique (app-validated, with optional DB unique indexes).
- Soft delete policy: changing status to 'inactive' for DELETE; restore sets back to 'active'.
- Validation: 
  - percent: 0..100
  - fixed_amount: >= 0
  - deduction_type/in/from restricted to enums above
  - status restricted to approved set
  - Note: both percent and fixed_amount may be null; discounts/scholarships may be descriptive-only for future assignment logic.

Frontend JS type (JSDoc for clarity):
- @typedef {Object} Scholarship
  - {number} id
  - {string} code
  - {string} name
  - {'scholarship'|'discount'} deduction_type
  - {'in-house'|'external'} deduction_from
  - {'active'|'inactive'|'suspended'|'revoked'|'expired'|'pending'} status
  - {number|null} percent
  - {number|null} fixed_amount
  - {string|null} description

[Files]
Add new API endpoints and AngularJS pages; modify existing controller, service, routes, and sidebar.

New files to be created:
- laravel-api/app/Http/Requests/Api/V1/ScholarshipStoreRequest.php
  - Purpose: Validate request payload for creating a scholarship.
- laravel-api/app/Http/Requests/Api/V1/ScholarshipUpdateRequest.php
  - Purpose: Validate payload for updating a scholarship; handle uniqueness ignoring current row.
- frontend/unity-spa/features/scholarship/scholarships/scholarships.service.js
  - Purpose: AngularJS service wrapping /api/v1/scholarships CRUD endpoints.
- frontend/unity-spa/features/scholarship/scholarships/scholarships.controller.js
  - Purpose: AngularJS controller for listing, creating, editing, soft-deleting, and restoring scholarships.
- frontend/unity-spa/features/scholarship/scholarships/list.html
  - Purpose: AngularJS template for scholarship catalog CRUD UI.

Optional (if DB constraints are desirable):
- laravel-api/database/migrations/2025_09_09_000900_add_unique_indexes_to_tb_mas_scholarships.php
  - Purpose: Add unique indexes on (code) and (name) if not present. Gracefully ignore if already present.

Existing files to be modified:
- laravel-api/app/Http/Controllers/Api/V1/ScholarshipController.php
  - Add: show(int $id), store(ScholarshipStoreRequest $req), update(ScholarshipUpdateRequest $req, int $id), destroy(int $id) soft-disables, restore(int $id) re-activates.
  - Keep: index/assigned/enrolled as-is; retain upsert assignment stub to avoid behavioral regression.
- laravel-api/app/Services/ScholarshipService.php
  - Add methods:
    - get(int $id): array|null
    - create(array $data): array
    - update(int $id, array $data): array
    - softDelete(int $id): array (returns updated row snapshot or status)
    - restore(int $id): array
  - Reuse Eloquent model Scholarship or DB facade; prefer Eloquent for writes for consistency and guarded=[].
- laravel-api/app/Http/Resources/ScholarshipResource.php
  - No functional changes required; already maps common fields (id/name/code/etc). Ensure it reads both array and object sources.
- laravel-api/routes/api.php
  - Register RESTful routes under /api/v1/scholarships for catalog CRUD; protect with middleware role:scholarship,admin.
  - Retain existing read-only list/assigned/enrolled and the upsert/delete stubs (or replace delete stub with soft-delete implementation).
- frontend/unity-spa/core/routes.js
  - Add route:
    - "/scholarship/scholarships" -> features/scholarship/scholarships/list.html, ScholarshipsController, requiredRoles: ["scholarship", "admin"].
- frontend/unity-spa/index.html
  - Include scripts in correct order:
    - features/scholarship/scholarships/scholarships.service.js
    - features/scholarship/scholarships/scholarships.controller.js
- frontend/unity-spa/shared/components/sidebar/sidebar.html
  - Add secondary menu item under Scholarships:
    - Link to "#/scholarship/scholarships" labeled "Scholarship Catalog".

[Functions]
Extend ScholarshipController/Service with create, read single, update, soft-delete, restore.

New functions:
- ScholarshipController::show(int $id): JsonResponse
  - File: laravel-api/app/Http/Controllers/Api/V1/ScholarshipController.php
  - Purpose: Fetch one scholarship by id; 404 if missing.
- ScholarshipController::store(ScholarshipStoreRequest $request): JsonResponse
  - Create a new scholarship from validated data; 201 on success; handles duplicate errors.
- ScholarshipController::update(ScholarshipUpdateRequest $request, int $id): JsonResponse
  - Update fields partially; 404 if missing; returns updated resource.
- ScholarshipController::destroy(int $id): JsonResponse
  - Soft delete: set status='inactive'; idempotent; 200 with message.
- ScholarshipController::restore(int $id): JsonResponse
  - Set status='active' (if row exists); returns updated resource.

- ScholarshipService::get(int $id): array|null
- ScholarshipService::create(array $data): array
- ScholarshipService::update(int $id, array $data): array
- ScholarshipService::softDelete(int $id): array
- ScholarshipService::restore(int $id): array

Modified functions:
- ScholarshipController::index(Request $request): keep as-is (already supports filters and q).
- Routes: add mappings for show/store/update/destroy/restore.

Removed functions:
- None. The existing upsert/delete stubs remain for compatibility; the stub delete will be replaced by soft-delete implementation in destroy().

[Classes]
Add two FormRequest classes and modify existing controller/service.

New classes:
- ScholarshipStoreRequest (laravel-api/app/Http/Requests/Api/V1/ScholarshipStoreRequest.php)
  - Rules:
    - code: required|string|max:64|unique:tb_mas_scholarships,code
    - name: required|string|max:255|unique:tb_mas_scholarships,name
    - deduction_type: required|in:discount,scholarship
    - deduction_from: required|in:in-house,external
    - percent: nullable|numeric|min:0|max:100
    - fixed_amount: nullable|numeric|min:0
    - status: sometimes|in:active,inactive,suspended,revoked,expired,pending
    - description: nullable|string|max:2000
- ScholarshipUpdateRequest (laravel-api/app/Http/Requests/Api/V1/ScholarshipUpdateRequest.php)
  - Rules (all sometimes):
    - code: sometimes|string|max:64|unique:tb_mas_scholarships,code,{id},intID
    - name: sometimes|string|max:255|unique:tb_mas_scholarships,name,{id},intID
    - deduction_type, deduction_from, percent, fixed_amount, status, description: same constraints as store.

Modified classes:
- ScholarshipController: add RESTful CRUD methods and import new FormRequests; wire to ScholarshipService; responses via ScholarshipResource.
- ScholarshipService: implement create/update/softDelete/restore/get using Eloquent Model Scholarship.
- Scholarship (Model): no change (table/PK already set).

Removed classes:
- None.

[Dependencies]
No new external packages. Optional: DB unique index migration for code and name. If applied:
- MySQL unique indexes:
  - ux_scholarships_code on tb_mas_scholarships(code)
  - ux_scholarships_name on tb_mas_scholarships(name)

[Testing]
End-to-end and unit-lite via HTTP tests plus manual cURL, and SPA smoke.

Backend cURL tests (assume BASE=/api/v1):
- Create (201):
  - POST /api/v1/scholarships
  - body: {"code":"RES-SCH","name":"Resident Scholar","deduction_type":"scholarship","deduction_from":"in-house","percent":100,"status":"active"}
- Duplicate name/code (422):
  - Repeat POST with same code or name.
- Show (200/404):
  - GET /api/v1/scholarships/{id}
- Update (200):
  - PUT /api/v1/scholarships/{id} body: {"fixed_amount":5000,"percent":null}
- Soft delete (200 idempotent):
  - DELETE /api/v1/scholarships/{id} -> status becomes 'inactive'
  - Repeat DELETE -> still 200, no change
- Restore (200):
  - POST /api/v1/scholarships/{id}/restore -> status 'active'
- List (200, filtered search):
  - GET /api/v1/scholarships?q=resident&amp;status=active
- Authorization:
  - All write routes guarded by role:scholarship,admin.

Frontend SPA validation:
- Navigate to #/scholarship/scholarships with role=scholarship or admin.
- Create a scholarship via form; verify in list.
- Edit row; verify data persisted.
- Soft-delete; row shows inactive; filter by status.
- Restore; row returns to active state.
- Search/filter by code/name/deduction_type/from/status.

[Implementation Order]
Implement API first, then integrate SPA, then test end-to-end.

1) Backend: Requests and Service
   - Add ScholarshipStoreRequest and ScholarshipUpdateRequest with validation/unique rules.
   - Extend ScholarshipService with get/create/update/softDelete/restore using Eloquent.

2) Backend: Controller and Routes
   - Update ScholarshipController to add show/store/update/destroy/restore methods returning ScholarshipResource.
   - Update routes/api.php to add:
     - GET /scholarships/{id}
     - POST /scholarships (role:scholarship,admin)
     - PUT /scholarships/{id} (role:scholarship,admin)
     - DELETE /scholarships/{id} (role:scholarship,admin) — soft-delete
     - POST /scholarships/{id}/restore (role:scholarship,admin)
   - Preserve existing index/assigned/enrolled endpoints and upsert stub (for assignment parity).

3) Optional Migration
   - Create migration adding unique indexes to (code) and (name). Handle if already present.

4) Frontend: SPA Files
   - Create features/scholarship/scholarships/scholarships.service.js for API calls.
   - Create features/scholarship/scholarships/scholarships.controller.js handling list, form, soft-delete, restore, filters.
   - Create features/scholarship/scholarships/list.html UI: search, filters, table, create/edit modal, soft-delete/restore actions.

5) Frontend: Wiring
   - Update core/routes.js to register route "/scholarship/scholarships" with requiredRoles ["scholarship","admin"].
   - Update index.html to include the new service and controller scripts.
   - Update shared/components/sidebar/sidebar.html to add "Scholarship Catalog" link.

6) Testing
   - Run cURL tests for all endpoints including error cases (duplicate, 404).
   - Manual SPA smoke test ensuring role gating, CRUD operations, filters, and list reflect backend changes.
