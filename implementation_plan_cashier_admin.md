# Implementation Plan

[Overview]
Implement a Cashier Administration feature that allows the cashier_admin role to assign, validate, and manage OR (Official Receipt) and Invoice number ranges per cashier, with current pointers, usage stats, and strict validations to prevent overlaps and reuse. The backend is delivered via Laravel (API) and the frontend via the existing AngularJS 1.x SPA.

This feature adds:
- A secured API for managing tb_mas_cashiers (assign/update OR/Invoice ranges, toggle temporary_admin, retrieve stats).
- A new cashier_admin role and access gating.
- An AngularJS page to list cashiers, edit ranges (auto-reset current to start), and view usage stats (remaining/used/last-used).
- Strong validation to block overlapping ranges across cashiers and ranges that include already-used numbers in transactions.

[Types]
Defines the schema and DTOs to ensure consistent typing and validation across backend and frontend.

- Database Table: tb_mas_cashiers (existing; confirm columns on deploy)
  - intID: int (PK, auto-increment)
  - user_id: int (FK to users)
  - or_start: bigint (or int; store numeric OR start)
  - or_end: bigint (or int; OR end)
  - or_current: bigint (or int; current pointer; auto-reset to or_start on range change)
  - invoice_start: bigint (or int; Invoice start)
  - invoice_end: bigint (or int; Invoice end)
  - invoice_current: bigint (or int; current pointer; auto-reset to invoice_start on range change)
  - or_used: bigint or int (optional legacy; may be computed; if present keep updated)
  - invoice_used: bigint or int (optional legacy)
  - temporary_admin: tinyint/bool
  - campus_id: int|null (if multi-campus scoping is applicable)
  - created_at/updated_at: timestamps (nullable if legacy)

- Backend DTOs (JSON):
  - CashierResource (list item):
    {
      id: int,
      user_id: int,
      name: string, // derived from users table
      campus_id: int|null,
      temporary_admin: boolean|int,
      or: { start: number|null, end: number|null, current: number|null },
      invoice: { start: number|null, end: number|null, current: number|null }
    }

  - CashierStats:
    {
      id: int,
      or: {
        start: number|null, end: number|null, current: number|null,
        used_count: number, remaining_count: number, last_used: number|null
      },
      invoice: {
        start: number|null, end: number|null, current: number|null,
        used_count: number, remaining_count: number, last_used: number|null
      }
    }

  - CashierUpdateRequest (PATCH /cashiers/{id}):
    - temporary_admin: boolean|int (optional)
    - or_current: number (optional)
    - invoice_current: number (optional)

  - CashierRangeUpdateRequest (POST /cashiers/{id}/ranges or create):
    {
      user_id?: int, // required on create
      campus_id?: int|null,
      or_start?: number, or_end?: number,
      invoice_start?: number, invoice_end?: number
    }
    Rules:
    - When setting/changing any start/end, auto-reset current to start (auto-reset confirmed).
    - Enforce start <= end and non-negative integers.

- Validation/Business Rules:
  - Enforce uniqueness/no-overlap of OR ranges across cashiers (scoped by campus_id if present).
  - Enforce uniqueness/no-overlap of Invoice ranges across cashiers (scoped by campus_id if present).
  - Block assignment if any existing transactions already used numbers within the proposed ranges (for both OR and Invoice). Return first conflicting number (and conflict type) for clarity.
  - Single active range per cashier for OR and for Invoice (no history table; current resets on change).
  - Current must remain within [start, end]; when start/end updated, set current = start.

[Files]
Summarizes new files, edits, and configurations.

