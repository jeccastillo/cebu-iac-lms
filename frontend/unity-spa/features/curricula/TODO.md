task_progress Items:
- [x] Step 1: Backend - create App/Http/Requests/Api/V1/CurriculumSubjectsBulkRequest.php (validation with max 60, ranges)
- [x] Step 2: Backend - register POST /v1/curriculum/{id}/subjects/bulk route with role:registrar,admin
- [x] Step 3: Backend - implement CurriculumController::addSubjectsBulk() with counters, per-item errors, and SystemLogService logging
- [x] Step 4: Frontend - add CurriculaService.addSubjectsBulk(curriculumId, payload)
- [x] Step 5: Frontend - extend CurriculumEditController with subject search/list, selection map, defaults, updateIfExists, submit handler
- [x] Step 6: Frontend - update features/curricula/edit.html with "Add Subjects" panel (search, filters, list with checkboxes, defaults, toggle, submit)
- [ ] Step 7: Critical-path tests - API happy paths and duplicate update; UI selection cap 60, submit summary; basic regressions
- [x] Step 8: Update TODO.md checkboxes reflecting completed steps
