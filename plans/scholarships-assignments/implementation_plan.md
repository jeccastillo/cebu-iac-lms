# Implementation Plan

[Overview]
Create a new role-gated page and supporting APIs for Scholarship/Admin users to assign scholarships or discounts to a student per term using tb_mas_student_discount, saving new assignments as pending with an option to activate them later by changing status to applied.

This implementation extends existing scholarship/discount catalog and read-only endpoints by adding assignment CRUD (create, list, apply, delete) scoped to student and term (syid). It introduces a new AngularJS page “Scholarship Assignments” to search a student, select a term, list current assignments (with assignment statuses), add new scholarships/discounts as pending, and apply them when needed. Backend services reuse StudentDiscount model and augment ScholarshipService to manage assignment rows including status. Routes are protected for users with scholarship or admin roles.

[Types]  
Introduce assignment-specific types and payload contracts, adding a status field with allowed values pending and applied.

Data structures and validation:
- StudentDiscountAssignment (DB row / normalized API shape)
  - id: number (maps to tb_mas_student_discount.intID)
  - student_id: number (tb_mas_student_discount.student_id)
  - syid: number (tb_mas_student_discount.syid)
  - discount_id: number (tb_mas_student_discount.discount_id, FK to tb_mas_scholarships.intID)
  - status: 'pending' | 'applied' (tb_mas_student_discount.status; default 'pending')
  - created_by?: number|null
  - updated_by?: number|null
  - created_at?: string|null (if table supports; otherwise omitted)
  - updated_at?: string|null (if table supports; otherwise omitted)

- AssignmentCreatePayload (POST /api/v1/scholarships/assignments)
  - student_id: integer (required)
  - syid: integer (required)
  - discount_id: integer (required) – points to a scholarship/discount catalog row
  - status?: string (ignored if provided; API forces 'pending' on create)
  Validation:
  - student_id: required|integer
  - syid: required|integer
  - discount_id: required|integer|exists:tb_mas_scholarships,intID
  Notes: Upsert behavior on (student_id, syid, discount_id) – if exists, keep as-is (idempotent) or update status back to pending as an option (see upsert rule below).

- AssignmentApplyPayload (PATCH /api/v1/scholarships/assignments/apply)
  - ids: integer[] (required, non-empty)
  Validation:
  - ids: required|array|min:1
  - each ids.*: integer|exists:tb_mas_student_discount,intID
  Behavior:
  - For each id in ids, set status = 'applied'. Idempotent if already applied.

- AssignmentListQuery (GET /api/v1/scholarships/assignments)
  - syid: integer (required)
  - student_id?: integer (optional – to list for a specific student)
  - q?: string (optional – filter by student number or name, when student_id not provided)
  Response:
  - items: StudentDiscountAssignment[] with attached scholarship name, deduction_type, deduction_from

- AssignedQuery augmentation (GET /api/v1/scholarships/assigned)
  - Already exists; update response item to include assignment_status (merged from sd.status, default 'applied' if missing)
  Response item fields:
  - id, syid, discount_id, name, deduction_type, deduction_from, status (catalog status), assignment_status ('pending'|'applied')

[Files]
Add new API endpoints, request validators, service logic, SPA route, page, and service; modify existing controller/service to attach assignment_status.

Detailed breakdown:
- New files to be created:
  - laravel-api/app/Http/Requests/Api/V1/ScholarshipAssignmentStoreRequest.php
    - Purpose: Validate AssignmentCreatePayload for creating pending assignments.
  - laravel-api/app/Http/Requests/Api/V1/ScholarshipAssignmentApplyRequest.php
    - Purpose: Validate AssignmentApplyPayload for applying (activating) assignments.
  - laravel-api/database/migrations/2025_09_09_000900_add_status_to_tb_mas_student_discount.php
    - Purpose: Guarded migration to add status column (VARCHAR(20) or ENUM) default 'pending' if not present; add composite index (student_id, syid, discount_id) if not present.
  - frontend/unity-spa/features/scholarship/assignments/assignments.service.js
    - Purpose: AngularJS $http wrapper for new assignments endpoints.
  - frontend/unity-spa/features/scholarship/assignments/assignments.controller.js
    - Purpose: Controller for the scholarship assignments page.
  - frontend/unity-spa/features/scholarship/assignments/assignments.html
    - Purpose: UI for selecting student and term, listing assignments, creating pending entries, and applying them.

