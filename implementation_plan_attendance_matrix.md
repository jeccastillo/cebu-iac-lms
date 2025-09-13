# Implementation Plan

[Overview]
Deliver a complete attendance matrix workflow that lets faculty download an Excel template with date headers and student rows and then import the same matrix file to update attendance marks (1=present, 0=absent, blank=unset) across a date range for a single classlist and period.

This plan builds on the existing attendance module (per-date and all-dates imports/exports) and leverages the already-present ClasslistAttendanceMatrixTemplateExport to generate a matrix-format spreadsheet. It introduces a corresponding matrix import path that parses the sheet, auto-creates missing dates, and updates rows idempotently. The UI will expose two actions on the Attendance page: Download Matrix Template (with start/end dates and period) and Import Matrix File. Authorization and policies will reuse the existing attendance.classlist.view/edit gates and the header fallbacks for admin/faculty.

[Types]
Minimal type system additions; no DB schema changes are required.

- Enums / Constants
  - period: 'midterm' | 'finals' (required). Already enforced across attendance services; reused here.
  - Sheet name: 'attendance_matrix'.
- DTOs (implicit arrays; PHPDoc for clarity)
  - MatrixParseRow: { intCSID: int, values: Record<YYYY-MM-DD, 0|1|''>, line: int }
  - MatrixUpsertResult: { updated: int, skipped: int, created_dates: int, seeded_rows: int, totalRows: int, errors: Array<{ line: int, code: string, message: string }> }
- Validation Rules
  - Query params for template: start, end must be YYYY-MM-DD; period ∈ {midterm, finals}; require start ≤ end.
  - Import: multipart/form-data with file (.xlsx), and period ∈ {midterm, finals}.
  - Header row on sheet row 1: columns A..D fixed (intCSID, student_number, last_name, first_name), E..N are dates in YYYY-MM-DD; at least one date is required.
  - Cells E2.. end: only 1, 0, or blank accepted (case-insensitive normalization for safety).

[Files]
Introduce two controller endpoints, new import parsing/upsert logic (added to existing service), and frontend wiring. No deletions.

- New files to be created
  - laravel-api/app/Http/Requests/Api/V1/Attendance/AttendanceMatrixImportRequest.php
    - FormRequest to validate matrix import: requires file, period ∈ {midterm, finals}.
  - (Optional) laravel-api/tests/Feature/Api/V1/ClasslistAttendanceMatrixTest.php
    - Feature tests for template and import paths.
- Existing files to be modified
  - laravel-api/routes/api.php
    - Add routes:
      - GET  /api/v1/classlists/{id}/attendance/matrix/template
      - POST /api/v1/classlists/{id}/attendance/matrix/import
  - laravel-api/app/Http/Controllers/Api/V1/ClasslistAttendanceController.php
    - Add methods:
      - templateMatrix(Request $request, int $id)
      - importMatrix(AttendanceMatrixImportRequest $request, int $id)
  - laravel-api/app/Services/ClasslistAttendanceImportService.php
    - Add constants/methods:
      - private const SHEET_NAME_MATRIX = 'attendance_matrix';
      - parseXlsxMatrix(string $path): \Generator
      - upsertMatrix(iterable $rows, int $classlistId, string $period, ?int $actorId = null): array
  - laravel-api/app/Exports/ClasslistAttendanceMatrixTemplateExport.php
    - No breaking changes; ensure build(int $classlistId, string $start, string $end, string $period) matches new controller usage. Minor notes/validation as needed.
  - frontend/unity-spa/features/classlists/classlists.service.js
    - Add:
      - downloadAttendanceMatrixTemplate(classlistId, start, end, period)
      - importAttendanceMatrix(classlistId, file, period)
  - frontend/unity-spa/features/classlists/attendance.controller.js
    - Add UI state and actions:
      - vm.matrixStartDate, vm.matrixEndDate, vm.matrixPeriod
      - vm.downloadMatrixTemplate()
      - vm.onMatrixFileChange()
      - vm.importMatrix()
  - frontend/unity-spa/features/classlists/attendance.html
    - Add form controls:
      - date range inputs (start/end), period select (midterm/finals)
      - buttons: “Download Matrix Template”, “Import Matrix File”
- Files to be deleted or moved
  - None
- Configuration file updates
  - None

[Functions]
Add controller actions for template download and import, parsing/upsert methods in the import service, and frontend wiring. No existing function removals.

- New functions
  - laravel-api/app/Http/Controllers/Api/V1/ClasslistAttendanceController.php
    - templateMatrix(Request $request, int $id)
      - Params (query): start (Y-m-d), end (Y-m-d), period ('midterm'|'finals')
      - Auth: attendance.classlist.view (with header fallbacks)
      - Use ClasslistAttendanceMatrixTemplateExport::build() and stream .xlsx
    - importMatrix(AttendanceMatrixImportRequest $request, int $id)
      - Body: multipart/form-data { file: .xlsx, period: 'midterm'|'finals' }
      - Auth: attendance.classlist.edit (with header fallbacks)
      - Use ClasslistAttendanceImportService::parseXlsxMatrix() + upsertMatrix()
  - laravel-api/app/Services/ClasslistAttendanceImportService.php
    - parseXlsxMatrix(string $path): \Generator
      - Reads sheet 'attendance_matrix'
      - Row 1: discover date columns from E..last; validate YYYY-MM-DD; yield ['line' => $r, 'data' => ['intCSID' => int, 'dates' => Record<date, value>]]
    - upsertMatrix(iterable $rows, int $classlistId, string $period, ?int $actorId = null): array
      - For each row (CSID + map of date => value):
        - For each date in map: ensure attendance_date row exists for (classlist, date, period) via ClasslistAttendanceService::createDate() (idempotent)
        - Update tb_mas_classlist_attendance.is_present and mark metadata
      - Return { updated, skipped, created_dates, seeded_rows, totalRows, errors }
