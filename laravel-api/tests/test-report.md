# Test Report — Laravel API v1

Metadata
- Date/Time: YYYY-MM-DD HH:mm
- Tester: Name/Initials
- Environment: Local Dev / Staging
- PHP: 8.1+
- Web Server: XAMPP Apache
- DB: MySQL version, schema snapshot/version
- BASE URL: http://localhost/laravel-api/public/api/v1
- Token: Bearer <redacted>

Scope and Methodology
- Scope: Thorough testing for migrated modules (Users, Programs, Portal, Subjects, TuitionYear). Future modules (Student, Registrar, Finance, Scholarship, Unity) to be covered post-implementation.
- Method: cURL-based manual tests following laravel-api/tests/TEST_PLAN.md, validating both happy and error/edge cases. Parity checks vs legacy CodeIgniter endpoints where applicable. CORS/JSON validity checks. Security and performance sampling.

Result Legend
- [ ] Not Run
- [x] Pass
- [!] Fail
- [~] Partial/Observation

Environment Checks
- [ ] PHP error_reporting/logs configured
- [ ] DB connectivity to existing schema
- [ ] Sanctum configured and tokens issued
- [ ] CORS configured for SPA origins

Module: Users (UsersController)
Endpoints
- POST /users/auth
- POST /users/auth-student
- POST /users/register
- POST /users/forgot
- POST /users/password-reset
- POST /users/logout

Happy Path
- [ ] Auth returns token + user meta (faculty/admin/student)
- [ ] Student auth returns token + student meta
- [ ] Logout invalidates token

Error/Edge
- [ ] Invalid credentials → 401/422 envelope
- [ ] Missing fields → 422 with errors
- [ ] Logout without token → 401

Notes
- Observations:
- Evidence (response snippets):

Module: Programs (ProgramController)
Endpoints
- GET /programs

Happy Path
- [ ] Returns list of active programs with expected fields

Error/Edge
- [ ] Filters (if any) validated
- [ ] Pagination (if any) validated

Notes
- Observations:
- Evidence:

Module: Portal (PortalController)
Endpoints
- POST /portal/save-token
- GET /portal/active-programs
- POST /portal/student-data

Happy Path
- [ ] save-token persists token/email mapping
- [ ] active-programs returns enabled programs
- [ ] student-data returns correct student by token

Error/Edge
- [ ] Invalid token → error envelope
- [ ] Missing fields → 422

Notes
- Observations:
- Evidence:

Module: Subjects (SubjectController)
Endpoints (read)
- GET /subjects
- GET /subjects/by-curriculum?curriculum_id=...
- GET /subjects/{id}

Endpoints (write)
- POST /subjects/submit
- POST /subjects/edit
- POST /subjects/submit-eq
- POST /subjects/submit-days
- POST /subjects/submit-room
- POST /subjects/submit-prereq
- POST /subjects/delete-prereq
- POST /subjects/delete

Happy Path
- [ ] List/By curriculum/Show return expected structures
- [ ] submit creates subject
- [ ] edit updates subject
- [ ] submit-eq adds equivalent
- [ ] submit-days/submit-room persist properly
- [ ] submit-prereq/deletes work
- [ ] delete removes subject

Error/Edge
- [ ] Invalid/missing IDs → 404/422
- [ ] Idempotency for prereqs/equivalents/days/rooms
- [ ] Transaction rollback on partial failures
- [ ] Foreign key validation

Evidence (run scripts)
- [ ] php laravel-api/scripts/test_subject_write.php
- [ ] php laravel-api/scripts/verify_subject_writes.php
- Observations:

Module: TuitionYear (TuitionYearController)
Endpoints (read)
- GET /tuition-years
- GET /tuition-years/{id}
- GET /tuition-years/{id}/misc
- GET /tuition-years/{id}/lab-fees
- GET /tuition-years/{id}/tracks
- GET /tuition-years/{id}/programs
- GET /tuition-years/{id}/electives

Endpoints (write)
- POST /tuition-years/add
- POST /tuition-years/finalize
- POST /tuition-years/submit-extra
- POST /tuition-years/delete-type
- POST /tuition-years/delete
- POST /tuition-years/duplicate

