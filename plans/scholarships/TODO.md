# Scholarships CRUD + SPA — Implementation TODO

This TODO tracks the implementation steps approved in plans/scholarships/implementation_plan.md and will be updated as steps are completed.

task_progress Items:
- [x] Step 1: Silent investigation of scholarship-related backend and frontend modules
- [x] Step 2: Clarifications on columns, soft-delete, uniqueness, and SPA scope
- [x] Step 3: Author implementation plan at plans/scholarships/implementation_plan.md
- [x] Step 4: Implement backend CRUD (Requests, Service, Controller, Routes)
- [ ] Step 5: Optional migration for unique indexes (code, name)
- [x] Step 6: Implement AngularJS SPA (service, controller, template)
- [x] Step 7: Wire up AngularJS route, sidebar link, and index.html scripts
- [ ] Step 8: Execute agreed testing scope (API and SPA), fix issues, and finalize

## Detailed Tasks

1) Backend — Form Requests
- [x] Create laravel-api/app/Http/Requests/Api/V1/ScholarshipStoreRequest.php
- [x] Create laravel-api/app/Http/Requests/Api/V1/ScholarshipUpdateRequest.php

2) Backend — Service
- [x] Extend ScholarshipService with:
  - get(int $id): array|null
  - create(array $data): array
  - update(int $id, array $data): array
  - softDelete(int $id): array
  - restore(int $id): array

3) Backend — Controller
- [x] Add methods to ScholarshipController:
  - show(int $id): JsonResponse
  - store(ScholarshipStoreRequest $request): JsonResponse
  - update(ScholarshipUpdateRequest $request, int $id): JsonResponse
  - destroy(int $id): JsonResponse (soft-delete → status=inactive)
  - restore(int $id): JsonResponse (status=active)

4) Backend — Routes
- [x] Register new routes in laravel-api/routes/api.php under /api/v1:
  - GET /scholarships/{id}
  - POST /scholarships (role: scholarship,admin)
  - PUT /scholarships/{id} (role: scholarship,admin)
  - DELETE /scholarships/{id} (role: scholarship,admin)
  - POST /scholarships/{id}/restore (role: scholarship,admin)

5) Backend — Optional Migration
- [ ] Create migration to add unique indexes for code and name (skip if already present)

6) Frontend — AngularJS
- [x] Create features/scholarship/scholarships/scholarships.service.js
- [x] Create features/scholarship/scholarships/scholarships.controller.js
- [x] Create features/scholarship/scholarships/list.html

7) Frontend — Wiring
- [x] Update core/routes.js to add route "/scholarship/scholarships"
- [x] Update shared/components/sidebar/sidebar.html to add "Scholarship Catalog" link
- [x] Update index.html to include scripts for new service and controller

8) Testing
- [ ] API smoke and edge cases: create, duplicate, update, soft-delete (idempotent), restore, show, list with filters
- [ ] SPA manual: listing, create, edit, soft-delete, restore, filters, role-gated routing
- [ ] Regression: /scholarships/assigned and /scholarships/enrolled still work
