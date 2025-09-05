# Students page campus scoping

Goal: Display only students whose campus_id matches the globally selected campus.

Tasks:
- [ ] Backend: Add optional campus_id filter to GET /api/v1/students
  - File: laravel-api/app/Http/Controllers/Api/V1/StudentController.php
  - Change: If query has campus_id, add where u.campus_id = :campus_id
- [ ] Frontend: Pass selected campus_id in StudentsController requests
  - File: frontend/unity-spa/features/students/students.controller.js
  - Inject CampusService and $scope
  - In buildParams(), include campus_id from CampusService.getSelectedCampus()
  - Subscribe to campusChanged and re-run vm.search(), resetting to page 1
- [ ] Verification
  - In the app, change campus via the sidebar Campus Selector
  - Navigate to #!/students and confirm API requests include campus_id
  - Change campus and confirm the list refreshes and changes accordingly
- [ ] Optional hardening
  - Gate initial search until CampusService has initialized to avoid potential unfiltered flash
