# Classlist Special Multiplier — TODO

Task Progress:
- [x] Step 1: Create migration to add tb_mas_classlist.special_class (TINYINT default 0) and special_multiplier (DECIMAL(8,4) default 1.0000, nullable) with guarded up/down
- [x] Step 2: Extend TuitionService subject query and mapper to include classlist_id, special_class, special_multiplier (with guards: treat null/<=0 as 1.0)
- [x] Step 3: Update TuitionCalculator::computeCollegeTuition to apply multiplier to non-NSTP per-unit lines (ignore for NSTP); store adjusted rate
- [x] Step 4: Update TuitionCalculator::computeSHSTuition to apply multiplier to MODULAR payment_amount and ELECTIVE rates; TRACK unchanged
- [ ] Step 5: Add CLI test script (scripts/test_special_class_multiplier.php) to validate multiplier deltas and manual test instructions
- [ ] Step 6: Run Critical-path testing
  - [ ] College: one flagged non-NSTP classlist shows multiplied amounts; flagged NSTP remains unchanged
  - [ ] SHS: flagged MODULAR and ELECTIVE lines multiply; TRACK unchanged
  - [ ] Save snapshot/invoice reflects adjusted totals

Notes:
- NSTP subjects: multiplier ignored.
- Guards: special_class=0 or invalid multiplier (null/<=0) defaults to 1.0 (no change).
- No change to lab/thesis fees, discounts/scholarships logic, or installment plan computation aside from propagated totals.
