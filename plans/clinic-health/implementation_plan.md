# Implementation Plan

[Overview]
Implement a complete Clinic and Health Records module to add, display, search, and view health records and clinic visit logs for both students and faculty across the existing Laravel API (backend) and Unity SPA (AngularJS) frontend.

This implementation introduces new database tables and APIs to manage a longitudinal health record per person (student or faculty) and per-visit clinic encounter logs with triage, assessment, diagnosis, and treatment. It integrates with the existing roles middleware for access control, reuses the existing SystemLogService for audit logging, and adds a new Clinic section in the SPA to list/search records, view details, and review visit histories. The design favors pragmatic, minimally disruptive integration by using explicit linkages to existing person records (tb_mas_users for students and Faculty model/table for faculty) with simple, index-friendly structures and JSON-typed detail payloads for flexible clinical data.

The backend will provide:
- Core CRUD for Health Records (one longitudinal record per person).
- Visit logs (encounter records) linked to a health record.
- File attachments for records and visits (images/pdfs).
- Search endpoints with filters for identity (student number/faculty id), name, date ranges, campus, program, year level, diagnosis, medication, and allergy substrings.
- Strict role-based access (clinic_staff/clinic_admin). Students and faculty can view their own data in read-only mode if desired later via dedicated endpoints.

The frontend will provide:
- A Clinic module with list/search, detail view (including demographics, allergies, medications, immunizations, conditions), and visit log tab with per-visit detail view (triage, assessment, treatment, medications dispensed, follow-up).
- Integration into existing route structure and sidebar with appropriate role gating.

[Types]
Introduce new domain types for clinic health data and visits, with relational links to both students and faculty.

Data structures and validation specifications:

1) Database Tables (new)

- clinic_health_records
  - id: PK, bigint
  - person_type: enum('student','faculty') NOT NULL
  - person_student_id: bigint NULL, FK tb_mas_users.intID (nullable; required when person_type='student')
  - person_faculty_id: bigint NULL, FK faculties.id (nullable; required when person_type='faculty') — adapt to Faculty model table name/PK
  - blood_type: varchar(3) NULL, in {'A+','A-','B+','B-','AB+','AB-','O+','O-'}
  - height_cm: decimal(5,2) NULL, 0 <= height_cm <= 300
  - weight_kg: decimal(5,2) NULL, 0 <= weight_kg <= 500
  - allergies: json NULL (array of {name:string, reaction?:string, severity?:'mild'|'moderate'|'severe'})
  - medications: json NULL (array of {name:string, dose?:string, freq?:string, start_date?:date, end_date?:date, ongoing?:bool})
  - immunizations: json NULL (array of {name:string, date?:date, lot?:string, site?:string})
  - conditions: json NULL (array of {name:string, since?:date, status?:'active'|'resolved'})
  - notes: text NULL
  - campus_id: bigint NULL (for default campus association)
  - last_updated_by: bigint NULL (users.id of staff)
  - created_at: timestamp
  - updated_at: timestamp
  - Indexes:
    - idx_clinic_hr_subject (person_type, person_student_id, person_faculty_id)
    - idx_clinic_hr_campus (campus_id)

- clinic_visits
  - id: PK, bigint
  - record_id: FK clinic_health_records.id, ON DELETE CASCADE
  - visit_date: datetime NOT NULL (default now)
  - reason: varchar(255) NULL
  - triage: json NULL ({bp?:string, hr?:int, rr?:int, temp_c?:decimal(4,1), spo2?:int, pain?:int(0..10)})
  - assessment: text NULL
  - diagnosis_codes: json NULL (array of strings; free-form or ICD codes)
  - treatment: text NULL
  - medications_dispensed: json NULL (array of {name:string, dose?:string, qty?:number, instructions?:string})
  - follow_up: text NULL
  - campus_id: bigint NULL
  - attachments_count: int NOT NULL DEFAULT 0
  - created_by: bigint NOT NULL (users.id of staff)
  - updated_by: bigint NULL
  - created_at: timestamp
  - updated_at: timestamp
  - Indexes:
    - idx_clinic_visits_record (record_id)
    - idx_clinic_visits_date (visit_date)
    - idx_clinic_visits_campus (campus_id)

- clinic_attachments
  - id: PK, bigint
  - record_id: FK clinic_health_records.id NULL
  - visit_id: FK clinic_visits.id NULL
  - original_name: varchar(255) NOT NULL
  - path: varchar(512) NOT NULL
  - mime: varchar(128) NOT NULL
  - size_bytes: bigint NOT NULL
  - uploaded_by: bigint NOT NULL (users.id of staff)
  - created_at: timestamp
  - updated_at: timestamp
  - Indexes:
    - idx_clinic_attach_record (record_id)
    - idx_clinic_attach_visit (visit_id)

