# Implementation Plan

[Overview]
Add a student-facing “Request Program Change” feature that lets a logged-in student submit a single program-change request for the active term, persists it to a new table tb_mas_shift_requests, and automatically creates a System Alert targeted to Registrar users linking to the Registrar Shifting page for processing.

This feature introduces a lightweight workflow so students can explicitly request a change of their current program. It enforces at most one request per student per term and notifies the Registrar via a system-generated alert. The backend exposes a minimal student-safe API to create and view requests while the frontend adds a new Student page to submit requests. The Registrar continues to act on such requests through their existing Shifting tools.

[Types]  
The type system changes introduce a new Eloquent model ShiftRequest with a backing table tb_mas_shift_requests, plus clearly defined request/response shapes for the API endpoints.

New database table: tb_mas_shift_requests
- id: bigint, PK, autoincrement
- student_id: int, required; FK to tb_mas_users.intID (logical)
- student_number: varchar(64), nullable; copy from tb_mas_users.strStudentNumber at creation time for convenience
- term_id: int, required; active AY/Term id
- program_from: int, required; intProgramID from tb_mas_users at request time (snapshot)
- program_to: int, required; intProgramID requested
- reason: text, nullable; optional student-provided reason
- status: enum|string, default 'pending'; expected values: 'pending' | 'approved' | 'denied' | 'canceled'
- requested_at: datetime, default now(); mirrors created_at
- processed_at: datetime, nullable; set when approved/denied
- processed_by_faculty_id: int, nullable; registrar/faculty who processed
- campus_id: int, nullable; copy from tb_mas_users.campus_id if available
- meta: json, nullable; for extensibility (UA, client info, etc.)
- timestamps: created_at, updated_at
- Constraints/Indexes:
  - unique index on (student_id, term_id) to enforce one request per student per term
  - indexes on student_id, term_id, program_to for typical queries

Model: App\Models\ShiftRequest
- $fillable: [student_id, student_number, term_id, program_from, program_to, reason, status, requested_at, processed_at, processed_by_faculty_id, campus_id, meta]
- $casts:
  - requested_at, processed_at: 'datetime'
  - program_from, program_to, term_id, student_id, processed_by_faculty_id, campus_id: 'integer'
  - meta: 'array'

API Contracts (student-safe)
- POST /api/v1/student/shift-requests
  - Request (JSON):
    - student_id?: number (optional; if omitted, resolve from token or session headers)
    - token?: string (optional; e.g., portal token for identification)
    - term: number (required)
    - program_to: number (required; existing tb_mas_programs.intProgramID)
    - reason?: string (optional)
  - Behavior:
    - Resolve student_id by token, headers, or body.
    - Validate existence of student and program_to.
    - Populate program_from from student’s current program at request time.
    - Enforce uniqueness (student_id, term_id) at app and DB levels.
    - Create system-generated SystemAlert targeted to role_codes ['registrar'] with link "#/registrar/shifting".
  - Responses:
    - 201 { success: true, data: ShiftRequest } on success
    - 409 { success: false, message: 'Duplicate request exists for this term.' } if unique constraint violated
    - 422 { success: false, errors: { field: [msg] } } on validation errors

- GET /api/v1/student/shift-requests
  - Query:
    - term?: number (optional, filter by term)
  - Behavior: Lists the caller’s own shift requests (ordered desc by requested_at)
  - Response: 200 { success: true, data: ShiftRequest[] }

System Alert payload (SystemAlert model, tb_sys_alerts)
- title: 'Program Change Request'
- message: "Program change request from {student_number}: {program_from} → {program_to}"
- link: '#/registrar/shifting'
- type: 'info' (or 'request' if acceptable)
- role_codes: ['registrar']
- campus_ids: [campus_id] when available; else []
- target_all: false
- intActive: 1
- system_generated: true
- created_by: 'system'

[Files]
The change introduces new backend and frontend files and modifies select routing and navigation files.

