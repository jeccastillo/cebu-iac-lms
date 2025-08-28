# Implementation Plan

[Overview]
Update the My Classes page to list the logged-in faculty member’s classlists for a selected term using the Laravel API, with pagination and a direct link to edit/view grades for each classlist.

This work wires the existing AngularJS SPA page at #/faculty/classes to backend data. It will fetch available terms, default to the active term, and then query the classlists endpoint filtered by the current faculty and selected term. The UI will render a paginated table with the requested columns and include a View/Edit Grades action that navigates to the existing Classlist Grading Viewer route. The legacy CI link will be removed, completing the migration of this view to the new Laravel API.

[Types]  
Introduce concrete data shapes in JSDoc to document the objects used in the controller and view (no framework-level type system changes).

- Term
  - id: string | number (sy identifier as returned by /api/v1/generic/terms and /api/v1/generic/active-term)
  - label: string (human-readable display label, e.g., “2025-2026 T2”)
- FacultyLoginState
  - faculty_id: number (ID of the logged-in faculty)
  - roles?: string[] (optional role codes)
- ClasslistRow (as returned by GET /api/v1/classlists with joins)
  - intID: number (classlist ID)
  - strAcademicYear: string (term/sy id this classlist belongs to)
  - intSubjectID: number
  - intFacultyID: number
  - sectionCode?: string (render as “Section Code”)
  - subjectCode?: string (render as “Subject Code”)
  - subjectDescription?: string (render as “Description”)
  - intFinalized?: number (0 or 1; render as “Finalized” or “Not Finalized”)
- PaginationMeta (from controller response)
  - current_page: number
  - per_page: number
  - total: number
  - last_page: number

Validation rules and relationships:
- The faculty_id must be resolved from the current loginState stored in StorageService.
- The list query must include both strAcademicYear (term) and intFacultyID filters.
- Pagination (page, per_page) must be honored by both controller and UI.

[Files]
Modify existing files in the faculty feature and reuse existing services; minimal new files.

- Existing files to modify:
  - frontend/unity-spa/features/faculty/classes.controller.js
    - Replace placeholder logic with:
      - Fetch terms (using /api/v1/generic/terms)
      - Resolve active term (/api/v1/generic/active-term) and/or load persisted selection from StorageService
      - Resolve faculty_id from StorageService.getJSON('loginState')
      - Query ClasslistsService.list({ strAcademicYear, intFacultyID, page, per_page }) with pagination
      - Map API rows to the UI model and expose pagination meta
      - Provide handlers for term change and page changes
  - frontend/unity-spa/features/faculty/classes.html
    - Remove CI link
    - Add a filter bar with:
      - Term dropdown (vm.terms, vm.selectedTerm)
      - Per-page selector (optional, default 20)
    - Replace placeholder with a paginated table:
      - Columns: Subject Code, Description, Section Code, Finalized
      - Action: “View/Edit Grades” linking to #/classlists/{{c.intID}}/viewer
    - Add simple pagination controls (Prev/Next, page x of y)

- New files to be created (optional; prefer to avoid if not required):
  - None required. Term fetching will be done inline in the controller using $http and APP_CONFIG.API_BASE (endpoints: /generic/terms and /generic/active-term).

- Files to be deleted or moved:
  - None.

- Configuration updates:
  - None.

[Functions]
Introduce controller functions; no backend function changes are required.

- New functions in frontend/unity-spa/features/faculty/classes.controller.js:
  - init(): void
    - Purpose: Bootstraps page, loads terms, active term, and initial classes list.
  - loadTerms(): Promise<void>
    - Purpose: GET /api/v1/generic/terms and map to Term[]
  - loadActiveTerm(): Promise<void>
    - Purpose: GET /api/v1/generic/active-term, or fallback to persisted selection.
  - loadClasses(page?: number): Promise<void>
    - Purpose: GET /api/v1/classlists with params { strAcademicYear, intFacultyID, page, per_page }
    - Updates vm.classes and vm.meta
  - onTermChange(): void
    - Purpose: Persist selection, reset to page 1, reload classes.
  - onPerPageChange(): void
    - Purpose: Persist per_page, reset to page 1, reload classes.
  - nextPage(): void / prevPage(): void
    - Purpose: Basic pagination navigation.
  - gotoViewer(c: ClasslistRow): void
    - Purpose: $location.path('/classlists/' + c.intID + '/viewer') or use anchor in template.

- Modified functions:
  - None (existing placeholder removed; actual data wiring added).

- Removed functions:
  - None.

[Classes]
No class changes or new classes at the backend. AngularJS controllers are simple functions.

- Modified controller:
  - FacultyClassesController (frontend/unity-spa/features/faculty/classes.controller.js)
    - Inject additional dependencies: $http, APP_CONFIG, StorageService, ClasslistsService, $location (if needed)

[Dependencies]
No new third-party packages.

- Backend endpoints to use:
  - GET /api/v1/generic/terms
  - GET /api/v1/generic/active-term
  - GET /api/v1/classlists?strAcademicYear={term}&amp;intFacultyID={id}&amp;page={n}&amp;per_page={m}
- Existing Angular services to reuse:
  - ClasslistsService (list): currently does GET without auth headers; adequate since we pass intFacultyID explicitly.
  - StorageService: retrieve loginState and persist UI selections (term, per_page).
  - APP_CONFIG.API_BASE: for building $http URLs in controller.

[Testing]
Manual integration testing focused on UX and API correctness.

- Scenarios:
  - With a valid loginState.faculty_id:
    - On load: active term is selected automatically and classes are fetched.
    - Changing the term dropdown refetches and updates the table.
    - Pagination shows correct counts and allows navigating between pages.
    - View/Edit Grades link navigates to #/classlists/:id/viewer and loads grading viewer data.
    - “Finalized” column reflects intFinalized (0 = “Not Finalized”, 1 = “Finalized”).
  - With no classes for term: show the empty state message.
  - With large result sets: verify per_page and pagination meta work.
- Non-happy paths:
  - API errors (network, 500): show toast or light inline message; log to console.
  - Missing faculty_id in loginState: show an inline error indicating user context is missing.

[Implementation Order]
Implement in small, verifiable steps to minimize integration risks.

1. Controller wiring
   - Update FacultyClassesController:
     - Inject $http, APP_CONFIG, StorageService, ClasslistsService
     - Implement init(), loadTerms(), loadActiveTerm(), loadClasses(), onTermChange(), pagination handlers
2. Template update
   - Remove CI link, add filter bar (term, per-page)
   - Render table with requested columns
   - Add “View/Edit Grades” action to #/classlists/:id/viewer
   - Add simple pagination controls
3. Persistence and polish
   - Persist selected term and per_page in StorageService
   - Handle loading and error states
4. Verification
   - Manually test with seed data for multiple pages and finalization states
   - Confirm viewer navigation and permissions
