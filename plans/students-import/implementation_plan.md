# Implementation Plan

[Overview]
Add an end-to-end Student Import feature that allows registrar/admin to download an Excel template (reflecting tb_mas_users columns with specific substitutions) and upload an Excel/CSV file to insert or update student rows in tb_mas_users, resolving Program Code, Curriculum Code, and Campus name to their primary keys, with strict validation and a detailed results summary.

This feature integrates into the existing Students list page in the AngularJS SPA by adding two actions:
- Download template: provides a generated .xlsx template whose headers are based on tb_mas_users columns, but:
  - Replace intProgramID with Program Code
  - Replace intCurriculumID with Curriculum Code
  - Replace campus_id with Campus (campus_name)
- Import students: accepts .xlsx/.xls/.csv; validates, maps code/name fields to their FKs, and writes to tb_mas_users (insert or update by strStudentNumber).

Business rules and clarifications:
- Roles: registrar and admin can import and download the template (confirmed).
- Template scope: include all columns from tb_mas_users (confirmed). However during import:
  - Primary key (intID) and any computed or system-only fields are ignored.
  - Password/hash fields (e.g., strPass) are ignored for write unless later requested otherwise.
- Duplicates: rows where strStudentNumber already exists are UPDATED (confirmed).
- Program/Curriculum: template carries Program Code and Curriculum Code; importer resolves to tb_mas_programs.intProgramID by tb_mas_programs.strProgramCode, and tb_mas_curriculum.intID by tb_mas_curriculum.strName. If either cannot be resolved for a row, that row is rejected and not written.
- Campus: template carries Campus (name); importer resolves to tb_mas_campuses.id by campus_name (case-insensitive exact match). If not resolved, the row is rejected.
- File types: accept .xlsx, .xls, and .csv (confirmed).
- Strictness: If a row fails any required mapping or hard validation, it is recorded as a failure with a reason, and the row is skipped.

[Types]  
Introduce strongly-typed DTOs and enums (PHP docblocks for clarity) to standardize parsing, validation, and results reporting.

Type definitions:
- class ImportSource
  - string $originalFilename
  - string $extension one of: 'xlsx'|'xls'|'csv'
  - string $path Temporary local path to uploaded file

- class StudentImportRow
  - array $raw Associative array of raw cell values indexed by template header
  - ?string $studentNumber Required; maps to tb_mas_users.strStudentNumber
  - ?string $programCode Optional; maps to tb_mas_programs.strProgramCode
  - ?string $curriculumName Optional; maps to tb_mas_curriculum.strName
  - ?string $campusName Optional; maps to tb_mas_campuses.campus_name
  - array $userColumns Associative array for tb_mas_users updatable columns (keys must be tb_mas_users columns; values normalized)
  - array $warnings Non-fatal notes during normalization
  - int $lineNo 1-based row index within the file (including header offset handling per format)

- class StudentImportResult
  - int $totalRows
  - int $inserted
  - int $updated
  - int $skipped
  - array $errors List<array{line:int, student_number:?string, message:string}>

- enum StudentImportConflictStrategy (doc-only)
  - update_existing (default)  // confirmed by user
  - skip_existing (not used)

Header mapping (template &rarr; model):
- "Program Code" &rarr; tb_mas_users.intProgramID (FK by tb_mas_programs.strProgramCode)
- "Curriculum Code" &rarr; tb_mas_users.intCurriculumID (FK by tb_mas_curriculum.strName)
- "Campus" &rarr; tb_mas_users.campus_id (FK by tb_mas_campuses.campus_name)
- All other columns &rarr; same name as tb_mas_users column headers (best-effort case-insensitive match).

Validation rules (per row):
- strStudentNumber: required, non-empty; used to resolve INSERT vs UPDATE.
- Program Code: if provided, must resolve; else reject row.
- Curriculum Code: if provided, must resolve; else reject row.
- Campus: if provided, must resolve; else reject row.
- All other tb_mas_users columns: optional; if missing, leave unchanged on update; null/blank coerces to NULL where permissible.
- For UPDATE: only columns provided in import are updated (partial update). For INSERT: missing optional columns are set to NULL or defaults.

[Files]
Add a backend controller, service, request, and export generator; update routes; update SPA Students UI and service.

New files:
- laravel-api/app/Http/Controllers/Api/V1/StudentImportController.php
  - REST controller for import endpoints:
    - GET /api/v1/students/import/template
    - POST /api/v1/students/import
  - Role-protected with middleware('role:registrar,admin').

- laravel-api/app/Http/Requests/Api/V1/StudentImportRequest.php
  - Validates uploaded file presence, size limits, and extension in {xlsx, xls, csv}.