Happy Path
- [ ] Index/Show return expected shape
- [ ] add creates a new tuition year configuration
- [ ] finalize marks as finalized
- [ ] submit-extra adds misc/lab/track/program
- [ ] delete-type removes the specified item
- [ ] delete removes tuition year
- [ ] duplicate clones to target SY/Semester

Error/Edge
- [ ] Invalid/missing IDs → 404/422
- [ ] Duplicate to existing target → error envelope
- [ ] Transaction rollback on partial updates

Evidence (run scripts)
- [ ] php laravel-api/scripts/test_tuition_year_write.php
- [ ] php laravel-api/scripts/test_tuition_year_extra.php
- Observations:

Module: Curriculum (CurriculumController)
Endpoints
- GET /curriculum
- GET /curriculum/{id}
- GET /curriculum/{id}/subjects
- POST /curriculum
- PUT /curriculum/{id}
- DELETE /curriculum/{id}
- POST /curriculum/{id}/subjects
- DELETE /curriculum/{id}/subjects/{subjectId}

Happy Path
- [x] Index returns list with intID, strName, intProgramID, active, isEnhanced
- [x] Show returns expected record
- [x] subjects returns linked subjects
- [x] create returns newid
- [x] update persists changes
- [x] add subject links record
- [x] remove subject unlinks record
- [x] delete removes when no associations

Error/Edge
- [x] duplicate association → 422
- [x] delete with existing associations → 422
- [ ] invalid/missing IDs → 404/422
- [ ] validation errors on upsert → 422

Notes
- Observations:
- Evidence:

Parity With CodeIgniter
- [ ] Select representative endpoints to compare (subjects show, programs list, portal student-data)
- [ ] Compare JSON structure/value subsets
- Differences noted (intentional vs defects):

Security and Authorization
- [ ] Unauthenticated calls to protected endpoints → 401/403
- [ ] Unauthorized role checks (if enabled) → 403
- [ ] Rate limiting (if enabled) returns 429 with headers

CORS and JSON Validity
- [ ] OPTIONS preflight returns expected Access-Control-Allow-* headers
- [ ] All responses are valid JSON and correct status codes (200/201/4xx/5xx as applicable)

Performance Sampling
- [ ] Paginated endpoints return limited payloads
- [ ] Heavy endpoints measured under 10/25/50 quick loops
- [ ] Slow queries identified and indexed if needed

Defects and Observations
- ID | Area | Severity | Summary | Steps | Expected | Actual | Evidence
- 1 | Subjects | High | Missing defaults on create → RESOLVED | POST /subjects/submit minimal payload | 201 created with id | 500 QueryException (pre-fix) → now 201 with newid | tests/out/20250823-080706-httpw-summary.json
- 2 | TuitionYear | High | Missing defaults on add → RESOLVED | POST /tuition-years/add minimal payload | 201 created with id | 500 QueryException (pre-fix) → now 201 with newid | tests/out/20250823-080706-httpw-summary.json

Action Items
- Fixes required before next testing pass:
- Performance/index recommendations:
- Security/policy adjustments:

Conclusion
- Overall status: Green / Yellow / Red
- Next steps:
  - Re-run smoke after fixes
- Extend coverage to new modules as implemented (Student, Registrar, Finance, Scholarship, Unity)

Module: Registrar (RegistrarController)
Endpoints
- POST /registrar/daily-enrollment
- GET /registrar/grading/meta
- GET /registrar/grading/sections
- POST /registrar/grading/results
- GET /registrar/classlist/{id}/submitted

Happy Path
- [x] grading/meta valid dept returns terms/faculty
- [ ] grading/sections returns sections/subjects
- [ ] grading/results returns results
- [ ] classlist/{id}/submitted returns students/classlist for valid id

Error/Edge
- [x] grading/meta invalid dept → 422
- [x] daily-enrollment missing fields → 422
- [x] classlist/{id}/submitted unknown id → 404

Notes
- Observations:
- Evidence:

Module: Finance (FinanceController)
Endpoints
- GET /finance/transactions
- GET /finance/or-lookup

Happy Path
- [x] transactions no params returns envelope
- [ ] transactions filtered by student_number/syid returns envelope
- [ ] or-lookup valid OR returns breakdown

Error/Edge
- [x] or-lookup missing param → 422
- [x] or-lookup non-existent → 404

