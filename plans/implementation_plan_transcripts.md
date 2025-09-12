# Implementation Plan

[Overview]
Implement end-to-end Transcript/Copy-of-Grades generation with billing: resolve the fee from Payment Descriptions, prompt a registrar confirmation before generating, persist a transcript_requests record, auto-create a student billing row, and allow reprinting of saved transcripts with a history view and appropriate API routes.

This change integrates with the existing Laravel API and AngularJS (unity-spa) frontend. The backend will expose endpoints to: 1) fetch the applicable fee based on type (Transcript of Records vs Copy of Grades), 2) list prior transcript requests for a student (history for reprint), and 3) reprint a previously saved request. The existing POST that renders a transcript PDF will be extended to save the request and create a corresponding billing item in tb_mas_student_billing. The frontend Registrar Transcripts page will fetch fee, show a native confirmation dialog before billing, submit generation, and display history with reprint actions.

This implementation is necessary to connect Transcript/Copy-of-Grades generation to billing and auditing while ensuring registrar users are aware of the charge prior to generating. It fits into the current roles/middleware (role:registrar,admin), leverages the provided StudentTranscriptPdf renderer, TranscriptRequest model and migrations for transcript_requests and tb_mas_student_billing, and adheres to existing patterns for API/SPA integration.

[Types]  
Type additions/clarifications for API requests/responses and model schema references.

- API DTOs
  - TranscriptFeeResponse
    - type: 'transcript' | 'copy' (required)
    - payment_description_id: int|null
    - description: string (e.g., 'Transcript of Records' or 'Copy of Grades')
    - amount: number|null (decimal with 2 places; null when not configured)
    - currency: string (default 'PHP')
    - found: boolean (true if PaymentDescription found with amount)
    - note: string (optional info when not configured)
  - TranscriptRequestDTO (history listing)
    - id: int
    - student_id: int
    - student_number: string|null
    - type: 'transcript' | 'copy'
    - payment_description_id: int|null
    - amount: number|null
    - term_ids: number[] (array of syid)
    - campus_id: int|null
    - date_issued: string|null ('YYYY-mm-dd HH:ii:ss')
    - prepared_by: string
    - verified_by: string
    - registrar_signatory: string
    - signatory: string
    - remarks: string|null
    - created_by_faculty_id: int|null
    - created_at: string
  - Reprint PDF
    - Binary PDF streamed inline (Content-Type: application/pdf), filename pattern: 
      - transcript: transcript-<student_number?>-YYYYmmdd-HHMMSS-reprint.pdf
      - copy: copy-of-grades-<student_number?>-YYYYmmdd-HHMMSS-reprint.pdf

- Backend database schemas (existing)
  - transcript_requests (see migration 2025_09_11_120000_create_transcript_requests_table.php)
    - id, student_id, student_number, type, payment_description_id, amount, term_ids (json), campus_id, date_issued, prepared_by, verified_by, registrar_signatory, signatory, remarks, created_by_faculty_id, timestamps
  - tb_mas_student_billing (see migration 2025_08_30_001100_create_tb_mas_student_billing.php)
    - intID, intStudentID, syid, description, amount, posted_at, remarks, created_by, updated_by, created_at, updated_at

[Files]
Modify existing backend and frontend files, add 1 backend script (optional for local testing).

- New files to be created
  - laravel-api/scripts/test_transcript_billing.php
    - Purpose: Dev/QA script to simulate generation and dump latest transcript_requests and tb_mas_student_billing entries for a student+term.

