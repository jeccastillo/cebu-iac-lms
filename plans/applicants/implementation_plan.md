# Implementation Plan

[Overview]
Migrate legacy applicant rows from the SMS table admission_student_information into tb_mas_applicant_data by matching each row to tb_mas_users via email (strEmail) and creating exactly one applicant JSON snapshot per matched user, skipping users who already have any tb_mas_applicant_data row.

This plan introduces a one-time repeatable migration utility (Artisan command + service) to safely read from the legacy SMS source table admission_student_information (singular), resolve the corresponding legacy user in tb_mas_users by matching email to strEmail only, and then insert a normalized snapshot row into tb_mas_applicant_data (JSON). It is idempotent and skip-safe, deduplicates multiple SMS rows per email by selecting the latest source row, and performs schema preflight checks before writing. No frontend changes are needed. This fits the current data model where tb_mas_applicant_data holds flexible JSON payloads and auxiliary flags while the legacy user identity remains in tb_mas_users.

[Types]
No PHP type system changes; introduce a strict JSON snapshot schema for tb_mas_applicant_data.data.

JSON schema (stored in tb_mas_applicant_data.data; keys present when available):
- _meta (object)
  - source_table: "admission_student_information"
  - source_pk: int (admission_student_information.id)
  - imported_at: ISO-8601 timestamp
  - import_version: string (e.g., "asi_to_applicant_data_v1")
- identity (object)
  - first_name: string
  - middle_name: string|null
  - last_name: string
  - email: string
  - mobile_number: string|null
  - tel_number: string|null
  - gender: string|null
  - date_of_birth: string|null (YYYY-MM-DD)
- address (object)
  - address: string|null
  - barangay: string|null
  - city: string|null
  - province: string|null
  - country: string|null
- application (object)
  - syid: int|null
  - campus: string|null
  - student_type: string|null
  - status: string|null (original status from source)
  - type_id: int|null
  - type_id2: int|null
  - type_id3: int|null
  - program: string|null
  - program2: string|null
  - program3: string|null
  - school: string|null
  - school_id: int|null
  - slug: string (uuid)
  - referrer: string|null
  - source: string|null
- parents_guardian (object)
  - father_name, father_contact, father_email, father_occupation
  - mother_name, mother_contact, mother_email, mother_occupation
  - guardian_name, guardian_contact, guardian_email, guardian_occupation
- health (object)
  - good_moral: string|int|bool|null
  - crime: string|int|bool|null
  - hospitalized: string|int|bool|null
  - hospitalized_reason: string|null
  - health_concern: string|null
  - other_health_concern: string|null
- schedules (object)
  - best_time: string|null
  - schedule_date: string|null
  - schedule_time_from: string|null
  - schedule_time_to: string|null
  - date_interviewed: string|null (YYYY-MM-DD)
  - date_reserved: string|null (YYYY-MM-DD)
  - date_enrolled: string|null (YYYY-MM-DD)
  - date_withdrawn: string|null (YYYY-MM-DD)

Status handling:
- tb_mas_applicant_data.status column:
  - Set to "new" by default if present (matches existing migrations).
  - Preserve original source status at application.status in JSON for audit.

[Files]
Add a new Artisan command and a small migration service; do not modify existing controllers or models.

- New files:
  - laravel-api/app/Console/Commands/MigrateApplicantsFromAdmissionStudentInfo.php
    - Purpose: CLI entrypoint to run the migration with options (--dry-run, --limit, --since, --email, --source-connection).
  - laravel-api/app/Services/Admissions/ApplicantsMigrationService.php
    - Purpose: Encapsulate logic for reading from admission_student_information, deduplicating by email, matching tb_mas_users, JSON normalization, skip logic, and inserting tb_mas_applicant_data.

- Existing files to be modified:
  - laravel-api/app/Console/Kernel.php
    - Register the new command class in $commands and/or schedule() comment if needed.

- Optional/new configuration (no changes by default):
  - If a separate connection for the SMS database exists (e.g., config('database.connections.sms')), the command will accept --source-connection=sms to use DB::connection('sms'). If not provided, default connection is used.

- Verification/utility (existing):
  - laravel-api/scripts/dump_table_columns.php (used for schema preflight checks in runbook).

[Functions]
Introduce new functions for controlled, idempotent migration.

- New functions:
  - ApplicantsMigrationService::__construct(Connection $default, ?Connection $source = null)
    - File: app/Services/Admissions/ApplicantsMigrationService.php
    - Purpose: Set up DB handles (default = legacy portal DB with tb_mas_users; source defaulting to same unless --source-connection provided).
  - ApplicantsMigrationService::preflight(): array
    - Purpose: Confirm existence of required tables/columns:
      - admission_student_information: must exist (singular)
      - tb_mas_users: must exist with strEmail, intID
      - tb_mas_applicant_data: must exist with user_id, data; detect optional status
    - Returns summary with booleans and missing items.
  - ApplicantsMigrationService::fetchSourceRows(?string $since = null, ?string $email = null, ?int $limit = null): Collection
    - Purpose: Read candidate rows from admission_student_information with filters.
  - ApplicantsMigrationService::dedupeLatestPerEmail(Collection $rows): Collection
    - Purpose: For duplicate emails, select latest by created_at desc, then id desc.
  - ApplicantsMigrationService::resolveUserIdByEmail(string $email): ?int
    - Purpose: Find tb_mas_users.intID where strEmail = email. No fallback to strGSuiteEmail (per requirement).
  - ApplicantsMigrationService::hasAnyApplicantData(int $userId): bool
    - Purpose: Returns true if any tb_mas_applicant_data row exists for user_id.
  - ApplicantsMigrationService::normalizeRow(object $row): array
    - Purpose: Build strict JSON array matching the schema above.
  - ApplicantsMigrationService::insertSnapshot(int $userId, array $json, ?string $status = 'new'): void
    - Purpose: Insert one tb_mas_applicant_data record. If status column exists, include; otherwise omit safely.

  - MigrateApplicantsFromAdmissionStudentInfo::handle(): int
    - File: app/Console/Commands/MigrateApplicantsFromAdmissionStudentInfo.php
    - Purpose: Parse options, run preflight, fetch + dedupe, resolve users, perform skip checks, render a dry-run summary or perform inserts. Outputs totals.

