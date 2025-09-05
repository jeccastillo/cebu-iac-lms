# Applicant Types CRUD (Laravel API + AngularJS)

Goal:
- Create table tb_mas_applicant_types with fields:
  - intID (PK, increments)
  - name (string)
  - type (enum: college, shs, grad)
- Implement full CRUD via Laravel API backend.
- Implement AngularJS (unity-spa) frontend pages (list and edit/create).
- Log create/update/delete via SystemLogService.
- Enforce uniqueness on (name, type).

Conventions:
- Primary key: intID
- Resource responses: { success, data, meta? }
- Role middleware: admissions, admin
- Frontend headers: include X-Faculty-ID when available.

Tasks:
1) Migration
  - [ ] Create migration: database/migrations/2025_09_03_000800_create_tb_mas_applicant_types_table.php
    - [ ] Table tb_mas_applicant_types with columns intID, name, type (enum), timestamps
    - [ ] Unique constraint on (name, type) named uq_tb_mas_applicant_types_name_type

2) Eloquent Model
  - [ ] Create app/Models/ApplicantType.php
    - [ ] $table = 'tb_mas_applicant_types'
    - [ ] $primaryKey = 'intID'
    - [ ] $fillable = ['name','type']
    - [ ] public $timestamps = true

3) API Resource
  - [ ] Create app/Http/Resources/ApplicantTypeResource.php
    - [ ] Map id from intID, include name, type, created_at, updated_at

4) Form Requests
  - [ ] Create app/Http/Requests/Api/V1/ApplicantTypeStoreRequest.php
    - [ ] Validate: name required|string|max:255 and unique on (name,type)
    - [ ] Validate: type required|in:college,shs,grad
  - [ ] Create app/Http/Requests/Api/V1/ApplicantTypeUpdateRequest.php
    - [ ] Validate: sometimes|required fields
    - [ ] Unique (name,type) ignoring current intID

5) Controller
  - [ ] Create app/Http/Controllers/Api/V1/ApplicantTypeController.php
    - [ ] index(): optional filters: search (name), type filter, sorting (name,type,created_at), optional pagination
    - [ ] show(int $id)
    - [ ] store(): use ApplicantTypeStoreRequest, log create via SystemLogService
    - [ ] update(): use ApplicantTypeUpdateRequest, log update via SystemLogService
    - [ ] destroy(): hard delete, log delete via SystemLogService

6) Routes
  - [ ] Update laravel-api/routes/api.php
    - [ ] Add use App\Http\Controllers\Api\V1\ApplicantTypeController;
    - [ ] Add GET/POST/PUT/DELETE routes under role:admissions,admin

7) Frontend (AngularJS)
  - [ ] Create folder frontend/unity-spa/features/admissions/applicant-types/
  - [ ] Service: applicant-types.service.js
    - [ ] list/show/create/update/remove against /applicant-types endpoints
    - [ ] Attach X-Faculty-ID headers from StorageService (like PreviousSchoolsService)
  - [ ] Controllers: applicant-types.controller.js
    - [ ] ApplicantTypesController: list, filters, pagination, sorting
    - [ ] ApplicantTypeEditController: create/edit form logic
  - [ ] Templates:
    - [ ] list.html: table with Name, Type, Created At, Actions; filters and pager
    - [ ] edit.html: form with name (text) and type (select: college/shs/grad)
  - [ ] Add routes in frontend/unity-spa/core/routes.js
    - [ ] /admissions/applicant-types
    - [ ] /admissions/applicant-types/new
    - [ ] /admissions/applicant-types/:id/edit
  - [ ] Add sidebar link in frontend/unity-spa/shared/components/sidebar/sidebar.html under Admissions

8) Testing / Verification
  - [ ] Run migrations (php artisan migrate) in laravel-api
  - [ ] Smoke test API endpoints (create/update/delete and 422 on duplicate)
  - [ ] Open UI routes and verify create/edit/delete flow
  - [ ] Confirm SystemLogService entries created on add/edit/delete

Notes:
- Uniqueness: composite (name, type) per confirmation.
- Deletions: hard delete (no soft deletes) as per default.
