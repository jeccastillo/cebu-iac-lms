# Cebu IAC LMS - AI Coding Assistant Instructions

## Architecture Overview

This is a hybrid **Learning Management System** with three coexisting layers:

1. **Legacy CodeIgniter 3 + HMVC** - Root level (`index.php`, `application/modules/`)
2. **Modern Laravel 10 API** - `laravel-api/` subdirectory
3. **AngularJS SPA Frontend** - `frontend/unity-spa/` (Angular 1.x, not Angular 2+)

### Key Architectural Decisions

- **Dual-stack authentication**: CodeIgniter sessions bridge to Laravel via custom `CodeIgniterSessionGuard`
- **Shared database**: Both stacks access the same MySQL database with legacy table names (e.g., `tb_mas_users`, `tb_mas_faculty`)
- **XAMPP environment**: Runs under Apache with path `c:/xampp8/htdocs/iacademy/cebu-iac-lms/`
- **API-first for new features**: New functionality goes in Laravel API, legacy code remains in CodeIgniter

## Critical Developer Workflows

### Running the Application

```bash
# Laravel API (from laravel-api/ directory)
php artisan serve  # Typically port 8000

# Frontend expects API at: /iacademy/cebu-iac-lms/laravel-api/public/api/v1
# See frontend/unity-spa/core/baseRoot.js for dynamic path resolution
```

### Database Migrations

```bash
cd laravel-api
php artisan migrate              # Run pending migrations
php artisan migrate:rollback     # Rollback last batch
```

### Code Generation

```bash
# Laravel API scaffolding
php artisan make:model ModelName
php artisan make:controller Api/V1/ControllerName
php artisan make:request Api/V1/RequestName
php artisan make:resource ResourceName
```

## Project-Specific Conventions

### Laravel API Patterns

#### 1. Route Organization (`laravel-api/routes/api.php`)

- **All routes under `/api/v1` prefix**
- **Role-based middleware**: `->middleware('role:admin,registrar')` (comma-separated roles)
- **Literal routes before parameterized**: Place `/faculty/me` BEFORE `/faculty/{id}` to avoid collisions
- **Resource imports at end of controller list**: Organize large route files with controller imports at top

```php
// Correct ordering example
Route::get('/faculty/search', [FacultyController::class, 'index']);
Route::get('/faculty/me', [FacultySelfController::class, 'me']);
Route::get('/faculty/{id}', [FacultyController::class, 'show']);
```

#### 2. Authentication & User Context

**Multi-source user resolution** via `UserContextResolver` service:
1. Laravel Auth (session/sanctum)
2. CodeIgniter `$_SESSION` data
3. Request headers: `X-Faculty-ID`, `X-Student-ID`
4. Database session validation (last resort)

**Always use for logging**:
```php
use App\Services\SystemLogService;

SystemLogService::log($context, 'event_type', 'Event description', ['data' => $payload]);
```

#### 3. Model Conventions

- **Legacy tables have non-standard primary keys**: Set `protected $primaryKey = 'intID';` or `'id'` explicitly
- **No timestamps on legacy tables**: `public $timestamps = false;`
- **Fillable over guarded**: Explicitly whitelist with `protected $fillable = [...]`
- **User models**: `Faculty` (tb_mas_faculty), `Student` (tb_mas_users), `User` (tb_mas_users for non-student contexts)

Example legacy model:
```php
class Payee extends Model
{
    protected $table = 'tb_mas_payee';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = ['id_number', 'firstname', 'lastname'];
}
```

#### 4. Service Layer Pattern

All business logic in services (`laravel-api/app/Services/`):
- **CashierService**: Payment processing, OR/Invoice generation
- **FinanceService**: Ledger operations, billing calculations
- **SystemLogService**: Centralized audit logging with user context
- **UserContextResolver**: Multi-source authentication resolution

Controllers stay thin, delegating to services.

#### 5. PDF Generation

- Uses **barryvdh/laravel-snappy** (wkhtmltopdf wrapper)
- Views in `laravel-api/resources/views/pdf/`
- Logo path configured in `.env`: `APP_LOGO_PATH="c:/xampp8/htdocs/iacademy/cebu-iac-lms/assets/img/iacademy-logo.png"`

