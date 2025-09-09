# Assign Scholarship Page: Referral Referrer Capture

Scope:
- Detect "Referral" discounts and require a referrer input (existing student or free text).
- Persist the referrer name into tb_mas_student_discount.referrer upon saving.

Tasks:
- [x] Frontend: Update requiresReferrer() logic (assignments.controller.js)
  - Ensure selected item is a discount.
  - Use case-insensitive substring match for "referral" (also tolerate "referal"), not exact equality.
  - Keep existing UI and payload fields:
    - referrer_student_id (number)
    - referrer_name (string)

- [x] Backend: Validation (ScholarshipAssignmentStoreRequest.php)
  - Add optional request rules:
    - referrer_student_id: sometimes|integer
    - referrer_name: sometimes|string

- [x] Backend: Persistence (ScholarshipService.php::assignmentUpsert)
  - Accept referrer_student_id or referrer_name from payload.
  - If referrer_student_id is provided:
    - Resolve tb_mas_users.intID to a full name string: "Lastname, Firstname Middlename".
  - Else if referrer_name provided: trim and use as-is.
  - Insert path:
    - Include referrer => name (string, empty string if absent).
  - Update path (row already exists):
    - If a non-empty referrer is provided, update the existing row referrer field.
  - Leave response shape unchanged.

Manual Test Checklist:
- [ ] Select a Discount with name containing "Referral" or "Referal" (any case) → Referrer UI shows.
- [ ] Mode "Existing student": choose a student via autocomplete → Save → Row created/updated; tb_mas_student_discount.referrer contains full name.
- [ ] Mode "Free text": enter a name → Save → tb_mas_student_discount.referrer contains typed text.
- [ ] Non-referral discounts: Referrer UI not shown; pending creation works as before.
