# Implementation Plan

[Overview]
Introduce a configurable Tuition Year Installment Plans feature that replaces hardcoded 50% and 30% down payment schemes with dynamic plans defined per tuition year. Each plan defines down payment as percent or fixed, an increase percent applied to tuition/lab, and the number of installments. The backend will compute totals per plan and expose them to the frontend. The UI will render these plans in the Cashier view and the Registrar Enlistment tuition preview, and the Registration Form PDF will display Full plus the first two active plans by sort order. Cashiers can select an installment plan and a specific bucket (DP/Installment 1..N) when posting payments. The selected plan will be persisted on registration and used by Tuition computation and invoice generation.

This implementation aligns tuition handling across modules, eliminates rigid assumptions (30%/50%), and enables flexible institution-specific policies without code changes.

[Types]  
Add a new table for tuition-year-specific installment plans and a registration field to persist the chosen plan.

Data Structures:
- Table: tb_mas_tuition_year_installment
  - id: int, PK, auto-increment
  - tuitionyear_id: int, required, FK → tb_mas_tuition_year.intID, indexed
  - code: varchar(32), required, unique per tuitionyear_id (e.g., 'standard', 'dp50', 'dp30', 'customX')
  - label: varchar(64), required (e.g., 'Standard', '50% Down Payment', '30% Down Payment')
  - dp_type: enum('percent','fixed'), required
  - dp_value: decimal(10,2), required, >= 0 (percent 0..100)
  - increase_percent: decimal(5,2), required, >= 0 (applies to tuition and lab for this plan)
  - installment_count: tinyint unsigned, required, default 5, 1..12 typical
  - is_active: tinyint(1), default 1
  - sort_order: tinyint unsigned, default 0
  - level: enum('college','shs','both') nullable
  - timestamps
  - unique index: (tuitionyear_id, code)

- Registration column:
  - tb_mas_registration.tuition_installment_plan_id: int nullable, FK → tb_mas_tuition_year_installment.id
    - Used to select the plan used by TuitionService->compute for installments and by invoice amount derivation when paymentType='partial'.

Validation rules:
- dp_type='percent' ⇒ 0 ≤ dp_value ≤ 100
- dp_type='fixed' ⇒ dp_value ≥ 0
- increase_percent ≥ 0
- installment_count ≥ 1
- code unique per tuitionyear_id

[Files]
Introduce migrations and endpoints for installments; extend tuition computation; wire UI in Cashier, Enlistment, and PDF outputs.

Detailed breakdown:
- New files to be created:
  - laravel-api/database/migrations/YYYY_MM_DD_HHMMSS_create_tb_mas_tuition_year_installment.php
    - Creates tb_mas_tuition_year_installment per schema above.
  - laravel-api/database/migrations/YYYY_MM_DD_HHMMSS_add_tuition_installment_plan_id_to_registration.php
    - Adds tuition_installment_plan_id (FK nullable) to tb_mas_registration.
  - laravel-api/app/Models/TuitionYearInstallment.php
    - Eloquent model for tb_mas_tuition_year_installment (fillable, casts).
  - laravel-api/app/Services/InstallmentPlanService.php
    - Helper service to load plans, seed defaults, and compute DP/fees given totals and plan config (used by TuitionCalculator/TuitionService).
  - laravel-api/tests/Feature/TuitionYearInstallmentPlansTest.php (optional)