### AngularJS Frontend Patterns

#### 1. Module Structure (`frontend/unity-spa/`)

```
core/          - App initialization, routes, services
  app.module.js       - Main module definition
  routes.js           - Route configuration with role guards
  baseRoot.js         - Dynamic API base URL resolution
shared/        - Reusable directives, filters
features/      - Feature modules (registrar/, faculty/, students/, etc.)
```

#### 2. Routing & Role Guards

Routes defined in `core/routes.js` with `requiredRoles` property:
```javascript
.when("/registrar/reports", {
  templateUrl: "features/registrar/reports.html",
  controller: "ReportsController",
  controllerAs: "vm",
  requiredRoles: ["registrar", "admin"]
})
```

#### 3. API Communication

Base URL auto-computed in `baseRoot.js` from XAMPP path structure:
```javascript
window.API_BASE = "/iacademy/cebu-iac-lms/laravel-api/public/api/v1";
```

Controllers use `axios` for HTTP, stored in `window.API_BASE` or `api_url` globals.

### Payment Gateway Integration

- **Paynamics**: Primary gateway (webhook: `/api/v1/payments/webhook`)
- **BDO Pay**: Direct bank integration (`/api/v1/payments/webhook_bdo`)
- **Maya Pay**: E-wallet integration (`/api/v1/payments/webhook_maya`)
- **Webhook handlers**: `PaymentGatewayController.php` and `PaynamicsWebhookController.php` at root level (legacy CI controllers)

Convenience fees calculated in frontend, passed as `convenience_fee` param to payment creation.

## File Naming & Location Rules

### Laravel API
- Controllers: `laravel-api/app/Http/Controllers/Api/V1/ExampleController.php`
- Models: `laravel-api/app/Models/ModelName.php`
- Services: `laravel-api/app/Services/ServiceName.php`
- Migrations: `laravel-api/database/migrations/YYYY_MM_DD_HHMMSS_description.php`
- Requests: `laravel-api/app/Http/Requests/Api/V1/RequestName.php`
- Resources: `laravel-api/app/Http/Resources/ResourceName.php`

### AngularJS Frontend
- Controllers: `frontend/unity-spa/features/{module}/{name}.controller.js`
- Services: `frontend/unity-spa/features/{module}/{name}.service.js` or `frontend/unity-spa/core/{name}.service.js`
- Templates: `frontend/unity-spa/features/{module}/{name}.html`
- Directives: `frontend/unity-spa/shared/directives/{name}.directive.js`

## Implementation Planning

For complex features, create markdown plans in project root:
- `implementation_plan_{feature}.md` - Detailed specifications
- `TODO-{feature}.md` - Task tracking with progress checkboxes

See existing examples: `implementation_plan_cashier_admin.md`, `TODO-auth-bridge.md` (completed auth integration).

## Testing & Validation

- **Laravel tests**: `laravel-api/tests/` (PHPUnit)
- **Manual testing**: Update `laravel-api/tests/test-report.md` with test scenarios
- **No automated frontend tests**: Manual QA in browser

## Database Context

- **Legacy naming**: Tables prefixed `tb_mas_`, columns use camelCase (e.g., `intID`, `strFirstName`)
- **School years**: Reference table `tb_mas_sy` with format like "2024-2025 1st Sem"
- **Roles**: Faculty roles stored in `tb_roles` junction table, checked via `Faculty::hasAnyRole(['admin', 'registrar'])`

## Common Gotchas

1. **CodeIgniter HMVC**: Legacy modules load via `Modules::run('module/controller/method')` - don't refactor without testing
2. **AngularJS is Angular 1.x**: Use `angular.module('unityApp')`, NOT `@Component` decorators
3. **XAMPP path sensitivity**: Always use forward slashes in configs, even on Windows
4. **Dual authentication**: Changes to auth flow must update BOTH Laravel guard and CI session handling
5. **OR/Invoice numbering**: Generated by `CashierService` with campus-specific prefixes - do not modify manually

## Major Feature Modules

