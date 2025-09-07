# Implementation Plan

[Overview]
Add import/template parity for Terms (tb_mas_sy) on the School Years list page and Laravel API, mirroring the existing Subjects import workflow.

This work introduces a downloadable Excel template and an import endpoint for School Years (Terms) and wires the same UX into the School Years list page. It reuses existing architectural patterns already used by subjects import: a template export class, an import service that parses xlsx/xls/csv via PhpSpreadsheet, a controller with two endpoints, and AngularJS service/controller/template changes to surface UI actions and import feedback. Campus scoping remains respected; the upsert key is (strYearStart, strYearEnd, enumSem, campus_id).

[Types]
Add explicit request/response and row structure types for School Years import.

- TermRow (tb_mas_sy) required fields:
  - enumSem: string (values like '1st', '2nd', '3rd', '4th', 'Summer')
  - strYearStart: string (YYYY)
  - strYearEnd: string (YYYY)
  - campus_id: number (nullable on table, but required for import per spec)
  - term_label: string (e.g., 'Semester', 'Trimester')
  - term_student_type: string (enum-like: 'college' | 'shs' | 'next' | 'others')

- TermRow optional fields (accepted by import; nullable):
  - midterm_start: string (YYYY-MM-DD)
  - midterm_end: string (YYYY-MM-DD)
  - final_start: string (YYYY-MM-DD)
  - final_end: string (YYYY-MM-DD)
  - end_of_submission: string (YYYY-MM-DD)
  - start_of_classes: string (YYYY-MM-DD)
  - final_exam_start: string (YYYY-MM-DD)
  - final_exam_end: string (YYYY-MM-DD)
  - viewing_midterm_start: string (YYYY-MM-DD)
  - viewing_midterm_end: string (YYYY-MM-DD)
  - viewing_final_start: string (YYYY-MM-DD)
  - viewing_final_end: string (YYYY-MM-DD)
  - endOfApplicationPeriod: string (YYYY-MM-DD)
  - reconf_start: string (YYYY-MM-DD)
  - reconf_end: string (YYYY-MM-DD)
  - ar_report_date_generation: string (YYYY-MM-DD)
  - classType: string
  - pay_student_visa: number (0|1)
  - is_locked: number (0|1)
  - enumGradingPeriod: string
  - enumMGradingPeriod: string
  - enumFGradingPeriod: string
  - intProcessing: number
  - enumStatus: string ('active' | 'inactive' | other)
  - enumFinalized: string

- ImportTemplate (XLSX) header order:
  1) enumSem
  2) strYearStart
  3) strYearEnd
  4) campus_id
  5) term_label
  6) term_student_type
  7) midterm_start
  8) midterm_end
  9) final_start
  10) final_end
  11) end_of_submission
  12) start_of_classes
  13) final_exam_start
  14) final_exam_end
  15) viewing_midterm_start
  16) viewing_midterm_end
  17) viewing_final_start
  18) viewing_final_end
  19) endOfApplicationPeriod
  20) reconf_start
  21) reconf_end
  22) ar_report_date_generation
  23) classType
  24) pay_student_visa
  25) is_locked
  26) enumGradingPeriod
  27) enumMGradingPeriod
  28) enumFGradingPeriod
  29) intProcessing
  30) enumStatus
  31) enumFinalized

- ImportRequest (multipart/form-data):
  - file: .xlsx | .xls | .csv (required)
  - dry_run: boolean (optional; default false)

- ImportResponse JSON:
  - success: boolean
  - result?: {
    - totalRows: number
    - inserted: number
    - updated: number
    - skipped: number
    - errors: Array<{ line: number, code?: string, message: string }>
  }
  - message?: string (on error)

[Files]
Introduce new Laravel files for School Years import and modify the School Years SPA to add buttons and actions.

- New files to be created:
  - laravel-api/app/Http/Controllers/Api/V1/SchoolYearImportController.php
    - Purpose: Expose GET /api/v1/school-years/import/template and POST /api/v1/school-years/import; mirror SubjectImportController semantics.
  - laravel-api/app/Http/Requests/Api/V1/SchoolYearImportRequest.php
    - Purpose: Validate multipart form and inputs (file required; dry_run optional boolean).
  - laravel-api/app/Exports/SchoolYearTemplateExport.php
    - Purpose: Build an XLSX with the column headers (see Types) and sample row(s) if desired.
  - laravel-api/app/Services/SchoolYearImportService.php
    - Purpose: Parse .xlsx/.xls/.csv and upsert rows into tb_mas_sy using unique key (strYearStart, strYearEnd, enumSem, campus_id); return import summary.
  - (Optional, developer aid) laravel-api/scripts/test_school_year_template.php
    - Purpose: Quick dev test to emit the template to stdout or file.

