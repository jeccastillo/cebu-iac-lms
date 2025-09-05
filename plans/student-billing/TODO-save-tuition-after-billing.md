# Student Billing Auto Tuition Save — TODO

Objective:
Trigger tuition snapshot save (tb_mas_tuition_saved) immediately after successfully adding a student billing item.

Scope:
- Only on create (POST /api/v1/finance/student-billing). Response schema must remain unchanged.

Steps:
- [ ] TuitionService: add helper method saveSnapshotByStudentId(int $studentId, int $syid, ?int $actorId = null, ?int $discountId = null, ?int $scholarshipId = null): array
  - [ ] Load user by intID (tb_mas_users.intID = $studentId). Throw \InvalidArgumentException if missing.
  - [ ] Load registration by (intStudentID = $studentId, intAYID = $syid). Throw if missing.
  - [ ] Ensure registration.tuition_year is present. Throw if missing.
  - [ ] Resolve student_number from user, compute breakdown: $this->compute($studentNumber, $syid, $discountId, $scholarshipId).
  - [ ] Upsert tb_mas_tuition_saved keyed by (intStudentID,intRegistrationID).
  - [ ] Return: { id, intStudentID, intRegistrationID, syid, saved_by, overwritten, saved_at }.

- [ ] StudentBillingController@store
  - [ ] Inject TuitionService in action signature: store(StudentBillingStoreRequest $request, TuitionService $tuition)
  - [ ] After $item = $this->service->create(...), call:
        $tuition->saveSnapshotByStudentId($data['intStudentID'], $data['syid'], $actorId);
  - [ ] Wrap in try/catch to avoid breaking billing creation when tuition save fails.
  - [ ] Keep response schema the same (201 with created billing row).

Optional follow-ups (not in initial scope):
- [ ] Consider mirroring the behavior on update/delete (PUT/DELETE) if required by business rules.
- [ ] Add logging when snapshot save fails (e.g., SystemLogService) for observability.

Testing:
- [ ] Use scripts:
  - [ ] laravel-api/scripts/test_student_billing.php to exercise POST /finance/student-billing.
  - [ ] laravel-api/scripts/test_tuition_save.php for reference of expected behavior.
- [ ] Manual API test:
  - [ ] POST /api/v1/finance/student-billing with a valid {student_id, term(syid), description, amount,...}.
  - [ ] Verify tb_mas_tuition_saved row exists/updated for (intStudentID,intRegistrationID).
  - [ ] GET /api/v1/unity/tuition-saved?student_number=&amp;term= to confirm saved snapshot.

Notes:
- This implementation reuses the same validation and upsert semantics as UnityController::tuitionSave.
- No changes to request/response contracts of existing endpoints.
