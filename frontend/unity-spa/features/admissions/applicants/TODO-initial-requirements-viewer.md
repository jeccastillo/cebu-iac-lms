# TODO — Admissions Applicant Viewer: Initial Requirements Section

Goal: On the Admissions Applicant Viewer page, add a section to view initial requirements details of the applicant.

Tasks:
- [ ] Backend: Expose latest applicant_data `hash` in `ApplicantController@show` response
- [ ] Frontend (Controller): 
  - [ ] Capture `hash` from applicant details response
  - [ ] Fetch initial requirements via `InitialRequirementsService.getList(hash)`
  - [ ] Store state in `vm.irLoading`, `vm.irError`, `vm.initial_requirements`
- [ ] Frontend (View): 
  - [ ] Add an "Initial Requirements" card in `view.html`
  - [ ] Show loading, error, empty state, and list/table of requirements
  - [ ] Open `file_link` in new tab when present
- [ ] Verify end-to-end with an applicant that has a valid `hash` in latest `tb_mas_applicant_data`
- [ ] QA: Error handling, no-hash scenarios, and UI parity

Notes:
- Public API endpoint already exists: `GET /api/v1/public/initial-requirements/{hash}`
- Frontend service exists: `InitialRequirementsService.getList(hash)`
- No route/policy updates required
