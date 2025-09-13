# Implementation Plan

[Overview]
Add an “Add Visit” modal to the Health Record view page that allows clinic staff to create a clinic visit for the currently viewed record. On save, the modal posts to the existing Laravel endpoint to create a ClinicVisit, then updates the page’s visits list and pagination without a full page reload.

This fits into the existing Clinic &amp; Health Records module. The backend already supports listing and creating visits via ClinicVisitController and VisitStoreRequest. The frontend already has ClinicService endpoints. The work is primarily UI (AngularJS 1.x) and controller logic within the ClinicRecordView page. We will follow established SPA modal patterns (inline Tailwind modal with ng-if state), similar to Student Billing’s add/edit modal pattern.

[Types]  
No new TypeScript types; we define the visit payload shape in JS/controller with explicit validation rules matching the backend FormRequest.

Visit payload (POST /api/v1/clinic/visits)
- record_id: number (required)
- visit_date: string (nullable; date or datetime). If omitted, backend will default to now().
- reason: string (nullable, max 255)
- triage: object (nullable)
  - triage.bp: string (nullable, max 20)
  - triage.hr: integer (0..300, nullable)
  - triage.rr: integer (0..100, nullable)
  - triage.temp_c: number (25..45, nullable)
  - triage.spo2: integer (0..100, nullable)
  - triage.pain: integer (0..10, nullable)
- assessment: string (nullable)
- diagnosis_codes: string[] (nullable). UI provided as CSV, coerced to array of trimmed non-empty strings on submit.
- treatment: string (nullable)
- medications_dispensed: array<MedicationDispensed> (nullable)
  - MedicationDispensed:
    - name: string (required_with:medications_dispensed, max 255)
    - dose: string (nullable, max 255)
    - qty: number (nullable, min 0)
    - instructions: string (nullable, max 500)
- follow_up: string (nullable)
- campus_id: number (nullable)
- created_by: number (required) – Per product direction for this task: use uid = 13.

Frontend UI model structures in ClinicRecordViewController:
- vm.ui: { showAddVisitModal: boolean, savingVisit: boolean }
- vm.visitForm: {
    visit_date: string|null,
    reason: string|null,
    triage: { bp?: string, hr?: number, rr?: number, temp_c?: number, spo2?: number, pain?: number }|null,
    assessment: string|null,
    diagnosis_csv: string|null,      // UI helper for CSV
    diagnosis_codes: string[]|null,  // will be filled from diagnosis_csv on submit
    treatment: string|null,
    medications_dispensed: Array<{ name: string, dose?: string, qty?: number, instructions?: string }>|null,
    follow_up: string|null,
    campus_id: number|null
  }

Validation rules mirrored in controller before submit:
- Trim strings; drop empty strings to null.
- Coerce CSV to array of strings; reject empty entries.
- Filter medications_dispensed to rows with a non-empty name; ensure qty is number >= 0 if provided; drop optional empty fields.
- Ensure record_id is the current vm.record.id; ensure created_by is 13.

[Files]
Add/modify frontend files in the Clinic feature to integrate the modal and post-create refresh.

Detailed breakdown:
- Modify: frontend/unity-spa/features/clinic/record-view.html
  - Add a right-aligned “Add Visit” button in the “Clinic Visits” card header (beside the title).
  - Add an inline Tailwind modal (fixed overlay) rendered with ng-if="vm.ui.showAddVisitModal".
  - Modal form fields:
    - Visit Date (datetime-local or date input)
    - Reason (text)
    - Triage fields: BP (text), HR, RR, Temp C, SpO2, Pain (0..10)
    - Assessment (textarea)
    - Diagnosis Codes (CSV text input)
    - Treatment (textarea)
    - Medications Dispensed (repeatable rows: Name [required], Dose, Qty, Instructions; add/remove row buttons)
    - Follow-up (textarea)
    - Campus (read-only display of vm.record.campus_id for clarity; the value is used as campus_id in payload; if needed, allow edit or keep hidden)
    - Buttons: Cancel (closes modal), Save (posts).
  - Modal should display backend validation errors and a top-level error area. Disable Save when vm.ui.savingVisit is true; show spinner.

- Modify: frontend/unity-spa/features/clinic/clinic.controller.js
  - In ClinicRecordViewController:
    - Add UI state: vm.ui = vm.ui || {}; vm.ui.showAddVisitModal = false; vm.ui.savingVisit = false.
    - Add default form builder/reset: vm.resetVisitForm()
    - Add handlers:
      - vm.openAddVisitModal()
      - vm.closeAddVisitModal()
      - vm.addMedicationRow()
      - vm.removeMedicationRow(idx)
      - vm.saveVisit()
    - In saveVisit():
      - Build payload per Types. Ensure:
        - record_id = vm.record.id
        - created_by = 13 (per directive)
        - campus_id = vm.record.campus_id (fallback to selected campus via CampusService if present)
        - diagnosis_codes parsed from vm.visitForm.diagnosis_csv
        - medications_dispensed filtered to only named items; coerce qty to number when not empty
      - Call ClinicService.createVisit(payload)
      - On success:
        - Option A (preferred for consistency and correctness): Reload visits via loadVisits(1) if current page is 1, otherwise reload current page to reflect newest visit ordering by visit_date desc.
        - Option B (simple insert): Prepend the returned visit to vm.visits and update vm.vmeta.total++, but still call loadVisits to respect ordering.
      - Close modal and clear form; surface ToastService.success on success if available.
      - On fail: show inline validation errors and top-level error area; keep modal open; clear saving flag.

