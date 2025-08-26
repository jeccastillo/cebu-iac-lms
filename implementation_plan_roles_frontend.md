# Implementation Plan

[Overview]
Introduce role-based access control (RBAC) to the AngularJS Unity SPA by extending the login state with roles and enforcing route and navigation visibility based on those roles. This will gate UI access to modules (faculty, registrar, finance, scholarship, campuses, students) using a front-end-only approach, with a pluggable role provider that can later be switched to a backend-sourced role feed.

The SPA currently enforces only “logged in” guards and minimally shows faculty-only items in the sidebar using loginType. We will add a central RoleService, annotate routes with requiredRoles, and update the global guard and sidebar/menu logic to check roles. Because the scope is “frontend only,” we will introduce a configurable role provider with safe defaults: (a) accept roles from loginState if already present (future backend), (b) fall back to a configurable mapping by username or pattern from window.USER_ROLE_MAP (no server dependency), and (c) derive minimal roles from loginType for backward compatibility.

[Types]  
Add a compact type system for RBAC data to the SPA.

Detailed type definitions, interfaces, enums, or data structures:
- Role (string enum-like): 'faculty' | 'registrar' | 'finance' | 'scholarship' | 'campus_admin' | 'student_view' | 'admin'
- LoginState (extends existing shape):
  - loggedIn: boolean
  - username: string
  - loginType: 'faculty' | 'student'
  - ts: number
  - roles?: string[]  // optional array of Role
- RoleProviderConfig (window-injected or constants):
  - USER_ROLE_MAP?: { [username: string]: string[] } // pure frontend mapping, optional
  - DEFAULT_FACULTY_ROLES?: string[] // e.g., ['faculty']
  - DEFAULT_STUDENT_ROLES?: string[] // e.g., ['student_view']
- AccessMatrix
  - A mapping of route patterns → allowed roles:
    - '/faculty/*' → ['faculty', 'admin']
    - '/registrar/*' → ['registrar', 'admin']
    - '/finance/*' → ['finance', 'admin']
    - '/scholarship/*' → ['scholarship', 'admin']
    - '/campuses' and '/campuses/*' → ['campus_admin', 'admin']
    - '/students' → ['registrar', 'scholarship', 'finance', 'admin']  // list visibility
    - '/students/:id' → same as '/students'
    - '/dashboard' → any authenticated (no roles required)
  - Unlisted routes default: any authenticated (non-restricted)

[Files]
Add a RoleService, a role config/constants module, annotate routes with requiredRoles, and update guards and sidebar visibility.

Detailed breakdown:
- New files to be created:
  - frontend/unity-spa/core/roles.constants.js
    - Purpose: Define role names, AccessMatrix defaults, and read optional window.USER_ROLE_MAP overrides.
  - frontend/unity-spa/core/role.service.js
    - Purpose: Central role provider: resolve current roles, canAccess(path|route), hasRole(role), normalize loginState with roles, and pluggable resolution (loginState → window map → loginType fallback).

- Existing files to be modified:
  - frontend/unity-spa/core/routes.js
    - Add non-breaking metadata to each route definition: requiredRoles: string[] (or omit for public/auth-only).
    - Example: '/registrar/reports' → requiredRoles: ['registrar', 'admin']; '/dashboard' → omit or [] to mean auth-only.
  - frontend/unity-spa/core/run.js
    - Extend global $routeChangeStart guard: if next.$$route.requiredRoles exists, call RoleService.canAccess; if denied, redirect to '/dashboard' or '/login'.
    - Keep current “isProtected” behavior for loggedIn; add role check only when requiredRoles set.
  - frontend/unity-spa/shared/components/sidebar/sidebar.controller.js
    - Inject RoleService, compute vm.loginState and vm.roles, and expose vm.canAccess(path) for template.
  - frontend/unity-spa/shared/components/sidebar/sidebar.html
    - Wrap each nav entry with role checks using vm.canAccess('...') or inline RoleService.hasRole checks; keep faculty section with refined role checks (e.g., faculty/admin).
  - frontend/unity-spa/features/auth/login.controller.js
    - On successful login: optionally attach roles to loginState if present in future API response (data.roles); else call RoleService.deriveRoles(username, loginType) and set loginState.roles for consistent downstream checks.

- Files to be deleted or moved:
  - None.

- Configuration file updates:
  - None mandatory; role defaults can be overridden at runtime via window.USER_ROLE_MAP in frontend/unity-spa/index.html if desired (no change required if not used).

