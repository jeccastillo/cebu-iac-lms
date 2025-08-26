# Laravel API Thorough Test Plan

Scope
- Thorough test coverage for Laravel API v1 running in parallel with the existing CodeIgniter app.
- Validate correctness, parity with legacy endpoints, error handling, security, and performance for all migrated modules.
- Current focus modules: Users, Programs, Portal, Subjects, TuitionYear. Future modules: Student, Registrar, Finance, Scholarship, Unity.

Environment and Conventions
- Base URL (adjust to your local): http://localhost/laravel-api/public/api/v1
- Authentication: Sanctum tokens where endpoints are protected. Unprotected endpoints must still return JSON envelopes with correct status codes.
- Use JSON payloads and Content-Type: application/json for POST requests.
- Use consistent response envelope:
  - Success: { "success": true, "data": ..., "meta"?: ... }
  - Error: { "success": false, "message": "...", "code": "...", "errors"?: {...} }
- Variables used in examples:
  - BASE=http://localhost/laravel-api/public/api/v1
  - TOKEN=your_sanctum_token
  - SN=student_number
  - PID=program_id
  - SID=subject_id
  - TYID=tuition_year_id
  - CLID=classlist_id

Prerequisites
- Database seeded or pointing to an existing schema that contains real data.
- Optional seeders/scripts:
  - php laravel-api/scripts/seed_test_data.php
  - php laravel-api/scripts/test_subject_write.php
  - php laravel-api/scripts/verify_subject_writes.php
  - php laravel-api/scripts/test_tuition_year_write.php
  - php laravel-api/scripts/test_tuition_year_extra.php

Authentication (UsersController)
Endpoints
- POST /users/auth
- POST /users/auth-student
- POST /users/register
- POST /users/forgot
- POST /users/password-reset
- POST /users/logout

cURL Examples
1) Authenticate (faculty/admin/student by loginType field if applicable):
curl -s -X POST "%BASE%/users/auth" -H "Content-Type: application/json" -d "{\"strUser\":\"USERNAME\",\"strPass\":\"PASSWORD\",\"loginType\":\"faculty\"}"

2) Authenticate student:
curl -s -X POST "%BASE%/users/auth-student" -H "Content-Type: application/json" -d "{\"strUser\":\"USERNAME\",\"strPass\":\"PASSWORD\"}"

3) Logout:
curl -s -X POST "%BASE%/users/logout" -H "Authorization: Bearer %TOKEN%"

Validation
- Happy path returns success with token and user meta.
- Invalid credentials return proper error envelope and 401/422.
- Logout invalidates token.

Programs (ProgramController)
Endpoint
- GET /programs

cURL
curl -s "%BASE%/programs"

Validation
- List of active programs by default.
- Optional filtering (enabledOnly) if implemented.

Portal (PortalController)
Endpoints
- POST /portal/save-token
- GET /portal/active-programs
- POST /portal/student-data

cURL
1) Save token (maps to tb_mas_users.strGSuiteEmail or relevant field):
curl -s -X POST "%BASE%/portal/save-token" -H "Content-Type: application/json" -d "{\"email\":\"user@example.com\",\"token\":\"ABC123\"}"

2) Active programs:
curl -s "%BASE%/portal/active-programs"

3) Student data by token:
curl -s -X POST "%BASE%/portal/student-data" -H "Content-Type: application/json" -d "{\"token\":\"ABC123\"}"

Validation
- Ensure success envelopes and proper filtering.
- Invalid token returns error envelope.

Subjects (SubjectController)
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

Representative cURL (payload shape depends on schema; adapt fields to existing DB)
1) List:
curl -s "%BASE%/subjects"

2) By curriculum:
curl -s "%BASE%/subjects/by-curriculum?curriculum_id=1"

3) Show:
curl -s "%BASE%/subjects/123"

4) Create:
curl -s -X POST "%BASE%/subjects/submit" -H "Content-Type: application/json" -d "{\"code\":\"SUBJ101\",\"title\":\"Intro\",\"units\":3,\"program_id\":1}"

