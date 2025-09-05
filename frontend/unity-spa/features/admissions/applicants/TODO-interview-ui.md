# Applicant Interview UI - Implementation TODO

Scope: Add Interview scheduling/result UI to the Applicant Details page and wire it to the existing Laravel API.

## Steps

- [ ] Backend: Include `applicant_data_id` in `GET /api/v1/applicants/{id}` response
  - File: `laravel-api/app/Http/Controllers/Api/V1/ApplicantController.php`
  - Add to response payload: `"applicant_data_id" => (int) $appData->id`
  - Purpose: Frontend needs the latest applicant_data row id to call interview endpoints.

- [ ] Frontend: Create InterviewsService
  - New file: `frontend/unity-spa/features/admissions/interviews/interviews.service.js`
  - Methods:
    - `getByApplicantData(applicantDataId)` - GET `/api/v1/admissions/applicant-data/{applicantDataId}/interview`
    - `schedule(applicantDataId, payload)` - POST `/api/v1/admissions/interviews`
      - payload: `{ applicant_data_id, scheduled_at, interviewer_user_id?, remarks? }`
    - `submitResult(interviewId, payload)` - PUT `/api/v1/admissions/interviews/{id}/result`
      - payload: `{ assessment: 'Passed'|'Failed'|'No Show', remarks?, reason_for_failing?, completed_at? }`
  - Reuse header convention like `ApplicantsService` (include `X-Faculty-ID` from `StorageService` when available).

- [ ] Frontend: Update ApplicantViewController
  - File: `frontend/unity-spa/features/admissions/applicants/applicants.controller.js`
  - Inject `InterviewsService`.
  - State:
    - `vm.applicant_data_id`
    - `vm.interview`, `vm.interviewLoading`, `vm.interviewError`
    - `vm.scheduling`, `vm.resultSaving`
    - `vm.schedule = { scheduled_at: '', remarks: '' }`
    - `vm.result = { assessment: '', remarks: '', reason_for_failing: '' }`
    - `vm.canManageInterview = RoleService.hasAny(['admissions','admin'])`
  - After loading applicant: set `vm.applicant_data_id` and call `InterviewsService.getByApplicantData`; 404 means no interview yet.
  - Methods:
    - `vm.scheduleInterview()`
    - `vm.submitPassed()`
    - `vm.submitFailed()` (require `reason_for_failing`)
    - `vm.submitNoShow()`
    - `vm.reloadInterview()`

- [ ] Frontend: Add Interview UI to view
  - File: `frontend/unity-spa/features/admissions/applicants/view.html`
  - Add an "Interview" card:
    - Header badge:
      - No interview: "Not Scheduled"
      - Scheduled (no result): "Scheduled for {datetime}"
      - Completed: "Completed — Passed/Failed/No Show"
    - Body:
      - If none and can manage: show schedule form (datetime-local + remarks)
      - If exists:
        - Show details: scheduled_at, remarks, interviewer_user_id, assessment, reason_for_failing, completed_at
        - If not completed and can manage: actions (Passed, Failed with reason, No Show)
  - Follow existing Tailwind-like utility classes and error/info patterns.

- [ ] Manual test
  - Pick an applicant with no interview
  - Schedule an interview; verify badge and interview section update
  - Submit result (Passed/Failed/No Show) and verify interviewed flag and UI refresh
  - Confirm Applicants list and details reflect `interviewed` status

- [ ] Clean-up
  - Update this TODO file as steps are completed

## API Reference (Backend already implemented)

- Schedule: `POST /api/v1/admissions/interviews`
  - Body: `{ applicant_data_id: number, scheduled_at: "YYYY-MM-DD HH:mm:ss", interviewer_user_id?: number|null, remarks?: string }`
  - Returns: `{ success: true, data: { id, applicant_data_id, scheduled_at, ... } }`

- Fetch by applicant_data: `GET /api/v1/admissions/applicant-data/{applicantDataId}/interview`
  - Returns 200 with data or 404 if none

- Submit result: `PUT /api/v1/admissions/interviews/{id}/result`
  - Body: `{ assessment: 'Passed'|'Failed'|'No Show', remarks?: string, reason_for_failing?: string, completed_at?: 'YYYY-MM-DD HH:mm:ss' }`
  - Returns: `{ success: true, data: { ..., applicant_data_interviewed: boolean } }`
