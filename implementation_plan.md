# Implementation Plan

[Overview]
Create full CRUD for tb_mas_sy (School Years/Terms) using the existing Laravel API and integrate a new management UI into the existing AngularJS Unity SPA styled with Tailwind. The API will expose REST endpoints for read and write operations with role-based access, and the SPA will provide list, create, update, and delete screens for registrar/admin while allowing read to authenticated users.

This implementation standardizes term management used across enrollment, grading windows, reports, and CI parity endpoints. It introduces a dedicated REST resource (/api/v1/school-years) alongside the existing generic list endpoints (/api/v1/generic/terms, /api/v1/generic/active-term). The UI will be added into the Unity SPA under a new feature module and will use Tailwind (via CDN) for styling to minimize build pipeline impact.

[Types]  
Define the TB_MAS_SY entity and related structures used by the API and SPA.

Data model: tb_mas_sy
- intID: int (PK, auto-increment)
- enumSem: string (e.g., "1st", "2nd", "Summer"); non-empty
- strYearStart: string (YYYY)
- strYearEnd: string (YYYY)
- term_label: string (e.g., "Semester", "Trimester", "Quarter", "Term"); optional, defaults to "Semester"
- term_student_type: string (e.g., "college", "shs", "next"); optional
- campus_id: int|null (FK to tb_mas_campuses.id, nullable, indexed)
- midterm_start: datetime|null (YYYY-MM-DD HH:MM:SS)
- midterm_end: datetime|null (YYYY-MM-DD HH:MM:SS)
- final_start: datetime|null (YYYY-MM-DD HH:MM:SS)
- final_end: datetime|null (YYYY-MM-DD HH:MM:SS)
- end_of_submission: datetime|null (must not be '0000-00-00 00:00:00')
- intProcessing: int|null (0/1; used historically to indicate current/processing term)
- enumStatus: string|null (e.g., "active", "inactive")
- enumFinalized: string|null (e.g., "yes", "no")
- created_at/updated_at: not used (timestamps disabled in model)

Laravel validation (Store/Update):
- enumSem: required|string|max:16
- strYearStart: required|digits:4
- strYearEnd: required|digits:4|gte:strYearStart
- term_label: sometimes|string|max:32
- term_student_type: sometimes|string|max:32
- campus_id: sometimes|nullable|integer
- midterm_start/midterm_end/final_start/final_end/end_of_submission: sometimes|nullable|date
- intProcessing: sometimes|integer|in:0,1
- enumStatus: sometimes|string|max:16
- enumFinalized: sometimes|string|max:8

Response DTO (API):
- For collections: { success: true, data: SchoolYear[] }
- For single: { success: true, data: SchoolYear }
- On mutations, additionally include message when appropriate

[Files]
Introduce new Laravel controller and requests, add routes, optionally adjust model casts, and create SPA feature files with Tailwind integration.

Laravel API
- New files:
  - laravel-api/app/Http/Controllers/Api/V1/SchoolYearController.php
    - Purpose: RESTful CRUD for tb_mas_sy with SystemLogService auditing.
  - laravel-api/app/Http/Requests/Api/V1/SchoolYearStoreRequest.php
    - Purpose: Validate create payload.
  - laravel-api/app/Http/Requests/Api/V1/SchoolYearUpdateRequest.php
    - Purpose: Validate update payload (all fields optional).
  - laravel-api/app/Http/Resources/SchoolYearResource.php (optional)
    - Purpose: Normalize output if needed; can be skipped to keep parity style with existing controllers.

- Existing files to modify:
  - laravel-api/routes/api.php
    - Add REST routes under /api/v1/school-years:
      - GET /school-years (open read)
      - GET /school-years/{id} (open read)
      - POST /school-years (role: registrar,admin)
      - PUT /school-years/{id} (role: registrar,admin)
      - DELETE /school-years/{id} (role: registrar,admin)
  - laravel-api/app/Models/SchoolYear.php
    - Add attribute casts for datetime fields for consistency:
      - protected $casts = [ 'midterm_start' => 'datetime', 'midterm_end' => 'datetime', 'final_start' => 'datetime', 'final_end' => 'datetime', 'end_of_submission' => 'datetime' ];
    - Keep timestamps = false.

- Database/Migrations:
  - Verify presence of expected columns. If missing in the environment, add new migration:
    - laravel-api/database/migrations/2025_08_28_000200_update_tb_mas_sy_columns.php
      - Adds any absent columns: term_label, term_student_type, midterm_start, midterm_end, final_start, final_end, end_of_submission, enumStatus, enumFinalized, intProcessing (nullable).
      - Adds indexes where appropriate (campus_id).
      - Note: campus_id handled by existing migration 2025_08_25_000009_add_campus_id_to_tb_mas_sy.php (data dependency: end_of_submission must not be '0000-00-00 00:00:00').
  - Data fix (precondition): laravel-api/scripts/fix_invalid_sy_end_of_submission.php exists; ensure it is run prior to FK addition to avoid 'Invalid datetime value'.

Unity SPA (AngularJS)
- New directory:
  - frontend/unity-spa/features/school-years/
    - school-years.service.js: Wraps API calls.
    - school-years.controller.js: List view controller.
    - school-year-edit.controller.js: Create/Edit controller.
    - list.html: Tailwind-styled table with filters and actions.
    - edit.html: Tailwind-styled form (create/update).

