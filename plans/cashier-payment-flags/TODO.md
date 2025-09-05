# Cashier Applicant Payment Flags - TODO

Goal:
When a cashier submits a payment in the Cashier Viewer:
- If the description indicates an Application fee, set tb_mas_applicant_data.paid_application_fee = 1 for the payee.
- If the description indicates a Reservation payment, set tb_mas_applicant_data.paid_reservation_fee = 1 for the payee.

Steps:
- [ ] Backend: Add a post-save hook in `laravel-api/app/Http/Controllers/Api/V1/CashierController.php::createPayment()` to update applicant_data flags.
  - [ ] Determine fee type based on `description`:
    - Exact matches (case-insensitive): "Application Payment", "Application Fee" -> `paid_application_fee`
    - Exact match (case-insensitive): "Reservation Payment" -> `paid_reservation_fee`
    - Fallback: contains "application" or "reservation" in description
  - [ ] Guard checks:
    - Ensure `tb_mas_applicant_data` table exists
    - Ensure target flag column exists
  - [ ] Update logic:
    - Prefer row with matching `user_id` and `syid` (from the same request term), if `syid` column exists
    - Fallback: update latest row by `user_id` (ORDER BY id DESC) if no `syid` match
    - Non-blocking: wrap in try/catch; do not interrupt payment creation flow on failures
  - [ ] Optional logging:
    - Use `SystemLogService::log('update', 'ApplicantPaymentFlag', student_id, null, ['flag' => flag, 'value' => 1, 'syid' => $syid], $request)`

Testing:
- [ ] In the Cashier Viewer, submit a payment with description "Application Payment" for an applicant; verify via `GET /api/v1/applicants/{id}` that `paid_application_fee` is `true`.
- [ ] Submit a payment with description "Reservation Payment"; verify `paid_reservation_fee` is `true`.
- [ ] Verify both cases with and without applicant_data.syid present.
- [ ] Ensure payment creation, pointer increments, and system logs remain unaffected.

Notes:
- Descriptions default in UI include "Application Payment" and "Reservation Payment"; support "Application Fee" for robustness.
- The logic should not error even if applicant_data does not exist or schema varies between environments.
