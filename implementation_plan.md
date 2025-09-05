# Implementation Plan

[Overview]
Add an admin/registrar page for monitoring sections with capacity vs utilization using tb_mas_classlist.slots. For each classlist (section), compute and display:
- Enrolled count: number of students in tb_mas_classlist_student joined to tb_mas_registration for the same term where the student is considered enrolled.
- Enlisted count: number of students in tb_mas_classlist_student for the term (regardless of enrollment).
- Remaining slots: slots - enrolled_count (never negative).

This enables real-time capacity tracking per section, driven by legacy tables, and scoped by term (tb_mas_sy.intID). Enrolled determination is made against tb_mas_registration (same term). The page provides filtering and searching by subject, faculty, and section identifiers.

[Types]  
Introduce a typed response model for the new API endpoint.

Detailed type definitions:
- Request (query string)
  - term: int required (tb_mas_sy.intID)
  - page: int optional (default 1)
  - perPage: int optional (default 20)
  - filters (optional):
    - intSubjectID: int
    - intFacultyID: int
    - section: string (matches tb_mas_classlist.sectionCode using LIKE)
    - class_name: string (tb_mas_classlist.strClassName)
    - year: int (tb_mas_classlist.year)
    - sub_section: string
- Response (JSON)
  - data: Array<ClasslistSlotsItem>
  - meta: { page: int, per_page: int, total: int }
- ClasslistSlotsItem
  - classlist_id: int (tb_mas_classlist.intID)
  - section_code: string|null (tb_mas_classlist.sectionCode)
  - class_name: string (tb_mas_classlist.strClassName)
  - year: int|null
  - section: string|null (tb_mas_classlist.strSection)
  - sub_section: string|null
  - subject_code: string
  - subject_description: string
  - faculty_name: string|null
  - slots: int|null (tb_mas_classlist.slots)
  - enlisted_count: int
  - enrolled_count: int
  - remaining_slots: int (max(slots - enrolled_count, 0))
  - finalized: int (tb_mas_classlist.intFinalized)
- Enrolled semantics
  - A student is counted as enrolled if a tb_mas_registration row exists for (intStudentID = cls.intStudentID, intAYID = term) AND intROG indicates enrollment (>=1) AND is not withdrawn/terminal.
  - Practical SQL filters:
    - r.intROG >= 1
    - r.intROG NOT IN (3,5) when columns/values are present in the schema
  - NOTE: We will avoid relying on date fields (dteRegistered / date_enrolled) for counts to stay compatible with schema variants observed in logs.

[Files]
Add a dedicated API endpoint and a new AngularJS (1.x) SPA feature page.

Detailed breakdown:
- New files to be created
  - laravel-api/app/Services/ClasslistSlotsService.php
    - Purpose: Provide term-scoped, paginated classlist slot utilization with efficient SQL aggregation.
  - laravel-api/app/Http/Controllers/Api/V1/ClasslistSlotsController.php
    - Purpose: REST controller exposing GET /api/v1/classlists/slots with filters.
  - frontend/unity-spa/features/registrar/sections-slots/sections-slots.controller.js
    - Purpose: AngularJS controller for sections/slots monitoring page.
  - frontend/unity-spa/features/registrar/sections-slots/sections-slots.html
    - Purpose: UI view for tabular display, search, and pagination.
  - frontend/unity-spa/features/registrar/sections-slots/sections-slots.service.js (optional, if we keep controllers lean)
    - Purpose: Encapsulate API calls to the Laravel endpoint.

- Existing files to be modified
  - laravel-api/routes/api.php
    - Register new route GET /api/v1/classlists/slots -> ClasslistSlotsController@index
  - frontend/unity-spa/shared/components/sidebar/sidebar.controller.js
    - Add new menu entry under Registrar (or equivalent grouping) linking to sections/slots monitor.
  - Optionally: laravel-api/app/Http/Resources/RegistrarClasslistResource.php
    - If desired, add a dedicated resource ClasslistSlotsResource for consistent field naming. Not required for the first pass.

- Files to be deleted or moved
  - None

- Configuration updates
  - None

