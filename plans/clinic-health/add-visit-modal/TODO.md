# Add Visit Modal — TODO

Source plan: plans/clinic-health/add-visit-modal/implementation_plan.md

task_progress Items:
- [x] Step 1: Add controller UI state and visit form methods in ClinicRecordViewController (open/close/reset/add/remove)
- [x] Step 2: Insert “Add Visit” button and inline Tailwind modal markup into record-view.html
- [x] Step 3: Implement saveVisit() to build payload and call ClinicService.createVisit, then refresh visits list and pagination
- [x] Step 4: Add error handling, disable buttons during saving, UX polish (spinners, validation messages)
- [ ] Step 5: Manual test runs (student and faculty records), verify POST/GET flows, pagination, and edge cases

Details per step:

## Step 1: Controller UI state and methods
File: frontend/unity-spa/features/clinic/clinic.controller.js
- Add vm.ui = { showAddVisitModal: false, savingVisit: false }
- Add vm.visitForm with defaults via resetVisitForm()
- Add handlers:
  - vm.openAddVisitModal()
  - vm.closeAddVisitModal()
  - vm.resetVisitForm()
  - vm.addMedicationRow()
  - vm.removeMedicationRow(idx)

## Step 2: Modal markup
File: frontend/unity-spa/features/clinic/record-view.html
- Add “Add Visit” button on Clinic Visits card header (top-right)
- Add inline Tailwind modal (fixed overlay) with ng-if="vm.ui.showAddVisitModal"
- Fields:
  - visit_date, reason
  - triage: bp, hr, rr, temp_c, spo2, pain
  - assessment, diagnosis_csv (CSV input), treatment
  - medications_dispensed rows: name (required), dose, qty, instructions
  - follow_up
- Buttons: Cancel (close), Save (disabled while vm.ui.savingVisit)

## Step 3: Save flow
File: frontend/unity-spa/features/clinic/clinic.controller.js
- Implement vm.saveVisit():
  - Build payload:
    - record_id = vm.record.id
    - campus_id = vm.record.campus_id (fallback to CampusService selected campus if needed)
    - created_by = 13
    - coerce CSV → diagnosis_codes[]
    - filter meds: name required; qty number >= 0 if provided
    - drop empty strings → null
  - Call ClinicService.createVisit(payload)
  - On success: close modal, reset form, refresh via loadVisits(current page or 1), update counts
  - On failure: show error in modal, keep open

## Step 4: Error handling & polish
- Render validation messages in modal (top-level error block)
- Disable Save button while saving; show spinner text
- Ensure inputs trimmed and numeric coercions performed safely

## Step 5: Manual testing
- UI: open/close modal, required fields, meds add/remove
- API: POST /api/v1/clinic/visits (happy + 422 paths), GET listing reflects new visit with sorting/pagination
- Verify VisitResource fields used by UI: diagnosis_codes, attachments_count
