# Attendance All-Dates Upload — Enhancement Proposal

Goal
Enable uploading attendance for all dates of a classlist from a single .xlsx file, improving productivity and aligning with bulk workflows. Maintain parity with existing authorization patterns and data integrity rules.

Summary
- Add an “All Dates” template export (single file listing all existing attendance dates/rows for a classlist).
- Add an “All Dates” import endpoint that reads attendance_date + period per row and applies updates across multiple dates.
- Keep per-date template/import endpoints for focused workflows.
- Frontend: provide buttons to download/import “All Dates” in the Attendance page.

Why
- Faculty/Admin commonly update multiple meeting dates in one batch.
- Reduces repeated per-date downloads/uploads.
- Ensures consistency by centralizing normalization and rules.

Scope
- Backend: new export builder (or extend current to support scope=all), new import method/endpoint to process multiple dates, idempotent date seeding on demand.
- Frontend: add service/controller methods and UI controls for “All Dates”.

Compatibility and Data Model
- Reuses tb_mas_classlist_attendance_date and tb_mas_classlist_attendance.
- No DB changes required.
- Keeps the period field required per attendance date.

Excel Template Specifications

Two templates:
1) Per-Date (already implemented)
   - Sheet: attendance
   - Headers: intCSID, student_number, last_name, first_name, attendance_date, period, is_present, remarks

2) All-Dates (new)
   - Sheet: attendance_all
   - Headers (case-insensitive read):
     - attendance_date: YYYY-MM-DD (required)
     - period: midterm|finals (required)
     - intCSID: numeric/string (required)
     - is_present: tokens (1/0/blank; present/absent/unset; true/false; case-insensitive)
     - remarks: optional
     - Optional informational columns (ignored on import): student_number, last_name, first_name
   - Rows: all students for all attendance dates currently present for the classlist (ordered by date desc, period asc, last name asc).
   - Notes sheet: instructions including accepted tokens, that remarks are cleared when present/unset.

Authorization
- View (download template_all): Gate attendance.classlist.view OR header fallbacks (admin or assigned faculty).
- Edit (import_all): Gate attendance.classlist.edit OR header fallbacks (admin or assigned faculty).

Backend Design

New Endpoints
- GET  /api/v1/classlists/{id}/attendance/template?scope=all
  - Returns all-dates template (.xlsx) for the classlist.
- POST /api/v1/classlists/{id}/attendance/import
  - Accepts .xlsx with sheet attendance_all
  - Applies updates across multiple dates

Controller Changes (ClasslistAttendanceController)
- Add templateAll(Request $req, int $id)
  - Reuse authorize(view)
  - Build spreadsheet (see Export below) and stream download; filename: classlist-{id}-attendance-all-template.xlsx
- Add importAll(AttendanceImportRequest $req, int $id)
  - Reuse authorize(edit)
  - Parse file via new service method upsertAll; return JSON summary:
    { updated, skipped, created_dates, seeded_rows, totalRows, errors[] }

Export Builder
Option A: Create App\Exports\ClasslistAttendanceAllTemplateExport
- build(int $classlistId): Spreadsheet
  - Fetch all dates for classlist, then fetch all rows joined with user info
  - Write to sheet attendance_all with required headers
  - Include Notes sheet (accepted tokens and rules)

Option B: Extend existing ClasslistAttendanceTemplateExport with a buildAll($classlistId) method.

