# Faculty Department Tagging - Implementation TODO

Goal: Add a function in the UI in the edit faculty page where you can tag departments of the faculty.

Backend Endpoints (already available):
- GET /api/v1/faculty/{id}/departments
- POST /api/v1/faculty/{id}/departments
- DELETE /api/v1/faculty/{id}/departments/{code}?campus_id=

Scope (Frontend AngularJS Unity SPA):
- files:
  - frontend/unity-spa/features/faculty/faculty.service.js
  - frontend/unity-spa/features/faculty/faculty.controller.js
  - frontend/unity-spa/features/faculty/edit.html
  - Reuse DepartmentDeficienciesService.meta() for department codes

Tasks:
- [ ] Services: Extend FacultyService
  - [ ] listDepartments(facultyId): GET /faculty/{id}/departments
  - [ ] addDepartment(facultyId, payload): POST /faculty/{id}/departments
  - [ ] removeDepartment(facultyId, code, campusId?): DELETE /faculty/{id}/departments/{code}?campus_id=
- [ ] Controller: FacultyEditController updates
  - [ ] Inject DepartmentDeficienciesService
  - [ ] Add vm.dept state: { list: [], meta: { departments: [] }, form: { department_code: '', campus_id: '' }, loading: false, error: null }
  - [ ] Methods:
    - [ ] vm.loadDepartmentsMeta()
    - [ ] vm.loadDepartments()
    - [ ] vm.addDepartmentTag()
    - [ ] vm.removeDepartmentTag(tag)
  - [ ] On edit mode load, call loadDepartmentsMeta() and loadDepartments()
- [ ] UI: Faculty Edit Page
  - [ ] Add a "Department Tags" panel (visible only on edit)
  - [ ] Controls: Department select (options from vm.dept.meta.departments), optional Campus ID, and "Add Tag" button
  - [ ] Table to list current tags with Remove action
- [ ] Manual Test
  - [ ] Open #/faculty/:id/edit
  - [ ] Verify department list loads (from DepartmentDeficienciesService.meta)
  - [ ] Add department tag (with/without campus ID) and see in list
  - [ ] Remove department tag and verify it disappears
  - [ ] Confirm X-Faculty-ID headers are sent (network tab)

Notes:
- Use existing _adminHeaders from FacultyService to include X-Faculty-ID (and other admin headers).
- Keep UI non-intrusive; only show panel for edit mode (vm.isEdit).
