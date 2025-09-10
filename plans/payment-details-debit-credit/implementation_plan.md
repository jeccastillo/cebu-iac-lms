# Implementation Plan

[Overview]
Add a Finance page and supporting APIs to create debit and credit adjustments against a student's balance using the payment_details table. Debits will increase the amount owed (negative journal entry) and credits will decrease it (positive journal entry), with optional linkage to an invoice; credits must respect the “not exceeding invoice remaining” rule, while neither operation consumes OR/Invoice sequences or numbers by default.

The solution introduces a “Journal” workflow on payment_details that:
- Creates credit entries as positive Paid rows without assigning OR/Invoice numbers (mode='none').
- Creates debit entries as negative adjustments without assigning numbers and without changing existing invoice counters.
- Allows linking either entry to an invoice_number for allocation, and enforces remaining balance constraints for credits.
- Presents a Finance > Debit/Credit page restricted to roles finance and admin for searching a student, selecting a term, issuing debit and credit entries, optionally referencing an invoice or free-form description, and seeing results reflected in the student ledger.

[Types]  
The plan adds lightweight “Journal” request DTOs; no DB schema changes are required.

Type definitions and validations:
- DebitRequest (POST body)
  - student_id: int (required)
  - term: int (required) — syid
  - amount: number (required, > 0) — will be stored as negative subtotal_order
  - description: string (required)
  - remarks: string (optional)
  - invoice_id: int (optional) — if provided, validate existence; link via invoice_number to payment_details when resolvable
  - invoice_number: int (optional) — fallback linkage if invoice_id is unavailable but number is supplied
  - method: string (optional) — pmethod/payment_method if present in schema
  - posted_at: string (optional, ISO datetime) — will map to paid_at/date/created_at where available
  - campus_id: int (optional)
  - mode_of_payment_id: int (optional) — persisted if column exists
- CreditRequest (POST body)
  - student_id: int (required)
  - term: int (required)
  - amount: number (required, > 0) — stored as positive subtotal_order
  - description: string (required)
  - remarks: string (optional)
  - method: string (optional)
  - posted_at: string (optional)
  - campus_id: int (optional)
  - mode_of_payment_id: int (optional)
  - invoice_id: int (optional)
  - invoice_number: int (optional)
  - enforce_invoice_remaining: boolean (defaults true) — if true and linked to invoice, enforce that credits do not exceed remaining (reuses validation logic from cashier createPayment)

Response envelopes:
- Both endpoints return:
  - success: boolean
  - data: object
    - id: int (payment_details.id)
    - source: 'payment_details'
    - entry_type: 'debit' | 'credit'
    - syid: int
    - invoice_number: int|null
    - posted_at: string|null
    - amount: number (signed value stored)
  - 201 Created on success

Validation rules:
- DebitRequest.amount > 0; the service will persist -amount to subtotal_order.
- CreditRequest.amount > 0; persisted amount as-is.
- If invoice_id/invoice_number provided for credit and enforce_invoice_remaining true, perform “remaining amount” check using payment_details (status='Paid') sums and invoice amount_total/amount/total if available.
- Neither request consumes or assigns OR/Invoice numbers; no uniqueness checks on numbers required.

[Files]
New and modified files to implement the feature across API and frontend.

New files:
- laravel-api/app/Http/Controllers/Api/V1/PaymentJournalController.php
  - Purpose: Expose finance-facing POST endpoints to create debit and credit journal entries on payment_details (no number assignment).
- laravel-api/app/Services/PaymentJournalService.php
  - Purpose: Encapsulate shared create logic (column detection, defaults, pulling user info for non-nullable columns, optional invoice linkage, remaining enforcement for credits) and return normalized results.
- frontend/unity-spa/features/finance/debit-credit/debit-credit.service.js
  - Purpose: AngularJS service to call the new APIs; includes helper to search invoices for a student/term via existing endpoints if needed.
- frontend/unity-spa/features/finance/debit-credit/debit-credit.controller.js
  - Purpose: Page controller; supports student lookup (existing finance flow), term selection, invoice lookup, and posting debit/credit.
- frontend/unity-spa/features/finance/debit-credit/debit-credit.html
  - Purpose: UI to perform debit/credit entries, show last entries list and current term meta from /finance/payment-details.

