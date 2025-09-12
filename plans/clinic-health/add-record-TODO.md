# Add Health Record — TODO

Scope: Implement the “Add Health Record” feature for Clinic Records list page per plans/clinic-health/add-record-implementation_plan.md.

task_progress Items:
- [x] Step 1: Add new route /clinic/records/new in frontend/unity-spa/core/routes.js with requiredRoles ["clinic_staff","clinic_admin","admin"] and controller ClinicRecordNewController
- [x] Step 2: Implement ClinicRecordNewController in frontend/unity-spa/features/clinic/clinic.controller.js (save, cancel, reset)
- [x] Step 3: Create creation template frontend/unity-spa/features/clinic/record-new.html (identity + optional baseline fields)
- [x] Step 4: Add “Add Record” button to frontend/unity-spa/features/clinic/clinic.html and vm.goNew() to ClinicController
- [ ] Step 5: Critical-path testing (navigation, form validations, POST, redirect to record view)

## Details

- Route: /clinic/records/new
  - templateUrl: features/clinic/record-new.html
  - controller: ClinicRecordNewController
  - controllerAs: vm
  - requiredRoles: ["clinic_staff","clinic_admin","admin"]

- Controller: ClinicRecordNewController
  - Inject: $location, ClinicService
  - Model: vm.form = { person_type: 'student', person_student_id: null, person_faculty_id: null, blood_type: '', height_cm: null, weight_kg: null, allergies: [], medications: [], immunizations: [], conditions: [], notes: '', campus_id: null }
  - Methods:
    - vm.save(): Validate identity based on person_type; construct payload; call ClinicService.createOrUpdateRecord(payload); on success redirect to /clinic/records/{id}; handle errors
    - vm.cancel(): $location.path('/clinic')
    - vm.reset(): Reset form to defaults; clear errors

- Template: record-new.html
  - Fields: person_type (radio/select), conditional ID input (student_number vs faculty_id as numeric IDs per backend), optional baseline fields per HealthRecordStoreRequest
  - Buttons: Cancel, Save (disabled when saving)
  - Show inline validation and error alert area

- Clinic list page:
  - Add “Add Record” button on Results header aligned to right
  - vm.goNew(): $location.path('/clinic/records/new')

## Critical-Path Testing Checklist

- [ ] From /#/clinic, “Add Record” button visible for authorized roles; click navigates to /#/clinic/records/new
- [ ] On /#/clinic/records/new:
  - [ ] person_type toggles required ID field (student → person_student_id, faculty → person_faculty_id)
  - [ ] Save with valid minimal identity POSTs to /api/v1/clinic/records and redirects to /#/clinic/records/{id}
  - [ ] Backend validation error displayed without navigation
  - [ ] Cancel returns to /#/clinic
- [ ] Record view (/clinic/records/{id}) loads the created/upserted record
