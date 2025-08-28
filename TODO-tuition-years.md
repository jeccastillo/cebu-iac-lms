# TODO — Tuition Years Migration (CI → Laravel API + AngularJS)

This task list implements the approved plan in `implementation_plan.md` to migrate the CodeIgniter Tuition Year module to Laravel API + AngularJS (Tailwind) under Finance.

Progress Legend:
- [ ] not started
- [x] completed
- [~] partial/in-progress

## Backend (Laravel API)

1) Route and Controller method: Set Default
- [x] Add route POST /api/v1/tuition-years/{id}/set-default (role: registrar,admin)
- [x] Implement TuitionYearController@setDefault(Request $request, $id):
  - scope=college → reset isDefault=0 across table, then set isDefault=1 for $id
  - scope=shs → reset isDefaultShs=0 across table, then set isDefaultShs=1 for $id
  - Return { success: true, message: 'Updated' }
  - 422 on invalid scope or missing id

2) Schema guard migration
- [x] Create migration: add columns if missing on tb_mas_tuition_year:
  - installmentFixed: decimal(10,2) nullable
  - freeElectiveCount: integer nullable
  - final: tinyint(1) default(0)
- [x] Use Schema::hasColumn guards to avoid failure on existing columns

3) Verify/add request validation fallback
- [ ] Ensure TuitionYearAddRequest prepares safe defaults (already implemented)
- [ ] No changes needed unless new fields added (installmentFixed/freeElectiveCount/final are updated via finalize endpoint)

## Frontend (AngularJS + Tailwind)

4) New Service: TuitionYearsService
- [ ] Methods:
  - list(): GET /tuition-years
  - show(id): GET /tuition-years/{id}
  - create(payload): POST /tuition-years/add
  - update(fields): POST /tuition-years/finalize (expects intID and fields)
  - duplicate(id): POST /tuition-years/duplicate
  - remove(id): POST /tuition-years/delete
  - setDefault(id, scope): POST /tuition-years/{id}/set-default?scope=college|shs
  - listMisc(id): GET /tuition-years/{id}/misc
  - listLabFees(id): GET /tuition-years/{id}/lab-fees
  - listTracks(id): GET /tuition-years/{id}/tracks
  - listPrograms(id): GET /tuition-years/{id}/programs
  - listElectives(id): GET /tuition-years/{id}/electives
  - addExtra(type, payload): POST /tuition-years/submit-extra
  - deleteExtra(type, id): POST /tuition-years/delete-type

5) List Page (Finance → Tuition Years)
- [ ] File: frontend/unity-spa/features/tuition-years/list.html
- [ ] Controller: frontend/unity-spa/features/tuition-years/tuition-years.controller.js (ListController)
- [ ] Features:
  - Tailwind table of tuition years (year, default badges College/SHS, final status)
  - Actions: Edit/View, Duplicate, Delete, Set Default (College/SHS)
  - “New Tuition Year” button to create (then redirect to edit screen)

6) Edit Page (Edit/View Tuition Year)
- [ ] File: frontend/unity-spa/features/tuition-years/edit.html
- [ ] Controller: added in tuition-years.controller.js (EditController)
- [ ] Features:
  - Base fields (year, pricePerUnit*, installmentDP, installmentIncrease, installmentFixed, freeElectiveCount, isDefault/isDefaultShs)
  - Save uses POST /tuition-years/finalize with intID and fields
  - Finalize (final=1) and Un-finalize (final=0) buttons (un-finalize gated to elevated role where applicable)
  - Sections (each table + add form + delete):
    - Misc: miscRegular/miscOnline/miscHyflex/miscHybrid + type
    - Lab fees: labRegular/labOnline/labHyflex/labHybrid
    - SHS Tracks: tuition_amount per mode, for programs type in shs/next
    - SHS Electives: tuition per mode for subjects marked elective
    - College Programs: unit rates per mode for program types college/other
  - Disable edit actions when final=1

7) Routes and Sidebar
- [ ] Add routes in frontend/unity-spa/core/routes.js:
  - /finance/tuition-years → list
  - /finance/tuition-years/:id → edit
- [ ] Add Finance sidebar link:
  - frontend/unity-spa/shared/components/sidebar/sidebar.html → “Tuition Years” → /finance/tuition-years

8) Role Gating / UX
- [ ] Show write actions only to roles allowed by API (registrar/admin)
- [ ] Guard destructive actions with confirmations (Swal)

## Integration Checks

9) Enlistment dropdown compatibility
- [ ] Ensure /tuition-years still returns objects with intID and year fields; label mapping consistent (as used by EnlistmentController)

10) Registration API validation
- [ ] Ensure UnityRegistrationUpdateRequest validation for fields.tuition_year stays valid (exists:tb_mas_tuition_year,intID)

## QA / Smoke (after implementation)
- [ ] Happy path flow: Create → Add extras → Duplicate → Delete extras → Set defaults → Finalize
- [ ] Edge cases: Missing FK on extras → 422; Invalid scope on set-default → 422
- [ ] UI disabled state when final=1; Un-finalize permitted for elevated role
