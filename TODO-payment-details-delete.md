# Admin Payment Details Delete - TODO

Goal: Allow admin users to delete rows from the `payment_details` list (API + SPA).

Status: In progress

Steps:
- [ ] 1) Backend Service: Add `delete(int $id)` in `laravel-api/app/Services/PaymentDetailAdminService.php`
  - Validate table exists
  - Ensure row exists; if not, throw ValidationException
  - Transactionally delete: `DB::table('payment_details')->where('id', $id)->delete()`

- [ ] 2) Backend Controller: Add `destroy(int $id): JsonResponse` in `laravel-api/app/Http/Controllers/Api/V1/PaymentDetailAdminController.php`
  - Call service `delete($id)`
  - Return `{ success: true }`

- [ ] 3) Routes: Register DELETE endpoint in `laravel-api/routes/api.php`
  - `DELETE /api/v1/finance/payment-details/{id}` with `middleware('role:admin')`
  - Place near existing GET/PATCH routes

- [ ] 4) Frontend Service: Add `remove(id)` in `frontend/unity-spa/features/admin/payment-details/payment-details.service.js`
  - Calls DELETE `/finance/payment-details/{id}` with admin headers

- [ ] 5) Frontend Controller: Add `vm.remove(item)` in `frontend/unity-spa/features/admin/payment-details/payment-details.controller.js`
  - Confirm via `window.confirm`
  - Call service `remove(id)`
  - On success: toast message, remove from `vm.items`, close editor if selected is deleted, refresh search

- [ ] 6) Frontend Template: Add Delete buttons in `frontend/unity-spa/features/admin/payment-details/edit.html`
  - Row actions: Add a Delete button next to Edit → `ng-click="vm.remove(p)"`
  - Edit drawer footer: Add red Delete button → `ng-click="vm.remove(vm.selected)"`

- [ ] 7) QA
  - API: GET search → DELETE item → GET search confirms removal
  - SPA: Delete from list and from edit drawer; verify list refresh and messaging

- [ ] 8) Documentation (optional)
  - Mention admin-only delete in implementation plan or README snippet
