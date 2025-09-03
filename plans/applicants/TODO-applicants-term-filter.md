# Applicants: Filter by Term (SYID)

Objective:
Add a "Term" filter on the Applicants list page. Populate a dropdown with academic terms using label format:
term_student_type + " " + enumSem + " " + term_label + " " + strYearStart + "-" + strYearEnd
Send selected term as syid to the backend and filter results accordingly.

Scope:
- Frontend (AngularJS unity-spa)
  - features/admissions/applicants/list.html
  - features/admissions/applicants/applicants.controller.js
  - features/admissions/applicants/applicants.service.js
- Backend (Laravel)
  - app/Http/Controllers/Api/V1/ApplicantController.php

References:
- SchoolYearsService: GET /api/v1/school-years (fields: term_student_type, enumSem, term_label, strYearStart, strYearEnd, primary key likely intID)
- Migration: 2025_09_03_001200_add_syid_to_tb_mas_applicant_data.php (tb_mas_applicant_data.syid exists)
- Applicants API currently supports: search, status, campus, date_from, date_to, pagination, sorting.

Tasks:
- [ ] Frontend: Service param wiring
  - [ ] Update ApplicantsService._paramsFromFilters to include syid when present.
- [ ] Frontend: Controller enhancements
  - [ ] Inject SchoolYearsService into ApplicantsListController.
  - [ ] Add vm.filters.syid with default ''.
  - [ ] Add vm.terms array and helper vm.termLabel(t) to format labels as:
        `${t.term_student_type} ${t.enumSem} ${t.term_label} ${t.strYearStart}-${t.strYearEnd}`.
  - [ ] Implement vm.loadTerms(campusId) to fetch terms, using SchoolYearsService.list({ campus_id }).
  - [ ] In activate(), after campus initialization, call vm.loadTerms(selectedCampusId) before initial load().
  - [ ] Update vm.onCampusChange to refresh terms and then reload list (ensure promise handling).
  - [ ] Add vm.onTermChange to reset page and reload list.
  - [ ] Update vm.clearFilters to also clear syid and reload terms by current campus.
- [ ] Frontend: UI
  - [ ] Add a "Term" dropdown to features/admissions/applicants/list.html (next to Campus/Date filters).
  - [ ] Bind to vm.filters.syid, populate via vm.terms with ng-options "t.intID as vm.termLabel(t) for t in vm.terms".
  - [ ] Add "All" option and ng-change="vm.onTermChange()".
- [ ] Backend: API filter
  - [ ] ApplicantController@index: accept syid (or alias term) from query string.
  - [ ] If provided and numeric, apply where('ad.syid', (int)$syid).
  - [ ] Keep existing filters/sort/pagination logic unchanged.

QA Checklist:
- [ ] Applicants page shows Term dropdown, populated with correct labels.
- [ ] Selecting a term triggers a list reload with syid sent to API.
- [ ] Clearing filters resets Term and reloads (respecting campus default).
- [ ] Changing campus refreshes Term list; selection is cleared if not available for new campus; list reloads.
- [ ] Backend returns filtered applicants where latest applicant_data.syid matches the selected term.

Notes:
- SchoolYearsService already supports params campus_id, term_student_type, search, limit; use campus_id to scope terms.
- ApplicantController joins latest tb_mas_applicant_data row per user and exposes ad.status & ad.created_at; we add filter on ad.syid.
- Label composition must match exactly: term_student_type + enumSem + term_label + strYearStart + "-" + strYearEnd.
