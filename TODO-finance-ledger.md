# TODO - Finance/Admin Student Financial Activity (Ledger-like) Page

- [ ] Backend: StudentLedgerService
  - [ ] Resolve student_id from student_number when provided
  - [ ] Collect syids (if term='all') across saved tuition, student_billing, and payment_details (status='Paid')
  - [ ] Fetch latest SavedTuition per syid, extract total_due, build assessment rows
  - [ ] Fetch StudentBilling rows for syids, map positive as assessment and negative as payment
  - [ ] Fetch Paid PaymentDetails via FinanceService::listPaymentDetails and filter status='Paid'
  - [ ] Enrich payment rows with cashier name (cashier_id -> tb_mas_cashiers -> tb_mas_faculty; fallback created_by)
  - [ ] Normalize row fields (posted_at, or_no, invoice_number, assessment, payment, cashier_name, syid, sy_label)
  - [ ] Sort by posted_at asc; compute meta totals; return LedgerResponse

- [ ] Controller & Route
  - [ ] Add FinanceController::studentLedger(Request) with validation (student_number|student_id, term='all'|int, sort)
  - [ ] Register GET /api/v1/finance/student-ledger with middleware role:finance,admin

- [ ] Frontend Service
  - [ ] Create features/finance/ledger.service.js (FinanceLedgerService)
  - [ ] getLedger(params) -> calls /finance/student-ledger and returns response data
  - [ ] CSV helper for export (optional)

- [ ] Frontend Controller
  - [ ] Enhance features/finance/ledger.controller.js to bind filters, call service, and compute running balance per row
  - [ ] Integrate TermService for term selector with 'All terms' option

- [ ] Frontend Template
  - [ ] Update features/finance/ledger.html to include:
        - Student Number input, Term selector ('All terms' or specific)
        - Search button
        - Table: Transaction Date, OR Number, Invoice Number, Assessment, Payment, Cashier Name, Running Balance
        - Loading/empty states
        - Optional CSV export

- [ ] Testing
  - [ ] Validate various data mixes (only tuition; tuition+billing; payments across terms)
  - [ ] Verify posted_at mapping and cashier name fallback
  - [ ] Confirm RBAC (route requires finance/admin)
