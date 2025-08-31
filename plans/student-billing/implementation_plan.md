# Implementation Plan

[Overview]
Introduce Student Billing so Finance can add ad-hoc charges or credits per student per term. These entries will be stored in a new table tb_mas_student_billing and automatically included in the tuition computation as “other payments” (additional) without affecting installment increase calculations.

This feature enables Finance to record miscellaneous billable items (e.g., ID replacement, penalty fees, manual adjustments) scoped by student and term (syid). The TuitionService compute pipeline will aggregate these rows into the existing additional bucket (items.additional and summary.additional_total). Access is controlled: Finance/Admin can manage billing rows, while read is available via tuition/ledger flows. A dedicated SPA screen under Finance will allow Finance/Admin to search, add, edit, and delete billing entries.

[Types]  
Add new Student Billing data structures and refine compute payload composition.

- Database schema: tb_mas_student_billing
  - intID: int unsigned, PK, auto-increment
  - intStudentID: int unsigned, required; FK to tb_mas_users.intID (not enforced, consistent with legacy style)
  - syid: int unsigned, required; term/school year id
  - description: varchar(255), required; free text shown in breakdown
  - amount: decimal(12,2), required; positive for charges, negative for credits
  - posted_at: datetime nullable; when it should take effect or logged (optional)
  - remarks: text nullable
  - created_by: int unsigned nullable; user id who created (optional)
  - updated_by: int unsigned nullable
  - created_at / updated_at: timestamps

- API DTOs
  - StudentBillingItem
    - id: number (maps to intID)
    - student_id: number
    - syid: number
    - description: string
    - amount: number (two-decimal)
    - posted_at?: string|null (ISO or Y-m-d H:i:s)
    - remarks?: string|null
    - created_at?: string
    - updated_at?: string
  - StudentBillingCreate
    - student_id: number (required)
    - syid: number (required)
    - description: string (required, max 255)
    - amount: number (required, can be negative; disallow 0)
    - posted_at?: string|null
    - remarks?: string|null
  - StudentBillingUpdate
    - description?: string (max 255)
    - amount?: number (can be negative; disallow 0)
    - posted_at?: string|null
    - remarks?: string|null

Validation rules:
- student_id: required|integer|exists:tb_mas_users,intID (soft; match project style, may fallback to presence)
- syid: required|integer|exists:tb_mas_sy,intID (soft check or presence)
- description: required|string|max:255
- amount: required|numeric|not_in:0
- posted_at: sometimes|nullable|date
- remarks: sometimes|nullable|string

[Files]
Introduce new Laravel API components, one migration, one model, new controller/service/requests, extend routes, integrate TuitionService aggregation, and add a new Finance SPA feature.

- New backend files
  - laravel-api/database/migrations/{timestamp}_create_tb_mas_student_billing.php
    - Create table tb_mas_student_billing as specified above with indexes:
      - index(intStudentID), index(syid), composite index (intStudentID, syid)
  - laravel-api/app/Models/StudentBilling.php
    - Eloquent model for tb_mas_student_billing (primaryKey=intID, timestamps=true, fillable fields)
  - laravel-api/app/Http/Requests/Api/V1/StudentBillingStoreRequest.php
    - Validation for create
  - laravel-api/app/Http/Requests/Api/V1/StudentBillingUpdateRequest.php
    - Validation for update (partial)
  - laravel-api/app/Services/StudentBillingService.php
    - Encapsulate CRUD and list operations with role-aware usage in controllers
  - laravel-api/app/Http/Controllers/Api/V1/StudentBillingController.php
    - Endpoints: index, store, update, destroy, show (optional)
  - laravel-api/scripts/test_student_billing.php
    - Smoke script to seed/list basic flows (optional utility)

- Existing backend to modify
  - laravel-api/routes/api.php
    - Register:
      - GET /api/v1/finance/student-billing (role: finance,admin; allow registrar read if needed via query param or separate read route if later required)
      - POST /api/v1/finance/student-billing (role: finance,admin)
      - PUT /api/v1/finance/student-billing/{id} (role: finance,admin)
      - DELETE /api/v1/finance/student-billing/{id} (role: finance,admin)
  - laravel-api/app/Services/TuitionService.php
    - In compute(), after existing additional items (foreign/thesis/late/new student packs), load tb_mas_student_billing rows by (student_id, syid), append to items.additional, and add to additional_total. Ensure these are excluded from any installment increases (they already are if additional bucket is not increased).

- New frontend files (AngularJS)
  - frontend/unity-spa/features/finance/student-billing/list.html
    - Finance-only screen: filters (student number, term), table of items (description, amount, posted_at), Add/Edit/Delete modal
  - frontend/unity-spa/features/finance/student-billing/student-billing.controller.js
    - Controller: load term, search, open modal, create/update/delete with toast feedback
  - frontend/unity-spa/features/finance/student-billing/student-billing.service.js
    - Service: HTTP calls to new Laravel endpoints

- Existing frontend to modify
  - frontend/unity-spa/core/routes.js
    - Add route .when('/finance/student-billing', { templateUrl: 'features/finance/student-billing/list.html', controller: 'FinanceStudentBillingController', controllerAs: 'vm', requiredRoles: ['finance','admin'] })
  - frontend/unity-spa/shared/components/sidebar/sidebar.controller.js
    - Under Finance group, add { label: 'Student Billing', path: '/finance/student-billing' }
  - frontend/unity-spa/shared/components/sidebar/sidebar.html
    - No structural change; links render from controller config

