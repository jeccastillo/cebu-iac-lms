# Implementation Plan

[Overview]
Create an admin/finance_admin feature to upload an Excel file that imports payment_details rows; during import, if a provided invoice_number does not exist in tb_mas_invoices, automatically create an invoice with the correct type and amount based on row fields.

This feature introduces a backend import API and a frontend admin tool consistent with existing import flows (subjects, classlists, schedules, students). The backend relies on schema-safe mapping via PaymentDetailAdminService::detectColumns to adapt to environment differences, and leverages InvoiceService to ensure invoice existence. The importer supports upsert semantics: insert by default; when an id column is present, update that existing payment_details row instead. The minimal required identifier in the template is student_number; all other columns are optional. The API is restricted to roles admin and finance_admin.

[Types]
Define import payload structure and type mapping for schema-safe insert/update.

- Import Row (Excel headers, used as associative keys):
  - id: int (optional). If present, update that payment_details row; otherwise insert a new row.
  - student_number: string (required). Used to resolve student_id for invoice creation via tb_mas_users.strStudentNumber.
  - syid: int (optional). If present, used on invoice row; otherwise null.
  - description: string (optional). Used for invoice type classification and stored in payment_details when column exists.
  - subtotal_order: float (optional). Used for payment amount and invoice amount basis (per spec).
  - total_amount_due: float (optional). Ignored for invoice amount calculation (still written if column exists).
  - method or payment_method: string (optional). Written through detectColumns() mapping.
  - mode_of_payment_id: int (optional). Written through detectColumns() mapping.
  - status: string (optional). Written through detectColumns() mapping.
  - posted_at: string (datetime; optional). Mapped to paid_at/date/created_at as available via detectColumns()->date.
  - or_no or or_number: string|int (optional). If OR column exists, uniqueness is enforced by PaymentDetailAdminService::update path; on insert, we will preflight-validate uniqueness if possible.
  - invoice_number: string|int (optional). If present and not found in tb_mas_invoices, an invoice is auto-created and the same invoice_number is stored on the payment row when the column exists.
  - remarks: string (optional). Written through detectColumns() mapping.

- Invoice auto-creation classification (from description):
  - If description == "Application Payment" → type "application payment"
  - If description == "Reservation Payment" → type "reservation payment"
  - If description contains "Tuition" (case-insensitive) → type "tuition"
  - Else → type "billing"
  - Invoice amount_total: from subtotal_order (per spec).
  - Invoice status: "Issued".
  - Invoice number: use provided invoice_number from the row.
  - Invoice syid: from row syid when provided; otherwise null.
  - Student linkage: resolve student_id via tb_mas_users.strStudentNumber = student_number; if not found → error for that row and skip.

[Files]
Add new backend service and controller for import/template, wire routes, and add a frontend page for admins/finance_admin.

- New files to be created:
  - laravel-api/app/Services/PaymentDetailsImportService.php
    - Purpose: Parse Excel files, validate and normalize rows, ensure invoice existence, and insert/update payment_details via schema-safe mapping.
  - laravel-api/app/Http/Controllers/Api/V1/PaymentDetailsImportController.php
    - Purpose: Expose template and import endpoints: GET /api/v1/finance/payment-details/import/template and POST /api/v1/finance/payment-details/import.
  - laravel-api/app/Exports/PaymentDetailsTemplateExport.php
    - Purpose: Build an Excel template with correct headers and sample hints, leveraging PhpSpreadsheet like other template exports.
  - frontend/unity-spa/features/admin/payment-details/import/payment-details-import.html
    - Purpose: UI for template download, file select/upload, and import summary/error display.
  - frontend/unity-spa/features/admin/payment-details/import/payment-details-import.service.js
    - Purpose: Angular service for calling template and import APIs.
  - frontend/unity-spa/features/admin/payment-details/import/payment-details-import.controller.js
    - Purpose: Angular controller handling file selection, upload, and UI state.
  - frontend/unity-spa/features/admin/payment-details/import/payment-details-import.route.js (optional depending on routing pattern)
    - Purpose: Register route "#/admin/payment-details/import".
  - plans/payment-details-import/sample/payment-details-import-sample.xlsx (optional)
    - Purpose: Example file used by test script.

- Existing files to be modified:
  - laravel-api/routes/api.php
    - Add:
      - Route::get('/finance/payment-details/import/template', [PaymentDetailsImportController::class, 'template'])->middleware('role:finance_admin,admin');
      - Route::post('/finance/payment-details/import', [PaymentDetailsImportController::class, 'import'])->middleware('role:finance_admin,admin');
  - frontend/unity-spa/index.html
    - Add script tags for new controller/service/route js files in a section consistent with other features.
  - frontend/unity-spa/shared/components/sidebar/sidebar.html
    - Add menu link "Payment Details Import" visible to admin and finance_admin only.
  - plans/ (documentation only, no code execution impact)

- Files to be deleted or moved:
  - None.

- Configuration updates:
  - None required; reuse existing PhpSpreadsheet dependency and ng-file-upload in frontend.

