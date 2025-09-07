# Implementation Plan

[Overview]
Add a user-friendly multi-select “Add Subjects to Curriculum” feature that allows registrar/admin users to search, filter, and select up to 60 subjects at once, specify default Year Level and Sem with optional per-row overrides, and submit them in one bulk request to the backend, which inserts new curriculum-subject associations or updates existing ones when requested.

This feature complements existing curriculum CRUD and subjects listing by introducing both a dedicated bulk API endpoint on the backend and a panel on the Curriculum Edit page on the frontend. The backend will validate inputs, enforce limits, and summarize results (inserted/updated/skipped/errors). The frontend will provide a searchable, filterable list of subjects with checkboxes and defaults UI controls, streamlining registrar workflows compared to one-by-one additions.

[Types]
Define a typed payload and response contract for bulk add, and augment frontend view models for selections and overrides.

- Backend request JSON (POST /v1/curriculum/{id}/subjects/bulk):
  - update_if_exists: boolean (optional; default false) — when true, update Year/Sem for existing link
  - subjects: array[1..60] of SubjectAddItem (required)
- SubjectAddItem:
  - intSubjectID: int (required; must exist in tb_mas_subjects.intID)
  - intYearLevel: int (required; 1..10)
  - intSem: int (required; 1..3)
- Backend response JSON:
  - success: boolean
  - message: string (optional; summary text)
  - result:
    - inserted: int
    - updated: int
    - skipped: int
    - errors: array of { index:int, subject_id:int|null, message:string }
    - limit: int (echoed max=60)
- Frontend AngularJS VM additions (CurriculumEditController):
  - subjectsSearch: string
  - subjectsFilters:
    - department: string|null
    - program: string|null
  - subjectsPage: int
  - subjectsLimit: int
  - subjectsRows: Array<SubjectRow> (list results)
  - selectedMap: { [subjectId:number]: { selected:boolean, year?:number, sem?:number } }
  - defaultYearLevel: number (1..10)
  - defaultSem: number (1..3)
  - updateIfExists: boolean
  - selectionCount: number (derived, capped at 60)
- SubjectRow (UI shape):
  - intID: number
  - strCode: string
  - strDescription: string
  - strUnits: string|number
  - intLab: number
  - alreadyLinked: boolean (derived from curriculum subjects GET to inform UI)

[Files]
Introduce one new backend Request class, one new backend controller method, one new frontend service method, and UI/Controller additions in the Curriculum Edit feature, plus route registration.

- New files to be created:
  - laravel-api/app/Http/Requests/Api/V1/CurriculumSubjectsBulkRequest.php
    - Purpose: Validate and sanitize the bulk-add payload with enforced limits and ranges.

- Existing files to be modified:
  - laravel-api/routes/api.php
    - Add POST /v1/curriculum/{id}/subjects/bulk route with role:registrar,admin middleware.
  - laravel-api/app/Http/Controllers/Api/V1/CurriculumController.php
    - Add method addSubjectsBulk(CurriculumSubjectsBulkRequest $request, $id).
    - Use DB transactions; insert or update per item as per update_if_exists; collect counters and errors; log changes via SystemLogService.
  - frontend/unity-spa/features/curricula/curricula.service.js
    - Add function addSubjectsBulk(curriculumId:int, payload:{update_if_exists?:boolean, subjects: SubjectAddItem[]}).
  - frontend/unity-spa/features/curricula/curricula.controller.js
    - CurriculumEditController: add state, loaders, selection logic, validation, and submit handler for bulk add.
    - Use SubjectsService.list for searching/filtering; optionally merge with /curriculum/{id}/subjects to set alreadyLinked flags.
  - frontend/unity-spa/features/curricula/edit.html
    - Add “Add Subjects” panel:
      - Search box + department/program filters.
      - Results table with checkbox per subject, plus inline Year/Sem inputs shown/enabled when checked.
      - Default Year Level + Sem controls applied to all selected (with ability to override per-row).
      - “Update if exists” toggle.
      - Selected counter and a disabled state when selection exceeds 60.
      - Submit button wired to vm.submitSubjectsBulk().

- Files to be deleted or moved:
  - None.

- Configuration file updates:
  - None (no new packages).

[Functions]
Add one new backend controller method and one frontend service method. Update the CurriculumEditController with several UI handlers.

- New backend functions:
  - App\Http\Controllers\Api\V1\CurriculumController::addSubjectsBulk(CurriculumSubjectsBulkRequest $request, int $id)
    - Purpose: Bulk add/update curriculum-subject links with summary result.
    - Behavior:
      - Validate curriculum existence.
      - For each subject item, validate subject existence.
      - If link exists:
        - If update_if_exists, update Year/Sem, increment updated.
        - Else skip and increment skipped.
      - If link missing: insert and increment inserted.
      - Collect per-item errors (e.g., invalid subject id, duplicates w/o update flag).
      - Enforce 60 cap at Request level; reject if exceeded.
      - Wrap in DB::transaction() for atomicity (or process per item; transaction recommended).
      - Log via SystemLogService with action 'update' and a compact delta summary.
      - Return JSON {success:true, result:{...}}.
- Modified backend functions:
  - None of existing signatures; add a new method only.

- Removed backend functions:
  - None.

