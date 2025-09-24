# Invoice Reports Feature - TODO

Goal: Add a Finance page "Invoice Reports" with a date range picker to filter by payment_details.or_date, listing invoice report rows with VAT/EWT breakdown and payment method.

Scopes:
- Backend API: GET /api/v1/reports/invoices (role: finance,admin)
- Frontend SPA: /finance/invoice-reports route, service, controller, template, and sidebar link.

Tasks:
- [ ] Backend: Add route in laravel-api/routes/api.php
- [ ] Backend: Implement ReportsController::invoiceReports(Request)
  - [ ] Validate params: date_from (Y-m-d), date_to (Y-m-d)
  - [ ] Use payment_details.or_date (if exists) else fallback to detected date column
  - [ ] Filter payment_details: status='Paid', invoice_number not null, date range inclusive
  - [ ] Aggregate per invoice_number: earliest or_date (invoice_date), earliest method (MOP)
  - [ ] Join tb_mas_invoices + tb_mas_users for invoice and student fields
  - [ ] Compute columns:
        Invoice Date, Invoice Number, Student Number, Payee Name,
        Payment For, Particulars, Payment Type, MOP,
        Vatable Amount, Vat Exempt, Zero Rated, Total Sales, VAT, EWT Rate, EWT Amount, Net Amount Due
  - [ ] Ordering: invoice_date asc, invoice_number asc
  - [ ] Return JSON { success, data, meta: { count } }
- [ ] Frontend: Add route in frontend/unity-spa/core/routes.js
  - path: /finance/invoice-reports
  - requiredRoles: ["finance","admin"]
- [ ] Frontend: Add sidebar link under Finance group
  - Label: Invoice Reports
  - Path: /finance/invoice-reports
- [ ] Frontend: Create feature files
  - [ ] features/finance/invoice-reports/invoice-reports.service.js
        - GET /api/v1/reports/invoices with params and admin headers
  - [ ] features/finance/invoice-reports/invoice-reports.controller.js
        - Manage filters (date_from/date_to/type/status/campus_id)
        - Load, clear, and export CSV
  - [ ] features/finance/invoice-reports/invoice-reports.html
        - Form with date range inputs
        - Search/Clear/Export buttons
        - Table with columns:
          No, Invoice Date, Invoice Number, Student Number, Payee Name, Payment For, Particulars, Payment Type, MOP, Vatable Amount, Vat Exempt, Zero Rated, Total Sales, VAT, EWT Rate, EWT Amount, Net Amount Due
- [ ] Testing
  - [ ] Verify API returns rows for sample date range
  - [ ] Verify SPA loads data, pagination/scroll, sorting visual by date asc, and CSV export
  - [ ] Verify access control (only finance/admin)

Notes:
- Prefer payment_details.or_date when present.
- Method detection via PaymentDetailAdminService::detectColumns() (method/pmethod).
- VAT calculation: 12% of vatable amount when invoice_amount is non-null.
- EWT amount: vatable * (withholding_tax_percentage / 100) when present.
- Net amount due: (vatable + ves + vzrs) + VAT - EWT amount.
