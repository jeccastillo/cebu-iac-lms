# Implementation Plan

[Overview]
Add full CRUD for tb_mas_programs in the Laravel API and provide corresponding AngularJS (1.x) admin UI for listing, creating, editing, and soft-disabling programs, including server-side system logging for all write operations.

This change will allow administrators and registrars to manage academic programs directly via the new Laravel API endpoints, with access restrictions and logging aligned to existing patterns (Subjects, Campus, Roles). The AngularJS frontend will expose a simple management interface under the unity SPA for convenient operations. The system logging service will capture create/update/delete actions, including before/after snapshots and request metadata, for auditability.

[Types]  
Add/clarify program DTO shapes in API responses; no external package-level type system changes.

Detailed data structures:
- Database table: tb_mas_programs (existing legacy table)
  - Primary key: intProgramID (int, auto-increment)
  - Fields in scope:
    - strProgramCode (string, required, unique among enabled programs)
    - strProgramDescription (string, required)
    - strMajor (string, optional)
    - type (enum/string, values used in legacy: college | shs | drive | other; required)
    - school (string, optional; e.g., Computing, Business, Design)
    - short_name (string, optional)
    - default_curriculum (int/bool, optional; treat as integer flag)
    - enumEnabled (tinyint, required; 1 enabled, 0 disabled)
    - campus_id (int, nullable; optional relationship added via migration 2025_08_25_000003_add_campus_id_to_legacy_tables.php)
- Eloquent Model: App\Models\Program
  - $primaryKey = intProgramID
  - $fillable: include all fields above
  - $casts:
    - enumEnabled => integer
    - default_curriculum => integer
    - campus_id => integer
- API response DTOs
  - List/Index (existing mapping for Portal parity):
    - { id: intProgramID, title: strProgramDescription, type: type, strMajor: strMajor }
  - Show (detailed):
    - {
        intProgramID,
        strProgramCode,
        strProgramDescription,
        strMajor,
        type,
        school,
        short_name,
        default_curriculum,
        enumEnabled,
        campus_id
      }
- AngularJS client-side shapes (JSDoc for clarity)
  - Program:
    - id (number)
    - code (string)
    - description (string)
    - major (string)
    - type (string)
    - school (string)
    - short_name (string)
    - default_curriculum (number)
    - enumEnabled (number)
    - campus_id (number|null)

[Files]
Create and modify Laravel API files and add new AngularJS feature files.

Detailed breakdown:
- New files to be created (Laravel)
  - laravel-api/app/Http/Requests/ProgramStoreRequest.php
    - Purpose: Validate create requests for programs.
  - laravel-api/app/Http/Requests/ProgramUpdateRequest.php
    - Purpose: Validate update requests with partial fields.
- Existing files to be modified (Laravel)
  - laravel-api/app/Models/Program.php
    - Add missing fillable fields: school, campus_id
    - Add $casts for enumEnabled, default_curriculum, campus_id
  - laravel-api/app/Http/Controllers/Api/V1/ProgramController.php
    - Add methods: show, store, update, destroy
    - Enhance index with filter/sorting query params: enabledOnly (existing), type?, school?, search?
    - Integrate SystemLogService::log calls for store/update/destroy
  - laravel-api/routes/api.php
    - Add routes:
      - GET /api/v1/programs/{id} (show)
      - POST /api/v1/programs (store) [middleware role:registrar,admin]
      - PUT /api/v1/programs/{id} (update) [middleware role:registrar,admin]
      - DELETE /api/v1/programs/{id} (destroy soft-disable) [middleware role:registrar,admin]
- New files to be created (AngularJS)
  - frontend/unity-spa/features/programs/list.html
    - Purpose: List with filters (enabled/type), actions (add, edit, disable).
  - frontend/unity-spa/features/programs/edit.html
    - Purpose: Form for create/edit program.
  - frontend/unity-spa/features/programs/programs.service.js
    - Purpose: $http wrapper for API endpoints.
  - frontend/unity-spa/features/programs/programs.controller.js
    - Purpose: Controllers for list and edit views.
  - frontend/unity-spa/features/programs/programs.routes.js
    - Purpose: ngRoute routes for /admin/programs and /admin/programs/:id.
- Existing files to be modified (AngularJS)
  - frontend/unity-spa/core/app.module.js
    - Ensure module unityApp includes ngRoute (present) and register new features.
  - If present, frontend/unity-spa/core/app.routes.js
    - Import/ensure routes; if missing, programs.routes.js will self-register with angular.module('unityApp').

