# TODO: Add Download Template for Applicants

## Tasks
- [x] Add download button to frontend/unity-spa/features/admissions/applicants/list.html
- [x] Add API route for applicants template download in laravel-api/routes/api.php
- [x] Implement template method in ApplicantController to generate Excel file
- [x] Test the download functionality

## Summary
Successfully implemented the "Download Template" feature for the applicants list page. The feature allows users to download a CSV template containing all the fields from the application form, which can be used for bulk import of applicant data.

### Changes Made:
1. **Frontend (HTML)**: Added a "Download Template" button in the filters section of the applicants list page, styled consistently with the students page.
2. **Frontend (Controller)**: Added `downloadTemplate` method to handle the download process, creating a blob from the API response and triggering a file download.
3. **Frontend (Service)**: Added `downloadTemplate` method to make the API call to the template endpoint.
4. **Backend (Routes)**: Added the GET `/api/v1/applicants/template` route with proper middleware for admissions and admin roles.
5. **Backend (Controller)**: Implemented the `template` method in ApplicantController to generate a CSV file with headers based on the application form fields and a sample row.
6. **Backend (Export)**: Created ApplicantImportTemplateExport class (though ultimately used CSV generation in controller for simplicity).

### Template Fields:
The CSV template includes columns for all major sections of the application form:
- Basic Information (name, gender, DOB, campus, etc.)
- Program details
- Contact information
- Parent/Guardian details
- Educational background
- Address
- Health information
- Awareness/awareness sources
- Privacy policy agreement

The implementation follows the same pattern as other template downloads in the system and includes proper error handling and user feedback.
