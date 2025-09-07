# Implementation Plan

[Overview]
Add a Schedule column to the Student Viewer Subjects table that shows the class meeting days (short format like MW), meeting times (combined if multiple), and room codes (combined if multiple) for each class the student is enlisted in for the selected/current term.

This change improves the Student Viewer by surfacing schedule details directly alongside subject records, avoiding context switching to the separate Schedules feature. The Student Viewer already loads student records by term; we will augment the backend records payload to include per-class summarized schedule info so the frontend can simply render a single string in a new "Schedule" column. The summary logic will handle classes having multiple schedules (e.g., separate lecture/lab, or multiple days) by concatenating day short-codes, compressing and deduping time ranges as needed, and presenting distinct room codes.

[Types]  
No external library/TS type system changes; API response shapes are extended to include schedule summary fields.

- API response shape additions for each subject record (DataFetcherService@getStudentRecords and getStudentRecordsByTerm):
  - schedule_days: string | null
    - Short day codes concatenated in order (M T W Th F Sa Su compressed to strings like "MWF" or "TTh").
    - Example rules:
      - 1: M, 2: T, 3: W, 4: Th, 5: F, 6: Sa, 7: Su
      - Sorted by day index ascending.
      - Deduplicate if multiple entries for same day/time.
  - schedule_times: string | null
    - One or more ranges in 24h HH:MM-HH:MM, sorted, unique. If multiple unique time ranges exist, join with ", ".
    - Example: "10:00-11:00, 13:00-14:30"
  - schedule_rooms: string | null
    - Unique room codes joined by ", ". Only the room code (e.g., "Rm 301"). No description per request.
  - schedule_text: string | null
    - Preformatted string intended for direct rendering in UI:
      - If there is one unique time range and one or more days, prefer: "{days} {time} — {rooms}"
      - If multiple times: "{days} {times} — {rooms}"
      - If no days but have times/rooms, fallback: "{times} — {rooms}" or only "{rooms}" if needed
      - If no schedule found, null

[Files]
Modify backend service to enrich student records with schedule summaries and add a Schedule column on the Student Viewer page.

- New files to be created
  - None.

- Existing files to be modified
  - laravel-api/app/Services/DataFetcherService.php
    - Add helper methods to fetch and summarize schedules keyed by classlist_id for a given term (syid).
    - Enhance getStudentRecords(...) to attach schedule fields when syid is present on rows. For multi-term (all terms) responses, compute summaries by syid/classlist_id pairs.
    - Enhance getStudentRecordsByTerm(...) to attach schedule fields for that specific term (syid).
  - laravel-api/app/Http/Controllers/Api/V1/StudentController.php
    - No signature changes; relies on DataFetcherService enhancements to include schedule_* fields in returned payloads.
  - frontend/unity-spa/features/students/viewer.html
    - Add a new table header "Schedule" in Subjects table (in both per-term and flat fallback tables).
    - Render r.schedule_text (falling back to locally computed formatting if ever needed).
  - frontend/unity-spa/features/students/viewer.controller.js
    - Optional: Provide a tiny fallback formatter vm.formatSchedule(r) if schedule_text is missing, combining any raw schedule fields if provided later (non-blocking).
    - No additional API calls from frontend to avoid N+1 requests.

- Files to be deleted or moved
  - None.

- Configuration file updates
  - None.

[Functions]
Add summarization helpers on the backend; render-only changes on the frontend.

- New functions (name, signature, file path, purpose)
  - DataFetcherService::getClasslistSchedulesForTerm(array $classlistIds, int|string $syid): array
    - File: laravel-api/app/Services/DataFetcherService.php
    - Purpose: Query tb_mas_room_schedules joined with classrooms for the given set of classlist IDs for a specific term (syid), returning rows grouped by classlist_id: [classlist_id => [ { day, start, end, room_code }, ... ] ].
  - DataFetcherService::summarizeSchedules(array $rows): array
    - File: laravel-api/app/Services/DataFetcherService.php
    - Purpose: Given an array of schedule rows for a classlist, compute:
      - days string (short codes M T W Th F Sa Su, concatenated and deduped),
      - times string (unique HH:MM-HH:MM ranges sorted),
      - rooms string (unique room codes joined),
      - text formatted as "{days} {times} — {rooms}" (flexible if days/times missing).
    - Returns associative array with keys: days, times, rooms, text.

- Modified functions (exact name, current file path, required changes)
  - DataFetcherService::getStudentRecords(int $studentId, ?string $term, bool $includeGrades)
    - File: laravel-api/app/Services/DataFetcherService.php
    - Change: After retrieving $records, collect all rows that have classlist_id and syid; bulk-fetch schedules by term (syid) and classlist_id; summarize per-classlist and merge fields into each $records[] item as schedule_days, schedule_times, schedule_rooms, schedule_text.
  - DataFetcherService::getStudentRecordsByTerm(int $studentId, string $term, bool $includeGrades)
    - File: laravel-api/app/Services/DataFetcherService.php
    - Change: Similar to above but simpler since term is known; fetch for classlist_id set with that syid, attach summaries to records inside the single term.

- Removed functions
  - None.

[Classes]
No new classes; existing models assumed present.

- Modified classes
  - None (only service functions added; models remain unchanged).

- New/Removed classes
  - None.

[Dependencies]
No new Composer or NPM packages.

- Laravel/Eloquent used to query:
  - tb_mas_room_schedules (RoomSchedule model)
  - tb_mas_classrooms (Classroom model; for strRoomCode)
- No version changes.

[Testing]
Add manual and API-level tests to validate schedule summaries appear and format correctly.

- Backend
  - Hit POST /api/v1/student/records (with student_id and include_grades=true, optionally term omitted to return multiple terms).
  - Hit POST /api/v1/student/records-by-term (with student_id, term, include_grades=true).
  - Validate that each record in data.records or in data.terms[*].records has:
    - schedule_text consistent with the source schedules
    - schedule_days compressed short codes
    - schedule_times correctly formatted and deduped
    - schedule_rooms listing only room codes
- Frontend
  - Load Student Viewer for a student with scheduled classes in the selected/current term.
  - Verify Schedule column:
    - Single-schedule class: “MW 10:00-11:00 — 305”
    - Multi-schedule different time ranges: “TTh 09:00-10:30, 13:00-14:00 — 305, 405”
    - No schedule: shows “-”

[Implementation Order]
Backend first, then frontend to minimize UI churn.

1) Backend: DataFetcherService
   - Implement getClasslistSchedulesForTerm(...)
   - Implement summarizeSchedules(...)
   - Integrate summaries into getStudentRecords(...) and getStudentRecordsByTerm(...)
2) Validate payload via Postman/curl on records and records-by-term endpoints.
3) Frontend: viewer.html
   - Add Schedule column in both per-term table and flat fallback table, rendering r.schedule_text || '-'.
4) Frontend: viewer.controller.js
   - Optional formatter vm.formatSchedule(r) as a safe fallback (kept simple).
5) Manual test across different students and terms; verify rendering and edge cases.
