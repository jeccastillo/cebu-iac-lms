# Implementation Plan

[Overview]
Add a new Laravel API CRUD for tb_mas_classlist (Eloquent: App\Models\Classlist), with system logs recorded on create/update/dissolve, and a guarded dissolve that prevents setting dissolved when related tb_mas_classlist_student records exist. The delete operation follows the legacy behavior: instead of hard-deleting, we set isDissolved=1. The API must also force strClassName, year, strSection, and sub_section to be saved as blank strings (""), never taken from client input.

This CRUD will follow existing API architectural patterns (Program/Campus controllers) and reuse the existing SystemLogService for audit trails. The delete operation will validate referential usage against App\Models\ClasslistStudent. Request validation will explicitly disallow the four restricted fields and the controller will forcibly set them to "" before persistence to guarantee the invariant regardless of client behavior. No database schema changes are required.

[Types]  
No new global type system constructs; new request validation classes and a resource may be added for response shaping.

Detailed structures/specs:
- App\Models\Classlist (exists)
  - Table: tb_mas_classlist, PK: intID
  - Relevant fields that may be accepted/managed by this CRUD (based on legacy schema and usage across services):
    - intSubjectID: int, required; FK to tb_mas_subjects.intID
    - intFacultyID: int, required; FK to tb_mas_faculty.intID
    - strAcademicYear: string, required (term/syid), e.g. numeric or code; max 50
    - strUnits: string|null, optional; max 20
    - intFinalized: int|null, optional; default 0 (if omitted)
    - campus_id: int|null, optional; if provided, integer
    - Restricted fields (must be saved as blank string by backend): strClassName, year, strSection, sub_section
- App\Models\ClasslistStudent (exists)
  - Table: tb_mas_classlist_student, PK: intCSID
  - Relation: intClassListID → tb_mas_classlist.intID

New validation request classes:
- App\Http\Requests\Api\V1\ClasslistStoreRequest
  - Validates payload for create
  - Explicitly forbids strClassName, year, strSection, sub_section as input; they are ignored and overwritten as ""
- App\Http\Requests\Api\V1\ClasslistUpdateRequest
  - Validates payload for update (all fields optional)
  - Same forbidden-field handling as store

[Files]
Add one controller and two request classes, update routes. Optionally add a resource for responses.

Detailed breakdown:
- New files to be created:
  - laravel-api/app/Http/Controllers/Api/V1/ClasslistController.php
    - Purpose: RESTful CRUD for classlists (index, show, store, update, destroy) with logging and delete safety.
  - laravel-api/app/Http/Requests/Api/V1/ClasslistStoreRequest.php
    - Purpose: Validate payload for creating a classlist; restrict forbidden fields.
  - laravel-api/app/Http/Requests/Api/V1/ClasslistUpdateRequest.php
    - Purpose: Validate payload for updating a classlist; restrict forbidden fields.
  - Optional (nice-to-have): laravel-api/app/Http/Resources/ClasslistResource.php
    - Purpose: Normalize output fields and alignment with RegistrarClasslistResource if needed. For MVP, controller may return Eloquent arrays directly to reduce scope.

- Existing files to be modified:
  - laravel-api/routes/api.php
    - Add API v1 endpoints for classlists:
      - GET /api/v1/classlists
      - GET /api/v1/classlists/{id}
      - POST /api/v1/classlists (role: registrar,admin)
      - PUT /api/v1/classlists/{id} (role: registrar,admin)
      - DELETE /api/v1/classlists/{id} (role: registrar,admin)

- Files to be deleted or moved:
  - None.

- Configuration file updates:
  - None.

[Functions]
Add a new controller with five methods; no modifications to core services.

Detailed breakdown:
- New functions:
  - App\Http\Controllers\Api\V1\ClasslistController::index(Request $request)
    - Purpose: List classlists with optional filters (e.g., strAcademicYear, intSubjectID, intFacultyID, finalized). Return JSON list.
  - App\Http\Controllers\Api\V1\ClasslistController::show(int $id)
    - Purpose: Return a single classlist by intID or 404.
  - App\Http\Controllers\Api\V1\ClasslistController::store(ClasslistStoreRequest $request)
    - Purpose: Create classlist; force strClassName/year/strSection/sub_section to ""; set intFinalized default 0 if missing; log via SystemLogService.
  - App\Http\Controllers\Api\V1\ClasslistController::update(ClasslistUpdateRequest $request, int $id)
    - Purpose: Update classlist; forcibly overwrite the four restricted fields to "" regardless of input; log via SystemLogService.
  - App\Http\Controllers\Api\V1\ClasslistController::destroy(int $id)
    - Purpose: Follow legacy dissolve: if no ClasslistStudent rows exist, set isDissolved=1 (soft delete) and log via SystemLogService as an update; if students exist, return 422 and do not modify.

