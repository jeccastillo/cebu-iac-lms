# Payment Details: Debit/Credit — Critical-Path Testing

This guide provides critical-path tests for the new Debit/Credit functionality across API and UI. Use these steps to verify end-to-end behavior without exercising every edge case.

API Base (adjust if needed):
- http://localhost/iacademy/cebu-iac-lms/laravel-api/public/api/v1

Headers (adjust as needed for your environment):
- Content-Type: application/json
- Optional actor header if applicable in your environment: X-Faculty-ID: 1

Assumptions:
- A valid student exists with:
  - student_id: 12345 (replace accordingly)
  - term (syid): 20241 (replace accordingly)
- Optionally, an invoice exists:
  - invoice_id: 1001 or invoice_number: 2024000123 (replace accordingly)

Note: The Debit and Credit endpoints do not consume or assign OR/Invoice numbers. Credits with an invoice link enforce remaining (if an invoice total is discoverable).

---

## 1. API: Create Debit (Journal)

Purpose:
- Creates a payment_details row with negative subtotal_order (increasing balance).
- status = "Journal".
- No OR/Invoice number is assigned or consumed.
- Optional invoice linkage via invoice_id or invoice_number.

Request:
```
curl -X POST "http://localhost/iacademy/cebu-iac-lms/laravel-api/public/api/v1/finance/payment-details/debit" ^
  -H "Content-Type: application/json" ^
  -H "X-Faculty-ID: 1" ^
  -d "{
    \"student_id\": 12345,
    \"term\": 20241,
    \"amount\": 500.00,
    \"description\": \"Manual debit adjustment - books\",
    \"remarks\": \"DEBIT: books\",
    \"method\": \"none\",
    \"posted_at\": \"2025-01-05T10:00:00\",
    \"campus_id\": 1,
    \"mode_of_payment_id\": null,
    \"invoice_number\": 2024000123
  }"
```

Expected:
- 201 Created
- Response data includes:
  - entry_type = "debit"
  - amount = -500
  - source = "payment_details"
  - invoice_number = 2024000123 (when supplied)
  - posted_at set based on provided posted_at or server time
- Database:
  - payment_details.subtotal_order = -500
  - payment_details.status = "Journal"
  - No OR number and no sequence consumption

---

## 2. API: Create Credit (Paid, numberless)

Purpose:
- Creates a payment_details row with positive subtotal_order (decreasing balance).
- status = "Paid".
- Does not consume or assign OR/Invoice numbers.
- If invoice linked and invoice total available, enforce that amount ≤ remaining.

Request (without invoice linkage):
```
curl -X POST "http://localhost/iacademy/cebu-iac-lms/laravel-api/public/api/v1/finance/payment-details/credit" ^
  -H "Content-Type: application/json" ^
  -H "X-Faculty-ID: 1" ^
  -d "{
    \"student_id\": 12345,
    \"term\": 20241,
    \"amount\": 300.00,
    \"description\": \"Manual credit adjustment\",
    \"remarks\": \"CREDIT: adjustment\",
    \"method\": \"none\",
    \"posted_at\": \"2025-01-05T10:05:00\"
  }"
```

Request (with invoice linkage and enforcement):
```
curl -X POST "http://localhost/iacademy/cebu-iac-lms/laravel-api/public/api/v1/finance/payment-details/credit" ^
  -H "Content-Type: application/json" ^
  -H "X-Faculty-ID: 1" ^
  -d "{
    \"student_id\": 12345,
    \"term\": 20241,
    \"amount\": 250.00,
    \"description\": \"Manual credit - apply to invoice\",
    \"invoice_number\": 2024000123,
    \"enforce_invoice_remaining\": true
  }"
```

Expected:
- 201 Created
- Response data includes:
  - entry_type = "credit"
  - amount = +300 or +250
  - source = "payment_details"
  - invoice_number as provided in the invoice test
- If the credit amount exceeds invoice remaining (and invoice totals can be determined):
  - 422 Unprocessable Entity with validation error on amount.

---

## 3. API: Verify Payment Details Listing (Existing endpoint)

If applicable in your environment (e.g., FinanceService::listPaymentDetails), verify that new entries appear:

Example:
```
curl -X GET "http://localhost/iacademy/cebu-iac-lms/laravel-api/public/api/v1/finance/payment-details?student_id=12345&term=20241" ^
  -H "Accept: application/json"
```

Expected:
- items includes the two created entries (one debit, one credit).
- meta.total_paid_filtered should reflect only Paid credits (not the Journal debit).

Note: Exact endpoint and parameters may vary; use the existing listing endpoint in your app (if present).

---

## 4. Ledger View Verification (Backend Integration)

If StudentLedgerService is exposed via an endpoint (e.g., GET /finance/ledger or via a viewer), verify:

- Debit entry (Journal) appears as an assessment/charge row (increases balance).
- Credit entry (Paid) appears as a payment row (decreases balance).
- Ordering is correct by posted_at (or or_date if present) and consistent with existing tie-breakers.

---

## 5. UI: Finance > Debit/Credit Page

Navigation:
- Ensure the Finance > Debit/Credit link is visible for roles finance and admin.
- Route: #/finance/debit-credit

Forms:
- Required fields validation:
  - Debit: student, term, amount > 0, description
  - Credit: same as debit
- Optional fields:
  - remarks, method (e.g., 'none'), posted_at
  - invoice_id or invoice_number (for allocation)
- Submit Debit:
  - Success toast
  - Refresh payment details list
  - New row visible with negative amount and remarks tag (if provided/auto-tagged)
- Submit Credit:
  - Success toast
  - Refresh payment details list
  - Enforce invoice remaining if invoice linked; show validation message on rejection.

Smoke checks:
- No OR or Invoice sequence numbers incremented by these actions.
- Timestamps render as expected in lists.
- Refresh uses existing services and shows up-to-date state.

---

## 6. Cleanup (Optional)

- Reverse test data by deleting rows from payment_details (if admin flows support it) or by test DB rollback.
- Alternatively, use admin tools (PaymentDetailAdmin) to remove test entries.

---

## Appendix: Sample JSON Payloads

Debit:
```
{
  "student_id": 12345,
  "term": 20241,
  "amount": 500.00,
  "description": "Manual debit - books",
  "remarks": "DEBIT: books",
  "method": "none",
  "posted_at": "2025-01-05T10:00:00",
  "campus_id": 1,
  "mode_of_payment_id": null,
  "invoice_number": 2024000123
}
```

Credit:
```
{
  "student_id": 12345,
  "term": 20241,
  "amount": 300.00,
  "description": "Manual credit adjustment",
  "remarks": "CREDIT: adjustment",
  "method": "none",
  "posted_at": "2025-01-05T10:05:00"
}
```

Credit with invoice enforcement:
```
{
  "student_id": 12345,
  "term": 20241,
  "amount": 250.00,
  "description": "Manual credit - apply to invoice",
  "invoice_number": 2024000123,
  "enforce_invoice_remaining": true
}
