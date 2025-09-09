# Implementation Plan

[Overview]
Add scholarship and discount computations into the tuition compute pipeline using tb_mas_scholarships and tb_mas_student_discount. Implement per-bucket percentage/fixed deductions with correct compute_full semantics: compute_full=true items are computed against original base values and capped at the base; compute_full=false items are computed sequentially against the running remainder.

The current TuitionService delegates discount/scholarship math to TuitionCalculator::computeDiscountsAndScholarships, which is a placeholder. This plan implements that function to: (1) load applied assignments for a student/term, (2) derive per-bucket deduction amounts from scholarship catalog fields, (3) apply compute_full groups against original bases with caps, (4) apply non-compute_full items sequentially on remaining amounts, and (5) return totals and line items split into scholarships vs discounts. This integrates cleanly with TuitionService::compute, which already feeds tuition, misc_total, lab_total, and additional_total into the aggregator and expects discount/scholarship totals and lines.

[Types]  
Add precise array shapes returned by the calculator and consistent bucket naming.

- Enum-like strings:
  - deduction_type: 'scholarship' | 'discount'
  - bucket: 'tuition' | 'misc' | 'lab' | 'additional' | 'total_assessment'

- Input args for TuitionCalculator::computeDiscountsAndScholarships(array $args):
  - student_id: int (required)
  - syid: int (required)
  - tuition: float (base tuition)
  - misc_total: float (base misc)
  - lab_total: float (base lab)
  - additional_total: float (base additional)
  - tuition_year: array (for context; optional)
  - class_type: string (for context; optional)
  - year_level: ?int (for context; optional)
  - level: string (for context; optional)
  - stype: string (for context; optional)
  - program_id: int (for context; optional)
  - discount_id: ?int (optional targeted)
  - scholarship_id: ?int (optional targeted)

- Scholarship/discount catalog fields (tb_mas_scholarships) used:
  - name: string
  - deduction_type: 'scholarship' | 'discount'
  - deduction_from: 'in-house' | 'external' | other (free-form)
  - status: 'active'|'inactive'
  - compute_full: bool (default true in DB)
  - Per-bucket fields (nullable):
    - tuition_fee_rate: ?int [0..100], tuition_fee_fixed: ?float
    - basic_fee_rate: ?int, basic_fee_fixed: ?float (alias of tuition bucket)
    - misc_fee_rate: ?int, misc_fee_fixed: ?float
    - lab_fee_rate: ?int, lab_fee_fixed: ?float
    - other_fees_rate: ?int, other_fees_fixed: ?float (mapped to 'additional')
    - penalty_fee_rate: ?int, penalty_fee_fixed: ?float (mapped to 'additional')
    - total_assessment_rate: ?int, total_assessment_fixed: ?float

- Output shape from TuitionCalculator::computeDiscountsAndScholarships(array $args): array
  - scholarship_grand_total: float
  - discount_grand_total: float
  - lines: array
    - scholarships: array<DeductionLine>
    - discounts: array<DeductionLine>
  - installment: array (reserved for future; zeros)
    - scholarship, discount, scholarship30, discount30, scholarship50, discount50: float
  - ar: array (reserved for AR reporting; zeros)
    - ar_discounts_full, ar_discounts_installment, ar_discounts_installment30, ar_discounts_installment50,
      ar_external_scholarship_full, ar_external_scholarship_installment, ar_external_scholarship_installment30,
      ar_external_scholarship_installment50, ar_late_tagged_discounts_full, ar_late_tagged_discounts_installment,
      ar_late_tagged_discounts_installment30, ar_late_tagged_discounts_installment50, ar_external_discounts_full,
      ar_external_discounts_installment, ar_external_discounts_installment30, ar_external_discounts_installment50

- DeductionLine: array
  - id: int (tb_mas_scholarships.intID)
  - assignment_id: int (tb_mas_student_discount PK id/intID)
  - name: string
  - deduction_type: 'scholarship'|'discount'
  - deduction_from: ?string
  - compute_full: bool
  - basis: 'tuition'|'misc'|'lab'|'additional'|'total_assessment'
  - rate: ?int
  - fixed: ?float
  - amount: float (actual applied amount after caps/sequencing)
  - notes: ?string (e.g. 'capped_at_base', 'zero_base', 'skipped_by_exclusive_total_assessment', etc.)

