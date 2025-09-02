# TODO — Add `status` column to `tb_mas_applicant_data`

Scope:
- Add a `status` field to `tb_mas_applicant_data`, default "new".
- Reflect change in both Laravel migration and manual SQL.

Steps:
1. [ ] Laravel migration: `2025_09_02_000700_add_status_to_tb_mas_applicant_data.php`
   - up(): add string('status', 20)->default('new')->after('data')
   - down(): dropColumn('status')

2. [ ] Update manual SQL: `laravel-api/database/manual_sql/2025_08_25_create_tb_mas_applicant_data.sql`
   - JSON variant: after `data` add `status` VARCHAR(20) NOT NULL DEFAULT 'new'
   - LONGTEXT fallback variant: after `data` add `status` VARCHAR(20) NOT NULL DEFAULT 'new'

3. [ ] Migration run (once table exists and FK issues are resolved)
   - php artisan migrate

4. [ ] Verification
   - Confirm column exists: DESCRIBE tb_mas_applicant_data; or use `scripts/dump_table_columns.php`
   - Ensure default is "new" and AdmissionsController insert (without `status`) stores default correctly.

Notes:
- No application code changes required at this time; defaults handle existing inserts.
- Indexing on `status` is not added; re-evaluate if queries on status become frequent.
