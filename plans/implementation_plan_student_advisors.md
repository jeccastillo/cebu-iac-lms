# Implementation Plan

[Overview]
Introduce a faculty advisor assignment capability with strict department/campus authorization and a complete assignment history, enabling faculty_admin (and admin) users to bulk-assign students to teaching faculty, view/maintain current advisors, and quickly switch all advisees from one advisor to another.

This feature enforces that only faculty_admins sharing both department and campus scope with the target advisor can perform assignments. It maintains a single active advisor per student and records historical changes. A denormalized pointer (tb_mas_users.intAdvisorID) accelerates reads for current advisor. The UI adds a dedicated “Advisors Management” page to select an advisor and bulk-add students (by ID or student number), and a “Quick Switch” operation to reassign all advisees from one advisor to another.

[Types]  
Add relational persistence for advisor history, plus denormalized link on students.

- Database Schema
  - Table: tb_mas_student_advisor
    - intID: int, auto-increment, PK
    - intStudentID: int, required, indexed (references tb_mas_users.intID; no FK to avoid cross-env failures)
    - intAdvisorID: int, required, indexed (references tb_mas_faculty.intID; no FK)
    - is_active: tinyint(1), default 1
    - started_at: datetime, default current timestamp
    - ended_at: datetime, nullable
    - assigned_by: int, nullable, index (faculty intID performing action)
    - department_code: varchar(64), nullable, index (canonical lowercase)
    - campus_id: int, nullable, index
    - created_at: timestamp, nullable
    - updated_at: timestamp, nullable
    - Unique index: uniq_active_advisor_per_student on (intStudentID, is_active) to ensure only one active row per student.
    - Supporting index: idx_advisor_active on (intAdvisorID, is_active).
  - Column: tb_mas_users.intAdvisorID
    - intAdvisorID: int, nullable, index (fast pointer to current advisor for read-heavy endpoints)
- Eloquent Models and Relations
  - App\Models\StudentAdvisor
    - $table = 'tb_mas_student_advisor'
    - $guarded = []
    - $casts:
      - intStudentID: 'integer'
      - intAdvisorID: 'integer'
      - assigned_by: 'integer'
      - campus_id: 'integer'
      - is_active: 'integer'
      - started_at: 'datetime'
      - ended_at: 'datetime'
    - Relations:
      - student(): belongsTo(User::class, 'intStudentID', 'intID')
      - advisor(): belongsTo(Faculty::class, 'intAdvisorID', 'intID')
      - assignedBy(): belongsTo(Faculty::class, 'assigned_by', 'intID')
  - Update App\Models\User
    - advisor(): belongsTo(Faculty::class, 'intAdvisorID', 'intID')
    - advisorHistory(): hasMany(StudentAdvisor::class, 'intStudentID', 'intID')
  - Update App\Models\Faculty
    - advisees(): hasMany(User::class, 'intAdvisorID', 'intID')
    - advisorAssignments(): hasMany(StudentAdvisor::class, 'intAdvisorID', 'intID')

Validation and Authorization Rules
- Advisor must be teaching = 1.
- Only roles faculty_admin or admin can call advisor assignment APIs (route middleware).
- Department & campus checks (strict):
  - Actor (faculty_admin) must share at least one department_code with the target advisor (tb_mas_faculty_departments), case-insensitive canonical comparison.
  - Campus scoping: when both sides have campus_id values for the same department tag, they must match; a null campus_id is treated as global and can match any campus for that department.
- Uniqueness: Only one active advisor per student enforced by unique index and service-level checks.
- History: Reassignments preserve historical rows by ending previous active rows (set is_active=0 and ended_at) and creating a new active row; tb_mas_users.intAdvisorID is updated accordingly.

[Files]
Create new backend migrations, model, service, requests, and controller; modify routes and existing models. Add a new AngularJS feature for advisors management.

- New backend files
  - laravel-api/database/migrations/2025_09_16_000001_create_tb_mas_student_advisor.php
    - Creates tb_mas_student_advisor with the schema above and named indexes:
      - uniq_active_advisor_per_student (intStudentID, is_active)
      - idx_studentid (intStudentID)
      - idx_advisor_active (intAdvisorID, is_active)
      - idx_department_code (department_code)
      - idx_campus_id (campus_id)
  - laravel-api/database/migrations/2025_09_16_000002_alter_tb_mas_users_add_intAdvisorID.php
    - Adds nullable intAdvisorID to tb_mas_users with index.
    - Optional safe backfill (no-op on empty): set intAdvisorID for rows with a single matching active tb_mas_student_advisor.
  - laravel-api/app/Models/StudentAdvisor.php
    - Eloquent model as specified.
  - laravel-api/app/Services/StudentAdvisorService.php
    - Business logic for validation, authorization, bulk assignment, switching, and unassignment.
  - laravel-api/app/Http/Requests/Api/V1/StudentAdvisorAssignBulkRequest.php
    - Body: { student_ids?: int[], student_numbers?: string[], replace_existing?: bool }
  - laravel-api/app/Http/Requests/Api/V1/StudentAdvisorSwitchRequest.php
    - Body: { from_advisor_id: int, to_advisor_id: int }
  - laravel-api/app/Http/Requests/Api/V1/StudentAdvisorShowRequest.php
    - Query: ?student_id or ?student_number
  - laravel-api/app/Http/Controllers/Api/V1/StudentAdvisorController.php
    - Endpoints for show, assign-bulk, switch, and delete (unassign).
