# Implementation Plan

[Overview]
Build a finance/admin-only “Student Financial Activity” page and API that displays a ledger-like timeline of a student’s financial activity without using the ledger table. Data will be composed from: Saved Tuition (single “Tuition Assessment” per term), Student Billing (charges/credits), and Payment Details (Paid payments). Each row shows OR number, Invoice number, Transaction date, Assessment, Payment, Cashier Name, and a running balance.

This feature enables Finance and Admin users to inspect student account activity for a selected term or across all terms, with clear separation of charges vs payments and a per-row running balance. It leverages existing tables and services to ensure consistency with current tuition, billing, and payment processes while avoiding reliance on legacy ledger tables. The API aggregates, normalizes, and sorts events chronologically, then the frontend renders the table with running totals, filters, and CSV export.

[Types]  
Introduce explicit row types and response structures for consistent data handling on the API and front-end.

- Row types:
  - ref_type: 'tuition_assessment' | 'billing' | 'payment'

- API Request parameters:
  - student_number: string (optional when student_id provided)
  - student_id: int (optional when student_number provided)
  - term: 'all' | int (syid)
  - sort: 'asc' | 'desc' (optional; default 'asc')

- API Response shape:
  - LedgerResponse:
    - student_id: int|null
    - student_number: string|null
    - scope: { term: 'all'|int, sy_label: string|null }
    - meta:
      - opening_balance: float
      - total_assessment: float
      - total_payment: float
      - closing_balance: float
      - terms_included: int[]  // syids included in the aggregation
    - rows: LedgerRow[]

  - LedgerRow:
    - id: string                 // stable composite id e.g., "{ref_type}:{source_id}:{syid}"
    - ref_type: 'tuition_assessment' | 'billing' | 'payment'
    - syid: int|null
    - sy_label: string|null
    - posted_at: string|null     // ISO date (Y-m-d or Y-m-d H:i:s); per user: posted_at for non-payment; or_date for payments already mapped by service
    - or_no: string|int|null
    - invoice_number: int|null
    - assessment: float|null     // amount added to balance; positive value
    - payment: float|null        // payment amount; positive value (absolute)
    - cashier_id: int|null
    - cashier_name: string|null  // from tb_mas_cashiers -> faculty name; fallback to created_by
    - remarks: string|null
    - source: 'saved_tuition' | 'student_billing' | 'payment_details'
    - source_id: int|null        // intID or equivalent

- Balance conventions:
  - assessment increases balance (positive)
  - payment decreases balance (negative; API exposes as positive in payment field but used as negative in running balance calc)
  - running balance is computed client-side and/or returned by API when requested

- Validation notes:
  - posted_at for non-payment rows:
    - Student Billing: use tb_mas_student_billing.posted_at
    - Tuition Assessment: use tb_mas_tuition_saved.updated_at (fallback created_at) as posted_at
  - Cashier Name:
    - payment_details.cashier_id -> tb_mas_cashiers (faculty_id) -> tb_mas_faculty first_name + last_name
    - fallback: payment_details.created_by (if resolvable to a user/faculty name)

[Files]
Add a new service for aggregation, new controller method and route, and front-end controller/service/template updates. No deletions.

- New files to be created:
  - laravel-api/app/Services/StudentLedgerService.php
    - Purpose: Aggregate SavedTuition (per-term single line), StudentBilling items, and Paid PaymentDetails into unified rows; normalize fields; resolve cashier names; optionally compute running balance.
  - frontend/unity-spa/features/finance/ledger.service.js
    - Purpose: Angular service to call the new API endpoint and provide data + CSV export helpers.

- Existing files to be modified:
  - laravel-api/app/Http/Controllers/Api/V1/FinanceController.php
    - Add method: studentLedger(Request $request): JsonResponse
      - Validates inputs, delegates to StudentLedgerService, returns normalized LedgerResponse.
  - laravel-api/routes/api.php
    - Add GET /api/v1/finance/student-ledger (role: finance,admin)
  - frontend/unity-spa/features/finance/ledger.controller.js
    - Implement data retrieval using ledger.service.js, form inputs (student number, term selector with “All terms”), and client-side running balance calculation as rows render.
  - frontend/unity-spa/features/finance/ledger.html
    - Render search form and results table:
      - Columns: Transaction Date, OR Number, Invoice Number, Assessment, Payment, Cashier Name, Running Balance
      - CSV export button
      - Loading/empty states
      - Paging or virtual scroll (simple client-side for MVP)

- Configuration updates:
  - None (reuse existing env and role guards).

[Functions]
Add a new aggregation function and controller entrypoint; minor UI functions on frontend.

