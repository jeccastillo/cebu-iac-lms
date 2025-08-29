# TODO — Cashier Administration (OR/Invoice Range Management)

Context:
- Implement a Cashier Admin page and secured APIs so the cashier_admin role can assign and manage OR and Invoice number ranges by cashier, with validations (overlaps and used-number conflicts) and usage stats.

Approach:
- Backend: Laravel (API, service, validators, role gating)
- Frontend: AngularJS (new /cashier-admin page, service, controller, role access)
- Role: cashier_admin (in addition to admin)
- Validations: 
  - No overlapping ranges across cashiers (OR & Invoice)
  - Block ranges that include already-used OR/Invoice numbers
  - start ≤ end; current in [start, end]; auto-reset current to start when range changes
- Stats: used_count, remaining_count, last_used (both OR and Invoice)

Task Progress Items:
- [ ] Step 1: Implement Laravel model, service, validators, controller, and routes with role gating
  - [ ] Model: app/Models/Cashier.php (tb_mas_cashiers)
  - [ ] Service: app/Services/CashierService.php (overlap/usage validation, stats)
  - [ ] Requests: CashierStoreRequest, CashierUpdateRequest, CashierRangeUpdateRequest
  - [ ] Controller: app/Http/Controllers/Api/V1/CashierController.php
    - index, store, update, updateRanges, stats, statsAll
  - [ ] Routes: routes/api.php (/api/v1/cashiers...)
  - [ ] Policy/Gate: cashier_admin OR Gate-based check in AuthServiceProvider

- [ ] Step 2: Implement validation logic for overlap and usage checks; stats computation
  - [ ] Overlap: OR/Invoice ranges across cashiers (scope campus_id where applicable)
  - [ ] Usage: conflicts against payment_details (or_no/or_number/invoice_number) and related tables
  - [ ] Stats: used_count, remaining_count, last_used

- [ ] Step 3: Seed/register cashier_admin role and configure gate in AuthServiceProvider
  - [ ] Seeder: RolesSeederCashierAdmin (if role table-based)
  - [ ] Ensure middleware/guards recognize cashier_admin

- [ ] Step 4: Update Angular ROLES and ACCESS_MATRIX, add route /cashier-admin, add sidebar link
  - [ ] roles.constants.js: add cashier_admin, ACCESS_MATRIX for ^/cashier-admin(?:/.*)?$ → ['cashier_admin','admin']
  - [ ] routes.js: register /cashier-admin
  - [ ] sidebar.html: add top-level “Cashier Admin” link (not under Finance)

- [ ] Step 5: Build Angular cashiers list page, service, controller, edit modal, stats display
  - [ ] features/cashiers/cashiers.service.js
  - [ ] features/cashiers/cashiers.controller.js
  - [ ] features/cashiers/list.html
  - [ ] Edit ranges (auto-reset current), toggle temporary_admin, show stats

- [ ] Step 6: Write Laravel feature tests; manual QA through the UI
  - [ ] Authorization tests (deny non-cashier_admin/admin)
  - [ ] Validations (overlap, used-number conflicts, start/end rules)
  - [ ] Stats endpoints
  - [ ] Manual QA: route/role visibility, CRUD flows, error handling, stats refresh

- [ ] Step 7: Prepare rollout notes and migration/seed instructions
  - [ ] Note DB columns assumed in tb_mas_cashiers
  - [ ] Seed cashier_admin role
  - [ ] API contract and example curl calls

Endpoints (planned):
- GET /api/v1/cashiers?includeStats=bool
- POST /api/v1/cashiers
- PATCH /api/v1/cashiers/{id}
- POST /api/v1/cashiers/{id}/ranges
- GET /api/v1/cashiers/{id}/stats
- (Optional) GET /api/v1/cashiers/stats

Testing Plan (per user instruction: proceed; thorough scope to be applied during implementation):
- Frontend: Full coverage of /cashier-admin flows; role visibility; validations surfaced in UI
- Backend: Full coverage of endpoints (happy/error/edge); curl examples; role denial; performance sanity
