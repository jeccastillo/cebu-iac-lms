# Scholarship/Discount Assignments - Referrer UI (Referral/Referal)

Objective:
- When a discount named "Referal" or "Referral" is selected, show referrer input:
  - Option A: Pick existing student via pui-autocomplete
  - Option B: Free-text input for a non-student referrer name
- Validate before submission; include referrer fields in payload where applicable.

Status: Completed

Changes Implemented
- Controller (assignments.controller.js):
  - Added REFERRAL_NAMES = ['referal', 'referral'] to tolerate spelling variants.
  - Added vm.referrer state: { mode: 'student' | 'text', studentId, text }.
  - Added vm.referrerStudents list, onReferrerQuery, onReferrerSelect for autocomplete.
  - Exposed vm.studentLabel = getStudentLabel to eliminate duplicated label expression in templates.
  - Added vm.getSelectedCatalogItem() and vm.requiresReferrer() helpers to centralize logic.
  - Added vm.onDiscountChange() to reset referrer state when no longer required.
  - Updated createPending() to validate required referrer fields and include:
    - payload.referrer_student_id when mode === 'student'
    - payload.referrer_name when mode === 'text'
  - Fixed HTML-escaped operators to valid JS (replaced &amp;&amp; and < artifacts).
- Template (assignments.html):
  - Reused vm.studentLabel(item) for autocomplete labels.
  - Added conditional referrer UI under Catalog select:
    - Radio toggle for Existing student / Free text
    - Autocomplete for student mode
    - Textbox for free-text mode
  - Disabled Add as Pending button when referrer is required but missing.
- Service (assignments.service.js):
  - Documented optional referral fields in create() payload comment.

Test Plan
- Basic:
  - Select a student via the Student autocomplete.
  - Choose a Term.
  - Select a discount:
    - When non-referral: referrer UI should not appear; Add as Pending enabled if other requirements met.
    - When "Referal" or "Referral": referrer UI appears.
- Validation:
  - Referral + mode "student" with no referrer.studentId: button disabled; createPending shows warn.
  - Referral + mode "text" with empty referrer.text: button disabled; createPending shows warn.
- Submission:
  - Referral+student mode: payload includes referrer_student_id.
  - Referral+text mode: payload includes referrer_name.
  - Non-referral: payload unaffected.
- UX:
  - Switch from referral discount to non-referral: referrer state resets.
  - Autocomplete displays labels consistently using vm.studentLabel.
  - No console errors from directive usage.

Follow-ups / Future Improvements
- Prefer backend-driven flag (requires_referrer: boolean) on scholarship/discount items instead of name matching.
- Persist and display referrer on the assignments list if API supports it (read model + table column).
- Add unit/e2e tests for createPending validation and payload.
- Consider extracting referrer UI into a small directive/component if used elsewhere.
