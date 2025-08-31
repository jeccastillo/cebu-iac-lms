# Student Billing: pui-autocomplete integration

Goal: Use pui-autocomplete for Student and Term (SYID) search filters on the Student Billing page for faster, user-friendly selection.

Scope:
- Page: frontend/unity-spa/features/finance/student-billing/list.html
- Controller: frontend/unity-spa/features/finance/student-billing/student-billing.controller.js
- Services used: StudentsService (listAll), TermService (init, availableTerms, getSelectedTerm, getActiveTerm)

Tasks:
- [ ] UI: Replace "Student ID (optional)" number input with pui-autocomplete bound to vm.filters.student_id
  - pui-source="vm.students"
  - pui-item-key="id"
  - pui-label="(item.student_number + ' — ' + item.last_name + ', ' + item.first_name + (item.middle_name ? (' ' + item.middle_name) : ''))"
  - pui-on-select="vm.onStudentSelect()"
- [ ] UI: Replace "Term (SYID)" number input with pui-autocomplete bound to vm.filters.term
  - pui-source="vm.termOptions"
  - pui-item-key="intID"
  - pui-label="(item.label || ('SY ' + item.intID))"
- [ ] Controller: Add arrays and preload data
  - vm.students = []
  - vm.termOptions = []
  - In activate(): TermService.init() then set vm.termOptions from TermService.availableTerms; set default vm.filters.term from TermService.getSelectedTerm() else TermService.getActiveTerm()
  - Preload vm.students via StudentsService.listAll()
- [ ] Controller: Update vm.onStudentSelect to lookup selected student by vm.filters.student_id and sync vm.filters.student_number
- [ ] Verify search behavior (still uses student_id/student_number + term) and Reset works (defaults term from active/global)
- [ ] Manual test:
  - Autocomplete lists appear for both fields; labels correct
  - Selecting a student sets vm.filters.student_id and fills vm.filters.student_number
  - Selecting a term sets vm.filters.term (intID); label shown when source is present
  - Search returns expected records

Notes:
- pui-autocomplete displays the label when ng-model matches an item key present in the source array&#39;s index; ensure vm.termOptions includes the selected term intID for display.
- No backend changes required.
