# Implementation Plan

[Overview]
Create a new Registrar-facing Shifting page that allows changing a student’s Program and Curriculum for a selected term by updating the registration’s current_program and current_curriculum, with curriculum options filtered by the selected program.

This implementation adds a dedicated Registrar page called "Shifting" under the SPA. The page lets users search/select a student, use the global term selector for the effective term, pick a Program, then choose a Curriculum whose tb_mas_curriculum.intProgramID equals the selected Program’s id. Saving will call the existing UnityController updateRegistration endpoint to persist current_program and current_curriculum for that student and term. The page also displays current registration values to aid verification and aligns with existing backend capabilities (no backend changes required). The Enlistment feature already contains similar registration editing UI; this new Shifting page extracts and streamlines that workflow into a focused tool.

[Types]
No new server-side types; on the frontend define/consume clear data shapes for Program, Curriculum, and the Unity update payload.

Type definitions and shapes:
- Program (from ProgramsService.list):
  - intProgramID: number
  - strProgramCode: string
  - strProgramDescription: string
  - Optional adjunct fields (enabled flags, campus references) may be present and should be tolerated.

- Curriculum (from CurriculaService.list with program_id filter):
  - intID: number
  - strName: string
  - intProgramID: number (must equal the selected Program id)
  - campus_id?: number
  - active?: 0|1
  - isEnhanced?: 0|1

- Registration (from UnityService.getRegistration/getRegistrationById):
  - intRegistrationID: number
  - intStudentID: number
  - intAYID: number
  - intYearLevel?: number
  - enumStudentType?: string
  - current_program?: number
  - current_curriculum?: number
  - program_code?: string
  - program_description?: string
  - curriculum_name?: string
  - tuition_year?: number
  - paymentType?: string
  - enrollment_status?: string
  - date_enlisted?: string|null
  - withdrawal_period?: string|number
  - loa_remarks?: string

- Update payload (UnityRegistrationUpdateRequest via UnityService.updateRegistration):
  - {
      student_number: string,
      term: number,
      fields: {
        current_program?: number,
        current_curriculum?: number
      }
    }
Validation:
- Program is required.
- Curriculum is required and must belong to the selected program (client enforces, server trusts).
- Student and Term are required to scope the registration row to update.

[Files]
Add a new SPA page, route, and menu entry; reuse existing services; update index.html script includes.

New files to be created:
- frontend/unity-spa/features/registrar/shifting/shifting.controller.js
  - AngularJS controller (ShiftingController) to manage student search, term context, program/curriculum selection, and save.
- frontend/unity-spa/features/registrar/shifting/shifting.html
  - HTML template for the Shifting UI (student selection, program dropdown, curriculum dropdown, current registration peek, and Save).

Existing files to be modified:
- frontend/unity-spa/core/routes.js
  - Register a new route:
    - when('/registrar/shifting', { templateUrl: 'features/registrar/shifting/shifting.html', controller: 'ShiftingController', controllerAs: 'vm' })
- frontend/unity-spa/index.html
  - Append script tags:
    - features/registrar/shifting/shifting.controller.js
- frontend/unity-spa/shared/components/sidebar/sidebar.controller.js
  - Add Registrar menu item:
    - { label: 'Shifting', path: '/registrar/shifting' }

Files to be deleted or moved:
- None.

Configuration file updates:
- None (no new dependencies or build config changes).

[Functions]
Introduce a new AngularJS controller with isolated responsibilities and reuse existing services.

