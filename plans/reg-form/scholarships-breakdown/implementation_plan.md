# Implementation Plan

[Overview]
Add a scholarship/discount breakdown to the Registration Form PDF (UnityController::regForm) that mirrors the provided screenshot’s “ASSESSMENT SUMMARY” with three columns (Full, 50% Down, 30% Down) and includes line items for Scholarships and Discounts beneath the base fees (Tuition, Laboratory, Miscellaneous, Other Fees). Scholarships are listed first, followed by Discounts, with labels exactly as defined in the catalog. Scholarship/discount line amounts must scale for 50% and 30% partial payment schemes by applying increases only to tuition/lab-based lines (+9% for DP50, +15% for DP30), leaving misc/other-fee-based lines unchanged.

This work builds on the existing TuitionService->compute() and DiscountScholarshipService to leverage a single compute source of truth. The PDF layout will be extended to render the per-line breakdown and preserve the existing “Miscellaneous Detail” and “Other Fees Detail” blocks.

[Types]  
No schema changes; reuse existing compute output structures.

Type structures used (associative arrays from TuitionService::compute return):
- summary: { tuition: float, misc_total: float, lab_total: float, additional_total: float, scholarships_total: float, discounts_total: float, total_due: float, installments: { total_installment: float, total_installment50: float, total_installment30: float, down_payment: float, down_payment50: float, down_payment30: float, installment_fee: float, installment_fee50: float, installment_fee30: float } }
- items:
  - tuition: array<{ code?: string, subject_id?: int, units?: int, rate: float, amount: float }>
  - lab: array<{ code: string, amount: float }>
  - misc: array<{ name: string, amount: float }>
  - additional: array<{ name: string, amount: float }>
  - billing: array<{ name?: string, description?: string, amount: float }>
  - new_student: array<{ name: string, amount: float }>
  - scholarships: DeductionLine[]
  - discounts: DeductionLine[]

DeductionLine (from DiscountScholarshipService):
- {
  id: int,
  assignment_id: int,
  name: string,                // exact catalog name, e.g., "iACADEMY Cebu Scholarship", "Referral 1"
  deduction_type: 'scholarship' | 'discount',
  basis: 'tuition' | 'misc' | 'lab' | 'additional' | 'total_assessment',
  basis_label: string,
  rate?: int | null,
  fixed?: float | null,
  amount: float                // positive number; a deduction to be shown as negative in PDF
}

Scaling rule for 50% / 30% columns:
- If line.basis is tuition or lab:
  - DP50 column amount = line.amount × 1.09
  - DP30 column amount = line.amount × 1.15
- Else (misc, additional, total_assessment): leave as-is (no increase applied).

[Files]
Modify only one backend controller file; no new routes and no service changes required.

- Existing files to be modified
  - laravel-api/app/Http/Controllers/Api/V1/UnityController.php
    - Extend regForm() to:
      - Extract items.scholarships and items.discounts (when present) from TuitionService->compute result.
      - Compute per-column amounts per line item using the scaling rule stated above.
      - Insert the “Scholarships” and “Discounts” line items into the ASSESSMENT SUMMARY table under the base fee rows and before the Total row, with negative amounts displayed (e.g., "-4,615.20").
      - Display scholarships lines first, then discounts lines.
      - Preserve existing “MISCELLANEOUS DETAIL” and “OTHER FEES DETAIL” sections using items.misc and items.new_student.
      - Keep the down payment and installment rows as currently rendered.

- New files to be created
  - None

- Files to be deleted or moved
  - None

- Configuration file updates
  - None

[Functions]
Add helper closures inside regForm and integrate scholarship/discount lines into the existing drawing flow.

- New helper closures in UnityController::regForm (local to method):
  - money(float|numeric $x): string
    - Formats to number_format(2).
  - fmtNeg(float $x): string
    - Returns money($x) prefixed with "-" when x > 0; for 0 returns "0.00".
  - scaledAmount(array $line, float $mult): float
    - Returns round(line['amount'] × $mult, 2) where $mult = 1.0, 1.09, or 1.15 depending on the target column and basis is tuition|lab; otherwise returns the unmodified amount.
  - drawDeductionLine(string $label, float $full, float $fifty, float $thirty, float $x0, float $x1, float $x2, float $x3, float $y, float $h)
    - Renders a row label at the left (x0), and the three right-aligned amounts at column x1/x2/x3 using fmtNeg for display.

