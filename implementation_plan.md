# Implementation Plan

[Overview]
Generate and assign a final student number to a student when a tuition payment flips their registration status to "enrolled", using a deterministic, collision-free format derived from the active term (tb_mas_sy) and an incrementing sequence per term.

This change introduces a controlled generator that uses the school year start (strYearStart), the semester indicator (enumSem -> two-digit code), and term_student_type to produce a unique student number. For Senior High School (SHS) terms, the literal "SHA" is inserted after the year start. The number is assigned exactly at the point of enrollment finalization in CashierController::createPayment() and replaces any temporary pre-enrollment student number.

[Types]  
No new PHP types or database schema changes are required; we will add a private/protected helper with a clearly defined return type and input constraints.

Type-like specifications:
- Input: syid: int (tb_mas_sy.intID)
- Output: string (final student number)
- Derived data:
  - From tb_mas_sy: strYearStart: string (4 digits), enumSem: string (e.g., "1st", "2nd", "3rd"), term_student_type: string (e.g., "college" or "shs")
- Working structures (runtime only):
  - prefix: string = strYearStart + (isSHS ? "SHA" : "") + semCode
  - semCode: string = "01" | "02" | "03" | "04" (fallback derived from enumSem)
  - counter: int (>= 1)
  - suffix: string = zero-padded counter; default width 3, e.g. "001"
- Validation:
  - tb_mas_sy row must exist for syid
  - enumSem mapped to two-digit code
  - Look up max existing counter for the computed prefix across tb_mas_users.strStudentNumber

[Files]
We will only modify CashierController and add an internal helper. No migrations or new services are strictly required for the first iteration.

Detailed breakdown:
- Existing files to be modified:
  - laravel-api/app/Http/Controllers/Api/V1/CashierController.php
    - Add a protected helper method generateStudentNumber(int $syid): string
    - Call this generator inside createPayment() exactly when we flip enrollment_status to "enrolled" and set applicant status to "Enrolled"
    - Update tb_mas_users.strStudentNumber to the generated final number within the same transaction block used to finalize enrollment status (or in an immediate atomic block) to minimize race risk
- New files to be created: None
- Files to be deleted or moved: None
- Configuration updates: None

[Functions]
We will add one new helper and modify one existing function.

Detailed breakdown:
- New functions:
  - Name: generateStudentNumber
  - Signature: protected function generateStudentNumber(int $syid): string
  - File path: laravel-api/app/Http/Controllers/Api/V1/CashierController.php
  - Purpose:
    - Fetch tb_mas_sy by intID
    - Map enumSem to a two-digit code: "1st" -> "01", "2nd" -> "02", "3rd" -> "03", "4th" -> "04"; fallback by extracting leading digits if formatting differs
    - Determine SHS inclusion: if term_student_type contains "shs" (case-insensitive), insert "SHA" directly after strYearStart
    - Compute prefix = strYearStart + (isSHS ? "SHA" : "") + semCode
    - Determine max used suffix for that prefix by scanning tb_mas_users.strStudentNumber LIKE "{$prefix}%"; compute next counter = max + 1 (min 1)
    - Format result = prefix . sprintf("%03d", $nextCounter)  // no separators; default 3-digit padding
    - Collision hardening: in a short loop, check if result already exists; if yes, increment and re-check
- Modified functions:
  - Exact name: createPayment
  - Current file path: laravel-api/app/Http/Controllers/Api/V1/CashierController.php
  - Required changes:
    - In the tuition payment branch where enrollment is flipped to "enrolled" (near the "//Assign Student Number" placeholder), immediately generate the final student number with generateStudentNumber($syid) and update tb_mas_users.strStudentNumber regardless of previous value (temporary number may already be present).
    - Keep existing updates to tb_mas_registration and tb_mas_applicant_data as-is.
    - Optionally add a SystemLogService::log entry for the student number change.

[Classes]
We will modify one controller class.

Detailed breakdown:
- Modified classes:
  - Class: App\Http\Controllers\Api\V1\CashierController
  - Specific modifications:
    - Add generateStudentNumber helper
    - Wire the helper into the createPayment enrollment-finalization branch

[Dependencies]
No new PHP packages are required.

- Uses existing Illuminate\Support\Facades\DB and Illuminate\Support\Facades\Schema
- No external services or configuration

[Testing]
Manual end-to-end and targeted query tests.

- Pre-conditions:
  - tb_mas_sy contains the target syid with fields: enumSem, strYearStart, term_student_type
  - tb_mas_users has the target student with a temporary strStudentNumber (or any placeholder)
  - Trigger a tuition payment through POST /api/v1/cashiers/{id}/payments with description indicating tuition (as existing logic detects), causing enrollment flip to "enrolled"

- Expected:
  - When enrollment flips, strStudentNumber becomes:
    - For college: {strYearStart}{semCode}{NNN}, e.g. 202501001
    - For SHS: {strYearStart}SHA{semCode}{NNN}, e.g. 2025SHA01001
  - If multiple students are enrolled in the same term, suffix increments without collisions

- Validations:
  - Check tb_mas_users.strStudentNumber updated to the generated final value
  - Re-run a second enrollment in the same syid and verify the suffix increments by +1
  - Verify format rules (no separators; SHS mapping correct)

[Implementation Order]
Implement helper first, then wire-in at assignment point, followed by smoke verification.

1) Add protected helper generateStudentNumber(int $syid): string in CashierController
   - Implement enumSem mapping, SHS detection, prefix computation
   - Implement max-suffix scan and next number computation with 3-digit zero-padding
   - Add collision re-check loop

2) Modify createPayment()
   - In tuition enrollment flip branch (where enrollment_status set to "enrolled"), call generateStudentNumber($syid)
   - Update tb_mas_users.strStudentNumber with the newly generated number
   - Add optional system log for the change

3) Quick code scan
   - Ensure no naming conflicts; keep method visibility protected
   - Ensure no additional side effects to payment number logic

4) Manual Smoke
   - With a test user and a test syid, trigger tuition payment; verify strStudentNumber updated
