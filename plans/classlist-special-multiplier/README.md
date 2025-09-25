# How to Edit special_class and special_multiplier for a Classlist

There is no UI for this yet. You can update the fields directly using:
- The provided PHP helper script (recommended)
- A raw SQL UPDATE (manual)
- Via a seed/debug script flow to verify effects

Prerequisites:
- Run DB migrations to add the columns:
  - php laravel-api/artisan migrate
- Ensure laravel-api/.env has a working DB connection.

Option A — Use helper script (recommended)
- File:
  - laravel-api/scripts/set_classlist_special.php
- Usage:
  - php laravel-api/scripts/set_classlist_special.php <classlist_id> <special_class:0|1> <special_multiplier>
- Examples:
  - Enable with 1.25x: php laravel-api/scripts/set_classlist_special.php 12345 1 1.25
  - Disable (resets multiplier to 1.0): php laravel-api/scripts/set_classlist_special.php 12345 0 1.00

Notes:
- The script validates: when special_class=1, multiplier must be > 0.
- When special_class=0, multiplier is set to 1.0 for clarity.

Option B — Direct SQL (manual)
- Example (MySQL):
  - UPDATE tb_mas_classlist SET special_class=1, special_multiplier=1.25 WHERE intID=12345;
- To disable:
  - UPDATE tb_mas_classlist SET special_class=0, special_multiplier=1.00 WHERE intID=12345;

Verifying the effect (Critical-path checks)
- Test script:
  - laravel-api/scripts/test_special_class_multiplier.php
- Usage:
  - php laravel-api/scripts/test_special_class_multiplier.php <student_number> <syid> <classlist_id> <multiplier>
- What it does:
  1) Computes baseline tuition
  2) Applies special_class=1 with the given multiplier to that classlist
  3) Recomputes tuition and prints deltas for the classlist’s subject item and summary totals
  4) Restores original special_class/multiplier values

Computation behavior implemented
- College (per-unit): Multiplier is applied only to non-NSTP subjects on that classlist; NSTP keeps using its dedicated NSTP rate (multiplier ignored).
- SHS:
  - TRACK: unchanged
  - MODULAR: payment_amount is multiplied when the modular classlist is flagged
  - ELECTIVE: elective rate is multiplied when the elective classlist is flagged

Where in code
- Migration: laravel-api/database/migrations/2025_09_20_000200_add_special_fields_to_tb_mas_classlist.php
- Service query mapping: laravel-api/app/Services/TuitionService.php (adds classlist_id, special_class, special_multiplier to subject rows)
- Computation:
  - College path: TuitionCalculator::computeCollegeTuition
  - SHS path: TuitionCalculator::computeSHSTuition

Troubleshooting
- Migration fails / cannot connect:
  - Update laravel-api/.env with DB connection values and re-run:
    - php laravel-api/artisan migrate
- No changes observed:
  - Ensure you flagged the correct classlist_id (the one the student is enrolled in for that term).
  - Ensure multiplier > 0 and special_class=1.
  - Re-run the test script to confirm item-level rate/amount deltas.
