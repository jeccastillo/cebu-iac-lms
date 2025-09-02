# Task: Add nullable file_link to tb_mas_application_requirements

Scope:
- Add a new nullable column file_link to the tb_mas_application_requirements table.
- Update the Eloquent model to allow mass assignment and explicit casting.

Steps:
- [ ] Create a new Laravel migration to add file_link (string, 255, nullable) after submitted_status.
- [ ] Implement down() to drop the file_link column.
- [ ] Update App\Models\ApplicationRequirement:
  - [ ] Add 'file_link' to $fillable.
  - [ ] Add 'file_link' => 'string' to $casts (explicit typing).
- [ ] Run migration locally: php artisan migrate (inside laravel-api).
- [ ] Smoke test:
  - [ ] Tinker or quick script to create/update an ApplicationRequirement with file_link set to a URL.
  - [ ] Verify DB column exists and accepts null.

Notes:
- Column placement: after submitted_status for readability.
- Default length: 255 (change later if longer URLs are required).

Owner: BLACKBOXAI
Created: 2025-09-02
