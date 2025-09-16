# Implementation Plan

[Overview]
Implement a Student Advisor management feature that lets faculty_admins assign students to a faculty advisor (teaching faculty), maintain historical advisor changes, and support bulk assignment and quick advisor switching, while enforcing department and campus alignment, and exposing APIs and a dedicated UI page.

This feature introduces a normalized history table for advisor assignments while maintaining a denormalized pointer on tb_mas_users for fast lookup. Authorization is enforced via existing middleware and department/campus scoping using tb_mas_faculty_departments. The backend provides endpoints to fetch, assign in bulk, unassign, and quick-switch advisors. The frontend adds a dedicated management page (role-gated to faculty_admin) where an admin selects an advisor and bulk-adds students, and can quickly replace an advisor across all advisees.

[Types]  
Introduce new DB entities and extend existing models to represent student↔advisor relationships with history and validation, plus request/response contracts for the APIs.

1) Database
- New table: tb_mas_student_advisor
  - intID: bigint unsigned auto-increment (PK)
  - intStudentID: bigint unsigned NOT NULL — references tb_mas_users.intID (logical FK)
  - intAdvisorID: bigint unsigned NOT NULL — references tb_mas_faculty.intID (logical FK)
  - is_active: tinyint(1) NOT NULL DEFAULT 1 — exactly one active row per student
  - started_at: datetime NOT NULL — when assignment was activated (default now())
  - ended_at: datetime NULL — when assignment ended (set on unassign/switch)
  - assigned_by: bigint unsigned NOT NULL — faculty who performed assignment (tb_mas_faculty.intID)
  - department_code: varchar(64) NOT NULL — department alignment used at assign-time (lowercased/tracked)
  - campus_id: int NULL — nullable campus alignment used at assign-time
  - created_at: timestamp NULL, updated_at: timestamp NULL
  - Indexes/Constraints:
    - UNIQUE (intStudentID, is_active) — ensures only one active assignment per student
    - INDEX idx_studentid (intStudentID)
    - INDEX idx_advisor_active (intAdvisorID, is_active)
    - Optional check constraints (enforced in app if DB lacks support):
      - is_active in (0,1)
      - ended_at is NULL when is_active=1; ended_at NOT NULL when is_active=0

- Alter table: tb_mas_users
  - Add column: intAdvisorID bigint unsigned NULL (indexed)
  - Purpose: denormalized pointer to the currently active advisor for fast joins and listings.
  - Backfill migration step populates intAdvisorID based on active tb_mas_student_advisor rows where available.

2) Eloquent Models
- New: App\Models\StudentAdvisor
  - $table = 'tb_mas_student_advisor'
  - $primaryKey = 'intID'
  - $casts:
    - intStudentID: integer
    - intAdvisorID: integer
    - assigned_by: integer
    - is_active: integer
    - campus_id: integer
    - started_at: datetime
    - ended_at: datetime
  - Relations:
    - student(): belongsTo(App\Models\User, 'intStudentID', 'intID')
    - advisor(): belongsTo(App\Models\Faculty, 'intAdvisorID', 'intID')
    - assignedBy(): belongsTo(App\Models\Faculty, 'assigned_by', 'intID')

- Existing: App\Models\User (tb_mas_users)
  - Add $fillable[] = 'intAdvisorID'
  - Relations:
    - advisor(): belongsTo(App\Models\Faculty, 'intAdvisorID', 'intID')
    - advisorHistory(): hasMany(App\Models\StudentAdvisor, 'intStudentID', 'intID')

- Existing: App\Models\Faculty (tb_mas_faculty)
  - New relations:
    - advisorAssignments(): hasMany(App\Models\StudentAdvisor, 'intAdvisorID', 'intID')
    - advisees(): hasManyThrough(App\Models\User, App\Models\StudentAdvisor, 'intAdvisorID', 'intID', 'intID', 'intStudentID')

3) Request Types (Laravel Form Requests)
- StudentAdvisorAssignBulkRequest
  - Body:
    - student_ids: int[] (optional)
    - student_numbers: string[] (optional)
    - replace_existing: bool (optional, default false) — whether to end existing active advisor if present
  - Rules:
    - At least one of student_ids or student_numbers must be present.
    - Each ID/number must resolve to an existing student.
- StudentAdvisorSwitchRequest
  - Body:
    - from_advisor_id: int required
    - to_advisor_id: int required, must be different from from_advisor_id
  - Rules:
    - Both advisors must exist and be teaching=1.
- StudentAdvisorShowRequest
  - Query:
    - student_id: int optional
    - student_number: string optional
  - Rules:
    - One of student_id or student_number is required.

[Files]
Introduce new backend and frontend files, and modify existing files to wire the feature end-to-end.

