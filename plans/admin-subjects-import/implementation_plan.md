# Implementation Plan

[Overview]
Add an Admin-only Subjects Import page at /admin/subjects-import mirroring the Admin Programs Import flow. The page will support campus selection, CSV file upload (preferred and enforced), a dry-run option, and a template download button. It will display an import summary and an errors table with columns: Line, Code, Message. The existing import controls on the Subjects list page will remain unchanged.

This implementation consolidates subject import under Admin alongside Users/Programs/Curricula imports for parity and discoverability. The Laravel backend already has endpoints for subjects import/template; CSV will be required by the Admin UI, though the backend continues to accept .xlsx/.xls/.csv. Campus selection will be presented in the Admin UI for parity; current backend subject import ignores campus_id, but we will pass it along for observability and future compatibility.

[Types]  
Minimal frontend data structures for requests and UI summaries.

- ImportOptions (JS object)
  - file: File (required)
  - dry_run?: boolean (default false)
  - campus_id?: number (optional; required by UI; sent to backend but currently ignored)

- ImportSummary (API response shape)
  - totalRows: number
  - inserted: number
  - updated: number
  - skipped: number
  - errors: Array<ImportError>

- ImportError
  - line: number
  - code?: string
  - message: string

Validation/Rules:
- Frontend: enforce campus selection (required) and .csv file extension. If not provided or invalid, disable import and show a clear error.
- Backend: existing SubjectImportController import() returns { success, result: { totalRows, inserted, updated, skipped, errors[] } }. Errors items include { line, code, message }. We map these directly into the UI.

[Files]
Add a new Admin page, wire routing and sidebar, and extend SubjectsService to support campus_id parameter.

- New files to be created:
  - frontend/unity-spa/features/admin/subjects-import/subjects-import.controller.js
    - AngularJS controller: AdminSubjectsImportController.
    - Responsibilities: campus init/selection listener, .csv file selection, dry-run toggle, template download (via SubjectsService), import submit (via SubjectsService with campus_id + dry_run), summary/errors state.
  - frontend/unity-spa/features/admin/subjects-import/subjects-import.html
    - Tailwind-based view mirroring Admin Programs Import layout:
      - Header: Admin — Subjects Import
      - Section: Import Settings
        - Campus selector component (campus-selector, compact)
        - File upload button + label; accept=".csv"; show selected file name
        - Dry run checkbox
        - Actions: Download Template, Import
      - Feedback: error alert, success summary, errors table (Line, Code, Message; limit display to 50 rows)

- Existing files to be modified:
  - frontend/unity-spa/core/routes.js
    - Add Admin route:
      .when("/admin/subjects-import", {
        templateUrl: "features/admin/subjects-import/subjects-import.html",
        controller: "AdminSubjectsImportController",
        controllerAs: "vm",
        requiredRoles: ["admin"],
      })
  - frontend/unity-spa/shared/components/sidebar/sidebar.controller.js
    - Under Admin group → children: insert { label: 'Subjects Import', path: '/admin/subjects-import' } near Programs/Curricula Import.
  - frontend/unity-spa/index.html
    - Add script include:
      <script src="features/admin/subjects-import/subjects-import.controller.js"></script>
  - frontend/unity-spa/features/subjects/subjects.service.js
    - Enhance importFile(file, opts) to append campus_id (if provided) to FormData (parity with ProgramsService.importFile).
    - No other behavior change; keep existing Subjects list page import working.

- Files to be deleted/moved:
  - None.

- Configuration updates:
  - None.

[Functions]
Add controller functions for Admin Subjects Import and extend SubjectsService import function.

