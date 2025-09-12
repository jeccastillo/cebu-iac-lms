# Implementation Plan

[Overview]
Create a single enriched backend endpoint to power the Cashier Viewer with one request: GET /api/v1/finance/cashier/viewer-data?student_number=&amp;term=, aggregating invoices, payment_details, student_billing, and missing-invoices using student_number as the identifier.

The current Cashier Viewer loads multiple panels separately (invoices, payment details, student billing, missing invoices) which results in many sequential HTTP calls and complex client-side recomputation. This plan introduces a consolidated endpoint that resolves the student by student_number, loads all relevant finance data for the selected term, enriches it (per-invoice paid/remaining, effective totals with reservation offsets, meta totals) and returns a single well-structured payload. The AngularJS controller will be updated to consume this one endpoint, drastically reducing network chatter and simplifying UI logic.

The endpoint is scoped under Finance and guarded by role: finance,admin, consistent with existing endpoints. The aggregator pulls from existing services (InvoiceService, FinanceService, StudentBillingService, StudentBillingExtrasService), applies deterministic enrichment, and returns data in a format compatible with current UI expectations so the migration is low-risk and incremental.

[Types]
Introduce a single aggregate response type with enriched invoice metrics computed server-side.

Type definitions (JSON schema-like, documentation-level):

- CashierViewerDataResponse
  - success: boolean
  - data: CashierViewerData

- CashierViewerData
  - student_number: string
  - student_id: int|null
  - syid: int
  - sy_label: string|null
  - invoices: InvoiceEnriched[]
  - payment_details: PaymentDetailsBlock
  - student_billing: BillingItem[]
  - missing_billing: BillingItem[]
  - meta: CashierViewerMeta

- PaymentDetailsBlock
  - student_number: string|null
  - registration_id: int|null
  - syid: int|null
  - sy_label: string|null
  - items: PaymentDetailItem[]
  - meta:
    - total_paid_filtered: number
    - total_paid_all_status: number
    - total_all_rows: number
    - count_rows: number

- PaymentDetailItem
  - id: int
  - posted_at: string|null
  - or_no: string|int|null
  - invoice_number: string|int|null
  - description: string|null
  - subtotal_order: number
  - status: string|null
  - method: string|null
  - sy_reference: int|null
  - syid: int|null
  - sy_label: string|null
  - source: 'payment_details'

- BillingItem
  - id: int
  - student_id: int
  - syid: int
  - description: string
  - amount: number
  - posted_at: string|null
  - remarks: string|null
  - created_at: string|null
  - updated_at: string|null

- InvoiceBase (normalized from InvoiceService::normalizeRow)
  - id: int
  - student_id: int
  - syid: int
  - registration_id: int|null
  - type: string
  - status: string
  - invoice_number: string|int|null
  - amount_total: number
  - posted_at: string|null
  - due_at: string|null
  - remarks: string|null
  - payload: object|null (may contain items[] or invoice_items[])
  - campus_id: int|null
  - cashier_id: int|null
  - created_by: int|null
  - updated_by: int|null
  - created_at: string|null
  - updated_at: string|null

- InvoiceEnriched extends InvoiceBase with computed fields
  - _total: number|null
  - _paid: number
  - _remaining: number|null
  - _reservation_applied: number
  - _total_effective: number|null
  - _remaining_effective: number|null

- CashierViewerMeta
  - amount_paid: number                 // adjusted per FinanceService (including journal debits/credits) from payment_details
  - billing_paid: number                // sum of Paid toward 'billing' invoices or non-tuition/reservation classifications (server-side)
  - reservation_paid: number            // sum of Paid Reservation payments for current term
  - invoice_counts:
    - total: int
    - tuition: int
    - billing: int
  - totals:
    - invoices_total: number            // sum of invoice.amount_total
    - invoices_paid_total: number       // sum of invoice._paid
    - invoices_remaining_total: number  // sum of invoice._remaining
  - sy_label: string|null               // convenience copy

Validation rules:
- Query params: student_number (required, string), term (required, integer)
- Optional: allow student_id as a non-advertised param for fallback in mixed datasets (do not document publicly). Server resolves student_id via student_number as primary behavior.

[Files]
Create one new service, add one controller method, wire one route, and add one frontend service function and controller consumption. No files deleted.

- New files to be created
  - laravel-api/app/Services/CashierViewerAggregateService.php
    - Purpose: Encapsulate all aggregation logic for the cashier viewer endpoint (resolve student, load invoices/payment_details/student_billing/missing_billing, compute enrichments and meta).
  - plans/cashier-viewer-aggregate/implementation_plan.md
    - Purpose: This plan document (current file).