New files to be created
- laravel-api/database/migrations/2025_09_15_000250_create_tb_mas_shift_requests_table.php
  - Defines table tb_mas_shift_requests with columns and constraints above.
- laravel-api/app/Models/ShiftRequest.php
  - Eloquent model for tb_mas_shift_requests.
- laravel-api/app/Http/Controllers/Api/V1/ShiftRequestController.php
  - Controller implementing index() and store() and helper resolveStudentId().

- frontend/unity-spa/features/student/change-program-request/change-program-request.controller.js
  - AngularJS controller StudentChangeProgramRequestController with init(), reloadPrograms(), canSubmit(), onSubmit().
- frontend/unity-spa/features/student/change-program-request/change-program-request.html
  - Template providing program dropdown, optional reason input, state handling, and submit button.

Existing files to be modified
- laravel-api/routes/api.php
  - Register:
    - GET /api/v1/student/shift-requests → ShiftRequestController@index (role: student_view, admin)
    - POST /api/v1/student/shift-requests → ShiftRequestController@store (role: student_view, admin)
- frontend/unity-spa/core/routes.js
  - Add route when('/student/change-program-request', ...) with requiredRoles: ['student_view', 'admin'].
- frontend/unity-spa/shared/components/sidebar/sidebar.controller.js
  - Under Student group: add menu entry { label: 'Request Program Change', path: '/student/change-program-request' }.
- frontend/unity-spa/index.html
  - Add script tag for features/student/change-program-request/change-program-request.controller.js.

Files to be deleted or moved
- None.

Configuration file updates
- None (no new packages or env keys required).

[Functions]
New and modified functions provide the new API and UI workflow.

Backend: App\Http\Controllers\Api\V1\ShiftRequestController
- public function index(Request $request): JsonResponse
  - Purpose: Return authenticated student’s program change requests with optional term filter.
  - Details:
    - Resolve student id via resolveStudentId($request).
    - Query ShiftRequest::where('student_id', $sid)->when(term, fn)->orderByDesc('requested_at')->get().
    - Return { success: true, data: [...] }.

- public function store(Request $request): JsonResponse
  - Purpose: Create a new pending shift request.
  - Validation:
    - term: required|integer
    - program_to: required|integer|exists:tb_mas_programs,intProgramID
    - reason: nullable|string|max:2000
  - Flow:
    - $sid = resolveStudentId($request); fetch student record from tb_mas_users (id, strStudentNumber, intProgramID, campus_id).
    - program_from = student.intProgramID
    - enforce app-level uniqueness check; attempt insert; catch DB unique error return 409.
    - Create ShiftRequest row (status='pending', requested_at=now()).
    - Create SystemAlert row via SystemAlert model and broadcast via SystemAlertService::broadcast('create', ...).
    - Return 201 { success: true, data: created }.

- protected function resolveStudentId(Request $request): int
  - Purpose: Determine student_id from token, explicit student_id, or other hints.
  - Approach:
    - If student_id provided and exists → use it.
    - Else if token provided → DataFetcherService->getStudentByToken($token).
    - Else attempt headers (X-User-ID where role matches student_view) as fallback.
    - Throw validation exception if not resolvable.

Frontend: StudentChangeProgramRequestController (features/student/change-program-request/change-program-request.controller.js)
- init()
  - Purpose: Initialize active term and student profile then load programs.
  - Flow:
    - TermService.init() or TermService.getActiveTerm() fallback; if unavailable GET /generic/active-term fallback; handle gracefully.
    - StudentFinancesService.resolveProfile() to obtain student id and student number.
    - Call reloadPrograms().

- reloadPrograms()
  - Purpose: Load available programs and normalize to { id, code, name } for the dropdown.
  - Flow:
    - ProgramsService.list({ enabledOnly: true }) and map rows.

- canSubmit()
  - Purpose: Enable the submit button only when profile, term, and selected program are available and not already submitting.

