# Implementation Plan

[Overview]
Add an editable "Registration Details" section to the Registrar Enlistment page that allows registrar/admin to edit an existing tb_mas_registration record for the selected student and selected term. If no registration exists for that student/term, display a read-only notice and do not allow creation from this UI.

This feature complements the existing enlistment workflow by letting registrars adjust key registration attributes (year level, student type, current program/curriculum, tuition year, payment type, LOA remarks, withdrawal period) for a student in the selected term. The backend will expose read and update endpoints, enforcing that updates are allowed only when a registration row exists. All updates will be audit-logged via SystemLogService. The frontend will integrate this UI in the Enlistment screen under "Current Enlisted", pull dropdown data for programs and curricula, and handle state refresh on student/term changes.


[Types]  
Define strict payload/request/response shapes and field validation to ensure safe updates without schema errors.

Types and constraints:
- RegistrationEditableFields
  - intYearLevel: integer, required, min 1
  - enumStudentType: string, one of: continuing | new | returnee | transfer
  - current_program: integer (tb_mas_programs.intProgramID), nullable allowed
  - current_curriculum: integer (tb_mas_curriculum.intID), nullable allowed
  - tuition_year: integer (tb_mas_tuition_year.intID), nullable allowed
  - paymentType: string, nullable (free-form string aligned with legacy values, e.g., "full", "installment", "voucher", etc.)
  - loa_remarks: string, nullable (default to '')
  - withdrawal_period: integer, nullable (default to 0)

- GET /v1/unity/registration Response
  - success: boolean
  - data:
    - exists: boolean
    - registration?: object (present if exists)
      - intRegistrationID: int
      - intStudentID: int
      - intAYID: int
      - intROG: int
      - dteRegistered: string (Y-m-d H:i:s)
      - intYearLevel: int
      - enumStudentType: string
      - current_program: int|null
      - current_curriculum: int|null
      - tuition_year: int|null
      - paymentType: string|null
      - loa_remarks: string|null
      - withdrawal_period: int|null
      - program?: { id:int, code:string, description?:string } (optional join for display)
      - curriculum?: { id:int, name:string } (optional join for display)

- PUT /v1/unity/registration Request
  - student_number: string (required)
  - term: int (required)
  - fields: RegistrationEditableFields (at least one updatable field present)

- PUT /v1/unity/registration Response
  - success: boolean
  - message: string
  - data:
    - updated: int (affected rows)
    - registration: same shape as GET (fresh row after update)

Validation rules:
- student_number: required|string
- term: required|integer
- fields: required|array|min:1
- fields.intYearLevel: sometimes|integer|min:1
- fields.enumStudentType: sometimes|string|in:continuing,new,returnee,transfer
- fields.current_program: sometimes|integer|exists:tb_mas_programs,intProgramID
- fields.current_curriculum: sometimes|integer|exists:tb_mas_curriculum,intID
- fields.tuition_year: sometimes|integer|exists:tb_mas_tuition_year,intID
- fields.paymentType: sometimes|nullable|string|max:50
- fields.loa_remarks: sometimes|nullable|string|max:1000
- fields.withdrawal_period: sometimes|integer|min:0


[Files]
Introduce two new API routes and a new FormRequest; modify the UnityController and RegistrationService; enhance Angular service/controller/template.

Detailed breakdown:
- New files to be created
  - laravel-api/app/Http/Requests/Api/V1/UnityRegistrationUpdateRequest.php
    - Purpose: Validate PUT /unity/registration request payload for editing registration fields ensuring constraints listed above.

- Existing files to be modified
  - laravel-api/routes/api.php
    - Add endpoints:
      - GET /v1/unity/registration (role: registrar,admin) to fetch existing registration for student_number+term.
      - PUT /v1/unity/registration (role: registrar,admin) to update editable fields when a registration exists.

  - laravel-api/app/Http/Controllers/Api/V1/UnityController.php
    - Add two controller actions:
      - registration(Request $request): fetch registration by student_number+term; returns exists=false if not found.
      - updateRegistration(UnityRegistrationUpdateRequest $request): perform guarded update; strictly fail with 404 when not exists; audit-log via SystemLogService; return fresh row after update.

  - laravel-api/app/Services/RegistrationService.php
    - Add methods:
      - findByStudentNumberAndTerm(string $studentNumber, int $term): ?object row with optional program/curriculum joins; no creation.
      - updateByStudentNumberAndTerm(string $studentNumber, int $term, array $fields, Request $request): array { success, updated:int, row?:object } with audit logging. Guards: update only if row exists; whitelist fields; best-effort for legacy nullable columns.

  - frontend/unity-spa/features/unity/unity.service.js
    - Add methods:
      - getRegistration(student_number, term): GET /unity/registration
      - updateRegistration(payload): PUT /unity/registration
    - Leverage _adminHeaders to tag acting faculty for auditing.

  - frontend/unity-spa/features/registrar/enlistment/enlistment.controller.js
    - Add view-model state:
      - vm.registration (raw row)
      - vm.regForm = { intYearLevel, enumStudentType, current_program, current_curriculum, tuition_year, paymentType, loa_remarks, withdrawal_period }
      - vm.programs = [] and vm.curricula = []
      - vm.loadRegistration(), vm.saveRegistration(), vm.loadPrograms(), vm.loadCurricula()
    - Wire loadRegistration on student selection and term changes (existing onStudentSelected + termChanged listener).
    - Handle "exists=false" with UI notice; disable save if no row or no changes; deep-compare to enable Save.

  - frontend/unity-spa/features/registrar/enlistment/enlistment.html
    - Add "Registration Details" panel beneath "Current Enlisted":
      - When vm.registration exists: render editable form with:
        - Year Level (number)
        - Student Type (dropdown: continuing, new, returnee, transfer)
        - Program (dropdown from ProgramsService)
        - Curriculum (dropdown from CurriculaService)
        - Tuition Year (optional dropdown from TuitionYear API if readily available; if not, numeric input for now)
        - Payment Type (text input or small dropdown if common values are present elsewhere)
        - LOA Remarks (textarea)
        - Withdrawal Period (number)
      - Disabled Save button when no changes; show spinner on saving; show success/error toast.
      - When no registration exists: show read-only notice: "No registration for this term. This form allows editing only when a registration already exists."