[Functions]
Add a new service method and a controller method for aggregation; wire a frontend fetch function.

Detailed breakdown:
- New functions
  - Class: App\Services\ClasslistSlotsService
    - Method: public function listByTerm(array $params): array
      - Input: ['term'=>int, 'page'=>int, 'perPage'=>int, optional filters]
      - Output: ['data'=>array, 'meta'=>array]
      - Purpose: Construct SQL to fetch classlists for the term along with enlisted_count, enrolled_count, and derived remaining_slots. Supports optional filters and isDissolved=0 by default.

    - Internal helpers (private)
      - buildBaseQuery($term): \Illuminate\Database\Query\Builder
        - Joins tb_mas_subjects and tb_mas_faculty for display fields
      - applyFilters($q, $params): void
        - Applies filters: intSubjectID, intFacultyID, section (LIKE), class_name, year, sub_section
      - aggregateCounts($q, $term): void
        - Adds subqueries/left joins for enlisted and enrolled counts

  - Class: App\Http\Controllers\Api\V1\ClasslistSlotsController
    - Method: public function index(Request $request): JsonResponse
      - Validates 'term' is present and numeric
      - Forwards to ClasslistSlotsService::listByTerm
      - Returns JSON with data and meta

  - Frontend AngularJS
    - sections-slots.service.js
      - getList({ term, page, perPage, filters }): $http.get(...)
    - sections-slots.controller.js
      - $scope.state for pagination, filters, and results
      - load() to call service and map response
      - compute client-side derived presentation elements (e.g., percentage bars)
    - sections-slots.html
      - Table with columns: Section Code, Subject Code, Subject Desc, Faculty, Capacity, Enlisted, Enrolled, Remaining, Finalized, and optional filters row

- Modified functions
  - frontend/unity-spa/shared/components/sidebar/sidebar.controller.js
    - Add navigation item linking to the new page (route path to be defined, e.g., #/registrar/sections-slots)

- Removed functions
  - None

[Classes]
Introduce one new controller class and one new service class.

Detailed breakdown:
- New classes
  - App\Services\ClasslistSlotsService
    - Key methods: listByTerm, buildBaseQuery, applyFilters, aggregateCounts
  - App\Http\Controllers\Api\V1\ClasslistSlotsController
    - Key methods: index()

- Modified classes
  - None of the existing service classes require modification; this feature remains additive.

- Removed classes
  - None

[Dependencies]
No new package dependencies.

- Backend: Laravel DB facade
- Frontend: Existing AngularJS 1.x stack; no new libs

[Testing]
Use API-first verification and then UI validation.

- API tests (manual via Postman/curl)
  1) GET /api/v1/classlists/slots?term={syid}
     - Validate each item includes capacity (slots), enlisted_count, enrolled_count, and remaining_slots = max(slots - enrolled_count, 0)
  2) Verify filters:
     - intSubjectID, intFacultyID, section (LIKE), class_name, year, sub_section
  3) Verify paging meta: meta.page, meta.per_page, meta.total
  4) Edge cases:
     - classlists with null slots (treat as null in response, remaining_slots should be 0 or null; for display we will render 0 if null)
     - dissolved classlists (isDissolved=1) excluded by default
     - Zero enrolled but positive enlisted → remaining_slots equals full capacity; enlisted_count is separately visible as requested.

- Enrollment determination checks
  - Ensure joined registration r: r.intStudentID = cls.intStudentID AND r.intAYID = term
  - Count enrolled when r.intROG >= 1 AND r.intROG NOT IN (3,5) (withdrawn/terminal)
  - Use WHERE EXISTS subquery or LEFT JOIN + COUNT(DISTINCT CASE WHEN conditions THEN cls.intStudentID END)

- UI tests
  - Load page, set term selector (from latest term or a dropdown)
  - Sort and filter; numbers match API
  - Remaining slots updates as filters change

[Implementation Order]
Implement backend first, then frontend wiring, then UI.