- Existing files to be modified:
  - laravel-api/app/Services/ScholarshipService.php
    - Add: assignmentUpsert(array $payload): array
    - Add: listAssignments(array $filters): array
    - Add: applyAssignments(array $ids, ?int $actorId = null): array{updated:int}
    - Add: deleteAssignment(int $id): array{deleted:bool}
    - Modify: assigned(...) to include sd.status as assignment_status in result mapping.
  - laravel-api/app/Http/Controllers/Api/V1/ScholarshipController.php
    - Add actions: assignments (GET), assignmentsStore (POST), assignmentsApply (PATCH), assignmentsDelete (DELETE)
    - Wire to new Request classes; role middleware 'scholarship,admin'.
  - laravel-api/routes/api.php
    - Add routes under /api/v1:
      - GET /scholarships/assignments (role: scholarship,admin)
      - POST /scholarships/assignments (role: scholarship,admin)
      - PATCH /scholarships/assignments/apply (role: scholarship,admin)
      - DELETE /scholarships/assignments/{id} (role: scholarship,admin)
    - Keep existing /scholarships/assigned and /scholarships/enrolled.
  - frontend/unity-spa/core/routes.js
    - Add route: "/scholarship/assignments" → new page (role: ['scholarship','admin'])
  - frontend/unity-spa/core/roles.constants.js
    - No change needed (pattern '^/scholarship/.*$' already role-gated); verify page access works.

- Files to be deleted or moved
  - None.

- Configuration file updates
  - None.

[Functions]
Add assignment CRUD functions on backend and wire frontend service/controller calls to them.

Detailed breakdown:
- New functions:
  - App\Services\ScholarshipService::assignmentUpsert(array $payload): array
    - Purpose: Insert tb_mas_student_discount row with status 'pending'; if row exists for (student_id, syid, discount_id) then return existing row (idempotent) or update status to 'pending' if currently 'applied' and client requests re-pending (decision: keep simple and idempotent – do not auto-downgrade; only create when missing).
    - Behavior: Validate existence of scholarship/discount id in tb_mas_scholarships; optionally check student exists; return normalized assignment array.
  - App\Services\ScholarshipService::listAssignments(array $filters): array
    - Signature: listAssignments(['syid' => int, 'student_id' => ?int, 'q' => ?string])
    - Purpose: Return joined rows sd + sc (+ u if q filter used) with assignment status and scholarship metadata.
  - App\Services\ScholarshipService::applyAssignments(array $ids, ?int $actorId = null): array{updated:int}
    - Purpose: Bulk set status='applied' for given assignment IDs; ignore already applied; return count updated.
  - App\Services\ScholarshipService::deleteAssignment(int $id): array{deleted:bool}
    - Purpose: Delete an assignment by id; allow deletion for pending (recommended), allow for applied only if business rule permits (decision: allow delete only if status='pending'; otherwise 422).
  - App\Http\Controllers\Api\V1\ScholarshipController::assignments(Request $request): JsonResponse
    - Purpose: GET list endpoint, reading syid, optional student_id or q.
  - App\Http\Controllers\Api\V1\ScholarshipController::assignmentsStore(ScholarshipAssignmentStoreRequest $request): JsonResponse
    - Purpose: POST to create pending assignment.
  - App\Http\Controllers\Api\V1\ScholarshipController::assignmentsApply(ScholarshipAssignmentApplyRequest $request): JsonResponse
    - Purpose: PATCH to set pending → applied for IDs.
  - App\Http\Controllers\Api\V1\ScholarshipController::assignmentsDelete(int $id): JsonResponse
    - Purpose: DELETE single assignment (pending only).
  - Frontend: features/scholarship/assignments/assignments.service.js
    - list(params): Promise – GET /api/v1/scholarships/assignments
    - create(payload): Promise – POST /api/v1/scholarships/assignments
    - apply(ids): Promise – PATCH /api/v1/scholarships/assignments/apply
    - remove(id): Promise – DELETE /api/v1/scholarships/assignments/{id}
    - helpers: listCatalog(filters) via existing /api/v1/scholarships
  - Frontend: features/scholarship/assignments/assignments.controller.js
    - load(): init terms (via existing SchoolYearsService or Generic /terms), student search (reuse students list? simple input for ID), and current assignments for selected student+term.
    - add(): create pending entry.
    - bulkApply(): apply selected pending entries.
    - remove(item): delete pending entry.

- Modified functions:
  - App\Services\ScholarshipService::assigned(int $syid, ?int $studentId, ?string $studentNumber): array
    - Change: SELECT 'sd.status as assignment_status' and merge into each item.
  - App\Http\Controllers\Api\V1\ScholarshipController::assigned(Request $request)
    - Response: include assignment_status in scholarships[] and discounts[] items.

- Removed functions:
  - None.

[Classes]
Introduce two Request classes; reuse StudentDiscount model; no new Eloquent models needed.

Detailed breakdown:
- New classes:
  - App\Http\Requests\Api\V1\ScholarshipAssignmentStoreRequest
    - rules(): student_id:int|required; syid:int|required; discount_id:int|required|exists:tb_mas_scholarships,intID
  - App\Http\Requests\Api\V1\ScholarshipAssignmentApplyRequest
    - rules(): ids:array|required|min:1; ids.*:integer|exists:tb_mas_student_discount,intID

