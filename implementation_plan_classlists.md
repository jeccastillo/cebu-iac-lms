# Implementation Plan

[Overview]
Provide full CRUD for tb_mas_classlist via Laravel API with legacy-safe delete as dissolve (isDissolved=1) gated by student membership checks, integrate SystemLogService logging for create/update/delete, and implement an AngularJS 1.x SPA UI (list/create/edit/dissolve) that excludes restricted legacy-computed fields.

This implementation adds a dedicated API surface and front-end screens for managing classlists. The delete operation mirrors legacy behavior: it does not physically delete rows; instead, it marks classlists dissolved by setting isDissolved=1. A safety rule prevents dissolving when any tb_mas_classlist_student rows reference the classlist. To preserve legacy constructs generated elsewhere, restricted fields (strClassName, year, strSection, sub_section) are excluded from all forms and forcibly saved as blank strings. SystemLogService is used to write audit logs on all mutations.

[Types]
Introduce request DTO-style validation for create/update and rely on existing Eloquent models for persistence.

Detailed definitions:
- tb_mas_classlist (subset used)
  - intID: int PK
  - intSubjectID: int required (FK to tb_mas_subjects.intID)
  - intFacultyID: int required (FK to tb_mas_faculty.intID)
  - strAcademicYear: string required (term id: e.g., tb_mas_sy.intID)
  - strUnits: string|null optional
  - intFinalized: int in [0,1,2] default 0
  - isDissolved: int in [0,1] default 0
  - campus_id: int|null optional (added by migration fk_subjects/campuses)
  - sectionCode: string|null optional (migrations, non-form)
  - Restricted legacy-computed fields (force blank on save):
    - strClassName: string set to ''
    - year: string|int set to ''
    - strSection: string set to ''
    - sub_section: string set to ''
- tb_mas_classlist_student
  - intCSID: int PK
  - intClassListID: int required FK -> tb_mas_classlist.intID
  - intStudentID: int FK -> tb_mas_users.intID
  - Other grading columns not modified here

[Files]
Add Laravel API endpoints and AngularJS feature files; keep existing models; update SPA routes and sidebar.

Backend (Laravel)
- New/Existing to be modified:
  - laravel-api/app/Http/Controllers/Api/V1/ClasslistController.php
    - index(): list with filters and includeDissolved flag
    - show(): return single row or 404
    - store(): create row, force restricted fields to "", default intFinalized=0, isDissolved=0; log create
    - update(): update allowed fields, force restricted fields to ""; log update
    - destroy(): dissolve: check tb_mas_classlist_student; 422 if exists; otherwise set isDissolved=1 idempotently; log delete
  - laravel-api/app/Http/Requests/Api/V1/ClasslistStoreRequest.php
    - Validate: intSubjectID, intFacultyID, strAcademicYear required; strUnits optional; intFinalized in [0,1,2]; campus_id int|null; explicitly DO NOT accept restricted fields.
  - laravel-api/app/Http/Requests/Api/V1/ClasslistUpdateRequest.php
    - Same as store but all optional; explicitly DO NOT accept restricted fields.
  - laravel-api/routes/api.php
    - Add routes:
      - GET /api/v1/classlists
      - GET /api/v1/classlists/{id}
      - POST /api/v1/classlists (role:registrar,admin)
      - PUT /api/v1/classlists/{id} (role:registrar,admin)
      - DELETE /api/v1/classlists/{id} (role:registrar,admin)
- Existing used:
  - laravel-api/app/Models/Classlist.php
  - laravel-api/app/Models/ClasslistStudent.php
  - laravel-api/app/Services/SystemLogService.php
- Config/DB
  - Ensure tb_mas_classlist has isDissolved column; no schema change required unless missing (out of scope unless failure observed).

Frontend (AngularJS 1.x)
- New files:
  - frontend/unity-spa/features/classlists/classlists.service.js
    - Wraps API endpoints for list/get/create/update/dissolve; pulls subjects per term via registrar/grading/sections; faculty options via generic/faculty.
  - frontend/unity-spa/features/classlists/classlists.controller.js
    - List view controller: filters by term/subject/faculty/finalized; includeDissolved toggle; dissolve action with 422 handling.
  - frontend/unity-spa/features/classlists/classlist-edit.controller.js
    - Add/Edit controller: binds model to form; excludes restricted fields; default strAcademicYear from selected term; save → POST/PUT.
  - frontend/unity-spa/features/classlists/list.html
    - Table view with filters and dissolve button.
  - frontend/unity-spa/features/classlists/edit.html
    - Form view excluding restricted fields; fields: intSubjectID, intFacultyID, strAcademicYear, strUnits, intFinalized, campus_id.