- Modified function
  - UnityController::regForm(Request $request)
    - After computing $breakdown and existing $sum/$items/$inst:
      - Read $scholarshipLines = $items['scholarships'] ?? [];
      - Read $discountLines = $items['discounts'] ?? [];
      - Compute for each line:
        - $isTuLab = in_array(strtolower($line['basis'] ?? ''), ['tuition','lab'], true);
        - $fullAmt = (float)($line['amount'] ?? 0);
        - $amt50 = $isTuLab ? round($fullAmt * 1.09, 2) : $fullAmt;
        - $amt30 = $isTuLab ? round($fullAmt * 1.15, 2) : $fullAmt;
      - Insert the rows below the existing base-fee rows (Tuition Fee, Laboratory, Miscellaneous, Other Fees):
        - Block header (optional): none (follow screenshot style). Immediately list scholarship lines with negative amounts (using fmtNeg), then discount lines.
      - Then proceed with the existing “Total” row (for Full / 50% / 30%), and the DP and Installment rows (kept as-is).
    - Defensive null checks to avoid notices when any group is empty.

- Removed functions
  - None

[Classes]
No class-level changes.

- New classes
  - None

- Modified classes
  - None

- Removed classes
  - None

[Dependencies]
No external dependencies added or removed.

- Continue using setasign/fpdi for PDF generation.
- Consumption of TuitionService->compute output remains unchanged.
- No Composer modifications necessary.

[Testing]
Manual verification via API call and visual inspection against the screenshot.

- Endpoint to test:
  - GET {API_BASE}/unity/reg-form?student_number={SN}&term={SYID}
  - Ensure X-Faculty-ID header is sent in contexts that require it (UnityService.regFormFetch already handles admin headers for the app).
- Expected:
  - Existing header fields (SN, Name, Program, Term, Address) unchanged.
  - Subjects table unchanged.
  - Assessment Summary:
    - Columns: FULL PAYMENT, 50% DOWN PAYMENT, 30% DOWN PAYMENT.
    - Rows in order:
      - Tuition Fee
      - Laboratory
      - Miscellaneous
      - Other Fees (new-student pack total)
      - Scholarships block (one row per scholarship line, negative, labels from catalog)
      - Discounts block (one row per discount line, negative, labels from catalog)
      - Total (bold/underlined as currently rendered)
      - Down Payment (existing)
      - 1st–5th Installment (existing)
    - For scholarship/discount lines:
      - 50% column values = full × 1.09 only when basis ∈ {tuition, lab}; otherwise unchanged.
      - 30% column values = full × 1.15 only when basis ∈ {tuition, lab}; otherwise unchanged.
      - Use minus sign for display (e.g., "-5,030.57").
- Edge cases:
  - No scholarships/discounts: rows absent; rendering skips those blocks cleanly.
  - Only discounts: show discount block rows only.
  - Only scholarships: show scholarship block rows only.
  - Basis = total_assessment: leave amounts unscaled in 50%/30% columns per requirement.
  - Very long scholarship names: truncate visually via cell width; current font/size should suffice. If overflow occurs, reduce font size for label cell only (not implemented initially; optional tweak).
- Data spot checks:
  - Pick a student where TuitionService->compute produces scholarship lines with mixed bases (tuition and misc) to verify scaling logic correctness.
  - Validate that totals (Full/50/30) already computed by existing logic still agree with expectations; note the summary Total rows already reflect full totals (not per-line recomposed totals) and will remain as currently rendered.

[Implementation Order]
Implement the controller changes in a small unit and verify incrementally.

1) Add extraction and normalization:
   - Extract items.scholarships and items.discounts to arrays; set to [] when absent.
   - Implement helpers: money, fmtNeg, scaledAmount.
2) Determine insertion Y after base fee rows:
   - Use current y after “Other Fees”; keep consistent line height (lineH).
3) Render scholarship lines:
   - For each line in items.scholarships:
     - label = line.name (exact catalog name)
     - amounts: full = amount; dp50 = scaled by 1.09 if basis tuition/lab else unchanged; dp30 = scaled by 1.15 if basis tuition/lab else unchanged
     - drawDeductionLine(label, full, dp50, dp30, colX[0], colX[1], colX[2], colX[3], y, lineH)
     - increment y by lineH
4) Render discount lines (after scholarships):
   - Same logic and scaling; ensure discounts follow scholarships.
5) Render Total row and DP/Installment rows as currently implemented:
   - Keep bold/underline styles; do not alter installment math.
6) Manual test with a known SN + Term:
   - Compare PDF values against the screenshot and expected scaling behavior.
   - Adjust minor X/Y offsets if any overlap is observed.
7) QA pass on a second student/term with different scholarship mixes.
