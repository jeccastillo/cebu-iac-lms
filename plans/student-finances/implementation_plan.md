# Implementation Plan

[Overview]
Add a unified Student/Applicant Finances page that uses the globally selected term to display tuition summary, amount paid, detailed payments, and invoices (tuition and installment billings). Provide an online payment flow that lets the user choose to pay tuition or a specific invoice, supports partial payments when the term registration paymentType is partial (installment), enables all online payment gateways except Onsite, and attaches invoice_number and term (syid) to the checkout for correct reconciliation.

This implementation is needed to give students and applicants self-service visibility of their financial status per term and allow them to initiate online payments safely. It integrates with existing tuition computation and payment gateway infrastructure, while introducing student-scoped, read-only endpoints that exclude finance-admin data and permissions. The page will respect the global term selector and unify finances for both audiences (students/applicants) under one route.

[Types]  
Type system changes introduce student-safe DTOs (API response shapes) and frontend models.

- Term
  - id: number (tb_mas_sy.intID)
  - label: string (e.g., "1st Sem 2024-2025")

- RegistrationMeta
  - id: number|null (tb_mas_registration.intRegistrationID)
  - paymentType: 'full'|'partial'|null

- TuitionSummary
  - total_full: number
  - total_installment: number|null
  - selected_total: number   // derived by paymentType
  - source: 'computed'|'saved'|'fallback'

- PaymentsSummary
  - total_paid: number
  - last_payment_date: string|null

- StudentFinancesSummary
  - student_id: number
  - student_number: string
  - term: { id: number, label: string }
  - registration: RegistrationMeta
  - tuition: TuitionSummary
  - payments: PaymentsSummary
  - outstanding: number
  - meta: {
      data_source: string, // diagnostics
      notes?: string|null
    }

- PaymentDetailItem (student-safe subset)
  - id: number
  - posted_at: string|null
  - method: string|null
  - amount: number
  - description: string|null
  - or_number: number|string|null
  - invoice_number: number|string|null
  - status: string|null

- InvoiceItem (student-safe subset; normalized via InvoiceService)
  - id: number
  - student_id: number
  - syid: number
  - registration_id: number|null
  - type: 'tuition'|'billing'|'other'
  - status: 'Draft'|'Issued'|'Paid'|'Void'|string
  - invoice_number: number|string|null
  - amount_total: number
  - posted_at: string|null
  - due_at: string|null
  - remarks: string|null

- OnlineModeItem (student-safe subset of PaymentMode)
  - id: number
  - name: string
  - type: 'fixed'|'percentage'
  - charge: number            // raw charge value (percent or fixed)
  - pmethod: string           // gateway method key
  - pchannel: string          // provider channel
  - is_active: boolean

- CheckoutPayload (frontend → PaymentGatewayController@checkout)
  - description: string
  - student_number: string
  - first_name: string
  - last_name: string
  - middle_name?: string
  - email: string
  - contact_number: string
  - mode_of_payment_id: number          // must be from OnlineModeItem; Onsite excluded
  - total_price_without_charge: number
  - total_price_with_charge: number
  - charge: number
  - order_items: Array<{ title: string, qty: number, price_default: number }>
  - syid?: number
  - invoice_number?: number|string
  - target: 'tuition'|'invoice'
  - mailing_fee?: number

Validation rules:
- Partial tuition payments are allowed only if registration.paymentType === 'partial'.
- When invoice_number is supplied, amount must not exceed invoice remaining; server will enforce.
- Online modes exclude Onsite and only include is_active modes.

[Files]
New student-facing feature files plus minimal backend endpoints and small controller/service enhancements.

- New frontend files:
  - frontend/unity-spa/features/student/finances/finances.html
    - Tailwind template rendering summary tiles, invoices table, payments table, and a “Pay Online” action with target selector.
  - frontend/unity-spa/features/student/finances/student-finances.controller.js
    - Loads summary, invoices, payments for the global term; computes installment suggestions when partial; drives checkout.
  - frontend/unity-spa/features/student/finances/student-finances.service.js
    - Wrapper service for student-scoped endpoints and checkout payload building.

