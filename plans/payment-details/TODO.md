# Payment Details System Logging

Goal: Add system logging for add (create), edit (update), and delete actions on payment_details using SystemLogService.

## Scope
- Service: laravel-api/app/Services/PaymentDetailAdminService.php
- Controller: laravel-api/app/Http/Controllers/Api/V1/PaymentDetailAdminController.php
- System log utility: laravel-api/app/Services/SystemLogService.php

## Steps

- [ ] Wire Request propagation and add logs for update and delete:
  - [ ] In PaymentDetailAdminService:
    - [ ] Change signatures to accept optional Request:
      - update(int $id, array $payload, ?Request $request = null): array
      - delete(int $id, ?Request $request = null): void
    - [ ] For update:
      - [ ] Capture normalized old snapshot via getById($id) before update
      - [ ] Perform update transaction
      - [ ] Fetch normalized updated row via getById($id)
      - [ ] Call SystemLogService::log('update', 'PaymentDetail', $id, $old, $updated, $request)
    - [ ] For delete:
      - [ ] Capture normalized old snapshot via getById($id)
      - [ ] Perform delete transaction
      - [ ] Call SystemLogService::log('delete', 'PaymentDetail', $id, $old, null, $request)
  - [ ] In PaymentDetailAdminController:
    - [ ] update(): pass $request to service
    - [ ] destroy(): accept Request and pass to service

- [ ] Instrument creation ("add") flow:
  - [ ] Locate insertion points for payment_details creation across codebase
  - [ ] Add SystemLogService::log('create', 'PaymentDetail', $newId, null, $newValues, $request) at the point of successful insert

- [ ] Smoke tests:
  - [ ] PATCH /api/v1/finance/payment-details/{id} with some field changes and verify a SystemLog entry (action=update)
  - [ ] DELETE /api/v1/finance/payment-details/{id} and verify a SystemLog entry (action=delete)
  - [ ] Execute a flow that creates a payment_details row and verify a SystemLog entry (action=create)

## Notes
- SystemLogService already guards failures; no additional try/catch required around logging calls.
- Use normalized snapshots via PaymentDetailAdminService::getById to keep logs consistent with API payloads.