Import Service
Extend App\Services\ClasslistAttendanceImportService:
- parseXlsx(): unchanged (sheet name selectable)
- normalizeIsPresent(): unchanged
- upsertAll(iterable $rows, int $classlistId, ?int $actorId): array
  - For each row:
    1) Validate attendance_date (Y-m-d) and period in {midterm, finals}
    2) Resolve or create attendance_date row:
       - If not found, create tb_mas_classlist_attendance_date (idempotent on unique (classlist, attendance_date, period))
       - If created, seed tb_mas_classlist_attendance rows for all enrolled students with is_present=null (idempotent based on unique (intAttendanceDateID,intCSID))
    3) Validate intCSID belongs to the classlist and exists in seeded rows for that dateId
    4) Normalize is_present and remarks (clear when present/unset; trim and 255-limit when absent)
    5) Update tb_mas_classlist_attendance row (marked_by, marked_at)
  - Track counters:
    - updated (rows updated)
    - skipped (empty or unchanged rows)
    - created_dates (new date rows created)
    - seeded_rows (new attendance rows seeded across dates)
    - errors[] with line, code, message
  - Performance:
    - Preload all existing attendance dates into a map keyed by date + period
    - Cache classlist student CSIDs to validate quickly
    - Maintain created dateIds in-memory
    - Insert seeds in batches (e.g., 500)

Routing
- Add above endpoints in routes/api.php under Classlist Attendance, before parameterized routes to avoid collisions.

Frontend Design

Service (frontend/unity-spa/features/classlists/classlists.service.js):
- downloadAttendanceTemplateAll(classlistId)
  - GET /classlists/{id}/attendance/template?scope=all (arraybuffer)
- importAttendanceAll(classlistId, file)
  - POST /classlists/{id}/attendance/import (multipart/form-data)

Controller (attendance.controller.js):
- State:
  - vm.importFileAll, vm.uploadingAll
- Actions:
  - vm.downloadTemplateAll()
  - vm.onImportFileAllChange()
  - vm.importAttendanceAll()
- UX:
  - Place alongside per-date controls
  - After import success, refresh dates list and, if a date is selected, its details

Template (attendance.html):
- Buttons:
  - “Download All-Dates Template”
  - “Choose File” + “Upload All-Dates”
- Show selected filename and busy state
- Keep per-date controls intact

Error Handling and Validation
- Backend returns 422 with detailed errors[] including line and code
- Frontend shows summary toast and surfaces a simple count of errors
- Optional: allow dry_run=1 to validate without persisting (future)

Maintainability Improvements (Refactors)
- Consolidate spreadsheet utility behaviors:
  - Common header parsing, cell read helpers (already similar between grades and attendance)
  - Consider a SpreadsheetHelper class or trait for read/normalize helpers used by both Grades and Attendance imports
- Constants:
  - Define accepted tokens for is_present in a single place
- Reuse createDate + seeding logic:
  - Factor seeding into a reusable method in ClasslistAttendanceService and call from importAll for consistency
- Transactions:
  - Wrap per-date create+seed in transactions for atomicity
- Logging:
  - Log actor, counts, and errors summary for audit
- Large Roster Performance:
  - Use chunked inserts for seeds
  - Avoid N+1 queries by preloading maps (dates, CSIDs, existing rows)
- Validation:
  - Strict Y-m-d validation for attendance_date
  - Strict period validation
  - Robust CSID validation and mismatch errors

Testing (Additions)
- Feature tests:
  - test_template_all_download_requires_view_permission
  - test_import_all_requires_edit_permission
  - test_import_all_creates_missing_dates_and_seeds_rows_idempotently
  - test_import_all_updates_multiple_dates_and_handles_tokens
  - test_import_all_skips_invalid_rows_and_reports_errors
- Manual QA:
  - Download All-Dates template, edit multiple dates, import, verify across multiple dates in UI
  - Re-import same data (idempotent)
  - Include invalid tokens, mismatched CSID, missing period/date rows

Implementation Steps (Incremental)
1) Backend export (buildAll) + route GET /classlists/{id}/attendance/template?scope=all
2) Backend import (upsertAll) + route POST /classlists/{id}/attendance/import
3) Frontend service + controller + template changes for All-Dates
4) QA: end-to-end across multiple dates
5) Optional: Create a shared SpreadsheetHelper for both attendance and grades imports

Open Questions
- Auto-create missing dates on import_all? Proposed: YES (idempotent create+seed when not found)
- Dry-run mode for import_all? Proposed: Future enhancement (consistent with classlists import flow)
