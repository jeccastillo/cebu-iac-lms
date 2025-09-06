# Implementation Plan

[Overview]
Add a Generate Reg Form (PDF) button on the Registrar Enlistment page that is visible when the selected student’s registration for the selected term has enrollment_status = 'enlisted' or 'enrolled'. When clicked, it opens a student-specific Registration Form PDF (rendered from the repo-root template reg_form.pdf) inline in a new browser tab, with the browser’s standard download option available.

This implementation leverages an existing Laravel API controller method UnityController::regForm that generates the Registration Form PDF using the reg_form.pdf template via setasign/fpdi. We will expose this endpoint via GET /api/v1/unity/reg-form and integrate it in the Unity SPA Enlistment UI. The front-end will construct the correct URL and conditionally render the button based on the current registration’s enrollment_status. The output should stream inline with appropriate headers to allow viewing and downloading from the browser’s native PDF viewer.

[Types]  
No new back-end PHP types or database schema. Minor front-end ViewModel and service shape additions.

Detailed definitions and behaviors:
- Query parameters to GET /api/v1/unity/reg-form:
  - student_number: string (required) – sanitized student number (vm.sanitizeStudentNumber(vm.studentNumber))
  - term: integer (required) – selected term syid (vm.term)
- Front-end VM fields used:
  - vm.registration: object|null – existing registration for the selected student and term
    - vm.registration.enrollment_status: string, must equal 'enlisted' or 'enrolled' to enable the button
  - vm.studentNumber: string – selected student number (may include display suffix; must sanitize)
  - vm.term: number|string – selected term syid (coerced to integer for query)
- Front-end additions:
  - vm.regFormUrl(): () => string – builds the absolute API URL to GET inline PDF
- Validation rules:
  - Button visible only when vm.registration && (vm.registration.enrollment_status === 'enlisted' || vm.registration.enrollment_status === 'enrolled') && vm.studentNumber && vm.term
  - URL building must sanitize student_number to the canonical value before passing as query param

[Files]
We will introduce one new API route and minimal UI updates. No files are deleted or moved.

- New files to be created: None.
- Existing files to be modified:
  - laravel-api/routes/api.php
    - Add GET /api/v1/unity/reg-form mapped to UnityController@regForm and protect with role:registrar,admin (and optionally finance if desired).
  - laravel-api/app/Http/Controllers/Api/V1/UnityController.php
    - Ensure regForm returns a streamed inline PDF with headers:
      - Content-Type: application/pdf
      - Content-Disposition: inline; filename="reg-form-{student_number}-{term}.pdf"
    - Confirm template path uses base_path('../reg_form.pdf') and fails gracefully when missing.
    - Confirm it overlays Student Number, Student Name, Program, Term, Address, Enlisted Subjects (Code, Description, Section, Units), and Assessment summary.
  - frontend/unity-spa/features/unity/unity.service.js
    - Add helper method regFormUrl(student_number: string, term: number): string that returns BASE + '/unity/reg-form?student_number=...&term=...'
  - frontend/unity-spa/features/registrar/enlistment/enlistment.controller.js
    - Add vm.regFormUrl function delegating to UnityService.regFormUrl with sanitized student number and integer term.
  - frontend/unity-spa/features/registrar/enlistment/enlistment.html
    - In the “Current Enlisted (Selected Term)” section header area, add a button aligned to the right:
      - Visible when vm.registration && (vm.registration.enrollment_status === 'enlisted' || vm.registration.enrollment_status === 'enrolled') && vm.studentNumber && vm.term
      - Opens UnityService.regFormUrl(...) in a new tab (target="_blank")
      - Label: “Generate Reg Form (PDF)”
- Configuration file updates: None.

[Functions]
We will add a small utility function in the Unity service and Enlistment controller; the Laravel controller function exists already and must return inline PDF with headers.

- New functions:
  - frontend/unity-spa/features/unity/unity.service.js
    - regFormUrl(student_number: string, term: number): string
      - Returns BASE + '/unity/reg-form?student_number=' + encodeURIComponent(student_number) + '&term=' + term
      - No network call; used to construct a link for target="_blank" inline streaming.
  - frontend/unity-spa/features/registrar/enlistment/enlistment.controller.js
    - vm.regFormUrl(): string
      - Uses vm.sanitizeStudentNumber(vm.studentNumber) and parseInt(vm.term, 10), then calls UnityService.regFormUrl.
      - Returns empty string when prerequisites are missing.
