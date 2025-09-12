# Implementation Plan

[Overview]
Deliver a new Academics: Faculty Loading "Assign by Faculty" page that allows registrar, faculty_admin, and admin users to pick a faculty member and assign or unassign sections (classlists) for the active term. The page complements the existing classlist-centric assignment page by providing a faculty-centric workflow with dual lists (Assigned vs Unassigned) and bulk-apply operations.

This implementation reuses existing backend APIs:
- GET /api/v1/classlists for listing classlists (with pagination and filters)
- PUT /api/v1/classlists/{id} for single-row updates
- POST /api/v1/classlists/assign-faculty-bulk for bulk assignments

Minimal backend changes are planned only to support unassign (clearing intFacultyID) in the single update endpoint while keeping all other logic and server-side validations intact. The frontend will add a dedicated route, controller, and template, and wire them to the existing service with minor service extensions and menu updates. Access is restricted to roles: registrar, faculty_admin, and admin.

[Types]  
Type system changes are limited to AngularJS JSDoc typedefs and explicit payload/response contracts used by the SPA.

Detailed type definitions and contracts:
- AngularJS Interfaces (doc types in code comments)
  - FacultyOption
    - id: number
    - full_name: string
    - first_name?: string
    - middle_name?: string
    - last_name?: string
    - teaching?: number (0|1)
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
    - isDissolved?: number (0|1)
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

Validation rules/relationships:
- Assigning requires teaching=1 faculty and campus match with classlist when classlist.campus_id is not null (enforced server-side in controller).
- Unassigning sets intFacultyID to null for the selected rows (frontend will call PUT /classlists/{id} with intFacultyID: null, requiring backend to allow nullable).

[Files]
Introduce a new faculty-centric page; minimally extend service; update routes, role guards, and sidebar; add one backend change to enable unassign via single-row update.

Detailed breakdown:
- New files to be created (frontend)
  - frontend/unity-spa/features/academics/faculty-loading/by-faculty.html
    - Purpose: Dual-list UI (Assigned vs Unassigned) with controls to move items and bulk save. Includes pagination on both lists.
  - frontend/unity-spa/features/academics/faculty-loading/by-faculty.controller.js
    - Purpose: AngularJS controller for the by-faculty page. Manages selected faculty, loads lists, paginates, queues changes, and triggers save.

- Existing files to be modified (frontend)
  - frontend/unity-spa/features/academics/faculty-loading/faculty-loading.service.js
    - Add helper methods:
      - listByFaculty(params): wraps list() adding intFacultyID
      - listUnassigned(params): calls list() then client-filters rows where intFacultyID == null or == 0
  - frontend/unity-spa/core/routes.js
    - Add route:
      - path: "/faculty-loading/by-faculty"
        - templateUrl: "features/academics/faculty-loading/by-faculty.html"
        - controller: "FacultyLoadingByFacultyController"
        - controllerAs: "vm"
        - requiredRoles: ["registrar", "faculty_admin", "admin"]
  - frontend/unity-spa/core/roles.constants.js
    - Ensure: { test: '^/faculty-loading(?:/.*)?$', roles: ['registrar', 'faculty_admin', 'admin'] } to include faculty_admin.
  - frontend/unity-spa/shared/components/sidebar/sidebar.controller.js or sidebar.html
    - Add menu item in Academics:
      - { label: "Assign by Faculty", path: "/faculty-loading/by-faculty" }
      - visibility guarded by roles (registrar, faculty_admin, admin)
  - frontend/unity-spa/index.html
    - Add script tag for features/academics/faculty-loading/by-faculty.controller.js

- Existing files to be modified (backend)
  - laravel-api/app/Http/Requests/Api/V1/ClasslistUpdateRequest.php
    - Change intFacultyID rule to allow nullable:
      - from: ['sometimes', 'integer', 'exists:tb_mas_faculty,intID']
      - to:   ['sometimes', 'nullable', 'integer', 'exists:tb_mas_faculty,intID']
  - laravel-api/app/Http/Controllers/Api/V1/ClasslistController.php
    - In update(): handle unassign (when array_key_exists('intFacultyID', $data) and $data['intFacultyID'] === null):
      - Skip faculty existence/teaching/campus validations
      - Apply update(['intFacultyID' => null]) subject to dissolved check
      - System log the change

- Files to be deleted or moved
  - None.

- Configuration file updates
  - None. Reuse existing APP_CONFIG.API_BASE and RequireRole via X-Faculty-ID header patterns already implemented in FacultyLoadingService.

[Functions]
Add a new controller with focused methods; extend the service; add small backend allowance for unassign.