Modified files:
- laravel-api/routes/api.php
  - Add routes:
    - POST /api/v1/finance/payment-details/debit (role: finance,admin)
    - POST /api/v1/finance/payment-details/credit (role: finance,admin)
- laravel-api/app/Services/StudentLedgerService.php
  - Augment getLedger() to include payment_details journal entries:
    - Include entries with status 'Paid' (existing) plus “Journal” entries:
      - credit: subtotal_order > 0 and remarks flag; treated as payment (negative ledger delta or positive depending current sign convention)
      - debit: subtotal_order < 0; treated as charge line to increase balance
    - Preserve chronological merge with existing sources; tag source='payment_details' and indicate debit/credit.
- frontend/unity-spa/shared/components/sidebar/sidebar.html
  - Add Finance > Debit/Credit nav item (visible for finance, admin roles).

Files to be deleted or moved:
- None.

Configuration updates:
- None required; reuses existing Laravel config and role middleware.

[Functions]
New functions and modifications for API and frontend.

New (backend):
- PaymentJournalController::debit(Request): JsonResponse
  - Signature: debit(Request $request)
  - Body validates DebitRequest; calls PaymentJournalService::createDebit(); returns 201 with payload.
- PaymentJournalController::credit(Request): JsonResponse
  - Signature: credit(Request $request)
  - Body validates CreditRequest; calls PaymentJournalService::createCredit(); returns 201 with payload.
- PaymentJournalService::createDebit(array $payload, ?Request $request = null): array
  - Detects payment_details columns (reuses mapping approach in PaymentDetailAdminService/CashierController)
  - Loads student info to satisfy non-nullable columns (first_name, etc.) in target environments
  - Persists subtotal_order as -abs(amount), status='Journal' (or 'Paid' false) and remarks flag 'DEBIT ADJUSTMENT' for clarity
  - Sets sy_reference=term, student_information_id=student_id, method/posted_at/remarks/campus, optional mode_of_payment_id
  - If invoice linkage provided, sets invoice_number only (no sequence consumption)
  - Returns normalized row (id, syid, amount, invoice_number, posted_at, source)
- PaymentJournalService::createCredit(array $payload, ?Request $request = null): array
  - Same baseline mapping; persists subtotal_order as +abs(amount), status='Paid' (per payment semantics) and remarks includes 'CREDIT ADJUSTMENT' by default (or free-text remarks)
  - Does not consume OR/Invoice number (mode='none'); explicitly does not update cashier counters
  - If invoice linked and enforce rule is true, validates remaining using payment_details sums for status='Paid' and tb_mas_invoices.amount_total (when present)
  - Returns normalized row as above.
- PaymentJournalService::buildInsert(array $payload, bool $isCredit): array (private)
  - Shared builder ported from CashierController::createPayment:
    - Resolves optional columns: pmethod/payment_method, paid_at/date/created_at, remarks, student_number, student_campus, first_name/middle_name/last_name/email_address/contact_number, or_date, mode_of_payment_id
    - Ensures totals: total_amount_due = abs(amount) + convenience_fee (fee default 0)
    - request_id random slug and slug '' or random per environment constraints
    - invoice_number persistence if provided; no uniqueness enforcement (no number consumption)
- StudentLedgerService::getLedger(...) (modified)
  - Extend “payments from payment_details” aggregation to include:
    - credit rows: status='Paid' and remarks contains 'CREDIT ADJUSTMENT' (or flagged via negative vs positive)
    - debit rows: subtotal_order < 0 OR remarks contains 'DEBIT ADJUSTMENT'
  - Map debit rows to charges (positive delta in balance); map credits as payments as currently handled.
  - Preserve term filtering (sy_reference) and posted_at ordering.

New (frontend):
- debitCreditService.postDebit(payload)
- debitCreditService.postCredit(payload)
- debitCreditService.fetchPaymentDetails(studentId, term) — thin wrapper over GET /finance/payment-details to refresh after posting
- debitCreditController
  - Handles student context (by lookup or from viewer state), term selection (from Generic API terms/active-term), form inputs (amount, description, optional invoice link, method, posted_at), calls service post, shows toast and refreshes list.
