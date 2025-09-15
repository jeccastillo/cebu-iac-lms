# Implementation Plan

[Overview]
Add a secure registrar-admin UI page and API to change a student's password, supporting either generating a temporary password or setting a specific one, with full audit logging.

This implementation introduces a registrar-facing page in the Unity SPA where an authorized registrar can search/select a student and either generate a random secure password or set a specific password. On the backend, a protected endpoint will update tb_mas_users.strPass using modern hashing, with SystemLog audit of the action. The UI will use the existing student listing API with q-based search for autocomplete and follow established role middleware patterns. The backend will avoid logging plaintext secrets and will only return a generated password one time to the registrar.

[Types]
New request/response payloads for the password change operation with strict validation.

Type definitions/specs:
- Request: RegistrarStudentPasswordUpdateRequest (JSON)
  - mode: string; required; enum: 'generate' | 'set'
  - new_password: string; required if mode == 'set'; minLength: 8; maxLength: 64
  - note: string; optional; free-text reason field to include in SystemLog new_values for auditability (no secrets)
- Path params:
  - id: integer; required; tb_mas_users.intID
- Response (JSON):
  - success: boolean
  - message: string
  - data?: object
    - student_id: number
    - mode: 'generate'|'set'
    - generated_password?: string (present only when mode == 'generate')
    - updated_at: string (ISO datetime)
- Logging redaction structure:
  - old_values: { strPass: 'REDACTED' }
  - new_values: { strPass: 'REDACTED', mode: 'generate'|'set', note?: string }

[Files]
Add a new request class, a controller method, a protected route, and new SPA feature module with route and menu entry.

Backend (Laravel):
- New files to be created:
  - laravel-api/app/Http/Requests/Api/V1/RegistrarStudentPasswordUpdateRequest.php
    - Purpose: Validate registrar password change input (mode, new_password), authorize via route middleware.
- Existing files to be modified:
  - laravel-api/app/Http/Controllers/Api/V1/StudentController.php
    - Add method updatePassword(RegistrarStudentPasswordUpdateRequest $request, int $id)
    - Import Str and Hash; use SystemLogService with redacted values
  - laravel-api/routes/api.php
    - Add POST /api/v1/students/{id}/password guarded by middleware('role:registrar,admin')
- Files to be deleted or moved: none
- Configuration updates: none (uses existing middleware 'role' and DB schema tb_mas_users)

Frontend (Unity SPA):
- New files to be created:
  - frontend/unity-spa/features/registrar/change-password/change-password.controller.js
    - Purpose: Page controller for selecting student, choosing mode (generate | set), submitting request, and showing result
  - frontend/unity-spa/features/registrar/change-password/change-password.service.js
    - Purpose: API client to call POST /api/v1/students/{id}/password
  - frontend/unity-spa/features/registrar/change-password/change-password.html
    - Purpose: View with student autocomplete, mode toggle, password input (for set), generate/submit UX and result display
- Existing files to be modified:
  - frontend/unity-spa/core/routes.js
    - Add state/route for registrar change password page (e.g., /registrar/change-password)
  - frontend/unity-spa/shared/components/sidebar/sidebar.html
    - Add menu link under Registrar section to the new page
- Files to be deleted or moved: none
- Configuration updates: none

[Functions]
Introduce a new StudentController action and corresponding FE service/controller functions.

Backend:
- New functions:
  - StudentController@updatePassword(RegistrarStudentPasswordUpdateRequest $request, int $id): JsonResponse
    - Purpose: Update student password by intID using mode-based behavior; log action; return generated password when applicable
- Modified functions: none (other controller methods remain unchanged)
- Removed functions: none

Frontend:
- New functions:
  - registrarChangePasswordService.changePassword(studentId: number, mode: 'generate'|'set', new_password?: string, note?: string): Promise
    - Purpose: Wraps POST /api/v1/students/{id}/password
  - RegistrarChangePasswordController.onSearch(query: string): Promise<Student[]>
    - Purpose: Debounced autocomplete calling GET /api/v1/students?q=...&amp;per_page=10
  - RegistrarChangePasswordController.submit(): void
    - Purpose: Validate form; call service; handle response; show generated password when present
- Modified functions:
  - Add route registration in routes.js (module config) and sidebar toggle; no function signatures changed
- Removed functions: none

[Classes]
Add a request validation class; reuse existing controller; no new models.

- New classes:
  - App\Http\Requests\Api\V1\RegistrarStudentPasswordUpdateRequest
    - Key methods: authorize() → true (route middleware enforces roles), rules() → { mode, new_password, note }
- Modified classes: none (StudentController is extended with a new method only)
- Removed classes: none

[Dependencies]
No additional packages are required.

- Laravel:
  - Use Illuminate\Support\Facades\Hash and Illuminate\Support\Str (already available)
  - Use existing SystemLogService for audit records
- Frontend:
  - Use existing AngularJS 1.x app modules/services, shared autocomplete directives (select2 or pui-autocomplete), and toast service
- No composer/npm additions or updates needed

[Testing]
Add API and UI validation scenarios and security checks.

- Backend:
  - cURL smoke tests:
    - Generate mode:
      curl -s -X POST "http://localhost/laravel-api/public/api/v1/students/123/password" -H "Content-Type: application/json" -H "X-User-Roles: registrar" -d "{\"mode\":\"generate\"}"
    - Set mode:
      curl -s -X POST "http://localhost/laravel-api/public/api/v1/students/123/password" -H "Content-Type: application/json" -H "X-User-Roles: registrar" -d "{\"mode\":\"set\",\"new_password\":\"Minimum8\"}"
  - Verify:
    - Success JSON with generated_password when mode=generate
    - Password changed: UsersController::auth-student should accept the new password
    - SystemLog entries created with redacted values
    - Role middleware rejects unauthorized roles (403)
  - Edge cases:
    - Non-existent student id → 404
    - Weak password (< 8) when mode=set → 422
    - Missing mode → 422
- Frontend:
  - Manual test flow:
    - Navigate via sidebar Registrar → Change Password
    - Search student via autocomplete (q-based), select entry
    - Toggle mode:
      - Generate → Submit → Show one-time password
      - Set specific → enter new password ≥ 8 chars → Submit → Show success and do not echo password back
    - Verify toasts for success/error
  - Security:
    - Ensure passwords aren’t logged in console/network inspection beyond necessary HTTPS request; no persistence in local storage
    - Generated password only displayed once; masked copy available

[Implementation Order]
Implement backend endpoint and FE UI iteratively; verify with smoke tests after each stage.

1) Backend: Add request validator RegistrarStudentPasswordUpdateRequest.php
2) Backend: Add StudentController@updatePassword and wire SystemLogService redacted logging
3) Backend: Register route POST /api/v1/students/{id}/password with middleware('role:registrar,admin')
4) Backend: Smoke test with cURL (generate and set)
5) Frontend: Create change-password.service.js and integrate API call
6) Frontend: Create change-password.controller.js and change-password.html with autocomplete, mode toggle, and submit
7) Frontend: Update core/routes.js to include /registrar/change-password and sidebar.html to add menu item under Registrar
8) Frontend: Manual test end-to-end (generate/set modes), confirm UI shows generated password one-time
9) Review and refine (copy-to-clipboard, loading states, validation messages)
10) Final QA: Role gating, logging entries, negative cases, and success flows
