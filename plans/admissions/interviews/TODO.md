# Admissions Interviews: Implementation TODO

Scope: Implement interview scheduling and results linked to tb_mas_applicant_data.id with a single interview per applicant, and flip the interviewed flag on result submission. Perform thorough testing, including impacted applicants endpoints.

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
- [ ] Step 10: Execute migrations, run phpunit, and perform thorough API testing (happy/error paths, auth, duplicates, migration up/down)

Details and Acceptance Criteria:

1) Migration: tb_mas_applicant_interviews
- Columns:
  - id (PK), applicant_data_id (INT, NOT NULL, UNIQUE), scheduled_at (DATETIME, NOT NULL)
  - interviewer_user_id (INT, NULL), remarks (TEXT, NULL)
  - assessment (ENUM('Passed','Failed'), NULL)
  - reason_for_failing (VARCHAR(255), NULL)
  - completed_at (DATETIME, NULL)
  - timestamps
- Indexes:
  - UNIQUE(applicant_data_id)
  - INDEX(interviewer_user_id)
- Guarded foreign keys (attempt to add, ignore failure gracefully):
  - applicant_data_id -> tb_mas_applicant_data(id) ON DELETE CASCADE
  - interviewer_user_id -> tb_mas_users(intID) ON UPDATE CASCADE ON DELETE SET NULL

2) Migration: add interviewed to tb_mas_applicant_data
- Column:
  - interviewed (BOOLEAN/TINYINT) NOT NULL DEFAULT 0
- Rollback: remove column if exists

3) Model: App\Models\ApplicantInterview
- $table = 'tb_mas_applicant_interviews'
- $fillable: ['applicant_data_id','scheduled_at','interviewer_user_id','remarks','assessment','reason_for_failing','completed_at']
- $casts: scheduled_at => 'datetime', completed_at => 'datetime'

4) Requests:
- ApplicantInterviewScheduleRequest:
  - Rules: applicant_data_id required|integer|exists tb_mas_applicant_data.id; scheduled_at required|date; interviewer_user_id nullable|integer
- ApplicantInterviewResultRequest:
  - Rules: assessment required|in:Passed,Failed; remarks nullable|string; reason_for_failing required_if:assessment,Failed|max:255|nullable; completed_at nullable|date

5) Service: App\Services\ApplicantInterviewService
- schedule(applicantDataId, scheduledAt, interviewerUserId?, request ctx)
  - Enforce uniqueness per applicant_data_id
  - Create interview row
  - SystemLogService::log create
- submitResult(interviewId, assessment, remarks?, reason?, completedAt?, request ctx)
  - Validate constraint reason when Failed
  - In transaction:
    - Update interview with assessment/remarks/reason/completed_at (default now)
    - Update tb_mas_applicant_data.interviewed = true (where id = applicant_data_id)
  - SystemLogService::log update

6) Controller: App\Http\Controllers\Api\V1\ApplicantInterviewController
- store(ScheduleRequest): 201 { data: interview }
- show(id): 200/404
- showByApplicantData(applicantDataId): 200/404
- submitResult(ResultRequest, id): 200 updated interview + applicant_data.interviewed

7) Routes (role: admissions,admin)
- POST /v1/admissions/interviews -> store
- GET /v1/admissions/interviews/{id} -> show
- GET /v1/admissions/applicant-data/{applicantDataId}/interview -> showByApplicantData
- PUT /v1/admissions/interviews/{id}/result -> submitResult

8) Modify ApplicantController
- index(): include ad.interviewed as interviewed in select and payload
- show(): include interviewed flag from latest applicant_data row; optional basic interview summary (scheduled_at, assessment, completed_at) if available

9) Tests (Feature)
- test_can_schedule_interview_for_applicant_data
- test_cannot_schedule_multiple_interviews_for_same_applicant_data
- test_submit_result_sets_interviewed_flag_true
- test_submit_result_requires_reason_when_failed
- test_show_by_applicant_data_returns_404_when_absent
- test_auth_and_roles_for_endpoints
- test_applicants_index_and_show_include_interviewed_flag

10) Manual verification
- Migrate up/down success on dev env (with index and guarded foreign keys)
- cURL smoke for all endpoints (happy/error paths); verify SystemLog entries (if visible)

Notes
- Link target confirmed: tb_mas_applicant_data.id
- Single interview per applicant (no reschedules/multiples)
- Track interviewer_user_id
- Assessment values strictly: Passed, Failed
- completed_at saved at result submission; scheduled_at at scheduling