[Functions]
Add CRUD functions for Student Billing, integrate read into tuition compute, and wire SPA actions.

- New backend functions
  - StudentBillingService
    - list(?string $studentNumber, ?int $studentId, ?int $syid): array
    - get(int $id): ?array
    - create(array $payload, int $actorId): array
    - update(int $id, array $payload, int $actorId): array
    - delete(int $id): void
  - StudentBillingController
    - index(Request $request): JsonResponse
      - Query params: student_number?, student_id?, term (syid) required for scoping results
    - store(StudentBillingStoreRequest $request): JsonResponse
    - update(int $id, StudentBillingUpdateRequest $request): JsonResponse
    - destroy(int $id): JsonResponse
    - show(int $id): JsonResponse (optional; for editing with direct link)

- Modified backend functions
  - TuitionService::compute(string $studentNumber, int $syid, ?int $discountId = null, ?int $scholarshipId = null): array
    - Load rows from tb_mas_student_billing where intStudentID = user.intID AND syid = $syid
    - For each row, append to $itemsAdditional: ['name' => description, 'amount' => round(amount, 2)]
    - Sum these into $additionalTotal (preserve sign; credits reduce total)
    - Ensure no change to computeInstallments so additional is not increased by any scheme

- New frontend functions
  - FinanceStudentBillingController
    - init(), search(), add(), edit(item), save(), remove(item), resetFilters()
  - FinanceStudentBillingService
    - list(params), create(payload), update(id, payload), delete(id)

[Classes]
Introduce new Laravel classes for Student Billing and wire into existing patterns.

- New classes
  - App\Models\StudentBilling
    - table = 'tb_mas_student_billing', primaryKey = 'intID', timestamps = true, fillable
  - App\Http\Requests\Api\V1\StudentBillingStoreRequest
    - authorize(): role check handled via middleware; rules() as specified
  - App\Http\Requests\Api\V1\StudentBillingUpdateRequest
    - rules(): partial update validation
  - App\Services\StudentBillingService
    - Encapsulate all DB operations and normalization
  - App\Http\Controllers\Api\V1\StudentBillingController
    - Methods: index, store, update, destroy, show

- Modified classes
  - App\Services\TuitionService
    - Update compute() to aggregate student billing rows into additional items and totals

[Dependencies]
No external dependencies are required.

- Leverage existing role middleware: RequireRole (role:finance,admin) for write; optionally allow registrar read by dedicated read-only endpoint later if needed.
- Use Illuminate DB and Schema as existing codebase standards.

[Testing]
Adopt manual testing and minimal scripts for smoke checks.

- Backend manual/API tests
  1) POST /api/v1/finance/student-billing to create entries (positive and negative amounts); expect 201 JSON with created item.
  2) GET /api/v1/finance/student-billing?student_number=...&amp;term=...; expect list scoped by student+syid only.
  3) PUT /api/v1/finance/student-billing/{id}; update description/amount/posted_at/remarks; expect 200 JSON updated item.
  4) DELETE /api/v1/finance/student-billing/{id}; expect 204 no content.
  5) Compute integration: POST /api/v1/unity/tuition-preview (or GET /api/v1/tuition/compute when applicable) and verify:
     - Each student billing row appears in items.additional as a separate line with its description and signed amount.
     - summary.additional_total includes the signed sum of student billing items in addition to existing foreign/thesis/late/new-student packs.
     - Installments exclude any increases on additional (unchanged compared to current behavior).

- Frontend manual tests
  1) Access control: Finance/Admin can open /#/finance/student-billing; others cannot.
  2) Create/edit/delete flows with form validation and toast notifications.
  3) Search by student number and term; paging if necessary.
  4) Cross-check: After creating entries, open Tuition Preview/Registration Viewer/Cashier Viewer to confirm the additional lines reflect student billing amounts.

- Optional script
  - laravel-api/scripts/test_student_billing.php to seed a row and request tuition preview to verify end-to-end additional aggregation.

[Implementation Order]
Implement APIs and DB first, then backend integration, then frontend UI.

1) Migration
   - Create {timestamp}_create_tb_mas_student_billing.php to define schema and indexes; run migration.
2) Model
   - Add App\Models\StudentBilling with fillable: ['intStudentID','syid','description','amount','posted_at','remarks','created_by','updated_by'].
3) Requests
   - Add StudentBillingStoreRequest and StudentBillingUpdateRequest with validation rules in [Types].
4) Service
   - Add StudentBillingService with list/get/create/update/delete.
5) Controller
   - Add StudentBillingController with routes: index/store/update/destroy/show; guard via middleware role:finance,admin (GET index can later be extended to allow registrar read as needed).
6) Routes
   - Update routes/api.php to register the new endpoints in the v1 group under Finance.
7) Tuition integrate
   - Modify TuitionService::compute():
     - Query tb_mas_student_billing for (intStudentID = user.intID, syid = $syid)
     - Append each row to $itemsAdditional and include in $additionalTotal
     - Do not alter computeInstallments; additional remains excluded from increases.
8) Frontend service
   - Create student-billing.service.js with list/create/update/delete methods.
9) Frontend controller and view
   - Create student-billing.controller.js and list.html with filters, table, and modal for CRUD; restrict via requiredRoles ['finance','admin'].
10) Frontend routes and sidebar
    - Update core/routes.js and sidebar.controller.js to add "Student Billing" under Finance.
11) QA and fixes
    - Verify role gating, validation errors, correct aggregation in tuition compute, and UI usability.
