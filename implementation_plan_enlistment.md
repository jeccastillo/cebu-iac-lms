# Implementation Plan

[Overview]
Implement a registrar-driven enlistment workflow where the registrar can add, drop, and change a student’s section per term using tb_mas_classlist_student as the authoritative enrollment detail table and ensuring a corresponding tb_mas_registration record exists for the student and term (intROG=0 enlisted). Expose a Laravel API endpoint to process atomic enlistment operations and add an AngularJS front-end screen for the registrar to manage enlistment.

This implementation introduces a dedicated enlistment service and request validator on the Laravel side to ensure data integrity (no duplicates, correct term alignment, proper defaults) and to upsert a compliant tb_mas_registration row with required fields. The front-end provides a unified workflow where registrar staff can search a student, pick a term, view current enlisted classes, add/drop sections, and submit changes. All mutations should be recorded in system logs for auditability.

[Types]  
New request and DTO-like types for enlistment payload and validation.

Detailed type definitions and data structures:
- Laravel request payload (UnityEnlistRequest)
  - student_number: string (required) — resolves to tb_mas_users.strStudentNumber
  - term: integer (required) — tb_mas_sy.intID (syid)
  - year_level: integer (required) — for tb_mas_registration.intYearLevel
  - student_type: string (optional, default 'continuing') — tb_mas_registration.enumStudentType
  - operations: array (required, min:1)
    - operations.*.type: enum('add','drop','change_section') (required)
    - For type='add':
      - classlist_id: integer (required) — tb_mas_classlist.intID
    - For type='drop':
      - classlist_id: integer (required)
    - For type='change_section':
      - from_classlist_id: integer (required)
      - to_classlist_id: integer (required)
- Internal enlistment operation result (returned per operation)
  - type: 'add'|'drop'|'change_section'
  - ok: boolean
  - message: string|null
  - details:
    - For add: { intCSID, classlist_id }
    - For drop: { deleted: 1 }
    - For change_section: { from_deleted:1, to_intCSID }
- tb_mas_classlist_student field conventions (to be set on add):
  - intStudentID: resolved from student_number
  - intClassListID: from classlist
  - intsyID: equals term (syid)
  - enumStatus: 'act'
  - strRemarks: ''
  - strUnits: copy of subject units (tb_mas_subjects.strUnits) if available; otherwise null
  - enlisted_user: current authenticated faculty/registrar id where available; otherwise null
  - Grades fields (floatPrelimGrade, floatMidtermGrade, floatFinalsGrade, floatFinalGrade): untouched (null/0)
- tb_mas_registration upsert shape
  - intStudentID: user.intID
  - intAYID: term
  - intROG: 0 (enlisted)
  - dteRegistered: now()
  - enumStudentType: student_type or 'continuing'
  - intYearLevel: year_level
  - Fields observed without defaults in logs (provide safe values if columns exist and are non-nullable):
    - loa_remarks: '' (empty string)
    - withdrawal_period: 0

[Files]
New Laravel request and service, controller updates, and new Angular view/service.

Detailed breakdown:
- New files to be created (Laravel):
  - laravel-api/app/Http/Requests/Api/V1/UnityEnlistRequest.php
    - Purpose: Validate enlistment payload, normalize structure, enforce required fields and operation-specific rules.
  - laravel-api/app/Services/EnlistmentService.php
    - Purpose: Encapsulate core enlistment logic (add/drop/change-section), perform DB transactions, upsert tb_mas_registration, enforce duplicates and term alignment, write system logs.
- Existing files to be modified (Laravel):
  - laravel-api/app/Http/Controllers/Api/V1/UnityController.php
    - Implement enlist() using UnityEnlistRequest and EnlistmentService. Return per-operation results and overall summary.
- New files to be created (AngularJS):
  - frontend/unity-spa/features/registrar/enlistment/enlistment.html
    - Purpose: Registrar UI to manage enlistment for a student and term; show current enlisted classes and provide add/drop/change actions.
  - frontend/unity-spa/features/registrar/enlistment/enlistment.controller.js
    - Purpose: Controller orchestrating UI state, loading student profile/records, assembling operations, and invoking API.
  - frontend/unity-spa/features/unity/unity.service.js
    - Purpose: Service wrapper for Unity API (advising, enlist, tag-status, tuition-preview). Expose enlist(payload).
- Existing files to be modified (AngularJS):
  - frontend/unity-spa/core/routes.js
    - Add route: '/registrar/enlistment' with requiredRoles ['registrar', 'admin'] mapping to the new view/controller.
  - Optionally: reuse existing classlists.service.js for classlist query; if additional filters needed (by term/subject), extend service minimally (if needed).
