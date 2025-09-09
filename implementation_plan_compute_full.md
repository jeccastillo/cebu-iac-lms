# Implementation Plan

[Overview]
Add a new boolean column compute_full to tb_mas_scholarships (default true), expose it in the SPA add/edit form, and display a toast success notification after successfully updating a scholarship. The compute_full flag will be included in API payloads and responses so it can be toggled by admins per scholarship/discount.

This change introduces a database column, request validation, resource serialization, service normalization/mapping, and AngularJS view/controller updates. The flag will default to true for new and existing rows via DB default, ensuring backward-compatible behavior. No computation logic change is required in this scope; the flag is being persisted and made configurable in the UI for subsequent features to consume.

[Types]  
Add a single boolean field with validation and resource mapping.

Detailed type specifications:
- Database (MySQL):
  - Table: tb_mas_scholarships
  - Column: compute_full BOOLEAN NOT NULL DEFAULT true
  - Semantics: Indicates whether the scholarship/discount should be computed on full assessment. Defaults to true.
- Backend (Laravel):
  - Validation (store/update):
    - compute_full: sometimes|boolean
  - Model:
    - App\Models\Scholarship remains guarded = []; no change required.
  - Resource mapping:
    - App\Http\Resources\ScholarshipResource includes compute_full in JSON output.
  - Service normalization:
    - App\Services\ScholarshipService::normalizePayload whitelists compute_full.
  - Service mapping (list/mapModel):
    - Include compute_full in returned item arrays for index/show payloads.
- Frontend (AngularJS, unity-spa):
  - Form model:
    - Add vm.form.compute_full (default true).
  - UI:
    - Add a labeled checkbox in the Add/Edit modal: “Compute on Full Assessment”.
    - Help text beneath: “If enabled, this scholarship/discount is computed on full assessment.”
  - Save behavior:
    - Include compute_full in submit payload.
  - Toast:
    - After successful Update (PUT), show ToastService.success('Scholarship updated.'). Do not toast on Create.

[Files]
Add one migration and modify existing backend and frontend files.

Detailed breakdown:
- New files to be created:
  - laravel-api/database/migrations/2025_09_10_000300_add_compute_full_to_tb_mas_scholarships.php
    - Purpose: Add compute_full BOOLEAN NOT NULL DEFAULT true to tb_mas_scholarships.
    - Up: Conditionally add compute_full; place after a nearby column when possible (e.g., after max_stacks or status), otherwise append at end.
    - Down: Conditionally drop compute_full if present.

- Existing files to be modified:
  - laravel-api/app/Http/Requests/Api/V1/ScholarshipStoreRequest.php
    - Add validation rule: 'compute_full' => 'sometimes|boolean'.
  - laravel-api/app/Http/Requests/Api/V1/ScholarshipUpdateRequest.php
    - Add validation rule: 'compute_full' => 'sometimes|boolean'.
  - laravel-api/app/Http/Resources/ScholarshipResource.php
    - Add 'compute_full' to toArray() output with null-safe getter.
  - laravel-api/app/Services/ScholarshipService.php
    - list(): Include 'compute_full' in the mapped return array (cast to boolean when non-null).
    - normalizePayload(): Add 'compute_full' to the $allow whitelist.
    - mapModel(): Include 'compute_full' in normalized API output (boolean when non-null).
  - frontend/unity-spa/features/scholarship/scholarships/scholarships.controller.js
    - State: add vm.form.compute_full with default true in initial state and in openCreate().
    - openEdit(row): prefill vm.form.compute_full from row (default true if missing).
    - submitForm(): include compute_full in payload; after successful update only, call ToastService.success('Scholarship updated.').
  - frontend/unity-spa/features/scholarship/scholarships/list.html
    - In the Add/Edit modal form, add a checkbox for “Compute on Full Assessment” bound to vm.form.compute_full, plus short help text.
    - Optionally show validation error (vm.validation.compute_full) beneath the field if present.

- Files to be deleted or moved:
  - None.

- Configuration file updates:
  - None.

[Functions]
Modify validation, mapping, and form submit behavior; no new endpoints.

Detailed breakdown:
- New functions:
  - None.

