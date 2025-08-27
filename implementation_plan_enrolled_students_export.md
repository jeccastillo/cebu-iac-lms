# Implementation Plan

[Overview]
Implement an Export to Excel for enrolled students for a selected academic term, exposed via a protected Laravel API endpoint and a new "Registrar Reports" page in the SPA with an "Export Enrolled Students" action.

This implementation adds a backend export that pulls students enrolled in a specific term (tb_mas_registration.intAYID = :syid) with r.intROG = 1, joining to users and programs to output: student number, first name, last name, middle name, program code. Program code will be derived from registration.current_program when present; otherwise fallback to users.intProgramID. The API will be added under /api/v1/reports/enrolled-students/export guarded by role:registrar,admin. The frontend will add a Registrar Reports page, consume the selected term from the existing TermSelector component, and trigger the export for download.

[Types]  
Introduce a simple request validation typing for export parameters and a mapped row structure for the Excel file.

Detailed type definitions:
- Export Request (query string):
  - syid: integer (required) — tb_mas_sy.intID (selected term)
- EnrolledStudentRow (export mapping):
  - student_number: string — tb_mas_users.strStudentNumber
  - first_name: string — tb_mas_users.strFirstname
  - last_name: string — tb_mas_users.strLastname
  - middle_name: string|null — tb_mas_users.strMiddlename
  - program_code: string|null — tb_mas_programs.strProgramCode (joined using COALESCE(registration.current_program, users.intProgramID))
- Excel sheet formatting:
  - Headings: ["Student Number", "First Name", "Last Name", "Middle Name", "Program Code"]
  - Auto-size columns

[Files]
Create a new export class and controller endpoint; wire the route; add a new Reports page and client service.

Detailed breakdown:
- New files to be created (backend)
  - laravel-api/app/Exports/EnrolledStudentsExport.php
    - Purpose: Builds the dataset for export with headings and mapping. Implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize.
- New files to be created (backend controller)
  - laravel-api/app/Http/Controllers/Api/V1/ReportsController.php
    - Purpose: Handle GET /api/v1/reports/enrolled-students/export?syid=, validate input, invoke Excel::download(new EnrolledStudentsExport($syid)), set filename.
- Existing files to be modified (backend)
  - laravel-api/routes/api.php
    - Add: Route::get('/reports/enrolled-students/export', [ReportsController::class, 'enrolledStudentsExport'])->middleware('role:registrar,admin');
- New files to be created (frontend)
  - frontend/unity-spa/features/reports/reports.html
    - Purpose: Registrar Reports page UI with TermSelector context and an "Export Enrolled Students" button, error/status display.
  - frontend/unity-spa/features/reports/reports.controller.js
    - Purpose: Angular controller to call service, gather selected term from TermService, trigger file download.
  - frontend/unity-spa/features/reports/reports.service.js
    - Purpose: Angular service calling GET /api/v1/reports/enrolled-students/export with responseType arraybuffer for xlsx.
- Existing files to be modified (frontend)
  - frontend/unity-spa/core/routes.js
    - Add route definition for "#/registrar/reports".
  - frontend/unity-spa/shared/components/sidebar/sidebar.html
    - No structural changes required (link already exists). Verify visibility controlled by vm.canAccess('/registrar/reports').
  - frontend/unity-spa/shared/components/sidebar/sidebar.controller.js
    - Only if needed: ensure canAccess mapping allows '/registrar/reports' for registrar and admin roles (otherwise add mapping).
- Files to be deleted or moved
  - None
- Configuration file updates
  - None (maatwebsite/excel already installed in composer.json)

[Functions]
Add an export endpoint function and Excel mapping.

Detailed breakdown:
- New functions
  - ReportsController@enrolledStudentsExport(Request $request): \Symfony\Component\HttpFoundation\BinaryFileResponse
    - File path: laravel-api/app/Http/Controllers/Api/V1/ReportsController.php
    - Purpose: Validate syid, trigger Excel::download(new EnrolledStudentsExport($syid), 'enrolled-students-YYYYmmdd-HHMMSS.xlsx').
    - Validation: syid: required|integer
  - EnrolledStudentsExport::__construct(int $syid)
    - File path: laravel-api/app/Exports/EnrolledStudentsExport.php
    - Purpose: Accept term id for scoping query.
  - EnrolledStudentsExport::collection(): \Illuminate\Support\Collection
    - Purpose: Execute the join query and return a collection of rows.
    - SQL (conceptual):
      SELECT DISTINCT
        u.strStudentNumber AS student_number,
        u.strFirstname AS first_name,
        u.strLastname AS last_name,
        u.strMiddlename AS middle_name,
        p.strProgramCode AS program_code
      FROM tb_mas_registration r
      JOIN tb_mas_users u ON u.intID = r.intStudentID
      LEFT JOIN tb_mas_programs p
        ON p.intProgramID = COALESCE(r.current_program, u.intProgramID)
      WHERE r.intAYID = :syid AND r.intROG = 1
      ORDER BY u.strStudentNumber DESC
  - EnrolledStudentsExport::headings(): array
    - Returns headers as specified.
  - EnrolledStudentsExport::map($row): array
    - Map collection row to final array [student_number, first_name, last_name, middle_name, program_code].
