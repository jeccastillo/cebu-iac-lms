# Department Deficiency Tagging — Implementation TODO

task_progress Items:
- [ ] Step 1: Backend migrations — create tb_mas_faculty_departments and tb_mas_student_deficiencies
- [ ] Step 2: Backend models — FacultyDepartment and StudentDeficiency
- [ ] Step 3: Authorization — add Gates in AuthServiceProvider for department.deficiency.view/manage
- [ ] Step 4: Context helpers — DepartmentContextService (resolve campus_id, allowed departments)
- [ ] Step 5: Core service — DepartmentDeficiencyService (store/list/get/update/destroy with scoping and billing linkage)
- [ ] Step 6: HTTP layer — Requests, Resource, Controller for Department Deficiencies
- [ ] Step 7: Routes — register /api/v1/department-deficiencies endpoints; extend PaymentDescriptions access for department_admin
- [ ] Step 8: Frontend — Department Deficiency page (service/controller/html), header/sidebar visibility, script includes/route
- [ ] Step 9: Critical-path testing — exercise primary API endpoints and core UI interactions

Scope of Testing (per user confirmation): Critical-path testing only

Backend/API critical-path tests:
- POST /api/v1/department-deficiencies creates:
  - Inline PaymentDescription when requested
  - StudentBilling row (no invoice)
  - StudentDeficiency row linked to billing_id and payment_description_id
- GET /api/v1/department-deficiencies lists newly created item (filters: student_id/student_number, term, department_code)
- GET /api/v1/department-deficiencies/{id} returns the created item
- Role middleware role:department_admin,admin on new endpoints
- Department scoping: department_admin can only act for assigned department_code
- PaymentDescriptions: department_admin can list and create (campus-scoped)

Frontend critical-path tests:
- New Department Deficiency page loads with department_admin role
- Select department from assigned list, choose student (id or number), choose term, select/create PaymentDescription
- Amount defaults from PaymentDescription and is editable
- Submit deficiency → success toast and appears in list

Notes:
- We will use X-Faculty-ID headers for department scoping and campus resolution (consistent with existing patterns).
- Admin bypass applies via Gates; department_admin requires FacultyDepartment mapping entry.
