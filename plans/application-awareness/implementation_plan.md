# Implementation Plan

[Overview]
Add a multi-select "How did you find out about iACADEMY?" section to the Admissions Application form and persist each selected awareness item into a new relational table tb_mas_application_awareness linked to a specific application (tb_mas_applicant_data), while also embedding the submitted awareness info inside the applicant_data JSON for full context.

This change mirrors the Makati site behavior where applicants can select multiple awareness sources and optionally supply details for Event, Referral, and Others. The back end will store each selected source as its own normalized row, enabling clear analytics and filtering by awareness channels. The application flow will remain unchanged: the AdmissionsController@store endpoint continues to create tb_mas_users and tb_mas_applicant_data rows; immediately after the applicant_data row is created, related awareness rows are inserted. The UI enhancement is scoped to the public application page at #/admissions/apply.

[Types]  
Introduce structured types for awareness payload and DB schema to support multi-select inputs with optional metadata.

Detailed type definitions:
- Frontend payload (AngularJS, sent to API as JSON):
  - AwarenessPayload (array of AwarenessItem)
    - AwarenessItem
      - name: string (required; one of: "Google", "Facebook", "Instagram", "Tiktok", "News", "School Fair/Orientation", "Billboard", "Event", "Referral", "Others")
      - sub_name: string | null (optional; for "Event" name, or "Others" specify)
      - referral: boolean (default false; true if name === "Referral")
      - name_of_referee: string | null (optional; provided only when referral === true)
- Backend DB schema (MySQL):
  - tb_mas_application_awareness
    - id: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
    - applicant_data_id: INT UNSIGNED NOT NULL (FK → tb_mas_applicant_data.id)
    - name: VARCHAR(100) NOT NULL
    - sub_name: VARCHAR(255) NULL
    - referral: TINYINT(1) NOT NULL DEFAULT 0
    - name_of_referee: VARCHAR(255) NULL
    - created_at: TIMESTAMP NULL
    - updated_at: TIMESTAMP NULL
    - INDEX: idx_app_awareness_applicant_data_id (applicant_data_id)
    - FOREIGN KEY (best-effort, guarded for legacy envs): fk_app_awareness_applicant_data_id → tb_mas_applicant_data(id) ON DELETE CASCADE

Validation rules and relationships:
- Frontend:
  - Allow multiple selection.
  - Conditionally show text inputs:
    - Event (please specify) → sets sub_name
    - Referral (please specify) → sets referral = true and name_of_referee
    - Others (please specify) → sets sub_name
- Backend:
  - Require at least one AwarenessItem to be valid only if the section is used (no hard-required; empty awareness array allowed).
  - For AwarenessItem named "Referral", referral flag will be coerced to true regardless of submitted value.
  - Each AwarenessItem row persists separately with applicant_data_id referencing the just-created tb_mas_applicant_data row.

[Files]
Add a migration, model, and service wiring, plus frontend form and controller changes to collect and submit awareness data.

Detailed breakdown:
- New files to be created:
  - laravel-api/database/migrations/2025_09_XX_XXXXXX_create_tb_mas_application_awareness.php
    - Purpose: Create tb_mas_application_awareness with the fields and guarded foreign key described above.
  - laravel-api/app/Models/ApplicationAwareness.php
    - Purpose: Eloquent model for tb_mas_application_awareness with fillable fields and relationship to ApplicantData (via applicant_data_id).
  - laravel-api/app/Services/ApplicationAwarenessService.php
    - Purpose: Encapsulate creation of multiple awareness rows from a normalized awareness payload array. Accepts applicant_data_id and array<AwarenessItem>.

- Existing files to be modified:
  - frontend/unity-spa/features/admissions/apply.html
    - Add a new fieldset "HOW DID YOU FIND OUT ABOUT iACADEMY?" with multiple checkboxes:
      - Google, Facebook, Instagram, Tiktok, News, School Fair/Orientation, Billboard, Event (please specify), Referral (please specify), Others (please specify).
    - For Event/Referral/Others options, render conditional text inputs.
  - frontend/unity-spa/features/admissions/apply.controller.js
    - Extend vm.form with awareness-related state (boolean flags and text fields).
    - In submit(), transform awareness flags into payload.awareness: AwarenessItem[].
    - Include payload.awareness in the POST to /api/v1/admissions/student-info.
  - laravel-api/app/Http/Controllers/Api/V1/AdmissionsController.php
    - After inserting tb_mas_applicant_data (applicantDataId), parse $request->input('awareness') as array and insert rows using ApplicationAwarenessService. Also keep awareness data within the applicant_data JSON (already persisted as request payload).
    - Handle empty/missing awareness gracefully (no-op).

