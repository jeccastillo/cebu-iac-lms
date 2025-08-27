# TODO — Co-requisites Feature Implementation

This file tracks progress for implementing co-requisites analogous to prerequisites, per implementation_plan_corequisites.md.

Scope:
- New DB table tb_mas_corequisites
- Backend services (AcademicRecordService, CorequisiteService)
- API endpoints in SubjectController + routes
- EnlistmentService integration for concurrent co-req validation
- Minimal frontend service/UI parity (optional per plan)
- Tests and verification scripts
- Thorough testing after implementation

task_progress Items:
- [x] Step 1: Add migration 2025_08_27_000100_create_tb_mas_corequisites.php with schema, indexes, and unique constraint
- [x] Step 2: Create AcademicRecordService and refactor PrerequisiteService to delegate pass checks
- [x] Step 3: Implement CorequisiteService (get, check, batch, for-classlist)
- [x] Step 4: Add corequisite routes to routes/api.php
- [x] Step 5: Add SubjectController methods for corequisites
- [x] Step 6: Integrate corequisite checks into EnlistmentService (pre-scan planned adds, opAdd/opChangeSection)
- [ ] Step 7: Extend Unity SPA subjects.service.js and edit UI to manage corequisites
- [ ] Step 8: Add tests and verification scripts for coreqs; update test plan docs
- [ ] Step 9: Run migrations and smoke test endpoints
- [ ] Step 10: E2E enlistment tests for concurrent co-req satisfaction and error paths

Details:

1) Migration
- File: laravel-api/database/migrations/2025_08_27_000100_create_tb_mas_corequisites.php
- Table: tb_mas_corequisites
  - intID (PK, AI)
  - intSubjectID (int, not null)
  - intCorequisiteID (int, not null)
  - program (varchar(50), nullable, default null)
- Indexes:
  - index on (intSubjectID)
  - index on (intCorequisiteID)
  - unique (intSubjectID, intCorequisiteID, program)

2) AcademicRecordService
- New: app/Services/AcademicRecordService.php
- Methods:
  - hasStudentPassedSubject(int $studentId, int $subjectId): bool
  - isPassingRecord(object $record): bool
- PrerequisiteService will inject and delegate to this service.

3) CorequisiteService
- New: app/Services/CorequisiteService.php
- Methods:
  - checkCorequisites(int $studentId, int $subjectId, ?string $program = null, array $concurrentSubjectIds = []): array
  - batchCheckCorequisites(int $studentId, array $subjectIds, ?string $program = null, array $concurrentSubjectIds = []): array
  - checkCorequisitesForClasslist(int $studentId, int $classlistId, array $plannedClasslistIdsForTerm = []): array
  - getSubjectCorequisites(int $subjectId, ?string $program = null)

4) Routes
- Update laravel-api/routes/api.php:
  - GET /api/v1/subjects/{id}/corequisites
  - POST /api/v1/subjects/{id}/check-corequisites
  - POST /api/v1/subjects/check-corequisites-batch
  - POST /api/v1/subjects/submit-coreq (role:registrar,admin)
  - POST /api/v1/subjects/delete-coreq (role:registrar,admin)

5) SubjectController
- Add methods:
  - corequisites($id)
  - submitCoreq(Request $request)
  - deleteCoreq(Request $request)
  - checkCorequisites($id, Request $request, CorequisiteService $service)
  - checkCorequisitesBatch(Request $request, CorequisiteService $service)

6) EnlistmentService integration
- Inject CorequisiteService in constructor
- Pre-scan enlist payload operations to compute planned adds (classlist_id → subject_id) for the term
- In opAdd() and opChangeSection() call CorequisiteService->checkCorequisitesForClasslist()
- Add helper: getSubjectIdByClasslist(int $classlistId): ?int
- Failure message: "Corequisites not satisfied. Missing: CODE1, CODE2"

7) Frontend Unity SPA (parity)
- Update frontend/unity-spa/features/subjects/subjects.service.js:
  - corequisites(subjectId), submitCoreq(payload), deleteCoreq(payload),
    checkCorequisites(subjectId, payload), checkCorequisitesBatch(payload)
- subjects edit UI list mirroring prerequisites (optional/minimal)

8) Tests / Scripts / Docs
- Add laravel-api/tests/TEST_PLAN_COREQ.md
- Add laravel-api/scripts/test_subject_coreq_write.php
- Add laravel-api/scripts/verify_subject_coreq_writes.php
- Update laravel-api/tests/TEST_PLAN.md and laravel-api/tests/test-report.md

9) Migrations / Smoke Tests
- Run: cd laravel-api &amp;&amp; php artisan migrate
- Smoke-test coreq endpoints with curl

10) E2E Enlistment
- Test success when both sides present in one enlist batch
- Test failure with missing coreqs (message consistency)
- Regression on prerequisites via AcademicRecordService

Notes:
- Program scoping consistent with prerequisites: program filter accepts NULL/'' as global.
- Write endpoints protected by role:registrar,admin.
- Idempotency enforced via DB unique constraint.
