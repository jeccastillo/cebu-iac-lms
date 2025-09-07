# Implementation Plan

[Overview]
Add a registrar/admin-only Curriculum Import feature that mirrors the existing Students Import UX and backend patterns. On the Curricula list page, add “Download Template” and “Import” actions. The backend will stream a two-sheet .xlsx template and accept .xlsx/.xls/.csv uploads to upsert curricula in tb_mas_curriculum and their subject associations in tb_mas_curriculum_subject.

This implementation introduces a dedicated CurriculumImportController, a CurriculumImportService, and a CurriculumTemplateExport. The import will upsert curriculum rows by the composite identity (strName + Program Code + Campus) and upsert curriculum-subject associations by (curriculum_id + subject_id), updating year level and sem when associations already exist. All operations will be chunked, validated, and summarized with detailed error reporting, following the tested Students Import approach.

[Types]
Type system changes introduce structured payload/summary objects for import operations.

- CurriculumImportResult (JSON returned by import)
  - totalRows: int (rows across both sheets)
  - insertedCurricula: int
  - updatedCurricula: int
  - skippedCurricula: int
  - insertedSubjectLinks: int
  - updatedSubjectLinks: int
  - skippedSubjectLinks: int
  - errors: array of
    - { sheet: string, line: int, key?: string, message: string }
- Template sheets
  - Sheet “curricula” headers:
    - Name (string; required; maps to tb_mas_curriculum.strName)
    - Program Code (string; required; resolves to tb_mas_programs.intProgramID by strProgramCode case-insensitive exact)
    - Campus (string; required; resolves to tb_mas_campuses.id by campus_name case-insensitive exact)
    - Active (0|1; optional default 1; maps to tb_mas_curriculum.active)
    - Enhanced (0|1; optional default 0; maps to tb_mas_curriculum.isEnhanced)
  - Sheet “curriculum_subjects” headers:
    - Curriculum Name (string; required; joins to curriculum by Name + Program Code + Campus)
    - Program Code (string; required)
    - Campus (string; required)
    - Subject Code (string; required; resolves to tb_mas_subjects.intID by strCode case-insensitive exact)
    - Year Level (int 1..10; required; maps to tb_mas_curriculum_subject.intYearLevel)
    - Sem (int 1..3; required; maps to tb_mas_curriculum_subject.intSem)
- Upsert identities
  - Curriculum: unique by (strName + intProgramID + campus_id)
  - Curriculum Subject Link: unique by (intCurriculumID + intSubjectID), with Year Level and Sem updated if link exists

[Files]
Introduce new import components on the backend and minimal enhancements to existing frontend Curricula feature.

- New files:
  - laravel-api/app/Http/Controllers/Api/V1/CurriculumImportController.php
    - Exposes GET /curriculum/import/template (xlsx stream) and POST /curriculum/import (multipart upload)
  - laravel-api/app/Services/CurriculumImportService.php
    - Core logic: build template, parse sheets, resolve FKs, upsert curricula and subject links in chunks, return summary
  - laravel-api/app/Exports/CurriculumTemplateExport.php
    - Thin wrapper around service to construct a Spreadsheet with two sheets and basic formatting
  - laravel-api/app/Http/Requests/Api/V1/CurriculumImportRequest.php
    - Validates uploaded file presence, size, and extension (xlsx|xls|csv)

- Existing files to be modified:
  - laravel-api/routes/api.php
    - Register routes:
      - GET /v1/curriculum/import/template (role: registrar,admin)
      - POST /v1/curriculum/import (role: registrar,admin)
  - frontend/unity-spa/features/curricula/curricula.service.js
    - Add downloadImportTemplate() and importFile(file, opts) methods (mirroring StudentsService)
  - frontend/unity-spa/features/curricula/curricula.controller.js
    - In CurriculaListController, add vm.downloadTemplate(), vm.openImportDialog(), vm.onFileSelected(), vm.runImport(), and summary state
  - frontend/unity-spa/features/curricula/list.html
    - Add header buttons: “Download Template”, “Import”
    - Add hidden file input accept=".xlsx,.xls,.csv"
    - Add small result/summary block to display import results

- Files to delete or move:
  - None

- Configuration updates:
  - None. phpoffice/phpspreadsheet is already installed and used by Students Import.

[Functions]
Add new endpoints and service functions; modify existing frontend to call new endpoints.

