# Implementation Plan

[Overview]
Implement Invoice PDF generation using setasign/fpdf + setasign/fpdi (same stack used by regForm), replacing the current Snappy view-based approach, and render a Letter-sized, Helvetica invoice that matches the provided screenshot layout.

The current InvoiceController::pdf() uses barryvdh/laravel-snappy to render a Blade view named pdf.invoice which does not exist. This plan introduces a dedicated FPDI-based renderer that draws the invoice directly onto a PDF canvas. The output will be an inline-streamed PDF with correct headers, consumed by the existing frontend AdminInvoicesService.pdf method. The layout will follow the screenshot: top-right Invoice No and Date, recipient name, a main line item (UG Tuition Fee / {term}) with Qty=1 and a right-aligned Price/Amount, a bold Total, and the signature name area bottom-right. Currency is formatted as 1,234.00. Page size is Letter, font is Helvetica.

[Types]  
Define small DTOs and helpers for predictable rendering.

- InvoiceDTO (array shape used by renderer)
  - id: int
  - invoice_number: int|string|null
  - posted_at: string|null (expected format: Y-m-d or Y-m-d H:i:s; renderer formats as m/d/Y)
  - student_name: string (Last, First Middle) – derived in controller from tb_mas_users where possible
  - syid: int|null (used to resolve a display term, optional)
  - type: string ('tuition'|'billing'|'other')
  - status: string
  - amount_total: float
  - remarks: string|null
  - payload: array|null (service normalizeRow already decodes JSON)
  - items: array<ItemLine> (resolved from payload.items/invoice_items, or synthesized)

- ItemLine
  - description: string (required)
  - qty: float (default 1)
  - price: float (unit price; default equals amount for qty=1)
  - amount: float (line total = qty * price; computed if missing)

Validation and normalization rules:
- If invoice has no items but has amount_total, renderer synthesizes a single item:
  - description = 'UG Tuition Fee / {TERM}' for type='tuition' when possible; fallback 'Invoice amount'
  - qty = 1
  - price = amount_total
  - amount = amount_total
- Money format: number_format(value, 2, '.', ',').
- Date format: posted_at shown as m/d/Y; if null, use created_at when available; else blank.

[Files]
Introduce a PDF renderer service and update the controller endpoint to use it.

- New files:
  - laravel-api/app/Services/Pdf/InvoicePdf.php
    - Purpose: Encapsulate FPDI drawing logic for invoices. Returns a binary PDF string via Output('S').

- Modified files:
  - laravel-api/app/Http/Controllers/Api/V1/InvoiceController.php
    - Replace Snappy-based code in pdf() with:
      - Data normalization and display-field resolution (invoice number, date, name, term label).
      - Build items[] as per rules above.
      - Call App\Services\Pdf\InvoicePdf::render($dto) to get content string.
      - Stream inline via response($content, 200, headers).
    - Remove Snappy import use Barryvdh\Snappy\Facades\SnappyPdf as PDF; from this method.

- Files to be deleted or moved:
  - None. Keep Snappy dependency and facade for other potential PDFs; just stop using it for invoices.

- Configuration updates:
  - None. Composer already includes setasign/fpdf and setasign/fpdi. No config changes needed.

[Functions]
Add a dedicated renderer API and minimally refactor controller logic.