- laravel-api/app/Services/StudentImportService.php
  - Orchestrates reading the file, mapping headers, validating rows, resolving FKs, and performing batched inserts/updates.

- laravel-api/app/Exports/StudentTemplateExport.php
  - Generates an .xlsx workbook using PhpSpreadsheet reflecting tb_mas_users columns with the specified header substitutions and basic formatting (bold header row, data types as text to preserve leading zeros).

Modified files:
- laravel-api/routes/api.php
  - Register:
    - GET /api/v1/students/import/template  (role: registrar,admin)
    - POST /api/v1/students/import          (role: registrar,admin)

- frontend/unity-spa/features/students/students.html
  - Add two buttons near Advanced Search actions:
    - Download Template
    - Import (opens file chooser)
  - Add minimal status/summary panel for last import run (counts + link to CSV of errors).

- frontend/unity-spa/features/students/students.controller.js
  - Add functions to call StudentsService for template download and import.
  - Handle upload progress, show results, and refresh list upon success.

- frontend/unity-spa/features/students/students.service.js
  - Add methods:
    - downloadTemplate(): GET arraybuffer
    - import(file): POST multipart/form-data; returns summary.

Files to be created optionally (developer utility and docs):
- plans/students-import/sample_template_notes.md (optional reference for fields, will not be auto-generated)
- A minimal example .xlsx in assets/excel/ (optional, for manual dev testing only; not strictly needed in repo)

[Functions]
Add import/template endpoints and core service methods.

New functions:
- StudentImportController::template(Request $request): \Symfony\Component\HttpFoundation\BinaryFileResponse
  - Purpose: Generate and stream an .xlsx template with headers mapping rules applied.
  - Notes: Uses StudentTemplateExport to build Spreadsheet and stream with correct content-type.

- StudentImportController::import(StudentImportRequest $request): JsonResponse
  - Purpose: Accept multipart file, dispatch to service, return normalized StudentImportResult JSON.

- StudentImportService::__construct()
  - Inject DB and optional logger.

- StudentImportService::buildTemplateColumns(): array
  - Purpose: Introspect tb_mas_users columns and produce ordered header list with substitutions:
    - 'intProgramID' &rarr; 'Program Code'
    - 'intCurriculumID' &rarr; 'Curriculum Code'
    - 'campus_id' &rarr; 'Campus'
    - Exclude PK (intID), timestamp-like non-legacy columns if present, and system-only fields (slug, strPass, tokens).
  - Return: array of strings (headers)

- StudentImportService::generateTemplateXlsx(): \PhpOffice\PhpSpreadsheet\Spreadsheet
  - Purpose: Build Spreadsheet with header row, notes sheet (optional), and basic formatting.

- StudentImportService::parse(ImportSource $src): \Generator<StudentImportRow>
  - Purpose: Yield normalized rows from xlsx/xls via PhpSpreadsheet or csv via fgetcsv.
  - Header mapping is case-insensitive; trims whitespace.

- StudentImportService::resolveForeignKeys(StudentImportRow $row): void
  - Purpose:
    - $row.programCode &rarr; intProgramID via tb_mas_programs.strProgramCode
    - $row.curriculumName &rarr; intCurriculumID via tb_mas_curriculum.strName
    - $row.campusName &rarr; campus_id via tb_mas_campuses.campus_name
  - Throws RowValidationException on failure.

- StudentImportService::normalizeUserColumns(StudentImportRow $row): void
  - Purpose: Produce $row->userColumns array keyed by tb_mas_users columns to write (excluding prohibited columns), mapping special headers, coercing blanks to null where appropriate.

- StudentImportService::upsertRows(iterable $rows, string $conflict = 'update_existing'): StudentImportResult
  - Purpose: In chunks (e.g., 200 rows), resolve and insert or update by strStudentNumber.
  - Insert when strStudentNumber not found; update when found; only provided columns are updated.
  - Wrap chunk in transaction; rollback on unexpected DB error with detailed message.

- StudentTemplateExport::build(array $headers): Spreadsheet
  - Purpose: Render headers and styles into a workbook.

Modified functions:
- frontend/students.controller.js:
  - Add vm.downloadTemplate(), vm.openImportDialog(), vm.onFileSelected(files), vm.runImport().

- frontend/students.service.js:
  - Add downloadTemplate(), import(file).

[Classes]
New classes:
- App\Http\Controllers\Api\V1\StudentImportController
  - Methods: template, import
  - Inheritance: Laravel base Controller

