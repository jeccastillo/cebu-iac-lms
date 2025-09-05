# Implementation Plan

[Overview]
Add a Payments panel to the Registration Viewer that lists and totals student payment rows sourced from the payment_details table using student_information_id as the reference (mapped from tb_mas_users.intID) and filtered to the current selected term's registration (payment_details.sy_reference = tb_mas_registration.intRegistrationID). Display each row with core fields and the human-readable School Year/Semester label (e.g., "1st Sem 2024-2025"), order newest-first, and show a total from payment_details alongside the existing transactions-based total for comparison.

This implementation completes the new stack parity for finance visibility within the registration viewer by introducing a narrowly scoped Laravel API endpoint and minimal AngularJS changes to fetch and render the data. It integrates with existing term selection flow and preserves current tuition computation and ledger features.

[Types]  
Introduce shape specs for Payment Details in PHP array responses and AngularJS controller models. No strict language-level types added.

Detailed type definitions:
- PaymentDetailItem (API response item)
  - id: number (payment_details.id)
  - posted_at: string|null (coalesced date field if present; otherwise null)
  - or_no: string|number|null (nullable)
  - description: string|null
  - subtotal_order: number (float)
  - status: string|null (e.g., 'Paid', 'Pending', etc.)
  - method: string|null (if column exists, otherwise null)
  - sy_reference: number (intRegistrationID this payment is keyed to)
  - syid: number (tb_mas_sy.intID via registration)
  - sy_label: string (e.g., '1st Sem 2024-2025' built from tb_mas_sy.enumSem + strYearStart + strYearEnd)
  - source: 'payment_details'

- PaymentDetailsMetrics (API response meta)
  - total_paid_filtered: number
      - Sum of subtotal_order where status = 'Paid' and description LIKE 'Tuition%' OR 'Reservation%' (aligned with TuitionService meta.amount_paid rules)
  - total_paid_all_status: number
      - Sum of subtotal_order where status = 'Paid'
  - total_all_rows: number
      - Sum of subtotal_order across all rows returned (regardless of status/description)
  - count_rows: number

- PaymentDetailsResponse (API response)
  - student_number: string
  - registration_id: number
  - syid: number
  - sy_label: string
  - items: PaymentDetailItem[]
  - meta: PaymentDetailsMetrics

[Files]
Modify Laravel API (service, controller, route) and Angular SPA (service, controller, template) to introduce Payment Details.

Detailed breakdown:
- New files to be created
  - None.

- Existing files to be modified
  - laravel-api/app/Services/FinanceService.php
    - Add a new method listPaymentDetails(?string $studentNumber, ?int $syid): array
      - Resolve user (tb_mas_users by strStudentNumber) → intID
      - Resolve registration for (intStudentID=intID, intAYID=$syid) → intRegistrationID
      - Query payment_details:
          WHERE student_information_id = user.intID
            AND sy_reference = registration.intRegistrationID
      - Select columns: id, or_no (if exists), description, subtotal_order, status, method (if exists), date (if exists), sy_reference
      - Build sy_label by joining tb_mas_registration (r.intAYID) → tb_mas_sy, formatting "enumSem strYearStart-strYearEnd"
      - Compute meta totals per Types
      - Return normalized array of PaymentDetailItem items plus meta
  - laravel-api/app/Http/Controllers/Api/V1/FinanceController.php
    - Add method paymentDetails(Request $request): JsonResponse
      - Validate: student_number (required|string), term (required|integer)
      - Call FinanceService::listPaymentDetails($studentNumber, (int)$term)
      - Return { success: true, data: ... } with normalized response
  - laravel-api/routes/api.php
    - Register GET /api/v1/finance/payment-details → FinanceController@paymentDetails
  - frontend/unity-spa/features/unity/unity.service.js
    - Add method paymentDetails(params)
      - params: { student_number: string, term: int }
      - GET BASE + '/finance/payment-details' with params and admin headers, returns unwrapped data
  - frontend/unity-spa/features/registrar/registration-viewer/registration-viewer.controller.js
    - Add state:
      - vm.paymentDetails = { items: [], meta: {...}, sy_label: '' }
    - Add loader:
      - vm.loadPaymentDetails(force)
        - Guard duplicate loads via vm._last.payments
        - Calls UnityService.paymentDetails({ student_number: vm.sn, term: vm.term })
        - Sets vm.paymentDetails, computes a UI total for display
      - Integrate into bootstrap and term-change chain after tuition and ledger load
    - Add summary recomputation hook:
      - Incorporate vm.paymentDetails.meta.total_paid_filtered into comparison section (alongside transactions sum)
  - frontend/unity-spa/features/registrar/registration-viewer/registration-viewer.html
    - Add "Payments" panel under Tuition Summary:
      - Header shows sy_label returned by API (e.g., "1st Sem 2024-2025")
      - Show comparison:
        - Payment Details total (Paid Tuition/Reservation) vs Transactions total
      - Table rows (newest first by date/OR number):
        - Date, OR Number, Description, Subtotal, Status, Method, Registration Ref (sy_reference), SY label (inline or header-level)

- Files to be deleted or moved
  - None.

- Configuration file updates
  - None.

[Functions]
Add one new Laravel service method and one controller method; add Angular service and controller methods.