- Optional New (only if we want to isolate modal template):
  - New file: frontend/unity-spa/features/clinic/partials/visit-modal.html
    - Purpose: Keep record-view.html slim by extracting modal markup. However, to match existing patterns (e.g., student-billing/list.html), we will embed modal inline and avoid a new file for now.

- No backend changes required:
  - Existing Laravel controllers/services/requests already satisfy requirements.
  - Endpoint: POST /api/v1/clinic/visits (VisitStoreRequest enforces validation).
  - Created_by mandated; per instruction use uid = 13.

[Functions]
Add controller-level functions in ClinicRecordViewController only.

Detailed breakdown:
- New functions:
  - openAddVisitModal(): void
    - File: frontend/unity-spa/features/clinic/clinic.controller.js
    - Purpose: Initialize vm.visitForm using resetVisitForm(); set vm.ui.showAddVisitModal = true.
  - closeAddVisitModal(): void
    - File: same
    - Purpose: Hide modal; reset transient errors.
  - resetVisitForm(): void
    - File: same
    - Purpose: Initialize vm.visitForm with sensible defaults (visit_date = today, meds array with one blank row, diagnosis_csv = '') and derive campus_id from vm.record.campus_id if present, else from CampusService.getSelectedCampus().
  - addMedicationRow(): void
    - File: same
    - Purpose: Push a new blank medication row to vm.visitForm.medications_dispensed.
  - removeMedicationRow(idx: number): void
    - File: same
    - Purpose: Splice medication row at index if exists.
  - saveVisit(): void (async via promises)
    - File: same
    - Purpose: Build payload per Types, set created_by = 13, call ClinicService.createVisit(payload), handle success/error, update visits list, close modal or keep open with errors.

- Modified functions:
  - loadVisits(page?: number): void
    - File: frontend/unity-spa/features/clinic/clinic.controller.js (existing)
    - Change: None to signature; internal: allow optional force reload after save (call unchanged). We will call it after successful save.

- Removed functions:
  - None.

[Classes]
No new classes.

Detailed breakdown:
- New classes: None
- Modified classes: None
- Removed classes: None

[Dependencies]
No new runtime dependencies.

Details:
- Reuse existing services: ClinicService, CampusService (if needed), ToastService (if available), StorageService (not necessary for this modal as created_by is fixed).
- Continue to rely on Tailwind classes for modal style (existing SPA pattern).

[Testing]
Manual testing plus light controller-level verification.

Details:
- Manual UI tests:
  - Open /#/clinic/records/:id with a valid record.
  - Click “Add Visit” → modal opens.
  - Leave form blank except required created_by and record link; Save → success with defaults where applicable; verify list updates and count increments.
  - Enter triage with out-of-range values (e.g., hr=-1) → controller should guard or let backend 422; errors surface in modal.
  - Diagnosis CSV parsing:
    - "A00.0, B25, , C19" → array ["A00.0","B25","C19"].
  - Medications rows:
    - No name → row ignored/not submitted; name present with qty string "2" → coerced to number 2.
  - Verify new visit appears with visit_date, reason, diagnosis_codes chips, attachments count 0.
- API contract:
  - Confirm POST payload matches VisitStoreRequest; backend returns success: true with VisitResource.
  - Verify created_by=13 is persisted (if returned by resource) or visible in DB/logs.

[Implementation Order]
Frontend-first changes in smallest cohesive steps and reuse existing patterns.

1) Controller scaffolding
   - Add UI state (vm.ui), form builder (resetVisitForm), open/close handlers.
2) Modal markup
   - Add “Add Visit” button in record-view header; add inline Tailwind modal markup bound to vm.visitForm and vm.ui.showAddVisitModal.
3) Save flow
   - Implement saveVisit(): build payload, call ClinicService.createVisit, handle success/error, reload visits, close modal.
4) Polish and validation
   - Disable Save during in-flight; show loading spinner; basic front-end sanity checks aligned with backend rules; surface backend errors.
5) QA passes
   - Manual tests on a sample record for student and faculty types; edge-case data entry; pagination refresh verification.

task_progress Items:
- [ ] Step 1: Add controller UI state and visit form methods in ClinicRecordViewController (open/close/reset/add/remove)
- [ ] Step 2: Insert “Add Visit” button and inline Tailwind modal markup into record-view.html
- [ ] Step 3: Implement saveVisit() to build payload and call ClinicService.createVisit, then refresh list
- [ ] Step 4: Add error handling, disable buttons during saving, and small UX polish
- [ ] Step 5: Manual test runs (student and faculty records), fix any integration issues
