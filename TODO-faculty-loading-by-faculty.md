# TODO — Faculty Loading: Assign by Faculty

Goal: Implement a faculty-centric page to assign or unassign sections (classlists) per faculty for the active term. Restricted to roles: registrar, faculty_admin, admin.

Plan Source: frontend/unity-spa/features/academics/faculty-loading/implementation_plan.md

task_progress Items:
- [x] Step 1: Backend — allow nullable intFacultyID in ClasslistUpdateRequest (unassign support)
- [x] Step 2: Backend — add unassign branch in ClasslistController@update (bypass faculty validations when null; enforce dissolved guard; keep logs)
- [x] Step 3: Frontend — extend FacultyLoadingService with listByFaculty(params) and listUnassigned(params) (client-side unassigned)
- [x] Step 4: Frontend — create by-faculty.controller.js (select faculty, load assigned/unassigned, queue assign/unassign, saveAll, pagination, toasts)
- [x] Step 5: Frontend — create by-faculty.html (dual lists UI with move controls, pagination, bulk Save)
- [x] Step 6: Frontend — wire route in core/routes.js (/faculty-loading/by-faculty) with requiredRoles: ["registrar", "faculty_admin", "admin"]
- [x] Step 7: Frontend — update roles.constants.js to include faculty_admin for ^/faculty-loading(?:/.*)?$ if not already present
- [x] Step 8: Frontend — add sidebar menu item "Assign by Faculty" under Academics and add script include in index.html
- [ ] Step 9: Critical-path API tests — assign, unassign, bulk-assign, basic filters, faculty options
- [ ] Step 10: Critical-path UI tests — route access, list loading, move/queue/save, pagination, toasts

Notes:
- Keep unassigned filtering client-side (do not add unassigned=1 backend param).
- Use GenericApiController@faculty (teaching=1 default) for faculty options; filter by campus_id when provided.
- RequireRole via X-Faculty-ID header (already handled in FacultyLoadingService._adminHeaders).
- Respect dissolved guard and campus/teaching checks on backend.
