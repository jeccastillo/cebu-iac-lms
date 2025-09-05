# Admin Payment Details — Implementation TODO

This file tracks the execution of the implementation plan for the Admin-only Payment Details page and related Laravel API endpoints.

task_progress Items:
- [x] Step 1: Backend service — create laravel-api/app/Services/PaymentDetailAdminService.php with detectColumns(), search(), getById(), update() including number uniqueness checks and posted_at coalescing
- [ ] Step 2: Backend request — create laravel-api/app/Http/Requests/Api/V1/PaymentDetailUpdateRequest.php with conditional validation rules (subtotal_order, total_amount_due, status, method/payment_method, posted_at, mode_of_payment_id, OR/Invoice number fields)
- [ ] Step 3: Backend controller — create laravel-api/app/Http/Controllers/Api/V1/PaymentDetailAdminController.php with index(), show($id), update($id) enforcing admin authorization (CodeIgniterSessionGuard/UserContextResolver)
- [ ] Step 4: Backend routes — edit laravel-api/routes/api.php to register:
      - GET /api/v1/finance/payment-details/admin (index)
      - GET /api/v1/finance/payment-details/{id} (show)
      - PATCH /api/v1/finance/payment-details/{id} (update)
- [ ] Step 5: Frontend service — create frontend/unity-spa/features/admin/payment-details/payment-details.service.js with search(params), get(id), update(id, payload) targeting the new Laravel endpoints
- [ ] Step 6: Frontend controller — create frontend/unity-spa/features/admin/payment-details/payment-details.controller.js (AdminPaymentDetailsController) to manage filters, results, selection, edit modal/drawer, and save flow; enforce admin-only access via RoleService
- [ ] Step 7: Frontend view — create frontend/unity-spa/features/admin/payment-details/edit.html using Tailwind classes; include filters (q, student_number, term, mode, number fields, status, dates), table with pagination, and edit modal/drawer
- [ ] Step 8: Frontend routes — update frontend/unity-spa/core/routes.js to add route /admin/payment-details with requiredRoles: ["admin"]
- [ ] Step 9: Sidebar — update frontend/unity-spa/shared/components/sidebar/sidebar.controller.js to add Admin → { label: "Payment Details", path: "/admin/payment-details" }
- [ ] Step 10: Critical-path QA — verify access control, search → view/edit → save → refresh flow; validate required fields and error handling (422/403/404)

Notes:
- Tailwind CSS utility classes are used throughout the SPA. The new page will follow existing styling conventions.
- Column detection in backend should gracefully handle environments with or_no vs or_number, method vs payment_method, and paid_at/date/created_at.
- All admin endpoints must return appropriate error codes and messages; SPA surfaces them via toast/service error handling.
