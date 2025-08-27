# Implementation Plan

[Overview]
Implement a reset registration operation exposed via POST /api/v1/unity/reset-registration that, for a given student and term, deletes all tb_mas_classlist_student rows for that student in the term and then deletes the tb_mas_registration row(s) for that student/term, wrapped in a single DB transaction, with a single summary system log entry and a frontend confirmation modal that prompts the acting user for their password prior to invoking the API.

This feature complements the existing Unity enlistment workflow (add/drop/change_section) already implemented in UnityController and EnlistmentService. It is intended to quickly revert a student's enlistment for the current/active term (or a specified term) back to a clean state by purging enlisted classlist rows and the corresponding registration record. The backend will not gate this action on registration state or finance transactions; instead, a role-protected API and a client-side confirmation modal (password prompt) will reduce accidental usage. The backend will perform a single summary log for auditability.

[Types]  
Add one request DTO for strong payload validation.

Detailed type specs:
- App\Http\Requests\Api\V1\UnityResetRegistrationRequest
  - authorizes: any authenticated caller; route protected by role:registrar,admin
  - rules:
    - student_number: required|string
    - term: sometimes|integer (if omitted, defaults to active term determined by tb_mas_sy the same way GenericApiController::activeTerm does)
    - password: sometimes|string (collected by modal; not enforced by backend per requirement; may be logged as null and must never be persisted)
- Result JSON shape (success or failure):
  - success: bool
  - message: string
  - data?: {
      student_id: int,
      student_number: string,
      term: int,
      deleted: {
        classlist_student_rows: int,
        registrations: int
      }
    }

Data relationships referenced:
- tb_mas_registration: PK intRegistrationID; columns used: intStudentID, intAYID (term), dteRegistered, intROG, enumStudentType, intYearLevel
- tb_mas_classlist_student: PK intCSID; columns used: intStudentID, intClassListID, intsyID (term), enumStatus, strUnits
- tb_mas_classlist: join validation is not required for deletion; filtering by cls.intsyID == term suffices

[Files]
Add one new request class; update existing controller, service, and routes; add frontend UI confirmation and service method.

Detailed breakdown:
- New files to be created:
  - laravel-api/app/Http/Requests/Api/V1/UnityResetRegistrationRequest.php
    - Purpose: Validate incoming payload for reset-registration.

- Existing backend files to be modified:
  - laravel-api/routes/api.php
    - Add: POST /api/v1/unity/reset-registration → UnityController@resetRegistration
    - Middleware: role:registrar,admin
  - laravel-api/app/Http/Controllers/Api/V1/UnityController.php
    - Add: public function resetRegistration(UnityResetRegistrationRequest $request): JsonResponse
      - Resolve student by student_number; resolve/derive term; call EnlistmentService::resetRegistration(...)
      - Return structured JSON with counts and message
  - laravel-api/app/Services/EnlistmentService.php
    - Add: protected function resetRegistration(int $studentId, int $term, \Illuminate\Http\Request $request): array
      - Implementation details in Functions section below

- Existing frontend files to be modified (Unity SPA):
  - frontend/unity-spa/features/registrar/enlistment/enlistment.html
    - Add a "Reset Registration" button for the current student/term context
    - Hook opens a password confirmation modal
  - frontend/unity-spa/features/registrar/enlistment/enlistment.controller.js
    - Add controller method to launch modal, collect password, and call the service
    - On success: refresh enlisted snapshot/list; show toast with summary
  - frontend/unity-spa/features/unity/unity.service.js
    - Add service method resetRegistration(payload) → POST /api/v1/unity/reset-registration

- Files to be deleted or moved:
  - None

- Configuration file updates:
  - None

[Functions]
Add one public controller method and one service method; add frontend functions for UI action and API call.

Detailed breakdown:
- New backend functions:
  - App\Http\Controllers\Api\V1\UnityController::resetRegistration(UnityResetRegistrationRequest $request): JsonResponse
    - Signature: resetRegistration(UnityResetRegistrationRequest $request): JsonResponse
    - Purpose:
      - Validate payload
      - Resolve active term if not provided: pick the most recent term using same ordering as GenericApiController::activeTerm (strYearStart desc, enumSem asc) and return 422 if none
      - Resolve student by strStudentNumber; 404 if not found
      - Invoke EnlistmentService::resetRegistration($studentId, $term, $request)
      - Return JSON with success flag, message, and counts
  - App\Services\EnlistmentService::resetRegistration(int $studentId, int $term, Request $request): array
    - Purpose:
      - Atomically delete all tb_mas_classlist_student rows where intStudentID = $studentId AND intsyID = $term
      - Delete tb_mas_registration row(s) where intStudentID = $studentId AND intAYID = $term
      - Produce counts of deleted rows for both tables
      - Single summary log via SystemLogService::log('delete', 'RegistrationReset', null, null, [
          'student_id' => $studentId,
          'term' => $term,
          'deleted' => ['classlist_student_rows' => X, 'registrations' => Y],
          'actor_id' => resolved via UserContextResolver in SystemLogService,
        ], $request)
      - Return ['success' => true, 'message' => 'Reset completed', 'data' => ...]
      - On exception: rollback and return success=false with error message

