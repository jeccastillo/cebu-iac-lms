# Implementation Plan

[Overview]
Add remaining-slots visibility and hard guards to the Enlistment page so users see how many seats are left while queuing subjects and are prevented from queuing full sections for both Add and Change Section actions.

This work augments the existing Registrar Enlistment SPA (AngularJS) to improve slot awareness and enforce capacity constraints. Currently, Add already blocks queuing when remaining_slots ≤ 0 and each option label embeds a "rem: X" suffix. We will (1) add an explicit inline "Remaining: X" indicator next to the Add selector, and (2) extend blocking to Change Section when the target section has no remaining slots, surfacing a clear alert. This ensures consistent behavior across add/change flows and improves UX clarity during queuing.

[Types]  
No schema migrations or TypeScript changes; we will standardize the in-memory Section object fields expected by the Enlistment UI.

- SectionListItem (JS object, in-memory)
  - intID: number (classlist id; required)
  - subjectCode | strCode | code: string (normalized to subjectCode/strCode)
  - sectionCode: string
  - subject_id: number|null (for prerequisite checking on Add)
  - display: string (e.g., "SUBJ101 — A — rem: 2")
  - slots: number|undefined (class capacity; optional UI decoration)
  - enlisted_count: number|undefined
  - enrolled_count: number|undefined
  - remaining_slots: number|null|undefined
    - null/undefined means unknown; do not block; display as "—"
    - 0 means full; block queuing for Add and Change Section
    - > 0 means available

[Files]
Only UI files in the Enlistment feature are modified.

- Existing files to be modified:
  - frontend/unity-spa/features/registrar/enlistment/enlistment.controller.js
    - Add helper functions to compute selected remaining slots for Add and Change-To selectors.
    - Add blocking logic to queueChange() mirroring Add when remaining_slots ≤ 0, with SweetAlert fallback to alert().
  - frontend/unity-spa/features/registrar/enlistment/enlistment.html
    - Add an explicit inline "Remaining: X" indicator next to the Add Select, with red/green text styling depending on availability.

- New files: none
- Files to be deleted or moved: none
- Configuration updates: none

[Functions]
We will add small helper functions and modify one existing queuing function.

- New functions
  - EnlistmentController.selectedAddRemaining(): number|null
    - File: frontend/unity-spa/features/registrar/enlistment/enlistment.controller.js
    - Purpose: Return the remaining_slots of the section currently selected in the Add dropdown (vm.selectedAddClasslistId). Returns null if unknown/not selected.
  - EnlistmentController.selectedChangeToRemaining(): number|null
    - File: frontend/unity-spa/features/registrar/enlistment/enlistment.controller.js
    - Purpose: Return the remaining_slots of the section currently selected in the Change-To dropdown (vm.changeToId). Returns null if unknown/not selected.

- Modified functions
  - EnlistmentController.queueChange()
    - File: frontend/unity-spa/features/registrar/enlistment/enlistment.controller.js
    - Change: Before pushing the change_section operation, block if the target section toId has remaining_slots ≤ 0, and show an alert (SweetAlert if present; otherwise alert()).
  - EnlistmentController.queueAdd()
    - Already contains a guard for remaining_slots ≤ 0 and will remain unchanged, but will leverage the new helper in the view to render the inline indicator.

- Removed functions
  - None

[Classes]
No classes are introduced or modified; this is an AngularJS controller enhancement with helper functions.

[Dependencies]
No new packages or changes. Uses existing SweetAlert2 detection (window.Swal) for alerts, with native alert() fallback.

[Testing]
Manual functional validation in the SPA.

- Preconditions:
  - The SectionsSlotsService.list endpoint returns rows including remaining_slots for classlists in the selected term.
  - Enlistment page can load classlists and merge slot data via fetchSlotsAndMerge().

- Scenarios:
  1. Add flow, available section:
     - Select a section with remaining_slots = 3 in the Add selector.
     - Inline indicator shows "Remaining: 3" in green.
     - Click "Queue Add" → Operation is queued.
  2. Add flow, full section:
     - Select a section with remaining_slots = 0.
     - Inline indicator shows "Remaining: 0" in red.
     - Click "Queue Add" → A blocking alert is shown; operation is not added.
  3. Change Section flow, available target:
     - Pick a current classlist in "Change From".
     - Pick a target section with remaining_slots = 1 in "Change To".
     - Click "Queue Change" → Operation is queued.
  4. Change Section flow, full target:
     - Pick a target section with remaining_slots = 0.
     - Click "Queue Change" → A blocking alert is shown; operation is not added.
  5. Unknown remaining:
     - If remaining_slots is null/undefined, the inline indicator shows "—" in neutral color and actions proceed (no block).
  6. Regression:
     - Auto-queue from Checklist remains unaffected and still prefers remaining_slots > 0 candidates as implemented.

[Implementation Order]
Implement helpers and UI indicator first, then enforce change guard, then test.

1. Add selectedAddRemaining() and selectedChangeToRemaining() helpers on vm in enlistment.controller.js.
2. Update enlistment.html to show explicit inline "Remaining: X" indicator next to the Add selector using selectedAddRemaining().
3. In queueChange(), compute toSec and block with an alert when remaining_slots ≤ 0.
4. Manual test the four scenarios plus unknown state.
5. Commit.
