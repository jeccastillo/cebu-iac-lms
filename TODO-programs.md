# TODO - Programs CRUD (Laravel API + AngularJS UI)

- [x] Step 1: Harden Program model ($fillable and $casts updates)
  - Add fields: school, campus_id
  - Add casts: enumEnabled(int), default_curriculum(int), campus_id(int)
- [ ] Step 2: Create validation requests
  - App\Http\Requests\ProgramStoreRequest (create rules)
  - App\Http\Requests\ProgramUpdateRequest (update rules + unique code check)
- [ ] Step 3: Extend ProgramController
  - Add: show, store, update, destroy (soft-disable)
  - Enhance index: filters (type?, school?, search?) keeping enabledOnly default
  - Wire SystemLogService::log for store/update/destroy
- [ ] Step 4: Routes
  - GET /api/v1/programs/{id}
  - POST /api/v1/programs (role:registrar,admin)
  - PUT /api/v1/programs/{id} (role:registrar,admin)
  - DELETE /api/v1/programs/{id} (role:registrar,admin)
- [x] Step 5: AngularJS service and routes
  - features/programs/programs.service.js
  - routes registered in core/routes.js
- [x] Step 6: AngularJS views and controllers
  - features/programs/list.html + ProgramsListController
  - features/programs/edit.html + ProgramsEditController
- [ ] Step 7: System logging verification
  - Exercise create/update/destroy; verify entries with old/new snapshots
- [ ] Step 8: Tests
  - tests/Feature/ProgramApiTest.php (index, filters, show, store, update, destroy)
- [ ] Step 9: Documentation
  - Add endpoints and UI path notes to README/TODO

Notes:
- Delete behavior: soft-disable via enumEnabled=0; optional force delete (admin-only) can be added later.
- Fields in scope: strProgramCode, strProgramDescription, strMajor, type, school, short_name, default_curriculum, enumEnabled, campus_id.
- Access control: create/update/delete restricted to role:registrar,admin; GET endpoints public.
