# Implementation Plan

[Overview]
Remove the Campus ID input from the Add Program page and automatically set campus_id to the globally selected campus via CampusService; keep Campus ID editable only on Edit Program.

This change aligns Add Program behavior with the global campus context so users do not choose campus manually when creating programs. The system will derive campus_id from the currently selected campus (restored or defaulted by CampusService). This reduces user error, keeps curricula filtering consistent, and matches the UX pattern used elsewhere in the app for campus-scoped data. The backend validation will continue to accept campus_id as nullable so no backend changes are required.

[Types]  
No new static type system introduced; payload shape and field constraints are clarified for frontend and backend surfaces.

Program payload (frontend to backend):
- strProgramCode: string; required; max 50
- strProgramDescription: string; required; max 255
- strMajor: string|null; optional; max 100
- type: 'college'|'shs'|'drive'|'other'; required
- school: string|null; optional; max 100
- short_name: string|null; optional; max 100
- default_curriculum: number|null; optional; integer; depends on campus_id
- enumEnabled: 0|1; optional; defaults to 1 on backend if omitted
- campus_id: number|null;:
  - Add Program: set automatically from CampusService.getSelectedCampus().id (number) after CampusService.init()
  - Edit Program: displayed as editable numeric field (current behavior)
  - Backend accepts nullable; no change to rules

[Files]
Only frontend files in the Programs feature require minor adjustments for clarity and correctness; backend remains unchanged.

- New files to be created:
  - None

- Existing files to be modified:
  - frontend/unity-spa/features/programs/edit.html
    - Keep Campus ID input hidden on Add (already true via ng-if="vm.isEdit").
    - Add a read-only visual indicating the selected campus when adding, to provide context. Example: "Campus: {selectedCampus.campus_name}" badge/label shown only when !vm.isEdit.
    - Ensure Default Curriculum label/help text reflects dependency on selected campus.
  - frontend/unity-spa/features/programs/programs.controller.js
    - Verify/ensure vm.initCampusBinding() (Add mode only) sets vm.model.campus_id from CampusService.getSelectedCampus() after CampusService.init() resolves.
    - Ensure vm.onCampusChange() is invoked when the initial binding sets campus_id to load curricula immediately. (This already occurs via the current changed flag; keep/strengthen behavior to be explicit.)
    - Keep listener for 'campusChanged' events to update campus_id and curricula live while adding.
    - No functional change to payload construction other than ensuring campus_id remains derived and included if present.
  - frontend/unity-spa/core/campus.service.js
    - No changes expected. Serves as source of truth for selected campus.
  - frontend/unity-spa/features/programs/programs.service.js
    - No changes expected.

- Files to be deleted or moved:
  - None

- Configuration file updates:
  - None

[Functions]
Minor adjustments to existing controller functions; no new services required.

- New functions:
  - None

- Modified functions:
  - ProgramEditController.initCampusBinding (frontend/unity-spa/features/programs/programs.controller.js)
    - Purpose: On Add, initialize campus_id from CampusService and react to global campus changes.
    - Required changes: Ensure vm.onCampusChange() fires on initial binding even if campus_id already matches, so curricula load deterministically.
  - ProgramEditController.onCampusChange (same file)
    - Purpose: Reset default_curriculum and loadCurricula based on campus_id.
    - Required changes: None functionally; remains the same.
  - ProgramEditController.save (same file)
    - Purpose: Create or update program with payload.
    - Required changes: None; ensure campus_id flows from vm.model and remains read-only in Add flow.

- Removed functions:
  - None

[Classes]
No classes are introduced or removed; existing AngularJS controllers/services remain.

- New classes: None
- Modified classes: None
- Removed classes: None

[Dependencies]
No dependency changes.

- No new packages required.
- No version changes.
- No integration changes.

[Testing]
Manual and targeted validation focused on Add vs. Edit behaviors and curriculum loading.

- Add Program
  - Open /#/programs/add with a clean session: CampusService.init should pick first active campus or first available campus; the page should display a read-only campus context. The Save button remains disabled until required fields are filled; it is not blocked by campus selection if one is auto-bound.
  - Verify default_curriculum dropdown is enabled only when campus_id is set; after auto-binding, ensure loadCurricula is called and the dropdown lists campus-scoped curricula.
  - While the Add page is open, change campus via the global campus selector; verify vm.model.campus_id updates and curricula reload accordingly; default_curriculum resets to null.

- Edit Program
  - Open /#/programs/:id/edit: Campus ID field is visible and editable (existing behavior).
  - Changing Campus ID should reset default_curriculum and reload curricula.

- Backend
  - Create program via UI and confirm request contains campus_id (matching CampusService selection). Validate created record includes campus_id. Confirm ProgramStoreRequest continues to accept nullable and backend defaulting works if not provided.

[Implementation Order]
Minimal, low-risk sequence to keep behavior cohesive while avoiding regressions.

1. edit.html: Add read-only display of the selected campus for Add mode (!vm.isEdit). Keep Campus ID input restricted to Edit mode (already present).
2. programs.controller.js: Make vm.initCampusBinding explicitly call vm.onCampusChange() after initial campus_id assignment to guarantee curricula loading.
3. Verify that vm.save payload still carries campus_id; no code change if already correct.
4. Manual test Add and Edit flows; verify campus change events update curricula live.
5. Code review; ensure no unintended exposure of campus_id on Add.
6. Commit changes.
