# TODO — Student Program Change Requests

Tracking the implementation steps for the student-facing Program Change Request feature.

task_progress Items:
- [x] Step 1: Add migration for tb_mas_shift_requests with unique(student_id, term_id)
- [x] Step 2: Add Eloquent model App/Models/ShiftRequest
- [x] Step 3: Implement ShiftRequestController@store and @index; wire routes in laravel-api/routes/api.php
- [x] Step 4: Integrate SystemAlert creation/broadcast on request create
- [x] Step 5: Create student page controller and template for Request Program Change
- [x] Step 6: Wire new route in frontend/unity-spa/core/routes.js, add sidebar link, add script include in index.html
- [ ] Step 7: Run critical-path testing (success, duplicate, invalid input, registrar alert visibility)

## Scope Summary
- Students request a change of base Program (no curriculum selection).
- Prevent duplicates: one request per student per term.
- On create, generate a system alert targeted to Registrar with link "#/registrar/shifting".

## Backend
- Migration: tb_mas_shift_requests (id, student_id, student_number, term_id, program_from, program_to, reason, status, requested_at, processed_at, processed_by_faculty_id, campus_id, meta, timestamps). Unique(student_id, term_id).
- Model: App\Models\ShiftRequest
- Controller: App\Http\Controllers\Api\V1\ShiftRequestController
  - POST /api/v1/student/shift-requests (store)
  - GET  /api/v1/student/shift-requests (index)
- System Alert creation via SystemAlert model + SystemAlertService::broadcast('create', $alert).

## Frontend
- New page: #!/student/change-program-request (StudentChangeProgramRequestController)
- Route guard: requiredRoles ["student_view","admin"]
- Sidebar: add "Request Program Change" under Student group.
- Script include in index.html.
