# Implementation Plan

[Overview]
Build a new AngularJS SPA page that displays a Daily Enrollment report with a date range filter, backed entirely by Laravel API endpoints. The report aggregates enrollments from tb_mas_registration by enumStudentType for a selected term and a date range based on dteRegistered. Provide an Excel export using the existing Laravel reporting stack.

This feature modernizes the legacy CodeIgniter report into the SPA while standardizing on Laravel for APIs and exports. The SPA will allow registrars to select a term, filter by date range, view per-day counts grouped by enumStudentType, and see totals. PDF generation is out of scope; Excel export remains via Laravel.

[Types]  
Define explicit API contracts and client-side structures for predictable integration.

- API request parameters
  - syid: integer, required. tb_mas_sy.intID of selected term.
  - date_from: string (YYYY-MM-DD), required. Inclusive start date.
  - date_to: string (YYYY-MM-DD), required. Inclusive end date.

- enum StudentTypeKey (normalized from enumStudentType)
  - freshman
  - transferee
  - second
  - continuing
  - shiftee
  - returning

- interface DailyEnrollmentBucket
  - date: string (YYYY-MM-DD)
  - freshman: number
  - transferee: number
  - second: number
  - continuing: number
  - shiftee: number
  - returning: number
  - total: number

- interface DailyEnrollmentTotals
  - freshman: number
  - transferee: number
  - second: number
  - continuing: number
  - shiftee: number
  - returning: number
  - total: number

- interface DailyEnrollmentResponse
  - syid: number
  - date_from: string (YYYY-MM-DD)
  - date_to: string (YYYY-MM-DD)
  - data: DailyEnrollmentBucket[]
  - totals: DailyEnrollmentTotals

Validation rules (Laravel):
- syid exists in tb_mas_sy.intID
- date_from, date_to: dates; date_to >= date_from
- Filtering on dteRegistered uses: dteRegistered >= date_from 00:00:00 AND dteRegistered < (date_to + 1 day) 00:00:00 for inclusivity.

[Files]
Add one new Laravel API endpoint and new Angular feature module files; wire routes and navigation.

- New (Frontend)
  - frontend/unity-spa/features/registrar/daily-enrollment.html
    - Purpose: UI for selecting date range and term, rendering the daily table and totals, and triggering export.
  - frontend/unity-spa/features/registrar/daily-enrollment.controller.js
    - Purpose: Binds date/term state, fetches data from Laravel, computes totals and handles export action.
  - frontend/unity-spa/features/registrar/daily-enrollment.service.js
    - Purpose: Angular service that calls Laravel GET /api/v1/reports/daily-enrollment.

- Modified (Frontend)
  - frontend/unity-spa/core/routes.js
    - Add route /registrar/daily-enrollment to the SPA.
  - frontend/unity-spa/shared/components/sidebar/sidebar.html
    - Add menu link to “Daily Enrollment (SPA)” for registrar/admin.

- New/Modified (Laravel API)
  - laravel-api/app/Http/Controllers/Api/V1/ReportsController.php
    - Add dailyEnrollmentSummary(Request $request) returning DailyEnrollmentResponse JSON.
  - laravel-api/routes/api.php
    - Register GET /api/v1/reports/daily-enrollment guarded by appropriate middleware (auth + role:registrar,admin).

- No changes (Legacy CI)
  - application/modules/registrar/* untouched.

[Functions]
Define new functions and changes with signatures and responsibilities.

- New (Laravel)
  - ReportsController::dailyEnrollmentSummary(Request $request)
    - Signature: public function dailyEnrollmentSummary(Request $request)
    - Purpose: Validate parameters, query tb_mas_registration by syid and dteRegistered range, include only enrolled (intROG = 1), group by calendar date and enumStudentType, aggregate per-day buckets and totals, return JSON.
    - Notes:
      - Normalize enumStudentType to one of: freshman, transferee, second, continuing, shiftee, returning.
      - Unknown enumStudentType values are ignored (not counted).
      - Date range end is inclusive via < (date_to + 1 day).

- Modified (Laravel)
  - laravel-api/routes/api.php
    - New route registration for the daily-enrollment endpoint under /api/v1/reports.

- New (Frontend)
  - DailyEnrollmentService.getDailyEnrollment(syid, dateFrom, dateTo)
    - Signature: function getDailyEnrollment(syid, dateFrom, dateTo): Promise<$http>
    - Purpose: Calls API_BASE + '/reports/daily-enrollment' with params, includes admin headers like ReportsService, returns JSON.
  - DailyEnrollmentController
    - init(): Resolve term from TermService; set default date range to today..today; prefill form.
    - load(): Validate inputs; call service; set vm.dates, vm.totals; compute overall total; handle empty results.
    - onTermChanged(): Listen to 'termChanged' event to update vm.selectedTerm and optionally reload.
    - exportExcel(): Use existing ReportsService.exportEnrolled(vm.selectedTerm.intID).

[Classes]
No additional PHP classes required beyond controller method. No Eloquent model additions; DB query uses Query Builder.

[Dependencies]
- Reuse existing:
  - AngularJS app, storage, TermService term selection broadcast, ReportsService for Excel.
  - Laravel Excel (maatwebsite/excel) already installed and used for enrolled students export.
- No new external packages.

[Testing]
- Backend API
  - Valid syid + valid date range returns JSON with aligned bucket totals; verify inclusivity (records on date_to counted).
  - Missing/invalid parameters return 422 with validation errors.
  - Performance: Ensure indexed filtering on (intAYID, dteRegistered) via where conditions and avoid N+1.

- Frontend
  - Load with today..today yields table; change term/date range and reload updates correctly.
  - Empty intervals show a friendly “No data” while totals are zero.
  - Export Enrolled triggers Excel download via existing endpoint for the selected term.
  - Auth headers present (X-Faculty-ID) consistent with ReportsService.

[Implementation Order]
Implement backend first, then frontend service, controller/template, routing, navigation, then test.

1) Laravel: Add daily-enrollment route in routes/api.php with auth/role middleware.
2) Laravel: Implement ReportsController::dailyEnrollmentSummary with validation, query, bucketing, and JSON response.
3) Frontend: Create DailyEnrollmentService calling the new Laravel endpoint.
4) Frontend: Create daily-enrollment.controller.js and daily-enrollment.html (term indicator, date inputs, Load button, export button, table).
5) Frontend: Update core routes to add /registrar/daily-enrollment.
6) Frontend: Add sidebar link under Registrar to the new route.
7) QA: Verify parity against legacy CI report for sample ranges; verify export via Laravel.

Task Progress Items:
- [ ] Step 1: Add Laravel route and implement ReportsController::dailyEnrollmentSummary
- [ ] Step 2: Create frontend DailyEnrollmentService (getDailyEnrollment)
- [ ] Step 3: Create frontend page daily-enrollment.html and controller daily-enrollment.controller.js
- [ ] Step 4: Wire new route in frontend/unity-spa/core/routes.js
- [ ] Step 5: Add sidebar navigation link for Daily Enrollment page
- [ ] Step 6: Test end-to-end with sample terms and ranges; validate totals and inclusivity
- [ ] Step 7: Confirm enrolled students Excel export integration via ReportsService
