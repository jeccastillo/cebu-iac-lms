# Implementation Plan

[Overview]
Deliver a finance/admin-only Student Financial Activity page and API that render a ledger-like view without using the legacy ledger table. The view unifies three data sources—Saved Tuition (single “Tuition Assessment” line per term), Student Billing (charges/credits), and Payment Details (Paid payments)—and displays per-row running balance along with the required columns: OR Number, Invoice Number, Transaction Date (prefer payment_details.or_date), Assessment, Payment, and Cashier Name.

This page enables Finance and Admin roles to inspect a student’s activity for a specific term or across all terms, showing chronological rows that add or reduce the balance. The backend aggregates normalized rows from existing tables and resolves sy labels and cashier names; the frontend provides filters (student, term, sort), table, running balance computation, and CSV export.

[Types]  
No new PHP/JS type systems are introduced; instead, we will standardize the row and response shapes for client-server interoperability.

- API Request
  - student_number: string | optional when student_id is provided
  - student_id: int | optional when student_number is provided
  - term: 'all' | int (syid)
  - sort: 'asc' | 'desc' (default 'asc')

- API Response (LedgerResponse)
  - student_id: int|null
  - student_number: string|null
  - scope: { term: 'all'|int, sy_label: string|null }
  - meta:
    - opening_balance: float
    - total_assessment: float
    - total_payment: float
    - closing_balance: float
    - terms_included: int[]  // included syids
  - rows: LedgerRow[]

- LedgerRow
  - id: string                  // "{ref_type}:{source_id}:{syid}"
  - ref_type: 'tuition_assessment' | 'billing' | 'payment'
  - syid: int|null
  - sy_label: string|null
  - posted_at: string|null      // for payments: prefer or_date; others: posted_at (billing) or updated_at (saved tuition)
  - or_no: string|int|null
  - invoice_number: int|null
  - assessment: float|null      // amount added to balance
  - payment: float|null         // payment amount (absolute value; subtracted in running balance)
  - cashier_id: int|null
  - cashier_name: string|null   // cashier mapping (see below)
  - remarks: string|null
  - source: 'saved_tuition' | 'student_billing' | 'payment_details'
  - source_id: int|null

- Cashier Name Resolution
  - Primary: payment_details.cashier_id -> tb_mas_cashiers (faculty_id) -> tb_mas_faculty (first + last name)
  - Fallback: payment_details.created_by -> tb_mas_faculty or tb_mas_users name fields

[Files]
Minimal file modifications; core of the implementation already exists. We will verify and adjust where needed.

- New files to be created
  - implementation_plan_student_financial_activity.md (this document)

- Existing files to be verified/modified (as necessary)
  - Backend
    - laravel-api/app/Services/StudentLedgerService.php
      - Ensure the following:
        - fetchPaymentRows(): order and date selection prefer or_date; include or_no/or_number, invoice_number, subtotal_order, cashier_id, created_by, sy_reference (when exists).
        - resolveCashierName(): implement lookup chain: tb_mas_cashiers->tb_mas_faculty; fallback created_by -> faculty/users names.
        - fetchSavedTuitionRows(): per term single line; posted_at = updated_at (fallback created_at); optional invoice_number enrichment from tb_mas_invoices.
        - fetchBillingRows(): map amount > 0 to assessment; amount < 0 to payment.
        - sortRows(): chronological sort by posted_at with tie-breakers (or_no, invoice_number, id).
        - meta totals: opening_balance = 0; closing = opening + total_assessment - total_payment.
    - laravel-api/app/Http/Controllers/Api/V1/FinanceController.php
      - Ensure studentLedger(Request $request) validator and response shape comply with the Types above.
    - laravel-api/routes/api.php
      - Ensure GET /api/v1/finance/student-ledger exists and is protected via middleware('role:finance,admin').

  - Frontend (AngularJS)
    - frontend/unity-spa/features/finance/ledger.service.js
      - Ensure GET to /finance/student-ledger with params { student_number|student_id, term, sort }.
      - Ensure CSV export includes columns: Transaction Date, OR Number, Invoice Number, Assessment, Payment, Cashier Name, Running Balance, SY Label, Type, Remarks.
    - frontend/unity-spa/features/finance/ledger.controller.js
      - Ensure vm.search() maps filters and handles error/loading states.
      - Ensure computeRunningBalance() applies assessment - payment across rows, calculating per-row running_balance.
      - Ensure autocomplete for student (StudentsService integration).
    - frontend/unity-spa/features/finance/ledger.html
      - Ensure required columns exist: Transaction Date, OR Number, Invoice Number, Assessment, Payment, Cashier Name, Running Balance.
      - Ensure filters: student autocomplete, term selector (All terms + specific syid), sort (asc/desc).
      - Ensure CSV export button and feedback states (loading, error, empty).

- Files to be deleted/moved
  - None

- Configuration updates
  - None

[Functions]
No new public function signatures required; ensure existing implementations match requirements and adjust internal helpers as needed.