- Modified functions:
  - app/Console/Kernel.php: add the command class reference in $commands list.

[Classes]
Introduce one command and one service class, no inheritance changes.

- New classes:
  - App\Console\Commands\MigrateApplicantsFromAdmissionStudentInfo
    - Key methods: configure signature/description, handle()
    - Options:
      - --dry-run: do not write, print summary
      - --limit=INT: cap processed source emails
      - --since=YYYY-MM-DD: only include source rows with created_at >= since (if column exists; fallback no filter)
      - --email=STRING: only process this email
      - --source-connection=STRING: DB connection name for admission_student_information
  - App\Services\Admissions\ApplicantsMigrationService
    - Key methods as listed above

- Modified classes:
  - App\Console\Kernel: register command

[Dependencies]
No new Composer packages are required.

- Built-in:
  - Illuminate\Support\Facades\DB
  - Illuminate\Support\Arr / Str / Carbon (optional)
- Optional database connection:
  - If a distinct "sms" connection exists in config/database.php, the command can be given --source-connection=sms to read admission_student_information from that connection.

[Testing]
Create a repeatable, dry-run-first migration flow with verifiable counts and spot-check queries.

- Preflight verification:
  - php artisan migrate:status (ensure tb_mas_applicant_data and status column exist per recent migrations)
  - php -d detect_unicode=0 laravel-api/scripts/dump_table_columns.php tb_mas_applicant_data
- Dry run:
  - php artisan applicants:migrate-from-sms --dry-run --limit=50
  - Expected output:
    - tables/columns OK/MISSING
    - candidates found
    - deduped (unique emails)
    - resolved users
    - already-have-applicant-data (skipped)
    - to-insert count
- Targeted email test:
  - php artisan applicants:migrate-from-sms --email="john.doe@example.com" --dry-run
- Execute small batch:
  - php artisan applicants:migrate-from-sms --limit=50
  - Confirm inserted rows: SELECT COUNT(*) FROM tb_mas_applicant_data WHERE user_id IN (SELECT intID FROM tb_mas_users WHERE strEmail IS NOT NULL);
- Spot-check a row:
  - SELECT ad.user_id, ad.status, ad.created_at FROM tb_mas_applicant_data ad JOIN tb_mas_users u ON u.intID = ad.user_id WHERE u.strEmail = 'john.doe@example.com' ORDER BY ad.id DESC LIMIT 1;
  - Verify JSON content fields and original status preserved at application.status.

[Implementation Order]
Implement via a safe, idempotent sequence focusing on preflight checks and dry run to minimize risk.

1) Add ApplicantsMigrationService with:
   - preflight()
   - fetchSourceRows()
   - dedupeLatestPerEmail()
   - resolveUserIdByEmail()
   - hasAnyApplicantData()
   - normalizeRow()
   - insertSnapshot()
2) Add Artisan command MigrateApplicantsFromAdmissionStudentInfo with signature applicants:migrate-from-sms and all options.
3) Register command in app/Console/Kernel.php.
4) Manual runbook:
   - Run command with --dry-run and small --limit to verify output.
   - Inspect schema using scripts/dump_table_columns.php for tb_mas_users (intID, strEmail) and tb_mas_applicant_data (user_id, data, status?).
5) Execute migration in batches (e.g., --limit=500 per run) monitoring logs.
6) Post-validation:
   - Count matched unique emails vs inserted rows; verify duplicates skipped properly.
   - Confirm only one tb_mas_applicant_data row per user_id (existing rows cause skip).
7) Logging and rollback strategy:
   - The tool is additive-only (inserts). If any unexpected data is found, delete newly inserted rows for a given run window by created_at cutoff.
   - Rerun safely: idempotent (skips users with existing applicant_data).

Notes and constraints addressed:
- Source: admission_student_information (singular).
- Matching: tb_mas_users.strEmail only; no fallback to strGSuiteEmail.
- Duplicates: Only one latest snapshot per unique email (based on created_at desc, fallback id desc).
- Existing applicant rows: Skip if any tb_mas_applicant_data exists for user_id.
- Snapshot integrity: Preserve original source status in JSON; tb_mas_applicant_data.status defaults to "new" if column exists.
- Foreign key concerns previously seen on tb_mas_applicant_data are avoided by relying on existing live schema; the command only inserts if tables/columns exist and types match.
