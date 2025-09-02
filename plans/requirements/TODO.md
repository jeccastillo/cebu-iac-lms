# Requirements Checklist Feature - TODO

Scope:
- Implement master requirements table and CRUD.
- Implement linking table to associate requirements with applicants (tb_mas_users).
- Follow existing Laravel conventions (no hard FKs, use indexes; integer PKs).

Status Legend:
- [ ] Pending
- [x] Done

Tasks:

1) Database Migrations
- [ ] Create migration: tb_mas_requirements
  - Columns:
    - intID (increments, PK)
    - name (string, unique)
    - type (string; values: college, shs, grad)
    - is_foreign (boolean; default false)
    - timestamps
  - Indexes:
    - type
    - is_foreign
- [ ] Create migration: tb_mas_application_requirements
  - Columns:
    - intID (increments, PK)
    - intStudentID (unsigned integer; tb_mas_users.intID)
    - tb_mas_requirements_id (unsigned integer; tb_mas_requirements.intID)
    - submitted_status (boolean; default false)
    - timestamps
  - Indexes:
    - intStudentID
    - tb_mas_requirements_id
    - unique(intStudentID, tb_mas_requirements_id)

2) Eloquent Models
- [ ] app/Models/Requirement.php
  - table: tb_mas_requirements
  - primaryKey: intID
  - fillable: name, type, is_foreign
  - casts: is_foreign => boolean
- [ ] app/Models/ApplicationRequirement.php
  - table: tb_mas_application_requirements
  - primaryKey: intID
  - fillable: intStudentID, tb_mas_requirements_id, submitted_status
  - casts: submitted_status => boolean

3) Form Requests
- [ ] app/Http/Requests/Api/V1/RequirementStoreRequest.php
  - rules: 
    - name: required|string|max:255|unique:tb_mas_requirements,name
    - type: required|in:college,shs,grad
    - is_foreign: sometimes|boolean
- [ ] app/Http/Requests/Api/V1/RequirementUpdateRequest.php
  - rules:
    - name: sometimes|string|max:255|unique:tb_mas_requirements,name,{{id}},intID
    - type: sometimes|in:college,shs,grad
    - is_foreign: sometimes|boolean

4) API Resource
- [ ] app/Http/Resources/RequirementResource.php
  - fields: id, name, type, is_foreign, created_at, updated_at

5) Controller (CRUD)
- [ ] app/Http/Controllers/Api/V1/RequirementController.php
  - index: filters: search(name), type, is_foreign; sort: name|type; pagination optional
  - show: by id
  - store: uses RequirementStoreRequest
  - update: uses RequirementUpdateRequest
  - destroy: delete row

6) Routes
- [ ] Update laravel-api/routes/api.php
  - Add endpoints (guard with role:admissions,admin):
    - GET /api/v1/requirements
    - GET /api/v1/requirements/{id}
    - POST /api/v1/requirements
    - PUT /api/v1/requirements/{id}
    - DELETE /api/v1/requirements/{id}

7) (Optional - Next Step) Applicant Checklist Endpoints (not in initial task, can be added later)
- [ ] GET /api/v1/applicants/{studentId}/requirements
- [ ] POST /api/v1/applicants/{studentId}/requirements/bulk-attach
- [ ] PUT /api/v1/applicants/{studentId}/requirements/{applicationRequirementId}
- [ ] DELETE /api/v1/applicants/{studentId}/requirements/{applicationRequirementId}

Notes:
- Do not add hard foreign key constraints to match legacy schema approach.
- Use consistent naming and patterns as PaymentMode/PaymentDescription CRUD for uniformity.
- Middleware can be adjusted later if required.
