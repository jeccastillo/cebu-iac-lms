# Implementation Plan

[Overview]
Add per-term GPA display on the Student Records page (Student Grades tab). Compute both Midterm GPA and Final GPA using weighted averages: Sum(grade × units) / Sum(units). Include a subject in the computation only when the subject's include_gwa is true and the record is not a credited subject. Credited rows are already excluded in the backend via is_credited_subject = 0.

This implementation augments the existing records payload from the Laravel API to include the include_gwa flag per subject, and updates the AngularJS Student Records page to compute and render the GPAs for each term card. The feature is read-only and does not modify grade data.

[Types]
No runtime type system (TS) is used; however the API response shape will be extended with:
- DataFetcherService records items:
  - include_gwa: number (1 or 0) indicating if subject should be included in GWA
  - grades: { prelim?: number|null, midterm?: number|null, finals?: number|null, final?: number|null }
  - units: number|null (existing; parsed as integer)

[Files]
We will modify and create the following files.

- Existing files to be modified:
  - laravel-api/app/Services/DataFetcherService.php
    - getStudentRecords(): include s.include_gwa in SELECT and map to response items
    - getStudentRecordsByTerm(): include s.include_gwa in SELECT and map to response items
  - frontend/unity-spa/features/students/records.controller.js
    - Add helper functions to compute weighted midterm and final GPA per term, respecting include_gwa and units
  - frontend/unity-spa/features/students/records.html
    - Render per-term GPA (Midterm GPA and Final GPA) in the term header area for each term card

- New files to be created:
  - None (plan only)

- Files to be deleted or moved:
  - None

- Configuration file updates:
  - None

[Functions]
We will add or modify the following functions:

- laravel-api/app/Services/DataFetcherService.php
  - getStudentRecords(int $studentId, ?string $term, bool $includeGrades): array
    - Modify SELECT: add 's.include_gwa as include_gwa'
    - In the record mapping loop, set:
      - 'include_gwa' => isset($r->include_gwa) ? (int)$r->include_gwa : 0
  - getStudentRecordsByTerm(int $studentId, string $term, bool $includeGrades): array
    - Modify SELECT: add 's.include_gwa as include_gwa'
    - In the record mapping loop, set:
      - 'include_gwa' => isset($r->include_gwa) ? (int)$r->include_gwa : 0

- frontend/unity-spa/features/students/records.controller.js
  - Add helper normalization utilities:
    - function _num(v): returns finite numeric or null
    - function _units(r): returns finite units from r.units or r.strUnits as number or null
    - function _grade(r, keyCandidates): walk through r[key] and r.grades[key] and other variants to get numeric grade or null
  - Add GPA computation functions:
    - vm._computeWeightedGpa = function(records, gradeKeys) { // gradeKeys example: ['midterm'] or ['final','finals']
        - Filter records to include only those with r.include_gwa truthy (1) AND valid units AND grade
        - sumNum += grade * units; sumDen += units; return (sumDen > 0 ? sumNum / sumDen : null)
      }
    - vm.termGpa = function(term) {
        var mid = vm._computeWeightedGpa(term.records, ['midterm']);
        var fin = vm._computeWeightedGpa(term.records, ['final','finals']);
        return { midterm: mid, final: fin, hasData: (mid != null || fin != null) };
      }

- frontend/unity-spa/features/students/records.html
  - In each term header (inside ng-repeat for terms), render:
    - A small line under the term label: "Midterm GPA: {{ vm.termGpa(t).midterm | number:2 || '-' }} | Final GPA: {{ vm.termGpa(t).final | number:2 || '-' }}"
    - Only show when at least one GPA exists (vm.termGpa(t).hasData)

[Classes]
No classes to be added or modified beyond the above function-level changes.

[Dependencies]
No new dependencies.

[Testing]
Approach:
- Backend:
  - Verify that both endpoints now return include_gwa per record.
  - Ensure credited rows remain excluded (existing filter is_credited_subject = 0).
- Frontend:
  - Load Student Records page (#/students/:id/records?sn=...) and confirm GPA lines per term appear.
  - Check correctness on sample data:
    - For a term with subjects:
      - A: units=3, final=2.0, include_gwa=1
      - B: units=2, final=1.5, include_gwa=1
      - C: units=3, final=3.0, include_gwa=0 (excluded)
      Expected Final GPA = (2.0*3 + 1.5*2) / (3+2) = 1.8
    - Verify midterm GPA computed similarly using midterm grade.
  - Edge cases:
    - No grades present: displays '-' and does not error.
    - Missing units: excluded from computation.
    - include_gwa absent: default 0 (excluded).
    - Credits (is_credited_subject=1) already excluded by backend.
- Regression:
  - Confirm records still render as before.
  - Confirm viewer and other pages are unaffected.

[Implementation Order]
Implement backend first, then frontend, and finally perform manual verification.

1) Backend — Add include_gwa in DataFetcherService
   - getStudentRecords(): add 's.include_gwa as include_gwa' to select; map to item 'include_gwa'
   - getStudentRecordsByTerm(): same change

2) Frontend Controller — Add GPA helpers
   - In records.controller.js, implement _num, _units, _grade, _computeWeightedGpa, and vm.termGpa

3) Frontend Template — Render GPA
   - In records.html, within each term block header, show "Midterm GPA" and "Final GPA" (number:2), hidden if none

4) QA / Verification
   - Confirm GPAs match manual calculations
   - Confirm credited rows are excluded
   - Confirm include_gwa filtering works by toggling subject include_gwa for test data
