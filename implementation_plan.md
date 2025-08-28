# Implementation Plan

[Overview]
Implement a Laravel tuition computation service and API endpoint that reproduces the legacy CodeIgniter tuition logic, using the student's selected tuition_year on tb_mas_registration to compute a full tuition breakdown (tuition, labs, misc, scholarships, discounts, installment variants, late fees, foreign/new-student fees), with a dedicated endpoint to retrieve the result.

The existing CodeIgniter implementation (Data_fetcher::getTuition/getTuitionSubjects) computes tuition based on the student's registration record, enrolled subjects for a term, and fee schedules in the tuition year tables. This plan ports that logic to Laravel so the SPA and other consumers can obtain a consistent, canonical breakdown from the new API. The Laravel service will:
- Read the student registration row for the requested term to determine tuition_year, student status/type, class type, year level, internship tag, withdrawal periods, and program overrides.
- Gather the student's classes for the term (and their properties such as units, lab classification, electives, modular payments).
- Resolve fee schedules from the tuition year tables (unit rates by program or default; lab/misc fees by type and delivery mode; track/elective fees for SHS).
- Apply special rules (NSTP, thesis, internship, foreign fees, late enrollment).
- Compute discounts and scholarships (in-house, external, late-tagged) across tuition, lab, misc, and other fee buckets with both percentage and fixed schemes.
- Generate installment variants with configured increases, down payment strategies (percentage or fixed), and installment fee schedules.
- Return a structured breakdown compatible with both detailed UI display and AR/ledger reporting fields exposed by the legacy computation.

[Types]  
Define request/response types to formalize the API contract.

- Request (query params):
  - student_number: string (required) — tb_mas_users.strStudentNumber
  - term: int (required) — tb_mas_sy.intID (syid)
  - override_discount_id: int|null (optional) — tb_mas_student_discount.intID to compute a specific discount only (parity feature)
  - override_scholarship_id: int|null (optional) — tb_mas_student_discount.intID to compute a specific scholarship only (parity feature)

- Enums/constraints:
  - class_type: enum {regular, online, hybrid, hyflex} — derived from registration.type_of_class or SHS year-level mapping
  - student_type: strings consistent with CI logic: new, freshman, transferee, continuing, shiftee, returning, 2nd Degree, 2nd Degree iAC, foreign, etc.
  - level: enum {college, shs, other, drive} — normalized from tb_mas_users.level
  - withdrawal_period: enum {'before','during','after'} or null

- Response (TuitionBreakdown):
  - summary:
    - tuition: float
    - lab_total: float
    - misc_total: float
    - additional_total: float (new_student + foreign + internship + late_enrollment + thesis/nsf if applicable)
    - discounts_total: float (in-house)
    - scholarships_total: float (external + in-house distinguished in fields)
    - total_before_deductions: float
    - total_due: float
    - total_installment: float
    - total_installment30: float
    - total_installment50: float
    - down_payment: float
    - down_payment30: float
    - down_payment50: float
    - installment_fee: float (per schedule)
    - installment_fee30: float
    - installment_fee50: float
  - items:
    - tuition: array of { code: string, subject_id: int, units: int, rate: float, amount: float, section?: string }
    - lab: array of { code: string, lab_type: string, rate: float, hours: int, amount: float }
    - misc: array of { name: string, amount: float }
    - additional: array of { name: string, amount: float } (new_student, late_enrollment, foreign items, internship, thesis)
    - discounts: array of { name: string, deduction_from: 'in-house'|'external', scope: 'tuition'|'lab'|'misc'|'other'|'total', type: 'rate'|'fixed', value: float, amount: float, date_applied?: datetime }
    - scholarships: same structure as discounts when deduction_type = scholarship
  - ar_reporting:
    - ar_discounts_full, ar_discounts_installment, ar_discounts_installment30, ar_discounts_installment50: float
    - ar_external_scholarship_full, ar_external_scholarship_installment, ar_external_scholarship_installment30, ar_external_scholarship_installment50: float
    - ar_late_tagged_discounts_full, ar_late_tagged_discounts_installment, ar_late_tagged_discounts_installment30, ar_late_tagged_discounts_installment50: float
    - ar_external_discounts_full, ar_external_discounts_installment, ar_external_discounts_installment30, ar_external_discounts_installment50: float
  - meta:
    - class_type: string
    - tuition_year_id: int
    - year_level: int|null
    - program_id_used: int
    - currency: string ('PHP')
    - computed_at: datetime