[Functions]
Introduce import orchestration and template generation; reuse existing schema-safe update logic.

- New functions:
  - PaymentDetailsTemplateExport
    - __construct(): initialize structure and headers
    - toSpreadsheet(): Spreadsheet
      - File: laravel-api/app/Exports/PaymentDetailsTemplateExport.php
      - Purpose: Return a Spreadsheet with headers:
        id, student_number, syid, description, subtotal_order, total_amount_due, method, mode_of_payment_id, status, posted_at, or_no, or_number, invoice_number, remarks
  - PaymentDetailsImportService
    - parseXlsx(string $path): iterable<array>
      - Purpose: Open file via PhpSpreadsheet and yield associative rows by header (lowercased snake-case normalization).
    - ensureInvoiceExists(array $row, int $studentId): ?array
      - Purpose: If row['invoice_number'] present and not found, call InvoiceService->generate with:
        type (classified from description), studentId, syid (if present), options including 'invoice_number' and 'amount' from subtotal_order, status "Issued".
        Returns normalized invoice or null on no-op.
    - mapRowToPaymentDetailPayload(array $row, array $cols): array
      - Purpose: Build a payload matching PaymentDetailAdminService::update accepted keys using detectColumns() mapping.
    - insertPaymentDetail(array $payload, array $cols): int
      - Purpose: Insert a new payment_details row with schema-safe column mapping, returning new id.
    - import(string $path, array $options = []): array
      - Purpose: Orchestrate parsing, validation, student lookup, invoice ensure, and insert/update:
        - Counters: totalRows, inserted, updated, skipped; errors[] as {line, code?, message}
        - Update path uses PaymentDetailAdminService::update($id, $payload, null)
        - Insert path uses DB::table with $cols mapping
        - Validations:
          - student_number required and must resolve to a user
          - if invoice_number present and student resolves, ensure invoice via ensureInvoiceExists()
          - if or_no/or_number present and column exists, pre-check duplicates on insert (update path duplicate check handled by service)
  - PaymentDetailsImportController
    - template(): StreamedResponse
      - Purpose: Return xlsx template as download (like other template controllers).
    - import(Request $request): JsonResponse
      - Purpose: Accept file upload (xlsx/xls/csv), store temp, call service->import, return summary JSON.

- Modified functions:
  - None in existing controllers/services besides adding routes and including script tags and sidebar link.

- Removed functions:
  - None.

[Classes]
Add import service and template export analogs to existing import/template patterns.

- New classes:
  - App\Services\PaymentDetailsImportService
    - Key methods: parseXlsx, ensureInvoiceExists, mapRowToPaymentDetailPayload, insertPaymentDetail, import
    - Dependencies: PhpSpreadsheet; DB; PaymentDetailAdminService; InvoiceService
  - App\Exports\PaymentDetailsTemplateExport
    - Key methods: toSpreadsheet
    - Inheritance: none; follow existing Export classes style

- Modified classes:
  - None (InvoiceService and PaymentDetailAdminService remain unchanged and are reused).

- Removed classes:
  - None.

[Dependencies]
No new packages; reuse existing libraries.

- Backend:
  - PhpOffice\PhpSpreadsheet: already used by other import/template services.
- Frontend:
  - ng-file-upload (present in index.html).
- No composer.json changes expected.

[Testing]
Introduce a backend script and manual validation paths; leverage existing test patterns.

- Backend script:
  - laravel-api/scripts/test_payment_details_import.php
    - Load Laravel kernel; invoke PaymentDetailsImportService->import on a sample file.
    - Verify counters and sample assertions:
      - When invoice_number is new: tb_mas_invoices has a row with provided invoice_number, status Issued, type classified from description, amount_total == subtotal_order.
      - Created/Updated payment_details reflect mapped fields.
- API manual tests (Postman/curl):
  - GET /api/v1/finance/payment-details/import/template → returns xlsx.
  - POST /api/v1/finance/payment-details/import with multipart file → returns JSON summary with counters and errors.
- UI manual tests:
  - Navigate to #/admin/payment-details/import as admin/finance_admin:
    - Download template.
    - Upload sample; verify Import Summary (Total/Inserted/Updated/Skipped; error list truncated with top N e.g., 50).
  - Verify new invoice in /api/v1/finance/invoices list when expected.

[Implementation Order]
Implement backend import service/controllers first, then integrate frontend UI, followed by testing.

1. Backend: PaymentDetailsTemplateExport and PaymentDetailsImportService with parse/import and ensureInvoiceExists logic.
2. Backend: PaymentDetailsImportController (template/import endpoints) and laravel-api/routes/api.php updates with role:finance_admin,admin.
3. Frontend: Add payment-details-import.html, controller.js, service.js, and route.js; add script tags to index.html; add sidebar link visible to admin and finance_admin.
4. Testing: Add laravel-api/scripts/test_payment_details_import.php; create a sample xlsx; perform manual API and UI validation.
5. Polish: Improve error messages (line numbers, codes), handle big files (row limit checks), guard duplicates (OR number uniqueness).
6. Documentation: Place sample and any additional notes in plans/payment-details-import/.
