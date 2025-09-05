# Applicants List - Campus Dropdown + Global Default

Goal: On the Applicant search page, replace the free-text campus filter with a dropdown populated from CampusService, and default it to the globally selected campus. React to global campus changes.

Tasks:
- [x] Frontend: Replace campus text input with select dropdown
  - File: features/admissions/applicants/list.html
  - Bind: ng-model="vm.filters.campus"
  - Options: c.id as c.campus_name for c in vm.campuses
  - Include "All" option with empty value ""
  - ng-change="vm.onCampusChange()"

- [x] Frontend: Wire ApplicantsListController to CampusService
  - File: features/admissions/applicants/applicants.controller.js
  - Inject: CampusService, $scope
  - Add: vm.campuses = []
  - In activate():
    - await CampusService.init()
    - set vm.campuses from CampusService.availableCampuses
    - default vm.filters.campus = selected campus id if empty
    - then load()
  - Listen to 'campusChanged':
    - update vm.filters.campus to new selected campus id
    - reset page to 1, then re-run search/load
  - Update clearFilters() to reset vm.filters.campus to the global selection (not empty)
  - Add vm.onCampusChange() to trigger page reset and load

- [x] No backend changes required
  - ApplicantController already accepts ?campus as either campus_name or campus_id; we will send campus_id

Verification:
- Initial page load: dropdown shows global campus and results are scoped
- Changing global campus via sidebar updates filter and refreshes results
- Selecting a different campus from dropdown updates the list and request params with ?campus={id}
- Clear resets to global default campus and refreshes
