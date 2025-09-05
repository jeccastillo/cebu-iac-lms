# Implementation Plan

[Overview]
Enable admin and cashier_admin roles to assign a cashier record to a specific faculty user with enforced constraints.

The current Cashier module stores an association column named user_id intended to reference Laravel&#39;s users table; however, the functional requirement is to bind cashier ownership to legacy faculty accounts (tb_mas_faculty.intID). This plan introduces a faculty_id linkage, enforces uniqueness per campus, disallows unassignment, and adds a searchable faculty picker in the SPA. The backend will expose a safe assignment API and a faculty search endpoint; the frontend will surface an Assign flow with validation and clear feedback. This work aligns Cashier administration with existing CI/legacy data models and role-based access middleware already present.

[Types]  
Introduce faculty_id (unsigned int) on tb_mas_cashiers and formalize response types for the Cashier JSON payloads.

Detailed type definitions:
- Database
  - tb_mas_cashiers
    - faculty_id: unsigned integer, not null after migration cutover (temporarily nullable during rollout); references tb_mas_faculty.intID
    - campus_id: unsigned integer, not null for new rows; used for per-campus uniqueness
    - Unique index: (campus_id, faculty_id)
    - user_id: deprecated (kept for backward-compat, not used)
- API DTO (response)
  - CashierRow:
    - id: number (tb_mas_cashiers.intID)
    - faculty_id: number
    - name: string (computed from tb_mas_faculty.strFirstname + &#34; &#34; + tb_mas_faculty.strLastname)
    - campus_id: number
    - temporary_admin: number (0|1)
    - or: { start: number|null, end: number|null, current: number|null }
    - invoice: { start: number|null, end: number|null, current: number|null }
    - stats?: { start, end, current, used_count, remaining_count, last_used }
- Validation rules
  - faculty_id: required, integer, exists in tb_mas_faculty.intID
  - campus consistency: faculty.campus_id must equal tb_mas_cashiers.campus_id
  - uniqueness per campus: no other cashier has same (campus_id, faculty_id)
  - disallow unassignment: faculty_id cannot be null once set (both create and update)

[Files]
Add a migration, new request class, and modify existing controllers/services and SPA assets to support assignment and search.

Detailed breakdown:
- New files to be created
  - laravel-api/database/migrations/2025_08_29_000700_add_faculty_id_to_tb_mas_cashiers.php
    - Adds faculty_id (unsigned integer)
    - Backfills where possible (noop if none)
    - Adds unique index (campus_id, faculty_id)
    - Optionally sets faculty_id NOT NULL after backfill (second step guarded)
  - laravel-api/app/Http/Requests/Api/V1/CashierAssignRequest.php
    - rules(): faculty_id required integer; forbid null
  - Backend (optional if not present): A faculty search route/method if current FacultyController doesn’t provide it
    - laravel-api/app/Http/Controllers/Api/V1/FacultyController.php (add method search if absent)
    - Route: GET /api/v1/faculty/search?query=...&amp;campus_id=... (role:cashier_admin,admin)
- Existing files to be modified
  - laravel-api/app/Models/Cashier.php
    - (No structural changes required; ensure timestamps=false compatibility remains; optionally add $fillable for faculty_id)
  - laravel-api/app/Http/Controllers/Api/V1/CashierController.php
    - index(): change join from users to tb_mas_faculty, compute name via strFirstname/strLastname
    - store(): accept faculty_id instead of user_id; enforce constraints (campus match and uniqueness)
    - update(): optionally accept faculty_id when sent (or keep it restricted and use dedicated assign())
    - new method assign($id, CashierAssignRequest): enforce constraints and persist faculty_id
  - laravel-api/app/Http/Requests/Api/V1/CashierStoreRequest.php
    - Replace user_id with faculty_id; add exists:tb_mas_faculty,intID and campus consistency validation at controller/service
  - laravel-api/app/Http/Requests/Api/V1/CashierUpdateRequest.php
    - Option A: leave as is (only temporary_admin / current pointers)
    - Option B: allow faculty_id here and route to same validation path
  - laravel-api/app/Services/CashierService.php
    - Add ensureFacultyAssignable(int $facultyId, int $campusId): array{ok:bool, reason?:string}
    - (Optionally) add helper to fetch faculty full name (or compute in SQL)
  - laravel-api/routes/api.php
    - Add route: PATCH /cashiers/{id}/assign (role:cashier_admin,admin)
    - Add route: GET /faculty/search (role:cashier_admin,admin) if not existing
  - laravel-api/scripts/seed_cashiers.php, laravel-api/scripts/smoke_cashiers.php, laravel-api/scripts/test_cashiers_api.php
    - Update references from user_id to faculty_id for any new seeds/tests
  - frontend/unity-spa/features/cashiers/cashiers.service.js
    - Add method assign(id, faculty_id): PATCH /cashiers/{id}/assign
    - Add method searchFaculty(query, campusId?) or reuse existing FacultyService
  - frontend/unity-spa/features/cashiers/cashiers.controller.js
    - Add UI flow: open assign dialog, search by name/email, pick one, call assign()
    - Enforce campus tie-in (disable candidates not matching campus, or filter query by campus_id)
  - frontend/unity-spa/features/cashiers/list.html
    - Add Assign button per row; dialog with searchable select (select2); show faculty name; disable unassign
- Files to be deleted or moved
  - None (user_id remains for backward-compat, flagged deprecated)
- Configuration file updates
  - None

[Functions]
Add a dedicated assignment function and faculty search while updating existing selection logic.

Detailed breakdown:
- New functions
  - CashierController::assign(int $id, CashierAssignRequest $request): JsonResponse
    - Purpose: Assign cashier to faculty_id with validation
    - Logic:
      - Load cashier
      - Validate faculty exists and campus match
      - Enforce uniqueness: no other cashier with same (campus_id, faculty_id)
      - Persist: set faculty_id; reject null (disallow unassign)
      - Return { success: true, data: { id } }
  - (If absent) FacultyController::search(Request $request): JsonResponse
    - Params: query (string), campus_id (optional; filter)
    - Returns: [{ id:intID, full_name, email, campus_id }]
- Modified functions
  - CashierController::index(Request $request)
    - Replace leftJoin(&#39;users&#39;,...) with leftJoin(&#39;tb_mas_faculty as f&#39;,&#39;f.intID&#39;,&#39;=&#39;,&#39;tb_mas_cashiers.faculty_id&#39;)
    - Select name via CONCAT(COALESCE(f.strFirstname,&#39;&#39;),&#39; &#39;,COALESCE(f.strLastname,&#39;&#39;)) as name
    - Include faculty_id in SELECT or map layer
  - CashierController::store(CashierStoreRequest $request)
    - Require faculty_id; enforce campus match; enforce (campus_id, faculty_id) uniqueness; no null faculty_id
    - Set $row->faculty_id instead of user_id
  - CashierController::update(...)
    - Optionally accept faculty_id and reuse same validation path as assign()
  - CashierService
    - Add ensureFacultyAssignable(int $facultyId, ?int $campusId): array{ok:bool, reason?:string}
      - Checks faculty existence and campus_id equality (must match), and uniqueness constraint conflicts
- Removed functions
  - None

[Classes]
Update existing controllers and requests; add a new request class for assignment.

Detailed breakdown:
- New classes
  - App\Http\Requests\Api\V1\CashierAssignRequest
    - rules(): [&#39;faculty_id&#39; => [&#39;required&#39;,&#39;integer&#39;]] (existence checked via DB join in controller/service)
- Modified classes
  - App\Http\Controllers\Api\V1\CashierController
    - As per Functions section
  - App\Http\Requests\Api\V1\CashierStoreRequest
    - Replace user_id with faculty_id rule
- Removed classes
  - None

[Dependencies]
No new external packages are required; reuse existing AngularJS select2 directive for searchable UI.

Details:
- Frontend uses existing shared/directives/select2.directive.js to implement a searchable dropdown.
- Backend uses Query Builder against legacy tables (tb_mas_faculty) through the default connection.

[Testing]
Add migration smoke, API internal smoke, and SPA manual tests.

Test file requirements and validation strategies:
- Migration
  - Run php artisan migrate, verify tb_mas_cashiers has faculty_id and unique index
- API
  - Update laravel-api/scripts/test_cashiers_api.php to:
    - Create or locate test faculty (from tb_mas_faculty)
    - Create cashier with campus_id
    - Call PATCH /cashiers/{id}/assign with faculty_id
    - Verify conflicts:
      - Assign same faculty to second cashier with same campus → expect 422
      - Assign faculty with mismatched campus → expect 422
      - Assign null faculty_id → expect 422
  - Exercise GET /cashiers (ensure name reflects faculty) and GET /cashiers/stats
  - Exercise GET /faculty/search?query=...&amp;campus_id=...
- SPA
  - From Cashier Admin page:
    - See Assign control
    - Search by name/email and assign
    - Verify errors displayed upon conflict and successful refresh shows owner name

[Implementation Order]
Sequence starts with schema, then backend, then frontend.

1. Migration: add faculty_id and unique index (campus_id, faculty_id) to tb_mas_cashiers
2. Backend: modify CashierController::index to join tb_mas_faculty and return faculty_id/name
3. Backend: add CashierAssignRequest and CashierController::assign; wire PATCH /cashiers/{id}/assign route with role middleware
4. Backend: modify CashierStoreRequest and CashierController::store to require faculty_id and enforce constraints
5. Backend: add FacultyController::search (if needed) and route
6. Backend: add CashierService::ensureFacultyAssignable and integrate into store/assign/update
7. Scripts: update seed/smoke/test scripts to use faculty_id
8. Frontend: add CashiersService.assign and (if needed) searchFaculty (or use FacultyService)
9. Frontend: update cashiers.controller.js and list.html to add Assign UX with select2-based search
10. Manual/API tests and adjust as needed; keep user_id column deprecated but unused
