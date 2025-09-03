# Implementation Plan

[Overview]
Implement an Admissions Applicants Analytics feature that visualizes applicant data per term (syid) using Chart.js. The solution provides a new SPA page with charts (status distribution, applicant type/sub-type, campus distribution, daily application trends, payment/waiver stats) with both single-term and side-by-side comparison modes, powered by new Laravel API analytics endpoints.

The scope includes: backend analytics aggregation endpoint(s) in Laravel, frontend AngularJS 1.x route, service, controller, and template for analytics, Chart.js integration via CDN, and wiring to admissions navigation. The data source is tb_mas_applicant_data joined to tb_mas_users (and applicant_types/campuses where available); all metrics are filtered per term (syid) and based on the latest applicant_data row per user (per syid) to avoid duplication.

[Types]  
Type definitions for API contracts and JS model shapes used by frontend and backend responses.

- Backend query primitives
  - LatestApplicantData per user and term: for a given syid, select the latest tb_mas_applicant_data row (max id) per (user_id, syid). Fallback to latest per user when syid is missing in the table.
  - Joins:
    - tb_mas_users u on u.intID = ad.user_id
    - tb_mas_applicant_types t on t.intID = ad.applicant_type (optional)
    - tb_mas_campuses c on c.id = u.campus_id (optional)

- API request
  - GET /api/v1/applicants/analytics/summary
    - Query params:
      - syid: int (required for primary term; if missing, 422)
      - compare_syid: int (optional; when provided, compute a second summary)
      - start: string Y-m-d (optional; default: 30 days before today for time series)
      - end: string Y-m-d (optional; default: today for time series)
      - campus: string|int (optional; filters campus by name or id; applied on u.campus_id or c.campus_name)
      - type: string (optional; filters ApplicantType.type)
      - sub_type: string (optional; filters ApplicantType.sub_type)
      - status: string (optional; filters ad.status)
      - search: string (optional; filters u.strFirstname/strLastname/strEmail/strMobileNumber LIKE %search%)

- API response shapes (JSON)
  - ApplicantsAnalyticsSummaryEnvelope
    {
      success: true,
      data: {
        terms: {
          [syid:string]: ApplicantsAnalyticsSummary
        },
        meta: {
          primary_syid: number,
          compare_syid: number|null,
          date_range: { start: string, end: string }
        }
      }
    }

  - ApplicantsAnalyticsSummary
    {
      syid: number,
      counts: {
        total_applicants: number,
        by_status: { [status: string]: number },
        by_applicant_type: { [type: string]: number },              // e.g., college, shs, grad
        by_applicant_sub_type: { [sub_type: string]: number },      // if available
        by_campus: { [campus_label: string]: number },              // campus_name or fallback id/string
        payment_flags: {
          paid_application_fee: number,
          paid_reservation_fee: number
        },
        waivers: {
          waive_application_fee: number
        }
      },
      timeseries: {
        daily_new_applications: Array<{ date: string (YYYY-MM-DD), count: number }>
      }
    }

- Frontend model shapes
  - vm.filters: {
      syidA: number, syidB?: number|null,
      start?: string, end?: string,
      status?: string, campus?: string|number, type?: string, sub_type?: string
    }
  - vm.charts: Record of Chart.js instances keyed by chart id strings.
  - vm.summaryA, vm.summaryB: ApplicantsAnalyticsSummary for each selected term.

Validation rules:
- syid (primary) is required on backend; compare_syid optional.
- start/end should validate to Y-m-d; fallback to defaults.

[Files]
Files to be modified and created across backend and frontend.

- New files to be created
  - laravel-api/app/Http/Controllers/Api/V1/ApplicantAnalyticsController.php
    - Purpose: Provide /applicants/analytics/summary endpoint aggregating applicants metrics per term and optional comparison term.

  - frontend/unity-spa/features/admissions/applicants/analytics.service.js
    - Purpose: AngularJS service wrapping analytics API calls with headers, params, and response unwrapping.

  - frontend/unity-spa/features/admissions/applicants/analytics.controller.js
    - Purpose: AngularJS controller implementing filters, term selection (on-page override), Chart.js dataset transforms, rendering and updating charts.

  - frontend/unity-spa/features/admissions/applicants/analytics.html
    - Purpose: Analytics page template with filters, term selectors, and chart canvases (status doughnut, type/subtype bar, campus bar, daily line, payments/waivers bar).

- Existing files to be modified
  - laravel-api/routes/api.php
    - Add route: GET /api/v1/applicants/analytics/summary → ApplicantAnalyticsController@summary, guarded with role: admissions, admin.

  - frontend/unity-spa/core/routes.js
    - Add route definition:
      - path: "/admissions/applicants/analytics"
      - template: "features/admissions/applicants/analytics.html"
      - controller: "ApplicantsAnalyticsController"
      - requiredRoles: ["admissions", "admin"]

  - frontend/unity-spa/index.html
    - Add Chart.js CDN script before core app files:
      <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

  - frontend/unity-spa/shared/components/sidebar/sidebar.html (optional, but recommended)
    - Add navigation link under Admissions to "Applicants Analytics" route, gated by roles admissions/admin.

- Files to be deleted or moved
  - None.

- Configuration file updates
  - None required (Chart.js via CDN; Laravel routes only).

