# Implementation Plan

[Overview]
Add a new Class Records import/export feature for tb_mas_classlist_student, mirroring the subjects import pattern, and surface it on the Students list page with its own template download and import workflow; also add two new columns credited_subject_name (string) and is_credited_subject (boolean) to tb_mas_classlist_student.

This feature lets Registrar/Admin bulk upsert student-classlist rows by identifying each record using (sectionCode + subjectCode + term string + campus name) for the classlist and student_number for the student. It includes a backend service to build a spreadsheet template and parse uploads, a controller exposing GET/POST endpoints, and UI wiring in Students page to download/import, with a dedicated status/summary panel separate from the existing Students import.

[Types]  
Add two DB columns to tb_mas_classlist_student and type cast in the model (optional): is_credited_subject as boolean/tinyint (0/1), credited_subject_name as string (nullable).

- Database
  - Table: tb_mas_classlist_student
    - is_credited_subject: tinyint(1) NOT NULL default 0
    - credited_subject_name: varchar(255) NULL default NULL

- Model (optional casting for convenience)
  - App\Models\ClasslistStudent::$casts
    - 'is_credited_subject' => 'boolean'

[Files]
Introduce a dedicated import service/controller/export, migration for new columns, and front-end additions on the Students feature.

- New files to be created:
  - laravel-api/app/Http/Controllers/Api/V1/ClasslistStudentImportController.php
    - Purpose: Expose /class-records/import/template (GET) and /class-records/import (POST) endpoints following the same semantics as SubjectImportController.
  - laravel-api/app/Exports/ClasslistStudentTemplateExport.php
    - Purpose: Thin wrapper to generate the Spreadsheet template via the service.
  - laravel-api/app/Http/Requests/Api/V1/ClasslistStudentImportRequest.php
    - Purpose: Validate multipart upload (file required) and optional dry_run.
  - laravel-api/app/Services/ClasslistStudentImportService.php
    - Purpose: Build template, parse .xlsx/.xls/.csv, normalize rows, resolve foreign keys (student, subject, campus, term), and upsert tb_mas_classlist_student with safe defaults.
  - laravel-api/database/migrations/2025_09_06_000500_add_credited_fields_to_tb_mas_classlist_student.php
    - Purpose: Add is_credited_subject and credited_subject_name to tb_mas_classlist_student.
  - assets/excel/class-records-import-sample.csv (optional)
    - Purpose: Provide a quick sample for manual testing and documentation.

- Existing files to be modified:
  - laravel-api/routes/api.php
    - Add:
      - GET /api/v1/class-records/import/template (role: registrar,admin)
      - POST /api/v1/class-records/import (role: registrar,admin)
  - frontend/unity-spa/features/students/students.service.js
    - Add methods:
      - downloadClassRecordsTemplate() -> GET /class-records/import/template, returns { data, filename }
      - importClassRecords(file, opts) -> POST /class-records/import
  - frontend/unity-spa/features/students/students.controller.js
    - Add controller state and handlers:
      - vm.crImporting, vm.crImportError, vm.crImportSummary
      - vm.downloadClassRecordsTemplate(), vm.openClassRecordsImportDialog(), vm.onCRFileSelected(), vm.runCRImport()
  - frontend/unity-spa/features/students/students.html
    - Add a new separate pair of buttons:
      - Download Class Records Template
      - Import Class Records
    - Add second hidden file input #classRecordsImportFile
    - Add separate status/summary panel for class records import.
  - laravel-api/app/Models/ClasslistStudent.php (optional)
    - Add $casts for 'is_credited_subject' => 'boolean' (non-breaking).

- Files to be deleted or moved
  - None

- Configuration file updates
  - None required.

[Functions]
Add parsing, normalization, and upsert functions; add UI controller functions to drive import.

