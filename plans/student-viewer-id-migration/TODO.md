# Student Viewer ID Migration - TODO

This checklist tracks the implementation progress for migrating from student_number to student_id (tb_mas_users.intID) across Student Viewer and related consumers.

task_progress Items:
- [x] Step 1: Update backend request validators to require student_id and drop student_number (StudentBalanceRequest done).
- [ ] Step 2: Refactor DataFetcherService methods to accept and query by student_id.
- [ ] Step 3: Update StudentController methods to read student_id and call refactored service methods.
- [ ] Step 4: Update StudentRecordsRequest to validate student_id (replace student_number).
- [ ] Step 5: Update FinanceController (transactions) and TuitionController (compute) to accept student_id instead of student_number.
- [ ] Step 6: Update StudentViewerController to stop using ?sn= and send { student_id } to all endpoints.
- [ ] Step 7: Update related frontend services/pages using student_number to use student_id (finance student billing/ledger if affected).
- [ ] Step 8: Update tests and scripts to use student_id; adjust harnesses and queries.
- [ ] Step 9: Regression validation across viewer, finance, tuition endpoints; ensure no student_number acceptance remains.
- [ ] Step 10: Optional cleanup: remove deprecated code paths using student_number (e.g., getStudentByNumber if unused).

Notes:
- No backward compatibility: endpoints should reject student_number post-migration (422).
- Ensure joins consistently use intStudentID and not strStudentNumber.
- Keep getStudentByToken unchanged (uses token, not student_number).
