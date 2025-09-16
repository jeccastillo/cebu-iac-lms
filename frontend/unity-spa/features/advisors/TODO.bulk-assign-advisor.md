# Bulk Assign Students to Advisor — Include advisor_id in payload

Summary:
Ensure the Bulk Assign operation posts advisor_id in the JSON payload, in addition to using the advisorId path parameter.

Backend:
- API: POST /api/v1/advisors/{advisorId}/assign-bulk
- Controller reads advisorId from URL and ignores extra fields safely.
- No backend changes required.

Plan:
- [x] Frontend: advisors.controller.js — in assignBulk(), add payload.advisor_id = advisorId.
- [x] Frontend: advisors.service.js — in assignBulk(advisorId, payload), ensure payload.advisor_id is set from advisorId if missing.
- [ ] Validate via UI/Network tab that payload includes advisor_id.
- [ ] Quick regression check: success/error messages still behave as before.

Files changed:
- frontend/unity-spa/features/advisors/advisors.controller.js
  - Added:
    // Include advisor_id in request body for parity/analytics, even though API also uses path param
    payload.advisor_id = advisorId;

- frontend/unity-spa/features/advisors/advisors.service.js
  - Added:
    // Ensure advisor_id is included in body for parity/analytics
    if (advisorId != null &amp;&amp; payload.advisor_id == null) {
      var aid = parseInt(advisorId, 10);
      if (isFinite(aid)) payload.advisor_id = aid;
    }

Test steps:
1. Open Advisors Management page.
2. Select an Advisor and one or more students, click Assign Bulk.
3. Inspect the Network POST to /advisors/{advisorId}/assign-bulk:
   - Payload should include:
     {
       advisor_id: <advisorId>,
       student_ids: [...]/student_numbers: [...],
       replace_existing: <bool>
     }
4. Confirm operation completes and results render as before.

Notes:
- advisor_id in payload is redundant with the URL param but harmless. It may be useful for logging/analytics and for future API parity expectations.
