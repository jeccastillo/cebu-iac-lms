# Implementation Plan

[Overview]
Create an internal, wiki-style documentation hub categorized by Registrar, Admissions, Scholarship, Finance, and Academics, with the first full article focused on Registrar → Enlistment. The docs will be authored in Markdown under plans/wiki/ and linked in the SPA via a new Help/Docs route. This enables role-gated, version-controlled guidance for staff directly within the system’s UI.

The documentation hub will live under plans/wiki/ so it is versioned with code and easy to maintain. The SPA will gain a Help/Docs page that renders Markdown safely (sanitized HTML) and supports direct links for specific categories/pages. The initial content will include a comprehensive Registrar Enlistment guide leveraging the existing UI (features/registrar/enlistment) and back-end behavior, plus stubs for the other categories. Access is internal-only (registrar, admissions, scholarship, finance, academics, admin).

[Types]  
Introduce lightweight front-end types for organizing and gating documentation pages.

Detailed type/interface specifications:
- DocCategory (JS object)
  - key: string
    - One of: registrar | admissions | scholarships | finance | academics
  - label: string
    - Display name for the category (e.g., Registrar)
  - pages: DocPage[]
    - List of pages belonging to the category

- DocPage (JS object)
  - key: string
    - Short slug (e.g., enlistment)
  - label: string
    - Display label (e.g., Enlistment)
  - path: string
    - MD path relative to the web root via baseRoot (e.g., {baseRoot}/plans/wiki/registrar/enlistment.md)
  - requiredRoles: string[]
    - Roles allowed to view this page (subset of the category’s allowed roles)

- RenderPayload (internal)
  - markdown: string
    - Raw MD fetched via $http
  - html: string
    - HTML rendered from markdown (via marked), before sanitization
  - safeHtml: string
    - Sanitized HTML (via DOMPurify) for final injection

Validation and relationships:
- The DocsController will validate that selected category and page exist and that the current user’s roles (from RoleService or current session) intersect the allowed roles.
- The SPA routes will be configured to accept /docs and /docs/:category/:page? for deep linking.
- Page path resolution must factor baseRoot (frontend/unity-spa/core/baseRoot.js) to correctly fetch /plans/wiki/... even though the SPA is served from /frontend/unity-spa/.

[Files]
Create a docs tree in plans/wiki and add a minimal SPA feature to browse and render the Markdown docs.

Detailed breakdown:
- New files to be created (Markdown docs):
  - plans/wiki/README.md
    - Documentation hub overview; conventions (style, screenshots, versioning).
  - plans/wiki/registrar/index.md
    - Registrar docs overview and navigation.
  - plans/wiki/registrar/enlistment.md
    - Full detailed guide for Registrar Enlistment (first complete article).
  - plans/wiki/admissions/index.md
    - Stub with outline of intended content structure.
  - plans/wiki/scholarships/index.md
    - Stub.
  - plans/wiki/finance/index.md
    - Stub.
  - plans/wiki/academics/index.md
    - Stub.

- New SPA feature (front-end):
  - frontend/unity-spa/features/help/docs/docs.controller.js
    - AngularJS controller: route params → select page → fetch MD → render → sanitize → display.
  - frontend/unity-spa/features/help/docs/docs.service.js
    - AngularJS service: defines categories/pages config; fetches markdown; renders with marked; sanitizes with DOMPurify.
  - frontend/unity-spa/features/help/docs/docs.html
    - AngularJS template with a left sidebar (categories/pages) and content area (rendered safeHtml).
  - assets/lib/marked/marked.min.js
    - Markdown parser (no bundler assumed).
  - assets/lib/dompurify/purify.min.js
    - HTML sanitizer to prevent XSS from MD content.

- Existing files to be modified (specific changes):
  - frontend/unity-spa/core/routes.js
    - Register new routes:
      - /docs → docs.html (category list + index)
      - /docs/:category/:page? → docs.html (renders selected category/page)
    - requiredRoles: internal staff only (['registrar','admissions','scholarship','finance','academics','admin'])
  - frontend/unity-spa/index.html (or a global script include file used by the SPA)
    - Include script tags for assets/lib/marked/marked.min.js and assets/lib/dompurify/purify.min.js before DocsService usage.
  - Global menu/sidebar (where feature menus are added; if a shared header template exists or a menu service)
    - Add a “Help / Docs” menu entry that links to #/docs and is visible to internal roles (as above).