- New functions
  - laravel-api/app/Services/ClasslistStudentImportService.php
    - generateTemplateXlsx(): Spreadsheet
      - Build header sheet and notes for class-records import.
    - parse(string $path, string $ext): \Generator<['line' => int, 'data' => array]>
      - Read .xlsx/.xls/.csv; header row is case-insensitive.
    - normalizeRow(array $row): array [$cols, $keys]
      - Map inputs to normalized columns and keys for resolution.
    - upsertRows(iterable $rows, bool $dryRun = false): array
      - Resolve student, subject, campus, term, then find classlist by (term + sectionCode + subjectId [+ campus_id if present]).
      - Insert or update tb_mas_classlist_student with defaults and new credited fields.
    - resolveSubjectIdByCode(string $code): ?int
    - resolveCampusIdByName(string $name): ?int
    - resolveOrCreateTermByString(string $term, ?int $campusId): ?int
      - Follows ClasslistImportService approach to parse "1st 2025-2026 college".
    - toIntOrNull($v): ?int
    - toIntInSet($v, array $allowed, int $default): int
  - laravel-api/app/Http/Controllers/Api/V1/ClasslistStudentImportController.php
    - template(Request $request)
    - import(ClasslistStudentImportRequest $request): JsonResponse
  - frontend/unity-spa/features/students/students.service.js
    - downloadClassRecordsTemplate()
    - importClassRecords(file, opts)
  - frontend/unity-spa/features/students/students.controller.js
    - downloadClassRecordsTemplate()
    - openClassRecordsImportDialog()
    - onCRFileSelected(files)
    - runCRImport()

- Modified functions
  - None of the existing imports; only additive changes.

- Removed functions
  - None

[Classes]
Introduce new classes around import; optionally modify ClasslistStudent model for casts.

- New classes
  - App\Http\Controllers\Api\V1\ClasslistStudentImportController
    - Methods: template(), import()
  - App\Exports\ClasslistStudentTemplateExport
    - Methods: build(): Spreadsheet
  - App\Http\Requests\Api\V1\ClasslistStudentImportRequest
    - Methods: rules(), authorize()
  - App\Services\ClasslistStudentImportService
    - Methods as listed above

- Modified classes
  - App\Models\ClasslistStudent (optional)
    - Add protected $casts = ['is_credited_subject' => 'boolean']

- Removed classes
  - None

[Dependencies]
No new composer or npm dependencies.

- PHP/Composer
  - phpoffice/phpspreadsheet is already present and used by existing imports.
- Node/NPM
  - None

[Testing]
End-to-end verification across backend and frontend flows.

- Migration
  - Run migration; verify columns exist:
    - is_credited_subject (tinyint, default 0)
    - credited_subject_name (nullable varchar)
- Backend import
  - Prepare a sample spreadsheet with:
    - sectionCode: MMA11
    - subjectCode: FUNDPROG
    - term: 1st 2025-2026 college
    - campus: Cebu
    - student_number: 20230001
    - is_credited_subject: 1
    - credited_subject_name: Intro to Programming (Credited)
  - GET /api/v1/class-records/import/template returns .xlsx (200, correct headers)
  - POST /api/v1/class-records/import (multipart) returns JSON summary with inserted/updated/skipped/errors; ensure dry_run=1 path counts would-insert/update without DB writes.
  - Validate deduplication: an existing tb_mas_classlist_student row for (student, classlist) should update instead of reinsert.
- Frontend
  - Students page displays a new pair of buttons with independent status/summary panel.
  - Download button downloads the template (filename from headers, default fallback).
  - Import flow shows progress, success summary (Total, Inserted, Updated, Skipped) and up to 50 errors.
- Edge cases
  - Ambiguous or not found subject/campus/term produces SKIPPED with clear error codes/messages.
  - Missing required fields (sectionCode, subjectCode, term, campus, student_number) produce SKIPPED.

[Implementation Order]
Start with schema changes, then backend service/controller/routes, then frontend wiring, then testing.

1) Migration: add credited_subject_name and is_credited_subject to tb_mas_classlist_student.
2) Backend service: ClasslistStudentImportService with template generation, parsing, normalization, resolution, upsert.
3) Export and Request classes: ClasslistStudentTemplateExport and ClasslistStudentImportRequest.
4) Controller: ClasslistStudentImportController with template()/import() endpoints.
5) Routes: add GET/POST /class-records/import/* with registrar,admin middleware.
6) Frontend: StudentsService methods; StudentsController state/handlers; Students HTML buttons + input + summary panel.
7) Optional: Model cast on ClasslistStudent.
8) Manual testing: migration, API endpoints, UI flow.
9) Add sample CSV (optional) to assets/excel for quick testing reference.