[Files]
Introduce a new controller and enrich the TuitionService; add helpers for parity computations; add a resource for clean output; and wire the route.

- New files:
  - laravel-api/app/Http/Controllers/Api/V1/TuitionController.php
    - Purpose: Validate request, orchestrate computation via TuitionService, return JSON.
  - laravel-api/app/Http/Requests/Api/V1/TuitionComputeRequest.php
    - Purpose: Input validation (student_number, term, optional overrides).
  - laravel-api/app/Http/Resources/TuitionBreakdownResource.php
    - Purpose: Shape output consistently, including AR reporting fields.
  - laravel-api/app/Services/TuitionCalculator.php
    - Purpose: Extracted helper methods ported from CI: getUnitPrice, getExtraFee, resolveClassType, computeSHSTrackRate, elective/track lookup, lab type resolution, late enrollment, foreign fees, installment math.
  - laravel-api/tests/Feature/TuitionComputeTest.php
    - Purpose: API tests for common scenarios (college with labs/misc; SHS with track/elective; with in-house/external scholarships; late enrollment; foreign).

- Existing files to modify:
  - laravel-api/routes/api.php
    - Add route: GET /api/v1/tuition/compute -> TuitionController@compute
  - laravel-api/app/Services/TuitionService.php
    - Replace placeholder preview with full parity logic:
      - public function compute(string $studentNumber, int $syid, ?int $discountId = null, ?int $scholarshipId = null): array
      - Internal queries to tb_mas_registration, tb_mas_classlist_student, tb_mas_classlist, tb_mas_subjects, tb_mas_tuition_year_* tables, tb_mas_student_discount + tb_mas_scholarships, tb_mas_sy, tb_mas_users.
      - Build breakdown arrays and AR fields.

- No files to delete or move.

- Configuration:
  - None required beyond route registration. Existing migration 2025_08_28_000400_add_missing_columns_to_tb_mas_tuition_year.php already adds needed columns (installmentFixed, freeElectiveCount, final).

[Functions]
Add new functions and ported helpers; modify TuitionService methods to compute full parity outputs.

- New functions (TuitionService):
  - compute(studentNumber: string, syid: int, discountId?: int|null, scholarshipId?: int|null): array
    - Purpose: Orchestrate full computation and return the breakdown array.