- Files to be deleted or moved:
  - None.

- Configuration file updates:
  - None (no build system or composer updates required).

[Functions]
Add functions in the new Docs feature and minimal changes in route/menu setup.

Detailed breakdown:
- New functions:
  - DocsService.getCategories(): DocCategory[]
    - Returns the static config of categories/pages with their labels, MD paths (prefixed with baseRoot), and requiredRoles.
  - DocsService.resolvePath(categoryKey: string, pageKey?: string): string|null
    - Resolves the Markdown file full URL (using window.API_BASE or baseRoot pattern; for static MD use baseRoot + '/plans/wiki/...').
  - DocsService.fetchMarkdown(path: string): Promise<string>
    - Fetches markdown via $http.get(path, { responseType: 'text' }).
  - DocsService.renderMarkdown(md: string): string
    - Uses window.marked to render; configure options for code block highlighting (optional, not required).
  - DocsService.sanitize(html: string): string
    - Uses window.DOMPurify.sanitize(html, { USE_PROFILES: { html: true } }).
  - DocsController.init()
    - Parses $routeParams; selects the category and default page (index.md or first available); triggers load/render.
  - DocsController.select(categoryKey, pageKey)
    - Updates $location.path('/docs/'+categoryKey+'/'+(pageKey||'')); triggers load/render.
  - DocsController.hasRole(role: string)
    - Returns true if current user has the role; use RoleService or existing context (see _fe_roles.txt and RoleService in core).
  - DocsController.canView(page: DocPage)
    - Ensures intersection between page.requiredRoles and current user roles; hides otherwise.

- Modified functions:
  - Route configuration (frontend/unity-spa/core/routes.js)
    - Add .when('/docs', ...) and .when('/docs/:category/:page?', ...).
  - Menu builder (where defined)
    - Add a Help/Docs link with requiredRoles: ['registrar','admissions','scholarship','finance','academics','admin'].

- Removed functions:
  - None.

[Classes]
AngularJS components only (service + controller) for the docs feature.

Detailed breakdown:
- New classes (AngularJS components):
  - DocsService (factory/service) in frontend/unity-spa/features/help/docs/docs.service.js
  - DocsController (controller) in frontend/unity-spa/features/help/docs/docs.controller.js

- Modified classes:
  - None explicitly; route/menu configuration code paths are functional blocks rather than classes.

- Removed classes:
  - None.

[Dependencies]
Introduce two client-side libraries for MD rendering and sanitation.

- New packages/files:
  - assets/lib/marked/marked.min.js (v4.x)
  - assets/lib/dompurify/purify.min.js (v3.x)
- Integration requirements:
  - Include via script tags in frontend/unity-spa/index.html before Angular app bootstraps or at least before DocsService usage.
  - No NPM or composer changes (static assets are sufficient).

[Testing]
Manual validation and optional unit tests for rendering correctness and role-gated visibility.

- Manual tests:
  - Navigate to #/docs: Docs index loads, categories listed; default landing (either global README.md content or instruction).
  - Navigate to #/docs/registrar/enlistment: Full registrar enlistment guide renders; links and table of contents function.
  - Refresh deep links: page should render correctly via route params.
  - Role gating: Log in as registrar → Docs visible; as finance only → Docs visible; as student-only → Docs hidden (or blocked).
  - Security: MD with embedded HTML is sanitized; no inline scripts or dangerous URLs render.
  - File path validation: Ensure fetching from baseRoot + /plans/wiki/... works under XAMPP path trimming logic (see core/baseRoot.js).

- Optional unit tests (if test harness is present):
  - DocsService.renderMarkdown and sanitize produce expected outputs for sample MD.
  - DocsController route param parsing selects the correct page.

[Implementation Order]
A small, low-risk sequence prioritizing content creation then UI to render it.

