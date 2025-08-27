# Student Graduation Checklist - TODO

Scope: Implement backend (Laravel API) + frontend (AngularJS) for student graduation checklist.

Legend:
- [ ] Pending
- [x] Done

## 1) Database (Laravel migrations)
- [x] Create migration: tb_student_checklists
  - intID (PK, increments)
  - intStudentID (int, index)
  - intCurriculumID (int, index)
  - intYearLevel (int)
  - strSem (string: '1st'|'2nd'|'3rd')
  - remarks (string, nullable)
  - created_by (int, nullable, index)
  - timestamps
- [x] Create migration: tb_student_checklist_items
  - intID (PK, increments)
  - intChecklistID (int, index)
  - intSubjectID (int, index)
  - strStatus (string: 'planned'|'in-progress'|'passed'|'failed'|'waived')
  - dteCompleted (date, nullable)
  - isRequired (boolean, default true)
  - timestamps

## 2) Models (Eloquent)
- [x] app/Models/StudentChecklist.php
  - $table = 'tb_student_checklists'
  - $primaryKey = 'intID'
  - guarded = []
  - items(): hasMany(StudentChecklistItem, 'intChecklistID', 'intID')
- [x] app/Models/StudentChecklistItem.php
  - $table = 'tb_student_checklist_items'
  - $primaryKey = 'intID'
  - guarded = []
  - checklist(): belongsTo(StudentChecklist, 'intChecklistID', 'intID')
  - subject(): belongsTo(Subject, 'intSubjectID', 'intID')

## 3) Service
- [x] app/Services/StudentChecklistService.php
  - generateFromCurriculum(intStudentID, intCurriculumID, intYearLevel, string $strSem): StudentChecklist
    - Read tb_mas_curriculum_subject
    - Create checklist & items
    - Include all subjects, but mark passed via computePassedMap
  - computePassedMap(intStudentID): array<intSubjectID,bool>
  - computeSummary(StudentChecklist $checklist): array { total, required, completed, remaining, percent }

## 4) Requests & Resources
- [x] app/Http/Requests/Api/V1/ChecklistGenerateRequest.php (intCurriculumID made optional to allow fallback)
- [x] app/Http/Requests/Api/V1/ChecklistItemStoreRequest.php
- [x] app/Http/Requests/Api/V1/ChecklistItemUpdateRequest.php
- [x] app/Http/Resources/StudentChecklistResource.php (includes items + summary)
- [x] app/Http/Resources/StudentChecklistItemResource.php

## 5) Controller & Routes
- [x] app/Http/Controllers/Api/V1/StudentChecklistController.php
  - GET /api/v1/students/{student}/checklist
  - POST /api/v1/students/{student}/checklist/generate
  - POST /api/v1/students/{student}/checklist/items
  - PUT /api/v1/students/{student}/checklist/items/{item}
  - DELETE /api/v1/students/{student}/checklist/items/{item}
  - GET /api/v1/students/{student}/checklist/summary
- [x] Update routes/api.php to register routes under Api\V1 namespace

## 6) Frontend (AngularJS)
- [x] Create frontend/unity-spa/features/students/checklist.service.js
  - get(studentId, {year, sem}?)
  - generate(studentId, { curriculum_id, year_level, sem })
  - addItem(studentId, payload)
  - updateItem(studentId, itemId, payload)
  - deleteItem(studentId, itemId)
  - summary(studentId, {year, sem}?)
- [x] Update frontend/unity-spa/features/students/viewer.controller.js
  - Inject ChecklistService
  - vm.fetchChecklist(), vm.generateChecklist(), vm.addChecklistItem(), vm.removeChecklistItem(item), vm.updateChecklistItem(item)
  - vm.checklist, vm.checklistSummary
- [x] Update frontend/unity-spa/features/students/viewer.html
  - Add "Checklist" card with:
    - Generate form (Curriculum defaults from tb_mas_users.intCurriculumID; YearLevel, Sem)
    - Items table (code, description, units, status, completed date)
    - Add/Remove / Update controls
    - Summary / progress ribbon

## 7) Logging (optional but recommended)
- [ ] generate (intentionally skipped per instruction)
- [x] add item
- [x] update item
- [x] delete item

## 8) QA
- [x] Run migrations and verify tables
- [x] Manual API tests (generate, add/update/delete, summary)
- [ ] Manual UI tests in Student Viewer
- [ ] Edge cases: student without curriculum, duplicate generate handling, empty curriculum subjects
