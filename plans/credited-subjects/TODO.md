# Credited Subjects TODO

Progress tracker for implementing credited subjects per plans/credited-subjects/implementation_plan.md

task_progress Items:
- [x] Step 1: Create migration to add equivalent_subject, term_taken, school_taken on tb_mas_classlist_student (and consider intClassListID nullability)
- [x] Step 2: Extend AcademicRecordService to recognize credited passes (direct and via tb_mas_equivalents)
- [x] Step 3: Update StudentChecklistService to treat credits as passes
- [x] Step 4: Exclude credits in TuitionService/ClasslistSlotsService/DataFetcherService computations
- [x] Step 5: Implement CreditedSubjectsService + CreditedSubjectsController + CreditSubjectStoreRequest with role protection and SystemLog
- [x] Step 6: Add API routes in routes/api.php
- [x] Step 7: Build frontend registrar page (service/controller/html), wire route, add sidebar link
- [ ] Step 8: Add tests and/or scripts to validate credited behavior, and perform manual E2E verification

Verification summary:
- Step 2: AcademicRecordService::hasStudentPassedSubject includes credited subjects and tb_mas_equivalents (laravel-api/app/Services/AcademicRecordService.php)
- Step 3: StudentChecklistService::computePassedMap merges credited passes and equivalents (laravel-api/app/Services/StudentChecklistService.php)
- Step 4: TuitionService, ClasslistSlotsService, DataFetcherService filter cls.is_credited_subject = 0 (laravel-api/app/Services/{TuitionService,ClasslistSlotsService,DataFetcherService}.php)
- Step 5: CreditedSubjectsService, CreditedSubjectsController, CreditSubjectStoreRequest implemented with SystemLog on create/delete (laravel-api/app/Services/CreditedSubjectsService.php, app/Http/Controllers/Api/V1/CreditedSubjectsController.php, app/Http/Requests/Api/V1/CreditSubjectStoreRequest.php)
- Step 6: Routes registered with middleware role:registrar,admin (laravel-api/routes/api.php)
- Step 7: Frontend registrar UI implemented with route and sidebar link (frontend/unity-spa/features/registrar/credit-subjects/*, core/routes.js, shared/components/sidebar/sidebar.html)

Details:

- Step 1 (DB Migration)
  - File: laravel-api/database/migrations/2025_09_07_000600_add_equivalent_and_taken_fields_to_tb_mas_classlist_student.php
  - Adds: equivalent_subject (unsigned int, indexed, nullable), term_taken (varchar 100, nullable), school_taken (varchar 255, nullable)
  - Note: No hard FK to preserve legacy compatibility.

- Step 2 (AcademicRecordService)
  - Modify hasStudentPassedSubject to:
    - Pass if credited row exists where equivalent_subject == subjectId
    - Pass if credited row exists where equivalent_subject is equivalent to subjectId via tb_mas_equivalents
    - Preserve existing pass conditions (final grade <= 3.0, remarks contain pass/credit)
  - Add helpers:
    - hasCreditedPass(int $studentId, int $subjectId): bool
    - getEquivalentSubjectIds(int $subjectId): array<int>

- Step 3 (StudentChecklistService)
  - computePassedMap: merge in credited passes (direct + equivalents) so curriculum items get marked passed

- Step 4 (Service Exclusions)
  - TuitionService: filter cls.is_credited_subject = 0
  - ClasslistSlotsService: filter cls.is_credited_subject = 0 for enlist/enroll counts
  - DataFetcherService: where applicable, exclude credits from enrolled/enlisted listings

- Step 5 (API + Logging)
  - New service/controller/request:
    - CreditedSubjectsService (list/create/delete + SystemLog)
    - CreditedSubjectsController (GET/POST/DELETE)
    - CreditSubjectStoreRequest (validations)
  - Enforce role:registrar,admin

- Step 6 (Routes)
  - /api/v1/students/{student_number}/credits [GET, POST]
  - /api/v1/students/{student_number}/credits/{id} [DELETE]

- Step 7 (Frontend Registrar UI)
  - features/registrar/credit-subjects/{html,controller.js,service.js}
  - Wire in core/routes.js and sidebar link (Registrar/Admin roles)

- Step 8 (Testing - Critical Path per user)
  - Backend:
    - POST credits: create one, verify SystemLog, prevent duplicates
    - GET credits: returns the created credit with subject and metadata
    - DELETE credits: removes credit, logs delete
    - Prereq checks: credits satisfy checkPrerequisites and batch
    - Checklist: generate and verify passed item for credited subject
    - Exclusions: verify tuition/slots ignore credited rows
  - Frontend:
    - Load page, search/select student, add/list/delete credit
    - Visibility and route guards for registrar/admin

Notes:
- Apply SystemLog to data changes for credited subjects (create/delete).
- Credits must not affect tuition, slots, or enrollment metrics.
