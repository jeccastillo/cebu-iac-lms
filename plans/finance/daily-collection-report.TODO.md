# Daily Collection Report (payment_details, Paid only)

Goal: Add a daily collection report for payment_details table focusing on status='Paid' transactions, exposed via a new API endpoint.

Approved Plan Summary:
- Implement ReportsController::dailyCollections(Request): JsonResponse
- Add route: GET /api/v1/reports/daily-collections (role: finance,admin)
- Use PaymentDetailAdminService::detectColumns() for schema-safe column detection.
- Prefer date column: 'or_date' if exists; else use detectColumns()['date'] (paid_at/date/created_at).
- Filters: status='Paid' (when status exists), inclusive date range, optional campus_id (student_campus field or join users), optional cashier_id (when payment_details.cashier_id exists).
- Output JSON:
  {
    "date_from": "Y-m-d",
    "date_to": "Y-m-d",
    "meta": { "count_rows": int, "grand_total": float },
    "daily": [
      {
        "date": "Y-m-d",
        "total_paid": float,
        "count_paid": int,
        "by_method": { "<method>": float, ... },
        "by_cashier": [ { "cashier_id": int, "total": float }, ... ] // only if cashier_id column exists
      }
    ]
  }

Tasks:
- [x] 1. Implement ReportsController::dailyCollections
  - [x] 1.1 Validate inputs: date_from (Y-m-d), date_to (Y-m-d, >= date_from), campus_id? int, cashier_id? int
  - [x] 1.2 Detect columns via PaymentDetailAdminService::detectColumns()
  - [x] 1.3 Choose date column: prefer 'or_date' else cols['date']
  - [x] 1.4 Build base query on payment_details with filters:
        - status = 'Paid' (if cols['status'])
        - date range on chosen date column (inclusive)
        - campus filter: student_campus OR join tb_mas_users on student_id
        - cashier filter: when cashier_id column exists
  - [x] 1.5 Select minimal fields: DATE(dateCol) as d, subtotal_order, method (if exists), cashier_id (if exists)
  - [x] 1.6 Aggregate per day in PHP: totals, counts, by_method, by_cashier
  - [x] 1.7 Compute meta: count_rows and grand_total
  - [x] 1.8 Return JSON payload as specified

- [x] 2. Wire the route in laravel-api/routes/api.php
  - [x] 2.1 Add: Route::get('/reports/daily-collections', [ReportsController::class, 'dailyCollections'])->middleware('role:finance,admin');

- [ ] 3. Smoke test
  - [ ] 3.1 GET /api/v1/reports/daily-collections?date_from=YYYY-MM-DD&amp;date_to=YYYY-MM-DD
  - [ ] 3.2 Test with campus filter if available: &amp;campus_id=1
  - [ ] 3.3 Test with cashier filter if available: &amp;cashier_id=1

Notes:
- subtotal_order is summed directly for status='Paid' credits (aligned with PaymentJournalService behavior).
- No join to tb_mas_cashiers for names (schema varies); return cashier_id only if present.
- Future enhancement (optional): include_items boolean for raw rows; CSV/Excel export.