### 1. Enlistment & Registration
**Service**: `EnlistmentService.php`
- Handles student course enrollment with prerequisite/corequisite validation
- Operations: `add`, `drop`, `change_section` with atomic transaction handling
- Integrates with `PrerequisiteService` and `CorequisiteService`
- Validates slot availability and student eligibility per term
- Frontend: `frontend/unity-spa/features/registrar/enlistment/`

### 2. Grading System
**Service**: `GradingSheetService.php`
- Generates PDF grading sheets for midterm/final periods
- Calculates GWA (General Weighted Average) with include_gwa flag
- Supports multiple grading systems (5-point, 1.0-5.0, percentage)
- Frontend: `frontend/unity-spa/features/grading/`
- PDF Views: `laravel-api/resources/views/pdf/grading_sheet.blade.php`

### 3. Finance & Billing
**Services**: `CashierService`, `FinanceService`, `StudentBillingService`
- **OR/Invoice Generation**: Campus-specific numbering (CEBxxx format)
- **Student Billing**: Tuition fees, installment plans, extra charges
- **Payment Processing**: 
  - Manual cashier payments (OR, Invoice, None modes)
  - Online gateway integration (Paynamics, BDO, Maya)
  - Payment details stored in `payment_details` table with audit trail
- **Ledger Operations**: Balance calculations, payment applications, excess handling
- Frontend: `frontend/unity-spa/features/cashiers/`, `frontend/unity-spa/features/finance/`

### 4. Scholarship Management
**Service**: `ScholarshipService`, `DiscountScholarshipService`
- Two types: **scholarships** (in-house/external) and **discounts**
- Student scholarship assignments with percentage/fixed amount deductions
- Automatic billing adjustments when scholarships applied
- Scholarship rules validation (min GWA requirements, status checks)
- Frontend: `frontend/unity-spa/features/scholarship/`

### 5. Clinic & Health Records
**Service**: `ClinicHealthService`
- Health records for students and faculty (person_type: 'student'|'faculty')
- JSON fields: allergies, medications, immunizations, conditions
- Clinic visits with attachments and medical notes
- Idempotent create/update by person identifier
- Frontend: `frontend/unity-spa/features/clinic/`

### 6. Admissions
**Services**: Various in `laravel-api/app/Services/Admissions/`
- Application journey tracking (stages: applied, interviewed, accepted, etc.)
- Applicant requirements management with file uploads
- Interview scheduling and notes
- Previous school records
- Frontend: `frontend/unity-spa/features/admissions/`

### 7. Faculty Loading & Classlists
**Services**: `ClasslistService`, `ClasslistAttendanceService`, `ClasslistGradesImportService`
- Faculty assignment to subjects (classlists)
- Attendance tracking with date-based records
- Grade imports via Excel (midterm/prelim/final/overall)
- Classlist merging for combined sections
- Frontend: `frontend/unity-spa/features/classlists/`, `frontend/unity-spa/features/faculty/`

## Business Logic Patterns

### School Year (SY) Context
- School years stored in `tb_mas_sy` with format: "2024-2025 1st Sem"
- Referenced as `syid` or `term` throughout the system
- Most operations are scoped to a specific school year term

### Campus Scoping
- Multi-campus support (Makati, Cebu)
- Campus-specific OR/Invoice numbering prefixes
- Campus filters in reports and dashboards
- Campus stored in `tb_mas_campuses` (id, campus_name)

### Role-Based Access Control
- Roles: admin, registrar, faculty, cashier_admin, finance_admin, etc.
- Stored in `tb_roles` junction table linking to `tb_mas_faculty`
- Middleware checks: `->middleware('role:admin,registrar')`
- Frontend route guards: `requiredRoles: ["registrar", "admin"]`

## Key External Dependencies

- Laravel 10 + PHP 8.1
- MySQL (legacy schema with MyISAM tables)
- wkhtmltopdf (PDF generation)
- Maatwebsite/Excel (imports/exports)
- AngularJS 1.x + jQuery
- Tailwind CSS (CDN in frontend)

---

**For questions about specific features**, check implementation plans in root directory or `laravel-api/TODO-*.md` files. Many complex workflows are documented with detailed type specs and file change lists.