- New (backend):
  - App\Services\StudentLedgerService::getLedger(?string $studentNumber, ?int $studentId, string|int $term = 'all', string $sort = 'asc'): array
    - Purpose: Return LedgerResponse with unified normalized rows.
    - Steps:
      1) Resolve student_id from number if needed, and sy_label(s).
      2) Determine syids to include:
         - If term = int: [term]
         - If term = 'all': union of syids present in:
           - tb_mas_tuition_saved for student
           - tb_mas_student_billing for student
           - payment_details for student (sy_reference when exists; include rows even if sy_reference null by bucketing under null syid)
      3) SavedTuition lines (per term):
         - For each syid, get the latest tb_mas_tuition_saved row.
         - Extract total_due from payload (try keys: total_due, total, grand_total, totals.totalPayable; fallback to TuitionService->compute if needed).
         - Construct LedgerRow:
           - ref_type: 'tuition_assessment'
           - posted_at: saved.updated_at (fallback saved.created_at)
           - assessment: total_due
           - invoice_number: attempt to resolve latest tb_mas_invoices row with registration_id or syid & type='tuition' for the student; include invoice_number if found
           - cashier fields: null
      4) Student Billing lines:
         - Query tb_mas_student_billing by student_id and syid list; build rows:
           - ref_type: 'billing'
           - posted_at: billing.posted_at
           - If amount > 0 => assessment = amount
           - If amount < 0 => payment = abs(amount)
           - remarks: billing.remarks
           - or/invoice: null (unless a known mapping exists; leave null)
      5) Payment Details lines:
         - Query payment_details where student_information_id = student_id AND status='Paid'
           - If term != 'all', filter by sy_reference = term; else include all
           - Select: id, or_date as posted_at (if available; else paid_at/date/created_at), description, subtotal_order, or_no/or_number, invoice_number, cashier_id, created_by
           - ref_type: 'payment'
           - payment = subtotal_order (positive)
           - cashier_name: resolve via tb_mas_cashiers (cashier_id) -> tb_mas_faculty; fallback to created_by mapping
      6) Normalize, tag source/source_id, compute totals, sort (posted_at asc; tiebreakers id/or/invoice).
      7) Return LedgerResponse; optionally include rows with a server-computed running balance if query param include_balance=true (default compute balance client-side).

- Modified (backend):
  - App\Http\Controllers\Api\V1\FinanceController::studentLedger(Request $request): JsonResponse
    - Validate:
      - student_number: sometimes|string|nullable
      - student_id: sometimes|integer|nullable
      - term: required|in:all,integer
      - sort: sometimes|in:asc,desc
    - Call StudentLedgerService->getLedger and return JSON.

- New (frontend):
  - features/finance/ledger.service.js
    - getLedger(params): Promise
    - toCsv(rows): string
  - features/finance/ledger.controller.js
    - Methods:
      - vm.search(): call service with form values
      - vm.computeRunningBalance(rows): produce runningBalance per row
      - vm.exportCsv(): trigger CSV download

- Modified (frontend):
  - features/finance/ledger.html
    - Form: student number input; term selector with “All terms”
    - Table with columns:
      - Transaction Date, OR Number, Invoice Number, Assessment, Payment, Cashier Name, Running Balance

[Classes]
Introduce one new backend service, reusing existing models and services.

- New classes:
  - App\Services\StudentLedgerService
    - Key methods: getLedger(), private resolvers (studentId, sy labels, invoice resolve), private extractors (saved tuition total), private cashier name resolver.

- Modified classes:
  - App\Http\Controllers\Api\V1\FinanceController
    - Add studentLedger() method.

[Dependencies]
No new external packages. Rely on DB facade and existing tables:
- tb_mas_tuition_saved
- tb_mas_student_billing
- payment_details
- tb_mas_invoices (optional invoice_number enrichment)
- tb_mas_cashiers, tb_mas_faculty (cashier name resolution)

[Testing]
Add API-level and UI manual validation.

- Backend testing:
  - Test students with:
    - Only tuition saved
    - Tuition + billing positive + billing negative (credit)
    - Paid payment_details with OR and Invoice numbers
    - Multiple terms; verify union and sorting, totals, and closing balance
    - Edge cases: missing or_date; missing sy_reference; missing cashier mapping
  - Verify computed totals:
    - total_assessment = sum(assessment across rows)
    - total_payment = sum(payment across rows)
    - closing_balance = opening_balance + total_assessment - total_payment (opening assumed 0 for display; can extend later)

- Frontend testing:
  - RBAC: route requires roles ['finance','admin']
  - Search by student number; show results; compute running balance
  - CSV export; numeric formatting (2 decimals), null-safe cells
  - “All terms” vs specific term filter

[Implementation Order]
Implement in layered steps to minimize risk and enable incremental verification.

1) Backend Service:
   - Create App\Services\StudentLedgerService with getLedger() and helpers:
     - resolveStudentId, resolveSyLabels, collectSyids, fetchSavedTuitionRows, extractTuitionTotal, fetchBillingRows, fetchPaymentRows, resolveCashierName, normalizeRows, sortRows, summarizeTotals.

2) Controller + Route:
   - Add FinanceController::studentLedger()
   - Register route GET /api/v1/finance/student-ledger with middleware role:finance,admin

3) Frontend Service and UI:
   - Add features/finance/ledger.service.js
   - Update ledger.controller.js to call service, compute running balance, and handle UI state
   - Update ledger.html to include query form, table, and CSV export

4) Formatting & Edge Cases:
   - Ensure posted_at mapping:
     - payment_details: or_date (preferred)
     - billing: posted_at
     - tuition: updated_at (fallback created_at)
   - Cashier Name resolution with robust fallbacks

5) Validation:
   - Manual API tests via browser or Postman:
     - /api/v1/finance/student-ledger?student_number=XXXX&amp;term=all
     - Specific term
   - Frontend manual tests and UI review with finance/admin accounts

6) Documentation:
   - Add a short README section in features/finance/ledger.service.js header and StudentLedgerService docblock explaining data sources and running balance logic.