- Laravel (API)
  - New
    - app/Http/Controllers/Api/V1/CashierController.php
      - index(): list cashiers (+ optional include stats flag)
      - store(): create cashier row with ranges; validates overlaps and usage; sets current pointers to starts
      - update($id): modify temporary_admin and optionally current pointers (with bounds checks)
      - updateRanges($id): modify OR/Invoice ranges; auto-reset current; revalidate overlaps and usage
      - stats($id): per-cashier stats; computes used/remaining and last_used
      - statsAll(): stats for all cashiers (paged)
    - app/Http/Requests/Api/V1/CashierStoreRequest.php
    - app/Http/Requests/Api/V1/CashierUpdateRequest.php
    - app/Http/Requests/Api/V1/CashierRangeUpdateRequest.php
    - app/Models/Cashier.php // Eloquent model mapping tb_mas_cashiers (if not present)
    - app/Services/CashierService.php
      - validateRangeOverlap()
      - validateRangeUsage()
      - computeStats()
      - helpers to coalesce transaction tables/columns
    - app/Policies/CashierPolicy.php OR define Gate in AuthServiceProvider
    - database/seeders/RolesSeederCashierAdmin.php (adds cashier_admin role)
  - Edited
    - routes/api.php: add cashier routes under /api/v1 with auth middleware and gate
    - app/Providers/AuthServiceProvider.php: define Gate('cashier_admin') or role-based check
    - Possibly add migration (only if missing fields) to alter tb_mas_cashiers (add missing columns; ensure numeric types)
  - Optional
    - app/Http/Resources/CashierResource.php, CashierStatsResource.php for clean API responses

- AngularJS (frontend/unity-spa)
  - New
    - features/cashiers/list.html
    - features/cashiers/cashiers.controller.js
    - features/cashiers/cashiers.service.js
  - Edited
    - core/roles.constants.js
      - Add role cashier_admin to ROLES
      - Add access matrix entry for ^/cashier-admin(?:/.*)?$ to ['cashier_admin', 'admin']
    - core/routes.js
      - Register /cashier-admin to the new list view/controller
    - shared/components/sidebar/sidebar.html
      - Add “Cashier Admin” top-level link (not under Finance; per confirmation “No” to Finance group placement)
    - Optional: toast.service.js usage for success/errors

[Functions]
Details of new/modified functions with signatures and behavior.

- Backend (Controller)
  - index(Request $req): JSON
    - Query: joins tb_mas_cashiers -> users (name), optional campus filter
    - Query Params: includeStats=bool, page, perPage
    - Returns: list of CashierResource; if includeStats, adds stats per item (or provide stats endpoint separately)
  - store(CashierStoreRequest $req): JSON
    - Validates: user_id exists; OR/Invoice ranges valid; no overlaps; no used numbers in those ranges
    - Writes: row with or_current=or_start, invoice_current=invoice_start
  - update($id, CashierUpdateRequest $req): JSON
    - Allows changing temporary_admin; can adjust current pointers with bounds checks (must be within [start,end])
  - updateRanges($id, CashierRangeUpdateRequest $req): JSON
    - Updates any provided start/end; auto-sets current=start; revalidates overlaps and prior usage
  - stats($id): JSON -> CashierStats
  - statsAll(): JSON -> array of CashierStats (paged)
  - Private/Service helpers:
    - validateRangeOverlap($type, $start, $end, $excludeId, $campusId)
    - validateRangeUsage($type, $start, $end): checks transaction tables
      - OR check: payment_details.or_no or or_number, fallback to transaction tables as available
      - Invoice check: payment_details.invoice_number, other finance tables (common columns)
    - computeStats($cashierId):
      - used_count: count of payments in [start,end]
      - remaining: (end - current + 1) for simple tracking; optionally dynamic from used_count
      - last_used: max used OR or invoice within range

- Frontend
  - cashiers.service.js
    - list({includeStats}): GET /api/v1/cashiers
    - create(payload)
    - update(id, payload)
    - updateRanges(id, payload)
    - stats(id)
  - cashiers.controller.js
    - Loads list, binds filters
    - Edit modals for OR/Invoice ranges (start/end, auto-reset current)
    - Toggle temporary_admin
    - Displays usage stats (remaining, used_count, last_used)
    - Error handling: show first conflicting number and rule (overlap/used)
  - roles.constants.js (edited)
    - ROLES: add cashier_admin
    - ACCESS_MATRIX: add { test: '^/cashier-admin(?:/.*)?$', roles: ['cashier_admin','admin'] }
  - sidebar.html (edited)
    - Add link: href="#/cashier-admin", show if user has role cashier_admin or admin
  - routes.js (edited)
    - Register /cashier-admin => templateUrl/features/cashiers/list.html controller CashiersController