New functions (ShiftingController in features/registrar/shifting/shifting.controller.js):
- activate(): Initialize term context via TermService, preload programs, wire initial state.
- loadPrograms(): Fetch full programs list (ProgramsService.list or CurriculaService.getPrograms) and normalize for labels.
- onProgramChange(): When program changes, clear curriculum selection and loadCurricula(selectedProgramId).
- loadCurricula(programId: number): Fetch curricula filtered by program_id (CurriculaService.list({ program_id })).
- loadStudents(): Optionally prefetch students (paged) to seed autocomplete; reuse patterns from EnlistmentController.
- onStudentQuery(q: string): Remote search for autocomplete suggestions (StudentsService.listSuggestions).
- onStudentSelected(): Resolve student id by exact student_number, fetch registration (UnityService.getRegistrationById or getRegistration), and populate current values.
- resolveStudentIdIfNeeded(): Same strategy as EnlistmentController; use local list or GET /students with student_number.
- loadRegistration(): Pull registration for student+term (UnityService.getRegistrationById/getRegistration) and store current values for read-only display.
- canSave(): Return true if student, term, program, and curriculum selections are valid.
- saveShift(): Compose and call UnityService.updateRegistration with fields { current_program, current_curriculum }; show success/failure alerts; refresh registration view.
- ui helpers for label rendering (programLabel, curriculumLabel) and simple read-only registration summary.

Modified functions:
- sidebar.controller.js: Extend Registrar group list (no behavioral change).
- routes.js: Add route config entry only.

Removed functions:
- None.

[Classes]
No new classes; AngularJS controller pattern only.

New classes:
- N/A.

Modified classes:
- N/A (AngularJS controllers are functions).

Removed classes:
- N/A.

[Dependencies]
No new NPM/PHP dependencies; reuse existing Angular services and Laravel endpoints.

Details:
- Frontend services reused:
  - ProgramsService (list programs)
  - CurriculaService (list curricula with program_id)
  - UnityService (getRegistration, getRegistrationById, updateRegistration)
  - StudentsService (autocomplete)
  - TermService (global term context)
- Backend endpoints already present (no changes):
  - GET /api/v1/programs
  - GET /api/v1/curriculum?program_id=...
  - GET /api/v1/unity/registration (by id or student_number)
  - PUT /api/v1/unity/registration (update fields)

[Testing]
Manual validation and integration-level checks in the SPA, covering state, filters, and update flow.

Test plan:
- Access control: Ensure menu "Shifting" shows for users with registrar/admin roles; path renders template.
- Student selection:
  - Autocomplete returns results; selecting student populates selected name.
  - Term is taken from global TermService; changing term triggers reload of registration.
- Program/Curriculum:
  - Programs dropdown lists all; selection triggers curricula fetch.
  - Curricula dropdown lists only rows where intProgramID equals selected program id.
- Load registration:
  - With valid student and term, registration data appears; current values (program/curriculum) are visible.
- Save update:
  - Save is enabled only with student+term+program+curriculum set.
  - On save success, a confirmation toast/alert displays; registration reloads; current values reflect the change.
  - On backend error (e.g., registration not found), show error alert; no state change occurs.
- Regression:
  - Verify existing Enlistment and other registrar pages unaffected.
  - Optionally verify tuition preview from Enlistment reflects changed program (out of scope to wire directly here, but changing program/curriculum should impact subsequent computations in Enlistment).

Artifacts:
- No unit tests required in this project; rely on end-to-end manual checks and backend logs.

[Implementation Order]
Implement in a safe sequence to minimize integration issues.

1) Route registration:
   - Modify frontend/unity-spa/core/routes.js to register when('/registrar/shifting', ...).
2) Controller + Template:
   - Create features/registrar/shifting/shifting.controller.js with ShiftingController and its methods.
   - Create features/registrar/shifting/shifting.html with UI for student selection, program dropdown, curriculum dropdown, current registration summary, and Save button.
3) Script wiring:
   - Append script tag in frontend/unity-spa/index.html to load shifting.controller.js.
4) Sidebar:
   - Add Registrar menu item "Shifting" in frontend/unity-spa/shared/components/sidebar/sidebar.controller.js.
5) Smoke test:
   - Navigate to #!/registrar/shifting, select student and term, confirm program/curriculum filtering, save, and verify registration updated via reloading.
6) Polish and validation:
   - Ensure disabled states and error messages are present; confirm no console errors; confirm role-based visibility.
