# Implementation Plan

[Overview]
Implement functionality in the student ledger to allow users to apply excess negative closing balance from a selected term as payment for another term, and to revert this action.

This feature addresses the need to handle cases where a student has a negative closing balance (excess payment) in a term and wants to allocate that excess to another term's payment. It also provides the ability to revert such allocations. The implementation will extend the existing student ledger system, integrating with the current ledger data aggregation, API endpoints, and frontend UI. This ensures seamless user experience and accurate financial tracking across terms.

[Types]
Extend the ledger data model to include excess payment application records, with fields to track source term, target term, amount applied, and status (applied/reverted).

Define new request and response types for API endpoints handling apply and revert actions.

[Files]
Modify existing backend and frontend files; add new migration files for database schema changes.

- New files:
  - Database migration file(s) to add tables/columns for excess payment applications.
- Modified files:
  - laravel-api/app/Services/StudentLedgerService.php: Add methods to apply and revert excess payments.
  - laravel-api/app/Http/Controllers/Api/V1/FinanceController.php: Add new API endpoints for apply and revert actions.
  - frontend/unity-spa/features/finance/ledger.controller.js: Add UI logic and API calls for the new feature.
  - frontend/unity-spa/features/finance/ledger.service.js: Add service methods to call new API endpoints.
  - frontend/unity-spa/features/finance/ledger.html: Add UI elements for applying and reverting excess payments.

[Functions]
- New functions:
  - applyExcessPayment(studentId: int, sourceTermId: int, targetTermId: int, amount: float): bool (StudentLedgerService.php)
  - revertExcessPayment(applicationId: int): bool (StudentLedgerService.php)
  - API controller methods for the above actions (FinanceController.php)
  - Frontend service methods to call the new API endpoints (ledger.service.js)
  - Frontend controller methods to handle UI interactions and call service methods (ledger.controller.js)
- Modified functions:
  - getLedger(...) (StudentLedgerService.php) to include applied excess payments in ledger rows and reflect updated balances.

[Classes]
- Modified classes:
  - StudentLedgerService: Add new methods for excess payment application and reversion.
  - FinanceController: Add new methods for API endpoints.

[Dependencies]
No new external packages are required. Database migration will require Laravel's migration system.

[Testing]
- Backend:
  - Unit tests for new service methods.
  - API endpoint tests for apply and revert actions.
- Frontend:
  - Unit tests for new service and controller methods.
  - Integration/UI tests for the new UI elements and workflows.

[Implementation Order]
1. Create database migration(s) for excess payment application tracking.
2. Implement new methods in StudentLedgerService.php for applying and reverting excess payments.
3. Add new API endpoints in FinanceController.php for apply and revert actions.
4. Update getLedger method to include applied excess payments and update balances accordingly.
5. Update frontend ledger.service.js to add service methods for new API endpoints.
6. Update frontend ledger.controller.js to add UI logic for detecting negative closing balance, applying excess payment, and reverting.
7. Update ledger.html to add UI controls for the feature.
8. Write and run tests for backend and frontend.
9. Perform manual testing and validation.