5) Edit:
curl -s -X POST "%BASE%/subjects/edit" -H "Content-Type: application/json" -d "{\"id\":123,\"title\":\"Intro Updated\",\"units\":3}"

6) Equivalents:
curl -s -X POST "%BASE%/subjects/submit-eq" -H "Content-Type: application/json" -d "{\"subject_id\":123,\"equivalent_subject_id\":456}"

7) Days:
curl -s -X POST "%BASE%/subjects/submit-days" -H "Content-Type: application/json" -d "{\"subject_id\":123,\"days\":[\"M\",\"W\",\"F\"]}"

8) Room:
curl -s -X POST "%BASE%/subjects/submit-room" -H "Content-Type: application/json" -d "{\"subject_id\":123,\"room\":\"A101\"}"

9) Prereq add:
curl -s -X POST "%BASE%/subjects/submit-prereq" -H "Content-Type: application/json" -d "{\"subject_id\":123,\"prereq_subject_id\":789}"

10) Prereq delete:
curl -s -X POST "%BASE%/subjects/delete-prereq" -H "Content-Type: application/json" -d "{\"subject_id\":123,\"prereq_subject_id\":789}"

11) Delete:
curl -s -X POST "%BASE%/subjects/delete" -H "Content-Type: application/json" -d "{\"id\":123}"

Validation
- DB state changes verified via laravel-api/scripts/test_subject_write.php and verify_subject_writes.php.
- Idempotency: double-submit of same prereq should not duplicate; repeats should be safe or properly error.
- FK checks: invalid IDs should return clean 422/404 envelopes.
- Transactions/rollback: partial failures must not leave inconsistent state.

Tuition Year (TuitionYearController)
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

cURL (examples)
1) Index:
curl -s "%BASE%/tuition-years"

2) Show:
curl -s "%BASE%/tuition-years/%TYID%"

3) Add:
curl -s -X POST "%BASE%/tuition-years/add" -H "Content-Type: application/json" -d "{\"sy\":\"2024-2025\",\"semester\":\"1st\",\"class_type\":\"regular\",\"program_id\":1}"

4) Finalize:
curl -s -X POST "%BASE%/tuition-years/finalize" -H "Content-Type: application/json" -d "{\"id\":%TYID%}"

5) Submit extra:
curl -s -X POST "%BASE%/tuition-years/submit-extra" -H "Content-Type: application/json" -d "{\"id\":%TYID%, \"type\":\"misc\",\"items\":[{\"code\":\"REG\",\"amount\":1000.00}]}"

6) Delete type:
curl -s -X POST "%BASE%/tuition-years/delete-type" -H "Content-Type: application/json" -d "{\"id\":%TYID%, \"type\":\"misc\",\"code\":\"REG\"}"

7) Delete:
curl -s -X POST "%BASE%/tuition-years/delete" -H "Content-Type: application/json" -d "{\"id\":%TYID%}"

8) Duplicate:
curl -s -X POST "%BASE%/tuition-years/duplicate" -H "Content-Type: application/json" -d "{\"id\":%TYID%, \"target_sy\":\"2025-2026\",\"target_semester\":\"1st\"}"

Validation
- Ensure read endpoints reflect extra items (misc/lab-fees/tracks/programs/electives).
- Finalize/duplicate correctness; duplicate should copy related rows.
- Rollback when partial insert/update fails.

Security and Authorization
- For protected endpoints, calls without Authorization: Bearer %TOKEN% must return 401/403 consistently.
- For role-based checks (if enabled), ensure faculty/student/admin receive proper access controls.
- Rate limiting (if enabled) returns correct headers and 429 on abuse.

CORS and JSON Validity
- Verify OPTIONS preflight for endpoints used by SPA with expected Access-Control-Allow-* headers.
- All responses should be valid JSON (parseable) with correct status codes.

