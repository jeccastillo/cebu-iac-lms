# Student Finances Feature — TODO and Progress

This TODO tracks implementation of the unified Student/Applicant Finances page and student-safe APIs as per plans/student-finances/implementation_plan.md.

task_progress Items:
- [ ] Step 1: Add StudentFinancesController with summary, invoices, and paymentModes endpoints
- [ ] Step 2: Add DataFetcherService::getRegistrationMeta(studentId, syid)
- [ ] Step 3: Add helper service for invoice remaining/paid totals (student-safe) to avoid risky edits on existing InvoiceService
- [ ] Step 4: Register routes for /student/finances/* in routes/api.php within the v1 group
- [ ] Step 5: Update PaymentGatewayController::checkout to validate invoice overpay using subtotal (exclude charge and mailing fee) and persist syid/invoice_number
- [ ] Step 6: Add SPA route /student/finances in frontend/unity-spa/core/routes.js
- [ ] Step 7: Create student-finances.service.js (API wrappers, charge computation, checkout payload)
- [ ] Step 8: Create student-finances.controller.js (page orchestration, selection logic, validations, checkout)
- [ ] Step 9: Create finances.html (summary, invoices with remaining, payments list, pay online panel)
- [ ] Step 10: Include student finances scripts in frontend/unity-spa/index.html and add optional “View Finances” link from student dashboard
- [ ] Step 11: Smoke test critical flows (summary load, invoices incl. Draft, modes excluding Onsite, overpay guard, partial tuition rule)

Notes:
- Use the global term selector (TermService) for term context.
- Include Draft and Issued invoices in listings.
- Partial tuition only when registration.paymentType === 'partial'; compute installment suggestions similar to Cashier Viewer.
- Exclude Onsite from payment modes; enable Paynamics, BDO, Maxx.
- Attach syid and invoice_number to checkout and enforce server-side invoice overpay guard using subtotal only (excluding gateway charges and mailing fee).