- Existing files to be modified
  - laravel-api/routes/api.php
    - Add three routes (role:registrar,admin):
      - GET /api/v1/reports/transcript-fee → ReportsController@transcriptFee
      - GET /api/v1/reports/students/{studentId}/transcripts → ReportsController@listTranscriptRequests
      - GET /api/v1/reports/students/{studentId}/transcripts/{requestId}/reprint → ReportsController@reprintTranscript
  - laravel-api/app/Http/Controllers/Api/V1/ReportsController.php
    - Add methods:
      - transcriptFee(Request $request): JsonResponse
      - listTranscriptRequests(int $studentId, Request $request): JsonResponse
      - reprintTranscript(int $studentId, int $requestId, Request $request)
    - Modify existing studentTranscriptPdf (the POST /reports/students/{id}/transcript handler):
      - After successful PDF data assembly, persist a transcript_requests row (via TranscriptRequest::create).
      - Resolve applicable amount; create tb_mas_student_billing record via StudentBillingService::create with description and amount (0.00 when amount is null), posted_at = date_issued, remarks indicating auto-generation; include created_by_faculty_id from X-Faculty-ID header.
  - frontend/unity-spa/features/registrar/reports.service.js
    - Add methods:
      - transcriptFee(params): GET /reports/transcript-fee
      - listTranscriptRequests(studentId): GET /reports/students/{id}/transcripts
      - reprintTranscript(studentId, requestId): GET (arraybuffer) /reports/students/{id}/transcripts/{requestId}/reprint
    - Keep generateStudentTranscript as-is for POST, using responseType: 'arraybuffer' and X-Faculty-ID header when available.
  - frontend/unity-spa/features/registrar/transcripts/transcripts.controller.js
    - Extend controller:
      - On selectStudent(), call loadHistory() and loadTerms() for selected student.
      - Before generate(), fetch fee via ReportsService.transcriptFee(). Show native window.confirm (no SweetAlert) with message:
        - When configured: "This action will bill the student for 'Transcript of Records' or 'Copy of Grades' in the amount of ₱{amount}. Proceed?"
        - When not configured: "Amount not configured; proceed anyway?"
      - If confirmed, POST generate; on success open a new tab with PDF and reload history.
      - Expose reprint(request) action to open arraybuffer response from reprint API in a new tab.
  - frontend/unity-spa/features/registrar/transcripts/transcripts.html
    - Add "History / Reprints" section listing prior transcript_requests for selected student with: Created At, Type, Amount, Terms (syid list), and a Reprint button per entry.

- Files to be deleted or moved
  - None.

- Configuration file updates
  - None (rely on existing APP_CONFIG.API_BASE and role middleware).

[Functions]
Introduce three backend controller methods, modify one; add three frontend service methods and controller methods for fee confirmation, history and reprint.

- New backend functions
  - App\Http\Controllers\Api\V1\ReportsController::transcriptFee(Request $request): JsonResponse
    - Params: type='transcript'|'copy'
    - Behavior: Resolve PaymentDescription by type ('Transcript of Records' or 'Copy of Grades'). Return amount and pd id or null if not configured.
  - App\Http\Controllers\Api\V1\ReportsController::listTranscriptRequests(int $studentId, Request $request): JsonResponse
    - Behavior: Return latest N (default 50) transcript_requests by student id, ordered desc by created_at.
  - App\Http\Controllers\Api\V1\ReportsController::reprintTranscript(int $studentId, int $requestId, Request $request)
    - Behavior: Authorize access, read transcript_requests row belonging to student, reconstruct DTO (student + chosen terms + metadata), render PDF using StudentTranscriptPdf, stream inline with filename including '-reprint'.

- Modified backend functions
  - App\Http\Controllers\Api\V1\ReportsController::studentTranscriptPdf(Request $request, int $studentId)
    - Add persistence to transcript_requests with the incoming payload (type, term_ids, date_issued, etc.) and resolved payment_description_id, amount.
    - After save, create tb_mas_student_billing entry using StudentBillingService::create with:
      - intStudentID = studentId
      - syid = first selected term id (term_ids[0])
      - description = 'Transcript of Records' when type='transcript', 'Copy of Grades' when type='copy'
      - amount = resolved fee or 0.00 (when not configured)
      - posted_at = date_issued
      - remarks = 'Auto-billed upon transcript generation' + optional '(no amount configured)'
      - actorId = X-Faculty-ID header (when present)
    - Ensure that the PDF response remains inline with appropriate filename.