Detailed breakdown:
- New functions
  - PHP: laravel-api/app/Services/FinanceService.php
    - listPaymentDetails(?string $studentNumber, ?int $syid): array
      - Purpose: Return normalized payment_details rows for the registration matching student+term, including metrics and sy_label.
  - PHP: laravel-api/app/Http/Controllers/Api/V1/FinanceController.php
    - paymentDetails(Request $request): JsonResponse
      - Purpose: Validate inputs, call FinanceService::listPaymentDetails, wrap JSON response.
  - JS: frontend/unity-spa/features/unity/unity.service.js
    - paymentDetails(params): Promise<Body>
      - Purpose: GET /finance/payment-details with X-Faculty-ID header propagation for admin context.
  - JS: frontend/unity-spa/features/registrar/registration-viewer/registration-viewer.controller.js
    - loadPaymentDetails(force): Promise
      - Purpose: Load payment details for current sn+term, set vm.paymentDetails, compute totals, update comparison.
    - comparePayments(): void
      - Purpose: Compute/prepare side-by-side totals: payment_details (filtered paid tuition/reservation) vs transactions ledger sum if present.

- Modified functions
  - JS: RegistrationViewerController bootstrap chain
    - Append .then(vm.loadPaymentDetails) after loadLedger, and on vm.onTermChange chain.
  - JS: refreshTuitionSummary
    - Optionally incorporate vm.paymentDetails.meta.total_paid_filtered to recompute remaining if ledger API did not provide a filtered amount_paid.

- Removed functions
  - None.

[Classes]
Add no new classes; modify existing service and controller.

Detailed breakdown:
- New classes
  - None.

- Modified classes
  - App\Services\FinanceService
    - Add listPaymentDetails method with registration+term resolution, metrics computation, and sy label formatting.
  - App\Http\Controllers\Api\V1\FinanceController
    - Add paymentDetails action.

- Removed classes
  - None.

[Dependencies]
No new external packages. Use existing:
- tb_mas_users, tb_mas_registration, tb_mas_sy, payment_details tables in the same DB.
- Existing controller/service structure and routing in Laravel API.
- Existing AngularJS UnityService pattern for admin header injection.

[Testing]
Add endpoint and UI validations to ensure correctness and parity with expectations.

Test file requirements, existing test modifications, and validation strategies:
- Backend
  - GET /api/v1/finance/payment-details?student_number={SN}&term={SYID}
    - 200 OK with:
      - Correct registration_id (intRegistrationID), syid, sy_label
      - items limited to sy_reference = registration_id
      - metrics:
        - total_paid_filtered matches SUM(subtotal_order) WHERE status='Paid' AND description LIKE 'Tuition%' OR 'Reservation%'
        - newest-first ordering in items (by date if present; fallback by id/OR descending)
    - 404/422 handling on unknown student/term/registration appropriately (return 200 with empty items and zero totals or 422 with message; choose consistent UX-friendly 200 with empty items).
- Frontend
  - Registration Viewer:
    - On load with selected term, Payments panel appears under Tuition Summary.
    - Header shows sy_label (e.g., "1st Sem 2024-2025").
    - Table shows fields: Date, OR Number, Description, Subtotal, Status, Method, Registration Ref (sy_reference), and optionally SY label.
    - Ordering: newest first.
    - Comparison section displays:
      - Payment Details total (Paid Tuition/Reservation)
      - Transactions total (existing ledger sum) for side-by-side comparison.
  - Term change:
    - Changing term refreshes the Payments panel with correct sy_label and rows for the selected term.

[Implementation Order]
Implement backend first, then wire frontend service and UI to minimize integration churn.

1) Laravel API
   - Add FinanceService::listPaymentDetails($studentNumber, $syid) with:
     - Resolve user.intID by strStudentNumber
     - Resolve registration.intRegistrationID by (intStudentID, intAYID)
     - Query payment_details rows filtered by (student_information_id=user.intID) AND (sy_reference=registration.intRegistrationID)
     - Join r→tb_mas_sy to build sy_label
     - Compute meta totals (filtered paid tuition/reservation, paid-all-status, all-rows)
     - Order newest-first (prefer by date desc, then OR number desc, else id desc)
   - Add FinanceController::paymentDetails action (validation + service call)
   - Register route GET /api/v1/finance/payment-details in routes/api.php

2) AngularJS
   - unity.service.js: add paymentDetails(params) using admin headers
   - registration-viewer.controller.js:
     - Add vm.paymentDetails state and vm.loadPaymentDetails(force)
     - Call loadPaymentDetails in bootstrap after loadLedger and within onTermChange()
     - Add compare function to compute side-by-side totals in the controller

3) Template
   - registration-viewer.html:
     - Add Payments panel below Tuition Summary
     - Show sy_label, totals comparison, and table with required columns

4) UAT
   - Validate with a student who has payment_details for the selected registration and ensure totals align with TuitionService meta.amount_paid and transactions fallback

5) Hardening
   - Handle missing optional columns (e.g., method, date, or_no) gracefully
   - Ensure API returns empty items + zero totals instead of error when no registration exists for the term

task_progress Items:
- [ ] Step 1: Implement FinanceService::listPaymentDetails with registration+term resolution, normalized rows, metrics, and sy_label
- [ ] Step 2: Add FinanceController::paymentDetails and route GET /api/v1/finance/payment-details
- [ ] Step 3: Extend unity.service.js with paymentDetails(params) wrapper
- [ ] Step 4: Update registration-viewer.controller.js to load and display payment details, integrate into bootstrap and term-change flows
- [ ] Step 5: Add Payments panel markup to registration-viewer.html with totals comparison and newest-first ordering
- [ ] Step 6: Manual test with multiple students/terms; verify totals and labels; refine ordering and fallback fields
