# Payment Modes UI Adaptation - TODO

Objective: Make the Payment Modes list page adopt the same UI elements and layout pattern used by the Students list page.

Scope:
- Restructure list.html to use Students page layout (header with total, Advanced Search card, Results card, per-column client-side filters, actions dropdown, pagination).
- Enhance controller to support new UI (title, total, error handling, client-side column filters, dropdown state).

Tasks:
- [ ] Controller: Add vm.title, vm.total, vm.error
- [ ] Controller: Add vm.cf and vm.filteredRows() (client-side filtering for columns)
- [ ] Controller: Add dropdown state helpers (vm.menuOpenId, vm.toggleMenu, vm.isMenuOpen, vm.closeMenu)
- [ ] Controller: Update load() to set vm.total, initialize vm.error, and handle failure with .catch
- [ ] Template: Replace top layout with Students layout (space-y-6, header with icon + total)
- [ ] Template: Implement Advanced Search card (move existing filters, Search/Clear buttons, loading spinner, error message)
- [ ] Template: Implement Results card with:
  - [ ] Table headers aligned to Students style
  - [ ] Second header row for per-column inputs (Name, Type, Channel, Method)
  - [ ] Body using vm.filteredRows() track by row.id
  - [ ] Actions dropdown button and menu (Edit, Activate/Deactivate, Delete, Restore)
  - [ ] Empty state row when no filtered rows and not loading
- [ ] Template: Pagination footer styled like Students (Prev/Next, page display)

Notes:
- Preserve existing server-side filters, sorting, and pagination via PaymentModesService.
- No backend changes required.
