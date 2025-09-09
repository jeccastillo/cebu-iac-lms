# TODO — compute_full implementation

Task Progress:
- [ ] Step 1: Migration — add compute_full (boolean default true) to tb_mas_scholarships
- [ ] Step 2: Backend validation — accept compute_full in ScholarshipStoreRequest and ScholarshipUpdateRequest
- [ ] Step 3: Backend mapping — include compute_full in ScholarshipResource, ScholarshipService::list/mapModel/normalizePayload
- [ ] Step 4: SPA controller — add vm.form.compute_full (default true), include in payload, and show toast only on successful Update
- [ ] Step 5: SPA template — add checkbox for “Compute on Full Assessment” with help text, bind to vm.form.compute_full
- [ ] Step 6: Run migrations and smoke test API/UI

Notes:
- Default value: true (per requirement)
- Toast: only on successful Update (PUT), text: “Scholarship updated.”
