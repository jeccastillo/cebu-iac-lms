# Laravel API Roles Implementation - TODO

Scope:
Implement database-backed roles with APIs and middleware enforcement, without breaking existing read endpoints.

Tasks:

1) Database schema
- [ ] Migration: create table tb_mas_roles
- [ ] Migration: create table tb_mas_user_roles (pivot)
- [ ] Seeder: RoleSeeder with baseline roles
- [ ] Wire DatabaseSeeder to call RoleSeeder

2) Models and relationships
- [ ] Model: App\Models\Role
- [ ] Update App\Models\User: roles() relation, hasRole()/hasAnyRole() helpers

3) Middleware
- [ ] Middleware: App\Http\Middleware\RequireRole
- [ ] Register middleware alias 'role' in App\Http\Kernel

4) Controllers and Routes
- [ ] Controller: App\Http\Controllers\Api\V1\RoleController
  - [ ] GET /api/v1/roles
  - [ ] POST /api/v1/roles (admin)
  - [ ] PUT /api/v1/roles/{id} (admin)
  - [ ] DELETE /api/v1/roles/{id} (admin)
  - [ ] GET /api/v1/users/{id}/roles
  - [ ] POST /api/v1/users/{id}/roles (admin)
  - [ ] DELETE /api/v1/users/{id}/roles/{roleId} (admin)
- [ ] Update routes/api.php
  - [ ] Add routes above
  - [ ] Add role middleware to write endpoints (minimal enforcement):
    - [ ] Campus POST/PUT/DELETE -> role:admin
    - [ ] TuitionYear write routes -> role:registrar,admin
    - [ ] Curriculum writes -> role:registrar,admin
    - [ ] Subject writes -> role:registrar,admin
    - [ ] Scholarship writes -> role:scholarship,admin

5) Verification
- [ ] Provide example curl/PowerShell scripts
- [ ] Document migration/seed steps

Notes:
- User primary key: intID; legacy users table: tb_mas_users
- No timestamps on legacy tables
- Dev/test path to identify user: auth()->id() or X-User-ID header (development only)
