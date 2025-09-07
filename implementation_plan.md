# Implementation Plan

[Overview]
Add an import/export feature for Student Class Records (tb_mas_classlist_student) modeled after the existing Subjects import. The feature includes new DB fields (credited_subject_name, is_credited_subject), backend endpoints for template download and file upload, and UI controls on the Students list page to download/import these class records separately from the existing Students import.

Student Class Records will be upserted by uniquely resolving the target classlist using (sectionCode + subjectCode + term string + campus name) and the target student using student_number. This mirrors proven import patterns (template builder, parser, normalizer, upsert, dry_run) already used across the project for Subjects/Classlists/School Years and integrates cleanly with existing roles and middleware.

[Types]  
Database columns are extended to carry credited-subject flags; Eloquent model gets an optional cast for boolean semantics.

- Database: tb_mas_classlist_student
  - is_credited_subject: tinyint(1) NOT NULL DEFAULT 0
  - credited_subject_name: varchar(255) NULL DEFAULT NULL

- Model: App\Models\ClasslistStudent (optional)
  - $casts:
    - is_credited_subject => boolean

[Files]
Create new backend import surfaces and wire new UI actions into Students feature. Routes are added under /api/v1.

- New files to be created:
  - laravel-api/app/Http/Controllers/Api/V1/ClasslistStudentImportController.php
    - Purpose: Provide GET /class-records/import/template and POST /class-records/import for Registrar/Admin.
  - laravel-api/app/Exports/ClasslistStudentTemplateExport.php
    - Purpose: Thin wrapper around the service to build the spreadsheet template.
  - laravel-api/app/Http/Requests/Api/V1/ClasslistStudentImportRequest.php
    - Purpose: Validate multipart upload and optional dry_run flag.
  - laravel-api/app/Services/ClasslistStudentImportService.php
    - Purpose: Build template; parse xlsx/xls/csv; normalize inputs; resolve student, subject, campus, and term; upsert tb_mas_classlist_student.
  - laravel-api/database/migrations/2025_09_06_000500_add_credited_fields_to_tb_mas_classlist_student.php
    - Purpose: Add is_credited_subject and credited_subject_name columns.

- Existing files to be modified:
  - laravel-api/routes/api.php
    - Add new routes (role: registrar,admin):
      - GET /api/v1/class-records/import/template
      - POST /api/v1/class-records/import
  - frontend/unity-spa/features/students/students.service.js
    - Add:
      - downloadClassRecordsTemplate()
      - importClassRecords(file, opts)
  - frontend/unity-spa/features/students/students.controller.js
    - Add controller state and handlers:
      - vm.crImporting, vm.crImportError, vm.crImportSummary
      - vm.downloadClassRecordsTemplate(), vm.openClassRecordsImportDialog(), vm.onCRFileSelected(), vm.runCRImport()
  - frontend/unity-spa/features/students/students.html
    - Add separate controls:
      - “Download Class Records Template” button
      - “Import Class Records” button
      - Hidden file input #classRecordsImportFile
      - Independent status/summary block
  - laravel-api/app/Models/ClasslistStudent.php (optional)
    - Add $casts = ['is_credited_subject' => 'boolean']

- Files to be deleted or moved:
  - None

- Configuration updates:
  - None (PhpSpreadsheet already used in repo; no NPM changes).

[Functions]
Introduce service methods for template generation, parsing, normalization, and upsert while exposing controller methods for routes. UI gets dedicated functions for class records import.

