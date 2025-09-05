# Implementation Plan

[Overview]
Introduce interview scheduling and result tracking for applicants by linking interview records to tb_mas_applicant_data.id, enforcing one interview per applicant, and adding an interviewed flag on tb_mas_applicant_data that flips to true upon result submission.

The scope includes database migrations for a new interview table and a boolean flag on applicant data, backend endpoints for creating a schedule and submitting results, validation rules, and service-layer transaction logic to ensure atomic updates. The changes fit into the existing Admissions API (Laravel) alongside Applicant-related endpoints. We will keep constraints defensive (unique per applicant, guarded FKs) to avoid legacy FK pitfalls evident in prior migrations.

[Types]  
Add a dedicated table for interviews linked to the latest applicant data row, plus a boolean flag on applicant data.

Detailed type definitions and constraints:
- Table: tb_mas_applicant_interviews
  - id: INT AUTO_INCREMENT PRIMARY KEY
  - applicant_data_id: INT NOT NULL
    - Relationship: References tb_mas_applicant_data.id
    - Uniqueness: UNIQUE (one interview per applicant data)
    - Index: idx_ai_applicant_data_id (also used by FK when supported)
  - scheduled_at: DATETIME NOT NULL
  - interviewer_user_id: INT NULL
    - Purpose: User who will conduct/conducted the interview (tb_mas_users.intID)
    - Index: idx_ai_interviewer_user_id (FK optional due to historical FK issues)
  - remarks: TEXT NULL
  - assessment: ENUM('Passed','Failed') NULL
  - reason_for_failing: VARCHAR(255) NULL
    - Validation: Required when assessment = 'Failed'
  - completed_at: DATETIME NULL
    - Set when results are submitted; separate from scheduled_at
  - created_at: DATETIME (Laravel timestamps)
  - updated_at: DATETIME (Laravel timestamps)
  - Constraints:
    - UNIQUE (applicant_data_id) to enforce one interview per applicant
    - Foreign Keys (guarded):
      - FK to tb_mas_applicant_data(id) ON DELETE CASCADE (try/catch guarded add; index regardless)
      - Optional FK to tb_mas_users(intID) for interviewer_user_id (guarded; index regardless)

- Column addition: tb_mas_applicant_data
  - interviewed: TINYINT(1) NOT NULL DEFAULT 0
    - Boolean semantics in Laravel (cast to bool on Model if needed)

Validation rules:
- Schedule creation (ApplicantInterviewScheduleRequest):
  - applicant_data_id: required|integer|exists:tb_mas_applicant_data,id
  - scheduled_at: required|date
  - interviewer_user_id: nullable|integer
  - Business rule: Reject if an interview already exists for the applicant_data_id (422)
- Result submission (ApplicantInterviewResultRequest):
  - assessment: required|in:Passed,Failed
  - remarks: nullable|string
  - reason_for_failing: required_if:assessment,Failed|max:255|nullable
  - completed_at: nullable|date (default to now() if absent)
  - Business rule: Only allowed once; if interview already has completed_at, respond 409/422

[Files]
Create new migrations, controller, requests, model, and service; update routes and ApplicantController surfacing.

Detailed breakdown:
- New files:
  - laravel-api/database/migrations/2025_09_04_000900_create_tb_mas_applicant_interviews.php
    - Creates tb_mas_applicant_interviews with columns and guarded FKs, unique index on applicant_data_id.
  - laravel-api/database/migrations/2025_09_04_001000_add_interviewed_to_tb_mas_applicant_data.php
    - Adds interviewed boolean (default false) to tb_mas_applicant_data; drops on down().
  - laravel-api/app/Models/ApplicantInterview.php
    - Eloquent model for tb_mas_applicant_interviews; casts; guarded attributes.
  - laravel-api/app/Http/Requests/Api/V1/ApplicantInterviewScheduleRequest.php
    - Validates schedule creation payload.
  - laravel-api/app/Http/Requests/Api/V1/ApplicantInterviewResultRequest.php
    - Validates result submission payload.
  - laravel-api/app/Http/Controllers/Api/V1/ApplicantInterviewController.php
    - Endpoints to create schedule, show interview, submit result; role: admissions,admin.
  - laravel-api/app/Services/ApplicantInterviewService.php
    - Encapsulates schedule and result logic, ensures atomic flag updates with transactions; logs via SystemLogService.

- Existing files to be modified:
  - laravel-api/routes/api.php
    - Add routes:
      - POST /v1/admissions/interviews (schedule)
      - GET /v1/admissions/interviews/{id} (show)
      - GET /v1/admissions/applicant-data/{applicantDataId}/interview (show by applicant_data_id)
      - PUT /v1/admissions/interviews/{id}/result (submit result)
    - Middleware: role:admissions,admin
  - laravel-api/app/Http/Controllers/Api/V1/ApplicantController.php
    - Include 'interviewed' in select/show payload, surfaced from tb_mas_applicant_data.
    - Optional: Include basic interview summary (scheduled_at, assessment, completed_at) via left join or a secondary fetch.

- Files to be deleted or moved:
  - None.

- Configuration updates:
  - None required.

[Functions]
Define new controller actions and service methods; minor augmentation to ApplicantController.