1) Content: Create docs tree under plans/wiki/ with the full Registrar → Enlistment article and category stubs.
2) Assets: Add marked.min.js and purify.min.js to assets/lib and include them via index.html.
3) Service: Implement DocsService with categories config, fetch/render/sanitize helpers.
4) Controller+Template: Implement DocsController and docs.html UI.
5) Routing: Register /docs and /docs/:category/:page? in frontend/unity-spa/core/routes.js.
6) Menu: Add “Help / Docs” to the SPA menu visible to internal roles.
7) Manual QA: Verify navigation, deep-links, sanitation, and role gating; adjust paths/roles as needed.
8) Document: Update plans/wiki/README.md with contributor notes (how to add new pages) and finalize.

-----------------------------------------
Registrar → Enlistment: Full Article Content Specification
-----------------------------------------
File: plans/wiki/registrar/enlistment.md

Purpose
- Guide registrars through adding/dropping/changing sections, generating checklists and registration forms, and tuition preview/save in the Enlistment tool.

Audience
- Registrar staff and admins with registrar access.

Pre-requisites
- Logged in with role: registrar or admin.
- Confirm the correct Term is selected from the global term selector (top-level UI; TermService).
- Student exists and can be found by Student Number.

Access
- Route: #/registrar/enlistment
- Required Roles: ['registrar','admin']

Sections (outline and mapping to UI/Controller)
1) Overview
   - Page location and purpose.
   - What operations are supported: Add, Drop, Change Section, Checklist generation, Tuition preview/save, Reg Form generation, Reset Registration.
2) Selecting a Student and Term
   - Student field with autocomplete (vm.studentNumber, StudentsService.listSuggestions, onStudentSelected()).
   - Term display sourced from global selector (vm.selectedTerm, TermService).
   - Year Level and Student Type input selectors.
   - Block Section filter (optional) for queueing.
   - Screenshots placeholders: [Student and Term controls]
3) Current Enlisted (Selected Term)
   - Load current enlisted (loadCurrent → /student/records-by-term).
   - Table columns: Code, Description, Section, Units, Classlist ID.
   - Link to student records viewer (#/students/:id/records?sn=...).
4) Queue Operations
   - Add: choose classlist (vm.selectedAddClasslistId); slot availability shown via remaining_slots; prerequisite checks.
   - Drop: choose current classlist to drop (vm.selectedDropClasslistId).
   - Change Section: from (current) to (target) (vm.changeFromId → vm.changeToId); checks for remaining slots.
   - Auto-Queue from Checklist:
     - Pre-conditions: checklist exists or can be generated.
     - Filtering by Year Level and Sem (vm.clYearLevel, vm.clSemInt) when available.
     - Batch prerequisite checks endpoint: POST /subjects/check-prerequisites-batch.
   - Generate Checklist: when missing (ChecklistService.generate).
   - Pending Operations list with Remove and Clear.
   - Submit: POST UnityService.enlist({ student_number, term, year_level, student_type, operations }).
5) Results and Post-Submit
   - Results summary (success, message).
   - Per-operation results table (type, ok, message, details).
   - Current After Operations table reloaded from server.
6) Registration Details
   - Current values (read-only): Year Level, Student Type, Payment Type, Program, Curriculum, Tuition Year, Withdrawal Period, LOA Remarks.
   - Editable fields on the right (vm.regForm.*), Save Registration (UnityService.updateRegistration), Reset form.
   - Enrollment status and date_enlisted controls Reg Form availability.
7) Registration Form (PDF)
   - Button enabled under enlisted/enrolled status (canGenerateRegForm()).
   - openRegFormPdf() uses UnityService.regFormFetch to fetch Blob and open.
   - Troubleshooting for blocked pop-ups.
8) Tuition Details
   - Load Tuition preview from current enlistment (UnityService.tuitionPreview); requires program id and current enlisted subjects.
   - Dynamic installment plans (vm.installmentPlans / selectedInstallmentPlanId); legacy tabs (standard/dp30/dp50) fallback when no dynamic plans.
   - Summary panels and item tables (Tuition, Misc, Lab, Additional, Scholarships, Discounts).
   - Save Tuition: UnityService.tuitionSave; preflight UnityService.tuitionSaved; overwrite confirmation when snapshot exists.
9) Reset Registration
   - Requires password confirmation (SweetAlert2 prompt).
   - Deletes enlisted classes and registration for the selected student and optional term scope.
   - UnityService.resetRegistration({ student_number, term?, password }) and success counts.
