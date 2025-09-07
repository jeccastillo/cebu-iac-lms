# Implementation Plan

[Overview]
Add end-to-end support for credited subjects for transferee students by storing credits as rows in tb_mas_classlist_student using an equivalent_subject reference to tb_mas_subjects (instead of linking to a classlist), capturing term_taken and school_taken metadata, integrating credits into prerequisite/checklist pass logic, excluding them from tuition/slots/enrollment metrics, and providing a Registrar/Admin UI to credit, list, and delete entries with full audit logging.

Credited subjects will be represented as non-classlist class records in tb_mas_classlist_student with is_credited_subject=1 and equivalent_subject set to the credited subject’s intID. These rows are treated as passed when evaluating prerequisites and student checklists, but they are excluded from tuition computation, section slot counts, and enrollment statistics. A new registrar page will enable staff to search a student and add credit entries by subject lookup (with term_taken, school_taken, and optional remarks), as well as view and delete credited entries. All mutations will be logged via SystemLogService. Backend APIs enforce role protection (registrar, admin) and validation.

[Types]  
Extend table tb_mas_classlist_student to carry credited metadata and link credited rows directly to subjects via equivalent_subject.

- Database: tb_mas_classlist_student (existing)
  - New columns:
    - equivalent_subject: int unsigned, nullable, FK target tb_mas_subjects.intID (soft-FK with index; actual FK may be skipped if legacy schema constraints exist)
      - Validation: nullable|integer|exists:tb_mas_subjects,intID
      - Semantics: when is_credited_subject=1, equivalent_subject holds the credited subject ID; intClassListID is not used.
    - term_taken: varchar(100), nullable
      - Validation: nullable|string|max:100
    - school_taken: varchar(255), nullable
      - Validation: nullable|string|max:255
  - Existing relevant columns:
    - is_credited_subject: tinyint(1) NOT NULL DEFAULT 0 (already added)
    - credited_subject_name: varchar(255) NULL DEFAULT NULL (already added)
    - intClassListID: int; for credited rows, this is ignored (may be null if schema allows). If not nullable in schema, set to 0 as fallback (implementation will prefer making it nullable if feasible).

- Data Contracts (API payloads)
  - POST /api/v1/students/{student_number}/credits
    - Body: { subject_id: int, term_taken?: string, school_taken?: string, remarks?: string }
    - Behavior: create one credited row in tb_mas_classlist_student with is_credited_subject=1, equivalent_subject=subject_id, strRemarks default &#34;credited&#34; (or provided).
  - DELETE /api/v1/students/{student_number}/credits/{id}
    - Behavior: delete the credited row by intCSID (only if is_credited_subject=1).
  - GET /api/v1/students/{student_number}/credits
    - Returns credited rows with subject code/description and metadata (term_taken, school_taken, remarks).

- Relationships and Rules
  - Prerequisite pass evaluation:
    - A subject S is considered passed if:
      1) student has a classlist record with numeric pass or passing remarks (existing), OR
      2) student has a credited row where equivalent_subject == S, OR
      3) student has a credited row where equivalent_subject IN equivalents-of(S) using tb_mas_equivalents (either direction where appropriate).
  - Checklist pass evaluation adopts the same logic as prerequisites (credits count as pass).
  - Exclusions for credits: credited rows must be excluded from:
    - TuitionService subject list and fee computation
    - ClasslistSlotsService capacity/occupancy counts
    - Enrollment/registration metrics and reporting
    - DataFetcherService or any generic listing that implies enrolled subjects

[Files]
Touch backend services, requests, controllers, routes, migrations, and add a new frontend feature for the registrar.

- New files to be created
  - laravel-api/database/migrations/2025_09_07_000600_add_equivalent_and_taken_fields_to_tb_mas_classlist_student.php
    - Purpose: Add equivalent_subject, term_taken, school_taken columns (with indexes where applicable), and adjust intClassListID nullability if feasible.
  - laravel-api/app/Services/CreditedSubjectsService.php
    - Purpose: Encapsulate list/create/delete for credited subjects, enforce constraints, and call SystemLogService.
  - laravel-api/app/Http/Requests/Api/V1/CreditSubjectStoreRequest.php
    - Purpose: Validate POST body for creating a credited entry.
  - laravel-api/app/Http/Controllers/Api/V1/CreditedSubjectsController.php
    - Purpose: REST-like controller for listing/creating/deleting credited subjects for a student_number; role-protected.
  - frontend/unity-spa/features/registrar/credit-subjects/credit-subjects.service.js
    - Purpose: Angular service to call new APIs.
  - frontend/unity-spa/features/registrar/credit-subjects/credit-subjects.controller.js
    - Purpose: Controller for the registrar credit subjects page.
  - frontend/unity-spa/features/registrar/credit-subjects/credit-subjects.html
    - Purpose: Page markup for search student, list credits, and add/delete entries.

