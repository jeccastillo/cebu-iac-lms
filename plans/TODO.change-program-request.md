# TODO: Student - Request Program Change

Goal: 
- On the Request Program Change page:
  - Only include programs matching the student's level (College vs SHS).
  - Show the currently selected program on the page.
  - Exclude the currently selected program from the dropdown options.

Files:
- frontend/unity-spa/features/student/change-program-request/change-program-request.controller.js
- frontend/unity-spa/features/student/change-program-request/change-program-request.html

Tasks:
- [x] Controller: Add state for current program and student level
  - [x] vm.currentProgramId, vm.currentProgramCode, vm.currentProgramName
  - [x] vm.studentLevelNormalized ('college' | 'shs')
- [x] Controller: Implement loadStudentDetails()
  - [x] Use StudentFinancesService.resolveProfile() -> student_id
  - [x] GET /api/v1/students/{id} to populate current program and level
  - [x] Normalize level to 'college' | 'shs'
- [x] Controller: Filter programs by level and exclude current
  - [x] Use ProgramsService.list({ enabledOnly: false, type: vm.studentLevelNormalized })
  - [x] Map to { id, code, name } and filter out x.id === vm.currentProgramId
  - [x] Fallback to all programs if student_id/level unavailable
- [x] Template: Display Current Program and Student Level
  - [x] Add contextual card showing vm.currentProgramLabel()
  - [x] Show "Student Level" text using vm.studentLevelNormalized (uppercase)
- [x] Guards: Ensure submitting same program is prevented (already present; keep)
- [ ] Manual verification:
  - [ ] Current Program is visible
  - [ ] Dropdown lists only same-level programs
  - [ ] Current program is not in the options
  - [ ] Submission guard works

Notes:
- ProgramsService.list supports &#39;type&#39;: &#39;college&#39; | &#39;shs&#39;; reuse this to restrict options.
- StudentController@show (GET /api/v1/students/{id}) returns program_id, program (code), program_description, student_level; use this for display and filtering.
- Graceful fallback when student_id not resolved or API fails: keep list as-is but still exclude current program if known.