10) Tips and Troubleshooting
   - Term not set → Select term via global term selector.
   - No sections found → Ensure classlists exist for the term; verify Sections &amp; Slots.
   - Prereq checks failing unexpectedly → Retry or contact academics for curriculum setup.
   - Reg Form not available → Ensure status is enlisted/enrolled or date_enlisted present.

Appendices
- Data safety: All actions use API endpoints with audit logging (SystemLogService integration on the back end per TODO-enlistment.md notes).
- Glossary: Enrollment statuses, checklist, classlist, subject code vs section code.

Screenshots
- Add placeholders: /plans/wiki/images/registrar/enlistment/*.png (to be captured and added later).

-----------------------------------------
Category Stubs (index.md) Content Specification
-----------------------------------------
- admissions/index.md
  - Overview of admissions features: Applicants, Requirements, Previous Schools, Applicant Types, Interviews.
  - Quick links to SPA routes (e.g., #/admissions/applicants).
- scholarships/index.md
  - Overview: Students, Scholarships CRUD, Assignments, Scholarship rules overview.
- finance/index.md
  - Overview: Ledger, Payment Descriptions/Modes, Student Billing, OR/Invoice reports, Non-student payments.
- academics/index.md
  - Overview: Faculty Loading, Grading Sheet, Grading Systems; cross-links to registrar views where relevant.
- registrar/index.md
  - Registrar landing: Programs, Subjects, Curricula, Classlists, Schedules, Enlistment, Transcripts; links out.

[Implementation Order]
1. Author Markdown content under plans/wiki/ (Registrar → Enlistment full, other categories stubs).
2. Add marked.min.js and purify.min.js under assets/lib; include via index.html.
3. Implement DocsService (categories/pages config, fetch, render, sanitize).
4. Implement DocsController and docs.html (sidebar/menu + content).
5. Register routes in frontend/unity-spa/core/routes.js:
   - /docs (index)
   - /docs/:category/:page? (deep links)
   - requiredRoles: ['registrar','admissions','scholarship','finance','academics','admin']
6. Add “Help / Docs” entry to the app menu (visible to the roles above).
7. Manual QA with multiple roles; confirm deep links and sanitization.
8. Finalize formatting and publish.

[Testing]
- Manual:
  - Load #/docs, #/docs/registrar/enlistment, test refresh and navigation.
  - Verify role gating by testing with registrar-only, finance-only, admin, and a non-internal role (e.g., student_view) which should not see Docs.
  - Confirm MD renders as expected; code blocks, tables, headings; and no unsafe HTML.
- Optional unit tests (if available):
  - DocsService: renderMarkdown + sanitize for a sample MD.

[Dependencies]
- Add:
  - assets/lib/marked/marked.min.js
  - assets/lib/dompurify/purify.min.js
- No package.json or composer changes.

[Files]
- New files to be created (summary):
  - plans/wiki/README.md
  - plans/wiki/registrar/index.md
  - plans/wiki/registrar/enlistment.md
  - plans/wiki/admissions/index.md
  - plans/wiki/scholarships/index.md
  - plans/wiki/finance/index.md
  - plans/wiki/academics/index.md
  - frontend/unity-spa/features/help/docs/docs.controller.js
  - frontend/unity-spa/features/help/docs/docs.service.js
  - frontend/unity-spa/features/help/docs/docs.html
  - assets/lib/marked/marked.min.js
  - assets/lib/dompurify/purify.min.js
- Existing files to modify:
  - frontend/unity-spa/core/routes.js
  - frontend/unity-spa/index.html (script includes)
  - Shared menu/header (add Help/Docs entry; exact file depends on current layout template)

task_progress Items:
- [ ] Step 1: Scaffold plans/wiki/ tree and write full Registrar → Enlistment guide plus category stubs
- [ ] Step 2: Add marked.min.js and purify.min.js to assets/lib and load them via index.html
- [ ] Step 3: Create DocsService (categories/pages, fetch, render, sanitize)
- [ ] Step 4: Create DocsController and docs.html
- [ ] Step 5: Register /docs and /docs/:category/:page? routes with internal role gating
- [ ] Step 6: Add Help/Docs to SPA menu visible to internal roles
- [ ] Step 7: Manual testing across roles and deep links; address path/role issues
- [ ] Step 8: Finalize formatting, capture screenshots, and update README with contributor notes
