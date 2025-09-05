# Student Dashboard Implementation TODO

Goal: Create a student dashboard page for student login that displays class schedules and records separated by term.

Approved Plan (Summary):
- Add new route /student/dashboard gated to student_view.
- Add ACCESS_MATRIX entry for /student/*.
- Implement StudentDashboardController + template to fetch viewer profile and records, group by term, and render schedules/records.
- Redirect student login to /student/dashboard (non-redirects mode).

Tasks:
- [ ] Routing
  - [ ] Add route in core/routes.js:
    - path: /student/dashboard
    - templateUrl: features/student-dashboard/student-dashboard.html
    - controller: StudentDashboardController
    - controllerAs: vm
    - requiredRoles: ['student_view', 'admin']
  - [ ] Update ACCESS_MATRIX in core/roles.constants.js to include:
    - { test: '^/student(?:/.*)?$', roles: ['student_view', 'admin'] }

- [ ] Controller + Template
  - [ ] Create features/student-dashboard/student-dashboard.controller.js
    - [ ] Read loginState; if not logged in or not a student, optionally allow but keep page data fetch guarded.
    - [ ] Resolve student_number via POST /api/v1/student/viewer using token = loginState.username; fallback to username if needed.
    - [ ] Fetch /api/v1/student/records with include_grades = true.
    - [ ] Group records by term (deriveTermsShapeIfFlat) and sort terms for display.
    - [ ] Bind computed vm.profile, vm.terms, loading/error states.
  - [ ] Create features/student-dashboard/student-dashboard.html
    - [ ] Header with profile: name, program, last term.
    - [ ] Sections by term, each with a table of subjects (schedule/records): Code, Description, Section, Faculty, Units, Remarks, Grades (Prelim, Midterm, Finals, Final).
    - [ ] Loading/error messages.

- [ ] Login Redirect
  - [ ] Update features/auth/login.controller.js doRedirect():
    - [ ] When useRedirects is false and loginType === 'student', route to '/student/dashboard' (else '/dashboard').

- [ ] Smoke Test Checklist
  - [ ] Login as a student account.
  - [ ] Ensure landing at /student/dashboard.
  - [ ] Verify profile header shows correct student and program info.
  - [ ] Verify terms are grouped and sorted; verify subjects per term.
  - [ ] Verify grades appear when available.
  - [ ] RBAC: Non-student (without admin) cannot access /student/*.

Notes:
- Endpoints used:
  - POST /api/v1/student/viewer
  - POST /api/v1/student/records
- Grouping falls back to derive from flat records if backend does not send {terms: [...]}.
