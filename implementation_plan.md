# Implementation Plan

[Overview]
Enable institutional departments to tag students for deficiencies that automatically generate finance billings, with a new Department Admin role and frontend UI. The solution introduces department-scoped tagging, inline Payment Description creation, and a controlled permission model with department assignments for faculty.

This feature allows designated department administrators to:
- Select a department (registrar, finance, admissions, building_admin, purchasing, academics, clinic, guidance, osas)
- Choose a student (by student_id or student_number) and a term (syid)
- Select an existing Payment Description or create one inline if it does not exist (campus-scoped)
- Set amount (defaulted from Payment Description) and remarks
- Create a deficiency record that always generates a Student Billing row

The implementation fits into the existing Laravel API and AngularJS Unity SPA. Backend service logic reuses StudentBillingService and PaymentDescription facilities, while adding a new persistence for deficiencies and a faculty-to-department mapping. Access control is enforced by role middleware and department scoping based on a new faculty-department assignment table.

[Types]  
Type system changes introduce two new database-backed models and supporting request payloads.

Detailed type definitions, validation, and relationships:
- Enum DepartmentCode (string; constrained values)
  - Allowed codes: registrar, finance, admissions, building_admin, purchasing, academics, clinic, guidance, osas
  - Validation: required, in:[list above]

- Model: StudentDeficiency (table: tb_mas_student_deficiencies)
  - Fields:
    - intID (int, PK, auto-increment)
    - intStudentID (int, required) -> tb_mas_users.intID
    - syid (int, required) -> school-year/term
    - department_code (string, required, in DepartmentCode)
    - payment_description_id (int, nullable) -> payment_descriptions.intID
    - billing_id (int, required) -> tb_mas_student_billing.intID
    - amount (decimal(12,2), required, not zero; signed allowed)
    - description (string, required, max:255)  // ledger/billing description
    - remarks (text, nullable)
    - posted_at (datetime, nullable)
    - campus_id (int, nullable) // optional scope parity with PaymentDescription
    - created_by (int, nullable) -> faculty.intID
    - updated_by (int, nullable) -> faculty.intID
    - created_at, updated_at (timestamps)
  - Relationships:
    - belongsTo Student (tb_mas_users)
    - belongsTo PaymentDescription
    - belongsTo Billing (tb_mas_student_billing)
  - Constraints:
    - FK payment_description_id -> payment_descriptions.intID (nullable)
    - FK billing_id -> tb_mas_student_billing.intID (required)
    - Indexes: (intStudentID, syid), (department_code), (billing_id)

- Model: FacultyDepartment (table: tb_mas_faculty_departments)
  - Purpose: map faculty to allowed department codes for scoping
  - Fields:
    - intID (int, PK, auto-increment)
    - intFacultyID (int, required) -> tb_mas_faculty.intFacultyID or faculties PK
    - department_code (string, required, in DepartmentCode)
    - campus_id (int, nullable)
    - created_at, updated_at (timestamps)
  - Constraints:
    - Unique: (intFacultyID, department_code, campus_id) to avoid duplicates
    - Indexes: (intFacultyID), (department_code)

- API Request DTOs:
  - DepartmentDeficiencyStoreRequest
    - student_id: integer (required_without:student_number)
    - student_number: string (required_without:student_id)
    - term: integer (required) // syid
    - department_code: string (required, in DepartmentCode)
    - payment_description_id: integer (required_without:new_payment_description)
    - new_payment_description: object (required_without:payment_description_id)
      - name: string required, max:128, unique per campus (case-insensitive)
      - amount: numeric nullable, min:0 (defaults to 0 if omitted)
      - campus_id: integer nullable (otherwise resolved from headers or cashier)
    - amount: numeric required (defaults from PD if PD selected; editable; not_in:0)
    - posted_at: datetime nullable
    - remarks: string nullable, max:1000
  - DepartmentDeficiencyUpdateRequest (optional for corrections)
    - amount?: numeric not_in:0
    - posted_at?: datetime
    - remarks?: string max:1000

- API Response DTOs:
  - DepartmentDeficiencyResource
    - id, student_id, syid, department_code, payment_description_id, billing_id,
      amount, description, remarks, posted_at, campus_id, created_by, updated_by, created_at, updated_at

[Files]
Introduce new backend models, service, controller, requests, and migrations. Modify existing route protections and config to expose necessary capabilities to department_admin. Add frontend AngularJS UI page.

