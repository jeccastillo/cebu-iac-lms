# Registration Viewer - Global Term Adoption

Goal: Use the global term selected from the sidebar (TermService) and remove any page-local term handling for the Registration Viewer.

Status:
- [ ] Step 1: Controller cleanup
  - Remove dead local-terms code (vm.loadTerms) and related loading/error fields (loading.terms, error.terms).
  - Keep using TermService (applyGlobalTerm + termChanged) to drive vm.term.
- [ ] Step 2: Template updates
  - Remove the Terms error line from the errors summary.
  - Add a compact indicator under the page header:
    - If vm.termLabel: show “Term: …”
    - Else: show “Select a term from the global selector.”
- [ ] Step 3: Test
  - Navigate to #/registrar/registration/:id
  - Verify the term indicator shows the global term.
  - Change the global term from the sidebar; verify registration, tuition, and records auto-refresh.
  - Confirm there is no local term selection UI and no “Terms” error message.

Notes:
- Controller already initializes TermService, applies global term selection, and listens to termChanged to trigger reloads.
- vm.termLabel is constructed from the global term object in the controller; template will now display it.