- Existing files to modify:
  - laravel-api/app/Http/Controllers/Api/V1/TuitionYearController.php
    - Add GET /api/v1/tuition-years/{id}/installments
    - Extend submitExtra/editType/deleteType to support type=installment (tuitionyear_id FK).
  - laravel-api/app/Http/Controllers/Api/V1/UnityController.php
    - In tuitionPreview/save/compute usage, TuitionService->compute should honor registration.tuition_installment_plan_id
    - In regForm(), dynamically render the first two active plans by sort_order in columns instead of fixed 50%/30%.
  - laravel-api/app/Services/TuitionService.php
    - Load plans for the student’s tuition_year
    - Compute and return summary.installments as:
      - selected_plan_id: int|null (from registration, fallback to first active)
      - plans: array of { id, code, label, increase_percent, installment_count, down_payment, installment_fee, total_installment }
      - legacy fields preserved: total_installment, total_installment30, total_installment50, down_payment(,30,50), installment_fee(,30,50) mapped to selected plan and seeded plans for backward compatibility
    - For tuition invoice amount:
      - When registration.paymentType == 'partial', use selected plan total_installment; fallback to first active plan; final fallback = legacy total_installment.
  - laravel-api/app/Services/TuitionCalculator.php
    - Add a method computeInstallmentForPlan(totals, plan, level, yearLevel) returning { down_payment, installment_fee, total_installment }
    - Refactor computeInstallments to:
      - Compute 'standard' legacy fields for backward compatibility (using tuition_year.installmentIncrease and registration/tables for dp defaults).
      - When plans exist, compute a per-plan result for all active plans via the new method.
    - Notes: Increase percent of a plan applies to tuition and lab only (consistent with legacy), misc/additional unchanged.
  - laravel-api/app/Services/RegistrationService.php
    - Accept tuition_installment_plan_id in updateByStudentNumberAndTerm (validate presence under the student's tuition_year).
  - laravel-api/app/Http/Requests/Api/V1/UnityRegistrationUpdateRequest.php
    - Add validation: 'fields.tuition_installment_plan_id' => ['sometimes','nullable','integer','exists:tb_mas_tuition_year_installment,id']
  - laravel-api/app/Services/Pdf/InvoicePdf.php
    - No structural changes; optional to annotate selected plan in footer metadata (minimal/no-op).
  - frontend/unity-spa/features/finance/cashier-viewer/cashier-viewer.controller.js
    - Load available plans (from tuition.summary.installments.plans) and populate UI
    - Add dropdown selector for plan (defaults to registration.tuition_installment_plan_id; fallback first active)
    - Add ability to select which bucket (DP or Installment 1..N) to apply in the payment form; keep “Use” convenience buttons
    - Update vm._recomputeInstallmentsPanel to use selected plan’s dp and per-installment fees; respect amount paid allocation logic
    - Update vm.updateRegistration/onOptionChange to persist tuition_installment_plan_id (if edited from Cashier)
  - frontend/unity-spa/features/finance/cashier-viewer/cashier-viewer.html
    - Add Plan selector (ng-options from vm.installmentPlans)
    - Render Installment Plan panel from selected plan with DP and N installment rows
    - Add bucket selection (DP/i1..iN) or inline “Use” buttons per row
  - frontend/unity-spa/features/registrar/enlistment/enlistment.controller.js
    - Replace fixed tabs (Standard/30%/50%) with dynamic tabs from tuition.summary.installments.plans (selected tab used for Total Due display)
  - frontend/unity-spa/features/registrar/enlistment/enlistment.html
    - Render tab buttons from plans; display DP, Per-Installment (×N), and Total per selected plan; update Total Due card accordingly
  - laravel-api/app/Http/Controllers/Api/V1/UnityController.php::regForm()
    - Replace hardcoded 50%/30% columns:
      - Keep FULL column
      - Use first two active plans by sort_order; compute Tuition/Lab increases and totals using plan.increase_percent and computed plan results
      - Down Payment row value = computed plan DP
      - Installment rows count = plan.installment_count (cap to 5 if strict space constraints; fill remaining with blanks if fewer)
  - frontend/unity-spa/services (if present): TuitionYearsService or UnityService
    - Ensure endpoint call to /tuition-years/{id}/installments if needed in any admin UI (optional for this scope)

- Files to be deleted or moved: None

- Configuration updates: None

[Functions]
Modify tuition compute paths and add new API endpoints to manage and expose plans.

Detailed breakdown:
- New functions:
  - App\Services\InstallmentPlanService
    - public function listByTuitionYear(int $tuitionYearId, ?string $level = null): array
    - public function computePlan(array $totals, array $plan, string $level, ?int $yearLevel): array
      - totals keys: tuition, lab, misc, additional, discount_total, scholarship_total
      - plan: { dp_type, dp_value, increase_percent, installment_count }
      - Algorithm:
        - increase = max(0, plan.increase_percent)/100
        - tuition_i = tuition * (1 + increase)
        - lab_i = lab * (1 + increase)
        - gross = tuition_i + lab_i + misc + additional
        - dp = (plan.dp_type === 'fixed') ? min(gross, dp_value) : gross * (dp_value/100)
        - fee = (gross - dp) / max(1, installment_count)
        - return { total_installment: round(gross,2), down_payment: round(dp,2), installment_fee: round(fee,2), count: installment_count }
  - TuitionYearController
    - public function installments($id)
      - Query tb_mas_tuition_year_installment where tuitionyear_id=$id and is_active=1 order by sort_order, code
  - TuitionYearController::submitExtra/editType/deleteType
    - Support type=installment (tuitionyear_id FK; PK is id)
    - Allowed fields for editType: ['code','label','dp_type','dp_value','increase_percent','installment_count','is_active','sort_order','level']

- Modified functions:
  - TuitionService::compute(student_number, syid, discountId, scholarshipId): array
    - Load registration (must exist) and tuition_year; read registration.tuition_installment_plan_id
    - Aggregate totals (tuition, misc, lab, additional, ds lines)
    - Load active plans for tuition_year via InstallmentPlanService
    - For each plan, compute results via InstallmentPlanService::computePlan
    - Select plan: if registration.tuition_installment_plan_id exists in list → selected; else first active
    - Construct summary.installments:
      - selected_plan_id: int|null
      - plans: array of { id, code, label, increase_percent, installment_count, down_payment, installment_fee, total_installment }
    - Backward compatibility:
      - summary.installments.total_installment = selected plan total_installment
      - For seeds dp30/dp50 present, expose total_installment30/50, down_payment30/50, installment_fee30/50
    - Invoice amount logic unchanged except base now uses selected plan’s total when paymentType='partial'
  - TuitionCalculator
    - Add computeInstallmentForPlan wrapper delegating to InstallmentPlanService::computePlan
    - Leave computeInstallments to fill legacy fields using seeded standard/30/50 mapping for BC.
  - RegistrationService::updateByStudentNumberAndTerm
    - Accept tuition_installment_plan_id and update registration with audit logging
    - Optional guard: ensure plan belongs to registration.tuition_year
  - UnityController::regForm
    - Replace fixed PDF columns to use first two active plans:
      - Map earlier “50%” column to first plan; “30%” to second
      - Use plan.increase_percent for Tuition and Lab lines; keep Misc/Other unchanged (consistency)
      - DP and per-installment rows come from computed plan results; use installment_count to draw the right number of rows (cap 5 visually)

- Removed functions: None

[Classes]
Add an Eloquent model and a utility service; extend existing services minimally.

Detailed breakdown:
- New classes:
  - App\Models\TuitionYearInstallment
    - protected $table = 'tb_mas_tuition_year_installment';
    - protected $primaryKey = 'id';
    - protected $fillable = ['tuitionyear_id','code','label','dp_type','dp_value','increase_percent','installment_count','is_active','sort_order','level'];
    - casts: ['dp_value' => 'decimal:2','increase_percent' => 'decimal:2']
  - App\Services\InstallmentPlanService
    - listByTuitionYear, computePlan (as above)

- Modified classes:
  - App\Http\Controllers\Api\V1\TuitionYearController
  - App\Services\TuitionService
  - App\Services\TuitionCalculator
  - App\Services\RegistrationService
  - App\Http\Requests\Api\V1\UnityRegistrationUpdateRequest
  - App\Http\Controllers\Api\V1\UnityController (regForm)

- Removed classes: None

[Dependencies]
No new external packages. Reuse Laravel DB/Eloquent and existing services. Maintain FPDI for PDFs. No composer.json or npm changes are required.

[Testing]
Adopt API and UI validations, focusing on dynamic plan rendering, persistence, and invoice values.

Test requirements:
- Backend:
  - Migrations create tables/columns as specified.
  - GET /tuition-years/{id}/installments returns seeded/default plans.
  - TuitionService->compute returns summary.installments with plans array and selected_plan_id; legacy fields still present.
  - Registration update accepts tuition_installment_plan_id; save; compute uses it for selected plan.
  - regForm PDF shows Full + first two plans; DP and per-installments match computed values.

- Frontend:
  - Cashier Viewer:
    - Plan selector appears and defaults to registration setting; “Use” buttons copy DP/Installment amounts; bucket selector works for N count.
    - Selected plan persists when changed (via registration update flow).
  - Registrar Enlistment:
    - Tabs render dynamically from plans; selecting a tab switches displayed Total Due to the plan total.
  - Regression:
    - When no plans exist, fallback to legacy Standard fields and still show amounts; UI degrades gracefully.

Validation strategies:
- Manual testing on sample students and terms (partial/full).
- Verify invoice amount selection for partial now uses selected plan’s total.
- Check PDF layout after plan changes to avoid overflow; cap installments to 5 lines in PDF for consistent layout.

[Implementation Order]
Implement schema and backend services first, compute and APIs next, then UI changes, and finally PDF changes.

1) Database
   - Create tb_mas_tuition_year_installment migration and add fk column to tb_mas_registration.
   - Backfill defaults:
     - Seed three plans per tuition_year (code/label/increase/dp): standard (dp=tuition_year.installmentDP or Fixed), dp50 (50%, 9%), dp30 (30%, 15%), count=5, active=1, sorted 0,1,2.
