# Implementation Plan

[Overview]
Automatically set the Student Type dropdown to "Shiftee" on the Registrar Enlistment page when the selected student has a recorded shifting event for the currently selected term. The auto-selection should trigger once upon student selection for the student+term combination and must not override subsequent manual edits by the user.

This feature bridges the Shifting workflow with Enlistment UX to reduce errors and manual toggling. The backend already logs shifting events into tb_mas_shifting via POST /api/v1/students/{id}/shift; this plan adds a minimal read endpoint to detect whether a shifting record exists for a student in the currently selected term. The frontend Enlistment controller will query this endpoint after resolving student_id and term, and set vm.studentType = 'shiftee' if a shifting record exists. In alignment with the requirement, it will not update the Registration Details panel (regForm.enumStudentType) and will never override manual Student Type changes after the initial auto-apply.

[Types]  
No persisted type system changes; introduce a small UI-state flag.

Detailed structures:
- Backend response (new):
  - GET /api/v1/students/{id}/shifted?term={id}
  - Response body:
    - { success: boolean, data: { shifted: boolean, term_id: number|null, latest_at?: string|null } }
  - shifted: true when a row exists in tb_mas_shifting where student_id = {id} and term_id = {term}.
  - latest_at: optional timestamp of the latest matching row (ISO 8601); useful for future UI hints.
- Frontend UI state:
  - vm._autoStudentTypeApplied: object map keyed by "studentId|termId" → true
    - Prevents re-applying auto selection and ensures no override of manual user choice.

[Files]
Introduce one backend read endpoint and minimally modify the Enlistment frontend controller; no template change required.

Detailed breakdown:
- New files to be created
  - None

- Existing files to be modified
  - laravel-api/routes/api.php
    - Add route: GET /api/v1/students/{id}/shifted → StudentController@shifted (role: registrar, admin)
  - laravel-api/app/Http/Controllers/Api/V1/StudentController.php
    - Add method public function shifted(Request $request, int $id): JsonResponse
      - Validates student exists.
      - Resolves term_id from query param term|term_id|syid or X-Term-ID header.
      - Checks tb_mas_shifting existence for (student_id = $id AND term_id = $termId).
      - Returns { success: true, data: { shifted: bool, term_id: int|null, latest_at: string|null } }.
  - frontend/unity-spa/features/students/students.service.js
    - Add StudentsService.shifted(studentId, termId): Promise<{shifted:boolean, term_id:number|null, latest_at?:string|null}>
      - Calls GET /api/v1/students/{id}/shifted?term={termId}.
  - frontend/unity-spa/features/registrar/enlistment/enlistment.controller.js
    - Add UI state: vm._autoStudentTypeApplied = {};
    - On student selection (onStudentSelected flow) and on term change (termChanged event), after ensuring student_id and term are resolved:
      - If key "studentId|termId" is not in vm._autoStudentTypeApplied, call StudentsService.shifted(sid, termInt).
      - If response.shifted === true, set vm.studentType = 'shiftee' and mark vm._autoStudentTypeApplied[key] = true.
      - If user later manually changes vm.studentType, code does not interfere (no watchers added).
    - Do NOT touch vm.regForm.enumStudentType per scope: only the top-level Student Type dropdown used by enqueue/submit.

- Files to be deleted or moved
  - None

- Configuration file updates
  - None

[Functions]
Add a backend read method and lightweight frontend helpers.

Detailed breakdown:
- New functions (backend)
  - StudentController::shifted(Request $request, int $id): JsonResponse
    - File: laravel-api/app/Http/Controllers/Api/V1/StudentController.php
    - Purpose: Report whether a shifting record exists for the student in the given term.
    - Behavior:
      - Validate student exists in tb_mas_users.
      - Resolve $termId from query or header: term | term_id | syid; fallback to X-Term-ID.
      - If $termId not numeric/invalid, return shifted=false with term_id=null.
      - If Schema::hasTable('tb_mas_shifting') and $termId valid, query exists() and latest row date_shifted.
      - Return success:true with data.

