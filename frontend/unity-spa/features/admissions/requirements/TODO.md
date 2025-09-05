# Application Requirements CRUD (AngularJS) - TODO

Scope: Build the Admissions/Admin UI for managing Requirement definitions backed by Laravel endpoints:
- GET/POST/PUT/DELETE /api/v1/requirements (roles: admissions, admin)

Data fields (RequirementResource):
- id (int)
- name (string, required)
- type (enum: college | shs | grad)
- is_foreign (bool)
- is_initial_requirements (bool)
- created_at, updated_at (string|null)

Routes to add:
- /admissions/requirements              (list view)
- /admissions/requirements/new          (create view)
- /admissions/requirements/:id/edit     (edit view)
All with requiredRoles: ["admissions", "admin"]

Tasks
- [ ] Add Angular routes in frontend/unity-spa/core/routes.js
      - Insert 3 routes after /admissions/success
- [ ] Create RequirementsService
      - Path: features/admissions/requirements/requirements.service.js
      - Methods: list(filters), show(id), create(payload), update(id, payload), remove(id)
      - Endpoint base: `${APP_CONFIG.API_BASE}/requirements`
      - Headers: include X-Faculty-ID if available (via StorageService loginState), same as PaymentDescriptionsService
      - Filters support: search, type, is_foreign, sort, order, page, per_page
- [ ] Create controllers
      - Path: features/admissions/requirements/requirements.controller.js
      - RequirementsController (list):
        - State: filters {search, type, is_foreign, sort=name, order=asc, page=1, per_page=10}
        - Methods: load(), search(), clearFilters(), changeSort(field), changePage(delta), changePerPage(), add(), edit(row), remove(row)
      - RequirementEditController (create/edit):
        - Fields: name (required), type (college|shs|grad), is_foreign (bool), is_initial_requirements (bool)
        - Methods: load(), save(), cancel()
        - Validation: require non-empty name; type must be valid
- [ ] Create list view
      - Path: features/admissions/requirements/list.html
      - Table columns: Name, Type, Foreign?, Initial?, Actions
      - Controls: search box, type filter (All/College/SHS/Grad), foreign filter (All/Yes/No), sortable headers, pagination, Add button
      - Error and loading indicators
- [ ] Create edit view
      - Path: features/admissions/requirements/edit.html
      - Form inputs:
        - Name (text, required)
        - Type (select: college/shs/grad)
        - Is Foreign (checkbox)
        - Is Initial Requirements (checkbox)
      - Buttons: Save, Cancel, with basic client-side validation and SweetAlert feedback when available

Manual Verification
- [ ] Navigate to /#/admissions/requirements; ensure roles admissions/admin can access; others blocked by requiredRoles
- [ ] Confirm listing works with server-side search, type/is_foreign filters, sorting, pagination
- [ ] Create flow: /#/admissions/requirements/new; required fields enforced; success returns to list with success toast
- [ ] Edit flow: /#/admissions/requirements/:id/edit; loads existing record; updates persist; success toast
- [ ] Delete flow from list; confirm dialog; after delete refresh list
- [ ] API error handling: shows friendly message from response.message or fallback
- [ ] Header X-Faculty-ID present when available in loginState

Out of Scope (Phase 2)
- Per-application checklist UI using ApplicationRequirement model; pending explicit endpoints and flows.

Notes
- Follow the same code and UX patterns used in Finance Payment Descriptions for consistency.
- Keep new files under features/admissions/requirements/
