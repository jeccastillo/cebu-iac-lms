# Standalone Makati Application Form - Implementation TODO

Scope:
- Recreate the application form from https://sms-makati.iacademy.edu.ph/ as a standalone page (no header).
- On submit:
  - Save core fields to `tb_mas_users` with status = "applicant".
  - Save any extra fields not in `tb_mas_users` to a new table `tb_mas_applicant_data`.

Plan and Steps:

- [ ] Backend: Controller
  - [ ] Create `application/modules/admissions/controllers/ApplicationStandalone.php`
    - `form()` => renders standalone page
    - `submit()` => accepts JSON POST, delegates to model, returns JSON

- [ ] Backend: Model
  - [ ] Create `application/modules/admissions/models/Applicant_model.php`
    - `save_application($payload)`:
      - Discover `tb_mas_users` fields with `$this->db->list_fields('tb_mas_users')`
      - Split payload into:
        - `$userData` (fields that exist in `tb_mas_users`)
        - `$extraData` (remaining fields)
      - Set defaults dynamically if fields exist:
        - `student_status` or `enumEnrolledStatus` = 'applicant'
        - `dteCreated` = now (if exists)
        - `slug` = unique slug (if exists)
      - Run DB transaction:
        - Insert into `tb_mas_users`, get `insert_id`
        - Insert into `tb_mas_applicant_data` with `user_id` and JSON `data` (fallback to LONGTEXT if JSON not supported)
      - Return result array: `['success' => true/false, 'message' => '', 'slug' => '', 'user_id' => int]`

- [ ] Frontend: View
  - [ ] Create `application/modules/admissions/views/standalone/application_form.php`
    - Standalone HTML (no header include)
    - Uses `assets/css/bootstrap.min.css` for layout
    - Fields: replicate essential fields from `site/views/student_application_makati.php` (first/middle/last name, suffix, DOB, POB, gender, citizenship (+ dual option), email + confirm, mobile + confirm, address, parents/guardian, educational background, additional info, how did you find us, best time)
    - Client-side validation for email/mobile confirmation
    - Submit via `fetch` POST JSON to `/apply/submit`, handle JSON response

- [ ] Routing
  - [ ] Edit `application/config/routes.php`:
    - `$route['apply'] = 'admissions/ApplicationStandalone/form';`
    - `$route['apply/submit'] = 'admissions/ApplicationStandalone/submit';`

- [ ] Database
  - [ ] Create SQL file: `sql_update/2025_08_25_create_tb_mas_applicant_data.sql`
    - ```
      CREATE TABLE IF NOT EXISTS `tb_mas_applicant_data` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `data` JSON NULL,
        `created_at` DATETIME NULL,
        `updated_at` DATETIME NULL,
        INDEX (`user_id`),
        CONSTRAINT `fk_applicant_user_id` FOREIGN KEY (`user_id`)
          REFERENCES `tb_mas_users`(`intID`) ON DELETE CASCADE
      );
      ```
    - If JSON not supported in your MySQL version, replace `JSON` with `LONGTEXT`

- [ ] Testing
  - [ ] Manually execute the SQL in your DB (if not already present)
  - [ ] Visit `/apply` and verify the page renders without a header
  - [ ] Submit the form and verify:
    - New row in `tb_mas_users` with status "applicant" (via `student_status` or `enumEnrolledStatus`)
    - New row in `tb_mas_applicant_data` with matching `user_id` and JSON `data`
  - [ ] Validate slug is generated when `slug` column exists
  - [ ] Validate `dteCreated` set when column exists

Notes:
- All user-table field checks and defaults are dynamic to support schema variations between environments.
- The first pass minimizes external dependencies (e.g., no token, no remote APIs for school list). We can enhance with the same dynamic behavior later if required.