- New functions
  - App\Services\ClasslistStudentImportService
    - generateTemplateXlsx(): Spreadsheet
      - Sheet: “class-records” with headers; “Notes” with instructions.
    - parse(string $path, string $ext): \Generator<[line:int,data:array]>
      - Read xlsx/xls/csv (header row, lowercased map).
    - normalizeRow(array $row): array [$norm, $keys]
      - Map inputs to normalized db columns + keys for lookup.
    - upsertRows(iterable $rows, bool $dryRun=false): array
      - Resolve student (by student_number).
      - Resolve subject (by subjectCode).
      - Resolve campus (by name, case-insensitive); returns campus_id or null.
      - Resolve or create term from string using ClasslistImportService approach (“1st 2025-2026 college”).
      - Find classlist by (term_id + sectionCode + subjectId) and optionally campus_id if present on classlist; skip when not found.
      - Upsert tb_mas_classlist_student by (intStudentID + intClassListID).
      - Safely write credited_subject_name, is_credited_subject; leave grades untouched; default enumStatus/strRemarks/strUnits as needed.
    - resolveSubjectIdByCode(string $code): ?int
    - resolveCampusIdByName(string $name): ?int
    - resolveOrCreateTermByString(string $term, ?int $campusId): ?int
    - toIntOrNull($v): ?int, toIntInSet($v, array $allowed, int $default): int
  - App\Http\Controllers\Api\V1\ClasslistStudentImportController
    - template(Request $req): streamDownload (.xlsx)
    - import(ClasslistStudentImportRequest $req): JsonResponse (inserted/updated/skipped/errors, dry_run support)
  - Frontend additions
    - StudentsService.downloadClassRecordsTemplate()
    - StudentsService.importClassRecords(file, opts)
    - StudentsController.downloadClassRecordsTemplate()
    - StudentsController.openClassRecordsImportDialog()
    - StudentsController.onCRFileSelected(files)
    - StudentsController.runCRImport()

- Modified functions
  - None of existing subject/classlist/student import functions; all changes are additive.

- Removed functions
  - None

[Classes]
New backend classes mirror SubjectImport patterns; ClasslistStudent model optionally gets boolean cast.

- New classes
  - App\Http\Controllers\Api\V1\ClasslistStudentImportController
  - App\Exports\ClasslistStudentTemplateExport
  - App\Http\Requests\Api\V1\ClasslistStudentImportRequest
  - App\Services\ClasslistStudentImportService

- Modified classes
  - App\Models\ClasslistStudent (optional) add $casts for is_credited_subject

- Removed classes
  - None

[Dependencies]
No new dependencies required.

- Composer: phpoffice/phpspreadsheet already present, used by other imports.
- NPM: no changes.

[Testing]
Validate schema, backend endpoints, and UI.

- DB Migration
  - Run migration; check tb_mas_classlist_student has:
    - is_credited_subject (tinyint(1) default 0)
    - credited_subject_name (nullable varchar)
- Service: Template
  - GET /api/v1/class-records/import/template returns application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; filename includes “class-records-import-template.xlsx”.
- Service: Import
  - Prepare sample with required identifiers:
    - sectionCode: MMA11
    - subjectCode: FUNDPROG
    - term: 1st 2025-2026 college
    - campus: Cebu
    - student_number: 20230001
    - is_credited_subject: 1
    - credited_subject_name: Intro to Programming (Credited)
  - POST /api/v1/class-records/import (multipart) returns JSON summary; dry_run=1 counts without writing.
  - Existing (student,classlist) updates; non-existing inserts.
  - Errors produce SKIPPED with clear messages: REQUIRED, STUDENT_NOT_FOUND, SUBJECT_NOT_FOUND, TERM_NOT_FOUND, CLASSLIST_NOT_FOUND, CAMPUS_NOT_FOUND/AMBIGUOUS.
- Frontend
  - New buttons on Students page, with independent importing state and summary display.
  - Template downloads with proper filename; Import posts multipart and displays summary; handles error display (limit to 50 items for view).

[Implementation Order]
Execute schema first, then backend service/controller/routes, then frontend additions, then tests.

1) Migration: add credited_subject_name and is_credited_subject to tb_mas_classlist_student.
2) Service: ClasslistStudentImportService (template, parse, normalize, resolve, upsert).
3) Export + Request classes: ClasslistStudentTemplateExport and ClasslistStudentImportRequest.
4) Controller: ClasslistStudentImportController (template, import).
5) Routes: GET/POST under /api/v1/class-records/import/* with role: registrar,admin.
6) Frontend: Add service/controller/html for Class Records import separate from Students import.
7) Optional: Add $casts to ClasslistStudent model.
8) Manual E2E validation with sample spreadsheet; ensure dry_run path and error reporting work.
