# Parents/Guardian Information Section - Implementation TODO

Objective:
Organize Parents/Guardian information on the application page into a distinct, clearly separated section. Place it after the "Contact Information" section and add a top "Jump to Parents/Guardian section" link.

Files:
- frontend/unity-spa/features/admissions/apply.html

Approved Specs:
- Section label: "Parents/Guardian Information"
- Position: After the "Contact Information" section
- Add: Top anchor link under page intro to jump to the section
- UI Enhancements:
  - Wrap with a dedicated container (id="parents-guardian-section") to visually separate it
  - Add helper note under the legend
  - Add inline badge that displays the selected primary contact when chosen
- No controller or validation logic changes; keep ng-model bindings intact

Steps:
- [ ] Insert a "Jump to Parents/Guardian section" anchor link below the page intro paragraph (before the form).
- [ ] Move the existing Parents section markup to follow immediately after the Contact Information fieldset.
- [ ] Update the section legend to: "Parents/Guardian Information".
- [ ] Wrap the moved section in a visual container:
      <div id="parents-guardian-section" class="border border-blue-200 rounded bg-blue-50 p-4 mb-6">...</div>
- [ ] Add a helper note under the legend:
      "Provide at least one parent or guardian with name and either email address or mobile number."
- [ ] Add a small primary contact badge under the legend (ng-if="vm.form.primary_contact"):
      Primary contact: {{ (vm.form.primary_contact | uppercase) }}
- [ ] Ensure the existing validation message remains within this container:
      applyForm.$submitted &amp;&amp; !vm.validateParentContacts()
- [ ] Remove the original Parents section (to avoid duplication) from its previous location.
- [ ] Manual verification in the browser:
  - [ ] Anchor link scrolls to the section.
  - [ ] Section visually separated and positioned after Contact Information.
  - [ ] Radios still set vm.form.primary_contact and badge updates accordingly.
  - [ ] Validation still shows when no valid parent/guardian is provided.
  - [ ] No console errors or Angular validation regressions.

Notes:
- Do not change bindings or field names:
  - mother_name, mother_occupation, mother_email, mother_mobile
  - father_name, father_occupation, father_email, father_mobile
  - guardian_name, guardian_relationship, guardian_email, guardian_mobile
  - primary_contact
- Keep current details accordions (Mother/Father/Guardian) and their validations intact.

Changelog:
- 1.0.0: Initial plan and steps recorded after approval.