Detailed breakdown:
- New files to be created:
  - Backend (Laravel):
    - app/Models/StudentDeficiency.php
      - Eloquent model for tb_mas_student_deficiencies with fillable, casts, relations
    - app/Models/FacultyDepartment.php
      - Eloquent model for tb_mas_faculty_departments
    - app/Services/DepartmentDeficiencyService.php
      - Core business logic for tagging deficiencies, PaymentDescription inline create, StudentBilling row generation, and scoping checks
    - app/Http/Controllers/Api/V1/DepartmentDeficiencyController.php
      - Endpoints: index (list), store, show, update (optional), destroy (optional), meta (options)
    - app/Http/Requests/Api/V1/DepartmentDeficiencyStoreRequest.php
    - app/Http/Requests/Api/V1/DepartmentDeficiencyUpdateRequest.php
    - app/Http/Resources/DepartmentDeficiencyResource.php
    - app/Services/DepartmentContextService.php
      - Helper to resolve allowed departments for current faculty (via mapping table), and resolve campus_id from headers or cashier
    - database/migrations/20xx_xx_xx_xxxxxx_create_tb_mas_student_deficiencies.php
    - database/migrations/20xx_xx_xx_xxxxxx_create_tb_mas_faculty_departments.php
  - Frontend (AngularJS Unity SPA):
    - frontend/unity-spa/features/department/deficiencies/deficiencies.service.js
    - frontend/unity-spa/features/department/deficiencies/deficiencies.controller.js
    - frontend/unity-spa/features/department/deficiencies/deficiencies.html
    - frontend/unity-spa/features/department/deficiencies/index.js (route wiring if needed)
  - Config (optional extension):
    - Update laravel-api/config/departments.php
      - Add 'codes' => [ 'registrar', 'finance', 'admissions', 'building_admin', 'purchasing', 'academics', 'clinic', 'guidance', 'osas' ]

- Existing files to be modified (specific changes):
  - laravel-api/routes/api.php
    - Add Department Deficiency routes under /api/v1/department-deficiencies with middleware('role:department_admin,admin')
    - Extend PaymentDescriptions routes to allow department_admin to list/create:
      - index/store/update/destroy: middleware('role:finance,department_admin,admin') for index/store
      - keep update/destroy to finance/admin only if desired; minimally index+store for department_admin
  - laravel-api/app/Providers/AuthServiceProvider.php
    - Add Gates:
      - department.deficiency.manage($user, $departmentCode)
      - department.deficiency.view($user, $departmentCode)
    - Gate logic uses FacultyDepartment mapping; admin bypass
  - laravel-api/app/Http/Middleware/RequireRole.php
    - No change needed (we will rely on existing role middleware with 'department_admin')
  - laravel-api/config/departments.php
    - Add 'codes' array as above (if not present)
  - frontend/unity-spa/index.html
    - Include new scripts for the feature (controller, service)
  - frontend/unity-spa/shared/components/header/header.controller.js and shared/components/sidebar/sidebar.html
    - Add link visibility for users whose roles include 'department_admin'
    - Add menu entry: "Department Deficiencies"
  - Optional: frontend/unity-spa/core/run.js
    - Register route for '/department/deficiencies'

- Files to be deleted or moved:
  - None

- Configuration file updates:
  - .env values not required
  - departments.php codes array addition as above

[Functions]
Add new endpoints and service methods; modify route guards. No removal.

Detailed breakdown:
- New functions:
  - App\Services\DepartmentDeficiencyService
    - function list(?string $studentNumber, ?int $studentId, ?int $syid, ?string $departmentCode, ?int $campusId, int $page = 1, int $perPage = 25): array
      - Purpose: list deficiencies by optional filters with pagination metadata
    - function store(array $payload, ?int $actorFacultyId = null): array
      - Purpose: orchestrate inline PD creation (when needed), create StudentBilling item via StudentBillingService, and persist StudentDeficiency with linkage; returns normalized deficiency
      - Steps:
        1) Resolve faculty context, campus_id, and allowed departments; assert department_code allowed
        2) Resolve student_id (by id or student_number)
        3) Resolve or create PaymentDescription (campus-scoped)
        4) Create billing with StudentBillingService::create([...]) using description = PD name, amount = payload amount, posted_at, remarks "Deficiency: {dept}" merged with input remarks; no invoice generation
        5) Insert StudentDeficiency row linking billing_id and PD id
        6) Return normalized data
    - function get(int $id): ?array
    - function update(int $id, array $payload, ?int $actorFacultyId = null): ?array
    - function destroy(int $id, ?int $actorFacultyId = null): void
    - function allowedDepartmentsForFaculty(?int $facultyId): array
    - function resolveCampusIdFromRequest(Request $request): ?int
  - App\Http\Controllers\Api\V1\DepartmentDeficiencyController
    - index(Request): JsonResponse // list with filters
    - meta(Request): JsonResponse // returns departments, terms, and PDs for dropdowns
    - store(DepartmentDeficiencyStoreRequest, Request): JsonResponse
    - show(int $id): JsonResponse
    - update(int $id, DepartmentDeficiencyUpdateRequest, Request): JsonResponse
    - destroy(int $id): JsonResponse
