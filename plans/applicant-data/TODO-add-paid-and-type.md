# TODO - Add fields to tb_mas_applicant_data

Goal:
- Add the following columns to tb_mas_applicant_data:
  - paid_application_fee (boolean, default false, not nullable)
  - paid_reservation_fee (boolean, default false, not nullable)
  - applicant_type (unsigned integer, nullable) with FK to tb_mas_applicant_types.intID
    - ON UPDATE CASCADE
    - ON DELETE SET NULL

Context:
- tb_mas_applicant_types exists with primary key intID (unsigned int).
- Past FK errors occurred due to mismatched column types. Ensure applicant_type is unsigned integer to match intID and that referenced table exists before adding FK.

Steps:
1. Create a Laravel migration:
   - File: laravel-api/database/migrations/2025_09_03_001000_add_paid_and_applicant_type_to_tb_mas_applicant_data.php
   - Up:
     - Add columns if not existing:
       - paid_application_fee: boolean, default false, after status
       - paid_reservation_fee: boolean, default false, after paid_application_fee
       - applicant_type: unsignedInteger, nullable, after paid_reservation_fee
     - Add foreign key if both tables/columns exist:
       - applicant_type references tb_mas_applicant_types(intID)
       - onUpdate('cascade'), onDelete('set null')
       - Constraint name: fk_applicant_data_applicant_type
   - Down:
     - Drop foreign key fk_applicant_data_applicant_type (if exists)
     - Drop the three columns (if exist)

2. Run migrations:
   - cd laravel-api &amp;&amp; php artisan migrate

3. Verify DB schema:
   - Use MySQL DESCRIBE tb_mas_applicant_data or laravel-api/scripts/dump_table_columns.php
   - Confirm:
     - paid_application_fee tinyint(1) not null default 0
     - paid_reservation_fee tinyint(1) not null default 0
     - applicant_type int unsigned null
     - FK exists to tb_mas_applicant_types(intID) with ON DELETE SET NULL / ON UPDATE CASCADE

4. (Optional) Wire-up in code:
   - If needed for APIs, update any resources/DTOs/models to expose these fields.
   - Update form requests/validation if these fields will be accepted from clients.

Rollback:
- php artisan migrate:rollback (will drop FK and columns added by this migration)
