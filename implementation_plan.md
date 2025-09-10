# Implementation Plan

[Overview]
Enable finance users to apply an excess (negative closing balance) from a selected student term as a payment to another term, and allow reverting this allocation, with the ledger view reflecting these applications and reversions.

This feature addresses a common finance workflow where students may overpay in one term and need the credit applied to amounts due in other terms. The system currently provides a term-filtered student ledger with assessment and payment rows, but lacks cross-term allocation mechanics. This plan introduces a robust, auditable allocation mechanism recorded in the backend and surfaced in the UI. The backend will record allocation applications and their status changes (applied/reverted) without mutating historical assessment/payment sources. The ledger response will be augmented with virtual rows representing the allocation out of the source term and allocation into the target term to preserve transparent running balances. The frontend will provide clear apply and revert controls on the ledger page, enforce constraints (e.g., not exceeding available credit or target due), and refresh the view accordingly.

[Types]  
Introduce a new ExcessPaymentApplication record and typed API contracts for apply/revert and augmented ledger response.

Data Structures:
- Table: excess_payment_applications
  - id: int, PK, auto-increment
  - student_id: int, required, FK to tb_mas_users.intID (or student info id used by ledger)
  - source_term_id: int, required, FK to tb_mas_sy.intID
  - target_term_id: int, required, FK to tb_mas_sy.intID
  - amount: decimal(12,2), required, positive
  - status: enum('applied','reverted') default 'applied'
  - created_by: int, nullable (FK to faculty/user id if available)
  - reverted_by: int, nullable
  - reverted_at: timestamp, nullable
  - notes: text, nullable
  - created_at/updated_at timestamps
  - Indexes: (student_id), (source_term_id), (target_term_id), (status)

- API payloads:
  - ApplyExcessRequest (JSON)
    - student_id: int (required)
    - source_term_id: int (required)
    - target_term_id: int (required)
    - amount: number (required, > 0)
    - notes: string (optional)
  - ApplyExcessResponse
    - success: boolean
    - data: {
        id: int,
        student_id, source_term_id, target_term_id, amount, status,
        created_by, created_at
      }
  - RevertExcessRequest (JSON)
    - application_id: int (required)
    - notes: string (optional)
  - RevertExcessResponse
    - success: boolean
    - data: {
        id: int,
        status: 'reverted',
        reverted_by, reverted_at
      }

- Ledger augmentation (server response)
  - Existing ledger: {
      meta: { opening_balance, total_assessment, total_payment, closing_balance, terms_included },
      rows: LedgerRow[]
    }
  - Augmented: include excess allocation rows (source vs target term):
    - Transfer Out (source term): 
      - ref_type: 'excess_transfer_out'
      - source: 'excess_application'
      - assessment: +amount (reduces negative credit towards zero)
      - remarks: 'Transfer to {target_sy_label} (App #{id})'
    - Transfer In (target term):
      - ref_type: 'excess_transfer_in'
      - source: 'excess_application'
      - payment: +amount (reduces positive due)
      - remarks: 'Transfer from {source_sy_label} (App #{id})'
    - Only include rows with status='applied'.

[Files]
Introduce one new service and one new model; update controller, routes, frontend controller/service/template, and migrations.

Detailed breakdown:
- New files:
  - laravel-api/app/Models/ExcessPaymentApplication.php
    - Eloquent model for excess_payment_applications table.
  - laravel-api/app/Services/ExcessPaymentService.php
    - Encapsulates business logic to validate, apply, revert allocations and augment ledger response.

- Existing files to modify:
  - laravel-api/app/Http/Controllers/Api/V1/FinanceController.php
    - Add endpoints: applyExcessPayment(), revertExcessPayment()
    - In studentLedger(), post-process response via ExcessPaymentService->augmentLedger().
  - laravel-api/routes/api.php
    - Add routes:
      - POST /v1/finance/ledger/excess/apply  (role: finance,admin)
      - POST /v1/finance/ledger/excess/revert (role: finance,admin)
  - laravel-api/app/Services/StudentLedgerService.php
    - No invasive changes required. Keep existing logic; allocations are injected via augmentation to preserve stability.
  - frontend/unity-spa/features/finance/ledger.service.js
    - Add methods: applyExcess(payload), revertExcess(applicationId), getLedgerApplications(optional).
  - frontend/unity-spa/features/finance/ledger.controller.js
    - Add UI logic and state:
      - Detect when term != 'all' and vm.summary.closing < 0 (excess credit).
      - Allow selection of target term and amount; default amount = min(abs(sourceClosing), max(0, targetTermClosing)).
      - Trigger apply/revert via FinanceLedgerService; refresh ledger after completion.
      - Render markers for transfer rows; enable revert for applicable items.
  - frontend/unity-spa/features/finance/ledger.html
    - Add a compact panel above the table:
      - If closing < 0: show "Excess available" with select target term and amount input; Apply button.
      - If any applied transfers exist in current view: list with Revert action per item.

- Files to be created (migration updates):
  - laravel-api/database/migrations/YYYY_MM_DD_HHMMSS_add_columns_to_excess_payment_applications.php
    - Add created_by, reverted_by, reverted_at, notes (if missing).
    - Add necessary indexes.
  - laravel-api/tests/Feature/FinanceExcessPaymentTest.php (optional if time permits per testing scope).

- Configuration file updates:
  - None.

