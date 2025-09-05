# Initial Requirements (Public Upload) - TODO

Scope:
- Improve UX and resiliency of the public initial requirements upload page.
- Ensure client-side validation matches backend rules.
- Provide graceful handling for invalid/expired links.

Status Legend:
- [x] Done
- [ ] Pending

Work Items:

1) Controller enhancements (initial-requirements.controller.js)
- [x] Add vm.error and vm.invalid flags for better error states.
- [x] Set vm.invalid on 404 from API; show friendly invalid/expired message.
- [x] Capture other API errors into vm.error; display non-intrusive alert panel.
- [x] Add drag-and-drop handler (vm.onDrop) using ng-file-upload; reuse existing validation + upload logic.
- [x] Keep accept list and size/type validation aligned with backend (10MB max, mimes: pdf, images, xls, xlsx, csv).

2) Template updates (initial-requirements.html)
- [x] Add invalid/expired-link panel when vm.invalid is true.
- [x] Add generic error panel when vm.error is set and vm.invalid is false.
- [x] Add per-row drag-and-drop area (ngf-drop), alongside existing click-to-upload.
- [x] Preserve existing progress bar, chips, and View file link.
- [x] Hide student summary, instructions, requirements list, and footer when vm.invalid.

3) Optional
- [x] Hide global sidebar/header for this public route (route-aware toggle). Implemented via $rootScope.hideChrome (core/run.js) and ng-if on app-header/app-sidebar (index.html).
- [x] Add copy-to-clipboard for Admissions support email. Implemented vm.supportEmail + vm.copySupportEmail() with SweetAlert feedback (controller + template).
- [x] Replace-file drag-and-drop supported. Per-row drop zone (ngf-drop) uploads for both first-time and replace flows (same app_req_id), using existing uploadFile() path.
- [x] Optional return link back to application provided via vm.returnUrl (query param ?return=...), rendered above the content.

Notes:
- Route: /public/initial-requirements/:hash
- API:
  - GET /public/initial-requirements/{hash}
  - POST multipart /public/initial-requirements/{hash}/upload/{appReqId} (field: file)
- Backend enforces: mimes pdf,jpg,jpeg,png,gif,webp,xls,xlsx,csv and max size 10MB.

Verification Checklist:
- [ ] Invalid hash shows friendly invalid/expired link message panel.
- [ ] Valid hash loads student info and requirements list.
- [ ] Upload with disallowed type or >10MB shows client-side warning.
- [ ] Drag-and-drop and click-to-upload both work and show progress.
- [ ] Successful upload updates status chip and View file link without reload.
- [ ] Error during upload shows clear error message.
