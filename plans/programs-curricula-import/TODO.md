# Programs &amp; Curricula Import — TODO

Track implementation progress for the approved plan.

task_progress Items:
- [x] Step 1: Backend Program import core
  - [x] Create ProgramImportService (parse/normalize/resolve/upsert/template)
  - [x] Create ProgramTemplateExport
  - [x] Create ProgramImportRequest
  - [x] Create ProgramImportController
- [x] Step 2: API routes
  - [x] Add GET /api/v1/programs/import/template with role:registrar,admin
  - [x] Add POST /api/v1/programs/import with role:registrar,admin
- [x] Step 3: Frontend — Programs Import
  - [x] Extend ProgramsService with:
    - [x] downloadImportTemplate()
    - [x] importFile(file, { dry_run, campus_id })
  - [x] Create Admin UI:
    - [x] features/admin/programs-import/programs-import.html
    - [x] features/admin/programs-import/programs-import.controller.js
  - [x] Add /admin/programs-import route in core/routes.js (admin-only)
- [x] Step 4: Frontend — Curricula Import
  - [x] Create Admin UI:
    - [x] features/admin/curricula-import/curricula-import.html
    - [x] features/admin/curricula-import/curricula-import.controller.js
  - [x] Add /admin/curricula-import route in core/routes.js (admin-only)
- [ ] Step 5: QA/Validation
  - [ ] Manual API smoke test (template + dry_run + import)
  - [ ] Manual FE flow verification for both admin pages
  - [ ] Adjust messages and headers if needed
