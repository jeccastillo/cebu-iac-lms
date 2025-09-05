# Sidebar Hierarchical Menus — Implementation TODO

Goal: Implement proper hierarchical, collapsible second-level menus in the Unity SPA sidebar with role-aware visibility, persisted open/closed state, and correct active highlighting.

Scope:
- Files:
  - frontend/unity-spa/shared/components/sidebar/sidebar.controller.js
  - frontend/unity-spa/shared/components/sidebar/sidebar.html

Plan:
- Introduce grouped parent/child menu model in controller (vm.menu) with:
  - Registrar: Reports, Enlistment, Cashier (Registration Viewer)
  - Finance: Ledger, Tuition Years
  - Faculty: Profile, My Classes
  - Academics: Programs, Subjects, Curricula, School Terms, Classrooms, Grading Systems
  - Admin: Faculty, Roles, Logs, Cashier Admin
  - Keep standalone: Dashboard, Students, Campuses
- Add helpers:
  - vm.isActivePrefix(path): marks a child active if current path equals or starts with the child path (supports routes with params, e.g., /registrar/registration/:id).
  - vm.canShowGroup(group): show parent if any child is accessible (RoleService.canAccess).
  - vm.toggleGroup(key): toggle parent open/close and persist to StorageService (key: sidebarOpen.<groupKey>).
- Persist and restore group open state across reloads.
- Update template to render parents/children via ng-repeat, with expand/collapse buttons, indentation and active styles.
- Ensure children are hidden when the sidebar is collapsed (vm.isCollapsed).

Tasks:
- [ ] Controller: Add vm.menu data structure and keys (faculty, registrar, finance, academics, admin).
- [ ] Controller: Add vm.groupOpen map + persistence (StorageService.get/set).
- [ ] Controller: Add helpers isActivePrefix, canShowGroup, toggleGroup.
- [ ] Template: Replace flat nav anchors with grouped, nested structure using ng-repeat and toggle buttons.
- [ ] Template: Keep existing CampusSelector and TermSelector sections intact.
- [ ] Styling: Use Tailwind utility classes for indentation and active styles; reuse rotate-180 for caret.
- [ ] Routing/Links: For the Cashier viewer child, use path prefix `/registrar/registration` for active highlight and href `#/registrar/registration/0` for navigation.
- [ ] Verification:
  - [ ] Admin user sees Admin group (Faculty, Roles, Logs, Cashier Admin).
  - [ ] Registrar user sees Registrar (Reports, Enlistment); does not see finance-only items.
  - [ ] Finance user sees Finance (Ledger, Tuition Years) and Cashier (Registration Viewer).
  - [ ] Faculty user sees Faculty (Profile, Classes) and Academics as allowed.
  - [ ] Active highlighting works on param routes (e.g., /registrar/registration/:id).
  - [ ] Group open/close state persists across reloads.
  - [ ] Sidebar collapsed state hides children; icons-only on parent rows.
  - [ ] Mobile behavior unchanged; selectors and main-content offset remain correct.

Notes:
- Role gating relies on RoleService.canAccess with ACCESS_MATRIX in core/roles.constants.js and route.requiredRoles where applicable.
- This change is non-breaking and keeps existing selectors and top-level Dashboard/Students/Campuses behavior.