- Files to be deleted or moved:
  - None.
- Configuration file updates:
  - None.

[Functions]
Add enlistment API endpoint logic and supporting service methods; front-end flows to build and submit enlistment operations.

Detailed breakdown:
- New functions (Laravel, in EnlistmentService):
  - EnlistmentService::enlist(array $payload, \Illuminate\Http\Request $request): array
    - Transactionally process operations for the given student and term.
    - Steps:
      1) Resolve student by student_number; 404 if not found.
      2) Upsert tb_mas_registration with intROG=0 and required defaults.
      3) For each operation in order:
         - add: ensure no existing cls row for (student, classlist); validate classlist term match; insert tb_mas_classlist_student with defaults; log via SystemLogService ('unity:enlist:add').
         - drop: ensure an existing cls row exists; delete tb_mas_classlist_student; log ('unity:enlist:drop').
         - change_section: drop from from_classlist_id then add to to_classlist_id atomically; log both ('unity:enlist:change:from','unity:enlist:change:to').
      4) Return results per operation and computed current enlisted after operations.
  - EnlistmentService::upsertRegistration(int $studentId, int $term, array $meta): intRegistrationID
    - Ensure one row exists with intROG=0 for syid; insert if missing else update non-breaking fields (dteRegistered).
  - EnlistmentService::getSubjectUnitsByClasslist(int $classlistId): ?int
    - Join classlist->subjects to fetch units; return null if unavailable.
  - EnlistmentService::log(string $action, string $entity, int|string|null $id, array|null $old, array|null $new, Request $request): void
    - Small wrapper using SystemLogService (reuse existing service).
- Modified functions (Laravel):
  - UnityController::enlist(UnityEnlistRequest $request)
    - Replace placeholder with call to EnlistmentService::enlist(), return structured JSON with success summary and per-operation results.
- New functions (AngularJS):
  - unity.service.js:
    - enlist(payload): POST /api/v1/unity/enlist
    - advising(payload), tuitionPreview(payload), tagStatus(payload) (stubs or pass-through as already present).
  - EnlistmentController:
    - loadStudent(student_number)
    - loadTermOptions()
    - loadCurrentEnlisted(student_number, term)
    - addOperationAdd(classlist)
    - addOperationDrop(classlist)
    - addOperationChange(fromClasslist, toClasslist)
    - submitOperations()

[Classes]
Introduce request and service; update controller.

Detailed breakdown:
- New classes:
  - App\Http\Requests\Api\V1\UnityEnlistRequest (extends FormRequest)
    - Rules:
      - student_number: required|string
      - term: required|integer
      - year_level: required|integer
      - student_type: sometimes|string|in:continuing,new,returnee,transfer
      - operations: required|array|min:1
      - operations.*.type: required|string|in:add,drop,change_section
      - operations.*.classlist_id: required_if:operations.*.type,add|integer
      - operations.*.from_classlist_id: required_if:operations.*.type,change_section|integer
      - operations.*.to_classlist_id: required_if:operations.*.type,change_section|integer
      - operations.*.classlist_id (for drop): required_if:operations.*.type,drop|integer
  - App\Services\EnlistmentService
    - Dependencies: DB, SystemLogService, optional UserContextResolver for current actor ID.
- Modified classes:
  - App\Http\Controllers\Api\V1\UnityController
    - Inject EnlistmentService and implement enlist() method based on validated request.
- Removed classes:
  - None.

[Dependencies]
No new composer packages required.

Integration details:
- Reuse App\Services\SystemLogService to log enlist operations:
  - Action strings: 'unity:enlist:add', 'unity:enlist:drop', 'unity:enlist:change'
  - Entity: 'ClasslistStudent' and/or 'Registration'
  - Old/new payloads: for add, log new row; for drop, log old row; for change, log both sides.
- Reuse CodeIgniterSessionGuard/Auth if available to resolve acting user; fallback to null for enlisted_user when not available.
- Reuse existing ClasslistService for reading metadata as needed on the front end (sections/subjects by term).

[Testing]
End-to-end API tests plus UI smoke tests.

