# Laravel API - Student Module Baseline TODO

Context
- Goal: Implement StudentController baseline with requests/resources/service and routes, aligned with implementation_plan.md.
- Approach: Thin controller, pragmatic DB::table queries in a service, standardized JSON envelopes, reuse PortalController field mappings.

Tasks
1) Form Requests
- [x] app/Http/Requests/Api/V1/StudentLookupRequest.php
- [x] app/Http/Requests/Api/V1/StudentRecordsRequest.php
- [x] app/Http/Requests/Api/V1/StudentBalanceRequest.php

2) API Resources
- [x] app/Http/Resources/StudentResource.php
- [x] app/Http/Resources/StudentBalanceResource.php
- [x] app/Http/Resources/TransactionResource.php

3) Service Layer
- [x] app/Services/DataFetcherService.php
  - [x] getStudentByToken(string $token)
  - [x] getStudentByNumber(string $studentNumber)
  - [x] getStudentBalances(string $studentNumber)
  - [x] getStudentRecords(string $studentNumber, ?string $term, bool $includeGrades)
  - [x] getStudentLedger(string $studentNumber)

4) Controller
- [x] app/Http/Controllers/Api/V1/StudentC{ontroller.php
  - [x] viewer(StudentLookupRequest $request)
  - [x] balances(StudentBalanceRequest $request)
  - [x] records(StudentRecordsRequest $request)
  - [x] ledger(StudentBalanceRequest $request)

5) Routes
- [x] Update laravel-api/routes/api.php (under Route::prefix('v1'))
  - [x] POST /student/viewer
  - [x] POST /student/balances
  - [x] POST /student/records
  - [x] POST /student/ledger

6) Parity and Validation
- [ ] Reuse PortalController::studentData mapping in StudentResource (field names: first_name, last_name, personal_email, student_number, contact_number, course_id, course_name, last_term, last_term_sy).
- [ ] Ensure envelopes: { "success": true|false, "data"?: any, "message"?: string }.
- [ ] For incomplete endpoints (balances/records/ledger), return 501 Not Implemented with a clear message initially, then iterate.

7) Testing and Reporting
- [ ] Add basic smoke coverage placeholders under tests/Feature/Api/V1 (future)
- [ ] Update laravel-api/tests/test-report.md after manual checks

Notes
- Keep controllers thin: validate → delegate(service) → transform(resource) → envelope.
- Start with viewer endpoint using existing schema/joins; progressively implement balances/records/ledger.
- Consider pagination for ledger in future iterations.