- Modified functions
  - None (existing per-date/all-dates endpoints remain unchanged)
- Removed functions
  - None

[Classes]
No new backend classes required; extend existing service. Frontend controllers remain in the same module.

- New classes
  - None required; add a new FormRequest class for validation.
- Modified classes
  - ClasslistAttendanceController
    - Add templateMatrix and importMatrix actions using existing authorization helper
  - ClasslistAttendanceImportService
    - Add matrix parser and upsert logic alongside existing per-date and all-dates support
- Removed classes
  - None

[Dependencies]
No new external packages; reuse PhpSpreadsheet already in the project.

- Ensure PhpSpreadsheet is available; otherwise rely on composer install already present for exports/imports.
- No composer.json changes expected.

[Testing]
Add backend feature tests and perform manual E2E QA via UI.

- Backend tests (Feature):
  - test_template_matrix_downloads_with_valid_parameters
  - test_matrix_import_updates_existing_dates_and_creates_missing_dates
  - test_matrix_import_validates_period_and_rejects_bad_dates
  - test_matrix_import_forbidden_without_permissions
- Manual UI QA:
  - As assigned faculty/admin, open Attendance page
  - Use date pickers and select period, download template; verify header row shows dates
  - Enter a few 1/0/blank values across several dates, upload; verify date creation happens and rows update
  - Re-import same file to confirm idempotency for unchanged cells (skipped count increases) and changed cells (updated count increases)

[Implementation Order]
Implement controller endpoints and service changes first, then wire up frontend, and finally add tests and QA.

1) Backend routes
   - Add GET /classlists/{id}/attendance/matrix/template and POST /classlists/{id}/attendance/matrix/import to laravel-api/routes/api.php before parameterized dateId routes.
2) Controller actions
   - Implement templateMatrix() and importMatrix() in ClasslistAttendanceController using existing authorized() helper and streaming writer.
3) Import service
   - Add SHEET_NAME_MATRIX constant, parseXlsxMatrix() to read sheet, detect dates (E..), normalize values (1,0,blank), and upsertMatrix() to create/seed dates and update rows atomically.
4) FormRequest
   - Add AttendanceMatrixImportRequest validating period and file; ensure controller uses it.
5) Export
   - Reuse ClasslistAttendanceMatrixTemplateExport::build(); confirm validation messages and notes are aligned with import semantics.
6) Frontend service
   - Implement downloadAttendanceMatrixTemplate() (arraybuffer mode; pass params start, end, period) and importAttendanceMatrix() (multipart/form-data; pass period and file) with admin headers propagation.
7) Frontend controller/template
   - Add UI controls and hook service calls; provide progress/disabled states and minimal error reporting.
8) QA + Tests
   - Add Feature tests; execute manual end-to-end verification.

--------------------------------
Appendix — Code Quality & Maintainability Improvements
--------------------------------
Backend
- Centralize spreadsheet header/value normalization:
  - Introduce small internal helpers in ClasslistAttendanceImportService (e.g., getHeaderMap, readCellString, isValidYmd) to reduce repetition across parseXlsx, parseXlsxWithSheet, parseXlsxAll, parseXlsxMatrix.
- Stronger typing + PHPDoc:
  - Add @return tags for arrays/generators and precise shapes to improve IDE/static analysis.
- Transactions and idempotency:
  - Wrap date creation + seeding in DB transactions inside ClasslistAttendanceService::createDate() if not already transactional.
  - Ensure upsert methods batch updates where possible.
- Input validation:
  - Enforce max file size and MIME type in FormRequest; return clear error messages.
- Error codes consistency:
  - Use consistent error codes across upsert, upsertAll, and upsertMatrix (e.g., INVALID_DATE, INVALID_PERIOD, CSID_NOT_IN_CLASSLIST, CREATE_DATE_FAILED).
- Performance:
  - Preload maps: roster CSIDs and existing date ids into hash maps for O(1) lookups; already done in upsertAll — mirror in upsertMatrix.
  - Optional chunked updates if row counts are high (accumulate update payloads, then issue batched operations).
- Index review:
  - Existing indexes ux_attd_date, ux_attd_date_csid, ix_attd_date, ix_attd_classlist look appropriate. Confirm ANALYZE queries on hot paths.

Frontend
- Service consistency:
  - Use _adminHeaders() everywhere for protected calls; existing code already follows this pattern — mirror for matrix endpoints.
- Date handling:
  - Reuse createAttendanceDate’s toYMD helper – extract into a shared utility if duplicated across controllers.
- UI responsiveness:
  - Disable buttons while uploading/downloading; show progress and clear errors with user-friendly messages.
- DRY for file downloads:
  - Centralize arraybuffer download + filename extraction logic reused by templates (attendance, grades, matrix).

Security
- Maintain Gate checks and header fallbacks consistently.
- Sanitize and validate user inputs; avoid trusting sheet contents beyond declared headers.
