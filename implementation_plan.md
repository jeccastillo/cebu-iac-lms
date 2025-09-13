# Implementation Plan

[Overview]
Add per-date Classlist Attendance template download (.xlsx) and file upload (import) so authorized users can bulk-mark attendance for a selected date. This extends the existing attendance module with Excel-based workflows similar to classlist grades import/export, using per-date endpoints and the same authorization fallbacks.

The scope includes:
- Backend: export builder for attendance templates, import service to parse and upsert attendance rows, controller endpoints, and routes.
- Frontend: UI actions to download the template and upload an .xlsx file for the currently selected attendance date, using the existing service pattern with X-Faculty-ID and X-User-Roles headers.

Why needed:
- Mirrors the grades import/export productivity pattern for attendance.
- Allows faculty/admin to quickly prepare, update, and re-import attendance outside the UI, then sync back into the system.
- Keeps consistent authorization and header fallbacks used across this project.

[Types]  
Introduce attendance template export and import types; reuse existing DB tables and attendance rows.

Detailed type specifications:
- Excel Template Format (.xlsx; sheet name: "attendance")
  - Headers (exact, case-insensitive on read):
    - intCSID: string|int; required per row
    - student_number: string; readonly informational
    - last_name: string; readonly informational
    - first_name: string; readonly informational
    - attendance_date: string YYYY-MM-DD; informational, matches selected date
    - period: string "midterm"|"finals"; informational, from attendance date row
    - is_present: string|int|bool|null; editable
      - Allowed input values (case-insensitive; normalized):
        - true values: "1","true","present","p","yes"
        - false values: "0","false","absent","a","no"
        - null/unset: "", "null", "unset"
    - remarks: string|null; optional (only persisted when is_present=false; cleared when is_present=true|null)
- PHP DTOs (FormRequests)
  - AttendanceImportRequest (POST /classlists/{id}/attendance/dates/{dateId}/import)
    - file: required, .xlsx mimetype application/vnd.openxmlformats-officedocument.spreadsheetml.sheet
- Import Result Structure (JSON)
  - { success: true, result: { updated: int, skipped: int, totalRows: int, errors: [ { line, code, message } ] } }
- Authorization
  - Download template: view permission (assigned faculty or admin)
  - Import: edit permission (assigned faculty or admin)
  - Fallback headers honored: X-Faculty-ID and X-User-Roles

[Files]
Add new files for export/import and modify existing controller, routes, and frontend.

Detailed breakdown:
- New files to be created
  - laravel-api/app/Exports/ClasslistAttendanceTemplateExport.php
    - Builds an .xlsx Spreadsheet with the "attendance" sheet and specified headers/rows.
  - laravel-api/app/Http/Requests/Api/V1/Attendance/AttendanceImportRequest.php
    - Validates upload (.xlsx only).
  - laravel-api/app/Services/ClasslistAttendanceImportService.php
    - Parses .xlsx and upserts attendance rows for a specific attendance date, normalizing is_present and remarks.
- Existing files to be modified
  - laravel-api/app/Http/Controllers/Api/V1/ClasslistAttendanceController.php
    - Add:
      - template(Request $request, int $id, int $dateId)
      - import(AttendanceImportRequest $request, int $id, int $dateId)
    - Use existing authorized() helper with action "view" for template and "edit" for import.
  - laravel-api/routes/api.php
    - Add routes:
      - GET  /api/v1/classlists/{id}/attendance/dates/{dateId}/template
      - POST /api/v1/classlists/{id}/attendance/dates/{dateId}/import
  - frontend/unity-spa/features/classlists/classlists.service.js
    - Add:
      - downloadAttendanceTemplate(classlistId, dateId)
      - importAttendance(classlistId, dateId, file)
  - frontend/unity-spa/features/classlists/attendance.controller.js
    - Add UI handlers:
      - downloadTemplate()
      - onImportFileChange($event)
      - importAttendance()
    - Track vm.importFile and vm.uploading flags.
  - frontend/unity-spa/features/classlists/attendance.html
    - Add UI:
      - "Download Template" button for selected date
      - File input and "Upload" button to import .xlsx
- Files to be deleted or moved
  - None.
- Configuration updates
  - None; PhpSpreadsheet already present and used in grades export/import.

[Functions]
Add export/import functions in backend and wire frontend service/controller functions.

