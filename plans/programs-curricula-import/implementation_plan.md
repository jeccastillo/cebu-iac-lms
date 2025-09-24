# Implementation Plan

[Overview]
Add an Admin-facing bulk import for Programs and Curriculum that mirrors the existing Admin → Users Import: provide XLSX templates, accept .xlsx/.xls/.csv uploads, perform dry-run validation, resolve campus references, and upsert rows with clear, row-level error reporting.

This implementation introduces a backend Program importer (service + controller + request + template export) for tb_mas_programs and an Admin UI for both Programs Import and Curriculum Import. Curriculum import backend already exists (CurriculumImportService, controller, request, export); this plan adds a dedicated Admin UI to drive those endpoints. The Program importer follows the established conventions used by StudentImportService and CurriculumImportService: PhpSpreadsheet-based parsing, dry_run support, consistent error envelope, campus override, and chunked upserts. Per stakeholder guidance, program upsert identity uses intProgramID (primary key), and Default Curriculum is included as a numeric ID without name-based resolution.

[Types]  
Define precise input/output and normalization types for mapper and API payloads.

- Program Import Template Columns (header → db column or special)
  - Program ID → tb_mas_programs.intProgramID (integer; when provided, upsert targets this PK)
  - Program Code → tb_mas_programs.strProgramCode (string; optional, validated for length/non-empty if present)
  - Program Description → tb_mas_programs.strProgramDescription (string; recommended when inserting)
  - Major → tb_mas_programs.strMajor (string; optional)
  - Type → tb_mas_programs.type (string; optional)
  - School → tb_mas_programs.school (string; optional)
  - Short Name → tb_mas_programs.short_name (string; optional)
  - Default Curriculum ID → tb_mas_programs.default_curriculum (integer; optional; validated by existence on tb_mas_curriculum.intID when provided; no string/name-based resolution)
  - Enabled → tb_mas_programs.enumEnabled (0|1; optional; default 1 if blank)
  - Campus → resolves to tb_mas_programs.campus_id by tb_mas_campuses.campus_name (case-insensitive exact)

- Normalization/Validation Rules
  - Trim strings; coerce nullish tokens ('', 'null', 'n/a', 'na', 'none', 'nil', '0000-00-00') to null.
  - Enabled: blank → default 1; when present, coerce to int 0|1.
  - Campus: resolve by campus_name; when resolution fails, error row.
  - Default Curriculum ID: if provided, verify existence in tb_mas_curriculum.intID; on failure, error row.
  - Program ID (intProgramID):
    - If provided and matches an existing row → partial update of provided columns only.
    - If provided and does not exist → insert using provided value if DB allows explicit PK insert; otherwise insert without intProgramID (DB auto-increment). DB behavior will be detected; on failure, error row.
    - If not provided → insert (DB auto-increment).

- API Request Types
  - ProgramImportRequest: multipart/form-data
    - file: required; mimes xlsx|xls|csv; max 10MB
    - dry_run: optional boolean
    - campus_id: optional integer; when provided, overrides Campus column for all rows

- API Response Envelope (Programs Import)
  - success: bool
  - result: {
      totalRows: int,
      inserted: int,
      updated: int,
      skipped: int,
      errors: [ { line: int, program_id?: int|null, message: string } ]
    }

- Frontend View-Model (Programs Import, Curricula Import)
  - vm.file: File|null
  - vm.dry_run: boolean
  - vm.selectedCampus: { id, campus_name }|null
  - vm.importing: boolean
  - vm.error: string|null
  - vm.summary: Programs: { totalRows, inserted, updated, skipped, errors[] }
               Curricula: { totalRows, insertedCurricula, updatedCurricula, skippedCurricula, insertedSubjectLinks, updatedSubjectLinks, skippedSubjectLinks, errors[] }

[Files]
Extend backend with Program importer and add Admin UI pages for Programs/Curricula import.

- New files (Backend)
  - laravel-api/app/Services/ProgramImportService.php
    - PhpSpreadsheet-based template generation/parsing; normalization; FK resolution/validation; upsert.
  - laravel-api/app/Exports/ProgramTemplateExport.php
    - Thin wrapper to build Program template Spreadsheet.
  - laravel-api/app/Http/Controllers/Api/V1/ProgramImportController.php
    - GET /api/v1/programs/import/template (admin/registrar)
    - POST /api/v1/programs/import (admin/registrar)
  - laravel-api/app/Http/Requests/Api/V1/ProgramImportRequest.php
    - Validates file, dry_run, campus_id.

- Existing files to be modified (Backend)
  - laravel-api/routes/api.php
    - Add:
      - Route::get('/programs/import/template', [ProgramImportController::class, 'template'])->middleware('role:registrar,admin');
      - Route::post('/programs/import', [ProgramImportController::class, 'import'])->middleware('role:registrar,admin');

- New files (Frontend)
  - frontend/unity-spa/features/admin/programs-import/programs-import.html
    - UI mirrors Admin → Users Import: Campus selector, file picker, Dry-run, Download Template, Import, summary/errors.
  - frontend/unity-spa/features/admin/programs-import/programs-import.controller.js
    - Controller drives ProgramsService methods; manages campus override and display.
  - frontend/unity-spa/features/programs/programs.service.js (if not existing) OR extend existing Programs service
    - Add:
      - downloadImportTemplate()
      - importFile(file, { dry_run, campus_id })

  - frontend/unity-spa/features/admin/curricula-import/curricula-import.html
    - Similar to programs-import, but uses CurriculaService methods; Campus selector optional (no override required).
  - frontend/unity-spa/features/admin/curricula-import/curricula-import.controller.js
    - Uses CurriculaService.downloadImportTemplate and CurriculaService.importFile.