- New functions:
  - App\Services\Pdf\InvoicePdf::render(array $dto): string
    - Inputs:
      - $dto keys:
        - number (string|int|null)
        - date (string|null, already formatted m/d/Y)
        - student_name (string)
        - term_label (string|null)
        - items: array<ItemLine> with description, qty, price, amount
        - total: float
        - footer_name (string|null) optional printed name at bottom-right (leave blank if n/a)
    - Behavior:
      - Create FPDI('P','mm','Letter'), AddPage('P', 'Letter'), SetTextColor(0,0,0), base font Helvetica.
      - Draw header:
        - Invoice No: top-right (x=165,y=10)
        - Date: near invoice no (x=165,y=16)
        - Student Name: top-left (x=10,y=16)
        - Optional Term label under the name (x=10,y=22)
      - Draw items table header - columns:
        - Description (x=10, w=130mm), Qty (w=15mm), Price (w=25mm), Amount (w=25mm)
      - Render each item row starting y=40 with line height h=6.
      - Draw Total aligned to the amount column bottom (bold/underline).
      - Reproduce the big amount repetitions shown in the screenshot by echoing the total at three anchors on the right:
        - near mid body (e.g., x=165,y=70)
        - just above the final total (e.g., x=165,y=120)
        - bottom-right summary line (x=165,y=240)
      - Draw a signature-name area on bottom-right (y≈250) to print footer_name (if provided).
      - Return $pdf->Output('S').

  - App\Http\Controllers\Api\V1\InvoiceController::pdf($id, Request $request)
    - Modified behavior:
      - Resolve invoice via $this->svc->get($id)
      - Resolve items (from payload.items or invoice_items) and normalize qty/price/amount
      - Resolve student/display data:
        - student_name via tb_mas_users (Last, First Middle) for the invoice's intStudentID
        - term_label via tb_mas_sy by syid (enumSem + YearStart-YearEnd) when available
      - $dto built for renderer and inline stream response with filename "invoice-{numberOrId}.pdf"

- Removed functions:
  - None.

[Classes]
Introduce one new class to organize PDF responsibilities.

- New classes:
  - App\Services\Pdf\InvoicePdf
    - Methods: render(array $dto): string
    - No inheritance; uses FPDI directly.
    - Key private helpers:
      - money(float $v): string
      - dateStr(?string $iso): string (expects m/d/Y already; minimal)
      - text(x,y,string,fontStyle,size,align='L')
      - row rendering with proper alignment and column widths.

- Modified classes:
  - App\Http\Controllers\Api\V1\InvoiceController: update pdf() to delegate to the renderer.

- Removed classes:
  - None.

[Dependencies]
No new Composer packages; reuse existing PDF libs.

- Continue using:
  - setasign/fpdf ^1.8
  - setasign/fpdi ^2.5
- Stop using Snappy for invoices; do not remove the package (barryvdh/laravel-snappy) as it may be used elsewhere.

[Testing]
Manual validation and frontend integration checks.

- Backend smoke:
  - GET /api/v1/finance/invoices/{id}/pdf with role finance/admin should:
    - Return 200
    - Headers: Content-Type: application/pdf; Content-Disposition: inline; filename="invoice-{noOrId}.pdf"
    - Render Letter page with Helvetica.
    - Layout:
      - Top-right: Invoice No and Date
      - Top-left: Student name
      - Items grid (Description, Qty, Price, Amount)
      - Total repeated on the right positions similar to screenshot
      - Bottom-right signature name (leave blank for now)
  - Edge cases:
    - Missing items but amount_total present → synthesize single-item line.
    - Missing posted_at → blank date.
    - Missing invoice_number → use id in filename; omit number label or show "-".
    - Large description text → truncated to fit the row width.

- Frontend:
  - AdminInvoicesService.pdf(id) should open a new tab with the PDF blob.
  - Verify the new invoice renders correctly for existing invoice data (tuition-save flow).

[Implementation Order]
Apply changes in a safe, incremental sequence.

1) Add renderer: create App/Services/Pdf/InvoicePdf.php with FPDI layout utilities and render() implementation.
2) Update InvoiceController::pdf() to:
   - Resolve display data (student name, term label),
   - Normalize items (qty, price, amount),
   - Call the renderer and inline-stream the PDF.
   - Remove Snappy facade usage from this method.
3) Quick sanity check by hitting GET /api/v1/finance/invoices/{id}/pdf on a real invoice id.
4) Frontend smoke via Admin → Invoices list → PDF action.
5) Fine-tune coordinates if QA feedback indicates a need to shift fields to better match printouts.
