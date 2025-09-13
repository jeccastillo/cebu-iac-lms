# Implementation Plan

[Overview]
Introduce a per-date, per-classlist attendance module that lets an authorized user specify meeting dates for a classlist and mark each enrolled student’s is_present state (null by default, true/false when marked) with optional remarks for absences.

This implementation adds backend database tables, service and controller endpoints to manage attendance dates and marks for each classlist, and a frontend UI to create/select dates and quickly mark attendance for all students in the section. Authorization follows the existing grading viewer conventions: the assigned faculty for a classlist may manage attendance for that classlist, and an admin may manage any classlist. The frontend will live under the existing Classlists feature and adds a route and page dedicated to attendance, plus service methods to call the new API.


[Types]  
Add two new Eloquent models and related database tables to represent attendance dates per classlist, and rows per student-date.

Detailed type definitions, interfaces, enums, or data structures with complete specifications. Include field names, types, validation rules, and relationships.
- Database
  - Table: tb_mas_classlist_attendance_date
    - intID: int, PK, auto-increment
    - intClassListID: int, required, FK to tb_mas_classlist.intID (not strictly enforced by FK in initial pass)
    - attendance_date: date, required (YYYY-MM-DD), unique per (intClassListID, attendance_date)
    - created_by: int|null, actor faculty id when known
    - created_at: datetime|null
    - Indexes:
      - ux_classlist_date: unique (intClassListID, attendance_date)
      - ix_classlist: (intClassListID)
  - Table: tb_mas_classlist_attendance
    - intID: int, PK, auto-increment
    - intAttendanceDateID: int, required, FK to tb_mas_classlist_attendance_date.intID
    - intClassListID: int, required, FK to tb_mas_classlist.intID
    - intCSID: int, required, FK to tb_mas_classlist_student.intCSID
    - intStudentID: int, required, FK to tb_mas_users.intID
    - is_present: tinyint(1)|boolean|null (null=unset by default; true=present; false=absent)
    - remarks: varchar(255)|null (only used/filled if absent; optional)
    - marked_by: int|null (faculty id/admin id who last updated the row)
    - marked_at: datetime|null
    - Indexes:
      - ux_date_csid: unique (intAttendanceDateID, intCSID)
      - ix_date: (intAttendanceDateID)
      - ix_classlist: (intClassListID)
- PHP Models
  - App\Models\ClasslistAttendanceDate
    - $table = 'tb_mas_classlist_attendance_date'
    - $primaryKey = 'intID'
    - $timestamps = false
    - $guarded = []
    - Relationships:
      - classlist(): BelongsTo(Classlist::class, 'intClassListID', 'intID')
      - rows(): HasMany(ClasslistAttendance::class, 'intAttendanceDateID', 'intID')
  - App\Models\ClasslistAttendance
    - $table = 'tb_mas_classlist_attendance'
    - $primaryKey = 'intID'
    - $timestamps = false
    - $guarded = []
    - $casts = [ 'is_present' => 'boolean' ]
    - Relationships:
      - date(): BelongsTo(ClasslistAttendanceDate::class, 'intAttendanceDateID', 'intID')
      - classlist(): BelongsTo(Classlist::class, 'intClassListID', 'intID')
      - classlistStudent(): BelongsTo(ClasslistStudent::class, 'intCSID', 'intCSID')
      - student(): BelongsTo(User::class, 'intStudentID', 'intID')
- Validation DTOs (FormRequests)
  - AttendanceDateStoreRequest (POST /classlists/{id}/attendance/dates)
    - date: required|date_format:Y-m-d
  - AttendanceSaveRequest (PUT /classlists/{id}/attendance/dates/{dateId})
    - items: required|array|min:1
    - items.*.intCSID: required|integer|min:1
    - items.*.is_present: nullable|boolean
    - items.*.remarks: nullable|string|max:255
    - Notes:
      - Backend does not strictly require remarks when absent; it is optional but recommended per requirements.


[Files]
Create new backend files for models, migration, service, controller, requests, and routes; add new frontend files for UI and extend existing service with new API methods.

