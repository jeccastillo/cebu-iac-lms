# Task: Prevent Finalization When Any Grade Is Missing

Scope: Classlist grading viewer finalize flow (AngularJS Unity SPA + Laravel API)

Status: Completed

Changes Implemented
1. Frontend: viewer.controller.js
   - Added vm.hasIncompleteGrades() and internal missingCountForPeriod() helpers to detect missing grades for the active period (midterm/finals).
   - Updated finalize() to block when incomplete grades exist, setting a user-friendly error and showing a toast. No API call is made in this case.
   - Kept grade normalization consistent with existing logic that treats 'NGS' and 50 as no-grade-submitted (null).
2. Frontend: viewer.html
   - Disabled the Finalize button when vm.hasIncompleteGrades() is true to prevent accidental attempt.
3. Backend: ClasslistGradesController@finalize (Laravel)
   - Added server-side enforcement prior to state transition:
     - Queries tb_mas_classlist_student for rows missing a grade in the requested period (midterm: floatMidtermGrade; finals: floatFinalsGrade) where the column is NULL, '', 'NGS', 50, or '50'.
     - Returns HTTP 422 with a useful message and payload including period and missing_count when missing grades are detected.

Files Modified
- frontend/unity-spa/features/classlists/viewer.controller.js
- frontend/unity-spa/features/classlists/viewer.html
- laravel-api/app/Http/Controllers/Api/V1/ClasslistGradesController.php

Test Plan
- Precondition: Load Classlist Viewer with students and at least one missing grade for the current active period (based on intFinalized: 0 = midterm; ≥1 = finals).
- Frontend:
  - The Finalize button should be disabled when a student has no grade for the active period.
  - If the button is force-enabled and clicked, finalize() should show: 
    - "Cannot finalize: there is/are N student(s) without a grade for MIDTERM/FINALS. Please complete all grades."
- Backend:
  - Call POST /api/v1/classlists/{id}/finalize with body: { "period": "midterm" | "finals" }.
  - Expect 422 with JSON:
    {
      "success": false,
      "message": "Cannot finalize: N student(s) missing midterm/finals grade(s).",
      "data": {
        "classlist_id": {id},
        "period": "midterm|finals",
        "missing_count": N
      }
    }
  - After entering all grades, expect successful transition:
    - midterm: intFinalized 0 → 1
    - finals: intFinalized 1 → 2

Notes
- The enforcement applies to all roles on the backend. Client UI disables by default if any missing grade detected.
- Existing grading modes:
  - Numeric fallback (1..100) remains unchanged.
  - Grading-system mode respects configured items and remarks.
- Window enforcement and role-based permissions are unaffected by this change.