[Functions]
Add CRUD controller methods and AngularJS service functions.

Detailed breakdown:
- New functions (Laravel)
  - ProgramController@show(Request $request, int $id): JsonResponse
    - Return one program with full details; 404 if not found.
  - ProgramController@store(ProgramStoreRequest $request): JsonResponse (201)
    - Create program with enumEnabled default 1; SystemLogService::log('create', 'Program', id, null, newValues, $request).
  - ProgramController@update(ProgramUpdateRequest $request, int $id): JsonResponse
    - Update allowed fields; SystemLogService::log('update', 'Program', id, old, new, $request).
  - ProgramController@destroy(int $id): JsonResponse
    - Soft-disable by setting enumEnabled=0; SystemLogService::log('update', 'Program', id, old, new, request()).
- Modified functions (Laravel)
  - ProgramController@index(Request $request): add optional filters (type, school, search on code/description) while preserving enabledOnly default semantics.
- New functions (AngularJS service)
  - ProgramsService.list(params)
  - ProgramsService.get(id)
  - ProgramsService.create(payload)
  - ProgramsService.update(id, payload)
  - ProgramsService.disable(id)
- New functions (AngularJS controllers)
  - ProgramsListController: load filters, fetch list, navigate to edit, disable program.
  - ProgramsEditController: load by id or initialize, save (create/update), navigate back to list.

[Classes]
Introduce Request classes; keep using existing middleware-based access control.

Detailed breakdown:
- New classes
  - App\Http\Requests\ProgramStoreRequest
    - Rules:
      - strProgramCode: required|string|max:50
      - strProgramDescription: required|string|max:255
      - strMajor: nullable|string|max:100
      - type: required|in:college,shs,drive,other
      - school: nullable|string|max:100
      - short_name: nullable|string|max:100
      - default_curriculum: nullable|integer
      - enumEnabled: nullable|integer|in:0,1
      - campus_id: nullable|integer|exists:tb_mas_campuses,id (if campuses table alias differs, validate nullable|integer)
  - App\Http\Requests\ProgramUpdateRequest
    - Same fields as above but all nullable; include uniqueness check for strProgramCode against other programs.
- Modified classes
  - App\Models\Program: expand $fillable and $casts as listed in [Types].
  - App\Http\Controllers\Api\V1\ProgramController: add methods and logging as listed in [Functions].

[Dependencies]
No new Composer or NPM dependencies required.

Integration details:
- Reuse existing role middleware: role:registrar,admin for write routes.
- Reuse existing SystemLogService for logging.
- Continue using default JSON responses aligned with other controllers.

[Testing]
Add feature tests for API and smoke-check frontend integration.

Test requirements:
- Laravel Feature Tests (PHPUnit):
  - tests/Feature/ProgramApiTest.php
    - test_index_default_enabled_only
    - test_filter_by_type_and_search
    - test_show_404_on_missing
    - test_store_validates_and_creates_and_logs
    - test_update_validates_and_logs
    - test_destroy_soft_disables_and_logs
- Manual/E2E checklist:
  - With role=registrar/admin token/session, create program then update fields; verify system-logs endpoint shows entries with old/new values.
  - From AngularJS UI, list programs, filter by type, create/edit/disable, and verify changes via API.
- Optional: stubbed HTTP tests if DB unavailable (skip or mark as integration).

[Implementation Order]
Implement Laravel API first, then AngularJS UI, then tests and verification.

Numbered steps:
1) Laravel model hardening:
   - Update App\Models\Program: add missing fillable fields and casts.
2) Requests:
   - Create ProgramStoreRequest and ProgramUpdateRequest with validation rules.
3) Controller:
   - Extend ProgramController: add show/store/update/destroy; enhance index filters; integrate SystemLogService calls.
4) Routes:
   - Register GET /programs/{id}, POST /programs, PUT /programs/{id}, DELETE /programs/{id} with role:registrar,admin for write routes.
5) AngularJS service and routes:
   - Create programs.service.js and programs.routes.js; wire into unityApp.
6) AngularJS views and controllers:
   - Implement list.html + ProgramsListController; implement edit.html + ProgramsEditController.
7) System logging verification:
   - Exercise create/update/destroy and confirm entries via GET /api/v1/system-logs (admin).
8) Tests:
   - Add Laravel feature tests; run locally with configured DB.
9) Documentation:
   - Update README/TODO to note new endpoints and UI path.
10) Deployment/config sanity:
   - Ensure .env has DB set; verify role middleware active; ensure CORS permits frontend calls.
