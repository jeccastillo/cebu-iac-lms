# Implementation Plan

[Overview]
Add import pipelines for classrooms and room schedules with downloadable Excel templates, following existing import architecture (Students/Subjects) and enforcing campus and role constraints.

This implementation introduces two new import modules: one for Classrooms and one for Schedules. Each module provides a GET endpoint that streams an .xlsx template and a POST endpoint that accepts .xlsx/.xls/.csv uploads. The backend follows the same patterns as StudentImportService and SubjectImportService: PhpSpreadsheet for templates/parsing, service-layer normalization and resolution logic, and controller-layer streaming and request validation. Permissions align with existing CRUD: classrooms are restricted to building_admin,admin; schedules to registrar,admin. Schedule imports resolve classlists by Class Name + Section + Term and Campus ID as specified, honor “TBA” room code to 99999, and upsert by strScheduleCode.

[Types]  
The type system will be extended with two services’ data contracts for normalized rows and template schemas.

Detailed type definitions, interfaces, and data structures:

1) Classroom Import Template Schema (Sheet: "classrooms")
- Room Code (string, required)
  - Maps to tb_mas_classrooms.strRoomCode
  - Validation: non-empty, trim; used with campus_id to find existing row
- Type (enum: lecture|laboratory|hrm|pe, required)
  - Maps to tb_mas_classrooms.enumType
  - Validation: lowercased exact match to allowed set
- Description (string, optional)
  - Maps to tb_mas_classrooms.description
- Campus (string, optional)
  - Case-insensitive exact match to tb_mas_campuses.campus_name
  - Resolves to campus_id if provided
- Campus ID (integer, optional)
  - Maps to tb_mas_classrooms.campus_id
Resolution rule: Either Campus or Campus ID must be provided and resolve to a valid campus. If both are provided, they must refer to the same campus.

Upsert identity: (strRoomCode, campus_id) composite natural key.
Prohibited write columns (never written by import): intID, timestamps, etc. (align with guarded logic in model and service).

2) Schedule Import Template Schema (Sheet: "schedules")
- Code (string, required, unique)
  - Maps to tb_mas_room_schedule.strScheduleCode
  - Upsert identity: “just code” (update on code match; else insert)
- Term (int, required)
  - Maps to tb_mas_room_schedule.intSem
  - Must exist in tb_mas_sy.intID and belong to Campus ID (tb_mas_sy.campus_id == provided Campus ID)
- Day (int 1..7, required)
  - Maps to tb_mas_room_schedule.strDay
  - 7 follows same semantics used in ScheduleController (all/block days behavior)
- Start (HH:MM, required)
  - Maps to tb_mas_room_schedule.dteStart
  - Validation: date_format:H:i
- End (HH:MM, required)
  - Maps to tb_mas_room_schedule.dteEnd
  - Validation: date_format:H:i and after Start
- Class Type (enum: lect|lab, required)
  - Maps to tb_mas_room_schedule.enumClassType
- Room Code (string, required)
  - Resolves to tb_mas_classrooms.intID given Campus ID
  - Special “TBA” is supported and maps to intRoomID=99999
- Class Name (string, required)
  - Used to locate Classlist (tb_mas_classlist.strClassName)
- Section (string, required)
  - Used to locate Classlist via tb_mas_classlist.blockSection (and provides blockSection payload)
- Campus ID (int, required)
  - Used to scope School Year and classlist search
Additional runtime resolution:
- intClasslistID: resolved by (Class Name, Section, Term=intSem) within Campus ID scope
- intRoomID: resolved by Room Code within Campus ID (or 99999 for TBA)
- intEncoderID: resolved from header X-Faculty-ID if present (optional; enhances auditing)

Conflict checks (mirroring ScheduleController):
- Room conflict: time overlap within same room and term; skip row with error if conflict
- Section conflict: time overlap for same block section and term; skip on conflict
- Faculty conflict: time overlap for same faculty (via Classlist faculty) and term; skip on conflict

[Files]
We will add new controllers, requests, services, exports, assets, and routes. No deletions.

