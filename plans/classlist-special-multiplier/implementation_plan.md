# Implementation Plan

[Overview]
Add special_class and special_multiplier columns to tb_mas_classlist and integrate their effect into tuition computation for both College (per-unit) and SHS (track/elective/modular) paths, ignoring NSTP subjects when applying the multiplier.

This change allows finance/registrar to flag a specific classlist as a "special class" and provide a multiplier that adjusts tuition rate calculations associated with that classlist. For College, the per-unit rate for that classlist will be multiplied by the provided factor (except NSTP). For SHS, modular payments and elective subject rates tied to the flagged classlist will be multiplied accordingly, while base track rates remain unaffected. The implementation is backwards-compatible: when special_class=0 or multiplier is null/1.0, behavior does not change. This integrates into existing TuitionService and TuitionCalculator flows that already join tb_mas_classlist/student/subjects for term tuition computation.

[Types]  
Introduce two new schema fields on tb_mas_classlist and propagate typed fields through the tuition computation inputs.

- Database: tb_mas_classlist (existing)
  - New columns:
    - special_class: TINYINT(1) NOT NULL DEFAULT 0
      - Semantic: 1 means enable class-specific rate multiplier for this classlist; 0 disables it.
      - Validation: Accept only 0 or 1.
    - special_multiplier: DECIMAL(8,4) NULL DEFAULT 1.0000
      - Semantic: Factor applied to the class’s effective rate where applicable.
      - Validation: When special_class=1, treat values <= 0 as invalid and default to 1.0000 at compute-time safeguards.
      - Range guidance: practical range [0.1000, 10.0000]; not enforced at DB, validated in app logic.

- In-memory payloads passed to TuitionCalculator methods (no PHP type files to add, but shape extended):
  - College subjects (from TuitionService::compute subject query)
    - subjectID: int
    - code: string
    - units: int
    - intLab: int
    - labClass: string
    - isNSTP: 0/1
    - isThesisSubject: 0/1
    - special_class: 0/1
    - special_multiplier: float|null
  - SHS subjects
    - subjectID: int
    - isElective: 0/1
    - additional_elective: 0/1
    - is_modular: 0/1
    - payment_amount: float
    - special_class: 0/1
    - special_multiplier: float|null

[Files]
Modify tuition computation services and create a migration; no new controllers or routes required.

- New files to be created
  - laravel-api/database/migrations/2025_09_20_000200_add_special_fields_to_tb_mas_classlist.php
    - Purpose: Add special_class and special_multiplier with safe up/down and guard checks.

- Existing files to be modified
  - laravel-api/app/Services/TuitionService.php
    - Extend subject gather query (JOIN tb_mas_classlist) to select cl.intID as classlist_id, cl.special_class, cl.special_multiplier.
    - Normalize mapped fields: special_class=int, special_multiplier=float default 1.0 when null/invalid.
    - Pass extended subjects array to TuitionCalculator for both College and SHS paths.
  - laravel-api/app/Services/TuitionCalculator.php
    - computeCollegeTuition():
      - When computing per-subject line amounts (non-NSTP), apply multiplier if special_class=1: effective_rate = unitFee * special_multiplier_guarded.
      - Preserve stored line item rate as effective_rate.
      - Do not apply multiplier to NSTP lines (explicitly ignored).
    - computeSHSTuition():
      - TRACK line (program track base rate) remains unchanged (not classlist-tied).
      - Modular lines (is_modular with payment_amount from classlist): apply multiplier to payment_amount when special_class=1.
      - ELECTIVE lines (from tuition_year_elective): when the student’s enrolled classlist for that elective is flagged, apply multiplier to the elective rate.
      - Store rate in line items as the effective rate after multiplier.

- Files to be deleted or moved
  - None.

- Configuration updates
  - None.

[Functions]
Extend query shaping in TuitionService and apply multipliers in TuitionCalculator on impacted functions only.

- New functions
  - None.

