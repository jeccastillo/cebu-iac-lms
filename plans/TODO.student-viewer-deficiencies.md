# TODO — Student Viewer Deficiencies Panel

Scope: Add a Deficiencies panel to the Student Viewer info page showing all student deficiencies across all terms, visible to registrar/admin, grouped by term with per‑term totals and grand total.

Task Progress:
- [x] Step 1: Backend - create StudentDeficienciesRequest (validator) for POST /api/v1/student/deficiencies
- [x] Step 2: Backend - create StudentDeficiencyResource (normalize deficiency item shape)
- [x] Step 3: Backend - add route in routes/api.php with role:registrar,admin to StudentController@deficiencies
- [x] Step 4: Backend - implement StudentController@deficiencies (group by term with totals)
- [x] Step 5: Frontend - update viewer.controller.js (endpoint, state, fetchDeficiencies, role gating)
- [x] Step 6: Frontend - update viewer.html (Deficiencies panel grouped by term with totals)
- [ ] Step 7: Critical-path testing - verify API and UI integration

Notes:
- Backend uses DepartmentDeficiencyService::list(studentNumber=null, studentId, syid=null, departmentCode=null, campusId=null, page=1, perPage=1000, allowedDepartments=[]).
- Response shape:
  {
    "success": true,
    "data": {
      "student_id": number,
      "terms": [
        { "syid": number, "label": string|null, "items": [...], "total_amount": number }
      ],
      "totals": { "grand_total_amount": number }
    }
  }
- Frontend will map missing labels using /generic/terms; visibility gated to registrar/admin.