Detailed breakdown:
- New files
  - laravel-api/database/migrations/2025_09_13_000100_create_classlist_attendance_tables.php
    - Creates tb_mas_classlist_attendance_date and tb_mas_classlist_attendance with indexes.
  - laravel-api/app/Models/ClasslistAttendanceDate.php
    - Eloquent model for attendance dates.
  - laravel-api/app/Models/ClasslistAttendance.php
    - Eloquent model for per-student attendance rows.
  - laravel-api/app/Http/Requests/Api/V1/Attendance/AttendanceDateStoreRequest.php
    - Validates POST body for creating a new attendance date for a classlist.
  - laravel-api/app/Http/Requests/Api/V1/Attendance/AttendanceSaveRequest.php
    - Validates PUT body for bulk saving marks.
  - laravel-api/app/Services/ClasslistAttendanceService.php
    - Business logic for date creation, listing, retrieval, and saving marks with consistency checks.
  - laravel-api/app/Http/Controllers/Api/V1/ClasslistAttendanceController.php
    - Exposes REST endpoints to manage attendance dates and marks.
  - frontend/unity-spa/features/classlists/attendance.controller.js
    - AngularJS controller to manage UI: load dates, create date, load students for date, mark attendance, save.
  - frontend/unity-spa/features/classlists/attendance.html
    - AngularJS template: date selector/list, student table with present/absent/unset and remarks input.
- Modified files
  - laravel-api/routes/api.php
    - Add routes:
      - GET /api/v1/classlists/{id}/attendance/dates
      - POST /api/v1/classlists/{id}/attendance/dates
      - GET /api/v1/classlists/{id}/attendance/dates/{dateId}
      - PUT /api/v1/classlists/{id}/attendance/dates/{dateId}
      - DELETE /api/v1/classlists/{id}/attendance/dates/{dateId} (optional)
  - laravel-api/app/Providers/AuthServiceProvider.php
    - Add Gates:
      - attendance.classlist.view
      - attendance.classlist.edit
    - Policy: assigned faculty OR admin; view/edit share same rules.
  - frontend/unity-spa/core/routes.js
    - Add route:
      - .when("/classlists/:id/attendance", { templateUrl: "features/classlists/attendance.html", controller: "ClasslistAttendanceController", controllerAs: "vm", requiredRoles: ["faculty", "admin"] })
  - frontend/unity-spa/features/classlists/classlists.service.js
    - Add methods:
      - getAttendanceDates(classlistId)
      - createAttendanceDate(classlistId, date)
      - getAttendanceByDate(classlistId, dateId)
      - saveAttendance(classlistId, dateId, items)
  - frontend/unity-spa/features/classlists/viewer.html (optional, small UI addition)
    - Add link/button to navigate to Attendance page for the classlist.
- Files to be deleted or moved
  - None.
- Configuration file updates
  - None (leverages existing APP_CONFIG and headers propagation for role context).


[Functions]
Add new public functions in a service and controller for attendance management.

Detailed breakdown:
- New functions (name, signature, file path, purpose)
  - laravel-api/app/Services/ClasslistAttendanceService.php
    - listDates(int $classlistId): array
      - Returns list of dates with per-date summary: present_count, absent_count, unset_count.
    - createDate(int $classlistId, string $date, ?int $actorId = null): array
      - Idempotently creates (or retrieves) an attendance date; seeds tb_mas_classlist_attendance rows for all enrolled students with is_present = null.
    - getDateDetails(int $classlistId, int $dateId): array
      - Returns classlist info, date info, and student rows with attendance fields.
    - saveMarks(int $classlistId, int $dateId, array $items, ?int $actorId = null): array
      - Bulk update is_present/remarks for provided intCSID rows; validates ownership to classlist/date.
  - laravel-api/app/Http/Controllers/Api/V1/ClasslistAttendanceController.php
    - dates(Request $request, int $id): JsonResponse
      - GET /classlists/{id}/attendance/dates
    - createDate(AttendanceDateStoreRequest $request, int $id): JsonResponse
      - POST /classlists/{id}/attendance/dates
    - dateDetails(Request $request, int $id, int $dateId): JsonResponse
      - GET /classlists/{id}/attendance/dates/{dateId}
    - save(AttendanceSaveRequest $request, int $id, int $dateId): JsonResponse
      - PUT /classlists/{id}/attendance/dates/{dateId}
    - deleteDate(Request $request, int $id, int $dateId): JsonResponse (optional)
      - DELETE /classlists/{id}/attendance/dates/{dateId}
  - frontend/unity-spa/features/classlists/classlists.service.js
    - getAttendanceDates(classlistId): Promise<ApiResponse>
    - createAttendanceDate(classlistId, date): Promise<ApiResponse>
    - getAttendanceByDate(classlistId, dateId): Promise<ApiResponse>
    - saveAttendance(classlistId, dateId, items): Promise<ApiResponse>
  - frontend/unity-spa/features/classlists/attendance.controller.js
    - init(): void
    - loadDates(): void
    - createDate(): void
    - selectDate(dateRow): void
    - markAllPresent(): void
    - markAllAbsent(): void
    - togglePresent(row): void
    - save(): void
