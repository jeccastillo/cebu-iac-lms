# Students Page - Add "Type" Column After "Status"

Goal: Display each student's "student_type" in the Students list table, in a new "Type" column placed immediately after the "Status" column.

## Tasks

- [ ] Backend (Laravel): Update StudentController@index to return `u.student_type AS type` in the students list API.
  - File: `laravel-api/app/Http/Controllers/Api/V1/StudentController.php`
  - Endpoint: `GET /api/v1/students`
  - Change: Add select column `u.student_type as type`.

- [ ] Frontend Template (AngularJS SPA): Add "Type" column after "Status".
  - File: `frontend/unity-spa/features/students/students.html`
  - Changes:
    - Add `<th>Type</th>` after the "Status" header and before "Actions".
    - Add per-column filter input: "Search Type" bound to `vm.cf.type` (after Status filter).
    - Render `{{ r.type || '-' }}` in the new column.
    - Update the "No results" row `colspan` from 8 to 9.

- [ ] Frontend Controller (AngularJS SPA): Wire up client-side filter.
  - File: `frontend/unity-spa/features/students/students.controller.js`
  - Changes:
    - Add `type: ''` to `vm.cf`.
    - Update `vm.filteredRows()` predicate to include `has(r.type, cf.type)`.

- [ ] Verification
  - Reload Students page and run a search.
  - Confirm the API response contains a `type` property for each row.
  - Confirm the "Type" column displays values and the per-column filter works.
