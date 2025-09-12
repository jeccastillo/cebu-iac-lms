task_progress Items:
- [x] Step 1: Backend — create App/Services/CashierViewerAggregateService.php with buildByStudentNumber(), computeInvoiceEnrichment(), sumReservationPaid(), sumBillingPaid()
- [x] Step 2: Backend — add FinanceController::viewerData(Request) to call the service and validate inputs
- [x] Step 3: Backend — wire GET /api/v1/finance/cashier/viewer-data in routes/api.php with role:finance,admin
- [x] Step 4: Frontend — add UnityService.cashierViewerData(params) to call the new endpoint with admin headers
- [x] Step 5: Frontend — (minimal) expose vm.loadViewerData(force) in controller and prepare for integration (optional in this iteration)
- [ ] Step 6: Critical-path API tests — curl GET /finance/cashier/viewer-data for one student/term; verify invoices/payment_details/billing/missing_billing presence and enriched fields
- [ ] Step 7: Critical-path UI check — open Cashier Viewer for a student/term and verify panels render correctly using the aggregated response (after opting in)
