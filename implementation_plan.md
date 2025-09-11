# Implementation Plan

[Overview]
Deliver a new Academics: Faculty Loading "Assign by Faculty" page so registrar, faculty_admin, and admin users can select a faculty member and assign or unassign sections (classlists) for the active term. This complements the existing classlist-centric assignment page with a faculty-centric workflow using dual lists (Assigned vs Unassigned) and bulk operations.

The solution reuses existing backend APIs for listing classlists, single-row updates, and bulk-assign. The only backend change required is to explicitly allow unassigning (clearing intFacultyID) in the single update endpoint. The frontend adds a dedicated route, controller, and template, extends the service with helper methods, and updates the sidebar and role guards accordingly. Unassigned filtering remains client-side per requirements.

[Types]  
Type system changes are limited to AngularJS JSDoc typedefs and explicit request/response contracts used in the SPA. No TypeScript is used in this repository.

Detailed type definitions and contracts:
- AngularJS Interfaces (doc types in code comments)
  - FacultyOption
    - id: number
    - full_name: string
    - first_name?: string
    - middle_name?: string
    - last_name?: string
    - teaching?: 0|1
  - ClasslistRow
    - intID: number
    - intSubjectID: number
    - sectionCode: string
    - strAcademicYear: string (term id)
    - intFacultyID: number|null
    - subjectCode?: string
    - subjectDescription?: string
    - facultyFirstname?: string
    - facultyLastname?: string
    - campus_id?: number|null
    - isDissolved?: 0|1
    - intFinalized?: number
  - FacultyLoadingListResponse
    - success: boolean
    - data: ClasslistRow[]
    - meta: { current_page: number, per_page: number, total: number, last_page: number }
  - AssignBulkRequest
    - term: number
    - assignments: Array<{ classlist_id: number, faculty_id: number }>
  - AssignBulkResponse
    - success: boolean
    - applied_count: number
    - total: number
    - results: Array<{ classlist_id: number, ok: boolean, message?: string }>

Validation rules and relationships:
- Assigning requires faculty.teaching=1 and classlist.campus_id to match faculty.campus_id when classlist.campus_id is not null (enforced server-side).
- Unassigning sets intFacultyID to null (requires backend update endpoint to accept nullable intFacultyID).
- Client-side “unassigned” listing fetches and filters the dataset by intFacultyID in [null, 0, '', undefined]; aggregates up to enough rows to fill requested page.

[Files]
Frontend adds a faculty-centric page and service helpers; backend allows nullable intFacultyID on update. Role guards and sidebar gain entries for the new route.

Detailed breakdown:
- New files to be created (frontend)
  - frontend/unity-spa/features/academics/faculty-loading/by-faculty.html
    - Purpose: Dual-list UI (Assigned vs Unassigned) with faculty selector, section search, pagination on both lists, and Save All for bulk apply.
  - frontend/unity-spa/features/academics/faculty-loading/by-faculty.controller.js
    - Purpose: AngularJS controller for the faculty-centric page. Handles term sync, faculty selection, list loading, queues (assign/unassign), and saving.

- Existing files to be modified (frontend)
  - frontend/unity-spa/features/academics/faculty-loading/faculty-loading.service.js
    - Add:
      - listByFaculty(params): wraps list() to inject intFacultyID from params.facultyId|intFacultyID.
      - listUnassigned(params): calls list() and client-filters rows where intFacultyID is null/0/''; adjusts meta locally.
    - Update:
      - updateSingle(classlistId, facultyId) to support facultyId null for unassign.
  - frontend/unity-spa/core/routes.js
    - Add route:
      - "/faculty-loading/by-faculty"
        - templateUrl: "features/academics/faculty-loading/by-faculty.html"
        - controller: "FacultyLoadingByFacultyController"
        - controllerAs: "vm"
        - requiredRoles: ["registrar", "faculty_admin", "admin"]
    - Ensure existing "/faculty-loading" route has requiredRoles: ["registrar", "faculty_admin", "admin"].
  - frontend/unity-spa/core/roles.constants.js
    - ACCESS_MATRIX: ensure { test: '^/faculty-loading(?:/.*)?$', roles: ['registrar', 'faculty_admin', 'admin'] }.
  - frontend/unity-spa/shared/components/sidebar/sidebar.controller.js (or sidebar.html if menu is declarative)
    - Add menu entry under Academics:
      - { label: 'Assign by Faculty', path: '/faculty-loading/by-faculty' }
  - frontend/unity-spa/index.html
    - Include script tag for features/academics/faculty-loading/by-faculty.controller.js

- Existing files to be modified (backend)
  - laravel-api/app/Http/Requests/Api/V1/ClasslistUpdateRequest.php
    - Change intFacultyID rule to allow nullable:
      - ['sometimes', 'nullable', 'integer', 'exists:tb_mas_faculty,intID']
  - laravel-api/app/Http/Controllers/Api/V1/ClasslistController.php
    - In update(): if intFacultyID is present and is null, allow unassign:
      - Guard dissolved classlists with 422.
      - Update intFacultyID to null.
      - Persist system log via SystemLogService.
    - Keep existing validations for assignment: faculty exists, teaching=1, campus match when classlist.campus_id != null.

- Files to be deleted or moved
  - None.

- Configuration file updates
  - None required. Reuse APP_CONFIG.API_BASE, X-Faculty-ID header, and existing middleware for role checks.

