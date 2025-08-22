# Implementation Plan

[Overview]
Migrate the existing CodeIgniter-based LMS to a modern Laravel API with a Vue.js SPA frontend while maintaining feature parity, data continuity, and minimal downtime.

This migration will re-platform the application into a clean separation of concerns:
- Backend: Laravel (RESTful JSON API), Eloquent ORM, queues, events, scheduler, and first-class authentication/authorization.
- Frontend: Vue 3 SPA (Vite), modular components, routing, state management, and API-driven UX.
- Infrastructure: Incremental “strangler” pattern to reduce risk, allowing legacy and new systems to coexist temporarily, with shared database and phased endpoint cutover.

Key goals:
- Preserve all functional domains: users/auth, admissions, registrar, finance, scheduling, classroom, messaging, portal, unity/admin, reservations, deficiencies, PDFs/excel, AI analytics.
- Improve reliability, performance, developer productivity, and testability.
- Ensure backward compatibility during transition via URL routing and shared database, then perform a clean cutover.

[Types]  
Adopt PHP 8.2 strict typing for backend and TypeScript for the Vue frontend to enforce stronger contracts across the API boundary.

Backend core data structures will map to Eloquent models based on existing schema (table names retained initially for compatibility). Key domain types and relationships (illustrative; adjust to actual schema during model discovery):
- User (users)
  - Fields: id (int), email (string), password_hash (string), role (enum), name fields, status, created_at, updated_at
  - Relations: hasMany(Messages), hasOne(Student|Faculty) depending on role
  - Notes: replicate legacy Salting/Password hashing (application/libraries/Salting.php) or migrate to bcrypt/argon2 with a compatibility layer
- Student (students)
  - Fields: id, user_id, student_number, curriculum_id, program_id, year_level, status
  - Relations: belongsTo(User), hasMany(Enrollment), hasMany(Transactions), hasMany(ClasslistMembership)
- Faculty (faculty)
  - Fields: id, user_id, employee_no, department_id, status
  - Relations: belongsTo(User), hasMany(Classlists), hasMany(Schedules)
- Program (programs)
  - Fields: id, code, name, department_id, level
  - Relations: hasMany(Curriculum)
- Subject (subjects)
  - Fields: id, code, description, units, type, lab, lecture
  - Relations: belongsToMany(Curriculum) with pivot details
- Curriculum (curriculums)
  - Fields: id, program_id, year_effective, version, status
  - Relations: hasMany(CurriculumSubject), belongsTo(Program)
- Classlist (classlists)
  - Fields: id, subject_id, faculty_id, ay_id, section, schedule_code, capacity, status
  - Relations: belongsTo(Subject), belongsTo(Faculty), hasMany(ClasslistMembership)
- ClasslistMembership (classlist_students)
  - Fields: id, classlist_id, student_id, status, grades fields
  - Relations: belongsTo(Classlist), belongsTo(Student)
- Schedule (schedules)
  - Fields: id, code, day, time_start, time_end, room_id, section, sem
- Transactions/Finance (transactions, payments, ORs)
  - Fields: id, student_id, type, amount, or_number, date_posted
- Messages (messages)
  - Fields: id, from_user_id, to_user_id, subject, body, read_at
- Admissions (applicants, exams, registrations)
  - Entities mapping to legacy admissions tables per AdmissionsV1/V2
- Reservations (reservations)
  - Fields per create_reservation_table.sql and enhanced schema
- Deficiencies (deficiencies)
  - Fields: id, student_id, deficiency_type, status, remarks
- AI Analytics (ai_analysis)
  - Based on application/modules/ai_analytics/sql/create_ai_analysis_table.sql
Validation rules: enforce via Laravel Form Requests; front-end TS interfaces mirror API DTOs for requests/responses. Example TS interfaces: UserDTO, StudentDTO, EnrollmentDTO, ClasslistDTO, TransactionDTO, etc.

[Files]
Introduce two new top-level projects with clear boundaries; preserve the existing CodeIgniter app during migration to enable phased rollout.

