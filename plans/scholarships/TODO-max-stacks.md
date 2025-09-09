# TODO — Max Stacks for Scholarships/Discounts

Derived from implementation_plan.md

## Scope
Add `max_stacks` to `tb_mas_scholarships` (default 1), expose in SPA add/edit form, and enforce cap in assignment creation for exact same scholarship stacking within a term.

## Checklist

- [ ] Step 1: Create migration to add `max_stacks` column to `tb_mas_scholarships`
  - File: `laravel-api/database/migrations/2025_09_10_000200_add_max_stacks_to_tb_mas_scholarships.php`
  - Up: add `TINYINT UNSIGNED NOT NULL DEFAULT 1` if column missing
  - Down: drop column if exists

- [ ] Step 2: Backend validation
  - [ ] Update `laravel-api/app/Http/Requests/Api/V1/ScholarshipStoreRequest.php`
    - Add rule: `max_stacks` => `sometimes|integer|min:1|max:255`
  - [ ] Update `laravel-api/app/Http/Requests/Api/V1/ScholarshipUpdateRequest.php`
    - Add rule: `max_stacks` => `sometimes|integer|min:1|max:255`

- [ ] Step 3: Resource serialization
  - [ ] Update `laravel-api/app/Http/Resources/ScholarshipResource.php`
    - Include `max_stacks` in `toArray()` output

- [ ] Step 4: Service updates
  - [ ] `App\Services\ScholarshipService::list` — include `max_stacks` in mapped output
  - [ ] `normalizePayload` — whitelist `max_stacks`
  - [ ] `mapModel` — include `max_stacks` (int)
  - [ ] `assignmentUpsert` — enforce max-stacks cap for (student_id, syid, discount_id)
    - Count existing rows; if count >= cap, throw `InvalidArgumentException`
    - If count < cap, insert additional row (even if one exists already)
    - Preserve mutual exclusion and referrer validations

- [ ] Step 5: Frontend controller changes
  - File: `frontend/unity-spa/features/scholarship/scholarships/scholarships.controller.js`
    - [ ] Add `vm.form.max_stacks` default to `1` in initial form state and `openCreate()`
    - [ ] Pre-fill `max_stacks` in `openEdit(row)`
    - [ ] Include `max_stacks` in `submitForm()` payload (ensure integer conversion and min=1)

- [ ] Step 6: Frontend template changes
  - File: `frontend/unity-spa/features/scholarship/scholarships/list.html`
    - [ ] Add numeric input for “Max Stacks” (min=1, step=1) in the form modal
    - [ ] Bind validation error display (`vm.validation.max_stacks`)
    - [ ] (Optional) Show `max_stacks` in list view or tooltip

- [ ] Step 7: Migrate &amp; Thorough Testing
  - [ ] Run `php artisan migrate` inside `laravel-api`
  - [ ] DB verify: column exists, default=1, non-null
  - [ ] API tests:
    - [ ] POST /scholarships: accepts `max_stacks`; rejects invalid values
    - [ ] PUT /scholarships/{id}: updates `max_stacks`
    - [ ] GET /scholarships and /scholarships/{id}: include `max_stacks`
    - [ ] POST /scholarships/assignments: honors cap (1 blocks second; N allows N then blocks N+1), mutual exclusion/referrer validations hold
    - [ ] PATCH /scholarships/assignments/apply: no regressions
    - [ ] DELETE /scholarships/assignments/{id}: no regressions
    - [ ] Concurrency: two simultaneous assignment attempts at cap boundary
  - [ ] Frontend UI tests:
    - [ ] Scholarship Catalog modal shows “Max Stacks” default 1; edits persist; validation surfaced
    - [ ] Assignments UX shows cap-reached error from API

## Notes
- Default `max_stacks=1` preserves current behavior.
- No new dependencies required.
- Ensure error message for cap exceeded is descriptive: “This scholarship can only be assigned N time(s).”
