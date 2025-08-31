# Implementation Plan

[Overview]
Create a new Finance CRUD for managing payment descriptions, with a minimal schema containing intID and name, exposed via secured RESTful endpoints and optional AngularJS admin UI.

This feature introduces a new master table for Finance to centrally manage short descriptions applied to payments or related financial records. The backend follows existing Laravel structures (PaymentModeController style responses, resources, and form requests), while aligning with legacy key naming conventions requested for this entity (primary key intID). The API will be protected by finance/admin roles. Optionally, a lightweight AngularJS UI under Finance will mirror existing Payment Modes UX patterns for a consistent admin experience.

[Types]
Add a new Eloquent model using a non-default primary key and a simple Resource DTO.

- Database table: payment_descriptions
  - Columns:
    - intID: int unsigned auto-increment primary key
    - name: varchar(128) NOT NULL (unique index recommended)
  - Notes:
    - No timestamps (created_at/updated_at) per requirement of only two fields.
    - No soft deletes (deleted_at) to keep schema strictly two fields.

- Eloquent Model: App\Models\PaymentDescription
  - $table = 'payment_descriptions'
  - $primaryKey = 'intID'
  - public $timestamps = false
  - $fillable = ['name']

- API Resource: App\Http\Resources\PaymentDescriptionResource
  - Shape response: { intID: int, name: string }

- Form Requests:
  - Store: name required|string|max:128|unique:payment_descriptions,name
  - Update: name sometimes|required|string|max:128|unique:payment_descriptions,name,{intID},intID

[Files]
Introduce new migration, model, controller, requests, resource, and routes; optionally add AngularJS screens/services and integrate with routes and sidebar.

- New backend files:
  - laravel-api/database/migrations/2025_08_30_000900_create_payment_descriptions_table.php
    - Create payment_descriptions with:
      - increments('intID')
      - string('name', 128)
      - unique index on name
      - No timestamps, no soft deletes
  - laravel-api/app/Models/PaymentDescription.php
    - Eloquent model per Types section
  - laravel-api/app/Http/Resources/PaymentDescriptionResource.php
    - Maps model to API response
  - laravel-api/app/Http/Requests/Api/V1/PaymentDescriptionStoreRequest.php
    - Validates create payload
  - laravel-api/app/Http/Requests/Api/V1/PaymentDescriptionUpdateRequest.php
    - Validates update payload, handles unique rule with current record
  - laravel-api/app/Http/Controllers/Api/V1/PaymentDescriptionController.php
    - REST controller modeled after PaymentModeController patterns
  - Modify: laravel-api/routes/api.php
    - Add routes under /api/v1/payment-descriptions with middleware role:finance,admin

- Optional new frontend files (AngularJS admin UI):
  - frontend/unity-spa/features/finance/payment-descriptions/payment-descriptions.service.js
    - $http integration for list/show/create/update/delete
  - frontend/unity-spa/features/finance/payment-descriptions/payment-descriptions.controller.js
    - Controller for list + edit views (mirrors payment-modes controller style)
  - frontend/unity-spa/features/finance/payment-descriptions/list.html
    - Table with search/filter, pagination, and actions
  - frontend/unity-spa/features/finance/payment-descriptions/edit.html
    - Form with single input name and save/delete actions
  - Modify: frontend/unity-spa/core/routes.js
    - Register states: finance.payment-descriptions, finance.payment-descriptions.edit
  - Modify: frontend/unity-spa/shared/components/sidebar/sidebar.html
    - Add menu entry under Finance

[Functions]
Add a new set of REST endpoints and associated handlers; add new service functions for the Angular client.

- New Controller methods in PaymentDescriptionController:
  - index(Request $request): JsonResponse
    - Query all with optional search=name substring; support sorting by name; optional pagination (page, per_page) like PaymentModeController
  - show(int $id): JsonResponse
  - store(PaymentDescriptionStoreRequest $request): JsonResponse
    - Create row with validated name
  - update(PaymentDescriptionUpdateRequest $request, int $id): JsonResponse
    - Update name
  - destroy(int $id): JsonResponse
    - Hard delete (no soft deletes)

- New AngularJS service methods (PaymentDescriptionsService):
  - list(filters): GET /payment-descriptions with params { search?, sort?, order?, page?, per_page? }
  - show(id): GET /payment-descriptions/{id}
  - create(payload): POST /payment-descriptions
  - update(id, payload): PUT /payment-descriptions/{id}
  - remove(id): DELETE /payment-descriptions/{id}

[Classes]
Introduce new Laravel classes adhering to existing naming/layout patterns.

- New classes:
  - App\Models\PaymentDescription
    - Key properties: $table, $primaryKey, $timestamps=false, $fillable
  - App\Http\Resources\PaymentDescriptionResource
    - toArray returns ['intID' => ..., 'name' => ...]
  - App\Http\Requests\Api\V1\PaymentDescriptionStoreRequest
    - rules: name required|string|max:128|unique
  - App\Http\Requests\Api\V1\PaymentDescriptionUpdateRequest
    - rules: name sometimes|required|string|max:128|unique (ignoring current intID)
  - App\Http\Controllers\Api\V1\PaymentDescriptionController
    - Uses Resource responses and mirrors PaymentModeController response envelopes

- Modified classes:
  - N/A

[Dependencies]
No new third-party packages required.

All components leverage existing Laravel and AngularJS infrastructure in the repository. No composer.json or package.json changes.

[Testing]
Provide API smoke tests and manual verification steps; optionally add a PHP script for quick CLI testing.

- API Tests (manual via curl or REST client):
  - Create:
    - POST /api/v1/payment-descriptions { "name": "Down Payment" }
    - Expect 201, { success: true, data: { intID, name } }
  - List:
    - GET /api/v1/payment-descriptions?search=Down&amp;page=1&amp;per_page=10
    - Expect 200, success: true, data: [...], meta pagination when paged
  - Show:
    - GET /api/v1/payment-descriptions/{id}
  - Update:
    - PUT /api/v1/payment-descriptions/{id} { "name": "Downpayment" }
  - Delete:
    - DELETE /api/v1/payment-descriptions/{id}
  - Auth header:
    - Use X-Faculty-ID header when applicable (same as PaymentModesService _adminHeaders pattern) and role middleware role:finance,admin

- Frontend UI (if included):
  - Navigate to Finance > Payment Descriptions
  - Verify list rendering, create/edit flows, validation messages, and delete

[Implementation Order]
Create database and backend first, then wire routes, then (optionally) add the AngularJS UI, finally test end-to-end.

1) Migration
   - Add 2025_08_30_000900_create_payment_descriptions_table.php with only intID and name, unique(name), no timestamps/soft deletes
2) Model
   - Create App\Models\PaymentDescription
3) Resource and Requests
   - Add PaymentDescriptionResource, Store/Update requests with validations
4) Controller
   - Implement CRUD with consistent JSON envelopes (success, data, optional meta on pagination)
5) Routes
   - Register /api/v1/payment-descriptions endpoints in laravel-api/routes/api.php under role:finance,admin
6) Optional AngularJS UI
   - Service: payment-descriptions.service.js (based on payment-modes.service.js)
   - Controller: payment-descriptions.controller.js
   - Views: list.html and edit.html
   - Routes: add states in frontend/unity-spa/core/routes.js
   - Sidebar: add Finance menu item
7) Testing
   - Run migration; smoke REST endpoints; validate unique constraint and error handling; verify UI flows if included
