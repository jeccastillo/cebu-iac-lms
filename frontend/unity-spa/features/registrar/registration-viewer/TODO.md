task_progress Items:
- [x] Step 1: Extend Laravel RegistrationService whitelist to include allow_enroll, downpayment, intROG
- [x] Step 2: Add AngularJS route /registrar/registration/:id in core/routes.js
- [x] Step 3: Create AngularJS controller RegistrationViewerController (registration-viewer.controller.js)
- [x] Step 4: Create AngularJS template registration-viewer.html with term selector, student info, registration meta, tuition summary, ledger, subjects (converted to TailwindCSS)
- [ ] Step 5: Update StudentsController.financesUrl to point to SPA route (#/registrar/registration/:id)
- [ ] Step 6: Critical-path testing of new page (load student, terms, registration, tuition compute, ledger, records-by-term; update registration fields)
- [ ] Step 7: Fix issues from testing (UX, error handling, formatting) and finalize cutover from CI links

Notes:
- Endpoints used (granular):
  - GET /api/v1/students/{id} to resolve student_number from intID
  - GET /api/v1/school-years for terms list
  - GET /api/v1/generic/active-term to default the selected term
  - GET /api/v1/unity/registration?student_number&amp;term (reads existing row)
  - PUT /api/v1/unity/registration (updates whitelisted fields)
  - GET /api/v1/tuition/compute?student_number&amp;term (summary totals)
  - POST /api/v1/student/ledger (by student_number)
  - POST /api/v1/student/records-by-term (student_number, term, include_grades=true)
- Headers: where updates are required, use UnityService to add X-Faculty-ID automatically; reads may use plain $http.
