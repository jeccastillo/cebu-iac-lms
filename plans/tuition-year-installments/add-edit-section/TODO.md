task_progress Items:
- [ ] Step 1: Backend guard — prevent deleting an installment plan if referenced by any registration
- [ ] Step 2: Backend list enhancement — include used_count in GET /tuition-years/{id}/installments (optional UX improvement)
- [ ] Step 3: Frontend service — add listInstallments(tuitionYearId) to tuition-years.service.js
- [ ] Step 4: Frontend controller — add state and CRUD methods for installment plans in TuitionYearEditController
- [ ] Step 5: Frontend template — add Installment Plans section (table, inline edit, add form) to tuition-years/edit.html
- [ ] Step 6: QA — verify CRUD flows, finalized gating, and delete guard behavior

Context:
- Scope: Add/Edit Installment Plans on Tuition Year edit page; disallow deletion if plan in use.
- API in use:
  - GET /api/v1/tuition-years/{id}/installments
  - POST /api/v1/tuition-years/submit-extra (type=installment)
  - POST /api/v1/tuition-years/edit-type (xtype=installment)
  - POST /api/v1/tuition-years/delete-type (type=installment) with server-side guard

Notes:
- Finalized tuition year -> hide/disable add/edit/delete controls.
- Optional: disable delete button in UI when used_count > 0 if backend provides the count.
- Validation: code/label required; dp_type in ['percent','fixed']; dp_value bounds; increase_percent >= 0; installment_count 1..12; level in ['', 'college', 'shs', 'both'].