- Modified functions:
  - laravel-api/app/Providers/AuthServiceProvider.php
    - boot(): add Gate::define('department.deficiency.manage', ...), Gate::define('department.deficiency.view', ...)
- Removed functions:
  - None

[Classes]
Introduce new Eloquent models and a service; no removals.

Detailed breakdown:
- New classes:
  - App\Models\StudentDeficiency
    - Table: tb_mas_student_deficiencies
    - Key methods: relations (student, paymentDescription, billing)
  - App\Models\FacultyDepartment
    - Table: tb_mas_faculty_departments
    - Key methods: scopeByFaculty, scopeByDepartment
  - App\Services\DepartmentDeficiencyService
    - Methods: orchestrate store/list/update/destroy, scoping helpers
  - App\Http\Requests\Api\V1\DepartmentDeficiencyStoreRequest
  - App\Http\Requests\Api\V1\DepartmentDeficiencyUpdateRequest
  - App\Http\Resources\DepartmentDeficiencyResource
  - App\Services\DepartmentContextService (optional helper used by service/controller)
- Modified classes:
  - App\Providers\AuthServiceProvider
    - Add Gate definitions using FacultyDepartment mapping and admin bypass
- Removed classes:
  - None

[Dependencies]
No new external package dependencies.

- Laravel migrations add two tables
- Reuse existing StudentBillingService, PaymentDescription model/controller, Cashier model for campus resolution
- Route middleware role:department_admin,admin on new endpoints
- Extend PaymentDescription routes to grant department_admin access to list/create

[Testing]
Introduce backend unit/integration tests and light frontend validation.

- Backend:
  - tests/Feature/DepartmentDeficiencyControllerTest.php
    - test_store_creates_pd_billing_deficiency()
    - test_store_rejects_unauthorized_department()
    - test_list_filters_by_department_term_student()
    - test_update_and_destroy_respect_scoping()
  - tests/Unit/DepartmentDeficiencyServiceTest.php
    - test_inline_pd_creation_scoped_by_campus()
    - test_billing_creation_via_service_without_invoice()
  - Gate tests to ensure department scoping
- Frontend:
  - Manual QA checklist
    - Department Admin can open page, see allowed department list
    - Student selector works by student_number and student_id
    - Payment Description dropdown loads campus-scoped PDs; inline create flows to controller and refreshes dropdown
    - Amount defaults from PD, editable
    - Submit creates deficiency and shows in list
  - E2E (if test harness exists): flow from selection to creation and list verification

[Implementation Order]
Implement backend foundations first (DB, service, controller), then wire routes and gates, finally build UI and expose menu links. PaymentDescription access must be extended early for UI.

Numbered steps:
1. Database
   - Create migration for tb_mas_faculty_departments (intID, intFacultyID, department_code, campus_id nullable, timestamps, unique compound index)
   - Create migration for tb_mas_student_deficiencies (columns listed above with FKs and indexes)
2. Models
   - Add FacultyDepartment and StudentDeficiency models with relations and casts
3. Authorization
   - Add Gates in AuthServiceProvider for department.deficiency.view/manage
   - Ensure role 'department_admin' will be created and assignable via existing RoleController
4. Services
   - Implement DepartmentContextService helper (resolve campus_id, allowed departments)
   - Implement DepartmentDeficiencyService with store/list/get/update/destroy and scoping assertions
5. Controllers/Requests/Resources
   - Create DepartmentDeficiencyStoreRequest and UpdateRequest with validation rules
   - Create DepartmentDeficiencyResource to shape API output
   - Create DepartmentDeficiencyController: endpoints index/meta/store/show/update/destroy
6. Routes
   - Register /api/v1/department-deficiencies endpoints with middleware('role:department_admin,admin')
   - Extend /api/v1/payment-descriptions index and store to allow department_admin role
7. Frontend (Unity SPA)
   - Create deficiencies.service.js with API bindings (include X-Faculty-ID headers)
   - Create deficiencies.controller.js for UI logic (load departments, terms, students, PDs, inline PD create; submit deficiency)
   - Create deficiencies.html with form (department, student selector, term, PD dropdown + inline create, amount, remarks) and list
   - Wire routes and include scripts in index.html; add menu link visible to department_admin
8. Smoke Tests
   - Verify end-to-end flow: as department_admin, create PD inline, create deficiency; billing row appears via API; data visible in list
9. Documentation
   - Update README or internal docs with role setup steps: create role 'department_admin', assign via API, configure faculty-department mapping