Notes
- Observations:
- Evidence:

---

Run Summary — 2025-08-22 23:53 PH (Local Dev)
- Environment: XAMPP Apache, PHP 8.1.6 (system), Laravel API base http://localhost/iacademy/cebu-iac-lms/laravel-api/public/api/v1
- Scope executed:
  - Smoke (read-only): Health, Programs, Subjects, Subjects by Curriculum, Tuition Years, Portal Active Programs → PASS (exitCode 0)
  - HTTP write tests (curl/Invoke-WebRequest via http-write.ps1): minimal create/add flows for Subjects/TuitionYear → PASS (see evidence)
  - Comprehensive suite with writes (run-suite.ps1 -RunWrites): executed PHP write scripts for Subjects and TuitionYear; suite summaries saved

Results
- Smoke: PASS
- HTTP writes (minimal regression):
  - subject-create: PASS 200/201
    - Evidence (summary): tests/out/20250822-234329-httpw-summary.json
    - Note: Script log “Could not resolve created subject ID” due to response parsing; follow-ups were skipped by the script, but overall summary = PASS. Controller now returns envelope with ‘newid’.
  - ty-add: PASS 200/201
    - Evidence (summary): tests/out/20250822-234329-httpw-summary.json
- Suite Summaries (writes enabled):
  - tests/out/20250822-235245-suite-summary.json
  - tests/out/20250822-235345-suite-summary.json

Defects and Observations (Updated)
- ID 1 | Subjects | High | Missing defaults on create → RESOLVED by SubjectSubmitRequest defaults/validation
  - Evidence: http write summary PASS; no 500 observed on create after remediation
  - Follow-up: Update http-write.ps1 to parse ‘newid’ from response envelope to enable chained subject steps
- ID 2 | TuitionYear | High | Missing defaults on add → RESOLVED by TuitionYearAddRequest defaults/validation
  - Evidence: http write summary PASS; no 500 observed on add after remediation

Action Items (Before Next Thorough Pass)
- Tests
  - Update tests/scripts/http-write.ps1 to parse ‘newid’ from the JSON envelope for chained flows
  - Extend coverage to edge/error/rollback/idempotency for:
    - Subjects: edit, submit-eq, submit-days, submit-room, submit-prereq, delete-prereq, delete
    - TuitionYear: delete/duplicate/finalize/submit-extra/delete-type
  - Security: sample unauthenticated/unauthorized and optional rate limiting
  - Performance: pagination/limits on heavy endpoints; capture timings
- Artifacts
  - Update this report (sections above) with concrete Pass/Fail checkboxes as cases are executed
  - Log any new defects with evidence paths

Next Steps
- Proceed with thorough testing across Subjects and TuitionYear (edge/rollback/idempotency) and update this report iteratively
- In parallel, continue implementation for Curriculum and Student per TODO roadmap while maintaining smoke/regression sanity

---

Run Summary — 2025-08-23 08:44 PH (Local Dev)
- Environment: Laravel serve at http://127.0.0.1:8000/api/v1, PHP CLI 8.2 (C:\xampp8\php\php.exe)
- Scope executed:
  - Smoke (read-only): PASS (exitCode 0)
  - Write tests (PHP scripts): Subjects and TuitionYear → PASS
- Evidence:
  - Suite summary: tests/out/20250823-084029-suite-summary.json
  - Latest run: tests/out/20250823-084433-suite-summary.json

Run Summary — 2025-08-23 22:45 PH (Local Dev)
- Environment: XAMPP Apache, PHP 8.1.x
- Base: http://localhost/iacademy/cebu-iac-lms/laravel-api/public/api/v1
- Scope executed:
  - Smoke: PASS (6/6)
  - HTTP writes: PASS (8/8)
  - Full run-suite with writes: PASS
- Evidence:
  - Smoke summary: laravel-api/tests/out/20250823-224325-summary.json
  - HTTP writes summary: laravel-api/tests/out/20250823-224340-httpw-summary.json
  - Suite summary: laravel-api/tests/out/20250823-224511-suite-summary.json
- Notes:
  - No regressions observed in minimal create flows.
  - Recommendation: improve PowerShell scripts to robustly parse "newid" from response envelopes for chained steps.
