# TODO — Department Deficiency Tagging (Critical-Path)

This checklist tracks the implementation according to implementation_plan.md.

## Task Progress

- [ ] Step 1: Create DB migrations for tb_mas_faculty_departments and tb_mas_student_deficiencies
  - Migration A: tb_mas_faculty_departments
    - intID (PK), intFacultyID (FK-ish), department_code (enum codes), campus_id (nullable), timestamps
    - Unique: (intFacultyID, department_code, campus_id)
  - Migration B: tb_mas_student_deficiencies
    - intID (PK), intStudentID, syid, department_code, payment_description_id (nullable), billing_id (required), amount (decimal 12,2, signed, not 0), description (string 255), remarks (text nullable), posted_at (datetime nullable), campus_id (nullable), created_by, updated_by, timestamps
    - Indexes: (intStudentID, syid), (department_code), (billing_id)
- [ ] Step 2: Add Eloquent models FacultyDepartment and StudentDeficiency with relations and casts
  - app/Models/FacultyDepartment.php
    - $table, $fillable, scopes: byFaculty, byDepartment, allowedForFaculty($facultyId, $campusId? = null)
  - app/Models/StudentDeficiency.php
    - $table = 'tb_mas_student_deficiencies', $fillable, $casts, relations: paymentDescription(), billing()
- [ ] Step 3: Define authorization gates in App\Providers\AuthServiceProvider
  - department.deficiency.view and department.deficiency.manage using FacultyDepartment mapping and admin bypass
- [ ] Step 4: Add DepartmentContextService
  - resolveCampusId(Request) using X-Campus-ID/X-Faculty-ID -> Cashier
  - departmentCodes() from config('departments.codes')
- [ ] Step 5: Implement DepartmentDeficiencyService
  - list(?studentNumber, ?studentId, ?syid, ?departmentCode, ?campusId, int $page=1, int $perPage=25, array $allowedDepartments=[])
  - get(int $id)
  - store(array $payload, array $ctx={ 'faculty_id'?, 'campus_id'? })
    - Resolve student, resolve/create PaymentDescription (campus-scoped), call StudentBillingService::create, insert StudentDeficiency
    - Remarks merged with "Deficiency: {dept}"
  - update(int $id, array $payload, ?int $actorFacultyId)
  - destroy(int $id, ?int $actorFacultyId)
  - Helpers: resolveStudentId(), normalizeRow(), mergeRemarks()
- [ ] Step 6: HTTP layer: Requests/Resource/Controller
  - DepartmentDeficiencyStoreRequest, DepartmentDeficiencyUpdateRequest
    - Validate: student_id or student_number; term (syid); department_code in allowed codes; payment_description_id or new_payment_description{name, amount, campus_id}; amount not 0; posted_at; remarks
  - DepartmentDeficiencyResource
  - DepartmentDeficiencyController: index, meta, store, show, update, destroy
- [ ] Step 7: Routes and PaymentDescription access
  - routes/api.php: /api/v1/department-deficiencies with middleware('role:department_admin,admin')
  - Extend /api/v1/payment-descriptions index/store to allow department_admin role
- [ ] Step 8: Minimal frontend (Unity SPA)
  - features/department/deficiencies/
    - deficiencies.service.js (API bindings)
    - deficiencies.controller.js (load dropdowns, inline PD create, submit)
    - deficiencies.html (form + list)
  - Wire scripts in index.html, route in core/run.js, menu visibility in shared/components/sidebar
- [ ] Step 9: Critical-path testing
  - Backend:
    - POST /api/v1/department-deficiencies (with inline PD create and with existing PD)
    - GET /api/v1/department-deficiencies (filters: student, term, department)
    - GET /api/v1/department-deficiencies/{id}
  - Authorization:
    - department_admin with assigned dept allowed; disallow other depts; admin bypass works
  - PaymentDescriptions:
    - department_admin can GET/POST
  - Billing linkage:
    - Billing row created and referenced by deficiency
- [ ] Step 10: Documentation
  - Role setup: create 'department_admin'
  - Faculty-department mapping procedures
  - Header requirements: X-Faculty-ID / X-Campus-ID

## Notes

- Departments: registrar, finance, admissions, building_admin, purchasing, academics, clinic, guidance, osas
- Use campus scoping for PaymentDescription and default amount from PD but allow overrides
- Student selection supports student_id or student_number
- Every deficiency must link to a specific term (syid) and produce a billing row