- Existing files to be modified
  - laravel-api/app/Services/AcademicRecordService.php
    - Add credited-subject pass checks (equivalent_subject == S or included in tb_mas_equivalents).
  - laravel-api/app/Services/PrerequisiteService.php
    - No structural change required; ensure pass resolution via AcademicRecordService includes credits.
  - laravel-api/app/Services/StudentChecklistService.php
    - Include credited rows as passing records in completed/prereq satisfaction logic for checklist generation.
  - laravel-api/app/Services/TuitionService.php
    - Exclude credited rows (is_credited_subject=1) from tuition subject aggregation.
  - laravel-api/app/Services/ClasslistSlotsService.php
    - Exclude credited rows from enlisted/enrolled counts to avoid consuming capacity.
  - laravel-api/app/Services/DataFetcherService.php (if it lists current subjects)
    - Exclude credited rows wherever an &#34;enrolled/enlisted&#34; semantic is implied.
  - laravel-api/app/Http/Controllers/Api/V1/SubjectController.php (no change expected)
    - Ensure equivalents logic is used by service; no direct change unless shared helper is needed.
  - laravel-api/app/Models/ClasslistStudent.php
    - Optional: add $casts for any new flags if necessary; ensure guarded/fillable allows new columns.
  - laravel-api/routes/api.php
    - Register new credited subjects routes protected by middleware role:registrar,admin.
  - frontend/unity-spa/core/routes.js
    - Add new route/state for registrar credit subjects page.
  - frontend/unity-spa/shared/components/sidebar/sidebar.html
    - Add menu link for Registrar/Admin roles.

- Files to be deleted or moved
  - None.

- Configuration file updates
  - None.

[Functions]
Add new service/controller methods and integrate credits into pass-check logic.

- New functions
  - App\Services\CreditedSubjectsService
    - list(string $studentNumber): array
      - Resolve student by student_number and return credited entries joined with tb_mas_subjects (code, description).
    - create(string $studentNumber, int $subjectId, ?string $termTaken, ?string $schoolTaken, ?string $remarks, Request $request): array
      - Validate non-duplication for same subject credit; insert tb_mas_classlist_student row with:
        - is_credited_subject=1
        - equivalent_subject=subjectId
        - intStudentID=resolved student id
        - strRemarks=(remarks || &#39;credited&#39;), intsyID may be null for credits
        - term_taken, school_taken, credited_subject_name optional
      - SystemLogService::log(&#39;create&#39;, &#39;ClasslistStudent&#39;, id, null, newData, $request)
    - delete(string $studentNumber, int $creditId, Request $request): bool
      - Validate row belongs to student and is_credited_subject=1; delete; SystemLogService::log(&#39;delete&#39;, &#39;ClasslistStudent&#39;, id, oldData, null, $request)
    - subjectEquivalentsFor(int $subjectId): array<int>
      - Utility: returns list of equivalent subject IDs from tb_mas_equivalents.

  - App\Http\Controllers\Api\V1\CreditedSubjectsController
    - index(string $student_number): JsonResponse
    - store(CreditSubjectStoreRequest $request, string $student_number): JsonResponse
    - destroy(string $student_number, int $id): JsonResponse

- Modified functions
  - App\Services\AcademicRecordService::hasStudentPassedSubject(int $studentId, int $subjectId): bool
    - Extend logic:
      - If any credited row exists with equivalent_subject == subjectId → pass
      - Else if any credited row’s equivalent_subject is listed in tb_mas_equivalents for subjectId → pass
      - Else fallback to existing grade/remarks-based logic via classlist-joined records
    - Add helper:
      - hasCreditedPass(int $studentId, int $subjectId): bool
      - getEquivalentSubjectIds(int $subjectId): array<int>
  - App\Services\StudentChecklistService (specific functions depend on file contents)
    - Where completed/passed subjects are determined, merge credited pass subjects with academic pass subjects.
  - App\Services\TuitionService::get/compute (select queries)
    - Add where cls.is_credited_subject = 0 filter to all aggregations.
  - App\Services\ClasslistSlotsService (subEnlisted/subEnrolled queries)
    - Add where cls.is_credited_subject = 0 to exclude credited rows from capacity counts.
  - App\Services\DataFetcherService (where it surfaces enrolled subjects lists)
    - Add where cls.is_credited_subject = 0 if the view is intended for active enrollment.

- Removed functions
  - None.

[Classes]
Introduce a focused service/controller pair; augment existing services.

- New classes
  - App\Services\CreditedSubjectsService
    - Key methods: list, create, delete, subjectEquivalentsFor
    - Dependencies: DB, SystemLogService
  - App\Http\Requests\Api\V1\CreditSubjectStoreRequest
    - Validates subject_id, term_taken, school_taken, remarks
  - App\Http\Controllers\Api\V1\CreditedSubjectsController
    - Injects CreditedSubjectsService, RequireRole via middleware

- Modified classes
  - App\Services\AcademicRecordService
    - Add credited pass logic and helpers
  - App\Services\StudentChecklistService
    - Include credits in pass determination
  - App\Services\TuitionService
  - App\Services\ClasslistSlotsService
  - App\Services\DataFetcherService (where relevant)
  - App\Models\ClasslistStudent (ensure properties for new columns if needed)

- Removed classes
  - None.

[Dependencies]
No external packages are required; all changes leverage existing Laravel stack and current schema tables (tb_mas_subjects, tb_mas_equivalents, tb_mas_classlist_student).

- Integration details:
  - Route protection using existing middleware: &#39;role:registrar,admin&#39;
  - SystemLogService used for all create/delete operations on credited rows
  - Subject equivalents via tb_mas_equivalents (already in use by SubjectController)

[Testing]
Validate backend logic for prerequisites and checklist behavior with credits, and ensure UI flows work end-to-end for Registrar/Admin.

- Backend
  - Unit/feature tests:
    - AcademicRecordService::hasStudentPassedSubject with:
      - Pure grade pass (no credits)
      - Pure credit pass (equivalent_subject==S)
      - Credit pass via equivalents (tb_mas_equivalents)
      - Non-pass scenarios (no rows, failing grades, unrelated credits)
    - CreditedSubjectsController:
      - store success/failure (duplicates, invalid subject_id)
      - index returns credited list with metadata
      - destroy only removes credited rows
    - TuitionService/ClasslistSlotsService/DataFetcherService filters ignore credits
  - Manual scripts (optional):
    - scripts/test_credited_subjects.php to seed a credited row and verify behaviors.

- Frontend
  - Credit Subjects page:
    - Search/select student → view existing credits
    - Add credit → appears in list; verified by GET response
    - Delete credit → removed from list
    - Role gating: only Registrar/Admin can access route
  - Regression:
    - Enlistment flow remains unchanged
    - Prerequisite warnings reflect credited passes (no false negatives)

[Implementation Order]
Start with schema and backend pass logic, then APIs, then UI, then exclusions, followed by integration testing.

1) Migration
   - Add columns equivalent_subject (indexed), term_taken, school_taken to tb_mas_classlist_student
   - If feasible, make intClassListID nullable to better support non-classlist credits; otherwise accept 0 as sentinel (but service will not depend on classlist link for credits)

2) Backend pass logic
   - Update AcademicRecordService to include credited-pass checks (direct and equivalents)
   - Verify PrerequisiteService (no structural changes) picks up credits through AcademicRecordService

3) Checklist integration
   - Update StudentChecklistService to count credited subjects as passed for checklist display/logic

