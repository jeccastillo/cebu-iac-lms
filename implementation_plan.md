# Implementation Plan

[Overview]
Create a non-student payments module that enables school tenants to pay through the existing cashier module. Payments for tenants will reference tb_mas_payee instead of tb_mas_users, including full CRUD for payees, cashier payment support for either student or payee, and OR PDF updates to show payee details when applicable.

This implementation extends the current finance and cashier subsystems with a parallel flow for non-student entities (tenants). We will introduce a Payee model mapped to the legacy table tb_mas_payee, expose an admin/finance-admin CRUD API for payee management, and update cashier payment creation to accept either student_id or payee_id. Payments created for payees will store a nullable payee_id on payment_details and omit sy_reference as requested. OR PDF generation will prefer payee data when payment_details.payee_id is set, falling back to student details as before. System logging will be added to all create/update/delete events of payees and to cashier payment creation for auditability.

[Types]  
Add a nullable foreign key column payment_details.payee_id and new types for Payee payloads and responses.

Detailed type specifications:
- Table: tb_mas_payee (legacy, pre-existing; used as read/write store)
  - id: int, PK, auto-increment
  - id_number: varchar(40), required
  - firstname: varchar(99), required
  - lastname: varchar(99), nullable
  - middlename: varchar(99), nullable
  - tin: varchar(99), nullable
  - address: text, nullable
  - contact_number: varchar(40), nullable
  - email: varchar(99), required

- New Model: App\Models\Payee
  - protected $table = 'tb_mas_payee';
  - protected $primaryKey = 'id';
  - $timestamps = false (unless columns exist; per screenshot appears none)
  - Fillable: ['id_number','firstname','lastname','middlename','tin','address','contact_number','email']
  - Relationships:
    - paymentDetails(): hasMany(PaymentDetail::class, 'payee_id', 'id')

- Modified Table: payment_details
  - payee_id: integer, nullable, FK to tb_mas_payee.id (no cascade delete; set null on payee removal)
  - Validation/Rules:
    - When payee_id is set: student_information_id may be null; sy_reference nullable (omitted for tenants)
    - When student_information_id is set: existing behavior continues; sy_reference required
    - OR invoice number assignment rules remain the same

- API Request DTOs:
  - PayeeStoreRequest:
    - id_number: required|string|max:40|unique:tb_mas_payee,id_number
    - firstname: required|string|max:99
    - lastname: nullable|string|max:99
    - middlename: nullable|string|max:99
    - tin: nullable|string|max:99
    - address: nullable|string
    - contact_number: nullable|string|max:40
    - email: required|email|max:99
  - PayeeUpdateRequest:
    - same as store; id_number unique except current id

  - CashierPaymentStoreRequest (modified):
    - student_id: required_without:payee_id|integer|exists:tb_mas_users,intID
    - payee_id: required_without:student_id|integer|exists:tb_mas_payee,id
    - term: required_if:student_id,*,nullable|integer (nullable and omitted when paying as payee)
    - mode: required|in:or,invoice,none
    - amount: required|numeric|gt:0
    - description: required|string|max:255
    - mode_of_payment_id: required|integer|exists:payment_modes,id
    - method: nullable|string|max:100
    - remarks: required|string|max:1000
    - posted_at: nullable|date
    - campus_id: nullable|integer
    - invoice_id: sometimes|nullable|integer
    - invoice_number: sometimes|nullable|integer
    - or_date: sometimes|nullable|date
    - convenience_fee: sometimes|nullable|numeric|min:0

[Files]
Introduce a new Payee module with CRUD and modify existing controllers/services to support payee-based payments and OR PDF rendering.

Detailed breakdown:

- New files to be created:
  - laravel-api/app/Models/Payee.php
    - Eloquent model for tb_mas_payee
  - laravel-api/app/Http/Controllers/Api/V1/PayeeController.php
    - CRUD endpoints for payee management; guarded by role:admin,finance_admin
    - Uses SystemLogService::log for create/update/delete
  - laravel-api/app/Http/Requests/Api/V1/PayeeStoreRequest.php
    - Validation for creating a payee
  - laravel-api/app/Http/Requests/Api/V1/PayeeUpdateRequest.php
    - Validation for updating a payee
  - laravel-api/app/Http/Resources/PayeeResource.php
    - Normalized API shape for payee responses
  - laravel-api/database/migrations/20xx_xx_xx_xxxxxx_add_payee_id_to_payment_details.php
    - Adds nullable payee_id, FK to tb_mas_payee(id). Safe up/down.

