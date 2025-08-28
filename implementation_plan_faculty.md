# Implementation Plan

[Overview]
Create full CRUD for tb_mas_faculty exposed via Laravel API and an AngularJS (unity-spa) admin-only UI. All write operations (create, update, delete) and detail endpoints are protected by role:admin middleware. The UI menu/routes are only visible/accessible to users with the admin role.

The backend will add RESTful endpoints for listing, viewing, creating, updating, and deleting faculty. Validation will enforce required fields aligned with existing database NOT NULL constraints observed in logs. Passwords are hashed with bcrypt on create (required) and on update (optional; only if provided). The frontend will add a Faculty feature with list and edit pages, integrated into the existing SPA routing and sidebar with role-based visibility.

This implementation integrates with the existing role middleware (RequireRole) and Faculty model, and follows the patterns used in Programs, School Years, and other features in the repository.

[Types]  
Define request/response schemas for Faculty CRUD and Angular interfaces used in the SPA.

Detailed type definitions:
- Backend (request validation via FormRequest):
  - FacultyStoreRequest:
    - strUsername: string (required, unique: tb_mas_faculty.strUsername, max:50)
    - strPass: string (required, min:8; bcrypt hashed)
    - strFirstname: string (required, max:100)
    - strMiddlename: string (required, max:100)
    - strLastname: string (required, max:100)
    - strEmail: string (required, email, max:150)
    - strMobileNumber: string (required, max:20)
    - strAddress: string (required, max:255)
    - strDepartment: string (required, max:150)
    - strSchool: string (required, max:150)
    - intUserLevel: integer (required, between:0,10) [legacy values observed: 0,2,7]
    - teaching: integer (required, in:0,1)
    - isActive: integer (required, in:0,1)
    - strFacultyNumber: string (sometimes, max:50, unique if provided)
    - campus_id: integer (sometimes, nullable)
  - FacultyUpdateRequest:
    - strUsername: string (sometimes, unique except current id, max:50)
    - strPass: string (sometimes, nullable, min:8; bcrypt if provided)
    - strFirstname: string (sometimes, required_with for business needs; max:100)
    - strMiddlename: string (sometimes, required_without_all of others for NOT NULL; max:100)
    - strLastname: string (sometimes, max:100)
    - strEmail: string (sometimes, email, max:150)
    - strMobileNumber: string (sometimes, max:20)
    - strAddress: string (sometimes, max:255)
    - strDepartment: string (sometimes, max:150)
    - strSchool: string (sometimes, max:150)
    - intUserLevel: integer (sometimes, between:0,10)
    - teaching: integer (sometimes, in:0,1)
    - isActive: integer (sometimes, in:0,1)
    - strFacultyNumber: string (sometimes, max:50, unique:tb_mas_faculty,strFacultyNumber,{id},intID)
    - campus_id: integer (sometimes, nullable)

- Backend (response shape):
  - FacultyResource (JSON):
    - intID, strUsername, strFirstname, strMiddlename, strLastname
    - strEmail, strMobileNumber, strAddress, strDepartment, strSchool
    - intUserLevel, teaching (int), isActive (int), strFacultyNumber, campus_id
    - role_codes: string[] (via accessor)
    - createdAt/updatedAt omitted (legacy table likely no timestamps)

- Frontend (AngularJS) interfaces (JS doc typing in code):
  - FacultyListItem:
    - intID: number
    - strFirstname: string
    - strMiddlename: string
    - strLastname: string
    - strEmail: string
    - teaching: number
    - isActive: number
    - strFacultyNumber?: string
    - fullName (computed in UI)
  - FacultyPayload (for create/update):
    - strUsername, strPass?, strFirstname, strMiddlename, strLastname,
      strEmail, strMobileNumber, strAddress, strDepartment, strSchool,
      intUserLevel, teaching, isActive, strFacultyNumber?, campus_id?

[Files]
Add new Laravel controller, requests, and resource; update API routes. Add new AngularJS feature (service, controller, templates) and wire in routes + sidebar.

