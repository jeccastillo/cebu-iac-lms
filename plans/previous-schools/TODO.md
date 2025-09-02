# Previous Schools CRUD - Implementation TODO

Scope:
- Laravel API: Full CRUD for previous_schools with unique(name, city), grade as numeric, timestamps, and system logging on create/update/delete.
- AngularJS (unity-spa): Admin UI under Admissions to manage Previous Schools (list, create, edit, delete).
- Public read-only endpoint for applicant forms: GET /api/v1/admissions/previous-schools

Conventions to follow:
- Responses wrapped as { success, data, meta? }
- Filtering, sorting, optional pagination
- Authorization via route middleware role:admissions,admin
- System logs using App\Services\SystemLogService::log

Backend (Laravel) Steps:
1) Migration
   - File: laravel-api/database/migrations/2025_09_02_000500_create_previous_schools_table.php
   - Table: previous_schools
     - intID (increments, PK)
     - name (string 255, required)
     - city (string 128, nullable)
     - province (string 128, nullable)
     - country (string 128, nullable)
     - grade (integer, nullable)
     - timestamps()
     - unique index on (name, city)

2) Eloquent Model
   - File: laravel-api/app/Models/PreviousSchool.php
   - $table = 'previous_schools'
   - $primaryKey = 'intID'
   - $fillable = ['name', 'city', 'province', 'country', 'grade']

3) Form Requests
   - File: laravel-api/app/Http/Requests/Api/V1/PreviousSchoolStoreRequest.php
     - Rules:
       - name: required|string|max:255
       - city: nullable|string|max:128
       - province: nullable|string|max:128
       - country: nullable|string|max:128
       - grade: nullable|integer
       - Enforce unique name+city: unique:previous_schools,name,NULL,intID,city,{{city}}
   - File: laravel-api/app/Http/Requests/Api/V1/PreviousSchoolUpdateRequest.php
     - Rules: same as store, but sometimes|... and unique rule ignoring current id

4) Resource
   - File: laravel-api/app/Http/Resources/PreviousSchoolResource.php
   - Fields: id, name, city, province, country, grade, created_at, updated_at

5) Controller
   - File: laravel-api/app/Http/Controllers/Api/V1/PreviousSchoolController.php
   - Methods:
     - index(Request): supports filters: search (name/city/province/country), sort (default name), order, page/per_page
     - show(int $id)
     - store(PreviousSchoolStoreRequest $request) + SystemLogService::log('create', 'PreviousSchool', ...)
     - update(PreviousSchoolUpdateRequest $request, int $id) + SystemLogService::log('update', ...)
     - destroy(int $id) + SystemLogService::log('delete', ...)

6) Routes
   - File: laravel-api/routes/api.php
   - Within prefix v1 group:
     - Public read-only for applicant forms:
       - GET /admissions/previous-schools -> PreviousSchoolController@index (no role middleware)
     - Admin CRUD (Admissions/Admin):
       - GET /previous-schools -> role:admissions,admin
       - GET /previous-schools/{id} -> role:admissions,admin
       - POST /previous-schools -> role:admissions,admin
       - PUT /previous-schools/{id} -> role:admissions,admin
       - DELETE /previous-schools/{id} -> role:admissions,admin

7) Migrate
   - Run: cd laravel-api &amp;&amp; php artisan migrate

Frontend (AngularJS 1.x) Steps:
8) Service
   - File: frontend/unity-spa/features/admissions/previous-schools/previous-schools.service.js
   - Methods: list(filters), show(id), create(payload), update(id, payload), remove(id)
   - Base paths:
     - Admin CRUD: {API_BASE}/previous-schools
     - Public read-only (if needed by SPA later): {API_BASE}/admissions/previous-schools

9) Views
   - File: frontend/unity-spa/features/admissions/previous-schools/list.html
     - Table columns: Name, City, Province, Country, Grade, Actions
     - Filters: search, sort/order, pagination
     - Actions: New, Edit, Delete (confirm)
   - File: frontend/unity-spa/features/admissions/previous-schools/edit.html
     - Form fields: name*, city, province, country, grade (number)
     - Save/Cancel buttons
     - Show API validation errors

10) Controllers
   - File: frontend/unity-spa/features/admissions/previous-schools/previous-schools.controller.js
     - Handles listing, search, pagination, delete flow
   - File: frontend/unity-spa/features/admissions/previous-schools/previous-school-edit.controller.js
     - Handles create/edit with route param :id

11) Angular routes
   - File: frontend/unity-spa/core/routes.js (edit)
     - Add:
       - /admissions/previous-schools -> list.html, PreviousSchoolsController, roles ['admissions','admin']
       - /admissions/previous-schools/new -> edit.html, PreviousSchoolEditController, roles ['admissions','admin']
       - /admissions/previous-schools/:id/edit -> edit.html, PreviousSchoolEditController, roles ['admissions','admin']

12) Sidebar Menu
   - File: frontend/unity-spa/shared/components/sidebar/sidebar.html (edit)
   - Add link under Admissions for Previous Schools

13) Manual QA
   - Verify API responses
   - Create valid/duplicate entries (name+city unique check)
   - Edit and validate uniqueness still enforced
   - Delete and verify system logs
   - Angular pages function with permissions gating

14) Optional Seeds
   - Seed a few common schools if needed (skipped for now)

Checklist Progress:
- [ ] Migration
- [ ] Model
- [ ] Form Requests
- [ ] Resource
- [ ] Controller with System Logs
- [ ] Routes (public list + secured CRUD)
- [ ] Migrate
- [ ] Angular service
- [ ] Angular controllers
- [ ] Angular views (list/edit)
- [ ] Angular routes
- [ ] Sidebar link
- [ ] QA pass

Notes:
- Grade: numeric (integer).
- Unauthenticated GET /api/v1/admissions/previous-schools allowed for applicant forms.
- Unique constraint: (name, city).
- Timestamps enabled.
- System logs on create/update/delete via SystemLogService.