Detailed breakdown:
- New files to be created (with full paths and purpose)
  - laravel-api/app/Http/Controllers/Api/V1/ClassroomImportController.php
    - Purpose: Provide template download and import endpoints for classrooms (roles: building_admin,admin)
  - laravel-api/app/Http/Controllers/Api/V1/ScheduleImportController.php
    - Purpose: Provide template download and import endpoints for schedules (roles: registrar,admin)
  - laravel-api/app/Http/Requests/Api/V1/ClassroomImportRequest.php
    - Purpose: Validate multipart file and optional dry_run flag for classrooms import
  - laravel-api/app/Http/Requests/Api/V1/ScheduleImportRequest.php
    - Purpose: Validate multipart file and optional dry_run flag for schedules import
  - laravel-api/app/Services/ClassroomImportService.php
    - Purpose: Build template, parse files, normalize/resolve, and upsert tb_mas_classrooms by (strRoomCode, campus_id)
  - laravel-api/app/Services/ScheduleImportService.php
    - Purpose: Build template, parse files, normalize/resolve, conflict checks, and upsert tb_mas_room_schedule by strScheduleCode
  - laravel-api/app/Exports/ClassroomTemplateExport.php
    - Purpose: Thin wrapper to return Spreadsheet from ClassroomImportService::generateTemplateXlsx()
  - laravel-api/app/Exports/ScheduleTemplateExport.php
    - Purpose: Thin wrapper to return Spreadsheet from ScheduleImportService::generateTemplateXlsx()
  - laravel-api/scripts/test_classroom_template.php
    - Purpose: Developer test script to quickly output a classroom template file
  - laravel-api/scripts/test_schedule_template.php
    - Purpose: Developer test script to quickly output a schedule template file
  - assets/excel/classrooms-import-sample.csv
    - Purpose: Example CSV for classrooms import
  - assets/excel/schedules-import-sample.csv
    - Purpose: Example CSV for schedules import

- Existing files to be modified (with specific changes)
  - laravel-api/routes/api.php
    - Add:
      - Route::get('/classrooms/import/template', [ClassroomImportController::class, 'template'])->middleware('role:building_admin,admin');
      - Route::post('/classrooms/import', [ClassroomImportController::class, 'import'])->middleware('role:building_admin,admin');
      - Route::get('/schedules/import/template', [ScheduleImportController::class, 'template'])->middleware('role:registrar,admin');
      - Route::post('/schedules/import', [ScheduleImportController::class, 'import'])->middleware('role:registrar,admin');

- Files to be deleted or moved
  - None

- Configuration file updates
  - None required; PhpSpreadsheet already in use

[Functions]
Introduce service-layer functions for templates, parsing, normalization, resolution, conflict checks, and upsert flows. Controller actions stream templates and perform imports similar to existing Subject/Student import controllers.

Detailed breakdown:
- New functions (name, signature, path, purpose)
  - ClassroomTemplateExport::build(): Spreadsheet
    - Path: app/Exports/ClassroomTemplateExport.php
    - Purpose: Return template from service
  - ScheduleTemplateExport::build(): Spreadsheet
    - Path: app/Exports/ScheduleTemplateExport.php
    - Purpose: Return template from service
  - ClassroomImportService::generateTemplateXlsx(): Spreadsheet
    - Path: app/Services/ClassroomImportService.php
    - Purpose: Build “classrooms” sheet with headers [Room Code, Type, Description, Campus, Campus ID], add “Notes”
  - ClassroomImportService::parse(string $path, string $ext): \Generator
    - Purpose: Stream parsed rows (['line' => int, 'data' => array]) from xlsx/xls/csv
  - ClassroomImportService::normalizeRow(array $row): array [cols, meta]
    - Purpose: Map input into tb_mas_classrooms columns + meta (campus_name/campus_id)
  - ClassroomImportService::resolveForeigns(array &amp;$cols, array $meta): void
    - Purpose: Resolve campus to campus_id; error when unresolved/mismatch
  - ClassroomImportService::upsertRows(iterable $rows, bool $dryRun=false): array
    - Purpose: Upsert by (strRoomCode, campus_id), return summary: [totalRows, inserted, updated, skipped, errors[]]

  - ScheduleImportService::generateTemplateXlsx(): Spreadsheet
    - Purpose: Build “schedules” sheet with headers [Code, Term, Day, Start, End, Class Type, Room Code, Class Name, Section, Campus ID], add “Notes”
  - ScheduleImportService::parse(string $path, string $ext): \Generator
    - Purpose: Stream parsed rows from files
  - ScheduleImportService::normalizeRow(array $row): array [cols, meta, key]
    - Purpose: Map to tb_mas_room_schedule write columns and meta for lookups. key = strScheduleCode (string)
  - ScheduleImportService::resolveForeigns(array &amp;$cols, array $meta, array &amp;$contextMeta): void
    - Purpose: Resolve:
      - School Year: tb_mas_sy.intID = Term, validate campus_id
      - Classlist: find tb_mas_classlist by strClassName (Class Name), blockSection (Section), strAcademicYear (Term) within Campus ID; populate intClasslistID; also fetch faculty ID for conflict check
      - Room: Room Code “TBA” → intRoomID=99999; otherwise resolve within Campus ID by strRoomCode
      - Encoder: optional X-Faculty-ID header passed down via contextMeta['encoder_id'] → intEncoderID
  - ScheduleImportService::checkRoomConflicts(array $cols, ?int $excludeId=null): array
  - ScheduleImportService::checkSectionConflicts(array $cols, ?int $excludeId=null, string $blockSection): array
  - ScheduleImportService::checkFacultyConflicts(array $cols, ?int $excludeId=null, ?int $facultyId): array
    - Purpose: Mirror ScheduleController conflict logic
  - ScheduleImportService::upsertRows(iterable $rows, bool $dryRun=false, bool $skipConflicts=true, array $contextMeta=[]): array
    - Purpose: Upsert by strScheduleCode; on conflicts if skipConflicts=true, record errors and skip

  - ClassroomImportController::template(Request $request)
    - Purpose: Stream download .xlsx, similar to StudentImportController::template
  - ClassroomImportController::import(ClassroomImportRequest $request): JsonResponse
    - Purpose: Multipart upload handling; call service->parse and service->upsertRows
  - ScheduleImportController::template(Request $request)
    - Purpose: Stream download .xlsx
  - ScheduleImportController::import(ScheduleImportRequest $request): JsonResponse
    - Purpose: Multipart upload handling with conflict checks; pass X-Campus-ID (and X-Faculty-ID) via contextMeta for consistency

