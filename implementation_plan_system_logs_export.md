# Implementation Plan

[Overview]
Add an XLSX export capability for System Logs, exposing a new backend endpoint with the same filters as listing and a frontend Export button on the Logs page.

This implementation enables privileged users to download a filtered snapshot of system logs for auditing or analysis. On the server, we will use Laravel Excel to stream a properly formatted XLSX file. On the client, AngularJS will request the file (with the same filters used for listing) and trigger a browser download. Access to the export will be restricted to admin and registrar roles.

[Types]  
No PHP type-system changes; introduce a well-defined filter payload and a deterministic column mapping for XLSX export.

Proposed logical structures:
- ExportFilters (associative array validated in controller and export class):
  - page? (ignored by export)
  - per_page? (ignored by export)
  - entity?: string
  - action?: string
  - user_id?: int
  - entity_id?: int
  - method?: string
  - path?: string
  - q?: string (applies like on entity, action, path, method, user_agent)
  - date_from?: YYYY-MM-DD
  - date_to?: YYYY-MM-DD

- Export columns (order matters):
  1) created_at (string, format: YYYY-MM-DD HH:mm:ss, server timezone)
  2) user_id (int or empty)
  3) entity (string)
  4) action (string: create/update/delete)
  5) method (string or empty)
  6) path (string or empty)

[Files]
Create a new export class, add an API route and controller method, and extend the frontend logs module with an Export button and service call.

- New files to be created:
  - laravel-api/app/Exports/SystemLogsExport.php
    - Purpose: Encapsulate query building and data mapping for XLSX using maatwebsite/excel.
- Existing files to be modified:
  - laravel-api/composer.json
    - Add dependency "maatwebsite/excel": "^3.1".
  - laravel-api/app/Http/Controllers/Api/V1/SystemLogController.php
    - Add export(Request $request) method that validates filters and returns Excel::download().
    - Optionally refactor filter logic into a private method used by index() and export().
  - laravel-api/routes/api.php
    - Add GET /api/v1/system-logs/export route protected by role:registrar,admin middleware.
  - frontend/unity-spa/features/logs/logs.service.js
    - Add export(params) that issues GET with responseType: 'arraybuffer', sends X-Faculty-ID header, and returns binary data.
  - frontend/unity-spa/features/logs/logs.controller.js
    - Wire vm.export() to call SystemLogsService.export() with current filters, then trigger download using Blob + temporary anchor.
  - frontend/unity-spa/features/logs/logs.html
    - Add an "Export XLSX" button near the search/reset controls.
- Files to be deleted or moved:
  - None.
- Configuration file updates:
  - None required beyond composer.json (package discovery will register Laravel Excel automatically). Ensure PHP ext-zip is present in runtime stack.

[Functions]
Add a new controller method for export and client-side functions for initiating download.

- New functions:
  - SystemLogController::export(Request $request): Symfony\Component\HttpFoundation\BinaryFileResponse
    - File: laravel-api/app/Http/Controllers/Api/V1/SystemLogController.php
    - Purpose: Validate filter payload; instantiate SystemLogsExport with payload; return Excel::download() using filename pattern system-logs-YYYYMMDD-HHmm.xlsx.
  - SystemLogsExport::__construct(array $filters)
    - File: laravel-api/app/Exports/SystemLogsExport.php
    - Purpose: Store validated filters to be applied during query().
  - SystemLogsExport::query(): \Illuminate\Database\Eloquent\Builder
    - Purpose: Build a SystemLog query applying the same filtering semantics as index().
  - SystemLogsExport::headings(): array
    - Purpose: Provide XLSX header row: ['created_at', 'user_id', 'entity', 'action', 'method', 'path'].
  - SystemLogsExport::map(SystemLog $row): array
    - Purpose: Map model instances to export row arrays in the precise column order.
  - SystemLogsService.export(params): Promise<ArrayBuffer>
    - File: frontend/unity-spa/features/logs/logs.service.js
    - Purpose: HTTP GET to /system-logs/export with query params and admin header (X-Faculty-ID) and responseType 'arraybuffer'.
  - SystemLogsController (Angular) vm.export(): void
    - File: frontend/unity-spa/features/logs/logs.controller.js
    - Purpose: Collect current filters, call service.export(), construct a Blob, and trigger browser download of system-logs-YYYYMMDD-HHmm.xlsx.