Backend (Laravel API)
- New files:
  - laravel-api/database/migrations/2025_09_16_000001_create_tb_mas_student_advisor.php
    - Creates tb_mas_student_advisor with schema above and indexes.
  - laravel-api/database/migrations/2025_09_16_000002_alter_tb_mas_users_add_intAdvisorID.php
    - Adds intAdvisorID column, index, and backfill.
  - laravel-api/app/Models/StudentAdvisor.php
  - laravel-api/app/Services/StudentAdvisorService.php
    - Core business logic (bulk assign, switch, show, unassign, validations).
  - laravel-api/app/Http/Controllers/Api/V1/StudentAdvisorController.php
    - API endpoints handler; injects service; handles requests/responses.
  - laravel-api/app/Http/Requests/Api/V1/StudentAdvisorAssignBulkRequest.php
  - laravel-api/app/Http/Requests/Api/V1/StudentAdvisorSwitchRequest.php
  - laravel-api/app/Http/Requests/Api/V1/StudentAdvisorShowRequest.php

- Modified files:
  - laravel-api/routes/api.php
    - Add routes:
      - GET   /api/v1/student-advisors                 → StudentAdvisorController@index      (role:faculty_admin,admin)
      - POST  /api/v1/advisors/{advisorId}/assign-bulk → StudentAdvisorController@assignBulk (role:faculty_admin,admin)
      - POST  /api/v1/advisors/switch                  → StudentAdvisorController@switch     (role:faculty_admin,admin)
      - DELETE /api/v1/student-advisors/{studentId}    → StudentAdvisorController@destroy    (role:faculty_admin,admin)
  - laravel-api/app/Models/User.php
    - $fillable includes intAdvisorID; add advisor() and advisorHistory() relations.
  - laravel-api/app/Models/Faculty.php
    - Add advisorAssignments() and advisees() relations.

Frontend (AngularJS unity-spa)
- New feature module: Advisors Management (faculty_admin-only)
  - frontend/unity-spa/features/advisors/advisors.service.js
    - Wrapper for new API endpoints; uses existing auth/header patterns; select2 data sources for faculty/students.
  - frontend/unity-spa/features/advisors/advisors.controller.js
    - Page controller for:
      - Select Advisor (teaching=1, filtered by department and campus)
      - Bulk add students (by search, multi-select, or CSV paste) to selected advisor
      - Quick switch all advisees from Advisor A to Advisor B
      - Show summary of actions and conflicts
  - frontend/unity-spa/features/advisors/advisors.html
    - UI layout with:
      - Advisor picker (select2)
      - Bulk student picker (select2; support by ID or Student Number)
      - Replace Existing toggle
      - Actions: Assign Bulk, Quick Switch
      - Results/History display for selected students/advisor
  - Optional route/registration (if needed by app shell):
    - frontend/unity-spa/features/advisors/advisors.routes.js (if project patterns isolate routes per feature)
      - Registers state: app.advisors (url: /advisors), role-gated via existing FE role checks
- Modified (if navigation/menu is centralized):
  - Add menu entry "Advisors" visible to role faculty_admin.

[Functions]
Add new controller actions and service methods; modify models only to add relations; no removals.

New functions (Backend)
- App\Services\StudentAdvisorService
  - assignBulk(int $advisorId, array $studentIds = [], array $studentNumbers = [], bool $replaceExisting, int $actorFacultyId): array
    - Validates advisor exists, teaching=1
    - Resolves student IDs from inputs
    - Enforces department_code and campus_id overlap: actor ↔ advisor (via tb_mas_faculty_departments)
    - For each student:
      - If active advisor exists:
        - If same advisor: skip (idempotent)
        - If replaceExisting: end active with ended_at, create new active assignment
        - Else: record conflict
      - If none: create new active assignment
      - Update tb_mas_users.intAdvisorID pointer accordingly
    - Returns summary: assigned_count, skipped, conflicts, errors
  - switchAll(int $fromAdvisorId, int $toAdvisorId, int $actorFacultyId): array
    - Validates both advisors (teaching=1)
    - Enforces actor dept/campus overlap with both advisors
    - For each active advisee of fromAdvisorId:
      - If already assigned to toAdvisorId: skip
      - End current, create new active assignment to toAdvisorId
      - Update pointer
    - Returns counts: switched, skipped, errors
  - showByStudent(?int $studentId, ?string $studentNumber): array
    - Returns current active advisor and full assignment history for the student
  - unassign(int $studentId, int $actorFacultyId): array
    - If active assignment exists: end it (ended_at), set is_active=0, nullify tb_mas_users.intAdvisorID
    - Idempotent if none active
  - Helpers (private):
    - resolveStudentIds(array $ids, array $numbers): int[]
    - checkDeptCampusOverlap(int $actorFacultyId, int $targetFacultyId): array{ok:bool, dept:string, campus_id:?int}
      - At least one common department_code, campus-aware when campus tags exist
    - endActiveAssignment(int $studentId): void
    - createAssignment(int $studentId, int $advisorId, string $dept, ?int $campusId, int $actorId): StudentAdvisor
    - facultyDeptTags(int $facultyId): array{departments: string[], campus_ids: int[]|null}

