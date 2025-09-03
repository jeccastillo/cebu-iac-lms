# Student Dashboard Routing & Sidebar RBAC Fix - TODO

Context:
- Students reported landing on Faculty Dashboard after login.
- Admin group/menus visible in sidebar for student users.

Root Causes Identified:
1) Sidebar visibility relies on ACCESS_MATRIX (regex gating) when passing string paths; missing /admin and base /faculty entries cause default-allow for many admin-only links.
2) Login redirect picks destination by form loginType instead of actual resolved roles; mis-selected loginType (=faculty) sends a real student to /dashboard.

Planned Fixes:
- [x] Add explicit RBAC entries in ACCESS_MATRIX:
  - [x] `^/admin(?:/.*)?$` => admin
  - [x] `^/faculty$` => admin (faculty CRUD landing page)
- [x] Make login redirect role-driven (based on resolved roles array):
  - [x] If roles include `student_view` => `/student/dashboard`
  - [x] Else => `/dashboard`
  - [x] Keep APP_CONFIG.LOGIN_APP_CONFIG.useRedirects logic intact

Files to Update:
- frontend/unity-spa/core/roles.constants.js
- frontend/unity-spa/features/auth/login.controller.js

Test Checklist:
- [ ] Login as student (with loginType=student): lands on /student/dashboard; sidebar shows no Admin menus.
- [ ] Login as student (intentionally select loginType=faculty): still lands on /student/dashboard; sidebar hides Admin menus.
- [ ] Login as faculty: lands on /dashboard; faculty menus visible; admin menus hidden.
- [ ] Login as admin: lands on /dashboard; admin menus visible; /admin/* accessible.
- [ ] Sidebar:
  - [ ] Admin group/links hidden for non-admin roles.
  - [ ] Faculty CRUD (/faculty) hidden for non-admin.
  - [ ] Faculty pages (/faculty/profile, /faculty/classes) still available for faculty/admin by existing pattern `^/faculty/.*$`.

Notes:
- Sidebar uses RoleService.canAccess(path) with string paths; only ACCESS_MATRIX applies (not route.requiredRoles).
- Student dashboard route already defined at `/student/dashboard` with requiredRoles `['student_view', 'admin']`.
