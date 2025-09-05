# Applicants Migration from admission_student_information to tb_mas_applicant_data

Scope:
- One-time, repeatable, idempotent import from legacy SMS table admission_student_information (singular) into tb_mas_applicant_data (JSON snapshots), matched by tb_mas_users.strEmail.
- Skip users who already have any tb_mas_applicant_data row.
- Preserve original source status in JSON at application.status; default tb_mas_applicant_data.status to "new" if column exists.

Plan Checklist:
- [ ] Service: laravel-api/app/Services/Admissions/ApplicantsMigrationService.php
  - [ ] preflight(): verify source/target tables and columns exist
  - [ ] fetchSourceRows(?since, ?email, ?limit): read candidate rows with safe ordering and filters
  - [ ] dedupeLatestPerEmail(Collection): ensure one latest record per unique email
  - [ ] resolveUserIdByEmail(email): match tb_mas_users.intID using strEmail only
  - [ ] hasAnyApplicantData(userId): skip if any tb_mas_applicant_data exists
  - [ ] normalizeRow(row): build strict JSON payload per implementation plan schema
  - [ ] insertSnapshot(userId, json, status='new'): insert into tb_mas_applicant_data, include status only if column exists

- [ ] Artisan Command: laravel-api/app/Console/Commands/MigrateApplicantsFromAdmissionStudentInfo.php
  - [ ] Signature: applicants:migrate-from-sms
  - [ ] Options: --dry-run, --limit=INT, --since=YYYY-MM-DD, --email=STRING, --source-connection=STRING
  - [ ] Behavior: run preflight, fetch + dedupe, resolve users, summarize actions, insert unless --dry-run

- [ ] Kernel autoload verification
  - [ ] app/Console/Kernel.php autoloads app/Console/Commands; no explicit registration required

- [ ] Dry-run validation
  - [ ] php artisan applicants:migrate-from-sms --dry-run --limit=50
  - [ ] Output should include: preflight OK/MISSING, candidates, deduped (unique emails), resolved users, existing applicant_data skipped, to-insert count

- [ ] Targeted email test
  - [ ] php artisan applicants:migrate-from-sms --dry-run --email="john.doe@example.com"

- [ ] Execute initial small batch
  - [ ] php artisan applicants:migrate-from-sms --limit=50

- [ ] Post-validation queries
  - [ ] SELECT COUNT(*) FROM tb_mas_applicant_data WHERE user_id IN (SELECT intID FROM tb_mas_users WHERE strEmail IS NOT NULL);
  - [ ] Spot-check latest row for a known email; verify JSON fields and application.status preserved

- [ ] Logging and rollback note
  - [ ] Tool inserts only; if rollback needed, delete by created_at window of the run
  - [ ] Re-runs are safe (skips users that already have applicant_data)

Runbook Notes:
- If a separate DB connection exists for SMS source (e.g., "sms"), pass --source-connection=sms
- Use laravel-api/scripts/dump_table_columns.php to inspect schema quickly