- Modified backend files
  - laravel-api/routes/api.php
    - Add routes under /api/v1 guarded by middleware('role:faculty_admin,admin'):
      - GET    /student-advisors                                  → StudentAdvisorController@index
      - POST   /advisors/{advisorId}/assign-bulk                  → StudentAdvisorController@assignBulk
      - POST   /advisors/switch                                   → StudentAdvisorController@switch
      - DELETE /student-advisors/{studentId}                      → StudentAdvisorController@destroy
  - laravel-api/app/Models/User.php
    - Add advisor() and advisorHistory() relations; ensure $fillable includes 'intAdvisorID' if service uses mass assignment.
  - laravel-api/app/Models/Faculty.php
    - Add advisees() and advisorAssignments() relations.
- New frontend files (AngularJS)
  - frontend/unity-spa/features/advisors/manage/manage.html
    - UI for selecting the advisor, student entry (IDs or student numbers), previewing resolves, submitting bulk assignment, and displaying per-student results (success/errors).
    - “Quick Switch” section for from_advisor and to_advisor with confirmation modal.
  - frontend/unity-spa/features/advisors/manage/manage.controller.js
    - Controller: load advisor options (via GenericApiController@faculty with teaching=1 and optional campus filter), resolve students (via a lightweight API or existing services), call assign-bulk/switch endpoints, and render results.
  - frontend/unity-spa/features/advisors/manage/advisors.service.js
    - Angular service wrappers for:
      - GET    /api/v1/student-advisors
      - POST   /api/v1/advisors/{advisorId}/assign-bulk
      - POST   /api/v1/advisors/switch
      - DELETE /api/v1/student-advisors/{studentId}
- Modified frontend files
  - Add SPA route and menu item for “Advisors Management” under a faculty_admin-only section (and admin). File to update depends on existing router/menu registry (follow patterns used by faculty-loading pages).

[Functions]
Add new controller/service functions and adjust models accordingly.

- New functions
  - App\Services\StudentAdvisorService
    - assignBulk(int $advisorId, array $studentIds, bool $replaceExisting, int $actorId): array
      - Validates advisor (exists, teaching=1).
      - Authorization: actor must share department/campus with advisor.
      - For each studentId:
        - If active exists:
          - If replaceExisting=false → error for that student.
          - If replaceExisting=true → end active (is_active=0, ended_at=now), create new active row; update tb_mas_users.intAdvisorID.
        - If not exists → create new active row; update intAdvisorID.
      - Returns per-student results: [{ student_id, ok, message }]
      - Uses DB::transaction for batch chunks to ensure consistency with intAdvisorID updates.
    - switchAll(int $fromAdvisorId, int $toAdvisorId, int $actorId): array
      - Validate from != to; validate toAdvisor teaching=1.
      - Authorization: actor must share department/campus with toAdvisor (optionally enforce overlap with fromAdvisor as configuration; initial version: only toAdvisor).
      - Fetch all active rows for fromAdvisor; for each:
        - endActiveAssignment(studentId...), then create new active assignment to toAdvisor.
        - Update tb_mas_users.intAdvisorID.
      - Returns summary: { from_advisor_id, to_advisor_id, total_processed, switched, skipped, errors: [...] }.
    - showByStudent(?int $studentId, ?string $studentNumber): array
      - Resolve student, return { current, history, student } where:
        - current: null or { advisor_id, started_at, department_code, campus_id }
        - history: array of rows with ended entries.
    - unassign(int $studentId, int $actorId): array
      - If active exists → end it and set tb_mas_users.intAdvisorID = null. Returns { ok:true } else { ok:false, message }.
    - helpers (private):
      - resolveStudentIds(array $ids, array $studentNumbers): array<int>
      - checkDeptCampusOverlap(int $actorId, int $advisorId): array{ ok:bool, message?:string, department_code?:string, campus_id?:int }
      - endActiveAssignment(int $studentId, int $endedBy, \DateTimeInterface $when): void
      - createAssignment(int $studentId, int $advisorId, int $assignedBy, ?string $dept, ?int $campusId): void
  - App\Http\Controllers\Api\V1\StudentAdvisorController
    - index(StudentAdvisorShowRequest $request): JsonResponse
      - Inputs: student_id or student_number
      - Returns: { success:true, data:{ student, current, history } }
    - assignBulk(int $advisorId, StudentAdvisorAssignBulkRequest $request): JsonResponse
      - Body: { student_ids?:int[], student_numbers?:string[], replace_existing?:bool }
      - Returns: { success:true, data:{ results:[...] } }
    - switch(StudentAdvisorSwitchRequest $request): JsonResponse
      - Body: { from_advisor_id:int, to_advisor_id:int }
      - Returns: { success:true, data:{ summary } }
    - destroy(int $studentId, Request $request): JsonResponse
      - Ends current advisor and clears intAdvisorID; returns status JSON.