- Existing frontend files to modify:
  - frontend/unity-spa/core/routes.js
    - Add route "/student/finances" with requiredRoles: ["student_view","admin"].
    - Add link from student dashboard if needed (or ensure navigation exists).
  - frontend/unity-spa/features/student-dashboard/student-dashboard.html
    - Optional: add a prominent link/button to “View Finances”.
  - frontend/unity-spa/index.html
    - Include scripts for the new controller and service.

- New backend files:
  - laravel-api/app/Http/Controllers/Api/V1/StudentFinancesController.php
    - student-scoped, read-only endpoints:
      - GET /api/v1/student/finances/summary
      - GET /api/v1/student/finances/invoices
      - GET /api/v1/student/finances/payment-modes (online only)

- Existing backend files to modify:
  - laravel-api/routes/api.php
    - Register the Student Finances endpoints (no admin middleware; student-safe data only).
  - laravel-api/app/Services/DataFetcherService.php
    - Add helper: getRegistrationMeta(int $studentId, int $syid): RegistrationMeta
  - laravel-api/app/Http/Controllers/Api/V1/PaymentGatewayController.php
    - Accept optional syid and invoice_number in checkout; store them on PaymentDetail when schema allows; enforce amount does not exceed invoice remaining if invoice_number present.
  - laravel-api/app/Services/InvoiceService.php
    - Add helper to compute invoice remaining (paid sum vs amount_total) for validation.
  - laravel-api/app/Services/FinanceService.php (optional)
    - Expose a student-safe listPaymentDetailsByTerm() to resolve posted_at, method, and compute totals with journal adjustments consistent with balances.

- No files to delete or move.

- Configuration updates:
  - None required; reuse payments config. Ensure payments.frontend URLs are correct.

[Functions]
New and modified functions across frontend/backend.

- Laravel (new)
  - StudentFinancesController::summary(Request): JsonResponse
    - Query: student_id or student_number, syid (required)
    - Flow: resolve registration (id, paymentType) → compute tuition (GET /tuition/compute via local service methods, or SavedTuition) → payments meta via StudentPaymentStatusService::termBalance() → shape StudentFinancesSummary.
  - StudentFinancesController::invoices(Request): JsonResponse
    - Query: student_id or student_number, syid, include_draft=1
    - Use InvoiceService::list filters; include Draft invoices as requested; return InvoiceItem[].
  - StudentFinancesController::paymentModes(Request): JsonResponse
    - Return online-only modes: is_active && pmethod != 'onsite'; map to OnlineModeItem.

  - DataFetcherService::getRegistrationMeta(int $studentId, int $syid): array
    - Returns { id, paymentType } from tb_mas_registration.

  - InvoiceService::getInvoicePaidTotal(int $invoiceNumber): float
    - Sum payment_details where invoice_number matches and status in ['Paid'].
  - InvoiceService::getInvoiceRemaining(int $invoiceNumber): float
    - amount_total - getInvoicePaidTotal(), clamped to >= 0.

- Laravel (modified)
  - PaymentGatewayController::checkout(CheckoutRequest $request): JsonResponse
    - Accept optional syid, invoice_number, target.
    - Persist syid (when column exists; otherwise ignore) and invoice_number on PaymentDetail.
    - Enforce (invoice_number provided) that subtotal (or requested pay amount) <= invoice remaining; 422 on violation with details.
    - Keep existing gateway branches and response shape.

- AngularJS frontend (new)
  - StudentFinancesService
    - api:
      - summary(params): GET /api/v1/student/finances/summary?student_id|student_number&amp;syid
      - invoices(params): GET /api/v1/student/finances/invoices?student_id|student_number&amp;syid&amp;include_draft=1
      - paymentModes(): GET /api/v1/student/finances/payment-modes
      - checkout(payload): POST /api/v1/payments/checkout
    - helpers:
      - computeCharges(mode, amount): { charge, total_with_charge }  // mirrors backend percentage min-floor logic (e.g., 28.00 min)
      - buildOrderItems(target, amount, context): array

  - StudentFinancesController
    - state: { loading, error, term, profile, summary, invoices, payments, modes, selection }
    - methods:
      - loadAll(): in parallel summary, invoices, payments, modes (payments from balances/ledger or summary.payments)
      - selectTarget(type: 'tuition'|'invoice', id? invoice_number)
      - suggestInstallmentAmounts(): number[]  // derive from tuition compute similar to Cashier Viewer
      - openCheckout(): validate allowed partial/full based on paymentType; cap by remaining; compute charges; build payload
      - submitCheckout(): call service.checkout and redirect/open payment link depending on gateway
      - currency helpers, formatting functions