Test file requirements and validation strategies:
- API Feature tests (PHPUnit):
  - POST /api/v1/unity/enlist (add only):
    - Given empty state, operations=[{type:'add',classlist_id:X}], expect one tb_mas_classlist_student row for (student,classlist), intsyID=term, enumStatus='act', strRemarks='', strUnits set; tb_mas_registration upserted with intROG=0 and provided year_level and defaults for loa_remarks and withdrawal_period when columns exist.
  - Duplicate add prevention:
    - Add same classlist twice in a single payload and in consecutive requests; expect second add to be ok:false with a duplicate message.
  - Drop existing:
    - Given a pre-existing cls row, operations=[{type:'drop',classlist_id:X}], expect deletion and log entry.
  - Change section:
    - Given existing from_classlist_id, operations=[{type:'change_section',from_classlist_id: A, to_classlist_id: B}]; expect A removed, B added; atomic if B invalid (rollback).
  - Cross-term guard:
    - Attempt to add a classlist from a different term than payload.term => ok:false with descriptive message, transactional no-op.
  - Registration upsert idempotency:
    - Multiple enlist requests for same student/term do not create duplicate tb_mas_registration rows.
- AngularJS smoke:
  - Route '/registrar/enlistment' renders and shows form controls.
  - unity.service.enlist posts payload and displays per-operation results in UI.

[Implementation Order]
Implement server-side first to stabilize API; then front-end screen and integration; finally confirm with manual tests.

Numbered steps:
1. Create Laravel request: App\Http\Requests\Api\V1\UnityEnlistRequest
   - Implement rules as specified (student_number, term, year_level, student_type, operations[*]).
   - Authorize(): return true (behind role middleware on route).
2. Create Laravel service: App\Services\EnlistmentService
   - enlist(payload, request):
     - Begin DB::transaction()
     - Resolve student by student_number; if not found, throw 404-like exception or return structured error (stop early).
     - Upsert tb_mas_registration with:
       - intStudentID = user.intID, intAYID = term, intROG=0, dteRegistered=now(), enumStudentType = provided or 'continuing', intYearLevel = year_level.
       - Safe defaults: loa_remarks = '' (string), withdrawal_period = 0 (int) if columns exist and are non-nullable (perform try/catch for schema variance).
     - For each operation:
       - add:
         - Verify tb_mas_classlist.intID exists and cl.strAcademicYear == term; if not, mark ok=false.
         - Prevent duplicate: check tb_mas_classlist_student exists for (intStudentID, intClassListID); if exists, ok=false.
         - Determine enlisted_user from auth (UserContextResolver/CodeIgniterSessionGuard) if available.
         - Fetch subject units via join classlist->subject: tb_mas_subjects.strUnits, cast to numeric string if needed.
         - Insert tb_mas_classlist_student row with defaults described above; capture intCSID.
         - Log create ('unity:enlist:add').
       - drop:
         - Verify row exists for (student, classlist); if not found, ok=false.
         - Delete row; log delete ('unity:enlist:drop').
       - change_section:
         - Validate 'from' row exists and 'to' classlist term matches; prevent duplicate with 'to'.
         - Perform delete('from') then insert('to') in same transaction; log both sides ('unity:enlist:change').
     - Commit; return payload with per-operation status and updated enlisted snapshot for the student/term.
3. Update Laravel controller: App\Http\Controllers\Api\V1\UnityController::enlist
   - Inject EnlistmentService, accept UnityEnlistRequest, call service->enlist(), return JSON:
     - { success: true, data: { operations: [...], current: [...] } } or success:false with message.
   - Protect route via middleware role:registrar,admin at routes/api.php (route already present).
4. Front-end additions:
   - Add unity.service.js (AngularJS) under frontend/unity-spa/features/unity/
     - Methods: enlist(payload) => POST /api/v1/unity/enlist; advising, tagStatus, tuitionPreview pass-throughs.
   - Add registrar enlistment screen:
     - enlistment.html: student number + term selector (use existing generic/terms endpoint), year_level control, current enlisted list (loaded from Student records by term endpoint), and controls to add/drop/change.
     - enlistment.controller.js: orchestrates loading and building operations array; calls unity.service.enlist; displays results; refresh current enlisted.
   - Update routes.js: add '/registrar/enlistment' with requiredRoles ['registrar', 'admin'].
5. Logging and auditing:
   - Use SystemLogService in EnlistmentService with action strings and old/new values (when applicable); include request metadata (actor, ip).
6. Manual verification:
   - Use Postman to test add/drop/change flows and confirm tb_mas_registration upsert, tb_mas_classlist_student mutations, logs written.
   - UI smoke: navigate to /#/registrar/enlistment, run a basic operation.
7. Edge cases and guards (deferred if not supported by schema today):
   - Capacity checks (slots) and finalized restrictions (intFinalized) — add as warnings or blockers if confirmed later.
   - Prerequisite validation — out of scope for this pass.
   - Duplicate subject across multiple sections in same term — permitted unless specified otherwise.