- Modified functions
  - App\Models\User
    - Add:
      - public function advisor(): BelongsTo
      - public function advisorHistory(): HasMany
  - App\Models\Faculty
    - Add:
      - public function advisees(): HasMany
      - public function advisorAssignments(): HasMany
- Removed functions
  - None.

[Classes]
Add a new model, new service, new controller, and new request classes; update existing models.

- New classes
  - App\Models\StudentAdvisor
    - Key methods: student(), advisor(), assignedBy()
  - App\Services\StudentAdvisorService
    - Key methods: assignBulk(), switchAll(), showByStudent(), unassign(), and validation helpers.
  - App\Http\Controllers\Api\V1\StudentAdvisorController
    - Methods: index(), assignBulk(), switch(), destroy()
  - Requests:
    - App\Http\Requests\Api\V1\StudentAdvisorAssignBulkRequest
    - App\Http\Requests\Api\V1\StudentAdvisorSwitchRequest
    - App\Http\Requests\Api\V1\StudentAdvisorShowRequest
- Modified classes
  - App\Models\User: add advisor/advisorHistory relations (+ $fillable intAdvisorID if mass-assigned).
  - App\Models\Faculty: add advisees/advisorAssignments relations.
- Removed classes
  - None.

[Dependencies]
No new composer or npm dependencies required; reuse existing middleware (RequireRole) and GenericApiController for selecting teaching faculty. Migrations and Eloquent suffice.

[Testing]
Add feature tests for request validation, authorization, and business logic; include end-to-end flows.

- Backend HTTP Tests (PHPUnit)
  - StudentAdvisorController@index:
    - Resolve by student_id and student_number; returns current null when none; returns full ordered history when present.
  - assignBulk:
    - Reject when advisor not teaching.
    - Reject when actor missing role faculty_admin/admin.
    - Reject when actor lacks dept/campus overlap with advisor.
    - Success: create new active for students without advisor; history preserved for those with replace_existing=true.
    - Unique constraint: ensure only one active row persisted (force concurrency by double-submit simulation).
    - Updates tb_mas_users.intAdvisorID accordingly.
  - switch:
    - Reject from == to.
    - Reject toAdvisor not teaching.
    - Reject when actor lacks dept/campus overlap with toAdvisor.
    - Success: ends all active for fromAdvisor, creates new active to toAdvisor, updates intAdvisorID, returns counts.
  - destroy:
    - Success ends active and clears intAdvisorID; idempotent when none active.
- Frontend Integration (manual/QA and lightweight unit where applicable)
  - Advisors dropdown loads teaching=1 faculty; respects campus filter.
  - Bulk assign resolves input list (IDs or student numbers), shows dry-run preview (optional), displays per-row results.
  - Switch operation confirms and displays summary.
  - Visibility restricted to faculty_admin and admin roles.
- Data Integrity
  - Migrations run clean; unique index enforces invariant.
  - Backfill migration no-ops safely across environments.

[Implementation Order]
Sequence to minimize risk and isolate changes.

1) DB migrations
   - Create tb_mas_student_advisor table with indexes.
   - Add tb_mas_users.intAdvisorID column + index.
2) Backend scaffolding
   - Add StudentAdvisor model, requests, and service with core methods and validations.
   - Update User and Faculty models with new relations.
3) API surface
   - Implement StudentAdvisorController endpoints.
   - Wire routes with middleware('role:faculty_admin,admin').
4) Frontend scaffolding
   - Add advisors.service.js wrappers.
   - Add manage.controller.js and manage.html; register route/menu for faculty_admin/admin.
5) Authorization and filters
   - Reuse GenericApiController faculty endpoint for teaching=1 options; integrate campus filter where needed.
6) Tests
   - Implement backend HTTP tests for main flows and edge cases; adjust if issues arise.
7) Manual QA
   - Verify bulk assign (with/without replace), verify switch-all, verify unassign; verify department/campus enforcement; verify intAdvisorID updates and history.
8) Rollout
   - Deploy migrations and API; validate with test data; enable UI entry points for faculty_admins.
