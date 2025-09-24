# Implementation Plan

[Overview]
Add an Admin-only UI page at #/admin/users-import that allows uploading a spreadsheet to upsert tb_mas_users records by strStudentNumber, applying the selected Campus to all rows (overriding any file content). The backend will extend the existing StudentImport pipeline to accept a campus_id override and process .xlsx uploads (keeping existing .xls/.csv support intact).

This implementation reuses the existing StudentImportController and StudentImportService (already supporting template generation, parsing, and upsert-by-student-number) and exposes them through an Admin-focused import page with Campus selection and upload. The change ensures the selected campus_id is uniformly applied to all imported rows as per requirement, without any student number cleanup transformations.

Scope includes:
- Backend: Add campus override handling (campus_id) to import endpoint and service.
- Frontend: New Admin page with Campus selector and upload controls, using existing StudentsService with a small enhancement to pass campus_id to the API.
- Security: Route visibility restricted to Admin role.
- Behavior: Upsert (insert if new, update if existing) by strStudentNumber. Include all columns available except prohibited system columns. No student number cleanup.

[Types]  
Extend import function signatures to carry an optional forced campus override.

Type definitions / payloads:
- Request (multipart/form-data):
  - file: .xlsx (primary), optional support for .xls/.csv
  - dry_run: optional, "1" or "0"
  - campus_id: integer, required by UI; overrides per-row campus assignment

- Result (JSON):
  - success: boolean
  - result: {
      totalRows: int,
      inserted: int,
      updated: int,
      skipped: int,
      errors: Array<{ line: int, student_number: string|null, message: string }>
    }

Validation rules / data constraints:
- strStudentNumber: required, non-empty (deterministic key for upsert)
- campus_id: when provided (UI requires), apply the same campus to all rows (override)
- Columns: include all columns available from tb_mas_users except prohibited (intID, slug, strPass, strReset, timestamps, etc.)
- No normalization/cleanup of strStudentNumber (preserve value as provided)

[Files]
Add one new feature folder for the Admin UI, minimally modify two existing files on backend and one shared StudentsService on frontend.

Detailed breakdown:
- New files:
  - frontend/unity-spa/features/admin/users-import/users-import.html
    - Purpose: Admin UI for Users Import (download template, select campus, upload .xlsx, show result summary).
  - frontend/unity-spa/features/admin/users-import/users-import.controller.js
    - Purpose: Controller wiring for the Admin Users Import page, integrates CampusService and StudentsService.

- Existing files to be modified:
  - frontend/unity-spa/core/routes.js
    - Add route: "/admin/users-import" with requiredRoles: ["admin"].
  - frontend/unity-spa/features/students/students.service.js
    - importFile(file, opts): Append campus_id to FormData when provided (opts.campus_id), preserving existing behavior for callers that omit it.
  - laravel-api/app/Http/Controllers/Api/V1/StudentImportController.php
    - import(): Read campus_id from request and pass to StudentImportService::upsertRows as a new optional parameter.
  - laravel-api/app/Services/StudentImportService.php
    - upsertRows(iterable $rowIter, bool $dryRun = false, ?int $forcedCampusId = null): implement campus override application per row.
    - resolveForeigns(...): Optionally accept and apply $forcedCampusId override, or apply override after resolving meta.

- Files to be deleted or moved:
  - None.

- Configuration file updates:
  - None (existing PhpSpreadsheet setup is sufficient; routes/API already defined).

[Functions]
Introduce new parameters and small logic blocks, no breaking changes for existing callers.

Detailed breakdown:
- New functions:
  - None required.