- Modified backend functions:
  - None of existing signatures; only additions

- New frontend functions:
  - unity.service.js: resetRegistration({ student_number, term?, password? })
    - POST /api/v1/unity/reset-registration with JSON payload
    - Returns the API JSON result
  - enlistment.controller.js:
    - openResetModal(): opens a modal with password input; on confirm, calls doReset()
    - doReset(): reads current student_number and term (from view model or route params); calls unityService.resetRegistration; on success, refreshes enlisted view and shows counts; on error, shows error alert

[Classes]
Add one FormRequest class and extend existing controller/service.

Detailed breakdown:
- New classes:
  - App\Http\Requests\Api\V1\UnityResetRegistrationRequest
    - Extends: Illuminate\Foundation\Http\FormRequest
    - authorize(): returns true (route-level role middleware already restricts)
    - rules(): [
        'student_number' => 'required|string',
        'term' => 'sometimes|integer',
        'password' => 'sometimes|string' // captured by UI; not enforced by backend
      ]
- Modified classes:
  - App\Http\Controllers\Api\V1\UnityController: add resetRegistration method
  - App\Services\EnlistmentService: add resetRegistration method
- Removed classes:
  - None

[Dependencies]
No new dependencies.

Details of integration:
- Reuse Illuminate\Support\Facades\DB for transaction and deletes
- Reuse SystemLogService for audit logging
- Reuse existing UserContextResolver via SystemLogService to stamp actor_id (no additional wiring needed)
- Route protected with existing role middleware: role:registrar,admin

[Testing]
Add backend feature tests and manual UI verification.

Test coverage:
- Backend (Feature):
  - Reset with explicit term:
    - Seed: user with student_number S, create tb_mas_registration (S, term T), multiple tb_mas_classlist_student rows for (S, intsyID = T)
    - POST /api/v1/unity/reset-registration with { student_number: S, term: T }
    - Expect 200, success=true, deleted counts match seeded rows
    - Verify DB: no tb_mas_classlist_student rows remain for (S, T); no tb_mas_registration rows remain for (S, T)
    - Verify one SystemLog created with entity='RegistrationReset'
  - Reset with omitted term:
    - Ensure an active term exists in tb_mas_sy
    - Omit term in payload; expect defaulting to active term and successful deletion
  - Student not found:
    - Expect 404 and success=false
  - Active term missing (when term omitted and no tb_mas_sy rows):
    - Expect 422 and clear message
  - Transaction rollback:
    - Simulate exception (e.g., by DB::shouldReceive in tests) and assert no partial deletion committed

- Frontend (Manual/Integration):
  - Enlistment page shows "Reset Registration" button
  - Clicking opens password modal; entering any password allows proceeding
  - After confirm, API is called and, on success, enlisted list is cleared/updated; toast shows counts
  - Confirm that users without registrar/admin role cannot see action or receive 403/401 on direct call

[Implementation Order]
Implement backend API first, then frontend UI and service integration, followed by tests.

1. Backend: Create App\Http\Requests\Api\V1\UnityResetRegistrationRequest with rules for student_number, term?, password?.
2. Backend: Update routes in laravel-api/routes/api.php to add:
   - Route::post('/unity/reset-registration', [UnityController::class, 'resetRegistration'])->middleware('role:registrar,admin');
3. Backend: Add method UnityController::resetRegistration(UnityResetRegistrationRequest $request):
   - Resolve student (tb_mas_users by strStudentNumber), resolve term (or derive active term), handle 404/422, delegate to EnlistmentService::resetRegistration, return response.
4. Backend: Add method EnlistmentService::resetRegistration(int $studentId, int $term, Request $request):
   - DB::beginTransaction();
   - $clsDeleted = DB::table('tb_mas_classlist_student')->where('intStudentID', $studentId)->where('intsyID', $term)->delete();
   - $regDeleted = DB::table('tb_mas_registration')->where('intStudentID', $studentId)->where('intAYID', $term)->delete();
   - SystemLogService::log('delete', 'RegistrationReset', null, null, ['student_id' => $studentId, 'term' => $term, 'deleted' => ['classlist_student_rows' => $clsDeleted, 'registrations' => $regDeleted]], $request);
   - DB::commit(); return success payload.
   - On Throwable: DB::rollBack(); return error payload.
5. Frontend: unity.service.js — add resetRegistration(payload) that POSTs to /api/v1/unity/reset-registration.
6. Frontend: enlistment.html — add a "Reset Registration" button and a modal (password input, confirm/cancel).
7. Frontend: enlistment.controller.js — wire openResetModal() and doReset(); call unity.service.js; handle success/error and refresh enlisted display from backend snapshot (existing enlistment APIs).
8. QA: Manual verification using sample data; verify system logs.
9. Add/adjust PHPUnit feature tests for the backend endpoint; optionally add minimal e2e coverage later.