- Existing files to be modified:
  - laravel-api/routes/api.php
    - Add routes:
      - GET /api/v1/school-years/import/template -> SchoolYearImportController@template (middleware: role:registrar,admin)
      - POST /api/v1/school-years/import -> SchoolYearImportController@import (middleware: role:registrar,admin)
  - frontend/unity-spa/features/school-years/school-years.service.js
    - Add:
      - downloadImportTemplate(): Promise<{ data:ArrayBuffer, filename:string }>
      - importFile(file, { dry_run?: boolean }): Promise<ImportResponse>
  - frontend/unity-spa/features/school-years/school-years.controller.js
    - Add controller state and handlers to support template download and import:
      - vm.importing, vm.importError, vm.importSummary, vm._selectedFile
      - vm.downloadTemplate(), vm.openImportDialog(), vm.onFileSelected(files), vm.import()
  - frontend/unity-spa/features/school-years/list.html
    - Add "Download Template" and "Import" buttons in header
    - Add hidden <input type="file" accept=".xlsx,.xls,.csv"> and import feedback section (importing/error/summary), mirroring Subjects list UI.

- Files to be deleted or moved:
  - None.

- Configuration updates:
  - None (PhpSpreadsheet already exists in project via subjects import).

[Functions]
Add new API endpoints and Angular functions; modify School Years controller/template accordingly.

- New functions (Backend):
  - SchoolYearImportController::template(Request): Stream XLSX template download (filename: school-years-import-template.xlsx).
  - SchoolYearImportController::import(SchoolYearImportRequest): Validate file, detect extension, parse via SchoolYearImportService, perform upsert (dry_run supported), return summary.
  - SchoolYearImportService::buildTemplateSpreadsheet(): Spreadsheet
  - SchoolYearImportService::parse(string $path, string $ext): iterable<Row>
  - SchoolYearImportService::upsertRows(iterable $rows, bool $dryRun): array{inserted:int, updated:int, skipped:int, errors:[]}

- New functions (Frontend):
  - SchoolYearsService.downloadImportTemplate()
  - SchoolYearsService.importFile(file, {dry_run?: boolean})
  - SchoolYearsListController:
    - vm.downloadTemplate()
    - vm.openImportDialog()
    - vm.onFileSelected(files)
    - vm.import()

- Modified functions:
  - SchoolYearsListController: add new state fields and ensure vm.search() is invoked after successful import to refresh list.

- Removed functions:
  - None.

[Classes]
Add export/service/controller classes.

- New classes:
  - App\Http\Controllers\Api\V1\SchoolYearImportController
    - Methods: template(), import()
  - App\Http\Requests\Api\V1\SchoolYearImportRequest
    - Rules: file required|mimes:xlsx,xls,csv; dry_run boolean
  - App\Exports\SchoolYearTemplateExport
    - Methods: build(): \PhpOffice\PhpSpreadsheet\Spreadsheet
  - App\Services\SchoolYearImportService
    - Methods: buildTemplateSpreadsheet(), parse(), upsertRows()

- Modified classes:
  - None.

- Removed classes:
  - None.

[Dependencies]
No new external dependencies.

- Reuse PhpSpreadsheet already present (used by subjects import).
- Reuse existing RequireRole middleware for role gating on routes.
- No composer.json changes needed.

[Testing]
Manual and script-based verification patterned after subjects import.

- Backend:
  - Download template: GET /api/v1/school-years/import/template returns 200 with XLSX content and proper Content-Disposition filename.
  - Import (dry run): POST /api/v1/school-years/import with dry_run=1; ensure summary counts and errors reported.
  - Import (write): POST with valid rows; verify tb_mas_sy rows inserted/updated based on key (strYearStart,strYearEnd,enumSem,campus_id). Confirm term_label and term_student_type required; fail when missing.
  - Edge cases: invalid file type, missing required columns, invalid dates (normalize or error with clear messages).

- Frontend:
  - From School Years list page, click Download Template; verify a .xlsx file with correct filename and headers.
  - Import .xlsx with one new row; expect green success panel with summary and list refresh.
  - Import with missing required column; expect red error message.
  - Verify UI on slow network: importing spinner, disabled buttons where appropriate.

[Implementation Order]
Implement backend first, then frontend, then smoke test.

1) Backend: Create SchoolYearImportService with parse/upsert/template builder matching required/optional fields and upsert key.
2) Backend: Create SchoolYearTemplateExport delegating to service buildTemplateSpreadsheet().
3) Backend: Create SchoolYearImportRequest (validation).
4) Backend: Create SchoolYearImportController with template() and import() logic (streamDownload + import summary).
5) Backend: Wire routes in laravel-api/routes/api.php with role:registrar,admin middleware.
6) Frontend: Update SchoolYearsService with downloadImportTemplate() and importFile().
7) Frontend: Update SchoolYearsListController with state + handlers.
8) Frontend: Update list.html to add buttons, hidden input, and summary panel.
9) Test end-to-end:
   - Download template
   - Dry run import
   - Write import
   - Verify list refresh
10) Polish: error messages, filename detection from Content-Disposition, accept .xlsx/.xls/.csv.
