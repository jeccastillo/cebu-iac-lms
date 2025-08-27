# Implementation Plan

[Overview]
Add a new faculty_admin role and deliver a complete Grading Systems management feature that spans Laravel API (CRUD for tb_mas_grading and tb_mas_grading_item) and AngularJS (Unity SPA) UI, gated so only admin and faculty_admin can create, edit, and delete grading systems and items, while reads are publicly available.

This feature centralizes grading configuration so Registrar/Admin and Faculty Admin can maintain institutional grading scales. The Laravel API exposes well-validated endpoints, logs all actions, and enforces usage constraints (e.g., prevent deleting systems in use). The AngularJS UI provides listing, creation, editing, and item management views with role-based visibility in the sidebar, using the same access rules. The work integrates with legacy CodeIgniter components non-invasively: existing CI views/routes remain, but the new SPA pages become the primary admin interface for grading configuration.

[Types]
Eloquent model-backed types represent the underlying tb_mas_grading and tb_mas_grading_item records.

- GradingSystem (maps to tb_mas_grading)
  - id: int, primary key, auto-increment
  - name: string, required, unique (case-insensitive if supported)
  - items(): hasMany GradingItem by grading_id

- GradingItem (maps to tb_mas_grading_item)
  - id: int, primary key, auto-increment
  - grading_id: int, required, foreign key to tb_mas_grading.id, on delete cascade (enforced in app-level transactions)
  - value: string, required, unique per grading_id (app-level uniqueness)
  - remarks: string, required

Validation rules:
- GradingSystemStoreRequest: { name: required|string|min:1|max:255|unique:tb_mas_grading,name }
- GradingSystemUpdateRequest: { name: required|string|min:1|max:255|unique:tb_mas_grading,name,{id} }
- GradingItemStoreRequest: { value: required|string|min:1|max:20, remarks: required|string|min:1|max:255 }
- GradingItemsBulkStoreRequest: { items: required|array|min:1, items.*.value: string|required, items.*.remarks: string|required }; reject duplicate values within the same payload and skip already-existing values to be idempotent.

[Files]
Implement new Laravel API files, update Auth gate, add AngularJS SPA routes/components, and expose UI in sidebar.

- New (Laravel API)
  - laravel-api/app/Models/GradingSystem.php: Eloquent model: protected $table='tb_mas_grading'; fillable ['name']; relation items()
  - laravel-api/app/Models/GradingItem.php: Eloquent model: protected $table='tb_mas_grading_item'; fillable ['grading_id','value','remarks']; relation gradingSystem()
  - laravel-api/app/Http/Controllers/Api/V1/GradingSystemController.php: REST controller with CRUD and items endpoints; uses SystemLogService and DB transactions
  - laravel-api/app/Http/Requests/Api/V1/GradingSystemStoreRequest.php: validation for POST create
  - laravel-api/app/Http/Requests/Api/V1/GradingSystemUpdateRequest.php: validation for PUT update
  - laravel-api/app/Http/Requests/Api/V1/GradingItemStoreRequest.php: validation for single item add
  - laravel-api/app/Http/Requests/Api/V1/GradingItemsBulkStoreRequest.php: validation for bulk add
- Modified (Laravel API)
  - laravel-api/app/Providers/AuthServiceProvider.php: define Gate::define('grading.manage', ...) to allow admin or faculty_admin
  - laravel-api/routes/api.php: register routes under /api/v1:
    - GET /grading-systems
    - GET /grading-systems/{id}
    - POST /grading-systems (authorize grading.manage)
    - PUT /grading-systems/{id} (authorize grading.manage)
    - DELETE /grading-systems/{id} (authorize grading.manage)
    - POST /grading-systems/{id}/items (authorize grading.manage)
    - POST /grading-systems/{id}/items/bulk (authorize grading.manage)
    - DELETE /grading-systems/items/{itemId} (authorize grading.manage)
- New (AngularJS Unity SPA)
  - frontend/unity-spa/features/grading/grading.service.js: wraps API endpoints; uses APP_CONFIG.API_BASE
  - frontend/unity-spa/features/grading/grading.controller.js: GradingListController and GradingEditController
  - frontend/unity-spa/features/grading/list.html: list view with create/edit/delete actions and counts
  - frontend/unity-spa/features/grading/edit.html: create/edit form and item management (add, add bulk, remove)
- Modified (AngularJS Unity SPA)
  - frontend/unity-spa/core/routes.js: add routes
    - /grading-systems -> GradingListController (requiredRoles: ['faculty_admin','admin'])
    - /grading-systems/new -> GradingEditController (requiredRoles: ['faculty_admin','admin'])
    - /grading-systems/:id/edit -> GradingEditController (requiredRoles: ['faculty_admin','admin'])
  - frontend/unity-spa/shared/components/sidebar/sidebar.html: add "Grading Systems" menu visible to faculty_admin and admin
  - frontend/unity-spa/index.html: ensure new grading feature scripts are included in correct order so pages render (scripts loaded after core, before run)
  - frontend/unity-spa/core/roles.constants.js and frontend/unity-spa/core/run.js: used to enforce requiredRoles metadata at route-level (no code changes required for this feature, but noted as dependencies)

[Functions]
API controller functions implement CRUD and item operations; Angular service/controller functions orchestrate UI flow.

