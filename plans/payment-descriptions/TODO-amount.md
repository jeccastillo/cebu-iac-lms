# TODO - Payment Descriptions: Add Amount Field (decimal/float) and Update CRUD

Scope: Add an amount field to Payment Descriptions and propagate through backend (Laravel) and frontend (AngularJS) CRUD.

Approved Plan Summary:
- DB: Add `amount` column (decimal(12,2), nullable) to `payment_descriptions`.
- Backend: expose and validate `amount`; allow sorting by amount.
- Frontend: add amount to form and list; pass through service; normalize payload.

Tasks:
- [ ] Migration: add `amount` to `payment_descriptions`
  - File: `laravel-api/database/migrations/2025_08_30_001000_add_amount_to_payment_descriptions.php`
  - Column: `amount` decimal(12,2) nullable after `name`
- [ ] Model: `App\Models\PaymentDescription`
  - [ ] Add `'amount'` to `$fillable`
  - [ ] Add `$casts = ['amount' => 'float']`
- [ ] Requests:
  - [ ] `PaymentDescriptionStoreRequest`: add rule `amount` => `nullable|numeric|min:0`
  - [ ] `PaymentDescriptionUpdateRequest`: add rule `amount` => `sometimes|nullable|numeric|min:0`
- [ ] Resource: `App\Http\Resources\PaymentDescriptionResource`
  - [ ] Include `amount` in response
- [ ] Controller: `PaymentDescriptionController@index`
  - [ ] Allow sorting by `amount` (add to `$allowedSort`)
- [ ] Frontend - Edit Page
  - [ ] `features/finance/payment-descriptions/edit.html`: add Amount input (number, step 0.01, min 0) bound to `vm.model.amount`
  - [ ] `payment-descriptions.controller.js` (Edit controller):
    - [ ] Initialize `vm.model.amount = null`
    - [ ] Map API response `d.amount` into `vm.model.amount`
    - [ ] Before save, normalize: set `payload.amount` to `null` or `parseFloat(...)`
- [ ] Frontend - List Page
  - [ ] `features/finance/payment-descriptions/list.html`: add Amount column, sortable by `amount`, display `{{ row.amount | number:2 }}`
- [ ] Run migration
  - [ ] Command: `cd laravel-api && php artisan migrate`

Notes:
- DB type chosen as decimal(12,2) for currency precision; value is cast to float in API responses for frontend use.
- Sorting and searching remain primarily by name; optional sort by amount added.

Progress Log:
- [ ] Created this TODO file.