4) Exclusions in services
   - Update TuitionService, ClasslistSlotsService, and DataFetcherService to filter out is_credited_subject=1 for subject counts/tuition/occupancy/enrollment

5) API surface for Registrar/Admin
   - New CreditedSubjectsService and CreditedSubjectsController
   - New CreditSubjectStoreRequest
   - Routes under /api/v1/students/{student_number}/credits (GET/POST/DELETE) with middleware role:registrar,admin
   - Apply SystemLogService on create/delete

6) Frontend registrar UI
   - Add service/controller/html under features/registrar/credit-subjects
   - Wire routes (frontend/unity-spa/core/routes.js) and sidebar entry (Registrar-only visibility)

7) Manual and automated tests
   - Validate prerequisite checks with credits present (including equivalents)
   - Validate checklist logic
   - Validate tuition/slots/enrollment unaffected by credits
   - UI flow verification for Registrar/Admin

8) Documentation and deployment checklist
   - Ensure migration applied
   - Provide brief README snippet for Registrar usage and API contracts

task_progress Items:
- [ ] Step 1: Create migration to add equivalent_subject, term_taken, school_taken on tb_mas_classlist_student (and consider intClassListID nullability)
- [ ] Step 2: Extend AcademicRecordService to recognize credited passes (direct and via tb_mas_equivalents)
- [ ] Step 3: Update StudentChecklistService to treat credits as passes
- [ ] Step 4: Exclude credits in TuitionService/ClasslistSlotsService/DataFetcherService computations
- [ ] Step 5: Implement CreditedSubjectsService + CreditedSubjectsController + CreditSubjectStoreRequest with role protection and SystemLog
- [ ] Step 6: Add API routes in routes/api.php
- [ ] Step 7: Build frontend registrar page (service/controller/html), wire route, add sidebar link
- [ ] Step 8: Add tests and/or scripts to validate credited behavior, and perform manual E2E verification