[Functions]
Introduce role resolution and gate checks; update guards and UI visibility accordingly.

Detailed breakdown:
- New functions:
  - RoleService.getRoles(): string[]
    - file: frontend/unity-spa/core/role.service.js
    - purpose: Returns current user roles by resolving in order: loginState.roles → USER_ROLE_MAP[username] → DEFAULT roles based on loginType.
  - RoleService.deriveRoles(username: string, loginType: string): string[]
    - file: frontend/unity-spa/core/role.service.js
    - purpose: Deterministically compute roles for setting into loginState at login if backend didn’t supply roles.
  - RoleService.hasRole(role: string): boolean
    - file: frontend/unity-spa/core/role.service.js
  - RoleService.canAccess(pathOrRoute: string|Route): boolean
    - file: frontend/unity-spa/core/role.service.js
    - purpose: Check AccessMatrix and/or route.requiredRoles against current roles; unlisted routes default to “auth-only allowed”.
  - RoleService.normalizeState(state): state
    - file: frontend/unity-spa/core/role.service.js
    - purpose: Ensure state has roles[] using deriveRoles if absent.

- Modified functions:
  - run.js → initialize() listener for $routeChangeStart:
    - Add role check block: if next.$$route.requiredRoles?.length > 0 and !RoleService.canAccess(next.$$route.originalPath), preventDefault and redirect.
  - LoginController.submit():
    - After successful login, compute roles: const roles = data.roles || RoleService.deriveRoles(username, loginType); put into loginState.roles before saving.
  - SidebarController.activate():
    - Inject RoleService, recompute vm.loginState and vm.roles on storage changes; add vm.canAccess = RoleService.canAccess for template usage.

- Removed functions:
  - None.

[Classes]
AngularJS services and controllers; no ES classes added, but new AngularJS service module introduced.

Detailed breakdown:
- New classes (conceptual service modules)
  - RoleService (AngularJS factory) in frontend/unity-spa/core/role.service.js
    - key methods: getRoles, deriveRoles, hasRole, canAccess, normalizeState
- Modified classes
  - Controllers: LoginController, SidebarController
  - App run block in run.js (not a class)

[Dependencies]
No new npm/bower dependencies required.

Details of new packages, version changes, and integration requirements:
- None. All logic implemented within AngularJS modules and plain JS.
- Optional: index.html can define window.USER_ROLE_MAP at bootstrap to inject per-user roles without backend changes.

[Testing]
Add manual validation and lightweight, framework-free checks consistent with the repo.

Test file requirements, existing test modifications, and validation strategies:
- Manual smoke:
  - Set window.USER_ROLE_MAP in frontend/unity-spa/index.html (temporary) for a few test accounts (e.g., registrar1 → ['registrar'], fin1 → ['finance'], admin1 → ['admin']).
  - Login with each user; verify:
    - Sidebar shows only allowed modules.
    - Navigating to restricted routes via URL is blocked and redirected (stays on dashboard).
  - Verify faculty-only section visible only with roles that include 'faculty' or 'admin'.
  - Verify students route visibility for roles in AccessMatrix (registrar/scholarship/finance/admin).
- Regression:
  - Existing login redirection & state storage unaffected for users without configured USER_ROLE_MAP (fallback to loginType-derived roles).
- Optional unit (if using any JS test harness locally): stub RoleService and test canAccess for several patterns.

[Implementation Order]
Implement in a sequence that minimizes UI breakage and keeps guard logic off until routes are annotated.

1) Scaffolding
   - Create roles.constants.js with Role names and AccessMatrix.
   - Create role.service.js with role resolution and canAccess.
2) Route Annotation
   - Update core/routes.js to add requiredRoles per route (registrar/finance/scholarship/campuses/faculty).
   - Leave dashboard without requiredRoles (auth-only).
3) Global Guard
   - Update core/run.js to consult RoleService.canAccess when requiredRoles exist (after login check).
4) Sidebar Visibility
   - Update SidebarController to inject RoleService; add vm.canAccess and expose roles; update sidebar.html to hide links based on role checks.
5) Login Flow Integration
   - Update LoginController to set loginState.roles from data.roles if present or derive via RoleService.
6) Smoke Test
   - (Optional) Add temporary window.USER_ROLE_MAP in index.html for demo users; verify UI gating and deep-link blocking.