2) Models and Services
   - Add TuitionYearInstallment model.
   - Add InstallmentPlanService with listByTuitionYear and computePlan.
3) Tuition computation
   - Update TuitionService->compute: integrate InstallmentPlanService, selected plan handling, and summary.installments with plans array + legacy fields.
   - Update TuitionCalculator: add computeInstallmentForPlan helper and keep legacy computeInstallments for BC.
4) Controllers/Requests
   - TuitionYearController: add installments() endpoint; extend submitExtra/editType/deleteType for type=installment.
   - UnityRegistrationUpdateRequest: whitelist tuition_installment_plan_id.
   - RegistrationService: persist tuition_installment_plan_id with validation.
5) Frontend: Cashier
   - cashier-viewer.controller.js: load plans from tuition payload; add vm.selectedPlanId, vm.installmentPlans; recompute panel by selected plan; add bucket selector; persist registration change.
   - cashier-viewer.html: add plan dropdown; render N installment rows based on selected plan.
6) Frontend: Enlistment
   - enlistment.controller.js/html: switch from fixed Standard/30/50 tabs to dynamic plan tabs; update displayed totals.
7) PDF
   - UnityController::regForm: replace fixed 50%/30% columns with first two plans; draw DP and N installment rows; cap to 5 rows for layout.
8) QA
   - Validate end-to-end: compute, UI renders, payments “Use” amounts, registration persistence, invoice amounts, PDF output.
   - Verify BC: existing pages relying on legacy summary.installments fields still function.
