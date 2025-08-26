# Task: Gate Campus Selector to Admins, Add campus_id to tb_mas_sy, and Scope Terms by Campus

Status: In Progress

Objectives:
- Display the campus selector only for admin users.
- Add a campus_id column to tb_mas_sy and wire FK to tb_mas_campuses(id).
- Update /generic/terms and /generic/active-term to optionally filter by campus_id.
- Scope the frontend TermService to the selected campus and respond to campus changes.

Checklist:
1) Database
- [ ] Create migration: add campus_id to tb_mas_sy (nullable int, indexed, FK to tb_mas_campuses(id), SET NULL on delete)
- [ ] Run migration
- [ ] (Optional) Backfill campus_id as needed

2) Backend API
- [ ] Update GenericApiController@terms to accept campus_id query param and filter accordingly; include campus_id in payload
- [ ] Update GenericApiController@activeTerm similarly

3) Frontend UI
- [ ] Gate Campus Selector in sidebar to admins only (vm.hasRole('admin'))
- [ ] Update TermService to:
  - [ ] Inject CampusService
  - [ ] Append campus_id to /generic/terms and /generic/active-term requests
  - [ ] Make terms cache campus-aware
  - [ ] Validate persisted selectedTerm against current campus
  - [ ] Listen to campusChanged to clear cache, reload, and set/keep selection appropriately

4) Verification
- [ ] Verify API: GET /api/v1/generic/terms?campus_id=1 returns only campus 1 terms
- [ ] Verify API: GET /api/v1/generic/active-term?campus_id=1 returns latest campus 1 term
- [ ] Verify SPA: Campus selector hidden for non-admin, visible for admin
- [ ] Verify SPA: Changing campus triggers TermService reload; selection remains valid or re-selects active term
- [ ] Regression: When campus is not selected/null, terms still load (unfiltered)

Commands (to be run from laravel-api):
- php artisan migrate
- php artisan route:list (optional sanity check)
- php artisan tinker (optional queries)