Detailed breakdown:
- New files to be created (backend)
  - laravel-api/app/Http/Controllers/Api/V1/FacultyController.php
    - Controller implementing index, show, store, update, destroy.
  - laravel-api/app/Http/Requests/Api/V1/FacultyStoreRequest.php
    - FormRequest for create validation rules.
  - laravel-api/app/Http/Requests/Api/V1/FacultyUpdateRequest.php
    - FormRequest for update validation rules.
  - laravel-api/app/Http/Resources/FacultyResource.php
    - Transforms Faculty model to API response.

- Existing files to be modified (backend)
  - laravel-api/routes/api.php
    - Add:
      - GET /faculty (index) — admin-only
      - GET /faculty/{id} (show) — admin-only
      - POST /faculty (store) — admin-only
      - PUT /faculty/{id} (update) — admin-only
      - DELETE /faculty/{id} (destroy) — admin-only

- New files to be created (frontend)
  - frontend/unity-spa/features/faculty/faculty.service.js
    - Angular service for API calls (list, show, create, update, delete).
  - frontend/unity-spa/features/faculty/faculty.controller.js
    - Angular controller for list & edit routing helpers and delete.
  - frontend/unity-spa/features/faculty/list.html
    - Table with search, paging, create button.
  - frontend/unity-spa/features/faculty/edit.html
    - Form for add/edit with validations and save/cancel.
- Existing files to be modified (frontend)
  - frontend/unity-spa/core/routes.js
    - Add states:
      - admin.faculty (list)
      - admin.faculty.edit (create/update)
    - Use route metadata to restrict to admin role using existing role service/guards.
  - frontend/unity-spa/shared/components/sidebar/sidebar.html
    - Add navigation link to Faculty for admin users only (ng-if with hasRole('admin')).
  - Optionally: frontend/unity-spa/core/role.service.js (no code changes anticipated; reused for guarding UI).

- Files to be deleted or moved
  - None.

- Configuration file updates
  - None for composer/npm. Ensure base API URL usage matches existing services.

[Functions]
Define new controller actions, requests validation, Angular services and controller methods.

Detailed breakdown:
- New functions (backend)
  - FacultyController@index(Request): JsonResponse
    - Query parameters: q (search by name), teaching (0|1), isActive (0|1), page?, per_page?
    - Returns paginated list (or full list if pagination omitted) of faculty using FacultyResource.
  - FacultyController@show(int $id): JsonResponse
    - Returns a single faculty by id (404 if not found).
  - FacultyController@store(FacultyStoreRequest): JsonResponse
    - Creates faculty; bcrypt-hash strPass; returns created entity.
  - FacultyController@update(FacultyUpdateRequest, int $id): JsonResponse
    - Updates faculty; if strPass provided, bcrypt-hash and update; otherwise unchanged.
  - FacultyController@destroy(int $id): JsonResponse
    - Deletes faculty (soft delete not present; perform hard delete). If foreign key dependencies exist, return 409 with message.

- Modified functions
  - routes/api.php: add Route::get/post/put/delete handlers under /v1 with middleware('role:admin').

- Removed functions
  - None.

[Classes]
Introduce controller, resource, and request classes. Model Faculty remains unchanged.

Detailed breakdown:
- New classes
  - App\Http\Controllers\Api\V1\FacultyController
    - Methods: index, show, store, update, destroy
    - Uses RequireRole middleware via route declarations.
  - App\Http\Requests\Api\V1\FacultyStoreRequest
    - authorize(): true
    - rules(): array (see [Types])
  - App\Http\Requests\Api\V1\FacultyUpdateRequest
    - authorize(): true
    - rules(): array (see [Types])
  - App\Http\Resources\FacultyResource
    - toArray(): array (map legacy fields to API keys; include role_codes via accessor.)

- Modified classes
  - None; App\Models\Faculty already provides roles(), hasRole(), hasAnyRole(), and role_codes accessor.