- Modified functions
  - None of the existing backend services are modified; ClasslistGradesController remains unchanged.
  - frontend/unity-spa/core/routes.js: configuration array extends to include the new route.
- Removed functions
  - None.


[Classes]
Add two new models and one controller and one service at the backend; add a new AngularJS controller at the frontend.

Detailed breakdown:
- New classes
  - App\Models\ClasslistAttendanceDate (laravel-api/app/Models/ClasslistAttendanceDate.php)
    - Key methods: relationships: classlist(), rows()
  - App\Models\ClasslistAttendance (laravel-api/app/Models/ClasslistAttendance.php)
    - Key methods: relationships: date(), classlist(), classlistStudent(), student()
  - App\Services\ClasslistAttendanceService (laravel-api/app/Services/ClasslistAttendanceService.php)
    - Key methods: listDates, createDate, getDateDetails, saveMarks
  - App\Http\Controllers\Api\V1\ClasslistAttendanceController (laravel-api/app/Http/Controllers/Api/V1/ClasslistAttendanceController.php)
    - Key actions: dates, createDate, dateDetails, save, deleteDate
  - AngularJS: ClasslistAttendanceController (frontend/unity-spa/features/classlists/attendance.controller.js)
    - UI logic as described above.
- Modified classes
  - App\Providers\AuthServiceProvider
    - Add Gate definitions:
      - Gate::define('attendance.classlist.view', fn($user, int $classlistId) => policy: assigned faculty or admin)
      - Gate::define('attendance.classlist.edit', fn($user, int $classlistId) => policy: assigned faculty or admin)
- Removed classes
  - None.


[Dependencies]
No new Composer or NPM packages required.

Details of new packages, version changes, and integration requirements.
- None. The implementation relies on:
  - Existing Laravel + DBAL
  - Existing CodeIgniterSessionGuard/Gate patterns for header fallbacks (X-User-Roles, X-Faculty-ID)
  - Existing AngularJS app structure and services


[Testing]
Add feature tests for backend permissions and workflows, and manual QA steps for frontend.

Test file requirements, existing test modifications, and validation strategies.
- Backend (Feature or HTTP tests)
  - tests/Feature/Api/V1/ClasslistAttendanceTest.php (new)
    - test_admin_can_create_and_list_dates()
    - test_assigned_faculty_can_create_and_save_attendance()
    - test_unassigned_faculty_forbidden()
    - test_duplicate_date_creation_is_idempotent()
    - test_save_marks_validates_csid_belongs_to_classlist()
- Manual Frontend QA
  - Navigate to /#!/classlists/:id/attendance as assigned faculty/admin
  - Create a new date (YYYY-MM-DD), verify rows seeded with Unset
  - Mark all present; Save; reload page, verify persisted
  - Mark a few absent, add remarks; Save; reload, verify remarks only for absent rows
  - Attempt access as non-assigned faculty and expect redirect/forbidden messaging
- Edge cases
  - Dissolved classlist: disable attendance actions (return 422) if tb_mas_classlist.isDissolved=1
  - Roster changes after a date was created: out-of-scope for auto-sync; documented limitation in this phase


[Implementation Order]
Implement DB, backend logic, routes, then frontend service and page; wire navigation last.

Numbered steps:
1) Migration: create tb_mas_classlist_attendance_date and tb_mas_classlist_attendance with indexes and constraints.
2) Backend models: ClasslistAttendanceDate and ClasslistAttendance with relationships and type casts.
3) Auth gates: add attendance.classlist.view/edit to AuthServiceProvider, mirroring grading gates logic (assigned faculty OR admin).
4) Service: implement ClasslistAttendanceService (listDates, createDate, getDateDetails, saveMarks) with integrity checks and transactions for seeding.
5) Controller: implement ClasslistAttendanceController endpoints with header fallbacks (X-User-Roles, X-Faculty-ID) consistent with ClasslistGradesController patterns. Enforce policy: assigned faculty or admin.
6) Routes: add attendance routes in laravel-api/routes/api.php under /api/v1/classlists/{id}/attendance/*.
7) Frontend service: extend features/classlists/classlists.service.js with attendance API methods, propagating admin headers (X-User-Roles, X-Faculty-ID).
8) Frontend UI: add attendance.controller.js and attendance.html; implement date creation/selection, table with toggles and remarks for absences, and batch actions (mark all present/absent).
9) Frontend routes: add /classlists/:id/attendance route in core/routes.js; optional button in viewer.html to navigate.
10) QA & Tests: write backend feature tests; manual end-to-end verification from UI.
