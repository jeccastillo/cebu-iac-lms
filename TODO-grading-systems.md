# TODO — Grading Systems (Laravel API + Angular)

task_progress Items:
- [x] Step 1: Define Gate grading.manage in AuthServiceProvider for admin and faculty_admin
- [x] Step 2: Create Eloquent models GradingSystem and GradingItem for legacy tables
- [x] Step 3: Add FormRequests (GradingSystemStore, GradingSystemUpdate, GradingItemStore, GradingItemsBulkStore)
- [x] Step 4: Implement Api\V1\GradingSystemController (index, show, store, update, destroy, addItemsBulk, addItem, deleteItem)
- [x] Step 5: Register routes in routes/api.php with role middleware (admin, faculty_admin)
- [ ] Step 6: Critical-path API testing — GET /grading-systems and GET /grading-systems/{id}
- [ ] Step 7: Critical-path API testing — POST/PUT/DELETE endpoints (requires an authenticated user with admin or faculty_admin)
- [ ] Step 8: Integrate SystemLogService verifications (ensure create/update/delete entries recorded)
- [x] Step 9: Optional — AngularJS frontend (service, controllers, views, routes, sidebar) and UI testing

Notes:
- Tables used: tb_mas_grading (systems), tb_mas_grading_item (items)
- Deletion of a grading system is blocked if referenced by tb_mas_subjects.grading_system_id or grading_system_id_midterm
- Items uniqueness: (grading_id, value) enforced logically in controller
- Public endpoints (no role required): GET /api/v1/grading-systems, GET /api/v1/grading-systems/{id}
- Protected endpoints (role: admin or faculty_admin): create/update/delete systems and items

Next actions:
- Execute critical-path tests for list and show endpoints
- If CI session or test user is available with admin/faculty_admin, test protected endpoints
