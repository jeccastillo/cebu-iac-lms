# TODO — Advisors Quick View (Students with Advisors by Campus)

Goal:
Create a quick viewing page to list all students in alphabetical order by last name, showing their respective advisors, filtered by the globally selected campus.

Scopes:
- Backend: add a lightweight listing endpoint that returns students with their current advisors, optionally filtered by campus_id, sorted by last_name asc then first_name asc.
- Frontend: add a new route and page under /advisors/quick-view that fetches the list and renders a simple table. It should react to campus changes via CampusService.

Steps:

1) Backend — Controller method
- File: laravel-api/app/Http/Controllers/Api/V1/StudentAdvisorController.php
- Add: public function list(Request $request): JsonResponse
  - Query tb_mas_users (alias u)
  - Left-join tb_mas_faculty (alias f) on f.intID = u.intAdvisorID
  - Optionally left-join tb_mas_campuses (alias c) on c.id = u.campus_id when table exists
  - Filter: if campus_id is provided (?campus_id=ID), apply where u.campus_id = ID
  - Order: u.strLastname ASC, u.strFirstname ASC
  - Select minimal fields:
    - student_id, student_number, first_name, middle_name, last_name, campus_id, campus_name?, advisor_id, advisor_name
  - Return: { success: true, data: rows }

2) Backend — Route
- File: laravel-api/routes/api.php
- Add route under v1 group, guarded by role: faculty_admin, admin:
  Route::get('/student-advisors/list', [StudentAdvisorController::class, 'list'])->middleware('role:faculty_admin,admin');

3) Frontend — Service extension
- File: frontend/unity-spa/features/advisors/advisors.service.js
- Add method:
  listByCampus(campusId) -> GET /student-advisors/list?campus_id={id}
  - Add X-Faculty-ID header via existing _adminHeaders
  - Return unwrapped payload

4) Frontend — New page
- File: frontend/unity-spa/features/advisors/quick-view.controller.js
  - Controller: AdvisorsQuickViewController
  - Inject: $scope, $http, APP_CONFIG, CampusService, StudentAdvisorService
  - On activate(): await CampusService.init(), resolve selected campus, fetch list
  - Watch campusChanged to refresh
  - Expose: loading, error, items[], campus

- File: frontend/unity-spa/features/advisors/quick-view.html
  - Heading: "Advisors Quick View"
  - Note: "Scoped by selected campus"
  - Table columns: Student Number, Last Name, First Name, Middle, Advisor
  - Show total count, loading spinner, and error state

5) Frontend — Route
- File: frontend/unity-spa/core/routes.js
- Add route:
  .when("/advisors/quick-view", {
    templateUrl: "features/advisors/quick-view.html",
    controller: "AdvisorsQuickViewController",
    controllerAs: "vm",
    requiredRoles: ["faculty_admin", "admin"]
  })

6) Validation / Testing
- Navigate to #!/advisors/quick-view
- Verify sorted by last_name
- Change global campus via header/campus selector; verify list updates
- Verify proper headers sent (X-Faculty-ID)
- Large datasets: if needed later, add pagination (not in current scope)