- Modified functions:
  - laravel-api/app/Http/Controllers/Api/V1/UnityController.php
    - public function regForm(Request $request)
      - Ensure it ends with an inline streaming response:
        - $content = $pdf->Output('S'); // string
        - return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
          ]);
      - Include robust try/catch around tuition compute; allow PDF generation even if breakdown fails, with no assessment block values.
- Removed functions: None.

[Classes]
No new classes or inheritance changes.

- New classes: None.
- Modified classes:
  - App\Http\Controllers\Api\V1\UnityController – regForm method behavior (response/headers).
- Removed classes: None.

[Dependencies]
No new third-party dependencies.

- Back-end:
  - setasign/fpdi is already used and configured; no changes required.
- Front-end:
  - No new packages; AngularJS app already includes UnityService to centralize API URL building and admin headers for X-Faculty-ID on AJAX calls. For this download/view, we only need a plain URL; custom headers are not needed since the endpoint does not require request body or protected header for authorization beyond role middleware (cookie/session).

[Testing]
Manual end-to-end validation from the Registrar Enlistment page.

- Preconditions:
  - Select a student and term with current enlisted records and an existing registration row with enrollment_status = 'enlisted'.
- UI tests:
  - Verify the Generate Reg Form (PDF) button is visible only when enrollment_status === 'enlisted'.
  - Click the button and confirm a new tab opens with a PDF inline viewer and browser-native download option is present.
  - Verify PDF content:
    - Header fields: Student Number, Student Name (Last, First Middle), Program, Term, Address
    - Subject table: Code, Description, Section, Units (contains current enlisted of the term)
    - Assessment block shows computed tuition summary if available, or is left blank when compute fails
- API tests:
  - Directly GET /api/v1/unity/reg-form?student_number=SN&term=SYID via browser; confirm content-type and disposition headers.
  - 404 / 500 behavior: Missing student returns 404 JSON error; missing template returns 500 JSON error (as already implemented).
- Non-regression:
  - Enlistment flows and tuition preview/save remain unaffected.

[Implementation Order]
Status and next steps.

- [x] 1. Laravel: GET /api/v1/unity/reg-form route in laravel-api/routes/api.php mapped to UnityController@regForm with middleware('role:registrar,admin').
- [x] 2. Laravel: UnityController::regForm streams inline PDF with filename: reg-form-{student_number}-{term}.pdf using response($pdf->Output('S'), ...) and proper headers.
- [x] 3. Front-end: frontend/unity-spa/features/unity/unity.service.js includes regFormUrl(student_number, term).
- [x] 4. Front-end: frontend/unity-spa/features/registrar/enlistment/enlistment.controller.js exposes vm.regFormUrl() delegating to UnityService.regFormUrl with sanitized SN and parsed term.
- [x] 5. Front-end: frontend/unity-spa/features/registrar/enlistment/enlistment.html adds the button in the “Current Enlisted (Selected Term)” header; visibility now allows 'enlisted' or 'enrolled'.
- [ ] 6. Manual smoke test: Select an enlisted or enrolled student and click the button; verify inline PDF with download.
- [ ] 7. Edge-case test: When not enlisted/enrolled, the button is hidden; when no current enlisted/registration, behavior remains unchanged.

[Progress Update]
- Backend route and controller confirmed present and functional for inline PDF.
- UnityService.regFormUrl implemented and used by vm.regFormUrl.
- Enlistment HTML button updated to be visible when enrollment_status is 'enlisted' or 'enrolled'.
- Decision: per product direction, allow when status is enrolled as well.

[Testing/Verification]
- UI:
  - Button visible only when vm.registration exists, status is 'enlisted' or 'enrolled', and vm.studentNumber and vm.term are set.
  - Clicking opens a new tab with inline PDF; browser-native download is available.
- API:
  - GET {API_BASE}/unity/reg-form?student_number=SN&amp;term=SYID returns Content-Type: application/pdf and Content-Disposition: inline; filename="reg-form-SN-SYID.pdf".
  - Error cases: 404 when student not found; 500 when template missing.
- Non-regression:
  - Enlistment flows and tuition preview/save remain unaffected.
