# Credited Subjects — Test Plan

Purpose
- Validate end-to-end behavior for Credited Subjects:
  - API list/create/delete with role-gated access and SystemLog
  - AcademicRecordService and StudentChecklistService treat credits as passes (direct + equivalents)
  - Exclusion of credited rows from tuition/slots/records computations
  - Frontend Registrar UI flow
- Confirm no regression to enrollment metrics, tuition, or data views

Scope
- Backend (Laravel API)
- Frontend (Unity SPA AngularJS Registrar page)
- DB schema: tb_mas_classlist_student includes credited fields:
  - is_credited_subject (tinyint default 0)
  - credited_subject_name (nullable varchar)
  - equivalent_subject (nullable unsigned int, indexed)
  - term_taken (nullable varchar(100))
  - school_taken (nullable varchar(255))

Preconditions
- A student exists in tb_mas_users with a valid strStudentNumber (e.g., 2023-00001)
- A subject exists in tb_mas_subjects with intID = SUBJECT_ID_UNDER_TEST (e.g., 156)
- Optional: An equivalents mapping exists in tb_mas_equivalents so we can test equivalents behavior:
  - intSubjectID = SUBJECT_ID_UNDER_TEST
  - intEquivalentID = AN_EQUIVALENT_SUBJECT_ID
- API is accessible at /laravel-api/public/api/v1
- Test users:
  - Registrar user (role: registrar or admin) for POST/DELETE tests
  - Non-privileged user to confirm denied access

Conventions
- Replace placeholders in ALL_CAPS with real values, e.g.:
  - STUDENT_NUMBER → 2023-00001
  - SUBJECT_ID_UNDER_TEST → 156
  - EQUIVALENT_SUBJECT_ID → 157

--------------------------------------------------------------------------------
1) Backend API — CreditedSubjectsController
--------------------------------------------------------------------------------

1.1 List credited subjects (GET)
- Request (as registrar/admin):
  GET /laravel-api/public/api/v1/students/STUDENT_NUMBER/credits
- Expected:
  {
    "success": true,
    "data": [
      {
        "id": 123,
        "subject_id": 156,
        "subject_code": "CS101",
        "subject_description": "Intro to Programming",
        "credited_subject_name": "CS101 - Intro to Programming",
        "term_taken": "1st Term 2022-2023",
        "school_taken": "Transfer University",
        "remarks": "credited"
      }
      ...
    ]
  }

1.2 Create credited subject (POST)
- Request (as registrar/admin):
  POST /laravel-api/public/api/v1/students/STUDENT_NUMBER/credits
  Content-Type: application/json
  {
    "subject_id": SUBJECT_ID_UNDER_TEST,
    "term_taken": "1st Term 2022-2023",
    "school_taken": "Transfer University",
    "remarks": "credited"
  }
- Expected:
  - 201 response
  - success=true; data returns created row (id, subject_id, term_taken, school_taken, remarks, credited_subject_name)
  - A SystemLog entry is recorded (creation), tolerated if logging infra unavailable

1.3 Prevent duplicates
- Send the same POST again for the same subject and student.
- Expected:
  - 422 response with message "Credited subject already exists for this student"

1.4 Delete credited subject (DELETE)
- Request (as registrar/admin):
  DELETE /laravel-api/public/api/v1/students/STUDENT_NUMBER/credits/CREDIT_ID
- Expected:
  - success=true
  - Row removed in tb_mas_classlist_student and a corresponding SystemLog delete entry recorded (tolerate no-op if logging infra unavailable)

1.5 Role protection
- Repeat 1.2 and 1.4 using a user without registrar/admin role.
- Expected:
  - Access denied via middleware (HTTP 403/401 as configured)

--------------------------------------------------------------------------------
2) AcademicRecordService — Pass recognition with Credits
--------------------------------------------------------------------------------

2.1 Direct credited pass
- Pre: ensure a credited row exists with equivalent_subject == SUBJECT_ID_UNDER_TEST for the student.
- Call paths that rely on AcademicRecordService::hasStudentPassedSubject:
  - Subjects controller batch or single prerequisite checks:
    POST /laravel-api/public/api/v1/subjects/SUBJECT_ID_UNDER_TEST/check-prerequisites
    {
      "student_id": STUDENT_INT_ID
    }
- Expected:
  - Service considers the subject as passed when a credited row exists matching equivalent_subject = SUBJECT_ID_UNDER_TEST.

2.2 Equivalents-based credited pass
- Pre: ensure a credited row exists for EQUIVALENT_SUBJECT_ID and tb_mas_equivalents links it to SUBJECT_ID_UNDER_TEST.
- Same call as 2.1.
- Expected:
  - Service returns pass due to equivalents mapping.

2.3 Remarks/Grade fallback preserved
- For classlist-based records (non-credited), verify grade <= 3.0 or remarks contain pass/credit/credited → pass.
- Expected:
  - Existing logic intact.