- Existing files to be modified
  - laravel-api/routes/api.php
    - Add GET /api/v1/finance/cashier/viewer-data → FinanceController@viewerData with middleware('role:finance,admin').
  - laravel-api/app/Http/Controllers/Api/V1/FinanceController.php
    - Add public function viewerData(Request $request): JsonResponse delegating to CashierViewerAggregateService.
  - frontend/unity-spa/features/unity/unity.service.js
    - Add cashierViewerData(params) to call GET /finance/cashier/viewer-data with headers (X-Faculty-ID).
  - frontend/unity-spa/features/finance/cashier-viewer/cashier-viewer.controller.js
    - Add vm.loadViewerData(force) to replace the 4 separate calls (loadInvoices, loadPaymentDetails, loadBilling, loadMissingBilling) with a single request and state-binding. Keep legacy loaders as fallback behind a feature flag or progressive migration path.

- Files to be deleted or moved
  - None.

- Configuration file updates
  - None.

[Functions]
Add an aggregate method on the controller, a new service with small helper methods, and new FE calls. Existing functions remain, but client flow will prefer the aggregate call.

- New functions (backend)
  - FinanceController::viewerData(Request $request): JsonResponse
    - Path: laravel-api/app/Http/Controllers/Api/V1/FinanceController.php
    - Purpose: Validate student_number (required) and term (required), optionally student_id fallback, then call CashierViewerAggregateService::buildByStudentNumber and return the JSON with success/data.
    - Signature: public function viewerData(Request $request): JsonResponse
  - CashierViewerAggregateService::buildByStudentNumber(string $studentNumber, int $term, ?int $studentId = null): array
    - Path: laravel-api/app/Services/CashierViewerAggregateService.php
    - Purpose: Orchestrate data loading and enrichment. Steps:
      1. Resolve student_id via tb_mas_users by studentNumber when not supplied.
      2. Query payment_details via FinanceService::listPaymentDetails(studentNumber, term, studentId) to get base items and meta.
      3. Query invoices via InvoiceService::list(['student_number' => ..., 'syid' => term]); compute per-invoice _paid using payment_details matches by invoice_number and status='Paid'.
      4. For tuition invoices, compute reservation offsets based on PaymentDetails reservation-like rows and set _reservation_applied; derive _total_effective and _remaining_effective.
      5. Query student_billing via StudentBillingService::list(studentNumber, null, term).
      6. Query missing_billing via StudentBillingExtrasService::listMissingInvoices(studentId, term).
      7. Compute meta: amount_paid (from FinanceService meta with journal adjustments), billing_paid (sum 'Paid' rows classified for billing; or inferred when invoice type='billing' else non-tuition/reservation), reservation_paid (Paid reservation rows sum), invoice_counts and totals.
      8. Return the assembled array payload.
    - Returns: CashierViewerData array (see Types).
  - CashierViewerAggregateService::computeInvoiceEnrichment(array $invoices, array $paymentItems, float $reservationPaid): array
    - Purpose: Given normalized invoices and payment_details items, compute enriched per-invoice metrics. For tuition invoices, use reservationPaid as an offset to total when computing effective totals.
  - CashierViewerAggregateService::sumReservationPaid(array $paymentItems): float
    - Purpose: Sum Paid payment_details with description LIKE 'Reservation%'.
  - CashierViewerAggregateService::sumBillingPaid(array $paymentItems, array $invoiceTypeIndex): float
    - Purpose: Sum Paid toward billing invoices (type='billing') or rows not clearly tuition/reservation, to derive meta.billing_paid.

- Modified functions (frontend)
  - UnityService.cashierViewerData(params): Promise<CashierViewerDataResponse>
    - Path: frontend/unity-spa/features/unity/unity.service.js
    - Purpose: Thin GET wrapper to the new endpoint, with admin headers.
  - CashierViewerController: add vm.loadViewerData(force): Promise
    - Path: frontend/unity-spa/features/finance/cashier-viewer/cashier-viewer.controller.js
    - Purpose: Replace multi-call chains by one call; set vm.invoices (enriched), vm.paymentDetails, vm.billingItems, vm.missingBilling, vm.meta; trigger recompute helpers that depend on these models (e.g., recomputeInvoicesPayments becomes mostly a no-op or integrity check).

- Removed functions
  - None (keep existing loaders for fallback and easy rollback).

[Classes]
Introduce one new Laravel service class; extend controller.

- New classes
  - App\Services\CashierViewerAggregateService
    - Path: laravel-api/app/Services/CashierViewerAggregateService.php
    - Key methods:
      - buildByStudentNumber(string $studentNumber, int $term, ?int $studentId = null): array
      - computeInvoiceEnrichment(array $invoices, array $paymentItems, float $reservationPaid): array
      - sumReservationPaid(array $paymentItems): float
      - sumBillingPaid(array $paymentItems, array $invoiceTypeIndex): float
    - Inheritance: none.

- Modified classes
  - App\Http\Controllers\Api\V1\FinanceController: add viewerData(Request)

- Removed classes
  - None.

[Dependencies]
No new external packages required; reuse existing services and DB schema.

