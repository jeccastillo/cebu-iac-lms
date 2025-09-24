# OR Reports Feature - TODO

Status: Completed

- [x] Backend: Add GET /api/v1/reports/official-receipts (role: finance,admin)
  - [x] Implement ReportsController::orReports with schema-safe mapping via PaymentDetailAdminService
  - [x] Filter by date range (prefer payment_details.or_date when present; else paid_at/date/created_at)
  - [x] Include only rows with existing OR number and status='Paid' (when column exists)
  - [x] Resolve fields: or_date, or_number, invoice_number, student_number, payee_name (payee or student), payment_for, particulars, payment_received
  - [x] Optional campus filter via payment_details.student_campus or join to tb_mas_users.campus_id
  - [x] Route registered in routes/api.php

- [x] Frontend: Create OR Reports page
  - [x] Service: features/finance/or-reports/or-reports.service.js
  - [x] Controller: features/finance/or-reports/or-reports.controller.js
  - [x] Template: features/finance/or-reports/or-reports.html
  - [x] Wire route: /finance/or-reports (core/routes.js)
  - [x] Add sidebar menu entry under Finance
  - [x] Include scripts in index.html

- [x] CSV Export
  - [x] Implement CSV export in controller with columns:
        No, OR Date, OR Number, Invoice Number, Student Number, Payee Name, Payment For,
