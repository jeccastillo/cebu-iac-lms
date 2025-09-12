# Implementation Plan

[Overview]
Add an “Add Health Record” capability to the Clinic Records module by introducing a dedicated creation page and wiring it to the existing POST /api/v1/clinic/records endpoint, enabling staff to create a record for either a student or faculty and then redirect to the newly created record view.

This change enhances the clinic records list by providing a clear entry point for creating a new longitudinal health record when one does not yet exist for a person. It fits into the current system by leveraging the already implemented backend endpoints, request validation, and service-layer upsert logic (ClinicHealthService::createOrUpdate). The approach avoids modal dependencies (none present in the SPA) and keeps UX aligned with existing route-based navigation patterns. Access control remains enforced via existing role middleware (clinic_staff, clinic_admin, admin).

[Types]  
No new backend types; introduce a frontend draft shape for the creation form.

Frontend data structure specifications (JavaScript shape for the creation page model):
- HealthRecordDraft
  - person_type: 'student' | 'faculty' (required)
  - person_student_id: number | null (required if person_type = 'student', integer greater than 0)
  - person_faculty_id: number | null (required if person_type = 'faculty', integer greater than 0)
  - blood_type?: string | null (one of: A+, A-, B+, B-, AB+, AB-, O+, O-; or left empty)
  - height_cm?: number | null (0..300)
  - weight_kg?: number | null (0..500)
  - allergies?: Array<{ name: string, reaction?: string, severity?: 'mild' | 'moderate' | 'severe' }> | null
  - medications?: Array<{ name: string, dose?: string, freq?: string, start_date?: string, end_date?: string, ongoing?: boolean }> | null
  - immunizations?: Array<{ name: string, date?: string, lot?: string, site?: string }> | null
  - conditions?: Array<{ name: string, since?: string, status?: 'active' | 'resolved' }> | null
  - notes?: string | null
  - campus_id?: number | null
  - last_updated_by?: number | null (optional; may be set by backend if omitted)

Validation rules (frontend):
- Require person_type and the appropriate person id based on type.
- Validate optional numeric ranges for height_cm (0..300) and weight_kg (0..500).
- Allow arrays to be empty or omitted at creation time.
- Allow baseline fields to be optional (identity-only creation is acceptable; additional fields can be filled as available).

[Files]
Introduce one new frontend template and modify existing frontend files to support navigation and controller logic.

Detailed breakdown:
- New files to be created:
  - frontend/unity-spa/features/clinic/record-new.html
    - Purpose: Dedicated creation form for a clinic health record with inputs for identity (person_type + ID) and optional baseline fields. Provides Save/Cancel actions.
- Existing files to be modified:
  - frontend/unity-spa/core/routes.js
    - Add route /clinic/records/new, controller ClinicRecordNewController, requiredRoles ["clinic_staff", "clinic_admin", "admin"].
  - frontend/unity-spa/features/clinic/clinic.controller.js
    - Register a new controller: ClinicRecordNewController.
    - In ClinicController, add a goNew() navigation function to move to /clinic/records/new.
  - frontend/unity-spa/features/clinic/clinic.html
    - Add an “Add Record” button in the Results card header that calls vm.goNew().
- Files to be deleted or moved:
  - None
- Configuration file updates:
  - None required. Existing APP_CONFIG/API_BASE and role guards suffice.

[Functions]
Add a new controller and small navigation function; reuse existing service methods.

Detailed breakdown:
- New functions:
  - ClinicRecordNewController (angular controller; file path: frontend/unity-spa/features/clinic/clinic.controller.js)
    - Signature: function ClinicRecordNewController($location, ClinicService, StorageService)
    - Purpose: Manage the create form: model state, basic validation, submit to POST /clinic/records, handle success/error, and redirect to the record-view route.
    - Key methods:
      - vm.save(): Validates identity fields; constructs payload; calls ClinicService.createOrUpdateRecord(payload); on success redirect to /clinic/records/{id}; handle errors.
      - vm.cancel(): Navigates back to /clinic.
      - vm.reset(): Resets the form model to defaults.
  - In ClinicController (same file):
    - vm.goNew(): $location.path('/clinic/records/new') to navigate from list page.
- Modified functions:
  - none in services (ClinicService already exposes createOrUpdateRecord(payload)).
- Removed functions:
  - None

[Classes]
No backend class changes; one new AngularJS controller added to existing module.

Detailed breakdown:
- New classes:
  - AngularJS controller: ClinicRecordNewController
    - File: frontend/unity-spa/features/clinic/clinic.controller.js
    - Injects: $location, ClinicService, StorageService
    - Responsibilities: Manage creation form lifecycle and submission.
- Modified classes:
  - AngularJS controller: ClinicController (add goNew method)
- Removed classes:
  - None

[Dependencies]
No new external packages or libraries.

- Reuse existing:
  - ClinicService.createOrUpdateRecord(payload) → POST /api/v1/clinic/records
  - Role routing guards already present in routes.js
  - StorageService for reading login state (optional; if needed to pass last_updated_by)
- No modal or UI library introduced (no $uibModal/ngDialog in repo); adopt dedicated route pattern.

[Testing]
Manual UI and integration checks focusing on the creation workflow.

- New UI flow tests (manual):
  - With a user having clinic_staff or clinic_admin or admin role:
    - From /#/clinic:
      - Verify “Add Record” button appears.
      - Click “Add Record” → navigates to /#/clinic/records/new.
    - On creation page:
      - Attempt Save with missing identity → validation error shown, no request sent.
      - Select person_type = student, enter person_student_id = valid numeric id; optionally fill baseline fields; Save → observe POST; on success redirect to /#/clinic/records/{id}; record page loads correctly.
      - Repeat for person_type = faculty with person_faculty_id.
    - Error handling:
      - Enter invalid values (e.g., negative height_cm) → backend validation error returns; error displayed in page.
      - Attempt creating a record for a person that already has a record → service upsert should return existing record; redirect still occurs to that record’s view.
- API checks:
  - Confirm POST payload matches HealthRecordStoreRequest (identity + optional fields).
  - Confirm response shape { success: true, data: {...} } includes id for redirect.
- Regression:
  - Existing search and record view continue working unchanged.

[Implementation Order]
Implement in a minimal-risk sequence touching routing, controller, then template, and finally list-page wiring.

1) Routing
   - Add new route /clinic/records/new to frontend/unity-spa/core/routes.js with requiredRoles ["clinic_staff", "clinic_admin", "admin"] and controller ClinicRecordNewController.
2) Controller
   - Extend frontend/unity-spa/features/clinic/clinic.controller.js:
     - Register and implement ClinicRecordNewController.
     - Add vm.goNew() to ClinicController for navigation to the new route.
3) Template
   - Create frontend/unity-spa/features/clinic/record-new.html with form fields:
     - Person Type (student/faculty), Conditional Person ID input, optional baseline fields, Save/Cancel.
     - Basic inline validation messages.
4) List Page button
   - Update frontend/unity-spa/features/clinic/clinic.html to include an “Add Record” button near the Results header.
5) Smoke Test
   - Validate navigation, form validation, POST integration, and redirect to record view upon success.
6) Documentation
   - None required beyond code comments (plan file serves as design record).
