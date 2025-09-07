# Critical-Path Test Plan — Add Subjects to Curriculum (Bulk)

Objective
Verify the main happy paths and key interactions for the new multi-select “Add Subjects to Curriculum” feature, including backend bulk endpoint behavior and frontend UI flows. This plan avoids exhaustive edge-case coverage and focuses on critical functionality.

Scope
- Web UI (Curriculum Edit page)
  - New “Add Subjects” panel: search, department filter, pagination, checkbox multi-select up to 60, default Year/Sem and per-row overrides, “Update if exists” toggle, submit summary, and refresh of “already linked” flags.
  - Regression: Curricula list page works (listing/pagination/actions); core Curriculum Edit form unaffected.
- API (Backend)
  - POST /v1/curriculum/{id}/subjects/bulk happy path and duplicate handling with update_if_exists true/false.
  - Minimal validation checks (empty list, >60 cap); role enforcement; response structure.
  - Regression: POST /v1/curriculum/{id}/subjects (single add) and GET /v1/curriculum/{id}/subjects.

Prerequisites
- API base (adjust as needed):
  - Windows PowerShell example: $BASE = "http://localhost/laravel-api/public/api/v1"
  - Or: $BASE = "http://localhost/api/v1" (depending on your virtual host)
- Auth/Role: The routes are protected via role:registrar,admin.
  - When testing via backend cURL, include a valid session/cookie/bearer token as your environment requires.
  - When testing via the SPA, log in as a user with registrar or admin role.
- X-Faculty-ID header: Frontend services send X-Faculty-ID when available. For raw cURL, add this only if your RequireRole or internal policies need it.

Quick API sanity checks (PowerShell/cURL)
Note: Replace placeholders like {CURRICULUM_ID} and {SUBJECT_ID}.

1) List subjects (used by UI picker)
- GET $BASE/subjects?search=&department=&page=1&limit=25

2) Curriculum subjects (used to mark “Already Linked”)
- GET $BASE/curriculum/{CURRICULUM_ID}/subjects

3) Single add (regression)
- POST $BASE/curriculum/{CURRICULUM_ID}/subjects
  Body (application/json):
  {
    "intSubjectID": 123,
    "intYearLevel": 1,
    "intSem": 1
  }

4) Bulk add (new)
- POST $BASE/curriculum/{CURRICULUM_ID}/subjects/bulk
  Body (application/json):
  {
    "update_if_exists": false,
    "subjects": [
      { "intSubjectID": 123, "intYearLevel": 1, "intSem": 1 },
      { "intSubjectID": 456, "intYearLevel": 1, "intSem": 2 }
    ]
  }
- Re-run with update_if_exists=true to convert duplicates into updates.

Example PowerShell (adjust BASE and Auth headers as needed)
$BASE = "http://localhost/laravel-api/public/api/v1"
$CID  = 1   # curriculum id
# 1) Subjects index
iwr -UseBasicParsing -Method GET "$BASE/subjects?limit=5" | Select-Object -ExpandProperty Content

# 2) Curriculum subjects
iwr -UseBasicParsing -Method GET "$BASE/curriculum/$CID/subjects" | Select-Object -ExpandProperty Content

# 3) Bulk add (insert new)
$body = @{
  update_if_exists = $false
  subjects = @(
    @{ intSubjectID = 123; intYearLevel = 1; intSem = 1 },
    @{ intSubjectID = 456; intYearLevel = 1; intSem = 2 }
  )
} | ConvertTo-Json -Depth 5
iwr -UseBasicParsing -Method POST "$BASE/curriculum/$CID/subjects/bulk" -ContentType "application/json" -Body $body | Select-Object -ExpandProperty Content

# 4) Bulk add (update existing)
$body2 = @{
  update_if_exists = $true
  subjects = @(
    @{ intSubjectID = 123; intYearLevel = 2; intSem = 1 },
    @{ intSubjectID = 456; intYearLevel = 2; intSem = 2 }
  )
} | ConvertTo-Json -Depth 5
iwr -UseBasicParsing -Method POST "$BASE/curriculum/$CID/subjects/bulk" -ContentType "application/json" -Body $body2 | Select-Object -ExpandProperty Content

Expected Responses (Bulk)
- JSON:
  {
    "success": true,
    "result": {
      "inserted": <int>,
      "updated": <int>,
      "skipped": <int>,
      "errors": [ { "index": <int>, "subject_id": <int|null>, "message": "..." }, ... ],
      "limit": 60
    }
  }

Frontend (SPA) Critical Path
1) Navigate to Curriculum Edit page
- Open /#/curricula
- Click Edit on an existing curriculum (or create one first, then return to Edit).
- Confirm the basic form fields load and are editable.

2) Open “Add Subjects to Curriculum” panel (only visible in Edit mode)
- Verify:
  - Search box and Department filter are visible.
  - Defaults (Year Level=1, Sem=1) prefilled.
  - “Update Year/Sem if link already exists” toggle is visible (default off).
  - Selected counter shows 0/60.

3) Load subjects
- Click search with empty terms or add a quick filter and click search.
- Confirm subjects table loads (Code, Description, Units, Department).
- Already Linked badges appear for subjects already linked to the curriculum.

4) Multi-select up to 60
- Select several subjects via the checkbox.
- Confirm the counter increments and per-row inputs (Year/Sem) become editable.
- Adjust a few per-row Year/Sem to override defaults.

5) Apply Defaults to Selected
- Change default Year/Sem and click “Apply Defaults to Selected.”
- Verify that rows without overrides adopt the new defaults.

6) Submit
- Click “Add Selected Subjects.”
- Expected:
  - Summary message displayed (Inserted, Updated, Skipped).
  - “Already Linked” badges update after refresh of the curriculum subjects cache.
  - The selection clears to 0/60 and default values reset to 1.

7) Duplicate handling
- Re-run selection of the same subjects:
  - With Update if exists = Off → expect “Skipped” greater than 0 and no updates.
  - With Update if exists = On → expect “Updated” greater than 0.

8) Regression checks
- Curricula list: verify page loads and actions still work (Add/Edit/Delete).
- Curriculum Edit core form: update a field and save to ensure unaffected behavior.

Minimal Validation Checks (API via SPA)
- Selecting zero subjects should disable submission.
- Attempt selecting more than 60 → extra selections should be blocked in UI (and server capped at 60).
- Year Level and Sem must be between ranges (1..10 and 1..3). Confirm UI inputs enforce ranges.

Notes
- If your environment requires auth tokens or cookies, ensure the SPA is logged in as registrar/admin before testing UI flows.
- For direct API testing, include necessary auth headers or cookies according to your setup.

Logging and Observability
- SystemLogService captures an update log for bulk operations summarizing inserted/updated/skipped and update_if_exists flag.
- For API debugging, watch application logs for DB transactions and errors.

Pass/Fail Criteria (Critical)
- Able to add multiple subjects in one action (Inserted > 0 on first run).
- Duplicate submissions with update_if_exists=false skip; with update_if_exists=true update Year/Sem.
- UI clearly reflects “Already Linked” status and updates after submission.
- No regressions in Curriculum list and Edit forms.