- Modified files:
  - frontend/unity-spa/core/routes.js
    - Add routes:
      - /classlists (list)
      - /classlists/add
      - /classlists/:id/edit
      - All require roles ['registrar','admin']
  - frontend/unity-spa/index.html
    - Include the new classlists scripts
  - frontend/unity-spa/shared/components/sidebar/sidebar.html
    - Add link to /classlists with RBAC check

[Functions]
Add controller and service functions in both PHP and JS, and wire logging.

Backend
- ClasslistController@index(Request $request): array/json
  - Filters: strAcademicYear (term), intSubjectID, intFacultyID, intFinalized, includeDissolved=false
  - Order: intID desc (default)
- ClasslistController@show(int $id): json 200/404
- ClasslistController@store(ClasslistStoreRequest $request): json 201
  - Force restricted fields to ''; defaults intFinalized=0, isDissolved=0
  - Log: SystemLogService::log('create','Classlist',$id,null,$new,$request)
- ClasslistController@update(ClasslistUpdateRequest $request, int $id): json 200/404
  - Force restricted fields to ''; log update
- ClasslistController@destroy(int $id): json 200/404/422
  - If students exist: 422 {message: 'Cannot dissolve: classlist has students'}
  - Else: set isDissolved=1 (idempotent); log delete (entity=Classlist)

Frontend
- ClasslistsService
  - list(opts), get(id), create(payload), update(id,payload), dissolve(id)
  - getFacultyOptions(), getSubjectsByTerm(termId)
- ClasslistsController
  - init(), reload(), goAdd(), goEdit(id), onTermChange(), dissolve(row), resetFilters()
- ClasslistEditController
  - init(), save(), cancel()
  - Model excludes restricted fields; backend overwrites them to ''

[Classes]
No new PHP classes beyond the controller/requests; reuse existing models.

- New/Modified Classes
  - App\Http\Controllers\Api\V1\ClasslistController (new)
  - App\Http\Requests\Api\V1\ClasslistStoreRequest (new)
  - App\Http\Requests\Api\V1\ClasslistUpdateRequest (new)
- Existing to use without change
  - App\Models\Classlist
  - App\Models\ClasslistStudent
  - App\Services\SystemLogService

[Dependencies]
No new external packages required; rely on existing Laravel, AngularJS 1.x, and project services.

- Backend: Laravel DB/Eloquent, existing SystemLogService
- Frontend: AngularJS 1.8, project’s RoleService, StorageService, TermService, CampusService; APP_CONFIG.API_BASE

[Testing]
Use critical-path manual verification for API and UI; ensure logging and dissolve guard.

Backend API (manual)
- POST /api/v1/classlists (201): creates with isDissolved=0, intFinalized default 0; restricted fields blank
- GET /api/v1/classlists?strAcademicYear=... (200): filters respected
- GET /api/v1/classlists/{id} (200/404)
- PUT /api/v1/classlists/{id} (200): updates allowed fields; restricted remain blank
- DELETE /api/v1/classlists/{id} (200/422/404):
  - 422 when tb_mas_classlist_student exists
  - 200 when dissolved or idempotent re-dissolve
- SystemLogService logs present for create/update/delete (entity='Classlist')

Frontend UI (manual)
- Navigate to /classlists:
  - Term auto-populated from TermService; filters drive API requests
  - Include dissolved toggle works
- Add flow:
  - Form excludes strClassName/year/strSection/sub_section
  - Save → success and route back to list
- Edit flow:
  - Loads values; save updates
- Dissolve flow:
  - 422 shows friendly error; otherwise sets dissolved badge
- RBAC:
  - Routes guarded for registrar/admin
  - Sidebar link visible only for allowed roles

[Implementation Order]
Implement backend first, then UI, then test end-to-end.

1) Backend: Controller + Requests + Routes
2) Force restricted fields blank in store/update helpers
3) Delete safety: check tb_mas_classlist_student before dissolve
4) Integrate SystemLogService for create/update/delete with request metadata
5) Frontend: service, controllers, templates
6) Wire routes and sidebar; include scripts
7) Manual API tests (Postman/Curl)
8) Manual UI tests; verify role restrictions and dissolve guard
9) Review logs and adjust error messages