- Existing files to be modified (specific changes):
  - laravel-api/routes/api.php
    - Add routes:
      - GET /api/v1/payees
      - POST /api/v1/payees
      - GET /api/v1/payees/{id}
      - PATCH /api/v1/payees/{id}
      - DELETE /api/v1/payees/{id}
      - Protect with middleware('role:admin,finance_admin')
  - laravel-api/app/Http/Requests/Api/V1/CashierPaymentStoreRequest.php
    - Relax validation: accept payee_id OR student_id; make term required only for student payments; nullable otherwise
  - laravel-api/app/Http/Controllers/Api/V1/CashierController.php
    - In createPayment():
      - Support either student_id or payee_id
      - When payee_id is provided:
        - Do not require or use student-dependent lookups (tb_mas_users)
        - Do not require sy_reference; set null
        - Fill optional name/email/contact columns (first_name, middle_name, last_name, email_address, contact_number) from tb_mas_payee when columns exist
        - Persist payment_details.payee_id = {payee_id}
      - Continue existing invoice/OR logic, pointer increments, and applicant logic only for student payments
  - laravel-api/app/Services/PaymentDetailAdminService.php
    - detectColumns(): include 'payee_id' => $col('payee_id')
    - selectList(): include payee_id when column exists
    - normalizeRow(): include 'payee_id' (int|null) and 'source' unchanged
  - laravel-api/app/Http/Controllers/Api/V1/FinanceController.php
    - orPdf():
      - Resolve payment_detail row and prefer payee info when payee_id is present:
        - Lookup tb_mas_payee.id, read firstname/lastname/middlename, tin, address
        - Render RECEIVED FROM using payee name/address; TIN from payee
      - Fallback to student as currently implemented
  - laravel-api/app/Models/PaymentDetail.php
    - Add relationship:
      - public function payee() { return $this->belongsTo(\App\Models\Payee::class, 'payee_id', 'id'); }
  - laravel-api/app/Services/Pdf/OfficialReceiptPdf.php
    - No schema changes, but ensure renderer supports any non-student labels if needed (existing DTO already generic)

- Files to be deleted or moved
  - None

- Configuration file updates
  - None required

[Functions]
Add Payee CRUD and augment cashier payment creation and OR PDF logic.

Detailed breakdown:

- New functions:
  - App\Http\Controllers\Api\V1\PayeeController
    - index(Request $request): list/search with pagination and q filter (id_number, firstname, lastname, email)
    - show(int $id)
    - store(PayeeStoreRequest $request): create; SystemLogService::log('create','Payee',id,null,newValues,$request)
    - update(int $id, PayeeUpdateRequest $request): update; SystemLogService::log('update','Payee',id,old,new,$request)
    - destroy(int $id): delete; on delete set related payment_details.payee_id = null; SystemLogService::log('delete','Payee',id,old,null,$request)

- Modified functions:
  - App\Http\Requests\Api\V1\CashierPaymentStoreRequest::rules()
    - Change validation to:
      - 'student_id' => ['required_without:payee_id','integer','exists:tb_mas_users,intID']
      - 'payee_id'   => ['required_without:student_id','integer','exists:tb_mas_payee,id']
      - 'term'       => ['nullable','integer','required_if:student_id,*']  (or programmatically enforce in controller)
  - App\Http\Controllers\Api\V1\CashierController::createPayment()
    - Branch on payee_id vs student_id:
      - If payee_id present:
        - Bypass applicant_data hooks, student_number lookups, registration/enrollment toggles
        - Set sy_reference to null
        - Fill name/email/contact columns from tb_mas_payee if columns exist
        - Set 'payee_id' in insert when payment_details.payee_id column exists
    - Continue pointer logic (OR/invoice) unchanged
  - App\Services\PaymentDetailAdminService
    - detectColumns(): add 'payee_id'
    - selectList(): include 'payee_id'
    - normalizeRow(): include 'payee_id'
  - App\Http\Controllers\Api\V1\FinanceController::orPdf()
    - Resolve RECEIVED FROM:
      - If payment_details.payee_id is non-null (resolved via PaymentDetailAdminService::detectColumns() and follow-up fetch), then:
        - name: lastname, firstname middlename (uppercased as in student path)
        - tin: tb_mas_payee.tin (or empty)
        - address: tb_mas_payee.address
      - else current student resolution as-is

- Removed functions:
  - None

[Classes]
Add new Payee Eloquent model and supporting controller/request/resource classes.