- Using:
  - App\Services\FinanceService
  - App\Services\InvoiceService
  - App\Services\StudentBillingService
  - App\Services\StudentBillingExtrasService
  - Illuminate\Support\Facades\DB
  - Illuminate\Support\Facades\Schema (within existing services)

[Testing]
Introduce API and UI tests to validate server enrichment and client integration.

- API tests (manual / Postman / curl)
  1) Aggregate fetch
     - curl -G "http://localhost/iacademy/cebu-iac-lms/laravel-api/public/api/v1/finance/cashier/viewer-data" ^
         --data-urlencode "student_number=2020-00001" ^
         --data-urlencode "term=20251" ^
         -H "X-Faculty-ID: <faculty_id>"
     - Expect: 200, success=true, data with invoices[], payment_details.meta totals, student_billing[], missing_billing[], meta with invoice_counts and totals.
  2) Cross-check per-invoice paid/remaining
     - Compare data.invoices[*]._paid vs sum of payment_details items with the same invoice_number and status 'Paid'.
  3) Tuition effective totals
     - For tuition invoices, verify _reservation_applied equals reservation_paid from payment_details and _remaining_effective computations match: max(0, (amount_total - reservation_paid) - _paid).
  4) Missing billing list integrity
     - Compare missing_billing[] results to GET /finance/student-billing/missing-invoices to ensure parity.
  5) Error handling
     - Missing student_number or term: 422 validation.

- Frontend validation
  - Open Cashier Viewer for a student with activity:
    - Ensure initial bootstrap uses vm.loadViewerData() and the panels render correctly.
    - Generate a payment; refresh should update amounts and recomputed invoice remaining values without extra requests.
    - Missing-invoices modal badge count should match data.missing_billing.length.

[Implementation Order]
Backend-first, then frontend wiring, then switch controller to use new endpoint.

1) Backend: Service
   - Create App\Services\CashierViewerAggregateService with:
     - buildByStudentNumber(studentNumber, term, studentId?): array
     - computeInvoiceEnrichment(invoices, paymentItems, reservationPaid)
     - sumReservationPaid(paymentItems)
     - sumBillingPaid(paymentItems, invoiceTypeIndex)

2) Backend: Controller + Routes
   - In FinanceController, add viewerData(Request): JsonResponse
     - Validate: student_number (required string), term (required integer), optional student_id (integer).
     - Delegate to CashierViewerAggregateService and return { success, data }.
   - In routes/api.php, register:
     - GET /api/v1/finance/cashier/viewer-data → FinanceController@viewerData middleware('role:finance,admin').

3) Backend: Enrichment details
   - Build invoice index by invoice_number → type.
   - Compute per-invoice _paid, _remaining from payment_details Paid rows.
   - Compute reservation_paid via description LIKE 'Reservation%'.
   - For each tuition invoice:
     - _reservation_applied = reservation_paid
     - _total_effective = max(0, amount_total - reservation_paid)
     - _remaining_effective = max(0, _total_effective - _paid)

4) Frontend: UnityService
   - Add function cashierViewerData(params): returns $http.get(BASE + '/finance/cashier/viewer-data', { params }, _adminHeaders()).

5) Frontend: CashierViewerController
   - Add vm.loadViewerData(force):
     - Guard by vm.sn (student_number) and vm.term (int)
     - Call UnityService.cashierViewerData({ student_number: vm.sn, term: vm.term })
     - Bind data:
       - vm.invoices = data.invoices (enriched)
       - vm.paymentDetails = data.payment_details
       - vm.paymentDetailsTotal = data.payment_details.meta.total_paid_filtered
       - vm.billingItems = data.student_billing
       - vm.missingBilling = data.missing_billing
       - vm.badge.count = data.missing_billing.length
       - vm.meta.amount_paid = data.meta.amount_paid
       - vm.meta.billing_paid = data.meta.billing_paid
       - vm.meta.reservation_paid = data.meta.reservation_paid
     - Trigger:
       - vm.refreshTuitionSummary()
       - Recompute installment panel if applicable
   - Replace bootstrap chain:
     - After loadStudent(), term application, and tuition-years load, call vm.loadViewerData()
     - Remove redundant immediate calls to vm.loadInvoices(), vm.loadPaymentDetails(), vm.loadBilling(), vm.loadMissingBilling() (leave as fallback feature-flag if needed).

6) Regression checklist
   - Printing, assign-number modal, and any per-invoice UI should still work (enriched invoices now carry _paid/_remaining so client recomputation may be skipped or simplified).
   - Partial-payment side panel uses vm.paymentDetails to compute paid tuition; these values remain correct as payment_details block is present in the payload.
   - Application/Reservation fee generators still call StudentBillingService.create, then vm.loadViewerData(true) to refresh everything at once.

7) Testing and stabilization
   - Execute API tests with diverse students (with/without registration, applicants, different invoice types).
   - Validate client render state equals or improves over previous multiple-call approach.
   - Compare latency and number of requests before/after.
