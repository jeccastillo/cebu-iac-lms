# Implementation Plan

[Overview]
Add full CRUD for tb_mas_curriculum via Laravel API and AngularJS frontend, with system logs for create, update, delete, and subject add/remove operations, using the globally selected campus as the required campus_id for creation.

This implementation provides a dedicated Curriculum management module in the AngularJS unity-spa application with list/add/edit pages. The backend already exposes core endpoints; we will enforce campus_id validation, integrate comprehensive system logging, and wire a new frontend module. The UI will default campus_id from the global CampusService-selected campus, allow selection via a dropdown, and reuse existing APIs for programs and campuses. Deletions will be protected by backend constraints (no delete when curriculum has subjects). Write operations remain restricted to registrar and admin roles.

[Types]  
No new PHP classes or database schema types are introduced; we refine validation and detail the payload structures used by API and frontend.

Type definitions and fields (CI-compatible naming):
- Curriculum (tb_mas_curriculum)
  - intID: integer (primary key)
  - strName: string (required on create, max 255)
  - intProgramID: integer (required on create)
  - active: boolean/integer (default 1 on create)
  - isEnhanced: boolean/integer (default 0 on create)
  - campus_id: integer (required on create; optional on update)
- CurriculumSubject association (tb_mas_curriculum_subject) [existing]
  - intID: integer (primary key)
  - intCurriculumID: integer (FK to Curriculum.intID)
  - intSubjectID: integer (FK to tb_mas_subjects.intID)
  - intYearLevel: integer (1..10)
  - intSem: integer (1..3)

Validation rules:
- Create Curriculum: strName required|string|max:255, intProgramID required|integer, campus_id required|integer, active boolean (default 1), isEnhanced boolean (default 0)
- Update Curriculum: any of strName|intProgramID|campus_id|active|isEnhanced sometimes
- Add Subject: intSubjectID required|integer, intYearLevel required|integer|min:1|max:10, intSem required|integer|min:1|max:3
- Remove Subject: path params id (curriculum) and subjectId (subject)

[Files]
We will create a new AngularJS module for Curriculum CRUD, update routes, and enhance backend validation and logging.

- New frontend files:
  - frontend/unity-spa/features/curricula/list.html
    - Purpose: list curricula with search, pagination-lite, campus filter display, and delete action.
  - frontend/unity-spa/features/curricula/edit.html
    - Purpose: add/edit curriculum form with fields strName, campus_id (dropdown, defaulted from global), intProgramID (dropdown), active (toggle), isEnhanced (toggle).
  - frontend/unity-spa/features/curricula/curricula.controller.js
    - Purpose: CurriculaListController and CurriculumEditController controllers implementing list/search/delete and create/update flows, binding to CampusService and reusing APIs for programs and campuses.
  - frontend/unity-spa/features/curricula/curricula.service.js
    - Purpose: CurriculaService encapsulating API calls: list, show, create, update, delete; plus utilities for fetching programs/campuses for dropdowns.

- Existing frontend files to be modified:
  - frontend/unity-spa/core/routes.js
    - Add routes:
      - /curricula (list)
      - /curricula/add (create)
      - /curricula/:id/edit (update)
    - requiredRoles: ['registrar', 'admin']

- Backend files to be modified:
  - laravel-api/app/Http/Controllers/Api/V1/CurriculumController.php
    - Import and use App\Services\SystemLogService
    - Log on store (create), update, destroy (delete)
    - Log on addSubject and removeSubject (use action='update' to conform to SystemLogService docblock; include subject association in old/new values)
  - laravel-api/app/Http/Requests/Api/V1/CurriculumUpsertRequest.php
    - Require campus_id on POST (create)
    - Allow campus_id sometimes on PUT/PATCH (update)
    - Add rule: 'campus_id' => ['integer', 'required' on create, 'sometimes' on update]

- Existing files to be referenced (no modifications required unless discovered during implementation):
  - laravel-api/routes/api.php (routes already exist with role middlewares)
  - laravel-api/app/Http/Resources/CurriculumResource.php (maps output)
  - laravel-api/app/Services/SystemLogService.php (logging facility)
  - Existing Program and Campus endpoints (for dropdown data)

- Files to be deleted or moved:
  - None.

- Configuration updates:
  - None required.

[Functions]
We add new AngularJS controller/service functions and modify backend controller methods for logging and validation.

- New functions:
  - frontend/unity-spa/features/curricula/curricula.service.js
    - CurriculaService.list(params): Promise - GET /api/v1/curriculum with optional search, campus_id, limit/page
    - CurriculaService.show(id): Promise - GET /api/v1/curriculum/{id}
    - CurriculaService.create(payload): Promise - POST /api/v1/curriculum
    - CurriculaService.update(id, payload): Promise - PUT /api/v1/curriculum/{id}
    - CurriculaService.remove(id): Promise - DELETE /api/v1/curriculum/{id}
    - CurriculaService.getPrograms(): Promise - GET /api/v1/programs
    - CurriculaService.getCampuses(): Promise - GET /api/v1/campuses
  - frontend/unity-spa/features/curricula/curricula.controller.js
    - CurriculaListController:
      - vm.load(): loads list with search term
      - vm.onSearch(): debounced reload
      - vm.delete(row): confirm then call remove
    - CurriculumEditController:
      - vm.init(): initialize for add/edit; bind campus_id from CampusService when adding
      - vm.loadDropdowns(): fetch programs and campuses
      - vm.save(): create or update based on isEdit
      - vm.onCampusChange(): optional; keep consistency with global campus changed
      - vm.load(): fetch record for edit, then load dropdowns and set selections