- New frontend functions:
  - CurriculaService.addSubjectsBulk(curriculumId:number, payload:{ update_if_exists?:boolean, subjects: { intSubjectID:number, intYearLevel:number, intSem:number }[] }): Promise<ApiResult>
  - CurriculumEditController:
    - vm.loadCurriculumSubjects(): GET /curriculum/{id}/subjects → cache set for “already linked”.
    - vm.loadSubjects(): SubjectsService.list({ search, department, program, page, limit }) → subjectsRows; set alreadyLinked flags based on cached set.
    - vm.toggleSelect(row): toggle selectedMap[row.intID], prefill year/sem with defaults if selecting.
    - vm.applyDefaultsToSelection(): iterate selectedMap; set year/sem to defaults if not overridden.
    - vm.validateSelection(): ensure 1..60 selected and each has valid year(1..10)/sem(1..3).
    - vm.submitSubjectsBulk(): build payload and POST via CurriculaService.addSubjectsBulk(...); show summary, reload curriculum subjects list, and optionally clear selections.
- Modified frontend functions:
  - None to existing ones aside from adding new methods to the controller.
- Removed frontend functions:
  - None.

[Classes]
Introduce one new Request class for Laravel validation.

- New classes:
  - App\Http\Requests\Api\V1\CurriculumSubjectsBulkRequest extends FormRequest
    - key methods:
      - authorize(): true (route middleware enforces role)
      - rules():
        - 'update_if_exists' => 'sometimes|boolean'
        - 'subjects' => 'required|array|min:1|max:60'
        - 'subjects.*.intSubjectID' => 'required|integer|min:1'
        - 'subjects.*.intYearLevel' => 'required|integer|min:1|max:10'
        - 'subjects.*.intSem' => 'required|integer|min:1|max:3'
      - messages(): specific readable error messages
- Modified classes:
  - None.
- Removed classes:
  - None.

[Dependencies]
No new third-party packages are required.

All logic uses existing Laravel components (DB, validation FormRequest, responses) and AngularJS services already present in the project (SubjectsService).

[Testing]
End-to-end validation via API and UI, with clear acceptance criteria.

- Backend API tests (manual or scripted):
  - Happy Path:
    - POST /v1/curriculum/{id}/subjects/bulk with 2-5 valid items and update_if_exists=false → inserted count matches, updated=0, skipped=0
    - Re-run with same payload and update_if_exists=false → skipped matches prior size
    - Re-run with update_if_exists=true and altered Year/Sem → updated matches prior size
  - Validation:
    - subjects missing/empty → 422
    - subjects length 61 → 422 (limit enforced)
    - out-of-range YearLevel (0/11) or Sem (0/4) → 422
    - non-existing subject id → per-item error, skipped counter incremented, success still true but errors[] not empty
  - Permissions:
    - Route protected by role:registrar,admin; non-privileged requests rejected.
- Frontend UI manual tests:
  - Navigate to /#/curricula/:id/edit (registrar/admin)
  - Interact with Add Subjects panel:
    - Search subjects; apply department/program filters; verify list updates.
    - Select multiple items; ensure default Year/Sem apply and per-row override works.
    - Ensure “selected count” enforces a maximum of 60 (UI blocks beyond 60).
    - Toggle “Update if exists” and confirm behavior by attempting to add duplicates.
    - Submit and observe summary (inserted/updated/skipped/errors).
    - After submission, GET /curriculum/{id}/subjects reflects updates; alreadyLinked flags in UI update accordingly.
- Regression:
  - Ensure existing /v1/curriculum/{id}/subjects and single-add endpoint continue to work unchanged.
  - Curricula list and edit forms unaffected for non-subject fields.

[Implementation Order]
Implement backend route and controller logic first, then wire frontend service and UI, concluding with manual verification.

1) Backend: Add Request class: App/Http/Requests/Api/V1/CurriculumSubjectsBulkRequest.php with validation rules, messages.
2) Backend: Update routes/api.php to register POST /v1/curriculum/{id}/subjects/bulk → CurriculumController@addSubjectsBulk with role:registrar,admin.
3) Backend: Implement CurriculumController::addSubjectsBulk():
   - Validate curriculum existence and payload via FormRequest.
   - Enforce limit (60) and ranges (Year 1..10, Sem 1..3).
   - DB::transaction; for each item:
     - Verify subject existence; on failure, errors[] and continue.
     - Check existing link:
       - If exists and update_if_exists, update Year/Sem.
       - If exists and not update_if_exists, skip.
       - If not exists, insert.
   - Counters: inserted, updated, skipped; Collect errors (with index and subject_id).
   - SystemLogService::log('update', 'Curriculum', id, null, compact delta summary, request).
   - Return structured JSON.
4) Frontend: CurriculaService.addSubjectsBulk(curriculumId, payload) $http.post(BASE + '/curriculum/{id}/subjects/bulk', payload, admin headers).
5) Frontend: CurriculumEditController additions:
   - Load current curriculum subjects once to mark alreadyLinked.
   - Search/list subjects with SubjectsService.list({ search, department, program, page, limit }).
   - Manage selection map, default Year/Sem, overrides, updateIfExists toggle; enforce selection cap (60).
   - Build payload and submit; show result summary; refresh current subjects cache.
6) Frontend: edit.html panel:
   - Header “Add Subjects”
   - Controls: search, department/program filters, default Year/Sem inputs, updateIfExists toggle.
   - Table of subjects with checkbox, subject code/description/units, “Already Linked” indication, and inline Year/Sem inputs shown when checked.
   - Footer: Selected count (max 60), Submit button disabled if invalid.
7) Manual verification across happy paths and edge cases; adjust messaging and minor UX polish as needed.