- Backend – New/Verified
  - StudentLedgerService::getLedger(?string $studentNumber, ?int $studentId, string|int $term = 'all', string $sort = 'asc'): array
    - Aggregates rows from saved tuition (one per term), student billing, and paid payment_details.
    - Computes totals and returns normalized rows with fields as specified.
  - StudentLedgerService::fetchPaymentRows(int $studentId, array $syids, array $syLabels, string|int $term): array
    - Ensure or_date is used as posted_at when available.
    - Selects id, description, subtotal_order, or_no/or_number, invoice_number, cashier_id, created_by, posted_at, sy_reference.
    - Only includes rows with status='Paid'.
  - StudentLedgerService::resolveCashierName(?int $cashierId, ?int $createdBy): ?string
    - Return tb_mas_faculty name via tb_mas_cashiers mapping; fallback to created_by against faculty/users.
  - StudentLedgerService::fetchSavedTuitionRows(...)
    - Use updated_at (fallback created_at) as posted_at; single row per term; enrich invoice_number from tb_mas_invoices if present.
  - StudentLedgerService::fetchBillingRows(...)
    - Map amount > 0 => assessment; amount < 0 => payment; posted_at from billing.posted_at.
  - FinanceController::studentLedger(Request $request): JsonResponse
    - Validate inputs and return StudentLedgerService response.

- Frontend – Verified
  - FinanceLedgerService.getLedger(params): Promise
    - Build querystring (student_number|student_id, term, sort).
  - FinanceLedgerService.toCsv(rows): string
    - Emit CSV with required columns including Running Balance.
  - FinanceLedgerController
    - vm.search(): fetch ledger and compute running balance.
    - vm.computeRunningBalance(rows): fold over rows to produce running_balance; also compute summary if meta absent.
    - vm.exportCsv(): trigger CSV download.
    - vm.onStudentQuery / vm.onStudentSelected for autocomplete UX.

[Classes]
No new classes required beyond the existing StudentLedgerService; ensure the following classes expose/consume the necessary functionality.

- New classes
  - None

- Modified classes
  - App\Services\StudentLedgerService
    - Ensure methods listed above match the required behavior for column mapping, term scoping, sorting, and cashier resolution.
  - App\Http\Controllers\Api\V1\FinanceController
    - Ensure studentLedger endpoint exists and is wired to the service.

[Dependencies]
No new Composer or NPM dependencies. All lookups use existing tables and facades.

- Tables referenced (guard via Schema::hasTable/hasColumn where appropriate)
  - tb_mas_tuition_saved, tb_mas_student_billing, payment_details, tb_mas_invoices, tb_mas_cashiers, tb_mas_faculty, tb_mas_users, tb_mas_sy

[Testing]
Adopt layered testing focusing on API integrity and UI behavior.

- Backend/API
  - Happy paths
    - /api/v1/finance/student-ledger?student_number=XXXX&amp;term=all (multiple terms)
    - /api/v1/finance/student-ledger?student_id=Y&amp;term={syid} (specific term)
  - Edge cases
    - No saved tuition or no billing or no payments -> still returns empty rows and zero totals.
    - Payments without sy_reference appear with syid=null and sy_label=null (when term=all).
    - Missing columns/tables handled via Schema guards without exceptions.
    - Sorting asc/desc by posted_at; tie-breaks by or_no, invoice_number, then id.
    - Cashier name resolution when only created_by is present.
  - Totals validation
    - total_assessment = sum of assessment fields
    - total_payment = sum of payment fields
    - closing_balance = opening_balance + assessment - payment

- Frontend/UI
  - RBAC: route only accessible to finance/admin (already enforced server-side; verify client nav visibility).
  - Form behavior: All terms vs specific term; correct parameter passing.
  - Running balance: verify per-row calculations across mixed rows.
  - CSV: validate columns, numeric formatting (2 decimals), and escaping.
  - States: loading, error, empty set handling.

[Implementation Order]
Proceed in a verification-first sequence since core pieces exist; adjust only if gaps are observed.

1) Backend verification
   - Confirm StudentLedgerService adheres to column/date/cashier mapping (payments prefer or_date; non-payments use posted_at/updated_at).
   - Confirm totals and sort behavior; adjust if discrepancies exist.

2) API wireup
   - Confirm FinanceController::studentLedger(Request) and routes/api.php entry are present and correct with middleware('role:finance,admin').

3) Frontend verification
   - Ensure ledger.service.js calls correct endpoint and renders required columns.
   - Ensure computeRunningBalance is applying assessment - payment correctly and table shows Running Balance.

4) Functional tests
   - Term=all vs specific term; student_number vs student_id.
   - Validate cashier name rendering and missing cases fallback to '-'.

5) CSV & polish
   - Validate CSV contents include specified columns and match on-screen order.
   - Review UI states and usability for finance/admin users.

6) Signoff
   - Share with finance/admin stakeholders for UAT; iterate on minor UX/data adjustments if needed.