Detailed breakdown:
- New functions:
  - App\Services\ApplicantInterviewService
    - public function schedule(int $applicantDataId, \Carbon\Carbon $scheduledAt, ?int $interviewerUserId, \Illuminate\Http\Request $ctx): object
      - Ensures uniqueness per applicant_data_id
      - Creates interview row
      - SystemLogService::log('create','ApplicantInterview', ...)
    - public function submitResult(int $interviewId, string $assessment, ?string $remarks, ?string $reason, ?\Carbon\Carbon $completedAt, \Illuminate\Http\Request $ctx): object
      - Validates assessment in ['Passed','Failed']; reason required if 'Failed'
      - Sets completed_at (default now) and updates remarks, assessment, reason_for_failing
      - Atomically sets tb_mas_applicant_data.interviewed = true in same transaction
      - SystemLogService::log('update','ApplicantInterview', ... old vs new)

  - App\Http\Controllers\Api\V1\ApplicantInterviewController
    - public function store(ApplicantInterviewScheduleRequest $request): JsonResponse
      - Payload: applicant_data_id, scheduled_at, interviewer_user_id?
      - Returns created interview
    - public function show(int $id): JsonResponse
      - Returns interview details by id
    - public function showByApplicantData(int $applicantDataId): JsonResponse
      - Returns interview details by applicant_data_id (404 if none)
    - public function submitResult(ApplicantInterviewResultRequest $request, int $id): JsonResponse
      - Returns updated interview and the updated applicant_data.interviewed flag

- Modified functions:
  - App\Http\Controllers\Api\V1\ApplicantController@index(Request $request): JsonResponse
    - Add tb_mas_applicant_data.interviewed to selected columns (ad.interviewed as interviewed)
  - App\Http\Controllers\Api\V1\ApplicantController@show(int $id): JsonResponse
    - Include interviewed in response data (surface from latest applicant_data row)
    - Optionally fetch related interview (single row) and surface summary fields

- Removed functions:
  - None.

[Classes]
Introduce a new Eloquent model; optional relationship wiring.

Detailed breakdown:
- New classes:
  - App\Models\ApplicantInterview
    - protected $table = 'tb_mas_applicant_interviews';
    - protected $fillable = ['applicant_data_id','scheduled_at','interviewer_user_id','remarks','assessment','reason_for_failing','completed_at'];
    - protected $casts = [
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
      ];
    - Relationships (optional):
      - applicantData(): belongsTo(\Illuminate\Support\Facades\DB) via manual queries, or define a minimal ApplicantData model if present later.
- Modified classes:
  - App\Http\Controllers\Api\V1\ApplicantController (see Functions above)
- Removed classes:
  - None.

[Dependencies]
No new third-party dependencies.

All work is within the existing Laravel framework; leverage Carbon (already shipped), DB facade, and SystemLogService (already in repo).

[Testing]
Implement feature and database tests focusing on scheduling uniqueness and result submission effects.

Test plan:
- tests/Feature/ApplicantInterviewTest.php
  - test_can_schedule_interview_for_applicant_data
  - test_cannot_schedule_multiple_interviews_for_same_applicant_data
  - test_submit_result_sets_interviewed_flag_true
  - test_submit_result_requires_reason_when_failed
  - test_show_by_applicant_data_returns_404_when_absent
- Manual smoke via tinker or bespoke scripts (optional).
- Ensure ApplicantController index/show payloads include interviewed flag.

[Implementation Order]
Perform schema changes first, then service and controller, route additions, and minor controller augmentation.

1. Create migration 2025_09_04_000900_create_tb_mas_applicant_interviews.php:
   - Table with fields, unique index, guarded FK add (try/catch), indexes for performance.
2. Create migration 2025_09_04_001000_add_interviewed_to_tb_mas_applicant_data.php:
   - Adds interviewed boolean default false; safely drops on down().
3. Add Eloquent model App\Models\ApplicantInterview.
4. Add Requests:
   - ApplicantInterviewScheduleRequest (schedule payload validation)
   - ApplicantInterviewResultRequest (result payload validation)
5. Add Service App\Services\ApplicantInterviewService with schedule() and submitResult() methods, both transactional and logging via SystemLogService.
6. Add Controller App\Http\Controllers\Api\V1\ApplicantInterviewController with store, show, showByApplicantData, submitResult actions.
7. Update routes (laravel-api/routes/api.php) under /v1:
   - POST /admissions/interviews -> store (role:admissions,admin)
   - GET /admissions/interviews/{id} -> show (role:admissions,admin)
   - GET /admissions/applicant-data/{applicantDataId}/interview -> showByApplicantData (role:admissions,admin)
   - PUT /admissions/interviews/{id}/result -> submitResult (role:admissions,admin)
8. Augment ApplicantController index/show to surface ad.interviewed and optionally an interview summary.
9. Write Feature tests as outlined; run phpunit.
10. Smoke test endpoints; ensure applicant_data.interviewed flips true only upon result submission.

task_progress Items:
- [ ] Step 1: Add migration for tb_mas_applicant_interviews with unique applicant_data_id and guarded FKs
- [ ] Step 2: Add migration to tb_mas_applicant_data to include interviewed boolean (default false)
- [ ] Step 3: Create ApplicantInterview Eloquent model with casts and fillable fields
- [ ] Step 4: Create ApplicantInterviewScheduleRequest and ApplicantInterviewResultRequest validators
- [ ] Step 5: Implement ApplicantInterviewService with schedule() and submitResult() (transactional, logging)
- [ ] Step 6: Implement ApplicantInterviewController with store, show, showByApplicantData, submitResult
- [ ] Step 7: Register routes under /v1/admissions with role:admissions,admin
- [ ] Step 8: Modify ApplicantController to surface interviewed flag (and optional interview summary)
- [ ] Step 9: Add Feature tests covering schedule, uniqueness, result submission, and validations
- [ ] Step 10: Execute migrations, run tests, and perform smoke tests