- Modified functions
  - frontend routes.js: register new route for '/registrar/reports'.
- Removed functions
  - None

[Classes]
Add a new controller and a new export class.

Detailed breakdown:
- New classes
  - App\Http\Controllers\Api\V1\ReportsController
    - Key methods: enrolledStudentsExport()
    - Inheritance: extends Controller
  - App\Exports\EnrolledStudentsExport
    - Key methods: __construct(int $syid), collection(), headings(), map()
    - Implements: Maatwebsite\Excel\Concerns\FromCollection, WithHeadings, WithMapping, ShouldAutoSize
- Modified classes
  - None (SystemLogController retained as reference pattern)
- Removed classes
  - None

[Dependencies]
No new dependencies required.

Details:
- maatwebsite/excel (^3.1) is already present and used by existing SystemLogsExport and SystemLogController::export.
- Use Illuminate\Support\Facades\DB for Query Builder.

[Testing]
Manual end-to-end and API-only verification.

Test file requirements and validation strategies:
- Backend:
  - Curl request:
    - curl -v -G "http://localhost/laravel-api/public/api/v1/reports/enrolled-students/export" --data-urlencode "syid=XXXX" -H "Authorization: Bearer <token>"
    - Expect: 200 OK, Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, with Content-Disposition filename.
    - Open file, verify columns: Student Number, First Name, Last Name, Middle Name, Program Code.
    - Verify program_code correctness:
      - Case A: registration.current_program not null -> use that program's code.
      - Case B: registration.current_program null -> fallback to users.intProgramID program code.
    - Verify enrollment filter: only tb_mas_registration.intROG = 1 and intAYID = syid are included, no extra conditions (e.g., dteRegistered not required).
- Frontend:
  - Navigate to "#/registrar/reports".
  - Ensure TermSelector shows selected term (or use TermService activeTerm).
  - Click "Export Enrolled Students" and ensure browser downloads .xlsx. Verify filename and contents as above.
- Edge cases:
  - No matches: return an empty sheet with only headings.
  - Duplicates across multiple registrations: DISTINCT on student (via DISTINCT student_number) to avoid duplicate rows.
  - Very large datasets: rely on streaming handled by Excel package; keep mapping minimal.

[Implementation Order]
Implement backend first, then wire frontend, following established patterns to minimize friction.

1) Backend: Create EnrolledStudentsExport.php with FromCollection + WithHeadings + WithMapping + ShouldAutoSize and implement robust query:
   - Join r (tb_mas_registration), u (tb_mas_users), p (tb_mas_programs using COALESCE).
   - Filter: r.intAYID = :syid AND r.intROG = 1.
   - DISTINCT by student; order by u.strStudentNumber desc.
2) Backend: Create ReportsController.php with enrolledStudentsExport() validating syid and performing Excel::download(new EnrolledStudentsExport($syid), filename).
3) Backend: Update routes/api.php to register GET /api/v1/reports/enrolled-students/export with middleware role:registrar,admin.
4) Frontend: Create features/reports/reports.service.js providing exportEnrolled(syid) returning an $http request with responseType: 'arraybuffer'.
5) Frontend: Create features/reports/reports.controller.js to read selected term from TermService (TermService.getSelectedTerm().intID) and invoke service, building a download link and filename from Content-Disposition header as in logs export pattern.
6) Frontend: Create features/reports/reports.html with UI mirroring System Logs export UX: shows TermSelector context and an "Export Enrolled Students" button; show progress/errors.
7) Frontend: Update core/routes.js to add '#/registrar/reports' route using the new controller/template.
8) Frontend: Verify sidebar link "#/registrar/reports" already exists and is gated by vm.canAccess('/registrar/reports'); if canAccess map doesn&#39;t authorize registrar/admin yet, update sidebar.controller.js accordingly.
9) QA: Test API with cURL and UI workflow end-to-end; validate Excel file data and headers.
