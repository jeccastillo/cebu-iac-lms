# Admissions Success Page: Add Upload Initial Requirements Link

Objective:
- On the success page of the application, add a link that directs the applicant to the Upload Initial Requirements page.
- Remove the "Submit Another Application" button.

Context:
- Success page template: `frontend/unity-spa/features/admissions/success.html`
- Route exists: `/public/initial-requirements/:hash` handled by `InitialRequirementsController`
- Backend returns `hash` after successful submission in `AdmissionsController@store` response (`data.hash`).
- Current submit success redirect: `/admissions/success` (no hash transmitted)
- Success route currently has no controller attached.

Steps:
1. Update submit flow to carry `hash` to success page
   - File: `frontend/unity-spa/features/admissions/apply.controller.js`
   - Change success handler to read `resp.data.data.hash` and redirect to: `/admissions/success?hash=<hash>`.
   - Fallback to `/admissions/success` if `hash` is missing.

2. Add controller for success page to read `hash` from query string
   - File: `frontend/unity-spa/features/admissions/success.controller.js` (new)
   - Logic: read `$location.search().hash`, expose as `vm.hash`, and set `vm.uploadUrl = "#/public/initial-requirements/" + encodeURIComponent(vm.hash)`.

3. Register the controller in routes
   - File: `frontend/unity-spa/core/routes.js`
   - For route `/admissions/success`, add:
     - `controller: "AdmissionsSuccessController"`
     - `controllerAs: "vm"`

4. Load the controller script in index
   - File: `frontend/unity-spa/index.html`
   - Add script tag to load `features/admissions/success.controller.js`.

5. Update success page UI
   - File: `frontend/unity-spa/features/admissions/success.html`
   - Remove the "Submit Another Application" button.
   - Add link button: `href="{{ vm.uploadUrl }}"` with `ng-if="vm.hash"`, text: "Upload Initial Requirements".

Testing:
- Fill the application form and submit.
- Confirm redirect to `/admissions/success?hash=...`.
- Verify the success page shows the "Upload Initial Requirements" button and clicking it navigates to `#/public/initial-requirements/<hash>`.
- Verify the initial requirements page loads items for the applicant.

Status:
- [ ] Step 1
- [ ] Step 2
- [ ] Step 3
- [ ] Step 4
- [ ] Step 5