[Files]
Modify TuitionCalculator and add an integration test script; no controller changes needed.

- Existing files to be modified:
  - laravel-api/app/Services/TuitionCalculator.php
    - Implement computeDiscountsAndScholarships(array $args) with full logic as specified.
    - Optionally add private helpers inside the class:
      - private function mapCatalogRowToBucketSpecs(stdClass|array $sc): array
      - private function computeAmount(float $base, ?int $rate, ?float $fixed): float

- New files to be created:
  - laravel-api/scripts/test_tuition_scholarship_compute.php
    - Purpose: Integration sanity test covering:
      - Only 'applied' assignments counted.
      - compute_full=true capping at base with multiple items.
      - Mixed compute_full true/false sequencing and caps on remaining.
      - total_assessment vs per-bucket exclusive precedence.
      - Tuition and non-tuition buckets (misc/lab/additional).
    - This script will seed minimal test rows (guarded by Schema::hasColumn/hasTable), create assignments (status='applied'), call TuitionService::compute, and print the summary and lines.

- Files to review/fix (if needed):
  - laravel-api/database/migrations/2025_09_10_000300_add_compute_full_to_tb_mas_scholarships.php
    - Has minor syntax typos per grep (missing commas in Schema::hasColumn calls). If not migrated yet, fix and run migration to ensure compute_full exists with default true.

[Functions]
Implement the scholarship/discount engine in TuitionCalculator; no signature changes externally.

- New/Modified functions:
  - Modify: App\Services\TuitionCalculator::computeDiscountsAndScholarships(array $args): array
    - Behavior:
      1) Load tb_mas_student_discount rows for (student_id, syid) with sd.status='applied' only; join tb_mas_scholarships sc.status='active'.
      2) For each catalog row, derive one or more bucket specs (basis, rate, fixed, compute_full, deduction_type, name, ids).
         - Bucket mapping:
           - tuition_fee_* and basic_fee_* => basis='tuition'
           - misc_fee_* => basis='misc'
           - lab_fee_* => basis='lab'
           - other_fees_* and penalty_fee_* => basis='additional'
           - total_assessment_* => basis='total_assessment' and EXCLUSIVE: if any total_assessment field is set on the row, ignore all other per-bucket fields for that same row to avoid double counting.
         - If both rate and fixed are provided for a basis, use fixed (explicit amount) and ignore rate.
         - If neither is provided for all bases on a row, skip row (amount=0).
      3) Compute base values from $args:
         - base['tuition']     = (float)$args['tuition']
         - base['misc']        = (float)$args['misc_total']
         - base['lab']         = (float)$args['lab_total']
         - base['additional']  = (float)$args['additional_total']
         - base['total_assessment'] = sum of the four bases above
      4) Split specs into two groups per-basis: compute_full=true and compute_full=false.
         - For compute_full=true group on basis B:
           - For each spec, compute raw = computeAmount(base[B], rate, fixed) using the ORIGINAL base[B] (not reduced).
           - Sum across specs for that basis; cap: fullSum[B] = min(base[B], sum(raw_i)).
         - For compute_full=false group on basis B:
           - Sort rows by assignment id ascending for deterministic order.
           - Initialize remaining[B] = base[B] - fullSum[B].
           - For each spec, compute raw on current remaining[B] (rate uses remaining): raw = computeAmount(remaining[B], rate, fixed).
           - applied = min(remaining[B], raw); accumulate, then remaining[B] -= applied.
      5) Sum amounts by deduction_type to yield:
         - scholarship_grand_total (deduction_type = 'scholarship')
         - discount_grand_total (deduction_type = 'discount')
      6) Build lines arrays (scholarships/discounts) containing an entry per applied spec with its final amount and metadata.
    - Returns the array per [Types] above; keep 'installment' and 'ar' keys present (zeros) for compatibility.
    - Input validation: treat null/negative bases as 0. Rate bounds clipped to [0,100].

  - New private helper: computeAmount(float $base, ?int $rate, ?float $fixed): float
    - If $fixed is not null and > 0, return round($fixed, 2)
    - Else if $rate in [0,100], return round($base * ($rate/100), 2)
    - Else return 0.0

  - New private helper: mapCatalogRowToBucketSpecs($sc, $sdId): array<Spec>
    - Returns a list of specs:
      - { assignment_id:int, scholarship_id:int, name:string, basis:string, deduction_type:string, deduction_from:?string, compute_full:bool, rate:?int, fixed:?float }