- Existing files to be modified (Frontend)
  - frontend/unity-spa/core/routes.js
    - Add admin-only routes:
      - /admin/programs-import → programs-import.html / AdminProgramsImportController
      - /admin/curricula-import → curricula-import.html / AdminCurriculaImportController

- Files to be deleted or moved
  - None.

- Configuration updates
  - None required (PhpSpreadsheet already present; API_BASE already used).

[Functions]
Introduce new functions for template, parsing, normalization, resolution, and upsert; modify routes and add FE service methods.

- New functions (Backend)
  - ProgramImportService::generateTemplateXlsx(): Spreadsheet
  - ProgramImportService::parse(string $path, string $ext): \Generator<['line'=>int, 'data'=>array]>
  - ProgramImportService::isEmptyRow(array $row): bool
  - ProgramImportService::normalizeRow(array $row): array [array $cols, array $meta]
    - $cols maps to tb_mas_programs columns
    - $meta includes 'program_id', 'campus_name'
  - ProgramImportService::resolveForeigns(array&amp; $cols, array $meta, ?int $forcedCampusId = null): void
    - Campus name → campus_id resolution; optional forced override
  - ProgramImportService::validateForeignKeyExistence(array $cols): void
    - default_curriculum existence check when provided
  - ProgramImportService::upsertRows(iterable $rowIter, bool $dryRun=false, ?int $forcedCampusId=null): array
    - Chunked upsert by intProgramID when provided; else insert.
  - ProgramImportController::template(Request $request)
  - ProgramImportController::import(ProgramImportRequest $request): JsonResponse

- Modified functions (Frontend)
  - Add to Programs service:
    - downloadImportTemplate(): Promise<{ data:ArrayBuffer, filename:string }>
    - importFile(file, opts): Promise<{ success:bool, result:? }>

- New functions (Frontend controllers)
  - AdminProgramsImportController: downloadTemplate(), openFileDialog(), onFileChanged(e), importFile()
  - AdminCurriculaImportController: downloadTemplate(), openFileDialog(), onFileChanged(e), importFile()

[Classes]
Add one new service class and supporting controller/request/export.

- New classes
  - App\Services\ProgramImportService
    - Methods listed above; similar structure/quality bar as StudentImportService.
  - App\Exports\ProgramTemplateExport
    - build(): Spreadsheet using ProgramImportService.
  - App\Http\Controllers\Api\V1\ProgramImportController
    - Endpoints for template and import.
  - App\Http\Requests\Api\V1\ProgramImportRequest
    - Validation and pre-validation normalization for file/dry_run/campus_id.

- Modified classes
  - None.

[Dependencies]
Reuse existing libraries; no new runtime deps.

- Backend
  - PhpOffice/PhpSpreadsheet (already in composer; used by student/curriculum import)
  - Illuminate DB, Request, Response (existing)

- Frontend
  - AngularJS (existing)
  - Existing CampusService, StorageService

[Testing]
Add backend feature tests and manual FE validation to ensure parity with Users/Curriculum import UX.

- Backend Tests (Feature)
  - tests/Feature/ProgramImportTest.php
    - Template generation returns expected headers and content type.
    - Parse + dry_run: valid rows produce inserted/updated counts, unresolved campus/default_curriculum produce errors.
    - Upsert:
      - Update existing by intProgramID (only provided columns update).
      - Insert when intProgramID missing or non-existent.
      - Campus override applies to all rows.
      - Collect row-level errors without failing entire batch.
- Integration Smoke
  - Upload small CSV/XLSX via API; verify response envelope and db side-effects on non-dry run.
- Frontend Manual
  - Admin → Programs Import: download template, import dry-run with errors, fix and import; verify counters.
  - Admin → Curricula Import: same flows; ensure pages gated by admin role.

[Implementation Order]
Implement backend Program importer first, then wire routes, then frontend pages, then smoke test end-to-end.

1) Backend: Program import core
   - Create ProgramImportService with template, parse, normalize, resolve, upsert.
   - Create ProgramTemplateExport.
   - Create ProgramImportRequest.
   - Create ProgramImportController.
2) API wiring
   - Update routes/api.php to add /programs/import/template and /programs/import endpoints with role:registrar,admin.
3) Frontend: Programs Import page
   - Add programs.service.js methods (or extend existing) for downloadImportTemplate/importFile.
   - Add features/admin/programs-import/{programs-import.html, programs-import.controller.js}.
   - Add /admin/programs-import route in core/routes.js (requiredRoles: ["admin"]).
4) Frontend: Curricula Import page
   - Add features/admin/curricula-import/{curricula-import.html, curricula-import.controller.js}.
   - Wire to CurriculaService.downloadImportTemplate/importFile.
   - Add /admin/curricula-import route (requiredRoles: ["admin"]).
5) QA and refinements
   - Backend feature tests for Program import success/fail paths.
   - Manual FE e2e flows with sample data.
   - Adjust error messages and headers as needed for parity with Students Import UX.