- Existing files to modify:
  - frontend/unity-spa/index.html
    - Add Tailwind via CDN: <script src="https://cdn.tailwindcss.com"></script>
    - Add a minimal tailwind.config inline (safelisting utility classes used if needed).
  - frontend/unity-spa/core/routes.js
    - Register routes:
      - /school-years (list)
      - /school-years/new (create)
      - /school-years/:id/edit (edit)
  - frontend/unity-spa/shared/components/sidebar/sidebar.html
    - Add "School Years" nav link (visible for registrar/admin).
  - frontend/unity-spa/core/role.service.js (no code change; used to hide/show UI controls based on roles)

[Functions]
Add new controller methods and SPA service methods; no removals.

Laravel: new functions (SchoolYearController)
- index(Request $request): JsonResponse
  - Query params: campus_id?, term_student_type?, search?, limit?, page?
  - Returns list ordered by strYearStart desc, enumSem asc.
- show(int $id): JsonResponse
  - Returns a single record or 404.
- store(SchoolYearStoreRequest $request): JsonResponse
  - Create record; defaults: term_label=Semester if missing.
  - SystemLogService::log('create', 'SchoolYear', id, null, new, $request)
- update(SchoolYearUpdateRequest $request, int $id): JsonResponse
  - Update record (partial). Log 'update' with old/new.
- destroy(int $id): JsonResponse
  - Soft strategy: if enumStatus exists, set to 'inactive'; else perform delete (configurable). Log 'update' or 'delete'.

Model changes
- App\Models\SchoolYear::$casts: add datetime casts.

Unity SPA: new functions
- school-years.service.js
  - list(params): Promise<response> GET /api/v1/school-years
  - get(id): Promise<response> GET /api/v1/school-years/{id}
  - create(payload): Promise<response> POST /api/v1/school-years
  - update(id, payload): Promise<response> PUT /api/v1/school-years/{id}
  - remove(id): Promise<response> DELETE /api/v1/school-years/{id}

- school-years.controller.js
  - init(): load terms, filters, handle role gating of actions
  - onFilterChange(): refetch
  - onCreate(), onEdit(id), onDelete(id): UI actions (with confirmations)

- school-year-edit.controller.js
  - init(id?): load existing when editing
  - submit(): create/update via service with toasts and redirects
  - form normalization for date fields (YYYY-MM-DD HH:mm:ss)

[Classes]
Introduce one new Laravel controller class; adjust one model class.

- New classes:
  - App\Http\Controllers\Api\V1\SchoolYearController
    - Methods: index, show, store, update, destroy
    - Uses: Illuminate\Support\Facades\DB (optional), App\Models\SchoolYear, SystemLogService, Store/Update requests
- Modified classes:
  - App\Models\SchoolYear
    - Add $casts for datetime fields as noted above
- Removed classes:
  - None

[Dependencies]
No new Composer dependencies. Tailwind integrated via CDN in SPA.

- Laravel:
  - Use existing SystemLogService for audit logs.
  - Continue route middleware 'role:registrar,admin' for mutations.

- Frontend:
  - Tailwind via CDN: https://cdn.tailwindcss.com with minimal runtime config.
  - No change to build tooling.

[Testing]
Adopt a layered approach for API and UI behaviors.

API
- Unit/Feature tests (optional, if test suite is in use):
  - tests/Feature/SchoolYearControllerTest.php
    - index_returns_list_with_filters
    - show_returns_404_for_missing
    - store_creates_and_logs
    - update_patches_and_logs
    - destroy_soft_disables_and_logs (or deletes)
- Manual test checklist:
  - Ensure GET /api/v1/school-years matches GenericApiController ordering and fields superset.
  - Validate date field acceptance and persistence.
  - Verify SystemLogService produces proper entries.
  - Verify role middleware blocks mutations for non-registrar/admin.

SPA
- Manual test checklist:
  - Sidebar shows "School Years" only for registrar/admin.
  - List loads and filters (campus, student type, search).
  - Create/edit forms validate required fields (enumSem, strYearStart, strYearEnd).
  - Midterm/Final date windows saved and reflected.
  - Delete flow confirmation and outcome messaging.

[Implementation Order]
Implement backend first, then frontend, then data fixes, then integration tests.

1) Backend model and requests
- Add $casts to SchoolYear model (datetime fields).
- Create SchoolYearStoreRequest and SchoolYearUpdateRequest with rules.

2) Backend controller and routes
- Implement SchoolYearController (index, show, store, update, destroy).
- Wire routes in routes/api.php under prefix v1 (/school-years).
- Add role middleware for POST/PUT/DELETE; keep GET open (parity with other reads).

3) Database/migrations
- Verify columns; create 2025_08_28_000200_update_tb_mas_sy_columns.php to add any missing.
- Pre-run data fix script (fix_invalid_sy_end_of_submission.php) to replace '0000-00-00 00:00:00' with NULL or valid timestamps.
- Run migration for campus_id if pending, then new columns migration.

4) SPA Tailwind integration
- Update frontend/unity-spa/index.html to include Tailwind CDN.
- Add minimal tailwind.config inline (safelist frequently used utilities if necessary).

5) SPA feature files
- Create frontend/unity-spa/features/school-years/service/controller/templates.
- Update core/routes.js to register new routes.
- Update shared/components/sidebar/sidebar.html to add menu link.

6) Role/UX gating
- Use role.service.js to hide create/edit/delete buttons for non-registrar/admin.
- Keep read (list/show) accessible as per app norms.

7) Validation and logging verification
- Exercise CRUD from SPA; inspect laravel logs and tb_mas_system_log for entries.

8) Documentation
- Update or append to README/TODO as needed regarding term management and grading windows alignment.
