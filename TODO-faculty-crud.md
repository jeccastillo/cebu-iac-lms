# TODO — Faculty CRUD (Admin-only)

Source of truth: implementation_plan_faculty.md

task_progress Items:
- [x] Step 1: Implement Laravel FormRequests (FacultyStoreRequest, FacultyUpdateRequest) with full validation rules
- [x] Step 2: Implement FacultyResource for consistent API output
- [x] Step 3: Implement FacultyController CRUD with bcrypt handling on strPass and 404/409 responses
- [x] Step 4: Add Laravel routes for /api/v1/faculty endpoints with middleware('role:admin')
- [x] Step 5: Create AngularJS faculty.service.js (list/get/create/update/delete)
- [x] Step 6: Create AngularJS faculty.controller.js to manage list and edit flows
- [x] Step 7: Create AngularJS list.html and edit.html views with form validations
- [x] Step 8: Update core/routes.js to add admin.faculty and admin.faculty.edit states, guarded by role service
- [x] Step 9: Update sidebar to include Faculty menu visible only to admin
- [ ] Step 10: Perform cURL and UI smoke tests to verify role enforcement and CRUD functionality

Notes:
- All write and detail endpoints must be protected by middleware('role:admin') using X-Faculty-ID header for dev context.
- Required fields align with legacy NOT NULL behavior: strMiddlename, strEmail, strMobileNumber, strAddress, strDepartment, strSchool.
- Password handling: hash strPass with bcrypt on create; on update only hash if provided, otherwise leave unchanged.
- Frontend visibility: Faculty menu and routes only visible/accessible to admin role.