1) Backend service
   - Create laravel-api/app/Services/ClasslistSlotsService.php
   - Implement listByTerm($params):
     - Base query: tb_mas_classlist as cl
       - JOIN tb_mas_subjects as s ON s.intID = cl.intSubjectID
       - LEFT JOIN tb_mas_faculty as f ON f.intID = cl.intFacultyID
       - WHERE cl.strAcademicYear = :term AND cl.isDissolved = 0
       - SELECT cl.intID, cl.sectionCode, cl.strClassName, cl.year, cl.strSection, cl.sub_section, cl.intFinalized, cl.slots, s.strCode, s.strDescription, f.strFirstname, f.strLastname
     - enlisted_count:
       - LEFT JOIN subq_enlisted:
         SELECT intClassListID, COUNT(*) as enlisted_count
         FROM tb_mas_classlist_student
         WHERE intsyID = :term
         GROUP BY intClassListID
       - LEFT JOIN on cl.intID = subq_enlisted.intClassListID
     - enrolled_count:
       - LEFT JOIN subq_enrolled:
         SELECT cls.intClassListID, COUNT(*) as enrolled_count
         FROM tb_mas_classlist_student cls
         JOIN tb_mas_registration r ON r.intStudentID = cls.intStudentID AND r.intAYID = :term
         WHERE cls.intsyID = :term
           AND r.intROG >= 1
           AND (r.intROG NOT IN (3,5) OR 1=1) -- permissive for schema variants
         GROUP BY cls.intClassListID
       - LEFT JOIN on cl.intID = subq_enrolled.intClassListID
     - Apply filters (subject, faculty, section LIKE, class_name, year, sub_section)
     - Pagination (page, perPage)
     - Map rows into response array with:
       - faculty_name = CONCAT(f.strFirstname, ' ', f.strLastname) or null
       - slots as int (nullable)
       - enlisted_count = COALESCE(subq_enlisted.enlisted_count, 0)
       - enrolled_count = COALESCE(subq_enrolled.enrolled_count, 0)
       - remaining_slots = max((int)slots - enrolled_count, 0) when slots not null; else 0
   - Return ['data' => items, 'meta' => ['page' => page, 'per_page' => perPage, 'total' => total]]

2) Backend controller and route
   - Create laravel-api/app/Http/Controllers/Api/V1/ClasslistSlotsController.php with index(Request)
     - Validate term
     - Send request->query() to service
     - Return JSON response
   - Modify laravel-api/routes/api.php to add:
     Route::get('/v1/classlists/slots', [ClasslistSlotsController::class, 'index']);
   - Optional: auth/role middleware (registrar, admin) as appropriate

3) Frontend SPA page (AngularJS 1.x)
   - Create frontend/unity-spa/features/registrar/sections-slots/sections-slots.service.js
     - Expose getList(params)
   - Create frontend/unity-spa/features/registrar/sections-slots/sections-slots.controller.js
     - Manage term selection (default latest), filters, pagination
     - Call service and bind to $scope.items and $scope.meta
   - Create frontend/unity-spa/features/registrar/sections-slots/sections-slots.html
     - Filters: subject code (text), faculty (select optional), section code (text)
     - Columns:
       - Section Code | Subject Code | Subject Description | Faculty | Capacity | Enlisted | Enrolled | Remaining | Finalized
     - Render capacity - enrolled for Remaining
   - Wire new route/state (if router exists in this SPA) or link from sidebar to this HTML/controller pair

4) Sidebar entry
   - Modify frontend/unity-spa/shared/components/sidebar/sidebar.controller.js
     - Add menu item "Sections Slots Monitor" under Registrar → route to the new page

5) Verification
   - API manual checks
   - UI smoke tests
   - Edge checks with classlists having null slots and zero counts

task_progress Items:
- [ ] Step 1: Implement backend service ClasslistSlotsService::listByTerm with aggregation and filters
- [ ] Step 2: Add ClasslistSlotsController@index and wire GET /api/v1/classlists/slots
- [ ] Step 3: Build AngularJS page (service/controller/html) for sections/slots monitoring
- [ ] Step 4: Add sidebar navigation to the new page
- [ ] Step 5: Perform API and UI smoke tests; verify counts, filters, and remaining slots computation
