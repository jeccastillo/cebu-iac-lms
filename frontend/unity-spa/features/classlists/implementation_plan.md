# Implementation Plan

[Overview]
Add Classlists import (upload) and download-template features to mirror the existing Subjects implementation. This enables admins/registrars to bulk-create or update tb_mas_classlists via an XLSX/CSV template from the classlists/list.html page, using GET /classlists/import/template and POST /classlists/import.

This work follows the tested Subjects pattern already present in the project. On the frontend, we will add two actions (Download Template, Import) with UX parity to Subjects, including progress/error/success summary. On the backend, we will add a template export and an import service that validates and upserts rows into tb_mas_classlists keyed by intID (if provided) or the (term + sectionCode) pair. We will resolve related references via subjectCode and faculty full name matching. API responses will match the Subjects import shape, including support for dry_run.

[Types]  
Type system changes are limited to structured objects for import summary results and frontend view-model state.

Detailed definitions:
- Frontend JSDoc pseudo-types (AngularJS)
/**
 * @typedef {Object} ClasslistsImportSummary
 * @property {number} totalRows
 * @property {number} inserted
 * @property {number} updated
 * @property {number} skipped
 * @property {Array<{ line:number, code?:string, message:string }>} errors
 */

/**
 * @typedef {Object} ClasslistTemplateRow
 * @property {number} term_id              // Required; selected Term ID (int)
 * @property {string} sectionCode          // Required; unique per term
 * @property {string} subjectCode          // Required; matches tb_mas_subjects.strCode
 * @property {string} facultyName          // Optional; "Lastname, Firstname" or full_name
 * @property {string|number} strUnits      // Optional; defaults from subject when empty
 * @property {number} intFinalized         // Optional; default 0 (0/1/2)
 * @property {number} isDissolved          // Optional; default 0 (0/1)
 * @property {number} [intID]              // Optional; if present, treated as update key
 */

Validation rules and relationships:
- term_id: must reference an existing term (tb_mas_school_year or equivalent; matches intID from TermService)
- sectionCode: non-empty; unique within a term; used for upsert when intID is absent
- subjectCode: must resolve to a subject (tb_mas_subjects.strCode)
- facultyName: optional; if provided, must resolve uniquely to a faculty record; ambiguity or not-found is an error
- intFinalized: enum {0,1,2}; default 0 when omitted or blank
- isDissolved: enum {0,1}; default 0 when omitted or blank
- strUnits: optional numeric; if blank/null, derive from subject default units; store as string/number consistent with existing column type
- intID: optional; when provided, update that row; when absent, upsert by (term_id + sectionCode)

[Files]
The following files will be created or modified to add import and template capabilities for Classlists.

Detailed breakdown:
- New files (backend)
  - laravel-api/app/Http/Controllers/Api/V1/ClasslistImportController.php
    - Purpose: Expose GET /classlists/import/template and POST /classlists/import; use middleware role:registrar,admin; delegate to export/service layers
  - laravel-api/app/Services/ClasslistImportService.php
    - Purpose: Parse spreadsheet/CSV, validate rows, resolve subject/faculty references, perform dry run vs persisted upsert; return standardized summary
  - laravel-api/app/Exports/ClasslistTemplateExport.php
    - Purpose: Build XLSX using PhpSpreadsheet similar to SubjectTemplateExport with header columns and sample data/notes

- Existing files to be modified
  - laravel-api/routes/api.php
    - Add routes:
      - GET /api/v1/classlists/import/template → ClasslistImportController@template → middleware role:registrar,admin
      - POST /api/v1/classlists/import → ClasslistImportController@import → middleware role:registrar,admin
  - frontend/unity-spa/features/classlists/list.html
    - Add two buttons (Download Template, Import) and a hidden file input, plus summary area like Subjects
  - frontend/unity-spa/features/classlists/classlists.controller.js
    - Add UI state and handlers: downloadTemplate, openImportDialog, onFileSelected, runImport with summary/errors; follow Subjects pattern
  - frontend/unity-spa/features/classlists/classlists.service.js
    - Add functions: downloadImportTemplate() and importFile(file, { dry_run }) mirroring SubjectsService methods; parse Content-Disposition for filename; pass X-Faculty-ID and X-User-Roles headers