- Modified functions
  - TuitionService::compute(string $studentNumber, int $syid, ?int $discountId = null, ?int $scholarshipId = null)
    - Section “3) Gather subjects for the term”:
      - Add to select:
        - 'cl.intID as classlist_id'
        - 'cl.special_class as special_class'
        - 'cl.special_multiplier as special_multiplier'
      - In the map() normalizer:
        - $arr['special_class'] = (int) ($arr['special_class'] ?? 0);
        - $arr['special_multiplier'] = (float) (is_numeric($arr['special_multiplier'] ?? null) ? $arr['special_multiplier'] : 1.0);
        - Guard: if $arr['special_class'] === 0 or $arr['special_multiplier'] <= 0 then set $arr['special_multiplier'] = 1.0.
  - TuitionCalculator::computeCollegeTuition(array $subjects, array $tuitionYear, string $classType, int $syid, float $unitFee): array
    - For each subject:
      - If isNSTP is truthy, keep current NSTP logic (multiplier ignored).
      - Else compute effective_rate = unitFee; if (special_class==1) effective_rate = unitFee * guardMultiplier(special_multiplier).
      - lineAmount = units * effective_rate; store 'rate' = effective_rate.
  - TuitionCalculator::computeSHSTuition(array $subjects, array $tuitionYear, string $classType, int $yearLevel, int $programId): array
    - TRACK: unchanged (base program track rate).
    - MODULAR: when (!empty($s['is_modular']) and has payment_amount):
      - rate = payment_amount; if (special_class==1) rate *= guardMultiplier(special_multiplier); amount=rate; store adjusted rate.
    - ELECTIVE: when elective row resolved:
      - rate = elective rate by yearLevel; if (special_class==1) rate *= guardMultiplier(special_multiplier); amount=rate; store adjusted rate.

- Removed functions
  - None.

[Classes]
No new classes; modify existing service classes for tuition computation.

- New classes
  - None.

- Modified classes
  - App\Services\TuitionService
  - App\Services\TuitionCalculator

- Removed classes
  - None.

[Dependencies]
No new external dependencies or composer changes are required.

All changes leverage existing Laravel DB facade, Schema facade, and current tuition-year tables and services.

[Testing]
Add a focused CLI script and manual checks to validate multiplier behavior across college and SHS paths.

- New test script (optional but recommended)
  - laravel-api/scripts/test_special_class_multiplier.php
    - Usage: php laravel-api/scripts/test_special_class_multiplier.php <student_number> <syid> <classlist_id> <multiplier>
    - Steps:
      1) Capture baseline computation via TuitionService::compute (special_class disabled).
      2) Update tb_mas_classlist set special_class=1, special_multiplier=<multiplier> for <classlist_id>.
      3) Recompute and diff tuition items for:
         - College: verify only non-NSTP subject tied to classlist has rate multiplied.
         - SHS: verify MODULAR and ELECTIVE tied to classlist reflect multiplied rates; TRACK unchanged.
      4) Restore special_class=0 and special_multiplier=1.0000.
    - Output: JSON deltas for tuition_items list and totals.
- Manual testing checklist
  - College section with one non-NSTP subject and one NSTP subject in flagged classlist:
    - Expect only non-NSTP subject rate to change; NSTP unchanged.
  - SHS enrollment with one modular subject and one elective in flagged classlists:
    - Expect modular amount and elective rate to change; TRACK unchanged.
  - Edge cases:
    - special_class=1 with multiplier null/0/<=0 should behave as 1.0 due to guards.
    - special_class=0 should not change any totals.
  - Regression: tuition save, discounts, installment plans continue to work (multiplier influences only base tuition figures feeding those flows).

[Implementation Order]
Apply schema first, then service-level code changes, followed by testing.

1) Migration: add special_class (tinyint default 0) and special_multiplier (decimal(8,4) default 1.0000, nullable) to tb_mas_classlist with guarded up/down.
2) TuitionService: extend subject join select to include classlist_id, special_class, special_multiplier; normalize fields with guards in the mapper.
3) TuitionCalculator (College): apply multiplier to per-unit effective rate for non-NSTP subjects only; store effective rate in line items.
4) TuitionCalculator (SHS): apply multiplier to MODULAR payment_amount and to ELECTIVE rates tied to flagged classlists; keep TRACK unchanged.
5) Script: add test_special_class_multiplier.php to verify behavior; run manual validations on both paths and edge cases.

task_progress Items:
- [ ] Step 1: Add migration to introduce special_class and special_multiplier to tb_mas_classlist
- [ ] Step 2: Extend TuitionService subject query and mapping to include special fields
- [ ] Step 3: Update TuitionCalculator::computeCollegeTuition to apply multiplier to non-NSTP per-unit rates
- [ ] Step 4: Update TuitionCalculator::computeSHSTuition to apply multiplier to modular and elective rates; leave TRACK unchanged
- [ ] Step 5: Create CLI test script to validate multipliers and run manual checks
