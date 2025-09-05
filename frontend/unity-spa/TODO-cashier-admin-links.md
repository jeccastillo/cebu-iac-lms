# Cashier Admin Links Implementation

Goal: Provide quick access to Cashier Admin across the SPA:
- Header/navbar: Quick-access "Cashier Admin" link (visible to cashier_admin/admin).
- Dashboard: Add a card/button linking to `#/cashier-admin`.
- Finance section: Provide a link/button within Finance pages.
- Inside Cashier Admin page: Add sub-links/buttons (e.g., "Create Cashier", "Search Faculty", etc.)

Status: Completed

Changes Made

1) Dashboard
- Updated controller to expose RBAC helper and SPA links:
  - File: frontend/unity-spa/features/dashboard/dashboard.controller.js
  - Changes:
    - Injected RoleService
    - Exposed vm.canAccess = RoleService.canAccess
    - Exposed vm.nav = LinkService.buildSpaLinks()
- Added Quick Action link to Cashier Admin (role-gated):
  - File: frontend/unity-spa/features/dashboard/dashboard.html
  - Added a new list item under Quick Actions:
    - Link: `#!/cashier-admin`
    - Visibility: `ng-if="vm.canAccess('/cashier-admin')"`

2) Finance section
- Updated Finance Ledger controller to expose RBAC helper:
  - File: frontend/unity-spa/features/finance/ledger.controller.js
  - Injected RoleService and set vm.canAccess
- Added button to open Cashier Admin:
  - File: frontend/unity-spa/features/finance/ledger.html
  - Added a top-right button:
    - Link: `#!/cashier-admin`
    - Visibility: `ng-if="vm.canAccess('/cashier-admin')"`

3) Cashier Admin page
- Added sub-links/buttons in header action area:
  - File: frontend/unity-spa/features/cashiers/list.html
  - Added:
    - "Create Cashier" → `#!/cashier-admin` (entry point for creation workflow)
    - "Search Faculty" → `#!/faculty`
    - "Finance Ledger" → `#!/finance/ledger`

4) Header (pre-existing)
- Quick access link already present:
  - File: frontend/unity-spa/shared/components/header/header.html
  - Link uses: `ng-if="vm.canAccess('/cashier-admin')"` → `#!/cashier-admin`

Testing Checklist

- [ ] Login as cashier_admin or admin:
  - [ ] Verify header shows "Cashier Admin"
  - [ ] Verify Dashboard Quick Actions shows "Cashier Admin" and navigates to `#/cashier-admin`
- [ ] Finance → Ledger:
  - [ ] Verify "Cashier Admin" button appears and navigates
- [ ] Cashier Admin page:
  - [ ] Verify "Create Cashier" (links to `#/cashier-admin`)
  - [ ] Verify "Search Faculty" (links to `#/faculty`)
  - [ ] Verify "Finance Ledger" (links to `#/finance/ledger`)

RBAC Notes

- Route `/cashier-admin` is already gated to roles: `['cashier_admin','admin']`
- All new links are wrapped with `vm.canAccess('/cashier-admin')` where needed.

Files touched

- frontend/unity-spa/features/dashboard/dashboard.controller.js
- frontend/unity-spa/features/dashboard/dashboard.html
- frontend/unity-spa/features/finance/ledger.controller.js
- frontend/unity-spa/features/finance/ledger.html
- frontend/unity-spa/features/cashiers/list.html

No backend changes required.