2) Model Contracts (PHP)

- App\Models\ClinicHealthRecord
  - fillable: person_type, person_student_id, person_faculty_id, blood_type, height_cm, weight_kg, allergies, medications, immunizations, conditions, notes, campus_id, last_updated_by
  - casts: allergies => 'array', medications => 'array', immunizations => 'array', conditions => 'array'
  - relations: visits() hasMany ClinicVisit, attachments() morph-like simulated via where('record_id')

- App\Models\ClinicVisit
  - fillable: record_id, visit_date, reason, triage, assessment, diagnosis_codes, treatment, medications_dispensed, follow_up, campus_id, attachments_count, created_by, updated_by
  - casts: triage => 'array', diagnosis_codes => 'array', medications_dispensed => 'array'
  - relations: record() belongsTo ClinicHealthRecord, attachments() where('visit_id')

- App\Models\ClinicAttachment
  - fillable: record_id, visit_id, original_name, path, mime, size_bytes, uploaded_by

Validation rules (controller/service level):
- person_type in {'student','faculty'}
- Required conditional: person_student_id required if student; person_faculty_id required if faculty
- Numeric ranges for vitals where applicable
- Payload arrays for JSON fields must validate as arrays of objects with allowed keys

[Files]
Introduce new backend and frontend files; minimal edits to existing router and sidebar.

New backend files:
- laravel-api/app/Models/ClinicHealthRecord.php (Eloquent model)
- laravel-api/app/Models/ClinicVisit.php (Eloquent model)
- laravel-api/app/Models/ClinicAttachment.php (Eloquent model)
- laravel-api/app/Http/Controllers/Api/V1/ClinicHealthController.php (records CRUD/search)
- laravel-api/app/Http/Controllers/Api/V1/ClinicVisitController.php (visits CRUD/search)
- laravel-api/app/Http/Controllers/Api/V1/ClinicAttachmentController.php (upload/list)
- laravel-api/app/Services/ClinicHealthService.php (business logic for records)
- laravel-api/app/Services/ClinicVisitService.php (business logic for visits)
- laravel-api/app/Http/Requests/Api/V1/Clinic/HealthRecordStoreRequest.php (validation)
- laravel-api/app/Http/Requests/Api/V1/Clinic/HealthRecordUpdateRequest.php
- laravel-api/app/Http/Requests/Api/V1/Clinic/VisitStoreRequest.php
- laravel-api/app/Http/Requests/Api/V1/Clinic/VisitUpdateRequest.php
- laravel-api/app/Http/Resources/Clinic/HealthRecordResource.php
- laravel-api/app/Http/Resources/Clinic/HealthRecordListResource.php
- laravel-api/app/Http/Resources/Clinic/VisitResource.php
- laravel-api/app/Http/Resources/Clinic/AttachmentResource.php
- laravel-api/database/migrations/2025_09_12_000001_create_clinic_health_records_table.php
- laravel-api/database/migrations/2025_09_12_000002_create_clinic_visits_table.php
- laravel-api/database/migrations/2025_09_12_000003_create_clinic_attachments_table.php

