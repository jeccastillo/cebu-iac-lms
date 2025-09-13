# Classlist Attendance — TODO

task_progress Items:
- [x] Step 1: Create DB migration for attendance tables (tb_mas_classlist_attendance_date, tb_mas_classlist_attendance)
- [x] Step 2: Add Eloquent models (ClasslistAttendanceDate, ClasslistAttendance)
- [x] Step 3: Add Gates in AuthServiceProvider (attendance.classlist.view/edit)
- [x] Step 4: Implement ClasslistAttendanceService (listDates, createDate, getDateDetails, saveMarks)
- [x] Step 5: Implement ClasslistAttendanceController endpoints (dates, createDate, dateDetails, save, deleteDate)
- [x] Step 6: Register API routes in laravel-api/routes/api.php
- [x] Step 7: Extend frontend classlists.service.js with attendance API methods
- [x] Step 8: Add Angular controller (attendance.controller.js) and template (attendance.html)
- [x] Step 9: Add route /classlists/:id/attendance in frontend/unity-spa/core/routes.js
- [x] Step 10: Add navigation link from viewer.html to Attendance page
- [ ] Step 11: Smoke test (manual) — create date, seed rows, mark/save attendance

Attendance Template Download/Upload Enhancements:
- [x] Step 12: Backend export class for attendance (.xlsx): App/Exports/ClasslistAttendanceTemplateExport.php
- [x] Step 13: Backend import request and service:
  - App/Http/Requests/Api/V1/Attendance/AttendanceImportRequest.php
  - App/Services/ClasslistAttendanceImportService.php
- [x] Step 14: Controller endpoints (per-date):
  - GET /api/v1/classlists/{id}/attendance/dates/{dateId}/template
  - POST /api/v1/classlists/{id}/attendance/dates/{dateId}/import
- [x] Step 15: Register new routes in laravel-api/routes/api.php (place before parameterized {dateId})
- [x] Step 16: Frontend service methods:
  - downloadAttendanceTemplate(classlistId, dateId)
  - importAttendance(classlistId, dateId, file)
- [x] Step 17: Frontend controller updates:
  - Add vm.downloadTemplate, vm.onImportFileChange, vm.importAttendance
  - Track vm.importFile, vm.uploading
- [x] Step 18: Frontend template controls:
  - “Download Template” button, file chooser, “Upload” button
- [ ] Step 19: Smoke tests (manual) — download template, edit is_present/remarks, upload, reload
- [ ] Step 20: Backend tests (feature) — auth, template content, import normalization/validation

Notes:
- Authorization: assigned faculty or admin (view/edit). Use Gate with header fallbacks similar to ClasslistGradesController (X-User-Roles, X-Faculty-ID).
- Defaults: is_present = null on seeding; remarks optional; clear remarks when is_present is true or null; keep remarks only for absent (false).
- Uniqueness:
  - (intClassListID, attendance_date, period) unique on date table.
  - (intAttendanceDateID, intCSID) unique on attendance rows.
- Save payload: items[].intCSID, items[].is_present (true|false|null), items[].remarks (string|null).
- Import accepted is_present tokens (case-insensitive):
  - Present/true: 1, true, present, p, yes
  - Absent/false: 0, false, absent, a, no
  - Unset/null: "", null, unset
