task_progress Items:
- [x] Step 1: Create TODO.md tracking the approved steps
- [ ] Step 2: Add admin routes in laravel-api/routes/api.php (GET /api/v1/students/{id}/raw, PUT /api/v1/students/{id}) with role:admin
- [ ] Step 3: Create StudentUpdateRequest (laravel-api/app/Http/Requests/Api/V1/StudentUpdateRequest.php)
- [ ] Step 4: Implement StudentController@raw and @update (schema-safe update + SystemLogService)
- [ ] Step 5: Add Angular route in frontend/unity-spa/core/routes.js (/admin/students/:id/edit)
- [ ] Step 6: Create AdminStudentEditController (features/admin/students/admin-student-edit.controller.js)
- [ ] Step 7: Create Admin Student Edit template (features/admin/students/edit.html)
- [ ] Step 8: Optional: StudentsAdminService for API calls (features/admin/students/students-admin.service.js)
- [ ] Step 9: Critical-path tests: GET raw, PUT update, verify SystemLog record
