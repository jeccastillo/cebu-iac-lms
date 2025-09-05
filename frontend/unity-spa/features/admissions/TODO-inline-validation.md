# Admissions Apply - Inline Validation (Real-time Errors)

Goal: Show inline validation error messages while typing (not only on submit) on the Admissions application page.

Status:
- [ ] Step 1: Add `name` attributes to relevant inputs/selects and wire up real-time validation UI in `apply.html`.
- [ ] Step 2: Verify cross-field mismatch messages (email/mobile confirmation) show while typing using only template logic.
- [ ] Step 3: Optional UX: add red border on invalid + dirty fields.
- [ ] Step 4: Manual test end-to-end; keep existing submit-time validation as a fallback.
- [ ] Step 5: Update this TODO to mark completed steps.

Details:
- Required fields to validate in real-time:
  - First Name, Last Name
  - Campus
  - Student Level (term type), Term
  - Program (First Choice)
  - Email, Confirm Email (also mismatch)
  - Mobile Number, Confirm Mobile Number (also mismatch)
- Approach:
  - Use AngularJS form validation states: `applyForm.field.$dirty`, `.$invalid`, `.$error.required`, `.$error.email`
  - Cross-field mismatch messages done via simple `ng-if` checks comparing models (no directive needed).
  - Add `ng-class` for red border when invalid and dirty.

No controller changes required for core functionality.
