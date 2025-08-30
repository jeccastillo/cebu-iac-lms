# Implementation Plan

[Overview]
Refactor the existing Registrar Registration Viewer into a Finance-aligned Cashier view under the /finance namespace with proper role-based editing restrictions and updated navigation.

This refactor relocates, renames, and slightly augments the current registration viewer UI to become a dedicated Cashier view. Functionality remains the same (student selection, registration options, tuition breakdown, payment details, ledger, records) but is now positioned under Finance, with Finance/Admin users having edit permissions and Registrar having read-only access. No backend API changes are required; we will reuse existing endpoints and client services (UnityService, TermService, TuitionYearsService, StudentsService, Tuition endpoints). The new route path will be /finance/cashier/:id. There will be no backward-compatibility redirect from the old Registrar path.

[Types]  
Type system changes introduce a simple, controller-level capability flag to enforce edit vs. read-only behavior based on roles; no global TypeScript/Flow is involved.

- Controller VM state (approximate pseudo-types for clarity):
  - vm.state: { loggedIn: boolean, username: string, loginType: 'faculty'|'student'|string, roles?: string[], faculty_id?: number }
  - vm.id: number (route param student id)
  - vm.sn: string|null (student_number)
  - vm.term: number|null (selected term ID)
  - vm.student: object|null (shape from GET /students/{id})
  - vm.registrationResp: { success?: boolean, data?: { exists: boolean, registration?: object } }|null
  - vm.registration: {
      paymentType?: 'full'|'partial',
      tuition_year?: number,
      allow_enroll?: 0|1,
      downpayment?: 0|1,
      intROG?: 0|1|2|3|4,
      intAYID?: number
    }|null
  - vm.tuition: TuitionBreakdownResource|any
  - vm.tuitionSaved: { id: number, payload: TuitionBreakdownResource, created_at?: string }|null
  - vm.paymentDetails: { items: any[], meta?: { total_paid_filtered?: number }, sy_label?: string }|null
  - vm.ledger: { transactions?: { amount: number, posted_at?: string, or_no?: string }[], meta?: { amount_paid?: number } }|null
  - vm.records: { records?: any[], terms?: { term?: string, records: any[] }[] }|null
  - vm.edit: {
      paymentType: 'full'|'partial'|null,
      tuition_year: number|null,
      allow_enroll: 0|1|null,
      downpayment: 0|1|null,
      intROG: 0|1|2|3|4|null
    }
  - vm.canEdit: boolean (derived via RoleService.hasAny(['finance','admin']))
  - vm.selectedTuitionAmount: number|null
  - vm.meta: { amount_paid: number|null, remaining_amount: number, tuition_source: 'computed'|'saved' }
  - vm.ui: { showTuitionModal: boolean }

Validation rules (UI-level):
- paymentType: one of 'full' or 'partial'
- tuition_year: integer ID present in tuitionYearOptions
- allow_enroll, downpayment: 0 or 1
- intROG: integer in [0..4]

[Files]
We will create a new Cashier feature folder, move/rename the viewer controller and template, register a new route, update navigation, and enforce role-based edit flags.

- New files to be created:
  - frontend/unity-spa/features/finance/cashier-viewer/cashier-viewer.controller.js
    - Purpose: AngularJS controller renamed to CashierViewerController; content primarily sourced from registration-viewer.controller.js with minimal changes: inject RoleService, compute vm.canEdit, guard update paths and UI interactions when read-only.
  - frontend/unity-spa/features/finance/cashier-viewer/cashier-viewer.html
    - Purpose: Template cloned from registration-viewer.html, adjusted for controller binding and read-only UI states (disable inputs and Save button if !vm.canEdit), ensure labels remain appropriate for Cashier.

- Existing files to be modified:
  - frontend/unity-spa/core/routes.js
    - Add route: /finance/cashier/:id → templateUrl features/finance/cashier-viewer/cashier-viewer.html, controller CashierViewerController, requiredRoles: ['finance', 'registrar', 'admin'] (to allow Registrar read-only access).
    - Remove the old /registrar/registration/:id route entry (no backward compatibility redirect).
  - frontend/unity-spa/shared/components/sidebar/sidebar.html
    - Replace the Registration Viewer link pointing to #/registrar/registration/0 with new Cashier link #/finance/cashier/0.
    - Update visibility gating to vm.canAccess('/finance/cashier').
    - Update active-class checks to /finance/cashier.
  - frontend/unity-spa/features/unity/unity.service.js
    - No code changes. Listed here for awareness only (endpoints used as-is).
  - frontend/unity-spa/core/roles.constants.js
    - No change required to ACCESS_MATRIX since route.requiredRoles has priority. Keep '^/finance/.*$' entry as-is so other Finance routes remain Finance/Admin-only.

- Files to be deleted or moved
  - Do not delete existing Registrar viewer files to preserve history; instead, the code is duplicated into the new Cashier viewer location. If desired in a later cleanup task, we can remove the Registrar viewer files once the team confirms no longer needed.
  - Alternatively (optional future task): replace Registrar link with a clear message or remove the route entirely to prevent usage.

