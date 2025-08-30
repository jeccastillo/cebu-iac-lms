# Implementation Plan

[Overview]
Add a payment-entry function on the Cashier Admin page that allows a user to input a student payment and save it into the payment_details table, using the next assigned or_number or invoice_number from tb_mas_cashiers for that cashier. The system must atomically reserve and persist the number, store the payment row with required metadata, and auto-increment the cashier&#39;s current pointer.

This implementation extends the existing Cashier Admin module with a per-cashier "Add Payment" flow in the SPA. A new backend endpoint will receive validated input (student selection, term/SYID, amount, description, method, required remarks) and persist a row into payment_details. The endpoint will detect table/column variants (or_no vs or_number, method vs payment_method, paid_at vs date vs created_at, invoice_number) and write to what exists. The endpoint will also ensure the chosen number has not been used (re-check usage), then update the cashier&#39;s or_current or invoice_current within a transaction for consistency.

[Types]  
Adds a new request/response schema for payment creation.

Detailed type definitions:
- API Request (CashierPaymentStoreRequest payload)
  - student_id: int (required) – selected student (tb_mas_users.intID)
  - term: int (required) – SYID (tb_mas_sy.intID). Save sy_reference as SYID (per instruction).
  - mode: enum(&#39;or&#39;, &#39;invoice&#39;) (required) – which numbering track to use.
  - amount: number (required) – maps to payment_details.subtotal_order.
  - description: string (required) – e.g., Tuition Payment, Reservation Payment, Others.
  - method: string|null (optional) – maps to payment_details.method or payment_method when present.
  - remarks: string (required) – reason/notes for this payment.
  - posted_at: string (optional, ISO datetime or Y-m-d H:i:s) – maps to paid_at/date/created_at fallback.
  - campus_id: int|null (optional) – default to global selected campus on the SPA; if provided, maps to payment_details.student_campus when present.
- API Response
  - { success: true, data: { id: number, number_used: number, mode: &#39;or&#39;|&#39;invoice&#39;, cashier_id: number } }
- Internal types (DB columns for payment_details; detected at runtime)
  - number columns:
    - OR: or_no or or_number (one of them may exist)
    - Invoice: invoice_number (when mode=&#39;invoice&#39;)
  - method columns: method or payment_method
  - date columns: paid_at or date or created_at (priority in this order)
  - other columns used if present:
    - student_information_id (required)
    - student_number (optional; derive from tb_mas_users.strStudentNumber)
    - status (set to &#39;Paid&#39;)
    - description, subtotal_order, sy_reference (SYID), student_campus (if present)
    - remarks (required)

[Files]
Introduce a new Laravel Request, a new endpoint under CashierController, minimal Service additions, and SPA UI additions (controller, service, template).

Detailed breakdown:
- New files to be created
  - laravel-api/app/Http/Requests/Api/V1/CashierPaymentStoreRequest.php
    - Validates: student_id (exists in tb_mas_users), term (integer), mode in {or, invoice}, amount numeric > 0, description string, remarks string required, method optional string, posted_at optional date format, campus_id optional integer.
  - laravel-api/scripts/test_cashier_payment_entry.php (optional developer smoke)
    - Internal-kernel test that posts a payment to /api/v1/cashiers/{id}/payments and asserts pointer increment and row presence in payment_details.

- Existing files to be modified
  - laravel-api/app/Http/Controllers/Api/V1/CashierController.php
    - Add: public function createPayment(int $id, CashierPaymentStoreRequest $request)
      - Transactional logic:
        1) Load cashier row; determine mode; read current pointer (or_current/invoice_current), start/end, campus_id.
        2) Validate current pointer is within [start,end].
        3) Re-validate usage with CashierService::validateRangeUsage(mode, current, current).
        4) Build insert payload for payment_details with dynamic column detection (method/payment_method, or_no/or_number, invoice_number, paid_at/date/created_at).
        5) Insert row; capture inserted id.
        6) Increment the corresponding cashier pointer (current+1) and save.
        7) Return success response with inserted id and number used.
    - Route registration (see routes/api.php).
  - laravel-api/app/Services/CashierService.php
    - Add helper: public function nextNumberOrFail(Cashier $row, string $type): array { number:int, start:int, end:int }
      - Ensures number within range; returns {number,start,end}.
    - Reuse existing validateRangeUsage for one-number check {start=end=number}.
  - laravel-api/routes/api.php
    - Add route: POST /api/v1/cashiers/{id}/payments → CashierController@createPayment → middleware(&#39;role:cashier_admin,admin&#39;).
  - frontend/unity-spa/features/cashiers/cashiers.service.js
    - Add method: createPayment(cashierId, payload) → POST /cashiers/{id}/payments.
  - frontend/unity-spa/features/cashiers/cashiers.controller.js
    - Add per-row modal state: vm.payments[row.id] = { open, student, term, mode:&#39;or&#39;, amount, description, method, remarks, posted_at }.
    - Use StudentsService.listAll() to populate student selector; term selection via existing global "active term" if available, else a simple numeric input/select (vm.term or from a shared service).
    - On submit: compose payload (include campus_id = (vm.selectedCampus.id || intID) for student_campus), call CashiersService.createPayment, show success, refresh stats, and reload row; report errors.
  - frontend/unity-spa/features/cashiers/list.html
    - Add "Add Payment" button in Actions column (per row).
    - Add modal form with:
      - Student (select, required)
      - Term/SYID (number/select, required)
      - Mode (radio: OR / Invoice)
      - Amount (number, required)
      - Description (text, required)
      - Method (text, optional)
      - Remarks (textarea, required)
      - Posted At (datetime-local, optional)
      - Read-only "Using Number" preview (current pointer based on selected mode)
      - Submit/Cancel buttons
- Files to be deleted or moved
  - None
- Configuration updates
  - None

[Functions]
Adds a new API function for payment creation; extends Angular service and controller with helpers for creating payments and refreshing stats.

Detailed breakdown:
- New functions
  - CashierController::createPayment(int $id, CashierPaymentStoreRequest $request)
    - Signature: public function createPayment($id, CashierPaymentStoreRequest $request)
    - Purpose: Atomically create a payment_details row using cashier&#39;s next number and increment the pointer.
    - Steps:
      - Load cashier by id; assert mode.
      - Determine columns present in payment_details using Schema::hasColumn (or_no/or_number; invoice_number; method/payment_method; paid_at/date/created_at; student_campus; student_number).
      - number := cashier.or_current (mode=or) or cashier.invoice_current (mode=invoice).
      - Guard: bounds check [start,end], and uniqueness via validateRangeUsage(mode, number, number).
      - Build insert payload:
        - student_information_id = student_id
        - student_number = fetched tb_mas_users.strStudentNumber (if column exists)
        - sy_reference = term (SYID)
        - description, subtotal_order = amount, status = &#39;Paid&#39;
        - remarks (required)
        - method/payment_method (if provided and present)
        - or_no/or_number or invoice_number → number
        - paid_at/date/created_at set to posted_at or now()
        - student_campus = campus_id (if present) – default from frontend passed campus_id
      - Insert row into payment_details and capture id.
      - Update cashier pointer (or_current++ or invoice_current++) and save.
      - Return { success:true, data:{ id, number_used:number, mode, cashier_id:id } }
  - CashierService::nextNumberOrFail(Cashier $row, string $type): array
    - Purpose: Small helper to centralize current pointer / bounds logic.
- Modified functions
  - CashierService::validateRangeUsage already handles number usage checks; call with [number, number].
  - CashierController (no changes to other endpoints).
- Removed functions
  - None

[Classes]
Adds a Laravel FormRequest for robust input validation; minor service helper addition.

Detailed breakdown:
- New classes
  - App\Http\Requests\Api\V1\CashierPaymentStoreRequest
    - rules():
      - student_id: [&#39;required&#39;, &#39;integer&#39;, &#39;exists:tb_mas_users,intID&#39;]
      - term: [&#39;required&#39;, &#39;integer&#39;] // SYID
      - mode: [&#39;required&#39;, &#39;in:or,invoice&#39;]
      - amount: [&#39;required&#39;, &#39;numeric&#39;, &#39;gt:0&#39;]
      - description: [&#39;required&#39;, &#39;string&#39;, &#39;max:255&#39;]
      - method: [&#39;nullable&#39;, &#39;string&#39;, &#39;max:100&#39;]
      - remarks: [&#39;required&#39;, &#39;string&#39;, &#39;max:1000&#39;]
      - posted_at: [&#39;nullable&#39;, &#39;date&#39;]
      - campus_id: [&#39;nullable&#39;, &#39;integer&#39;]
- Modified classes
  - App\Http\Controllers\Api\V1\CashierController – add createPayment action (see Functions).
  - App\Services\CashierService – add nextNumberOrFail helper (optional).
- Removed classes
  - None

[Dependencies]
No external package changes; reuses:
- Laravel DB/Schema, current CashierService validateRangeUsage, and role middleware.
- AngularJS existing modules (StudentsService for selection, CampusService for campus, existing SPA structure).

[Testing]
Add internal kernel smoke test and manual UI validation.

Test requirements:
- scripts/test_cashier_payment_entry.php (optional)
  - Pre-req: existing cashier with ranges and current pointer within bounds.
  - Steps:
    1) Prepare payload (student_id, term(SYID), mode=&#39;or&#39;, amount, description, remarks, method, posted_at).
    2) POST /api/v1/cashiers/{id}/payments
    3) Expect success; verify payment_details row created with the proper number column and subtotal_order = amount; check status = &#39;Paid&#39;.
    4) Fetch cashier and assert pointer incremented by 1.
    5) Repeat with mode=&#39;invoice&#39; when invoice range is present.
- Manual SPA:
  - Navigate to #/cashier-admin.
  - Click "Add Payment" in a cashier row.
  - Fill form: choose student (from listAll), select SYID, mode OR/Invoice, amount, description, required remarks, optional method/date.
  - Submit → expect toast success, and pointer/Stats updated, error surface on invalid inputs or exhausted range.
- Edge Cases:
  - Range exhausted: pointer > end → 422 with message.
  - Number already used: 422 with validateRangeUsage conflict info.
  - Missing optional columns in payment_details: endpoint should still succeed, skipping absent columns gracefully.

[Implementation Order]
Implement backend endpoint and validation first, then wire SPA and run through tests.

1) Backend: Create CashierPaymentStoreRequest with validation rules.
2) Backend: Add CashierController::createPayment
   - Transactional write to payment_details + pointer increment.
   - Dynamic column detection via Schema::hasColumn (or_no/or_number, invoice_number, method/payment_method, paid_at/date/created_at, student_campus, student_number).
   - Reuse CashierService::validateRangeUsage.
3) Backend: Optionally add CashierService::nextNumberOrFail helper for bounds/consistency.
4) Backend: routes/api.php – POST /api/v1/cashiers/{id}/payments (role:cashier_admin,admin).
5) Frontend: CashiersService.createPayment(cashierId, payload).
6) Frontend: CashiersController/list.html – Add per-row "Add Payment" modal and form; integrate StudentsService.listAll(), CampusService for campus_id, default mode=&#39;or&#39;, required remarks.
7) Frontend: After success, refresh row stats and pointer; show success toast.
8) Testing: Optional internal script and manual UI verification.
