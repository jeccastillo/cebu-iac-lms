# Applicant Details — "Interviewed" Flag

Goal: Show an "Interviewed" flag on the Applicant Details page header, alongside the existing Status and Payment flags.

Scope:
- Frontend AngularJS (unity-spa)
- Files to modify:
  - frontend/unity-spa/features/admissions/applicants/applicants.controller.js
  - frontend/unity-spa/features/admissions/applicants/view.html

Data Source:
- Backend ApplicantController@show already returns:
  - `interviewed` (boolean)
  - `interview_summary` (optional)
- Frontend ApplicantViewController currently does not bind `interviewed` to `vm`.

Plan / Steps:
1) Controller binding
   - In ApplicantViewController `load()` success handler:
     - Map API field to view-model: `vm.interviewed = !!d.interviewed;`
   - Placement: right after other surfaced fields (e.g., applicant_type, paid flags) to keep consistency.

2) UI badge in header
   - In `frontend/unity-spa/features/admissions/applicants/view.html`, header right section (alongside Status and Payment flags):
     - Add a badge:
       - Text: `Interviewed: {{ vm.interviewed ? 'Yes' : 'No' }}`
       - Style:
         - When true: `bg-green-100 text-green-800`
         - When false: `bg-gray-100 text-gray-700`
       - Optional icon parity with payments:
         - Check icon when true; times icon when false.

3) Behavior / Refresh
   - The interview flow calls `vm.reload()` after submitting a result. This will refresh the applicant payload, updating the `interviewed` flag.
   - No additional wiring needed for live updates.

4) QA / Test Cases
   - When `interviewed = true`: badge shows green with "Yes".
   - When `interviewed = false` or missing: badge shows gray with "No".
   - Badge coexists with existing "Application Fee" and "Reservation Fee" flags.
   - Verify on pages:
     - Fresh load of an applicant with no interview: shows "No".
     - After scheduling only (not completed): still "No".
     - After submitting result (Passed/Failed/No Show with completion): "Yes".

5) Non-functional
   - Keep DOM minimal; follow current Tailwind utility class conventions used in the header.
   - Do not change existing status coloration logic (already maps "interviewed" within status class list).
