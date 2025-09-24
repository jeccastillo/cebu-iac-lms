task_progress Items:
- [x] Step 1: Add backend PaymentDetailsTemplateExport and PaymentDetailsImportService (parse/import logic with invoice ensure)
- [x] Step 2: Add backend PaymentDetailsImportController endpoints and wire routes with finance_admin,admin roles
- [x] Step 3: Add frontend page (HTML), service, controller, and route registration; link in sidebar for admin/finance_admin
- [ ] Step 4: Add a backend test script to exercise import flow; manually validate via API and UI
- [ ] Step 5: Finalize error handling, logging, and documentation

Details:
- Backend Step 1:
  - Create laravel-api/app/Exports/PaymentDetailsTemplateExport.php
  - Create laravel-api/app/Services/PaymentDetailsImportService.php (parseXlsx, ensureInvoiceExists, mapRowToPaymentDetailPayload, insertPaymentDetail, import)
- Backend Step 2:
  - Create laravel-api/app/Http/Controllers/Api/V1/PaymentDetailsImportController.php (template, import)
  - Update laravel-api/routes/api.php with:
    - GET /api/v1/finance/payment-details/import/template (role:finance_admin,admin)
    - POST /api/v1/finance/payment-details/import (role:finance_admin,admin)
- Frontend Step 3:
  - Create frontend/unity-spa/features/admin/payment-details/import/payment-details-import.html
  - Create frontend/unity-spa/features/admin/payment-details/import/payment-details-import.service.js
  - Create frontend/unity-spa/features/admin/payment-details/import/payment-details-import.controller.js
  - Create optional frontend/unity-spa/features/admin/payment-details/import/payment-details-import.route.js
  - Update frontend/unity-spa/index.html to include scripts
  - Update frontend/unity-spa/shared/components/sidebar/sidebar.html to add link (admin/finance_admin)
- Testing Step 4:
  - Create laravel-api/scripts/test_payment_details_import.php
  - Add sample file in plans/payment-details-import/sample/payment-details-import-sample.xlsx (optional)
- Polish Step 5:
  - Improve error reporting (line numbers, codes)
  - Guard duplicate OR on insert
  - Large file notes and documentation
