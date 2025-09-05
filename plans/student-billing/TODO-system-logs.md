# Student Billing - System Logs TODO

Goal: Add system logs for billing create, update, and delete operations using SystemLogService.

Scope:
- File: laravel-api/app/Http/Controllers/Api/V1/StudentBillingController.php

Steps:
- [ ] Import SystemLogService in StudentBillingController.php
      use App\Services\SystemLogService;

- [ ] store(): After successful creation and optional invoice generation, log:
      SystemLogService::log('create', 'StudentBilling', $item['id'], null, $item, $request);

- [ ] update(): Capture old before update and log after update:
      $old = $this->service->get($id);
      $item = $this->service->update($id, $data, $actorId);
      SystemLogService::log('update', 'StudentBilling', $id, $old, $item, $request);

- [ ] destroy(): Use $existing already fetched, log after delete:
      $this->service->delete($id);
      SystemLogService::log('delete', 'StudentBilling', $id, $existing, null, request());

- [ ] Verify build: PHP syntax, imports, request usage.

- [ ] Manual test calls:
  - POST /api/v1/finance/student-billing (with generate_invoice true/false)
  - PUT /api/v1/finance/student-billing/{id}
  - DELETE /api/v1/finance/student-billing/{id}
  Confirm entries appear in system logs viewer.

Notes:
- SystemLogService::log gracefully handles failures; will not break main flow.
- Entity name: "StudentBilling" consistent with other controllers.