- New functions (TuitionCalculator):
  - resolveRegistrationContext(studentId: int, syid: int): array
    - Returns registration-derived parameters: tuition_year_id, stype, class_type, year_level, internship, intROG, withdrawal_period, current_program, dteRegistered.
  - gatherSubjectsForTerm(studentId: int, syid: int): array
    - Return selected subjects for the term with major/elective/modular flags and payment_amount from classlist.
  - getUnitPrice(tuitionYear: array, classType: string, programId?: int|null): float
    - Resolve program-specific unit rate from tb_mas_tuition_year_program or fallback tuition year defaults by classType.
  - getExtraFee(row: array, classType: string, bucket: 'misc'|'lab'): float
    - Read correct column per class type (tuition_amount[_online|_hybrid|_hyflex]).
  - resolveLabClassification(subjectId: int, syid: int, default: string): string
    - Use tb_mas_subjects_labtype override else default subject classification.
  - computeCollegeTuition(unitFee: float, subjects: array, tuitionYear: array, classType: string, syid: int): array
    - Returns tuple [tuition: float, lab_total: float, thesis_fee: float, detailed lab list].
  - computeSHSTuition(subjects: array, tuitionYear: array, classType: string, yearLevel: int, programId: int): array
    - Returns [tuition: float from track, modular add-ons, elective add-ons, lab_total (usually none), line items].
  - computeMiscFees(miscRows: array, classType: string, stype: string, yearLevel: int, wStatus: string|null, semRow: array, dteRegistered: date|null): array
    - Builds misc_list with ID Validation omission for new students; late enrollment fee logic when dteRegistered >= sem.reconf_start; internship misc pack if needed.
  - computeForeignFees(studentCitizenship: string, semRow: array, tuitionYear: array, classType: string): array
    - Adds SVF/ISF as applicable and returns list + total.
  - computeDiscountsAndScholarships(args…): array
    - Aggregates in-house and external; computes rate/fixed per fee bucket and total assessment; builds AR fields and late-tagging segregation per sy.ar_report_date_generation; returns totals and line items; exposes installment variants (regular/installmentIncrease, 30%, 50%).
  - computeInstallments(totals…): array
    - Applies installmentIncrease, DP percentage or installmentFixed behavior; produces down_payment, installment_fee, 30/50 variants.

- Modified functions:
  - TuitionService::preview(array $input): array
    - Keep for backward compatibility, but update to call compute(...) in a preview mode when student_number and term are present; otherwise return placeholder as today.

- Removed functions:
  - None.

[Classes]
Add a controller, request validator, resource, and helper service. Modify TuitionService to orchestrate parity logic.

- New classes:
  - App\Http\Controllers\Api\V1\TuitionController
    - Methods: compute(TuitionComputeRequest $request)
    - Dependencies: TuitionService
  - App\Http\Requests\Api\V1\TuitionComputeRequest
    - Rules: student_number required, term required integer, optional overrides numeric
  - App\Http\Resources\TuitionBreakdownResource
    - Transforms TuitionService result into API response
  - App\Services\TuitionCalculator
    - Stateless helper, injected into TuitionService

- Modified classes:
  - App\Services\TuitionService
    - Add compute() and integrate TuitionCalculator; retain preview() with compatibility behavior.

- Removed classes:
  - None.

[Dependencies]
No new external composer packages are required.

Rationale: All logic is DB-driven and can be implemented with Laravel DB/Query Builder or Eloquent. No extra libs beyond standard PHP/Carbon for dates.

[Testing]
Adopt API feature tests and limited unit tests for calculators.

- Feature tests (laravel-api/tests/Feature/TuitionComputeTest.php):
  - test_college_basic_with_labs_and_misc: seeds a student, registration with tuition_year, 2 subjects with units/lab, misc fees; asserts totals match CI parity calculation.
  - test_shs_track_with_modular_and_elective: seeds track rates and elective fees; asserts tuition and add-ons.
  - test_discounts_inhouse_rate_and_fixed: attaches in-house discounts; validates AR in-house buckets and net totals.
  - test_scholarships_external_and_late_tagged_discounts: attaches external scholarships and late-tagged in-house discounts; validates external/lated-tagged AR fields.
  - test_late_enrollment_fee_and_foreign_fees: sets dteRegistered after reconf_start and foreign citizenship; asserts additions.
  - test_withdrawal_status_zero_out_on_before: sets intROG to OW/LOA/AWOL cases and w_status 'before' to zero tuition/lab/misc; asserts behavior.

- Manual validation:
  - Compare outputs against CI endpoints or database-known cases.
  - cURL example:
    - GET http://localhost:8000/api/v1/tuition/compute?student_number=C2024-1-1234&amp;term=123

[Implementation Order]
Build from read-only endpoint outward, minimizing churn and enabling incremental validation.

1. Routes and Controller scaffolding
   - Add GET /api/v1/tuition/compute in routes/api.php mapped to TuitionController@compute.
   - Create TuitionComputeRequest with validation.
   - Create TuitionBreakdownResource returning the pre-existing TuitionService::preview to keep the endpoint working during development.

