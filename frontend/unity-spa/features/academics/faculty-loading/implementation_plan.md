# Implementation Plan

[Overview]
Build a Faculty Loading feature that allows registrar, faculty_admin, and admin users to assign subjects (classlists) to faculty by updating tb_mas_classlist.intFacultyID, with both inline single-save and bulk-save capabilities, enforcing teaching=1 and campus match restrictions.

This feature introduces a dedicated page under Academics for viewing classlists by term, filtering/searching, and assigning faculty. The backend will expose a bulk assignment endpoint in addition to the existing single-row update, expanding permissions to include faculty_admin. The UI will support inline select per row, tracking pending changes for a Save All action. Campus restrictions will be enforced server-side and reflected client-side (faculty dropdown filtered by classlist campus when available). Teaching filter (teaching=1) will be enforced on both UI options and backend validation. This complements existing Classlist CRUD APIs and uses the existing faculty listing endpoint with an added campus filter.

[Types]  
Request/response types will be extended to support bulk assignment and refined faculty search.

- Backend (PHP/Laravel)
  - New Request: App\Http\Requests\Api\V1\ClasslistAssignFacultyBulkRequest
    - Fields:
      - term: integer (required) — tb_mas_sy.intID to scope classlists
      - assignments: array (required, 1..500)
      - assignments[].classlist_id: integer, exists:tb_mas_classlist,intID
      - assignments[].faculty_id: integer, exists:tb_mas_faculty,intID
    - Validation rules:
      - term is integer and required
      - assignments is a non-empty array with reasonable upper bound (e.g., max:500)
      - classlist must belong to provided term (checked in controller logic)
      - classlist is not dissolved (isDissolved=0)
      - faculty.teaching must be 1
      - campus match: classlist.campus_id === faculty.campus_id (if both present; if classlist has campus_id but faculty none or mismatched → reject)
  - Modified Request: App\Http\Controllers\Api\V1\GenericApiController::faculty
    - Add optional campus_id: integer filter
    - Existing teaching: integer|in:0,1 retained

- Frontend (AngularJS objects)
  - ClasslistRow (from /api/v1/classlists):
    - intID: number
    - intSubjectID: number
    - intFacultyID: number|null
    - strAcademicYear: string|number (syid)
    - subjectCode: string
    - subjectDescription: string
    - campus_id: number|null
    - sectionCode: string|null
    - intFinalized: number
    - isDissolved: number (0/1)
  - FacultyOption (from /api/v1/generic/faculty):
    - id: number
    - full_name: string
    - teaching: number (0/1)
  - BulkPayload:
    - term: number
    - assignments: Array<{ classlist_id: number, faculty_id: number }>

[Files]
Introduce a new feature module in frontend and minor updates to backend routes/controllers/requests.

- New files to be created:
  - frontend/unity-spa/features/academics/faculty-loading/faculty-loading.html
    - Purpose: UI for listing classlists by term and assigning faculty (inline and bulk save).
  - frontend/unity-spa/features/academics/faculty-loading/faculty-loading.controller.js
    - Purpose: AngularJS controller handling state, filters, pending changes, and save actions.
  - frontend/unity-spa/features/academics/faculty-loading/faculty-loading.service.js
    - Purpose: AngularJS service wrapping /api/v1/classlists list, single PUT update, and bulk assignment endpoint.
  - frontend/unity-spa/features/academics/faculty-loading/by-faculty.html
    - Purpose: Faculty-centric UI to assign sections by faculty. Select a faculty (filtered by teaching=1 and campus), view unassigned sections and sections assigned to that faculty, move items between lists, and bulk save.
  - frontend/unity-spa/features/academics/faculty-loading/by-faculty.controller.js
    - Purpose: AngularJS controller for the by-faculty assignment page. Manages selected faculty, loads classlists (assigned and unassigned), queues changes, and triggers bulk save to assign sections to the chosen faculty.
  - frontend/unity-spa/features/academics/faculty-loading/implementation_plan.md
    - This document.

