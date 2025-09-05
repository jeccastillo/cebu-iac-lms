# Tuition Years - Miscellaneous Fees Edit Feature

Scope: Add ability to edit existing Miscellaneous Fees for a Tuition Year.

Status: In Progress

## Tasks

- [ ] Backend (Laravel)
  - [ ] Add `editType(Request $request)` in `TuitionYearController` to update rows in extra tables.
    - Validate: `type` in `{misc, lab_fee, track, program, elective}`, `id` present.
    - Determine table: `tb_mas_tuition_year_{type}`.
    - Determine PK: `intID` for `misc`, `lab_fee`; `id` for `track`, `program`, `elective`.
    - Whitelist editable fields per type (do not allow FK changes).
    - Update and return `{ success: true, message: 'Successfully Updated' }`.
  - [ ] Wire route `POST /api/v1/tuition-years/edit-type` with `role:registrar,admin`.

- [ ] Frontend (AngularJS)
  - [ ] Service: add `updateExtra(type, id, payload)` in `features/tuition-years/tuition-years.service.js` to POST to `/tuition-years/edit-type`.
  - [ ] Controller: `features/tuition-years/tuition-years.controller.js`
    - [ ] Add `vm.editing = { miscId: null }`.
    - [ ] Methods:
      - [ ] `startEditMisc(row)` → set `vm.editing.miscId = row.intID`, `vm.local.miscEdit = angular.copy(row)`.
      - [ ] `cancelEditMisc()` → clear editing state.
      - [ ] `updateMisc(id, payload)` → call service, show toast, reload list, clear editing.
    - [ ] Reset `vm.editing.miscId` on `loadAll()` completion.
  - [ ] Template: `features/tuition-years/edit.html`
    - [ ] Add "Edit" button beside "Delete" for Misc rows when not finalized.
    - [ ] Render inline editor row when `vm.editing.miscId === m.intID` with inputs for:
      - `name`, `miscRegular`, `miscOnline`, `miscHyflex`, `miscHybrid`, `type`
      - Actions: "Save" (calls `vm.updateMisc(...)`), "Cancel" (calls `vm.cancelEditMisc()`)

## Test Plan

1. Open a Tuition Year (Draft state).
2. Add a Misc item (if none exists).
3. Click "Edit" on a misc row.
4. Change values and click "Save".
5. Verify table refresh and updated values.
6. Verify backend persisted data (reload page).
7. Verify "Finalize" state disables Edit/Add/Delete.
8. Regression: Add/Delete still work for Misc and other sections.

## Files to Change

- laravel-api/app/Http/Controllers/Api/V1/TuitionYearController.php
- laravel-api/routes/api.php
- frontend/unity-spa/features/tuition-years/tuition-years.service.js
- frontend/unity-spa/features/tuition-years/tuition-years.controller.js
- frontend/unity-spa/features/tuition-years/edit.html
