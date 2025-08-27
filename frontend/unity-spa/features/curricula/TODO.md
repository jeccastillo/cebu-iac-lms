# Curricula Module TODO

Scope: Track implementation progress for implementation_plan_curriculum.md

Status Legend:
- [x] Completed
- [ ] Pending

Backend
- [x] Validation: CurriculumUpsertRequest requires campus_id on POST, sometimes on update; defaults active=1, isEnhanced=0 on create
- [x] Logging: CurriculumController logs create, update, delete, addSubject, removeSubject via SystemLogService
- [x] Routes: API routes wired with role:registrar,admin for write endpoints

Frontend (Routing & Base Screens)
- [x] Routes: Added /curricula, /curricula/add, /curricula/:id/edit with requiredRoles ['registrar','admin']
- [x] List screen: features/curricula/list.html + controller for listing/search/delete
- [x] Edit screen: features/curricula/edit.html + controller for create/update with campus default from CampusService
- [x] Service base: CurriculaService with list/get/create/update/remove, plus getPrograms/getCampuses

Frontend (Subjects Management)
- [ ] Service: Add subjects(), addSubject(), removeSubject() methods in CurriculaService
- [ ] Controller: In CurriculumEditController, load subjects for existing curriculum, and expose add/remove subject actions
- [ ] View: In edit.html, add a minimal Subjects section:
  - [ ] Display subjects table (code, description, year level, sem) with remove action
  - [ ] Add-subject mini-form (intSubjectID, intYearLevel, intSem) and Add button

List View Enhancement (Optional)
- [ ] Show Campus column/badge in list.html to make campus context visible

Testing & Verification
- [ ] (Optional) Smoke script: laravel-api/tests/scripts/curriculum-smoke.ps1 to validate index/create/show/update/subjects/add/remove/delete
- [ ] Manual UI check for subjects add/remove and campus defaulting

Documentation
- [ ] Update implementation_plan_curriculum.md with a Status Update section reflecting completed items and remaining tasks
