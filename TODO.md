# Tuition Year Installment Plans - Implementation TODO

- [ ] Step 1: Add migration to create `tb_mas_tuition_year_installment` table
- [ ] Step 2: Add migration to add `tuition_installment_plan_id` to `tb_mas_registration`
- [ ] Step 3: Create Eloquent model `App\Models\TuitionYearInstallment`
- [ ] Step 4: Add `App\Services\InstallmentPlanService` with `listByTuitionYear` and `computePlan`
- [ ] Step 5: Update `UnityRegistrationUpdateRequest` to accept `tuition_installment_plan_id`
- [ ] Step 6: Update `RegistrationService` to persist `tuition_installment_plan_id` and validate ownership to registration's `tuition_year`
- [ ] Step 7: Extend `TuitionYearController` with `installments()` endpoint and support for `type=installment` in `submitExtra`/`editType`/`deleteType`
- [ ] Step 8: Update `TuitionCalculator` to add `computeInstallmentForPlan` and keep legacy `computeInstallments` for compatibility
- [ ] Step 9: Update `TuitionService::compute` to populate `summary.installments` (plans + `selected_plan_id`) and adjust invoice amount logic for partial using selected plan
- [ ] Step 10: Update `UnityController::regForm` PDF to output Full + first two plans (DP and N installments)
- [ ] Step 11: Frontend Cashier Viewer: add plan selector, render DP + N installment rows, persist registration change, “Use” controls per bucket
- [ ] Step 12: Frontend Enlistment: dynamic plan tabs, DP/fee/total per plan, update Total Due display
- [ ] Step 13: Seed default plans (`standard`, `dp50`, `dp30`) for existing tuition years (within migration)
- [ ] Step 14: Thorough testing: backend endpoints, UI flows, PDF output, and edge cases

## Testing Plan (Thorough)

- Backend/API:
  - [ ] GET `/api/v1/tuition-years/{id}/installments`
  - [ ] POST `/api/v1/tuition-years/submit-extra` with `type=installment`
  - [ ] POST `/api/v1/tuition-years/edit-type` with `xtype=installment`
  - [ ] POST `/api/v1/tuition-years/delete-type` with `type=installment`
  - [ ] PUT `/api/v1/unity/registration` accepts `tuition_installment_plan_id` and persists correctly
  - [ ] Tuition compute includes `summary.installments` with `plans` + `selected_plan_id`
  - [ ] Tuition invoice amount derivation for partial uses selected plan’s `total_installment`
  - [ ] Unity `/tuition-preview` and `/tuition-save` continue working

- UI:
  - [ ] Cashier Viewer: plan selector defaults, DP and Installments N, “Use” with invoice cap, persistence
  - [ ] Registrar Enlistment: dynamic plan tabs, DP/fee/total, Total Due switching
  - [ ] Registration Form PDF: Full + first two active plans, DP and N installment rows (cap 5)

- Edge Cases:
  - [ ] `dp_type` fixed vs percent; `dp_value` bounds; `increase_percent=0`
  - [ ] `installment_count` 1..12; amounts round/truncate 2 dp
  - [ ] SHS special rules, year level handling, no plans defined (legacy fallback)
  - [ ] Invalid plan id, inactive plan, plan not in tuition_year
  - [ ] PDF layout constraints for N installments and long labels
  - [ ] Cashier permissions, reservation offsets, remaining calculations
