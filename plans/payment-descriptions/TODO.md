# Payment Descriptions TODO

Scope: Complete AngularJS admin UI and wiring for Payment Descriptions, leveraging existing backend and Payment Modes patterns.

Status Legend:
- [ ] Pending
- [x] Done

## Steps

1) Service
- [x] Add frontend/unity-spa/features/finance/payment-descriptions/payment-descriptions.service.js
  - Methods: list, show, create, update, remove
  - Include X-Faculty-ID header via StorageService
  - Filters: search, sort, order, page, per_page

2) Controllers
- [x] Add PaymentDescriptionsController (list)
  - Search, sort by name, pagination, add/edit/delete actions
- [x] Add PaymentDescriptionEditController (create/edit)
  - Single field model: name
  - Client-side validation and save/cancel flows

3) Views
- [x] Add frontend/unity-spa/features/finance/payment-descriptions/list.html
  - Search input, Add button, table (ID, Name, Actions), pagination controls
- [x] Add frontend/unity-spa/features/finance/payment-descriptions/edit.html
  - Form with single Name input, Save/Cancel buttons

4) Routes
- [x] Update frontend/unity-spa/core/routes.js
  - Add routes:
    - /finance/payment-descriptions/new -> edit.html (PaymentDescriptionEditController)
    - /finance/payment-descriptions/:id/edit -> edit.html (PaymentDescriptionEditController)
  - requiredRoles: ["finance", "admin"]

5) Smoke Test
- [ ] Load list page, verify fetch and search
- [ ] Create description and verify duplicate validation
- [ ] Edit existing description
- [ ] Delete description (hard delete as per API)

Notes:
- Backend routes and controllers are already complete and protected by role:finance,admin.
- Sidebar entry for Payment Descriptions exists and points to /finance/payment-descriptions.
- Align UX with Payment Modes for consistency.
- Index wiring: added scripts for Payment Descriptions service and controller in frontend/unity-spa/index.html.
- Routes.js verified with Payment Descriptions list/new/edit routes; stray leading comma at file start resolved.
