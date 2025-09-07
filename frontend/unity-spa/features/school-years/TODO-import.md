# School Years Import/Template - TODO

This TODO tracks the implementation steps to add Import and Download Template for Terms (tb_mas_sy) on the School Years list page, mirroring the Subjects import workflow.

task_progress Items:
- [x] Step 1: Backend - Create SchoolYearImportService with parse/upsert/template builder
- [x] Step 2: Backend - Create SchoolYearTemplateExport delegating to service buildTemplateSpreadsheet()
- [x] Step 3: Backend - Create SchoolYearImportRequest (validation)
- [x] Step 4: Backend - Create SchoolYearImportController with template() and import() logic
- [x] Step 5: Backend - Wire routes in laravel-api/routes/api.php with role:registrar,admin middleware
- [x] Step 6: Frontend - Update SchoolYearsService with downloadImportTemplate() and importFile()
- [x] Step 7: Frontend - Update SchoolYearsListController with import state + handlers
- [x] Step 8: Frontend - Update features/school-years/list.html to add buttons, file input, and import summary panel
- [ ] Step 9: Manual verification pass (template download, dry run, write import, list refresh)

Details per step:

## Step 1: Backend - SchoolYearImportService
- File: laravel-api/app/Services/SchoolYearImportService.php
- Methods:
  - buildTemplateSpreadsheet(): Spreadsheet
  - parse(string $path, string $ext): iterable<array>
  - upsertRows(iterable $rows, bool $dryRun): array{inserted:int,updated:int,skipped:int,errors:array<{line:int,code?:string,message:string}>}
- Required columns: enumSem, strYearStart, strYearEnd, campus_id, term_label, term_student_type
- Optional columns: midterm_start, midterm_end, final_start, final_end, end_of_submission, start_of_classes, final_exam_start, final_exam_end, viewing_midterm_start, viewing_midterm_end, viewing_final_start, viewing_final_end, endOfApplicationPeriod, reconf_start, reconf_end, ar_report_date_generation, classType, pay_student_visa, is_locked, enumGradingPeriod, enumMGradingPeriod, enumFGradingPeriod, intProcessing, enumStatus, enumFinalized
- Upsert key: (strYearStart, strYearEnd, enumSem, campus_id)

## Step 2: Backend - SchoolYearTemplateExport
- File: laravel-api/app/Exports/SchoolYearTemplateExport.php
- build(): Spreadsheet using service->buildTemplateSpreadsheet()

## Step 3: Backend - SchoolYearImportRequest
- File: laravel-api/app/Http/Requests/Api/V1/SchoolYearImportRequest.php
- Rules: file required|mimes:xlsx,xls,csv; dry_run boolean

## Step 4: Backend - SchoolYearImportController
- File: laravel-api/app/Http/Controllers/Api/V1/SchoolYearImportController.php
- Endpoints:
  - GET /api/v1/school-years/import/template -> stream XLSX (school-years-import-template.xlsx)
  - POST /api/v1/school-years/import -> parse+upsert (supports dry_run)

## Step 5: Backend - Routes
- File: laravel-api/routes/api.php
- Add routes with middleware('role:registrar,admin') for both endpoints.

## Step 6: Frontend - Service
- File: frontend/unity-spa/features/school-years/school-years.service.js
- Add:
  - downloadImportTemplate(): Promise<{ data:ArrayBuffer, filename:string }>
  - importFile(file, { dry_run?: boolean }): Promise<ImportResponse>

## Step 7: Frontend - Controller
- File: frontend/unity-spa/features/school-years/school-years.controller.js
- Add controller state+handlers:
  - vm.importing, vm.importError, vm.importSummary, vm._selectedFile
  - vm.downloadTemplate(), vm.openImportDialog(), vm.onFileSelected(files), vm.import()
- Refresh list on success: vm.search()

## Step 8: Frontend - Template
- File: frontend/unity-spa/features/school-years/list.html
- Add buttons beside New/Refresh:
  - Download Template
  - Import (opens hidden file input)
- Add hidden <input type="file" accept=".xlsx,.xls,.csv">
- Add import status panel mirroring Subjects UI.

## Step 9: Manual Verification (Critical-Path)
- Backend:
  - Verify GET template returns XLSX with correct headers and role gating.
  - Verify POST import (dry-run + write) with required fields and summary counts.
- Frontend:
  - Verify template downloads, import flow works, summary displays, list refreshes.