- New functions:
  - CurriculumImportController::template(Request $request)
    - Path: laravel-api/app/Http/Controllers/Api/V1/CurriculumImportController.php
    - Purpose: Stream two-sheet .xlsx template via Xlsx writer
  - CurriculumImportController::import(CurriculumImportRequest $request)
    - Path: as above
    - Purpose: Accept multipart upload, delegate to CurriculumImportService, return CurriculumImportResult
  - CurriculumImportService::__construct()
    - Path: laravel-api/app/Services/CurriculumImportService.php
  - CurriculumImportService::generateTemplateXlsx(): Spreadsheet
    - Purpose: Build “curricula” and “curriculum_subjects” sheets with headers and a “Notes” sheet
  - CurriculumImportService::parse(string $path, string $ext): array
    - Purpose: Return associative arrays:
      - [ 'curricula' => Generator<['line'=>int,'data'=>array]>, 'subjects' => Generator<['line'=>int,'data'=>array]> ]
      - XLSX/XLS via PhpSpreadsheet; CSV: support single-sheet imports (assume “curricula” only for CSV)
  - CurriculumImportService::normalizeCurriculumRow(array $row): [array $cols, array $meta]
    - Maps “Name”, “Program Code”, “Campus”, “Active”, “Enhanced” into writeable tb_mas_curriculum columns; meta carries program_code, campus_name, key
  - CurriculumImportService::resolveCurriculumFKs(array&amp; $cols, array $meta): void
    - program_code -> intProgramID, campus_name -> campus_id; throws if unresolved
  - CurriculumImportService::upsertCurricula(iterable $rows, bool $dryRun=false): array
    - Upsert by (strName + intProgramID + campus_id); returns [inserted, updated, skipped, errors[], idMap]
      - idMap: key "{name}|{program_id}|{campus_id}" => intID
  - CurriculumImportService::normalizeSubjectRow(array $row): [array $meta, array $linkCols]
    - meta includes curriculum_identity: { name, program_code, campus_name }; linkCols: { intYearLevel, intSem, subject_code }
  - CurriculumImportService::resolveSubjectFKs(array&amp; $linkCols, array $meta, array $curriculumIdMap): void
    - curriculum_identity => intCurriculumID using idMap (fallback lookups allowed), subject_code => intSubjectID
  - CurriculumImportService::upsertSubjectLinks(iterable $rows, array $curriculumIdMap, bool $dryRun=false): array
    - Upsert into tb_mas_curriculum_subject by (intCurriculumID + intSubjectID). If exists, update intYearLevel and intSem.
  - CurriculumTemplateExport::build(): Spreadsheet
    - Path: laravel-api/app/Exports/CurriculumTemplateExport.php

  - Frontend: CurriculaService additions
    - downloadImportTemplate(): Promise<{data:ArrayBuffer, filename:string}>
      - GET /curriculum/import/template with responseType: arraybuffer, X-Faculty-ID header
    - importFile(file, opts): Promise<{success:boolean, result:CurriculumImportResult}|error>
      - POST multipart to /curriculum/import, supports dry_run flag

  - Frontend: CurriculaListController additions
    - vm.downloadTemplate(), vm.openImportDialog(), vm.onFileSelected(files), vm.runImport()
    - maintains vm.importing, vm.importError, vm.importSummary
    - reloads list after successful import

- Modified functions:
  - None in CurriculumController (import kept as separate controller for SRP).
  - Minor controller/view enhancements only.

- Removed functions:
  - None

[Classes]
New PHP classes to support curriculum import/template features.

- New classes:
  - App\Http\Controllers\Api\V1\CurriculumImportController
    - Methods: template(), import()
  - App\Http\Requests\Api\V1\CurriculumImportRequest (extends FormRequest)
    - Methods: rules(), messages()
    - Validates required file; ext in {xlsx,xls,csv}; sensible max size (e.g., 5–10MB)
  - App\Services\CurriculumImportService
    - Methods listed in Functions section
  - App\Exports\CurriculumTemplateExport
    - Methods: __construct(service), build()

- Modified classes:
  - None (existing CRUD remains unchanged)

- Removed classes:
  - None

[Dependencies]
No new packages required; reuse PhpSpreadsheet from Students Import.

- Ensure phpoffice/phpspreadsheet is present (already used by Students Import).
- No schema changes required; relies on existing:
  - tb_mas_curriculum: intID, strName, intProgramID, active, isEnhanced, campus_id
  - tb_mas_programs: intProgramID, strProgramCode
  - tb_mas_campuses: id, campus_name
  - tb_mas_subjects: intID, strCode
  - tb_mas_curriculum_subject: intID, intCurriculumID, intSubjectID, intYearLevel, intSem

[Testing]
Add smoke coverage and manual UI validation.

- Backend
  - Quick manual cURL or PowerShell tests:
    - GET /v1/curriculum/import/template: returns 200 with XLSX and Content-Disposition filename "curriculum-import-template.xlsx"
    - POST /v1/curriculum/import (multipart):
      - dry_run true first: validate summary counters with no DB writes
      - happy path: 1–2 new curricula rows plus matching curriculum_subjects; confirm inserts/updates and idempotency
      - failures: unknown Program Code, Campus, Subject Code → row-level errors, no partial write for that row
- Frontend
  - Curricula list page:
    - Buttons render and are role-gated by app-level route protections
    - Download Template triggers browser download with correct filename
    - Import: select .xlsx → displays summary (inserted/updated/skipped/errors) and reloads list

[Implementation Order]
Implement backend first to unblock frontend integration, then wire the UI.

1) Backend: Service and Export
   - Create CurriculumImportService with:
     - generateTemplateXlsx()
     - parse()
     - normalizeCurriculumRow(), resolveCurriculumFKs(), upsertCurricula()
     - normalizeSubjectRow(), resolveSubjectFKs(), upsertSubjectLinks()
   - Create CurriculumTemplateExport wrapping service->generateTemplateXlsx()

2) Backend: Request + Controller + Routes
   - Create CurriculumImportRequest (validates file and extension)
   - Create CurriculumImportController with template() and import()
   - Update routes/api.php with:
     - GET /v1/curriculum/import/template → template() [role:registrar,admin]
     - POST /v1/curriculum/import → import() [role:registrar,admin]

3) Frontend: Service
   - Modify features/curricula/curricula.service.js:
     - Add downloadImportTemplate() and importFile(file, opts), mirroring StudentsService

4) Frontend: Controller + View
   - Modify CurriculaListController to add import/template methods and state (importing, importError, importSummary)
   - Update features/curricula/list.html to add:
     - Buttons: “Download Template”, “Import”
     - Hidden file input (#curriculaImportFile) accept=".xlsx,.xls,.csv"
     - Summary panel for import results

5) Manual verification
   - Download template and fill small dataset; run dry_run then actual import
   - Validate curricula appear in list and subject links exist via GET /curriculum/{id}/subjects

6) Optional: Logging
   - Consider integrating SystemLogService within upsert flows for created/updated curricula and subject links (future enhancement if required).
