# Laravel API + Vue.js SPA Migration Tracker

This file tracks the end-to-end migration from CodeIgniter to Laravel (API) + Vue 3 (SPA) with a strangler rollout and thorough testing.

## Checklist

- [x] 1. Create migration tracker file with step-by-step checklist
- [x] 2. Scaffold Laravel API project in `laravel-api`
- [ ] 3. Configure Laravel:
  - [ ] 3.1 Set `.env` DB connection pointing to current MySQL (read-only initially)
  - [ ] 3.2 Enable Sanctum (SPA auth) and CORS/CSRF
  - [ ] 3.3 Install and set up Spatie Permission (RBAC)
  - [ ] 3.4 Configure queues (redis) and logging
  - [x] 3.5 Generate application key
- [ ] 4. Scaffold Vue 3 + Vite + TypeScript SPA in `vue-frontend`
- [ ] 5. Configure Vue app:
  - [ ] 5.1 Router (vue-router) and route guards
  - [ ] 5.2 Pinia stores (auth, users, admissions, registrar, finance, schedule, classroom, messages, reservations, deficiencies, analytics)
  - [ ] 5.3 Axios instance with interceptors, error handling, and base URL from env
  - [ ] 5.4 Global styles and UI library (Bootstrap 5 or alternative) decision
- [ ] 6. Implement Auth/RBAC:
  - [ ] 6.1 Password bridging (legacy hashes -> rehash to bcrypt/argon2 on login)
  - [ ] 6.2 Sanctum SPA endpoints: login, logout, me
  - [ ] 6.3 Roles/permissions parity with legacy user levels
- [ ] 7. Read-only endpoints (low risk):
  - [ ] 7.1 Portal data (student ledger, schedules, classlists, curriculum)
  - [ ] 7.2 Unity/Admin dashboards (metrics)
  - [ ] 7.3 Admissions read endpoints (applicant status, exam info)
- [ ] 8. Frontend read-only pages: dashboards, tables, detail views consuming new GET APIs
- [ ] 9. Mutations by module:
  - [ ] 9.1 Admissions (applicants, exams, registrations)
  - [ ] 9.2 Registrar (shift student, generate student number, enrollment flows)
  - [ ] 9.3 Finance (OR generation, transactions, invoices)
  - [ ] 9.4 Scheduling (code/section, conflicts)
  - [ ] 9.5 Classroom/Classlists (add/transfer/drop)
  - [ ] 9.6 Messaging (threads, send/read)
  - [ ] 9.7 Reservations (CRUD)
  - [ ] 9.8 Deficiencies (CRUD)
- [ ] 10. PDF/Excel:
  - [ ] 10.1 Port templates/services to jobs
  - [ ] 10.2 Signed download URLs and progress polling
- [ ] 11. AI Analytics:
  - [ ] 11.1 Migrate SQL and configs
  - [ ] 11.2 Controllers/Services and endpoints
- [ ] 12. Scheduler/Cron:
  - [ ] 12.1 Port `cron_script` tasks to Laravel scheduler
  - [ ] 12.2 Queue workers and Horizon (optional)
- [ ] 13. Coexistence (Strangler):
  - [ ] 13.1 Web server rules to route `/api` to Laravel and `/app` to SPA
  - [ ] 13.2 Feature flags per module; shared DB
- [ ] 14. Thorough Testing:
  - [ ] 14.1 Backend: Pest/PHPUnit (happy, error, edge cases, RBAC, rate limiting)
  - [ ] 14.2 Frontend: Vitest unit + Cypress E2E (navigation, forms, downloads, responsive)
  - [ ] 14.3 Data: counts, aggregates, referential integrity parity with legacy
  - [ ] 14.4 Performance/security baselines and scans
- [ ] 15. Progressive Cutover:
  - [ ] 15.1 Module-by-module traffic shift
  - [ ] 15.2 Rollback validation
- [ ] 16. Final Cutover:
  - [ ] 16.1 Make SPA default UI
  - [ ] 16.2 Archive/decommission legacy modules and document post-cutover ops

## Notes

- Migration pattern: Strangler to minimize risk.
- DB: Keep legacy table names initially; map with Eloquent `$table`; refactor later.
- Auth: Sanctum SPA; maintain legacy hash compatibility until all users rehash.
- PDFs/Excel: Use queue jobs for heavy tasks; keep layouts consistent with legacy.
- Observability: Centralized logs, metrics, and alerts for anomalies during cutover.
