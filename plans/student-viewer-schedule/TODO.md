task_progress Items:
- [x] Create implementation plan
- [ ] Backend: add getClasslistSchedulesForTerm(...) in DataFetcherService to bulk fetch schedules by classlist IDs for a term
- [ ] Backend: add summarizeSchedules(...) in DataFetcherService to compute days/times/rooms and schedule_text
- [ ] Backend: integrate schedule summaries into getStudentRecords(...)
- [ ] Backend: integrate schedule summaries into getStudentRecordsByTerm(...)
- [ ] Verify API payloads for records and records-by-term endpoints
- [ ] Frontend: add “Schedule” column in viewer.html (per-term table and flat fallback)
- [ ] Frontend: optional vm.formatSchedule fallback in viewer.controller.js
- [ ] Manual test with students across terms and multiple schedules
