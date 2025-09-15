# Admin: Student Editor - Autocomplete Integration

Goal:
Use autocomplete to select a student instead of manually typing the internal Student ID (tb_mas_users.intID) on the Admin Student Editor prompt page.

Scope:
- Frontend only (AngularJS 1.x, unity-spa).
- Page: `frontend/unity-spa/features/admin/students/prompt.html`
- Controller already has autocomplete logic in `prompt.controller.js`:
  - `vm.selectedId`, `vm.items`, `vm.onStudentQuery(q)`, `vm.goFromSelect()`, and `vm.go()` fallback.

Plan / Tasks:
- [ ] Update helper text to mention search by name/number with fallback to internal ID.
- [ ] Replace the single numeric input with:
  - [ ] Autocomplete input using `pui-autocomplete`
        - `pui-source="vm.items"`
        - `pui-item-key="id"`
        - `pui-label="(item.student_number + ' — ' + (item.last_name || '') + ', ' + (item.first_name || ''))"`
        - `pui-on-query="vm.onStudentQuery($query)"`
        - `pui-on-select="vm.goFromSelect()"`
        - `ng-model="vm.selectedId"`
  - [ ] Keep a fallback numeric input for internal Student ID (optional).
- [ ] Manual Testing:
  - [ ] Focus autocomplete, type a name or number → suggestions appear.
  - [ ] Selecting a suggestion navigates to `/admin/students/:id/edit`.
  - [ ] Entering a numeric ID and clicking Go still navigates correctly.
  - [ ] Unauthorized users still see "Access denied. Admin only." and cannot proceed.

Notes:
- The `pui-autocomplete` directive is already included in `index.html`.
- API endpoint `/students?q=...` is used elsewhere and provides the needed fields.

Files to Change:
- `frontend/unity-spa/features/admin/students/prompt.html`

No backend changes required.
