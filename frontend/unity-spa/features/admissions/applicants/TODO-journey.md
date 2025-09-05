# Applicants — Applicant Journey Timeline

Goal: Add an “Applicant Journey” section to the Applicant Details page and display the journey logs in a vertical timeline.

Backend
- [x] Confirmed endpoint exists: GET /api/v1/admissions/applicant-data/{applicantDataId}/journey (roles: admissions, registrar, admin)
- [x] Response format: { success, data: [ { id, applicant_data_id, remarks, log_date }, ... ], meta? }
- [x] Ordering: log_date ASC

Frontend Changes
- [x] Create ApplicantJourneyService
  - File: features/admissions/applicants/journey.service.js
  - Method: listByApplicantData(applicantDataId, { page?, perPage? })
  - Headers: X-User-ID / X-Faculty-ID (same approach as InterviewsService)
- [x] Wire into ApplicantViewController
  - File: features/admissions/applicants/applicants.controller.js
  - Inject ApplicantJourneyService (added to $inject and function signature)
  - State: vm.journey, vm.journeyLoading, vm.journeyError
  - Method: vm.loadJourney() triggers after vm.applicant_data_id is set (alongside vm.loadInterview())
- [x] Add UI section (timeline)
  - File: features/admissions/applicants/view.html
  - New card “Applicant Journey” (above Initial Requirements)
  - Loading skeleton, error banner, empty state
  - Timeline markup: vertical line with dots; show j.log_date and j.remarks (pre-wrap)

Testing Notes
- Navigate to an applicant with journey records.
- Verify:
  - Section loads automatically once applicant_data_id is available
  - Reload button works
  - Loading, empty, and error states
  - Timeline order is oldest to newest (ASC as per backend)
- Optional follow-ups:
  - Add pagination controls if data grows large.
  - Date formatting adjustments if required (currently shows raw log_date string).