New files/projects:
- laravel-api/ (Laravel 10/11)
  - app/Models/*.php (User, Student, Faculty, Program, Subject, Curriculum, Classlist, ClasslistMembership, Transaction, Message, Admission*, Reservation, Deficiency, AiAnalysis, etc.)
  - app/Http/Controllers/Api/V1/*.php (AdmissionsController, RegistrarController, FinanceController, ScheduleController, ClassroomController, MessagesController, UsersController, PortalController, UnityAdminController, ReservationController, DeficienciesController, PdfController, ExcelController, AiAnalyticsController)
  - app/Http/Requests/*.php (FormRequest validators per endpoint)
  - app/Services/*.php (Domain services e.g., EnrollmentService, TuitionService, PdfService, ExcelService, AdmissionsService, MessageService)
  - app/Policies/*.php (resource policies)
  - database/migrations/*.php (initial scaffolding and additive changes; no destructive changes in phase 1)
  - database/seeders/*.php (dev/test seeders)
  - routes/api.php (namespaced V1 routes)
  - config/*.php (sanctum, permission, queue, mail, logging)
  - app/Console/Kernel.php (scheduler equivalents of cron_script tasks)
  - app/Jobs/*.php (async tasks for PDF/Excel generation, analytics)
  - tests/Feature/*.php and tests/Unit/*.php
- vue-frontend/ (Vue 3 + Vite + TS)
  - src/main.ts, src/App.vue
  - src/router/index.ts (route tree for portal/unity/admin)
  - src/store/index.ts (Pinia) with modules per domain
  - src/api/http.ts (Axios instance with interceptors)
  - src/api/*.ts (typed client per API group)
  - src/components/* (shared UI)
  - src/views/*
    - Admissions/*, Registrar/*, Finance/*, Schedule/*, Classroom/*, Messages/*, Portal/*, Unity/*, Reservations/*, Deficiencies/*, Analytics/*
  - src/types/*.d.ts or *.ts (DTOs/interfaces matching backend)
  - src/styles/* (migrate AdminLTE theme selectively or adopt modern UI library)
  - tests/unit/* (Vitest) and e2e/* (Cypress)
Existing files to be modified (in legacy project for coexistence):
- .htaccess / web server config to route new /api and /app (SPA) paths to Laravel and Vue respectively while keeping legacy routes functioning.
- application/config/routes.php to optionally proxy specific new endpoints to Laravel during strangler phase (or handle via web server vhost/nginx).
Files to be moved/deleted:
- None during phase 1 to ensure rollback safety; decommission legacy modules after cutover completion per module.
Configuration updates:
- New .env for Laravel (DB creds matching current MySQL), queue (redis), mail, storage disks.
- Vite/SPA environment variables (.env.[mode]) for API base URL and auth endpoints.

[Functions]
Replace legacy controller function logic with Laravel controllers/services while preserving behavior. Provide new REST endpoints grouped by domain.

New functions (examples; signatures representative):
- app/Http/Controllers/Api/V1/UsersController.php
  - login(LoginRequest): JsonResponse
  - logout(): JsonResponse
  - me(): JsonResponse
  - register(RegisterRequest): JsonResponse (admin-bound)
- AdmissionsController
  - listApplicants(FilterRequest): Paginator JSON
  - createApplicant(ApplicantRequest): JSON
  - scheduleExam(ApplicantExamRequest): JSON
  - getApplicationByCode(string $code): JSON
- RegistrarController
  - getCurriculum(int $programId): JSON
  - shiftStudent(ShiftRequest): JSON
  - generateStudentNumber(GenerateNumberRequest): JSON
  - enrollmentStatistics(FilterRequest): JSON
- FinanceController
  - generateOR(GenerateOrRequest): JSON
  - getTransactions(FilterRequest): JSON
  - printInvoice(PrintInvoiceRequest): Job dispatch + status
- ScheduleController
  - getScheduleByCode(string $code): JSON
  - getScheduleBySection(int $sectionId, string $sem): JSON
- ClassroomController
  - getClasslist(int $id): JSON
  - addToClasslist(AddClasslistRequest): JSON
  - transferClasslist(TransferRequest): JSON
- MessagesController
  - listThreads(): JSON
  - sendMessage(MessageRequest): JSON
- ReservationController
  - CRUD endpoints per schema
- DeficienciesController
  - CRUD endpoints per schema
- PdfController / ExcelController
  - Dispatch jobs and return signed download URLs
Modified functions:
- Legacy PDF/Excel builder functions ported into services (PdfService::studentViewer(), ExcelService::dailyEnrollmentReport(), etc.)
Removed functions:
- Legacy CodeIgniter controllers’ procedural helpers once feature is migrated; replaced by service methods with tests. Migration strategy includes a switch-over flag per module.

[Classes]
Introduce Laravel classes for domain controllers, models, policies, services, and jobs; maintain parity with legacy modules.

New classes (illustrative):
- Models: User, Student, Faculty, Program, Curriculum, Subject, Classlist, ClasslistMembership, Transaction, Message, Reservation, Deficiency, AiAnalysis
- Controllers: UsersController, AdmissionsController, RegistrarController, FinanceController, ScheduleController, ClassroomController, MessagesController, PortalController (read models for SPA), UnityAdminController (admin endpoints), ReservationController, DeficienciesController, AiAnalyticsController, PdfController, ExcelController
- Requests: LoginRequest, RegisterRequest, ApplicantRequest, GenerateOrRequest, ShiftRequest, EnrollmentRequests, ClasslistRequests, ReservationRequest, DeficiencyRequest
- Services: EnrollmentService, TuitionService, MessageService, PdfService, ExcelService, AnalyticsService, AdmissionsService
- Jobs: GeneratePdfJob, GenerateExcelJob, RunAnalyticsJob
- Policies: Model policies for role-based access using Spatie Permission
Modified classes:
- None in legacy; new equivalents supplant functionality.
Removed classes:
- Legacy controllers/models gradually retired as each module is cut over; rollback path documented per module.

[Dependencies]
Adopt modern, actively maintained packages for security, DX, and performance.

Backend (Composer):
- laravel/framework:^10|^11
- laravel/sanctum:^4 for SPA token auth
- spatie/laravel-permission:^6 for RBAC
- barryvdh/laravel-dompdf:^2 and/or tecnickcom/tcpdf if parity required
- maatwebsite/excel:^3 for Excel import/export
- predis/predis or php-redis for queues/cache; laravel/horizon optional
- laravel/telescope (dev), nunomaduro/collision, pestphp/pest or phpunit/phpunit
- league/flysystem-aws-s3-v3 (if cloud storage planned)
Frontend (npm):
- vue:^3, vue-router:^4, pinia:^2, axios:^1
- typescript, vite, vitest, cypress
- UI: bootstrap@5 + bootstrap-vue-next or primevue/naive-ui/tailwind (choose one)
Integration:
- CORS, CSRF handling (Sanctum SPA, same-site cookies if same origin), environment configs.

[Testing]
Implement multi-layer testing to prevent regressions during migration.

- Backend: Pest/PHPUnit Feature tests for all API endpoints, Policy tests for RBAC, Job tests for async work, Request validation tests, Contract tests matching OpenAPI spec.
- Frontend: Vitest unit tests for components/stores, Cypress e2e for core workflows (login, enrollment, classlist ops, finance OR generation, messaging).
- Contract: Generate OpenAPI/Swagger docs (e.g., using Laravel OpenAPI) and validate with CI.
- Data migration validation: SQL checks to ensure counts and key aggregates match pre/post migration.

[Implementation Order]
Use a strangler pattern: stand up new Laravel API + Vue SPA alongside legacy, gradually redirect traffic per module. Maintain shared DB until final cutover.

1) Bootstrap projects
   - Create laravel-api (PHP 8.2), configure .env to point to current MySQL; enable Sanctum, Spatie Permission, queues, logging.
   - Create vue-frontend (Vue 3 + TS + Vite), configure base API URL, auth flows.
2) Authentication and RBAC
   - Implement Sanctum SPA auth; migrate/bridge password hashing (preserve legacy hashes; rehash on login).
   - Seed roles/permissions equivalent to legacy user levels; add Policies.
3) Read-only endpoints first (low-risk)
   - Portal data (student ledger, schedules, classlists, curriculum) as GET endpoints.
   - Admissions read endpoints (applicant status, exam info).
   - Unity admin dashboards (metrics) read endpoints.
4) Frontend read-only pages
   - Build views for portal and admin dashboards consuming new GET APIs.
   - Validate performance and data parity vs legacy.
5) Mutations by domain (module-by-module)
   - Admissions: applicant create/update, exam scheduling, registration.
   - Registrar: shifting, registration flows, student number generation.
   - Finance: OR generation, transactions posting, invoice printing (PDF job).
   - Classroom: classlist CRUD, transfers, add/drop.
   - Schedule: code/section queries, conflict checks.
   - Reservations and Deficiencies: CRUD + validation.
   - Messages: thread/message send/read.
6) PDFs/Excel
   - Port key PDFs and Excel exports to jobs + signed URLs; ensure layouts match legacy output.
7) AI Analytics
   - Recreate analysis pipeline; expose endpoints; migrate SQL and configs (application/modules/ai_analytics/sql/*).
8) Scheduler/Cron
   - Port cron_script tasks to Laravel scheduler; configure queues.
9) Progressive switchover
   - Route module endpoints from legacy to Laravel; feature flag per module; monitor logs/metrics.
10) Full cutover
   - Move SPA to default UI for end users; deprecate legacy routes.
   - Archive legacy code; keep DB migrations audited; write post-cutover docs.

Rollback strategy:
- Module-level toggle to revert to legacy endpoints.
- Database writes wrapped with transactions and idempotency where applicable.
- Logs/metrics dashboards to detect anomalies.

Assumptions and decisions to confirm:
- Use Sanctum for SPA auth (same-origin or first-party domain).
- Keep current DB schema names; adjust via Eloquent $table and guarded/fillable, then refactor schema in phase 2.
- UI library choice (Bootstrap/AdminLTE compatibility vs new system) — default Bootstrap 5 or PrimeVue.
- Migration approach: strangler (recommended). If big-bang is required, extend hardening and UAT phases.