- AngularJS frontend (modified)
  - routes.js: add route “/student/finances”
  - student-dashboard.html: add “View Finances” link (optional)

[Classes]
Class-level changes introduce a new controller and no new models.

- New classes
  - App\Http\Controllers\Api\V1\StudentFinancesController
    - Methods: summary, invoices, paymentModes
    - Uses: DataFetcherService, StudentPaymentStatusService, InvoiceService, FinanceService (for payment items if needed)

- Modified classes
  - App\Http\Controllers\Api\V1\PaymentGatewayController
    - checkout: support syid/invoice_number persistence and validation
  - App\Services\DataFetcherService
    - add getRegistrationMeta
  - App\Services\InvoiceService
    - add getInvoicePaidTotal, getInvoiceRemaining

- Removed classes
  - None

[Dependencies]
No new external dependencies.

- PHP/Laravel: use existing services (InvoiceService, FinanceService, StudentPaymentStatusService).
- Frontend: reuse AngularJS stack; no new libraries.

[Testing]
End-to-end testing via Postman and UI, with targeted server validations.

- API tests (manual/Postman)
  - GET /api/v1/student/finances/summary with valid student_id and syid returns consistent totals with /student/balances and /tuition/compute.
  - GET /api/v1/student/finances/invoices returns Draft and Issued invoices filtered by syid.
  - GET /api/v1/student/finances/payment-modes excludes Onsite and includes active online modes.
  - POST /api/v1/payments/checkout
    - With invoice_number: 422 when amount exceeds remaining; success with <= remaining; syid and invoice_number stored on PaymentDetail (when columns exist).
    - With tuition target: partial allowed only when paymentType=partial; otherwise require full remaining.

- Frontend tests (manual)
  - Global term selection changes data on Finances page.
  - Tuition summary cards show correct full/installment totals and outstanding = selected_total - total_paid.
  - Invoices table includes Draft + Issued; shows remaining per invoice if available.
  - Payments table shows recent rows and total paid matches summary.
  - Payment flow:
    - Select tuition (full or partial based on term paymentType) or a specific invoice.
    - Mode selection shows computed charges consistent with server.
    - Redirects/links work per gateway; webhooks mark payment Paid and show in payments list after refresh.

[Implementation Order]
Implement backend first, then frontend integration, then validation.

1) Backend: Add StudentFinancesController with summary, invoices, paymentModes endpoints (student-safe).
2) Backend: Extend DataFetcherService with getRegistrationMeta(studentId, syid).
3) Backend: Extend InvoiceService with getInvoicePaidTotal and getInvoiceRemaining helpers.
4) Backend: Update routes/api.php to register new endpoints under /api/v1/student/finances/* (no admin middleware).
5) Backend: Update PaymentGatewayController::checkout to accept and store syid and invoice_number and enforce remaining for invoice payments; keep charge recompute validation.
6) Frontend: Add route /student/finances in core/routes.js (requiredRoles ["student_view","admin"]).
7) Frontend: Create student-finances.service.js with summary, invoices, paymentModes, checkout wrappers and helper computations.
8) Frontend: Create student-finances.controller.js to orchestrate loading, selection, installment suggestions, and checkout flow.
9) Frontend: Create finances.html template with sections: Summary, Invoices, Payments, and Pay Online panel.
10) Frontend: Wire into index.html (script tags) and optionally add link from Student Dashboard to Finances page.
11) QA: Test scenarios for full vs partial, invoice overpay guard, multiple gateways (Paynamics, BDO Pay, Maxx), and webhook result visibility (Paid/Expired).
12) Finalize: Documentation notes, confirm roles and global term selection behavior.
