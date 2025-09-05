# Admissions Apply - Additional Information Fields

Scope:
Add the following fields to the application form UI (AngularJS) and wire them to the form model and payload.
- Radio (required): good_moral_standing
- Radio (required): illegal_activities_involved
- Radio (required): hospitalized_before
- Checkbox group (multi): health_conditions
  - diabetes, allergies, high_blood, anemia, others (+ others_specify text when checked)
- Text (required): other_health_concerns (placeholder: "Type 'none' if you do not have any")

Files:
- frontend/unity-spa/features/admissions/apply.html
- frontend/unity-spa/features/admissions/apply.controller.js

Plan / Steps:
- [ ] Update vm.form in apply.controller.js with new fields and sensible defaults.
- [ ] Insert "Additional Information" fieldset into apply.html (before error block and submit).
- [ ] Bind radio/checkbox/text inputs to vm.form with AngularJS validation messages (consistent styling).
- [ ] Show conditional input when "Others" is checked (health_conditions.others_specify).
- [ ] Manual test: render page, interact with radios/checkboxes, verify model values update.
- [ ] Manual test: submit and ensure new fields are included in the JSON payload to /admissions/student-info.

Notes:
- Follow existing pattern: keep current submit gating logic (custom checks for critical fields only). Use UI required indicators and messages for the new fields without adding extra submit-side blocking, unless requested otherwise.
