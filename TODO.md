# PHP Error Fix - Portal.php Line 204

## Task: Fix "Cannot use object of type stdClass as array" error

### Steps to Complete:
- [x] Identify the issue in Portal.php at line 204
- [x] Analyze the problematic code in submit_enlistment_form() method
- [x] Fix the array access issue by changing `$enlistment['id']` to `$enlistment->id`
- [ ] Check for similar issues in the same method or file
- [ ] Verify the fix is complete

### Issue Details:
- **File:** application/modules/portal/controllers/Portal.php
- **Line:** 204 (approximately)
- **Method:** submit_enlistment_form()
- **Problem:** Using array syntax `['id']` on stdClass object returned by `first_row()`
- **Solution:** Change to object syntax `->id`

### Status: Fix Applied - Checking for Additional Issues
