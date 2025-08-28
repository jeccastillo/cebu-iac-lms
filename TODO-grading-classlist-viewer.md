# TODO — Classlist Grading Viewer

Scope: Add classlist viewer for grade entry (midterm/finals) with date-window activation (tb_mas_sy.midterm_start/midterm_end, final_start/final_end), extension overrides (tb_mas_sy_grading_extension + tb_mas_sy_grading_extension_faculty), grading system–driven options (fallback numeric 1–100), and finalize/unfinalize flows (faculty finalize only; registrar/admin finalize and unfinalize). Backend: Laravel API. Frontend: AngularJS Unity SPA.

Derived from implementation_plan.md (approved).

## Checklist

- [ ] Step 1: Backend services
  - [x] Create GradingWindowService with windowInfo() and canEditPeriod()
  - [ ] Extend ClasslistService with getClasslistForGrading(int $classlistId)
- [ ] Step 2: Backend requests
  - [ ] Create ClasslistGradeSaveRequest (period, items[intCSID, grade, remarks?], overwrite_ngs?)
  - [ ] Create ClasslistFinalizeRequest (period, confirm_complete?)
- [ ] Step 3: Backend controller
  - [ ] Create ClasslistGradesController with:
    - [ ] viewerData(int $id)
    - [ ] saveGrades(ClasslistGradeSaveRequest $req, int $id)
    - [ ] finalize(ClasslistFinalizeRequest $req, int $id)
    - [ ] unfinalize(Request $req, int $id) // registrar/admin only
- [ ] Step 4: Routes
  - [ ] Register:
    - [ ] GET /api/v1/classlists/{id}/viewer
    - [ ] POST /api/v1/classlists/{id}/grades
    - [ ] POST /api/v1/classlists/{id}/finalize
    - [ ] POST /api/v1/classlists/{id}/unfinalize
- [ ] Step 5: Authorization gates
  - [ ] Add in AuthServiceProvider:
    - [ ] grade.classlist.edit
    - [ ] grade.classlist.finalize
    - [ ] grade.classlist.unfinalize (registrar/admin only)
- [ ] Step 6: Frontend integration (services + routes)
  - [ ] Add route: /classlists/:id/viewer in frontend/unity-spa/core/routes.js
  - [ ] Extend ClasslistsService with:
    - [ ] getViewer(id)
    - [ ] saveGrades(id, payload)
    - [ ] finalize(id, payload)
    - [ ] unfinalize(id)
- [ ] Step 7: Frontend UI
  - [ ] Create viewer.controller.js (ClasslistViewerController)
  - [ ] Create viewer.html
  - [ ] Add “View Grades” action in classlists/list.html (navigates to viewer)
- [ ] Step 8: Role and window behaviors
  - [ ] Faculty (assigned to classlist):
    - [ ] Can edit and finalize within window or when extension exists
    - [ ] Cannot unfinalize
  - [ ] Registrar/Admin:
    - [ ] Can edit/finalize/unfinalize anytime (bypass windows)
- [ ] Step 9: Critical-path tests (post-implementation)
  - [ ] API:
    - [ ] GET viewer returns options (system or numeric) and permissions properly
    - [ ] POST grades persists values; sets strRemarks from system items; numeric 1–100 accepted; invalid rejected
    - [ ] POST finalize transitions 0→1 (midterm), 1→2 (finals); unfinalize (registrar/admin) 2→1, 1→0
    - [ ] Window enforcement: faculty blocked outside window without extension; allowed with extension; registrar/admin bypass
  - [ ] UI:
    - [ ] Viewer loads, grade inputs render (dropdown or numeric), save works
    - [ ] Buttons visibility per role and intFinalized
    - [ ] “View Grades” link works from classlists list

## Implementation Notes

- Grade options:
  - Use tb_mas_subjects.grading_system_id_midterm for midterm; grading_system_id for finals.
  - If null → numeric fallback input 1..100 (clamp and validate).
- Remarks policy:
  - If system item chosen → persist strRemarks = item.remarks (server-side).
  - If numeric fallback → leave strRemarks unchanged (null or existing).
- Windows:
  - Use tb_mas_sy midterm_start/end and final_start/end.
  - Extension override via the latest tb_mas_sy_grading_extension row by date plus matching row in tb_mas_sy_grading_extension_faculty (classlist_id + grading_extension_id).
  - Registrar/Admin bypass windows.
- Finalization state:
  - tb_mas_classlist.intFinalized: 0 (not submitted), 1 (submitted midterm), 2 (submitted final).

## Files to Touch

Backend:
- app/Services/GradingWindowService.php (done)
- app/Services/ClasslistService.php (+ getClasslistForGrading)
- app/Http/Requests/Api/V1/ClasslistGradeSaveRequest.php
- app/Http/Requests/Api/V1/ClasslistFinalizeRequest.php
- app/Http/Controllers/Api/V1/ClasslistGradesController.php
- app/Providers/AuthServiceProvider.php (add gates)
- routes/api.php (register routes)

Frontend:
- frontend/unity-spa/core/routes.js (add viewer route)
- frontend/unity-spa/features/classlists/classlists.service.js (add API calls)
- frontend/unity-spa/features/classlists/viewer.controller.js (new)
- frontend/unity-spa/features/classlists/viewer.html (new)
- frontend/unity-spa/features/classlists/list.html (add action)

## Testing Level

As confirmed: Critical-path testing only (new viewer page and new endpoints; positive flows for faculty and registrar/admin plus one negative window case; quick check of gates and window service behavior).