- New (Laravel API)
  - GradingSystemController@index(Request): JsonResponse
  - GradingSystemController@show(int $id): JsonResponse
  - GradingSystemController@store(GradingSystemStoreRequest): JsonResponse
  - GradingSystemController@update(GradingSystemUpdateRequest, int $id): JsonResponse
  - GradingSystemController@destroy(Request, int $id): JsonResponse
  - GradingSystemController@addItemsBulk(GradingItemsBulkStoreRequest, int $id): JsonResponse
  - GradingSystemController@addItem(GradingItemStoreRequest, int $id): JsonResponse
  - GradingSystemController@deleteItem(Request, int $itemId): JsonResponse
- Modified (Laravel API)
  - AuthServiceProvider@boot(): Gate::define('grading.manage', fn($user) => $user->isAdmin() || $user->hasRole('faculty_admin'))
  - routes/api.php: register routes and middleware for authorization
- New (AngularJS)
  - grading.service.js:
    - list(): GET /grading-systems
    - get(id): GET /grading-systems/{id}
    - create(payload): POST /grading-systems
    - update(id, payload): PUT /grading-systems/{id}
    - remove(id): DELETE /grading-systems/{id}
    - addItem(id, payload): POST /grading-systems/{id}/items
    - addItemsBulk(id, payload): POST /grading-systems/{id}/items/bulk
    - deleteItem(itemId): DELETE /grading-systems/items/{itemId}
  - grading.controller.js:
    - GradingListController: loads list, navigates to create/edit, deletes system with confirm, guards roles
    - GradingEditController: handles create/update of system, loads items, add/remove single and bulk operations

[Classes]
Eloquent models express relationship mapping; request classes enforce validation; Angular controllers define UI logic.

- New (Laravel API)
  - App\Models\GradingSystem
    - $table='tb_mas_grading'
    - $fillable=['name']
    - items(): hasMany(App\Models\GradingItem, 'grading_id')
  - App\Models\GradingItem
    - $table='tb_mas_grading_item'
    - $fillable=['grading_id','value','remarks']
    - gradingSystem(): belongsTo(App\Models\GradingSystem, 'grading_id')
  - App\Http\Requests\Api\V1\GradingSystemStoreRequest
  - App\Http\Requests\Api\V1\GradingSystemUpdateRequest
  - App\Http\Requests\Api\V1\GradingItemStoreRequest
  - App\Http\Requests\Api\V1\GradingItemsBulkStoreRequest
- Modified (Laravel API)
  - App\Providers\AuthServiceProvider: adds Gate grading.manage
- New (AngularJS)
  - GradingListController
  - GradingEditController

[Dependencies]
No third-party packages. Depends on:
- Laravel: Eloquent, Request validation, Gate authorization, DB transactions, SystemLogService
- AngularJS: existing unityApp module, APP_CONFIG.API_BASE, StorageService, RoleService, ngRoute, and templates include pipeline in index.html
- Backend DB tables: tb_mas_grading and tb_mas_grading_item (existing RDBMS schema)

[Testing]
Critical-path API tests via curl/Postman:
- GET /grading-systems ⇒ 200, JSON array with items_count
- GET /grading-systems/{id} ⇒ 200, contains system and ordered items
- POST /grading-systems (authorized) ⇒ 201, creates system
- PUT /grading-systems/{id} (authorized) ⇒ 200, updates name
- DELETE /grading-systems/{id} (authorized) ⇒ 200; 409 if in use by tb_mas_subjects.grading_system_id or grading_system_id_midterm
- POST /grading-systems/{id}/items (authorized) ⇒ 201; 409 on duplicate value in same system
- POST /grading-systems/{id}/items/bulk (authorized) ⇒ 201; skips existing values; 422 if duplicate values in the same payload
- DELETE /grading-systems/items/{itemId} (authorized) ⇒ 200
- Verify SystemLogService entries created for create/update/delete operations
UI smoke tests:
- Sidebar shows “Grading Systems” for admin and faculty_admin
- List page loads; create opens and persists; edit loads items; add/remove single & bulk works; route guards prevent access for other roles

Note: If API responses show malformed JSON in some environments (e.g., commas missing in console), capture raw responses to a file and verify content; investigate output buffering or encoding if needed; controller returns proper JSON via response()->json.

[Implementation Order]
Complete backend first, then frontend, then tests; protect destructive actions with guards and logging.

1) Authorization and Roles
   - Add/confirm faculty_admin role mapping in user context and Gate grading.manage in AuthServiceProvider.
2) Data Models
   - Implement GradingSystem and GradingItem Eloquent models and relations.
3) Validation
   - Implement GradingSystemStore/Update and GradingItemStore/ItemsBulk requests with rules.
4) Controller
   - Implement CRUD + item add/bulk-delete endpoints with transactions, constraints, logging.
5) Routes
   - Register API v1 routes; make GETs public; protect mutations with gate.
6) Angular Service
   - Implement grading.service.js calling API endpoints using APP_CONFIG.API_BASE.
7) Angular Controllers/Views
   - Implement GradingListController and GradingEditController; add list.html and edit.html.
8) Routing/Sidebar
   - Wire routes in core/routes.js with requiredRoles ['faculty_admin','admin']; add menu to sidebar.html.
9) Index Includes
   - Ensure new grading feature scripts are included in index.html in the correct order.
10) API Tests
   - Execute critical-path tests (GET/POST/PUT/DELETE, including conflict cases).
11) UI Smoke Tests
   - Verify role gating, CRUD from UI, and no console errors; fix CORS only for local file:// testing by running via http://localhost/... rather than file://.
12) Logging Verification
   - Confirm SystemLogService captured create/update/delete entries and metadata.