- Modified functions:
  - frontend/unity-spa/features/students/students.service.js
    - function importFile(file, opts)
      - Signature unchanged.
      - Change: if (opts &amp;&amp; opts.campus_id != null) fd.append('campus_id', String(opts.campus_id));
      - Purpose: Allow UI to pass campus override for the import pipeline.
  - laravel-api/app/Http/Controllers/Api/V1/StudentImportController.php
    - public function import(StudentImportRequest $request): JsonResponse
      - Change: $campusId = $request->input('campus_id'); pass to service: upsertRows($iter, $dryRun, $campusId ? (int)$campusId : null)
      - Purpose: Carry campus override from UI to service.
  - laravel-api/app/Services/StudentImportService.php
    - public function upsertRows(iterable $rowIter, bool $dryRun = false, ?int $forcedCampusId = null): array
      - Change: After normalizeRow(), force $userCols['campus_id'] = $forcedCampusId when not null; ensure this is applied regardless of “Campus” column in file. If $forcedCampusId is provided, skip meta Campus resolution (or override it post-resolution).
      - Purpose: Enforce selected campus across all rows as required.
    - public function resolveForeigns(array &amp;$userCols, array $meta): void
      - Option A: Leave signature; apply $forcedCampusId after resolveForeigns in upsert loop (preferred for minimal change).
      - Purpose: Keep foreign resolution intact while letting upsert override campus_id.

- Removed functions:
  - None.

[Classes]
Small targeted changes to existing classes; no new classes are required.

Detailed breakdown:
- New classes:
  - None.

- Modified classes:
  - App\Http\Controllers\Api\V1\StudentImportController
    - Behavior: Accept campus_id and forward to service.
  - App\Services\StudentImportService
    - Behavior: Upsert logic to apply campus_id override when provided.

- Removed classes:
  - None.

[Dependencies]
No new runtime packages required.

Details:
- Backend already uses phpoffice/phpspreadsheet for .xlsx support (keep).
- Frontend already loads ng-file-upload library globally; the page will use $http and FormData consistent with StudentsService.

[Testing]
End-to-end manual tests via Admin UI and API, with a focus on campus override and upsert idempotence.

Test requirements:
- Provide a small .xlsx with headers from the template:
  - Case 1: Rows without “Campus” column
  - Case 2: Rows with a different “Campus” string; expect override by selected campus_id
- Include:
  - One new strStudentNumber (should insert)
  - One existing strStudentNumber (should update only provided columns)
  - One row missing strStudentNumber (should be skipped with error)
- Confirm:
  - Result JSON tallies inserted/updated/skipped
  - tb_mas_users rows reflect campus_id = selected campus for all processed rows
  - Running import again updates existing without duplicates
- API dry_run:
  - Pass dry_run=1 to confirm counts without writing DB
- Role:
  - Page visible only to Admin; API accepts registrar/admin as currently defined (leave as is).

[Implementation Order]
Implement backend override first, then frontend UI and service pass-through, followed by tests.

1) Backend: StudentImportService::upsertRows
   - Add optional ?int $forcedCampusId param
   - In the per-row loop, after normalizeRow() and before DB write:
     - If $forcedCampusId !== null: set $userCols['campus_id'] = (int)$forcedCampusId
     - Else: leave existing resolveForeigns Campus behavior
   - Keep resolveForeigns() unchanged (apply override after calling it)

2) Backend: StudentImportController::import
   - Read $campusId = $request->input('campus_id')
   - Call $this->service->upsertRows($iter, $dryRun, $campusId ? (int)$campusId : null)

3) Frontend: StudentsService.importFile
   - Append campus_id to FormData when provided via opts.campus_id (do not change callers that omit it)

4) Frontend: Routes
   - Add route "/admin/users-import" with requiredRoles: ["admin"]

5) Frontend: New Admin Users Import page
   - Controller users-import.controller.js:
     - Inject: $scope, $http, APP_CONFIG, CampusService, StudentsService, ToastService?
     - On activate: await CampusService.init(), read CampusService.getSelectedCampus() for default
     - Bind UI: selectedCampus (object), file, dry_run, importing flag, result summary
     - Actions:
       - downloadTemplate(): StudentsService.downloadTemplate()
       - import(): StudentsService.importFile(file, { campus_id: selectedCampus.id, dry_run: vm.dry_run })
   - Template users-import.html:
     - Campus selector (reuse existing campus-selector component, or show selected campus)
     - File input accept=".xlsx" (backend also supports .xls/.csv — optional additions)
     - Dry run checkbox
     - Buttons: Download Template, Import (disabled while importing)
     - Summary/results panel

6) Verification
   - Upload prepared .xlsx, confirm DB effects and summary result
   - Change selected campus and re-upload to observe override
   - Test dry_run, missing student number, and error surfacing