- New functions (AdminSubjectsImportController)
  - activate()
    - Initialize CampusService; set vm.selectedCampus from CampusService.getSelectedCampus(); subscribe to campusChanged event to keep selection in sync.
  - downloadTemplate()
    - Use SubjectsService.downloadImportTemplate(); create Blob and save filename from Content-Disposition, with fallback 'subjects-import-template.xlsx'.
  - openFileDialog()
    - Trigger hidden file input click (#subjectsImportFileAdmin).
  - onFileChanged($event)
    - Capture selected file; only allow .csv; if not .csv, set vm.error and clear selection.
  - importFile()
    - Validate: file present, extension .csv, and campus selected (numeric id).
    - Call SubjectsService.importFile(vm.file, { campus_id, dry_run }); on success set vm.summary from response.result; on failure set vm.error.
  - safeClearErrorSoon() and safeDefer(fn)
    - UI niceties for clearing errors and scheduling.

- Modified functions
  - SubjectsService.importFile(file, opts) in frontend/unity-spa/features/subjects/subjects.service.js
    - Add:
      if (opts && opts.campus_id != null) { fd.append('campus_id', String(parseInt(opts.campus_id, 10))); }
    - Keep response mapping and headers/X-Faculty-ID patterns unchanged.

- Removed functions
  - None.

[Classes]
No new JS classes or PHP classes required.

- New classes
  - None.

- Modified classes
  - None (Angular controllers/services are functions/factories).

- Removed classes
  - None.

[Dependencies]
No new external dependencies required.

- AngularJS 1.x and existing app dependencies suffice.
- Reuses SubjectsService with minor enhancement.
- Uses existing CampusService and campus-selector component.
- Backend endpoints already exist:
  - GET /api/v1/subjects/import/template (SubjectImportController@template)
  - POST /api/v1/subjects/import (SubjectImportController@import)
- No composer/npm package changes.

[Testing]
Manual acceptance tests in local environment using realistic CSVs.

- API-level sanity (already covered by existing controllers/services)
  - GET /subjects/import/template returns .xlsx; download works; filename respects Content-Disposition.
  - POST /subjects/import with CSV payload succeeds; when dry_run=true, no DB writes.

- UI-level tests (Admin-only):
  - Navigation:
    - Sidebar Admin → Subjects Import entry visible to admin; route /admin/subjects-import loads view/controller.
  - Campus:
    - Campus selector initializes to current; changing campus updates vm.selectedCampus; Import disabled until campus chosen.
  - File:
    - Accept only .csv; picking .xlsx/.xls yields UI error and prevents import.
    - Show chosen filename; allows clearing and re-selecting.
  - Dry run:
    - When checked, submit appends dry_run=1; summary returned without DB changes.
  - Submit:
    - Valid file + campus → import runs; show summary: Total, Inserted, Updated, Skipped.
    - Errors table headers: Line, Code, Message; render up to 50 rows.
  - Error cases:
    - Missing file → error message.
    - Missing campus → error message.
    - Backend validation error → error shows err.data.message if present; otherwise generic message.
  - Regression:
    - Subjects list page existing import controls still function unchanged.

[Implementation Order]
Implement in small, verifiable steps to minimize integration risk.

1) Service enhancement:
   - Update frontend/unity-spa/features/subjects/subjects.service.js to accept and send opts.campus_id in importFile() FormData (do not change existing public API shape otherwise).
2) New Admin page assets:
   - Create frontend/unity-spa/features/admin/subjects-import/subjects-import.controller.js with functions: activate, downloadTemplate, openFileDialog, onFileChanged, importFile, helpers.
   - Create frontend/unity-spa/features/admin/subjects-import/subjects-import.html mirroring Admin Programs Import layout; enforce accept=".csv"; include campus selector; results table (Line, Code, Message).
3) Wire-up:
   - Modify frontend/unity-spa/core/routes.js to add .when("/admin/subjects-import", ...).
   - Modify frontend/unity-spa/shared/components/sidebar/sidebar.controller.js to add Admin menu entry: Subjects Import.
   - Modify frontend/unity-spa/index.html to include <script src="features/admin/subjects-import/subjects-import.controller.js"></script>.
4) QA pass:
   - Navigate to /admin/subjects-import and verify UI behaviors and happy/error paths using sample CSV files.
   - Verify Subjects list page import feature remains unchanged.
5) Polish:
   - Ensure error/success alerts auto-clear after a short delay; validate loading states and disabled buttons during import.
