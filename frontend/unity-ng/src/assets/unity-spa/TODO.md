# Unity SPA: Unified Header and View Migration TODO

Scope approved: proceed with unified header improvements and migration placeholders for Registrar, Finance, Scholarship.

Tasks
- [x] Header: switch brand link to SPA dashboard; add internal SPA navigation; ensure icons render
- [x] LinkService: add SPA link helpers; extend CI link set used by placeholders
- [x] Routes: register registrar/reports, finance/ledger, scholarship/students
- [x] Registrar placeholder: controller + template linking to CI registrar pages
- [x] Finance placeholder: controller + template linking to CI ledger page
- [x] Scholarship placeholder: controller + template linking to CI students-with-scholarships page

Details

1) Header improvements
- Change brand link from CI facultyDashboard to SPA #!/dashboard
- Add nav items (visible when logged-in):
  - Dashboard (#/dashboard)
  - My Classes (#/faculty/classes)
  - My Profile (#/faculty/profile)
- Keep Employee Portal button and user dropdown
- Add Font Awesome CDN in index.html (v5) so `fas` icons render

2) LinkService enhancements
- Add buildSpaLinks() returning:
  - dashboard: '#!/dashboard'
  - facultyClasses: '#!/faculty/classes'
  - facultyProfile: '#!/faculty/profile'
  - facultySettings: '#!/faculty/settings'
  - login: '#!/login'
- Extend buildLinks() (CI targets) with:
  - registrarReports: {root}/registrar/registrar_reports
  - financeStudentLedger: {root}/finance/view_all_students_ledger
  - scholarshipStudents: {root}/scholarship/scholarship_view

3) New routes
- /registrar/reports -> features/registrar/reports.html + ReportsController
- /finance/ledger -> features/finance/ledger.html + FinanceLedgerController
- /scholarship/students -> features/scholarship/students.html + ScholarshipStudentsController

4) Placeholders (Phase 1)
- Each page shows title, description, and CTA button linking to corresponding CI page via LinkService
- Use Tailwind, check login state via existing route guard

Verification
- Load frontend/unity-spa/index.html
- Confirm header logo and nav route within SPA; FA icons render
- Navigate to new pages and verify CI buttons open correct legacy views

Update Log
- [x] Created TODO and started implementation
- [x] Implemented header improvements (brand to #!/dashboard, SPA nav items, FA icons)
- [x] Extended LinkService with buildSpaLinks() and CI link additions
- [x] Registered routes for /registrar/reports, /finance/ledger, /scholarship/students
- [x] Added Registrar Reports placeholder (controller + template)
- [x] Added Finance Ledger placeholder (controller + template)
- [x] Added Scholarship Students placeholder (controller + template)

2) Students listing migration (CI -> Angular)
- [x] Laravel API: add GET /api/v1/students with filters (program, year_level, gender, graduated, inactive, registered+sem) and pagination
- [x] Laravel routes: register GET /students in routes/api.php
- [x] SPA route: register /students -> features/students/students.html + StudentsController
- [x] SPA controller: fetch programs, call API with filters, handle pagination, render actions
- [x] SPA template: Advanced Search UI + results table (id, student number, names, program, year level, status, actions)
- [ ] Optional: Add header nav link to #!/students
- [ ] Optional: Implement academicStatus and level parity mapping
- [ ] Optional: Add protected delete action parity

Verification
- Ensure Laravel API is served at {baseRoot}/laravel-api/public/api/v1 (index.html sets API_BASE accordingly)
- Open frontend/unity-spa/index.html
- Navigate to #!/students
- Use filters and paging; verify actions point to legacy CI pages (edit_student, registration_viewer, manualPay)

5) Sidebar integration
- [x] Load sidebar and term-selector scripts in index.html
- [x] Insert <app-sidebar> element after header
- [x] Add "main-content" class to primary content wrapper for offset CSS
- [ ] Optional: Mobile toggle from header to add/remove "mobile-open" class on .app-sidebar

Verification
- Log in and navigate across routes; sidebar shows when logged-in and hides on /login
- Collapse state persists across reloads (StorageService key: sidebarCollapsed)
- Term selector renders and broadcasts termChanged via TermService
- On small screens, main content has no left margin

Update Log
- [x] Sidebar component integrated and mounted; term-selector scripts loaded; main-content offset added
