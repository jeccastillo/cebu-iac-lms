# Roles CRUD + Assignment (Admin-only) TODO

Scope:
- Backend hardening on read endpoints for roles.
- Frontend AngularJS roles management UI: list/add/edit/disable and assign/unassign to faculty.
- Ensure admin-only access via route guards and Access Matrix.
- Wire X-Faculty-ID header using faculty_id captured at login.

Tasks:

1) Backend: routes protection
- [ ] Protect GET /api/v1/roles with role:admin
- [ ] Protect GET /api/v1/faculty/{id}/roles with role:admin

2) Backend: include faculty_id in login response
- [ ] Update UsersController::auth to include faculty_id for non-student logins
- [ ] Frontend login stores faculty_id in loginState

3) Frontend: routes and access
- [ ] Add /roles route (requiredRoles: ['admin']) in core/routes.js
- [ ] Update ACCESS_MATRIX to include '^/roles(?:/.*)?$' for admin only
- [ ] Add Roles link in sidebar (admin-only)

4) Frontend: service
- [ ] Create features/roles/roles.service.js
  - list(includeInactive?)
  - create(payload)
  - update(id, payload)
  - remove(id)
  - facultyRoles(facultyId)
  - assignFacultyRoles(facultyId, payload)
  - removeFacultyRole(facultyId, roleId)
  - searchFaculty(q)
  - Automatically attach X-Faculty-ID from loginState.faculty_id on admin endpoints

5) Frontend: controller + template
- [ ] Create features/roles/roles.controller.js
  - Manage roles table + add/edit form + disable
  - Assign roles: faculty search, view assigned roles, add & remove roles
- [ ] Create features/roles/roles.html

6) Validation & smoke
- [ ] Verify route guard redirects when non-admin hits /roles
- [ ] Verify role CRUD flows
- [ ] Verify assigning/removing roles for a faculty
- [ ] Verify calls include X-Faculty-ID and pass RequireRole middleware

Notes:
- API base: APP_CONFIG.API_BASE (e.g. /laravel-api/public/api/v1)
- Header: X-Faculty-ID taken from loginState.faculty_id
- Fallbacks: If missing faculty_id, display warning in Roles screen
