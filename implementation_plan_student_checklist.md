# Implementation Plan

[Overview]
Add a Student Graduation Checklist feature using the current curriculum’s subjects. The API will generate a checklist for a student by copying subjects from the student’s curriculum, and mark subjects as passed if already completed. Users can add/remove items from the checklist and view progress to determine graduation eligibility. Identification per the requirement is stored at the checklist level using YearLevel (int) and sem (string: 1st, 2nd, 3rd). Backend is Laravel API; frontend is the Angular (unity-spa) Student Viewer.

Scope:
- Database schema (via Laravel migrations) to store student checklists and items.
- Models, service logic to generate checklist from curriculum, status propagation, and summary computation.
- API endpoints (Laravel) to manage checklist lifecycle and items.
- Angular integration in Student Viewer UI to display, generate, add/remove and show summary.

[Types]
Type system additions (Laravel PHP, AngularJS).

Backend (Laravel/PHP)
- App\Models\StudentChecklist (maps to table tb_student_checklists)
  - intID: int PK
  - intStudentID: int (FK to tb_mas_users.intID) [per confirmation #1]
  - intCurriculumID: int (FK to tb_mas_curriculum.intID) [per confirmation #5]
  - intYearLevel: int (YearLevel values: 1,2,3,4) [per confirmation #6]
  - strSem: varchar (values: '1st', '2nd', '3rd') [per confirmation #6]
  - remarks: string|null
  - created_by: int|null
  - created_at: datetime
  - updated_at: datetime

- App\Models\StudentChecklistItem (maps to tb_student_checklist_items)
  - intID: int PK
  - intChecklistID: int (FK to tb_student_checklists.intID)
  - intSubjectID: int (FK to tb_mas_subjects.intID)
  - strStatus: varchar (enum set by validation: 'planned'|'in-progress'|'passed'|'failed'|'waived') [per confirmation #3]
  - dteCompleted: date|null
  - isRequired: tinyint(1) default 1
  - created_at: datetime
  - updated_at: datetime

Notes:
- Per confirmation #6, Year/Sem identifiers are stored at the checklist-level only (not per-item). No per-item term is stored.
- We will use guarded = [] and standard intID primary keys to align with existing models.

Frontend (AngularJS)
- Checklist (JS):
  - id, student_id, curriculum_id
  - year_level (int), sem (string), remarks
  - items: ChecklistItem[]
  - summary: { total, required, completed, remaining, percent }
- ChecklistItem (JS):
  - id, subject_id, subject_code, subject_desc, units
  - status ('planned'|'in-progress'|'passed'|'failed'|'waived')
  - completed_date

[Files]
New files (Laravel API)
- app/Models/StudentChecklist.php
- app/Models/StudentChecklistItem.php
- app/Services/StudentChecklistService.php
- app/Http/Controllers/Api/V1/StudentChecklistController.php
- app/Http/Requests/Api/V1/ChecklistGenerateRequest.php
- app/Http/Requests/Api/V1/ChecklistItemStoreRequest.php
- app/Http/Requests/Api/V1/ChecklistItemUpdateRequest.php
- app/Http/Resources/StudentChecklistResource.php
- app/Http/Resources/StudentChecklistItemResource.php
- database/migrations/XXXX_XX_XX_000000_create_student_checklists_table.php
- database/migrations/XXXX_XX_XX_000001_create_student_checklist_items_table.php

Existing files to be modified (Laravel API)
- routes/api.php: register Api/V1 student checklist endpoints
- Optionally add relationships on Curriculum and CurriculumSubject (non-breaking)

New files (Angular - unity-spa)
- frontend/unity-spa/features/students/checklist.service.js (API integration)
- Update frontend/unity-spa/features/students/viewer.controller.js (inject ChecklistService; add generate/view actions)
- Update frontend/unity-spa/features/students/viewer.html (add new “Checklist” section UI)

Files to delete/move
- None.

Configuration updates
- None required beyond migrations.

[Functions]
Backend (new)
- StudentChecklistService
  - generateFromCurriculum(intStudentID, intCurriculumID, intYearLevel, string sem): StudentChecklist
    - Loads curriculum subjects (tb_mas_curriculum_subject) by intCurriculumID
    - Creates tb_student_checklists row for (student,curriculum,year,sem)
    - Creates StudentChecklistItem rows for each subject (isRequired=1 by default; status='planned')
    - Mark subjects as 'passed' where applicable using student records (see computePassedMap below)
  - computeSummary(StudentChecklist $checklist): array
    - Summarize: total items, required count, completed passed/waived count, remaining, percent completed
  - computePassedMap(intStudentID): array<intSubjectID => bool>
    - Uses existing student records API/data to determine passed subjects (best-effort as available)
- StudentChecklistController
  - GET /api/v1/students/{student}/checklist
    - Student is intStudentID [confirmed]
    - Returns latest checklist by created_at or with optional query filter year & sem
  - POST /api/v1/students/{student}/checklist/generate
    - ChecklistGenerateRequest:
      - intCurriculumID: required|integer
      - intYearLevel: required|integer|min:1|max:10
      - strSem: required|string|in:1st,2nd,3rd
    - Compose with StudentChecklistService::generateFromCurriculum()
    - Behavior [#4]: Include all from curriculum but mark passed ones as 'passed'
  - POST /api/v1/students/{student}/checklist/items
    - ChecklistItemStoreRequest:
      - intChecklistID: required|integer
      - intSubjectID: required|integer
      - isRequired: sometimes|boolean
      - strStatus: sometimes|in:planned,in-progress,passed,failed,waived
      - dteCompleted: sometimes|date
  - PUT /api/v1/students/{student}/checklist/items/{item}
    - ChecklistItemUpdateRequest:
      - strStatus, isRequired, dteCompleted (validations as above)
  - DELETE /api/v1/students/{student}/checklist/items/{item}
  - GET /api/v1/students/{student}/checklist/summary
    - Returns summary from StudentChecklistService::computeSummary()

Frontend (new/modified)
- ChecklistService (Angular)
  - get(studentId, {year, sem}?) -> GET /students/{id}/checklist
  - generate(studentId, payload { curriculum_id, year_level, sem }) -> POST /students/{id}/checklist/generate
  - addItem(studentId, payload) -> POST /students/{id}/checklist/items
  - updateItem(studentId, itemId, payload) -> PUT
  - deleteItem(studentId, itemId) -> DELETE
  - summary(studentId, {year, sem}?)
- StudentViewerController.js
  - vm.fetchChecklist()
  - vm.generateChecklist({ curriculum_id, year_level, sem })
  - vm.addChecklistItem(subjectId)
  - vm.removeChecklistItem(item)
  - vm.updateChecklistItem(item, fields)
  - vm.checklistSummary (display progress/eligibility)

[Classes]
New classes (Laravel)
- App\Models\StudentChecklist
  - $table = 'tb_student_checklists'
  - $primaryKey = 'intID'
  - guarded = []
  - relations: items(): hasMany(StudentChecklistItem, 'intChecklistID', 'intID')
- App\Models\StudentChecklistItem
  - $table = 'tb_student_checklist_items'
  - $primaryKey = 'intID'
  - guarded = []
  - relations: checklist(): belongsTo(StudentChecklist, 'intChecklistID', 'intID'); subject(): belongsTo(Subject, 'intSubjectID', 'intID')

Modified classes (optional)
- Curriculum: hasMany to CurriculumSubject
- CurriculumSubject: belongsTo Subject

[Dependencies]
- No new composer deps.
- AngularJS integration with existing framework.

[Testing]
Backend
- Migrations run cleanly; tables created.
- Generate checklist:
  - Creates tb_student_checklists row for provided (student,curriculum,year,sem)
  - Inserts items for each curriculum subject
  - Marks passed subjects as 'passed' (if records available)
- Item CRUD:
  - Add/Update/Delete lifecycle OK.
- Summary endpoint returns counts consistent with items.
- Logging: If desired, add SystemLogService::log on generate/add/remove (consistent with patterns in implementation_plan_curriculum.md).

Frontend
- On Student Viewer page (using vm.id as intStudentID), user can:
  - Generate checklist (inputs for curriculum, year, sem)
  - View items table (subject code, description, units, status)
  - Add/remove/update items
  - See summary/progress display for eligibility assessment

[Implementation Order]
1) Database schema (migrations)
   - tb_student_checklists: intID, intStudentID, intCurriculumID, intYearLevel, strSem, remarks, timestamps
   - tb_student_checklist_items: intID, intChecklistID, intSubjectID, strStatus, dteCompleted, isRequired, timestamps
2) Models
   - StudentChecklist, StudentChecklistItem
3) Service
   - StudentChecklistService::generateFromCurriculum(), ::computeSummary(), ::computePassedMap()
4) Requests & Resources
   - ChecklistGenerateRequest, ChecklistItemStoreRequest, ChecklistItemUpdateRequest
   - StudentChecklistResource (includes items + summary), StudentChecklistItemResource
5) Controller & Routes
   - StudentChecklistController with endpoints: index, generate, addItem, updateItem, deleteItem, summary
   - routes/api.php registration under Api/V1
6) Frontend
   - checklist.service.js
   - viewer.controller.js: add inject/use ChecklistService; vm.fetchChecklist(), vm.generateChecklist(), etc.; use vm.id (intStudentID)
   - viewer.html: create “Checklist” card; controls (generate form), items table, status select, remove button, summary ribbon
7) QA & polish
   - Ensure consistency with existing API patterns (success/data wrapper)
   - Resolve curriculum id from tb_mas_users.intCurriculumID if not provided by UI (controller can default from DB)
   - Verify permission checks if applicable

[Notes from Confirmations]
1) Student identifier: Use intStudentID (we will use vm.id from route and backend params named {student} resolving to intStudentID)
2) Table names & keys: Approved
3) Status values: Approved
4) Generate behavior: Include all subjects from curriculum; mark passed ones as 'passed'
5) Curriculum source: Use tb_mas_users.intCurriculumID (fallback in controller if UI does not pass curriculum_id)
6) Year/Sem vs Term: Store YearLevel (intYearLevel) and sem (strSem as '1st','2nd','3rd') on tb_student_checklists only
7) Schema approach: Laravel migrations
