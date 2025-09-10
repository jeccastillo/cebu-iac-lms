# TODO - Save Tuition Snapshot When Applying Scholarship

Goal: Upon applying scholarship/discount assignments for a student, automatically recompute and save the tuition snapshot for the affected student/term.

## Tasks

- [ ] Backend - ScholarshipService::applyAssignments
  - [ ] Add `use App\Services\TuitionService;`
  - [ ] After updating `tb_mas_student_discount.status` to `applied`, fetch distinct `(student_id, syid)` for affected assignment IDs.
  - [ ] For each `(student_id, syid)`, call `TuitionService::saveSnapshotByStudentId($studentId, $syid, $actorId)` in try/catch.
  - [ ] Return response augmented with `snapshots` count alongside `updated`.

- [ ] Backend - ScholarshipController::assignmentsApply
  - [ ] Pass authenticated actor ID to service:
    - From: `$this->scholarships->applyAssignments($ids, null);`
    - To:   `$this->scholarships->applyAssignments($ids, optional($request->user())->intID ?? null);`

## Notes

- TuitionService::saveSnapshotByStudentId will recompute tuition using current assignments (including those just applied) and upsert into `tb_mas_tuition_saved`. It may also create/update an invoice if a cashier actor context is present.
- No database migrations needed for this change.

## Manual Test Plan

1. Create a pending assignment for a student and term.
2. Apply the assignment via `PATCH /api/v1/scholarships/assignments/apply` with the assignment ID.
3. Verify a row exists/updated in `tb_mas_tuition_saved` for `(intStudentID, intRegistrationID)` corresponding to the student and term.
4. Optionally, verify that invoices are created/updated when applying with a cashier-authenticated user.
