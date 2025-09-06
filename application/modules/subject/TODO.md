# Subject Module - Add Grading System Dropdown to Add Subject Page

Goal:
- Ensure the Add Subject page includes a dropdown to select the grading system, mirroring the Edit Subject page implementation.

Context:
- Edit page already has: <select name="grading_system_id" id="grading_system_id"> populated from `tb_mas_grading`.
- Add page currently lacks this field.
- Controller `Subject::add_subject()` does not pass `grading_systems` to the view.

Tasks:
- [ ] Controller: In `Subject::add_subject()`, fetch and pass grading systems.
  - `$this->data['grading_systems'] = $this->data_fetcher->fetch_table('tb_mas_grading');`
- [ ] View: In `application/modules/subject/views/admin/add_subject.php`, insert dropdown:
  - Label: `Select Grading System`
  - Name/ID: `grading_system_id`
  - Options: loop through `$grading_systems` as provided by controller
  - Fallback: if empty, show disabled option `No grading systems configured`
- [ ] Verify: Submit flow posts `grading_system_id` to `submit_subject()` and persists to `tb_mas_subjects`.
- [ ] Regression: Confirm Edit Subject page remains unchanged.

Notes:
- DB column `grading_system_id` is already used by Edit Subject page; no migration required.
- Keep field placement consistent with Edit page (near other flags, before Include in GWA).