- Modified functions:
  - App\Http\Resources\ScholarshipResource::toArray($request)
    - Add 'compute_full' => (bool|null) when present.
  - App\Services\ScholarshipService::list(array $filters = []): array
    - Ensure mapped rows include 'compute_full' => isset($r->compute_full) ? (bool) $r->compute_full : null.
  - App\Services\ScholarshipService::normalizePayload(array $data, ?Scholarship $existing = null): array
    - Add 'compute_full' to $allow whitelist; payload will either set or rely on DB default.
  - App\Services\ScholarshipService::mapModel($m): array
    - Add 'compute_full' => $get('compute_full') !== null ? (bool) $get('compute_full') : null.
  - ScholarshipsController (frontend) methods in scholarships.controller.js:
    - openCreate(): set compute_full = true.
    - openEdit(row): set compute_full = row.compute_full !== false (default true).
    - submitForm(): include compute_full; after update success only, ToastService.success('Scholarship updated.').

- Removed functions:
  - None.

[Classes]
Only existing classes are updated to surface compute_full.

Detailed breakdown:
- New classes:
  - None.
- Modified classes:
  - App\Http\Resources\ScholarshipResource (serialization).
  - App\Http\Requests\Api\V1\ScholarshipStoreRequest (validation).
  - App\Http\Requests\Api\V1\ScholarshipUpdateRequest (validation).
  - App\Services\ScholarshipService (mapping and normalization).
- Removed classes:
  - None.

[Dependencies]
No new dependencies.

Details:
- Uses existing Laravel migration and resource infrastructure.
- Uses existing AngularJS services and ToastService.

[Testing]
Add a small matrix of DB/API/UI tests.

Test requirements and strategies:
- Migration:
  - Run php artisan migrate from laravel-api. Confirm compute_full exists and defaults to 1/true.
  - Ensure existing rows get default true.
- API (via curl/Postman):
  - POST /scholarships with no compute_full sent → expect compute_full in response true (default).
  - POST /scholarships with compute_full=false → expect response data.compute_full === false.
  - PUT /scholarships/{id} with compute_full toggled → response reflects new value.
  - GET /scholarships and GET /scholarships/{id} include compute_full field.
- UI:
  - New: Open Create modal; verify checkbox “Compute on Full Assessment” is checked by default.
  - Edit: Open Edit for an existing scholarship; verify checkbox reflects stored value.
  - Save: Toggle checkbox and Update; verify success toast “Scholarship updated.” appears and list reload reflects change.
  - Create: Save a new scholarship; verify no success toast for create (as specified).
- Validation:
  - Force compute_full with an invalid type (e.g., string) to ensure 422 with validation error shown under field.

[Implementation Order]
Apply DB first, then backend, then UI, and test.

1. Migration
   - Create: laravel-api/database/migrations/2025_09_10_000300_add_compute_full_to_tb_mas_scholarships.php
   - Up: add boolean('compute_full')->default(true) (try after('max_stacks') or after('status') with try/catch; fallback append).
   - Down: dropColumn('compute_full') if exists.
2. Backend validation
   - ScholarshipStoreRequest: add 'compute_full' => 'sometimes|boolean'.
   - ScholarshipUpdateRequest: add 'compute_full' => 'sometimes|boolean'.
3. Backend mapping/normalization
   - ScholarshipResource::toArray: include 'compute_full'.
   - ScholarshipService::normalizePayload: whitelist 'compute_full'.
   - ScholarshipService::mapModel: include 'compute_full'.
   - ScholarshipService::list: include 'compute_full'.
4. Frontend controller
   - scholarships.controller.js:
     - Add vm.form.compute_full defaults and prefill in openEdit.
     - submitForm(): include compute_full in payload.
     - On successful update only, call ToastService.success('Scholarship updated.').
5. Frontend template
   - scholarships/list.html:
     - Add checkbox field:
       - Label: “Compute on Full Assessment”
       - Help text below the input: “If enabled, this scholarship/discount is computed on full assessment.”
       - Bind: ng-model="vm.form.compute_full"
       - Optional validation message: vm.validation.compute_full[0]
6. Migrate and test
   - cd laravel-api &amp;&amp; php artisan migrate
   - Exercise endpoints and SPA behavior as per Testing above.
