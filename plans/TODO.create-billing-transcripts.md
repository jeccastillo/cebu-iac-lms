# TODO: Registrar Transcripts - "Create Billing" button on History/Reprints

Goal:
- On the Registrar > Transcripts page, add a "Create Billing" action next to each History/Reprints item that does not yet have a corresponding Student Billing. If billing already exists, do not render the button.

Scope:
- Backend (Laravel):
  - Augment history items with billing existence flags.
  - Add endpoint to create billing for a past transcript request idempotently.
  - Wire up API route with proper role middleware.

- Frontend (AngularJS Unity SPA):
  - Expose a service method to call the new endpoint.
  - Add controller method and UI button per history item.
  - Hide the button if `has_billing === true`.

Checklist:
- [ ] Backend: ReportsController::listTranscriptRequests
  - [ ] For each transcript history item, compute:
    - [ ] `has_billing` and `billing_id` by querying tb_mas_student_billing for:
      - `intStudentID = student_id`
      - `syid = first(term_ids)`
      - `LOWER(description)` equals:
        - "transcript of records" if `type==='transcript'`
        - "copy of grades" if `type==='copy'`
- [ ] Backend: ReportsController::createTranscriptBilling
  - [ ] Validate requestId+studentId and load transcript request.
  - [ ] If billing already exists (same criteria), respond `{ success:true, already_exists:true, billing_id }`.
  - [ ] Else create billing via `StudentBillingService::create` with:
    - `intStudentID`, `syid = firstTermId`, `description`, `amount` (or 0), `posted_at = date_issued`, `remarks`.
    - Actor from `X-Faculty-ID` header.
  - [ ] Return `{ success:true, data: billingRow }`.
- [ ] Backend: Route
  - [ ] POST `/api/v1/reports/students/{studentId}/transcripts/{requestId}/billing` => ReportsController@createTranscriptBilling
  - [ ] Protect with `role:registrar,admin`.

- [ ] Frontend: reports.service.js
  - [ ] Add `createTranscriptBilling(studentId, requestId)` that POSTs to the above endpoint.
  - [ ] Export this method in the service object.

- [ ] Frontend: transcripts.controller.js
  - [ ] Add `vm.createBilling = createBilling`.
  - [ ] Implement `createBilling(h)`:
    - [ ] Set `h._billingLoading = true`.
    - [ ] Call `ReportsService.createTranscriptBilling(vm.studentId, h.id)`.
    - [ ] On success, reload `loadHistory(vm.studentId)` to refresh has_billing flags.
    - [ ] On error, set `vm.error.history = 'Failed to create billing.'`.
    - [ ] Finally clear `_billingLoading`.

- [ ] Frontend: transcripts.html
  - [ ] In Actions column, add "Create Billing" action next to "Reprint":
    - [ ] Render only when `!h.has_billing`.
    - [ ] Disable when `h._billingLoading` or `vm.loading.history`.
    - [ ] Keep compact styling consistent with existing actions.

Verification:
- [ ] Load a student with transcript history.
- [ ] Confirm history rows display has_billing state (button shows only when false).
- [ ] Click "Create Billing" and confirm:
  - [ ] API returns 200 and creates row in `tb_mas_student_billing`.
  - [ ] History refresh hides the button for that row.

Notes:
- Existing generation flow already attempts billing creation; this feature is for historical reconciliation.
- Idempotence ensured by server-side existence check per item and term/description.
