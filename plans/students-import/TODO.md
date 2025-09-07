task_progress Items:
- [x] Step 1: Add backend request validator StudentImportRequest (validates xlsx/xls/csv, size)
- [x] Step 2: Add backend service StudentImportService (template columns, parse, resolve FKs, normalize, upsert)
- [x] Step 3: Add backend template exporter StudentTemplateExport (generate .xlsx with header substitutions)
- [x] Step 4: Add backend controller StudentImportController with template() and import() endpoints
- [x] Step 5: Register routes in laravel-api/routes/api.php for GET /students/import/template and POST /students/import (role: registrar,admin)
- [x] Step 6: Frontend service changes (students.service.js): add downloadTemplate() and import(file)
- [x] Step 7: Frontend UI changes (students.html + students.controller.js): add buttons, file input, import workflow, summary display
- [ ] Step 8: Critical-path tests: template download (headers), small import with 1 insert + 1 update, 1 failure due to bad Program Code, verify summary and list refresh
