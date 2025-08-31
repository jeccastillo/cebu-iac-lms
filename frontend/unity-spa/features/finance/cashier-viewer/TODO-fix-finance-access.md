# TODO - Fix Finance Access on Cashier Viewer

Goal: Resolve "Forbidden - missing required role" for Finance users on Cashier Viewer by aligning backend route middleware with feature requirements.

Context:
- Cashier Viewer uses UnityService endpoints:
  - GET /unity/registration
  - PUT /unity/registration
  - POST /unity/tuition-save
  - GET /unity/tuition-saved
- These endpoints in routes/api.php currently allow only registrar/admin, causing 403 for finance users despite the page allowing finance.

Plan/Steps:
- [ ] Update Laravel route middleware to include finance for the Unity endpoints used by Cashier Viewer:
  - [ ] GET /unity/registration → middleware('role:registrar,finance,admin')
  - [ ] PUT /unity/registration → middleware('role:registrar,finance,admin')
  - [ ] POST /unity/tuition-save → middleware('role:registrar,finance,admin')
  - [ ] GET /unity/tuition-saved → middleware('role:registrar,finance,admin')

Notes:
- Cashiers endpoints already include finance where applicable:
  - GET /cashiers/me, POST /cashiers/{id}/payments → 'role:cashier_admin,finance,admin'
- Payment Modes/Descriptions CRUD already allow 'finance,admin'.

Verification:
- [ ] As a Finance user:
  - Load #/finance/cashier/:id; ensure Registration, Tuition (compute/saved), Payment Details, Ledger, Records load without 403.
  - Confirm updates to registration (paymentType, tuition_year, etc.) succeed.
- [ ] As Registrar and Admin:
  - Ensure no regression; endpoints remain accessible as before.

Out of Scope:
- No frontend changes required for this fix.