--------------------------------------------------------------------------------
3) StudentChecklistService — Passed map includes Credits
--------------------------------------------------------------------------------

3.1 Generate checklist
- Endpoint:
  POST /laravel-api/public/api/v1/students/STUDENT_INT_ID/checklist/generate
  {
    "curriculum_id": CURRICULUM_ID
  }
- Expected:
  - Checklist created, items reflecting curriculum subjects.
  - Items corresponding to credited subjects (direct or equivalents) are marked passed.

3.2 Checklist index/summary
- GET /laravel-api/public/api/v1/students/STUDENT_INT_ID/checklist
- GET /laravel-api/public/api/v1/students/STUDENT_INT_ID/checklist/summary
- Expected:
  - Items include passed for credited subjects and equivalents; summary counts align.

--------------------------------------------------------------------------------
4) Exclusions: Tuition, Slots, Records
--------------------------------------------------------------------------------

4.1 TuitionService — exclude credits
- Endpoint (implementation dependent, example read path):
  GET /laravel-api/public/api/v1/tuition/compute?studentNumber=STUDENT_NUMBER&amp;syid=TERM_SYID
- Expected:
  - Any credited rows for the student are not included in subject aggregations (is_credited_subject=0 filter present).
  - Totals/line items unaffected by credited subjects.

4.2 ClasslistSlotsService — enlist/enroll counts
- Debug route (available with role registrar/admin):
  GET /laravel-api/public/api/v1/debug/classlists/slots?term=TERM_SYID
- Expected:
  - enlisted_count/enrolled_count not affected by credited rows.

4.3 DataFetcherService — student records views
- Records by term:
  POST /laravel-api/public/api/v1/student/records-by-term
  {
    "student_id": STUDENT_INT_ID,
    "term": TERM_SYID,
    "include_grades": false
  }
- Records all:
  POST /laravel-api/public/api/v1/student/records
  {
    "student_id": STUDENT_INT_ID,
    "include_grades": false
  }
- Expected:
  - Returned subject lists exclude credited rows.

--------------------------------------------------------------------------------
5) Frontend — Registrar UI Flow
--------------------------------------------------------------------------------

5.1 Access control
- As Registrar/Admin: navigate to #/registrar/credit-subjects
- As non-privileged user: confirm the route is not accessible and sidebar doesn&#39;t show the link.

5.2 Load credits
- Enter student number and click "Load Credits".
- Expected:
  - Table lists existing credited entries with columns: ID, Subject (code/description), Term Taken, School Taken, Remarks.

5.3 Add credit
- Enter Subject ID, Term Taken, School Taken, Remarks and click "Add Credit".
- Expected:
  - Toast success and table reload shows the newly added row.
  - Attempt adding the same subject again: toast error (duplicate).

5.4 Delete credit
- Click Delete on a row.
- Expected:
  - Row removed and feedback shown; table reload reflects change.

--------------------------------------------------------------------------------
6) Negative/Edge Cases
--------------------------------------------------------------------------------

- Invalid subject_id → 422 with message "subject_id must exist in tb_mas_subjects".
- Missing student → 422 with message "Student not found".
- Delete with wrong student or non-credited row → 422 with message "Credited subject entry not found for this student".
- Long term_taken/school_taken/remarks exceeding rules → 422 based on validation messages.

--------------------------------------------------------------------------------
7) Data Integrity Checks
--------------------------------------------------------------------------------

- Verify tb_mas_classlist_student row payload on create:
  - is_credited_subject = 1
  - equivalent_subject = SUBJECT_ID_UNDER_TEST
  - term_taken/school_taken/strRemarks normalized (empty strings ⇒ null for term/school)
  - intClassListID fallback to 0 if schema requires non-null
  - intsyID = null for credits (not tied to specific term by default)
- Verify SystemLog entries for create/delete operations where logging infra is available.

--------------------------------------------------------------------------------
8) Cleanup
--------------------------------------------------------------------------------

- Delete any test credits created during testing.
- Optionally remove test equivalents or revert any seeded data.

Notes
- All credited operations are designed to be harmless to tuition computation and slot counts.
- If running in an environment without certain tables (e.g., logging or billing), operations are tolerant and should still pass core tests.

Appendix — Example cURL Snippets

List:
curl -s "http://localhost/laravel-api/public/api/v1/students/STUDENT_NUMBER/credits" -H "Authorization: Bearer TOKEN"

Create:
curl -s -X POST "http://localhost/laravel-api/public/api/v1/students/STUDENT_NUMBER/credits" \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d &#39;{"subject_id": SUBJECT_ID_UNDER_TEST, "term_taken": "1st Term 2022-2023", "school_taken": "Transfer University", "remarks": "credited"}&#39;

Delete:
curl -s -X DELETE "http://localhost/laravel-api/public/api/v1/students/STUDENT_NUMBER/credits/CREDIT_ID" -H "Authorization: Bearer TOKEN"