- Existing files to be modified:
  - frontend/unity-spa/core/routes.js
    - Add routes:
      - path: "/faculty-loading"
        - templateUrl: "features/academics/faculty-loading/faculty-loading.html"
        - controller: "FacultyLoadingController"
        - controllerAs: "vm"
        - requiredRoles: ["registrar", "faculty_admin", "admin"]
      - path: "/faculty-loading/by-faculty"
        - templateUrl: "features/academics/faculty-loading/by-faculty.html"
        - controller: "FacultyLoadingByFacultyController"
        - controllerAs: "vm"
        - requiredRoles: ["registrar", "faculty_admin", "admin"]
  - frontend/unity-spa/shared/components/sidebar/sidebar.controller.js
    - Under Academics group, add menu items:
      - { label: "Faculty Loading", path: "/faculty-loading" }
      - { label: "Assign by Faculty", path: "/faculty-loading/by-faculty" }
  - laravel-api/routes/api.php
    - Update existing route permissions:
      - PUT /api/v1/classlists/{id}: add faculty_admin to middleware 'role:registrar,faculty_admin,admin'
    - Add new route:
      - POST /api/v1/classlists/assign-faculty-bulk → ClasslistController@assignFacultyBulk
      - middleware: 'role:registrar,faculty_admin,admin'
  - laravel-api/app/Http/Controllers/Api/V1/ClasslistController.php
    - Ensure update(ClasslistUpdateRequest $request, int $id) logs faculty assignment changes via SystemLogService::log('update', 'Classlist', id, old, new, request) when intFacultyID is modified (include meta semantics via old/new snapshots; ensure old.intFacultyID and new.intFacultyID differ).
    - Add method assignFacultyBulk(ClasslistAssignFacultyBulkRequest $request): JsonResponse
      - Validate per-item constraints (term match, not dissolved, teaching=1, campus match)
      - Apply updates in a loop; for each applied change, call SystemLogService::log('update', 'Classlist', classlist_id, old, new, request) so logs capture intFacultyID transition (old vs new). Optionally annotate meta by merging flags into the new array (e.g., ['bulk' => true]) if desired.
      - Return structured result: { success, applied_count, total, results: [{ classlist_id, ok, message? }] }
  - laravel-api/app/Http/Controllers/Api/V1/GenericApiController.php
    - Modify faculty() to accept optional campus_id filter:
      - validate: 'campus_id' => 'sometimes|integer'
      - if provided, apply where('campus_id', campus_id)
  - laravel-api/app/Http/Requests/Api/V1/ClasslistAssignFacultyBulkRequest.php
    - New request class with rules described above.

- Files to be deleted or moved:
  - None

- Configuration file updates:
  - None required.

[Functions]
Add a bulk assign function on backend and new Angular service functions; extend faculty search.

- New functions:
  - Backend:
    - App\Http\Controllers\Api\V1\ClasslistController::assignFacultyBulk(ClasslistAssignFacultyBulkRequest $request): JsonResponse
      - Purpose: Process multiple classlist→faculty assignments with validation and campus/teaching constraints.
      - Signature: public function assignFacultyBulk(ClasslistAssignFacultyBulkRequest $request): JsonResponse
  - Frontend (Service):
    - FacultyLoadingService.list(params): Promise<{data, meta}>
      - GET /api/v1/classlists?term=...&amp;filters...
    - FacultyLoadingService.listUnassigned(params): Promise<{data, meta}>
      - GET /api/v1/classlists?term=...&amp;unassigned=1 (server-side when supported) or client-side filter where intFacultyID is null when unassigned flag used.
    - FacultyLoadingService.updateSingle(classlistId, facultyId): Promise<any>
      - PUT /api/v1/classlists/{id} with { intFacultyID: facultyId }
    - FacultyLoadingService.assignBulk(term, assignments): Promise<{applied_count,total,results}>
      - POST /api/v1/classlists/assign-faculty-bulk
  - Frontend (Controller methods - by-faculty page):
    - FacultyLoadingByFacultyController.loadFacultyOptions(campusId?): loads teaching=1 faculty; filters by campus_id when present.
    - FacultyLoadingByFacultyController.loadLists(): loads sections assigned to selected faculty and unassigned sections for the active term.
    - FacultyLoadingByFacultyController.queueAssign(classlistId): marks a classlist to be assigned to the selected faculty.
    - FacultyLoadingByFacultyController.queueUnassign(classlistId): marks a classlist to be cleared (set intFacultyID=null) — optional; if unassign is not allowed, this action can be omitted.
    - FacultyLoadingByFacultyController.saveAll(): builds assignments [{classlist_id, faculty_id}] for queued items and calls FacultyLoadingService.assignBulk; refreshes lists based on result.

