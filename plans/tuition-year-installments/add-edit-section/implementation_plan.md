# Implementation Plan

[Overview]
Add an Installment Plans admin section to the Tuition Year add/edit page so Finance can create and edit tuition-year-specific installment plans (code, label, DP rules, increase percent, count, ordering, activation, level). Enforce a server-side guard that prevents deletion of an installment plan when it is referenced by any student registration.

This implements management UI for dynamic installment plans alongside Misc/Lab/Tracks/Programs/Electives that already exist on the Tuition Year editor. It also introduces a strict backend constraint: an installment plan row cannot be deleted when it is “in use” (referenced by tb_mas_registration.tuition_installment_plan_id). The UI mirrors the existing editing patterns used across other related entities, respects the tuition year Finalized state (read-only), and integrates with existing REST endpoints: list via GET /tuition-years/{id}/installments; create/update via submit-extra/edit-type with type=installment; and delete via delete-type with type=installment (now guarded).

[Types]
Introduce typed shapes for Installment Plan payloads in frontend controller and a count metadata in backend list to improve UX for delete availability.

- Backend DB table: tb_mas_tuition_year_installment (already present)
  - id: int, PK, auto-increment
  - tuitionyear_id: int, required, FK to tb_mas_tuition_year.intID
  - code: varchar(32), required, unique per tuitionyear_id
  - label: varchar(64), required
  - dp_type: enum('percent','fixed'), required, default 'percent'
  - dp_value: decimal(10,2), required, >= 0; if dp_type='percent', must be 0..100
  - increase_percent: decimal(5,2) >= 0
  - installment_count: tinyint unsigned, >= 1 (0 means “all in DP” but we’ll bound to 1..12 in UI)
  - is_active: tinyint(1), 0/1
  - sort_order: int
  - level: varchar(10) nullable: 'college' | 'shs' | 'both' | null
  - timestamps (if present in schema)
- Backend response shape (GET /tuition-years/{id}/installments)
  - Array<InstallmentPlanRow>, sorted by sort_order, code
  - InstallmentPlanRow:
    - id: number
    - tuitionyear_id: number
    - code: string
    - label: string
    - dp_type: 'percent' | 'fixed'
    - dp_value: number
    - increase_percent: number
    - installment_count: number
    - is_active: 0|1
    - sort_order: number
    - level: 'college' | 'shs' | 'both' | null
    - used_count?: number  // Optional enhancement: count of registrations referencing this plan
- Frontend form shape (AngularJS; documentation-only)
  - vm.installments: InstallmentPlanRow[]
  - vm.local.installment: { code, label, dp_type, dp_value, increase_percent, installment_count, is_active, sort_order, level }
  - vm.local.installmentEdit: same fields for inline edit
  - vm.editing.installmentId: number | null
  - Validation rules:
    - code: required, 2..32 chars, [A-Za-z0-9_-]
    - label: required, 1..64 chars
    - dp_type: 'percent' | 'fixed'
    - dp_value: number; when percent, 0..100; when fixed, >= 0
    - increase_percent: number >= 0
    - installment_count: integer 1..12
    - sort_order: integer (default 0)
    - is_active: 0/1
    - level: '', 'college', 'shs', 'both'

[Files]
Modify Tuition Year FE and Backend delete guard; no deletions or moves.

- New files: none

- Existing files to be modified:
  - frontend/unity-spa/features/tuition-years/tuition-years.service.js
    - Add listInstallments(tuitionYearId): GET /tuition-years/{id}/installments
  - frontend/unity-spa/features/tuition-years/tuition-years.controller.js
    - TuitionYearEditController: state + methods for Installment Plans
    - Integrate loading, add, edit, delete flows using TuitionYearsService.addExtra/updateExtra/deleteExtra with type=installment
  - frontend/unity-spa/features/tuition-years/edit.html
    - Add new “Installment Plans” section (table + inline edit + add form)
    - Respect vm.form.final read-only gating (hide add/edit/delete when final=1)
    - Optionally disable Delete button when row.used_count > 0
  - laravel-api/app/Http/Controllers/Api/V1/TuitionYearController.php
    - Strengthen deleteType(): for type=installment, refuse delete when plan is used by any registration
    - Optional enhancement: add used_count in installments() listing via subquery for better UX

- Files to be deleted or moved: none

- Configuration file updates: none

[Functions]
Add one FE service function; add several controller functions; adjust backend delete logic.

