# Educational Background Section - Application Page

Plan steps:
- [ ] Wire PreviousSchoolsService into AdmissionsApplyController (DI and function args).
- [ ] Extend controller state:
  - [ ] vm.previousSchools collection and vm.educ (notOnList, selected).
  - [ ] vm.form fields: previous_school_id, previous_school_name, previous_school_city, previous_school_province, previous_school_country, program_strand_degree, grade_year_level, lrn_number.
- [ ] Add methods:
  - [ ] vm.loadPreviousSchools()
  - [ ] vm.onPreviousSchoolChange()
  - [ ] vm.toggleNotOnList()
  - [ ] Call vm.loadPreviousSchools() in activate().
- [ ] Update template (apply.html):
  - [ ] Insert Educational Background fieldset before Address.
  - [ ] Last School Attended select + NOT ON THE LIST checkbox + manual name field.
  - [ ] City, State/Province, Country inputs auto-populated from selection; editable in NOT ON THE LIST mode.
  - [ ] Grade/Year Level, Program/Strand/Degree earned, and LRN inputs.
- [ ] Verify submit payload contains the new fields (no backend changes required; AdmissionsController stores full payload JSON).

Notes:
- The previous_schools table has fields: name, city, province, country. The grade column was dropped; capture grade and LRN on the application form only.
- Public list endpoint: PreviousSchoolsService.publicList({ per_page: 500, search? }).