- Utility: invoice lookup helper (optional) leveraging existing /finance/invoices endpoints to allow selecting a specific invoice (readonly chooser).

Modified (frontend):
- sidebar: add navigation link to #/finance/debit-credit (or similar route)

[Classes]
One new controller and one new service on backend; no model changes.

New classes:
- App\Http\Controllers\Api\V1\PaymentJournalController
  - Methods: debit(), credit()
  - Depends on PaymentJournalService
  - Middleware: role:finance,admin
- App\Services\PaymentJournalService
  - Methods: createDebit(), createCredit(), private helpers (buildInsert, resolveInvoiceRemaining, detectPdColumns, resolveStudentInfo)

Modified classes:
- App\Services\StudentLedgerService
  - Include payment_details debit (negative) and credit journal entries in ledger computation, correctly signed and ordered.

Removed classes:
- None.

[Dependencies]
No new external dependencies.

- Reuse existing:
  - Illuminate DB, Schema
  - SystemLogService (optional logs 'create' with context 'PaymentDetail' for journal entries)
  - PaymentDetailAdminService::detectColumns pattern for schema-safety
  - Existing invoices endpoints/tables when present for validation

[Testing]
A focused API and UI validation approach.

Backend tests (manual/integration via scripts or Postman):
- POST /api/v1/finance/payment-details/debit
  - Creates payment_details row with negative subtotal_order, status='Journal', no OR/Invoice number consumed
  - With invoice_id or invoice_number: row stores invoice_number; no number consumption
- POST /api/v1/finance/payment-details/credit
  - Creates payment_details row with positive subtotal_order, status='Paid', no number consumed
  - With enforce_invoice_remaining=true (default): reject when amount exceeds remaining on invoice; accept otherwise
- GET /api/v1/finance/payment-details?student_id=&amp;term=
  - New entries appear in items array with correct signed amounts; meta totals for “Paid” continue to reflect Paid rows (credits), while debits do not perturb Paid totals (by design)
- StudentLedgerService.getLedger
  - Ledger reflects new debit/credit journal lines in proper chronology and signs

Frontend tests:
- Navigation: Finance > Debit/Credit visible for finance/admin only
- Page flows:
  - Load student context, select term
  - Debit form: amount, description, optional invoice ref; submit; see success and ledger/payment details refresh
  - Credit form: amount, description, optional invoice ref; fails when exceeding remaining; passes when valid; refresh
- Usability: disable submit until form valid; show validation messages returned by API

Edge cases:
- Missing payment_details optional columns (service should detect and default)
- Student without registration: still allow journal entries tied to sy_reference=term (FinanceService already tolerant)
- Environments without tb_mas_invoices: allow credits without linkage validation (best-effort validation only when table/columns exist)

[Implementation Order]
Implement backend service and endpoints first, then frontend, then ledger augmentation.

1) Backend service scaffolding
   - Add PaymentJournalService with column detection and builder functions (port safe defaults from CashierController createPayment)
   - Implement createDebit (negative subtotal_order, status='Journal', remarks tagging)
   - Implement createCredit (positive subtotal_order, status='Paid'; invoice remaining enforcement when linked)
   - Integrate SystemLogService::log('create','PaymentDetail', id, null, normalized, request)

2) Backend controller and routes
   - Add PaymentJournalController with debit() and credit() validators and handlers
   - Register routes in routes/api.php under /api/v1 (middleware role:finance,admin)

3) Ledger augmentation
   - Modify StudentLedgerService::getLedger to include debit (negative) and credit journal entries from payment_details with proper sign mapping and source tagging

4) Frontend
   - Add debit-credit.service.js for API calls and optional invoice lookup
   - Add debit-credit.controller.js for UI logic
   - Add debit-credit.html for forms and results; reuse existing finance styles/components
   - Update shared sidebar to add Finance > Debit/Credit link gated by roles finance and admin

5) Verification
   - Manual tests with realistic student and invoice data
   - Validate “not exceeding remaining” rule for credits
   - Validate no OR/Invoice number consumption or cashier current pointer changes
   - Validate ledger/payment-details screens reflect changes

6) Documentation and handoff
   - Brief README in the feature dir with payload samples for debit/credit
   - Capture example cURL commands and expected responses