Detailed breakdown:
- New functions (frontend)
  - FacultyLoadingService.listByFaculty(params: { term:number, page?:number, per_page?:number, ... }): Promise<FacultyLoadingListResponse>
    - Implementation: return list(Object.assign({}, params, { intFacultyID: params.facultyId || params.intFacultyID }))
  - FacultyLoadingService.listUnassigned(params: { term:number, page?:number, per_page?:number, ... }): Promise<FacultyLoadingListResponse>
    - Implementation: call list(params), then client-filter rows where intFacultyID is null or falsy, adjust meta locally (preserve total for transparency)
  - FacultyLoadingByFacultyController (by-faculty.controller.js)
    - activate(): init term (via TermService), load faculty options (teaching=1), and load lists
    - setTerm(id): update term; reset pagination; reload lists
    - selectFaculty(id): set selected faculty; reset pagination; reload lists
    - loadAssigned(): FacultyLoadingService.listByFaculty({ term, intFacultyID: selectedFaculty, page, per_page })
    - loadUnassigned(): FacultyLoadingService.listUnassigned({ term, page, per_page })
    - queueAssign(classlistId): mark for assignment to selected faculty
    - queueUnassign(classlistId): mark for unassign (clear)
    - moveToAssign(classlistId)/moveToUnassign(classlistId): UI helpers to move items between lists &amp; update queues
    - saveAll(): build:
        - assignments = [{ classlist_id, faculty_id: selectedFaculty }]
        - unassignments = [{ classlist_id }] (performed via per-row PUT with intFacultyID=null)
      Submit assignments with assignBulk(); for unassignments, iterate PUT classlists/{id} with { intFacultyID: null }
    - Toast feedback and refresh lists on completion or per-row error
    - Pagination helpers for both lists (pagePrev/Next/Go patterns matching existing page)

- Modified functions (backend)
  - ClasslistController@update(ClasslistUpdateRequest $request, int $id): JsonResponse
    - Add branch to allow unassign: if provided and null, bypass faculty validations and set intFacultyID to null (still block dissolved classlists)
  - ClasslistUpdateRequest@rules(): array
    - Allow nullable for intFacultyID to support clearing assignments

- Removed functions
  - None.

[Classes]
No new backend classes; one frontend controller added.

Detailed breakdown:
- New classes
  - FacultyLoadingByFacultyController (AngularJS controller)
    - Key methods: activate, selectFaculty, loadAssigned, loadUnassigned, queueAssign, queueUnassign, saveAll, pagination helpers
    - No inheritance (standard Angular controller pattern)
- Modified classes
  - App\Http\Controllers\Api\V1\ClasslistController: see [Functions]
  - App\Http\Requests\Api\V1\ClasslistUpdateRequest: see [Functions]
- Removed classes
  - None.

[Dependencies]
No new external dependencies.

The feature reuses:
- Existing GenericApiController@faculty for dropdown of teaching faculty (filter teaching=1; optional 'q' search)
- Existing Classlist endpoints and validations for campus matching and teaching flag
- Existing TermService, StorageService, ToastService, and APP_CONFIG.API_BASE

[Testing]
End-to-end UI and API validation for assigning and unassigning sections by faculty.

- Backend
  - Manual API checks:
    - PUT /api/v1/classlists/{id} with { intFacultyID: null } should clear assignment when not dissolved; 422 on dissolved; 404 when not found
    - PUT /api/v1/classlists/{id} with { intFacultyID: <teaching faculty id> } should succeed (existing behavior)
    - POST /api/v1/classlists/assign-faculty-bulk for bulk assigns works for valid campus/teaching and term match
- Frontend
  - Role access: routes restricted to registrar, faculty_admin, admin
  - Faculty dropdown loads teaching=1 options; can search/filter via GenericApiController?q
  - Lists paginate correctly; move between lists updates local queues
  - saveAll applies:
    - assign queue via assignBulk
    - unassign queue via per-row PUT with intFacultyID=null
  - Toast messages cover partial failures and totals

[Implementation Order]
Implement minimal backend allowance first; then frontend page/service/route wiring; finally menu/role guard and manual testing.

1) Backend (Minimal allowance for unassign)
   1.1 Edit ClasslistUpdateRequest to allow nullable intFacultyID.
   1.2 Update ClasslistController@update to support unassign (intFacultyID === null) without faculty validations, but still respecting dissolved restriction; keep existing validations for assign.
   1.3 Smoke-test with curl/HTTP client.

2) Frontend: Service and Page
   2.1 Extend FacultyLoadingService with listByFaculty and listUnassigned (client-filter).
   2.2 Create by-faculty.controller.js with controller logic and queues.
   2.3 Create by-faculty.html with dual lists, controls, pagination, and bulk save.
   2.4 Wire new route in core/routes.js with requiredRoles: ["registrar", "faculty_admin", "admin"].
   2.5 Update roles.constants.js to include faculty_admin on '^/faculty-loading(?:/.*)?$'.
   2.6 Add sidebar menu item "Assign by Faculty" under Academics; visibility restricted by roles.
   2.7 Add script tag for by-faculty.controller.js in index.html.

3) QA and Verification
   3.1 Verify page permissions with non-privileged and privileged users.
   3.2 Verify assign and unassign flows; review classlist record changes on the backend.
   3.3 Validate server-side campus/teaching checks with expected error prompts and partial successes.