- Files to be deleted or moved
  - None

- Configuration file updates
  - None expected; reuse existing PhpSpreadsheet dependency and API base config

[Functions]
New and modified functions to implement Classlists import/template in parity with Subjects.

Detailed breakdown:
- New frontend functions
  - In classlists.service.js
    - downloadImportTemplate(): Promise<{ data:ArrayBuffer, filename:string }>
      - GET {BASE}/classlists/import/template with responseType: 'arraybuffer'; parse Content-Disposition for filename
    - importFile(file: File, opts: { dry_run?: boolean }): Promise<any>
      - POST {BASE}/classlists/import with FormData('file'); optional 'dry_run' (0/1)
  - In classlists.controller.js
    - downloadTemplate(): void
      - Calls service.downloadImportTemplate(), triggers browser download; sets importError on failure
    - openImportDialog(): void
      - Resets import state; opens hidden file input; binds onchange → onFileSelected
    - onFileSelected(files: FileList): void
      - Stores first file in vm._selectedFile; triggers runImport() if present
    - runImport(): void
      - Sets vm.importing; calls service.importFile(); parses result → vm.importSummary; sets vm.importError on failure; refresh list

- Modified frontend functions
  - classlists.controller.js
    - reload(): unchanged; invoked after successful import to refresh table

- New backend functions
  - ClasslistImportController
    - template(): StreamedResponse (xlsx)
    - import(Request): JsonResponse with shape { success: boolean, result: ClasslistsImportSummary, message?: string }
  - ClasslistTemplateExport
    - build(): Spreadsheet
    - toResponse(): StreamedResponse with appropriate headers and filename "classlists-import-template.xlsx"
  - ClasslistImportService
    - parse(file): iterable rows with (line, values)
    - validateAndNormalize(row): normalized ClasslistTemplateRow with resolved subject/faculty IDs; collect errors with codes
    - upsert(rows, opts): returns ClasslistsImportSummary; dry_run → no DB writes
    - resolveSubjectIdByCode(code): int|null
    - resolveFacultyIdByName(name): int|null with disambiguation rules
    - findExistingByIntIdOrTermSection(intID, term_id, sectionCode): model|null

- Removed functions
  - None

[Classes]
Define and integrate backend classes consistent with existing imports (Subjects).

Detailed breakdown:
- New classes
  - App\Http\Controllers\Api\V1\ClasslistImportController
    - Methods: template(), import()
    - Dependencies: App\Exports\ClasslistTemplateExport, App\Services\ClasslistImportService, Illuminate\Http\Request
    - Inheritance: extends Controller
  - App\Services\ClasslistImportService
    - Key methods: parse(), validateAndNormalize(), upsert(), resolveSubjectIdByCode(), resolveFacultyIdByName(), findExistingByIntIdOrTermSection()
    - Uses: PhpSpreadsheet for parsing; Eloquent/DB for lookups and upserts
  - App\Exports\ClasslistTemplateExport
    - Methods: __construct(service?), build(), toResponse()
    - Uses: PhpSpreadsheet; provides headers, sample row, and notes

- Modified classes
  - None (no existing classlists import classes exist today)

- Removed classes
  - None

[Dependencies]
No new external dependencies required; reuse existing PhpSpreadsheet that powers Subjects and SchoolYear templates/imports.

Details:
- Composer: phpoffice/phpspreadsheet already present (used by SubjectTemplateExport and SubjectImportService)
- Frontend: no new NPM packages; reuse AngularJS + $http

[Testing]
Validation and integration tests mirroring Subjects import.

