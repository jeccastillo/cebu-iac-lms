# Implementation Plan

[Overview]
Migrate the Student Viewer and all related API consumers from using portal student numbers (tb_mas_users.strStudentNumber) to using primary keys (tb_mas_users.intID). This change standardizes lookups and joins on the canonical user identifier, eliminates ambiguity or collisions with string-based numbers, and aligns all services with legacy schema foreign keys (intStudentID).

Scope includes:
- Student Viewer stack (frontend Angular controller and all backend /api/v1/student/* endpoints).
- Additional consumers currently using student_number per your directive (e.g., Finance transactions, Tuition compute, and any other endpoints/services/scripts passing student_number).
- No backward compatibility: student_number is dropped from request contracts in favor of student_id.

[Types]  
Switch request contracts to student_id:number payloads and query params, maintaining response resource shapes.

Type definitions:
- StudentIdPayload
  - student_id: integer (required)
- StudentRecordsPayload
  - student_id: integer (required)
  - include_grades: boolean (optional; default false)
  - term: string|number (nullable; tb_mas_sy.intID when provided)
- StudentRecordsByTermPayload
  - student_id: integer (required)
  - include_grades: boolean (optional; default false)
  - term: string|number (required; tb_mas_sy.intID)
- StudentLedgerPayload
  - student_id: integer (required)

Validation rules:
- All above payloads must require student_id (int) and must not accept student_number.
- term should be normalized to string for compatibility where current validators enforce string, but service logic will treat it as numeric tb_mas_sy.intID when applicable.
- include_grades normalized to boolean (unchanged behavior).

Relational assumptions:
- tb_mas_users.intID is the canonical user PK.
- Foreign lookups consistently join via intStudentID where applicable.

[Files]
Update both backend and frontend files, and adjust tests/scripts.

- New files to be created (optional):
  - laravel-api/app/Http/Requests/Api/V1/StudentIdRequest.php
    - Purpose: DRY validation for endpoints that accept only student_id. Not strictly required but recommended.

- Existing files to be modified:
  Backend: Laravel API
  - laravel-api/app/Http/Controllers/Api/V1/StudentController.php
    - Change methods using student_number (balances, records, recordsByTerm, ledger) to require student_id and pass intStudentID to services.
  - laravel-api/app/Services/DataFetcherService.php
    - Replace signatures and internals:
      - getStudentBalances(string $studentNumber) → getStudentBalances(int $studentId)
      - getStudentRecords(string $studentNumber, ?string $term, bool $includeGrades) → getStudentRecords(int $studentId, ?string $term, bool $includeGrades)
      - getStudentRecordsByTerm(string $studentNumber, string $term, bool $includeGrades) → getStudentRecordsByTerm(int $studentId, string $term, bool $includeGrades)
      - getStudentLedger(string $studentNumber) → getStudentLedger(int $studentId)
    - Keep getStudentByToken unchanged; optionally deprecate getStudentByNumber if unused after migration.
  - laravel-api/app/Http/Requests/Api/V1/StudentBalanceRequest.php
    - Replace rule: student_number:string → student_id:integer|required
  - laravel-api/app/Http/Requests/Api/V1/StudentRecordsRequest.php
    - Replace rule: student_number:string → student_id:integer|required
    - Retain include_grades and term normalization.
  - Other controllers/services that accept student_number (per code search and tests):
    - Finance transactions endpoint(s): switch query param student_number → student_id and adjust queries (likely in App\Http\Controllers\Api\V1\FinanceController or similar).
    - Tuition compute: /api/v1/tuition/compute should accept student_id (in TuitionController) and Tuitional logic should resolve by id.
    - Any other endpoints in UnityController or ReportsController that accept student_number should be converted if they consume student viewer data.

  Frontend: AngularJS SPA
  - frontend/unity-spa/features/students/viewer.controller.js
    - Stop using vm.sn or ?sn= query parameter.
    - Use vm.id (route param) for all API calls.
    - Update API payloads to { student_id: vm.id } for balances, records, recordsByTerm, ledger.
  - Any SPA services/controllers that pass student_number to updated endpoints:
    - Review finance/student-billing and finance/ledger pages or services and update to student_id where affected endpoints are changed.

- Files to be deleted or moved:
  - None required for initial migration.

- Configuration file updates:
  - None.

[Functions]
Modify function signatures and internals to use student_id.

- New functions (optional):
  - App\Http\Requests\Api\V1\StudentIdRequest
    - rules(): [ 'student_id' => ['required','integer'] ]

- Modified functions:
  - App\Http\Controllers\Api\V1\StudentController:
    - balances(StudentBalanceRequest $request): accept student_id; call $this->fetcher->getStudentBalances($studentId)
    - records(StudentRecordsRequest $request): accept student_id; call $this->fetcher->getStudentRecords($studentId, $term, $includeGrades)
    - recordsByTerm(StudentRecordsRequest $request): accept student_id; call $this->fetcher->getStudentRecordsByTerm($studentId, $term, $includeGrades)
    - ledger(StudentBalanceRequest $request): accept student_id; call $this->fetcher->getStudentLedger($studentId)
  - App\Services\DataFetcherService:
    - getStudentBalances(int $studentId): Query tb_mas_student_ledger l.student_id = $studentId; augment transactions by joining registration r.intStudentID = $studentId.
    - getStudentRecords(int $studentId, ?string $term, bool $includeGrades): Filter by cls.intStudentID = $studentId; remove user lookup by student_number.
    - getStudentRecordsByTerm(int $studentId, string $term, bool $includeGrades): Filter by cls.intStudentID = $studentId; cl.strAcademicYear = $term.
    - getStudentLedger(int $studentId): Join tb_mas_transactions via registration where r.intStudentID = $studentId; drop u.strStudentNumber filter.
  - Finance/Tuition controllers (where applicable):
    - transactions(): accept student_id query and join on r.intStudentID = student_id.
    - tuition compute(): accept student_id and resolve registration/subjects by id.

- Removed functions:
  - None required; optionally deprecate getStudentByNumber after codebase-wide migration.

[Classes]
Update request classes to accept student_id.

- New classes (optional):
  - StudentIdRequest (see above) to reduce duplication of student_id validation rules.

- Modified classes:
  - App\Http\Requests\Api\V1\StudentBalanceRequest: rules → student_id:int|required
  - App\Http\Requests\Api\V1\StudentRecordsRequest: rules → student_id:int|required; retain include_grades, term normalization

- Removed classes:
  - None.

[Dependencies]
No external package or version changes. All changes are internal refactors.

[Testing]
Update feature tests and harness scripts to use student_id.

- Update Laravel Feature tests:
  - laravel-api/tests/Feature/Api/V1/FinanceControllerTest.php: change ?student_number= to ?student_id= and adjust assertions if necessary.
  - Any student tests that posted student_number must now post student_id.

- Update scripts under laravel-api/scripts:
  - test_student_billing.php, test_invoices.php, test_or_first_invoice_rules.php, test_tuition_save.php, debug_tuition_compute.php:
    - Resolve a student row and use intID consistently; call endpoints with student_id instead of student_number.

- Manual regression:
  - Student Viewer page (#/students/viewer/:id) loads balances, records (with and without term filter), and ledger by id.
  - Finance transactions page accepts student_id in filters (if applicable).
  - Tuition compute works with student_id.

Validation strategies:
- Ensure 422 validation when student_number is supplied (since backward compatibility is not required).
- Verify that downstream joins use intStudentID consistently and match expected data.

[Implementation Order]
Implement in a minimal-breakage sequence:

1) Backend request contracts
   - Update StudentBalanceRequest and StudentRecordsRequest to require student_id:int.
   - Optional: add StudentIdRequest for reuse.

2) Backend service layer
   - Refactor DataFetcherService methods to accept and query by student_id.

3) Backend controllers
   - Update StudentController (balances, records, recordsByTerm, ledger) to read student_id and call updated service methods.

4) Backend other consumers
   - Update Finance transactions and Tuition compute controllers to accept student_id and query by id.

5) Frontend Angular SPA
   - Update StudentViewerController to stop using ?sn= and send { student_id } payloads.
   - Update any SPA consumers calling modified endpoints to pass student_id.

6) Tests and scripts
   - Update Feature tests to use student_id.
   - Update scripts under laravel-api/scripts to use student_id.

7) Regression validation
   - Verify Student Viewer and related pages.
   - Confirm no endpoints accept student_number anymore.

8) Cleanup
   - Optionally deprecate getStudentByNumber if no references remain.

task_progress Items:
- [ ] Step 1: Update backend request validators to require student_id and drop student_number.
- [ ] Step 2: Refactor DataFetcherService methods to accept and query by student_id.
- [ ] Step 3: Update StudentController methods to read student_id and call refactored service methods.
- [ ] Step 4: Update FinanceController and TuitionController to accept student_id instead of student_number.
- [ ] Step 5: Update StudentViewerController to stop using ?sn= and send { student_id } to all endpoints.
- [ ] Step 6: Update all related frontend services/pages using student_number to use student_id.
- [ ] Step 7: Update tests and scripts to use student_id; adjust harnesses and queries.
- [ ] Step 8: Regression validation across viewer, finance, tuition endpoints; ensure no student_number acceptance remains.
- [ ] Step 9: Optional cleanup: remove deprecated code paths using student_number.