[Functions]
New functions and modifications required for analytics feature.

- New functions
  - Backend: ApplicantAnalyticsController@summary (laravel-api/app/Http/Controllers/Api/V1/ApplicantAnalyticsController.php)
    - Signature: public function summary(Request $request): JsonResponse
    - Purpose: compute ApplicantsAnalyticsSummary for primary syid and optional compare_syid using:
      - latest rows per (user_id, syid) via correlated subqueries (ad.id = (select max(ad2.id) ...))
      - groupings for by_status, by_applicant_type (join tb_mas_applicant_types), by_applicant_sub_type, by_campus (join tb_mas_campuses, fallback to u.campus_id), payments &amp; waivers counts, daily_new_applications grouped by DATE(ad.created_at) within [start,end].
    - Edge cases: If applicant_data.syid column is missing in environment, fallback to latest per user (warn in meta). If campuses/applicant_types tables/columns unavailable, gracefully skip or fallback to raw values.

  - Frontend: ApplicantsAnalyticsService.summary (frontend/unity-spa/features/admissions/applicants/analytics.service.js)
    - Signature: summary(params: object) → Promise<ApplicantsAnalyticsSummaryEnvelope>
    - Purpose: GET /applicants/analytics/summary with params { syid, compare_syid?, start?, end?, ...filters }, attach X-Faculty-ID header from StorageService (like ApplicantsService).

  - Frontend: ApplicantsAnalyticsController (frontend/unity-spa/features/admissions/applicants/analytics.controller.js)
    - Methods:
      - init(): read default term(s) from TermService but allow on-page override; set initial filters; load data.
      - load(): call service, map data into chart datasets; create/update Chart.js instances.
      - setTermA/ setTermB(): handlers for term selectors; supports single-term and side-by-side (B optional).
      - buildCharts(): create charts (status doughnut, type/sub-type bar, campus bar, payments/waivers bar, daily line).
      - updateChart(chart, data): utility update; destroy/recreate safely when series/labels change.
      - toSeries(summaryA, summaryB, key): helpers to align categories and build two datasets for compare mode.
      - onFilterChange(): reloads data with debounced behavior.

- Modified functions
  - frontend/unity-spa/core/routes.js: configure($routeProvider...) — add .when("/admissions/applicants/analytics", ...) block.

- Removed functions
  - None.

[Classes]
New classes and modifications (PHP controller class; AngularJS controllers are functions not classes in ES5 style).

- New classes
  - App\Http\Controllers\Api\V1\ApplicantAnalyticsController
    - File: laravel-api/app/Http/Controllers/Api/V1/ApplicantAnalyticsController.php
    - Key methods: summary(Request $request): JsonResponse
    - Inheritance: extends Controller
    - Dependencies: Illuminate\Support\Facades\DB; optional use of Carbon for date range.

- Modified classes
  - None.

- Removed classes
  - None.

[Dependencies]
Add Chart.js via CDN only. No Composer or npm changes.

- Frontend:
  - Chart.js v4 UMD: https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js

- Backend:
  - None (use existing Laravel DB/Query Builder).

[Testing]
A layered testing and validation approach.

- Backend API:
  - Manual smoke using browser or curl:
    - GET /api/v1/applicants/analytics/summary?syid=29
    - GET /api/v1/applicants/analytics/summary?syid=29&amp;compare_syid=30
    - Include campus/type/status filters to validate filter plumbing.
  - Optional Feature Test (PHPUnit) new test file:
    - laravel-api/tests/Feature/ApplicantAnalyticsControllerTest.php
      - test_summary_requires_syid_returns_422
      - test_summary_structure_contains_expected_keys
      - test_summary_compare_syid_contains_terms_map

- Frontend:
  - Load /admissions/applicants/analytics with admissions/admin role.
  - Validate single-term mode (only Term A selected) and compare mode (Term B selected).
  - Verify each chart renders and updates when terms/filters change.
  - Confirm on-page term override works even if sidebar term is set differently.

[Implementation Order]
A clear sequence to minimize churn and ensure verification at each step.

1) Backend analytics endpoint
   - Create ApplicantAnalyticsController.php with summary() aggregation for syid and compare_syid.
   - Wire route in laravel-api/routes/api.php with role: admissions,admin.
   - Quick manual test via query strings to verify JSON shape and counts.

2) Frontend service
   - Create features/admissions/applicants/analytics.service.js with summary() wrapping API call and admin headers.

3) Frontend page and charts
   - Add Chart.js script tag in frontend/unity-spa/index.html (before core app files).
   - Create analytics.html with layout: filters (term A/B selectors, date range), chart canvases, toggles.
   - Create analytics.controller.js to bind filters and render charts in single-term and side-by-side modes.

4) Routing and navigation
   - Update core/routes.js to add /admissions/applicants/analytics route with requiredRoles ["admissions","admin"].
   - Optionally add link in shared/components/sidebar/sidebar.html under Admissions group.

5) Verification
   - Navigate to the page; test single term then side-by-side.
   - Validate counts match backend JSON by logging results in console.
   - Check performance (no heavy memory usage); verify graceful handling if types/campus tables missing.

6) Optional automated test
   - Add Feature test for controller summary() structure and 422 on missing syid (time-permitting).