- Modified functions (frontend)
  - StudentsService: add shifted(studentId:number, termId:number): Promise
    - File: frontend/unity-spa/features/students/students.service.js
    - Purpose: Thin wrapper to call GET /api/v1/students/{id}/shifted.
    - Returns normalized data: { shifted:boolean, term_id:number|null, latest_at:string|null }
  - EnlistmentController.onStudentSelected (enhancement)
    - File: frontend/unity-spa/features/registrar/enlistment/enlistment.controller.js
    - Change: After resolving student_id and loading current/registration, call a new helper maybeAutoSelectShiftee() if term is set.
  - EnlistmentController.activate() termChanged handler (enhancement)
    - Also call maybeAutoSelectShiftee() after vm.term updates and student_id is known, but only if not applied before.
  - New helper: maybeAutoSelectShiftee()
    - Signature: function maybeAutoSelectShiftee() : void
    - Logic:
      - Guard: require vm.student_id (int) and vm.term (int); build key = student_id + '|' + termInt.
      - If vm._autoStudentTypeApplied[key] is true → return (do nothing).
      - Call StudentsService.shifted(vm.student_id, termInt).then(res => { if (res.shifted) vm.studentType = 'shiftee'; vm._autoStudentTypeApplied[key] = true; });
      - Catch errors silently; never block UI.
  - Submit flow remains unchanged (payload uses vm.studentType).

- Removed functions
  - None

[Classes]
No class additions or changes; all procedural additions.

Detailed breakdown:
- New classes
  - None
- Modified classes
  - None
- Removed classes
  - None

[Dependencies]
No new third-party packages.

Details:
- Backend uses existing DB facade and Schema checks.
- Route guarded with role:registrar,admin consistent with POST /students/{id}/shift.
- Frontend uses existing AngularJS app stack and StudentsService.

[Testing]
Manual testing paths for registrar role.

Test file requirements: None. Use existing UI and API client.

Validation strategies:
- Preconditions:
  - Use Shifting page to POST a shift for a student and a specific term (already supported via POST /api/v1/students/{id}/shift). Alternatively seed tb_mas_shifting.
- Scenarios:
  1) Student with shift in selected term:
     - Select student on Enlistment page with the same global term selected.
     - Observe Student Type auto-switch to "Shiftee" once.
     - Change dropdown manually to another type; refresh term or re-open page → auto-selection must NOT override manual change (vm._autoStudentTypeApplied prevents re-apply).
  2) Student without shift:
     - Select student; ensure Student Type remains whatever default (vm.studentType initial 'new' or previous selection).
  3) Term change:
     - With same student, switch to a different term where a shift exists. Auto-select should apply if not previously applied for that student/term.
  4) Registration exists with non-shiftee:
     - Confirm we do NOT change Registration Details dropdown (regForm.enumStudentType). Only top-level Student Type is auto-selected.
  5) Error paths:
     - Missing tb_mas_shifting table or DB error → endpoint returns shifted=false; no auto-select occurs; UI continues normally.

[Implementation Order]
Implement backend read endpoint first, then wire frontend service, finally integrate the enlistment controller logic.

1) Backend route
   - Add GET /api/v1/students/{id}/shifted to laravel-api/routes/api.php with middleware role:registrar,admin.

2) Backend controller
   - Add StudentController::shifted(Request $request, int $id): JsonResponse
   - Resolve termId from query/header, validate student existence, check tb_mas_shifting existence and latest date, return JSON.

3) Frontend service
   - Add StudentsService.shifted(studentId, termId) that calls GET /api/v1/students/{id}/shifted?term=.

4) Frontend controller
   - Add vm._autoStudentTypeApplied = {};
   - Add helper maybeAutoSelectShiftee().
   - Call maybeAutoSelectShiftee() after:
     - onStudentSelected resolves student_id and vm.term is present.
     - termChanged event when both student_id and term are present.
   - Ensure it only runs once per student/term key and never overrides manual changes.

5) QA
   - Verify all scenarios above and ensure no regressions in enlistment submit payload.