[Functions]
Frontend adds new controller functions and service helpers; backend modifies the update path for unassign support.

Detailed breakdown:
- New functions (frontend)
  - FacultyLoadingService.listByFaculty(params: { term:number, facultyId?:number, intFacultyID?:number, page?:number, per_page?:number }): Promise<FacultyLoadingListResponse>
  - FacultyLoadingService.listUnassigned(params: { term:number, page?:number, per_page?:number }): Promise<FacultyLoadingListResponse> (client-side filter and pagination)
  - FacultyLoadingByFacultyController (file: frontend/unity-spa/features/academics/faculty-loading/by-faculty.controller.js)
    - activate()
    - setTerm(id)
    - onFacultyChange()
    - reload(force?)
    - applySearch()
    - clearSearch()
    - onSectionQuery(q)
    - onSectionSelect()
    - pageAssignedPrev/Next/Go
    - pageUnassignedPrev/Next/Go
    - moveToAssign(row)
    - moveToUnassign(row)
    - queueAssign(row)
    - queueUnassign(row)
    - isQueuedAssign(row)
    - isQueuedUnassign(row)
    - hasPending()
    - saveAll()
    - facultyNameOfRow(row)
    - getTermLabel(sel, id)

- Modified functions (backend)
  - App\Http\Controllers\Api\V1\ClasslistController::update(ClasslistUpdateRequest $request, int $id): JsonResponse
    - Add branch to set intFacultyID = null when provided and null; skip faculty validations in this branch; still guard dissolved.
  - App\Http\Requests\Api\V1\ClasslistUpdateRequest::rules(): array
    - Allow nullable for intFacultyID.

- Removed functions
  - None.

[Classes]
No new backend classes; one new AngularJS controller is added.

Detailed breakdown:
- New classes
  - FacultyLoadingByFacultyController (AngularJS)
    - Key methods described above; follows existing controller patterns; no inheritance.
- Modified classes
  - ClasslistController (Laravel): update() method extended for unassign as described.
  - ClasslistUpdateRequest (Laravel): rules() updated for nullable intFacultyID.
- Removed classes
  - None.

[Dependencies]
No new external dependencies or packages.

The feature reuses:
- GenericApiController@faculty for dropdown (teaching=1 default; supports q and campus_id filters).
- Classlist endpoints:
  - GET /api/v1/classlists (term, sectionCode, intFacultyID, paging)
  - PUT /api/v1/classlists/{id} (now supports intFacultyID: null)
  - POST /api/v1/classlists/assign-faculty-bulk (bulk assign)
- TermService, StorageService, ToastService, and APP_CONFIG.API_BASE in the SPA.
- RequireRole via X-Faculty-ID header for protected routes, consistent with other admin features.

[Testing]
Testing focuses on role-gated access and critical-path flows for assign/unassign.

- Backend (manual API checks)
  - PUT /api/v1/classlists/{id} with { intFacultyID: null }
    - Expect 200 success when not dissolved; log entry created.
    - Expect 422 on dissolved classlist.
    - Expect 404 on not found.
  - PUT /api/v1/classlists/{id} with { intFacultyID: <teaching faculty id> }
    - Expect success; campus/teaching rules enforced (422 with message on violations).
  - POST /api/v1/classlists/assign-faculty-bulk
    - Bulk success and partial failure handling (collects per-row results).
  - GET /api/v1/classlists
    - Verify filtering via term, intFacultyID, sectionCode, pagination meta.

- Frontend (manual UI checks)
  - Route access enforced for registrar, faculty_admin, admin.
  - Dropdown loads teaching=1 faculty; campus filter works when applied; q search returns expected results.
  - Unassigned list aggregates client-side across pages until enough rows are gathered; respects page/per_page; performance acceptable with per_page up to 100 per fetch.
  - Moving items between lists updates assign/unassign queues; Save All applies:
    - Assign queue via assignBulk
    - Unassign queue via sequential PUTs with { intFacultyID: null }
  - Toast messages display for success, partial failures, and error cases; list refreshes reflect final state.

[Implementation Order]
Backend unassign allowance first, then frontend service and page, followed by role/menu wiring and QA.

1) Backend (Unassign allowance)
   1.1 Update ClasslistUpdateRequest to accept nullable intFacultyID.
   1.2 Extend ClasslistController::update to handle intFacultyID === null (unassign) with dissolved guard; keep existing assignment validations.
   1.3 Smoke-test with curl.

2) Frontend: Service and Page
   2.1 Extend FacultyLoadingService with listByFaculty and listUnassigned (client filtering).
   2.2 Implement by-faculty.controller.js for faculty-centric workflow (dual lists, search, pagination, queues).
   2.3 Implement by-faculty.html for the UI.
   2.4 Add route "/faculty-loading/by-faculty" with requiredRoles ["registrar", "faculty_admin", "admin"].
   2.5 Update roles.constants.js ACCESS_MATRIX for '^/faculty-loading' to include faculty_admin.
   2.6 Add sidebar menu entry "Assign by Faculty" under Academics.
   2.7 Include by-faculty.controller.js in index.html.

3) QA and Verification
   3.1 Verify permissions for registrar/faculty_admin/admin; ensure hidden for other roles.
   3.2 Verify assign and unassign flows; check edge cases (teaching=0, campus mismatch, dissolved).
   3.3 Confirm pagination and search behave as expected in both lists; validate bulk and per-row operations and toasts.
