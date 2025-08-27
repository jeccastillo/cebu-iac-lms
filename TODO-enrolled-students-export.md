# TODO — Enrolled Students Export

Scope: Implement export to Excel for enrolled students for a selected term, exposed via GET /api/v1/reports/enrolled-students/export and add a new Registrar Reports page with an Export action. Enrollment filter: tb_mas_registration.intAYID = :syid AND r.intROG = 1. Program code: use registration.current_program if not null, else fallback to users.intProgramID.

Progress will be tracked by checking off steps as they are completed.

task_progress Items:
- [x] Step 1: Add backend export class EnrolledStudentsExport.php (FromCollection, WithHeadings, WithMapping, ShouldAutoSize)
- [x] Step 2: Add ReportsController@enrolledStudentsExport with validation and Excel::download
- [x] Step 3: Update laravel-api/routes/api.php to register GET /api/v1/reports/enrolled-students/export with role:registrar,admin
- [x] Step 4: Create frontend service features/reports/reports.service.js to call export endpoint (arraybuffer)
- [x] Step 5: Create frontend controller features/reports/reports.controller.js to fetch selected term and trigger download
- [x] Step 6: Create frontend page features/reports/reports.html with Export button and status/error handling
- [x] Step 7: Wire route in frontend/unity-spa/core/routes.js for #/registrar/reports
- [x] Step 8: Ensure sidebar access vm.canAccess('/registrar/reports') is allowed for registrar/admin roles
- [ ] Step 9: Critical-path testing limited to the new Registrar Reports page and related sidebar/term interactions

Notes:
- Output columns: Student Number, First Name, Last Name, Middle Name, Program Code
- Deduplication: Group by student to avoid duplicates if multiple rows could match in joins
- Filename: enrolled-students-YYYYmmdd-HHMMSS.xlsx