- onSubmit()
  - Purpose: Submit POST /api/v1/student/shift-requests with { student_id, term, program_to, reason }.
  - Handles:
    - 201: toast success + local state updated + disable resubmission for this term.
    - 409: toast warning “Request already exists for this term.”
    - 422: toast error with validation messages.
    - Errors: general toast error.

[Classes]
New classes cover the data model and controller.

New classes
- App\Models\ShiftRequest (laravel-api/app/Models/ShiftRequest.php)
  - Extends Illuminate\Database\Eloquent\Model
  - Table: tb_mas_shift_requests
  - Casts and fillable as defined under [Types].
- App\Http\Controllers\Api\V1\ShiftRequestController (laravel-api/app/Http/Controllers/Api/V1/ShiftRequestController.php)
  - Depends on DataFetcherService, SystemAlertService, DB, SystemAlert, ShiftRequest

Modified classes
- None beyond route usage; no changes required to existing controllers.

Removed classes
- None.

[Dependencies]
No new composer/npm packages are required.

Integration requirements
- Table tb_mas_programs available and contains intProgramID for validation.
- Table tb_mas_users provides student lookup (intID, strStudentNumber, intProgramID, campus_id).
- SystemAlertService::broadcast must be callable and tolerant of failures (already designed to degrade gracefully).

[Testing]
Testing approach covers both API and UI with focus on the critical path.

Backend/API
- Migration: run php artisan migrate to create tb_mas_shift_requests with unique(student_id, term_id).
- POST /api/v1/student/shift-requests
  - Valid: returns 201 and row has status 'pending', program_from matches student, SystemAlert row created.
  - Duplicate: second request same student+term returns 409.
  - Invalid program_to or missing term: returns 422 with errors.
- GET /api/v1/student/shift-requests
  - Returns caller’s list; respects term filter.

System Alerts
- After create, tb_sys_alerts has a new row with role_codes ['registrar'], link '#/registrar/shifting', intActive=1, system_generated=1.
- Optional: Verify event broadcast (no hard requirement if broadcasting disabled in environment).

Frontend
- Route access: '/student/change-program-request' requires role student_view or admin.
- Initialization: Page resolves active term, profile, and programs without throwing; shows user-friendly errors if fallbacks kick in.
- Submission:
  - Success path: Toast “Request submitted”, submit disabled for same term afterward.
  - Duplicate path: Toast “A request already exists for this term.”
  - Validation errors: Display reasons without breaking the page.

Regression
- Registrar sidebar and pages unaffected.
- Roles and guards remain consistent.

[Implementation Order]
Sequence minimizes integration risk and enables early API verification.

1) Database
   - Create migration 2025_09_15_000250_create_tb_mas_shift_requests_table.php
   - Columns, indexes, and unique(student_id, term_id)

2) Backend Model
   - Add App\Models\ShiftRequest with casts/fillable and table mapping

3) Backend Controller
   - Add App\Http\Controllers\Api\V1\ShiftRequestController with:
     - index(Request)
     - store(Request)
     - resolveStudentId(Request)

4) Routes
   - Update laravel-api/routes/api.php:
     - GET /api/v1/student/shift-requests → index (role: student_view, admin)
     - POST /api/v1/student/shift-requests → store (role: student_view, admin)

5) System Alerts Integration
   - In store(): create SystemAlert row and call SystemAlertService->broadcast('create', $alert)

6) Frontend UI
   - Add features/student/change-program-request/change-program-request.controller.js
   - Add features/student/change-program-request/change-program-request.html
   - Update core/routes.js to register “/student/change-program-request”
   - Update shared/components/sidebar/sidebar.controller.js to add Student menu item
   - Update index.html to include controller script

7) End-to-end Tests
   - Migrate DB; verify POST/GET endpoints (201/409/422)
   - Load UI page; confirm initialization and submission flows (success and duplicate)
   - Verify registrar alert row created and visible to registrar role

8) Hardening and Polish
   - Surface clear toasts on all error cases
   - Ensure init fallbacks don’t block form rendering:
     - If active term or profile fetch fails, show appropriate message and keep retry affordance
   - Confirm no console errors and acceptable UX