- Modified functions:
  - None in existing controllers/services.

- Removed functions:
  - None.

[Classes]
Add one controller class and two FormRequest classes.

Detailed breakdown:
- New classes:
  - ClasslistController (laravel-api/app/Http/Controllers/Api/V1/ClasslistController.php)
    - Key methods: index, show, store, update, destroy
    - Dependencies: App\Models\Classlist, App\Models\ClasslistStudent, App\Services\SystemLogService, Illuminate\Http\Request
  - ClasslistStoreRequest (laravel-api/app/Http/Requests/Api/V1/ClasslistStoreRequest.php)
    - Extends: Illuminate\Foundation\Http\FormRequest
    - Validates required fields; prohibits restricted fields from being used.
  - ClasslistUpdateRequest (laravel-api/app/Http/Requests/Api/V1/ClasslistUpdateRequest.php)
    - Extends: FormRequest
    - Validates optional fields; prohibits restricted fields from being used.

- Modified classes:
  - None required. App\Models\Classlist and App\Models\ClasslistStudent are sufficient as-is (guarded = [] covers mass assignment).

- Removed classes:
  - None.

[Dependencies]
No new composer dependencies required.

Details:
- Reuse existing App\Services\SystemLogService for action logs.
- Use Illuminate components already included in the Laravel app.

[Testing]
Add feature tests for basic CRUD and guard checks.

Test plan:
- Create (201): POST /api/v1/classlists with required fields only; verify response payload has the four restricted fields as "", intFinalized default 0 when omitted; verify system log row recorded.
- Update (200): PUT /api/v1/classlists/{id} attempting to set restricted fields to non-empty values; ensure they remain "" in DB and system log captured the change of other fields only.
- Read (200/404): GET /api/v1/classlists and GET /api/v1/classlists/{id}; 404 for unknown id.
- Dissolve guard (422): Seed a ClasslistStudent record referencing a classlist; attempt DELETE and expect 422 with message about connected records; verify no change to isDissolved and no success log entry.
- Dissolve success (200): DELETE an unreferenced classlist; verify isDissolved changed from 0 to 1 and system log recorded as update with old_values and new_values reflecting the change.

[Implementation Order]
Implement controller and requests, wire routes, then validate with minimal manual tests or automated tests.

Numbered steps:
1. Create App\Http\Requests\Api\V1\ClasslistStoreRequest with rules:
   - intSubjectID: required|integer|exists:tb_mas_subjects,intID
   - intFacultyID: required|integer|exists:tb_mas_faculty,intID
   - strAcademicYear: required|string|max:50
   - strUnits: sometimes|nullable|string|max:20
   - intFinalized: sometimes|nullable|integer
   - campus_id: sometimes|nullable|integer
   - Explicitly do not require/prohibit strClassName/year/strSection/sub_section; they will be ignored and overwritten as "" in controller.
2. Create App\Http\Requests\Api\V1\ClasslistUpdateRequest with same fields optional (sometimes), and same prohibited/restricted-field note.
3. Create App\Http\Controllers\Api\V1\ClasslistController:
   - index(): allow optional filters like strAcademicYear (term), intSubjectID, intFacultyID, intFinalized; return ordered list.
   - show(): find by intID or return 404.
   - store(): $data = $request->validated(); unset restricted fields if present; force set restricted fields to ""; set intFinalized default 0 if absent; create; SystemLogService::log('create','Classlist',$id,null,$new,$request).
   - update(): load model; $old = toArray(); $data = $request->validated(); always override restricted fields to "" in $data; update; $new = fresh()->toArray(); log update.
   - destroy(): check ClasslistStudent::where('intClassListID',$id)->exists(); if true return 422 JSON {success:false,message:'Cannot dissolve classlist; students exist.'}; else $old = toArray(); update(['isDissolved' => 1]); $new = fresh()->toArray(); log update with old/new.
4. Update laravel-api/routes/api.php to add:
   - GET /api/v1/classlists
   - GET /api/v1/classlists/{id}
   - POST /api/v1/classlists (middleware role:registrar,admin)
   - PUT /api/v1/classlists/{id} (middleware role:registrar,admin)
   - DELETE /api/v1/classlists/{id} (middleware role:registrar,admin)
5. Optional: Create App\Http\Resources\ClasslistResource for consistent responses; initially, return Eloquent arrays for speed.
6. Manual verification using Postman/curl; later add PHPUnit Feature tests covering success/guard cases.
7. Documentation: note that the backend will always blank strClassName/year/strSection/sub_section, so forms should omit them entirely.