- Files to be deleted or moved
  - None

- Configuration file updates
  - None


[Functions]
Add minimal new backend functions and extend frontend controller/service to support fetching and updating registration.

Detailed breakdown:
- New functions
  - UnityController::registration(Request $request): JsonResponse
    - Query params: ?student_number=...&term=...
    - Purpose: Fetch an existing registration row for display. Returns { success:true, data:{ exists:false } } when not found.
  - UnityController::updateRegistration(UnityRegistrationUpdateRequest $request): JsonResponse
    - Purpose: Update editable fields if record exists; returns 404 if missing. Log via SystemLogService::log('update', 'Registration', id, old, new, $request).

  - RegistrationService::findByStudentNumberAndTerm(string $studentNumber, int $term): ?object
    - Purpose: Locate registration and include optional joins for program/curriculum names.

  - RegistrationService::updateByStudentNumberAndTerm(string $studentNumber, int $term, array $fields, Request $request): array
    - Purpose: Whitelist and update only allowed columns; 404-equivalent when not exists; return fresh row; log audit.

  - UnityService (Angular):
    - getRegistration(student_number, term)
    - updateRegistration(payload)

- Modified functions
  - EnlistmentController (Angular):
    - onStudentSelected(): also triggers loadRegistration()
    - termChanged listener: also triggers loadRegistration()
    - Add: loadRegistration(), loadPrograms(), loadCurricula(), saveRegistration()

- Removed functions
  - None


[Classes]
One new FormRequest class; no model changes required.

Detailed breakdown:
- New classes
  - App\Http\Requests\Api\V1\UnityRegistrationUpdateRequest
    - Extends FormRequest; validates student_number, term, and fields.* keys per Types section.

- Modified classes
  - App\Http\Controllers\Api\V1\UnityController
    - Add actions registration() and updateRegistration()

  - App\Services\RegistrationService
    - Add query and update helpers as listed

- Removed classes
  - None


[Dependencies]
No new external dependencies. Reuse existing:
- SystemLogService for auditing
- UserContextResolver for actor resolution
- Existing Program/Curriculum/ (optionally TuitionYear) controllers/services for dropdown data


[Testing]
Manual and API testing to validate correctness.

Test plan:
- Backend
  - GET /v1/unity/registration with:
    - Existing student_number+term → returns exists:true and row
    - Non-existing student_number+term → returns exists:false
  - PUT /v1/unity/registration:
    - Without existing row → 404 Not Found (message: "Registration not found")
    - With existing row, valid fields → 200, success:true, updated:1, registration reflects changes
    - Invalid fields (e.g., enumStudentType invalid) → 422 validation errors
    - Program/curriculum IDs invalid → 422 validation errors
    - Audit log written (verify in SystemLog export or DB)

- Frontend
  - Enlistment page
    - Select student + term with existing registration → "Registration Details" panel appears with prefilled values; Save enabled when changes made.
    - Select student + term without registration → show notice; Save disabled
    - Modify each field and Save → success toast; data persists on reload (switch student and back)
    - Ensure enlistment Year Level (vm.yearLevel) still works independently of editing the registration Year Level; ensure labels clarify context.

Edge cases:
- API returns failure or network error → toast error displayed
- Changing term triggers refresh and hides/shows panel appropriately
- Unknown columns in some DB variants: Service whitelists to prevent SQL errors


[Implementation Order]
Implement backend endpoints first, then frontend integration and UI, followed by verification.

1) Backend validation and service:
   - Create UnityRegistrationUpdateRequest with rules (Types)
   - Extend RegistrationService with findByStudentNumberAndTerm() and updateByStudentNumberAndTerm()

2) Backend controller and routes:
   - Add GET /v1/unity/registration (registrar,admin) → UnityController::registration
   - Add PUT /v1/unity/registration (registrar,admin) → UnityController::updateRegistration

3) Frontend service:
   - Add UnityService.getRegistration(student_number, term)
   - Add UnityService.updateRegistration(payload)

4) Frontend controller:
   - Add vm.registration, vm.regForm, vm.programs, vm.curricula, vm.loadRegistration(), vm.loadPrograms(), vm.loadCurricula(), vm.saveRegistration()
   - Wire loadRegistration on onStudentSelected() and termChanged listener
   - Handle Save button state (disabled until dirty)

5) Frontend template:
   - Add "Registration Details" panel beneath "Current Enlisted"
   - Inputs: year level, student type, program dropdown, curriculum dropdown, tuition year (numeric or dropdown), payment type, LOA remarks, withdrawal period
   - Show read-only notice when no registration exists

6) Testing:
   - Exercise happy and error paths; confirm audit logs; verify no creation happens from this UI
