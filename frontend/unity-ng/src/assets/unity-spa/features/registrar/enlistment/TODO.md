# Enlistment Page: Side-by-side Registration Details and Edit Form

Goal: Display current registration values in a left column and the editable registration form in a right column on the Enlistment page.

## Tasks

- [ ] Controller: add small helper methods for read-only display
  - `vm.findProgramById(id)` - lookup in `vm.programs`
  - `vm.findCurriculumById(id)` - lookup in `vm.curricula`
  - `vm.findTuitionYearById(id)` - lookup in `vm.tuitionYears`
  - `vm.readableWithdrawalPeriod(value)` - normalize `before/start/end` (or numbers) to labels

- [ ] Template (enlistment.html): update Registration Details block
  - Wrap the content with a responsive two-column layout: `grid grid-cols-1 md:grid-cols-2`
  - Left column: read-only current values pulled from `vm.registration` with lookups/helpers
  - Right column: keep existing edit form fields and actions unchanged

- [ ] Responsiveness and UX
  - Ensure it collapses to single column on small screens
  - Preserve existing loading/empty states
  - Keep form validation, Save, and Reset behaviors intact

- [ ] Manual verification
  - Load a student with an existing registration and verify left column shows correct values
  - Change form values on the right, Save, and confirm left column updates after save
  - No console errors

## Notes

- Read-only column uses `vm.registration` (source of truth).
- Edit column uses `vm.regForm` (existing behavior).
- Program/Curriculum/Tuition Year labels are resolved via local arrays already loaded in the controller.
