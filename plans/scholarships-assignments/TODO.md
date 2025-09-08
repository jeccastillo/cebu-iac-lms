task_progress Items:
- [x] Step 1: Add guarded migration to add status column (default 'pending') and composite index to tb_mas_student_discount
- [x] Step 2: Update ScholarshipService::assigned to include assignment_status from sd.status
- [x] Step 3: Implement ScholarshipService methods: assignmentUpsert, listAssignments, applyAssignments, deleteAssignment
- [x] Step 4: Add ScholarshipAssignmentStoreRequest and ScholarshipAssignmentApplyRequest validators
- [x] Step 5: Add controller endpoints (assignments list/store/apply/delete) and bind routes with role: scholarship,admin
- [x] Step 6: Add SPA route /scholarship/assignments and build assignments.service.js
- [x] Step 7: Build assignments.controller.js and assignments.html for pending/apply flows
- [ ] Step 8: Critical-path testing for UI and endpoints; fix issues found
- [ ] Step 9: Update docs/TODOs and finalize