Parity With CodeIgniter
- Identify the legacy CI endpoint for each Laravel endpoint.
- Use parity comparison to ensure structure/values are equivalent where intended (fields may differ if standardized).
- Example PowerShell parity script (adjust endpoints and params):
$legacy = Invoke-WebRequest -Uri "http://localhost/legacy/api/subjects?id=123" -UseBasicParsing
$new    = Invoke-WebRequest -Uri "$env:BASE/subjects/123" -UseBasicParsing
# If jq is installed:
# Compare selected fields after ConvertFrom-Json and jq normalization.

Performance Testing
- For heavy endpoints, paginate and validate response times under load.
- If wrk/ab not available, measure simple loops:
Measure-Command { 1..50 | ForEach-Object { curl.exe -s "$env:BASE/subjects" > $null } }
- Validate indexes and slow queries via DB logs where possible.

Error and Edge Cases Checklist
Subjects
- Invalid subject_id, duplicate prereq, missing required fields
- Idempotent re-submission
- Deleting non-existent records
- Days/room payloads malformed

TuitionYear
- Invalid tuition_year id
- Duplicate with existing target SY/Semester
- Adding extra items with invalid codes/types
- Finalize twice

Users/Portal/Programs
- Invalid credentials
- Missing/invalid token for protected requests
- Input validation errors

Regression Checklist (After Changes)
- Re-run smoke tests for Users, Programs, Portal
- Re-run subject write scripts:
  - php laravel-api/scripts/test_subject_write.php
  - php laravel-api/scripts/verify_subject_writes.php
- Re-run tuition scripts:
  - php laravel-api/scripts/test_tuition_year_write.php
  - php laravel-api/scripts/test_tuition_year_extra.php

Reporting
- Create laravel-api/tests/test-report.md (manual) summarizing:
  - Date/time, environment
  - Endpoints tested, scenarios, pass/fail
  - Defects/observations and screenshots if applicable
  - Performance snapshots and recommendations

Execution Checklist
- [ ] Set BASE and TOKEN environment variables
- [ ] Run smoke: Users/Programs/Portal
- [ ] Run Subjects read + write (happy paths)
- [ ] Run Subjects error/edge/idempotency
- [ ] Run TuitionYear read + write (happy paths)
- [ ] Run TuitionYear error/edge/rollback
- [ ] Verify CORS and JSON validity (OPTIONS + parse)
- [ ] Parity checks vs CI for migrated endpoints
- [ ] Security (401/403, role access if enabled)
- [ ] Performance sampling on heavy endpoints
- [ ] Record results in test-report.md

Future Modules (to be covered when implemented)
Student
- Viewer, balances, records, ledger (happy/error/edge)
Registrar
- Classlist operations, grading results, daily enrollment
Finance
- Transactions (list, upsert), OR lookup, negative/zero amounts, method variants
Scholarship
- CRUD, status transitions, eligibility, effective periods
Unity
- Advising, enlistment, tag status, tuition preview (integration with TuitionService)

Notes
- Prefer realistic payloads aligned with existing schema.
- Keep DB snapshots/backups when testing writes.
- Use transactions/rollback in dev where practical to maintain clean test state.

Scholarship (baseline)
Endpoints
- GET /scholarships
- GET /scholarships/assigned?syid=...&student_id=... or student_number=...
- GET /scholarships/enrolled?syid=...&q=...
- POST /scholarships/upsert (currently returns 501 Not Implemented)
- DELETE /scholarships/{id} (currently returns 501 Not Implemented)

cURL
1) Index:
curl -s "%BASE%/scholarships"

2) Assigned (missing student param should 422):
curl -s "%BASE%/scholarships/assigned?syid=1"

3) Enrolled (missing syid should 422):
curl -s "%BASE%/scholarships/enrolled"

Validation
- Endpoints return standardized envelopes.
- Upsert/Delete return 501 with { "success": false, "message": "Not Implemented" } for now.
- Expand feature tests and implement real write logic in future iteration once parity is validated.
