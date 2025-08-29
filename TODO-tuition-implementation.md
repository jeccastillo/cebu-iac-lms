# Tuition Computation Implementation TODO

task_progress Items:
- [x] Step 1: Add route GET /api/v1/tuition/compute and TuitionController@compute with TuitionComputeRequest
- [x] Step 2: Scaffold TuitionBreakdownResource and extend TuitionService with compute(...) signature
- [x] Step 3: Add TuitionCalculator helper with unit price, extra fee, lab classification, SHS track/elective helpers
- [x] Step 4: Implement college tuition (units, NSTP, lab, thesis) and SHS tuition (track, modular, elective)
- [x] Step 5: Implement misc/additional fees (regular/internship packs, ID validation rule, late enrollment, foreign, internship, thesis)
- [ ] Step 6: Implement discounts/scholarships (in-house/external, rate/fixed, total assessment, late-tag, AR fields)
      Notes: Aggregation wired (shape only) via TuitionCalculator::computeDiscountsAndScholarships; totals/lines remain zero/empty until full logic is implemented.
- [x] Step 7: Implement installment variants (installmentIncrease, 30%/50%, DP rules, installment fee calculations)
- [x] Step 8: Wire resource to shape response with summary, items, AR fields
- [ ] Step 9: Run critical-path API tests: happy path, invalid student, invalid term, missing registration/tuition_year; fix issues found

Notes:
- Endpoint returns a detailed breakdown using the student’s selected tuition_year from tb_mas_registration (for the provided term).
- Full parity with CodeIgniter Data_fetcher::getTuition / getTuitionSubjects is the target.
- Critical-path testing scope confirmed by user.