[Functions]
Add new endpoints and service functions; modify ledger shaping pipeline.

Detailed breakdown:
- New functions:
  - laravel-api/app/Services/ExcessPaymentService.php
    - public function applyExcessPayment(int $studentId, int $sourceTermId, int $targetTermId, float $amount, ?int $actorId = null, ?string $notes = null): array
      - Validations:
        - Source term closing (including existing applied transfers) must be <= -amount (sufficient credit).
        - Target term closing (including applied transfers) must be >= amount (has at least that much due), or allow partial if strictly required by business rules; plan uses caller-provided amount validated by bounds.
        - sourceTermId != targetTermId.
      - Creates ExcessPaymentApplication with status='applied'.
      - Returns application record as array.
    - public function revertExcessPayment(int $applicationId, ?int $actorId = null, ?string $notes = null): array
      - Validations:
        - Application exists and status='applied'.
      - Marks status='reverted', sets reverted_by, reverted_at, notes (append).
      - Returns updated application.
    - public function augmentLedger(array $ledger): array
      - Fetches all applied applications for student present in ledger scope (by 'all' or specific term).
      - For each application:
        - If ledger scoped to 'all', inject both out/in rows.
        - If scoped to specific term, inject only the relevant row (out if source == term, in if target == term).
      - Recompute meta totals by including injected rows.

  - frontend/unity-spa/features/finance/ledger.service.js
    - applyExcess(payload: { student_id, source_term_id, target_term_id, amount, notes? }): Promise<ApplyExcessResponse>
      - POST BASE + '/finance/ledger/excess/apply'
    - revertExcess(application_id: number, notes?): Promise<RevertExcessResponse>
      - POST BASE + '/finance/ledger/excess/revert'
    - (optional) listApplications(params): Retrieve app-level data if a dedicated endpoint is later added.

  - frontend/unity-spa/features/finance/ledger.controller.js
    - computeTargetSuggestion(targetTermId): number
      - Calls getLedger for target term (or caches from 'all') and returns min(abs(sourceClosing), max(0, targetClosing)).
    - applyExcess(): Validate inputs, call service, refresh.
    - revertExcess(appId): Confirm, call service, refresh.
    - enrichRowsWithFlags(): Mark transfer rows for UI badges.

- Modified functions:
  - laravel-api/app/Http/Controllers/Api/V1/FinanceController.php::studentLedger()
    - After $svc->getLedger(...), pipe result through ExcessPaymentService->augmentLedger().
  - frontend/unity-spa/features/finance/ledger.controller.js
    - search(): After fetching ledger, detect negative closing; preselect target term and amount suggestion; surface UI.

- Removed functions:
  - None

[Classes]
Add an Eloquent model and a new service.

Detailed breakdown:
- New classes:
  - App\Models\ExcessPaymentApplication
    - $table = 'excess_payment_applications'
    - $fillable = ['student_id','source_term_id','target_term_id','amount','status','created_by','reverted_by','reverted_at','notes']
    - casts: amount => 'decimal:2', reverted_at => 'datetime'
  - App\Services\ExcessPaymentService
    - Methods as specified above; depends on DB, ExessPaymentApplication model, and StudentLedgerService for computing closings.

- Modified classes:
  - App\Http\Controllers\Api\V1\FinanceController
    - Add applyExcessPayment(), revertExcessPayment()
  - App\Services\StudentLedgerService
    - No change in public contract; logic remains isolated. Ledger augmentation is handled by ExcessPaymentService.

- Removed classes:
  - None

[Dependencies]
No new external dependencies; leverage existing Laravel components (Eloquent, DB, validation, middleware).

Details:
- Route middleware: role:finance,admin enforced on apply/revert endpoints.
- Rely on existing TermService / GenericApiController for term labels when enriching remarks.

[Testing]
Critical-path UI tests and minimal API tests.

Test requirements:
- UI:
  - Given a student and term with closing < 0, the excess panel displays with default amount suggestion.
  - Applying allocation updates the ledger and reduces source closing and target due.
  - Transfers are visible as rows with appropriate labels.
  - Revert action is available for applied transfers and restores ledger state.
- API:
  - Apply: validates bounds, creates application, returns success and record.
  - Revert: toggles status to 'reverted', returns updated record.
  - Ledger: includes augmented rows for applied (and excludes reverted) applications; meta totals adjust.

Validation strategies:
- Manual via SPA (preferred by request).
- Minimal HTTP tests via Postman/Thunder Client or a small Feature test for apply/revert.

[Implementation Order]
Implement backend foundation first, then API routes, then frontend wiring and UI, followed by testing.

1) Database: Ensure excess_payment_applications table exists and has required columns; add migration to include created_by, reverted_by, reverted_at, notes if missing.
2) Backend model: Create App\Models\ExcessPaymentApplication with fillable and casts.
3) Backend service: Create App\Services\ExcessPaymentService with apply, revert, augmentLedger.
4) API: Update FinanceController to add apply/revert endpoints and to augment studentLedger response; add routes with proper middleware.
5) Frontend service: Add applyExcess and revertExcess methods to ledger.service.js.
6) Frontend controller: Add UI state and handlers; integrate into search lifecycle.
7) Frontend template: Add apply/revert panel and row badges.
8) Manual tests: Exercise apply/revert and verify ledger changes.
9) Optional: Add Feature test for API apply/revert.