2. Data gathering primitives
   - In TuitionService, add methods to look up student, registration (with tuition_year, stype, class_type, year_level, internship, intROG, withdrawal_period, current_program, dteRegistered), sy row, and subjects for the term (classlist_student/classlist/subjects join mirroring CI).

3. TuitionCalculator helper
   - Implement helpers: getUnitPrice(), getExtraFee(), resolveLabClassification(), resolveClassType(), and the SHS/elective/track lookup routines against:
     - tb_mas_tuition_year_program (program-based unit rates)
     - tb_mas_tuition_year_lab_fee (per lab classification)
     - tb_mas_tuition_year_misc (types: regular|internship|new_student|thesis|late_enrollment|svf|isf|nstp)
     - tb_mas_tuition_year_track (SHS track fees)
     - tb_mas_tuition_year_elective (SHS elective fees)
     - tb_mas_subjects_labtype (per-term lab override)
   - Port special rules: NSTP rate, thesis fee, internship pack, foreign fees (only when sem.pay_student_visa != 0 and/or isf), late enrollment windows (sem.reconf_start).

4. Compute college and SHS tuition
   - For college: loop subjects and sum int(strTuitionUnits)*unit_rate, NSTP override, lab classification fee * intLab hours, thesis if flagged.
   - For SHS: base track amount by year_level, add modular subject payment_amount, add elective subject amount via tuition_year_elective by year_level; consider delivery mapping for year level where applicable.

5. Compute misc and additional fees
   - Build misc_list from misc pack (regular vs internship), omit ID VALIDATION for brand-new students (same condition set as CI), add late_enrollment fee when applicable, prepare new_student fees for stype ∈ {new,freshman,transferee, 2nd Degree, 2nd Degree iAC}, foreign fee pack, internship fee pack, thesis fee when applicable.

6. Discounts and scholarships
   - Gather applied tb_mas_student_discount for syid and student (deduction_type ∈ {discount,scholarship}, status = 'applied'), split in-house vs external and late-tagged by date_applied vs sy.ar_report_date_generation.
   - Compute rate/fixed reductions over tuition, lab, misc, and other buckets; also allow 'total assessment' discounts where defined; detect full scholarship when total_assessment_rate=100.
   - Produce AR reporting fields:
     - ar_discounts_full/installment(,30,50)
     - ar_external_scholarship_full/installment(,30,50)
     - ar_late_tagged_discounts_full/installment(,30,50)
     - ar_external_discounts_full/installment(,30,50)
   - Store per-award lines with scope/type/amount metadata in items.discounts/scholarships.

7. Installment math
   - Calculate installment totals with tuition_year.installmentIncrease for regular installment, and 30%/50% variants with 0.15 and 0.09 multipliers per parity logic.
   - Determine down_payment:
     - If tuition_year.installmentFixed is set and non-zero, use fixed DP (for SHS year_level 2 or 4, DP=total_installment/2 per parity) else DP = total_installment*(installmentDP/100).
   - Compute installment_fee = (total_installment - down_payment)/5, and similarly for 30%/50%.

8. Withdrawal and special-case adjustments
   - If intROG ∈ {3(OW),4(LOA),5(AWOL)} and withdrawal_period == 'before', force tuition/lab/misc to 0 and clear per-item lists; drop late fee.

9. Resource shaping and response
   - Populate TuitionBreakdownResource from computed arrays, including summary, items, and AR reporting.

10. Replace TuitionService::preview with compute
   - Update preview() to call compute() when given valid inputs for backwards compatibility; otherwise return placeholder.

11. Tests
   - Write feature tests for major scenarios with seeded rows resembling CI data.
   - Validate edge cases: foreign, late enrollment, full scholarship, withdrawal 'before'.

12. Performance and correctness
   - Use minimal selects, indexes (where possible), and avoid N+1 by joining once per list.
   - Cross-validate with known CI runs and sample registrations.