- App\Http\Requests\Api\V1\StudentImportRequest
  - Methods: rules(), messages()
  - Validates: file required, mimetypes/extensions for xlsx/xls/csv, max size (configurable)

- App\Services\StudentImportService
  - Key methods as above; pure logic for template generation, parsing, mapping, upserts.

- App\Exports\StudentTemplateExport
  - Uses PhpSpreadsheet to generate template.

Modified classes:
- None removed or heavily modified. StudentController remains unchanged (import kept separate for SRP).

[Dependencies]
No new Composer packages required; PhpSpreadsheet already present in vendor.
- Use PhpOffice\PhpSpreadsheet for reading/writing .xlsx/.xls.
- For .csv, use native fgetcsv with correct encoding and delimiter detection where possible.
- Optional: use Illuminate\Support\Facades\Log for import summaries.
- Optional: leverage existing SystemLogService (if desired) to log bulk import events with counts; not mandatory.

[Testing]
Adopt a layered testing strategy:
- Service unit tests (if test infra active):
  - StudentImportService::buildTemplateColumns() against a mocked Schema to ensure header substitutions are correct.
  - resolveForeignKeys() with seeded/minimal rows in tb_mas_programs, tb_mas_curriculum, tb_mas_campuses.
  - upsertRows() behavior for insert vs update, partial updates, and error collection.
- Feature/integration tests:
  - POST /api/v1/students/import with small .csv and .xlsx payloads exercising success, duplicates-update, and failures (unresolved codes).
  - GET /api/v1/students/import/template responds with file and correct headers.
- Manual tests:
  - Download template from Students page.
  - Fill 3–5 rows (mix of new and existing strStudentNumber; include one row with wrong Program Code to see a rejection).
  - Upload file; verify summary and that correct rows were inserted/updated.
  - Confirm campus is set by campus_name mapping and program/curriculum IDs resolve.
  - Re-run import to confirm idempotent updates (no duplicate insert for same strStudentNumber).

[Implementation Order]
Implement backend first (template + import), then wire frontend actions and perform tests.

1. Backend: Service & Request
   - Create StudentImportRequest with extension/mime/size validation.
   - Create StudentImportService with:
     - buildTemplateColumns()
     - generateTemplateXlsx()
     - parse() for xlsx/xls/csv
     - resolveForeignKeys()
     - normalizeUserColumns()
     - upsertRows() with transactions and chunking

2. Backend: Controller + Routes
   - StudentImportController::template() uses StudentTemplateExport/Service to stream .xlsx.
   - StudentImportController::import() accepts file and returns StudentImportResult JSON.
   - Register routes in routes/api.php:
     - GET /api/v1/students/import/template (role: registrar,admin)
     - POST /api/v1/students/import (role: registrar,admin)

3. Frontend: Service
   - Update frontend/unity-spa/features/students/students.service.js with:
     - downloadTemplate(): GET arraybuffer; sets Content-Disposition filename: students-import-template.xlsx
     - import(file): POST multipart/form-data, returns { success, result }

4. Frontend: UI/Controller
   - Update students.html:
     - Add buttons “Download Template” and “Import”
     - File input (hidden) bound to controller; show toast/summary; link to error CSV.
   - Update students.controller.js:
     - Implement vm.downloadTemplate(), vm.openImportDialog(), vm.onFileSelected(files), vm.runImport()
     - On success: refresh list via vm.search()

5. Validation & Edge Cases
   - Enforce required mapping:
     - Reject row if Program Code, Curriculum Code, or Campus is present but fails to resolve.
     - student_number required; blank rows skipped with error.
   - Update policy:
     - On duplicate strStudentNumber: perform UPDATE of provided columns only.
   - Ignore columns: intID, slug, strPass, tokens.
   - Add date coercion if present (to Y-m-d) and numeric coercion for year-level-like fields as best-effort.

6. Result Summary & Developer Utilities
   - Return JSON with counts and error list (line, student_number, reason).
   - Provide optional CSV export of errors from the SPA (client-side generation) for easy download.

7. Manual QA
   - Test with .xlsx/.xls/.csv.
   - Verify FK resolution, case-insensitive campus matching, update behavior, pagination refresh, and error messaging.

Notes and assumptions:
- Routes protected for registrar/admin only for both template and import. If later required, template can be exposed publicly.
- If tb_mas_curriculum is actually named differently in the target DB, service will surface a descriptive error (introspection can verify table presence up front).
- Size and timeout limits: default PHP upload limits apply; chunk and stream reading to avoid memory spikes. Recommend chunk size 200.
- Logging: Optional SystemLogService integration to record imports, actor (X-Faculty-ID), counts.
