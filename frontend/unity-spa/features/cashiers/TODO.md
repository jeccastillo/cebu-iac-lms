# Cashier Admin — TODO

Scope: Frontend SPA for Cashier Administration page and RBAC. Backend endpoints and validators are already implemented.

Progress
- [x] RBAC: Add `cashier_admin` role constant and access matrix rule `^/cashier-admin(?:/.*)?$` (roles: `['cashier_admin','admin']`)
- [x] Routing: Register route `/cashier-admin` → `features/cashiers/list.html` + `CashiersController`
- [x] Sidebar: Add top-level "Cashier Admin" link (top-level, not under Finance)
- [x] Service: `features/cashiers/cashiers.service.js` wrapping:
  - GET `/cashiers?includeStats&campus_id`
  - POST `/cashiers`
  - PATCH `/cashiers/{id}`
  - POST `/cashiers/{id}/ranges`
  - GET `/cashiers/{id}/stats`
  - GET `/cashiers/stats`
- [x] Controller: `features/cashiers/cashiers.controller.js`
  - Load list (with campus filter binding)
  - Toggle `temporary_admin`
  - Update `or_current` / `invoice_current`
  - Edit & save ranges (OR/Invoice) with backend overlap/usage errors surfaced
  - Refresh stats per row or all
- [x] View: `features/cashiers/list.html` with table, inline edit UX, stats display
- [x] Index wiring: Include scripts in `index.html`

Next
- [ ] Optional: Add "Create Cashier" UI (form) to POST `/cashiers` (user_id, campus_id, initial ranges)
- [ ] UX: Replace inline range edit with modal and better validation hints
- [ ] QA: Manual verification
  - Login as `cashier_admin` or `admin`
  - Navigate to `#/cashier-admin`
  - Load list (optionally toggle Include stats)
  - Test:
    - Toggle Temporary Admin
    - Update current pointers (out-of-bounds should be rejected)
    - Edit ranges to an overlapping range (should be blocked)
    - Edit ranges overlapping used numbers (should be blocked and show first conflicting number)
    - Refresh stats and confirm used/remaining/last_used change
- [ ] Docs: Add README section with API contract & example curl
- [ ] Backend seed: Seeder for role `cashier_admin` if role table-based and not yet present
- [ ] Tests: Add frontend unit tests where applicable

Notes
- The page respects campus scope using the existing Campus selector (filters by `campus_id`).
- Backend routes enforce `middleware('role:cashier_admin,admin')`.