- New frontend functions
  - TuitionYearsService.listInstallments(tuitionYearId: number): Promise<{success:boolean, data:InstallmentPlanRow[]}>
    - In tuition-years.service.js
    - Purpose: fetch active plans for a tuition year
  - TuitionYearEditController.loadInstallments(): void
    - Fetch via listInstallments(vm.id) and assign to vm.installments
  - TuitionYearEditController.addInstallment(item): void
    - POST /tuition-years/submit-extra with type=installment and FK tuitionyear_id
    - Coerce/validate fields; on success reload installments
  - TuitionYearEditController.startEditInstallment(row): void
    - Clone fields into vm.local.installmentEdit and set vm.editing.installmentId
  - TuitionYearEditController.cancelEditInstallment(): void
    - Clear editing state
  - TuitionYearEditController.updateInstallment(id): void
    - POST /tuition-years/edit-type with xtype=installment, id, payload [code,label,dp_type,dp_value,increase_percent,installment_count,is_active,sort_order,level]
    - On success, clear editing and reload
  - TuitionYearEditController.deleteInstallment(id): void
    - POST /tuition-years/delete-type with type=installment, id
    - On 422 error “in use” show Swal error message and do not remove from UI; otherwise reload on success

- Modified frontend functions
  - TuitionYearEditController.loadAll(): void
    - After loading base TY and other related entities, also call loadInstallments()

- Backend modifications
  - TuitionYearController::deleteType(Request $request)
    - If type=installment:
      - Verify no tb_mas_registration row exists where tuition_installment_plan_id = id
      - If exists: 422 { success:false, message:'Cannot delete: installment plan is in use by X registration(s).' }
      - Else proceed with delete
  - TuitionYearController::installments($id) [optional enhancement]
    - Include used_count via left join/subquery for each plan id:
      - used_count = count(*) from tb_mas_registration where tuition_installment_plan_id = plan.id
    - This is optional; UI can rely on backend guard and display delete errors gracefully

- Removed functions: none

[Classes]
No new classes; minor controller extension.

- New classes: none

- Modified classes:
  - App\Http\Controllers\Api\V1\TuitionYearController
    - deleteType(): add usage guard for type=installment
    - installments(): optionally add used_count in select

- Removed classes: none

[Dependencies]
No new external packages or composer/npm changes.

- Frontend: AngularJS (existing)
- Backend: Laravel DB facade (existing)

[Testing]
Add targeted tests around CRUD and deletion guard; manual UI verification on the editor.

- Backend/API tests:
  - Create TU (POST /tuition-years/add) → newid
  - Add installment plan via submit-extra
  - Edit plan via edit-type (all fields)
  - Attempt delete when unused → OK
  - Create registration referencing plan (set tb_mas_registration.tuition_installment_plan_id = plan.id through existing RegistrationService flow or DB seed)
  - Attempt delete when used → 422 with proper message
  - List installments returns updated rows; if used_count included, verify correctness

- Frontend/UI tests:
  - Tuition Year Edit page loads Installment Plans section with table
  - Add new plan; plan appears in list
  - Inline edit updates row; values persist after reload
  - Finalized TY (final=1): Add/Edit/Delete controls hidden/disabled; list still visible
  - Delete:
    - When unused: succeeds and UI reloads without the row
    - When used: server returns 422; Swal shows message; row remains
  - Validation:
    - dp_type toggles percent/fixed; dp_value bounded accordingly
    - installment_count bounded [1..12]; increase_percent >= 0
    - code required and unique per tuitionyear_id (server may 500 on dup; show graceful error)

[Implementation Order]
Implement backend guard first to protect data, then UI additions.

1. Backend: Add usage guard in TuitionYearController::deleteType for type=installment (block delete if referenced in tb_mas_registration). Optionally enrich installments() with used_count.
2. Frontend Service: tuition-years.service.js
   - Add listInstallments(tuitionYearId) calling GET /tuition-years/{id}/installments.
3. Frontend Controller: tuition-years.controller.js (TuitionYearEditController)
   - Add state: vm.installments, vm.editing.installmentId, vm.local.installment, vm.local.installmentEdit
   - Add methods: loadInstallments, addInstallment, startEditInstallment, cancelEditInstallment, updateInstallment, deleteInstallment
   - Integrate loadInstallments() into loadAll() chain.
4. Frontend Template: tuition-years/edit.html
   - Add “Installment Plans” section:
     - Table columns: Code, Label, DP Type, DP Value, Increase %, Installments, Level, Sort, Active, Actions
     - Inline edit row respecting final state (disabled when final=1)
     - Add form respecting final state
     - Optionally disable Delete button when row.used_count > 0 (if provided by API)
5. Manual QA:
   - Exercise CRUD flows on the editor
   - Verify 422 deletion error path when plan is used
   - Verify finalized gating and input validation UX
   - Sanity-check Cashier/Enlistment pages still render (no changes in those modules in this scope)