- Modified functions
  - None

- Removed functions
  - None

[Classes]
- New classes
  - App\Http\Controllers\Api\V1\ClassroomImportController
  - App\Http\Controllers\Api\V1\ScheduleImportController
  - App\Http\Requests\Api\V1\ClassroomImportRequest
  - App\Http\Requests\Api\V1\ScheduleImportRequest
  - App\Services\ClassroomImportService
  - App\Services\ScheduleImportService
  - App\Exports\ClassroomTemplateExport
  - App\Exports\ScheduleTemplateExport

- Modified classes
  - None

- Removed classes
  - None

[Dependencies]
No new packages required.

Details of new packages/version changes:
- PhpSpreadsheet is already installed and used by existing import controllers/services.
- No composer.json updates required.

[Testing]
Manual and scripted verification against acceptance rules and conflict logic.

Test file requirements and validation strategies:
- API tests (Postman/curl):
  - Classrooms:
    - GET /api/v1/classrooms/import/template with role building_admin → returns .xlsx
    - POST /api/v1/classrooms/import with multipart file and dry_run=true → returns counts, errors array; then dry_run=false → commits
    - Verify upsert: same Room Code + Campus ID updates description/type
  - Schedules:
    - GET /api/v1/schedules/import/template with role registrar → returns .xlsx
    - POST /api/v1/schedules/import with multipart file and headers:
      - X-Campus-ID: match rows’ Campus ID (or rely solely on column; service enforces row Campus ID)
      - X-Faculty-ID: optional, to set intEncoderID
    - Verify classlist resolution by Class Name + Section + Term within campus
    - Verify “TBA” Room Code maps to 99999
    - Verify conflicts cause rows to be skipped with detailed errors
- Developer scripts:
  - laravel-api/scripts/test_classroom_template.php → exercises ClassroomTemplateExport and writes a file
  - laravel-api/scripts/test_schedule_template.php → exercises ScheduleTemplateExport

[Implementation Order]
Implement in layered sequence minimizing coupling and enabling early verification.

1) Services: ClassroomImportService and ScheduleImportService with template, parse, normalize/resolve, upsert, and conflict checks (for schedules)
2) Export wrappers: ClassroomTemplateExport and ScheduleTemplateExport
3) Requests: ClassroomImportRequest and ScheduleImportRequest for upload validation
4) Controllers: ClassroomImportController and ScheduleImportController
5) Routes: Add import routes with correct role middleware in routes/api.php
6) Samples/scripts: Add sample CSVs and developer template test scripts
7) Manual tests: Run dry_run then commit runs, verify conflict detection and TBA handling
8) Optional future: Frontend upload UI parity (separate PR)

task_progress Items:
- [ ] Step 1: Implement ClassroomImportService with template generation, parse, normalize/resolve, upsert
- [ ] Step 2: Implement ScheduleImportService with template generation, parse, normalize/resolve, conflict checks, upsert
- [ ] Step 3: Create ClassroomTemplateExport and ScheduleTemplateExport wrappers
- [ ] Step 4: Add ClassroomImportRequest and ScheduleImportRequest validators
- [ ] Step 5: Add ClassroomImportController and ScheduleImportController
- [ ] Step 6: Wire new endpoints in routes/api.php with correct role middleware
- [ ] Step 7: Add sample CSVs and developer test scripts
- [ ] Step 8: Run manual API tests (dry_run and full upsert), verify conflicts and TBA handling
- [ ] Step 9: Prepare follow-up frontend tasks (optional)
