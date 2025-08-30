# Cashier CRUD Implementation Tracker

Scope: Complete Cashier Administration CRUD (Create, Read, Update, Delete) for cashier_admin/admin roles across Laravel API and AngularJS SPA.

Status Summary
- Backend (Laravel)
  - [x] Routes: GET/POST/PATCH/DELETE endpoints for /api/v1/cashiers incl. stats and assign
  - [x] Controller: CashierController with index, store, update, updateRanges, assign, stats, statsAll, show, destroy
  - [x] Service: CashierService with overlap/usage validation and stats
  - [x] Requests: CashierStoreRequest, CashierUpdateRequest, CashierRangeUpdateRequest, CashierAssignRequest
  - [x] Model: Cashier (tb_mas_cashiers, intID PK)
  - [x] Migrations:
    - 2025_08_29_000600_create_tb_mas_cashiers_table.php
    - 2025_08_29_000700_add_faculty_id_to_tb_mas_cashiers.php (unique campus_id+faculty_id)
- Frontend (AngularJS)
  - [x] RBAC: roles.constants.js includes ^/cashier-admin → ['cashier_admin','admin']
  - [x] Route: /cashier-admin → CashiersController + list.html
  - [x] Service: list, create, update, updateRanges, stats, statsAll, assign, searchFaculty
  - [x] Controller:
    - [x] List loading (campus filter)
    - [x] Toggle temporary_admin
    - [x] Save current pointers
    - [x] Edit ranges (+ campus_id change)
    - [x] Assignment flow (search faculty, assign)
    - [x] Create modal (faculty search, campus_id, OR/Invoice ranges)
    - [x] Delete action with conflict surfacing
  - [x] View: list.html table + Create modal + Delete button + campus edit when editing ranges

Pending Work
1) Database setup
   - [ ] Run migrations
     - Command: cd laravel-api &amp;&amp; php artisan migrate
   - [ ] (Optional) Seed a sample cashier if list is empty
     - Command: php laravel-api/scripts/seed_cashiers.php

2) API smoke tests
   - [ ] Internal smoke: scripts/test_cashiers_api.php
     - Command: php laravel-api/scripts/test_cashiers_api.php
   - [ ] Assign flow critical path: scripts/test_cashiers_assign.php
     - Command: php laravel-api/scripts/test_cashiers_assign.php
   - [ ] Thorough tests: scripts/test_cashiers_thorough.php
     - Command: php laravel-api/scripts/test_cashiers_thorough.php

3) Manual QA (UI)
   - [ ] Navigate to #/cashier-admin as cashier_admin/admin
   - [ ] Create cashier with campus-aligned faculty and initial ranges
   - [ ] Edit ranges (validate: no overlaps; no used-number conflicts)
   - [ ] Update current pointers (validate bounds)
   - [ ] Assign to faculty (validate campus match &amp; per-campus uniqueness)
   - [ ] Refresh stats; verify used/remaining/last_used values
   - [ ] Try Delete; verify:
     - [ ] Succeeds when no used-number conflicts in ranges
     - [ ] Returns 422 with meaningful error when conflicts exist

4) UX improvements (optional)
   - [ ] Replace inline range editing with a modal and richer validation hints
   - [ ] Add disable states and inline error displays for Create modal

5) Documentation
   - [ ] Add README section: API contract and example curl for each endpoint
   - [ ] Add rollout notes including migration names and seed instructions

6) Roles/Access
   - [ ] Verify cashier_admin role presence in target environment (see scripts/grant_role.php)
   - [ ] Confirm RequireRole middleware gating: role:cashier_admin,admin

Reference Files
- Backend
  - laravel-api/routes/api.php
  - laravel-api/app/Http/Controllers/Api/V1/CashierController.php
  - laravel-api/app/Services/CashierService.php
  - laravel-api/app/Models/Cashier.php
  - laravel-api/app/Http/Requests/Api/V1/CashierStoreRequest.php
  - laravel-api/app/Http/Requests/Api/V1/CashierUpdateRequest.php
  - laravel-api/app/Http/Requests/Api/V1/CashierRangeUpdateRequest.php
  - laravel-api/app/Http/Requests/Api/V1/CashierAssignRequest.php
  - laravel-api/database/migrations/2025_08_29_000600_create_tb_mas_cashiers_table.php
  - laravel-api/database/migrations/2025_08_29_000700_add_faculty_id_to_tb_mas_cashiers.php
  - laravel-api/scripts/test_cashiers_api.php
  - laravel-api/scripts/test_cashiers_assign.php
  - laravel-api/scripts/test_cashiers_thorough.php
  - laravel-api/scripts/seed_cashiers.php
- Frontend
  - frontend/unity-spa/core/roles.constants.js
  - frontend/unity-spa/core/routes.js
  - frontend/unity-spa/features/cashiers/cashiers.service.js
  - frontend/unity-spa/features/cashiers/cashiers.controller.js
  - frontend/unity-spa/features/cashiers/list.html
  - frontend/unity-spa/features/cashiers/TODO.md

Notes
- CashierController joins tb_mas_faculty for display name; no dependency on users table for names.
- Service validates both overlaps across cashiers and usage conflicts against payment_details columns or_no/or_number/invoice_number (auto-detected).
- Campus must match between cashier.campus_id and faculty.campus_id for assignment and creation.