Existing backend files to be modified:
- laravel-api/routes/api.php
  - Add new prefixed routes under /api/v1/clinic/* with role middleware.
- Optional: laravel-api/app/Http/Controllers/Api/V1/UsersController.php or RoleController if we need to seed/view roles, but seeding is not required for initial scaffolding.

Frontend new files:
- frontend/unity-spa/features/clinic/clinic.service.js (API integration)
- frontend/unity-spa/features/clinic/clinic.controller.js (List/Search controller)
- frontend/unity-spa/features/clinic/clinic.html (List/Search view)
- frontend/unity-spa/features/clinic/record-view.controller.js (Record detail controller)
- frontend/unity-spa/features/clinic/record-view.html (Record + visit tabs view)
- frontend/unity-spa/features/clinic/visit-modal.controller.js (Create visit modal)
- frontend/unity-spa/features/clinic/visit-modal.html
- frontend/unity-spa/features/clinic/attachment-uploader.directive.js (optional small directive for upload)

Existing frontend files to be modified:
- frontend/unity-spa/core/routes.js (register /clinic and /clinic/records/:id routes)
- frontend/unity-spa/shared/components/sidebar/sidebar.controller.js (add menu item for Clinic, role-gated for clinic_staff and clinic_admin)

Files to be deleted or moved:
- None

Configuration file updates:
- Laravel filesystem disks: ensure default public storage configured; if not, update laravel-api/config/filesystems.php (optional, only if missing)
- .env keys (documented only; no code change): CLINIC_MAX_UPLOAD_MB default 10

[Functions]
Add new public API endpoints and service functions for records, visits, attachments, and search.

New functions (controllers):
- ClinicHealthController@index(Request): list/search health records; file: laravel-api/app/Http/Controllers/Api/V1/ClinicHealthController.php
- ClinicHealthController@store(HealthRecordStoreRequest): create record (idempotent: upsert by person)
- ClinicHealthController@show(int $id): view one record with latest vitals and summary
- ClinicHealthController@update(int $id, HealthRecordUpdateRequest): update demographics, baseline, allergies/medications/etc.
- ClinicHealthController@search(Request): advanced search facets (name, student_number, faculty_id, diagnosis, medication, allergy, campus, program, year level)

- ClinicVisitController@index(Request): list visits by record_id/date range
- ClinicVisitController@store(VisitStoreRequest): create a visit with triage/assessment/treatment
- ClinicVisitController@show(int $id): view visit detail
- ClinicVisitController@update(int $id, VisitUpdateRequest): update visit

- ClinicAttachmentController@store(Request): upload attachment (record_id or visit_id required)
- ClinicAttachmentController@index(Request): list attachments for record_id or visit_id
- ClinicAttachmentController@download(int $id): stream file
- ClinicAttachmentController@destroy(int $id): delete (clinic_admin only)

New functions (services):
- ClinicHealthService::search(array $filters): array
- ClinicHealthService::createOrUpdate(array $payload): ClinicHealthRecord
- ClinicHealthService::get(int $id): ClinicHealthRecord
- ClinicVisitService::listByRecord(int $recordId, array $filters): array
- ClinicVisitService::create(array $payload): ClinicVisit
- ClinicVisitService::update(int $id, array $payload): ClinicVisit
- ClinicVisitService::addAttachment(array $fileMeta): ClinicAttachment
- ClinicVisitService::listAttachments(array $filters): array

Modified functions:
- laravel-api/routes/api.php: add route registrations under Route::prefix('v1') with proper middleware
  - GET /clinic/records (index/search)
  - POST /clinic/records (store)
  - GET /clinic/records/{id} (show)
  - PUT /clinic/records/{id} (update)
  - GET /clinic/visits (index)
  - POST /clinic/visits (store)
  - GET /clinic/visits/{id} (show)
  - PUT /clinic/visits/{id} (update)
  - POST /clinic/attachments (upload)
  - GET /clinic/attachments (list)
  - GET /clinic/attachments/{id}/download (download)
  - DELETE /clinic/attachments/{id} (destroy)

Removed functions:
- None

[Classes]
Add new Eloquent models and controller classes; integrate with existing SystemLogService for audit.

New classes:
- App\Models\ClinicHealthRecord
  - Methods: visits(), attachments()
- App\Models\ClinicVisit
  - Methods: record(), attachments()
- App\Models\ClinicAttachment

- App\Http\Controllers\Api\V1\ClinicHealthController
  - Methods: index, store, show, update, search
  - Uses: ClinicHealthService, SystemLogService
- App\Http\Controllers\Api\V1\ClinicVisitController
  - Methods: index, store, show, update
  - Uses: ClinicVisitService, SystemLogService
- App\Http\Controllers\Api\V1\ClinicAttachmentController
  - Methods: store, index, download, destroy
  - Uses: Storage, SystemLogService

- App\Services\ClinicHealthService
  - Responsibilities: validation, upsert-by-person, JSON field normalization, search joins to users/faculty/programs
- App\Services\ClinicVisitService
  - Responsibilities: visit create/update, attachments_count sync, search and date-range filters

Modified classes:
- None; only route registrations are changed.

Removed classes:
- None

[Dependencies]
No new external dependencies are required for the MVP; use built-in Storage for uploads.

- Laravel built-in:
  - Illuminate\Support\Facades\Storage for file handling (public disk)
  - Illuminate\Support\Facades\DB for cross-table joins to tb_mas_users and faculty table
  - Existing App\Services\SystemLogService for audit entries

Optional future enhancements (not required now):
- ICD code list package or static catalog
- Virus scanning for uploads

[Testing]
Adopt feature tests for API endpoints and basic UI smoke tests/manual flows.

Backend tests (new):
- laravel-api/tests/Feature/Clinic/HealthRecordsTest.php
  - Create, show, update, search with role middleware
- laravel-api/tests/Feature/Clinic/VisitsTest.php
  - Create visit, view visit, list by record/date, update
- laravel-api/tests/Feature/Clinic/AttachmentsTest.php
  - Upload and list attachments; permissions for delete/download
- Edge cases: wrong person_type combinations; invalid JSON payloads; permissions enforcement

Frontend validation:
- Manual verification steps:
  - Menu visibility by role
  - List/search filters return expected results
  - Record view renders allergies/medications/immunizations/conditions
  - Visit list and visit details render triage/assessment/treatment
  - Attachment upload and visibility

[Implementation Order]
Implement in layers to keep migrations and API stable before wiring up the SPA.

1) Database Layer
   - Create migrations for clinic_health_records, clinic_visits, clinic_attachments
   - Run migrations and verify schema
2) Models
   - Add Eloquent models ClinicHealthRecord, ClinicVisit, ClinicAttachment with casts and relations
3) Services
   - Implement ClinicHealthService (createOrUpdate, search, get)
   - Implement ClinicVisitService (create, update, listByRecord, addAttachment, listAttachments)
4) Controllers and Requests/Resources
   - Implement validation request classes (Store/Update for record/visit)
   - Implement controllers (Health, Visit, Attachment) with responses via resources
   - Integrate SystemLogService for create/update/delete audit entries (context: 'clinic')
5) Routes
   - Register /api/v1/clinic/* endpoints with middleware:
     - role:clinic_staff,clinic_admin for create/update
     - role:clinic_staff,clinic_admin for search/index
     - read-only self-view endpoints (optional future): allow authenticated students/faculty to view their own via token/id with redactions
6) Frontend SPA
   - Add clinic.service.js with methods for records/visits/attachments/search
   - Add clinic.controller.js + clinic.html for list/search page
   - Add record-view.controller.js + record-view.html for detail and visit tabs
   - Add visit-modal.* for creating visits (staff-only)
   - Wire routes in core/routes.js and add sidebar item (role: clinic_staff, clinic_admin)
7) Attachments
   - Implement upload endpoint and integrate basic file input directive in UI
   - Ensure files stored under storage/app/public/clinic/{record_id}/{visit_id?}/
8) Security & Permissions
   - Enforce role middleware on routes
   - If needed, add self-view endpoints later with narrow scopes
9) Testing & QA
   - Write and run feature tests
   - Perform manual UI walkthrough
10) Documentation
   - Endpoint reference and UI usage notes in plans/clinic-health/README.md (optional)

Appendix: API Shapes (MVP)

- POST /api/v1/clinic/records
  { person_type: 'student'|'faculty', person_student_id?: number, person_faculty_id?: number,
    blood_type?: string, height_cm?: number, weight_kg?: number,
    allergies?: Array<{name,reaction?,severity?}>,
    medications?: Array<{name,dose?,freq?,start_date?,end_date?,ongoing?}>,
    immunizations?: Array<{name,date?,lot?,site?}>,
    conditions?: Array<{name,since?,status?}>, notes?: string, campus_id?: number }

- GET /api/v1/clinic/records?q=&amp;student_number=&amp;faculty_id=&amp;last_name=&amp;first_name=&amp;campus_id=&amp;program_id=&amp;year_level=&amp;diagnosis=&amp;medication=&amp;allergy=&amp;date_from=&amp;date_to=&amp;page=&amp;per_page=
  Returns paginated HealthRecordListResource

- GET /api/v1/clinic/records/{id}
  Returns HealthRecordResource with recent visits (summary)

- PUT /api/v1/clinic/records/{id}
  Same shape as store (partial accepted)

- POST /api/v1/clinic/visits
  { record_id: number, visit_date?: string, reason?: string, triage?: {...}, assessment?: string,
    diagnosis_codes?: string[], treatment?: string, medications_dispensed?: Array<{...}>,
    follow_up?: string, campus_id?: number }

- GET /api/v1/clinic/visits?record_id=...&amp;date_from=&amp;date_to=&amp;page=&amp;per_page=
  Returns list of VisitResource

- GET /api/v1/clinic/visits/{id}
  Returns VisitResource

- PUT /api/v1/clinic/visits/{id}
  Same shape as store (partial accepted)

- POST /api/v1/clinic/attachments (multipart/form-data)
  Fields: file, record_id? (required if no visit_id), visit_id?
  Returns AttachmentResource

- GET /api/v1/clinic/attachments?record_id=... or ?visit_id=...
  Returns list of AttachmentResource

- GET /api/v1/clinic/attachments/{id}/download
  Streams file

- DELETE /api/v1/clinic/attachments/{id}
  Deletes file (clinic_admin only)
