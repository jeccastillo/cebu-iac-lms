# Shifting Page Implementation TODO

task_progress Items:
- [x] Step 1: Register route /registrar/shifting in frontend/unity-spa/core/routes.js
- [x] Step 2: Create ShiftingController at frontend/unity-spa/features/registrar/shifting/shifting.controller.js
- [x] Step 3: Create template at frontend/unity-spa/features/registrar/shifting/shifting.html
- [x] Step 4: Wire script include in frontend/unity-spa/index.html
- [x] Step 5: Add sidebar menu item in frontend/unity-spa/shared/components/sidebar/sidebar.controller.js
- [ ] Step 6: Smoke test navigation and save flow (student/term/program/curriculum)
- [x] Step 7: Update task_progress checkboxes

Details:
- Implement dedicated "Shifting" page for Registrar to change registration.current_program and registration.current_curriculum for a selected student and term.
- Curricula dropdown must filter by selected Program (tb_mas_curriculum.intProgramID = selected program id).
- Use existing services: ProgramsService, CurriculaService, UnityService, StudentsService, TermService.

Files:
- New:
  - frontend/unity-spa/features/registrar/shifting/shifting.controller.js
  - frontend/unity-spa/features/registrar/shifting/shifting.html
- Modified:
  - frontend/unity-spa/core/routes.js
  - frontend/unity-spa/index.html
  - frontend/unity-spa/shared/components/sidebar/sidebar.controller.js

Backend:
- No backend changes required; use:
  - GET /api/v1/curriculum?program_id=...
  - GET /api/v1/programs
  - GET /api/v1/unity/registration
  - PUT /api/v1/unity/registration

Validation:
- Require student + term + program + curriculum before enabling Save.
