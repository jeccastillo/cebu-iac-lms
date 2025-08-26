# Implementation Plan

[Overview]
Make the Campus field uneditable on the Program Edit page while keeping the Add page behavior unchanged. The edit form should display a read-only Campus (name and ID) instead of an editable numeric input, and the backend payload should remain unchanged.

This change improves data integrity by preventing accidental campus reassignment for existing programs. The UI will show campus context clearly without allowing modifications. The Add form will continue to bind to the global campus selector via CampusService; the Edit form will simply display the program&#39;s current campus.

[Types]  
No new TypeScript or external type system is introduced; we will maintain plain JavaScript object shapes used in the AngularJS controllers and services.

Data shapes involved:
- Program model (view-model in ProgramEditController):
  - strProgramCode: string (required)
  - strProgramDescription: string (required)
  - strMajor: string | null
  - type: &#39;college&#39; | &#39;shs&#39; | &#39;drive&#39; | &#39;other&#39;
  - school: string | null
  - short_name: string | null
  - default_curriculum: number | null
  - enumEnabled: 0 | 1
  - campus_id: number | null (set from API; uneditable on Edit)
- Campus (from CampusService.availableCampuses):
  - id: number
  - campus_name: string
  - [other fields may exist but are not required for this change]

[Files]
Only frontend files in the Unity SPA Programs feature will be modified.

- New files to be created
  - None.

- Existing files to be modified
  1) frontend/unity-spa/features/programs/edit.html
     - Replace the editable Campus ID numeric input (shown when vm.isEdit) with a read-only display showing campus name and ID.
     - Keep the existing Add-mode campus display (vm.selectedCampus sourced from CampusService) unchanged.
     - Ensure no ng-change triggers for campus on Edit mode.
  2) frontend/unity-spa/features/programs/programs.controller.js
     - Add a helper function vm.syncSelectedCampusForEdit() that, in Edit mode, initializes CampusService (if needed) and sets vm.selectedCampus by matching vm.model.campus_id against CampusService.availableCampuses.
     - Call vm.syncSelectedCampusForEdit() after vm.load() populates vm.model and after vm.loadCurricula() in Edit mode.

- Files to be deleted or moved
  - None.

- Configuration file updates
  - None.

[Functions]
We will add one function and adjust one existing function call sequence. No backend service changes are required.

- New functions
  - Name: vm.syncSelectedCampusForEdit
  - Signature: function () : void
  - File path: frontend/unity-spa/features/programs/programs.controller.js
  - Purpose: In Edit mode, ensure vm.selectedCampus is set for read-only display by finding the campus object that matches vm.model.campus_id. It initializes CampusService (if necessary) and then derives vm.selectedCampus. If the campus is not found, fall back to showing the numeric campus_id in the template.

  Pseudocode/spec:
  ```
  vm.syncSelectedCampusForEdit = function () {
    if (!vm.isEdit) return;
    var assign = function () {
      try {
        var list = (CampusService && CampusService.availableCampuses) || [];
        var id = vm.model.campus_id;
        var found = null;
        for (var i = 0; i < list.length; i++) {
          var c = list[i];
          var cid = (c &amp;&amp; c.id !== undefined &amp;&amp; c.id !== null) ? parseInt(c.id, 10) : null;
          if (cid === id) { found = c; break; }
        }
        vm.selectedCampus = found;
      } catch (e) { /* no-op */ }
    };
    var p = (CampusService &amp;&amp; CampusService.init) ? CampusService.init() : null;
    if (p &amp;&amp; p.then) { p.then(assign); } else { assign(); }
  };
  ```

- Modified functions
  - Name: vm.load
  - File path: frontend/unity-spa/features/programs/programs.controller.js
  - Required changes:
    - After populating vm.model from API and calling vm.loadCurricula(), call vm.syncSelectedCampusForEdit() to set up vm.selectedCampus for the edit view&#39;s read-only campus display.

- Removed functions
  - None. (vm.onCampusChange remains for Add mode where campus binding still exists.)

[Classes]
No classes are used or modified (AngularJS controllers are functions).

- New classes
  - None.

- Modified classes
  - None.

- Removed classes
  - None.

[Dependencies]
No dependency modifications.

- No new packages.
- No version changes.
- Integration continues to rely on existing CampusService and ProgramsService.

[Testing]
Manual UI validation with focused checks on both Add and Edit flows.

- Test files
  - No automated test files exist in scope; manual verification steps outlined below.

- Manual validation steps
  1) Navigate to Add Program (/programs/add):
     - Verify campus display remains driven by global campus selector (unchanged).
     - Verify curriculum dropdown enables only after a campus is present.
     - Verify saving still includes campus_id from global selector.
  2) Navigate to Edit Program (/programs/:id/edit) for an existing program with campus_id set:
     - Confirm the Campus section renders as read-only (no input).
     - Confirm it shows "CampusName (ID: 123)" when campus list is available.
     - If campus not found in list, confirm it shows "Campus ID: 123" fallback.
     - Confirm curriculum list loads (using vm.model.campus_id) and can be changed.
     - Confirm Save works and payload still includes campus_id unchanged.
  3) Navigate to Edit Program for a program with campus_id null:
     - Confirm read-only section shows "No campus set".
     - Confirm curriculum dropdown remains disabled due to missing campus_id.
  4) Regressions:
     - Program list page still loads and filters correctly.
     - Add Program still blocks Save until a campus is selected (unchanged rule: Save disabled when !vm.isEdit &amp;&amp; !vm.model.campus_id).

[Implementation Order]
Make template changes first, then controller enhancements, and validate behavior.

1) Edit template (frontend/unity-spa/features/programs/edit.html):
   - Replace the Edit-mode campus input with a read-only display:
     - Remove:
       - The <input type="number" ... ng-model="vm.model.campus_id" ng-change="vm.onCampusChange()"> block inside <div ng-if="vm.isEdit">.
     - Add:
       ```
       <div ng-if="vm.isEdit">
         <label class="block text-xs font-medium text-gray-600 mb-1">Campus</label>
         <div class="w-full px-3 py-2 border border-gray-200 rounded-md bg-gray-50 text-sm">
           <span ng-if="vm.selectedCampus">
             {{ vm.selectedCampus.campus_name }} (ID: {{ vm.selectedCampus.id }})
           </span>
           <span ng-if="!vm.selectedCampus &amp;&amp; vm.model.campus_id !== null">
             Campus ID: {{ vm.model.campus_id }}
           </span>
           <span ng-if="vm.model.campus_id === null" class="text-gray-500">No campus set</span>
         </div>
         <p class="text-xs text-gray-500 mt-1">Campus cannot be changed when editing a program.</p>
       </div>
       ```
   - Keep the Add-mode campus info block (ng-if="!vm.isEdit") unchanged.

2) Controller changes (frontend/unity-spa/features/programs/programs.controller.js):
   - Add vm.syncSelectedCampusForEdit function as defined above.
   - In vm.load(), after:
     - Setting vm.model.campus_id from the loaded row, and
     - Calling vm.loadCurricula();
     - Then call vm.syncSelectedCampusForEdit(); to populate vm.selectedCampus for display.
   - Do not alter payload or save logic; campus_id remains included and unchanged in Edit mode.

3) Validate in browser:
   - Confirm read-only campus display on Edit and unchanged Add behavior.
   - Confirm curriculum loading behavior remains correct.

4) Code cleanup (optional, non-functional):
   - None required; keep onCampusChange for Add path.