New functions (Controllers)
- App\Http\Controllers\Api\V1\StudentAdvisorController
  - index(StudentAdvisorShowRequest $request, StudentAdvisorService $service)
    - Query params: student_id or student_number
    - Returns { active, history[] }
  - assignBulk(int $advisorId, StudentAdvisorAssignBulkRequest $request, StudentAdvisorService $service)
    - Returns summary
  - switch(StudentAdvisorSwitchRequest $request, StudentAdvisorService $service)
    - Returns summary
  - destroy(int $studentId, Request $request, StudentAdvisorService $service)
    - Returns result of unassign

Modified functions
- None removed; existing models extended only with relations.

Removed functions
- None.

[Classes]
New classes
- App\Models\StudentAdvisor — history model
- App\Services\StudentAdvisorService — domain logic
- App\Http\Controllers\Api\V1\StudentAdvisorController — endpoint layer
- App\Http\Requests\Api\V1\StudentAdvisorAssignBulkRequest
- App\Http\Requests\Api\V1\StudentAdvisorSwitchRequest
- App\Http\Requests\Api\V1\StudentAdvisorShowRequest

Modified classes
- App\Models\User — add fillable intAdvisorID and relations advisor(), advisorHistory()
- App\Models\Faculty — add advisorAssignments(), advisees()

Removed classes
- None.

[Dependencies]
No new external composer or npm dependencies required; rely on existing Laravel components, DB facade, Eloquent, and current middleware RequireRole. Frontend uses existing AngularJS stack and shared select2 directive for pickers.

[Testing]
Adopt critical-path API testing with authorized context and department/campus enforcement.

Backend tests/manual validation
- Migrations
  - php artisan migrate → creates tables and columns; backfill intAdvisorID
- API happy path (actor faculty_admin with matching dept/campus to advisor)
  - POST /api/v1/advisors/{1188}/assign-bulk
    - Body: { student_ids: [158], replace_existing: false }
    - Headers: X-Faculty-ID: 13
    - Expect: assigned_count=1, tb_mas_users.intAdvisorID of 158 becomes 1188, tb_mas_student_advisor row active
  - GET /api/v1/student-advisors?student_id=158
    - Expect: active advisor 1188, history includes created row
  - POST /api/v1/advisors/switch
    - Body: { from_advisor_id: 1188, to_advisor_id: 1199 }
    - Headers: X-Faculty-ID: 13
    - Expect: ended previous active, created new active to 1199, pointer updated
  - DELETE /api/v1/student-advisors/158
    - Expect: active ended, pointer cleared; idempotent on subsequent calls
- Authorization/Validation
  - Missing/invalid X-Faculty-ID → 401
  - Role without faculty_admin/admin → 403
  - Advisor not teaching=1 → 422
  - No dept/campus overlap → 422 or 403 with clear message
  - Duplicate assign to same advisor → idempotent skip

Frontend validation
- Role-gated page visible only to faculty_admin
- Advisor select shows only teaching=1 faculty filtered by the acting admin&#39;s departments/campus
- Bulk student select supports search by student number/name; handles server errors; shows summary of assigned/skipped/conflicts
- Quick switch flow prevents from=to and shows results

[Implementation Order]
Implement in safe, incremental steps minimizing risk and integration conflicts.

1) Database
   - Create migration: 2025_09_16_000001_create_tb_mas_student_advisor.php
   - Create migration: 2025_09_16_000002_alter_tb_mas_users_add_intAdvisorID.php (with backfill)
   - Run migrations

2) Backend Models/Service
   - Add App\Models\StudentAdvisor
   - Extend App\Models\User with fillable and relations
   - Extend App\Models\Faculty with relations
   - Implement App\Services\StudentAdvisorService (core logic + constraints)

3) Requests/Controller/Routes
   - Add Form Requests (AssignBulk, Switch, Show)
   - Implement StudentAdvisorController actions
   - Register routes in laravel-api/routes/api.php guarded by middleware('role:faculty_admin,admin')

4) Frontend
   - Create advisors.service.js (API integration)
   - Create advisors.controller.js and advisors.html (UI workflows)
   - Register route/state and add menu entry for faculty_admin

5) QA & Verification
   - Manual API tests using provided IDs (actor=13, advisor=1188, student=158)
   - Frontend smoke test: bulk assign and quick switch
   - Edge cases: idempotency, validation errors, dept/campus mismatch

6) Documentation & Rollout
   - README snippet for new endpoints and headers usage
   - Add seeding examples if needed for test dept/campus tags