Detailed breakdown:
- New backend functions
  - laravel-api/app/Exports/ClasslistAttendanceTemplateExport.php
    - build(int $classlistId, int $dateId): Spreadsheet
      - Validates date belongs to classlist, fetches roster, fills template rows.
  - laravel-api/app/Services/ClasslistAttendanceImportService.php
    - parseXlsx(string $path): \Generator of ['line' => int, 'data' => array]
      - Reads sheet "attendance" or first sheet; builds header map; yields non-empty rows.
    - upsert(iterable $rows, int $classlistId, int $dateId): array { updated, skipped, totalRows, errors[] }
      - Validates date/classlist ownership; normalizes is_present; trims remarks; updates tb_mas_classlist_attendance per (dateId, intCSID).
    - normalizeIsPresent($val): ?bool
      - Converts accepted tokens to true/false/null.
- Modified backend functions
  - laravel-api/app/Http/Controllers/Api/V1/ClasslistAttendanceController.php
    - template(Request $request, int $id, int $dateId)
      - Authorization: authorized($request, $id, 'view'); streamDownload Xlsx
    - import(AttendanceImportRequest $request, int $id, int $dateId)
      - Authorization: authorized($request, $id, 'edit'); parse + upsert; return JSON summary
- Frontend service functions
  - downloadAttendanceTemplate(classlistId, dateId) -> Promise<{ data: ArrayBuffer, filename: string }>
  - importAttendance(classlistId, dateId, file) -> Promise<ApiResponse>
- Frontend controller functions
  - downloadTemplate()
  - onImportFileChange($event)
  - importAttendance()

[Classes]
Introduce export/import classes and extend controller.

Detailed breakdown:
- New classes
  - App\Exports\ClasslistAttendanceTemplateExport
    - Methods: build(classlistId, dateId)
  - App\Http\Requests\Api\V1\Attendance\AttendanceImportRequest
    - Rules: file .xlsx only
  - App\Services\ClasslistAttendanceImportService
    - Methods: parseXlsx, upsert, normalizeIsPresent
- Modified classes
  - App\Http\Controllers\Api\V1\ClasslistAttendanceController
    - Add methods template() and import()
- Removed classes
  - None

[Dependencies]
No dependency modifications.

Details:
- PhpSpreadsheet already used for grades export/import.
- Continue to use Gate + header fallbacks.

[Testing]
Adopt feature tests for backend and manual QA for frontend.

Test coverage:
- Backend (new)
  - tests/Feature/Api/V1/ClasslistAttendanceImportExportTest.php
    - test_template_download_requires_view_permission()
    - test_import_requires_edit_permission()
    - test_template_contains_expected_headers_and_rows()
    - test_import_normalizes_is_present_values_and_persists_remarks()
    - test_import_skips_rows_with_invalid_csid_or_not_in_date()
    - test_import_clears_remarks_when_present_or_unset()
- Manual Frontend QA
  - Navigate to /#!/classlists/:id/attendance
  - Create/select a date
  - Use "Download Template" and verify headers/rows
  - Fill some is_present values: 1/0/blank and present/absent/unset; add remarks for absences only
  - Upload .xlsx; verify success message and that reloading date shows persisted values
  - Try import as non-assigned faculty; verify forbidden

[Implementation Order]
Sequence to minimize risk and ensure end-to-end flow.

1) Backend: Export builder
   - Create ClasslistAttendanceTemplateExport with build(classlistId, dateId).
2) Backend: Import request + service
   - Create AttendanceImportRequest (.xlsx only)
   - Create ClasslistAttendanceImportService (parseXlsx, normalizeIsPresent, upsert).
3) Backend: Controller endpoints
   - Add template() and import() to ClasslistAttendanceController using existing authorized() checks.
4) Routes
   - Register GET /classlists/{id}/attendance/dates/{dateId}/template and POST /classlists/{id}/attendance/dates/{dateId}/import.
5) Frontend: Service
   - Implement downloadAttendanceTemplate and importAttendance in classlists.service.js with headers and filename extraction.
6) Frontend: Controller/UI
   - Add "Download Template" and file upload controls in attendance.html
   - Wire handlers in attendance.controller.js; track vm.importFile, vm.uploading; success/error toasts.
7) QA
   - Manual run-through on a sample classlist with a few rows; verify both paths.
   - Add/execute backend tests as appropriate.
