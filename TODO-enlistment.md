# TODO — Enlistment Feature (Registrar Add/Drop/Change Section)

Plan reference: implementation_plan_enlistment.md

Progress Checklist:
- [x] Step 1: Create Laravel request validator App\Http\Requests\Api\V1\UnityEnlistRequest (student_number, term, year_level, student_type, operations with add/drop/change_section)
- [x] Step 2: Create App\Services\EnlistmentService with:
  - enlist(payload, request)
  - upsertRegistration(studentId, term, meta)
  - getSubjectUnitsByClasslist(classlistId)
  - system logging integration
- [x] Step 3: Implement UnityController::enlist to use the service and return structured results; ensure route uses role:registrar,admin
- [x] Step 4: Add AngularJS unity.service.js with enlist(payload) to POST /api/v1/unity/enlist
- [x] Step 5: Add Registrar Enlistment screen (features/registrar/enlistment/enlistment.html + enlistment.controller.js) and update routes to include /registrar/enlistment (requiredRoles: registrar, admin)
- [ ] Step 6: Critical-path testing:
  - API: add, drop, change_section; registration upsert idempotency; minimal error cases
  - UI: basic smoke (load student, select term, add/drop/change, submit and refresh)
- [ ] Step 7: Address issues found in critical-path testing and finalize

Notes:
- Defaults per agreement:
  - tb_mas_registration: upsert for (student, term) with intROG=0, dteRegistered=now(), enumStudentType default 'continuing', intYearLevel provided; handle non-null fields (loa_remarks='', withdrawal_period=0) if present.
  - tb_mas_classlist_student on add: enumStatus='act', strRemarks='', intsyID=term, strUnits from subject if available, enlisted_user=acting registrar id if available, grades untouched.
- Scope includes drops and section changes.
- All mutations must write audit entries via SystemLogService.
