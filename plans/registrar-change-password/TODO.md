# Registrar Change Password - TODO

Tracking the implementation steps for registrar-managed student password changes.

- [x] Step 1: Create backend request validator RegistrarStudentPasswordUpdateRequest.php
- [x] Step 2: Add StudentController@updatePassword method with hashing and SystemLog redaction
- [x] Step 3: Register POST /api/v1/students/{id}/password route with role:registrar,admin
- [ ] Step 4: Smoke test backend via cURL (generate/set)

- [x] Step 5: Create FE service change-password.service.js to call API
- [x] Step 6: Create FE controller change-password.controller.js and view change-password.html
- [x] Step 7: Wire route in core/routes.js and add sidebar link for Registrar section

- [ ] Step 8: Manual E2E test both modes; show generated password one-time
- [ ] Step 9: Refine UX (copy-to-clipboard, loading states, validation)
- [ ] Step 10: Final QA for role gating, logging, negative cases

## Notes
- Password policy: minimum 8 characters when mode is "set".
- Registrar can choose mode: "generate" (return generated password once) or "set" (no echo back).
- Autocomplete student selection will use GET /api/v1/students?q=...&amp;per_page=10.
- Logging via SystemLogService with redacted password values.