- Removed backend functions
  - None.

- New frontend functions
  - ReportsService.transcriptFee({ type })
  - ReportsService.listTranscriptRequests(studentId)
  - ReportsService.reprintTranscript(studentId, requestId)
  - Controller:
    - loadHistory()
    - reprint(req)
    - Update generate() to confirm and reload history on success

[Classes]
No new classes; integrate with existing ones.

- New classes
  - None.

- Modified classes
  - App\Http\Controllers\Api\V1\ReportsController
    - Add three methods and internal helpers (e.g., resolvePaymentDescriptionByType, normalizeTermIds array).
  - Angular Controller RegistrarTranscriptsController
    - Augment methods and state: vm.history, vm.loadHistory, vm.reprint, confirmation in vm.generate.

- Removed classes
  - None.

[Dependencies]
No new external packages.

- Backend
  - Leverage existing App\Models\TranscriptRequest, App\Models\PaymentDescription, App\Services\StudentBillingService, App\Services\Pdf\StudentTranscriptPdf.
  - Use Illuminate\Support\Facades\DB for joins/lookups where needed.

- Frontend
  - Use native window.confirm as requested (do not use SweetAlert).
  - Continue using StorageService for X-Faculty-ID header passthrough.

[Testing]
Manual and automated verifications to ensure fee resolution, persistence, billing, and reprint work as expected.

- Backend
  - Lint ReportsController.php (php -l) after edits.
  - Optional script: laravel-api/scripts/test_transcript_billing.php
    - Simulate POST /api/v1/reports/students/{id}/transcript with payload and then GET fee/history endpoints.
    - Verify transcript_requests row is created with expected fields.
    - Verify tb_mas_student_billing row is created for the chosen term (amount 0.00 when fee not configured).
  - Feature tests (optional, patterned after tests/Feature/InvoicePdfTest.php):
    - Fee endpoint returns amount and headers.
    - POST returns 200 PDF, creates transcript_requests and student_billing rows.
    - Reprint returns inline PDF with expected filename, 404 when not found or mismatched student.

- Frontend
  - Registrar Transcripts page:
    - Select student → loads terms and history.
    - Change type between Transcript/Copy → fee call returns appropriate description/amount.
    - Generate with fee configured → confirm dialog shows amount; proceed → opens PDF and records appear in history.
    - Generate with fee not configured → confirm dialog shows “Amount not configured; proceed anyway?”; proceed → opens PDF and a zero-amount billing is created.
    - Reprint button opens PDF in new tab with '-reprint' in filename.

[Implementation Order]
Implement backend first to stabilize APIs, then wire frontend UI and services.

1) Backend: ReportsController
   - Add transcriptFee() and listTranscriptRequests() with role guard via routes.
   - Add reprintTranscript() method reusing existing StudentTranscriptPdf data-paths.
   - Modify studentTranscriptPdf() to save TranscriptRequest and create StudentBillingService entry after PDF render prep.
   - Add private helpers (resolvePaymentDescriptionByType, pluckStudentNumber, sanitizeTermIds).
2) Backend: Routes
   - Register GET /reports/transcript-fee, GET /reports/students/{studentId}/transcripts, GET /reports/students/{studentId}/transcripts/{requestId}/reprint with middleware role:registrar,admin.
3) Backend: Quick smoke test
   - php -l on ReportsController, hit fee endpoint via Postman/curl.
4) Frontend: ReportsService
   - Implement transcriptFee(), listTranscriptRequests(), reprintTranscript() with responseType where applicable.
5) Frontend: RegistrarTranscriptsController
   - Add vm.history state, loadHistory(), reprint(), and update generate() to confirm using window.confirm, then submit and reload history.
6) Frontend: Template
   - Add History/Reprints UI section with reprint actions and error states.
7) Manual QA
   - Run through acceptance criteria end-to-end with a test student across both types.
8) Optional: Add dev script laravel-api/scripts/test_transcript_billing.php to aid local verification.