- Modified functions:
  - Optionally refactor SystemLogController::index filtering into a reusable private function for consistency, then call it from both index() and export(). If not refactored, replicate logic carefully in export.
- Removed functions:
  - None.

[Classes]
Add a new Export class for XLSX generation.

- New classes:
  - App\Exports\SystemLogsExport
    - File: laravel-api/app/Exports/SystemLogsExport.php
    - Implements: \Maatwebsite\Excel\Concerns\FromQuery, WithHeadings, WithMapping, ShouldAutoSize
    - Key Methods:
      - __construct(array $filters)
      - query(): Builder
      - headings(): array
      - map(SystemLog $row): array
    - Inheritance: none.
- Modified classes:
  - App\Http\Controllers\Api\V1\SystemLogController
    - Add export() method, optional private applyFilters() helper for reuse in index() and export().
- Removed classes:
  - None.

[Dependencies]
Add Laravel Excel for XLSX support.

- New packages:
  - maatwebsite/excel: ^3.1
    - Auto-discovered by Laravel; no provider registration necessary.
    - Requires PHP ext-zip; ensure the environment has the Zip extension enabled.
- Version changes:
  - None other than composer.lock updates from install.
- Integration requirements:
  - composer install/update in laravel-api; no artisan vendor:publish required for baseline usage.

[Testing]
Validate both backend and frontend flows.

- Backend tests:
  - Call GET /api/v1/system-logs/export with:
    - No filters → returns XLSX with all rows (within system memory limits).
    - Filters for entity/action/user_id/method/path/q/date_from/date_to → verify row set matches GET /api/v1/system-logs with same filters.
  - Validate response headers:
    - Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet
    - Content-Disposition includes filename matching system-logs-YYYYMMDD-HHmm.xlsx
  - Verify role protection:
    - Unprivileged role or missing X-Faculty-ID → 403.
    - admin or registrar role → 200 with file stream.
- Frontend tests:
  - On /#/logs page:
    - Apply filters, click "Export XLSX" → browser downloads the file; filename pattern matches spec.
    - Validate that request includes X-Faculty-ID header pulled from StorageService loginState.
    - Column headers and order in XLSX are: created_at, user_id, entity, action, method, path.
  - Edge cases:
    - No results → file contains only header row.
    - Very long path → included as full string (Excel cell).
- Manual verification:
  - Cross-check that rows in Excel match API listing for the same filter criteria and sort order (created_at desc in listing; export uses query without pagination; keep consistent ordering with orderBy created_at desc).

[Implementation Order]
Implement backend first, then frontend, validate end-to-end.

1) Composer dependency
   - Add "maatwebsite/excel": "^3.1" to laravel-api/composer.json and run composer install in laravel-api.
   - Ensure PHP Zip extension enabled.

2) Backend Export class
   - Create laravel-api/app/Exports/SystemLogsExport.php implementing FromQuery, WithHeadings, WithMapping, ShouldAutoSize.
   - Map columns in order: created_at, user_id, entity, action, method, path.

3) Controller + Route
   - Add export(Request $request) method to SystemLogController.
   - Validate filters identical to index().
   - Build filters array; instantiate SystemLogsExport and return Excel::download($export, "system-logs-YYYYMMDD-HHmm.xlsx").
   - Add route in routes/api.php:
     Route::get('/system-logs/export', [SystemLogController::class, 'export'])->middleware('role:registrar,admin');
   - Note: The /logs frontend route currently requires ['admin'] only. If registrars should access the UI, update requiredRoles to ['registrar', 'admin'].

4) Frontend service
   - In frontend/unity-spa/features/logs/logs.service.js:
     - Add export(params) using $http with { responseType: 'arraybuffer', headers: include X-Faculty-ID } to GET BASE + '/system-logs/export'.

5) Frontend controller + template
   - In logs.controller.js:
     - Add vm.export = export; implement function to call service.export() with vm.filters (excluding pagination fields) and then construct a Blob and trigger download using a temporary anchor element.
   - In logs.html:
     - Add "Export XLSX" button next to Search/Reset that calls vm.export() and disables while vm.loading if desired.

6) E2E verification
   - Exercise filters via UI; compare listing results to exported file content.
   - Confirm role gating for export endpoint is enforced (admin/registrar allowed).

7) Optional refactor
   - Extract filter application into a shared helper used by both index() and export() to avoid drift.