- Modified functions:
  - laravel-api/app/Http/Controllers/Api/V1/CurriculumController.php
    - store(): after insert, SystemLogService::log('create', 'Curriculum', $newId, null, $created, $request)
    - update(): capture $old; after update fetch $updated; SystemLogService::log('update', 'Curriculum', $id, $old, $updated, $request)
    - destroy(): capture $old; after delete SystemLogService::log('delete', 'Curriculum', $id, $old, null, request())
    - addSubject(): on success, SystemLogService::log('update', 'Curriculum', $id, null, ['intSubjectID' => $data['intSubjectID'], 'intYearLevel' => $data['intYearLevel'], 'intSem' => $data['intSem']], $request)
    - removeSubject(): on success, SystemLogService::log('update', 'Curriculum', $id, ['intSubjectID' => (int)$subjectId], null, request())
  - laravel-api/app/Http/Requests/Api/V1/CurriculumUpsertRequest.php
    - Add campus_id rules per above

- Removed functions:
  - None.

[Classes]
We add AngularJS controllers; no new PHP classes. We modify an existing PHP controller class.

- New classes (AngularJS controllers):
  - CurriculaListController (frontend/unity-spa/features/curricula/curricula.controller.js)
    - Key methods: load, onSearch, delete
  - CurriculumEditController (frontend/unity-spa/features/curricula/curricula.controller.js)
    - Key methods: init, loadDropdowns, save, onCampusChange, load
    - Inherits from AngularJS base controller pattern; no classical inheritance.

- Modified classes:
  - App\Http\Controllers\Api\V1\CurriculumController (PHP)
    - Logging integration per [Functions] and input validation enforced by request class

- Removed classes:
  - None.

[Dependencies]
No new external dependencies.

- Reuse existing:
  - SystemLogService (already present)
  - Program and Campus APIs for dropdowns
  - Routes with middleware role:registrar,admin for curriculum writes
  - AngularJS unityApp, $http, $routeParams, $location, CampusService

[Testing]
Manual tests and existing smoke scripts will validate CRUD and subject association paths; add checks for system log entries.

- Backend tests:
  - Use laravel-api/tests/scripts/curriculum-smoke.ps1 to:
    - GET /curriculum (index) succeeds
    - POST /curriculum creates with required campus_id (use global campus ID or a known campus)
    - GET /curriculum/{id} returns created
    - PUT /curriculum/{id} updates strName
    - GET /curriculum/{id}/subjects returns array
    - POST /curriculum/{id}/subjects (with a valid subjectId) adds association (should log 'update')
    - DELETE /curriculum/{id}/subjects/{subjectId} removes association (should log 'update')
    - DELETE /curriculum/{id} deletes record (or returns 422 if still associated)
  - Validate system logs table (App\Models\SystemLog) contains records for the above actions (optional script or DB check).

- Frontend tests (manual):
  - Navigate to #/curricula: see list, search by name; delete prompts confirmation
  - #/curricula/add: campus dropdown defaulted to global; strName required; intProgramID required; save succeeds and returns to list
  - #/curricula/:id/edit: loads existing data; update persists; toggles active/isEnhanced
  - Change global campus while on add page: campus dropdown updates to selected; can save with new campus
  - Permissions: routes requireRoles ['registrar', 'admin']; ensure other roles cannot access

[Implementation Order]
Implement backend validation/logging first, then frontend scaffolding, then wire routes and UI, and finally test end-to-end.

1) Backend validation update:
   - Update CurriculumUpsertRequest: add campus_id rule (required on POST, sometimes on PUT/PATCH).
2) Backend logging:
   - Import SystemLogService in CurriculumController.
   - Add log() calls for store, update, destroy, addSubject, removeSubject as specified.
3) Frontend routing:
   - Update frontend/unity-spa/core/routes.js with /curricula, /curricula/add, /curricula/:id/edit routes (requiredRoles ['registrar', 'admin']).
4) Frontend services:
   - Create CurriculaService with list/show/create/update/remove and helpers getPrograms/getCampuses.
5) Frontend controllers and views:
   - Create curricula.controller.js containing CurriculaListController and CurriculumEditController.
   - Create list.html and edit.html with required form controls, validation, and UX states.
   - Bind CurriculumEditController to CampusService to default campus_id on Add; allow dropdown change; listen to campusChanged if needed.
6) Manual verification:
   - Exercise CRUD flows in UI; verify payloads and responses.
7) Smoke tests:
   - Run curriculum-smoke script and confirm pass.
8) Logs verification:
   - Verify SystemLog records for create, update, delete, addSubject, removeSubject actions (spot-check).