[Classes]
No new classes; modify existing TuitionCalculator only.

- Modified class:
  - App\Services\TuitionCalculator
    - Add internal helpers and implement computeDiscountsAndScholarships with the new logic.
    - No signature changes for other public methods.

[Dependencies]
No new external packages. Uses existing DB facade and schema guards.

- Ensure compute_full column exists in tb_mas_scholarships (migration already present).
- No frontend changes required to consume outputs; TuitionService already forwards lines to the response payload under items.scholarships/items.discounts.

[Testing]
Add an integration test script and manual verifications via the debug tools.

- New script: laravel-api/scripts/test_tuition_scholarship_compute.php
  - Seeds:
    - One test student with registration and tuition_year ensuring non-zero tuition/misc/lab/additional bases (can reuse debug_tuition_compute.php approach).
    - Scholarships:
      - SCH_TU_20_CF_TRUE: tuition_fee_rate=20, compute_full=true
      - SCH_TU_10_CF_FALSE: tuition_fee_rate=10, compute_full=false
      - SCH_MISC_100F_CF_TRUE: misc_fee_fixed=100, compute_full=true
      - SCH_TA_50F_CF_FALSE: total_assessment_fixed=50, compute_full=false (exclusive row)
    - tb_mas_student_discount rows with status='applied' for the above.
  - Calls TuitionService::compute(studentNumber, syid) and prints:
    - Bases (tuition/misc/lab/additional/total_assessment)
    - scholarship_grand_total and discount_grand_total
    - Lines (with basis and amounts)
    - Verifies:
      - Only 'applied' counted (create a 'pending' row and assert it is ignored)
      - compute_full sum capped at base (e.g., two 60% rows on tuition cap at 100% of base)
      - Mixed sequence: compute_full first, then non-full on remaining
      - total_assessment row ignores other fields on same row and applies to total base

- Manual testing:
  - Reuse laravel-api/scripts/debug_tuition_compute.php to pick a real student with classes and a term; add catalog rows/assignments and observe compute results via /api/v1/tuition/compute.

[Implementation Order]
Implement in small, verifiable steps to keep system stable.

1. Migration sanity (only if not yet migrated):
   - Review laravel-api/database/migrations/2025_09_10_000300_add_compute_full_to_tb_mas_scholarships.php for typos (Schema::hasColumn commas) and fix if necessary.
   - Run migration to ensure compute_full BOOLEAN NOT NULL DEFAULT true exists.

2. Implement TuitionCalculator::computeDiscountsAndScholarships:
   - Add helpers computeAmount and mapCatalogRowToBucketSpecs.
   - Implement loading of assignments and catalog rows with filters:
     - sd.status='applied' only (per spec)
     - sc.status='active'
   - Implement bucket mapping, compute_full grouping and capping, sequential application for non-full, and line building.
   - Ensure exclusive precedence: if total_assessment_* is present on a row, ignore other per-bucket fields of that row.

3. Wire-through verification:
   - Confirm TuitionService::compute receives the updated ds payload and sets summary.discounts_total and summary.scholarships_total correctly (no code changes expected).
   - Ensure no negative totals, and final total_due remains non-negative.

4. Add script laravel-api/scripts/test_tuition_scholarship_compute.php:
   - Seed minimal fixtures guarded by Schema::hasColumn/hasTable checks.
   - Execute compute and print structured output validating the rules.

5. Smoke tests:
   - Run scripts/debug_tuition_compute.php on an existing student/term with real data and a mix of catalog rows to validate results.
   - Inspect frontend consumers (cashier-viewer/registration-viewer) to ensure no regressions (they already display totals and items arrays).

6. Documentation:
   - Document bucket mapping and compute_full semantics inline (PHPDoc on computeDiscountsAndScholarships) and add notes in the script header.