Test coverage:
- Backend
  - Unit tests for ClasslistImportService.validateAndNormalize():
    - resolves subject by subjectCode (case-insensitive, trims)
    - resolves faculty by facultyName with formats:
      - "Lastname, Firstname"
      - exact full_name match
      - ambiguity → error code FACULTY_AMBIGUOUS
      - not found → error code FACULTY_NOT_FOUND
    - defaults: intFinalized=0, isDissolved=0, strUnits from subject if missing
    - rejects invalid enums / missing mandatory columns
  - Service upsert() dry-run vs persist behavior; upsert by intID or (term_id+sectionCode)
  - Controller responses and headers for template download
- Frontend
  - Manual/Smoke:
    - Buttons appear on Classlists list page
    - Download Template triggers file save with correct filename
    - Import flow: selects file, displays Importing..., then shows summary or error
    - After success, table refreshes via reload()
  - API contract:
    - Response shape equals Subjects summary: { success, result: { totalRows, inserted, updated, skipped, errors[] } }

[Implementation Order]
Implement from backend to frontend to ensure endpoints exist before UI integration.

Numbered steps:
1) Backend: Create ClasslistImportService with parse/validate/upsert and resolution helpers; write core logic and unit tests where applicable.
2) Backend: Create ClasslistTemplateExport to build XLSX with headers:
   - term_id, sectionCode, subjectCode, facultyName, strUnits, intFinalized, isDissolved, intID (optional)
   - Include a "Notes" sheet with instructions and allowed values (intFinalized: 0/1/2; isDissolved: 0/1).
3) Backend: Create ClasslistImportController with template() and import() methods delegating to the service; implement dry_run support via request param; return standardized JSON.
4) Backend: Update laravel-api/routes/api.php to register:
   - GET /api/v1/classlists/import/template → template (role:registrar,admin)
   - POST /api/v1/classlists/import → import (role:registrar,admin)
5) Frontend: Update frontend/unity-spa/features/classlists/classlists.service.js to add:
   - downloadImportTemplate()
   - importFile(file, { dry_run })
   - Use X-Faculty-ID and propagate X-User-Roles as done in ClasslistsService._adminHeaders.
6) Frontend: Update classlists.controller.js to add UI state and handlers:
   - importing, importError, importSummary, _selectedFile
   - downloadTemplate(), openImportDialog(), onFileSelected(), runImport()
   - On success, call reload()
7) Frontend: Update classlists/list.html header:
   - Add "Download Template" and "Import" buttons mirroring Subjects UI
   - Add hidden input#classlistsImportFile accept ".xlsx,.xls,.csv"
   - Add alert box rendering Importing..., Errors, and Import Summary (Total/Inserted/Updated/Skipped, Errors list)
8) QA: Manual verification with small sample files (dry_run first); confirm records upsert correctly by intID or (term+sectionCode); verify faculty and subject resolution errors surface in summary.
9) Docs: Add a brief README note under plans/ or features/classlists/ indicating template columns and business rules.

Appendix: Business rules implemented in backend import
- Upsert key:
  - If intID present: update that record (must exist → else error code CLASSLIST_NOT_FOUND).
  - Else: find by (term_id + sectionCode). If found → update; else → insert.
- Field normalization:
  - intFinalized defaults to 0; must be one of 0,1,2.
  - isDissolved defaults to 0; must be 0 or 1.
  - strUnits: if null/blank, use subject units; else coerce numeric/string compatible with DB column.
- Relationships:
  - subjectCode → tb_mas_subjects.strCode (case-insensitive; unique). Not found → error SUBJECT_NOT_FOUND.
  - facultyName (optional):
    - Accepts "Lastname, Firstname" (trim spaces), or exact match to faculty.full_name
    - On multiple matches → error FACULTY_AMBIGUOUS.
    - On not found → error FACULTY_NOT_FOUND (if provided).
  - term_id must exist; not found → error TERM_NOT_FOUND.
- Error reporting:
  - Each row collects at most one primary error; reported with line number and code.
  - Skipped count increments for rows with errors.
- Dry run:
  - When dry_run=1, no database writes; return summary with computed inserts/updates but perform only validation and lookup.
