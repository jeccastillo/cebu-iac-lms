# Applicants List and View Pages — TODO

Approved plan to add a Display Applicants page and an Applicant View page (backend + frontend).

Scope:
- Backend (Laravel API)
  - Add ApplicantController with index and show endpoints using tb_mas_users and tb_mas_applicant_data.
  - Wire routes under /api/v1 with admissions/admin role protection.

- Frontend (Unity SPA - AngularJS 1.x)
  - Add service, controllers, views for Applicants list and Applicant view.
  - Register routes in core/routes.js.
  - Add sidebar menu item under Admissions.

Assumptions:
- Applicant identifier in endpoints uses tb_mas_users.intID.
- Minimal filters for index: status, search, campus, date range.
- Applicant details page shows core fields and formatted applicant_data JSON.

Tasks:

1) Backend: Controller
- [ ] Create file: laravel-api/app/Http/Controllers/Api/V1/ApplicantController.php
  - [ ] Method index(Request $request)
    - [ ] Join tb_mas_users (u) to tb_mas_applicant_data (ad) on u.intID = ad.user_id
    - [ ] Select: u.intID as id, u.strFirstname, u.strLastname, u.strEmail, u.strMobileNumber, u.campus, u.student_type, u.dteCreated, ad.status, ad.created_at
    - [ ] Filters (optional): status, search (name/email), campus, date_from/date_to (on ad.created_at)
    - [ ] Pagination: page (default 1), per_page (default 20)
    - [ ] Return JSON: { data: [...], meta: { page, per_page, total, total_pages } }
  - [ ] Method show(int $id)
    - [ ] Fetch tb_mas_users by intID
    - [ ] Fetch tb_mas_applicant_data by user_id (latest if multiple)
    - [ ] Return JSON: { user: {...}, status, applicant_data (decoded), created_at, updated_at }
    - [ ] 404 if user or applicant data not found

2) Backend: Routes
- [ ] Edit laravel-api/routes/api.php
  - [ ] Add use App\Http\Controllers\Api\V1\ApplicantController;
  - [ ] Within v1 group:
    - [ ] GET /applicants -> ApplicantController@index (middleware role:admissions,admin)
    - [ ] GET /applicants/{id} -> ApplicantController@show (middleware role:admissions,admin)

3) Frontend: Service
- [ ] Create file: frontend/unity-spa/features/admissions/applicants/applicants.service.js
  - [ ] list(params): GET api/v1/applicants with query params
  - [ ] get(id): GET api/v1/applicants/{id}

4) Frontend: List Page
- [ ] Create file: frontend/unity-spa/features/admissions/applicants/applicants.controller.js (ApplicantsListController)
  - [ ] Fetch with filters, support pagination
- [ ] Create file: frontend/unity-spa/features/admissions/applicants/list.html
  - [ ] Table columns: ID, Name, Email, Mobile, Campus, Status, Created, Action/View
  - [ ] Filters: search, status, campus, date range (basic UI)
  - [ ] Pagination controls

5) Frontend: View Page
- [ ] Create file: frontend/unity-spa/features/admissions/applicants/applicant-view.controller.js (ApplicantViewController)
  - [ ] Load applicant by :id
- [ ] Create file: frontend/unity-spa/features/admissions/applicants/view.html
  - [ ] Display core user details, status, created/updated
  - [ ] Pretty-print applicant_data JSON and highlight known fields (address, program, student_type)

6) Frontend: Routes and Menu
- [ ] Edit frontend/unity-spa/core/routes.js
  - [ ] Add routes:
    - [ ] /admissions/applicants (list)
    - [ ] /admissions/applicants/:id (view)
  - [ ] Set requiredRoles: ["admissions", "admin"]
- [ ] Edit frontend/unity-spa/shared/components/sidebar/sidebar.controller.js
  - [ ] Add menu item into Admissions group: { label: 'Applicants', path: '/admissions/applicants' }

7) Smoke Tests
- [ ] Hit GET /api/v1/applicants (no filters) and verify JSON shape
- [ ] Open #/admissions/applicants to see list
- [ ] Click a row to go to #/admissions/applicants/:id and verify details display

8) Enhancements (optional, post-MVP)
- [ ] Sorting on columns
- [ ] Advanced filters with chips
- [ ] Status update endpoint and UI (PUT/PATCH)
- [ ] Export to CSV
