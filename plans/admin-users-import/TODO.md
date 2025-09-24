# Admin Users Import - Date Normalization Fix

Context:
- Error encountered during admin users import:
  "Invalid datetime format: 1292 Incorrect date value: 'NULL' for column `portal_cebu`.`tb_mas_users`.`date_of_graduation`".
- Cause: The importer sends literal strings like "NULL" (and other nullish tokens) into MySQL DATE columns, which MySQL rejects.

Scope:
- Implement normalization in StudentImportService to coerce nullish tokens to NULL and properly format date fields to MySQL-friendly formats.

Tasks:
- [ ] Update StudentImportService to:
  - [ ] Coerce common nullish tokens to null for all fields:
        null, n/a, na, none, nil, 0000-00-00, 0000-00-00 00:00:00 (case-insensitive).
  - [ ] Normalize known date columns to Y-m-d:
        dteBirthDate, date_of_graduation, date_of_admission, date_enrolled.
  - [ ] If parsing a date fails, set value to null (safe default).
- [ ] Keep existing behavior intact (header mapping, prohibited columns, foreign key resolution, chunked upsert).
- [ ] Add Carbon usage for parsing date strings.

Testing Plan:
- [ ] Use POST /api/v1/students/import with dry_run=true and a sample file covering:
  - date_of_graduation = "NULL", "", "0000-00-00" (should become null)
  - date_of_graduation = "9/1/2024", "2024-09-01", "01-09-2024" (should become "2024-09-01")
- [ ] Verify summary shows would-insert / would-update without DB errors.
- [ ] Run non-dry-run to confirm DB write succeeds and dates are stored correctly.

Notes:
- Only StudentImportService is modified.
- No schema changes are required.