Detailed breakdown:
- New classes:
  - App\Models\Payee
    - Table: tb_mas_payee; key: id; timestamps: false
    - Key methods: paymentDetails() relationship
  - App\Http\Controllers\Api\V1\PayeeController
    - Inherits Controller
    - Methods: index, show, store, update, destroy
    - Middleware: role:admin,finance_admin
  - App\Http\Requests\Api\V1\PayeeStoreRequest
    - Rules as described
  - App\Http\Requests\Api\V1\PayeeUpdateRequest
    - Rules as described
  - App\Http\Resources\PayeeResource
    - Maps to: id, id_number, firstname, middlename, lastname, tin, address, contact_number, email

- Modified classes:
  - App\Http\Requests\Api\V1\CashierPaymentStoreRequest (rules change)
  - App\Http\Controllers\Api\V1\CashierController (createPayment branch for payees)
  - App\Services\PaymentDetailAdminService (column mapping extensions)
  - App\Http\Controllers\Api\V1\FinanceController (orPdf payee precedence)
  - App\Models\PaymentDetail (add payee() relationship)

- Removed classes:
  - None

[Dependencies]
No external package dependencies required.

- Use existing SystemLogService for Payee CRUD logging and payment logging.
- Use existing role middleware; grant access to admin and finance admin roles as required.
- No composer changes.

[Testing]
Add coverage for payee CRUD and non-student cashier payments.

- New test scripts (optional parity with existing CLI tests):
  - laravel-api/scripts/test_payees_api.php
    - Create, list, update, delete payees; assert SystemLog entries exist (best-effort)
  - laravel-api/scripts/test_cashier_nonstudent_payment_entry.php
    - Create a payee
    - Create cashier ranges if needed
    - Create payment with payee_id, mode='or' and verify:
      - payment_details row: payee_id set, student_information_id null, sy_reference null, OR assigned correctly
      - Optional name/email/contact columns populated when present
    - Fetch OR PDF and ensure RECEIVED FROM name/address come from payee

- PHPUnit Feature tests (if desired):
  - Feature/PayeeControllerTest.php (CRUD happy-path + validation failures)
  - Feature/CashierNonStudentPaymentTest.php (happy path + validation)

- Manual validation:
  - Verify API responses and OR PDF content for both student and payee-based payments

[Implementation Order]
Implement DB changes first, then API surface, then controller/service modifications, followed by testing scripts and verification.

1. Migration: add nullable payment_details.payee_id (FK to tb_mas_payee.id; on delete set null)
2. Model: create App\Models\Payee
3. Requests/Resource/Controller: implement PayeeStoreRequest, PayeeUpdateRequest, PayeeResource, PayeeController
4. Routes: register /api/v1/payees endpoints with role:admin,finance_admin
5. Request Validation: modify CashierPaymentStoreRequest to accept student_id OR payee_id and make term nullable/required_if student
6. CashierController::createPayment: implement non-student branch (payee flow), ensure no student-specific hooks run for payees; set payee_id in insert; populate optional columns from payee
7. PaymentDetailAdminService: add payee_id to detectColumns/select/normalize
8. PaymentDetail model: add payee() relationship
9. FinanceController::orPdf: prefer payee details when payment_details.payee_id is set; fallback to student
10. Test scripts: add test_payees_api.php and test_cashier_nonstudent_payment_entry.php; dry-run locally
11. Security & logs: verify all create/update/delete for payees are logged using SystemLogService
12. Documentation: brief README section in plans or commit description to onboard users

task_progress Items:
- [ ] Step 1: Create migration to add payment_details.payee_id (nullable FK to tb_mas_payee.id)
- [ ] Step 2: Implement App\Models\Payee
- [ ] Step 3: Implement PayeeStoreRequest, PayeeUpdateRequest, PayeeResource
- [ ] Step 4: Implement PayeeController with CRUD + SystemLog integration and role middleware
- [ ] Step 5: Register /api/v1/payees routes in routes/api.php
- [ ] Step 6: Update CashierPaymentStoreRequest rules for payee_id vs student_id and term behavior
- [ ] Step 7: Update CashierController::createPayment to handle payee branch (no sy_reference; set payee_id; fill optional columns)
- [ ] Step 8: Extend PaymentDetailAdminService detectColumns/select/normalize for payee_id
- [ ] Step 9: Add payee() relation to PaymentDetail model
- [ ] Step 10: Update FinanceController::orPdf to prefer payee data when available
- [ ] Step 11: Add CLI test scripts for Payee CRUD and non-student payments; run local checks
- [ ] Step 12: Smoke-test OR/Invoice number pointer behavior for payee payments
- [ ] Step 13: Verify SystemLog entries for payee CRUD and payments; finalize docs