[Classes]
New and modified classes with a quick description.

- Laravel
  - app/Models/Cashier extends Model
    - protected $table = 'tb_mas_cashiers';
    - fillable: [user_id, campus_id, or_start, or_end, or_current, invoice_start, invoice_end, invoice_current, temporary_admin, or_used, invoice_used]
    - relationships: user()
  - app/Http/Requests/Api/V1/CashierStoreRequest extends FormRequest
    - rules: user_id required|exists, start/end integers min:0, start<=end; both OR and Invoice may be set
  - app/Http/Requests/Api/V1/CashierUpdateRequest
    - rules: temporary_admin boolean, or_current/invoice_current between ranges
  - app/Http/Requests/Api/V1/CashierRangeUpdateRequest
    - rules: any of [or_start,or_end,invoice_start,invoice_end]; start<=end; resets current
  - app/Services/CashierService
    - methods as described under Functions
  - Policies / Gates
    - Configure cashier_admin gate in AuthServiceProvider or Policy to allow index/store/update/updateRanges/stats endpoints.

- AngularJS
  - CashiersController: orchestrates data, modals, and calls service
  - CashiersService: wraps API calls, consistent error formatting

[Dependencies]
- No additional composer/npm packages are required.
- Ensure migrations/seeds register cashier_admin role (if role management is table-driven).
- Consistency with existing role middleware and guard.

[Testing]
- Laravel PHPUnit
  - Authorization: endpoints forbidden for non cashier_admin/admin users
  - Validation: rejects overlaps across different cashiers, rejects ranges with already-used numbers; accepts valid input; current auto-resets
  - Stats: returns accurate used_count, remaining_count, last_used (seed test data for payment_details)
- AngularJS
  - Unit tests (where applicable) for service method payloads/parsing
  - Manual QA:
    - Login as cashier_admin; open /cashier-admin; assign OR/Invoice ranges; see auto-reset current; view stats; attempt overlapping ranges => error; attempt range that includes used numbers => error; toggle temporary_admin; refresh list

[Implementation Order]
1) Backend scaffolding
   - Add model Cashier, service CashierService, request validators, controller with endpoints.
   - Add gates/policies to restrict to cashier_admin/admin.
   - Wire routes in routes/api.php under /api/v1/cashiers.
   - Add seed/migration to register cashier_admin role (if role table-based).

2) Validations & stats (service)
   - Implement overlap validator (scoped by campus_id if present).
   - Implement usage validator scanning existing transactions (payment_details and other finance tables as available) for or_no/or_number and invoice_number in [start,end].
   - Implement computeStats.

3) Frontend routing and RBAC
   - Update roles.constants.js: add cashier_admin in ROLES; add ACCESS_MATRIX rule for ^/cashier-admin(?:/.*)?$.
   - Add /cashier-admin route in core/routes.js.
   - Add menu entry “Cashier Admin” as top-level link in sidebar.html (not under Finance).

4) Frontend pages
   - Implement features/cashiers/{list.html, cashiers.controller.js, cashiers.service.js}.
   - Table with Name, OR start/end/current, Invoice start/end/current, Temporary Admin (toggle), Actions (edit).
   - Edit modal to update ranges with auto-reset.
   - Show stats (used_count, remaining, last_used) per cashier.

5) Tests
   - Add Laravel feature tests for success and failure cases.
   - QA pass on UI for end-to-end flows.

6) Harden & document
   - Add API docstrings to controllers/requests.
   - Ensure error messages include first conflicting number and type (overlap/used) for UX clarity.

Task Progress Items:
- [ ] Step 1: Implement Laravel model, service, validators, controller, routes with role gating
- [ ] Step 2: Implement validation logic for overlap and usage checks; stats computation
- [ ] Step 3: Seed/register cashier_admin role and configure gate in AuthServiceProvider
- [ ] Step 4: Update Angular ROLES and ACCESS_MATRIX, add route /cashier-admin, add sidebar link
- [ ] Step 5: Build Angular cashiers list page, service, controller, edit modal, stats display
- [ ] Step 6: Write Laravel feature tests; manual QA through the UI
- [ ] Step 7: Prepare rollout notes and migration/seed instructions
