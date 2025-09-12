# Clinic &amp; Health Records Module — TODO

Scope: Implement backend and frontend for adding, displaying, searching, and viewing health records and clinic visit logs for students and faculty, with Critical-path testing.

## Implementation Steps

- [ ] 1) Database Migrations
  - [ ] 1.1 Create migration: clinic_health_records
  - [ ] 1.2 Create migration: clinic_visits
  - [ ] 1.3 Create migration: clinic_attachments
  - Notes:
    - Use indexes (avoid hard foreign keys to legacy tables to reduce migration risk).
    - JSON fields for allergies, medications, immunizations, conditions, triage, medications_dispensed.

- [ ] 2) Eloquent Models (Laravel)
  - [ ] 2.1 App\Models\ClinicHealthRecord (casts, relations: visits, attachments)
  - [ ] 2.2 App\Models\ClinicVisit (casts, relations: record, attachments)
  - [ ] 2.3 App\Models\ClinicAttachment (basic model)

- [ ] 3) Services
  - [ ] 3.1 App\Services\ClinicHealthService
    - createOrUpdate(payload)
    - search(filters)
    - get(id)
  - [ ] 3.2 App\Services\ClinicVisitService
    - create(payload), update(id, payload)
    - listByRecord(recordId, filters)
    - addAttachment(fileMeta), listAttachments(filters)

- [ ] 4) Requests &amp; Resources
  - [ ] 4.1 Requests: HealthRecordStoreRequest, HealthRecordUpdateRequest
  - [ ] 4.2 Requests: VisitStoreRequest, VisitUpdateRequest
  - [ ] 4.3 Resources: HealthRecordResource, HealthRecordListResource, VisitResource, AttachmentResource

- [ ] 5) Controllers
  - [ ] 5.1 App\Http\Controllers\Api\V1\ClinicHealthController: index, store, show, update
  - [ ] 5.2 App\Http\Controllers\Api\V1\ClinicVisitController: index, store, show, update
  - [ ] 5.3 App\Http\Controllers\Api\V1\ClinicAttachmentController: store, index, download, destroy
  - [ ] 5.4 Integrate SystemLogService for audit entries (context: clinic)

- [ ] 6) Routes
  - [ ] 6.1 Register /api/v1/clinic/* endpoints in laravel-api/routes/api.php
    - role:clinic_staff,clinic_admin for create/update/search/list
    - Leave self-view endpoints for future enhancement

- [ ] 7) Frontend (Unity SPA)
  - [ ] 7.1 features/clinic/clinic.service.js (API calls)
  - [ ] 7.2 features/clinic/clinic.controller.js + clinic.html (list &amp; search)
  - [ ] 7.3 features/clinic/record-view.controller.js + record-view.html (record details + visits tab)
  - [ ] 7.4 features/clinic/visit-modal.controller.js + visit-modal.html (create/edit visit)
  - [ ] 7.5 features/clinic/attachment-uploader.directive.js (optional)
  - [ ] 7.6 core/routes.js: add routes /clinic and /clinic/records/:id
  - [ ] 7.7 shared/components/sidebar/sidebar.controller.js: add Clinic menu (roles: clinic_staff, clinic_admin)

- [ ] 8) Attachments
  - [ ] 8.1 Ensure Storage public disk; store under clinic/{record_id}/{visit_id?}/
  - [ ] 8.2 Enforce type/size (PDF/JPG/PNG, limit via env CLINIC_MAX_UPLOAD_MB=10 default)

- [ ] 9) Critical-path Testing
  - Backend:
    - [ ] Health Records: GET /clinic/records, POST /clinic/records, GET /clinic/records/{id}, PUT /clinic/records/{id}
    - [ ] Visits: GET /clinic/visits, POST /clinic/visits, GET /clinic/visits/{id}, PUT /clinic/visits/{id}
    - [ ] Attachments: POST /clinic/attachments, GET /clinic/attachments, GET /clinic/attachments/{id}/download
    - [ ] Permissions: role:clinic_staff,clinic_admin enforcement
    - [ ] Validation: person_type and conditional IDs; JSON structures; numeric ranges
  - Frontend:
    - [ ] Clinic list/search renders and filters
    - [ ] Record detail view shows demographics, allergies, medications, immunizations, conditions
    - [ ] Visits tab lists and shows visit detail; create visit modal works
    - [ ] Attachment upload and listing works
    - [ ] Sidebar visibility gated by roles
  - Files:
    - [ ] Upload acceptance for allowed MIME/types; download authorization

- [ ] 10) Documentation
  - [ ] 10.1 Update plans/clinic-health/README.md (optional) with endpoint shapes and usage
  - [ ] 10.2 Record known limitations and next steps

## Operational Notes

- Running migrations:
  - cd laravel-api
  - php artisan migrate

- Seeding roles (if needed):
  - Ensure roles clinic_staff and clinic_admin exist; otherwise add via existing RoleController/seeders out of scope for MVP.

- Env:
  - CLINIC_MAX_UPLOAD_MB=10 (default) for server-side validation

## Traceability

This TODO implements tasks from plans/clinic-health/implementation_plan.md and aligns with Critical-path testing selection.
