# TODO - Non-Student Payments (tb_mas_payee) Implementation

This file tracks implementation progress for the non-student payments module.

- [ ] Step 1: Create migration to add payment_details.payee_id (nullable FK to tb_mas_payee.id)
- [ ] Step 2: Implement App\Models\Payee
- [ ] Step 3: Implement PayeeStoreRequest, PayeeUpdateRequest, PayeeResource
- [ ] Step 4: Implement PayeeController with CRUD + SystemLog integration and role middleware
- [ ] Step 5: Register /api/v1/payees routes in routes/api.php
- [ ] Step 6: Update CashierPaymentStoreRequest rules for payee_id vs student_id and term behavior
- [ ] Step 7: Update CashierController::createPayment to handle payee branch (no sy_reference; set payee_id; fill optional columns)
- [ ] Step 8: Extend PaymentDetailAdminService detectColumns/select/normalize for payee_id
- [ ] Step 9: Add payee() relation to PaymentDetail model
- [ ] Step 10: Update FinanceController::orPdf to prefer payee data when available
- [ ] Step 11: Add CLI test scripts for Payee CRUD and non-student payments; run local checks
- [ ] Step 12: Smoke-test OR/Invoice number pointer behavior for payee payments
- [ ] Step 13: Verify SystemLog entries for payee CRUD and payments; finalize docs

References:
- Plan: implementation_plan.md
- Tables: tb_mas_payee (tenants), payment_details (payments)
- Roles: admin, finance_admin (for Payees CRUD)