- Modified classes:
  - App\Http\Controllers\Api\V1\ScholarshipController
    - Add 4 new methods and route bindings; update assigned() mapping for assignment_status.
  - App\Services\ScholarshipService
    - Add 4 new methods and update assigned() to include assignment_status.
  - App\Models\StudentDiscount (optional minor)
    - Add protected $fillable = ['student_id','syid','discount_id','status','created_by','updated_by']; ensure timestamps = false kept.

- Removed classes:
  - None.

[Dependencies]
No external dependencies required; use existing Laravel, DB facade, and AngularJS stack.

Details:
- No composer package changes.
- Migration uses Schema facade; service uses DB and existing models.
- Frontend uses existing $http, role middleware, and services (LinkService, possibly SchoolYearsService if available).

[Testing]
Add feature tests for assignment endpoints and perform manual UI validation.

Test plan:
- Backend Feature tests (new file suggestions):
  - laravel-api/tests/Feature/Api/V1/ScholarshipAssignmentsTest.php
    - test_create_pending_assignment_success()
    - test_create_duplicate_is_idempotent()
    - test_apply_single_and_bulk()
    - test_delete_pending_only_and_block_delete_applied()
    - test_list_assignments_by_student_and_term()
    - test_assigned_includes_assignment_status()
- Manual/API checks:
  - POST /api/v1/scholarships/assignments with valid payload → 201 and assignment status 'pending'.
  - GET /api/v1/scholarships/assignments?syid=...&amp;student_id=... → includes items with assignment_status.
  - PATCH /api/v1/scholarships/assignments/apply with ids → updates assignment_status to 'applied'.
  - DELETE /api/v1/scholarships/assignments/{id} when pending → 200 deleted; when applied → 422.
  - GET /api/v1/scholarships/assigned?syid=...&amp;student_id=... → each item includes assignment_status.
- Frontend manual:
  - Access /#/scholarship/assignments with scholarship/admin role; add pending entries; apply; refresh; verify state and visibility in /scholarships/assigned panel if used elsewhere.

[Implementation Order]
Implement database schema safeguards first, then backend endpoints, then frontend page, and finally tests and validation.

1) Database migration
   - Create guarded migration to add tb_mas_student_discount.status (default 'pending') and index (student_id, syid, discount_id).
   - Do not break existing timestamps behavior; keep StudentDiscount::$timestamps = false.

2) Backend service and controller
   - Update ScholarshipService::assigned to include assignment_status.
   - Implement ScholarshipService::assignmentUpsert, listAssignments, applyAssignments, deleteAssignment.
   - Add ScholarshipAssignmentStoreRequest and ScholarshipAssignmentApplyRequest.
   - Add controller endpoints: assignments (GET), assignmentsStore (POST), assignmentsApply (PATCH), assignmentsDelete (DELETE).
   - Wire routes in api.php with role: scholarship,admin.

3) Frontend SPA
   - Add route "/scholarship/assignments" in frontend/unity-spa/core/routes.js (requiredRoles: ['scholarship','admin']).
   - Create assignments.service.js to call the new APIs and leverage existing /scholarships listing to populate selection (with filters: status=active, deduction_type in ['scholarship','discount']).
   - Create assignments.controller.js to manage page state (select student/term, list current rows, add pending, bulk apply, delete pending).
   - Create assignments.html UI: student picker (id input or autocomplete), term dropdown from Generic /terms, two lists (pending, applied), and controls.

4) Tests and validation
   - Add Feature tests as above (optional within current scope).
   - Manual validation via Postman/cURL and SPA UI.

5) Documentation and handover
   - Update plans/scholarships/TODO.md and plans/scholarships/implementation_plan.md references to note new endpoints and page.
   - Optional: link page from Scholarship sidebar/menu if present.

task_progress Items:
- [ ] Step 1: Add guarded migration to add status column (default 'pending') and composite index to tb_mas_student_discount
- [ ] Step 2: Update ScholarshipService::assigned to include assignment_status from sd.status
- [ ] Step 3: Implement ScholarshipService methods: assignmentUpsert, listAssignments, applyAssignments, deleteAssignment
- [ ] Step 4: Add ScholarshipAssignmentStoreRequest and ScholarshipAssignmentApplyRequest validators
- [ ] Step 5: Add controller endpoints (assignments list/store/apply/delete) and bind routes with role: scholarship,admin
- [ ] Step 6: Add SPA route /scholarship/assignments and build assignments.service.js
- [ ] Step 7: Build assignments.controller.js and assignments.html for pending/apply flows
- [ ] Step 8: Manual/API verification and adjust UI/UX (catalog filter, error handling)
- [ ] Step 9: Add Feature tests (optional) and update docs/TODOs