- Modified functions:
  - Backend:
    - App\Http\Controllers\Api\V1\ClasslistController::update(ClasslistUpdateRequest $request, int $id): JsonResponse
      - When intFacultyID changes, call SystemLogService::log('update', 'Classlist', id, old, new, request) to persist audit trail (old/new snapshots capturing intFacultyID change).
    - App\Http\Controllers\Api\V1\ClasslistController::index(Request $request): JsonResponse
      - Add optional query flag unassigned=1 to filter tb_mas_classlist.intFacultyID IS NULL (in combination with term).
    - App\Http\Controllers\Api\V1\GenericApiController::faculty(Request $request): JsonResponse
      - Add optional campus_id filter
  - Route middleware on PUT /classlists/{id} to include faculty_admin role.

- Removed functions:
  - None

[Classes]
Introduce one Request class for bulk assignment.

- New classes:
  - App\Http\Requests\Api\V1\ClasslistAssignFacultyBulkRequest
    - Methods:
      - authorize(): bool — returns true (authorization handled by route middleware)
      - rules(): array — as specified under Types
      - messages(): array — customized error messages for bulk items (include index-based context)

- Modified classes:
  - None (controllers updated with new method)

- Removed classes:
  - None

[Dependencies]
No third-party packages are added; reuse existing services and endpoints.

- PHP/Laravel: No composer changes.
- JS/Angular: No npm changes; reuse existing StorageService, TermService, ToastService, and existing API base config.

[Testing]
Adopt endpoint-level validation and UI flows for registrar/faculty_admin/admin roles.

- Backend:
  - Single update: verify PUT /api/v1/classlists/{id} with faculty_admin succeeds; registrar/admin also succeed.
  - Campus restriction: attempt assign with mismatched campus → 422 with clear message.
  - Teaching=1 restriction: attempt assign to teaching=0 faculty → 422.
  - Term mismatch in bulk: specific item rejected with message; others applied.
  - Dissolved classlist rejection: isDissolved=1 → rejected.
  - System logs:
    - After a single update, confirm a log entry exists with action='update', entity='Classlist', entity_id={classlist_id}, where old.intFacultyID ≠ new.intFacultyID and values match the operation; actor resolved from X-Faculty-ID in request.
    - After a bulk assignment, confirm applied_count log entries exist (one per updated classlist) with the same structure capturing the faculty change; if a bulk meta flag was included in new snapshot, verify its presence.
- Frontend:
  - Route guard: routes /faculty-loading and /faculty-loading/by-faculty only visible and accessible to registrar/faculty_admin/admin.
  - Faculty dropdown: filtered by teaching=1; additionally request list with campus_id (from classlist row).
  - Inline save: selecting faculty and saving single row updates immediately and refreshes row.
  - Bulk save: multiple edits queued; Save All sends payload; partial failures show per-row errors; successes clear pending state.
  - By-faculty page:
    - Faculty selector loads teaching=1 faculty; respects campus_id filtering.
    - Lists show: (A) sections currently assigned to selected faculty, (B) unassigned sections for the active term; moving items between lists updates the local queue.
    - Save All assigns queued sections to the selected faculty via bulk API; refreshes lists and surfaces per-row errors.

[Implementation Order]
Backend-first to enable API use by the UI, then frontend wiring and UI.

1) Backend routes and permissions:
   - Update middleware on PUT /api/v1/classlists/{id} to include faculty_admin.
   - Add POST /api/v1/classlists/assign-faculty-bulk route with role:registrar,faculty_admin,admin.
2) New Request class:
   - Create ClasslistAssignFacultyBulkRequest with validation rules.
3) Controller logic:
   - Implement assignFacultyBulk in ClasslistController with teaching and campus validations, term check, dissolved guard, and per-item logging.
4) Generic faculty endpoint:
   - Add campus_id filter to GenericApiController::faculty for client-side dropdown constraints.
5) Frontend routing and menu:
   - Add /faculty-loading route (requiredRoles: registrar, faculty_admin, admin).
   - Add /faculty-loading/by-faculty route (requiredRoles: registrar, faculty_admin, admin).
   - Add "Faculty Loading" and "Assign by Faculty" under Academics in sidebar.
6) Frontend service:
   - Implement FacultyLoadingService with list, listUnassigned (temporary client-side filter until backend supports unassigned=1), updateSingle, assignBulk methods.
