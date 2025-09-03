# TODO — Waive Application Fee flag on Applicant Details

Scope:
- Add a "Waive Application Fee" toggle on Applicant Details page allowing admissions/admin to check/uncheck.
- Persist to tb_mas_applicant_data: waive_application_fee (bool), waive_reason (nullable), waived_at (nullable), waived_by_user_id (nullable, no FK).
- Surface fields in API and wire UI to update.

Tasks:
1) Migration
   - Create migration: 2025_09_03_001100_add_waive_flags_to_tb_mas_applicant_data.php
   - Columns:
     - waive_application_fee boolean default false not null
     - waive_reason string(255) nullable
     - waived_at timestamp nullable
     - waived_by_user_id unsignedInteger nullable (no FK)
   - Down(): drop columns if exist
   - [ ] Implement
   - [ ] Run php artisan migrate

2) Backend changes
   - ApplicantUpdateRequest.php
     - Add rules:
       - waive_application_fee => sometimes|boolean
       - waive_reason => sometimes|nullable|string|max:255
   - ApplicantController@show
     - Surface waive_application_fee, waive_reason, waived_at from latest tb_mas_applicant_data
   - ApplicantController@update
     - If waive_application_fee present:
       - Update latest row columns:
         - set waive_application_fee to provided boolean
         - if true and was false: set waived_at = now(), waived_by_user_id from X-Faculty-ID header if present; keep waive_reason if sent
         - if false: clear waived_at and waived_by_user_id; if waive_reason sent empty, clear it; otherwise keep as-is
   - [ ] Implement request validation
   - [ ] Implement show() surfacing
   - [ ] Implement update() handling

3) Frontend changes
   - applicants.controller.js (ApplicantViewController)
     - Inject RoleService
     - Store on load: vm.waive_application_fee, vm.waive_reason, vm.waived_at
     - vm.canEditWaiver = RoleService.hasAny(['admissions','admin'])
     - vm.waiver object + vm.savingWaiver + vm.saveWaiver() calling ApplicantsService.update(id, {...})
   - view.html
     - Under "Application Data", add UI:
       - Checkbox "Waive Application Fee" bound to vm.waiver.waive_application_fee
       - Reason input visible when checked
       - Save button (admissions/admin only)
       - Note when waived: "Waived at {{ vm.waived_at | date:'medium' }}"
   - [ ] Implement controller changes
   - [ ] Implement template changes

4) Testing
   - [ ] Load Applicant Details; verify initial state
   - [ ] Toggle on with reason; Save; verify DB + UI reflect values
   - [ ] Toggle off; Save; verify waived_at/by cleared; reason behavior per spec
   - [ ] Verify role gating (non-admissions/admin cannot edit)

Decisions:
- Unchecking does NOT auto-clear waive_reason unless client sends empty string (keeps historical note unless explicitly cleared).
- waived_by_user_id is not FK due to prior FK issues on this table.

Follow-ups (out of scope):
- Integrate waiver flag in fee computation and invoice generation flows.