- Removed classes
  - None.

[Dependencies]
No new dependencies are required.

The plan uses:
- Existing middleware App\Http\Middleware\RequireRole with header X-Faculty-ID or faculty_id fallback for dev.
- Existing App\Models\Faculty and Role relations.
- Existing AngularJS app structure, role.service.js, and toast.service.js for notifications.

[Testing]
End-to-end validation for admin-only access and CRUD behaviors.

Test file requirements and strategies:
- Backend Feature Tests (PHPUnit)
  - laravel-api/tests/Feature/Api/V1/FacultyControllerTest.php
    - test_index_requires_admin_role (401/403 without header or non-admin)
    - test_create_requires_admin_role
    - test_create_validates_required_fields (e.g., missing strMiddlename fails)
    - test_create_hashes_password (verify column differs from plain)
    - test_update_without_password_does_not_change_password
    - test_delete_requires_admin_role
  - Optionally: seed admin faculty (scripts/grant_faculty_role.php) and use X-Faculty-ID header.

- Manual/API tests (cURL)
  - GET /api/v1/faculty with X-Faculty-ID of admin
  - POST /api/v1/faculty (all required fields)
  - PUT /api/v1/faculty/{id} (selected fields, omit strPass to keep unchanged)
  - DELETE /api/v1/faculty/{id}

- Frontend (manual)
  - Sidebar shows Faculty menu only for admin.
  - List loads with search; create/update/delete flows; client-side validations parallel server ones.

[Implementation Order]
Implement backend first, then frontend, then tests, to minimize integration issues.

1) Backend
   1.1 Create FacultyStoreRequest and FacultyUpdateRequest with rules enforcing all required fields (including NOT NULL legacy fields).
   1.2 Create FacultyResource to standardize API responses and expose role_codes.
   1.3 Create FacultyController with CRUD, hashing strPass via bcrypt on create and on update when provided.
   1.4 Update routes/api.php with /faculty endpoints and middleware('role:admin').
   1.5 Smoke test endpoints via cURL using X-Faculty-ID for an admin user; validate role enforcement.

2) Frontend
   2.1 Create features/faculty/faculty.service.js with methods: list, get, create, update, remove (set X-Faculty-ID header consistent with other admin features).
   2.2 Create features/faculty/faculty.controller.js handling list and navigation to edit/create.
   2.3 Create features/faculty/list.html and edit.html following existing UI patterns (programs, school-years).
   2.4 Wire routes in core/routes.js (admin.faculty, admin.faculty.edit) and guard visibility (role: admin).
   2.5 Add sidebar link in shared/components/sidebar/sidebar.html guarded by admin role.
   2.6 Manual test SPA flows.

3) Tests and QA
   3.1 Add PHPUnit tests described above (optional if time-constrained; provide cURL scripts).
   3.2 Verify Generic API unaffected; role middleware enforced consistently.
   3.3 Document usage and field requirements in README snippet or comments in service/controller.

task_progress Items:
- [ ] Step 1: Implement Laravel FormRequests (FacultyStoreRequest, FacultyUpdateRequest) with full validation rules
- [ ] Step 2: Implement FacultyResource for consistent API output
- [ ] Step 3: Implement FacultyController CRUD with bcrypt handling on strPass and 404/409 responses
- [ ] Step 4: Add Laravel routes for /api/v1/faculty endpoints with middleware('role:admin')
- [ ] Step 5: Create AngularJS faculty.service.js (list/get/create/update/delete)
- [ ] Step 6: Create AngularJS faculty.controller.js to manage list and edit flows
- [ ] Step 7: Create AngularJS list.html and edit.html views with form validations
- [ ] Step 8: Update core/routes.js to add admin.faculty and admin.faculty.edit states, guarded by role service
- [ ] Step 9: Update sidebar to include Faculty menu visible only to admin
- [ ] Step 10: Perform cURL and UI smoke tests to verify role enforcement and CRUD functionality
