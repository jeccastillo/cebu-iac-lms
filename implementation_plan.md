# Implementation Plan

[Overview]
Implement an applicant journey logging system that records key milestones into a new table, with integrations across admissions, requirements upload, payments, interviews, and enlistment, and exposes a read API for the journey.

The goal is to introduce a consistent, auditable logging mechanism for applicant lifecycle events. This will be centered on a new table tb_mas_applicant_journey with minimal fields and will hook into existing controllers/services at key points to capture events. The system will standardize log remarks (human-readable messages) and timestamps. It fits into the existing Laravel API by following the current architecture patterns (Services + Controllers + System logging) and integrates where events already occur: Admissions creation, Public Initial Requirements upload, Cashier payment creation (including reservation), Interview schedule/result, and Enlistment. A dedicated read endpoint will allow consumers to fetch an applicant’s journey by applicant_data_id in chronological order.

[Types]  
Add a new table and corresponding model/service to store applicant journey logs.

Detailed specifications:

- Database: tb_mas_applicant_journey
  - id: unsigned big integer, primary key, auto-increment
  - applicant_data_id: unsigned integer, required, references tb_mas_applicant_data.id (guard foreign key to avoid legacy FK failures)
  - remarks: string(255) or text, required; stores human-readable event description
  - log_date: datetime, required; no timezone handling; defaults to current timestamp when not provided
  - Indexes:
    - idx_journey_applicant_data_id on (applicant_data_id)
    - idx_journey_log_date on (log_date)
  - No created_at/updated_at columns (per requirement)

- Model: App\Models\ApplicantJourney
  - protected $table = 'tb_mas_applicant_journey'
  - public $timestamps = false
  - $fillable = ['applicant_data_id', 'remarks', 'log_date']

- Service: App\Services\ApplicantJourneyService
  - public function log(int $applicantDataId, string $remarks, ?\Carbon\CarbonInterface $logDate = null): void
    - Inserts a row; if $logDate is null, uses now().
    - Graceful no-op if applicant_data_id is invalid or missing (optional guard).

- Standardized event messages (remarks) to be used:
  - "Student Applied"
  - "Student Submitted [Requirement Name]"
  - "Student paid application fee"
  - "Status was changed to Reserved"
  - "Interview Scheduled"
  - "Interview Result: Passed" or "Interview Result: Failed"
  - "Status was changed to Enlisted"
  - "Status was changed to Enrolled" (when enrollment confirmation is implemented)

[Files]
Add new files for model, service, controller, and migration; update existing controllers/services to emit journey logs.

Detailed breakdown:

- New files:
  - laravel-api/database/migrations/2025_09_04_010500_create_tb_mas_applicant_journey.php
    - Purpose: Create tb_mas_applicant_journey with id, applicant_data_id, remarks, log_date; add indexes; guarded FK if feasible.
  - laravel-api/app/Models/ApplicantJourney.php
    - Purpose: Eloquent model for tb_mas_applicant_journey (no timestamps).
  - laravel-api/app/Services/ApplicantJourneyService.php
    - Purpose: Encapsulate creation of journey entries; single responsibility logging API for other modules.
  - laravel-api/app/Http/Controllers/Api/V1/ApplicantJourneyController.php
    - Purpose: Read-only endpoint to fetch journey logs by applicant_data_id with optional pagination.

- Existing files to be modified:
  - laravel-api/app/Http/Controllers/Api/V1/AdmissionsController.php
    - After inserting a new tb_mas_applicant_data row, call ApplicantJourneyService->log($applicantDataId, 'Student Applied').
  - laravel-api/app/Http/Controllers/Api/V1/PublicInitialRequirementsController.php
    - Method: upload()
    - After successful update and after retrieving the requirement name (already available as $updated->name), call ApplicantJourneyService->log($appData->id, "Student Submitted {$updated->name}").
  - laravel-api/app/Http/Controllers/Api/V1/CashierController.php
    - Method: createPayment()
    - When $isApp is true (application fee detected), log "Student paid application fee" using the resolved applicant_data_id for the student/syid selection (reuse existing logic that already resolves the latest row for updates).
    - When $isRes is true (reservation payment detected and status change to Reserved executed), log "Status was changed to Reserved" using same applicant_data_id resolution.
  - laravel-api/app/Services/ApplicantInterviewService.php
    - Method: schedule()
      - After creating ApplicantInterview row, determine  applicant_data_id (it’s the one used to create the row) and log "Interview Scheduled".
    - Method: submitResult()
      - After updating the interview including assessment and completed_at and setting interviewed flag, log "Interview Result: Passed" or "Interview Result: Failed" based on provided assessment.
  - laravel-api/app/Services/EnlistmentService.php
    - Method: enlist()
    - After successful path where tb_mas_applicant_data.status is updated to "Enlisted", log "Status was changed to Enlisted" for the resolved applicant_data_id row used in that update.
  - laravel-api/app/Services/RegistrationService.php (if/where enrollment confirmation is finalized)
    - Identify function where a student transitions to enrolled (likely intROG or status change).
    - Hook: After enrollment confirmation, log "Status was changed to Enrolled" against the latest applicant_data_id for the student in the relevant term.
    - If no definitive enrollment function exists yet, place a TODO marker and skip code changes for now.
  - laravel-api/routes/api.php
    - Add GET /api/v1/admissions/applicant-data/{applicantDataId}/journey → ApplicantJourneyController@index (role: admissions, admin, registrar as appropriate).

- Files to be deleted or moved:
  - None.

- Configuration updates:
  - None.

[Functions]
Introduce the ApplicantJourneyService::log and integrate small log calls in existing workflows.

Detailed breakdown:

- New functions:
  - App\Services\ApplicantJourneyService::log(int $applicantDataId, string $remarks, ?\Carbon\CarbonInterface $logDate = null): void
    - Inserts into tb_mas_applicant_journey (applicant_data_id, remarks, log_date).
  - App\Http\Controllers\Api\V1\ApplicantJourneyController::index(int $applicantDataId, Request $request): JsonResponse
    - Query tb_mas_applicant_journey where applicant_data_id = param, orderBy log_date asc, returns array of { id, applicant_data_id, remarks, log_date }.
    - Optional query params: page, perPage (if present, paginate).

- Modified functions:
  - AdmissionsController::store(...) or equivalent method that persists applicant_data:
    - Add call to ApplicantJourneyService->log($newApplicantDataId, 'Student Applied').
  - PublicInitialRequirementsController::upload(string $hash, int $appReqId, Request $request): JsonResponse
    - After updating the requirement and retrieving $updated (joined with requirements), add ApplicantJourneyService->log((int)$appData->id, "Student Submitted {$updated->name}").
  - CashierController::createPayment($id, CashierPaymentStoreRequest $request)
    - In the post-payment hook where $isApp is true: ApplicantJourneyService->log($applicantDataIdResolved, 'Student paid application fee').
    - In the same post-payment hook where $isRes is true and status changed: ApplicantJourneyService->log($applicantDataIdResolved, 'Status was changed to Reserved').
    - Resolve $applicantDataIdResolved by reusing existing logic that finds applicant_data row considering (user_id, syid) or latest fallback (same as current update code path).
  - ApplicantInterviewService::schedule(...)
    - After create: ApplicantJourneyService->log($applicantDataId, 'Interview Scheduled').
  - ApplicantInterviewService::submitResult(...)
    - After successful update: ApplicantJourneyService->log($applicantDataId, 'Interview Result: ' . ucfirst(strtolower($assessment))).
  - EnlistmentService::enlist(array $payload, Request $request): array
    - On the branch where status → 'Enlisted' update succeeds: ApplicantJourneyService->log($applicantDataIdUsedForUpdate, 'Status was changed to Enlisted').
  - RegistrationService (when enrollment is finalized, if applicable)
    - After status changes to enrolled (or equivalent), log "Status was changed to Enrolled".

- Removed functions:
  - None.

[Classes]
Add model, service, and controller for Applicant Journey.

Detailed breakdown:

- New classes:
  - App\Models\ApplicantJourney
    - Table: tb_mas_applicant_journey, no timestamps, fillable: applicant_data_id, remarks, log_date.
  - App\Services\ApplicantJourneyService
    - Methods: log().
  - App\Http\Controllers\Api\V1\ApplicantJourneyController
    - Methods: index().

- Modified classes:
  - App\Http\Controllers\Api\V1\AdmissionsController (append a call to log after applicant creation).
  - App\Http\Controllers\Api\V1\PublicInitialRequirementsController (append log call in upload()).
  - App\Http\Controllers\Api\V1\CashierController (append log calls in createPayment()).
  - App\Services\ApplicantInterviewService (append log calls in schedule and submitResult).
  - App\Services\EnlistmentService (append log call after successful status change to Enlisted).
  - App\Services\RegistrationService (append log call after enrollment, if applicable).

- Removed classes:
  - None.

[Dependencies]
No new external dependencies required.

No composer dependencies are needed; use existing Illuminate/DB and Carbon which are already present with Laravel.

[Testing]
Write feature tests around the new journey behavior.

- New tests:
  - laravel-api/tests/Feature/ApplicantJourneyTest.php
    - test_journey_logs_on_apply: Simulate applicant creation; assert a "Student Applied" row exists for applicant_data_id.
    - test_journey_logs_on_initial_requirement_upload: Seed requirement and perform upload; assert "Student Submitted [Requirement Name]" exists.
    - test_journey_logs_on_application_payment_and_reservation: Create payment with description matching application fee; assert "Student paid application fee". Create reservation payment; assert "Status was changed to Reserved".
    - test_journey_logs_on_interview_schedule_and_result: Schedule interview; assert "Interview Scheduled". Submit result; assert "Interview Result: Passed/Failed".
    - test_journey_logs_on_enlistment: Perform enlistment via service and assert "Status was changed to Enlisted".
    - test_get_journey_endpoint: Insert several logs and GET /api/v1/admissions/applicant-data/{id}/journey; assert order and payload.

- Existing tests to modify:
  - None required; avoid breaking existing tests.

- Validation strategies:
  - Focused DB assertions on tb_mas_applicant_journey.
  - Controller endpoint response assertions for the GET endpoint.

[Implementation Order]
Implement migration, model, and service first; then integrate logging at source events, then add GET endpoint.

1) Migration: create tb_mas_applicant_journey with fields and indexes (no timestamps; datetime log_date).
2) Model: ApplicantJourney (no timestamps).
3) Service: ApplicantJourneyService with log() helper.
4) Controllers/Services integration:
   - AdmissionsController: log "Student Applied".
   - PublicInitialRequirementsController::upload: log "Student Submitted [Requirement Name]".
   - CashierController::createPayment: log "Student paid application fee" and "Status was changed to Reserved".
   - ApplicantInterviewService: log "Interview Scheduled" and "Interview Result: [assessment]".
   - EnlistmentService: log "Status was changed to Enlisted".
   - RegistrationService: log "Status was changed to Enrolled" where finalized (if present; otherwise leave TODO).
5) API: Add ApplicantJourneyController@index and routes entry GET /api/v1/admissions/applicant-data/{applicantDataId}/journey.
6) Tests: Add feature tests for journey events and GET endpoint.
7) Manual verification: exercise key flows via Postman or scripts and verify logs order and content.
