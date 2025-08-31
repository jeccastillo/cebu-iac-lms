# Invoices Implementation TODO

Scope:
- Add invoice generation function, separate invoices by type (tuition, billing, other).
- Create new table tb_mas_invoices.
- Expose finance endpoints to generate and list invoices.

Steps:
1. Migration
   - [ ] Create migration: laravel-api/database/migrations/2025_08_30_001200_create_tb_mas_invoices.php
     - [ ] Table: tb_mas_invoices
       - intID (PK), intStudentID, syid, type, status, invoice_number, amount_total,
         posted_at, due_at, remarks, payload (JSON), campus_id, cashier_id, created_by, updated_by, timestamps.
2. Model
   - [ ] Create app/Models/Invoice.php
     - Eloquent model with table tb_mas_invoices, primaryKey intID, casts payload.
3. Service
   - [ ] Create app/Services/InvoiceService.php
     - generate(type, studentId, syid, options, actorId?): array
       - type in ['tuition','billing','other']
       - tuition: load latest SavedTuition snapshot if available (or options override)
       - billing: aggregate positive charges from tb_mas_student_billing
       - other: require items or amount in options
       - Insert tb_mas_invoices with status 'Draft' or 'Issued' (from options)
4. Request
   - [ ] Create app/Http/Requests/Api/V1/InvoiceGenerateRequest.php
     - Validates: type, student_id, syid, optional items[], amount, posted_at, due_at, campus_id, remarks.
5. Controller
   - [ ] Create app/Http/Controllers/Api/V1/InvoiceController.php
     - index(): filter by student_id/student_number, syid, type, status
     - show($id)
     - generate(InvoiceGenerateRequest)
     - All routes protected by role: finance,admin
6. Routes
   - [ ] Update laravel-api/routes/api.php
     - Add use App\Http\Controllers\Api\V1\InvoiceController;
     - Add:
       - GET /api/v1/finance/invoices
       - GET /api/v1/finance/invoices/{id}
       - POST /api/v1/finance/invoices/generate
7. Optional future integration (not in this change set)
   - [ ] When CashierController creates payment with mode=invoice, update matching tb_mas_invoices row to set invoice_number and mark Paid.
8. Testing
   - [ ] php artisan migrate (inside laravel-api)
   - [ ] Generate tuition invoice
   - [ ] Generate billing invoice
   - [ ] List invoices by filters

Notes:
- Do not assign invoice_number at generation time to avoid conflicts with cashier ranges; number assignment happens on payment posting.
- Keep naming conventions consistent (intID, intStudentID, syid).
