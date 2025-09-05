# Applicant Journey Logging - TODO

Plan source: implementation_plan.md

Progress Checklist:
- [x] Step 1: Create migration for tb_mas_applicant_journey (id, applicant_data_id, remarks, log_date), indexes, guarded FK
- [x] Step 2: Create Eloquent model App\Models\ApplicantJourney (no timestamps)
- [x] Step 3: Create App\Services\ApplicantJourneyService::log(applicantDataId, remarks, logDate?)
- [x] Step 4: Create read-only API controller App\Http\Controllers\Api\V1\ApplicantJourneyController@index
- [x] Step 5: Add GET route /api/v1/admissions/applicant-data/{applicantDataId}/journey (middleware role: admissions,registrar,admin)
- [x] Step 6: Integrations
  - [x] AdmissionsController: after applicant_data insert, log "Student Applied"
  - [x] PublicInitialRequirementsController::upload: log "Student Submitted [Requirement Name]"
  - [x] CashierController::createPayment: log "Student paid application fee" and "Status was changed to Reserved"
  - [x] ApplicantInterviewService::schedule: log "Interview Scheduled"
  - [x] ApplicantInterviewService::submitResult: log "Interview Result: Passed/Failed"
  - [x] EnlistmentService::enlist: log "Status was changed to Enlisted"
- [ ] Step 7: Smoke tests/manual verification (post-implementation)
  - [ ] Run migration and verify schema
  - [ ] Exercise key endpoints and confirm logs persisted and retrievable via journey endpoint

Notes:
- applicant_data_id is the only linkage key.
- log_date is datetime; no created_at/updated_at columns in the table.
- Log every requirements upload.