- Configuration updates
  - None required.

[Functions]
We keep most functions intact but add role-based guards and rename the controller.

- New functions:
  - CashierViewerController.canEdit (boolean property, computed once at bootstrap from RoleService.hasAny(['finance','admin']))
    - File: frontend/unity-spa/features/finance/cashier-viewer/cashier-viewer.controller.js
    - Purpose: Feature gating for edit actions.

- Modified functions:
  - updateRegistration (existing)
    - File: cashier-viewer.controller.js
    - Change: early-return if !vm.canEdit to enforce read-only for Registrar; also ensure Save UI honors vm.canEdit.
  - onOptionChange (existing)
    - File: cashier-viewer.controller.js
    - Change: guard: if !vm.canEdit return; prevents firing update flows for read-only roles.
  - Controller bootstrap (existing sequence)
    - File: cashier-viewer.controller.js
    - Change: inject RoleService and compute vm.canEdit at start; otherwise logic unchanged.
  - UI bindings in cashier-viewer.html
    - Disable controls and Save button when !vm.canEdit.
    - Ensure "View Breakdown" stays enabled for all roles if breakdown exists.

- Removed functions:
  - None.

[Classes]
AngularJS controllers are function-based; we are introducing a new named controller to reflect the feature move.

- New classes:
  - CashierViewerController (AngularJS controller)
    - File: frontend/unity-spa/features/finance/cashier-viewer/cashier-viewer.controller.js
    - Inherits: none (function-based)
    - Key methods: loadStudent, loadStudents, loadRegistration, loadTuition, loadLedger, loadPaymentDetails, loadRecords, updateRegistration, onOptionChange, refreshTuitionSummary, tuitionPayload, installmentPreview, openTuitionModal, closeTuitionModal.

- Modified classes:
  - N/A (renamed from RegistrationViewerController; retains methods with added guards).

- Removed classes:
  - None.

[Dependencies]
No new package dependencies.

- AngularJS services used remain the same: $http, $q, $rootScope, $scope, $timeout, $routeParams, $location, APP_CONFIG, StorageService, UnityService, TermService, TuitionYearsService, StudentsService, plus RoleService (new injection).
- Backend Laravel API endpoints unchanged.

[Testing]
Manual end-to-end testing across role types with existing local dev stack.

- Scenarios:
  1) Finance user (or Admin):
     - Navigate to #/finance/cashier/0 → select a student and term → verify registration options are enabled; making changes triggers updateRegistration and tuition-save flow; saved tuition snapshot source label flips to Saved when available.
     - Verify Amount Paid and Remaining compute correctly via payment details and ledger fallbacks.
  2) Registrar user:
     - Navigate to #/finance/cashier/0 → verify controls (Payment Type, Tuition Year, Allow Enroll, Downpayment, ROG Status, Save) are disabled and no update requests are sent; read-only viewing works (tuition, payments, ledger, records).
  3) Sidebar Navigation:
     - Cashier link now points to #/finance/cashier/0; visibility follows canAccess('/finance/cashier'). Active state highlights when at /finance/cashier route.
  4) Route Guarding:
     - Confirm Registrar can access /finance/cashier/:id due to route.requiredRoles overriding ACCESS_MATRIX.
     - Confirm non-authorized roles cannot access the page (e.g., faculty-only without admin/registrar/finance).
  5) Regression:
     - Old Registrar path /registrar/registration/:id is removed. Ensure no menu/link still references it. If attempting to navigate, user should not find a matching route (intended by design; no redirect per requirement).

[Implementation Order]
We will implement changes in a sequence to keep the app running and easy to verify.

1) Create new files under features/finance/cashier-viewer/:
   - cashier-viewer.controller.js (copy contents from RegistrationViewerController, rename controller, inject RoleService, add vm.canEdit and guards)
   - cashier-viewer.html (copy template, change controller binding if necessary via route; add ng-disabled/guards tied to vm.canEdit)
2) Update routes.js:
   - Add .when('/finance/cashier/:id', { templateUrl: 'features/finance/cashier-viewer/cashier-viewer.html', controller: 'CashierViewerController', controllerAs: 'vm', requiredRoles: ['finance', 'registrar', 'admin'] })
   - Remove the .when('/registrar/registration/:id', ...) block (no backward compatibility).
3) Update sidebar.html:
   - Change the Registration Viewer link to: href="#/finance/cashier/0", ng-if to vm.canAccess('/finance/cashier'), and active checks to /finance/cashier.
4) Verify Role Access:
   - No changes to roles.constants.js required (route.requiredRoles takes priority). Confirm with RoleService.canAccess logic.
5) Verify UI Read-only:
   - Ensure selections and Save button are disabled (ng-disabled="!vm.canEdit"); ensure onOptionChange respects vm.canEdit.
6) Run manual tests across roles outlined above.