- Files to be deleted or moved
  - None.

- Configuration file updates
  - None.

[Functions]
Add normalized creation and mapping functions for awareness, and modify AdmissionsController@store to call them.

Detailed breakdown:
- New functions
  - ApplicationAwarenessService::createMany(int $applicantDataId, array $items): array
    - File: laravel-api/app/Services/ApplicationAwarenessService.php
    - Purpose: Validate/normalize each AwarenessItem and insert rows; returns inserted IDs.
    - Signature: public function createMany(int $applicantDataId, array $items): array
  - ApplicationAwareness::applicantData() relationship
    - File: laravel-api/app/Models/ApplicationAwareness.php
    - Purpose: belongsTo relation to tb_mas_applicant_data.

- Modified functions
  - AdmissionsController::store(Request $request)
    - File: laravel-api/app/Http/Controllers/Api/V1/AdmissionsController.php
    - Required changes:
      - After $applicantDataId is known, extract $request->input('awareness', []).
      - Validate as array of objects with keys: name (string), sub_name (nullable string), referral (bool), name_of_referee (nullable string).
      - Coerce 'Referral' items to referral = true.
      - Call ApplicationAwarenessService::createMany($applicantDataId, $awareness).
      - Keep existing transactional behavior; awareness inserts should be inside the same transaction block. Failures should rollback to keep data consistent.

- Removed functions
  - None.

[Classes]
Add an Eloquent model for awareness and a service to encapsulate logic.

Detailed breakdown:
- New classes
  - App\Models\ApplicationAwareness
    - File: laravel-api/app/Models/ApplicationAwareness.php
    - Table: tb_mas_application_awareness
    - Fillable: ['applicant_data_id', 'name', 'sub_name', 'referral', 'name_of_referee']
    - Relationships: belongsTo('tb_mas_applicant_data', 'applicant_data_id')
  - App\Services\ApplicationAwarenessService
    - File: laravel-api/app/Services/ApplicationAwarenessService.php
    - Methods: createMany(int $applicantDataId, array $items): array
    - Responsibilities: Validate and insert multiple rows.

- Modified classes
  - App\Http\Controllers\Api\V1\AdmissionsController
    - Add awareness persistence after applicant_data insert.

- Removed classes
  - None.

[Dependencies]
No new external dependencies; use existing Laravel components.

Details:
- Utilize Laravel Schema, Eloquent, and DB transactions (already in use by AdmissionsController@store).
- No composer.json changes required.

[Testing]
Add API-level tests and manual UI verification to ensure data flow end-to-end.

Test plan:
- Backend (Feature tests - optional scope for now):
  - POST /api/v1/admissions/student-info with:
    - awareness: [
        {"name":"Google"},
        {"name":"Event","sub_name":"Campus Roadshow"},
        {"name":"Referral","referral":true,"name_of_referee":"Jane Smith"},
        {"name":"Others","sub_name":"Walk-in at building lobby"}
      ]
  - Assert: 
    - tb_mas_applicant_data row created.
    - N awareness rows created with correct applicant_data_id and values.
    - applicant_data JSON includes the awareness array.
- Frontend manual:
  - Navigate to #/admissions/apply, select multiple awareness options, fill conditional inputs.
  - Submit and verify success flow remains intact.
  - Confirm payload in network tab contains awareness array built as per spec.

[Implementation Order]
Proceed from schema to backend service/controller to frontend UI, minimizing integration friction.

Numbered steps:
1. Add migration: create tb_mas_application_awareness with fields, indexes, and guarded FK to tb_mas_applicant_data.id.
2. Add Eloquent model App\Models\ApplicationAwareness with fillable fields and relationship.
3. Add App\Services\ApplicationAwarenessService with createMany(applicant_data_id, items) method.
4. Modify AdmissionsController@store:
   - Extract $awareness from request; validate/normalize.
   - After $applicantDataId is created, call ApplicationAwarenessService->createMany(...).
   - Keep awareness within applicant_data JSON as part of request payload (already stored).
5. Frontend: admissions/apply.html
   - Add "HOW DID YOU FIND OUT ABOUT iACADEMY?" fieldset with checkboxes:
     - Google, Facebook, Instagram, Tiktok, News
     - School Fair/Orientation
     - Billboard
     - Event (please specify)
     - Referral (please specify)
     - Others (please specify)
   - Add conditional inputs for Event/Referral/Others.
6. Frontend: admissions/apply.controller.js
   - Extend vm.form with awareness flags and specify fields.
   - Transform selected flags into payload.awareness array in submit().
   - Post to the same endpoint.
7. Manual verification in the browser.
8. Optional: Add a basic Feature test covering awareness persistence.
